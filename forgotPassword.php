<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */
include_once 'vtlib/Vtiger/Module.php';
require_once 'includes/main/WebUI.php';
require_once 'include/utils/utils.php';
require_once 'include/utils/VtlibUtils.php';
require_once 'modules/Vtiger/helpers/ShortURL.php';
require_once 'vtlib/Vtiger/Mailer.php';
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
global $adb;
$adb = PearDatabase::getInstance();
$response = new Vtiger_Response();
if (isset($_REQUEST['username']) && isset($_REQUEST['emailId'])) {
	$username = vtlib_purify($_REQUEST['username']);
	$result = $adb->pquery('select email1 from vtiger_users where user_name= ? ', array($username));
	if ($adb->num_rows($result) > 0) {
		$email = $adb->query_result($result, 0, 'email1');
	}
	
	if (vtlib_purify($_REQUEST['emailId']) == $email) {
		$time = time();
		$options = array(
			'handler_path' => 'modules/Users/handlers/ForgotPassword.php',
			'handler_class' => 'Users_ForgotPassword_Handler',
			'handler_function' => 'changePassword',
			'handler_data' => array(
				'username' => $username,
				'email' => $email,
				'time' => $time,
				'hash' => md5($username.$time)
			)
		);
		$trackURL = Vtiger_ShortURL_Helper::generateURL($options);
		$content = 'Dear User,<br><br> 
					You recently requested a password reset for your CRM  Account.<br> 
					To create a new password, click on the link <a target="_blank" href='.$trackURL.'>here</a>. 
					<br><br> 
					This request was made on '. date("d/m/Y h:i:s a")  .' and will expire in next 24 hours.<br><br> 
					Regards,<br> 
					CRM Support Team.<br>';

		$subject = 'Password Reset';

		// $mail = new Vtiger_Mailer();
		// $mail->IsHTML();
		// $mail->Body = $content;
		// $mail->Subject = $subject;
		// $mail->AddAddress($email);

		// $status = $mail->Send(true);
		include_once 'modules/Emails/mail.php';
		global $HELPDESK_SUPPORT_EMAIL_ID, $HELPDESK_SUPPORT_NAME;
		$status = send_mail('Users', $email, $HELPDESK_SUPPORT_NAME, $HELPDESK_SUPPORT_EMAIL_ID, $subject, $content, '', '', '', '', '', true);
		if ($status === 1 || $status === true) {
			// header('Location:  index.php?modules=Users&view=Login&mailStatus=success');
			$responseObject['message'] = "OTP has sent to registered email";
			$response->setResult($responseObject);
		} else {
			$response->setError('Outgoing mail server was not configured');
		}
	} else {
		$response->setError('Email Associated With Badge Number Is Not Matching');
		// header('Location:  index.php?modules=Users&view=Login&error=fpError');
	}
	$response->emit();
}
