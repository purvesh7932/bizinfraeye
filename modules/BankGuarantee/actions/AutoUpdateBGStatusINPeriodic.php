<?php
class BankGuarantee_AutoUpdateBGStatusINPeriodic_Action extends Vtiger_IndexAjax_View {
	public function requiresPermission(\Vtiger_Request $request) {
		$permissions = parent::requiresPermission($request);
		$permissions[] = array('module_parameter' => 'module', 'action' => 'Export');
		return $permissions;
	}

	public function process(Vtiger_Request $request) {
		$response = new Vtiger_Response();
		ini_set('max_execution_time', 0);
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		if ($currentUserModel->isAdminUser()) {
			include_once 'modules/BankGuarantee/AutoUpdateBGStatusINPeriodic.php';
			$entityData = [];
			AutoUpdateBGStatusINPeriodic();
		} else {
			$response->setError("External App Sync Not Allowed");
			$response->emit();
		}
	}
}
