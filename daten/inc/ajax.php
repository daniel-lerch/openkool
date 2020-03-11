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

if(!in_array($_GET['action'], array('jsongetevents', 'fcsetdate', 'pdfcalendar', 'fceditevent', 'fcdelevent'))) {
	//Set session id from GET (session will be started in ko.inc)
	if(!isset($_GET["sesid"]) && !isset($_POST["sesid"])) exit;
	if (isset($_GET["sesid"])) $sesid = $_GET["sesid"];
	if (!$sesid && isset($_POST["sesid"])) $sesid = $_POST["sesid"];
	if(FALSE === session_id($sesid)) exit;
}

//Send headers to ensure latin1 charset
header('Content-Type: text/html; charset=ISO-8859-1');

$ko_menu_akt = 'daten';
$ko_path = "../../";
require($ko_path."inc/ko.inc");
$ko_path = "../";

array_walk_recursive($_GET,'utf8_decode_array');
array_walk_recursive($_POST,'utf8_decode_array');

//Rechte auslesen
ko_get_access('daten');
if($access['daten']['MAX'] < 1) exit;
 
ko_include_kota(array('ko_event', 'ko_eventgruppen', 'ko_event_rooms'));

// Plugins einlesen:
$hooks = hook_include_main("daten");
if(sizeof($hooks) > 0) foreach($hooks as $hook) include_once($hook);
 
require($BASE_PATH."daten/inc/daten.inc");

//HOOK: Submenus einlesen
$hooks = hook_include_sm();
if(sizeof($hooks) > 0) foreach($hooks as $hook) include($hook);

hook_show_case_pre($_SESSION['show']);


