<?php

class ReturnSaleOrders extends CRMEntity {

    var $table_name = 'vtiger_returnsaleorders';
    var $table_index = 'returnsaleordersid';
    var $moduleName = 'ReturnSaleOrders';
    var $customFieldTable = Array('vtiger_returnsaleorderscf', 'returnsaleordersid');
    var $tab_name = Array('vtiger_crmentity', 'vtiger_returnsaleorders', 'vtiger_returnsaleorderscf', 'vtiger_inventoryproductrel');
    var $tab_name_index = Array(
        'vtiger_crmentity' => 'crmid',
        'vtiger_returnsaleorders' => 'returnsaleordersid',
        'vtiger_returnsaleorderscf' => 'returnsaleordersid',
        'vtiger_inventoryproductrel' => 'id',
        'vtiger_inventoryproductrel_fp' => 'id'
    );
    var $list_fields = Array(
        'Name' => Array('ReturnSaleOrders', 'creditnote_name'),
        'Assigned To' => Array('crmentity', 'smownerid')
    );
    var $list_fields_name = Array(
        'Name' => 'creditnote_name',
        'Assigned To' => 'assigned_user_id',
    );
    var $list_link_field = 'creditnote_name';
    var $search_fields = Array(
        'Name' => Array('ReturnSaleOrders', 'creditnote_name'),
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

    function ReturnSaleOrders() {
        $this->db = PearDatabase::getInstance();
        $this->column_fields = getColumnFields('ReturnSaleOrders');
    }

    public function vtlib_handler($moduleName, $eventType) {
        require_once('include/utils/utils.php');
        if ($eventType == 'module.postinstall') {
            $this->addLinks();
            $this->registerHandlers();
            $this->enableModtracker();
            $this->setModuleSeqNumber('configure', $this->moduleName, 'CN-', 1);
            vtws_addModuleTypeWebserviceEntity('ReturnSaleOrders', 'include/Webservices/VtigerModuleOperation.php', 'VtigerModuleOperation');
            $this->applyRelateModuleChanges();
            $this->applyPostInstallSchemaChanges();
            $this->addRelatedFields();
            $this->enableProfileUtilities();
            $this->addTaxFields($eventType);
            $db = PearDatabase::getInstance();
            $tandC = $db->pquery('SELECT 1 FROM vtiger_inventory_tandc where type=?', array('ReturnSaleOrders'));
            if (!($db->num_rows($tandC))) {
                $db->pquery('INSERT INTO vtiger_inventory_tandc(id, type) values(?,?)', array($db->getUniqueId("vtiger_inventory_tandc"), 'ReturnSaleOrders'));
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
        $moduleInstance = Vtiger_Module_Model::getInstance('ReturnSaleOrders');
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
        $moduleInstance = Vtiger_Module_Model::getInstance('ReturnSaleOrders');
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

        $moduleModel = Vtiger_Module_Model::getInstance('ReturnSaleOrders');
        $orgModule = Vtiger_Module::getInstance('Accounts');
        if (!Vtiger_Relation_Model::isRelationEntryExist($orgModule->name, $moduleInstance->name, '1:N')) {
            $account_id = $moduleModel->getField('account_id');
            $account_id->setRelatedModules(array('Accounts'));
            $orgModule->setRelatedList($moduleInstance, 'ReturnSaleOrders', false, 'get_dependents_list', $account_id->getId());
        }

        $contModule = Vtiger_Module::getInstance('Contacts');
        if (!Vtiger_Relation_Model::isRelationEntryExist($contModule->name, $moduleInstance->name, '1:N')) {
            $contact_id = $moduleModel->getField('contact_id');
            $contact_id->setRelatedModules(array('Contacts'));
            $contModule->setRelatedList($moduleInstance, 'ReturnSaleOrders', false, 'get_dependents_list', $contact_id->getId());
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

        $moduleModel = Vtiger_Module_Model::getInstance('ReturnSaleOrders');
        $orgModule = Vtiger_Module::getInstance('Accounts');
        if (Vtiger_Relation_Model::isRelationEntryExist($orgModule->name, $moduleInstance->name, '1:N')) {
            $account_id = $moduleModel->getField('account_id');
            $account_id->unsetRelatedModules(array('Accounts'));
            $orgModule->unsetRelatedList($moduleInstance, 'ReturnSaleOrders', 'get_dependents_list');
        }

        $contModule = Vtiger_Module::getInstance('Contacts');
        if (Vtiger_Relation_Model::isRelationEntryExist($contModule->name, $moduleInstance->name, '1:N')) {
            $contact_id = $moduleModel->getField('contact_id');
            $contact_id->unsetRelatedModules(array('Contacts'));
            $contModule->unsetRelatedList($moduleInstance, 'ReturnSaleOrders', 'get_dependents_list');
        }

        $invoiceModule = Vtiger_Module_Model::getInstance('Invoice');
        if (Vtiger_Relation_Model::isRelationEntryExist($moduleInstance->name, $invoiceModule->getName(), 'N:N')) {
            $moduleInstance->unsetRelatedList($invoiceModule, 'Invoice', 'get_related_list');
        }
        if (Vtiger_Relation_Model::isRelationEntryExist($invoiceModule->getName(), $moduleInstance->name, 'N:N')) {
            $invoiceModule->unsetRelatedList($moduleInstance, 'ReturnSaleOrders', 'get_related_list');
        }

        $paymentsModule = Vtiger_Module_Model::getInstance('Payments');
        if ($paymentsModule) {
            $related_to = $paymentsModule->getField('related_to');
            $referenceList = $related_to->getReferenceList();
            if (in_array('ReturnSaleOrders', $referenceList)) {
                $related_to->unsetRelatedModules(array('Payments' => 'ReturnSaleOrders'));
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

        if ($_REQUEST['action'] != 'ServiceOrdersAjax' && $_REQUEST['ajxaction'] != 'DETAILVIEW' && $_REQUEST['action'] != 'MassEditSave' && $_REQUEST['action'] != 'ProcessDuplicates' && $_REQUEST['action'] != 'SaveAjax' && $this->isLineItemUpdate != false && $_REQUEST['action'] != 'FROM_WS') {
            saveInventoryProductDetails($this, 'ReturnSaleOrders');
        }

        $update_query = "update vtiger_returnsaleorders set currency_id=?, conversion_rate=? where returnsaleordersid=?";
        $update_params = array($this->column_fields['currency_id'], $this->column_fields['conversion_rate'], $this->id);
        $adb->pquery($update_query, $update_params);
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
				INNER JOIN vtiger_returnsaleorders ON vtiger_returnsaleorders.returnsaleordersid = vtiger_crmentity.crmid
				LEFT JOIN vtiger_returnsaleorderscf ON vtiger_returnsaleorderscf.returnsaleordersid = vtiger_returnsaleorders.returnsaleordersid
				LEFT JOIN vtiger_inventoryproductrel ON vtiger_inventoryproductrel.id = vtiger_quotes.quoteid
				LEFT JOIN vtiger_products ON vtiger_products.productid = vtiger_inventoryproductrel.productid
				LEFT JOIN vtiger_service ON vtiger_service.serviceid = vtiger_inventoryproductrel.productid
				LEFT JOIN vtiger_contactdetails ON vtiger_contactdetails.contactid = vtiger_returnsaleorders.contactid
				LEFT JOIN vtiger_account ON vtiger_account.accountid = vtiger_returnsaleorders.accountid
				LEFT JOIN vtiger_currency_info ON vtiger_currency_info.id = vtiger_returnsaleorders.currency_id
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

        $matrix->setDependency('vtiger_inventoryproductreltmpServiceOrders', array('vtiger_productsServiceOrders', 'vtiger_serviceServiceOrders'));

        $query = "from vtiger_returnsaleorders
        inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_returnsaleorders.returnsaleordersid";

        if ($queryplanner->requireTable("vtiger_currency_info$module")) {
            $query .= " left join vtiger_currency_info as vtiger_currency_info$module on vtiger_currency_info$module.id = vtiger_returnsaleorders.currency_id";
        }
        if ($type !== 'COLUMNSTOTOTAL' || $this->lineItemFieldsInCalculation == true) {
            if ($queryplanner->requireTable("vtiger_inventoryproductreltmpServiceOrders", $matrix) || $queryplanner->requireTable("vtiger_inventoryproductrelServiceOrders", $matrix)) {
                $query .= " left join vtiger_inventoryproductrel as vtiger_inventoryproductreltmpServiceOrders on vtiger_returnsaleorders.returnsaleordersid = vtiger_inventoryproductreltmpServiceOrders.id";
            }
            if ($queryplanner->requireTable("vtiger_productsServiceOrders")) {
                $query .= " left join vtiger_products as vtiger_productsServiceOrders on vtiger_productsServiceOrders.productid = vtiger_inventoryproductreltmpServiceOrders.productid";
            }
            if ($queryplanner->requireTable("vtiger_serviceServiceOrders")) {
                $query .= " left join vtiger_service as vtiger_serviceServiceOrders on vtiger_serviceServiceOrders.serviceid = vtiger_inventoryproductreltmpServiceOrders.productid";
            }
        }
        if ($queryplanner->requireTable("vtiger_returnsaleorderscf")) {
            $query .= " left join vtiger_returnsaleorderscf on vtiger_returnsaleorders.returnsaleordersid = vtiger_returnsaleorderscf.returnsaleordersid";
        }
        if ($queryplanner->requireTable("vtiger_groupsServiceOrders")) {
            $query .= " left join vtiger_groups as vtiger_groupsServiceOrders on vtiger_groupsServiceOrders.groupid = vtiger_crmentity.smownerid";
        }
        if ($queryplanner->requireTable("vtiger_usersServiceOrders")) {
            $query .= " left join vtiger_users as vtiger_usersServiceOrders on vtiger_usersServiceOrders.id = vtiger_crmentity.smownerid";
        }

        $query .= " left join vtiger_groups on vtiger_groups.groupid = vtiger_crmentity.smownerid";
        $query .= " left join vtiger_users on vtiger_users.id = vtiger_crmentity.smownerid";

        if ($queryplanner->requireTable("vtiger_lastModifiedByServiceOrders")) {
            $query .= " left join vtiger_users as vtiger_lastModifiedByServiceOrders on vtiger_lastModifiedByServiceOrders.id = vtiger_crmentity.modifiedby";
        }
        if ($queryplanner->requireTable('vtiger_createdbyServiceOrders')) {
            $query .= " left join vtiger_users as vtiger_createdbyServiceOrders on vtiger_createdbyServiceOrders.id = vtiger_crmentity.smcreatorid";
        }
        if ($queryplanner->requireTable("vtiger_accountServiceOrders")) {
            $query .= " left join vtiger_account as vtiger_accountServiceOrders on vtiger_accountServiceOrders.accountid = vtiger_returnsaleorders.account_id";
        }
        if ($queryplanner->requireTable("vtiger_contactdetailsServiceOrders")) {
            $query .= " left join vtiger_contactdetails as vtiger_contactdetailsServiceOrders on vtiger_contactdetailsServiceOrders.contactid = vtiger_returnsaleorders.contact_id";
        }
        $focus = CRMEntity::getInstance($module);
        $relQuery = $focus->getReportsUiType10Query($module, $queryplanner);
        $query .= ' ' . $relQuery;
        return $query;
    }

    function generateReportsSecQuery($module, $secmodule, $queryPlanner) {
        $matrix = $queryPlanner->newDependencyMatrix();
        $matrix->setDependency('vtiger_crmentityServiceOrders', array('vtiger_usersServiceOrders', 'vtiger_groupsServiceOrders', 'vtiger_lastModifiedByServiceOrders'));
        $matrix->setDependency('vtiger_inventoryproductrelServiceOrders', array('vtiger_productsServiceOrders', 'vtiger_serviceServiceOrders'));


        if (!$queryPlanner->requireTable('vtiger_returnsaleorders', $matrix) && !$queryPlanner->requireTable('vtiger_crmentityServiceOrders', $matrix)) {
            return '';
        }
        $matrix->setDependency('vtiger_returnsaleorders', array('vtiger_crmentityServiceOrders', "vtiger_currency_info$secmodule",
            'vtiger_returnsaleorderscf', 'vtiger_inventoryproductrelServiceOrders', 'vtiger_contactdetailsServiceOrders', 'vtiger_accountServiceOrders',
            'vtiger_usersRel1'));

        $query = $this->getRelationQuery($module, $secmodule, "vtiger_returnsaleorders", "returnsaleordersid", $queryPlanner);
        if ($queryPlanner->requireTable("vtiger_crmentityServiceOrders", $matrix)) {
            $query .= " left join vtiger_crmentity as vtiger_crmentityServiceOrders on vtiger_crmentityServiceOrders.crmid=vtiger_returnsaleorders.returnsaleordersid and vtiger_crmentityServiceOrders.deleted=0";
        }
        if ($queryPlanner->requireTable("vtiger_returnsaleorderscf")) {
            $query .= " left join vtiger_returnsaleorderscf on vtiger_returnsaleorders.returnsaleordersid = vtiger_returnsaleorderscf.returnsaleordersid";
        }
        if ($queryPlanner->requireTable("vtiger_currency_info$secmodule")) {
            $query .= " left join vtiger_currency_info as vtiger_currency_info$secmodule on vtiger_currency_info$secmodule.id = vtiger_returnsaleorders.currency_id";
        }
        if ($queryPlanner->requireTable("vtiger_inventoryproductrelServiceOrders", $matrix)) {
            if ($module !== "Products" && $module !== "Services") {
                $query .= " LEFT JOIN vtiger_inventoryproductrel AS vtiger_inventoryproductreltmpServiceOrders ON vtiger_returnsaleorders.returnsaleordersid = vtiger_inventoryproductreltmpServiceOrders.id";
            }
        }

        if ($queryPlanner->requireTable("vtiger_productsServiceOrders")) {
            $query .= " left join vtiger_products as vtiger_productsServiceOrders on vtiger_productsServiceOrders.productid = vtiger_inventoryproductreltmpServiceOrders.productid";
        }
        if ($queryPlanner->requireTable("vtiger_serviceServiceOrders")) {
            $query .= " left join vtiger_service as vtiger_serviceServiceOrders on vtiger_serviceServiceOrders.serviceid = vtiger_inventoryproductreltmpServiceOrders.productid";
        }
        if ($queryPlanner->requireTable("vtiger_groupsServiceOrders")) {
            $query .= " left join vtiger_groups as vtiger_groupsServiceOrders on vtiger_groupsServiceOrders.groupid = vtiger_crmentityServiceOrders.smownerid";
        }
        if ($queryPlanner->requireTable("vtiger_usersServiceOrders")) {
            $query .= " left join vtiger_users as vtiger_usersServiceOrders on vtiger_usersServiceOrders.id = vtiger_crmentityServiceOrders.smownerid";
        }
        if ($queryPlanner->requireTable("vtiger_usersRel1")) {
            $query .= " left join vtiger_users as vtiger_usersRel1 on vtiger_usersRel1.id = vtiger_quotes.inventorymanager";
        }
        if ($queryPlanner->requireTable("vtiger_contactdetailsServiceOrders")) {
            $query .= " left join vtiger_contactdetails as vtiger_contactdetailsServiceOrders on vtiger_contactdetailsServiceOrders.contactid = vtiger_returnsaleorders.contact_id";
        }
        if ($queryPlanner->requireTable("vtiger_accountServiceOrders")) {
            $query .= " left join vtiger_account as vtiger_accountServiceOrders on vtiger_accountServiceOrders.accountid = vtiger_returnsaleorders.account_id";
        }
        if ($queryPlanner->requireTable("vtiger_lastModifiedByServiceOrders")) {
            $query .= " left join vtiger_users as vtiger_lastModifiedByServiceOrders on vtiger_lastModifiedByServiceOrders.id = vtiger_crmentityServiceOrders.modifiedby ";
        }
        if ($queryPlanner->requireTable("vtiger_createdbyServiceOrders")) {
            $query .= " left join vtiger_users as vtiger_createdbyServiceOrders on vtiger_createdbyServiceOrders.id = vtiger_crmentityServiceOrders.smcreatorid ";
        }

        $query .= parent::getReportsUiType10Query($secmodule, $queryPlanner);
        return $query;
    }

    public function getNonAdminAccessControlQuery($module, $user, $scope = '') {
		global $current_user;
		require_once('include/utils/GeneralUtils.php');
		$data = getUserDetailsBasedOnEmployeeModuleG($current_user->user_name);
		if (empty($data)) {
			return parent::getNonAdminAccessControlQuery($module, $user, $scope = '');
		}
		if (($data['cust_role'] == 'Service Manager' || $data['cust_role'] == 'Service Engineer') && $data['office'] != 'Service Centre') {
			return parent::getNonAdminAccessControlQuery($module, $user, $scope = '');
		} else if (($data['office'] == 'Production Division')) {
			return '';
		}  else if ($data['office'] == 'Service Centre') {
			return '';
		} else {
			return parent::getNonAdminAccessControlQuery($module, $user, $scope = '');
		}
	}

}
