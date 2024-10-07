<?php

class ServiceOrders_EditRecordStructure_Model extends Inventory_EditRecordStructure_Model {
    public function getStructure() {
        if (!empty($this->structuredValues)) {
            return $this->structuredValues;
        }

        $values = array();
        $recordModel = $this->getRecord();
        $recordExists = !empty($recordModel);
        $recordId = $recordModel->getId();
        $moduleModel = $this->getModule();
        $blockModelList = $moduleModel->getBlocks();
        foreach ($blockModelList as $blockLabel => $blockModel) {
            if($blockLabel == 'LBL_SYSTEM_INFORMATION'){
				continue;
			}
            $fieldModelList = $blockModel->getFields();
            if (!empty($fieldModelList)) {
                $values[$blockLabel] = array();
                foreach ($fieldModelList as $fieldName => $fieldModel) {
                    if ($fieldModel->isEditable()) {
                        if ($recordExists) {
                            $fieldValue = $recordModel->get($fieldName, null);
                            if ($fieldName == 'terms_conditions' && $fieldValue == '' && !$recordModel->getId()) {
                                $fieldValue = $recordModel->getInventoryTermsAndConditions();
                            } else if ($fieldValue == '') {
                                $defaultValue = $fieldModel->getDefaultFieldValue();
                                if (!empty($defaultValue) && !$recordId)
                                    $fieldValue = $defaultValue;
                            }
                            $fieldModel->set('fieldvalue', $fieldValue);
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
