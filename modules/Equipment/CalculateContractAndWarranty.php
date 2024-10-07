<?php
include_once('include/utils/GeneralConfigUtils.php');
function CalculateContractAndWarranty($entityData) {
	$recordInfo = $entityData->{'data'};
	$id = $recordInfo['id'];
	$id = explode('x', $id);
	$id = $id[1];
	$warrantyStatus = $recordInfo['eq_run_war_st'];
	$cotractStartDate = $recordInfo['cont_start_date'];
	$contractEndDate = $recordInfo['cont_end_date'];

	$warrantyTerms = $recordInfo['equip_war_terms'];
	$beginGurantee = $recordInfo['cust_begin_guar'];
	$lastHMR = $recordInfo['eq_last_hmr'];

	IgCalculateWarranty($id, $warrantyTerms, $lastHMR, $beginGurantee, $warrantyStatus);
	CalculateContract($warrantyStatus, $id, $cotractStartDate, $contractEndDate);
}