<?php
include_once('include/utils/GeneralUtils.php');
class Portal_GetEquipmentDetail_API extends Portal_Default_API {

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
		$contactId = Portal_Session::get('contact_id');
		$hasAccess =  isInAllowedInLinkedEquipmentsContacts($contactId , $record);
		if ($hasAccess) {
			$sourceModule = $request->get('module');
			$recordModel = Vtiger_Record_Model::getInstanceById($record, $sourceModule);
			$data = $recordModel->getData();
			$referenceFields = array('account_id' => 'Accounts', 'functional_loc' => 'FunctionalLocations');
			foreach ($referenceFields as $referenceField => $val) {
				if ($data[$referenceField] == "0") {
					$data[$referenceField . '_label'] = '';
					$data[$referenceField] = '';
				} else {
					$moduleWSID = Portal_Default_API::getEntityModuleWSId($val);
					$data[$referenceField . '_label'] = Vtiger_Functions::getCRMRecordLabel($data[$referenceField]);
					$data[$referenceField] = $moduleWSID . 'x' . $data[$referenceField];
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
