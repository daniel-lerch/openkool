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

ob_start();  //Ausgabe-Pufferung starten

$ko_path = "../";
$ko_menu_akt = "admin";

include($ko_path . "inc/ko.inc.php");
include("inc/admin.inc.php");

$notifier = koNotifier::Instance();

//Redirect to SSL if needed
ko_check_ssl();

if(!ko_module_installed("admin")) {
	header("Location: ".$BASE_URL."index.php");  //Absolute URL
}

ob_end_flush();  //Puffer flushen

//Get access rights
ko_get_access('admin');

//kOOL Table Array
ko_include_kota(array('ko_pdf_layout', 'ko_news', '_ko_sms_log', 'ko_log', 'ko_admingroups'));

//*** Plugins einlesen:
$hooks = hook_include_main("admin");
if(sizeof($hooks) > 0) foreach($hooks as $hook) include_once($hook);


//***Action auslesen:
if($_POST["action"]) $do_action = $_POST["action"];
else if($_GET["action"]) $do_action = $_GET["action"];
else $do_action = "";

//Reset show_start if from another module
if($_SERVER['HTTP_REFERER'] != '' && FALSE === strpos($_SERVER['HTTP_REFERER'], '/'.$ko_menu_akt.'/')) $_SESSION['show_start'] = 1;

