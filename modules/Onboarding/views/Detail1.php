<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Onboarding_Detail_View extends Vtiger_Detail_View {

	function __construct() {
		parent::__construct();
	}

	public function showModuleDetailView(Vtiger_Request $request) {
		$recordId = $request->get('record');
		$moduleName = $request->getModule();

		// Getting model to reuse it in parent 
		if (!$this->record) {
			$this->record = Vtiger_DetailView_Model::getInstance($moduleName, $recordId);
		}
		$recordModel = $this->record->getRecord();
		$viewer = $this->getViewer($request);
		//$viewer->assign('IMAGE_DETAILS', $recordModel->getImageDetails());
		
		global $adb, $site_URL;
		$sql = "SELECT vtiger_attachments.*, vtiger_crmentity.setype FROM vtiger_attachments
						INNER JOIN vtiger_seattachmentsrel ON vtiger_seattachmentsrel.attachmentsid = vtiger_attachments.attachmentsid
						INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_attachments.attachmentsid
						WHERE vtiger_seattachmentsrel.crmid = ?";

		$result = $adb->pquery($sql, array($recordId));

		$imageId = $adb->query_result($result, 0, 'attachmentsid');
		$imagePath = $adb->query_result($result, 0, 'path');
		$imageName = $adb->query_result($result, 0, 'name');
        $url = \Vtiger_Functions::getFilePublicURL($imageId, $imageName);
		//decode_html - added to handle UTF-8 characters in file names
		$imageOriginalName = urlencode(decode_html($imageName));
        if($url) {
            $url = $site_URL.$url;
        }
        
		if(!empty($imageName)){
			$imageDetails[] = array(
				'id' => $imageId,
				'orgname' => $imageOriginalName,
				'path' => $imagePath.$imageId,
				'name' => $imageName,
                'url'  => $url
			);
		}
     
		$viewer->assign('FILE_URL', $imageDetails[0]['url']);
	
		return parent::showModuleDetailView($request);
	}

	function showModuleSummaryView($request) {
		$recordId = $request->get('record');
		$moduleName = $request->getModule();

		if(!$this->record){
			$this->record = Vtiger_DetailView_Model::getInstance($moduleName, $recordId);
		}
		$recordModel = $this->record->getRecord();
		$recordStrucure = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_SUMMARY);

		$moduleModel = $recordModel->getModule();
		$viewer = $this->getViewer($request);
		$viewer->assign('RECORD', $recordModel);
		$viewer->assign('BLOCK_LIST', $moduleModel->getBlocks());
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());

		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('IS_AJAX_ENABLED', $this->isAjaxEnabled($recordModel));
		$viewer->assign('SUMMARY_RECORD_STRUCTURE', $recordStrucure->getStructure());
		$viewer->assign('RELATED_ACTIVITIES', $this->getActivities($request));

		$viewer->assign('CURRENT_USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$pagingModel = new Vtiger_Paging_Model();
		$viewer->assign('PAGING_MODEL', $pagingModel);

		$picklistDependencyDatasource = Vtiger_DependencyPicklist::getPicklistDependencyDatasource($moduleName);
		$viewer->assign('PICKIST_DEPENDENCY_DATASOURCE', Vtiger_Functions::jsonEncode($picklistDependencyDatasource));
		$viewer->assign('IMAGE_DETAILS', $recordModel->getImageDetails());

		global $adb, $site_URL;
		$sql = "SELECT vtiger_attachments.*, vtiger_crmentity.setype FROM vtiger_attachments
						INNER JOIN vtiger_seattachmentsrel ON vtiger_seattachmentsrel.attachmentsid = vtiger_attachments.attachmentsid
						INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_attachments.attachmentsid
						WHERE vtiger_seattachmentsrel.crmid = ?";

		$result = $adb->pquery($sql, array($recordId));

		$imageId = $adb->query_result($result, 0, 'attachmentsid');
		$imagePath = $adb->query_result($result, 0, 'path');
		$imageName = $adb->query_result($result, 0, 'name');
        $url = \Vtiger_Functions::getFilePublicURL($imageId, $imageName);
		//decode_html - added to handle UTF-8 characters in file names
		$imageOriginalName = urlencode(decode_html($imageName));
        if($url) {
            $url = $site_URL.$url;
        }
        
		if(!empty($imageName)){
			$imageDetails[] = array(
				'id' => $imageId,
				'orgname' => $imageOriginalName,
				'path' => $imagePath.$imageId,
				'name' => $imageName,
                'url'  => $url
			);
		}
     
		$viewer->assign('FILE_URL', $imageDetails[0]['url']);

		return $viewer->view('ModuleSummaryView.tpl', $moduleName, true);
	}
}
