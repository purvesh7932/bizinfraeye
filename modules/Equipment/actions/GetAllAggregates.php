<?php

class Equipment_GetAllAggregates_Action extends Vtiger_IndexAjax_View {
	public function requiresPermission(\Vtiger_Request $request) {
		$permissions = parent::requiresPermission($request);
		$permissions[] = array('module_parameter' => 'module', 'action' => 'Export');
		return $permissions;
	}

	public function process(Vtiger_Request $request) {
		global $adb;
		$response = new Vtiger_Response();
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		if ($currentUserModel->isAdminUser()) {
			ini_set('max_execution_time', 0);
			require_once('modules/Equipment/Equipment.php');
			include_once('include/utils/GeneralUtils.php');
			$url = getExternalAppURL('getAllEquipmentAggregates');
			$itiration = $request->get('indexForLink');
			$fireRequest = true;
			$requestUrl = $url . '&INDEX=' . $itiration;
			$xml = file_get_contents($requestUrl);
			$xml = json_decode($xml);
			if (empty($xml)) {
				$fireRequest = false;
			}
			foreach ($xml as $key => $value) {
				$sapRefNum = trim($value->{'EQUNR'});
				$sql = 'select equipmentid from vtiger_equipment where equipment_sl_no = ?';
				$sqlResult = $adb->pquery($sql, array($sapRefNum));
				$num_rows = $adb->num_rows($sqlResult);
				if ($num_rows > 0) {
					$dataRow = $adb->fetchByAssoc($sqlResult, 0);
					$recordModel = Vtiger_Record_Model::getInstanceById($dataRow['equipmentid'], 'Equipment');
					$recordModel->set('mode', 'edit');
					$recordModel->set('equi_desc', trim($value->{'EQKTX'}));
					$recordModel->set('equip_ag_serial_no', trim($value->{'SERGE'}));
					$recordModel->set('equip_ag_part_no', trim($value->{'MAPAR'}));
					$recordModel->set('equip_ag_manu_fact', trim($value->{'HERST'}));
					$recordModel->set('equip_model', trim($value->{'TYPBZ'}));

					$sql1 = "select equipmentid from vtiger_equipment where equipment_sl_no = ? ";
					$result = $adb->pquery($sql1, array(trim($value->{'HEQUI'})));
					$dataRow1 = $adb->fetchByAssoc($result, 0);
					if (empty($dataRow1['equipmentid'])) {
						$recordModel->set('agg_equipment_id', '');
					} else {
						$recordModel->set('agg_equipment_id', $dataRow1['equipmentid']);
					}
					$recordModel->save();
				}
			}
			$response = new Vtiger_Response();
			$response->setResult(array('success' => true, 'hasNext' => $fireRequest));
			$response->emit();
		} else {
			$response->setError("External App Sync Not Allowed");
			$response->emit();
		}
	}
}
