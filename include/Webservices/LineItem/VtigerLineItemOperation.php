<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ***********************************************************************************/

require_once "include/Webservices/VtigerActorOperation.php";
require_once "include/Webservices/LineItem/VtigerInventoryOperation.php";
require_once("include/events/include.inc");
require_once 'modules/com_vtiger_workflow/VTEntityCache.inc';
require_once 'data/CRMEntity.php';
require_once 'include/events/SqlResultIterator.inc';
require_once 'include/Webservices/LineItem/VtigerLineItemMeta.php';
require_once 'include/Webservices/Retrieve.php';
require_once 'include/Webservices/Update.php';
require_once 'include/Webservices/Utils.php';
require_once 'modules/Emails/mail.php';
require_once 'include/utils/InventoryUtils.php';

/**
 * Description of VtigerLineItemOperation
 */
class VtigerLineItemOperation extends VtigerActorOperation {
	private static $lineItemCache = array();
	private $taxType = null;
	private $Individual = 'Individual';
	private $Group = 'Group';
	private $newId = null;
	private $taxList = null;
	private $inActiveTaxList = null;
	private static $parentCache = array();

	public function __construct($webserviceObject,$user,$adb,$log) {
		$this->user = $user;
		$this->log = $log;
		$this->webserviceObject = $webserviceObject;
		$this->pearDB = $adb;
		$this->entityTableName = $this->getActorTables();
		if($this->entityTableName === null){
			throw new WebServiceException(WebServiceErrorCode::$UNKOWNENTITY, 'Entity is not associated with any tables');
		}
		$this->meta = new VtigerLineItemMeta($this->entityTableName,$webserviceObject,$adb,$user);
		$this->moduleFields = null;
		$this->taxList = array();
		$this->inActiveTaxList = array();
	}

	protected function getNextId($elementType, $element) {
		$sql = 'SELECT MAX(vtiger_inventoryproductrel.id) as maxvalue_lineitem_id FROM '.$this->entityTableName;
		$result = $this->pearDB->pquery($sql,array());
		$numOfRows = $this->pearDB->num_rows($result);

		for ($i=0; $i<$numOfRows; $i++) {
			$row = $this->pearDB->query_result($result, $i, 'maxvalue_lineitem_id');
		}

		$id = $row + 1;
		return $id;
	}

	public function recreate($lineItem,$parent){
		$components = vtws_getIdComponents($lineItem['id']);
		$this->newId = $components[1];
		$elementType = 'LineItem';
		$this->initTax($lineItem, $parent);
		$this->_create($elementType, $lineItem);
	}

	/**
	 * Function gives all the line items related to inventory records
	 * @param $parentId - record id or array of the inventory record id's
	 * @return <Array> - list of line items
	 * @throws WebServiceException - Database error
	 */
	public function getAllLineItemForParent($parentId){
		$result = null;

		if (!is_array($parentId)) {
			$parentId = array($parentId);
		}
		global $isControlFromMobileApi;
		$IGMODULE = vglobal('IGMODULE');
		$viewableFields = vglobal('VIEWABLEFIELDSLINE');
		$otherFields = Array('productname','lineitem_id');
		$viewableFields = array_merge($viewableFields,$otherFields);
		$query = "SELECT vtiger_crmentity.label AS productname,vtiger_crmentity.setype AS entitytype,vtiger_crmentity.deleted AS deleted, {$this->entityTableName}.*
						FROM {$this->entityTableName}
						LEFT JOIN vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_inventoryproductrel.productid
						WHERE id IN (". generateQuestionMarks($parentId) .")";

		$transactionSuccessful = vtws_runQueryAsTransaction($query,array($parentId),$result);
		if(!$transactionSuccessful){
			throw new WebServiceException(WebServiceErrorCode::$DATABASEQUERYERROR, 'Database error while performing required operation');
		}
		$lineItemList = array();
		if($result){
			$rowCount = $this->pearDB->num_rows($result);
			for ($i = 0 ; $i < $rowCount ; ++$i) {
				$rowElement = $element = $this->pearDB->query_result_rowdata($result,$i);
				$element['parent_id'] = $parentId;
				$productName = $element['productname'];
				$entityType = $element['entitytype'];
				$lineItemId =  $element['lineitem_id'];
				$id = vtws_getId($this->meta->getEntityId(), $element['lineitem_id']);
				$element = DataTransform::filterAndSanitize($element,$this->meta);
				$element['product_name'] = $productName;
				$element['entity_type'] = $entityType;
				$element['id'] = $id;
				$element['lineitem_id'] = $lineItemId;
				$element['deleted'] = $rowElement['deleted'];
				if($IGMODULE == 'FailedParts' || $IGMODULE == 'ServiceOrders' ||  $IGMODULE == 'ReturnSaleOrders' || $IGMODULE == 'SalesOrder'){
					$elementNew = [];
					foreach($element as $key => $value){
						if (in_array($key, $viewableFields)) {
							$elementNew[$key] = $value;
						}
						if ($key == 'vendor_name' || $key == 'line_vendor_id') {
							if ($isControlFromMobileApi == true) {
								$elementNew[$key . '_Label'] = Vtiger_Functions::getCRMRecordLabel($value);
								$moduleWSID = Mobile_WS_Utils::getEntityModuleWSId('Vendors');
								$elementNew[$key] = $moduleWSID . 'x' . $value;
							}
						} else if ($key == 'submitted_qty_log') {
							$valArr = json_decode($value);
							if (empty($valArr)) {
								$elementNew[$key] = [];
							} else {
								$elementNew[$key] = $valArr->{'val'};
							}
						} else if ($key == 'tot_excluded_qty_rem') {
							$valArr = json_decode($value);
							if (empty($valArr)) {
								$elementNew[$key] = [];
							} else {
								$elementNew[$key] = $valArr->{'val'};
							}
						}
					}
					$elementNew['product_name'] = $productName;
					$lineItemList[] = $elementNew;
				} else {
					$elementNew = [];
					foreach ($element as $key => $value) {
						$elementNew[$key] = $value;
						if ($key == 'vendor_name' || $key == 'line_vendor_id') {
							if ($isControlFromMobileApi == true) {
								$elementNew[$key . '_Label'] = Vtiger_Functions::getCRMRecordLabel($value);
								$moduleWSID = Mobile_WS_Utils::getEntityModuleWSId('Vendors');
								$elementNew[$key] = $moduleWSID . 'x' . $value;
							}
						}
					}
					$lineItemList[] = $elementNew;
				}
			}
		}
		return $lineItemList;
	}

