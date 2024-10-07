<?php
include_once 'include/Webservices/DescribeObject.php';
include_once 'include/Webservices/Query.php';

class Mobile_WS_FetchReferenceRecords extends Mobile_WS_Controller {
    
    function process(Mobile_API_Request $request) {
        
        $response = new Mobile_API_Response();
		$current_user = $this->getActiveUser();
        
        //fetch reference records request params
        $referenceModule = $request->get('module');
        $searchKey = $request->get('searchValue');
        
        if($referenceModule=='Documents') {
            $labelFields = 'notes_title';
        } else if($referenceModule=='HelpDesk') {
            $labelFields = 'ticket_title';
        } else {
            $describe = vtws_describe($referenceModule, $current_user);
            $labelFields = $describe['labelFields'];
        }
        
        $labelFieldsArray = explode(',', $labelFields);
        $sql = "";
        if ($referenceModule == 'Equipment') {
            $sql = sprintf("SELECT %s FROM %s WHERE ",$labelFields,$referenceModule);

            $model = $request->get('sr_equip_model');
            foreach($labelFieldsArray as $labelField) {
                if(empty($model)){
                    $sql .= $labelField . " LIKE '%" . $searchKey . "%' OR ";
                } else {
                    $sql .= " equip_model LIKE '%" . $model . "%' AND ";
                    $sql .= " equip_category = 'S' AND ";
                    $sql .= $labelField . " LIKE '%" . $searchKey . "%' OR ";
                }
            }
            $sql = rtrim($sql,' OR ') . ';';
        } else if ($referenceModule == 'Products') {
            $sql = sprintf("SELECT %s FROM %s WHERE ", $labelFields.',unit_price', $referenceModule);
            foreach ($labelFieldsArray as $labelField) {
                $sql .= $labelField . " LIKE '%" . $searchKey . "%' OR ";
            }
            $sql = rtrim($sql, ' OR ') . ';';
        } else {
            $sql = sprintf("SELECT %s FROM %s WHERE ",$labelFields,$referenceModule);
            foreach($labelFieldsArray as $labelField) {
                $sql .= $labelField . " LIKE '%" . $searchKey . "%' OR ";
            }
            $sql = rtrim($sql,' OR ') . ';';
        }

        $wsresult = vtws_query($sql,$current_user);
        
        $referenceRecords = array();
        if ($referenceModule == 'Products') {
            foreach ($wsresult as $result) {
                $record = array();
                foreach ($labelFieldsArray as $labelField) {
                    $record['label'] .= $result[$labelField] . ' ';
                }
                $record['label'] = trim($record['label']);
                $record['value'] = decode_html($result['id']);
                $record['unit_price'] = (float) number_format((float)$result['unit_price'], 2, '.', '');
                $referenceRecords[] = $record;
            }
        } else {
            foreach ($wsresult as $result) {
                $record = array();
                foreach ($labelFieldsArray as $labelField) {
                    $record['label'] .= $result[$labelField] . ' ';
                }
                $record['label'] = trim($record['label']);
                $record['value'] = decode_html($result['id']);
                $record['unit_price'] = (float) number_format((float)$result['unit_price'], 2, '.', '');
                $referenceRecords[] = $record;
            }
        }
        if ($referenceModule == 'Equipment') {
            require_once('include/utils/GeneralUtils.php');
            $model = $request->get('sr_equip_model');
            $data = getUserDetailsBasedOnEmployeeModuleG($current_user->user_name);
            if ($data && $data['cust_role'] == 'Service Manager' && $data['sub_service_manager_role'] == 'Service Manager Support') {
                $referenceRecords = getAllEquipmentsAssociatedWithSE('36x' . $data['serviceengineerid'], $searchKey, $model);
            } else if ($data && $data['cust_role'] == 'Service Engineer') {
                $referenceRecords = getAllEquipmentsAssociatedWithSE('36x' . $data['serviceengineerid'], $searchKey , $model);
            }
        }
        $res['referenceRecords'] =  $referenceRecords;
        $response->setResult($res);
        $response->setApiSucessMessage('Successfully Fetched Data');
        return $response;
    }
    
}
