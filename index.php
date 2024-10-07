<?php
include_once 'config.php';
include_once 'include/Webservices/Relation.php';
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
include_once 'vtlib/Vtiger/Module.php';
include_once 'includes/main/WebUI.php';

$webUI = new Vtiger_WebUI();
$webUI->process(new Vtiger_Request($_REQUEST, $_REQUEST));