	public function getAllLineItemsOtherForParent($parentId) {
		$result = null;
		if (!is_array($parentId)) {
			$parentId = array($parentId);
		}
		$IGMODULE = vglobal('IGMODULE');
		$viewableFields = vglobal('VIEWABLEFIELDSLINE');
		$otherFields = array('productname', 'lineitem_id', 'quantity', 'comment_other','sequence_no', 'productid');
		$viewableFields = array_merge($viewableFields, $otherFields);
		$query = "SELECT vtiger_crmentity.label AS productname,vtiger_crmentity.setype AS entitytype,vtiger_crmentity.deleted AS deleted, vtiger_inventoryproductrel_other.*
						FROM vtiger_inventoryproductrel_other
						LEFT JOIN vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_inventoryproductrel_other.productid
						WHERE id IN (" . generateQuestionMarks($parentId) . ")";
		$transactionSuccessful = vtws_runQueryAsTransaction($query, array($parentId), $result);
		if (!$transactionSuccessful) {
			throw new WebServiceException(WebServiceErrorCode::$DATABASEQUERYERROR, 'Database error while performing required operation');
		}
		$lineItemList = array();
		if ($result) {
			$rowCount = $this->pearDB->num_rows($result);
			for ($i = 0; $i < $rowCount; ++$i) {
				$rowElement = $element = $this->pearDB->query_result_rowdata($result, $i);
				$element['parent_id'] = $parentId;
				$productName = $element['productname'];
				$entityType = $element['entitytype'];
				$lineItemId =  $element['lineitem_id'];
				$id = vtws_getId($this->meta->getEntityId(), $element['lineitem_id']);
				$element['product_name'] = $productName;
				$element['entity_type'] = $entityType;
				$element['id'] = $id;
				$element['lineitem_id'] = $lineItemId;
				$element['deleted'] = $rowElement['deleted'];
				if ($IGMODULE == 'FailedParts' || $IGMODULE == 'ServiceReports' || $IGMODULE == 'ReturnSaleOrders'
				|| $IGMODULE == 'RecommissioningReports') {
					$elementNew = [];
					foreach ($element as $key => $value) {
						if (in_array($key, $viewableFields)) {
							if ($key == '0') {
								continue;
							}
							$elementNew[$key] = $value;
							// to do generalisation of the fields
							if ($key == 'sad_line_dnsoc') {
								if ($value == '0') {
									$elementNew[$key] = 'No';
								} else {
									$elementNew[$key] = 'Yes';
								}
							} else if ($key == 'comment_other') {
								$elementNew['comment'] = $elementNew[$key];
							}
						}
					}
					$lineItemList[] = $elementNew;
				} else {
					$lineItemList[] = $rowElement;
				}
			}
		}
		return $lineItemList;
	}

	public function _create($elementType, $element){
		$createdElement = parent::create($elementType, $element);
		$productId = vtws_getIdComponents($element['productid']);
		$productId = $productId[1];

		$parentTypeHandler = vtws_getModuleHandlerFromId($element['parent_id'], $this->user);
		$parentTypeMeta = $parentTypeHandler->getMeta();
		$parentType = $parentTypeMeta->getEntityName();
		$parent = $this->getParentById($element['parent_id']);
		if($parentType != 'PurchaseOrder') {
			//update the stock with existing details
			updateStk($productId,$element['quantity'],'',array(),$parentType);
		}

		$this->initTax($element, $parent);
		$this->updateTaxes($createdElement);
		$createdElement['incrementondel'] = '1';
		if(strcasecmp($parent['hdnTaxType'], $this->Individual) ===0){
			$createdElement = $this->appendTaxInfo($createdElement);
		}
		return $createdElement;
	}

