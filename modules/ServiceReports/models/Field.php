<?php

class ServiceReports_Field_Model extends Vtiger_Field_Model {

	public function isRoleBased() {
		if ($this->get('uitype') == '15' || ($this->get('uitype') == '55' && $this->getFieldName() == 'salutationtype')) {
			return true;
		}
		return false;
	}

	function getValidator() {
		$validator = array();
		$fieldName = $this->getName();

		switch ($fieldName) {
			case 'date_of_failure':
				$funcName = array('name' => 'lessThanDependentField', 'params' => array('restoration_date'));
				break;
			case 'restoration_date':
				$funcName = array('name' => 'greaterThanDependentField', 'params' => array('date_of_failure'));
				break;
			case 'date_of_failure':
				$funcName = array('name' => 'lessThanThreeDays');
				array_push($validator, $funcName);
				$funcName = array('name' => 'lessThanOrEqualToToday');
				array_push($validator, $funcName);
				break;

			default:
				$validator = parent::getValidator();
				break;
		}
		if ($funcName) {
			array_push($validator, $funcName);
		}
		return $validator;
	}

	public function isAjaxEditable() {
		return false;
	}
}
