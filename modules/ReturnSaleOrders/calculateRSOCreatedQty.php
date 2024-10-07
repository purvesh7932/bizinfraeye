<?php
function calculateRSOCreatedQty($entityData) {
    global $adb;
    $data = $entityData->{'data'};

    $parentLineItemId = $data['parent_line_itemid'];
    include_once('include/utils/GeneralConfigUtils.php');
    $recordIds = getAllRSOWithParentId($parentLineItemId);

    $sql = "SELECT sum(sto_qty) as totalqty FROM `vtiger_inventoryproductrel` where id in (" .
        generateQuestionMarks($recordIds) .
        ")";

    $result = $adb->pquery($sql, $recordIds);
    $qty = 0;
    while ($row = $adb->fetch_array($result)) {
        $qty = $row['totalqty'];
    }

    if (!empty($qty)) {
        $query = "UPDATE vtiger_inventoryproductrel SET rso_created_qty=? WHERE lineitem_id=?";
        $adb->pquery($query, array($qty, $parentLineItemId));
    }
}
