<?php
include_once('include/utils/GeneralUtils.php');
include_once('include/utils/GeneralConfigUtils.php');
class ServiceReports_Edit_View extends Inventory_Edit_View {

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

		if (empty($record) || $record != $_SESSION["lastSyncedExterAppRecord"]) {
			$_SESSION["errorFromExternalApp"] = NULL;
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
			$isSubmitted = $recordModel->get('is_submitted');
			$alreadyOrderSynced = $this->alreadyOrderSynced($recordModel->get('ticket_id'));
			if($isSubmitted == '1' && $alreadyOrderSynced == true){
				$viewer = new Vtiger_Viewer();
				$viewer->assign('MESSAGE', 'Service Report Is Already Submitted');
				$viewer->view('OperationNotPermitted.tpl', 'Vtiger');
				exit();
			}

			$currencyInfo = $recordModel->getCurrencyInfo();
			$taxes = $recordModel->getProductTaxes();
			$relatedProducts = $recordModel->getProducts();
			$viewer->assign('RECORD_ID', $record);
			$viewer->assign('MODE', 'edit');
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
		} elseif ($request->get('salesorder_id') || $request->get('quote_id')) {
			if ($request->get('salesorder_id')) {
				$referenceId = $request->get('salesorder_id');
			} else {
				$referenceId = $request->get('quote_id');
			}

			$parentRecordModel = Inventory_Record_Model::getInstanceById($referenceId);
			$currencyInfo = $parentRecordModel->getCurrencyInfo();
			$taxes = $parentRecordModel->getProductTaxes();
			$relatedProducts = $parentRecordModel->getProducts();
			$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
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

		$termsAndConditions = '';

		foreach ($requestFieldList as $fieldName => $fieldValue) {
			$fieldModel = $fieldList[$fieldName];
			if ($fieldModel->isEditable()) {
				$recordModel->set($fieldName, $fieldModel->getDBInsertValue($fieldValue));
			}
		}
		$sourceRecord = $request->get('sourceRecord');
		$sourceModule = $request->get('sourceModule');
		$ticketType = '';
		$nonEditKeys = [];
		$fM = $this->getMapping();
		if (!empty($sourceRecord)) {
			$recordInstnce = Vtiger_Record_Model::getInstanceById($sourceRecord, $sourceModule);
			foreach ($fM as $key => $value) {
				$aKey = $value['HelpDesk']['igFieldName'];
				$bKey = $value['ServiceReports']['igFieldName'];
				$recordModel->set($bKey, $recordInstnce->get($aKey));
				array_push($nonEditKeys, $bKey);
			}

			if (empty($recordModel->get('kilometer_reading'))) {
				$recordModel->set('kilometer_reading', '');
				$recordModel->set('kilo_date', '');
			}
			if (empty($recordModel->get('hmr'))) {
				$recordModel->set('hmr', '');
				$recordModel->set('sr_hmr', '');
			}

			$ticketType = $recordInstnce->get('ticket_type');
			$purpose = $recordInstnce->get('purpose');
			$recordModel->set('ticket_id', $sourceRecord);
			$viewer->assign('IMAGE_DETAILS', $recordInstnce->getImageDetails());

			$recId = $recordInstnce->get('smcreatorid');
			$db = PearDatabase::getInstance();
			$sql = 'select user_name from vtiger_users where id = ?';
			$sqlResult = $db->pquery($sql, array($recId));
			$dataRow = $db->fetchByAssoc($sqlResult, 0);

			$sql = 'select serviceengineerid from vtiger_serviceengineer' .
			' inner join vtiger_crmentity on vtiger_crmentity.crmid = vtiger_serviceengineer.serviceengineerid' .
			' where badge_no = ? and vtiger_crmentity.deleted= 0 ORDER BY serviceengineerid DESC LIMIT 1';
			$sqlResult = $db->pquery($sql, array($dataRow['user_name']));
			$dataRowEng = $db->fetchByAssoc($sqlResult, 0);
			$equipmentKays = array(
				'badge_no', 'ser_eng_name', 'sr_designaion',
				'sr_regional_office', 'dist_off_or_act_cen', 'reported_by', 'ticket_id'
			);
			$nonEditKeys = array_merge($equipmentKays, $nonEditKeys);
			if (!empty($dataRowEng) && isRecordExists($dataRowEng['serviceengineerid'])) {
				$recordModel->set('reported_by', $dataRowEng['serviceengineerid']);
			} else {
				$contactId = $recordInstnce->get('contact_id');
				$recordModel->set('reported_by', $contactId);
			}
			$equipmentKays = array('area_name', 'project_name');
			$nonEditKeys = array_merge($equipmentKays, $nonEditKeys);
			$recId = $recordInstnce->get('func_loc_id');
			if (!empty($recId) && isRecordExists($recId)) {
				$recInstance = Vtiger_Record_Model::getInstanceById($recId, 'FunctionalLocations');
				$recordModel->set('area_name', $recInstance->get('func_area_name'));
				$recordModel->set('project_name', $recInstance->get('func_proj_name'));
			}
			$equipmentId = $recordInstnce->get('equipment_id');
			$equipmentKays = array(
				'dte_of_commissing', 'type_of_conrt', 'run_year_cont',
				'cont_start_date', 'cont_end_date', 'sr_war_status',
				'sr_eq_warranty_terms', 'warranty_end_dte'
			);
			$aggregates = array(
				'sr_engine', 'sr_engine_wt', 'sr_transmission',
				'sr_transmission_wt', 'sr_final_drive', 'sr_final_drive_wt',
				'sr_rear_axle', 'sr_rear_axle_wt', 'sr_chassis', 'sr_chassis_wt',
				'eng_sl_no', 'motor_sl_no', 'trans_sl_no'
			);
			$nonEditKeys = array_merge($equipmentKays, $nonEditKeys);
			$nonEditKeys = array_merge($aggregates, $nonEditKeys);
			if (!empty($recId) && $equipmentId != '0' && isRecordExists($recId)) {
				$recInstance = Vtiger_Record_Model::getInstanceById($equipmentId, 'Equipment');
				$recordModel->set('dte_of_commissing', $recInstance->get('cust_begin_guar'));
				$recordModel->set('sr_eq_warranty_terms', $recInstance->get('equip_war_terms'));
				$recordModel->set('warranty_end_dte', $recInstance->get('cust_war_end'));
				$recordModel->set('type_of_conrt', $recInstance->get('eq_type_of_conrt'));
				$recordModel->set('run_year_cont', $recInstance->get('run_year_cont'));
				$recordModel->set('cont_start_date', $recInstance->get('cont_start_date'));
				$recordModel->set('cont_end_date', $recInstance->get('cont_end_date'));
				$recordModel->set('sr_war_status', $recInstance->get('eq_run_war_st'));

				//Implement Aggregates AutoFilllig
				$equipmentSerialNum = $recInstance->get('equipment_sl_no');
				$AgDetail = IGgetAggregateDetailsImproved('Engine', $equipmentSerialNum, $equipmentId);
				if (!empty($AgDetail)) {
					$recordModel->set('sr_engine', $AgDetail['equipment_sl_no']);
					$recordModel->set('sr_engine_wt', $AgDetail['equip_war_terms']);
					$recordModel->set('eng_sl_no', $AgDetail['equip_ag_serial_no']);
				}
				// $AgDetail = getAggregateDetailsBasedOnCode('TM', $equipmentSerialNum, $equipmentId);
				$AgDetail = IGgetAggregateDetailsImproved('Transmission', $equipmentSerialNum, $equipmentId);
				if (!empty($AgDetail)) {
					$recordModel->set('sr_transmission', $AgDetail['equipment_sl_no']);
					$recordModel->set('sr_transmission_wt', $AgDetail['equip_war_terms']);
					$recordModel->set('trans_sl_no', $AgDetail['equip_ag_serial_no']);
				}
				// $AgDetail = getAggregateDetailsBasedOnCode('FD', $equipmentSerialNum, $equipmentId);
				$AgDetail = IGgetAggregateDetailsImproved('FinalDrive', $equipmentSerialNum, $equipmentId);
				if (!empty($AgDetail)) {
					$recordModel->set('sr_final_drive', $AgDetail['equipment_sl_no']);
					$recordModel->set('sr_final_drive_wt', $AgDetail['equip_war_terms']);
				}
				$AgDetail = IGgetAggregateDetailsImproved('RearAxle', $equipmentSerialNum, $equipmentId);
				// $AgDetail = getAggregateDetailsBasedOnCode('RA', $equipmentSerialNum, $equipmentId);
				if (!empty($AgDetail)) {
					$recordModel->set('sr_rear_axle', $AgDetail['equipment_sl_no']);
					$recordModel->set('sr_rear_axle_wt', $AgDetail['equip_war_terms']);
				}
				$AgDetail = IGgetAggregateDetailsImproved('Chassis', $equipmentSerialNum, $equipmentId);
				// $AgDetail = getAggregateDetailsBasedOnCode('CH', $equipmentSerialNum, $equipmentId);
				if (!empty($AgDetail)) {
					$recordModel->set('sr_chassis', $AgDetail['equipment_sl_no']);
					$recordModel->set('sr_chassis_wt', $AgDetail['equip_war_terms']);
				}

				// $dataAg = IGgetAggregateDetailsImproved('Engine', $equipmentSerialNum, $equipmentId);
				// // $dataAg = getAggregateDetails('EN', $equipmentSerialNum, $equipmentId);
				// if (!empty($dataAg)) {
				// 	$recordModel->set('eng_sl_no', $dataAg['equip_ag_serial_no']);
				// }
				// $dataAg = IGgetAggregateDetailsImproved('Engine', $equipmentSerialNum, $equipmentId);
				// $dataAg = getAggregateDetails('TM', $equipmentSerialNum, $equipmentId);
				// if (!empty($dataAg)) {
				// 	$recordModel->set('trans_sl_no', $dataAg['equip_ag_serial_no']);
				// }
				// $dataAg = getAggregateDetails('Motor', $equipmentSerialNum, $equipmentId);
				$dataAg = IGgetAggregateDetailsImproved('InductionMotor', $equipmentSerialNum, $equipmentId);
				if (!empty($dataAg)) {
					$recordModel->set('motor_sl_no', $dataAg['equip_ag_serial_no']);
				}
			}
			$recId = $recordInstnce->get('assigned_user_id');
			$db = PearDatabase::getInstance();
			$sql = 'select user_name from vtiger_users where id = ?';
			$sqlResult = $db->pquery($sql, array($recId));
			$dataRow = $db->fetchByAssoc($sqlResult, 0);

			$sql = 'select serviceengineerid from vtiger_serviceengineer' .
                ' inner join vtiger_crmentity on vtiger_crmentity.crmid = vtiger_serviceengineer.serviceengineerid' .
                ' where badge_no = ? and vtiger_crmentity.deleted= 0 ORDER BY serviceengineerid DESC LIMIT 1';
            $sqlResult = $db->pquery($sql, array($dataRow['user_name']));
            $dataRow = $db->fetchByAssoc($sqlResult, 0);
			
			if (!empty($dataRow) && isRecordExists($dataRow['serviceengineerid'])) {
                $recInstance = Vtiger_Record_Model::getInstanceById($dataRow['serviceengineerid'], 'ServiceEngineer');
                $recordModel->set('badge_no', $recInstance->get('badge_no'));
                $recordModel->set('ser_eng_name', $recInstance->get('service_engineer_name'));
                $recordModel->set('sr_designaion', $recInstance->get('designaion'));
                $recordModel->set('sr_regional_office', $recInstance->get('regional_office'));
				$officeValue = $recInstance->get('office');
				if ($officeValue == 'Activity Centre') {
					$recordModel->set('dist_off_or_act_cen', $recInstance->get('activity_centre'));
				} else if ($officeValue == 'District Office') {
					$recordModel->set('dist_off_or_act_cen', $recInstance->get('district_office'));
				}
            }

			// SAP Service Notification Type  auto set 
			$SAPDefalutValue = getSAPBasedOnType($ticketType, $purpose);
			if (!empty($SAPDefalutValue)) {
				$recordModel->set('fail_de_sap_noti_type',  $SAPDefalutValue);
			}
		} else {
			$ticketType = $recordModel->get('sr_ticket_type');
			$purpose = $recordModel->get('tck_det_purpose');
		}

		$recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_EDIT);
		foreach ($fM as $key => $value) {
			$bKey = $value['ServiceReports']['igFieldName'];
			array_push($nonEditKeys, $bKey);
		}
		$equipmentKays = array(
			'badge_no', 'ser_eng_name', 'sr_designaion',
			'sr_regional_office', 'dist_off_or_act_cen', 'reported_by', 'ticket_id'
		);
		$nonEditKeys = array_merge($equipmentKays, $nonEditKeys);
		$equipmentKays = array('area_name', 'project_name');
		$nonEditKeys = array_merge($equipmentKays, $nonEditKeys);
		$nonEditKeys = array_merge($equipmentKays, $nonEditKeys);
		$equipmentKays = array(
			'dte_of_commissing', 'type_of_conrt', 'run_year_cont',
			'cont_start_date', 'cont_end_date', 'sr_war_status',
			'sr_eq_warranty_terms', 'warranty_end_dte'
		);
		$aggregates = array(
			'sr_engine', 'sr_engine_wt', 'sr_transmission',
			'sr_transmission_wt', 'sr_final_drive', 'sr_final_drive_wt',
			'sr_rear_axle', 'sr_rear_axle_wt', 'sr_chassis', 'sr_chassis_wt',
			'eng_sl_no', 'motor_sl_no', 'trans_sl_no'
		);
		$nonEditKeys = array_merge($equipmentKays, $nonEditKeys);
		$nonEditKeys = array_merge($aggregates, $nonEditKeys);
		$nonEditKeys = array_unique($nonEditKeys);
		$nonEditKeys = array_values($nonEditKeys);
		if ($ticketType == 'PRE-DELIVERY' || $ticketType == 'ERECTION AND COMMISSIONING') {
			$predeliveryAllowedKeys = array(
				'eng_sl_no', 'motor_sl_no', 'trans_sl_no', 'hmr',
				'kilo_date', 'sr_hmr', 'kilometer_reading', 'sr_equip_status'
			);
			$nonEditKeys = array_diff($nonEditKeys, $predeliveryAllowedKeys);
			$nonEditKeys = array_values($nonEditKeys);
		}
		$generalAllowedKeys = array(
			'hmr', 'kilometer_reading'
		);
		$nonEditKeys = array_diff($nonEditKeys, $generalAllowedKeys);
		$nonEditKeys = array_values($nonEditKeys);
		if (!empty($record) && $moduleName == 'RecommissioningReports') {
			array_push($nonEditKeys, 'fail_de_sap_noti_type');
		}
		$viewer->assign('NONEDITABLEKEYS', $nonEditKeys);
		$viewer->assign('VIEW_MODE', "fullForm");

