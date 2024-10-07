<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
class Mobile_APIV1_Controller {

	static $opControllers = array(
		'getModuleRelations' => array('file' => '/api/ws/crm/getModuleRelations.php', 'class' => 'Mobile_WS_getModuleRelations'),
		'login'						=> array('file' => '/api/ws/Login.php',						'class' => 'Mobile_WS_Login'),
		'loginAndFetchModules'		=> array('file' => '/api/ws/LoginAndFetchModules.php',		'class' => 'Mobile_WS_LoginAndFetchModules'),
		'fetchModuleFilters'		=> array('file' => '/api/ws/FetchModuleFilters.php',		'class' => 'Mobile_WS_FetchModuleFilters'),
		'filterDetailsWithCount'	=> array('file' => '/api/ws/FilterDetailsWithCount.php',	'class' => 'Mobile_WS_FilterDetailsWithCount'),
		'fetchAllAlerts'			=> array('file' => '/api/ws/FetchAllAlerts.php',			'class' => 'Mobile_WS_FetchAllAlerts'),
		'alertDetailsWithMessage'	=> array('file' => '/api/ws/AlertDetailsWithMessage.php',	'class' => 'Mobile_WS_AlertDetailsWithMessage'),
		'listModuleRecords'			=> array('file' => '/api/ws/ListModuleRecords.php',			'class' => 'Mobile_WS_ListModuleRecords'),
		'fetchRecord'				=> array('file' => '/api/ws/FetchRecord.php',				'class' => 'Mobile_WS_FetchRecord'),
		'fetchRecordWithGrouping'	=> array('file' => '/api/ws/FetchRecordWithGrouping.php',	'class' => 'Mobile_WS_FetchRecordWithGrouping'),
		'fetchRecordsWithGrouping'	=> array('file' => '/api/ws/FetchRecordsWithGrouping.php',	'class' => 'Mobile_WS_FetchRecordsWithGrouping'),
		'fetchReferenceRecords'		=> array('file' => '/api/ws/FetchReferenceRecords.php',		'class' => 'Mobile_WS_FetchReferenceRecords'),
		'describe'					=> array('file' => '/api/ws/Describe.php',					'class' => 'Mobile_WS_Describe'),
		'saveRecord'				=> array('file' => '/api/ws/SaveRecord.php',				'class' => 'Mobile_WS_SaveRecord'),
		'syncModuleRecords'			=> array('file' => '/api/ws/SyncModuleRecords.php',			'class' => 'Mobile_WS_SyncModuleRecords'),
		'query'						=> array('file' => '/api/ws/Query.php',						'class' => 'Mobile_WS_Query'),
		'queryWithGrouping'			=> array('file' => '/api/ws/QueryWithGrouping.php',			'class' => 'Mobile_WS_QueryWithGrouping'),
		'relatedRecordsWithGrouping' => array('file' => '/api/ws/RelatedRecordsWithGrouping.php', 'class' => 'Mobile_WS_RelatedRecordsWithGrouping'),
		'deleteRecords'				=> array('file' => '/api/ws/DeleteRecords.php',				'class' => 'Mobile_WS_DeleteRecords'),
		'logout'					=> array('file' => '/api/ws/Logout.php',					'class' => 'Mobile_WS_Logout'),
		'fetchModules'				=> array('file' => '/api/ws/FetchModules.php',				'class' => 'Mobile_WS_FetchModules'),
		'userInfo'					=> array('file' => '/api/ws/UserInfo.php',					'class' => 'Mobile_WS_UserInfo'),
		'addRecordComment'			=> array('file' => '/api/ws/AddRecordComment.php',			'class' => 'Mobile_WS_AddRecordComment'),
		'history'					=> array('file' => '/api/ws/History.php',					'class' => 'Mobile_WS_History'),
		'taxByType'					=> array('file' => '/api/ws/TaxByType.php',					'class' => 'Mobile_WS_TaxByType'),
		'fetchModuleOwners'			=> array('file' => '/api/ws/FetchModuleOwners.php',			'class' => 'Mobile_WS_FetchModuleOwners'),
		'getMenuStructure'			=> array('file' => '/api/ws/GetMenuStructure.php',			'class' => 'Mobile_WS_GetMenuStructure'),
		'getRecordComments'	=> array('file' => '/api/ws/getRecordComments.php',	'class' => 'Mobile_WS_getRecordComments'),
		'saveUserRecord'	=> array('file' => '/api/ws/saveUserRecord.php',	'class' => 'Mobile_WS_saveUserRecord'),
		'saveUser'	=> array('file' => '/api/ws/saveUser.php',	'class' => 'Mobile_WS_saveUser'),
		'DescribeUserForSignUp'	=> array('file' => '/api/ws/crm/DescribeUserForSignUp.php',	'class' => 'Mobile_WS_DescribeUserForSignUp'),
		'UserSignUp'	=> array('file' => '/api/ws/crm/UserSignUp.php',	'class' => 'Mobile_WS_UserSignUp'),
		'PreUserSignUp'	=> array('file' => '/api/ws/crm/PreUserSignUp.php',	'class' => 'Mobile_WS_PreUserSignUp'),
		'GetInitData'	=> array('file' => '/api/ws/Users/GetInitData.php',	'class' => 'Mobile_WS_GetInitData'),
		'GetUserOfficeDependancy'	=> array('file' => '/api/ws/Users/GetUserOfficeDependancy.php',	'class' => 'Mobile_WS_GetUserOfficeDependancy'),
		'GetUserLevelDependancy'	=> array('file' => '/api/ws/Users/GetUserLevelDependancy.php',	'class' => 'Mobile_WS_GetUserLevelDependancy'),
		'GetUserTypeBasedOnUName'	=> array('file' => '/api/ws/Users/GetUserTypeBasedOnUName.php',	'class' => 'Mobile_WS_GetUserTypeBasedOnUName'),
		'GetTicketType'	=> array('file' => '/api/ws/HelpDesk/GetTicketType.php',	'class' => 'Mobile_WS_GetTicketType'),
		'ResendOTP'	=> array('file' => '/api/ws/crm/ResendOTP.php',	'class' => 'Mobile_WS_ResendOTP'),
		'GetSRFieldsBaasedOnType'	=> array('file' => '/api/ws/HelpDesk/GetSRFieldsBaasedOnType.php',	'class' => 'Mobile_WS_GetSRFieldsBaasedOnType'),
		'GetSRTypeCounts' => array('file' => '/api/ws/HelpDesk/GetSRTypeCounts.php',	'class' => 'Mobile_WS_GetSRTypeCounts'),
		'PreResetPassword' => array('file' => '/api/ws/crm/PreResetPassword.php',	'class' => 'Mobile_WS_PreResetPassword'),
		'ResetPassword' => array('file' => '/api/ws/crm/ResetPassword.php',	'class' => 'Mobile_WS_ResetPassword'),
		'GetSRList' => array('file' => '/api/ws/HelpDesk/GetSRList.php',	'class' => 'Mobile_WS_GetSRList'),
		'GetProfileInfo' => array('file' => '/api/ws/Users/GetProfileInfo.php',	'class' => 'Mobile_WS_GetProfileInfo'),
		'GetPickListValuesOfField' => array('file' => '/api/ws/crm/GetPickListValuesOfField.php',	'class' => 'Mobile_WS_GetPickListValuesOfField'),
		'GetAllAccessibleUsers' => array('file' => '/api/ws/crm/GetAllAccessibleUsers.php',	'class' => 'Mobile_WS_GetAllAccessibleUsers'),
		'UploadAttachment' => array('file' => '/api/ws/crm/UploadAttachment.php',	'class' => 'Mobile_WS_UploadAttachment'),
		'GetRecordDetail' => array('file' => '/api/ws/HelpDesk/GetRecordDetail.php',	'class' => 'Mobile_WS_GetRecordDetail'),
		'UpdateProfile' => array('file' => '/api/ws/Users/UpdateProfile.php',	'class' => 'Mobile_WS_UpdateProfile'),
		'UpdateLeaveStatus' => array('file' => '/api/ws/Users/UpdateLeaveStatus.php',	'class' => 'Mobile_WS_UpdateLeaveStatus'),
		'GetALLData' => array('file' => '/api/ws/crm/GetALLData.php',	'class' => 'Mobile_WS_GetALLData'),
		'GetRecordModelFromTicket' => array('file' => '/api/ws/ServiceReports/GetRecordModelFromTicket.php',	'class' => 'Mobile_WS_GetRecordModelFromTicket'),
		'GetPincodeInfo' => array('file' => '/api/ws/crm/GetPincodeInfo.php',	'class' => 'Mobile_WS_GetPincodeInfo'),
		'AjaxEdit' => array('file' => '/api/ws/crm/AjaxEdit.php',	'class' => 'Mobile_WS_AjaxEdit'),
		'GetAllPicklistFieldsOfModule' => array('file' => '/api/ws/crm/GetAllPicklistFieldsOfModule.php',	'class' => 'Mobile_WS_GetAllPicklistFieldsOfModule'),
		'DescribeWithGrouping' => array('file' => '/api/ws/crm/DescribeWithGrouping.php',	'class' => 'Mobile_WS_DescribeWithGrouping'),
		'SRStatusPercent' => array('file' => '/api/ws/DashBoard/SRStatusPercent.php',	'class' => 'Mobile_WS_SRStatusPercent'),
		'GetEquipmentDetail' => array('file' => '/api/ws/Equipment/GetEquipmentDetail.php',	'class' => 'Mobile_WS_GetEquipmentDetail'),
		'GetAllAggregateInfo' => array('file' => '/api/ws/ServiceReports/GetAllAggregateInfo.php',	'class' => 'Mobile_WS_GetAllAggregateInfo'),
		'GetDeliveryNotesDetail' => array('file' => '/api/ws/DeliveryNotes/GetDeliveryNotesDetail.php',	'class' => 'Mobile_WS_GetDeliveryNotesDetail'),
		'GetAllMatchingSubAssebly' => array('file' => '/api/ws/ServiceReports/GetAllMatchingSubAssebly.php',	'class' => 'Mobile_WS_GetAllMatchingSubAssebly'),
		'GetWarrantableCheck' => array('file' => '/api/ws/ServiceReports/GetWarrantableCheck.php',	'class' => 'Mobile_WS_GetWarrantableCheck'),
		'GetSalesorderList' => array('file' => '/api/ws/GetSalesorderList.php',	'class' => 'Mobile_WS_GetSalesorderList'),
		'GetRecordModelFromSO' => array('file' => '/api/ws/ServiceOrder/GetRecordModelFromSO.php',	'class' => 'Mobile_WS_GetRecordModelFromSO'),
		'validateSTONumber' => array('file' => '/api/ws/FailedParts/validateSTONumber.php',	'class' => 'Mobile_WS_validateSTONumber'),
		'BGValuesStatusWise' => array('file' => '/api/ws/BankGuarantee/BGValuesStatusWise.php',	'class' => 'Mobile_WS_BGValuesStatusWise'),
		'GetBGList' => array('file' => '/api/ws/BankGuarantee/GetBGList.php',	'class' => 'Mobile_WS_GetBGList'),
		'GetALLPincodeInfo' => array('file' => '/api/ws/crm/GetALLPincodeInfo.php',	'class' => 'Mobile_WS_GetALLPincodeInfo'),
		'GetALLAggregatesInfo' => array('file' => '/api/ws/Equipment/GetALLAggregatesInfo.php',	'class' => 'Mobile_WS_GetALLAggregatesInfo'),
		'DownloadFile' => array('file' => '/api/ws/Documents/DownloadFile.php',	'class' => 'Mobile_WS_DownloadFile'),
		'DownloadPDFReport' => array('file' => '/api/ws/Documents/DownloadPDFReport.php',	'class' => 'Mobile_WS_DownloadPDFReport'),
		'ChangePassword' => array('file' => '/api/ws/crm/ChangePassword.php',	'class' => 'Mobile_WS_ChangePassword'),
		'DeleteAttachment' => array('file' => '/api/ws/Documents/DeleteAttachment.php',	'class' => 'Mobile_WS_DeleteAttachment'),
		'registerDeviceIdWithCRM' => array('file' => '/api/ws/crm/registerDeviceIdWithCRM.php',	'class' => 'Mobile_WS_registerDeviceIdWithCRM'),
		'getCInstanceURL'	=> array('file' => '/api/ws/crm/getCInstanceURL.php',	'class' => 'Mobile_WS_getCInstanceURL'),
		'onboarding' => array('file' => '/api/ws/onBoarding.php',	'class' => 'Mobile_WS_onboarding'),
		'Registration' => array('file' => '/api/ws/Registration.php',	'class' => 'Mobile_WS_UserRegistration'),
		'OwnerRegistration' => array('file' => '/api/ws/OwnerRegistration.php', 'class' => 'Mobile_WS_OwnerRegistration'),
		'OTPGeneration' => array('file' => '/api/ws/OTPGeneration.php', 'class' => 'Mobile_WS_OTPGeneration'),
	);

