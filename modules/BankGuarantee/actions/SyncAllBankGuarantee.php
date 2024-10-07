<?php
class BankGuarantee_SyncAllBankGuarantee_Action extends Vtiger_IndexAjax_View {
	public function requiresPermission(\Vtiger_Request $request) {
		$permissions = parent::requiresPermission($request);
		$permissions[] = array('module_parameter' => 'module', 'action' => 'Export');
		return $permissions;
	}

	public function process(Vtiger_Request $request) {
		$response = new Vtiger_Response();
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		if ($currentUserModel->isAdminUser()) {
			global $adb;
			include_once('include/utils/GeneralUtils.php');
			$url = getExternalAppURL('getAllBankGuarantees');
			$xml = file_get_contents($url);
			$xml = json_decode($xml);
			foreach ($xml as $key => $value) {

				$dateVal = $value->{'ZBG_VAL_DATE1'};
				if ($dateVal == '00000000') {
					$bg_initial_vl_st_date = NULL;
				} else {
					$y = substr($dateVal, 0, 4);
					$m = substr($dateVal, 4, 2);
					$d = substr($dateVal, 6, 2);
					$bg_initial_vl_st_date =  $y . '-' . $m . '-' . $d;
				}

				$dateVal = $value->{'ZBG_CLM_DATE1'};
				if ($dateVal == '00000000') {
					$bg_initial_vl_en_date = NULL;
				} else {
					$y = substr($dateVal, 0, 4);
					$m = substr($dateVal, 4, 2);
					$d = substr($dateVal, 6, 2);
					$bg_initial_vl_en_date =  $y . '-' . $m . '-' . $d;
				}

				$dateVal = $value->{'ZBG_VAL_DATE2'};
				if ($dateVal == '00000000') {
					$bg_initial_cl_st_date = NULL;
				} else {
					$y = substr($dateVal, 0, 4);
					$m = substr($dateVal, 4, 2);
					$d = substr($dateVal, 6, 2);
					$bg_initial_cl_st_date =  $y . '-' . $m . '-' . $d;
				}

				$dateVal = $value->{'ZBG_CLM_DATE2'};
				if ($dateVal == '00000000') {
					$bg_initial_cl_end_date = NULL;
				} else {
					$y = substr($dateVal, 0, 4);
					$m = substr($dateVal, 4, 2);
					$d = substr($dateVal, 6, 2);
					$bg_initial_cl_end_date =  $y . '-' . $m . '-' . $d;
				}

				$dateVal = $value->{'ZBG_VAL_DATE3'};
				if ($dateVal == '00000000') {
					$ex_val_start_d = NULL;
				} else {
					$y = substr($dateVal, 0, 4);
					$m = substr($dateVal, 4, 2);
					$d = substr($dateVal, 6, 2);
					$ex_val_start_d =  $y . '-' . $m . '-' . $d;
				}

				$dateVal = $value->{'ZBG_CLM_DATE3'};
				if ($dateVal == '00000000') {
					$ex_val_end_d = NULL;
				} else {
					$y = substr($dateVal, 0, 4);
					$m = substr($dateVal, 4, 2);
					$d = substr($dateVal, 6, 2);
					$ex_val_end_d =  $y . '-' . $m . '-' . $d;
				}


				$dateVal = $value->{'ZBG_VAL_DATE4'};
				if ($dateVal == '00000000') {
					$bg_extended_cl_st_date = NULL;
				} else {
					$y = substr($dateVal, 0, 4);
					$m = substr($dateVal, 4, 2);
					$d = substr($dateVal, 6, 2);
					$bg_extended_cl_st_date =  $y . '-' . $m . '-' . $d;
				}

				$dateVal = $value->{'ZBG_CLM_DATE4'};
				if ($dateVal == '00000000') {
					$bg_extended_cl_end_date = NULL;
				} else {
					$y = substr($dateVal, 0, 4);
					$m = substr($dateVal, 4, 2);
					$d = substr($dateVal, 6, 2);
					$bg_extended_cl_end_date =  $y . '-' . $m . '-' . $d;
				}

				$sapRefNum = trim($value->{'ZBG_NO'});
				$sql = 'select bankguaranteeid from vtiger_bankguarantee where zbg_no = ?';
				$sqlResult = $adb->pquery($sql, array($sapRefNum));
				$num_rows = $adb->num_rows($sqlResult);
				if ($num_rows > 0) {
					$dataRow = $adb->fetchByAssoc($sqlResult, 0);
					$recordModel = Vtiger_Record_Model::getInstanceById($dataRow['bankguaranteeid'], 'BankGuarantee');
					$focus = CRMEntity::getInstance('BankGuarantee');
					$recordModel->set('mode', 'edit');
					$recordModel->set('equipment_model', trim($value->{'ZBG_EQPTMODEL'}));
					$recordModel->set('zbg_no', $sapRefNum);
					$recordModel->set('bg_val', $value->{'ZBG_VALUE'});
					$recordModel->set('zbg_region', trim($value->{'ZBG_REGION'}));
					$this->checkAndInsertOption(trim($value->{'ZBG_REGION'}));
					$recordModel->set('manual_equ_ser', trim($value->{'ZBG_EQPTSLNO'}));

					$validfrom = $value->{'ZBG_VALDTFROM'};
					$y = substr($validfrom, 0, 4);
					$m = substr($validfrom, 4, 2);
					$d = substr($validfrom, 6, 2);
					$recordModel->set('bg_valid_from', $y . '-' . $m . '-' . $d);

					$validTo = $value->{'ZBG_VALDTTO'};
					$y = substr($validTo, 0, 4);
					$m = substr($validTo, 4, 2);
					$d = substr($validTo, 6, 2);
					$recordModel->set('bg_valid_to', $y . '-' . $m . '-' . $d);

					$sql = "select equipmentid, functional_loc from vtiger_equipment where equipment_sl_no = ? ";
					$result = $adb->pquery($sql, array(trim($value->{'ZBG_EQPTMODEL'}) . '-' . trim(ltrim(trim($value->{'ZBG_EQPTSLNO'}), '0'))));
					$dataRow = $adb->fetchByAssoc($result, 0);
					if (empty($dataRow['equipmentid'])) {
						$recordModel->set('equipment_id', '');
					} else {
						$recordModel->set('equipment_id', $dataRow['equipmentid']);
						$recordModel->set('functional_loc', $dataRow['functional_loc']);
					}

					$sql = "select accountid from vtiger_account where external_app_num = ? ";
					$result = $adb->pquery($sql, array(trim($value->{'ZBG_CUSTCODE'})));
					$dataRow = $adb->fetchByAssoc($result, 0);
					if (empty($dataRow['accountid'])) {
						$recordModel->set('account_id', '');
					} else {
						$recordModel->set('account_id', $dataRow['accountid']);
					}

					$sql = "select maintenanceplantid,plant_name from vtiger_maintenanceplant where plant_code = ? ";
					$result = $adb->pquery($sql, array(trim($value->{'ZBG_PLANTNO'})));
					$dataRow = $adb->fetchByAssoc($result, 0);
					if (empty($dataRow['maintenanceplantid'])) {
						$recordModel->set('plant_name', '');
					} else {
						$recordModel->set('plant_name', $dataRow['maintenanceplantid']);
						$plantName = $dataRow['plant_name'];
						$sql = "select groupid from vtiger_groups where groupname = ? ";
						$result = $adb->pquery($sql, array( trim($value->{'ZBG_REGION'}).'-Depot'));
						$dataRow = $adb->fetchByAssoc($result, 0);
						if (empty($dataRow['groupid'])) {
						} else {
							$recordModel->set('assigned_user_id', $dataRow['groupid']);
						}
					}
					$recordModel->set('bg_initial_vl_st_date', $bg_initial_vl_st_date);
					$recordModel->set('bg_initial_vl_en_date', $bg_initial_vl_en_date);

					$recordModel->set('bg_initial_cl_st_date', $bg_initial_cl_st_date);
					$recordModel->set('bg_initial_cl_end_date', $bg_initial_cl_end_date);

					$recordModel->set('ex_val_start_d', $ex_val_start_d);
					$recordModel->set('ex_val_end_d', $ex_val_end_d);

					$recordModel->set('bg_extended_cl_st_date', $bg_extended_cl_st_date);
					$recordModel->set('bg_extended_cl_end_date', $bg_extended_cl_end_date);

					$recordModel->save();
				} else {
					$focus = CRMEntity::getInstance('BankGuarantee');
					$focus->column_fields['equipment_model'] = $value->{'ZBG_EQPTMODEL'};
					$sql = "select equipmentid,functional_loc from vtiger_equipment where equipment_sl_no = ? ";
					$result = $adb->pquery($sql, array(trim($value->{'ZBG_EQPTMODEL'}) . '-' . trim(ltrim(trim($value->{'ZBG_EQPTSLNO'}), '0'))));
					$dataRow = $adb->fetchByAssoc($result, 0);
					if (empty($dataRow['equipmentid'])) {
						$focus->column_fields['equipment_id'] = '';
					} else {
						$focus->column_fields['equipment_id'] =  $dataRow['equipmentid'];
						$focus->column_fields['functional_loc'] =  $dataRow['functional_loc'];
					}
					$focus->column_fields['zbg_no'] = $sapRefNum;
					$focus->column_fields['bg_val'] = $value->{'ZBG_VALUE'};
					$focus->column_fields['zbg_region'] = $value->{'ZBG_REGION'};
					$this->checkAndInsertOption(trim($value->{'ZBG_REGION'}));
					$validfrom = $value->{'ZBG_VALDTFROM'};
					$y = substr($validfrom, 0, 4);
					$m = substr($validfrom, 4, 2);
					$d = substr($validfrom, 6, 2);
					$focus->column_fields['bg_valid_from'] = $y . '-' . $m . '-' . $d;

					$validTo = $value->{'ZBG_VALDTTO'};
					$y = substr($validTo, 0, 4);
					$m = substr($validTo, 4, 2);
					$d = substr($validTo, 6, 2);
					$focus->column_fields['bg_valid_to'] = $y . '-' . $m . '-' . $d;

					$sql = "select accountid from vtiger_account where external_app_num = ? ";
					$result = $adb->pquery($sql, array(trim($value->{'ZBG_CUSTCODE'})));
					$dataRow = $adb->fetchByAssoc($result, 0);
					if (empty($dataRow['accountid'])) {
						$focus->column_fields['account_id'] = '';
					} else {
						$focus->column_fields['account_id'] = $dataRow['accountid'];
					}

					$sql = "select maintenanceplantid,plant_name from vtiger_maintenanceplant where plant_code = ? ";
					$result = $adb->pquery($sql, array(trim($value->{'ZBG_PLANTNO'})));
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

					$focus->column_fields['bg_initial_vl_st_date'] = $bg_initial_vl_st_date;
					$focus->column_fields['bg_initial_vl_en_date'] = $bg_initial_vl_en_date;

					$focus->column_fields['bg_initial_cl_st_date'] =  $bg_initial_cl_st_date;
					$focus->column_fields['bg_initial_cl_end_date'] = $bg_initial_cl_end_date;

					$focus->column_fields['ex_val_start_d'] = $ex_val_start_d;
					$focus->column_fields['ex_val_end_d'] = $ex_val_end_d;

					$focus->column_fields['bg_extended_cl_st_date'] = $bg_extended_cl_st_date;
					$focus->column_fields['bg_extended_cl_end_date'] = $bg_extended_cl_end_date;

					$focus->save("BankGuarantee");
				}
			}
		} else {
			$response->setError("External App Sync Not Allowed");
			$response->emit();
		}
	}

	public  function checkAndInsertOption($option) {
		global $adb;
		$field = "zbg_region";
		$sql = "select 1 from `vtiger_$field` where $field = ?";
		$sqlResult = $adb->pquery($sql, array($option));
		$num_rows = $adb->num_rows($sqlResult);
		if ($num_rows > 0) {
		} else {
			$insertSql = "INSERT INTO `vtiger_$field` (`$field"."id`, `$field`, `sortorderid`, `presence`, `color`)
			 VALUES (NULL, ? , NULL, '1', NULL)";
			$adb->pquery($insertSql, array($option));
		}
	}
}