		$isRelationOperation = $request->get('relationOperation');

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

		$recordStructure = $recordStructureInstance->getStructure($ticketType, $purpose);

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
		$viewer->assign('MAX_UPLOAD_SIZE', Vtiger_Util_Helper::getMaxUploadSizeInBytes());
		$viewer->assign('TAX_REGIONS', $taxRegions);
		$viewer->assign('DEFAULT_TAX_REGION_INFO', $defaultRegionInfo);
		$viewer->assign('INVENTORY_CHARGES', Inventory_Charges_Model::getInventoryCharges());
		$viewer->assign('RELATED_PRODUCTS', $relatedProducts);

		if (empty($record)) {
			$subAssemblies = $recordInstnce->get('sub_assembly');
			$subAssemblies = explode('|##|', $subAssemblies);
			$relatedProductsAnother = $recordModel->getProductsOther();
			$i = 1;
			foreach ($subAssemblies as $subAssembly) {
				if ($i == 1) {
					$relatedProductsAnother[1]['sad_su_ass_srp1'] = $subAssembly;
				} else {
					array_push($relatedProductsAnother, array('sad_su_ass_srp' . $i => $subAssembly));
				}
				$i = $i + 1;
			}
			$viewer->assign('RELATED_PRODUCTS_OTHER', $relatedProductsAnother);
		} else {
			$viewer->assign('RELATED_PRODUCTS_OTHER', $recordModel->getProductsOther());
		}

