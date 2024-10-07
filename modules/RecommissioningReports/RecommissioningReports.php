<?php

class RecommissioningReports extends CRMEntity {

    var $table_name = 'vtiger_recommissioningreports';
    var $table_index = 'recommissioningreportsid';
    var $moduleName = 'RecommissioningReports';
    var $customFieldTable = Array('vtiger_recommissioningreportscf', 'recommissioningreportsid');
    var $tab_name = array(
        'vtiger_crmentity', 'vtiger_recommissioningreports',
        'vtiger_recommissioningreportscf', 'vtiger_inventoryproductrel',
        'vtiger_recommissioningreports_other', 'vtiger_inventoryproductrel_other_masn',
        'vtiger_inventoryproductrel_other', 'vtiger_crmentity_user_field'
    );
    var $tab_name_index = Array(
        'vtiger_crmentity' => 'crmid',
        'vtiger_recommissioningreports' => 'recommissioningreportsid',
        'vtiger_recommissioningreports_other' => 'recommissioningreportsid',
        'vtiger_recommissioningreportscf' => 'recommissioningreportsid',
        'vtiger_inventoryproductrel_other' => 'id',
        'vtiger_inventoryproductrel_other_masn' => 'id',
        'vtiger_inventoryproductrel' => 'id',
        'vtiger_crmentity_user_field' => 'recordid'
    );
    var $list_fields = Array(
        'Name' => Array('RecommissioningReports', 'creditnote_name'),
        'Assigned To' => Array('crmentity', 'smownerid')
    );
    var $list_fields_name = Array(
        'Name' => 'creditnote_name',
        'Assigned To' => 'assigned_user_id',
    );
    var $list_link_field = 'creditnote_name';
    var $search_fields = Array(
        'Name' => Array('RecommissioningReports', 'creditnote_name'),
        'Assigned To' => Array('crmentity', 'assigned_user_id'),
    );
    var $search_fields_name = Array(
        'Name' => 'creditnote_name',
        'Assigned To' => 'assigned_user_id',
    );
    var $popup_fields = Array('creditnote_name');
    var $def_basicsearch_col = 'creditnote_name';
    var $def_detailview_recname = 'creditnote_name';
    var $mandatory_fields = Array('creditnote_name', 'creditnote_status', 'assigned_user_id', 'createdtime', 'modifiedtime', 'quantity', 'productid', 'netprice');
    var $default_order_by = 'creditnote_name';
    var $default_sort_order = 'ASC';
    var $isLineItemUpdate = true;

    function RecommissioningReports() {
        $this->db = PearDatabase::getInstance();
        $this->column_fields = getColumnFields('RecommissioningReports');
    }

    public function vtlib_handler($moduleName, $eventType) {
        require_once('include/utils/utils.php');
        if ($eventType == 'module.postinstall') {
            $this->addLinks();
            $this->registerHandlers();
            $this->enableModtracker();
            $this->setModuleSeqNumber('configure', $this->moduleName, 'CN-', 1);
            vtws_addModuleTypeWebserviceEntity('RecommissioningReports', 'include/Webservices/VtigerModuleOperation.php', 'VtigerModuleOperation');
            $this->applyRelateModuleChanges();
            $this->applyPostInstallSchemaChanges();
            $this->addRelatedFields();
            $this->enableProfileUtilities();
            $this->addTaxFields($eventType);
            $db = PearDatabase::getInstance();
            $tandC = $db->pquery('SELECT 1 FROM vtiger_inventory_tandc where type=?', array('RecommissioningReports'));
            if (!($db->num_rows($tandC))) {
                $db->pquery('INSERT INTO vtiger_inventory_tandc(id, type) values(?,?)', array($db->getUniqueId("vtiger_inventory_tandc"), 'RecommissioningReports'));
            }
        } else if ($eventType == 'module.preuninstall') {
            $this->disableModtracker();
            $this->deleteLinks();
            $this->unregisterEventHandlers();
            $this->revertRelateModuleChanges();
            $this->removeTaxFields();
        } else if ($eventType == 'module.enabled') {
            $this->addLinks();
            $this->registerHandlers();
        } else if ($eventType == 'module.disabled') {
            $this->deleteLinks();
            $this->unregisterEventHandlers();
        } else if ($eventType == 'module.postupdate') {
            $this->addLinks();
            $this->registerHandlers();
            $this->enableProfileUtilities();
            $this->addTaxFields($eventType);
        }
    }

