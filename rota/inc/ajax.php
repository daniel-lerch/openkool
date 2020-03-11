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

error_reporting(0);

//Set session id from GET (session will be started in ko.inc)
if(!isset($_GET["sesid"]) && !isset($_POST["sesid"])) exit;
if (isset($_GET["sesid"])) $sesid = $_GET["sesid"];
if (!$sesid && isset($_POST["sesid"])) $sesid = $_POST["sesid"];
if(FALSE === session_id($sesid)) exit;

//Send headers to ensure latin1 charset
header('Content-Type: text/html; charset=ISO-8859-1');

$ko_menu_akt = 'rota';
$ko_path = '../../';
require_once($ko_path.'inc/ko.inc');
$ko_path = '../';

array_walk_recursive($_GET,'utf8_decode_array');

//Rechte auslesen
ko_get_access('daten');
ko_get_access('rota');
if($access['rota']['MAX'] < 1) exit;
 
ko_include_kota(array('ko_rota_teams', 'ko_event'));

//Plugins
$hooks = hook_include_main('rota');
if(sizeof($hooks) > 0) foreach($hooks as $hook) include_once($hook);
 
require($BASE_PATH.'rota/inc/rota.inc');

$hooks = hook_include_sm();
if(sizeof($hooks) > 0) foreach($hooks as $hook) include($hook);

hook_show_case_pre($_SESSION['show']);

/**
 * Print the selector and values to replace in DOM after ajax-request
 *
 * @param int $event_id event which has changed
 * @param int $team_id team which has changed
 * @param int $schedule person or group which has changed
 * @param string $action scheduled or unscheduled
 */
function ko_rota_print_planning_code($event_id, $team_id, $schedule, $action) {
	if(is_numeric($schedule) || substr($schedule,0,1) == "g") {
		if($action == "schedule") {
			$icon = "<i class='fa fa-circle rota_scheduled'></i>";
		} else {
			$icon = "<i class='fa fa-circle-thin'></i>";
		}
		print 'rota_schedule_' . $event_id . '_' . $team_id . '_' . $schedule . '@@@' . $icon;
	} else {
		$icon = "<i class='fa fa-circle".($action=="schedule" ? " rota_scheduled" : "-thin")."'></i>";
		print 'rota_schedule_' . $event_id . '_' . $team_id . '_free_' . md5($schedule) . '@@@' . $icon;
	}

	$all_teams = ko_rota_get_all_teams();
	$event = ko_rota_get_events('', $event_id, TRUE);

	if($all_teams[$team_id]['rotatype'] == "day") {
		$events = array_column(ko_rota_get_events(array_keys($all_teams), '', FALSE, TRUE), "startdatum");
		$where = "WHERE team_id = " . $team_id . " AND event_id IN('" . implode("','", $events) . "')";
	} else {
		$events = array_column(ko_rota_get_events(array_keys($all_teams), '', FALSE, TRUE), "id");
		$where = "WHERE team_id = " . $team_id . " AND event_id IN(" . implode(",", $events) . ")";
	}
	$db_schedules = db_select_data("ko_rota_schedulling", $where);
	$scheduled_N_times = 0;
	foreach($db_schedules AS $db_schedule) {
		$scheduled_persons = explode(",", $db_schedule['schedule']);
		if(in_array($schedule, $scheduled_persons)) {
			$scheduled_N_times++;
		}
	}

	if(is_numeric($schedule) || substr($schedule,0,1) == "g") {
		print '@@@rota_schedule_' . $team_id . '_' . $schedule . '_sum@@@' . $scheduled_N_times;
	} else {
		print '@@@rota_schedule_' . $team_id . '_free_' . md5($schedule) . '_sum@@@' . $scheduled_N_times;
	}

	if($all_teams[$team_id]['rotatype'] == "day") {
		$where = "WHERE team_id = " . $team_id . " AND event_id = '" . $event['startdatum'] . "'";
	} else {
		$where = "WHERE team_id = " . $team_id . " AND event_id = " . $event_id;
	}
	$teams_schedules = db_select_data("ko_rota_schedulling", $where, "schedule", "", "LIMIT 1", TRUE, TRUE);

	if(empty($teams_schedules['schedule'])) {
		print '@@@rota_schedule_event_' . $team_id . '_' . $event_id . '_sum@@@<a class="text-hidden text-hover-danger" href="javascript:sendReq(\'../rota/inc/ajax.php\', \'action,eventid,teamid,status,type,sesid\', \'seteventteamstatus,'.$event_id.','.$team_id.',1,planning,\'+kOOL.sid, do_element);update_team_status('.$event_id.','.$team_id.',1);" title="' . getLL('rota_status_e_t_close') . '"><i class="fa fa-ban"></i></a>';
	} else {
		print '@@@rota_schedule_event_' . $team_id . '_' . $event_id . '_sum@@@' . (trim($teams_schedules['schedule']) != '' ? sizeof(explode(",", $teams_schedules['schedule'])) : '');
	}

	if($all_teams[$team_id]['rotatype'] == "day") {
		$events = ko_rota_get_events(array_keys($all_teams), '', FALSE, TRUE);
		$where = "WHERE event_id IN ('" . implode('\',\'', array_column($events, "startdatum")) . "') AND team_id = '" . $team_id . "' AND schedule != ''";
	} else {
		$events = ko_rota_get_events(array_keys($all_teams), '', FALSE, TRUE);
		$where = "WHERE event_id IN ('" . implode('\',\'', array_column($events, "id")) . "') AND team_id = '" . $team_id . "' AND schedule != ''";
	}
	$scheduled_N_times = count(db_select_data("ko_rota_schedulling", $where));

	$all_events = ko_rota_get_events(array_keys($all_teams), '', TRUE, TRUE);
	foreach($all_events AS $key => $event) {
		if(!in_array($team_id,$event['teams']) ||
			ko_rota_is_scheduling_disabled($event['id'], $team_id)
		) {
			unset($all_events[$key]);
		}
	}
	print '@@@rota_schedule_team_sum_'.$team_id.'@@@' . $scheduled_N_times . ' / ' . count($all_events);
}



