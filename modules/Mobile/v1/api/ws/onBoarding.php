<?php

/*include('config.inc.php');

$onboardingScreens = [
    [
        'image' => $site_URL.'resources/onboarding/onboarding_1.png',
        'text' => 'Onboarding Screen 1 Text',
    ],
    [
        'image' => $site_URL.'resources/onboarding/onboarding_2.png',
        'text' => 'Onboarding Screen 2 Text',
    ],
    [
        'image' => $site_URL.'resources/onboarding/onboarding_3.png', 
        'text' => 'Onboarding Screen 3 Text',
    ],
];

// Number of pages
$numberOfPages = count($onboardingScreens);

// Combine data with the number of pages
$response = [
    'pages' => $numberOfPages,
    'screens' => $onboardingScreens,
];

// Set the content type to JSON
header('Content-Type: application/json');

// Output the response data as JSON
echo json_encode($response);*/


include('config.inc.php');

global $adb, $site_URL;
$query = $adb->pquery("SELECT * FROM vtiger_onboarding INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_onboarding.onboardingid WHERE vtiger_crmentity.deleted = 0", array());
$rows = $adb->num_rows($query);

$onboardingScreens = array();

for ($i = 0; $i < $rows; $i++) {
    $onboardingid = $adb->query_result($query, $i, 'onboardingid');
    $onboarding_text = $adb->query_result($query, $i, 'onboarding_text');
    $sql = "SELECT vtiger_attachments.*, vtiger_crmentity.setype FROM vtiger_attachments
                INNER JOIN vtiger_seattachmentsrel ON vtiger_seattachmentsrel.attachmentsid = vtiger_attachments.attachmentsid
                INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_attachments.attachmentsid
                WHERE vtiger_seattachmentsrel.crmid = ?";

    $result = $adb->pquery($sql, array($onboardingid));

    $imageId = $adb->query_result($result, 0, 'attachmentsid');
    $imagePath = $adb->query_result($result, 0, 'path');
    $imageName = $adb->query_result($result, 0, 'name');
    $type = explode('/', $adb->query_result($result, 0, 'type'));
    $url = \Vtiger_Functions::getFilePublicURL($imageId, $imageName);
    //decode_html - added to handle UTF-8 characters in file names
    $imageOriginalName = urlencode(decode_html($imageName));
    if ($url) {
        $url = $site_URL . $url;
    }
    $onboardingScreens[] = array('image' => $url, 'text' => $onboarding_text);
}
// Number of pages
$numberOfPages = $rows;

// Combine data with the number of pages
$response = [
    'pages' => $numberOfPages,
    'screens' => $onboardingScreens,
];

// Set the content type to JSON
header('Content-Type: application/json');

// Output the response data as JSON
echo json_encode($response);

