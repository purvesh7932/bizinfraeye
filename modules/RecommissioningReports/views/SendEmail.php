<?php

class RecommissioningReports_SendEmail_View extends Inventory_SendEmail_View {

    public function composeMailData(Vtiger_Request $request) {
        $recordId = $request->get('record');
        if (isRecordExists($recordId)) {
            $record = Vtiger_Record_Model::getInstanceById($recordId);

            $viewer = $this->getViewer($request);
            $viewer->assign('SUBJECT', $record->getName());
        }
        parent::composeMailData($request);
    }

}

?>
