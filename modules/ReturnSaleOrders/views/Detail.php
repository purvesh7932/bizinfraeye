<?php
class ReturnSaleOrders_Detail_View extends Inventory_Detail_View {

    function showLineItemDetails(Vtiger_Request $request) {
        $record = $request->get('record');
        $moduleName = $request->getModule();

        $recordModel = ReturnSaleOrders_Record_Model::getInstanceById($record);
        $relatedProducts = $recordModel->getProducts();

        //##Final details convertion started
        $finalDetails = $relatedProducts[1]['final_details'];

        //Final tax details convertion started
        $taxtype = $finalDetails['taxtype'];
        if ($taxtype == 'group') {
            $taxDetails = $finalDetails['taxes'];
            $taxCount = count($taxDetails);
            foreach ($taxDetails as $key => $taxInfo) {
                $taxDetails[$key]['amount'] = Vtiger_Currency_UIType::transformDisplayValue($taxInfo['amount'], null, true);
            }
            $finalDetails['taxes'] = $taxDetails;
        }
        //Final tax details convertion ended

        //Deducted tax details convertion started
        $deductTaxes = $finalDetails['deductTaxes'];
        foreach ($deductTaxes as $taxId => $taxInfo) {
            $deductTaxes[$taxId]['taxAmount'] = Vtiger_Currency_UIType::transformDisplayValue($deductTaxes[$taxId]['taxAmount'], null, true);
        }
        $finalDetails['deductTaxes'] = $deductTaxes;
        //Deducted tax details convertion ended

        $currencyFieldsList = array(
            'adjustment', 'grandTotal', 'hdnSubTotal', 'preTaxTotal', 'tax_totalamount',
            'shtax_totalamount', 'discountTotal_final', 'discount_amount_final', 'shipping_handling_charge', 'totalAfterDiscount', 'deductTaxesTotalAmount'
        );
        foreach ($currencyFieldsList as $fieldName) {
            $finalDetails[$fieldName] = Vtiger_Currency_UIType::transformDisplayValue($finalDetails[$fieldName], null, true);
        }

        $relatedProducts[1]['final_details'] = $finalDetails;
        //##Final details convertion ended

        //##Product details convertion started
        $productsCount = count($relatedProducts);
        for ($i = 1; $i <= $productsCount; $i++) {
            $product = $relatedProducts[$i];

            //Product tax details convertion started
            if ($taxtype == 'individual') {
                $taxDetails = $product['taxes'];
                $taxCount = count($taxDetails);
                for ($j = 0; $j < $taxCount; $j++) {
                    $taxDetails[$j]['amount'] = Vtiger_Currency_UIType::transformDisplayValue($taxDetails[$j]['amount'], null, true);
                }
                $product['taxes'] = $taxDetails;
            }
            //Product tax details convertion ended

            $currencyFieldsList = array(
                'taxTotal', 'netPrice', 'listPrice', 'unitPrice', 'productTotal', 'purchaseCost', 'margin',
                'discountTotal', 'discount_amount', 'totalAfterDiscount'
            );
            foreach ($currencyFieldsList as $fieldName) {
                $product[$fieldName . $i] = Vtiger_Currency_UIType::transformDisplayValue($product[$fieldName . $i], null, true);
            }

            $relatedProducts[$i] = $product;
        }
        //##Product details convertion ended

        $selectedChargesAndItsTaxes = $relatedProducts[1]['final_details']['chargesAndItsTaxes'];
        if (!$selectedChargesAndItsTaxes) {
            $selectedChargesAndItsTaxes = array();
        }

        $shippingTaxes = array();
        $allShippingTaxes = getAllTaxes('all', 'sh');
        foreach ($allShippingTaxes as $shTaxInfo) {
            $shippingTaxes[$shTaxInfo['taxid']] = $shTaxInfo;
        }

        $selectedTaxesList = array();
        foreach ($selectedChargesAndItsTaxes as $chargeId => $chargeInfo) {
            if ($chargeInfo['taxes']) {
                foreach ($chargeInfo['taxes'] as $taxId => $taxPercent) {
                    $taxInfo = array();
                    $amount = $calculatedOn = $chargeInfo['value'];

                    if ($shippingTaxes[$taxId]['method'] === 'Compound') {
                        $compoundTaxes = Zend_Json::decode(html_entity_decode($shippingTaxes[$taxId]['compoundon']));
                        if (is_array($compoundTaxes)) {
                            foreach ($compoundTaxes as $comTaxId) {
                                $calculatedOn += ((float)$amount * (float)$chargeInfo['taxes'][$comTaxId]) / 100;
                            }
                            $taxInfo['method']        = 'Compound';
                            $taxInfo['compoundon']    = $compoundTaxes;
                        }
                    }
                    $calculatedAmount = ((float)$calculatedOn * (float)$taxPercent) / 100;

                    $taxInfo['name']    = $shippingTaxes[$taxId]['taxlabel'];
                    $taxInfo['percent']    = $taxPercent;
                    $taxInfo['amount']    = Vtiger_Currency_UIType::transformDisplayValue($calculatedAmount, null, true);

                    $selectedTaxesList[$chargeId][$taxId] = $taxInfo;
                }
            }
        }

        $selectedChargesList = Inventory_Charges_Model::getChargeModelsList(array_keys($selectedChargesAndItsTaxes));
        foreach ($selectedChargesList as $chargeId => $chargeModel) {
            $chargeInfo['name']        = $chargeModel->getName();
            $chargeInfo['amount']    = Vtiger_Currency_UIType::transformDisplayValue($selectedChargesAndItsTaxes[$chargeId]['value'], null, true);
            $chargeInfo['percent']    = $selectedChargesAndItsTaxes[$chargeId]['percent'];
            $chargeInfo['taxes']    = $selectedTaxesList[$chargeId];
            $chargeInfo['deleted']    = $chargeModel->get('deleted');

            $selectedChargesList[$chargeId] = $chargeInfo;
        }

        $viewer = $this->getViewer($request);
        $viewer->assign('RELATED_PRODUCTS', $relatedProducts);
        $viewer->assign('SELECTED_CHARGES_AND_ITS_TAXES', $selectedChargesList);
        $viewer->assign('RECORD', $recordModel);
        $viewer->assign('MODULE_NAME', $moduleName);

        // implement Fields
        global $adb,$current_user;
        $tabId = getTabId($moduleName);
        $sql = "SELECT * FROM `vtiger_field` where tabid = ? and helpinfo = 'li_lg' ORDER BY `vtiger_field`.`sequence` ASC";
        $result = $adb->pquery($sql, array($tabId));
        $fields = [];
        $fieldNames = [];
        while ($row = $adb->fetch_array($result)) {
            array_push($fieldNames, $row['fieldname']);
            array_push($fields, $row);
        }
        global $current_user;
        include_once 'include/Webservices/DescribeObject.php';
        $describeInfo = vtws_describe($moduleName, $current_user);
        $modifiedFieldsArray = [];
        $modifiedFieldNamesArray = [];
        foreach ($fields as $fieldnameKey => $fieldinfo) {
            $fieldname = $fieldinfo['fieldname'];
            foreach ($describeInfo['fields'] as $key => $value) {
                if ($value['name'] ==  $fieldname) {
                    $fieldinfo['editable'] =  $value['editable'];
                    array_push($modifiedFieldsArray, $fieldinfo);
                    array_push($modifiedFieldNamesArray, $fieldname);
                    break;
                }
            }
        }

        //dropdown 1 purvesh
        $sql = "SELECT * FROM `vtiger_field` where tabid = ? and helpinfo = 'li_lg_1' ORDER BY `vtiger_field`.`sequence` ASC";
        $result = $adb->pquery($sql, array($tabId));
        $fields = [];
        $fieldNames = [];
        while ($row = $adb->fetch_array($result)) {
            array_push($fieldNames, $row['fieldname']);
            array_push($fields, $row);
        }
        $describeInfo = vtws_describe($moduleName, $current_user);
        $submodifiedFieldsArray = [];
        $submodifiedFieldNamesArray = [];
        foreach ($fields as $fieldnameKey => $fieldinfo) {
            $fieldname = $fieldinfo['fieldname'];
            foreach ($describeInfo['fields'] as $key => $value) {
                if ($value['name'] ==  $fieldname) {
                    $fieldinfo['editable'] =  $value['editable'];
                    array_push($submodifiedFieldsArray, $fieldinfo);
                    array_push($submodifiedFieldNamesArray, $fieldname);
                    break;
                }
            }
        }

        //dropdown 2
        $sql = "SELECT * FROM `vtiger_field` where tabid = ? and helpinfo = 'li_lg_2' ORDER BY `vtiger_field`.`sequence` ASC";
        $result = $adb->pquery($sql, array($tabId));
        $fields = [];
        $fieldNames = [];
        while ($row = $adb->fetch_array($result)) {
            array_push($fieldNames, $row['fieldname']);
            array_push($fields, $row);
        }
        $describeInfo = vtws_describe($moduleName, $current_user);
        $sub2modifiedFieldsArray = [];
        $sub2modifiedFieldNamesArray = [];
        foreach ($fields as $fieldnameKey => $fieldinfo) {
            $fieldname = $fieldinfo['fieldname'];
            foreach ($describeInfo['fields'] as $key => $value) {
                if ($value['name'] ==  $fieldname) {
                    $fieldinfo['editable'] =  $value['editable'];
                    array_push($sub2modifiedFieldsArray, $fieldinfo);
                    array_push($sub2modifiedFieldNamesArray, $fieldname);
                    break;
                }
            }
        }


         //sub line item
        // implement Fields
        global $adb,$current_user;
        $tabId = getTabId($moduleName);
        $sql = "SELECT * FROM `vtiger_field` where tabid = ? and helpinfo LIKE 'li_lg%' ORDER BY `vtiger_field`.`sequence` ASC";
        $result = $adb->pquery($sql, array($tabId));
        $fields = [];
        $fieldNames = [];
        while ($row = $adb->fetch_array($result)) {
            array_push($fieldNames, $row['fieldname']);
            array_push($fields, $row);
        }
        $describeInfo = vtws_describe($moduleName, $current_user);
        $allmodifiedFieldsArray = [];
        $allmodifiedFieldNamesArray = [];
        foreach ($fields as $fieldnameKey => $fieldinfo) {
            $fieldname = $fieldinfo['fieldname'];
            foreach ($describeInfo['fields'] as $key => $value) {
                if ($value['name'] ==  $fieldname) {
                    $fieldinfo['editable'] =  $value['editable'];
                    array_push($allmodifiedFieldsArray, $fieldinfo);
                    array_push($allmodifiedFieldNamesArray, $fieldname);
                    break;
                }
            }
        }


        $viewer->assign('LINEITEM_CUSTOM_FIELDNAMES', $allmodifiedFieldNamesArray);
        $viewer->assign('LINEITEM_CUSTOM_FIELDS', $modifiedFieldsArray);
        $viewer->assign('SUB_LINEITEM_CUSTOM_FIELDNAMES',$submodifiedFieldNamesArray );
        $viewer->assign('SUB_LINEITEM_CUSTOM_FIELDS', $submodifiedFieldsArray);
        $viewer->assign('SUB2_LINEITEM_CUSTOM_FIELDNAMES', $sub2modifiedFieldNamesArray);
        $viewer->assign('SUB2_LINEITEM_CUSTOM_FIELDS', $sub2modifiedFieldsArray);
    }
}
