<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

/**
 * Inventory Record Model Class
 */
class Inventory_Record_Model extends Vtiger_Record_Model {

	function getCurrencyInfo() {
		$moduleName = $this->getModuleName();
		$currencyInfo = getInventoryCurrencyInfo($moduleName, $this->getId());
		return $currencyInfo;
	}

	function getProductTaxes() {
		$taxDetails = $this->get('taxDetails');
		if ($taxDetails) {
			return $taxDetails;
		}

		$record = $this->getId();
		if ($record) {
			$relatedProducts = getAssociatedProducts($this->getModuleName(), $this->getEntity());
			$taxDetails = $relatedProducts[1]['final_details']['taxes'];
		} else {
			$taxDetailsFromDB = getAllTaxes('available', '', $this->getEntity()->mode, $this->getId());
			$taxDetails = array();
			foreach ($taxDetailsFromDB as $key => $taxInfo) {
				$taxInfo['regions'] = Zend_Json::decode(html_entity_decode($taxInfo['regions']));
				$taxInfo['compoundon'] = Zend_Json::decode(html_entity_decode($taxInfo['compoundon']));
				$taxDetails[$taxInfo['taxid']] = $taxInfo;
			}
		}

		foreach ($taxDetails as $key => $taxInfo) {
			if ($taxInfo['method'] === 'Deducted') {
				unset($taxDetails[$key]);
			}
		}

		$this->set('taxDetails', $taxDetails);
		return $taxDetails;
	}

	function getShippingTaxes() {
		$shippingTaxDetails = $this->get('shippingTaxDetails');
		if ($shippingTaxDetails) {
			return $shippingTaxDetails;
		}

		$record = $this->getId();
		if ($record) {
			$relatedProducts = getAssociatedProducts($this->getModuleName(), $this->getEntity());
			$shippingTaxDetails = $relatedProducts[1]['final_details']['sh_taxes'];
		} else {
			$shippingTaxDetails = getAllTaxes('available', 'sh', 'edit', $this->getId());
		}

		$this->set('shippingTaxDetails', $shippingTaxDetails);
		return $shippingTaxDetails;
	}

	function getProducts() {
		$numOfCurrencyDecimalPlaces = getCurrencyDecimalPlaces();
		$relatedProducts = getAssociatedProducts($this->getModuleName(), $this->getEntity());
		$productsCount = count($relatedProducts);
		if($productsCount == 0){
			return [];
		}
		//Updating Tax details
		$taxtype = $relatedProducts[1]['final_details']['taxtype'];
		$productIdsList = array();
		for ($i=1;$i<=$productsCount; $i++) {
			$product = $relatedProducts[$i];
			$productId = $product['hdnProductId'.$i];
			$totalAfterDiscount = $product['totalAfterDiscount'.$i];

			if ($taxtype == 'individual') {
				$taxDetails = getTaxDetailsForProduct($productId, 'all');
				$taxCount = count($taxDetails);
				$taxTotal = '0';

				for($j=0; $j<$taxCount; $j++) {
					$taxValue = $product['taxes'][$j]['percentage'];

					$taxAmount = $totalAfterDiscount * $taxValue / 100;
					$taxTotal = $taxTotal + $taxAmount;

					$product['taxes'][$j]['amount'] = $taxAmount;
					$relatedProducts[$i]['taxes'][$j]['amount'] = $taxAmount;
				}

				$productTaxes = array();
				if ($product['taxes']) {
					foreach ($product['taxes'] as $key => $taxInfo) {
						$taxInfo['key'] = $key;
						$productTaxes[$taxInfo['taxid']] = $taxInfo;
					}
				}

				$taxTotal = 0.00;
				foreach ($productTaxes as $taxId => $taxInfo) {
					$taxAmount = $taxInfo['amount'];
					if ($taxInfo['compoundon']) {
						$amount = $totalAfterDiscount;
						foreach ($taxInfo['compoundon'] as $compTaxId) {
							$amount = $amount + $productTaxes[$compTaxId]['amount'];
						}
						$taxAmount = $amount * $taxInfo['percentage'] / 100;
					}
					$taxTotal = $taxTotal + $taxAmount;

					$relatedProducts[$i]['taxes'][$taxInfo['key']]['amount'] = $taxAmount;
					$relatedProducts[$i]['taxTotal'.$i]	= number_format($taxTotal, $numOfCurrencyDecimalPlaces, '.', '');
				}
				$netPrice = $totalAfterDiscount + $taxTotal;
				$relatedProducts[$i]['netPrice'.$i] = number_format($netPrice, $numOfCurrencyDecimalPlaces, '.', '');
			}

			if ($relatedProducts[$i]['entityType'.$i] == 'Products') {
				$productIdsList[] = $productId;
			}
		}

		//Updating Pre tax total
		$preTaxTotal = (float)$relatedProducts[1]['final_details']['hdnSubTotal']
						+ (float)$relatedProducts[1]['final_details']['shipping_handling_charge']
						- (float)$relatedProducts[1]['final_details']['discountTotal_final'];

		$relatedProducts[1]['final_details']['preTaxTotal'] = number_format($preTaxTotal, $numOfCurrencyDecimalPlaces,'.','');
		
		//Updating Total After Discount
		$totalAfterDiscount = (float)$relatedProducts[1]['final_details']['hdnSubTotal'] - (float)$relatedProducts[1]['final_details']['discountTotal_final'];

		$relatedProducts[1]['final_details']['totalAfterDiscount'] = number_format($totalAfterDiscount, $numOfCurrencyDecimalPlaces,'.','');
		$relatedProducts[1]['final_details']['discount_amount_final'] = number_format((float)$relatedProducts[1]['final_details']['discount_amount_final'], $numOfCurrencyDecimalPlaces,'.','');

		//charge value setting to related products array
		$selectedChargesAndItsTaxes = $this->getCharges();
		if (!$selectedChargesAndItsTaxes) {
			$selectedChargesAndItsTaxes = array();
		}
		$relatedProducts[1]['final_details']['chargesAndItsTaxes'] = $selectedChargesAndItsTaxes;

		$allChargeTaxes = array();
		foreach ($selectedChargesAndItsTaxes as $chargeId => $chargeInfo) {
			if (is_array($chargeInfo['taxes'])) {
				$allChargeTaxes = array_merge($allChargeTaxes, array_keys($chargeInfo['taxes']));
			} else {
				$selectedChargesAndItsTaxes[$chargeId]['taxes'] = array();
			}
		}

		$shippingTaxes = array();
		$allShippingTaxes = getAllTaxes('all', 'sh');
		foreach ($allShippingTaxes as $shTaxInfo) {
			$shippingTaxes[$shTaxInfo['taxid']] = $shTaxInfo;
		}

		$totalAmount = 0.00;
		foreach ($selectedChargesAndItsTaxes as $chargeId => $chargeInfo) {
			foreach ($chargeInfo['taxes'] as $taxId => $taxPercent) {
				$amount = $calculatedOn = $chargeInfo['value'];

				if ($shippingTaxes[$taxId]['method'] === 'Compound') {
					$compoundTaxes = Zend_Json::decode(html_entity_decode($shippingTaxes[$taxId]['compoundon']));
					if (is_array($compoundTaxes)) {
						foreach ($compoundTaxes as $comTaxId) {
							if ($shippingTaxes[$comTaxId]) {
								$calculatedOn += ((float)$amount * (float)$chargeInfo['taxes'][$comTaxId]) / 100;
							}
						}
					}
				}
				$totalAmount += ((float)$calculatedOn * (float)$taxPercent) / 100;
			}
		}
		$relatedProducts[1]['final_details']['shtax_totalamount'] = number_format($totalAmount, $numOfCurrencyDecimalPlaces, '.', '');

		//deduct tax values setting to related products
		$totalAfterDiscount = (float) $relatedProducts[1]['final_details']['totalAfterDiscount'];
		$deductedTaxesTotalAmount = 0.00;

		$deductTaxes = $this->getDeductTaxes();
		foreach ($deductTaxes as $taxId => $taxInfo) {
			$taxAmount = ($totalAfterDiscount * (float)$taxInfo['percentage']) / 100;
			$deductTaxes[$taxId]['amount'] = number_format($taxAmount, $numOfCurrencyDecimalPlaces,'.','');
			if ($taxInfo['selected']) {
				$deductedTaxesTotalAmount = $deductedTaxesTotalAmount + $taxAmount;
			}
		}

		$relatedProducts[1]['final_details']['deductTaxes'] = $deductTaxes;
		$relatedProducts[1]['final_details']['deductTaxesTotalAmount'] = number_format($deductedTaxesTotalAmount, $numOfCurrencyDecimalPlaces,'.','');

		if ($productIdsList) {
			$imageDetailsList = Products_Record_Model::getProductsImageDetails($productIdsList);

			for ($i=1; $i<=$productsCount; $i++) {
				$product = $relatedProducts[$i];
				$productId = $product['hdnProductId'.$i];
				$imageDetails = $imageDetailsList[$productId];
				if ($imageDetails) {
					$relatedProducts[$i]['productImage'.$i] = $imageDetails[0]['url'];
				}
			}
		}

		return $relatedProducts;
	}
	function getProductsOther() {
		$numOfCurrencyDecimalPlaces = getCurrencyDecimalPlaces();
		$relatedProducts = getAssociatedProductsAnother($this->getModuleName(), $this->getEntity());
		$productsCount = count($relatedProducts);
		if ($productsCount == 0) {
			return [];
		}
		//Updating Tax details
		$taxtype = $relatedProducts[1]['final_details']['taxtype'];
		$productIdsList = array();
		for ($i=1;$i<=$productsCount; $i++) {
			$product = $relatedProducts[$i];
			$productId = $product['hdnProductId'.$i];
			$totalAfterDiscount = $product['totalAfterDiscount'.$i];

			if ($taxtype == 'individual') {
				$taxDetails = getTaxDetailsForProduct($productId, 'all');
				$taxCount = count($taxDetails);
				$taxTotal = '0';

				for($j=0; $j<$taxCount; $j++) {
					$taxValue = $product['taxes'][$j]['percentage'];

					$taxAmount = $totalAfterDiscount * $taxValue / 100;
					$taxTotal = $taxTotal + $taxAmount;

					$product['taxes'][$j]['amount'] = $taxAmount;
					$relatedProducts[$i]['taxes'][$j]['amount'] = $taxAmount;
				}

				$productTaxes = array();
				if ($product['taxes']) {
					foreach ($product['taxes'] as $key => $taxInfo) {
						$taxInfo['key'] = $key;
						$productTaxes[$taxInfo['taxid']] = $taxInfo;
					}
				}

				$taxTotal = 0.00;
				foreach ($productTaxes as $taxId => $taxInfo) {
					$taxAmount = $taxInfo['amount'];
					if ($taxInfo['compoundon']) {
						$amount = $totalAfterDiscount;
						foreach ($taxInfo['compoundon'] as $compTaxId) {
							$amount = $amount + $productTaxes[$compTaxId]['amount'];
						}
						$taxAmount = $amount * $taxInfo['percentage'] / 100;
					}
					$taxTotal = $taxTotal + $taxAmount;

					$relatedProducts[$i]['taxes'][$taxInfo['key']]['amount'] = $taxAmount;
					$relatedProducts[$i]['taxTotal'.$i]	= number_format($taxTotal, $numOfCurrencyDecimalPlaces, '.', '');
				}
				$netPrice = $totalAfterDiscount + $taxTotal;
				$relatedProducts[$i]['netPrice'.$i] = number_format($netPrice, $numOfCurrencyDecimalPlaces, '.', '');
			}

			if ($relatedProducts[$i]['entityType'.$i] == 'Products') {
				$productIdsList[] = $productId;
			}
		}

		//Updating Pre tax total
		$preTaxTotal = (float)$relatedProducts[1]['final_details']['hdnSubTotal']
						+ (float)$relatedProducts[1]['final_details']['shipping_handling_charge']
						- (float)$relatedProducts[1]['final_details']['discountTotal_final'];

		$relatedProducts[1]['final_details']['preTaxTotal'] = number_format($preTaxTotal, $numOfCurrencyDecimalPlaces,'.','');
		
		//Updating Total After Discount
		$totalAfterDiscount = (float)$relatedProducts[1]['final_details']['hdnSubTotal'] - (float)$relatedProducts[1]['final_details']['discountTotal_final'];

		$relatedProducts[1]['final_details']['totalAfterDiscount'] = number_format($totalAfterDiscount, $numOfCurrencyDecimalPlaces,'.','');
		$relatedProducts[1]['final_details']['discount_amount_final'] = number_format((float)$relatedProducts[1]['final_details']['discount_amount_final'], $numOfCurrencyDecimalPlaces,'.','');

		//charge value setting to related products array
		$selectedChargesAndItsTaxes = $this->getCharges();
		if (!$selectedChargesAndItsTaxes) {
			$selectedChargesAndItsTaxes = array();
		}
		$relatedProducts[1]['final_details']['chargesAndItsTaxes'] = $selectedChargesAndItsTaxes;

		$allChargeTaxes = array();
		foreach ($selectedChargesAndItsTaxes as $chargeId => $chargeInfo) {
			if (is_array($chargeInfo['taxes'])) {
				$allChargeTaxes = array_merge($allChargeTaxes, array_keys($chargeInfo['taxes']));
			} else {
				$selectedChargesAndItsTaxes[$chargeId]['taxes'] = array();
			}
		}

		$shippingTaxes = array();
		$allShippingTaxes = getAllTaxes('all', 'sh');
		foreach ($allShippingTaxes as $shTaxInfo) {
			$shippingTaxes[$shTaxInfo['taxid']] = $shTaxInfo;
		}

		$totalAmount = 0.00;
		foreach ($selectedChargesAndItsTaxes as $chargeId => $chargeInfo) {
			foreach ($chargeInfo['taxes'] as $taxId => $taxPercent) {
				$amount = $calculatedOn = $chargeInfo['value'];

				if ($shippingTaxes[$taxId]['method'] === 'Compound') {
					$compoundTaxes = Zend_Json::decode(html_entity_decode($shippingTaxes[$taxId]['compoundon']));
					if (is_array($compoundTaxes)) {
						foreach ($compoundTaxes as $comTaxId) {
							if ($shippingTaxes[$comTaxId]) {
								$calculatedOn += ((float)$amount * (float)$chargeInfo['taxes'][$comTaxId]) / 100;
							}
						}
					}
				}
				$totalAmount += ((float)$calculatedOn * (float)$taxPercent) / 100;
			}
		}
		$relatedProducts[1]['final_details']['shtax_totalamount'] = number_format($totalAmount, $numOfCurrencyDecimalPlaces, '.', '');

		//deduct tax values setting to related products
		$totalAfterDiscount = (float) $relatedProducts[1]['final_details']['totalAfterDiscount'];
		$deductedTaxesTotalAmount = 0.00;

		$deductTaxes = $this->getDeductTaxes();
		foreach ($deductTaxes as $taxId => $taxInfo) {
			$taxAmount = ($totalAfterDiscount * (float)$taxInfo['percentage']) / 100;
			$deductTaxes[$taxId]['amount'] = number_format($taxAmount, $numOfCurrencyDecimalPlaces,'.','');
			if ($taxInfo['selected']) {
				$deductedTaxesTotalAmount = $deductedTaxesTotalAmount + $taxAmount;
			}
		}

		$relatedProducts[1]['final_details']['deductTaxes'] = $deductTaxes;
		$relatedProducts[1]['final_details']['deductTaxesTotalAmount'] = number_format($deductedTaxesTotalAmount, $numOfCurrencyDecimalPlaces,'.','');

		if ($productIdsList) {
			$imageDetailsList = Products_Record_Model::getProductsImageDetails($productIdsList);

			for ($i=1; $i<=$productsCount; $i++) {
				$product = $relatedProducts[$i];
				$productId = $product['hdnProductId'.$i];
				$imageDetails = $imageDetailsList[$productId];
				if ($imageDetails) {
					$relatedProducts[$i]['productImage'.$i] = $imageDetails[0]['url'];
				}
			}
		}

		return $relatedProducts;
	}

