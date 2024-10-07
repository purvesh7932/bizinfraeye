<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
include_once('include/utils/GeneralUtils.php');
include_once('include/utils/IgClassUtils.php');
function CreateOnlyNotification($entityData) {
    global $adb;
    global $log;
    $recordInfo = $entityData->{'data'};

    $notiType = $recordInfo['fail_de_sap_noti_type'];
    // no need to sync to sap of undefined notification types
    $id = $recordInfo['id'];
    $id = explode('x', $id);
    $id = $id[1];

    $ticketId = $recordInfo['ticket_id'];
    $ticketId = explode('x', $ticketId);
    $ticketId = $ticketId[1];

    if ($notiType == '--') {
        $isRecomisionCreated = false;
        $sql = 'select ticket_id from vtiger_recommissioningreports ' .
            ' inner join vtiger_crmentity on vtiger_crmentity.crmid = vtiger_recommissioningreports.recommissioningreportsid ' .
            ' where ticket_id = ? and vtiger_crmentity.deleted = 0 ';
        $sqlResult = $adb->pquery($sql, array($ticketId));
        $num_rows = $adb->num_rows($sqlResult);
        if ($num_rows == 0) {
            $isRecomisionCreated = handleCreationOfRecommisioningReport($recordInfo, $id);
        }

        $isSubmitted = $recordInfo['is_submitted'];
        if ($isSubmitted == '1' && $isRecomisionCreated == true) {
            $query = "UPDATE vtiger_troubletickets SET status = ? WHERE ticketid=?";
            $adb->pquery($query, array('Closed : Recommissioning Is Pending', $ticketId));
        } else if ($isSubmitted == '1') {
            $query = "UPDATE vtiger_troubletickets SET status = ? WHERE ticketid=?";
            $adb->pquery($query, array('Closed', $ticketId));
        } else {
            $query = "UPDATE vtiger_troubletickets SET status = ? WHERE ticketid=?";
            $adb->pquery($query, array('In Progress', $ticketId));
        }
        return;
    }

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
        $isRecomisionCreated = false;
        
        // IgClassUtils::handleUpdatedOfNotification(
        //     $recordInfo,
        //     $ticketCreatedDateTime,
        //     $notiType,
        //     $id,
        //     $exterAppNum,
        //     'ServiceReports'
        // );

        $sql = 'select ticket_id from vtiger_recommissioningreports ' .
        ' inner join vtiger_crmentity on vtiger_crmentity.crmid = vtiger_recommissioningreports.recommissioningreportsid ' .
        ' where ticket_id = ? and vtiger_crmentity.deleted = 0 ';
        $sqlResult = $adb->pquery($sql, array($ticketId));
        $num_rows = $adb->num_rows($sqlResult);
        if ($num_rows == 0) {
            $isRecomisionCreated = handleCreationOfRecommisioningReport($recordInfo, $id);
        }
        if ($isSubmitted == '1' && $isRecomisionCreated == false) {
            $responseObject = changeNotifiationStatus('Closed', $exterAppNum, $ticketId);
            if ($responseObject['success'] == true) {
                $query = "UPDATE vtiger_troubletickets SET status = ? WHERE ticketid=?";
                $adb->pquery($query, array('Closed', $ticketId));
                $failedPartCanBeCreated = FailedPartCanBeCreated($id);
                if ($failedPartCanBeCreated == true) {
                    $sql = 'select ticket_id from vtiger_failedparts ' .
                    ' inner join vtiger_crmentity 
                        on vtiger_crmentity.crmid = vtiger_failedparts.failedpartid ' .
                    ' where ticket_id = ? and vtiger_crmentity.deleted = 0 ';
                    $sqlResult = $adb->pquery($sql, array($ticketId));
                    $num_rows = $adb->num_rows($sqlResult);
                    if ($num_rows == 0) {
                        createFailedPartsRecords($id, $ticketId, $exterAppNum);
                    }
                }
            } else {
                $query = "UPDATE vtiger_servicereports SET is_submitted = ? WHERE servicereportsid=?";
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
                    header("Location: index.php?module=ServiceReports&view=Edit&record=$id&app=SUPPORT");
                    exit();
                }
            }
        } else if ($isSubmitted == '1' && $isRecomisionCreated == true) {
            $query = "UPDATE vtiger_troubletickets SET status = ? WHERE ticketid=?";
            $adb->pquery($query, array('Closed : Recommissioning Is Pending', $ticketId));
        }
        $_SESSION["errorFromExternalApp"] = NULL;
        $_SESSION["lastSyncedExterAppRecord"] = NULL;
        return;
    }

    $reportedById = $recordInfo['reported_by'];
    $reportedById = explode('x', $reportedById);
    $reportedById = $reportedById[1];
    $reportedBy = Vtiger_Functions::getCRMRecordLabel($reportedById);
    $symptoms = $recordInfo['symptoms'];

    $Observation = $recordInfo['fd_obvservation'];
    $actionTaken = $recordInfo['action_taken_block'];

    // Implement fail_de_part_pertains_to
    $partPertainsTo = $recordInfo['fail_de_part_pertains_to'];
    if ($partPertainsTo == 'BEML') {
        $partPertainsTo1 = '';
        if ($recordInfo['fd_sub_div']  == 'Engine') {
            $partPertainsTo1 = "Responsible Agency_._BEML - Engine Divn.";
        } else if ($recordInfo['fd_sub_div']  == 'Truck') {
            $partPertainsTo1 = "Responsible Agency_._BEML - Truck Divn.";
        } else if ($recordInfo['fd_sub_div']  == 'H&P') {
            $partPertainsTo1 = "Responsible Agency_._BEML - H & P Divn.";
        } else if ($recordInfo['fd_sub_div']  == 'EM') {
            $partPertainsTo1 = "Responsible Agency_._BEML - EM Divn.";
        }
        $sql = 'select code , code_group from vtiger_fail_de_part_pertains_to_ano '
            . ' where fail_de_part_pertains_to_ano = ?';
        $sqlResult = $adb->pquery($sql, array($partPertainsTo1));
        $dataRow = $adb->fetchByAssoc($sqlResult, 0);
        $partPertainsToCode = '';
        $partPertainsToCodeGroup = '';
        if (empty($dataRow)) {
            $partPertainsToCode = '';
            $partPertainsToCodeGroup = '';
        } else {
            $partPertainsToCode = $dataRow['code'];
            $partPertainsToCodeGroup = $dataRow['code_group'];
        }
    }

    $recordInstance = '';
    $SAPrefEquip = '';
    $equipId = '';
    if ($recordInfo['sr_ticket_type'] == 'ERECTION AND COMMISSIONING' || $recordInfo['sr_ticket_type'] == 'PRE-DELIVERY') {
        $equipId = $recordInfo['equip_id_da_sr'];
        $equipId = explode('x', $equipId);
        $equipId = $equipId[1];
        $notActualEquip = false;
        $notActualEquipAnother = false;
        if (!empty($equipId)) {
            $recordInstance = Vtiger_Record_Model::getInstanceById($equipId);
            $SAPrefEquip = $recordInstance->get('manual_equ_ser');
            $notActualEquipAnother = true;
        } else {
            $notActualEquip = true;
            $SAPrefEquip = $recordInfo['manual_equ_ser'];
        }
    } else {
        $equipId = $recordInfo['equipment_id'];
        $equipId = explode('x', $equipId);
        $equipId = $equipId[1];
        if (!empty($equipId)) {
            $recordInstance = Vtiger_Record_Model::getInstanceById($equipId);
            $SAPrefEquip = $recordInstance->get('equipment_sl_no');
        }
    }

    $conditionAfterAction = $recordInfo['eq_sta_aft_act_taken'];
    $conditionAfterActionCode = getCodeOFValue('eq_sta_aft_act_taken', $conditionAfterAction);

    $conditionBeforeSRGen = $recordInfo['fd_eq_sta_bsr'];
    $conditionBeforeSRGenCode = getCodeOFValue('fd_eq_sta_bsr', $conditionBeforeSRGen);

    // malfunction Implementation
    $ticketCreatedDateTimeArr = explode(' ', $ticketCreatedDateTime);
    $ticketCreatedDate = $ticketCreatedDateTimeArr[0];
    $ticketCreatedDateSapFormat = str_replace('-', '', $ticketCreatedDate);
    $ticketCreatedTime = $ticketCreatedDateTimeArr[1];

    $time = strtotime($ticketCreatedTime);
    $startTime = date("H:i:s", strtotime('+5 hours 30 minutes', $time));
    $ticketTimeSAPFormat = str_replace(':', '', $startTime);

    $malfunctionStartDate = $recordInfo['date_of_failure'];
    $malfunctionStartDateSAPFormat = str_replace('-', '', $malfunctionStartDate);


    $malfunctionEndDate = $recordInfo['restoration_date'];
    $malfunctionEndDateSAPFormat = str_replace('-', '', $malfunctionEndDate);
    $malfunctionEndTime = $recordInfo['restoration_time'];
    $malfunctionEndTimeSAPFormat = str_replace(':', '', $malfunctionEndTime);

    if ($recordInfo['eq_sta_aft_act_taken'] == 'Off Road') {
        if (empty($malfunctionStartDate)) {
            $malfunctionStartDateSAPFormat = $ticketCreatedDateSapFormat;
        }

        if (empty($malfunctionEndDate)) {
            $malfunctionEndDateSAPFormat = $ticketCreatedDateSapFormat;
        }
        if (empty($malfunctionEndTime)) {
            $malfunctionEndTimeSAPFormat = $ticketTimeSAPFormat;
        }
    }
    
    $hmr = floatval($recordInfo['hmr']);
    $kmRun = floatval($recordInfo['kilometer_reading']);

    $url = getExternalAppURL('CreateOnlySR');
    $header = array('Content-Type:multipart/form-data');

    $im_msausVal = '';
    if ($recordInfo['eq_sta_aft_act_taken'] == 'Off Road') {
        $im_msausVal = 'X';
    }
    $data = array(
        'IM_TYPE'  => $notiType,
        'IM_TEXT' => $symptoms,
        'IM_EQUNR'  => $SAPrefEquip,
        'IM_MSAUS' => $im_msausVal,
        // 'IM_EAUSZT' =>  '10.00',
        // 'IM_MAUEH'  => '',
        'IM_LTEXT1' => $Observation,
        'IM_LTEXT2' => $actionTaken,
        'IM_LTEXT3' => '',
        'IM_ISHMR' => 'X',
        'IM_LTEXT4' => '',
        'IM_REPORTEDBY' => $reportedBy,
        'IM_RESPOSIBLE' => $partPertainsToCodeGroup . ',' . $partPertainsToCode,
        'IM_EFFECT' =>  getValueEffect($recordInfo['eq_sta_aft_act_taken']),
        'IM_BEFORE_MALFUNC' => $conditionBeforeSRGenCode,
        'IM_AFTER_MALFUNC' =>  getCodeOFValue('sr_equip_status', $recordInfo['sr_equip_status']),
        'IM_COND_AFTERTASK' =>  $conditionAfterActionCode,
        'IM_MALFUNC_STARTDATE' => $malfunctionStartDateSAPFormat,
        'IM_MALFUNC_ENDDATE' => $malfunctionEndDateSAPFormat,
        'IM_MALFUNC_ENDTIME' => $malfunctionEndTimeSAPFormat,
        'IM_HMR_READING' => $hmr,
        'IM_NOTIFDATE' => str_replace('-', '', $ticketCreatedDateTimeArr[0]),
        'IM_NOTIFTIME' => $ticketTimeSAPFormat
    );

    if ($data['IM_RESPOSIBLE'] == ',') {
        $data['IM_RESPOSIBLE'] = "";
    }

    if (!empty($kmRun)) {
        $data['IM_ISHMR'] = '';
        $data['IM_HMR_READING'] = $kmRun;
    }

    if (!empty($kmRun) && !empty($hmr) ) {
        $data['IM_ISHMR'] = 'x';
        $data['IM_HMR_READING'] = $hmr;
    }

    if (empty($malfunctionStartDateSAPFormat)) {
        unset($data['IM_MALFUNC_STARTDATE']);
    }
    if (empty($malfunctionEndDateSAPFormat)) {
        unset($data['IM_MALFUNC_ENDDATE']);
    }
    if (empty($ticketTimeSAPFormat)) {
        unset($data['IM_MALFUNC_STARTTIME']);
    }
    if (empty($malfunctionEndTimeSAPFormat)) {
        unset($data['IM_MALFUNC_ENDTIME']);
    }

    if (empty($data['IM_NOTIFDATE'])) {
        unset($data['IM_NOTIFDATE']);
    }

    $data['sr_ticket_type'] = $recordInfo['sr_ticket_type'];
    if ($recordInfo['sr_ticket_type'] == 'BREAKDOWN' || $recordInfo['sr_ticket_type'] == 'ERECTION AND COMMISSIONING' || $recordInfo['sr_ticket_type'] == 'PRE-DELIVERY') {
        if ($recordInfo['sr_ticket_type'] == 'BREAKDOWN') {
            $data['IM_MALFUNC_STARTTIME'] = $ticketTimeSAPFormat;
        }

        if (empty($data['IM_MALFUNC_STARTTIME']) && $recordInfo['eq_sta_aft_act_taken'] == 'Off Road') {
            $data['IM_MALFUNC_STARTTIME'] = $ticketTimeSAPFormat;
        }
        if ($recordInfo['eq_sta_aft_act_taken'] != 'Off Road' && $recordInfo['sr_ticket_type'] != 'BREAKDOWN' ) {
            unset($data['IM_MALFUNC_STARTTIME']);
            unset($data['IM_MALFUNC_STARTDATE']);
            unset($data['IM_MALFUNC_ENDDATE']);
            unset($data['IM_MALFUNC_ENDTIME']);
        }

        if (
            $recordInfo['sr_ticket_type'] == 'ERECTION AND COMMISSIONING'
            || $recordInfo['sr_ticket_type'] == 'PRE-DELIVERY'
        ) {
            $data['IM_TEXT'] = $recordInfo['td_symptoms'];
        }
        $data['IT_OBJECTPART'] = json_encode(getAsArrayOfCodes($recordInfo['fail_de_system_affected'], 'fail_de_system_affected'));
        $data['IT_DAMAGE'] = json_encode(getAsArrayOfCodes($recordInfo['fail_de_parts_affected'], 'fail_de_parts_affected'));
        $data['IT_CAUSE'] = json_encode(getAsArrayOfCodes($recordInfo['fail_de_type_of_damage'], 'fail_de_type_of_damage'));
    }
    // print_r($data);
    // die();
    if (
        $notActualEquip == true && (
            ($recordInfo['sr_ticket_type'] == 'ERECTION AND COMMISSIONING' ||
            $recordInfo['sr_ticket_type'] == 'PRE-DELIVERY'))
    ) {
        $preDelPartNerArr = [];
        $accountId = $recordInfo['account_id'];
        $accountId = explode('x', $accountId);
        $accountId = $accountId[1];

        $sapRefAccountNum = '';
        if(!empty($accountId)){
            $dataArr = getSingleColumnValue(array(
                'table' => 'vtiger_account',
                'columnId' => 'accountid',
                'idValue' => $accountId,
                'expectedColValue' => 'external_app_num'
            ));
            $sapRefAccountNum = $dataArr[0]['external_app_num'];
        }
        $preDElData = [
            'PARTN_ROLE' => 'AG',
            'PARTNER' => $sapRefAccountNum,
            'PARTNER_OLD' => '',
            'PARTN_ROLE_OLD' => ''
        ];
        array_push($preDelPartNerArr, $preDElData);
        $data['IT_PARTNER'] = json_encode($preDelPartNerArr);
        unset($data['IM_HMR_READING']);
        unset($data['IM_ISHMR']);
        $data['IM_LTEXT3'] = 'Manualy Entered Serial Number : ' .
        $recordInfo['eq_sr_equip_model'] . '-' . $SAPrefEquip;
        $data['IM_EQUNR'] = '';
        $url = getExternalAppURL('CreateSRWithNOHMR');
    } else if ($notActualEquipAnother == true  &&  
        ($recordInfo['sr_ticket_type'] == 'ERECTION AND COMMISSIONING' ||
        $recordInfo['sr_ticket_type'] == 'PRE-DELIVERY')) {

        $preDelPartNerArr = [];
        $accountId = $recordInfo['account_id'];
        $accountId = explode('x', $accountId);
        $accountId = $accountId[1];

        $sapRefAccountNum = '';
        if(!empty($accountId)){
            $dataArr = getSingleColumnValue(array(
                'table' => 'vtiger_account',
                'columnId' => 'accountid',
                'idValue' => $accountId,
                'expectedColValue' => 'external_app_num'
            ));
            $sapRefAccountNum = $dataArr[0]['external_app_num'];
        }
        $preDElData = [
            'PARTN_ROLE' => 'AG',
            'PARTNER' => $sapRefAccountNum,
            'PARTNER_OLD' => '',
            'PARTN_ROLE_OLD' => ''
        ];
        array_push($preDelPartNerArr, $preDElData);
        $data['IT_PARTNER'] = json_encode($preDelPartNerArr);
        unset($data['IM_HMR_READING']);
        unset($data['IM_ISHMR']);
        $url = getExternalAppURL('CreateSRWithNOHMR');
    }
    $log->debug("*****Data Sendig To SAP***********" . json_encode($data) . "********");
    $resource = curl_init();
    curl_setopt($resource, CURLOPT_URL, $url);
    curl_setopt($resource, CURLOPT_HTTPHEADER, $header);
    curl_setopt($resource, CURLOPT_POST, 1);
    curl_setopt($resource, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($resource, CURLOPT_POSTFIELDS, $data);
    $responseUnEncoded = curl_exec($resource);
    $log->debug("*****Response Recived From SAP***********$responseUnEncoded********");
    $response = json_decode($responseUnEncoded, true);
    curl_close($resource);

    $ticketId = $recordInfo['ticket_id'];
    $ticketId = explode('x', $ticketId);
    $ticketId = $ticketId[1];
    if (empty(trim($response['EX_QMNUM']))) {
        $query = "UPDATE vtiger_servicereports SET is_submitted = ? WHERE servicereportsid=?";
        $adb->pquery($query, array('0', $id));
        global $actionFromMobileApis;
        if ($actionFromMobileApis) {
            $jsonParseError = json_last_error();
            global $hasSAPErrors, $ErrorMessage, $SAPDetailError;
            $hasSAPErrors = true;
            $ErrorMessage = "SAP Sync Is Failed";
            if (empty($jsonParseError)) {
                $SAPDetailError = IgGetSAPErrorFormatASerrorArray($response['IT_RETURN']);
            } else {
                $SAPDetailError = $responseUnEncoded;
            }
        } else {
            $jsonParseError = json_last_error();
            if (empty($jsonParseError)) {
                $_SESSION["errorFromExternalApp"] = IgGetSAPErrorFormatASerrorArray($response['IT_RETURN']);
                $_SESSION["lastSyncedExterAppRecord"] = $id;
                header("Location: index.php?module=ServiceReports&view=Edit&record=$id&app=SUPPORT");
                exit();
            } else {
                $_SESSION["errorFromExternalApp"] = $responseUnEncoded;
                $_SESSION["lastSyncedExterAppRecord"] = $id;
                header("Location: index.php?module=ServiceReports&view=Edit&record=$id&app=SUPPORT");
                exit();
            }
        }
    } else {
        $notificationNumber = trim($response['EX_QMNUM']);
        $isSubmitted = $recordInfo['is_submitted'];
        if ($recordInstance) {
            $recordInstance->set('mode', 'edit');
            $recordInstance->set('eq_last_hmr', $hmr);
            $recordInstance->save();
            $query = "UPDATE vtiger_equipment SET eq_last_hmr=? WHERE equipmentid=?";
            $adb->pquery($query, array($hmr, $equipId));
        }
        $query = "UPDATE vtiger_troubletickets SET external_app_num=? WHERE ticketid=?";
        $adb->pquery($query, array($notificationNumber, $ticketId));
        $isRecomisionCreated = handleCreationOfRecommisioningReport($recordInfo, $id);
        $responseObject = changeNotifiationStatus('In Progress', $notificationNumber, $ticketId);
        if ($responseObject['success'] == true) {
            $query = "UPDATE vtiger_troubletickets SET status = ? WHERE ticketid=?";
            $adb->pquery($query, array('In Progress', $ticketId));
        }
        if ($isSubmitted == '1' && $isRecomisionCreated == false) {
            $responseObject = changeNotifiationStatus('Closed', $notificationNumber, $ticketId);
            if ($responseObject['success'] == true) {
                $query = "UPDATE vtiger_troubletickets SET status = ? WHERE ticketid=?";
                $adb->pquery($query, array('Closed', $ticketId));
                $failedPartCanBeCreated = FailedPartCanBeCreated($id);
                if ($failedPartCanBeCreated == true) {
                    $sql = 'select ticket_id from vtiger_failedparts ' .
                    ' inner join vtiger_crmentity 
                      on vtiger_crmentity.crmid = vtiger_failedparts.failedpartid ' .
                    ' where ticket_id = ? and vtiger_crmentity.deleted = 0 ';
                    $sqlResult = $adb->pquery($sql, array($ticketId));
                    $num_rows = $adb->num_rows($sqlResult);
                    if ($num_rows == 0) {
                        createFailedPartsRecords($id, $ticketId, $notificationNumber);
                    }
                }
            } else {
                global $actionFromMobileApis;
                $query = "UPDATE vtiger_servicereports SET is_submitted = ? WHERE servicereportsid=?";
                $adb->pquery($query, array('0', $id));
                if ($actionFromMobileApis) {
                    global $hasSAPErrors, $ErrorMessage, $SAPDetailError;
                    $hasSAPErrors = true;
                    $ErrorMessage = "Changing Notificatin Status Failed";
                    $SAPDetailError = $responseObject['message'];
                } else {
                    $_SESSION["errorFromExternalApp"] = $responseObject['message'];
                    $_SESSION["lastSyncedExterAppRecord"] = $id;
                    header("Location: index.php?module=ServiceReports&view=Edit&record=$id&app=SUPPORT");
                    exit();
                }
            }
        } else if ($isSubmitted == '1' && $isRecomisionCreated == true) {
            $query = "UPDATE vtiger_troubletickets SET status = ? WHERE ticketid=?";
            $adb->pquery($query, array('Closed : Recommissioning Is Pending', $ticketId));
        }
        $_SESSION["errorFromExternalApp"] = NULL;
        $_SESSION["lastSyncedExterAppRecord"] = NULL;
    }
}

