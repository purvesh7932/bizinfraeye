<?php
class Contacts_DetailRecordStructure_Model extends Vtiger_DetailRecordStructure_Model {

	public function getStructure() {
		$currentUsersModel = Users_Record_Model::getCurrentUserModel();
		if(!empty($this->structuredValues)) {
			return $this->structuredValues;
		}

		$values = array();
		$recordModel = $this->getRecord();
		$recordExists = !empty($recordModel);
		$moduleModel = $this->getModule();
		$blockModelList = $moduleModel->getBlocks();
		$hideFields = array();
		array_push($hideFields, 'user_password', 'confirm_password');
		foreach($blockModelList as $blockLabel=>$blockModel) {
			$fieldModelList = $blockModel->getFields();
			if (!empty ($fieldModelList)) {
				$values[$blockLabel] = array();
				foreach($fieldModelList as $fieldName=>$fieldModel) {
					if($fieldModel->isViewableInDetailView()) {
						if (in_array($fieldName, $hideFields)) {
							continue;
						}
						if($recordExists) {
							$value = $recordModel->get($fieldName);
							if(!$currentUsersModel->isAdminUser() && ($fieldModel->getFieldDataType() == 'picklist' || $fieldModel->getFieldDataType() == 'multipicklist')) {
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