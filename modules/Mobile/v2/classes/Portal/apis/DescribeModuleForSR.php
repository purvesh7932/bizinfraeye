<?php
class Portal_DescribeModuleForSR_API extends Portal_Default_API {
	public function process(Portal_Request $request) {
		$module = $request->getModule();
		$language = Portal_Session::get('language');
		$result = Vtiger_Connector::getInstance()->DescribeModuleForSR($module, $language);
		$response = new Portal_Response();
		$response->setResult($result);
		return $response;
	}
}