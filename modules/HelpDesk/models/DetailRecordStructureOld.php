<?php
class HelpDesk_DetailRecordStructure_Model extends Vtiger_DetailRecordStructure_Model {

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
		$tiketType = $recordModel->get('ticket_type');
		$purposeValue = $recordModel->get('purpose');
		$dependecyFieldList = $this->getFieldsOfCategory($tiketType, $purposeValue);
		$singleFieldDependency = ['pincode', 'city', 'pre_address', 'state', 'district', 'nearest_railway','manual_equ_ser'];
		$singleFieldDeendentValue = $recordModel->get('chg_func_loc');

		$secondDependency = ['manual_equ_ser'];
		$secondFieldDeendentValue = $recordModel->get('equip_id_da');
		$thirdFieldDeendentValue = $recordModel->get('equipment_id');
		$manualEqValue = $recordModel->get('manual_equ_ser');
		foreach ($blockModelList as $blockLabel => $blockModel) {
			$fieldModelList = $blockModel->getFields();
			if (!empty($fieldModelList)) {
				$values[$blockLabel] = array();
				foreach ($fieldModelList as $fieldName => $fieldModel) {
					if (in_array($fieldName, $singleFieldDependency)) {
						if ($tiketType == 'PRE-DELIVERY' || $tiketType == 'ERECTION AND COMMISSIONING') {
							if ($singleFieldDeendentValue == '0' && empty($recordModel->get('manual_equ_ser'))) {
								continue;
							}
						} else {
							if ($singleFieldDeendentValue == '0' && empty($manualEqValue)) {
								continue;
							}
						}
					}
					if (in_array($fieldName, $secondDependency)) {
						if (!empty($secondFieldDeendentValue)) {
							continue;
						}
					}
					if ($tiketType == 'PRE-DELIVERY' || $tiketType == 'ERECTION AND COMMISSIONING') {
						if (($fieldName == 'equip_id_da' || $fieldName == 'equip_location'
							|| $fieldName == 'chg_func_loc') && empty($secondFieldDeendentValue)) {
							continue;
						}
					} else {
						if (($fieldName == 'equipment_id' || $fieldName == 'func_loc_id'
							|| $fieldName == 'chg_func_loc') && empty($thirdFieldDeendentValue)) {
							continue;
						}
					}

					if ($fieldModel->isViewableInDetailView() && in_array($fieldName, $dependecyFieldList)) {
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

	public function getFieldsOfCategory($type, $purposeValue) {
		if ($type == 'GENERAL INSPECTION' || $type == 'SERVICE FOR SPARES PURCHASED') {
			$fieldDependeny = Vtiger_DependencyPicklist::getFieldsFitDependency('HelpDesk', 'purpose', 'ticketstatus');
			$type = $purposeValue;
		} else {
			$fieldDependeny = Vtiger_DependencyPicklist::getFieldsFitDependency('HelpDesk', 'ticket_type', 'ticketpriorities');
		}
		foreach ($fieldDependeny['valuemapping'] as $valueMapping) {
			if ($valueMapping['sourcevalue'] == $type) {
				return $valueMapping['targetvalues'];
			}
		}
	}
}