	function getAssociatedProductsAnother2($module, $focus, $seid = '', $refModuleName = false) {
		global $log;
		global $adb;
	
		$no_of_decimal_places = getCurrencyDecimalPlaces();
		$product_Detail = array();
	
		$inventoryModules = getInventoryModules();
		if (in_array($module, $inventoryModules)) {
			$taxtype = getInventoryTaxType($module, $focus->id);
		}
	
		$additionalProductFieldsString = $additionalServiceFieldsString = '';
		$lineItemSupportedModules = array('Accounts', 'Contacts', 'Leads', 'Potentials');
	
		$fieldNames = [];
		if ($module == 'ServiceReports') {
			$tabId = getTabId($module);
			$sql = "SELECT * FROM `vtiger_field` LEFT JOIN vtiger_blocks
			on vtiger_blocks.blockid = vtiger_field.block where vtiger_field.tabid = ? 
			and helpinfo = 'li_lg' and blocklabel = ? and presence != 1 ORDER BY `vtiger_field`.`sequence` ASC;";
			$result = $adb->pquery($sql, array($tabId, 'Major_Aggregates_Sl_No'));
			while ($row = $adb->fetch_array($result)) {
				array_push($fieldNames, $row['fieldname']);
			}
		}
		if (in_array($module, $inventoryModules)) {
			$query = "SELECT
						case when vtiger_products.productid != '' then vtiger_products.productname else vtiger_service.servicename end as productname,
						case when vtiger_products.productid != '' then vtiger_products.product_no else vtiger_service.service_no end as productcode,
						case when vtiger_products.productid != '' then vtiger_products.unit_price else vtiger_service.unit_price end as unit_price,
						case when vtiger_products.productid != '' then vtiger_products.qtyinstock else 'NA' end as qtyinstock,
						case when vtiger_products.productid != '' then 'Products' else 'Services' end as entitytype,
						vtiger_products.is_subproducts_viewable, 
						vtiger_inventoryproductrel_other_masn.*,
						vtiger_crmentity.deleted FROM vtiger_inventoryproductrel_other_masn
						LEFT JOIN vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_inventoryproductrel_other_masn.productid
						LEFT JOIN vtiger_products ON vtiger_products.productid=vtiger_inventoryproductrel_other_masn.productid
						LEFT JOIN vtiger_service ON vtiger_service.serviceid=vtiger_inventoryproductrel_other_masn.productid
						WHERE id=? ORDER BY sequence_no";
			$params = array($focus->id);
		} elseif (in_array($module, $lineItemSupportedModules)) {
			$query = '(SELECT vtiger_products.productid, vtiger_products.productname, vtiger_products.product_no AS productcode, vtiger_products.purchase_cost,
						vtiger_products.unit_price, vtiger_products.qtyinstock, vtiger_crmentity.deleted, "Products" AS entitytype,
						vtiger_products.is_subproducts_viewable, vtiger_crmentity.description ' . $additionalProductFieldsString . ' FROM vtiger_products
						INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_products.productid
						INNER JOIN vtiger_seproductsrel ON vtiger_seproductsrel.productid=vtiger_products.productid
						INNER JOIN vtiger_productcf ON vtiger_products.productid = vtiger_productcf.productid
						WHERE vtiger_seproductsrel.crmid=? AND vtiger_crmentity.deleted=0 AND vtiger_products.discontinued=1)
						UNION
						(SELECT vtiger_service.serviceid AS productid, vtiger_service.servicename AS productname, vtiger_service.service_no AS productcode,
						vtiger_service.purchase_cost, vtiger_service.unit_price, "NA" as qtyinstock, vtiger_crmentity.deleted,
						"Services" AS entitytype, 1 AS is_subproducts_viewable, vtiger_crmentity.description ' . $additionalServiceFieldsString . ' FROM vtiger_service
						INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_service.serviceid
						INNER JOIN vtiger_crmentityrel ON vtiger_crmentityrel.relcrmid = vtiger_service.serviceid
						INNER JOIN vtiger_servicecf ON vtiger_service.serviceid = vtiger_servicecf.serviceid
						WHERE vtiger_crmentityrel.crmid=? AND vtiger_crmentity.deleted=0 AND vtiger_service.discontinued=1)';
			$params = array($seid, $seid);
		} elseif ($module == 'Vendors') {
			$query = 'SELECT vtiger_products.productid, vtiger_products.productname, vtiger_products.product_no AS productcode, vtiger_products.purchase_cost,
						vtiger_products.unit_price, vtiger_products.qtyinstock, vtiger_crmentity.deleted, "Products" AS entitytype,
						vtiger_products.is_subproducts_viewable, vtiger_crmentity.description ' . $additionalServiceFieldsString . ' FROM vtiger_products
						INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_products.productid
						INNER JOIN vtiger_vendor ON vtiger_vendor.vendorid = vtiger_products.vendor_id
						INNER JOIN vtiger_productcf ON vtiger_products.productid = vtiger_productcf.productid
						WHERE vtiger_vendor.vendorid=? AND vtiger_crmentity.deleted=0 AND vtiger_products.discontinued=1';
			$params = array($seid);
		} elseif ($module == 'HelpDesk') {
			$query = 'SELECT vtiger_service.serviceid AS productid, vtiger_service.servicename AS productname, vtiger_service.service_no AS productcode,
						vtiger_service.purchase_cost, vtiger_service.unit_price, "NA" as qtyinstock, vtiger_crmentity.deleted,
						"Services" AS entitytype, 1 AS is_subproducts_viewable, vtiger_crmentity.description ' . $additionalServiceFieldsString . ' FROM vtiger_service
						INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_service.serviceid
						INNER JOIN vtiger_crmentityrel ON vtiger_crmentityrel.relcrmid = vtiger_service.serviceid
						INNER JOIN vtiger_servicecf ON vtiger_service.serviceid = vtiger_servicecf.serviceid
						WHERE vtiger_crmentityrel.crmid=? AND vtiger_crmentity.deleted=0 AND vtiger_service.discontinued=1';
			$params = array($seid);
		} elseif ($module == 'Products') {
			$query = 'SELECT vtiger_products.productid, vtiger_products.productname, vtiger_products.product_no AS productcode, vtiger_products.purchase_cost,
						vtiger_products.unit_price, vtiger_products.qtyinstock, vtiger_crmentity.deleted, "Products" AS entitytype,
						vtiger_products.is_subproducts_viewable, vtiger_crmentity.description ' . $additionalProductFieldsString . ' FROM vtiger_products
						INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_products.productid
						INNER JOIN vtiger_productcf ON vtiger_products.productid = vtiger_productcf.productid
						WHERE vtiger_crmentity.deleted=0 AND vtiger_products.productid=?';
			$params = array($seid);
		} elseif ($module == 'Services') {
			$query = 'SELECT vtiger_service.serviceid AS productid, vtiger_service.servicename AS productname, vtiger_service.service_no AS productcode,
						vtiger_service.purchase_cost, vtiger_service.unit_price AS unit_price, "NA" AS qtyinstock, vtiger_crmentity.deleted,
						"Services" AS entitytype, 1 AS is_subproducts_viewable, vtiger_crmentity.description ' . $additionalServiceFieldsString . ' FROM vtiger_service
						INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_service.serviceid
						INNER JOIN vtiger_servicecf ON vtiger_service.serviceid = vtiger_servicecf.serviceid
						WHERE vtiger_crmentity.deleted=0 AND vtiger_service.serviceid=?';
			$params = array($seid);
		}
	