switch($do_action) {

	//Anzeigen
	case "set_allgemein":
		if($access['admin']['MAX'] < 2) break;
		$_SESSION["show"] = "set_allgemein";
	break;

	case "set_etiketten":
		if($access['admin']['MAX'] < 2) break;
		$_SESSION["show"] = "set_etiketten";
	break;

	case "set_leute_pdf":
		if($access['admin']['MAX'] < 2) break;
		$_SESSION["show"] = "set_leute_pdf";
	break;

	case "set_layout":
		if($access['admin']['MAX'] < 1) break;
		$_SESSION["show"] = "set_layout";
	break;

	case "set_layout_guest":
		if($access['admin']['MAX'] < 3) break;
		$_SESSION["show"] = "set_layout_guest";
	break;

	case "set_show_logins":
		if($access['admin']['MAX'] < 5) break;
		if($_SESSION['show'] == 'show_logins') $_SESSION['show_start'] = 1;
		$_SESSION['show'] = 'show_logins';
	break;

	case "show_logs":
		if($access['admin']['MAX'] < 4) break;
		if($_SESSION['show'] == 'show_logs') $_SESSION['show_logs_start'] = 1;
		$_SESSION['show'] = 'show_logs';
	break;

	case 'show_sms_log':
		if($access['admin']['MAX'] < 1) break;
		if(!ko_module_installed('sms')) break;
		$_SESSION['show'] = 'show_sms_log';
		$_SESSION['show_start'] = 1;
	break;

	case "submit_log_filter":
		if($access['admin']['MAX'] < 4) break;
		$_SESSION["log_type"] = $_GET["set_log_filter"];
	break;

	case "submit_user_filter":
		if($access['admin']['MAX'] < 4) break;
		$_SESSION["log_user"] = $_GET["set_user_filter"];
	break;

	case "submit_time_filter":
		if($access['admin']['MAX'] < 1) break;
		$_SESSION["log_time"] = $_GET["set_time_filter"];
	break;

	case "submit_hide_guest":
		if($access['admin']['MAX'] < 4) break;

		if($_GET["logs_hide_status"] == "true") {
			$_SESSION["logs_hide_guest"] = TRUE;
		} else if($_GET["logs_hide_status"] == "false") {
			$_SESSION["logs_hide_guest"] = FALSE;
		}
		$_SESSION["show_start"] = 1;
	break;

	case "submit_clear_guest":
		if($_SESSION["ses_userid"] != ko_get_root_id()) break;
		db_delete_data("ko_log", "WHERE `type`='guest'");
		$notifier->addInfo(7, $do_action);
	break;


	case "change_password":
		if(ko_get_setting("change_password") == 1) {
			$_SESSION["show"] = "change_password";
		}
	break;

	case "submit_change_password":
		if(ko_get_setting("change_password") == 1) {
			$old = $_POST["txt_pwd_old"];
			$new1 = $_POST["txt_pwd_new1"];
			$new2 = $_POST["txt_pwd_new2"];

			ko_get_login($_SESSION["ses_userid"], $login);
			if(md5($old) != $login["password"]) $notifier->addError(15, $do_action);
			if(!$new1 || $new1 != $new2) $notifier->addError(16, $do_action);

			if(!$notifier->hasErrors()) {
				//Update DB
				db_update_data("ko_admin", "WHERE `id` = '".$_SESSION["ses_userid"]."'", array("password" => md5($new1)));
				//Update LDAP access
				ko_admin_check_ldap_login($_SESSION['ses_userid']);
				$notifier->addInfo(8, $do_action);
			}
		}
	break;


	//Speichern
	case "save_set_allgemein":
		if($access['admin']['MAX'] < 2) break;

		if(in_array('leute', $MODULES)) {
			$login_edit_person = format_userinput($_POST["rd_login_edit_person"], "uint", FALSE, 1);
			if($login_edit_person == 0 || $login_edit_person == 1)
				ko_set_setting("login_edit_person", $login_edit_person);
		}

		if(in_array('sms', $MODULES)) {
			$sms_country_code = format_userinput($_POST["txt_sms_country_code"], "uint");
			ko_set_setting("sms_country_code", $sms_country_code);
		}

		if(in_array('mailing', $MODULES) && is_array($MAILING_PARAMETER) && $MAILING_PARAMETER['domain'] != '') {
			ko_set_setting('mailing_mails_per_cycle', format_userinput($_POST['txt_mailing_mails_per_cycle'], 'uint'));
			ko_set_setting('mailing_max_recipients', format_userinput($_POST['txt_mailing_max_recipients'], 'uint'));
			ko_set_setting('mailing_only_alias', format_userinput($_POST['chk_mailing_only_alias'], 'uint'));
			ko_set_setting('mailing_allow_double', format_userinput($_POST['chk_mailing_allow_double'], 'uint'));
		}

		//XLS export
		ko_set_setting('xls_default_font', format_userinput($_POST['txt_xls_default_font'], 'text'));
		ko_set_setting('xls_title_font', format_userinput($_POST['txt_xls_title_font'], 'text'));
		ko_set_setting('xls_title_bold', format_userinput($_POST['chk_xls_title_bold'], 'uint'));
		ko_set_setting('xls_title_color', format_userinput($_POST['txt_xls_title_color'], 'alpha'));

		$change_password = format_userinput($_POST["rd_change_password"], "uint", FALSE, 1);
		if($change_password == 0 || $change_password == 1)
			ko_set_setting("change_password", $change_password);

		//Contact settings
		$contact_fields = array('name', 'address', 'zip', 'city', 'phone', 'url');
		foreach($contact_fields as $field) {
			ko_set_setting('info_'.$field, format_userinput($_POST['txt_contact_'.$field], 'text'));
		}
		$email_info = format_userinput($_POST['txt_contact_email'], 'email');
		if(check_email($email_info)) ko_set_setting('info_email', $email_info);
			
			
		$notifier->addInfo(1, $do_action);
		$_SESSION["show"] = "set_allgemein";
	break;



	case 'submit_sms_sender_id':
		if(!ko_module_installed('sms') || $SMS_PARAMETER['provider'] != 'aspsms' || !$SMS_PARAMETER['user'] || !$SMS_PARAMETER['pass']) break;

		$number = $_POST['sms_sender_id'];
		$is_number = check_natel($number);
		$code = trim(format_userinput($_POST['sms_sender_id_code'], 'alphanum'));
		if($is_number === TRUE) {
			require_once($ko_path.'inc/aspsms.php');
			$sms = new SMS($SMS_PARAMETER['user'], $SMS_PARAMETER['pass']);
			if($code != '') {
				//Unlock number
				$sms->unlockOriginator($number, $code);
				$credits = (float)$sms->getCreditsUsed();
				//Check for success and add it to the list of known senderIDs
				$ret = $sms->checkOriginatorAuthorization($number);
				if($ret != 1) {
					$sender_ids = explode(',', ko_get_setting('sms_sender_ids'));
					$sender_ids[] = $number;
					$new = array();
					foreach($sender_ids as $id) {
						if(!$id) continue;
						$new[] = $id;
					}
					ko_set_setting('sms_sender_ids', implode(',', $new));
					ko_log('sms_new_sender_id', $number.' - '.($credits+(float)$sms->getCreditsUsed()));
				}
			} else {
				//Have unlock code sent to new senderID
				$ret = $sms->sendOriginatorUnlockCode($number);
				if($ret == 1) {
					ko_log('sms_unlock_code', $number.' - '.$sms->getCreditsUsed());
				} else {
					$error_txt_add = $ret.' '.getLL('error_aspsms_'.intval($ret));
					$notifier->addError(17, $do_action, array($error_txt_add));
				}
			}
		} else {
			//alpha nummeric senderIDs don't have to be registered with aspsms
			$sender_ids = explode(',', ko_get_setting('sms_sender_ids'));
			$senderID = format_userinput($_POST['sms_sender_id'], 'alphanum++');
			if($senderID != '' && !in_array($senderID, $sender_ids)) {
				$sender_ids[] = $senderID;
				$new = array();
				foreach($sender_ids as $id) {
					if(!$id) continue;
					$new[] = $id;
				}
				$new = array_unique($new);
				ko_set_setting('sms_sender_ids', implode(',', $new));
				ko_log('sms_new_sender_id', $senderID);
			}
		}
	break;



	case 'delete_sms_sender_id':
		if($access['admin']['MAX'] < 2) break;

		if(!ko_module_installed('sms') || $SMS_PARAMETER['provider'] != 'aspsms' || !$SMS_PARAMETER['user'] || !$SMS_PARAMETER['pass']) break;
		$senderID = urldecode($_GET['sender_id']);
		if(!$senderID) break;
		
		$sender_ids = explode(',', ko_get_setting('sms_sender_ids'));
		$new = array();
		foreach($sender_ids as $id) {
			if($id == $senderID) continue;
			$new[] = $id;
		}
		$new = array_unique($new);
		ko_set_setting('sms_sender_ids', implode(',', $new));
		ko_log('sms_delete_sender_id', $senderID);
		$notifier->addInfo(9, $do_action);
	break;



	case 'submit_sms_sender_id_clickatell':
		if(!ko_module_installed('sms') || $SMS_PARAMETER['provider'] == 'aspsms' || !$SMS_PARAMETER['user'] || !$SMS_PARAMETER['pass']) break;

		//Get all senderIDs
		$sender_ids = explode(',', ko_get_setting('sms_sender_ids'));

		//Check for valid number
		$senderID = $_POST['sms_sender_id'];
		check_natel($senderID);
		if($senderID != '') {
			$sender_ids[] = $senderID;
		} else {
			//If not a number store it as non-nummeric senderID
			$senderID = format_userinput($_POST['sms_sender_id'], 'alphanum+');
			if($senderID != '' && !in_array($senderID, $sender_ids)) {
				$sender_ids[] = $senderID;
			}
		}
		$new = array();
		foreach($sender_ids as $id) {
			if(!$id) continue;
			$new[] = $id;
		}
		ko_set_setting('sms_sender_ids', implode(',', $new));
		ko_log('sms_new_sender_id', $senderID);
	break;



	case "save_set_layout_guest":
		if($access['admin']['MAX'] < 3) break;
	case "save_set_layout":
		if($access['admin']['MAX'] < 1) break;

		$uid = ($do_action == "save_set_layout") ? $_SESSION["ses_userid"] : ko_get_guest_id();

		//Frontmodules speichern
		$fm_left = $fm_center = $fm_right = "";
		foreach($FRONTMODULES as $fm_i => $fm) {
			if(!ko_check_fm_for_user($fm_i, $uid)) continue;
			switch($_POST["rd_fm_".$fm_i]) {
				case "nicht":
				break;

				case "left":
					$fm_left .= $fm_i . ",";
				break;

				case "center":
					$fm_center .= $fm_i . ",";
				break;

				case "right":
					$fm_right .= $fm_i . ",";
				break;
			}//switch()
		}//foreach()
		ko_save_userpref($uid, "front_modules_left", substr($fm_left,0,-1));
		ko_save_userpref($uid, "front_modules_center", substr($fm_center,0,-1));
		ko_save_userpref($uid, "front_modules_right", substr($fm_right,0,-1));

		//Limiten
		ko_save_userpref($uid, "show_limit_logins", format_userinput($_POST["txt_limit_logins"], "uint"));
		ko_save_userpref($uid, "show_limit_fileshare", format_userinput($_POST["txt_limit_fileshare"], "uint"));
		ko_save_userpref($uid, "show_limit_tapes", format_userinput($_POST["txt_limit_tapes"], "uint"));

		//Default-Seiten pro Modul
		ko_save_userpref($uid, "default_view_admin", format_userinput($_POST["sel_admin"], "js"));
		ko_save_userpref($uid, "default_view_tapes", format_userinput($_POST["sel_tapes"], "js"));
		ko_save_userpref($uid, "default_view_fileshare", format_userinput($_POST["sel_fileshare"], "js"));
		ko_save_userpref($uid, 'default_module', format_userinput($_POST['sel_default_module'], 'js'));

		//Popupmenu-Einstellungen
		ko_save_userpref($uid, "modules_dropdown", format_userinput($_POST["sel_modules_dropdown"], "js"));
		ko_save_userpref($uid, "menu_order", format_userinput($_POST["sel_menu_order"], "alphanumlist"));

		//Diverses-Einstellungen
		ko_save_userpref($uid, "save_files_as_share", format_userinput($_POST["sel_save_files_as_share"], "uint", FALSE, 1));
		ko_save_userpref($uid, 'export_table_format', format_userinput($_POST['export_table_format'], 'alphanum'));
		ko_save_userpref($uid, 'show_notes', format_userinput($_POST['show_notes'], 'uint', FALSE, 1));
		ko_save_userpref($uid, 'save_kota_filter', format_userinput($_POST['save_kota_filter'], 'uint', FALSE, 1));
		ko_save_userpref($uid, 'download_not_directly', format_userinput($_POST['download_not_directly'], 'uint', FALSE, 1));


		$notifier->addInfo(1, $do_action);
		$_SESSION["show"] = ($do_action == "save_set_layout") ? "set_layout" : "set_layout_guest";
	break;



	case "login_details":
		$login_id = format_userinput($_GET["id"], "uint");
		$_SESSION["show"] = "login_details";
	break;


	case "set_show_admingroups":
		if($access['admin']['MAX'] < 5) break;

		$_SESSION["show"] = "show_admingroups";
	break;


	case "delete_admingroup":
		if($access['admin']['MAX'] < 5) break;

		$id = format_userinput($_POST["id"], "uint");
		if(!$id) break;

		$old = db_select_data('ko_admingroups', "WHERE `id` = '$id'", '*', '', '', TRUE);

		//Delete Admingroup
		db_delete_data("ko_admingroups", "WHERE `id` = '$id'");

		//LDAP-Login
		if(ko_do_ldap()) {
			//Check all logins assigned to this group for ldap access
			$logins = db_select_data("ko_admin", "WHERE `admingroups` REGEXP '(^|,)$id($|,)' AND `disabled` = ''", "*");
			foreach($logins as $login) {
				ko_admin_check_ldap_login($login);
				//unset deleted admingroup in login
				$new_admingroups = array();
				foreach(explode(",", $login["admingroups"]) as $gid) {
					if($gid == $id || !$gid) continue;
					$new_admingroups[] = $gid;
				}
				ko_save_admin("admingroups", $login["id"], implode(",", $new_admingroups));
			}//foreach(logins as login)
		}//if(ko_do_ldap())

		//Log entry
		ko_log("delete_admingroup", "id: $id, ".$old['name']);
	break;


	case "set_new_admingroup":
		if($access['admin']['MAX'] < 5) break;

		$_SESSION["show"] = "new_admingroup";
		$onload_code = "form_set_first_input();".$onload_code;
	break;


	case "edit_admingroup":
		if($access['admin']['MAX'] < 5) break;

		$edit_id = format_userinput($_POST["id"], "uint");
		if(!$edit_id) break;
		$_SESSION["show"] = "edit_admingroup";
		$onload_code = "form_set_first_input();".$onload_code;
	break;


	case "submit_new_admingroup":
		if($access['admin']['MAX'] < 5) break;

		//Gruppenname verlangen
		$txt_name = format_userinput($_POST["txt_name"], "text");
		if(!$txt_name) $notifier->addError(12, $do_action);
		//Gruppenname darf nicht schon existieren
		$admingroups = ko_get_admingroups();
		foreach($admingroups as $ag) {
			if($ag["name"] == $txt_name) $notifier->addError(13, $do_action);
		}

		if(!$notifier->hasErrors()) {
			$save_modules = explode(",", format_userinput($_POST["sel_modules"], "alphanumlist"));
			foreach($save_modules as $m_i => $m) {
				if(!in_array($m, $MODULES)) unset($save_modules[$m_i]);
				if($m == 'tools') unset($save_modules[$m_i]);
			}
			$data["modules"] = implode(",", $save_modules);

			//Gruppen-Daten speichern
			$data["name"] = $txt_name;
			$id = db_insert_data("ko_admingroups", $data);

			//Log
			ko_log("new_admingroup", "$id: $txt_name, Module: \"".implode(", ", $save_modules)."\"");
		}//if(!error)
		$edit_id = $id;
		$_SESSION["show"] = $notifier->hasErrors() ? "new_admingroup" : "edit_admingroup";
	break;



	case "submit_edit_admingroup":
		if($access['admin']['MAX'] < 5) break;

		$data = array();
		if(FALSE === ($id = format_userinput($_POST["id"], "uint", TRUE))) {
	    	trigger_error("Not allowed id: ".$id, E_USER_ERROR);
    	}
		//Gruppen-Name speichern
		$name = format_userinput($_POST["txt_name"], "js");
		if($_POST["txt_name"] != "") {
			ko_save_admin("name", $id, $name, "admingroup");
		} else {
			$notifier->addError(12, $do_action);
			break;
		}
		$_old_admingroup = ko_get_admingroups($id);
		$old_admingroup = $_old_admingroup[$id];

		//Module speichern
		$save_modules = explode(",", format_userinput($_POST["sel_modules"], "alphanumlist"));
		foreach($save_modules as $m_i => $m) {
			if(!in_array($m, $MODULES)) unset($save_modules[$m_i]);
			if($m == 'tools') unset($save_modules[$m_i]);
		}
		foreach($MODULES as $m) {
      if(!in_array($m, $save_modules)) {
				ko_save_admin($m, $id, "0", "admingroup");
				if($m == "leute") {
					ko_save_admin("leute_filter", $id, "0", "admingroup");
					ko_save_admin("leute_spalten", $id, "0", "admingroup");
				}
			}
		}
		ko_save_admin("modules", $id, implode(",", $save_modules), "admingroup");


		//Rechte speichern
		$log_message  = "$name ($id): ";
		$log_message .= getLL('admin_logins_modules').': "'.implode(", ", $save_modules).'", ';

		$done_modules = array();
		if(in_array("leute", $save_modules)) {  //Leute-Rechte
			$done_modules[] = 'leute';
			$leute_save_string = format_userinput($_POST["sel_rechte_leute"], "uint", FALSE, 1);
			ko_save_admin("leute", $id, $leute_save_string, "admingroup");
			$log_message .= getLL("module_leute").': "'.str_replace(",", ", ", $leute_save_string).'", ';

			//Filter für Stufen
			$save_filter = ko_get_leute_admin_filter($id, "admingroup");
			if(!$save_filter) $save_filter = array();
			$filterset = array_merge((array)ko_get_userpref('-1', '', 'filterset'), (array)ko_get_userpref($_SESSION['ses_userid'], '', 'filterset'));
			for($i=1; $i < 4; $i++) {
				$filter = format_userinput($_POST["sel_rechte_leute_$i"], "js");
				if($filter == -1) {
					continue;
				} else if($filter == "") {
					unset($save_filter[$i]);
				} else {
					//A new filter has been selected
					if(preg_match('/sg[0-9]{4}/', $filter) == 1) {  //small group
						$sg = db_select_data('ko_kleingruppen', "WHERE `id` = '".format_userinput($filter, 'uint')."'", '*', '', '', TRUE);
						$sgFilter = db_select_data('ko_filter', "WHERE `typ` = 'leute' AND `name` = 'smallgroup'", 'id', '', '', TRUE);
						$save_filter[$i]['value'] = $filter;
						$save_filter[$i]['name'] = $sg['name'];
						$save_filter[$i]['filter'] = array('link' => 'and', 0 => array(0 => $sgFilter['id'], 1 => array(1 => $sg['id']), 2 => 0));
					} else if(preg_match('/g[0-9]{6}/', $filter) == 1) {  //group
						$gid = substr($filter, -6);
						$gr = db_select_data('ko_groups', "WHERE `id` = '$gid'", '*', '', '', TRUE);
						$grFilter = db_select_data('ko_filter', "WHERE `typ` = 'leute' AND `name` = 'group'", 'id', '', '', TRUE);
						$save_filter[$i]['value'] = $filter;
						$save_filter[$i]['name'] = $gr['name'];
						$save_filter[$i]['filter'] = array('link' => 'and', 0 => array(0 => $grFilter['id'], 1 => array(1 => $filter, 2 => ''), 2 => 0));
					} else {  //filter preset
						$save_filter[$i]["name"] = $filter;
						$save_filter[$i]['value'] = $filter;
						//Filter-Infos aus Filterset lesen
						foreach($filterset as $set) {
							if($set["key"] == $filter) {
								$save_filter[$i]["filter"] = unserialize($set["value"]);
							}
						}
					}
				}
			}//for(i=1..3)
			ko_save_admin("leute_filter", $id, serialize($save_filter), "admingroup");

			//Spaltenvorlagen
			$save_preset = ko_get_leute_admin_spalten($id, "admingroup");
			if(!$save_preset) $save_preset = array();
			$presets = array_merge((array)ko_get_userpref('-1', '', 'leute_itemset', 'ORDER BY `key` ASC'), (array)ko_get_userpref($_SESSION['ses_userid'], '', 'leute_itemset', 'ORDER BY `key` ASC'));
			//view
			$preset = format_userinput($_POST["sel_leute_cols_view"], "js");
			if($preset == -1) {
			} else if($preset == "") {
				unset($save_preset["view"]);
				unset($save_preset["view_name"]);
			} else {
				$save_preset["view_name"] = $preset;
				foreach($presets as $p) {
					if($p["key"] == $preset) {
						$save_preset["view"] = explode(",", $p["value"]);
					}
				}//foreach(presets as p)
			}//if..elseif..else()
			//edit
			$preset = format_userinput($_POST["sel_leute_cols_edit"], "js");
			if($preset == -1) {
			} else if($preset == "") {
				unset($save_preset["edit"]);
				unset($save_preset["edit_name"]);
			} else {
				$save_preset["edit_name"] = $preset;
				foreach($presets as $p) {
					if($p["key"] == $preset) {
						$save_preset["edit"] = explode(",", $p["value"]);
					}
				}//foreach(presets as p)
			}//if..elseif..else()
			if(sizeof($save_preset) == 0) {
				$save_preset = 0;
			} else {
				//Add edit preset to view as edit also means view
				if($save_preset["view"]) $save_preset["view"] = array_unique(array_merge((array)$save_preset["view"], (array)$save_preset["edit"]));
			}
			ko_save_admin("leute_spalten", $id, serialize($save_preset), "admingroup");

			//Admin groups
			$lag = format_userinput($_POST['sel_leute_admin_group'], 'alphanum', FALSE, 0, array(), ':');
			ko_save_admin('leute_groups', $id, $lag, 'admingroup');

			//Group subscriptions
			$gs = format_userinput($_POST['chk_leute_admin_gs'], 'uint');
			ko_save_admin('leute_gs', $id, $gs, 'admingroup');

			//Allow user to assign people to own group
			//Only store if $lag is set
			if($lag) {
				$assign = format_userinput($_POST['chk_leute_admin_assign'], 'uint');
				ko_save_admin('leute_assign', $id, $assign, 'admingroup');
			} else {
				ko_save_admin('leute_assign', $id, 0, 'admingroup');
			}

		}//if(leute_module)
		else if(in_array('leute', explode(',', $old_admingroup['modules']))) {
			//If leute module is removed from admingroup, then also set all leute_admin fields to 0
			ko_save_admin('leute_assign', $id, 0, 'admingroup');
			ko_save_admin('leute_gs', $id, 0, 'admingroup');
			ko_save_admin('leute_groups', $id, '', 'admingroup');
		}


		if(in_array('groups', $save_modules)) {
			$done_modules[] = 'groups';
			$groups_save_string = format_userinput($_POST['sel_rechte_groups'], 'uint', FALSE, 1);
			ko_save_admin('groups', $id, $groups_save_string, 'admingroup');
			$log_message .= getLL('module_groups').': "'.str_replace(',', ', ', $leute_save_string).'", ';

			//Loop über die drei Rechte-Stufen
			$mode = array('', 'view', 'new', 'edit', 'del');
			for($i=4; $i>0; $i--) {
				if(isset($_POST["sel_groups_rights_".$mode[$i]])) {
					//Nur Änderungen bearbeiten
					$old = explode(",", format_userinput($_POST["old_sel_groups_rights_".$mode[$i]], "intlist", FALSE, 0, array(), ":"));
					$new = explode(",", format_userinput($_POST["sel_groups_rights_".$mode[$i]], "intlist", FALSE, 0, array(), ":"));
					$deleted = array_diff($old, $new);
					$added = array_diff($new, $old);
				
					//Login aus gelöschten Gruppen entfernen
					foreach($deleted as $gid) {
						$gid = substr($gid, -6);  //Nur letzte ID verwenden, davor steht die Motherline
						//bisherige Rechte auslesen
						$group = db_select_data("ko_groups", "WHERE `id` = '$gid'", "id,rights_".$mode[$i]);
						$rights_array = explode(",", $group[$gid]["rights_".$mode[$i]]);
						//Zu löschendes Login finden und entfernen
						foreach($rights_array as $index => $right) if($right == 'g'.$id) unset($rights_array[$index]);
						foreach($rights_array as $a => $b) if(!$b) unset($rights_array[$a]);  //Leere Einträge löschen
						//Neuer Eintrag in Gruppe speichern
						db_update_data("ko_groups", "WHERE `id` = '$gid'", array("rights_".$mode[$i] => implode(",", $rights_array)));
						$all_groups[$gid]['rights_'.$mode[$i]] = implode(',', $rights_array);
					}

					//Login in neu hinzugefügten Gruppen hinzufügen
					foreach($added as $gid) {
						$gid = substr($gid, -6);  //Nur letzte ID verwenden, davor steht die Motherline
						//Bestehende Rechte auslesen
						$group = db_select_data("ko_groups", "WHERE `id` = '$gid'", "id,rights_".$mode[$i]);
						$rights_array = explode(",", $group[$gid]["rights_".$mode[$i]]);
						//Überprüfen, ob Login schon vorhanden ist (sollte nicht)
						$add = TRUE;
						foreach($rights_array as $right) if($right == 'g'.$id) $add = FALSE;
						if($add) $rights_array[] = 'g'.$id;
						foreach($rights_array as $a => $b) if(!$b) unset($rights_array[$a]);  //Leere Einträge löschen
						//Neue Liste der Logins in Gruppe speichern
						db_update_data("ko_groups", "WHERE `id` = '$gid'", array("rights_".$mode[$i] => implode(",", $rights_array)));
						$all_groups[$gid]['rights_'.$mode[$i]] = implode(',', $rights_array);
					}
				}//if(isset(_POST[sel_groups_rights_*]))
			}//for(i=1..3)
		}//if(in_array(groups, save_modules))
		else if(in_array('groups', explode(',', $old_admingroup['modules']))) {
			//If groups module has been deselected then remove all access settings from ko_groups
			foreach(array('view', 'new', 'edit', 'del') as $amode) {
				$granted_groups = db_select_data('ko_groups', "WHERE `rights_".$amode."` REGEXP '(^|,)g$id(,|$)'");
				foreach($granted_groups as $gg) {
					$granted_logins = explode(',', $gg['rights_'.$amode]);
					foreach($granted_logins as $k => $v) {
						if($v == 'g'.$id) unset($granted_logins[$k]);
					}
					db_update_data('ko_groups', "WHERE `id` = '".$gg['id']."'", array('rights_'.$amode => implode(',', $granted_logins)));
					$all_groups[$gg['id']]['rights_'.$amode] = implode(',', $granted_logins);
				}
			}
		}

		foreach($MODULES_GROUP_ACCESS as $module) {
			$done_modules[] = $module;
			if(!in_array($module, $MODULES)) continue;
			if(in_array($module, $save_modules)) {
				$save_string = format_userinput($_POST['sel_rechte_'.$module.'_0'], 'uint', FALSE, 1).',';
				unset($gruppen);
				switch($module) {
					case "daten":
						if(ko_get_setting('daten_access_calendar') == 1) {
							//First get calendars
							$cals = db_select_data('ko_event_calendar', 'WHERE 1=1', '*', 'ORDER BY name ASC');
							foreach($cals as $cid => $cal) $gruppen['cal'.$cid] = $cal;
							//Then add event groups withouth calendar
							$egs = db_select_data('ko_eventgruppen', "WHERE `calendar_id` = '0'", '*', 'ORDER BY name ASC');
							foreach($egs as $eid => $eg) $gruppen[$eid] = $eg;
						} else {
							$egs = db_select_data('ko_eventgruppen', 'WHERE 1=1', '*', 'ORDER BY name ASC');
							foreach($egs as $eid => $eg) $gruppen[$eid] = $eg;
						}
						ko_save_admin($module . '_force_global', $id, $_POST['sel_force_global_'.$module], "admingroups");
						ko_save_admin($module . '_reminder_rights', $id, $_POST['sel_reminder_rights_'.$module], "admingroups");
					break;
					case "reservation":
						if(ko_get_setting('res_access_mode') == 1) {
							ko_get_resitems($items);
							foreach($items as $iid => $item) $gruppen[$iid] = $item;
						} else {
							ko_get_resgroups($resgroups);
							foreach($resgroups as $gid => $g) $gruppen['grp'.$gid] = $g;
						}
						ko_save_admin($module . '_force_global', $id, $_POST['sel_force_global_'.$module], "admingroups");
					break;
					case 'rota': $gruppen = db_select_data('ko_rota_teams', '', '*', 'ORDER BY name ASC'); break;
					case "tapes": ko_get_tapegroups($gruppen); break;
					case "donations": $gruppen = db_select_data("ko_donations_accounts", "", "*", "ORDER BY number ASC"); break;
					case 'tracking': $gruppen = db_select_data('ko_tracking', '', '*', 'ORDER BY name ASC'); break;

					default:
						$gruppen = hook_access_get_groups($module);
				}
				foreach($gruppen as $g_i => $g) {
					$save_string .= format_userinput($_POST["sel_rechte_".$module."_".$g_i], "uint", FALSE, 1)."@".$g_i.",";
				}
			} else $save_string = "0 ";
			ko_save_admin($module, $id, substr($save_string, 0, -1), 'admingroup');
			$log_message .= getLL("module_".$module).': "'.str_replace(",", ", ", $save_string).'", ';
		}

		$done_modules[] = 'tools';
		foreach($MODULES as $module) {
			if(in_array($module, $done_modules)) continue;
			$done_modules[] = $module;

			if(in_array($module, $save_modules)) {
				$save_string = format_userinput($_POST['sel_rechte_'.$module], 'uint', FALSE, 1);
			} else $save_string = '0';
			ko_save_admin($module, $id, $save_string, "admingroup");
			$log_message .= getLL("module_".$module).': "'.str_replace(",", ", ", $save_string).'", ';
		}


		//LDAP-Login
		if(ko_do_ldap()) {
			//Check all logins assigned to this group for ldap access
			$logins = db_select_data("ko_admin", "WHERE `admingroups` REGEXP '(^|,)$id($|,)' AND `disabled` = ''", "*");
			foreach($logins as $login) {
				ko_admin_check_ldap_login($login);
			}
		}//if(ko_do_ldap())


		ko_log("edit_admingroup", $log_message);
		$notifier->addInfo(1, $do_action);

		$edit_id = $id;
		//$_SESSION["show"] = "show_admingroups";
	break;


	case "submit_edit_login":
		if($access['admin']['MAX'] < 5) break;

		if(FALSE === ($id = format_userinput($_POST["id"], "uint", TRUE))) {
	    	trigger_error("Not allowed id: ".$id, E_USER_ERROR);
    	}
		//root darf nur von root bearbeitet werden
		if($id == ko_get_root_id() && $_SESSION["ses_username"] != "root") break;

		//Altes Login speichern (für LDAP)
		ko_get_login($id, $old_login);
	
		//Login-Name speichern
		$login_name = format_userinput($_POST["txt_name"], "js");
		if($_POST["txt_name"] != "") {
			//Changing the name of ko_guest and root is not allowed
			if($id != ko_get_guest_id() && $id != ko_get_root_id()) ko_save_admin("login", $id, $login_name);
		} else {
			$notifier->addError(1, $do_action);
			break;
		}

		//Passwort neu setzen
		if($_POST["txt_pwd1"] != "") {
			if($_POST["txt_pwd1"] == $_POST["txt_pwd2"]) {
				ko_save_admin("password", $id, md5($_POST["txt_pwd1"]));
			} else {
				$notifier->addError(2, $do_action);
				break;
			}
		}

		//Module speichern
		$save_modules = explode(",", format_userinput($_POST["sel_modules"], "alphanumlist"));
		foreach($save_modules as $m_i => $m) {
			if(!in_array($m, $MODULES)) unset($save_modules[$m_i]);
			if($m == 'tools' && $id != ko_get_root_id()) unset($save_modules[$m_i]);
		}
		foreach($MODULES as $m) {
      if(!in_array($m, $save_modules)) {
				ko_save_admin($m, $id, "0");
				if($m == "leute") {
					ko_save_admin("leute_filter", $id, "0");
					ko_save_admin("leute_spalten", $id, "0");
				}
			}
		}
		ko_save_admin("modules", $id, implode(",", $save_modules));


		//Admingroups speichern
		$save_admingroups = explode(",", format_userinput($_POST["sel_admingroups"], "intlist"));
		$admingroups = ko_get_admingroups();
		foreach($save_admingroups as $m_i => $m) {
			if(!in_array($m, array_keys($admingroups))) unset($save_admingroups[$m_i]);
		}
		ko_save_admin("admingroups", $id, implode(",", $save_admingroups));


		//Start log message
		$log_message  = "$login_name ($id): ";
		$log_message .= 'Module: "'.implode(", ", $save_modules).'", Admingroups: "'.implode(", ", $save_admingroups).'", ';


		//Assigned person from DB
		$leute_id = format_userinput($_POST["sel_leute_id"], "uint");
		db_update_data("ko_admin", "WHERE `id` = '$id'", array("leute_id" => $leute_id));
		$log_message .= 'leute_id => '.$leute_id.', ';
		//Admin email
		$email = format_userinput($_POST['txt_email'], 'email');
		db_update_data('ko_admin', 'WHERE `id` = \''.$id.'\'', array('email' => $email));
		$log_message .= 'email => '.$email.', ';
		//Admin mobile
		$mobile = format_userinput($_POST['txt_mobile'], 'alphanum++');
		db_update_data('ko_admin', 'WHERE `id` = \''.$id.'\'', array('mobile' => $mobile));
		$log_message .= 'mobile => '.$mobile.', ';


		//Rechte speichern
		$done_modules = array();
		if(in_array("leute", $save_modules)) {  //Leute-Rechte
			$done_modules[] = 'leute';
			$leute_save_string = format_userinput($_POST["sel_rechte_leute"], "uint", FALSE, 1);
			ko_save_admin("leute", $id, $leute_save_string);
			$log_message .= getLL("module_leute").': "'.str_replace(",", ", ", $leute_save_string).'", ';

			//Filter für Stufen
			$save_filter = ko_get_leute_admin_filter($id, "login");
			$filterset = array_merge((array)ko_get_userpref('-1', '', 'filterset'), (array)ko_get_userpref($_SESSION['ses_userid'], '', 'filterset'));
			for($i=1; $i < 4; $i++) {
				$filter = format_userinput($_POST["sel_rechte_leute_$i"], "js");
				if($filter == -1) {
					continue;
				} else if($filter == "") {
					unset($save_filter[$i]);
				} else {
					//A new filter has been selected
					if(preg_match('/sg[0-9]{4}/', $filter) == 1) {  //small group
						$sg = db_select_data('ko_kleingruppen', "WHERE `id` = '".format_userinput($filter, 'uint')."'", '*', '', '', TRUE);
						$sgFilter = db_select_data('ko_filter', "WHERE `typ` = 'leute' AND `name` = 'smallgroup'", 'id', '', '', TRUE);
						$save_filter[$i]['value'] = $filter;
						$save_filter[$i]['name'] = $sg['name'];
						$save_filter[$i]['filter'] = array('link' => 'and', 0 => array(0 => $sgFilter['id'], 1 => array(1 => $sg['id']), 2 => 0));
					} else if(preg_match('/g[0-9]{6}/', $filter) == 1) {  //group
						$gid = substr($filter, -6);
						$gr = db_select_data('ko_groups', "WHERE `id` = '$gid'", '*', '', '', TRUE);
						$grFilter = db_select_data('ko_filter', "WHERE `typ` = 'leute' AND `name` = 'group'", 'id', '', '', TRUE);
						$save_filter[$i]['value'] = $filter;
						$save_filter[$i]['name'] = $gr['name'];
						$save_filter[$i]['filter'] = array('link' => 'and', 0 => array(0 => $grFilter['id'], 1 => array(1 => $filter, 2 => ''), 2 => 0));
					} else {  //filter preset
						$save_filter[$i]["name"] = $filter;
						$save_filter[$i]['value'] = $filter;
						//Filter-Infos aus Filterset lesen
						foreach($filterset as $set) {
							if($set["key"] == $filter) {
								$save_filter[$i]["filter"] = unserialize($set["value"]);
							}
						}
					}
				}
			}//for(i=1..3)
			ko_save_admin("leute_filter", $id, serialize($save_filter));
			$log_message .= 'Leute filter: '.json_encode($save_filter).', ';

			//Spaltenvorlagen
			$save_preset = ko_get_leute_admin_spalten($id, "login");
			if(!$save_preset) $save_preset = array();
			$presets = array_merge((array)ko_get_userpref('-1', '', 'leute_itemset', 'ORDER BY `key` ASC'), (array)ko_get_userpref($_SESSION['ses_userid'], '', 'leute_itemset', 'ORDER BY `key` ASC'));
			//view
			$preset = format_userinput($_POST["sel_leute_cols_view"], "js");
			if($preset == -1) {
			} else if($preset == "") {
				unset($save_preset["view"]);
				unset($save_preset["view_name"]);
			} else {
				$save_preset["view_name"] = $preset;
				foreach($presets as $p) {
					if($p["key"] == $preset) {
						$save_preset["view"] = explode(",", $p["value"]);
					}
				}//foreach(presets as p)
			}//if..elseif..else()
			//edit
			$preset = format_userinput($_POST["sel_leute_cols_edit"], "js");
			if($preset == -1) {
			} else if($preset == "") {
				unset($save_preset["edit"]);
				unset($save_preset["edit_name"]);
			} else {
				$save_preset["edit_name"] = $preset;
				foreach($presets as $p) {
					if($p["key"] == $preset) {
						$save_preset["edit"] = explode(",", $p["value"]);
					}
				}//foreach(presets as p)
			}//if..elseif..else()
			if(sizeof($save_preset) == 0) {
				$save_preset = 0;
			} else {
				//Add edit preset to view as edit also means view
				if($save_preset["view"]) $save_preset["view"] = array_unique(array_merge((array)$save_preset["view"], (array)$save_preset["edit"]));
			}
			ko_save_admin("leute_spalten", $id, serialize($save_preset));
			$log_message .= 'Leute presets: '.json_encode($save_preset).', ';

			//Admin groups
			$lag = format_userinput($_POST["sel_leute_admin_group"], "alphanum", FALSE, 0, array(), ":");
			ko_save_admin("leute_groups", $id, $lag);
			$log_message .= 'Leute assignGroup: '.$lag.', ';

			//Group subscriptions
			$gs = format_userinput($_POST["chk_leute_admin_gs"], "uint");
			ko_save_admin("leute_gs", $id, $gs);
			$log_message .= 'Leute groupSubscription: '.$gs.', ';

			//Assign people to own group
			//Only store if $gs is set
			if($lag) {
				$assign = format_userinput($_POST["chk_leute_admin_assign"], "uint");
				ko_save_admin("leute_assign", $id, $assign);
				$log_message .= 'Leute assignToGroup: '.$assign.', ';
			} else {
				ko_save_admin('leute_assign', $id, 0);
			}
		}//if(in_array(leute, $save_modules))
		else if(in_array('leute', explode(',', $old_login['modules']))) {
			//If leute module is removed from login, then also set all leute_admin fields to 0
			ko_save_admin('leute_assign', $id, 0);
			ko_save_admin('leute_gs', $id, 0);
			ko_save_admin('leute_groups', $id, '');
		}


		if(in_array('groups', $save_modules)) {
			$done_modules[] = 'groups';
			$groups_save_string = format_userinput($_POST['sel_rechte_groups'], 'uint', FALSE, 1);
			ko_save_admin('groups', $id, $groups_save_string);
			$log_message .= getLL('module_groups').': "'.str_replace(',', ', ', $leute_save_string).'", ';

			//Loop über die drei Rechte-Stufen
			$mode = array('', 'view', 'new', 'edit', 'del');
			for($i=4; $i>0; $i--) {
				if(isset($_POST["sel_groups_rights_".$mode[$i]])) {
					//Nur Änderungen bearbeiten
					$old = explode(",", format_userinput($_POST["old_sel_groups_rights_".$mode[$i]], "intlist", FALSE, 0, array(), ":"));
					$new = explode(",", format_userinput($_POST["sel_groups_rights_".$mode[$i]], "intlist", FALSE, 0, array(), ":"));
					$deleted = array_diff($old, $new);
					$added = array_diff($new, $old);
				
					//Login aus gelöschten Gruppen entfernen
					foreach($deleted as $gid) {
						$gid = substr($gid, -6);  //Nur letzte ID verwenden, davor steht die Motherline
						//bisherige Rechte auslesen
						$group = db_select_data("ko_groups", "WHERE `id` = '$gid'", "id,rights_".$mode[$i]);
						$rights_array = explode(",", $group[$gid]["rights_".$mode[$i]]);
						//Zu löschendes Login finden und entfernen
						foreach($rights_array as $index => $right) if($right == $id) unset($rights_array[$index]);
						foreach($rights_array as $a => $b) if(!$b) unset($rights_array[$a]);  //Leere Einträge löschen
						//Neuer Eintrag in Gruppe speichern
						db_update_data("ko_groups", "WHERE `id` = '$gid'", array("rights_".$mode[$i] => implode(",", $rights_array)));
						$all_groups[$gid]['rights_'.$mode[$i]] = implode(',', $rights_array);
					}

					//Login in neu hinzugefügten Gruppen hinzufügen
					foreach($added as $gid) {
						$gid = substr($gid, -6);  //Nur letzte ID verwenden, davor steht die Motherline
						//Bestehende Rechte auslesen
						$group = db_select_data("ko_groups", "WHERE `id` = '$gid'", "id,rights_".$mode[$i]);
						$rights_array = explode(",", $group[$gid]["rights_".$mode[$i]]);
						//Überprüfen, ob Login schon vorhanden ist (sollte nicht)
						$add = TRUE;
						foreach($rights_array as $right) if($right == $id) $add = FALSE;
						if($add) $rights_array[] = $id;
						foreach($rights_array as $a => $b) if(!$b) unset($rights_array[$a]);  //Leere Einträge löschen
						//Neue Liste der Logins in Gruppe speichern
						db_update_data("ko_groups", "WHERE `id` = '$gid'", array("rights_".$mode[$i] => implode(",", $rights_array)));
						$all_groups[$gid]['rights_'.$mode[$i]] = implode(',', $rights_array);
					}
				}//if(isset(_POST[sel_groups_rights_*]))
			}//for(i=1..3)
		}//if(in_array(groups, save_modules))
		else if(in_array('groups', explode(',', $old_login['modules']))) {
			//If groups module has been deselected then remove all access settings from ko_groups
			foreach(array('view', 'new', 'edit', 'del') as $amode) {
				$granted_groups = db_select_data('ko_groups', "WHERE `rights_".$amode."` REGEXP '(^|,)$id(,|$)'");
				foreach($granted_groups as $gg) {
					$granted_logins = explode(',', $gg['rights_'.$amode]);
					foreach($granted_logins as $k => $v) {
						if($v == $id) unset($granted_logins[$k]);
					}
					db_update_data('ko_groups', "WHERE `id` = '".$gg['id']."'", array('rights_'.$amode => implode(',', $granted_logins)));
					$all_groups[$gg['id']]['rights_'.$amode] = implode(',', $granted_logins);
				}
			}
		}

		foreach($MODULES_GROUP_ACCESS as $module) {
			$done_modules[] = $module;
			if(!in_array($module, $MODULES)) continue;
			if(in_array($module, $save_modules)) {
				$save_string = format_userinput($_POST["sel_rechte_".$module."_0"], "uint", FALSE, 1) . ",";
				unset($gruppen);
				switch($module) {
					case "daten":
						if(ko_get_setting('daten_access_calendar') == 1) {
							//First get calendars
							$cals = db_select_data('ko_event_calendar', 'WHERE 1=1', '*', 'ORDER BY name ASC');
							foreach($cals as $cid => $cal) $gruppen['cal'.$cid] = $cal;
							//Then add event groups withouth calendar
							$egs = db_select_data('ko_eventgruppen', "WHERE `calendar_id` = '0'", '*', 'ORDER BY name ASC');
							foreach($egs as $eid => $eg) $gruppen[$eid] = $eg;
						} else {
							$egs = db_select_data('ko_eventgruppen', 'WHERE 1=1', '*', 'ORDER BY name ASC');
							foreach($egs as $eid => $eg) $gruppen[$eid] = $eg;
						}
						ko_save_admin($module . '_force_global', $id, $_POST['sel_force_global_'.$module], "login");
						ko_save_admin($module . '_reminder_rights', $id, $_POST['sel_reminder_rights_'.$module], "login");
					break;
					case "reservation":
						if(ko_get_setting('res_access_mode') == 1) {
							ko_get_resitems($items);
							foreach($items as $iid => $item) $gruppen[$iid] = $item;
						} else {
							ko_get_resgroups($resgroups);
							foreach($resgroups as $gid => $g) $gruppen['grp'.$gid] = $g;
						}
						ko_save_admin($module . '_force_global', $id, $_POST['sel_force_global_'.$module], "login");
					break;
					case 'rota': $gruppen = db_select_data('ko_rota_teams', '', '*', 'ORDER BY name ASC'); break;
					case "tapes": ko_get_tapegroups($gruppen); break;
					case "donations": $gruppen = db_select_data("ko_donations_accounts", "", "*", "ORDER BY number ASC"); break;
					case 'tracking': $gruppen = db_select_data('ko_tracking', '', '*', 'ORDER BY name ASC'); break;

					default:
						$gruppen = hook_access_get_groups($module);
				}
				foreach($gruppen as $g_i => $g) {
					$save_string .= format_userinput($_POST["sel_rechte_".$module."_".$g_i], "uint", FALSE, 1)."@".$g_i.",";
				}
			} else $save_string = "0 ";
			ko_save_admin($module, $id, substr($save_string, 0, -1));
			$log_message .= getLL("module_".$module).': "'.str_replace(",", ", ", $save_string).'", ';
		}

		$done_modules[] = 'tools';
		foreach($MODULES as $module) {
			if(in_array($module, $done_modules)) continue;
			$done_modules[] = $module;

			if(in_array($module, $save_modules)) {
				$save_string = format_userinput($_POST["sel_rechte_".$module], "uint", FALSE, 1);
			} else $save_string = "0";
			ko_save_admin($module, $id, $save_string);
			$log_message .= getLL("module_".$module).': "'.str_replace(",", ", ", $save_string).'", ';

			//KOTA columns
			if($module == 'kg') {
				$coltable = 'ko_kleingruppen';
				$savecols = format_userinput($_POST['kota_columns_'.$coltable], 'alphanumlist');
				ko_save_admin('kota_columns_'.$coltable, $id, $savecols);
			}
		}


		ko_log("edit_login", $log_message);
		$notifier->addInfo(1, $do_action);


		//LDAP-Login
		if(ko_do_ldap()) {
			$ldap = ko_ldap_connect();
			if($old_login['login'] != $login_name) {
				//Delete old login if login name has changed
				if(ko_ldap_check_login($ldap, $old_login['login'])) {
					ko_ldap_del_login($ldap, $old_login['login']);
				}
			}
			ko_ldap_close($ldap);
			//Check the current login for access rights and add an LDAP login if needed
			ko_admin_check_ldap_login($id);
		}//if(ko_do_ldap())

		//Initial Fileshare-Folders
		ko_fileshare_check_inbox_shareroot($id);

		//Go back to list of logins if no modules have changed
		if($old_login['modules'] == implode(',', $save_modules)) $_SESSION['show'] = 'show_logins';
	break;



	case "submit_neues_login":
		if($access['admin']['MAX'] < 5) break;

		$txt_name = format_userinput($_POST["txt_name"], "js");
	
		//Loginname verlangen
		if(!$txt_name) $notifier->addError(1, $do_action);
		//Passwörter müssen übereinstimmen
		if($_POST["txt_pwd1"] == "" || $_POST["txt_pwd1"] != $_POST["txt_pwd2"]) $notifier->addError(2, $do_action);
		//Loginname darf nicht ko_guest sein
		if($txt_name == "ko_guest" || strlen($_POST["txt_name"]) >= 50) $notifier->addError(3, $do_action);
		if($txt_name == "root") $notifier->addError(10, $do_action);
		//Loginname darf nicht schon existieren
		ko_get_logins($logins);
		foreach($logins as $l) {
			if($l["login"] == $txt_name) $notifier->addError(4, $do_action);
		}

		if(!$notifier->hasErrors()) {
			//Berechtigungen von Login kopieren:
			$copy_rights_id = format_userinput($_POST["sel_copy_rights"], "uint");
			if($copy_rights_id) {
				ko_get_login($copy_rights_id, $data);
				unset($data["id"]);
				unset($data["leute_id"]);
			}
			else {  //Nicht kopieren sondern Module gemäss Auswahl übernehmen
				$save_modules = explode(",", format_userinput($_POST["sel_modules"], "alphanumlist"));
				foreach($save_modules as $m_i => $m) {
					if(!in_array($m, $MODULES)) unset($save_modules[$m_i]);
					if($m == 'tools') unset($save_modules[$m_i]);
				}
				$data["modules"] = implode(",", $save_modules);
				//admingroups
				$save_admingroups = explode(",", format_userinput($_POST["sel_admingroups"], "intlist"));
				$admingroups = ko_get_admingroups();
				foreach($save_admingroups as $m_i => $m) {
					if(!in_array($m, array_keys($admingroups))) unset($save_admingroups[$m_i]);
				}
				$data["admingroups"] = implode(",", $save_admingroups);
			}//if..else(copy_rights_id)

			//Assigned person from DB
			$data['leute_id'] = format_userinput($_POST['sel_leute_id'], 'uint');
			//Admin email
			$data['email'] = format_userinput($_POST['txt_email'], 'email');
			//Admin mobile
			$data['mobile'] = format_userinput($_POST['txt_mobile'], 'alphanum++');

			//Login-Daten speichern
			$data["login"]      = $txt_name;
			$data["password"]   = md5($_POST["txt_pwd1"]);
			$data["last_login"] = strftime("%Y-%m-%d %H:%M:%S", time());
			$id = db_insert_data("ko_admin", $data);

			$notifier->addInfo(2, $do_action);

			//Userprefs von bestehendem Login kopieren
			$copy_userprefs_id = format_userinput($_POST["sel_copy_userprefs"], "uint");
			if($copy_userprefs_id) {
				$userprefs = db_select_data("ko_userprefs", "WHERE `user_id` = '$copy_userprefs_id'", array("key", "value", "type"));
				foreach($userprefs as $pref) {
					ko_save_userpref($id, $pref["key"], $pref["value"], $pref["type"]);
				}
			}
			else {  //Default-Werte für Userprefs einfügen
				//Submenus als Userprefs speichern
				foreach($MODULES as $m) {
					$sm = implode(",", ko_get_submenus($m."_left"));
					ko_save_userpref($id, "submenu_".$m."_left", $sm, "");
					$sm = implode(",", ko_get_submenus($m."_right"));
					ko_save_userpref($id, "submenu_".$m."_right", $sm, "");
				}
				//Zusätzliche Userpref-Defaults setzen
				foreach($DEFAULT_USERPREFS as $d) {
					ko_save_userpref($id, $d["key"], $d["value"], $d["type"]);
				}
			}//if..else(copy_userprefs_is)

			//Copy group rights from ko_groups if rights should be copied
			if($copy_rights_id) {
				foreach(array('rights_view', 'rights_new', 'rights_edit', 'rights_del') as $right) {
					$groups = db_select_data("ko_groups", "WHERE `$right` REGEXP '(^|,)$copy_rights_id(,|$)'");
					foreach($groups as $group) {
						db_update_data("ko_groups", "WHERE `id` = '".$group["id"]."'", array($right => ($group[$right].",".$id)));
					}
				}
			}

			//Log
			ko_log("new_login", "$id: $txt_name, Module: \"".implode(", ", $save_modules)."\"");

			//Initial Fileshare-Folders
			ko_fileshare_check_inbox_shareroot($id);

			//LDAP-Login
			if(ko_do_ldap()) {
				ko_admin_check_ldap_login($id);
			}
		}//if(!error)

		//Neues Login gleich zum Bearbeiten geben, damit Berechtigungen gesetzt werden können.
		$_SESSION["show"] = $notifier->hasErrors() ? "new_login" : "edit_login";
	break;


	case "set_new_login":
		if($access['admin']['MAX'] < 5) break;
		$_SESSION["show"] = "new_login";
		$onload_code = "form_set_first_input();".$onload_code;
	break;



	//Bearbeiten
	case "edit_login":
		if($access['admin']['MAX'] < 5) break;
		if(FALSE === ($id = format_userinput($_POST["id"], "uint", TRUE))) {
	    	trigger_error("Not allowed id: ".$id, E_USER_ERROR);
    	}
		//root darf nur von root bearbeitet werden
		if($id == ko_get_root_id() && $_SESSION["ses_username"] != "root") break;

		$_SESSION["show"] = "edit_login";
		//Don't add form_set_first_input() to onload_code, so the username doens't trigger the autocomplete feature for the first password field
	break;


	//Löschen
	case "delete_login":
		if($access['admin']['MAX'] < 5) break;

		if(FALSE === ($id = format_userinput($_POST["id"], "uint", TRUE))) {
	    	trigger_error("Not allowed id: ".$id, E_USER_ERROR);
    	}
		if((int)$id == (int)ko_get_guest_id()) break;  //ko_guest may not be deleted
		if((int)$id == (int)ko_get_root_id()) break;   //root may not be deleted

		//Get username before deleting it (for logging)
		$old_login = db_select_data('ko_admin', "WHERE `id` = '$id'", '*', '', '', TRUE);

		//Log message
		ko_log('delete_login', $id.': '.$old_login['login']);
	
		//Delete login
		db_delete_data("ko_admin", "WHERE `id` = '$id'");

		//Delete all userprefs for this user
		db_delete_data("ko_userprefs", "WHERE `user_id` = '$id'");

		//LDAP
		if(ko_do_ldap()) {
			$ldap = ko_ldap_connect();
			ko_ldap_del_login($ldap, $old_login['login']);
			ko_ldap_close($ldap);
		}

		$notifier->addInfo(3, $do_action);
	break;



	//Etiketten-Vorlage
	case "open_etiketten":
		if($access['admin']['MAX'] < 2) break;

		//GET- oder POST-Übergabe testen
		if($_POST["sel_vorlage_open"]) {
			$etiketten_id = format_userinput($_POST["sel_vorlage_open"], "js");
		} else if($_GET["sel_vorlage_open"]) {
			$etiketten_id = format_userinput($_GET["sel_vorlage_open"], "js");
		} else {
			$notifier->addError(9, $do_action);
			break;
		}

		//Auf gültige Vorlagen-ID prüfen
		ko_get_etiketten_vorlage($etiketten_id, $vorlage);
		if(is_array($vorlage) && $vorlage["name"] != "") {
			$_SESSION["show"] = "set_etiketten_open";
		} else {
			$etiketten_id = "";
			$notifier->addError(9, $do_action);
		}
	break;



	case "submit_del_etiketten_vorlage":
		if($access['admin']['MAX'] < 2) break;
		$sel_vorlage = format_userinput($_POST["sel_vorlage_open"], "alphanum");
		if(!$sel_vorlage || strlen($sel_vorlage) != 32) {
			$notifier->addError(8, $do_action);
			break;
		}

		ko_get_etiketten_vorlage($sel_vorlage, $vorlage);
		if(is_array($vorlage) && $vorlage["name"] != "") {
			db_delete_data("ko_etiketten", "WHERE `vorlage` = '$sel_vorlage'");
			//Delete image if any stored for this preset
			if($vorlage['pic_file'] && file_exists($BASE_PATH.$vorlage['pic_file'])) unlink($BASE_PATH.$vorlage['pic_file']);
			$notifier->addInfo(6, $do_action);
		} else {
			$notifier->addError(8, $do_action);
		}

		$_SESSION["show"] = "set_etiketten_open";
		$etiketten_id = "";
	break;


	case "save_etiketten":
		if($access['admin']['MAX'] < 2) break;

		if($_POST["txt_vorlage_neu"]) {  //Neue Vorlage speichern
			$keys["name"] = format_userinput($_POST["txt_vorlage_neu"], "text");
			$id = md5(time().format_userinput($_POST["txt_vorlage_neu"], "text"));
			$mode = "new";
			$notifier->addInfo(4, $do_action);
		} else if($_POST["sel_vorlage_save"]) {  //Bestehende neu speichern
			$id = format_userinput($_POST["sel_vorlage_save"], "alphanum");
			$mode = "edit";
			$notifier->addInfo(5, $do_action);
		} else break;

		//Verlangte Angaben überprüfen (per_row, per_col)
		if(!$_POST["txt_per_row"] || !$_POST["txt_per_col"]) {
			$notifier->addError(7, $do_action);
			break;
		}

		$keys['page_format']      = format_userinput($_POST['sel_pageformat'], 'alphanum');
		$keys['page_orientation'] = format_userinput($_POST['sel_pageorientation'], 'alpha', FALSE, 1);
		$keys['per_row']          = format_userinput($_POST['txt_per_row'], 'uint');
		$keys['per_col']          = format_userinput($_POST['txt_per_col'], 'uint');
		$keys['border_top']       = format_userinput($_POST['txt_border_top'], 'float');
		$keys['border_right']     = format_userinput($_POST['txt_border_right'], 'float');
		$keys['border_bottom']    = format_userinput($_POST['txt_border_bottom'], 'float');
		$keys['border_left']      = format_userinput($_POST['txt_border_left'], 'float');
		$keys['spacing_horiz']    = format_userinput($_POST['txt_spacing_horiz'], 'float');
		$keys['spacing_vert']     = format_userinput($_POST['txt_spacing_vert'], 'float');
		$keys['align_horiz']      = format_userinput($_POST['sel_align_horiz'], 'alpha', FALSE, 1);
		$keys['align_vert']       = format_userinput($_POST['sel_align_vert'], 'alpha', FALSE, 1);
		$keys['font']             = format_userinput($_POST['sel_font'], 'text');
		$keys['textsize']         = format_userinput($_POST['sel_textsize'], 'uint');
		$keys['ra_font']          = format_userinput($_POST['sel_ra_font'], 'text');
		$keys['ra_textsize']      = format_userinput($_POST['sel_ra_textsize'], 'uint');
		$keys['ra_margin_top']    = format_userinput($_POST['txt_ra_margin_top'], 'float');
		$keys['ra_margin_left']   = format_userinput($_POST['txt_ra_margin_left'], 'float');

		//Save image
		if($_FILES['pic_file']['tmp_name']) {
			$dissallow_ext = array('php', 'php3', 'inc');
			$tmp = $_FILES['pic_file']["tmp_name"];
			if(!$tmp) return FALSE;
			$upload_name = $_FILES['pic_file']["name"];
			$ext_ = explode('.', $upload_name);
			$ext = strtolower($ext_[sizeof($ext_)-1]);
			if(in_array($ext, $dissallow_ext)) return FALSE;

			$path = $BASE_PATH.'my_images/';
			$filename = 'label_'.$id.'.'.$ext;
			$dest = $path.$filename;

			$ret = move_uploaded_file($tmp, $dest);
			if($ret) {
				$value = 'my_images/'.$filename;
				chmod($dest, 0644);
			} else {
				$value = '';
			}
			$keys['pic_file']       = $value;
		}
		$keys['pic_x']          = format_userinput($_POST['txt_pic_x'], 'float');
		$keys['pic_y']          = format_userinput($_POST['txt_pic_y'], 'float');
		$keys['pic_w']          = format_userinput($_POST['txt_pic_w'], 'float');

		ko_save_etiketten_vorlage($id, $keys, $mode);

		//Log-Meldung
		$log_type = ($mode == "new") ? "new_etiketten" : "edit_etiketten";
    $name = $keys["name"]; unset($keys["name"]);
    $log_message = $name.": ".implode(", ", $keys);
	  ko_log($log_type, $log_message);

		$_SESSION["show"] = "set_etiketten_open";
		$etiketten_id = $id;
	break;


	
	//Identität annehmen (mit anderem Login einloggen)
	case "sudo_login":
		if($access['admin']['MAX'] < 5) break;

		$found = 0;
		foreach($_POST["chk"] as $c_i => $c) {
    		if($c) {
				$found++;
				$sudo_id = format_userinput($c_i, "uint");
			}
		}//foreach(chk as c)

		//Nur genau eine Selektion erlauben
		if($found != 1) {
			$notifier->addError(5, $do_action);
			break;
		}
		
		//ko_guest nicht erlauben
		if($sudo_id == ko_get_guest_id()) {
			$notifier->addError(6, $do_action);
			break;
		}

		//root nicht erlauben
		if($sudo_id == ko_get_root_id()) {
			$notifier->addError(11, $do_action);
			break;
		}

		//Auf gültiges Login testen
		$found = FALSE;
		ko_get_logins($logins, " AND (`disabled` = '' OR `disabled` = '0')");
		foreach($logins as $l) {
			if((int)$l["id"] == (int)$sudo_id) {
				$found = TRUE;
			}
		}
		if(!$found) {
			$notifier->addError(11, $do_action);
			break;
		}

		//Identität wechseln
		$_SESSION["ses_userid"] = $sudo_id;
		ko_get_login($sudo_id, $sudo_l);
		$_SESSION["ses_username"] = $sudo_l["login"];
		ko_init();
	break;





	//PDF layouts for address export
	case "set_leute_pdf_new":
		if($access['admin']['MAX'] < 2) break;

		$_SESSION["show"] = "new_leute_pdf";
	break;


	case "edit_leute_pdf":
		if($access['admin']['MAX'] < 2) break;

		$layout_id = format_userinput($_POST["id"], "uint");
		if(!$layout_id) break;

		$_SESSION["show"] = "edit_leute_pdf";
	break;


	case "submit_new_leute_pdf":
	case "submit_edit_leute_pdf":
		if($access['admin']['MAX'] < 2) break;

		if($do_action == "submit_edit_leute_pdf") {
			$layout_id = format_userinput($_POST["layout_id"], "uint");
			if(!$layout_id) $notifier->addError(14, $do_action);
		}

		if(!$notifier->hasErrors()) {
			$new = array();
			$post = $_POST["pdf"];

			$name = format_userinput($post["name"], "js");
			$new["page"]["orientation"] = format_userinput($post["page"]["orientation"], "alpha", FALSE, 1);
			foreach(array("left", "top", "right", "bottom") as $pos) $new["page"]["margin_".$pos] = $post["page"]["margin_".$pos];
			foreach(array("header", "footer") as $a) {
				foreach(array("left", "center", "right") as $b) {
					foreach(array("font", "fontsize", "text") as $c) {
						$new[$a][$b][$c] = $post[$a][$b][$c];
					}
				}
			}
			foreach(array("font", "fontsize", "fillcolor") as $a) {
				$new["headerrow"][$a] = $post["headerrow"][$a];
			}
			if($post['columns']) {
				if(substr($post['columns'], 0, 3) == '@G@') $value = ko_get_userpref('-1', substr($post['columns'], 3), 'leute_itemset');
				else $value = ko_get_userpref($_SESSION['ses_userid'], $post['columns'], 'leute_itemset');
				$new['columns'] = explode(',', $value[0]['value']);
			}
			$new["columns_children"] = $post["columns_children"] ? TRUE : FALSE;
			$new["sort"] = $post["sort"];
			$new["sort_order"] = $post["sort_order"];
			if($post["filter"]) {
				if(substr($post['filter'], 0, 3) == '@G@') {
					$value = ko_get_userpref('-1', '', 'filterset');
					$post['filter'] = substr($post['filter'], 3);
				} else $value = ko_get_userpref($_SESSION['ses_userid'], '', 'filterset');
				foreach($value as $v_i => $v) {
					if($v["key"] == $post["filter"]) $new["filter"] = unserialize($value[$v_i]["value"]);
				}
			}
			$new["col_template"]["_default"]["font"] = $post["col_template"]["_default"]["font"];
			$new["col_template"]["_default"]["fontsize"] = $post["col_template"]["_default"]["fontsize"];

			if($do_action == "submit_edit_leute_pdf") {
				db_update_data('ko_pdf_layout', "WHERE `id` = '$layout_id'", array('name' => $name, 'data' => serialize($new)));
			} else {
				db_insert_data('ko_pdf_layout', array('type' => 'leute', 'name' => $name, 'data' => serialize($new)));
			}
		}
		$_SESSION["show"] = "set_leute_pdf";
	break;


	case "delete_leute_pdf":
		if($access['admin']['MAX'] < 2) break;

		$id = format_userinput($_POST["id"], "uint");
		if(!$id) break;

		$old = db_select_data('ko_pdf_layout', "WHERE `id` = '$id' AND `type` = 'leute'", '*', '', '', TRUE);

		if($old['id'] > 0 && $old['id'] == $id) {
			db_delete_data("ko_pdf_layout", "WHERE `id` = '$id'");
			ko_log("delete_leute_pdf_layout", "id: $id, name: ".$old["name"]);
		}
	break;



	//News
	case 'list_news':
		if($access['admin']['MAX'] < 2) break;

		$_SESSION['show'] = 'list_news';
	break;


	case 'new_news':
		if($access['admin']['MAX'] < 2) break;

		$_SESSION['show'] = 'new_news';
		$onload_code = 'form_set_first_input();'.$onload_code;
	break;


	case 'submit_new_news':
	case 'submit_as_new_news':
		if($access['admin']['MAX'] < 2) break;

		if($do_action == 'submit_as_new_news') {
			list($table, $columns, $ids, $hash) = explode('@', $_POST['id']);
			//Fake POST[id] for kota_submit_multiedit() to remove the id from the id. Otherwise this entry will be edited
			$new_hash = md5(md5($mysql_pass.$table.implode(':', explode(',', $columns)).'0'));
			$_POST['id'] = $table.'@'.$columns.'@0@'.$new_hash;
		}

		kota_submit_multiedit('', 'new_news');
		if(!$notifier->hasErrors()) $_SESSION['show'] = 'list_news';
	break;


	case 'edit_news':
		if($access['admin']['MAX'] < 2) break;

		$id = format_userinput($_POST['id'], 'uint');
		$_SESSION['show'] = 'edit_news';
		$onload_code = 'form_set_first_input();'.$onload_code;
	break;


	case 'submit_edit_news':
		if($access['admin']['MAX'] < 2) break;

		kota_submit_multiedit('', 'edit_news');
		if(!$notifier->hasErrors()) $_SESSION['show'] = 'list_news';
	break;


	case 'delete_news':
		if($access['admin']['MAX'] < 2) break;

		$id = format_userinput($_POST['id'], 'uint');
		if(!$id) break;

		$old = db_select_data('ko_news', "WHERE `id` = '$id'", '*', '', '', TRUE);
		db_delete_data('ko_news', "WHERE `id` = '$id'");
		ko_log_diff('del_news', $old);
	break;




	case 'sms_log_mark':
		if($_SESSION['ses_userid'] != ko_get_root_id()) break;

		db_insert_data('ko_log', array('type' => 'sms_mark', 'user_id' => $_SESSION['ses_userid'], 'date' => date('Y-m-d H:m:i')));
	break;



	//Submenus
  case "move_sm_left":
  case "move_sm_right":
    ko_submenu_actions("admin", $do_action);
  break;


	//Default:
  default:
	if(!hook_action_handler($do_action))
    include($ko_path."inc/abuse.inc.php");
  break;


}//switch(do_action)