    public function addTaxFields($eventType) {
        $db = PearDatabase::getInstance();
        $moduleInstance = Vtiger_Module_Model::getInstance('RecommissioningReports');
        $productTaxes = Inventory_TaxRecord_Model::getProductTaxes();
        $itemsBlock = Vtiger_Block_Model::getInstance('LBL_ITEM_DETAILS', $moduleInstance);

        if ($itemsBlock) {
            foreach ($productTaxes as $taxInfo) {
                $fieldInstance = Vtiger_Field_Model::getInstance($taxInfo->get('taxname'), $moduleInstance);
                if (!$fieldInstance) {
                    $taxField = new Vtiger_Field();
                    $taxField->name = $taxInfo->get('taxname');
                    $taxField->label = $taxInfo->get('taxlabel');
                    $taxField->column = $taxInfo->get('taxname');
                    $taxField->uitype = 83;
                    $taxField->typeofdata = 'V~O';
                    $taxField->readonly = 1;
                    $taxField->presence = 2;
                    $taxField->displaytype = 5;
                    $taxField->table = 'vtiger_inventoryproductrel';
                    $itemsBlock->addField($taxField);
                    if ($eventType == 'module.postinstall') {
                        $db->pquery('UPDATE vtiger_profile2field SET readonly=? WHERE tabid=? AND fieldid=? AND readonly=?', array(1, $taxField->getModuleId(), $taxField->id, 0));
                    }
                }
            }
        }
    }

    public function removeTaxFields() {
        $moduleInstance = Vtiger_Module_Model::getInstance('RecommissioningReports');
        $productTaxes = Inventory_TaxRecord_Model::getProductTaxes();
        foreach ($productTaxes as $taxInfo) {
            $fieldInstance = Vtiger_Field_Model::getInstance($taxInfo->get('taxname'), $moduleInstance);
            if ($fieldInstance) {
                $fieldInstance->delete();
            }
        }
    }

    public function enableProfileUtilities() {
        $db = PearDatabase::getInstance();
        $moduleModel = Vtiger_Module_Model::getInstance($this->moduleName);
        $allowed_tools = array('Export', 'PrintTemplates', 'Reopen', 'DuplicatesHandling', 'Create List');
        $actionMappingRes = $db->pquery('SELECT actionid, actionname FROM vtiger_actionmapping WHERE actionname '
                . 'IN (' . generateQuestionMarks($allowed_tools) . ')', $allowed_tools);

        $actionMappingInfo = array();
        while ($row = $db->fetchByAssoc($actionMappingRes)) {
            $actionMappingInfo[$row['actionname']] = $row['actionid'];
        }

        foreach ($allowed_tools as $tool) {
            $result = $db->pquery('SELECT 1 FROM vtiger_profile2utility WHERE tabid = ? AND activityid = ?', array($moduleModel->getId(), $actionMappingInfo[$tool]));
            $rows = $db->num_rows($result);
            if (!$rows) {
                Vtiger_Access::updateTool($moduleModel, $tool, true);
            }
        }
    }

    function addLinks() {
        
    }

    function deleteLinks() {
        
    }

    public function registerHandlers() {
        
    }

    function unregisterEventHandlers() {
        
    }

    public function enableModtracker() {
        include_once 'modules/ModTracker/ModTracker.php';
        $tabId = getTabid($this->moduleName);
        ModTracker::enableTrackingForModule($tabId);
    }

    public function disableModtracker() {
        include_once 'modules/ModTracker/ModTracker.php';
        $tabId = getTabid($this->moduleName);
        ModTracker::disableTrackingForModule($tabId);
    }

    public function applyRelateModuleChanges() {
        $db = PearDatabase::getInstance();

        $moduleInstance = Vtiger_Module::getInstance($this->moduleName);
        $modtrackerModule = Vtiger_Module::getInstance('ModTracker');
        if (!Vtiger_Relation_Model::isRelationEntryExist($moduleInstance->name, $modtrackerModule->name, 'N:N')) {
            $moduleInstance->setRelatedList($modtrackerModule, 'LBL_UPDATES');
        }

        $modCommentsModule = Vtiger_Module::getInstance('ModComments');
        if (!Vtiger_Relation_Model::isRelationEntryExist($moduleInstance->name, $modCommentsModule->name, '1:N')) {
            $modCommentsModModel = Vtiger_Module_Model::getInstance('ModComments');
            $related_to = $modCommentsModModel->getField('related_to');
            $related_to->setRelatedModules(array($this->moduleName));
            $moduleInstance->setRelatedList($modCommentsModule, 'ModComments', false, 'get_comments', $related_to->getId());
        }

        $moduleModel = Vtiger_Module_Model::getInstance('RecommissioningReports');
        $orgModule = Vtiger_Module::getInstance('Accounts');
        if (!Vtiger_Relation_Model::isRelationEntryExist($orgModule->name, $moduleInstance->name, '1:N')) {
            $account_id = $moduleModel->getField('account_id');
            $account_id->setRelatedModules(array('Accounts'));
            $orgModule->setRelatedList($moduleInstance, 'RecommissioningReports', false, 'get_dependents_list', $account_id->getId());
        }

        $contModule = Vtiger_Module::getInstance('Contacts');
        if (!Vtiger_Relation_Model::isRelationEntryExist($contModule->name, $moduleInstance->name, '1:N')) {
            $contact_id = $moduleModel->getField('contact_id');
            $contact_id->setRelatedModules(array('Contacts'));
            $contModule->setRelatedList($moduleInstance, 'RecommissioningReports', false, 'get_dependents_list', $contact_id->getId());
        }

    }

