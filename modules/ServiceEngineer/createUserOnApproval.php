<?php
function createUserOnApproval($entityData) {
	$data = $entityData->{'data'};
	require_once('modules/Users/Users.php');
	global $adb;

	// getting the id of the created record it in the format 12x237
	$recId = $data['id'];
	$idsOfCreated = explode('x', $recId);
	$data['id'] = $idsOfCreated[1];

	$username = preg_replace('/\s+/', '', $data['badge_no']);
	// print_r($data['user_name']);
	$data['confirm_password'] = Vtiger_Functions::fromProtectedText($data['confirm_password']);
	$password = preg_replace('/\s+/', '', $data['confirm_password']);
	$result = $adb->pquery('SELECT 1 FROM `vtiger_users` where user_name = ?', array($username));
	$rowCount = $adb->num_rows($result);
	global $ajaxEditingInSEmod;
	if ($rowCount > 0) {
		unSetAcceptValue($data['id']);
		if ($ajaxEditingInSEmod) {
			$response = new Vtiger_Response();
			$response->setEmitType(Vtiger_Response::$EMIT_JSON);
			$response->setError('Badge Number is Alredy Exits, User Is Not Created');
			$response->emit();
			exit();
		} else {
			$viewer = new Vtiger_Viewer();
			$viewer->assign('MESSAGE', "Badge Number is Alredy Exits");
			$viewer->view('OperationNotPermitted.tpl', 'Vtiger');
			exit();
		}
	}
// 	print_r($data);
	$roleFeldName = getRoleBasedOnValue($data);
	$role = getRoleIdBasedOnRoleName($data['sub_service_manager_role']);
	if (empty($role)) {
		unSetAcceptValue($data['id']);
		if ($ajaxEditingInSEmod) {
			$response = new Vtiger_Response();
			$response->setEmitType(Vtiger_Response::$EMIT_JSON);
			$response->setError('Unable To Find The User Role');
			$response->emit();
			exit();
		} else {
			$viewer = new Vtiger_Viewer();
			$viewer->assign('MESSAGE', "Unable To Find The User Role");
			$viewer->view('OperationNotPermitted.tpl', 'Vtiger');
			exit();
		}
	}
	

	$focus = new Users();
	$focus->column_fields['user_name'] =   $data['badge_no'];
	$focus->column_fields['first_name'] =  '';
	$focus->column_fields['last_name'] =  $data['service_engineer_name'];
	$focus->column_fields['status'] =  'Active';
	$focus->column_fields['is_admin'] =  'off';
	$focus->column_fields['user_password'] =  $password;
	$focus->column_fields['confirm_password'] =  $password;
	$focus->column_fields['email1'] =   $data['email'];
	// $focus->column_fields['address_street'] = $data['bill_street'];
	$focus->column_fields['phone_mobile'] = $data['phone'];
	$focus->column_fields['roleid'] =  $role; //'H37';
	$focus->column_fields['tz'] =  'Asia/Kolkata';
	$focus->column_fields['time_zone'] =  'Asia/Kolkata';
	$focus->column_fields['date_format'] =  'dd/mm/yyyy';
	$focus->column_fields['title'] =  'Asia';
	$focus->save("Users");
	ignore_user_abort(true);

	ob_start();
	$response = new Vtiger_Response();
	$response->setResult(array('success' => true, 'message' => 'Successfuly Approved'));
	$response->emit();
	header($_SERVER["SERVER_PROTOCOL"] . " 202 Accepted");
	header("Status: 202 Accepted");
	header("Content-Type: application/json");
	header('Content-Length: ' . ob_get_length());
	ob_end_flush();
	ob_flush();
	flush();

	$dashboardSql = "INSERT INTO `vtiger_dashboard_tabs` 
	(`id`, `tabname`, `isdefault`, `sequence`, `appname`, `modulename`, `userid`) 
	VALUES (NULL, 'My Dashboard', '1', '1', '', '', $focus->id)";
	$result = $adb->pquery($dashboardSql, array());

	$sql = 'SELECT id FROM vtiger_dashboard_tabs where userid = ? and tabname = ?';
	$params = array($focus->id, 'My Dashboard');
	$result = $adb->pquery($sql, $params);
	$noOfUsers = $adb->num_rows($result);
	$dashboardTabId = '';
	if ($noOfUsers > 0) {
		for ($i = 0; $i < $noOfUsers; ++$i) {
			$dashboardTabId = $adb->query_result($result, $i, 'id');
		}
	}
	$dashboardTabIds = array(67);
	foreach ($dashboardTabIds as $value) {
		$sql = 'SELECT id FROM vtiger_module_dashboard_widgets WHERE linkid = ? AND userid = ? AND dashboardtabid=?';
		$params = array($value, $focus->id, $dashboardTabId);
		$result = $adb->pquery($sql, $params);
		$size = '';
		if ($value == 117 || $value == 120 || $value == 121) {
			$size = '{"sizex":"1","sizey":"1"}';
		} else {
			$size = '{"sizex":"2","sizey":"1"}';
		}
		if (!$adb->num_rows($result)) {
			$adb->pquery(
				'INSERT INTO vtiger_module_dashboard_widgets(linkid, userid,'
					. ' dashboardtabid,size) '
					. 'VALUES(?,?,?,?)',
				array($value, $focus->id, $dashboardTabId, $size)
			);
		}
	}

	Settings_SharingAccess_Module_Model::recalculateSharingRules();
}

