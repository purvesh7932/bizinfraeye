<?php
class CustomerPortal_FetchReferenceRecords extends CustomerPortal_API_Abstract {

	function process(CustomerPortal_API_Request $request) {
		$response = new CustomerPortal_API_Response();
		$current_user = $this->getActiveUser();

		if ($current_user) {
			$customerId = $this->getActiveCustomer()->id;
			$contactWebserviceId = vtws_getWebserviceEntityId('Contacts', $customerId);
			$module = $request->get('module');
			$searchKey = $request->get('searchKey');

			if (!CustomerPortal_Utils::isModuleActive($module)) {
				throw new Exception("Module not accessible", 1412);
				exit;
			}

			$describe = vtws_describe($module, $current_user);
			$labelFields = $describe['labelFields'];
			//Describe giving wrong labels for HelpDesk and Documents.
			if ($module == 'Documents') {
				$labelFields = 'notes_title';
			}
			if ($module == 'HelpDesk') {
				$labelFields = 'ticket_title';
			}
			$sql = sprintf("SELECT %s FROM %s ", $labelFields, $module);
			$labelFieldsArray = explode(',', $labelFields);

			if (!empty($searchKey)) {
				$sql .= "WHERE ";
				foreach ($labelFieldsArray as $labelField) {
					$sql .= $labelField . " LIKE '%" . $searchKey . "%' OR ";
				}
				$sql = rtrim($sql, ' OR ');
			}
			$accountId = $this->getParent($contactWebserviceId);
			$mode = CustomerPortal_Settings_Utils::getDefaultMode($module);
			$relatedId = null;
			$referenceRecords = array();
			if ($mode == 'mine') {
				$relatedId = $contactWebserviceId;
				$result = vtws_query_related($sql, $relatedId, CustomerPortal_Utils::getRelatedModuleLabel($module), $current_user);
			} else if ($mode == 'all') {
				if (in_array($module, array('Products', 'Services'))) {
					$sql = $sql . ';';
					$result = vtws_query($sql, $current_user);
				} else {
					if (!empty($accountId)) {
						$relatedId = $accountId;
					} else {
						$relatedId = $contactWebserviceId;
					}
					$result = vtws_query_related($sql, $relatedId, CustomerPortal_Utils::getRelatedModuleLabel($module), $current_user);
				}
			}

			foreach ($result as $value) {
				$record = array();
				foreach ($labelFieldsArray as $labelField) {
					$record['label'].= ' ' . decode_html($value[$labelField]);
					$record['id'] = decode_html($value['id']);
				}
				$referenceRecords[] = $record;
			}
			
			if($module == 'Equipment'){
				require_once('include/utils/GeneralUtils.php');
				$otherRecordsFromRelation = getAllEquipmentsAssociatedWithContact($contactWebserviceId, $searchKey);
				$referenceRecords = array_merge($referenceRecords, $otherRecordsFromRelation);
			}
			
			$other = array();
			$other['label'] = 'Other';
			$other['id'] = 'Other';
			$referenceRecords[] = $other;
			$response->setResult($referenceRecords);
		}
		return $response;
	}

}
