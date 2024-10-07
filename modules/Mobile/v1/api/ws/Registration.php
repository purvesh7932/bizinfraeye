<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

class Mobile_WS_UserRegistration extends Mobile_WS_Controller {
    function process(Mobile_API_Request $request) {
        $response = new Mobile_API_Response();

        $userName = $_POST['service_engineer_name'] ?? '';
        $userId = $_POST['badge_no'] ?? '';
        $aadharNo = $_POST['aadhar_no'] ?? '';
        $mobileNo = $_POST['phone'] ?? '';
        $profilePic = $_FILES['emp_imagefile']['name'] ?? '';  
        $setpassword = $_POST['user_password'] ?? '';
        $confirmpassword = $_POST['confirm_password'] ?? '';
        $role = $_POST['sub_service_manager_role'] ?? '';

        if ($setpassword !== $confirmpassword) {
            $response->setError(400, 'Password check both password match');
            return $response;
        }

        if (empty($userName) || empty($userId) || empty($aadharNo) || empty($mobileNo)|| empty($profilePic) || empty($role) || empty($setpassword) || empty($confirmpassword)) {
            $response->setError(400, 'Missing required fields');
            return $response;
        }
       
    

        $recordModel = Vtiger_Record_Model::getCleanInstance('ServiceEngineer');
        
        $recordModel->set('service_engineer_name', $userName);
        $recordModel->set('user_password', $setpassword);
        $recordModel->set('confirm_password', $confirmpassword);
        $recordModel->set('phone', $mobileNo);
        $recordModel->set('badge_no', $userId);
        $recordModel->set('aadhar_no', $aadharNo);
        $recordModel->set('emp_imagefile', $profilePic);
        $recordModel->set('sub_service_manager_role', $role);

        try {
            $recordModel->save();
            $recordId = $recordModel->getId();
            if (!$recordId) {
                $response->setError(500, 'Failed to register user');
                return $response;
            }
        } catch (Exception $e) {
            $response->setError(500, 'Error saving record: ' . $e->getMessage());
            return $response;
        }

        $savedRecord = Vtiger_Record_Model::getInstanceById($recordId, 'ServiceEngineer');
        $savedData = array(
            'service_engineer_name' => $savedRecord->get('service_engineer_name'),
            'user_password' => $savedRecord->get('user_password'),
            'confirm_password' => $savedRecord->get('confirm_password'),
            'phone' => $savedRecord->get('phone'),
            'badge_no' => $savedRecord->get('badge_no'),
            'aadhar_no' => $savedRecord->get('aadhar_no'),
            'emp_imagefile' => $savedRecord->get('emp_imagefile'),
            'sub_service_manager_role' => $savedRecord->get('sub_service_manager_role')
        );
        $response->setResult(array(
      
           
                'record' => $savedData,
                'message' => 'Successfully Created user',
            
        ));
        $response->setApiSucessMessage('Successfully  register user');
        return $response; 
    }
}

?>