//HOOK: Plugins erlauben, die bestehenden Actions zu erweitern
hook_action_handler_add($do_action);



//***Rechte neu auslesen:
if(in_array($do_action, array('submit_edit_login', 'submit_edit_admingroup', 'sudo_login'))) {
	ko_get_access('admin', '', TRUE);
}



//Filter (rechts)

//Set some default values
if(!$_SESSION['sort_logins']) $_SESSION['sort_logins'] = 'login';
if(!$_SESSION['sort_logins_order']) $_SESSION['sort_logins_order'] = 'ASC';
if(!$_SESSION["sort_logs"]) $_SESSION["sort_logs"] = "date";
if(!$_SESSION["sort_logs_order"]) $_SESSION["sort_logs_order"] = "DESC";
if(!$_SESSION["show_start"]) $_SESSION["show_start"] = 1;
$_SESSION["show_limit"] = ko_get_userpref($_SESSION["ses_userid"], "show_limit_logins");
if(!$_SESSION["show_limit"]) $_SESSION["show_limit"] = ko_get_setting("show_limit_logins");
if(!$_SESSION["show_logs_start"]) $_SESSION["show_logs_start"] = 1;
$_SESSION["show_logs_limit"] = ko_get_userpref($_SESSION["ses_userid"], "show_limit_logs");
if(!$_SESSION["show_logs_limit"]) $_SESSION["show_logs_limit"] = ko_get_setting("show_limit_logs");
if(!$_SESSION["log_type"]) $_SESSION["log_type"] = "";
if(!$_SESSION["log_user"]) $_SESSION["log_user"] = "";
if(!isset($_SESSION["log_time"])) $_SESSION["log_time"] = "2";
if(!$_SESSION['sort_news']) $_SESSION['sort_news'] = 'cdate';
if(!$_SESSION['sort_news_order']) $_SESSION['sort_news_order'] = 'DESC';

