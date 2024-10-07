<?php

class Portal_relatedRecordsWithGrouping_API extends Portal_Default_API {

	function process(Portal_Request $request) {
		global $current_user, $adb, $currentModule;
		$response = new Portal_Response();
		$record = $request->get('record');
		$relatedmodule = $request->get('relatedmodule');
		$currentPage = $request->get('page', 0);

		// Input validation
		if (empty($record)) {
			$response->setError(1001, 'Record id is empty');
			return $response;
		}
		$recordid = vtws_getIdComponents($record);
		$recordid = $recordid[1];

		$module = Portal_Default_API::detectModulenameFromRecordId($record);

		// Initialize global variable
		$currentModule = $module;

		$functionHandler = Portal_Default_API::getRelatedFunctionHandler($module, $relatedmodule);
		$relatedRecords = array();
		if ($functionHandler) {
			$sourceFocus = CRMEntity::getInstance($module);
			$relationResult = call_user_func_array(array($sourceFocus, $functionHandler), array($recordid, getTabid($module), getTabid($relatedmodule), ''));
			$query = $relationResult['query'];

			// $querySEtype = "vtiger_crmentity.setype as setype";
			// if ($relatedmodule == 'Calendar') {
			// 	$querySEtype = "vtiger_activity.activitytype as setype";
			// }

			// $query = sprintf("SELECT vtiger_crmentity.crmid, $querySEtype %s", substr($query, stripos($query, 'FROM')));
			$queryResult = $adb->pquery($query, array());

			// Gather resolved record id's
			$numOfRows = $adb->num_rows($queryResult);
			if ($numOfRows == 0) {
				$ResponseObject['relatedRecords'] = [];
				$response->setResult($ResponseObject);
				$response->setApiSucessMessage('No Related Records Found');
				return $response;
			}

			$actveCols = [];
			$relatedmoduleColFields = getColumnFields($relatedmodule);
			foreach ($relatedmoduleColFields as $key => $val) {
				array_push($actveCols, $key);
			}

			while ($row = $adb->fetch_array($queryResult)) {
				$modRow = [];
				$row['assigned_user_id'] = $row['smownerid'];
				foreach ($row as $colKey => $val) {
					$modRow[$colKey] = $row[$colKey];
					if ($colKey == 'equipment_id') {
						$modRow[$colKey] = '37x' . $row[$colKey];
					} else if ($colKey == 'crmid') {
						$modRow[$colKey] = '53x' . $row[$colKey];
					} else if ($colKey == 'assigned_user_id') {
						$modRow[$colKey] = '19x' . $row[$colKey];
					}
				}

				$this->resolveRecordValues($modRow);

				$modRow1 = [];
				$modRow1['relatedRecordId'] = $modRow['crmid'];
				foreach ($actveCols as $colKey) {
					$modRow1[$colKey] = $modRow[$colKey];
				}
				$relatedRecords[] = $modRow1;
			}

			// Perform query to get record information with grouping
			// $wsquery = sprintf("SELECT * FROM %s WHERE id IN ('%s');", $relatedmodule, implode("','", $relatedRecords));

			// $newRequest = new Mobile_API_Request();
			// $newRequest->set('module', $relatedmodule);
			// $newRequest->set('query', $wsquery);
			// $newRequest->set('page', ($currentPage - 1));

			// $response = parent::process($newRequest);
			// $records = $response->getResult('records');
			// $recordsWithoutBlock = [];
			// foreach($records['records'] as $record){
			// 	$WithoutBlocks = [];
			// 	foreach($record['blocks'] as $block){
			// 		foreach($block['fields'] as $field){
			// 			$WithoutBlocks[$field['name']] = $field['value'];
			// 		}
			// 	}
			// 	$WithoutBlocks['relatedRecordId'] = $record['id'];
			// 	array_push($recordsWithoutBlock , $WithoutBlocks);
			// }
		}

		if (empty($recordsWithoutBlock)) {
			$recordsWithoutBlock = [];
		}

		$ResponseObject['relatedRecords'] = $relatedRecords;
		$response->setResult($ResponseObject);
		$response->setApiSucessMessage('Successfully Fetched Data');
		return $response;
	}

	static function resolveRecordValues(&$record, $user = null, $ignoreUnsetFields = false) {
		$userTypeFields = array('assigned_user_id', 'creator', 'userid', 'created_user_id', 'modifiedby', 'folderid');

		if (empty($record)) {
			return $record;
		}

		$module = Vtiger_Util_Helper::detectModulenameFromRecordId($record['crmid']);
		$fieldnamesToResolve = Vtiger_Util_Helper::detectFieldnamesToResolve($module);

		if (!empty($fieldnamesToResolve)) {
			foreach ($fieldnamesToResolve as $resolveFieldname) {

				if (isset($record[$resolveFieldname]) && !empty($record[$resolveFieldname])) {
					$fieldvalueid = $record[$resolveFieldname];

					if (in_array($resolveFieldname, $userTypeFields)) {
						$fieldvalue = decode_html(trim(vtws_getName($fieldvalueid, $user)));
					} else {
						$fieldvalue = Vtiger_Util_Helper::fetchRecordLabelForId($fieldvalueid);
					}
					$record[$resolveFieldname] =  $fieldvalue;
				}
			}
		}
		return $record;
	}
}
