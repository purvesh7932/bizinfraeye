<?php
include_once dirname(__FILE__) . '/models/Alert.php';
include_once dirname(__FILE__) . '/models/SearchFilter.php';
include_once dirname(__FILE__) . '/models/Paging.php';

class Mobile_WS_ListModuleRecords extends Mobile_WS_Controller {

	function isCalendarModule($module) {
		return ($module == 'Events' || $module == 'Calendar');
	}

	function getSearchFilterModel($module, $search) {
		return Mobile_WS_SearchFilterModel::modelWithCriterias($module, Zend_JSON::decode($search));
	}

	function getPagingModel(Mobile_API_Request $request) {
		$page = $request->get('page', 0);
		return Mobile_WS_PagingModel::modelWithPageStart($page);
	}

	function process(Mobile_API_Request $request) {
		$module = $request->get('module');
		$filterId = $request->get('filterid');
		$page = $request->get('page', '1');
		$orderBy = $request->getForSql('orderBy');
		$sortOrder = $request->getForSql('sortOrder');

		$moduleModel = Vtiger_Module_Model::getInstance($module);
		$headerFieldModels = $moduleModel->getHeaderViewFieldsList();

		$headerFields = array();
		$fields = array();
		$headerFieldColsMap = array();

		$nameFields = $moduleModel->getNameFields();
		$listViewGeneralisedModules = [
			'ServiceOrders', 'StockTransferOrders',
			'Invoice', 'DeliveryNotes',
			'ReturnSaleOrders', 'BankGuarantee', 'Equipment',
			'EquipmentAvailability', 'ServiceReports', 'RecommissioningReports',
			'Documents', 'HelpDesk'
		];
		if (in_array($module, $listViewGeneralisedModules)) {
			$headerFieldStatusValue = 'All-Mobile-Field-List';
			$nameFields = $this->getConfiguredStatusFields($headerFieldStatusValue, $module);
		}

		if (is_string($nameFields)) {
			$nameFieldModel = $moduleModel->getField($nameFields);
			$headerFields[] = $nameFields;
			$fields = array('name' => $nameFieldModel->get('name'), 'label' => vtranslate($nameFieldModel->get('label'), $module), 'fieldType' => $nameFieldModel->getFieldDataType());
		} else if (is_array($nameFields)) {
			foreach ($nameFields as $nameField) {
				$nameFieldModel = $moduleModel->getField($nameField);
				if (empty($nameFieldModel)) {
					$fields[] = array(
						'name' => $nameField,
					);
					$headerFields[] = $nameField;
					continue;
				}
				$headerFields[] = $nameField;
				$fieldType = $nameFieldModel->getFieldDataType();
				if ($fieldType == 'reference') {
					$fields[] = array(
						'name' => $nameFieldModel->get('name'),
						'label' => vtranslate($nameFieldModel->get('label'), $module),
						'fieldType' => $nameFieldModel->getFieldDataType(),
						'referesTo' => $nameFieldModel->getReferenceList()
					);
				} else {
					$fields[] = array(
						'name' => $nameFieldModel->get('name'),
						'label' => vtranslate($nameFieldModel->get('label'), $module),
						'fieldType' => $nameFieldModel->getFieldDataType()
					);
				}
			}
		}

		foreach ($headerFieldModels as $fieldName => $fieldModel) {
			$headerFields[] = $fieldName;
			$fieldType = $fieldModel->getFieldDataType();
			if ($fieldType == 'reference') {
				$fields[] = array(
					'name' => $fieldName,
					'label' => vtranslate($fieldModel->get('label'), $module),
					'fieldType' => $fieldType,
					'referesTo' => $fieldModel->getReferenceList()
				);
			} else {
				$fields[] = array(
					'name' => $fieldName,
					'label' => vtranslate($fieldModel->get('label'), $module),
					'fieldType' => $fieldType,
				);
			}
			$headerFieldColsMap[$fieldModel->get('column')] = $fieldName;
		}

		if ($module == 'HelpDesk') $headerFieldColsMap['title'] = 'ticket_title';
		if ($module == 'Documents') $headerFieldColsMap['title'] = 'notes_title';
		global $fetchinFormMobile;
		$fetchinFormMobile = true;
		$listViewModel = Vtiger_ListView_Model::getInstance($module, $filterId, $headerFields);

		if (!empty($sortOrder)) {
			$listViewModel->set('orderby', $orderBy);
			$listViewModel->set('sortorder', $sortOrder);
		}
		if (!empty($request->get('search_key'))) {
			$listViewModel->set('search_key', $request->get('search_key'));
			$listViewModel->set('search_value', $request->get('search_value'));
			$listViewModel->set('operator', $request->get('operator'));
		}

		$response = new Mobile_API_Response();
		if (!empty($request->get('search_params'))) {
			$searchParams = json_decode($request->get('search_params'));
			if (empty($searchParams)) {
				$searchParams = [];
			}
			if(empty($searchParams) && !empty($request->get('search_params'))){
				$response->setError(100, "Invalid search_params Format ");
            	return $response;
			}
			$searchParams = array($searchParams);
			$transformedSearchParams = $this->transferListSearchParamsToFilterCondition($searchParams, $listViewModel->getModule());
			$listViewModel->set('search_params', $transformedSearchParams);
		}

		$listViewModel->set('searchAllFieldsValue', $request->get('searchAllFieldsValue'));

		$pagingModel = new Vtiger_Paging_Model();
		$pageLimit = $pagingModel->getPageLimit();
		$pagingModel->set('page', $page);
		$pagingModel->set('limit', $pageLimit);
		$listViewEntries = $listViewModel->getListViewEntries($pagingModel);

		if (empty($filterId)) {
			$customView = new CustomView($module);
			$filterId = $customView->getViewId($module);
		}

		if ($listViewEntries) {
			$moduleWSID = Mobile_WS_Utils::getEntityModuleWSId($module);
			$dateFields = $this->getDateFields($fields);
			$referenceFieldsWithCode = $this->getReferenceFields($fields);
			$referenceFields = array_keys($referenceFieldsWithCode);
			$multiPicklistFields = $this->getMultiPickListFields($fields);
			$focusObj = CRMEntity::getInstance($module);
			$moduleIdColumn = $focusObj->table_index;
			global $site_URL_NonHttp;
			foreach ($listViewEntries as $index => $listViewEntryModel) {
				$data = $listViewEntryModel->getRawData();
				$recordID =  $data[$moduleIdColumn];
				$record = array('id' => $moduleWSID . 'x' . $recordID);
				foreach ($data as $i => $value) {
					if (is_string($i)) {
						if (isset($headerFieldColsMap[$i])) {
							$i = $headerFieldColsMap[$i];
						}
						$record[$i] = decode_html($value);
						if (in_array($i, $referenceFields)) {
							if (!empty($value)) {
								$record[$i] = Vtiger_Functions::getCRMRecordLabel($value);
								$record[$i . '_idofreference'] = $referenceFieldsWithCode[$i] . 'x' . $value;
								$record[$i . '_Label'] = $record[$i];
							} else {
								$record[$i] = "";
								$record[$i . '_idofreference'] = "";
							}
						} else if (in_array($i, $dateFields) && !empty($record[$i])) {
							$record[$i] = Vtiger_Date_UIType::getDisplayDateValue($record[$i]);
						} else if (in_array($i, $multiPicklistFields) && !empty($record[$i])) {
							$record[$i] = str_replace('|##|', ',', $record[$i]);
						} else if ($i == 'is_submitted') {
							if ($record[$i] == '1') {
								$record['report_status'] = 'Submitted';
								$record['report_url'] = $site_URL_NonHttp . "modules/Mobile/v1/DownloadPDFReport?module=PDFMaker&source_module=ServiceReports&action=IndexAjax&record=$recordID&mode=getPreviewContent&language=en_us&generate_type=attachment&igtempid=1&access_token=".$request->get('access_token')."&useruniqueid=".$request->get('useruniqueid');
							} else {
								$record['report_status'] = 'In Progress';
								$record['report_url'] = NULL;
							}
							if ($data['is_recommisionreport'] == '1') {
								$record['report_status'] = 'Closed : Recommissioning Is Pending';
							}
						} else if ($i == 'notesid') {
							$record['doc_url'] = $site_URL_NonHttp . 'modules/Mobile/v1/DownloadFile?record='.$data['notesid'].'&access_token='.$request->get('access_token').'&useruniqueid='.$request->get('useruniqueid');
						} else if($i == 'createdtime' || $i == 'modifiedtime'){
							$record[$i] = $listViewEntryModel->get($i);
						}
					}
				}
				$records[] = $record;
			}
		}

		$moreRecords = false;
		if ((count($listViewEntries) + 1) > $pageLimit) {
			$moreRecords = true;
			// array_pop($records);
		}

		if (empty($records)) {
			$records = array();
		}
		$response->setResult(array(
			'records' => $records,
			'headers' => $fields,
			'selectedFilter' => $filterId,
			'records_per_page' => $pageLimit,
			'nameFields' => $nameFields,
			'moreRecords' => $moreRecords,
			'orderBy' => $orderBy,
			'sortOrder' => $sortOrder,
			'page' => $page
		));
		$response->setApiSucessMessage('Successfully Fetched Data');
		return $response;
	}