	private function appendTaxInfo($element) {
		$meta = $this->getMeta();
		$moduleFields = $meta->getModuleFields();
		foreach ($moduleFields as $fieldName=>$field) {
			if(preg_match('/tax\d+/', $fieldName) != 0){
				if(is_array($this->taxList[$fieldName])){
					$element[$fieldName] = $this->taxList[$fieldName]['percentage'];
				}else{
					$element[$fieldName] = '0.000';
				}
			}
		}
		return $element;
	}

	private function resetTaxInfo($element, $parent) {
		$productTaxInfo = array();
		if(empty($this->taxType)){
			list($typeId,$recordId) = vtws_getIdComponents($element['productid']);
			$productTaxInfo = $this->getProductTaxList($recordId);
		}
		if(count($productTaxInfo) == 0 && strcasecmp($parent['hdnTaxType'], $this->Individual) !==0) {
			$meta = $this->getMeta();
			$moduleFields = $meta->getModuleFields();
			foreach ($moduleFields as $fieldName=>$field) {
				if(preg_match('/tax\d+/', $fieldName) != 0){
					$element[$fieldName] = '0.000';
				}
			}
		}
		return $element;
	}

	private function updateTaxes($createdElement){
		if (count($this->taxList) > 0 || (is_array($this->inActiveTaxList) && count($this->inActiveTaxList) > 0)) {
			$taxList = $this->taxList;
			if (is_array($this->inActiveTaxList) && count($this->inActiveTaxList) > 0) {
				$taxList = array_merge($taxList, $this->inActiveTaxList);
			}
			$id = vtws_getIdComponents($createdElement['id']);
			$id = $id[1];
			$sql = 'UPDATE vtiger_inventoryproductrel set ';
			$sql .= implode('=?,',array_keys($taxList));
			$sql .= '=? WHERE lineitem_id = ?';
			$params = array();
			foreach ($taxList as $taxInfo) {
				$params[] = $taxInfo['percentage'];
			}
			$params[] = $id;
			$result = null;
			$transactionSuccessful = vtws_runQueryAsTransaction($sql,$params,$result);
			if(!$transactionSuccessful){
				throw new WebServiceException(WebServiceErrorCode::$DATABASEQUERYERROR, 'Database error while performing required operation');
			}
		}
	}

	private function initTax($element, $parent) {
		$this->taxList = array();
		$this->inActiveTaxList = array();
		$allTaxes = getAllTaxes();
		if (!empty($element['parent_id'])) {
			$this->taxType = $parent['hdnTaxType'];
		}
		$productId = vtws_getIdComponents($element['productid']);
		$productId = $productId[1];
		if (strcasecmp($parent['hdnTaxType'], $this->Individual) === 0) {
			$found = false;
			$meta = $this->getMeta();
			$moduleFields = $meta->getModuleFields();
			$productTaxList = $this->getProductTaxList($productId);
			if (count($productTaxList) > 0) {
				$this->providedTaxList = array();
				foreach ($moduleFields as $fieldName => $field) {
					if (preg_match('/tax\d+/', $fieldName) != 0) {
						if (isset($element[$fieldName])) {
							$found = true;
							if (is_array($productTaxList[$fieldName])) {
								$this->providedTaxList[$fieldName] = array(
									'label' => $field->getFieldLabelKey(),
									'percentage' => $element[$fieldName]
								);
							}
						}
					}
				}

				if ($found) {
					$this->taxList = $this->providedTaxList;
				}
			} elseif ($found == false) {
				array_merge($this->taxList, $productTaxList);
			}
		} else {
			$meta = $this->getMeta();
			$moduleFields = $meta->getModuleFields();
			$found = false;
			foreach ($moduleFields as $fieldName => $field) {
				if (preg_match('/tax\d+/', $fieldName) != 0) {
					$found = true;
					if (isset($element[$fieldName])) {
						$this->taxList[$fieldName] = array(
							'label' => $field->getFieldLabelKey(),
							'percentage' => $element[$fieldName]
						);
					}
				}
			}
			if(!$found) {
				foreach ($allTaxes as $taxInfo) {
					if ($taxInfo['deleted'] == '0') {
						$this->taxList[$taxInfo['taxname']] = array(
							'label' => $field->getFieldLabelKey(),
							'percentage' => $taxInfo['percentage']
						);
					}
				}
			}
		}
		foreach ($allTaxes as $taxInfo) {
			if ($taxInfo['deleted'] == '1' && !array_key_exists($taxInfo['taxname'], $this->taxList)) {
				$this->inActiveTaxList[$taxInfo['taxname']] = array('percentage' => NULL);
			}
		}
		$this->taxList;
	}

