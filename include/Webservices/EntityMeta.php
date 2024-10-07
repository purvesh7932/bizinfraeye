<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

abstract class EntityMeta{
	
	public static $RETRIEVE = "DetailView";
	public static $CREATE = "CreateView";
	public static $UPDATE = "EditView";
	public static $DELETE = "Delete";
	
	protected $webserviceObject;
	protected $objectName;
	protected $objectId;
	protected $user;
	protected $baseTable;
	protected $tableList;
	protected $tableIndexList;
	protected $defaultTableList;
	protected $idColumn;
	
	protected $userAccessibleColumns;
	protected $columnTableMapping;
	protected $fieldColumnMapping;
	protected $mandatoryFields;
	protected $referenceFieldDetails;
	protected $emailFields;
	protected $ownerFields;
	protected $dateFields;
	protected $referenceFields;
	protected $moduleFields = null;
	
	protected function __construct($webserviceObject,$user)
    {
        $this->webserviceObject = $webserviceObject;
		$this->objectName = $this->webserviceObject->getEntityName();
		$this->objectId = $this->webserviceObject->getEntityId();
		
		$this->user = $user;
    }
	protected function EntityMeta($webserviceObject,$user){
		// PHP4-style constructor.
        // This will NOT be invoked, unless a sub-class that extends `foo` calls it.
        // In that case, call the new-style constructor to keep compatibility.
        self::__construct($webserviceObject,$user);
	}
	
	public function getEmailFields(){
		if($this->emailFields === null){
			$this->emailFields =  array();
			$moduleFields = $this->getModuleFields();
			foreach ($moduleFields as $fieldName=>$webserviceField) {
				if((strcasecmp($webserviceField->getFieldType(),'e') === 0) || $webserviceField->getUIType() === '13'){
					array_push($this->emailFields, $fieldName);
				}
			}
		}
		
		return $this->emailFields;
	}
	
	public function getFieldColumnMapping(){
		if($this->fieldColumnMapping === null){
			$this->fieldColumnMapping =  array();
			
			$moduleFields = $this->getModuleFields();
			foreach ($moduleFields as $fieldName=>$webserviceField) {
				$this->fieldColumnMapping[$fieldName] = $webserviceField->getColumnName();
			}
			$this->fieldColumnMapping['id'] = $this->idColumn;
		}
		return $this->fieldColumnMapping;
	}
	
	public function getMandatoryFields(){
		if($this->mandatoryFields === null){
			$this->mandatoryFields =  array();
			
			$moduleFields = $this->getModuleFields();
			foreach ($moduleFields as $fieldName=>$webserviceField) {
				if($webserviceField->isMandatory() === true){
					array_push($this->mandatoryFields,$fieldName);
				}
			}
		}
		return $this->mandatoryFields;
	}
	
	public function getReferenceFieldDetails(){
		if($this->referenceFieldDetails === null){
			$this->referenceFieldDetails =  array();
			
			$moduleFields = $this->getModuleFields();
			foreach ($moduleFields as $fieldName=>$webserviceField) {
				if(strcasecmp($webserviceField->getFieldDataType(),'reference') === 0){
					$this->referenceFieldDetails[$fieldName] = $webserviceField->getReferenceList();
				}
			}
		}
		return $this->referenceFieldDetails;
	}
	
	public function getOwnerFields(){
		if($this->ownerFields === null){
			$this->ownerFields =  array();
			
			$moduleFields = $this->getModuleFields();
			foreach ($moduleFields as $fieldName=>$webserviceField) {
				if(strcasecmp($webserviceField->getFieldDataType(),'owner') === 0){
					array_push($this->ownerFields, $fieldName);
				}
			}
		}
		return $this->ownerFields;
	}

	public function getIGDateFields() {
		if ($this->dateFields === null) {
			$this->dateFields =  array();
			$moduleFields = $this->getModuleFields();
			foreach ($moduleFields as $fieldName => $webserviceField) {
				if (strcasecmp($webserviceField->getFieldDataType(), 'date') === 0) {
					array_push($this->dateFields, $fieldName);
				}
			}
		}
		return $this->dateFields;
	}

