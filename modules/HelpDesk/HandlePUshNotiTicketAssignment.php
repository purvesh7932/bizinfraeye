<?php
function HandlePUshNotiTicketAssignment($entityData) {
    global $adb;
    $recordInfo = $entityData->{'data'};
    $id = $recordInfo['id'];
    $id = explode('x', $id);
    $id = $id[1];

    $assigned_user_id = $recordInfo['assigned_user_id'];
    $assigned_user_id = explode('x', $assigned_user_id);
    $assigned_user_id = $assigned_user_id[1];

    $deviceId = getUserDeviceIdONAssignment($assigned_user_id);

    if (empty($deviceId)) {
        return;
    }

    $recordModel = Vtiger_Record_Model::getInstanceById($assigned_user_id, 'Users');
    $userLabel = $recordModel->get('last_name');
    $userMobileNo = $recordModel->get('phone_mobile');

    $url = 'https://fcm.googleapis.com/fcm/send';
    $api_key = 'AAAAk_UsrDE:APA91bHX19RWAvW4hL9J-228deIqZR91Cw7AhhK1JHiWPnmfR6RLo2JUhUqh87vsbmZMYKm8aJMIy8RnkmfmI_9W1oZzmzbjqZEK6JqECG07KT1f5ZYqWnw7yaENm1Sr4tB4_BI5WsLo';
    // $deviceId = 'f-YlezrGQAG8UbxiJPsaWh:APA91bFcEMXq1NQ9OjD9zxF8Y6fEwAaj-fpnO0MaWeUemuHW4FvF2t6QGHOMU5JE_u385jAAztkpUebW5XPxGrhlw7L7vfQJgWaTE2asGIXkOSqFQG21QSW1FyxKt4KYa00I104tU4On';
    $fields = array(
        'registration_ids' => array(
            $deviceId
        ),
        'notification' => array(
            'title' => "SR Assigned",
            'body' => "Service Engineer $userLabel ($userMobileNo) is assigned"
        ),
        'priority' => 'high',
    );
    $headers = array(
        'Content-Type:application/json',
        'Authorization:key=' . $api_key
    );
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
    $result = curl_exec($ch);
    if ($result === FALSE) {
        // die('FCM Send Error: ' . curl_error($ch));
    }
    curl_close($ch);
    if ($result) {
        $result = json_decode($result, true);
    }
    if ($result['success'] == 1) {
        // print_r($fields);
        // die();
        // die("message sent");
    }
}

function getUserDeviceIdONAssignment($userId) {
    global $adb;
    $sql = 'SELECT deviceid FROM vtiger_mobilenotifiaction WHERE userid=?';
    $result = $adb->pquery($sql, array($userId));
    $purchaseOrderStatus = $adb->query_result($result, 0, "deviceid");
    return $purchaseOrderStatus;
}
