<?php

function UserSpecificNumberCreation($entityData) {
    global $adb;
    $data = $entityData->{'data'};
    $recId = $data['id'];
    $idsOfCreated = explode('x', $recId);
    $data['id'] = $idsOfCreated[1];

    $Number = getUserDealCustNumber();
    $Number = (int)$Number + 1;
    $custNumber = 'ININV00000'.$Number;
    $query = "UPDATE vtiger_troubletickets SET invoice_number=? WHERE ticketid=?";
    $adb->pquery($query, array($custNumber,$data['id']));
}
function getUserDealCustNumber() {
    global $adb;
    $sql = 'SELECT cur_id FROM `vtiger_modentity_num` '
            . ' where semodule = "HelpDesk" and active = "1"';
    $result = $adb->pquery($sql, array());
    $dataRow = $adb->fetchByAssoc($result, 1);
    if(empty($dataRow)){
        return 0;
    }
    $number = $dataRow['cur_id'];
    return $number;
}
