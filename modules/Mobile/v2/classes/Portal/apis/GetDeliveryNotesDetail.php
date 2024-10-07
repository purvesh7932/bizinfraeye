<?php
include_once('include/utils/GeneralUtils.php');
class Portal_GetDeliveryNotesDetail_API extends Portal_Default_API {

	public function preProcess(Portal_Request $request) {
	}

	public function postProcess(Portal_Request $request) {
	}

	public function process(Portal_Request $request) {
		$response = new Portal_Response();
		$record = $request->get('record');
		if (empty($record)) {
			$response->setError(100, "Record Is Missing");
			return $response;
		}
		if (strpos($record, 'x') == false) {
			$response->setError(100, 'Record Is Not Webservice Format');
			return $response;
		}
		$record = explode('x', $record);
		$record = $record[1];

		$hasAccess = true;
		if ($hasAccess) {
			$sourceModule = $request->get('module');
			$recordModel = Vtiger_Record_Model::getInstanceById($record, $sourceModule);
			$data = $recordModel->getData();
			$referenceFields = array('account_id');
			foreach ($referenceFields as $referenceField) {
				if (!empty($data[$referenceField]) &&  isRecordExists($data[$referenceField])) {
					$recInstance = Vtiger_Record_Model::getInstanceById($data[$referenceField], 'Accounts');
					$data['cityOfEquipment'] = $recInstance->get('bill_city');
					$data[$referenceField . '_label'] = $recInstance->get('label');
				}
			}
			$equipmentSerialNum = $data['manual_equ_ser'];
			if (strpos($equipmentSerialNum, "-") !== false) {
				$equipmentSerialNumCodes = explode('-', $equipmentSerialNum);
				$data['equip_model'] = $equipmentSerialNumCodes[0];
			} else {
				$data['equip_model'] = "";
			}

			$responseObject = array_map('decode_html', $data);
			$response->setApiSucessMessage('Successfully Fetched Data');
			$response->setResult($responseObject);
		} else {
			$response->setError(100, 'Permission to read given object is denied');
		}
		return $response;
	}
}
