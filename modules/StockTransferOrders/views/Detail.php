<?php
include_once('include/utils/GeneralUtils.php');
class StockTransferOrders_Detail_View extends Inventory_Detail_View {
    public function showModuleDetailView(Vtiger_Request $request) {
        $recordId = $request->get('record');
        $moduleName = $request->getModule();
        $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
        $viewer = $this->getViewer($request);
        $viewer->assign('IMAGE_DETAILS', $recordModel->getImageDetails());
        $data = configuredLineItemFieldsWithOutDepend($moduleName);
        $viewer->assign('LINEITEM_CUSTOM_FIELDNAMES', $data['fieldNames']);
        $viewer->assign('LINEITEM_CUSTOM_FIELDS', $data['fields']);
        return parent::showModuleDetailView($request);
    }
}