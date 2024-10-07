<?php

	function createUserOnApproval($entityData) {
		$data = $entityData->{'data'};
		require_once('modules/Users/Users.php');
		global $adb;
	
		// Getting the id of the created record in the format 12x237
		$recId = $data['id'];
		$idsOfCreated = explode('x', $recId);
		$data['id'] = $idsOfCreated[1];
	
		// Prepare username and password
		$username = preg_replace('/\s+/', '', $data['owntenant_name']);
		$data['owntenant_password'] = Vtiger_Functions::fromProtectedText($data['owntenant_name']);
		$password = preg_replace('/\s+/', '', $data['owntenant_password']);
	
		// Check if username already exists
		$result = $adb->pquery('SELECT 1 FROM `vtiger_users` where user_name = ?', array($username));
		if ($adb->num_rows($result) > 0) {
			return jsonResponse(false, 'Username already exists. User not created.');
		}
	
		// Get role ID based on the provided role name
		$role = getRoleIdBasedOnRoleName($data['owntenant_role']);
		if (empty($role)) {
			return jsonResponse(false, 'Unable to find the user role.');
		}
	
		// Create new user
		$focus = new Users();
		$focus->column_fields['user_name'] = $username;
		$focus->column_fields['first_name'] = '';
		$focus->column_fields['last_name'] = $data['owntenant_name'];
		$focus->column_fields['status'] = 'Active';
		$focus->column_fields['is_admin'] = 'off';
		$focus->column_fields['user_password'] = $password;
		$focus->column_fields['confirm_password'] = $password;
		$focus->column_fields['email1'] = $data['owntenant_email'];
		$focus->column_fields['phone_mobile'] = $data['owntenant_mobile'];
		$focus->column_fields['roleid'] = $role; 
		$focus->column_fields['tz'] = 'Asia/Kolkata';
		$focus->column_fields['time_zone'] = 'Asia/Kolkata';
		$focus->column_fields['date_format'] = 'dd/mm/yyyy';
		$focus->column_fields['title'] = 'Asia';
	
		// Save the user
		$focus->save("Users");
	
		// Send success response
		return jsonResponse(true, 'User successfully created.');
	}
	
	function jsonResponse($success, $message) {
		header('Content-Type: application/json');
		echo json_encode(['success' => $success, 'message' => $message]);
		exit();
	}
	