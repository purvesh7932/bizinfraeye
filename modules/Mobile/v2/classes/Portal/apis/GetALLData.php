<?php

class Portal_GetALLData_API extends Portal_Default_API {

	public function process(Portal_Request $request) {
		$module = $request->getModule();
		$response = new Portal_Response();
		$module = $request->get('module');
		if (empty($module)) {
			$response->setError(1501, "Module Is Not Specified.");
			return $response;
		}
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

		$fileName =  $request->get('useruniqueid') . '_' . $module;
		unlink(dirname(__FILE__) . DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."apicache".DIRECTORY_SEPARATOR."$fileName.json");
        $round = 1;
        $handle = @fopen(dirname(__FILE__) . DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."apicache".DIRECTORY_SEPARATOR."$fileName.json", 'r+');
        if ($handle == null) {
            $handle = fopen(dirname(__FILE__) . DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."apicache".DIRECTORY_SEPARATOR."$fileName.json", 'w+');
        }

		$result = Vtiger_Connector::getInstance()->ListModuleRecords($module, $request->get('label'), $request->get('q', array()), $params['fields'], $pageNo, $pageLimit);
		
		$totalPages = ceil((float)$result['count'] / 10);
		$focusObj = CRMEntity::getInstance($module);
		$moduleIdColumn = $focusObj->table_index;
		for($i = 0; $i < $totalPages; $i++){
			$result = Vtiger_Connector::getInstance()->ListModuleRecords($module, $request->get('label'), $request->get('q', array()), $params['fields'], $i , $pageLimit);
			$result = $this->processResponse($result, $module, $language, false, $moduleIdColumn);
			if ($result['records'] == null || empty($result['records'])) {
                continue;
            }
			if ($handle) {
                fseek($handle, 0, SEEK_END);
                if (ftell($handle) > 0) {
                    fseek($handle, -1, SEEK_END);
                    fwrite($handle, ',', 1);
                    fwrite($handle, ltrim(json_encode($result['records']), "["));
                } else {
                    fwrite($handle, json_encode($result['records']));
                }
            }
		}
		fclose($handle);
		$filename = dirname(__FILE__) . DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."apicache".DIRECTORY_SEPARATOR."$fileName.json";
        if (file_exists($filename)) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            header('Content-Type: ' . finfo_file($finfo, $filename));
            finfo_close($finfo);
            header('Content-Disposition: attachment; filename=' . basename($filename));
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filename));
            ob_clean();
            flush();
            readfile($filename);
            exit;
        }
	}

	public function processResponse($result, $module, $language, $isExport = false, $moduleIdColumn) {
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
		// $headers = $result['headers'];
		$records = $result['records'];
		// $edits = $result['edit'];
		unset($result['edit']);
		$recordMeta = parent::processResponse($module, $language);
		// $headerNames = array();
		// $editFieldNames = array();
		// foreach ($headers as $key) {
		// 	if (empty($key)) {
		// 		continue;
		// 	}
		// 	$headerData = [];
		// 	$headerData['label'] = $recordMeta[$key]['label'];
		// 	$headerData['fieldType'] = $recordMeta[$key]['type'];
		// 	$headerData['name'] = $key;
		// 	array_push($headerNames, $headerData);
		// }
		// foreach ($edits as $key) {
		// 	$editFieldNames[$recordMeta[$key]['label']] = $key;
		// }
	
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
				}else if ($recordMeta[$fieldLabel]['type'] == 'integer' && $module == 'Documents' && $fieldLabel == 'filesize') {
					$fieldValue = round(($fieldValue / 1024), 2) . 'KB';
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
						$newVal[$fieldLabel . '_Label'] = $newVal[$fieldLabel];
					} else {
						$newVal[$fieldLabel] = "";
						$newVal[$fieldLabel . '_idofreference'] = "";
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
				}
			}
			$records[$key] = $newVal;
		}
		// $result['headers'] = $headerNames;
		$result['records'] = $records;
		// $result['editLabels'] = $editFieldNames;
		// $result['records_per_page'] = "10";
		// $result['page'] = "1";
		// $result['orderBy'] = false;
		// $result['sortOrder'] = false;
		// $result['page'] = "1";
		// $result['count'] = strval($result['count']);
		return $result;
	}
}
