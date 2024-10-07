<?php
function getLineITemGeneralisedModules() {
	return array(
		'ServiceReports', 'StockTransferOrders', 'ServiceOrders',
		'FailedParts', 'SalesOrder', 'ReturnSaleOrders', 'RecommissioningReports'
	);
}

function IGGetRelventRegionalOfficeBasedOnType($releventRole) {
	$Neyveli = array('Neyveli' => array('Chennai', 'Kochi'));
	if (in_array($releventRole, $Neyveli['Neyveli'])) {
		return 'Neyveli';
	}
	$Neyveli = array('Mumbai' => array('Panjim', 'Pune', 'Ahmedabad', 'Udaipur'));
	if (in_array($releventRole, $Neyveli['Mumbai'])) {
		return 'Mumbai';
	}

	$Neyveli = array('Bilaspur' => array('Bilaspur Service Centre'));
	if (in_array($releventRole, $Neyveli['Bilaspur'])) {
		return 'Bilaspur';
	}

	$Neyveli = array('Singrauli' => array('Maihar', 'Singrauli Service Centre'));
	if (in_array($releventRole, $Neyveli['Singrauli'])) {
		return 'Singrauli';
	}

	$Neyveli = array('Nagpur' => array('Bhopal', 'Bhilai', 'Chandrapur'));
	if (in_array($releventRole, $Neyveli['Nagpur'])) {
		return 'Nagpur';
	}

	$Neyveli = array('Delhi' => array('Leh', 'Jammu'));
	if (in_array($releventRole, $Neyveli['Delhi'])) {
		return 'Delhi';
	}

	$Neyveli = array('Hyderabad' => array(
		'Ramagundam', 'Kothagudem', 'Vishakapatnam',
		'Bacheli', 'Hyderabad Service Centre'
	));
	if (in_array($releventRole, $Neyveli['Hyderabad'])) {
		return 'Hyderabad';
	}

	$Neyveli = array('Bangalore' => array('Hospet'));
	if (in_array($releventRole, $Neyveli['Bangalore'])) {
		return 'Bangalore';
	}

	$Neyveli = array('Kolkata' => array(
		'Itanagar', 'Silapathar', 'Guwahati', 'Asansol',
		'Kolkata Service Centre'
	));
	if (in_array($releventRole, $Neyveli['Kolkata'])) {
		return 'Kolkata';
	}

	$Neyveli = array('Sambalpur' => array('Bhubaneshwar'));
	if (in_array($releventRole, $Neyveli['Sambalpur'])) {
		return 'Sambalpur';
	}
}

function getAllRSOWithParentId($parentLineItem) {
	global $adb;
	$query = "select returnsaleordersid from vtiger_returnsaleorders " .
		" inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_returnsaleorders.returnsaleordersid " .
		" WHERE parent_line_itemid=? and vtiger_crmentity.deleted = 0";
	$result = $adb->pquery($query, array($parentLineItem));
	$recordIds = [];
	while ($row = $adb->fetch_array($result)) {
		array_push($recordIds, $row['returnsaleordersid']);
	}
	return $recordIds;
}

function getAllSOWithParentId($parentLineItem) {
	global $adb;
	$query = "select sum(final_qty) as totalqty from vtiger_inventoryproductrel " .
		" inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_inventoryproductrel.id " .
		" WHERE failedpart_lineid=? and vtiger_crmentity.deleted = 0";
	$result = $adb->pquery($query, array($parentLineItem));
	$totalQty = 0;
	while ($row = $adb->fetch_array($result)) {
		$totalQty = $row['totalqty'];
	}
	return $totalQty;
}

