<?php

class FunctionalLocations_getAllFunctionalLocations_Action extends Vtiger_IndexAjax_View {
	public function requiresPermission(\Vtiger_Request $request) {
		$permissions = parent::requiresPermission($request);
		$permissions[] = array('module_parameter' => 'module', 'action' => 'Export');
		return $permissions;
	}

	public function process(Vtiger_Request $request) {
		global $adb;
		require_once('modules/FunctionalLocations/FunctionalLocations.php');
		include_once('include/utils/GeneralUtils.php');
		$url = getExternalAppURL('getAllFunctionalLocations');
		$xml = file_get_contents($url);
		$xml = json_decode($xml);
		foreach ($xml as $key => $value) {
			$sapRefNum = trim($value->{'TPLNR'});
			$plantCodes = explode("-",$sapRefNum);
			$plantCode = $plantCodes[0];
			$sql = 'select functionallocationsid from vtiger_functionallocations where functionallocation_name = ?';
			$sqlResult = $adb->pquery($sql, array($sapRefNum));
			$num_rows = $adb->num_rows($sqlResult);
			if ($num_rows > 0) {
				$dataRow = $adb->fetchByAssoc($sqlResult, 0);
				$recordModel = Vtiger_Record_Model::getInstanceById($dataRow['functionallocationsid'], 'FunctionalLocations');
				$recordModel->set('mode', 'edit');
				$recordModel->set('ftl_loc_desc', trim($value->{'PLTXT'}));
				$recordModel->set('assigned_user_id', $this->getAssignedUserOfTheFunctionalLocation($sapRefNum));
				$recordModel->set('plant_code', $plantCode);
				$recordModel->save();
			} else {
				$focus = CRMEntity::getInstance('FunctionalLocations');
				$focus->column_fields['functionallocation_name'] = trim($value->{'TPLNR'});
				$focus->column_fields['ftl_loc_desc'] = trim($value->{'PLTXT'});
				$focus->column_fields['plant_code'] = $plantCode;
				$focus->column_fields['assigned_user_id'] = $this->getAssignedUserOfTheFunctionalLocation($sapRefNum);
				$focus->save("FunctionalLocations");
			}
		}
	}

	public  function getAssignedUserOfTheFunctionalLocation($functionalName) {
		global $adb;
		if (empty($functionalName)) {
			return 1;
		}
		$arr = explode("-", $functionalName);
		$plantCode = $arr[0];
		$sql = "select maintenanceplantid,plant_name from vtiger_maintenanceplant where plant_code = ? ";
		$result = $adb->pquery($sql, array($plantCode));
		$dataRow = $adb->fetchByAssoc($result, 0);
		if (empty($dataRow['maintenanceplantid'])) {
			return 1;
		} else {
			$plantName = $dataRow['plant_name'];
			$sql = "select groupid from vtiger_groups where groupname = ? ";
			$result = $adb->pquery($sql, array($plantName));
			$dataRow = $adb->fetchByAssoc($result, 0);
			if (empty($dataRow['groupid'])) {
				return 1;
			} else {
				return $dataRow['groupid'];
			}
		}
	}
}