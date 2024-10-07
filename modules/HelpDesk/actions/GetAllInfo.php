<?php
require_once('include/utils/GeneralUtils.php');
class HelpDesk_GetAllInfo_Action extends Vtiger_IndexAjax_View {

	public function requiresPermission(\Vtiger_Request $request) {
		$record = $request->get('record');
		if($request->get('source_module') == 'Equipment' && isInAllowedFunctionalLocation($record)){
			return [];
		} else {
			$permissions = parent::requiresPermission($request);
			$permissions[] = array('module_parameter' => 'source_module', 'action' => 'DetailView', 'record_parameter' => 'record');
			return $permissions;
		}
	}
	
	public function process(Vtiger_Request $request) {
		$record = $request->get('record');
		$sourceModule = $request->get('source_module');
		$response = new Vtiger_Response();

		$recordModel = Vtiger_Record_Model::getInstanceById($record, $sourceModule);
		$data = $recordModel->getData();
		$referenceFields = array('account_id','functional_loc');
		foreach($referenceFields as $referenceField){
			$data[$referenceField.'_label'] = Vtiger_Functions::getCRMRecordLabel($data[$referenceField]);
		}
		$response->setResult(array('success'=>true, 'data'=>array_map('decode_html',$data)));
		
		$response->emit();
	}
}
