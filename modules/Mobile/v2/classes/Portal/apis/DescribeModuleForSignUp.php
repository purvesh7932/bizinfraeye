<?php

class Portal_DescribeModuleForSignUp_API extends Portal_Default_API {

	public function requireLogin() {
		return false;
	}

	public function preProcess(Portal_Request $request) {
	}

	public function postProcess(Portal_Request $request) {
	}

	public function process(Portal_Request $request) {
		$responseObject = Vtiger_Connector::getInstance()->describeModuleForSignUp('Contacts', '');
		$result = array();
		$result['fields'] = $responseObject['describe']['blocks'];
		$response = new Portal_Response();
		$response->setResult($result);
		return $response;
	}
}
