<?php
include_once dirname(__FILE__) . '/FetchRecordWithGrouping.php';

include_once 'include/Webservices/Create.php';
include_once 'include/Webservices/Update.php';

class Mobile_WS_SaveRecord extends Mobile_WS_FetchRecordWithGrouping {
	protected $recordValues = false;

	// Avoid retrieve and return the value obtained after Create or Update
	protected function processRetrieve(Mobile_API_Request $request) {
		return $this->recordValues;
	}

	function getJsonParsError() {
		$error = '';
		switch (json_last_error()) {
			case JSON_ERROR_NONE:
				$error = 'No errors';
				break;
			case JSON_ERROR_DEPTH:
				$error = 'Maximum stack depth exceeded';
				break;
			case JSON_ERROR_STATE_MISMATCH:
				$error = 'Underflow or the modes mismatch';
				break;
			case JSON_ERROR_CTRL_CHAR:
				$error = 'Unexpected control character found';
				break;
			case JSON_ERROR_SYNTAX:
				$error = 'Syntax error, malformed JSON';
				break;
			case JSON_ERROR_UTF8:
				$error = 'Malformed UTF-8 characters, possibly incorrectly encoded';
				break;
			default:
				$error = 'Unknown error';
				break;
		}
		return $error;
	}

	function getAllDateFieldsInModule($module){
		$current_user = $this->getActiveUser();
		$dateFields = [];
		$describeInfo = vtws_describe($module, $current_user);
		foreach ($describeInfo['fields'] as $key => $value) {
			if ($value['type']['name'] ==  'date') {
				array_push($dateFields, $value['name']);
			}
		}
		return $dateFields;
	}

	function getEditableFieldsInLineItem($module) {
		$current_user = $this->getActiveUser();
		$moduleFieldGroups = Mobile_WS_Utils::gatherModuleFieldGroupInfo($module);
		$describeInfo = vtws_describe($module, $current_user);
		$fields = array();
		foreach ($moduleFieldGroups as $blocklabel => $fieldgroups) {
			if ($blocklabel == 'Item Details') {
				foreach ($fieldgroups as $fieldname => $fieldinfo) {
					foreach ($describeInfo['fields'] as $key => $value) {
						if ($value['name'] ==  $fieldname) {
							if ($value['editable'] == true) {
								$fields[] = $fieldname;
								break;
							}
						}
					}
				}
				break;
			}
		}
		return $fields;
	}

