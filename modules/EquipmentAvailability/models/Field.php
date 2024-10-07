<?php

class EquipmentAvailability_Field_Model extends Vtiger_Field_Model {

	public function isViewableInDetailView() {
		if(!$this->isViewable() || $this->getDisplayType() == '5' || $this->getDisplayType() == '6') {
			return false;
		}
		return true;
	}

	public function isAjaxEditable() {
		return false;
	}
	
}
