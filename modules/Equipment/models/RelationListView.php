<?php

class Equipment_RelationListView_Model extends Vtiger_RelationListView_Model {

    public function getCreateViewUrl() {
        $createViewUrl = parent::getCreateViewUrl();
        $parentRecordModel = $this->getParentRecordModel();
        $accountId = $parentRecordModel->get('id');
        return $createViewUrl.'&equipment_id='.$accountId;
    }

}
