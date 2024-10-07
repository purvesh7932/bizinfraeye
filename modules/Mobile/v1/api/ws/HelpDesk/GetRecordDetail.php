<?php
include_once 'include/Webservices/Retrieve.php';
include_once dirname(__FILE__) . '/../FetchRecord.php';
include_once 'include/Webservices/DescribeObject.php';
include_once('include/utils/GeneralUtils.php');
include_once('include/utils/GeneralConfigUtils.php');
class Mobile_WS_GetRecordDetail extends Mobile_WS_FetchRecord {

	private $_cachedDescribeInfo = false;
	private $_cachedDescribeFieldInfo = false;

	protected function cacheDescribeInfo($describeInfo) {
		$this->_cachedDescribeInfo = $describeInfo;
		$this->_cachedDescribeFieldInfo = array();
		if (!empty($describeInfo['fields'])) {
			foreach ($describeInfo['fields'] as $describeFieldInfo) {
				$this->_cachedDescribeFieldInfo[$describeFieldInfo['name']] = $describeFieldInfo;
			}
		}
	}

	protected function cachedDescribeInfo() {
		return $this->_cachedDescribeInfo;
	}

	protected function cachedDescribeFieldInfo($fieldname) {
		if ($this->_cachedDescribeFieldInfo !== false) {
			if (isset($this->_cachedDescribeFieldInfo[$fieldname])) {
				return $this->_cachedDescribeFieldInfo[$fieldname];
			}
		}
		return false;
	}

	protected function cachedEntityFieldnames($module) {
		$describeInfo = $this->cachedDescribeInfo();
		$labelFields = $describeInfo['labelFields'];
		switch ($module) {
			case 'HelpDesk':
				$labelFields = 'ticket_title';
				break;
			case 'Documents':
				$labelFields = 'notes_title';
				break;
		}
		return explode(',', $labelFields);
	}

	protected function isTemplateRecordRequest(Mobile_API_Request $request) {
		$recordid = $request->get('record');
		return (preg_match("/([0-9]+)x0/", $recordid));
	}

	protected function processRetrieve(Mobile_API_Request $request) {
		$recordid = $request->get('record');

		// Create a template record for use 
		if ($this->isTemplateRecordRequest($request)) {
			$current_user = $this->getActiveUser();

			$module = $this->detectModuleName($recordid);
			$describeInfo = vtws_describe($module, $current_user);
			Mobile_WS_Utils::fixDescribeFieldInfo($module, $describeInfo);

			$this->cacheDescribeInfo($describeInfo);

			$templateRecord = array();
			foreach ($describeInfo['fields'] as $describeField) {
				$templateFieldValue = '';
				if (isset($describeField['type']) && isset($describeField['type']['defaultValue'])) {
					$templateFieldValue = $describeField['type']['defaultValue'];
				} else if (isset($describeField['default'])) {
					$templateFieldValue = $describeField['default'];
				}
				$templateRecord[$describeField['name']] = $templateFieldValue;
			}
			if (isset($templateRecord['assigned_user_id'])) {
				$templateRecord['assigned_user_id'] = sprintf("%sx%s", Mobile_WS_Utils::getEntityModuleWSId('Users'), $current_user->id);
			}
			// Reset the record id
			$templateRecord['id'] = $recordid;

			return $templateRecord;
		}

		// Or else delgate the action to parent
		return parent::processRetrieve($request);
	}

	function process(Mobile_API_Request $request) {
		global $isControlFromMobileApi;
		$isControlFromMobileApi = true;
		$module = $request->get('module');
		if ($module == 'ServiceReports' || $module == 'RecommissioningReports') {
			vglobal('IGMODULE', $module);
			vglobal('VIEWABLEFIELDSLINE', $this->geAllowedFieldsInLineItem($module, 'Shortages Or Damages'));
		}
		if ($module == 'ServiceOrders' || $module == 'SalesOrder' || $module == 'FailedParts' || $module == 'ReturnSaleOrders') {
			vglobal('IGMODULE', $module);
			vglobal('VIEWABLEFIELDSLINE', $this->geAllowedFieldsInLineItem($module, 'Item Details'));
		}
		$response = parent::process($request);
		return $this->processWithGrouping($request, $response);
	}

