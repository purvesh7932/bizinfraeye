<?php

class Inventory_Field_Model extends Vtiger_Field_Model {

    public static $lineItemFieldDisplayType = 5;

    public function getPicklistValues($refEntityType = false, $referenceFieldId = false) {
        if ($this->get('displaytype') == self::$lineItemFieldDisplayType) {
            if ($this->getName() == 'region_id') {
                return null;
            }

            if ($referenceFieldId || ($refEntityType && array_key_exists($refEntityType, $this->lineItemsSupportedModulesInfo))) {
                $values = $this->getLineItemPicklistValues($refEntityType, $referenceFieldId);
                if ($values && is_array($values)) {
                    return $values;
                }
            }
        }

        $fieldName = $this->getName();
        $moduleName = $this->getModuleName();
        if (strpos($fieldName, 'hdnTaxType') !== false) {
            $values = array();
            $values['individual'] = vtranslate('individual', $moduleName);
            $values['group'] = vtranslate('group', $moduleName);
            return $values;
        }

        return parent::getPicklistValues();
    }

    public function getEditablePicklistValues($refEntityType = false, $referenceFieldId = false) {
        if ($this->get('displaytype') == self::$lineItemFieldDisplayType) {
            if ($this->getName() == 'region_id') {
                return null;
            }

            if ($referenceFieldId || ($refEntityType && array_key_exists($refEntityType, $this->lineItemsSupportedModulesInfo))) {
                $values = $this->getLineItemEditablePicklistValues($refEntityType, $referenceFieldId);
                if ($values && is_array($values)) {
                    return $values;
                }
            }
        }

        $fieldName = $this->getName();
        $moduleName = $this->getModuleName();
        if (strpos($fieldName, 'hdnTaxType') !== false) {
            $values = array();
            $values['individual'] = vtranslate('individual', $moduleName);
            $values['group'] = vtranslate('group', $moduleName);
            return $values;
        }

        return parent::getEditablePicklistValues();
    }

    public function getLineItemFieldMapping() {
        return parent::getLineItemFieldMapping();
    }

    public function getDefaultFieldValue() {
        $defaultValue = parent::getDefaultFieldValue();
        return $defaultValue;
    }

    public function getEditViewDisplayValueInLineItemsBlock($value) {
        return parent::getEditViewDisplayValueInLineItemsBlock($value);
    }

    public function getDisplayValue($value, $record = false, $recordInstance = false, $removeTags = false, $textLengthCheck = true) {
        $fieldName = $this->getName();
        if (in_array($fieldName, array('outstanding_qty', 'received_qty', 'delivered_qty'))) {
            return $value;
        }
        return parent::getDisplayValue($value, $record, $recordInstance, $removeTags, $textLengthCheck);
    }

}
