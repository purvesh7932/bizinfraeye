<?php
class Mobile_WS_GetPincodeInfo extends Mobile_WS_Controller {

    function process(Mobile_API_Request $request) {
        $response = new Mobile_API_Response();
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
