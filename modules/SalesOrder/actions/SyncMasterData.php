<?php
class SalesOrder_SyncMasterData_Action extends Vtiger_IndexAjax_View {
	public function requiresPermission(\Vtiger_Request $request) {
		$permissions = parent::requiresPermission($request);
		$permissions[] = array('module_parameter' => 'module', 'action' => 'Export');
		return $permissions;
	}

	public function process(Vtiger_Request $request) {
		include_once('include/utils/GeneralUtils.php');
		global $adb;
		$response = new Vtiger_Response();
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		if ($currentUserModel->isAdminUser()) {
			global $adb;
			$url = getExternalAppURL('getSettingsDataOfSalesOrder');
			$xml = file_get_contents($url);
			$xml = json_decode($xml);

			$sortid = 0;
			foreach ($xml->{'IT_AUGRU'} as $key => $value) {
				$val = trim($value->{'BEZEI'});
				$code = trim($value->{'AUGRU'});
				$fieldName = 'order_for_reason';
				$otherField = 'code';
				$table = 'vtiger_' . $fieldName;
				$isExits = $this->checkAndInsertOption($table, $fieldName, $otherField, $val, $code);
				if ($isExits == false) {
					$new_id = $adb->getUniqueID('vtiger_' . $fieldName);
					++$sortid;
					$fieldId =  $fieldName . 'id';
					$sql = "INSERT INTO vtiger_$fieldName( $fieldId , $fieldName, presence,sortorderid,code) VALUES(?,?,?,?,?)";
					$arr = array($new_id, $val, 1, $sortid, $code);
					$adb->pquery($sql, $arr);
				}
			}

			$response->setResult(array('success' => true, 'data' => $xml));
			$response->emit();
		} else {
			$response->setError("External App Sync Not Allowed");
			$response->emit();
		}
	}

	public  function checkAndInsertOption($table, $fieldName, $otherField, $val, $code) {
		global $adb;
		$sql = "select 1 from $table where $fieldName = ? and $otherField = ?";
		$sqlResult = $adb->pquery($sql, array($val, $code));
		$num_rows = $adb->num_rows($sqlResult);
		if ($num_rows > 0) {
			return true;
		} else {
			return false;
		}
	}
}
