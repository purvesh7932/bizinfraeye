<?php
class ServiceEngineer_DetailRecordStructure_Model extends Vtiger_DetailRecordStructure_Model {

	public function getStructure() {
		global $log;
		$currentUsersModel = Users_Record_Model::getCurrentUserModel();
		if (!empty($this->structuredValues)) {
			return $this->structuredValues;
		}

		$values = array();
		$recordModel = $this->getRecord();
		$recordExists = !empty($recordModel);
		$moduleModel = $this->getModule();
		$blockModelList = $moduleModel->getBlocks();
		$office = $recordModel->get('office');

		$hideField = array();
		if ($office == 'Regional Office') {
			$hideField = ['district_office', 'activity_centre', 'service_centre', 'production_division'];
		} else if ($office == 'District Office') {
			$hideField = ['activity_centre', 'service_centre', 'production_division'];
		} else if ($office == 'Activity Centre') {
			$hideField = ['district_office', 'service_centre', 'production_division'];
		} else if ($office == 'Service Centre') {
			$hideField = ['district_office', 'activity_centre', 'production_division'];
		} else if ($office == 'Production Division') {
			$hideField = ['regional_office', 'district_office', 'activity_centre', 'service_centre'];
		} else {
			$hideField = ['regional_office', 'district_office', 'activity_centre', 'service_centre', 'production_division'];
		}

		// $cust_role = $recordModel->get('cust_role');
		// if ($cust_role != 'Service Manager') {
		// 	array_push($hideField, 'sub_service_manager_role');
		// }

		$cust_role = $recordModel->get('cust_role');
		if ($cust_role != 'Service Manager') {
			array_push($hideField, 'usr_verification');
		}
		
		array_push($hideField, 'user_password', 'confirm_password');
		foreach ($blockModelList as $blockLabel => $blockModel) {
			$fieldModelList = $blockModel->getFields();
			if (!empty($fieldModelList)) {
				$values[$blockLabel] = array();
				foreach ($fieldModelList as $fieldName => $fieldModel) {
					if (in_array($fieldName, $hideField)) {
						continue;
					}
					if ($fieldModel->isViewableInDetailView()) {
						if ($recordExists) {
							$value = $recordModel->get($fieldName);
							if (!$currentUsersModel->isAdminUser() && ($fieldModel->getFieldDataType() == 'picklist' || $fieldModel->getFieldDataType() == 'multipicklist')) {
								$value = decode_html($value);
								$this->setupAccessiblePicklistValueList($fieldModel);
							}
							$fieldModel->set('fieldvalue', $value);
						}
						$values[$blockLabel][$fieldName] = $fieldModel;
					}
				}
			}
		}
		$this->structuredValues = $values;
		return $values;
	}
}
