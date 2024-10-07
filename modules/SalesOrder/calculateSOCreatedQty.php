<?php
function calculateSOCreatedQty($entityData) {
    global $adb;
    $data = $entityData->{'data'};
    include_once('include/utils/GeneralConfigUtils.php');
    foreach ($data['LineItems'] as $key1 => $lineItem) {
        $parentLineId = $lineItem['failedpart_lineid'];
        $qty = getAllSOWithParentId($parentLineId);
        $creatableQty = getValidatedRecivedQty($parentLineId) -  $qty;
        $query = "UPDATE vtiger_inventoryproductrel SET salesorder_cr_qty=? ,
        so_creatable_qty = ?
        WHERE lineitem_id=?";
        $adb->pquery($query, array($qty, $creatableQty, $parentLineId));
    }
}
