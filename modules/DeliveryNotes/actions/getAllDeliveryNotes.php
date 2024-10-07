<?php

class DeliveryNotes_getAllDeliveryNotes_Action extends Vtiger_IndexAjax_View {
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
			$url = getExternalAppURL('getAllDispatchAdvise');
			$itiration = $request->get('indexForLink');
			$fireRequest = true;
			$requestUrl = $url . '&INDEX=' . $itiration;
			$xml = file_get_contents($requestUrl);
			$xml = json_decode($xml);

			if (empty($xml)) {
				$fireRequest = false;
			}
			foreach ($xml->{'IT_DESPATCHDATA'} as $key => $value) {

				$sapRefNum = trim($value->{'VBELN'});
				$eqNum = trim($value->{'EQUNR'});
				$sql = ' select deliverynotesid from vtiger_deliverynotes '.
				// 'INNER JOIN vtiger_equipment on '.
				// 'vtiger_equipment.equipmentid = vtiger_deliverynotes.equipment_id '.
				' where deliveynotessapref = ? and vtiger_deliverynotes.manual_equ_ser = ?';

				$sqlResult = $adb->pquery($sql, array($sapRefNum, $eqNum));
				$num_rows = $adb->num_rows($sqlResult);

				$deliveryDate = $value->{'LFDAT'};
				if ($deliveryDate == '00000000') {
					$del_date = NULL;
				} else {
					$y = substr($deliveryDate, 0, 4);
					$m = substr($deliveryDate, 4, 2);
					$d = substr($deliveryDate, 6, 2);
					$del_date = $y . '-' . $m . '-' . $d;
				}

				$createdDate = $value->{'ERDAT'};
				if ($createdDate == '00000000') {
					$rec_created_dt = NULL;
				} else {
					$y = substr($createdDate, 0, 4);
					$m = substr($createdDate, 4, 2);
					$d = substr($createdDate, 6, 2);
					$rec_created_dt = $y . '-' . $m . '-' . $d;
				}

				if ($num_rows > 0) {
					$dataRow = $adb->fetchByAssoc($sqlResult, 0);
					$recordModel = Vtiger_Record_Model::getInstanceById($dataRow['deliverynotesid'], 'DeliveryNotes');
					$recordModel->set('mode', 'edit');

					$recordModel->set('rec_created_dt', $rec_created_dt);
					$recordModel->set('delivery_date', $del_date);

					$sql = "select accountid from vtiger_account where external_app_num = ? ";
					$result = $adb->pquery($sql, array(trim($value->{'KUNNR'})));
					$dataRow = $adb->fetchByAssoc($result, 0);
					if (empty($dataRow['accountid'])) {
						$recordModel->set('account_id', '');
					} else {
						$recordModel->set('account_id', $dataRow['accountid']);
					}

					$sql = "select equipmentid from vtiger_equipment where equipment_sl_no = ? ";
					$result = $adb->pquery($sql, array($eqNum));
					$dataRow = $adb->fetchByAssoc($result, 0);
					if (empty($dataRow['equipmentid'])) {
						$recordModel->set('manual_equ_ser', $eqNum);
						$recordModel->set('equipment_id', NULL);
					} else {
						$recordModel->set('equipment_id', $dataRow['equipmentid']);
						$recordModel->set('manual_equ_ser', $eqNum);
					}

					// implement plant
					$sql = "select maintenanceplantid,plant_name from vtiger_maintenanceplant where plant_code = ? ";
					$result = $adb->pquery($sql, array(trim($value->{'VSTEL'})));
					$dataRow = $adb->fetchByAssoc($result, 0);
					if (empty($dataRow['maintenanceplantid'])) {
						$recordModel->set('recieving_plant', '');
					} else {
						$recordModel->set('recieving_plant', $dataRow['maintenanceplantid']);
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
					$focus = CRMEntity::getInstance('DeliveryNotes');
					$focus->column_fields['rec_created_dt'] = $rec_created_dt;
					$focus->column_fields['deliveynotessapref'] = $sapRefNum;
					$focus->column_fields['delivery_date'] = $del_date;

					$sql = "select accountid from vtiger_account where external_app_num = ? ";
					$result = $adb->pquery($sql, array(trim($value->{'KUNNR'})));
					$dataRow = $adb->fetchByAssoc($result, 0);
					if (empty($dataRow['accountid'])) {
						$focus->column_fields['account_id']  = '';
					} else {
						$focus->column_fields['account_id']  = $dataRow['accountid'];
					}

					$sql = "select equipmentid from vtiger_equipment where equipment_sl_no = ? ";
					$result = $adb->pquery($sql, array($eqNum));
					$dataRow = $adb->fetchByAssoc($result, 0);
					if (empty($dataRow['equipmentid'])) {
						$focus->column_fields['equipment_id'] = NULL;
						$focus->column_fields['manual_equ_ser'] = $eqNum;
					} else {
						$focus->column_fields['equipment_id'] =  $dataRow['equipmentid'];
						$focus->column_fields['manual_equ_ser'] = $eqNum;
					}

					$sql = "select maintenanceplantid,plant_name from vtiger_maintenanceplant where plant_code = ? ";
					$result = $adb->pquery($sql, array(trim($value->{'VSTEL'})));
					$dataRow = $adb->fetchByAssoc($result, 0);
					if (empty($dataRow['maintenanceplantid'])) {
						$focus->column_fields['recieving_plant'] = NULL;
					} else {
						$focus->column_fields['recieving_plant'] = $dataRow['maintenanceplantid'];
						$sql = "select groupid from vtiger_groups where groupname = ? ";
						$result = $adb->pquery($sql, array($dataRow['plant_name']));
						$dataRow = $adb->fetchByAssoc($result, 0);
						if (empty($dataRow['groupid'])) {
						} else {
							$focus->column_fields['assigned_user_id'] = $dataRow['groupid'];
						}
					}
					$focus->save("DeliveryNotes");
				}
			}
			$response = new Vtiger_Response();
			$response->setResult(array('success' => true, 'hasNext' => false));
			$response->emit();
		} else {
			$response->setError("External App Sync Not Allowed");
			$response->emit();
		}
	}
}