function handleCreationOfRecommisioningReport($recordInfo, $id) {
    $ticketType = $recordInfo['sr_ticket_type'];
    $purpose = $recordInfo['tck_det_purpose'];
    $recommisioingReportCanBeCreated = false;
    if ($ticketType == 'BREAKDOWN') {
        $recommisioingReportCanBeCreated = recommisioingReportCanBeCreated($recordInfo);
        if ($recommisioingReportCanBeCreated == true) {
            createRecommisioningReport($id);
        }
    } else if ($ticketType == 'GENERAL INSPECTION' || $ticketType == 'PRE-DELIVERY' || $ticketType == 'ERECTION AND COMMISSIONING') {
        $recommisioingReportCanBeCreated = recommisioingReportCanBeCreatedGI($recordInfo);
        if ($recommisioingReportCanBeCreated == true) {
            createRecommisioningReport($id);
        }
    } else if ($ticketType == 'PREVENTIVE MAINTENANCE') {
        $recommisioingReportCanBeCreated = recommisioingReportCanBeCreatedGI($recordInfo);
        if ($recommisioingReportCanBeCreated == true) {
            createRecommisioningReport($id);
        }
    } else if ($ticketType == 'INSTALLATION OF SUB ASSEMBLY FITMENT') {
        $recommisioingReportCanBeCreated = recommisioingReportCanBeCreatedIOSAF($recordInfo);
        if ($recommisioingReportCanBeCreated == true) {
            createRecommisioningReport($id);
        }
    } else if ($ticketType == 'SERVICE FOR SPARES PURCHASED' && $purpose == 'WARRANTY CLAIM FOR SUB ASSEMBLY / OTHER SPARE PARTS') {
        $recommisioingReportCanBeCreated = recommisioingReportCanBeCreatedSFSP($recordInfo);
        if ($recommisioingReportCanBeCreated == true) {
            createRecommisioningReport($id);
        }
    } else if ($ticketType == 'DESIGN MODIFICATION') {
        $recommisioingReportCanBeCreated = recommisioingReportCanBeCreatedDesignModification($recordInfo);
        if ($recommisioingReportCanBeCreated == true) {
            createRecommisioningReport($id);
        }
    }
    return $recommisioingReportCanBeCreated;
}