		$result = $adb->pquery($query, $params);
		$num_rows = $adb->num_rows($result);
		if ($num_rows == 0) {
			return [];
		}
		for ($i = 1; $i <= $num_rows; $i++) {
			$deleted = $adb->query_result($result, $i - 1, 'deleted');
			$hdnProductId = $adb->query_result($result, $i - 1, 'productid');
			$hdnProductcode = $adb->query_result($result, $i - 1, 'productcode');
			$productname = $adb->query_result($result, $i - 1, 'productname');
			$productdescription = $adb->query_result($result, $i - 1, 'description');
			$comment = $adb->query_result($result, $i - 1, 'comment');
			$dependent = $adb->query_result($result, $i - 1, 'dependency');
			$qtyinstock = $adb->query_result($result, $i - 1, 'qtyinstock');
			$qty = $adb->query_result($result, $i - 1, 'quantity');
			$unitprice = $adb->query_result($result, $i - 1, 'unit_price');
			$listprice = $adb->query_result($result, $i - 1, 'listprice');
			$entitytype = $adb->query_result($result, $i - 1, 'entitytype');
			$purchaseCost = $adb->query_result($result, $i - 1, 'purchase_cost');
			$margin = $adb->query_result($result, $i - 1, 'margin');
			$isSubProductsViewable = $adb->query_result($result, $i - 1, 'is_subproducts_viewable');
	
			if ($purchaseCost) {
				$product_Detail[$i]['purchaseCost' . $i] = number_format($purchaseCost, $no_of_decimal_places, '.', '');
			}
	
			if ($margin) {
				$product_Detail[$i]['margin' . $i] = number_format($margin, $no_of_decimal_places, '.', '');
			}
	
			if (($deleted) || (!isset($deleted))) {
				$product_Detail[$i]['productDeleted' . $i] = true;
			} elseif (!$deleted) {
				$product_Detail[$i]['productDeleted' . $i] = false;
			}
	
			if (!empty($entitytype)) {
				$product_Detail[$i]['entityType' . $i] = $entitytype;
			}
	
			if ($listprice == '')
			$listprice = $unitprice;
			if ($qty == '')
			$qty = 1;
	
			//calculate productTotal
			$productTotal = $qty * $listprice;
	
			//Delete link in First column
			if ($i != 1) {
				$product_Detail[$i]['delRow' . $i] = "Del";
			}
	
			if (in_array($module, $lineItemSupportedModules) || $module === 'Vendors' || (!$focus->mode && $seid)) {
				$subProductsQuery = 'SELECT vtiger_seproductsrel.crmid AS prod_id, quantity FROM vtiger_seproductsrel
									 INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_seproductsrel.crmid
									 INNER JOIN vtiger_products ON vtiger_products.productid = vtiger_seproductsrel.crmid
									 WHERE vtiger_seproductsrel.productid=? AND vtiger_seproductsrel.setype=? AND vtiger_products.discontinued=1';
	
				$subParams = array($seid);
				if (in_array($module, $lineItemSupportedModules) || $module === 'Vendors') {
					$subParams = array($hdnProductId);
				}
				array_push($subParams, 'Products');
			} else {
				$subProductsQuery = 'SELECT productid AS prod_id, quantity FROM vtiger_inventorysubproductrel WHERE id=? AND sequence_no=?';
				$subParams = array($focus->id, $i);
			}
			$subProductsResult = $adb->pquery($subProductsQuery, $subParams);
			$subProductsCount = $adb->num_rows($subProductsResult);
	
			$subprodid_str = '';
			$subprodname_str = '';
	
			$subProductQtyList = array();
			for ($j = 0; $j < $subProductsCount; $j++) {
				$sprod_id = $adb->query_result($subProductsResult, $j, 'prod_id');
				$sprod_name = getProductName($sprod_id);
				if (isset($sprod_name)) {
					$subQty = $adb->query_result($subProductsResult, $j, 'quantity');
					$subProductQtyList[$sprod_id] = array('name' => $sprod_name, 'qty' => $subQty);
					if (isRecordExists($sprod_id) && $_REQUEST['view'] === 'Detail') {
						$subprodname_str .= "<a href='index.php?module=Products&view=Detail&record=$sprod_id' target='_blank'> <em> - $sprod_name ($subQty)</em><br></a>";
					} else {
						$subprodname_str .= "<em> - $sprod_name ($subQty)</em><br>";
					}
					$subprodid_str .= "$sprod_id:$subQty,";
				}
			}
			$subprodid_str = rtrim($subprodid_str, ',');
	
			$product_Detail[$i]['hdnProductId_other' . $i] = $hdnProductId;
			$product_Detail[$i]['productName_other' . $i] = from_html($productname);
	
			if ($module == 'FailedParts' || $module == 'ServiceReports') {
				foreach ($fieldNames as $fieldName) {
					$product_Detail[$i][$fieldName . $i] = from_html($adb->query_result($result, $i - 1, $fieldName));
				}
			}
	
			/* Added to fix the issue Product Pop-up name display*/
			if ($_REQUEST['action'] == 'CreateSOPDF' || $_REQUEST['action'] == 'CreatePDF' || $_REQUEST['action'] == 'SendPDFMail')
			$product_Detail[$i]['productName' . $i] = htmlspecialchars($product_Detail[$i]['productName' . $i]);
			$product_Detail[$i]['hdnProductcode' . $i] = $hdnProductcode;
			$product_Detail[$i]['dependent' . $i] = from_html($dependent);
			$product_Detail[$i]['productDescription' . $i] = from_html($productdescription);
			if ($module == 'Vendors' || $module == 'Products' || $module == 'Services' || in_array($module, $lineItemSupportedModules)) {
				$product_Detail[$i]['comment' . $i] = $productdescription;
			} else {
				$product_Detail[$i]['comment' . $i] = $comment;
			}
	
			if ($module != 'PurchaseOrder' && $focus->object_name != 'Order') {
				$product_Detail[$i]['qtyInStock' . $i] = decimalFormat($qtyinstock);
			}
			$listprice = number_format($listprice, $no_of_decimal_places, '.', '');
			$product_Detail[$i]['qty_other' . $i] = decimalFormat($qty);
			$product_Detail[$i]['listPrice' . $i] = $listprice;
			$product_Detail[$i]['unitPrice' . $i] = number_format($unitprice, $no_of_decimal_places, '.', '');
			$product_Detail[$i]['productTotal' . $i] = number_format($productTotal, $no_of_decimal_places, '.', '');
			$product_Detail[$i]['subproduct_ids' . $i] = $subprodid_str;
			if ($isSubProductsViewable) {
				$product_Detail[$i]['subprod_qty_list' . $i] = $subProductQtyList;
				$product_Detail[$i]['subprod_names' . $i] = $subprodname_str;
			}
			$discount_percent = decimalFormat($adb->query_result($result, $i - 1, 'discount_percent'));
			$discount_amount = $adb->query_result($result, $i - 1, 'discount_amount');
			$discount_amount = decimalFormat(number_format($discount_amount, $no_of_decimal_places, '.', ''));
			$discountTotal = 0;
			//Based on the discount percent or amount we will show the discount details
	
			//To avoid NaN javascript error, here we assign 0 initially to' %of price' and 'Direct Price reduction'(for Each Product)
			$product_Detail[$i]['discount_percent' . $i] = 0;
			$product_Detail[$i]['discount_amount' . $i] = 0;
	
			if (!empty($discount_percent)) {
				$product_Detail[$i]['discount_type' . $i] = "percentage";
				$product_Detail[$i]['discount_percent' . $i] = $discount_percent;
				$product_Detail[$i]['checked_discount_percent' . $i] = ' checked';
				$product_Detail[$i]['style_discount_percent' . $i] = ' style="visibility:visible"';
				$product_Detail[$i]['style_discount_amount' . $i] = ' style="visibility:hidden"';
				$discountTotal = $productTotal * $discount_percent / 100;
			} elseif (!empty($discount_amount)) {
				$product_Detail[$i]['discount_type' . $i] = "amount";
				$product_Detail[$i]['discount_amount' . $i] = $discount_amount;
				$product_Detail[$i]['checked_discount_amount' . $i] = ' checked';
				$product_Detail[$i]['style_discount_amount' . $i] = ' style="visibility:visible"';
				$product_Detail[$i]['style_discount_percent' . $i] = ' style="visibility:hidden"';
				$discountTotal = $discount_amount;
			} else {
				$product_Detail[$i]['checked_discount_zero' . $i] = ' checked';
			}
			$totalAfterDiscount = $productTotal - $discountTotal;
			$totalAfterDiscount = number_format($totalAfterDiscount, $no_of_decimal_places, '.', '');
			$discountTotal = number_format($discountTotal, $no_of_decimal_places, '.', '');
			$product_Detail[$i]['discountTotal' . $i] = $discountTotal;
			$product_Detail[$i]['totalAfterDiscount' . $i] = $totalAfterDiscount;
	
			$taxTotal = 0;
			$taxTotal = number_format($taxTotal, $no_of_decimal_places, '.', '');
			$product_Detail[$i]['taxTotal' . $i] = $taxTotal;
	
			//Calculate netprice
			$netPrice = $totalAfterDiscount + $taxTotal;
			//if condition is added to call this function when we create PO/SO/Quotes/Invoice from Product module
			if (in_array($module, $inventoryModules)) {
				if ($taxtype == 'individual') {
					//Add the tax with product total and assign to netprice
					$netPrice = $netPrice + $taxTotal;
				}
			}
			$product_Detail[$i]['netPrice' . $i] = number_format($netPrice, getCurrencyDecimalPlaces(), '.', '');
	
			//First we will get all associated taxes as array
			$tax_details = getTaxDetailsForProduct($hdnProductId, 'all');
			$regionsList = array();
			foreach ($tax_details as $taxInfo) {
				$regionsInfo = array('default' => $taxInfo['percentage']);
				foreach ($taxInfo['productregions'] as $list) {
					if (is_array($list['list'])) {
						foreach (array_fill_keys($list['list'], $list['value']) as $key => $value) {
							$regionsInfo[$key] = $value;
						}
					}
				}
				$regionsList[$taxInfo['taxid']] = $regionsInfo;
			}
			//Now retrieve the tax values from the current query with the name
			for ($tax_count = 0; $tax_count < count($tax_details); $tax_count++) {
				$tax_name = $tax_details[$tax_count]['taxname'];
				$tax_label = $tax_details[$tax_count]['taxlabel'];
				$tax_value = 0;
	
				$tax_value = $tax_details[$tax_count]['percentage'];
				if ($focus->id != '' && $taxtype == 'individual') {
					$lineItemId = $adb->query_result($result, $i - 1, 'lineitem_id');
					$tax_value = getInventoryProductTaxValue($focus->id, $hdnProductId, $tax_name, $lineItemId);
					$selectedRegionId = $focus->column_fields['region_id'];
					if ($selectedRegionId) {
						$regionsList[$tax_details[$tax_count]['taxid']][$selectedRegionId] = $tax_value;
					} else {
						$regionsList[$tax_details[$tax_count]['taxid']]['default'] = $tax_value;
					}
				}
	
				$product_Detail[$i]['taxes'][$tax_count]['taxname']		= $tax_name;
				$product_Detail[$i]['taxes'][$tax_count]['taxlabel']	= $tax_label;
				$product_Detail[$i]['taxes'][$tax_count]['percentage']	= $tax_value;
				$product_Detail[$i]['taxes'][$tax_count]['deleted']		= $tax_details[$tax_count]['deleted'];
				$product_Detail[$i]['taxes'][$tax_count]['taxid']		= $tax_details[$tax_count]['taxid'];
				$product_Detail[$i]['taxes'][$tax_count]['type']		= $tax_details[$tax_count]['type'];
				$product_Detail[$i]['taxes'][$tax_count]['method']		= $tax_details[$tax_count]['method'];
				$product_Detail[$i]['taxes'][$tax_count]['regions']		= $tax_details[$tax_count]['regions'];
				$product_Detail[$i]['taxes'][$tax_count]['compoundon']	= $tax_details[$tax_count]['compoundon'];
				$product_Detail[$i]['taxes'][$tax_count]['regionsList']	= $regionsList[$tax_details[$tax_count]['taxid']];
			}
		}
	
