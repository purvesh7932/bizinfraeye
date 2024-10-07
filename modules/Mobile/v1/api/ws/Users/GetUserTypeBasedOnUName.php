<?php
include_once 'include/Webservices/DescribeObject.php';
class Mobile_WS_GetUserTypeBasedOnUName extends Mobile_WS_Controller {

    function requireLogin() {
        return false;
    }

    function process(Mobile_API_Request $request) {
        $response = new Mobile_API_Response();
        $userName = trim($request->get('username'));
        if(empty($userName)){
            $response->setError(100, 'UserName is Empty');
            return $response;
        }
        global $adb;
        $found = false;
        $userType = '';
        if(is_numeric($userName)){
            $sql = 'select 1 from vtiger_users where user_name = ?';
            $sqlResult = $adb->pquery($sql, array(trim($userName)));
            $num_rows = $adb->num_rows($sqlResult);
            if($num_rows == 1){
                $userType = 'BEMLUSER';
                $found = true;
            } else {
                $found = false;
            }
        } else {
            $sql = "SELECT id, user_name,vtiger_contactdetails.usr_log_plat, vtiger_portalinfo.user_password,last_login_time, isactive, support_start_date, support_end_date, cryptmode FROM vtiger_portalinfo
			INNER JOIN vtiger_customerdetails ON vtiger_portalinfo.id=vtiger_customerdetails.customerid
			INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_portalinfo.id
			INNER JOIN vtiger_contactdetails ON vtiger_contactdetails.contact_no=vtiger_portalinfo.user_name
			WHERE vtiger_crmentity.deleted=0 AND user_name=? AND isactive=1 AND vtiger_customerdetails.portal=1";
            $sqlResult = $adb->pquery($sql, array(trim($userName)));
            $num_rows = $adb->num_rows($sqlResult);
            if($num_rows == 1){
                $userType = 'CUSTOMER';
                $found = true;
            } else {
                $found = false;
            }
        }
        $data = [];
        if($found == true){
            $data['userexits'] = true;
            $data['type'] = $userType;
            $response->setResult($data);
        } else {
            $data['userexits'] = false;
            $response->setResult($data);
        }
        $response->setApiSucessMessage('Successfully Fetched Data');

        return $response;
    }
}