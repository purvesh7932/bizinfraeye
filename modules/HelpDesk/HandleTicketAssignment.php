<?php
function HandleTicketAssignment($entityData) {
    global $adb;
    $recordInfo = $entityData->{'data'};
    // $functionalLocationId = $recordInfo['func_loc_id'];
    // $record = getUsersIdOfASsociatedFuntionalLocation($functionalLocationId);
    $id = $recordInfo['id'];
    $id = explode('x', $id);
    $id = $id[1];
    // if (!empty($record['crmid'])) {
    //     $recordInstance = Vtiger_Record_Model::getInstanceById($record['crmid'], $record['module']);
    //     $badgeNo = $recordInstance->get('badge_no');
    //     $userId = getUserIdBasedOnBadge($badgeNo);
    // } else {
    //     $contcatId = $recordInfo['contact_id'];
    //     $contcatId = explode('x', $contcatId);
    //     $contcatId = $contcatId[1];
    //     if (!empty($contcatId)) {
    //         $recordInstance = Vtiger_Record_Model::getInstanceById($contcatId, 'Contacts');
    //         $nearestOffice = $recordInstance->get('nearest_office');
    //         $userId = getAssignedPerson($nearestOffice);
    //     }
    // }

    // if (!empty($userId)) {
    //     $query = "UPDATE vtiger_crmentity SET smownerid=? WHERE crmid=?";
    //     $adb->pquery($query, array($userId, $id));
    // }


    // include_once('include/utils/GeneralUtils.php');
    // $accountId = '';
    // if (
    //     $recordInfo['ticket_type'] == 'PRE-DELIVERY' ||
    //     $recordInfo['ticket_type'] == 'ERECTION AND COMMISSIONING'
    // ) {
    //     $equipmentId = $recordInfo['equip_id_da'];
    //     $equipmentId = explode('x', $equipmentId);
    //     $equipmentId = $equipmentId[1];
    //     $parentAcoountId = $recordInfo['parent_id'];
    //     if (empty($parentAcoountId)) {
    //         $dataArr = getTwoColumnValue(array(
    //             'table' => 'vtiger_deliverynotes',
    //             'columnId' => 'deliverynotesid',
    //             'idValue' => $equipmentId,
    //             'expectedColValue' => 'account_id',
    //             'expectedColValue1' => 'equipment_id'
    //         ));
    //         $accountId = $dataArr[0]['account_id'];
    //     } else {
    //         $parentAcoountId = explode('x', $parentAcoountId);
    //         $accountId = $parentAcoountId[1];
    //     }
    //     $query = "UPDATE vtiger_troubletickets SET ticket_date=?, status = ?, 
    //     no_of_days = ? , parent_id = ?
    //     WHERE ticketid=?";
    //     $adb->pquery($query, array(date('Y-m-d'), 'Open', 1, $accountId, $id));
    //     $equipment_id = $dataArr[0]['equipment_id'];
    //     if (!empty($equipment_id)) {
    //         $dataArrFunc = getSingleColumnValue(array(
    //             'table' => 'vtiger_equipment',
    //             'columnId' => 'equipmentid',
    //             'idValue' => $equipment_id,
    //             'expectedColValue' => 'functional_loc',
    //         ));
    //         $functional_loc = $dataArrFunc[0]['functional_loc'];
    //         $query = "UPDATE vtiger_troubletickets SET func_loc_id=? WHERE ticketid=?";
    //         $adb->pquery($query, array($functional_loc, $id));
    //     }
    // } else {
    //     $equipmentId = $recordInfo['equipment_id'];
    //     $equipmentId = explode('x', $equipmentId);
    //     $equipmentId = $equipmentId[1];
    //     $dataArr = getSingleColumnValue(array(
    //         'table' => 'vtiger_equipment',
    //         'columnId' => 'equipmentid',
    //         'idValue' => $equipmentId,
    //         'expectedColValue' => 'account_id'
    //     ));
    //     $accountId = $dataArr[0]['account_id'];
    //     $query = "UPDATE vtiger_troubletickets SET ticket_date=?, status = ?, 
    //     no_of_days = ? , parent_id = ?
    //     WHERE ticketid=?";
    //     $adb->pquery($query, array(date('Y-m-d'), 'Open', 1, $accountId, $id));
    // }
    $query = 'UPDATE vtiger_troubletickets SET ticket_date=?, status = ? WHERE ticketid=?';
    $adb->pquery($query, array(date('Y-m-d'), 'Open', $id));

    // if ($recordInfo['source'] == 'CUSTOMER PORTAL') {
    //     $func_loc_id = explode('x', $recordInfo['func_loc_id']);
    //     $recordInfo['func_loc_id'] = $func_loc_id[1];
    //     $processedData = IGprocess($recordInfo);
    //     $userId = $processedData[0]['id'];
    //     if (!empty($userId)) {
    //         $query = "UPDATE vtiger_crmentity SET smownerid=? WHERE crmid=?";
    //         $adb->pquery($query, array($userId, $id));
    //     }
    // }
}

