<?PHP

/***************************************************************
 *  Implementation of Telegram Bot
 *  API @link https://core.telegram.org/bots/api
 *
 *  Copyright notice
 *
 *  (c) 2003-2020 Renzo Lauper (renzo@churchtool.org)
 *  All rights reserved
 *
 *  This script is part of the kOOL project. The kOOL project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  A copy is found in the textfile GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *  kOOL is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

require_once('auth.php');

class Bot extends Authentication {

	public $debug = FALSE;

	private $botId;
	private $botName;
	private $endpoint = 'https://api.telegram.org/';
	private $token;
	private $actions = [];

	private $log = [];
	private $entries = [];
	private $lastUpdateId;

	protected static $_instance = null;

	/**
	 * Create a new Connection to Telegram API
	 *
	 * @param String  $token
	 * @param Integer $botId
	 * @param String  $botName
	 * @param Integer $initialUpdateId
	 * @param String  $mode
	 * @throws Exception
	 * @return Bot
	 */
	public static function getInstance($token, $botId, $botName, $initialUpdateId = 0, $mode = "webhook") {
		if (null === self::$_instance) {
			self::$_instance = new self;
			self::$_instance->setToken($token);
			self::$_instance->setToken($token);
			self::$_instance->setBotId($botId);
			self::$_instance->setBotName($botName);
			self::$_instance->setLastUpdateId($initialUpdateId);

			self::$_instance->setActions();

			if ($mode == 'scheduler') {
				self::$_instance->getApiUpdates();
			}
		}
		return self::$_instance;
	}

	protected function __construct() {}

	public function __destruct() {
		foreach ($this->log as $log) {
			ko_log('telegram_' . $log['type'], $log['string']);
		}
	}


	public function setActions() {
		global $PLUGINS;

		$this->actions[] = [
			'command' => '/start',
			'description' => getLL('telegram_command_start'),
			'fcn' => 'ko_telegram_command_start',
		];
		$this->actions[] = [
			'command' => '/stop',
			'description' => getLL('telegram_command_stop'),
			'fcn' => 'ko_telegram_command_stop',
		];
		$this->actions[] = [
			'command' => '/help',
			'description' => getLL('telegram_command_help'),
			'fcn' => 'ko_telegram_command_help',
		];

		//TODO: Allow plugins to add commands

	}//setActions()


	public function getActions() {
		return $this->actions;
	}


	/**
	 * @param Integer $botId
	 */
	public function setBotId($botId) {
		$this->botId = $botId;
	}

	/**
	 * @param String $token
	 */
	public function setToken($token) {
		if (substr($token, 0, 3) != "bot") {
			$this->token = "bot" . $token;
		} else {
			$this->token = $token;
		}
	}

	/**
	 * @param String $endpoint
	 */
	public function setEndpoint($endpoint) {
		$this->endpoint = $endpoint;
	}

	/**
	 * @param Integer $id
	 */
	public function setLastUpdateId($id) {
		if (!is_numeric($id)) {
			$id = 0;
		}
		$this->lastUpdateId = $id;
	}

	/**
	 * @return Integer
	 */
	public function getLastUpdateId() {
		return $this->lastUpdateId;
	}

	/**
	 * @return Integer
	 */
	public function getBotId() {
		return $this->botId;
	}

	/**
	 * @return String
	 */
	public function getEndpoint() {
		return $this->endpoint;
	}


	/**
	 * @throws Exception
	 */
	public function getApiUpdates() {
		$data = [
			'offset' => ($this->getLastUpdateId() + 1),
			'timeout' => 1,
		];

		$response = $this->sendCurl('getUpdates', $data);
		if (!$response->ok) {
			throw new Exception("Problem connecting to API: " . $this->getEndpoint());
		}

		$this->entries = $response->result;
		$lastEntry = end($this->entries);
		$this->setLastUpdateId($lastEntry->update_id);
	}


	public function setEntry($entry) {
		$this->entries[] = $entry;
	}

	/**
	 * Go through all new messages sent to our bot and check if bot_command /login or /start with usertoken is set.
	 *
	 * @throws Exception
	 */
	public function processMessages() {
		if ($this->debug) $fp = fopen('telegram.log', 'a'); else $fp = "";

		foreach ($this->entries as $entry) {
			$processed = FALSE;

			//A command
			if (substr($entry->message->text, 0, 1) == '/') {
				if ($this->debug) fputs($fp, "command found\n");
				foreach ($this->actions as $action) {
					if ($processed) continue;

					if (substr(strtolower($entry->message->text), 0, strlen($action['command'])) == $action['command']) {
						if ($this->debug) fputs($fp, "command found: " . $action['command'] . "\n");
						if ($action['fcn']) {
							$processed = TRUE;

							if ($this->debug) fputs($fp, "calling: " . $action['fcn'] . "\n");
							$status = [];
							call_user_func_array($action['fcn'], [&$status, &$this, &$entry]);
							if ($this->debug) fputs($fp, 'command: ' . $action['command'] . ', status: ' . print_r($status, TRUE));
							if ($status['ok'] === TRUE) {
								$logtype = 'info';
							} else {
								$logtype = 'error';
							}
							if ($status['log']) {
								$this->addLog($logtype, $status['log']);
							}
							if ($status['message']) {
								$this->sendNotification($entry->message->from->id, $status['message']);
							}
						}
					}
				}
				if (!$processed) {
					$message = getLL('telegram_unknown_command') . '<br /><br />';
					$message .= getLL('telegram_default_error_message');
					$this->sendNotification($entry->message->from->id, $message);
				}
			} //Not a command
			else {
				$message = getLL('telegram_default_error_message');
				$this->sendNotification($entry->message->from->id, $message);
			}
		}
	}//processMessages()

	/**
	 * remove html, convert linebreaks and set encoding
	 *
	 * @param string $message
	 * @return string
	 */
	public function encodeMessage($message) {
		$message = html_entity_decode($message, ENT_COMPAT | ENT_HTML401, 'ISO-8859-1');
		$message = str_replace('</p>', PHP_EOL, $message);
		$message = preg_replace('/\<br(\s*)?\/?\>/i', PHP_EOL, $message);
		$message = preg_replace('/<p\b[^>]*>(.*?)<\/p>/mi', PHP_EOL . "$1", $message);
		$message = strip_tags($message, '<b><strong><em><i><a><code><pre>');
		return utf8_encode($message);
	}

	/**
	 * Create the array that will be send to telegram server
	 *
	 * @param int    $chatid
	 * @param string $message
	 * @throws Exception
	 */
	public function sendNotification($chatid, $message) {
		$data = [
			'chat_id' => $chatid,
			'text' => $this->encodeMessage($message),
			'disable_notification' => FALSE,
			'disable_web_page_preview' => 1,
			'parse_mode' => 'HTML',
		];

		$response = $this->sendCurl('sendMessage', $data);
		if (!$response->ok) {
			$response_string = var_export($response, TRUE);
			$this->addLog('error', $response_string);
			throw new Exception($response->description);
		}
	}

	private function sendCurl($command, Array $data = NULL) {
		$uri = $this->endpoint . $this->token . "/" . $command;
		$data_string = '';

		if ($data) {
			$data_string = json_encode($data);
		}

		$ch = curl_init($uri);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
				'Content-Type: application/json',
				'Content-Length: ' . strlen($data_string)]
		);

		$json = curl_exec($ch);
		curl_close($ch);

		if ($this->debug) {
			die($json);
		}
		return json_decode($json);
	}

	/**
	 * Update ko_leute with telegramid
	 *
	 * @param int $userid ko_leute.id
	 * @param int $chatid ko_leute.telegram_id
	 */
	public function addTelegramIdToUser($userid, $chatid) {
		$data = ['telegram_id' => $chatid];
		$where = "WHERE `id` = " . $userid;
		db_update_data('ko_leute', $where, $data);
	}

	/**
	 * @return mixed
	 */
	public function getBotName() {
		return $this->botName;
	}

	/**
	 * @param mixed $botName
	 */
	public function setBotName($botName) {
		$this->botName = $botName;
	}

	public function addLog($type, $string) {
		$this->log[] = ['type' => $type, 'string' => $string];
	}
}

