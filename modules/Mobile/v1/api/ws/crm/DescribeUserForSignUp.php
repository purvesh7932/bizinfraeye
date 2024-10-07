<?php
include_once 'include/Webservices/DescribeObject.php';
class Mobile_WS_DescribeUserForSignUp extends Mobile_WS_Controller {

    function requireLogin() {
        return false;
    }

    function process(Mobile_API_Request $request) {
        // $data = Vtiger_DependencyPicklist::getPickListDependency('ServiceEngineer', 'cust_role', 'designaion');
        // print_r($data);
        // die();
        $current_user = CRMEntity::getInstance('Users');
        $current_user->id = $current_user->getActiveAdminId();
        $current_user->retrieve_entity_info($current_user->id, 'Users');
        $response = new Mobile_API_Response();

        if ($current_user) {
            $describeInfo = vtws_describe('ServiceEngineer', $current_user);
            $module = 'ServiceEngineer';
            $moduleFieldGroups = $this->gatherModuleFieldGroupInfo($module);
            $activeFields = $this->getActiveFields($module, true);
            $activeFieldKeys = array_keys($activeFields);
            $dependencyFields = array('service_centre', 'district_office', 'regional_office','production_division','activity_centre','sub_service_manager_role');
            $dependencyPickListFields = array('cust_role','office');
            foreach ($moduleFieldGroups as $blocklabel => $fieldgroups) {
                $fields = array();
                foreach ($fieldgroups as $fieldname => $fieldinfo) {
                    if (!in_array($fieldname, $activeFieldKeys)) {
                        continue;
                    }
                    foreach ($describeInfo['fields'] as $key => $value) {
                        if ($value['name'] ==  $fieldname && ($value['name'] != 'assigned_user_id')) {
                            if (in_array($value['name'], $dependencyFields)) {
                                $value['dependentField'] = true;
                                $value['initialDisplay'] = 'no';
                                $moreData = $this->getMoreFieldData($value['name']);
                                foreach($moreData as $moreDatakey => $moreDataValue){
                                    $value[$moreDatakey] = $moreDataValue;
                                }
                            } else {
                                $value['initialDisplay'] = 'yes';
                            }
                            if (in_array($value['name'], $dependencyPickListFields)) {
                                $sourceField = $value['name'];
                                $targetField = $this->getDependencyConfiguredTargetField($sourceField);
                                $value['dependency'] = Vtiger_DependencyPicklist::getPickListDependency('ServiceEngineer', $sourceField, $targetField);
                            }
                            $value['default'] = decode_html($value['default']);
                            if ($value['type']['name'] === 'picklist' || $value['type']['name'] === 'metricpicklist') {
                                $pickList = $value['type']['picklistValues'];
                                foreach ($pickList as $pickListKey => $pickListValue) {
                                    $pickListValue['label'] = decode_html(vtranslate($pickListValue['value'], $module));
                                    $pickListValue['value'] = decode_html($pickListValue['value']);
                                    $pickList[$pickListKey] = $pickListValue;
                                }
                                $value['type']['picklistValues'] = $pickList;
                            } else if ($value['type']['name'] === 'time') {
                                $value['default'] = Vtiger_Time_UIType::getTimeValueWithSeconds($value['default']);
                            }
                            $value['label'] = decode_html($value['label']);
                            if ($activeFields[$value['name']]) {
                                $value['editable'] = true;
                            } else {
                                $value['editable'] = false;
                            }
                            $fields[] = $value;
                            break;
                        }
                    }
                }
                if (count($fields) > 0) {
                    $blocks[] = array('label' => $blocklabel, 'fields' => $fields);
                }
            }
            $modifiedResult = array('blocks' => $blocks);
            // $response->addToResult('describe', $modifiedResult);
            $response->setResult($modifiedResult);
            return $response;
        }
    }

    function getMoreFieldData($fieldName){
        $moreData = [];
        switch ($fieldName) {
            case "service_centre":
                $moreData['dependValue'] = 'Service Centre';
                $moreData['dependOn'] = 'office';
                break;
            case "district_office":
                $moreData['dependValue'] = 'District Office';
                $moreData['dependOn'] = 'office';
                break;
            case "regional_office":
                $moreData['dependValue'] = 'Regional Office';
                $moreData['dependOn'] = 'office';
                break;
            case "production_division":
                $moreData['dependValue'] = 'Production Division';
                $moreData['dependOn'] = 'office';
                break;
            case "activity_centre":
                $moreData['dependValue'] = 'Activity Centre';
                $moreData['dependOn'] = 'office';
                break;
            case "sub_service_manager_role":
                $moreData['dependValue'] = 'Service Manager';
                $moreData['dependOn'] = 'cust_role';
                break;
            default:
        }
        return $moreData;
    }

