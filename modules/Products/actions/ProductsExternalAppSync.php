<?php
class Products_ProductsExternalAppSync_Action extends Vtiger_IndexAjax_View {
	public function requiresPermission(\Vtiger_Request $request) {
		$permissions = parent::requiresPermission($request);
		$permissions[] = array('module_parameter' => 'module', 'action' => 'Export');
		return $permissions;
	}

	public function process(Vtiger_Request $request) {
		$response = new Vtiger_Response();
		ini_set('max_execution_time', 0);
		ini_set('default_socket_timeout', 9000);
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		if ($currentUserModel->isAdminUser()) {
			global $adb;
			include_once('include/utils/GeneralUtils.php');
			$url = getExternalAppURL('getAllMaterialMaster');
			$itiration = $request->get('indexForLink');

			$fireRequest = true;
			$requestUrl = $url . '&INDEX=' . $itiration;
			$xml = file_get_contents($requestUrl);
			$undecoded = $xml;
			$xml = json_decode($xml);
			if (empty($xml)) {
				$fireRequest = false;
			}
			foreach ($xml as $key => $value) {
				$sapRefNum = trim($value->{'MATNR'});
				$maintPlant = trim($value->{'WERKS'});
				$sql = 'select productid from vtiger_products where productname = ? and maintain_plant = ?';
				$sqlResult = $adb->pquery($sql, array($sapRefNum, $maintPlant));
				$num_rows = $adb->num_rows($sqlResult);
				if ($num_rows > 0) {
					$dataRow = $adb->fetchByAssoc($sqlResult, 0);
					$recordModel = Vtiger_Record_Model::getInstanceById($dataRow['productid'], 'Products');
					$recordModel->set('mode', 'edit');
					$recordModel->set('description', trim($value->{'MAKTX'}));
					$recordModel->set('qtyinstock', $value->{'LABST'});
					$recordModel->set('store_code', trim($value->{'LGORT'}));
					$sql = "select maintenanceplantid,plant_name from vtiger_maintenanceplant where plant_code = ? ";
					$result = $adb->pquery($sql, array(trim($value->{'WERKS'})));
					$dataRow = $adb->fetchByAssoc($result, 0);
					if (empty($dataRow['maintenanceplantid'])) {
						$recordModel->set('plant_name', '');
					} else {
						$recordModel->set('plant_name', $dataRow['maintenanceplantid']);
						$plantName = $dataRow['plant_name'];
						$sql = "select groupid from vtiger_groups where groupname = ? ";
						$result = $adb->pquery($sql, array($plantName));
						$dataRow = $adb->fetchByAssoc($result, 0);
						if (empty($dataRow['groupid'])) {
						} else {
							$recordModel->set('assigned_user_id', $dataRow['groupid']);
						}
					}
					$recordModel->save();
				} else {
					$focus = CRMEntity::getInstance('Products');
					$focus->column_fields['description'] = trim($value->{'MAKTX'});
					$focus->column_fields['store_code'] = trim($value->{'LGORT'});
					$focus->column_fields['productname'] = $sapRefNum;
					$focus->column_fields['maintain_plant'] = $maintPlant;
					$focus->column_fields['qtyinstock'] = $value->{'LABST'};
					$focus->column_fields['discontinued'] = 1;
					$sql = "select maintenanceplantid,plant_name from vtiger_maintenanceplant where plant_code = ? ";
					$result = $adb->pquery($sql, array(trim($value->{'WERKS'})));
					$dataRow = $adb->fetchByAssoc($result, 0);
					if (empty($dataRow['maintenanceplantid'])) {
						$focus->column_fields['plant_name'] = '';
					} else {
						$focus->column_fields['plant_name'] = $dataRow['maintenanceplantid'];
						$plantName = $dataRow['plant_name'];
						$sql = "select groupid from vtiger_groups where groupname = ? ";
						$result = $adb->pquery($sql, array($plantName));
						$dataRow = $adb->fetchByAssoc($result, 0);
						if (empty($dataRow['groupid'])) {
						} else {
							$focus->column_fields['assigned_user_id'] = $dataRow['groupid'];
						}
					}
					$focus->save("Products");
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