function recommisioingReportCanBeCreatedSFSP($recordInfo) {
    $equipmentStatus = $recordInfo['eq_sta_aft_act_t_sub'];
    if ($equipmentStatus == 'Not Working' || $equipmentStatus == 'Working with Problem') {
        return true;
    } else {
        return false;
    }
}

function recommisioingReportCanBeCreatedIOSAF($recordInfo) {
    $equipmentStatus = $recordInfo['at_sais'];
    if ($equipmentStatus == 'Not Completed') {
        return true;
    } else {
        return false;
    }
}

function recommisioingReportCanBeCreatedDesignModification($recordInfo) {
    $equipmentStatus = $recordInfo['at_dm_status'];
    if ($equipmentStatus == 'Not Completed') {
        return true;
    } else {
        return false;
    }
}

function recommisioingReportCanBeCreatedGI($recordInfo) {
    $equipmentStatus = $recordInfo['eq_sta_aft_act_taken'];
    $NeedToCreateEqSta = false;
    if ($equipmentStatus == 'Off Road') {
        return true;
    }
    if ($equipmentStatus == 'On Road' || $equipmentStatus == 'Running with Problem') {
        $NeedToCreateEqSta = true;
    }

    $id = $recordInfo['id'];
    $id = explode('x', $id);
    $id = $id[1];

    $demandCheck = getDemandCheckGI($id);

    if ($demandCheck == true && $NeedToCreateEqSta == true) {
        return true;
    } else {
        return false;
    }
}

