<?php
require_once('include/utils/GeneralUtils.php');
class HelpDesk_GetAllInfoDA_Action extends Vtiger_IndexAjax_View {

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
		$referenceFields = array('account_id');
		foreach($referenceFields as $referenceField){
			if(!empty($data[$referenceField]) &&  isRecordExists($data[$referenceField])){
				$recInstance = Vtiger_Record_Model::getInstanceById($data[$referenceField], 'Accounts');
				$data['cityOfEquipment'] = $recInstance->get('bill_city');
				$data[$referenceField.'_label'] = $recInstance->get('label');
			}
		}
		$referenceFields = array('equipment_id');
		foreach($referenceFields as $referenceField){
			if(!empty($data[$referenceField]) &&  isRecordExists($data[$referenceField])){
				$recInstance = Vtiger_Record_Model::getInstanceById($data[$referenceField], 'Equipment');
				$data['eq_last_hmr'] = $recInstance->get('eq_last_hmr');
				$data['eq_last_km_run'] = $recInstance->get('eq_last_km_run');
				$data['func_loc_id'] = $recInstance->get('functional_loc');
			}
		}
		$response->setResult(array('success'=>true, 'data'=>array_map('decode_html',$data)));
		
		$response->emit();
	}
}