	public function transferListSearchParamsToFilterCondition($listSearchParams, $moduleModel) {
		return Vtiger_Util_Helper::transferListSearchParamsToFilterCondition($listSearchParams, $moduleModel);
	}

	function getDateFields($headerFields) {
		$dateFields = [];
		foreach ($headerFields as $index => $headerField) {
			if ($headerField['fieldType'] == 'date') {
				array_push($dateFields, $headerField['name']);
			}
		}
		return $dateFields;
	}

	function getReferenceFields($headerFields) {
		$fields = [];
		foreach ($headerFields as $index => $headerField) {
			if ($headerField['fieldType'] == 'reference') {
				$fields[$headerField['name']] = Mobile_WS_Utils::getEntityModuleWSId($headerField['referesTo'][0]);
			}
		}
		return $fields;
	}

	function getMultiPickListFields($headerFields) {
		$fields = [];
		foreach ($headerFields as $index => $headerField) {
			if ($headerField['fieldType'] == 'multipicklist') {
				array_push($fields, $headerField['name']);
			}
		}
		return $fields;
	}

	function getConfiguredStatusFields($statusFilterName, $moduleName) {
		global $adb;
		$sql = "SELECT columnname FROM `vtiger_customview` inner join vtiger_cvcolumnlist " .
			"on vtiger_cvcolumnlist.cvid = vtiger_customview.cvid " .
			"where vtiger_customview.viewname = ? and vtiger_customview.userid = 1 
			and vtiger_customview.entitytype = ? 
			ORDER BY `vtiger_cvcolumnlist`.`columnindex` ASC";
		$result = $adb->pquery($sql, array($statusFilterName, $moduleName));
		$columns = [];
		while ($row = $adb->fetch_array($result)) {
			$columnname = $row['columnname'];
			$columnname = explode(':', $columnname);
			array_push($columns, $columnname[2]);
		}
		return $columns;
	}

