<?php
include_once('include/utils/GeneralUtils.php');
function ExternalAppSyncUpdated($entityData) {
    global $adb, $triggerOnceServiceOrders;

    if ($triggerOnceServiceOrders == true) {
        return;
    } else {
        $triggerOnceServiceOrders = true;
    }
    include_once('include/utils/GeneralUtils.php');
    $url = getExternalAppURL('GeneralisedRFCCaller');
    $data = array(
        'rfcName'  => 'ZPM_CRM_SERVORD_RFC',
        'IM_DATE'  => date("Ymd"),
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
    curl_close($resource);
    foreach ($xml['IT_SERVORDER'] as $key => $value) {
        $sql = "insert into vtiger_temp_so_table(soid,qty,part_number) values (?,?,?)";
        $adb->pquery(
            $sql,
            array(
                trim($value['AUFNR']),
                (float) trim($value['BDMNG']),
                trim($value['MATNR'])
            )
        );
    }

    $sql = "SELECT distinct soid FROM `vtiger_temp_so_table`";
    $result = $adb->pquery($sql, array());
    while ($row = $adb->fetch_array($result)) {
        updateLineItemsSO($row['soid']);
    }

    $sql = "delete from vtiger_temp_so_table";
    $adb->pquery(
        $sql,
        array()
    );
}

function updateLineItemsSO($id) {
    global $adb;
    $dataArr = getSingleColumnValue(array(
        'table' => 'vtiger_serviceorders',
        'columnId' => 'external_app_num',
        'idValue' => $id,
        'expectedColValue' => 'serviceordersid'
    ));
    $soid = $dataArr[0]['serviceordersid'];
    if (empty($soid)) {
        return;
    }
    $adb->pquery("delete from vtiger_inventoryproductrel where id=?", array($soid));
    $sql = "SELECT *  FROM `vtiger_temp_so_table` where soid = ? ";
    $result = $adb->pquery($sql, array($id));
    while ($row = $adb->fetch_array($result)) {
        $insertSql = "insert into vtiger_inventoryproductrel(id,productid,productname,quantity,comment )
         values (?,?,?, ?,?)";

        $dataArr = getTwoColumnValue(array(
            'table' => 'vtiger_products',
            'columnId' => 'productname',
            'idValue' => $row['part_number'],
            'expectedColValue' => 'productid',
            'expectedColValue1' => 'description'
        ));
        if (empty($soid)) {
            continue;
        }
        $adb->pquery(
            $insertSql,
            array(
                $soid,
                $dataArr[0]['productid'],
                $row['part_number'],
                $row['qty'],
                $dataArr[0]['description'],
            )
        );
    }
}
