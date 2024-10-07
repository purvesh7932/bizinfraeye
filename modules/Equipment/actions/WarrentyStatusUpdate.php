<?php
class Equipment_WarrentyStatusUpdate_Action extends Vtiger_IndexAjax_View {
	public function process(Vtiger_Request $request) {
		$response = new Vtiger_Response();
		$db = PearDatabase::getInstance();
		$query = "SELECT vtiger_equipment.equipmentid , equip_war_terms , eq_run_war_st, cust_begin_guar, eq_last_hmr
					FROM `vtiger_equipment`
					INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_equipment.equipmentid
					INNER JOIN vtiger_equipmentcf ON vtiger_equipmentcf.equipmentid = vtiger_equipment.equipmentid
					WHERE equip_war_terms != '' and vtiger_crmentity.`deleted` = 0";
		$result = $db->pquery($query);
		while ($row = $db->fetchByAssoc($result)) {
			$this->CalculateWarranty($row["equipmentid"], $row["equip_war_terms"], $row['eq_last_hmr'], $row['cust_begin_guar'], $row['eq_run_war_st']);
		}
		$response->setResult(array('success' => true));
		$response->emit();
	}

	public function getMonthAndHMRBasedOnWarranty($warantyText, $id) {
		global $adb;
		$sql = "select * from `vtiger_warrantydetails`
			INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_warrantydetails.warrantydetailsid
		 	where wr_warranty_description = ? and vtiger_crmentity.deleted = 0";
		$sqlResult = $adb->pquery($sql, array(decode_html($warantyText)));
		$num_rows = $adb->num_rows($sqlResult);

		if ($num_rows > 0) {
			$dataRow = $adb->fetchByAssoc($sqlResult, 0);
			$month = $dataRow['wr_warranty_in_mon'];
			$warrantyHours = $dataRow['wr_waranty_hours'];
			return array("month" => $month, "hmr" => $warrantyHours);
		} else {
			return [];
		}
	}

	public function CalculateWarranty($id, $text, $currhmr, $warrentyStartDate, $currStatus) {
		$dataValues = $this->getMonthAndHMRBasedOnWarranty($text, $id);
		if (!empty($dataValues["month"]) && !empty($warrentyStartDate)) {
			$warrentyStartDate = date_create($warrentyStartDate);
			if (!empty($dataValues["month"])) {
				$warrentyEndDate  = $warrentyStartDate->modify("+" . $dataValues["month"] . ' month');
			}
			$warrentyEndDate = date_format($warrentyEndDate, "Y-m-d");
			if ($warrentyEndDate > date("Y-m-d")) {
				$currStatus = "Under Warranty";
				$this->valueUpdateDb($currStatus, $id, $warrentyEndDate);
			} else {
				$currStatus = "Outside Warranty";
				$this->valueUpdateDb($currStatus, $id, $warrentyEndDate);
			}
		}

		if (!empty($dataValues["hmr"])) {
			if (intval($dataValues["hmr"]) < intval($currhmr)) {
				$currStatus = "Outside Warranty";
				$this->valueUpdateDb($currStatus, $id, $warrentyEndDate);
			}
			if (empty($dataValues["month"]) && empty($dataValues["days"]) && empty($dataValues["year"]) && intval($dataValues["hmr"]) > intval($currhmr)) {
				$currStatus = "Under Warranty";
				$this->valueUpdateDb($currStatus, $id, $warrentyEndDate);
			}
		}
	}

	public function valueSet($id, $text, $currhmr, $warrentyStartDate, $currStatus) {
		$dataValues = $this->valueFinder($text);
		if (!empty($dataValues["days"]) || !empty($dataValues["month"]) || !empty($dataValues["year"])) {
			$warrentyStartDate = date_create($warrentyStartDate);
			if (!empty($dataValues["month"])) {
				$warrentyEndDate  = $warrentyStartDate->modify("+" . $dataValues["month"] . ' month');
			} else if (!empty($dataValues["year"])) {
				$warrentyEndDate  = $warrentyStartDate->modify("+" . $dataValues["year"] . ' year');
			} else if (!empty($dataValues["days"])) {
				$warrentyEndDate  = $warrentyStartDate->modify("+" . $dataValues["days"] . ' day');
			}
			$warrentyEndDate = date_format($warrentyEndDate, "Y-m-d");
			if ($warrentyEndDate > date("Y-m-d")) {
				$currStatus = "Under Warranty";
				$this->valueUpdateDb($currStatus, $id);
			} else {
				$currStatus = "Outside Warranty";
				$this->valueUpdateDb($currStatus, $id);
			}
			//echo $warrentyEndDate." ". $currStatus.'</br>';
		}

		if (!empty($dataValues["hmr"])) {
			if (intval($dataValues["hmr"]) < intval($currhmr)) {
				$currStatus = "Outside Warranty";
				$this->valueUpdateDb($currStatus, $id);
			}
			if (empty($dataValues["month"]) && empty($dataValues["days"]) && empty($dataValues["year"]) && intval($dataValues["hmr"]) > intval($currhmr)) {
				$currStatus = "Under Warranty";
				$this->valueUpdateDb($currStatus, $id);
			}
		}
	}

	public function valueUpdateDb($currStatus, $id, $warrantyEndDate) {
		$db = PearDatabase::getInstance();
		$db->pquery("UPDATE vtiger_equipment set eq_run_war_st=? , cust_war_end = ?
		 where equipmentid=?", array($currStatus, $warrantyEndDate, $id));
	}

	public function valueFinder($text) {
		$text = strtolower($text);
		$textArray = explode(" ", $text);
		$hmrArray    = ['hours', 'hrs'];
		$daysArray   = ["days"];
		$monthsArray = ["months", "monts", "month"];
		$yearsArray  = ["year", "yrs", "years"];

		$hmrValue    = $this->loopValueFinder($textArray, $hmrArray);
		$dayValue    = $this->loopValueFinder($textArray, $daysArray);
		$monthValue  = $this->loopValueFinder($textArray, $monthsArray);
		$yearValue   = $this->loopValueFinder($textArray, $yearsArray);
		return array(
			"hmr"   => $hmrValue,
			"days"  => $dayValue,
			"month" => $monthValue,
			"year"  => $yearValue
		);
	}

	public function loopValueFinder($textArray, $findArray) {
		$length_txt  = count($textArray);
		$length_find = count($findArray);
		for ($i = 0; $i < $length_txt; $i++) {
			for ($j = 0; $j < $length_find; $j++) {
				if (strcmp($textArray[$i], $findArray[$j]) == 0) {
					return $textArray[$i - 1];
				}
			}
		}
	}
}