	protected function processWithGrouping(Mobile_API_Request $request, $response) {
		$isTemplateRecord = $this->isTemplateRecordRequest($request);
		$result = $response->getResult();
		$resultRecord = $result['record'];
		$module = $this->detectModuleName($resultRecord['id']);
		// if ($module == 'HelpDesk') {
		// 	$modifiedRecord = $this->transformRecordWithOutGrouping($resultRecord, $module, $isTemplateRecord);
		// } else 
		if ($module == 'ServiceReports' || $module == 'RecommissioningReports') {
			$modifiedRecord = $this->transformRecordWithOutGroupingServiceReport($resultRecord, $module, $isTemplateRecord);
		} else {
			$modifiedRecord = $this->transformRecordWithOutGroupingNonDependent($resultRecord, $module, $isTemplateRecord, $resultRecord['id']);
		}
		$modifiedRecord['CurrentRecordId'] = $resultRecord['id'];
		if ($module == 'HelpDesk') {
			$recordId = explode("x", $resultRecord['id']);
			// $dataArray = IGalreadyReportGenerated($recordId[1]);
			// if ($dataArray['reportedGenerated'] == true) {
			// 	$generatedRepoId = $dataArray['generatedServiceRepId'];
			// 	$generatedRepoId = explode("x", $generatedRepoId);
			// 	$generatedRepoId = $generatedRepoId[1];
			$dataTableVal = getSingleColumnValue(array(
				'table' => 'vtiger_inventorychargesrel',
				'columnId' => 'recordid',
				'idValue' => $recordId[1],
				'expectedColValue' => 'charges'
			));

			$dataTableValTaxInfo = getSingleColumnValue(array(
				'table' => 'vtiger_inventoryproductrel',
				'columnId' => 'id',
				'idValue' => $recordId[1],
				'expectedColValue' => 'tax1'
			));

			// 	$dataArrayRR = [];
			// 	if ($dataArray['is_recommisionreport'] == '1') {
			// 		$dataArrayRR = IGalreadyRecomisioningReportGenerated($recordId[1]);
			// 	}
			// 	$response->setResult(array(
			// 		'record' => $modifiedRecord, 'isServiceReportGenerated' => true,
			// 		'generatedServiceRepId' => $dataArray['generatedServiceRepId'],
			// 		'is_submitted' => $dataTableVal[0]['is_submitted'],
			// 		'is_recommisionreport' => $dataArray['is_recommisionreport'],
			// 		'generatedRRRepId' => $dataArrayRR['generatedRRRepId']
			// 	));
			// } else {
			$charges = json_decode(decode_html($dataTableVal[0]['charges']));
			$inArrayFormat = [];
			foreach ($charges as $charge) {
				array_push($inArrayFormat, $charge);
			}
			$modifiedRecord['charges'] = $inArrayFormat;
			$modifiedRecord['tax1'] = $dataTableValTaxInfo[0]['tax1'];
			$response->setResult(array(
				'record' => $modifiedRecord,
			));
			// }
		} else if ($module == 'ServiceReports' || $module == 'RecommissioningReports') {
			$recordId = explode("x", $modifiedRecord['id_ticket_id']);
			$dataArray = IGalreadyServiceOrderGenerated($recordId[1]);

			$dataArr = getSingleColumnValue(array(
				'table' => 'vtiger_troubletickets',
				'columnId' => 'ticketid',
				'idValue' => $recordId[1],
				'expectedColValue' => 'external_app_num'
			));
			$modifiedRecord['external_app_num'] = $dataArr[0]['external_app_num'];

			if ($dataArray['orderGenerated'] == true) {
				$response->setResult(array(
					'record' => $modifiedRecord, 'orderGenerated' => true,
					'generatedServiceOrderId' => $dataArray['generatedServiceOrderId']
				));
			} else {
				$response->setResult(array('record' => $modifiedRecord, 'orderGenerated' => false));
			}
		} else if ($module == 'FailedParts') {
			//add values from service request into failed parts details api purvesh
			$recordId = explode("x", $resultRecord['id']);
			$dataArray = GetPOFields($recordId[1]);
			$Date = explode(" ", $dataArray['createdtime']);
			$pono = str_replace(' ', '', $dataArray['external_app_num']);

			$modifiedRecord['po'] = $pono;
			$modifiedRecord['podate'] = $Date[0];
			$response->setResult(array('record' => $modifiedRecord));
		} else {
			$response->setResult(array('record' => $modifiedRecord));
		}
		$response->setApiSucessMessage('Successfully Fetched Data');
		return $response;
	}

