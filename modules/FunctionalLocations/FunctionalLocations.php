<?php
include_once 'modules/Vtiger/CRMEntity.php';

class FunctionalLocations extends Vtiger_CRMEntity {
	var $table_name = 'vtiger_functionallocations';
	var $table_index = 'functionallocationsid';

	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = array('vtiger_functionallocationscf', 'functionallocationsid');

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	var $tab_name = array('vtiger_crmentity', 'vtiger_functionallocations', 'vtiger_functionallocationscf');

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	var $tab_name_index = array(
		'vtiger_crmentity' => 'crmid',
		'vtiger_functionallocations' => 'functionallocationsid',
		'vtiger_functionallocationscf' => 'functionallocationsid'
	);

	/**
	 * Mandatory for Listing (Related listview)
	 */
	var $list_fields = array();
	var $list_fields_name = array();

	// Make the field link to detail view
	var $list_link_field = 'functionallocation_name';

	// For Popup listview and UI type support
	var $search_fields = array();
	var $search_fields_name = array();

	// For Popup window record selection
	var $popup_fields = array('functionallocation_name');

	// For Alphabetical search
	var $def_basicsearch_col = 'functionallocation_name';

	// Column value to use on detail view record text display
	var $def_detailview_recname = 'functionallocation_name';

	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	var $mandatory_fields = array('functionallocation_name', 'assigned_user_id');

	var $default_order_by = 'functionallocation_name';
	var $default_sort_order = 'ASC';

	function FunctionalLocations() {
		$this->log = LoggerManager::getLogger('FunctionalLocations');
		$this->db = PearDatabase::getInstance();
		$this->column_fields = getColumnFields('FunctionalLocations');
	}

	/**
	 * Invoked when special actions are performed on the module.
	 * @param String Module name
	 * @param String Event Type
	 */
	function vtlib_handler($moduleName, $eventType) {
		if ($eventType == 'module.postinstall') {
			//Enable ModTracker for the module
			static::enableModTracker($moduleName);
			//Create Related Lists
			static::createRelatedLists();
		} else if ($eventType == 'module.disabled') {
			// Handle actions before this module is being uninstalled.
		} else if ($eventType == 'module.preuninstall') {
			// Handle actions when this module is about to be deleted.
		} else if ($eventType == 'module.preupdate') {
			// Handle actions before this module is updated.
		} else if ($eventType == 'module.postupdate') {
			//Create Related Lists
			static::createRelatedLists();
		}
	}

	/**
	 * Enable ModTracker for the module
	 */
	public static function enableModTracker($moduleName) {
		include_once 'vtlib/Vtiger/Module.php';
		include_once 'modules/ModTracker/ModTracker.php';

		//Enable ModTracker for the module
		$moduleInstance = Vtiger_Module::getInstance($moduleName);
		ModTracker::enableTrackingForModule($moduleInstance->getId());
	}

	protected static function createRelatedLists() {
		include_once('vtlib/Vtiger/Module.php');
	}
	public function getNonAdminAccessControlQuery($module, $user, $scope = '') {
		global $current_user;
		require_once('include/utils/GeneralUtils.php');
		$data = getUserDetailsBasedOnEmployeeModuleG($current_user->user_name);
		if (empty($data)) {
			return parent::getNonAdminAccessControlQuery($module, $user, $scope = '');
		}
		if (($data['cust_role'] == 'Service Manager' &&
			($data['sub_service_manager_role'] == 'Regional Manager'
				|| $data['sub_service_manager_role'] == 'Regional Service Manager'))) {
			return parent::getNonAdminAccessControlQuery($module, $user, $scope = '');
		} else {
			return '';
		}
	}
}
