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

if(!in_array($_GET['action'], array('jsongetreservations', 'jsongetresitems', 'fcsetdate', 'pdfcalendar', 'fceditres', 'fcdelres'))) {
	//Set session id from GET (session will be started in ko.inc)
	if(!isset($_GET["sesid"]) && !isset($_POST["sesid"])) exit;
	if (isset($_GET["sesid"])) $sesid = $_GET["sesid"];
	if (!$sesid && isset($_POST["sesid"])) $sesid = $_POST["sesid"];
	if(FALSE === session_id($sesid)) exit;
}

//Send headers to ensure latin1 charset
header('Content-Type: text/html; charset=ISO-8859-1');

$ko_menu_akt = 'reservation';
$ko_path = "../../";
require($ko_path."inc/ko.inc");
$ko_path = "../";

array_walk_recursive($_GET,'utf8_decode_array');
array_walk_recursive($_POST,'utf8_decode_array');

//Rechte auslesen
ko_get_access('reservation');
if($access['reservation']['MAX'] < 1) exit;

// Include KOTA definitions
$kotaDefs = array('ko_reservation', 'ko_resitem', 'ko_reservation_mod');
if (ko_module_installed('daten')) $kotaDefs = array_merge($kotaDefs, array('ko_event', 'ko_eventgruppen'));
ko_include_kota($kotaDefs);

// Plugins einlesen:
$hooks = hook_include_main("reservation");
if(sizeof($hooks) > 0) foreach($hooks as $hook) include_once($hook);

