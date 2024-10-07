<?php
include_once('include/utils/GeneralUtils.php');
function SyncToSAPOnSaleOrderCreate($entityData) {
    global $adb;
    global $log;
    $recordInfo = $entityData->{'data'};
    $id = $recordInfo['id'];
    $id = explode('x', $id);
    $id = $id[1];

    $sql = 'select external_app_num from vtiger_salesorder ' .
        ' inner join vtiger_crmentity on vtiger_crmentity.crmid = vtiger_salesorder.salesorderid ' .
        ' where salesorderid = ? and vtiger_crmentity.deleted = 0';
    $sqlResult = $adb->pquery($sql, array($id));
    $dataRow = $adb->fetchByAssoc($sqlResult, 0);
    $exterAppNum = 0;
    if (empty($dataRow)) {
    } else {
        $exterAppNum = $dataRow['external_app_num'];
    }
    if (!empty($exterAppNum)) {
        return;
    }

    $soldToParty = $recordInfo['sold_to_party'];
    $soldToParty = explode('x', $soldToParty);
    $soldToParty = $soldToParty[1];
    $dataArr = getSingleColumnValue(array(
        'table' => 'vtiger_account',
        'columnId' => 'accountid',
        'idValue' => $soldToParty,
        'expectedColValue' => 'external_app_num'
    ));
    $soldToParty = $dataArr[0]['external_app_num'];

    $shipToParty = $recordInfo['ship_to_party'];
    $shipToParty = explode('x', $shipToParty);
    $shipToParty = $shipToParty[1];
    $dataArr = getSingleColumnValue(array(
        'table' => 'vtiger_account',
        'columnId' => 'accountid',
        'idValue' => $shipToParty,
        'expectedColValue' => 'external_app_num'
    ));
    $shipToParty = $dataArr[0]['external_app_num'];

    $ticketId = $recordInfo['ticket_id'];
    $ticketId = explode('x', $ticketId);
    $ticketId = $ticketId[1];
    $dataArr = getSingleColumnValue(array(
        'table' => 'vtiger_troubletickets',
        'columnId' => 'ticketid',
        'idValue' => $ticketId,
        'expectedColValue' => 'external_app_num'
    ));
    $notificationNum = $recordInfo['po_no']; //$dataArr[0]['external_app_num'];

    $ticketId = $recordInfo['ticket_id'];
    $ticketId = explode('x', $ticketId);
    $ticketId = $ticketId[1];


    $failed_part_id = $recordInfo['failed_part_id'];
    $failed_part_id = explode('x', $failed_part_id);
    $failed_part_id = $failed_part_id[1];
    
    $dataArr = getSingleColumnValue(array(
        'table' => 'vtiger_failedparts',
        'columnId' => 'failedpartid',
        'idValue' => $failed_part_id,
        'expectedColValue' => 'ticket_id'
    ));
    $fTicketId = $dataArr[0]['ticket_id'];

    if (!empty($fTicketId)) {
        $reportId = IGGetFirstServiceReportOfSR($fTicketId);
        if (!empty($reportId) && isRecordExists($reportId)) {
            $dataArr = getSingleColumnValue(array(
                'table' => 'vtiger_troubletickets',
                'columnId' => 'ticketid',
                'idValue' => $fTicketId,
                'expectedColValue' => 'external_app_num'
            ));
            $recordInstance = Vtiger_Record_Model::getInstanceById($reportId);
            $equipment_id = $recordInstance->get('equipment_id');
            $project_name = $recordInstance->get('project_name');
            // $account_id = $recordInstance->get('account_id');
            // $sr_ticket_type = $recordInstance->get('sr_ticket_type');
            // $ext_app_num_noti = $dataArr[0]['external_app_num'];
            $query = "UPDATE vtiger_salesorder SET equipment_id=? ,
            project_name=?, ticket_id = ?
            WHERE salesorderid=?";
            $adb->pquery($query, array($equipment_id, $project_name,$fTicketId, $id));
        }
    }

    $poDate = $recordInfo['po_date'];
    $poDate = IGgetDateInEterAPPFormat($poDate);

    $plant = $recordInfo['plant_name'];
    $plant = explode('x', $plant);
    $plant = $plant[1];
    $dataArr = getSingleColumnValue(array(
        'table' => 'vtiger_maintenanceplant',
        'columnId' => 'maintenanceplantid',
        'idValue' => $plant,
        'expectedColValue' => 'plant_code'
    ));
    $plantcode = $dataArr[0]['plant_code'];

    $items = getProductsOfSalesOrder($id, $plantcode);
    $url = getExternalAppURL('CreateSalesOrder');
    $header = array('Content-Type:multipart/form-data');
    $data = array(
        'IM_KUNNR'  => $soldToParty,
        'IM_KUNWE'  => $shipToParty,
        'IM_BSTKD'  => $notificationNum,
        'IM_BSTDK'  => $poDate,
        // 'IM_AUART'  => 'ZAFO',
        'IM_AUGRU' => IGgetCodeOFValue('order_for_reason', $recordInfo['order_for_reason']),
        // 'IM_VKORG'  => '8000',
        // 'IM_VTWEG'  => '10',
        // 'IM_SPART'  => '00',
        'IT_SALESORDER' => json_encode($items)
    );

    $log->debug("*****Data Sendig To SAP***********" . json_encode($data) . "********");
    $resource = curl_init();
    curl_setopt($resource, CURLOPT_URL, $url);
    curl_setopt($resource, CURLOPT_HTTPHEADER, $header);
    curl_setopt($resource, CURLOPT_POST, 1);
    curl_setopt($resource, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($resource, CURLOPT_POSTFIELDS, $data);
    $response = curl_exec($resource);
    $responseUnencode = $response;
    $log->debug("*****Response Recived From SAP***********$response********");
    $response = json_decode($response, true);
    curl_close($resource);
    $jsonParseError = json_last_error();
    if (empty(trim($response['EX_VBELN']))) {
        global $actionFromMobileApis;
        if ($actionFromMobileApis) {
            global $hasSAPErrors, $ErrorMessage, $SAPDetailError;
            $hasSAPErrors = true;
            $ErrorMessage = "SAP Sync Is Failed";
            $SAPDetailError = IgGetSAPErrorFormatASerrorArray($response['IT_RETURN']);
        } else {
            if (empty($jsonParseError)) {
                $_SESSION["errorFromExternalApp"] = IgGetSAPErrorFormatASerrorArray($response['IT_RETURN']);
                $_SESSION["lastSyncedExterAppRecord"] = $id;
                header("Location: index.php?module=SalesOrder&view=Edit&record=$id&app=SUPPORT");
                exit();
            } else {
                $_SESSION["errorFromExternalApp"] = $responseUnencode;
                $_SESSION["lastSyncedExterAppRecord"] = $id;
                header("Location: index.php?module=SalesOrder&view=Edit&record=$id&app=SUPPORT");
                exit();
            }
        }
    } else {
        $query = "UPDATE vtiger_salesorder SET external_app_num=? WHERE salesorderid=?";
        $adb->pquery($query, array($response['EX_VBELN'], $id));
        $_SESSION["errorFromExternalApp"] = NULL;
        $_SESSION["lastSyncedExterAppRecord"] = NULL;
    }
}

function getProductsOfSalesOrder($recordId, $recplantcode) {
    global $adb;
    $query = "SELECT
        case when vtiger_products.productid != '' then vtiger_products.productname else vtiger_service.servicename end as productname,
        case when vtiger_products.productid != '' then vtiger_products.product_no else vtiger_service.service_no end as productcode,
        case when vtiger_products.productid != '' then vtiger_products.unit_price else vtiger_service.unit_price end as unit_price,
        case when vtiger_products.productid != '' then vtiger_products.qtyinstock else 'NA' end as qtyinstock,
        case when vtiger_products.productid != '' then 'Products' else 'Services' end as entitytype,
        vtiger_inventoryproductrel.listprice, vtiger_products.is_subproducts_viewable, vtiger_products.plant_name,
        vtiger_inventoryproductrel.description AS product_description, vtiger_inventoryproductrel.*,
        vtiger_crmentity.deleted FROM vtiger_inventoryproductrel
        LEFT JOIN vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_inventoryproductrel.productid
        LEFT JOIN vtiger_products ON vtiger_products.productid=vtiger_inventoryproductrel.productid
        LEFT JOIN vtiger_service ON vtiger_service.serviceid=vtiger_inventoryproductrel.productid
        WHERE id=? ORDER BY sequence_no";

    $params = array($recordId);
    $result = $adb->pquery($query, $params);
    $num_rows = $adb->num_rows($result);
    $products = [];
    for ($i = 0; $i < $num_rows; $i++) {
        $product = array();
        $product['MATNR'] = $adb->query_result($result, $i, 'productname');
        $product['ZMENG'] = $adb->query_result($result, $i, 'quantity');
        $product['WERKS'] = $recplantcode;
        $product['KBETR'] =  $adb->query_result($result, $i, 'final_amount');
        array_push($products, $product);
    }
    return $products;
}
