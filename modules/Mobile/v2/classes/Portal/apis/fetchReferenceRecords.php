<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.2
 * ("License.txt"); You may not use this file except in compliance with the License
 * The Original Code is: Vtiger CRM Open Source
 * The Initial Developer of the Original Code is Vtiger.
 * Portions created by Vtiger are Copyright (C) Vtiger.
 * All Rights Reserved.
 * ***********************************************************************************/

class Portal_fetchReferenceRecords_API extends Portal_Default_API {

	public function process(Portal_Request $request) {
		$module = $request->get('module');
		$query = $request->get('searchValue');
		$response = new Portal_Response();
		$result = Vtiger_Connector::getInstance()->fetchReferenceRecords($module, $query);
		if(empty($result)){
			$result = [];
		}
		$responseObject = [];
		$responseObject['referenceRecords'] = $result;
		$response->setApiSucessMessage('Successfully Fetched Data');
		$response->setResult($responseObject);
		return $response;
	}

}
