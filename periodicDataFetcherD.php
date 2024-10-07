<?php
include_once 'vtlib/Vtiger/Cron.php';
require_once 'config.inc.php';
require_once('modules/Emails/mail.php');

if (file_exists('config_override.php')) {
	include_once 'config_override.php';
}

// Extended inclusions
require_once 'includes/Loader.php';
vimport('includes.runtime.EntryPoint');

$site_URLArray = explode('/', $site_URL);

$version = explode('.', phpversion());

$php = ($version[0] * 10000 + $version[1] * 100 + $version[2]);
if ($php <  50300) {
	$hostName = php_uname('n');
} else {
	$hostName = gethostname();
}

$mailbody = "Instance dir : $root_directory <br/> Site Url : $site_URL <br/> Host Name : $hostName<br/>";
$mailSubject = "[Alert] ";

function vtigercron_detect_run_in_cli() {
	return (!isset($_SERVER['SERVER_SOFTWARE']) && (php_sapi_name() == 'cli' ||  is_numeric($_SERVER['argc']) && $_SERVER['argc'] > 0));
}

// if (vtigercron_detect_run_in_cli() || (isset($_SESSION["authenticated_user_id"]) &&	isset($_SESSION["app_unique_key"]) && $_SESSION["app_unique_key"] == $application_unique_key)) {
	//set global current user permissions
	global $current_user;
	$current_user = Users::getActiveAdminUser();
	include_once('modules/Equipment/CalculateContractAndWarrantyForAll.php');
	CalculateContractAndWarrantyForAll([]);
// } else {
// 	echo ("Access denied!");
// }
