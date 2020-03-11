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

$ko_path = "../";
$ko_menu_akt = "ical";

include($ko_path . "inc/ko.inc");
include($ko_path . "rota/inc/rota.inc");

//Include plugins
$hooks = hook_include_main('rota');
if (sizeof($hooks) > 0) foreach ($hooks as $hook) include_once($hook);

$mapping = [';' => '\;', ',' => '\,', "\r" => '', "\n" => "\r\n "];
define('CRLF', chr(13) . chr(10));

$ical_deadline = date('Y-m-d H:i:s', strtotime("-9999 months", time()));
ko_include_kota(['ko_rota_teams']);


/**
 * check if checksum is correct
 *
 * @param string $hash
 * @return int|bool $id
 */
function ko_check_ical_hash($hash) {
	$xPos = strpos($hash, 'x');
	$id = intval(substr($hash, 0, $xPos));
	$pHash = substr($hash, $xPos + 1);
	$test = strtolower(substr(md5($id . KOOL_ENCRYPTION_KEY . 'rotaIcal' . KOOL_ENCRYPTION_KEY . 'rotaIcal' . KOOL_ENCRYPTION_KEY . $id), 0, 10));
	if($pHash != $test) {
		return FALSE;
	} else {
		return $id;
	}
}

$schedule = [];
if ($_GET['person']) {
	$id = ko_check_ical_hash($_GET['person']);
	ko_get_person_by_id($id, $person);

	if (!$person) {
		header("HTTP/1.0 401 Unauthorized");
		print "No access"; exit;
	}

	$schedule_ = ko_rota_get_scheduled_events($id, $ical_deadline);
	foreach ($schedule_ as $eventId => $s_) {
		foreach ($s_['in_teams'] as $team) {
			$s = $s_;
			$s['_helpers'] = ko_rota_get_helpers_by_event_team($eventId, $team);
			$s['team_id'] = $team;
			$s['event_id'] = $eventId;
			$schedule[] = $s;
		}
	}
} else if ($_GET['team']) {
	$id = ko_check_ical_hash($_GET['team']);
	$where = "WHERE id = '" . $id ."'";
	$team = db_select_data("ko_rota_teams", $where, "*", "", "LIMIT 1", TRUE, TRUE);

	if (!$team) {
		header("HTTP/1.0 401 Unauthorized");
		print "No access"; exit;
	}

	$where = "WHERE team_id = '" . $id . "'";
	$schedules = db_select_data("ko_rota_schedulling", $where);

	if($team['rotatype'] == "day") {
		foreach($schedules AS $schedule_) {
			$helpers = [];
			foreach(explode(",", $schedule_['schedule']) AS $person_id) {
				if(is_numeric($person_id)) {
					ko_get_person_by_id($person_id, $person);
					$helpers[] = [
						"vorname" => $person['vorname'],
						"nachname" => $person['nachname'],
					];
				} else {
					$helpers[] = [
						"name" => $person_id,
						"is_free_text" => true,
					];
				}
			}

			$schedule[] = [
				"id" => $schedule_['team_id'] . "_" . $schedule_['event_id'],
				"startdatum" => $schedule_['event_id'],
				"startzeit" => "00:00:00",
				"enddatum" => $schedule_['event_id'],
				"endzeit" => "00:00:00",
				"_helpers" => $helpers,
				"team_id" => $schedule_['team_id'],
				"event_id" => $schedule_['team_id'] . "_" . $schedule_['event_id'],
			];
		}

	} else {
		foreach($schedules AS $schedule_) {
			$helpers = ko_rota_get_helpers_by_event_team($schedule_['event_id'], $id, true);
			ko_get_event_by_id($schedule_['event_id'], $event);
			if(empty($event)) continue;
			$schedule[] = [
				"id" => $schedule_['event_id'],
				"startdatum" => $event['startdatum'],
				"startzeit" => $event['startzeit'],
				"enddatum" => $event['enddatum'],
				"endzeit" => $event['endzeit'],
				"_helpers" => $helpers,
				"team_id" => $id,
				"event_id" => $schedule_['event_id'],
			];
		}
	}
} else {
	header("HTTP/1.0 401 Unauthorized");
	print "No access";
	exit;
}

$ical = ko_get_ics_for_rota($schedule);

//Set charset to utf-8, but not for google calendar (there seem to be problems with utf-8 for google as of 2010-08)
if (FALSE === strpos($_SERVER['HTTP_USER_AGENT'], 'Googlebot')) {
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
	if (!isset($_SERVER["HTTPS"]) || $_SERVER["HTTPS"] != "on") {
		header("Pragma: no-cache");
	}
} else {
	header("Cache-Control: no-cache, must-revalidate");
	header("Pragma: no-cache");
}

header('Content-Type: text/calendar; charset=' . $charset, TRUE);
header('Content-Disposition: attachment; filename="kOOLrota.ics"');
header("Content-Length: " . strlen($ical));
print $ical;

//Clear session
session_destroy();
unset($_SESSION);
unset($GLOBALS['kOOL']);
