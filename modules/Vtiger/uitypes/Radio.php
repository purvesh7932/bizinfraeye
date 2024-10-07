<?php
class Vtiger_Radio_UIType extends Vtiger_Base_UIType {

	/**
	 * Function to get the Template name for the current UI Type object
	 * @return <String> - Template Name
	 */
	public function getTemplateName() {
		return 'uitypes/Radio.tpl';
	}

	/**
	 * Function to get the Display Value, for the current field type with given DB Insert Value
	 * @param <Object> $value
	 * @return <Object>
	 */
	public function getDisplayValue($value, $record = false, $recordInstance = false) {
		return Vtiger_Language_Handler::getTranslatedString($value, $this->get('field')->getModuleName());
	}

	public function getListSearchTemplateName() {
		return 'uitypes/PickListFieldSearchView.tpl';
	}
}
