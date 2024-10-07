<?php
class DiscussionBlog_Module_Model extends Vtiger_Module_Model {


	public function isQuickCreateSupported() {
		//SalesOrder module is not enabled for quick create
		return false;
	}

	/**
	 * Function to check whether the module is summary view supported
	 * @return <Boolean> - true/false
	 */
	public function isSummaryViewSupported() {
		return true;
	}

	public function isCommentEnabled() {
		return true;
	}

	function getUtilityActionsNames() {
		return array('Import', 'Export');
	}
}
