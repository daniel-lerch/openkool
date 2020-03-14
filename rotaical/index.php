<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2003-2015 Renzo Lauper (renzo@churchtool.org)
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

$ko_path = "../";
$ko_menu_akt = "ical";

include($ko_path."inc/ko.inc");
include($ko_path."rota/inc/rota.inc");


//Include plugins
$hooks = hook_include_main('rota');
if(sizeof($hooks) > 0) foreach($hooks as $hook) include_once($hook);


$mapping = array(';' => '\;', ',' => '\,', "\r" => '', "\n" => "\r\n ");
define('CRLF', chr(13).chr(10));


$mode = 'person';

if(isset($_GET['user'])) { //User hash given in URL
	$userhash = $_GET['user'];
	if(strlen($userhash) != 32) exit;
	for($i=0; $i<32; $i++) {
		if(!in_array(substr($userhash, $i, 1), array(1,2,3,4,5,6,7,8,9,0,'a','b','c','d','e','f'))) exit;
	}
	if(!defined('KOOL_ENCRYPTION_KEY') || trim(KOOL_ENCRYPTION_KEY) == '') exit;

	$pId = 0;
	ko_get_logins($logins);
	foreach($logins as $login) {
		if(md5($login['id'].$login['password'].KOOL_ENCRYPTION_KEY) == $userhash) {
			$auth = TRUE;
			$_SESSION['ses_username'] = $login['login'];
			$_SESSION['ses_userid']   = $login['id'];
			$pId = $login['leute_id'];
			ko_init();
		}
	}
	unset($logins);

	$mode = 'user';

	if(!$pId || !ko_module_installed("rota")) {
		header("HTTP/1.0 404 Not Found");
	}
}
else if ($_GET['person']) {
	// check if checksum is correct for given userId
	$pS = $_GET['person'];
	$xPos = strpos($pS, 'x');
	$pId = intval(substr($pS, 0, $xPos));
	$pHash = substr($pS, $xPos+1);

	$mode = 'person';

	$test = strtolower(substr(md5($pId . KOOL_ENCRYPTION_KEY . 'rotaIcal' . KOOL_ENCRYPTION_KEY . 'rotaIcal' . KOOL_ENCRYPTION_KEY . $pId), 0, 10));
	if ($test != $pHash) {
		header("HTTP/1.0 401 Unauthorized");
		print "No access";
		exit;
	}

	$login = db_select_data('ko_admin', "WHERE `leute_id` = '{$pId}'", '*', '', '', TRUE);
	if ($login && $login['leute_id'] == $pId) {
		$auth = TRUE;
		$_SESSION['ses_username'] = $login['login'];
		$_SESSION['ses_userid']   = $login['id'];
		ko_init();

		$mode = 'user';

		if(!ko_module_installed("rota")) {
			header("HTTP/1.0 404 Not Found");
		}
	}
}
else {
	header("HTTP/1.0 401 Unauthorized");
	print "No access";
	exit;
}

//Get setting of how far back to export events
$ical_deadline = ko_get_userpref($_SESSION['ses_userid'], 'rota_ical_deadline');
if($ical_deadline >= 0) $ical_deadline = date('Y-m-d H:i:s');
else $ical_deadline = date('Y-m-d H:i:s', strtotime("{$ical_deadline} months", time()));

ko_include_kota(array('ko_rota_teams'));

if ($mode == 'person' || $mode == 'user') {
	$schedule = array();
	$schedule_ = ko_rota_get_scheduled_events($pId, $ical_deadline);

	foreach ($schedule_ as $eventId => $s_) {
		foreach ($s_['in_teams'] as $team) {
			$s = $s_;
			$s['_helpers'] = ko_rota_get_helpers_by_event_team($eventId, $team);
			$s['team_id'] = $team;
			$s['event_id'] = $eventId;
			$schedule[] = $s;
		}
	}
} else {
	//Get access rights
	ko_get_access('rota', $_SESSION['ses_userid'], TRUE);

	// TODO: has to be completed if we want iCal links for teams
	header("HTTP/1.0 401 Unauthorized");
	exit;
}

$ical = ko_get_ics_for_rota($schedule);


//Set charset to utf-8, but not for google calendar (there seem to be problems with utf-8 for google as of 2010-08)
if(FALSE === strpos($_SERVER['HTTP_USER_AGENT'], 'Googlebot')) {
	$charset = 'utf-8';
	$ical = utf8_encode($ical);
} else {
	$charset = 'latin1';
}


//Output
if (isset($_SERVER["HTTP_USER_AGENT"]) && strpos($_SERVER["HTTP_USER_AGENT"], "MSIE")) {
	// IE cannot download from sessions without a cache
	header("Cache-Control: public");
	// q316431 - Don't set no-cache when over HTTPS
	if (	!isset($_SERVER["HTTPS"]) || $_SERVER["HTTPS"] != "on") {
		header("Pragma: no-cache");
	}
}
else {
	header("Cache-Control: no-cache, must-revalidate");
	header("Pragma: no-cache");
}
header('Content-Type: text/calendar; charset='.$charset, TRUE);
header('Content-Disposition: attachment; filename="kOOLrota.ics"');
header("Content-Length: ".strlen($ical));
print $ical;


//Clear session
session_destroy();
unset($_SESSION);
unset($GLOBALS['kOOL']);
?>
