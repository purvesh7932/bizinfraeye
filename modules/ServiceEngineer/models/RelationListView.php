<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class ServiceEngineer_RelationListView_Model extends Vtiger_RelationListView_Model {

	public function getCreateViewUrl(){
		$createViewUrl = parent::getCreateViewUrl();
		$parentRecordModel				= $this->getParentRecordModel();
		return $createViewUrl.'&phone='.$parentRecordModel->get('phone').'&service_engineerid='. 
		$parentRecordModel->getId() .'&eq_sr_equip_model='.$parentRecordModel->get('eq_sr_equip_model');
	}

}
?>
