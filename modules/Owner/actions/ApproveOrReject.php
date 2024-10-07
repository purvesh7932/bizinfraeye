<?php
class Owner_ApproveOrReject_Action extends Vtiger_IndexAjax_View {

	public function requiresPermission(\Vtiger_Request $request) {
		$permissions = parent::requiresPermission($request);
		$permissions[] = array('module_parameter' => 'source_module', 'action' => 'DetailView', 'record_parameter' => 'record');
		return $permissions;
	}

	public function process(Vtiger_Request $request) {
		$record = $request->get('record');
		$sourceModule = $request->get('source_module');
		$statusValue = $request->get('apStatus');
		$response = new Vtiger_Response();

		$recordModel = Vtiger_Record_Model::getInstanceById($record, $sourceModule);
		if (!empty($recordModel)) {
			$recordModel->set('mode', 'edit');
			global $ajaxEditingInSEmod;
			$ajaxEditingInSEmod = true;
			$recordModel->set('approval_status', $statusValue);
			if ($statusValue == 'Rejected') {
				$rejectionReason = $request->get('RejectionReason');
				if (empty($rejectionReason)) {
					$response->setResult(array('success' => false, 'message' => 'Rejection Reason Is Empty'));
				}
				$recordModel->set('rejection_reason', $rejectionReason);
			}
			$recordModel->save();
			if ($statusValue == 'Rejected') {
				$response->setResult(array('success' => true, 'message' => 'Successfuly Rejected'));
			} else {
				$response->setResult(array('success' => true, 'message' => 'Successfuly Approved'));
			}
		} else {
			$response->setResult(array('success' => false, 'message' => 'Not Able To Approve Or Reject'));
		}
		$response->emit();
	}
}