function CheckExitenseOFSTO($STONumber) {
	include_once('include/utils/GeneralUtils.php');
	if (empty($STONumber)) {
		$responseObject['success'] = false;
		$responseObject['message'] = "STO Number Is Empty";
	}
	$url = getExternalAppURL('CheckAndFetchSTOHistory');
	$header = array('Content-Type:multipart/form-data');
	$data = array(
		'IM_EBELN'  => $STONumber
	);
	$resource = curl_init();
	curl_setopt($resource, CURLOPT_URL, $url);
	curl_setopt($resource, CURLOPT_HTTPHEADER, $header);
	curl_setopt($resource, CURLOPT_POST, 1);
	curl_setopt($resource, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($resource, CURLOPT_POSTFIELDS, $data);
	$responseUnEncoded = curl_exec($resource);
	$response = json_decode($responseUnEncoded, true);
	curl_close($resource);
	$responseObject = [];
	$responseObject['success'] = false;
	$responseObject['message'] = "Not Able To Fetch Details";
	$jsonParseError = json_last_error();
	if (empty($jsonParseError)) {
		if ($response && $response['IT_RETURN'] && $response['IT_RETURN'][0]['MSGTYP'] == 'E') {
			$responseObject['success'] = false;
			$responseObject['message'] = $response['IT_RETURN'][0]['MESSAGE'];
		} else if ($response && $response['IT_RETURN'] == []) {
			$responseObject['success'] = true;
			$responseObject['data'] = $response['IT_HISTORY'];
			$responseObject['message'] = '';
		}
	} else {
		$responseObject['success'] = false;
		$responseObject['message'] = $responseUnEncoded;
	}
	return $responseObject;
}

function getAllLineItemsForEquipment($parentId, $module) {
	$result = null;
	global $adb;
	$tabId = getTabId($module);
	$fieldNames = [];
	$sql = "SELECT * FROM `vtiger_field` LEFT JOIN vtiger_blocks
	on vtiger_blocks.blockid = vtiger_field.block where vtiger_field.tabid = ? 
	and helpinfo = 'li_lg' and blocklabel = ? and presence != 1 ORDER BY `vtiger_field`.`sequence` ASC;";
	$result = $adb->pquery($sql, array($tabId, 'daadcp_lineblock'));
	while ($row = $adb->fetch_array($result)) {
		array_push($fieldNames, $row['fieldname']);
	}
	$query = "SELECT vtiger_inventoryproductrel_equipment.*
				FROM vtiger_inventoryproductrel_equipment
				WHERE id=?";
	$transactionSuccessful = vtws_runQueryAsTransaction($query, array($parentId), $result);
	if (!$transactionSuccessful) {
		throw new WebServiceException(WebServiceErrorCode::$DATABASEQUERYERROR, 'Database error while performing required operation');
	}
	$lineItemList = array();
	if ($result) {
		$rowCount =  $adb->num_rows($result);
		for ($i = 0; $i < $rowCount; ++$i) {
			$element = [];
			$rowElement =  $adb->query_result_rowdata($result, $i);
			foreach ($rowElement as $key => $value) {
				if (in_array($key, $fieldNames)) {
					if ($key == '0') {
						continue;
					}
					$element[$key] = $value;
				}
			}
			$lineItemList[] = $element;
		}
	}
	return $lineItemList;
}

function generalisedInsertIntoEquipMentAvailability($yMd, $totalbreakdown, $equipmentId, $rowData = []) {
	global $adb;
	$result = $adb->pquery("SELECT mdy,equipmentavailabilityid from vtiger_equipmentavailability 
        INNER JOIN vtiger_crmentity on vtiger_crmentity.crmid = vtiger_equipmentavailability.equipmentavailabilityid
        where equipment_id = ? and mdy = ? and deleted = 0", array($equipmentId, $yMd));
	$rowCount = $adb->num_rows($result);
	if ($rowCount > 0) {
		$dataRow = $adb->fetchByAssoc($result, 0);
		$recordModel = Vtiger_Record_Model::getInstanceById($dataRow['equipmentavailabilityid'], 'EquipmentAvailability');
		$recordModel->set('mode', 'edit');
		$recordModel->set('equipment_id', $equipmentId);
		$recordModel->set('run_year_cont', $rowData['run_year_cont']);
		$recordModel->set('shift_hours', $rowData['shift_hours']);
		$recordModel->set('cust_maint_hour_cons', $rowData['maint_h_app_for_ac']);
		$recordModel->set('commited_avl_m_w', $rowData['daadcp_avail_mon_percent']);
		$recordModel->set('type_of_eq_availability', $rowData['type_of_eq_availability']);
		$recordModel->set('commited_avl_y', $rowData['commited_avl_y']);
		$recordModel->set('commited_avl_m_w', $rowData['commited_avl_m_w']);
		$recordModel->save();
	} else {
		$focus = CRMEntity::getInstance('EquipmentAvailability');
		$focus->column_fields['equipment_id'] = $equipmentId;
		$focus->column_fields['shift_hours'] = $rowData['shift_hours'];
		$focus->column_fields['mdy'] = $yMd;
		$focus->column_fields['run_year_cont'] = $rowData['run_year_cont'];
		$focus->column_fields['commited_avl_m_w'] = $rowData['daadcp_avail_mon_percent'];
		$focus->column_fields['cust_maint_hour_cons'] = $rowData['maint_h_app_for_ac'];
		$focus->column_fields['type_of_eq_availability'] = $rowData['type_of_eq_availability'];
		$focus->save("EquipmentAvailability");
	}
}

function calculateEquipmentAvailabilty($equipmentId, $yMd, $createdDate, $rowData = []) {
	$date = $createdDate;
	$first_date_find = strtotime(date("Y-m-d", strtotime($date)) . ", first day of this month");
	$firstDate = date("Y-m-d", $first_date_find);
	$last_date_find = strtotime(date("Y-m-d", strtotime($date)) . ", last day of this month");
	$lastDate = date("Y-m-d", $last_date_find);
	global $adb;
	$sql = "SELECT  sum(TIMEDIFF(CONCAT(restoration_date,' ',restoration_time),
        CONCAT(date_of_failure,' ', failure_time))) as 'totalbreakdown' from `vtiger_servicereports`
        INNER JOIN vtiger_servicereportscf 
        on vtiger_servicereportscf.servicereportsid = vtiger_servicereports.servicereportsid
        INNER JOIN vtiger_crmentity 
        on vtiger_crmentity.crmid = vtiger_servicereports.servicereportsid 
        WHERE vtiger_servicereports.equipment_id = ? and deleted = 0 and createdtime BETWEEN '$firstDate' and '$lastDate' ";

	$sqlResult = $adb->pquery($sql, array($equipmentId));
	$dataRow = $adb->fetchByAssoc($sqlResult, 0);
	$totalbreakdown = '';
	if (!empty($dataRow)) {
		$totalbreakdown = $dataRow['totalbreakdown'] / 10000;
		generalisedInsertIntoEquipMentAvailability($yMd, $totalbreakdown, $equipmentId, $rowData);
	}
}

