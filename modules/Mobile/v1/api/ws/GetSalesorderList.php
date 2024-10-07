<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
class Mobile_WS_GetSalesorderList extends Mobile_WS_Controller {

    function process(Mobile_API_Request $request) {
        $adb = PearDatabase::getInstance();

        $whereQuery = "select s.equipment_id,s.ticket_id,s.project_name,s.contactid,i.productname,i.quantity,i.validated_part_no,i.part_description from vtiger_salesorder s INNER JOIN vtiger_inventoryproductrel i INNER JOIN vtiger_crmentity c on s.salesorderid=c.crmid where s.salesorderid= i.id and c.deleted=0";

        $recordInfos = [];
        $result = $adb->pquery($whereQuery, array());
        if($result){
            $rowCount = $adb->num_rows($result);
            while ($rowCount > 0) {
                $rowData = $adb->query_result_rowdata($result,$rowCount-1);
                array_push($recordInfos, array(
                    'equipment_no' => $rowData['equipment_id'],
                    'ticket_no' => $rowData['ticket_id'],
                    'project' => $rowData['project_name'],
                    'customer' => $rowData['contactid'],
                    'productname' => $rowData['productname'],
                    'qty' => $rowData['quantity'],
                    'validated_part_no' => $rowData['validated_part_no'],
                    'part_description' => $rowData['part_description']
                ));
                --$rowCount;
            }
        }

        $responseObject['recordData'] = $recordInfos;

        $response = new Mobile_API_Response();
        $response->setResult($responseObject);
        return $response;
    }
}