    function getDependencyConfiguredTargetField($fieldName){
        if($fieldName == 'office'){
            return 'cust_role';
        } elseif($fieldName == 'cust_role'){
            return 'designaion';
        }
    }



    function getActiveFields($module, $withPermissions = false) {
        $activeFields = Vtiger_Cache::get('CustomerPortal', 'activeFields'); // need to flush cache when fields updated at CRM settings

        if (empty($activeFields)) {
            global $adb;
            $sql = "SELECT name, fieldinfo FROM vtiger_customerportal_fields INNER JOIN vtiger_tab ON vtiger_customerportal_fields.tabid=vtiger_tab.tabid";
            $sqlResult = $adb->pquery($sql, array());
            $num_rows = $adb->num_rows($sqlResult);

            for ($i = 0; $i < $num_rows; $i++) {
                $retrievedModule = $adb->query_result($sqlResult, $i, 'name');
                $fieldInfo = $adb->query_result($sqlResult, $i, 'fieldinfo');
                $activeFields[$retrievedModule] = $fieldInfo;
            }
            Vtiger_Cache::set('CustomerPortal', 'activeFields', $activeFields);
        }

        $fieldsJSON = $activeFields[$module];
        $data = Zend_Json::decode(decode_html($fieldsJSON));
        $fields = array();

        if (!empty($data)) {
            foreach ($data as $key => $value) {
                if (self::isViewable($key, $module)) {
                    if ($withPermissions) {
                        $fields[$key] = $value;
                    } else {
                        $fields[] = $key;
                    }
                }
            }
        }
        return $fields;
    }

    function isViewable($fieldName, $module) {
        global $db;
        $db = PearDatabase::getInstance();
        $tabidSql = "SELECT tabid from vtiger_tab WHERE name = ?";
        $tabidResult = $db->pquery($tabidSql, array($module));
        if ($db->num_rows($tabidResult)) {
            $tabId = $db->query_result($tabidResult, 0, 'tabid');
        }
        $presenceSql = "SELECT presence,displaytype FROM vtiger_field WHERE fieldname=? AND tabid = ?";
        $presenceResult = $db->pquery($presenceSql, array($fieldName, $tabId));
        $num_rows = $db->num_rows($presenceResult);
        if ($num_rows) {
            $fieldPresence = $db->query_result($presenceResult, 0, 'presence');
            $displayType = $db->query_result($presenceResult, 0, 'displaytype');
            if ($fieldPresence == 0 || $fieldPresence == 2 && $displayType !== 4) {
                return true;
            } else {
                return false;
            }
        }
    }

    static $gatherModuleFieldGroupInfoCache = array();

    function gatherModuleFieldGroupInfo($module) {
        global $adb;

        if ($module == 'Events') $module = 'Calendar';

        // Cache hit?
        if (isset(self::$gatherModuleFieldGroupInfoCache[$module])) {
            return self::$gatherModuleFieldGroupInfoCache[$module];
        }

        $result = $adb->pquery(
            "SELECT fieldname, fieldlabel, blocklabel, uitype FROM vtiger_field INNER JOIN
			vtiger_blocks ON vtiger_blocks.tabid=vtiger_field.tabid AND vtiger_blocks.blockid=vtiger_field.block 
			WHERE vtiger_field.tabid=? AND vtiger_field.presence != 1 ORDER BY vtiger_blocks.sequence, vtiger_field.sequence",
            array(getTabid($module))
        );

        $fieldgroups = array();
        while ($resultrow = $adb->fetch_array($result)) {
            $blocklabel = getTranslatedString($resultrow['blocklabel'], $module);
            if (!isset($fieldgroups[$blocklabel])) {
                $fieldgroups[$blocklabel] = array();
            }
            $fieldgroups[$blocklabel][$resultrow['fieldname']] =
                array(
                    'label' => getTranslatedString($resultrow['fieldlabel'], $module),
                    'uitype' => self::fixUIType($module, $resultrow['fieldname'], $resultrow['uitype'])
                );
        }

        // Cache information
        self::$gatherModuleFieldGroupInfoCache[$module] = $fieldgroups;

        return $fieldgroups;
    }

    function fixUIType($module, $fieldname, $uitype) {
        if ($module == 'Contacts' || $module == 'Leads') {
            if ($fieldname == 'salutationtype') {
                return 16;
            }
        } else if ($module == 'Calendar' || $module == 'Events') {
            if ($fieldname == 'time_start' || $fieldname == 'time_end') {
                // Special type for mandatory time type (not defined in product)
                return 252;
            }
        }
        return $uitype;
    }
}
