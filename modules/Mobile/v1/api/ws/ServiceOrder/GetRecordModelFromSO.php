<?php
include_once('include/utils/GeneralUtils.php');
include_once 'include/Webservices/DescribeObject.php';
class Mobile_WS_GetRecordModelFromSO extends Mobile_WS_Controller {
    function process(Mobile_API_Request $request) {
        $response = new Mobile_API_Response();
        $moduleName = 'ServiceOrders';
        $recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);

        $sourceRecord = $request->get('sourceRecord');
        $sourceModule = $request->get('sourceModule');
        if (empty($sourceRecord)) {
            $response->setError(100, "sourceRecord Is Missing");
            return $response;
        }
        if (strpos($sourceRecord, 'x') == false) {
            $response->setError(100, 'sourceRecord Is Not Webservice Format');
            return $response;
        }
        $sourceRecord = explode('x', $sourceRecord);
        $sourceRecord = $sourceRecord[1];

        $nonEditKeys = [];
        if (!empty($sourceRecord)) {

            $soId = $this->getFirstServiceOrderOfSR($sourceRecord);
            if (empty($soId)) {
                $response->setError(100, "ServiceOrder Has Not Been Generated For This Service Request");
                return $response;
            }

            $parentRecordModel = ServiceOrders_Record_Model::getInstanceById($soId);
            $recordModel = Vtiger_Record_Model::getCleanInstance('ServiceOrders');
            $parentRecordModuleName = $parentRecordModel->getModuleName();
            if ($parentRecordModuleName == 'ServiceOrders') {
                $db = PearDatabase::getInstance();
                $sql = 'SELECT external_app_num,equipment_id FROM `vtiger_troubletickets` 
				where ticketid = ?';
                $sqlResult = $db->pquery($sql, array($sourceRecord));
                $dataRow = $db->fetchByAssoc($sqlResult, 0);
                $recordModel->set('your_ref', $dataRow['external_app_num']);
                array_push($nonEditKeys, 'your_ref');
                $equipmentNum = $dataRow['equipment_id'];
                $sql = 'SELECT external_app_num FROM `vtiger_serviceorders`
				where ticket_id = ?';
                $sqlResult = $db->pquery($sql, array($sourceRecord));
                $dataRow = $db->fetchByAssoc($sqlResult, 0);
                $recordModel->set('our_ref', $dataRow['external_app_num']);
                array_push($nonEditKeys, 'our_ref');
                $equipId =  $equipmentNum;
                $SAPrefEquip = '';
                if (isRecordExists($equipId)) {
                    $recordInstance = Vtiger_Record_Model::getInstanceById($equipId);
                    $SAPrefEquip = $recordInstance->get('equipment_sl_no');
                }
                array_push($nonEditKeys, 'collective_no');
                $recordModel->set('collective_no', $SAPrefEquip);

                global $adb;
                $currentUserModal = Users_Record_Model::getCurrentUserModel();
                $badge_no = $currentUserModal->get('user_name');
                $sql = "SELECT e.regional_office FROM vtiger_serviceengineer as e WHERE e.badge_no=? 
                ORDER BY serviceengineerid DESC LIMIT 1";
                $sqlResult = $adb->pquery($sql, array(decode_html($badge_no)));
                $sqlData = $adb->fetch_array($sqlResult);
                $plant_name = $sqlData['regional_office'] . "-Depot";
                $sql = "SELECT m.maintenanceplantid,m.plant_name FROM vtiger_maintenanceplant as m WHERE m.plant_name=?";
                $sqlResult = $adb->pquery($sql, array(decode_html($plant_name)));
                $sqlData = $adb->fetch_array($sqlResult);
                if (!empty($sqlData['maintenanceplantid'])) {
                    $tabId = Mobile_WS_Utils::getEntityModuleWSId('MaintenancePlant');
                    $recordModel->set('rec_plant_name', $tabId . 'x' . $sqlData['maintenanceplantid']);
                    $recordModel->set('rec_plant_name_label', $sqlData['plant_name']);
                    array_push($nonEditKeys, 'rec_plant_name', 'rec_plant_name_label');
                }
            }

            $data = $recordModel->getData();

            $recordModel = [];
            foreach ($data as $key => $value) {
                if (in_array($key, $nonEditKeys)) {
                    $recordModel[$key] = decode_html($value);
                }
            }
            $recordModel['lsi_company_code '] = '1000';
            $recordModel['lsi_purchase_org'] = 'SP01';
            $responseObject['recordValues'] = $recordModel;
            $responseObject['LineItems'] = $this->getAllLineItemForParent($soId);
            $response->setResult($responseObject);
            $response->setApiSucessMessage('Successfully Fetched Data');
            return $response;
        }
    }

    public function getAllLineItemForParent($parentId) {
        $result = null;
        global $adb;
        if (!is_array($parentId)) {
            $parentId = array($parentId);
        }
        $viewableFields = $this->geAllowedFieldsInLineItem('ServiceOrders', 'Item Details');

        $otherFields = array('productname', 'lineitem_id');
        $viewableFields = array_merge($viewableFields, $otherFields);

        $query = "SELECT vtiger_crmentity.label AS productname,vtiger_crmentity.setype AS entitytype,vtiger_crmentity.deleted AS deleted,vtiger_inventoryproductrel.*
						FROM vtiger_inventoryproductrel
						LEFT JOIN vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_inventoryproductrel.productid
						WHERE id IN (" . generateQuestionMarks($parentId) . ")";
        $transactionSuccessful = vtws_runQueryAsTransaction($query, array($parentId), $result);
        if (!$transactionSuccessful) {
            throw new WebServiceException(WebServiceErrorCode::$DATABASEQUERYERROR, 'Database error while performing required operation');
        }
        $lineItemList = array();
        if ($result) {
            $rowCount = $adb->num_rows($result);
            for ($i = 0; $i < $rowCount; ++$i) {
                $element = $adb->query_result_rowdata($result, $i);
                $element['parent_id'] = $parentId;
                $productName = $element['productname'];
                $entityType = $element['entitytype'];
                $lineItemId =  $element['lineitem_id'];
                $element['product_name'] = $productName;
                $element['entity_type'] = $entityType;
                $element['lineitem_id'] = $lineItemId;

                $elementNew = [];
                foreach ($element as $key => $value) {
                    if ($key == '0') {
                        continue;
                    }
                    if (in_array($key, $viewableFields)) {
                        $elementNew[$key] = $value;
                    }
                }
                $lineItemList[] = $elementNew;
            }
        }
        return $lineItemList;
    }



    public function getFirstServiceOrderOfSR($id) {
        global $adb;
        $query = "SELECT serviceordersid FROM `vtiger_serviceorders`"
            . " inner join vtiger_crmentity on vtiger_crmentity.crmid = vtiger_serviceorders.serviceordersid "
            . " where ticket_id = ? and deleted = ?";
        $result = $adb->pquery($query, array($id, 0));
        $num_rows = $adb->num_rows($result);
        $dataRow = $adb->fetchByAssoc($result, 0);
        if ($num_rows > 0) {
            return $dataRow['serviceordersid'];
        } else {
            return '';
        }
    }
}
