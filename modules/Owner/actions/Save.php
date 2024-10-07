<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Owner_Save_Action extends Vtiger_Action_Controller {

	public function requiresPermission(\Vtiger_Request $request) {
		$permissions = parent::requiresPermission($request);
		$moduleParameter = $request->get('source_module');
		if (!$moduleParameter) {
			$moduleParameter = 'module';
		}else{
			$moduleParameter = 'source_module';
		}
		$record = $request->get('record');
		$recordId = $request->get('id');
		if (!$record) {
			$recordParameter = '';
		}else{
			$recordParameter = 'record';
		}
		$actionName = ($record || $recordId) ? 'EditView' : 'CreateView';
        $permissions[] = array('module_parameter' => $moduleParameter, 'action' => 'DetailView', 'record_parameter' => $recordParameter);
		$permissions[] = array('module_parameter' => $moduleParameter, 'action' => $actionName, 'record_parameter' => $recordParameter);
		return $permissions;
	}
	
	public function checkPermission(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$record = $request->get('record');

		$nonEntityModules = array('Users', 'Events', 'Calendar', 'Portal', 'Reports', 'Rss', 'EmailTemplates');
		if ($record && !in_array($moduleName, $nonEntityModules)) {
			$recordEntityName = getSalesEntityType($record);
			if ($recordEntityName !== $moduleName) {
				throw new AppException(vtranslate('LBL_PERMISSION_DENIED'));
			}
		}
		return parent::checkPermission($request);
	}
	
	public function validateRequest(Vtiger_Request $request) {
		return $request->validateWriteAccess();
	}

	public function process(Vtiger_Request $request) {
		try {
			$recordModel = $this->saveRecord($request);
			if ($request->get('returntab_label')){
				$loadUrl = 'index.php?'.$request->getReturnURL();
			} else if($request->get('relationOperation')) {
				$parentModuleName = $request->get('sourceModule');
				$parentRecordId = $request->get('sourceRecord');
				$parentRecordModel = Vtiger_Record_Model::getInstanceById($parentRecordId, $parentModuleName);
				//TODO : Url should load the related list instead of detail view of record
				$loadUrl = $parentRecordModel->getDetailViewUrl();
			} else if ($request->get('returnToList')) {
				$loadUrl = $recordModel->getModule()->getListViewUrl();
			} else if ($request->get('returnmodule') && $request->get('returnview')) {
				$loadUrl = 'index.php?'.$request->getReturnURL();
			} else {
				$loadUrl = $recordModel->getDetailViewUrl();
			}
			//append App name to callback url
			//Special handling for vtiger7.
			$appName = $request->get('appName');
			if(strlen($appName) > 0){
				$loadUrl = $loadUrl.$appName;
			}
			header("Location: $loadUrl");
		} catch (DuplicateException $e) {
			$requestData = $request->getAll();
			$moduleName = $request->getModule();
			unset($requestData['action']);
			unset($requestData['__vtrftk']);

			if ($request->isAjax()) {
				$response = new Vtiger_Response();
				$response->setError($e->getMessage(), $e->getDuplicationMessage(), $e->getMessage());
				$response->emit();
			} else {
				$requestData['view'] = 'Edit';
				$requestData['duplicateRecords'] = $e->getDuplicateRecordIds();
				$moduleModel = Vtiger_Module_Model::getInstance($moduleName);

				global $vtiger_current_version;
				$viewer = new Vtiger_Viewer();

				$viewer->assign('REQUEST_DATA', $requestData);
				$viewer->assign('REQUEST_URL', $moduleModel->getCreateRecordUrl().'&record='.$request->get('record'));
				$viewer->view('RedirectToEditView.tpl', 'Vtiger');
			}
		} catch (Exception $e) {
			throw new Exception($e->getMessage());
		}
	}

	/**
	 * Function to save record
	 * @param <Vtiger_Request> $request - values of the record
	 * @return <RecordModel> - record Model of saved record
	 */
	public function saveRecord($request) {
		$recordModel = $this->getRecordModelFromRequest($request);
		if($request->get('imgDeleted')) {
			$imageIds = $request->get('imageid');
			foreach($imageIds as $imageId) {
				$status = $recordModel->deleteImage($imageId);
			}
		}
	
		$recordModel->save();
		$recordId = $request->get('record');
		if($recordId == ''){
			$focus = new Users();
			$focus->column_fields['user_name'] =   $_REQUEST['owntenant_name'];
			$focus->column_fields['first_name'] =  '';
			$focus->column_fields['last_name'] =  $_REQUEST['owntenant_name'];
			$focus->column_fields['status'] =  'Active';
			$focus->column_fields['is_admin'] =  'off';
			//$focus->column_fields['ser_usr_log_plat'] =  'Both';
			//$focus->column_fields['approval_status'] =  'Accepted';
			$focus->column_fields['user_password'] =  $_REQUEST['user_password'];
			$focus->column_fields['confirm_password'] =  $_REQUEST['confirm_password'];
			$focus->column_fields['email1'] =   $_REQUEST['email'];
			$focus->column_fields['phone_mobile'] = $_REQUEST['phone'];
			$focus->column_fields['roleid'] =  'H335'; //'H37';
			$focus->column_fields['tz'] =  'Asia/Kolkata';
			$focus->column_fields['time_zone'] =  'Asia/Kolkata';
			$focus->column_fields['date_format'] =  'dd/mm/yyyy';
			$focus->column_fields['title'] =  'Asia';
			// $focus->save("Users");

			
			global $current_user, $upload_badext, $adb;
			$current_id = $adb->getUniqueID("vtiger_crmentity");
			$date_var = date("Y-m-d H:i:s");
		
			$filename = $_FILES['emp_imagefile'][0]['name'];
			$filetype = $_FILES['emp_imagefile'][0]['type'];
			$filetmp_name = $_FILES['emp_imagefile'][0]['tmp_name'];
			$upload_file_path = decideFilePath();
			$binFile = sanitizeUploadFileName($filename, $upload_badext);
			$encryptFileName = Vtiger_Util_Helper::getEncryptedFileName($binFile);
			
			$upload_status = move_uploaded_file($filetmp_name,$upload_file_path.$current_id."_".$encryptFileName);
			
			$sql1 = "insert into vtiger_crmentity (crmid,smcreatorid,smownerid,setype,description,createdtime,modifiedtime) values(?,?,?,?,?,?,?)";
			$params1 = array($current_id, $current_user->id, $current_user->id, "Users Image", "", $adb->formatDate($date_var, true), $adb->formatDate($date_var, true));
			$adb->pquery($sql1, $params1);

			$sql2="insert into vtiger_attachments(attachmentsid, name, description, type, path, storedname) values(?,?,?,?,?,?)";
			$params2 = array($current_id, $filename, "", $filetype, $upload_file_path, $encryptFileName);
			$adb->pquery($sql2, $params2);

			$sql3='insert into vtiger_salesmanattachmentsrel(smid,attachmentsid) values(?,?)';
			$adb->pquery($sql3, array($focus->id, $current_id));

			//we should update the imagename in the users table
			$adb->pquery("update vtiger_users set imagename=? where id=?", array($filename, $focus->id));
			

			$content = 'Dear User,<br><br> 
						This is you Login details 
						<br><br>
						Your Username Is : ' . $_REQUEST['badge_no'] . '<br>
						Your Password Is : ' . $_REQUEST['user_password'] . '
						<br><br><br>
						Regards,<br>
					CRM Support Team.<br>';

			$subject = 'CRM Login details';

			include_once 'modules/Emails/mail.php';
			global $HELPDESK_SUPPORT_EMAIL_ID, $HELPDESK_SUPPORT_NAME;
			$status = send_mail('Users', $_REQUEST['email'], $HELPDESK_SUPPORT_NAME, $HELPDESK_SUPPORT_EMAIL_ID, $subject, $content, '', '', '', '', '', true);
		}

		
		if($request->get('relationOperation')) {
			$parentModuleName = $request->get('sourceModule');
			$parentModuleModel = Vtiger_Module_Model::getInstance($parentModuleName);
			$parentRecordId = $request->get('sourceRecord');
			$relatedModule = $recordModel->getModule();
			$relatedRecordId = $recordModel->getId();
			if($relatedModule->getName() == 'Events'){
				$relatedModule = Vtiger_Module_Model::getInstance('Calendar');
			}

			$relationModel = Vtiger_Relation_Model::getInstance($parentModuleModel, $relatedModule);
			$relationModel->addRelation($parentRecordId, $relatedRecordId);
		}
		$this->savedRecordId = $recordModel->getId();
		return $recordModel;
	}

	/**
	 * Function to get the record model based on the request parameters
	 * @param Vtiger_Request $request
	 * @return Vtiger_Record_Model or Module specific Record Model instance
	 */
	protected function getRecordModelFromRequest(Vtiger_Request $request) {

		$moduleName = $request->getModule();
		$recordId = $request->get('record');

		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);

		if(!empty($recordId)) {
			$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
			$recordModel->set('id', $recordId);
			$recordModel->set('mode', 'edit');
		} else {
			$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
			$recordModel->set('mode', '');
		}

		$fieldModelList = $moduleModel->getFields();
		foreach ($fieldModelList as $fieldName => $fieldModel) {
			$fieldValue = $request->get($fieldName, null);
			$fieldDataType = $fieldModel->getFieldDataType();
			if($fieldDataType == 'time' && $fieldValue !== null){
				$fieldValue = Vtiger_Time_UIType::getTimeValueWithSeconds($fieldValue);
			}
            $ckeditorFields = array('commentcontent', 'notecontent');
            if((in_array($fieldName, $ckeditorFields)) && $fieldValue !== null){
                $purifiedContent = vtlib_purify(decode_html($fieldValue));
                // Purify malicious html event attributes
                $fieldValue = purifyHtmlEventAttributes(decode_html($purifiedContent),true);
			}
			if($fieldValue !== null) {
				if(!is_array($fieldValue) && $fieldDataType != 'currency') {
					$fieldValue = trim($fieldValue);
				}
				$recordModel->set($fieldName, $fieldValue);
			}
		}
		return $recordModel;
	}
}
