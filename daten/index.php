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

ob_start();  //Ausgabe-Pufferung einschalten

$ko_path = "../";
$ko_menu_akt = "daten";

include($ko_path . "inc/ko.inc");
include($ko_path . 'consensus/consensus.inc');
include("inc/daten.inc");
if(ko_module_installed("reservation")) 
	include("../reservation/inc/reservation.inc");

$notifier = koNotifier::Instance();

//Redirect to SSL if needed
ko_check_ssl();

if(!ko_module_installed("daten")) {
	header("Location: ".$BASE_URL."index.php");  //Absolute URL
}

ob_end_flush();  //Puffer flushen

//***Rechte auslesen:
ko_get_access('daten');


//kOOL Table Array
ko_include_kota(array('ko_event', 'ko_eventgruppen', 'ko_reservation', 'ko_pdf_layout', 'ko_reminder'));


//Smarty-Templates-Engine laden
require("$ko_path/inc/smarty.inc");


// Plugins einlesen:
$hooks = hook_include_main("daten");
if(sizeof($hooks) > 0) foreach($hooks as $hook) include_once($hook);


//***Action auslesen:
if($_POST["action"]) {
	$do_action=$_POST["action"];
	$action_mode = "POST";
} else if($_GET["action"]) {
	$do_action=$_GET["action"];
	$action_mode = "GET";
} else {
	$do_action = $action_mode = "";
}

if(FALSE === format_userinput($do_action, "alpha+", TRUE, 30)) trigger_error("invalid action: ".$do_action, E_USER_ERROR);

//Reset show_start if from another module
if($_SERVER['HTTP_REFERER'] != '' && FALSE === strpos($_SERVER['HTTP_REFERER'], '/'.$ko_menu_akt.'/')) $_SESSION['show_start'] = 1;

