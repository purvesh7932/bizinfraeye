<?php
include_once 'vtlib/Vtiger/Module.php';
include_once "include/utils/VtlibUtils.php";
include_once "include/utils/CommonUtils.php";
include_once "includes/Loader.php";
include_once 'includes/runtime/BaseModel.php';
include_once "includes/http/Request.php";
include_once "include/Webservices/Utils.php";
include_once "includes/runtime/EntryPoint.php";
vimport('includes.runtime.EntryPoint');
class CustomerPortal_CustomerSignUp extends CustomerPortal_API_Abstract {

	function process(CustomerPortal_API_Request $request) {
		$response = new CustomerPortal_API_Response();
		$responseObject = [];
		$id = $request->get('uid');
		$otp = $request->get('otp');
		$status = Vtiger_ShortURL_Helper::handleForgotPasswordMobile(vtlib_purify($id));
		if ($status == true) {
			$shortURLModel = Vtiger_ShortURL_Helper::getInstance($id);
			$otpFromDataBase = $shortURLModel->handler_data['otp'];
			if ($otp == $otpFromDataBase) {
				$sentTime = $shortURLModel->handler_data['time'];
				$now = strtotime("Now");
				if ($now >  $sentTime) {
					$response->setError(100, "OTP is Expired");
					$shortURLModel->delete();
					return $response;
				} else {
					$activeFields = CustomerPortal_Utils::getActiveFields('Contacts', true);
					$focus = CRMEntity::getInstance('Contacts');
					$activeFieldKeys = array_keys($activeFields);
					foreach ($activeFieldKeys as $activeFieldKey) {
						if ($activeFieldKey == 'assigned_user_id') {
							$focus->column_fields['assigned_user_id'] = $this->getAssignedPerson($shortURLModel->handler_data['nearest_office']);
						} else {
							$focus->column_fields[$activeFieldKey] = $shortURLModel->handler_data[$activeFieldKey];
						}
					}
					if (!empty($shortURLModel->handler_data['nearest_office'])) {
						$focus->column_fields['assigned_user_id'] = $this->getAssignedPerson($shortURLModel->handler_data['nearest_office']);
					} else {
						$response->setError(100, "Mandatory Field nearest_office is missing");
						return $response;
					}
					$focus->save("Contacts");
					$responseObject['mobile'] = $focus->column_fields['mobile'];
					$responseObject['usercreatedid'] =  $focus->id;
					$responseObject['useruniqeid'] =  $focus->id;;
					$date = new DateTime();
					$responseObject['timestamp'] = $date->getTimestamp();
					$responseObject['message'] = "Thank you for your valuable registration" .
						"Verification pending from BEML" .
						"After succesful verification, you will be communicated through SMS/Email";
					$shortURLModel->delete();
					$response->setResult($responseObject);
					return $response;
				}
			} else {
				$response->setError(100, "OTP Is Invalid");
				return $response;
			}
		} else {
			$response->setError(100, "UID Is Invalid");
			return $response;
		}
	}

	function getAssignedPerson($roleName) {
		$realRole = explode(" - ", $roleName);
		$region = trim($realRole[0]);
		global $adb;
		$roleName = $region . ' - REGIONAL MANAGER';
		$sql = "SELECT * FROM `vtiger_role` 
		INNER JOIN `vtiger_user2role` ON `vtiger_user2role`.`roleid` = `vtiger_role`.`roleid` 
		where rolename = ?";
		$result = $adb->pquery($sql, array($roleName));
		$dataRow = $adb->fetchByAssoc($result, 0);
		if (empty($dataRow['userid'])) {
			return 1;
		} else {
			return $dataRow['userid'];
		}
	}

	function authenticatePortalUser($username, $password) {
		return true;
	}
}
