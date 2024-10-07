<?php

class CallAssistance_Field_Model extends Vtiger_Field_Model {

	public function isRoleBased() {
		if ($this->get('uitype') == '15' || ($this->get('uitype') == '55' && $this->getFieldName() == 'salutationtype')) {
			return true;
		}
		return false;
	}

}
