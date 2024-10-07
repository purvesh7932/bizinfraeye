<?php
/* * *******************************************************************************
 * The content of this file is subject to the ITS4YouInstaller license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

class Settings_ITS4YouInstaller_License_Action extends Settings_Vtiger_Index_Action
{

    public function process(Vtiger_Request $request)
    {

        $success = false;
        $message = 'LBL_INVALID_LICENSE';
        $mode = $request->getMode();
        $qualifiedModule = $request->getModule(false);

        $this->clearCache();

        if ($mode == 'update') {
            $success = self::updateLicenses(true);
            $message = 'LBL_UPDATE_MESSAGE';

        } elseif ($request->has('license') && !$request->isEmpty('license')) {
            $license = trim($request->get('license'));
            if (!empty($license)) {
                $licenseModel = Settings_ITS4YouInstaller_License_Model::getInstance((string)$license);

                if (!$licenseModel) {
                    $licenseModel = Settings_ITS4YouInstaller_License_Model::getCleanInstance((string)$license);
                }

                if ($mode === 'activate') {
                    $result = $licenseModel->activate();
                    $success = $result['success'];
                    $message = $result['message'];

                } elseif ($mode === 'deactivate') {
                    $success = $licenseModel->deactivate();
                    $message = 'LBL_DEACTIVATE_MESSAGE';
                }
            }
        }

        $this->clearCache();

        $result = array('success' => $success, 'message' => vtranslate($message, $qualifiedModule));
        $response = new Vtiger_Response();
        $response->setResult($result);
        $response->emit();
    }

    public function clearCache()
    {
        (new Settings_ITS4YouInstaller_Extension_Model())->clearCache();
    }

    /**
     * @param false $triggered
     * @return bool
     * @throws Exception
     */
    public static function updateLicenses($triggered = false)
    {
        $licenseModels = Settings_ITS4YouInstaller_License_Model::getList();

        foreach ($licenseModels as $licenseModel) {
            $licenseModel->update($triggered);
        }

        return true;
    }
}