	private function initTaxNONGroup($element, $parent, $allRecordValues) {
		$this->taxList = array();
		$this->inActiveTaxList = array();
		$allTaxes = getAllTaxes();
		if (!empty($element['parent_id'])) {
			$this->taxType = $parent['hdnTaxType'];
		}
		$productId = vtws_getIdComponents($element['productid']);
		$productId = $productId[1];
		if (strcasecmp($parent['hdnTaxType'], $this->Individual) === 0) {
			$found = false;
			$meta = $this->getMeta();
			$moduleFields = $meta->getModuleFields();
			$productTaxList = $this->getProductTaxList($productId);
			if (count($productTaxList) > 0) {
				$this->providedTaxList = array();
				foreach ($moduleFields as $fieldName => $field) {
					if (preg_match('/tax\d+/', $fieldName) != 0) {
						if (isset($element[$fieldName])) {
							$found = true;
							if (is_array($productTaxList[$fieldName])) {
								$this->providedTaxList[$fieldName] = array(
									'label' => $field->getFieldLabelKey(),
									'percentage' => $element[$fieldName]
								);
							}
						}
					}
				}

				if ($found) {
					$this->taxList = $this->providedTaxList;
				}
			} elseif ($found == false) {
				array_merge($this->taxList, $productTaxList);
			}
		} else {
			$meta = $this->getMeta();
			$moduleFields = $meta->getModuleFields();
			$found = false;
			foreach ($moduleFields as $fieldName => $field) {
				if (preg_match('/tax\d+/', $fieldName) != 0) {
					$found = true;
					if (isset($allRecordValues[$fieldName])) {
						$this->taxList[$fieldName] = array(
							'label' => $field->getFieldLabelKey(),
							'percentage' => $allRecordValues[$fieldName]
						);
					}
				}
			}
			if(!$found) {
				foreach ($allTaxes as $taxInfo) {
					if ($taxInfo['deleted'] == '0') {
						$this->taxList[$taxInfo['taxname']] = array(
							'label' => $field->getFieldLabelKey(),
							'percentage' => $taxInfo['percentage']
						);
					}
				}
			}
		}
		foreach ($allTaxes as $taxInfo) {
			if ($taxInfo['deleted'] == '1' && !array_key_exists($taxInfo['taxname'], $this->taxList)) {
				$this->inActiveTaxList[$taxInfo['taxname']] = array('percentage' => NULL);
			}
		}
		$this->taxList;
	}

	public function cleanLineItemList($parentId){
		$components = vtws_getIdComponents($parentId);
		$pId = $components[1];

		$parentTypeHandler = vtws_getModuleHandlerFromId($parentId, $this->user);
		$parentTypeMeta = $parentTypeHandler->getMeta();
		$parentType = $parentTypeMeta->getEntityName();

		$parentObject = CRMEntity::getInstance($parentType);
		$parentObject->id = $pId;
		$lineItemList = $this->getAllLineItemForParent($pId);
		deleteInventoryProductDetails($parentObject);
		$this->resetInventoryStockById($parentId);
	}

	public function setLineItems($elementType, $lineItemList, $parent){
		$currentValue = vglobal('updateInventoryProductRel_deduct_stock');
		vglobal('updateInventoryProductRel_deduct_stock', false);
		$sequenceNo = 1;
		foreach ($lineItemList as $lineItem) {
		$lineItem['parent_id'] = $parent['id'];
			$lineItem['sequence_no'] = $sequenceNo++;
			$this->initTax($lineItem, $parent);
			$id = vtws_getIdComponents($lineItem['parent_id']);
			$this->newId = $id[1];
			$this->create($elementType, $lineItem);
		}
		$element['parent_id'] = $parent['id'];
		vglobal('updateInventoryProductRel_deduct_stock', true);
		$this->updateInventoryStock($element,$parent);
		vglobal('updateInventoryProductRel_deduct_stock', $currentValue);
	}

	public function create($elementType, $element) {
		$parentId = vtws_getIdComponents($element['parent_id']);
		$parentId = $parentId[1];

		$parent = $this->getParentById($element['parent_id']);
		if (!isset($element['listprice']) && $element['listprice'] == '') {
			$productId = vtws_getIdComponents($element['productid']);
			$productId = $productId[1];
			$element['listprice'] = $this->getProductPrice($productId);
		}
		$element = $this->calculateNetprice($element); 
		$id = vtws_getIdComponents($element['parent_id']);
		$this->newId = $id[1];
		$createdLineItem = $this->_create($elementType, $element);
		$updatedLineItemList = $createdLineItem;
		$updatedLineItemList['parent_id'] = $element['parent_id'];
		$this->setCache($parentId, $updatedLineItemList);
		return $createdLineItem;
	}
	
