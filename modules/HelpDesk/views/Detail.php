<?php
class HelpDesk_Detail_View extends Inventory_Detail_View {

	function __construct() {
		parent::__construct();
		$this->exposeMethod('showRelatedRecords');
	}

	public function showModuleDetailView(Vtiger_Request $request) {

		$recordId = $request->get('record');
		$moduleName = $request->getModule();
		$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
		$viewer = $this->getViewer($request);
		$viewer->assign('IMAGE_DETAILS', $recordModel->getImageDetails());
		$viewer->assign('RECORDID', $recordId);
		return parent::showModuleDetailView($request);
	}

	/**
	 * Function to get activities
	 * @param Vtiger_Request $request
	 * @return <List of activity models>
	 */
	public function getActivities(Vtiger_Request $request) {
		$moduleName = 'Calendar';
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);

		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if ($currentUserPriviligesModel->hasModulePermission($moduleModel->getId())) {
			$moduleName = $request->getModule();
			$recordId = $request->get('record');

			$pageNumber = $request->get('page');
			if (empty($pageNumber)) {
				$pageNumber = 1;
			}
			$pagingModel = new Vtiger_Paging_Model();
			$pagingModel->set('page', $pageNumber);
			$pagingModel->set('limit', 10);

			if (!$this->record) {
				$this->record = Vtiger_DetailView_Model::getInstance($moduleName, $recordId);
			}
			$recordModel = $this->record->getRecord();
			$moduleModel = $recordModel->getModule();

			$relatedActivities = $moduleModel->getCalendarActivities('', $pagingModel, 'all', $recordId);

			$viewer = $this->getViewer($request);
			$viewer->assign('RECORD', $recordModel);
			$viewer->assign('MODULE_NAME', $moduleName);
			$viewer->assign('PAGING_MODEL', $pagingModel);
			$viewer->assign('PAGE_NUMBER', $pageNumber);
			$viewer->assign('ACTIVITIES', $relatedActivities);
			return $viewer->view('RelatedActivities.tpl', $moduleName, true);
		}
	}

	function preProcess(Vtiger_Request $request, $display = true) {
		parent::preProcess($request, false);

		// $recordId = $request->get('record');
		// $moduleName = $request->getModule();
		// if (!$this->record) {
		// 	$this->record = Vtiger_DetailView_Model::getInstance($moduleName, $recordId);
		// }
		// $recordModel = $this->record->getRecord();
		// $recordStrucure = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_DETAIL);
		// $summaryInfo = array();
		// // Take first block information as summary information
		// $stucturedValues = $recordStrucure->getStructure();
		// foreach ($stucturedValues as $blockLabel => $fieldList) {
		// 	$summaryInfo[$blockLabel] = $fieldList;
		// 	break;
		// }

		// $detailViewLinkParams = array('MODULE' => $moduleName, 'RECORD' => $recordId);

		// $detailViewLinks = $this->record->getDetailViewLinks($detailViewLinkParams);
		// $navigationInfo = ListViewSession::getListViewNavigation($recordId);

		// $viewer = $this->getViewer($request);
		// $viewer->assign('RECORD', $recordModel);
		// $viewer->assign('NAVIGATION', $navigationInfo);

		// //Intially make the prev and next records as null
		// $prevRecordId = null;
		// $nextRecordId = null;
		// $found = false;
		// if ($navigationInfo) {
		// 	foreach ($navigationInfo as $page => $pageInfo) {
		// 		foreach ($pageInfo as $index => $record) {
		// 			//If record found then next record in the interation
		// 			//will be next record
		// 			if ($found) {
		// 				$nextRecordId = $record;
		// 				break;
		// 			}
		// 			if ($record == $recordId) {
		// 				$found = true;
		// 			}
		// 			//If record not found then we are assiging previousRecordId
		// 			//assuming next record will get matched
		// 			if (!$found) {
		// 				$prevRecordId = $record;
		// 			}
		// 		}
		// 		//if record is found and next record is not calculated we need to perform iteration
		// 		if ($found && !empty($nextRecordId)) {
		// 			break;
		// 		}
		// 	}
		// }

		// $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		// if (!empty($prevRecordId)) {
		// 	$viewer->assign('PREVIOUS_RECORD_URL', $moduleModel->getDetailViewUrl($prevRecordId));
		// }
		// if (!empty($nextRecordId)) {
		// 	$viewer->assign('NEXT_RECORD_URL', $moduleModel->getDetailViewUrl($nextRecordId));
		// }

		// $viewer->assign('MODULE_MODEL', $this->record->getModule());
		// $viewer->assign('DETAILVIEW_LINKS', $detailViewLinks);

		// $viewer->assign('IS_EDITABLE', $this->record->getRecord()->isEditable($moduleName));
		// $viewer->assign('IS_DELETABLE', $this->record->getRecord()->isDeletable($moduleName));

		// $linkParams = array('MODULE' => $moduleName, 'ACTION' => $request->get('view'));
		// $linkModels = $this->record->getSideBarLinks($linkParams);
		// $viewer->assign('QUICK_LINKS', $linkModels);
		// $viewer->assign('MODULE_NAME', $moduleName);

		// $currentUserModel = Users_Record_Model::getCurrentUserModel();
		// $viewer->assign('DEFAULT_RECORD_VIEW', $currentUserModel->get('default_record_view'));

		// $picklistDependencyDatasource = Vtiger_DependencyPicklist::getPicklistDependencyDatasource($moduleName);
		// $viewer->assign('PICKIST_DEPENDENCY_DATASOURCE', Vtiger_Functions::jsonEncode($picklistDependencyDatasource));

		// $tagsList = Vtiger_Tag_Model::getAllAccessible($currentUserModel->getId(), $moduleName, $recordId);
		// $allUserTags = Vtiger_Tag_Model::getAllUserTags($currentUserModel->getId());
		// $viewer->assign('TAGS_LIST', $tagsList);
		// $viewer->assign('ALL_USER_TAGS', $allUserTags);
		// $appName = $request->get('app');
		// if (!empty($appName)) {
		// 	$viewer->assign('SELECTED_MENU_CATEGORY', $appName);
		// }

		// $selectedTabLabel = $request->get('tab_label');
		// $relationId = $request->get('relationId');

		// if (empty($selectedTabLabel)) {
		// 	if ($currentUserModel->get('default_record_view') === 'Detail') {
		// 		$selectedTabLabel = vtranslate('SINGLE_' . $moduleName, $moduleName) . ' ' . vtranslate('LBL_DETAILS', $moduleName);
		// 	} else {
		// 		if ($moduleModel->isSummaryViewSupported()) {
		// 			$selectedTabLabel = vtranslate('SINGLE_' . $moduleName, $moduleName) . ' ' . vtranslate('LBL_SUMMARY', $moduleName);
		// 		} else {
		// 			$selectedTabLabel = vtranslate('SINGLE_' . $moduleName, $moduleName) . ' ' . vtranslate('LBL_DETAILS', $moduleName);
		// 		}
		// 	}
		// }

		// $viewer->assign('SELECTED_TAB_LABEL', $selectedTabLabel);
		// $viewer->assign('SELECTED_RELATION_ID', $relationId);

		// //Vtiger7 - TO show custom view name in Module Header
		// $viewer->assign('CUSTOM_VIEWS', CustomView_Record_Model::getAllByGroup($moduleName));

		// $viewer->assign('IS_AJAX_ENABLED', $this->isAjaxEnabled($recordModel));
		// $viewer->assign('isItRecommissioningReport', $this->isItRecommissioningReport($recordId));
		// if ($display) {
		// 	$this->preProcessDisplay($request);
		// }
	}

	public function isItRecommissioningReport($id) {
		global $adb;
		$sql = 'select recommissioningreportsid from vtiger_recommissioningreports'
			. ' INNER JOIN vtiger_crmentity '
			. ' ON vtiger_crmentity.crmid = vtiger_recommissioningreports.recommissioningreportsid '
			. ' where vtiger_recommissioningreports.ticket_id = ? and vtiger_crmentity.deleted = 0';
		$sqlResult = $adb->pquery($sql, array($id));
		$num_rows = $adb->num_rows($sqlResult);
		if ($num_rows > 0) {
			return true;
		} else {
			return false;
		}
	}
}
