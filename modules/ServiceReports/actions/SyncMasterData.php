<?php
class ServiceReports_SyncMasterData_Action extends Vtiger_IndexAjax_View {
	public function requiresPermission(\Vtiger_Request $request) {
		$permissions = parent::requiresPermission($request);
		$permissions[] = array('module_parameter' => 'module', 'action' => 'Export');
		return $permissions;
	}
	function __getPicklistUniqueId() {
		global $adb;
		return $adb->getUniqueID('vtiger_picklist');
	}

	public function process(Vtiger_Request $request) {
		global $adb;
		$response = new Vtiger_Response();
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		if ($currentUserModel->isAdminUser()) {
			global $adb;
			include_once('include/utils/GeneralUtils.php');
			$url = getExternalAppURL('getAllNotificationMetaInfo');
			$xml = file_get_contents($url);
			// print_r($xml);
			// die();
			$xml = json_decode($xml);
			// $sortid = 0;
			// foreach ($xml->{'IT_CAUSE'} as $key => $value) {
			// 	$new_id = $adb->getUniqueID('vtiger_fail_de_type_of_damage');
			// 	$val = trim($value->{'KURZTEXT'}).'_._'.trim($value->{'CODETEXT'});
			// 	++$sortid;
			// 	$new_picklistvalueid = getUniquePicklistID();
			// 	$adb->pquery("INSERT INTO vtiger_fail_de_type_of_damage(fail_de_type_of_damageid, fail_de_type_of_damage, presence, picklist_valueid,sortorderid,code,code_group) VALUES(?,?,?,?,?,?,?)", Array($new_id, $val, 1, $new_picklistvalueid, $sortid,trim($value->{'CODE'}),trim($value->{'CODEGRUPPE'})));
			// }
			// $sortid = 0;
			// foreach ($xml->{'IT_OBJECTPART'} as $key => $value) {
			// 	$new_id = $adb->getUniqueID('vtiger_fail_de_system_affected');
			// 	$val = trim($value->{'KURZTEXT'}).'_._'.trim($value->{'CODETEXT'});
			// 	++$sortid;
			// 	$new_picklistvalueid = getUniquePicklistID();
			// 	$adb->pquery("INSERT INTO vtiger_fail_de_system_affected(fail_de_system_affectedid, fail_de_system_affected, presence, picklist_valueid,sortorderid,code,code_group) VALUES(?,?,?,?,?,?,?)", Array($new_id, $val, 1, $new_picklistvalueid, $sortid,trim($value->{'CODE'}),trim($value->{'CODEGRUPPE'})));
			// }
			// $sortid = 0;
			// foreach ($xml->{'IT_RESPONSIBLE'} as $key => $value) {
			// 	$fieldName = 'fail_de_part_pertains_to_ano';
			// 	$new_id = $adb->getUniqueID('vtiger_'.$fieldName);
			// 	$val = trim($value->{'KURZTEXT'}).'_._'.trim($value->{'CODETEXT'});
			// 	++$sortid;
			// 	$fieldId =  $fieldName.'id';
			// 	$sql = "INSERT INTO vtiger_$fieldName( $fieldId , $fieldName, presence,sortorderid,code,code_group) VALUES(?,?,?,?,?,?)";
			// 	$arr = Array($new_id, $val, 1, $sortid,trim($value->{'CODE'}),trim($value->{'CODEGRUPPE'}));
			// 	$adb->pquery($sql, $arr);
			// }

			// $sortid = 0;
			// foreach ($xml->{'IT_DAMAGE'} as $key => $value) {
			// 	$fieldName = 'fail_de_parts_affected';
			// 	$new_id = $adb->getUniqueID('vtiger_'.$fieldName);
			// 	$val = trim($value->{'KURZTEXT'}).'_._'.trim($value->{'CODETEXT'});
			// 	++$sortid;
			// 	$fieldId =  $fieldName.'id';
			// 	$new_picklistvalueid = getUniquePicklistID();
			// 	$adb->pquery("INSERT INTO vtiger_$fieldName($fieldId, $fieldName, presence, picklist_valueid,sortorderid,code,code_group) VALUES(?,?,?,?,?,?,?)", Array($new_id, $val, 1, $new_picklistvalueid, $sortid,trim($value->{'CODE'}),trim($value->{'CODEGRUPPE'})));
			// }

			// $sortid = 0;
			// foreach ($xml->{'IT_SYSCONDITION'} as $key => $value) {
			// 	$fieldName = 'fd_eq_sta_bsr';
			// 	$new_id = $adb->getUniqueID('vtiger_'.$fieldName);
			// 	$val = trim($value->{'ANLAZT'});
			// 	++$sortid;
			// 	$fieldId =  $fieldName.'id';
			// 	$sql = "INSERT INTO vtiger_$fieldName( $fieldId , $fieldName, presence,sortorderid,code) VALUES(?,?,?,?,?)";
			// 	$arr = Array($new_id, $val, 1, $sortid,trim($value->{'ANLAZ'}));
			// 	// print_r(array($sql, implode("','" ,$arr)));
			// 	// die();
			// 	$adb->pquery($sql, $arr);
			// }
			// $sortid = 0;
			// foreach ($xml->{'IT_SYSCONDITION'} as $key => $value) {
			// 	$fieldName = 'sr_equip_status';
			// 	$new_id = $adb->getUniqueID('vtiger_'.$fieldName);
			// 	$val = trim($value->{'ANLAZT'});
			// 	++$sortid;
			// 	$fieldId =  $fieldName.'id';
			// 	$sql = "INSERT INTO vtiger_$fieldName( $fieldId , $fieldName, presence,sortorderid,code) VALUES(?,?,?,?,?)";
			// 	$arr = Array($new_id, $val, 1, $sortid,trim($value->{'ANLAZ'}));
			// 	// print_r(array($sql, implode("','" ,$arr)));
			// 	// die();
			// 	$adb->pquery($sql, $arr);
			// }
			// $sortid = 0;
			// foreach ($xml->{'IT_SYSCONDITION'} as $key => $value) {
			// 	$fieldName = 'eq_sta_aft_act_taken';
			// 	$new_id = $adb->getUniqueID('vtiger_'.$fieldName);
			// 	$val = trim($value->{'ANLAZT'});
			// 	++$sortid;
			// 	$fieldId =  $fieldName.'id';
			// 	$sql = "INSERT INTO vtiger_$fieldName( $fieldId , $fieldName, presence,sortorderid,code) VALUES(?,?,?,?,?)";
			// 	$arr = Array($new_id, $val, 1, $sortid,trim($value->{'ANLAZ'}));
			// 	// print_r(array($sql, implode("','" ,$arr)));
			// 	// die();
			// 	$adb->pquery($sql, $arr);
			// }
			// $sortid = 0;
			// foreach ($xml->{'IT_SYSCONDITION'} as $key => $value) {
			// 	$fieldName = 'equip_status';
			// 	$new_id = $adb->getUniqueID('vtiger_'.$fieldName);
			// 	$val = trim($value->{'ANLAZT'});
			// 	++$sortid;
			// 	$fieldId =  $fieldName.'id';
			// 	$sql = "INSERT INTO vtiger_$fieldName( $fieldId , $fieldName, presence,sortorderid,code) VALUES(?,?,?,?,?)";
			// 	$arr = Array($new_id, $val, 1, $sortid,trim($value->{'ANLAZ'}));
			// 	// print_r(array($sql, implode("','" ,$arr)));
			// 	// die();
			// 	$adb->pquery($sql, $arr);
			// }

			// $sortid = 0;
			// foreach ($xml->{'IT_DAMAGE'} as $key => $value) {
			// 	$fieldName = 'sr_system_affected';
			// 	$new_id = $adb->getUniqueID('vtiger_'.$fieldName);
			// 	$val = trim($value->{'KURZTEXT'}).'_._'.trim($value->{'CODETEXT'});
			// 	++$sortid;
			// 	$fieldId =  $fieldName.'id';
			// 	$new_picklistvalueid = getUniquePicklistID();
			// 	$adb->pquery("INSERT INTO vtiger_$fieldName($fieldId, $fieldName, presence, picklist_valueid,sortorderid) VALUES(?,?,?,?,?)", Array($new_id, $val, 1, $new_picklistvalueid, $sortid));
			// }

			// $sortid = 0;
			// foreach ($xml->{'IT_DAMAGE'} as $key => $value) {
			// 	$fieldName = 'system_affected';
			// 	$new_id = $adb->getUniqueID('vtiger_'.$fieldName);
			// 	$val = trim($value->{'KURZTEXT'}).'_._'.trim($value->{'CODETEXT'});
			// 	++$sortid;
			// 	$fieldId =  $fieldName.'id';
			// 	$new_picklistvalueid = getUniquePicklistID();
			// 	$adb->pquery("INSERT INTO vtiger_$fieldName($fieldId, $fieldName, presence, picklist_valueid,sortorderid) VALUES(?,?,?,?,?)", Array($new_id, $val, 1, $new_picklistvalueid, $sortid));
			// }
		} else {
			$response->setError("External App Sync Not Allowed");
			$response->emit();
		}
	}
}
