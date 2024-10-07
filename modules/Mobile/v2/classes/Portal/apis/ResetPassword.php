<?php

class Portal_ResetPassword_API extends Portal_Default_API {

	public function requireLogin() {
		return false;
	}

	public function preProcess(Portal_Request $request) {
	}

	public function postProcess(Portal_Request $request) {
	}

	public function process(Portal_Request $request) {
		$wholeRequest = array_merge($request->getAll(), $_REQUEST);
		$wholeRequest['_operation'] = $wholeRequest['api'];
		$ResponseFromPortal = Vtiger_Connector::getInstance()->fireRequest($wholeRequest);
		
		$result = [];
		$response = new Portal_Response();
		if (isset($ResponseFromPortal['code'])) {
			$response->setError($ResponseFromPortal['code'], $ResponseFromPortal['message']);
		} else {
			$response->setApiSucessMessage("Changed password successfully");
			$result['message'] = 'Changed password successfully';
			$response->setResult($result);
		}
		return $response;
	}
}