	public function calculateNetprice($element) {
		global $current_user;
		$productId = $element['parent_id'];
		$parent = $this->getParentById($productId);
		$listPrice = $element['listprice'];
		$quantity = $element['quantity'];
		$discount_amount = $element['discount_amount'];
		$discount_percent = $element['discount_percent'];
		$productTotal = $listPrice * $quantity;
		$total_after_discount = $productTotal;

		if (!empty($discount_amount)) {
			$total_after_discount -= $discount_amount;
		}
		if (!empty($discount_percent)) {
			$percentage_discount = ($productTotal * $discount_percent) / 100;
			$total_after_discount -= $percentage_discount;
		}

		$this->initTax($element, $parent);
		if (strcasecmp($parent['hdnTaxType'], $this->Individual) === 0) {
			$tax_net = 0;
			foreach ($this->taxList as $taxname => $taxArray) {
				$taxValue = $taxArray['percentage'];
				$tax_net += ($taxValue * $total_after_discount) / 100;
			}
		}

		$net_price = number_format(($total_after_discount + $tax_net), getCurrencyDecimalPlaces($current_user), '.', '');
		$element['netprice'] = $net_price;
		return $element;
	}

	public function retrieve($id) {
		$element = parent::retrieve($id);
		$element['id'] = $id;
		$parent = $this->getParentById($element['parent_id']);
		return $this->resetTaxInfo($element, $parent);
	}

	public function update($element) {
		$parentId = vtws_getIdComponents($element['parent_id']);
		$parentId = $parentId[1];
		$parentTypeHandler = vtws_getModuleHandlerFromId($element['parent_id'], $this->user);
		$parentTypeMeta = $parentTypeHandler->getMeta();
		$parentType = $parentTypeMeta->getEntityName();
		$parentObject = CRMEntity::getInstance($parentType);
		$parentObject->id = $parentId;
		$lineItemList = $this->getAllLineItemForParent($parentId);
		$parent = $this->getParentById($element['parent_id']);
		$location = $this->getLocationById($lineItemList, $element['id']);
		if($location === false){
			throw new WebserviceException('UNKOWN_CHILD','given line item is not child of parent');
		}
		if(empty($element['listprice'])){
			$productId = vtws_getIdComponents($element['productid']);
			$productId = $productId[1];
			$element['listprice'] = $this->getProductPrice($productId);
		}
		$lineItemList[$location] = $element;
		deleteInventoryProductDetails($parentObject);
		$this->resetInventoryStock($element, $parent);
		$updatedLineItemList = array();
		foreach ($lineItemList as $lineItem) {
			$id = vtws_getIdComponents($lineItem['id']);
			$this->newId = $id[1];
			$updatedLineItemList[] = $this->_create($elementType, $lineItem);
			if($element == $lineItem){
				$createdElement = $updatedLineItemList[count($updatedLineItemList) - 1];
			}
		}
		$this->setCache($parentId, $updatedLineItemList);
		$this->updateInventoryStock($element,$parent);
		$this->updateParent($element, $parent);
		return $createdElement;
	}

	function getProductPrice($productId){
		$db = PearDatabase::getInstance();
		$sql = "select unit_price from vtiger_products where productid=?";
		$params = array($productId);
		$result = null;
		$transactionSuccessful = vtws_runQueryAsTransaction($sql,$params,$result);
		if(!$transactionSuccessful){
			throw new WebServiceException(WebServiceErrorCode::$DATABASEQUERYERROR, 'Database error while performing required operation');
		}
		$price = 0;
		$it = new SqlResultIterator($db, $result);
		foreach ($it as $row) {
			$price = $row->unit_price;
		}
		return $price;
	}

	private function getLocationById($lineItemList, $id){
		foreach ($lineItemList as $index=>$lineItem) {
			if($lineItem['id'] == $id){
				return $index;
			}
		}
		return false;
	}

	public function delete($id){
		$element = vtws_retrieve($id, $this->user);
		if(!empty($element['parent_id'])){
			$parent = $this->getParentById($element['parent_id']);
		}
		$parentId = vtws_getIdComponents($element['parent_id']);
		$parentId = $parentId[1];
		$lineItemList = $this->getAllLineItemForParent($parentId);
		$this->cleanLineItemList($element['parent_id']);
		$this->initTax($element, $parent);
		$result = parent::delete($id);
		$updatedList = array();
		$element = null;
		foreach ($lineItemList as $lineItem) {
			if($id != $lineItem['id']){
				$updatedList[] = $lineItem;
			}else{
				$element = $lineItem;
			}
		}
		$this->setLineItems('LineItem', $updatedList, $parent);
		$this->resetCacheForParent($parentId);
		$this->updateParent($element, $parent);
		$this->updateInventoryStock($element, $parent);
		return $result;
	}

	private function resetCacheForParent($parentId){
		self::$lineItemCache[$parentId] = null;
	}

