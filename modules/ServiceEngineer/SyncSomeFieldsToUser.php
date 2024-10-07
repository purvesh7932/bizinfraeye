<?php
function SyncSomeFieldsToUser($entityData) {
	$data = $entityData->{'data'};
	require_once('modules/Users/Users.php');
	global $adb;

	$recId = $data['id'];
	$idsOfCreated = explode('x', $recId);
	$data['id'] = $idsOfCreated[1];

	$username = preg_replace('/\s+/', '', $data['badge_no']);
	$result = $adb->pquery('SELECT id FROM `vtiger_users` where user_name = ?', array($username));
	$rowCount = $adb->num_rows($result);
	if ($rowCount > 0) {
		$dataRow = $adb->fetchByAssoc($result, 0);
		$recordModel = Vtiger_Record_Model::getInstanceById($dataRow['id'], 'Users');
		if (!empty($recordModel)) {
			$recordModel->set('mode', 'edit');
			$recordModel->set('last_name', $data['service_engineer_name']);
			$recordModel->set('email1', $data['email']);
			$recordModel->save();
		}
	}
}
