<?php
vimport('~~/vtlib/Vtiger/Net/Client.php');
include_once 'include/Webservices/DescribeObject.php';
class Users_UserSetupAnother_View extends Vtiger_View_Controller {

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
        $userModel = Users_Record_Model::getCurrentUserModel();
        $viewer->assign('PRERESETRECORDID', $userModel->id);
        $viewer->view('UserSetupAnother.tpl', 'Users');
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
            'modules.Users.resources.UserSetupAnother',
            'modules.Users.resources.validationEngineEn.js',
            'modules.Users.resources.validationEngine.js',
            'modules.Vtiger.resources.Popup',
        );
        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
        $headerScriptInstances = array_merge($jsScriptInstances, $headerScriptInstances);
        return $headerScriptInstances;
    }
}
