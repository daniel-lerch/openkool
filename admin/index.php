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

ob_start();  //Ausgabe-Pufferung starten

$ko_path = "../";
$ko_menu_akt = "admin";

include($ko_path . "inc/ko.inc");
include("inc/admin.inc");

$notifier = koNotifier::Instance();

//Redirect to SSL if needed
ko_check_ssl();

//***Action auslesen:
if($_POST["action"]) $do_action = $_POST["action"];
else if($_GET["action"]) $do_action = $_GET["action"];
else $do_action = "";

if(!ko_module_installed("admin") && !in_array($do_action, array('change_password', 'submit_change_password'))) {
	header("Location: ".$BASE_URL."index.php"); exit;
}

ob_end_flush();  //Puffer flushen

//Get access rights
ko_get_access('admin');
if (ko_module_installed('vesr')) {
	ko_get_access('vesr');
}

//kOOL Table Array
ko_include_kota(array('ko_news', '_ko_sms_log', '_ko_telegram_log', 'ko_log', 'ko_admingroups', 'ko_admin', 'ko_labels', 'ko_pdf_layout', 'ko_vesr', 'ko_vesr_camt', 'ko_google_cloud_printers', 'ko_detailed_person_exports'));

//*** Plugins einlesen:
$hooks = hook_include_main("admin");
if(sizeof($hooks) > 0) foreach($hooks as $hook) include_once($hook);

//Reset show_start if from another module
if($_SERVER['HTTP_REFERER'] != '' && FALSE === strpos($_SERVER['HTTP_REFERER'], '/'.$ko_menu_akt.'/')) $_SESSION['show_start'] = 1;

