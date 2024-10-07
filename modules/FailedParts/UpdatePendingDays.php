<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

function UpdatePendingDays($entityData){
   global $adb;
   $data = $entityData->{'data'};
   $moduleName = $entityData->getModuleName();
   $wsId = $entityData->getId();
   $parts = explode('x', $wsId);
   $entityId = $parts[1];
   $replaceddate = $data['LineItems'][0]['replaced_date'];
   $sdate = date('Y-m-d', strtotime($replaceddate . ' +10 day'));
   $tdate = date('Y-m-d');
   $date1 = date_create($sdate);
   $date2 = date_create($tdate);
   $diff = date_diff($date1,$date2);
   $diffday = $diff->format("%a");
   $num_rows = count($data['LineItems']);
     // print_r($data['LineItems']);
     // exit();
   if($moduleName == "FailedParts")
   {
      for($i=0; $i<$num_rows; $i++){
           $lineitemid = $data['LineItems'][$i]['lineitem_id'];
           $status = $data['LineItems'][$i]['fail_pa_pa_status'];
           $sdate = $data['LineItems'][$i]['replaced_date'];
           $tdate = date('Y-m-d');
           $date1 = date_create($sdate);
           $date2 = date_create($tdate);
           $diff = date_diff($date1,$date2);
           $diffday = $diff->format("%a");

           if($status != "Closed")
             {
               $adb->pquery("UPDATE vtiger_inventoryproductrel SET pending_days = ".$diffday." WHERE lineitem_id = ".$lineitemid);
             }   
         }
     }
}		
