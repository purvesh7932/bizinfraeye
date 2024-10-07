<?php
class ServiceReports_ArrgateDetails_Action extends Vtiger_Action_Controller {

	public function process(Vtiger_Request $request) {
		$record = $request->get('record');
		$aggregate = $request->get('aggregate');
		$query = "SELECT i.apmd_peridic_maint_type,i.sad_ag_sl_no,i.sad_sel_ag_name,
		i.sad_whoa,i.sad_manu_name,i.sad_dof FROM vtiger_inventoryproductrel_other AS i
                  LEFT JOIN vtiger_servicereports AS s ON s.servicereportsid = i.id
				  LEFT JOIN vtiger_servicereportscf AS scf ON scf.servicereportsid = i.id
				  LEFT JOIN vtiger_servicereports_other AS vtiger_servicereports_other ON vtiger_servicereports_other.servicereportsid = i.id
                  WHERE s.sr_ticket_type = 'PERIODICAL MAINTENANCE' AND s.equipment_id = ? 
				  AND i.sad_sel_ag_name=? and vtiger_servicereports_other.at_copm = 'Completed'";
		$db = PearDatabase::getInstance();
		$result = $db->pquery($query, array($record, $aggregate));
		$records = array();
		while ($row = $db->fetchByAssoc($result)) {
			array_push($records, $row);
		}
		$response = new Vtiger_Response();
		if (empty($records)) {
			$sourceModule = $request->get('source_module');
			$recordModel = Vtiger_Record_Model::getInstanceById($record, $sourceModule);
			$eqSerialNo = $recordModel->get('equipment_sl_no');
			$aggregateRecord = '';
			if ($aggregate == 'Engine') {
				$aggregateRecord = $eqSerialNo . '-' . 'EN';
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
				$recordInfo = [];
				$recordInfo['sad_manu_name'] = $data['equip_ag_manu_fact'];
				$recordInfo['sad_ag_sl_no'] = $data['equip_ag_serial_no'];
				$recordInfo['sad_whoa'] = $data['eq_last_hmr'];
				$recordInfo['sad_dof'] = $data['eq_valid_from'];
				$recordInfo['sad_sel_ag_name'] = $aggregate;
				$recordInfos = [];
				array_push($recordInfos, $recordInfo);
			} else {
				$recordInfos = [];
			}
			$response->setResult(array('success' => true, 'data' => $recordInfos));
		} else {
			$response->setResult(array('success' => true, 'data' => $records));
		}
		$response->emit();
	}
}
