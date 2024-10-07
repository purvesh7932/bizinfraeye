<?php

class ServiceOrders_DetailView_Model extends Inventory_DetailView_Model {
    public function getDetailViewLinks($linkParams) {
        $linkTypes = array('DETAILVIEWBASIC', 'DETAILVIEW');
        $moduleModel = $this->getModule();
        $recordModel = $this->getRecord();

        $moduleName = $moduleModel->getName();
        $recordId = $recordModel->getId();

        $detailViewLink = array();
        $linkModelList = array();
        $alreadyOrderSynced = $this->alreadyOrderSynced($recordId);
        if (Users_Privileges_Model::isPermitted($moduleName, 'EditView', $recordId) && $alreadyOrderSynced == false) {
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

        // if ($moduleModel->isDuplicateOptionAllowed('CreateView', $recordId)) {
        //     $duplicateLinkModel = array(
        //         'linktype' => 'DETAILVIEWBASIC',
        //         'linklabel' => 'LBL_DUPLICATE',
        //         'linkurl' => $recordModel->getDuplicateRecordUrl(),
        //         'linkicon' => ''
        //     );
        //     $linkModelList['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($duplicateLinkModel);
        // }

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
        $sql = 'select external_app_num from vtiger_serviceorders '
        . ' where vtiger_serviceorders.serviceordersid = ?';
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
}
