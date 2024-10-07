<?php

/* * *******************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

$memory_limit = substr(ini_get("memory_limit"), 0, -1);
if ($memory_limit < 256) {
    ini_set("memory_limit", "256M");
}

class PDFMaker_SaveIntoDocuments_Action extends Vtiger_Action_Controller
{

    public function checkPermission(Vtiger_Request $request)
    {
    }

    public function process(Vtiger_Request $request)
    {

        require_once("modules/PDFMaker/resources/mpdf/mpdf.php");
        $adb = PearDatabase::getInstance();
        $PDFMaker = new PDFMaker_PDFMaker_Model();

        $language = Vtiger_Language_Handler::getLanguage();
        if ($request->has('language') && !$request->isEmpty('language')) {
            $language = $request->get('language');
        }

        $parentModuleName = $request->get('pmodule');
        $parentid = $request->get('pid');
        $forview = $request->get('forview');

        if ($forview == "List") {
            $PDFMakerModuleModel = Vtiger_Module_Model::getInstance('PDFMaker');
            $Records = $PDFMakerModuleModel->getRecordsListFromRequest($request);
            $file_name = "doc_" . $parentModuleName . date("ymdHi") . ".pdf";
        } else {

            $modFocus = CRMEntity::getInstance($parentModuleName);
            if (isset($parentid) && is_numeric($parentid)) {
                $modFocus->retrieve_entity_info($parentid, $parentModuleName);
                $modFocus->id = $parentid;

                $Records = array($modFocus->id);
            }

            $result = $adb->query("SELECT fieldname FROM vtiger_field WHERE uitype=4 AND tabid=" . getTabId($parentModuleName));
            $fieldname = $adb->query_result($result, 0, "fieldname");
            if (isset($modFocus->column_fields[$fieldname]) && $modFocus->column_fields[$fieldname] != "") {
                $file_name = $PDFMaker->generate_cool_uri($modFocus->column_fields[$fieldname]) . ".pdf";
            } else {
                $file_name = "doc_" . $parentid . date("ymdHi") . ".pdf";
            }
        }

        $moduleName = "Documents";
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
        $recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
        $recordModel->set('filename', $file_name);
        $recordModel->set('filetype', 'application/pdf');
        $recordModel->set('fileversion', 'I');
        $recordModel->set('filestatus', 'on');
        $recordModel->set('parentid', $parentid);

        $fieldModelList = $moduleModel->getFields();
        foreach ($fieldModelList as $fieldName => $fieldModel) {
            if ($request->has($fieldName)) {
                $fieldValue = $request->get($fieldName, null);
            } else {
                $fieldValue = $fieldModel->getDefaultFieldValue();
            }
            $fieldDataType = $fieldModel->getFieldDataType();
            if ($fieldDataType == 'time') {
                $fieldValue = Vtiger_Time_UIType::getTimeValueWithSeconds($fieldValue);
            }
            if ($fieldValue !== null) {
                if (!is_array($fieldValue)) {
                    $fieldValue = trim($fieldValue);
                }
                $recordModel->set($fieldName, $fieldValue);
            }
        }

        $recordModel->save();
        $new_crmid = $recordModel->getId();

        $focus = CRMEntity::getInstance($moduleName);
        $focus->retrieve_entity_info($new_crmid, $moduleName);
        $focus->id = $new_crmid;

        $focus->insertintonotesrel($parentid, $new_crmid);

        //saving the relation to Contacts
        $pdfdoc_contact_id = $request->get('pdfdoc_contact_id');
        if (isset($pdfdoc_contact_id) && $pdfdoc_contact_id != "") {
            $sql = "INSERT INTO vtiger_senotesrel(crmid, notesid) VALUES(?,?)";
            $adb->pquery($sql, array($pdfdoc_contact_id, $focus->id));
        }
        //saving the relation to Accounts
        $pdfdoc_account_id = $request->get('pdfdoc_account_id');
        if (isset($pdfdoc_account_id) && $pdfdoc_account_id != "") {
            $sql = "INSERT INTO vtiger_senotesrel(crmid, notesid) VALUES(?,?)";
            $adb->pquery($sql, array($pdfdoc_account_id, $focus->id));
        }

        if ($request->has('template_ids') && !$request->isEmpty('template_ids')) {
            $template_ids = $request->get('template_ids');
        } else {
            $default_mode = "1";
            $forview = $request->get('forview');
            if ($forview == "List") {
                $default_mode = "2";
            }

            $PDFMakerModuleModel = Vtiger_Module_Model::getInstance('PDFMaker');
            $template_ids = $PDFMakerModuleModel->GetDefaultTemplates($default_mode, $parentModuleName);
        }


        $PDFMaker->createPDFAndSaveFile($request, $template_ids, $focus, $Records, $file_name, $parentModuleName, $language);

        $result = array("success" => true, "message" => vtranslate("LBL_PDF_ADDED_DOC", "PDFMaker"));


        $response = new Vtiger_Response();
        try {
            $response->setResult($result);
        } catch (Exception $e) {
            $response->setError($e->getCode(), $e->getMessage());
        }
        $response->emit();
    }
}