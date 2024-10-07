<?php
class SalesOrder_Detail_View extends Inventory_Detail_View {

    public function showModuleDetailView(Vtiger_Request $request) {
        $recordId = $request->get('record');
        $moduleName = $request->getModule();
        $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
        $viewer = $this->getViewer($request);
        global $adb;
        $tabId = getTabId($moduleName);
        $sql = "SELECT * FROM `vtiger_field` LEFT JOIN vtiger_blocks
        on vtiger_blocks.blockid = vtiger_field.block where vtiger_field.tabid = ? 
        and helpinfo = 'li_lg' and presence=2 and blocklabel = ? ORDER BY `vtiger_field`.`sequence` ASC";
        $result = $adb->pquery($sql, array($tabId, 'LBL_ITEM_DETAILS'));
        $fields = [];
        $fieldNames = [];
        while ($row = $adb->fetch_array($result)) {
            if ($row['fieldname'] == 'so_creatable_qty') {
				continue;
			}
            if ($row['fieldname'] == 'failedpart_lineid') {
				continue;
			}
            if ($row['uitype'] == '16') {
                $row['picklistValues'] = getAllPickListValues($row['fieldname']);
            }
            array_push($fieldNames, $row['fieldname']);
            array_push($fields, $row);
        }
        $external_app_num = $recordModel->get('external_app_num');
        if (empty($external_app_num)) {
            $viewer->assign('CAN_CREATE_RSO', false);
        } else {
            $viewer->assign('CAN_CREATE_RSO', true);
        }
        $viewer->assign('LINEITEM_CUSTOM_FIELDNAMES', $fieldNames);
        $viewer->assign('LINEITEM_CUSTOM_FIELDS', $fields);

        return parent::showModuleDetailView($request);
    }
}
