<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Mobile_WS_Login extends Mobile_WS_Controller {

	function requireLogin() {
		return false;
	}

	function process(Mobile_API_Request $request) {
		$response = new Mobile_API_Response();
		$username = $request->get('username');
		$password = $request->get('password');
		if (empty($username)) {
			$response->setError(1501, 'Username Is Missing');
			return $response;
		}
		if (empty($password)) {
			$response->setError(1501, 'Password Is Missing');
			return $response;
		}
		$current_user = CRMEntity::getInstance('Users');
		$current_user->column_fields['user_name'] = $username;
		if (vtlib_isModuleActive('Mobile') === false) {
			$response->setError(1501, 'Service not available');
			return $response;
		}

		$loginPlatDEtails = $this->getAccessiblePlatforms($username);
		// if (empty($loginPlatDEtails)) {
		// 	$response->setError(1501, 'You Are Not Registered');
		// 	return $response;
		// }

		/*if ($loginPlatDEtails['approval_status'] !== 'Accepted' && $loginPlatDEtails['approval_status'] !== 'Rejected') {
			$response->setError(1501, 'User Verfication Is Pending');
			return $response;
		}*/

		if ($loginPlatDEtails['approval_status'] == 'Rejected') {
			$response->setError(1501, 'User Verfication Is Rejected , Please Sign Up Again');
			return $response;
		}
		// $loginPlat =  $loginPlatDEtails['ser_usr_log_plat'];
		// if($loginPlat != 'Both' && $loginPlat != 'Mobile App') {
		// 	$response->setError(1501, 'You Are Not Allowed To Login From Mobile App');
		// 	return $response;
		// }

		if (!$current_user->doLogin($password)) {
			$response->setError(1210, 'Authentication Failed');
		} else {
			// $sessionid = Mobile_API_Session::init();
			// if ($sessionid === false) {
			// 	echo "Session init failed $sessionid\n";
			// }
			$current_user->id = $current_user->retrieve_user_id($username);
			$current_user->retrieveCurrentUserInfoFromFile($current_user->id);
			$this->setActiveUser($current_user);

			// JWT Implementation
			require __DIR__ . DIRECTORY_SEPARATOR .'autoload.php';
			$key = "ONSGVGFDKNBXVDAWTYSVSCDX".$current_user->user_password . $current_user->user_name;
			$payload = [
				'userid' => $current_user->id,
			];
			$jwt = JWT::encode($payload, $key, 'HS256');
			
			$data = $this->getUserDetailsBasedOnEmployeeModule($current_user->user_name);
			if ($data == false) {
				$response->setError(1501, 'Not Able To Find User Details');
				return $response;
			}

			$sql = 'select serviceengineerid from vtiger_serviceengineer ' .
            ' inner join vtiger_crmentity on vtiger_crmentity.crmid = vtiger_serviceengineer.serviceengineerid' .
            ' where badge_no = ? and vtiger_crmentity.deleted= 0 ORDER BY serviceengineerid DESC LIMIT 1 ';
			global $adb;
			$sqlResult = $adb->pquery($sql, array($username));
			$dataRow = $adb->fetchByAssoc($sqlResult, 0);
			$recordModel = Vtiger_Record_Model::getInstanceById($dataRow['serviceengineerid'], 'ServiceEngineer');
            $emData = $recordModel->getData();
            unset($emData['confirm_password']);
            unset($emData['user_password']);
// print_r($data);
			$date = new DateTime();
			$role = $data['sub_service_manager_role'];
			if ($emData['office'] == 'Production Division') {
				$role = 'Divisonal Service Manager';
			} else if ($emData['office'] == 'Service Centre' && $emData['sub_service_manager_role'] == 'Service Manager') {
				$role = 'Service Centre Manager';
			}
			$emData['imagename'] = $this->getUserImageDetails($current_user->id);
			$result = array(
				'assign_user_id' => $current_user->id,
				'usercreatedid' => $data['serviceengineerid'],
				'usertype' => "BIZINFRAUSER",
				'access_token' => $jwt,
				'username' => $username,
				'password' => $password,
				'usermobilenumber' => $data['phone'],
				"userRole" => $data['sub_service_manager_role'],
				"userRoleUnified" => $role,
				'useruniqeid' => $current_user->id,
				'timestamp' => $date->getTimestamp(),
				'message' => 'Thank You Have Been Login Succesfully',
				'profileInfo' => $emData
			);
			include_once('include/utils/GeneralUtils.php');
			$plant = getCurrentUserPlantDetailsFromMobileLogin($username);
			if($plant != false && !empty($plant)){
				$tabId = Mobile_WS_Utils::getEntityModuleWSId('MaintenancePlant');
				$result['plant_name'] = $tabId . 'x' . $plant;
				$result['plant_name_label'] = decode_html(Vtiger_Functions::getCRMRecordLabel($plant));
			}
			$response->setApiSucessMessage('Successfully Logged In');
			$response->setResult($result);
			$this->postProcess($response);
		}
		return $response;
	}

	protected static $userURLCache = array();

	public function getUserImageDetails($recordId) {
		if (empty($recordId)) {
			return NULL;
		}
		global $site_URL_NonHttp;
		$db = PearDatabase::getInstance();
		$url = NULL;
		if (!self::$userURLCache[$recordId]) {
			$query = "SELECT vtiger_attachments.attachmentsid, vtiger_attachments.path, 
					vtiger_attachments.name, vtiger_attachments.storedname FROM vtiger_attachments
					LEFT JOIN vtiger_salesmanattachmentsrel 
					ON vtiger_salesmanattachmentsrel.attachmentsid = vtiger_attachments.attachmentsid
					WHERE vtiger_salesmanattachmentsrel.smid=? order by attachmentsid desc limit 1";
			$result = $db->pquery($query, array($recordId));
			$storedname = $db->query_result($result, 0, 'storedname');
			$imagePath = $db->query_result($result, 0, 'path');
			$imageId = $db->query_result($result, 0, 'attachmentsid');
			$url = $site_URL_NonHttp . $imagePath. $imageId .'_'.$storedname;
			self::$userURLCache[$recordId] = $url;
			if (empty($imagePath)) {
				self::$userURLCache[$recordId] = NULL;
			}
		}
		return self::$userURLCache[$recordId];
	}

	function getAccessiblePlatforms($userName) {
		global $adb;
		$sql = 'select ser_usr_log_plat,badge_no,approval_status from vtiger_serviceengineer
		INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_serviceengineer.serviceengineerid
		where badge_no = ? ';
		$result = $adb->pquery($sql, array($userName));
		$loginPlatforms = '';
		while ($row = $adb->fetch_array($result)) {
			$loginPlatforms =  $row;
		}
		return $loginPlatforms;
	}

	function getUserDetailsBasedOnEmployeeModule($badgeNo) {
		global $adb;
		$sql = 'select serviceengineerid,phone,sub_service_manager_role from vtiger_serviceengineer '
			. ' inner join vtiger_crmentity on vtiger_crmentity.crmid = vtiger_serviceengineer.serviceengineerid '
			. ' where vtiger_serviceengineer.badge_no = ? and vtiger_crmentity.deleted = 0 ORDER BY serviceengineerid DESC LIMIT 1';
		$sqlResult = $adb->pquery($sql, array($badgeNo));
		$num_rows = $adb->num_rows($sqlResult);
		if ($num_rows == 1) {
			$dataRow = $adb->fetchByAssoc($sqlResult, 0);
			return $dataRow;
		} else {
			return false;
		}
	}

	function postProcess(Mobile_API_Response $response) {
		return $response;
	}
}
