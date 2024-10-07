<?php
include_once('include/utils/GeneralConfigUtils.php');
class ServiceReports_DetailView_Model extends Inventory_DetailView_Model {

    public function ParentgetDetailViewLinks($linkParams) {
        $linkTypes = array('DETAILVIEWBASIC', 'DETAILVIEW');
        $moduleModel = $this->getModule();
        $recordModel = $this->getRecord();

        $moduleName = $moduleModel->getName();
        $recordId = $recordModel->getId();

        $detailViewLink = array();
        $linkModelList = array();
        $alreadyOrderSynced = $this->alreadyOrderSynced($recordModel->get('ticket_id'));
        $isSubmitted = $recordModel->get('is_submitted');
        $isRecommisioningReport = $recordModel->get('is_recommisionreport');
        $reportType = $recordModel->get('sr_ticket_type');
        if (Users_Privileges_Model::isPermitted($moduleName, 'EditView', $recordId)) {
            if ($isSubmitted != '1' && $isRecommisioningReport != '1') {
                $detailViewLinks[] = array(
                    'linktype' => 'DETAILVIEWBASIC',
                    'linklabel' => 'LBL_EDIT',
                    'linkurl' => $recordModel->getEditViewUrl(),
                    'linkicon' => ''
                );

                foreach ($detailViewLinks as $detailViewLink) {
                    $linkModelList['DETAILVIEWBASIC'][] = Vtiger_Link_Model::getInstanceFromValues($detailViewLink);
                }
            } else if ($isSubmitted == '1' &&  $alreadyOrderSynced == false && !isSAPTypeIsNotDefined($reportType) && $isRecommisioningReport != '1') {
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

        if ($moduleModel->isDuplicateOptionAllowed('CreateView', $recordId)) {
            $duplicateLinkModel = array(
                'linktype' => 'DETAILVIEWBASIC',
                'linklabel' => 'LBL_DUPLICATE',
                'linkurl' => $recordModel->getDuplicateRecordUrl(),
                'linkicon' => ''
            );
            $linkModelList['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($duplicateLinkModel);
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

    public function alreadyOrderSynced($id) {
        global $adb;
        $sql = 'select external_app_num from vtiger_troubletickets '
            . ' where vtiger_troubletickets.ticketid = ?';
        $sqlResult = $adb->pquery($sql, array($id));
        $num_rows = $adb->num_rows($sqlResult);
        if ($num_rows > 0) {
            $dataRow = $adb->fetchByAssoc($sqlResult, 0);
            if (empty($dataRow['external_app_num'])) {
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }

    public function getDetailViewLinks($linkParams) {
        $currentUserModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();

        $linkModelList = $this->ParentgetDetailViewLinks($linkParams);
        $recordModel = $this->getRecord();

        $quotesModuleModel = Vtiger_Module_Model::getInstance('Faq');
        if ($currentUserModel->hasModuleActionPermission($quotesModuleModel->getId(), 'CreateView')) {
            $basicActionLink = array(
                'linktype' => 'DETAILVIEW',
                'linklabel' => 'LBL_CONVERT_FAQ',
                'linkurl' => $this->getConvertFAQUrl() . $recordModel->getId(),
                'linkicon' => ''
            );
            $linkModelList['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($basicActionLink);
        }

        return $linkModelList;
    }
    public function getConvertFAQUrl() {
        return "index.php?module=ServiceReports&action=ConvertFAQ&record=";
    }
}