function IGgetAggregateDetailsImproved($aggregateName, $equipmentSerialNum, $parentEquipment) {
	global $adb;

	$EqApendedAggregates = [];
	array_push($EqApendedAggregates, $parentEquipment);
	$aggregateCodes = IGgetAggregateCodesBasedOnName($aggregateName);
	foreach ($aggregateCodes as $aggregateCode) {
		array_push($EqApendedAggregates, $equipmentSerialNum . '-' . $aggregateCode);
	}

	$sql = "SELECT equipment_sl_no,equip_war_terms,equip_ag_serial_no FROM `vtiger_equipment` 
	INNER JOIN vtiger_equipmentcf on vtiger_equipmentcf.equipmentid = vtiger_equipment.equipmentid
	where agg_equipment_id = ? and equipment_sl_no in (" . generateQuestionMarks($EqApendedAggregates) . ")";

	$params = array_merge(array($parentEquipment), $EqApendedAggregates);

	$sqlResult = $adb->pquery($sql, $params);
	$num_rows = $adb->num_rows($sqlResult);
	$dataRow = [];
	if ($num_rows > 0) {
		$dataRow = $adb->fetchByAssoc($sqlResult, 0);
	}
	return $dataRow;
}

function IGgetAggregateCodesBasedOnName($aggregateName) {
	$aggregateCodes = [];
	switch ($aggregateName) {
		case 'Chassis':
			$aggregateCodes = ['CH'];
			break;
		case 'Engine':
			$aggregateCodes = ['EN', 'ENG'];
			break;
		case 'Transmission':
			$aggregateCodes = ['TR', 'TM'];
			break;
		case 'RearAxle':
			$aggregateCodes = ['RA', 'RAX', 'REAX', 'FD'];
			break;
		case 'FinalDrive':
			$aggregateCodes = ['FD', 'FDS'];
			break;
		case 'FrontAxle':
			$aggregateCodes = ['FA-RH', 'FA-LH'];
			break;
		case 'RH Final Drive':
			$aggregateCodes = ['FDRH'];
			break;
		case 'LH Final Drive':
			$aggregateCodes = ['FDRH'];
			break;
		case 'InductionMotor':
			$aggregateCodes = ['MT', 'IM'];
			break;
		case 'TrackDrive':
			$aggregateCodes = ['TD', 'TDLH', 'TDRH'];
			break;
	}
	return $aggregateCodes;
}

