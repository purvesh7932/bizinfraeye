<?php
class Mobile_WS_GetALLPincodeInfo extends Mobile_WS_Controller {

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
            " ";
        $stmt = $connection->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $pincodes = mysqli_fetch_all($result, MYSQLI_ASSOC);
        $ResponseObject['pincodes'] = $pincodes;
        $response->setResult($ResponseObject);
        $response->setApiSucessMessage('Successfully Fetched Data');
        return $response;
    }
}
