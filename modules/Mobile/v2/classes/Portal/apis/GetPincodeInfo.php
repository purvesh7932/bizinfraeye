<?php

class Portal_GetPincodeInfo_API extends Portal_Default_API {

	public function requireLogin() {
		return false;
	}

	public function preProcess(Portal_Request $request) {
	}

	public function postProcess(Portal_Request $request) {
	}

	public function process(Portal_Request $request) {
		// $wholeRequest = array_merge($request->getAll(),$_REQUEST);
		// $responseObject = Vtiger_Connector::getInstance()->GetPincodeInfo($wholeRequest);
		// $result = array();
		$response = new Portal_Response();
		$pincode = $request->get('pincode');
        global $pincodeDatabaseName, $pincodeDatabaseUser, $pincodeDatabaseNamePassword;
        $connection = mysqli_connect("localhost", $pincodeDatabaseUser, $pincodeDatabaseNamePassword, $pincodeDatabaseName);
        if (mysqli_connect_errno()) {
            $response->setError(100, 'Not Able To Fetch Pincode Details');
            return $response;
        }
        $sql = "SELECT * FROM $pincodeDatabaseName.vtiger_pincodes inner join $pincodeDatabaseName.vtiger_pincodescf " .
            " on $pincodeDatabaseName.vtiger_pincodescf.pincodesid = $pincodeDatabaseName.vtiger_pincodes.pincodesid " .
            " WHERE pincode = ?";
        $stmt = $connection->prepare($sql);
        $stmt->bind_param("s", $pincode);
        $stmt->execute();
        $result = $stmt->get_result();
        $pincodes = [];
        while ($pincode = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
            array_push($pincodes, $pincode);
        }
        $ResponseObject['pincodes'] = $pincodes;
        $response->setResult($ResponseObject);
        $response->setApiSucessMessage('Successfully Fetched Data');
        return $response;
	}
}
