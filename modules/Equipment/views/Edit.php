<?php

class Equipment_Edit_View extends Vtiger_Edit_View {

	public function process(Vtiger_Request $request) {
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$record = $request->get('record');
		if (!empty($record) && $request->get('isDuplicate') == true) {
			$recordModel = $this->record ? $this->record : Vtiger_Record_Model::getInstanceById($record, $moduleName);
			$viewer->assign('MODE', '');

			//While Duplicating record, If the related record is deleted then we are removing related record info in record model
			$mandatoryFieldModels = $recordModel->getModule()->getMandatoryFieldModels();
			foreach ($mandatoryFieldModels as $fieldModel) {
				if ($fieldModel->isReferenceField()) {
					$fieldName = $fieldModel->get('name');
					if (Vtiger_Util_Helper::checkRecordExistance($recordModel->get($fieldName))) {
						$recordModel->set($fieldName, '');
					}
				}
			}
		} else if (!empty($record)) {
			$recordModel = $this->record ? $this->record : Vtiger_Record_Model::getInstanceById($record, $moduleName);
			$viewer->assign('RECORD_ID', $record);
			$viewer->assign('MODE', 'edit');
		} else {
			$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
			$viewer->assign('MODE', '');
		}
		if (!$this->record) {
			$this->record = $recordModel;
		}

		$moduleModel = $recordModel->getModule();
		$fieldList = $moduleModel->getFields();
		$requestFieldList = array_intersect_key($request->getAllPurified(), $fieldList);

		$relContactId = $request->get('contact_id');
		if ($relContactId && $moduleName == 'Calendar') {
			$contactRecordModel = Vtiger_Record_Model::getInstanceById($relContactId);
			$requestFieldList['parent_id'] = $contactRecordModel->get('account_id');
		}
		foreach ($requestFieldList as $fieldName => $fieldValue) {
			$fieldModel = $fieldList[$fieldName];
			$specialField = false;
			// We collate date and time part together in the EditView UI handling 
			// so a bit of special treatment is required if we come from QuickCreate 
			if ($moduleName == 'Calendar' && empty($record) && $fieldName == 'time_start' && !empty($fieldValue)) {
				$specialField = true;
				// Convert the incoming user-picked time to GMT time 
				// which will get re-translated based on user-time zone on EditForm 
				$fieldValue = DateTimeField::convertToDBTimeZone($fieldValue)->format("H:i");
			}

			if ($moduleName == 'Calendar' && empty($record) && $fieldName == 'date_start' && !empty($fieldValue)) {
				$startTime = Vtiger_Time_UIType::getTimeValueWithSeconds($requestFieldList['time_start']);
				$startDateTime = Vtiger_Datetime_UIType::getDBDateTimeValue($fieldValue . " " . $startTime);
				list($startDate, $startTime) = explode(' ', $startDateTime);
				$fieldValue = Vtiger_Date_UIType::getDisplayDateValue($startDate);
			}
			if ($fieldModel->isEditable() || $specialField) {
				$recordModel->set($fieldName, $fieldModel->getDBInsertValue($fieldValue));
			}
		}
		$recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_EDIT);
		$picklistDependencyDatasource = Vtiger_DependencyPicklist::getPicklistDependencyDatasource($moduleName);

		$viewer->assign('PICKIST_DEPENDENCY_DATASOURCE', Vtiger_Functions::jsonEncode($picklistDependencyDatasource));
		$viewer->assign('RECORD_STRUCTURE_MODEL', $recordStructureInstance);
		$viewer->assign('RECORD_STRUCTURE', $recordStructureInstance->getStructure());
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('CURRENTDATE', date('Y-n-j'));
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());

		$isRelationOperation = $request->get('relationOperation');

		//if it is relation edit
		$viewer->assign('IS_RELATION_OPERATION', $isRelationOperation);
		if ($isRelationOperation) {
			$viewer->assign('SOURCE_MODULE', $request->get('sourceModule'));
			$viewer->assign('SOURCE_RECORD', $request->get('sourceRecord'));
		}

		// added to set the return values
		if ($request->get('returnview')) {
			$request->setViewerReturnValues($viewer);
		}
		$viewer->assign('MAX_UPLOAD_LIMIT_MB', Vtiger_Util_Helper::getMaxUploadSize());
		$viewer->assign('MAX_UPLOAD_LIMIT_BYTES', Vtiger_Util_Helper::getMaxUploadSizeInBytes());

		global $adb;
		$tabId = getTabId($moduleName);
		$sql = "SELECT * FROM `vtiger_field` LEFT JOIN vtiger_blocks
			on vtiger_blocks.blockid = vtiger_field.block where vtiger_field.tabid = ? 
			and helpinfo = 'li_lg' and blocklabel = ? and presence != 1 ORDER BY `vtiger_field`.`sequence` ASC;";
		$result = $adb->pquery($sql, array($tabId, 'daadcp_lineblock'));
		$fields = [];
		$fieldNames = [];
		$pickListFields = [];
		$dependentList = array();
		while ($row = $adb->fetch_array($result)) {
			if ($row['uitype'] == '16' || $row['uitype'] == '999') {
				array_push($pickListFields, $row['fieldname']);
				$row['picklistValues'] = getAllPickListValues($row['fieldname']);
			}
			if (in_array($row['fieldname'], $dependentList)) {
				$row['hideInitialDisplay'] = 'true';
			}
			array_push($fieldNames, $row['fieldname']);
			array_push($fields, $row);
		}

		$viewer->assign('LINEITEM_CUSTOM_FIELDNAMES_OTHER1', $fieldNames);
		$viewer->assign('LINEITEM_CUSTOM_OTHER_PICK_FIELDS1', $pickListFields);
		$viewer->assign('LINEITEM_CUSTOM_FIELDS_OTHER1', $fields);
		if (!empty($record)) {
			$relatedLines = $recordModel->getProductsOther2();
			// die();
			$noOfYearsOfContract = (int) $recordModel->get('total_year_cont') + 1;
			$i = 1;
			for ($i = 1; $i < $noOfYearsOfContract; $i++) {
				if ($i == 1) {
					$relatedProductsAnother[1]['daadcp_contra_lable1'] = '1 year Contract';
					$relatedProductsAnother[1]['daadcp_avail_sl_no1'] = 1;
					$relatedProductsAnother[1]['daadcp_avail_percent1'] = $relatedLines[$i]['daadcp_avail_percent'.$i];
					$relatedProductsAnother[1]['daadcp_avail_mon_percent1'] = $relatedLines[$i]['daadcp_avail_mon_percent'.$i];
				} else {
					array_push($relatedProductsAnother, array(
						'daadcp_contra_lable' . $i => "$i year Contract",
						'daadcp_avail_sl_no' . $i => $i,
						'daadcp_avail_percent' . $i => $relatedLines[$i]['daadcp_avail_percent'.$i],
						'daadcp_avail_mon_percent' . $i => $relatedLines[$i]['daadcp_avail_mon_percent'.$i]
					));
				}
			}
			if (empty($noOfYearsOfContract)) {
				$viewer->assign('RELATED_PRODUCTS_OTHER1', []);
			} else {
				$viewer->assign('RELATED_PRODUCTS_OTHER1', $relatedProductsAnother);
			}
		}


		if ($request->get('displayMode') == 'overlay') {
			$viewer->assign('SCRIPTS', $this->getOverlayHeaderScripts($request));
			$viewer->view('OverlayEditView.tpl', $moduleName);
		} else {
			$viewer->view('EditView.tpl', $moduleName);
		}
	}
}
