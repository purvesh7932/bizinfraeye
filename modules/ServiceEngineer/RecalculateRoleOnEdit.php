<?php
function RecalculateRoleOnEdit($entityData) {
	$data = $entityData->{'data'};
	require_once('modules/Users/Users.php');
	global $adb;

	$recId = $data['id'];
	$idsOfCreated = explode('x', $recId);
	$data['id'] = $idsOfCreated[1];

	$username = preg_replace('/\s+/', '', $data['badge_no']);
	$result = $adb->pquery('SELECT id FROM `vtiger_users` where user_name = ?', array($username));
	$rowCount = $adb->num_rows($result);
	if ($rowCount > 0) {
		$dataRow = $adb->fetchByAssoc($result, 0);
		$roleFeldName = getRoleBasedOnValue1($data);
		$currentRole = getRoleIdBasedOnRoleNameRROE($roleFeldName);

		$recordModel = Vtiger_Record_Model::getInstanceById($dataRow['id'], 'Users');
		if (!empty($recordModel)) {
			$oldRoleId = $recordModel->get('roleid');
			if ($oldRoleId == $currentRole) {
			} else {
				if(!empty($currentRole)){
					$recordModel->set('mode', 'edit');
					$recordModel->set('roleid', $currentRole);
					$recordModel->save();
					Settings_SharingAccess_Module_Model::recalculateSharingRules();
				}
			}
		}
	}
}

function getRoleIdBasedOnRoleNameRROE($roleName) {
	global $adb;
	$sql = "SELECT * FROM `vtiger_role` where rolename = ?";
	$result = $adb->pquery($sql, array($roleName));
	$dataRow = $adb->fetchByAssoc($result, 0);
	if (empty($dataRow['roleid'])) {
		return '';
	} else {
		return $dataRow['roleid'];
	}
}

function getRoleBasedOnValue1($handlerData) {
	$SystemDetectedRole = '';
	$hasSubRoleArr = array('Regional Office', 'Service Centre', 'Activity Centre', 'District Office', 'Production Division');
	if (in_array($handlerData['office'], $hasSubRoleArr)) {
		$releventRole = '';
		if ($handlerData['office'] == 'District Office') {
			$releventRole =  $handlerData['district_office'];
			$handlerData['regional_office'] = '';
			$handlerData['service_centre'] = '';
			$handlerData['activity_centre'] = '';
			$handlerData['production_division'] = '';
		} else if ($handlerData['office'] == 'Regional Office') {
			$releventRole =  $handlerData['regional_office'];
			if ($releventRole == 'New Delhi') {
				$releventRole = 'Delhi';
			}
			$handlerData['district_office'] = '';
			$handlerData['service_centre'] = '';
			$handlerData['activity_centre'] = '';
			$handlerData['production_division'] = '';
		} else if ($handlerData['office'] == 'Production Division') {
			$releventRole =  $handlerData['production_division'];
			$SystemDetectedRole = getProductionDivisionRole1($releventRole);
			$handlerData['district_office'] = '';
			$handlerData['regional_office'] = '';
			$handlerData['service_centre'] = '';
			$handlerData['activity_centre'] = '';
		} else if ($handlerData['office'] == 'Service Centre') {
			$releventRole =  $handlerData['service_centre'];
			if ($releventRole == 'New Delhi Service Centre') {
				$releventRole = 'Delhi';
			}
			$handlerData['district_office'] = '';
			$handlerData['regional_office'] = '';
			$handlerData['activity_centre'] = '';
			$handlerData['production_division'] = '';
		} else if ($handlerData['office'] == 'Activity Centre') {
			$handlerData['district_office'] = '';
			$handlerData['regional_office'] = '';
			$handlerData['service_centre'] = '';
			$handlerData['production_division'] = '';
			$releventRole =  $handlerData['activity_centre'];
		}
		if ($handlerData['cust_role'] == 'Service Manager') {
			if ($handlerData['sub_service_manager_role'] == 'Service Manager Support') {
				$SystemDetectedRole = $releventRole . ' - Service Manager';
			} else {
				$SystemDetectedRole = $releventRole . ' - ' . $handlerData['sub_service_manager_role'];
			}
		} else {
			if ($handlerData['office'] != 'Production Division') {
				$SystemDetectedRole = $releventRole . ' - ' . $handlerData['cust_role'];
			}
		}
	} else {
		$SystemDetectedRole = $handlerData['cust_role'];
	}
	return $SystemDetectedRole;
}

function getProductionDivisionRole1($releventRole) {
	$role = '';
	switch ($releventRole) {
		case "EM Division":
			$role = 'EM DIVISION, KGF';
			break;
		case "Engine Division":
			$role = 'ENGINE DIVISION';
			break;
		case "Truck Division":
			$role = "TRUCK DIVISION, MYSORE";
			break;
		case "H&P Division":
			$role = "H&P DIVISION";
			break;
		case "Palakkad Division":
			$role = "DEFENCE PRODUCTION, PALAKKAD";
			break;
		default:
			$role = '';
	}
	return $role;
}
