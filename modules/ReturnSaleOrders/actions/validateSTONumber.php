<?php
class ReturnSaleOrders_validateSTONumber_Action extends Vtiger_IndexAjax_View {
    public function process(Vtiger_Request $request) {
        $response = new Vtiger_Response();
        $sto_no = trim($request->get('sto_no'));
        if (empty($sto_no)) {
            $response->setError(100, 'sto_no is Empty');
            return $response;
        }
        include_once('include/utils/GeneralConfigUtils.php');
        $reponseData = CheckExitenseOFSTO($sto_no);
        $responseObject = [];
        if ($reponseData['success'] == false) {
            $responseObject['validSTONumber'] = false;
        } else {
            $responseObject['validSTONumber'] = true;
            $responseObject['goods_consg_no'] = "goods_consg_no_from_sap";
            $responseObject['goods_consg_dte'] = "goods_consg_dte_from_sap";
            // $responseObject['data'] = $reponseData;
        }
        $response->setResult($responseObject);
        $response->emit();
        exit();
    }
}
