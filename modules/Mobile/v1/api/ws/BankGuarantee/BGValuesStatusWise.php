<?php
class Mobile_WS_BGValuesStatusWise extends Mobile_WS_Controller {
    function process(Mobile_API_Request $request) {
        $response = new Mobile_API_Response();
        $moduleModel = Vtiger_Module_Model::getInstance('BankGuarantee');
		$counts['BgStatusValues'] = $moduleModel->BGValuesStatusWise('' , '');
        $response->setApiSucessMessage('Successfully Fetched Data');
		$response->setResult($counts);
		return $response;
    }
}
