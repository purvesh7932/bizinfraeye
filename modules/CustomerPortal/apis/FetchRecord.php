<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ***********************************************************************************/

class CustomerPortal_FetchRecord extends CustomerPortal_API_Abstract {

	protected function processRetrieve(CustomerPortal_API_Request $request, $module) {
		global $adb;
		$parentId = $request->get('parentId');
		$recordId = $request->get('recordId');
		// $module = VtigerWebserviceObject::fromId($adb, $recordId)->getEntityName();

		if (!CustomerPortal_Utils::isModuleActive($module)) {
			throw new Exception("Records not Accessible for this module", 1412);
			exit;
		}

		if (!empty($parentId)) {
			if (!$this->isRecordAccessible($parentId)) {
				throw new Exception("Parent record not Accessible", 1412);
				exit;
			}
			$relatedRecordIds = $this->relatedRecordIds($module, CustomerPortal_Utils::getRelatedModuleLabel($module), $parentId);
			
			if (!in_array($recordId, $relatedRecordIds)) {
				throw new Exception("Record not Accessible", 1412);
				exit;
			}
		} else {
			if (!$this->isRecordAccessible($recordId, $module)) {
				throw new Exception("Record not Accessible", 1412);
				exit;
			}
		}

		$fields = implode(',', CustomerPortal_Utils::getActiveFields($module));
		$sql = sprintf('SELECT %s FROM %s WHERE id=\'%s\';', '*', $module, $recordId);
		$result = vtws_query($sql, $this->getActiveUser());
		return $result[0];
	}

	function process(CustomerPortal_API_Request $request) {
		$response = new CustomerPortal_API_Response();
		$current_user = $this->getActiveUser();
		global $adb;
		$recordId = $request->get('recordId');
		$module = VtigerWebserviceObject::fromId($adb, $recordId)->getEntityName();

		if ($current_user) {
			$record = $this->processRetrieve($request, $module);

			$record = CustomerPortal_Utils::resolveRecordValues($record);
			$response->setResult(array('record' => $record,
			'moduleFieldGroups' => $this->gatherModuleFieldGroupInfo($module),
			'fieldsOfCategory' =>  $this->getFieldsOfCategory($record['ticket_type'], $record['purpose'])
		));

		}
		return $response;
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

	static $gatherModuleFieldGroupInfoCache = array();

	static function gatherModuleFieldGroupInfo($module) {
		global $adb;

		if ($module == 'Events') $module = 'Calendar';

		if (isset(self::$gatherModuleFieldGroupInfoCache[$module])) {
			return self::$gatherModuleFieldGroupInfoCache[$module];
		}

		$result = $adb->pquery(
			"SELECT fieldname, fieldlabel, blocklabel, uitype FROM vtiger_field INNER JOIN
			vtiger_blocks ON vtiger_blocks.tabid=vtiger_field.tabid AND vtiger_blocks.blockid=vtiger_field.block 
			WHERE vtiger_field.tabid=? AND vtiger_field.presence != 1 and blocklabel !='LBL_TICKET_INFORMATION' ORDER BY vtiger_blocks.sequence, vtiger_field.sequence",
			array(getTabid($module))
		);

		$fieldgroups = array();
		while ($resultrow = $adb->fetch_array($result)) {
			$blocklabel = getTranslatedString($resultrow['blocklabel'], $module);
			if (!isset($fieldgroups[$blocklabel])) {
				$fieldgroups[$blocklabel] = array();
			}
			$fieldgroups[$blocklabel][$resultrow['fieldname']] =
				array(
					'label' => getTranslatedString($resultrow['fieldlabel'], $module),
					'uitype' => self::fixUIType($module, $resultrow['fieldname'], $resultrow['uitype'])
				);
		}

		self::$gatherModuleFieldGroupInfoCache[$module] = $fieldgroups;

		return $fieldgroups;
	}

	static function fixUIType($module, $fieldname, $uitype) {
		if ($module == 'Contacts' || $module == 'Leads') {
			if ($fieldname == 'salutationtype') {
				return 16;
			}
		} else if ($module == 'Calendar' || $module == 'Events') {
			if ($fieldname == 'time_start' || $fieldname == 'time_end') {
				return 252;
			}
		}
		return $uitype;
	}

}
