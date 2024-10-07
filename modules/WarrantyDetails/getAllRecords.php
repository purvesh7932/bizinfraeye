<?php
function getAllRecords($entityData) {
    global $adb;
    include_once('include/utils/GeneralUtils.php');
    $url = getExternalAppURL('GeneralisedRFCCaller');

    $data = array(
        'rfcName'  => 'ZPM_CRM_MASTER_WARRANTY'
    );
    $header = array('Content-Type:multipart/form-data');
    $resource = curl_init();
    curl_setopt($resource, CURLOPT_URL, $url);
    curl_setopt($resource, CURLOPT_HTTPHEADER, $header);
    curl_setopt($resource, CURLOPT_POST, 1);
    curl_setopt($resource, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($resource, CURLOPT_POSTFIELDS, $data);
    curl_setopt($resource, CURLOPT_SSL_VERIFYPEER, 0);
    $responseUnEncoded = curl_exec($resource);
    $xml = json_decode($responseUnEncoded, true);

    foreach ($xml['IT_WARRANTY'] as $key => $value) {
        $sapRefNum = trim($value['MGANR']);
        $sapRefNum = trim(ltrim(trim($sapRefNum), '0'));
        $sql = 'select warrantydetailsid from vtiger_warrantydetails 
        INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_warrantydetails.warrantydetailsid
        where wr_warranty_code = ? and vtiger_crmentity.deleted = 0';
        $sqlResult = $adb->pquery($sql, array($sapRefNum));
        $num_rows = $adb->num_rows($sqlResult);
        if ($num_rows > 0) {
            $dataRow = $adb->fetchByAssoc($sqlResult, 0);
            $recordModel = Vtiger_Record_Model::getInstanceById($dataRow['warrantydetailsid'], 'WarrantyDetails');
            $recordModel->set('mode', 'edit');
            $recordModel->set('wr_warranty_description', trim($value['GAKTX']));
            $recordModel->save();
        } else {
            $focus = CRMEntity::getInstance('WarrantyDetails');
            $focus->column_fields['wr_warranty_code'] = $sapRefNum;
            $focus->column_fields['wr_warranty_description'] = trim($value['GAKTX']);
            $focus->save("WarrantyDetails");
        }
    }
}
