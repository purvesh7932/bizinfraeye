<?php
include_once 'include/Webservices/DescribeObject.php';
class Mobile_WS_GetUserLevelDependancy extends Mobile_WS_Controller {

    function requireLogin() {
        return false;
    }

    function process(Mobile_API_Request $request) {
        $response = new Mobile_API_Response();
        $dependentData = Vtiger_DependencyPicklist::getPickListDependency('ServiceEngineer', 'cust_role', 'designaion');

        $officeValue = $request->get('cust_role');
        if (empty($officeValue)) {
            $response->setError(100, 'Access Level is Empty');
            return $response;
        }
        $officeValue1 = $request->get('office');
        // if (empty($officeValue1)) {
        //     $response->setError(100, 'Office Value is Empty');
        //     return $response;
        // }
        $fieldListPicklist = [];
        foreach ($dependentData['valuemapping'] as $valueMapping) {
            if ($valueMapping['sourcevalue'] == $officeValue) {
                $pickList = [];
                foreach ($valueMapping['targetvalues'] as $targetValue) {
                    if ($targetValue != "") {
                        array_push($pickList, array('designaion' => decode_html($targetValue)));
                    }
                }
                $fieldListPicklist['designaion'] = $pickList;
                break;
            }
        }
        $dependentFieldInfo = array(
            'Service Manager' => 'sub_service_manager_role'
        );
        foreach ($dependentFieldInfo as $key => $value) {
            if ($key == $officeValue) {
                $dependentData = Vtiger_DependencyPicklist::getPickListDependency('ServiceEngineer', 'office', 'sub_service_manager_role');
                foreach ($dependentData['valuemapping'] as $valueMapping) {
                    if ($valueMapping['sourcevalue'] == $officeValue1) {
                        $pickList = [];
                        foreach ($valueMapping['targetvalues'] as $targetValue) {
                            if ($targetValue != "") {
                                array_push($pickList, array($value => decode_html($targetValue)));
                            }
                        }
                        $fieldListPicklist[$value] = $pickList;
                        break;
                    } else {
                        $fieldListPicklist[$value] = [];
                    }
                }
            } else {
                $fieldListPicklist[$value] = [];
            }
        }
        $response->setApiSucessMessage('Successfully Fetched Data');
        $response->setResult($fieldListPicklist);
        return $response;
    }
}
