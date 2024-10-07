<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
class Portal_Login_API extends Portal_Default_API {

	public function requireLogin() {
		return false;
	}

	public function preProcess(Portal_Request $request) {
		
	}

	public function postProcess(Portal_Request $request) {
		
	}

	public function process(Portal_Request $request) {
		$response = new Portal_Response();
		$params = $request->get('q');
		$userName = strtoupper($request->get('username'));
		if(empty($userName)){
			$response->setError('Username Is Missing');
			return $response;
		}
		$password = $request->get('password');
		if(empty($password)){
			$response->setError('Password Is Missing');
			return $response;
		}
		if ($params['language']) {
			Portal_Session::set('language', $params['language']);
		}
		$result = Vtiger_Connector::getInstance()->ping($userName, $password);
		$loginStatus = array();
		if (isset($result['message']) && (!strcmp($result['message'], "Login Required"))) {
			$response->setError(1501,  'Could not authenticate.');
		} else if (isset($result['message']) && !strcmp($result['message'], "Login failed")) {
			$response->setError(1501,  'The email or password you entered is incorrect.');
		} else if (isset($result['message']) && strpos($result['message'], "Portal access has not been enabled for this account") !== false) {
			$response->setError(1501, 'Portal access has not been enabled for this account.');
		} else if (isset($result['message']) && strpos($result['message'], "Access to the portal was disabled on ") !== false) {
			$response->setError(1501,  $result['message']);
		} else if (isset($result['message']) && strpos($result['message'], "Contacts module is disabled") !== false) {
			$response->setError(1501,  "Contacts module is disabled!");
		} else if (isset($result['message']) && strpos($result['message'], "Customer portal not available with the current edition, please upgrade!!") !== false) {
			$response->setError(1501,  $result['message']);
		} else if (isset($result['message']) && strpos($result['message'], "Your access to portal is not enabled yet. Access to support starts on") !== false) {
			$response->setError(1501,  $result['message']);
		} else if (empty($result)) {
			$response->setError(1501, 'Cannot connect to Server.Please configure your site url in provided config file.');
		} else {
			Portal_Session::set('username', $userName);
			Portal_Session::set('password', $password);
			Vtiger_Connector::getInstance()->fetchModules();
			Vtiger_Connector::getInstance()->updateLoginDetails('Login');
			// $loginStatus['session'] = session_id();
			$date = new DateTime();
			$data = $this->getUserDetailsBasedOnEmployeeModule($userName);
			if($data == false){
				$response->setError(1501, 'Not Able To Find User Details');
				return $response;
			}
			require __DIR__ . DIRECTORY_SEPARATOR .'..'.DIRECTORY_SEPARATOR.'..'. DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR .'autoload.php';
			$key = 'ONSGVGFDKNBXVDAWTYSVSCDX'. $userName . $data['user_password'] . 'JHJJHJH*&*JHJJHJH&hjdHGHGHG';
			$payload = [
				'contactid' => $data['contactid'],
				'psw' => $password
			];
			$jwt = JWT::encode($payload, $key, 'HS256');

			$loginStatus['usertype'] = 'CUSTOMER';
			$loginStatus['access_token'] = $jwt;
			$loginStatus['usercreatedid'] = $data['contactid'];
			$loginStatus['usermobilenumber'] = $data['mobile'];
			$loginStatus['timestamp'] = $date->getTimestamp();
			$loginStatus['useruniqeid'] = $data['contactid'];
			$loginStatus['message'] = 'Thank You Have Been Login Succesfully';

			$recordModel = Vtiger_Record_Model::getInstanceById($data['contactid'], 'Contacts');
			$emData = $recordModel->getData();
			unset($emData['confirm_password']);
			unset($emData['user_password']);
			$imageObject = $this->getImageDetailsASURL($data['contactid'], 'Contacts');
			if (!empty($imageObject)) {
				$imageArray = $imageObject[0];
				global $site_URL_NonHttp;
				$emData['imagename'] = $site_URL_NonHttp . $imageArray['url'];
			} else {
				$emData['imagename'] = NULL;
			}
			$loginStatus['profileInfo'] = $emData;
			$loginStatus['message'] = 'Thank You Have Been Login Succesfully';
			$response->setApiSucessMessage('Successfully Logged In');
			$response->setResult($loginStatus);
		}
		return $response;
	}

	function getImageDetailsASURL($recordId, $module) {
		global $adb;
		$sql = "SELECT vtiger_attachments.*, vtiger_crmentity.setype FROM vtiger_attachments
					INNER JOIN vtiger_seattachmentsrel ON vtiger_seattachmentsrel.attachmentsid = vtiger_attachments.attachmentsid
					INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_attachments.attachmentsid
						WHERE vtiger_crmentity.setype = ? and vtiger_seattachmentsrel.crmid = ? order by attachmentsid desc limit 1";
		$result = $adb->pquery($sql, array($module . ' Image', $recordId));
		$imageId = $adb->query_result($result, 0, 'attachmentsid');
		$imagePath = $adb->query_result($result, 0, 'path');
		$imageName = $adb->query_result($result, 0, 'name');
		$imageType = $adb->query_result($result, 0, 'type');
		$imageOriginalName = urlencode(decode_html($imageName));

		if (!empty($imageName)) {
			$imageDetails[] = array(
				'id' => $imageId,
				'orgname' => $imageOriginalName,
				'path' => $imagePath . $imageId,
				'name' => $imageName,
				'type' => $imageType,
				'url' => Vtiger_Functions::getFilePublicURL($imageId, $imageName)
			);
		}
		return $imageDetails;
	}

	function getUserDetailsBasedOnEmployeeModule($badgeNo) {
		global $adb;
		$sql = 'select contactid,mobile,vtiger_portalinfo.user_password  from vtiger_contactdetails '
			. ' inner join vtiger_crmentity on vtiger_crmentity.crmid = vtiger_contactdetails.contactid '
			. ' inner join vtiger_portalinfo on vtiger_portalinfo.id = vtiger_contactdetails.contactid '
			. ' where vtiger_contactdetails.contact_no = ? and vtiger_crmentity.deleted = 0';
		$sqlResult = $adb->pquery($sql, array($badgeNo));
		$num_rows = $adb->num_rows($sqlResult);
		if ($num_rows == 1) {
			$dataRow = $adb->fetchByAssoc($sqlResult, 0);
			return $dataRow;
		} else {
			return false;
		}
	}

}
