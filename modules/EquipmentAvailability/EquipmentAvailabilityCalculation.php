<?php
function EquipmentAvailabilityCalculation($entityData) {
    global $adb;
    $recordInfo = $entityData->{'data'};

    $id = $recordInfo['id'];
    $id = explode('x', $id);
    $id = $id[1];

    $monthAndYear = $recordInfo['mdy'];

    $numberOfDays = 0;
    if ($recordInfo['type_of_eq_availability'] == 'Month') {
        $MonthAndYear = (explode("-", $monthAndYear));
        $numberOfDays = (float) cal_days_in_month(CAL_GREGORIAN, $MonthAndYear['1'], $MonthAndYear['0']);
    } else {
        $daysInFeb = cal_days_in_month(CAL_GREGORIAN, 2, $monthAndYear);
        $numberOfDays = (float)($daysInFeb + 337);
    }

    if (empty($shiftHours)) {
        $shiftHours = 0;
    }
    $shiftHours = (float)$recordInfo['shift_hours'];
    if (empty($shiftHours)) {
        $shiftHours = 0;
    }
    $totalHoursInMonth = $shiftHours * $numberOfDays;

    global $log;
    $log->debug("-------Record Info--------(".json_encode($recordInfo).") method ...");
    $custMaint = (float)$recordInfo['cust_maint_hours'];
    $orgMaint = (float)$recordInfo['beml_maint_hours'];
    $totalMaintainence = $custMaint + $orgMaint;
    $consideredMaint = 0;
    $considerCustomerMaint = $recordInfo['cust_maint_hour_cons'];
    if ($considerCustomerMaint == 'Yes') {
        $consideredMaint = $totalMaintainence;
    } else {
        $consideredMaint = $orgMaint;
    }

    $totalBreakDownHours = (float)$recordInfo['beml_total_breakdown'] + (float)$recordInfo['cust_total_breakdown'];

    // print_r("=========totalHoursInMonth  $totalHoursInMonth==================");
    // print_r("===========totalBreakDownHours $totalBreakDownHours================");
    // print_r("============consideredMaint $consideredMaint===============");
    // print_r("=========totalHoursInMonth  $totalHoursInMonth==================");
    // die();

    $eqAva = (($totalHoursInMonth - $totalBreakDownHours - $consideredMaint) / $totalHoursInMonth) * 100;
    $eqAva = number_format($eqAva, 2, '.', '');

    $commitedAvail = $recordInfo['commited_avl_m_w'];
    $shortageOrMore = (float) $commitedAvail - (float) $eqAva;

    $isLessThanCommited = '';
    if ($shortageOrMore > 0) {
        $isLessThanCommited = 'Yes';
    } else {
        $isLessThanCommited = 'No';
    }

    $query = "UPDATE vtiger_equipmentavailability SET equi_avail_percent=? ,
    total_hours = ? , total_maint_hours = ?, less_than_commi_ava = ?,
    total_breakdown = ?
    WHERE equipmentavailabilityid=?";
    $adb->pquery($query, array(
        $eqAva, $totalHoursInMonth,
        $totalMaintainence, $isLessThanCommited,
        $totalBreakDownHours, $id
    ));
}
