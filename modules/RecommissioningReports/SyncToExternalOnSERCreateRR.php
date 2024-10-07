<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
include_once('include/utils/GeneralUtils.php');
include_once('include/utils/IgClassUtils.php');
function SyncToExternalOnSERCreateRR($entityData) {
    global $adb, $noTRiggerOFWorkFlowForRR;
    global $log;
    if ($noTRiggerOFWorkFlowForRR == true) {
        return;
    }
    $recordInfo = $entityData->{'data'};

    $notiType = $recordInfo['fail_de_sap_noti_type'];
    // no need to sync to sap of undefined notification types
    $id = $recordInfo['id'];
    $id = explode('x', $id);
    $id = $id[1];

    $ticketId = $recordInfo['ticket_id'];
    $ticketId = explode('x', $ticketId);
    $ticketId = $ticketId[1];

    $sql = 'select external_app_num,createdtime from vtiger_troubletickets ' .
        ' inner join vtiger_crmentity on vtiger_crmentity.crmid = vtiger_troubletickets.ticketid ' .
        ' where ticketid = ?';
    $sqlResult = $adb->pquery($sql, array($ticketId));
    $dataRow = $adb->fetchByAssoc($sqlResult, 0);
    $ticketCreatedDateTime = '';
    if (empty($dataRow)) {
    } else {
        $exterAppNum = $dataRow['external_app_num'];
        $ticketCreatedDateTime =  $dataRow['createdtime'];
    }
    if (!empty($exterAppNum)) {
        $isSubmitted = $recordInfo['is_submitted'];
        // IgClassUtils::handleUpdatedOfNotification(
        //     $recordInfo,
        //     $ticketCreatedDateTime,
        //     $notiType,
        //     $id,
        //     $exterAppNum,
        //     'RecommissioningReports'
        // );
        if ($isSubmitted == '1') {
            $responseObject = changeNotifiationStatusWithRR('Closed', $exterAppNum, $ticketId);
            if ($responseObject['success'] == true) {
                $query = "UPDATE vtiger_troubletickets SET status = ? WHERE ticketid=?";
                $adb->pquery($query, array('Closed', $ticketId));
            } else {
                $query = "UPDATE vtiger_recommissioningreports SET is_submitted = ? WHERE recommissioningreportsid=?";
                $adb->pquery($query, array('0', $id));
                global $actionFromMobileApis;
                if ($actionFromMobileApis) {
                    global $hasSAPErrors, $ErrorMessage, $SAPDetailError;
                    $hasSAPErrors = true;
                    $ErrorMessage = "Changing Notificatin Status Failed";
                    $SAPDetailError = $responseObject['message'];
                } else {
                    $_SESSION["errorFromExternalApp"] = $responseObject['message'];
                    $_SESSION["lastSyncedExterAppRecord"] = $id;
                    header("Location: index.php?module=RecommissioningReports&view=Edit&record=$id&app=SUPPORT");
                    exit();
                }
            }
        } else {
            $_SESSION["errorFromExternalApp"] = NULL;
            $_SESSION["lastSyncedExterAppRecord"] = NULL;
        }

        if ($isSubmitted == '1') {
            include_once('include/utils/IgClassUtils.php');
            $failedPartCanBeCreated = IgClassUtils::FailedPartCanBeCreated($id);
            if ($failedPartCanBeCreated == true) {
                $sql = 'select ticket_id from vtiger_failedparts ' .
                ' inner join vtiger_crmentity 
                  on vtiger_crmentity.crmid = vtiger_failedparts.failedpartid ' .
                ' where ticket_id = ? and vtiger_crmentity.deleted = 0 ';
                $sqlResult = $adb->pquery($sql, array($ticketId));
                $num_rows = $adb->num_rows($sqlResult);
                if ($num_rows == 0) {
                   IgClassUtils::createFailedPartsRecords($id, $ticketId, $exterAppNum);
                }
            }
        }
        return;
    }
}
