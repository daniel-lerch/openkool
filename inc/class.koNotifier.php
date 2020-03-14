<?php
/**
 * Date: 07.07.14
 * Time: 17:14
 */
class koNotifier {

	const
		EMERGENCY = 1,
		ALERT = 2,
		CRITICAL = 4,
		ERROR = 8,
		WARNING = 16,
		NOTICE = 32,
		INFO = 64,
		DEBUG = 128,
		NONE = 0,
		ALL = 255,
		ERRS = 15,
		WARNS = 48,

		DISPLAY = 1,
		LOGTODB = 2,
		LOGTOFILE = 4,
		NOTIFYALL = 7;

	private $levelNamesToAlertNames = array(
		'error' => 'danger',
		'info' => 'success',
		'warning' => 'warning',
		'notice' => 'info',
		'critical' => 'danger',
		'emergency' => 'danger',
	);

	private $levelCodesToNames = array();

	private $logToDBLevel = self::ALL;
	private $displayLevel = self::NONE;
	private $logToFileLevel = self::DEBUG;

	private $logFileName = 'log.txt';

	private $notifications = array();
	private $thresholdLevel;

	private $moduleToLLLabel = array();

	public $dummy = 0;

	private static $instance = null;

	private function __clone () {}
	private function __construct () {

		$reflector = new ReflectionClass('koNotifier');

		foreach($reflector->getConstants() as $constantKey => $constantValue) {
			if ($constantValue != self::ALL && $constantValue != self::NONE && $constantValue != self::ERRS) {
				$this->notifications[$constantValue] = array();
			}
			$this->levelCodesToNames[$constantValue] = $constantKey;
		}

		$this->updateThresholdLevel ();

		$this->moduleToLLLabel = array(
			'reservation' => 'res',
			'home' => 'home',
		);
	}
	private function updateThresholdLevel () {
		$this->thresholdLevel = $this->logToDBLevel | $this->displayLevel | $this->logToFileLevel;
	}

	public static function Instance() {
		if (self::$instance === null) {
			self::$instance = new self;
			return (self::$instance);
		}
		else {
			return self::$instance;
		}
	}

	public function setLogToDBLevel ($logToDBLevel) {
		$this->logToDBLevel = $logToDBLevel;
		$this->updateThresholdLevel ();
	}

	public function setDisplayLevel ($displayLevel) {
		$this->displayLevel = $displayLevel;
		$this->updateThresholdLevel ();
	}

	public function setLogToFileLevel ($logToFileLevel) {
		$this->logToFileLevel = $logToFileLevel;
		$this->updateThresholdLevel ();
	}

	public function getLogToFileLevel () {
		return $this->logToFileLevel;
	}

	public function getLogToDBLevel () {
		return $this->logToDBLevel;
	}

	public function getDisplayLevel () {
		return $this->displayLevel;
	}

	public function hasErrors () {
		return $this->hasNotifications (self::ERRS);
	}
	public function hasInfos () {
		return $this->hasNotifications (self::INFO);
	}

	public function setLogFileName ($logFileName) {
		$this->logFileName = $logFileName;
	}

	public function getLogFileName () {
		return $this->logFileName;
	}

	public function addEmergency ($notNumber, $doAction = '', $parameters = array(), $module = '') {
		$this->addNotification ($notNumber, self::EMERGENCY, $doAction, $parameters, $module);
	}
	public function addCritical ($notNumber, $doAction = '', $parameters = array(), $module = '') {
		$this->addNotification ($notNumber, self::CRITICAL, $doAction, $parameters, $module);
	}
	public function addAlert ($notNumber, $doAction = '', $parameters = array(), $module = '') {
		$this->addNotification ($notNumber, self::ALERT, $doAction, $parameters, $module);
	}
	public function addError ($notNumber, $doAction = '', $parameters = array(), $module = '') {
		$this->addNotification ($notNumber, self::ERROR, $doAction, $parameters, $module);
	}
	public function addWarning ($notNumber, $doAction = '', $parameters = array(), $module = '') {
		$this->addNotification ($notNumber, self::WARNING, $doAction, $parameters, $module);
	}
	public function addNotice ($notNumber, $doAction = '', $parameters = array(), $module = '') {
		$this->addNotification ($notNumber, self::NOTICE, $doAction, $parameters, $module);
	}
	public function addInfo ($notNumber, $doAction = '', $parameters = array(), $module = '') {
		$this->addNotification ($notNumber, self::INFO, $doAction, $parameters, $module);
	}
	public function addDebug ($notNumber, $doAction = '', $parameters = array(), $module = '') {
		$this->addNotification ($notNumber, self::DEBUG, $doAction, $parameters, $module);
	}

