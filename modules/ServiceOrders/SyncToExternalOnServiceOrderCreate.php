<?php
include_once('include/utils/GeneralUtils.php');
function SyncToExternalOnServiceOrderCreate($entityData) {
    global $adb;
    global $log;
    global $isAlreadyServiceOrderCreated;
    if ($isAlreadyServiceOrderCreated == true) {
        return;
    }
    $recordInfo = $entityData->{'data'};
    $id = $recordInfo['id'];
    $id = explode('x', $id);
    $id = $id[1];

    $ticketId = $recordInfo['ticket_id'];
    $ticketId = explode('x', $ticketId);
    $ticketId = $ticketId[1];

    if (!empty($ticketId)) {
        $reportId = IGGetFirstServiceReportOfSR($ticketId);
        if (!empty($reportId) && isRecordExists($reportId)) {
            $dataArr = getSingleColumnValue(array(
                'table' => 'vtiger_troubletickets',
                'columnId' => 'ticketid',
                'idValue' => $ticketId,
                'expectedColValue' => 'external_app_num'
            ));
            $recordInstance = Vtiger_Record_Model::getInstanceById($reportId);
            $equipment_id = $recordInstance->get('equipment_id');
            $project_name = $recordInstance->get('project_name');
            $account_id = $recordInstance->get('account_id');
            $sr_ticket_type = $recordInstance->get('sr_ticket_type');
            $ext_app_num_noti = $dataArr[0]['external_app_num'];
            $query = "UPDATE vtiger_serviceorders SET equipment_id=? ,
            project_name=? , account_id=? , sr_ticket_type=? , ext_app_num_noti= ?
            WHERE serviceordersid=?";
            $adb->pquery($query, array($equipment_id, $project_name, $account_id, $sr_ticket_type, $ext_app_num_noti, $id));
        }
    }

    $sql = "select external_app_num from vtiger_troubletickets where ticketid = ? ";
    $result = $adb->pquery($sql, array($ticketId));
    $ticketId = '';
    $dataRow = $adb->fetchByAssoc($result, 0);
    if (empty($dataRow['external_app_num'])) {
        $ticketId = '';
    } else {
        $ticketId = $dataRow['external_app_num'];
    }

    $items = getProductsOfSTO($id);

    $url = getExternalAppURL('CreateSO');
    $header = array('Content-Type:multipart/form-data');
    $data = array(
        'IM_QMNUM'  => '000'.$ticketId,
        'IT_ITEMS' => json_encode($items)
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
    if (empty(trim($response['EX_AUFNR']))) {
        global $actionFromMobileApis;
        if ($actionFromMobileApis) {
            global $hasSAPErrors, $ErrorMessage, $SAPDetailError;
            $hasSAPErrors = true;
            $ErrorMessage = "SAP Sync Is Failed";
            if (empty($jsonParseError)) {
                $SAPDetailError = IgGetSAPErrorFormatASerrorArray($response['IT_MESSAGES']);
            } else {
                $SAPDetailError = $responseUnencode;
            }
        } else {
            if (empty($jsonParseError)) {
                $_SESSION["errorFromExternalApp"] = IgGetSAPErrorFormatASerrorArray($response['IT_MESSAGES']);
                $_SESSION["lastSyncedExterAppRecord"] = $id;
                header("Location: index.php?module=ServiceOrders&view=Edit&record=$id&app=SUPPORT");
                exit();
            } else {
                $_SESSION["errorFromExternalApp"] = $responseUnencode;
                $_SESSION["lastSyncedExterAppRecord"] = $id;
                header("Location: index.php?module=ServiceOrders&view=Edit&record=$id&app=SUPPORT");
                exit();
            }
        }
    } else {
        $query = "UPDATE vtiger_serviceorders SET external_app_num=? WHERE serviceordersid=?";
        $adb->pquery($query, array($response['EX_AUFNR'], $id));
        $_SESSION["errorFromExternalApp"] = NULL;
        $_SESSION["lastSyncedExterAppRecord"] = NULL;
    }
}

function getProductsOfSTO($recordId) {
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
        $product['MENGE'] = $adb->query_result($result, $i, 'quantity');
        $product['POSTP'] = 'L';
        array_push($products, $product);
    }
    return $products;
}
