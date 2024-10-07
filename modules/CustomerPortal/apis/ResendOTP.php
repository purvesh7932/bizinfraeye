<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
class CustomerPortal_ResendOTP extends CustomerPortal_API_Abstract {

    function authenticatePortalUser($username, $password) {
		return true;
	}

    function process(CustomerPortal_API_Request $request) {
        $current_user = CRMEntity::getInstance('Users');
        $current_user->id = $current_user->getActiveAdminId();
        $current_user->retrieve_entity_info($current_user->id, 'Users');
        $response = new CustomerPortal_API_Response();
        $uid = $request->get('uid');
        $status = Vtiger_ShortURL_Helper::handleForgotPasswordMobile(vtlib_purify($uid));
        if ($status == false) {
            $response->setError(100, "UID Is Invalid");
            return $response;
        } else {
            $shortURLModel = Vtiger_ShortURL_Helper::getInstance($uid);
            $emailId = vtlib_purify($shortURLModel->handler_data['email']);
            $responseObject = [];
            if (!empty($emailId)) {
                $time = time();
                $otp = substr(number_format(time() * rand(), 0, '', ''), 0, 6);
                $options = array(
                    'handler_path' => 'modules/Users/handlers/ForgotPassword.php',
                    'handler_class' => 'Users_ForgotPassword_Handler',
                    'handler_function' => 'changePassword',
                    'onetime' => 0
                );
                $handler_data = [];
                $activeFields = CustomerPortal_Utils::getActiveFields('Contacts', true);
                $activeFieldKeys = array_keys($activeFields);
                foreach ($activeFieldKeys as $activeFieldKey) {
                    if ($activeFieldKey == 'assigned_user_id') {
                        $handler_data['assigned_user_id'] = 1;
                    } else {
                        $handler_data[$activeFieldKey] = $shortURLModel->handler_data[$activeFieldKey];
                    }
                }
                $handler_data['time'] = strtotime("+15 Minute");
                $handler_data['hash'] = md5($emailId . $time);
                $handler_data['otp'] = $otp;
                $handler_data['badgeNo'] = $shortURLModel->handler_data['badgeNo'];
                $handler_data['id'] = $shortURLModel->handler_data['id'];
                $options['handler_data'] = $handler_data;
                $trackURL = Vtiger_ShortURL_Helper::generateURLMobile($options);
                $content = 'Dear User,<br><br> 
                            Here is your OTP for Mobile Number verification : ' . $otp . '
                            <br><br> 
                            This request was made on ' . date("d/m/Y h:i:s a") . ' and will expire in next 15 Minute.<br><br> 
                            Regards,<br> 
                            CRM Support Team.<br>';
                $subject = 'CCHS: OTP Verification';
                vimport('~~/modules/Emails/mail.php');
                global $HELPDESK_SUPPORT_EMAIL_ID, $HELPDESK_SUPPORT_NAME;
                $status = send_mail('Users', $emailId, $HELPDESK_SUPPORT_NAME, $HELPDESK_SUPPORT_EMAIL_ID, $subject, $content, '', '', '', '', '', true);
                if ($status === 1 || $status === true) {
                    $responseObject['uid'] = $trackURL;
                    // $response->setApiSucessMessage("OTP Has Sent To Registered Email");
                    $shortURLModel->delete();
                    $response->setResult($responseObject);
                } else {
                    $response->setError(100, 'Not Able To Send Email');
                }
            } else {
                $response->setError(100, 'Phone Number Or Email is Required To Send OTP');
            }
            return $response;
        }
    }
}