switch($do_action) {

	//Filter
	case "set_filter_today":
		$_SESSION['filter_start'] = 'today';
		$_SESSION['filter_ende'] = 'immer';
	break;


	case "submit_filter":
		if(FALSE === ($_SESSION["filter_start"] = format_userinput($_POST["sel_filter_start"], "alphanum+", TRUE, 5))) {
			trigger_error("Not allowed filterstart: ".$_POST["sel_filter_start"], E_USER_ERROR);
		}
		if(FALSE === ($_SESSION["filter_ende"] = format_userinput($_POST["sel_filter_ende"], "alphanum+", TRUE, 5))) {
			trigger_error("Not allowed filterend: ".$_POST["sel_filter_ende"], E_USER_ERROR);
		}
	break;


	case "unset_perm_filter":
		if($access['daten']['MAX'] > 3) {
			ko_set_setting("daten_perm_filter_start", "");
			ko_set_setting("daten_perm_filter_ende", "");
		}
	break;


	case 'set_perm_filter':
		if($access['daten']['MAX'] > 3) {
			get_heute($tag, $monat, $jahr);
			if($_SESSION['filter_start'] != 'immer') {
				if($_SESSION['filter_start'] == 'today') {
					$pfs = strftime('%Y-%m-%d', time());
				} else {
					addmonth($monat, $jahr, $_SESSION['filter_start']);
					$pfs = strftime('%Y-%m-%d', mktime(1,1,1, $monat, 1, $jahr));
				}
			} else $pfs = '';

			get_heute($tag, $monat, $jahr);
			if($_SESSION['filter_ende'] != 'immer') {
				if($_SESSION['filter_ende'] == 'today') {
					$pfe = strftime('%Y-%m-%d', time());
				} else {
					addmonth($monat, $jahr, ($_SESSION['filter_ende']+1));
					$pfe = strftime('%Y-%m-%d', mktime(1,1,1, $monat, 0, $jahr));  //0 gleich letzter Tag des Vormonates
				}
			} else $pfe = '';

			ko_set_setting('daten_perm_filter_start', $pfs);
			ko_set_setting('daten_perm_filter_ende', $pfe);
		}
	break;





	//Löschen
	case 'delete_termin':
		if(FALSE === ($del_id = format_userinput($_POST['id'], 'uint', TRUE))) {
			trigger_error('Not allowed del_id: '.$_POST['id'], E_USER_ERROR);
		}
		$event = db_select_data('ko_event', "WHERE `id` = '$del_id'", '*', '', '', TRUE);
		if($access['daten'][$event['eventgruppen_id']] < 2) continue;

		$mode = do_del_termin($del_id);
		if($mode == 'del') $notifier->addInfo(3, $do_action);
		else $notifier->addInfo(10, $do_action);
	break;



	//Ausgewählte Termine löschen
	case "del_selected":
	  foreach($_POST["chk"] as $c_i => $c) {
			if($c) {
				if(FALSE === ($del_id = format_userinput($c_i, "uint", TRUE))) {
					trigger_error("Not allowed del_id (multiple): ".$c_i, E_USER_ERROR);
				}
				$event = db_select_data('ko_event', "WHERE `id` = '$del_id'", '*', '', '', TRUE);
				if($access['daten'][$event['eventgruppen_id']] > 1) do_del_termin($del_id);
			}
		}
		$notifier->addInfo(7, $do_action);
	break;



	//Termingruppe löschen
	case "delete_gruppe":
		if(FALSE === ($del_id = format_userinput($_POST["id"], "uint", TRUE))) {
			trigger_error("Not allowed del_id: ".$_POST["id"], E_USER_ERROR);
		}

		//Check for ALL rights to be able to delete
		if($access['daten']['ALL'] < 3) continue;

		//Log-Meldung erstellen
		ko_get_eventgruppe_by_id($del_id, $del_eventgruppe);
		$log_message  = $del_eventgruppe["name"].": ".substr($del_eventgruppe["startzeit"],0,-3)."-".substr($del_eventgruppe["endzeit"],0,-3);
		$log_message .= " in ".$del_eventgruppe["room"].' "'.$del_eventgruppe["beschreibung"].'", '.$del_eventgruppe["farbe"];

		//Gruppe löschen
		db_delete_data("ko_eventgruppen", "WHERE `id` = '$del_id'");
		ko_log("delete_termingruppe", $log_message);

		//Alle Termine dieser Termingruppe löschen (inkl. zugehöriger Reservationen)
		$rows = db_select_data("ko_event", "WHERE `eventgruppen_id` = '$del_id'");
		foreach($rows as $row) {
			do_del_termin(format_userinput($row["id"], "uint"));
		}

		//Check for empty calendars
		ko_delete_empty_calendars();

		//Rota
		if(in_array('rota', $MODULES)) {
			//Delete reference to this event group for weekly teams
			db_update_data('ko_rota_teams', "WHERE `export_eg` = '$del_id'", array('export_eg' => '0'));

			//Delete event group from rota teams
			$teams = db_select_data('ko_rota_teams', "WHERE `eg_id` REGEXP '(^|,)$del_id(,|$)'", '*', 'ORDER BY name ASC');
			if(sizeof($teams) > 0) {
				foreach($teams as $tid => $team) {
					$new_eg_id = explode(',', $team['eg_id']);
					foreach($new_eg_id as $k => $v) {
						if($v == $del_id) unset($new_eg_id[$k]);
					}
					db_update_data('ko_rota_teams', "WHERE `id` = '$tid'", array('eg_id' => implode(',', $new_eg_id)));
				}
			}
		}

		$notifier->addInfo(4, $do_action);
	break;


	//Settings
	case 'daten_settings':
		if($access['daten']['MAX'] < 1) continue;
		$_SESSION['show_back'] = $_SESSION['show'];
		$_SESSION['show'] = 'daten_settings';
	break;

	case "submit_daten_settings":
		if($access['daten']['MAX'] < 1) continue;

		ko_save_userpref($_SESSION['ses_userid'], 'default_view_daten', format_userinput($_POST['sel_daten'], 'js'));
		ko_save_userpref($_SESSION['ses_userid'], 'show_limit_daten', format_userinput($_POST['txt_limit_daten'], 'uint'));
		ko_save_userpref($_SESSION['ses_userid'], 'cal_jahr_num', format_userinput($_POST['sel_cal_jahr_num'], 'uint'));
		ko_save_userpref($_SESSION['ses_userid'], 'cal_woche_start', format_userinput($_POST['txt_cal_woche_start'], 'uint'));
		ko_save_userpref($_SESSION['ses_userid'], 'cal_woche_end', format_userinput($_POST['txt_cal_woche_end'], 'uint'));
		ko_save_userpref($_SESSION['ses_userid'], 'daten_monthly_title', format_userinput($_POST['sel_monthly_title'], 'js', FALSE));
		ko_save_userpref($_SESSION['ses_userid'], 'daten_title_length', format_userinput($_POST['txt_title_length'], 'uint', FALSE));
		ko_save_userpref($_SESSION['ses_userid'], 'daten_pdf_show_time', format_userinput($_POST['sel_pdf_show_time'], 'uint', FALSE, 1));
		ko_save_userpref($_SESSION['ses_userid'], 'daten_pdf_use_shortname', format_userinput($_POST['sel_pdf_use_shortname'], 'uint', FALSE, 1));
		ko_save_userpref($_SESSION['ses_userid'], 'daten_export_show_legend', format_userinput($_POST['sel_export_show_legend'], 'uint', FALSE, 1));
		ko_save_userpref($_SESSION['ses_userid'], 'daten_pdf_week_start', format_userinput($_POST['sel_pdf_week_start'], 'uint', FALSE, 1));
		ko_save_userpref($_SESSION['ses_userid'], 'daten_pdf_week_length', format_userinput($_POST['sel_pdf_week_length'], 'uint', FALSE, 2));
		ko_save_userpref($_SESSION['ses_userid'], 'daten_mark_sunday', format_userinput($_POST['sel_mark_sunday'], 'uint', FALSE, 1));
		ko_save_userpref($_SESSION['ses_userid'], 'daten_no_cals_in_itemlist', format_userinput($_POST['sel_no_cals_in_itemlist'], 'uint', FALSE, 1));
		ko_save_userpref($_SESSION['ses_userid'], 'show_birthdays', format_userinput($_POST['sel_show_birthdays'], 'uint', FALSE, 1));
		ko_save_userpref($_SESSION['ses_userid'], 'daten_show_res_in_tooltip', format_userinput($_POST['sel_show_res_in_tooltip'], 'uint', FALSE, 1));
		ko_save_userpref($_SESSION['ses_userid'], 'daten_rooms_only_future', format_userinput($_POST['chk_daten_rooms_only_future'], 'uint', FALSE, 1));
		if($_SESSION['ses_userid'] != ko_get_guest_id()) {
			ko_save_userpref($_SESSION['ses_userid'], 'daten_ical_deadline', format_userinput($_POST['sel_ical_deadline'], 'int', FALSE));
			if($access['daten']['MAX'] > 3) {
				ko_save_userpref($_SESSION['ses_userid'], 'do_mod_email_for_edit_daten', format_userinput($_POST['sel_do_mod_email_for_edit_daten'], 'uint', FALSE, 1));
			}
		}
		ko_save_userpref($_SESSION['ses_userid'], 'daten_ical_description_fields', format_userinput($_POST['sel_ical_description_fields'], 'alphanumlist'));

		if($access['daten']['MAX'] > 3) {
			if(in_array('groups', $MODULES)) {
				ko_set_setting('daten_gs_pid', format_userinput($_POST['sel_gs_pid'], 'uint'));
				ko_set_setting('daten_gs_role', format_userinput($_POST['sel_gs_role'], 'uint'));
				ko_set_setting('daten_gs_available_roles', format_userinput($_POST['sel_gs_available_roles'], 'alphanumlist'));
			}
			ko_set_setting('daten_show_mod_to_all', format_userinput($_POST['sel_show_mod_to_all'], 'uint'));
			ko_set_setting('daten_mod_exclude_fields', format_userinput($_POST['sel_mod_exclude_fields'], 'alphanumlist'));
			ko_set_setting('daten_mandatory', format_userinput($_POST['sel_mandatory'], 'alphanumlist'));
			ko_set_setting('daten_access_calendar', format_userinput($_POST['sel_calendar_access'], 'uint'));
			if ($_SESSION['ses_userid'] == ko_get_root_id()) {
				ko_set_setting('activate_event_program', format_userinput($_POST['sel_activate_event_program'], 'uint'));
			}
		}


		$_SESSION['show'] = ($_SESSION['show_back'] && in_array($_SESSION['show_back'], array_keys($DISABLE_SM['daten']))) ? $_SESSION['show_back'] : 'daten_settings';
	break;



	//Anzeigen
	case 'show_all_events':  //Backwards compatibility for stored userpref
	case 'all_events':
		if($access['daten']['MAX'] < 1) continue;

		if($_SESSION['show'] == 'all_events') $_SESSION['show_start'] = 1;
		$_SESSION["show"] = "all_events";
	break;

	case "all_groups":
		if($access['daten']['MAX'] < 3) continue;

		$_SESSION["show"] = "all_groups";
		$_SESSION["show_start"] = 1;
	break;

	case 'show_calendar':  //Backwards compatibility
	case 'calendar':
		if($access['daten']['MAX'] < 1) continue;

		$_SESSION['show'] = 'calendar';
		$wt = kota_filter_get_warntext('ko_event');
		if (trim($wt) != '') $notifier->addTextWarning($wt);
	break;

	case "show_cal_monat":
		if($access['daten']['MAX'] < 1) continue;

		if($_GET["set_month"]) {
			if(FALSE === ($new_month = format_userinput($_GET["set_month"], "int", TRUE, 7))) {
				trigger_error("Not allowed set_month: ".$_GET["set_month"], E_USER_ERROR);
			}
			$_SESSION['cal_tag'] = 1;
			$_SESSION["cal_monat"] = (int)substr($new_month, 0, 2);
			$_SESSION["cal_jahr"] = (int)substr($new_month, -4);
		}

		$_SESSION['cal_view'] = 'month';
		$_SESSION['show'] = 'calendar';
		$wt = kota_filter_get_warntext('ko_event');
		if (trim($wt) != '') $notifier->addTextWarning($wt);
	break;

	case "show_cal_woche":
		if($access['daten']['MAX'] < 1) continue;

		$_SESSION['cal_view'] = 'agendaWeek';
		$_SESSION["show"] = "calendar";
		$wt = kota_filter_get_warntext('ko_event');
		if (trim($wt) != '') $notifier->addTextWarning($wt);
	break;

	case "show_cal_jahr":
		if($access['daten']['MAX'] < 1) continue;

		$_SESSION["show"] = "cal_jahr";
		$_SESSION['cal_view'] = '';
	break;

	case 'daten_ical_links':
		if($access['daten']['MAX'] < 1) continue;

		$_SESSION['show'] = 'ical_links';
	break;



	//Neu
	case "neuer_termin":
		if($access['daten']['MAX'] < 2) continue;

		//Get new date and time from GET param dayDate
		if(isset($_GET['dayDate'])) $start_stamp = $end_stamp = strtotime($_GET['dayDate']);
		if(isset($_GET['endDate'])) $end_stamp = strtotime($_GET['endDate']);
		if(!$start_stamp) $start_stamp = $end_stamp = time();

		$new_date_start = strftime('%Y-%m-%d', $start_stamp);
		$new_date_end   = strftime('%Y-%m-%d', $end_stamp);
		$new_time_start = strftime("%H:%M", $start_stamp);
		if($new_time_start == '00:00') {  //All day
			$new_time_end = '';
		} else {  //time given
			$new_time_end = $end_stamp != $start_stamp ? strftime('%H:%M', $end_stamp) : strftime('%H:00', (int)$end_stamp+3600);
		}

		kota_assign_values("ko_event", array("startdatum" => $new_date_start));
		if($new_date_start != $new_date_end) kota_assign_values("ko_event", array("enddatum" => $new_date_end));

		$new_event_data = array("start_time" => $new_time_start, "end_time" => $new_time_end);

		if($_SESSION['show'] != 'neuer_termin') $_SESSION['show_back'] = $_SESSION['show'];
		$_SESSION["show"]= "neuer_termin";
		$onload_code = "form_set_first_input();".$onload_code;
	break;



	case "neue_gruppe":
		if($access['daten']['ALL'] < 3) continue;

		$_SESSION["show_back"] = $_SESSION["show"];
		$_SESSION["show"]= "neue_gruppe";
		$onload_code = "form_set_first_input();".$onload_code;
	break;



	case 'new_googlecal':
		if($access['daten']['ALL'] < 3) continue;

		$_SESSION['show_back'] = $_SESSION['show'];
		$_SESSION['show']= 'new_googlecal';
		$onload_code = 'form_set_first_input();'.$onload_code;
	break;



	case 'new_ical':
		if($access['daten']['ALL'] < 3) continue;

		$_SESSION['show_back'] = $_SESSION['show'];
		$_SESSION['show']= 'new_ical';
		$onload_code = 'form_set_first_input();'.$onload_code;
	break;



	case 'submit_neuer_termin':
	case 'submit_as_new_event':
		if($access['daten']['MAX'] < 2) continue;

		//Formulardaten
		$data = $_POST["koi"]["ko_event"];
		kota_process_data("ko_event", $data, "post");

		$data["resitems"] = format_userinput($_POST["sel_do_res"], "intlist");
		foreach($EVENTS_SHOW_RES_FIELDS as $f) {
			if(in_array($f, array('startzeit', 'endzeit'))) {
				$data['res_'.$f] = $_POST['res_'.$f] ? sql_zeit($_POST['res_'.$f]) : $data[$f];
			} else {
				$data['res_'.$f] = $_POST['res_'.$f];
			}
		}

		//Group subscription
		$data['gs_gid'] = isset($_POST['chk_gs_gid']) ? 1 : '';
		//Copy event: Copy GS group as well
		if($do_action == 'submit_as_new_event') {
			$id = format_userinput($_POST['id'], 'uint');
			$orig_event = db_select_data('ko_event', "WHERE `id` = '$id'", '*', '', '', TRUE);
			if($data['gs_gid'] == 1 && $orig_event['gs_gid'] != '') {
				$data['gs_gid'] = 'COPY'.$orig_event['gs_gid'];
			}
		}

		// get program of new event (if there is any)
		if (ko_get_setting('activate_event_program') == 1) {
			if ($do_action == 'submit_as_new_event') {
				$programFromId = $orig_event['id'];
			}
			else {
				$programFromId = format_userinput($_POST['ft_ko_event_program_id'], 'alphanum+');
			}
			$programEntries = db_select_data('ko_event_program', "where `pid` = '" . $programFromId . "'");
			foreach ($programEntries as $k => $programEntry) {
				unset($programEntries[$k]['pid']);
				unset($programEntries[$k]['id']);
			}
			$data['programEntries'] = $programEntries;
		}


		//Eingaben überprüfen
		$errorOut = check_daten_entries($data);
		if($errorOut) continue;

		//Get repetition
		switch($_POST["rd_wiederholung"]) {
			case "taeglich":     $inc = format_userinput($_POST["txt_repeat_tag"], "uint"); break;
			case "woechentlich": $inc = format_userinput($_POST["txt_repeat_woche"], "uint"); break;
			case "monatlich1":   $inc = format_userinput($_POST["sel_monat1_nr"], "uint")."@".format_userinput($_POST["sel_monat1_tag"], "uint"); break;
			case "monatlich2":   $inc = format_userinput($_POST["txt_repeat_monat2"], "uint"); break;
		}
		ko_get_wiederholung($data["startdatum"], $data["enddatum"], $_POST["rd_wiederholung"], $inc,
												$_POST["sel_bis_tag"], $_POST["sel_bis_monat"], $_POST["sel_bis_jahr"],
												$repeat, ($_POST["txt_num_repeats"] ? $_POST["txt_num_repeats"] : ""),
												format_userinput($_POST['sel_repeat_eg'], 'uint'));

		if(sizeof($repeat) <= 0) $notifier->addError(5, $do_action);
		if($notifier->hasErrors()) continue;


		//Find moderation from event group
		$eg = db_select_data("ko_eventgruppen", "WHERE `id` = '".$data["eventgruppen_id"]."'", "*", "", "", TRUE);
		$moderation = $eg["moderation"];

		$event_data = $mod_data = array();

		//Loop through all repetitions
		for($i=0; $i<sizeof($repeat); $i+=2) {
			$data["startdatum"] = sql_datum($repeat[$i]);
			$data["enddatum"] = $repeat[$i+1] != "" ? sql_datum($repeat[$i+1]) : $data["startdatum"];

			$data['res_startdatum'] = date('Y-m-d', ($_POST['res_startdatum_delta']*24*3600 + strtotime($data["startdatum"])));
			$data['res_enddatum'] = date('Y-m-d', ($_POST['res_enddatum_delta']*24*3600 + strtotime($data["enddatum"])));

			if($moderation == 0 || $access['daten'][$data['eventgruppen_id']] > 2) {
				$event_data[] = $data;
			} else {
				$mod_data[] = $data;
			}
		}//for(i=0..sizeof(repeat))

		if (sizeof($event_data) > 0) {
			$errorOut = ko_daten_store_event($event_data);
			if(!$errorOut) {
				// copy images from base event in case action == submit_as_new_event
				if ($do_action == 'submit_as_new_event') {
					$fileFields = array();
					foreach ($KOTA['ko_event'] as $k => $v) {
						if (substr($k, 0, 1) == '_') continue;
						if ($v['form']['type'] != 'file')  continue;
						if ($orig_event[$k] == '') continue;
						$fileFields[] = $k;
					}
					foreach ($event_data as $savedEvent) {
						$updateData = array();
						foreach ($fileFields as $fileField) {
							if ($savedEvent[$fileField] != '') continue;
							if (array_key_exists($fileField . '_DELETE', $savedEvent)) continue;
							$origName = $orig_event[$fileField];
							$ext = trim(end((explode(".", $origName))));
							$newName = 'my_images/kota_ko_event_' . $fileField . '_' . $savedEvent['id'] . ($ext == '' ? '' : '.' . $ext);
							copy($ko_path . $origName, $ko_path . $newName);
							$updateData[$fileField] = $newName;
						}
						db_update_data('ko_event', 'where id = ' . $savedEvent['id'], $updateData);
					}
				}

				$notifier->addInfo(1, $do_action);
			}
		}
		if (sizeof($mod_data) > 0) {
			ko_daten_store_moderation($mod_data);
			$notifier->addInfo(9, $do_action);
		}

		// delete program with dummy pid
		if (!$notifier->hasErrors() && ko_get_setting('activate_event_program') == 1 && $do_action != 'submit_as_new_event') {
			db_delete_data('ko_event_program', "where `pid` = '" . $data['programEntries'] . "'");
		}


		$_SESSION['show'] = ($_SESSION['show_back'] && in_array($_SESSION['show_back'], array_keys($DISABLE_SM['daten']))) ? $_SESSION['show_back'] : 'calendar';
	break;



	case "submit_neue_gruppe":
	case "submit_edit_gruppe":
	case 'submit_new_googlecal':
	case 'submit_edit_googlecal':
	case 'submit_new_ical':
		list($table, $columns, $id, $hash) = explode("@", $_POST["id"]);
		if($do_action == 'submit_edit_gruppe' || $do_action == 'submit_edit_googlecal') {
			if(FALSE === ($id = format_userinput($id, "uint", TRUE))) continue;
			//Check for edit right (3) for this event group
			if($access['daten'][$id] < 3) continue;
		} else {
			//Only allow new event groups with ALL rights >= 3
			if($access['daten']['ALL'] < 3) continue;
		}


		//Check for calendar_id given in submitted columns (might not be set for certain types)
		if(in_array('calendar_id', explode(',', $columns))) {
			$cal_id = TRUE;

			$txt_cal = trim(format_userinput($_POST["koi"]["ko_eventgruppen"]["calendar_id_PLUS"][$id], "text"));

			//Check for new calendar
			$found = FALSE;
			if($txt_cal != "") {
				//New calendars only with ALL rights 3 or higher
				$allowed = ($access['daten']['ALL'] > 2);

				if($allowed) {
					ko_get_event_calendar($cals);
					foreach($cals as $cal) {
						if($cal["name"] == $txt_cal) {
							$found = TRUE;
							$new_cal = $cal["id"];
						}//if(r[name] == txt_cal)
					}//foreach(resg)

					if(!$found) {
						$new_cal = db_insert_data("ko_event_calendar", array("name" => $txt_cal));
						//Log
						ko_log("new_calendar", "$new_cal: $txt_cal");
					}//if(!found)
				}//if(allowed)

			} else {  //An existing calendar has been selected
				$new_cal = format_userinput($_POST["koi"]["ko_eventgruppen"]["calendar_id"][$id], "uint");
				$allowed = TRUE;
			}//if..else(txt_cal)
		}
		//No calendar id set in submitted columns
		else {
			$cal_id = FALSE;
			$allowed = TRUE;
		}


		//Check for edit rights for selected cal (or new cal)
		if($allowed && ($access['daten']['cal'.$new_cal] > 2 || !$found)) {
			if($cal_id) {
				$_POST["koi"]["ko_eventgruppen"]["calendar_id"][$id] = $new_cal;
				$_POST["koi"]["ko_eventgruppen"]["calendar_id_PLUS"][$id] = "";
			}

			if($do_action == 'submit_new_googlecal') $_POST['kota_type'] = 1;
			if($do_action == 'submit_new_ical') $_POST['kota_type'] = 3;
			if($do_action == 'submit_neue_gruppe' || $do_action == 'submit_new_googlecal' || $do_action == 'submit_new_ical') {
				$new_id = kota_submit_multiedit("", "new_eventgruppe");
				$_SESSION["show_tg"][] = $new_id;
				$notifier->addInfo(5, $do_action);
			} else if($do_action == 'submit_edit_gruppe' || $do_action == 'submit_edit_googlecal') {
				kota_submit_multiedit("", "edit_eventgruppe");
				ko_delete_empty_calendars();
				$notifier->addInfo(6, $do_action);
			}
		}

		//Set type to 1 for googlecals
		if($do_action == 'submit_new_googlecal') {
			db_update_data('ko_eventgruppen', "WHERE `id` = '$new_id'", array('type' => '1'));
		}
		//Set type to 3 for ical
		if($do_action == 'submit_new_ical') {
			db_update_data('ko_eventgruppen', "WHERE `id` = '$new_id'", array('type' => '3'));
			//Initial import of ical events
			ko_daten_import_ical($new_id);
		}
		//Reimport events after editing an ical event group
		if($id) ko_daten_import_ical($id);

		$_SESSION['show'] = ($_SESSION['show_back'] && in_array($_SESSION['show_back'], array_keys($DISABLE_SM['daten']))) ? $_SESSION['show_back'] : 'all_groups';
	break;






	case "edit_termin":
		if($_POST["id"]) $id = $_POST["id"];
    	else if($_GET["id"]) $id = $_GET["id"];
    	else continue;

		if(FALSE === ($id = format_userinput($id, "uint", TRUE))) {
			trigger_error("Not allowed id: ".$id, E_USER_ERROR);
		}

		$event = db_select_data('ko_event', "WHERE `id` = '$id'", '*', '', '', TRUE);
		if($access['daten'][$event['eventgruppen_id']] < 2) continue;

		$_SESSION["show_back"] = $_SESSION["show"];
		$_SESSION["show"]= "edit_termin";
		$edit_id = $id;
		$onload_code = "form_set_first_input();".$onload_code;
	break;



	case "edit_gruppe":
		$edit_id = format_userinput($_POST['id'], 'uint');
		if($access['daten'][$edit_id] < 3) continue;

		$eg = db_select_data('ko_eventgruppen', "WHERE `id` = '$edit_id'", '*', '', '', TRUE);

		$_SESSION["show_back"] = $_SESSION["show"];
		$_SESSION['show'] = $eg['gcal_url'] != '' ? 'edit_googlecal' : 'edit_gruppe';
		$onload_code = "form_set_first_input();".$onload_code;
	break;



	case "submit_edit_termin":
		$id = format_userinput($_POST["id"], "uint");
		$event = db_select_data('ko_event', "WHERE `id` = '$id'", '*', '', '', TRUE);
		if($access['daten'][$event['eventgruppen_id']] < 2) continue;

		//Process data
		$data = $_POST["koi"]["ko_event"];
		kota_process_data("ko_event", $data, "post", $log, $id);
		$errorOut = check_daten_entries($data);
		if($errorOut) {
			$edit_id = $id;
			continue;
		}
		$data["resitems"] = format_userinput($_POST["sel_do_res"], "intlist");
		foreach($EVENTS_SHOW_RES_FIELDS as $f) {
			if(in_array($f, array('startzeit', 'endzeit'))) {
				$data['res_'.$f] = $_POST['res_'.$f] ? sql_zeit($_POST['res_'.$f]) : $data[$f];
			} else {
				$data['res_'.$f] = $_POST['res_'.$f];
			}
		}
		$data['res_startdatum'] = date('Y-m-d', ($_POST['res_startdatum_delta']*24*3600 + strtotime($data["startdatum"])));
		$data['res_enddatum'] = date('Y-m-d', ($_POST['res_enddatum_delta']*24*3600 + strtotime($data["enddatum"])));

		$data["startdatum"] = sql_datum($data["startdatum"]);
		$data["enddatum"] = sql_datum($data["enddatum"]);
		if($data["enddatum"] == "0000-00-00" || trim($data["enddatum"]) == "") $data["enddatum"] = $data["startdatum"];
		//Group subscription (group will be created in ko_daten_store_event() or ko_daten_update_event())
		if(ko_get_setting('daten_gs_pid') && ko_module_installed('groups')) {
			if(!isset($access['groups'])) ko_get_access('groups');
			if($access['groups']['ALL'] > 2 || $access['groups'][ko_get_setting('daten_gs_pid')] > 2) {
				$data['gs_gid'] = isset($_POST['chk_gs_gid']) ? 1 : '';
			}
		}

		//Check for changes
		$event["startzeit"] = sql_zeit($event["startzeit"]);
		$event["endzeit"] = sql_zeit($event["endzeit"]);
		$dont_check = array('id', 'gs_gid', 'cdate', 'last_change', 'reservationen', 'import_id');
		if(!ko_module_installed('rota')) $dont_check[] = 'rota';
		//Add exclude fields from settings
		foreach(explode(',', ko_get_setting('daten_mod_exclude_fields')) as $f) {
			if(!$f || in_array($f, $dont_check)) continue;
			$dont_check[] = $f;
		}
		$event_changed = FALSE;
		foreach($event as $key => $value) {
			if(in_array($key, $dont_check)) continue;
			if($event[$key] != $data[$key]) $event_changed = TRUE;
		}

		//Check for event moderation
		ko_get_eventgruppe_by_id($data["eventgruppen_id"], $eg);
		//Only store changes as moderation if something changed. (e.g. only changes in reservations don't need moderation)
		if($eg["moderation"] > 0 && $access['daten'][$event['eventgruppen_id']] < 3 && $event_changed) {
			$data["_event_id"] = $id;
			ko_daten_store_moderation(array($data));
			$notifier->addInfo(9, $do_action);
		} else {
			$errorOut = ko_daten_update_event($id, $data);
			if (!$errorOut) {
				$notifier->addInfo(2, $do_action);
			}
		}

		$_SESSION['show'] = ($_SESSION['show_back'] && in_array($_SESSION['show_back'], array_keys($DISABLE_SM['daten']))) ? $_SESSION['show_back'] : 'calendar';
	break;





	case "multiedit":
		if($_SESSION["show"] == "all_events") {
			if($access['daten']['MAX'] < 2) continue;

			//Zu bearbeitende Spalten
			$columns = explode(",", format_userinput($_POST["id"], "alphanumlist"));
			foreach($columns as $column) {
				$do_columns[] = $column;
			}
			if(sizeof($do_columns) < 1) $notifier->addError(8, $do_action);

			//Zu bearbeitende Einträge
			$do_ids = array();
			foreach($_POST["chk"] as $c_i => $c) {
				if(!$c) continue;
				if(FALSE === ($edit_id = format_userinput($c_i, "uint", TRUE))) {
					trigger_error("Not allowed multiedit_id: ".$c_i, E_USER_ERROR);
				}
				//Force level 2 access for non moderated and level 3 access for moderated entries for multiediting
				$event = db_select_data('ko_event AS e LEFT JOIN ko_eventgruppen AS g ON e.eventgruppen_id = g.id', "WHERE e.id = '$edit_id'", 'e.id AS id, e.eventgruppen_id AS eventgruppen_id, g.moderation AS moderation, e.import_id AS import_id', '', '', TRUE);

				//Don't allow editing imported events
				if($event['import_id'] != '') continue;

				if(($event['moderation'] > 0 && $access['daten'][$event['eventgruppen_id']] > 2) || ($event['moderation'] == 0 && $access['daten'][$event['eventgruppen_id']] > 1)) $do_ids[] = $edit_id;
			}
			if(sizeof($do_ids) < 1) $notifier->addError(7, $do_action);

			//Daten für Formular-Aufruf vorbereiten
			if(!$notifier->hasErrors()) {
				$order = "ORDER BY ".$_SESSION["sort_events"]." ".$_SESSION["sort_events_order"];
				$_SESSION["show_back"] = $_SESSION["show"];
				$_SESSION["show"] = "multiedit";
			}


		/* Termingruppen */
		} else if($_SESSION["show"] == "all_groups") {
			if($access['daten']['MAX'] < 3) continue;

			//Zu bearbeitende Spalten
			$columns = explode(",", format_userinput($_POST["id"], "alphanumlist"));
			foreach($columns as $column) {
				$do_columns[] = $column;
			}
			if(sizeof($do_columns) < 1) $notifier->addError(8, $do_action);

			//Zu bearbeitende Einträge
			$do_ids = array();
			foreach($_POST["chk"] as $c_i => $c) {
				if(!$c) continue;
				if(FALSE === ($edit_id = format_userinput($c_i, "uint", TRUE))) {
					trigger_error("Not allowed multiedit_id: ".$c_i, E_USER_ERROR);
				}
				if($access['daten'][$edit_id] > 2) $do_ids[] = $edit_id;
			}
			if(sizeof($do_ids) < 1) $notifier->addError(8, $do_action);

			//Daten für Formular-Aufruf vorbereiten
			if(!$notifier->hasErrors()) {
				$order = "ORDER BY ".$_SESSION["sort_tg"]." ".$_SESSION["sort_tg_order"];
				$_SESSION["show_back"] = $_SESSION["show"];
				$_SESSION["show"] = "multiedit_tg";
			}
		}
		$onload_code = "form_set_first_input();".$onload_code;

	break;



	case "submit_multiedit":
		if($_SESSION["show"] == "multiedit") {
			if($access['daten']['MAX'] < 2) continue;
			kota_submit_multiedit(2, '', 'last_change', $changes);
			//Send notifications for multiediting events
			foreach($changes['ko_event'] as $id => $v) {
				foreach($v as $a => $b) $v[$a] = '';
				ko_daten_send_notification($id, 'update', $v);
			}
		} else if($_SESSION["show"] == "multiedit_tg") {
			if($access['daten']['MAX'] < 3) continue;
			kota_submit_multiedit(3);
			//Check for empty calendars
			ko_delete_empty_calendars();
		}

		if(!$notifier->hasErrors()) {
			$notifier->addInfo(8, $do_action);
		}
		$_SESSION['show'] = ($_SESSION['show_back'] && in_array($_SESSION['show_back'], array_keys($DISABLE_SM['daten']))) ? $_SESSION['show_back'] : 'all_events';
	break;






	//Moderation
	case "list_events_mod":
		if($_SESSION["ses_userid"] == ko_get_guest_id()) continue;
		$_SESSION["show"] = "list_events_mod";
	break;


	case "daten_mod_new_approve":
	case "daten_mod_new_approve_multi":
		$ids = $email_rec = array();
		$event_text = "";

		if($do_action == "daten_mod_new_approve") {
			$ids[] = format_userinput($_POST["id"], "uint");
		}
		else if($do_action == "daten_mod_new_approve_multi") {
			foreach($_POST["chk"] as $c_i => $c) {
				if($c) $ids[] = format_userinput($c_i, "uint");
			}
		}
		if(!$ids[0]) continue;

		$notification = format_userinput($_POST["mod_confirm"], "alpha", FALSE, 5) == "true";

		foreach($ids as $id) {
			$new_event_data = db_select_data("ko_event_mod", "WHERE `id` = '$id'", "*", "", "", TRUE);
			if(!$new_event_data["eventgruppen_id"] || $access['daten'][$new_event_data["eventgruppen_id"]] < 4) continue;

			//Moderation to delete the event
			if($mod_event["_delete"] == 1) {
				do_del_termin($id);  //Delete event which also deleted mod entries for this event
			} else {
				//Store event
				$store_data = array($new_event_data);
				$errorOut = ko_daten_store_event($store_data);
				if(!$errorOut) {
					$notifier->addInfo(1, $do_action);
				}
				//Delete mod entry
				db_delete_data("ko_event_mod", "WHERE `id` = '$id'");
			}

			//Logging
			$new_event_data["notification"] = $notification ? getLL("yes") : getLL("no");
			ko_log_diff("event_mod_approve", $new_event_data);

			if($notification) {
				$person = ko_get_logged_in_person($new_event_data['_user_id']);
				if($person["email"]) $email_rec[] = $person["email"];
				$event_text .= ko_daten_infotext($new_event_data);
			}
		}

		if($notification) {
			$smarty->assign("txt_empfaenger", implode(", ", array_unique($email_rec)));
			$smarty->assign('txt_empfaenger_semicolon', implode('; ', array_unique($email_rec)));
			$p = ko_get_logged_in_person();
      $smarty->assign("tpl_show_bcc_an_mich", ($p["email"] ? TRUE : FALSE));
      $smarty->assign("tpl_show_send", TRUE);
			$smarty->assign('txt_betreff', ('[kOOL] '.(sizeof($ids) > 1 ? getLL('daten_emails_mod_confirm_subject') : getLL('daten_email_mod_confirm_subject'))) );

			$smarty->assign('txt_emailtext', ((sizeof($ids) > 1 ? getLL('daten_emails_mod_confirm_text') : getLL('daten_email_mod_confirm_text'))."\n\n".ko_html($event_text)) );

			$smarty->assign("tpl_show_rec_link", TRUE);
			$_SESSION["show"]= "email_confirm";
		}
	break;

	
	case "daten_mod_delete":
	case "daten_mod_delete_multi":
		$ids = $email_rec = array();
		$event_text = "";

		if($do_action == "daten_mod_delete") {
			$ids[] = format_userinput($_POST["id"], "uint");
		}
		else if($do_action == "daten_mod_delete_multi") {
			foreach($_POST["chk"] as $c_i => $c) {
				if($c) $ids[] = format_userinput($c_i, "uint");
			}
		}
		if(!$ids[0]) continue;

		$notification = format_userinput($_POST["mod_confirm"], "alpha", FALSE, 5) == "true";

		foreach($ids as $id) {
			$mod_event = db_select_data("ko_event_mod", "WHERE `id` = '$id'", "*", "", "", TRUE);
			if(!$mod_event["eventgruppen_id"]) continue;
			if($access['daten'][$mod_event["eventgruppen_id"]] < 4 && $mod_event["_user_id"] != $_SESSION["ses_userid"]) continue;

			//Delete mod entry
			db_delete_data("ko_event_mod", "WHERE `id` = '$id'");

			//Logging
			$mod_event["notification"] = $notification ? getLL("yes") : getLL("no");
			ko_log_diff("event_mod_delete", $mod_event);

			if($notification) {
				$person = db_select_data("ko_admin AS a LEFT JOIN ko_leute AS l ON a.leute_id = l.id", "WHERE a.id = '".$mod_event["_user_id"]."'", "a.id AS id,l.email AS email", "", "", TRUE);
				if($person["email"]) $email_rec[] = $person["email"];
				$event_text .= ko_daten_infotext($mod_event);
			}
		}

		if($notification) {
			$smarty->assign("txt_empfaenger", implode(", ", array_unique($email_rec)));
			$smarty->assign('txt_empfaenger_semicolon', implode('; ', array_unique($email_rec)));
			$p = ko_get_logged_in_person();
      $smarty->assign("tpl_show_bcc_an_mich", ($p["email"] ? TRUE : FALSE));
      $smarty->assign("tpl_show_send", TRUE);
			$smarty->assign('txt_betreff', ('[kOOL] '.(sizeof($ids) > 1 ? getLL('daten_emails_mod_delete_subject') : getLL('daten_email_mod_delete_subject'))) );

			$smarty->assign('txt_emailtext', ((sizeof($ids) > 1 ? getLL('daten_emails_mod_delete_text') : getLL('daten_email_mod_delete_text'))."\n\n".ko_html($event_text)) );

			$smarty->assign("tpl_show_rec_link", TRUE);
			$_SESSION["show"]= "email_confirm";
		}

	break;



	//Perfom a moderated deletion (delete the event associated with the given mod event)
	case "daten_mod_delete_approve":
	case "daten_mod_delete_approve_multi":
		$email_rec = $ids = array();
		$event_text = "";

		if($do_action == "daten_mod_delete_approve") {
			$ids[] = format_userinput($_POST["id"], "uint");
		}
		else if($do_action == "daten_mod_delete_approve_multi") {
			foreach($_POST["chk"] as $c_i => $c) {
				if($c) $ids[] = format_userinput($c_i, "uint");
			}
		}
		if(!$ids[0]) continue;

		$notification = format_userinput($_POST["mod_confirm"], "alpha", FALSE, 5) == "true";

		foreach($ids as $id) {
			//Get event moderation
			$mod_event = db_select_data("ko_event_mod", "WHERE `id` = '$id'", "*", "", "", TRUE);
			if(!$mod_event["eventgruppen_id"] || $access['daten'][$mod_event["eventgruppen_id"]] < 4 || !$mod_event["_event_id"]) continue;
			
			//Delete event
			do_del_termin($mod_event["_event_id"]);

			//Store recipient email address for notification
			if($notification) {
				$person = db_select_data("ko_admin AS a LEFT JOIN ko_leute AS l ON a.leute_id = l.id", "WHERE a.id = '".$mod_event["_user_id"]."'", "a.id AS id,l.email AS email", "", "", TRUE);
				if($person["email"]) $email_rec[] = $person["email"];
				$event_text .= ko_daten_infotext($mod_event);
			}
		}

		if($notification) {
			$smarty->assign("txt_empfaenger", implode(", ", array_unique($email_rec)));
			$smarty->assign('txt_empfaenger_semicolon', implode('; ', array_unique($email_rec)));
			$p = ko_get_logged_in_person();
      $smarty->assign("tpl_show_bcc_an_mich", ($p["email"] ? TRUE : FALSE));
      $smarty->assign("tpl_show_send", TRUE);
			$smarty->assign("txt_betreff", ("[kOOL] ".getLL("daten_email_mod_delete_confirm_subject")) );

			$smarty->assign("txt_emailtext", (getLL("daten_email_mod_delete_confirm_text")."\n\n".ko_html($event_text)) );

			$smarty->assign("tpl_show_rec_link", TRUE);
			$_SESSION["show"]= "email_confirm";
		}
	break;



	//Perfom a moderated edit (update the event associated with the given mod event according to the given data)
	case "daten_mod_edit_approve":
	case "daten_mod_edit_approve_multi":
		$email_rec = $ids = array();
		$event_text = "";

		if($do_action == "daten_mod_edit_approve") {
			$ids[] = format_userinput($_POST["id"], "uint");
		}
		else if($do_action == "daten_mod_edit_approve_multi") {
			foreach($_POST["chk"] as $c_i => $c) {
				if($c) $ids[] = format_userinput($c_i, "uint");
			}
		}
		if(!$ids[0]) continue;

		$notification = format_userinput($_POST["mod_confirm"], "alpha", FALSE, 5) == "true";

		foreach($ids as $id) {
			$mod_event = db_select_data("ko_event_mod", "WHERE `id` = '$id'", "*", "", "", TRUE);
			if(!$mod_event["eventgruppen_id"] || $access['daten'][$mod_event["eventgruppen_id"]] < 4 || !$mod_event["_event_id"]) continue;
			
			$errorOut = ko_daten_update_event($mod_event["_event_id"], $mod_event);
			if(!$errorOut) {
				$notifier->addInfo(2, $do_action);
			}
			db_delete_data("ko_event_mod", "WHERE `id` = '$id'");

			//Store recipient email address for notification
			if($notification) {
				$person = db_select_data("ko_admin AS a LEFT JOIN ko_leute AS l ON a.leute_id = l.id", "WHERE a.id = '".$mod_event["_user_id"]."'", "a.id AS id,l.email AS email", "", "", TRUE);
				if($person["email"]) $email_rec[] = $person["email"];
				$event_text .= ko_daten_infotext($mod_event);
			}
		}

		if($notification) {
			$smarty->assign("txt_empfaenger", implode(", ", array_unique($email_rec)));
			$smarty->assign('txt_empfaenger_semicolon', implode('; ', array_unique($email_rec)));
			$p = ko_get_logged_in_person();
      $smarty->assign("tpl_show_bcc_an_mich", ($p["email"] ? TRUE : FALSE));
      $smarty->assign("tpl_show_send", TRUE);
			$smarty->assign("txt_betreff", ("[kOOL] ".getLL("daten_email_mod_edit_confirm_subject")) );

			$smarty->assign("txt_emailtext", (getLL("daten_email_mod_edit_confirm_text")."\n\n".ko_html($event_text)) );

			$smarty->assign("tpl_show_rec_link", TRUE);
			$_SESSION["show"]= "email_confirm";
		}
	break;




	//Send email
	case 'submit_email':
		$p = ko_get_logged_in_person();
		$email = $p['email'] ? $p['email'] : ko_get_setting('info_email');

		if($_POST['rd_bcc_an_mich'] == 'ja') $_POST['txt_bcc'] .= ($_POST['txt_bcc'] == '') ? $email : ', '.$email;

		$headers = array('From' => $email);
		if($_POST["txt_cc"] != "") $headers["CC"] = explode(',', (str_replace(";", ",", $_POST["txt_cc"])));
		if($_POST["txt_bcc"] != "") $headers["BCC"] = explode(',', nl2br(str_replace(";", ",", $_POST["txt_bcc"])));

		$recipients = explode(',', str_replace(";", ",", $_POST["txt_empfaenger"]));
		array_walk($recipients, create_function('&$val', '$val = trim($val);'));

		// remove trailing whitespaces
		array_walk($headers['CC'], create_function('&$val', '$val = trim($val);'));

		// remove trailing whitespaces
		array_walk($headers['BCC'], create_function('&$val', '$val = trim($val);'));

		$text = ko_emailtext($_POST["txt_emailtext"]);

		ko_send_mail(
			$email,
			$recipients,
			$_POST["txt_betreff"],
			ko_emailtext($_POST['txt_emailtext']),
			array(),
			$headers['CC'],
			$headers['BCC']
		);

		if (!$notifier->hasErrors()) {
			$notifier->addInfo(11, $do_action);
			ko_log('event_mod_email', '"'.format_userinput($_POST['txt_betreff'], 'text').'": '.format_userinput($_POST['txt_empfaenger'], 'text').', cc: '.format_userinput($_POST['txt_cc'], 'text').', bcc: '.format_userinput($_POST['txt_bcc'], 'text'));
		}

		$_SESSION['show'] = $_SESSION['show_back'] ? $_SESSION['show_back'] : 'list_events_mod';
	break;


	// TODO: Access?!
	case 'list_reminders':
		if($access['daten']['REMINDER'] < 1) continue;

		$_SESSION['show_start'] = 1;
		$_SESSION['show'] = 'list_reminders';
		break;
	case 'new_reminder':
		if($access['daten']['REMINDER'] < 1) continue;

		$_SESSION['show'] = 'new_reminder';
		$onload_code = 'form_set_first_input();'.$onload_code;
		break;
	case 'submit_new_event_reminder':
		if($access['daten']['REMINDER'] < 1) continue;

		if($do_action == 'submit_new_event_reminder') $_POST['kota_type'] = 1;
		$newId = kota_submit_multiedit('', 'new_reminder');
		if($do_action == 'submit_new_event_reminder') {
			db_update_data('ko_reminder', "WHERE `id` = '$newId'", array('type' => '1'));
		}
		if(!$notifier->hasErrors()) {
			$notifier->addInfo(14);
			$_SESSION['show'] = 'list_reminders';
			ko_include_kota('ko_reminder');
		}
		break;
	//Edit
	case 'edit_reminder':
		if($access['daten']['REMINDER'] < 1) continue;
		$editId = format_userinput($_POST['id'], 'uint');
		if (!ko_get_reminder_access($editId)) continue;

		$_SESSION['show'] = 'edit_reminder';
		$onload_code = 'form_set_first_input();'.$onload_code;
	break;


	case 'submit_edit_event_reminder':
		if($access['daten']['REMINDER'] < 1) continue;

		list($t1, $t2, $editId) = explode('@', $_POST['id']);
		if (!ko_get_reminder_access($editId)) continue;

		if($do_action == 'submit_edit_event_reminder') $_POST['kota_type'] = 1;
		kota_submit_multiedit('', 'edit_reminder');
		if(!$notifier->hasErrors()) {
			$notifier->addInfo(15);
			$_SESSION['show'] = 'list_reminders';
			ko_include_kota('ko_reminder');
		}
	break;



	case 'delete_reminder':
		if($access['daten']['REMINDER'] < 1) continue;
		$id = format_userinput($_POST['id'], 'uint');
		ko_get_reminders($entry, 1, ' and `id` = ' . $id, '', '', TRUE, TRUE);
		if(!$entry['id'] || $entry['id'] != $id) continue;

		db_delete_data('ko_reminder', "WHERE `id` = '$id'");
		ko_log_diff('del_reminder', $entry);
		$notifier->addInfo(16);
	break;



	case 'send_test_reminder':
		if($access['daten']['REMINDER'] < 1) continue;

		$id = format_userinput($_POST['id'], 'uint');
		ko_get_reminders($entry, 1, ' and `id` = ' . $id, '', '', TRUE, TRUE);
		if(!$entry['id'] || $entry['id'] != $id) continue;

		$done = ko_task_reminder($id);

		if (!$notifier->hasErrors()) {
			$notifier->addInfo(17, '', array(implode(', ', $done)));
		}
	break;





	case 'export_xls_daten':
		if($access['daten']['MAX'] < 1 || $_SESSION['ses_userid'] == ko_get_guest_id()) continue;

		//Get selected columns from GET
		$cols = $_POST['sel_xls_cols'] ? $_POST['sel_xls_cols'] : $_GET['sel_xls_cols'];

		if($cols == '_session') {
			$value = implode(',', $_SESSION['kota_show_cols_ko_event']);
		} else {
			//Get preset from userprefs
			$name = format_userinput($cols, 'js', FALSE, 0, array(), '@');
			if($name == '') continue;
			if(substr($name, 0, 3) == '@G@') $preset = ko_get_userpref('-1', substr($name, 3), 'ko_event_colitemset');
			else $preset = ko_get_userpref($_SESSION['ses_userid'], $name, 'ko_event_colitemset');
			$value = $preset[0]['value'];
		}
		//Fallback to default columns
		if($value == '') $value = implode(',', $KOTA['ko_event']['_listview_default']);


		//Store currently displayed columns
		$orig_cols = $_SESSION['kota_show_cols_ko_event'];
		$_SESSION['kota_show_cols_ko_event'] = explode(',', $value);

		//Export with the selected columns
		$filename = ko_list_events('all', TRUE, 'xls', TRUE);
		$onload_code = "ko_popup('".$ko_path."download.php?action=file&amp;file=".substr($filename, 3)."');";

		//Restore column
		$_SESSION['kota_show_cols_ko_event'] = $orig_cols;
	break;




	case 'export_pdf':
		if($access['daten']['MAX'] < 1 || $_SESSION['ses_userid'] == ko_get_guest_id()) continue;

		list($mode, $inc) = explode('-', $_GET['mode']);
		switch($mode) {
			case 'd':
				$inc = intval($inc);
				$start = add2date(date('Y-m-d'), 'day', $inc, TRUE);
				$filename = ko_export_cal_weekly_view('daten', 1, $start);
				$onload_code = "ko_popup('".$ko_path."download.php?action=file&amp;file=download/pdf/".$filename."');";
			break;

			case 'w':
				$inc = intval($inc);
				$start = date_find_last_monday(date('Y-m-d'));
				$start = add2date($start, 'week', $inc, TRUE);
				$filename = ko_export_cal_weekly_view('daten', 7, $start);
				$onload_code = "ko_popup('".$ko_path."download.php?action=file&amp;file=download/pdf/".$filename."');";
			break;

			case 'm':
				$inc = intval($inc);
				$start = add2date(date('Y-m-d'), 'month', $inc, TRUE);
				$filename = basename(ko_daten_export_months(1, date('m', strtotime($start)), date('Y', strtotime($start))));
				$onload_code = "ko_popup('".$ko_path."download.php?action=file&amp;file=download/pdf/".$filename."');";
			break;

			case 'y':
				$inc = intval($inc);
				$filename = basename(ko_export_cal_pdf_year('daten', 1, (int)date('Y')+$inc));
				$onload_code = "ko_popup('".$ko_path."download.php?action=file&amp;file=download/pdf/".$filename."');";
			break;

			case 's':
				list($inc, $month) = explode(':', $inc);
				$inc = intval($inc);
				$month = intval($month);
				$filename = basename(ko_export_cal_pdf_year('daten', $month, (int)date('Y')+$inc, 6));
				$onload_code = "ko_popup('".$ko_path."download.php?action=file&amp;file=download/pdf/".$filename."');";
			break;

			case '12m':
				$inc = intval($inc);
				$filename = basename(ko_daten_export_months(12, 1, (int)date('Y')+$inc));
				$onload_code = "ko_popup('".$ko_path."download.php?action=file&amp;file=download/pdf/".$filename."');";
			break;

			case 'newpreset':
				if($access['daten']['MAX'] > 3) {
					$_SESSION['show'] = 'new_export_preset';
				}
			break;

			case 'listpresets':
				if($access['daten']['MAX'] > 3) {
					$_SESSION['show'] = 'list_export_presets';
				}
			break;


			//Handle selection of presets
			default:
				if(substr($mode, 0, 6) == 'preset') {
					$id = intval(substr($mode, 6));
					if(!$id) break;
					$preset = db_select_data('ko_pdf_layout', "WHERE `id` = '$id' AND `type` = 'daten'", '*', '', '', TRUE);
					if($preset['id'] > 0 && $preset['id'] == $id) {
						$filename = basename(ko_daten_export_preset(unserialize($preset['data'])));
						$onload_code = "ko_popup('".$ko_path."download.php?action=file&amp;file=download/pdf/".$filename."');";
					}
				}
		}
	break;



	case 'listpresets':
		if($access['daten']['MAX'] < 4) break;

		$_SESSION['show'] = 'list_export_presets';
	break;


	case 'new_export_preset':
		if($access['daten']['MAX'] < 4) break;

		$_SESSION['show'] = 'new_export_preset';
	break;


	case 'edit_export_preset':
		if($access['daten']['MAX'] < 4) break;

		$edit_id = format_userinput($_POST['id'], 'uint');
		if(!$edit_id) break;
		$_SESSION['show'] = 'edit_export_preset';
	break;



	case 'submit_new_export_preset':
		if($access['daten']['MAX'] < 4) break;

		$preset = $_POST['preset'];
		$data = array('type' => 'daten', 'name' => $preset['name'], 'data' => serialize($preset));
		db_insert_data('ko_pdf_layout', $data);

		ko_log_diff('new_event_export_preset', $data);

		$notifier->addInfo(12, $do_action);
		$_SESSION['show'] = 'list_export_presets';
	break;



	case 'submit_edit_export_preset':
		if($access['daten']['MAX'] < 4) break;

		$id = format_userinput($_POST['preset_id'], 'uint');
		if(!$id) break;
		$old = db_select_data('ko_pdf_layout', "WHERE `id` = '$id' AND `type` = 'daten'", '*', '', '', TRUE);
		if($old['id'] > 0 && $old['id'] == $id) {
			$preset = $_POST['preset'];
			$data = array('type' => 'daten', 'name' => $preset['name'], 'data' => serialize($preset));
			db_update_data('ko_pdf_layout', "WHERE `id` = '$id'", $data);

			ko_log_diff('edit_event_export_preset', $data, $old);

			$notifier->addInfo(12, $do_action);
			$_SESSION['show'] = 'list_export_presets';
		}
	break;


	case 'delete_export_preset':
		if($access['daten']['MAX'] < 4) break;

		$id = format_userinput($_POST['id'], 'uint');
		if(!$id) break;
		$old = db_select_data('ko_pdf_layout', "WHERE `id` = '$id' AND `type` = 'daten'", '*', '', '', TRUE);
		if($old['id'] > 0 && $old['id'] == $id) {
			db_delete_data('ko_pdf_layout', "WHERE `id` = '$id'");
			ko_log_diff('del_event_export_preset', $data, $old);

			$notifier->addInfo(13, $do_action);
			$_SESSION['show'] = 'list_export_presets';
		}
	break;



	//Backwards compatibility
	case 'set_no_filter':
	break;



	//Submenus
	case "move_sm_left":
	case "move_sm_right":
		ko_submenu_actions("daten", $do_action);
	break;




	default:
		if(!hook_action_handler($do_action))
      include($ko_path."inc/abuse.inc");
	break;
}//switch(do_action)