	public function addTextEmergency ($text, $doAction = '', $module = '') {
		$this->addTextNotification ($text, self::EMERGENCY, $doAction, $module);
	}
	public function addTextCritical ($text, $doAction = '', $module = '') {
		$this->addTextNotification ($text, self::CRITICAL, $doAction, $module);
	}
	public function addTextAlert ($text, $doAction = '', $module = '') {
		$this->addTextNotification ($text, self::ALERT, $doAction, $module);
	}
	public function addTextError ($text, $doAction = '', $module = '') {
		$this->addTextNotification ($text, self::ERROR, $doAction, $module);
	}
	public function addTextWarning ($text, $doAction = '', $module = '') {
		$this->addTextNotification ($text, self::WARNING, $doAction, $module);
	}
	public function addTextNotice ($text, $doAction = '', $module = '') {
		$this->addTextNotification ($text, self::NOTICE, $doAction, $module);
	}
	public function addTextInfo ($text, $doAction = '', $module = '') {
		$this->addTextNotification ($text, self::INFO, $doAction, $module);
	}
	public function addTextDebug ($text, $doAction = '', $module = '') {
		$this->addTextNotification ($text, self::DEBUG, $doAction, $module);
	}

	public function dropDebugs () {
		$this->dropNotifications(self::DEBUG);
	}

	public function addNotification ($notNumber, $notLevel, $doAction = '', $parameters = array(), $module = NULL) {
		global $ko_menu_akt;
		if ($module == '' || $module === NULL) {
			$module = $ko_menu_akt;
		}
		if (array_key_exists($module, $this->moduleToLLLabel) && $this->moduleToLLLabel[$module] == '') {
			$underline = '';
		}
		else {
			$underline = '_';
		}
		$this->notifications[$notLevel][] = array(
			'notNumber' => $notNumber,
			'notText' => null,
			'activeModule' => $module,
			'doAction' => $doAction,
			'parameters' => $parameters,
			'underline' => $underline,
		);
	}

	public function addTextNotification ($text, $notLevel, $doAction = '', $module = NULL) {
		global $ko_menu_akt;
		if ($module == '' || $module === NULL) {
			$module = $ko_menu_akt;
		}
		if (array_key_exists($module, $this->moduleToLLLabel) && $this->moduleToLLLabel[$module] == '') {
			$underline = '';
		}
		else {
			$underline = '_';
		}
		$this->notifications[$notLevel][] = array(
			'notNumber' => null,
			'notText' => $text,
			'activeModule' => $module,
			'doAction' => $doAction,
			'parameters' => array(),
			'underline' => $underline,
		);
	}

	public function jsDisplay ($doPrint=TRUE, $container='#notifications') {
		$prints = array();
		foreach ($this->notifications as $notLevel => $nots) {
			if (($notLevel & $this->displayLevel) != 0) {
				foreach ($nots as $not) {
					if ($not['notText'] === null) {
						if (array_key_exists($not['activeModule'], $this->moduleToLLLabel)) {
							$moduleLabel = $this->moduleToLLLabel[$not['activeModule']];
						}
						else {
							$moduleLabel = $not['activeModule'];
						}
						$LLLabel = vsprintf(getLL(strtolower($this->levelCodesToNames[$notLevel]) . '_' . $moduleLabel . $not['underline'] . $not['notNumber']), $not['parameters']);
					}
					else {
						$LLLabel = $not['notText'];
					}
					$type = $this->levelNamesToAlertNames[strtolower($this->levelCodesToNames[$notLevel])];
					$prints[] =
						'<div class="alert alert-' . $type . ' alert-dismissible" role="alert">
	<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
	' . $LLLabel . '
</div>';
				}
			}
		}
		$print = implode('', $prints);
		$print = '<script>$("'.$container.'").html("'.str_replace(array("\n", '"'), array('', '\"'), $print).'");</script>';
		if ($doPrint) print $print;
		else return $print;

		return TRUE;
	}

	public function display ($doPrint=TRUE) {
		foreach ($this->notifications as $notLevel => $nots) {
			if (($notLevel & $this->displayLevel) != 0) {
				foreach ($nots as $not) {
					if ($not['notText'] === null) {
						if (array_key_exists($not['activeModule'], $this->moduleToLLLabel)) {
							$moduleLabel = $this->moduleToLLLabel[$not['activeModule']];
						}
						else {
							$moduleLabel = $not['activeModule'];
						}
						$LLLabel = vsprintf(getLL(strtolower($this->levelCodesToNames[$notLevel]) . '_' . $moduleLabel . $not['underline'] . $not['notNumber']), $not['parameters']);
					}
					else {
						$LLLabel = $not['notText'];
					}
					$type = $this->levelNamesToAlertNames[strtolower($this->levelCodesToNames[$notLevel])];
					$print =
'<div class="alert alert-' . $type . ' alert-dismissible" role="alert">
	<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
	' . $LLLabel . '
</div>';
					if ($doPrint) print $print;
					else return $print;
				}
			}
		}
		return true;
	}

