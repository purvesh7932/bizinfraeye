<?php
include_once('include/utils/GeneralConfigUtils.php');
include_once('include/utils/GeneralUtils.php');
class HMREntries_SaveAjax_Action extends Vtiger_SaveAjax_Action {

	public function process(Vtiger_Request $request) {
		$fieldToBeSaved = $request->get('field');
		$response = new Vtiger_Response();
		try {
			$recordModel = '';
			vglobal('VTIGER_TIMESTAMP_NO_CHANGE_MODE', $request->get('_timeStampNoChangeMode', false));
			if ($request->get('action') == 'SaveAjax') {
				$recordId = $request->get('equipment_id');
				if (!empty($recordId)) {
					$hmr = $request->get('hmr_value');
					if (empty($hmr)) {
						$hmr = 0;
					}
					$lastHMR = IGgetLastHMR($recordId);
					if ($lastHMR  > $hmr) {
						$response->setError('Current HMR Value Is Less Than Last HMR Value, Current HMR Value Is ' . $lastHMR);
						$response->emit();
						exit();
					}
					$sapMessage = updateHMRINExternalApp($recordId, $hmr);
					if ($sapMessage['success'] == false) {
						$response = new Vtiger_Response();
						$response->setEmitType(Vtiger_Response::$EMIT_JSON);
						$response->setError($sapMessage['message']);
						$response->emit();
						exit();
					}
					$recordModel = $this->saveRecord($request);
				} else {
					$recordModel = $this->saveRecord($request);
				}
			} else {
				$recordModel = $this->saveRecord($request);
			}
			vglobal('VTIGER_TIMESTAMP_NO_CHANGE_MODE', false);

			$fieldModelList = $recordModel->getModule()->getFields();
			$result = array();
			$picklistColorMap = array();
			foreach ($fieldModelList as $fieldName => $fieldModel) {
				if ($fieldModel->isViewable()) {
					$recordFieldValue = $recordModel->get($fieldName);
					if (is_array($recordFieldValue) && $fieldModel->getFieldDataType() == 'multipicklist') {
						foreach ($recordFieldValue as $picklistValue) {
							$picklistColorMap[$picklistValue] = Settings_Picklist_Module_Model::getPicklistColorByValue($fieldName, $picklistValue);
						}
						$recordFieldValue = implode(' |##| ', $recordFieldValue);
					}
					if ($fieldModel->getFieldDataType() == 'picklist') {
						$picklistColorMap[$recordFieldValue] = Settings_Picklist_Module_Model::getPicklistColorByValue($fieldName, $recordFieldValue);
					}
					$fieldValue = $displayValue = Vtiger_Util_Helper::toSafeHTML($recordFieldValue);
					if ($fieldModel->getFieldDataType() !== 'currency' && $fieldModel->getFieldDataType() !== 'datetime' && $fieldModel->getFieldDataType() !== 'date' && $fieldModel->getFieldDataType() !== 'double') {
						$displayValue = $fieldModel->getDisplayValue($fieldValue, $recordModel->getId());
					}
					if ($fieldModel->getFieldDataType() == 'currency') {
						$displayValue = Vtiger_Currency_UIType::transformDisplayValue($fieldValue);
					}
					if (!empty($picklistColorMap)) {
						$result[$fieldName] = array('value' => $fieldValue, 'display_value' => $displayValue, 'colormap' => $picklistColorMap);
					} else {
						$result[$fieldName] = array('value' => $fieldValue, 'display_value' => $displayValue);
					}
				}
			}

			//Handling salutation type
			if ($request->get('field') === 'firstname' && in_array($request->getModule(), array('Contacts', 'Leads'))) {
				$salutationType = $recordModel->getDisplayValue('salutationtype');
				$firstNameDetails = $result['firstname'];
				$firstNameDetails['display_value'] = $salutationType . " " . $firstNameDetails['display_value'];
				if ($salutationType != '--None--') $result['firstname'] = $firstNameDetails;
			}

			// removed decode_html to eliminate XSS vulnerability
			$result['_recordLabel'] = decode_html($recordModel->getName());
			$result['_recordId'] = $recordModel->getId();
			$response->setEmitType(Vtiger_Response::$EMIT_JSON);
			$response->setResult($result);
		} catch (DuplicateException $e) {
			$response->setError($e->getMessage(), $e->getDuplicationMessage(), $e->getMessage());
		} catch (Exception $e) {
			$response->setError($e->getMessage());
		}
		$response->emit();
	}
}
