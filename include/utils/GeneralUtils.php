<?php

function getAllEquipmentsAssociatedWithContact($recordId, $searchKey) {
	global $adb;
	$functionalLocations = getAllAssociatedFunctionalLocations($recordId);
	if (empty($functionalLocations)) {
		return [];
	}
	$sql = 'select equipmentid,equipment_sl_no  from vtiger_equipment'
		. ' INNER JOIN vtiger_crmentity '
		. ' ON vtiger_crmentity.crmid = vtiger_equipment.equipmentid '
		. ' where vtiger_equipment.functional_loc IN ("' . implode('","', $functionalLocations) . '")'
		. ' AND equipment_sl_no  LIKE ? AND vtiger_crmentity.deleted = 0';
	$result = $adb->pquery($sql, array("%$searchKey%"));
	$recordIds = [];
	while ($row = $adb->fetch_array($result)) {
		array_push($recordIds, array('label' => $row['equipment_sl_no'], 'id' => '38x' . $row['equipmentid']));
	}
	return $recordIds;
}


function getAllAssociatedFunctionalLocations($recordId) {
	$recordId = explode('x', $recordId);
	$recordId = $recordId[1];
	$data = getRoleIdOfcustomer($recordId);
	$roles = $data[0];
	$accountId = $data[1];
	$belowRoleContactIds = getAllBelowRoleContacts($accountId, $roles);
	array_push($belowRoleContactIds, $recordId);
	global $adb;
	$sql = "SELECT `vtiger_crmentityrel`.relcrmid FROM `vtiger_crmentityrel` " .
		" inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_crmentityrel.relcrmid " .
		" where `vtiger_crmentityrel`.crmid  in (" . generateQuestionMarks($belowRoleContactIds) . ") and relmodule =  'FunctionalLocations'  and vtiger_crmentity.deleted = 0";
	$result = $adb->pquery($sql, $belowRoleContactIds);
	$recordIds = [];
	while ($row = $adb->fetch_array($result)) {
		array_push($recordIds, $row['relcrmid']);
	}
	return $recordIds;
}

function getAllBelowRoleContacts($recordId, $roles) {
	if (empty($roles)) {
		return [];
	}
	global $adb;
	$sql = "SELECT `vtiger_contactdetails`.contactid FROM `vtiger_contactdetails` " .
		" inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_contactdetails.contactid " .
		" where con_role in (" . generateQuestionMarks($roles) . ") and accountid =  ?  and vtiger_crmentity.deleted = 0";
	$params = $roles;
	$params[] = $recordId;
	$result = $adb->pquery($sql, $params);
	$recordIds = [];
	while ($row = $adb->fetch_array($result)) {
		array_push($recordIds, $row['contactid']);
	}
	return $recordIds;
}

function getRoleIdOfcustomer($recordId) {
	// Todo On Confirmation
	global $adb;
	$sql = "select con_role,accountid from vtiger_contactdetails where contactid = ?";
	$result = $adb->pquery($sql, array($recordId));
	$roleName = '';
	$accountId = '';
	while ($row = $adb->fetch_array($result)) {
		$roleName =  $row['con_role'];
		$accountId =  $row['accountid'];
	}
	$sql = "SELECT parentrole FROM `vtiger_role` where rolename = ?";
	$result = $adb->pquery($sql, array($roleName));
	$parentRoleString = '';
	while ($row = $adb->fetch_array($result)) {
		$parentRoleString =  $row['parentrole'];
	}

	$sql = "SELECT rolename FROM `vtiger_role` where parentrole like ?";
	$result = $adb->pquery($sql, array("$parentRoleString::%"));
	$roles = [];
	while ($row = $adb->fetch_array($result)) {
		array_push($roles, $row['rolename']);
	}
	return array($roles, $accountId);
}
function getImageDetailsInUtils($recordId) {
	global $site_URL;
	$db = PearDatabase::getInstance();
	$imageDetails = array();
	if ($recordId) {
		$sql = "SELECT vtiger_attachments.*, vtiger_crmentity.setype FROM vtiger_attachments
			INNER JOIN vtiger_seattachmentsrel ON vtiger_seattachmentsrel.attachmentsid = vtiger_attachments.attachmentsid
			INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_attachments.attachmentsid
			WHERE vtiger_crmentity.setype In ('HelpDesk Attachment' , 'HelpDesk Image')  AND vtiger_seattachmentsrel.crmid = ?";
		$result = $db->pquery($sql, array($recordId));
		$count = $db->num_rows($result);
		for ($i = 0; $i < $count; $i++) {
			$imageId = $db->query_result($result, $i, 'attachmentsid');
			$imageIdsList[] = $db->query_result($result, $i, 'attachmentsid');
			$imagePathList[] = $db->query_result($result, $i, 'path');
			$storedname[] = $db->query_result($result, $i, 'storedname');
			$imageName = $db->query_result($result, $i, 'name');
			$fieldName[] = $db->query_result($result, $i, 'subject');
			$url = \Vtiger_Functions::getFilePublicURL($imageId, $imageName);
			$imageOriginalNamesList[] = urlencode(decode_html($imageName));
			$imageNamesList[] = $imageName;
			$imageUrlsList[] = $url;
			$descriptionOffield[] = $db->query_result($result, $i, 'description');
		}
		if (is_array($imageOriginalNamesList)) {
			$countOfImages = count($imageOriginalNamesList);
			for ($j = 0; $j < $countOfImages; $j++) {
				$imageDetails[] = array(
					'id' => $imageIdsList[$j],
					'orgname' => $imageOriginalNamesList[$j],
					'path' => $imagePathList[$j] . $imageIdsList[$j],
					'location' => $imagePathList[$j] . $imageIdsList[$j] . '_' . $storedname[$j],
					'name' => $imageNamesList[$j],
					'url' => $imageUrlsList[$j],
					'field' => $imageUrlsList[$j],
					'fieldNameFromDB' => $fieldName[$j],
					'descriptionOffield' => $descriptionOffield[$j]
				);
			}
		}
	}
	return $imageDetails;
}

function getFieldsOfCategoryGeneralised($type, $purposeValue) {
	if ($type == 'GENERAL INSPECTION' || $type == 'SERVICE FOR SPARES PURCHASED') {
		$fieldDependeny = Vtiger_DependencyPicklist::getFieldsFitDependency('HelpDesk', 'purpose', 'ticketstatus');
		$type = $purposeValue;
	} else {
		$fieldDependeny = Vtiger_DependencyPicklist::getFieldsFitDependency('HelpDesk', 'ticket_type', 'ticketpriorities');
	}
	foreach ($fieldDependeny['valuemapping'] as $valueMapping) {
		if ($valueMapping['sourcevalue'] == $type) {
			return $valueMapping['targetvalues'];
		}
	}
}

function IGalreadyRRReportGenerated($id) {
	global $adb;
	$sql = 'select recommissioningreportsid from vtiger_recommissioningreports'
		. ' INNER JOIN vtiger_crmentity '
		. ' ON vtiger_crmentity.crmid = vtiger_recommissioningreports.recommissioningreportsid '
		. ' where vtiger_recommissioningreports.ticket_id = ? and vtiger_crmentity.deleted = 0';
	$sqlResult = $adb->pquery($sql, array($id));
	$num_rows = $adb->num_rows($sqlResult);
	if ($num_rows > 0) {
		$data = $adb->fetchByAssoc($sqlResult, 0);
		return array('generatedServiceRepId' => '46x' . $data['recommissioningreportsid'], 'reportedGenerated' => true);
	} else {
		return array('generatedServiceRepId' => '', 'reportedGenerated' => false);
	}
}

function IGalreadyReportGenerated($id) {
	global $adb;
	$sql = 'select servicereportsid,is_recommisionreport from vtiger_servicereports'
	. ' INNER JOIN vtiger_crmentity '
		. ' ON vtiger_crmentity.crmid = vtiger_servicereports.servicereportsid '
		. ' where vtiger_servicereports.ticket_id = ? and vtiger_crmentity.deleted = 0';
	$sqlResult = $adb->pquery($sql, array($id));
	$num_rows = $adb->num_rows($sqlResult);
	if ($num_rows > 0) {
		$data = $adb->fetchByAssoc($sqlResult, 0);
		return array(
			'generatedServiceRepId' => '46x' . $data['servicereportsid'],
			'reportedGenerated' => true,
			'is_recommisionreport' => $data['is_recommisionreport']
		);
	} else {
		return array('generatedServiceRepId' => '', 'reportedGenerated' => false);
	}
}