/**
 * @param array  $status informations to process
 * @param Bot    $bot
 * @param object $entry telegram response
 */
function ko_telegram_command_help(&$status, &$bot, &$entry) {
	$status = [];
	$status['ok'] = TRUE;
	//$status['log'] = FALSE;
	$status['message'] = sprintf(getLL('telegram_help_notification'), ko_get_setting('info_name'));

	$status['message'] .= '<br /><br />';
	foreach ($bot->getActions() as $action) {
		$status['message'] .= $action['command'] . ' - ' . $action['description'] . '<br />';
	}
}


/**
 * @param array  $status informations to process
 * @param Bot    $bot
 * @param object $entry telegram response
 */
function ko_telegram_command_start(&$status, &$bot, &$entry) {
	$usertoken = trim(substr($entry->message->text, 6));
	if (!$usertoken) {
		$status['ok'] = FALSE;
		$status['message'] = getLL('telegram_registration_error_no_token');
		return;
	}

	$userid = $bot->doAuthentication($usertoken);
	if (is_numeric($userid)) {
		ko_get_person_by_id($userid, $person);
		$bot->addTelegramIdToUser($userid, $entry->message->from->id);

		$status['ok'] = TRUE;
		$status['log'] = 'Added user: ' . trim($person['vorname'] . ' ' . $person['nachname'] . ' ' . $person['firm']) . ' (' . $userid . ') with chatid: ' . $entry->message->from->id;
		$status['message'] = getLL('telegram_registration_success');
	} else {
		$status['ok'] = FALSE;
		$status['message'] = getLL('telegram_registration_error');
	}
}


/**
 * @param array  $status informations to process
 * @param Bot    $bot
 * @param object $entry telegram response
 */
function ko_telegram_command_stop(&$status, &$bot, &$entry) {
	$telegramId = $entry->message->from->id;
	if (!$telegramId) {
		$status['ok'] = FALSE;
		return;
	}
	$people = db_select_data('ko_leute', "WHERE `telegram_id` = '$telegramId' AND `hidden` = '0' AND `deleted` = '0'");
	if (sizeof($people) == 0) {
		$status['ok'] = FALSE;
		$status['log'] = '/stop command not successful, no address found for ID ' . $telegramId;
		$status['message'] = getLL('telegram_stop_error');
	} else if (sizeof($people) == 1) {
		db_update_data('ko_leute', "WHERE `telegram_id` = '$telegramId' AND `hidden` = '0' AND `deleted` = '0'", ['telegram_id' => -1]);
		$person = array_shift($people);

		$status['ok'] = TRUE;
		$status['log'] = 'Unsubscribed user: ' . trim($person['vorname'] . ' ' . $person['nachname'] . ' ' . $person['firm']) . ' (' . $person['id'] . ') with chatid: ' . $entry->message->from->id;
		$status['message'] = getLL('telegram_stop_success');
	} else {
		$status['ok'] = FALSE;
		$status['log'] = '/stop command not successful, multiple addresses found for ID ' . $telegramId;
	}
}

