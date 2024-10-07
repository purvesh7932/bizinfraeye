<?php
function CalculateEquipmentAvailability($entityData) {
    global $adb;
    $recordInfo = $entityData->{'data'};
    $id = $recordInfo['id'];
    $id = explode('x', $id);
    $id = $id[1];

    $equipId = $recordInfo['equipment_id'];
    $equipId = explode('x', $equipId);
    $equipId = $equipId[1];

    $createdDate = $recordInfo['createdtime'];
    $createdDate = substr($createdDate, 0, 7);
    include_once('include/utils/GeneralConfigUtils.php');
    calculateEquipmentAvailabilty($equipId, $createdDate, $recordInfo['createdtime']);

    $ticket_id = $recordInfo['ticket_id'];
    $ticket_id = explode('x', $ticket_id);
    $ticket_id = $ticket_id[1];

    $reportedUserId = '';
    $sql = 'SELECT source,smcreatorid FROM `vtiger_crmentity` where crmid = ? and source = "CUSTOMER PORTAL"';
    $result = $adb->pquery($sql, array($ticket_id));
    $dataRow = $adb->fetchByAssoc($result, 0);
    $num_rows = $adb->num_rows($result);
    if ($num_rows > 0) {
        $reportedUserId = createdContactUserId($ticket_id);
    } else {
        $reportedUserId = getEngineerInfo($dataRow['smcreatorid']);
    }

    if (!empty($reportedUserId)) {
        $query = "UPDATE vtiger_servicereports SET reported_by = ? WHERE servicereportsid=?";
        $adb->pquery($query, array($reportedUserId, $id));
    }
}

function createdContactUserId($ticket_id) {
    global $adb;
    $query = "SELECT contact_id FROM `vtiger_troubletickets`"
        . " where ticketid = ?";
    $result = $adb->pquery($query, array($ticket_id));
    $num_rows = $adb->num_rows($result);
    $dataRow = $adb->fetchByAssoc($result, 0);
    if ($num_rows > 0) {
        return $dataRow['contact_id'];
    } else {
        return '';
    }
}

function getEngineerInfo($recId) {
    $db = PearDatabase::getInstance();
    $sql = 'select user_name from vtiger_users where id = ?';
    $sqlResult = $db->pquery($sql, array($recId));
    $dataRow = $db->fetchByAssoc($sqlResult, 0);

    $sql = 'select serviceengineerid from vtiger_serviceengineer' .
        ' inner join vtiger_crmentity on vtiger_crmentity.crmid = vtiger_serviceengineer.serviceengineerid' .
        ' where badge_no = ? and vtiger_crmentity.deleted= 0 ORDER BY serviceengineerid DESC LIMIT 1';
    $sqlResult = $db->pquery($sql, array($dataRow['user_name']));
    $dataRowEng = $db->fetchByAssoc($sqlResult, 0);
    return $dataRowEng['serviceengineerid'];
}
