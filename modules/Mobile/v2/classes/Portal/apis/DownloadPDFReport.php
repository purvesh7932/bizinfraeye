<?php

class Portal_DownloadPDFReport_API extends Portal_Default_API {

    function process(Portal_Request $request) {
        $source_module = $request->get("source_module");
        global $current_user;
        if (!$current_user) {
            $current_user = Users::getActiveAdminUser();
        }
        $GeneratePDF = PDFMaker_checkGenerate_Model::getInstance();
        $GeneratePDF->set("source_module", $source_module);

        $generate_type = "inline";
        if ($request->has("generate_type")) {
            $generate_type = $request->get("generate_type");
        }
        $GeneratePDF->set("generate_type", $generate_type);
        header('Content-Type: application/octet-stream');
        $GeneratePDF->generateMobileCustomer($request);
    }

}