function IGprocess($dataOFRecord) {
    include_once('include/utils/GeneralUtils.php');
    $functionalLoationId = $dataOFRecord['func_loc_id'];
    $adb = PearDatabase::getInstance();
    $sql = "SELECT `vtiger_crmentityrel`.crmid,vtiger_serviceengineer.badge_no FROM `vtiger_crmentityrel`
            inner join vtiger_crmentity 
            on vtiger_crmentity.crmid=vtiger_crmentityrel.relcrmid 
            inner join vtiger_serviceengineer 
            on vtiger_serviceengineer.serviceengineerid=vtiger_crmentityrel.crmid
            where `vtiger_crmentityrel`.relcrmid = ? 
            and module = 'ServiceEngineer' and vtiger_serviceengineer.approval_status = 'Accepted'
            and vtiger_serviceengineer.auto_asgn_ticket = 'Yes'
            and vtiger_crmentity.deleted = 0";

    $result = $adb->pquery($sql, array($functionalLoationId));
    $records = array();
    $currentUserModel = Users_Record_Model::getCurrentUserModel();
    $allUsers = $currentUserModel->getAccessibleUsers();
    $allUserIds = array_keys($allUsers);
    $userData = [];
    $empData = [];
    while ($row = $adb->fetch_array($result)) {
        $data = getUserIdDetailsBasedOnEmployeeModuleG($row['badge_no']);
        if (in_array($data['id'], $allUserIds)) {
            array_push($userData, $data['id']);
            array_push($empData, $row['crmid']);
        }
    }
    if (count($empData) > 1) {
        $records = IGmodelCheck($dataOFRecord, $empData);
        if (!empty($records)) {
            return $records;
        } else {
            $leastLoadedUser = IGgetLeastLoadedUser($userData);
            return array($leastLoadedUser);
        }
    } else if (count($empData) == 1) {
        //$row = getUserIdDetailsBasedOnEmployeeModuleG($dataRow['badge_no']);
        array_push($records, array('id' => $userData[0]));
        return $records;
    }

    $records = array();
    return $records;
}

function IGmodelCheck($data, $empData) {
    global $adb;
    $model = $data['sr_equip_model'];
    $modelCheckSql = "SELECT `vtiger_crmentityrel`.crmid , vtiger_serviceengineer.badge_no
    FROM `vtiger_crmentityrel` 
    inner join vtiger_crmentity 
    on vtiger_crmentity.crmid=vtiger_crmentityrel.relcrmid 
    inner join vtiger_serviceengineer 
    on vtiger_serviceengineer.serviceengineerid=vtiger_crmentityrel.crmid
    left join vtiger_modelandaggregates 
    on vtiger_modelandaggregates.modelandaggregatesid = vtiger_crmentityrel.relcrmid 
    where `vtiger_crmentityrel`.crmid in (" . generateQuestionMarks($empData) . ") 
    and vtiger_crmentityrel.module = 'ServiceEngineer' 
    and `vtiger_modelandaggregates`.eq_sr_equip_model = ? 
    and vtiger_crmentity.deleted = 0";

    $params = $empData;
    array_push($params, $model);
    $result = $adb->pquery($modelCheckSql, $params);
    $num_rows = $adb->num_rows($result);

    $records = array();
    if ($num_rows > 1) {
        $records = IGaggregateCheck($data, $empData);
        if (!empty($records)) {
            return $records;
        } else {
            $userData = [];
            while ($row = $adb->fetch_array($result)) {
                $data = getUserIdDetailsBasedOnEmployeeModuleG($row['badge_no']);
                array_push($userData, $data['id']);
            }
            $leastLoadedUser = IGgetLeastLoadedUser($userData);
            return array($leastLoadedUser);
        }
    } else if ($num_rows == 1) {
        $dataRow = $adb->fetchByAssoc($result, 0);
        $row = getUserIdDetailsBasedOnEmployeeModuleG($dataRow['badge_no']);
        array_push($records, $row);
        return $records;
    } else {
        return false;
    }
}

