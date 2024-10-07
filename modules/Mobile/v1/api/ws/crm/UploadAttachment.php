<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

class Mobile_WS_UploadAttachment extends Mobile_WS_Controller {

    function process(Mobile_API_Request $request) {
        $response = new Mobile_API_Response();
        $module = $request->get('module');

        if (empty($module)) {
            $response->setError(100, "Module Is Missing");
            return $response;
        }
        $recordId = $request->get('recordId');
        if ($module == "Users") {
            global $uploadingUserImageFormTheApi;
            $uploadingUserImageFormTheApi = true;
            $recordId = '19x' . $request->get('useruniqueid');
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
            $sourceFocus = CRMEntity::getInstance($module);
            $recordIdOfUploaded = $sourceFocus->uploadAndSaveFile($recordId, $module, $file, 'Attachment', $fieldName);

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
