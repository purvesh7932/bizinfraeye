<?php

class Portal_listModuleRecords_API extends Portal_Default_API {

	public function process(Portal_Request $request) {
		$module = $request->getModule();
		$response = new Portal_Response();
		$language = Portal_Session::get('language');
		$params = $request->get('q');
		$pageNo = $params['page'];
		$filter = $request->get('filter');
		if (empty($pageNo))
			$pageNo = 0;
		$pageLimit = $params['pageLimit'];
		if (empty($pageLimit))
			$pageLimit = 10;
		if (!empty($filter)) {
			$params['fields'] = json_encode($filter);
		}
		$order = $params['order'];
		$orderBy = $params['orderBy'];
		$result = Vtiger_Connector::getInstance()->ListModuleRecords($module, $request->get('label'), $request->get('q', array()), $params['fields'], $pageNo, $pageLimit);
		$response->setResult($this->processResponse($result, $module, $language, false , $request));
		$response->setApiSucessMessage('Successfully Fetched Data');
		return $response;
	}

	public function processResponse($result, $module, $language, $isExport = false, $request) {
		$accessToken = $request->get('access_token');
		$userUniqueId = $request->get('useruniqueid');
		if ($result['records'] === null || empty($result['records'])) {
			$result['records'] = [];
			$result['count'] = "0";
			$result['page'] = "1";
			$result['records_per_page'] = "10";
			$result['moreRecords'] = false;
			$result['orderBy'] = false;
			$result['sortOrder'] = false;
			return $result;
		}
		$headers = $result['headers'];
		$records = $result['records'];
		$edits = $result['edit'];
		unset($result['edit']);
		$recordMeta = parent::processResponse($module, $language);
		$headerNames = array();
		$editFieldNames = array();
		foreach ($headers as $key) {
			if (empty($key)) {
				continue;
			}
			$headerData = [];
			$headerData['label'] = $recordMeta[$key]['label'];
			$headerData['fieldType'] = $recordMeta[$key]['type'];
			$headerData['name'] = $key;
			array_push($headerNames, $headerData);
		}
		foreach ($edits as $key) {
			$editFieldNames[$recordMeta[$key]['label']] = $key;
		}
		$focusObj = CRMEntity::getInstance($module);
		$moduleIdColumn = $focusObj->table_index;
		foreach ($records as $key => $value) {
			$newVal = [];
			foreach ($value as $fieldLabel => $fieldValue) {
				if ($recordMeta[$fieldLabel]['type'] == 'picklist') {
					foreach ($recordMeta[$fieldLabel]['picklistValues'] as $key1 => $value1) {
						if ($value[$fieldLabel] == $value1['value']) {

							$fieldValue = $value1['label'];
						}
					}
				} else if ($recordMeta[$fieldLabel]['type'] == 'multipicklist') {
					$fieldValue = str_replace(' |##| ', ",", $fieldValue);
				} else if ($recordMeta[$fieldLabel]['type'] == 'double' || $recordMeta[$fieldLabel]['type'] == 'currency') {
					$fieldValue = round($fieldValue, 2);
				} else if ($recordMeta[$fieldLabel]['type'] == 'boolean') {
					$fieldValue = $fieldValue == 1 ? "Yes" : "No";
				} else if ($recordMeta[$fieldLabel]['type'] == 'integer' && $module == 'Documents' && $fieldLabel == 'filesize') {
					$fieldValue = round(($fieldValue / 1024), 2).'KB';
				} else if ($recordMeta[$fieldLabel]['type'] == 'string' && $fieldLabel == 'filelocationtype' && $module == 'Documents') {
					if ($fieldValue !== '' && $fieldValue == "I") {
						$fieldValue = "Internal";
					}
					if ($fieldValue !== '' && $fieldValue == "E") {
						$fieldValue = "External";
					}
				} else if ($recordMeta[$fieldLabel]['type'] == "text") {
					$fieldValue = strip_tags($fieldValue);
					$fieldValue = preg_replace('/<br(\s+)?\/?>/i', "\n", $fieldValue);
				} else if ($recordMeta[$fieldLabel]['type'] == "file" && $fieldLabel == 'filename' && $module == 'Documents') {
					$docExists = true;
					if ($fieldValue == '') {
						$docExists = false;
					}
				} else if ($recordMeta[$fieldLabel]['type'] == 'reference') {
					if (!empty($fieldValue)) {
						$newVal[$fieldLabel . '_idofreference'] = $fieldValue;
						$recordWithModuleCode = $fieldValue;
						$ids = explode('x', $recordWithModuleCode);
						$newVal[$fieldLabel] = Vtiger_Functions::getCRMRecordLabel($ids[1]);
						$fieldValue = $newVal[$fieldLabel.'_Label'] = $newVal[$fieldLabel];
					} else {
						$newVal[$fieldLabel] = "";
						$newVal[$fieldLabel . '_idofreference'] = "";
					}
				} else if ($recordMeta[$fieldLabel]['type'] == 'date') {
					if (!empty($fieldValue)) {
						$newVal[$fieldLabel] = Vtiger_Date_UIType::getDisplayDateValue($fieldValue);
					}
				}
				$fieldValue = strip_tags($fieldValue);
				$newVal[$fieldLabel] = $fieldValue;
				$value[$recordMeta[$fieldLabel]['label']] = $fieldValue;
				if ($module == 'Documents') {
					if ($fieldLabel !== "filename") {
						unset($value[$fieldLabel]);
					}
				} else {
					unset($value[$fieldLabel]);
				}
				if ($isExport) {
					unset($value['id']);
				}
				if ($recordMeta[$fieldLabel]['label'] == 'id') {
					$newVal[$moduleIdColumn] = $fieldValue;
				} else if ($fieldLabel == 'is_submitted') {
					if ($fieldValue == '1') {
						$newVal['report_status'] = 'Submitted';
						$newVal['report_url'] = $this->getReportURL($value['id'], $accessToken, $userUniqueId);
					} else {
						$newVal['report_status'] = 'In Progress';
						$newVal['report_url'] = NULL;
					}
				}
			}
			$records[$key] = $newVal;
			if ($docExists && $module == 'Documents') {
				$records[$key]['documentExists'] = true;
			}
		}
		$result['headers'] = $headerNames;
		$result['records'] = $records;
		$result['editLabels'] = $editFieldNames;
		// $result['pageLimit'] = 10;
		$result['records_per_page'] = "10";
		$result['page'] = "1";
		$result['orderBy'] = false;
		$result['sortOrder'] = false;
		$result['page'] = "1";
		$result['count'] = strval($result['count']);
		return $result;
	}

