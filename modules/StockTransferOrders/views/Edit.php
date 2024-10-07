<?php
include_once('include/utils/GeneralUtils.php');
class StockTransferOrders_Edit_View extends Vtiger_Edit_View {

	public function process(Vtiger_Request $request) {
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$record = $request->get('record');
		$sourceRecord = $request->get('sourceRecord');
		$sourceModule = $request->get('sourceModule');
		if (empty($sourceRecord) && empty($sourceModule)) {
			$sourceRecord = $request->get('returnrecord');
			$sourceModule = $request->get('returnmodule');
		}

		$viewer->assign('MODE', '');
		$viewer->assign('IS_DUPLICATE', false);
		if ($request->has('totalProductCount')) {
			if ($record) {
				$recordModel = Vtiger_Record_Model::getInstanceById($record);
			} else {
				$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
			}
			$relatedProducts = $recordModel->convertRequestToProducts($request);
			$taxes = $relatedProducts[1]['final_details']['taxes'];
		} else if (!empty($record)  && $request->get('isDuplicate') == true) {
			$recordModel = Inventory_Record_Model::getInstanceById($record, $moduleName);
			$currencyInfo = $recordModel->getCurrencyInfo();
			$taxes = $recordModel->getProductTaxes();
			$relatedProducts = $recordModel->getProducts();

			//While Duplicating record, If the related record is deleted then we are removing related record info in record model
			$mandatoryFieldModels = $recordModel->getModule()->getMandatoryFieldModels();
			foreach ($mandatoryFieldModels as $fieldModel) {
				if ($fieldModel->isReferenceField()) {
					$fieldName = $fieldModel->get('name');
					if (Vtiger_Util_Helper::checkRecordExistance($recordModel->get($fieldName))) {
						$recordModel->set($fieldName, '');
					}
				}
			}
			$viewer->assign('IS_DUPLICATE', true);
		} elseif (!empty($record)) {
			$recordModel = Inventory_Record_Model::getInstanceById($record, $moduleName);
			$currencyInfo = $recordModel->getCurrencyInfo();
			$taxes = $recordModel->getProductTaxes();
			$relatedProducts = $recordModel->getProducts();
			$viewer->assign('RECORD_ID', $record);
			$viewer->assign('MODE', 'edit');
		} elseif ($request->get('salesorder_id') || $request->get('quote_id')) {
			if ($request->get('salesorder_id')) {
				$referenceId = $request->get('salesorder_id');
			} else if ($request->get('servicereport_id')) {
				$referenceId = $request->get('servicereport_id');
			} else {
				$referenceId = $request->get('quote_id');
			}

			$parentRecordModel = ServiceReports_Record_Model::getInstanceById($referenceId);
			$currencyInfo = $parentRecordModel->getCurrencyInfo();
			$taxes = $parentRecordModel->getProductTaxes();
			$relatedProducts = $parentRecordModel->getProductsForSO();
			$viewer->assign('RELATED_PRODUCTS_OTHER', $parentRecordModel->getProductsForView());
			$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
			$recordModel->setRecordFieldValues($parentRecordModel);
		} elseif (($request->get('salesorder_id') || $request->get('quote_id') || $request->get('invoice_id')) && ($moduleName == 'PurchaseOrder')) {
			if ($request->get('salesorder_id')) {
				$referenceId = $request->get('salesorder_id');
			} elseif ($request->get('invoice_id')) {
				$referenceId = $request->get('invoice_id');
			} else {
				$referenceId = $request->get('quote_id');
			}

			$parentRecordModel = Inventory_Record_Model::getInstanceById($referenceId);
			$currencyInfo = $parentRecordModel->getCurrencyInfo();

			$relatedProducts = $parentRecordModel->getProductsForPurchaseOrder();
			$taxes = $parentRecordModel->getProductTaxes();

			$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
			$recordModel->setRecordFieldValues($parentRecordModel);
		} elseif ($request->get('salesorder_id') || $request->get('quote_id') || $request->get('servicereport_id')) {
			if ($request->get('serviceorder_id')) {
				$referenceId = $request->get('serviceorder_id');
			} else {
				$referenceId = $request->get('quote_id');
			}

			$parentRecordModel = ServiceOrders_Record_Model::getInstanceById($referenceId);
			$relatedProducts = [];
			$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
			$parentRecordModuleName = $parentRecordModel->getModuleName();
			if ($parentRecordModuleName == 'ServiceOrders') {
				$db = PearDatabase::getInstance();
				$sql = 'SELECT external_app_num,equipment_id FROM `vtiger_troubletickets` 
				where ticketid = ?';
				$sqlResult = $db->pquery($sql, array($request->get('ticket_id')));
				$dataRow = $db->fetchByAssoc($sqlResult, 0);
				$recordModel->set('your_ref', $dataRow['external_app_num']);
				$equipmentNum = $dataRow['equipment_id'];
				$sql = 'SELECT external_app_num FROM `vtiger_serviceorders`
				where ticket_id = ?';
				$sqlResult = $db->pquery($sql, array($request->get('ticket_id')));
				$dataRow = $db->fetchByAssoc($sqlResult, 0);
				$recordModel->set('our_ref', $dataRow['external_app_num']);

				$equipId =  $equipmentNum;
				$SAPrefEquip = '';
				if (isRecordExists($equipId)) {
					$recordInstance = Vtiger_Record_Model::getInstanceById($equipId);
					$SAPrefEquip = $recordInstance->get('equipment_sl_no');
				}
				$recordModel->set('collective_no', $SAPrefEquip);
			}
			$dataArr = getSingleColumnValue(array(
				'table' => 'vtiger_troubletickets',
				'columnId' => 'ticketid',
				'idValue' => $request->get('ticket_id'),
				'expectedColValue' => 'external_app_num'
			));
			$recordModel->set('ext_app_num_noti', $dataArr[0]['external_app_num']);

			$dataArr = getSingleColumnValue(array(
				'table' => 'vtiger_serviceorders',
				'columnId' => 'serviceordersid',
				'idValue' => $request->get('serviceorder_id'),
				'expectedColValue' => 'external_app_num'
			));
			$recordModel->set('ext_app_num_so', $dataArr[0]['external_app_num']);
			$viewer->assign('RELATED_PRODUCTS_OTHER', $parentRecordModel->getProductsForSTO());
			$recordModel->setRecordFieldValues($parentRecordModel);
		} else {
			$taxes = Inventory_Module_Model::getAllProductTaxes();
			$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);

			//The creation of Inventory record from action and Related list of product/service detailview the product/service details will calculated by following code
			if ($request->get('product_id') || $sourceModule === 'Products' || $request->get('productid')) {
				if ($sourceRecord) {
					$productRecordModel = Products_Record_Model::getInstanceById($sourceRecord);
				} else if ($request->get('product_id')) {
					$productRecordModel = Products_Record_Model::getInstanceById($request->get('product_id'));
				} else if ($request->get('productid')) {
					$productRecordModel = Products_Record_Model::getInstanceById($request->get('productid'));
				}
				$relatedProducts = $productRecordModel->getDetailsForInventoryModule($recordModel);
			} elseif ($request->get('service_id') || $sourceModule === 'Services') {
				if ($sourceRecord) {
					$serviceRecordModel = Services_Record_Model::getInstanceById($sourceRecord);
				} else {
					$serviceRecordModel = Services_Record_Model::getInstanceById($request->get('service_id'));
				}
				$relatedProducts = $serviceRecordModel->getDetailsForInventoryModule($recordModel);
			} elseif ($sourceRecord && in_array($sourceModule, array('Accounts', 'Contacts', 'Potentials', 'Vendors', 'PurchaseOrder'))) {
				$parentRecordModel = Vtiger_Record_Model::getInstanceById($sourceRecord, $sourceModule);
				$recordModel->setParentRecordData($parentRecordModel);
				if ($sourceModule !== 'PurchaseOrder') {
					$relatedProducts = $recordModel->getParentRecordRelatedLineItems($parentRecordModel);
				}
			} elseif ($sourceRecord && in_array($sourceModule, array('HelpDesk', 'Leads'))) {
				$parentRecordModel = Vtiger_Record_Model::getInstanceById($sourceRecord, $sourceModule);
				$relatedProducts = $recordModel->getParentRecordRelatedLineItems($parentRecordModel);
			}
		}

