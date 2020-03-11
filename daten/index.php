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

header('Content-Type: text/html; charset=ISO-8859-1');

ob_start();  //Ausgabe-Pufferung einschalten

$ko_path = "../";
$ko_menu_akt = "daten";

include_once($ko_path . "inc/ko.inc");
include_once($ko_path . 'consensus/consensus.inc');
include_once("inc/daten.inc");
if(ko_module_installed("reservation"))
	include_once("../reservation/inc/reservation.inc");
if(ko_module_installed("rota"))
	include_once("../rota/inc/rota.inc");

$notifier = koNotifier::Instance();

//Redirect to SSL if needed
ko_check_ssl();

if(!ko_module_installed("daten")) {
	header("Location: ".$BASE_URL."index.php"); exit;
}

ob_end_flush();  //Puffer flushen

//***Rechte auslesen:
ko_get_access('daten');


//kOOL Table Array
ko_include_kota(array('ko_event', 'ko_eventgruppen', 'ko_reservation', 'ko_pdf_layout', 'ko_reminder', 'ko_event_rooms', 'ko_event_absence'));



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

if(FALSE === format_userinput($do_action, "alpha+", TRUE)) trigger_error("invalid action: ".$do_action, E_USER_ERROR);

//Reset show_start if from another module
if($_SERVER['HTTP_REFERER'] != '' && FALSE === strpos($_SERVER['HTTP_REFERER'], '/'.$ko_menu_akt.'/')) $_SESSION['show_start'] = 1;

// Fallback if user came from reservation timeline-view #2636
if(substr($_SESSION['cal_view'],0,8) == "timeline") {
	$do_action = ko_get_userpref($_SESSION["ses_userid"], "default_view_daten");
}


if($_GET['kota_filter']) {
	list($field, $value) = explode(':', $_GET['kota_filter']);
	$field = format_userinput($field, 'js');
	if(isset($KOTA['ko_event'][$field])) {
		$filterOK = FALSE;
		foreach($KOTA['ko_event']['_listview'] as $k => $v) {
			if($v['name'] == $field && $v['filter'] === TRUE) $filterOK = TRUE;
		}
		if($filterOK) {
			$_SESSION['kota_filter']['ko_event'] = array($field => array($value));
		}
	}
}


