<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class Users_SystemSetup_View extends Vtiger_Index_View {
	
	public function requiresPermission(\Vtiger_Request $request) {
		return array();
	}
    
    public function preProcess(Vtiger_Request $request, $display=true) {
		return true;
	}
	
	public function process(Vtiger_Request $request) {
		$userModel = Users_Record_Model::getCurrentUserModel();
		$userSetupStatus = $userModel->isFirstTimeLogin($userModel->id);
		if($userSetupStatus == true) {
			header ('Location: index.php?module=Users&parent=Settings&view=UserSetupAnother');
			exit();
		} else {
			header ('Location: index.php');
			exit();
		}
	}
	
	function postProcess(Vtiger_Request $request) {
		return true;
	}
	
}