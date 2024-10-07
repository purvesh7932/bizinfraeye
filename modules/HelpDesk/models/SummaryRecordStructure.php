<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

/**
 * Vtiger Summary View Record Structure Model
 */
class HelpDesk_SummaryRecordStructure_Model extends Vtiger_SummaryRecordStructure_Model {

	public function getStructure() {
		$currentUsersModel = Users_Record_Model::getCurrentUserModel();
		$summaryFieldsList = $this->getModule()->getSummaryViewFieldsList();

		//For Calendar module getSummaryViewFieldsList() returns empty array. On changing that API Calendar related tab header
		//field changes. In related tab if summary fields are empty, it is depending of getRelatedListFields(). So added same here.
		if(empty($summaryFieldsList)) {
			$fieldModuleModel = $this->getModule();
			if($fieldModuleModel->getName() == 'Events') {
				$fieldModuleModel = Vtiger_Module_Model::getInstance('Calendar');
			}
			$summaryFieldsListNames = $fieldModuleModel->getRelatedListFields();
			foreach($summaryFieldsListNames as $summaryFieldsListName) {
				$summaryFieldsList[$summaryFieldsListName] = $fieldModuleModel->getField($summaryFieldsListName);
			}
		}

		$recordModel = $this->getRecord();
		$tiketType = $recordModel->get('ticket_type');
		$purposeValue = $recordModel->get('purpose');
		$dependecyFieldList = $this->getFieldsOfCategory($tiketType, $purposeValue);
		$blockSeqSortSummaryFields = array();
		if ($summaryFieldsList) {
			foreach ($summaryFieldsList as $fieldName => $fieldModel) {
				if($fieldModel->isViewableInDetailView() && in_array($fieldName, $dependecyFieldList)) {
					$fieldModel->set('fieldvalue', $recordModel->get($fieldName));
					$blockSequence = $fieldModel->block->sequence;
					if(!$currentUsersModel->isAdminUser() && ($fieldModel->getFieldDataType() == 'picklist' || $fieldModel->getFieldDataType() == 'multipicklist')) {
						$this->setupAccessiblePicklistValueList($fieldName);
					}
					$blockSeqSortSummaryFields[$blockSequence]['SUMMARY_FIELDS'][$fieldName] = $fieldModel;
				}
			}
		}
		$summaryFieldModelsList = array();
		ksort($blockSeqSortSummaryFields);
		foreach($blockSeqSortSummaryFields as $blockSequence => $summaryFields){
			$summaryFieldModelsList = array_merge_recursive($summaryFieldModelsList , $summaryFields);
		}
		return $summaryFieldModelsList;
	}

	public function getFieldsOfCategory($type, $purposeValue) {
		if ($type == 'GENERAL INSPECTION' || $type == 'SERVICE FOR SPARES PURCHASED' ) {
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