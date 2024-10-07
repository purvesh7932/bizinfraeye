<?php
require_once 'include/events/VTEventHandler.inc';
class AccountsHandler extends VTEventHandler {
	function handleEvent($eventName, $entityData) {
		global $adb;
		$modeid = $entityData->get('id');
		if ($eventName == 'vtiger.entity.beforesave') {
			$moduleName = $entityData->getModuleName();
			if ($moduleName == 'Contacts') {
				if ($_REQUEST['action'] == 'SaveAjax' && ($_REQUEST['field'] == 'con_approval_status' || isset($_REQUEST['con_approval_status'])) && $entityData->get('con_approval_status') == 'Rejected') {
					$modeid = $entityData->get('id');
					$result = $adb->pquery('SELECT rejection_reason FROM `vtiger_contactdetails` where contactid = ?', array($modeid));
					$rowData = $adb->fetchByAssoc($result, 0);
					if ($rowData) {
						$gstno = $rowData['rejection_reason'];
						if (empty($gstno)) {
							$response = new Vtiger_Response();
							$response->setEmitType(Vtiger_Response::$EMIT_JSON);
							$response->setError('Please Update the Rejection Reason');
							$response->emit();
							die();
						}
					}
				} else if ($entityData->get('con_approval_status') == 'Rejected' && $_REQUEST['action'] == 'Save' && empty($entityData->get('rejection_reason'))) {
					$exception = new DuplicateException(vtranslate('LBL_DUPLICATES_DETECTED'));
					$exception->setSpecialError('Please Update the Rejection Reason');
					throw $exception;
				} else if ($_REQUEST['action'] == 'SaveAjax' && ($_REQUEST['field'] == 'rejection_reason' || isset($_REQUEST['rejection_reason'])) && empty($entityData->get('rejection_reason'))) {
					$modeid = $entityData->get('id');
					$result = $adb->pquery('SELECT con_approval_status FROM `vtiger_contactdetails` where contactid = ?', array($modeid));
					$rowData = $adb->fetchByAssoc($result, 0);
					if ($rowData) {
						$gstno = $rowData['con_approval_status'];
						if ($gstno == 'Rejected') {
							$response = new Vtiger_Response();
							$response->setEmitType(Vtiger_Response::$EMIT_JSON);
							$response->setError('Please Change the Approval Status Befoe Making Rejection Reason Empty');
							$response->emit();
							die();
						}
					}
				}
			} else if ($moduleName == 'ServiceEngineer') {
				if ($_REQUEST['action'] == 'SaveAjax' && ($_REQUEST['field'] == 'approval_status' || isset($_REQUEST['approval_status'])) && $entityData->get('approval_status') == 'Rejected') {
					$modeid = $entityData->get('id');
					$result = $adb->pquery('SELECT rejection_reason FROM `vtiger_serviceengineer` where serviceengineerid = ?', array($modeid));
					$rowData = $adb->fetchByAssoc($result, 0);
					if ($rowData) {
						$gstno = $rowData['rejection_reason'];
						if (empty($gstno)) {
							$response = new Vtiger_Response();
							$response->setEmitType(Vtiger_Response::$EMIT_JSON);
							$response->setError('Please Update the Rejection Reason');
							$response->emit();
							die();
						}
					}
				} else if ($entityData->get('approval_status') == 'Rejected' && $_REQUEST['action'] == 'Save' && empty($entityData->get('rejection_reason'))) {
					$exception = new DuplicateException(vtranslate('LBL_DUPLICATES_DETECTED'));
					$exception->setSpecialError('Please Update the Rejection Reason');
					throw $exception;
				} else if ($_REQUEST['action'] == 'SaveAjax' && ($_REQUEST['field'] == 'rejection_reason' || isset($_REQUEST['rejection_reason'])) && empty($entityData->get('rejection_reason'))) {
					$modeid = $entityData->get('id');
					$result = $adb->pquery('SELECT approval_status FROM `vtiger_serviceengineer` where serviceengineerid = ?', array($modeid));
					$rowData = $adb->fetchByAssoc($result, 0);
					if ($rowData) {
						$gstno = $rowData['approval_status'];
						if ($gstno == 'Rejected') {
							$response = new Vtiger_Response();
							$response->setEmitType(Vtiger_Response::$EMIT_JSON);
							$response->setError('Please Change the Approval Status Befoe Making Rejection Reason Empty');
							$response->emit();
							die();
						}
					}
				}

				// After All The Role Confirmation This Will be Enabled
				// if ($_REQUEST['action'] == 'SaveAjax' && ($_REQUEST['field'] == 'approval_status' || isset($_REQUEST['approval_status'])) && $entityData->get('approval_status') == 'Accepted') {
				// 	$modeid = $entityData->get('id');
				// 	$result = $adb->pquery('SELECT sys_detect_role FROM `vtiger_serviceengineer` where serviceengineerid = ?', array($modeid));
				// 	$rowData = $adb->fetchByAssoc($result, 0);
				// 	if ($rowData) {
				// 		$systemDetectedRole = $rowData['sys_detect_role'];
				// 		if (empty($systemDetectedRole)) {
				// 			$response = new Vtiger_Response();
				// 			$response->setEmitType(Vtiger_Response::$EMIT_JSON);
				// 			$response->setError('Unable To Find Role Of This Employee');
				// 			$response->emit();
				// 			die();
				// 		}
				// 		$sql = "SELECT * FROM `vtiger_role` where rolename = ?";
				// 		$result = $adb->pquery($sql, array($systemDetectedRole));
				// 		$dataRow = $adb->fetchByAssoc($result, 0);
				// 		if (empty($dataRow['roleid'])) {
				// 			$response = new Vtiger_Response();
				// 			$response->setEmitType(Vtiger_Response::$EMIT_JSON);
				// 			$response->setError('Unable To Find Role Of This Employee');
				// 			$response->emit();
				// 			die();
				// 		}
				// 	}
				// } else if ($entityData->get('approval_status') == 'Accepted' && $_REQUEST['action'] == 'Save' && empty($entityData->get('sys_detect_role'))) {
				// 	$exception = new DuplicateException(vtranslate('LBL_DUPLICATES_DETECTED'));
				// 	$exception->setSpecialError('Unable To Find Role Of This Employee');
				// 	throw $exception;
				// }
				if ($_REQUEST['action'] == 'SaveAjax' && ($_REQUEST['field'] == 'cust_role' || isset($_REQUEST['cust_role'])) && $entityData->get('cust_role') == 'Service Engineer') {
					$result = $adb->pquery('SELECT ser_usr_log_plat FROM `vtiger_serviceengineer` where serviceengineerid = ?', array($modeid));
					$rowData = $adb->fetchByAssoc($result, 0);
					if ($rowData) {
						$gstno = $rowData['ser_usr_log_plat'];
						if ($gstno != 'Mobile App') {
							$response = new Vtiger_Response();
							$response->setEmitType(Vtiger_Response::$EMIT_JSON);
							$response->setError('Please Set Accessing Portal to Mobile App Before Chnaging To Service Engineer');
							$response->emit();
							die();
						}
					}
				} else if ($entityData->get('cust_role') == 'Service Engineer' && $_REQUEST['action'] == 'Save' && empty($entityData->get('cust_role'))) {
					$exception = new DuplicateException(vtranslate('LBL_DUPLICATES_DETECTED'));
					$exception->setSpecialError('Please Set Accessing Portal to Mobile App Before Chnaging To Service Engineer');
					throw $exception;
				} else if ($_REQUEST['action'] == 'SaveAjax' && ($_REQUEST['field'] == 'ser_usr_log_plat' || isset($_REQUEST['ser_usr_log_plat'])) && $entityData->get('ser_usr_log_plat') != "Mobile App") {
					$modeid = $entityData->get('id');
					$result = $adb->pquery('SELECT cust_role FROM `vtiger_serviceengineer` where serviceengineerid = ?', array($modeid));
					$rowData = $adb->fetchByAssoc($result, 0);
					if ($rowData) {
						$gstno = $rowData['cust_role'];
						if ($gstno == 'Service Engineer') {
							$response = new Vtiger_Response();
							$response->setEmitType(Vtiger_Response::$EMIT_JSON);
							$response->setError('For Service Engineer You Can Set Accessing Portals To Only Mobile App');
							$response->emit();
							die();
						}
					}
				}

				if ($_REQUEST['action'] == 'SaveAjax' && ($_REQUEST['field'] == 'cust_role' || isset($_REQUEST['cust_role'])) && $entityData->get('cust_role') == 'Service Manager') {
					$result = $adb->pquery('SELECT sub_service_manager_role FROM `vtiger_serviceengineer` where serviceengineerid = ?', array($modeid));
					$rowData = $adb->fetchByAssoc($result, 0);
					if ($rowData) {
						$gstno = $rowData['sub_service_manager_role'];
						if (empty($gstno)) {
							$response = new Vtiger_Response();
							$response->setEmitType(Vtiger_Response::$EMIT_JSON);
							$response->setError('Please Update Role Before Chnaging To Service Manager');
							$response->emit();
							die();
						}
					}
				} else if ($entityData->get('cust_role') == 'Service Manager' && $_REQUEST['action'] == 'Save' && empty($entityData->get('cust_role'))) {
					$result = $adb->pquery('SELECT sub_service_manager_role FROM `vtiger_serviceengineer` where serviceengineerid = ?', array($modeid));
					$rowData = $adb->fetchByAssoc($result, 0);
					if ($rowData) {
						$gstno = $rowData['sub_service_manager_role'];
						if (empty($gstno)) {
							$exception = new DuplicateException(vtranslate('LBL_DUPLICATES_DETECTED'));
							$exception->setSpecialError('Please Update Role Before Chnaging To Service Manager');
							throw $exception;
						}
					}
				} else if ($_REQUEST['action'] == 'SaveAjax' && ($_REQUEST['field'] == 'sub_service_manager_role' || isset($_REQUEST['sub_service_manager_role'])) && empty($entityData->get('sub_service_manager_role'))) {
					$modeid = $entityData->get('id');
					$result = $adb->pquery('SELECT cust_role FROM `vtiger_serviceengineer` where serviceengineerid = ?', array($modeid));
					$rowData = $adb->fetchByAssoc($result, 0);
					if ($rowData) {
						$gstno = $rowData['cust_role'];
						if ($gstno == 'Service Manager') {
							$response = new Vtiger_Response();
							$response->setEmitType(Vtiger_Response::$EMIT_JSON);
							$response->setError('Please Change Access Level To Service Engineer Before Updating Role To Empty');
							$response->emit();
							die();
						}
					}
				}
			} else if ($moduleName == 'HelpDesk') {
				$recordId = $entityData->get('equipment_id');
				$tiketType = $entityData->get('ticket_type');
				$fields = $this->getFieldsOfCategory($tiketType, $entityData->get('purpose'));
				if (in_array('hmr', $fields) && $_REQUEST['action'] == 'Save') {
					if (!empty($recordId)) {
						// handle HMR Funtionality
						$hmr = $entityData->get('hmr');
						if (empty($hmr)) {
							$hmr = 0;
						}
						$lastHMR = $this->getLastHMR($recordId);
						if (empty($modeid)) {
							if ($lastHMR  > $hmr) {
								$exception = new DuplicateException('Current HMR Value Is Less Than Last HMR Value, Current HMR Value Is ' . $lastHMR, 200);
								$exception->setModule($moduleName);
								$exception->setSpecialError('Current HMR Value Is Less Than Last HMR Value, Current HMR Value Is ' . $lastHMR);
								throw $exception;
							}
						} else {
							if ($lastHMR  > $hmr) {
								$exception = new DuplicateException('Current HMR Value Is Less Than Last HMR Value, Current HMR Value Is ' . $lastHMR, 200);
								$exception->setModule($moduleName);
								$exception->setSpecialError('Current HMR Value Is Less Than Last HMR Value, Current HMR Value Is ' . $lastHMR);
								throw $exception;
							}
						}
					}
				}
				if ($_REQUEST['action'] == 'SaveAjax' && ($_REQUEST['field'] == 'tket_acc_status' || isset($_REQUEST['tket_acc_status'])) && $entityData->get('tket_acc_status') == 'Rejected') {
					$result = $adb->pquery('SELECT rejection_reason FROM `vtiger_serviceengineer` where serviceengineerid = ?', array($modeid));
					$rowData = $adb->fetchByAssoc($result, 0);
					if ($rowData) {
						$gstno = $rowData['rejection_reason'];
						if (empty($gstno)) {
							$response = new Vtiger_Response();
							$response->setEmitType(Vtiger_Response::$EMIT_JSON);
							$response->setError('Please Update the Rejection Reason');
							$response->emit();
							die();
						}
					}
				} else if ($entityData->get('tket_acc_status') == 'Rejected' && $_REQUEST['action'] == 'Save' && empty($entityData->get('rejection_reason'))) {
					$exception = new DuplicateException(vtranslate('LBL_DUPLICATES_DETECTED'));
					$exception->setSpecialError('Please Update the Rejection Reason');
					throw $exception;
				} else if ($_REQUEST['action'] == 'SaveAjax' && ($_REQUEST['field'] == 'rejection_reason' || isset($_REQUEST['rejection_reason'])) && empty($entityData->get('rejection_reason'))) {
					$modeid = $entityData->get('id');
					$result = $adb->pquery('SELECT tket_acc_status FROM `vtiger_troubletickets` where ticketid = ?', array($modeid));
					$rowData = $adb->fetchByAssoc($result, 0);
					if ($rowData) {
						$gstno = $rowData['tket_acc_status'];
						if ($gstno == 'Rejected') {
							$response = new Vtiger_Response();
							$response->setEmitType(Vtiger_Response::$EMIT_JSON);
							$response->setError('Please Change the Acceptence Status Befoe Making Rejection Reason Empty');
							$response->emit();
							die();
						}
					}
				}

				if ($_REQUEST['action'] == 'SaveAjax' && ($_REQUEST['field'] == 'ticketstatus' || isset($_REQUEST['ticketstatus']))) {
					$status = $entityData->get('ticketstatus');
					$notificationNumber = $entityData->get('external_app_num');
					include_once('include/utils/GeneralUtils.php');
					$sapMessage = changeNotifiationStatus($status, $notificationNumber, $entityData->getId());
					if ($sapMessage['success'] == false) {
						$response = new Vtiger_Response();
						$response->setEmitType(Vtiger_Response::$EMIT_JSON);
						$response->setError($sapMessage['message']);
						$response->emit();
						exit();
					}
				}
			} else if ($moduleName == 'RecommissioningReports') {
				if ($_REQUEST['action'] == 'SaveAjax' && ($_REQUEST['field'] == 'ticketstatus' || isset($_REQUEST['ticketstatus']))) {
					include_once('include/utils/GeneralUtils.php');
					$recordInfo = $entityData->{'data'};
					$status = $entityData->get('ticketstatus');
					$ticketId = $entityData->get('ticket_id');
					if (empty($ticketId)) {
						$response = new Vtiger_Response();
						$response->setEmitType(Vtiger_Response::$EMIT_JSON);
						$response->setError("Unable To Find Service Request Information Associated with Current Recommissioning Report");
						$response->emit();
						exit();
					}
					$dataArr = getSingleColumnValue(array(
						'table' => 'vtiger_troubletickets',
						'columnId' => 'ticketid',
						'idValue' => $ticketId,
						'expectedColValue' => 'external_app_num'
					));
					$notificationNumber = $dataArr[0]['external_app_num'];
					$sapMessage = changeNotifiationStatus($status, $notificationNumber, $ticketId);
					if ($sapMessage['success'] == false) {
						$response = new Vtiger_Response();
						$response->setEmitType(Vtiger_Response::$EMIT_JSON);
						$response->setError($sapMessage['message']);
						$response->emit();
						exit();
					} else {
						$query = "UPDATE vtiger_troubletickets SET status = ? WHERE ticketid=?";
						$adb->pquery($query, array($status, $ticketId));
					}
				}
			} else if ($moduleName == 'HMREntries') {
				$recordId = $entityData->get('equipment_id');
				if ($_REQUEST['action'] == 'Save') {
					if (!empty($recordId)) {
						$hmr = $entityData->get('hmr_value');
						if (empty($hmr)) {
							$hmr = 0;
						}
						$lastHMR = $this->getLastHMR($recordId);
						if (empty($modeid)) {
							if ($lastHMR  > $hmr) {
								$exception = new DuplicateException('Current HMR Value Is Less Than Last HMR Value, Current HMR Value Is ' . $lastHMR, 200);
								$exception->setModule($moduleName);
								$exception->setSpecialError('Current HMR Value Is Less Than Last HMR Value, Current HMR Value Is ' . $lastHMR);
								throw $exception;
							}
						}
						include_once('include/utils/GeneralUtils.php');
						$sapMessage = updateHMRINExternalApp($recordId, $hmr);
						if ($sapMessage['success'] == false) {
							$exception = new DuplicateException($sapMessage['message']);
							$exception->setModule($moduleName);
							$exception->setSpecialError($sapMessage['message']);
							throw $exception;
						}
					}
				}
			} else if ($moduleName == 'ServiceOrders') {
				$recordId = $entityData->get('ticket_id');
				if ($_REQUEST['action'] == 'Save') {
					if (!empty($recordId)) {
						global $adb;
						$sql = 'select external_app_num from vtiger_troubletickets where ticketid = ?';
						$sqlResult = $adb->pquery($sql, array(trim($recordId)));
						$num_rows = $adb->num_rows($sqlResult);
						$generatedOrderId = '';
						if ($num_rows > 0) {
							$dataRow = $adb->fetchByAssoc($sqlResult, 0);
							$generatedOrderId = $dataRow['external_app_num'];
						} else {
							$generatedOrderId = '';
						}
						if (empty($generatedOrderId)) {
							$exception = new DuplicateException('Notification Is Not Created In SAP, For This Service Request ', 200);
							$exception->setModule($moduleName);
							$exception->setSpecialError('Notification Is Not Created In SAP, For This Service Request ');
							throw $exception;
						}
					}
				}
			}
		}

		if ($eventName == 'vtiger.entity.aftersave') {
			$moduleName = $entityData->getModuleName();
			if ($moduleName == 'FailedParts') {
				global $current_user, $adb;
				require_once('include/utils/GeneralUtils.php');
				$data = getUserDetailsBasedOnEmployeeModuleG($current_user->user_name);
				if (!empty($data) && $data['cust_role'] == "Service Manager") {
					handleUpdateOfFailedpartsDetails($entityData->getId());
					global $creationOfFailedPartRecord;
					$creationOfFailedPartRecord = true;
					handleUpdateOfFailedpartsDetailServiceEngineer($entityData->getId());
				} else if (!empty($data) && $data['cust_role'] == "Service Engineer") {
					handleUpdateOfFailedpartsDetailServiceEngineer($entityData->getId());
				} else {
					handleUpdateOfFailedpartsDetailsNonServiceManager($entityData->getId());
				}
			} else if ($moduleName == 'Equipment') {
				global $onlyFromWeb;
				if ($onlyFromWeb == true) {
					$recordId = $entityData->getId();
					require_once('include/utils/GeneralUtils.php');
					$dataArr = getSingleColumnValue(array(
						'table' => 'vtiger_equipment',
						'columnId' => 'equipmentid',
						'idValue' => $recordId,
						'expectedColValue' => 'total_year_cont'
					));
					$total_year_cont = $dataArr[0]['total_year_cont'];

					include_once('include/utils/IgClassUtils.php');
					IgClassUtils::saveLineDetailsEquipment(
						$total_year_cont,
						$recordId,
						'Equipment'
					);
				}
			}
		}
	}
	function detectHasSAPError($data) {
		if ($data) {
		}
	}
	function getLastHMR($recordId) {
		global $adb;
		$sql = 'select eq_last_hmr from vtiger_equipment where equipmentid = ?';
		$sqlResult = $adb->pquery($sql, array(trim($recordId)));
		$num_rows = $adb->num_rows($sqlResult);
		if ($num_rows > 0) {
			$dataRow = $adb->fetchByAssoc($sqlResult, 0);
			return (float)$dataRow['eq_last_hmr'];
		} else {
			return 0;
		}
	}

	function getFieldsOfCategory($type, $purposeValue) {
		if ($type == 'GENERAL INSPECTION') {
			$fieldDependeny = Vtiger_DependencyPicklist::getFieldsFitDependency('HelpDesk', 'purpose', 'ticketstatus');
			$type = $purposeValue;
		} else {
			$fieldDependeny = Vtiger_DependencyPicklist::getFieldsFitDependency('HelpDesk', 'ticket_type', 'ticketpriorities');
		}
		foreach ($fieldDependeny['valuemapping'] as $valueMapping) {
			if ($valueMapping['sourcevalue'] == $type) {
				return $valueMapping['targetvalues'];
			}
		}
	}
}