		$prdoducteditDisabledTypes = [
			'INSTALLATION OF SUB ASSEMBLY FITMENT',
			'PERIODICAL MAINTENANCE',
			'SERVICE FOR SPARES PURCHASED'
		];
		$viewer->assign('PRODUCTEDITDISABLEDTYPES', $prdoducteditDisabledTypes);
		$viewer->assign('DEDUCTED_TAXES', $deductTaxes);
		$viewer->assign('TAXES', $taxes);
		$viewer->assign('TAX_TYPE', $taxType);
		$viewer->assign('CURRENCINFO', $currencyInfo);
		$viewer->assign('CURRENCIES', $currencies);
		$viewer->assign('TERMSANDCONDITIONS', $termsAndConditions);
		$viewer->assign('REPORTTYPE', $purpose);
		$viewer->assign('SERVICEREPORTTYPE', $ticketType);

		//ToDO Generalise following
		$BLOCKNAMEFROMDEPENDENCY = 'Shortages_And_Damages';
		if ($ticketType == 'INSTALLATION OF  SUB ASSEMBLY FITMENT') {
			$BLOCKNAMEFROMDEPENDENCY = 'Sub_Assembly_Details';
		} else {
			$BLOCKNAMEFROMDEPENDENCY = 'Shortages_And_Damages';
		}
		$viewer->assign('BLOCKNAMEFROMDEPENDENCY', $BLOCKNAMEFROMDEPENDENCY);
		$ExtraLineItemRequiredTypes = [
			'ERECTION AND COMMISSIONING', 'PRE-DELIVERY',
			'INSTALLATION OF SUB ASSEMBLY FITMENT', 'PERIODICAL MAINTENANCE'
		];
		$viewer->assign('EXTRALINEITEMREQUIREDTYPES', $ExtraLineItemRequiredTypes);
		$ExtraLineItemRequiredSubTypes = ['WARRANTY CLAIM FOR SUB ASSEMBLY / OTHER SPARE PARTS'];
		$viewer->assign('EXTRALINEITEMREQUIREDSUBTYPES', $ExtraLineItemRequiredSubTypes);
		$viewer->assign('ITEMBLOCKNAME', getBlockLableBasedOnType($ticketType, $purpose));
		$viewer->assign('ITEMBLOCKNAMEANOTHER', getSecondBlockLableBasedOnType($ticketType, $purpose));
		$productModuleModel = Vtiger_Module_Model::getInstance('Products');
		$viewer->assign('PRODUCT_ACTIVE', $productModuleModel->isActive());
		$viewer->assign('SERVICE_ACTIVE', false);