    public function revertRelateModuleChanges() {
        $moduleInstance = Vtiger_Module::getInstance($this->moduleName);
        $modtrackerModule = Vtiger_Module::getInstance('ModTracker');
        if (Vtiger_Relation_Model::isRelationEntryExist($moduleInstance->name, $modtrackerModule->name, 'N:N')) {
            $moduleInstance->unsetRelatedList($modtrackerModule, 'LBL_UPDATES', 'get_related_list');
        }

        $modCommentsModule = Vtiger_Module::getInstance('ModComments');
        if (Vtiger_Relation_Model::isRelationEntryExist($moduleInstance->name, $modCommentsModule->name, '1:N')) {
            $modCommentsModModel = Vtiger_Module_Model::getInstance('ModComments');
            $related_to = $modCommentsModModel->getField('related_to');
            $related_to->unsetRelatedModules(array($this->moduleName));
            $moduleInstance->unsetRelatedList($modCommentsModule, 'ModComments', 'get_comments');
        }

        $moduleModel = Vtiger_Module_Model::getInstance('RecommissioningReports');
        $orgModule = Vtiger_Module::getInstance('Accounts');
        if (Vtiger_Relation_Model::isRelationEntryExist($orgModule->name, $moduleInstance->name, '1:N')) {
            $account_id = $moduleModel->getField('account_id');
            $account_id->unsetRelatedModules(array('Accounts'));
            $orgModule->unsetRelatedList($moduleInstance, 'RecommissioningReports', 'get_dependents_list');
        }

        $contModule = Vtiger_Module::getInstance('Contacts');
        if (Vtiger_Relation_Model::isRelationEntryExist($contModule->name, $moduleInstance->name, '1:N')) {
            $contact_id = $moduleModel->getField('contact_id');
            $contact_id->unsetRelatedModules(array('Contacts'));
            $contModule->unsetRelatedList($moduleInstance, 'RecommissioningReports', 'get_dependents_list');
        }

        $invoiceModule = Vtiger_Module_Model::getInstance('Invoice');
        if (Vtiger_Relation_Model::isRelationEntryExist($moduleInstance->name, $invoiceModule->getName(), 'N:N')) {
            $moduleInstance->unsetRelatedList($invoiceModule, 'Invoice', 'get_related_list');
        }
        if (Vtiger_Relation_Model::isRelationEntryExist($invoiceModule->getName(), $moduleInstance->name, 'N:N')) {
            $invoiceModule->unsetRelatedList($moduleInstance, 'RecommissioningReports', 'get_related_list');
        }

        $paymentsModule = Vtiger_Module_Model::getInstance('Payments');
        if ($paymentsModule) {
            $related_to = $paymentsModule->getField('related_to');
            $referenceList = $related_to->getReferenceList();
            if (in_array('RecommissioningReports', $referenceList)) {
                $related_to->unsetRelatedModules(array('Payments' => 'RecommissioningReports'));
                $moduleModel->unsetRelatedListForField($related_to->getId());
            }
        }
    }

    public function applyPostInstallSchemaChanges() {
    }

    public function addRelatedFields() {
      
    }

    function save_module() {
        global $adb;


        if (isset($_REQUEST['REQUEST_FROM_WS']) && $_REQUEST['REQUEST_FROM_WS']) {
            unset($_REQUEST['totalProductCount']);
        }

        if (isset($this->_recurring_mode) && $this->_recurring_mode == 'creating_rr_from_service_report' && isset($this->_servicereportid) && $this->_servicereportid != '') {
            $this->createRecurringRecommisioningFromServiceReport();
            global $log;
            if ($this->mode != 'edit') {
                $allImages = $this->getImageDetailsForCopy($this->_servicereportid);
                $log->debug("The Files Recived =======" . json_encode($allImages) . "========= method.");
                foreach ($allImages as $attachmentdetails) {
                    $this->uploadAndSaveFileCustom($this->id, 'RecommissioningReports', $attachmentdetails, 'Attachment', $attachmentdetails['fieldNameFromDB']);
                }
            }
            $this->secondlineCopingFromServiceReports();
            $this->thirdlineCopingFromServiceReports();
        } else if (isset($_REQUEST)) {
            if ($_REQUEST['action'] != 'RecommissioningReportsAjax' && $_REQUEST['ajxaction'] != 'DETAILVIEW' && $_REQUEST['action'] != 'MassEditSave' && $_REQUEST['action'] != 'ProcessDuplicates' && $_REQUEST['action'] != 'SaveAjax' && $this->isLineItemUpdate != false && $_REQUEST['action'] != 'FROM_WS') {
                saveInventoryProductDetails($this, 'RecommissioningReports');
                saveInventoryProductDetails1($this, 'RecommissioningReports');
                saveInventoryProductDetails2($this, 'RecommissioningReports');
            }
        }

        $update_query = "update vtiger_recommissioningreports set currency_id=?, conversion_rate=? where recommissioningreportsid=?";
        $update_params = array($this->column_fields['currency_id'], $this->column_fields['conversion_rate'], $this->id);
        $adb->pquery($update_query, $update_params);
    }