	public function updateParent($createdElement,$parent){
		$discount = 0;
		$parentId = vtws_getIdComponents($parent['id']);
		$parentId = $parentId[1];
		$lineItemList = $this->getAllLineItemForParent($parentId);
		$parent['hdnSubTotal'] = 0;
		$taxAmount = 0;
		global $adb;
		if (gettype($_REQUEST['values']) == 'string') {
			$allRecordValues = json_decode($_REQUEST['values'], true);
		} else {
			$allRecordValues = $_REQUEST['values'];
		}

		$individualPreTaxTotal = 0;
		$compoundOn = $allTaxes = array();
		$allItemTaxes = getAllTaxes('available');
		foreach ($allItemTaxes as $taxInfo) {
			$taxCompoundOnInfo = array();
			if ($taxInfo['compoundon']) {
				$taxCompoundOnInfo = Zend_Json::decode(html_entity_decode($taxInfo['compoundon']));
			}
			$compoundOn[$taxInfo['taxid']] = $taxCompoundOnInfo;
			$allTaxes[$taxInfo['taxname']] = $taxInfo;
		}

		foreach ($lineItemList as $lineItem) {
			$discount = 0;
			$lineItemTotal = $lineItem['listprice'] * $lineItem['quantity'];
			$lineItem['discount_amount'] = (float)($lineItem['discount_amount']);
			$lineItem['discount_percent'] = (float)($lineItem['discount_percent']);
			if(!empty($lineItem['discount_amount'])){
				$discount = ($lineItem['discount_amount']);
			}elseif(!empty($lineItem['discount_percent'])) {
				$discount = ($lineItem['discount_percent'])/100 * $lineItemTotal;
			}
			$this->initTax($lineItem, $parent);
			$lineItemTotal = $lineItemTotal - $discount;
			$parent['hdnSubTotal'] = ($parent['hdnSubTotal'] ) + $lineItemTotal;
			if(strcasecmp($parent['hdnTaxType'], $this->Individual) ===0){
				$taxAmountsList = array();
				foreach ($this->taxList as $taxName => $taxInfo) {
					$taxAmountsList[$allTaxes[$taxName]['taxid']] = array('percentage' => $taxInfo['percentage'], 'amount' => ($lineItemTotal * $taxInfo['percentage']) / 100);
				}

				foreach ($taxAmountsList as $taxId => $taxInfo) {
					if ($compoundOn[$taxId]) {
						$amount = $lineItemTotal;
						foreach ($compoundOn[$taxId] as $comTaxId) {
							$amount += $taxAmountsList[$comTaxId]['amount'];
						}
						$taxAmountsList[$taxId]['amount'] = ($amount * $taxInfo['percentage']) / 100;
					}

					$parent['hdnSubTotal'] += $taxInfo['amount'];
				}
				$individualPreTaxTotal += $lineItemTotal;
			}
		}

		if(!empty($parent['hdnDiscountAmount']) && ((double)$parent['hdnDiscountAmount']) > 0){
			$discount = ($parent['hdnDiscountAmount']);
		} elseif(!empty($parent['hdnDiscountPercent'])){
			$discount = ($parent['hdnDiscountPercent']/100 * $parent['hdnSubTotal']);
		} else {
			$discount = 0;
		}
		
		$discount_amount_final = vtlib_purify($allRecordValues['discount_amount_final']);
		$discount = (float) $discount_amount_final;

		$parent['pre_tax_total'] = $total = $parent['hdnSubTotal'] - $discount + $parent['hdnS_H_Amount'];
		if ($parent['hdnTaxType'] === 'individual') {
			$parent['pre_tax_total'] = $individualPreTaxTotal - $discount + $parent['hdnS_H_Amount'];
		}

		$taxTotal = $parent['hdnSubTotal'] - $discount;
		if (strcasecmp($parent['hdnTaxType'], $this->Individual) !== 0) {
			$newTaxList = array();
			foreach ($createdElement as $element) {
				$this->initTaxNONGroup($element, $parent, $allRecordValues);
				$newTaxList[] = $this->taxList;
			}
			if ($newTaxList) {
				$this->taxList = $newTaxList[0];
			}
			$taxAmountsList = array();
			foreach ($this->taxList as $taxName => $taxInfo) {
				$taxAmountsList[$allTaxes[$taxName]['taxid']] = array('percentage' => $taxInfo['percentage'], 'amount' => ($taxTotal * $taxInfo['percentage']) / 100);
			}

			foreach ($taxAmountsList as $taxId => $taxInfo) {
				if ($compoundOn[$taxId]) {
					$amount = $taxTotal;
					foreach ($compoundOn[$taxId] as $comTaxId) {
						$amount += $taxAmountsList[$comTaxId]['amount'];
					}
					$taxInfo['amount'] = $taxAmountsList[$taxId]['amount'] = ($amount * $taxInfo['percentage']) / 100;
				}

				$taxAmount += $taxInfo['amount'];
			}
		}

		$adb->pquery('DELETE FROM vtiger_inventorychargesrel WHERE recordid = ?', array($parentId));
		$chargesInfo = array();
		if (isset($allRecordValues['charges'])) {
			$chargesInfo = $allRecordValues['charges'];
		}
		$objectFormat = [];
		$idIg = 1;
		foreach($chargesInfo as $cahrinf){
			$objectFormat[$idIg] = $cahrinf;
			$idIg = $idIg + 1;
		}
		$adb->pquery('INSERT INTO vtiger_inventorychargesrel VALUES (?, ?)', array($parentId, Zend_Json::encode((object)$objectFormat)));
		// $adb->pquery('INSERT INTO vtiger_inventorychargesrel VALUES (?, ?)', array($parentId, Zend_Json::encode((object)$chargesInfo)));
		$chargeTotal = 0;
		foreach($chargesInfo as $charge){
			$chargeTotal = $chargeTotal + $charge['value'];
		}

		$tax1Val = $allRecordValues['tax1'];
		$result = $this->pearDB->pquery('update vtiger_inventoryproductrel set tax1 = ? WHERE id = ?', array($tax1Val, $parentId));
		//Calculating charge values
		$result = $this->pearDB->pquery('SELECT * FROM vtiger_inventorychargesrel WHERE recordid = ?', array($parentId));
		$rowData = $this->pearDB->fetch_array($result);
		if ($rowData['charges']) {
			$allShippingTaxes = array();
			$shippingTaxes = getAllTaxes('all', 'sh', 'edit', $parentId);
			foreach ($shippingTaxes as $shippingTaxInfo) {
				$compoundOnInfo = array();
				if ($shippingTaxInfo['compoundon']) {
					$compoundOnInfo = Zend_Json::decode(html_entity_decode($shippingTaxInfo['compoundon']));
				}

				$shippingTaxInfo['compoundon'] = $compoundOnInfo;
				$allShippingTaxes[$shippingTaxInfo['taxid']] = $shippingTaxInfo;
			}

			$charges = Zend_Json::decode(html_entity_decode($rowData['charges']));
			foreach ($charges as $chargeId => $chargeInfo) {
				$chargeTaxes = $chargeInfo['taxes'];
				if ($chargeTaxes) {
					foreach ($chargeTaxes as $shTaxId => $shTaxPercentage) {
						$amount = $calculatedOn = $chargeInfo['value'];
						if ($allShippingTaxes[$shTaxId]['method'] === 'Compound') {
							foreach ($allShippingTaxes[$shTaxId]['compoundon'] as $comShTaxId) {
								$calculatedOn += ($amount * $chargeTaxes[$comShTaxId]) / 100;
							}
						}

						$shTaxAmount = ($calculatedOn * $shTaxPercentage) / 100;
						$taxAmount += $shTaxAmount;
					}
				}
			}
		}
		$parent['hdnGrandTotal'] = $total + $taxAmount + $parent['txtAdjustment'];

		$parentTypeHandler = vtws_getModuleHandlerFromId($parent['id'], $this->user);
		$parentTypeMeta = $parentTypeHandler->getMeta();
		$parentType = $parentTypeMeta->getEntityName();

		$parentInstance = CRMEntity::getInstance($parentType);
		$sql = 'update '.$parentInstance->table_name.' set subtotal=?, total=?, pre_tax_total=? where '.
		$parentInstance->tab_name_index[$parentInstance->table_name].'=?';
		$params = array($parent['hdnSubTotal'],$parent['hdnGrandTotal'],$parent['pre_tax_total'],$parentId);
		$transactionSuccessful = vtws_runQueryAsTransaction($sql,$params,$result);
		$this->setParent($parent['id'], $parent);
		if(!$transactionSuccessful){
			throw new WebServiceException(WebServiceErrorCode::$DATABASEQUERYERROR, 'Database error while performing required operation');
		}

		$updatequery  = " update $parentInstance->table_name set ";
		$updateparams = array();
		$subtotal = vtlib_purify($parent['hdnSubTotal']);
		$updatequery .= " subtotal=?,";
		array_push($updateparams, $subtotal);

		$igDisc = 0;
		if ($allRecordValues['discount_type_final'] == 'percentage') {
			$updatequery .= " discount_percent=?,discount_amount=?,";
			array_push($updateparams, vtlib_purify($allRecordValues['discount_percentage_final']));
			array_push($updateparams, null);
		} elseif ($allRecordValues['discount_type_final'] == 'amount') {
			$discount_amount_final = vtlib_purify($allRecordValues['discount_amount_final']);
			$updatequery .= " discount_amount=?,discount_percent=?,";
			array_push($updateparams, $discount_amount_final);
			array_push($updateparams, null);
			$igDisc = (float) $discount_amount_final;
		} elseif ($allRecordValues['discount_type_final'] == 'zero') {
			$updatequery .= " discount_amount=?,discount_percent=?,";
			array_push($updateparams, null);
			array_push($updateparams, null);
		}

		$updatequery .= " s_h_amount=?,";
		array_push($updateparams, $chargeTotal);

		$parent['pre_tax_total'] = $parent['hdnSubTotal'] - $igDisc + $chargeTotal;
		$pretaxTotal = vtlib_purify($parent['pre_tax_total']);
		$updatequery .= " pre_tax_total=?,";
		array_push($updateparams, $pretaxTotal);

		$updatequery .= " taxtype=?,";
		array_push($updateparams, $allRecordValues['taxtype']);

		// $shipping_handling_charge = vtlib_purify($allRecordValues['shipping_handling_charge']);
		// $updatequery .= " s_h_amount=?,";
		// array_push($updateparams, $shipping_handling_charge);

		$adjustmentType = '';
		if($allRecordValues['adjustmentType'] == '-')
			$adjustmentType = vtlib_purify($allRecordValues['adjustmentType']);
	
		$adjustment = vtlib_purify($allRecordValues['adjustment']);
		$updatequery .= " adjustment=?,";
		array_push($updateparams, $adjustmentType.$adjustment);

	
		// $total = vtlib_purify($allRecordValues['total']);
		$updatequery .= " total=?,";
		$parent['hdnGrandTotal'] = $parent['hdnSubTotal'] - $igDisc + $chargeTotal + $taxAmount + $parent['txtAdjustment'];
		array_push($updateparams, $parent['hdnGrandTotal']);
	
		// $updatequery .= ' compound_taxes_info = ?,';
		// array_push($updateparams, Zend_Json::encode($compoundTaxesInfo));
	
		if (isset($allRecordValues['region_id'])) {
			$updatequery .= " region_id = ?,";
			array_push($updateparams, vtlib_purify($allRecordValues['region_id']));
		}
	
		$updatequery .= " s_h_percent=?";
		array_push($updateparams, vtlib_purify($allRecordValues['s_h_percent']));
	
		$updatequery .= " where ".$parentInstance->tab_name_index[$parentInstance->table_name]."=?";
		array_push($updateparams, $parentId);
		$adb->pquery($updatequery,$updateparams);
	}