function getDemandCheckGI($id) {
    global $adb;
    $sql = "select 1 from vtiger_inventoryproductrel  where id = ? and sr_action_one IN (? , ?)";
    $result = $adb->pquery($sql, array($id, 'To be Repaired', 'To be replaced'));
    $count = $adb->num_rows($result);
    if ($count > 0) {
        return true;
    } else {
        return false;
    }
}

function FailedPartCanBeCreated($id) {
    global $adb;
    $sql = "select 1 from vtiger_inventoryproductrel  where id = ? and sr_action_one = ?
     and sr_replace_action = ?";
    $result = $adb->pquery($sql, array($id, 'Replaced', 'From BEML Stock'));
    $count = $adb->num_rows($result);
    if ($count > 0) {
        return true;
    } else {
        return false;
    }
}

function recommisioingReportCanBeCreated($recordInfo) {
    $equipmentStatus = $recordInfo['eq_sta_aft_act_taken'];
    $NeedToCreateEqSta = false;
    if ($equipmentStatus == 'Off Road') {
        return true;
    }
    if ($equipmentStatus == 'On Road' || $equipmentStatus == 'Running with Problem') {
        $NeedToCreateEqSta = true;
    }

    $id = $recordInfo['id'];
    $id = explode('x', $id);
    $id = $id[1];

    $demandCheck = getDemandCheck($id);

    if ($demandCheck == true && $NeedToCreateEqSta == true) {
        return true;
    } else {
        return false;
    }
}

