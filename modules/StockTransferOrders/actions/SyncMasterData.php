<?php
class StockTransferOrders_SyncMasterData_Action extends Vtiger_IndexAjax_View {
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
			global $adb;
			$sql = 'select * from vtiger_external_application_info where id = 1 ';
			$sqlResult = $adb->pquery($sql, array());
			$dataRow = $adb->fetchByAssoc($sqlResult, 0);
			$u = $dataRow['uname'];
			$p = $dataRow['pass'];
			$urlBase = $dataRow['url'];
			$url = $urlBase . "getSettingsDataOfSTO.php" . "?uta=" . $u . '&pws=' . $p;
			$xml = file_get_contents($url);
			$xml = json_decode($xml);

			// $sortid = 0;
			// foreach ($xml->{'IT_TYPE'} as $key => $value) {
			// 	$fieldName = 'lsi_sto_type';
			// 	$new_id = $adb->getUniqueID('vtiger_'.$fieldName);
			// 	$val = trim($value->{'BATXT'});
			// 	++$sortid;
			// 	$fieldId =  $fieldName.'id';
			// 	$sql = "INSERT INTO vtiger_$fieldName( $fieldId , $fieldName, presence,sortorderid,code) VALUES(?,?,?,?,?)";
			// 	$arr = Array($new_id, $val, 1, $sortid,trim($value->{'BSART'}));
			// 	$adb->pquery($sql, $arr);
			// }

			// $sortid = 0;
			// foreach ($xml->{'IT_EKGRP'} as $key => $value) {
			// 	$fieldName = 'lsi_purchase_grp';
			// 	$new_id = $adb->getUniqueID('vtiger_'.$fieldName);
			// 	$val = trim($value->{'EKGRP'});
			// 	++$sortid;
			// 	$fieldId =  $fieldName.'id';
			// 	$sql = "INSERT INTO vtiger_$fieldName( $fieldId , $fieldName, presence,sortorderid,code) VALUES(?,?,?,?,?)";
			// 	$arr = Array($new_id, $val, 1, $sortid,trim($value->{'EKGRP'}));
			// 	$adb->pquery($sql, $arr);
			// }
			// $url = $urlBase . "getAllPlants.php" . "?uta=" . $u . '&pws=' . $p;
			// $xml = file_get_contents($url);
			// $xml = json_decode($xml);
			// $sortid = 0;
			// foreach ($xml->{'STORE_CODE'} as $key => $value) {
			// 	$fieldName = 'lid_store_locations';
			// 	$new_id = $adb->getUniqueID('vtiger_'.$fieldName);
			// 	$val = trim($value->{'LGOBE'});
			// 	++$sortid;
			// 	$fieldId =  $fieldName.'id';
			// 	$sql = "INSERT INTO vtiger_$fieldName( $fieldId , $fieldName, presence,sortorderid,code) VALUES(?,?,?,?,?)";
			// 	$arr = Array($new_id, $val, 1, $sortid,trim($value->{'LGORT'}));
			// 	$adb->pquery($sql, $arr);
			// }
			$response->setResult(array('success' => true, 'data' => $xml));
			$response->emit();
		} else {
			$response->setError("External App Sync Not Allowed");
			$response->emit();
		}
	}
}
