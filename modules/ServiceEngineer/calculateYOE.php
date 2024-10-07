<?php
function calculateYOE($entityData) {
    global $adb;
    $paymentRecordInfo = $entityData->{'data'};
    $today = new DateTime();
    $joinedDate = new DateTime($paymentRecordInfo['date_of_joining']);
    $interval=$joinedDate->diff($today);
    $yoe = $interval->format('%y.%m');

    $id = $paymentRecordInfo['id'];
    $id = explode('x', $id);
    $id = $id[1];

    $query = "UPDATE vtiger_serviceengineer SET yoe=? WHERE serviceengineerid=?";
    $adb->pquery($query, array($yoe , $id));

    // Following code makes control to enter inifite loop
    // $recordInstance = Vtiger_Record_Model::getInstanceById($id);
    // $recordInstance->set('mode', 'edit');
    // $recordInstance->set('yoe', $yoe);
    // $recordInstance->save();
}
