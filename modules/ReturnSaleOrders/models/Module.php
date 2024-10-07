<?php

class ReturnSaleOrders_Module_Model extends Inventory_Module_Model {

    public static $allowed_utilities = array('Export', 'PrintTemplates', 'Reopen', 'DuplicatesHandling', 'Create List');

    public function moduleSpecificLineItemTotalsTpl() {
        return 'CreditNoteLineItemTotals.tpl';
    }

    public function isMassEditEnabled() {
        return false;
    }

    public function isMassDeleteEnabled() {
        return false;
    }

    public function isInventoryModule() {
        return true;
    }

    function isMandatoryModule($profileModel = false) {
        $isInvoiceActive = vtlib_isModuleActive('Invoice');
        if ($profileModel && $isInvoiceActive) {
            $isInvoiceActive = $profileModel->hasModulePermission('Invoice');
        }
        return $isInvoiceActive ? true : false;
    }

    function isProfileUtilityAllowed($action) {
        $allowed = false;
        $allowedUtilities = self::$allowed_utilities;
        if (in_array($action, $allowedUtilities)) {
            $allowed = true;
        }
        return $allowed;
    }

    public function getModuleBasicLinks() {
        return array();
    }

    public function getInvoiceHeaders() {
        $fields = array('invoice_no', 'invoicedate', 'hdnGrandTotal', 'balance');
        $invoiceModule = Vtiger_Module_Model::getInstance('Invoice');

        $headers = array();
        foreach ($fields as $fieldName) {
            $headers[$fieldName] = $invoiceModule->getField($fieldName);
        }
        return $headers;
    }

    public function getDisabledClosedStates() {
        return array(ReturnSaleOrders_Record_Model::$PARTIALLY_REDEEMED);
    }

    public function isFixedReopenState() {
        return true;
    }

    public function isAllowedInWorkflowCreateRecordTask() {
        return false;
    }

    function isAllowedInMailroom() {
        return false;
    }

    public function getDefaultProfileActionPermissions() {
        $permissions = array();
        $standardActions = Vtiger_Action_Model::$standardActions;
        $notPermittedActions = array('Delete');
        foreach ($standardActions as $key => $actionName) {
            $permissions[$actionName] = in_array($actionName, $notPermittedActions) ? Settings_Profiles_Module_Model::NOT_PERMITTED_VALUE :
                    Settings_Profiles_Module_Model::IS_PERMITTED_VALUE;
        }
        return $permissions;
    }

   
    function unsetRelatedListForField($fieldId) {
        $db = PearDatabase::getInstance();
        Vtiger_Functions::triggerSettingsEvent('Related Tab', 'vtiger.settings.beforedelete.related_tab', array('module' => $this, 'related_field_id' => $fieldId));
        $result = $db->pquery("SELECT * FROM vtiger_relatedlists  WHERE relationfieldid = ?", array($fieldId));
        if ($db->num_rows($result) == 1) {
            $db->pquery("DELETE FROM vtiger_relatedlists WHERE relationfieldid=?", array($fieldId));
        } else if ($db->num_rows($result) > 1) {
            $tabId = getTabid($this->getName());
            $db->pquery("DELETE FROM vtiger_relatedlists WHERE relationfieldid = ? AND tabid = ?", array($fieldId, $tabId));
        }
        Vtiger_Functions::triggerSettingsEvent('Related Tab', 'vtiger.settings.afterdelete.related_tab', array('module' => $this, 'related_field_id' => $fieldId));
    }

}
