<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
include_once "include/Webservices/Custom/ChangePassword.php";
include_once "include/Webservices/Utils.php";
include_once "includes/runtime/EntryPoint.php";
class Users_UserSetupSaveFromSE_Action extends Users_Save_Action {

	public function process(Vtiger_Request $request) {
		$response = new Vtiger_Response();
		$userRecordModel = Users_Record_Model::getCurrentUserModel();
		$response = new Vtiger_Response();

		$newPassword = $request->get('user_password');
		$confirmPassword = $request->get('confirm_password');

		if ($newPassword != $confirmPassword) {
			$response->setError('New Password and Confirm Password Are Not Matching');
		} else {
			$userId = $userRecordModel->getId();
			$user = Users::getActiveAdminUser();
			$wsUserId = vtws_getWebserviceEntityId('Users', $userId);
			$wsStatus = vtws_changePassword($wsUserId, '', $newPassword, $confirmPassword, $user);

			if ($wsStatus['message']) {
				$response->setResult($wsStatus);
			} else {
				$response->setError('Unable To Reset Password');
			}

			$db = PearDatabase::getInstance();
			$query = 'delete FROM vtiger_crmsetup WHERE userid = ? and setup_status = ?';
			$db->pquery($query, array($userRecordModel->getId(), 1));
		}

		$response->emit();
	}
}
