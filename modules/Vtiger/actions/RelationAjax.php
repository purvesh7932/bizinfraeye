<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Vtiger_RelationAjax_Action extends Vtiger_Action_Controller {
	function __construct() {
		parent::__construct();
		$this->exposeMethod('addRelation');
		$this->exposeMethod('deleteRelation');
        $this->exposeMethod('deleteRelationEnhanced');
		$this->exposeMethod('getRelatedListPageCount');
		$this->exposeMethod('getRelatedRecordInfo');
	}

	public function requiresPermission(Vtiger_Request $request){
		$permissions = parent::requiresPermission($request);
		$mode = $request->getMode();
		if(!empty($mode)) {
			switch ($mode) {
				case 'addRelation':
				case 'deleteRelation':
                                case  'deleteRelationEnhanced':
					$permissions[] = array('module_parameter' => 'module', 'action' => 'DetailView', 'record_parameter' => 'src_record');
					$permissions[] = array('module_parameter' => 'related_module', 'action' => 'DetailView');
					break;
				case 'getRelatedListPageCount':
					$permissions[] = array('module_parameter' => 'module', 'action' => 'DetailView', 'record_parameter' => 'record');
					$permissions[] = array('module_parameter' => 'relatedModule', 'action' => 'DetailView');
				case 'getRelatedRecordInfo':
					$permissions[] = array('module_parameter' => 'module', 'action' => 'DetailView', 'record_parameter' => 'id');
				default:
					break;
			}
		}
		return $permissions;
	}
	
	function checkPermission(Vtiger_Request $request) {
 		return parent::checkPermission($request);
	}

	function preProcess(Vtiger_Request $request) {
		return true;
	}

	function postProcess(Vtiger_Request $request) {
		return true;
	}

	function process(Vtiger_Request $request) {
		$mode = $request->get('mode');
		if(!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
	}

	/*
	 * Function to add relation for specified source record id and related record id list
	 * @param <array> $request
	 *		keys					Content
	 *		src_module				source module name
	 *		src_record				source record id
	 *		related_module			related module name
	 *		related_record_list		json encoded of list of related record ids
	 */
	function addRelation($request) {
		global $adb;
		$sourceModule = $request->getModule();
		$sourceRecordId = $request->get('src_record');

		$relatedModule = $request->get('related_module');
		$relatedRecordIdList = $request->get('related_record_list');

		if ($sourceModule == 'ServiceEngineer' && $relatedModule == 'FunctionalLocations') {
			require_once('include/utils/GeneralUtils.php');
			$relquery = $adb->pquery("SELECT service_engineer_name,badge_no,designaion,
			regional_office,district_office,activity_centre,office
			 FROM `vtiger_serviceengineer` where serviceengineerid= ?", array($sourceRecordId));
			$name = $adb->query_result($relquery, 0, 'service_engineer_name');
			$badge_no = $adb->query_result($relquery, 0, 'badge_no');
			$designation = $adb->query_result($relquery, 0, 'designaion');
			$roffice = $adb->query_result($relquery, 0, 'regional_office');
			$officeValue = $adb->query_result($relquery, 0, 'office');
			$doffice = '';
			if ($officeValue == 'Activity Centre') {
				$doffice = $adb->query_result($relquery, 0, 'activity_centre');
			} else if ($officeValue == 'District Office') {
				$doffice = $adb->query_result($relquery, 0, 'district_office');
			}
		}
		
		if ($relatedRecordIdList != 'all') {
			$sourceModuleModel = Vtiger_Module_Model::getInstance($sourceModule);
			$relatedModuleModel = Vtiger_Module_Model::getInstance($relatedModule);
			$relationModel = Vtiger_Relation_Model::getInstance($sourceModuleModel, $relatedModuleModel);

			if ($sourceModule == 'ServiceEngineer' && $relatedModule == 'FunctionalLocations') {
				foreach ($relatedRecordIdList as $relatedRecordId) {
					$checkpresence = $adb->pquery("SELECT crmid FROM vtiger_crmentityrel WHERE
					crmid = ? AND module = ? AND relcrmid = ? AND relmodule = ?", array($sourceRecordId, 'ServiceEngineer', $relatedRecordId, 'FunctionalLocations'));
					if ($checkpresence && $adb->num_rows($checkpresence))
						continue;
					$adb->pquery("INSERT INTO vtiger_crmentityrel(crmid, module, relcrmid, relmodule) VALUES(?,?,?,?)", array($sourceRecordId, 'ServiceEngineer', $relatedRecordId, 'FunctionalLocations'));

					$anotherRelations = getAllAssociatedEquipmentsBasedSELInkedInEmployeeFunc($relatedRecordId);

					foreach ($anotherRelations as $anotherRelation) {
						// $eqRelationModel->addRelation($sourceRecordId,$anotherRelation);
						$checkpresence = $adb->pquery("SELECT crmid FROM vtiger_crmentityrel WHERE
														crmid = ? AND module = ? AND relcrmid = ? AND relmodule = ?", array($sourceRecordId, 'ServiceEngineer', $anotherRelation, 'Equipment'));
						if ($checkpresence && $adb->num_rows($checkpresence))
							continue;
						$adb->pquery("INSERT INTO vtiger_crmentityrel(crmid, module, relcrmid, relmodule) VALUES(?,?,?,?)", array($sourceRecordId, 'ServiceEngineer', $anotherRelation, 'Equipment'));
						$adb->pquery(
							"update `vtiger_equipmentcf` set 
							ser_eng_name=?,badge_no=?,
							sr_designaion=?,sr_regional_office=?,
							dist_off_or_act_cen=? where equipmentid= ?",
							array(
								$name, $badge_no, $designation, $roffice,
								$doffice, $anotherRelation
							)
						);
					}
				}
			} else {
				foreach($relatedRecordIdList as $relatedRecordId) {
					$response = $relationModel->addRelation($sourceRecordId,$relatedRecordId);
				}
			}
		} else {
			$recordIds = $this->getAllRecordsToAddInthePopUp($request);
            $sourceModuleModel = Vtiger_Module_Model::getInstance($sourceModule);
            $relatedModuleModel = Vtiger_Module_Model::getInstance($relatedModule);
            $relationModel = Vtiger_Relation_Model::getInstance($sourceModuleModel, $relatedModuleModel);
            $excludedIds = $request->get('excluded_ids');
            if(empty($excludedIds)){
                $excludedIds = [];
            }

			if ($sourceModule == 'ServiceEngineer' && $relatedModule == 'FunctionalLocations') {
				require_once('include/utils/GeneralUtils.php');
			}

            $recordIds = array_diff($recordIds, $excludedIds);
            if ($sourceModule == 'ServiceEngineer' && $relatedModule == 'FunctionalLocations') {
				foreach ($recordIds as $relatedRecordId) {
					$checkpresence = $adb->pquery("SELECT crmid FROM vtiger_crmentityrel WHERE
					crmid = ? AND module = ? AND relcrmid = ? AND relmodule = ?", array($sourceRecordId, 'ServiceEngineer', $relatedRecordId, 'FunctionalLocations'));
					if ($checkpresence && $adb->num_rows($checkpresence))
						continue;
					$adb->pquery("INSERT INTO vtiger_crmentityrel(crmid, module, relcrmid, relmodule) VALUES(?,?,?,?)", array($sourceRecordId, 'ServiceEngineer', $relatedRecordId, 'FunctionalLocations'));

					$anotherRelations = getAllAssociatedEquipmentsBasedSELInkedInEmployeeFunc($relatedRecordId);

					foreach ($anotherRelations as $anotherRelation) {
						// $eqRelationModel->addRelation($sourceRecordId,$anotherRelation);
						$checkpresence = $adb->pquery("SELECT crmid FROM vtiger_crmentityrel WHERE
					crmid = ? AND module = ? AND relcrmid = ? AND relmodule = ?", array($sourceRecordId, 'ServiceEngineer', $anotherRelation, 'Equipment'));
						if ($checkpresence && $adb->num_rows($checkpresence))
							continue;
						$adb->pquery("INSERT INTO vtiger_crmentityrel(crmid, module, relcrmid, relmodule) VALUES(?,?,?,?)", array($sourceRecordId, 'ServiceEngineer', $anotherRelation, 'Equipment'));
						$adb->pquery(
							"update `vtiger_equipmentcf` set 
							ser_eng_name=?,badge_no=?,
							sr_designaion=?,sr_regional_office=?,
							dist_off_or_act_cen=? where equipmentid= ?",
							array($name, $badge_no, $designation, $roffice, $doffice, $anotherRelation)
						);
					}
				}
			} else {
				foreach($recordIds as $relatedRecordId) {
					$response = $relationModel->addRelation($sourceRecordId,$relatedRecordId);
				}
			}
		}

		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}

	/**
	 * Function to delete the relation for specified source record id and related record id list
	 * @param <array> $request
	 *		keys					Content
	 *		src_module				source module name
	 *		src_record				source record id
	 *		related_module			related module name
	 *		related_record_list		json encoded of list of related record ids
	 */
	function deleteRelation($request) {
		$sourceModule = $request->getModule();
		$sourceRecordId = $request->get('src_record');

		$relatedModule = $request->get('related_module');
		$relatedRecordIdList = $request->get('related_record_list');
		$recurringEditMode = $request->get('recurringEditMode');
		$relatedRecordList = array();
		if($relatedModule == 'Calendar' && !empty($recurringEditMode) && $recurringEditMode != 'current') {
			foreach($relatedRecordIdList as $relatedRecordId) {
				$recordModel = Vtiger_Record_Model::getCleanInstance($relatedModule);
				$recordModel->set('id', $relatedRecordId);
				$recurringRecordsList = $recordModel->getRecurringRecordsList();
				foreach($recurringRecordsList as $parent => $childs) {
					$parentRecurringId = $parent;
					$childRecords = $childs;
				}
				if($recurringEditMode == 'future') {
					$parentKey = array_keys($childRecords, $relatedRecordId);
					$childRecords = array_slice($childRecords, $parentKey[0]);
				}
				foreach($childRecords as $recordId) {
					$relatedRecordList[] = $recordId;
				}
				$relatedRecordIdList = array_slice($relatedRecordIdList, $relatedRecordId);
			}
		}

		foreach($relatedRecordList as $record) {
			$relatedRecordIdList[] = $record;
		}

		//Setting related module as current module to delete the relation
		vglobal('currentModule', $relatedModule);

		$sourceModuleModel = Vtiger_Module_Model::getInstance($sourceModule);
		$relatedModuleModel = Vtiger_Module_Model::getInstance($relatedModule);
		$relationModel = Vtiger_Relation_Model::getInstance($sourceModuleModel, $relatedModuleModel);

		if ($sourceModule == 'ServiceEngineer' && $relatedModule == 'FunctionalLocations') {
			$eqRelatedModuleModel = Vtiger_Module_Model::getInstance('Equipment');
			$eqRelationModel = Vtiger_Relation_Model::getInstance($sourceModuleModel, $eqRelatedModuleModel);
			require_once('include/utils/GeneralUtils.php');
		}

		foreach($relatedRecordIdList as $relatedRecordId) {
			if ($sourceModule == 'ServiceEngineer' && $relatedModule == 'FunctionalLocations') {
				$anotherRelations = getAllAssociatedFunctionalLocationsSELInkedInEmployee($sourceRecordId,$relatedRecordId);
				foreach($anotherRelations as $anotherRelation) {
					$eqRelationModel->deleteRelation($sourceRecordId,$anotherRelation);
				}
			}
			$response = $relationModel->deleteRelation($sourceRecordId,$relatedRecordId);
		}

		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}

	function deleteRelationEnhanced($request) {
        $sourceModule = $request->getModule();
        $sourceRecordId = $request->get('src_record');
        $relatedModule = $request->get('related_module');
        $relatedRecordIdList = $request->get('related_record_list');
        $recurringEditMode = $request->get('recurringEditMode');
		
        if ($relatedRecordIdList != 'all') {
            $relatedRecordList = array();
            foreach ($relatedRecordList as $record) {
                $relatedRecordIdList[] = $record;
            }
            //Setting related module as current module to delete the relation
            vglobal('currentModule', $relatedModule);
            $sourceModuleModel = Vtiger_Module_Model::getInstance($sourceModule);
            $relatedModuleModel = Vtiger_Module_Model::getInstance($relatedModule);
            $relationModel = Vtiger_Relation_Model::getInstance($sourceModuleModel, $relatedModuleModel);

			if ($sourceModule == 'ServiceEngineer' && $relatedModule == 'FunctionalLocations') {
				$eqRelatedModuleModel = Vtiger_Module_Model::getInstance('Equipment');
				$eqRelationModel = Vtiger_Relation_Model::getInstance($sourceModuleModel, $eqRelatedModuleModel);
				require_once('include/utils/GeneralUtils.php');
			}

            foreach ($relatedRecordIdList as $relatedRecordId) {

				if ($sourceModule == 'ServiceEngineer' && $relatedModule == 'FunctionalLocations') {
					$anotherRelations = getAllAssociatedEquipmentsBasedSELInkedInEmployeeFunc($relatedRecordId);
					
					foreach($anotherRelations as $anotherRelation) {
						$eqRelationModel->deleteRelation($sourceRecordId,$anotherRelation);
					}
				}

                $response = $relationModel->deleteRelation($sourceRecordId, $relatedRecordId);
            }
            $response = new Vtiger_Response();
            $response->setResult(true);
            $response->emit();
        } else {
            $recordIds = $this->deleteASeRelationEnhanced($request);
            $sourceModuleModel = Vtiger_Module_Model::getInstance($sourceModule);
            $relatedModuleModel = Vtiger_Module_Model::getInstance($relatedModule);
            $relationModel = Vtiger_Relation_Model::getInstance($sourceModuleModel, $relatedModuleModel);
            $excludedIds = $request->get('excluded_ids');
            if(empty($excludedIds)){
                $excludedIds = [];
            }

			if ($sourceModule == 'ServiceEngineer' && $relatedModule == 'FunctionalLocations') {
				$eqRelatedModuleModel = Vtiger_Module_Model::getInstance('Equipment');
				$eqRelationModel = Vtiger_Relation_Model::getInstance($sourceModuleModel, $eqRelatedModuleModel);
				require_once('include/utils/GeneralUtils.php');
			}

            $recordIds = array_diff($recordIds, $excludedIds);
            foreach ($recordIds as $relatedRecordId) {
				if ($sourceModule == 'ServiceEngineer' && $relatedModule == 'FunctionalLocations') {
					$anotherRelations = getAllAssociatedEquipmentsBasedSELInkedInEmployeeFunc($relatedRecordId);
					
					foreach($anotherRelations as $anotherRelation) {
						$eqRelationModel->deleteRelation($sourceRecordId,$anotherRelation);
					}
				}

                $response = $relationModel->deleteRelation($sourceRecordId, $relatedRecordId);
            }
            $response = new Vtiger_Response();
            $response->setResult(true);
            $response->emit();
        }
    }

	function getListViewEntriesSQL(Vtiger_Request $request) {
		$moduleName = $request->get('related_module');
		$sourceModule = $request->get('src_field');
		$sourceField = $request->get('src_field');
		$sourceRecord = $request->get('src_record');
		$orderBy = $request->get('orderby');
		$sortOrder = $request->get('sortorder');
		$currencyId = $request->get('currency_id');

		$searchKey = $request->get('search_key');
		$searchValue = $request->get('search_value');
		$searchParams = $request->get('search_params');

		$relatedParentModule = $request->get('related_parent_module');
		$relatedParentId = $request->get('related_parent_id');
		if (!empty($relatedParentModule) && !empty($relatedParentId)) {
			$parentRecordModel = Vtiger_Record_Model::getInstanceById($relatedParentId, $relatedParentModule);
			$listViewModel = Vtiger_RelationListView_Model::getInstance($parentRecordModel, $moduleName, $label);
		} else {
			$listViewModel = Vtiger_ListView_Model::getInstanceForPopup($moduleName);
		}

		if (!empty($sourceModule)) {
			$listViewModel->set('src_module', $sourceModule);
			$listViewModel->set('src_field', $sourceField);
			$listViewModel->set('src_record', $sourceRecord);
			$listViewModel->set('currency_id', $currencyId);
		}

		if (!empty($orderBy)) {
			$listViewModel->set('orderby', $orderBy);
			$listViewModel->set('sortorder', $sortOrder);
		}
		if ((!empty($searchKey)) && (!empty($searchValue))) {
			$listViewModel->set('search_key', $searchKey);
			$listViewModel->set('search_value', $searchValue);
		}

		if (!empty($searchParams)) {
			$transformedSearchParams = $this->transferListSearchParamsToFilterCondition($searchParams, $listViewModel->getModule());
			$listViewModel->set('search_params', $transformedSearchParams);
		}
		if (!empty($relatedParentModule) && !empty($relatedParentId)) {
			$count = $listViewModel->getRelatedEntriesCount();
		} else {
			$count = $listViewModel->IGgetListViewSqlForPopView();
		}
		return $count;
	}

	public function transferListSearchParamsToFilterCondition($listSearchParams, $moduleModel) {
		return Vtiger_Util_Helper::transferListSearchParamsToFilterCondition($listSearchParams, $moduleModel);
	}

	function getAllRecordsToAddInthePopUp(Vtiger_Request $request) {
        $totalCountSQL = $this->getListViewEntriesSQL($request);
        $db = PearDatabase::getInstance();
        $result = $db->pquery($totalCountSQL);
        $allREcordIds = [];
        while ($rowData = $db->fetch_array($result)) {
            array_push($allREcordIds,$rowData['crmid']);
        }
        return $allREcordIds;
    }

    function deleteASeRelationEnhanced(Vtiger_Request $request) {
        $moduleName = $request->getModule();
        $relatedModuleName = $request->get('related_module');
        $parentId = $request->get('src_record');
        $label = $request->get('tab_label');
        $parentRecordModel = Vtiger_Record_Model::getInstanceById($parentId, $moduleName);
        $relationListView = Vtiger_RelationListView_Model::getInstance($parentRecordModel, $relatedModuleName, $label);
        $totalCountSQL = $relationListView->getRelatedEntriesREcordIds($request);
        $db = PearDatabase::getInstance();
        $result = $db->pquery($totalCountSQL);
        $allREcordIds = [];
        while ($rowData = $db->fetch_array($result)) {
            array_push($allREcordIds,$rowData['crmid']);
        }
        return $allREcordIds;
    }

    /**
	 * Function to get the page count for reltedlist
	 * @return total number of pages
	 */
	function getRelatedListPageCount(Vtiger_Request $request){
		$moduleName = $request->getModule();
		$relatedModuleName = $request->get('relatedModule');
		$parentId = $request->get('record');
		$label = $request->get('tab_label');
		$pagingModel = new Vtiger_Paging_Model();
		$parentRecordModel = Vtiger_Record_Model::getInstanceById($parentId, $moduleName);
		$relationListView = Vtiger_RelationListView_Model::getInstance($parentRecordModel, $relatedModuleName, $label);
		$totalCount = $relationListView->getRelatedEntriesCount();
		$pageLimit = $pagingModel->getPageLimit();
		$pageCount = ceil((int) $totalCount / (int) $pageLimit);

		if($pageCount == 0){
			$pageCount = 1;
		}
		$result = array();
		$result['numberOfRecords'] = $totalCount;
		$result['page'] = $pageCount;
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}

	public function validateRequest(Vtiger_Request $request) {
		$request->validateWriteAccess();
	}

	function getRelatedRecordInfo($request) {
		try {
			return $this->getParentRecordInfo($request);
		} catch (Exception $e) {
			$response = new Vtiger_Response();
			$response->setError($e->getCode(), $e->getMessage());
			$response->emit();
		}
	}

	function getParentRecordInfo($request) {
		$moduleName = $request->get('module');
		$recordModel = Vtiger_Record_Model::getInstanceById($request->get('id'), $moduleName);
		$moduleModel = $recordModel->getModule();
		$autoFillData = $moduleModel->getAutoFillModuleAndField($moduleName);

		if($autoFillData) {
			foreach($autoFillData as $data) {
				$autoFillModule = $data['module'];
				$autoFillFieldName = $data['fieldname'];
				$autofillRecordId = $recordModel->get($autoFillFieldName);

				$autoFillNameArray = getEntityName($autoFillModule, $autofillRecordId);
				$autoFillName = $autoFillNameArray[$autofillRecordId];

				$resultData[] = array(	'id'		=> $request->get('id'), 
										'name'		=> decode_html($recordModel->getName()),
										'parent_id'	=> array(	'id' => $autofillRecordId,
																'name' => decode_html($autoFillName),
																'module' => $autoFillModule));
			}

			$result[$request->get('id')] = $resultData;

		} else {
			$resultData = array('id'	=> $request->get('id'), 
								'name'	=> decode_html($recordModel->getName()),
								'info'	=> $recordModel->getRawData());
			$result[$request->get('id')] = $resultData;
		}

		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}