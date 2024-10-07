<?php
$uriSegments = explode("/", parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$_REQUEST['api'] = $uriSegments[5];

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
header('Access-Control-Allow-Origin: *');
// TODO Remove below if condition and move login specific funtion to customer Portal folder
// if ($_REQUEST['api'] == 'Login' || $_REQUEST['api'] == 'FetchRecords') {
chdir(dirname(__FILE__) . '/../../../');
require_once 'includes/main/WebUI.php';
// }
include_once dirname(__FILE__) . '/classes/Portal/Loader.php';
if (!file_exists(PORTAL_APP_DIR . '/config.php')) {
    header("Content-type: text/plain");
    echo "Please make copy of config.sample.php as config.php and update.";
    exit;
}

/* Class check force inclusion and activate required runtime config */
if (!class_exists('Portal_Config')) {
    header("Content-type: text/plain");
    echo "Portal class loader not working as expected.";
    exit;
}
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
$portalMainController = new Portal_Main_Controller();
$portalMainController->dispatch(Portal_Request::parseFormOrJSONRequest());