	protected function transformRecordWithOutGrouping($resultRecord, $module, $isTemplateRecord = false) {
		global $site_URL_NonHttp;
		$current_user = $this->getActiveUser();
		$moduleFieldGroups = Mobile_WS_Utils::gatherModuleFieldGroupInfo($module);
		$fields = array();
		$tiketType = $resultRecord['ticket_type'];
		$purposeValue = $resultRecord['purpose'];
		$dependecyFieldList = getFieldsOfCategoryGeneralised($tiketType, $purposeValue);
		foreach ($moduleFieldGroups as $blocklabel => $fieldgroups) {
			foreach ($fieldgroups as $fieldname => $fieldinfo) {
				if (isset($resultRecord[$fieldname]) && in_array($fieldname, $dependecyFieldList)) {
					$field = array(
						'name'  => $fieldname,
						'value' => $resultRecord[$fieldname],
						'label' => $fieldinfo['label'],
						'uitype' => $fieldinfo['uitype']
					);
					if ($isTemplateRecord) {
						$describeFieldInfo = $this->cachedDescribeFieldInfo($fieldname);
						if ($describeFieldInfo) {
							foreach ($describeFieldInfo as $k => $v) {
								if (isset($field[$k])) continue;
								$field[$k] = $v;
							}
						}
					}
					if ($field['uitype'] == '53') {
						$field['type']['defaultValue'] = array('value' => "19x{$current_user->id}", 'label' => $current_user->column_fields['last_name']);
					} else if ($field['uitype'] == "5") {
						$field['value'] = Vtiger_Date_UIType::getDisplayDateValue($field['value']);
					} else if ($field['uitype'] == "33") {
						$field['value'] = str_replace('|##|', ',', $field['value']);
					} else if ($field['uitype'] == '117') {
						$field['type']['defaultValue'] = $field['value'];
					} else if ($field['name'] == 'terms_conditions' && in_array($module, array('Quotes', 'Invoice', 'SalesOrder', 'PurchaseOrder'))) {
						$field['type']['defaultValue'] = $field['value'];
					} else if ($field['name'] == 'visibility' && in_array($module, array('Calendar', 'Events'))) {
						$field['type']['defaultValue'] = $field['value'];
					} else if ($field['type']['name'] != 'reference') {
						$field['type']['defaultValue'] = $field['default'];
					}
					if ($field['uitype'] == '10' || $field['uitype'] == '52' || $field['uitype'] == '53') {
						$fields[$field['name']] = $field['value']['label'];
						$fields[$field['name'] . '_id'] = $field['value']['value'];
					} else {
						$fields[$field['name']] = $field['value'];
					}
					if ($field['uitype'] == '69') {
						$recordId = explode('x', $resultRecord['id']);
						$recordId = $recordId[1];
						$attachments = [];
						$imageDetails = getImageDetailsInUtils($recordId);
						foreach ($imageDetails as $imageDetail) {
							$attachment = [];
							$attachment['urlpath'] = $site_URL_NonHttp . $imageDetail['url'];
							$attachment['loadimage'] = '';
							array_push($attachments, $attachment);
						}
						$fields[$field['name']] = $attachments;
					}
				}
			}
		}
		return $fields;
	}

