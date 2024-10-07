<?php
include_once 'include/Webservices/Retrieve.php';
include_once dirname(__FILE__) . '/../FetchRecord.php';
include_once 'include/Webservices/DescribeObject.php';
include_once('include/utils/GeneralUtils.php');
class Mobile_WS_GetDeliveryNotesDetail extends Mobile_WS_FetchRecord {

	function process(Mobile_API_Request $request) {
		$response = new Mobile_API_Response();
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

		$hasAccess = true; // isInAllowedFunctionalLocation($record);
		if ($hasAccess) {
			$sourceModule = $request->get('module');
			if (empty($sourceModule)) {
				$response->setError(100, "module Is Missing");
				return $response;
			}
			$recordModel = Vtiger_Record_Model::getInstanceById($record, $sourceModule);
			$data = $recordModel->getData();
			$referenceFields = array('account_id');
			foreach ($referenceFields as $referenceField) {
				if (!empty($data[$referenceField]) &&  isRecordExists($data[$referenceField])) {
					$recInstance = Vtiger_Record_Model::getInstanceById($data[$referenceField], 'Accounts');
					$data['cityOfEquipment'] = $recInstance->get('bill_city');
					$data[$referenceField . '_label'] = $recInstance->get('label');
					$data['account_id'] = '11x' . $data[$referenceField];
				} else {
					$data['cityOfEquipment'] = NULL;
					$data[$referenceField . '_label'] = NULL;
					$data['account_id'] = NULL;
				}
			}
			$equipmentSerialNum = $data['manual_equ_ser'];
			if (strpos($equipmentSerialNum, "-") !== false) {
				$equipmentSerialNumCodes = explode('-', $equipmentSerialNum);
				$data['equip_model'] = $equipmentSerialNumCodes[0];
			} else {
				$data['equip_model'] = "";
			}
			$referenceFields = array('equipment_id');
			foreach ($referenceFields as $referenceField) {
				if (!empty($data[$referenceField]) &&  isRecordExists($data[$referenceField])) {
					$recInstance = Vtiger_Record_Model::getInstanceById($data[$referenceField], 'Equipment');
					$data['eq_last_hmr'] = $recInstance->get('eq_last_hmr');
					$data['eq_last_km_run'] = $recInstance->get('eq_last_km_run');
					$data['equipment_id'] = '38x' . $data[$referenceField];
				} else {
					$data['eq_last_hmr'] = NULL;
					$data['eq_last_km_run'] = NULL;
					$data['equipment_id'] = NULL;
				}
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