//HOOK: Plugins erlauben, die bestehenden Actions zu erweitern
hook_action_handler_add($do_action);



//Reread access rights if necessary
if(in_array($do_action, array('submit_neue_gruppe', 'submit_edit_gruppe', 'delete_gruppe', 'submit_new_ical'))) {
	ko_get_access('daten', '', TRUE);
}





//***Defaults einlesen
if(!isset($_SESSION["show_tg"]) || $_SESSION["show_tg"]== "") {
	$show_tg_string = ko_get_userpref($_SESSION["ses_userid"], "show_daten_tg");
	if($show_tg_string) {
		$_SESSION["show_tg"]= explode(",", $show_tg_string);
	} else {
		ko_get_eventgruppen($grps);
		$_SESSION["show_tg"] = array_keys($grps);
	}
}

$_SESSION['show_limit']= ko_get_userpref($_SESSION['ses_userid'], 'show_limit_daten');
if(!$_SESSION['show_limit']) $_SESSION['show_limit'] = ko_get_setting('show_limit_daten');

if(!$_SESSION['show_start']) {
	$_SESSION['show_start'] = 1;
}
if($_SESSION['sort_events'] == '') {
	$_SESSION['sort_events'] = 'startdatum';
	$_SESSION['sort_events_order'] = 'ASC';
}
if($_SESSION['sort_tg'] == '') {
	$_SESSION['sort_tg']= 'name';
	$_SESSION['sort_tg_order'] = 'ASC';
}
if($_SESSION['cal_tag'] == '') {
	$_SESSION['cal_tag'] = strftime('%d', time());
}
if($_SESSION['cal_monat'] == '') {
	$_SESSION['cal_monat'] = strftime('%m', time());
}
if($_SESSION['cal_jahr'] == '') {
	$_SESSION['cal_jahr'] = strftime('%Y', time());
}
if($_SESSION['cal_view'] == '') {
	$userpref = ko_get_userpref($_SESSION['ses_userid'], 'default_view_daten');
	if($userpref == 'show_cal_woche') $_SESSION['cal_view'] = 'agendaWeek';
	else if($userpref == 'show_cal_monat') $_SESSION['cal_view'] = 'month';
	else $_SESSION['cal_view'] = 'month';
}
if($_SESSION['cal_woche'] == '') {
	$_SESSION['cal_woche'] = strftime('%V', time());
}
if($_SESSION['cal_woche_jahr'] == '') {
	$_SESSION['cal_woche_jahr'] = strftime('%Y', time());
}
if($_SESSION['cal_jahr_jahr'] == '') {
	$_SESSION['cal_jahr_jahr'] = strftime('%Y', time());
}
if($_SESSION['cal_jahr_start'] == '') {
	$_SESSION['cal_jahr_start'] = 1;
	$num = (int)ko_get_userpref($_SESSION['ses_userid'], 'cal_jahr_num');
	if($num == 0) $num = ko_get_setting('cal_jahr_num');
	get_heute($h_tag, $h_monat, $h_jahr);
	while( ((int)$_SESSION['cal_jahr_start']+$num-1) < $h_monat) {
		$_SESSION['cal_jahr_start'] += $num;
	}
}
if($_SESSION['filter_start'] == '') {
	$_SESSION['filter_start'] = 'today';
	$_SESSION['filter_ende'] = 'immer';
}
$_SESSION['show_birthdays'] = ko_get_userpref($_SESSION['ses_userid'], 'show_birthdays');
if(!isset($_SESSION['show_birthdays'])) $_SESSION['show_birthdays'] = FALSE;

