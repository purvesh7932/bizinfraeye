<?php

class Portal_GetCustomerDropDownFields_API extends Portal_Default_API {

	public function requireLogin() {
		return false;
	}

	public function preProcess(Portal_Request $request) {
	}

	public function postProcess(Portal_Request $request) {
	}

	public function process(Portal_Request $request) {
		$params = array(
			'_operation' => 'GetCustomerDropDownFields'
		);
		$result = Vtiger_Connector::getInstance()->fireRequest($params);
		$response = new Portal_Response();
		if (isset($result['code'])) {
			$response->setError($result['code'], $result['message']);
		} else {
			$response->setApiSucessMessage('Successfully Fetched Data');
			$response->setResult($result);
		}
		return $response;
	}
}
