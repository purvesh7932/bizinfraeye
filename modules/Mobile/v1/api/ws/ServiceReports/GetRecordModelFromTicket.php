<?php
include_once('include/utils/GeneralUtils.php');
class Mobile_WS_GetRecordModelFromTicket extends Mobile_WS_Controller {
    function process(Mobile_API_Request $request) {
        $response = new Mobile_API_Response();
        $moduleName = $request->get('module');
        $recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);

        $sourceRecord = $request->get('sourceRecord');
        $sourceModule = $request->get('sourceModule');
        if (empty($sourceRecord)) {
            $response->setError(100, "sourceRecord Is Missing");
            return $response;
        }
        if (strpos($sourceRecord, 'x') == false) {
            $response->setError(100, 'sourceRecord Is Not Webservice Format');
            return $response;
        }
        $sourceRecord = explode('x', $sourceRecord);
        $sourceRecord = $sourceRecord[1];

        $nonEditKeys = [];
        $moduleModel = Vtiger_Module_Model::getInstance($sourceModule);
        if (!empty($sourceRecord)) {
            $recordInstnce = Vtiger_Record_Model::getInstanceById($sourceRecord, $sourceModule);
            $fM = $this->getMapping();
            foreach ($fM as $key => $value) {
                $aKey = $value['HelpDesk']['igFieldName'];
                $bKey = $value['ServiceReports']['igFieldName'];

                $field = Vtiger_Field_Model::getInstance($aKey, $moduleModel);
                if ($field->get('uitype') == '10') {
                    $recId = $recordInstnce->get($aKey);
                    if ($recId == '0' || empty($recId)) {
                        $recordModel->set($bKey, '');
                        $recordModel->set($bKey . '_Label', "");
                        array_push($nonEditKeys, $bKey . '_Label');
                    } else {
                        $data = $this->getModuleLableAndSetType($recId);
                        $moduleWSID = Mobile_WS_Utils::getEntityModuleWSId($data['setype']);
                        $recordModel->set($bKey, $moduleWSID . 'x' . $recId);
                        $recordModel->set($bKey . '_Label', $data['label']);
                        array_push($nonEditKeys, $bKey . '_Label');
                    }
                } else if ($field->get('uitype') == '5') {
                    $recordModel->set($bKey, Vtiger_Date_UIType::getDisplayDateValue($recordInstnce->get($aKey)));
                } else {
                    $recordModel->set($bKey, $recordInstnce->get($aKey));
                }
                array_push($nonEditKeys, $bKey);
            }
            if (empty($recordModel->get('kilometer_reading'))) {
				$recordModel->set('kilometer_reading', '');
				$recordModel->set('kilo_date', '');
			}
			if (empty($recordModel->get('hmr'))) {
				$recordModel->set('hmr', '');
				$recordModel->set('sr_hmr', '');
			}
            array_push($nonEditKeys, 'ticket_id');
            $bKey = 'ticket_id';
            $data = $this->getModuleLableAndSetType($sourceRecord);
            $moduleWSID = Mobile_WS_Utils::getEntityModuleWSId('HelpDesk');
            $recordModel->set($bKey, $moduleWSID . 'x' . $sourceRecord);
            $recordModel->set($bKey . '_Label', $data['label']);
            array_push($nonEditKeys, $bKey . '_Label');

            $recId = $recordInstnce->get('smcreatorid');
            $db = PearDatabase::getInstance();
            $sql = 'select user_name from vtiger_users where id = ?';
            $sqlResult = $db->pquery($sql, array($recId));
            $dataRow = $db->fetchByAssoc($sqlResult, 0);

            $sql = 'select serviceengineerid from vtiger_serviceengineer ' .
                ' inner join vtiger_crmentity on vtiger_crmentity.crmid = vtiger_serviceengineer.serviceengineerid' .
                ' where badge_no = ? and vtiger_crmentity.deleted= 0 ORDER BY serviceengineerid DESC LIMIT 1';
            $sqlResult = $db->pquery($sql, array($dataRow['user_name']));
            $dataRow = $db->fetchByAssoc($sqlResult, 0);

            $bKey = 'reported_by';
            array_push($nonEditKeys, 'reported_by');
            if (!empty($dataRow)) {
                $data = $this->getModuleLableAndSetType($dataRow['serviceengineerid']);
                $moduleWSID = Mobile_WS_Utils::getEntityModuleWSId($data['setype']);
                $recordModel->set($bKey, $moduleWSID . 'x' . $dataRow['serviceengineerid']);
                $recordModel->set($bKey . '_Label', $data['label']);
                array_push($nonEditKeys, $bKey . '_Label');
            }

            $equipmentKays = array('area_name', 'project_name');
            $nonEditKeys = array_merge($equipmentKays, $nonEditKeys);
            $recId = $recordInstnce->get('func_loc_id');
            if (!empty($recId) && isRecordExists($recId) && $recId != '0') {
                $recInstance = Vtiger_Record_Model::getInstanceById($recId, 'FunctionalLocations');
                $recordModel->set('area_name', $recInstance->get('func_area_name'));
                $recordModel->set('project_name', $recInstance->get('func_proj_name'));
            }

            $equipmentKays = array(
                'dte_of_commissing', 'type_of_conrt', 'run_year_cont',
                'cont_start_date', 'cont_end_date', 'sr_war_status',
                'sr_eq_warranty_terms', 'warranty_end_dte'
            );
            $aggregates = array(
                'sr_engine', 'sr_engine_wt', 'sr_transmission',
                'sr_transmission_wt', 'sr_final_drive', 'sr_final_drive_wt',
                'sr_rear_axle', 'sr_rear_axle_wt', 'sr_chassis', 'sr_chassis_wt',
                'eng_sl_no', 'motor_sl_no', 'trans_sl_no'
            );
            $nonEditKeys = array_merge($aggregates, $nonEditKeys);
            $nonEditKeys = array_merge($equipmentKays, $nonEditKeys);
            $equipmentId = $recordInstnce->get('equipment_id');
            if (!empty($recId) && $equipmentId != '0' && isRecordExists($equipmentId)) {
                $recInstance = Vtiger_Record_Model::getInstanceById($equipmentId, 'Equipment');
                $recordModel->set('dte_of_commissing', $recInstance->get('cust_begin_guar'));
                $recordModel->set('sr_eq_warranty_terms', $recInstance->get('equip_war_terms'));
                $recordModel->set('warranty_end_dte', $recInstance->get('cust_war_end'));
                $recordModel->set('type_of_conrt', $recInstance->get('eq_type_of_conrt'));
                $recordModel->set('run_year_cont', $recInstance->get('run_year_cont'));
                $recordModel->set('cont_start_date', $recInstance->get('cont_start_date'));
                $recordModel->set('cont_end_date', $recInstance->get('cont_end_date'));
                $recordModel->set('sr_war_status', $recInstance->get('eq_run_war_st'));

                //Implement Aggregates AutoFilllig
                $equipmentSerialNum = $recInstance->get('equipment_sl_no');
                $AgDetail = getAggregateDetailsBasedOnCode('EN', $equipmentSerialNum, $equipmentId);
                if (!empty($AgDetail)) {
                    $recordModel->set('sr_engine', $AgDetail['equipment_sl_no']);
                    $recordModel->set('sr_engine_wt', $AgDetail['equip_war_terms']);
                }
                $AgDetail = getAggregateDetailsBasedOnCode('TM', $equipmentSerialNum, $equipmentId);
                if (!empty($AgDetail)) {
                    $recordModel->set('sr_transmission', $AgDetail['equipment_sl_no']);
                    $recordModel->set('sr_transmission_wt', $AgDetail['equip_war_terms']);
                }
                $AgDetail = getAggregateDetailsBasedOnCode('FD', $equipmentSerialNum, $equipmentId);
                if (!empty($AgDetail)) {
                    $recordModel->set('sr_final_drive', $AgDetail['equipment_sl_no']);
                    $recordModel->set('sr_final_drive_wt', $AgDetail['equip_war_terms']);
                }
                $AgDetail = getAggregateDetailsBasedOnCode('RA', $equipmentSerialNum, $equipmentId);
                if (!empty($AgDetail)) {
                    $recordModel->set('sr_rear_axle', $AgDetail['equipment_sl_no']);
                    $recordModel->set('sr_rear_axle_wt', $AgDetail['equip_war_terms']);
                }
                $AgDetail = getAggregateDetailsBasedOnCode('CH', $equipmentSerialNum, $equipmentId);
                if (!empty($AgDetail)) {
                    $recordModel->set('sr_chassis', $AgDetail['equipment_sl_no']);
                    $recordModel->set('sr_chassis_wt', $AgDetail['equip_war_terms']);
                }

                $dataAg = getAggregateDetails('EN', $equipmentSerialNum, $equipmentId);
                if (!empty($dataAg)) {
                    $recordModel->set('eng_sl_no', $dataAg['equip_ag_serial_no']);
                }
                $dataAg = getAggregateDetails('TM', $equipmentSerialNum, $equipmentId);
                if (!empty($dataAg)) {
                    $recordModel->set('trans_sl_no', $dataAg['equip_ag_serial_no']);
                }
                $dataAg = getAggregateDetails('Motor', $equipmentSerialNum, $equipmentId);
                if (!empty($dataAg)) {
                    $recordModel->set('motor_sl_no', $dataAg['equip_ag_serial_no']);
                }
            }
            $recId = $recordInstnce->get('assigned_user_id');
            $db = PearDatabase::getInstance();
            $sql = 'select user_name from vtiger_users where id = ?';
            $sqlResult = $db->pquery($sql, array($recId));
            $dataRow = $db->fetchByAssoc($sqlResult, 0);

            $sql = 'select serviceengineerid from vtiger_serviceengineer' .
                ' inner join vtiger_crmentity on vtiger_crmentity.crmid = vtiger_serviceengineer.serviceengineerid' .
                ' where badge_no = ? and vtiger_crmentity.deleted= 0 ORDER BY serviceengineerid DESC LIMIT 1';
            $sqlResult = $db->pquery($sql, array($dataRow['user_name']));
            $dataRow = $db->fetchByAssoc($sqlResult, 0);

            $equipmentKays = array(
                'badge_no', 'ser_eng_name', 'sr_designaion',
                'sr_regional_office', 'dist_off_or_act_cen'
            );
            $nonEditKeys = array_merge($equipmentKays, $nonEditKeys);
            if (!empty($dataRow) && isRecordExists($dataRow['serviceengineerid'])) {
                $recInstance = Vtiger_Record_Model::getInstanceById($dataRow['serviceengineerid'], 'ServiceEngineer');
                $recordModel->set('badge_no', $recInstance->get('badge_no'));
                $recordModel->set('ser_eng_name', $recInstance->get('service_engineer_name'));
                $recordModel->set('sr_designaion', $recInstance->get('designaion'));
                $recordModel->set('sr_regional_office', $recInstance->get('regional_office'));
                $officeValue = $recInstance->get('office');
                if ($officeValue == 'Activity Centre') {
                    $recordModel->set('dist_off_or_act_cen', $recInstance->get('activity_centre'));
                } else if ($officeValue == 'District Office') {
                    $recordModel->set('dist_off_or_act_cen', $recInstance->get('district_office'));
                }
            }
            $data = $recordModel->getData();
            $recordModel = [];
            foreach ($data as $key => $value) {
                if (in_array($key, $nonEditKeys)) {
                    $recordModel[$key] = decode_html($value);
                }
            }
            global $site_URL_NonHttp;
            $imageDetails = getImageDetailsInUtils($sourceRecord);
            $attachments = [];
            foreach ($imageDetails as $imageDetail) {
                $attachment = [];
                $attachment['urlpath'] = $site_URL_NonHttp . $imageDetail['url'];
                $attachment['loadimage'] = '';
                array_push($attachments, $attachment);
            }
            $recordModel['imagename'] = $attachments;
            $ticketType = $recordInstnce->get('ticket_type');
            if ($ticketType == 'PRE-DELIVERY' || $ticketType == 'ERECTION AND COMMISSIONING') {
                $subAssemblies = getModelBasedAggregates($recordInstnce->get('sr_equip_model'));
                $i = 1;
                $allAggregateInfo = [];
                foreach ($subAssemblies as $subAssembly) {
                    $aggregateInfo = [];
                    $aggregateInfo['aggregate'] = trim($subAssembly);
                    $aggregateInfo['aggregateManufacture'] = json_decode(decode_html(IGGetDependentValuesOfPickList($subAssembly, 'masn_manu')));
                    array_push($allAggregateInfo, $aggregateInfo);
                }
                $recordModel['modelAggregates'] = $allAggregateInfo;
            } else if ($ticketType == 'INSTALLATION OF SUB ASSEMBLY FITMENT') {
                $subAssemblies = $recordInstnce->get('sub_assembly');
                if(empty($subAssemblies)){
                    $recordModel['sub_assembly'] = [];
                } else {
                    $subAssemblies = explode('|##|', $subAssemblies);
                    $trimmedSubAss = [];
                    foreach($subAssemblies as $subAss){
                        array_push($trimmedSubAss , trim($subAss));
                    }
                    $recordModel['sub_assembly'] = $trimmedSubAss;
                }
            }

            $responseObject['recordValues'] = $recordModel;
            $response->setResult($responseObject);
            $response->setApiSucessMessage('Successfully Fetched Data');
            return $response;
        }
    }

    public function getMapping($editable = false) {
        if (!$this->mapping) {
            $db = PearDatabase::getInstance();
            $query = 'SELECT * FROM vtiger_convertpotentialmapping';
            if ($editable) {
                $query .= ' WHERE editable = 1';
            }

            $result = $db->pquery($query, array());
            $numOfRows = $db->num_rows($result);
            $mapping = array();
            for ($i = 0; $i < $numOfRows; $i++) {
                $rowData = $db->query_result_rowdata($result, $i);
                $mapping[$rowData['cfmid']] = $rowData;
            }

            $finalMapping = $fieldIdsList = array();
            foreach ($mapping as $mappingDetails) {
                array_push($fieldIdsList, $mappingDetails['potentialfid'], $mappingDetails['projectfid']);
            }
            $fieldLabelsList = array();
            if (!empty($fieldIdsList)) {
                $fieldLabelsList = $this->getFieldsInfo(array_unique($fieldIdsList));
            }
            foreach ($mapping as $mappingId => $mappingDetails) {
                $finalMapping[$mappingId] = array(
                    'editable'    => $mappingDetails['editable'],
                    'HelpDesk'        => $fieldLabelsList[$mappingDetails['potentialfid']],
                    'ServiceReports'    => $fieldLabelsList[$mappingDetails['projectfid']]
                );
            }
            $this->mapping = $finalMapping;
        }
        return $this->mapping;
    }

    public function getModuleLableAndSetType($crmid) {
        global $adb;
        $sql = 'select setype,label from vtiger_crmentity where crmid = ? and deleted = 0';
        $sqlResult = $adb->pquery($sql, array($crmid));
        $num_rows = $adb->num_rows($sqlResult);
        $data = array('setype' => '', 'label' => '');
        if ($num_rows > 0) {
            $data = $adb->fetchByAssoc($sqlResult, 0);
        }
        return $data;
    }

    public function getFieldsInfo($fieldIdsList) {
        $db = PearDatabase::getInstance();
        $result = $db->pquery('SELECT fieldid, fieldlabel, uitype, typeofdata, fieldname, tablename, tabid FROM vtiger_field WHERE fieldid IN (' . generateQuestionMarks($fieldIdsList) . ')', $fieldIdsList);
        $numOfRows = $db->num_rows($result);
        $fieldLabelsList = array();
        for ($i = 0; $i < $numOfRows; $i++) {
            $rowData = $db->query_result_rowdata($result, $i);
            $fieldInfo = array('id' => $rowData['fieldid'], 'label' => $rowData['fieldlabel']);
            $fieldModel = Settings_Leads_Field_Model::getCleanInstance();
            $fieldModel->set('uitype', $rowData['uitype']);
            $fieldModel->set('typeofdata', $rowData['typeofdata']);
            $fieldModel->set('name', $rowData['fieldname']);
            $fieldModel->set('table', $rowData['tablename']);
            $fieldInfo['igFieldName'] = $rowData['fieldname'];
            $fieldInfo['fieldDataType'] = $fieldModel->getFieldDataType();
            $fieldLabelsList[$rowData['fieldid']] = $fieldInfo;
        }
        return $fieldLabelsList;
    }
}
