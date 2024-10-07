<?php
class Campaigns_EmailDashboard_View extends Vtiger_IndexAjax_View
{

    function __construct()
    {
        parent::__construct();
        $this->exposeMethod('emailinfo');
    }

    function process(Vtiger_Request $request)
    {
        $mode = $request->get('mode');
        if (!empty($mode)) {
            $this->invokeExposedMethod($mode, $request);
            return;
        }
    }

    function emailinfo(Vtiger_Request $request)
    {
        $viewer = $this->getViewer($request);

        $moduleName = $request->getModule();
        $conid = $request->get('record');
        $empRecordModel = Vtiger_Record_Model::getInstanceById($conid, $moduleName);
        global $adb, $log;
        global $upload_badext;
        $adb = PearDatabase::getInstance();
        $campaignidQuery = "SELECT * FROM `campaigns_leads_sendmail` where campaignid = ?";

        $result = $adb->pquery($campaignidQuery, array($conid));
        $no_of_campaign = $adb->num_rows($result);

        $emailidQuery = $adb->pquery("SELECT * FROM `vtiger_email_track` where crmid = ?", array($conid));
        $row = $adb->num_rows($emailidQuery);
        $getcount = array();

        // Initialize total access and click counts
        $total_access_count = 0;
        $total_click_count = 0;

        for ($i = 0; $i < $row; $i++) {
            $access_count = $adb->query_result($emailidQuery, $i, 'access_count');
            $click_count = $adb->query_result($emailidQuery, $i, 'click_count');
            $mail_id = $adb->query_result($emailidQuery, $i, 'mailid');

            // Accumulate the counts correctly
            $total_access_count += $access_count;
            $total_click_count += $click_count;

            // Store individual mail count data
            $getcount[$mail_id] = array('mailid' => $mail_id, 'access_count' => $access_count, 'click_count' => $click_count);
        }

        // Assign variables to the viewer
        $viewer->assign('NO_OF_CAMPAIGN', $no_of_campaign);
        $viewer->assign('ACCESS', $total_access_count);
        $viewer->assign('CLICK', $total_click_count);

        $viewer->view('EmailDashboard.tpl', $moduleName);
    }
}
