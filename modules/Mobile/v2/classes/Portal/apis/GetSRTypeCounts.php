<?php
class Portal_GetSRTypeCounts_API extends Portal_Default_API {
	public function process(Portal_Request $request) {
		$params = array(
			'_operation' => 'GetSRTypeCountsMobile'
		);
		$data = Vtiger_Connector::getInstance()->fireRequest($params);
		$response = new Portal_Response();
		$result = [];
		$result['SRCounts'] = $data['data'];
		$response->setResult($result);
		$response->setApiSucessMessage('Successfully Fetched Data');
		return $response;
	}
}