function getDemandCheck($id) {
    global $adb;
    $sql = "select 1 from vtiger_inventoryproductrel  where id = ? and sr_action_one IN (? , ?)
     and sr_action_two = ?";
    $result = $adb->pquery($sql, array($id, 'To be Repaired', 'To be replaced', 'Required'));
    $count = $adb->num_rows($result);
    if ($count > 0) {
        return true;
    } else {
        return false;
    }
}

function getAsArrayOfCodes($recFieldValue, $fieldName) {
    global $adb;
    $products = [];
    $product = [];
    $reordMultiValues = explode('|##|', $recFieldValue);
    foreach ($reordMultiValues as $reordMultiValue) {
        $sql = "select code , code_group from vtiger_$fieldName "
            . " where $fieldName = ?";
        $sqlResult = $adb->pquery($sql, array(trim($reordMultiValue)));
        $dataRow = $adb->fetchByAssoc($sqlResult, 0);
        $typeOfDamageCode = '';
        $typeOfDamageGroupCode = '';
        if (!empty($dataRow)) {
            $typeOfDamageCode = $dataRow['code'];
            $typeOfDamageGroupCode = $dataRow['code_group'];
            $product['LINE'] = $typeOfDamageGroupCode . ',' . $typeOfDamageCode;
            array_push($products, $product);
        }
    }
    return $products;
}

