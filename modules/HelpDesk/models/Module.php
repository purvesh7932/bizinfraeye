<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class HelpDesk_Module_Model extends Inventory_Module_Model {

	/**
	 * Function to get the Quick Links for the module
	 * @param <Array> $linkParams
	 * @return <Array> List of Vtiger_Link_Model instances
	 */
	public function getSideBarLinks($linkParams) {
		$parentQuickLinks = parent::getSideBarLinks($linkParams);

		$quickLink = array(
			'linktype' => 'SIDEBARLINK',
			'linklabel' => 'LBL_DASHBOARD',
			'linkurl' => $this->getDashBoardUrl(),
			'linkicon' => '',
		);

		//Check profile permissions for Dashboards
		$moduleModel = Vtiger_Module_Model::getInstance('Dashboard');
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$permission = $userPrivilegesModel->hasModulePermission($moduleModel->getId());
		if ($permission) {
			$parentQuickLinks['SIDEBARLINK'][] = Vtiger_Link_Model::getInstanceFromValues($quickLink);
		}

		return $parentQuickLinks;
	}

	/**
	 * Function to get Settings links for admin user
	 * @return Array
	 */
	public function getSettingLinks() {
		$settingsLinks = parent::getSettingLinks();
		$currentUserModel = Users_Record_Model::getCurrentUserModel();

		if ($currentUserModel->isAdminUser()) {
			$settingsLinks[] = array(
				'linktype' => 'LISTVIEWSETTING',
				'linklabel' => 'LBL_EDIT_MAILSCANNER',
				'linkurl' => 'index.php?parent=Settings&module=MailConverter&view=List',
				'linkicon' => ''
			);
		}
		return $settingsLinks;
	}


	/**
	 * Function returns Tickets grouped by Status
	 * @param type $data
	 * @return <Array>
	 */
	public function getOpenTickets() {
		$db = PearDatabase::getInstance();
		//TODO need to handle security
		$params = array();
		$picklistvaluesmap = getAllPickListValues("ticketstatus");
		if (in_array('Open', $picklistvaluesmap)) $params[] = 'Open';

		if (count($params) > 0) {
			$result = $db->pquery('SELECT count(*) AS count, COALESCE(vtiger_groups.groupname,concat(vtiger_users.first_name, " " ,vtiger_users.last_name)) as name, COALESCE(vtiger_groups.groupid,vtiger_users.id) as id  FROM vtiger_troubletickets
						INNER JOIN vtiger_crmentity ON vtiger_troubletickets.ticketid = vtiger_crmentity.crmid
						LEFT JOIN vtiger_users ON vtiger_users.id=vtiger_crmentity.smownerid AND vtiger_users.status="ACTIVE"
						LEFT JOIN vtiger_groups ON vtiger_groups.groupid=vtiger_crmentity.smownerid
						' . Users_Privileges_Model::getNonAdminAccessControlQuery($this->getName()) .
				' WHERE vtiger_troubletickets.status = ? AND vtiger_crmentity.deleted = 0 GROUP BY smownerid', $params);
		}
		$data = array();
		for ($i = 0; $i < $db->num_rows($result); $i++) {
			$row = $db->query_result_rowdata($result, $i);
			$row['name'] = decode_html($row['name']);
			$data[] = $row;
		}
		return $data;
	}

	public function searchRecord($searchValue, $parentId = false, $parentModule = false, $relatedModule = false, $searchModule = '', $equipmentModel = false) {
		if ($searchModule == 'Equipment') {
			$searchFields = array('crmid', 'label', 'setype');
			if (!empty($searchValue) && empty($parentId) && empty($parentModule)) {
				$matchingRecords = $this->getSearchResultEqui($searchValue, $this->getName(), $parentModule, $equipmentModel);
			} else if ($parentId && $parentModule) {
				$db = PearDatabase::getInstance();
				$result = $db->pquery($this->getSearchRecordsQueryEqui($searchValue, $searchFields, $parentId, $parentModule), array());
				$noOfRows = $db->num_rows($result);

				$moduleModels = array();
				$matchingRecords = array();
				for ($i = 0; $i < $noOfRows; ++$i) {
					$row = $db->query_result_rowdata($result, $i);
					if (Users_Privileges_Model::isPermitted($row['setype'], 'DetailView', $row['crmid'])) {
						$row['id'] = $row['crmid'];
						$moduleName = $row['setype'];
						if (!array_key_exists($moduleName, $moduleModels)) {
							$moduleModels[$moduleName] = Vtiger_Module_Model::getInstance($moduleName);
						}
						$moduleModel = $moduleModels[$moduleName];
						$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'Record', $moduleName);
						$recordInstance = new $modelClassName();
						$matchingRecords[$moduleName][$row['id']] = $recordInstance->setData($row)->setModuleFromInstance($moduleModel);
					}
				}
			}
			return $matchingRecords;
		} else {
			return parent::searchRecord($searchValue, $parentId, $parentModule, $relatedModule);
		}
	}

	public function getSearchResultEqui($searchKey, $module = false, $parentModule, $equipmentModel) {
		$db = PearDatabase::getInstance();
		require_once('include/utils/GeneralUtils.php');
		global $current_user;
		$data = getUserDetailsBasedOnEmployeeModuleG($current_user->user_name);

		if ($data && $data['cust_role'] == 'Service Manager' && $data['sub_service_manager_role'] == 'Service Manager Support') {
			$functionalLocations = getOnlyLinkedEquimentsSE('36x' . $data['serviceengineerid']);
			$query = 'SELECT label, crmid, setype, createdtime 
				FROM vtiger_crmentity 
				INNER JOIN vtiger_equipment on vtiger_equipment.equipmentid = vtiger_crmentity.crmid
				WHERE label LIKE ? AND vtiger_crmentity.deleted = 0 ';

			if (empty($functionalLocations)) {
				$query = $query . ' and 1 = 2 ';
			} else {
				$query .= ' AND vtiger_equipment.equipmentid IN ("' .
					implode('","', $functionalLocations) . '")';
			}

			$params = array("%$searchKey%");

			if (!empty($equipmentModel)) {
				$query .= ' AND vtiger_equipment.equip_model = ?';
				$params[] = $equipmentModel;
			}

			$result = $db->pquery($query, $params);
			$noOfRows = $db->num_rows($result);

			$moduleModels = $matchingRecords = $leadIdsList = array();

			for ($i = 0, $recordsCount = 0; $i < $noOfRows && $recordsCount < 100; ++$i) {
				$row = $db->query_result_rowdata($result, $i);
				$row['id'] = $row['crmid'];
				$moduleName = $row['setype'];
				if (!array_key_exists($moduleName, $moduleModels)) {
					$moduleModels[$moduleName] = Vtiger_Module_Model::getInstance($moduleName);
				}
				$moduleModel = $moduleModels[$moduleName];
				$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'Record', $moduleName);
				$recordInstance = new $modelClassName();
				$matchingRecords[$moduleName][$row['id']] = $recordInstance->setData($row)->setModuleFromInstance($moduleModel);
				$recordsCount++;
			}
			return $matchingRecords;
		} else {
			$searchFields = array('crmid', 'label', 'setype');
			if (!empty($searchKey) && empty($parentId) && empty($parentModule)) {
				$matchingRecords = $this->getSearchResultHelpDesk($searchKey, 'Equipment', $equipmentModel);
			} else if ($parentId && $parentModule) {
				$db = PearDatabase::getInstance();
				$result = $db->pquery($this->getSearchRecordsQuery($searchValue, $searchFields, $parentId, $parentModule), array());
				$noOfRows = $db->num_rows($result);

				$moduleModels = array();
				$matchingRecords = array();
				for ($i = 0; $i < $noOfRows; ++$i) {
					$row = $db->query_result_rowdata($result, $i);
					if (Users_Privileges_Model::isPermitted($row['setype'], 'DetailView', $row['crmid'])) {
						$row['id'] = $row['crmid'];
						$moduleName = $row['setype'];
						if (!array_key_exists($moduleName, $moduleModels)) {
							$moduleModels[$moduleName] = Vtiger_Module_Model::getInstance($moduleName);
						}
						$moduleModel = $moduleModels[$moduleName];
						$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'Record', $moduleName);
						$recordInstance = new $modelClassName();
						$matchingRecords[$moduleName][$row['id']] = $recordInstance->setData($row)->setModuleFromInstance($moduleModel);
					}
				}
			}

			return $matchingRecords;
		}
	}

	public  function getSearchResultHelpDesk($searchKey, $module=false, $equipmentModel) {
		$db = PearDatabase::getInstance();

		$query = 'SELECT label, crmid, setype, createdtime 
		FROM vtiger_crmentity 
		INNER JOIN vtiger_equipment 
		ON vtiger_equipment.equipmentid = vtiger_crmentity.crmid 
		WHERE label LIKE ? AND vtiger_crmentity.deleted = 0
		 and vtiger_equipment.equip_category = "S" ';
		$params = array("%$searchKey%");

		if($module !== false) {
			$query .= ' AND setype = ?';
			$params[] = $module;
		}

		if (!empty($equipmentModel)) {
			$query .= ' AND vtiger_equipment.equip_model = ?';
			$params[] = $equipmentModel;
		}
		//Remove the ordering for now to improve the speed
		//$query .= ' ORDER BY createdtime DESC';

		$result = $db->pquery($query, $params);
		$noOfRows = $db->num_rows($result);

		$moduleModels = $matchingRecords = $leadIdsList = array();
		for($i=0; $i<$noOfRows; ++$i) {
			$row = $db->query_result_rowdata($result, $i);
			if ($row['setype'] === 'Leads') {
				$leadIdsList[] = $row['crmid'];
			}
		}
		$convertedInfo = Leads_Module_Model::getConvertedInfo($leadIdsList);

		for($i=0, $recordsCount = 0; $i<$noOfRows && $recordsCount<100; ++$i) {
			$row = $db->query_result_rowdata($result, $i);
			if ($row['setype'] === 'Leads' && $convertedInfo[$row['crmid']]) {
				continue;
			}
			if(Users_Privileges_Model::isPermitted($row['setype'], 'DetailView', $row['crmid'])) {
				$row['id'] = $row['crmid'];
				$moduleName = $row['setype'];
				if(!array_key_exists($moduleName, $moduleModels)) {
					$moduleModels[$moduleName] = Vtiger_Module_Model::getInstance($moduleName);
				}
				$moduleModel = $moduleModels[$moduleName];
				$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'Record', $moduleName);
				$recordInstance = new $modelClassName();
				$matchingRecords[$moduleName][$row['id']] = $recordInstance->setData($row)->setModuleFromInstance($moduleModel);
				$recordsCount++;
			}
		}
		return $matchingRecords;
	}

	public function getSearchRecordsQueryEqui($searchValue, $searchFields, $parentId = false, $parentModule = false) {
		$db = PearDatabase::getInstance();
		$query = $db->convert2Sql("SELECT " . implode(',', $searchFields) . " FROM vtiger_crmentity 
		WHERE label LIKE ? AND vtiger_crmentity.deleted = 0", array("%$searchValue%"));
		return $query;
	}

	public function getTicketsByStatusCountsForUser($owner, $dateFilter, $type) {
		$db = PearDatabase::getInstance();
		$ownerSql = $this->getOwnerWhereConditionForDashBoards($owner);
		if (!empty($ownerSql)) {
			$ownerSql = ' AND ' . $ownerSql;
		}
		$params = array();
		if (!empty($dateFilter)) {
			$dateFilterSql = ' AND createdtime BETWEEN ? AND ? ';
			$params[] = $dateFilter['start'];
			$params[] = $dateFilter['end'];
		}
		$picklistvaluesmap = getAllPickListValues("ticketstatus");
		foreach ($picklistvaluesmap as $picklistValue) {
			$params[] = $picklistValue;
		}
		$params[] = $type;
		$result = $db->pquery('SELECT COUNT(*) as count, CASE WHEN vtiger_troubletickets.status IS NULL OR vtiger_troubletickets.status = "" THEN "" ELSE vtiger_troubletickets.status END AS statusvalue 
			FROM vtiger_troubletickets INNER JOIN vtiger_crmentity ON vtiger_troubletickets.ticketid = vtiger_crmentity.crmid AND vtiger_crmentity.deleted=0
			' . Users_Privileges_Model::getNonAdminAccessControlQuery($this->getName()) . $ownerSql . ' ' . $dateFilterSql .
			' INNER JOIN vtiger_ticketstatus ON vtiger_troubletickets.status = vtiger_ticketstatus.ticketstatus 
			WHERE vtiger_troubletickets.status IN (' . generateQuestionMarks($picklistvaluesmap) . ') AND vtiger_troubletickets.ticket_type = ?
			GROUP BY statusvalue ORDER BY vtiger_ticketstatus.sortorderid', $params);
		$availablePicklist = array();
		$statusObject = [];
		$num_rows = $db->num_rows($result);
		for ($i = 0; $i < $num_rows; $i++) {
			$row = $db->query_result_rowdata($result, $i);
			$ticketStatusVal = $row['statusvalue'];
			if ($ticketStatusVal == '') {
				$ticketStatusVal = 'LBL_BLANK';
			}
			array_push($availablePicklist, $ticketStatusVal);
			$ticketStatusVal = str_replace(' ', '', $ticketStatusVal);
			$statusObject[$ticketStatusVal . 'Count'] = $row['count'];
		}
		
		$notAvailable = array_diff($picklistvaluesmap, $availablePicklist);
		foreach ($notAvailable as $picklist) {
			// $response[] = array('count' => '0','label' => vtranslate($picklist, 'HelpDesk'),'key' => $picklist);
			$ticketStatusVal = vtranslate($picklist, 'HelpDesk');
			$ticketStatusVal = str_replace(' ', '', $ticketStatusVal);
			$statusObject[$ticketStatusVal . 'Count'] = '0';
		}
		global $current_user;
		$data = getUserDetailsBasedOnEmployeeModuleInUtils($current_user->user_name);
		if ($data['cust_role'] == 'Service Engineer') {
			foreach ($statusObject as $statusObjectValKey => $statusObjectVal) {
				if ($statusObjectValKey == 'EngineerAssignedCount') {
					continue;
				}
				if($statusObjectValKey == 'OpenCount') {
					$statusObject[$statusObjectValKey] = strval((int) $statusObjectVal + (int) $statusObject['EngineerAssignedCount']);
				}
				
			}
			unset($statusObject['EngineerAssignedCount']);
			return $statusObject;
		} else {
			return $statusObject;
		}
	}

	public function getTicketsByStatusCountsForCustomer($owner, $dateFilter, $type, $contactId) {
		$db = PearDatabase::getInstance();
		$ownerSql = $this->getOwnerWhereConditionForDashBoards($owner);
		if (!empty($ownerSql)) {
			$ownerSql = ' AND ' . $ownerSql;
		}
		$params = array();
		if (!empty($dateFilter)) {
			$dateFilterSql = ' AND createdtime BETWEEN ? AND ? ';
			$params[] = $dateFilter['start'];
			$params[] = $dateFilter['end'];
		}
		$params[] = $contactId;
		$picklistvaluesmap = getAllPickListValues("ticketstatus");
		foreach ($picklistvaluesmap as $picklistValue) {
			$params[] = $picklistValue;
		}
		$params[] = $type;
		$result = $db->pquery('SELECT COUNT(*) as count, CASE WHEN vtiger_troubletickets.status IS NULL OR vtiger_troubletickets.status = "" THEN "" ELSE vtiger_troubletickets.status END AS statusvalue 
			FROM vtiger_troubletickets INNER JOIN vtiger_crmentity ON vtiger_troubletickets.ticketid = vtiger_crmentity.crmid AND vtiger_crmentity.deleted=0
			' . Users_Privileges_Model::getNonAdminAccessControlQuery($this->getName()) . $ownerSql . ' ' . $dateFilterSql .
			' INNER JOIN vtiger_ticketstatus ON vtiger_troubletickets.status = vtiger_ticketstatus.ticketstatus 
			WHERE vtiger_troubletickets.contact_id = ? and vtiger_troubletickets.status IN (' . generateQuestionMarks($picklistvaluesmap) . ') AND vtiger_troubletickets.ticket_type = ?
			GROUP BY statusvalue ORDER BY vtiger_ticketstatus.sortorderid', $params);
		$availablePicklist = array();
		$statusObject = [];
		$num_rows = $db->num_rows($result);
		for ($i = 0; $i < $num_rows; $i++) {
			$row = $db->query_result_rowdata($result, $i);
			$ticketStatusVal = $row['statusvalue'];
			if ($ticketStatusVal == '') {
				$ticketStatusVal = 'LBL_BLANK';
			}
			array_push($availablePicklist, $ticketStatusVal);
			$ticketStatusVal = str_replace(' ', '', $ticketStatusVal);
			$statusObject[$ticketStatusVal . 'Count'] = $row['count'];
		}
		
		$notAvailable = array_diff($picklistvaluesmap, $availablePicklist);
		foreach ($notAvailable as $picklist) {
			$ticketStatusVal = vtranslate($picklist, 'HelpDesk');
			$ticketStatusVal = str_replace(' ', '', $ticketStatusVal);
			$statusObject[$ticketStatusVal . 'Count'] = '0';
		}
		return $statusObject;
	}

	public function getTicketsByStatus($owner, $dateFilter) {
		$db = PearDatabase::getInstance();

		$ownerSql = $this->getOwnerWhereConditionForDashBoards($owner);
		if (!empty($ownerSql)) {
			$ownerSql = ' AND ' . $ownerSql;
		}

		$params = array();
		if (!empty($dateFilter)) {
			$dateFilterSql = ' AND createdtime BETWEEN ? AND ? ';
			//appended time frame and converted to db time zone in showwidget.php
			$params[] = $dateFilter['start'];
			$params[] = $dateFilter['end'];
		}
		$picklistvaluesmap = getAllPickListValues("ticketstatus");
		foreach ($picklistvaluesmap as $picklistValue) {
			$params[] = $picklistValue;
		}

		$result = $db->pquery('SELECT COUNT(*) as count, CASE WHEN vtiger_troubletickets.status IS NULL OR vtiger_troubletickets.status = "" THEN "" ELSE vtiger_troubletickets.status END AS statusvalue 
							FROM vtiger_troubletickets INNER JOIN vtiger_crmentity ON vtiger_troubletickets.ticketid = vtiger_crmentity.crmid AND vtiger_crmentity.deleted=0
							' . Users_Privileges_Model::getNonAdminAccessControlQuery($this->getName()) . $ownerSql . ' ' . $dateFilterSql .
			' INNER JOIN vtiger_ticketstatus ON vtiger_troubletickets.status = vtiger_ticketstatus.ticketstatus 
							WHERE vtiger_troubletickets.status IN (' . generateQuestionMarks($picklistvaluesmap) . ') 
							GROUP BY statusvalue ORDER BY vtiger_ticketstatus.sortorderid', $params);

		$response = array();

		for ($i = 0; $i < $db->num_rows($result); $i++) {
			$row = $db->query_result_rowdata($result, $i);
			$response[$i][0] = $row['count'];
			$ticketStatusVal = $row['statusvalue'];
			if ($ticketStatusVal == '') {
				$ticketStatusVal = 'LBL_BLANK';
			}
			$response[$i][1] = vtranslate($ticketStatusVal, $this->getName());
			$response[$i][2] = $ticketStatusVal;
		}
		return $response;
	}

	public function getTicketsByStatusPercentage($owner, $dateFilter) {
		$db = PearDatabase::getInstance();
		$ownerSql = $this->getOwnerWhereConditionForDashBoards($owner);
		if (!empty($ownerSql)) {
			$ownerSql = ' AND ' . $ownerSql;
		}
		$params = array();
		if (!empty($dateFilter)) {
			$dateFilterSql = ' AND createdtime BETWEEN ? AND ? ';
			$params[] = $dateFilter['start'];
			$params[] = $dateFilter['end'];
		}
		$picklistvaluesmap = getAllPickListValues("ticketstatus");
		$anothrParams = $params;
		foreach ($picklistvaluesmap as $picklistValue) {
			$anothrParams[] = $picklistValue;
		}
		$nonAdminQueryPecent = Users_Privileges_Model::getNonAdminAccessControlQuery($this->getName());
		$result = $db->pquery('SELECT COUNT(*) as count
			FROM vtiger_troubletickets INNER JOIN vtiger_crmentity ON vtiger_troubletickets.ticketid = vtiger_crmentity.crmid AND vtiger_crmentity.deleted=0
			' .  $nonAdminQueryPecent . $ownerSql . ' ' . $dateFilterSql .
			' ', $params);
		$dataRow = $db->fetchByAssoc($result, 0);
		$totalCount = 0;
		if (!empty($dataRow)) {
			$totalCount = $dataRow['count'];
		}

		$result = $db->pquery('SELECT COUNT(*) as count, CASE WHEN vtiger_troubletickets.status IS NULL OR vtiger_troubletickets.status = "" THEN "" ELSE vtiger_troubletickets.status END AS statusvalue 
			FROM vtiger_troubletickets INNER JOIN vtiger_crmentity ON vtiger_troubletickets.ticketid = vtiger_crmentity.crmid AND vtiger_crmentity.deleted=0
			' . $nonAdminQueryPecent . $ownerSql . ' ' . $dateFilterSql .
			' INNER JOIN vtiger_ticketstatus ON vtiger_troubletickets.status = vtiger_ticketstatus.ticketstatus 
			WHERE vtiger_troubletickets.status IN (' . generateQuestionMarks($picklistvaluesmap) . ')
			GROUP BY statusvalue ORDER BY vtiger_ticketstatus.sortorderid', $anothrParams);
		$availablePicklist = array();
		$statusObject = [];
		for ($i = 0; $i < $db->num_rows($result); $i++) {
			$statusObjectVal = [];
			$row = $db->query_result_rowdata($result, $i);
			$ticketStatusVal = $row['statusvalue'];
			if ($ticketStatusVal == '') {
				$ticketStatusVal = 'LBL_BLANK';
			}
			$statusObjectVal['label'] = $ticketStatusVal;
			$ticketStatusVal = str_replace(' ', '_', $ticketStatusVal);
			$statusObjectVal['key'] = $ticketStatusVal;
			$statusObjectVal['count'] = $row['count'];
			if ($totalCount == 0) {
				$statusObjectVal['percent'] = 0;
			} else {
				$statusObjectVal['percent'] = ($row['count'] / $totalCount) * 100;
			}

			array_push($statusObject, $statusObjectVal);
			array_push($availablePicklist, $statusObjectVal['label']);
		}
		$notAvailable = array_diff($picklistvaluesmap, $availablePicklist);
		foreach ($notAvailable as $picklist) {
			$statusObjectVal = [];
			$ticketStatusVal = vtranslate($picklist, 'HelpDesk');
			$statusObjectVal['label'] = $ticketStatusVal;
			$ticketStatusVal = str_replace(' ', '', $ticketStatusVal);
			$statusObjectVal['key'] = $ticketStatusVal;
			if ($totalCount == 0) {
				$statusObjectVal['percent'] = 0;
			} else {
				$statusObjectVal['percent'] = (0 / $totalCount) * 100;
			}
			$statusObjectVal['count'] = '0';
			array_push($statusObject, $statusObjectVal);
		}
		global $current_user;
		$data = getUserDetailsBasedOnEmployeeModuleInUtils($current_user->user_name);
		if ($data['cust_role'] == 'Service Engineer') {
			$engineerObject = [];
			foreach ($statusObject as $statusObjectVal) {
				if ($statusObjectVal['key'] == 'Engineer_Assigned') {
					$engineerObject = $statusObjectVal;
					break;
				}
			}
			$alteredStatusObject = [];
			foreach ($statusObject as $statusObjectVal) {
				if ($statusObjectVal['key'] == 'Open') {
					$statusObjectVal['count'] = (float)$statusObjectVal['count'] + (float)$engineerObject['count'];
					$statusObjectVal['percent'] = (float)$statusObjectVal['percent'] + (float)$engineerObject['percent'];
				}
				if ($statusObjectVal['key'] == 'Engineer_Assigned') {
					continue;
				}
				array_push($alteredStatusObject, $statusObjectVal);
			}
			return $alteredStatusObject;
		} else {
			return $statusObject;
		}
	}

	public function getTicketsByType($owner, $dateFilter, $contactId) {
		$db = PearDatabase::getInstance();
		$ownerSql = $this->getOwnerWhereConditionForDashBoards($owner);
		if (!empty($ownerSql)) {
			$ownerSql = ' AND ' . $ownerSql;
		}
		$params = array();
		if (!empty($dateFilter)) {
			$dateFilterSql = ' AND createdtime BETWEEN ? AND ? ';
			$params[] = $dateFilter['start'];
			$params[] = $dateFilter['end'];
		}
		$params[] = $contactId;
		$picklistvaluesmap = getAllPickListValues("ticket_type");
		foreach ($picklistvaluesmap as $picklistValue) {
			$params[] = $picklistValue;
		}
		$result = $db->pquery('SELECT COUNT(*) as count, CASE WHEN vtiger_troubletickets.ticket_type IS NULL OR vtiger_troubletickets.ticket_type = "" THEN "" ELSE vtiger_troubletickets.ticket_type END AS statusvalue 
							FROM vtiger_troubletickets INNER JOIN vtiger_crmentity ON vtiger_troubletickets.ticketid = vtiger_crmentity.crmid AND vtiger_crmentity.deleted=0
							' . Users_Privileges_Model::getNonAdminAccessControlQuery($this->getName()) . $ownerSql . ' ' . $dateFilterSql .
			' INNER JOIN vtiger_ticket_type ON vtiger_troubletickets.ticket_type = vtiger_ticket_type.ticket_type 
							WHERE vtiger_troubletickets.contact_id = ? AND vtiger_troubletickets.ticket_type IN (' . generateQuestionMarks($picklistvaluesmap) . ')
							GROUP BY statusvalue ORDER BY vtiger_ticket_type.sortorderid', $params);
		$response = array();
		$availablePicklist = array();
		$noOfRows = $db->num_rows($result);
		for ($i = 0; $i < $noOfRows; $i++) {
			$row = $db->query_result_rowdata($result, $i);
			$response[$i][0] = $row['count'];
			$ticketStatusVal = $row['statusvalue'];
			if ($ticketStatusVal == '') {
				$ticketStatusVal = 'LBL_BLANK';
			}
			$response[$i][1] = vtranslate($ticketStatusVal, 'HelpDesk');
			$response[$i][2] = $ticketStatusVal;
			array_push($availablePicklist, $ticketStatusVal);
		}
		$notAvailable = array_diff($picklistvaluesmap, $availablePicklist);
		unset($notAvailable['DESIGN MODIFICATION']);
		unset($notAvailable['PRE-DELIVERY']);
		foreach ($notAvailable as $picklist) {
			$response[] = array(0, vtranslate($picklist, 'HelpDesk'), $picklist);
		}
		return $response;
	}

	public function getTicketsByTypeMobile($owner, $dateFilter, $contactId) {
		$db = PearDatabase::getInstance();
		$ownerSql = $this->getOwnerWhereConditionForDashBoards($owner);
		if (!empty($ownerSql)) {
			$ownerSql = ' AND ' . $ownerSql;
		}
		$params = array();
		if (!empty($dateFilter)) {
			$dateFilterSql = ' AND createdtime BETWEEN ? AND ? ';
			$params[] = $dateFilter['start'];
			$params[] = $dateFilter['end'];
		}
		$params[] = $contactId;
		$picklistvaluesmap = getAllPickListValues("ticket_type");
		foreach ($picklistvaluesmap as $picklistValue) {
			$params[] = $picklistValue;
		}
		$result = $db->pquery('SELECT COUNT(*) as count, CASE WHEN vtiger_troubletickets.ticket_type IS NULL OR vtiger_troubletickets.ticket_type = "" THEN "" ELSE vtiger_troubletickets.ticket_type END AS statusvalue 
							FROM vtiger_troubletickets INNER JOIN vtiger_crmentity ON vtiger_troubletickets.ticketid = vtiger_crmentity.crmid AND vtiger_crmentity.deleted=0
							' . Users_Privileges_Model::getNonAdminAccessControlQuery($this->getName()) . $ownerSql . ' ' . $dateFilterSql .
			' INNER JOIN vtiger_ticket_type ON vtiger_troubletickets.ticket_type = vtiger_ticket_type.ticket_type 
							WHERE vtiger_troubletickets.contact_id = ? AND vtiger_troubletickets.ticket_type IN (' . generateQuestionMarks($picklistvaluesmap) . ')
							GROUP BY statusvalue ORDER BY vtiger_ticket_type.sortorderid', $params);
		$response = array();
		$availablePicklist = array();
		$noOfRows = $db->num_rows($result);
		for ($i = 0; $i < $noOfRows; $i++) {
			$row = $db->query_result_rowdata($result, $i);
			$rowData = [];
			$rowData['count'] = $row['count'];
			$ticketStatusVal = $row['statusvalue'];
			if ($ticketStatusVal == '') {
				$ticketStatusVal = 'LBL_BLANK';
			}
			$rowData['key'] = $ticketStatusVal;
			$rowData['label'] = vtranslate($ticketStatusVal, 'HelpDesk');
			array_push($response, $rowData);
			array_push($availablePicklist, $ticketStatusVal);
		}
		$notAvailable = array_diff($picklistvaluesmap, $availablePicklist);
		unset($notAvailable['DESIGN MODIFICATION']);
		unset($notAvailable['PRE-DELIVERY']);
		foreach ($notAvailable as $picklist) {
			$response[] = array('count' => '0', 'label' => vtranslate($picklist, 'HelpDesk'), 'key' => $picklist);
		}
		return $response;
	}

	public function getTicketsByTypeUser($owner, $dateFilter) {
		$db = PearDatabase::getInstance();
		$ownerSql = $this->getOwnerWhereConditionForDashBoards($owner);
		if (!empty($ownerSql)) {
			$ownerSql = ' AND ' . $ownerSql;
		}
		$params = array();
		if (!empty($dateFilter)) {
			$dateFilterSql = ' AND createdtime BETWEEN ? AND ? ';
			$params[] = $dateFilter['start'];
			$params[] = $dateFilter['end'];
		}
		$picklistvaluesmap = getAllPickListValues("ticket_type");
		foreach ($picklistvaluesmap as $picklistValue) {
			$params[] = $picklistValue;
		}
		$result = $db->pquery('SELECT COUNT(*) as count, CASE WHEN vtiger_troubletickets.ticket_type IS NULL OR vtiger_troubletickets.ticket_type = "" THEN "" ELSE vtiger_troubletickets.ticket_type END AS statusvalue 
							FROM vtiger_troubletickets INNER JOIN vtiger_crmentity ON vtiger_troubletickets.ticketid = vtiger_crmentity.crmid AND vtiger_crmentity.deleted=0
							' . Users_Privileges_Model::getNonAdminAccessControlQuery($this->getName()) . $ownerSql . ' ' . $dateFilterSql .
			' INNER JOIN vtiger_ticket_type ON vtiger_troubletickets.ticket_type = vtiger_ticket_type.ticket_type 
							WHERE vtiger_troubletickets.ticket_type IN (' . generateQuestionMarks($picklistvaluesmap) . ')
							GROUP BY statusvalue ORDER BY vtiger_ticket_type.sortorderid', $params);
		$response = array();
		$availablePicklist = array();
		$noOfRows = $db->num_rows($result);
		for ($i = 0; $i < $noOfRows; $i++) {
			$row = $db->query_result_rowdata($result, $i);
			$rowData = [];
			$rowData['count'] = $row['count'];
			$ticketStatusVal = $row['statusvalue'];
			if ($ticketStatusVal == '') {
				$ticketStatusVal = 'LBL_BLANK';
			}
			$rowData['key'] = $ticketStatusVal;
			$rowData['label'] = vtranslate($ticketStatusVal, 'HelpDesk');
			array_push($response, $rowData);
			array_push($availablePicklist, $ticketStatusVal);
		}
		$notAvailable = array_diff($picklistvaluesmap, $availablePicklist);
		foreach ($notAvailable as $picklist) {
			$response[] = array('count' => '0', 'label' => vtranslate($picklist, 'HelpDesk'), 'key' => $picklist);
		}
		return $response;
	}

	/**
	 * Function to get relation query for particular module with function name
	 * @param <record> $recordId
	 * @param <String> $functionName
	 * @param Vtiger_Module_Model $relatedModule
	 * @return <String>
	 */
	public function getRelationQuery($recordId, $functionName, $relatedModule, $relationId) {
		if ($functionName === 'get_activities') {
			$userNameSql = getSqlForNameInDisplayFormat(array('first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'), 'Users');

			$query = "SELECT CASE WHEN (vtiger_users.user_name not like '') THEN $userNameSql ELSE vtiger_groups.groupname END AS user_name,
						vtiger_crmentity.*, vtiger_activity.activitytype, vtiger_activity.subject, vtiger_activity.date_start, vtiger_activity.time_start,
						vtiger_activity.recurringtype, vtiger_activity.due_date, vtiger_activity.time_end, vtiger_activity.visibility, vtiger_seactivityrel.crmid AS parent_id,
						CASE WHEN (vtiger_activity.activitytype = 'Task') THEN (vtiger_activity.status) ELSE (vtiger_activity.eventstatus) END AS status
						FROM vtiger_activity
						INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_activity.activityid
						LEFT JOIN vtiger_seactivityrel ON vtiger_seactivityrel.activityid = vtiger_activity.activityid
						LEFT JOIN vtiger_cntactivityrel ON vtiger_cntactivityrel.activityid = vtiger_activity.activityid
						LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid
						LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid
							WHERE vtiger_crmentity.deleted = 0 AND vtiger_activity.activitytype <> 'Emails'
								AND vtiger_seactivityrel.crmid = " . $recordId;

			$relatedModuleName = $relatedModule->getName();
			$query .= $this->getSpecificRelationQuery($relatedModuleName);
			$nonAdminQuery = $this->getNonAdminAccessControlQueryForRelation($relatedModuleName);
			if ($nonAdminQuery) {
				$query = appendFromClauseToQuery($query, $nonAdminQuery);

				if (trim($nonAdminQuery)) {
					$relModuleFocus = CRMEntity::getInstance($relatedModuleName);
					$condition = $relModuleFocus->buildWhereClauseConditionForCalendar();
					if ($condition) {
						$query .= ' AND ' . $condition;
					}
				}
			}
		} else {
			$query = parent::getRelationQuery($recordId, $functionName, $relatedModule, $relationId);
		}

		return $query;
	}

	/**
	 * Function to get list view query for popup window
	 * @param <String> $sourceModule Parent module
	 * @param <String> $field parent fieldname
	 * @param <Integer> $record parent id
	 * @param <String> $listQuery
	 * @return <String> Listview Query
	 */
	public function getQueryByModuleField($sourceModule, $field, $record, $listQuery) {
		if (in_array($sourceModule, array('Assets', 'Project', 'ServiceContracts', 'Services'))) {
			$condition = " vtiger_troubletickets.ticketid NOT IN (SELECT relcrmid FROM vtiger_crmentityrel WHERE crmid = ? UNION SELECT crmid FROM vtiger_crmentityrel WHERE relcrmid = ?) ";
			$db = PearDatabase::getInstance();
			$condition = $db->convert2Sql($condition, array($record, $record));
			$pos = stripos($listQuery, 'where');

			if ($pos) {
				$split = preg_split('/where/i', $listQuery);
				$overRideQuery = $split[0] . ' WHERE ' . $split[1] . ' AND ' . $condition;
			} else {
				$overRideQuery = $listQuery . ' WHERE ' . $condition;
			}
			return $overRideQuery;
		}
	}

	/**
	 * Function to get list of field for header view
	 * @return <Array> list of field models <Vtiger_Field_Model>
	 */
	function getConfigureRelatedListFields() {
		$summaryViewFields = $this->getSummaryViewFieldsList();
		$headerViewFields = $this->getHeaderViewFieldsList();
		$allRelationListViewFields = array_merge($headerViewFields, $summaryViewFields);
		$relatedListFields = array();
		if (count($allRelationListViewFields) > 0) {
			foreach ($allRelationListViewFields as $key => $field) {
				$relatedListFields[$field->get('column')] = $field->get('name');
			}
		}

		if (count($relatedListFields) > 0) {
			$nameFields = $this->getNameFields();
			foreach ($nameFields as $fieldName) {
				if (!$relatedListFields[$fieldName]) {
					$fieldModel = $this->getField($fieldName);
					$relatedListFields[$fieldModel->get('column')] = $fieldModel->get('name');
				}
			}
		}

		return $relatedListFields;
	}
}
