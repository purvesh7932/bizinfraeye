<?php

class StockTransferOrders_Field_Model extends Inventory_Field_Model {

    public function isRestrictedInWorkflowUpdate() {
        $restricted = false;
        $restrictedFields = array('account_id', 'contact_id');
        if (in_array($this->getName(), $restrictedFields)) {
            $restricted = true;
        }
        return $restricted;
    }

    public function isAjaxEditable() {
        return false;
    }
}
