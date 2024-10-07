<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
class Mobile_WS_getRelatedProjectsOfAccounts extends Mobile_WS_Controller {
	
	function process(Mobile_API_Request $request) {
		$adb = PearDatabase::getInstance();
		$whereQuery = "select * from vtiger_project where linktoaccountscontacts = ?";
		$recordId = $request->get('record');
		$recordIds = explode("x",$recordId);
		$id = $recordIds[1];
		$result = $adb->pquery($whereQuery, array($id));
		
		$recentlyClosedProjects = [];
		if($result){
			$rowCount = $adb->num_rows($result);
			while ($rowCount > 0) {
				$rowData = $adb->query_result_rowdata($result,$rowCount-1);
				array_push($recentlyClosedProjects, $rowData);
				--$rowCount;
			}
		}
		
		$response = new Mobile_API_Response();
		$response->setResult($recentlyClosedProjects);
        return $response;
	}
	
}