	protected function transformRecordWithOutGroupingServiceReport($resultRecord, $module, $isTemplateRecord = false) {
		global $site_URL_NonHttp;
		$current_user = $this->getActiveUser();
		$moduleFieldGroups = Mobile_WS_Utils::gatherModuleFieldGroupInfo($module);
		$fields = array();
		$tiketType = $resultRecord['sr_ticket_type'];
		$purposeValue = $resultRecord['tck_det_purpose'];
		$dependecyFieldList = getFieldsOfCategoryGeneralisedServiceReport($tiketType, $purposeValue);
		foreach ($moduleFieldGroups as $blocklabel => $fieldgroups) {
			foreach ($fieldgroups as $fieldname => $fieldinfo) {
				if (isset($resultRecord[$fieldname]) && in_array($fieldname, $dependecyFieldList)) {
					$field = array(
						'name'  => $fieldname,
						'value' => $resultRecord[$fieldname],
						'label' => $fieldinfo['label'],
						'uitype' => $fieldinfo['uitype']
					);
					if ($field['uitype'] == '53') {
						$field['type']['defaultValue'] = array('value' => "19x{$current_user->id}", 'label' => $current_user->column_fields['last_name']);
					} else if ($field['uitype'] == "5") {
						$field['value'] = Vtiger_Date_UIType::getDisplayDateValue($field['value']);
					} else if ($field['uitype'] == "33") {
						$field['value'] = str_replace('|##|', ',', $field['value']);
					} else if ($field['uitype'] == '117') {
						$field['type']['defaultValue'] = $field['value'];
					} else if ($field['name'] == 'terms_conditions' && in_array($module, array('Quotes', 'Invoice', 'SalesOrder', 'PurchaseOrder'))) {
						$field['type']['defaultValue'] = $field['value'];
					} else if ($field['name'] == 'visibility' && in_array($module, array('Calendar', 'Events'))) {
						$field['type']['defaultValue'] = $field['value'];
					} else if ($field['type']['name'] != 'reference') {
						$field['type']['defaultValue'] = $field['default'];
					}
					if ($field['uitype'] == '10' || $field['uitype'] == '52' || $field['uitype'] == '53') {
						$fields[$field['name']] = $field['value']['value'];
						$fields[$field['name'] . '_Label'] = $field['value']['label'];
						$fields['id_' . $field['name']] = $field['value']['value'];
					} else {
						$fields[$field['name']] = $field['value'];
					}
					if ($field['uitype'] == '69') {
						$recordId = explode('x', $resultRecord['id']);
						$recordId = $recordId[1];
						$attachments = [];
						$imageDetails = getImageDetailsInUtilsServiceReports($recordId, $field['name']);
						foreach ($imageDetails as $imageDetail) {
							$attachment = [];
							$attachment['urlpath'] = $site_URL_NonHttp . $imageDetail['url'];
							$attachment['loadimage'] = '';
							array_push($attachments, $attachment);
						}
						$fields[$field['name']] = $attachments;
					}
				}
			}
		}
		if (isset($resultRecord['LineItems'])) {
			$fields['LineItems'] = $resultRecord['LineItems'];
		}
		if (isset($resultRecord['LineItemsAnother'])) {
			$fields['LineItemsAnother'] = $resultRecord['LineItemsAnother'];
		}
		if ($tiketType == 'PRE-DELIVERY' || $tiketType == 'ERECTION AND COMMISSIONING') {
			$IgRecord = $resultRecord['id'];
			$recordId = explode("x", $IgRecord);
			$recordId = $recordId[1];
			vglobal('VIEWABLEFIELDSLINE', $this->geAllowedFieldsInLineItem($module, 'Major Aggregates Sl.No.'));
			$fields['LineItemsMASN'] = IGgetAllLineItemsOtherForParent($recordId);
		}
		if ($module == 'RecommissioningReports') {
			$fields['is_recommisionreport'] = '1';
		}
		return $fields;
	}

