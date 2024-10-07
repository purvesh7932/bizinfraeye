<?php

class ServiceEngineer_Module_Model extends Vtiger_Module_Model {

    // public function getModuleBasicLinks() {
    //     return array();
    // }
    public function isMassEditEnabled() {
        return false;
    }

    public function isMassDeleteEnabled() {
        return false;
    }
}
