<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.2
 * ("License.txt"); You may not use this file except in compliance with the License
 * The Original Code is: Vtiger CRM Open Source
 * The Initial Developer of the Original Code is Vtiger.
 * Portions created by Vtiger are Copyright (C) Vtiger.
 * All Rights Reserved.
 * ***********************************************************************************/

class Portal_FetchRecord_API extends Portal_Default_API {

	public function process(Portal_Request $request) {

		$module = $request->getModule();
		$language = $request->getLanguage();
		if (null !== ($request->get('parentId'))) {
			$parentId = $request->get('parentId');
			$result = Vtiger_Connector::getInstance()->fetchRecord($request->get('record'), $module, $parentId);
		} else {
			$result = Vtiger_Connector::getInstance()->fetchRecord($request->get('record'), $module, '');
		}

		$response = new Portal_Response();
		$response->setResult($this->processResponse($result, $module, $language));
		$response->setApiSucessMessage('Successfully Fetched Data');
		return $response;
	}

	public function processResponse($result, $module, $language) {
		$moduleMeta = array();
		$recordMeta = parent::processResponse($module, $language);
		$editFieldNames = array();
		$initialResult = $result;
		if (!empty($result) && isset($result['record'])) {
			$result['recordInfo'] = $result['record'];
			foreach ($initialResult['record'] as $key => $value) {
				$result['record']['identifierName'] = $recordMeta['labelField'];
				$name = $recordMeta[$key]['label'];
				$editFieldNames[$key] = $recordMeta[$key]['label'];
				if ($recordMeta[$key]['type'] == 'double' || $recordMeta[$key]['type'] == 'currency') {
					$value = round($value, 2);
				}
				if ($recordMeta[$key]['type'] == 'picklist' || $recordMeta[$key]['type'] == 'metricpicklist') {
					foreach ($recordMeta[$key]['picklistValues'] as $key1 => $value1) {
						if ($module == 'HelpDesk' && $key == 'ticketstatus') {
							$result['HelpDesk']['isStatusEditable'] = $recordMeta[$key]['editable'];
							if ($value1['value'] == 'Closed') {
								$result['HelpDesk']['closeLabel'] = $value1['label'];
							}
						}

						if ($module == 'Quotes' && $key == 'quotestage') {
							if ($value1['value'] == 'Accepted') {
								$result['Quotes']['acceptLabel'] = $value1['label'];
							}
						}
						if ($result['record'][$key] == $value1['value']) {

							if ($module == 'HelpDesk' && $key == 'ticketstatus' && $value == 'Closed') {
								$result['HelpDesk']['status'] = 'Closed';
							}
							if ($module == 'HelpDesk' && $key == 'ticketstatus' && $value !== 'Closed') {
								$result['HelpDesk']['status'] = 'Open';
							}

							if ($module == 'Quotes' && $key == 'quotestage' && $value == 'Accepted') {
								$result['Quotes']['stage'] = 'Accepted';
							}
							if ($module == 'Quotes' && $key == 'quotestage' && $value !== 'Accepted') {
								$result['Quotes']['stage'] = 'Created';
							}
							$value = $value1['label'];
						}
					}
				}
				if ($recordMeta[$key]['type'] == 'multipicklist') {
					$value = str_replace(' |##| ', ",", $value);
				}
				if ($recordMeta[$key]['type'] == 'text') {
					$value = strip_tags($value);
					$value = preg_replace('/<br(\s+)?\/?>/i', "\n", $value);
				}
				if ($recordMeta[$key]['type'] == 'boolean') {
					$value = $value == 1 ? "Yes" : "No";
				}
				if ($recordMeta[$key]['type'] == 'integer' && $module == "Documents" && $key == 'filesize') {
					$value = round(($value / 1024), 2).'KB';
				}
				if ($recordMeta[$key]['type'] == 'string' && $key == 'filelocationtype' && $module == 'Documents') {
					if ($value !== '' && $value == "I") {
						$value = "Internal";
					}
					if ($value !== '' && $value == "E") {
						$value = "External";
					}
				}
				if ($recordMeta[$key]['type'] == 'file' && $key == 'filename' && $module == 'Documents') {
					$docExists = true;
					if ($value == '') {
						$docExists = false;
					}
				}

				if ($recordMeta[$key]['type'] == 'reference') {
					$result[$module]['referenceFields'][$name] = $value;
				}

				if ($recordMeta[$key]['type'] == 'string' || $recordMeta[$key]['type'] == 'string') {
					$value = strip_tags($value);
				}

				if ($key !== 'id') {
					$result['record'][$name] = $value;
					unset($result['record'][$key]);
				}
				if (is_array($value)) {
					$result['record'][$name] = $value['label'];
					unset($result['record'][$key]);
				}
				if ($docExists && $module == 'Documents') {
					$result['record']['documentExists'] = true;
				}
				if ($recordMeta[$key]['type'] == 'reference') {
					$initialResult['record'][$key] = $initialResult['record'][$key]['label'];
				}

				if ($recordMeta[$key]['type'] == 'image') {
					include_once('include/utils/GeneralUtils.php');
					global $site_URL_NonHttp;
					$recordId = explode('x', $initialResult['record']['id']);
					$recordId = $recordId[1];
					$attachments = [];
					$imageDetails = getImageDetailsInUtils($recordId);
					foreach ($imageDetails as $imageDetail) {
						$attachment = [];
						$attachment['urlpath'] = $site_URL_NonHttp . $imageDetail['url'];
						$attachment['loadimage'] = '';
						array_push($attachments, $attachment);
					}
					$initialResult['record'][$key] = $attachments;
				}
			}
			$initialResult['record']['CurrentRecordId'] = $initialResult['record']['id'];
		}
		return $initialResult;
	}

}
