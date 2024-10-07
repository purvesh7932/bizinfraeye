<?php

class ReturnSaleOrders_ListView_Model extends Inventory_ListView_Model {

    public function isImportEnabled() {
        return false;
    }

    public function getListViewEntries($pagingModel) {
        $db = PearDatabase::getInstance();

        $moduleName = $this->getModule()->get('name');
        $moduleFocus = CRMEntity::getInstance($moduleName);
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);

        $queryGenerator = $this->get('query_generator');
        $listViewContoller = $this->get('listview_controller');

        $searchParams = $this->get('search_params');
        if (empty($searchParams)) {
            $searchParams = array();
        }
        $glue = "";
        if (count($queryGenerator->getWhereFields()) > 0 && (count($searchParams)) > 0) {
            $glue = QueryGenerator::$AND;
        }
        $queryGenerator->parseAdvFilterList($searchParams, $glue);

        $searchKey = $this->get('search_key');
        $searchValue = $this->get('search_value');
        $operator = $this->get('operator');
        if (!empty($searchKey)) {
            $queryGenerator->addUserSearchConditions(array('search_field' => $searchKey, 'search_text' => $searchValue, 'operator' => $operator));
        }

        $orderBy = $this->getForSql('orderby');
        $sortOrder = $this->getForSql('sortorder');

        if (!empty($orderBy)) {
            $queryGenerator = $this->get('query_generator');
            $fieldModels = $queryGenerator->getModuleFields();
            $orderByFieldModel = $fieldModels[$orderBy];
            if ($orderByFieldModel && ($orderByFieldModel->getFieldDataType() == Vtiger_Field_Model::REFERENCE_TYPE ||
                $orderByFieldModel->getFieldDataType() == Vtiger_Field_Model::OWNER_TYPE)) {
                $queryGenerator->addWhereField($orderBy);
            }
        }
        $listQuery = $this->getQuery();

        $sourceModule = $this->get('src_module');
        if (!empty($sourceModule)) {
            if (method_exists($moduleModel, 'getQueryByModuleField')) {
                $overrideQuery = $moduleModel->getQueryByModuleField($sourceModule, $this->get('src_field'), $this->get('src_record'), $listQuery, $this->get('relationId'));
                if (!empty($overrideQuery)) {
                    $listQuery = $overrideQuery;
                }
            }
        }

        $startIndex = $pagingModel->getStartIndex();
        $pageLimit = $pagingModel->getPageLimit();
        $paramArray = array();

        if (!empty($orderBy) && $orderByFieldModel) {
            if ($orderBy == 'roleid' && $moduleName == 'Users') {
                $listQuery .= ' ORDER BY vtiger_role.rolename ' . ' ' . $sortOrder;
            } else {
                $listQuery .= ' ORDER BY ' . $queryGenerator->getOrderByColumn($orderBy) . ' ' . $sortOrder;
            }

            if ($orderBy == 'first_name' && $moduleName == 'Users') {
                $listQuery .= ' , last_name ' . ' ' . $sortOrder . ' ,  email1 ' . ' ' . $sortOrder;
            }
        } else if (empty($orderBy) && empty($sortOrder) && $moduleName != "Users") {
            // List view will be displayed on recently created/modified records
            require_once('include/utils/GeneralUtils.php');
            global $current_user;
            $data = getUserDetailsBasedOnEmployeeModuleG($current_user->user_name);
            if (empty($data)) {
            } else {
                if (($data['cust_role'] == 'Service Manager' || $data['cust_role'] == 'Service Engineer') && $data['office'] != 'Service Centre') {
                } else if (($data['office'] == 'Production Division')) {
                    $listQuery = $listQuery . ' and vtiger_inventoryproductrel.div_or_ser_center =  "'.$data['production_division'].'"';
                    // array_push($paramArray, $data['production_division']);
                } else if (($data['office'] == 'Service Centre')) {
                    $listQuery = $listQuery . ' and vtiger_inventoryproductrel.div_or_ser_center =  "'.$data['service_centre'].'"';
                }
            }
            
            $listQuery .= ' ORDER BY vtiger_crmentity.modifiedtime DESC';
        }

        $viewid = ListViewSession::getCurrentView($moduleName);
        if (empty($viewid)) {
            $viewid = $pagingModel->get('viewid');
        }
        $_SESSION['lvs'][$moduleName][$viewid]['start'] = $pagingModel->get('page');

