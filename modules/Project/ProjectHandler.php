<?php

class ProjectHandler extends VTEventHandler {

	function handleEvent($eventName, $entityData) {

		global $log, $adb;

		if ($eventName == 'vtiger.entity.aftersave') {
			$values = json_decode($_REQUEST['values']);
			$owners = $values->{'ownersofserv'};

			$moduleName = $entityData->getModuleName();
			if ($moduleName == 'Project') {

				$projectype = $entityData->get('projectype');
				$repeat = $entityData->get('cf_856');
				$nextRepeateDate = "";
				switch ($repeat) {
					case "hourly":
						$nextRepeateDate =  date("Y:m:d", strtotime("+0 day"));
						break;
					case "daily":
						if (strpos($entityData->get('cf_854'), '-')) {
							$nextRepeateDate = date('Y-m-d', strtotime($entityData->get('cf_854') . " +1 day"));
						} else {
							$nextRepeateDate =  date("Y:m:d", strtotime("+1 day"));
						}
						break;
					case "weekly":
						$nextRepeateDate =  date("Y:m:d", strtotime("+7 day"));
						break;
					case "monthly":
						$nextRepeateDate =  date("Y:m:d", strtotime("+30 day"));
						break;
					case "yearly":
						$nextRepeateDate =  date("Y:m:d", strtotime("+365 day"));
						break;
					default:
						$nextRepeateDate = '';
				}

				$projectid = $_REQUEST['currentid'];
				if ($nextRepeateDate != '') {
					$adb->pquery("update vtiger_projectcf set cf_854 = '$nextRepeateDate' where projectid = " . $projectid);
				}

				$modeid = $entityData->get('id');

				if (is_null($modeid) && !empty($projectype)) {

					$PtQuery = $adb->pquery("SELECT * from vtiger_inventoryproductrel where id=" . $projectype);

					$PtFieldsCount = $adb->num_rows($PtQuery);
					$prevTaskId = 0;
					for ($i = 0; $i < $PtFieldsCount; $i++) {
						$focus = CRMEntity::getInstance('ProjectTask');
						$Pt_Productname = $adb->query_result($PtQuery, $i, 'productname');
						$comment = $adb->query_result($PtQuery, $i, 'comment');
						$productId = (int) $adb->query_result($PtQuery, $i, 'productid');

						$dependency = (int) $adb->query_result($PtQuery, $i, 'dependency');

						if ($dependency == 'on') {
							$focus->column_fields['cf_862'] = $prevTaskId;
						}

						$taskTypeInstance = Vtiger_Record_Model::getInstanceById($productId);

						$statDateinInt = $taskTypeInstance->get("cf_858");
						$endDateinInt = $taskTypeInstance->get("cf_860");

						if (!empty($statDateinInt)) {
							$startDate = date("Y-m-d", strtotime("+$statDateinInt day"));
							$focus->column_fields['startdate'] = $startDate;
						} elseif ((int)$statDateinInt == 0) {
							$startDate = date("Y-m-d", strtotime("+$statDateinInt day"));
							$focus->column_fields['startdate'] = $startDate;
						}
						if (!empty((int)$endDateinInt)) {
							$endDate = date("Y-m-d", strtotime("+$endDateinInt day"));
							$focus->column_fields['enddate'] = $endDate;
						} elseif ((int)$endDateinInt == 0) {
							$endDate = date("Y-m-d", strtotime("+$endDateinInt day"));
							$focus->column_fields['enddate'] = $endDate;
						}

						$focus->id = $entityData->getId();
						$focus->column_fields['projecttaskname'] = $Pt_Productname;
						$focus->column_fields['projectid'] = $projectid;
						$focus->column_fields['description'] = $comment;
						$focus->column_fields['projecttaskpriority'] = 'high';
						$focus->column_fields['projecttaskstatus'] = 'Open';
						$focus->column_fields['assigned_user_id'] = $taskTypeInstance->get("assigned_user_id");

						if ($owners && $owners->{'26x' . $productId}) {
							$assigned = $owners->{'26x' . $productId};
							$assignedExploded = explode("x", $assigned);
							$focus->column_fields['assigned_user_id'] = $assignedExploded[1];
						}
						$focus->column_fields['projecttasktype'] = 'Other';

						$focus->save("ProjectTask");
						$prevTaskId = $focus->id;
					}
				}
			}
		}
	}
}
