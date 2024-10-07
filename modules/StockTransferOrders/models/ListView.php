<?php

class StockTransferOrders_ListView_Model extends Inventory_ListView_Model {
    
    public function isImportEnabled() {
        return false;
    }
    
}