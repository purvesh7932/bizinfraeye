<?php
include_once('include/utils/GeneralUtils.php');
class Mobile_WS_GetAllMatchingSubAssebly extends Mobile_WS_Controller {
    function process(Mobile_API_Request $request) {
        $response = new Mobile_API_Response();
        $poDetail = $request->get('sad_po_detail');
        $sad_po_date = $request->get('sad_po_date');
        $query = "SELECT * FROM vtiger_servicereports
		LEFT JOIN vtiger_servicereportscf ON vtiger_servicereportscf.servicereportsid = vtiger_servicereports.servicereportsid
		LEFT JOIN vtiger_inventoryproductrel ON vtiger_inventoryproductrel.id = vtiger_servicereports.servicereportsid 
		 LEFT JOIN vtiger_inventoryproductrel_other ON vtiger_inventoryproductrel_other.id = vtiger_servicereports.servicereportsid
		 where ";


        if (empty($poDetail) && empty($poDetail)) {
            $responseObject['recordData'] = [];
            $response->setResult($responseObject);
            $response->setApiSucessMessage('Successfully Fetched Data');
            return $response;
        }

        $whereCondition = '';
        $params = [];
        if (!empty($poDetail)) {
            $whereCondition = $whereCondition . ' vtiger_inventoryproductrel_other.sad_sub_ass_po_det = ? or ';
            array_push($params, $poDetail);
        }

        if (!empty($sad_po_date)) {
            $whereCondition = $whereCondition . ' vtiger_inventoryproductrel_other.sad_podate = ? or ';
            array_push($params, $sad_po_date);
        }

        $pos = strrpos($whereCondition, "or");

        if ($pos !== false) {
            $whereCondition = substr_replace($whereCondition, "", $pos, strlen("or"));
        }

        $adb = PearDatabase::getInstance();
        $result = $adb->pquery($query . '  ' . $whereCondition, $params);
        $records = array();
        while ($row = $adb->fetchByAssoc($result)) {
            array_push($records, $row);
        }

        $responseObject['recordData'] = $records;
        $response->setResult($responseObject);
        $response->setApiSucessMessage('Successfully Fetched Data');
        return $response;
    }
}
