<?php

class CustomerPortal_GetProfileInfo extends CustomerPortal_API_Abstract {

	function process(CustomerPortal_API_Request $request) {
		$response = new CustomerPortal_API_Response();
		$current_user = $this->getActiveUser();

		if ($current_user) {
			$contactId = vtws_getWebserviceEntityId('Contacts', $this->getActiveCustomer()->id);
			$encodedContactImage = CustomerPortal_Utils::getImageDetailsASURL($this->getActiveCustomer()->id, 'Contacts');
			$accountId = $this->getParent($contactId);

			$contact = vtws_retrieve($contactId, $current_user);
			$contact = CustomerPortal_Utils::resolveRecordValues($contact);
			$contact['imagedata'] = $encodedContactImage['imagedata'];
			$contact['imagetype'] = $encodedContactImage['imagetype'];
			$contact['imagename'] = $encodedContactImage['url'];
			$response->addToResult('customer_details', $contact);

			if (!empty($accountId)) {
				$idComponents = explode('x', $accountId);
				$encodedAccountImage = CustomerPortal_Utils::getImageDetails($idComponents[1], 'Accounts');
				$account = vtws_retrieve($accountId, $current_user);
				$account = CustomerPortal_Utils::resolveRecordValues($account);
				$account['imagedata'] = $encodedAccountImage['imagedata'];
				$account['imagetype'] = $encodedAccountImage['imagetype'];
				$response->addToResult('company_details', $account);
			}
		}
		return $response;
	}

}