function SyncToExternalOnSERCreate($entityData) {
    CreateOnlyNotification($entityData);
}

function getProductsOfServiceReport($recordId) {
    global $adb;
    $query = "SELECT
        case when vtiger_products.productid != '' then vtiger_products.productname else vtiger_service.servicename end as productname,
        case when vtiger_products.productid != '' then vtiger_products.product_no else vtiger_service.service_no end as productcode,
        case when vtiger_products.productid != '' then vtiger_products.unit_price else vtiger_service.unit_price end as unit_price,
        case when vtiger_products.productid != '' then vtiger_products.qtyinstock else 'NA' end as qtyinstock,
        case when vtiger_products.productid != '' then 'Products' else 'Services' end as entitytype,
        vtiger_inventoryproductrel.listprice, vtiger_products.is_subproducts_viewable, 
        vtiger_inventoryproductrel.description AS product_description, vtiger_inventoryproductrel.*,
        vtiger_crmentity.deleted FROM vtiger_inventoryproductrel
        LEFT JOIN vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_inventoryproductrel.productid
        LEFT JOIN vtiger_products ON vtiger_products.productid=vtiger_inventoryproductrel.productid
        LEFT JOIN vtiger_service ON vtiger_service.serviceid=vtiger_inventoryproductrel.productid
        WHERE id=? ORDER BY sequence_no";
    $params = array($recordId);
    $result = $adb->pquery($query, $params);
    $num_rows = $adb->num_rows($result);
    $products = [];
    for ($i = 0; $i < $num_rows; $i++) {
        $product = array();
        $product['MATNR'] = $adb->query_result($result, $i, 'productname');
        $product['MENGE'] = $adb->query_result($result, $i, 'quantity');
        $product['POSTP'] = 'L';
        array_push($products, $product);
    }
    return $products;
}