function IGalreadyRecomisioningReportGenerated($id) {
	global $adb;
	$sql = 'select recommissioningreportsid from vtiger_recommissioningreports'
		. ' INNER JOIN vtiger_crmentity '
		. ' ON vtiger_crmentity.crmid = vtiger_recommissioningreports.recommissioningreportsid '
		. ' where vtiger_recommissioningreports.ticket_id = ? and vtiger_crmentity.deleted = 0';
	$sqlResult = $adb->pquery($sql, array($id));
	$num_rows = $adb->num_rows($sqlResult);
	if ($num_rows > 0) {
		$data = $adb->fetchByAssoc($sqlResult, 0);
		return array('generatedRRRepId' => '61x' . $data['recommissioningreportsid'], 'RRGenerated' => true);
	} else {
		return array('generatedRRRepId' => '', 'RRGenerated' => false);
	}
}

function IGalreadyServiceOrderGenerated($id) {
	global $adb;
	$sql = 'select serviceordersid from vtiger_serviceorders'
		. ' INNER JOIN vtiger_crmentity '
		. ' ON vtiger_crmentity.crmid = vtiger_serviceorders.serviceordersid '
		. ' where vtiger_serviceorders.ticket_id = ? and vtiger_crmentity.deleted = 0';
	$sqlResult = $adb->pquery($sql, array($id));
	$num_rows = $adb->num_rows($sqlResult);
	if ($num_rows > 0) {
		$data = $adb->fetchByAssoc($sqlResult, 0);
		return array('generatedServiceOrderId' => '54x' . $data['serviceordersid'], 'orderGenerated' => true);
	} else {
		return array('generatedServiceOrderId' => '', 'orderGenerated' => false);
	}
}

function getImageDetailsInUtilsServiceReports($recordId, $fieldNameFromTable) {
	$db = PearDatabase::getInstance();
	$imageDetails = array();
	if ($recordId) {
		if ($fieldNameFromTable == 'imagename') {
			$sql = "SELECT vtiger_attachments.*, vtiger_crmentity.setype FROM vtiger_attachments
			INNER JOIN vtiger_seattachmentsrel ON vtiger_seattachmentsrel.attachmentsid = vtiger_attachments.attachmentsid
			INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_attachments.attachmentsid
			WHERE vtiger_crmentity.setype In ('ServiceReports Attachment' , 'ServiceReports Image' , 'HelpDesk Attachment' , 'HelpDesk Image') 
			AND vtiger_attachments.subject = ?
			AND vtiger_seattachmentsrel.crmid = ?";
		} else {
			$sql = "SELECT vtiger_attachments.*, vtiger_crmentity.setype FROM vtiger_attachments
			INNER JOIN vtiger_seattachmentsrel ON vtiger_seattachmentsrel.attachmentsid = vtiger_attachments.attachmentsid
			INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_attachments.attachmentsid
			WHERE vtiger_crmentity.setype In ('ServiceReports Attachment' , 'ServiceReports Image') 
			AND vtiger_attachments.subject = ?
			AND vtiger_seattachmentsrel.crmid = ?";
		}
		$result = $db->pquery($sql, array($fieldNameFromTable, $recordId));
		$count = $db->num_rows($result);
		for ($i = 0; $i < $count; $i++) {
			$imageId = $db->query_result($result, $i, 'attachmentsid');
			$imageIdsList[] = $db->query_result($result, $i, 'attachmentsid');
			$imagePathList[] = $db->query_result($result, $i, 'path');
			$storedname[] = $db->query_result($result, $i, 'storedname');
			$imageName = $db->query_result($result, $i, 'name');
			$fieldName[] = $db->query_result($result, $i, 'subject');
			$url = \Vtiger_Functions::getFilePublicURL($imageId, $imageName);
			$imageOriginalNamesList[] = urlencode(decode_html($imageName));
			$imageNamesList[] = $imageName;
			$imageUrlsList[] = $url;
			$descriptionOffield[] = $db->query_result($result, $i, 'description');
		}
		if (is_array($imageOriginalNamesList)) {
			$countOfImages = count($imageOriginalNamesList);
			for ($j = 0; $j < $countOfImages; $j++) {
				$imageDetails[] = array(
					'id' => $imageIdsList[$j],
					'orgname' => $imageOriginalNamesList[$j],
					'path' => $imagePathList[$j] . $imageIdsList[$j],
					'location' => $imagePathList[$j] . $imageIdsList[$j] . '_' . $storedname[$j],
					'name' => $imageNamesList[$j],
					'url' => $imageUrlsList[$j],
					'field' => $imageUrlsList[$j],
					'fieldNameFromDB' => $fieldName[$j],
					'descriptionOffield' => $descriptionOffield[$j]
				);
			}
		}
	}
	return $imageDetails;
}

function getFieldsOfCategoryGeneralisedServiceReport($type, $purposeValue) {
	if ($type == 'SERVICE FOR SPARES PURCHASED') {
		$fieldDependeny = Vtiger_DependencyPicklist::getFieldsFitDependency('ServiceReports', 'tck_det_purpose', 'type_of_conrt');
		$type = $purposeValue;
	} else {
		$fieldDependeny = Vtiger_DependencyPicklist::getFieldsFitDependency('ServiceReports', 'sr_ticket_type', 'sr_war_status');
	}
	foreach ($fieldDependeny['valuemapping'] as $valueMapping) {
		if ($valueMapping['sourcevalue'] == $type) {
			return $valueMapping['targetvalues'];
		}
	}
}

function getFieldsOfCategoryServiceReport($type, $purposeValue) {
	if ($type == 'SERVICE FOR SPARES PURCHASED') {
		$fieldDependeny = Vtiger_DependencyPicklist::getFieldsFitDependency('ServiceReports', 'tck_det_purpose', 'type_of_conrt');
		$type = $purposeValue;
	} else {
		$fieldDependeny = Vtiger_DependencyPicklist::getFieldsFitDependency('ServiceReports', 'sr_ticket_type', 'sr_war_status');
	}
	foreach ($fieldDependeny['valuemapping'] as $valueMapping) {
		if ($valueMapping['sourcevalue'] == $type) {
			return $valueMapping['targetvalues'];
		}
	}
}

function getBlockLableBasedOnType($type, $purposeValue) {
	$blockLabel = '';
	switch ($type) {
		case 'BREAKDOWN':
			$blockLabel = 'LBL_ITEM_DETAILS';
			break;
		case 'GENERAL INSPECTION':
			$blockLabel = 'Parts Recommendation';
			break;
		case 'PRE-DELIVERY':
			$blockLabel = 'PARTS_REQUIREMENT';
			break;
		case 'ERECTION AND COMMISSIONING':
			$blockLabel = 'PARTS_REQUIREMENT';
			break;
		case 'INSTALLATION OF SUB ASSEMBLY FITMENT':
			$blockLabel = 'Shortages/Damages if any';
			break;
		case 'SERVICE FOR SPARES PURCHASED':
			$blockLabel = 'Parts Details';
			break;
		case 'DESIGN MODIFICATION':
			$blockLabel = 'PARTS_REQUIREMENT';
			break;
		default:
			$blockLabel = 'Parts Recommendation';
			break;
	}
	return $blockLabel;
}

