<?php
class HelpDesk_AutosetAssignUser_Action extends Vtiger_Action_Controller {

    public function process(Vtiger_Request $request) {
        include_once('include/utils/GeneralUtils.php');
        $functionalLoationId = $request->get('func_loc_id');
        $adb = PearDatabase::getInstance();
        $sql = "SELECT `vtiger_crmentityrel`.crmid,vtiger_serviceengineer.badge_no FROM `vtiger_crmentityrel`
                inner join vtiger_crmentity 
                on vtiger_crmentity.crmid=vtiger_crmentityrel.relcrmid 
                inner join vtiger_serviceengineer 
                on vtiger_serviceengineer.serviceengineerid=vtiger_crmentityrel.crmid
                where `vtiger_crmentityrel`.relcrmid = ? 
                and module = 'ServiceEngineer' and vtiger_serviceengineer.approval_status = 'Accepted'
                and vtiger_serviceengineer.auto_asgn_ticket = 'Yes'
                and vtiger_crmentity.deleted = 0";

        $result = $adb->pquery($sql, array($functionalLoationId));
        $records = array();
        $currentUserModel = Users_Record_Model::getCurrentUserModel();
        $allUsers = $currentUserModel->getAccessibleUsers();
        $allUserIds = array_keys($allUsers);

        $userData = [];
        $empData = [];
        while ($row = $adb->fetch_array($result)) {
            $data = getUserIdDetailsBasedOnEmployeeModuleG($row['badge_no']);
            if (in_array($data['id'], $allUserIds)) {
                array_push($userData, $data['id']);
                array_push($empData, $row['crmid']);
            }
        }
        if (count($empData) > 1) {
            $records = $this->modelCheck($request, $empData);
            if (!empty($records)) {
                $response = new Vtiger_Response();
                $response->setResult($records);
                $response->emit();
                exit();
            } else {
                $leastLoadedUser = $this->getLeastLoadedUser($userData);
                $response = new Vtiger_Response();
                $response->setResult(array($leastLoadedUser));
                $response->emit();
                exit();
            }
        } else if (count($empData) == 1) {
            //$row = getUserIdDetailsBasedOnEmployeeModuleG($dataRow['badge_no']);
            array_push($records, array('id' => $userData[0]));
            $response = new Vtiger_Response();
            $response->setResult($records);
            $response->emit();
            exit();
        }

        $records = array();
        $response = new Vtiger_Response();
        $response->setResult($records);
        $response->emit();
    }

    public function modelCheck(Vtiger_Request $request, $empData) {
        global $adb;
        $model = $request->get('model');
        $modelCheckSql = "SELECT `vtiger_crmentityrel`.crmid , vtiger_serviceengineer.badge_no
        FROM `vtiger_crmentityrel` 
        inner join vtiger_crmentity 
        on vtiger_crmentity.crmid=vtiger_crmentityrel.relcrmid 
        inner join vtiger_serviceengineer 
        on vtiger_serviceengineer.serviceengineerid=vtiger_crmentityrel.crmid
        left join vtiger_modelandaggregates 
        on vtiger_modelandaggregates.modelandaggregatesid = vtiger_crmentityrel.relcrmid 
        where `vtiger_crmentityrel`.crmid in (" . generateQuestionMarks($empData) . ") 
        and vtiger_crmentityrel.module = 'ServiceEngineer' 
        and `vtiger_modelandaggregates`.eq_sr_equip_model = ? 
        and vtiger_crmentity.deleted = 0";

        $params = $empData;
        array_push($params, $model);
        $result = $adb->pquery($modelCheckSql, $params);
        $num_rows = $adb->num_rows($result);

        $records = array();
        if ($num_rows > 1) {
            $records = $this->aggregateCheck($request, $empData);
            if (!empty($records)) {
                $response = new Vtiger_Response();
                $response->setResult($records);
                $response->emit();
                exit();
            } else {
                $userData = [];
                while ($row = $adb->fetch_array($result)) {
                    $data = getUserIdDetailsBasedOnEmployeeModuleG($row['badge_no']);
                    array_push($userData, $data['id']);
                }
                $leastLoadedUser = $this->getLeastLoadedUser($userData);
                $response = new Vtiger_Response();
                $response->setResult(array($leastLoadedUser));
                $response->emit();
                exit();
            }
        } else if ($num_rows == 1) {
            $dataRow = $adb->fetchByAssoc($result, 0);
            $row = getUserIdDetailsBasedOnEmployeeModuleG($dataRow['badge_no']);
            array_push($records, $row);
            return $records;
        } else {
            return false;
        }
    }