function getCodeOFValue($keyTable, $value) {
    // global $adb;
    // $sql = 'select code from vtiger_' . $keyTable
    //     . ' where ' . $keyTable . ' = ?';
    // $sqlResult = $adb->pquery($sql, array($value));
    // $dataRow = $adb->fetchByAssoc($sqlResult, 0);
    // $code = '';
    // if (empty($dataRow)) {
    //     $code = '';
    // } else {
    //     $code = $dataRow['code'];
    // }
    // return $code;
    $code = '';
    switch ($value) {
        case "On Road":
            $code = '1';
            break;
        case "Running with Problem":
            $code = '2';
            break;
        case "Off Road":
            $code = '3';
            break;
        default:
            $code = '';
    }
    return $code;
}

function createFailedPartsRecords($id, $ticketId, $sapNotiNumber) {
    $salesorder_id = $id;
    require_once('include/utils/utils.php');
    require_once('modules/ServiceReports/ServiceReports.php');
    require_once('modules/FailedParts/FailedParts.php');
    require_once('modules/Users/Users.php');

    global $current_user;
    if (!$current_user) {
        $current_user = Users::getActiveAdminUser();
    }
    $so_focus = new ServiceReports();
    $so_focus->id = $salesorder_id;
    $so_focus->retrieve_entity_info($salesorder_id, "ServiceReports");
    foreach ($so_focus->column_fields as $fieldname => $value) {
        $so_focus->column_fields[$fieldname] = decode_html($value);
    }

    $focus = new FailedParts();
    $focus = getConvertSrepToServiceOrder($focus, $so_focus, $salesorder_id);
    $focus->id = '';
    $focus->mode = '';
    $invoice_so_fields = array(
        'txtAdjustment' => 'txtAdjustment',
        'hdnSubTotal' => 'hdnSubTotal',
        'hdnGrandTotal' => 'hdnGrandTotal',
        'hdnTaxType' => 'hdnTaxType',
        'hdnDiscountPercent' => 'hdnDiscountPercent',
        'hdnDiscountAmount' => 'hdnDiscountAmount',
        'hdnS_H_Amount' => 'hdnS_H_Amount',
        'assigned_user_id' => 'assigned_user_id',
        'currency_id' => 'currency_id',
        'conversion_rate' => 'conversion_rate',
    );
    foreach ($invoice_so_fields as $invoice_field => $so_field) {
        $focus->column_fields[$invoice_field] = $so_focus->column_fields[$so_field];
    }
    $focus->column_fields['ticket_id'] = $ticketId;
    $focus->column_fields['equipment_id'] = $so_focus->column_fields['equipment_id'];
    $focus->column_fields['project_name'] = $so_focus->column_fields['project_name'];
    $focus->column_fields['sr_app_num'] = $sapNotiNumber;
    $focus->column_fields['replaced_date'] = $so_focus->column_fields['createdtime'];
    global $replacedDate;
    $replacedDate = $so_focus->column_fields['createdtime'];
    $focus->_servicereportid = $salesorder_id;
    $focus->_recurring_mode = 'duplicating_from_service_report';
    global $creationOfFailedPartRecord;
    $creationOfFailedPartRecord = true;

    $focus->save("FailedParts");
    global $adb;
    if (!empty($focus->id)) {
        $query = "UPDATE vtiger_failedparts SET sr_app_num = ? WHERE failedpartid=?";
        $adb->pquery($query, array($sapNotiNumber, $focus->id));
    }
    return $focus->id;
}