	public function getCollectiveTaxList(){
		$db = PearDatabase::getInstance();
		$sql = 'select * from vtiger_inventorytaxinfo where deleted=0';
		$params = array();
		$result = null;
		$transactionSuccessful = vtws_runQueryAsTransaction($sql,$params,$result);
		if(!$transactionSuccessful){
			throw new WebServiceException(WebServiceErrorCode::$DATABASEQUERYERROR, 'Database error while performing required operation');
		}
		$it = new SqlResultIterator($db, $result);
		$this->taxList = array();
		foreach ($it as $row) {
			$this->taxList[$row->taxname] = array('label'=>$row->taxlabel,
				'percentage'=>$row->percentage);
		}
		return $this->taxList;
	}

	private function getProductTaxList($productId){
		$db = PearDatabase::getInstance();
		$sql = 'select * from vtiger_producttaxrel inner join vtiger_inventorytaxinfo on
			vtiger_producttaxrel.taxid=vtiger_inventorytaxinfo.taxid and deleted=0
			where productid=?';
		$params = array($productId);
		$result = null;
		$transactionSuccessful = vtws_runQueryAsTransaction($sql,$params,$result);
		if(!$transactionSuccessful){
			throw new WebServiceException(WebServiceErrorCode::$DATABASEQUERYERROR, 'Database error while performing required operation');
		}
		$it = new SqlResultIterator($db, $result);
		$this->taxList = array();
		foreach ($it as $row) {
			$this->taxList[$row->taxname] = array('label'=>$row->taxlabel,
				'percentage'=>$row->taxpercentage);
		}
		return $this->taxList;
	}

