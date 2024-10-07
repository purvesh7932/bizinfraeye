<?php

class FailedParts extends CRMEntity {

    var $table_name = 'vtiger_failedparts';
    var $table_index = 'failedpartid';
    var $moduleName = 'FailedParts';
    var $customFieldTable = Array('vtiger_failedpartscf', 'failedpartid');
    var $tab_name = Array('vtiger_crmentity', 'vtiger_failedparts', 'vtiger_failedpartscf', 'vtiger_inventoryproductrel');
    var $tab_name_index = Array(
        'vtiger_crmentity' => 'crmid',
        'vtiger_failedparts' => 'failedpartid',
        'vtiger_failedpartscf' => 'failedpartid',
        'vtiger_inventoryproductrel' => 'id',
        'vtiger_inventoryproductrel_fp' => 'id'
    );
    var $list_fields = Array(
        'Name' => Array('FailedParts', 'creditnote_name'),
        'Assigned To' => Array('crmentity', 'smownerid')
    );
    var $list_fields_name = Array(
        'Name' => 'creditnote_name',
        'Assigned To' => 'assigned_user_id',
    );
    var $list_link_field = 'creditnote_name';
    var $search_fields = Array(
        'Name' => Array('FailedParts', 'creditnote_name'),
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

    function FailedParts() {
        $this->db = PearDatabase::getInstance();
        $this->column_fields = getColumnFields('FailedParts');
    }

    public function vtlib_handler($moduleName, $eventType) {
        require_once('include/utils/utils.php');
        if ($eventType == 'module.postinstall') {
            $this->addLinks();
            $this->registerHandlers();
            $this->enableModtracker();
            $this->setModuleSeqNumber('configure', $this->moduleName, 'CN-', 1);
            vtws_addModuleTypeWebserviceEntity('FailedParts', 'include/Webservices/VtigerModuleOperation.php', 'VtigerModuleOperation');
            $this->applyRelateModuleChanges();
            $this->applyPostInstallSchemaChanges();
            $this->addRelatedFields();
            $this->enableProfileUtilities();
            $this->addTaxFields($eventType);
            $db = PearDatabase::getInstance();
            $tandC = $db->pquery('SELECT 1 FROM vtiger_inventory_tandc where type=?', array('FailedParts'));
            if (!($db->num_rows($tandC))) {
                $db->pquery('INSERT INTO vtiger_inventory_tandc(id, type) values(?,?)', array($db->getUniqueId("vtiger_inventory_tandc"), 'FailedParts'));
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
        $moduleInstance = Vtiger_Module_Model::getInstance('FailedParts');
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
        $moduleInstance = Vtiger_Module_Model::getInstance('FailedParts');
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

        $moduleModel = Vtiger_Module_Model::getInstance('FailedParts');
        $orgModule = Vtiger_Module::getInstance('Accounts');
        if (!Vtiger_Relation_Model::isRelationEntryExist($orgModule->name, $moduleInstance->name, '1:N')) {
            $account_id = $moduleModel->getField('account_id');
            $account_id->setRelatedModules(array('Accounts'));
            $orgModule->setRelatedList($moduleInstance, 'FailedParts', false, 'get_dependents_list', $account_id->getId());
        }

        $contModule = Vtiger_Module::getInstance('Contacts');
        if (!Vtiger_Relation_Model::isRelationEntryExist($contModule->name, $moduleInstance->name, '1:N')) {
            $contact_id = $moduleModel->getField('contact_id');
            $contact_id->setRelatedModules(array('Contacts'));
            $contModule->setRelatedList($moduleInstance, 'FailedParts', false, 'get_dependents_list', $contact_id->getId());
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

        $moduleModel = Vtiger_Module_Model::getInstance('FailedParts');
        $orgModule = Vtiger_Module::getInstance('Accounts');
        if (Vtiger_Relation_Model::isRelationEntryExist($orgModule->name, $moduleInstance->name, '1:N')) {
            $account_id = $moduleModel->getField('account_id');
            $account_id->unsetRelatedModules(array('Accounts'));
            $orgModule->unsetRelatedList($moduleInstance, 'FailedParts', 'get_dependents_list');
        }

        $contModule = Vtiger_Module::getInstance('Contacts');
        if (Vtiger_Relation_Model::isRelationEntryExist($contModule->name, $moduleInstance->name, '1:N')) {
            $contact_id = $moduleModel->getField('contact_id');
            $contact_id->unsetRelatedModules(array('Contacts'));
            $contModule->unsetRelatedList($moduleInstance, 'FailedParts', 'get_dependents_list');
        }

        $invoiceModule = Vtiger_Module_Model::getInstance('Invoice');
        if (Vtiger_Relation_Model::isRelationEntryExist($moduleInstance->name, $invoiceModule->getName(), 'N:N')) {
            $moduleInstance->unsetRelatedList($invoiceModule, 'Invoice', 'get_related_list');
        }
        if (Vtiger_Relation_Model::isRelationEntryExist($invoiceModule->getName(), $moduleInstance->name, 'N:N')) {
            $invoiceModule->unsetRelatedList($moduleInstance, 'FailedParts', 'get_related_list');
        }

        $paymentsModule = Vtiger_Module_Model::getInstance('Payments');
        if ($paymentsModule) {
            $related_to = $paymentsModule->getField('related_to');
            $referenceList = $related_to->getReferenceList();
            if (in_array('FailedParts', $referenceList)) {
                $related_to->unsetRelatedModules(array('Payments' => 'FailedParts'));
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

        if (isset($this->_recurring_mode) && $this->_recurring_mode == 'duplicating_from_service_report' && isset($this->_servicereportid) && $this->_servicereportid != '') {
            $this->createRecurringServiceOrderFromServiceReport();
        } else if (isset($_REQUEST)) {
            if ($_REQUEST['action'] != 'FailedPartsAjax' && $_REQUEST['ajxaction'] != 'DETAILVIEW' && $_REQUEST['action'] != 'MassEditSave' && $_REQUEST['action'] != 'ProcessDuplicates' && $_REQUEST['action'] != 'SaveAjax' && $this->isLineItemUpdate != false && $_REQUEST['action'] != 'FROM_WS') {
                if($this->mode == 'edit'){
                    vglobal('NODELETEOFLINEITEMS', true);
                }
                saveInventoryFailedProductDetails($this, 'FailedParts');
            }
        }

        $update_query = "update vtiger_failedparts set currency_id=?, conversion_rate=? where failedpartid=?";
        $update_params = array($this->column_fields['currency_id'], $this->column_fields['conversion_rate'], $this->id);
        $adb->pquery($update_query, $update_params);
    }
    function createRecurringServiceOrderFromServiceReport(){
        global $adb;
        $salesorder_id = $this->_servicereportid;
        $query1 = "SELECT * FROM vtiger_inventoryproductrel WHERE id=?";
        $res = $adb->pquery($query1, array($salesorder_id));
        $no_of_products = $adb->num_rows($res);
        $fieldsList = $adb->getFieldsArray($res);
        global $replacedDate;
        for ($j = 0; $j < $no_of_products; $j++) {
            $row = $adb->query_result_rowdata($res, $j);
            if($row['sr_action_one'] != 'Replaced' && $row['sr_replace_action'] != 'From BEML Stock'){
                continue;
            }
            $col_value = array();
            for ($k = 0; $k < count($fieldsList); $k++) {
                if ($fieldsList[$k] != 'lineitem_id') {
                    $col_value[$fieldsList[$k]] = $row[$fieldsList[$k]];
                }
            }
            $col_value['replaced_date'] = $replacedDate;
            $col_value['date_of_submiss'] = NULL;
            $col_value['pending_days'] = '0';
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

        $updatequery = " UPDATE vtiger_failedparts SET ";
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

            $updatequery .= " WHERE failedpartid=?";
            array_push($updateparams, $this->id);

            $adb->pquery($updatequery, $updateparams);
        }
    }

    function insertIntoEntityTable($table_name, $module, $fileid = '') {
        if ($table_name == 'vtiger_inventoryproductrel') {
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
				INNER JOIN vtiger_failedparts ON vtiger_failedparts.failedpartid = vtiger_crmentity.crmid
				LEFT JOIN vtiger_failedpartscf ON vtiger_failedpartscf.failedpartid = vtiger_failedparts.failedpartid
				LEFT JOIN vtiger_inventoryproductrel ON vtiger_inventoryproductrel.id = vtiger_quotes.quoteid
				LEFT JOIN vtiger_products ON vtiger_products.productid = vtiger_inventoryproductrel.productid
				LEFT JOIN vtiger_service ON vtiger_service.serviceid = vtiger_inventoryproductrel.productid
				LEFT JOIN vtiger_contactdetails ON vtiger_contactdetails.contactid = vtiger_failedparts.contactid
				LEFT JOIN vtiger_account ON vtiger_account.accountid = vtiger_failedparts.accountid
				LEFT JOIN vtiger_currency_info ON vtiger_currency_info.id = vtiger_failedparts.currency_id
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

        $matrix->setDependency('vtiger_inventoryproductreltmpStockTransferOrders', array('vtiger_productsStockTransferOrders', 'vtiger_serviceStockTransferOrders'));

        $query = "from vtiger_failedparts
        inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_failedparts.failedpartid";

        if ($queryplanner->requireTable("vtiger_currency_info$module")) {
            $query .= " left join vtiger_currency_info as vtiger_currency_info$module on vtiger_currency_info$module.id = vtiger_failedparts.currency_id";
        }
        if ($type !== 'COLUMNSTOTOTAL' || $this->lineItemFieldsInCalculation == true) {
            if ($queryplanner->requireTable("vtiger_inventoryproductreltmpStockTransferOrders", $matrix) || $queryplanner->requireTable("vtiger_inventoryproductrelStockTransferOrders", $matrix)) {
                $query .= " left join vtiger_inventoryproductrel as vtiger_inventoryproductreltmpStockTransferOrders on vtiger_failedparts.failedpartid = vtiger_inventoryproductreltmpStockTransferOrders.id";
            }
            if ($queryplanner->requireTable("vtiger_productsStockTransferOrders")) {
                $query .= " left join vtiger_products as vtiger_productsStockTransferOrders on vtiger_productsStockTransferOrders.productid = vtiger_inventoryproductreltmpStockTransferOrders.productid";
            }
            if ($queryplanner->requireTable("vtiger_serviceStockTransferOrders")) {
                $query .= " left join vtiger_service as vtiger_serviceStockTransferOrders on vtiger_serviceStockTransferOrders.serviceid = vtiger_inventoryproductreltmpStockTransferOrders.productid";
            }
        }
        if ($queryplanner->requireTable("vtiger_failedpartscf")) {
            $query .= " left join vtiger_failedpartscf on vtiger_failedparts.failedpartid = vtiger_failedpartscf.failedpartid";
        }
        if ($queryplanner->requireTable("vtiger_groupsStockTransferOrders")) {
            $query .= " left join vtiger_groups as vtiger_groupsStockTransferOrders on vtiger_groupsStockTransferOrders.groupid = vtiger_crmentity.smownerid";
        }
        if ($queryplanner->requireTable("vtiger_usersStockTransferOrders")) {
            $query .= " left join vtiger_users as vtiger_usersStockTransferOrders on vtiger_usersStockTransferOrders.id = vtiger_crmentity.smownerid";
        }

        $query .= " left join vtiger_groups on vtiger_groups.groupid = vtiger_crmentity.smownerid";
        $query .= " left join vtiger_users on vtiger_users.id = vtiger_crmentity.smownerid";

        if ($queryplanner->requireTable("vtiger_lastModifiedByStockTransferOrders")) {
            $query .= " left join vtiger_users as vtiger_lastModifiedByStockTransferOrders on vtiger_lastModifiedByStockTransferOrders.id = vtiger_crmentity.modifiedby";
        }
        if ($queryplanner->requireTable('vtiger_createdbyStockTransferOrders')) {
            $query .= " left join vtiger_users as vtiger_createdbyStockTransferOrders on vtiger_createdbyStockTransferOrders.id = vtiger_crmentity.smcreatorid";
        }
        if ($queryplanner->requireTable("vtiger_accountStockTransferOrders")) {
            $query .= " left join vtiger_account as vtiger_accountStockTransferOrders on vtiger_accountStockTransferOrders.accountid = vtiger_failedparts.account_id";
        }
        if ($queryplanner->requireTable("vtiger_contactdetailsStockTransferOrders")) {
            $query .= " left join vtiger_contactdetails as vtiger_contactdetailsStockTransferOrders on vtiger_contactdetailsStockTransferOrders.contactid = vtiger_failedparts.contact_id";
        }
        $focus = CRMEntity::getInstance($module);
        $relQuery = $focus->getReportsUiType10Query($module, $queryplanner);
        $query .= ' ' . $relQuery;
        return $query;
    }

    function generateReportsSecQuery($module, $secmodule, $queryPlanner) {
        $matrix = $queryPlanner->newDependencyMatrix();
        $matrix->setDependency('vtiger_crmentityStockTransferOrders', array('vtiger_usersStockTransferOrders', 'vtiger_groupsStockTransferOrders', 'vtiger_lastModifiedByStockTransferOrders'));
        $matrix->setDependency('vtiger_inventoryproductrelStockTransferOrders', array('vtiger_productsStockTransferOrders', 'vtiger_serviceStockTransferOrders'));


        if (!$queryPlanner->requireTable('vtiger_failedparts', $matrix) && !$queryPlanner->requireTable('vtiger_crmentityStockTransferOrders', $matrix)) {
            return '';
        }
        $matrix->setDependency('vtiger_failedparts', array('vtiger_crmentityStockTransferOrders', "vtiger_currency_info$secmodule",
            'vtiger_failedpartscf', 'vtiger_inventoryproductrelStockTransferOrders', 'vtiger_contactdetailsStockTransferOrders', 'vtiger_accountStockTransferOrders',
            'vtiger_usersRel1'));

        $query = $this->getRelationQuery($module, $secmodule, "vtiger_failedparts", "failedpartid", $queryPlanner);
        if ($queryPlanner->requireTable("vtiger_crmentityStockTransferOrders", $matrix)) {
            $query .= " left join vtiger_crmentity as vtiger_crmentityStockTransferOrders on vtiger_crmentityStockTransferOrders.crmid=vtiger_failedparts.failedpartid and vtiger_crmentityStockTransferOrders.deleted=0";
        }
        if ($queryPlanner->requireTable("vtiger_failedpartscf")) {
            $query .= " left join vtiger_failedpartscf on vtiger_failedparts.failedpartid = vtiger_failedpartscf.failedpartid";
        }
        if ($queryPlanner->requireTable("vtiger_currency_info$secmodule")) {
            $query .= " left join vtiger_currency_info as vtiger_currency_info$secmodule on vtiger_currency_info$secmodule.id = vtiger_failedparts.currency_id";
        }
        if ($queryPlanner->requireTable("vtiger_inventoryproductrelStockTransferOrders", $matrix)) {
            if ($module !== "Products" && $module !== "Services") {
                $query .= " LEFT JOIN vtiger_inventoryproductrel AS vtiger_inventoryproductreltmpStockTransferOrders ON vtiger_failedparts.failedpartid = vtiger_inventoryproductreltmpStockTransferOrders.id";
            }
        }

        if ($queryPlanner->requireTable("vtiger_productsStockTransferOrders")) {
            $query .= " left join vtiger_products as vtiger_productsStockTransferOrders on vtiger_productsStockTransferOrders.productid = vtiger_inventoryproductreltmpStockTransferOrders.productid";
        }
        if ($queryPlanner->requireTable("vtiger_serviceStockTransferOrders")) {
            $query .= " left join vtiger_service as vtiger_serviceStockTransferOrders on vtiger_serviceStockTransferOrders.serviceid = vtiger_inventoryproductreltmpStockTransferOrders.productid";
        }
        if ($queryPlanner->requireTable("vtiger_groupsStockTransferOrders")) {
            $query .= " left join vtiger_groups as vtiger_groupsStockTransferOrders on vtiger_groupsStockTransferOrders.groupid = vtiger_crmentityStockTransferOrders.smownerid";
        }
        if ($queryPlanner->requireTable("vtiger_usersStockTransferOrders")) {
            $query .= " left join vtiger_users as vtiger_usersStockTransferOrders on vtiger_usersStockTransferOrders.id = vtiger_crmentityStockTransferOrders.smownerid";
        }
        if ($queryPlanner->requireTable("vtiger_usersRel1")) {
            $query .= " left join vtiger_users as vtiger_usersRel1 on vtiger_usersRel1.id = vtiger_quotes.inventorymanager";
        }
        if ($queryPlanner->requireTable("vtiger_contactdetailsStockTransferOrders")) {
            $query .= " left join vtiger_contactdetails as vtiger_contactdetailsStockTransferOrders on vtiger_contactdetailsStockTransferOrders.contactid = vtiger_failedparts.contact_id";
        }
        if ($queryPlanner->requireTable("vtiger_accountStockTransferOrders")) {
            $query .= " left join vtiger_account as vtiger_accountStockTransferOrders on vtiger_accountStockTransferOrders.accountid = vtiger_failedparts.account_id";
        }
        if ($queryPlanner->requireTable("vtiger_lastModifiedByStockTransferOrders")) {
            $query .= " left join vtiger_users as vtiger_lastModifiedByStockTransferOrders on vtiger_lastModifiedByStockTransferOrders.id = vtiger_crmentityStockTransferOrders.modifiedby ";
        }
        if ($queryPlanner->requireTable("vtiger_createdbyStockTransferOrders")) {
            $query .= " left join vtiger_users as vtiger_createdbyStockTransferOrders on vtiger_createdbyStockTransferOrders.id = vtiger_crmentityStockTransferOrders.smcreatorid ";
        }

        $query .= parent::getReportsUiType10Query($secmodule, $queryPlanner);
        return $query;
    }

}
