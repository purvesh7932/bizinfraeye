<?php

class Contacts_EditRecordStructure_Model extends Vtiger_EditRecordStructure_Model {

	public function getStructure() {
		if(!empty($this->structuredValues)) {
			return $this->structuredValues;
		}

		$values = array();
		$recordModel = $this->getRecord();
		$recordId = $recordModel->getId();
		$moduleModel = $this->getModule();
		$blockModelList = $moduleModel->getBlocks();
		$hideFields = array();
		array_push($hideFields, 'user_password', 'confirm_password');
		foreach($blockModelList as $blockLabel=>$blockModel) {
			$fieldModelList = $blockModel->getFields();
			if (!empty ($fieldModelList)) {
				$values[$blockLabel] = array();
				foreach($fieldModelList as $fieldName=>$fieldModel) {
					if($fieldModel->isEditable()) {
						if (in_array($fieldName, $hideFields)) {
							continue;
						}
						if($recordModel->get($fieldName) != '') {
							$fieldModel->set('fieldvalue', $recordModel->get($fieldName));
						}else{
							$defaultValue = $fieldModel->getDefaultFieldValue();
							if(!empty($defaultValue) && !$recordId)
								$fieldModel->set('fieldvalue', $defaultValue);
						}
						$values[$blockLabel][$fieldName] = $fieldModel;
                        if ($fieldName == 'taxclass' && count($recordModel->getTaxClassDetails()) < 1) {
                            unset($values[$blockLabel][$fieldName]);
                        }
					}
				}
			}
		}
		$this->structuredValues = $values;
		return $values;
	}
}