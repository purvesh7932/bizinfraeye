<?php
function CalculateContractAndWarrantyForAll($entityData) {
	$db = PearDatabase::getInstance();
	$query = "SELECT vtiger_equipment.equipmentid , equip_war_terms , eq_run_war_st,
				cust_begin_guar, eq_last_hmr, cont_start_date , cont_end_date
				FROM `vtiger_equipment`
				INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_equipment.equipmentid
				INNER JOIN vtiger_equipmentcf ON vtiger_equipmentcf.equipmentid = vtiger_equipment.equipmentid
				WHERE equip_war_terms != '' and vtiger_crmentity.`deleted` = 0";
	$result = $db->pquery($query);
	while ($row = $db->fetchByAssoc($result)) {
		IgCalculateWarranty(
			$row["equipmentid"],
			$row["equip_war_terms"],
			$row['eq_last_hmr'],
			$row['cust_begin_guar'],
			$row['eq_run_war_st']
		);
		CalculateContract(
			$row['eq_run_war_st'],
			$row["equipmentid"],
			$row['cont_start_date'],
			$row['cont_end_date'],
		);
	}
}
