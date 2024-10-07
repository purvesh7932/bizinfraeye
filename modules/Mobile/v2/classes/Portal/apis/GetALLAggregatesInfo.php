<?php
include_once('include/utils/GeneralUtils.php');
include_once('include/utils/GeneralConfigUtils.php');
class Portal_GetALLAggregatesInfo_API extends Portal_Default_API {

	public function process(Portal_Request $request) {
		$response = new Portal_Response();
		$record = $request->get('record');
		if (empty($record)) {
			$response->setError(100, "Record Is Missing");
			return $response;
		}
		if (strpos($record, 'x') == false) {
			$response->setError(100, 'Record Is Not Webservice Format');
			return $response;
		}
		$record = explode('x', $record);
		$record = $record[1];

        // To add permission of only linked equipments
		// $permitted = isPermitted('Equipment', 'DetailView', $record);
		// if (strcmp($permitted, "yes") === 0) {
		// } else {
		// 	$response->setError(100, 'Permission to read given object is denied');
		// 	return $response;
		// }

		$aggregates = array(
			'Chassis' => 'Chassis', 'Engine' => 'Engine(LH&RH)',
			'Transmission' => 'Transmission', 'RearAxle' => 'Rear Axle',
			'FinalDrive' => 'Final Drive & Tandem Assembly(Motor Grader)', 
			'FrontAxle' => 'Front Axle',
			'RH Final Drive' => 'RH Final Drive(Excavator)',
			'LH Final Drive' => 'LH Final Drive(Excavator)',
			'InductionMotor' => 'Induction Motor(LH & RH)',
			'TrackDrive' => 'Track Drive (LH & RH)'
		);

		$dataArr = getSingleColumnValue(array(
			'table' => 'vtiger_equipment',
			'columnId' => 'equipmentid',
			'idValue' => $record,
			'expectedColValue' => 'equipment_sl_no'
		));
		$equipmentSerialNum = $dataArr[0]['equipment_sl_no'];

		$dataArr = [];
		foreach ($aggregates as $aggregate => $label) {
			$obj = [];
			$obj['aggregate'] = $aggregate;
			$obj['aggregateLabel'] = $label;
			$obj['aggregateInfo'] = IGgetAggregateDetailsImproved($aggregate, $equipmentSerialNum, $record);
			if (empty($obj['aggregateInfo'])) {
				$obj['aggregateInfo'] = array(
					'equipment_sl_no' => '',
					'equip_war_terms' => '',
					'equip_ag_serial_no' => ''
				);
			}
			array_push($dataArr, $obj);
		}

		$responseObject['AllAggregatesInfo'] = $dataArr;
		$response->setApiSucessMessage('Successfully Fetched Data');
		$response->setResult($responseObject);

		return $response;
	}
}
