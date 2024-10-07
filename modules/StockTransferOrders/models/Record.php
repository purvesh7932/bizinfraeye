<?php

class StockTransferOrders_Record_Model extends Inventory_Record_Model {

    public static $OPEN = '';
    public static $PARTIALLY_REDEEMED = 'Partially';
    public static $FULLY_REDEEMED = 'Fully';
    public static $VOID = 'Void';
    public static $CREDIT_TYPE = 'credited';

    public function getStatus() {
        return '';
    }

    public function getParentRecord() {
        $related_to = false;
        $rel_acc = $this->get('account_id');
        $rel_cont = $this->get('contact_id');
        if ($rel_acc && isRecordExists($rel_acc)) {
            $related_to = $rel_acc;
        } else if ($rel_cont && isRecordExists($rel_cont)) {
            $related_to = $rel_cont;
        }
        return $related_to ? Vtiger_Record_Model::getInstanceById($related_to) : $related_to;
    }

}