	protected function transformRecordWithOutGroupingNonDependent($resultRecord, $module, $isTemplateRecord = false, $IgRecord) {
		global $site_URL_NonHttp;
		$current_user = $this->getActiveUser();
		$moduleFieldGroups = Mobile_WS_Utils::gatherModuleFieldGroupInfo($module);
		$fields = array();
		foreach ($moduleFieldGroups as $blocklabel => $fieldgroups) {
			foreach ($fieldgroups as $fieldname => $fieldinfo) {
				if (isset($resultRecord[$fieldname])) {
					$field = array(
						'name'  => $fieldname,
						'value' => $resultRecord[$fieldname],
						'label' => $fieldinfo['label'],
						'uitype' => $fieldinfo['uitype']
					);

					if ($isTemplateRecord) {
						$describeFieldInfo = $this->cachedDescribeFieldInfo($fieldname);
						if ($describeFieldInfo) {
							foreach ($describeFieldInfo as $k => $v) {
								if (isset($field[$k])) continue;
								$field[$k] = $v;
							}
						}
					}
					if ($field['uitype'] == '53') {
						$field['type']['defaultValue'] = array('value' => "19x{$current_user->id}", 'label' => $current_user->column_fields['last_name']);
					} else if ($field['uitype'] == "5") {
						$field['value'] = Vtiger_Date_UIType::getDisplayDateValue($field['value']);
					} else if ($field['uitype'] == "33") {
						$field['value'] = str_replace('|##|', ',', $field['value']);
					} else if ($field['uitype'] == '117') {
						$field['type']['defaultValue'] = $field['value'];
					} else if ($field['name'] == 'terms_conditions' && in_array($module, array('Quotes', 'Invoice', 'SalesOrder', 'PurchaseOrder'))) {
						$field['type']['defaultValue'] = $field['value'];
					} else if ($field['name'] == 'visibility' && in_array($module, array('Calendar', 'Events'))) {
						$field['type']['defaultValue'] = $field['value'];
					} else if ($field['type']['name'] != 'reference') {
						$field['type']['defaultValue'] = $field['default'];
					}
					if ($field['uitype'] == '10' || $field['uitype'] == '52' || $field['uitype'] == '53') {
						$fields[$field['name'] . 'Label'] = $field['value']['label'];
						$fields[$field['name']] = $field['value']['value'];
					} else {
						$fields[$field['name']] = $field['value'];
					}
					if ($field['uitype'] == '69') {
						$recordId = explode('x', $resultRecord['id']);
						$recordId = $recordId[1];
						$attachments = [];
						$imageDetails = getImageDetailsInUtils($recordId);
						foreach ($imageDetails as $imageDetail) {
							$attachment = [];
							$attachment['urlpath'] = $site_URL_NonHttp . $imageDetail['url'];
							$attachment['loadimage'] = '';
							$attachment['attachmentid'] = $imageDetail['id'];
							array_push($attachments, $attachment);
						}
						$fields[$field['name']] = $attachments;
					} else if ($field['uitype'] == '72') {
						$fields[$field['name']] = CurrencyField::convertToUserFormat($field['value'], null, true);
					}
				}
			}
		}

		if ($module == 'SalesOrder') {
			if (isset($resultRecord['LineItems'])) {
				$lineItems = $resultRecord['LineItems'];
				$moduleWSID = Mobile_WS_Utils::getEntityModuleWSId('ReturnSaleOrders');
				vglobal('VIEWABLEFIELDSLINE', $this->geAllowedFieldsInLineItem('ReturnSaleOrders', 'Item Details'));
				foreach ($lineItems as $key => $lineItem) {
					$lineitemId = $lineItem['lineitem_id'];
					$records = getAllRSOWithParentId($lineitemId);
					$fullRecordDetail = [];
					foreach ($records as $record) {
						$record = vtws_retrieve($moduleWSID . 'x' . $record, $current_user);
						unset($record['LineItems_FinalDetails']);
						foreach ($record['LineItems'] as $key1 => $lineItemRSO) {
							array_push($fullRecordDetail, $lineItemRSO);
						}
					}
					$lineItems[$key]['RSODetails'] = $fullRecordDetail;
				}
				$fields['LineItems'] = $lineItems;
			}
		} else if ($module == 'Equipment') {
			$recordId = explode("x", $IgRecord);
			$relatedLines = getAllLineItemsForEquipment($recordId[1], 'Equipment');
			$relatedProductsAnother = [];
			$noOfYearsOfContract = (int) $resultRecord['total_year_cont'] + 1;
			for ($i = 1; $i < $noOfYearsOfContract; $i++) {
				array_push($relatedProductsAnother, array(
					'daadcp_contra_lable'  => "$i year Contract",
					'daadcp_avail_sl_no' => $i,
					'daadcp_avail_percent' => empty($relatedLines[$i - 1]['daadcp_avail_percent']) ? 0 : $relatedLines[$i - 1]['daadcp_avail_percent'],
					'daadcp_avail_mon_percent' => empty($relatedLines[$i - 1]['daadcp_avail_mon_percent']) ? 0 : $relatedLines[$i - 1]['daadcp_avail_mon_percent']
				));
			}
			$fields['ContractsAvalibiltyValues'] = $relatedProductsAnother;
		} else {
			if (isset($resultRecord['LineItems'])) {
				$lineItems = $resultRecord['LineItems'];
				$fields['LineItems'] = $lineItems;
			}
		}
		return $fields;
	}
}