function getSecondBlockLableBasedOnType($type, $purposeValue) {
	$blockLabel = 'Shortages Or Damages';
	switch ($type) {
		case 'BREAKDOWN':
			break;
		case 'GENERAL INSPECTION':
			break;
		case 'PRE-DELIVERY':
			break;
		case 'ERECTION AND COMMISSIONING':
			break;
		case 'INSTALLATION OF SUB ASSEMBLY FITMENT':
			$blockLabel = 'Sub Assembly Details';
			break;
		case 'SERVICE FOR SPARES PURCHASED':
			$blockLabel = 'Sub Assembly Details';
			break;
		case 'PERIODICAL MAINTENANCE':
			$blockLabel = 'Aggregate Periodic Maintenance Details';
			break;
		default:
			break;
	}
	return $blockLabel;
}
function getSAPBasedOnType($type, $purposeValue) {
	$SAPDefalutValue = '';
	switch ($type) {
		case 'GENERAL INSPECTION':
			$SAPDefalutValue = 'Z3';
			break;
		case 'PRE-DELIVERY':
			$SAPDefalutValue = 'ZB';
			break;
		case 'ERECTION AND COMMISSIONING':
			$SAPDefalutValue = 'ZE';
			break;
		case 'DESIGN MODIFICATION':
			$SAPDefalutValue = 'Z2';
			break;
		case 'PERIODICAL MAINTENANCE':
			$SAPDefalutValue = 'Z4';
			break;
		default:
			$SAPDefalutValue = '';
			break;
	}
	return $SAPDefalutValue;
}

function configuredLineItemFieldsWithOutDepend($moduleName) {
	global $adb;
	$tabId = getTabId($moduleName);
	$sql = "SELECT * FROM `vtiger_field` LEFT JOIN vtiger_blocks
         on vtiger_blocks.blockid = vtiger_field.block where vtiger_field.tabid = ? 
         and helpinfo = 'li_lg' and blocklabel = ? ORDER BY `vtiger_field`.`sequence` ASC;";
	$result = $adb->pquery($sql, array($tabId, 'LBL_ITEM_DETAILS'));
	$fields = [];
	$fieldNames = [];

	while ($row = $adb->fetch_array($result)) {
		if ($row['uitype'] == '16') {
			$row['picklistValues'] = getAllPickListValues($row['fieldname']);
		}
		array_push($fieldNames, $row['fieldname']);
		array_push($fields, $row);
	}
	return array('fieldNames' => $fieldNames, 'fields' => $fields);
}

function configuredLineItemFieldsWithOutDependBLock($moduleName, $blockName) {
	global $adb;
	$tabId = getTabId($moduleName);
	$sql = "SELECT * FROM `vtiger_field` LEFT JOIN vtiger_blocks
         on vtiger_blocks.blockid = vtiger_field.block where vtiger_field.tabid = ? 
         and helpinfo = 'li_lg' and blocklabel = ? ORDER BY `vtiger_field`.`sequence` ASC;";
	$result = $adb->pquery($sql, array($tabId, $blockName));
	$fields = [];
	$fieldNames = [];

	while ($row = $adb->fetch_array($result)) {
		if ($row['uitype'] == '16') {
			$row['picklistValues'] = getAllPickListValues($row['fieldname']);
		}
		array_push($fieldNames, $row['fieldname']);
		array_push($fields, $row);
	}
	return array('fieldNames' => $fieldNames, 'fields' => $fields);
}

function configuredLineItemFieldsWithOutDependBLockEX($moduleName, $blockName, $excludedFields) {
	global $adb;
	$tabId = getTabId($moduleName);
	$sql = "SELECT * FROM `vtiger_field` LEFT JOIN vtiger_blocks
         on vtiger_blocks.blockid = vtiger_field.block where vtiger_field.tabid = ? 
         and helpinfo = 'li_lg' and blocklabel = ? ORDER BY `vtiger_field`.`sequence` ASC;";
	$result = $adb->pquery($sql, array($tabId, $blockName));
	$fields = [];
	$fieldNames = [];

	while ($row = $adb->fetch_array($result)) {
		if ($row['uitype'] == '16') {
			$row['picklistValues'] = getAllPickListValues($row['fieldname']);
		}
		if (in_array($row['fieldname'], $excludedFields)) {
			continue;
		}
		array_push($fieldNames, $row['fieldname']);
		array_push($fields, $row);
	}
	return array('fieldNames' => $fieldNames, 'fields' => $fields);
}

function getExternalAppURL($endPointName) {
	global $adb;
	$sql = 'select * from vtiger_external_application_info where id = 1 ';
	$sqlResultSet = $adb->pquery($sql, array());
	$dataRow = $adb->fetchByAssoc($sqlResultSet, 0);
	$u = $dataRow['uname'];
	$p = $dataRow['pass'];
	$url = $dataRow['url'];
	$url = $url . $endPointName . '.php' . "?uta=" . $u . '&pws=' . $p;
	return $url;
}

