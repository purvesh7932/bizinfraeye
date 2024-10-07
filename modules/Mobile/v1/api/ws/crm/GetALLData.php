<?php
require_once dirname(__FILE__) . '/../../wsapi.php';
class Mobile_WS_GetALLData extends Mobile_WS_Controller {

    function process(Mobile_API_Request $request) {
        global $current_user;
        $response = new Mobile_API_Response();
        $module = $request->get('module');
        if (empty($module)) {
            $response->setError(1501, "Module Is Not Specified.");
            return $response;
        }
        $fileName =  $current_user->id . '_' . $module;
        $initialData = [];
        $initialData['moreRecords'] = true;
        $initialData['records'] = "Initial";
        unlink(dirname(__FILE__) . "/../apicache/$fileName.json");
        $round = 1;
        $handle = @fopen(dirname(__FILE__) . "/../apicache/$fileName.json", 'r+');
        if ($handle == null) {
            $handle = fopen(dirname(__FILE__) . "/../apicache/$fileName.json", 'w+');
        }
        while (!empty($initialData['records'])) {
            $request->set('page', $round);
            if ($module == 'Equipment') {
                $request->set('search_params', json_encode([["equip_category", "e", "S"]]));
            }
            $initialData = Mobile_WS_API::process(
                $request,
                $current_user,
                'Mobile_WS_ListModuleRecords',
                'ws/ListModuleRecords.php'
            );
            if ($initialData['records'] == null) {
                continue;
            }
            if ($handle) {
                fseek($handle, 0, SEEK_END);
                if (ftell($handle) > 0) {
                    fseek($handle, -1, SEEK_END);
                    fwrite($handle, ',', 1);
                    fwrite($handle, ltrim(json_encode($initialData['records']), "["));
                } else {
                    fwrite($handle, json_encode($initialData['records']));
                }
            }
            $round = $round + 1;
        }
        fclose($handle);
        $filename = dirname(__FILE__) . "/../apicache/$fileName.json";
        if (file_exists($filename)) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            header('Content-Type: ' . finfo_file($finfo, $filename));
            finfo_close($finfo);
            header('Content-Disposition: attachment; filename=' . basename($filename));
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filename));
            ob_clean();
            flush();
            readfile($filename);
            exit;
        }
    }
}
