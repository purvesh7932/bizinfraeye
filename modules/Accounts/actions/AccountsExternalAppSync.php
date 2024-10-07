<?php
class Accounts_AccountsExternalAppSync_Action extends Vtiger_IndexAjax_View {
	public function requiresPermission(\Vtiger_Request $request) {
		$permissions = parent::requiresPermission($request);
		$permissions[] = array('module_parameter' => 'module', 'action' => 'Export');
		return $permissions;
	}

	public function process(Vtiger_Request $request) {
		$response = new Vtiger_Response();
		$currentUser = Users_Record_Model::getCurrentUserModel();
		if ($currentUser->isAdminUser()) {
			global $adb;
			include_once('include/utils/GeneralUtils.php');
			$url = getExternalAppURL('getAllCustomers');
			$xml = file_get_contents($url);
			$xml = json_decode($xml);
			foreach ($xml as $key => $value) {
				$sapRefNum = trim($value->{'KUNNR'});
				$sql = 'select accountid from vtiger_account where external_app_num = ?';
				$sqlResult = $adb->pquery($sql, array($sapRefNum));
				$num_rows = $adb->num_rows($sqlResult);
				if ($num_rows > 0) {
					$dataRow = $adb->fetchByAssoc($sqlResult, 0);
					$recordModel = Vtiger_Record_Model::getInstanceById($dataRow['accountid'], 'Accounts');
					$recordModel->set('mode', 'edit');
					$recordModel->set('accountname', trim($value->{'NAME1'}));
					$recordModel->set('bill_city', trim($value->{'ORT01'}));
					$recordModel->set('email1', trim($value->{'SMTP_ADDR'}));
					$recordModel->set('phone', trim($value->{'TELF1'}));
					$recordModel->set('bill_code', trim($value->{'PSTLZ'}));

					$plantName = $this->getMappedRegion(trim($value->{'PSTLZ'}));
					$sql = "select groupid from vtiger_groups where groupname = ? ";
					$result = $adb->pquery($sql, array($plantName . '-Depot'));
					$dataRow = $adb->fetchByAssoc($result, 0);
					if (empty($dataRow['groupid'])) {
					} else {
						$recordModel->set('assigned_user_id', $dataRow['groupid']);
					}
					$recordModel->save();
				} else {
					$focus = CRMEntity::getInstance('Accounts');
					$focus->column_fields['accountname'] = trim($value->{'NAME1'});
					$focus->column_fields['bill_city'] = trim($value->{'ORT01'});
					$focus->column_fields['external_app_num'] = trim($sapRefNum);
					$focus->column_fields['email1'] = trim($value->{'SMTP_ADDR'});
					$focus->column_fields['phone'] = trim($value->{'TELF1'});
					$focus->column_fields['bill_code'] = trim($value->{'PSTLZ'});
					$focus->save("Accounts");
				}
			}
		} else {
			$response->setError("External App Sync Not Allowed");
			$response->emit();
		}
	}

	public function getMappedRegion($pincode) {
		global $pincodeDatabaseName, $pincodeDatabaseUser, $pincodeDatabaseNamePassword;
        $connection = mysqli_connect("localhost", $pincodeDatabaseUser, $pincodeDatabaseNamePassword , $pincodeDatabaseName);
		if (mysqli_connect_errno()) {
			echo "Database connection failed.";
		}
		$sql = "SELECT * FROM $pincodeDatabaseName.vtiger_pincodes inner join $pincodeDatabaseName.vtiger_pincodescf " .
			" on $pincodeDatabaseName.vtiger_pincodescf.pincodesid = $pincodeDatabaseName.vtiger_pincodes.pincodesid " .
			" WHERE pincode = ?";
		$stmt = $connection->prepare($sql);
		$stmt->bind_param("s", $pincode);
		$stmt->execute();
		$result = $stmt->get_result();
		$pincodes = [];
		while ($pincode = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
			return $pincode['pincode_regional_office'];
		}
	}
}
