<?php

class Mobile_WS_registerDeviceIdWithCRM extends Mobile_WS_Controller {

    function process(Mobile_API_Request $request) {
        $response = new Mobile_API_Response();
        $userId = $request->get('useruniqueid');
        $deviceId = $request->get('deviceId');
        $responseObject = [];
        if (empty($deviceId) || empty($userId)) {
            $responseObject['message'] = 'userid or deviceId is missing';
            $response->setError(1401, $responseObject);
            return $response;
        }
        global $adb;
        $sql = " select *  from vtiger_mobilenotifiaction where vtiger_mobilenotifiaction.userid = ?";
        $result = $adb->pquery($sql, array($userId));
        $no_of_rows = $adb->num_rows($result);
        if ($no_of_rows > 0) {
            $upSql = " Update vtiger_mobilenotifiaction set deviceid = ? where userId = ?  ";
            $adb->pquery($upSql, array($deviceId, $userId));
            $responseObject['message'] = "DeviceId is For User is Updated";
        } else {
            $inSql = " insert into vtiger_mobilenotifiaction(userid,deviceid) values (?,?)";
            $adb->pquery($inSql, array($userId, $deviceId));
            $responseObject['message'] = "Device is registered with CRM";
        }

        $response->setResult($responseObject);
        return $response;
    }

}