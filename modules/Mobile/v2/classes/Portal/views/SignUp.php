<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.2
 * ("License.txt"); You may not use this file except in compliance with the License
 * The Original Code is: Vtiger CRM Open Source
 * The Initial Developer of the Original Code is Vtiger.
 * Portions created by Vtiger are Copyright (C) Vtiger.
 * All Rights Reserved.
 * ***********************************************************************************/
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
class Portal_SignUp_View extends Portal_Default_View {

	public function requireLogin() {
		return false;
	}

	public function process(Portal_Request $request) {
		$viewer = $this->getViewer($request);
		if ($request->getView() == 'Login') {
			$viewer->assign('OLDURL', $this->oldPortalLogin());
		}
		
		$result = Vtiger_Connector::getInstance()->describeModuleForSignUp('Contacts', '');
		// print_r($result['describe']['blocks']);
		// die();
		$viewer->assign('CONTACTBLOCKS', $result['describe']['blocks']);
		$viewer->display($this->templateFile($request));
	}

}
