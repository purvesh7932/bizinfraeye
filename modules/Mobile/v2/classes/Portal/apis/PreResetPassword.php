<?php

class Portal_PreResetPassword_API extends Portal_Default_API {

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
		$result = array();
		$response = new Portal_Response();
		if (isset($ResponseFromPortal['code'])) {
			$response->setError($ResponseFromPortal['code'], $ResponseFromPortal['message']);
		} else {
			$result['uid'] = $ResponseFromPortal['uid'];
			$response->setApiSucessMessage("OTP has sent to registered email");
			$response->setResult($result);
		}
		return $response;
	}
}
