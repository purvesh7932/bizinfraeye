<?php

include_once 'include/Webservices/DescribeObject.php';

class Mobile_WS_DescribeWithGrouping extends Mobile_WS_Controller {

	function process(Mobile_API_Request $request) {
		$response = new Mobile_API_Response();
		$current_user = $this->getActiveUser();
		$module = $request->get('module');

		$moduleFieldGroups = Mobile_WS_Utils::gatherModuleFieldGroupInfo($module);
		$describeInfo = vtws_describe($module, $current_user);

		$serviceRepotType = $request->get('serviceRepotType');
		$purpose = $request->get('purpose');
		$blocks = array();
		$dependecyFieldList = $this->getFieldsOfCategory($serviceRepotType, $purpose);
		foreach ($moduleFieldGroups as $blocklabel => $fieldgroups) {
			$fields = array();
			foreach ($fieldgroups as $fieldname => $fieldinfo) {
				foreach ($describeInfo['fields'] as $key => $value) {
					if ($value['name'] ==  $fieldname && in_array($fieldname, $dependecyFieldList)) {
						$field = array(
							'name'  => $fieldname,
							'label' => $fieldinfo['label'],
							'uitype' => $fieldinfo['uitype'],
							'editable' => $value['editable']
						);

						if ($field['uitype'] == '53') {
							$field['type']['defaultValue'] = array('value' => "19x{$current_user->id}", 'label' => $current_user->column_fields['last_name']);
						} else if ($field['uitype'] == '117') {
							$field['type']['defaultValue'] = $field['value'];
						} else if ($field['name'] == 'terms_conditions' && in_array($module, array('Quotes', 'Invoice', 'SalesOrder', 'PurchaseOrder'))) {
							$field['type']['defaultValue'] = $field['value'];
						} else if ($field['name'] == 'visibility' && in_array($module, array('Calendar', 'Events'))) {
							$field['type']['defaultValue'] = $field['value'];
						} else if ($field['type']['name'] != 'reference') {
							$field['type']['defaultValue'] = $field['default'];
						}
						if ($field['uitype'] == '10' || $field['uitype'] == '52' || $field['uitype'] == '53' || $field['uitype'] == '117') {
							$field['id'] = $field['value']['value'];
							$field['value'] = $field['value']['label'];
						}
						$fields[] = $field;
						break;
					}
				}
			}
			if(count($fields) > 0){
				$blocks[] = array('label' => $blocklabel, 'fields' => $fields);
			}
		}

		$ResponseObject['moduleBlocks'] = $blocks;
		$response->setResult($ResponseObject);
		$response->setApiSucessMessage('Successfully Fetched Data');
		return $response;
	}

	public function getFieldsOfCategory($type,$purposeValue) {
        if ($type == 'SERVICE FOR SPARES PURCHASED' ) {
			$fieldDependeny = Vtiger_DependencyPicklist::getFieldsFitDependency('ServiceReports', 'tck_det_purpose', 'type_of_conrt');
			$type = $purposeValue;
		} else {
			$fieldDependeny = Vtiger_DependencyPicklist::getFieldsFitDependency('ServiceReports', 'sr_ticket_type', 'sr_war_status');
		}
        foreach ($fieldDependeny['valuemapping'] as $valueMapping) {
            if ($valueMapping['sourcevalue'] == $type) {
                return $valueMapping['targetvalues'];
            }
        }
    }
}