		//set the taxtype
		$product_Detail[1]['final_details']['taxtype'] = $taxtype;
	
		//Get the Final Discount, S&H charge, Tax for S&H and Adjustment values
		//To set the Final Discount details
		$finalDiscount = 0;
		$product_Detail[1]['final_details']['discount_type_final'] = 'zero';
	
		$subTotal = ($focus->column_fields['hdnSubTotal'] != '') ? $focus->column_fields['hdnSubTotal'] : 0;
		$subTotal = number_format($subTotal, $no_of_decimal_places, '.', '');
	
		$product_Detail[1]['final_details']['hdnSubTotal'] = $subTotal;
		$discountPercent = ($focus->column_fields['hdnDiscountPercent'] != '') ? $focus->column_fields['hdnDiscountPercent'] : 0;
		$discountAmount = ($focus->column_fields['hdnDiscountAmount'] != '') ? $focus->column_fields['hdnDiscountAmount'] : 0;
		if ($discountPercent != '0') {
			$discountAmount = ($product_Detail[1]['final_details']['hdnSubTotal'] * $discountPercent / 100);
		}
	
		//To avoid NaN javascript error, here we assign 0 initially to' %of price' and 'Direct Price reduction'(For Final Discount)
		$discount_amount_final = 0;
		$discount_amount_final = number_format($discount_amount_final, $no_of_decimal_places, '.', '');
		$product_Detail[1]['final_details']['discount_percentage_final'] = 0;
		$product_Detail[1]['final_details']['discount_amount_final'] = $discount_amount_final;
	
		$hdnDiscountPercent = (float) $focus->column_fields['hdnDiscountPercent'];
		$hdnDiscountAmount	= (float) $focus->column_fields['hdnDiscountAmount'];
	
		if (!empty($hdnDiscountPercent)) {
			$finalDiscount = ($subTotal * $discountPercent / 100);
			$product_Detail[1]['final_details']['discount_type_final'] = 'percentage';
			$product_Detail[1]['final_details']['discount_percentage_final'] = $discountPercent;
			$product_Detail[1]['final_details']['checked_discount_percentage_final'] = ' checked';
			$product_Detail[1]['final_details']['style_discount_percentage_final'] = ' style="visibility:visible"';
			$product_Detail[1]['final_details']['style_discount_amount_final'] = ' style="visibility:hidden"';
		} elseif (!empty($hdnDiscountAmount)) {
			$finalDiscount = $focus->column_fields['hdnDiscountAmount'];
			$product_Detail[1]['final_details']['discount_type_final'] = 'amount';
			$product_Detail[1]['final_details']['discount_amount_final'] = $discountAmount;
			$product_Detail[1]['final_details']['checked_discount_amount_final'] = ' checked';
			$product_Detail[1]['final_details']['style_discount_amount_final'] = ' style="visibility:visible"';
			$product_Detail[1]['final_details']['style_discount_percentage_final'] = ' style="visibility:hidden"';
		}
		$finalDiscount = number_format($finalDiscount, $no_of_decimal_places, '.', '');
		$product_Detail[1]['final_details']['discountTotal_final'] = $finalDiscount;
	
		//To set the Final Tax values
		//we will get all taxes. if individual then show the product related taxes only else show all taxes
		//suppose user want to change individual to group or vice versa in edit time the we have to show all taxes. so that here we will store all the taxes and based on need we will show the corresponding taxes
	
		//First we should get all available taxes and then retrieve the corresponding tax values
		$tax_details = getAllTaxes('available', '', 'edit', $focus->id);
		$taxDetails = array();
	
		for ($tax_count = 0; $tax_count < count($tax_details); $tax_count++) {
			if ($tax_details[$tax_count]['method'] === 'Deducted') {
				continue;
			}
	
			$tax_name = $tax_details[$tax_count]['taxname'];
			$tax_label = $tax_details[$tax_count]['taxlabel'];
	
			//if taxtype is individual and want to change to group during edit time then we have to show the all available taxes and their default values
			//Also taxtype is group and want to change to individual during edit time then we have to provide the asspciated taxes and their default tax values for individual products
			if ($taxtype == 'group')
			$tax_percent = $adb->query_result($result, 0, $tax_name);
			else
				$tax_percent = $tax_details[$tax_count]['percentage']; //$adb->query_result($result,0,$tax_name);
	
			if ($tax_percent == '' || $tax_percent == 'NULL')
			$tax_percent = 0;
			$taxamount = ($subTotal - $finalDiscount) * $tax_percent / 100;
			list($before_dot, $after_dot) = explode('.', $taxamount);
			if ($after_dot[$no_of_decimal_places] == 5) {
				$taxamount = round($taxamount, $no_of_decimal_places, PHP_ROUND_HALF_DOWN);
			} else {
				$taxamount = number_format($taxamount, $no_of_decimal_places, '.', '');
			}
	
			$taxId = $tax_details[$tax_count]['taxid'];
			$taxDetails[$taxId]['taxname']		= $tax_name;
			$taxDetails[$taxId]['taxlabel']		= $tax_label;
			$taxDetails[$taxId]['percentage']	= $tax_percent;
			$taxDetails[$taxId]['amount']		= $taxamount;
			$taxDetails[$taxId]['taxid']		= $taxId;
			$taxDetails[$taxId]['type']			= $tax_details[$tax_count]['type'];
			$taxDetails[$taxId]['method']		= $tax_details[$tax_count]['method'];
			$taxDetails[$taxId]['regions']		= Zend_Json::decode(html_entity_decode($tax_details[$tax_count]['regions']));
			$taxDetails[$taxId]['compoundon']	= Zend_Json::decode(html_entity_decode($tax_details[$tax_count]['compoundon']));
		}
	
		$compoundTaxesInfo = getCompoundTaxesInfoForInventoryRecord($focus->id, $module);
		//Calculating compound info
		$taxTotal = 0;
		foreach ($taxDetails as $taxId => $taxInfo) {
			$compoundOn = $taxInfo['compoundon'];
			if ($compoundOn) {
				$existingCompounds = $compoundTaxesInfo[$taxId];
				if (!is_array($existingCompounds)) {
					$existingCompounds = array();
				}
				$compoundOn = array_unique(array_merge($existingCompounds, $compoundOn));
				$taxDetails[$taxId]['compoundon'] = $compoundOn;
	
				$amount = $subTotal - $finalDiscount;
				foreach ($compoundOn as $id) {
					$amount = (float)$amount + (float)$taxDetails[$id]['amount'];
				}
				$taxAmount = ((float)$amount * (float)$taxInfo['percentage']) / 100;
				list($beforeDot, $afterDot) = explode('.', $taxAmount);
	
				if ($afterDot[$no_of_decimal_places] == 5) {
					$taxAmount = round($taxAmount, $no_of_decimal_places, PHP_ROUND_HALF_DOWN);
				} else {
					$taxAmount = number_format($taxAmount, $no_of_decimal_places, '.', '');
				}
	
				$taxDetails[$taxId]['amount'] = $taxAmount;
			}
			$taxTotal = $taxTotal + $taxDetails[$taxId]['amount'];
		}
		$product_Detail[1]['final_details']['taxes'] = $taxDetails;
		$product_Detail[1]['final_details']['tax_totalamount'] = number_format($taxTotal, $no_of_decimal_places, '.', '');
	
		//To set the Shipping & Handling charge
		$shCharge = ($focus->column_fields['hdnS_H_Amount'] != '') ? $focus->column_fields['hdnS_H_Amount'] : 0;
		$shCharge = number_format($shCharge, $no_of_decimal_places, '.', '');
		$product_Detail[1]['final_details']['shipping_handling_charge'] = $shCharge;
	
