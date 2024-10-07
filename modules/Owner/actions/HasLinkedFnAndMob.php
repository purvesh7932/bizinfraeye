<?php
require_once('include/utils/GeneralUtils.php');
class Owner_HasLinkedFnAndMob_Action extends Vtiger_IndexAjax_View {

	public function requiresPermission(\Vtiger_Request $request) {
		$permissions = parent::requiresPermission($request);
		$permissions[] = array(
			'module_parameter' => 'source_module',
			'action' => 'DetailView', 'record_parameter' => 'record'
		);
		return $permissions;
	}

	public function process(Vtiger_Request $request) {
		$record = $request->get('record');
		$module = $request->get('module');
		$response = new Vtiger_Response();
		// $noLinkedFnAndMob = false;
		// $functionalLocations = getAllAssociatedFunctionalLocationsSE('36x' . $record);
		// $noFunctionalLocation = false;
		// $noLinekedPlatForm = false;
		// if (empty($functionalLocations)) {
		// 	$noLinkedFnAndMob = true;
		// 	$noFunctionalLocation = true;
		// }

		// $recordModel = Vtiger_Record_Model::getInstanceById($record, $module);
		// $accessingPortal = $recordModel->get('ser_usr_log_plat');

		// if (empty($accessingPortal)) {
		// 	$noLinkedFnAndMob = true;
		// 	$noLinekedPlatForm = true;
		// }
		// $data = getUserDetailsBasedOnEmployeeModuleG($recordModel->get('badge_no'));
		// if (
		// 	$data && $data['cust_role'] == 'Service Manager' &&
		// 	$data['sub_service_manager_role'] == 'Regional Manager'
		// ) {
		// 	$noFunctionalLocation = false;
		// }
		// if (
		// 	$data && $data['cust_role'] == 'Service Manager' &&
		// 	$data['sub_service_manager_role'] == 'Regional Service Manager'
		// ) {
		// 	$noFunctionalLocation = false;
		// }

		// if ($noLinkedFnAndMob == true) {
		// 	// if ($noFunctionalLocation) {
		// 	// 	$response->setError('No Functonal Location Is Linked To This User');
		// 	// } else if ($noLinekedPlatForm) {
		// 	// 	$response->setError('No Accessing Postal is Defined To This User');
		// 	// }
		// } else {
		// 	$response->setResult(array(
		// 		'success' => true,
		// 		'message' => 'Has Functional Location And Accessing Portals'
		// 	));
		// }
		$response->setResult(array(
			'success' => true,
			'message' => 'Has Functional Location And Accessing Portals'
		));
		$response->emit();
	}
}