	function process(Mobile_API_Request $request) {
		global $current_user; // Required for vtws_update API
		$current_user = $this->getActiveUser();

		$module = $request->get('module');
		$recordid = $request->get('record');
		$valuesJSONString =  $request->get('values');

		$values = "";
		if (!empty($valuesJSONString) && is_string($valuesJSONString)) {
			$values = Zend_Json::decode($valuesJSONString);
		} else {
			$values = $valuesJSONString; // Either empty or already decoded.
		}
		if (
			$module == 'FailedParts' || $module == 'ServiceReports'
			|| $module == 'ServiceOrders' || $module == 'StockTransferOrders'
			|| $module == 'ReturnSaleOrders' || $module == 'SalesOrder'
			|| $module == 'RecommissioningReports' || $module == 'HelpDesk'
		) {
			global $iGLineItems;
			$iGLineItems = $values['LineItems'];
			global $moduleNameForLine, $actionFromMobileApis;
			$actionFromMobileApis = true;
			vglobal('NODELETEOFLINEITEMS', true);
			vglobal('EDITABLEFIELDSLINE', $this->getEditableFieldsInLineItem($module));
			$moduleNameForLine = $module;
		}
		if ($module == 'ServiceReports' || $module == 'ServiceOrders' || 
			$module == 'StockTransferOrders' || $module == 'SalesOrder' ||
			$module == 'RecommissioningReports' || $module == 'HelpDesk') {
			vglobal('NODELETEOFLINEITEMS', false);
		}
		$response = new Mobile_API_Response();
		$jsonParseError = json_last_error();
		if (!empty($jsonParseError)) {
			$error = $this->getJsonParsError($jsonParseError);
			$response->setError(1501, $error);
			return $response;
		}
		if (empty($values)) {
			$response->setError(1501, "Values cannot be empty!");
			return $response;
		}
		if (isset($values['assigned_user_id'])) {
			$values['assigned_user_id'] = '19x' . $values['assigned_user_id'];
		}
		try {
			if (vtws_recordExists($recordid)) {
				// Retrieve or Initalize
				if (!empty($recordid) && !$this->isTemplateRecordRequest($request)) {
					$this->recordValues = vtws_retrieve($recordid, $current_user);
				} else {
					$this->recordValues = array();
				}
				$dateFields = $this->getAllDateFieldsInModule($module);
				// Set the modified values
				foreach ($values as $name => $value) {
					$this->recordValues[$name] = $value;
					if (in_array($name, $dateFields)) {
						if (strpos($value, "/") !== false) {
							$date = new DateTimeField($value);
							$this->recordValues[$name] = $date::__convertToDBFormat($value, 'dd/mm/yyyy');
						}
					}
				}
				// Update or Create
				if (isset($this->recordValues['id'])) {
					global $IGRecordMode;
					$IGRecordMode = 'Edit';

					if ($module == 'ServiceReports' || $module == 'RecommissioningReports') {
						$idOfRecord = explode('x', $this->recordValues['id']);
						$idOfRecord = $idOfRecord[1];
						include_once('include/utils/GeneralUtils.php');
						$dataArr = getSingleColumnValue(array(
							'table' => 'vtiger_servicereports',
							'columnId' => 'servicereportsid',
							'idValue' => $idOfRecord,
							'expectedColValue' => 'is_submitted'
						));
						$isSubmitted = $dataArr[0]['is_submitted'];

						$ticketId = explode('x', $this->recordValues['ticket_id']);
						$ticketId = $ticketId[1];
						$dataArr = getSingleColumnValue(array(
							'table' => 'vtiger_troubletickets',
							'columnId' => 'ticketid',
							'idValue' => $ticketId,
							'expectedColValue' => 'external_app_num'
						));
						$externalNumApp = $dataArr[0]['external_app_num'];
						if ($isSubmitted == '1' && !empty($externalNumApp)) {
							$response->setError(1501, 'Service Report Is Already Submitted');
							return $response;
						}
					}
					$this->recordValues = vtws_update($this->recordValues, $current_user);
					if ($module == 'ServiceReports' || $module == 'RecommissioningReports') {
						saveInventoryProductDetailsFromMobile(
							$values['LineItemsAnother'],
							$module,
							$this->recordValues['id'],
							$this->recordValues
						);
					}
					$reportType = $this->recordValues['sr_ticket_type'];
					if (($module == 'ServiceReports' || $module == 'RecommissioningReports') && ($reportType == 'PRE-DELIVERY' || $reportType == 'ERECTION AND COMMISSIONING')) {
						saveInventoryProductDetailsFromMobileMASN($values['LineItemsMASN'], $module, $this->recordValues['id']);
					}
					if ($module == 'Equipment') {
						saveContractsAvalibiltyValues($values['ContractsAvalibiltyValues'], $module, $this->recordValues['id']);
					}
				} else {
					if ($module == 'Calendar') {
						if (!empty($this->recordValues['eventstatus']) && $this->recordValues['activitytype'] != 'Task') {
							$module = 'Events';
						}
					}
					if ($module == 'HelpDesk') {
						$this->recordValues['ticketstatus'] = 'Open';
					}
					$this->recordValues['source'] = 'MOBILE';
					$this->recordValues = vtws_create($module, $this->recordValues, $current_user);

					if ($module == 'ServiceReports' || $module == 'RecommissioningReports') {
						saveInventoryProductDetailsFromMobile($values['LineItemsAnother'], $module, $this->recordValues['id'], $this->recordValues);
					}
					$reportType = $this->recordValues['sr_ticket_type'];
					if (($module == 'ServiceReports' || $module == 'RecommissioningReports') && ($reportType == 'PRE-DELIVERY' || $reportType == 'ERECTION AND COMMISSIONING')) {
						saveInventoryProductDetailsFromMobileMASN($values['LineItemsMASN'], $module, $this->recordValues['id']);
					}
					if ($module == 'ServiceReports'){
						global $adb;
						$createdIdTickId = explode('x', $this->recordValues['ticket_id']);
						$createdIdTickId = $createdIdTickId[1];
						$query = "UPDATE vtiger_troubletickets SET status = ? WHERE ticketid=?";
						$adb->pquery($query, array('In Progress', $createdIdTickId ));
					}
				}
				// Update the record id
				$request->set('record', $this->recordValues['id']);
				// Gather response with full details
				// $response = parent::process($request);
				$data = [];
				$data['id'] = $this->recordValues['id'];
				if (empty($recordid)) {
					$message = 'Successfully Created The Record';
				} else {
					$message = 'Successfully Updated The Record';
				}
				$data['message'] = $message;
				$date = new DateTime();
				$data['timestamp'] = $date->getTimestamp();
				$data['usercreatedid'] = $current_user->id;
				$data['useruniqeid'] = $current_user->id;
				global $hasSAPErrors, $ErrorMessage, $SAPDetailError;
				if (
					$module == 'ServiceReports' || $module == 'ServiceOrders'
					|| $module == 'StockTransferOrders' || $module == 'SalesOrder'
					|| $module == 'RecommissioningReports'
				) {
					$data['hasSAPSyncErrors'] = $hasSAPErrors;
					if ($hasSAPErrors == true) {
						$response->setError(1501, $SAPDetailError);
						$response->setErrorCreatedId($data['id']);
						return $response;
						// $data['message'] = $ErrorMessage;
						// $data['DetailedErrorMessage'] = $SAPDetailError;
					}
				}
				if ($module == 'HelpDesk') {
					$this->CalculateAmountInWords($this->recordValues);
				}
				$response->setApiSucessMessage($message);
				$response->setResult($data);
				return $response;
			} else {
				$response->setError("RECORD_NOT_FOUND", "Record does not exist");
				return $response;
			}
		} catch (DuplicateException $e) {
			$response->setError($e->getCode(), $e->getMessage());
		} catch (Exception $e) {
			$response->setError($e->getCode(), $e->getMessage());
		}
		return $response;
	}

