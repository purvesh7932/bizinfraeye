<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class SalesOrder_DetailView_Model extends Inventory_DetailView_Model {

	public function getDetailViewLinksIG($linkParams) {
		$linkTypes = array('DETAILVIEWBASIC', 'DETAILVIEW');
		$moduleModel = $this->getModule();
		$recordModel = $this->getRecord();

		$moduleName = $moduleModel->getName();
		$recordId = $recordModel->getId();

		$detailViewLink = array();
		$linkModelList = array();
		$external_app_num = $recordModel->get('external_app_num');
		if (Users_Privileges_Model::isPermitted($moduleName, 'EditView', $recordId) && empty($external_app_num)) {
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

		if (Users_Privileges_Model::isPermitted($moduleName, 'Delete', $recordId)) {
			$deletelinkModel = array(
				'linktype' => 'DETAILVIEW',
				'linklabel' => sprintf("%s %s", getTranslatedString('LBL_DELETE', $moduleName), vtranslate('SINGLE_' . $moduleName, $moduleName)),
				'linkurl' => 'javascript:Vtiger_Detail_Js.deleteRecord("' . $recordModel->getDeleteUrl() . '")',
				'linkicon' => ''
			);
			$linkModelList['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($deletelinkModel);
		}

		if ($this->getModule()->isModuleRelated('Emails') && Vtiger_RecipientPreference_Model::getInstance($this->getModuleName())) {
			$emailRecpLink = array(
				'linktype' => 'DETAILVIEW',
				'linklabel' => vtranslate('LBL_EMAIL_RECIPIENT_PREFS',  $this->getModuleName()),
				'linkurl' => 'javascript:Vtiger_Index_Js.showRecipientPreferences("' . $this->getModuleName() . '");',
				'linkicon' => ''
			);
			$linkModelList['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($emailRecpLink);
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

		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		if ($currentUserModel->isAdminUser()) {
			$settingsLinks = $moduleModel->getSettingLinks();
			foreach ($settingsLinks as $settingsLink) {
				$linkModelList['DETAILVIEWSETTING'][] = Vtiger_Link_Model::getInstanceFromValues($settingsLink);
			}
		}
		return $linkModelList;
	}
	public function getDetailViewLinks($linkParams) {
		$currentUserModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();

		$linkModelList = $this->getDetailViewLinksIG($linkParams);
		$recordModel = $this->getRecord();

		$invoiceModuleModel = Vtiger_Module_Model::getInstance('Invoice');
		if ($currentUserModel->hasModuleActionPermission($invoiceModuleModel->getId(), 'CreateView')) {
			$basicActionLink = array(
				'linktype' => 'DETAILVIEW',
				'linklabel' => vtranslate('LBL_CREATE') . ' ' . vtranslate($invoiceModuleModel->getSingularLabelKey(), 'Invoice'),
				'linkurl' => $recordModel->getCreateInvoiceUrl(),
				'linkicon' => ''
			);
			$linkModelList['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($basicActionLink);
		}

		$purchaseOrderModuleModel = Vtiger_Module_Model::getInstance('PurchaseOrder');
		if ($currentUserModel->hasModuleActionPermission($purchaseOrderModuleModel->getId(), 'CreateView')) {
			$basicActionLink = array(
				'linktype' => 'DETAILVIEW',
				'linklabel' => vtranslate('LBL_CREATE') . ' ' . vtranslate($purchaseOrderModuleModel->getSingularLabelKey(), 'PurchaseOrder'),
				'linkurl' => $recordModel->getCreatePurchaseOrderUrl(),
				'linkicon' => ''
			);
			$linkModelList['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($basicActionLink);
		}
		return $linkModelList;
	}
}
