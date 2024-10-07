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

class LabelManager extends Vtiger_CRMEntity {
	var $table_name = 'vtiger_labelmanager';
	var $table_index= 'labelmanagerid';

	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array('vtiger_labelmanagercf', 'labelmanagerid');

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	var $tab_name = Array('vtiger_crmentity', 'vtiger_labelmanager', 'vtiger_labelmanagercf');

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	var $tab_name_index = Array(
		'vtiger_crmentity' => 'crmid',
		'vtiger_labelmanager' => 'labelmanagerid',
		'vtiger_labelmanagercf'=>'labelmanagerid');

	/**
	 * Mandatory for Listing (Related listview)
	 */
	var $list_fields = Array (
		/* Format: Field Label => Array(tablename, columnname) */
		// tablename should not have prefix 'vtiger_'
		'LabelManager' => Array('labelmanager', 'labelmanager'),
		'Assigned To' => Array('crmentity','smownerid')
	);
	var $list_fields_name = Array (
		/* Format: Field Label => fieldname */
		'LabelManager' => '<entityfieldname>',
		'Assigned To' => 'assigned_user_id',
	);

	// Make the field link to detail view
	var $list_link_field = '<entityfieldname>';

	// For Popup listview and UI type support
	var $search_fields = Array(
		/* Format: Field Label => Array(tablename, columnname) */
		// tablename should not have prefix 'vtiger_'
		'LabelManager' => Array('labelmanager', 'labelmanager'),
		'Assigned To' => Array('vtiger_crmentity','assigned_user_id'),
	);
	var $search_fields_name = Array (
		/* Format: Field Label => fieldname */
		'LabelManager' => '<entityfieldname>',
		'Assigned To' => 'assigned_user_id',
	);

	// For Popup window record selection
	var $popup_fields = Array ('<entityfieldname>');

	// For Alphabetical search
	var $def_basicsearch_col = '<entityfieldname>';

	// Column value to use on detail view record text display
	var $def_detailview_recname = '<entityfieldname>';

	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	var $mandatory_fields = Array('<entityfieldname>','assigned_user_id');

	var $default_order_by = '<entityfieldname>';
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
			$this->insertJsLink();
		} else if($eventType == 'module.disabled') {
			// TODO Handle actions before this module is being uninstalled.
			$this->insertJsLink();
			$this->disableLink();
		} else if($eventType == 'module.preuninstall') {
			// TODO Handle actions when this module is about to be deleted.
			$this->insertJsLink();
		} else if($eventType == 'module.preupdate') {
			// TODO Handle actions before this module is updated.
		} else if($eventType == 'module.postupdate') {
			// TODO Handle actions after this module is updated.
			$this->insertJsLink();
		}else{
			$this->enableLink();
		}
 	}
	
	private function insertJsLink() {
        $adb = PearDatabase::getInstance();
        $linkto = 'index.php?module=LabelManager&parent=Settings&view=LabelManager&mode=languageSettings';
        $result1=$adb->pquery('SELECT 1 FROM vtiger_settings_field WHERE name=?',array('Label Manager'));
        if($adb->num_rows($result1)){
            $adb->pquery('UPDATE vtiger_settings_field SET name=?, iconpath=?, description=?, linkto=? WHERE name=?',array('Label Manager', '', '', $linkto, 'Label Manager'));
        }else{
            $fieldid = $adb->getUniqueID('vtiger_settings_field');
            $blockid = getSettingsBlockId('LBL_OTHER_SETTINGS');
            $seq_res = $adb->pquery("SELECT max(sequence) AS max_seq FROM vtiger_settings_field WHERE blockid = ?", array($blockid));
            if ($adb->num_rows($seq_res) > 0) {
                    $cur_seq = $adb->query_result($seq_res, 0, 'max_seq');
                    if ($cur_seq != null)   $seq = $cur_seq + 1;
            }
            $adb->pquery('INSERT INTO vtiger_settings_field(fieldid, blockid, name, iconpath, description, linkto, sequence) VALUES (?,?,?,?,?,?,?)', array($fieldid, $blockid, 'Label Manager' , '', '', $linkto, $seq));
        }
    }
	
	private function disableLink(){
		$adb = PearDatabase::getInstance();
		$adb->pquery("UPDATE vtiger_settings_field SET active = 1 WHERE name = 'Label Manager'", array());
	}
	
	private function enableLink(){
		$adb = PearDatabase::getInstance();
		$adb->pquery("UPDATE vtiger_settings_field SET active = 0 WHERE name = 'Label Manager'", array());
	}
}