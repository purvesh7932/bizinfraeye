<?php
class CustomerPortal_GetSRTypeCounts extends CustomerPortal_API_Abstract {

	function process(CustomerPortal_API_Request $request) {
		$moduleModel = Vtiger_Module_Model::getInstance('HelpDesk');
		$contactId = $this->getActiveCustomer()->id;
		$data = $moduleModel->getTicketsByType('' , '',$contactId);
		$response = new CustomerPortal_API_Response();
		$response->addToResult('data', $data);
		return $response;
	}
}
