<?php
class ServiceEngineer_RejectionReason_View extends Vtiger_Index_View {

	function __construct() {
		parent::__construct();
		$this->exposeMethod('showRejectionReasonForm');
	}

	public function requiresPermission(Vtiger_Request $request) {
		$permissions = parent::requiresPermission($request);
		$permissions[] = array('module_parameter' => 'module', 'action' => 'DetailView');
		return $permissions;
	}

	function process(Vtiger_Request $request) {
		$mode = $request->getMode();
		if (!empty($mode)) {
			echo $this->invokeExposedMethod($mode, $request);
			return;
		}
	}

	function showRejectionReasonForm($request) {
		$moduleName = $request->getModule();

		$viewer = $this->getViewer($request);
		$viewer->assign("MODULE", $moduleName);
		echo $viewer->view('ShowRejectionREasonForm.tpl', $moduleName, true);
	}
}