		global $adb;
		$tabId = getTabId($moduleName);
		$sql = "SELECT * FROM `vtiger_field` LEFT JOIN vtiger_blocks
         on vtiger_blocks.blockid = vtiger_field.block where vtiger_field.tabid = ? 
         and helpinfo = 'li_lg' and blocklabel = ? ORDER BY `vtiger_field`.`sequence` ASC;";
		$result = $adb->pquery($sql, array($tabId, 'LBL_ITEM_DETAILS'));
		$fields = [];
		$fieldNames = [];
		$dependentList = array('vendor_item', 'line_vendor_id');
		$fieldAllowed = getFieldsOfCategoryServiceReport($ticketType, $purpose);
		$pickListFields = [];
		while ($row = $adb->fetch_array($result)) {
			if (!in_array($row['fieldname'], $fieldAllowed)) {
				continue;
			}
			if ($row['uitype'] == '16') {
				array_push($pickListFields, $row['fieldname']);
				$row['picklistValues'] = getAllPickListValues($row['fieldname']);
			}
			if (in_array($row['fieldname'], $dependentList)) {
				$row['hideInitialDisplay'] = 'true';
			}
			array_push($fieldNames, $row['fieldname']);
			array_push($fields, $row);
		}
		$viewer->assign('LINEITEM_CUSTOM_FIELDNAMES', $fieldNames);
		$viewer->assign('LINEITEM_CUSTOM_PICK_FIELDS', $pickListFields);
		$viewer->assign('LINEITEM_CUSTOM_FIELDS', $fields);