    public function aggregateCheck(Vtiger_Request $request, $empData) {
        global $adb;
        $model = $request->get('model');
        $aggregate = $request->get('aggregate');
        $modelCheckSql = "SELECT `vtiger_crmentityrel`.crmid , vtiger_serviceengineer.badge_no
        FROM `vtiger_crmentityrel` 
        inner join vtiger_crmentity 
        on vtiger_crmentity.crmid=vtiger_crmentityrel.relcrmid 
        inner join vtiger_serviceengineer 
        on vtiger_serviceengineer.serviceengineerid=vtiger_crmentityrel.crmid
        left join vtiger_modelandaggregates 
        on vtiger_modelandaggregates.modelandaggregatesid = vtiger_crmentityrel.relcrmid 
        where `vtiger_crmentityrel`.crmid in (" . generateQuestionMarks($empData) . ") 
        and vtiger_crmentityrel.module = 'ServiceEngineer' 
        and `vtiger_modelandaggregates`.eq_sr_equip_model = ? 
        and vtiger_modelandaggregates.masn_aggrregate LIKE ?
        and vtiger_crmentity.deleted = 0";

        $params = $empData;
        array_push($params, $model,  "%$aggregate%");
        $result = $adb->pquery($modelCheckSql, $params);
        $num_rows = $adb->num_rows($result);
        $records = array();
        if ($num_rows > 1) {
            $userData = [];
            while ($row = $adb->fetch_array($result)) {
                $data = getUserIdDetailsBasedOnEmployeeModuleG($row['badge_no']);
                array_push($userData, $data['id']);
            }
            $leastLoadedUser = $this->getLeastLoadedUser($userData);
            $response = new Vtiger_Response();
            $response->setResult(array($leastLoadedUser));
            $response->emit();
            exit();
        } else if ($num_rows == 1) {
            $dataRow = $adb->fetchByAssoc($result, 0);
            $row = getUserIdDetailsBasedOnEmployeeModuleG($dataRow['badge_no']);
            array_push($records, $row);
            return $records;
        } else {
            return false;
        }
    }

    public function getLeastLoadedUser($userids) {
        global $adb;
        $sql = "SELECT COUNT(smownerid) as ownercount,smownerid as id
        FROM vtiger_troubletickets INNER JOIN vtiger_crmentity 
        on vtiger_crmentity.crmid = vtiger_troubletickets.ticketid 
        where vtiger_crmentity.smownerid in (" . generateQuestionMarks($userids) . ") 
        and vtiger_crmentity.deleted = 0 
        and vtiger_troubletickets.status != 'Closed' 
        GROUP BY smownerid 
        ORDER BY ownercount ASC";

        $result = $adb->pquery($sql, $userids);
        $dataRow = $adb->fetchByAssoc($result, 0);
        $num_rows = $adb->num_rows($result);
        if (empty($dataRow)) {
            return array('id' => $userids[0]);
        } else if ($num_rows != count($userids)) {
            $allExt = [];
            for ($j = 0; $j < $num_rows; $j++) {
                $verId = $adb->query_result($result, $j, 'id');
                array_push($allExt, $verId);
            }
            $nonExt = array_diff($userids, $allExt);
            foreach ($nonExt as $nonExtId) {
                return array('id' => $nonExtId);
            }
        } else {
            return $dataRow;
        }
        return false;
    }
}
