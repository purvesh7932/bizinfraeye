<?php
class ServiceReports_GetDetailsForSubAssAutoFill_Action extends Vtiger_Action_Controller {

	public function process(Vtiger_Request $request) {
		$record = $request->get('record');
		$sad_sub_ass_po_det = $request->get('sad_sub_ass_po_det');
		$query = "SELECT * FROM vtiger_servicereports
		LEFT JOIN vtiger_servicereportscf ON vtiger_servicereportscf.servicereportsid = vtiger_servicereports.servicereportsid
		LEFT JOIN vtiger_inventoryproductrel ON vtiger_inventoryproductrel.id = vtiger_servicereports.servicereportsid 
		 LEFT JOIN vtiger_inventoryproductrel_other ON vtiger_inventoryproductrel_other.id = vtiger_servicereports.servicereportsid
		 where vtiger_servicereportscf.servicereportsid = ? and vtiger_inventoryproductrel_other.sad_sub_ass_po_det = ?";
		$db = PearDatabase::getInstance();
		$result = $db->pquery($query, array($record, $sad_sub_ass_po_det));
		$records = array();
		while ($row = $db->fetchByAssoc($result)) {
			array_push($records, $row);
		}
		if (!empty($records[0]['sad_date_oracs'])) {
			$dateValue = $records[0]['sad_date_oracs'];
			$date = new DateTimeField($dateValue);
			global $current_user;
			$convertedDate = $date->getDisplayDate($current_user);
			$records[0]['sad_date_oracs'] = $convertedDate;
		}
		$response = new Vtiger_Response();
		$response->setResult(array('success' => true, 'data' => $records[0]));
		$response->emit();
	}
}