		//To set the Shipping & Handling tax values
		//calculate S&H tax
		$shtaxtotal = 0;
		//First we should get all available taxes and then retrieve the corresponding tax values
		$shtax_details = getAllTaxes('available', 'sh', 'edit', $focus->id);
	
		//if taxtype is group then the tax should be same for all products in vtiger_inventoryproductrel table
		for ($shtax_count = 0; $shtax_count < count($shtax_details); $shtax_count++) {
			$shtax_name = $shtax_details[$shtax_count]['taxname'];
			$shtax_label = $shtax_details[$shtax_count]['taxlabel'];
			$shtax_percent = 0;
			//if condition is added to call this function when we create PO/SO/Quotes/Invoice from Product module
			if (in_array($module, $inventoryModules)) {
				$shtax_percent = getInventorySHTaxPercent($focus->id, $shtax_name);
			}
			$shtaxamount = $shCharge * $shtax_percent / 100;
			$shtaxtotal = $shtaxtotal + $shtaxamount;
			$product_Detail[1]['final_details']['sh_taxes'][$shtax_count]['taxname']	= $shtax_name;
			$product_Detail[1]['final_details']['sh_taxes'][$shtax_count]['taxlabel']	= $shtax_label;
			$product_Detail[1]['final_details']['sh_taxes'][$shtax_count]['percentage']	= $shtax_percent;
			$product_Detail[1]['final_details']['sh_taxes'][$shtax_count]['amount']		= $shtaxamount;
			$product_Detail[1]['final_details']['sh_taxes'][$shtax_count]['taxid']		= $shtax_details[$shtax_count]['taxid'];
			$product_Detail[1]['final_details']['sh_taxes'][$shtax_count]['type']		= $shtax_details[$shtax_count]['type'];
			$product_Detail[1]['final_details']['sh_taxes'][$shtax_count]['method']		= $shtax_details[$shtax_count]['method'];
			$product_Detail[1]['final_details']['sh_taxes'][$shtax_count]['regions']	= Zend_Json::decode(html_entity_decode($shtax_details[$shtax_count]['regions']));
			$product_Detail[1]['final_details']['sh_taxes'][$shtax_count]['compoundon']	= Zend_Json::decode(html_entity_decode($shtax_details[$shtax_count]['compoundon']));
		}
		$shtaxtotal = number_format($shtaxtotal, $no_of_decimal_places, '.', '');
		$product_Detail[1]['final_details']['shtax_totalamount'] = $shtaxtotal;
	
		//To set the Adjustment value
		$adjustment = ($focus->column_fields['txtAdjustment'] != '') ? $focus->column_fields['txtAdjustment'] : 0;
		$adjustment = number_format($adjustment, $no_of_decimal_places, '.', '');
		$product_Detail[1]['final_details']['adjustment'] = $adjustment;
	
		//To set the grand total
		$grandTotal = ($focus->column_fields['hdnGrandTotal'] != '') ? $focus->column_fields['hdnGrandTotal'] : 0;
		$grandTotal = number_format($grandTotal, $no_of_decimal_places, '.', '');
		$product_Detail[1]['final_details']['grandTotal'] = $grandTotal;
	
		$log->debug("Exiting getAssociatedProducts method ...");
	
