<?php

class BankGuarantee_Module_Model extends Vtiger_Module_Model {

	public function BGValuesStatusWise($owner, $dateFilter) {
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
		$picklistvaluesmap = getAllPickListValues("bnk_pre_status");
		foreach ($picklistvaluesmap as $picklistValue) {
			$params[] = $picklistValue;
		}

		require_once('include/utils/GeneralUtils.php');
		$listQuery = '';
		global $current_user;
		$data = getUserDetailsBasedOnEmployeeModuleG($current_user->user_name);
		if (empty($data)) {
		} else {
			if (($data['cust_role'] == 'Service Manager' &&
			($data['sub_service_manager_role'] == 'Regional Manager'
			|| $data['sub_service_manager_role'] == 'Regional Service Manager'))) {
			} else {
				$functionalLocations = getAllAssociatedFunctionalLocationsSE('36x' . $data['serviceengineerid']);
				if (empty($functionalLocations)) {
					$listQuery = $listQuery . ' and 1 = 2 ';
				} else {
					$listQuery = $listQuery . ' AND vtiger_bankguarantee.functional_loc IN ("' . implode('","', $functionalLocations) . '")';
				}
			}
		}

		$sql = 'SELECT sum(bg_val) as bg_val, COUNT(1) as number_of_bg, CASE WHEN vtiger_bankguarantee.bnk_pre_status IS NULL OR vtiger_bankguarantee.bnk_pre_status = "" THEN "" ELSE vtiger_bankguarantee.bnk_pre_status END AS statusvalue 
		FROM vtiger_bankguarantee INNER JOIN vtiger_crmentity ON vtiger_bankguarantee.bankguaranteeid = vtiger_crmentity.crmid AND vtiger_crmentity.deleted=0
		' . $listQuery . ' ' . $dateFilterSql .
		' INNER JOIN vtiger_bnk_pre_status ON vtiger_bankguarantee.bnk_pre_status = vtiger_bnk_pre_status.bnk_pre_status 
		WHERE vtiger_bankguarantee.bnk_pre_status IN (' . generateQuestionMarks($picklistvaluesmap) . ')
		GROUP BY statusvalue ORDER BY vtiger_bnk_pre_status.sortorderid';
		$result = $db->pquery($sql, $params);
		$response = array();
		$availablePicklist = array();
		$noOfRows = $db->num_rows($result);
		for ($i = 0; $i < $noOfRows; $i++) {
			$row = $db->query_result_rowdata($result, $i);
			$rowData = [];
			$rowData['bg_val'] = $row['bg_val'];
			$rowData['number_of_bg'] = $row['number_of_bg'];
			$ticketStatusVal = $row['statusvalue'];
			if ($ticketStatusVal == '') {
				$ticketStatusVal = 'LBL_BLANK';
			}
			$rowData['key'] = $ticketStatusVal;
			$rowData['label'] = vtranslate($ticketStatusVal, 'BankGuarantee');
			array_push($response, $rowData);
			array_push($availablePicklist, $ticketStatusVal);
		}
		$notAvailable = array_diff($picklistvaluesmap, $availablePicklist);
		foreach ($notAvailable as $picklist) {
			$response[] = array('number_of_bg' => '0','bg_val' => '0', 'label' => vtranslate($picklist, 'BankGuarantee'), 'key' => $picklist);
		}
		return $response;
	}

	public function getModuleBasicLinks() {
        return array();
    }
}
