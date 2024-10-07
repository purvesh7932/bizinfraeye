<?php

class ServiceEngineer_Field_Model extends Vtiger_Field_Model {

	function getValidator() {
		$validator = array();
		$fieldName = $this->getName();

		switch ($fieldName) {
			case 'phone':
				$funcName = array('name' => 'itivalidate');
				array_push($validator, $funcName);
				break;
			default:
				$validator = parent::getValidator();
				break;
		}
		return $validator;
	}
	public function isAjaxEditable() {
		if ($this->getName() == 'rejection_reason') {
			return false;
		} 
		$ajaxRestrictedFields = array('4', '72', '61', '27', '28');
		if (!$this->isEditable() || in_array($this->get('uitype'), $ajaxRestrictedFields)) {
			return false;
		}
		return true;
	}
}
