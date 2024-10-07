<?php
include_once 'include/Webservices/DescribeObject.php';
class HelpDesk_DescribeModuleForSR_Action extends Vtiger_Action_Controller {

	public function requiresPermission(\Vtiger_Request $request) {
		$permissions = parent::requiresPermission($request);
		$permissions[] = array('module_parameter' => 'module', 'action' => 'DetailView', 'record_parameter' => 'record');
		return $permissions;
	}

	public function process(Vtiger_Request $request) {
		global $current_user;
		$response = new Vtiger_Response();
		$describeInfo = [];
		if ($current_user) {
			$module = $request->get('module');
			$describeInfo = vtws_describe($module, $current_user);
			$dependencyFields = array('purpose', 'manual_equ_ser','pincode', 'city','pre_address','state','district','nearest_railway');
			foreach ($describeInfo['fields'] as $key => $value) {
				if (in_array($value['name'], $dependencyFields)) {
					if ($value['name'] == 'purpose') {
						$value['dependentField'] = true;
						$value['initialDisplay'] = false;
						$value['dependentOnOption'] = 'GENERAL INSPECTION';
						$value['mandatoryOndepentOnOption'] = true;
						$value['dependentOnField'] = 'ticket_type';
					} elseif (in_array($value['name'],array('pincode', 'city','pre_address','state','district','nearest_railway'))){
						$value['dependentOnMasterValue'] = array('PRE-DELIVERY','PREVENTIVE MAINTENANCE', 
						'PERIODICAL MAINTENANCE','BREAKDOWN','SERVICE FOR SPARES PURCHASED',
						'GENERAL INSPECTION','REHABILITATION','UPGRADTION','OVERHAUL',
						'PARTS REQUIREMENT','EQUIPMENT HEALTH CHECK UP',
						'WARRANTY CLAIM FOR SUB ASSEMBLY / OTHER SPARE PARTS','ERECTION AND COMMISSIONING');
						$value['dependentField'] = true;
						$value['initialDisplay'] = false;
						$value['dependentOnOption'] = true;
						$value['mandatoryOndepentOnOption'] = true;
						$value['dependentOnField'] = 'chg_func_loc';
					} else if ($value['name'] == 'manual_equ_ser') {
						$value['dependentField'] = true;
						$value['initialDisplay'] = false;
						$value['dependentOnOption'] = '';
						$value['mandatoryOndepentOnOption'] = true;
						$value['dependentOnMasterValue'] = array('ERECTION AND COMMISSIONING','PRE-DELIVERY','WARRANTY CLAIM FOR SUB ASSEMBLY / OTHER SPARE PARTS','GENERAL INSPECTION','REHABILITATION','UPGRADTION','OVERHAUL','PARTS REQUIREMENT','EQUIPMENT HEALTH CHECK UP');
						$value['dependentOnField'] = 'ticket_type';
					}
				} else {
					$value['initialDisplay'] = false;
				}
				if ($value['name'] == 'ticket_type') {
					$value['initialDisplay'] = true;
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
				$describeInfo['fields'][$key] = $value;

				// $position = array_search($value['name'], $activeFieldKeys);
				$fieldList[] = $describeInfo['fields'][$key];
			}
		}
		if ($fieldList) {
			unset($describeInfo['fields']);
			$describeInfo['fields'] = $fieldList;
		}

		$describeInfo['label'] = decode_html(vtranslate($describeInfo['label'], $module));
		$dependencyArray = [];
		$dependencyArray['ticket_type'] = Vtiger_DependencyPicklist::getFieldsFitDependency('HelpDesk', 'ticket_type', 'ticketpriorities');
		$dependencyArray['purpose'] = Vtiger_DependencyPicklist::getFieldsFitDependency('HelpDesk', 'purpose', 'ticketstatus');
		$describeInfo['FieldDependency'] = $dependencyArray;
		$picklistDependency = [];
		$picklistDependency['ticket_type'] = Vtiger_DependencyPicklist::getPickListDependency('HelpDesk', 'ticket_type', 'purpose');
		$describeInfo['picklistDependency'] = $picklistDependency;
		$response->setResult($describeInfo);
		$response->emit();
	}

	public function validateRequest(Vtiger_Request $request) {
		$request->validateWriteAccess();
	}
}
