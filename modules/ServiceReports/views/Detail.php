<?php
include_once('include/utils/GeneralUtils.php');
class ServiceReports_Detail_View extends Inventory_Detail_View {

    public function showModuleDetailView(Vtiger_Request $request) {
        $recordId = $request->get('record');
        $moduleName = $request->getModule();
        $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
        $viewer = $this->getViewer($request);
        $viewer->assign('IMAGE_DETAILS', $recordModel->getImageDetails());
        $viewer->assign('RELATED_PRODUCTS_OTHER', $recordModel->getProductsOther());
        global $adb;
        $ticketType = $recordModel->get('sr_ticket_type');
        $purpose = $recordModel->get('tck_det_purpose');
        $fieldAllowed = getFieldsOfCategoryServiceReport($ticketType, $purpose);
        $viewer->assign('REPORTTYPE', $purpose);
        $viewer->assign('SERVICEREPORTTYPE', $ticketType);
        $prdoducteditDisabledTypes = [
            'INSTALLATION OF SUB ASSEMBLY FITMENT',
            'PERIODICAL MAINTENANCE',
            'SERVICE FOR SPARES PURCHASED'
        ];
        $viewer->assign('PRODUCTEDITDISABLEDTYPES', $prdoducteditDisabledTypes);
        $tabId = getTabId($moduleName);
        $sql = "SELECT * FROM `vtiger_field` LEFT JOIN vtiger_blocks
         on vtiger_blocks.blockid = vtiger_field.block where vtiger_field.tabid = ? 
         and helpinfo = 'li_lg' and blocklabel = ? ORDER BY `vtiger_field`.`sequence` ASC";
        $result = $adb->pquery($sql, array($tabId, 'LBL_ITEM_DETAILS'));
        $fields = [];
        $fieldNames = [];
        while ($row = $adb->fetch_array($result)) {
            if (!in_array($row['fieldname'], $fieldAllowed)) {
                continue;
            }
            if ($row['uitype'] == '16') {
                $row['picklistValues'] = getAllPickListValues($row['fieldname']);
            }
            array_push($fieldNames, $row['fieldname']);
            array_push($fields, $row);
        }
        $viewer->assign('LINEITEM_CUSTOM_FIELDNAMES', $fieldNames);
        $viewer->assign('LINEITEM_CUSTOM_FIELDS', $fields);

        $sql = "SELECT * FROM `vtiger_field` LEFT JOIN vtiger_blocks
         on vtiger_blocks.blockid = vtiger_field.block where vtiger_field.tabid = ? 
         and helpinfo = 'li_lg' and blocklabel = ? ORDER BY `vtiger_field`.`sequence` ASC;";
        $result = $adb->pquery($sql, array($tabId, 'Shortages_And_Damages'));
        $fields = [];
        $fieldNames = [];
        while ($row = $adb->fetch_array($result)) {
            if (!in_array($row['fieldname'], $fieldAllowed)) {
                continue;
            }
            if ($row['uitype'] == '16') {
                $row['picklistValues'] = getAllPickListValues($row['fieldname']);
            }
            array_push($fieldNames, $row['fieldname']);
            array_push($fields, $row);
        }
        $viewer->assign('LINEITEM_CUSTOM_FIELDNAMES_OTHER', $fieldNames);
        $viewer->assign('LINEITEM_CUSTOM_FIELDS_OTHER', $fields);

        $viewer->assign('BLOCKNAMEFROMDEPENDENCY', $BLOCKNAMEFROMDEPENDENCY);
        $ExtraLineItemRequiredTypes = [
            'ERECTION AND COMMISSIONING', 'PRE-DELIVERY',
            'INSTALLATION OF SUB ASSEMBLY FITMENT',
            'PERIODICAL MAINTENANCE'
        ];
        $viewer->assign('EXTRALINEITEMREQUIREDTYPES', $ExtraLineItemRequiredTypes);
        $ExtraLineItemRequiredSubTypes = ['WARRANTY CLAIM FOR SUB ASSEMBLY / OTHER SPARE PARTS'];
        $viewer->assign('EXTRALINEITEMREQUIREDSUBTYPES', $ExtraLineItemRequiredSubTypes);
        $viewer->assign('ITEMBLOCKNAME', getBlockLableBasedOnType($ticketType, $purpose));
        $viewer->assign('ITEMBLOCKNAMEANOTHER', getSecondBlockLableBasedOnType($ticketType, $purpose));
        if ($ticketType == 'PRE-DELIVERY' || $ticketType == 'ERECTION AND COMMISSIONING') {
            $sql = "SELECT * FROM `vtiger_field` LEFT JOIN vtiger_blocks
                on vtiger_blocks.blockid = vtiger_field.block where vtiger_field.tabid = ? 
                and helpinfo = 'li_lg' and blocklabel = ? and presence != 1 ORDER BY `vtiger_field`.`sequence` ASC;";
            $result = $adb->pquery($sql, array($tabId, 'Major_Aggregates_Sl_No'));
            $fields = [];
            $fieldNames = [];
            $pickListFields = [];
            while ($row = $adb->fetch_array($result)) {
                if (!in_array($row['fieldname'], $fieldAllowed)) {
                    continue;
                }
                if ($row['uitype'] == '16' || $row['uitype'] == '999') {
                    array_push($pickListFields, $row['fieldname']);
                    $row['picklistValues'] = getAllPickListValues($row['fieldname']);
                }
                array_push($fieldNames, $row['fieldname']);
                array_push($fields, $row);
            }
            $viewer->assign('LINEITEM_CUSTOM_FIELDNAMES_OTHER1', $fieldNames);
            $viewer->assign('LINEITEM_CUSTOM_OTHER_PICK_FIELDS1', $pickListFields);
            $viewer->assign('LINEITEM_CUSTOM_FIELDS_OTHER1', $fields);
            if (empty($recordId)) {
                $subAssemblies = array('Engine', 'Transmission');
                $i = 1;
                foreach ($subAssemblies as $subAssembly) {
                    if ($i == 1) {
                        $relatedProductsAnother[1]['masn_aggrregate1'] = $subAssembly;
                    } else {
                        array_push($relatedProductsAnother, array('masn_aggrregate' . $i => $subAssembly));
                    }
                    $i = $i + 1;
                }
                $viewer->assign('RELATED_PRODUCTS_OTHER1', $relatedProductsAnother);
            } else {
                $viewer->assign('RELATED_PRODUCTS_OTHER1', $recordModel->getProductsOther2());
            }
        }
        $viewer->assign('RECORDID', $recordId);
        return parent::showModuleDetailView($request);
    }
}
