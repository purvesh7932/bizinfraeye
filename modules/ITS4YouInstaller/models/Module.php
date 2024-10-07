<?php
/* * *******************************************************************************
* Description:  ITS4You Installer
* All Rights Reserved.
* Contributor: IT-Solutions4You s.r.o - www.its4you.sk
 * ****************************************************************************** */

class ITS4YouInstaller_Module_Model extends Vtiger_Module_Model
{
    public static $mobileIcon = 'download';

    /**
     * Funxtion to identify if the module supports quick search or not
     */
    public function isQuickSearchEnabled()
    {
        return false;
    }

    /**
     * Function to get Settings links
     * @return array
     */
    public function getSettingLinks()
    {
        $settingsLinks = array();
        $currentUserModel = Users_Record_Model::getCurrentUserModel();

        if ($currentUserModel->isAdminUser()) {
            $settingsLinks[] = array(
                'linktype' => 'LISTVIEWSETTING',
                'linklabel' => vtranslate('LBL_EXTENSIONS', 'Settings:ITS4YouInstaller'),
                'linkurl' => $this->getDefaultUrl(),
            );
            $settingsLinks[] = array(
                'linktype' => 'LISTVIEWSETTING',
                'linklabel' => 'LBL_REQUIREMENTS',
                'linkurl' => 'index.php?module=ITS4YouInstaller&parent=Settings&view=Requirements',
            );
            $settingsLinks[] = array(
                'linktype' => 'LISTVIEWSETTING',
                'linklabel' => 'LBL_MODULE_REQUIREMENTS',
                'linkurl' => 'index.php?module=ITS4YouInstaller&parent=Settings&view=Requirements&mode=Module&sourceModule=ITS4YouInstaller',
            );
            $settingsLinks[] = array(
                'linktype' => 'LISTVIEWSETTING',
                'linklabel' => 'LBL_UPGRADE',
                'linkurl' => 'index.php?module=ModuleManager&parent=Settings&view=ModuleImport&mode=importUserModuleStep1',
            );
            $settingsLinks[] = array(
                'linktype' => 'LISTVIEWSETTING',
                'linklabel' => 'LBL_UNINSTALL',
                'linkurl' => 'index.php?module=ITS4YouInstaller&view=Uninstall&parent=Settings',
            );
        }

        return $settingsLinks;
    }

    public function getDefaultUrl()
    {
        return 'index.php?module=ITS4YouInstaller&parent=Settings&view=Extensions';
    }

    /**
     * @return string
     */
    public function getRequirementsUrl()
    {
        return 'index.php?module=ITS4YouInstaller&parent=Settings&view=Requirements';
    }

    public function getDatabaseTables()
    {
        return array(
            'its4you_installer_alert',
            'its4you_installer_license',
            'its4you_installer_user',
            'its4you_installer_version',
        );
    }

    public static function redirectByUrl($url)
    {
        if (!headers_sent($fileName, $lineNum)) {
            header('location:' . $url);
        } else {
            echo sprintf('<a href="%s" title="Headers Already Sent. File: %s, Line: %s">%s</a>',
                $url, $fileName, $lineNum, vtranslate('LBL_MANUAL_REDIRECT', 'ITS4YouInstaller')
            );
        }
    }
}
