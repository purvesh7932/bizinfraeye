<?php

class ServiceReports_GetAllAggregateInfo_Action extends Vtiger_IndexAjax_View {

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
		$eqSerialNo = $recordModel->get('equipment_sl_no');
		$aggregate = $request->get('aggregate');
		$aggregateRecord = '';
		if($aggregate == 'Engine'){
			$aggregateRecord = $eqSerialNo . '-' .'EN';
		} else if ($aggregate == 'Transmission') {
            $aggregateRecord = $eqSerialNo . '-' . 'TM';
        } else if ($aggregate == 'Final Drive') {
            $aggregateRecord = $eqSerialNo . '-' . 'FD';
        }
		global $adb;
		$sql = "select equipmentid from vtiger_equipment
		 INNER JOIN vtiger_crmentity 
		 ON vtiger_crmentity.crmid = vtiger_equipment.equipmentid 
		 where equipment_sl_no = ? and vtiger_crmentity.deleted = 0";
		$sqlResult = $adb->pquery($sql, array($aggregateRecord));
		$num_rows = $adb->num_rows($sqlResult);
		if ($num_rows > 0) {
			$dataRow = $adb->fetchByAssoc($sqlResult, 0);
			$agRecordModel = Vtiger_Record_Model::getInstanceById($dataRow['equipmentid'], 'Equipment');
			$data = $agRecordModel->getData();
		} else {
			$data = [];
		}
		$response->setResult(array('success'=>true, 'data'=>array_map('decode_html',$data)));
		
		$response->emit();
	}
}
