<?php

class CustomerPortal_GetPincodeInfo extends CustomerPortal_API_Abstract {

	function process(CustomerPortal_API_Request $request) {
		$pincode = $request->get('pincode');
		$response = new CustomerPortal_API_Response();
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
		$response->addToResult('describe', $pincodes);
		return $response;
	}

	function authenticatePortalUser($username, $password) {
		return true;
	}
}
