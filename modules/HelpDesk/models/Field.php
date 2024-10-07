<?php

class HelpDesk_Field_Model extends Vtiger_Field_Model {

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
				$funcName = array('name' => 'lessThanThreeDays');
				array_push($validator, $funcName);
				$funcName = array('name' => 'lessThanOrEqualToToday');
				array_push($validator, $funcName);
				break;
			case 'hmr':
				$funcName = array('name' => 'oldHmrValue', 'params' => array('old_hmr'));
				array_push($validator, $funcName);
				break;
			case 'kilometer_reading':
				$funcName = array('name' => 'oldKmValue', 'params' => array('old_km'));
				array_push($validator, $funcName);
				break;
			case 'phone':
				$funcName = array('name' => 'itivalidate');
				array_push($validator, $funcName);
				break;
			case 'tele_phone':
				$funcName = array('name' => 'tele');
				array_push($validator, $funcName);
				break;
			case 'opp_name':
				$funcName = array('name' => 'stringonlychars');
				array_push($validator, $funcName);
				break;
			case 'nearest_railway':
				$funcName = array('name' => 'stringonlychars');
				array_push($validator, $funcName);
				break;
			default:
				$validator = parent::getValidator();
				break;
		}
		return $validator;
	}
	public function isAjaxEditable() {
		return false;
	}
}
