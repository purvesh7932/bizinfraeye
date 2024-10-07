<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
include_once 'include/Webservices/Retrieve.php';
include_once dirname(__FILE__) . '/FetchRecord.php';
include_once 'include/Webservices/DescribeObject.php';
include_once('include/utils/GeneralUtils.php');
class Mobile_WS_FetchRecordWithGrouping extends Mobile_WS_FetchRecord {
	
	private $_cachedDescribeInfo = false;
	private $_cachedDescribeFieldInfo = false;
	
	protected function cacheDescribeInfo($describeInfo) {
		$this->_cachedDescribeInfo = $describeInfo;
		$this->_cachedDescribeFieldInfo = array();
		if(!empty($describeInfo['fields'])) {
			foreach($describeInfo['fields'] as $describeFieldInfo) {
				$this->_cachedDescribeFieldInfo[$describeFieldInfo['name']] = $describeFieldInfo;
			}
		}
	}
	
	protected function cachedDescribeInfo() {
		return $this->_cachedDescribeInfo;
	}
	
	protected function cachedDescribeFieldInfo($fieldname) {
		if ($this->_cachedDescribeFieldInfo !== false) {
			if(isset($this->_cachedDescribeFieldInfo[$fieldname])) {
				return $this->_cachedDescribeFieldInfo[$fieldname];
			}
		}
		return false;
	}
	
	protected function cachedEntityFieldnames($module) {
		$describeInfo = $this->cachedDescribeInfo();
		$labelFields = $describeInfo['labelFields'];
		switch($module) {
			case 'HelpDesk': $labelFields = 'ticket_title'; break;
			case 'Documents': $labelFields = 'notes_title'; break;
		}
		return explode(',', $labelFields);
	}
	
	protected function isTemplateRecordRequest(Mobile_API_Request $request) {
		$recordid = $request->get('record');
		return (preg_match("/([0-9]+)x0/", $recordid));
	}
	
	protected function processRetrieve(Mobile_API_Request $request) {
		$recordid = $request->get('record');

		// Create a template record for use 
		if ($this->isTemplateRecordRequest($request)) {
			$current_user = $this->getActiveUser();
			
			$module = $this->detectModuleName($recordid);
		 	$describeInfo = vtws_describe($module, $current_user);
		 	Mobile_WS_Utils::fixDescribeFieldInfo($module, $describeInfo);

		 	$this->cacheDescribeInfo($describeInfo);

			$templateRecord = array();
			foreach($describeInfo['fields'] as $describeField) {
				$templateFieldValue = '';
				if (isset($describeField['type']) && isset($describeField['type']['defaultValue'])) {
					$templateFieldValue = $describeField['type']['defaultValue'];
				} else if (isset($describeField['default'])) {
					$templateFieldValue = $describeField['default'];
				}
				$templateRecord[$describeField['name']] = $templateFieldValue;
			}
			if (isset($templateRecord['assigned_user_id'])) {
				$templateRecord['assigned_user_id'] = sprintf("%sx%s", Mobile_WS_Utils::getEntityModuleWSId('Users'), $current_user->id);
			} 
			// Reset the record id
			$templateRecord['id'] = $recordid;
			
			return $templateRecord;
		}
		
		// Or else delgate the action to parent
		return parent::processRetrieve($request);
	}
	
	function process(Mobile_API_Request $request) {
		$module = $request->get('module');
		if ($module == 'FailedParts' || $module == 'ServiceOrders') {
			vglobal('IGMODULE', $module);
			vglobal('VIEWABLEFIELDSLINE', $this->geAllowedFieldsInLineItem($module, 'Item Details'));
		}
		$response = parent::process($request);
		return $this->processWithGrouping($request, $response);
	}
	
	protected function processWithGrouping(Mobile_API_Request $request, $response) {
		$isTemplateRecord = $this->isTemplateRecordRequest($request);
		$result = $response->getResult();
		
		$resultRecord = $result['record'];
		$module = $this->detectModuleName($resultRecord['id']);
		$modifiedRecord = $this->transformRecordWithGrouping($resultRecord, $module, $isTemplateRecord);
		
		if ($module == 'FailedParts') {
			//add values from service request into failed parts api purvesh
			$recordId = explode("x", $resultRecord['id']);
			$dataArray = GetPOFields($recordId[1]);
			$Date = explode(" ", $dataArray['createdtime']);
			$pono = str_replace(' ', '', $dataArray['external_app_num']);
			$modifiedRecord['po'] = $pono;
			$modifiedRecord['podate'] = $Date[0];
			$response->setResult(array('record' => $modifiedRecord));
		} else {
			$response->setResult(array('record' => $modifiedRecord));
		}
		$response->setApiSucessMessage('Successfully Fetched Data');
		return $response;
	}
	
