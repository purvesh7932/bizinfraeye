<?php
include_once dirname(__FILE__) . '/models/Alert.php';
include_once dirname(__FILE__) . '/models/SearchFilter.php';
include_once dirname(__FILE__) . '/models/Paging.php';

class Mobile_WS_GetBGList extends Mobile_WS_Controller {

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
		$current_user = $this->getActiveUser();
		$response = new Mobile_API_Response();
		$module = $request->get('module');
		if (empty($module)) {
			$response->setError(100, "module Is Missing");
			return $response;
		}
		$filterId = $request->get('filterid');
		$page = $request->get('page', '1');
		$orderBy = $request->getForSql('orderBy');
		$sortOrder = $request->getForSql('sortOrder');

		$moduleModel = Vtiger_Module_Model::getInstance($module);
		$headerFieldModels = $moduleModel->getHeaderViewFieldsList();

		$headerFields = array();
		$fields = array();
		$headerFieldColsMap = array();
		$headerFieldStatusValue = $request->get('headerFieldStatusValue');
		$headerFieldPurposeValue = $request->get('headerFieldPurposeValue');
		$additinalConditions = [];
		if (!empty($headerFieldStatusValue) && empty($headerFieldPurposeValue)) {
			$additinalCondition = array('bnk_pre_status', 'e', $headerFieldStatusValue);
			array_push($additinalConditions, $additinalCondition);
			$nameFields = $this->getConfiguredStatusFields($headerFieldStatusValue);
		} else if (!empty($headerFieldStatusValue) && !empty($headerFieldPurposeValue)) {
			$additinalCondition = array('bnk_pre_status', 'e', $headerFieldStatusValue);
			array_push($additinalConditions, $additinalCondition);
			$additinalCondition = array('purpose', 'e', $headerFieldPurposeValue);
			array_push($additinalConditions, $additinalCondition);
			$nameFields = $this->getConfiguredStatusFields($headerFieldStatusValue . ' - ' . $headerFieldPurposeValue);
		} else {
			if (empty($headerFieldStatusValue)) {
				$headerFieldStatusValue = 'All-Mobile-Field-List';
			}
			$nameFields = $this->getConfiguredStatusFields($headerFieldStatusValue);
			// $nameFields =  $moduleModel->getNameFields();
		}
		if (is_string($nameFields)) {
			$nameFieldModel = $moduleModel->getField($nameFields);
			$headerFields[] = $nameFields;
			$fields = array('name' => $nameFieldModel->get('name'), 'label' => vtranslate($nameFieldModel->get('label'), $module), 'fieldType' => $nameFieldModel->getFieldDataType());
		} else if (is_array($nameFields)) {
			foreach ($nameFields as $nameField) {
				$nameFieldModel = $moduleModel->getField($nameField);
				$headerFields[] = $nameField;
				$fields[] = array('name' => $nameFieldModel->get('name'), 'label' => vtranslate($nameFieldModel->get('label'), $module), 'fieldType' => $nameFieldModel->getFieldDataType());
			}
		}
		
		// foreach ($headerFieldModels as $fieldName => $fieldModel) {
		// 	$headerFields[] = $fieldName;
		// 	// $fields[] = array('name' => $fieldName, 'label' => vtranslate($fieldModel->get('label'), $module), 'fieldType' => $fieldModel->getFieldDataType());
		// 	$headerFieldColsMap[$fieldModel->get('column')] = $fieldName;
		// }

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

