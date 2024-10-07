<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.2
 * ("License.txt"); You may not use this file except in compliance with the License
 * The Original Code is: Vtiger CRM Open Source
 * The Initial Developer of the Original Code is Vtiger.
 * Portions created by Vtiger are Copyright (C) Vtiger.
 * All Rights Reserved.
 * ***********************************************************************************/

class Portal_Default_API {

	public function requireLogin() {
		return true;
	}

	public function process(Portal_Request $request) {
		$response = new Portal_Response();
		$response->setResult($request->getAll());
		return $response;
	}

	public function processResponse($module, $language) {
		if (!isset($language)) {
			$language = Portal_Session::get('langauge');
		}
		$moduleMeta = array();
		$currentMetaInfo = Portal_Session::get('moduleMeta');
		if (isset($currentMetaInfo[$module])) {
			$recordMeta = $currentMetaInfo[$module];
		} else {
			$moduleMetaInfo = Vtiger_Connector::getInstance()->describeModule($module, $language);
			$moduleMeta[$module] = $moduleMetaInfo['describe']['fields'];
			$fieldInfo = array();
			$fieldInfo['labelField'] = $moduleMetaInfo['describe']['labelFields'];
			// For Leads module having two fields for name (firstname, lastname).
			if (strpos($fieldInfo['labelField'], ',')) {
				$labelFields = explode(',', $fieldInfo['labelField']);
				$fieldInfo['labelField'] = $labelFields[1];
			}
			if ($module == 'HelpDesk') {
				$fieldInfo['labelField'] = 'ticket_title';
			}
			if ($module == 'Documents') {
				$fieldInfo['labelField'] = 'notes_title';
			}
			foreach ($moduleMeta[$module] as $key => $value) {
				$fieldInfo['id']['label'] = 'id';
				$fieldInfo[$value['name']]['type'] = $value['type']['name'];
				$fieldInfo[$value['name']]['label'] = $value['label'];
				$fieldInfo[$value['name']]['editable'] = $value['editable'];
				if ($value['type']['name'] == 'picklist' || $value['type']['name'] == 'metricpicklist') {
					$fieldInfo[$value['name']]['picklistValues'] = $value['type']['picklistValues'];
				}
				if ($value['type']['name'] == 'multipicklist') {
					$fieldInfo[$value['name']]['picklistValues'] = $value['type']['picklistValues'];
				}
			}

			$identifierFieldLabel = $fieldInfo['labelField'];
			$fieldInfo['labelField'] = $fieldInfo[$identifierFieldLabel];
			$moduleMeta[$module] = $fieldInfo;
			Portal_Session::set('moduleMeta', $moduleMeta);
			$recordMeta = $moduleMeta[$module];
		}
		return $recordMeta;
	}

	static $moduleWSIdCache = array();
	
	static function getEntityModuleWSId($moduleName) {
		if (!isset(self::$moduleWSIdCache[$moduleName])) {
			global $adb;
			$result = $adb->pquery("SELECT id FROM vtiger_ws_entity WHERE name=?", array($moduleName));
			if ($result && $adb->num_rows($result)) {
				self::$moduleWSIdCache[$moduleName] = $adb->query_result($result, 0, 'id');
			}
		}
		return self::$moduleWSIdCache[$moduleName];
	}

	static function detectModulenameFromRecordId($wsrecordid) {
		global $adb;
		$idComponents = vtws_getIdComponents($wsrecordid);
		$result = $adb->pquery("SELECT name FROM vtiger_ws_entity WHERE id=?", array($idComponents[0]));
		if($result && $adb->num_rows($result)) {
			return $adb->query_result($result, 0, 'name');
		}
		return false;
	}

	static function getRelatedFunctionHandler($sourceModule, $targetModule) {
		global $adb;
		$relationResult = $adb->pquery("SELECT name FROM vtiger_relatedlists WHERE tabid=? and related_tabid=? and presence=0", array(getTabid($sourceModule), getTabid($targetModule)));
		$functionName = false;
		if ($adb->num_rows($relationResult)) $functionName = $adb->query_result($relationResult, 0, 'name');
		return $functionName;
	}

}
