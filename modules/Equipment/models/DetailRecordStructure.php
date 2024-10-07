<?php

class Equipment_DetailRecordStructure_Model extends Vtiger_DetailRecordStructure_Model {

	public function getStructure() {
		$currentUsersModel = Users_Record_Model::getCurrentUserModel();
		if (!empty($this->structuredValues)) {
			return $this->structuredValues;
		}

		$values = array();
		$recordModel = $this->getRecord();
		$recordExists = !empty($recordModel);
		$moduleModel = $this->getModule();
		$blockModelList = $moduleModel->getBlocks();
		$getCommitedAvail = $recordModel->get('eq_commited_avl');
		$dependentBlockInformation = array(
			'Availability For Warranty Period' => 'Availability For Warranty Period',
			'afbwcpas' => 'Availability for both Warranty & Contract Period are Same',
			'saatocp' => 'Same availability applicable through out contract period',
			'daadcp' => 'Different availability applicable during contract period',
		);
		$dependentBlocks = array_keys($dependentBlockInformation);
		$dependentFields = array(
			"eq_contra_app" => array(
				"fields" => array(
					'cont_end_date', 'cont_start_date',
					'run_year_cont', 'total_year_cont',
					'eq_type_of_conrt'
				),
				"dependentVal" => "Yes"
			),
			"eq_available" => array(
				"fields" => array(
					'eq_available_for', 'maint_h_app_for_ac',
					'eq_mon_available', 'eq_war_app_wp',
					'eq_war_app_cp',  'eq_commited_avl',
					'shift_hours', 'start_day_of_avail_calc'
				),
				"dependentVal" => "Aplicable"
			)
		);
		$allDependentList = [];
		foreach ($dependentFields as $key => $vals) {
			$allDependentList = array_merge($allDependentList, $vals['fields']);
		}
		
		$parentFieldVal = '';
		$parentFieldDependVal = '';
		foreach ($blockModelList as $blockLabel => $blockModel) {
			if (in_array($blockLabel, $dependentBlocks)) {
				if ($getCommitedAvail != $dependentBlockInformation[$blockLabel]) {
					continue;
				}
			}
			$fieldModelList = $blockModel->getFields();
			if (!empty($fieldModelList)) {
				$values[$blockLabel] = array();
				foreach ($fieldModelList as $fieldName => $fieldModel) {
					if ($fieldModel->isViewableInDetailView()) {
						if (in_array($fieldName, $allDependentList)) {
							foreach ($dependentFields as $key => $vals) {
								$allDependentList = array_merge($allDependentList, $vals['fields']);
								if(in_array($fieldName,  $vals['fields'])){
									$parentFieldVal = $recordModel->get($key);
									$parentFieldDependVal = $vals['dependentVal'];
									break;
								}
							}
							if ($parentFieldVal != $parentFieldDependVal) {
								continue;
							}
						}
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
		// die();
		$this->structuredValues = $values;
		return $values;
	}
}
