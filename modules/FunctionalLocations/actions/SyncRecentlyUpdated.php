<?php

class FunctionalLocations_SyncRecentlyUpdated_Action extends Vtiger_IndexAjax_View {
	public function requiresPermission(\Vtiger_Request $request) {
		$permissions = parent::requiresPermission($request);
		$permissions[] = array('module_parameter' => 'module', 'action' => 'Export');
		return $permissions;
	}

	public function process(Vtiger_Request $request) {
		global $adb;
		require_once('modules/FunctionalLocations/FunctionalLocations.php');
		include_once('include/utils/GeneralUtils.php');
		$url = getExternalAppURL('getAllFunctionalLocationsUpdated');
		$xml = file_get_contents($url);
		$xml = json_decode($xml);
		foreach ($xml as $key => $value) {
			$sapRefNum = trim($value->{'TPLNR'});
			$sql = 'select functionallocationsid from vtiger_functionallocations where functionallocation_name = ?';
			$sqlResult = $adb->pquery($sql, array($sapRefNum));
			$num_rows = $adb->num_rows($sqlResult);
			if ($num_rows > 0) {
				$dataRow = $adb->fetchByAssoc($sqlResult, 0);
				$recordModel = Vtiger_Record_Model::getInstanceById($dataRow['functionallocationsid'], 'FunctionalLocations');
				$recordModel->set('mode', 'edit');
				$recordModel->set('ftl_loc_desc', trim($value->{'PLTXT'}));
				$recordModel->save();
			} else {
				$focus = CRMEntity::getInstance('FunctionalLocations');
				$focus->column_fields['functionallocation_name'] = trim($value->{'TPLNR'});
				$focus->column_fields['ftl_loc_desc'] = trim($value->{'PLTXT'});
				$focus->column_fields['assigned_user_id'] = 1;
				$focus->save("FunctionalLocations");
			}
		}
	}
}
