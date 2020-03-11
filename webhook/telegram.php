<?php
/***************************************************************
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

$debug = FALSE;

$ko_path = '../';
$ko_menu_akt = 'telegram_bot';
include_once($ko_path.'inc/ko.inc');
require_once($ko_path.'inc/TelegramBot/bot.php');


//Check security hash
$hash = $_GET['h'];
if(!$hash || $hash != md5(KOOL_ENCRYPTION_KEY) || !KOOL_ENCRYPTION_KEY) {
	ko_log('telegram_webhook_error', 'Invalid hash: '.$hash);
	exit;
}


//Check Telegram IP range
$telegram_ip_ranges = [
	['lower' => '149.154.160.0', 'upper' => '149.154.175.255'], // literally 149.154.160.0/20
	['lower' => '91.108.4.0',    'upper' => '91.108.7.255'],    // literally 91.108.4.0/22
];

$ip_dec = (float)sprintf("%u", ip2long($_SERVER['REMOTE_ADDR']));
$ok = false;

foreach($telegram_ip_ranges as $telegram_ip_range) if (!$ok) {
	// Make sure the IP is valid.
	$lower_dec = (float) sprintf("%u", ip2long($telegram_ip_range['lower']));
	$upper_dec = (float) sprintf("%u", ip2long($telegram_ip_range['upper']));
	if ($ip_dec >= $lower_dec and $ip_dec <= $upper_dec) $ok = true;
}
if(!$ok) {
	ko_log('telegram_webhook_error', 'Invalid source IP: '.$_SERVER['REMOTE_ADDR']);
	exit;
}


if($debug) {
	$fp = fopen('telegram.log', 'a');
	fputs($fp, "PING at ".date('Y-m-d H:i:s')."\n");
}


//Get message from stdin
$content = file_get_contents("php://input");
$message = json_decode($content);
if($debug) fputs($fp, print_r($message, TRUE));


//Create Bot and process message
try {
	$bot = Bot::getInstance(
		ko_get_setting('telegram_token'),
		ko_get_setting('telegram_botid'),
		ko_get_setting('telegram_botname')
	);
	$bot->setEntry($message);
	$bot->processMessages();
} catch (Exception $e) {
	ko_log('telegram_webhook_error', $e."\n".print_r($message, TRUE));
}