	private function updateInventoryStock($element, $parent){
		global $updateInventoryProductRel_update_product_array;
		if(empty($updateInventoryProductRel_update_product_array)){
			$updateInventoryProductRel_update_product_array = array();
		}
		$entityCache = new VTEntityCache($this->user);
		$entityData = $entityCache->forId($element['parent_id']);
		updateInventoryProductRel($entityData);
	}

	private function resetInventoryStock($element,$parent){
		if(!empty($parent['id'])){
			$this->resetInventoryStockById($parent['id']);
		}
	}

	private function resetInventoryStockById($parentId){
		if(!empty($parentId)){
			$entityCache = new VTEntityCache($this->user);
			$entityData = $entityCache->forId($parentId);
			updateInventoryProductRel($entityData);
		}
	}

	public function getParentById($parentId){
		if (empty(self::$parentCache[$parentId])) {
			self::$parentCache[$parentId] = Vtiger_Functions::jsonEncode(vtws_retrieve($parentId, $this->user));
		}
		return json_decode(self::$parentCache[$parentId], true);
	}

	public function setParent($parentId, $parent) {
		if (is_array($parent) || is_object($parent)) {
			$parent = Vtiger_Functions::jsonEncode($parent);
		}
		self::$parentCache[$parentId] = $parent;
	}

	function setCache($parentId, $updatedList) {
		self::$lineItemCache[$parentId] = $updatedList;
	}

	public function __create($elementType,$element){
		$element['id'] = $element['parent_id'];
		unset($element['parent_id']);
		$success = parent::__create($elementType, $element);
		return $success;
	}

	protected function getElement(){
		if(!empty($this->element['id'])) {
			$this->element['parent_id'] = $this->element['id'];
		}
		return $this->element;
	}

	public function describe($elementType) {
		$describe = parent::describe($elementType);
		foreach ($describe['fields'] as $key => $list){
			if($list["name"] == 'description'){
				unset($describe['fields'][$key]);
			}
		}
		// unset will retain array index in the result, we should remove
		$describe['fields'] = array_values($describe['fields']);
		return $describe;
	}
}
?>
