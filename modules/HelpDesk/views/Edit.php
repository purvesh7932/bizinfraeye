<?php

class HelpDesk_Edit_View extends Inventory_Edit_View {
    public function getHeaderCss(Vtiger_Request $request) {
        $headerCssInstances = parent::getHeaderCss($request);

        $cssFileNames = array(
            "~layouts/" . Vtiger_Viewer::getDefaultLayoutName() . "/modules/Users/build/css/intlTelInput.css",
            "~layouts/" . Vtiger_Viewer::getDefaultLayoutName() . "/modules/HelpDesk/css/select2.min.css"
        );
        $cssInstances = $this->checkAndConvertCssStyles($cssFileNames);
        $headerCssInstances = array_merge($headerCssInstances, $cssInstances);

        return $headerCssInstances;
    }
    function getHeaderScripts(Vtiger_Request $request) {
        $headerScriptInstances = parent::getHeaderScripts($request);

        $jsFileNames = array(
            '~modules/HelpDesk/js/select2.min.js',
        );
        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
        $headerScriptInstances = array_merge($jsScriptInstances, $headerScriptInstances);
        return $headerScriptInstances;
    }

    public function process(Vtiger_Request $request) {
        parent::process($request);
        // $viewer = $this->getViewer($request);
        // $moduleName = $request->getModule();
        // $record = $request->get('record');
        // if (!empty($record) && $request->get('isDuplicate') == true) {
        //     $recordModel = $this->record ? $this->record : Vtiger_Record_Model::getInstanceById($record, $moduleName);
        //     $viewer->assign('MODE', '');

        //     //While Duplicating record, If the related record is deleted then we are removing related record info in record model
        //     $mandatoryFieldModels = $recordModel->getModule()->getMandatoryFieldModels();
        //     foreach ($mandatoryFieldModels as $fieldModel) {
        //         if ($fieldModel->isReferenceField()) {
        //             $fieldName = $fieldModel->get('name');
        //             if (Vtiger_Util_Helper::checkRecordExistance($recordModel->get($fieldName))) {
        //                 $recordModel->set($fieldName, '');
        //             }
        //         }
        //     }
        // } else if (!empty($record)) {
        //     $recordModel = $this->record ? $this->record : Vtiger_Record_Model::getInstanceById($record, $moduleName);
        //     $viewer->assign('RECORD_ID', $record);
        //     $viewer->assign('MODE', 'edit');
        // } else {
        //     $recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
        //     $viewer->assign('MODE', '');
        // }
        // if (!$this->record) {
        //     $this->record = $recordModel;
        // }

        // $moduleModel = $recordModel->getModule();
        // $fieldList = $moduleModel->getFields();
        // $requestFieldList = array_intersect_key($request->getAllPurified(), $fieldList);

        // $relContactId = $request->get('contact_id');
        // if ($relContactId && $moduleName == 'Calendar') {
        //     $contactRecordModel = Vtiger_Record_Model::getInstanceById($relContactId);
        //     $requestFieldList['parent_id'] = $contactRecordModel->get('account_id');
        // }
        // foreach ($requestFieldList as $fieldName => $fieldValue) {
        //     $fieldModel = $fieldList[$fieldName];
        //     $specialField = false;
        //     if ($fieldModel->isEditable() || $specialField) {
        //         $recordModel->set($fieldName, $fieldModel->getDBInsertValue($fieldValue));
        //     }
        // }
        // $recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_EDIT);
        // $picklistDependencyDatasource = Vtiger_DependencyPicklist::getPicklistDependencyDatasource($moduleName);

        // $viewer->assign('PICKIST_DEPENDENCY_DATASOURCE', Vtiger_Functions::jsonEncode($picklistDependencyDatasource));
        // $viewer->assign('RECORD_STRUCTURE_MODEL', $recordStructureInstance);
        // $viewer->assign('RECORD_STRUCTURE', $recordStructureInstance->getStructure());
        // $viewer->assign('MODULE', $moduleName);
        // $viewer->assign('CURRENTDATE', date('Y-n-j'));
        // $viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());

        // $isRelationOperation = $request->get('relationOperation');

        // //if it is relation edit
        // $viewer->assign('IS_RELATION_OPERATION', $isRelationOperation);
        // if ($isRelationOperation) {
        //     $viewer->assign('SOURCE_MODULE', $request->get('sourceModule'));
        //     $viewer->assign('SOURCE_RECORD', $request->get('sourceRecord'));
        // }

        // // added to set the return values
        // if ($request->get('returnview')) {
        //     $request->setViewerReturnValues($viewer);
        // }
        // $viewer->assign('MAX_UPLOAD_LIMIT_MB', Vtiger_Util_Helper::getMaxUploadSize());
        // $viewer->assign('OWNERASSIGNEDCOUNTLIST', $this->getAllAssignedUsersCount());
        // $viewer->assign('OWNERLEAVESTATUSLIST', $this->getAllUserOnLeaveStatus());
        // $viewer->assign('MAX_UPLOAD_LIMIT_BYTES', Vtiger_Util_Helper::getMaxUploadSizeInBytes());
        // $viewer->assign('MANDATORYFIELDS', Vtiger_DependencyPicklist::getFieldsFitDependency('HelpDesk', 'ticket_type', 'purpose'));
        // if ($request->get('displayMode') == 'overlay') {
        //     $viewer->assign('SCRIPTS', $this->getOverlayHeaderScripts($request));
        //     $viewer->view('OverlayEditView.tpl', $moduleName);
        // } else {
        //     $viewer->view('EditView.tpl', $moduleName);
        // }
    }

    public function getAllAssignedUsersCount() {
        global $adb;
        $sql = 'SELECT COUNT(smownerid) as ownercount,smownerid FROM vtiger_troubletickets 
        INNER JOIN vtiger_crmentity on vtiger_crmentity.crmid = vtiger_troubletickets.ticketid
        GROUP BY parent_id';
        $result = $adb->pquery($sql, array());
        $ids = [];
        while ($row = $adb->fetch_array($result)) {
            $ids[$row['smownerid']] = $row['ownercount'];
        }
        return $ids;
    }

    public function getAllUserOnLeaveStatus() {
        global $adb;
        $sql = 'SELECT on_leave, id FROM `vtiger_serviceengineer` 
        INNER JOIN vtiger_users on vtiger_users.user_name = vtiger_serviceengineer.badge_no
        WHERE vtiger_serviceengineer.approval_status = "Accepted"';
        $result = $adb->pquery($sql, array());
        $ids = [];
        while ($row = $adb->fetch_array($result)) {
            if($row['on_leave'] == '1'){
                $ids[$row['id']] = 'On Leave';
            }
        }
        return $ids;
    }
    
    public function getFieldsOfCategory($type, $purposeValue) {
		// if ($type == 'GENERAL INSPECTION' || $type == 'SERVICE FOR SPARES PURCHASED' ) {
		// 	$fieldDependeny = Vtiger_DependencyPicklist::getFieldsFitDependency('HelpDesk', 'purpose', 'ticketstatus');
		// 	$type = $purposeValue;
		// } else {
			$fieldDependeny = Vtiger_DependencyPicklist::getFieldsFitDependency('HelpDesk', 'ticket_type', 'purpose');
		// }
		// foreach ($fieldDependeny['valuemapping'] as $valueMapping) {
		// 	if ($valueMapping['sourcevalue'] == $type) {
		// 		return $valueMapping['targetvalues'];
		// 	}
		// }
	}
}
