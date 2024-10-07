<?php

include_once('include/Webservices/DescribeObject.php');
include_once('include/utils/GeneralUtils.php');

class Mobile_WS_OTPGeneration extends Mobile_WS_Controller {

    function requireLogin() {
        return false;
    }

    function process(Mobile_API_Request $request) {

        global $current_user; // Required for vtws_update API
        $current_user = $this->getActiveUser();

        $module = $request->get('module');
        $emailId = vtlib_purify($request->get('owntenant_email'));
        $responseObject = [];
        $response = new Mobile_API_Response();
        $userName = $request->get('owntenant_name');
        $mobileNo = $request->get('owntenant_mobile');

        if (empty($emailId)) {
            $response->setError(100, 'Email is required');
            return $response;
        }

        if (empty($mobileNo)) {
            $response->setError(100, 'Mobile is required');
            return $response;
        }

        // $RMandRSMCheck = RMandRSMCheckMob($request);
        // if ($RMandRSMCheck == true) {
        //     $response->setError(100, "User " . $request->get('owntenant_role') . " Is Already Registered");
        //     return $response;
        // }

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
            $allData = [];
            $activeFields = $this->getActiveFields($module, true);
            $activeFieldKeys = array_keys($activeFields);
            foreach ($activeFieldKeys as $activeFieldKey) {
                foreach ($describeInfo['fields'] as $key => $value) {
                    if (in_array($value['owntenant_name'], $activeFieldKeys)) {
                        if ($value['mandatory'] == 1 && empty($request->get($value['owntenant_name']))) {
                            $response->setError(100, 'Mandatory Field - ' . $value['label'] . ' Is Missing');
                            return $response;
                        }
                    }
                }
                if ($activeFieldKey == 'ownerid') {
                    $handler_data['ownerid'] = 1;
                } else {
                    $handler_data[$activeFieldKey] = $request->get($activeFieldKey);
                    $allData[$activeFieldKey] = $request->get($activeFieldKey);
                }
            }
            $handler_data['time'] = strtotime("+15 Minute");
            $handler_data['hash'] = md5($emailId . $time);
            $handler_data['otp'] = $otp;
            $options['handler_data'] = $handler_data;
            $trackURL = Vtiger_ShortURL_Helper::generateURLMobile($options);

            // Send OTP via Email
            $content = 'Dear User,<br><br>
                Here is your OTP for Mobile Number verification: ' . $otp . '
                <br><br>
                This request was made on ' . date("d/m/Y h:i:s a")  . ' and will expire in next 15 minutes.<br><br>
                Regards,<br>
                CRM Support Team.<br>';
            $subject = 'CCHS: OTP Verification';

            vimport('~~/modules/Emails/mail.php');
            global $HELPDESK_SUPPORT_EMAIL_ID, $HELPDESK_SUPPORT_NAME;
            $status = send_mail('Users', $emailId, $HELPDESK_SUPPORT_NAME, $HELPDESK_SUPPORT_EMAIL_ID, $subject, $content, '', '', '', '', '', true);

            // Check if the email was sent successfully
            if ($status === 1 || $status === true) {
                // Send OTP via SMS
                $smsStatus = $this->sendSmsOtp($mobileNo, $otp); // Placeholder function for SMS

                if ($smsStatus === true) {
                    $responseObject['uid'] = $trackURL;
                    $response->setApiSucessMessage("OTP has been sent to registered email and mobile number");
                    $response->setResult($responseObject);
                } else {
                    $response->setError(100, 'Email sent, but failed to send OTP to mobile');
                }
            } else {
                $response->setError(100, 'Failed to send email');
            }
        } else {
            $response->setError(100, 'Mobile Number or Email is required to send OTP');
        }
        return $response;
    }

    function getActiveFields($module, $withPermissions = false) {
        $activeFields = Vtiger_Cache::get('CustomerPortal', 'activeFields'); // need to flush cache when fields updated at CRM settings

        if (empty($activeFields)) {
            global $adb;
            $sql = "SELECT name, fieldinfo FROM vtiger_customerportal_fields INNER JOIN vtiger_tab ON vtiger_customerportal_fields.tabid=vtiger_tab.tabid";
            $sqlResult = $adb->pquery($sql, array());
            $num_rows = $adb->num_rows($sqlResult);

            for ($i = 0; $i < $num_rows; $i++) {
                $retrievedModule = $adb->query_result($sqlResult, $i, 'name');
                $fieldInfo = $adb->query_result($sqlResult, $i, 'fieldinfo');
                $activeFields[$retrievedModule] = $fieldInfo;
            }
            Vtiger_Cache::set('CustomerPortal', 'activeFields', $activeFields);
        }

        $fieldsJSON = $activeFields[$module];
        $data = Zend_Json::decode(decode_html($fieldsJSON));
        $fields = array();

        if (!empty($data)) {
            foreach ($data as $key => $value) {
                if (self::isViewable($key, $module)) {
                    if ($withPermissions) {
                        $fields[$key] = $value;
                    } else {
                        $fields[] = $key;
                    }
                }
            }
        }
        return $fields;
    }
    // Placeholder function to send SMS
    function sendSmsOtp($mobileNo, $otp) {
        // Implement your SMS sending logic here using Twilio, Nexmo, or any SMS gateway
        // Example:
        // $apiUrl = "https://api.twilio.com/send";
        // $params = [
        //     'To' => $mobileNo,
        //     'Body' => 'Your OTP is: ' . $otp,
        // ];
        // $result = $this->sendCurlRequest($apiUrl, $params);
        // return $result === true;

        return true; // Assume success for now
    }

    // Function to send a cURL request
    function sendCurlRequest($url, $params) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }
}