function IGgetLastHMR($recordId) {
	global $adb;
	$sql = 'select eq_last_hmr from vtiger_equipment where equipmentid = ?';
	$sqlResult = $adb->pquery($sql, array(trim($recordId)));
	$num_rows = $adb->num_rows($sqlResult);
	if ($num_rows > 0) {
		$dataRow = $adb->fetchByAssoc($sqlResult, 0);
		return (float)$dataRow['eq_last_hmr'];
	} else {
		return 0;
	}
}

function CalculateContract($lastStatus, $id, $start, $end) {

	if (!empty($start) && !empty($end)) {
		$start = date_create($start);
		$end = date_create($end);
		$today     = new DateTime();
		$contractYear = "";
		$contractMonth = "";
		$contractMonthDays = 0;
		$totalYearOfContrcat = "";
		if ($today > $end) {
			$interval =  date_diff($start, $end);
			$contractYear = ""; //$interval->format('%y');
			$contractMonth = -1; //$interval->format('%m');
		} else {
			$interval = date_diff($start, $today);
			$contractYear = $interval->format('%y');
			$contractMonth = $interval->format('%m');
			$contractMonthDays = $interval->format('%d');
			$lastStatus = "Contract";
		}
		$totalDiff = date_diff($start, $end);
		$totalYearOfContrcat = $totalDiff->format('%y');
		$totalYearOfContrcatMonth = $totalDiff->format('%m');
		if ($totalYearOfContrcatMonth > 0) {
			$totalYearOfContrcat = $totalYearOfContrcat + 1;
		}
		if ($contractMonth > 0) {
			$contractYear = $contractYear + 1;
		} else if ($contractMonthDays > 0) {
			$contractYear = $contractYear + 1;
		}
		InsertDatabase($id, $contractYear, $totalYearOfContrcat, $lastStatus);
	}
}

function IgCalculateWarranty($id, $text, $currhmr, $warrentyStartDate, $currStatus) {
	if (empty($text)) {
		return;
	}
	$dataValues = IGgetMonthAndHMRBasedOnWarranty($text, $id);
	if (!empty($dataValues["month"]) && !empty($warrentyStartDate)) {
		$warrentyStartDate = date_create($warrentyStartDate);
		if (!empty($dataValues["month"])) {
			$warrentyEndDate  = $warrentyStartDate->modify("+" . $dataValues["month"] . ' month');
		}
		$warrentyEndDate = date_format($warrentyEndDate, "Y-m-d");
		if ($warrentyEndDate > date("Y-m-d")) {
			$currStatus = "Under Warranty";
			IGvalueUpdateDb($currStatus, $id, $warrentyEndDate);
		} else {
			$currStatus = "Outside Warranty";
			IGvalueUpdateDb($currStatus, $id, $warrentyEndDate);
		}
	}

	if (!empty($dataValues["hmr"])) {
		if (intval($dataValues["hmr"]) < intval($currhmr)) {
			$currStatus = "Outside Warranty";
			IGvalueUpdateDb($currStatus, $id, $warrentyEndDate);
		}
		if (empty($dataValues["month"]) && empty($dataValues["days"]) && empty($dataValues["year"]) && intval($dataValues["hmr"]) > intval($currhmr)) {
			$currStatus = "Under Warranty";
			IGvalueUpdateDb($currStatus, $id, $warrentyEndDate);
		}
	}
}