		$sql = "SELECT * FROM `vtiger_field` LEFT JOIN vtiger_blocks
         on vtiger_blocks.blockid = vtiger_field.block where vtiger_field.tabid = ? 
         and helpinfo = 'li_lg' and blocklabel = ? ORDER BY `vtiger_field`.`sequence` ASC;";
		$result = $adb->pquery($sql, array($tabId, 'Shortages_And_Damages'));
		$fields = [];
		$fieldNames = [];
		$pickListFields = [];
		$dependentList = array(
			'sad_sub_ass_mon', 'sad_sub_ass_hmr', 'sad_sub_ass_km',
			'sad_war_term', 'sad_ag_sl_no', 'sad_whoa', 'sad_dof', 'sad_manu_name',
			'sad_war_start_con', 'sad_date_oracs'
		);
		while ($row = $adb->fetch_array($result)) {
			if (!in_array($row['fieldname'], $fieldAllowed)) {
				continue;
			}
			if ($row['fieldname'] == 'sad_whoa') {
				continue;
			}
			if ($row['uitype'] == '16' || $row['uitype'] == '999') {
				if ($row['uitype'] == '16') {
					array_push($pickListFields, $row['fieldname']);
				}
				$row['picklistValues'] = getAllPickListValues($row['fieldname']);
			}
			if (in_array($row['fieldname'], $dependentList)) {
				$row['hideInitialDisplay'] = 'true';
			}
			array_push($fieldNames, $row['fieldname']);
			array_push($fields, $row);
		}