		return $product_Detail;
	}

	function getProductsOther2() {
		$numOfCurrencyDecimalPlaces = getCurrencyDecimalPlaces();
		$relatedProducts = $this->getAssociatedProductsAnother2($this->getModuleName(), $this->getEntity());
		$productsCount = count($relatedProducts);
		if ($productsCount == 0) {
			return [];
		}
		$taxtype = $relatedProducts[1]['final_details']['taxtype'];
		$productIdsList = array();
		for ($i=1;$i<=$productsCount; $i++) {
			$product = $relatedProducts[$i];
			$productId = $product['hdnProductId'.$i];
			$totalAfterDiscount = $product['totalAfterDiscount'.$i];

			if ($taxtype == 'individual') {
				$taxDetails = getTaxDetailsForProduct($productId, 'all');
				$taxCount = count($taxDetails);
				$taxTotal = '0';

				for($j=0; $j<$taxCount; $j++) {
					$taxValue = $product['taxes'][$j]['percentage'];

					$taxAmount = $totalAfterDiscount * $taxValue / 100;
					$taxTotal = $taxTotal + $taxAmount;

					$product['taxes'][$j]['amount'] = $taxAmount;
					$relatedProducts[$i]['taxes'][$j]['amount'] = $taxAmount;
				}

				$productTaxes = array();
				if ($product['taxes']) {
					foreach ($product['taxes'] as $key => $taxInfo) {
						$taxInfo['key'] = $key;
						$productTaxes[$taxInfo['taxid']] = $taxInfo;
					}
				}

				$taxTotal = 0.00;
				foreach ($productTaxes as $taxId => $taxInfo) {
					$taxAmount = $taxInfo['amount'];
					if ($taxInfo['compoundon']) {
						$amount = $totalAfterDiscount;
						foreach ($taxInfo['compoundon'] as $compTaxId) {
							$amount = $amount + $productTaxes[$compTaxId]['amount'];
						}
						$taxAmount = $amount * $taxInfo['percentage'] / 100;
					}
					$taxTotal = $taxTotal + $taxAmount;

					$relatedProducts[$i]['taxes'][$taxInfo['key']]['amount'] = $taxAmount;
					$relatedProducts[$i]['taxTotal'.$i]	= number_format($taxTotal, $numOfCurrencyDecimalPlaces, '.', '');
				}
				$netPrice = $totalAfterDiscount + $taxTotal;
				$relatedProducts[$i]['netPrice'.$i] = number_format($netPrice, $numOfCurrencyDecimalPlaces, '.', '');
			}

			if ($relatedProducts[$i]['entityType'.$i] == 'Products') {
				$productIdsList[] = $productId;
			}
			$relatedProducts[$i]['picklistValuesConfigured'.$i] = json_decode(decode_html(IGGetDependentValuesOfPickList($product['masn_aggrregate'.$i], 'masn_manu')));
		}

		//Updating Pre tax total
		$preTaxTotal = (float)$relatedProducts[1]['final_details']['hdnSubTotal']
						+ (float)$relatedProducts[1]['final_details']['shipping_handling_charge']
						- (float)$relatedProducts[1]['final_details']['discountTotal_final'];

		$relatedProducts[1]['final_details']['preTaxTotal'] = number_format($preTaxTotal, $numOfCurrencyDecimalPlaces,'.','');
		
		//Updating Total After Discount
		$totalAfterDiscount = (float)$relatedProducts[1]['final_details']['hdnSubTotal'] - (float)$relatedProducts[1]['final_details']['discountTotal_final'];

		$relatedProducts[1]['final_details']['totalAfterDiscount'] = number_format($totalAfterDiscount, $numOfCurrencyDecimalPlaces,'.','');
		$relatedProducts[1]['final_details']['discount_amount_final'] = number_format((float)$relatedProducts[1]['final_details']['discount_amount_final'], $numOfCurrencyDecimalPlaces,'.','');

		//charge value setting to related products array
		$selectedChargesAndItsTaxes = $this->getCharges();
		if (!$selectedChargesAndItsTaxes) {
			$selectedChargesAndItsTaxes = array();
		}
		$relatedProducts[1]['final_details']['chargesAndItsTaxes'] = $selectedChargesAndItsTaxes;

		$allChargeTaxes = array();
		foreach ($selectedChargesAndItsTaxes as $chargeId => $chargeInfo) {
			if (is_array($chargeInfo['taxes'])) {
				$allChargeTaxes = array_merge($allChargeTaxes, array_keys($chargeInfo['taxes']));
			} else {
				$selectedChargesAndItsTaxes[$chargeId]['taxes'] = array();
			}
		}

		$shippingTaxes = array();
		$allShippingTaxes = getAllTaxes('all', 'sh');
		foreach ($allShippingTaxes as $shTaxInfo) {
			$shippingTaxes[$shTaxInfo['taxid']] = $shTaxInfo;
		}

		$totalAmount = 0.00;
		foreach ($selectedChargesAndItsTaxes as $chargeId => $chargeInfo) {
			foreach ($chargeInfo['taxes'] as $taxId => $taxPercent) {
				$amount = $calculatedOn = $chargeInfo['value'];

				if ($shippingTaxes[$taxId]['method'] === 'Compound') {
					$compoundTaxes = Zend_Json::decode(html_entity_decode($shippingTaxes[$taxId]['compoundon']));
					if (is_array($compoundTaxes)) {
						foreach ($compoundTaxes as $comTaxId) {
							if ($shippingTaxes[$comTaxId]) {
								$calculatedOn += ((float)$amount * (float)$chargeInfo['taxes'][$comTaxId]) / 100;
							}
						}
					}
				}
				$totalAmount += ((float)$calculatedOn * (float)$taxPercent) / 100;
			}
		}
		$relatedProducts[1]['final_details']['shtax_totalamount'] = number_format($totalAmount, $numOfCurrencyDecimalPlaces, '.', '');

		//deduct tax values setting to related products
		$totalAfterDiscount = (float) $relatedProducts[1]['final_details']['totalAfterDiscount'];
		$deductedTaxesTotalAmount = 0.00;

		$deductTaxes = $this->getDeductTaxes();
		foreach ($deductTaxes as $taxId => $taxInfo) {
			$taxAmount = ($totalAfterDiscount * (float)$taxInfo['percentage']) / 100;
			$deductTaxes[$taxId]['amount'] = number_format($taxAmount, $numOfCurrencyDecimalPlaces,'.','');
			if ($taxInfo['selected']) {
				$deductedTaxesTotalAmount = $deductedTaxesTotalAmount + $taxAmount;
			}
		}

		$relatedProducts[1]['final_details']['deductTaxes'] = $deductTaxes;
		$relatedProducts[1]['final_details']['deductTaxesTotalAmount'] = number_format($deductedTaxesTotalAmount, $numOfCurrencyDecimalPlaces,'.','');

		if ($productIdsList) {
			$imageDetailsList = Products_Record_Model::getProductsImageDetails($productIdsList);

			for ($i=1; $i<=$productsCount; $i++) {
				$product = $relatedProducts[$i];
				$productId = $product['hdnProductId'.$i];
				$imageDetails = $imageDetailsList[$productId];
				if ($imageDetails) {
					$relatedProducts[$i]['productImage'.$i] = $imageDetails[0]['url'];
				}
			}
		}

		return $relatedProducts;
	}

	/**
	 * Function to set record module field values
	 * @param parent record model
	 * @return <Model> returns Vtiger_Record_Model
	 */
	function setRecordFieldValues($parentRecordModel) {
		$currentUser = Users_Record_Model::getCurrentUserModel();

		$fieldsList = array_keys($this->getModule()->getFields());
		$parentFieldsList = array_keys($parentRecordModel->getModule()->getFields());

		$commonFields = array_intersect($fieldsList, $parentFieldsList);
		foreach ($commonFields as $fieldName) {
			if (getFieldVisibilityPermission($parentRecordModel->getModuleName(), $currentUser->getId(), $fieldName) == 0) {
				$this->set($fieldName, $parentRecordModel->get($fieldName));
			}
		}
		if($this->getModuleName() == 'PurchaseOrder' && getFieldVisibilityPermission($parentRecordModel->getModuleName(), $currentUser->getId(), 'account_id') == 0) {
			$this->set('accountid',$parentRecordModel->get('account_id'));
		}
		return $this;
	}

	/**
	 * Function to get inventoy terms and conditions
	 * @return <String>
	 */
	function getInventoryTermsAndConditions() {
		return getTermsAndConditions($this->getModuleName());
	}

	/**
	 * Function to set data of parent record model to this record
	 * @param Vtiger_Record_Model $parentRecordModel
	 * @return Inventory_Record_Model
	 */
	public function setParentRecordData(Vtiger_Record_Model $parentRecordModel) {
		$userModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$moduleName = $parentRecordModel->getModuleName();

		$data = array();
		$fieldMappingList = $parentRecordModel->getInventoryMappingFields();

		foreach ($fieldMappingList as $fieldMapping) {
			$parentField = $fieldMapping['parentField'];
			$inventoryField = $fieldMapping['inventoryField'];
			$fieldModel = Vtiger_Field_Model::getInstance($parentField, Vtiger_Module_Model::getInstance($moduleName));
			if ($fieldModel && $fieldModel->getPermissions()) {
				$data[$inventoryField] = $parentRecordModel->get($parentField);
			} else {
				$data[$inventoryField] = $fieldMapping['defaultValue'];
			}
		}
		return $this->setData($data);
	}


    /*function to create salesorder from failedparts purvesh*/
	 function getCreateSalesOrderUrl() {
		include_once('include/utils/GeneralUtils.php');
	    $SoModuleModel = Vtiger_Module_Model::getInstance('SalesOrder');
		$dataArr = getSingleColumnValue(array(
			'table' => 'vtiger_crmentity',
			'columnId' => 'crmid',
			'idValue' => $this->get('ticket_id'),
			'expectedColValue' => 'createdtime'
		));
		$notiDate = $dataArr[0]['createdtime'];
		$ticketCreatedDateTimeArr = explode(' ', $notiDate);
    	$notiDate = $ticketCreatedDateTimeArr[1];
		$notiDate = Vtiger_Date_UIType::getDisplayDateValue($$notiDate);

		$plantCode = '';
		$plant = getCurrentUserPlantDetails();
		if($plant != false){
			$plantCode = $plant;
		}

	    return "index.php?module=". $SoModuleModel ->getName()."&view=". 
		$SoModuleModel->getEditViewName()."&salesorder_id=".$this->getId().
		'&po_no='.$this->get('sr_app_num').
		'&plant_name='.$plantCode.
		'&failed_part_id='.$this->getId().
		'&po_date='.$notiDate;
		
	}

	/**
	 * Function to get URL for Export the record as PDF
	 * @return <type>
	 */
	public function getExportPDFUrl() {
		return "index.php?module=".$this->getModuleName()."&action=ExportPDF&record=".$this->getId();
	}

	/**
	  * Function to get the send email pdf url
	  * @return <string>
	  */
	public function getSendEmailPDFUrl() {
		return 'module='.$this->getModuleName().'&view=SendEmail&mode=composeMailData&record='.$this->getId();
	}

	/**
	 * Function to get this record and details as PDF
	 */
	public function getPDF() {
		$recordId = $this->getId();
		$moduleName = $this->getModuleName();

		$controllerClassName = "Vtiger_". $moduleName ."PDFController";

		$controller = new $controllerClassName($moduleName);
		$controller->loadRecord($recordId);

		$fileName = $moduleName.'_'.getModuleSequenceNumber($moduleName, $recordId);
		$controller->Output($fileName.'.pdf', 'D');
	}

	/**
	 * Function to get the pdf file name . This will conver the invoice in to pdf and saves the file
	 * @return <String>
	 *
	 */
	public function getPDFFileName() {
		$moduleName = $this->getModuleName();
		if ($moduleName == 'Quotes') {
			vimport("~~/modules/$moduleName/QuotePDFController.php");
			$controllerClassName = "Vtiger_QuotePDFController";
		} else {
			vimport("~~/modules/$moduleName/$moduleName" . "PDFController.php");
			$controllerClassName = "Vtiger_" . $moduleName . "PDFController";
		}

		$recordId = $this->getId();
		$controller = new $controllerClassName($moduleName);
		$controller->loadRecord($recordId);

		$sequenceNo = getModuleSequenceNumber($moduleName,$recordId);
		$translatedName = vtranslate($moduleName, $moduleName);
		$filePath = "storage/$translatedName"."_".$sequenceNo.".pdf";
		//added file name to make it work in IE, also forces the download giving the user the option to save
		$controller->Output($filePath,'F');
		return $filePath;
	}

	/**
	 * Function to get related line items of parent record
	 * @param <Vtiger_Record_Model> $parentRecordModel
	 * @return <Array>
	 */
	public function getParentRecordRelatedLineItems($parentRecordModel) {
		$userCurrencyInfo = Vtiger_Util_Helper::getUserCurrencyInfo();
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$currencyId = $currentUserModel->get('currency_id');
		$numOfCurrencyDecimals = $currentUserModel->get('no_of_currency_decimals');

		$moduleName = $this->getModuleName();
		$productDetails = getAssociatedProducts($parentRecordModel->getModuleName(), $parentRecordModel->getEntity(), $parentRecordModel->getId(), $this->getModuleName());

		$productIdsList = array();
		foreach ($productDetails as $key => $lineItemDetail) {
			$productId	= $lineItemDetail['hdnProductId'.$key];
			$entityType = $lineItemDetail['entityType'.$key];
			$productIdsList[$entityType][] = $productId;
		}

		//Getting list price value of each product in user currency
		$convertedPriceDetails = array();
		foreach ($productIdsList as $entityType => $productIds) {
			$convertedPriceDetails[$entityType] = getPricesForProducts($currencyId, $productIds, $entityType);
		}

		//Getting image details of each product
		$imageDetailsList = array();
		if ($productIdsList['Products']) {
			$imageDetailsList = Products_Record_Model::getProductsImageDetails($productIdsList['Products']);
		}

		foreach ($productDetails as $key => $lineItemDetail) {
			$productId = $lineItemDetail['hdnProductId'.$key];
			$entityType = $lineItemDetail['entityType'.$key];

			//updating list price details
			$productDetails[$key]['listPrice'.$key] = number_format((float)$convertedPriceDetails[$entityType][$productId], $numOfCurrencyDecimals, '.', '');

			//updating cost price details
			$purchaseCost = (float)$userCurrencyInfo['conversion_rate'] * (float)$lineItemDetail['purchaseCost'.$key];
			$productDetails[$key]['purchaseCost'.$key] = number_format($purchaseCost, $numOfCurrencyDecimals, '.', '');

			if($moduleName === 'PurchaseOrder') {
				$productDetails[$key]['listPrice'.$key] = number_format((float)$purchaseCost, $numOfCurrencyDecimals,'.','');
			}

			//Image detail
			if ($imageDetailsList[$productId]) {
				$imageDetails = $imageDetailsList[$productId];
				$productDetails[$key]['productImage'.$key] = $imageDetails[0]['path'].'_'.$imageDetails[0]['orgname'];
			}
		}
		return $productDetails;
	}

	/**
	 * Function to get charges
	 * @return <Array>
	 */
	public function getCharges() {
		if (!$this->chargesAndItsTaxes) {
			$this->chargesAndItsTaxes = array();
			$recordId = $this->getId();
			if ($recordId) {
				$db = PearDatabase::getInstance();
				$result = $db->pquery('SELECT * FROM vtiger_inventorychargesrel WHERE recordid = ?', array($recordId));
				while ($rowData = $db->fetch_array($result)) {
					$this->chargesAndItsTaxes = Zend_Json::decode(html_entity_decode($rowData['charges']));
				}
			}
		}
		return $this->chargesAndItsTaxes;
	}

	/**
	 * Function to get deduct taxes
	 * @return <Array>
	 */
	public function getDeductTaxes() {
		$deductTaxes = $this->get('deductTaxes');
		if ($deductTaxes) {
			return $deductTaxes;
		}

		$deductTaxes = Inventory_TaxRecord_Model::getDeductTaxesList($active = false);
		$record = $this->getId();
		if ($record && $deductTaxes) {
			$db = PearDatabase::getInstance();
			$deductTaxNamesList = array();
			foreach ($deductTaxes as $taxId => $taxInfo) {
				$deductTaxNamesList[] = $taxInfo['taxname'];
			}

			$result = $db->pquery('SELECT '.implode(',', $deductTaxNamesList).' FROM vtiger_inventoryproductrel WHERE id = ?', array($record));
			foreach ($deductTaxes as $taxId => $taxInfo) {
				$percent = $db->query_result($result, 0, $taxInfo['taxname']);
				if ($percent !== NULL && $percent < 0) {
					$deductTaxes[$taxId]['selected']	= true;
					$deductTaxes[$taxId]['percentage']	= -$percent;
				}
			}
		}

		$this->set('deductTaxes', $deductTaxes);
		return $deductTaxes;
	}

	public function getProductsForPurchaseOrder() {
		$relatedProducts = $this->getProducts();

		$productsCount = count($relatedProducts);
		for ($i = 1; $i <= $productsCount; $i++) {
			$relatedProducts[$i]['discountTotal'.$i] = 0;
			$relatedProducts[$i]['discount_percent'.$i] = 0;
			$relatedProducts[$i]['discount_amount'.$i]=0;
			$relatedProducts[$i]['checked_discount_zero'.$i] = 'checked';
			$relatedProducts[$i]['listPrice'.$i] = $relatedProducts[$i]['purchaseCost'.$i] / $relatedProducts[$i]['qty'.$i];
		}
		$relatedProducts[1]['final_details']['discount_percentage_final'] = 0;
		$relatedProducts[1]['final_details']['discount_amount_final'] = 0;
		$relatedProducts[1]['final_details']['discount_type_final'] = 'zero';
		return $relatedProducts;
	}

	/**
	 * Function to get regions list
	 * @return <Array>
	 */
	public function getRegionsList() {
		$recordId = $this->getId();
		$selectedRegionId = $this->get('region_id');

		//Constructing taxes for regions
		$taxesForRegions = array();
		$inventoryTaxes = Inventory_TaxRecord_Model::getProductTaxes();
		foreach ($inventoryTaxes as $taxId => $taxRecordModel) {
			if ($taxRecordModel->getTaxMethod() !== 'Deducted') {
				$taxInfo = array();
				$taxInfo['values']['default'] = $taxRecordModel->getTax();
				foreach ($taxRecordModel->getRegionTaxes() as $list) {
					if (is_array($list['list'])) {
						foreach(array_fill_keys($list['list'], $list['value']) as $key => $value) {
							$taxInfo['values'][$key] = $value;
						}
					}
				}

				$taxInfo['compoundOn'] = $taxRecordModel->getTaxesOnCompound();
				$taxesForRegions[$taxId] = $taxInfo;
			}
		}

		//Constructing charges for regions
		$chargesForRegions = array();
		$charges = Inventory_Charges_Model::getInventoryCharges();
		foreach ($charges as $chargeId => $chargeModel) {
			$chargeInfo = array();
			$chargeInfo['values']['default'] = $chargeModel->getValue();
			foreach ($chargeModel->getSelectedRegions() as $list) {
				if (is_array($list['list'])) {
					foreach(array_fill_keys($list['list'], $list['value']) as $key => $value) {
						$chargeInfo['values'][$key] = $value;
					}
				}
			}

			$chargeInfo['isPercent'] = ($chargeModel->get('format') === 'Percent') ? true : false;
			$chargeInfo['taxes'] = Zend_Json::decode(html_entity_decode($chargeModel->get('taxes')));
			$chargesForRegions[$chargeId] = $chargeInfo;
		}

		//Constructing charge taxes for regions
		$chargeTaxesForRegions = array();
		$chargeTaxes = Inventory_TaxRecord_Model::getChargeTaxes();
		foreach ($chargeTaxes as $taxId => $taxRecordModel) {
			$taxInfo = array();
			$taxInfo['values']['default'] = $taxRecordModel->getTax();
			foreach ($taxRecordModel->getRegionTaxes() as $list) {
				if (is_array($list['list'])) {
					foreach(array_fill_keys($list['list'], $list['value']) as $key => $value) {
						$taxInfo['values'][$key] = $value;
					}
				}
			}

			$taxInfo['compoundOn'] = $taxRecordModel->getTaxesOnCompound();
			$chargeTaxesForRegions[$taxId] = $taxInfo;
		}

		//Constructing Regions Info
		$allRegionsList = array();
		$taxes = $this->getProductTaxes();
		$selectedCharges = $this->getCharges();
		$conversionRateInfo = getCurrencySymbolandCRate($this->get('currency_id'));
		foreach ($selectedCharges as $chargeId => $chargeInfo) {
			$selectedCharges[$chargeId]['value'] = (float)$chargeInfo['value'] / (float)$conversionRateInfo['rate'];
		}

		foreach (Inventory_TaxRegion_Model::getAllTaxRegions() as $regionId => $regionModel) {
			$regionInfo['name'] = $regionModel->getName();

			foreach ($taxesForRegions as $taxId => $taxInfo) {
				$taxValue = $taxInfo['values']['default'];
				if (array_key_exists($regionId, $taxInfo['values'])) {
					$taxValue = $taxInfo['values'][$regionId];
				}

				if ($recordId && $selectedRegionId == $regionId) {
					$taxValue = $taxes[$taxId]['percentage'];
				}
				$regionInfo['taxes'][$taxId]['value'] = $taxValue;

				$compoundOn = $taxInfo['compoundOn'];
				if ($recordId) {
					$compoundOn = array();
					if ($taxes[$taxId]) {
						$compoundOn = $taxes[$taxId]['compoundon'];
					}
				}
				$regionInfo['taxes'][$taxId]['compoundOn'] = $compoundOn;
			}

			foreach ($chargesForRegions as $chargeId => $chargeInfo) {
				$updatedRegionInfo = array();
				$chargeValue = $chargeInfo['values']['default'];
				if (array_key_exists($regionId, $chargeInfo['values'])) {
					$chargeValue = $chargeInfo['values'][$regionId];
				}

				$checked = true;
				$key = ($chargeInfo['isPercent']) ? 'percent' : 'value';
				if ($recordId) {
					if ($selectedRegionId == $regionId) {
						$key = isset($selectedCharges[$chargeId]['percent']) ? 'percent' : 'value';
						$chargeValue = $selectedCharges[$chargeId][$key];
					}

					if (!$selectedCharges[$chargeId]) {
						$checked = false;
					}
				}
				$updatedRegionInfo[$key] = $chargeValue;
				$updatedRegionInfo['checked'] = $checked;

				if (is_array($chargeInfo['taxes'])) {
					foreach ($chargeInfo['taxes'] as $taxId) {
						$taxInfo = $chargeTaxesForRegions[$taxId];

						$taxValue = $taxInfo['values']['default'];
						if (array_key_exists($regionId, $taxInfo['values'])) {
							$taxValue = $taxInfo['values'][$regionId];
						}

						$taxChecked = $checked;
						if ($recordId) {
							if ($selectedCharges[$chargeId]['taxes'][$taxId]) {
								$taxChecked = true;
								if ($selectedRegionId == $regionId) {
									$taxValue = $selectedCharges[$chargeId]['taxes'][$taxId];
								}
							}
						}
						$updatedRegionInfo['taxes'][$taxId]['value']		= $taxValue;
						$updatedRegionInfo['taxes'][$taxId]['checked']		= $taxChecked;
						$updatedRegionInfo['taxes'][$taxId]['compoundOn']	= $taxInfo['compoundOn'];
					}
					$regionInfo['charges'][$chargeId] = $updatedRegionInfo;
				}
			}

			$allRegionsList[$regionId] = $regionInfo;
		}

		$defaultRegionInfo = array();
		foreach ($taxesForRegions as $taxId => $taxInfo) {
			$taxValue = $taxesForRegions[$taxId]['values']['default'];
			if (!$selectedRegionId) {
				$taxValue = $taxes[$taxId]['percentage'];
			}
			$defaultRegionInfo['taxes'][$taxId]['value'] = $taxValue;

			$compoundOn = $taxInfo['compoundOn'];
			if ($recordId) {
				$compoundOn = array();
				if ($taxes[$taxId]) {
					$compoundOn = $taxes[$taxId]['compoundon'];
				}
			}
			$defaultRegionInfo['taxes'][$taxId]['compoundOn'] = $compoundOn;
		}

		foreach ($chargesForRegions as $chargeId => $chargeInfo) {
			$key = ($chargeInfo['isPercent']) ? 'percent' : 'value';
			$chargeValue = $chargeInfo['values']['default'];

			$checked = true;
			if ($recordId) {
				if (!$selectedRegionId) {
					$key = isset($selectedCharges[$chargeId]['percent']) ? 'percent' : 'value';
					$chargeValue = $selectedCharges[$chargeId][$key];
					if (!$chargeValue) {
						$chargeValue = 0;
					}
				}

				if (!$selectedCharges[$chargeId]) {
					$checked = false;
				}
			}
			$defaultRegionInfo['charges'][$chargeId][$key] = $chargeValue;
			$defaultRegionInfo['charges'][$chargeId]['checked'] = $checked;

			if (is_array($chargeInfo['taxes'])) {
				foreach ($chargeInfo['taxes'] as $taxId) {
					$taxInfo = $chargeTaxesForRegions[$taxId];
					$taxValue = $taxInfo['values']['default'];

					$taxChecked = $checked;
					if ($recordId) {
						if ($selectedCharges[$chargeId]['taxes'][$taxId]) {
							$taxChecked = true;
							if (!$selectedRegionId) {
								$taxValue = $selectedCharges[$chargeId]['taxes'][$taxId];
							}
						}
					}

					$defaultRegionInfo['charges'][$chargeId]['taxes'][$taxId]['value']		= $taxValue;
					$defaultRegionInfo['charges'][$chargeId]['taxes'][$taxId]['checked']	= $taxChecked;
					$defaultRegionInfo['charges'][$chargeId]['taxes'][$taxId]['compoundOn'] = $taxInfo['compoundOn'];
				}
			}
		}

		$allRegionsList[0] = $defaultRegionInfo;
		return $allRegionsList;
	}

	/**
	 * Function to get charge tax models list
	 * @param Integer $chargeId
	 * @return Array
	 */
	public function getChargeTaxModelsList($chargeId) {
		if ($chargeId) {
			$chargeTaxModelsList = array();
			$chargesAndItsTaxes = $this->getCharges();
			$chargeInfo = $chargesAndItsTaxes[$chargeId];
			if ($chargeInfo && $chargeInfo['taxes']) {
				$taxes = array_keys($chargeInfo['taxes']);
				foreach ($taxes as $taxId) {
					$chargeTaxModelsList[$taxId] = Inventory_TaxRecord_Model::getInstanceById($taxId, Inventory_TaxRecord_Model::SHIPPING_AND_HANDLING_TAX);
				}
			}

			$chargeModel = Inventory_Charges_Model::getChargeModel($chargeId);
			$selectedChargeTaxes = $chargeModel->getSelectedTaxes();
			foreach ($selectedChargeTaxes as $taxId => $taxRecordModel) {
				$chargeTaxModelsList[$taxId] = $taxRecordModel;
			}
			return $chargeTaxModelsList;
		}
		return array();
	}

	public function convertRequestToProducts(Vtiger_Request $request) {
		$requestData = $request->getAll();
		$noOfDecimalPlaces = getCurrencyDecimalPlaces();
		$totalProductsCount = $requestData['totalProductCount'];

		$productIdsList = array();
		$relatedProducts = array();
		for ($i=1; $i<=$totalProductsCount; $i++) {
			$productId = $requestData["hdnProductId$i"];
			$productIdsList[] = $productId;
			$itemRecordModel = Vtiger_Record_Model::getInstanceById($productId);

			$productData = array();
			$productData["hdnProductId$i"]	= $productId;
			$productData["productName$i"]	= $itemRecordModel->getName();
			$productData["comment$i"]		= $requestData["comment$i"];
			$productData["qtyInStock$i"]	= $itemRecordModel->get('qtyinstock');
			$productData["qty$i"]			= $requestData["qty$i"];
			$productData["listPrice$i"]		= number_format($requestData["listPrice$i"], $noOfDecimalPlaces, '.', '');
			$productData["unitPrice$i"]		= number_format($requestData["listPrice$i"], $noOfDecimalPlaces, '.', '');
			$productData["purchaseCost$i"]	= number_format($purchaseCost, $noOfDecimalPlaces, '.', '');
			$productData["productDescription$i"]= $requestData["productDescription$i"];

			$margin = (float)$requestData["margin$i"];
			if (is_numeric($margin)) {
				$productData["margin$i"] = number_format($margin, $noOfDecimalPlaces, '.', '');
			}

			$productTotal = $requestData["qty$i"] * $requestData["listPrice$i"];
			$productData["productTotal$i"]	= number_format($productTotal, $noOfDecimalPlaces, '.', '');

			$subQtysList = array();
			$subProducts = $requestData["subproduct_ids$i"];
			$subProducts = split(',', rtrim($subProducts, ','));

			foreach ($subProducts as $subProductInfo) {
				 list($subProductId, $subProductQty) = explode(':', $subProductInfo);
				 if ($subProductId) {
					 $subProductName = getProductName($subProductId);
					 $subQtysList[$subProductId] = array('name' => $subProductName, 'qty' => $subProductQty);
				 }
			}
			$productData["subproduct_ids$i"]= $requestData["subproduct_ids$i"];
			$productData["subprod_qty_list$i"]	= $subQtysList;

			//individual disount calculation
			$discountType = $productData["discount_type$i"] = $requestData["discount_type$i"];
			$productData["discount_percent$i"]	= 0;
			$productData["discount_amount$i"]	= 0;
			$discountTotal = 0;

			if ($discountType === 'percentage') {
				$productData["discount_percent$i"] = $requestData["discount_percentage$i"];
				$productData["checked_discount_percent$i"] = 'checked';
				$discountTotal = $productTotal * $productData["discount_percent$i"] / 100;
			} elseif ($discountType === 'amount') {
				$productData["discount_amount$i"] = $requestData["discount_amount$i"];
				$productData["checked_discount_amount$i"] = 'checked';
				$discountTotal = $productData["discount_amount$i"];
			} else {
				$productData["checked_discount_zero$i"] = 'checked';
			}
			$productData["discountTotal$i"]		= number_format($discountTotal, $noOfDecimalPlaces, '.', '');

			//individual taxes calculation
			$taxType = $requestData['taxtype'];
			$itemTaxDetails = $itemRecordModel->getTaxClassDetails();	
			$regionsList = array();
			foreach ($itemTaxDetails as $taxInfo) {
				$regionsInfo = array('default' => $taxInfo['percentage']);
				if ($taxInfo['productregions']) {
					foreach ($taxInfo['productregions'] as $list) {
						if (is_array($list['list'])) {
							foreach (array_fill_keys($list['list'], $list['value']) as $key => $value) {
								$regionsInfo[$key] = $value;
							}
						}
					}
				}
				$regionsList[$taxInfo['taxid']] = $regionsInfo;
			}

			$taxTotal = 0;
			$totalAfterDiscount = $productTotal-$discountTotal;
			$netPrice = $totalAfterDiscount;
			$taxDetails = array();

			foreach ($itemTaxDetails as &$taxInfo) {
				$taxId = $taxInfo['taxid'];
				$taxName = $taxInfo['taxname'];
				$taxValue = 0;
				$taxAmount = 0;

				$taxValue = $taxInfo['percentage'];
				if ($taxType == 'individual') {
					$selectedRegionId = $requestData['region_id'];
					$taxValue = $requestData[$taxName.'_percentage'.$i];
					if ($selectedRegionId) {
						$regionsList[$taxId][$selectedRegionId] = $taxValue;
					} else {
						$regionsList[$taxId]['default'] = $taxValue;
					}

					$taxAmount = $totalAfterDiscount * $taxValue / 100;
				}

				$taxInfo['amount']		= $taxAmount;
				$taxInfo['percentage']	= $taxValue;
				$taxInfo['regionsList']	= $regionsList[$taxInfo['taxid']];
				$taxDetails[$taxId] = $taxInfo;
			}

			$taxTotal = 0;
			foreach ($taxDetails as $taxId => $taxInfo) {
				$taxAmount = $taxInfo['amount'];
				if ($taxInfo['compoundon']) {
					$amount = $totalAfterDiscount;
					foreach ($taxInfo['compoundon'] as $compTaxId) {
						$amount = $amount + $taxDetails[$compTaxId]['amount'];
					}
					$taxAmount = $amount * $taxInfo['percentage'] / 100;
				}
				$taxTotal = $taxTotal + $taxAmount;

				$taxDetails[$taxId]['amount'] = $taxAmount;
				$relatedProducts[$i]['taxTotal'.$i]	= number_format($taxTotal, $numOfCurrencyDecimalPlaces, '.', '');
			}

			$productData["taxTotal$i"]			= number_format($taxTotal, $noOfDecimalPlaces, '.', '');
			$productData["totalAfterDiscount$i"]= number_format($totalAfterDiscount, $noOfDecimalPlaces, '.', '');
			$productData["netPrice$i"]			= number_format($totalAfterDiscount + $taxTotal, $noOfDecimalPlaces, '.', '');

			$productData['taxes'] = $taxDetails;
			$relatedProducts[$i] = $productData;
		}

		//Final details started
		$finalDetails = array();
		$finalDetails['hdnSubTotal'] = number_format($requestData['subtotal'], $noOfDecimalPlaces, '.', '');

		//final discount calculation
		$discountTotalFinal = 0;
		$finalDiscountType = $finalDetails['discount_type_final'] = $requestData['discount_type_final'];
		if ($finalDiscountType === 'percentage') {
			$finalDetails['discount_percentage_final'] = $requestData['discount_percentage_final'];
			$finalDetails['checked_discount_percentage_final'] = 'checked';
			$discountTotalFinal = $finalDetails['discount_percentage_final'];
		} else if ($finalDetails === 'amount') {
			$finalDetails['discount_percentage_final'] = $requestData['discount_amount_final'];
			$finalDetails['checked_discount_amount_final'] = 'checked';
			$discountTotalFinal = $finalDetails['discount_percentage_final'];
		}
		$finalDetails['discountTotal_final'] = number_format($discountTotalFinal, $noOfDecimalPlaces, '.', '');

		//group taxes calculation
		$taxDetails = array();
		$taxTotal = 0;
		$allTaxes = getAllTaxes('available');
		foreach ($allTaxes as $taxInfo) {
			if ($taxInfo['method'] === 'Deducted') {
				continue;
			}

			$taxName = $taxInfo['taxname'];
			if ($taxType == 'group') {
				$taxPercent = $requestData[$taxName.'_group_percentage'];
			} else {
				$taxPercent = $taxInfo['percentage'];
			}
			if ($taxPercent == '' || $taxPercent == 'NULL') {
				$taxPercent = 0;
			}

			$taxInfo['percentage']	= $taxPercent;
			$taxInfo['amount']		= $requestData[$taxName.'_group_amount'];;
			$taxInfo['regions']		= Zend_Json::decode(html_entity_decode($taxInfo['regions']));
			$taxInfo['compoundon']	= Zend_Json::decode(html_entity_decode($taxInfo['compoundon']));
			$taxDetails[$taxInfo['taxid']] = $taxInfo;

			$taxTotal = $taxTotal + $taxInfo['amount'];
		}

		$finalDetails['taxtype']		= $taxType;
		$finalDetails['taxes']			= $taxDetails;
		$finalDetails['tax_totalamount']= number_format($taxTotal, $noOfDecimalPlaces, '.', '');
		$finalDetails['adjustment']		= number_format($requestData['adjustment'], $noOfDecimalPlaces, '.', '');
		$finalDetails['grandTotal']		= number_format($requestData['total'], $noOfDecimalPlaces, '.', '');
		$finalDetails['preTaxTotal']	= number_format($requestData['pre_tax_total'], $noOfDecimalPlaces, '.', '');
		$finalDetails['shipping_handling_charge'] = number_format($requestData['shipping_handling_charge'], $noOfDecimalPlaces, ',', '');
		$finalDetails['adjustment']		= $requestData['adjustmentType'].number_format($requestData['adjustment'], $noOfDecimalPlaces, '.', '');

		//charge value setting to related products array
		$selectedChargesAndItsTaxes = $requestData['charges'];
		foreach ($selectedChargesAndItsTaxes as $chargeId => $chargeInfo) {
			$selectedChargesAndItsTaxes[$chargeId] = Zend_Json::decode(html_entity_decode($chargeInfo));
		}
		$finalDetails['chargesAndItsTaxes'] = $selectedChargesAndItsTaxes;

		$allChargeTaxes = array();
		foreach ($selectedChargesAndItsTaxes as $chargeId => $chargeInfo) {
			if (is_array($chargeInfo['taxes'])) {
				$allChargeTaxes = array_merge($allChargeTaxes, array_keys($chargeInfo['taxes']));
			} else {
				$selectedChargesAndItsTaxes[$chargeId]['taxes'] = array();
			}
		}

		$shippingTaxes = array();
		$allShippingTaxes = getAllTaxes('all', 'sh');
		foreach ($allShippingTaxes as $shTaxInfo) {
			$shippingTaxes[$shTaxInfo['taxid']] = $shTaxInfo;
		}

		$totalAmount = 0;
		foreach ($selectedChargesAndItsTaxes as $chargeId => $chargeInfo) {
			foreach ($chargeInfo['taxes'] as $taxId => $taxPercent) {
				$amount = $calculatedOn = $chargeInfo['value'];

				if ($shippingTaxes[$taxId]['method'] === 'Compound') {
					$compoundTaxes = Zend_Json::decode(html_entity_decode($shippingTaxes[$taxId]['compoundon']));
					if (is_array($compoundTaxes)) {
						foreach ($compoundTaxes as $comTaxId) {
							if ($shippingTaxes[$comTaxId]) {
								$calculatedOn += ((float) $amount * (float) $chargeInfo['taxes'][$comTaxId]) / 100;
							}
						}
					}
				}
				$totalAmount += ((float) $calculatedOn * (float) $taxPercent) / 100;
			}
		}
		$finalDetails['shtax_totalamount'] = number_format($totalAmount, $noOfDecimalPlaces, '.', '');

		//deduct tax values setting to related products
		$deductedTaxesTotalAmount = 0;
		$deductTaxes = $this->getDeductTaxes();
		foreach ($deductTaxes as $taxId => $taxInfo) {
			$taxAmount = ($totalAfterDiscount * (float) $taxInfo['percentage']) / 100;
			$deductTaxes[$taxId]['amount'] = number_format($taxAmount, $noOfDecimalPlaces, '.', '');
			if ($taxInfo['selected']) {
				$deductedTaxesTotalAmount = $deductedTaxesTotalAmount + $taxAmount;
			}
		}
		$finalDetails['deductTaxes'] = $deductTaxes;
		$finalDetails['deductTaxesTotalAmount'] = number_format($deductedTaxesTotalAmount, $noOfDecimalPlaces, '.', '');

		$imageFieldModel = $this->getModule()->getField('image');
		if ($productIdsList && $imageFieldModel && $imageFieldModel->isViewable()) {
			$imageDetailsList = Products_Record_Model::getProductsImageDetails($productIdsList);

			for ($i = 1; $i <= $totalProductsCount; $i++) {
				$product = $relatedProducts[$i];
				$productId = $product["hdnProductId$i"];
				$imageDetails = $imageDetailsList[$productId];
				if ($imageDetails) {
					$relatedProducts[$i]["productImage$i"] = $imageDetails[0]['path'] . '_' . $imageDetails[0]['orgname'];
				}
			}
		}

		if ($relatedProducts[1]) {
			$relatedProducts[1]['final_details'] = $finalDetails;
		}
		return $relatedProducts;
	}

}
