<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

function UpdateAutoStatus($entityData) {
    global $adb;
    $data = $entityData->{'data'};
    $moduleName = $entityData->getModuleName();
    $wsId = $entityData->getId();
    $parts = explode('x', $wsId);
    $entityId = $parts[1];
    $status = $data['bnk_pre_status'];
    $IVsd = $data['bg_initial_vl_st_date'];
    $IVed = $data['bg_initial_vl_en_date'];
    $EVsd = $data['ex_val_start_d'];
    $EVed = $data['ex_val_end_d'];
    $ICsd = $data['bg_initial_cl_st_date'];
    $ICed = $data['bg_initial_cl_end_date'];
    $ECsd = $data['bg_extended_cl_st_date'];
    $Eced = $data['bg_extended_cl_end_date'];

    $todayDate = date('Y-m-d');
    $todayDate = date('Y-m-d', strtotime($todayDate));

    if ($moduleName == "BankGuarantee") {

        //Update Live status
        $IVDateBegin = date('Y-m-d', strtotime($IVsd));
        $IVDateEnd = date('Y-m-d', strtotime($IVed));
        if (($todayDate >= $IVDateBegin) && ($todayDate <= $IVDateEnd)) {
            $updatestatus = 'Live';
        }

        //Update Extended status
        $EVDateBegin = date('Y-m-d', strtotime($EVsd));
        $EVDateEnd = date('Y-m-d', strtotime($EVed));
        if (($todayDate >= $EVDateBegin) && ($todayDate <= $EVDateEnd)) {
            $updatestatus = 'Extended';
        }

        //Update Initial Claim Period expired status
        $ICDateBegin = date('Y-m-d', strtotime($ICsd));
        $ICDateEnd = date('Y-m-d', strtotime($ICed));
        if (($todayDate > $EVDateBegin) && ($todayDate > $ICDateEnd)) {
            $updatestatus = 'Initial Claim Period expired';
        }

        //Update Initial Validity Period expired status
        $IVDateBegin = date('Y-m-d', strtotime($IVsd));
        $IVDateEnd = date('Y-m-d', strtotime($IVed));
        if ($todayDate > $EVDateEnd && $todayDate > $IVDateEnd) {
            if (($todayDate >= $ICDateBegin) && ($todayDate <= $ICDateEnd)) {
                $updatestatus = 'Initial Validity Period expired';
            }
        }

        //Update Extended Validity Period expired status
        $ECDateBegin = date('Y-m-d', strtotime($ECsd));
        $ECDateEnd = date('Y-m-d', strtotime($ECed));
        if ($todayDate > $EVDateBegin) {
            if (($todayDate >= $ECDateBegin) && ($todayDate <= $ECDateEnd)) {
                $updatestatus = 'Extended Validity Period expired';
            }
        }

        //Update Extended Claim Period expired status
        $ECDateBegin = date('Y-m-d', strtotime($ECsd));
        $ECDateEnd = date('Y-m-d', strtotime($ECed));
        if ($todayDate > $ECDateBegin) {
            $updatestatus = 'Extended Claim Period expired';
        }
    }

    $adb->pquery("UPDATE vtiger_bankguarantee SET bnk_pre_status = '$updatestatus' WHERE bankguaranteeid = " . $entityId);
}
