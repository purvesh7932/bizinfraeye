<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.2
 * ("License.txt"); You may not use this file except in compliance with the License
 * The Original Code is: Vtiger CRM Open Source
 * The Initial Developer of the Original Code is Vtiger.
 * Portions created by Vtiger are Copyright (C) Vtiger.
 * All Rights Reserved.
 * ***********************************************************************************/

define('KB', 1024);
define('MB', 1048576);
define('GB', 1073741824);
define('TB', 1099511627776);
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);
class Portal_UploadAttachment_API extends Portal_Default_API {

	public function process(Portal_Request $request) {
		set_time_limit(0);
		ini_set('memory_limit', '1024M');
		ini_set('display_errors', 0);
		ini_set('display_startup_errors', 0);
		error_reporting(0);

		$response = new Portal_Response();
		$module = $request->get('module');

		if (empty($module)) {
			$response->setError(100, "Module Is Missing");
			return $response;
		}
		$recordId = $request->get('recordId');
		if ($module == "Contacts") {
			$recordId = '12x' . $request->get('useruniqueid');
		}
		if (empty($recordId)) {
			$response->setError(100, "recordId Is Missing");
			return $response;
		}
		if (strpos($recordId, 'x') == false) {
			$response->setError(100, 'RecordId Is Not Webservice Format');
			return $response;
		}
		$recordId = explode('x', $recordId);
		$recordId = $recordId[1];

		$fieldName = $request->get('fieldName');
		if (empty($fieldName)) {
			$response->setError(100, "fieldName Is Missing");
			return $response;
		}
		$file = $_FILES[$fieldName];
		if (empty($file)) {
			$response->setError(100, "Uploaded File Is Missing");
			return $response;
		}
		global $upload_maxsize;
		if ($file['size'] < $upload_maxsize) {
			global $current_user;
			global $uploadingUserImageFormTheApi;
            $uploadingUserImageFormTheApi = true;
			if (!$current_user) {
				$current_user = Users::getActiveAdminUser();
			}
			$sourceFocus = CRMEntity::getInstance($module);
			if ($module == "Contacts") {
				$recordIdOfUploaded = $sourceFocus->uploadAndSaveFile($recordId, $module, $file, 'Image', $fieldName);
			} else {
				$recordIdOfUploaded = $sourceFocus->uploadAndSaveFile($recordId, $module, $file, 'Attachment', $fieldName);
			}
			if ($recordIdOfUploaded) {
				$ResponseObject['uploadedAttachmentId'] = $recordIdOfUploaded;
				$response->setResult($ResponseObject);
				$response->setApiSucessMessage('Successfully Uploaded Attachment');
				return $response;
			} else {
				$response->setError(100, "Failed to Upload Attachment");
				return $response;
			}
		} else {
			$response->setError(100, "Filesize larger than $upload_maxsize bytes");
			return $response;
		}
	}
}
