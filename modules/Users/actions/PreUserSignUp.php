<?php
include_once('include/utils/GeneralUtils.php');
class Users_PreUserSignUp_Action extends Vtiger_Action_Controller {

    function loginRequired() {
        return false;
    }

    function checkPermission(Vtiger_Request $request) {
        return true;
    }

    function process(Vtiger_Request $request) {
        $emailId = vtlib_purify($request->get('email'));
        $responseObject = [];
        $response = new Vtiger_Response();
        $badgeNo = $request->get('badge_no');
        $mobileNo = $request->get('phone');
        $badgeNoAndMobile = IGisBadgeExits($request->get('badge_no'), $request->get('phone'));
        if (!empty($badgeNoAndMobile)) {
            if (isset($badgeNoAndMobile['badge_no']) && !empty($badgeNoAndMobile['badge_no']) && $badgeNo == $badgeNoAndMobile['badge_no']) {
                $response->setError(100, "Badge Number Already Exits");
                return $response;
            } else if (isset($badgeNoAndMobile['phone']) && !empty($badgeNoAndMobile['phone']) && $mobileNo == $badgeNoAndMobile['phone']) {
                $response->setError(100, "Mobile Number Already Exits");
                return $response;
            }
        }
        $RMandRSMCheck = RMandRSMCheck($request);
        if ($RMandRSMCheck == true) {
            $response->setError(100, "User " . $request->get('sub_service_manager_role') . " Is Already Registered In " . $request->get('regional_office'));
            return $response;
        }
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
            $activeFields = $this->getActiveFields('ServiceEngineer', true);
            $activeFieldKeys = array_keys($activeFields);
            foreach ($activeFieldKeys as $activeFieldKey) {
                if ($activeFieldKey == 'assigned_user_id') {
                    $handler_data['assigned_user_id'] = 1;
                } else {
                    $handler_data[$activeFieldKey] = $request->get($activeFieldKey);
                    if ($activeFieldKey == 'confirm_password' || $activeFieldKey == 'user_password') {
                        $handler_data[$activeFieldKey] = Vtiger_Functions::toProtectedText($handler_data[$activeFieldKey]);
                    }
                }
            }
            $handler_data['time'] = strtotime("+15 Minute");
            $handler_data['hash'] = md5($emailId . $time);
            $handler_data['otp'] = $otp;
            $options['handler_data'] = $handler_data;
            $trackURL = Vtiger_ShortURL_Helper::generateURLMobile($options);
            $saveTimeZone = date_default_timezone_get();
            date_default_timezone_set('Asia/Kolkata');
            $content = 'Dear User,<br><br> 
                 Here is your OTP for Mobile Number verification : ' . $otp . '
                <br><br> 
                This request was made on ' . date("d/m/Y h:i:s a") . ' and will expire in next 15 Minute.<br><br> 
                Regards,<br> 
                CRM Support Team.<br>';
            $subject = 'CCHS: OTP Verification';
            date_default_timezone_set($saveTimeZone);
            vimport('~~/modules/Emails/mail.php');
            global $HELPDESK_SUPPORT_EMAIL_ID, $HELPDESK_SUPPORT_NAME;
            $status = send_mail('Users', $emailId, $HELPDESK_SUPPORT_NAME, $HELPDESK_SUPPORT_EMAIL_ID, $subject, $content, '', '', '', '', '', true);
            if ($status === 1 || $status === true) {
                $responseObject['message'] = "OTP has sent to registered email";
                $responseObject['uid'] = $trackURL;
            } else {
                $responseObject['message'] = "Not Able to send email";
            }
        } else {
            $response->setError('Email is required to send OTP');
        }
        $response->setResult($responseObject);
        $response->emit();
    }

    function getActiveFields($module, $withPermissions = false) {
        $activeFields = Vtiger_Cache::get('CustomerPortal', 'activeFields');
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

    function isViewable($fieldName, $module) {
        global $db;
        $db = PearDatabase::getInstance();
        $tabidSql = "SELECT tabid from vtiger_tab WHERE name = ?";
        $tabidResult = $db->pquery($tabidSql, array($module));
        if ($db->num_rows($tabidResult)) {
            $tabId = $db->query_result($tabidResult, 0, 'tabid');
        }
        $presenceSql = "SELECT presence,displaytype FROM vtiger_field WHERE fieldname=? AND tabid = ?";
        $presenceResult = $db->pquery($presenceSql, array($fieldName, $tabId));
        $num_rows = $db->num_rows($presenceResult);
        if ($num_rows) {
            $fieldPresence = $db->query_result($presenceResult, 0, 'presence');
            $displayType = $db->query_result($presenceResult, 0, 'displaytype');
            if ($fieldPresence == 0 || $fieldPresence == 2 && $displayType !== 4) {
                return true;
            } else {
                return false;
            }
        }
    }
}
