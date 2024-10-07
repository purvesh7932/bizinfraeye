<?php
include_once('include/utils/GeneralUtils.php');
function SyncToExternalOnSTOCreate($entityData) {
    global $adb;
    global $log;
    $recordInfo = $entityData->{'data'};
    $id = $recordInfo['id'];
    $id = explode('x', $id);
    $id = $id[1];

    $sql = 'select external_app_num from vtiger_stocktransferorders ' .
        ' inner join vtiger_crmentity on vtiger_crmentity.crmid = vtiger_stocktransferorders.stocktransferorderid ' .
        ' where stocktransferorderid = ?';
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

    $plant = $recordInfo['plant_name'];
    $plant = explode('x', $plant);
    $plant = $plant[1];

    $sql = "select plant_code from vtiger_maintenanceplant where maintenanceplantid = ? ";
    $result = $adb->pquery($sql, array($plant));
    $plantcode = '';
    $dataRow = $adb->fetchByAssoc($result, 0);
    if (empty($dataRow['plant_code'])) {
        $plantcode = '';
    } else {
        $plantcode = $dataRow['plant_code'];
    }

    //Reciving Plant Implementation
    $recplant = $recordInfo['rec_plant_name'];
    $recplant = explode('x', $recplant);
    $recplant = $recplant[1];

    $sql = "select plant_code from vtiger_maintenanceplant where maintenanceplantid = ? ";
    $result = $adb->pquery($sql, array($recplant));
    $recplantcode = '';
    $dataRow = $adb->fetchByAssoc($result, 0);
    if (empty($dataRow['plant_code'])) {
        $recplantcode = '';
    } else {
        $recplantcode = $dataRow['plant_code'];
    }

    $items = getProductsOfSTO($id, $recplantcode);
    $docType = getCodeOFValue('lsi_sto_type', $recordInfo['lsi_sto_type']);
    $purchGroup = getCodeOFValue('lsi_purchase_grp', $recordInfo['lsi_purchase_grp']);
    $purchasOrg = $recordInfo['lsi_purchase_org'];
    if (empty($purchasOrg)) {
        $purchasOrg = 'SP01';
    }
    // $companyCode = $recordInfo['lsi_company_code'];
    $url = getExternalAppURL('CreateSTO');
    $header = array('Content-Type:multipart/form-data');
    $data = array(
        'DOC_TYPE'  => $docType,
        'PURCH_ORG' => $purchasOrg,
        'PUR_GROUP'  => $purchGroup,
        'SUPPLY_PLANT' => $plantcode,
        'COLLECTIVE_NO' => $recordInfo['collective_no'],
        'YOUR_REF' => $recordInfo['your_ref'],
        'OUR_REF' => $recordInfo['our_ref'],
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

            $dataArr = getSingleColumnValue(array(
                'table' => 'vtiger_serviceorders',
                'columnId' => 'ticket_id',
                'idValue' => $ticketId,
                'expectedColValue' => 'external_app_num'
            ));
            $ext_app_num_so = $dataArr[0]['external_app_num'];

            $query = "UPDATE vtiger_stocktransferorders SET equipment_id=? ,
            project_name=? , account_id=? , sr_ticket_type=? , ext_app_num_noti= ?, ext_app_num_so = ?
            WHERE stocktransferorderid=?";
            $adb->pquery($query, array(
                $equipment_id, $project_name, $account_id, $sr_ticket_type, $ext_app_num_noti,
                $ext_app_num_so, $id
            ));
        }
    }

    $jsonParseError = json_last_error();
    if (empty(trim($response['STO_NUMBER']))) {
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
                header("Location: index.php?module=StockTransferOrders&view=Edit&record=$id&app=SUPPORT");
                exit();
            } else {
                $_SESSION["errorFromExternalApp"] = $responseUnencode;
                $_SESSION["lastSyncedExterAppRecord"] = $id;
                header("Location: index.php?module=StockTransferOrders&view=Edit&record=$id&app=SUPPORT");
                exit();
            }
        }
    } else {
        $query = "UPDATE vtiger_stocktransferorders SET external_app_num=? WHERE stocktransferorderid=?";
        $adb->pquery($query, array($response['STO_NUMBER'], $id));
        $_SESSION["errorFromExternalApp"] = NULL;
        $_SESSION["lastSyncedExterAppRecord"] = NULL;
    }
}

function getProductsOfSTO($recordId, $recplantcode) {
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
        $product['PLANT'] = $recplantcode;
        $product['LGORT'] = getCodeOFValue('lid_store_locations', $adb->query_result($result, $i, 'lid_store_locations'));
        $delveryDate = getDateInEterAPPFormat($adb->query_result($result, $i, 'lid_sto_del_date'));
        if (!empty($delveryDate)) {
            $product['EEIND'] = $delveryDate;
        }
        array_push($products, $product);
    }
    return $products;
}

function getCodeOFValue($keyTable, $value) {
    global $adb;
    $sql = 'select code from vtiger_' . $keyTable
        . ' where ' . $keyTable . ' = ?';
    $sqlResult = $adb->pquery($sql, array($value));
    $dataRow = $adb->fetchByAssoc($sqlResult, 0);
    $code = '';
    if (empty($dataRow)) {
        $code = '';
    } else {
        $code = $dataRow['code'];
    }
    return $code;
}

function getDateInEterAPPFormat($date) {
    if (empty($date)) {
        return '';
    } else {
        return str_replace('-', '', $date);
    }
}