	public function logToDB () {
		foreach ($this->notifications as $notLevel => $nots) {
			if (($notLevel & $this->logToDBLevel) != 0) {
				foreach ($nots as $not) {
					if ($not['notText'] === null) {
						if (array_key_exists($not['activeModule'], $this->moduleToLLLabel)) {
							$moduleLabel = $this->moduleToLLLabel[$not['activeModule']];
						}
						else {
							$moduleLabel = $not['activeModule'];
						}
						$LLLabel = vsprintf(getLL(strtolower($this->levelCodesToNames[$notLevel]) . '_' . $moduleLabel . $not['underline'] . $not['notNumber']), $not['parameters']);
						ko_error_log(getLL("module_".$not['activeModule']), $not['notNumber'], $LLLabel, $not['doAction']);
					}
					else {
						ko_error_log(getLL("module_".$not['activeModule']), $not['notNumber'], $not['notText'], $not['doAction']);
					}
				}
			}
		}
	}

	public function logToFile () {
		global $BASE_PATH;
		$filePath = $BASE_PATH . $this->logFileName;
		if(!file_exists($filePath)) {
			fclose(fopen($filePath,"x"));
		}
		$logFile = fopen($filePath, 'a');
		foreach ($this->notifications as $notLevel => $nots) {
			if (($notLevel & $this->logToFileLevel) != 0) {
				foreach ($nots as $not) {
					if ($not['notText'] === null) {
						if (array_key_exists($not['activeModule'], $this->moduleToLLLabel)) {
							$moduleLabel = $this->moduleToLLLabel[$not['activeModule']];
						}
						else {
							$moduleLabel = $not['activeModule'];
						}
						$LLLabel = vsprintf(getLL(strtolower($this->levelCodesToNames[$notLevel]) . '_' . $moduleLabel . $not['underline'] . $not['notNumber']), $not['parameters']);
					}
					else {
						$LLLabel = $not['notText'];
					}
					$message = date('Y-m-d H:i:s') . "\t" . $_SESSION['ses_userid'] . "\t" . $LLLabel . "\n";
					fputs($logFile, $message);
				}
			}
		}
		fclose($logFile);
	}

	public function notify ($notifyMask=self::NOTIFYALL, $print=TRUE) {
		$result = TRUE;
		if ($notifyMask & self::DISPLAY != 0) {
			$result = $this->display($print);
		}
		if ($notifyMask & self::LOGTODB != 0) {
			$this->logToDB();
		}
		if ($notifyMask & self::LOGTOFILE != 0) {
			$this->logToFile();
		}
		return $result;
	}

	public function hasNotifications ($notLevelIn) {
		foreach ($this->notifications as $notLevel => $nots) {
			if (($notLevelIn & $notLevel) != 0) {
				if (sizeof($nots) > 0) {
					return true;
				}
			}
		}
		return false;
	}

	public function hasNotification ($notNumberIn, $notLevelIn=self::ALL) {
		foreach ($this->notifications as $notLevel => $nots) {
			if (($notLevelIn & $notLevel) != 0) {
				foreach ($nots as $not) {
					if ($not['notNumber'] == $notNumberIn) {
						return true;
					}
				}
			}
		}
		return false;
	}

	public function getNotifications () {
		$result = '';
		foreach ($this->notifications as $notLevel => $nots) {
			foreach ($nots as $not) {
				if (array_key_exists($not['activeModule'], $this->moduleToLLLabel)) {
					$moduleLabel = $this->moduleToLLLabel[$not['activeModule']];
				}
				else {
					$moduleLabel = $not['activeModule'];
				}
				$LLLabel = vsprintf(getLL(strtolower($this->levelCodesToNames[$notLevel]) . '_' . $moduleLabel . $not['underline'] . $not['notNumber']), $not['parameters']);
				$result .= $not['notNumber'] . ' : ' . $not['notText'] . ' : ' . strtolower($this->levelCodesToNames[$notLevel]) . '_' . $moduleLabel . $not['underline'] . $not['notNumber'] . ' : ' . getLL(strtolower($this->levelCodesToNames[$notLevel]) . '_' . $moduleLabel . $not['underline'] . $not['notNumber']) . ' : ' . $LLLabel . ' : ' . $not['activeModule'] . ' : ' . $not['doAction'] . ';\n';
			}
		}
		return $result;
	}

	public function dropNotifications ($notLevel) {
		foreach ($this->notifications as $lvl => $nots) {
			if ($lvl & $notLevel) $this->notifications[$lvl] = array();
		}
	}
}
