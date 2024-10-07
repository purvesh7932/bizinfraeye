<?php

class Mobile_WS_getModuleRelations extends Mobile_WS_Controller {

    function process(Mobile_API_Request $request) {
        $response = new Mobile_API_Response();
        $module = $request->get('module');
        if (empty($module)) {
            $response->setError(1501, "Module is not specified.");
            return $response;
        }
        global $adb;
        $products = [];
        $tabid = getTabid($module);
        $sql = "SELECT vtiger_relatedlists.*,vtiger_tab.name as 'modulename' FROM `vtiger_relatedlists`"
                . " INNER JOIN vtiger_tab on (vtiger_tab.tabid = vtiger_relatedlists.related_tabid) "
                . " where vtiger_relatedlists.tabid = ? and vtiger_relatedlists.presence = ?  ";
       
        $result = $adb->pquery($sql, array($tabid, 0));
        $productCategories = [];
        while ($row = $adb->fetchByAssoc($result)) {
            unset($row['name']);
            array_push($productCategories, $row);
        }
        $products['relatedModules'] = $productCategories;
        $response->setResult($products);
        return $response;
    }

}
