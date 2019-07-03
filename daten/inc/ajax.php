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

if(!in_array($_GET['action'], array('jsongetevents', 'fcsetdate', 'pdfcalendar', 'fceditevent', 'fcdelevent'))) {
	//Set session id from GET (session will be started in ko.inc.php)
	if(!isset($_GET["sesid"])) exit;
	if(FALSE === session_id($_GET["sesid"])) exit;
}

//Send headers to ensure latin1 charset
header('Content-Type: text/html; charset=ISO-8859-1');
 
error_reporting(0);
$ko_menu_akt = 'daten';
$ko_path = "../../";
require($ko_path."inc/ko.inc.php");
$ko_path = "../";

//Rechte auslesen
ko_get_access('daten');
if($access['daten']['MAX'] < 1) exit;
 
ko_include_kota(array('ko_event', 'ko_eventgruppen'));

// Plugins einlesen:
$hooks = hook_include_main("daten");
if(sizeof($hooks) > 0) foreach($hooks as $hook) include_once($hook);
 
//Smarty-Templates-Engine laden
require($BASE_PATH."inc/smarty.inc.php");
 
require($BASE_PATH."daten/inc/daten.inc.php");

//HOOK: Submenus einlesen
$hooks = hook_include_sm();
if(sizeof($hooks) > 0) foreach($hooks as $hook) include($hook);