//Include submenus
ko_set_submenues();
?>
<!DOCTYPE html 
  PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php print $_SESSION["lang"]; ?>" lang="<?php print $_SESSION["lang"]; ?>">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
<title><?php print "$HTML_TITLE: ".getLL("module_".$ko_menu_akt); ?></title>
<?php
$js_files = array($ko_path.'inc/jquery/jquery.js', $ko_path.'inc/kOOL.js');
//Color picker for event group form
if(in_array($_SESSION['show'], array('neue_gruppe', 'edit_gruppe', 'new_googlecal', 'edit_googlecal', 'new_ical'))) {
	print '<script language="javaScript" type="text/javascript">ko_path = \''.$ko_path.'\'</script>';
	$js_files[] = $ko_path.'inc/ColorPicker2.js';
}
if($_SESSION['show'] == 'calendar') {
	$js_files[] = $ko_path.'inc/jquery/jquery-ui.js';
	$js_files[] = $ko_path.'inc/js-fullcalendar.min.js';
}
$js_files[] = $ko_path.'inc/ckeditor/ckeditor.js';
$js_files[] = $ko_path.'inc/ckeditor/adapters/jquery.js';

print ko_include_js($js_files);

if($_SESSION['show'] == 'calendar') {
	print '<link rel="stylesheet" type="text/css" href="'.$ko_path.'inc/fullcalendar.css?'.filemtime($ko_path.'inc/fullcalendar.css').'" />';
}
print ko_include_css();
include($ko_path.'inc/js-sessiontimeout.inc');
include("inc/js-daten.inc");