    function uploadAndSaveFileCustom($id, $module, $file_details, $attachmentType = 'Attachment', $fieldNameOfAttach = '') {
        global $log;
        $log->debug("Entering into uploadAndSaveFile($id,$module,$file_details) method.");

        global $adb, $current_user;
        global $upload_badext;

        $date_var = date("Y-m-d H:i:s");

        if (!isset($ownerid) || $ownerid == '')
            $ownerid = $current_user->id;

        if (isset($file_details['original_name']) && $file_details['original_name'] != null) {
            $file_name = $file_details['original_name'];
        } else {
            $file_name = $file_details['name'];
        }

        $save_file = 'true';
        $mimeType = vtlib_mime_content_type($file_details['tmp_name']);
        $mimeTypeContents = explode('/', $mimeType);
        if (($module == 'Contacts' || $module == 'Products') && ($attachmentType == 'Image' || ($file_details['size'] && $mimeTypeContents[0] == 'image'))) {
            $save_file = validateImageFile($file_details);
        }
        $log->debug("File Validation status in Check1 save_file => $save_file");
        if ($save_file == 'false') {
            return false;
        }

        $save_file = 'true';
        if ($module == 'Contacts' || $module == 'Products') {
            $save_file = validateImageFile($file_details);
        }
        $log->debug("File Validation status in Check2 save_file => $save_file");
        $binFile = sanitizeUploadFileName($file_name, $upload_badext);
        $current_id = $adb->getUniqueID("vtiger_crmentity");
        $filename = ltrim(basename(" " . $binFile));
        $filetype = $file_details['type'];
        $upload_file_path = decideFilePath();
        $encryptFileName = Vtiger_Util_Helper::getEncryptedFileName($file_details['name']);
        $upload_status = copy($file_details['location'], $upload_file_path . $current_id . "_" . $encryptFileName);
        $log->debug("Upload status of file => $upload_status");
        if ($save_file == 'true' && $upload_status == 'true') {
            if ($attachmentType != 'Image' && $this->mode == 'edit' && ($module != 'HelpDesk' & $module != 'ServiceReports')) {
                $res = $adb->pquery('SELECT vtiger_seattachmentsrel.attachmentsid FROM vtiger_seattachmentsrel 
                        INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_seattachmentsrel.attachmentsid AND vtiger_crmentity.setype = ? 
                        WHERE vtiger_seattachmentsrel.crmid = ?', array($module . ' Attachment', $id));
                $oldAttachmentIds = array();
                for ($attachItr = 0; $attachItr < $adb->num_rows($res); $attachItr++) {
                    $oldAttachmentIds[] = $adb->query_result($res, $attachItr, 'attachmentsid');
                }
                if (count($oldAttachmentIds)) {
                    $adb->pquery('DELETE FROM vtiger_seattachmentsrel WHERE attachmentsid IN (' . generateQuestionMarks($oldAttachmentIds) . ')', $oldAttachmentIds);
                }
            }
            $sql1 = "INSERT INTO vtiger_crmentity (crmid,smcreatorid,smownerid,setype,description,createdtime,modifiedtime) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $params1 = array($current_id, $current_user->id, $ownerid, $module . " " . $attachmentType, $this->column_fields['description'], $adb->formatDate($date_var, true), $adb->formatDate($date_var, true));
            $adb->pquery($sql1, $params1);
            $sql2 = "INSERT INTO vtiger_attachments(attachmentsid, name, description, type, path, storedname,subject) values(?, ?, ?, ?, ?, ?,?)";
            $params2 = array($current_id, $filename, NULL, $filetype, $upload_file_path, $encryptFileName, $fieldNameOfAttach);
            $adb->pquery($sql2, $params2);
            $sql3 = 'INSERT INTO vtiger_seattachmentsrel VALUES(?,?)';
            $params3 = array($id, $current_id);
            $adb->pquery($sql3, $params3);
            $log->debug("File uploaded successfully with id => $current_id");
            return $current_id;
        } else {
            $log->debug('File upload failed');
            return false;
        }
    }

    public function getImageDetailsForCopy($recordId) {
        global $site_URL;
        $db = PearDatabase::getInstance();
        $imageDetails = array();
        if ($recordId) {
            $sql = "SELECT vtiger_attachments.*, vtiger_crmentity.setype FROM vtiger_attachments
                INNER JOIN vtiger_seattachmentsrel ON vtiger_seattachmentsrel.attachmentsid = vtiger_attachments.attachmentsid
                INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_attachments.attachmentsid
                WHERE vtiger_crmentity.setype In ('ServiceReports Attachment','HelpDesk Attachment' , 'ServiceReports Image')  AND vtiger_seattachmentsrel.crmid = ?";
            $result = $db->pquery($sql, array($recordId));
            $count = $db->num_rows($result);
            for ($i = 0; $i < $count; $i++) {
                $imageId = $db->query_result($result, $i, 'attachmentsid');
                $imageIdsList[] = $db->query_result($result, $i, 'attachmentsid');
                $imagePathList[] = $db->query_result($result, $i, 'path');
                $storedname[] = $db->query_result($result, $i, 'storedname');
                $imageName = $db->query_result($result, $i, 'name');
                $fieldName[] = $db->query_result($result, $i, 'subject');
                $url = \Vtiger_Functions::getFilePublicURL($imageId, $imageName);
                $imageOriginalNamesList[] = urlencode(decode_html($imageName));
                $imageNamesList[] = $imageName;
                $imageUrlsList[] = $url;
                $descriptionOffield[] = $db->query_result($result, $i, 'description');
                $typeList[] = $db->query_result($result, $i, 'type');
            }
            if (is_array($imageOriginalNamesList)) {
                $countOfImages = count($imageOriginalNamesList);
                for ($j = 0; $j < $countOfImages; $j++) {
                    $imageDetails[] = array(
                        'id' => $imageIdsList[$j],
                        'orgname' => $imageOriginalNamesList[$j],
                        'path' => $imagePathList[$j] . $imageIdsList[$j],
                        'location' => $imagePathList[$j] . $imageIdsList[$j] . '_' . $storedname[$j],
                        'name' => $imageNamesList[$j],
                        'url' => $imageUrlsList[$j],
                        'field' => $imageUrlsList[$j],
                        'fieldNameFromDB' => $fieldName[$j],
                        'descriptionOffield' => $descriptionOffield[$j],
                        'type' => $typeList[$j]
                    );
                }
            }
        }
        return $imageDetails;
    }

    function createRecurringRecommisioningFromServiceReport(){
        global $adb;
        $salesorder_id = $this->_servicereportid;
        $query1 = "SELECT * FROM vtiger_inventoryproductrel WHERE id=?";
        $res = $adb->pquery($query1, array($salesorder_id));
        $no_of_products = $adb->num_rows($res);
        $fieldsList = $adb->getFieldsArray($res);
        for ($j = 0; $j < $no_of_products; $j++) {
            $row = $adb->query_result_rowdata($res, $j);
            $col_value = array();
            for ($k = 0; $k < count($fieldsList); $k++) {
                if ($fieldsList[$k] != 'lineitem_id') {
                    $col_value[$fieldsList[$k]] = $row[$fieldsList[$k]];
                }
            }
            if (count($col_value) > 0) {
                $col_value['id'] = $this->id;
                $columns = array_keys($col_value);
                $values = array_values($col_value);
                $query2 = "INSERT INTO vtiger_inventoryproductrel(" . implode(",", $columns) . ") VALUES (" . generateQuestionMarks($values) . ")";
                $adb->pquery($query2, array($values));
            }
        }

        $query1 = "SELECT * FROM vtiger_inventorysubproductrel WHERE id=?";
        $res = $adb->pquery($query1, array($salesorder_id));
        $no_of_products = $adb->num_rows($res);
        $fieldsList = $adb->getFieldsArray($res);
        for ($j = 0; $j < $no_of_products; $j++) {
            $row = $adb->query_result_rowdata($res, $j);
            $col_value = array();
            for ($k = 0; $k < count($fieldsList); $k++) {
                $col_value[$fieldsList[$k]] = $row[$fieldsList[$k]];
            }
            if (count($col_value) > 0) {
                $col_value['id'] = $this->id;
                $columns = array_keys($col_value);
                $values = array_values($col_value);
                $query2 = "INSERT INTO vtiger_inventorysubproductrel(" . implode(",", $columns) . ") VALUES (" . generateQuestionMarks($values) . ")";
                $adb->pquery($query2, array($values));
            }
        }

        $adb->pquery('DELETE FROM vtiger_inventorychargesrel WHERE recordid = ?', array($this->id));
        $adb->pquery('INSERT INTO vtiger_inventorychargesrel SELECT ?, charges FROM vtiger_inventorychargesrel WHERE recordid = ?', array($this->id, $salesorder_id));

        $updatequery = " UPDATE vtiger_recommissioningreports SET ";
        $updateparams = array();
        $invoice_column_field = Array(
            'adjustment' => 'txtAdjustment',
            'subtotal' => 'hdnSubTotal',
            'total' => 'hdnGrandTotal',
            'taxtype' => 'hdnTaxType',
            'discount_percent' => 'hdnDiscountPercent',
            'discount_amount' => 'hdnDiscountAmount',
            's_h_amount' => 'hdnS_H_Amount',
            'region_id' => 'region_id',
            's_h_percent' => 'hdnS_H_Percent'
        );
        $updatecols = array();
        foreach ($invoice_column_field as $col => $field) {
            $updatecols[] = "$col=?";
            $updateparams[] = $this->column_fields[$field];
        }
        if (count($updatecols) > 0) {
            $updatequery .= implode(",", $updatecols);

            $updatequery .= " WHERE recommissioningreportsid=?";
            array_push($updateparams, $this->id);

            $adb->pquery($updatequery, $updateparams);
        }
    }

    function secondlineCopingFromServiceReports() {
        global $adb;
        $salesorder_id = $this->_servicereportid;
        $query1 = "SELECT * FROM vtiger_inventoryproductrel_other WHERE id=?";
        $res = $adb->pquery($query1, array($salesorder_id));
        $no_of_products = $adb->num_rows($res);
        $fieldsList = $adb->getFieldsArray($res);
        for ($j = 0; $j < $no_of_products; $j++) {
            $row = $adb->query_result_rowdata($res, $j);
            $col_value = array();
            for ($k = 0; $k < count($fieldsList); $k++) {
                if ($fieldsList[$k] != 'lineitem_id') {
                    $col_value[$fieldsList[$k]] = $row[$fieldsList[$k]];
                }
            }
            if (count($col_value) > 0) {
                $col_value['id'] = $this->id;
                $columns = array_keys($col_value);
                $values = array_values($col_value);
                $query2 = "INSERT INTO vtiger_inventoryproductrel_other(" . implode(",", $columns) . ") VALUES (" . generateQuestionMarks($values) . ")";
                $adb->pquery($query2, array($values));
            }
        }
    }

    function thirdlineCopingFromServiceReports() {
        global $adb;
        $salesorder_id = $this->_servicereportid;
        $query1 = "SELECT * FROM vtiger_inventoryproductrel_other_masn WHERE id=?";
        $res = $adb->pquery($query1, array($salesorder_id));
        $no_of_products = $adb->num_rows($res);
        $fieldsList = $adb->getFieldsArray($res);
        for ($j = 0; $j < $no_of_products; $j++) {
            $row = $adb->query_result_rowdata($res, $j);
            $col_value = array();
            for ($k = 0; $k < count($fieldsList); $k++) {
                if ($fieldsList[$k] != 'lineitem_id') {
                    $col_value[$fieldsList[$k]] = $row[$fieldsList[$k]];
                }
            }
            if (count($col_value) > 0) {
                $col_value['id'] = $this->id;
                $columns = array_keys($col_value);
                $values = array_values($col_value);
                $query2 = "INSERT INTO vtiger_inventoryproductrel_other_masn(" . implode(",", $columns) . ") VALUES (" . generateQuestionMarks($values) . ")";
                $adb->pquery($query2, array($values));
            }
        }
    }

    function insertIntoEntityTable($table_name, $module, $fileid = '') {
        if ($table_name == 'vtiger_inventoryproductrel' || $table_name == 'vtiger_inventoryproductrel_other' || $table_name == 'vtiger_inventoryproductrel_other_masn') {
            return;
        }
        parent::insertIntoEntityTable($table_name, $module, $fileid);
    }

    function createRecords($obj) {
        $createRecords = createRecords($obj);
        return $createRecords;
    }

    function importRecord($obj, $inventoryFieldData, $lineItemDetails) {
        $entityInfo = importRecord($obj, $inventoryFieldData, $lineItemDetails);
        return $entityInfo;
    }

    function getImportStatusCount($obj) {
        $statusCount = getImportStatusCount($obj);
        return $statusCount;
    }

    function undoLastImport($obj, $user) {
        $undoLastImport = undoLastImport($obj, $user);
    }

    function getMandatoryImportableFields() {
        return getInventoryImportableMandatoryFeilds($this->moduleName);
    }

    function create_export_query($where) {
        global $log;
        global $current_user;
        $log->debug("Entering create_export_query(" . $where . ") method ...");

        include("include/utils/ExportUtils.php");

        $sql = getPermittedFieldsQuery("Quotes", "detail_view");
        $fields_list = getFieldsListFromQuery($sql);
        $fields_list .= getInventoryFieldsForExport($this->table_name);
        $query = "SELECT $fields_list FROM " . $this->entity_table . "
				INNER JOIN vtiger_recommissioningreports ON vtiger_recommissioningreports.recommissioningreportsid = vtiger_crmentity.crmid
				LEFT JOIN vtiger_recommissioningreportscf ON vtiger_recommissioningreportscf.recommissioningreportsid = vtiger_recommissioningreports.recommissioningreportsid
				LEFT JOIN vtiger_inventoryproductrel ON vtiger_inventoryproductrel.id = vtiger_quotes.quoteid
				LEFT JOIN vtiger_products ON vtiger_products.productid = vtiger_inventoryproductrel.productid
				LEFT JOIN vtiger_service ON vtiger_service.serviceid = vtiger_inventoryproductrel.productid
				LEFT JOIN vtiger_contactdetails ON vtiger_contactdetails.contactid = vtiger_recommissioningreports.contactid
				LEFT JOIN vtiger_account ON vtiger_account.accountid = vtiger_recommissioningreports.accountid
				LEFT JOIN vtiger_currency_info ON vtiger_currency_info.id = vtiger_recommissioningreports.currency_id
				LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid
				LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid";

        $query .= $this->getNonAdminAccessControlQuery('Quotes', $current_user);
        $where_auto = " vtiger_crmentity.deleted=0";

        if ($where != "") {
            $query .= " where ($where) AND " . $where_auto;
        } else {
            $query .= " where " . $where_auto;
        }

        $log->debug("Exiting create_export_query method ...");
        return $query;
    }

    function checkACPermission($linkData) {
        return false;
    }

    function generateReportsQuery($module, $queryplanner) {
        global $current_user;
        $matrix = $queryplanner->newDependencyMatrix();

        $matrix->setDependency('vtiger_inventoryproductreltmpRecommissioningReports', array('vtiger_productsRecommissioningReports', 'vtiger_serviceRecommissioningReports'));

        $query = "from vtiger_recommissioningreports
        inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_recommissioningreports.recommissioningreportsid";

        if ($queryplanner->requireTable("vtiger_currency_info$module")) {
            $query .= " left join vtiger_currency_info as vtiger_currency_info$module on vtiger_currency_info$module.id = vtiger_recommissioningreports.currency_id";
        }
        if ($type !== 'COLUMNSTOTOTAL' || $this->lineItemFieldsInCalculation == true) {
            if ($queryplanner->requireTable("vtiger_inventoryproductreltmpRecommissioningReports", $matrix) || $queryplanner->requireTable("vtiger_inventoryproductrelRecommissioningReports", $matrix)) {
                $query .= " left join vtiger_inventoryproductrel as vtiger_inventoryproductreltmpRecommissioningReports on vtiger_recommissioningreports.recommissioningreportsid = vtiger_inventoryproductreltmpRecommissioningReports.id";
            }
            if ($queryplanner->requireTable("vtiger_productsRecommissioningReports")) {
                $query .= " left join vtiger_products as vtiger_productsRecommissioningReports on vtiger_productsRecommissioningReports.productid = vtiger_inventoryproductreltmpRecommissioningReports.productid";
            }
            if ($queryplanner->requireTable("vtiger_serviceRecommissioningReports")) {
                $query .= " left join vtiger_service as vtiger_serviceRecommissioningReports on vtiger_serviceRecommissioningReports.serviceid = vtiger_inventoryproductreltmpRecommissioningReports.productid";
            }
        }
        if ($queryplanner->requireTable("vtiger_recommissioningreportscf")) {
            $query .= " left join vtiger_recommissioningreportscf on vtiger_recommissioningreports.recommissioningreportsid = vtiger_recommissioningreportscf.recommissioningreportsid";
        }
        if ($queryplanner->requireTable("vtiger_groupsRecommissioningReports")) {
            $query .= " left join vtiger_groups as vtiger_groupsRecommissioningReports on vtiger_groupsRecommissioningReports.groupid = vtiger_crmentity.smownerid";
        }
        if ($queryplanner->requireTable("vtiger_usersRecommissioningReports")) {
            $query .= " left join vtiger_users as vtiger_usersRecommissioningReports on vtiger_usersRecommissioningReports.id = vtiger_crmentity.smownerid";
        }

        $query .= " left join vtiger_groups on vtiger_groups.groupid = vtiger_crmentity.smownerid";
        $query .= " left join vtiger_users on vtiger_users.id = vtiger_crmentity.smownerid";

        if ($queryplanner->requireTable("vtiger_lastModifiedByRecommissioningReports")) {
            $query .= " left join vtiger_users as vtiger_lastModifiedByRecommissioningReports on vtiger_lastModifiedByRecommissioningReports.id = vtiger_crmentity.modifiedby";
        }
        if ($queryplanner->requireTable('vtiger_createdbyRecommissioningReports')) {
            $query .= " left join vtiger_users as vtiger_createdbyRecommissioningReports on vtiger_createdbyRecommissioningReports.id = vtiger_crmentity.smcreatorid";
        }
        if ($queryplanner->requireTable("vtiger_accountRecommissioningReports")) {
            $query .= " left join vtiger_account as vtiger_accountRecommissioningReports on vtiger_accountRecommissioningReports.accountid = vtiger_recommissioningreports.account_id";
        }
        if ($queryplanner->requireTable("vtiger_contactdetailsRecommissioningReports")) {
            $query .= " left join vtiger_contactdetails as vtiger_contactdetailsRecommissioningReports on vtiger_contactdetailsRecommissioningReports.contactid = vtiger_recommissioningreports.contact_id";
        }
        $focus = CRMEntity::getInstance($module);
        $relQuery = $focus->getReportsUiType10Query($module, $queryplanner);
        $query .= ' ' . $relQuery;
        return $query;
    }

    function generateReportsSecQuery($module, $secmodule, $queryPlanner) {
        $matrix = $queryPlanner->newDependencyMatrix();
        $matrix->setDependency('vtiger_crmentityRecommissioningReports', array('vtiger_usersRecommissioningReports', 'vtiger_groupsRecommissioningReports', 'vtiger_lastModifiedByRecommissioningReports'));
        $matrix->setDependency('vtiger_inventoryproductrelRecommissioningReports', array('vtiger_productsRecommissioningReports', 'vtiger_serviceRecommissioningReports'));


        if (!$queryPlanner->requireTable('vtiger_recommissioningreports', $matrix) && !$queryPlanner->requireTable('vtiger_crmentityRecommissioningReports', $matrix)) {
            return '';
        }
        $matrix->setDependency('vtiger_recommissioningreports', array('vtiger_crmentityRecommissioningReports', "vtiger_currency_info$secmodule",
            'vtiger_recommissioningreportscf', 'vtiger_inventoryproductrelRecommissioningReports', 'vtiger_contactdetailsRecommissioningReports', 'vtiger_accountRecommissioningReports',
            'vtiger_usersRel1'));

        $query = $this->getRelationQuery($module, $secmodule, "vtiger_recommissioningreports", "recommissioningreportsid", $queryPlanner);
        if ($queryPlanner->requireTable("vtiger_crmentityRecommissioningReports", $matrix)) {
            $query .= " left join vtiger_crmentity as vtiger_crmentityRecommissioningReports on vtiger_crmentityRecommissioningReports.crmid=vtiger_recommissioningreports.recommissioningreportsid and vtiger_crmentityRecommissioningReports.deleted=0";
        }
        if ($queryPlanner->requireTable("vtiger_recommissioningreportscf")) {
            $query .= " left join vtiger_recommissioningreportscf on vtiger_recommissioningreports.recommissioningreportsid = vtiger_recommissioningreportscf.recommissioningreportsid";
        }
        if ($queryPlanner->requireTable("vtiger_currency_info$secmodule")) {
            $query .= " left join vtiger_currency_info as vtiger_currency_info$secmodule on vtiger_currency_info$secmodule.id = vtiger_recommissioningreports.currency_id";
        }
        if ($queryPlanner->requireTable("vtiger_inventoryproductrelRecommissioningReports", $matrix)) {
            if ($module !== "Products" && $module !== "Services") {
                $query .= " LEFT JOIN vtiger_inventoryproductrel AS vtiger_inventoryproductreltmpRecommissioningReports ON vtiger_recommissioningreports.recommissioningreportsid = vtiger_inventoryproductreltmpRecommissioningReports.id";
            }
        }

        if ($queryPlanner->requireTable("vtiger_productsRecommissioningReports")) {
            $query .= " left join vtiger_products as vtiger_productsRecommissioningReports on vtiger_productsRecommissioningReports.productid = vtiger_inventoryproductreltmpRecommissioningReports.productid";
        }
        if ($queryPlanner->requireTable("vtiger_serviceRecommissioningReports")) {
            $query .= " left join vtiger_service as vtiger_serviceRecommissioningReports on vtiger_serviceRecommissioningReports.serviceid = vtiger_inventoryproductreltmpRecommissioningReports.productid";
        }
        if ($queryPlanner->requireTable("vtiger_groupsRecommissioningReports")) {
            $query .= " left join vtiger_groups as vtiger_groupsRecommissioningReports on vtiger_groupsRecommissioningReports.groupid = vtiger_crmentityRecommissioningReports.smownerid";
        }
        if ($queryPlanner->requireTable("vtiger_usersRecommissioningReports")) {
            $query .= " left join vtiger_users as vtiger_usersRecommissioningReports on vtiger_usersRecommissioningReports.id = vtiger_crmentityRecommissioningReports.smownerid";
        }
        if ($queryPlanner->requireTable("vtiger_usersRel1")) {
            $query .= " left join vtiger_users as vtiger_usersRel1 on vtiger_usersRel1.id = vtiger_quotes.inventorymanager";
        }
        if ($queryPlanner->requireTable("vtiger_contactdetailsRecommissioningReports")) {
            $query .= " left join vtiger_contactdetails as vtiger_contactdetailsRecommissioningReports on vtiger_contactdetailsRecommissioningReports.contactid = vtiger_recommissioningreports.contact_id";
        }
        if ($queryPlanner->requireTable("vtiger_accountRecommissioningReports")) {
            $query .= " left join vtiger_account as vtiger_accountRecommissioningReports on vtiger_accountRecommissioningReports.accountid = vtiger_recommissioningreports.account_id";
        }
        if ($queryPlanner->requireTable("vtiger_lastModifiedByRecommissioningReports")) {
            $query .= " left join vtiger_users as vtiger_lastModifiedByRecommissioningReports on vtiger_lastModifiedByRecommissioningReports.id = vtiger_crmentityRecommissioningReports.modifiedby ";
        }
        if ($queryPlanner->requireTable("vtiger_createdbyRecommissioningReports")) {
            $query .= " left join vtiger_users as vtiger_createdbyRecommissioningReports on vtiger_createdbyRecommissioningReports.id = vtiger_crmentityRecommissioningReports.smcreatorid ";
        }

        $query .= parent::getReportsUiType10Query($secmodule, $queryPlanner);
        return $query;
    }

}
