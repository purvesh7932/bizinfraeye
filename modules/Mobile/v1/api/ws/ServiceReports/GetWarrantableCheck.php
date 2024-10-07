<?php
class Mobile_WS_GetWarrantableCheck extends Mobile_WS_Controller {
    function process(Mobile_API_Request $request) {
        $response = new Mobile_API_Response();

        $month          = $request->get('sad_sub_ass_mon1');
        $dateOfFitemnet = $request->get('sad_date_oracs1');
        $warrentyKM     = $request->get('sad_sub_ass_km1');
        $presentKM      = $request->get('eq_present_km');
        $fitMentKM      = $request->get('sad_km_run');
        $warrentyHMR    = $request->get('sad_sub_ass_hmr1');
        $presentHMR     = $request->get('eq_last_hmr');
        $fitMentHMR     = $request->get('sad_hmr');
        $responseObject = $this->oredCondition(
            $month,
            $dateOfFitemnet,
            $warrentyKM,
            $presentKM,
            $fitMentKM,
            $warrentyHMR,
            $presentHMR,
            $fitMentHMR
        );
        $response->setResult($responseObject);
        $response->setApiSucessMessage('Successfully Fetched Data');
        return $response;
    }
    public function checkWarntableOrNot($month, $dateOfFitemnet) {
        if (empty($month)) {
            return 'm';
        }
        if (empty($dateOfFitemnet)) {
            return 'm';
        }
        $dateOfFitemnet = date_create($dateOfFitemnet);
        $dateOfFitemnet  = $dateOfFitemnet->modify("+" . $month . ' month');
        $warrentyEndDate = date_format($dateOfFitemnet, "Y-m-d");

        if ($warrentyEndDate > date("Y-m-d")) {
            return true;
        } else {
            return false;
        }
    }

    public function checkWarntableOrNotKM($warrentyKM, $presentKM, $fitMentKM) {
        if (empty($warrentyKM)) {
            return 'm';
        }
        if (empty($presentKM)) {
            return 'm';
        }
        if (empty($fitMentKM)) {
            return 'm';
        }

        if (intval($warrentyKM) > (intval($presentKM) - intval($fitMentKM))) {
            return true;
        } else {
            return false;
        }
    }

    public function checkWarntableOrNotHMR($warrentyHMR, $presentHMR, $fitMentHMR) {
        if (empty($warrentyHMR)) {
            return 'm';
        }
        if (empty($presentHMR)) {
            return 'm';
        }
        if (empty($fitMentHMR)) {
            return 'm';
        }

        if (intval($warrentyHMR) > (intval($presentHMR) - intval($fitMentHMR))) {
            return true;
        } else {
            return false;
        }
    }

    public function oredCondition($month, $dateOfFitemnet, $warrentyKM, $presentKM, $fitMentKM, $warrentyHMR, $presentHMR, $fitMentHMR) {
        $monthCheck = $this->checkWarntableOrNot($month, $dateOfFitemnet);
        $kmCheck    = $this->checkWarntableOrNotKM($warrentyKM, $presentKM, $fitMentKM);
        $hmrCheck   = $this->checkWarntableOrNotHMR($warrentyHMR, $presentHMR, $fitMentHMR);

        if ($monthCheck === 'm' && $kmCheck === 'm' && $hmrCheck === 'm') {
            $value = 'Not Warrantable';
        } else if ($monthCheck === 'm' && $kmCheck === 'm' && $hmrCheck === true) {
            $value = 'Warrantable';
        } else if ($monthCheck === 'm' && $kmCheck === true && $hmrCheck === 'm') {
            $value = 'Warrantable';
        } else if ($monthCheck === true && $kmCheck === 'm' && $hmrCheck === 'm') {
            $value = 'Warrantable';
        } else if ($monthCheck === false || $kmCheck === false || $hmrCheck === false) {
            $value = 'Not Warrantable';
        } else if ($monthCheck === true && $kmCheck === true && $hmrCheck === 'm') {
            $value = 'Warrantable';
        } else if ($monthCheck === 'm' && $kmCheck === true && $hmrCheck === true) {
            $value = 'Warrantable';
        } else if ($monthCheck === true && $kmCheck === 'm' && $hmrCheck === true) {
            $value = 'Warrantable';
        } else if ($monthCheck === true && $kmCheck === true && $hmrCheck === true) {
            $value = 'Warrantable';
        }
        return $value;
    }
}