		$deductTaxes = $relatedProducts[1]['final_details']['deductTaxes'];
		if (!$deductTaxes) {
			$deductTaxes = Inventory_TaxRecord_Model::getDeductTaxesList();
		}

		$taxType = $relatedProducts[1]['final_details']['taxtype'];
		$moduleModel = $recordModel->getModule();
		$fieldList = $moduleModel->getFields();
		$requestFieldList = array_intersect_key($request->getAllPurified(), $fieldList);

		//get the inventory terms and conditions
		$inventoryRecordModel = Inventory_Record_Model::getCleanInstance($moduleName);
		$termsAndConditions = $inventoryRecordModel->getInventoryTermsAndConditions();

		foreach ($requestFieldList as $fieldName => $fieldValue) {
			$fieldModel = $fieldList[$fieldName];
			if ($fieldModel->isEditable()) {
				$recordModel->set($fieldName, $fieldModel->getDBInsertValue($fieldValue));
			}
		}
		$recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_EDIT);

		$viewer->assign('VIEW_MODE', "fullForm");

		$isRelationOperation = $request->get('relationOperation');

		//set plant name
		global $adb;
		$currentUserModal = Users_Record_Model::getCurrentUserModel();
		$badge_no = $currentUserModal->get('user_name');
		$sql = "SELECT e.regional_office FROM vtiger_serviceengineer as e WHERE e.badge_no=? 
		ORDER BY serviceengineerid DESC LIMIT 1";
		$sqlResult = $adb->pquery($sql, array(decode_html($badge_no)));
		$sqlData = $adb->fetch_array($sqlResult);
		$plant_name = $sqlData['regional_office'] . "-Depot";
		$sql = "SELECT m.maintenanceplantid FROM vtiger_maintenanceplant as m WHERE m.plant_name=?";
		$sqlResult = $adb->pquery($sql, array(decode_html($plant_name)));
		$sqlData = $adb->fetch_array($sqlResult);
		if (!empty($sqlData['maintenanceplantid'])) {
			$recordModel->set('rec_plant_name', $sqlData['maintenanceplantid']);
		}

		//if it is relation edit
		$viewer->assign('IS_RELATION_OPERATION', $isRelationOperation);
		if ($isRelationOperation) {
			$viewer->assign('SOURCE_MODULE', $sourceModule);
			$viewer->assign('SOURCE_RECORD', $sourceRecord);
		}
		if (!empty($record)  && $request->get('isDuplicate') == true) {
			$viewer->assign('IS_DUPLICATE', true);
		} else {
			$viewer->assign('IS_DUPLICATE', false);
		}
		$currencies = Inventory_Module_Model::getAllCurrencies();
		$picklistDependencyDatasource = Vtiger_DependencyPicklist::getPicklistDependencyDatasource($moduleName);

		$recordStructure = $recordStructureInstance->getStructure();

		$viewer->assign('PICKIST_DEPENDENCY_DATASOURCE', Vtiger_Functions::jsonEncode($picklistDependencyDatasource));
		$viewer->assign('RECORD', $recordModel);
		$viewer->assign('RECORD_STRUCTURE_MODEL', $recordStructureInstance);
		$viewer->assign('RECORD_STRUCTURE', $recordStructure);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('CURRENTDATE', date('Y-n-j'));
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());

		$taxRegions = $recordModel->getRegionsList();
		$defaultRegionInfo = $taxRegions[0];
		unset($taxRegions[0]);

		$viewer->assign('TAX_REGIONS', $taxRegions);
		$viewer->assign('DEFAULT_TAX_REGION_INFO', $defaultRegionInfo);
		$viewer->assign('INVENTORY_CHARGES', Inventory_Charges_Model::getInventoryCharges());
		$viewer->assign('RELATED_PRODUCTS', $relatedProducts);
		$viewer->assign('DEDUCTED_TAXES', $deductTaxes);
		$viewer->assign('TAXES', $taxes);
		$viewer->assign('TAX_TYPE', $taxType);
		$viewer->assign('CURRENCINFO', $currencyInfo);
		$viewer->assign('CURRENCIES', $currencies);
		$viewer->assign('TERMSANDCONDITIONS', $termsAndConditions);

		$productModuleModel = Vtiger_Module_Model::getInstance('Products');
		$viewer->assign('PRODUCT_ACTIVE', $productModuleModel->isActive());
		$viewer->assign('SERVICE_ACTIVE', false);
		// added to set the return values
		if ($request->get('returnview')) {
			$request->setViewerReturnValues($viewer);
		}
		$data = configuredLineItemFieldsWithOutDepend($moduleName);
		$viewer->assign('LINEITEM_CUSTOM_FIELDNAMES', $data['fieldNames']);
		$viewer->assign('LINEITEM_CUSTOM_FIELDS', $data['fields']);
		$excludedFields = array('sr_action_two','sr_action_one','sr_replace_action');
		$data = configuredLineItemFieldsWithOutDependBLockEX('ServiceOrders', 'PARTS_FROM_SR',$excludedFields);
		$viewer->assign('LINEITEM_CUSTOM_FIELDNAMES_OTHER', $data['fieldNames']);
		$viewer->assign('LINEITEM_CUSTOM_FIELDS_OTHER', $data['fields']);

		if (empty($record) || $record != $_SESSION["lastSyncedExterAppRecord"]) {
			$_SESSION["errorFromExternalApp"] = NULL;
		}

		$externalAppError = $_SESSION["errorFromExternalApp"];
		if (!empty($externalAppError)) {
			$viewer->assign('EXTERNALERRORISTHERE', true);
			$viewer->assign('EXTERNALERROR', $externalAppError);
		}

		if ($request->get('displayMode') == 'overlay') {
			$viewer->assign('SCRIPTS', $this->getOverlayHeaderScripts($request));
			echo $viewer->view('OverlayEditView.tpl', $moduleName);
		} else {
			$viewer->view('EditView.tpl', 'StockTransferOrders');
		}
	}

	/**
	 * Function to get the list of Script models to be included
	 * @param Vtiger_Request $request
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 */
	function getHeaderScripts(Vtiger_Request $request) {
		$headerScriptInstances = parent::getHeaderScripts($request);

		$moduleName = $request->getModule();
		$modulePopUpFile = 'modules.' . $moduleName . '.resources.Popup';
		$moduleEditFile = 'modules.' . $moduleName . '.resources.Edit';
		unset($headerScriptInstances[$modulePopUpFile]);
		unset($headerScriptInstances[$moduleEditFile]);

		$jsFileNames = array(
			'modules.Inventory.resources.Edit',
			'modules.Inventory.resources.Popup',
			'modules.PriceBooks.resources.Popup',
		);
		$jsFileNames[] = $moduleEditFile;
		$jsFileNames[] = $modulePopUpFile;
		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}

	public function getOverlayHeaderScripts(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$modulePopUpFile = 'modules.' . $moduleName . '.resources.Popup';
		$moduleEditFile = 'modules.' . $moduleName . '.resources.Edit';

		$jsFileNames = array(
			'modules.Inventory.resources.Popup',
			'modules.PriceBooks.resources.Popup',
		);
		$jsFileNames[] = $moduleEditFile;
		$jsFileNames[] = $modulePopUpFile;
		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		return $jsScriptInstances;
	}
}
