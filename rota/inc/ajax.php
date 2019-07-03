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


//Set session id from GET (session will be started in ko.inc.php)
if(!isset($_GET['sesid'])) exit;
if(FALSE === session_id($_GET['sesid'])) exit;

//Send headers to ensure latin1 charset
header('Content-Type: text/html; charset=ISO-8859-1');
 
error_reporting(0);
$ko_menu_akt = 'rota';
$ko_path = '../../';
require_once($ko_path.'inc/ko.inc.php');
$ko_path = '../';

//Rechte auslesen
ko_get_access('daten');
ko_get_access('rota');
if($access['rota']['MAX'] < 1) exit;
 
ko_include_kota(array('ko_rota_teams'));

//Plugins
$hooks = hook_include_main('rota');
if(sizeof($hooks) > 0) foreach($hooks as $hook) include_once($hook);
 
require($BASE_PATH.'inc/smarty.inc.php');

require($BASE_PATH.'rota/inc/rota.inc.php');

$hooks = hook_include_sm();
if(sizeof($hooks) > 0) foreach($hooks as $hook) include($hook);

hook_show_case_pre($_SESSION['show']);

 
if(isset($_GET) && isset($_GET['action'])) {
 	$action = format_userinput($_GET['action'], 'alphanum');

	hook_ajax_pre($ko_menu_akt, $action);

 	switch($action) {

		case 'setsort':
			if($access['rota']['MAX'] < 5) continue;

			$_SESSION['sort_rota_teams'] = format_userinput($_GET['sort'], 'alphanum+', TRUE, 30);
			$_SESSION['sort_rota_teams_order'] = format_userinput($_GET['sort_order'], 'alpha', TRUE, 4);

			print 'main_content@@@';
			print ko_rota_list_teams();
		break;


		case 'settime':
		case 'timetoday':
		case 'timeplus':
		case 'timeminus':
			if($action == 'settime') {
				$mul = 0;
				$date = format_userinput($_GET['date'], 'date');
				$_SESSION['rota_timestart'] = strtotime($date) > 0 ? $date : date('Y-m-d');
			} else if($action == 'timetoday') {
				$mul = 0;
				$_SESSION['rota_timestart'] = date('Y-m-d');
			} else {
				$mul = $action == 'timeplus' ? 1 : -1;
			}

			switch($_SESSION['rota_timespan']) {
				case '1d':
					$new = add2date($_SESSION['rota_timestart'], 'day', $mul, TRUE);
				break;

				case '1w': case '2w':
					$inc = substr($_SESSION['rota_timespan'], 0, -1);
					$new = add2date(date_find_last_monday($_SESSION['rota_timestart']), 'week', $mul*$inc, TRUE);
				break;

				case '1m': case '2m': case '3m': case '6m': case '12m':
					$inc = substr($_SESSION['rota_timespan'], 0, -1);
					$new = add2date(substr($_SESSION['rota_timestart'], 0, -2).'01', 'month', $mul*$inc, TRUE);
				break;
			}

			$_SESSION['rota_timestart'] = $new;
			print 'main_content@@@'.ko_rota_schedule(FALSE);
		break;


		case 'datefuture':
			ko_save_userpref($_SESSION['ses_userid'], 'rota_date_future', 1);

			print 'main_content@@@'.ko_rota_schedule(FALSE);
		break;

		case 'datefutured':
			ko_save_userpref($_SESSION['ses_userid'], 'rota_date_future', 0);

			print 'main_content@@@'.ko_rota_schedule(FALSE);
		break;


		case 'settimespan':
			$ts = format_userinput($_GET['timespan'], 'alphanum');
			if(!in_array($ts, $ROTA_TIMESPANS)) break;

			$_SESSION['rota_timespan'] = $_GET['timespan'];
			ko_save_userpref($_SESSION['ses_userid'], 'rota_timespan', $_SESSION['rota_timespan']);

			//Correct timestart
			switch($_SESSION['rota_timespan']) {
				case '1w': case '2w':
					$_SESSION['rota_timestart'] = date_find_last_monday($_SESSION['rota_timestart']);
				break;

				case '1m': case '2m': case '3m': case '6m': case '12m':
					$_SESSION['rota_timestart'] = substr($_SESSION['rota_timestart'], 0, -2).'01';
				break;
			}

			print 'main_content@@@'.ko_rota_schedule(FALSE);
		break;


		
		case 'seteventstatus':
			if($access['rota']['MAX'] < 5) continue;

			$id = format_userinput($_GET['id'], 'uint');
			$status = format_userinput($_GET['status'], 'uint');
			if(!in_array($status, array(1,2))) continue;
			if($id <= 0) continue;

			if(db_get_count('ko_rota_schedulling', 'event_id', "AND `event_id` = '$id'") > 0) {
				db_update_data('ko_rota_schedulling', "WHERE `event_id` = '$id'", array('status' => $status));
			} else {
				db_insert_data('ko_rota_schedulling', array('event_id' => $id, 'status' => $status));
			}


			//Log message
			ko_get_events($event, "AND ko_event.id = '$id'");
			$event = array_shift($event);
			ko_log('rota_event_status', 'Status: '.$status.': '.$event['id'].': '.$event['eventgruppen_name'].', '.$event['startdatum'].', '.$event['startzeit']);

			print 'main_content@@@'.ko_rota_schedule(FALSE);
		break;



		case 'setalleventstatus':
			if($access['rota']['MAX'] < 5) continue;

			$status = format_userinput($_GET['status'], 'uint');
			if(!in_array($status, array(1,2))) continue;

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


			//Change weeks' status as well
			$wids = array();
			$weeks = ko_rota_get_weeks();
			foreach($weeks as $week) {
				if($week['id']) $wids[] = $week['id'];
			}
			if(sizeof($wids) > 0) db_update_data('ko_rota_schedulling', "WHERE `event_id` IN ('".implode("','", $wids)."')", array('status' => $status));


			//Log message
			ko_log('rota_event_status_all', 'Status: '.$status.': '.$_SESSION['rota_timestart'].' (+'.$_SESSION['rota_timespan'].') - Total '.sizeof($wids).' + '.sizeof($ids).': '.implode(', ', $eids).' - '.implode(', ', $ids));

			print 'main_content@@@'.ko_rota_schedule(FALSE);
		break;



		case 'setweekstatus':
			if($access['rota']['MAX'] < 5) continue;

			$id = format_userinput($_GET['id'], 'int');
			$status = format_userinput($_GET['status'], 'uint');
			if(!in_array($status, array(1,2))) continue;
			if(strlen($id) != 7) continue;

			if(db_get_count('ko_rota_schedulling', 'event_id', "AND `event_id` = '$id'") > 0) {
				db_update_data('ko_rota_schedulling', "WHERE `event_id` = '$id'", array('status' => $status));
			} else {
				db_insert_data('ko_rota_schedulling', array('event_id' => $id, 'status' => $status));
			}


			//Log message
			ko_log('rota_week_status', 'Status: '.$status.': '.$id);

			print 'main_content@@@'.ko_rota_schedule(FALSE);
		break;



		case 'schedule':
			$team_id = format_userinput($_GET['teamid'], 'uint');
			if(!$team_id) continue;
			if($access['rota']['ALL'] < 3 && $access['rota'][$team_id] < 3) continue;

			$event_id = format_userinput($_GET['eventid'], 'int');
			$schedule = str_replace(',', '', format_userinput($_GET['schedule'], 'js'));

			//Get event and check for valid one
			if(FALSE === strpos($event_id, '-')) {  //Event ID
				$mode = 'event';
				$event = db_select_data('ko_event', "WHERE `id` = '$event_id'", '*', '', '', TRUE);
				if(!isset($event['id']) || $event['id'] != $event_id || $event['rota'] != 1) continue;
			} else {  //Week ID
				$mode = 'week';
				$current_schedule = db_select_data('ko_rota_schedulling', "WHERE `team_id` = '$team_id' AND `event_id` = '$event_id'", '*', '', '', TRUE);
				if(isset($current_schedule['event_id'])) {  //Only check for status if this week has values in the db already
					if($current_schedule['event_id'] != $event_id || $current_schedule['status'] != 1) continue;
				}
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


			//Store week entry as event
			if(ko_get_setting('rota_export_weekly_teams') == 1 && $mode == 'week') {
				$team = db_select_data('ko_rota_teams', "WHERE `id` = '$team_id'", '*', '', '', TRUE);
				ko_rota_create_weekly_event($event_id, $team_id, $team['export_eg'], $new_schedule);
			}
		

			//Make sure, the function uses all teams if called from event form
			if($_GET['module'] == 'daten') $teams = array_keys(db_select_data('ko_rota_teams', 'WHERE 1'));
			else $teams = '';
			print 'rota_schedule_'.$event_id.'@@@'.ko_rota_get_schedulling_code($event_id, $mode, $teams);

			//Set new status
			if($mode == 'event') $event = ko_rota_get_events('', $event_id);
			else $event = ko_rota_get_weeks('', $event_id);
			if($event['_stats']['total'] == $event['_stats']['done']) {
				$class = 'rota-stats-done';
			} else if($event['_stats']['done'] == 0) {
				$class = 'rota-stats-empty';
			} else {
				$class = 'rota-stats-half';
			}


			//Log message
			$team = db_select_data('ko_rota_teams', "WHERE `id` = '$team_id'", '*', '', '', TRUE);
			ko_log('rota_schedule', $mode.($_GET['module'] == 'daten' ? ' (from event)' : '').': '.$event_id.($mode == 'event' ? (', '.$event['eventgruppen_name'].' '.$event['_date']) : '').', Team: '.$team['name'].' ('.$team_id.'), Schedule: '.$schedule.': '.implode(', ', ko_rota_schedulled_text($schedule)));

			print '@@@rota_stats_'.$event['id'].'@@@<div class="'.$class.'">'.$event['_stats']['done'].'/'.$event['_stats']['total'].'</div>';
		break;



		case 'delschedule':
			$team_id = format_userinput($_GET['teamid'], 'uint');
			if(!$team_id) continue;
			if($access['rota']['ALL'] < 3 && $access['rota'][$team_id] < 3) continue;

			$event_id = format_userinput($_GET['eventid'], 'int');
			$schedule = str_replace(',', '', format_userinput($_GET['schedule'], 'js'));

			//Get event and check for valid one
			if(FALSE === strpos($event_id, '-')) {  //Event ID
				$mode = 'event';
				$event = db_select_data('ko_event', "WHERE `id` = '$event_id'", '*', '', '', TRUE);
				if(!isset($event['id']) || $event['id'] != $event_id || $event['rota'] != 1) continue;
			} else {  //Week ID
				$mode = 'week';
				$current_schedule = db_select_data('ko_rota_schedulling', "WHERE `team_id` = '$team_id' AND `event_id` = '$event_id'", '*', '', '', TRUE);
				if(isset($current_schedule['event_id'])) {  //Only check for status if this week has values in the db already
					if($current_schedule['event_id'] != $event_id || $current_schedule['status'] != 1) continue;
				}
			}

			//Get current schedule entry and append new value
			if(!is_array($current_schedule)) $current_schedule = db_select_data('ko_rota_schedulling', "WHERE `team_id` = '$team_id' AND `event_id` = '$event_id'", '*', '', '', TRUE);
			$new = array();
			foreach(explode(',', $current_schedule['schedule']) as $e) {
				if($e != $schedule && trim($e) != '') $new[] = $e;
			}
			db_update_data('ko_rota_schedulling', "WHERE `team_id` = '$team_id' AND `event_id` = '$event_id'", array('schedule' => implode(',', $new)));


			//Store week entry as event
			if(ko_get_setting('rota_export_weekly_teams') == 1 && $mode == 'week') {
				$team = db_select_data('ko_rota_teams', "WHERE `id` = '$team_id'", '*', '', '', TRUE);
				ko_rota_create_weekly_event($event_id, $team_id, $team['export_eg'], implode(',', $new));
			}


			//Make sure, the function uses all teams if called from event form
			if($_GET['module'] == 'daten') $teams = array_keys(db_select_data('ko_rota_teams', 'WHERE 1'));
			else $teams = '';
			print 'rota_schedule_'.$event_id.'@@@'.ko_rota_get_schedulling_code($event_id, $mode, $teams);


			//Set new status
			if($mode == 'event') $event = ko_rota_get_events('', $event_id);
			else $event = ko_rota_get_weeks('', $event_id);
			if($event['_stats']['total'] == $event['_stats']['done']) {
				$class = 'rota-stats-done';
			} else if($event['_stats']['done'] == 0) {
				$class = 'rota-stats-empty';
			} else {
				$class = 'rota-stats-half';
			}


			//Log message
			$team = db_select_data('ko_rota_teams', "WHERE `id` = '$team_id'", '*', '', '', TRUE);
			ko_log('rota_del_schedule', $mode.($_GET['module'] == 'daten' ? ' (from event)' : '').': '.$event_id.($mode == 'event' ? (', '.$event['eventgruppen_name'].' '.$event['_date']) : '').', Team: '.$team['name'].' ('.$team_id.'), Schedule: '.$schedule.': '.implode(', ', ko_rota_schedulled_text($schedule)));

			print '@@@rota_stats_'.$event['id'].'@@@<div class="'.$class.'">'.$event['_stats']['done'].'/'.$event['_stats']['total'].'</div>';
		break;




		case 'egdoubleselect':
			if($access['rota']['MAX'] < 5) continue;

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
			if($access['rota']['ALL'] < 1 && $access['rota'][$id] < 1) continue;
			$state = $_GET['state'] == 'true' ? 'checked' : '';

			if($state == 'checked') {  //Select it
				if(!in_array($id, $_SESSION['rota_teams'])) $_SESSION['rota_teams'][] = $id;
			} else {  //deselect it
				if(in_array($id, $_SESSION['rota_teams'])) $_SESSION['rota_teams'] = array_diff($_SESSION['rota_teams'], array($id));
			}

			//Check for valid teams and sort them
			$new = array();
			$all_teams = db_select_data('ko_rota_teams', 'WHERE 1', '*', 'ORDER BY name ASC');
			foreach($all_teams as $team) {
				if(in_array($team['id'], $_SESSION['rota_teams'])) $new[] = $team['id'];
			}
			$_SESSION['rota_teams'] = $new;
			foreach($_SESSION['rota_teams'] as $k => $v) if($v == '') unset($_SESSION['rota_teams'][$k]);

			//Save userpref
			ko_save_userpref($_SESSION['ses_userid'], 'rota_teams', implode(',', $_SESSION['rota_teams']));

			print 'main_content@@@';
			switch($_SESSION['show']) {
				case 'schedule':
					print ko_rota_schedule(FALSE);
				break;
			}
		break;


		case 'itemlistsaveteams':
			//Find position of submenu for redraw
			if(in_array('itemlist_teams', explode(',', $_SESSION['submenu_left']))) $pos = 'left';
			else $pos = 'right';

			//save new value
			if($_GET['name'] == '') continue;
			foreach($_SESSION['rota_teams'] as $k => $v) if($v == '') unset($_SESSION['rota_teams'][$k]);
			$new_value = implode(',', $_SESSION['rota_teams']);
			$user_id = ($access['rota']['MAX'] > 4 && $_GET['global'] == 'true') ? '-1' : $_SESSION['ses_userid'];
			ko_save_userpref($user_id, format_userinput($_GET['name'], 'js', FALSE, 0, array('allquotes')), $new_value, 'rota_itemset');

			print submenu_rota('itemlist_teams', $pos, 'open', 2);
		break;


		case 'itemlistopenteams':
			//Find position of submenu for redraw
			if(in_array('itemlist_teams', explode(',', $_SESSION['submenu_left']))) $pos = 'left';
			else $pos = 'right';

			//save new value
			$name = format_userinput($_GET['name'], 'js', FALSE, 0, array(), '@');
			if($name == '') continue;

			if($name == '_all_') {
				$all_teams = db_select_data('ko_rota_teams');
				$_SESSION['rota_teams'] = array_keys($all_teams);
			} else if($name == '_none_') {
				$_SESSION['rota_teams'] = array();
			} else {
				if(substr($name, 0, 3) == '@G@') $value = ko_get_userpref('-1', substr($name, 3), 'rota_itemset');
				else $value = ko_get_userpref($_SESSION['ses_userid'], $name, 'rota_itemset');
				$_SESSION['rota_teams'] = explode(',', $value[0]['value']);
			}
			foreach($_SESSION['rota_teams'] as $k => $v) if($v == '') unset($_SESSION['rota_teams'][$k]);
			ko_save_userpref($_SESSION['ses_userid'], 'rota_teams', implode(',', $_SESSION['rota_teams']));

			print 'main_content@@@';
			switch($_SESSION['show']) {
				case 'schedule':
					print ko_rota_schedule(FALSE);
				break;
			}
			print '@@@';
			print submenu_rota('itemlist_teams', $pos, 'open', 2);
		break;


		case 'itemlistdeleteteams':
			//Find position of submenu for redraw
			if(in_array('itemlist_teams', explode(',', $_SESSION['submenu_left']))) $pos = 'left';
			else $pos = 'right';

			//save new value
			$name = format_userinput($_GET['name'], 'js', FALSE, 0, array(), '@');
			if($name == '') continue;

			if(substr($name, 0, 3) == '@G@') {
				if($access['rota']['MAX'] > 4) ko_delete_userpref('-1', substr($name, 3), 'rota_itemset');
			} else ko_delete_userpref($_SESSION['ses_userid'], $name, 'rota_itemset');

			print submenu_rota('itemlist_teams', $pos, 'open', 2);
		break;




		case 'itemlistegs':
		case 'itemlistgroup':
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

			print 'main_content@@@';
			switch($_SESSION['show']) {
				case 'schedule':
					print ko_rota_schedule(FALSE);
				break;
			}

			if($action == 'itemlistgroup') {
				if(in_array('itemlist_eventgroups', explode(',', $_SESSION['submenu_left']))) $pos = 'left';
				else $pos = 'right';
				print '@@@'.submenu_rota('itemlist_eventgroups', $pos, 'open', 2);
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
			//Find position of submenu for redraw
			if(in_array('itemlist_eventgroups', explode(',', $_SESSION['submenu_left']))) $pos = 'left';
			else $pos = 'right';

			//save new value
			if($_GET['name'] == '') continue;
			$new_value = implode(',', $_SESSION['rota_egs']);
			$user_id = ($access['daten']['MAX'] > 3 && $_GET['global'] == 'true') ? '-1' : $_SESSION['ses_userid'];
			ko_save_userpref($user_id, format_userinput($_GET['name'], 'js', FALSE, 0, array('allquotes')), $new_value, 'daten_itemset');

			print submenu_rota('itemlist_eventgroups', $pos, 'open', 2);
		break;


		case 'itemlistopenegs':
			//Find position of submenu for redraw
			if(in_array('itemlist_eventgroups', explode(',', $_SESSION['submenu_left']))) $pos = 'left';
			else $pos = 'right';

			//save new value
			$name = format_userinput($_GET['name'], 'js', FALSE, 0, array(), '@');
			if($name == '') continue;

			if($name == '_all_') {
				ko_get_eventgruppen($grps);
				$_SESSION['rota_egs'] = array_keys($grps);
			} else if($name == '_none_') {
				$_SESSION['rota_egs'] = array();
			} else {
				if(substr($name, 0, 3) == '@G@') $value = ko_get_userpref('-1', substr($name, 3), 'daten_itemset');
				else $value = ko_get_userpref($_SESSION['ses_userid'], $name, 'daten_itemset');
				$_SESSION['rota_egs'] = explode(',', $value[0]['value']);
			}
			ko_save_userpref($_SESSION['ses_userid'], 'rota_egs', implode(',', $_SESSION['rota_egs']));

			print 'main_content@@@';
			switch($_SESSION['show']) {
				case 'schedule':
					print ko_rota_schedule(FALSE);
				break;
			}
			print '@@@';
			print submenu_rota('itemlist_eventgroups', $pos, 'open', 2);
		break;


		case 'itemlistdeleteegs':
			//Find position of submenu for redraw
			if(in_array('itemlist_eventgroups', explode(',', $_SESSION['submenu_left']))) $pos = 'left';
			else $pos = 'right';

			//save new value
			$name = format_userinput($_GET['name'], 'js', FALSE, 0, array(), '@');
			if($name == '') continue;

			if(substr($name, 0, 3) == '@G@') {
				if($access['daten']['MAX'] > 3) ko_delete_userpref('-1', substr($name, 3), 'daten_itemset');
			} else ko_delete_userpref($_SESSION['ses_userid'], $name, 'daten_itemset');

			print submenu_rota('itemlist_eventgroups', $pos, 'open', 2);
		break;




		case 'export':
			if($access['rota']['MAX'] < 2) continue;
			$no_post = TRUE;

			$mode = format_userinput($_GET['mode'], 'alpha');
			switch($mode) {
				case 'event':
					$eventid = format_userinput($_GET['id'], 'uint');
					if(!$eventid) continue;
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
					$filename = 'pdf/'.ko_rota_export_landscape_pdf($_SESSION['rota_timestart']);
					$filetype = 'landscape';
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
			if(!ko_module_installed('leute')) continue;

			$no_post = TRUE;

			$_SESSION['my_list'] = $_SESSION['rota_my_list'];
			ko_save_userpref($_SESSION['ses_userid'], 'leute_my_list', serialize($_SESSION['my_list']));

			print 'INFO@@@'.getLL('rota_stored_in_mylist');
		break;



		case 'eventmylist':
			if(!ko_module_installed('leute')) continue;
			$no_post = TRUE;

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
			$_SESSION['my_list'] = array_unique($_SESSION['my_list']);
			ko_save_userpref($_SESSION['ses_userid'], 'leute_my_list', serialize($_SESSION['my_list']));

			print 'INFO@@@'.getLL('rota_stored_in_mylist');
		break;



		case 'delpreset':
			$c = '';

			$id = format_userinput($_GET['id'], 'js');
			if(substr($id, 0, 7) != 'preset_') continue;
			$id = substr($id, 7);
			$presets = array_merge((array)ko_get_userpref('-1', '', 'rota_emailtext_presets', 'ORDER by `key` ASC'), (array)ko_get_userpref($_SESSION['ses_userid'], '', 'rota_emailtext_presets', 'ORDER by `key` ASC'));

			$c .= '<select name="preset" size="0" id="preset" style="width: 680px; float: left;" onchange="textarea_insert_text(\'emailtext\', this.value);">';
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
			$c .= '</select>';

			print 'text_presets@@@'.$c;
		break;


		case 'savepreset':
			$text = $_GET['text'];
			$global = format_userinput($_GET['global'], 'uint');
			$name = format_userinput($_GET['name'], 'js');
			$uid = $global ? -1 : $_SESSION['ses_userid'];

			ko_save_userpref($uid, $name, $text, 'rota_emailtext_presets');
			print 'INFO@@@'.getLL('info_rota_5');
		break;

	}//switch(action);

	hook_ajax_post($ko_menu_akt, $action);

	if(!$no_post) print '@@@POST@@@rota_init_jsdate();';
}//if(GET[action])
?>
