<?php

class Equipment_SyncRecentlyUpdated_Action extends Vtiger_IndexAjax_View {
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
			$url = getExternalAppURL('getAllEquipmentsUpdated');
			$xml = file_get_contents($url);
			$xml = json_decode($xml);
			foreach ($xml as $key => $value) {
				$sapRefNum = trim($value->{'EQUNR'});
				$sql = 'select equipmentid from vtiger_equipment where equipment_sl_no = ?';
				$sqlResult = $adb->pquery($sql, array($sapRefNum));
				$num_rows = $adb->num_rows($sqlResult);

				$validTo = $value->{'DATBI'};
				if ($validTo == '00000000') {
					$eq_valid_to = NULL;
				} else {
					$y = substr($validTo, 0, 4);
					$m = substr($validTo, 4, 2);
					$d = substr($validTo, 6, 2);
					$eq_valid_to = $y . '-' . $m . '-' . $d;
				}

				$date = $value->{'DATAB'};
				if ($date == '00000000') {
					$eq_valid_from = NULL;
				} else {
					$y = substr($date, 0, 4);
					$m = substr($date, 4, 2);
					$d = substr($date, 6, 2);
					$eq_valid_from = $y . '-' . $m . '-' . $d;
				}

				$date = $value->{'GWLDT'};
				if ($date == '00000000') {
					$cust_begin_guar = NULL;
				} else {
					$y = substr($date, 0, 4);
					$m = substr($date, 4, 2);
					$d = substr($date, 6, 2);
					$cust_begin_guar = $y . '-' . $m . '-' . $d;
				}

				// $date = $value->{'GWLEN'};
				// if ($date == '00000000') {
				// 	$cust_war_end = NULL;
				// } else {
				// 	$y = substr($date, 0, 4);
				// 	$m = substr($date, 4, 2);
				// 	$d = substr($date, 6, 2);
				// 	$cust_war_end = $y . '-' . $m . '-' . $d;
				// }

				$date = $value->{'IDATE'};
				if ($date == '00000000') {
					$last_hmr_date = NULL;
				} else {
					$y = substr($date, 0, 4);
					$m = substr($date, 4, 2);
					$d = substr($date, 6, 2);
					$last_hmr_date = $y . '-' . $m . '-' . $d;
				}

				$date = $value->{'IDATE1'};
				if ($date == '00000000') {
					$last_kilometer_date = NULL;
				} else {
					$y = substr($date, 0, 4);
					$m = substr($date, 4, 2);
					$d = substr($date, 6, 2);
					$last_kilometer_date = $y . '-' . $m . '-' . $d;
				}

				if ($num_rows > 0) {
					$dataRow = $adb->fetchByAssoc($sqlResult, 0);
					$recordModel = Vtiger_Record_Model::getInstanceById($dataRow['equipmentid'], 'Equipment');
					$recordModel->set('mode', 'edit');
					$recordModel->set('equi_desc', trim($value->{'SHTXT'}));
					$recordModel->set('eq_equip_status', trim($value->{'STTXT'}));
					$recordModel->set('maintain_plant', trim($value->{'SWERK'}));
					$recordModel->set('equip_war_terms', trim($value->{'GAKTX'}));
					$optionValue = trim($value->{'TYPBZ'});
					$recordModel->set('equip_model', $optionValue);
					$recordModel->set('equip_category', trim($value->{'EQTYP'}));
					$this->checkAndInsertOption($optionValue);
					$sql = "select accountid from vtiger_account where external_app_num = ? ";
					$result = $adb->pquery($sql, array(trim($value->{'KUND1'})));
					$dataRow = $adb->fetchByAssoc($result, 0);
					if (empty($dataRow['accountid'])) {
						$recordModel->set('account_id', '');
					} else {
						$recordModel->set('account_id', $dataRow['accountid']);
					}

					$sql = "select functionallocationsid from vtiger_functionallocations where functionallocation_name = ? ";
					$result = $adb->pquery($sql, array(trim($value->{'TPLNR'})));
					$dataRow = $adb->fetchByAssoc($result, 0);
					if (empty($dataRow['functionallocationsid'])) {
						$recordModel->set('functional_loc', '');
					} else {
						$recordModel->set('functional_loc', $dataRow['functionallocationsid']);
					}

					$recordModel->set('eq_valid_from', $eq_valid_from);
					$recordModel->set('eq_valid_to', $eq_valid_to);
					$recordModel->set('cust_begin_guar', $cust_begin_guar);
					// $recordModel->set('cust_war_end', $cust_war_end);
					$recordModel->set('last_hmr_date', $last_hmr_date);
					$recordModel->set('last_kilometer_date', $last_kilometer_date);
					$hmr = trim($value->{'RECDV'});
					$kmRun = trim($value->{'RECDV1'});
					$recordModel->set('eq_last_hmr', $hmr);
					$recordModel->set('eq_last_km_run', $kmRun);
					if (!empty($hmr)) {
						$recordModel->set('measuring_point', trim($value->{'POINT'}));
						$recordModel->set('measuring_point_desc', 'Hour Meter Reading');
					} else if (!empty($kmRun)) {
						$recordModel->set('measuring_point', trim($value->{'POINT1'}));
						$recordModel->set('measuring_point_desc', 'Kilometer Reading');
					}
					$idate = $value->{'ITIME'};
					$y = substr($idate, 0, 2);
					$m = substr($idate, 2, 2);
					$d = substr($idate, 4, 2);
					$recordModel->set('last_hmr_time', $y . ':' . $m . ':' . $d);

					// implement plant
					$sql = "select maintenanceplantid,plant_name from vtiger_maintenanceplant where plant_code = ? ";
					$result = $adb->pquery($sql, array(trim($value->{'SWERK'})));
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
							$recordModel->set('assigned_user_id', 1);
						} else {
							$recordModel->set('assigned_user_id', $dataRow['groupid']);
						}
					}
					$recordModel->save();
				} else {
					$focus = CRMEntity::getInstance('Equipment');
					$focus->column_fields['equipment_sl_no'] = trim($value->{'EQUNR'});
					$focus->column_fields['equi_desc'] = trim($value->{'SHTXT'});
					$focus->column_fields['eq_equip_status'] = trim($value->{'STTXT'});
					$focus->column_fields['equip_war_terms'] = trim($value->{'GAKTX'});
					$focus->column_fields['maintain_plant'] = trim($value->{'SWERK'});
					$focus->column_fields['equip_category'] = trim($value->{'EQTYP'});
					$optionValue = trim($value->{'TYPBZ'});
					$focus->column_fields['equip_model'] = $optionValue;
					$this->checkAndInsertOption($optionValue);
					$sql = "select accountid from vtiger_account where external_app_num = ? ";
					$result = $adb->pquery($sql, array(trim($value->{'KUND1'})));
					$dataRow = $adb->fetchByAssoc($result, 0);
					if (empty($dataRow['accountid'])) {
						$focus->column_fields['account_id']  = '';
					} else {
						$focus->column_fields['account_id']  = $dataRow['accountid'];
					}

					$sql = "select functionallocationsid from vtiger_functionallocations where functionallocation_name = ? ";
					$result = $adb->pquery($sql, array(trim($value->{'TPLNR'})));
					$dataRow = $adb->fetchByAssoc($result, 0);
					if (empty($dataRow['functionallocationsid'])) {
						$focus->column_fields['functional_loc']  = '';
					} else {
						$focus->column_fields['functional_loc']  = $dataRow['functionallocationsid'];
					}

					$focus->column_fields['cust_begin_guar'] = $cust_begin_guar;
					// $focus->column_fields['cust_war_end'] = $cust_war_end;
					$focus->column_fields['eq_valid_from'] = $eq_valid_from;
					$focus->column_fields['eq_valid_to'] = $eq_valid_to;
					$focus->column_fields['last_hmr_date'] = $last_hmr_date;
					$focus->column_fields['last_kilometer_date'] = $last_kilometer_date;

					$hmr = trim($value->{'RECDV'});
					$kmRun = trim($value->{'RECDV1'});
					$focus->column_fields['eq_last_km_run'] = $kmRun;
					if (!empty($hmr)) {
						$focus->column_fields['measuring_point'] = trim($value->{'POINT'});
						$focus->column_fields['measuring_point_desc'] = 'Hour Meter Reading';
					} else if (!empty($kmRun)) {
						$focus->column_fields['measuring_point'] = trim($value->{'POINT1'});
						$focus->column_fields['measuring_point_desc'] = 'Kilometer Reading';
					}
					$focus->column_fields['eq_last_hmr'] = $hmr;
					$idate = $value->{'ITIME'};
					$y = substr($idate, 0, 2);
					$m = substr($idate, 2, 2);
					$d = substr($idate, 4, 2);
					$focus->column_fields['last_hmr_time'] = $y . ':' . $m . ':' . $d;

					$sql = "select maintenanceplantid,plant_name from vtiger_maintenanceplant where plant_code = ? ";
					$result = $adb->pquery($sql, array(trim($value->{'SWERK'})));
					$dataRow = $adb->fetchByAssoc($result, 0);
					if (empty($dataRow['maintenanceplantid'])) {
						$focus->column_fields['plant_name'] = NULL;
					} else {
						$focus->column_fields['plant_name'] = $dataRow['maintenanceplantid'];
						$sql = "select groupid from vtiger_groups where groupname = ? ";
						$result = $adb->pquery($sql, array($dataRow['plant_name']));
						$dataRow = $adb->fetchByAssoc($result, 0);
						if (empty($dataRow['groupid'])) {
							$focus->column_fields['assigned_user_id'] = 1;
						} else {
							$focus->column_fields['assigned_user_id'] = $dataRow['groupid'];
						}
					}
					$focus->save("Equipment");
				}
				$this->createWarrantyRecordIfNOtEits($value);
			}
		} else {
			$response->setError("External App Sync Not Allowed");
			$response->emit();
		}
	}
	public  function createWarrantyRecordIfNOtEits($value) {
		global $adb;
		$warantyCode = trim($value->{'MGANR'});
		$sql = 'select 1 from `vtiger_warrantydetails` where wr_warranty_code = ?';
		$sqlResult = $adb->pquery($sql, array($warantyCode));
		$num_rows = $adb->num_rows($sqlResult);
		if ($num_rows > 0) {
		} else {
			$focus = CRMEntity::getInstance('WarrantyDetails');
			$focus->column_fields['wr_warranty_code'] = $warantyCode;
			$focus->column_fields['wr_warranty_description'] = trim($value->{'GAKTX'});
			$focus->column_fields['assigned_user_id'] = 1;
			$focus->save("WarrantyDetails");
		}
	}

	public  function checkAndInsertOption($option) {
		global $adb;
		$sql = 'select 1 from `vtiger_sr_equip_model` where sr_equip_model = ?';
		$sqlResult = $adb->pquery($sql, array($option));
		$num_rows = $adb->num_rows($sqlResult);
		if ($num_rows > 0) {
		} else {
			$insertSql = "INSERT INTO `vtiger_sr_equip_model` (`sr_equip_modelid`, `sr_equip_model`, `sortorderid`, `presence`, `color`)
			 VALUES (NULL, ? , NULL, '1', NULL)";
			$adb->pquery($insertSql, array($option));
		}

		$sql = 'select 1 from `vtiger_eq_sr_equip_model` where eq_sr_equip_model = ?';
		$sqlResult = $adb->pquery($sql, array($option));
		$num_rows = $adb->num_rows($sqlResult);
		if ($num_rows > 0) {
		} else {
			$insertSql = "INSERT INTO `vtiger_eq_sr_equip_model` (`eq_sr_equip_modelid`, `eq_sr_equip_model`, `sortorderid`, `presence`, `color`)
			 VALUES (NULL, ? , NULL, '1', NULL)";
			$adb->pquery($insertSql, array($option));
		}
	}
}
