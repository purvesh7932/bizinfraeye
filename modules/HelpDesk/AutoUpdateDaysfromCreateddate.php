<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

function AutoUpdateDaysfromCreateddate($entityData) {
    global $adb;
    $data = $entityData->{'data'};
    $moduleName = $entityData->getModuleName();
    $wsId = $entityData->getId();
    $parts = explode('x', $wsId);
    $entityId = $parts[1];
    $status = $data['ticketstatus'];
    $createdtime = $data['createdtime'];
    $crrtime = explode(" ", $createdtime);
    $sdate = $crrtime[0];
    $tdate = date('Y-m-d');
    $date1 = date_create($sdate);
    $date2 = date_create($tdate);
    $diff = date_diff($date1, $date2);
    $diffday = $diff->format("%a");

    if ($moduleName == "HelpDesk") {
        if ($status != "Closed") {
            $adb->pquery("UPDATE vtiger_troubletickets set no_of_days = " . $diffday . " WHERE ticketid = " . $entityId);
        }
    }
}
