<?php

class Mobile_WS_AjaxEdit extends Mobile_WS_Controller {
    function process(Mobile_API_Request $request) {
        $response = new Mobile_API_Response();
        $module = $request->get('module');
        if (empty($module)) {
            $response->setError(100, "Module Is Missing");
            return $response;
        }
        $recordId = $request->get('recordId');
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
            $response->setError(100, "FieldName Is Missing");
            return $response;
        }
        $fieldValue = $request->get('fieldValue');
        if (empty($fieldValue)) {
            $response->setError(100, "fieldValue Is Missing");
            return $response;
        }
        if (!isRecordExists($recordId)) {
            $response->setError(100, "Record You Are Trying To Update Is Does Not Exits");
            return $response;
        }
        $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $module);
        if (!empty($recordModel)) {
            $recordModel->set('mode', 'edit');
            $recordModel->set($fieldName, $fieldValue);
            $otherFielName = $request->get('otherFieldName');
            if(!empty($otherFielName)){
                $recordModel->set($otherFielName, $request->get('otherFieldValue'));
            }

            $otherFielName = $request->get('field_three');
            if(!empty($otherFielName)){
                $recordModel->set($otherFielName, $request->get('field_three_value'));
            }
            $recordModel->save();

            $response->setApiSucessMessage('Updated Successfully');
            $responseObject['message'] = 'Updated Successfully';
            $response->setResult($responseObject);
            return $response;
        } else {
            $response->setError(100, 'Not Able To Update');
            return $response;
        }
    }
}