function IGgetMonthAndHMRBasedOnWarranty($warantyText, $id) {
	global $adb;
	$sql = "select * from `vtiger_warrantydetails`
		INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_warrantydetails.warrantydetailsid
		 where wr_warranty_description = ? and vtiger_crmentity.deleted = 0";
	$sqlResult = $adb->pquery($sql, array(decode_html($warantyText)));
	$num_rows = $adb->num_rows($sqlResult);

	if ($num_rows > 0) {
		$dataRow = $adb->fetchByAssoc($sqlResult, 0);
		$month = $dataRow['wr_warranty_in_mon'];
		$warrantyHours = $dataRow['wr_waranty_hours'];
		return array("month" => $month, "hmr" => $warrantyHours);
	} else {
		return [];
	}
}

function IGvalueUpdateDb($currStatus, $id, $warrantyEndDate) {
	$db = PearDatabase::getInstance();
	$db->pquery("UPDATE vtiger_equipment set eq_run_war_st=? , cust_war_end = ?
	 where equipmentid=?", array($currStatus, $warrantyEndDate, $id));
}

function InsertDatabase($id, $contractYear, $totalYearOfContrcat, $contractStatus) {
	$db = PearDatabase::getInstance();
	$db->pquery(
		"UPDATE vtiger_equipment set run_year_cont = ? , eq_run_war_st = ?, total_year_cont= ?
		  where equipmentid=?",
		array($contractYear, $contractStatus, $totalYearOfContrcat, $id)
	);
}

function IGgetAllLineItemsOtherForParent($parentId) {
	$db = PearDatabase::getInstance();
	$result = null;
	if (!is_array($parentId)) {
		$parentId = array($parentId);
	}
	$IGMODULE = vglobal('IGMODULE');
	$viewableFields = vglobal('VIEWABLEFIELDSLINE');
	$otherFields = array('productname', 'lineitem_id', 'sequence_no');
	$viewableFields = array_merge($viewableFields, $otherFields);
	$query = "SELECT vtiger_crmentity.label AS productname,vtiger_crmentity.setype 
					AS entitytype,vtiger_crmentity.deleted AS deleted, 
					vtiger_inventoryproductrel_other_masn.*
					FROM vtiger_inventoryproductrel_other_masn
					LEFT JOIN vtiger_crmentity 
					ON vtiger_crmentity.crmid = vtiger_inventoryproductrel_other_masn.productid
					WHERE id IN (" . generateQuestionMarks($parentId) . ")";
	$transactionSuccessful = vtws_runQueryAsTransaction($query, array($parentId), $result);
	if (!$transactionSuccessful) {
		throw new WebServiceException(WebServiceErrorCode::$DATABASEQUERYERROR, 'Database error while performing required operation');
	}
	$lineItemList = array();
	if ($result) {
		$rowCount = $db->num_rows($result);
		for ($i = 0; $i < $rowCount; ++$i) {
			$rowElement = $element = $db->query_result_rowdata($result, $i);
			$element['parent_id'] = $parentId;
			$productName = $element['productname'];
			$entityType = $element['entitytype'];
			$lineItemId =  $element['lineitem_id'];
			$element['product_name'] = $productName;
			$element['entity_type'] = $entityType;
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

function getTypeBlockSequence($type, $purposeValue) {
	$blockDetails = [];
	switch ($type) {
		case "BREAKDOWN":
			$blockDetails = [
				"Ticket Details", "Equipment Details",
				"Warranty / Contract Details", "VISUAL_CHECKS", "FAILURE_DETAILS",
				"LBL_ITEM_DETAILS", "ACTION_TAKEN", "Equipment_Status_after_Action_taken",
				"Restoration_Date", 'Off_Road',
				"GENERAL_CHECKS", "SER_ENGG_DETAIL",
				"SYSTEM INFORMATION"
			];
			break;
		case "GENERAL INSPECTION":
			$blockDetails = [
				"Ticket Details", "Equipment Details",
				"Warranty / Contract Details", "VISUAL_CHECKS", "GENERAL_CHECKS",
				"Service_Engineer_Observations",
				"LBL_ITEM_DETAILS", "ACTION_TAKEN", "Equipment_Status_after_Action_taken",
				"SER_ENGG_DETAIL",
				"SYSTEM INFORMATION"
			];
			break;
		case "PRE-DELIVERY":
			$blockDetails = [
				"Ticket Details", "Equipment Details",
				"Major_Aggregates_Sl_No",
				"VISUAL_CHECKS",  "Shortages_And_Damages",
				"LBL_ITEM_DETAILS",
				"Equipment_Commissioning_details",
				"FAILURE_DETAILS", "GENERAL_CHECKS",
				"ACTION_TAKEN", "Equipment_Status_after_Action_taken",
				"SER_ENGG_DETAIL",
				"SYSTEM INFORMATION"
			];
			break;
		case "ERECTION AND COMMISSIONING":
			$blockDetails = [
				"Ticket Details", "Equipment Details",
				"Major_Aggregates_Sl_No",
				"VISUAL_CHECKS",  "Shortages_And_Damages",
				"LBL_ITEM_DETAILS",
				"Equipment_Commissioning_details",
				"FAILURE_DETAILS", "GENERAL_CHECKS",
				"ACTION_TAKEN", "Equipment_Status_after_Action_taken",
				"SER_ENGG_DETAIL",
				"SYSTEM INFORMATION"
			];
			break;
		case "PERIODICAL MAINTENANCE":
			$blockDetails = [
				"Ticket Details", "Equipment Details",
				"Warranty / Contract Details", "Shortages_And_Damages",
				"Aggregate_Periodic_Maintenance_Details", "Service_Engineer_Observations",
				"LBL_ITEM_DETAILS", "ACTION_TAKEN",
				"VISUAL_CHECKS", "GENERAL_CHECKS",
				"Equipment_Status_after_Action_taken",
				"SER_ENGG_DETAIL",
				// "Equipment_Commissioning_details",
				// "FAILURE_DETAILS",
				"SYSTEM INFORMATION"
			];
			break;
		case "PREVENTIVE MAINTENANCE":
			$blockDetails = [
				"Ticket Details", "Equipment Details",
				"Warranty / Contract Details",
				"VISUAL_CHECKS", "GENERAL_CHECKS", "Service_Engineer_Observations",
				"LBL_ITEM_DETAILS", "ACTION_TAKEN",
				"Equipment_Status_after_Action_taken",
				"SER_ENGG_DETAIL",
				"SYSTEM INFORMATION"
			];
			break;
		case "INSTALLATION OF SUB ASSEMBLY FITMENT":
			$blockDetails = [
				"Ticket Details", "Shortages_And_Damages",
				"VISUAL_CHECKS",
				"Sub_Assembly_Commissioning_Fitment_details",
				"Service_Engineer_Observations", "LBL_ITEM_DETAILS",
				"ACTION_TAKEN",
				"Equipment_Status_after_Action_taken",
				"SER_ENGG_DETAIL",
				"SYSTEM INFORMATION",
				// "Sub_Assembly_Details",
				// "Warranty / Contract Details", 
				// "GENERAL_CHECKS",
				// "Sub_Assembly_Spares_Parts_Details",
				//  "Major_Aggregates_Sl_No",
				// "Design_Modification_Details",
				// "Equipment_Commissioning_details",
				// "Maintenance_Details",
				// "Aggregate_Periodic_Maintenance_Details",
				// "Warrrantable"
			];
			break;
		case "SERVICE FOR SPARES PURCHASED":
			if ($purposeValue == 'WARRANTY CLAIM FOR SUB ASSEMBLY / OTHER SPARE PARTS') {
				$blockDetails = [
					"Ticket Details", "Equipment Details",
					"Sub_Assembly_Details", "Shortages_And_Damages",
					"Warrrantable",
					"VISUAL_CHECKS", "GENERAL_CHECKS",
					"Service_Engineer_Observations",
					"LBL_ITEM_DETAILS", "ACTION_TAKEN",
					"Equipment_Status_after_Action_taken", "SER_ENGG_DETAIL",
					"SYSTEM INFORMATION",
					"Sub_Assembly_Spares_Parts_Details",
					"Major_Aggregates_Sl_No",
					"Design_Modification_Details",
					"Sub_Assembly_Commissioning_Fitment_details",
					"Equipment_Commissioning_details",
					"Maintenance_Details",
					"Aggregate_Periodic_Maintenance_Details",
				];
			} else if ($purposeValue == 'INSPECTION OF REJECTED SPARES') {
				$blockDetails = [
					"Ticket Details", "Sub_Assembly_Spares_Parts_Details",
					"Warranty / Contract Details", "VISUAL_CHECKS",
					"Service_Engineer_Observations",
					"LBL_ITEM_DETAILS", "ACTION_TAKEN", "Equipment_Status_after_Action_taken",
					"GENERAL_CHECKS", "SER_ENGG_DETAIL",
					"SYSTEM INFORMATION",
				];
			}
			break;
		case "DESIGN MODIFICATION":
			$blockDetails = [
				"Ticket Details", "Equipment Details",
				"Warranty / Contract Details",
				"Design_Modification_Details",
				"Service_Engineer_Observations",
				"LBL_ITEM_DETAILS",
				"VISUAL_CHECKS", "FAILURE_DETAILS",
				"ACTION_TAKEN", "Equipment_Status_after_Action_taken",
				"SER_ENGG_DETAIL",
				"SYSTEM INFORMATION"
			];
			break;
		default:
			$blockDetails = [
				"Ticket Details", "Equipment Details",
				"Warranty / Contract Details", "VISUAL_CHECKS", "FAILURE_DETAILS",
				"LBL_ITEM_DETAILS", "ACTION_TAKEN", "Equipment_Status_after_Action_taken",
				"GENERAL_CHECKS", "SER_ENGG_DETAIL",
				"SYSTEM INFORMATION",
				"Sub_Assembly_Spares_Parts_Details",
				"Sub_Assembly_Details", "Major_Aggregates_Sl_No",
				"Design_Modification_Details",
				"Sub_Assembly_Commissioning_Fitment_details",
				"Equipment_Commissioning_details", "Service_Engineer_Observations",
				"Maintenance_Details", "Shortages_And_Damages",
				"Aggregate_Periodic_Maintenance_Details",
				"Warrrantable"
			];
			break;
	}

	return $blockDetails;
}

function getSAPTypeNotDefiendeTypes() {
	return array(
		"PREVENTIVE MAINTENANCE",
		"INSTALLATION OF SUB ASSEMBLY FITMENT",
		"SERVICE FOR SPARES PURCHASED"
	);
}

function isSAPTypeIsNotDefined($type) {
	$notTypes = getSAPTypeNotDefiendeTypes();
	if (in_array($type, $notTypes)) {
		return true;
	} else {
		return false;
	}
}

function getValidatedRecivedQty($parentLineItem) {
    global $adb;
    $query = "select sum(rcvd_qty_validated) as totalqty from vtiger_inventoryproductrel " .
        " inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_inventoryproductrel.id " .
        " WHERE lineitem_id=? and vtiger_crmentity.deleted = 0";
    $result = $adb->pquery($query, array($parentLineItem));
    $totalQty = 0;
    while ($row = $adb->fetch_array($result)) {
        $totalQty = $row['totalqty'];
    }
    return $totalQty;
}