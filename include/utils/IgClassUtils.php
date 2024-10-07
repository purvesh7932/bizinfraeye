<?php
class IgClassUtils {
    static function getMandatoryFieldsBasedOnType($type, $purposeValue) {
        if ($type == 'GENERAL INSPECTION' || $type == 'SERVICE FOR SPARES PURCHASED') {
            $fieldDependeny = Vtiger_DependencyPicklist::getFieldsFitDependency('HelpDesk', 'ticket_type', 'purpose');
            $type = $purposeValue;
        } else {
            $fieldDependeny = Vtiger_DependencyPicklist::getFieldsFitDependency('HelpDesk', 'ticket_type', 'purpose');
        }
        foreach ($fieldDependeny['valuemapping'] as $valueMapping) {
            if ($valueMapping['sourcevalue'] == $type) {
                return $valueMapping['targetvalues'];
            }
        }
    }

    static function getMandatoryFieldsBasedOnTypeForServiceReport($type, $purposeValue) {
        if ($type == 'GENERAL INSPECTION' || $type == 'SERVICE FOR SPARES PURCHASED') {
            $fieldDependeny = Vtiger_DependencyPicklist::getFieldsFitDependency('ServiceReports', 'sr_ticket_type', 'tck_det_purpose');
            $type = $purposeValue;
        } else {
            $fieldDependeny = Vtiger_DependencyPicklist::getFieldsFitDependency('ServiceReports', 'sr_ticket_type', 'tck_det_purpose');
        }
        foreach ($fieldDependeny['valuemapping'] as $valueMapping) {
            if ($valueMapping['sourcevalue'] == $type) {
                return $valueMapping['targetvalues'];
            }
        }
    }

    static function getEmployeePlanCode($recordId) {
        global $adb;
        $sql = "SELECT office,regional_office,service_centre,district_office,activity_centre
         FROM vtiger_serviceengineer WHERE serviceengineerid=?";
        $sqlResult = $adb->pquery($sql, array($recordId));
        $sqlData = $adb->fetch_array($sqlResult);
        $office = $sqlData['office'];
        $plantName = '';
        if ($office == 'Service Centre') {
            $plantName = $sqlData['service_centre'];
        } else if ($office == 'District Office') {
            $plantName = $sqlData['district_office'] . '-Depot';
        } else if ($office == 'Regional Office') {
            $plantName = $sqlData['regional_office'] . '-Depot';
        } else {
            $plantName = $sqlData['regional_office'] . '-Depot';
        }
        $sql = "SELECT m.maintenanceplantid, plant_code FROM vtiger_maintenanceplant as m WHERE m.plant_desc=?";
        $sqlResult = $adb->pquery($sql, array(decode_html($plantName)));
        $sqlData = $adb->fetch_array($sqlResult);
        if (!empty($sqlData['maintenanceplantid'])) {
            return $sqlData['plant_code'];
        } else {
            return '';
        }
    }

    static function saveLineDetailsEquipment($total_year_cont, $recordId, $module) {
        global $log, $adb;
        $table = 'vtiger_inventoryproductrel_equipment';
        deleteEquipmentLinesWithRecordId($recordId, $table);
        $prod_seq = 1;
        global $current_user;
        $formate = $current_user->date_format;
        $fieldNames = [];
        $fieldNamesData = [];
        if ($module == 'Equipment') {
            $tabId = getTabId($module);
            $sql = "SELECT * FROM `vtiger_field` LEFT JOIN vtiger_blocks
             on vtiger_blocks.blockid = vtiger_field.block where vtiger_field.tabid = ? 
             and helpinfo = 'li_lg' and blocklabel = ? and presence != 1 ORDER BY `vtiger_field`.`sequence` ASC;";
            $result = $adb->pquery($sql, array($tabId, 'daadcp_lineblock'));
            while ($row = $adb->fetch_array($result)) {
                array_push($fieldNames, $row['fieldname']);
                array_push($fieldNamesData, $row);
            }
        }
        for ($i = 1; $i <= $total_year_cont; $i++) {
            if ($module == 'Equipment') {
                $fieldNameValues = [];
                foreach ($fieldNamesData as $fieldNamesdata) {
                    $valueOfTheField = vtlib_purify($_REQUEST[$fieldNamesdata['fieldname'] . $i]);
                    if ($fieldNamesdata['uitype'] == '56') {
                        if ($valueOfTheField == 'on') {
                            $valueOfTheField = 1;
                        } else {
                            $valueOfTheField = 0;
                        }
                    } else if ($fieldNamesdata['uitype'] == '5') {
                        $date = new DateTimeField($valueOfTheField);
                        $valueOfTheField = $date::__convertToDBFormat($valueOfTheField, $formate);
                    }
                    array_push($fieldNameValues, $valueOfTheField);
                }
                if (empty($fieldNames)) {
                    $query = "INSERT INTO  $table(id)
                        VALUES(?)";
                } else {
                    $query = "INSERT INTO $table(id, " . implode(',', $fieldNames) . ")
                        VALUES(?," . generateQuestionMarks($fieldNames) . ")";
                }
                $qparams = array($recordId);
                $qparams = array_merge($qparams, $fieldNameValues);
                $adb->pquery($query, $qparams);
                $adb->getLastInsertID();
                $prod_seq++;
            }
        }
    }

