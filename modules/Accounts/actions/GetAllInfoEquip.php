<?php
class Accounts_GetAllInfoEquip_Action extends Vtiger_IndexAjax_View {

	public function requiresPermission(\Vtiger_Request $request) {
		$permissions = parent::requiresPermission($request);
		$permissions[] = array('module_parameter' => 'source_module', 'action' => 'DetailView', 'record_parameter' => 'record');
		return $permissions;
	}
	
	public function process(Vtiger_Request $request) {
		$record = $request->get('record');
		$sourceModule = $request->get('source_module');
		$response = new Vtiger_Response();

		$recordModel = Vtiger_Record_Model::getInstanceById($record, $sourceModule);
		$data = $recordModel->getData();

		// global $adb;
		// $sql = "SELECT productname,model_number,warrenty_period FROM `vtiger_equipment` where equipmentid = ?";
		// $sqlResult = $adb->pquery($sql, array($data['equipment_id']));
		// $num_rows = $adb->num_rows($sqlResult);
		// $rowsValue = [];
		// if ($num_rows > 0) {
		// 	while ($row = $adb->fetch_array($sqlResult)) {
		// 		$rowsValue = $row;
		// 	}
		// }
		// $data['model_number'] = $rowsValue['model_number'];
		// $data['warrenty_period'] = $rowsValue['warrenty_period'];
		// $data['productname'] = $rowsValue['productname'];

		$response->setResult(array('success'=>true, 'data'=>array_map('decode_html',$data)));
		
		$response->emit();
	}
}
