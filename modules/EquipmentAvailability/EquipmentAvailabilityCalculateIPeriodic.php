<?php
include_once('include/utils/GeneralConfigUtils.php');
function EquipmentAvailabilityCalculateIPeriodic($entityData) {
	die("cammmmdjjd ");
	$db = PearDatabase::getInstance();

	$sqlForGettingAll = 'SELECT * FROM `vtiger_equipment` where eq_available = ?';
	$result = $db->pquery($sqlForGettingAll, array('Aplicable'));
	while ($rowDataOfParent = $db->fetchByAssoc($result)) {
		$commitedAvailability = $rowDataOfParent['eq_commited_avl'];
		$id = $rowDataOfParent['equipmentid'];
		if ($commitedAvailability == 'Different availability applicable during contract period') {
			$runningYearOfContract = $rowDataOfParent['run_year_cont'];
			$query = "SELECT equipmentid,eq_run_war_st,shift_hours,run_year_cont,daadcp_avail_mon_percent,
				shift_hours,maint_h_app_for_ac, eq_mon_available
			FROM vtiger_equipment 
			INNER JOIN vtiger_crmentity 
			on vtiger_crmentity.crmid = vtiger_equipment.equipmentid 
			INNER JOIN vtiger_inventoryproductrel_equipment 
			on vtiger_inventoryproductrel_equipment.id = vtiger_equipment.equipmentid  
			where equipmentid = ? and daadcp_avail_sl_no = ? and eq_run_war_st IN('Contract', 'Under Warranty') 
			and eq_available = 'Aplicable' and vtiger_crmentity.deleted = 0";
			$result = $db->pquery($query, array($id, $runningYearOfContract));
			while ($row = $db->fetchByAssoc($result)) {
				CheckAndCreateAvailability($row["equipmentid"],  $row);
			}
		} else if ($commitedAvailability == 'Availability For Warranty Period') {
			$query = "SELECT equipmentid,eq_run_war_st,awp_commited_avl_y,
				awp_commited_avl_m,
				shift_hours,run_year_cont,daadcp_avail_mon_percent,
				shift_hours,maint_h_app_for_ac, eq_mon_available
				FROM vtiger_equipment 
				INNER JOIN vtiger_crmentity 
				on vtiger_crmentity.crmid = vtiger_equipment.equipmentid 
				INNER JOIN vtiger_inventoryproductrel_equipment 
				on vtiger_inventoryproductrel_equipment.id = vtiger_equipment.equipmentid  
				where equipmentid = ? and eq_run_war_st IN('Contract', 'Under Warranty') 
				and eq_available = 'Aplicable' and vtiger_crmentity.deleted = 0";
			$result = $db->pquery($query, array($id));
			while ($row = $db->fetchByAssoc($result)) {
				$row['commited_avl_y'] = $row['awp_commited_avl_y'];
				$row['commited_avl_m_w'] = $row['awp_commited_avl_m'];
				CheckAndCreateAvailability($row["equipmentid"],  $row);
			}
		} else if ($commitedAvailability == 'Availability for both Warranty & Contract Period are Same') {
			$query = "SELECT equipmentid,eq_run_war_st,afbwcpas_commited_avl_y,
				afbwcpas_commited_avl_m,
				shift_hours,run_year_cont,daadcp_avail_mon_percent,
				shift_hours,maint_h_app_for_ac, eq_mon_available
				FROM vtiger_equipment 
				INNER JOIN vtiger_crmentity 
				on vtiger_crmentity.crmid = vtiger_equipment.equipmentid 
				INNER JOIN vtiger_inventoryproductrel_equipment 
				on vtiger_inventoryproductrel_equipment.id = vtiger_equipment.equipmentid  
				where equipmentid = ? and eq_run_war_st IN('Contract', 'Under Warranty') 
				and eq_available = 'Aplicable' and vtiger_crmentity.deleted = 0";
			$result = $db->pquery($query, array($id));
			while ($row = $db->fetchByAssoc($result)) {
				$row['commited_avl_y'] = $row['afbwcpas_commited_avl_y'];
				$row['commited_avl_m_w'] = $row['afbwcpas_commited_avl_m'];
				CheckAndCreateAvailability($row["equipmentid"],  $row);
			}
		} else if ($commitedAvailability == 'Same availability applicable through out contract period') {
			$query = "SELECT equipmentid,eq_run_war_st,saatocp_commited_avl_y_w,
				saatocp_commited_avl_m_w,saatocp_commited_avl_y_c,saatocp_commited_avl_m_c,
				shift_hours,run_year_cont,daadcp_avail_mon_percent,
				shift_hours,maint_h_app_for_ac, eq_mon_available
				FROM vtiger_equipment 
				INNER JOIN vtiger_crmentity 
				on vtiger_crmentity.crmid = vtiger_equipment.equipmentid 
				INNER JOIN vtiger_inventoryproductrel_equipment 
				on vtiger_inventoryproductrel_equipment.id = vtiger_equipment.equipmentid  
				where equipmentid = ? and eq_run_war_st IN('Contract', 'Under Warranty') 
				and eq_available = 'Aplicable' and vtiger_crmentity.deleted = 0";
			$result = $db->pquery($query, array($id));
			while ($row = $db->fetchByAssoc($result)) {
				$warrantyStatus = $row['eq_run_war_st'];
				if ($warrantyStatus == 'Under Warranty') {
					$row['commited_avl_y'] = $row['saatocp_commited_avl_y_w'];
					$row['commited_avl_m_w'] = $row['saatocp_commited_avl_m_w'];
				} else if ($warrantyStatus == 'Contract') {
					$row['commited_avl_y'] = $row['saatocp_commited_avl_y_c'];
					$row['commited_avl_m_w'] = $row['saatocp_commited_avl_m_c'];
				}
				CheckAndCreateAvailability($row["equipmentid"],  $row);
			}
		}
	}
}
function CheckAndCreateAvailability($id, $row) {
	$row['type_of_eq_availability'] = 'Year';
	calculateEquipmentAvailabilty($id, date("Y"), date("Y-m-d"), $row);
	if ($row['eq_mon_available'] == 'Aplicable') {
		$row['type_of_eq_availability'] = 'Month';
		calculateEquipmentAvailabilty($id, date("Y-m"), date("Y-m-d"), $row);
	}
}
