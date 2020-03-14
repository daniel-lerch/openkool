<?php

require_once('GoogleCloudPrint.php');
require_once('Config.php');

class koGoogleCloudPrint {
	public $redirectConfig;
	public $authConfig;
	public $offlineAccessConfig;
	public $refreshTokenConfig;
	public $urlConfig;

	public $api;

	private static $instance = null;
	public static function Instance() {
		if (self::$instance === null) {
			self::$instance = new self;
			return (self::$instance);
		}
		else {
			return self::$instance;
		}
	}

	private function __clone () {}
	private function __construct (){
		global $redirectConfig, $authConfig, $offlineAccessConfig, $refreshTokenConfig, $urlConfig;

		$this->redirectConfig = $redirectConfig;
		$this->authConfig = $authConfig;
		$this->offlineAccessConfig = $offlineAccessConfig;
		$this->refreshTokenConfig = $refreshTokenConfig;
		$this->urlConfig = $urlConfig;

		$this->api = new GoogleCloudPrint();
	}

	public function requestUserAuth() {
		$_SESSION['REQUEST_URI'] = $_SERVER['REQUEST_URI'];
		$this->redirect('/googleCloudPrintRedirect.php?op=offline');
	}

	public function redirectToUserRequest() {
		$this->redirect($_SESSION['REQUEST_URI']);
	}

	public function processCode($code) {
		$this->authConfig['code'] = $code;
		$responseObj = $this->api->getAccessToken($this->urlConfig['accesstoken_url'], $this->authConfig);
		$this->setRefreshToken($responseObj->refresh_token);
		$this->setAuthToken($responseObj->access_token, $responseObj->expires_in);
	}

	public function getRefreshToken() {
		return ko_get_setting('google_cloud_print_api_refresh_token');
	}

	public function setRefreshToken($token) {
		ko_set_setting('google_cloud_print_api_refresh_token', $token);
	}

	public function getAuthToken() {
		if ($_SESSION['google_cloud_print_api_access_expiry'] > time() + 60) {
			return $_SESSION['google_cloud_print_api_access_token'];
		} else {
			return FALSE;
		}
	}

	public function setAuthToken($token, $expiresInSecs) {
		$_SESSION['google_cloud_print_api_access_token'] = $token;
		$_SESSION['google_cloud_print_api_access_expiry'] = time() + intval($expiresInSecs);
	}

	public function checkAuthStatus() {
		if (!$this->getAuthToken()) {
			if (!($refreshToken = $this->getRefreshToken())) {
				$this->requestUserAuth();
			} else {
				$this->refreshTokenConfig['refresh_token'] = $refreshToken;
				$responseObj = $this->api->getAccessTokenByRefreshToken($this->urlConfig['refreshtoken_url'],http_build_query($this->refreshTokenConfig));
				if (!@$responseObj->access_token) {
					$this->requestUserAuth();
				} else {
					$this->setAuthToken($responseObj->access_token, $responseObj->expires_in);
				}
			}
		}
		$this->api->setAuthToken($this->getAuthToken());
	}

	public function getPrinters() {
		$this->checkAuthStatus();
		return $this->api->getPrinters();
	}

	public function sendPrintToPrinter($printerid,$printjobtitle,$filepath,$contenttype,$options=array()) {
		$this->checkAuthStatus();
		return $this->api->sendPrintToPrinter($printerid,$printjobtitle,$filepath,$contenttype,$options);
	}

	public function jobStatus($jobid) {
		$this->checkAuthStatus();
		return $this->api->jobStatus($jobid);
	}

	public function redirect($url) {
		print '<script type="text/javascript">window.location = "'.$url.'";</script>';
	}
}