//Include submenus
ko_set_submenues();

//Smarty-Templates-Engine laden
require("$ko_path/inc/smarty.inc.php");
?>
<!DOCTYPE html 
  PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php print $_SESSION["lang"]; ?>" lang="<?php print $_SESSION["lang"]; ?>">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title><?php print "$HTML_TITLE: ".getLL("module_".$ko_menu_akt); ?></title>
<?php
print ko_include_css();

$js_files = array($ko_path.'inc/jquery/jquery.js', $ko_path.'inc/kOOL.js');
if(in_array($_SESSION['show'], array('edit_login', 'edit_admingroup'))) $js_files[] = $ko_path.'inc/selectmenu.js';
print ko_include_js($js_files);

include($ko_path.'inc/js-sessiontimeout.inc.php');
$js_calendar->load_files();

//Prepare group double selects when editing a login
if(in_array($_SESSION['show'], array('edit_login', 'edit_admingroup'))) {
	//Show dummy groups because the rights will be propagated downwards to all children
	$show_all_types = TRUE;
	//Show all groups, also terminated ones
	$show_passed_groups = ko_get_userpref($_SESSION['ses_userid'], 'show_passed_groups');
	ko_save_userpref($_SESSION['ses_userid'], 'show_passed_groups', 1);
	//View
	$list_id = 1;
	include($ko_path.'leute/inc/js-groupmenu.inc.php');
	$loadcode .= "initList($list_id, document.formular.sel_ds1_sel_groups_rights_view);";
	//New
	$list_id = 2;
	include($ko_path.'leute/inc/js-groupmenu.inc.php');
	$loadcode .= "initList($list_id, document.formular.sel_ds1_sel_groups_rights_new);";
	//Edit
	$list_id = 3;
	include($ko_path.'leute/inc/js-groupmenu.inc.php');
	$loadcode .= "initList($list_id, document.formular.sel_ds1_sel_groups_rights_edit);";
	//Del
	$list_id = 4;
	include($ko_path.'leute/inc/js-groupmenu.inc.php');
	$loadcode .= "initList($list_id, document.formular.sel_ds1_sel_groups_rights_del);";
	$onload_code = $loadcode.$onload_code;
	//Reset userpref to original value
	ko_save_userpref($_SESSION['ses_userid'], 'show_passed_groups', $show_passed_groups);
}
?>
</head>

