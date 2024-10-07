<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class Users_Login_Action extends Vtiger_Action_Controller {

	function loginRequired() {
		return false;
	}

	function checkPermission(Vtiger_Request $request) {
		return true;
	}

	function getAccessiblePlatforms($userName) {
		global $adb;
		$sql = 'select cust_role,ser_usr_log_plat,badge_no,approval_status from vtiger_serviceengineer
		INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_serviceengineer.serviceengineerid
		where badge_no = ? ORDER BY serviceengineerid DESC LIMIT 1';
		$result = $adb->pquery($sql, array($userName));
		$loginPlatforms = '';
		while ($row = $adb->fetch_array($result)) {
			$loginPlatforms =  $row;
		}
		return $loginPlatforms;
	}

	function process(Vtiger_Request $request) {
		$username = $request->get('username');
		$password = $request->getRaw('password');

		$user = CRMEntity::getInstance('Users');
		$user->column_fields['user_name'] = $username;
		$loginPlatDEtails = $this->getAccessiblePlatforms($username);
		if(empty($loginPlatDEtails) && $username != 'admin' && $username != 'masteradmin'){
			header ('Location: index.php?module=Users&parent=Settings&view=Login&error=noRegistration');
			exit;
		}
		/*if($loginPlatDEtails['approval_status'] !== 'Accepted' && $loginPlatDEtails['approval_status'] !== 'Rejected' && $username != 'admin' && $username != 'masteradmin'){
			header ('Location: index.php?module=Users&parent=Settings&view=Login&error=approvalPending');
			exit;
		}*/
		if($loginPlatDEtails['approval_status'] == 'Rejected' && $username != 'admin' && $username != 'masteradmin'){
			header ('Location: index.php?module=Users&parent=Settings&view=Login&error=approvalRejected');
			exit;
		}
// 		$loginPlat =  $loginPlatDEtails['ser_usr_log_plat'];
// 		if(($loginPlat != 'Both' && $loginPlat != 'Web Portal') && $username != 'admin' && $username != 'masteradmin'){
// 			$webPlatStatus = false;
// 			header ('Location: index.php?module=Users&parent=Settings&view=Login&error=platformerror');
// 			exit;
// 		} else {
			$webPlatStatus = true;
// 		}

		if ($user->doLogin($password)) {
			session_regenerate_id(true); // to overcome session id reuse.

			$userid = $user->retrieve_user_id($username);
			Vtiger_Session::set('AUTHUSERID', $userid);

			// For Backward compatability
			// TODO Remove when switch-to-old look is not needed$loginPlatDEtails
			$_SESSION['authenticated_user_id'] = $userid;
			$_SESSION['authenticated_user_role'] = $loginPlatDEtails['cust_role'];
			$_SESSION['app_unique_key'] = vglobal('application_unique_key');
			$_SESSION['authenticated_user_language'] = vglobal('default_language');

			//Enabled session variable for KCFINDER 
			$_SESSION['KCFINDER'] = array(); 
			$_SESSION['KCFINDER']['disabled'] = false; 
			$_SESSION['KCFINDER']['uploadURL'] = "test/upload"; 
			$_SESSION['KCFINDER']['uploadDir'] = "../test/upload";
			$deniedExts = implode(" ", vglobal('upload_badext'));
			$_SESSION['KCFINDER']['deniedExts'] = $deniedExts;
			// End
			
			if($webPlatStatus == true || $username == 'admin' || $username == 'masteradmin') {
				Vtiger_Session::set('canUseWebPortal', true);
			} else {
				Vtiger_Session::set('canUseWebPortal', false);
			}

			//Track the login History
			$moduleModel = Users_Module_Model::getInstance('Users');
			$moduleModel->saveLoginHistory($user->column_fields['user_name']);
			//End
						
			if(isset($_SESSION['return_params'])){
				$return_params = $_SESSION['return_params'];
			}

			header ('Location: index.php?module=Users&parent=Settings&view=SystemSetup');
			exit();
		} else {
			header ('Location: index.php?module=Users&parent=Settings&view=Login&error=login');
			exit;
		}
	}

}
