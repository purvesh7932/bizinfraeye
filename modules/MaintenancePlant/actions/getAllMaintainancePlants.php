<?php
class MaintenancePlant_getAllMaintainancePlants_Action extends Vtiger_IndexAjax_View {
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
			include_once('include/utils/GeneralUtils.php');
			$url = getExternalAppURL('getAllPlants');
			$xml = file_get_contents($url);
			$xml = json_decode($xml);
			foreach ($xml->{'PLANTS'} as $key => $value) {
				$sapRefNum = trim($value->{'WERKS'});
				$sql = 'select maintenanceplantid from vtiger_maintenanceplant where plant_code = ?';
				$sqlResult = $adb->pquery($sql, array($sapRefNum));
				$num_rows = $adb->num_rows($sqlResult);
				if ($num_rows > 0) {
					$dataRow = $adb->fetchByAssoc($sqlResult, 0);
					$recordModel = Vtiger_Record_Model::getInstanceById($dataRow['maintenanceplantid'], 'MaintenancePlant');
					$recordModel->set('mode', 'edit');
					$plantName = trim($value->{'NAME1'});
					$recordModel->set('plant_name', $plantName);

					// implement plant group sharing
					if (!empty($plantName)) {
						$sql = "select groupid from vtiger_groups where groupname = ? ";
						$result = $adb->pquery($sql, array($plantName));
						$dataRow = $adb->fetchByAssoc($result, 0);
						if (empty($dataRow['groupid'])) {
						} else {
							$recordModel->set('assigned_user_id', $dataRow['groupid']);
						}
					}
					// $recordModel->set('plant_desc', $plantName);
					$recordModel->save();
					$this->checkAndSaveGroup($plantName);
				} else {
					$focus = CRMEntity::getInstance('MaintenancePlant');
					$plantName = trim($value->{'NAME1'});
					$focus->column_fields['plant_name'] = $plantName;
					// $focus->column_fields['plant_desc'] = $plantName;
					$focus->column_fields['plant_code'] = trim($value->{'WERKS'});
					// implement plant group sharing
					if (!empty($plantName)) {
						$sql = "select groupid from vtiger_groups where groupname = ? ";
						$result = $adb->pquery($sql, array($plantName));
						$dataRow = $adb->fetchByAssoc($result, 0);
						if (empty($dataRow['groupid'])) {
						} else {
							$focus->column_fields['assigned_user_id'] = $dataRow['groupid'];
						}
					}
					$focus->save("MaintenancePlant");
					$this->checkAndSaveGroup($plantName);
				}
			}
		} else {
			$response->setError("External App Sync Not Allowed");
			$response->emit();
		}
	}

	public function checkAndSaveGroup($groupName) {
		$recordId = NULL;
		$recordModel = Settings_Groups_Record_Model::getInstanceByName(decode_html($groupName), array($recordId));
		if (empty($recordModel)) {
			$recordModel = new Settings_Groups_Record_Model();
			if ($recordModel) {
				$recordModel->set('groupname', decode_html($groupName));
				$recordModel->set('description', $groupName);
				$recordModel->set('group_members', array('Users:1'));
				$recordModel->save();
			}
		}
	}
}