if((isset($_GET) && isset($_GET["action"])) || (isset($_POST) && isset($_POST["action"]))) {
	$action = format_userinput($_GET["action"], "alphanum");
	if (!$action) $action = format_userinput($_POST["action"], "alphanum");

	hook_ajax_pre($ko_menu_akt, $action);

 	switch($action) {

		case 'pdfcalendar':
			if($_SESSION['cal_view'] == 'agendaDay') {
				$filename = ko_export_cal_weekly_view('daten', 1);
			}
			else if($_SESSION['cal_view'] == 'agendaWeek') {
				$filename = ko_export_cal_weekly_view('daten');
			}
			else if($_SESSION['cal_view'] == 'month') {
				$filename = basename(ko_daten_export_months(1, $_SESSION['cal_monat'], $_SESSION['cal_jahr']));
			}
			else break;

			print $BASE_URL.'download.php?action=file&file=download/pdf/'.$filename;
		break;

 

		case "setsortevent":
			$raw = format_userinput($_GET["sort"], "text", TRUE, 300);

			// dirty fix for reservation-sorting: remove escaping characters
			$_SESSION["sort_events"] = preg_replace("/(^|[^\\\\])\\\\'/", "$1'", $raw);
			$_SESSION["sort_events"] = preg_replace("/(^|[^\\\\])\\\\'/", "$1'", $_SESSION["sort_events"]);
			$_SESSION["sort_events_order"] = format_userinput($_GET["sort_order"], "alpha", TRUE, 4);

			$mode = $_SESSION["show"] == "list_events_mod" ? "mod" : "all";

			print "main_content@@@";
			print ko_list_events($mode);
		break;


		case "setsortrooms":
			$_SESSION['sort_rooms'] = format_userinput($_GET['sort'], 'alphanum+', TRUE, 30);
			ko_save_userpref($_SESSION['ses_userid'], 'sort_groups', $_SESSION['sort_groups']);
			$_SESSION['sort_rooms_order'] = format_userinput($_GET['sort_order'], 'alpha', TRUE, 4);
			ko_save_userpref($_SESSION['ses_userid'], 'sort_groups_order', $_SESSION['sort_groups_order']);

			print "main_content@@@";
			ko_list_rooms();
		break;

		case "setsorteventgroups":
			if($access['daten']['MAX'] < 3) break;

			$_SESSION["sort_tg"] = format_userinput($_GET["sort"], "alphanum+", TRUE, 30);
			$_SESSION["sort_tg_order"] = format_userinput($_GET["sort_order"], "alpha", TRUE, 4);

			print "main_content@@@";
			ko_list_groups();
		break;



		case 'fcsetdate':
			if($_GET['ymd']) {
				list($y, $m, $d) = explode('-', $_GET['ymd']);
				if($d) $_SESSION['cal_tag'] = intval($d);
				if($m) $_SESSION['cal_monat'] = intval($m);
				if($y) $_SESSION['cal_jahr'] = intval($y);
			}
			if($_GET['view']) $_SESSION['cal_view'] = format_userinput($_GET['view'], 'alpha');

			//Redraw mwselect box
			$sel = ko_calendar_mwselect();
			print 'mwselect@@@'.$sel;
		break;



		case "setstart":
			//Set list start
			if(isset($_GET['set_start'])) {
				$_SESSION['show_start'] = max(1, format_userinput($_GET['set_start'], 'uint'));
			}

			//Set list limit
			if(isset($_GET['set_limit'])) {
				$_SESSION['show_limit'] = max(1, format_userinput($_GET['set_limit'], 'uint'));
				ko_save_userpref($_SESSION['ses_userid'], 'show_limit_daten', $_SESSION['show_limit']);
			}

			print "main_content@@@";
			if($_SESSION["show"] == "all_events") {
				print ko_list_events("all");
			} else if($_SESSION["show"] == "all_groups") {
				ko_list_groups();
			} else if($_SESSION["show"] == "list_rooms") {
				ko_list_rooms();
			}
		break;



		case "itemlist":
		case 'itemlistRedraw':
		case "itemlistgroup":
		case 'itemlisttaxonomy':

			if($action == 'itemlistRedraw') {
				$action = 'itemlist';
				$redraw = TRUE;
			}
			//ID and state of the clicked field
			$id = format_userinput($_GET["id"], "js");
			$state = $_GET["state"] == "true" ? "checked" : "";

			//Single event group selected
			if($action == "itemlist") {
				if($state == "checked") {
					if (substr($id,0,7) == "absence") {
						$absence_id = substr($id,8);
						if (!in_array($absence_id, $_SESSION["show_absences"])) $_SESSION["show_absences"][] = $absence_id;
					} else if (substr($id,0,7) == "amtstag") {
						$amtstag_id = substr($id,8);
						if (!in_array($amtstag_id, $_SESSION["show_amtstage"])) $_SESSION["show_amtstage"][] = $amtstag_id;
					} else {
						if (!in_array($id, $_SESSION["show_tg"])) $_SESSION["show_tg"][] = $id;
					}
				} else {
					if (substr($id,0,7) == "absence") {
						$absence_id = substr($id,8);
						if (in_array($absence_id, $_SESSION["show_absences"])) $_SESSION["show_absences"] = array_diff($_SESSION["show_absences"], [$absence_id]);
					} else if (substr($id,0,7) == "amtstag") {
						$amtstag_id = substr($id,8);
						if (in_array($amtstag_id, $_SESSION["show_amtstage"])) $_SESSION["show_amtstage"] = array_diff($_SESSION["show_amtstage"], [$amtstag_id]);
					} else {
						if (in_array($id, $_SESSION["show_tg"])) $_SESSION["show_tg"] = array_diff($_SESSION["show_tg"], [$id]);
					}
				}
			}
			//Calendar selected or unselected
			else if($action == "itemlistgroup") {
				if ($id == "absence") {
					if ($access['daten']["ABSENCE"] > 1) {
						$absence_filters = array_merge((array)ko_get_userpref('-1', '', 'filterset', 'ORDER BY `key` ASC'), (array)ko_get_userpref($_SESSION['ses_userid'], '', 'filterset', 'ORDER BY `key` ASC'));

						foreach ($absence_filters AS $absence_filter) {
							if ($state == "checked") {
								if (!in_array($absence_filter['id'], $_SESSION["show_absences"])) $_SESSION["show_absences"][] = $absence_filter['id'];
							} else {
								if (in_array($absence_filter['id'], $_SESSION["show_absences"])) $_SESSION["show_absences"] = array_diff($_SESSION["show_absences"], [$absence_filter['id']]);
							}
						}
					}
				} else if ($id == "amtstag") {
					ko_get_access("rota");
					$allowed_team_ids = [];
					foreach($access['rota'] AS $team_id => $rights) {
						if(is_numeric($team_id) && $rights >= 1) {
							if ($state == "checked") {
								if (!in_array($team_id, $_SESSION["show_amtstage"])) $_SESSION["show_amtstage"][] = $team_id;
							} else {
								if (in_array($team_id, $_SESSION["show_amtstage"])) $_SESSION["show_amtstage"] = array_diff($_SESSION["show_amtstage"], [$team_id]);
							}
						}
					}
				} else {
					$groups = db_select_data("ko_eventgruppen", "WHERE `calendar_id` = '$id'", "*", "ORDER BY name ASC");
					foreach ($groups as $gid => $group) {
						if (!$access['daten'][$gid]) continue;
						if ($state == "checked") {  //Select it
							if (!in_array($gid, $_SESSION["show_tg"])) $_SESSION["show_tg"][] = $gid;
						} else {  //Deselect it
							if (in_array($gid, $_SESSION["show_tg"])) $_SESSION["show_tg"] = array_diff($_SESSION["show_tg"], [$gid]);
						}
					}//foreach(groups)
				}
			}
			else if($action == "itemlisttaxonomy") {
				$_SESSION["daten_taxonomy_filter"] = $id;
			}

			//Get rid of invalid event group ids
			$all_egs = db_select_data('ko_eventgruppen', 'WHERE 1', '*');
			foreach($_SESSION['show_tg'] as $k => $egid) {
				if(!in_array($egid, array_keys($all_egs))) {
					unset($_SESSION['show_tg'][$k]);
				}
			}

			//Save userpref
			sort($_SESSION["show_tg"]);
			ko_save_userpref($_SESSION["ses_userid"], "show_daten_tg", implode(",", $_SESSION["show_tg"]));
			ko_save_userpref($_SESSION["ses_userid"], "daten_taxonomy_filter", $_SESSION["daten_taxonomy_filter"]);
			ko_save_userpref($_SESSION["ses_userid"], "daten_absence_filter", implode(",",$_SESSION["show_absences"]));
			ko_save_userpref($_SESSION["ses_userid"], "daten_amtstage_filter", implode(",",$_SESSION["show_amtstage"]));

			//Redraw content for list and year view (month and week are done by fullCalendar)
			$done = FALSE;
			if($_SESSION['show'] == 'all_events') {
				print "main_content@@@";
				ko_list_events("all");
				$done = TRUE;
			} else if($_SESSION['show'] == 'ical_links') {
				print 'main_content@@@';
				ko_daten_ical_links();
				$done = TRUE;
			}

			//Find position of submenu for redraw
			if($action == "itemlistgroup" || $redraw) {
				if(in_array($_SESSION['show'], array('all_events', 'ical_link'))) print '@@@';
				print submenu_daten("itemlist_termingruppen", "open", 2);
				$done = TRUE;
			} else if($all_egs[$id]['calendar_id'] > 0) {
				//Update number of checked event groups for this calendar
				$calid = $all_egs[$id]['calendar_id'];
				$num = 0;
				foreach($all_egs as $eg) {
					if($_SESSION['show'] != 'calendar' && $eg['type'] != 0) continue;
					if($access['daten'][$eg['id']] < 1) continue;
					if($eg['calendar_id'] == $calid && in_array($eg['id'], $_SESSION['show_tg'])) $num++;
				}
				if($_SESSION['show'] != 'calendar') print '@@@';
				print 'calnum_'.$calid.'@@@'.$num;
				$done = TRUE;
			}

			//Refetch events
			if($_SESSION['show'] == 'calendar') {
				if($done) print '@@@';
				print "POST@@@$('#ko_calendar').fullCalendar('refetchEvents')";
			}
		break;


		case "itemlisttogglegroup":
			//ID and state of the clicked field
			$id = format_userinput($_GET["id"], "js");
			if(isset($_SESSION["daten_calendar_states"][$id])) {
				$_SESSION["daten_calendar_states"][$id] = $_SESSION["daten_calendar_states"][$id] ? 0 : 1;
			} else {
				$_SESSION["daten_calendar_states"][$id] = ($_GET["state"] == 1 ? 0 : 1);
			}
			
			//Don't redraw the submenu, as this is done in JS so the list doesn't scroll of the mouse's position
		break;


		case "itemlistsave":
			//save new value
			if($_GET["name"] == "") break;
			$new_value = implode(",", $_SESSION["show_tg"]);
			$user_id = $access['daten']['MAX'] > 3 && $_GET['global'] == 'true' ? '-1' : $_SESSION['ses_userid'];
			$name = format_userinput($_GET["name"], "js", FALSE, 0, array("allquotes"));
			ko_save_userpref($user_id, $name, $new_value, "daten_itemset");

			if(!empty($_SESSION['daten_taxonomy_filter'])) {
				ko_save_userpref($user_id, $name, $_SESSION['daten_taxonomy_filter'], "daten_taxonomy_filter");
			}
			if(!empty($_SESSION['show_absences'])) {
				ko_save_userpref($user_id, $name, implode(",", $_SESSION['show_absences']), "daten_absence_filter");
			}
			if(!empty($_SESSION['show_amtstage'])) {
				ko_save_userpref($user_id, $name, implode(",", $_SESSION['show_amtstage']), "daten_amtstage_filter");
			}

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
					while (ko_get_userpref($lid, $n, 'daten_itemset')) {
						$c++;
						$n = "{$nameForOthers} - {$c}";
					}
					ko_save_userpref($lid, $n, $new_value, "daten_itemset");
					if(!empty($_SESSION['daten_taxonomy_filter'])) {
						ko_save_userpref($lid, $n, $_SESSION['daten_taxonomy_filter'], "daten_taxonomy_filter");
					}
					if(!empty($_SESSION['show_absences'])) {
						ko_save_userpref($lid, $n, implode(",", $_SESSION['show_absences']), "daten_absence_filter");
					}
					if(!empty($_SESSION['show_amtstage'])) {
						ko_save_userpref($lid, $n, implode(",", $_SESSION['show_amtstage']), "daten_amtstage_filter");
					}
				}
			}

			print submenu_daten("itemlist_termingruppen", "open", 2);
		break;


		case "itemlistopen":
			//save new value
			$name = format_userinput($_GET['name'], 'js', FALSE, 0, array(), '@');
			if($name == "") break;

			if($name == '_all_') {
				ko_get_eventgruppen($grps);
				$_SESSION['show_tg'] = array_keys($grps);
				$_SESSION["daten_taxonomy_filter"] = "";
				$absence_filters = array_merge((array)ko_get_userpref('-1', '', 'filterset', 'ORDER BY `key` ASC'), (array)ko_get_userpref($_SESSION['ses_userid'], '', 'filterset', 'ORDER BY `key` ASC'));
				$_SESSION['show_absences'] = array_column($absence_filters, "id");
				$_SESSION["show_absences"][] = "all";
				$_SESSION["show_absences"][] = "own";
				$_SESSION["daten_amtstage_filter"] = [];
			} else if($name == '_none_') {
				$_SESSION['show_tg'] = [];
				$_SESSION["daten_taxonomy_filter"] = "";
				$_SESSION["show_absences"] = [];
				$_SESSION["show_amtstage"] = [];
			} else {
				if(substr($name, 0, 3) == '@G@') {
					$value = ko_get_userpref('-1', substr($name, 3), 'daten_itemset');
					$value_taxonomy = ko_get_userpref('-1', substr($name, 3), 'daten_taxonomy_filter');
					$value_absence = ko_get_userpref('-1', substr($name, 3), 'daten_absence_filter');
					$value_amtstage = ko_get_userpref('-1', substr($name, 3), 'daten_amtstage_filter');
				} else {
					$value = ko_get_userpref($_SESSION['ses_userid'], $name, 'daten_itemset');
					$value_taxonomy = ko_get_userpref($_SESSION['ses_userid'], $name, 'daten_taxonomy_filter');
					$value_absence = ko_get_userpref($_SESSION['ses_userid'], $name, 'daten_absence_filter');
					$value_amtstage = ko_get_userpref($_SESSION['ses_userid'], $name, 'daten_amtstage_filter');
				}

				$_SESSION["show_tg"] = array_filter(explode(",", $value[0]["value"]));
				$_SESSION["daten_taxonomy_filter"] = $value_taxonomy[0]["value"];
				$_SESSION["show_absences"] = array_filter(explode(",",$value_absence[0]["value"]));
				$_SESSION["show_amtstage"] = array_filter(explode(",",$value_amtstage[0]["value"]));
			}

			ko_save_userpref($_SESSION["ses_userid"], "show_daten_tg", implode(',', $_SESSION['show_tg']));
			ko_save_userpref($_SESSION["ses_userid"], "daten_taxonomy_filter", $_SESSION["daten_taxonomy_filter"]);
			ko_save_userpref($_SESSION["ses_userid"], "daten_absence_filter", implode(",", $_SESSION["show_absences"]));
			ko_save_userpref($_SESSION["ses_userid"], "daten_amtstage_filter", implode(",", $_SESSION["show_amtstage"]));

			print submenu_daten("itemlist_termingruppen", "open", 2);
			if($_SESSION['show'] == 'all_events') {
				print "@@@main_content@@@";
				ko_list_events("all");
			} else if($_SESSION['show'] == 'ical_links') {
				print '@@@main_content@@@';
				ko_daten_ical_links();
			}

			//Refetch events
			if($_SESSION['show'] == 'calendar') {
				print "@@@POST@@@$('#ko_calendar').fullCalendar('refetchEvents')";
			}
		break;


		case "itemlistdelete":
			//save new value
			$name = format_userinput($_GET['name'], 'js', FALSE, 0, array(), '@');
			if($name == "") break;

			if(substr($name, 0, 3) == '@G@') {
				if($access['daten']['MAX'] > 3) {
					ko_delete_userpref('-1', substr($name, 3), 'daten_itemset');
					ko_delete_userpref('-1', substr($name, 3), 'daten_taxonomy_filter');
					ko_delete_userpref('-1', substr($name, 3), 'daten_absence_filter');
					ko_delete_userpref('-1', substr($name, 3), 'daten_amtstage_filter');
				}
			} else {
				ko_delete_userpref($_SESSION['ses_userid'], $name, 'daten_itemset');
				ko_delete_userpref($_SESSION['ses_userid'], $name, 'daten_taxonomy_filter');
				ko_delete_userpref($_SESSION['ses_userid'], $name, 'daten_absence_filter');
				ko_delete_userpref($_SESSION['ses_userid'], $name, 'daten_amtstage_filter');
			}

			print submenu_daten("itemlist_termingruppen", "open", 2);
		break;



		case "calselect":
			if($access['daten']['MAX'] < 2) break;

			//GET data
			$id = format_userinput($_GET["cid"], "uint", FALSE, 11, array(), "-");
			$element = format_userinput($_GET["element"], "text");

			$values = array();
			kota_ko_event_eventgruppen_id_dynselect($v, $d, 2);
			if($id == "-") {  //Back to index
				foreach($v as $vid => $_v) {
					$suffix = substr($vid, 0, 1) == "i" ? "-->" : "";
					$values[] = $vid.",".str_replace(',', '&comma;', $d[$vid]).$suffix;
				}
			} else {  //Show event groups for the chosen calendar
				//Add up link
				$values[] = "i-,".str_replace(",", "", getLL("form_peopleselect_up"));
				foreach($v["i".$id] as $gid => $g) {
					$values[] = $gid.",".str_replace(',', '&comma;', $d[$gid]);
				}
			}
			$value = implode("#", $values);

			print "$element@@@$value";
		break;



		case 'jsongetevents':
			if (!is_array($access['reservation'])) ko_get_access('reservation');

			$data = [];
			$monthly_title = ko_get_userpref($_SESSION['ses_userid'], 'daten_monthly_title');

			if($_GET['type'] == "month") {
				$_SESSION['cal_monat'] = date("m", strtotime($_GET['start']));
				$_SESSION['cal_jahr'] = date("Y", strtotime($_GET['start']));
			}

			$start = strtotime('-10 days', strtotime(substr($_GET['start'], 0, 10).' 00:00:00'));
			$end = strtotime('+10 days', strtotime(substr($_GET['end'], 0, 10).' 23:59:59'));

			for($i=0; $i<2; $i++) {
				if($i == 1 && $access['daten']['MAX'] < 2) continue;

				//Get all events
				if($i==0) {
					apply_daten_filter($z_where, $z_limit, 'immer', 'immer');
					$z_where .= ' AND `enddatum` >= \''.strftime('%Y-%m-%d', $start).'\' AND `startdatum` <= \''.strftime('%Y-%m-%d', $end).'\'';
					ko_get_events($events, $z_where, '', 'ko_event', 'ORDER BY startdatum,startzeit,eventgruppen_name ASC');
				} else {
					$mod_where .= ' AND `enddatum` >= \''.strftime('%Y-%m-%d', $start).'\' AND `startdatum` <= \''.strftime('%Y-%m-%d', $end).'\'';
					if($_SESSION['ses_userid'] == ko_get_guest_id()) $mod_where .= ' AND 1=2 ';
					else $mod_where .= '';
					ko_get_events_mod($events, $mod_where);
				}

				if(sizeof($events) == 0) continue;
				if(ko_module_installed('reservation') && $access['reservation']['MAX'] > 0) {
					$tooltip_res = ko_get_userpref($_SESSION['ses_userid'], 'daten_show_res_in_tooltip');
				} else {
					$tooltip_res = 0;
				}
				foreach($events as $event) {
					//Only show allowed moderations
					if($i == 1) {
						if(ko_get_setting('daten_show_mod_to_all') == 1) {
							if($access['daten'][$event['eventgruppen_id']] < 1) continue;
						} else {
							if($access['daten'][$event['eventgruppen_id']] < 4 && $event['_user_id'] != $_SESSION['ses_userid']) continue;
						}
					}

					//Set title according to setting
					$fake_eg = array('name' => $event['eventgruppen_name']);
					$event_title = ko_daten_get_event_title($event, $fake_eg, $monthly_title);
					$title = $event_title['text'];

					//Format time for tooltip
					if($event['startzeit'] == '00:00:00' && $event['endzeit'] == '00:00:00') $time = getLL('time_all_day');
					else $time = substr($event['startzeit'], 0, -3).' - '.substr($event['endzeit'], 0, -3);

					$comment = $event['kommentar'] ? nl2br($event['kommentar']) : '';

					$mapping = ['room' => $event['room']];
					kota_process_data("ko_event", $mapping, "list");
					$room = $mapping['room'];

					$tooltip = '';

					//Moderated events
					if($i == 1) {
						if($event['_delete'] == 1 && $event['_event_id'] > 0) $mod_mode = 'delete';
						else if($event['_delete'] == 0 && $event['_event_id'] > 0) $mod_mode = 'edit';
						else $mod_mode = 'new';
						$tooltip .= '<b>'.getLL('daten_mod_open').': '.getLL('daten_mod_list_title_'.$mod_mode).'</b><br />';
						//Add user who entered the moderation request
						if($event['_user_id']) {
							ko_get_login($event['_user_id'], $login);
							if($login['leute_id']) {
								ko_get_person_by_id($login['leute_id'], $person);
								$tooltip .= getLL('by').': '.$person['vorname'].' '.$person['nachname'].' ('.$login['login'].')<br />';
							} else {
								$tooltip .= getLL('by').': '.$login['login'].'<br />';
							}
						}
						$tooltip .= '<br />';
					}

					//Tooltip-Text
					if($event['title']) {
						$ttTitle = $event['title'];
						if($_SESSION['ses_userid'] != ko_get_guest_id()) $ttTitle .= ' ('.$event['eventgruppen_name'].')';
					} else {
						$ttTitle = $event['eventgruppen_name'];
					}
					$tooltip .= '<h3 class="title">'.$ttTitle.'</h3>';
					/** @noinspection PhpUndefinedVariableInspection */
					$tooltip .= '<div class="datetime">'.strftime($DATETIME['DdmY'], strtotime($event['startdatum']));
					if($event['startdatum'] != $event['enddatum']) $tooltip .= ' - '.strftime($DATETIME['DdmY'], strtotime($event['enddatum']));
					$tooltip .= '<br /> '.getLL('kota_listview_ko_reservation_startzeit').': '.$time.'</div>';
					if($room) $tooltip .= '<div class="room">'.getLL('daten_location').': '.$room.'</div>';
					if($comment) $tooltip .= '<fieldset class="comment"><legend>' . getLL('daten_comment') . '</legend>'.$comment.'</fieldset>';

					$userFields = array_filter(explode(',', ko_get_userpref($_SESSION['ses_userid'], 'daten_tooltip_fields')), function($e){return $e?TRUE:FALSE;});
					if(sizeof($userFields) == 0) {
						/** @noinspection PhpUndefinedVariableInspection */
						$userFields = $EVENT_TOOLTIP_FIELDS;
					}
					if(!is_array($userFields)) $userFields = array();

					//Remove reservationen if no access
					if((!ko_module_installed('reservation') || $access['reservation']['MAX'] < 1) && in_array('reservationen', $userFields)) {
						foreach($userFields as $k => $v) {
							if($v == 'reservationen') unset($userFields[$k]);
						}
					}


					$teams = ko_rota_get_teams_for_eg($event['eventgruppen_id']);
					$view_only_teams = [];
					foreach($teams AS $team) {
						$view_only_teams[] = $team['id'];
					}

					$view_only_teams = array_unique($view_only_teams);

					$processed_event = $event;
					foreach($userFields as $dk) {
						if(!$dk) continue;

						if (substr($dk,0,9) == "rotateam_") {
							$rota_id = substr($dk,9);
							if (!in_array($rota_id, $view_only_teams)) continue;

							$where = "AND team_id = " . $rota_id ." AND event_id = " . $processed_event['id'];
							if (db_get_count("ko_rota_disable_scheduling","id",$where)) continue;
						}

						if(!isset($processed_event[$dk])) $processed_event[$dk] = '';
					}
					kota_process_data('ko_event', $processed_event, 'list', $_, $event['id']);
					if(is_array($userFields)) {
						foreach($userFields as $field) {
							if(!isset($processed_event[$field])) continue;
							if (substr($field, 0, 9) == 'rotateam_') {
								$d = getLL('kota_ko_event_rotateam_'.substr($field, 9));
							} else {
								$d = getLL('kota_ko_event_'.$field);
							}
							if(!$d) continue;
							$tooltip .= $processed_event[$field] ? '<br /><b>'.$d.'</b>: '.nl2br($processed_event[$field]) : '';
						}
					}

					//Add reservations in tooltip
					if($tooltip_res) {
						$ids = explode(',', $event['reservationen']);
						foreach($ids as $k => $v) {
							if(!$v) unset($ids[$k]);
						}
						$resDesc = [];
						if(sizeof($ids) > 0) {
							//Get reservation infos
							$res_columns = 'i.name AS resitem_name, r.startdatum, r.enddatum, r.startzeit, r.endzeit, r.zweck';
							$res = db_select_data('ko_reservation AS r LEFT JOIN ko_resitem AS i ON r.item_id = i.id', "WHERE r.id IN ('".implode("','", $ids)."')", $res_columns, '', '', FALSE, TRUE);
							$tooltip .= '<fieldset><legend class="res_title">'.getLL('res_list_title').'</legend><div class="event_res">';
							foreach($res as $r) {
								//Format time
								if($r['startzeit'] == '00:00:00' && $r['endzeit'] == '00:00:00') {
									$time = getLL('time_all_day');
								} else {
									$time = substr($r['startzeit'], 0, -3);
									if($r['endzeit'] != '00:00:00') $time .= ' - '.substr($r['endzeit'], 0, -3);
								}

								if($r['startdatum'] != $r['enddatum']) {
									$date = ko_date_format_timespan($r['startdatum'], $r['enddatum']);
								} else {
									$date = sql2datum($r['startdatum']);
								}

								$tooltip .= '- '.$r['resitem_name'].': ' . $date . ' ' .$time.'<br />';
								$resDesc[] = $r['resitem_name'].': '.$time;
							}
							$tooltip .= '</div></fieldset>';
						}

						if($tooltip_res == 2 && sizeof($resDesc) > 0) {
							$title = '<b>'.$title.'</b>'.'<p style="margin: 4px 0 0 0;">- '.implode('<br />- ', $resDesc).'</p>';
						}
					}

					//Add Groupsubscription info in tooltip
					if($event['gs_gid']) {
						$gid = format_userinput(ko_groups_decode($event['gs_gid'], 'group_id'), 'uint');
						$group = db_select_data('ko_groups', "WHERE `id` = '$gid'", '*', '', '', TRUE);
						if($group['id'] && $group['id'] == $gid) {
							$tooltip .= '<p class="res_title"><b>'.getLL('leute_groupsubscriptions_title').': </b>';
							$tooltip .= $group['count'];
							if($group['maxcount'] > 0) $tooltip .= ' / '.$group['maxcount'];
							if($group['count_role']) {
								$role = db_select_data('ko_grouproles', "WHERE `id` = '".$group['count_role']."'", '*', '', '', TRUE);
								if($role['id'] && $role['id'] == $group['count_role']) {
									$tooltip .= ' ('.$role['name'].')';
								}
							}
							$tooltip .= '</p>';

							//Find open subscriptions (ko_leute_mod)
							$gs_mod = db_select_data('ko_leute_mod', "WHERE `_group_id` LIKE 'g".$gid."%'", '_id');
							if(sizeof($gs_mod) > 0) {
								$tooltip .= '<b>'.getLL('fm_mod_open_group').':</b> '.sizeof($gs_mod);
							}

							$tooltip .= '<br />';
						}
					}

					//Add editIcons according to access rights
					if($i == 0) {
						if($event['import_id'] == '' && $access['daten'][$event['eventgruppen_id']] > 1) {
							$deleteIcon = '<button type="button" class="icon delete fcDeleteIcon" id="event'.$event['id'].'" title="'.getLL('daten_delete_event').'"><i class="fa fa-remove"></i></button>';
							$editIcons = $deleteIcon;
						} else {
							$editIcons = '';
						}
					}
					//Add links to approve or delete a moderation
					else {
						$checkIcon = $access['daten'][$event['eventgruppen_id']] > 3 ? '<button class="icon confirm" title="'.getLL('daten_mod_confirm').'" onclick="c1=confirm(\''.getLL('daten_mod_confirm_confirm').'\');if(!c1) return false; c = confirm(\''.getLL('daten_mod_confirm_confirm2').'\');set_hidden_value(\'mod_confirm\', c, this);set_action(\'daten_mod_'.$mod_mode.'_approve\', this);set_hidden_value(\'id\', \''.$event['id'].'\', this)"><i class="fa fa-check"></i></button>' : '';

						$deleteIcon = ($access['daten'][$event['eventgruppen_id']] > 3 || $event['_user_id'] == $_SESSION['ses_userid']) ? '<button class="icon deny" title="'.getLL('daten_mod_decline').'" onclick="c1=confirm(\''.getLL('daten_mod_decline_confirm').'\');if(!c1) return false;'.($access['daten'][$event['eventgruppen_id']] > 3 ? 'c = confirm(\''.getLL('daten_mod_decline_confirm2').'\');set_hidden_value(\'mod_confirm\', c, this);' : '').'set_action(\'daten_mod_delete\', this);set_hidden_value(\'id\', \''.$event['id'].'\', this);"><i class="fa fa-remove"></i></button>' : '';

						$editIcons = $checkIcon.($deleteIcon ? '&nbsp;'.$deleteIcon : '');
					}

					//Build data array for fullCalendar
					$allDay_ = $event['startzeit'] == '00:00:00' && $event['endzeit'] == '00:00:00';
					if ($allDay_) {
						$end_ = strftime('%Y-%m-%d', strtotime('+1 day', strtotime($event['enddatum']))).'T'.$event['endzeit'];
					} else {
						$end_ = $event['enddatum'].'T'.$event['endzeit'];
					}
					$data[] = array(
						'id' => $event['id'],
						'start' => $event['startdatum'].'T'.$event['startzeit'],
						'end' => $end_,
						'title' => utf8_encode($title),
						'allDay' => $allDay_,
						'editable' => ($i==0 && $event['import_id'] == '' && $access['daten'][$event['eventgruppen_id']] > 1) ? TRUE : FALSE,
						'className' => ($i==1 ? 'fc-modEvent' : ''),
						'isMod' => ($i==1),
						'color' => '#'.($event['eventgruppen_farbe'] ? $event['eventgruppen_farbe'] : 'aaaaaa'),
						'textColor' => ko_get_contrast_color($event['eventgruppen_farbe']),
						'kOOL_tooltip' => utf8_encode($tooltip),
						'kOOL_editIcons' => utf8_encode($editIcons),
					);
				}
			}

			if (!empty($_SESSION['show_absences'])) {
				$data = array_merge(ko_get_absences_for_calendar($_GET['start'], $_GET['end']), $data);
			}

			list($amtstageEvents, $amtstageEgs) = ko_get_amtstageevents_for_calendar($start, $end);
			if(!empty($amtstageEvents)) {
				$data = array_merge($amtstageEvents, $data);
			}

			//Show birthdays as allDay events
			if(ko_get_userpref($_SESSION['ses_userid'], 'show_birthdays') && ko_module_installed('leute')) {
				ko_get_access('leute');
				if($access['leute']['MAX'] > 0) {
					//Check for access to birthday column
					$columns = ko_get_leute_admin_spalten($_SESSION['ses_userid'], 'all');
					if(!is_array($columns['view']) || in_array('geburtsdatum', $columns['view'])) {

						$startmonth = (int)strftime('%m', $start);
						$endmonth = (int)strftime('%m', $end);
						$curmonth = (int)$_SESSION['cal_monat'];
						$curyear = (int)$_SESSION['cal_jahr'];

						$where = '';
						$bddate = strftime('%Y-%m-%d', $start);
						$i = 0;
						$bddates = array();
						while((int)str_replace('-', '', $bddate) < (int)str_replace('-', '', strftime('%Y-%m-%d', $end)) && $i < 100) {
							$bddates[] = $bddate;
							list($year, $month, $day) = explode('-', $bddate);
							$where .= " OR (MONTH(`geburtsdatum`) = '$month' AND DAY(`geburtsdatum`) = '$day') ";
							$bddate = add2date($bddate, 'day', 1, TRUE);
							$i++;
						}
						$where = 'WHERE ('.substr($where, 3).') ';

						//Allow plugins to add to the query
						$where .= ko_get_birthday_filter();

						//Get birthdays from db
						$bds = db_select_data('ko_leute', $where." AND `deleted` = '0' AND `hidden` = '0'", 'id, (YEAR(CURDATE())-YEAR(`geburtsdatum`)) AS `age`, geburtsdatum, vorname, nachname');

						foreach($bds as $bd) {
							$birthdays[strftime('%m%d', strtotime($bd['geburtsdatum']))][] = $bd;
						}
						foreach($birthdays as $day => $birthday) {
							//Calculate correct year for this birthday
							$year = $curyear;
							$thismonth = (int)substr($bd, 0, 2);
							if($thismonth == $startmonth) {
								if($startmonth > $curmonth) $year = $curyear-1;
							} else if($thismonth == $endmonth) {
								if($endmonth < $curmonth) $year = $curyear+1;
							}
							//Create tooltip with all birthdays on this day
							$tooltip = '';
							foreach($birthday as $b) {
								//Only add people the user has view access for
								if($access['leute']['ALL'] > 0 || $access['leute'][$b['id']] > 0) {
									$age = $year - (int)substr($b['geburtsdatum'], 0, 4);

									$tooltip .= $b['vorname'].' '.$b['nachname'].' ('.$age.')<br />';
								}
							}
							if($tooltip) {
								$tooltip = '<b>'.getLL('fm_birthdays_title').': '.strftime($DATETIME['dM'], mktime(1,1,1, substr($day, 0, 2), substr($day, 2, 2), $year)).'</b><br />'.$tooltip;
								$data[] = array('id' => 'bd'.$day,
									'start' => $year.'-'.substr($day, 0, 2).'-'.substr($day, 2, 2),
									'end' => $year.'-'.substr($day, 0, 2).'-'.substr($day, 2, 2),
									'allDay' => true,
									'title' => '<i class="fa fa-birthday-cake"></i>',
									'editable' => FALSE,
									'className' => 'fc-birthday',
									'color' => 'transparent',
									'textColor' => '#f48c41',
									'kOOL_tooltip' => utf8_encode($tooltip),
								);
							}
						}
					}
				}
			}//if(show_birthdays)

			hook_ajax_inline($ko_menu_akt, $action, $data);

			print json_encode($data);
		break;



		case 'fcdelevent':
			$id = format_userinput($_GET['id'], 'uint');
			if(!$id) break;
			do_del_termin($id);
		break;



		case 'fceditevent':
			$id = format_userinput($_GET['id'], 'uint');
			$mode = format_userinput($_GET['mode'], 'alpha');
			$allDay = format_userinput($_GET['allDay'], 'int');
			$secondDelta = format_userinput($_GET['secondDelta'], 'int');

			//Get current event entry
			ko_get_event_by_id($id, $event);
			ko_get_eventgruppe_by_id($event['eventgruppen_id'], $eg);

			//Check access
			if( ($eg['moderation'] == 0 && $access['daten'][$event['eventgruppen_id']] > 1) ||
					($eg['moderation'] > 0 && $access['daten'][$event['eventgruppen_id']] > 2)
				) {
				$new = $event;
				$new_res = array();
				//Get first reservation if any to get the res times
				if($event['reservationen']) {
					$current_res = db_select_data('ko_reservation', 'WHERE `id` IN (\''.implode("','", explode(',', $event['reservationen'])).'\')');
					$first_res = array_shift($current_res);
					//Add back to current_res as this array is used for double checks later on
					array_unshift($current_res, $first_res);
				}

				if($secondDelta != 0) {
					$wasAllDay = ($event['endzeit'] == $event['startzeit'] && $event['endzeit'] == '00:00:00');
					$interval = new DateInterval('PT'.abs($secondDelta).'S');
					if ($secondDelta < 0) $interval->invert = 1;

					$ev_stop = DateTime::createFromFormat('Y-m-d H:i:s', $event['enddatum'].' '.$event['endzeit']);
					$ev_start = DateTime::createFromFormat('Y-m-d H:i:s', $event['startdatum'].' '.$event['startzeit']);

					$secondDeltaEnd = $secondDelta;
					$intervalEnd = $interval;

					if ($mode == 'drop' && $wasAllDay && !$allDay) {
						$secondDeltaEnd = $secondDelta + 2 * 3600;
						$intervalEnd = new DateInterval('PT'.abs($secondDeltaEnd).'S');
						if ($secondDeltaEnd < 0) $intervalEnd->invert = 1;
					}

					if($mode == 'drop') {
						$ev_start = $ev_start->add($interval);
						$ev_stop = $ev_stop->add($intervalEnd);
					} else if ($mode == 'editEnd') {
						$ev_stop = $ev_stop->add($intervalEnd);
					}

					$new['enddatum'] = $ev_stop->format('Y-m-d');
					$new['startdatum'] = $ev_start->format('Y-m-d');
					$new['endzeit'] = $ev_stop->format('H:i:s');
					$new['startzeit'] = $ev_start->format('H:i:s');


					if ($event['reservationen'] && is_array($first_res)) {
						$res_stop = DateTime::createFromFormat('Y-m-d H:i:s', $first_res['enddatum'].' '.$first_res['endzeit']);
						$res_start = DateTime::createFromFormat('Y-m-d H:i:s', $first_res['startdatum'].' '.$first_res['startzeit']);

						if($mode == 'drop') {
							$res_start = $res_start->add($interval);
							$res_stop = $res_stop->add($intervalEnd);
						} else if ($mode == 'editEnd') {
							$res_stop = $res_stop->add($intervalEnd);
						}

						$new_res['enddatum'] = $res_stop->format('Y-m-d');
						$new_res['startdatum'] = $res_start->format('Y-m-d');
						$new_res['endzeit'] = $res_stop->format('H:i:s');
						$new_res['startzeit'] = $res_start->format('H:i:s');
					}
				}



				//Check for drop on allDay (minuteDelta and dayDelta 0)
				if($allDay) {
					$new['startzeit'] = $new['endzeit'] = $new_res['startzeit'] = $new_res['endzeit'] = '00:00';
				}

				// Set last_change
				$new['last_change'] = $new_res['last_change'] = date('Y-m-d H:i:s');
				$new['lastchange_user'] = $new_res['lastchange_user'] = $_SESSION['ses_userid'];

				//Update res
				$ok = TRUE;
				$double_error_txt = '';
				if($event['reservationen'] && sizeof($new_res) > 0) {
					require_once($BASE_PATH.'reservation/inc/reservation.inc');
					//Loop through all reservations to check for double entries after update
					foreach($current_res as $res) {
						//Apply new values for double check
						foreach($new_res as $k => $v) $res[$k] = $v;
						if(FALSE === ko_res_check_double($res['item_id'], $res['startdatum'], $res['enddatum'], $res['startzeit'], $res['endzeit'], $double_error_txt, $res['id'])) {
							$ok = FALSE;
						}
					}
					if($ok) {
						//Log-Meldung erstellen
						foreach (explode(',', $event['reservationen']) as $rId) {
							if (!$rId) continue;
							ko_log_diff("edit_res", $new_res, db_select_data('ko_reservation', "WHERE `id` = '{$rId}'", '*', '', '', TRUE));
						}

						db_update_data('ko_reservation', 'WHERE `id` IN (\''.implode("','", explode(',', $event['reservationen'])).'\')', $new_res);
					}
				}

				//Update event
				if($ok) {
					db_update_data('ko_event', 'WHERE `id` = \''.$id.'\'', $new);

					//Send notification
					ko_daten_send_notification($new, 'update', $event);

					//Log-Meldung erstellen
					ko_log_diff("edit_termin", array_merge(array("name" => $eventgroup["name"]), $new), $event);

					print TRUE;
				} else {
					print FALSE.'@@@error@@@'.'<b>'.getLL('res_collision').':</b> '.$double_error_txt;
				}
			} else {
				print FALSE;
			}
		break;

		case 'resconflictspreview':
			$ep = $_POST;

			// continue if can't find id of entry
			if (!$ep["koi"]["ko_event"]['eventgruppen_id']) break;
			reset($ep['koi']['ko_event']['eventgruppen_id']);
			$id = key($ep["koi"]["ko_event"]['eventgruppen_id']);

			// continue of no eg selected yet
			if (!$ep["koi"]["ko_event"]['eventgruppen_id'][$id]) break;

			// include required code and kota
			ko_include_kota(array('ko_reservation'));

			// read data from $_POST
			$data = $ep["koi"]["ko_event"];
			kota_process_data("ko_event", $data, "post");
			if (!is_array($access['reservation'])) ko_get_access('reservation');

			$data["resitems"] = format_userinput($ep["sel_do_res"], "intlist");
			foreach($EVENTS_SHOW_RES_FIELDS as $f) {
				if(in_array($f, array('startzeit', 'endzeit'))) {
					$data['res_'.$f] = $ep['res_'.$f] ? sql_zeit($ep['res_'.$f]) : $data[$f];
				} else {
					$data['res_'.$f] = $ep['res_'.$f];
				}
			}

			$allRes = array();
			if ($id == 0) { // new
				//Get repetition
				switch($ep["rd_wiederholung"]) {
					case "taeglich":     $inc = format_userinput($ep["txt_repeat_tag"], "uint"); break;
					case "woechentlich": $inc = format_userinput($ep["txt_repeat_woche"], "uint"); break;
					case "monatlich1":   $inc = format_userinput($ep["sel_monat1_nr"], "uint")."@".format_userinput($ep["sel_monat1_tag"], "uint"); break;
					case "monatlich2":   $inc = format_userinput($ep["txt_repeat_monat2"], "uint"); break;
					case "holidays":     $inc = format_userinput($ep["sel_repeat_holidays"], "alphanum+")."@".format_userinput($ep["sel_repeat_holidays_offset"], "alphanum+"); break;
					case "dates":     $inc = format_userinput($ep["sel_repeat_dates"], "alphanumlist"); break;
				}
				$startDatum = sql2datum($data["startdatum"]);
				$stopDatum = sql2datum($data["enddatum"]);
				ko_get_wiederholung($startDatum, $stopDatum, $ep["rd_wiederholung"], $inc,
					$ep["sel_bis_tag"], $ep["sel_bis_monat"], $ep["sel_bis_jahr"],
					$repeat, ($ep["txt_num_repeats"] ? $ep["txt_num_repeats"] : ""),
					format_userinput($ep['sel_repeat_eg'], 'uint'));

				//Loop through all repetitions
				for($i=0; $i<sizeof($repeat); $i+=2) {
					$data["startdatum"] = sql_datum($repeat[$i]);
					$data["enddatum"] = $repeat[$i+1] != "" ? sql_datum($repeat[$i+1]) : $data["startdatum"];

					$data['res_startdatum'] = date('Y-m-d', ($ep['res_startdatum_delta']*24*3600 + strtotime($data["startdatum"])));
					$data['res_enddatum'] = date('Y-m-d', ($ep['res_enddatum_delta']*24*3600 + strtotime($data["enddatum"])));

					$allRes[] = array(
						'id' => 0,
						'items' => explode(',', $data['resitems']),
						'startdatum' => $data['res_startdatum'],
						'enddatum' => $data['res_enddatum'],
						'startzeit' => $data["res_startzeit"],
						'endzeit' => $data["res_endzeit"],
					);
				}//for(i=0..sizeof(repeat))
			} else { // edit
				$data['res_startdatum'] = date('Y-m-d', ($_POST['res_startdatum_delta']*24*3600 + strtotime($data["startdatum"])));
				$data['res_enddatum'] = date('Y-m-d', ($_POST['res_enddatum_delta']*24*3600 + strtotime($data["enddatum"])));

				ko_get_event_by_id($id, $event);
				$oldResIds = explode(',', $event['reservationen']);
				$oldResIds = array_filter(array_map(function($e){return trim($e);}, $oldResIds), function($e){return $e?true:false;});
				$itemIdToRes = array();
				if (sizeof($oldResIds) > 0) {
					$oldRes = db_select_data('ko_reservation', "WHERE `id` IN (".implode(',', $oldResIds).")", 'item_id,id');
					foreach ($oldRes as $or) {
						$itemIdToRes[$or['item_id']] = $or['id'];
					}
				}

				foreach (explode(',', $data['resitems']) as $itemId) {
					$itemId = trim($itemId);
					if (!$itemId) continue;

					$resId = $itemIdToRes[$itemId];
					$resId = $resId?$resId:0;
					$allRes[] = array(
						'id' => $resId,
						'items' => array($itemId),
						'startdatum' => $data['res_startdatum'],
						'enddatum' => $data['res_enddatum'],
						'startzeit' => $data["res_startzeit"],
						'endzeit' => $data["res_endzeit"],
					);
				}
			}

			// get all conflicting reservations
			$conflicts = array();
			foreach ($allRes as $res) {
				foreach ($res['items'] as $itemId) {
					if (!$itemId) continue;
					if (FALSE === ko_res_check_double($itemId, $res['startdatum'], $res['enddatum'], $res['startzeit'], $res['endzeit'], $_double_error_txt, $res['id'], $cs)) {
						foreach ($cs as $c) {
							$conflicts[zerofill($c['item_id'], 8)."-{$c['startdatum']}-{$c['startzeit']}-{$c['id']}"] = $c;
						}
					}
				}
			}

			ksort($conflicts);
			$conflictHtml = '<table class="res-conflicts-table">'.implode('', array_map(function($e)use($access) {
				$editCode = kota_get_async_modal_code('ko_reservation', 'edit', $e['id'], 'primary', '<i class="fa fa-edit"></i>', 'res-edit-conflict-btn');

				$userId = $e['user_id'];
				$itemId = $e['item_id'];
				kota_process_data("ko_reservation", $e, "list");
				$descLineParts = array();
				if (trim($e['name'])) $descLineParts[] = getLL('kota_ko_reservation_name').': '.trim($e['name']);
				if (trim($e['zweck'])) $descLineParts[] = getLL('kota_ko_reservation_zweck').': '.trim($e['zweck']);
				$descLine = implode(', ', $descLineParts);

				$item = db_select_data('ko_resitem', "WHERE `id` = {$itemId}", '*', '', '', TRUE);

				$accessLvl = max($access['reservation']['ALL'], $access['reservation'][$itemId]);

				$btnHtml = '';
				if($accessLvl > 3 || ((($userId == $_SESSION['ses_userid'] && $_SESSION['ses_userid'] != ko_get_guest_id()) || ($item && $item['moderation'] == 0)) && $accessLvl > 2)) {
					$btnHtml = $editCode['html'];
				}
				$r = '<tr><td'.($descLine?' rowspan="2"':'').'>'.$btnHtml.'</td><td><b>'.$e['item_id'].'</b></td><td>'.$e['startdatum'].'</td><td>'.$e['startzeit'].'</td></tr>';
				if ($descLine) $r .= '<tr class="desc-line"><td colspan="3">'.$descLine.'</td></tr>';
				return $r;
			}, $conflicts)).'</table>'.(!$ep['id'] ? '<br><div class="checkbox"><label for="manual_moderation_for_conflicts"><input type="checkbox" id="manual_moderation_for_conflicts" name="manual_moderation_for_conflicts" value="1"> '.getLL('res_conflicts_create_manual_moderation').'</label></div>' : '');

			$return = array('nConflicts' => sizeof($conflicts), 'conflictHtml' => $conflictHtml);
			array_walk_recursive($return, 'utf8_encode_array');
			print json_encode($return);
		break;
	}//switch(action);

	hook_ajax_post($ko_menu_akt, $action);

}//if(GET[action])
?>