	public function getIGReferenceFields() {
		if ($this->referenceFields === null) {
			$this->referenceFields =  array();
			$moduleFields = $this->getModuleFields();
			foreach ($moduleFields as $fieldName => $webserviceField) {
				if (strcasecmp($webserviceField->getFieldDataType(), 'reference') === 0) {
					array_push($this->referenceFields, $fieldName);
				}
			}
		}
		return $this->referenceFields;
	}
	
	public function getObectIndexColumn(){
		return $this->idColumn;
	}
	
	public function getUserAccessibleColumns(){
		if($this->userAccessibleColumns === null){
			$this->userAccessibleColumns =  array();
			
			$moduleFields = $this->getModuleFields();
			foreach ($moduleFields as $fieldName=>$webserviceField) {
				array_push($this->userAccessibleColumns,$webserviceField->getColumnName());
			}
			array_push($this->userAccessibleColumns,$this->idColumn);
		}
		return $this->userAccessibleColumns;
	}

	public function getFieldByColumnName($column){
		$fields = $this->getModuleFields();
		foreach ($fields as $fieldName=>$webserviceField) {
			if($column == $webserviceField->getColumnName()) {
				return $webserviceField;
			}
		}
		return null;
	}
	
	public function getColumnTableMapping(){
		if($this->columnTableMapping === null){
			$this->columnTableMapping =  array();
			
			$moduleFields = $this->getModuleFields();
			foreach ($moduleFields as $fieldName=>$webserviceField) {
				$this->columnTableMapping[$webserviceField->getColumnName()] = $webserviceField->getTableName();
			}
			$this->columnTableMapping[$this->idColumn] = $this->baseTable;
		}
		return $this->columnTableMapping;
	}
	
	function getUser(){
		return $this->user;
	}

	function hasMandatoryFields($row) {
		$mandatoryFields = $this->getMandatoryFields();
		$hasMandatory = true;
		$lineFields = vglobal('EDITABLEFIELDSLINE');
		if (empty($lineFields)) {
			$lineFields = [];
		}
		if ($this->objectName == 'HelpDesk') {
			include_once('include/utils/IgClassUtils.php');
			$mandatoryFields = array_merge($mandatoryFields, IgClassUtils::getMandatoryFieldsBasedOnType($row['ticket_type'], $row['purpose']));
			$sanitisedMandatory = [];
			foreach($mandatoryFields as $mandatoryField){
				if(!empty($mandatoryField)){
					array_push($sanitisedMandatory , $mandatoryField);
				}
			}
			$mandatoryFields = $sanitisedMandatory;
		} 
		// else if ($this->objectName == 'ServiceReports') {
		// 	include_once('include/utils/IgClassUtils.php');
		// 	$mandatoryFields = array_merge($mandatoryFields, IgClassUtils::getMandatoryFieldsBasedOnType($row['sr_ticket_type'], $row['tck_det_purpose']));
		// 	$sanitisedMandatory = [];
		// 	foreach ($mandatoryFields as $mandatoryField) {
		// 		if (!empty($mandatoryField)) {
		// 			array_push($sanitisedMandatory, $mandatoryField);
		// 		}
		// 	}
		// 	$mandatoryFields = $sanitisedMandatory;
		// }
		foreach ($mandatoryFields as $ind => $field) {
			// dont use empty API as '0'(zero) is a valid value.
			if ((!isset($row[$field]) || $row[$field] === "" || $row[$field] === null) && !in_array($field, $lineFields)) {
				$fieldLabel = $this->getFieldLabel($field);
				throw new WebServiceException(
					WebServiceErrorCode::$MANDFIELDSMISSING,
					"$fieldLabel does not have a value"
				);
			}
		}
		global $moduleNameForLine,$IGRecordMode;
		$inventoryModules = getInventoryModules();
		if (in_array($moduleNameForLine, $inventoryModules) && $IGRecordMode != 'Edit') {
			$lineItemMandatoryFields = array_intersect($mandatoryFields,$lineFields);
			foreach ($row['LineItems'] as $lineKey => $lineRow) {
				foreach ($lineItemMandatoryFields as $lineManKey) {
					if ((!isset($lineRow[$lineManKey]) || $lineRow[$lineManKey] === "" || $lineRow[$lineManKey] === null)) {
						throw new WebServiceException(
							WebServiceErrorCode::$MANDFIELDSMISSING,
							"$lineManKey Does Not Have A Value In The Row ". ($lineKey + 1) ." In Line Items"
						);
					}
				}
			}
		}
		return $hasMandatory;
	}
	
