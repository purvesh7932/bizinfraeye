<?php
include_once('include/utils/GeneralConfigUtils.php');
class ServiceReports_DetailRecordStructure_Model extends Vtiger_DetailRecordStructure_Model {

	public function getStructure() {
		global $log;
		$currentUsersModel = Users_Record_Model::getCurrentUserModel();
		if (!empty($this->structuredValues)) {
			return $this->structuredValues;
		}

		$values = array();
		$recordModel = $this->getRecord();
		$recordExists = !empty($recordModel);
		$moduleModel = $this->getModule();
		$blockModelList = $moduleModel->getBlocks();
		$tiketType = $recordModel->get('sr_ticket_type');
		$purposeValue = $recordModel->get('tck_det_purpose');
		$dependecyFieldList = $this->getFieldsOfCategory($tiketType, $purposeValue);

		//1 visual
		$ed = $recordModel->get('vis_chk_external_damages');
		$hal = $recordModel->get('vis_chk_hydraulic_air_leakages');
		$lub = $recordModel->get('vis_chk_lubrication');
		$ov = $recordModel->get('vis_chk_oil_levels');
		$wlh = $recordModel->get('vis_chk_work_loseing_hders');

		$edFieldDependency = ['vis_chk_ext_dam', 'vis_chk_ext_dam_img'];
		$halFieldDependency = ['vis_chk_hyd_air', 'vis_hyd_air_dam_img'];
		$lubFieldDependency = ['vis_chk_lub_rem', 'vis_lub_los_img'];
		$halFieldDependency = ['vis_chk_hyd_air', 'vis_hyd_air_dam_img'];
		$ovFieldDependency = ['vis_chk_oil_rem', 'vis_oil_lev_img'];
		$wlhFieldDependency = ['vis_chk_wrk_los', 'vis_hyd_wrk_los_img'];

		//2 action
		$at = $recordModel->get('eq_sta_aft_act_taken');

		$atFieldDependency = ['restoration_date', 'restoration_time'];
		$attFieldDependency = ['off_on_account_of', 'remarks_for_offroad'];

		//3 gereral check
		$ge = $recordModel->get('genchk_engine');
		$gt = $recordModel->get('genchk_transmission');
		$gb = $recordModel->get('genchk_brake');
		$gee = $recordModel->get('genchk_electrical');
		$gh = $recordModel->get('genchk_hydraulic');

		$geFieldDependency = ['genchk_oil_pressure', 'genchk_oil_temperature', 'genchk_coolant_temperature'];
		$gtFieldDependency = ['genchk_oil_pre_tr', 'genchk_oil_tr_tem'];
		$gbFieldDependency = ['genchk_air_pressure', 'genchk_brk_oil_tem'];
		$geeFieldDependency = ['genchk_motor', 'genchk_transformer', 'genchk_field_switch', 'genchk_auto_electrical_system', 'genchk_battery_voltage', 'genchk_hi_volt_ele_system'];
		$ghFieldDependency = ['genchk_cylinders','genchk_suspension','genchk_pumps','genchk_oil_cooler'];

		array_push($dependecyFieldList, 'ticketstatus');
		$blocksSeq = getTypeBlockSequence($tiketType, $purposeValue);

		$fieldToBlockDependencyNames = [
			'FAILURE_DETAILS', 'GENERAL_CHECKS'
		];
		$fieldToBlockDependency = [
			'FAILURE_DETAILS' => [
				'field' => 'ecd_can_be_com',
				'value' => 'No'
			], 'GENERAL_CHECKS' => [
				'field' => 'ecd_can_be_com',
				'value' => 'Yes'
			]
		];
		$manualEqValue = $recordModel->get('manual_equ_ser');
		foreach ($blocksSeq as $blocksSeqLabel) {
			foreach ($blockModelList as $blockLabel => $blockModel) {
				if (decode_html($blocksSeqLabel) == decode_html($blockLabel)) {
					if (in_array($blockLabel, $fieldToBlockDependencyNames) && ($tiketType == 'PRE-DELIVERY' || $tiketType == 'ERECTION AND COMMISSIONING')) {
						$dependInfo = $fieldToBlockDependency[$blockLabel];
						$igFieldVal = $recordModel->get($dependInfo['field']);
						if (empty($igFieldVal)) {
							continue;
						}
						if ($igFieldVal != $dependInfo['value']) {
							continue;
						}
					}
					$fieldModelList = $blockModel->getFields();
					if (!empty($fieldModelList)) {
						$values[$blockLabel] = array();
						foreach ($fieldModelList as $fieldName => $fieldModel) {
							if ($fieldName == 'manual_equ_ser' && empty($manualEqValue)) {
								continue;
							}
							if (in_array($fieldName, $edFieldDependency)) {
								if ($ed == 'NO' || empty($ed)) {
									continue;
								}
							}
							if (in_array($fieldName, $halFieldDependency)) {
								if ($hal == 'NO' || empty($hal)) {
									continue;
								}
							}
							if (in_array($fieldName, $lubFieldDependency)) {
								if ($lub == 'OK' || empty($lub)) {
									continue;
								}
							}
							if (in_array($fieldName, $ovFieldDependency)) {
								if ($ov == 'OK' || empty($ov)) {
									continue;
								}
							}
							if (in_array($fieldName, $wlhFieldDependency)) {
								if ($wlh == 'NO' || empty($wlh)) {
									continue;
								}
							}
							if (in_array($fieldName, $atFieldDependency)) {
								if ($at == 'Off Road') {
									continue;
								}
							}
							if (in_array($fieldName, $attFieldDependency)) {
								if ($at == 'On Road' || $at == 'Running with Problem') {
									continue;
								}
							}
							if ($tiketType != 'SERVICE FOR SPARES PURCHASED' && $purposeValue != 'WARRANTY CLAIM FOR SUB ASSEMBLY / OTHER SPARE PARTS') {
								if (in_array($fieldName, $geFieldDependency)) {
									if ($ge == 'Not Applicable' || empty($ge)) {
										continue;
									}
								}
							}
							if (in_array($fieldName, $gtFieldDependency)) {
								if ($gt == 'Not Applicable' || empty($get)) {
									continue;
								}
							}
							if (in_array($fieldName, $gbFieldDependency)) {
								if ($gb == 'Not Applicable' || empty($gb)) {
									continue;
								}
							}
							if (in_array($fieldName, $geeFieldDependency)) {
								if ($gee == 'Not Applicable' || empty($gee)) {
									continue;
								}
							}

							if (in_array($fieldName, $ghFieldDependency)) {
								if ($gh == 'Not Applicable' || empty($gh)) {
									continue;
								}
							}
							if ($fieldModel->isViewableInDetailView() && in_array($fieldName, $dependecyFieldList)) {
								if ($recordExists) {
									$value = $recordModel->get($fieldName);
									if (!$currentUsersModel->isAdminUser() && ($fieldModel->getFieldDataType() == 'picklist' || $fieldModel->getFieldDataType() == 'multipicklist')) {
										$value = decode_html($value);
										$this->setupAccessiblePicklistValueList($fieldModel);
									}
									$fieldModel->set('fieldvalue', $value);
								}
								$values[$blockLabel][$fieldName] = $fieldModel;
							}
						}
					}
					break;
				}
			}
		}
		$this->structuredValues = $values;
		return $values;
	}

	public function getFieldsOfCategory($type, $purposeValue) {
		if ($type == 'SERVICE FOR SPARES PURCHASED') {
			$fieldDependeny = Vtiger_DependencyPicklist::getFieldsFitDependency('ServiceReports', 'tck_det_purpose', 'type_of_conrt');
			$type = $purposeValue;
		} else {
			$fieldDependeny = Vtiger_DependencyPicklist::getFieldsFitDependency('ServiceReports', 'sr_ticket_type', 'sr_war_status');
		}
		foreach ($fieldDependeny['valuemapping'] as $valueMapping) {
			if ($valueMapping['sourcevalue'] == $type) {
				return $valueMapping['targetvalues'];
			}
		}
	}
}