	function CalculateAmountInWords($entityData) {
		if ($_REQUEST['action'] == 'HelpDeskAjax' || $_REQUEST['ajxaction'] == 'DETAILVIEW' ||
				$_REQUEST['action'] == 'MassEditSave' || $_REQUEST['action'] == 'ProcessDuplicates' || $_REQUEST['action'] == 'SaveAjax') {
			return;
		}
		global $adb;
		$data = $entityData;
		$recordIdWithwebserviceFormat = $data['id'];
		$recordId = explode('x', $recordIdWithwebserviceFormat);
		$recordId = $recordId[1];
		$num = (float) $data['hdnGrandTotal'];
		$amountInWords = getIndianCurrency($num);
	
		$updateSO = 'update vtiger_troubletickets set amount_in_words = ? where ticketid = ?';
		$adb->pquery($updateSO, array($amountInWords, $recordId));
	}
	
	function getIndianCurrency($number) {
		$decimal = round($number - ($no = floor($number)), 2) * 100;
		$hundred = null;
		$digits_length = strlen($no);
		$i = 0;
		$str = array();
		$words = array(0 => '', 1 => 'One', 2 => 'Two',
			3 => 'Three', 4 => 'Four', 5 => 'Five', 6 => 'Six',
			7 => 'Seven', 8 => 'Eight', 9 => 'Nine',
			10 => 'Ten', 11 => 'Eleven', 12 => 'Twelve',
			13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen',
			16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen',
			19 => 'Nineteen', 20 => 'Twenty', 30 => 'Thirty',
			40 => 'Forty', 50 => 'Fifty', 60 => 'Sixty',
			70 => 'Seventy', 80 => 'Eighty', 90 => 'Ninety');
		$digits = array('', 'Hundred', 'Thousand', 'Lakh', 'Crore');
		while ($i < $digits_length) {
			$divider = ($i == 2) ? 10 : 100;
			$number = floor($no % $divider);
			$no = floor($no / $divider);
			$i += $divider == 10 ? 1 : 2;
			if ($number) {
				$plural = (($counter = count($str)) && $number > 9) ? 's' : null;
				$hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
				$str [] = ($number < 21) ? $words[$number] . ' ' . $digits[$counter] . $plural . ' ' . $hundred : $words[floor($number / 10) * 10] . ' ' . $words[$number % 10] . ' ' . $digits[$counter] . $plural . ' ' . $hundred;
			} else
				$str[] = null;
		}
		$Rupees = implode('', array_reverse($str));
		$paise = ($decimal > 0) ? " " . ($words[$decimal / 10] . " " . $words[$decimal % 10]) . ' Paise' : '';
		//    return ($Rupees ? $Rupees . 'Rupees ' : '') .'AND'. $paise;
		return ($Rupees ? 'Rupees ' . $Rupees . ' Only' : '');
	}
}
