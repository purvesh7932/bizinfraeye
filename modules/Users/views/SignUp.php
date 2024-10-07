<?php
vimport('~~/vtlib/Vtiger/Net/Client.php');
include_once 'include/Webservices/DescribeObject.php';
class Users_SignUp_View extends Vtiger_View_Controller {

    function loginRequired() {
        return false;
    }

    function checkPermission(Vtiger_Request $request) {
        return true;
    }
    public function getHeaderCss(Vtiger_Request $request) {
        $headerCssInstances = parent::getHeaderCss($request);

        $cssFileNames = array(
            "~layouts/" . Vtiger_Viewer::getDefaultLayoutName() . "/modules/Users/build/css/intlTelInput.css",
            "~layouts/" . Vtiger_Viewer::getDefaultLayoutName() . "/modules/Users/build/css/demo.css",
        );
        $cssInstances = $this->checkAndConvertCssStyles($cssFileNames);
        $headerCssInstances = array_merge($headerCssInstances, $cssInstances);

        return $headerCssInstances;
    }

    function preProcess(Vtiger_Request $request, $display = true) {
        $viewer = $this->getViewer($request);
        $viewer->assign('PAGETITLE', $this->getPageTitle($request));
        $viewer->assign('SCRIPTS', $this->getHeaderScripts($request));
        $viewer->assign('STYLES', $this->getHeaderCss($request));
        $viewer->assign('MODULE', $request->getModule());
        $viewer->assign('VIEW', $request->get('view'));
        $viewer->assign('LANGUAGE_STRINGS', array());
        if ($display) {
            $this->preProcessDisplay($request);
        }
    }

    function process(Vtiger_Request $request) {
        $finalJsonData = array();
        $viewer = $this->getViewer($request);
        $viewer->assign('DATA_COUNT', count($jsonData));
        $viewer->assign('JSON_DATA', $finalJsonData);

        $mailStatus = $request->get('mailStatus');
        $error = $request->get('error');
        $message = '';
        if ($error) {
            switch ($error) {
                case 'login':
                    $message = 'Invalid credentials';
                    break;
                case 'fpError':
                    $message = 'Invalid Username or Email address';
                    break;
                case 'statusError':
                    $message = 'Outgoing mail server was not configured';
                    break;
            }
        } else if ($mailStatus) {
            $message = 'Mail has been sent to your inbox, please check your e-mail';
        }

        $viewer->assign('ERROR', $error);
        $viewer->assign('MESSAGE', $message);
        $viewer->assign('MAIL_STATUS', $mailStatus);
        $allFields = $this->getAllFields();

        $viewer->assign('USERBLOCKS', $allFields['blocks']);
        $viewer->assign('OFFICETOROLEDEPENDENCY', Vtiger_DependencyPicklist::getPickListDependency('ServiceEngineer', 'office', 'sub_service_manager_role'));
        $viewer->view('SignUp.tpl', 'Users');
    }

    function postProcess(Vtiger_Request $request) {
        $moduleName = $request->getModule();
        $viewer = $this->getViewer($request);
        $viewer->view('Footer.tpl', $moduleName);
    }

    function getPageTitle(Vtiger_Request $request) {
        $companyDetails = Vtiger_CompanyDetails_Model::getInstanceById();
        return $companyDetails->get('organizationname');
    }

    function getHeaderScripts(Vtiger_Request $request) {
        $headerScriptInstances = parent::getHeaderScripts($request);

        $jsFileNames = array(
            '~libraries/jquery/boxslider/jquery.bxslider.min.js',
            'modules.Vtiger.resources.List',
            'modules.Users.resources.SignUp',
            'modules.Users.resources.validationEngineEn.js',
            'modules.Users.resources.validationEngine.js',
            'modules.Vtiger.resources.Popup',
        );
        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
        $headerScriptInstances = array_merge($jsScriptInstances, $headerScriptInstances);
        return $headerScriptInstances;
    }

    function getAllFields() {
        $current_user = CRMEntity::getInstance('Users');
        $current_user->id = $current_user->getActiveAdminId();
        $current_user->retrieve_entity_info($current_user->id, 'Users');
        if ($current_user) {
            $describeInfo = vtws_describe('ServiceEngineer', $current_user);
            $module = 'ServiceEngineer';
            $moduleFieldGroups = $this->gatherModuleFieldGroupInfo($module);
            $activeFields = $this->getActiveFields($module, true);
            $activeFieldKeys = array_keys($activeFields);
            $dependencyFields = array('district_office', 'service_centre', 'activity_centre', 'production_division', 'regional_office', 'sub_service_manager_role');
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
                                $value['dependentOnOption'] = 'Other';
                                $dependentField = str_replace("other_", "", $value['name']);
                                $value['dependentOnField'] = $dependentField;
                            } else {
                                $value['initialDisplay'] = 'yes';
                            }
                            if ($value['name'] == 'service_engineer_name') {
                                $value['type']['name'] = 'stringonlychars';
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
            return $modifiedResult;
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
                    'uitype' => $resultrow['uitype']
                );
        }
        self::$gatherModuleFieldGroupInfoCache[$module] = $fieldgroups;
        return $fieldgroups;
    }
}