	protected function transformRecordWithGrouping($resultRecord, $module, $isTemplateRecord=false) {
		$current_user = $this->getActiveUser();

		$moduleFieldGroups = Mobile_WS_Utils::gatherModuleFieldGroupInfo($module);
		$describeInfo = vtws_describe($module, $current_user);
		$modifiedResult = array();
		
		$blocks = array(); $labelFields = false;
		foreach($moduleFieldGroups as $blocklabel => $fieldgroups) {
			$fields = array();
			foreach($fieldgroups as $fieldname => $fieldinfo) {
				foreach ($describeInfo['fields'] as $key => $value) {
					if ($value['name'] ==  $fieldname){
						if(isset($resultRecord[$fieldname]) || $blocklabel == 'Item Details') {
							$field = array(
								'name'  => $fieldname,
								'value' => $resultRecord[$fieldname],
								'label' => $fieldinfo['label'],
								'uitype'=> $fieldinfo['uitype'],
								'editable' => $value['editable']
							);
							
							if ($isTemplateRecord) {
								$describeFieldInfo = $this->cachedDescribeFieldInfo($fieldname);
								if ($describeFieldInfo) {
									foreach($describeFieldInfo as $k=>$v) {
										if (isset($field[$k])) continue;
										$field[$k] = $v;
									}
								}
								// Entity fieldnames
								$labelFields = $this->cachedEntityFieldnames($module);
							}
							if ($field['uitype'] == '53') {
								$field['type']['defaultValue'] = array('value' => "19x{$current_user->id}", 'label' => $current_user->column_fields['last_name']);
							} else if($field['uitype'] == '117') {
								$field['type']['defaultValue'] = $field['value'];
							}
							else if($field['name'] == 'terms_conditions' && in_array($module, array('Quotes','Invoice', 'SalesOrder', 'PurchaseOrder'))){ 
								$field['type']['defaultValue'] = $field['value'];
							}
							else if ($field['name'] == 'visibility' && in_array($module, array('Calendar','Events'))){
								$field['type']['defaultValue'] = $field['value']; 
							} else if($field['type']['name'] != 'reference') {
								$field['type']['defaultValue'] = $field['default'];
							}
							if ($field['uitype'] == '10' || $field['uitype'] == '52' || $field['uitype'] == '53' || $field['uitype'] == '117') {
								$field['id'] = $field['value']['value'];
								$field['value'] = $field['value']['label'];
							}
							$fields[] = $field;
						}
						break;
					}
				}
			}
			$blocks[] = array( 'label' => $blocklabel, 'fields' => $fields );
		}
		
		$sections = array();
		$moduleFieldGroupKeys = array_keys($moduleFieldGroups);
		foreach($moduleFieldGroupKeys as $blocklabel) {
			if(isset($groups[$blocklabel]) && !empty($groups[$blocklabel])) {
				$sections[] = array( 'label' => $blocklabel, 'count' => count($groups[$blocklabel]) );
			}
		}
		
		$modifiedResult = array('blocks' => $blocks, 'id' => $resultRecord['id']);
		if($labelFields) $modifiedResult['labelFields'] = $labelFields;
		
		if (isset($resultRecord['LineItems'])) {
		$lineItems = $resultRecord['LineItems'];
			// foreach($lineItems as $key=>$lineItem){
			// 	// $modifiedResult['LineItems'] = $resultRecord['LineItems'];
    		// 	$lineItems = $resultRecord['LineItems'];
    		// 	foreach($lineItems as $key=>$lineItem){
    		// 		$recordid = $lineItem['productid'];
    		// 		$record = vtws_retrieve($recordid, $current_user);
    		// 		$lineItems[$key]['assigned_user_id'] = $record["assigned_user_id"];
    		// 	}
    		// 	$modifiedResult['LineItems'] = $lineItems;
			// }
			$modifiedResult['LineItems'] = $lineItems;
		}
		
		return $modifiedResult;
	}
}
