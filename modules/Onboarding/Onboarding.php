<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

include_once 'modules/Vtiger/CRMEntity.php';

class Onboarding extends Vtiger_CRMEntity {
	var $table_name = 'vtiger_onboarding';
	var $table_index= 'onboardingid';

	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array('vtiger_onboardingcf', 'onboardingid');

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	var $tab_name = Array('vtiger_crmentity', 'vtiger_onboarding', 'vtiger_onboardingcf');

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	var $tab_name_index = Array(
		'vtiger_crmentity' => 'crmid',
		'vtiger_onboarding' => 'onboardingid',
		'vtiger_onboardingcf'=>'onboardingid');

	/**
	 * Mandatory for Listing (Related listview)
	 */
	var $list_fields = Array (
		/* Format: Field Label => Array(tablename, columnname) */
		// tablename should not have prefix 'vtiger_'
		'Onboarding No' => Array('onboarding', 'onboarding_no'),
		'Assigned To' => Array('crmentity','smownerid')
	);
	var $list_fields_name = Array (
		/* Format: Field Label => fieldname */
		'Onboarding No' => 'onboarding_no',
		'Assigned To' => 'assigned_user_id',
	);

	// Make the field link to detail view
	var $list_link_field = 'onboarding_no';

	// For Popup listview and UI type support
	var $search_fields = Array(
		/* Format: Field Label => Array(tablename, columnname) */
		// tablename should not have prefix 'vtiger_'
		'Onboarding No' => Array('onboarding', 'onboarding_no'),
		'Assigned To' => Array('vtiger_crmentity','assigned_user_id'),
	);
	var $search_fields_name = Array (
		/* Format: Field Label => fieldname */
		'Onboarding No' => 'onboarding_no',
		'Assigned To' => 'assigned_user_id',
	);

	// For Popup window record selection
	var $popup_fields = Array ('onboarding_no');

	// For Alphabetical search
	var $def_basicsearch_col = 'onboarding_no';

	// Column value to use on detail view record text display
	var $def_detailview_recname = 'onboarding_no';

	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	var $mandatory_fields = Array('onboarding_no','assigned_user_id');

	var $default_order_by = 'onboarding_no';
	var $default_sort_order='ASC';

	/**
	* Invoked when special actions are performed on the module.
	* @param String Module name
	* @param String Event Type
	*/
	function vtlib_handler($moduleName, $eventType) {
		global $adb;
 		if($eventType == 'module.postinstall') {
			// TODO Handle actions after this module is installed.
		} else if($eventType == 'module.disabled') {
			// TODO Handle actions before this module is being uninstalled.
		} else if($eventType == 'module.preuninstall') {
			// TODO Handle actions when this module is about to be deleted.
		} else if($eventType == 'module.preupdate') {
			// TODO Handle actions before this module is updated.
		} else if($eventType == 'module.postupdate') {
			// TODO Handle actions after this module is updated.
		}
 	}

 	function save_module($module){
		// now handling in the crmentity for uitype 69
		$this->insertIntoAttachment($this->id,$module);
	}

	function insertIntoAttachment($id,$module)
	{
		global $log, $adb,$upload_badext;
		$log->debug("Entering into insertIntoAttachment($id,$module) method.");

		$file_saved = false;
		//This is to added to store the existing attachment id of the contact where we should delete this when we give new image
		
		$old_attachmentid = $adb->query_result($adb->pquery("select vtiger_crmentity.crmid from vtiger_seattachmentsrel inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_seattachmentsrel.attachmentsid where  vtiger_seattachmentsrel.crmid=?", array($id)),0,'crmid');

		if($_FILES['onboarding_image']['name'] != '' && $_FILES['onboarding_image']['size'] > 0){
			$_FILES['onboarding_image']['original_name'] = vtlib_purify($_REQUEST[$fileindex.'_hidden']);
			$file_saved = $this->uploadAndSaveFile($id,$module,$_FILES['onboarding_image']);
		}
	
		$imageNameSql = 'SELECT name FROM vtiger_seattachmentsrel INNER JOIN vtiger_attachments ON
								vtiger_seattachmentsrel.attachmentsid = vtiger_attachments.attachmentsid LEFT JOIN vtiger_onboarding ON
								vtiger_onboarding.onboardingid = vtiger_seattachmentsrel.crmid WHERE vtiger_seattachmentsrel.crmid = ?';
		$imageNameResult = $adb->pquery($imageNameSql,array($id));
		$imageName = decode_html($adb->query_result($imageNameResult, 0, "name"));

		//Inserting image information of record into base table
		$adb->pquery('UPDATE vtiger_onboarding SET onboarding_image = ? WHERE onboardingid = ?',array($_FILES['onboarding_image']['name'],$id));

		//This is to handle the delete image for contacts
		if($module == 'Onboarding' && $file_saved)
		{
			if($old_attachmentid != '')
			{
				$del_res1 = $adb->pquery("delete from vtiger_attachments where attachmentsid=?", array($old_attachmentid));
				$del_res2 = $adb->pquery("delete from vtiger_seattachmentsrel where attachmentsid=?", array($old_attachmentid));
			}
		}
		$log->debug("Exiting from insertIntoAttachment($id,$module) method.");
	}
}