require($BASE_PATH."reservation/inc/reservation.inc");

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
			if($_SESSION['ses_userid'] == ko_get_guest_id() && FALSE === strpos(ko_get_setting('res_allow_exports_for_guest'), 'pdf')) break;

			if($_SESSION['cal_view'] == 'agendaDay') {
				$filename = ko_export_cal_weekly_view('reservation', 1, '');
			}
			else if($_SESSION['cal_view'] == 'timelineDay') {
				$filename = ko_export_cal_weekly_view_resource(1, '');
			}
			else if($_SESSION['cal_view'] == 'agendaWeek') {
				$filename = ko_export_cal_weekly_view('reservation', '', '');
			}
			else if($_SESSION['cal_view'] == 'timelineWeek') {
				$filename = ko_export_cal_weekly_view_resource_2('', '');
			}
			else if(in_array($_SESSION['cal_view'], array('month', 'timelineMonth'))) {
				$filename = basename(ko_reservation_export_months(1, $_SESSION['cal_monat'], $_SESSION['cal_jahr']));
			}
			else break;

			print $BASE_URL.'download.php?action=file&file=download/pdf/'.$filename;
		break;



		case 'setsort':
			$_SESSION["sort_item"] = format_userinput($_GET["sort"], "alphanum+", TRUE, 30);
			$_SESSION["sort_item_order"] = format_userinput($_GET["sort_order"], "alpha", TRUE, 4);

			//Mode
			if($_SESSION["show"] == "show_mod_res") $mode = "mod";
			else $mode = "res";

			print "main_content@@@";
			ko_show_res_liste($mode);
		break;



		case "setsortresgroups":
			$_SESSION["sort_group"] = format_userinput($_GET["sort"], "alphanum+", TRUE, 30);
			$_SESSION["sort_group_order"] = format_userinput($_GET["sort_order"], "alpha", TRUE, 4);

			print "main_content@@@";
			ko_show_items_liste();
		break;


		case "setstart":
			//Set list start
			if(isset($_GET['set_start'])) {
				$_SESSION['show_start'] = max(1, format_userinput($_GET['set_start'], 'uint'));
	    }
			//Set list limit
			if(isset($_GET['set_limit'])) {
				$_SESSION['show_limit'] = max(1, format_userinput($_GET['set_limit'], 'uint'));
				ko_save_userpref($_SESSION['ses_userid'], 'show_limit_reservation', $_SESSION['show_limit']);
	    }

			print "main_content@@@";
			if($_SESSION["show"] == "liste") {
				ko_list_reservations();
			} else if($_SESSION["show"] == "show_mod_res") {
				ko_show_res_liste("mod");
			} else if($_SESSION["show"] == "list_items") {
				ko_show_items_liste();
			}
		break;


		case "itemlist":
		case "itemlistgroup":
			//ID and state of the clicked field
			$id = format_userinput($_GET["id"], "js");
			$state = $_GET["state"] == "true" ? "checked" : "";

			//A single res object was selected
			if($action == "itemlist") {
				if(($access['reservation'][$id] < 1 && substr($id,0,7) != "absence") || (substr($id,0,7) == "absence" && $access['daten']['ABSENCE'] < 1)) break;

				if($state == "checked") {
					if (substr($id,0,7) == "absence") {
						$absence_id = substr($id,8);
						if (!in_array($absence_id, $_SESSION["show_absences_res"])) $_SESSION["show_absences_res"][] = $absence_id;
					} else {
						if (!in_array($id, $_SESSION["show_items"])) $_SESSION["show_items"][] = $id;
					}
				} else {
					if (substr($id,0,7) == "absence") {
						$absence_id = substr($id,8);
						if (in_array($absence_id, $_SESSION["show_absences_res"])) $_SESSION["show_absences_res"] = array_diff($_SESSION["show_absences_res"], [$absence_id]);
					} else {
						if (in_array($id, $_SESSION["show_items"])) $_SESSION["show_items"] = array_diff($_SESSION["show_items"], [$id]);
					}
				}
			}
			//Resgroup selected or unselected
			else if($action == "itemlistgroup") {
				if($access['reservation']['grp'.$id] < 1 && $id != "absence") break;

				if ($id == "absence" && $access['daten']["ABSENCE"] > 1) {
					$absence_filters = array_merge((array)ko_get_userpref('-1', '', 'filterset', 'ORDER BY `key` ASC'), (array)ko_get_userpref($_SESSION['ses_userid'], '', 'filterset', 'ORDER BY `key` ASC'));
					foreach ($absence_filters AS $absence_filter) {
						if ($state == "checked") {
							if (!in_array($absence_filter['id'], $_SESSION["show_absences_res"])) $_SESSION["show_absences_res"][] = $absence_filter['id'];
						} else {
							if (in_array($absence_filter['id'], $_SESSION["show_absences_res"])) $_SESSION["show_absences_res"] = array_diff($_SESSION["show_absences_res"], [$absence_filter['id']]);
						}
					}
				} else {
					//Get all items for this group
					ko_get_resitems_by_group($id, $items);
					foreach ($items as $iid => $item) {
						if ($state == "checked") {  //Select it
							if (!in_array($iid, $_SESSION["show_items"])) $_SESSION["show_items"][] = $iid;
						} else {  //Deselect it
							if (in_array($iid, $_SESSION["show_items"])) $_SESSION["show_items"] = array_diff($_SESSION["show_items"], [$iid]);
						}
					}//foreach(items)
				}
			}//itemlistgroup

			//Get rid of invalid resitems
			ko_get_resitems($all_items);
			foreach($_SESSION['show_items'] as $k => $itemid) {
				if(!in_array($itemid, array_keys($all_items))) {
					unset($_SESSION['show_items'][$k]);
				}
			}

			//Save userpref
			sort($_SESSION['show_items']);
			ko_save_userpref($_SESSION['ses_userid'], 'show_res_items', implode(',', $_SESSION['show_items']));
			ko_save_userpref($_SESSION["ses_userid"], "res_absence_filter",  implode(",",$_SESSION["show_absences_res"]));

			$done = FALSE;
			if($_SESSION['show'] == 'liste') {
				print 'main_content@@@';
				ko_list_reservations();
				$done = TRUE;
			} else if($_SESSION['show'] == 'ical_links') {
				print 'main_content@@@';
				ko_res_ical_links();
				$done = TRUE;
			}

			//Find position of submenu for redraw
			if($action == 'itemlistgroup') {
				if(in_array($_SESSION['show'], array('liste', 'ical_links'))) print '@@@';
				print submenu_reservation('itemlist_objekte', 'open', 2);
				$done = TRUE;
			}

			//Refetch events and resources
			if($_SESSION['show'] == 'calendar') {
				if($done) print '@@@';
				print "POST@@@$('#ko_calendar').fullCalendar('refetchEvents'); $('#ko_calendar').fullCalendar('refetchResources');";
			}
		break;


		case "itemlisttogglegroup":
			//ID and state of the clicked field
			$id = format_userinput($_GET["id"], "js");
			if(isset($_SESSION["res_group_states"][$id])) {
				$_SESSION["res_group_states"][$id] = $_SESSION["res_group_states"][$id] ? 0 : 1;
			} else {
				$_SESSION["res_group_states"][$id] = ($_GET["state"] == 1 ? 0 : 1);
			}

			//Don't redraw the submenu, as this is done in JS so the list doesn't scroll of the mouse's position
		break;


		case "itemlistsave":
			//save new value
			if($_GET["name"] == "") break;
			$new_value = implode(",", $_SESSION["show_items"]);
			$user_id = $access['reservation']['MAX'] > 3 && $_GET['global'] == 'true' ? '-1' : $_SESSION['ses_userid'];
			$name = format_userinput($_GET["name"], "js", FALSE, 0, array("allquotes"));
			ko_save_userpref($user_id, $name, $new_value, "res_itemset");

			if(!empty($_SESSION['show_absences_res'])) {
				ko_save_userpref($user_id, $name, implode(",", $_SESSION['show_absences_res']), "res_absence_filter");
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
					while (ko_get_userpref($lid, $n, 'res_itemset')) {
						$c++;
						$n = "{$nameForOthers} - {$c}";
					}
					ko_save_userpref($lid, $n, $new_value, 'res_itemset');

					if($_SESSION['show_absences_res'] === TRUE) {
						ko_save_userpref($lid, $n, implode(",", $_SESSION['show_absences_res']), "res_absence_filter");
					}
				}
			}

			print submenu_reservation("itemlist_objekte", "open", 2);
		break;


		case "itemlistopen":
			//save new value
			$name = format_userinput($_GET['name'], 'js', FALSE, 0, array(), '@');
			if($name == "") break;

			if($name == '_all_') {
				ko_get_resitems($items);
				$_SESSION['show_items'] = array_keys($items);
				$absence_filters = array_merge((array)ko_get_userpref('-1', '', 'filterset', 'ORDER BY `key` ASC'), (array)ko_get_userpref($_SESSION['ses_userid'], '', 'filterset', 'ORDER BY `key` ASC'));
				$_SESSION['show_absences_res'] = array_column($absence_filters, "id");
				$_SESSION["show_absences_res"][] = "all";
				$_SESSION["show_absences_res"][] = "own";
			} else if($name == '_none_') {
				$_SESSION['show_items'] = array();
				$_SESSION["show_absences_res"] = [];
			} else {
				if(substr($name, 0, 3) == '@G@') {
					$value = ko_get_userpref('-1', substr($name, 3), 'res_itemset');
					$value_absence = ko_get_userpref('-1', substr($name, 3), 'res_absence_filter');
				}
				else {
					$value = ko_get_userpref($_SESSION['ses_userid'], $name, 'res_itemset');
					$value_absence = ko_get_userpref($_SESSION['ses_userid'], $name, 'res_absence_filter');
				}
				$_SESSION["show_items"] = array_filter(explode(",", $value[0]["value"]));
				$_SESSION["show_absences_res"] = array_filter(explode(",",$value_absence[0]["value"]));
			}
			ko_save_userpref($_SESSION['ses_userid'], 'show_res_items', implode(',', $_SESSION['show_items']));
			ko_save_userpref($_SESSION["ses_userid"], "res_absence_filter", implode(",", $_SESSION["show_absences_res"]));

			print submenu_reservation("itemlist_objekte", "open", 2);
			if($_SESSION['show'] == 'liste') {
				print "@@@main_content@@@";
				ko_list_reservations();
			} else if($_SESSION['show'] == 'ical_links') {
				print "@@@main_content@@@";
				ko_res_ical_links();
			}

			//Refetch events and resources
			if($_SESSION['show'] == 'calendar') {
				print "@@@POST@@@$('#ko_calendar').fullCalendar('refetchEvents'); $('#ko_calendar').fullCalendar('refetchResources')";
			}
		break;


		case "itemlistdelete":
			//save new value
			$name = format_userinput($_GET['name'], 'js', FALSE, 0, array(), '@');
			if($name == "") break;

			if(substr($name, 0, 3) == '@G@') {
				if($access['reservation']['MAX'] > 3) {
					ko_delete_userpref('-1', substr($name, 3), 'res_itemset');
					ko_delete_userpref('-1', substr($name, 3), 'res_absence_filter');
				}

			} else {
				ko_delete_userpref($_SESSION['ses_userid'], $name, 'res_itemset');
				ko_delete_userpref($_SESSION['ses_userid'], $name, 'res_absence_filter');
			}

			print submenu_reservation("itemlist_objekte", "open", 2);
		break;



		case "resgroupselect":
			if($access['reservation']['MAX'] < 2) break;

			//GET data
			$gid = format_userinput($_GET["gid"], "uint", FALSE, 11, array(), "-");
			$element = format_userinput($_GET["element"], "text");
			$li = format_userinput($_GET['li'], 'uint');

			$values = array();
			if($gid == "-") {
				$groups = db_select_data("ko_resgruppen", "WHERE 1=1", "*", "ORDER BY `name` ASC");
				foreach($groups as $gid => $group) {
					if($access['reservation']['grp'.$gid] < 2) continue;
					$values[] = "i".$gid.",".str_replace(",", "&comma;", $group["name"])."-->";
				}
			} else {
				//Add up link
				$values[] = "i-,".str_replace(",", "", getLL("form_peopleselect_up"));
				//Add filter to not include items with linked_items
				if($li) $z_where = " AND `linked_items` = '' ";
				else $z_where = '';
				//get resitems
				$items = db_select_data("ko_resitem", "WHERE `gruppen_id` = '$gid'".$z_where, "*", "ORDER BY `name` ASC");
				foreach($items as $iid => $item) {
					if($access['reservation'][$iid] < 2) continue;
					$values[] = $iid.",". str_replace(",", "&comma;", $item["name"]);
				}
			}
			$value = implode("#", $values);

			print "$element@@@$value";
		break;


		case 'jsongetreservations':
			$data = array();
			$monthly_title = ko_get_userpref($_SESSION['ses_userid'], 'res_monthly_title');
			$allow_fields =
				$_SESSION['ses_userid'] == ko_get_guest_id() ?
					array_merge($RES_GUEST_FIELDS_FORCE, explode(',', ko_get_setting('res_show_fields_to_guest'))) :
					array_merge(array_map(function($el) {return $el['Field'];}, db_get_columns('ko_reservation')), array('event_id'))
			;

			$start = strtotime('-10 days', strtotime(substr($_GET['start'], 0, 10).' 00:00:00'));
			$end = strtotime('+10 days', strtotime(substr($_GET['end'], 0, 10).' 23:59:59'));
			for($i=0; $i<2; $i++) {
				if($i == 1 && $access['reservation']['MAX'] < 2) continue;
				//Get all events
				if($i==0) {
					$z_where = ' AND `enddatum` >= \''.strftime('%Y-%m-%d', $start).'\' AND `startdatum` <= \''.strftime('%Y-%m-%d', $end).'\'';

					// fetching reservations for front-module?
					if ($_GET['target'] == 'fm') {
						$z_where .= " AND `user_id` = {$_SESSION['ses_userid']}";
						// apply filter from userpref
						$filterPresetName = ko_get_userpref($_SESSION['ses_userid'], 'res_fm_filter');
						if ($filterPresetName) {
							if (substr($filterPresetName, 0, 4) == '[G] ') {
								$n = substr($filterPresetName, 4);
								$user = -1;
							} else {
								$n = $filterPresetName;
								$user = $_SESSION['ses_userid'];
							}
							$filterPreset = ko_get_userpref($user, $n, 'res_itemset');

							$allowedGroups = ko_array_filter_empty(explode(',', end($filterPreset)['value']));
							if (sizeof($allowedGroups) > 0) {
								$z_where .= " AND `item_id` IN (".implode(',', $allowedGroups).")";
							}
						}
					} else {
						apply_res_filter($z_where, $z_limit, 'immer', 'immer');
						$z_where .= ' AND `enddatum` >= \''.strftime('%Y-%m-%d', $start).'\' AND `startdatum` <= \''.strftime('%Y-%m-%d', $end).'\'';
					}

					ko_get_reservationen($reservations, $z_where, '', 'res', 'ORDER BY startdatum,startzeit,item_name ASC');
				} else {
					$mod_where = ' AND `enddatum` >= \''.strftime('%Y-%m-%d', $start).'\' AND `startdatum` <= \''.strftime('%Y-%m-%d', $end).'\'';
					if($_SESSION['ses_userid'] == ko_get_guest_id()) $mod_where .= ' AND 1=2 ';
					else $mod_where .= '';

					// don't show mod in front-module
					if ($_GET['target'] == 'fm') {
						$mod_where = ' AND 1=2';
					}

					ko_get_reservationen($reservations, $mod_where, '', 'mod', 'ORDER BY startdatum,startzeit,item_name ASC');

					$mod_res = ko_get_reservations_from_events_mod(date("Y-m-d", $start), date("Y-m-d", $end));

					$reservations = array_merge($reservations, $mod_res);

				}

				$done_res = array();
				ko_get_resitems($resitems);
				ko_get_eventgruppen($egs, '', "AND `res_combined` = '1'");
				foreach($reservations as $res) {
					//Only show allowed moderations
					if($i == 1) {
						if(ko_get_setting('res_show_mod_to_all') == 1) {
							if($access['reservation'][$res['item_id']] < 1) continue;
						} else {
							if($access['reservation'][$res['item_id']] < 4 && $res['user_id'] != $_SESSION['ses_userid']) continue;
						}
					}

					//Create combined reservations for events
					if($i==0
						 && ko_get_userpref($_SESSION['ses_userid'], 'show_dateres_combined') == 1
						 && $_GET['view'] != 'resource'
						) {
						//Skip already processed reservations (linked to an event already processed)
						if(in_array($res['id'], $done_res)) continue;
						//Find an event with the current reservation
						ko_get_events($event, 'AND `reservationen` REGEXP \'(^|,)'.$res['id'].'(,|$)\'');
						$event = array_shift($event);
						if($event['id'] && $event['res_combined']) {
							//Mark all linked reservations as done and build purpose text as sum of all items
							$event_items = '';
							foreach(explode(',', $event['reservationen']) as $resid) {
								if($resid) $done_res[] = $resid;
								ko_get_res_by_id($resid, $thisres_); $thisres = $thisres_[$resid];
								$event_items .= $resitems[$thisres['item_id']]['name'].', ';
							}
							$event_items = substr($event_items, 0, -2);
							//Reset color and name according to event group
							$res['item_farbe'] = $event['eventgruppen_farbe'];
							if($event['kommentar']) {
								$htmlKommentar = html_entity_decode(strip_tags($event['kommentar']), ENT_COMPAT | ENT_HTML401, 'iso-8859-1');
								$res['zweck'] = $htmlKommentar.' ('.$event['eventgruppen_name'].')';
								$res['item_name'] = $event_items;
							} else {
								$res['zweck'] = getLL('res_cal_combined').' '.$event['eventgruppen_name'];
								$res['item_name'] = $event_items;
							}
							$res['_combined'] = $event['eventgruppen_id'];
						}
					}//if(res_combined)



					//Set title according to setting
					switch($monthly_title) {
						case 'name':
							$title = (in_array('name', $allow_fields) && $res['name']) ? $res['name'] : $res['item_name'];
						break;
						case 'zweck':
							$title = (in_array('zweck', $allow_fields) && $res['zweck']) ? $res['zweck'] : $res['item_name'];
						break;
						case 'item_id_zweck':
							$title = $res['item_name'];
							$title .= (in_array('zweck', $allow_fields) && $res['zweck']) ? ': '.$res['zweck'] : '';
						break;
						default:
							$title = $res['item_name'];
					}

					//Format time for tooltip
					if($res['startzeit'] == '00:00:00' && $res['endzeit'] == '00:00:00') $time = getLL('time_all_day');
					else $time = substr($res['startzeit'], 0, -3).' - '.substr($res['endzeit'], 0, -3);

					$tooltip = '';

					//Moderated events
					if($i == 1) {
						if(!ko_res_check_double($res['item_id'], sql2datum($res['startdatum']), sql2datum($res['enddatum']), substr($res['startzeit'], 0, -3), substr($res['endzeit'], 0, -3), $double_error)) {
							$title = '! '.$title.' !';
							$tooltip .= '<b>'.getLL('res_collision_text').'</b><br />'.$double_error.'<br /><br />';
							$checkLink = FALSE;
						} else {
							$tooltip .= '<b>'.getLL('res_mod_open').'</b><br /><br />';
							if($access['reservation']['ALL'] > 4 || $access['reservation'][$res['item_id']] > 4) $checkLink = TRUE;
							else $checkLink = FALSE;
						}

					}

					//Tooltip-Text
					$tooltip .= $res['item_name'].'<br />';
					$tooltip .= '<b>'.strftime($DATETIME['ddmy'], strtotime($res['startdatum']));
					if($res['startdatum'] != $res['enddatum']) $tooltip .= ' - '.strftime($DATETIME['ddmy'], strtotime($res['enddatum']));
					$tooltip .= '</b><br />';
					$tooltip .= getLL('kota_listview_ko_reservation_startzeit').': '.$time.'<br />';
					if(in_array('zweck', $allow_fields) && $res['zweck']) $tooltip .= '<b>'.nl2br($res['zweck']).'</b><br />';
					if(in_array('comments', $allow_fields) && $res['comments']) $tooltip .= nl2br($res['comments']).'<br />';
					$fields = array();
					if (in_array('name', $allow_fields)) $fields['name'] = $res['name'];
					if (in_array('email', $allow_fields)) $fields['zweck'] = $res['email'];

					//Show event this reservation belongs to (but not for moderated entries)
					if (in_array('event_id', $allow_fields) && $i == 0) {
						ko_get_events($events, " AND `reservationen` REGEXP '(^|,){$res['id']}(,|$)'");
						if ($events && ($event = end($events))) {
							$eventGroup = db_select_data('ko_eventgruppen', "WHERE `id` = {$event['eventgruppen_id']}", '*', '', '', TRUE);
							$eventTitle = ko_daten_get_event_title($event, $eventGroup, ko_get_userpref($_SESSION['ses_userid'], 'daten_monthly_title'));
							if ($eventTitle['short']) {
								$fields['event_id'] = getLL('kota_listview_ko_reservation_event_id').': '.$eventTitle['short'];
							}
						}
					}
					if (sizeof($fields) > 0) {
						$tooltip .= '<br />'.getLL('res_info_user').': '.implode('<br />', $fields);
					}

					//Add user who entered the moderation request
					if($i == 1) {
						if($res['user_id']) {
							ko_get_login($res['user_id'], $login);
							if($login['leute_id']) {
								ko_get_person_by_id($login['leute_id'], $person);
								$tooltip .= '<br /><br />'.getLL('by').': '.$person['vorname'].' '.$person['nachname'].' ('.$login['login'].')<br />';
							} else {
								$tooltip .= '<br /><br />'.getLL('by').': '.$login['login'].'<br />';
							}
						}
						$tooltip .= '<br />';
					}

					//Add editIcons according to access rights
					$editable = FALSE;
					if($i == 0) {
						if(!$res['_combined']
							&& ($access['reservation'][$res['item_id']] > 3
									|| ($_SESSION['ses_userid'] == $res['user_id'] && $_SESSION['ses_userid'] != ko_get_guest_id() && $access['reservation'][$res['item_id']] > 2)
									|| ($resitems[$res['item_id']]['moderation'] == 0 && $access['reservation'][$res['item_id']] > 2)
							)
						) {

							$editable = TRUE;
							ko_get_events($events, "AND ko_event.`reservationen` REGEXP '(^|,){$res['id']}(,|$)'");
							if ($events && sizeof($events) == 1) {
								$event = end($events);
							} else {
								$event = array();
							}
							$deleteIcon = '<button type="button" class="icon delete fcDeleteIcon" id="item'.$res['id'].($res['serie_id'] ? 'm' : 's').'" title="'.getLL('res_delete_res').'" data-event-id="'.($event?$event['id']:'0').'" data-event-title="'.$event['title'].'" data-event-group="'.$event['eventgruppen_name'].'"><i class="fa fa-remove"></i></button>';
							$editIcons = $deleteIcon;
						} else {
							$editIcons = '';
						}
					}
					//Add links to approve or delete a moderation
					else {
						if($checkLink) {
							$checkIcon = '<button class="icon confirm" title="'.getLL('res_mod_confirm').'" onclick="c1=confirm(\''.getLL('res_mod_confirm_confirm').'\');if(!c1) return false; c=confirm(\''.getLL('res_mod_confirm_confirm2').'\');set_hidden_value(\'id\', \''.$res['id'].'\', this);set_hidden_value(\'mod_confirm\', c, this);set_action(\'res_mod_approve\', this);"><i class="fa fa-check"></i></button>';
						} else {
							$checkIcon = '';
						}
						$delLink  = 'c1=confirm(\''.getLL('res_mod_decline_confirm').'\');if(!c1) return false;';
						if($access['reservation'][$res['item_id']] > 4) $delLink .= 'c=confirm(\''.getLL('res_mod_decline_confirm2').'\');set_hidden_value(\'mod_confirm\', c, this);';
						$delLink .= 'set_hidden_value(\'id\', \''.$res['id'].'\', this);set_action(\'res_mod_delete\', this);';
						$delIcon  = ($access['reservation'][$res['item_id']] > 4 || ($res['user_id'] == $_SESSION['ses_userid'] && !ko_get_setting('res_access_prevent_lvl2_del'))) ? '<button class="icon deny" title="'.getLL('res_mod_decline').'" onclick="'.$delLink.'"><i class="fa fa-remove"></i></button>' : '';
						$editIcons = $checkIcon.($delIcon != '' ? '&nbsp;'.$delIcon : '');

						if (!$res['_combined'] && ($access['reservation'][$res['item_id']] > 4 || $access['reservation']['ALL'] > 4 || ($res['user_id'] == $_SESSION['ses_userid'] && $_SESSION['ses_userid'] != ko_get_guest_id()))) {
							$editable = TRUE;
						}
					}

					//Build data array for fullCalendar
					$res_color = $res['_combined'] ? $egs[$res['_combined']]['farbe'] : $res['item_farbe'];
					$allDay = $res['startzeit'] == '00:00:00' && $res['endzeit'] == '00:00:00';
					if ($allDay) {
						$endT = strftime('%Y-%m-%d', strtotime('+1 day', strtotime($res['enddatum']))).'T'.$res['endzeit'];
					} else {
						$endT = $res['enddatum'].'T'.$res['endzeit'];
					}

					if($res['prov_event']) {
						$editable = FALSE;
						$editIcons = "";
					}

					$data[] = array('id' => $res['id'],
						'start' => $res['startdatum'].'T'.$res['startzeit'],
						'end' => $endT,
						'title' => utf8_encode($title),
						'allDay' => $allDay,
						'editable' => $editable,
						'className' => ($i == 1 ? ' fc-modEvent' : ''),
						'isMod' => ($i == 1),
						'color' => '#'.($res_color ? $res_color : 'aaaaaa'),
						'textColor' => ko_get_contrast_color($res_color),
						'kOOL_tooltip' => utf8_encode($tooltip),
						'kOOL_editIcons' => utf8_encode($editIcons),
						'resourceId' => $res['item_id'],
					);


					//Add fake entries for linked items (only in resource view)
					if($i == 0 && $_GET['view'] == 'resource' && $resitems[$res['item_id']]['linked_items']) {
						foreach(explode(',', $resitems[$res['item_id']]['linked_items']) as $liId) {
							if(!$liId) continue;

							$res_color = $resitems[$liId]['farbe'];
							$data[] = array('id' => $res['id'].'_'.$liId,
								'start' => $res['startdatum'].'T'.$res['startzeit'],
								'end' => $endT,
								'title' => utf8_encode($title),
								'allDay' => $allDay,
								'editable' => FALSE,
								'className' => '',
								'isMod' => FALSE,
								'color' => '#'.($res_color ? $res_color : 'aaaaaa'),
								'textColor' => ko_get_contrast_color($res_color),
								'kOOL_tooltip' => utf8_encode($tooltip),
								'kOOL_editIcons' => '',
								'resourceId' => $liId,
							);
						}
					}//linked items

				}
			}

			if ($_SESSION['show_absences_res'] && ko_module_installed('leute')) {
				require_once('../../daten/inc/daten.inc');
				$data = array_merge(ko_get_absences_for_calendar($_GET['start'], $_GET['end'], "resource"), $data);
			}

			print json_encode($data);
		break;



		case 'jsongetresitems':
			ko_get_resitems($resitems);
			if($access['reservation']['ALL'] < 1) {
				foreach($resitems as $k => $item) {
					if($access['reservation'][$item['id']] < 1) unset($resitems[$k]);
				}
			}
			$items = array();
			foreach($resitems as $item) {
				if(!in_array($item['id'], $_SESSION['show_items'])) continue;
				$items[] = array('title' => utf8_encode($item['name']), 'id' => $item['id']);
			}

			if(!empty($_SESSION['show_absences_res'])) {
				$items[] = [
					"title" => getLL('daten_absence_list_title'),
					"id" => "absences",
				];
			}

			print json_encode($items);
		break;


		case 'fcdelres':
			$id = format_userinput($_GET['id'], 'uint');
			if(!$id) break;
			$serie = ($_GET['serie'] == 'true');
			$event = $_GET['delevent'] == 'true';

			do_del_res($id, $serie, $event);

			if($serie || $event) {
				print 'POST@@@$(\'#ko_calendar\').fullCalendar(\'refetchEvents\'); $(".tooltip.in").remove();';
			} else {
				print 'POST@@@$(\'#ko_calendar\').fullCalendar(\'removeEvents\', \''.$id.'\'); $(".tooltip.in").remove();';
			}
		break;


		case 'fceditres':
			$id = format_userinput($_GET['id'], 'uint');
			$mode = format_userinput($_GET['mode'], 'alpha');
			$secondDelta = format_userinput($_GET['secondDelta'], 'int');
			$allDay = format_userinput($_GET['allDay'], 'int');
			$newItem = format_userinput($_GET['item'], 'uint');
			$isMod = ($_GET['isMod'] == 'true');

			ko_get_res_by_id($id, $res_, ($isMod ? 'ko_reservation_mod' : 'ko_reservation'));
			$res = $res_[$id];
			$new = $res;

			if($secondDelta != 0) {
				$wasAllDay = ($res['startzeit'] == $res['endzeit'] && $res['endzeit'] == '00:00:00');

				$interval = new DateInterval('PT'.abs($secondDelta).'S');
				if ($secondDelta < 0) $interval->invert = 1;

				$secondDeltaEnd = $secondDelta;
				$intervalEnd = $interval;

				if ($mode == 'drop' && $wasAllDay && !$allDay) {
					$secondDeltaEnd = $secondDelta + 2 * 3600;
					$intervalEnd = new DateInterval('PT'.abs($secondDeltaEnd).'S');
					if ($secondDeltaEnd < 0) $intervalEnd->invert = 1;
				}

				$res_stop = DateTime::createFromFormat('Y-m-d H:i:s', $res['enddatum'].' '.$res['endzeit']);
				$res_start = DateTime::createFromFormat('Y-m-d H:i:s', $res['startdatum'].' '.$res['startzeit']);

				if($mode == 'drop') {
					$res_start = $res_start->add($interval);
					$res_stop = $res_stop->add($intervalEnd);
				} else if ($mode == 'editStart') {
					$res_start = $res_start->add($interval);
				} else if ($mode == 'editEnd') {
					$res_stop = $res_stop->add($intervalEnd);
				}

				$new['enddatum'] = $res_stop->format('Y-m-d');
				$new['startdatum'] = $res_start->format('Y-m-d');
				$new['endzeit'] = $res_stop->format('H:i:s');
				$new['startzeit'] = $res_start->format('H:i:s');
			}


			//New item (from resource view)
			$noaccess = FALSE;
			if($newItem > 0 && $newItem != $res['item_id']) {
				ko_get_resitem_by_id($newItem, $_resitem);
				$resitem = $_resitem[$newItem];
				if($resitem['id'] > 0 && $resitem['id'] == $newItem) {
					if($access['reservation']['ALL'] > 3
						|| ($access['reservation'][$newItem] > 3 && $access['reservation'][$res['item_id']] > 3)
						|| ($res['user_id'] == $_SESSION['ses_userid'] && ($access['reservation'][$newItem] > 2 && $access['reservation'][$res['item_id']] > 2))) {
						//Editing for both items allowed without moderation
						$new['item_id'] = $newItem;
						$new['linked_items'] = $resitem['linked_items'];
					} else {
						$noaccess = TRUE;
					}
				} else {
					$noaccess = TRUE;
				}
			}
			if($noaccess) {
				print FALSE;
				break;
			}

			//Check for drop on allDay (minuteDelta and dayDelta 0)
			if($allDay) {
				$new['startzeit'] = $new['endzeit'] = '00:00';
			}

			if(!$isMod && FALSE === ko_res_check_double($new['item_id'], $new['startdatum'], $new['enddatum'], $new['startzeit'], $new['endzeit'], $double_error_txt, $id)) {
				print $double_error_txt;
				print FALSE;
			} else {
				$new['last_change'] = date('Y-m-d H:i:s');
				$new['lastchange_user'] = $_SESSION['ses_userid'];
				$table = $isMod ? 'ko_reservation_mod' : 'ko_reservation';
				db_update_data($table, 'WHERE `id` = \''.$id.'\'', $new);
				ko_log_diff($isMod ? 'edit_res_mod' : 'edit_res', $new, $res);

				print TRUE;
			}
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

		case 'resconflictspreview':
			$koi = $_POST["koi"]["ko_reservation"];
			reset($koi['item_id']);
			$id = key($koi['item_id']);
			kota_process_data("ko_reservation", $koi, "post");
			if($koi["enddatum"] == "0000-00-00" || trim($koi["enddatum"]) == "") $koi["enddatum"] = $koi["startdatum"];

			$err = check_entries($koi);
			if($err > 0) break;

			$itemIds = explode(',', $koi["item_id"]);

			$allRes = array();

			if ($id == 0) { // new
				//Wiederholungen berechnen
				switch($_POST["rd_wiederholung"]) {
					case "taeglich":     $inc = $_POST["txt_repeat_tag"]; break;
					case "woechentlich": $inc = $_POST["txt_repeat_woche"]; break;
					case "monatlich1":   $inc = $_POST["sel_monat1_nr"]. "@".format_userinput($_POST["sel_monat1_tag"], "uint", FALSE, 1); break;
					case "monatlich2":   $inc = $_POST["txt_repeat_monat2"]; break;
					case "holidays":     $inc = format_userinput($_POST["sel_repeat_holidays"], "alphanum+").'@'.format_userinput($_POST["sel_repeat_holidays_offset"], "alphanum+"); break;
					case "dates":     $inc = format_userinput($_POST["sel_repeat_dates"], "alphanumlist"); break;
				}

				ko_get_wiederholung($koi["startdatum"], $koi["enddatum"], $_POST["rd_wiederholung"], $inc,
					$_POST["sel_bis_tag"], $_POST["sel_bis_monat"], $_POST["sel_bis_jahr"],
					$repeat, ($_POST["txt_num_repeats"] ? $_POST["txt_num_repeats"] : ""),
					format_userinput($_POST['sel_repeat_eg'], 'uint'));

				for ($i = 0; $i < sizeof($repeat); $i++) {
					$startDate = sql_datum($repeat[$i++]);
					if (!$startDate) continue;

					$stopDate = sql_datum($repeat[$i]);
					if (!$stopDate) $stopDate = $startDate;

					$allRes[] = array(
						'id' => 0,
						'items' => explode(',', $koi['item_id']),
						'startdatum' => $startDate,
						'enddatum' => $stopDate,
						'startzeit' => $koi["startzeit"],
						'endzeit' => $koi["endzeit"],
					);
				}
			} else { // edit
				ko_get_res_by_id($id, $res);
				$res = $res[$id];
				if ($res['id'] != $id) break;

				$allRes[] = array(
					'id' => $id,
					'items' => explode(',', $koi['item_id']),
					'startdatum' => sql_datum($koi["startdatum"]),
					'enddatum' => sql_datum($koi['enddatum']),
					'startzeit' => $koi["startzeit"],
					'endzeit' => $koi["endzeit"],
				);

				// Check if we have to check whole series
				if ($_POST['chk_serie'] && $res['serie_id']) {
					$serie = db_select_data('ko_reservation', "WHERE `serie_id` = {$res['serie_id']} AND `id` <> {$id}");

					foreach ($serie as $s) {
						$allRes[] = array(
							'id' => $s['id'],
							'items' => explode(',', $koi['item_id']),
							'startdatum' => $s["startdatum"],
							'enddatum' => $s['enddatum'],
							'startzeit' => $koi["startzeit"],
							'endzeit' => $koi["endzeit"],
						);
					}
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

			// We wont allow ko_guest to send prov. reservation
			if ($_SESSION['ses_userid'] == ko_get_guest_id()) {
				$allowConflict = FALSE;
			} else {
				$allowConflict = TRUE;
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
			}, $conflicts)).'</table>'.((!$ep['id'] AND $allowConflict === TRUE) ? '<br><div class="checkbox"><label for="manual_moderation_for_conflicts"><input type="checkbox" id="manual_moderation_for_conflicts" name="manual_moderation_for_conflicts" value="1"> '.getLL('res_conflicts_create_manual_moderation').'</label></div>' : '');

			$return = array('nConflicts' => sizeof($conflicts), 'conflictHtml' => $conflictHtml);
			array_walk_recursive($return, 'utf8_encode_array');
			print json_encode($return);
		break;
	}//switch(action);

	hook_ajax_post($ko_menu_akt, $action);

}//if(GET[action])
?>
