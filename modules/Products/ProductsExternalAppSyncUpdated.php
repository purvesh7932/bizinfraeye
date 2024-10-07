<?php
function ProductsExternalAppSyncUpdated($entityData) {
    global $adb;
    include_once('include/utils/GeneralUtils.php');
    $url = getExternalAppURL('getAllMaterialMasterUpdated');
    $xml = file_get_contents($url);
    $xml = json_decode($xml);
    foreach ($xml as $key => $value) {
        $sapRefNum = trim($value->{'MATNR'});
        $sql = 'select productid from vtiger_products where productname = ?';
        $sqlResult = $adb->pquery($sql, array($sapRefNum));
        $num_rows = $adb->num_rows($sqlResult);
        if ($num_rows > 0) {
            $dataRow = $adb->fetchByAssoc($sqlResult, 0);
            $recordModel = Vtiger_Record_Model::getInstanceById($dataRow['productid'], 'Products');
            $recordModel->set('mode', 'edit');
            $recordModel->set('description', trim($value->{'MAKTX'}));

            $sql = "select maintenanceplantid,plant_name from vtiger_maintenanceplant where plant_code = ? ";
            $result = $adb->pquery($sql, array(trim($value->{'WERKS'})));
            $dataRow = $adb->fetchByAssoc($result, 0);
            if (empty($dataRow['maintenanceplantid'])) {
                $recordModel->set('plant_name', '');
            } else {
                $recordModel->set('plant_name', $dataRow['maintenanceplantid']);
                $plantName = $dataRow['plant_name'];
                $sql = "select groupid from vtiger_groups where groupname = ? ";
                $result = $adb->pquery($sql, array($plantName));
                $dataRow = $adb->fetchByAssoc($result, 0);
                if (empty($dataRow['groupid'])) {
                } else {
                    $recordModel->set('assigned_user_id', $dataRow['groupid']);
                }
            }
            $recordModel->save();
        } else {
            $focus = CRMEntity::getInstance('Products');
            $focus->column_fields['description'] = trim($value->{'MAKTX'});
            $focus->column_fields['productname'] = $sapRefNum;
            $focus->column_fields['discontinued'] = 1;
            $sql = "select maintenanceplantid,plant_name from vtiger_maintenanceplant where plant_code = ? ";
            $result = $adb->pquery($sql, array(trim($value->{'WERKS'})));
            $dataRow = $adb->fetchByAssoc($result, 0);
            if (empty($dataRow['maintenanceplantid'])) {
                $focus->column_fields['plant_name'] = '';
            } else {
                $focus->column_fields['plant_name'] = $dataRow['maintenanceplantid'];
                $plantName = $dataRow['plant_name'];
                $sql = "select groupid from vtiger_groups where groupname = ? ";
                $result = $adb->pquery($sql, array($plantName));
                $dataRow = $adb->fetchByAssoc($result, 0);
                if (empty($dataRow['groupid'])) {
                } else {
                    $focus->column_fields['assigned_user_id'] = $dataRow['groupid'];
                }
            }
            $focus->save("Products");
        }
    }
}
