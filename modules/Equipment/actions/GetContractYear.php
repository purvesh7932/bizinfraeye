<?php

class Equipment_GetContractYear_Action extends Vtiger_IndexAjax_View {
	public function process(Vtiger_Request $request) {
		$response = new Vtiger_Response();
		$db = PearDatabase::getInstance();
		$query = "SELECT equipmentid,eq_run_war_st,cont_start_date,cont_end_date FROM vtiger_equipment";
		$result = $db->pquery($query);
		while ($row = $db->fetchByAssoc($result)) {
			$this->CalculateContract($row["eq_run_war_st"], $row["equipmentid"], $row["cont_start_date"], $row['cont_end_date']);
		}
		$response->setResult(array('success' => true));
		$response->emit();
	}
	public function CalculateContract($lastStatus, $id, $start, $end) {

		if (!empty($start) && !empty($end)) {
			$start = date_create($start);
			$end = date_create($end);
			$today     = new DateTime();
			$contractYear = "";
			$contractMonth = "";

			$totalYearOfContrcat = "";
			if ($today > $end) {
				$interval =  date_diff($start, $end);
				$contractYear = ""; //$interval->format('%y');
				$contractMonth = -1; //$interval->format('%m');
			} else {
				$interval = date_diff($start, $today);
				$contractYear = $interval->format('%y');
				$contractMonth = $interval->format('%m');
				$lastStatus = "Contract";
			}
			$totalDiff = date_diff($start, $end);
			$totalYearOfContrcat = $totalDiff->format('%y');
			$totalYearOfContrcatMonth = $totalDiff->format('%m');
			if ($totalYearOfContrcatMonth > 0) {
				$totalYearOfContrcat = $totalYearOfContrcat + 1;
			}
			if ($contractMonth > 0) {
				$contractYear = $contractYear + 1;
			}
			$this->InsertDatabase($id, $contractYear, $totalYearOfContrcat, $lastStatus);
		}
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
