<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Portal_Main_Controller {

	protected function initLogin($redirectUri) {
		if (!Vtiger_Connector::getInstance()->isAuthenticated()) {
			$portalUrl = Portal_Config::get('portal.url');
			if ($portalUrl) {
				if ($redirectUri) {
					header("Location: $portalUrl/index.php?$redirectUri");
					exit;
				} else {
					throw new Exception('Authentication required');
				}
			} else {
				throw new Exception('Authentication required');
			}
		}
	}

	function getUserDetailsUNameAndPass($badgeNo) {
		global $adb;
		$sql = 'select vtiger_portalinfo.user_name,contactid,mobile,vtiger_portalinfo.user_password  from vtiger_contactdetails '
			. ' inner join vtiger_crmentity on vtiger_crmentity.crmid = vtiger_contactdetails.contactid '
			. ' inner join vtiger_portalinfo on vtiger_portalinfo.id = vtiger_contactdetails.contactid '
			. ' where vtiger_contactdetails.contactid = ? and vtiger_crmentity.deleted = 0';
		$sqlResult = $adb->pquery($sql, array($badgeNo));
		$num_rows = $adb->num_rows($sqlResult);
		if ($num_rows == 1) {
			$dataRow = $adb->fetchByAssoc($sqlResult, 0);
			return $dataRow;
		} else {
			return false;
		}
	}

	public function dispatch(Portal_Request $request) {
		Portal_Session::init($request->get('session_id'));

		$response = null;
		try {
			$module = $request->getModule('Portal');
			$view = $request->getView('Index');
			$api = $request->getApi();
			// Routing
			// module=Target&view=Index (try: Target_Index_View, Portal_Default_View - a generic handler)
			// module=Target&api=Context  (try: Target_Context_API,  Portal_Context_API - a fallback handler)

			$targetClass = $module . "_" . ($api ? $api : $view) . "_" . ($api ? "API" : "View");
			$fallbackClass = "Portal_" . ($api ? $api : 'Default') . "_" . ($api ? "API" : "View");
			$target = class_exists($targetClass) ? new $targetClass() : new $fallbackClass();
			if (!$target) {
				throw new Exception('Unsupported request');
			}

			if ($target->requireLogin()) {
				$response = new Portal_Response();
				$accessToken = $request->get('access_token');
				if (empty($accessToken)) {
					$response->setError('Access Token Is Empty');
					if ($response) {
						$response->emit();
					}
					exit();
				}
				$userUniqueId = $request->get('useruniqueid');
				if (empty($userUniqueId)) {
					$response->setError('User Unique Id Is Empty');
					if ($response) {
						$response->emit();
					}
					exit();
				}
				$data = $this->getUserDetailsUNameAndPass($userUniqueId);
				require __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'autoload.php';
				$key = 'ONSGVGFDKNBXVDAWTYSVSCDX' . $data['user_name'] . $data['user_password'] . 'JHJJHJH*&*JHJJHJH&hjdHGHGHG';
				try {
					$decoded = JWT::decode($accessToken, new Key($key, 'HS256'));
					if ($decoded->{'contactid'} != $userUniqueId) {

						$response->setError('Useruniqeid And Access Token Is Not Matching');
						if ($response) {
							$response->emit();
						}
					}
					Portal_Session::set('username', $data['user_name']);
					Portal_Session::set('password', $decoded->{'psw'});
					Portal_Session::set('contact_id', $decoded->{'contactid'});
					$auth = array('Authorization' => 'Basic ' . base64_encode($data['user_name'] . ':' . $data->{'psw'}));
					Portal_Session::set('portal_auth', $auth);
				} catch (Exception $e) {
					$response->setError('Invalid Access Token');
					if ($response) {
						$response->emit();
					}
					exit();
				}
				$this->initLogin($api ? NULL : 'module=Portal&view=Login');
			}

			if (!$api && !$request->isAjax()) {
				$target->preProcess($request);
			}

			$response = $target->process($request);

			if (!$api && !$request->isAjax()) {
				$target->postProcess($request);
			}
		} catch (Exception $e) {
			$response = new Portal_Response();
			$response->setError($e->getMessage());
		}

		if ($response) {
			$response->emit();
		}
	}
}