// function getRoleIdBasedOnRoleName($roleName) {
// 	global $adb;
// 	$sql = "SELECT * FROM `vtiger_role` where rolename = ?";
// 	$result = $adb->pquery($sql, array($roleName));
// 	$dataRow = $adb->fetchByAssoc($result, 0);
// 	if (empty($dataRow['roleid'])) {
// 		return '';
// 	} else {
// 		return $dataRow['roleid'];
// 	}
// }
function unSetAcceptValue($id) {
	$db = PearDatabase::getInstance();
	$query = "UPDATE vtiger_serviceengineer SET rejection_reason=?,approval_status=? WHERE serviceengineerid=?";
	$db->pquery($query, array('', '', $id));
}

function getRoleBasedOnValue($handlerData) {
	$SystemDetectedRole = '';
	$hasSubRoleArr = array(
		'Regional Office', 'Service Centre',
		'Activity Centre', 'District Office', 'Production Division',
		'International Business Division-New Delhi'
	);
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
			$handlerData['district_office'] = '';
			$handlerData['service_centre'] = '';
			$handlerData['activity_centre'] = '';
			$handlerData['production_division'] = '';
		} else if ($handlerData['office'] == 'Production Division') {
			$releventRole =  $handlerData['production_division'];
			$SystemDetectedRole = getProductionDivisionRole($releventRole);
			$handlerData['district_office'] = '';
			$handlerData['regional_office'] = '';
			$handlerData['service_centre'] = '';
			$handlerData['activity_centre'] = '';
		} else if ($handlerData['office'] == 'Service Centre') {
			$releventRole =  $handlerData['service_centre'];
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
		} else if ($handlerData['office'] == 'International Business Division-New Delhi') {
			$releventRole = 'International Business Division-New Delhi';
			$handlerData['district_office'] = '';
			$handlerData['regional_office'] = '';
			$handlerData['service_centre'] = '';
			$handlerData['production_division'] = '';
		}
		if ($handlerData['cust_role'] == 'Service Manager') {
			if ($handlerData['sub_service_manager_role'] == 'Service Manager Support') {
				$SystemDetectedRole = $releventRole . ' - Service Manager';
			} else {
				$SystemDetectedRole = $releventRole . ' - ' . $handlerData['sub_service_manager_role'];
			}
			if ($handlerData['office'] == 'International Business Division-New Delhi' && $handlerData['cust_role'] == 'BEML Management') {
				$SystemDetectedRole = $handlerData['cust_role'];
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

function getProductionDivisionRole($releventRole) {
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