		$viewer->assign('LINEITEM_CUSTOM_FIELDNAMES_OTHER', $fieldNames);
		$viewer->assign('LINEITEM_CUSTOM_OTHER_PICK_FIELDS', $pickListFields);
		$viewer->assign('LINEITEM_CUSTOM_FIELDS_OTHER', $fields);

		if ($ticketType == 'PRE-DELIVERY' || $ticketType == 'ERECTION AND COMMISSIONING') {
			$sql = "SELECT * FROM `vtiger_field` LEFT JOIN vtiger_blocks
			on vtiger_blocks.blockid = vtiger_field.block where vtiger_field.tabid = ? 
			and helpinfo = 'li_lg' and blocklabel = ? and presence != 1 ORDER BY `vtiger_field`.`sequence` ASC;";
			$result = $adb->pquery($sql, array($tabId, 'Major_Aggregates_Sl_No'));
			$fields = [];
			$fieldNames = [];
			$pickListFields = [];
			$dependentList = array('masn_other_manu');
			while ($row = $adb->fetch_array($result)) {
				if (!in_array($row['fieldname'], $fieldAllowed)) {
					continue;
				}
				if ($row['uitype'] == '16' || $row['uitype'] == '999') {
					array_push($pickListFields, $row['fieldname']);
					$row['picklistValues'] = getAllPickListValues($row['fieldname']);
				}
				if (in_array($row['fieldname'], $dependentList)) {
					$row['hideInitialDisplay'] = 'true';
				}
				array_push($fieldNames, $row['fieldname']);
				array_push($fields, $row);
			}
			$viewer->assign('LINEITEM_CUSTOM_FIELDNAMES_OTHER1', $fieldNames);
			$viewer->assign('LINEITEM_CUSTOM_OTHER_PICK_FIELDS1', $pickListFields);
			$viewer->assign('LINEITEM_CUSTOM_FIELDS_OTHER1', $fields);
			if (empty($record)) {
				$subAssemblies = getModelBasedAggregates($recordInstnce->get('sr_equip_model'));
				$i = 1;
				foreach ($subAssemblies as $subAssembly) {
					if ($i == 1) {
						$relatedProductsAnother[1]['masn_aggrregate1'] = $subAssembly;
						$relatedProductsAnother[1]['picklistValuesConfigured1'] = json_decode(decode_html(IGGetDependentValuesOfPickList($subAssembly, 'masn_manu')));
					} else {
						array_push($relatedProductsAnother, array('masn_aggrregate' . $i => $subAssembly, 'picklistValuesConfigured' . $i => json_decode(decode_html(IGGetDependentValuesOfPickList($subAssembly, 'masn_manu')))));
					}
					$i = $i + 1;
				}
				if (empty($subAssemblies)) {
					$viewer->assign('RELATED_PRODUCTS_OTHER1', []);
				} else {
					$viewer->assign('RELATED_PRODUCTS_OTHER1', $relatedProductsAnother);
				}
			} else {
				$viewer->assign('RELATED_PRODUCTS_OTHER1', $recordModel->getProductsOther2());
			}
		}

		if ($request->get('returnview')) {
			$request->setViewerReturnValues($viewer);
		}

