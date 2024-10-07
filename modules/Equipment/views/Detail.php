<?php
class Equipment_Detail_View extends Vtiger_Detail_View {

    public function showModuleDetailView(Vtiger_Request $request) {
        $recordId = $request->get('record');
        $moduleName = $request->getModule();
        $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
        $viewer = $this->getViewer($request);
        global $adb;
        $tabId = getTabId($moduleName);
        $sql = "SELECT * FROM `vtiger_field` LEFT JOIN vtiger_blocks
			on vtiger_blocks.blockid = vtiger_field.block where vtiger_field.tabid = ? 
			and helpinfo = 'li_lg' and blocklabel = ? and presence != 1 ORDER BY `vtiger_field`.`sequence` ASC;";
        $result = $adb->pquery($sql, array($tabId, 'daadcp_lineblock'));
        $fields = [];
        $fieldNames = [];
        $pickListFields = [];
        $dependentList = array();
        while ($row = $adb->fetch_array($result)) {
            if ($row['uitype'] == '16' || $row['uitype'] == '999') {
                array_push($pickListFields, $row['fieldname']);
                $row['picklistValues'] = getAllPickListValues($row['fieldname']);
            }
            if (in_array($row['fieldname'], $dependentList)) {
                $row['hideInitialDisplay'] = 'true';
            }
            array_push($fieldNames, $row['fieldname']);
            array_push($fields, $row);
        }

        $viewer->assign('LINEITEM_CUSTOM_FIELDNAMES_OTHER1', $fieldNames);
        $viewer->assign('LINEITEM_CUSTOM_OTHER_PICK_FIELDS1', $pickListFields);
        $viewer->assign('LINEITEM_CUSTOM_FIELDS_OTHER1', $fields);
        if (!empty($recordId)) {
            $relatedLines = $recordModel->getProductsOther2();
            $noOfYearsOfContract = (int) $recordModel->get('total_year_cont') + 1;
            $i = 1;
            for ($i = 1; $i < $noOfYearsOfContract; $i++) {
                if ($i == 1) {
                    $relatedProductsAnother[1]['daadcp_contra_lable1'] = '1 year Contract';
                    $relatedProductsAnother[1]['daadcp_avail_sl_no1'] = 1;
                    $relatedProductsAnother[1]['daadcp_avail_percent1'] = $relatedLines[$i]['daadcp_avail_percent' . $i];
                    $relatedProductsAnother[1]['daadcp_avail_mon_percent1'] = $relatedLines[$i]['daadcp_avail_mon_percent' . $i];
                } else {
                    array_push($relatedProductsAnother, array(
                        'daadcp_contra_lable' . $i => "$i year Contract",
                        'daadcp_avail_sl_no' . $i => $i,
                        'daadcp_avail_percent' . $i => $relatedLines[$i]['daadcp_avail_percent' . $i],
                        'daadcp_avail_mon_percent' . $i => $relatedLines[$i]['daadcp_avail_mon_percent' . $i]
                    ));
                }
            }
            if (empty($noOfYearsOfContract)) {
                $viewer->assign('RELATED_PRODUCTS_OTHER1', []);
            } else {
                $viewer->assign('RELATED_PRODUCTS_OTHER1', $relatedProductsAnother);
            }
        }

        return parent::showModuleDetailView($request);
    }

    function showModuleBasicView(Vtiger_Request $request) {

		$recordId = $request->get('record');
		$moduleName = $request->getModule();

		if(!$this->record){
			$this->record = Vtiger_DetailView_Model::getInstance($moduleName, $recordId);
		}
		$recordModel = $this->record->getRecord();

		$detailViewLinkParams = array('MODULE'=>$moduleName,'RECORD'=>$recordId);
		$detailViewLinks = $this->record->getDetailViewLinks($detailViewLinkParams);

		$viewer = $this->getViewer($request);
		$viewer->assign('RECORD', $recordModel);
		$viewer->assign('MODULE_SUMMARY', $this->showModuleSummaryView($request));

		$viewer->assign('DETAILVIEW_LINKS', $detailViewLinks);
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('IS_AJAX_ENABLED', $this->isAjaxEnabled($recordModel));
		$viewer->assign('MODULE_NAME', $moduleName);

		$recordStrucure = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_DETAIL);
		$structuredValues = $recordStrucure->getStructure();

		$moduleModel = $recordModel->getModule();
		$viewer->assign('CURRENT_USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('RECORD_STRUCTURE', $structuredValues);
		$viewer->assign('BLOCK_LIST', $moduleModel->getBlocks());
        $viewer = $this->getViewer($request);
        global $adb;
        $tabId = getTabId($moduleName);
        $sql = "SELECT * FROM `vtiger_field` LEFT JOIN vtiger_blocks
			on vtiger_blocks.blockid = vtiger_field.block where vtiger_field.tabid = ? 
			and helpinfo = 'li_lg' and blocklabel = ? and presence != 1 ORDER BY `vtiger_field`.`sequence` ASC;";
        $result = $adb->pquery($sql, array($tabId, 'daadcp_lineblock'));
        $fields = [];
        $fieldNames = [];
        $pickListFields = [];
        $dependentList = array();
        while ($row = $adb->fetch_array($result)) {
            if ($row['uitype'] == '16' || $row['uitype'] == '999') {
                array_push($pickListFields, $row['fieldname']);
                $row['picklistValues'] = getAllPickListValues($row['fieldname']);
            }
            if (in_array($row['fieldname'], $dependentList)) {
                $row['hideInitialDisplay'] = 'true';
            }
            array_push($fieldNames, $row['fieldname']);
            array_push($fields, $row);
        }

        $viewer->assign('LINEITEM_CUSTOM_FIELDNAMES_OTHER1', $fieldNames);
        $viewer->assign('LINEITEM_CUSTOM_OTHER_PICK_FIELDS1', $pickListFields);
        $viewer->assign('LINEITEM_CUSTOM_FIELDS_OTHER1', $fields);
        if (!empty($recordId)) {
            $relatedLines = $recordModel->getProductsOther2();
            $noOfYearsOfContract = (int) $recordModel->get('total_year_cont') + 1;
            $i = 1;
            for ($i = 1; $i < $noOfYearsOfContract; $i++) {
                if ($i == 1) {
                    $relatedProductsAnother[1]['daadcp_contra_lable1'] = '1 year Contract';
                    $relatedProductsAnother[1]['daadcp_avail_sl_no1'] = 1;
                    $relatedProductsAnother[1]['daadcp_avail_percent1'] = $relatedLines[$i]['daadcp_avail_percent' . $i];
                    $relatedProductsAnother[1]['daadcp_avail_mon_percent1'] = $relatedLines[$i]['daadcp_avail_mon_percent' . $i];
                } else {
                    array_push($relatedProductsAnother, array(
                        'daadcp_contra_lable' . $i => "$i year Contract",
                        'daadcp_avail_sl_no' . $i => $i,
                        'daadcp_avail_percent' . $i => $relatedLines[$i]['daadcp_avail_percent' . $i],
                        'daadcp_avail_mon_percent' . $i => $relatedLines[$i]['daadcp_avail_mon_percent' . $i]
                    ));
                }
            }
            if (empty($noOfYearsOfContract)) {
                $viewer->assign('RELATED_PRODUCTS_OTHER1', []);
            } else {
                $viewer->assign('RELATED_PRODUCTS_OTHER1', $relatedProductsAnother);
            }
        }
        
		echo $viewer->view('DetailViewSummaryContents.tpl', $moduleName, true);
	}
}
