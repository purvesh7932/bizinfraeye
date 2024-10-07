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
class CustomerPortal_PreResetPassword extends CustomerPortal_API_Abstract {

	function process(CustomerPortal_API_Request $request) {
		$response = new CustomerPortal_API_Response();
		$badgeNo = $request->get('badgeNo');
		if (empty($badgeNo)) {
			$response->setError(100, 'Badge Number Is Required');
			return $response;
		}
		$emailFromRequest = $request->get('email');
		if (empty($emailFromRequest)) {
			$response->setError(100, 'Email Is Required');
			return $response;
		}
		global $adb;
		$IpAddress = $this->getClientIp() . date("YmdH");
		$sql = " select count(*) as 'count' from vtiger_shorturls "
			. " where ip_address = ?";
		$result = $adb->pquery($sql, array($IpAddress));
		$dataRow = $adb->fetchByAssoc($result, 0);
		$numberOfattempts = (int) $dataRow['count'];
		if ($numberOfattempts > 10) {
			$response->setError(100, 'Number of Password Reset Attempt is Exceeded');
			return $response;
		}
		if (!empty($badgeNo)) {
			$usercreatedid = '';
			$email = '';
			$useruniqeid = '';
			$id = '';
			$mobile = '';
			$badgeNo = vtlib_purify($badgeNo);
			$sql = 'SELECT id, user_name,email,mobile,vtiger_contactdetails.usr_log_plat, vtiger_portalinfo.user_password,last_login_time, isactive, support_start_date, support_end_date, cryptmode FROM vtiger_portalinfo
			INNER JOIN vtiger_customerdetails ON vtiger_portalinfo.id=vtiger_customerdetails.customerid
			INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_portalinfo.id
			INNER JOIN vtiger_contactdetails ON vtiger_contactdetails.contact_no=vtiger_portalinfo.user_name
			WHERE vtiger_crmentity.deleted=0 AND user_name=? AND isactive=1 AND vtiger_customerdetails.portal=1';
			$result = $adb->pquery($sql, array($badgeNo));

			if ($adb->num_rows($result) == 1) {
				$email = $adb->query_result($result, 0, 'email');
				$id = $adb->query_result($result, 0, 'id');
				$mobile = $adb->query_result($result, 0, 'mobile');
				$usercreatedid =  $adb->query_result($result, 0, 'id');
			} else {
				$response->setError(100, 'Unable To Find User');
				return $response;
			}
			if (!empty($email)) {
				if ($email != $emailFromRequest) {
					$response->setError(100, 'Email Associated With Badge Number Is Not Matching');
					return $response;
				}
				$time = time();
				$otp = substr(number_format(time() * rand(), 0, '', ''), 0, 6);
				$options = array(
					'handler_path' => 'modules/Users/handlers/ForgotPassword.php',
					'handler_class' => 'Users_ForgotPassword_Handler',
					'handler_function' => 'changePassword',
					'onetime' => 0
				);
				$handler_data['time'] = strtotime("+15 Minute");
				$handler_data['hash'] = md5($badgeNo . $time);
				$handler_data['otp'] = $otp;
				$handler_data['badgeNo'] = $badgeNo;
				$handler_data['email'] = $emailFromRequest;
				$handler_data['id'] = $id;
				$options['handler_data'] = $handler_data;
				$trackURL = Vtiger_ShortURL_Helper::generateURLMobile($options);
				$content = 'Dear User,<br><br> 
                You recently requested a password reset for your CRM Account.<br> 
                To create a new password, Here is your OTP ' . $otp . '
                <br><br> 
                This request was made on ' . date("d/m/Y h:i:s a")  . ' and will expire in next 15 Minutes.<br><br> 
                Regards,<br> 
                CRM Support Team.<br>';

				$subject = 'CRM: Password Reset';
				vimport('~~/modules/Emails/mail.php');
				global $HELPDESK_SUPPORT_EMAIL_ID, $HELPDESK_SUPPORT_NAME;
				$status = send_mail('Users', $email, $HELPDESK_SUPPORT_NAME, $HELPDESK_SUPPORT_EMAIL_ID, $subject, $content, '', '', '', '', '', true);
				if ($status === 1 || $status === true) {
					$responseObject['uid'] = $trackURL;
					$responseObject['usermobilenumber'] = $mobile;
					$date = new DateTime();
					$responseObject['usercreatedid'] = $usercreatedid;
					$responseObject['useruniqeid'] = $id;
					$responseObject['timestamp'] = $date->getTimestamp();
					$responseObject['usertype'] = 'BEMLUSER';
					$responseObject['message'] = 'OTP Has Sent To Registered Email';
					$response->setResult($responseObject);
					$result = $adb->pquery('update vtiger_shorturls set ip_address = ? where uid= ? ', array($IpAddress, $trackURL));
					return $response;
				} else {
					$response->setError(100, 'Not Able To Send Email');
					return $response;
				}
			} else {
				$response->setError(100, 'Unable To Find Email Of The User');
				return $response;
			}
		} else {
			$response->setError(100, 'Email Id Is Required To Send OTP');
			return $response;
		}
	}

	function getClientIp() {
		$ipaddress = '';
		if (getenv('HTTP_CLIENT_IP'))
			$ipaddress = getenv('HTTP_CLIENT_IP');
		else if (getenv('HTTP_X_FORWARDED_FOR'))
			$ipaddress = getenv('HTTP_X_FORWARDED_FOR');
		else if (getenv('HTTP_X_FORWARDED'))
			$ipaddress = getenv('HTTP_X_FORWARDED');
		else if (getenv('HTTP_FORWARDED_FOR'))
			$ipaddress = getenv('HTTP_FORWARDED_FOR');
		else if (getenv('HTTP_FORWARDED'))
			$ipaddress = getenv('HTTP_FORWARDED');
		else if (getenv('REMOTE_ADDR'))
			$ipaddress = getenv('REMOTE_ADDR');
		else
			$ipaddress = 'UNKNOWN';

		return $ipaddress;
	}

	function authenticatePortalUser($username, $password) {
		return true;
	}
}