	protected function initSession(Mobile_API_Request $request) {
		$sessionid = $request->getSession();
		return Mobile_API_Session::init($sessionid);
	}

	protected function getController(Mobile_API_Request $request) {
		$operation = $request->getOperation();
		if (isset(self::$opControllers[$operation])) {
			$operationFile = self::$opControllers[$operation]['file'];
			$operationClass = self::$opControllers[$operation]['class'];

			include_once dirname(__FILE__) . $operationFile;
			$operationController = new $operationClass;
			return $operationController;
		}
	}

	function getUserDetailsUNameAndPass($userUniqueId) {
		global $adb;
		$sql = 'select user_name,user_password from vtiger_users '
			. ' where vtiger_users.id = ? and vtiger_users.deleted = 0';
		$sqlResult = $adb->pquery($sql, array($userUniqueId));
		$num_rows = $adb->num_rows($sqlResult);
		if ($num_rows == 1) {
			$dataRow = $adb->fetchByAssoc($sqlResult, 0);
			return $dataRow;
		} else {
			return false;
		}
	}

	function process(Mobile_API_Request $request) {
		$operation = $request->getOperation();
		$response = false;
		$operationController = $this->getController($request);
		if ($operationController) {
			$operationSession = false;
			if ($operationController->requireLogin()) {
				// $operationSession = $this->initSession($request);
				require __DIR__ . DIRECTORY_SEPARATOR .'autoload.php';
				$userUniqueId = $request->get('useruniqueid');
				if(empty($userUniqueId)){
					$response = new Mobile_API_Response();
					$response->setError(1501, 'userUniqueId is Empty');
					echo $response->emitJSON();
					exit();
				}
				$data = $this->getUserDetailsUNameAndPass($userUniqueId);
				$key = "ONSGVGFDKNBXVDAWTYSVSCDX".$data['user_password'].$data['user_name'];
				$jwt = $request->get('access_token');
				try {
					$decoded = JWT::decode($jwt, new Key($key, 'HS256'));
					global $authenticated_user_id;
					$authenticated_user_id = $decoded->{'userid'};
					$_SESSION["authenticated_user_id"] =  $decoded->{'userid'};
					$operationSession = true;
					$operationController->hasActiveUser();
				} catch (Exception $e) {
					$operationSession = false;
					$response = new Mobile_API_Response();
					$response->setError(1501, 'Invalid Access Token');
				}
			} else {
				// By-pass login
				$operationSession = true;
			}

			if ($operationSession === false) {
				$response = new Mobile_API_Response();
				$response->setError(1501, 'Invalid Access Token');
			} else {

				try {
					$response = $operationController->process($request);
				} catch (Exception $e) {
					$response = new Mobile_API_Response();
					$response->setError($e->getCode(), $e->getMessage());
				}
			}
		} else {
			$response = new Mobile_API_Response();
			$response->setError(1404, 'Operation not found: ' . $operation);
		}

		if ($response !== false) {
			echo $response->emitJSON();
		}
	}

	static function getInstance() {
		$instance = new static();
		return $instance;
	}
}