function createRecommisioningReport($id) {
    global $adb, $noTRiggerOFWorkFlowForRR;
    global $noUploadOfAttachment;
    $noUploadOfAttachment = true;
    $noTRiggerOFWorkFlowForRR = true;
    $query = "UPDATE vtiger_servicereports SET is_recommisionreport = ? WHERE servicereportsid=?";
    $adb->pquery($query, array('1', $id));
    $salesorder_id = $id;
    require_once('include/utils/utils.php');
    require_once('modules/ServiceReports/ServiceReports.php');
    require_once('modules/RecommissioningReports/RecommissioningReports.php');
    require_once('modules/Users/Users.php');

    global $current_user;
    if (!$current_user) {
        $current_user = Users::getActiveAdminUser();
    }
    $so_focus = new ServiceReports();
    $so_focus->id = $salesorder_id;
    $so_focus->retrieve_entity_info($salesorder_id, "ServiceReports");
    foreach ($so_focus->column_fields as $fieldname => $value) {
        $so_focus->column_fields[$fieldname] = decode_html($value);
    }

    $focus = new RecommissioningReports();
    $focus = getConvertSrepToServiceOrder($focus, $so_focus, $salesorder_id);
    $focus->id = '';
    $focus->mode = '';
    $invoice_so_fields = array(
        'txtAdjustment' => 'txtAdjustment',
        'hdnSubTotal' => 'hdnSubTotal',
        'hdnGrandTotal' => 'hdnGrandTotal',
        'hdnTaxType' => 'hdnTaxType',
        'hdnDiscountPercent' => 'hdnDiscountPercent',
        'hdnDiscountAmount' => 'hdnDiscountAmount',
        'hdnS_H_Amount' => 'hdnS_H_Amount',
        'assigned_user_id' => 'assigned_user_id',
        'currency_id' => 'currency_id',
        'conversion_rate' => 'conversion_rate',
    );
    foreach ($invoice_so_fields as $invoice_field => $so_field) {
        $focus->column_fields[$invoice_field] = $so_focus->column_fields[$so_field];
    }
    foreach ($so_focus->column_fields as $fieldname => $value) {
        $focus->column_fields[$fieldname] = decode_html($value);
    }
    $focus->_servicereportid = $salesorder_id;
    $focus->_recurring_mode = 'creating_rr_from_service_report';
    $focus->column_fields['ticketstatus'] = 'In Progress';
    $focus->column_fields['is_submitted'] = '0';
    // if ($so_focus->column_fields['is_submitted'] == '1') {
    //     $focus->column_fields['ticketstatus'] = 'Closed';
    // }
    $focus->save("RecommissioningReports");
    return $focus->id;
}