		if (!empty($request->get('search_params')) || !empty($request->get('headerFieldStatusValue'))) {
			$searchParams = json_decode($request->get('search_params'));
			if (empty($searchParams)) {
				$searchParams = [];
			}
			$i = 0;
			foreach ($searchParams as $listSearchParam) {
				if ($listSearchParam[1] == 'moneq') {
					$searchVal = $listSearchParam[2];
					list($month, $year) = explode('-', $searchVal);
					$monthCode = $this->getMonthDbCode($month);
					if ($monthCode == '') {
						$response->setError(100, "Invalid Month Is Given For Filter Condition");
						return $response;
					} else {
						$firstDate = date('d/m/Y', strtotime("first day of $month $year"));
						$lastDate = date('d/m/Y', strtotime("last day of $month $year"));
						$searchParams[$i][2] =  "$firstDate,$lastDate";
						$searchParams[$i][1] =  "bw";
					}
				}
				$i = $i + 1;
			}
			$searchParams = array(array_merge($searchParams, $additinalConditions));
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
			$referenceFields = $this->getReferenceFields($fields);
			foreach ($listViewEntries as $index => $listViewEntryModel) {
				$data = $listViewEntryModel->getRawData();
				$record = array('id' => $moduleWSID . 'x' . $listViewEntryModel->getId());
				foreach ($data as $i => $value) {
					if (is_string($i)) {
						if (isset($headerFieldColsMap[$i])) {
							$i = $headerFieldColsMap[$i];
						}
						$record[$i] = decode_html($value);
						if (in_array($i, $referenceFields)) {
							$record[$i] = Vtiger_Functions::getCRMRecordLabel($value);
						}
						if (in_array($i, $dateFields) && !empty($record[$i])) {
							$record[$i] = Vtiger_Date_UIType::getDisplayDateValue($record[$i]);
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
		// $newHeaders = [];
		// foreach ($fields as $field) {
		// 	if ($field['name'] != 'sr_equip_model') {
		// 		array_push($newHeaders, $field);
		// 	}
		// }
		// $moduleModel = Vtiger_Module_Model::getInstance('HelpDesk');
		// $counts = $moduleModel->getTicketsByStatusCountsForUser('', '', $headerFieldStatusValue);
		$response->setResult(array(
			// 'ticketStatusCounts' => $counts,
			'records' => $records,
			'records_per_page' => $pageLimit,
			'headers' => $fields,
			'selectedFilter' => $filterId,
			'nameFields' => $nameFields,
			'moreRecords' => $moreRecords,
			'orderBy' => $orderBy,
			'sortOrder' => $sortOrder,
			'page' => $page
		));
		$response->setApiSucessMessage('Successfully Fetched Data');
		return $response;
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
				array_push($fields, $headerField['name']);
			}
		}
		return $fields;
	}
	public function getMonthDbCode($month) {
		$code = '';
		switch ($month) {
			case 'January':
				$code = 1;
				break;
			case 'February':
				$code = 2;
				break;
			case 'March':
				$code = 3;
				break;
			case 'April':
				$code = 4;
				break;
			case 'May':
				$code = 5;
				break;
			case 'June':
				$code = 6;
				break;
			case 'July':
				$code = 7;
				break;
			case 'August':
				$code = 8;
				break;
			case 'September':
				$code = 9;
				break;
			case 'October':
				$code = 10;
				break;
			case 'November':
				$code = 11;
				break;
			case 'December':
				$code = 12;
				break;
			default:
				$code = '';
				break;
		}
		return $code;
	}
	public function transferListSearchParamsToFilterCondition($listSearchParams, $moduleModel) {
		return Vtiger_Util_Helper::transferListSearchParamsToFilterCondition($listSearchParams, $moduleModel);
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

	function getConfiguredStatusFields($statusFilterName) {
		global $adb;
		$sql = "SELECT columnname FROM `vtiger_customview` inner join vtiger_cvcolumnlist " .
			"on vtiger_cvcolumnlist.cvid = vtiger_customview.cvid " .
			"where vtiger_customview.viewname = ? and vtiger_customview.userid = 1 
			and vtiger_customview.entitytype = 'BankGuarantee' 
			ORDER BY `vtiger_cvcolumnlist`.`columnindex` ASC";
		$result = $adb->pquery($sql, array($statusFilterName));
		$columns = [];
		while ($row = $adb->fetch_array($result)) {
			$columnname = $row['columnname'];
			$columnname = explode(':', $columnname);
			array_push($columns, $columnname[2]);
		}
		return $columns;
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