	function processSearchRecordLabelForCalendar(Mobile_API_Request $request, $pagingModel = false) {
		$current_user = $this->getActiveUser();

		// Fetch both Calendar (Todo) and Event information
		$moreMetaFields = array('date_start', 'time_start', 'activitytype', 'location');
		$eventsRecords = $this->fetchRecordLabelsForModule('Events', $current_user, $moreMetaFields, false, $pagingModel);
		$calendarRecords = $this->fetchRecordLabelsForModule('Calendar', $current_user, $moreMetaFields, false, $pagingModel);

		// Merge the Calendar & Events information
		$records = array_merge($eventsRecords, $calendarRecords);

		$modifiedRecords = array();
		foreach ($records as $record) {
			$modifiedRecord = array();
			$modifiedRecord['id'] = $record['id'];
			unset($record['id']);
			$modifiedRecord['eventstartdate'] = $record['date_start'];
			unset($record['date_start']);
			$modifiedRecord['eventstarttime'] = $record['time_start'];
			unset($record['time_start']);
			$modifiedRecord['eventtype'] = $record['activitytype'];
			unset($record['activitytype']);
			$modifiedRecord['eventlocation'] = $record['location'];
			unset($record['location']);

			$modifiedRecord['label'] = implode(' ', array_values($record));

			$modifiedRecords[] = $modifiedRecord;
		}

		$response = new Mobile_API_Response();
		$response->setResult(array('records' => $modifiedRecords, 'module' => 'Calendar'));

		return $response;
	}

	function fetchRecordLabelsForModule($module, $user, $morefields = array(), $filterOrAlertInstance = false, $pagingModel = false) {
		if ($this->isCalendarModule($module)) {
			$fieldnames = Mobile_WS_Utils::getEntityFieldnames('Calendar');
		} else {
			$fieldnames = Mobile_WS_Utils::getEntityFieldnames($module);
		}

		if (!empty($morefields)) {
			foreach ($morefields as $fieldname) $fieldnames[] = $fieldname;
		}

		if ($filterOrAlertInstance === false) {
			$filterOrAlertInstance = Mobile_WS_SearchFilterModel::modelWithCriterias($module);
			$filterOrAlertInstance->setUser($user);
		}

		return $this->queryToSelectFilteredRecords($module, $fieldnames, $filterOrAlertInstance, $pagingModel);
	}

	function queryToSelectFilteredRecords($module, $fieldnames, $filterOrAlertInstance, $pagingModel) {

		if ($filterOrAlertInstance instanceof Mobile_WS_SearchFilterModel) {
			return $filterOrAlertInstance->execute($fieldnames, $pagingModel);
		}

		global $adb;

		$moduleWSId = Mobile_WS_Utils::getEntityModuleWSId($module);
		$columnByFieldNames = Mobile_WS_Utils::getModuleColumnTableByFieldNames($module, $fieldnames);

		// Build select clause similar to Webservice query
		$selectColumnClause = "CONCAT('{$moduleWSId}','x',vtiger_crmentity.crmid) as id,";
		foreach ($columnByFieldNames as $fieldname => $fieldinfo) {
			$selectColumnClause .= sprintf("%s.%s as %s,", $fieldinfo['table'], $fieldinfo['column'], $fieldname);
		}
		$selectColumnClause = rtrim($selectColumnClause, ',');

		$query = $filterOrAlertInstance->query();
		$query = preg_replace("/SELECT.*FROM(.*)/i", "SELECT $selectColumnClause FROM $1", $query);

		if ($pagingModel !== false) {
			$query .= sprintf(" LIMIT %s, %s", $pagingModel->currentCount(), $pagingModel->limit());
		}

		$prequeryResult = $adb->pquery($query, $filterOrAlertInstance->queryParameters());
		return new SqlResultIterator($adb, $prequeryResult);
	}
}
