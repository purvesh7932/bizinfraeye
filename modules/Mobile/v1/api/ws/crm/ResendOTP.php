<?php
// require_once 'vtlib/Vtiger/Mailer.php';
class Mobile_WS_ResendOTP extends Mobile_WS_Controller {

    function requireLogin() {
        return false;
    }

    function process(Mobile_API_Request $request) {
        $current_user = CRMEntity::getInstance('Users');
        $current_user->id = $current_user->getActiveAdminId();
        $current_user->retrieve_entity_info($current_user->id, 'Users');
        $response = new Mobile_API_Response();
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
                $activeFields = $this->getActiveFields('ServiceEngineer', true);
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
            // $mail = new Vtiger_Mailer();
            // $mail->IsHTML();
            // $mail->Body = $content;
            // $mail->Subject = $subject;
            // $mail->AddAddress($emailId);
            // $status = $mail->Send(true);
            $status = send_mail('Users', $emailId, $HELPDESK_SUPPORT_NAME, $HELPDESK_SUPPORT_EMAIL_ID, $subject, $content, '', '', '', '', '', true);
                if ($status === 1 || $status === true) {
                    $responseObject['uid'] = $trackURL;
                    $response->setApiSucessMessage("OTP Has Sent To Registered Email");
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

    function fixUIType($module, $fieldname, $uitype) {
        if ($module == 'Contacts' || $module == 'Leads') {
            if ($fieldname == 'salutationtype') {
                return 16;
            }
        } else if ($module == 'Calendar' || $module == 'Events') {
            if ($fieldname == 'time_start' || $fieldname == 'time_end') {
                return 252;
            }
        }
        return $uitype;
    }
}