switch($do_action) {


	case 'telegram_create_webhook':
		if($_SESSION['ses_userid'] != ko_get_root_id()) break;

		$url = 'https://api.telegram.org/bot'.ko_get_setting('telegram_token').'/setWebhook';
		$webhook = $BASE_URL.'webhook/telegram.php?h='.md5(KOOL_ENCRYPTION_KEY);
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, array('url' => $webhook));
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);

		$response = curl_exec($ch);
		$result = json_decode($response, TRUE);
		if($result['ok']) {
			$notifier->addInfo(12, $do_action);
		} else {
			$notifier->addError(25, $do_action);
			$notifier->addTextError(' '.$response, $do_action);
		}
		//var_dump($response);
		//var_dump(curl_error($ch));
	break;

	//Anzeigen
	case 'set_layout':  //Backwards compatibility (might still be set as default view in userprefs)
	case "admin_settings":
		if($access['admin']['MAX'] < 1) break;
		$_SESSION["show"] = "admin_settings";
	break;

	case "set_leute_pdf":
		if($access['admin']['MAX'] < 2) break;
		$_SESSION["show"] = "set_leute_pdf";
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

	case 'show_telegram_log':
		if($access['admin']['MAX'] < 1) break;
		if(!ko_module_installed('telegram')) break;
		$_SESSION['show'] = 'show_telegram_log';
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
		if(ko_get_setting("change_password") == 1 && $_SESSION['ses_userid'] != ko_get_guest_id()) {
			$_SESSION["show"] = "change_password";
		}
	break;

	case "submit_change_password":
		if(ko_get_setting("change_password") == 1 && $_SESSION['disable_password_change'] != 1 && $_SESSION['ses_userid'] != ko_get_guest_id()) {
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
	case "submit_admin_settings":
		// general settings
		if($access['admin']['MAX'] >= 2) {
			if(in_array('leute', $MODULES)) {
				$login_edit_person = format_userinput($_POST["rd_login_edit_person"], "uint", FALSE, 1);
				if($login_edit_person == 0 || $login_edit_person == 1)
					ko_set_setting("login_edit_person", $login_edit_person);
			}

			if(in_array('sms', $MODULES)) {
				$sms_country_code = format_userinput($_POST["txt_sms_country_code"], "uint");
				ko_set_setting("sms_country_code", $sms_country_code);
			}

			if(in_array('telegram', $MODULES) && $_SESSION["ses_username"] == "root") {
				ko_set_setting("telegram_botname", format_userinput($_POST["txt_telegram_botname"], "text"));
				ko_set_setting("telegram_botid", format_userinput($_POST["txt_telegram_botid"], "text"));
				ko_set_setting("telegram_token", format_userinput($_POST["txt_telegram_token"], "text"));
			}

			if(in_array('mailing', $MODULES) && is_array($MAILING_PARAMETER) && $MAILING_PARAMETER['domain'] != '') {
				ko_set_setting('mailing_mails_per_cycle', format_userinput($_POST['txt_mailing_mails_per_cycle'], 'uint'));
				ko_set_setting('mailing_max_recipients', format_userinput($_POST['txt_mailing_max_recipients'], 'uint'));
				ko_set_setting('mailing_only_alias', format_userinput($_POST['chk_mailing_only_alias'], 'uint'));
				ko_set_setting('mailing_allow_double', format_userinput($_POST['chk_mailing_allow_double'], 'uint'));
				$max_attempts = (!is_numeric($_POST['txt_mailing_max_attempts']) ? 10 : $_POST['txt_mailing_max_attempts']);
				ko_set_setting('mailing_max_attempts', format_userinput($max_attempts, 'uint'));
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

			ko_set_setting('pp_addresses', format_userinput($_POST['txt_pp_addresses'], 'text'));
		}

		// layout settings
		if($access['admin']['MAX'] >= 1) {

			$uid = $_SESSION["ses_userid"];

			//Default-Seiten pro Modul
			ko_save_userpref($uid, "default_view_admin", format_userinput($_POST["sel_admin"], "js"));
			ko_save_userpref($uid, "show_limit_logins", format_userinput($_POST["show_limit_logins"], "uint"));
			ko_save_userpref($uid, 'default_module', format_userinput($_POST['sel_default_module'], 'js'));

			//Popupmenu-Einstellungen
			ko_save_userpref($uid, "menu_order", format_userinput($_POST["sel_menu_order"], "alphanumlist"));

			//Diverses-Einstellungen
			ko_save_userpref($uid, "save_files_as_share", format_userinput($_POST["sel_save_files_as_share"], "uint", FALSE, 1));
			ko_save_userpref($uid, 'export_table_format', format_userinput($_POST['export_table_format'], 'alphanum'));
			ko_save_userpref($uid, 'show_notes', format_userinput($_POST['show_notes'], 'uint', FALSE, 1));
			ko_save_userpref($uid, 'save_kota_filter', format_userinput($_POST['save_kota_filter'], 'uint', FALSE, 1));
			ko_save_userpref($uid, 'download_not_directly', format_userinput($_POST['download_not_directly'], 'uint', FALSE, 1));
		}

		// layout settings guest
		if($access['admin']['MAX'] >= 3) {

			$uid = ko_get_guest_id();

			// front modules
			ko_save_userpref($uid, "front_modules", format_userinput($_POST["chks_front_modules_guest"], "text"));

			//Default-Seiten pro Modul
			ko_save_userpref($uid, "default_view_admin", format_userinput($_POST["sel_admin_guest"], "js"));
			ko_save_userpref($uid, 'default_module', format_userinput($_POST['sel_default_module_guest'], 'js'));
			ko_save_userpref($uid, 'fm_absence_infotext', format_userinput($_POST['txt_fm_absence_infotext'], 'text'));

			$save_filter = unserialize(ko_get_userpref($uid, "fm_absence_restriction"));
			if(!$save_filter) $save_filter = array();
			$filterset = array_merge((array)ko_get_userpref('-1', '', 'filterset'), (array)ko_get_userpref($_SESSION['ses_userid'], '', 'filterset'));

			$filter = format_userinput($_POST["sel_fm_absence_restriction"], "js");
			if($filter == -1) {
				break;
			} else if($filter == "") {
				unset($save_filter);
			} else {
				//A new filter has been selected
				if(preg_match('/sg[0-9]{4}/', $filter) == 1) {  //small group
					$sg = db_select_data('ko_kleingruppen', "WHERE `id` = '".format_userinput($filter, 'uint')."'", '*', '', '', TRUE);
					$sgFilter = db_select_data('ko_filter', "WHERE `typ` = 'leute' AND `name` = 'smallgroup'", 'id', '', '', TRUE);
					$save_filter['value'] = $filter;
					$save_filter['name'] = $sg['name'];
					$save_filter['filter'] = array('link' => 'and', 0 => array(0 => $sgFilter['id'], 1 => array(1 => $sg['id']), 2 => 0));
				} else if(preg_match('/g[0-9]{6}/', $filter) == 1) {  //group
					$gid = substr($filter, -6);
					$gr = db_select_data('ko_groups', "WHERE `id` = '$gid'", '*', '', '', TRUE);
					$grFilter = db_select_data('ko_filter', "WHERE `typ` = 'leute' AND `name` = 'group'", 'id', '', '', TRUE);
					$save_filter['value'] = $filter;
					$save_filter['name'] = $gr['name'];
					$save_filter['filter'] = array('link' => 'and', 0 => array(0 => $grFilter['id'], 1 => array(1 => $filter, 2 => ''), 2 => 0));
				} else {  //filter preset
					$save_filter["name"] = $filter;
					$save_filter['value'] = $filter;
					//Filter-Infos aus Filterset lesen
					foreach($filterset as $set) {
						if($set["key"] == $filter) {
							$save_filter["filter"] = unserialize($set["value"]);
						}
					}
				}
			}

			// save filter
			ko_save_userpref($uid, 'fm_absence_restriction', serialize($save_filter));

			//Popupmenu-Einstellungen
			ko_save_userpref($uid, "menu_order", format_userinput($_POST["sel_menu_order_guest"], "alphanumlist"));

			//Diverses-Einstellungen
			ko_save_userpref($uid, "save_files_as_share", format_userinput($_POST["sel_save_files_as_share_guest"], "uint", FALSE, 1));
			ko_save_userpref($uid, 'export_table_format', format_userinput($_POST['export_table_format_guest'], 'alphanum'));
			ko_save_userpref($uid, 'show_notes', format_userinput($_POST['show_notes_guest'], 'uint', FALSE, 1));
			ko_save_userpref($uid, 'save_kota_filter', format_userinput($_POST['save_kota_filter_guest'], 'uint', FALSE, 1));
			ko_save_userpref($uid, 'download_not_directly', format_userinput($_POST['download_not_directly_guest'], 'uint', FALSE, 1));

		}

		if($access['admin']['MAX'] >= 5) {
			ko_set_setting("qz_tray_enable", format_userinput($_POST['qz_tray_enable'],'uint'));
			ko_set_setting("qz_tray_host", preg_replace('/[^a-zA-Z0-9\._-]/','',$_POST['qz_tray_host']));
		}

		$_SESSION["show"] = "admin_settings";
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
				if($ret == '031') {
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
				else {
					$error_txt_add = $ret.' '.getLL('error_aspsms_'.intval($ret));
					$notifier->addError(17, $do_action, array($error_txt_add));
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

		ko_admin_remove_groups($id, "admingroup");

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
		ko_log_diff('delete_admingroup', $old);
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

		$txt_name = format_userinput($_POST["txt_name"], "text");
		if(empty($txt_name)) {
			$notifier->addError(12, $do_action);
		}

		$admingroups = ko_get_admingroups();
		foreach($admingroups as $ag) {
			if($ag["name"] == $txt_name) {
				$notifier->addError(13, $do_action);
			}
		}

		if(!$notifier->hasErrors()) {
			$log_message = [];
			$type = "admingroup";

			$login_dummy = ["name" => $txt_name];
			$log_message[] = $login_dummy;
			$id = db_insert_data("ko_admingroups", $login_dummy);
			$log_message[] = ['id' => $id];

			foreach($_POST["module_install_status"] AS $installed_module => $status) {
				$save_modules[] = format_userinput($installed_module, "text");
			}

			$_old_admingroup = ko_get_admingroups();
			$old_login = $_old_admingroup[$id];

			foreach ($save_modules as $key => $module_name) {
				if (!in_array($module_name, $MODULES)) unset($save_modules[$key]);
				if ($module_name == 'tools' && $id != ko_get_root_id()) unset($save_modules[$key]);
			}

			if(ko_admin_save_general($id, $type) === FALSE) break;

			$done_modules = [];
			$done_modules[] = 'tools';

			ko_admin_update_module("leute", $id, $type);
			ko_admin_update_module("groups", $id, $type);

			foreach ($MODULES_GROUP_ACCESS as $module) {
				if(ko_admin_update_groupaccess($module, $type) === FALSE) continue;
			}

			foreach ($MODULES as $module) {
				if (in_array($module, $done_modules)) continue;
				ko_admin_update_rights($module, $type);
			}

			$logTexts = [];
			foreach($log_message as $lMessages) {
				foreach($lMessages as $lKey => $lMessage) {
					$logTexts[] = $lKey.': '.trim($lMessage);
				}
			}
			ko_log('new_admingroup', implode(', ', $logTexts));
			$notifier->addInfo(1, $do_action);
			$edit_id = $id;
		}

		$_SESSION["show"] = ($notifier->hasErrors() || empty($edit_id)) ? "new_admingroup" : "edit_admingroup";
	break;



	case "submit_edit_admingroup":
		if($access['admin']['MAX'] < 5) break;

		$log_message = [];
		$type = "admingroup";

		if(FALSE === ($id = format_userinput($_POST["id"], "uint", TRUE))) {
			trigger_error("Not allowed id: ".$id, E_USER_ERROR);
		}

		$log_message[] = ['id' => $id];

		foreach($_POST["module_install_status"] AS $installed_module => $status) {
			$save_modules[] = format_userinput($installed_module, "text");
		}

		$_old_admingroup = ko_get_admingroups();
		$old_login = $_old_admingroup[$id];

		foreach ($save_modules as $key => $module_name) {
			if (!in_array($module_name, $MODULES)) unset($save_modules[$key]);
			if ($module_name == 'tools' && $id != ko_get_root_id()) unset($save_modules[$key]);
		}

		if(ko_admin_save_general($id, $type) === FALSE) break;

		$done_modules = [];
		$done_modules[] = 'tools';

		ko_admin_update_module("leute", $id, $type);
		ko_admin_update_module("groups", $id, $type);

		foreach ($MODULES_GROUP_ACCESS as $module) {
			if(ko_admin_update_groupaccess($module, $type) === FALSE) continue;
		}

		foreach ($MODULES as $module) {
			if (in_array($module, $done_modules)) continue;
			ko_admin_update_rights($module, $type);
		}

		$logTexts = [];
		foreach($log_message as $lMessages) {
			foreach($lMessages as $lKey => $lMessage) {
				$logTexts[] = $lKey.': '.trim($lMessage);
			}
		}
		ko_log('edit_admingroup', implode(', ', $logTexts));
		$notifier->addInfo(1, $do_action);

		$_SESSION['show'] = 'show_admingroups';
	break;


	case "submit_edit_login":
		if($access['admin']['MAX'] < 5) break;

		$log_message = [];

		if (FALSE === ($id = format_userinput($_POST["id"], "uint", TRUE))) {
			trigger_error("Not allowed id: " . $id, E_USER_ERROR);
		}
		//root darf nur von root bearbeitet werden
		if ($id == ko_get_root_id() && $_SESSION["ses_username"] != "root") break;

		$log_message[] = ['id' => $id];
		ko_get_login($id, $old_login);
		$save_modules = [];

		foreach($_POST["module_install_status"] AS $installed_module => $status) {
			$save_modules[] = format_userinput($installed_module, "text");
		}

		if($id == ko_get_root_id()) $save_modules[] = "tools";
		foreach ($save_modules as $key => $module_name) {
			if (!in_array($module_name, $MODULES)) unset($save_modules[$key]);
			if ($module_name == 'tools' && $id != ko_get_root_id()) unset($save_modules[$key]);
		}

		if(ko_admin_save_general($id) === FALSE) break;

		$done_modules = [];
		$done_modules[] = 'tools';

		ko_admin_update_module("leute", $id);
		ko_admin_update_module("groups", $id);

		foreach ($MODULES_GROUP_ACCESS as $module) {
			if(ko_admin_update_groupaccess($module) === FALSE) continue;
		}

		foreach ($MODULES as $module) {
			if (in_array($module, $done_modules)) continue;
			ko_admin_update_rights($module);
		}

		$logTexts = [];
		foreach ($log_message as $lMessages) {
			foreach ($lMessages as $lKey => $lMessage) {
				$logTexts[] = $lKey . ': ' . trim($lMessage);
			}
		}
		ko_log('edit_login', implode(', ', $logTexts));
		$notifier->addInfo(1, $do_action);

		$_SESSION['show'] = 'show_logins';
	break;



	case "submit_neues_login":
		if($access['admin']['MAX'] < 5) break;

		$txt_name = format_userinput($_POST["txt_name"], "js");
		if(empty($txt_name)) {
			$notifier->addError(1, $do_action);
		}

		if($_POST["txt_pwd1"] == "" || $_POST["txt_pwd1"] != $_POST["txt_pwd2"]) {
			$notifier->addError(2, $do_action);
		}

		if($txt_name == "ko_guest" || strlen($_POST["txt_name"]) >= 50) {
			$notifier->addError(3, $do_action);
		}

		if($txt_name == "root") {
			$notifier->addError(10, $do_action);
		}

		ko_get_logins($logins);
		foreach($logins as $l) {
			if($l["login"] == $txt_name) $notifier->addError(4, $do_action);
		}

		if(!$notifier->hasErrors()) {
			$log_message = [];
			$copy_rights_id = format_userinput($_POST["sel_copy_rights"], "uint");
			if($copy_rights_id) {
				ko_get_login($copy_rights_id, $login_template);
				unset($login_template["id"]);
				unset($login_template['ical_hash']);
				$login_template['leute_id'] = format_userinput($_POST['sel_leute_id'], 'uint');
				$login_template['email'] = format_userinput($_POST['txt_email'], 'email');
				$login_template['mobile'] = format_userinput($_POST['txt_mobile'], 'alphanum++');
				$login_template["login"]      = $txt_name;
				$login_template["password"]   = md5($_POST["txt_pwd1"]);
				$login_template["disable_password_change"] = ($_POST["chk_disable_password_change"] ? 1 : 0);
				$id = db_insert_data("ko_admin", $login_template);
				$log_message[] = $login_template;
			} else {
				// directly create a new login, then we can just edit it with all data
				$login_dummy = ["login" => $txt_name];
				$log_message[] = $login_dummy;
				$id = db_insert_data("ko_admin", $login_dummy);
				ko_get_login($id, $old_login);
				foreach($_POST["module_install_status"] AS $installed_module => $status) {
					$save_modules[] = format_userinput($installed_module, "text");
				}

				foreach ($save_modules as $key => $module_name) {
					if (!in_array($module_name, $MODULES)) unset($save_modules[$key]);
					if ($module_name == 'tools' && $id != ko_get_root_id()) unset($save_modules[$key]);
				}

				$_POST["txt_pwd2"] = $_POST["txt_pwd1"]; // set the new password
				ko_admin_save_general($id);

				$done_modules = [];
				$done_modules[] = 'tools';
				ko_admin_update_module("leute", $id);
				ko_admin_update_module("groups", $id);

				foreach ($MODULES_GROUP_ACCESS as $module) {
					if(ko_admin_update_groupaccess($module) === FALSE) continue;
				}

				foreach ($MODULES as $module) {
					if (in_array($module, $done_modules)) continue;
					ko_admin_update_rights($module);
				}
			}

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

			$logTexts = [];
			foreach ($log_message as $lMessages) {
				foreach ($lMessages as $lKey => $lMessage) {
					$logTexts[] = $lKey . ': ' . trim($lMessage);
				}
			}
			ko_log('new_login', implode(', ', $logTexts));

			ko_admin_check_ldap_login($id);
		}//if(!error)

		$_SESSION['show'] = $notifier->hasErrors() ? 'new_login' : 'show_logins';
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
		ko_log_diff('delete_login', $old_login);
	
		//Delete login
		db_delete_data("ko_admin", "WHERE `id` = '$id'");

		ko_admin_remove_groups($id, "login");

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



	//Detailed person export
	case "list_detailed_person_exports":
		if($access['admin']['MAX'] < 2) break;

		$_SESSION['show'] = 'list_detailed_person_exports';
	break;

	case "new_detailed_person_export":
		if($access['admin']['MAX'] < 2) break;

		$_SESSION['show'] = 'new_detailed_person_export';
		$onload_code = 'form_set_first_input();'.$onload_code;
	break;

	case 'edit_detailed_person_export':
		$id = format_userinput($_POST['id'], 'uint');
		if ($id == '') {
			$id = format_userinput($_GET['id'], 'uint');
		}
		if (!$id) break;
		if($access['admin']['ALL'] < 2) break;

		$_SESSION['show'] = 'edit_detailed_person_export';
		$onload_code = 'form_set_first_input();'.$onload_code;
	break;

	case "delete_detailed_person_export":
		if($access['admin']['MAX'] < 2) break;
		$id = format_userinput($_POST['id'], 'uint');
		$entry = db_select_data('ko_detailed_person_exports', "WHERE `id` = '$id'", '*', '', '', TRUE, TRUE);
		if(!$entry['id'] || $entry['id'] != $id) {
			$notifier->addError(8, $do_action);
			break;
		}

		db_delete_data('ko_detailed_person_exports', "WHERE `id` = '$id'");
		ko_log_diff('del_detailed_person_exports', $entry);
	break;


	case "submit_new_detailed_person_export":
	case "submit_as_new_detailed_person_export":
		if($access['admin']['MAX'] < 2) break;

		if($do_action == 'submit_as_new_detailed_person_export') {
			list($table, $columns, $ids, $hash) = explode('@', $_POST['id']);
			//Fake POST[id] for kota_submit_multiedit() to remove the id from the id. Otherwise this entry will be edited
			$new_hash = md5(md5($mysql_pass.$table.implode(':', explode(',', $columns)).'0'));
			$_POST['id'] = $table.'@'.$columns.'@0@'.$new_hash;
		}

		if (substr($_FILES['koi']['name']['ko_detailed_person_exports']['template'][0],-5) != ".docx") {
			$notifier->addTextError(getLL('error_leute_export_details_docx_upload')); break;
		}
		$new_id = kota_submit_multiedit('', 'new_detailed_person_export');
		if(!$notifier->hasErrors()) {
			$_SESSION['show'] = 'list_detailed_person_exports';
		}
	break;

	case "submit_edit_detailed_person_export":
		if($access['admin']['ALL'] < 2) break;

		list($table, $columns, $id, $hash) = explode("@", $_POST["id"]);
		if ($_POST['koi']['ko_detailed_person_exports']['template_DELETE'][$id] == 1 &&
			substr($_FILES['koi']['name']['ko_detailed_person_exports']['template'][$id],-5) != ".docx") {
			$notifier->addTextError(getLL('error_leute_export_details_docx_upload')); break;
		}
		kota_submit_multiedit('', 'edit_detailed_person_export');

		if(!$notifier->hasErrors()) {
			$_SESSION['show'] = 'list_detailed_person_exports';
		}
	break;



	//Etiketten-Vorlage
	case "list_labels":
		if($access['admin']['MAX'] < 2) break;

		$_SESSION['show'] = 'list_labels';
	break;

	case "new_label":
		if($access['admin']['MAX'] < 2) break;

		$_SESSION['show'] = 'new_label';
		$onload_code = 'form_set_first_input();'.$onload_code;
	break;

	case 'edit_label':
		$id = format_userinput($_POST['id'], 'uint');
		if ($id == '') {
			$id = format_userinput($_GET['id'], 'uint');
		}
		if (!$id) break;
		if($access['admin']['ALL'] < 2) break;

		$_SESSION['show'] = 'edit_label';
		$onload_code = 'form_set_first_input();'.$onload_code;
	break;

	case "delete_label":
		if($access['admin']['MAX'] < 2) break;
		$id = format_userinput($_POST['id'], 'uint');
		$entry = db_select_data('ko_labels', "WHERE `id` = '$id'", '*', '', '', TRUE, TRUE);
		if(!$entry['id'] || $entry['id'] != $id) {
			$notifier->addError(8, $do_action);
			break;
		}

		db_delete_data('ko_labels', "WHERE `id` = '$id'");
		ko_log_diff('del_label', $entry);
	break;


	case "submit_new_label":
	case "submit_as_new_label":
		if($access['admin']['MAX'] < 2) break;

		if($do_action == 'submit_as_new_label') {
			list($table, $columns, $ids, $hash) = explode('@', $_POST['id']);
			//Fake POST[id] for kota_submit_multiedit() to remove the id from the id. Otherwise this entry will be edited
			$new_hash = md5(md5($mysql_pass.$table.implode(':', explode(',', $columns)).'0'));
			$_POST['id'] = $table.'@'.$columns.'@0@'.$new_hash;
		}

		$new_id = kota_submit_multiedit('', 'new_label');
		if(!$notifier->hasErrors()) {
			$_SESSION['show'] = 'list_labels';
		}
	break;

	case "submit_edit_label":
		if($access['admin']['ALL'] < 2) break;

		kota_submit_multiedit('', 'edit_label');

		if(!$notifier->hasErrors()) {
			$_SESSION['show'] = 'list_labels';
		}
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
		$_SESSION["disable_password_change"] = $sudo_l["disable_password_change"];

		$u_admingroups = ko_get_admingroups($sudo_id);
		foreach($u_admingroups AS $u_admingroup) {
			if ($u_admingroup['disable_password_change'] == 1) {
				$_SESSION["disable_password_change"] = 1;
			}
		}

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
	case "submit_as_new_leute_pdf":
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



	case 'delete_v11':
		if($access['vesr'] < 1) break;

		$id = format_userinput($_POST['id'], 'uint');
		if(!$id) break;

		$entry = db_select_data('ko_vesr', "WHERE `id` = '$id'", '*', '', '', TRUE);
		if(!$entry['id'] || $entry['id'] != $id) break;

		db_delete_data('ko_vesr', "WHERE `id` = '$id'");
		ko_log_diff('delete_vesr', $entry);
		$notifier->addInfo(10);
	break;

	case 'delete_camt':
		if($access['vesr'] < 1) break;

		$id = format_userinput($_POST['id'], 'uint');
		if(!$id) break;

		$entry = db_select_data('ko_vesr_camt', "WHERE `id` = '$id'", '*', '', '', TRUE);
		if(!$entry['id'] || $entry['id'] != $id) break;

		db_delete_data('ko_vesr_camt', "WHERE `id` = '$id'");
		ko_log_diff('delete_vesr_camt', $entry);
		$notifier->addInfo(10);
		break;

	case 'vesr_import':
		if ($access['vesr'] < 1) break;

		$_SESSION['show_back'] = $_SESSION['show'];
		$_SESSION['show'] = 'vesr_import';
	break;

	case 'submit_vesr_import':
		if($access['vesr'] < 1) break;

		// TODO: handle camt upload
		@mkdir("{$BASE_PATH}my_images/mt940", 0775);
		@mkdir("{$BASE_PATH}my_images/camt", 0775);
		@mkdir("{$BASE_PATH}my_images/camt/done", 0775);
		@mkdir("{$BASE_PATH}my_images/v11", 0775);
		if(is_array(($esrFile = $_FILES['esrpayment_file']))) {
			if (strtolower(substr($esrFile['name'], -4)) == '.v11' || strtolower(substr($esrFile['name'], -4)) == '.esr') { // v11 import
				$upload_dir = $BASE_PATH.'my_images/v11/';
				$filename = 'vesr_'.date('Ymd_His').'.v11';
				move_uploaded_file($_FILES['esrpayment_file']['tmp_name'], $upload_dir.$filename);
				$file = $upload_dir.$filename;
				if(!file_exists($file)) $notifier->addError(19);

				if (ko_vesr_is_duplicate_file($filename)) {
					unlink($upload_dir.$filename);
					$notifier->addError(21);
				} else {
					$vesr_error = ko_vesr_import($file, $vesr_data, $vesr_done);
					if($vesr_error) {
						$notifier->addError($vesr_error);
						unlink($upload_dir.$filename);
					}
				}
			} else if (strtolower(substr($esrFile['name'], -4)) == '.zip' || strtolower(substr($esrFile['name'], -4)) == '.xml') { // camt import

				$PREVIEW_CAMT_IMPORT = ($_REQUEST['preview'] ? TRUE : FALSE);

				try {
					if(!$PREVIEW_CAMT_IMPORT) {
						$upload_dir = $BASE_PATH.'my_images/camt/';
						$processingFolder = $BASE_PATH.'my_images/camt/';

						if(is_file($upload_dir."done/".$esrFile['name'])) {
							throw new \LPC\LpcEsr\CashManagement\ProcessingException(getLL('error_admin_24'));
						}

						move_uploaded_file($esrFile['tmp_name'], $upload_dir.$esrFile['name']);

						$reader = new \LPC\LpcEsr\CashManagement\OfflineReader();
						$reader->setMessageFolder($processingFolder);
						$reader->setSourceFolder($upload_dir);

						$processor = new \LPC\LpcEsr\CashManagement\koProcessor;
						$reader->registerProcessor($processor);
						$reader->readAll();
						$vesr_camt_done = $processor->getDoneRows();
						$vesr_camt_total = $processor->getDoneTotal();
					} else {
						$reader = new \LPC\LpcEsr\CashManagement\OfflineReader();
						$reader->parseXml(file_get_contents($esrFile['tmp_name']),$esrFile['name']);
						$camtImport = $reader->getMessagesToProcess();
					}
				} catch(\LPC\LpcEsr\CashManagement\ParsingException $ex) {
					$notifier->addError(23, $do_action);
					ko_log('camt_parse_error', 'Error while parsing ' . ($upload_dir.$esrFile['name']) .' '. $ex->getMessage());
				}
				catch(\LPC\LpcEsr\CashManagement\ProcessingException $ex) {
					$notifier->addError(24, $do_action);
					ko_log('camt_parse_error', 'Error while parsing ' . ($upload_dir.$esrFile['name']) .' '. $ex->getMessage());
				}
			} else { // mt940 import

				$upload_dir = $BASE_PATH.'my_images/mt940/';
				$filename = 'vesr_'.date('Ymd_His').'.txt';
				move_uploaded_file($esrFile['tmp_name'], $upload_dir.$filename);

				class Zkb extends \Kingsquare\Parser\Banking\Mt940\Engine
				{
					/**
					 * returns the name of the bank.
					 *
					 * @return string
					 */
					protected function parseStatementBank()
					{
						return 'ZKB';
					}

					/**
					 * Overloaded: Is applicable if second line has ZKB.
					 *
					 * {@inheritdoc}
					 */
					public static function isApplicable($string)
					{
						$firstLine = strtok($string, "\n\r");
						return strpos($firstLine, 'ZKB') !== false;
					}

					/**
					 * uses the 61 field to determine the value timestamp.
					 *
					 * @return int
					 */
					protected function parseTransactionValueTimestamp()
					{
						$results = [];
						if (preg_match_all('/[:\n]?24:(\d{6})/s', $this->getCurrentTransactionData(), $results)
							&& !empty($results[1])
						) {
							return $this->sanitizeTimestamp($results[1], 'ymd');
						}

						return $this->parseTransactionTimestamp('61');
					}

					/**
					 * @param string $string
					 *
					 * @return string
					 */
					protected function sanitizeDescription($string)
					{
						$string = preg_replace('/\r+/', '', trim($string));
						$string = preg_replace('/\?(\d+|ZKB|ZI):[^\n:\?]*/', '', $string);
						return trim(preg_replace('/\n+/', "\n", $string));
					}


				}

				$parser = new \Kingsquare\Parser\Banking\Mt940();
				\Kingsquare\Parser\Banking\Mt940\Engine::registerEngine(Zkb::class, 1);
				$parsedStatements = $parser->parse(file_get_contents($upload_dir.$filename));

				$allAccounts = db_select_data('ko_donations_accounts', "WHERE 1=1");
				foreach ($allAccounts as &$account) {
					$account['search_name'] = strtolower(str_replace(array(' ', "'", '"', '+', '-', '.', ','), array('', '', '', '', '', '', ''), $account['name']));
					$account['search_number'] = strtolower(str_replace(array(' ', "'", '"', '+', '-', '.', ','), array('', '', '', '', '', '', ''), $account['number']));
				}

				$nNewMods = 0;
				foreach ($parsedStatements as $statement) {
					foreach ($statement->getTransactions() as $transaction) {
						// Skip payments (Debit) in import
						if ($transaction->getDebitCredit() == "D") { continue; }

						$comment = $transaction->getDescription();
						$searchComment = strtolower(str_replace(array(' ', "'", '"', '+', '-', '.', ','), array('', '', '', '', '', '', ''), $comment));
						$account = NULL;
						foreach ($allAccounts as $a) {
							if (strpos($searchComment, $account['search_name']) !== FALSE || strpos($searchComment, $account['search_number']) !== FALSE) {
								$account = $a;
								break;
							}
						}
						if ($account) $accountId = $account['id'];
						else $accountId = 0;

						$commentLines = ko_array_filter_empty(explode("\n", $comment));
						$foundAddress = ko_fuzzy_address_search($commentLines, $transaction->getAccountName());
						
						$donationMod = array(
							'date' => $transaction->getEntryTimestamp('Y-m-d'),
							'account' => $accountId,
							'amount' => $transaction->getPrice(),
							'valutadate' => $transaction->getValueTimestamp('Y-m-d'),
							'comment' => $transaction->getDescription(),
							'_crdate' => date('Y-m-d H:i:s'),
							'_cruser' => $_SESSION['ses_userid'],
							'_p_firm' => $foundAddress['firm'],
							'_p_department' => $foundAddress['department'],
							'_p_adresse' => $foundAddress['address'],
							'_p_adresse_zusatz' => $foundAddress['postfach'],
							'_p_plz' => $foundAddress['zip'],
							'_p_ort' => $foundAddress['city'],
						);
						db_insert_data('ko_donations_mod', $donationMod);
						$nNewMods++;
					}
				}
				if ($nNewMods > 0) {
					$notifier->addInfo(11, '', array($nNewMods));
				} else {
					$notifier->addError(22);
				}
			}

		} else {
			$notifier->addError(20);
		}

		$_SESSION['show'] = 'vesr_import';
	break;

	case 'vesr_archive':
		if($access['vesr'] < 1) break;

		$_SESSION['show'] = 'vesr_archive';
	break;

	case 'vesr_settings':
		if ($access['vesr'] < 2) break;

		$_SESSION['show_back'] = $_SESSION['show'];
		$_SESSION['show'] = 'vesr_settings';
	break;

	case 'submit_vesr_settings':
		if ($access['vesr'] < 2) break;

		ko_set_setting('vesr_import_email_host', format_userinput($_POST['txt_vesr_import_email_host'], 'text'));
		ko_set_setting('vesr_import_email_user', format_userinput($_POST['txt_vesr_import_email_user'], 'text'));
		// encrypt password
		require_once($BASE_PATH.'inc/class.openssl.php');
		$crypt = new openssl('AES-256-CBC');
		$crypt->setKey(KOOL_ENCRYPTION_KEY);
		$value = trim($crypt->encrypt(format_userinput($_POST['txt_vesr_import_email_pass'], 'text')));
		ko_set_setting('vesr_import_email_pass', $value);
		ko_set_setting('vesr_import_email_ssl', format_userinput($_POST['chk_vesr_import_email_ssl'], 'uint'));
		ko_set_setting('vesr_import_email_port', format_userinput($_POST['chk_vesr_import_email_port'], 'uint'));
		ko_set_setting('vesr_import_email_report_address', format_userinput($_POST['txt_vesr_import_email_report_address'], 'email', FALSE, 0, array(), ','));

		if ($_SESSION['ses_userid'] == ko_get_root_id()) {
			ko_set_setting('camt_import_port', $_POST['txt_camt_import_port']);
			ko_set_setting('camt_import_user', $_POST['txt_camt_import_user']);
			ko_set_setting('camt_import_host', $_POST['txt_camt_import_host']);
			ko_set_setting('camt_import_public_key', str_replace("\n", "\r\n", str_replace("\r", '', $_POST['txt_camt_import_public_key'])));
			ko_set_setting('camt_import_private_key', str_replace("\n", "\r\n", str_replace("\r", '', $_POST['txt_camt_import_private_key'])));
			ko_set_setting('currencyconverterapi_key', $_POST['txt_currencyconverterapi_key']);
		}

		$_SESSION['show'] = $_SESSION['show_back'] ? $_SESSION['show_back'] : 'show_logins';
	break;

	case 'google_cloud_printers':
		if ($access['admin']['MAX'] < 5) break;

		$_SESSION['show'] = 'google_cloud_printers';
	break;

	case 'qz_tray_printers':
		if ($access['admin']['MAX'] < 5) break;

		$_SESSION['show'] = 'qz_tray_printers';
	break;

	case 'refresh_google_cloud_printers':
		if ($access['admin']['MAX'] < 5) break;

		ko_update_google_cloud_printers();

		$task = db_select_data('ko_scheduler_tasks', "WHERE `call` = 'ko_task_update_google_cloud_printers'", '*', '', '', TRUE);
		if ($task['status'] == 0) {
			db_update_data('ko_scheduler_tasks', "WHERE `call` = 'ko_task_update_google_cloud_printers'", array('status' => 1));
		}
	break;

	case 'show_pubkey':
		if($_SESSION['ses_userid'] == ko_get_root_id()) {
			$_SESSION['show'] = 'show_pubkey';
		}
	break;

	//Default:
  default:
		if(!hook_action_handler($do_action)) {
			include($ko_path."inc/abuse.inc");
		}
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
print ko_include_css();

$js_files = array();
$js_files[] = $ko_path.'inc/ckeditor/ckeditor.js';
$js_files[] = $ko_path.'inc/ckeditor/adapters/jquery.js';
if(in_array($_SESSION['show'], array('new_login',  'new_admingroup', 'edit_login', 'edit_admingroup'))) $js_files[] = $ko_path.'inc/selectmenu.js';
$js_files[] = $ko_path.'inc/ckeditor/ckeditor.js';
$js_files[] = $ko_path.'inc/ckeditor/adapters/jquery.js';
print ko_include_js($js_files);

include($ko_path.'admin/inc/js-admin.inc');
include($ko_path.'inc/js-sessiontimeout.inc');
include("inc/js-admin.inc");

//Prepare group double selects when editing a login
if(in_array($_SESSION['show'], array('new_login', 'new_admingroup', 'edit_login', 'edit_admingroup'))) {
	//Show dummy groups because the rights will be propagated downwards to all children
	$show_all_types = TRUE;
	//Show all groups, also terminated ones
	$show_passed_groups = ko_get_userpref($_SESSION['ses_userid'], 'show_passed_groups');
	ko_save_userpref($_SESSION['ses_userid'], 'show_passed_groups', 1);
	//View
	$list_id = 1;
	foreach (['view', 'new', 'edit', 'del'] AS $rights_level) {
		include($ko_path . 'leute/inc/js-groupmenu.inc');
		$loadcode .= "initList(" . $list_id . ", document.getElementsByName('sel_ds1_sel_groups_rights_" . $rights_level . "')[0]);";
		$list_id++;
	}

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

ko_get_outer_submenu_code('admin');

?>

<main class="main">
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
	case "admin_settings";
		ko_admin_settings();
	break;
	case "list_labels";
		ko_admin_list_labels();
	break;
	case "edit_label":
	case "new_label":
		ko_admin_formular_labels(($_SESSION['show'] == 'new_label' ? 'new' : 'edit'), $id);
	break;
	case "list_detailed_person_exports";
		ko_admin_list_detailed_person_exports();
	break;
	case "edit_detailed_person_export":
	case "new_detailed_person_export":
		ko_admin_formular_detailed_person_export(($_SESSION['show'] == 'new_detailed_person_export' ? 'new' : 'edit'), $id);
	break;
	case "set_leute_pdf";
		ko_list_leute_pdf();
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
	case 'show_telegram_log':
		ko_show_telegram_log();
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
	case 'vesr_settings':
		ko_vesr_settings();
	break;
	case 'vesr_import':
		if(isset($vesr_data) && isset($vesr_done) && is_array($vesr_data)) {
			$reportAttachment = ko_vesr_v11_overview($vesr_data, $vesr_done, TRUE, TRUE);
			$report['mails'][0]['attachments'][] = getLL('filename') . ': ' . $esrFile['name'] . "<br>" . $reportAttachment;
			$total = $vesr_data['totals'];
			$total['total'] = $vesr_data['total'];
			$reportfile = ko_vesr_create_reportattachment(array($esrFile['name']), $total, $vesr_done, "v11");
			$attachment = Swift_Attachment::newInstance($reportfile->Output('','S'),'report_esr_import.pdf','application/pdf');
			ko_vesr_send_emailreport($report, 'v11', [$attachment]);
		}
		if (isset($vesr_camt_done) && isset($vesr_camt_total) && is_array($vesr_camt_done)) {
			$reportAttachment = ko_vesr_camt_overview($vesr_camt_total, $vesr_camt_done, TRUE, TRUE);
			$report['mails'][0]['attachments'][] = getLL('filename') . ': ' . $esrFile['name'] . "<br>" . $reportAttachment;
			if (!$PREVIEW_CAMT_IMPORT) {
				$reportfile = ko_vesr_create_reportattachment(array($esrFile['name']), $vesr_camt_total, $vesr_camt_done,  "camt");
				$attachment = Swift_Attachment::newInstance($reportfile->Output('','S'),'report_esr_import.pdf','application/pdf');
				ko_vesr_send_emailreport($report, 'camt', [$attachment]);
			}
		}

		if ($PREVIEW_CAMT_IMPORT) {
			ko_vesr_camt_preview($camtImport);
		}

		ko_show_vesr_import();
	break;
	case 'vesr_archive':
		ko_admin_vesr_archive();
	break;

	case 'google_cloud_printers':
		ko_list_google_cloud_printers();
	break;
	case 'qz_tray_printers':
		ko_list_qz_tray_printers();
	break;

	case 'show_pubkey':
		ko_admin_show_pubkey();
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