<body onload="session_time_init();<?php print $onload_code; ?>">

<?php
/*
 * Gibt bei erfolgreichem Login das Menü aus, sonst einfach die Loginfelder
 */
include($ko_path . "menu.php");
?>


<table width="100%">
<tr> 

<!-- Submenu -->
<td class="main_left" name="main_left" id="main_left">
<?php
print ko_get_submenu_code("admin", "left");
?>
&nbsp;
</td>


<!-- Hauptbereich -->
<td class="main">
<form action="index.php" method="post" name="formular" enctype="multipart/form-data" autocomplete="off">  <!-- Hauptformular -->
<input type="hidden" name="action" id="action" value="" />
<input type="hidden" name="id" id="id" value="" />
<div name="main_content" id="main_content">

<?php
if($notifier->hasNotifications(koNotifier::ALL)) {
	$notifier->notify();
}

hook_show_case_pre($_SESSION["show"]);

switch($_SESSION["show"]) {
	case "set_allgemein";
		ko_show_set_allgemein();
	break;
	case "set_etiketten";
		ko_show_set_etiketten();
	break;
	case "set_etiketten_open":
		ko_show_set_etiketten($etiketten_id);
	break;
	case "set_leute_pdf";
		ko_list_leute_pdf();
	break;
	case "set_layout";
		ko_show_set_layout($_SESSION["ses_userid"]);
	break;
	case "set_layout_guest";
		ko_show_set_layout(ko_get_guest_id());
	break;
	case "show_logins";
		ko_set_logins_list();
	break;
	case "edit_login":
		ko_login_formular("edit", $id);
	break;
	case "new_login":
		ko_login_formular("neu");
	break;
	case "login_details":
		ko_login_details($login_id);
	break;
	case "show_logs":
		ko_show_logs();
	break;
	case 'show_sms_log':
		ko_show_sms_log();
	break;
	case "show_admingroups":
		ko_list_admingroups();
	break;
	case "new_admingroup":
		ko_login_formular("neu", 0, "admingroup");
	break;
	case "edit_admingroup":
		ko_login_formular("edit", $edit_id, "admingroup");
	break;
	case "new_leute_pdf":
		ko_formular_leute_pdf("new");
	break;
	case "edit_leute_pdf":
		ko_formular_leute_pdf("edit", $layout_id);
	break;
	case "change_password":
		ko_change_password();
	break;
	case 'list_news':
		ko_list_news();
	break;
	case 'new_news':
		ko_formular_news('new');
	break;
	case 'edit_news':
		ko_formular_news('edit', $id);
	break;

	default:
		//HOOK: Plugins erlauben, neue Show-Cases zu definieren
    hook_show_case($_SESSION["show"]);
  break;
}//switch(show)

//HOOK: Plugins erlauben, die bestehenden Show-Cases zu erweitern
hook_show_case_add($_SESSION["show"]);

?>
&nbsp;
</div>
</form>
</td>

<td class="main_right" name="main_right" id="main_right">

<?php
print ko_get_submenu_code("admin", "right");
?>
&nbsp;
</td>
</tr>

<?php include($ko_path . "footer.php"); ?>

</table>

</body>
</html>
