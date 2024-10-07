<?php
include_once 'include/Webservices/DescribeObject.php';
class Mobile_WS_GetUserOfficeDependancy extends Mobile_WS_Controller {

    function requireLogin() {
        return false;
    }

    function process(Mobile_API_Request $request) {
        $response = new Mobile_API_Response();
        $dependentData = Vtiger_DependencyPicklist::getPickListDependency('ServiceEngineer', 'office', 'cust_role');

        $officeValue = $request->get('office');
        if (empty($officeValue)) {
            $response->setError(100, 'Office Value is Empty');
            return $response;
        }
        $fieldListPicklist = [];
        foreach ($dependentData['valuemapping'] as $valueMapping) {
            if ($valueMapping['sourcevalue'] == $officeValue) {
                $pickList = [];
                foreach ($valueMapping['targetvalues'] as $targetValue) {
                    if ($targetValue != "") {
                        array_push($pickList, array('cust_role' => decode_html($targetValue)));
                    }
                }
                $fieldListPicklist['cust_role'] = $pickList;
                break;
            }
        }
        $dependentFieldInfo = array(
            'Service Centre' => 'service_centre',
            'District Office' => 'district_office', 'Regional Office' => 'regional_office',
            'Production Division' => 'production_division', 'Activity Centre' => 'activity_centre'
        );
        foreach ($dependentFieldInfo as $key => $value) {
            if ($key == $officeValue) {
                $picklistvaluesmap = getAllPickListValues($value);
                $pickList = [];
                foreach ($picklistvaluesmap as $targetValue) {
                    array_push($pickList, array($value => decode_html($targetValue)));
                }
                $fieldListPicklist[$value] = $pickList;
            } else {
                $fieldListPicklist[$value] = [];
            }
        }
        $response->setApiSucessMessage('Successfully Fetched Data');
        $response->setResult($fieldListPicklist);
        return $response;
    }
}