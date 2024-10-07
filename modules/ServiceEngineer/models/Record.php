<?php

class ServiceEngineer_Record_Model extends Vtiger_Record_Model {

	/**
	 * Function to get the Display Name for the record
	 * @return <String> - Entity Display Name for the record
	 */
	public function getDisplayName() {
		return Vtiger_Util_Helper::getRecordName($this->getId());
	}

	/**
	 * Function to get URL for Convert FAQ
	 * @return <String>
	 */
	public function getConvertFAQUrl() {
		return "index.php?module=".$this->getModuleName()."&action=ConvertFAQ&record=".$this->getId();
	}

	public function getImageDetails() {
        global $site_URL;
        $db = PearDatabase::getInstance();
        $imageDetails = array();
        $recordId = $this->getId();
        if ($recordId) {
            $sql = "SELECT vtiger_attachments.*, vtiger_crmentity.setype FROM vtiger_attachments
                INNER JOIN vtiger_seattachmentsrel ON vtiger_seattachmentsrel.attachmentsid = vtiger_attachments.attachmentsid
                INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_attachments.attachmentsid
                WHERE vtiger_crmentity.setype In ('ServiceEngineer Attachment' , 'ServiceEngineer Image')  AND vtiger_seattachmentsrel.crmid = ?";
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
                        'orgname' => $imageOriginalNamesList[$j],
                        'path' => $imagePathList[$j] . $imageIdsList[$j],
                        'location' => $imagePathList[$j] . $imageIdsList[$j] . '_' . $storedname[$j],
                        'name' => $imageNamesList[$j],
                        'url' => $imageUrlsList[$j],
                        'field' => $imageUrlsList[$j],
                        'fieldNameFromDB' => $fieldName[$j],
                        'descriptionOffield' => $descriptionOffield[$j]
                    );
                }
            }
        }
        return $imageDetails;
    }

	/**
	 * Function to get Comments List of this Record
	 * @return <String>
	 */
	public function getCommentsList() {
		$db = PearDatabase::getInstance();
		$commentsList = array();

		$result = $db->pquery("SELECT commentcontent AS comments FROM vtiger_modcomments WHERE related_to = ?", array($this->getId()));
		$numOfRows = $db->num_rows($result);

		for ($i=0; $i<$numOfRows; $i++) {
			array_push($commentsList, $db->query_result($result, $i, 'comments'));
		}

		return $commentsList;
	}
}