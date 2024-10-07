<?php
class Mobile_WS_GetPickListValuesOfField extends Mobile_WS_Controller {
    function process(Mobile_API_Request $request) {
        $response = new Mobile_API_Response();
        $field = $request->get('field');
        $dependentFieldVal = $request->get('dependentFieldVal');
        if (empty($field)) {
            $response->setError(100, 'Field Name Is Missing');
            return $response;
        }
        $dependent = $this->IsDependentField($field, $dependentFieldVal);
        $dependentField = '';
        if ($dependent != false) {
            $dependentField = true;
        }
        $pickList = [];
        if ($dependentField == true) {
            $dependent = json_decode(decode_html($dependent), true);
            foreach ($dependent as $targetValue) {
                array_push($pickList, array($field => decode_html($targetValue)));
            }
        } else {
            $picklistvaluesmap = getAllPickListValues($field);
            foreach ($picklistvaluesmap as $targetValue) {
                array_push($pickList, array($field => decode_html($targetValue)));
            }
        }
        $fieldListPicklist[$field] = $pickList;
        $response->setApiSucessMessage('Successfully Fetched Data');
        $response->setResult($fieldListPicklist);
        return $response;
    }
    function IsDependentField($fieldName, $sourcevalue) {
        global $adb;
        $sql = "SELECT * FROM `vtiger_picklist_dependency` where targetfield = ? ";
        $sqlResult = $adb->pquery($sql, array($fieldName));
        $num_rows = $adb->num_rows($sqlResult);
        if ($num_rows > 0) {
            while ($row = $adb->fetch_array($sqlResult)) {
                if ($row['sourcevalue'] == $sourcevalue) {
                    return $row['targetvalues'];
                }
            }
        } else {
            return false;
        }
    }
}
