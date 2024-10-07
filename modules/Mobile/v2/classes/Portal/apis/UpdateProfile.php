<?php

class Portal_UpdateProfile_API extends Portal_Default_API {

	protected $allowedFields = array(
		'contactFields' => array(
			'firstname', 'lastname', 'email', 'secondaryemail', 'mobile',
			'phone', 'imagename', 'imagedata', 'imagetype', 'contact_no',
			'con_role'
		),
		'accountFields' => array('firstname', 'lastname', 'website', 'email1', 'phone', 'imagename', 'imagedata', 'imagetype', 'accountname'),
	);

	public function process(Portal_Request $request) {
		$response = new Portal_Response();

		global $adb;
		$updateSql = "update vtiger_contactdetails set firstname = ?,
		email = ? where contactid = ?";
		$contactId = Portal_Session::get('contact_id');
		$adb->pquery($updateSql, array(
			$request->get('service_engineer_name'),
			$request->get('email'),
			$contactId
		));

		$result = Vtiger_Connector::getInstance()->GetProfileInfo();
		$response = new Portal_Response();
		$response->setApiSucessMessage('User Profile Is Updated Successfully');
		$response->setResult($this->filterResponse($result, $this->allowedFields));
		return $response;
	}

	protected function filterResponse($result, $allowedFields) {
		$data = array();
		if (isset($result['customer_details'])) {
			foreach ($result['customer_details'] as $conKey => $conValues) {
				if (!in_array($conKey, $allowedFields['contactFields'])) {
					unset($result['customer_details'][$conKey]);
				}
			}
		}

		if (!empty($result['customer_details']['imagename'])) {
			global $site_URL_NonHttp;
			$result['customer_details']['imagename'] = $site_URL_NonHttp . $result['customer_details']['imagename'];
		}
		$result['customer_details']['badge_no'] = $result['customer_details']['contact_no'];
		$result['customer_details']['designaion'] = $result['customer_details']['con_role'];
		$result['customer_details']['phone'] = $result['customer_details']['mobile'];
		$result['customer_details']['service_engineer_name'] = $result['customer_details']['firstname'];
		unset($result['customer_details']['contact_no']);
		unset($result['customer_details']['con_role']);

		if (isset($result['company_details'])) {
			foreach ($result['company_details'] as $accKey => $accValues) {
				if (!in_array($accKey, $allowedFields['accountFields'])) {
					unset($result['company_details'][$accKey]);
				}
			}
		}
		$data['userDetails'] = $result['customer_details'];
		// $data['company_details'] = $result['company_details'];

		if (isset($data['company_details'])) {
			foreach ($data['company_details'] as $label => $value) {
				if ($label == 'website' && !empty($value)) {
					$matchPattern = "^[\w]+:\/\/^";
					preg_match($matchPattern, $value, $matches);
					if (!empty($matches[0])) {
						$value = $value;
					} else {
						$value = 'http://' . $value;
					}
					$data['company_details']['weburl'] = $value;
				}
			}
		}
		return $data;
	}
}