	public function getReportURL($orderId, $accessToken, $userUniqueId) {
		$orderId = explode('x', $orderId);
		$orderId = $orderId[1];
		global $site_URL_NonHttp;
		// $sql = " SELECT vtiger_servicereports.servicereportsid FROM `vtiger_servicereports` "
		// 	. " INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_servicereports.servicereportsid "
		// 	. " where vtiger_crmentity.deleted = 0 and vtiger_servicereports.ticket_id = ?";
		// $allProductIds = $adb->pquery($sql, array($orderId));
		// $imagePath = "";
		// while ($row = $adb->fetch_array($allProductIds)) {
		// 	$reportId = $row['servicereportsid'];
			$imagePath = $site_URL_NonHttp . "modules/Mobile/v2/DownloadPDFReport?module=PDFMaker&source_module=ServiceReports&action=IndexAjax&record=$orderId&mode=getPreviewContent&language=en_us&generate_type=attachment&igtempid=1&access_token=$accessToken&useruniqueid=$userUniqueId";
		// }
		// if ($imagePath == "") {
		// 	$imagePath = NULL;
		// }

		return $imagePath;
	}

	public function convertElapsedTime($value, $currentDate) {
		$minutes = (strtotime($currentDate) - strtotime($value)) / 60;
		$timeString = '';
		if ($minutes != 'NULL' && $value !== '0000-00-00 00:00:00') {
			$minutes = $minutes * 60;
			$s = (floor($minutes % 60) > 0) ? ($minutes % 60).' seconds ' : '';
			$m = (floor(($minutes % 3600) / 60) > 0) ? floor(($minutes % 3600) / 60).' minutes' : '';
			$h = (floor(($minutes % 86400) / 3600) > 0) ? floor(($minutes % 86400) / 3600).' hours' : '';
			$d = (floor(($minutes % 2592000) / 86400) > 0) ? floor(($minutes % 2592000) / 86400).' days' : '';
			$Mo = (floor($minutes / 2592000) > 0) ? floor($minutes / 2592000).' months' : '';
			$timeString = "$Mo $d $h $m $s";
		}
		return $timeString;
	}

}