//Include JS from rota module when editing an event
if(in_array($_SESSION['show'], array('edit_termin')) && ko_module_installed('rota')) include($ko_path.'rota/inc/js-rota.inc');
if(in_array($_SESSION["show"], array("neuer_termin"))) include("inc/js-seleventgroup.inc");
$js_calendar->load_files();
?>
</head>

<body onload="session_time_init();<?php if(isset($onload_code)) print $onload_code; ?>">

<?php
/*
 * Gibt bei erfolgreichem Login das Menü aus, sonst einfach die Loginfelder
 */
include($ko_path . "menu.php");
?>


<table width="100%">
<tr> 


<td class="main_left" name="main_left" id="main_left">
<?php
print ko_get_submenu_code("daten", "left");
?>
</td>


<!-- Hauptbereich -->
<td class="main">
<form action="index.php" method="post" name="formular" enctype="multipart/form-data">
<input type="hidden" name="action" id="action" value="" />
<input type="hidden" name="id" id="id" value="" />
<input type="hidden" name="mod_confirm" id="mod_confirm" value="" />  <!-- Confirm a moderated reservation -->
<input type="hidden" name="new_date" id="new_date" value="" />  <!-- Neuer Termin an Datum -->
<div name="main_content" id="main_content">

<?php
if($notifier->hasNotifications(koNotifier::ALL)) {
	$notifier->notify();
}

