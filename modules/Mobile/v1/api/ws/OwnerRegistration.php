<?php


class Mobile_WS_OwnerRegistration extends Mobile_WS_Controller {
    function process(Mobile_API_Request $request) {
        
        $response = new Mobile_API_Response();
        $ownerName = $_POST['owntenant_name'] ?? '';
        $ownerMobile = $_POST['owntenant_mobile'] ?? '';
        $ownerEmail = $_POST['owntenant_email'] ?? '';
        $ownerPass = $_POST['owntenant_password'] ?? '';
        // $societyName = $_POST['society_name'] ?? '';
        // $selectBlock = $_POST['select_block'] ?? '';
        // $societyNo = $_POST['select_society_no'] ?? '';
        // $ownerRole = $_POST['owntenant_role'] ?? '';
        if (empty($ownerName) || empty($ownerMobile) || empty($ownerEmail) || empty($ownerPass)) {
            $response->setError(400, 'Missing required fields');
            return $response;
        }
        $recordModel = Vtiger_Record_Model::getCleanInstance('Owner');
        $recordModel->set('owntenant_name', $ownerName);
        $recordModel->set('owntenant_mobile', $ownerMobile);
        $recordModel->set('owntenant_email', $ownerEmail);
        $recordModel->set('owntenant_password', $ownerPass);
        // $recordModel->set('society_name', $societyName);
        // $recordModel->set('select_block', $selectBlock);
        // $recordModel->set('select_society_no', $societyNo);
        // $recordModel->set('owntenant_role', $ownerRole);

        try {
            $recordModel->save();
            $recordId = $recordModel->getId();
            if (!$recordId) {
                $response->setError(500, 'Failed to register owner');
                return $response;
            }
        } catch (Exception $e) {
            $response->setError(500, 'Error saving record: ' . $e->getMessage());
            return $response;
        }
        $savedRecord = Vtiger_Record_Model::getInstanceById($recordId, 'Owner');
        $savedData = array(
            'owntenant_name' => $savedRecord->get('owntenant_name'),
            'owntenant_mobile' => $savedRecord->get('owntenant_mobile'),
            'owntenant_email' => $savedRecord->get('owntenant_email'),
            'owntenant_password' => $savedRecord->get('owntenant_password'),
            // 'society_name' => $savedRecord->get('society_name'),
            // 'select_block' => $savedRecord->get('select_block'),
            // 'select_society_no' => $savedRecord->get('select_society_no'),
            // 'owntenant_role' => $savedRecord->get('owntenant_role')
        );

        $response->setResult(array(
            'record' => $savedData,
            'message' => 'Successfully registered owner'
        ));
        $response->setApiSucessMessage('Successfully added fields');
        return $response;
    }
}
?>
