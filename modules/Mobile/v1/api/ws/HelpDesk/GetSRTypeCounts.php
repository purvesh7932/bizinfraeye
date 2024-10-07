<?php
class Mobile_WS_GetSRTypeCounts extends Mobile_WS_Controller {
    function process(Mobile_API_Request $request) {
        $response = new Mobile_API_Response();
        $moduleModel = Vtiger_Module_Model::getInstance('HelpDesk');
		$counts['SRCounts'] = $moduleModel->getTicketsByTypeUser('' , '');
        $response->setApiSucessMessage('Successfully Fetched Data');
		$response->setResult($counts);
		return $response;
    }
}
