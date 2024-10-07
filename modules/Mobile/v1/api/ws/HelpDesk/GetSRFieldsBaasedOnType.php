<?php
include_once 'include/Webservices/DescribeObject.php';
class Mobile_WS_GetSRFieldsBaasedOnType extends Mobile_WS_Controller {
    function process(Mobile_API_Request $request) {
        $response = new Mobile_API_Response();

        $ticketType = $request->get('ticket_type');
        if (empty($ticketType)) {
            $response->setError(100, "Ticket Type Is Missing");
            return $response;
        }

        $current_user = $this->getActiveUser();
        $module = 'HelpDesk';
        $describeInfo = vtws_describe($module, $current_user);
        $dependetFields = $this->getDependentFields($ticketType);
        $fieldList = [];
        $moduleFieldGroups = $this->gatherModuleFieldGroupInfo($module);
        foreach ($moduleFieldGroups as $blocklabel => $fieldgroups) {
            foreach ($fieldgroups as $fieldname => $fieldinfo) {
                foreach ($describeInfo['fields'] as $key => $value) {
                    if ($value['name'] ==  $fieldname){
                        if (in_array($value['name'], $dependetFields)) {
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
                            $describeInfo['fields'][$key] = $value;
                            array_push($fieldList , $describeInfo['fields'][$key]);
                        }
                        break;
                    }
                    
                }
            }
        }
        $response->setApiSucessMessage('Successfully Fetched Data');
        $response->setResult($fieldList);
        return $response;
    }

    function getDependentFields($ticketType) {
        $dependentFields = Vtiger_DependencyPicklist::getFieldsFitDependency('HelpDesk', 'ticket_type', 'ticketpriorities');
        foreach ($dependentFields['valuemapping'] as $value) {
            if ($value['sourcevalue'] == $ticketType) {
                return $value['targetvalues'];
            }
        }
    }

    static $gatherModuleFieldGroupInfoCache = array();

    function gatherModuleFieldGroupInfo($module) {
        global $adb;
        if ($module == 'Events') $module = 'Calendar';
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
                return 252;
            }
        }
        return $uitype;
    }
}
