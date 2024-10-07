<?php
include_once 'vtlib/Vtiger/Module.php';
include_once "include/utils/VtlibUtils.php";
include_once "include/utils/CommonUtils.php";
include_once "includes/Loader.php";
include_once 'includes/runtime/BaseModel.php';
include_once "includes/http/Request.php";
include_once "include/Webservices/Custom/ChangePassword.php";
include_once "include/Webservices/Utils.php";
include_once "includes/runtime/EntryPoint.php";
vimport('includes.runtime.EntryPoint');

class Mobile_WS_ResetPassword extends Mobile_WS_Controller {

    function requireLogin() {
        return false;
    }

    function process(Mobile_API_Request $request) {
        $response = new Mobile_API_Response();
        $id = $request->get('uid');
        $otp = $request->get('otp');
        $newPassword = $request->get('newPassword');
        $repeatnewPassword = $request->get('repeatnewPassword');
        if (empty($newPassword) || empty($repeatnewPassword)) {
            $response->setError(100, 'NewPassword Or RepeatnewPassword Is Missing');
            return $response;
        }
        if ($newPassword != $repeatnewPassword) {
            $response->setError(100, 'NewPassword And RepeatnewPassword Is Mot Matching');
            return $response;
        }
        $status = Vtiger_ShortURL_Helper::handleForgotPasswordMobile(vtlib_purify($id));
        if ($status == true) {
            $response = new Mobile_API_Response();
            $shortURLModel = Vtiger_ShortURL_Helper::getInstance($id);
            $userId = $shortURLModel->handler_data['id'];
            $otpFromDataBase = $shortURLModel->handler_data['otp'];
            if ($otp == $otpFromDataBase) {
                $sentTime = $shortURLModel->handler_data['time'];
                $now = strtotime("Now");
                if ($now >  $sentTime) {
                    $response->setError(100, "OTP is Expired");
                    return $response;
                } else {
                    $user = Users::getActiveAdminUser();
                    $wsUserId = vtws_getWebserviceEntityId('Users', $userId);
                    $result = vtws_changePassword($wsUserId, '', $newPassword, $repeatnewPassword, $user);
                    if ($result['message'] == 'Changed password successfully') {
                        $responseObject['message'] = 'Changed password successfully';
                        $response->setApiSucessMessage('Changed password successfully');
                        $response->setResult($responseObject);
                        $shortURLModel->delete();
                        return $response;
                    } else {
                        $response->setError(100,$result);
                        return $response;
                    }
                }
            } else {
                $response->setError(100, 'OTP Is Invalid');
                return $response;
            }
        } else {
            $response->setError(100, 'UID Is Invalid');
            return $response;
        }
    }
}