    static function createFailedPartsRecords($id, $ticketId, $sapNotiNumber) {
        $salesorder_id = $id;
        require_once('include/utils/utils.php');
        require_once('modules/RecommissioningReports/RecommissioningReports.php');
        require_once('modules/FailedParts/FailedParts.php');
        require_once('modules/Users/Users.php');

        global $current_user;
        if (!$current_user) {
            $current_user = Users::getActiveAdminUser();
        }
        $so_focus = new RecommissioningReports();
        $so_focus->id = $salesorder_id;
        $so_focus->retrieve_entity_info($salesorder_id, "RecommissioningReports");
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

    static function FailedPartCanBeCreated($id) {
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

    static function handleUpdatedOfNotification(
        $recordInfo,
        $ticketCreatedDateTime,
        $notiType,
        $id,
        $exterAppNum,
        $igModule
    ) {
        global $adb;
        global $log;
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
            if (!empty($equipId)) {
                $recordInstance = Vtiger_Record_Model::getInstanceById($equipId);
                $SAPrefEquip = $recordInstance->get('manual_equ_ser');
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
        $conditionAfterActionCode = self::getCodeOFValue('eq_sta_aft_act_taken', $conditionAfterAction);

        $conditionBeforeSRGen = $recordInfo['fd_eq_sta_bsr'];
        $conditionBeforeSRGenCode = self::getCodeOFValue('fd_eq_sta_bsr', $conditionBeforeSRGen);

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

        $url = getExternalAppURL('ChangeSR');
        $header = array('Content-Type:multipart/form-data');

        $im_msausVal = '';
        if ($recordInfo['eq_sta_aft_act_taken'] == 'Off Road') {
            $im_msausVal = 'X';
        }

        $formatToSAP = strval($exterAppNum);
        $zerosToAppend = 12 - strlen($formatToSAP);
        if ($zerosToAppend > 0) {
            $exterAppNum = sprintf("%012d", $exterAppNum);
        }

        $data = array(
            'IM_QMNUM' => $exterAppNum,
            'IM_TEXT' => $symptoms,
            'IM_EQUNR'  => $SAPrefEquip,
            'IM_MSAUS' => $im_msausVal,
            'IM_LTEXT1' => $Observation,
            'IM_LTEXT2' => $actionTaken,
            'IM_LTEXT3' => '',
            'IM_LTEXT4' => '',
            'IM_REPORTEDBY' => $reportedBy,
            'IM_RESPOSIBLE' => $partPertainsToCodeGroup . ',' . $partPertainsToCode,
            'IM_EFFECT' =>  getValueEffect($recordInfo['eq_sta_aft_act_taken']),
            'IM_BEFORE_MALFUNC' => $conditionBeforeSRGenCode,
            'IM_AFTER_MALFUNC' =>  self::getCodeOFValue('sr_equip_status', $recordInfo['sr_equip_status']),
            'IM_COND_AFTERTASK' =>  $conditionAfterActionCode,
            'IM_MALFUNC_STARTDATE' => $malfunctionStartDateSAPFormat,
            'IM_MALFUNC_ENDDATE' => $malfunctionEndDateSAPFormat,
            'IM_MALFUNC_ENDTIME' => $malfunctionEndTimeSAPFormat,
            'IM_NOTIFDATE' => str_replace('-', '', $ticketCreatedDateTimeArr[0]),
            'IM_NOTIFTIME' => $ticketTimeSAPFormat
        );

        if ($data['IM_RESPOSIBLE'] == ',') {
            $data['IM_RESPOSIBLE'] = "";
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
            if ($recordInfo['eq_sta_aft_act_taken'] != 'Off Road' && $recordInfo['sr_ticket_type'] != 'BREAKDOWN') {
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
            $data['IT_OBJECTPART'] = json_encode(self::getAsArrayOfCodes($recordInfo['fail_de_system_affected'], 'fail_de_system_affected'));
            $data['IT_DAMAGE'] = json_encode(self::getAsArrayOfCodes($recordInfo['fail_de_parts_affected'], 'fail_de_parts_affected'));
            $data['IT_CAUSE'] = json_encode(self::getAsArrayOfCodes($recordInfo['fail_de_type_of_damage'], 'fail_de_type_of_damage'));
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
            if ($igModule == 'ServiceReports') {
                $query = "UPDATE vtiger_servicereports SET is_submitted = ? WHERE servicereportsid=?";
                $adb->pquery($query, array('0', $id));
            } else {
                $query = "UPDATE vtiger_recommissioningreports SET is_submitted = ? WHERE recommissioningreportsid=?";
                $adb->pquery($query, array('0', $id));
            }
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
                if ($igModule == 'ServiceReports') {
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
                } else {
                    if (empty($jsonParseError)) {
                        $_SESSION["errorFromExternalApp"] = IgGetSAPErrorFormatASerrorArray($response['IT_RETURN']);
                        $_SESSION["lastSyncedExterAppRecord"] = $id;
                        header("Location: index.php?module=RecommissioningReports&view=Edit&record=$id&app=SUPPORT");
                        exit();
                    } else {
                        $_SESSION["errorFromExternalApp"] = $responseUnEncoded;
                        $_SESSION["lastSyncedExterAppRecord"] = $id;
                        header("Location: index.php?module=RecommissioningReports&view=Edit&record=$id&app=SUPPORT");
                        exit();
                    }
                }
            }
        }
    }

    public function getCodeOFValue($keyTable, $value) {
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

    public function getAsArrayOfCodes($recFieldValue, $fieldName) {
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
}
