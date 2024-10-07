<?php
 
class Mobile_WS_getCInstanceURL extends Mobile_WS_Controller {
 
    function requireLogin() {
        return false;
    }
 
    function process(Mobile_API_Request $request) {
        $response = new Mobile_API_Response();
        $cCode = $request->get('cCode');
        if (empty($cCode)) {
            $response->setError(1501, "cCode is not specified.");
            return $response;
        }
        global $adb;
        $products = [];
        $sql = " select company_website from vtiger_companies "
                . " INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_companies.companiesid "
                . " where vtiger_companies.company_name = ? and vtiger_crmentity.deleted = 0";
 
        $result = $adb->pquery($sql, array($cCode));
        $url = '';
        while ($row = $adb->fetchByAssoc($result)) {
            $url = $row['company_website'];
        }
        if (empty($url)) {
            $response->setError(1501, "URL Is Not Found For This Company Code");
            return $response;
        }
        $products['url'] = $url;
        $response->setResult($products);
        $response->setApiSucessMessage("URL Fetched Successfuly");
        return $response;
    }
 
}