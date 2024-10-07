<?php
function AutoUpdateBGStatusINPeriodic() {
	$db = PearDatabase::getInstance();
	$query = "SELECT * FROM `vtiger_bankguarantee`
				INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_bankguarantee.bankguaranteeid
				WHERE vtiger_crmentity.`deleted` = 0";
	$result = $db->pquery($query);
	while ($row = $db->fetchByAssoc($result)) {
		CalculateBGStatusINPeriodic($row);
	}
}

function CalculateBGStatusINPeriodic($data) {
	global $adb;
	$entityId = $data['bankguaranteeid'];
	$status = $data['bnk_pre_status'];
	$IVsd = $data['bg_initial_vl_st_date'];
	$IVed = $data['bg_initial_vl_en_date'];
	$EVsd = $data['ex_val_start_d'];
	$EVed = $data['ex_val_end_d'];
	$ICsd = $data['bg_initial_cl_st_date'];
	$ICed = $data['bg_initial_cl_end_date'];
	$ECsd = $data['bg_extended_cl_st_date'];
	$ECed = $data['bg_extended_cl_end_date'];

	$todayDate = date('Y-m-d');
	$todayDate = date('Y-m-d', strtotime($todayDate));

	//Update Live status
	if (!empty($IVsd) && !empty($IVed)) {
		$IVDateBegin = date('Y-m-d', strtotime($IVsd));
		$IVDateEnd = date('Y-m-d', strtotime($IVed));
		if (($todayDate >= $IVDateBegin) && ($todayDate <= $IVDateEnd)) {
			$updatestatus = 'Live';
		}
	}

	//Update Extended status
	if (!empty($EVsd) && !empty($EVed)) {
		$EVDateBegin = date('Y-m-d', strtotime($EVsd));
		$EVDateEnd = date('Y-m-d', strtotime($EVed));
		if (($todayDate >= $EVDateBegin) && ($todayDate <= $EVDateEnd)) {
			$updatestatus = 'Extended';
		}
	}

	//Update Initial Claim Period expired status
	if (!empty($ICsd) && !empty($ICed)) {
		$ICDateBegin = date('Y-m-d', strtotime($ICsd));
		$ICDateEnd = date('Y-m-d', strtotime($ICed));
		if (($todayDate > $EVDateBegin) && ($todayDate > $ICDateEnd)) {
			$updatestatus = 'Initial Claim Period expired';
		}
	}

	//Update Initial Validity Period expired status
	if (!empty($IVsd) && !empty($IVed)) {
		$IVDateBegin = date('Y-m-d', strtotime($IVsd));
		$IVDateEnd = date('Y-m-d', strtotime($IVed));
		if ($todayDate > $EVDateEnd && $todayDate > $IVDateEnd) {
			if (($todayDate >= $ICDateBegin) && ($todayDate <= $ICDateEnd)) {
				$updatestatus = 'Initial Validity Period expired';
			}
		}
	}

	//Update Extended Validity Period expired status
	if (!empty($ECsd) && !empty($ECed)) {
		$ECDateBegin = date('Y-m-d', strtotime($ECsd));
		$ECDateEnd = date('Y-m-d', strtotime($ECed));
		if ($todayDate > $EVDateBegin) {
			if (($todayDate >= $ECDateBegin) && ($todayDate <= $ECDateEnd)) {
				$updatestatus = 'Extended Validity Period expired';
			}
		}
	}

	//Update Extended Claim Period expired status
	if (!empty($ECsd) && !empty($ECed)) {
		$ECDateBegin = date('Y-m-d', strtotime($ECsd));
		$ECDateEnd = date('Y-m-d', strtotime($ECed));

		if ($todayDate > $ECDateBegin) {
			$updatestatus = 'Extended Claim Period expired';
		}
	}

	$adb->pquery("UPDATE vtiger_bankguarantee 
	SET bnk_pre_status = '$updatestatus' 
	WHERE bankguaranteeid = " . $entityId);
}