function IGaggregateCheck($data, $empData) {
    global $adb;
    $model = $data['sr_equip_model'];
    $aggregate = $data['system_affected'];
    $aggregate = explode('_._', $aggregate);
    $aggregate = $aggregate[1];

    $modelCheckSql = "SELECT `vtiger_crmentityrel`.crmid , vtiger_serviceengineer.badge_no
    FROM `vtiger_crmentityrel` 
    inner join vtiger_crmentity 
    on vtiger_crmentity.crmid=vtiger_crmentityrel.relcrmid 
    inner join vtiger_serviceengineer 
    on vtiger_serviceengineer.serviceengineerid=vtiger_crmentityrel.crmid
    left join vtiger_modelandaggregates 
    on vtiger_modelandaggregates.modelandaggregatesid = vtiger_crmentityrel.relcrmid 
    where `vtiger_crmentityrel`.crmid in (" . generateQuestionMarks($empData) . ") 
    and vtiger_crmentityrel.module = 'ServiceEngineer' 
    and `vtiger_modelandaggregates`.eq_sr_equip_model = ? 
    and vtiger_modelandaggregates.masn_aggrregate LIKE ?
    and vtiger_crmentity.deleted = 0";

    $params = $empData;
    array_push($params, $model,  "%$aggregate%");
    $result = $adb->pquery($modelCheckSql, $params);
    $num_rows = $adb->num_rows($result);
    $records = array();
    if ($num_rows > 1) {
        $userData = [];
        while ($row = $adb->fetch_array($result)) {
            $data = getUserIdDetailsBasedOnEmployeeModuleG($row['badge_no']);
            array_push($userData, $data['id']);
        }
        $leastLoadedUser = IGgetLeastLoadedUser($userData);
        return array($leastLoadedUser);
    } else if ($num_rows == 1) {
        $dataRow = $adb->fetchByAssoc($result, 0);
        $row = getUserIdDetailsBasedOnEmployeeModuleG($dataRow['badge_no']);
        array_push($records, $row);
        return $records;
    } else {
        return false;
    }
}

function IGgetLeastLoadedUser($userids) {
    global $adb;
    $sql = "SELECT COUNT(smownerid) as ownercount,smownerid as id
    FROM vtiger_troubletickets INNER JOIN vtiger_crmentity 
    on vtiger_crmentity.crmid = vtiger_troubletickets.ticketid 
    where vtiger_crmentity.smownerid in (" . generateQuestionMarks($userids) . ") 
    and vtiger_crmentity.deleted = 0 
    and vtiger_troubletickets.status != 'Closed' 
    GROUP BY smownerid 
    ORDER BY ownercount ASC";

    $result = $adb->pquery($sql, $userids);
    $dataRow = $adb->fetchByAssoc($result, 0);
    $num_rows = $adb->num_rows($result);
    if (empty($dataRow)) {
        return array('id' => $userids[0]);
    } else if ($num_rows != count($userids)) {
        $allExt = [];
        for ($j = 0; $j < $num_rows; $j++) {
            $verId = $adb->query_result($result, $j, 'id');
            array_push($allExt, $verId);
        }
        $nonExt = array_diff($userids, $allExt);
        foreach ($nonExt as $nonExtId) {
            return array('id' => $nonExtId);
        }
    } else {
        return $dataRow;
    }
    return false;
}

function getAssignedPerson($roleName) {
    $realRole = explode(" - ", $roleName);
    $region = trim($realRole[0]);
    global $adb;
    $roleName = $region . ' - REGIONAL MANAGER';
    $sql = "SELECT * FROM `vtiger_role` 
    INNER JOIN `vtiger_user2role` ON `vtiger_user2role`.`roleid` = `vtiger_role`.`roleid` 
    where rolename = ?";
    $result = $adb->pquery($sql, array($roleName));
    $dataRow = $adb->fetchByAssoc($result, 0);
    if (empty($dataRow['userid'])) {
        return 1;
    } else {
        return $dataRow['userid'];
    }
}

function getRegionalMangaerUserId($badgeNo) {
}
function getUserIdBasedOnBadge($badgeNo) {
    global $adb;
    $sql = "SELECT * FROM `vtiger_users` where user_name = ? ";
    $allRecords = $adb->pquery($sql, array($badgeNo));
    $dataRow = $adb->fetchByAssoc($allRecords, 0);
    return $dataRow['id'];
}

function getUsersIdOfASsociatedFuntionalLocation($functionalLocationId) {
    global $adb;
    $sql = "SELECT * FROM `vtiger_crmentityrel` " .
        " inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_crmentityrel.crmid " .
        " where relcrmid = ? and module =  ?  and vtiger_crmentity.deleted = 0";

    $functionalLocationId = explode('x', $functionalLocationId);
    $functionalLocationId = $functionalLocationId[1];
    $allRecords = $adb->pquery($sql, array($functionalLocationId, 'ServiceEngineer'));
    $dataRow = $adb->fetchByAssoc($allRecords, 0);
    return $dataRow;
}
