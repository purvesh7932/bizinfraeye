<?php
 include_once('include/utils/GeneralConfigUtils.php');
class Equipment_CalcAvail_Action extends Vtiger_IndexAjax_View {

	public function process(Vtiger_Request $request) {
		$response = new Vtiger_Response();
		$db = PearDatabase::getInstance();
		$query = "SELECT equipmentid,eq_run_war_st,cont_start_date,cont_end_date 
		FROM vtiger_equipment INNER JOIN vtiger_crmentity 
		on vtiger_crmentity.crmid = vtiger_equipment.equipmentid  
		where equip_category = 'S' and eq_run_war_st IN('Contract', 'Under Warranty') 
		and eq_available = 'Aplicable' and vtiger_crmentity.deleted = 0";
		$result = $db->pquery($query);
		while ($row = $db->fetchByAssoc($result)) {
			$this->CheckAndCreateAvailability($row["eq_run_war_st"], $row["equipmentid"], $row["cont_start_date"], $row['cont_end_date']);
			die();
		}
		$response->setResult(array('success' => true));
		$response->emit();
	}
	
	public function CheckAndCreateAvailability($lastStatus, $id, $start, $end) {
		calculateEquipmentAvailabilty($id, date("Y-m-d"),date("Y-m-d"));
	}

	public function InsertDatabase($id, $contractYear, $totalYearOfContrcat, $contractStatus) {
		$db = PearDatabase::getInstance();
		$db->pquery(
			"UPDATE vtiger_equipment set run_year_cont = ? , eq_run_war_st = ?, total_year_cont= ?
			  where equipmentid=?",
			array($contractYear, $contractStatus, $totalYearOfContrcat, $id)
		);
	}
}
