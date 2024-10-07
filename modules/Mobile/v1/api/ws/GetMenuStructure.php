<?php
include_once dirname(__FILE__ , 4).'\Settings\MenuEditor\models\module.php';
class Mobile_WS_GetMenuStructure extends Mobile_WS_Controller {
	
	function process(Mobile_API_Request $request) {
		$groupings = Settings_MenuEditor_Module_Model::getAllVisibleModules();
		foreach($groupings as $key => $value){
			foreach($value as $key2 => $moduleInfo){
				if(	$groupings[$key][$key2]->{'name'} == 'EmailTemplates'){
					$groupings[$key][$key2]->{'title'} = vtranslate($moduleInfo->{'label'},$key2);
					$groupings[$key][$key2]->{'url'} = '/emails';
					$groupings[$key][$key2]->{'icon'} = 'aica_emails';
				} else {
					$groupings[$key][$key2]->{'title'} = vtranslate($moduleInfo->{'label'},$key2);
					$groupings[$key][$key2]->{'url'} = '/'.strtolower($moduleInfo->{'label'});
					$groupings[$key][$key2]->{'icon'} = 'aica_'.strtolower($moduleInfo->{'label'});
				}
			}
		}
		$quotesAndServoices = [];
		$accountsarr = [];
		$toArray = [];
		foreach ($groupings as $key => $value){

			if($key == 'MARKETING' || $key == 'SALES' || $key == 'INVENTORY' || $key == 'SUPPORT' || $key == 'SETTINGS'){
					if($key == 'SALES'){
						foreach($value as $key2 => $value2){
							if($value2->{'name'} == 'Quotes' || $value2->{'name'} == 'Services'){
								array_push($quotesAndServoices,$value2);
								continue;
							}
						}
					}
			    	continue;
			}
			$toArray1 = [];
			$arr = [];
			foreach($value as $key2 => $value2){

				if($value2->{'name'} == 'Quotes' || $value2->{'name'} == 'Services' ){
					array_push($quotesAndServoices,$value2);
					continue;
				}
				if( $value2->{'name'} == 'Services'){
					array_push($quotesAndServoices,$value2);
					continue;
				}
				if( $value2->{'name'} == 'Accounts'){
					array_push($accountsarr,$value2);
					continue;
				}
				array_push($arr,$value2);
			}
            $toArray1['menu'] = $arr;
			$toArray1['menuname'] = $key;
			$toArray1['iconName'] = $this->getGroupingIconName($key);
			array_push($toArray,$toArray1);
        }
		
		$users = [];
		$users['title'] = 'Users';
		$users['url'] = '/profile';
		$users['icon'] = 'aica_users';
		array_push($quotesAndServoices,$users);

		$users = [];
		$users['title'] = 'Groups';
		$users['url'] = '/groups';
		$users['icon'] = 'aica_groups';
		array_push($quotesAndServoices,$users);

		$users = [];
		$users['title'] = 'Roles & Permission';
		$users['url'] = '/roles';
		$users['icon'] = 'aica_roles';
		array_push($quotesAndServoices,$users);

		$toArray1 = [];
		$toArray1['menu'] = $quotesAndServoices;
		$toArray1['menuname'] = "MASTER";
		$toArray1['iconName'] ="c-sidebar-nav-icon fas fa-box";
		array_push($toArray,$toArray1);

		$toArray1 = [];
	    $users = [];
		$users['title'] = 'Clients';
		$users['url'] = '/accounts';
		$users['icon'] = 'aica_accounts';
		array_push($accountsarr,$users);
		$toArray1['menu'] = $accountsarr;
		$toArray1['menuname'] = "CLIENT";
		$toArray1['iconName'] = $this->getGroupingIconName('SETTINGS');
		array_push($toArray,$toArray1);

        $toArray1 = [];
		$users = [];
		$users['title'] = 'Email Config';
		$users['url'] = '/emailconfig';
		$users['icon'] = 'aica_emailconfig';
		$emailConfig = [];
		array_push($emailConfig,$users);
		$toArray1['menu'] = $emailConfig;
		$toArray1['menuname'] = "SETTINGS";
		$toArray1['iconName'] = 'c-sidebar-nav-icon fas fa-cog';
		array_push($toArray,$toArray1);

		$resp['structure'] = $toArray;
		$response = new Mobile_API_Response();
		$response->setResult($resp);
        return $response;
	}

	function getGroupingIconName($group){

		switch($group){
			case 'PROJECT':
				return 'c-sidebar-nav-icon fas fa-sticky-note';
			case 'TOOLS' :
				return 'c-sidebar-nav-icon fas fa-tools';
			case 'SETTINGS' :
				return 'c-sidebar-nav-icon fas fa-users';
		}
		return '';

	}

}