function changeNotifiationStatus($status, $notificationNumber, $id) {
	$imStatus = "";
	if ($status == 'In Progress') {
		$imStatus = 'I0070';
	} elseif ($status == 'Closed') {
		$imStatus = 'I0072';
	}
	if (empty($imStatus)) {
		$responseObject['success'] = true;
		return $responseObject;
	}
	if (empty($notificationNumber)) {
		$responseObject['success'] = false;
		$responseObject['message'] = "Notification Is Not Yet Created In SAP";
		return $responseObject;
	}
	$url = getExternalAppURL('ChangeStatus');
	$header = array('Content-Type:multipart/form-data');
	$dataOfReport = IGalreadyReportGenerated($id);
	$effectValue = '';
	if ($dataOfReport['reportedGenerated'] == true) {
		$reportId = $dataOfReport['generatedServiceRepId'];
		$reportId = explode('x', $reportId);
		$reportId = $reportId[1];

		$dataArr = getSingleColumnValue(array(
			'table' => 'vtiger_servicereportscf',
			'columnId' => 'servicereportsid',
			'idValue' => $reportId,
			'expectedColValue' => 'eq_sta_aft_act_taken'
		));
		$effectValue = getValueEffect($dataArr[0]['eq_sta_aft_act_taken']);
		// if (empty($effectValue)) {
		// 	$responseObject['success'] = false;
		// 	$responseObject['message'] = "Equipment Status After Action Taken Value Is Missing In Service Report";
		// 	return $responseObject;
		// }
	} else {
		$responseObject['success'] = false;
		$responseObject['message'] = "Notification Is Not Yet Created In SAP";
		return $responseObject;
	}

	$data = array(
		'IM_STATUS'  => $imStatus,
		'IM_EFFECT' => $effectValue,
		'IM_NOTIFICATION' => $notificationNumber
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
	$responseObject['message'] = "Not Able To Change Status";
	$jsonParseError = json_last_error();
	if (empty($jsonParseError)) {
		if ($response && $response['IT_RETURN'] && $response['IT_RETURN'][0]['MSGTYP'] == 'E') {
			$responseObject['success'] = false;
			$responseObject['message'] = $response['IT_RETURN'][0]['MESSAGE'];
		} else if ($response && $response['IT_RETURN'] && $response['IT_RETURN'][0]['MSGTYP'] == 'S') {
			$responseObject['success'] = true;
		}
	} else {
		$responseObject['success'] = false;
		$responseObject['message'] = $responseUnEncoded;
	}
	return $responseObject;
}

function changeNotifiationStatusWithRR($status, $notificationNumber, $id) {
	$imStatus = "";
	if ($status == 'In Progress') {
		$imStatus = 'I0070';
	} elseif ($status == 'Closed') {
		$imStatus = 'I0072';
	}
	if (empty($imStatus)) {
		$responseObject['success'] = true;
		return $responseObject;
	}
	if (empty($notificationNumber)) {
		$responseObject['success'] = false;
		$responseObject['message'] = "Notification Is Not Yet Created In SAP";
		return $responseObject;
	}
	$url = getExternalAppURL('ChangeStatus');
	$header = array('Content-Type:multipart/form-data');
	$dataOfReport = IGalreadyRRReportGenerated($id);
	$effectValue = '';
	if ($dataOfReport['reportedGenerated'] == true) {
		$reportId = $dataOfReport['generatedServiceRepId'];
		$reportId = explode('x', $reportId);
		$reportId = $reportId[1];

		$dataArr = getSingleColumnValue(array(
			'table' => 'vtiger_recommissioningreportscf',
			'columnId' => 'recommissioningreportsid',
			'idValue' => $reportId,
			'expectedColValue' => 'eq_sta_aft_act_taken'
		));
		$effectValue = getValueEffect($dataArr[0]['eq_sta_aft_act_taken']);
		if (empty($effectValue)) {
			$responseObject['success'] = false;
			$responseObject['message'] = "Equipment Status After Action Taken Value Is Missing In Service Report";
			return $responseObject;
		}
	} else {
		$responseObject['success'] = false;
		$responseObject['message'] = "Notification Is Not Yet Created In SAP";
		return $responseObject;
	}

	$data = array(
		'IM_STATUS'  => $imStatus,
		'IM_EFFECT' => $effectValue,
		'IM_NOTIFICATION' => $notificationNumber
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
	$responseObject['message'] = "Not Able To Change Status";
	$jsonParseError = json_last_error();
	if (empty($jsonParseError)) {
		if ($response && $response['IT_RETURN'] && $response['IT_RETURN'][0]['MSGTYP'] == 'E') {
			$responseObject['success'] = false;
			$responseObject['message'] = $response['IT_RETURN'][0]['MESSAGE'];
		} else if ($response && $response['IT_RETURN'] && $response['IT_RETURN'][0]['MSGTYP'] == 'S') {
			$responseObject['success'] = true;
		}
	} else {
		$responseObject['success'] = false;
		$responseObject['message'] = $responseUnEncoded;
	}
	return $responseObject;
}


function getValueEffect($value) {
	$code = '';
	switch ($value) {
		case "On Road":
			$code = 'B';
			break;
		case "Running with Problem":
			$code = 'C';
			break;
		case "Off Road":
			$code = 'A';
			break;
		default:
			$code = '';
	}
	return $code;
}

function getAllEquipmentsAssociatedWithSE($recordId, $searchKey, $model) {
	global $adb;
	$functionalLocations = getOnlyLinkedEquimentsSE($recordId);
	if (empty($functionalLocations)) {
		return [];
	}
	$sql = 'select equipmentid,equipment_sl_no  from vtiger_equipment'
		. ' INNER JOIN vtiger_crmentity '
		. ' ON vtiger_crmentity.crmid = vtiger_equipment.equipmentid '
		. ' where vtiger_equipment.equipmentid IN ("' . implode('","', $functionalLocations) . '")'
		. ' AND equipment_sl_no LIKE ? AND equip_category = "S" AND vtiger_crmentity.deleted = 0';

	$params = array("%$searchKey%");
	if(!empty($model)){
		$sql = $sql . '  AND equip_model = ? ';
		array_push($params , $model);
	}
	$result = $adb->pquery($sql, $params);
	$recordIds = [];
	while ($row = $adb->fetch_array($result)) {
		array_push($recordIds, array('label' => $row['equipment_sl_no'], 'value' => '38x' . $row['equipmentid']));
	}
	return $recordIds;
}

function getAllAssociatedFunctionalLocationsSE($recordId) {
	$recordId = explode('x', $recordId);
	$recordId = $recordId[1];
	$belowRoleContactIds = [];
	array_push($belowRoleContactIds, $recordId);
	global $adb;
	$sql = "SELECT `vtiger_crmentityrel`.relcrmid FROM `vtiger_crmentityrel` " .
		" inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_crmentityrel.relcrmid " .
		" where `vtiger_crmentityrel`.crmid  in (" . generateQuestionMarks($belowRoleContactIds) . ") and relmodule =  'FunctionalLocations'  and vtiger_crmentity.deleted = 0";
	$result = $adb->pquery($sql, $belowRoleContactIds);
	$recordIds = [];
	while ($row = $adb->fetch_array($result)) {
		array_push($recordIds, $row['relcrmid']);
	}
	return $recordIds;
}

function getOnlyLinkedEquimentsSE($recordId) {
	$recordId = explode('x', $recordId);
	$recordId = $recordId[1];
	$belowRoleContactIds = [];
	array_push($belowRoleContactIds, $recordId);
	global $adb;
	$sql = "SELECT `vtiger_crmentityrel`.relcrmid FROM `vtiger_crmentityrel` " .
		" inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_crmentityrel.relcrmid " .
		" where `vtiger_crmentityrel`.crmid  in (" . generateQuestionMarks($belowRoleContactIds) . ") and relmodule =  'Equipment'  and vtiger_crmentity.deleted = 0";
	$result = $adb->pquery($sql, $belowRoleContactIds);
	$recordIds = [];
	while ($row = $adb->fetch_array($result)) {
		array_push($recordIds, $row['relcrmid']);
	}
	return $recordIds;
}

function getAllAssociatedFunctionalLocationsSELInkedInEmployee($recordId, $functionalId) {
	global $adb;
	$sql = "SELECT `vtiger_equipment`.equipmentid FROM `vtiger_crmentityrel` " .
		" inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_crmentityrel.relcrmid " .
		" inner join vtiger_equipment on vtiger_equipment.functional_loc=vtiger_crmentityrel.relcrmid " .
		" where `vtiger_crmentityrel`.crmid = ? and relmodule =  'FunctionalLocations' and relcrmid = ? 
		 and vtiger_crmentity.deleted = 0";
	$result = $adb->pquery($sql, array($recordId, $functionalId));
	$recordIds = [];
	while ($row = $adb->fetch_array($result)) {
		array_push($recordIds, $row['equipmentid']);
	}
	return $recordIds;
}

function getAllAssociatedEquipmentsBasedSELInkedInEmployeeFunc($relatedRecordId) {
	global $adb;
	$sql = 'select equipmentid from vtiger_equipment'
		. ' INNER JOIN vtiger_crmentity '
		. ' ON vtiger_crmentity.crmid = vtiger_equipment.equipmentid '
		. ' where vtiger_equipment.functional_loc = ? and equip_category = "S"'
		. ' and vtiger_crmentity.deleted = 0';
	$result = $adb->pquery($sql, array($relatedRecordId));
	$recordIds = [];
	while ($row = $adb->fetch_array($result)) {
		array_push($recordIds, $row['equipmentid']);
	}
	return $recordIds;
}

function getUserDetailsBasedOnEmployeeModuleG($badgeNo) {
	global $adb;
	// static $cacheOfBadges = array();
	// if (!isset($cacheOfBadges[$badgeNo]) && empty($cacheOfBadges[$badgeNo])) {
	$sql = 'select serviceengineerid,sub_service_manager_role,phone,cust_role,office,production_division,service_centre from vtiger_serviceengineer '
		. ' inner join vtiger_crmentity on vtiger_crmentity.crmid = vtiger_serviceengineer.serviceengineerid '
		. ' where vtiger_serviceengineer.badge_no = ? and vtiger_crmentity.deleted = 0';
	$sqlResult = $adb->pquery($sql, array($badgeNo));
	$num_rows = $adb->num_rows($sqlResult);

	if ($num_rows > 0) {
		$userData = '';
		while ($row = $adb->fetch_array($sqlResult)) {
			$userData =  $row;
		}
		// $cacheOfBadges[$badgeNo] = $userData;
		return $userData;
	} else {
		return false;
	}
	// } else {
	// 	$cacheOfBadges[$badgeNo];
	// }
}

function getUserIdDetailsBasedOnEmployeeModuleG($badgeNo) {
	global $adb;
	$sql = 'select vtiger_users.id from vtiger_serviceengineer '
		. ' inner join vtiger_crmentity on vtiger_crmentity.crmid = vtiger_serviceengineer.serviceengineerid '
		. ' inner join vtiger_users on vtiger_users.user_name = vtiger_serviceengineer.badge_no '
		. ' where vtiger_serviceengineer.badge_no = ? and vtiger_crmentity.deleted = 0';
	$sqlResult = $adb->pquery($sql, array($badgeNo));
	$num_rows = $adb->num_rows($sqlResult);
	if ($num_rows > 0) {
		$userData = '';
		while ($row = $adb->fetch_array($sqlResult)) {
			$userData =  $row;
		}
		return $userData;
	} else {
		return false;
	}
}

function getAggregateDetailsBasedOnCode($aggregateTypeCode, $equipmentSerialNum, $parentEquipmentId) {
	global $adb;
	$sql = "SELECT equipment_sl_no,equip_war_terms  FROM `vtiger_equipment` 
	INNER JOIN vtiger_equipmentcf on vtiger_equipmentcf.equipmentid = vtiger_equipment.equipmentid
	where  equipment_sl_no = ?";
	$sqlResult = $adb->pquery($sql, array($equipmentSerialNum . '-' . $aggregateTypeCode));
	$num_rows = $adb->num_rows($sqlResult);
	$dataRow = [];
	if ($num_rows > 0) {
		$dataRow = $adb->fetchByAssoc($sqlResult, 0);
	}
	return $dataRow;
}

function getAggregateDetails($aggregateType, $equipmentSerialNum, $parentEquipment) {
	global $adb;
	$sql = "SELECT equip_ag_serial_no FROM `vtiger_equipment` 
	INNER JOIN vtiger_equipmentcf on vtiger_equipmentcf.equipmentid = vtiger_equipment.equipmentid
	where equipment_sl_no = ? and agg_equipment_id = ?";
	$sqlResult = $adb->pquery($sql, array($equipmentSerialNum . '-' . $aggregateType, $parentEquipment));
	$num_rows = $adb->num_rows($sqlResult);
	$dataRow = [];
	if ($num_rows > 0) {
		$dataRow = $adb->fetchByAssoc($sqlResult, 0);
	}
	return $dataRow;
}

function IGisBadgeExits($badgeNo, $phone) {
	global $adb;
	$sql = 'select badge_no,phone,approval_status from vtiger_serviceengineer 
	inner join vtiger_crmentity on vtiger_crmentity.crmid = vtiger_serviceengineer.serviceengineerid
	where (badge_no = ? or phone = ?) and vtiger_crmentity.deleted = 0';
	$sqlResult = $adb->pquery($sql, array(trim($badgeNo), trim($phone)));
	$num_rows = $adb->num_rows($sqlResult);
	if ($num_rows > 0) {
		$foundduplicate = false;
		$firstEntryDetails = [];
		$i = 0;
		while ($row = $adb->fetch_array($sqlResult)) {
			if ($i == 0) {
				$firstEntryDetails = $row;
			}
			if ($row['approval_status'] != 'Rejected') {
				$foundduplicate = true;
				$firstEntryDetails = $row;
			}
			$i = $i + 1;
		}
		if ($foundduplicate == true) {
			return $firstEntryDetails;
		} else {
			'';
		}
	} else {
		return '';
	}
}

function RMandRSMCheck(Vtiger_Request $request) {
	$sub_service_manager_role = $request->get('sub_service_manager_role');
	$cust_role = $request->get('cust_role');
	$office = $request->get('office');
	if ($cust_role != 'Service Manager') {
		return false;
	}
	if ($office != 'Regional Office') {
		return false;
	}
	if (
		$sub_service_manager_role == 'Regional Manager' ||
		$sub_service_manager_role == 'Regional Service Manager'
	) {
	} else {
		return false;
	}
	global $adb;
	$sql = 'select 1 from vtiger_serviceengineer 
	inner join vtiger_crmentity on vtiger_crmentity.crmid = vtiger_serviceengineer.serviceengineerid
	where office = ? and regional_office = ?
	and cust_role = ? and sub_service_manager_role =?
	and approval_status = ? and vtiger_crmentity.deleted = 0';

	$regional_office = $request->get('regional_office');

	$sqlResult = $adb->pquery($sql, array(
		$office, $regional_office, $cust_role,
		$sub_service_manager_role,
		'Accepted'
	));
	$num_rows = $adb->num_rows($sqlResult);
	if ($num_rows > 0) {
		return true;
	} else {
		return false;
	}
}

function RMandRSMCheckMob(Mobile_API_Request $request) {
	$sub_service_manager_role = $request->get('sub_service_manager_role');
	$cust_role = $request->get('cust_role');
	$office = $request->get('office');
	if ($cust_role != 'Service Manager') {
		return false;
	}
	if ($office != 'Regional Office') {
		return false;
	}
	if (
		$sub_service_manager_role == 'Regional Manager' ||
		$sub_service_manager_role == 'Regional Service Manager'
	) {
	} else {
		return false;
	}
	global $adb;
	$sql = 'select 1 from vtiger_serviceengineer 
	inner join vtiger_crmentity on vtiger_crmentity.crmid = vtiger_serviceengineer.serviceengineerid
	where office = ? and regional_office = ?
	and cust_role = ? and sub_service_manager_role =?
	and approval_status = ? and vtiger_crmentity.deleted = 0';

	$regional_office = $request->get('regional_office');

	$sqlResult = $adb->pquery($sql, array(
		$office, $regional_office, $cust_role,
		$sub_service_manager_role,
		'Accepted'
	));
	$num_rows = $adb->num_rows($sqlResult);
	if ($num_rows > 0) {
		return true;
	} else {
		return false;
	}
}

function isInAllowedFunctionalLocation($recordId) {
	global $current_user;
	$data = getUserDetailsBasedOnEmployeeModuleG($current_user->user_name);
	$functionalLocations = getAllAssociatedFunctionalLocationsSE('36x' . $data['serviceengineerid']);
	if (empty($functionalLocations)) {
		return false;
	}
	global $adb;
	$sql = 'select equipmentid from vtiger_equipment'
		. ' INNER JOIN vtiger_crmentity '
		. ' ON vtiger_crmentity.crmid = vtiger_equipment.equipmentid '
		. ' where vtiger_equipment.functional_loc IN ("' . implode('","', $functionalLocations) . '")'
		. ' AND vtiger_equipment.equipmentid = ? and vtiger_crmentity.deleted = 0';
	$sqlResult = $adb->pquery($sql, array($recordId));
	$num_rows = $adb->num_rows($sqlResult);
	if ($num_rows > 0) {
		return true;
	} else {
		return false;
	}
}

function isInAllowedInLinkedEquipmentsContacts($contactId, $recordId) {
	global $adb;
	$sql = "SELECT `vtiger_crmentityrel`.relcrmid FROM `vtiger_crmentityrel` " .
	" inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_crmentityrel.relcrmid " .
	" where `vtiger_crmentityrel`.crmid = ? and relmodule = 'Equipment'  
		and relcrmid = ?
		and vtiger_crmentity.deleted = 0";
	$result = $adb->pquery($sql, array($contactId, $recordId));
	$num_rows = $adb->num_rows($result);
	if ($num_rows > 0) {
		return true;
	} else {
		return false;
	}
}

function isInAllowedInBGFunctionalLocation($recordId) {
	global $current_user;
	$data = getUserDetailsBasedOnEmployeeModuleG($current_user->user_name);
	$functionalLocations = getAllAssociatedFunctionalLocationsSE('36x' . $data['serviceengineerid']);
	if (empty($functionalLocations)) {
		return false;
	}
	global $adb;
	$sql = 'select bankguaranteeid from vtiger_bankguarantee'
		. ' INNER JOIN vtiger_crmentity '
		. ' ON vtiger_crmentity.crmid = vtiger_bankguarantee.bankguaranteeid '
		. ' where vtiger_bankguarantee.functional_loc IN ("' . implode('","', $functionalLocations) . '")'
		. ' AND vtiger_bankguarantee.bankguaranteeid = ? and vtiger_crmentity.deleted = 0';
	$sqlResult = $adb->pquery($sql, array($recordId));
	$num_rows = $adb->num_rows($sqlResult);
	if ($num_rows > 0) {
		return true;
	} else {
		return false;
	}
}

function isInAllowedInFuncFunctionalLocation($recordId) {
	global $current_user;
	$data = getUserDetailsBasedOnEmployeeModuleG($current_user->user_name);
	global $adb;
	$sql = "SELECT `vtiger_crmentityrel`.relcrmid FROM `vtiger_crmentityrel` " .
	" inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_crmentityrel.relcrmid " .
	" where `vtiger_crmentityrel`.crmid = ? and relmodule =  'FunctionalLocations'  
		and relcrmid = ?
		and vtiger_crmentity.deleted = 0";
	$result = $adb->pquery($sql, array($data['serviceengineerid'], $recordId));
	$num_rows = $adb->num_rows($result);
	if ($num_rows > 0) {
		return true;
	} else {
		return false;
	}
}

function isInAllowedInLinekedEquipments($recordId) {
	global $current_user;
	$data = getUserDetailsBasedOnEmployeeModuleG($current_user->user_name);
	$functionalLocations = getOnlyLinkedEquimentsSE('36x' . $data['serviceengineerid']);
	if (empty($functionalLocations)) {
		return false;
	}
	global $adb;
	$sql = 'select equipmentid from vtiger_equipment'
		. ' INNER JOIN vtiger_crmentity '
		. ' ON vtiger_crmentity.crmid = vtiger_equipment.equipmentid '
		. ' where vtiger_equipment.equipmentid IN ("' . implode('","', $functionalLocations) . '")'
		. ' AND vtiger_equipment.equipmentid = ? and vtiger_crmentity.deleted = 0';
	$sqlResult = $adb->pquery($sql, array($recordId));
	$num_rows = $adb->num_rows($sqlResult);
	if ($num_rows > 0) {
		return true;
	} else {
		return false;
	}
}

function getModelBasedAggregates($model) {
	if (empty($model)) {
		return [];
	}

	global $adb;
	$sql = 'SELECT masn_aggrregate FROM `vtiger_modelaggregateconfig` '
		. ' INNER JOIN vtiger_crmentity '
		. ' ON vtiger_crmentity.crmid = vtiger_modelaggregateconfig.modelaggregateconfigid '
		. ' where eq_sr_equip_model = ? and vtiger_crmentity.deleted = 0';
	$sqlResult = $adb->pquery($sql, array($model));
	$num_rows = $adb->num_rows($sqlResult);
	if ($num_rows > 0) {
		$subAssembly = $adb->fetchByAssoc($sqlResult, 0);
		$subAssembly = explode('|##|', $subAssembly['masn_aggrregate']);
		return $subAssembly;
	} else {
		return [];
	}
}

function getLastPeriodicMaintainenceDetails($record, $aggregate) {
	$query = "SELECT s.hmr,scf.mt_pdical_maint_type,i.sad_ag_sl_no,i.sad_sel_ag_name,i.sad_whoa,i.sad_manu_name,i.sad_dof FROM vtiger_inventoryproductrel_other AS i
		LEFT JOIN vtiger_servicereports AS s ON s.servicereportsid = i.id
		LEFT JOIN vtiger_servicereportscf AS scf ON scf.servicereportsid = i.id
		INNER JOIN vtiger_crmentity AS vc ON vc.crmid = i.id
		WHERE s.sr_ticket_type = 'PERIODICAL MAINTENANCE' AND s.equipment_id = ? AND i.sad_sel_ag_name=?
		ORDER BY vc.createdtime DESC Limit 1";
	$db = PearDatabase::getInstance();
	$result = $db->pquery($query, array($record, $aggregate));

	$records = array();
	while ($row = $db->fetchByAssoc($result)) {
		array_push($records, $row);
	}

	if (empty($records)) {
		$sourceModule = 'Equipment';
		if (empty($record)) {
			return [];
		}
		$recordModel = Vtiger_Record_Model::getInstanceById($record, $sourceModule);
		$eqSerialNo = $recordModel->get('equipment_sl_no');
		$aggregateRecord = '';
		if ($aggregate == 'Engine') {
			$aggregateRecord = $eqSerialNo . '-' . 'EN';
		} else if ($aggregate == 'Transmission') {
			$aggregateRecord = $eqSerialNo . '-' . 'TM';
		} else if ($aggregate == 'Final Drive') {
			$aggregateRecord = $eqSerialNo . '-' . 'FD';
		}
		$sql = "select equipmentid from vtiger_equipment
			INNER JOIN vtiger_crmentity 
			ON vtiger_crmentity.crmid = vtiger_equipment.equipmentid 
			where equipment_sl_no = ? and vtiger_crmentity.deleted = 0";
		$sqlResult = $db->pquery($sql, array($aggregateRecord));
		$num_rows = $db->num_rows($sqlResult);
		if ($num_rows > 0) {
			$dataRow = $db->fetchByAssoc($sqlResult, 0);
			$agRecordModel = Vtiger_Record_Model::getInstanceById($dataRow['equipmentid'], 'Equipment');
			$data = $agRecordModel->getData();
			$recordInfo = [];
			$recordInfo['sad_manu_name'] = $data['equip_ag_manu_fact'];
			$recordInfo['sad_ag_sl_no'] = $data['equip_ag_serial_no'];
			$recordInfo['sad_whoa'] = $data['eq_last_hmr'];
			$recordInfo['hmr'] = $data['eq_last_hmr'];
			$recordInfo['sad_dof'] = $data['eq_valid_from'];
			$recordInfo['sad_sel_ag_name'] = $aggregate;
			$recordInfos = [];
			array_push($recordInfos, $recordInfo);
		} else {
			$recordInfos = [];
		}
		return $recordInfos;
	}

	return $records;
}

function getWorkedHors($lastHMR, $currentHMR, $validation, $lastWorkedHours) {
	if ($validation == 'Yes') {
		$totalWorkedIntrasaction = $currentHMR - $lastHMR;
		$totalWorked = $lastWorkedHours + $totalWorkedIntrasaction;
		return $totalWorked;
	} else {
		$totalWorked = $currentHMR - $lastHMR;
		return $totalWorked;
	}
}

function IGGetDependentValuesOfPickList($sourcevalue, $fieldName) {
	global $adb;
	$sql = "SELECT * FROM `vtiger_picklist_dependency` where targetfield = ? ";
	$sqlResult = $adb->pquery($sql, array($fieldName));
	$num_rows = $adb->num_rows($sqlResult);
	if ($num_rows > 0) {
		while ($row = $adb->fetch_array($sqlResult)) {
			if ($row['sourcevalue'] == trim($sourcevalue)) {
				return $row['targetvalues'];
			}
		}
	}
}

function getSingleColumnValue($dataMeta) {
	global $adb;
	$table = $dataMeta['table'];
	$columnId = $dataMeta['columnId'];
	$idValue = $dataMeta['idValue'];
	$expectedColValue = $dataMeta['expectedColValue'];
	$sql = "SELECT $expectedColValue FROM $table where $columnId = ? ";

	$sqlResult = $adb->pquery($sql, array($idValue));
	$num_rows = $adb->num_rows($sqlResult);
	$rowsValues = [];
	if ($num_rows > 0) {
		while ($row = $adb->fetch_array($sqlResult)) {
			array_push($rowsValues, $row);
		}
		return $rowsValues;
	} else {
		return [];
	}
}

function getTwoColumnValue($dataMeta) {
	global $adb;
	$table = $dataMeta['table'];
	$columnId = $dataMeta['columnId'];
	$idValue = $dataMeta['idValue'];
	$expectedColValue = $dataMeta['expectedColValue'];
	$expectedColValue1 = $dataMeta['expectedColValue1'];
	$sql = "SELECT $expectedColValue, $expectedColValue1 FROM $table 
	INNER JOIN vtiger_crmentity 
			ON vtiger_crmentity.crmid = $table.$expectedColValue 
	where $columnId = ? ";

	$sqlResult = $adb->pquery($sql, array($idValue));
	$num_rows = $adb->num_rows($sqlResult);
	$rowsValues = [];
	if ($num_rows > 0) {
		while ($row = $adb->fetch_array($sqlResult)) {
			array_push($rowsValues, $row);
		}
		return $rowsValues;
	} else {
		return [];
	}
}

function getCurrentUserPlantDetails() {
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
		return $sqlData['maintenanceplantid'];
	} else {
		return false;
	}
}

function getCurrentUserPlantDetailsFromMobileLogin($badge_no) {
	global $adb;
	$sql = "SELECT e.regional_office FROM vtiger_serviceengineer as e WHERE e.badge_no=? 
	 ORDER BY serviceengineerid DESC LIMIT 1";
	$sqlResult = $adb->pquery($sql, array(decode_html($badge_no)));
	$sqlData = $adb->fetch_array($sqlResult);
	$plant_name = $sqlData['regional_office'] . "-Depot";
	$sql = "SELECT m.maintenanceplantid FROM vtiger_maintenanceplant as m WHERE m.plant_name=?";
	$sqlResult = $adb->pquery($sql, array(decode_html($plant_name)));
	$sqlData = $adb->fetch_array($sqlResult);
	if (!empty($sqlData['maintenanceplantid'])) {
		return $sqlData['maintenanceplantid'];
	} else {
		return false;
	}
}

function handleUpdateOfFailedpartsDetails($id) {
	global $adb;
	$query1 = "SELECT * FROM vtiger_inventoryproductrel WHERE id=?";
	$res = $adb->pquery($query1, array($id));
	$no_of_products = $adb->num_rows($res);
	include_once('include/utils/GeneralConfigUtils.php');
	for ($j = 0; $j < $no_of_products; $j++) {
		$row = $adb->query_result_rowdata($res, $j);
		$validatedQty = floatval($row["rcvd_qty_validated"]) + floatval($row["rcvd_qty_tr_validate"]);
		$lineItemId = $row["lineitem_id"];
		$lineQuantity = floatval($row["quantity"]);
		$soCreatableQty = floatval($row["so_creatable_qty"]) + floatval($row["rcvd_qty_tr_validate"]);
		$excludedQuantity = floatval($row["excluded_qty"]);
		$totalSubmittedQty = floatval($row["total_fail_pa_sb_qty"]);
		$totalExcluded =  floatval($row["total_excluded_qty"]) + $excludedQuantity;
		$jsonRemarks = $row["tot_excluded_qty_rem"];
		if (empty($jsonRemarks)) {
			$jsonRemarks = [];
		}

		$rcvd_qty_tr_validate = $row["rcvd_qty_tr_validate"];
		$fail_pa_sb_qty = $row["fail_pa_sb_qty"];
		$pending_qty_for_validation = (float) $row["pending_qty_for_validation"];
		if ($rcvd_qty_tr_validate == "" || $rcvd_qty_tr_validate == null) {
			$fail_pa_sb_qty = $row["fail_pa_sb_qty"];
			$rcvd_qty_tr_validate = $row["rcvd_qty_tr_validate"];
		} else {
			$pending_qty_for_validation = (float) $row["pending_qty_for_validation"] - (float) $rcvd_qty_tr_validate;
			if ($pending_qty_for_validation < 0) {
				$pending_qty_for_validation = NULL;
			}
			$rcvd_qty_tr_validate = NULL;
		}

		$pendingQuantity = $lineQuantity - ( $totalExcluded + $totalSubmittedQty);
		$quantityConsiderationForStatus = $lineQuantity - ($validatedQty + $totalExcluded);
		$totalExcludedRemrks = json_decode(json_encode(decode_html($jsonRemarks)));
		$totalExcludedRemrks = json_decode($totalExcludedRemrks, true);
		$totalExcludedRemrksAno = [];
		$jsonParseError = json_last_error();
		if (empty($jsonParseError)) {
			$totalExcludedRemrksAno = $totalExcludedRemrks['val'];
		}
		if (empty($totalExcludedRemrksAno)) {
			$totalExcludedRemrksAno = [];
		}
		if (!empty($excludedQuantity)) {
			array_push($totalExcludedRemrksAno, array(
				'quantity' => $excludedQuantity,
				'remarks' => $row["excluded_qty_rem"]
			));
		}
		$totalExcludedRemrks["val"] = $totalExcludedRemrksAno;
		if ($quantityConsiderationForStatus >= 0) {
			$status = 'Open';
			if ($quantityConsiderationForStatus == 0) {
				$status = 'Closed';
			}

			$updateSql = "Update vtiger_inventoryproductrel set rcvd_qty_validated = ?, 
						fail_pa_sb_qty = ?, rcvd_qty_tr_validate = ?, pending_qty_to_sub = ? ,
						fail_pa_pa_status = ?,
						excluded_qty = ?, excluded_qty_rem = ?,
						total_excluded_qty = ?, tot_excluded_qty_rem = ?, pending_qty_for_validation = ?
						where lineitem_id = ?";
			$adb->pquery($updateSql, array(
				$validatedQty, $fail_pa_sb_qty, $rcvd_qty_tr_validate, $pendingQuantity, $status,
				NULL, NULL, $totalExcluded, json_encode($totalExcludedRemrks),
				$pending_qty_for_validation,
				$lineItemId
			));
		} else {
			$status = 'Open';
			if ($quantityConsiderationForStatus < 0) {
				$quantityConsiderationForStatus = 0;
				$status = 'Closed';
			}
			$updateSql = "Update vtiger_inventoryproductrel set rcvd_qty_validated = ?, 
						fail_pa_sb_qty = ?, rcvd_qty_tr_validate = ?, pending_qty_to_sub = ? ,
						fail_pa_pa_status = ?,
						excluded_qty = ?, excluded_qty_rem = ?,
						total_excluded_qty = ?, tot_excluded_qty_rem = ? , pending_qty_for_validation = ?
						where lineitem_id = ?";
			$adb->pquery($updateSql, array(
				$validatedQty, $fail_pa_sb_qty, $rcvd_qty_tr_validate, $pendingQuantity, $status,
				NULL, NULL, $totalExcluded, json_encode($totalExcludedRemrks),
				$pending_qty_for_validation,
				$lineItemId
			));
		}
		$qtyOFSO = getAllSOWithParentId($lineItemId);
		$creatableQty = getValidatedRecivedQty($lineItemId) -  $qtyOFSO;
		$updateSql = "Update vtiger_inventoryproductrel set so_creatable_qty = ? where lineitem_id = ?";
		$adb->pquery($updateSql, array(
			$creatableQty, $lineItemId
		));
	}
}

function handleUpdateOfFailedpartsDetailsNonServiceManager($id) {
	global $adb;
	$query1 = "SELECT * FROM vtiger_inventoryproductrel WHERE id=?";
	$res = $adb->pquery($query1, array($id));
	$no_of_products = $adb->num_rows($res);

	for ($j = 0; $j < $no_of_products; $j++) {
		$row = $adb->query_result_rowdata($res, $j);

		$validatedQty = floatval($row["rcvd_qty_validated"]) + floatval($row["rcvd_qty_tr_validate"]);
		$lineItemId = $row["lineitem_id"];

		$lineQuantity = floatval($row["quantity"]);
		$excludedQuantity = floatval($row["excluded_qty"]);
		$totalExcluded =  floatval($row["total_excluded_qty"]) + $excludedQuantity;
		$pendingQuantity = $lineQuantity - ($validatedQty + $totalExcluded);
		$quantityConsiderationForStatus = $lineQuantity - ($validatedQty + $totalExcluded);
		if ($quantityConsiderationForStatus >= 0) {
			$status = 'Open';
			if ($quantityConsiderationForStatus == 0) {
				$status = 'Closed';
			}
			$updateSql = "Update vtiger_inventoryproductrel set rcvd_qty_validated = ?, 
						pending_qty_to_sub = ? ,
						fail_pa_pa_status = ?
						where lineitem_id = ?";
			$adb->pquery($updateSql, array($validatedQty, $pendingQuantity, $status, $lineItemId));
		} else {
			$updateSql = "Update vtiger_inventoryproductrel set fail_pa_pa_status = ?
						where lineitem_id = ?";
			$adb->pquery($updateSql, array('Closed', $lineItemId));
		}
	}
}

function handleUpdateOfFailedpartsDetailServiceEngineer($id) {
	global $adb;
	$query1 = "SELECT * FROM vtiger_inventoryproductrel WHERE id=?";
	$res = $adb->pquery($query1, array($id));
	$no_of_products = $adb->num_rows($res);
	global $creationOfFailedPartRecord;

	for ($j = 0; $j < $no_of_products; $j++) {
		$row = $adb->query_result_rowdata($res, $j);

		$validatedQty = floatval($row["rcvd_qty_validated"]) + floatval($row["rcvd_qty_tr_validate"]);
		$lineItemId = $row["lineitem_id"];

		$lineQuantity = floatval($row["quantity"]);
		$excludedQuantity = floatval($row["excluded_qty"]);
		$totalExcluded =  floatval($row["total_excluded_qty"]) + $excludedQuantity;
		$SubmittedQty = floatval($row["fail_pa_sb_qty"]);
		$totalSubmittedQty =  floatval($row["total_fail_pa_sb_qty"]) + $SubmittedQty;
		$pending_qty_for_validation =  floatval($row["pending_qty_for_validation"]) + $SubmittedQty;
		$rcvd_qty_tr_validate = $row["rcvd_qty_tr_validate"];
		$fail_pa_sb_qty = $row["fail_pa_sb_qty"];

		$jsonRemarks = $row["submitted_qty_log"];
		if (empty($jsonRemarks)) {
			$jsonRemarks = [];
		}
		$submittedLog = json_decode(json_encode(decode_html($jsonRemarks)));
		$submittedLog = json_decode($submittedLog, true);
		$submittedLogArr = [];
		$jsonParseError = json_last_error();
		if (empty($jsonParseError)) {
			$submittedLogArr = $submittedLog['val'];
		}
		if (empty($submittedLogArr)) {
			$submittedLogArr = [];
		}
		if (!empty($SubmittedQty)) {
			$dateOfSubMisssion = $row["date_of_submiss"];
			if(empty($dateOfSubMisssion)){
				$dateOfSubMisssion = date('Y-m-d');
			}
			array_push($submittedLogArr, array(
				'quantity' => $SubmittedQty,
				'date_of_submiss' => $dateOfSubMisssion
			));
		}
		$submittedLog["val"] = $submittedLogArr;

		if ($rcvd_qty_tr_validate == "" || $rcvd_qty_tr_validate == null) {
			$fail_pa_sb_qty = $row["fail_pa_sb_qty"];
			$rcvd_qty_tr_validate = $row["rcvd_qty_tr_validate"];
		} else {
			$fail_pa_sb_qty = (float) $row["fail_pa_sb_qty"] - (float) $rcvd_qty_tr_validate;
			if ($fail_pa_sb_qty < 0) {
				$fail_pa_sb_qty = NULL;
			}
			$rcvd_qty_tr_validate = NULL;
		}

		$pendingQuantity = $lineQuantity - ( $totalExcluded + $totalSubmittedQty);
		$quantityConsiderationForStatus = $lineQuantity - ($validatedQty + $totalExcluded);
		if ($quantityConsiderationForStatus >= 0) {
			$status = 'Validation Pending';
			if($creationOfFailedPartRecord == true){
				$status = 'Open';
			}
			if ($quantityConsiderationForStatus == 0) {
				$status = 'Closed';
			}
			$updateSql = "Update vtiger_inventoryproductrel set rcvd_qty_validated = ?, 
						pending_qty_to_sub = ? ,
						fail_pa_pa_status = ? , total_fail_pa_sb_qty = ?, fail_pa_sb_qty = ?,
						pending_qty_for_validation = ?, submitted_qty_log = ?
						where lineitem_id = ?";
			$adb->pquery($updateSql, array(
				$validatedQty, $pendingQuantity,
				$status, $totalSubmittedQty,
				NULL,$pending_qty_for_validation,
				json_encode($submittedLog),
				$lineItemId
			));
		} else {
			$updateSql = "Update vtiger_inventoryproductrel set fail_pa_pa_status = ?
						where lineitem_id = ?";
			$adb->pquery($updateSql, array('Closed', $lineItemId));
		}
	}
}

function GetPOFields($id) {
	global $adb;
	$ticketQuery = $adb->pquery("select ticket_id from vtiger_failedparts where failedpartid =" . $id);
	$ticketid = $adb->query_result($ticketQuery, 0, 'ticket_id');
	if (isset($ticketid)) {
		$sqlResult = $adb->pquery("select vtiger_troubletickets.external_app_num,vtiger_crmentity.createdtime from vtiger_troubletickets"
			. " INNER JOIN vtiger_crmentity "
			. " ON vtiger_crmentity.crmid = vtiger_troubletickets.ticketid "
			. " where vtiger_troubletickets.ticketid = " . $ticketid . " and vtiger_crmentity.deleted = 0");

		$FailedDetails["external_app_num"] = $adb->query_result($sqlResult, 0, 'external_app_num');
		$FailedDetails["createdtime"] = $adb->query_result($sqlResult, 0, 'createdtime');

		return $FailedDetails;
	}
}

function IgGetSAPErrorFormatASerrorArray($errorArray) {
	$errorText = "";
	if (is_array($errorArray)) {
		foreach ($errorArray as $error) {
			$errorText = $errorText . trim($error['MESSAGE']) . ' </br>';
		}
		return $errorText;
	} else {
		return $errorArray;
	}
}

function IGGetFirstServiceReportOfSR($id) {
	global $adb;
	$query = "SELECT servicereportsid FROM `vtiger_servicereports`"
		. " inner join vtiger_crmentity on vtiger_crmentity.crmid = vtiger_servicereports.servicereportsid "
		. " where ticket_id = ? and deleted = ?";
	$result = $adb->pquery($query, array($id, 0));
	$num_rows = $adb->num_rows($result);
	$dataRow = $adb->fetchByAssoc($result, 0);
	if ($num_rows > 0) {
		return $dataRow['servicereportsid'];
	} else {
		return '';
	}
}

function IGgetDateInEterAPPFormat($date) {
	if (empty($date)) {
		return '';
	} else {
		return str_replace('-', '', $date);
	}
}

function IGgetCodeOFValue($keyTable, $value) {
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

function updateHMRINExternalApp($equipmentId, $equipmentReading) {
	$url = getExternalAppURL('GeneralisedRFCCaller');

	$dataArr = getSingleColumnValue(array(
		'table' => 'vtiger_equipmentcf',
		'columnId' => 'equipmentid',
		'idValue' => $equipmentId,
		'expectedColValue' => 'measuring_point'
	));
	$measuringPoint = $dataArr[0]['measuring_point'];
	if (empty($measuringPoint)) {
		$responseObject['success'] = false;
		$responseObject['message'] = "No Measuring Point Are Defined For The Equipment";
		return $responseObject;
	}

	$formatToSAP = strval($measuringPoint);
	$zerosToAppend = 12 - strlen($formatToSAP);
	if ($zerosToAppend > 0) {
		$formatToSAP = sprintf("%012d", $formatToSAP);
	}

	$data = array(
		'rfcName'  => 'ZPM_CRM_ADD_READING',
		'IM_POINT' => $formatToSAP,
		'IM_READING' => $equipmentReading
	);

	$header = array('Content-Type:multipart/form-data');
	$resource = curl_init();
	curl_setopt($resource, CURLOPT_URL, $url);
	curl_setopt($resource, CURLOPT_HTTPHEADER, $header);
	curl_setopt($resource, CURLOPT_POST, 1);
	curl_setopt($resource, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($resource, CURLOPT_POSTFIELDS, $data);
	curl_setopt($resource, CURLOPT_SSL_VERIFYPEER, 0);
	$responseUnEncoded = curl_exec($resource);
	$response = json_decode($responseUnEncoded, true);
	curl_close($resource);
	$responseObject = [];
	$responseObject['success'] = false;
	$responseObject['message'] = "Not Able To Add Meter Reading Entry IN SAP";
	$jsonParseError = json_last_error();
	if (empty($jsonParseError)) {
		if ($response && $response['IT_RETURN'] && $response['IT_RETURN'][0]['MSGTYP'] == 'E') {
			$responseObject['success'] = false;
			$responseObject['message'] = $response['IT_RETURN'][0]['MESSAGE'];
		} else if ($response && $response['IT_RETURN'] && $response['IT_RETURN'][0]['MSGTYP'] == 'S') {
			$responseObject['success'] = true;
		}
	} else {
		$responseObject['success'] = false;
		$responseObject['message'] = $responseUnEncoded;
	}
	return $responseObject;
}
