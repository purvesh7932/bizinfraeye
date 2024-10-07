<?php
include_once 'include/Webservices/Retrieve.php';
include_once dirname(__FILE__) . '/../FetchRecord.php';
include_once 'include/Webservices/DescribeObject.php';
include_once('include/utils/GeneralUtils.php');
class Mobile_WS_DownloadFile extends Mobile_WS_FetchRecord {

	function process(Mobile_API_Request $request) {
		$response = new Mobile_API_Response();
		$record = $request->get('record');
		if (empty($record)) {
			$response->setError(100, "Record Is Missing");
			return $response;
		}
		$permitted = isPermitted('Documents', 'DetailView', $record);
		if (strcmp($permitted, "yes") === 0) {
		} else {
			$response->setError(100, 'Permission to read given object is denied');
			return $response;
		}

		$moduleName = 'Documents';
		$modCommentsRecordModel = Vtiger_Record_Model::getInstanceById($record, $moduleName);
		$attachmentId = $request->get('fileid');
		$modCommentsRecordModel->downloadFile($attachmentId);
		return $response;
	}
}
