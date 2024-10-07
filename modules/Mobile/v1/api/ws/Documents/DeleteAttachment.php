<?php
class Mobile_WS_DeleteAttachment extends Mobile_WS_Controller {

	function process(Mobile_API_Request $request) {
		$response = new Mobile_API_Response();
		$recordId = $request->get('record');
		$moduleName = $request->get('module');
		if (empty($recordId)) {
			$response->setError(100, "Record Is Missing");
			return $response;
		}
		if (strpos($recordId, 'x') == false) {
			$response->setError(100, 'Record Is Not Webservice Format');
			return $response;
		}
		$recordId = explode('x', $recordId);
		$recordId = $recordId[1];
		if (empty($moduleName)) {
			$response->setError(100, "Module Name Is Missing");
			return $response;
		}
		try {
			$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
			$recordModel->set('id', $recordId);
			$imageIds = $request->get('imageid');
			if (gettype($imageIds) == 'string') {
				$imageIds = json_decode($imageIds, true);
			}
			foreach ($imageIds as $imageId) {
				$status = $recordModel->deleteImage($imageId);
			}
			$response->setApiSucessMessage('Successfully Deleted Attachemnts');
			$responseObject['message'] = 'Successfully Deleted Attachemnts';
			$response->setResult($responseObject);
			return $response;
		} catch (Exception $e) {
			$response->setError(0, 'Record does not exist');
			return $response;
		}
	}
}