        ListViewSession::setSessionQuery($moduleName, $listQuery, $viewid);

        $listQuery .= " LIMIT ?, ?";
        
        array_push($paramArray, $startIndex);
        array_push($paramArray, ($pageLimit + 1));

        $listResult = $db->pquery($listQuery, $paramArray);

        $listViewRecordModels = array();
        $listViewEntries =  $listViewContoller->getListViewRecords($moduleFocus, $moduleName, $listResult);

        $pagingModel->calculatePageRange($listViewEntries);

        if ($db->num_rows($listResult) > $pageLimit) {
            array_pop($listViewEntries);
            $pagingModel->set('nextPageExists', true);
        } else {
            $pagingModel->set('nextPageExists', false);
        }

        $index = 0;
        foreach ($listViewEntries as $recordId => $record) {
            $rawData = $db->query_result_rowdata($listResult, $index++);
            $record['id'] = $recordId;
            $listViewRecordModels[$recordId] = $moduleModel->getRecordFromArray($record, $rawData);
        }
        return $listViewRecordModels;
    }

    public function getListViewCount() {
        $db = PearDatabase::getInstance();

        $queryGenerator = $this->get('query_generator');


        $searchParams = $this->get('search_params');
        if (empty($searchParams)) {
            $searchParams = array();
        }

        // for Documents folders we should filter with folder id as well
        $folderKey = $this->get('folder_id');
        $folderValue = $this->get('folder_value');
        if (!empty($folderValue)) {
            $queryGenerator->addCondition($folderKey, $folderValue, 'e');
        }

        $glue = "";
        if (count($queryGenerator->getWhereFields()) > 0 && (count($searchParams)) > 0) {
            $glue = QueryGenerator::$AND;
        }
        $queryGenerator->parseAdvFilterList($searchParams, $glue);

        $searchKey = $this->get('search_key');
        $searchValue = $this->get('search_value');
        $operator = $this->get('operator');
        if (!empty($searchKey)) {
            $queryGenerator->addUserSearchConditions(array('search_field' => $searchKey, 'search_text' => $searchValue, 'operator' => $operator));
        }
        $moduleName = $this->getModule()->get('name');
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);

        $listQuery = $this->getQuery();
        $sourceModule = $this->get('src_module');
        if (!empty($sourceModule)) {
            $moduleModel = $this->getModule();
            if (method_exists($moduleModel, 'getQueryByModuleField')) {
                $overrideQuery = $moduleModel->getQueryByModuleField($sourceModule, $this->get('src_field'), $this->get('src_record'), $listQuery);
                if (!empty($overrideQuery)) {
                    $listQuery = $overrideQuery;
                }
            }
        }
        $position = stripos($listQuery, ' from ');
        if ($position) {
            $split = preg_split('/ from /i', $listQuery);
            $splitCount = count($split);
            // If records is related to two records then we'll get duplicates. Then count will be wrong
            $meta = $queryGenerator->getMeta($this->getModule()->getName());
            $columnIndex = $meta->getObectIndexColumn();
            $baseTable = $meta->getEntityBaseTable();
            $listQuery = "SELECT count(distinct($baseTable.$columnIndex)) AS count ";
            for ($i = 1; $i < $splitCount; $i++) {
                $listQuery = $listQuery . ' FROM ' . $split[$i];
            }
        }

        if ($this->getModule()->get('name') == 'Calendar') {
            $listQuery .= ' AND activitytype <> "Emails"';
        }

        // List view will be displayed on recently created/modified records
        require_once('include/utils/GeneralUtils.php');
        $params = [];
        global $current_user;
        $data = getUserDetailsBasedOnEmployeeModuleG($current_user->user_name);
        if (empty($data)) {
        } else {
            if ($data['cust_role'] == 'Service Manager' || $data['cust_role'] == 'Service Engineer') {
            } else if (($data['office'] == 'Production Division')) {
                $listQuery = $listQuery . ' and vtiger_inventoryproductrel.div_or_ser_center = ? ';
                array_push($params, $data['production_division']);
            } else if (($data['office'] == 'Service Centre')) {
                $listQuery = $listQuery . ' and vtiger_inventoryproductrel.div_or_ser_center = ? ';
                array_push($params, $data['service_centre']);
            }
        }
        
        $listQuery .= ' ORDER BY vtiger_crmentity.modifiedtime DESC';
        $listResult = $db->pquery($listQuery, $params);
        return $db->query_result($listResult, 0, 'count');
    }
}