hook_show_case_pre($_SESSION['show']);
 
 
if(isset($_GET) && isset($_GET["action"])) {
 	$action = format_userinput($_GET["action"], "alphanum");

	hook_ajax_pre($ko_menu_akt, $action);

 	switch($action) {

		case 'pdfcalendar':
			if($_SESSION['show'] == 'cal_jahr') {
				$filename = basename(ko_daten_export_months(12, 1, $_SESSION['cal_jahr_jahr']));
			}
			else if($_SESSION['cal_view'] == 'agendaDay') {
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
			$_SESSION["sort_events"] = format_userinput($_GET["sort"], "alphanum+", TRUE, 30);
			$_SESSION["sort_events_order"] = format_userinput($_GET["sort_order"], "alpha", TRUE, 4);
			
			$mode = $_SESSION["show"] == "list_events_mod" ? "mod" : "all";

			print "main_content@@@";
			print ko_list_events($mode, FALSE);
		break;



		case "setsorteventgroups":
			if($access['daten']['MAX'] < 3) continue;

			$_SESSION["sort_tg"] = format_userinput($_GET["sort"], "alphanum+", TRUE, 30);
			$_SESSION["sort_tg_order"] = format_userinput($_GET["sort_order"], "alpha", TRUE, 4);

			print "main_content@@@";
			print ko_list_groups("all", FALSE);
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
			$sel = ko_calendar_mwselect($_SESSION['cal_view']);
			print 'mwselect@@@'.$sel;
		break;



		case "setjahr":
			$_SESSION["cal_jahr_jahr"] = format_userinput($_GET["set_year"], "uint", TRUE, 4);
			$_SESSION["cal_jahr_start"] = format_userinput($_GET["set_start"], "uint", TRUE, 2);
			$num = (int)ko_get_userpref($_SESSION["ses_userid"], "cal_jahr_num");
			if($num == 0) $num = 6;  //Default

			print "main_content@@@";
			print ko_daten_cal_jahr($num, $_SESSION["cal_jahr_start"], "html", FALSE);
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
				print ko_list_events("all", FALSE);
			} else if($_SESSION["show"] == "all_groups") {
				print ko_list_groups("all", FALSE);
			}
		break;



		case "itemlist":
		case 'itemlistRedraw':
		case "itemlistgroup":

			if($action == 'itemlistRedraw') {
				$action = 'itemlist';
				$redraw = TRUE;
			}
			//ID and state of the clicked field
			$id = format_userinput($_GET["id"], "js");
			$state = $_GET["state"] == "true" ? "checked" : "";

			//Single event group selected
			if($action == "itemlist") {
				if($state == "checked") {  //Select it
					if(!in_array($id, $_SESSION["show_tg"])) $_SESSION["show_tg"][] = $id;
				} else {  //deselect it
					if(in_array($id, $_SESSION["show_tg"])) $_SESSION["show_tg"] = array_diff($_SESSION["show_tg"], array($id));
				}
			}
			//Calendar selected or unselected
			else if($action == "itemlistgroup") {
				$groups = db_select_data("ko_eventgruppen", "WHERE `calendar_id` = '$id'", "*", "ORDER BY name ASC");
				foreach($groups as $gid => $group) {
					if(!$access['daten'][$gid]) continue;
					if($state == "checked") {  //Select it
						if(!in_array($gid, $_SESSION["show_tg"])) $_SESSION["show_tg"][] = $gid;
					} else {  //Deselect it
						if(in_array($gid, $_SESSION["show_tg"])) $_SESSION["show_tg"] = array_diff($_SESSION["show_tg"], array($gid));
					}
				}//foreach(groups)
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

			//Redraw content for list and year view (month and week are done by fullCalendar)
			if($_SESSION['show'] == 'all_events') {
				print "main_content@@@";
				ko_list_events("all", FALSE);
			} else if($_SESSION['show'] == 'cal_jahr') {
				print "main_content@@@";
				$num = (int)ko_get_userpref($_SESSION["ses_userid"], "cal_jahr_num");
				if($num == 0) $num = 6;
				ko_daten_cal_jahr($num, $_SESSION["cal_jahr_start"], FALSE);
			} else if($_SESSION['show'] == 'ical_links') {
				print 'main_content@@@';
				ko_daten_ical_links();
			}

			//Find position of submenu for redraw
			if($action == "itemlistgroup" || $redraw) {
				if(in_array("itemlist_termingruppen", explode(",", $_SESSION["submenu_left"]))) $pos = "left";
				else $pos = "right";
				if(in_array($_SESSION['show'], array('all_events', 'cal_jahr', 'ical_link'))) print '@@@';
				print submenu_daten("itemlist_termingruppen", $pos, "open", 2);
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
			}

			//Refetch events
			if($_SESSION['show'] == 'calendar') {
				print "@@@POST@@@$('#ko_calendar').fullCalendar('refetchEvents')";
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
			//Find position of submenu for redraw
			if(in_array("itemlist_termingruppen", explode(",", $_SESSION["submenu_left"]))) $pos = "left";
			else $pos = "right";

			//save new value
			if($_GET["name"] == "") continue;
			$new_value = implode(",", $_SESSION["show_tg"]);
			$user_id = $access['daten']['MAX'] > 3 && $_GET['global'] == 'true' ? '-1' : $_SESSION['ses_userid'];
			ko_save_userpref($user_id, format_userinput($_GET["name"], "js", FALSE, 0, array("allquotes")), $new_value, "daten_itemset");

			print submenu_daten("itemlist_termingruppen", $pos, "open", 2);
		break;


		case "itemlistopen":
			//Find position of submenu for redraw
			if(in_array("itemlist_termingruppen", explode(",", $_SESSION["submenu_left"]))) $pos = "left";
			else $pos = "right";

			//save new value
			$name = format_userinput($_GET['name'], 'js', FALSE, 0, array(), '@');
			if($name == "") continue;

			if($name == '_all_') {
				ko_get_eventgruppen($grps);
				$_SESSION['show_tg'] = array_keys($grps);
			} else if($name == '_none_') {
				$_SESSION['show_tg'] = array();
			} else {
				if(substr($name, 0, 3) == '@G@') $value = ko_get_userpref('-1', substr($name, 3), 'daten_itemset');
				else $value = ko_get_userpref($_SESSION['ses_userid'], $name, 'daten_itemset');
				$_SESSION["show_tg"] = explode(",", $value[0]["value"]);
			}
			ko_save_userpref($_SESSION["ses_userid"], "show_daten_tg", implode(',', $_SESSION['show_tg']));

			print submenu_daten("itemlist_termingruppen", $pos, "open", 2);
			if($_SESSION['show'] == 'all_events') {
				print "@@@main_content@@@";
				ko_list_events("all", FALSE);
			} else if($_SESSION['show'] == 'cal_jahr') {
				print "@@@main_content@@@";
				print ko_daten_cal_jahr($num, $_SESSION["cal_jahr_start"], "html", FALSE);
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
			//Find position of submenu for redraw
			if(in_array("itemlist_termingruppen", explode(",", $_SESSION["submenu_left"]))) $pos = "left";
			else $pos = "right";

			//save new value
			$name = format_userinput($_GET['name'], 'js', FALSE, 0, array(), '@');
			if($name == "") continue;

			if(substr($name, 0, 3) == '@G@') {
				if($access['daten']['MAX'] > 3) ko_delete_userpref('-1', substr($name, 3), 'daten_itemset');
			} else ko_delete_userpref($_SESSION['ses_userid'], $name, 'daten_itemset');

			print submenu_daten("itemlist_termingruppen", $pos, "open", 2);
		break;



		case "calselect":
			if($access['daten']['MAX'] < 2) continue;

			//GET data
			$id = format_userinput($_GET["cid"], "uint", FALSE, 11, array(), "-");
			$element = format_userinput($_GET["element"], "text");

			$values = array();
			kota_ko_event_eventgruppen_id_dynselect($v, $d, 2);
			if($id == "-") {  //Back to index
				foreach($v as $vid => $_v) {
					$suffix = substr($vid, 0, 1) == "i" ? "-->" : "";
					$values[] = $vid.",".$d[$vid].$suffix;
				}
			} else {  //Show event groups for the chosen calendar
				//Add up link
				$values[] = "i-,".str_replace(",", "", getLL("form_peopleselect_up"));
				foreach($v["i".$id] as $gid => $g) {
					$values[] = $gid.",".$d[$gid];
				}
			}
			$value = implode("#", $values);

			print "$element@@@$value";
		break;



		case 'jsongetevents':
			$data = array();
			$monthly_title = ko_get_userpref($_SESSION['ses_userid'], 'daten_monthly_title');
			$title_length = ko_get_userpref($_SESSION['ses_userid'], 'daten_title_length');

			for($i=0; $i<2; $i++) {
				if($i == 1 && $access['daten']['MAX'] < 2) continue;

				//Get all events
				if($i==0) {
					apply_daten_filter($z_where, $z_limit, 'immer', 'immer');
					$z_where .= ' AND `enddatum` >= \''.strftime('%Y-%m-%d', (int)$_GET['start']).'\' AND `startdatum` <= \''.strftime('%Y-%m-%d', (int)$_GET['end']).'\'';
					ko_get_events($events, $z_where, '', 'ko_event', 'ORDER BY startdatum,startzeit,eventgruppen_name ASC');
				} else {
					$mod_where .= ' AND `enddatum` >= \''.strftime('%Y-%m-%d', (int)$_GET['start']).'\' AND `startdatum` <= \''.strftime('%Y-%m-%d', (int)$_GET['end']).'\'';
					if($_SESSION['ses_userid'] == ko_get_guest_id()) $mod_where .= ' AND 1=2 ';
					else $mod_where .= '';
					ko_get_events_mod($events, $mod_where);
				}

				if(sizeof($events) == 0) continue;
				$tooltip_res = ko_get_userpref($_SESSION['ses_userid'], 'daten_show_res_in_tooltip');
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
					if(strlen($title) > $title_length) $title = substr($title, 0, $title_length).'..';

					//Format time for tooltip
					if($event['startzeit'] == '00:00:00' && $event['endzeit'] == '00:00:00') $time = getLL('time_all_day');
					else $time = substr($event['startzeit'], 0, -3).' - '.substr($event['endzeit'], 0, -3);

					$comment = $event['kommentar'] ? nl2br($event['kommentar']) : '';
					$room = $event['room'] ? $event['room'] : '';

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
								$tooltip .= utf8_encode(getLL('by')).': '.$person['vorname'].' '.$person['nachname'].' ('.$login['login'].')<br />';
							} else {
								$tooltip .= utf8_encode(getLL('by')).': '.$login['login'].'<br />';
							}
						}
						$tooltip .= '<br />';
					}

					//Tooltip-Text
					$tooltip .= '<p class="title">'.($event['title'] ? $event['title'].' ('.$event['eventgruppen_name'].')' : $event['eventgruppen_name']).'</p>';
					$tooltip .= '<p class="datetime">'.strftime($DATETIME['ddmy'], strtotime($event['startdatum']));
					if($event['startdatum'] != $event['enddatum']) $tooltip .= ' - '.strftime($DATETIME['ddmy'], strtotime($event['enddatum']));
					$tooltip .= '<br />'.getLL('kota_listview_ko_reservation_startzeit').': '.$time.'</p>';
					if($comment) $tooltip .= '<p class="comment">'.$comment.'</p>';
					if($room) $tooltip .= '<p class="room">'.getLL('daten_location').': '.$room.'</p>';
					//Add custom tooltip fields if defined
          if(is_array($EVENT_TOOLTIP_FIELDS)) {
            foreach($EVENT_TOOLTIP_FIELDS as $field) {
              if(!isset($event[$field])) continue;
              $tooltip .= $event[$field] ? '<br /><br /><b>'.getLL('kota_ko_event_'.$field).'</b>: '.nl2br($event[$field]) : '';
            }
          }

					//Add reservations in tooltip
					if($tooltip_res) {
						$ids = explode(',', $event['reservationen']);
						foreach($ids as $k => $v) {
							if(!$v) unset($ids[$k]);
						}
						if(sizeof($ids) > 0) {
							//Get reservation infos
							$res = db_select_data('ko_reservation AS r LEFT JOIN ko_resitem AS i ON r.item_id = i.id', "WHERE r.id IN ('".implode("','", $ids)."')", 'i.name AS resitem_name, r.startzeit, r.endzeit, r.zweck', '', '', FALSE, TRUE);
							$tooltip .= '<p class="res_title">'.getLL('res_list_title').':</p><div class="event_res">';
							foreach($res as $r) {
								//Format time
								if($r['startzeit'] == '00:00:00' && $r['endzeit'] == '00:00:00') {
									$time = getLL('time_all_day');
								} else {
									$time = substr($r['startzeit'], 0, -3);
									if($r['endzeit'] != '00:00:00') $time .= ' - '.substr($r['endzeit'], 0, -3);
								}
								$tooltip .= '- '.$r['resitem_name'].': '.$time.'<br />';
							}
							$tooltip .= '</div>';
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
							$gs_mod = db_select_data('ko_leute_mod', "WHERE `_group_id` LIKE 'g".$gid."%'");
							if(sizeof($gs_mod) > 0) {
								$tooltip .= '<b>'.getLL('fm_mod_open_group').':</b> '.sizeof($gs_mod);
							}

							$tooltip .= '<br />';
						}
					}

					//Add editIcons according to access rights
					if($i == 0) {
						if($event['import_id'] == '' && $access['daten'][$event['eventgruppen_id']] > 1) {
							$deleteIcon = '<input type="image" src="../images/icon_trash.png" id="event'.$event['id'].'" onclick="c=confirm(\''.utf8_encode(getLL('daten_delete_event_confirm')).'\');if(!c) return false; sendReq(\'inc/ajax.php\', \'action,id\', \'fcdelevent,'.$event['id'].'\'); $(\'#ko_calendar\').fullCalendar(\'removeEvents\', \''.$event['id'].'\'); tooltip.hide(); return false;" title="'.utf8_encode(getLL('daten_delete_event')).'" />';
							$editIcons = $deleteIcon;
						} else {
							$editIcons = '';
						}
					}
					//Add links to approve or delete a moderation
					else {
						$checkIcon = $access['daten'][$event['eventgruppen_id']] > 3 ? '<input type="image" src="../images/button_check.png" title="'.utf8_encode(getLL('daten_mod_confirm')).'" onclick="c1=confirm(\''.utf8_encode(getLL('daten_mod_confirm_confirm')).'\');if(!c1) return false; c = confirm(\''.utf8_encode(getLL('daten_mod_confirm_confirm2')).'\');set_hidden_value(\'mod_confirm\', c, this);set_action(\'daten_mod_'.$mod_mode.'_approve\', this);set_hidden_value(\'id\', \''.$event['id'].'\', this)" />' : '';

						$deleteIcon = ($access['daten'][$event['eventgruppen_id']] > 3 || $event['_user_id'] == $_SESSION['ses_userid']) ? '<input type="image" src="../images/button_delete.gif" title="'.utf8_encode(getLL('daten_mod_decline')).'" onclick="c1=confirm(\''.utf8_encode(getLL('daten_mod_decline_confirm')).'\');if(!c1) return false;'.($access['daten'][$event['eventgruppen_id']] > 3 ? 'c = confirm(\''.utf8_encode(getLL('daten_mod_decline_confirm2')).'\');set_hidden_value(\'mod_confirm\', c, this);' : '').'set_action(\'daten_mod_delete\', this);set_hidden_value(\'id\', \''.$event['id'].'\', this);" />' : '';

						$editIcons = $checkIcon.($deleteIcon ? '&nbsp;'.$deleteIcon : '');
					}

					//Build data array for fullCalendar
					$data[] = array('id' => $event['id'],
						'start' => $event['startdatum'].'T'.$event['startzeit'],
						'end' => $event['enddatum'].'T'.$event['endzeit'],
						'title' => utf8_encode($title),
						'allDay' => $event['startzeit'] == '00:00:00' && $event['endzeit'] == '00:00:00',
						'editable' => ($i==0 && $event['import_id'] == '' && $access['daten'][$event['eventgruppen_id']] > 1) ? TRUE : FALSE,
						'className' => ($i==1 ? 'fc-modEvent' : ''),
						'color' => '#'.($event['eventgruppen_farbe'] ? $event['eventgruppen_farbe'] : 'aaaaaa'),
						'textColor' => ko_get_contrast_color($event['eventgruppen_farbe']),
						'kOOL_tooltip' => utf8_encode($tooltip),
						'kOOL_editIcons' => $editIcons,
					);
				}
			}

			//Show birthdays as allDay events
			if(ko_get_userpref($_SESSION['ses_userid'], 'show_birthdays') && ko_module_installed('leute')) {
				ko_get_access('leute');
				if($access['leute']['MAX'] > 0) {
					//Check for access to birthday column
					$columns = ko_get_leute_admin_spalten($_SESSION['ses_userid']);
					if(!is_array($columns['view']) || in_array('geburtsdatum', $columns['view'])) {

						$startmonth = (int)strftime('%m', (int)$_GET['start']);
						$endmonth = (int)strftime('%m', (int)$_GET['end']);
						$curmonth = (int)$_SESSION['cal_monat'];
						$curyear = (int)$_SESSION['cal_jahr'];

						$where = '';
						$bddate = date('Y-m-d', $_GET['start']);
						$i = 0;
						$bddates = array();
						while((int)str_replace('-', '', $bddate) < (int)str_replace('-', '', date('Y-m-d', $_GET['end'])) && $i < 100) {
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
						$bds = db_select_data('ko_leute', $where, 'id, (YEAR(CURDATE())-YEAR(`geburtsdatum`)) AS `age`, geburtsdatum, vorname, nachname');

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
									'title' => '',
									'editable' => FALSE,
									'className' => 'fc-birthday',
									'color' => 'transparent',
									'textColor' => '#ffffff',
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
			if(!$id) continue;
			do_del_termin($id);
		break;



		case 'fceditevent':
			$id = format_userinput($_GET['id'], 'uint');
			$mode = format_userinput($_GET['mode'], 'alpha');
			$dayDelta = format_userinput($_GET['dayDelta'], 'int');
			$minuteDelta = format_userinput($_GET['minuteDelta'], 'int');

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

				//Adjust start and end date
				if($dayDelta != 0) {
					if($mode == 'drop') $new['startdatum'] = add2date($event['startdatum'], 'tag', $dayDelta, TRUE);
					$new['enddatum'] = add2date($event['enddatum'], 'tag', $dayDelta, TRUE);
					if($event['reservationen']) {
						$new_res['startdatum'] = $new['startdatum'];
						$new_res['enddatum'] = $new['enddatum'];
					}
				}
				//Adjust start and end time
				if($minuteDelta != 0) {
					if($mode == 'drop') {  //Drop changes start and end time
						$new['startzeit'] = add2time($event['startzeit'], $minuteDelta);
						//Adjust reservation time with the same delta
						if($event['reservationen']) $new_res['startzeit'] = add2time($first_res['startzeit'], $minuteDelta);
					}
					//Add 2h to events dropped from allDay to an hour, so start and end won't be the same after dropping
					$add = $event['startzeit'] == '00:00:00' && $event['endzeit'] == '00:00:00' ? 120 : 0;
					//Resize only changes end time
					$new['endzeit'] = add2time($event['endzeit'], $minuteDelta+$add);
					//Adjust reservation time with the same delta
					if($event['reservationen']) $new_res['endzeit'] = add2time($first_res['endzeit'], $minuteDelta+$add);
				}

				//Check for drop on allDay (minuteDelta and dayDelta 0)
				if($minuteDelta == 0 && $dayDelta == 0) {
					$new['startzeit'] = $new['endzeit'] = $new_res['startzeit'] = $new_res['endzeit'] = '00:00';
				}

				// Set last_change
				$new['last_change'] = $new_res['last_change'] = date('Y-m-d H:i:s');

				//Update res
				$ok = TRUE;
				$double_error_txt = '';
				if($event['reservationen'] && sizeof($new_res) > 0) {
					require_once($BASE_PATH.'reservation/inc/reservation.inc.php');
					//Loop through all reservations to check for double entries after update
					foreach($current_res as $res) {
						//Apply new values for double check
						foreach($new_res as $k => $v) $res[$k] = $v;
						if(FALSE === ko_res_check_double($res['item_id'], $res['startdatum'], $res['enddatum'], $res['startzeit'], $res['endzeit'], $double_error_txt, $res['id'])) {
							$ok = FALSE;
						}
					}
					if($ok) db_update_data('ko_reservation', 'WHERE `id` IN (\''.implode("','", explode(',', $event['reservationen'])).'\')', $new_res);
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
	}//switch(action);

	hook_ajax_post($ko_menu_akt, $action);

}//if(GET[action])
?>