	public function getFieldLabel($fieldname) {
		$moduleFields = $this->getModuleFields();
		foreach ($moduleFields as $fieldName => $webserviceField) {
			if (strcasecmp($fieldName, $fieldname) === 0) {
				return vtranslate($webserviceField->getFieldLabelKey(), $this->objectName);
			}
		}
		return '';
	}

	public function isUpdateMandatoryFields($element){
		if(!is_array($element)){
			throw new WebServiceException(WebServiceErrorCode::$MANDFIELDSMISSING,
							"Mandatory field does not have a value");
		}
		$mandatoryFields = $this->getMandatoryFields();
		$updateFields = array_keys($element);
		$hasMandatory = true;
		$updateMandatoryFields = array_intersect($updateFields, $mandatoryFields);
		if(!empty($updateMandatoryFields)){
			foreach($updateMandatoryFields as $ind=>$field){
				// dont use empty API as '0'(zero) is a valid value.
				if( !isset($element[$field]) || $element[$field] === "" || $element[$field] === null ){
					throw new WebServiceException(WebServiceErrorCode::$MANDFIELDSMISSING,
							"$field does not have a value");
				}
			}
		}
		return $hasMandatory;
	}
	
	public function getModuleFields(){
		return $this->moduleFields;
	}

	public function getFieldNameListByType($type) { 
		$type = strtolower($type); 
		$typeList = array(); 
		$moduleFields = $this->getModuleFields(); 
		foreach ($moduleFields as $fieldName=>$webserviceField) { 
			if(strcmp($webserviceField->getFieldDataType(),$type) === 0){ 
				array_push($typeList, $fieldName); 
			} 
		} 
		return $typeList; 
	}

	public function getFieldListByType($type) {
		$type = strtolower($type);
		$typeList = array();
		$moduleFields = $this->getModuleFields();
		foreach ($moduleFields as $fieldName=>$webserviceField) {
			if(strcmp($webserviceField->getFieldDataType(),$type) === 0){
				array_push($typeList, $webserviceField);
			}
		}
		return $typeList;
	}
	
	public function getIdColumn(){
		return $this->idColumn;
	}

	public function getEntityBaseTable() {
		return $this->baseTable;
	}

	public function getEntityTableIndexList() {
		return $this->tableIndexList;
	}

	public function getEntityDefaultTableList() {
		return $this->defaultTableList;
	}

	public function getEntityTableList() {
		return $this->tableList;
	}

	public function getEntityAccessControlQuery(){
		$accessControlQuery = '';
		return $accessControlQuery;
	}

	public function getEntityDeletedQuery(){
		if($this->getEntityName() == 'Leads') {
			return "vtiger_crmentity.deleted=0 and vtiger_leaddetails.converted=0";
		}
		if($this->getEntityName() != "Users"){
			return "vtiger_crmentity.deleted=0";
		}
		// not sure whether inactive users should be considered deleted or not.
		return "vtiger_users.status='Active'";
	}

	abstract function hasPermission($operation,$webserviceId);
	abstract function hasAssignPrivilege($ownerWebserviceId);
	abstract function hasDeleteAccess();
	abstract function hasAccess();
	abstract function hasReadAccess();
	abstract function hasCreateAccess();
	abstract function hasWriteAccess();
	abstract function getEntityName();
	abstract function getEntityId();
	abstract function exists($recordId);
	abstract function getObjectEntityName($webserviceId);
	abstract public function getNameFields();
	abstract public function getName($webserviceId);
	abstract public function isModuleEntity();
}
?>