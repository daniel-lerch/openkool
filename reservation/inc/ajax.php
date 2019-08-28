<?php
/*******************************************************************************
*
*    OpenKool - Online church organization tool
*
*    Copyright © 2003-2015 Renzo Lauper (renzo@churchtool.org)
*    Copyright © 2019      Daniel Lerch
*
*    This program is free software; you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation; either version 2 of the License, or
*    (at your option) any later version.
*
*    This program is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*******************************************************************************/

if(!in_array($_GET['action'], array('jsongetreservations', 'jsongetresitems', 'fcsetdate', 'pdfcalendar', 'fceditres', 'fcdelres'))) {
	//Set session id from GET (session will be started in ko.inc.php)
	if(!isset($_GET["sesid"])) exit;
	if(FALSE === session_id($_GET["sesid"])) exit;
}

//Send headers to ensure UTF-8 charset
header('Content-Type: text/html; charset=UTF-8');

error_reporting(0);
$ko_menu_akt = 'reservation';
$ko_path = "../../";
require($ko_path."inc/ko.inc.php");
$ko_path = "../";

ko_get_access('reservation');
if($access['reservation']['MAX'] < 1) exit;

ko_include_kota(array('ko_reservation', 'ko_resitem'));

// Plugins einlesen:
$hooks = hook_include_main("reservation");
if(sizeof($hooks) > 0) foreach($hooks as $hook) include_once($hook);

require($BASE_PATH."reservation/inc/reservation.inc.php");

