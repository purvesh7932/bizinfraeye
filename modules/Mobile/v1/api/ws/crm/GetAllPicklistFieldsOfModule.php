<?php
include_once 'include/Webservices/DescribeObject.php';
class Mobile_WS_GetAllPicklistFieldsOfModule extends Mobile_WS_Controller {

    function process(Mobile_API_Request $request) {
        $current_user = CRMEntity::getInstance('Users');
        $current_user->id = $current_user->getActiveAdminId();
        $current_user->retrieve_entity_info($current_user->id, 'Users');
        $response = new Mobile_API_Response();

        if ($current_user) {
            $module = $request->get('module');
            if (empty($module)) {
                $response->setError(1501, "Module Is Not Specified.");
                return $response;
            }
            $describeInfo = vtws_describe($module, $current_user);
            $fieldListOnlyPicklist = [];
            foreach ($describeInfo['fields'] as $key => $value) {
                if (($value['name'] != 'assigned_user_id')) {
                    $value['default'] = decode_html($value['default']);
                    if ($value['type']['name'] === 'picklist') {
                        $pickList = $value['type']['picklistValues'];
                        foreach ($pickList as $pickListKey => $pickListValue) {
                            $pickList[$pickListKey] = array($value['name'] => decode_html($pickListValue['value']));
                        }
                        $fieldListOnlyPicklist[$value['name']] = $pickList;
                    } else if ($value['type']['name'] === 'multipicklist') {
                        $fieldName = $value['name'];
                        $picklistvaluesmap = getAllPickListValues($value['name']);
                        $pickList = [];
                        foreach ($picklistvaluesmap as $targetValue) {
                            array_push($pickList, array($fieldName => decode_html($targetValue)));
                        }
                        $fieldListOnlyPicklist[$value['name']] = $pickList;
                    } else if ($value['type']['name'] == 'radio') {
                        $fieldName = $value['name'];
                        $picklistvaluesmap = getAllPickListValues($value['name']);
                        $pickList = [];
                        foreach ($picklistvaluesmap as $targetValue) {
                            array_push($pickList, array($fieldName => decode_html($targetValue)));
                        }
                        $fieldListOnlyPicklist[$value['name']] = $pickList;
                    }
                }
            }
            $response->setApiSucessMessage('Successfully Fetched Data');
            $response->setResult($fieldListOnlyPicklist);
            return $response;
        }
    }
}