if((isset($_GET) && isset($_GET["action"])) || (isset($_POST) && isset($_POST["action"]))) {
	$action = format_userinput($_GET["action"], "alphanum");
	if (!$action) $action = format_userinput($_POST["action"], "alphanum");

	hook_ajax_pre($ko_menu_akt, $action);

 	switch($action) {

		case 'setsort':
			if($access['rota']['MAX'] < 5) break;

			$_SESSION['sort_rota_teams'] = format_userinput($_GET['sort'], 'alphanum+', TRUE, 30);
			$_SESSION['sort_rota_teams_order'] = format_userinput($_GET['sort_order'], 'alpha', TRUE, 4);

			print 'main_content@@@';
			ko_rota_list_teams();
			break;


		case 'settime':
		case 'timetoday':
		case 'timeplus':
		case 'timeminus':
			if ($action == 'settime') {
				$mul = 0;
				$date = format_userinput($_GET['date'], 'date');
				$_SESSION['rota_timestart'] = strtotime($date) > 0 ? $date : date('Y-m-d');
			} else if ($action == 'timetoday') {
				$mul = 0;
				$_SESSION['rota_timestart'] = date('Y-m-d');
			} else {
				$mul = $action == 'timeplus' ? 1 : -1;
			}

			switch (substr($_SESSION['rota_timespan'], -1)) {
				case 'd':
					$inc = substr($_SESSION['rota_timespan'], 0, -1);
					$new = add2date($_SESSION['rota_timestart'], 'day', $mul * $inc, TRUE);
					break;

				case 'w':
					$inc = substr($_SESSION['rota_timespan'], 0, -1);
					$new = add2date(date_find_last_monday($_SESSION['rota_timestart']), 'week', $mul * $inc, TRUE);
					break;

				case 'm':
					$inc = substr($_SESSION['rota_timespan'], 0, -1);
					$new = add2date(substr($_SESSION['rota_timestart'], 0, -2) . '01', 'month', $mul * $inc, TRUE);
					break;
			}

			$_SESSION['rota_timestart'] = $new;
			print 'main_content@@@';

			if ($_GET['type'] == "planning") {
				ko_rota_planning_list();
			} else {
				ko_rota_schedule();
			}
			break;


		case 'datefuture':
			ko_save_userpref($_SESSION['ses_userid'], 'rota_date_future', 1);

			print 'main_content@@@';

			if ($_GET['type'] == "planning") {
				ko_rota_planning_list();
			} else {
				ko_rota_schedule();
			}
			break;

		case 'datefutured':
			ko_save_userpref($_SESSION['ses_userid'], 'rota_date_future', 0);

			print 'main_content@@@';

			if ($_GET['type'] == "planning") {
				ko_rota_planning_list();
			} else {
				ko_rota_schedule();
			}
			break;


		case 'settimespan':
			$ts = format_userinput($_GET['timespan'], 'alphanum');
			if (!in_array($ts, $ROTA_TIMESPANS)) break;

			$_SESSION['rota_timespan'] = $_GET['timespan'];
			ko_save_userpref($_SESSION['ses_userid'], 'rota_timespan', $_SESSION['rota_timespan']);

			//Correct timestart
			switch (substr($_SESSION['rota_timespan'], -1)) {
				case 'w':
					$_SESSION['rota_timestart'] = date_find_last_monday($_SESSION['rota_timestart']);
					break;

				case 'm':
					$_SESSION['rota_timestart'] = substr($_SESSION['rota_timestart'], 0, -2) . '01';
					break;
			}

			print 'main_content@@@';

			if ($_GET['type'] == "planning") {
				ko_rota_planning_list();
			} else {
				ko_rota_schedule();
			}
			break;


		case 'seteventstatus':
			if($access['rota']['MAX'] < 5) break;

			$ids = [];
			if (strstr($_GET['id'], "-")) {
				list($year,$week_number) = explode("-", $_GET['id']);
				for($day=1; $day<=7; $day++) {
					$ids[] = date('Y-m-d', strtotime($year."W".$week_number.$day));
				}
			} else {
				$ids[] = format_userinput($_GET['id'], 'uint');
			}

			$status = format_userinput($_GET['status'], 'uint');
			if(!in_array($status, array(1,2))) break;

			foreach($ids AS $id) {
				if (empty($id)) continue;

				if (db_get_count('ko_rota_schedulling', 'event_id', "AND `event_id` = '$id'") > 0) {
					db_update_data('ko_rota_schedulling', "WHERE `event_id` = '$id'", ['status' => $status]);
				} else {
					db_insert_data('ko_rota_schedulling', ['event_id' => $id, 'status' => $status]);
				}

				//Log message
				ko_get_events($event, "AND ko_event.id = '$id'");
				$event = array_shift($event);
				ko_log('rota_event_status', 'Status: ' . $status . ': ' . $event['id'] . ': ' . $event['eventgruppen_name'] . ', ' . $event['startdatum'] . ', ' . $event['startzeit']);
			}

			print 'main_content@@@';
			ko_rota_schedule();
		break;



		case 'seteventteamstatus':
			if($access['rota']['MAX'] < 5) break;

			$teamId = format_userinput($_GET['teamid'], 'uint');
			$eventId = format_userinput($_GET['eventid'], 'js');
			$status = format_userinput($_GET['status'], 'uint');
			$type = format_userinput($_GET['type'], 'js');
			if(!in_array($status, array(0,1))) break;
			if(!$teamId || !$eventId) break;

			if($access['rota'][$teamId] < 5) break;

			if ($status == 1) {
				ko_rota_disable_scheduling($eventId, $teamId);
				//Delete all schedulings
				db_delete_data('ko_rota_schedulling', "WHERE `team_id` = '$teamId' AND `event_id` = '$eventId'");
			} else if ($status == 0) {
				ko_rota_enable_scheduling($eventId, $teamId);
			}

			$statusText = $status ? 'disabled' : 'enabled';
			ko_get_events($event, "AND ko_event.id = '{$eventId}'");
			$event = array_shift($event);
			$team = db_select_data('ko_rota_teams', "WHERE `id` = {$teamId}", '*', '', '', TRUE);
			ko_log('rota_event_team_status', "Status: {$statusText}, Event: (id: {$event['id']}, eg_name: {$event['eventgruppen_name']}, startdatum: {$event['startdatum']}, startzeit: {$event['startzeit']}), Team: (id: {$teamId}, name: {$team['name']}, rotatype: {$team['rotatype']})");

			if($type == "planning") {
				$all_teams = ko_rota_get_all_teams();
				$events = ko_rota_get_events(array_keys($all_teams), '', FALSE, TRUE);
				$where = "WHERE event_id IN ('".implode('\',\'', array_column($events,"id"))."') AND team_id = '" . $teamId ."' AND schedule != ''";
				$scheduled_N_times = count(db_select_data("ko_rota_schedulling", $where));

				$all_events = ko_rota_get_events(array_keys($all_teams), '', FALSE, TRUE);
				foreach($all_events AS $key => $event) {
					if(!in_array($teamId,$event['teams']) ||
						ko_rota_is_scheduling_disabled($event['id'], $teamId)
					) {
						unset($all_events[$key]);
					}
				}
				print 'rota_schedule_team_sum_'.$teamId.'@@@' . $scheduled_N_times . ' / ' . count($all_events);

				$team_members = ko_rota_get_team_members($teamId);
				$columns = "concat(team_id,'_',event_id) AS id, schedule";
				$db_schedules = db_select_data("ko_rota_schedulling", "WHERE schedule != ''", $columns);
				foreach($db_schedules AS $key =>$db_schedule) {
					$db_schedules[$key]['scheduled_items'] = explode(",", $db_schedule['schedule']);
				}

				if($access['rota']['MAX'] >= 5) {
					if ($status == 1) {
						print '@@@rota_schedule_event_' . $teamId . '_' . $eventId . '_sum@@@<a class="text-danger" href="javascript:sendReq(\'../rota/inc/ajax.php\', \'action,eventid,teamid,status,type,sesid\', \'seteventteamstatus,' . $eventId . ',' . $teamId . ',0,planning,\'+kOOL.sid, do_element);update_team_status(' . $eventId . ',' . $teamId . ',0);" title="' . getLL('rota_status_e_t_open') . '"><i class="fa fa-ban"></i></a>';
					} else {
						print '@@@rota_schedule_event_' . $teamId . '_' . $eventId . '_sum@@@<a class="text-hidden text-hover-danger" href="javascript:sendReq(\'../rota/inc/ajax.php\', \'action,eventid,teamid,status,type,sesid\', \'seteventteamstatus,' . $eventId . ',' . $teamId . ',1,planning,\'+kOOL.sid, do_element);update_team_status(' . $eventId . ',' . $teamId . ',1);" title="' . getLL('rota_status_e_t_close') . '"><i class="fa fa-ban"></i></a>';
					}
				}

				foreach($team_members['groups'] AS $member) {
					print '@@@rota_schedule_'.$eventId.'_'.$teamId.'_g' . $member['id'].'@@@'. ($status==0? '<i class="fa fa-circle-thin"></i>' : '');
				}
				foreach($team_members['people'] AS $member) {
					print '@@@rota_schedule_'.$eventId.'_'.$teamId.'_' . $member['id'].'@@@'. ($status==0? '<i class="fa fa-circle-thin"></i>' : '');
				}

				foreach($db_schedules AS $db_schedule) {
					foreach($db_schedule['scheduled_items'] AS $free_text) {
						if (!is_numeric($free_text) && substr($free_text, 0, 1) != "g") {
							$free_text_key = md5($free_text);
							print '@@@rota_schedule_' . $eventId . '_' . $teamId . '_free_' . $free_text_key . '@@@'. ($status==0? '<i class="fa fa-circle-thin"></i>' : '');
						}
					}
				}

			} else {
				$mode = FALSE === strpos($eventId, '-') ? 'event' : 'day';
				//Make sure, the function uses all teams if called from event form
				if($_GET['module'] == 'daten') $teams = array_keys(db_select_data('ko_rota_teams', 'WHERE 1'));
				else $teams = '';
				print 'rota_schedule_'.$eventId.'@@@'.ko_rota_get_schedulling_code($eventId, $mode, $teams);

				//Set new status
				if($mode == 'event') $event = ko_rota_get_events('', $eventId);
				else $event = ko_rota_get_days('', $eventId);
				if($event['_stats']['total'] == $event['_stats']['done']) {
					$class = 'success';
				} else if($event['_stats']['done'] == 0) {
					$class = 'danger';
				}

				print '<script>$(".selectpicker").selectpicker();</script>';
				print '@@@rota_stats_' . $event['id'] . '@@@<button class="btn btn-' . $class . '" disabled>' . $event['_stats']['done'] . '/' . $event['_stats']['total'] . '</button>';
			}
		break;



		case 'setalleventstatus':
			if($access['rota']['MAX'] < 5) break;

			$status = format_userinput($_GET['status'], 'uint');
			if(!in_array($status, array(1,2))) break;

			//Get all currently displayed events
			$ids = array();
			$events = ko_rota_get_events();
			foreach($events as $e) {
				if($e['id']) $ids[] = $e['id'];
			}
			if(sizeof($ids) > 0) {
				foreach($ids as $id) {
					if(db_get_count('ko_rota_schedulling', 'event_id', "AND `event_id` = '$id'") > 0) {
						db_update_data('ko_rota_schedulling', "WHERE `event_id` = '$id'", array('status' => $status));
					} else {
						db_insert_data('ko_rota_schedulling', array('event_id' => $id, 'status' => $status));
					}
				}
			}

			//Change days' status as well
			$day_ids = [];
			$days = ko_rota_get_days();
			foreach($days as $day) {
				if($day['id']) $day_ids[] = $day['id'];
			}
			if(sizeof($day_ids) > 0) db_update_data('ko_rota_schedulling', "WHERE `event_id` IN ('".implode("','", $day_ids)."')", array('status' => $status));

			ko_log('rota_event_status_all', 'Status: '.$status.': '.$_SESSION['rota_timestart'].' (+'.$_SESSION['rota_timespan'].') - Total '.sizeof($day_ids).' + '.sizeof($ids).': '.implode(', ', $day_ids).' - '.implode(', ', $ids));

			print 'main_content@@@';
			ko_rota_schedule();
		break;

		case 'setdaystatus':
			if($access['rota']['MAX'] < 5) break;

			$id = format_userinput($_GET['id'], 'int');
			$status = format_userinput($_GET['status'], 'uint');
			if(!in_array($status, array(1,2))) break;
			if(strlen($id) != 7) break;

			if(db_get_count('ko_rota_schedulling', 'event_id', "AND `event_id` = '$id'") > 0) {
				db_update_data('ko_rota_schedulling', "WHERE `event_id` = '$id'", array('status' => $status));
			} else {
				db_insert_data('ko_rota_schedulling', array('event_id' => $id, 'status' => $status));
			}

			ko_log('rota_day_status', 'Status: '.$status.': '.$id);

			print 'main_content@@@';
			ko_rota_schedule();
		break;

		case 'schedule':
			$team_id = format_userinput($_GET['teamid'], 'uint');
			if(!$team_id) break;
			if($access['rota']['ALL'] < 3 && $access['rota'][$team_id] < 3) break;

			$event_id = format_userinput($_GET['eventid'], 'int');
			$schedule = str_replace(',', '', format_userinput($_GET['schedule'], 'js'));

			//Get event and check for valid one
			if(FALSE === strpos($event_id, '-')) {
				$mode = 'event';
				$event = db_select_data('ko_event', "WHERE `id` = '$event_id'", '*', '', '', TRUE);
				if(!isset($event['id']) || $event['id'] != $event_id || $event['rota'] != 1) break;

				$team = db_select_data("ko_rota_teams", "WHERE id = '" . $team_id . "'", "*", "","LIMIT 1", TRUE, TRUE);
				if($team['rotatype'] == "day") {
					$event_id = $event['startdatum'];
				}

				$where = "WHERE `team_id` = '$team_id' AND `event_id` = '$event_id'";
				$current_schedule = db_select_data('ko_rota_schedulling', $where, '*', '', '', TRUE);
				if(isset($current_schedule['event_id']) && ($current_schedule['event_id'] != $event_id || $current_schedule['status'] != 1)) {
					continue;
				}

				//Get current schedule entry and append new value
				if(!is_array($current_schedule)) $current_schedule = db_select_data('ko_rota_schedulling', "WHERE `team_id` = '$team_id' AND `event_id` = '$event_id'", '*', '', '', TRUE);
				if(!isset($current_schedule['schedule'])) {
					db_insert_data('ko_rota_schedulling', array('team_id' => $team_id, 'event_id' => $event_id, 'schedule' => $schedule));
					$new_schedule = $schedule;
				} else {
					$new = implode(',', array_unique(array_merge(explode(',', $current_schedule['schedule']), array($schedule))));
					while(substr($new, 0, 1) == ',') $new = substr($new, 1);
					while(substr($new, -1) == ',') $new = substr($new, 0, -1);
					db_update_data('ko_rota_schedulling', "WHERE `team_id` = '$team_id' AND `event_id` = '$event_id'", array('schedule' => $new));
					$new_schedule = $new;
				}

				//Make sure, the function uses all teams if called from event form
				if($_GET['module'] == 'daten') $teams = array_keys(db_select_data('ko_rota_teams', 'WHERE 1'));
				else $teams = '';

				if($_GET['type'] == "planning") {
					ko_rota_print_planning_code($event['id'], $team_id, $schedule, $_GET['action']);
				} else {
					print 'rota_schedule_' . $event_id . '@@@' . ko_rota_get_schedulling_code($event_id, $mode, $teams);
				}
				//Set new status
				if($mode == 'event') $event = ko_rota_get_events('', $event_id);
				else $event = ko_rota_get_days('', $event_id);
				if($event['_stats']['total'] == $event['_stats']['done']) {
					$class = 'success';
				} else if($event['_stats']['done'] == 0) {
					$class = 'danger';
				} else {
					$class = 'warning';
				}

				$team = db_select_data('ko_rota_teams', "WHERE `id` = '$team_id'", '*', '', '', TRUE);
				ko_log('rota_schedule', $mode.($_GET['module'] == 'daten' ? ' (from event)' : '').': '.$event_id.($mode == 'event' ? (', '.$event['eventgruppen_name'].' '.$event['_date']) : '').', Team: '.$team['name'].' ('.$team_id.'), Schedule: '.$schedule.': '.implode(', ', ko_rota_schedulled_text($schedule, 4)));
				if($_GET['type'] !== "planning") {
					print '<script>$(".selectpicker").selectpicker();</script>';
					print '@@@rota_stats_' . $event['id'] . '@@@<button class="btn btn-' . $class . '" disabled>' . $event['_stats']['done'] . '/' . $event['_stats']['total'] . '</button>';
				}
			} else {
				$mode = 'day';
				$person_id = format_userinput($_GET['personid'], "text");

				if(!is_numeric($person_id)) {
					$person_id = decodeFreeTextName($person_id);
				}

				list($year, $week_number) = explode("-", $event_id);

				$week = [];
				$selected_days = explode("-",$_GET['schedule']);
				for ($day=1; $day<=7; $day++) {
					$event_id = date('Y-m-d', strtotime($year . "W" . $week_number . $day));
					$where = "WHERE `team_id` = '$team_id' AND `event_id` = '$event_id'";
					$current_schedule = db_select_data('ko_rota_schedulling', $where, '*', '', '', TRUE);
					if (isset($current_schedule['event_id']) && ($current_schedule['event_id'] != $event_id || $current_schedule['status'] != 1)) {
						continue;
					}

					if (in_array($day, $selected_days)) {

						if (!isset($current_schedule['schedule'])) {
							db_insert_data('ko_rota_schedulling', ['team_id' => $team_id, 'event_id' => $event_id, 'schedule' => $person_id]);
							$new_schedule = $person_id;
						} else {
							$new = implode(',', array_unique(array_merge(explode(',', $current_schedule['schedule']), [$person_id])));
							while (substr($new, 0, 1) == ',') $new = substr($new, 1);
							while (substr($new, -1) == ',') $new = substr($new, 0, -1);
							if($new == $current_schedule['schedule']) continue;

							db_update_data('ko_rota_schedulling', "WHERE `team_id` = '$team_id' AND `event_id` = '$event_id'", ['schedule' => $new]);
							$new_schedule = $new;
						}

						$team = db_select_data('ko_rota_teams', "WHERE `id` = '$team_id'", '*', '', '', TRUE);
						$log_message = "added: " . $mode.($_GET['module'] == 'daten' ? ' (from event)' : '').': '.$event_id . ' ';
						$log_message.= 'Team: '.$team['name'].' ('.$team_id.'), ';
						$log_message.= 'Schedule: '.implode(', ', ko_rota_schedulled_text($person_id, 4)) . ' ('.$person_id.')';
						ko_log('rota_schedule', $log_message);
					} else {
						// remove from schedule
						$new = [];
						foreach(explode(',', $current_schedule['schedule']) as $e) {
							if($e != $person_id && trim($e) != '') $new[] = $e;
						}

						$where = "WHERE `team_id` = '$team_id' AND `event_id` = '$event_id'";
						if(empty($new) && !empty($current_schedule['schedule'])) {
							db_delete_data("ko_rota_schedulling", $where);
						} else if(implode(",", $new) != $current_schedule['schedule'] ) {
							db_update_data('ko_rota_schedulling', $where, ['schedule' => implode(',', $new)]);
						} else {
							continue;
						}
						$new_schedule = $new;
						$team = db_select_data('ko_rota_teams', "WHERE `id` = '$team_id'", '*', '', '', TRUE);
						$log_message = "removed: " . $mode.($_GET['module'] == 'daten' ? ' (from event)' : '').': '.$event_id . ' ';
						$log_message.= 'Team: '.$team['name'].' ('.$team_id.'), ';
						$log_message.= 'Schedule: '.implode(', ', ko_rota_schedulled_text($person_id, 4)) . ' ('.$person_id.')';
						ko_log('rota_schedule', $log_message);
					}
				}

				$order = 'ORDER BY '.$_SESSION['sort_rota_teams'].' '.$_SESSION['sort_rota_teams_order'];
				$rota_teams = db_select_data('ko_rota_teams', "WHERE `id` IN (".implode(',', $_SESSION['rota_teams']).")", '*', $order);
				$show_days = FALSE;
				if($_SESSION['rota_timespan'] != '1d') {
					foreach($rota_teams as $team) {
						if($team['rotatype'] == 'day') $show_days = TRUE;
					}
				}

				$weeks = [];
				if($show_days) {
					$all_teams = db_select_data('ko_rota_teams', "WHERE rotatype='day'");
					$days = ko_rota_get_days($rota_teams, '', '',TRUE);
					foreach($days as $key => $day) {
						if($days[$key]['month'] == 12 && $days[$key]['num'] == 1) {
							$week_key = ($days[$key]['year']+1) . $days[$key]['num'];
						} else {
							$week_key = $days[$key]['year'] . $days[$key]['num'];
						}

						if(isset($weeks[$week_key]['days'])) {
							$weeks[$week_key]['days'][] = $days[$key];
							$weeks[$week_key]['rotastatus'] = 1;
						} else {
							$weeks[$week_key]['days'][1] = $days[$key];
							$weeks[$week_key]['teams'] = [];
						}

						if ($days[$key]['rotastatus'] == 2) {
							$weeks[$week_key]['rotastatus'] = 2;
						}

						$weeks[$week_key]['_stats']['done']+= count($day['schedule']);
						$weeks[$week_key]['teams'] = array_unique(array_merge($weeks[$week_key]['teams'], $day['teams']));

						$week_start = new DateTime();
						$week_start->setISODate($days[$key]['year'], $days[$key]['num']);
						$week_stop = clone $week_start;
						$week_stop->modify("+6 days");
						$weeks[$week_key]['label'] = $week_start->format('d.m.Y') . " - " . $week_stop->format('d.m.Y');
						$weeks[$week_key]['id'] = $days[$key]['year'] . "-" . $days[$key]['num'];
					}

					foreach($weeks AS $key => $week) {
						$active_days_in_week = 0;

						foreach($week['teams'] aS $team) {
							$active_days_in_week+= count(explode(",",$all_teams[$team]['days_range']));
						}

						$weeks[$key]['_stats']['total'] = $active_days_in_week;
						$weeks[$key]['schedulling_code'] = ko_rota_get_schedulling_code_days($week);
					}
				}

				$selected_week = $weeks[$year.$week_number];
				print 'rota_schedule_' . $year . '-' . $week_number . '@@@' . $selected_week['schedulling_code'];

				if($selected_week['_stats']['done'] >= $selected_week['_stats']['total']) {
					$class = 'success';
				} else if($selected_week['_stats']['done'] == 0) {
					$class = 'danger';
				} else {
					$class = 'warning';
				}

				if($_GET['type'] !== "planning") {
					print '@@@rota_stats_' . $selected_week['id'] . '@@@<button class="btn btn-' . $class . '" disabled>' . $selected_week['_stats']['done'] . '/' . $selected_week['_stats']['total'] . '</button>';
				}

				$events = ko_rota_get_events();
				$teams = $_SESSION['rota_teams'];
				foreach($events AS $event) {
					print '@@@rota_schedule_' . $event['id'] . '@@@' . ko_rota_get_schedulling_code($event['id'], "event", $teams);
				}

				print '<script>$(".selectpicker").selectpicker();</script>';
			}
		break;

		case 'delschedule':
			$team_id = format_userinput($_GET['teamid'], 'uint');
			if(!$team_id) break;
			if($access['rota']['ALL'] < 3 && $access['rota'][$team_id] < 3) break;

			$event_id = format_userinput($_GET['eventid'], 'int');
			$schedule = str_replace(',', '', format_userinput($_GET['schedule'], 'js'));

			//Get event and check for valid one
			if(FALSE === strpos($event_id, '-')) {
				//Event ID
				$mode = 'event';
				$event = db_select_data('ko_event', "WHERE `id` = '$event_id'", '*', '', '', TRUE);
				if(!isset($event['id']) || $event['id'] != $event_id || $event['rota'] != 1) break;

				$team = db_select_data("ko_rota_teams", "WHERE id = '" . $team_id . "'", "*", "","LIMIT 1", TRUE, TRUE);
				if($team['rotatype'] == "day") {
					$event_id = $event['startdatum'];
				}

			} else {
				$mode = 'day';
				$current_schedule = db_select_data('ko_rota_schedulling', "WHERE `team_id` = '$team_id' AND `event_id` = '$event_id'", '*', '', '', TRUE);
				if(isset($current_schedule['event_id'])) {  //Only check for status if this week has values in the db already
					if($current_schedule['event_id'] != $event_id || $current_schedule['status'] != 1) break;
				}
			}

			//Get current schedule entry and append new value
			if(!is_array($current_schedule)) $current_schedule = db_select_data('ko_rota_schedulling', "WHERE `team_id` = '$team_id' AND `event_id` = '$event_id'", '*', '', '', TRUE);
			$new = array();
			foreach(explode(',', $current_schedule['schedule']) as $e) {
				if($e != $schedule && trim($e) != '') $new[] = $e;
			}
			db_update_data('ko_rota_schedulling', "WHERE `team_id` = '$team_id' AND `event_id` = '$event_id'", array('schedule' => implode(',', $new)));

			//Make sure, the function uses all teams if called from event form
			if($_GET['module'] == 'daten') $teams = array_keys(db_select_data('ko_rota_teams', 'WHERE 1'));
			else $teams = '';

			if($_GET['type'] == "planning") {
				ko_rota_print_planning_code($event['id'], $team_id, $schedule, $_GET['action']);
			} else {
				print 'rota_schedule_'.$event_id.'@@@'.ko_rota_get_schedulling_code($event_id, $mode, $teams);
			}

			//Set new status
			if($mode == 'event') {
				$event = ko_rota_get_events('', $event_id);
			} else {
				$event = ko_rota_get_days('', $event_id);
			}

			if($event['_stats']['total'] == $event['_stats']['done']) {
				$class = 'success';
			} else if($event['_stats']['done'] == 0) {
				$class = 'danger';
			} else {
				$class = 'warning';
			}


			//Log message
			$team = db_select_data('ko_rota_teams', "WHERE `id` = '$team_id'", '*', '', '', TRUE);
			ko_log('rota_del_schedule', $mode.($_GET['module'] == 'daten' ? ' (from event)' : '').': '.$event_id.($mode == 'event' ? (', '.$event['eventgruppen_name'].' '.$event['_date']) : '').', Team: '.$team['name'].' ('.$team_id.'), Schedule: '.$schedule.': '.implode(', ', ko_rota_schedulled_text($schedule, 4)));
			if($_GET['type'] !== "planning") {
				print '<script>$(".selectpicker").selectpicker();</script>';
				print '@@@rota_stats_' . $event['id'] . '@@@<button class="btn btn-' . $class . '" disabled>' . $event['_stats']['done'] . '/' . $event['_stats']['total'] . '</button>';
			}
		break;




		case 'egdoubleselect':
			if($access['rota']['MAX'] < 5) break;

			//GET data
			$id = format_userinput($_GET['gid'], 'uint', FALSE, 11, array(), '-');
			$element = format_userinput($_GET['element'], 'text');

			$values = array();
			kota_ko_event_eventgruppen_id_dynselect($v, $d, 1);
			if($id == '-') {  //Back to index
				foreach($v as $vid => $_v) {
					$suffix = substr($vid, 0, 1) == 'i' ? '-->' : '';
					$values[] = $vid.','.$d[$vid].$suffix;
				}
			} else {  //Show event groups for the chosen calendar
				//Add up link
				$values[] = 'i-,'.str_replace(',', '', getLL('form_peopleselect_up'));
				foreach($v['i'.$id] as $gid => $g) {
					$values[] = $gid.','.$d[$gid];
				}
			}
			$value = implode('#', $values);

			print $element.'@@@'.$value;
		break;



		case 'itemlistteams':
			//ID and state of the clicked field
			$id = format_userinput($_GET['id'], 'js');
			if($access['rota']['ALL'] < 1 && $access['rota'][$id] < 1) break;
			$state = $_GET['state'] == 'true' ? 'checked' : '';

			if(substr($id,-3) == "_ro") {
				$session_key = "rota_teams_readonly";
				$id = substr($id,0,-3);
			} else {
				$session_key = "rota_teams";
			}

			if($state == 'checked') {  //Select it
				if(!in_array($id, $_SESSION[$session_key])) $_SESSION[$session_key][] = $id;
			} else {  //deselect it
				if(in_array($id, $_SESSION[$session_key])) $_SESSION[$session_key] = array_diff($_SESSION[$session_key], array($id));
			}

			//Check for valid teams and sort them
			$new = array();
			$all_teams = db_select_data('ko_rota_teams', 'WHERE 1', '*', 'ORDER BY name ASC');
			foreach($all_teams as $team) {
				if(in_array($team['id'], $_SESSION[$session_key])) $new[] = $team['id'];
			}
			$_SESSION[$session_key] = $new;
			foreach($_SESSION[$session_key] as $k => $v) if($v == '') unset($_SESSION[$session_key][$k]);

			//Save userpref
			ko_save_userpref($_SESSION['ses_userid'], $session_key, implode(',', $_SESSION[$session_key]));

			print 'main_content@@@';
			switch($_SESSION['show']) {
				case 'schedule':
					ko_rota_schedule();
					break;
				case 'planning':
					ko_rota_planning_list();
					break;
			}
		break;


		case 'itemlistsaveteams':
			//save new value
			if($_GET['name'] == '') break;
			foreach($_SESSION['rota_teams'] as $k => $v) if($v == '') unset($_SESSION['rota_teams'][$k]);
			$new_value = implode(',', $_SESSION['rota_teams']);

			foreach($_SESSION['rota_teams_readonly'] as $k => $v) if($v == '') unset($_SESSION['rota_teams_readonly'][$k]);
			if(!empty($_SESSION['rota_teams_readonly'])) {
				$new_value .= ",ro_" . implode(',ro_', $_SESSION['rota_teams_readonly']);
			}
			$user_id = ($access['rota']['MAX'] > 4 && $_GET['global'] == 'true') ? '-1' : $_SESSION['ses_userid'];
			$name = format_userinput($_GET['name'], 'js', FALSE, 0, array('allquotes'));
			ko_save_userpref($user_id, $name, $new_value, 'rota_itemset');

			ko_get_login($_SESSION['ses_userid'], $loggedIn);
			$nameForOthers = "{$name} ({$loggedIn['login']})";

			$logins = trim($_GET['logins']);
			if ($logins) {
				$logins = explode(',', $logins);
				foreach ($logins as $lid) {
					$lid = format_userinput($lid, 'uint');
					if (!$lid) continue;
					if ($lid == ko_get_root_id() || $lid == $_SESSION['ses_userid']) continue;

					$n = $nameForOthers;
					$c = 0;
					while (ko_get_userpref($lid, $n, 'rota_itemset')) {
						$c++;
						$n = "{$nameForOthers} - {$c}";
					}
					ko_save_userpref($lid, $n, $new_value, 'rota_itemset');
				}
			}

			print submenu_rota('itemlist_teams', 'open', 2);
		break;


		case 'itemlistopenteams':
			//save new value
			$name = format_userinput($_GET['name'], 'js', FALSE, 0, array(), '@');
			if($name == '') break;

			if($name == '_all_') {
				$all_teams = db_select_data('ko_rota_teams');
				$_SESSION['rota_teams'] = $_SESSION['rota_teams_readonly'] = [];
				foreach($all_teams AS $team_id => $team) {
					$_SESSION['rota_teams'][] = $team_id;
					if($team['rotatype'] == "day") {
						$_SESSION['rota_teams_readonly'][] = $team_id;
					}
				}
			} else if($name == '_none_') {
				$_SESSION['rota_teams'] = array();
				$_SESSION['rota_teams_readonly'] = array();
			} else {
				if(substr($name, 0, 3) == '@G@') $value = ko_get_userpref('-1', substr($name, 3), 'rota_itemset');
				else $value = ko_get_userpref($_SESSION['ses_userid'], $name, 'rota_itemset');

				$_SESSION['rota_teams'] = [];
				$_SESSION['rota_teams_readonly'] = [];
				foreach(explode(',', $value[0]['value']) AS $team) {
					if(substr($team,0,3) == "ro_") {
						$_SESSION['rota_teams_readonly'][] = substr($team,3);
					} else {
						$_SESSION['rota_teams'][] = $team;
					}
				}
			}
			foreach($_SESSION['rota_teams'] as $k => $v) if($v == '') unset($_SESSION['rota_teams'][$k]);
			ko_save_userpref($_SESSION['ses_userid'], 'rota_teams', implode(',', $_SESSION['rota_teams']));
			foreach($_SESSION['rota_teams_readonly'] as $k => $v) if($v == '') unset($_SESSION['rota_teams_readonly'][$k]);
			ko_save_userpref($_SESSION['ses_userid'], 'rota_teams_readonly', implode(',', $_SESSION['rota_teams_readonly']));

			print 'main_content@@@';
			switch($_SESSION['show']) {
				case 'schedule':
					ko_rota_schedule();
				break;
			}
			print '@@@';
			print submenu_rota('itemlist_teams', 'open', 2);
		break;


		case 'itemlistdeleteteams':
			//save new value
			$name = format_userinput($_GET['name'], 'js', FALSE, 0, array(), '@');
			if($name == '') break;

			if(substr($name, 0, 3) == '@G@') {
				if($access['rota']['MAX'] > 4) ko_delete_userpref('-1', substr($name, 3), 'rota_itemset');
			} else ko_delete_userpref($_SESSION['ses_userid'], $name, 'rota_itemset');

			print submenu_rota('itemlist_teams', 'open', 2);
		break;




		case 'itemlistegs':
		case 'itemlistgroup':
		case 'itemlisttaxonomy':
		case 'itemlistroom':
			//ID and state of the clicked field
			$id = format_userinput($_GET['id'], 'js');
			$state = $_GET['state'] == 'true' ? 'checked' : '';

			if($action == 'itemlistegs') {
				if($state == 'checked') {  //Select it
					if(!in_array($id, $_SESSION['rota_egs'])) $_SESSION['rota_egs'][] = $id;
				} else {  //deselect it
					if(in_array($id, $_SESSION['rota_egs'])) $_SESSION['rota_egs'] = array_diff($_SESSION['rota_egs'], array($id));
				}
			} else if($action == 'itemlistgroup') {
				$groups = db_select_data('ko_eventgruppen', "WHERE `calendar_id` = '$id'", '*', 'ORDER BY name ASC');
				foreach($groups as $gid => $group) {
					if(!$access['daten'][$gid]) continue;
					if($state == 'checked') {  //Select it
						if(!in_array($gid, $_SESSION['rota_egs'])) $_SESSION['rota_egs'][] = $gid;
					} else {  //Deselect it
						if(in_array($gid, $_SESSION['rota_egs'])) $_SESSION['rota_egs'] = array_diff($_SESSION['rota_egs'], array($gid));
					}
				}//foreach(groups)
			}
			else if($action == "itemlisttaxonomy") {
				$_SESSION["daten_taxonomy_filter"] = $id;
			}
			else if($action == "itemlistroom") {
				$_SESSION["daten_room_filter"] = $id;
			}

			//Get rid of invalid event group ids
			$all_egs = array_keys(db_select_data('ko_eventgruppen', 'WHERE 1', '*'));
			foreach($_SESSION['rota_egs'] as $k => $egid) {
				if(!in_array($egid, $all_egs)) {
					unset($_SESSION['rota_egs'][$k]);
				}
			}

			//Save userpref
			sort($_SESSION['rota_egs']);
			ko_save_userpref($_SESSION['ses_userid'], 'rota_egs', implode(',', $_SESSION['rota_egs']));
			ko_save_userpref($_SESSION["ses_userid"], "daten_taxonomy_filter", $_SESSION["daten_taxonomy_filter"]);
			ko_save_userpref($_SESSION["ses_userid"], "daten_room_filter", $_SESSION["daten_room_filter"]);

			print 'main_content@@@';
			switch($_SESSION['show']) {
				case 'schedule':
					ko_rota_schedule();
					break;
				case 'planning':
					ko_rota_planning_list();
					break;
			}

			if($action == 'itemlistgroup') {
				print '@@@'.submenu_rota('itemlist_eventgroups', 'open', 2);
			}
		break;


		case 'itemlisttogglegroup':
			//ID and state of the clicked field
			$id = format_userinput($_GET['id'], 'js');
			if(isset($_SESSION['daten_calendar_states'][$id])) {
				$_SESSION['daten_calendar_states'][$id] = $_SESSION['daten_calendar_states'][$id] ? 0 : 1;
			} else {
				$_SESSION['daten_calendar_states'][$id] = ($_GET['state'] == 1 ? 0 : 1);
			}
			
			//Don't redraw the submenu, as this is done in JS so the list doesn't scroll of the mouse's position
		break;


		case 'itemlistsaveegs':
			//save new value
			if($_GET['name'] == '') break;
			$name = format_userinput($_GET['name'], 'js', FALSE, 0, array('allquotes'));
			$new_value = implode(',', $_SESSION['rota_egs']);
			$user_id = ($access['daten']['MAX'] > 3 && $_GET['global'] == 'true') ? '-1' : $_SESSION['ses_userid'];
			ko_save_userpref($user_id, $name, $new_value, 'daten_itemset');

			$taxonomy_filter = $_SESSION['daten_taxonomy_filter'];
			if(!empty($taxonomy_filter)) {
				ko_save_userpref($user_id, $name, $taxonomy_filter, "daten_taxonomy_filter");
			}
			$room_filter = $_SESSION['daten_room_filter'];
			if(!empty($room_filter)) {
				ko_save_userpref($user_id, $name, $room_filter, "daten_room_filter");
			}
			
			print submenu_rota('itemlist_eventgroups', 'open', 2);
		break;


		case 'itemlistopenegs':
			//save new value
			$name = format_userinput($_GET['name'], 'js', FALSE, 0, array(), '@');
			if($name == '') break;

			if($name == '_all_') {
				ko_get_eventgruppen($grps);
				$_SESSION['rota_egs'] = array_keys($grps);
				$_SESSION["daten_taxonomy_filter"] = "";
				$_SESSION["daten_room_filter"] = "";
			} else if($name == '_none_') {
				$_SESSION['rota_egs'] = array();
				$_SESSION["daten_taxonomy_filter"] = "";
				$_SESSION["daten_room_filter"] = "";
			} else {
				if(substr($name, 0, 3) == '@G@') {
					$value = ko_get_userpref('-1', substr($name, 3), 'daten_itemset');
					$value_taxonomy = ko_get_userpref('-1', substr($name, 3), 'daten_taxonomy_filter');
					$value_room = ko_get_userpref('-1', substr($name, 3), 'daten_room_filter');
				}
				else {
					$value = ko_get_userpref($_SESSION['ses_userid'], $name, 'daten_itemset');
					$value_taxonomy = ko_get_userpref($_SESSION['ses_userid'], $name, 'daten_taxonomy_filter');
					$value_room = ko_get_userpref($_SESSION['ses_userid'], $name, 'daten_room_filter');
				}
				$_SESSION['rota_egs'] = explode(',', $value[0]['value']);
				$_SESSION["daten_taxonomy_filter"] = $value_taxonomy[0]["value"];
				$_SESSION["daten_room_filter"] = $value_room[0]["value"];
			}
			ko_save_userpref($_SESSION['ses_userid'], 'rota_egs', implode(',', $_SESSION['rota_egs']));
			ko_save_userpref($_SESSION["ses_userid"], "daten_taxonomy_filter", $_SESSION["daten_taxonomy_filter"]);
			ko_save_userpref($_SESSION["ses_userid"], "daten_room_filter", $_SESSION["daten_room_filter"]);

			print 'main_content@@@';
			switch($_SESSION['show']) {
				case 'schedule':
					ko_rota_schedule();
				break;
				case 'planning':
					ko_rota_planning_list();
				break;
			}
			print '@@@';
			print submenu_rota('itemlist_eventgroups', 'open', 2);
		break;


		case 'itemlistdeleteegs':
			//save new value
			$name = format_userinput($_GET['name'], 'js', FALSE, 0, array(), '@');
			if($name == '') break;

			if(substr($name, 0, 3) == '@G@') {
				if($access['daten']['MAX'] > 3) {
					ko_delete_userpref('-1', substr($name, 3), 'daten_itemset');
					ko_delete_userpref('-1', substr($name, 3), 'daten_taxonomy_filter');
					ko_delete_userpref('-1', substr($name, 3), 'daten_room_filter');
				}
			} else {
				ko_delete_userpref($_SESSION['ses_userid'], $name, 'daten_itemset');
				ko_delete_userpref($_SESSION['ses_userid'], $name, 'daten_taxonomy_filter');
				ko_delete_userpref($_SESSION['ses_userid'], $name, 'daten_room_filter');
			}

			print submenu_rota('itemlist_eventgroups', 'open', 2);
		break;




		case 'export':
			if($access['rota']['MAX'] < 2) break;
			$no_post = TRUE;

			$mode = format_userinput($_GET['mode'], 'alpha');
			switch($mode) {
				case 'event':
					$eventid = format_userinput($_GET['id'], 'uint');
					if(!$eventid) break;
					$filename = 'excel/'.ko_rota_export_event_xls($eventid);
					$filetype = 'event:'.$eventid;
				break;

				case 'eventlist':
					$filename = 'excel/'.ko_rota_export_events_xls($_SESSION['rota_timestart']);
					$filetype = 'all';
				break;

				case 'eventtable':
					$filename = 'excel/'.ko_rota_export_landscape_xls($_SESSION['rota_timestart'], 'events');
					$filetype = 'landscape';
				break;

				case 'weektable':
					$filename = 'excel/'.ko_rota_export_landscape_xls($_SESSION['rota_timestart'], 'weeks');
					$filetype = 'landscape';
				break;

				case 'pdftable':
					$filename = 'pdf/'.ko_rota_export_landscape_pdf();
					$filetype = 'landscape';
				break;

				case 'helperoverviewp':
				case 'helperoverviewl':
					$orientation = substr($mode, -1, 1) == 'l' ? 'landscape' : 'portrait';
					$filename = 'excel/'.ko_rota_export_helper_overview($orientation);
					$filetype = $orientation;
				break;

				default:
					//Call export method set by plugins
					$plugins = hook_get_by_type('rota');
					foreach($plugins as $plugin) {
						if(function_exists('my_rota_export_handler_'.$plugin.'_'.$mode)) {
							$filename = call_user_func_array('my_rota_export_handler_'.$plugin.'_'.$mode, array(&$filetype));
						}
					}
			}

			$send = $access['rota']['MAX'] > 3 ? '&send=rota' : '';

			if($filename && file_exists($BASE_PATH.'download/'.$filename)) print $BASE_URL.'download.php?action=file&file=download/'.$filename.$send.'&filetype='.$filetype;
		break;



		case 'storeinmylist':
			if(!ko_module_installed('leute')) break;

			$no_post = TRUE;

			$_SESSION['my_list'] = $_SESSION['rota_my_list'];
			ko_save_userpref($_SESSION['ses_userid'], 'leute_my_list', serialize($_SESSION['my_list']));

			print 'INFO@@@'.getLL('rota_stored_in_mylist');
		break;



		case 'eventmylist':
			if(!ko_module_installed('leute')) break;
			$no_post = TRUE;
			if (strstr($_GET['id'], "-")) {
				list($year, $week_number) = explode("-", format_userinput($_GET['id'], 'text'));
				$start_date = date('Y-m-d', strtotime($year."W".$week_number."1"));
				$end_date = date('Y-m-d', strtotime($year."W".$week_number."7"));
				$where = "WHERE event_id >= '" . $start_date . "' AND event_id <= '" . $end_date . "'";
				$events = ko_rota_get_days("", $start_date, $end_date);

				foreach($events AS $event) {
					foreach ($event['teams'] as $tid) {
						if (!$tid) continue;
						if (!in_array($tid, $_SESSION['rota_teams'])) continue;
						foreach (explode(',', $event['schedule'][$tid]) as $pid) {
							$pid = format_userinput($pid, 'uint');
							if (!$pid) continue;
							$_SESSION['my_list'][$pid] = $pid;
						}
					}
				}
			} else {
				$eventid = format_userinput($_GET['id'], 'uint');
				$event = ko_rota_get_events('', $eventid);
				foreach($event['teams'] as $tid) {
					if(!$tid) continue;
					foreach(explode(',', $event['schedule'][$tid]) as $pid) {
						$pid = format_userinput($pid, 'uint');
						if(!$pid) continue;
						$_SESSION['my_list'][$pid] = $pid;
					}
				}
			}

			$_SESSION['my_list'] = array_unique($_SESSION['my_list']);
			ko_save_userpref($_SESSION['ses_userid'], 'leute_my_list', serialize($_SESSION['my_list']));

			print 'INFO@@@'.getLL('rota_stored_in_mylist');
		break;



		case 'delpreset':
			$c = '';

			$id = format_userinput($_GET['id'], 'js');
			if(substr($id, 0, 7) != 'preset_') break;
			$id = substr($id, 7);
			$presets = array_merge((array)ko_get_userpref('-1', '', 'rota_emailtext_presets', 'ORDER by `key` ASC'), (array)ko_get_userpref($_SESSION['ses_userid'], '', 'rota_emailtext_presets', 'ORDER by `key` ASC'));

			$c .= '<option value="">'.getLL('download_send_insert_preset').'</option>';
			$c .= '<option value="" disabled="disabled">-------------------------</option>';
			foreach($presets as $preset) {
				$deleted = FALSE;
				if($preset['id'] == $id) {
					if($preset['user_id'] == $_SESSION['ses_userid'] || ($preset['user_id'] == '-1' && $access['rota']['MAX'] > 4)) {
						db_delete_data('ko_userprefs', "WHERE `id` = '".$preset['id']."'");
						$deleted = TRUE;
					}
				}
				if(!$deleted) {
					$prefix = $preset['user_id'] == -1 ? getLL('itemlist_global_short').' ' : '';
					$c .= '<option id="preset_'.$preset['id'].'" value="'.ko_js_escape($preset['value']).'">'.$prefix.$preset['key'].'</option>';
				}
			}

			print 'preset@@@'.$c;
		break;


		case 'savepreset':
			$text = $_GET['text'];
			$global = format_userinput($_GET['global'], 'uint');
			$name = format_userinput($_GET['name'], 'text');
			$uid = $global ? -1 : $_SESSION['ses_userid'];

			ko_save_userpref($uid, $name, $text, 'rota_emailtext_presets');

			$presets = array_merge((array)ko_get_userpref('-1', '', 'rota_emailtext_presets', 'ORDER by `key` ASC'), (array)ko_get_userpref($_SESSION['ses_userid'], '', 'rota_emailtext_presets', 'ORDER by `key` ASC'));

			$c .= '<option value="">'.getLL('download_send_insert_preset').'</option>';
			$c .= '<option value="" disabled="disabled">-------------------------</option>';
			foreach($presets as $preset) {
				$prefix = $preset['user_id'] == -1 ? getLL('itemlist_global_short').' ' : '';
				$c .= '<option id="preset_'.$preset['id'].'" value="'.ko_js_escape($preset['value']).'">'.$prefix.$preset['key'].'</option>';
			}

			print 'preset@@@'.$c.'@@@';

			print 'INFO@@@'.getLL('info_rota_5');
		break;


		case 'filesendpreviewrecs':
			array_walk_recursive($_POST, 'utf8_decode_array');
			ko_rota_filesend_parse_post('PREVIEW', $text, $subject, $recipients, $eventid, $restrict_to_teams, $from, $send_files);
			$mailOK = $mailNOK = array();
			foreach ($recipients as $r) {
				if ($r['_has_mail']) $mailOK[] = $r;
				else $mailNOK[] = $r;
			}
			$html = '';
			if (!empty($mailNOK)) {
				$np = array();
				$np_ids = array();
				foreach ($mailNOK as $p) {
					$np[] = '<div class="col-md-6">'.$p['vorname'] . ' ' . $p['nachname'].'</div>';
					$np_ids[] = $p['id'];
				}
				$html .= sprintf('<div class="panel panel-warning"><div class="panel-heading"><span style="float:right;">
<button type="button" class="btn btn-xs btn-primary" name="btn_rota_xls_download" value="xls download" id="btn_rota_xls_download">%s</button>
<button type="button" class="btn btn-xs btn-primary" name="btn_rota_add_to_mylist" value="add to my list" id="btn_rota_add_to_mylist">%s</button>
</span><h4 class="panel-title">%s</h4>
</div><div class="panel-body" style="max-height:400px;overflow-y:auto;"><div class="row">%s</div></div>
<input type="hidden" id="recipient_nok_ids" value="%s"> </div>', getLL('rota_xls_export'), getLL('rota_to_mylist'), getLL('rota_send_preview_mail_nok'), implode('', $np), implode(',',$np_ids));
			}
			if (!empty($mailOK)) {
				$np = array();
				foreach ($mailOK as $p) {
					$np[] = '<div class="col-md-6 rota-filesend-person-preview cursor_pointer" data-html="true" data-container="body" data-placement="auto" data-utd="false" data-toggle="tooltip" data-id="'.$p['id'].'" title="<i class=&quot;fa fa-spinner fa-pulse&quot;></i>">'.$p['vorname'] . ' ' . $p['nachname'].'</div>';
				}
				$html .= sprintf('<div class="panel panel-warning"><div class="panel-heading"><h4 class="panel-title">%s</h4></div><div class="panel-body" style="max-height:400px;overflow-y:auto;"><div class="row">%s</div></div></div>', getLL('rota_send_preview_mail_ok'), implode('', $np));
			}
			print $html;
		break;


		case 'filesendpreview':
			array_walk_recursive($_POST, 'utf8_decode_array');
			ko_rota_filesend_parse_post('PREVIEW', $text, $subject, $recipients, $eventid, $restrict_to_teams, $from, $send_files);
			$recipient = NULL;
			foreach ($recipients as $r) {
				if ($r['id'] == $_POST['recipient_id']) $recipient = $r;
			}
			if ($recipient && ($ok = ko_rota_filesend_send_mail('PREVIEW', $text, $subject, $recipient, $send_files, $eventid, $from, $restrict_to_teams))) {
				printf ("<b>%s&nbsp;</b>%s<br><b>%s&nbsp;</b>%s<br><b>%s</b><br>%s", getLL('leute_email_to'), $ok['email'], getLL('leute_email_subject'), $ok['emailSubject'], getLL('leute_email_text'), $ok['emailText']);
			};
		break;

		case 'nomailsxlsdownload':
			$nok_ids = $_GET['text'];
			$ids = explode(",", $nok_ids);
			foreach($ids AS $id) {
				if(!is_numeric($id)) return FALSE;
			}

			ko_get_leute($accounts, "AND id IN ( ". $nok_ids .")");
			if(empty($accounts)) return FALSE;

			$header = [
				"Id",
				getLL('kota_ko_leute_anrede'),
				getLL('kota_ko_leute_firm'),
				getLL('kota_ko_leute_vorname'),
				getLL('kota_ko_leute_nachname'),
				getLL('kota_ko_leute_adresse'),
				getLL('kota_ko_leute_plz'),
				getLL('kota_ko_leute_ort'),
			];

			$xls_data = array();
			$row = 0;
			foreach($accounts as $id => $a) {
				$xls_data[$row++] = [
					$a["id"],
					$a["anrede"],
					$a["firm"],
					$a["vorname"],
					$a["nachname"],
					$a["adresse"],
					$a["plz"],
					$a["ort"]
				];
			}

			// TODO: optimize pathmapping for function call
			$filename = "download/excel/dienstplan_no_mails_".strftime("%d%m%Y_%H%M%S", time()).".xlsx";
			$GLOBALS['ko_path'] = '../../';
			ko_export_to_xlsx($header, $xls_data, '../../'.$filename, getLL("donations_export_title"));
			$GLOBALS['ko_path'] = '../';
			print $BASE_URL.'download.php?action=file&file='.$filename;
			break;

		case 'rotaaddtomylist':
			$nok_ids = $_GET['text'];
			$ids = explode(",", $nok_ids);
			foreach($ids AS $id) {
				if(!is_numeric($id)) return FALSE;
			}
			ko_save_userpref($_SESSION['ses_userid'], 'leute_my_list', serialize($ids));
			$_SESSION['my_list'] = $ids;

			print 'INFO@@@'.getLL('rota_stored_in_mylist');
			break;

		case 'minigraph':
			$teamId = format_userinput($_GET['team'],'uint');
			$personId = format_userinput($_GET['person'],'uint');

			if(max($access['rota']['ALL'],$access['rota'][$teamId]) < 1) break;

			$chart = ko_rota_get_participation_chart($personId, $teamId, 'all');

			// chrome gets in trouble when svgs are not utf8
			header('content-type: image/svg+xml; charset=UTF-8');
			print $chart;
			exit;
		break;


		//Default:
		default:
			if(!hook_action_handler($do_action))
				include($ko_path.'inc/abuse.inc');
		break;

	}//switch(action);

	hook_ajax_post($ko_menu_akt, $action);
}