//Smarty-Templates-Engine laden
require($BASE_PATH."inc/smarty.inc.php");

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
				$filename = basename(ko_reservation_export_months(12, '', $_SESSION['cal_jahr_jahr']));
			}
			//TODO: Jahreskalender
			else if($_SESSION['cal_view'] == 'agendaDay') {
				$filename = ko_export_cal_weekly_view('reservation', 1, '');
			}
			else if($_SESSION['cal_view'] == 'resourceDay') {
				$filename = ko_export_cal_weekly_view_resource(1, '');
			}
			else if($_SESSION['cal_view'] == 'agendaWeek') {
				$filename = ko_export_cal_weekly_view('reservation', '', '');
			}
			else if($_SESSION['cal_view'] == 'resourceWeek') {
				$filename = ko_export_cal_weekly_view_resource('', '');
			}
			else if(in_array($_SESSION['cal_view'], array('month', 'resourceMonth'))) {
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
			print ko_show_res_liste($mode, FALSE);
		break;



		case "setsortresgroups":
			$_SESSION["sort_group"] = format_userinput($_GET["sort"], "alphanum+", TRUE, 30);
			$_SESSION["sort_group_order"] = format_userinput($_GET["sort_order"], "alpha", TRUE, 4);

			print "main_content@@@";
			print ko_show_items_liste(FALSE);
		break;


		case "setjahr":
			$_SESSION["cal_jahr_jahr"] = format_userinput($_GET["set_year"], "uint", TRUE, 4);
			$_SESSION["cal_jahr_start"] = format_userinput($_GET["set_start"], "uint", TRUE, 2);
			$num = (int)ko_get_userpref($_SESSION["ses_userid"], "cal_jahr_num");
			if($num == 0) $num = 6;  //Default

			print "main_content@@@";
			print ko_res_cal_jahr($num, $_SESSION["cal_jahr_start"], "html", FALSE);
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
				print ko_list_reservations(FALSE);
			} else if($_SESSION["show"] == "show_mod_res") {
				print ko_show_res_liste("mod", FALSE);
			} else if($_SESSION["show"] == "list_items") {
				print ko_show_items_liste("all", FALSE);
			}
		break;


		case "itemlist":
		case "itemlistgroup":
			//ID and state of the clicked field
			$id = format_userinput($_GET["id"], "js");
			$state = $_GET["state"] == "true" ? "checked" : "";

			//A single res object was selected
			if($action == "itemlist") {
				if($access['reservation'][$id] < 1) break;

				if($state == "checked") {  //Select it
					if(!in_array($id, $_SESSION["show_items"])) $_SESSION["show_items"][] = $id;
				} else {  //deselect it
					if(in_array($id, $_SESSION["show_items"])) $_SESSION["show_items"] = array_diff($_SESSION["show_items"], array($id));
				}
			}
			//Resgroup selected or unselected
			else if($action == "itemlistgroup") {
				if($access['reservation']['grp'.$id] < 1) break;

				//Get all items for this group
				ko_get_resitems_by_group($id, $items);
				foreach($items as $iid => $item) {
					if($state == "checked") {  //Select it
						if(!in_array($iid, $_SESSION["show_items"])) $_SESSION["show_items"][] = $iid;
					} else {  //Deselect it
						if(in_array($iid, $_SESSION["show_items"])) $_SESSION["show_items"] = array_diff($_SESSION["show_items"], array($iid));
					}
				}//foreach(items)
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

			if($_SESSION['show'] == 'liste') {
				print 'main_content@@@';
				ko_list_reservations();
			} else if($_SESSION['show'] == 'cal_jahr') {
				print 'main_content@@@';
				$num = (int)ko_get_userpref($_SESSION['ses_userid'], 'cal_jahr_num');
				if($num == 0) $num = 6;
				ko_res_cal_jahr($num, $_SESSION['cal_jahr_start'], FALSE);
			} else if($_SESSION['show'] == 'ical_links') {
				print 'main_content@@@';
				ko_res_ical_links();
			}

			//Find position of submenu for redraw
			if($action == 'itemlistgroup') {
				if(in_array('itemlist_objekte', explode(',', $_SESSION['submenu_left']))) $pos = 'left';
				else $pos = 'right';
				if(in_array($_SESSION['show'], array('liste', 'cal_jahr', 'ical_links'))) print '@@@';
				print submenu_reservation('itemlist_objekte', $pos, 'open', 2);
			}

			//Refetch events and resources
			if($_SESSION['show'] == 'calendar') {
				print "@@@POST@@@$('#ko_calendar').fullCalendar('refetchEvents').fullCalendar('refetchResources')";
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
			//Find position of submenu for redraw
			if(in_array("itemlist_objekte", explode(",", $_SESSION["submenu_left"]))) $pos = "left";
			else $pos = "right";

			//save new value
			if($_GET["name"] == "") break;
			$new_value = implode(",", $_SESSION["show_items"]);
			$user_id = $access['reservation']['MAX'] > 3 && $_GET['global'] == 'true' ? '-1' : $_SESSION['ses_userid'];
			ko_save_userpref($user_id, format_userinput($_GET["name"], "js", FALSE, 0, array("allquotes")), $new_value, "res_itemset");

			print submenu_reservation("itemlist_objekte", $pos, "open", 2);
		break;


		case "itemlistopen":
			//Find position of submenu for redraw
			if(in_array("itemlist_objekte", explode(",", $_SESSION["submenu_left"]))) $pos = "left";
			else $pos = "right";

			//save new value
			$name = format_userinput($_GET['name'], 'js', FALSE, 0, array(), '@');
			if($name == "") break;

			if($name == '_all_') {
				ko_get_resitems($items);
				$_SESSION['show_items'] = array_keys($items);
			} else if($name == '_none_') {
				$_SESSION['show_items'] = array();
			} else {
				if(substr($name, 0, 3) == '@G@') $value = ko_get_userpref('-1', substr($name, 3), 'res_itemset');
				else $value = ko_get_userpref($_SESSION['ses_userid'], $name, 'res_itemset');
				$_SESSION["show_items"] = explode(",", $value[0]["value"]);
			}
			ko_save_userpref($_SESSION['ses_userid'], 'show_res_items', implode(',', $_SESSION['show_items']));

			print submenu_reservation("itemlist_objekte", $pos, "open", 2);
			if($_SESSION['show'] == 'liste') {
				print "@@@main_content@@@";
				ko_list_reservations(FALSE);
			} else if($_SESSION['show'] == 'cal_jahr') {
				print "@@@main_content@@@";
				ko_res_cal_jahr($num, $_SESSION['cal_jahr_start'], FALSE);
			} else if($_SESSION['show'] == 'ical_links') {
				print "@@@main_content@@@";
				ko_res_ical_links();
			}

			//Refetch events and resources
			if($_SESSION['show'] == 'calendar') {
				print "@@@POST@@@$('#ko_calendar').fullCalendar('refetchEvents').fullCalendar('refetchResources')";
			}
		break;


		case "itemlistdelete":
			//Find position of submenu for redraw
			if(in_array("itemlist_objekte", explode(",", $_SESSION["submenu_left"]))) $pos = "left";
			else $pos = "right";

			//save new value
			$name = format_userinput($_GET['name'], 'js', FALSE, 0, array(), '@');
			if($name == "") break;

			if(substr($name, 0, 3) == '@G@') {
				if($access['reservation']['MAX'] > 3) ko_delete_userpref('-1', substr($name, 3), 'res_itemset');
			} else ko_delete_userpref($_SESSION['ses_userid'], $name, 'res_itemset');

			print submenu_reservation("itemlist_objekte", $pos, "open", 2);
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
					$values[] = "i".$gid.",".$group["name"]."-->";
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
					$values[] = $iid.",".$item["name"];
				}
			}
			$value = implode("#", $values);

			print "$element@@@$value";
		break;


		case 'jsongetreservations':
			$data = array();
			$monthly_title = ko_get_userpref($_SESSION['ses_userid'], 'res_monthly_title');
			$title_length = ko_get_userpref($_SESSION['ses_userid'], 'res_title_length');
			$daten_title_length = ko_get_userpref($_SESSION['ses_userid'], 'daten_title_length');
			$show_persondata = ($_SESSION["ses_userid"] != ko_get_guest_id() || ko_get_setting("res_show_persondata"));
			$show_purpose = ($_SESSION['ses_userid'] != ko_get_guest_id() || ko_get_setting('res_show_purpose'));

			for($i=0; $i<2; $i++) {
				if($i == 1 && $access['reservation']['MAX'] < 2) continue;

				//Get all events
				if($i==0) {
					apply_res_filter($z_where, $z_limit, 'immer', 'immer');
					$z_where .= ' AND `enddatum` >= \''.strftime('%Y-%m-%d', (int)$_GET['start']).'\' AND `startdatum` <= \''.strftime('%Y-%m-%d', (int)$_GET['end']).'\'';
					ko_get_reservationen($reservations, $z_where, '', 'res', 'ORDER BY startdatum,startzeit,item_name ASC');
				} else {
					$mod_where = ' AND `enddatum` >= \''.strftime('%Y-%m-%d', (int)$_GET['start']).'\' AND `startdatum` <= \''.strftime('%Y-%m-%d', (int)$_GET['end']).'\'';
					if($_SESSION['ses_userid'] == ko_get_guest_id()) $mod_where .= ' AND 1=2 ';
					else $mod_where .= '';
					ko_get_reservationen($reservations, $mod_where, '', 'mod', 'ORDER BY startdatum,startzeit,item_name ASC');
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
								$res['zweck'] = substr($event['kommentar'], 0, $daten_title_length).' ('.$event['eventgruppen_name'].')';
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
							$title = ($show_purpose && $res['name']) ? $res['name'] : $res['item_name'];
						break;
						case 'zweck':
							$title = ($show_purpose && $res['zweck']) ? $res['zweck'] : $res['item_name'];
						break;
						default:
							$title = $res['item_name'];
					}
					if(strlen($title) > $title_length) $title = substr($title, 0, $title_length).'..';

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
					if($show_purpose) {
						$tooltip .= '<b>'.nl2br($res['zweck']).'</b><br />';
						if($res['comments']) $tooltip .= nl2br($res['comments']).'<br />';
					}
					if($show_persondata) $tooltip .= '<br />'.getLL('res_info_user').': '.$res['name'].'<br />'.$res['email'];

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
						if(!$res['_combined'] && ($access['reservation'][$res['item_id']] > 3 || ($_SESSION['ses_userid'] == $res['user_id'] && $_SESSION['ses_userid'] != ko_get_guest_id() && $access['reservation'][$res['item_id']] > 2))) {
							$editable = TRUE;
							$deleteIcon = '<img src="../images/icon_trash.png" class="fcDeleteIcon" id="item'.$res['id'].($res['serie_id'] ? 'm' : 's').'" title="'.getLL('res_delete_res').'" />';
							$editIcons = $deleteIcon;
						} else {
							$editIcons = '';
						}
					}
					//Add links to approve or delete a moderation
					else {
						if($checkLink) {
							$checkIcon = '<input type="image" src="../images/button_check.png" title="'.getLL('res_mod_confirm').'" onclick="c1=confirm(\''.getLL('res_mod_confirm_confirm').'\');if(!c1) return false; c=confirm(\''.getLL('res_mod_confirm_confirm2').'\');set_hidden_value(\'id\', \''.$res['id'].'\', this);set_hidden_value(\'mod_confirm\', c, this);set_action(\'res_mod_approve\', this);" />';
						} else {
							$checkIcon = '';
						}
						$delLink  = 'c1=confirm(\''.getLL('res_mod_decline_confirm').'\');if(!c1) return false;';
						if($access['reservation'][$res['item_id']] > 4) $delLink .= 'c=confirm(\''.getLL('res_mod_decline_confirm2').'\');set_hidden_value(\'mod_confirm\', c, this);';
						$delLink .= 'set_hidden_value(\'id\', \''.$res['id'].'\', this);set_action(\'res_mod_delete\', this);';
						$delIcon  = ($access['reservation'][$res['item_id']] > 4 || $res['user_id'] == $_SESSION['ses_userid']) ? '<input type="image" src="../images/icon_trash.png" title="'.getLL('res_mod_decline').'" onclick="'.$delLink.'" />' : '';
						$editIcons = $checkIcon.($delIcon != '' ? '&nbsp;'.$delIcon : '');
					}

					//Build data array for fullCalendar
					$res_color = $res['_combined'] ? $egs[$res['_combined']]['farbe'] : $resitems[$res['item_id']]['farbe'];
					$data[] = array('id' => $res['id'],
						'start' => $res['startdatum'].'T'.$res['startzeit'],
						'end' => $res['enddatum'].'T'.$res['endzeit'],
						'title' => $title,
						'allDay' => $res['startzeit'] == '00:00:00' && $res['endzeit'] == '00:00:00',
						'editable' => $editable,
						'className' => ($i == 1 ? ' fc-modEvent' : ''),
						'color' => '#'.($res_color ? $res_color : 'aaaaaa'),
						'textColor' => ko_get_contrast_color($res_color),
						'kOOL_tooltip' => $tooltip,
						'kOOL_editIcons' => $editIcons,
						'resource' => $res['item_id'],
					);
				}
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
				$items[] = array('name' => $item['name'], 'id' => $item['id']);
			}

			print json_encode($items);
		break;


		case 'fcdelres':
			$id = format_userinput($_GET['id'], 'uint');
			if(!$id) break;
			$serie = ($_GET['serie'] == 'true');

			do_del_res($id, $serie);

			if($serie) {
				print '@@@POST@@@$(\'#ko_calendar\').fullCalendar(\'refetchEvents\'); tooltip.hide();';
			} else {
				print '@@@POST@@@$(\'#ko_calendar\').fullCalendar(\'removeEvents\', \''.$id.'\'); tooltip.hide();';
			}
		break;


		case 'fceditres':
			$id = format_userinput($_GET['id'], 'uint');
			$mode = format_userinput($_GET['mode'], 'alpha');
			$dayDelta = format_userinput($_GET['dayDelta'], 'int');
			$minuteDelta = format_userinput($_GET['minuteDelta'], 'int');
			$allDay = format_userinput($_GET['allDay'], 'int');
			$newItem = format_userinput($_GET['item'], 'uint');

			ko_get_res_by_id($id, $res_);
			$res = $res_[$id];
			$new = $res;
			if($dayDelta != 0) {
				if($mode == 'drop') $new['startdatum'] = add2date($res['startdatum'], 'tag', $dayDelta, TRUE);
				$new['enddatum'] = add2date($res['enddatum'], 'tag', $dayDelta, TRUE);
			}
			if($minuteDelta != 0) {
				if($mode == 'drop') {
					$new['startzeit'] = add2time($res['startzeit'], $minuteDelta);
				}
				$new['endzeit'] = add2time($res['endzeit'], $minuteDelta);
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

			if(FALSE === ko_res_check_double($new['item_id'], $new['startdatum'], $new['enddatum'], $new['startzeit'], $new['endzeit'], $double_error_txt, $id)) {		
				print FALSE;
			} else {
				$new['last_change'] = date('Y-m-d H:i:s');
				db_update_data('ko_reservation', 'WHERE `id` = \''.$id.'\'', $new);
				ko_log_diff('edit_res', $new, $res);

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
			$sel = ko_calendar_mwselect($_SESSION['cal_view']);
			print 'mwselect@@@'.$sel;
		break;
	}//switch(action);

	hook_ajax_post($ko_menu_akt, $action);

}//if(GET[action])
?>
