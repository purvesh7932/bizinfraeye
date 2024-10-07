<?php
class CustomerPortal_DescribeModuleForSR extends CustomerPortal_API_Abstract {

	function process(CustomerPortal_API_Request $request) {
		$current_user = $this->getActiveUser();
		$response = new CustomerPortal_API_Response();

		if ($current_user) {
			$module = $request->get('module');

			if (!CustomerPortal_Utils::isModuleActive($module)) {
				throw new Exception('Module not accessible', 1412);
				exit;
			}

			$describeInfo = vtws_describe($module, $current_user);
			$moduleFieldGroups = $this->gatherModuleFieldGroupInfo($module);
			$activeFields = CustomerPortal_Utils::getActiveFields($module, true);
			$activeFieldKeys = array_keys($activeFields);
			$dependencyFields = array('purpose', 'manual_equ_ser', 'manual_loc','pincode', 'city','pre_address','state','district','nearest_railway');
			$blocks = array();
			foreach ($moduleFieldGroups as $blocklabel => $fieldgroups) {
				$fields = array();
				foreach ($fieldgroups as $fieldname => $fieldinfo) {
					foreach ($describeInfo['fields'] as $key => $value) {
						if ($value['name'] ==  $fieldname) {
							if (!in_array($value['name'], $activeFieldKeys)) {
								unset($describeInfo['fields'][$key]);
							} else {
								if (in_array($value['name'], $dependencyFields)) {
									if ($value['name'] == 'purpose') {
										$value['dependentField'] = true;
										$value['initialDisplay'] = false;
										$value['dependentOnOption'] = 'GENERAL INSPECTION';
										$value['mandatoryOndepentOnOption'] = true;
										$value['dependentOnField'] = 'ticket_type';
									} elseif ($value['name'] == 'manual_equ_ser') {
										$value['dependentField'] = true;
										$value['initialDisplay'] = false;
										$value['dependentOnOption'] = 'Other';
										$value['mandatoryOndepentOnOption'] = true;
										$value['dependentOnField'] = 'equipment_id';
									} elseif ($value['name'] == 'manual_loc') {
										$value['dependentField'] = true;
										$value['initialDisplay'] = false;
										$value['dependentOnOption'] = true;
										$value['mandatoryOndepentOnOption'] = true;
										$value['dependentOnField'] = 'chg_func_loc';
									} elseif (in_array($value['name'], array('pincode', 'city', 'pre_address', 'state', 'district', 'nearest_railway'))) {
										// print_r("-------");
										// print_r($value);
										$value['dependentOnMasterValue'] = array(
											'PRE-DELIVERY', 'PREVENTIVE MAINTENANCE',
											'PERIODICAL MAINTENANCE', 'BREAKDOWN', 'SERVICE FOR SPARES PURCHASED',
											'GENERAL INSPECTION', 'REHABILITATION', 'UPGRADTION', 'OVERHAUL',
											'PARTS REQUIREMENT', 'EQUIPMENT HEALTH CHECK UP',
											'WARRANTY CLAIM FOR SUB ASSEMBLY / OTHER SPARE PARTS', 'ERECTION AND COMMISSIONING'
										);
										$value['dependentField'] = true;
										$value['initialDisplay'] = false;
										$value['dependentOnOption'] = true;
										$value['mandatoryOndepentOnOption'] = true;
										$value['dependentOnField'] = 'chg_func_loc';
									} else {
										print_r("++++++++++++");
										print_r($value);
									}
								} else {
									$value['initialDisplay'] = false;
								}
								if ($value['name'] == 'ticket_type') {
									$value['initialDisplay'] = true;
									$pickList = $value['type']['picklistValues'];
									$pickListWithOnlyPermitted = [];
									foreach ($pickList as $pickListKey => $pickListValue) {
										if ($pickListValue['value'] == 'DESIGN MODIFICATION' || $pickListValue['value'] == 'PRE-DELIVERY') {
											continue;
										}
										$pickListValue['label'] = $pickListValue['label'];
										$pickListValue['value'] = $pickListValue['value'];
										array_push($pickListWithOnlyPermitted, $pickListValue);
									}
									$value['type']['picklistValues'] = $pickListWithOnlyPermitted;
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
								$describeInfo['fields'][$key] = $value;

								$position = array_search($value['name'], $activeFieldKeys);
								$fieldList[$position] = $describeInfo['fields'][$key];
							}
							$fields[] = $value;
							break;
						}
					}
				}
				if (count($fields) > 0) {
					$blocks[] = array('label' => $blocklabel, 'fields' => $fields);
				}
			}
			if ($blocks) {
				unset($describeInfo['fields']);
				$describeInfo['blocks'] = $blocks;
			}

			$describeInfo['label'] = decode_html(vtranslate($describeInfo['label'], $module));
			$dependencyArray = [];
			$dependencyArray['ticket_type'] = Vtiger_DependencyPicklist::getFieldsFitDependency('HelpDesk', 'ticket_type', 'ticketpriorities');
			$dependencyArray['purpose'] = Vtiger_DependencyPicklist::getFieldsFitDependency('HelpDesk', 'purpose', 'ticketstatus');
			$describeInfo['FieldDependency'] = $dependencyArray;
			$picklistDependency = [];
			$picklistDependency['ticket_type'] = Vtiger_DependencyPicklist::getPickListDependency('HelpDesk', 'ticket_type', 'purpose');
			$describeInfo['picklistDependency'] = $picklistDependency;
			$response->addToResult('describe', $describeInfo);
		}
		return $response;
	}

	static $gatherModuleFieldGroupInfoCache = array();

	static function gatherModuleFieldGroupInfo($module) {
		global $adb;

		if ($module == 'Events') $module = 'Calendar';

		if (isset(self::$gatherModuleFieldGroupInfoCache[$module])) {
			return self::$gatherModuleFieldGroupInfoCache[$module];
		}

		$result = $adb->pquery(
			"SELECT fieldname, fieldlabel, blocklabel, uitype FROM vtiger_field INNER JOIN
			vtiger_blocks ON vtiger_blocks.tabid=vtiger_field.tabid AND vtiger_blocks.blockid=vtiger_field.block 
			WHERE vtiger_field.tabid=? AND vtiger_field.presence != 1 and blocklabel !='LBL_TICKET_INFORMATION' ORDER BY vtiger_blocks.sequence, vtiger_field.sequence",
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

		self::$gatherModuleFieldGroupInfoCache[$module] = $fieldgroups;

		return $fieldgroups;
	}

	static function fixUIType($module, $fieldname, $uitype) {
		if ($module == 'Contacts' || $module == 'Leads') {
			if ($fieldname == 'salutationtype') {
				return 16;
			}
		} else if ($module == 'Calendar' || $module == 'Events') {
			if ($fieldname == 'time_start' || $fieldname == 'time_end') {
				return 252;
			}
		}
		return $uitype;
	}
}
