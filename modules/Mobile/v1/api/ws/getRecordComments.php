<?php
class Mobile_WS_getRecordComments extends Mobile_WS_Controller {

	function process(Mobile_API_Request $request) {
		$adb = PearDatabase::getInstance();
		$whereQuery = "select commentcontent,modcommentsid,parent_comments,
		concat(first_name, last_name) as name , vtiger_crmentity.createdtime, vtiger_crmentity.smcreatorid
		from vtiger_modcomments
		INNER JOIN vtiger_crmentity ON vtiger_modcomments.modcommentsid = vtiger_crmentity.crmid
		inner join vtiger_users on vtiger_users.id = vtiger_crmentity.smownerid
		where related_to = ? and parent_comments = '0'";
		$recordId = $request->get('record');
		$recordIds = explode("x", $recordId);
		$id = $recordIds[1];
		$result = $adb->pquery($whereQuery, array($id));
		$Comments = [];
		if ($result) {
			$rowCount = $adb->num_rows($result);
			while ($rowCount > 0) {
				$rowData = $adb->query_result_rowdata($result, $rowCount - 1);
				array_push($Comments, array(
					'commentcontent' => $rowData['commentcontent'],
					'modcommentsid' => '28x' . $rowData['modcommentsid'],
					'parent_comments' => $rowData['parent_comments'],
					'UserName' => $rowData['name'],
					'replies' => $this->getRepliedComments($rowData['modcommentsid']),
					'imagename' => $this->getImageDetailsOfModComments($rowData['modcommentsid']),
					'createdtime' => Vtiger_Util_Helper::formatDateDiffInStrings($rowData['createdtime']),
					'user_image' => $this->getUserImageDetails($rowData['smcreatorid'])
				));
				--$rowCount;
			}
		}
		$response = new Mobile_API_Response();
		$response->setResult($Comments);
		$response->setApiSucessMessage('Successfully Fetched Data');
		return $response;
	}

	function getRepliedComments($recordId) {
		$adb = PearDatabase::getInstance();
		$whereQuery = "select commentcontent,modcommentsid,parent_comments,
		concat(first_name, last_name) as name , vtiger_crmentity.createdtime, vtiger_crmentity.smcreatorid
		from vtiger_modcomments
		INNER JOIN vtiger_crmentity ON vtiger_modcomments.modcommentsid = vtiger_crmentity.crmid
		inner join vtiger_users on vtiger_users.id = vtiger_crmentity.smownerid
		where parent_comments = ?";
		$result = $adb->pquery($whereQuery, array($recordId));
		$Comments = [];
		if ($result) {
			$rowCount = $adb->num_rows($result);
			while ($rowCount > 0) {
				$rowData = $adb->query_result_rowdata($result, $rowCount - 1);
				array_push($Comments, array(
					'commentcontent' => $rowData['commentcontent'],
					'modcommentsid' => '28x' . $rowData['modcommentsid'],
					'parent_comments' => $rowData['parent_comments'],
					'UserName' => $rowData['name'],
					'replies' => $this->getRepliedComments($rowData['modcommentsid']),
					'createdtime' => Vtiger_Util_Helper::formatDateDiffInStrings($rowData['createdtime']),
					'user_image' => $this->getUserImageDetails($rowData['smcreatorid'])
				));
				--$rowCount;
			}
		}
		return $Comments;
	}

	function getImageDetailsOfModComments($recordId) {
		global $site_URL_NonHttp;
		$db = PearDatabase::getInstance();
		$imageDetails = array();
		if ($recordId) {
			$sql = "SELECT vtiger_attachments.*, vtiger_crmentity.setype FROM vtiger_attachments
				INNER JOIN vtiger_seattachmentsrel ON vtiger_seattachmentsrel.attachmentsid = vtiger_attachments.attachmentsid
				INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_attachments.attachmentsid
				WHERE vtiger_crmentity.setype In ('ModComments Attachment')  AND vtiger_seattachmentsrel.crmid = ?";
			$result = $db->pquery($sql, array($recordId));
			$count = $db->num_rows($result);
			for ($i = 0; $i < $count; $i++) {
				$imageId = $db->query_result($result, $i, 'attachmentsid');
				$imageIdsList[] = $db->query_result($result, $i, 'attachmentsid');
				$imagePathList[] = $db->query_result($result, $i, 'path');
				$storedname[] = $db->query_result($result, $i, 'storedname');
				$imageName = $db->query_result($result, $i, 'name');
				$fieldName[] = $db->query_result($result, $i, 'subject');
				$url = \Vtiger_Functions::getFilePublicURL($imageId, $imageName);
				$imageOriginalNamesList[] = urlencode(decode_html($imageName));
				$imageNamesList[] = $imageName;
				$imageUrlsList[] = $url;
				$descriptionOffield[] = $db->query_result($result, $i, 'description');
			}
			if (is_array($imageOriginalNamesList)) {
				$countOfImages = count($imageOriginalNamesList);
				for ($j = 0; $j < $countOfImages; $j++) {
					$imageDetails[] = array(
						'id' => $imageIdsList[$j],
						'loadimage' => '',
						'name' => $imageNamesList[$j],
						'url' => $site_URL_NonHttp . $imageUrlsList[$j],
						'fieldNameFromDB' => $fieldName[$j],
						'descriptionOffield' => $descriptionOffield[$j]
					);
				}
			}
		}
		return $imageDetails;
	}

	protected static $userURLCache = array();

	public function getUserImageDetails($recordId) {
		if (empty($recordId)) {
			return NULL;
		}
		global $site_URL;
		$db = PearDatabase::getInstance();
		$url = NULL;
		if (!self::$userURLCache[$recordId]) {
			$query = "SELECT vtiger_attachments.attachmentsid, vtiger_attachments.path, 
					vtiger_attachments.name, vtiger_attachments.storedname FROM vtiger_attachments
					LEFT JOIN vtiger_salesmanattachmentsrel 
					ON vtiger_salesmanattachmentsrel.attachmentsid = vtiger_attachments.attachmentsid
					WHERE vtiger_salesmanattachmentsrel.smid=? order by attachmentsid desc limit 1";
			$result = $db->pquery($query, array($recordId));
			$storedname = $db->query_result($result, 0, 'storedname');
			$imagePath = $db->query_result($result, 0, 'path');
			$imageId = $db->query_result($result, 0, 'attachmentsid');
			$url = $site_URL . $imagePath. $imageId .'_'.$storedname;
			self::$userURLCache[$recordId] = $url;
			if (empty($imagePath)) {
				self::$userURLCache[$recordId] = NULL;
			}
		}
		return self::$userURLCache[$recordId];
	}
}
