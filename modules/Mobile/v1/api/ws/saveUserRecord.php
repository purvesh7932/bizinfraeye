<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
class Mobile_WS_saveUserRecord extends Mobile_WS_Controller {
	
	function process(Mobile_API_Request $request) {
		$resp = [];
		$adb = PearDatabase::getInstance();
		$recentlyClosedProjects = [];
		$whereQuery = "UPDATE vtiger_users set first_name = ?, last_name =? ,email1 =? where id = ?";
		$values = json_decode( $request->get('values') );
		$first_name = $values->{'first_name'};
		$last_name = $values->{'last_name'};
		$email1 = $values->{'email1'};
		$recordId = $request->get('record');
		$recordIds = explode("x",$recordId);
		$id = $recordIds[1];

		$result = $adb->pquery($whereQuery, array($first_name,$last_name,$email1,$id));
		
		$whereQuery = "select * from  vtiger_users  where id = ?";
		$result = $adb->pquery($whereQuery, array($id));
		if($result){
			$rowCount = $adb->num_rows($result);
			while ($rowCount > 0) {
				$rowData = $adb->query_result_rowdata($result,$rowCount-1);
				array_push($recentlyClosedProjects, array(
					'first_name' => $rowData['first_name'], 
					'last_name' => $rowData['last_name'], 
					'email1' => $rowData['email1']));
				--$rowCount;
			}
		}

		$resp['userdetails'] = $recentlyClosedProjects[0];

		$response = new Mobile_API_Response();
		$response->setResult($resp);
        return $response;
	}

}