hook_show_case_pre($_SESSION["show"]);

switch($_SESSION["show"]) {
	case "all_events":
		ko_list_events("all");
	break;

	case "list_events_mod":
		ko_list_events("mod");
	break;

	case "all_groups":
		ko_list_groups("all");
	break;

 	case "neuer_termin":
		ko_formular_termin("neu", "", $new_event_data);
 	break;

 	case "edit_termin":
		ko_formular_termin("edit", $edit_id);
	break;

	case "neue_gruppe":
		ko_formular_termingruppe("neu");
	break;

	case "edit_gruppe":
		ko_formular_termingruppe("edit", $edit_id);
	break;

	case 'list_reminders':
		ko_list_reminders();
	break;

	case 'new_reminder':
		ko_formular_reminder('new');
	break;

	case 'edit_reminder':
		ko_formular_reminder('edit', $editId);
	break;

	case 'new_googlecal':
		ko_formular_googlecal('new');
	break;

	case 'edit_googlecal':
		ko_formular_googlecal('edit', $edit_id);
	break;

	case 'new_ical':
		ko_formular_ical('new');
	break;

	case "calendar":
		ko_daten_calendar();
	break;

	case "cal_jahr":
		$num = (int)ko_get_userpref($_SESSION["ses_userid"], "cal_jahr_num");
		if($num == 0) $num = 6;  //Default
		ko_daten_cal_jahr($num, $_SESSION["cal_jahr_start"]);
	break;

	case "multiedit":
		ko_multiedit_formular("ko_event", $do_columns, $do_ids, $order, array("cancel" => "all_events"));
	break;
	
	case "multiedit_tg":
		ko_multiedit_formular("ko_eventgruppen", $do_columns, $do_ids, $order, array("cancel" => "all_groups"));
	break;

	case "daten_settings":
		ko_daten_settings();
	break;

	case "email_confirm":
		$smarty->assign("tpl_title1", getLL('leute_email_title1'));
		$smarty->assign("tpl_body1", getLL('leute_email_body1'));
		$smarty->assign("tpl_all_recip", getLL('leute_email_all_recipients'));
		$smarty->assign('tpl_all_recip_semicolon', getLL('leute_email_all_recipients_semicolon'));
		$smarty->assign("tpl_no_email", getLL('leute_email_no_email'));
		$smarty->assign("tpl_xls_file", getLL('leute_email_xls_file'));
		$smarty->assign("tpl_title2", getLL('leute_email_title2'));
		$smarty->assign("tpl_body2", getLL('leute_email_body2'));
		$smarty->assign("tpl_more", getLL('leute_email_more'));
		$smarty->assign("tpl_to", getLL('leute_email_to'));
		$smarty->assign("tpl_cc", getLL('leute_email_cc'));
		$smarty->assign("tpl_bcc", getLL('leute_email_bcc'));
		$smarty->assign("tpl_subject", getLL('leute_email_subject'));
		$smarty->assign("tpl_text", getLL('leute_email_text'));
		$smarty->assign("tpl_bcc_me", getLL('leute_email_bcc_me'));
		$smarty->assign("tpl_yes", getLL('yes'));
		$smarty->assign("tpl_no", getLL('no'));
		$smarty->assign("tpl_send", getLL('leute_email_send'));
		$smarty->assign("tpl_error_no_subject", getLL("leute_email_error_no_subject"));
		$smarty->display("ko_formular_email.tpl");
	break;

	case 'ical_links':
		ko_daten_ical_links();
	break;

	case 'new_export_preset':
		ko_daten_export_preset_form('new');
	break;

	case 'edit_export_preset':
		ko_daten_export_preset_form('edit', $edit_id);
	break;

	case 'list_export_presets':
		ko_daten_list_export_presets();
	break;


	default:
		//HOOK: Plugins erlauben, neue Show-Cases zu definieren
    hook_show_case($_SESSION["show"]);
  break;

}//switch(show)

//HOOK: Plugins erlauben, die bestehenden Show-Cases zu erweitern
hook_show_case_add($_SESSION["show"]);

?>
</div>
</form>
</td>

<td class="main_right" name="main_right" id="main_right">

<?php
print ko_get_submenu_code("daten", "right");
?>

</td>
</tr>

<?php include($ko_path . "footer.php"); ?>

</table>

</body>
</html>
