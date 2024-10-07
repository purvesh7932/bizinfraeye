<?php
class Mobile_WS_GetProfileInfo extends Mobile_WS_Controller {

    function process(Mobile_API_Request $request) {
        global $adb;
        $response = new Mobile_API_Response();
        $current_user = $this->getActiveUser();
        $userName = $current_user->user_name;

        $recordModel = Vtiger_Record_Model::getInstanceById($current_user->id, 'Users');
        $imageObject = $recordModel->getImageDetails();
        $imageArray = $imageObject[0];
        $imageName = $imageArray['url'];
        
        $sql = 'select serviceengineerid from vtiger_serviceengineer ' .
            ' inner join vtiger_crmentity on vtiger_crmentity.crmid = vtiger_serviceengineer.serviceengineerid' .
            ' where badge_no = ? and vtiger_crmentity.deleted= 0 ORDER BY serviceengineerid DESC LIMIT 1';
        $sqlResult = $adb->pquery($sql, array($userName));
        $dataRow = $adb->fetchByAssoc($sqlResult, 0);

        if (empty($dataRow)) {
            $response->setError(100, 'Error In Finding The Profile Information');
            return $response;
        } else {
            $recordModel = Vtiger_Record_Model::getInstanceById($dataRow['serviceengineerid'], 'ServiceEngineer');
            $data = $recordModel->getData();
            $response->setApiSucessMessage('Successfully Fetched Data');
            unset($data['confirm_password']);
            unset($data['user_password']);
            $data['imagename'] = $imageName;
            $data['date_of_birth'] = Vtiger_Date_UIType::getDisplayDateValue($data['date_of_birth']);
            $data['date_of_joining'] = Vtiger_Date_UIType::getDisplayDateValue($data['date_of_joining']);
            $response->setResult(array('profileInfo' => $data));
            return $response;
        }
    }
}
