<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
include_once dirname(__FILE__ ).'/../../../ProjectTask/ProjectTask.php';
class Mobile_WS_TasksDueToday extends Mobile_WS_Controller {
	
	function process(Mobile_API_Request $request) {
		$resp = [];
		$p = new ProjectTask();
		$adb = PearDatabase::getInstance();
		$countQuery = $p->getCountQuery('ProjectTask');

		$today = date('Y-m-d');
		$whereQuery = " AND vtiger_projecttask.enddate = '$today' AND projecttaskstatus != 'Completed'";
		$result = $adb->pquery($countQuery.$whereQuery, array());
		if($result){
			$rowCount = $adb->num_rows($result);
			while ($rowCount > 0) {
				$rowData = $adb->query_result_rowdata($result,$rowCount-1);
				$resp['TodaysDueTasks'] = $rowData['count(*)'];
				--$rowCount;
			}
		}

		$qstr = '';
		for($i = 0;$i < 7;$i++ ){
			$qstr = $qstr. ',' ."'".date('Y-m-d',strtotime("last Sunday +$i day"))."'";
		}
		$qstr[0] = " ";
		$whereQuery = " AND vtiger_projecttask.enddate IN ( $qstr ) AND vtiger_projecttask.projecttaskstatus != 'Completed'";
		$result = $adb->pquery($countQuery.$whereQuery, array());
		if($result){
			$rowCount = $adb->num_rows($result);
			while ($rowCount > 0) {
				$rowData = $adb->query_result_rowdata($result,$rowCount-1);
				$resp['DueInThisWeek'] = $rowData['count(*)'];
				--$rowCount;
			}
		}

		$qstr = '';
		for($i = 0;$i < 7;$i++ ){
			$qstr = $qstr. ',' ."'".date('Y-m-d',strtotime("next Sunday +$i day"))."'";
		}
		$qstr[0] = " ";
		$whereQuery = " AND vtiger_projecttask.enddate IN ( $qstr ) AND vtiger_projecttask.projecttaskstatus != 'Completed'";
		$result = $adb->pquery($countQuery.$whereQuery, array());
		if($result){
			$rowCount = $adb->num_rows($result);
			while ($rowCount > 0) {
				$rowData = $adb->query_result_rowdata($result,$rowCount-1);
				$resp['DueInNextWeek'] = $rowData['count(*)'];
				--$rowCount;
			}
		}
		

		$today = date('Y-m-d');
		$whereQuery = " AND vtiger_projecttask.enddate < '$today' AND vtiger_projecttask.projecttaskstatus != 'Completed'";
		$result = $adb->pquery($countQuery.$whereQuery, array());
		if($result){
			$rowCount = $adb->num_rows($result);
			while ($rowCount > 0) {
				$rowData = $adb->query_result_rowdata($result,$rowCount-1);
				$resp['overDueTasks'] = $rowData['count(*)'];
				--$rowCount;
			}
		}

		$response = new Mobile_API_Response();
		$response->setResult($resp);
        return $response;
	}

}