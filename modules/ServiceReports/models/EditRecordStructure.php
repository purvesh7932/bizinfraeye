<?php
include_once('include/utils/GeneralConfigUtils.php');
class ServiceReports_EditRecordStructure_Model extends Inventory_EditRecordStructure_Model {
    public function getStructure($ticketType, $purpose) {
        if (!empty($this->structuredValues)) {
            return $this->structuredValues;
        }

        $values = array();
        $recordModel = $this->getRecord();
        $recordExists = !empty($recordModel);
        $recordId = $recordModel->getId();
        $moduleModel = $this->getModule();
        $blockModelList = $moduleModel->getBlocks();
        $dependecyFieldList = $this->getFieldsOfCategory($ticketType, $purpose);
        // $mandatoryFieldsFieldList = $this->getMandatoryFieldsBasedOnType($ticketType, $purpose);
        
        $blocksSeq = getTypeBlockSequence($ticketType, $purpose);
        $manualEqValue = $recordModel->get('manual_equ_ser');
        foreach ($blocksSeq as $blocksSeqLabel) {
            foreach ($blockModelList as $blockLabel => $blockModel) {
                if (decode_html($blocksSeqLabel) == decode_html($blockLabel)) {
                    $fieldModelList = $blockModel->getFields();
                    if (!empty($fieldModelList)) {
                        $values[$blockLabel] = array();
                        foreach ($fieldModelList as $fieldName => $fieldModel) {
                            if ($fieldName == 'manual_equ_ser' && empty($manualEqValue)) {
								continue;
							}
                            if ($fieldModel->isEditable() && in_array($fieldName, $dependecyFieldList)) {
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

                                // if (in_array($fieldName, $mandatoryFieldsFieldList)) {
                                //     $oldTpe = $fieldModel->get('typeofdata');
                                //     list($type, $mandatory) = explode('~', $oldTpe);
                                //     $fieldModel->set('typeofdata', $type . '~M');
                                // }
                            }
                        }
                    }
                    break;
                }
            }
        }
        $this->structuredValues = $values;
        return $values;
    }

    public function getFieldsOfCategory($type, $purposeValue) {
        if ($type == 'SERVICE FOR SPARES PURCHASED') {
            $fieldDependeny = Vtiger_DependencyPicklist::getFieldsFitDependency('ServiceReports', 'tck_det_purpose', 'type_of_conrt');
            $type = $purposeValue;
        } else {
            $fieldDependeny = Vtiger_DependencyPicklist::getFieldsFitDependency('ServiceReports', 'sr_ticket_type', 'sr_war_status');
        }
        foreach ($fieldDependeny['valuemapping'] as $valueMapping) {
            if ($valueMapping['sourcevalue'] == $type) {
                return $valueMapping['targetvalues'];
            }
        }
    }

    public function getMandatoryFieldsBasedOnType($type, $purposeValue) {
        // if ($type == 'GENERAL INSPECTION' || $type == 'SERVICE FOR SPARES PURCHASED' ) {
        // 	$fieldDependeny = Vtiger_DependencyPicklist::getFieldsFitDependency('HelpDesk', 'purpose', 'ticketstatus');
        // 	$type = $purposeValue;
        // } else {
        $fieldDependeny = Vtiger_DependencyPicklist::getFieldsFitDependency('ServiceReports', 'sr_ticket_type', 'tck_det_purpose');
        // }
        foreach ($fieldDependeny['valuemapping'] as $valueMapping) {
            if ($valueMapping['sourcevalue'] == $type) {
                return $valueMapping['targetvalues'];
            }
        }
    }
}
