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

class Mobile_WS_ChangePassword extends Mobile_WS_Controller {

    function process(Mobile_API_Request $request) {
        $response = new Mobile_API_Response();
        $useruniqueid = $request->get('useruniqueid');
        $OldPassword = $request->get('oldPassword');
        $repeatnewPassword = $request->get('newPassword');
        if (empty($OldPassword) || empty($repeatnewPassword)) {
            $response->setError(100, 'oldPassword Or newPassword Is Missing');
            return $response;
        }

        $response = new Mobile_API_Response();

        $current_user = $this->getActiveUser();
        $wsUserId = vtws_getWebserviceEntityId('Users', $useruniqueid);
        $result = vtws_changePassword($wsUserId, $OldPassword, $repeatnewPassword, $repeatnewPassword, $current_user);
        if ($result['message'] == 'Changed password successfully') {
            $responseObject['message'] = 'Changed password successfully';
            $response->setApiSucessMessage('Changed password successfully');
            $response->setResult($responseObject);
            return $response;
        } else {
            $response->setError(100, $result);
            return $response;
        }
    }
}
