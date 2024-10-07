<?php
function UpdateHMROnCreate($entityData) {
    global $adb;
    global $log;

    $recordInfo = $entityData->{'data'};
    $id = $recordInfo['id'];
    $id = explode('x', $id);
    $id = $id[1];

    $equipmentId = $recordInfo['equipment_id'];
    $equipmentId = explode('x', $equipmentId);
    $equipmentId = $equipmentId[1];
    $equipmentReading = $recordInfo['hmr_value'];
    $query = "UPDATE vtiger_equipment SET eq_last_hmr = ? ,
    last_hmr_date = ?
    WHERE equipmentid=?";
    $adb->pquery($query, array($equipmentReading, date('Y-m-d'), $equipmentId));
}