		$externalAppError = $_SESSION["errorFromExternalApp"];
		if (!empty($externalAppError)) {
			$viewer->assign('EXTERNALERRORISTHERE', true);
			$viewer->assign('EXTERNALERROR', $externalAppError);
		}
		$viewer->assign('MANDATORYFIELDS', $this->getMandatoryFieldsBasedOnType($ticketType, $purpose));
		if ($request->get('displayMode') == 'overlay') {
			$viewer->assign('SCRIPTS', $this->getOverlayHeaderScripts($request));
			echo $viewer->view('OverlayEditView.tpl', $moduleName);
		} else {
			$viewer->view('EditView.tpl', 'ServiceReports');
		}
	}

	public function alreadyOrderSynced($id) {
        global $adb;
        $sql = 'select external_app_num from vtiger_troubletickets '
            . ' where vtiger_troubletickets.ticketid = ?';
        $sqlResult = $adb->pquery($sql, array($id));
        $num_rows = $adb->num_rows($sqlResult);
        if ($num_rows > 0) {
            $dataRow = $adb->fetchByAssoc($sqlResult, 0);
            if (empty($dataRow['external_app_num'])) {
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }

	public function getMandatoryFieldsBasedOnType($type, $purposeValue) {
        // if ($type == 'GENERAL INSPECTION' || $type == 'SERVICE FOR SPARES PURCHASED' ) {
        // 	$fieldDependeny = Vtiger_DependencyPicklist::getFieldsFitDependency('HelpDesk', 'purpose', 'ticketstatus');
        // 	$type = $purposeValue;
        // } else {
        $fieldDependeny = Vtiger_DependencyPicklist::getFieldsFitDependency('ServiceReports', 'sr_ticket_type', 'tck_det_purpose');
        // }
        foreach ($fieldDependeny['valuemapping'] as $valueMapping) {
            if ($valueMapping['sourcevalue'] == $type) {
                return $valueMapping['targetvalues'];
            }
        }
		return $fieldDependeny;
    }

	public function getMapping($editable = false) {
		if (!$this->mapping) {
			$db = PearDatabase::getInstance();
			$query = 'SELECT * FROM vtiger_convertpotentialmapping';
			if ($editable) {
				$query .= ' WHERE editable = 1';
			}

			$result = $db->pquery($query, array());
			$numOfRows = $db->num_rows($result);
			$mapping = array();
			for ($i = 0; $i < $numOfRows; $i++) {
				$rowData = $db->query_result_rowdata($result, $i);
				$mapping[$rowData['cfmid']] = $rowData;
			}

			$finalMapping = $fieldIdsList = array();
			foreach ($mapping as $mappingDetails) {
				array_push($fieldIdsList, $mappingDetails['potentialfid'], $mappingDetails['projectfid']);
			}
			$fieldLabelsList = array();
			if (!empty($fieldIdsList)) {
				$fieldLabelsList = $this->getFieldsInfo(array_unique($fieldIdsList));
			}
			foreach ($mapping as $mappingId => $mappingDetails) {
				$finalMapping[$mappingId] = array(
					'editable'	=> $mappingDetails['editable'],
					'HelpDesk'		=> $fieldLabelsList[$mappingDetails['potentialfid']],
					'ServiceReports'	=> $fieldLabelsList[$mappingDetails['projectfid']]
				);
			}

			$this->mapping = $finalMapping;
		}

		return $this->mapping;
	}

	public function getFieldsInfo($fieldIdsList) {
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT fieldid, fieldlabel, uitype, typeofdata, fieldname, tablename, tabid FROM vtiger_field WHERE fieldid IN (' . generateQuestionMarks($fieldIdsList) . ')', $fieldIdsList);
		$numOfRows = $db->num_rows($result);

		$fieldLabelsList = array();
		for ($i = 0; $i < $numOfRows; $i++) {
			$rowData = $db->query_result_rowdata($result, $i);

			$fieldInfo = array('id' => $rowData['fieldid'], 'label' => $rowData['fieldlabel']);
			$fieldModel = Settings_Leads_Field_Model::getCleanInstance();
			$fieldModel->set('uitype', $rowData['uitype']);
			$fieldModel->set('typeofdata', $rowData['typeofdata']);
			$fieldModel->set('name', $rowData['fieldname']);
			$fieldModel->set('table', $rowData['tablename']);
			$fieldInfo['igFieldName'] = $rowData['fieldname'];
			$fieldInfo['fieldDataType'] = $fieldModel->getFieldDataType();

			$fieldLabelsList[$rowData['fieldid']] = $fieldInfo;
		}
		return $fieldLabelsList;
	}
	public function getHeaderCss(Vtiger_Request $request) {
		$headerCssInstances = parent::getHeaderCss($request);

		$cssFileNames = array(
			"~layouts/" . Vtiger_Viewer::getDefaultLayoutName() . "/modules/Users/build/css/intlTelInput.css",
		);
		$cssInstances = $this->checkAndConvertCssStyles($cssFileNames);
		$headerCssInstances = array_merge($headerCssInstances, $cssInstances);

		return $headerCssInstances;
	}
}
