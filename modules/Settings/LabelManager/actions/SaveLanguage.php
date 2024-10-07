<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_LabelManager_SaveLanguage_Action extends Vtiger_IndexAjax_View {
	public static $languageContainer;
	public function requiresPermission(\Vtiger_Request $request) {
		return true;
	}
	
	function __construct() {
		parent::__construct();
		$this->exposeMethod('saveLanguageLabel');
	}

	public function process (Vtiger_Request $request) {
		$mode = $request->getMode();
		if($mode){
			echo $this->invokeExposedMethod($mode, $request);
			return;
		}
	}
	
	public function saveLanguageLabel(Vtiger_Request $request) {
		global $root_directory;
		$viewer = $this->getViewer($request);
		$qualifiedModuleName = $request->getModule(false);
		
		$sourceModule = $request->get('sourceModule');
		$serializeData = explode('&', urldecode($request->get('serializeData')));
		$language = $request->get('language');
		
		$languageFilename = $root_directory.'/languages/'.$language.'/'.$sourceModule.'.php';
		$arrFile = file($languageFilename, FILE_IGNORE_NEW_LINES);
	
		if (file_exists($languageFilename)) {
            require $languageFilename;
            self::$languageContainer[$language][$sourceModule]['languageStrings'] = $languageStrings;
			self::$languageContainer[$language][$sourceModule]['jsLanguageStrings'] = $jsLanguageStrings;
        }

        $languageFileArray = isset(self::$languageContainer[$language][$sourceModule]) ? self::$languageContainer[$language][$sourceModule] : array();
	
		$file_module = fopen($languageFilename, "r");
        $newLanguage = '';

        if (false !== strpos($file_module, 'languageStrings')) {
            while (!feof($file_module)) {
                $line = fgets($file_module);
                if ((strpos($line, 'languageStrings'))) {
                    break;
                }
                $newLanguage .= $line;
            }
        } else {
            $newLanguage .= '<?php' . PHP_EOL . PHP_EOL;
        }

        $newLanguage .= '$languageStrings = array(' . PHP_EOL;

        foreach ($langArrays['languageStrings'] as $languageStringsKey => $languageStringsValue) {
            $line = str_replace("'", "\'", stripslashes($languageStringsValue));

            if (!empty($line)) {
                $newLanguage .= "\t" . "'" . $languageStringsKey . "' => '" . $line . "'," . PHP_EOL;
            }
        }
		
		foreach($serializeData as $key => $value){
			if($key != 0 && $key != 1 && $key != 2 && $key != 3 && $key != 4 && $key != 5 && $key != 6){
				$explodLabel = explode('=', $value);
				if($explodLabel[1]){
					$newLanguage .= "\t" . "'" . $explodLabel[0] . "' => '" . $explodLabel[1] . "'," . PHP_EOL;
				}
			}
		}
        $newLanguage .= ");" . PHP_EOL . PHP_EOL;
		
		$newLanguage .= '$jsLanguageStrings = array(' . PHP_EOL;
        foreach ($languageFileArray['jsLanguageStrings'] as $jsLanguageStringsKey => $jsLanguageStringsValue) {
            $line = str_replace("'", "\'", stripslashes($jsLanguageStringsValue));

            if (!empty($line)) {
                $newLanguage .= "\t" . "'" . $jsLanguageStringsKey . "' => '" . $line . "'," . PHP_EOL;
            }
        }
		$newLanguage .= ");" . PHP_EOL . PHP_EOL;
		
        file_put_contents($languageFilename, $newLanguage);
	}
}