<?php

class FailedParts_DetailView_Model extends Inventory_DetailView_Model {
	/**
	 * Function to get the detail view links (links and widgets)
	 * @param <array> $linkParams - parameters which will be used to calicaulate the params
	 * @return <array> - array of link models in the format as below
	 *                   array('linktype'=>list of link models);
	 */
	//add create salesorder link in failedparts purvesh

	/*public function getDetailViewLinks($linkParams) {
		$currentUserModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();

		$linkModelList = parent::getDetailViewLinks($linkParams);
		$recordModel = $this->getRecord();

		$salesOrderModuleModel = Vtiger_Module_Model::getInstance('SalesOrder');
		if($currentUserModel->hasModuleActionPermission($salesOrderModuleModel->getId(), 'CreateView')) {
			$basicActionLink = array(
				'linktype' => 'DETAILVIEW',
				'linklabel' =>  vtranslate('LBL_CREATE').' '.vtranslate($salesOrderModuleModel->getSingularLabelKey(), 'SalesOrder'),
				'linkurl' => $recordModel->getCreateSalesOrderUrl(),
				'linkicon' => ''
			);
			$linkModelList['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($basicActionLink);
		}
		return $linkModelList;
	}*/

	public function getDetailViewLinks($linkParams) {
		$linkTypes = array('DETAILVIEWBASIC', 'DETAILVIEW');
		$moduleModel = $this->getModule();
		$recordModel = $this->getRecord();

		$moduleName = $moduleModel->getName();
		$recordId = $recordModel->getId();

		$detailViewLink = array();
		$linkModelList = array();
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$isEditableAsPerFlow = $recordModel->IGisEditable($recordId);
		if (Users_Privileges_Model::isPermitted($moduleName, 'EditView', $recordId) && $isEditableAsPerFlow == true &&  !$currentUserModel->isAdminUser()) {
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

		if($moduleModel->isDuplicateOptionAllowed('CreateView', $recordId)) {
			$duplicateLinkModel = array(
						'linktype' => 'DETAILVIEWBASIC',
						'linklabel' => 'LBL_DUPLICATE',
						'linkurl' => $recordModel->getDuplicateRecordUrl(),
						'linkicon' => ''
				);
			$linkModelList['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($duplicateLinkModel);
		}

		if (Users_Privileges_Model::isPermitted($moduleName, 'Delete', $recordId)) {
			$deletelinkModel = array(
				'linktype' => 'DETAILVIEW',
				'linklabel' => sprintf("%s %s", getTranslatedString('LBL_DELETE', $moduleName), vtranslate('SINGLE_' . $moduleName, $moduleName)),
				'linkurl' => 'javascript:Vtiger_Detail_Js.deleteRecord("' . $recordModel->getDeleteUrl() . '")',
				'linkicon' => ''
			);
			$linkModelList['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($deletelinkModel);
		}

		$linkModelListDetails = Vtiger_Link_Model::getAllByType($moduleModel->getId(), $linkTypes, $linkParams);
		foreach ($linkTypes as $linkType) {
			if (!empty($linkModelListDetails[$linkType])) {
				foreach ($linkModelListDetails[$linkType] as $linkModel) {
					// Remove view history, needed in vtiger5 to see history but not in vtiger6
					if ($linkModel->linklabel == 'View History') {
						continue;
					}
					$linkModelList[$linkType][] = $linkModel;
				}
			}
			unset($linkModelListDetails[$linkType]);
		}

		$relatedLinks = $this->getDetailViewRelatedLinks();

		foreach ($relatedLinks as $relatedLinkEntry) {
			$relatedLink = Vtiger_Link_Model::getInstanceFromValues($relatedLinkEntry);
			$linkModelList[$relatedLink->getType()][] = $relatedLink;
		}

		$widgets = $this->getWidgets();
		foreach ($widgets as $widgetLinkModel) {
			$linkModelList['DETAILVIEWWIDGET'][] = $widgetLinkModel;
		}

		if ($currentUserModel->isAdminUser()) {
			$settingsLinks = $moduleModel->getSettingLinks();
			foreach ($settingsLinks as $settingsLink) {
				$linkModelList['DETAILVIEWSETTING'][] = Vtiger_Link_Model::getInstanceFromValues($settingsLink);
			}
		}

		// $recordModel = $this->getRecord();
		// $moduleName = $recordModel->getmoduleName();
		// if ($moduleName == "FailedParts") {
		// 	$createSOLink = array(
		// 		'linklabel' => vtranslate('Create Sales Order', $moduleName),
		// 		'linkurl' => $recordModel->getCreateSalesOrderUrl(),
		// 		'linkicon' => ''
		// 	);

		// 	$linkModelList['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($createSOLink);
		// }
		return $linkModelList;
	}
}
