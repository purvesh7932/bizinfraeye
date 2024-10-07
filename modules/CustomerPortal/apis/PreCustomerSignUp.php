<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
require_once 'includes/main/WebUI.php';
require_once 'include/utils/utils.php';
require_once 'include/utils/VtlibUtils.php';
require_once 'modules/Vtiger/helpers/ShortURL.php';
require_once 'vtlib/Vtiger/Mailer.php';
include_once 'include/Webservices/DescribeObject.php';
class CustomerPortal_PreCustomerSignUp extends CustomerPortal_API_Abstract {

	function process(CustomerPortal_API_Request $request) {
		$response = new CustomerPortal_API_Response();
		$emailId = vtlib_purify($request->get('email'));
		$responseObject = [];
		global $adb;
		if (!empty($emailId)) {
			$options = array(
				'handler_path' => 'modules/Users/handlers/ForgotPassword.php',
				'handler_class' => 'Users_ForgotPassword_Handler',
				'handler_function' => 'changePassword',
				'onetime' => 0
			);
			$handler_data = [];
			$allData = [];
			$activeFields = CustomerPortal_Utils::getActiveFields('Contacts', true);
			$activeFieldKeys = array_keys($activeFields);
			$current_user = CRMEntity::getInstance('Users');
			$current_user->id = $current_user->getActiveAdminId();
			$current_user->retrieve_entity_info($current_user->id, 'Users');
			$describeInfo = vtws_describe('Contacts', $current_user);
			foreach ($activeFieldKeys as $activeFieldKey) {
				foreach ($describeInfo['fields'] as $key => $value) {
					if (in_array($value['name'], $activeFieldKeys)) {
						if ($value['mandatory'] == 1 && empty($request->get($value['name']))) {
							$response->setError(100, 'Mandatory Field - ' . $value['label'] . ' Is Missing');
							return $response;
						}
					}
				}
				if ($activeFieldKey == 'assigned_user_id') {
					$handler_data['assigned_user_id'] = 1;
				} else {
					$handler_data[$activeFieldKey] = $request->get($activeFieldKey);
					$allData[$activeFieldKey] = $request->get($activeFieldKey);
				}
			}
			$time = strtotime("+15 Minute");
			$otp = substr(number_format(time() * rand(), 0, '', ''), 0, 6);
			$handler_data['time'] = $time;
			$handler_data['hash'] = md5($emailId . $time);
			$handler_data['otp'] = $otp;
			$options['handler_data'] = $handler_data;
			$trackURL = Vtiger_ShortURL_Helper::generateURLMobile($options);
			$content = 'Dear User,<br><br> 
                 Here is your OTP for Mobile Number verification : ' . $otp . '
                <br><br> 
                This request was made on ' . date("d/m/Y h:i:s a")  . ' and will expire in next 15 Minute.<br><br> 
                Regards,<br> 
                CRM Support Team.<br>';

			$subject = 'CCHS: OTP Verification';

			// $mail = new Vtiger_Mailer();
			// $mail->IsHTML();
			// $mail->Body = $content;
			// $mail->Subject = $subject;
			// $mail->AddAddress($emailId);
			// $status = $mail->Send(true);
			vimport('~~/modules/Emails/mail.php');
            global $HELPDESK_SUPPORT_EMAIL_ID, $HELPDESK_SUPPORT_NAME;
            $status = send_mail('Users', $emailId, $HELPDESK_SUPPORT_NAME, $HELPDESK_SUPPORT_EMAIL_ID, $subject, $content, '', '', '', '', '', true);
			if ($status === 1 || $status === true) {
				$responseObject['message'] = "OTP has sent to registered email";
				$responseObject['uid'] = $trackURL;
				$responseObject = array_merge($responseObject, $allData);
				$response->setResult($responseObject);
				return $response;
			} else {
				$response->setError(100, "Not Able To Send Email");
				return $response;
			}
		} else {
			$response->setError(100, "Email Is Required To Send OTP");
			return $response;
		}
	}

	function authenticatePortalUser($username, $password) {
		return true;
	}
}
