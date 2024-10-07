<?php
vimport('~~/include/Webservices/Custom/ChangePassword.php');

class ServiceEngineer_SaveAjaxSavePassword_Action extends Vtiger_IndexAjax_View {

	public function process(Vtiger_Request $request) {
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		if ($currentUserModel->isAdminUser()) {
			$this->savePassword($request);
		} else {
			$response = new Vtiger_Response();
			$response->setEmitType(Vtiger_Response::$EMIT_JSON);
			$response->setError('You Are Not Allowed Reset Password');
			$response->emit();
		}
	}

	public function savePassword(Vtiger_Request $request) {
		$response = new Vtiger_Response();
		$module = $request->getModule();
		$userModel = vglobal('current_user');
		$serviceEngid = $request->get('serviceEngid');
		$email = '';
		if (!empty($serviceEngid)) {
			$recordModel = Vtiger_Record_Model::getInstanceById($serviceEngid, 'ServiceEngineer');
			$email = $recordModel->get('email');
			if (empty($email)) {
				$response->setError('Email Is Empty In This Employee');
			}
		} else {
			return;
		}
		$newPassword = makeRandomPassword();
		$oldPassword = $newPassword;

		$wsUserId = vtws_getWebserviceEntityId($module, $request->get('userid'));
		$wsStatus = vtws_changePassword($wsUserId, $oldPassword, $newPassword, $newPassword, $userModel);

		global $adb;
		$insertQuery = 'INSERT INTO vtiger_crmsetup (userid, setup_status) VALUES (?, ?)';
		$adb->pquery($insertQuery, array($request->get('userid'), '1'));

		if ($wsStatus['message']) {
			$content = 'Dear User,<br><br> 
						Your Password Has Been Changed By Admin 
						<br><br>
						Your New Password Is : ' . $newPassword . '
						<br><br><br>
						Regards,<br>
					CRM Support Team.<br>';

			$subject = 'Password Reset Is Done By Admin';

			include_once 'modules/Emails/mail.php';
			global $HELPDESK_SUPPORT_EMAIL_ID, $HELPDESK_SUPPORT_NAME;
			$status = send_mail('Users', $email, $HELPDESK_SUPPORT_NAME, $HELPDESK_SUPPORT_EMAIL_ID, $subject, $content, '', '', '', '', '', true);
			if ($status === 1 || $status === true) {
				$responseObject['message'] = "Password Reset Is Done And Email Has Been Sent Regarding Password Reset";
				$response->setResult($responseObject);
			} else {
				$response->setError('Outgoing mail server was not configured');
			}
		} else {
			$response->setError('Not Able To Reset User Password');
		}
		$response->emit();
	}
}
