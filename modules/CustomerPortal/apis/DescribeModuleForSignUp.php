<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
include_once 'include/Webservices/Retrieve.php';
include_once 'include/Webservices/DescribeObject.php';
class CustomerPortal_DescribeModuleForSignUp extends CustomerPortal_API_Abstract {

	function process(CustomerPortal_API_Request $request) {
		$current_user = CRMEntity::getInstance('Users');
		$current_user->id = $current_user->getActiveAdminId();
		$current_user->retrieve_entity_info($current_user->id, 'Users');
		$response = new CustomerPortal_API_Response();

		if ($current_user) {
			$module = $request->get('module');

			if (!CustomerPortal_Utils::isModuleActive($module)) {
				throw new Exception('Module not accessible', 1412);
				exit;
			}

			$describeInfo = vtws_describe($module, $current_user);
			// Get active fields with read, write permissions
			$module = 'Contacts';
			$moduleFieldGroups = $this->gatherModuleFieldGroupInfo($module);
			$activeFields = CustomerPortal_Utils::getActiveFields($module, true);
			$activeFieldKeys = array_keys($activeFields);
			$dependencyFields = array('other_customer_type', 'other_business_sector', 'other_office_type');
			foreach ($moduleFieldGroups as $blocklabel => $fieldgroups) {
				$fields = array();
				foreach ($fieldgroups as $fieldname => $fieldinfo) {
					if (!in_array($fieldname, $activeFieldKeys)) {
						continue;
					}
					foreach ($describeInfo['fields'] as $key => $value) {
						if ($value['name'] ==  $fieldname && ($value['name'] != 'assigned_user_id')) {
							if (in_array($value['name'], $dependencyFields)) {
								$value['dependentField'] = true;
								$value['initialDisplay'] = 'no';
								$value['dependentOnOption'] = 'Other';
								$value['mandatoryOndepentOnOption'] = true;
								$dependentField = str_replace("other_", "", $value['name']);
								$value['dependentOnField'] = $dependentField;
							} else {
								$value['initialDisplay'] = 'yes';
							}
							if($value['name'] == 'firstname'){
								$value['type'] = 'stringonlychars';
							}
							$value['default'] = decode_html($value['default']);
							if ($value['type']['name'] === 'picklist' || $value['type']['name'] === 'metricpicklist') {
								$pickList = $value['type']['picklistValues'];
	
								foreach ($pickList as $pickListKey => $pickListValue) {
									$pickListValue['label'] = decode_html(vtranslate($pickListValue['value'], $module));
									$pickListValue['value'] = decode_html($pickListValue['value']);
									$pickList[$pickListKey] = $pickListValue;
								}
								$value['type']['picklistValues'] = $pickList;
							} else if ($value['type']['name'] === 'time') {
								$value['default'] = Vtiger_Time_UIType::getTimeValueWithSeconds($value['default']);
							}
							$value['label'] = decode_html($value['label']);
							if ($activeFields[$value['name']]) {
								$value['editable'] = true;
							} else {
								$value['editable'] = false;
							}
							$fields[] = $value;
							break;
						}
					}
				}
				if(count($fields) > 0){
					$blocks[] = array('label' => $blocklabel, 'fields' => $fields);
				}
			}

			$modifiedResult = array('blocks' => $blocks);
			$response->addToResult('describe', $modifiedResult);
		}
		return $response;
	}

	static $gatherModuleFieldGroupInfoCache = array();

	function gatherModuleFieldGroupInfo($module) {
		global $adb;

		if ($module == 'Events') $module = 'Calendar';

		// Cache hit?
		if (isset(self::$gatherModuleFieldGroupInfoCache[$module])) {
			return self::$gatherModuleFieldGroupInfoCache[$module];
		}

		$result = $adb->pquery(
			"SELECT fieldname, fieldlabel, blocklabel, uitype FROM vtiger_field INNER JOIN
			vtiger_blocks ON vtiger_blocks.tabid=vtiger_field.tabid AND vtiger_blocks.blockid=vtiger_field.block 
			WHERE vtiger_field.tabid=? AND vtiger_field.presence != 1 ORDER BY vtiger_blocks.sequence, vtiger_field.sequence",
			array(getTabid($module))
		);

		$fieldgroups = array();
		while ($resultrow = $adb->fetch_array($result)) {
			$blocklabel = getTranslatedString($resultrow['blocklabel'], $module);
			if (!isset($fieldgroups[$blocklabel])) {
				$fieldgroups[$blocklabel] = array();
			}
			$fieldgroups[$blocklabel][$resultrow['fieldname']] =
				array(
					'label' => getTranslatedString($resultrow['fieldlabel'], $module),
					'uitype' => self::fixUIType($module, $resultrow['fieldname'], $resultrow['uitype'])
				);
		}

		// Cache information
		self::$gatherModuleFieldGroupInfoCache[$module] = $fieldgroups;

		return $fieldgroups;
	}

	function authenticatePortalUser($username, $password) {
		return true;
	}
	function fixUIType($module, $fieldname, $uitype) {
		if ($module == 'Contacts' || $module == 'Leads') {
			if ($fieldname == 'salutationtype') {
				return 16;
			}
		} else if ($module == 'Calendar' || $module == 'Events') {
			if ($fieldname == 'time_start' || $fieldname == 'time_end') {
				// Special type for mandatory time type (not defined in product)
				return 252;
			}
		}
		return $uitype;
	}
}
