<?php
class Vendors_GetAllInitialData_Action extends Vtiger_IndexAjax_View {
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
			$sql = 'select * from vtiger_external_application_info where id = 1 ';
			$sqlResultSet = $adb->pquery($sql, array());
			$dataRow = $adb->fetchByAssoc($sqlResultSet, 0);
			$u = $dataRow['uname'];
			$p = $dataRow['pass'];
			$url = $dataRow['url'];
			$url = $url . "getAllVendors.php" . "?uta=" . $u . '&pws=' . $p;
			$xml = file_get_contents($url);
			$xml = json_decode($xml);
			foreach ($xml->{'IT_VENDOR'} as $key => $value) {
				$sapRefNum = trim($value->{'LIFNR'});
				$sql = 'select vendorid from vtiger_vendor where external_app_num = ?';
				$sqlResult = $adb->pquery($sql, array($sapRefNum));
				$num_rows = $adb->num_rows($sqlResult);
				if ($num_rows > 0) {
					$dataRow = $adb->fetchByAssoc($sqlResult, 0);
					$recordModel = Vtiger_Record_Model::getInstanceById($dataRow['vendorid'], 'Vendors');
					$recordModel->set('mode', 'edit');
					$recordModel->set('vendorname', trim($value->{'NAME1'}));
					$recordModel->set('city', trim($value->{'ORT01'}));
					$recordModel->set('email', trim($value->{'SMTP_ADDR'}));
					$recordModel->set('phone', trim($value->{'TELF1'}));
					$recordModel->set('postalcode', trim($value->{'PSTLZ'}));

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
					$focus = CRMEntity::getInstance('Vendors');
					$focus->column_fields['vendorname'] = trim($value->{'NAME1'});
					$focus->column_fields['city'] = trim($value->{'ORT01'});
					$focus->column_fields['external_app_num'] = trim($sapRefNum);
					$focus->column_fields['email'] = trim($value->{'SMTP_ADDR'});
					$focus->column_fields['phone'] = trim($value->{'TELF1'});
					$focus->column_fields['postalcode'] = trim($value->{'TELF1'});
					$focus->save("Vendors");
				}
			}
		} else {
			$response->setError("External App Sync Not Allowed");
			$response->emit();
		}
	}

	public function getMappedRegion($pincode) {
		$databaseName = 'pincodenew';
		$connection = mysqli_connect("localhost", "root", "", $databaseName);
		if (mysqli_connect_errno()) {
			echo "Database connection failed.";
		}
		$sql = "SELECT * FROM $databaseName.vtiger_pincodes inner join $databaseName.vtiger_pincodescf " .
			" on $databaseName.vtiger_pincodescf.pincodesid = $databaseName.vtiger_pincodes.pincodesid " .
			" WHERE pincode = ?";
		$stmt = $connection->prepare($sql);
		$stmt->bind_param("s", $pincode);
		$stmt->execute();
		$result = $stmt->get_result();
		while ($pincode = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
			return $pincode['pincode_regional_office'];
		}
	}
}