switch($do_action) {

	case "set_absence_ical_url":
		if (!($access['daten']['ABSENCE'] < 1 || (($leute_id = ko_get_logged_in_id()) == 0 && $access['daten']['ABSENCE'] < 2))) {
			if(empty($_POST['absence_ical_url']) || preg_match("%^((https?://)|(www\.))([a-z0-9-].?)+(:[0-9]+)?(/.*)?$%i", $_POST['absence_ical_url'])) {
				ko_save_userpref($_SESSION['ses_userid'], 'absence_ical_url', format_userinput($_POST['absence_ical_url'], 'text'));
				ko_daten_import_absences();
			} else {
				$notifier->addTextError(getLL("daten_settings_absence_ical_url_error"));
			}
		}
		$_SESSION['show'] = 'list_absence';
		break;

	//Filter
	case "set_filter_today":
		$_SESSION['filter_start'] = 'today';
		$_SESSION['filter_ende'] = 'immer';

		ko_save_userpref($_SESSION['ses_userid'], 'daten_filter_start', $_SESSION['filter_start']);
		ko_save_userpref($_SESSION['ses_userid'], 'daten_filter_ende', $_SESSION['filter_ende']);
	break;


	case "submit_filter":
		$start = ko_daten_parse_time_filter(format_userinput($_POST["daten_filter"]['date1'], "text"), 'today', FALSE);
		if ($start === NULL) {
			$notifier->addError(22);
			break;
		} else $_SESSION["filter_start"] = $start;

		$end = ko_daten_parse_time_filter(format_userinput($_POST["daten_filter"]['date2'], "text"), '', FALSE);
		if ($end === NULL) {
			$notifier->addError(22);
			break;
		} else $_SESSION["filter_ende"] = $end;

		ko_save_userpref($_SESSION['ses_userid'], 'daten_filter_start', $_SESSION['filter_start']);
		ko_save_userpref($_SESSION['ses_userid'], 'daten_filter_ende', $_SESSION['filter_ende']);
	break;


	case "unset_perm_filter":
		if($access['daten']['MAX'] > 3) {
			ko_set_setting("daten_perm_filter_start", "");
			ko_set_setting("daten_perm_filter_ende", "");
		}
	break;


	case 'set_perm_filter':
		if($access['daten']['MAX'] > 3) {
			$pfs = ko_daten_parse_time_filter($_SESSION['filter_start']);
			$pfe = ko_daten_parse_time_filter($_SESSION['filter_ende'], '');

			ko_set_setting('daten_perm_filter_start', $pfs);
			ko_set_setting('daten_perm_filter_ende', $pfe);
		}
	break;





	//Löschen
	case 'delete_termin':
		if(FALSE === ($del_id = format_userinput($_POST['id'], 'uint', TRUE))) {
			trigger_error('Not allowed del_id: '.$_POST['id'], E_USER_ERROR);
		}
		if(!$del_id) {
			if(FALSE === ($del_id = format_userinput($_GET['id'], 'uint', TRUE))) {
				trigger_error('Not allowed del_id: '.$_GET['id'], E_USER_ERROR);
			}
		}
		if(!$del_id) break;

		$event = db_select_data('ko_event', "WHERE `id` = '$del_id'", '*', '', '', TRUE);
		if($access['daten'][$event['eventgruppen_id']] < 2) break;

		$mode = do_del_termin($del_id);
		if($mode == 'del') $notifier->addInfo(3, $do_action);
		else $notifier->addInfo(10, $do_action);
	break;



	//Ausgewählte Termine löschen
	case "del_selected":
		$failed = FALSE;
		foreach($_POST["chk"] as $c_i => $c) {
			if($c) {
				if(FALSE === ($del_id = format_userinput($c_i, "uint", TRUE))) {
					trigger_error("Not allowed del_id (multiple): ".$c_i, E_USER_ERROR);
				}
				$event = db_select_data('ko_event', "WHERE `id` = '$del_id'", '*', '', '', TRUE);
				if($access['daten'][$event['eventgruppen_id']] > 1) $success = do_del_termin($del_id);
				else $success = FALSE;
				$failed = $failed || !$success;
			}
		}
		if (!$failed) $notifier->addInfo(7);
		else $notifier->addWarning(2);
	break;



	//Termingruppe löschen
	case "delete_gruppe":
		if(FALSE === ($del_id = format_userinput($_POST["id"], "uint", TRUE))) {
			trigger_error("Not allowed del_id: ".$_POST["id"], E_USER_ERROR);
		}

		//Check for ALL rights to be able to delete
		if($access['daten']['ALL'] < 3) break;

		//Get old entry for logging
		ko_get_eventgruppe_by_id($del_id, $del_eventgruppe);

		//Delete group
		db_delete_data("ko_eventgruppen", "WHERE `id` = '$del_id'");
		ko_log_diff('delete_termingruppe', $del_eventgruppe);

		//Alle Termine dieser Termingruppe löschen (inkl. zugehöriger Reservationen)
		$rows = db_select_data("ko_event", "WHERE `eventgruppen_id` = '$del_id'");
		foreach($rows as $row) {
			do_del_termin(format_userinput($row["id"], "uint"));
		}

		//Check for empty calendars
		ko_delete_empty_calendars();

		ko_taxonomy_delete_node($del_id, "ko_eventgruppen");

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
		if($access['daten']['MAX'] < 1) break;
		$_SESSION['show_back'] = $_SESSION['show'];
		$_SESSION['show'] = 'daten_settings';
	break;

	case "submit_daten_settings":
		if($access['daten']['MAX'] < 1 || $_SESSION['ses_userid'] == ko_get_guest_id()) break;

		//Userprefs for logged in user
		ko_save_userpref($_SESSION['ses_userid'], 'default_view_daten', format_userinput($_POST['sel_daten'], 'js'));
		ko_save_userpref($_SESSION['ses_userid'], 'show_limit_daten', format_userinput($_POST['txt_limit_daten'], 'uint'));
		ko_save_userpref($_SESSION['ses_userid'], 'cal_woche_start', min(23, format_userinput($_POST['txt_cal_woche_start'], 'uint')));
		ko_save_userpref($_SESSION['ses_userid'], 'cal_woche_end', min(24, format_userinput($_POST['txt_cal_woche_end'], 'uint')));
		ko_save_userpref($_SESSION['ses_userid'], 'daten_monthly_title', format_userinput($_POST['sel_monthly_title'], 'js', FALSE));
		ko_save_userpref($_SESSION['ses_userid'], 'daten_pdf_show_time', format_userinput($_POST['sel_pdf_show_time'], 'uint', FALSE, 1));
		ko_save_userpref($_SESSION['ses_userid'], 'daten_pdf_use_shortname', format_userinput($_POST['sel_pdf_use_shortname'], 'uint', FALSE, 1));
		ko_save_userpref($_SESSION['ses_userid'], 'daten_export_show_legend', format_userinput($_POST['sel_export_show_legend'], 'uint', FALSE, 1));
		ko_save_userpref($_SESSION['ses_userid'], 'daten_name_in_pdffooter', (format_userinput($_POST['sel_name_in_pdffooter'], 'uint', FALSE, 1) == 1 ? 1 : 0));
		ko_save_userpref($_SESSION['ses_userid'], 'daten_pdf_week_start', format_userinput($_POST['sel_pdf_week_start'], 'uint', FALSE, 1));
		ko_save_userpref($_SESSION['ses_userid'], 'daten_pdf_week_length', format_userinput($_POST['sel_pdf_week_length'], 'uint', FALSE, 2));
		ko_save_userpref($_SESSION['ses_userid'], 'daten_mark_sunday', format_userinput($_POST['sel_mark_sunday'], 'uint', FALSE, 1));
		ko_save_userpref($_SESSION['ses_userid'], 'daten_no_cals_in_itemlist', format_userinput($_POST['sel_no_cals_in_itemlist'], 'uint', FALSE, 1));
		ko_save_userpref($_SESSION['ses_userid'], 'show_birthdays', format_userinput($_POST['sel_show_birthdays'], 'uint', FALSE, 1));
		ko_save_userpref($_SESSION['ses_userid'], 'daten_show_res_in_tooltip', format_userinput($_POST['sel_show_res_in_tooltip'], 'uint', FALSE, 1));
		ko_save_userpref($_SESSION['ses_userid'], 'daten_fm_filter', format_userinput($_POST['sel_fm_filter'], 'text'));
		ko_save_userpref($_SESSION['ses_userid'], 'daten_ical_deadline', format_userinput($_POST['sel_ical_deadline'], 'int', FALSE));
		if($access['daten']['MAX'] > 3) {
			ko_save_userpref($_SESSION['ses_userid'], 'do_mod_email_for_edit_daten', format_userinput($_POST['sel_do_mod_email_for_edit_daten'], 'uint', FALSE, 1));
		}
		ko_save_userpref($_SESSION['ses_userid'], 'daten_ical_description_fields', format_userinput($_POST['sel_ical_description_fields'], 'alphanumlist'));
		ko_save_userpref($_SESSION['ses_userid'], 'daten_tooltip_fields', format_userinput($_POST['sel_tooltip_fields'], 'alphanumlist'));

		if (!($access['daten']['ABSENCE'] < 1 || (($leute_id = ko_get_logged_in_id()) == 0 && $access['daten']['ABSENCE'] < 2))) {
			if(empty($_POST['txt_absence_ical_url']) || preg_match('%^((https?://)|(www\.))([a-z0-9-].?)+(:[0-9]+)?(/.*)?$%i', $_POST['txt_absence_ical_url'])) {
				ko_save_userpref($_SESSION['ses_userid'], 'absence_ical_url', format_userinput($_POST['txt_absence_ical_url'], 'text'));
				ko_daten_import_absences();
			} else {
				$notifier->addTextError(getLL("daten_settings_absence_ical_url_error"));
			}
		}

		//Global settings
		if($access['daten']['MAX'] > 3) {
			if(in_array('groups', $MODULES)) {
				ko_set_setting('daten_gs_pid', format_userinput($_POST['sel_gs_pid'], 'uint'));
				ko_set_setting('daten_gs_role', format_userinput($_POST['sel_gs_role'], 'uint'));
				ko_set_setting('daten_gs_available_roles', format_userinput($_POST['sel_gs_available_roles'], 'alphanumlist'));
			}
			ko_set_setting('daten_show_mod_to_all', format_userinput($_POST['sel_show_mod_to_all'], 'uint'));
			ko_set_setting('daten_mod_exclude_fields', format_userinput($_POST['sel_mod_exclude_fields'], 'alphanumlist'));
			kota_save_mandatory_fields('ko_event', $_POST);
			ko_set_setting('daten_access_calendar', format_userinput($_POST['sel_calendar_access'], 'uint'));
			ko_daten_propagate_cal_access();
			ko_set_setting('daten_show_ical_links_to_guest', format_userinput($_POST['sel_show_ical_links_to_guest'], 'uint'));
			ko_set_setting('activate_event_program', format_userinput($_POST['sel_activate_event_program'], 'uint'));
			ko_set_setting('absence_color', format_userinput($_POST['absence_color'], 'color'));
		}


		//Guest userpref
		if(!isset($access['admin'])) ko_get_access('admin');
		if($access['admin']['ALL'] > 2) {
			$uid = ko_get_guest_id();
			ko_save_userpref($uid, 'default_view_daten', format_userinput($_POST['guest_sel_daten'], 'js'));
			ko_save_userpref($uid, 'show_limit_daten', format_userinput($_POST['guest_txt_limit_daten'], 'uint'));
			ko_save_userpref($uid, 'cal_woche_start', min(23, format_userinput($_POST['guest_txt_cal_woche_start'], 'uint')));
			ko_save_userpref($uid, 'cal_woche_end', min(24, format_userinput($_POST['guest_txt_cal_woche_end'], 'uint')));
			ko_save_userpref($uid, 'daten_monthly_title', format_userinput($_POST['guest_sel_monthly_title'], 'js', FALSE));
			ko_save_userpref($uid, 'daten_pdf_show_time', format_userinput($_POST['guest_sel_pdf_show_time'], 'uint', FALSE, 1));
			ko_save_userpref($uid, 'daten_pdf_use_shortname', format_userinput($_POST['guest_sel_pdf_use_shortname'], 'uint', FALSE, 1));
			ko_save_userpref($uid, 'daten_export_show_legend', format_userinput($_POST['guest_sel_export_show_legend'], 'uint', FALSE, 1));
			ko_save_userpref($uid, 'daten_pdf_week_start', format_userinput($_POST['guest_sel_pdf_week_start'], 'uint', FALSE, 1));
			ko_save_userpref($uid, 'daten_pdf_week_length', format_userinput($_POST['guest_sel_pdf_week_length'], 'uint', FALSE, 2));
			ko_save_userpref($uid, 'daten_mark_sunday', format_userinput($_POST['guest_sel_mark_sunday'], 'uint', FALSE, 1));
			ko_save_userpref($uid, 'daten_no_cals_in_itemlist', format_userinput($_POST['guest_sel_no_cals_in_itemlist'], 'uint', FALSE, 1));
			ko_save_userpref($uid, 'daten_show_res_in_tooltip', format_userinput($_POST['guest_sel_show_res_in_tooltip'], 'uint', FALSE, 1));
			ko_save_userpref($uid, 'daten_ical_description_fields', format_userinput($_POST['guest_sel_ical_description_fields'], 'alphanumlist'));
			ko_save_userpref($uid, 'daten_tooltip_fields', format_userinput($_POST['guest_sel_tooltip_fields'], 'alphanumlist'));
		}


		$_SESSION['show'] = ($_SESSION['show_back'] && in_array($_SESSION['show_back'], array_keys($DISABLE_SM['daten']))) ? $_SESSION['show_back'] : 'daten_settings';
	break;



	//Anzeigen
	case 'show_all_events':  //Backwards compatibility for stored userpref
	case 'all_events':
		if($access['daten']['MAX'] < 1) break;

		if($_SESSION['show'] == 'all_events') $_SESSION['show_start'] = 1;
		$_SESSION["show"] = "all_events";
	break;

	case "all_groups":
		if($access['daten']['MAX'] < 3) break;

		$_SESSION["show"] = "all_groups";
		$_SESSION["show_start"] = 1;
	break;

	case 'show_calendar':  //Backwards compatibility
	case 'calendar':
		if($access['daten']['MAX'] < 1) break;

		$_SESSION['show'] = 'calendar';
		$wt = kota_filter_get_warntext('ko_event');
		if (trim($wt) != '') $notifier->addTextWarning($wt);
	break;

	case "show_cal_monat":
		if($access['daten']['MAX'] < 1) break;

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
		if($access['daten']['MAX'] < 1) break;

		$_SESSION['cal_view'] = 'agendaWeek';
		$_SESSION["show"] = "calendar";
		$wt = kota_filter_get_warntext('ko_event');
		if (trim($wt) != '') $notifier->addTextWarning($wt);
	break;

	case "show_cal_jahr":
		if($access['daten']['MAX'] < 1) break;

		$_SESSION["show"] = "cal_jahr";
		$_SESSION['cal_view'] = '';
	break;

	case 'ical_links':
		if($access['daten']['MAX'] < 1) break;

		$_SESSION['show'] = 'ical_links';
	break;
	case 'ical_links_revoke':
		require_once($BASE_PATH.'admin/inc/admin.inc');
		$login['ical_hash'] = ko_admin_revoke_ical_hash($_SESSION['ses_userid']);
		$notifier->addTextInfo(getLL("ical_links_revoked"));
		$_SESSION['show'] = 'ical_links';
	break;

	case 'list_absence':
		if($access['daten']['ABSENCE'] < 1) break;

		if($_GET['mode'] == "xls") {
			if(!empty($_GET['set_person_filter'])) {
				unset($_SESSION['kota_filter']["ko_event_absence"]["leute_id"]);
				$_SESSION['kota_filter']["ko_event_absence"]["leute_id"][0] = format_userinput($_GET['set_person_filter'], 'uint');
			}
			$filename = ko_daten_list_absence('xls');
			if($filename) {
				$filename = substr($filename, 3);
				$onload_code = "ko_popup('/download.php?action=file&amp;file=$filename');";
				ko_log("export_donations", $filename);
			}
		}
		$_SESSION["show"] = "list_absence";
		break;

	case 'add_absence':
		if($access['daten']['ABSENCE'] < 1) break;
		$_SESSION["show"] = "add_absence";
		break;

	case 'edit_absence':
		if($access['daten']['ABSENCE'] < 1) break;
		$_SESSION["show"] = "edit_absence";
		break;

	case 'submit_new_absence':
	case 'submit_edit_absence':
		if ($access['daten']['ABSENCE'] < 1) break;

		if(sql_datum($_POST['koi']['ko_event_absence']['from_date'][0]) > sql_datum($_POST['koi']['ko_event_absence']['to_date'][0])) {
			$notifier->addTextError("Startdatum muss vor dem Enddatum liegen");
		} else {
			kota_submit_multiedit('', 'new_absence', $changes);
		}

		if (!$notifier->hasErrors()) {
			$_SESSION['show'] = 'list_absence';
			$notifier->addTextInfo(getLL("ko_event_absence_info_saved"), $do_action);
		}
		break;

	case 'delete_absence':
		if ($access['daten']['ABSENCE'] < 1) break;

		$absence_id = format_userinput($_POST["id"], "uint");
		if(ko_daten_delete_absence($absence_id)) {
			$notifier->addTextInfo(getLL('ko_event_absence_info_deleted'));
		} else {
			$notifier->addTextError(getLL('ko_event_absence_info_error'));
		}
		$_SESSION['show'] = 'list_absence';
		break;


	//Neu
	case "neuer_termin":
		if($access['daten']['MAX'] < 2) break;

		//Get new date and time from GET param dayDate
		if(isset($_GET['dayDate'])) $start_stamp = $end_stamp = strtotime($_GET['dayDate']);
		if(isset($_GET['endDate'])) $end_stamp = strtotime($_GET['endDate']);
		$setTime = TRUE;
		if(!$start_stamp) {
			$setTime = FALSE;
			$start_stamp = $end_stamp = time();
		}

		$new_date_start = strftime('%Y-%m-%d', $start_stamp);
		$new_date_end   = strftime('%Y-%m-%d', $end_stamp);
		if($setTime) {
			$new_time_start = strftime("%H:%M", $start_stamp);
			if($new_time_start == '00:00') {  //All day
				$new_time_end = '';
			} else {  //time given
				$new_time_end = $end_stamp != $start_stamp ? strftime('%H:%M', $end_stamp) : strftime('%H:00', (int)$end_stamp+3600);
			}
		}

		kota_assign_values("ko_event", array("startdatum" => $new_date_start));
		if($new_date_start != $new_date_end) kota_assign_values("ko_event", array("enddatum" => $new_date_end));

		if($setTime) $new_event_data = array("start_time" => $new_time_start, "end_time" => $new_time_end);

		//Manual moderation for admins
		if($access['daten']['MAX'] > 3) {
			$manualModerationButton = '<br /><button type="submit" class="btn btn-success" name="submit_mm" value="1" onclick="var ok = check_mandatory_fields($(this).closest(\'form\')); if (ok) {disable_onunloadcheck();set_action(\'submit_manual_moderation\', this)} else return false;">'.getLL('daten_submit_manual_moderation').'</button>';
			$smarty->assign('additional_button', $manualModerationButton);
		}

		if($_SESSION['show'] != 'neuer_termin') $_SESSION['show_back'] = $_SESSION['show'];
		$_SESSION["show"]= "neuer_termin";
		$onload_code = "form_set_first_input();".$onload_code;
	break;



	case "neue_gruppe":
		if($access['daten']['ALL'] < 3) break;

		$_SESSION["show_back"] = $_SESSION["show"];
		$_SESSION["show"]= "neue_gruppe";
		$onload_code = "form_set_first_input();".$onload_code;
	break;




	case 'new_ical':
		if($access['daten']['ALL'] < 3) break;

		$_SESSION['show_back'] = $_SESSION['show'];
		$_SESSION['show']= 'new_ical';
		$onload_code = 'form_set_first_input();'.$onload_code;
	break;



	case 'submit_neuer_termin':
	case 'submit_as_new_event':
	case 'submit_manual_moderation':
		if($access['daten']['MAX'] < 2) break;

		$event_data = ko_daten_insert_new_event($do_action, $_POST);

		if (!$notifier->hasErrors()) {
			$_SESSION['show'] = ($_SESSION['show_back'] && in_array($_SESSION['show_back'], array_keys($DISABLE_SM['daten']))) ? $_SESSION['show_back'] : 'calendar';
		}
	break;



	case "submit_neue_gruppe":
	case "submit_edit_gruppe":
	case 'submit_new_ical':
		list($table, $columns, $id, $hash) = explode("@", $_POST["id"]);
		if($do_action == 'submit_edit_gruppe') {
			if(FALSE === ($id = format_userinput($id, "uint", TRUE))) break;
			//Check for edit right (3) for this event group
			if($access['daten']['ALL'] < 3 && $access['daten'][$id] < 3) break;
		} else {
			//Only allow new event groups with ALL rights >= 3
			if($access['daten']['ALL'] < 3) break;
		}

		//Check for calendar_id given in submitted columns (might not be set for certain types)
		if(in_array('calendar_id', explode(',', $columns))) {
			$cal_id = TRUE;

			$txt_cal = trim(format_userinput($_POST["koi"]["ko_eventgruppen"]["calendar_id"][$id], "text"));

			//Check for new calendar
			$found = FALSE;
			if($txt_cal != "") {

				ko_get_event_calendar($cals);
				foreach($cals as $cal) {
					if($cal["name"] == $txt_cal) {
						$found = TRUE;
						$new_cal = $cal["id"];
					}
				}

				//New calendars only with ALL rights 3 or higher
				if(!$found && $access['daten']['ALL'] > 2) {
					$new_cal = db_insert_data("ko_event_calendar", array("name" => $txt_cal));
					//Log
					ko_log("new_calendar", "$new_cal: $txt_cal");
					$allowed = TRUE;
				} else if(!$found && $access['daten']['ALL'] <= 2) {
					$allowed = FALSE;
					$notifier->addTextError(getLL('daten_error_not_allowed_cal'));
				} else {
					$allowed = TRUE;
				}

			} else {  // no calendar has been selected
				$cal_id = FALSE;
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
			} else {
				$_POST["koi"]["ko_eventgruppen"]["calendar_id"][$id] = '';
			}

			if($do_action == 'submit_new_ical') $_POST['kota_type'] = 3;
			if($do_action == 'submit_neue_gruppe' || $do_action == 'submit_new_ical') {
				$new_id = kota_submit_multiedit("", "new_eventgruppe");
				if(ko_module_installed("taxonomy")) {
					$taxonomy_terms = explode(",", format_userinput($_POST['koi']['ko_eventgruppen']['terms'][0], "intlist"));
					ko_taxonomy_clear_terms_on_node("ko_eventgruppen", $new_id);
					ko_taxonomy_attach_terms_to_node($taxonomy_terms, "ko_eventgruppen", $new_id);
				}
				$_SESSION["show_tg"][] = $new_id;
				$notifier->addInfo(5, $do_action);
			} else if($do_action == 'submit_edit_gruppe') {
				kota_submit_multiedit("", "edit_eventgruppe");
				ko_delete_empty_calendars();
				$notifier->addInfo(6, $do_action);
			}
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
    	else break;

		if(FALSE === ($id = format_userinput($id, "uint", TRUE))) {
			trigger_error("Not allowed id: ".$id, E_USER_ERROR);
		}

		$event = db_select_data('ko_event', "WHERE `id` = '$id'", '*', '', '', TRUE);
		if($access['daten'][$event['eventgruppen_id']] < 2) break;

		$_SESSION["show_back"] = $_SESSION["show"];
		$_SESSION["show"]= "edit_termin";
		$edit_id = $id;
		$onload_code = "form_set_first_input();".$onload_code;
	break;



	case "edit_gruppe":
		$edit_id = format_userinput($_POST['id'], 'uint');
		if($access['daten']['ALL'] < 3 && $access['daten'][$edit_id] < 3) break;

		$eg = db_select_data('ko_eventgruppen', "WHERE `id` = '$edit_id'", '*', '', '', TRUE);

		$_SESSION["show_back"] = $_SESSION["show"];
		$_SESSION['show'] = 'edit_gruppe';
		$onload_code = "form_set_first_input();".$onload_code;
	break;



	case "submit_edit_termin":
		$id = format_userinput($_POST["id"], "uint");
		$event = db_select_data('ko_event', "WHERE `id` = '$id'", '*', '', '', TRUE);
		if ($access['daten'][$event['eventgruppen_id']] < 2) break;


		//Process data
		$data = $_POST["koi"]["ko_event"];
		kota_process_data("ko_event", $data, "post", $log, $id);

		//If KOTA exclude columns are set for this user, fill $data with values from current event, so these values won't get changed
		$kota_columns = ko_access_get_kota_columns($_SESSION['ses_userid'], 'ko_event');
		if (sizeof($kota_columns) > 0) {
			foreach ($KOTA['ko_event']['_allformcolumns'] as $col) {
				if (substr($col, 0, 1) == '_') continue;
				if (in_array($col, $kota_columns)) continue;
				$data[$col] = $event[$col];
			}
		}

		$errorOut = check_daten_entries($data);
		if ($errorOut) {
			$edit_id = $id;
			break;
		}


		// Ignore set data if not set in POST (avoid deleting res bcs the editing person does not have access to them
		$forceIgnoreRes = !isset($_POST["sel_do_res"]);

		$data["resitems"] = format_userinput($_POST["sel_do_res"], "intlist");
		foreach ($EVENTS_SHOW_RES_FIELDS as $f) {
			if (in_array($f, ['startzeit', 'endzeit'])) {
				$data['res_' . $f] = $_POST['res_' . $f] ? sql_zeit($_POST['res_' . $f]) : $data[$f];
			} else {
				$data['res_' . $f] = $_POST['res_' . $f];
			}
		}
		$data['res_startdatum'] = date('Y-m-d', ($_POST['res_startdatum_delta'] * 24 * 3600 + strtotime($data["startdatum"])));
		$data['res_enddatum'] = date('Y-m-d', ($_POST['res_enddatum_delta'] * 24 * 3600 + strtotime($data["enddatum"])));
		$data['responsible_for_res'] = format_userinput($_POST['sel_responsible_for_res'], 'uint');
		$data["startdatum"] = sql_datum($data["startdatum"]);
		$data["enddatum"] = sql_datum($data["enddatum"]);

		if ($data["enddatum"] == "0000-00-00" || trim($data["enddatum"]) == "") {
			$data["enddatum"] = $data["startdatum"];
		}

		//Group subscription (group will be created in ko_daten_store_event() or ko_daten_update_event())
		if (ko_get_setting('daten_gs_pid') && ko_module_installed('groups')) {
			if (!isset($access['groups'])) ko_get_access('groups');
			if ($access['groups']['ALL'] > 2 || $access['groups'][ko_get_setting('daten_gs_pid')] > 2) {
				$data['gs_gid'] = isset($_POST['chk_gs_gid']) ? 1 : '';
			}
		}

		//Check for changes
		$event["startzeit"] = sql_zeit($event["startzeit"]);
		$event["endzeit"] = sql_zeit($event["endzeit"]);
		$dont_check = ['id', 'gs_gid', 'cdate', 'last_change', 'lastchange_user', 'reservationen', 'import_id'];
		if (!ko_module_installed('rota')) $dont_check[] = 'rota';
		//Add exclude fields from settings
		foreach (explode(',', ko_get_setting('daten_mod_exclude_fields')) as $f) {
			if (!$f || in_array($f, $dont_check)) continue;
			$dont_check[] = $f;
		}
		$event_changed = FALSE;
		foreach ($event as $key => $value) {
			if (in_array($key, $dont_check)) continue;
			if (!isset($KOTA['ko_event'][$key]['form'])) continue;
			if ($event[$key] != $data[$key]) $event_changed = TRUE;
		}

		//Check for event moderation
		ko_get_eventgruppe_by_id($data["eventgruppen_id"], $eg);
		//Only store changes as moderation if something changed. (e.g. only changes in reservations don't need moderation)
		if ($eg["moderation"] > 0 && $access['daten'][$event['eventgruppen_id']] < 3 && $event_changed) {
			$data["_event_id"] = $id;

			//Copy all fields not set in mod data from original event.
			// e.g. necessary for file inputs as these are handled in kota_process_data but not set on $data if nothing's changed
			foreach ($event as $k => $v) {
				if (!isset($data[$k])) $data[$k] = $v;
			}

			ko_daten_store_moderation([$data]);
			$notifier->addWarning(9, $do_action);
		} else {
			$errorOut = ko_daten_update_event($id, $data, $forceIgnoreRes);
			if (!$errorOut) {
				$notifier->addInfo(2, $do_action);
			}
		}

		$_SESSION['show'] = ($_SESSION['show_back'] && in_array($_SESSION['show_back'], array_keys($DISABLE_SM['daten']))) ? $_SESSION['show_back'] : 'calendar';
		break;


	case "multiedit":
		if($_SESSION["show"] == "all_events") {
			if($access['daten']['MAX'] < 2) break;

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
			if($access['daten']['MAX'] < 3) break;

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
			if($access['daten']['MAX'] < 2) break;
			kota_submit_multiedit(2, '', $changes);
			//Send notifications for multiediting events
			foreach($changes['ko_event'] as $id => $v) {
				foreach($v as $a => $b) $v[$a] = '';
				ko_daten_send_notification($id, 'update', $v);
			}
		} else if($_SESSION["show"] == "multiedit_tg") {
			if($access['daten']['MAX'] < 3) break;
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
		if($_SESSION["ses_userid"] == ko_get_guest_id()) break;
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
		if(!$ids[0]) break;

		$notification = format_userinput($_POST["mod_confirm"], "alpha", FALSE, 5) == "true";

		foreach($ids as $id) {
			$new_event_data = db_select_data("ko_event_mod", "WHERE `id` = '$id'", "*", "", "", TRUE);
			if(!$new_event_data["eventgruppen_id"] || $access['daten'][$new_event_data["eventgruppen_id"]] < 4) continue;

			$terms = ko_taxonomy_get_terms_by_node($id, "ko_event_mod");
			$new_event_data['terms'] = implode(",", array_keys($terms));

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
				ko_taxonomy_delete_node($id, "ko_event_mod");
			}

			//Logging
			$new_event_data["notification"] = $notification ? getLL("yes") : getLL("no");
			ko_log_diff("event_mod_approve", $new_event_data);

			if($notification) {
				$person = ko_get_logged_in_person($new_event_data['_user_id']);
				if($person["email"]) {
					$email_rec[] = $person["email"];
				}
				else {
					if ($new_event_data['_user_id'] == ko_get_guest_id()) {
						$noemail_rec[] = getLL("entry_by_guest_without_contactinfos");
					} else {
						$noemail_rec[] = ko_html($person["vorname"] . " " . $person["nachname"]).", ".ko_html($person["telp"]);
					}
				}
				$event_text .= ko_daten_infotext($new_event_data);
			}
		}

		if($notification) {
			$smarty->assign("txt_empfaenger", implode(", ", array_unique($email_rec)));
			$smarty->assign('txt_empfaenger_semicolon', implode('; ', array_unique($email_rec)));
			$smarty->assign("tpl_ohne_email", ($person["email"] == "" ? implode(", ", array_unique($noemail_rec)) : getLL("res_mod_no")) );
			$p = ko_get_logged_in_person();
			$smarty->assign("tpl_show_bcc_an_mich", ($p["email"] ? TRUE : FALSE));
			$smarty->assign("tpl_show_send", TRUE);
			$smarty->assign('txt_betreff', (getLL('email_subject_prefix').(sizeof($ids) > 1 ? getLL('daten_emails_mod_confirm_subject') : getLL('daten_email_mod_confirm_subject'))) );

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
		if(!$ids[0]) break;

		$notification = format_userinput($_POST["mod_confirm"], "alpha", FALSE, 5) == "true";

		foreach($ids as $id) {
			$mod_event = db_select_data("ko_event_mod", "WHERE `id` = '$id'", "*", "", "", TRUE);
			if(!$mod_event["eventgruppen_id"]) continue;
			if($access['daten'][$mod_event["eventgruppen_id"]] < 4 && $mod_event["_user_id"] != $_SESSION["ses_userid"]) continue;

			//Delete mod entry
			db_delete_data("ko_event_mod", "WHERE `id` = '$id'");
			ko_taxonomy_delete_node($id, "ko_event_mod");

			//Logging
			$mod_event["notification"] = $notification ? getLL("yes") : getLL("no");
			ko_log_diff("event_mod_delete", $mod_event);

			if($notification) {
				$person = ko_get_logged_in_person($mod_event['_user_id']);
				if($person["email"]) {
					$email_rec[] = $person["email"];
				}
				else {
					if ($mod_event['_user_id'] == ko_get_guest_id()) {
						$noemail_rec[] = getLL("entry_by_guest_without_contactinfos");
					} else {
						$noemail_rec[] = ko_html($person["vorname"] . " " . $person["nachname"]).", ".ko_html($person["telp"]);
					}
				}

				$event_text .= ko_daten_infotext($mod_event);
			}
		}

		if($notification) {
			$smarty->assign("txt_empfaenger", implode(", ", array_unique($email_rec)));
			$smarty->assign('txt_empfaenger_semicolon', implode('; ', array_unique($email_rec)));
			$smarty->assign("tpl_ohne_email", ($person["email"] == "" ? implode(", ", array_unique($noemail_rec)) : getLL("res_mod_no")) );
			$p = ko_get_logged_in_person();
			$smarty->assign("tpl_show_bcc_an_mich", ($p["email"] ? TRUE : FALSE));
			$smarty->assign("tpl_show_send", TRUE);
			$smarty->assign('txt_betreff', (getLL('email_subject_prefix').(sizeof($ids) > 1 ? getLL('daten_emails_mod_delete_subject') : getLL('daten_email_mod_delete_subject'))) );

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
		if(!$ids[0]) break;

		$notification = format_userinput($_POST["mod_confirm"], "alpha", FALSE, 5) == "true";

		foreach($ids as $id) {
			//Get event moderation
			$mod_event = db_select_data("ko_event_mod", "WHERE `id` = '$id'", "*", "", "", TRUE);
			if(!$mod_event["eventgruppen_id"] || $access['daten'][$mod_event["eventgruppen_id"]] < 4 || !$mod_event["_event_id"]) continue;
			
			//Delete event
			do_del_termin($mod_event["_event_id"]);

			//Store recipient email address for notification
			if($notification) {
				$person = ko_get_logged_in_person($mod_event['_user_id']);
				if($person["email"]) {
					$email_rec[] = $person["email"];
				}
				else {
					if ($mod_event['_user_id'] == ko_get_guest_id()) {
						$noemail_rec[] = getLL("entry_by_guest_without_contactinfos");
					} else {
						$noemail_rec[] = ko_html($person["vorname"] . " " . $person["nachname"]).", ".ko_html($person["telp"]);
					}
				}
				$event_text .= ko_daten_infotext($mod_event);
			}
		}

		if($notification) {
			$smarty->assign("txt_empfaenger", implode(", ", array_unique($email_rec)));
			$smarty->assign('txt_empfaenger_semicolon', implode('; ', array_unique($email_rec)));
			$smarty->assign("tpl_ohne_email", ($person["email"] == "" ? implode(", ", array_unique($noemail_rec)) : getLL("res_mod_no")) );
			$p = ko_get_logged_in_person();
			$smarty->assign("tpl_show_bcc_an_mich", ($p["email"] ? TRUE : FALSE));
			$smarty->assign("tpl_show_send", TRUE);
			$smarty->assign("txt_betreff", (getLL('email_subject_prefix').getLL("daten_email_mod_delete_confirm_subject")) );

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
		if(!$ids[0]) break;

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
				$person = ko_get_logged_in_person($mod_event['_user_id']);
				if($person["email"]) {
					$email_rec[] = $person["email"];
				}
				else {
					if ($mod_event['_user_id'] == ko_get_guest_id()) {
						$noemail_rec[] = getLL("entry_by_guest_without_contactinfos");
					} else {
						$noemail_rec[] = ko_html($person["vorname"] . " " . $person["nachname"]).", ".ko_html($person["telp"]);
					}
				}
				$event_text .= ko_daten_infotext($mod_event);
			}
		}

		if($notification) {
			$smarty->assign("txt_empfaenger", implode(", ", array_unique($email_rec)));
			$smarty->assign('txt_empfaenger_semicolon', implode('; ', array_unique($email_rec)));
			$smarty->assign("tpl_ohne_email", ($person["email"] == "" ? implode(", ", array_unique($noemail_rec)) : getLL("res_mod_no")) );
			$p = ko_get_logged_in_person();
			$smarty->assign("tpl_show_bcc_an_mich", ($p["email"] ? TRUE : FALSE));
			$smarty->assign("tpl_show_send", TRUE);
			$smarty->assign("txt_betreff", (getLL('email_subject_prefix').getLL("daten_email_mod_edit_confirm_subject")) );

			$smarty->assign("txt_emailtext", (getLL("daten_email_mod_edit_confirm_text")."\n\n".ko_html($event_text)) );

			$smarty->assign("tpl_show_rec_link", TRUE);
			$_SESSION["show"]= "email_confirm";
		}
	break;


	//Import
	case "import":
	case "importtwo":
	case "importthree":
	case "importfour":
		if ($access['daten']['MAX'] < 2) break;

		switch ($do_action) {
			case 'import':
				$_SESSION["import_daten_state"] = 1;
			break;
			case 'importtwo':
				$_SESSION["import_daten_state"] = 2;
			break;
			case 'importthree':
				$_SESSION["import_daten_state"] = 3;
			break;
			case 'importfour':
				$_SESSION["import_daten_state"] = 4;
			break;
		}

		switch ($_SESSION["import_daten_state"]) {
			case 1:
				$_SESSION["import_daten_csv_1"] = array('seperator' => ',', 'content_seperator' => '"', 'file_encoding' => 'utf-8', 'first_line' => '');
			break;
			case 2:
				$_SESSION["import_daten_csv_1"] = array();

				// event group
				$_SESSION["import_daten_csv_1"]['eventgroup_id'] = format_userinput($_POST['eventgroup_id'], 'uint');
				if (!$_SESSION["import_daten_csv_1"]['eventgroup_id'] || ($access['daten']['ALL'] < 2 && $access['daten'][$_SESSION["import_daten_csv_1"]['eventgroup_id']] < 2)) {
					$notifier->addError(13, $do_action);
				}
				//file
				ini_set('auto_detect_line_endings', '1'); // for mac-excel line endings (CR)
				$file = $_FILES['csv'];
				$csv_file = $file['tmp_name'];
				//separator
				$sep = $_POST["txt_separator"];
				if(strlen($sep) == 1) {
					$_SESSION["import_daten_csv_1"]["separator"] = $sep;
				} else {
					$notifier->addError(15, $do_action);
				}
				//content separator
				$sep = $_POST["txt_content_separator"];
				$_SESSION["import_daten_csv_1"]["content_separator"] = $sep;
				//first line
				if($_POST["chk_first_line"]) {
					$_SESSION["import_daten_csv_1"]["first_line"] = 1;
				} else {
					$_SESSION["import_daten_csv_1"]["first_line"] = 0;
				}
				//File encoding
				if(in_array($_POST['sel_file_encoding'], array('utf-8', 'latin1', 'macintosh'))) {
					$_SESSION["import_daten_csv_1"]['file_encoding'] = $_POST['sel_file_encoding'];
				} else {
					$_SESSION["import_daten_csv_1"]['file_encoding'] = 'latin1';
				}

				if(!$notifier->hasErrors()) {
					if(is_array($data = ko_parse_general_csv($csv_file, $_SESSION["import_daten_csv_1"]))) {
						if (sizeof($data) == 0 || (!$_SESSION["import_daten_csv_1"]['first_line'] && sizeof($data) == 1)) {
							$_SESSION["import_daten_state"] = 1;
							$notifier->addError(18, $do_action);
						}
						$_SESSION["import_daten_data"] = $data;
					} else {
						$_SESSION["import_daten_state"] = 1;
						$notifier->addError(14, $do_action);
					}
				} else {
					$_SESSION["import_daten_state"] = 1;
				}
			break;
			case 3:
				$_SESSION["import_daten_csv_2"] = array();

				list($values, $descs) = ko_daten_get_user_assignable_cols();
				$fields = array();
				$_SESSION['assign_field'] = $_POST['assign_field'];

				foreach ($_SESSION['assign_field'] as $k => $field) {
					if ($field) {
						if (in_array($field, $fields)) {
							$notifier->addError(19, $do_action);
							break;
						} else if (!in_array($field, $values)) {
							$notifier->addError(20, $do_action);
							break;
						} else {
							$fields[$k] = $field;
						}
					}
				}

				if (!$notifier->hasErrors()) {
					$data = $_SESSION["import_daten_data"];
					$egId = $_SESSION["import_daten_csv_1"]['eventgroup_id'];
					$firstLine = $_SESSION["import_daten_csv_1"]["first_line"];
					$failures = array();
					foreach ($data as $j => $row_) {
						if ($j == 0 && $firstLine) continue;
						$row = array();
						foreach ($row_ as $k => $val) {
							if ($fields[$k]) $row[$fields[$k]] = $val;
						}
						$row['eventgruppen_id'] = $egId;
						ko_daten_insert_new_event($do_action, array('koi' => array('ko_event' => $row)));
						if ($notifier->hasErrors()) {
							$failures[] = $row_;
							$notifier->dropNotifications(koNotifier::ERROR);
						}
					}
					foreach ($failures as $failure) {
						$notifier->addError(21, $do_action, array(implode(',', $failure)));
					}
					if (sizeof($failures) >= sizeof($data)|| (sizeof($failures) >= sizeof($data) - 1 && $firstLine)) {
						$_SESSION["import_daten_state"] = 2;
					}
				} else {
					$_SESSION["import_daten_state"] = 2;
				}
			break;
		}

		if ($_SESSION["import_daten_state"] == 3) {
			$_SESSION["show"] = "all_events";
		} else {
			$_SESSION["show"] = "import";
		}
	break;  //import


	case "do_import":
		if($access['daten']['ALL'] < 2) break;

		//Add all imported events to the selected event group
		if($_POST["sel_eventgroup"]) {
			$add_group = format_userinput($_POST['sel_eventgroup'], 'uint');
		} else {
			$add_group = FALSE;
		}

		//Check for leute_admin_groups of current login and add all imported addresses to this group as well
		$gids = ko_get_leute_admin_groups($_SESSION['ses_userid'], 'all');
		if(is_array($gids) && sizeof($gids) > 0) {
			foreach($gids as $gid) {
				if(!$gid) continue;
				if($add_group) $add_group .= ','.$gid;
				else $add_group = $gid;
			}
		}

		$roles = array();
		$yes = array('yes', 'ja', '1', 'x');

		$do_ldap = ko_do_ldap();
		if($do_ldap) $ldap = ko_ldap_connect();

		foreach($_SESSION["import_data"] as $data) {
			if(sizeof($data) > 0) {

				// Check if some group fields are either filled with a role or with yes
				$newGroups = array();
				foreach ($data as $key => $entry) {
					if (substr($key, 0, 9) == 'MODULEgrp') {
						$entryLower = trim(strtolower($entry));
						$fullGroupId = substr($key, 9);
						$groupId = substr($key, -6);
						if (!isset($roles[$groupId])) {
							$group = null;
							ko_get_groups($group, 'and id = ' . $groupId);
							$group = $group[$groupId];
							$groupRoles = null;
							if (trim($group['roles'] != '')) {
								ko_get_grouproles($groupRoles, 'and id in (' . $group['roles'] . ')');
							}
							$roles[$groupId] = $groupRoles;
						}
						$added = false;
						foreach ($roles[$groupId] as $role) {
							if (trim(strtolower($role['name'])) == $entryLower) {
								$newGroups[] = $fullGroupId . ':r' . $role['id'];
								$added = true;
							}
						}
						if (!$added && (in_array($entryLower, $yes) || ($entryLower != '' && $entryLower != '0'))) {
							$newGroups[] = $fullGroupId;
						}

						//Unset MODULEgrp key
						unset($data[$key]);
					}
				}
				$hardCodedGroups = trim($data["groups"]);
				if ($hardCodedGroups != '') {
					$newGroups = array_merge($newGroups, explode(',', $hardCodedGroups));
				}
				if($add_group) {
					$newGroups[] = $add_group;
				}
				$newGroups = array_unique($newGroups);
				$data['groups'] = implode(',', $newGroups);
				$data["crdate"] = date("Y-m-d H:i:s");
				$data["cruserid"] = $_SESSION["ses_userid"];
				$new_id = db_insert_data("ko_leute", $data);

				//Create LDAP entry
				if($do_ldap) ko_ldap_add_person($ldap, $data, $new_id);

				//Add log entry
				$logdata = $data;
				$logdata['id'] = $new_id;
				ko_log_diff('imported_address', $logdata);
				unset($logdata);
			}
		}
		if($do_ldap) ko_ldap_close($ldap);


		//HOOK: Plugins erlauben, hier einzugreifen, bevor die Session-Daten gelöscht werden
		hook_action_handler_inline($do_action);

		$notifier->addInfo(15, $do_action);
		$_SESSION["show"] = "show_all";
		unset($_SESSION["import_mode"]);
		unset($_SESSION["import_state"]);
		unset($_SESSION["import_data"]);
		unset($_SESSION["import_csv"]);
		break;  //do_import


	//Send email
	case 'submit_email':
		if($_POST['rd_bcc_an_mich'] == 'ja') {
			$p = ko_get_logged_in_person();
			if(check_email($p['email'])) {
				$_POST['txt_bcc'] .= ($_POST['txt_bcc'] == '') ? $p['email'] : ','.$p['email'];
			}
		}

		$recipients = explode(',', str_replace(";", ",", $_POST["txt_empfaenger"]));
		if($_POST["txt_cc"] != "") $cc = explode(',', (str_replace(";", ",", $_POST["txt_cc"])));
		if($_POST["txt_bcc"] != "") $bcc = explode(',', nl2br(str_replace(";", ",", $_POST["txt_bcc"])));

		foreach($recipients AS $key => $value) $recipients[$key] = trim($value);
		foreach($cc AS $key => $value) $cc[$key] = trim($value);
		foreach($bcc AS $key => $value) $bcc[$key] = trim($value);

		$text = ko_emailtext($_POST["txt_emailtext"]);

		ko_send_mail(
			'',
			$recipients,
			$_POST["txt_betreff"],
			ko_emailtext($_POST['txt_emailtext']),
			array(),
			$cc,
			$bcc
		);

		if (!$notifier->hasErrors()) {
			$notifier->addInfo(11, $do_action);
			ko_log('event_mod_email', '"'.format_userinput($_POST['txt_betreff'], 'text').'": '.format_userinput($_POST['txt_empfaenger'], 'text').', cc: '.format_userinput($_POST['txt_cc'], 'text').', bcc: '.format_userinput($_POST['txt_bcc'], 'text'));
		}

		$_SESSION['show'] = $_SESSION['show_back'] ? $_SESSION['show_back'] : 'list_events_mod';
	break;


	// TODO: Access?!
	case 'list_reminders':
		if($access['daten']['REMINDER'] < 1) break;

		$_SESSION['show_start'] = 1;
		$_SESSION['show'] = 'list_reminders';
		break;
	case 'new_reminder':
		if($access['daten']['REMINDER'] < 1) break;

		$_SESSION['show'] = 'new_reminder';
		$onload_code = 'form_set_first_input();'.$onload_code;
		break;
	case 'submit_new_event_reminder':
		if($access['daten']['REMINDER'] < 1) break;

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

	case 'submit_as_new_event_reminder':
		if($access['daten']['REMINDER'] < 1) break;

		list($table, $columns, $ids, $hash) = explode('@', $_POST['id']);
		//Fake POST[id] for kota_submit_multiedit() to remove the id from the id. Otherwise this entry will be edited
		$new_hash = md5(md5($mysql_pass.$table.implode(':', explode(',', $columns)).'0'));
		$_POST['id'] = $table.'@'.$columns.'@0@'.$new_hash;

		$newId = kota_submit_multiedit('', 'new_reminder');
		db_update_data('ko_reminder', "WHERE `id` = '$newId'", array('type' => '1'));

		if(!$notifier->hasErrors()) {
			$notifier->addInfo(14);
			$_SESSION['show'] = 'list_reminders';
			ko_include_kota('ko_reminder');
		}
	break;

	//Edit
	case 'edit_reminder':
		if($access['daten']['REMINDER'] < 1) break;
		$editId = format_userinput($_POST['id'], 'uint');
		if (!ko_get_reminder_access($editId)) break;

		$_SESSION['show'] = 'edit_reminder';
		$onload_code = 'form_set_first_input();'.$onload_code;
	break;


	case 'submit_edit_event_reminder':
		if($access['daten']['REMINDER'] < 1) break;

		list($t1, $t2, $editId) = explode('@', $_POST['id']);
		if (!ko_get_reminder_access($editId)) break;

		if($do_action == 'submit_edit_event_reminder') $_POST['kota_type'] = 1;
		kota_submit_multiedit('', 'edit_reminder');
		if(!$notifier->hasErrors()) {
			$notifier->addInfo(15);
			$_SESSION['show'] = 'list_reminders';
			ko_include_kota('ko_reminder');
		}
	break;



	case 'delete_reminder':
		if($access['daten']['REMINDER'] < 1) break;
		$id = format_userinput($_POST['id'], 'uint');
		ko_get_reminders($entry, 1, ' and `id` = ' . $id, '', '', TRUE, TRUE);
		if(!$entry['id'] || $entry['id'] != $id) break;

		db_delete_data('ko_reminder', "WHERE `id` = '$id'");
		ko_log_diff('del_reminder', $entry);
		$notifier->addInfo(16);
	break;



	case 'send_test_reminder':
		if($access['daten']['REMINDER'] < 1) break;

		$id = format_userinput($_POST['id'], 'uint');
		ko_get_reminders($entry, 1, ' and `id` = ' . $id, '', '', TRUE, TRUE);
		if(!$entry['id'] || $entry['id'] != $id) break;

		$done = ko_task_reminder($id);

		if (!$notifier->hasErrors()) {
			$notifier->addInfo(17, '', array(implode(', ', $done)));
		}
	break;



	case "list_rooms":
		$_SESSION['show'] = 'list_rooms';
		break;

	case "new_room":
		$_SESSION["show"] = "new_room";
		break;
	case "edit_room":
		$edit_id = format_userinput($_POST['id'], 'uint');
		$_SESSION["show_back"] = $_SESSION["show"];
		$_SESSION['show'] = 'edit_room';
		$onload_code = "form_set_first_input();".$onload_code;
		break;

	case 'submit_new_event_room':
		$id = kota_submit_multiedit('', 'new_room');

		if(!$notifier->hasErrors()) {
			$notifier->addInfo(18);
			$_SESSION['show'] = 'list_rooms';
		}
		break;
	case 'submit_edit_event_room':
		$id = kota_submit_multiedit('', 'edit_room');

		if(!$notifier->hasErrors()) {
			$notifier->addInfo(19);
			$_SESSION['show'] = 'list_rooms';
		}
		break;

	case 'delete_room':
		if(FALSE === ($del_id = format_userinput($_POST['id'], 'uint', TRUE))) {
			trigger_error('Not allowed del_id: '.$_POST['id'], E_USER_ERROR);
		}
		if(!$del_id) {
			if(FALSE === ($del_id = format_userinput($_GET['id'], 'uint', TRUE))) {
				trigger_error('Not allowed del_id: '.$_GET['id'], E_USER_ERROR);
			}
		}
		if(!$del_id) break;

		if(ko_delete_event_room($del_id)) {
			$notifier->addTextInfo(getLL("action_delete_event_room_success"));
		} else {
			$notifier->addTextError(getLL("action_delete_event_room_error"));
		}

		break;

	case 'export_xls_daten':
		if($access['daten']['MAX'] < 1 || $_SESSION['ses_userid'] == ko_get_guest_id()) break;

		//Get selected columns from GET
		$cols = $_POST['sel_xls_cols'] ? $_POST['sel_xls_cols'] : $_GET['sel_xls_cols'];

		// check if we should include rows from event program
		if (substr($cols, 0, 1) == 'p') {
			$includeProgram = TRUE;
		} else {
			$includeProgram = FALSE;
		}
		$cols = substr($cols, 1);

		if($cols == '_session') {
			$value = implode(',', $_SESSION['kota_show_cols_ko_event']);
		} else {
			//Get preset from userprefs
			$name = format_userinput($cols, 'js', FALSE, 0, array(), '@');
			if($name == '') break;
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

		$ids = array();
		if(!empty($_GET['chk'])) {
			foreach (explode(',', $_GET['chk']) as $c_i) {
				$id = format_userinput($c_i, 'uint');
				if ($id) $ids[] = $id;
			}
		}

		$filename = ko_list_events('all', 'xls', TRUE, $includeProgram, $ids);
		$onload_code = "ko_popup('".$ko_path."download.php?action=file&amp;file=".substr($filename, 3)."');";

		//Restore column
		$_SESSION['kota_show_cols_ko_event'] = $orig_cols;
	break;


	case 'export_single_pdf_settings':
		if($access['daten']['MAX'] < 1 || $_SESSION['ses_userid'] == ko_get_guest_id()) break;
		$id = format_userinput($_GET['id'], 'uint');
		$fromRota = format_userinput($_GET['module'], 'alphanum') == 'rota';
		$event = db_select_data('ko_event', "WHERE `id` = {$id}", '*', '', '', TRUE);

		if (!$id || $event['id'] != $id || $access['daten'][$event['eventgruppen_id']] < 1) break;

		$eventId = $id;

		$_SESSION['show_back'] = $_SESSION['show'];
		$_SESSION['show'] = 'export_single_pdf_settings';
		break;


	case 'export_single_pdf':
		if($access['daten']['MAX'] < 1 || $_SESSION['ses_userid'] == ko_get_guest_id()) break;
		$id = format_userinput($_POST['id'], 'uint');
		if (!$id) break;

		$fromRota = format_userinput($_POST['from_rota'], 'uint') ? TRUE : FALSE;
		$rota_filterlist = format_userinput($_POST['sel_rota'], 'text');

		$event = db_select_data('ko_event', "WHERE `id` = {$id}", '*', '', '', TRUE);

		if (!$id || $event['id'] != $id || $access['daten'][$event['eventgruppen_id']] < 1) break;

		$eventCols_ = format_userinput($_POST['sel_event_cols'], 'text');
		ko_save_userpref($_SESSION['ses_userid'], 'daten_export_single_pdf_event_cols', $eventCols);
		if ($eventCols_ == '_current') {
			$eventCols = $_SESSION['kota_show_cols_ko_event'];
		} else {
			if (substr($eventCols_, 0, 1) == 'g') {
				$name = substr($eventCols_, 12);
				$userId = -1;
			} else {
				$name = substr($eventCols_, 11);
				$userId = $_SESSION['ses_userid'];
			}
			$ups = ko_get_userpref($userId, $name, 'ko_event_colitemset');
			$value = $ups[0]['value'];

			$eventCols = explode(',', $value);
		}
		foreach ($eventCols as $eck => $ec) {
			if (!$ec) unset($eventCols[$eck]);
		}
		//Sort the columns by KOTA listview
		$origColumns = $eventCols;
		$eventCols = array();
		ksort($KOTA['ko_event']['_listview']);
		foreach($KOTA['ko_event']['_listview'] as $col) {
			if(!in_array($col['name'], $origColumns)) continue;
			$eventCols[] = $col['name'];
		}

		$textBefore = format_userinput($_POST['txt_text_before'], 'text');
		$textAfter = format_userinput($_POST['txt_text_after'], 'text');
		ko_save_userpref($_SESSION['ses_userid'], 'daten_export_single_pdf_text_before', $textBefore);
		ko_save_userpref($_SESSION['ses_userid'], 'daten_export_single_pdf_text_after', $textAfter);

		$settings = [
			'eventCols' => $eventCols,
			'textBefore' => $textBefore,
			'textAfter' => $textAfter,
			'rotaFilterlist' => $rota_filterlist,
		];

		$filename = ko_daten_export_single_pdf($event, $settings);

		if ($fromRota) {
			$onload_code = "jumpToUrl('/rota?action=download_single_event_export&filename=".$filename."');";
		} else {
			$onload_code = "ko_popup('".$ko_path."download.php?action=file&amp;file=download/pdf/".$filename."');";

			$_SESSION['show'] = $_SESSION['show_back'] ? $_SESSION['show_back'] : 'all_events';
		}

	break;



	case 'export_pdf':
		if($access['daten']['MAX'] < 1 || $_SESSION['ses_userid'] == ko_get_guest_id()) break;

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
				$start = add2date(date('Y-m-01'), 'month', $inc, TRUE);
				$filename = basename(ko_daten_export_months(1, date('m', strtotime($start)), date('Y', strtotime($start))));
				$onload_code = "ko_popup('".$ko_path."download.php?action=file&amp;file=download/pdf/".$filename."');";
			break;

			case 'y':
				if ($inc == "minus1") {
					$inc = -1;
				} else {
					$inc = intval($inc);
				}
				$filename = basename(ko_export_cal_pdf_year('daten', 1, (int)date('Y')+$inc));
				$onload_code = "ko_popup('".$ko_path."download.php?action=file&amp;file=download/pdf/".$filename."');";
			break;

			case 's':
				list($inc, $month) = explode(':', $inc);
				if ($inc == "minus1") {
					$inc = -1;
				} else {
					$inc = intval($inc);
				}
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




	default:
		if(!hook_action_handler($do_action))
      include($ko_path."inc/abuse.inc");
	break;
}//switch(do_action)


if($_GET['returnAction']) {
	if(FALSE === strpos($_GET['returnAction'], ':')) {
		$module = $ko_menu_akt;
		$action = format_userinput($_GET['returnAction'], 'alpha+');
	} else {
		list($module, $action) = explode(':', $_GET['returnAction']);
	}
	if(ko_module_installed($module) && $action) {
		header('Location: '.'/'.$module.'/?action='.$action, TRUE, 302); exit;
	}
}


//HOOK: Plugins erlauben, die bestehenden Actions zu erweitern
hook_action_handler_add($do_action);



//Reread access rights if necessary
if(in_array($do_action, array('submit_neue_gruppe', 'submit_edit_gruppe', 'delete_gruppe', 'submit_new_ical'))) {
	ko_get_access('daten', '', TRUE);
}


// If we are handling a request that was redirected by /inc/form.php, then exit here
if ($asyncFormSubmit == 1) {
	throw new Exception('async-form-submit-dummy-exception');
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
if($_SESSION['sort_rooms'] == '') {
	$_SESSION['sort_rooms']= 'title';
	$_SESSION['sort_rooms_order'] = 'ASC';
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
if($_SESSION['filter_start'] === NULL) {
	$_SESSION['filter_start'] = 'today';
	$_SESSION['filter_ende'] = 'immer';
	ko_save_userpref($_SESSION['ses_userid'], 'daten_filter_start', $_SESSION['filter_start']);
	ko_save_userpref($_SESSION['ses_userid'], 'daten_filter_ende', $_SESSION['filter_ende']);
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
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title><?php print "$HTML_TITLE: ".getLL("module_".$ko_menu_akt); ?></title>
<?php
$js_files = array();
//Color picker for event group form
if(in_array($_SESSION['show'], array('neue_gruppe', 'edit_gruppe', 'new_ical'))) {
	print '<script language="javaScript" type="text/javascript">ko_path = \''.$ko_path.'\'</script>';
}
if($_SESSION['show'] == 'calendar') {
	$js_files[] = $ko_path.'inc/fullcalendar/lib/fullcalendar.min.js';
}
$js_files[] = $ko_path.'inc/ckeditor/ckeditor.js';
$js_files[] = $ko_path.'inc/ckeditor/adapters/jquery.js';

print ko_include_js($js_files);

$css_files = array();
if($_SESSION['show'] == 'calendar') {
	$css_files[] = $ko_path.'inc/fullcalendar/lib/fullcalendar.min.css';
}
print ko_include_css($css_files);
// include CSS to give color of eg to row in list
if (in_array($_SESSION['show'], array('all_events', 'list_events_mod'))) {
	print daten_get_eg_color_css();
}
include($ko_path.'inc/js-sessiontimeout.inc');
include("inc/js-daten.inc");

//Include JS from rota module when editing an event
if(in_array($_SESSION['show'], array('edit_termin')) && ko_module_installed('rota')) include($ko_path.'rota/inc/js-rota.inc');
if(in_array($_SESSION["show"], array("neuer_termin", "edit_termin"))) include("inc/js-seleventgroup.inc");
?>
</head>

<body onload="session_time_init();<?php if(isset($onload_code)) print $onload_code; ?>">

<?php
/*
 * Gibt bei erfolgreichem Login das Menü aus, sonst einfach die Loginfelder
 */
include($ko_path . "menu.php");
ko_get_outer_submenu_code('daten');

?>

<main class="main">
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
		ko_list_groups();
	break;

	case "list_rooms":
		ko_list_rooms();
		break;
	case "new_room":
		ko_formular_room("new");
		break;
	case "edit_room":
		ko_formular_room("edit", $edit_id);
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

	case 'new_ical':
		ko_formular_ical('new');
	break;

	case "import":
		ko_daten_import($_SESSION["import_daten_state"]);
	break;  //import

	case "calendar":
		ko_daten_calendar();
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

	case 'export_single_pdf_settings':
		ko_daten_formular_export_single_pdf_settings();
	break;

	case 'list_absence':

		if(!empty($_GET['set_person_filter'])) {
			unset($_SESSION['kota_filter']["ko_event_absence"]["leute_id"]);
			$_SESSION['kota_filter']["ko_event_absence"]["leute_id"][0] = format_userinput($_GET['set_person_filter'], 'uint');
		}
		ko_daten_list_absence();
		break;

	case 'add_absence':
		ko_daten_formular_absence("new");
		break;
	case 'edit_absence':
		ko_daten_formular_absence("edit", format_userinput($_REQUEST['id'],'int'));
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
</main>

</div>

<?php include($ko_path . "footer.php"); ?>

</body>
</html>
