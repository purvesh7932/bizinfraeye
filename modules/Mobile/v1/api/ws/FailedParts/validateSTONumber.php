<?php
class Mobile_WS_validateSTONumber extends Mobile_WS_Controller {

    function process(Mobile_API_Request $request) {
        $response = new Mobile_API_Response();
        $sto_no = trim($request->get('sto_no'));
        if (empty($sto_no)) {
            $response->setError(100, 'sto_no is Empty');
            return $response;
        }
        include_once('include/utils/GeneralConfigUtils.php');
        $reponseData = CheckExitenseOFSTO($sto_no);
        if ($reponseData['success'] == false) {
            $response->setError(100, 'STO Number Is Not Valid');
            return $response;
        } else {
            $responseObject['validSTONumber'] = true;
            $responseObject['goods_consg_no'] = "goods_consg_no_from_sap";
            $responseObject['goods_consg_dte'] = "goods_consg_dte_from_sap";
            $responseObject['data'] = $reponseData;
        }
        $response->setResult($responseObject);
        $response->setApiSucessMessage('Successfully Fetched Data');
        return $response;
    }
}
