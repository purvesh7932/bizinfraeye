<?php
class CustomerPortal_GetCustomerDropDownFields extends CustomerPortal_API_Abstract {

	function process(CustomerPortal_API_Request $request) {
		$current_user = CRMEntity::getInstance('Users');
		$current_user->id = $current_user->getActiveAdminId();
		$current_user->retrieve_entity_info($current_user->id, 'Users');
		$response = new CustomerPortal_API_Response();
		if ($current_user) {
			$module = 'Contacts';
			if (!CustomerPortal_Utils::isModuleActive($module)) {
				throw new Exception('Module not accessible', 1412);
				exit;
			}
			$describeInfo = vtws_describe($module, $current_user);
			$activeFields = CustomerPortal_Utils::getActiveFields($module, true);
			$activeFieldKeys = array_keys($activeFields);
			$fieldListOnlyPicklist = [];
			foreach ($describeInfo['fields'] as $key => $value) {
				if (!in_array($value['name'], $activeFieldKeys)) {
					unset($describeInfo['fields'][$key]);
				} else {
					$value['default'] = decode_html($value['default']);
					if ($value['type']['name'] === 'picklist' || $value['type']['name'] === 'metricpicklist') {
						$pickList = $value['type']['picklistValues'];
						foreach ($pickList as $pickListKey => $pickListValue) {
							$pickList[$pickListKey] = array($value['name'] => decode_html($pickListValue['value']));
						}
						$fieldListOnlyPicklist[$value['name']] = $pickList;
					}
				}
				$response->setResult($fieldListOnlyPicklist);
			}
			return $response;
		}
	}

	function authenticatePortalUser($username, $password) {
		return true;
	}
}
