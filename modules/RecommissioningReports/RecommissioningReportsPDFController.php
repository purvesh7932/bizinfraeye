<?php

include_once 'include/InventoryPDFController.php';

class vtiger_RecommissioningReportsPDFController extends Vtiger_InventoryPDFController {

    function buildHeaderModelTitle() {
        $singularModuleNameKey = 'SINGLE_' . $this->moduleName;
        $translatedSingularModuleLabel = getTranslatedString($singularModuleNameKey, $this->moduleName);
        if ($translatedSingularModuleLabel == $singularModuleNameKey) {
            $translatedSingularModuleLabel = getTranslatedString($this->moduleName, $this->moduleName);
        }
        return sprintf("%s: %s", $translatedSingularModuleLabel, $this->focusColumnValue('quote_no'));
    }

}
