<?php
class ServiceEngineer_DetailView_Model extends Vtiger_DetailView_Model {

	public function getDetailViewLinksAno($linkParams) {
		$linkTypes = array('DETAILVIEWBASIC','DETAILVIEW');
		$moduleModel = $this->getModule();
		$recordModel = $this->getRecord();

		$moduleName = $moduleModel->getName();
		$recordId = $recordModel->getId();

		$detailViewLink = array();
		$linkModelList = array();
		if(Users_Privileges_Model::isPermitted($moduleName, 'EditView', $recordId)) {
			$detailViewLinks[] = array(
					'linktype' => 'DETAILVIEWBASIC',
					'linklabel' => 'LBL_EDIT',
					'linkurl' => $recordModel->getEditViewUrl(),
					'linkicon' => ''
			);

			foreach ($detailViewLinks as $detailViewLink) {
				$linkModelList['DETAILVIEWBASIC'][] = Vtiger_Link_Model::getInstanceFromValues($detailViewLink);
			}
		}

		// if(Users_Privileges_Model::isPermitted($moduleName, 'Delete', $recordId)) {
		// 	$deletelinkModel = array(
		// 			'linktype' => 'DETAILVIEW',
		// 			'linklabel' => sprintf("%s %s", getTranslatedString('LBL_DELETE', $moduleName), vtranslate('SINGLE_'. $moduleName, $moduleName)),
		// 			'linkurl' => 'javascript:Vtiger_Detail_Js.deleteRecord("'.$recordModel->getDeleteUrl().'")',
		// 			'linkicon' => ''
		// 	);
		// 	$linkModelList['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($deletelinkModel);
		// }

		// if($moduleModel->isDuplicateOptionAllowed('CreateView', $recordId)) {
		// 	$duplicateLinkModel = array(
		// 				'linktype' => 'DETAILVIEWBASIC',
		// 				'linklabel' => 'LBL_DUPLICATE',
		// 				'linkurl' => $recordModel->getDuplicateRecordUrl(),
		// 				'linkicon' => ''
		// 		);
		// 	$linkModelList['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($duplicateLinkModel);
		// }

		if($this->getModule()->isModuleRelated('Emails') && Vtiger_RecipientPreference_Model::getInstance($this->getModuleName())) {
			$emailRecpLink = array('linktype' => 'DETAILVIEW',
								'linklabel' => vtranslate('LBL_EMAIL_RECIPIENT_PREFS',  $this->getModuleName()),
								'linkurl' => 'javascript:Vtiger_Index_Js.showRecipientPreferences("'.$this->getModuleName().'");',
								'linkicon' => '');
			$linkModelList['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($emailRecpLink);
		}

		$linkModelListDetails = Vtiger_Link_Model::getAllByType($moduleModel->getId(),$linkTypes,$linkParams);
		foreach($linkTypes as $linkType) {
			if(!empty($linkModelListDetails[$linkType])) {
				foreach($linkModelListDetails[$linkType] as $linkModel) {
					// Remove view history, needed in vtiger5 to see history but not in vtiger6
					if($linkModel->linklabel == 'View History') {
						continue;
					}
					$linkModelList[$linkType][] = $linkModel;
				}
			}
			unset($linkModelListDetails[$linkType]);
		}

		$relatedLinks = $this->getDetailViewRelatedLinks();

		foreach($relatedLinks as $relatedLinkEntry) {
			$relatedLink = Vtiger_Link_Model::getInstanceFromValues($relatedLinkEntry);
			$linkModelList[$relatedLink->getType()][] = $relatedLink;
		}

		$widgets = $this->getWidgets();
		foreach($widgets as $widgetLinkModel) {
			$linkModelList['DETAILVIEWWIDGET'][] = $widgetLinkModel;
		}

		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		if($currentUserModel->isAdminUser()) {
			$settingsLinks = $moduleModel->getSettingLinks();
			foreach($settingsLinks as $settingsLink) {
				$linkModelList['DETAILVIEWSETTING'][] = Vtiger_Link_Model::getInstanceFromValues($settingsLink);
			}
		}

		return $linkModelList;
	}

	public function getDetailViewLinks($linkParams) {
		$currentUserModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();

		$linkModelList = $this->getDetailViewLinksAno($linkParams);
		$recordModel = $this->getRecord();
		$recordId = $recordModel->getId();
		$acceptStatus =  $recordModel->get('approval_status');
		if (empty($acceptStatus)) {
			$basicActionLink = array(
				'linktype' => 'DETAILVIEWBASIC',
				'linklabel' => 'Approve',
				'linkurl' => "javascript:ServiceEngineer_Detail_Js.approveOrReject('index.php?module=" . $this->getModule()->getName() .
					"&action=ApproveOrReject&record=$recordId&source_module=ServiceEngineer','Accepted')",
				'linkicon' => ''
			);
			$linkModelList['DETAILVIEWBASIC'][] = Vtiger_Link_Model::getInstanceFromValues($basicActionLink);

			$basicActionLink = array(
				'linktype' => 'DETAILVIEWBASIC',
				'linklabel' => 'Reject',
				'linkurl' => "javascript:ServiceEngineer_Detail_Js.approveOrReject('index.php?module=" . $this->getModule()->getName() .
					"&action=ApproveOrReject&record=$recordId&source_module=ServiceEngineer', 'Rejected')",
				'linkicon' => ''
			);
			$linkModelList['DETAILVIEWBASIC'][] = Vtiger_Link_Model::getInstanceFromValues($basicActionLink);
		}
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		if($acceptStatus == 'Accepted' && $currentUserModel->isAdminUser()){
			$userId = $this->getAssociatedUserId($recordModel->get('badge_no'));
			$serviceEngid = $recordModel->get('id');
			$passwordChangeUrl="ServiceEngineer_Detail_Js.triggerChangePassword('index.php?module=ServiceEngineer&action=SaveAjaxSavePassword&userid=$userId&serviceEngid=$serviceEngid')";
			$basicActionLink = array(
				'linktype' => 'DETAILVIEWBASIC',
				'linklabel' => 'Reset Password',
				'linkurl' => $passwordChangeUrl,
				'linkicon' => ''
			);
			$linkModelList['DETAILVIEWBASIC'][] = Vtiger_Link_Model::getInstanceFromValues($basicActionLink);
		}
		return $linkModelList;
	}

	function getAssociatedUserId($userName) {
		global $adb;
		$result = $adb->pquery('SELECT id FROM `vtiger_users` where user_name = ?', array($userName));
		$num_rows = $adb->num_rows($result);
		if ($num_rows > 0) {
			$dataRow = $adb->fetchByAssoc($result, 0);
			return $dataRow['id'];
		}
	}
}
