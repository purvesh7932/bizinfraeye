<?php

class FailedParts_ListView_Model extends Inventory_ListView_Model {
    
    public function isImportEnabled() {
        return false;
    }
    
}