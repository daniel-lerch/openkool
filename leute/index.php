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
$ko_menu_akt = "leute";

include_once($ko_path . "inc/ko.inc");
include_once("inc/leute.inc");

//get notifier instance
$notifier = koNotifier::Instance();

//Redirect to SSL if needed
ko_check_ssl();

if(!ko_module_installed("leute")) {
	header("Location: ".$BASE_URL."index.php");  //Absolute URL
}

//Only allow checkin user if he submitted a form for a new address
if($_SESSION['ses_userid'] == ko_get_checkin_user_id() && ($_POST['async_form_tag'] != 'checkin_add_person' || $_SESSION['checkin_user'] != 'admin')) {
	header('Location: '.$BASE_URL.'index.php'); exit;
}

ob_end_flush();

ko_get_access('leute');
if(ko_module_installed('kg')) ko_get_access('kg');
if(ko_module_installed('crm')) ko_get_access('crm');
if(ko_module_installed('groups')) {
	ko_get_access('groups');
	ko_get_groups($all_groups);
}

//kOOL Table Array
ko_include_kota(array('ko_leute', 'ko_kleingruppen'));


//*** Plugins einlesen:
$hooks = hook_include_main("leute,kg");
if(sizeof($hooks) > 0) foreach($hooks as $hook) include_once($hook);



// required for xls export: fills in the post-data from the previous request
if ($_SESSION['leute_export_xls_post'] != '' && $_POST['action'] == 'leute_submit_export_xls' && $_SESSION['show'] == 'xls_settings') {
	foreach ($_SESSION['leute_export_xls_post'] as $k => $v) {
		$_POST[$k] = $v;
	}
	$_SESSION['show'] = $_SESSION['leute_export_xls_post']['session_show'];
	unset($_SESSION['leute_export_xls_post']);
	$_POST['action'] = 'leute_action';
	$_POST['id'] = 'excel';
	$_POST['from_settings'] = 'true';

	if ($_POST['sel_leute_force_family_firstname'] != '') {
		$v = format_userinput($_POST['sel_leute_force_family_firstname'], 'uint', FALSE, 1);
		if($v == 0 || $v == 1 || $v == 2) {
			ko_save_userpref($_SESSION['ses_userid'], 'leute_force_family_firstname', $v);
		}
	}
	ko_save_userpref($_SESSION['ses_userid'], 'leute_linebreak_columns', format_userinput($_POST['sel_linebreak_columns'], 'alphanumlist'));
	ko_save_userpref($_SESSION['ses_userid'], 'export_table_format', format_userinput($_POST['export_table_format'], 'alphanum'));
}
else {
	unset($_SESSION['leute_export_xls_post']);
}


// required for label export: fills in the post-data from the previous request
if ($_SESSION['leute_export_etiketten_post'] != '' && $_POST['action'] == 'submit_etiketten' && $_SESSION['show'] == 'etiketten_optionen') {
	foreach ($_SESSION['leute_export_etiketten_post'] as $k => $v) {
		$_POST[$k] = $v;
	}
	$_SESSION['show'] = $_SESSION['leute_export_etiketten_post']['session_show'];
	unset($_SESSION['leute_export_etiketten_post']);
	$_POST['action'] = 'leute_action';
	$_POST['id'] = 'etiketten';
	$_POST['from_settings'] = 'true';

	// save userprefs (if necessary)
	if ($_POST['sel_leute_force_family_firstname'] != '') {
		$v = format_userinput($_POST['sel_leute_force_family_firstname'], 'uint', FALSE, 1);
		if($v == 0 || $v == 1 || $v == 2) {
			ko_save_userpref($_SESSION['ses_userid'], 'leute_force_family_firstname', $v);
		}
	}
}
else {
	unset($_SESSION['leute_export_etiketten_post']);
}


//***Action auslesen:
if($_POST["action"]) {
	$do_action = $_POST["action"];
	$action_mode = "POST";
} else if($_GET["action"]) {
	$do_action = $_GET["action"];
	$action_mode = "GET";
} else {
	$do_action = $action_mode = "";
}

if(FALSE === format_userinput($do_action, "alphanum+", TRUE, 50)) trigger_error("invalid action: ".$do_action, E_USER_ERROR);

//Reset show_start if from another module
if($_SERVER['HTTP_REFERER'] != '' && FALSE === strpos($_SERVER['HTTP_REFERER'], '/'.$ko_menu_akt.'/')) $_SESSION['show_start'] = 1;

switch($do_action) {

	//Anzeige
	case 'show_all':
		if($_SESSION['show'] == 'show_all') $_SESSION['show_start'] = 1;
		$_SESSION['show'] = 'show_all';
		$onload_code = "form_set_focus('general_search');".$onload_code;
	break;


	case "show_adressliste":
		$_SESSION["show"] = "show_adressliste";
		$_SESSION["show_start"] = 1;
		$onload_code = "form_set_focus('general_search');".$onload_code;
	break;


	case "geburtstagsliste":
		if($access['leute']['MAX'] < 1) break;
		$allowed_cols = ko_get_leute_admin_spalten($_SESSION['ses_userid'], 'all');
		if(!is_array($allowed_cols['view']) || in_array('geburtsdatum', $allowed_cols['view'])) {
			$_SESSION['show_start'] = 1;
			$_SESSION["show"] = "geburtstagsliste";
		}
	break;


	case "single_view":
		$single_id = format_userinput($_GET["id"], "uint");
		if($access['leute']['ALL'] > 0 || $access['leute'][$single_id] > 0) {
			$_SESSION["show"] = "single_view";
		}
	break;



	case 'show_aa':
		if($access['leute']['MAX'] < 2) break;
		if(!ko_get_setting('leute_allow_moderation') && $access['leute']['MAX'] < 4) break;
		$_SESSION['show'] = $_SESSION['show_back'] = 'mutationsliste';
	break;

	case "groupsubscriptions":
		if(isset($_GET['gid'])) {
			if(!$_GET['gid']) unset($_SESSION['leute_gs_filter']);
			else $_SESSION['leute_gs_filter'] = format_userinput($_GET['gid'], 'uint');
		}
		$_SESSION["show"] = "groupsubscriptions";
	break;




	//My-List
	case "add_to_my_list":
		foreach(explode(',', $_POST['ids']) as $c) {
			if($c) {
				if(FALSE === ($value = format_userinput($c, 'uint', TRUE, 10))) {
					trigger_error("Not allowed my_list selection: $c_i", E_USER_ERROR);
				}
				if($value && !in_array($value, $_SESSION['my_list'])) $_SESSION['my_list'][$value] = $value;
			}
		}
    ko_save_userpref($_SESSION["ses_userid"], "leute_my_list", serialize($_SESSION["my_list"]));
	break;

	case "del_from_my_list":
		foreach(explode(',', $_POST['ids']) as $c) {
			if($c) unset($_SESSION['my_list'][$c]);
		}
    ko_save_userpref($_SESSION["ses_userid"], "leute_my_list", serialize($_SESSION["my_list"]));
	break;

	case "clear_my_list":
		$_SESSION["my_list"] = array();
    ko_save_userpref($_SESSION["ses_userid"], "leute_my_list", serialize($_SESSION["my_list"]));
	break;

	case "show_my_list":
		$_SESSION["show"] = "show_my_list";
		if($_SESSION['show'] == 'show_my_list') $_SESSION['show_start'] = 1;
	break;




	//Neu
	case "neue_person":
		$_SESSION["show_back"] = $_SESSION["show"];
		$_SESSION["show"] = "neue_person";
		$onload_code = "form_set_first_input();".$onload_code;
	break;

	case "submit_neue_person":
	case "submit_edit_person":
	case "submit_als_neue_person":

		list($table, $cols, $id, $hash) = explode('@', $_POST['id']);

		if (!kota_check_all_mandatory_fields($_POST)) {
			$notifier->addError(60);
			if ($id > 0) {
				$_POST['id'] = $id;
			}
			break;
		}

		//Beim Editieren auf korrekte Leute-ID prüfen
		if($do_action == "submit_edit_person") {
			if(!$id || !format_userinput($id, "uint", TRUE, 10)) break;
			$leute_id = format_userinput($id, "uint");
			ko_get_person_by_id($leute_id, $person);
			$action = "submit_edit_person";
		}
		//New person or "save as new person" for an existing one
		else if($do_action == "submit_als_neue_person" || $do_action == "submit_neue_person") {
			//Create a new entry and store new id
			$leute_id = db_insert_data("ko_leute", array("id" => "NULL", "crdate" => date("Y-m-d H:i:s"), "cruserid" => $_SESSION["ses_userid"]));
			$GLOBALS['insertedIds']['ko_leute'][] = $leute_id;
			//Simulate editing with new id
			$action = "submit_edit_person";
		  //Everything as when editing, but a new LDAP entry has to be created
			$ldap_new_entry = TRUE;
		}//if(do_action == submit_als_neue_person)
		else {
			break;
		}

		if($access['leute']['MAX'] > 1 || (ko_get_setting("login_edit_person") == 1 && $leute_id == ko_get_logged_in_id())) {}
		else {
			if ($do_action != "submit_edit_person") {
				db_delete_data('ko_leute', "WHERE `id` = '{$leute_id}'"); // TODO: is this okay?
			}
			break;
		}

		if ($do_action == "submit_edit_person") {
			// Do initial save leute changes
			ko_save_leute_changes($leute_id);
		}

		//LDAP
		$do_ldap = ko_do_ldap();


		//Datenbank-Spalten auslesen
		$leute_cols = db_get_columns("ko_leute");
		$col_namen = ko_get_leute_col_name();

		//get the cols, for which this user has edit-rights (saved in allowed_cols[edit])
		$allowed_cols = ko_get_leute_admin_spalten($_SESSION["ses_userid"], "all", $leute_id);

		//Handle view and edit separately because if only one of them is set for a login,
		//the test is_array() has to be called separately, so allowed_cols doesn't get turned into an array.
		if(is_array($allowed_cols["view"]) && sizeof($allowed_cols["view"]) > 0) {
			if($access['groups']['MAX'] > 0) $allowed_cols["view"][] = "groups";
			if($access['kg']['MAX'] > 1) $allowed_cols["view"][] = "smallgroups";
		}
		if(is_array($allowed_cols["edit"]) && sizeof($allowed_cols["edit"]) > 0) {
			if($access['groups']['MAX'] > 1) $allowed_cols["edit"][] = "groups";
			if($access['kg']['MAX'] > 2) $allowed_cols["edit"][] = "smallgroups";
		}

		/* Familien-Daten speichern */
		$old_famid  = $person["famid"];
		if(!is_array($allowed_cols["edit"]) || in_array("famid", $allowed_cols["edit"])) {
			$do_update_familie = FALSE;  //Familiendaten - falls nötig - erst nach Speichern der Person updaten
			$new_familie = FALSE;

			//Neue Familie
			if($_POST["hid_new_family"] == 1) {
				$save_famid = db_insert_data("ko_familie", array("nachname" => ""));
				$new_familie = TRUE;
			} else {
				$save_famid = format_userinput($_POST["sel_familie"], "uint");
			}

			//Familie geändert
			if($save_famid != $old_famid) {
				$log_message .= getLL("leute_log_family").": $old_famid --> $save_famid, ";

				//Update address before calling kota_process_data() below,
				//  as there ko_multiedit_familie() would store new address to old family
				db_update_data('ko_leute', "WHERE `id` = '$leute_id'", array('famid' => $save_famid));

				//Alte Familie löschen, falls keine Mitglieder mehr
				if($old_famid != 0) {
					$num = ko_get_personen_by_familie($old_famid, $asdf);
					if($num < 1) {
						db_update_data('ko_leute', "WHERE `famid` = '$old_famid'", array('famid' => '0', 'kinder' => '0'));
						db_delete_data('ko_familie', "WHERE `famid` = '$old_famid'");
					}
				}
			}

			// save father, mother
			$data['father'] = format_userinput($_POST['input_father'], 'uint');
			$data['mother'] = format_userinput($_POST['input_mother'], 'uint');

			// save famfunction
			$data['famfunction'] = format_userinput($_POST['input_famfunction'], 'alphanum');
		}//if(in_array(famid, allowed_cols[edit]))
		else {
			$save_famid = $person['famid'];
		}

		// Process KOTA part of submitted data
		$kotaData = $_POST["koi"]["ko_leute"];
		kota_process_data("ko_leute", $kotaData, "post", $log_, $leute_id, ($leute_id==0));


		//Update fam fields
		if($save_famid > 0) {
			$familien_cols = db_get_columns('ko_familie');
			foreach($familien_cols as $col_) {
				$col = $col_['Field'];
				
				//don't save not allowed columns (edit)
				//kota_process_data() adds empty values for checkbox/switch inputs if they're not set in _POST
				// so make sure to not store them on the family if no access is given, which makes them not to show up in _POST
				if(is_array($allowed_cols['edit'])) {
				   if(!in_array($col, $allowed_cols['edit'])) continue;
				} else if(is_array($allowed_cols['view'])) {
				   if(!in_array($col, $allowed_cols['view'])) continue;
				}

				if($col && isset($kotaData[$col])) {
					$fam_data[$col] = $kotaData[$col];
				} else if($col && isset($_POST['input_'.$col])) {
					$fam_data[$col] = format_userinput($_POST['input_'.$col], 'text');
				}
			}
			ko_update_familie($save_famid, $fam_data, $leute_id);
		}

		foreach($KOTA['ko_leute'] as $col => $def) {
			if (substr($col, 0, 1) == '_') continue;
			if (!is_array($def['form'])) continue;
			if ($def['form']['type'] == 'html') continue;
			if ($def['form']['dontsave']) continue;
			if (in_array($col, $KOTA['ko_leute']['_form_layout']['_ignore_fields'])) continue;

			//don't save not allowed columns (edit)
			if(is_array($allowed_cols["edit"])) {
				 if(!in_array($col, $allowed_cols["edit"])) continue;
			} else if(is_array($allowed_cols['view'])) {
				 if(!in_array($col, $allowed_cols["view"])) continue;
			}

			if (isset($kotaData[$col."_DELETE"]) && $person[$col]) {
				if (!isset($kotaData[$col])) $data[$col] = '';
				continue;
			}

			// Get value
			if (!isset($kotaData[$col])) continue;
			$value = $kotaData[$col];

			if ($col == 'groups') {
				// Handle groups
				if (ko_module_installed("groups") && $access['groups']['MAX'] > 1) {
					ko_groups_get_savestring($value, array("id" => $leute_id), $log, $person["groups"]);
					$data['groups'] = $value;
					//Store current datafields for versioning (stored with ko_save_leute_changes())
					$datafields = ko_get_datafields($leute_id);
					//Store datafields in DB
					ko_groups_save_datafields($_POST["group_datafields"], array("id" => $leute_id, "groups" => $value, "old_groups" => $person["groups"]), $log2);

					//Log-Message:
					if(trim($log) != "") $log_message .= getLL("leute_log_groups").": $log";
					if(trim($log2) != "") $log_message .= getLL("leute_log_datafields").": $log2";
				}
			} else {
				$data[$col] = trim($value);

				//Log-Message
				$label = getLL('kota_ko_leute_' . $col);
				if($action == "submit_neue_person") {  //Alle gemachten Angaben loggen
					if($value) {
						$ll = getLL('kota_ko_leute_'.$col.'_'.$value);
						$log_message .= "$label: '".($ll ? $ll : $value)."', ";
					}
				} else {  //Bei Editieren nur die loggen, die geändert wurden
					$new  = $value;
					$old  = $person[$col];
					if($old == '00:00:00' || $old == '0000-00-00' || $old == '0000-00-00 00:00:00') $old = '';
					if( ($old || $new) && $new != $old) {
						$ll_old = getLL('kota_ko_leute_'.$col.'_'.$old);
						$ll_new = getLL('kota_ko_leute_'.$col.'_'.$new);
						$log_message .= $label.": '".($ll_old ? $ll_old : $old)."' --> '".($ll_new ? $ll_new : $new)."', ";
					}
				}
			}
		}//foreach($kotaData)

		// create log entry for parents
		foreach (array('father', 'mother') as $parent) {
			if (!isset($data[$parent])) continue;
			$val = (int)$data[$parent];
			if ($action == 'submit_neue_person') {
				$log_message .= "{$col_namen[$parent]}: '{$val}', ";
			} else {
				if ($person[$parent] != $val) {
					$log_message .= "{$col_namen[$parent]}: '{$person[$parent]}' --> '{$val}', ";
				}
			}
		}


		//Check for leute_admin_groups to be added
		if((!defined('LEUTE_ADMIN_GROUPS_NEW_ONLY') || LEUTE_ADMIN_GROUPS_NEW_ONLY == FALSE)
				|| in_array($do_action, array("submit_neue_person", "submit_als_neue_person")))
			{
			$lag = ko_get_leute_admin_groups($_SESSION["ses_userid"], 'all');
			if(is_array($lag) && sizeof($lag) > 0) {
				foreach($lag as $gid) {
					if(!$gid) continue;
					if(FALSE === strstr($data["groups"], $gid)) {
						$data["groups"] .= $data["groups"] != "" ? ",".$gid : $gid;
					}
				}
			}
		}


		//In DB speichern
		$data["lastchange"] = date("Y-m-d H:i:s");  //LastChange hinzufügen
	  //Familien-ID hinzufügen
		if(!is_array($allowed_cols["edit"]) || in_array("famid", $allowed_cols["edit"])) {
			$data["famid"] = $save_famid;
			if($data['famid'] <= 0) $data['kinder'] = '0';  //Set number of children to 0 for persons without a family
		}
		if($action == "submit_edit_person") {
			db_update_data("ko_leute", "WHERE `id` = '$leute_id'", $data);
		} else {
			//Not needed? as submit_neue_person is handled as submit_edit_person with id of new empty entry...?
			$leute_id = db_insert_data("ko_leute", $data);
		}


		// Create revision entry for checkin
		if ($ASYNC_FORM_TAG == 'checkin_add_person' && $do_action == 'submit_neue_person') {
			$revisionEntry = array(
				'leute_id' => $leute_id,
				'reason' => 'checkin',
				'crdate' => date('Y-m-d H:i:s'),
				'cruser' => $_SESSION['ses_userid'],
			);
			db_insert_data('ko_leute_revisions', $revisionEntry);
		}


		// Spouse
		ko_leute_set_spouse($leute_id, format_userinput($_POST['input_spouse'], 'uint'));

		//Store email checkboxes to define prefered email fields
		if(sizeof($LEUTE_EMAIL_FIELDS) > 1) {
			foreach($LEUTE_EMAIL_FIELDS as $email_field) {
				if(is_array($allowed_cols['edit']) && !in_array($email_field, $allowed_cols['edit'])) continue;
				$current = db_select_data('ko_leute_preferred_fields', "WHERE `type` = 'email' AND `lid` = '$leute_id' AND `field` = '$email_field'", '*', '', '', TRUE);
				if($_POST['email_chk_'.$email_field]) {  //Field selected
					if($data[$email_field]) {  //Only set checkbox if email address is given
						if(!$current) db_insert_data('ko_leute_preferred_fields', array('type' => 'email', 'lid' => $leute_id, 'field' => $email_field));
					} else {  //Delete checkbox if no email address is given
						if($current) db_delete_data('ko_leute_preferred_fields', "WHERE `type` = 'email' AND `lid` = '$leute_id' AND `field` = '$email_field'");
					}
				} else {  //Field not selected
					if($current) db_delete_data('ko_leute_preferred_fields', "WHERE `type` = 'email' AND `lid` = '$leute_id' AND `field` = '$email_field'");
				}
			}
		}

		//Store mobile checkboxes to define prefered mobile fields
		if(sizeof($LEUTE_MOBILE_FIELDS) > 1) {
			foreach($LEUTE_MOBILE_FIELDS as $mobile_field) {
				if(is_array($allowed_cols['edit']) && !in_array($mobile_field, $allowed_cols['edit'])) continue;
				$current = db_select_data('ko_leute_preferred_fields', "WHERE `type` = 'mobile' AND `lid` = '$leute_id' AND `field` = '$mobile_field'", '*', '', '', TRUE);
				if($_POST['mobile_chk_'.$mobile_field]) {  //Field selected
					if($data[$mobile_field]) {  //Only set checkbox if mobile number is given
						if(!$current) db_insert_data('ko_leute_preferred_fields', array('type' => 'mobile', 'lid' => $leute_id, 'field' => $mobile_field));
					} else {  //Delete checkbox if no mobile number is given
						if($current) db_delete_data('ko_leute_preferred_fields', "WHERE `type` = 'mobile' AND `lid` = '$leute_id' AND `field` = '$mobile_field'");
					}
				} else {  //Field not selected
					if($current) db_delete_data('ko_leute_preferred_fields', "WHERE `type` = 'mobile' AND `lid` = '$leute_id' AND `field` = '$mobile_field'");
				}
			}
		}


		//In LDAP speichern
		if($do_ldap) {
			$ldap = ko_ldap_connect();
			//Get full person record (if only some columns can be edited only these would be in $data)
			ko_get_person_by_id($leute_id, $ldap_person);
			if($action == "submit_edit_person" && !$ldap_new_entry) {  //Als neue Person speichern, ist zwar edit, muss aber neu angelegt werden.
				ko_ldap_add_person($ldap, $ldap_person, $person['id'], TRUE);
			} else {
				ko_ldap_add_person($ldap, $ldap_person, $leute_id);
			}
			ko_ldap_close($ldap);
		}//if(do_ldap)


		//ezmlm: change email in mailinglist address if email has changed
		if($action != 'submit_neue_person' && defined("EXPORT2EZMLM") && EXPORT2EZMLM) {
			$old_email = $person['email']; $new_email = $_POST['input_email'];
			if($old_email != $new_email && check_email($new_email)) {  //Check for changed valid email
				foreach(explode(",", $data["groups"]) as $group) {  //Check this user's groups for one with an ML assigned
					$gid = ko_groups_decode($group, "group_id");
					if($all_groups[$gid]["ezmlm_list"]) {
						//Un- and resubscribe
						ko_ezmlm_unsubscribe($all_groups[$gid]["ezmlm_list"], $all_groups[$gid]["ezmlm_moderator"], $old_email);
						ko_ezmlm_subscribe($all_groups[$gid]["ezmlm_list"], $all_groups[$gid]["ezmlm_moderator"], $new_email);
					}
				}
			}
		}


		//Update count for groups this person is/was assigned to
		$ag = array_unique(array_merge(explode(',', $person['groups']), explode(',', $data['groups'])));
		foreach($ag as $g) {
			$g = ko_groups_decode($g, 'group_id');
			if($all_groups[$g]['maxcount'] > 0) {
				ko_update_group_count($g, $all_groups[$g]['count_role']);
			}
		}

		//Log-Meldung
		if($do_action == "submit_neue_person") {
			ko_log("new_person", $leute_id . ": " . substr($log_message,0,-2));
			$notifier->addInfo(1, $do_action);
		} else {
			if($log_message != "") {
				$name = $person["vorname"]." ".$person["nachname"];
				if(!$name) $name = $person["firm"];
				ko_log("edit_person", $leute_id . " ($name): " . substr($log_message,0,-2));
				//Save changes for versioning
				ko_save_leute_changes($leute_id, $person, $datafields);

				//Announce changes to selected users
				if(sizeof($_POST['sel_announce_changes']) > 0) {
					$moderator = ko_get_logged_in_person();
					foreach($_POST['sel_announce_changes'] as $lid) {
						$lid = (int)$lid;
						if(!$lid) continue;

						$loggedinPerson = ko_get_logged_in_person($lid);
						$text = sprintf(getLL('leute_announce_changes_email_text'), $moderator['vorname'].' '.$moderator['nachname'], $name)."\n\n".str_replace(',', "\n", substr($log_message, 0, -2));

						ko_send_mail(
							'',
							$loggedinPerson['email'],
							getLL('leute_announce_changes_email_subject'),
							$text
						);
					}
				}//if(POST[sel_announce_changes])

			}//if(log_message)
			if (!$notifier->hasErrors()) {
				$notifier->addInfo(2, $do_action);
			}
		}

		// Update groups assignment history
		ko_get_person_by_id($leute_id, $currentPerson);
		ko_create_groups_snapshot($currentPerson, NULL, NULL, TRUE);

		//Set show case
		$_SESSION["show"] = $_SESSION["show_back"] ? $_SESSION["show_back"] : "show_all";
	break;

	case "mailing":
		$_SESSION['show'] = 'leute_mailing';
	break;



	//Bearbeiten
	case "edit_person":
		if($action_mode == 'POST') $leute_id = format_userinput($_POST['id'], 'uint');
		else if($action_mode == 'GET') $leute_id = format_userinput($_GET['id'], 'uint');
		if($access['leute']['ALL'] > 1 || $access['leute'][$leute_id] > 1 || (ko_get_setting('login_edit_person') == 1 && $leute_id == ko_get_logged_in_id())) {} else break;

		$_SESSION["show_back"] = $_SESSION["show"];
		$_POST['id'] = $leute_id;
		$_SESSION["show"] = "edit_person";
	break;

	case "delete_persons":
		if (ko_get_setting('leute_multiple_delete')) {
			foreach($_POST["chk"] as $person_id => $status) {
				if($status == "on") {
					$del_id = format_userinput($person_id, "uint", TRUE);
					if(!$del_id) continue;
					if($access['leute']['MAX'] < 3) continue;

					if(ko_leute_delete_person($del_id)) {
						$notifier->addInfo(3, $do_action);
					} else {
						$notifier->addError(4, $do_action);
					}
				}
			}
		}
	break;



	case 'merge_duplicates':
		//TODO: Merge history of both address records?
		if($access['leute']['MAX'] < 3) break;

		//Find marked persons and test for access level 3 (edit and delete)
		$do_ids = array();
		foreach($_POST['chk'] as $c_i => $c) {
			if(!$c) continue;
			$dup_id = format_userinput($c_i, 'uint');
			if($dup_id > 0 && ($access['leute']['ALL'] > 2 || $access['leute'][$dup_id] > 2)) $do_ids[] = $dup_id;
		}
		if(sizeof($do_ids) <= 0) {
			$notifier->addError(5, $do_action);
			break;
		}


		//Find duplicate filter and get tested fields
		$filters = db_select_data('ko_filter', "WHERE `typ` = 'leute'", '*');
		foreach($_SESSION['filter'] as $k => $v) {
			if(!is_integer($k)) continue;
			if($filters[$v[0]]['name'] == 'duplicates') {
				$fields = explode('-', $v[1][1]);
			}
		}

		//Only get addresses with none-empty test field
		$where = "WHERE `deleted` = '0' ".ko_get_leute_hidden_sql();
		foreach($fields as $field) {
			$where .= ' AND `'.$field.'` != \'\' AND `'.$field.'` != \'0000-00-00\'';
		}
		$all = db_select_data('ko_leute', $where, '*');

		//Build test string for all persons
		$test = array();
		foreach($all as $person) {
			$value = array();
			foreach($fields as $field) $value[] = strtolower($person[$field]);
			$test[$person['id']] = implode('#', $value);
		}
		unset($all);

		//Find dups (only one is left in $dups)
		$dups = array_unique(array_diff_assoc($test, array_unique($test)));
		if(sizeof($dups) <= 0) break;

		//Get ids of all duplicates (not just one for each hit)
		$ids = $dups;
		foreach($test as $tid => $t) {
			if(in_array($t, $dups)) $ids[$tid] = $t;
		}

		//Group duplicates that contain possible duplicates
		$c = 0; $duplicates = array();
		$done = array();
		foreach($ids as $id => $value) {
			if(!in_array($id, $do_ids)) continue;  //Only test marked ids
			if(in_array($id, $done)) continue;  //Don't check IDs which have been added as duplicate of another ID
			foreach($test as $tid => $t) {
				if(!in_array($tid, $do_ids)) continue;  //Only test marked ids
				if($t == $value) {
					$duplicates[$c][] = $tid;
					$done[] = $tid;
				}
			}
			$c++;
		}



		$showMutations = FALSE;
		foreach($duplicates as $ids) {
			ko_leute_merge_ids($ids, $addressChanged, $all_datafields);
			$showMutations = $showMutations || $addressChanged;
		}//foreach(duplicates)

		//Show moderations
		if($showMutations && $access['leute']['MAX'] > 1) $_SESSION['show'] = 'mutationsliste';
	break;



	case 'merge_duplicates_no_filter':
		//TODO: Merge history of both address records?
		if($access['leute']['MAX'] < 3) break;

		//Find marked persons and test for access level 3 (edit and delete)
		$ids = array();
		foreach($_POST['chk'] as $c_i => $c) {
			if(!$c) continue;
			$dup_id = format_userinput($c_i, 'uint');
			if($dup_id > 0 && ($access['leute']['ALL'] > 2 || $access['leute'][$dup_id] > 2)) {
				$ids[] = $dup_id;
			} else {
				$notifier->addError(29, $do_action);
				break;
			}
		}
		if(sizeof($ids) != 2 || sizeof($_POST['chk']) != 2) {
			$notifier->addError(30, $do_action);
			break;
		}

		ko_leute_merge_ids($ids, $showMutations);

		//Show moderations
		if($showMutations && $access['leute']['MAX'] > 1) $_SESSION['show'] = 'mutationsliste';
	break;



	case 'decouple_from_household':
		if ($access['leute']['MAX'] < 2) break;

		//Find marked persons and test for access level 3 (edit and delete)
		$doIds = array();
		$checkAccess = TRUE;
		if (!is_array($_POST['chk']) || sizeof($_POST['chk']) == 0) {
			$doIds = 'APPLY_FILTER';
		} else {
			foreach($_POST['chk'] as $c_i => $c) {
				if(!$c) continue;
				$pid = format_userinput($c_i, 'uint');
				if($pid > 0 && ($access['leute']['ALL'] > 1 || $access['leute'][$pid] > 1)) $doIds[] = $pid;
				$checkAccess = FALSE;
			}
			if(sizeof($doIds) == 0) {
				$notifier->addError(5, $do_action);
				break;
			}
		}

		// decouple selected (or all if none were selected) people from housefolds
		ko_decouple_from_household($doIds, $checkAccess);
	break;




	case "multiedit":
		/* Leute-Multiedit */
		if($_SESSION["show"] == "show_all" || $_SESSION["show"] == "show_my_list") {
			if($access['leute']['MAX'] < 2) break;

			//Zu bearbeitende Spalten
			$columns = explode(",", format_userinput($_POST["id"], "alphanumlist"));
			foreach($columns as $column) {
				$do_columns[] = $column;
			}
			if(sizeof($do_columns) < 1) koNotifier::Instance()->addError(4);

			//Zu bearbeitende Einträge
			$do_ids = array();
			foreach($_POST["chk"] as $c_i => $c) {
				if($c) {
					if(FALSE === ($edit_id = format_userinput($c_i, "uint", TRUE))) {
						trigger_error("Not allowed multiedit_id: ".$c_i, E_USER_ERROR);
					}
					if($access['leute']['ALL'] > 1 || $access['leute'][$edit_id] > 1) $do_ids[] = $edit_id;
				}
			}
			if(sizeof($do_ids) < 1) $notifier->addError(5, $do_action);

			//Daten für Formular-Aufruf vorbereiten
			if(!$notifier->hasErrors()) {
				if(substr($_SESSION["sort_leute"][0], 0, 6) == "MODULE") {
					$order = "ORDER BY nachname ASC";
				} else {
					$order = "ORDER BY ".$_SESSION["sort_leute"][0]." ".$_SESSION["sort_leute_order"][0];
				}
				$_SESSION["show_back"] = $_SESSION["show"];
				$_SESSION["show"] = "multiedit";
			}


		/* KG-Multiedit */
		} else if($_SESSION["show"] == "list_kg") {
			if($access['kg']['MAX'] < 3) break;

			//Zu bearbeitende Spalten
			$columns = explode(",", format_userinput($_POST["id"], "alphanumlist"));
			foreach($columns as $column) {
				$do_columns[] = $column;
			}
			if(sizeof($do_columns) < 1) $notifier->addError(4, $do_action);

			//Zu bearbeitende Einträge
			$do_ids = array();
			foreach($_POST["chk"] as $c_i => $c) {
				if($c) {
					if(FALSE === ($edit_id = format_userinput($c_i, "uint", TRUE))) {
						trigger_error("Not allowed multiedit_id: ".$c_i, E_USER_ERROR);
					}
					$do_ids[] = $edit_id;
				}
			}
			if(sizeof($do_ids) < 1) $notifier->addError(5, $do_action);

			//Daten für Formular-Aufruf vorbereiten
			$order = 'ORDER BY name ASC';  //Default
			if(!$notifier->hasErrors()) {
				if(is_string($_SESSION['sort_kg'])) $order = "ORDER BY ".$_SESSION["sort_kg"]." ".$_SESSION["sort_kg_order"];
				$_SESSION["show_back"] = $_SESSION["show"];
				$_SESSION["show"] = "multiedit_kg";
			}
		}

		$onload_code = "form_set_first_input();".$onload_code;
	break;



	case "submit_multiedit":
		if($_SESSION["show"] == "multiedit") {
			kota_submit_multiedit(2);
		} else if($_SESSION["show"] == "multiedit_kg") {
			if($access['kg']['MAX'] < 3) break;
			kota_submit_multiedit(2);
		}
		if(!$notifier->hasErrors()) $notifier->addInfo(11, $do_action);
		$_SESSION["show"] = $_SESSION["show_back"] ? $_SESSION["show_back"] : "show_all";
	break;




	//Löschen
	case "delete_person":
		$del_id = format_userinput($_POST["id"], "uint", TRUE);
		if(!$del_id) break;
		if($access['leute']['MAX'] < 3) break;

		$ok = ko_leute_delete_person($del_id);

		if($ok) {
			$notifier->addInfo(3, $do_action);
		} else {
			$notifier->addError(4, $do_action);
		}
	break;


	case 'undelete_person':
		$del_id = format_userinput($_POST['id'], 'uint', TRUE);
		if(!$del_id) break;
		if(!($access['leute']['ALL'] > 2 || $access['leute'][$del_id] > 2)) break;

		ko_get_person_by_id($del_id, $del_person);
		if($del_person['deleted'] != 1) break;

		ko_save_leute_changes($del_id, $del_person);
		ko_log_diff('undelete_person', $del_person);
		db_update_data('ko_leute', "WHERE `id` = '$del_id'", array('deleted' => '0'));

		//set group datafields to undeleted
		db_update_data('ko_groups_datafields_data', "WHERE `person_id` = '$del_id'", array('deleted' => '0'));

		//re-subscribe to ezmlm
		if(defined('EXPORT2EZMLM') && EXPORT2EZMLM) {
			foreach(explode(',', $del_person['groups']) as $grp) {
				$gid = ko_groups_decode($grp, 'group_id');
				if($all_groups[$gid]['ezmlm_list']) ko_ezmlm_subscribe($all_groups[$gid]['ezmlm_list'], $all_groups[$gid]['ezmlm_moderator'], $del_person['email']);
			}
		}

		//add person to LDAP
		if(ko_do_ldap()) {
			$ldap = ko_ldap_connect();
			ko_ldap_add_person($ldap, $del_person, $del_id);
			ko_ldap_close($ldap);
		}

		//Update group counts for all assigned groups
		foreach(explode(',', $del_person['groups']) as $fullgid) {
			$group = ko_groups_decode($fullgid, 'group');
			if(!$group['maxcount']) continue;
			ko_update_group_count($group['id'], $group['count_role']);
		}

		$notifier->addInfo(20, $do_action);
	break;




	//Mutationen
	case "submit_mutation":
		if($access['leute']['MAX'] < 2) break;
		if(!$_POST["aa_id"]) break;

		//Initialisierung
		$do_ldap = ko_do_ldap();

		$mod_aa_id = format_userinput($_POST["aa_id"], "uint");

		//Mod- und alte Daten auslesen
		ko_get_mod_leute($_mod_p, $mod_aa_id);
		$mod_p = $_mod_p[$mod_aa_id];

		if($access['leute']['ALL'] < 2 && ($access['leute'][$mod_p['_leute_id']] < 2 || $mod_p['_leute_id'] < 1)) break;


		if($mod_p["_leute_id"] == -1) { //Neu:
			$new_entry = TRUE;
			$old_p = array();
		} else {
			$new_entry = FALSE;
			ko_get_person_by_id($mod_p["_leute_id"], $old_p);
		}

		$found = FALSE;
		foreach($_POST["chk_".$mod_aa_id] as $field => $field_state) {
			if ($field_state == "0") continue;
			if ($field == 'decouple_from_family') continue;
			$data[$field] = format_userinput($_POST["txt_".$mod_aa_id][$field], "text");
			$found = TRUE;
			//Familien-Daten:
			if(in_array($field, $COLS_LEUTE_UND_FAMILIE) && $old_p["famid"] != 0) {
				$fam_data[$field] = format_userinput($_POST["txt_".$mod_aa_id][$field], "text");
			}
			//ezmlm: change email in mailinglist address if email has changed
			if($field == "email" && !$new_entry && defined("EXPORT2EZMLM") && EXPORT2EZMLM) {
				if($old_p["email"] != $data["email"] && check_email($data[$field])) {  //Check for changed valid email
					foreach(explode(",", $old_p["groups"]) as $group) {  //Check this user's groups for one with an ML assigned
						$gid = ko_groups_decode($group, "group_id");
						if($all_groups[$gid]["ezmlm_list"]) {
							//Un- and resubscribe
							ko_ezmlm_unsubscribe($all_groups[$gid]["ezmlm_list"], $all_groups[$gid]["ezmlm_moderator"], $old_p["email"]);
							ko_ezmlm_subscribe($all_groups[$gid]["ezmlm_list"], $all_groups[$gid]["ezmlm_moderator"], $data["email"]);
						}
					}
				}
			}
		}//foreach(chk_id as field)

		//mindestens eine markierte CheckBox gefunden
		if($found) {
			// delete family link if decoupled (and delete empty family)
			$doUpdateFamily = false;
			if($old_p["famid"] != 0 && $_POST["chk_".$mod_aa_id]['decouple_from_family']) {
				$data['famid'] = 0;
				$data['kinder'] = 0;
				$num = ko_get_personen_by_familie($old_p["famid"], $asdf);
				if($num <= 1) {
					db_update_data('ko_leute', "WHERE `famid` = '".$old_p["famid"]."'", array('famid' => '0', 'kinder' => '0'));
					db_delete_data('ko_familie', "WHERE `famid` = '".$old_p["famid"]."'");
				}
				else {
					$doUpdateFamily = true;
				}
			}

			//Add lastchange
			$data["lastchange"] = date("Y-m-d H:i:s");

			if($new_entry) { //Neu:
				$data["crdate"] = date("Y-m-d H:i:s");
				$data["cruserid"] = $_SESSION["ses_userid"];
				$q_id = db_insert_data("ko_leute", $data);
			} else {
				//Save changes for versioning
				ko_save_leute_changes($mod_p["_leute_id"]);
				//Update record with new data
				db_update_data("ko_leute", "WHERE `id`='".$mod_p["_leute_id"]."'", $data);
			}

			//Familien-Daten aktualisieren, falls die person nicht von der familie entkuppelt wurde
			if($old_p["famid"] != 0 && !$_POST["chk_".$mod_aa_id]['decouple_from_family']) {
				ko_update_familie($old_p["famid"], $fam_data);
			}

			// if person was decoupled from family and old family still has members, update their family specific fields
			if ($doUpdateFamily) {
				ko_update_leute_in_familie($old_p["famid"]);
			}

			//In LDAP speichern
			if($do_ldap) {
				$ldap = ko_ldap_connect();
				if($new_entry) {  //Add new entry
					ko_get_person_by_id($q_id, $person);
					ko_ldap_add_person($ldap, $person, $q_id);
				} else {  //Edit existing entry
					ko_get_person_by_id($mod_p['_leute_id'], $person);
					ko_ldap_add_person($ldap, $person, $mod_p['_leute_id'], TRUE);
				}
				ko_ldap_close($ldap);
			}//if(do_ldap)

			ko_log_diff("aa_insert", $data, $old_p);
		}

		//Mod-Eintrag löschen
		db_delete_data("ko_leute_mod", "WHERE `_id`='$mod_aa_id'");

		if($_SESSION['show_back']) $_SESSION['show'] = $_SESSION['show_back'];
	break;


	case 'submit_del_mutation':
		if($access['leute']['MAX'] < 2) break;
		if(!$_POST['aa_id']) break;

		$aa_id = format_userinput($_POST['aa_id'], 'uint');

		//Check for access
		ko_get_mod_leute($_mod_p, $aa_id);
		$mod_p = $_mod_p[$aa_id];
		if($access['leute']['ALL'] < 2 && ($access['leute'][$mod_p['_leute_id']] < 2 || $mod_p['_leute_id'] < 1)) break;

		db_delete_data('ko_leute_mod', "WHERE `_id`='$aa_id'");

		//Add log entry with all data
		ko_log_diff('aa_del', $mod_p);

		if($_SESSION['show_back']) $_SESSION['show'] = $_SESSION['show_back'];
	break;




	//Group subscriptions
	case "submit_gs":  //Use found person from db
	case "submit_gs_aa":  //Use found person from db and add change
	case "submit_gs_new_person":  //New person to be added to db
	case "submit_gs_ps":  //Person from peoplesearch
	case "submit_gs_ps_aa":  //Person from peoplesearch and add change
		if($access['leute']['ALL'] < 4 && !($access['leute']['GS'] && $access['groups']['MAX'] > 1)) break;
		if(!$_POST["_id"]) break;

		//Initialisierung
		$do_ldap = ko_do_ldap();

		$_id = format_userinput($_POST["_id"], "uint");

		//get subscription-data
		ko_get_groupsubscriptions($_p, $_id); $_p = $_p[$_id];
		list($gid, $rid) = explode(":", $_p["_group_id"]);
		$gid = format_userinput($gid, "uint");
		//get full id for this group - including current motherline
		ko_get_groups($all_groups);
		$motherline = ko_groups_get_motherline($gid, $all_groups);
		$a_save_group_id = array();
		foreach($motherline as $m) $a_save_group_id[] = "g".$m;
		$a_save_group_id[] = "g".$gid;
		$save_group_id = implode(":", $a_save_group_id);

		//Get role from form
		if(isset($_POST['gs_role_'.$_id])) {
			$role_id = format_userinput($_POST['gs_role_'.$_id], 'uint');
			if($role_id && in_array($role_id, explode(',', $all_groups[$gid]['roles']))) {
				$save_group_id = $save_group_id.':r'.$role_id;
			}
		} else {
			if($rid) $save_group_id = $save_group_id.":".$rid;
		}

		//Get additional groups
		$additional_groups = array();
		$agroup_ids = unserialize($_p['_additional_group_ids']);

		$additional_gdf = array();
		$agdfs = unserialize($_p['_additional_group_datafields']);

		if(is_array($agroup_ids) && sizeof($agroup_ids) > 0) {
			$sel_agroup_ids = explode(',', $_POST['gs_agroups_'.$_id]);
			foreach($agroup_ids as $_agid => $sel) {
				list($agid, $arid) = explode(":", $_agid);
				$agid = format_userinput($agid, "uint");
				if(!$agid) continue;
				//Check for selection
				if(!in_array($_agid, $sel_agroup_ids)) continue;

				//get full id for this group - including current motherline
				$motherline = ko_groups_get_motherline($agid, $all_groups);
				$a_save_group_id = array();
				foreach($motherline as $m) $a_save_group_id[] = 'g'.$m;
				$a_save_group_id[] = 'g'.$agid;
				$add_save_group_id = implode(':', $a_save_group_id);
				//Add role
				if($arid) $add_save_group_id .= ':'.$arid;

				$additional_groups[] = $add_save_group_id;

				//Additional group datafields
				if(is_array($agdfs[$agid])) {
					foreach($agdfs[$agid] as $dfid => $dfv) {
						$additional_gdf[] = array('group_id' => $agid, 'datafield_id' => $dfid, 'value' => $dfv);
					}
				}
				
			}
		}


		//get person-data
		if($do_action == "submit_gs_new_person") {
			//insert data as new person
			$new_person = $_p;
			//Unset fields not to be stored in ko_leute
			foreach($new_person as $k => $v) {
				if(substr($k, 0, 1) == "_") unset($new_person[$k]);
			}

			//Set salutation from gender or vice versa
			if($new_person['anrede'] != '' && $new_person['geschlecht'] == '') {
				$new_person['geschlecht'] = $LEUTE_TITLE_TO_SEX[$_SESSION['lang']][$new_person['anrede']];

			} else if($new_person['geschlecht'] != '' && $new_person['anrede'] == '') {
				$title2GeschlechtMap = $LEUTE_TITLE_TO_SEX[$_SESSION['lang']];
				$geschlecht2TitleMap = array();
				foreach ($title2GeschlechtMap as $t => $s) {
					if(!isset($geschlecht2TitleMap[$s])) $geschlecht2TitleMap[$s] = $t;
				}
				$new_person['anrede'] = $geschlecht2TitleMap[$new_person['geschlecht']];
			}

			$new_person["crdate"] = date("Y-m-d H:i:s");
			$new_person["cruserid"] = $_SESSION["ses_userid"];
			$lid = db_insert_data("ko_leute", $new_person);
			$lids = array($lid);
		} else if($do_action == "submit_gs_ps" || $do_action == "submit_gs_ps_aa") {
			$lids = format_userinput($_POST["ps_".$_id], "intlist");
			if(!$lids) $notifier->addError(19, $do_action);
			else $lids = explode(",", $lids);
		} else {
			$lid = format_userinput($_POST["lid"], "uint");
			if(!$lid) $notifier->addError(19, $do_action);
			else $lids = array($lid);
		}
		if($notifier->hasErrors()) break;

		foreach($lids as $lid) {
			ko_get_person_by_id($lid, $p);

			//update group-data of person
			if($p['groups'] != '') {
				$groups = explode(',', $p['groups']);
			} else {
				$groups = array();
			}

			$new_groups = array_merge(array($save_group_id), $additional_groups);
			$store = FALSE;
			foreach($new_groups as $k => $v) {
				if(!$v) {
					unset($new_groups[$k]);
					continue;
				}
				if(!in_array($v, $groups)) {
					$store = TRUE;
				} else {
					unset($new_groups[$k]);
				}
			}

			if($store) {
				//Save changes for versioning (but not for new person)
				if($do_action != "submit_gs_new_person") {
					ko_save_leute_changes($lid);
				}

				//Add linked groups for all groups
				foreach($new_groups as $fullGid) {
					$new_gid = ko_groups_decode($fullGid, 'group_id');
					if(!$all_groups[$new_gid]['linked_group']) continue;

					$linked_group = $all_groups[$new_gid]['linked_group'];
					$motherline = ko_groups_get_motherline($linked_group, $all_groups);
					$a_save_group_id = array();
					foreach($motherline as $m) $a_save_group_id[] = 'g'.$m;
					$a_save_group_id[] = 'g'.$linked_group;
					$add_save_group_id = implode(':', $a_save_group_id);
					$new_groups[] = $add_save_group_id;
				}


				$data = array("groups" => implode(",", array_merge($groups, $new_groups)));
				db_update_data("ko_leute", "WHERE `id`='$lid'", $data);

				//Update group count
				foreach($new_groups as $new_gid) {
					$new_gid = ko_groups_decode($new_gid, 'group_id');
					ko_update_group_count($new_gid, $all_groups[$new_gid]['count_role']);

					//Add ezmlm subscription if set for this group
					if(defined("EXPORT2EZMLM") && EXPORT2EZMLM && $all_groups[$new_gid]["ezmlm_list"] != "") {
						ko_get_person_by_id($lid, $np);
						ko_ezmlm_subscribe($all_groups[$new_gid]["ezmlm_list"], $all_groups[$new_gid]["ezmlm_moderator"], $np["email"]);
					}
				}
			}

			//insert group datafields data
			foreach(explode(",", $all_groups[$gid]["datafields"]) as $fid) {
				$value = $_POST["group_datafields"][$_id][$gid][$fid];
				db_delete_data("ko_groups_datafields_data", "WHERE `datafield_id` = '$fid' AND `person_id` = '$lid' AND `group_id` = '$gid'");
				db_insert_data("ko_groups_datafields_data", array("group_id" => $gid, "person_id" => $lid, "datafield_id" => $fid, "value" => $value));
			}

			//Additional group datafields
			if(sizeof($additional_gdf) > 0) {
				foreach($additional_gdf as $agdf) {
					if(!$agdf['group_id'] || !$agdf['datafield_id']) continue;
					$agdf['person_id'] = $lid;
					db_insert_data('ko_groups_datafields_data', $agdf);
				}
			}

			//Set default tracking data
			if(in_array('tracking', $MODULES)) {
				$tracking = db_select_data('ko_tracking', "WHERE `filter` REGEXP '^g".$gid."[:r0-9]*'", '*', '', 'LIMIT 0,1', TRUE);
				if(isset($tracking['id'])) {
					include_once($ko_path.'tracking/inc/tracking.inc');
					ko_tracking_set_default($tracking['id'], $lid);
				}
			}

			//Handle address changes
			if($do_action == "submit_gs_aa" || $do_action == "submit_gs_ps_aa") {
				$aa = $_p;
				unset($aa["_id"]); unset($aa["_group_id"]); unset($aa["_group_datafields"]);
				$aa["_leute_id"] = $lid;
				db_insert_data("ko_leute_mod", $aa);
			}
		}//foreach(lids as lid)

		//Delete group subscription
		db_delete_data("ko_leute_mod", "WHERE `_id`='$_id'");

		//Store deleted data in log
		$logdata = '';
		foreach($_p as $k => $v) {
			if($k && $v != '') $logdata .= $k.': '.$v.', ';
		}
		if($logdata != '') $logdata = substr($logdata, 0, -2);


		if($do_action == "submit_gs_aa" || $do_action == "submit_gs_ps_aa") {
			//Display list of changes with back link to group subscriptions
			if($access['leute']['ALL'] > 3) {
				$_SESSION["show_back"] = "groupsubscriptions";
				$_SESSION["show"] = "mutationsliste";
			}
		} else if($do_action == "submit_gs_new_person") {
			//Add LDAP entry
			if($do_ldap) {
				ko_get_person_by_id($lid, $person);
				$ldap = ko_ldap_connect();
				ko_ldap_add_person($ldap, $person, $lid);
				ko_ldap_close($ldap);
			}
			//Display edit form for the new person
			$_POST["id"] = $lid;
			$_SESSION['show_back'] = 'groupsubscriptions';
			$_SESSION["show"] = "edit_person";
			//Don't show "save as new" button, as this might confuse people. Clicking it will duplicate the new address
			$hide_save_as_new = TRUE;
		}

		ko_log("groupsubscription", $p["vorname"]." " .$p["nachname"].": ".$all_groups[$gid]["name"].' - '.$logdata);
	break;



	case "submit_gs_delete":
		if($access['leute']['ALL'] < 4 && !($access['leute']['GS'] && $access['groups']['MAX'] > 1)) break;
		if(!$_POST['_id']) break;

		$_id = format_userinput($_POST["_id"], "uint");
		ko_get_groupsubscriptions($_p, $_id); $_p = $_p[$_id];
		//Mod-Eintrag löschen
		db_delete_data("ko_leute_mod", "WHERE `_id`='$_id'");

		//Store deleted data in log
		$logdata = '';
		foreach($_p as $k => $v) {
			if($k && $v != '') $logdata .= $k.': '.$v.', ';
		}
		if($logdata != '') $logdata = substr($logdata, 0, -2);

		ko_log("del_groupsubscription", $_p["vorname"]." " .$_p["nachname"].": ".$_p["_group_id"].' - '.$logdata);
	break;

	// leute revisions
	case 'revisions':
		if ($access['leute']['MAX'] < 4) break;
		$_SESSION['show'] = 'leute_revisions';
	break;

	case 'submit_leute_revision':
		$id = format_userinput($_POST['leute_revision_id'], 'uint');
		if (!$id) break;
		ko_get_leute_revisions($revision, TRUE, $id);
		if (!$revision) break;
		if ($access['leute']['ALL'] < 4 && $access['leute'][$revision['leute_id']] < 4) break;

		$pid = format_userinput($_POST['leute_revision_person_id'], 'alphanum');

		$addToSelectedPerson = format_userinput($_POST['add_to_selected_person_' . $id], 'uint');

		if ($pid == 'selected' && !$addToSelectedPerson) {
			$notifier->addError(22);
			break;
		}

		if ($pid == 'selected') {
			$mergeId = $addToSelectedPerson;
		}
		else {
			$mergeId = $pid;
		}
		ko_get_person_by_id($mergeId, $pData);
		if (!$pData['id']) {
			$notifier->addError(23);
			break;
		}

		if ($mergeId == $revision['leute_id']) {
			$notifier->addError(25);
			break;
		}

		// set hidden = 0 if one of them is not hidden
		ko_get_person_by_id($revision['leute_id'], $revisionPerson);
		if (!($revisionPerson['hidden'] && $pData['hidden'])) {
			foreach (array($revisionPerson, $pData) as $pp) {
				if ($pp['hidden']) {
					db_update_data('ko_leute', "WHERE `id` = '{$pp['id']}'", array('hidden' => 0));
				}
			}
		}

		//Set address ID to be kept. If force_keep is set on revision then use the new address,
		// otherwise (default) keep the older address from the database.
		// Keeping the new address is needed e.g. for import scripts, where the new address can hold
		// a newer import id (e.g. Wiederzuzüger)). Used e.g. in plugin gemowin_import.
		// For group subscriptions one usually wants to keep the old address (default)
		$keepId = $revision['force_keep'] ? $revision['leute_id'] : $mergeId;

		// perform merging of addresses
		ko_leute_merge_ids(array($mergeId, $revision['leute_id']), $showMutations, NULL, $keepId);

		db_delete_data('ko_leute_revisions', 'where `id` = ' . $id);
		ko_log_diff($do_action, $revision);
		$notifier->addInfo(17);

		//Show moderations
		if($showMutations) {
			$_SESSION['show_back'] = 'leute_revisions';
			$_SESSION['show'] = 'mutationsliste';
		} else {
			$_SESSION['show'] = 'leute_revisions';
		}
	break;

	case 'submit_del_leute_revision':
		$id = format_userinput($_POST['leute_revision_id'], 'uint');
		if (!$id) break;
		ko_get_leute_revisions($revision, FALSE, $id);
		if (!$revision) break;
		if ($access['leute']['ALL'] < 4 && $access['leute'][$revision['leute_id']] < 4) break;

		db_delete_data('ko_leute_revisions', 'where `id` = ' . $id);
		ko_log_diff($do_action, $revision);
		$notifier->addInfo(17);
	break;


	case 'submit_del_leute_revision_address':
		$id = format_userinput($_POST['leute_revision_id'], 'uint');
		if (!$id) break;
		ko_get_leute_revisions($revision, FALSE, $id);
		if (!$revision) break;
		if ($access['leute']['ALL'] < 4 && $access['leute'][$revision['leute_id']] < 4) break;

		ko_leute_delete_person($revision['leute_id']);
		db_delete_data('ko_leute_revisions', 'where `id` = ' . $id);
		$notifier->addInfo(3);
	break;


	case 'export_details':
		$exportName = $_POST['sel_detail_export'];
		$exportIds = $_SESSION['export_ids'];

		$exports = ko_leute_get_detail_exports();
		$export = NULL;
		foreach ($exports as $e) {
			if ($e['name'] == $exportName) {
				$export = $e;
				break;
			}
		}

		$fcn = NULL;
		if ($export === NULL) $notifier->addError(31);
		else {
			$fcnSuffix = $export['fcn_suffix'] ? $export['fcn_suffix'] : $exportName;
			$fcn1 = "ko_leute_export_details_{$fcnSuffix}";
			$fcn2 = "my_leute_export_details_{$fcnSuffix}";

			if (function_exists($fcn1)) {
				$fcn = $fcn1;
			} else if (function_exists($fcn2)) {
				$fcn = $fcn2;
			} else {
				$notifier->addError(31);
			}
		}

		if ($fcn) {
			$filename = call_user_func_array($fcn, array($export, $exportIds));
			if (($pos = strpos($filename, 'download/')) !== FALSE) $filename = substr($filename, $pos);
		} else {
			// already added error to notifier
		}

		if (!$notifier->hasErrors()) {
			ko_log('leute_detailed_export', print_r(array('export' => $export, 'ids' => $exportIds), TRUE), $logId);
			ko_create_crm_contact_from_post(TRUE, array('leute_ids' => implode(',', array_unique($exportIds)), 'reference' => 'ko_log:'.$logId));

			$onload_code = "ko_popup('{$ko_path}download.php?action=file&amp;file={$filename}');";
			$_SESSION['show'] = $_SESSION['show_back']?$_SESSION['show_back']:'show_all';
		}
	break;


	//Aktionen
	case "leute_action":
		set_time_limit(0);
		$fam = array();
		$person = array();
		$mapLeuteDatenOptions = array();

		$leute_col_name = ko_get_leute_col_name(FALSE, TRUE);

		// transfer information from $_GET to $_POST
		foreach (array('sel_cols', 'sel_auswahl', 'ids', 'id') as $tf) {
			if (isset($_GET[$tf]) && !isset($_POST[$tf])) $_POST[$tf] = $_GET[$tf];
		}

		//Spalten:
		switch($_POST["sel_cols"]) {
			case "alle":
				$xls_cols = array();
				foreach($leute_col_name as $c_i => $c) {
					$xls_cols[] = $c_i;
				}
			break;

			case "angezeigte":
				if($_SESSION["show"] == "show_adressliste")
					$xls_cols = $_SESSION["show_adressliste_cols"];
				else
					$xls_cols = $_SESSION["show_leute_cols"];
			break;
			default:
				if(substr($_POST["sel_cols"], 0, 4) == "set_") {
					$setname = substr($_POST["sel_cols"], 4);
					if(substr($setname, 0, 3) == '@G@') $set = ko_get_userpref('-1', substr($setname, 3), "leute_itemset");
					else $set = ko_get_userpref($_SESSION["ses_userid"], $setname, "leute_itemset");
					$xls_cols = explode(",", $set[0]["value"]);
				}
		}//switch(sel_cols)

		if($xls_cols == "") $notifier->addError(6, $do_action);

		//Birthday list
		if($_SESSION['show'] == 'geburtstagsliste') {
			if(!in_array('geburtsdatum', $xls_cols)) $xls_cols[] = 'geburtsdatum';
			if(!in_array('alter', $xls_cols)) $xls_cols[] = 'alter';
			if(!in_array('deadline', $xls_cols)) $xls_cols[] = 'deadline';
			$leute_col_name['deadline'] = getLL('leute_birthday_list_header_deadline');
			$leute_col_name['alter'] = getLL('leute_birthday_list_header_age');
			$leute_col_name_add['deadline'] = getLL('leute_birthday_list_header_deadline');
			$leute_col_name_add['alter'] = getLL('leute_birthday_list_header_age');
		}

		//Zeilen:
		$pidlist = $famlist = array();
		if(substr($_POST["sel_auswahl"], 0, 4) == "alle" && $_SESSION["show"] == "show_all") {
			$mode = substr($_POST["sel_auswahl"], 4);

			apply_leute_filter($_SESSION["filter"], $z_where, ($access['leute']['ALL'] < 1));
			ko_get_leute($es, $z_where);
			if(TRUE === ko_manual_sorting($_SESSION["sort_leute"])) {
				$es = ko_leute_sort($es, $_SESSION["sort_leute"], $_SESSION["sort_leute_order"], TRUE);
			}
		}
		//Birthday list
		else if(($_POST['sel_auswahl'] == 'markierte' || substr($_POST['sel_auswahl'], 0, 4) == 'alle') && $_SESSION['show'] == 'geburtstagsliste') {
			$mode = 'p';
			$deadline_plus = ko_get_userpref($_SESSION['ses_userid'], 'geburtstagsliste_deadline_plus');
			$deadline_minus = ko_get_userpref($_SESSION['ses_userid'], 'geburtstagsliste_deadline_minus');
			if(!$deadline_plus) $deadline_plus = 21;
			if(!$deadline_minus) $deadline_minus = 7;

			$cols = '*, ( YEAR( CURDATE() ) - YEAR(`geburtsdatum` ) ) AS `alter`, TO_DAYS(geburtsdatum + INTERVAL (YEAR( CURDATE() ) - YEAR(geburtsdatum)) YEAR) - TO_DAYS(CURDATE()) AS deadline';
			$where = " AND deleted = '0' ".ko_get_leute_hidden_sql()." AND TO_DAYS(geburtsdatum + INTERVAL (YEAR( CURDATE() ) - YEAR(geburtsdatum)) YEAR) - TO_DAYS(CURDATE()) <= $deadline_plus AND TO_DAYS(geburtsdatum + INTERVAL (YEAR( CURDATE() ) - YEAR(geburtsdatum)) YEAR) - TO_DAYS(CURDATE()) >= -$deadline_minus ".ko_get_birthday_filter();
			if($_POST['sel_auswahl'] == 'markierte' && isset($_POST['ids'])) {
				$pids = array();
				foreach(explode(',', $_POST['ids']) as $c_i => $c) {
					if($c) $pids[] = format_userinput($c_i, 'uint');
				}
				$where .= ' AND `id` IN (\''.implode("','", $pids)."') ";
			}

			$es = db_select_data('ko_leute', 'WHERE 1=1 '.$where, $cols, 'ORDER BY `deadline` ASC');
		}
		//Use mylist
		else if(substr($_POST["sel_auswahl"], 0, 4) == "alle") {
			$mode = substr($_POST["sel_auswahl"], 4);
			$pidlist = $_SESSION["my_list"];
		}
		else if($_POST["sel_auswahl"] == "markierte") {
			$mode = "f";
			$clear_famid_for_pidlist = TRUE;
			if(isset($_POST["ids"])) {
				foreach(explode(',', $_POST["ids"]) as $c) {
					if($c) $pidlist[] = format_userinput($c, "uint");
				}
			}

			//TODO: famchk in POST übergeben
			if(isset($_POST["famchk"])) {
				foreach($_POST["famchk"] as $c_i => $c) {
					if($c) $famlist[] = format_userinput($c_i, "uint");
				}
				$famlist = array_unique($famlist);
			}
		}//markierte
		else if($_POST["sel_auswahl"] == "markiertef") {
			$mode = "f";
			if(isset($_POST["ids"])) {
				foreach(explode(',', $_POST["ids"]) as $c) {
					if($c) $pidlist[] = format_userinput($c, "uint");
				}
			}
		}//markiertef
		else if($_POST["sel_auswahl"] == "markierteFam2") {
			$mode = "Fam2";
			if(isset($_POST["ids"])) {
				foreach(explode(',', $_POST["ids"]) as $c) {
					if($c) $pidlist[] = format_userinput($c, "uint");
				}
			}
		}//markierteFam2


		//Clear famid if selected persons should get exported as persons
		if($clear_famid_for_pidlist) $clear_pidlist = $pidlist;
		//get family member for all selected families
		if(sizeof($famlist) > 0) {
			foreach($famlist as $famid) {
				$members = db_select_data('ko_leute', ("WHERE `famid` = '$famid' AND `famfunction` IN ('husband', 'wife') AND `deleted` = '0'".ko_get_leute_hidden_sql()), 'id');
				foreach($members as $member) {
					if($member['id']) $pidlist[] = $member['id'];
				}
			}
		}
		if(sizeof($pidlist) > 0) {
			//Get people as defined in pidlist
			$in = array();
			foreach($pidlist as $pid) {
				if(!$pid) continue;
				$in[] = intval($pid);
			}
			if(sizeof($in) > 0) {
				$z_where = "AND `id` IN (".implode(',', $in).") AND `deleted` = '0'".ko_get_leute_hidden_sql();
				ko_get_leute($es, $z_where);
				if(TRUE === ko_manual_sorting($_SESSION["sort_leute"])) {
					$es = ko_leute_sort($es, $_SESSION["sort_leute"], $_SESSION["sort_leute_order"], TRUE);
				}
			}
		}
		$allExportIds = array_keys($es);

		//Clear famid if selected persons should get exported as persons
		if($clear_famid_for_pidlist) {
			foreach($clear_pidlist as $pid) {
				$es[$pid]["_famid"] = $es[$pid]["famid"];
				$es[$pid]["famid"] = "";
			}
		}

		$restricted_leute_ids = ko_apply_leute_information_lock();
		if (!empty($restricted_leute_ids)) {
			foreach($restricted_leute_ids AS $restricted_leute_id) {
				unset($es[$restricted_leute_id]);
				$notifier->addTextWarning(getLL("leute_notice_export_information_lock"));
			}
		}

		if(sizeof($es) == 0) $notifier->addError(5, $do_action);
		if($notifier->hasErrors()) break;

		//Keep list of addresses before removing not needed addresses because of family mergings
		$orig_es = $es;


		ko_get_familien($families);
		$all_datafields = db_select_data("ko_groups_datafields", "WHERE 1=1", "*");

		//Preprocess data if alleFam2
		//Unset famid for people where only one member of their family has been found
		//And unset all members of a family except for husband or wife or the first child
		$fam = array();
		foreach($es as $pid => $p) {
			if(!$p["famid"]) continue;
			$fam[$p["famid"]][] = $pid;  //Save all pids for each family
		}
		if($mode == "Fam2") {
			//Find
			foreach($fam as $famid => $pids) {
				//Find families with only one member in filtered people
				if(sizeof($pids) == 1) {
					foreach($pids as $pid) $es[$pid]["famid"] = "";  //And unset this famid, so it will be exported as person
				}
				//Export as family if more than one member has been found
				else if(sizeof($pids) > 1) {
					$famroles = array(); $keep = "";
					foreach($pids as $pid) {
						$famroles[] = $es[$pid]["famfunction"];
					}
					if(in_array("husband", $famroles)) $keep = "husband";
					else if(in_array("wife", $famroles)) $keep = "wife";
					else $keep = "";
					$done = FALSE;
					foreach($pids as $pid) {
						if( ($keep == "" && $done)
								|| ($keep != "" && $es[$pid]["famfunction"] != $keep)
							) {
							unset($es[$pid]);
							$done = TRUE;
						}
					}
				}
			}//foreach(fam as famid => pids)
		}//if(alleFam2)

		//Household export and use parents' firstnames in export
		//  then get parents and include them in the export list
		else if($mode == 'f' && ko_get_userpref($_SESSION['ses_userid'], 'leute_force_family_firstname') == 1) {
			foreach($fam as $famid => $pids) {
				$parents = (array)db_select_data('ko_leute', "WHERE `famid` = '$famid' AND `famfunction` IN ('husband', 'wife') AND `deleted` = '0'".ko_get_leute_hidden_sql());
				foreach($parents as $parent) {
					if(!in_array($parent['id'], $pids)) {
						$es[$parent['id']] = $parent;
						$orig_es[$parent['id']] = $parent;
						$fam[$famid][] = $parent['id'];
					}
				}
			}
		}

		//Force rectype
		if($_POST['sel_rectype'] && (in_array($_POST['sel_rectype'], array_keys($RECTYPES)) || $_POST['sel_rectype'] == '_default')) $force_rectype = $_POST['sel_rectype'];
		else $force_rectype = '';

		//Apply rectype here to be able to add more addresses to $es if needed
		foreach($es as $pid => $p) {
			//Use address as given in rectype (only apply if not _default was selected, which keeps the default address)
			if($force_rectype != '_default') {
				$p = $es[$pid] = ko_apply_rectype($p, $force_rectype, $addp);
				if(sizeof($addp) > 0) {
					$new = array();
					foreach($es as $k => $v) {
						$new[$k] = $v;
						if($k == $pid) {
							foreach($addp as $addk => $add) {
								$new[$addk] = $add;
							}
						}
					}
					$es = $new;
					unset($new);
				}
			}
		}

		// create crm entries
		$mapLeuteDatenOptions['crmContactId'] = ko_create_crm_contact_from_post(TRUE, array('leute_ids' => implode(',', $_POST['leute_ids'])));
		if (in_array($_POST["id"], array('xls_settings', 'excel', 'csv'))) $mapLeuteDatenOptions['kota_process_modes'] = 'xls,list';
		if (in_array($_POST["id"], array('mailmerge', 'etiketten', 'etiketten_settings'))) $mapLeuteDatenOptions['kota_process_modes'] = 'pdf,list';

		$data = array();
		foreach($es as $pid => $p) {
			if(($access['leute']['ALL'] < 1 && $access['leute'][$pid] < 1) || !$pid) continue;

			list($addToExport, $isFam) = ko_leute_process_person_for_export($p, $orig_es, $done_fam, $fam, $families, $xls_cols, $mode);
			if (!$addToExport) {
				unset($es[$p['id']]);
				continue;
			}

			if (!$isFam) {
				unset($cols_no_map);
			} else {
        $cols_no_map = array('MODULEsalutation_formal', 'MODULEsalutation_informal');
			}

			$es[$pid] = $p;

			//Restore famid if selected persons should get exported as persons (needed in map_leute_daten())
			if($clear_famid_for_pidlist) {
				$p["famid"] = $p["_famid"];
				unset($p["_famid"]);
			}

			foreach($xls_cols as $c) {
				if(!$leute_col_name[$c]) continue;

				//Check for columns that don't need any more mapping (may be set in plugin above)
				if(in_array($c, $cols_no_map)) {
					$value = $p[$c];
				} else {
					$value = map_leute_daten($p[$c], $c, $p, $all_datafields, FALSE, $mapLeuteDatenOptions);
				}

				if(is_array($value)) {  //group with datafields, so more than one column has to be added
					$n = 0;
					foreach($value as $v) $data[$p['id']][$c.'.'.($n++)] = strip_tags($v);
				} else {
					// we do need <a> tags in subscription form links
					if(substr($c,0,19) == 'MODULEsubscription_') {
						$data[$p['id']][$c] = $value;
					} else {
						$data[$p['id']][$c] = strip_tags($value);
					}
				}
			}//foreach(xls_cols as col)
		}//foreach(es as pid => p)

		if(sizeof($data) == 0) $notifier->addError(5, $do_action);

		if(!$notifier->hasErrors()) {
			switch($_POST["id"]) {
				case 'pdf_settings':
					$layout_id = format_userinput($_POST["pdf_layout_id"], "uint");
					if(!$layout_id) break;

					$_SESSION['post_data'] = $_POST;
					$_SESSION['post_get'] = $_GET;

					/*$_SESSION['export_data'] = $es;
					$_SESSION['export_cols'] = $xls_cols;*/

					$_SESSION["show_back"] = $_SESSION["show"];
					$_SESSION["show"] = "export_pdf";
				break;

				case 'details_settings':
					if ($access['leute']['MAX'] == 0) break;
					$ids = $allExportIds;

					foreach ($ids as $k => $id) {
						if ($access['leute']['ALL'] < 1 && $access['leute'][$id] < 1) unset($ids[$k]);
					}

					$_SESSION['export_ids'] = $ids;
					$_SESSION['show_back'] = $_SESSION['show'];
					$_SESSION['show'] = 'export_details_settings';
				break;

				case "xls_settings":
					$_SESSION['leute_export_xls_post'] = $_POST;
					$_SESSION['leute_export_xls_post']['session_show'] = $_SESSION['show'];
					$_SESSION['leute_export_xls_post']['leute_ids'] = array_keys($es);
					$_SESSION['show'] = 'xls_settings';
				break;

				case "excel":
				case 'csv':
					$leute_col_name = ko_get_leute_col_name(FALSE, TRUE);
					$leute_col_name = array_merge($leute_col_name, (array)$leute_col_name_add);
					$header = array();
					$wrap = array();
					$cellTypes = array();
					$headerColCounter = 0;
					foreach($xls_cols as $c) {
						if(!$leute_col_name[$c]) continue;
						$header[] = $leute_col_name[$c];
						// set cellType to 'text' if this col is a tel-number
						if (preg_match('/^(.|._)?(tel|telp|telg|fax|natel)(.|._)?$/', $c) && in_array($c, array('telp', 'telg', 'fax', 'natel'))) {
							$cellTypes[$headerColCounter+1] = 'text';
						} elseif(substr($c,-4) == "date" || substr($c,-5) == "datum") {
							$cellTypes[$headerColCounter+1] = 'date';
						}
						//Define wrapping for excel cells (only wrap for group columns)
						if($c == 'groups' || substr($c, 0, 9) == 'MODULEgrp') {
							$wrap[$headerColCounter+1] = TRUE;
						} else {
							$wrap[$headerColCounter+1] = FALSE;
						}
						$headerColCounter++;
					}//foreach(cols as c)

					$linebreak_columns = array();
					if ($_POST['from_settings'] == 'true') {
						// get linebreak columns
						$linebreak_columns_saved = ko_get_userpref($_SESSION['ses_userid'], 'leute_linebreak_columns');
						foreach (explode(',', $linebreak_columns_saved) as $c) {
							if ($leute_col_name[$c] != "") {
								$linebreak_columns[] = $leute_col_name[$c];
							}
						}
					}

					if($_POST['id'] == 'excel') {
						$filename = $ko_path."download/excel/".getLL("export_filename").strftime("%d%m%Y_%H%M%S", time()).".xlsx";
						$filename = ko_export_to_xlsx($header, $data, $filename, "kOOL", 'landscape', $wrap, array(), $linebreak_columns, $cellTypes);
					} else {
						$filename = $ko_path.'download/excel/'.getLL('export_filename').strftime('%d%m%Y_%H%M%S', time()).'.csv';
						ko_export_to_csv($header, $data, $filename);
					}

					$show = ko_get_userpref($_SESSION['ses_userid'], 'default_view_leute');
					if(!$show) $show = 'show_all';
					$_SESSION['show'] = $show;

					$onload_code = "ko_popup('".$ko_path."download.php?action=file&amp;file=".substr($filename, 3)."');";
				break;

				case "sms":
				case "telegram":
				case "smstelegram":

					if ($_POST['id'] == 'sms' || !ko_module_installed('telegram')) {
						$sendmode = "sms";
					} else if ($_POST['id'] == 'telegram') {
						$sendmode = "telegram";
					} else {
						$sendmode = "smstelegram";
					}

					$smarty->assign("tpl_viewmode", $sendmode);

					$link = "/leute/index.php?";
					foreach($_REQUEST AS $key => $value) {
						if ($key == 'id') continue;
						$link.= $key . "=" . $value . "&";
					}

					$smarty->assign("tpl_viewmodelink", $link);
					$smarty->assign("ko_path", $ko_path);

					//Leute-cols für Excel-Datei auslesen
					$cols = array("anrede", "vorname", "nachname", "adresse", "adresse_zusatz", "plz", "ort", "telp", "telg", "natel");
					if (strpos($sendmode, 'telegram') !== false) $cols[] = "telegram_id";

					$header = $xls_data = array();
					$rec_invalid = $rec_invalid_ids = "";
					foreach($cols as $c) {
						$header[] = $leute_col_name[$c];
					}

					$invalid_recipients_count = 0;
					foreach($es as $l_id => $l) {
						$invalid = FALSE;

						// todo: ignore not selected user from mylist (sind anscheinend im array, obwohl nicht gewählt.

						// first priority is telegram
						if (strpos($sendmode, 'telegram') !== false && $l['telegram_id'] != -1) {
							$telegram_recipient_leuteIds[] = $l_id;
							$telegram_recipient_names[] = $l["vorname"]." ".$l["nachname"];
						} else if (strpos($sendmode, 'sms') !== false)  {
							ko_get_leute_mobile($l, $mobiles);
							if(sizeof($mobiles) > 0) {
								foreach($mobiles as $mobile) {
									if(check_natel($mobile)) {
										$sms_recipient_leuteIds[] = $l_id;
										$sms_recipient_names[] = $l["vorname"]." ".$l["nachname"];
									} else {
										$invalid = TRUE;
									}
								}
							} else {
								$invalid = TRUE;
							}
						} else {
							$invalid = true;
						}

						if($invalid) {
							$rec_invalid .= $l["vorname"]." ".$l["nachname"].($l["ort"] ? (" ".getLL('from')." ".$l["ort"]) : "")."<br>";
							$rec_invalid_ids .= $l["id"].",";

							$col = 0;
							foreach($cols as $c) {
								$xls_data[$invalid_recipients_count][$col++] = sql2datum($l[$c]);
							}
							$invalid_recipients_count++;
						}
					}

					//XLS-Datei aller Leute ohne GSM-Nummer erstellen
					if($rec_invalid != "") {
						$rec_invalid_ids = substr($rec_invalid_ids, 0, -1);
						$smarty->assign("tpl_recipients_invalid", $rec_invalid);
						$smarty->assign("tpl_recipients_invalid_ids", $rec_invalid_ids);
						$filename = $ko_path."download/excel/".getLL("export_filename").strftime("%d%m%Y_%H%M%S", time()).".xlsx";
						$filename = ko_export_to_xlsx($header, $xls_data, $filename, "kOOL");
						$smarty->assign("xls_filename", $filename);
					}

					$smarty->assign("tpl_show_recipients", 1);
					$smarty->assign("tpl_show_sendbutton", 1);
					$smarty->assign("tpl_show_header", 1);

					if (strpos($sendmode, 'sms') !== false) {
						//Sender select
						$senders = array();
						//Check for admin mobile
						ko_get_login($_SESSION['ses_userid'], $login);
						$sender_ids = explode(',', ko_get_setting('sms_sender_ids'));
						if ($login['mobile'] && in_array($login['mobile'], $sender_ids)) $senders[] = $login['mobile'];
						//Check for assigned person
						ko_get_leute_mobile(ko_get_logged_in_id(), $mobiles);
						foreach ($mobiles as $mobile) {
							if (!check_natel($mobile)) continue;
							if (!in_array($mobile, $sender_ids)) continue;
							if (in_array($mobile, $senders)) continue;
							$senders[] = $mobile;
						}

						$smarty->assign('tpl_sms_senders', $senders);

						$smarty->assign("tpl_sms_recipients_avalues", "[" . implode(',', $sms_recipient_leuteIds) . "]");
						$smarty->assign("tpl_sms_recipients_avalue", "[" . implode(',', $sms_recipient_leuteIds) . "]");
						$smarty->assign("tpl_sms_recipients_adescs", "[\"" . implode('","', $sms_recipient_names) . "\"]");
					}

					if (strpos($sendmode, 'telegram') !== false) {
						$smarty->assign("tpl_telegram_recipients_avalues", "[" . implode(',', $telegram_recipient_leuteIds) . "]");
						$smarty->assign("tpl_telegram_recipients_avalue", "[" . implode(',', $telegram_recipient_leuteIds) . "]");
						$smarty->assign("tpl_telegram_recipients_adescs", "[\"" . implode('","', $telegram_recipient_names) . "\"]");
						$smarty->assign("tpl_leute_telegram_sender_text", sprintf(getLL('leute_telegram_sender_text'), ko_get_setting("telegram_botname")));
					}

					$smarty->assign('crm_contact_tpl_groups', ko_get_crm_contact_form_group(array('leute_ids'), array('type' => 'sms')));

					if(ko_module_installed('telegram') && ko_module_installed('sms')) {
						$smarty->assign("tpl_show_sendmethods", TRUE);
					}

					$_SESSION["show_back"] = $_SESSION["show"];
					$_SESSION["show"] = "mobil_versand";
				break;


				case "etiketten_settings":
					$_SESSION['leute_export_etiketten_post'] = $_POST;
					$_SESSION['leute_export_etiketten_post']['leute_ids'] = array_keys($es);
					$_SESSION['leute_export_etiketten_post']['session_show'] = $_SESSION['show'];

					//Vorlage-Namen auslesen (für Dropdown-Listen)
					ko_get_etiketten_vorlagen($vorlagen_);
					foreach($vorlagen_ as $k => $v) {
						$vorlagen["values"][] = $v['id'];
						$vorlagen["output"][] = $v["name"];
					}//foreach(vorlagen as v)
					$smarty->assign("vorlagen", $vorlagen);

					//Store data in session to be used on submission of label export settings
					//$_SESSION["etiketten_data"] = $es;

					$_SESSION["etiketten_cols"] = $xls_cols;

					$col = array();
					$col_counter = 0;
					foreach($xls_cols as $c_i => $c) {
						if(!$leute_col_name[$c]) {
							unset($_SESSION["etiketten_cols"][$c_i]);
							continue;
						}
						$col[$col_counter]["name"] = ko_html($leute_col_name[$c]);
						// if($col_counter < (sizeof($xls_cols)-1)) $col[$col_counter]["show_select"] = true;
						//else $col[$col_counter]["show_select"] = false;
						$col[$col_counter]["show_select"] = true;
						$col[$col_counter++]["id"] = $c;
					}

					$free_cols = array(array('id' => 'free_0', 'type' => 'empty', 'name' => 'free_0', 'show_select' => True));
					foreach ($col as $i => $c) {
						$free_cols[] = $c;
						$free_cols[] = ($i < sizeof($col) - 1 ? array('id' => 'free_'. ($i + 1), 'type' => 'empty', 'name' => 'free_'. ($i + 1), 'show_select' => True) : array('id' => 'free_'. ($i + 1), 'type' => 'empty', 'name' => 'free_'. ($i + 1), 'show_select' => False));
					}

					$col = $free_cols;

					$old_ses_cols = $_SESSION["etiketten_cols"];
					$new_ses_cols = array('free_0');
					foreach ($old_ses_cols as $i => $c) {
						$new_ses_cols[] = $c;
						$new_ses_cols[] = 'free_'. ($i + 1);
					}

					$_SESSION["etiketten_cols"] = $new_ses_cols;

					$smarty->assign("tpl_cols", $col);

					$returnAddress = ko_get_userpref($_SESSION['ses_userid'], 'labels_return_address');
					list($retChk, $retSel, $retTxt) = explode('@@@', $returnAddress);

					// prepare return addresses in order to show them in select
					$person = ko_get_logged_in_person();
					if ($person != null && $person['nachname'] != null && trim($person['nachname']) != '' && $person['ort'] != null && trim($person['ort']) != '') {
						$returnAddressLogin = '';
						$returnAddressLogin  = $person['vorname'] ? $person['vorname'].($person['nachname'] ? ' ' . $person['nachname'] : '') . ', ' : '';
						$returnAddressLogin .= $person['adresse'] ? $person['adresse'].', ' : '';
						$returnAddressLogin .= $person['plz'] ? $person['plz'].' ' : '';
						$returnAddressLogin .= $person['ort'] ? $person['ort'].', ' : '';
						if(substr($returnAddressLogin, -2) == ', ') $returnAddressLogin = substr($returnAddressLogin, 0, -2);
					}
					else {
						$returnAddressLogin = null;
					}
					if (ko_get_setting('info_name') != null && trim(ko_get_setting('info_name')) != '' && ko_get_setting('info_city') != null && trim(ko_get_setting('info_city')) != '') {
						$returnAddressInfo = '';
						$returnAddressInfo  = ko_get_setting('info_name') ? ko_get_setting('info_name').', ' : '';
						$returnAddressInfo .= ko_get_setting('info_address') ? ko_get_setting('info_address').', ' : '';
						$returnAddressInfo .= ko_get_setting('info_zip') ? ko_get_setting('info_zip').' ' : '';
						$returnAddressInfo .= ko_get_setting('info_city') ? ko_get_setting('info_city').', ' : '';
						if(substr($returnAddressInfo, -2) == ', ') $returnAddressInfo = substr($returnAddressInfo, 0, -2);
					}
					else {
						$returnAddressInfo = null;
					}

					$pp = ko_get_userpref($_SESSION['ses_userid'], 'labels_pp');
					list($ppChk, $ppSel, $ppTxt) = explode('@@@', $pp);

					// prepare pp in order to show them in select
					$ppChoices = array();
					$person = ko_get_logged_in_person();
					if ($person != null && $person['ort'] != null && trim($person['ort']) != '') {
						$ppChoices[] = "{$person['plz']} {$person['ort']}";
					}
					if (ko_get_setting('info_city') != null && trim(ko_get_setting('info_city')) != '') {
						$ppChoices[] = ko_get_setting('info_zip') . " " . ko_get_setting('info_city');
					}
					foreach (explode("\n", ko_get_setting('pp_addresses')) as $ppAddress) {
						$ppAddress = trim($ppAddress);
						if (!$ppAddress) continue;
						$ppChoices[] = $ppAddress;
					}
					$ppChoices = array_unique($ppChoices);

					if ($_POST['sel_auswahl'] == 'allef' || $_POST['sel_auswahl'] == 'alleFam2' || $_POST['sel_auswahl'] == 'markiertef' || $_POST['sel_auswahl'] == 'markierteFam2') {
						//Family firstname
						$smarty->assign('settings_force_family_firstname', array('desc' => getLL('admin_settings_options_leute_force_family_firstname'),
							'type' => 'select',
							'name' => 'sel_leute_force_family_firstname',
							'params' => 'size="0"',
							'values' => array(0, 1, 2),
							'descs' => array(getLL('admin_settings_options_leute_force_family_firstname_0'), getLL('admin_settings_options_leute_force_family_firstname_1'), getLL('admin_settings_options_leute_force_family_firstname_2')),
							'value' => ko_get_userpref($_SESSION['ses_userid'], 'leute_force_family_firstname'),
						));
					}

					// priority
					$priority = FALSE;

					ko_leute_export_show_warning($smarty, $es);

					//LL-Values
					$smarty->assign("label_title", getLL("leute_labels_title"));
					$smarty->assign("label_preset", getLL("leute_labels_preset"));
					$smarty->assign("label_start", getLL("leute_labels_start"));
					$smarty->assign("label_border", getLL("leute_labels_border"));
					$smarty->assign("label_yes", getLL("yes"));
					$smarty->assign("label_no", getLL("no"));
					$smarty->assign("label_fill_page", getLL("leute_labels_fill_page"));
					$smarty->assign('label_multiplyer', getLL('leute_labels_multiplyer'));
					$smarty->assign('label_return_address', getLL('leute_labels_return_address'));
					$smarty->assign('return_address_chk', $retChk);
					$smarty->assign('return_address_sel', $retSel);
					$smarty->assign('return_address_txt', $retTxt);
					$smarty->assign('return_address_info', $returnAddressInfo);
					$smarty->assign('return_address_login', $returnAddressLogin);
					$smarty->assign('label_pp', getLL('leute_labels_pp'));
					$smarty->assign('pp_chk', $ppChk);
					$smarty->assign('pp_sel', $ppSel);
					$smarty->assign('pp_txt', $ppTxt);
					$smarty->assign('pp_choices', $ppChoices);
					$smarty->assign('priority_chk', $priority);
					$smarty->assign('label_priority', getLL('leute_labels_priority'));
					$smarty->assign("label_limiter", getLL("leute_labels_limiter"));
					$smarty->assign("label_limiter_newline", getLL("leute_labels_limiter_newline"));
					$smarty->assign("label_limiter_doublenewline", getLL("leute_labels_limiter_doublenewline"));
					$smarty->assign("label_limiter_space", getLL("leute_labels_limiter_space"));
					$smarty->assign("label_limiter_nothing", getLL("leute_labels_limiter_nothing"));
					$smarty->assign("label_submit", getLL("leute_labels_submit"));

					$smarty->assign('crm_contact_tpl_groups', ko_get_crm_contact_form_group(array('leute_ids'), array('type' => 'letter')));

					$_SESSION["show_back"] = $_SESSION["show"];
					$_SESSION["show"] = "etiketten_optionen";
				break;


				case "etiketten":
					if(!$_POST["sel_vorlage"]) break;

					$labels_data = array(); $row = 0;

					$data = $es;
					$cols = $_SESSION["etiketten_cols"];

					//Parse data
					$all_datafields = db_select_data("ko_groups_datafields", "WHERE 1=1", "*");
					foreach($data as $p_id => $p) {
						$temp = "";
						foreach($cols as $i => $col) {
							if(substr($col, 0, 6) == "MODULE") {
								$value = map_leute_daten($p[$col], $col, $p, $all_datafields, FALSE, array('kota_process_modes' => 'pdf,list'));
								if(is_array($value)) {
									$values = NULL;
									foreach($value as $v) if($v) $values[] = strip_tags(ko_unhtml($v));
									$temp .= implode(", ", $values);
								} else {
									$temp .= strip_tags(ko_unhtml($value));
								}
							}
							else if (substr($col, 0, 4) == 'free') {
								$value = trim($_POST[$col]);
								if ($value == '') continue;
								$temp .= $value;
							} else {
								$value = map_leute_daten($p[$col], $col, $p, $all_datafields, FALSE, array('kota_process_modes' => 'pdf,list'));
								$temp .= strip_tags($value);
							}
							//Add newline or whitespace after value, if value is not empty
							if(trim($value) == "" || $i == sizeof($cols) - 1) continue;
							switch($_POST{"sel_col_".$col}) {
								case "Zeilenumbruch":
									$temp .= "\n";
									break;
								case "Doppelter Zeilenumbruch":
									$temp .= "\n\n";
									break;
								case "Leerschlag":
									$temp .= " ";
									break;
								case "Nichts":
									$temp .= "";
									break;
							}//switch(sel_col_)
						}//foreach(cols)
						$labels_data[$row++] = $temp;
					}//foreach(data)

					// return address
					$retChk = ($_POST['chk_return_address'] ? 1 : 0);
					$retSel = ($retChk == 1 ? $_POST['sel_return_address'] : '');
					$retTxt = (strstr($retSel, 'manual_address') != false ? $_POST['txt_return_address'] : '');

					// return address
					$ppChk = ($_POST['chk_pp'] ? 1 : 0);
					$ppSel = ($ppChk == 1 ? $_POST['sel_pp'] : '');
					$ppTxt = (strstr($ppSel, 'manual_address') != false ? $_POST['txt_pp'] : '');

					// priority
					$priority = $_POST['chk_priority'];

					//Etiketten erstellen
					$filename = ko_export_etiketten($_POST['sel_vorlage'], $_POST['txt_start'], $_POST['rd_rahmen'], $labels_data, ($_POST['chk_fill_page']?$_POST['txt_fill_page']:0), $_POST['txt_multiply'], $retChk==1, $retSel, $retTxt, $ppChk==1, $ppSel, $ppTxt, $priority);

					//Store return address selection and pp in userprefs
					ko_save_userpref($_SESSION['ses_userid'], 'labels_return_address', $retChk . '@@@' . $retSel . '@@@' . $retTxt);
					ko_save_userpref($_SESSION['ses_userid'], 'labels_pp', $ppChk . '@@@' . $ppSel . '@@@' . $ppTxt);

					$onload_code = "ko_popup('".$ko_path."download.php?action=file&amp;file=$filename');";
					$_SESSION["show"] = $_SESSION["show_back"] ? $_SESSION["show_back"] : "show_all";
				break;


				case 'mailmerge':
					//Get layouts
					$layouts = ko_leute_get_mailmerge_layouts();
					$smarty->assign('layouts', $layouts);

					//Show previously uploaded signature file
					foreach($mailmerge_signature_ext as $ext) {
						if(file_exists($ko_path.'my_images/signature_'.$_SESSION['ses_userid'].'.'.$ext)) {
							$smarty->assign('show_sig_file', $ko_path.'my_images/signature_'.$_SESSION['ses_userid'].'.'.$ext);
						}
					}

					//Check for complete addresses
					$mandatory_fields = array('adresse', 'plz', 'ort');
					$invalid_addresses = $invalid_addresses_ids = array();
					foreach($es as $id => $p) {
						foreach($mandatory_fields as $field) {
							if(trim($p[$field]) == '' && !in_array($p['id'], $invalid_addresses_ids)) {
								$invalid_addresses[] = (($p['vorname'] || $p['nachname']) ? ($p['vorname'].' '.$p['nachname']) : $p['firm']).($p['ort'] ? ' '.getLL('from').' '.$p['ort'] : '');
								$invalid_addresses_ids[] = $p['id'];
								// Don't remove them from export list (e.g. useful if letters are distributed by hand)
								//unset($es[$id]);
							}
						}
					}
					if(sizeof($invalid_addresses) > 0) {
						$smarty->assign('show_invalid', TRUE);
						$smarty->assign('invalid_addresses', implode(', ', $invalid_addresses));
						$smarty->assign('invalid_addresses_ids', implode('@', $invalid_addresses_ids));
					}

					ko_leute_export_show_warning($smarty, $es);

					//Prepare legend for all selected columns and their markers
					$leute_col_name = ko_get_leute_col_name(FALSE, TRUE);
					$leute_col_name = array_merge($leute_col_name, (array)$leute_col_name_add);
					foreach($xls_cols as $c) {
						if(!$leute_col_name[$c]) continue;
						$header[] = $leute_col_name[$c];
					}//foreach(cols as c)
					$colLegend = array();
					for($i=0; $i<sizeof($header); $i++) {
						$colLegend[$i] = $header[$i];
					}
					$smarty->assign('colLegend', $colLegend);

					//Get letters
					$rows = db_select_data('ko_mailmerge', 'WHERE `user_id` = \''.$_SESSION['ses_userid'].'\'', '*', 'ORDER BY `crdate` DESC');
					if(sizeof($rows) > 0) {
						$smarty->assign('show_reuse', TRUE);
						$letters = $letters_ids = array();
						$letters[] = ''; $letters_ids[] = '';
						foreach($rows as $letter) {
							$letters_ids[] = $letter['id'];
							$letters[] = strftime($DATETIME['dmy'], strtotime($letter['crdate'])).' '.ko_html(substr($letter['subject'], 0, 40)).' ('.$letter['num_recipients'].')';
						}
						$smarty->assign('letters', $letters);
						$smarty->assign('letters_ids', $letters_ids);
					} else {
						$smarty->assign('show_reuse', FALSE);
					}

					//Check for valid sender address of logged in user
					$p = ko_get_logged_in_person();
					if($p['id'] && $p['adresse'] && $p['plz'] && $p['ort']) {
						$address_user = $p['vorname'].' '.$p['nachname'].', '.$p['adresse'].', '.$p['plz'].' '.$p['ort'];
						$smarty->assign('sender_address_user', $address_user);
					} else {
						$smarty->assign('sender_address_user', '');
					}
					if(ko_get_setting('info_name')) {
						$address_church = ko_get_setting('info_name').', '.ko_get_setting('info_address').', '.ko_get_setting('info_zip').' '.ko_get_setting('info_city');
						$smarty->assign('sender_address_church', $address_church);
					} else {
						$smarty->assign('sender_address_church', '');
					}
					$smarty->assign('sender_address_none', getLL('leute_mailmerge_label_sender_none'));
					$smarty->assign('signature', $p['vorname'].' '.$p['nachname']);

					//Store data in session to be used on submission of label export settings
					$_SESSION['mailmerge_data'] = $es;
					$_SESSION['mailmerge_cols'] = $xls_cols;
					$_SESSION['mailmerge_famids'] = $done_fam;

					$_SESSION["show_back"] = $_SESSION["show"];
					$_SESSION["show"] = "mailmerge";
				break;


				case "email":
					$_SESSION['leute_mailing_people_data'] = $es;
					$_SESSION['leute_mailing_sel_auswahl'] = $_POST["sel_auswahl"];

					$_SESSION["show_back"] = $_SESSION["show"];
					$_SESSION["show"] = "email_versand";
				break;


				case 'vcard':
					include_once($ko_path.'leute/inc/vcard.php');
					$v = new vCard();
					foreach($es as $person) {
						$v->addPerson($person);
					}
					$filename = $v->writeCard();
					$onload_code = "ko_popup('".$ko_path.'download.php?action=file&amp;file='.substr($filename, 3)."');";
				break;
			}
		}//if(!error)

	break; //leute_action



	case "leute_chart":
		$_SESSION["show"] = "chart";
	break;



	case "do_export_pdf":
		$layout_id = format_userinput($_POST["layout_id"], "uint");
		if(!$layout_id) break;

		$filename = ko_export_leute_as_pdf($layout_id, '', FALSE, TRUE);

		$onload_code = "ko_popup('".$ko_path."download.php?action=file&amp;file=".substr($filename, 3)."');";
		$_SESSION["show"] = $_SESSION["show_back"] ? $_SESSION["show_back"] : "show_all";
	break;





	case "submit_sms":
	case "submit_telegram":
	case "submit_smstelegram":
		if($do_action == 'submit_telegram' || $do_action == 'submit_smstelegram') {
			$empfaenger = explode(",", format_userinput($_POST["sel_telegram_people"], "intlist"));
			$p = ko_get_logged_in_person();
			$text = format_userinput($_POST["txt_telegram_text"],'text');
			$text.= "\n\nVerschickt von {$p['vorname']} {$p['nachname']} ({$_SESSION['ses_username']})";

			$telegramRecipients = [];
			foreach($empfaenger AS $userid) {
				ko_get_person_by_id($userid, $telegram_user);
				$telegramRecipients[] = $telegram_user;
			}

			if(!empty($telegramRecipients)) {
				send_telegram_message($telegramRecipients, $text);
			}
		}

		if($do_action == 'submit_sms' || $do_action == 'submit_smstelegram') {
			$sel_sms_people = explode(",", format_userinput($_POST["sel_sms_people"], "intlist"));

			foreach($sel_sms_people AS $id) {
				ko_get_leute_mobile($id, $mobiles);
				if (check_natel($mobiles[0])) {
					$empfaenger_[] = $mobiles[0];
				}
			}

			$empfaenger__ = explode(",", format_userinput($_POST["txt_sms_recipients_add"], "intlist"));
			$empfaenger = array_unique(array_merge($empfaenger_, $empfaenger__));

			$rec_ids = explode(',', format_userinput($_POST['hid_recipient_ids'], 'intlist'));
			$text = $_POST["txt_smstext"];


			//Get possible senders
			$senders = array();
			//Check for admin mobile
			ko_get_login($_SESSION['ses_userid'], $login);
			$sender_ids = explode(',', ko_get_setting('sms_sender_ids'));
			if ($login['mobile'] && in_array($login['mobile'], $sender_ids)) $senders[] = $login['mobile'];
			//Check for assigned person
			ko_get_leute_mobile(ko_get_logged_in_id(), $mobiles);
			foreach ($mobiles as $mobile) {
				if (!check_natel($mobile)) continue;
				if (!in_array($mobile, $sender_ids)) continue;
				if (in_array($mobile, $senders)) continue;
				$senders[] = $mobile;
			}

			//Absender
			$p = ko_get_logged_in_person();
			if ($_POST['sel_sender'] && in_array($_POST['sel_sender'], $senders)) {  //User selected sener from GUI if set
				$from = $_POST['sel_sender'];
			} else if (sizeof($senders) > 0) {  //Use first (possibly only) entry of senders
				$from = array_shift($senders);
			} else {
				$from = $_SESSION["ses_username"];
			}

			if ($SMS_PARAMETER['provider'] == 'aspsms') {
				$ret = send_aspsms($empfaenger, $text, $from, $num, $charges, $log_id);
				if ($ret) {
					$info_txt = getLL('info_leute_99a') . $num . getLL('info_leute_99b');
					$info_txt .= getLL('info_leute_99d') . ' ' . $charges;
					$notifier->addTextInfo($info_txt, $do_action);

				}
			} //Use Clickatell as default (for backwards compatibility, if no provider is set in $SMS_PARAMETER)
			else {
				$climsgid = time() . "_" . $p["id"];
				$msg_type = "SMS_TEXT";
				$ret = send_sms($empfaenger, $text, $from, $climsgid, $msg_type, $success, $done, $problems, $charges, $error_message, $log_id);

				if ($ret) {
					$notifier->addInfo(99, $do_action);
					$info_txt = getLL('info_leute_99a') . $success . '/' . $done . getLL('info_leute_99b');
					if ($problems) $info_txt .= getLL('info_leute_99c') . substr($problems, 0, -2);
					$info_txt .= getLL('info_leute_99d') . ' ' . $charges;
				} else {
					$my_error_txt = $error_message;
					$notifier->addTextError($my_error_text, $do_action);
				}
			}

			// create crm entry
			if (!$notifier->hasErrors()) {
				ko_create_crm_contact_from_post(TRUE, array('leute_ids' => implode(',', array_unique($rec_ids)), 'reference' => 'ko_log:' . $log_id));
			}
		}

		$_SESSION['show'] = $_SESSION['show_back'] ? $_SESSION['show_back'] : 'show_all';
	break;

	case 'submit_mailmerge':
		$layout = $_POST['sel_layout'] ? format_userinput($_POST['sel_layout'], 'alphanum') : 'default';
		$layout_fcn = $layout == 'default' ? "leute_mailmerge_pdf_layout_default" : "my_leute_mailmerge_pdf_layout_{$layout}";
		if (!function_exists($layout_fcn)) {
			$notifier->addError(24, $do_action);
		}

		$mmdata = array(); $counter = 0;
		$data = $_SESSION['mailmerge_data'];
		$cols = $_SESSION['mailmerge_cols'];
		$fam_ids = $_SESSION['mailmerge_famids'];

		//Signature file
		$sig_file = '';
		if($_FILES['file_sig_file']['tmp_name']) {
			$upload_name = $_FILES['file_sig_file']['name'];
			$tmp = $_FILES['file_sig_file']['tmp_name'];
			$ext_ = explode('.', $upload_name);
			$ext = strtolower($ext_[sizeof($ext_)-1]);

			if(in_array($ext, $mailmerge_signature_ext)) {
				$path = $BASE_PATH.'my_images/';
				$filename = 'signature_'.$_SESSION['ses_userid'].'.'.$ext;
				$dest = $path.$filename;

				$ret = move_uploaded_file($tmp, $dest);
				if($ret) {
					$sig_file = '../my_images/'.$filename;
					chmod($dest, 0644);
				}
			}
		}
		//Get already stored file
		else if($_POST['chk_sig_file']) {
			foreach($mailmerge_signature_ext as $ext) {
				if(is_file($BASE_PATH.'my_images/signature_'.$_SESSION['ses_userid'].'.'.$ext)) {
					$sig_file = '../my_images/signature_'.$_SESSION['ses_userid'].'.'.$ext;
				}
			}
		}

		$filenames = array();
		$errors = array();
		$mapping_information = array();

		//Parse data
		$sender = ko_get_logged_in_person();
		$all_datafields = db_select_data('ko_groups_datafields', 'WHERE 1=1', '*');
		foreach($data as $p_id => $p) {
			//Used to store values for marks ###COL_X### to be replaced in text and subject below
			$mapCols = array(); $colcounter = 0;
			foreach($cols as $col) {
				if(substr($col, 0, 6) == 'MODULE') {
					$value = map_leute_daten($p[$col], $col, $p, $all_datafields);
					if(is_array($value)) {
						$values = NULL;
						foreach($value as $v) {
							$values[] = strip_tags(ko_unhtml($v));
							$mapCols['###COL_'.($colcounter++).'###'] = strip_tags(ko_unhtml($v));
						}
						$mmdata[$counter][$col] = implode(', ', $values);
					} else {
						$mmdata[$counter][$col] = strip_tags(ko_unhtml($value));
						$mapCols['###COL_'.($colcounter++).'###'] = strip_tags(ko_unhtml($value));
					}
				} else {
					$value = map_leute_daten($p[$col], $col, $p, $all_datafields);
					$mmdata[$counter][$col] = strip_tags($value);
					$mapCols['###COL_'.($colcounter++).'###'] = strip_tags($value);
				}
			}//foreach(cols)

			$map = array();

			//Salutation (opening)
			$opening = '';
			if($_POST['rd_salutation'] == 'formal') {
				$opening = $p['MODULEsalutation_formal'];
			} else {
				$opening = $p['MODULEsalutation_informal'];
			}

			//TODO: Add hook to allow the opening to be changed by a plugin
			$data[$p_id]['_opening_'] = $opening;
			$data[$p_id]['_closing_'] = $_POST['txt_closing'];;

			$data[$p_id]['_subject_'] = strtr($_POST['txt_subject'], $mapCols);

			//Replace markers in text
			$text = strtr($_POST['txt_text'], $mapCols);
			//Remove any newlines at the end of the text
			while(substr($text, -2) == '\\\\') $text = substr($text, 0, -2);
			$data[$p_id]['_text_'] = $text;

			$counter++;
		}//foreach(data)

		$general = array();

		$general['sender'] = $_POST['rd_sender'];
		$general['signature_file'] = $sig_file;
		$general['signature'] = $_POST['txt_signature'];
		$general['layout'] = getLL('letter_'.$_POST['sel_layout']);


		//Store letter in db
		$mmdb = array();
		$mmdb['user_id'] = $_SESSION['ses_userid'];
		$mmdb['crdate'] = strftime('%Y-%m-%d %H:%M:%S', time());
		$mmdb['num_recipients'] = $counter;
		$mmdb['preset'] = $_POST['sel_layout'];
		$mmdb['salutation'] = $_POST['rd_salutation'];
		$mmdb['subject'] = $_POST['txt_subject'];
		$mmdb['text'] = $_POST['txt_text'];
		$mmdb['closing'] = $_POST['txt_closing'];
		$mmdb['signature'] = $_POST['txt_signature'];
		$mmdb['sig_file'] = $_POST['chk_sig_file'] ? 1 : 0;
		db_insert_data('ko_mailmerge', $mmdb);


		//Set new show
		$_SESSION['show'] = $_SESSION['show_back'] ? $_SESSION['show_back'] : 'show_all';

		//create PDF
		$filename = $ko_path.'download/pdf/'.getLL('leute_mailmerge_filename').strftime('%d%m%Y_%H%M%S', time()).'.pdf';
		$success = call_user_func_array($layout_fcn, array($general, $data, $filename));
		if ($success) {
			$onload_code = "ko_popup('".$ko_path."download.php?action=file&amp;file=".substr($filename, 3)."');";
		}

	break;



	case 'submit_email':
		if(
			(defined('ALLOW_SEND_EMAIL') && ALLOW_SEND_EMAIL === FALSE) === FALSE &&
			(db_get_count('ko_scheduler_tasks', 'id', 'AND name = "Mailing" AND status = 1') >= 1) &&
			!(!is_array($MAILING_PARAMETER) || sizeof($MAILING_PARAMETER) < 3)
		) {
			$text = $_POST['leute_mailing_text'];
			$subject = $_POST['leute_mailing_subject'];

			$replyTo = $_POST['leute_mailing_reply_to'];

			$fileNames = array_filter(explode('@|,|@', $_POST['leute_mailing_files']), function ($e) {
				return $e ? true : false;
			});


			$recipients = $_SESSION['leute_mailing_people_data'];
			$mode = in_array($_SESSION['leute_mailing_sel_auswahl'], array('markierte', 'allep')) ? 'person' : 'family';

			$files = array();
			foreach ($fileNames as $file) {
				$files[$BASE_PATH . 'my_images/temp/' . $file] = substr($file, 37);
			}


			//Get sender (current user)
			$p = ko_get_logged_in_person();
			if(isset($MAILING_SENDER_NAME) && $replyTo == "info@refgossau.ch") {
				// temp fix for refgossau. see #3241
				$senderName = $MAILING_SENDER_NAME;
			} else if($p['vorname'] || $p['nachname']) {
				$senderName = $p['vorname'].' '.$p['nachname'];
			} else if($p['firm']) {
				$senderName = $p['firm'];
			} else {
				$senderName = $_SESSION['ses_username'];
			}
			$from = array($replyTo => $senderName);

			$message = ko_prepare_mail($from, $replyTo, $subject, $text, $files, array(), array(), array($replyTo));
			$message->setContentType('text/html');
			//$message->getHeaders()->addTextHeader('Content-Transfer-Encoding', 'quoted-printable');
			require_once($BASE_PATH . 'inc/class.html2text.php');
			$html2text = new html2text($text);
			$plainText = $html2text->get_text();
			$message->addPart($plainText, 'text/plain');

			$rawMessage = $message->toString();

			$parts = explode(chr(13) . chr(10) . chr(13) . chr(10), $rawMessage);
			$header = array_shift($parts);
			$header = preg_replace('/(\n|^)To: (.*)(\n\s+(.*))*\n/i', '$1', $header);
			$header = preg_replace('/(\n|^)Subject: (.*)(\n\s+(.*))*\n/i', '$1', $header);
			$body = implode(chr(13) . chr(10) . chr(13) . chr(10), $parts);

			$mail = array(
				'status' => 2,
				'header' => $header,
				'body' => $body,
				'subject' => $subject,
				'from' => $replyTo,
				'sender_email' => $replyTo,
				'user_id' => $_SESSION['ses_userid'],
				'crdate' => date('Y-m-d H:i:s'),
				'modify_rcpts' => 1,
			);

			$mailId = db_insert_data('ko_mailing_mails', $mail);

			foreach ($recipients as $r) {
				$recipient = array(
					'mail_id' => $mailId,
				);

				if ($mode == 'family') {
					$placeholders = $r;
					array_walk_recursive($placeholders, 'utf8_encode_array');
					$recipient['email'] = $r['email'];
					$recipient['placeholder_data'] = json_encode($placeholders);
					$recipient['leute_id'] = $r['id'];
				} else {
					ko_get_leute_email($r['id'], $emailAddresses);
					if (sizeof($emailAddresses) > 0) $emailAddress = array_shift($emailAddresses);
					else continue;

					$recName = trim($r['firm'] . ' ' . $r['vorname'] . ' ' . $r['nachname']);

					$recipient['email'] = $emailAddress;
					$recipient['leute_id'] = $r['id'];
					$recipient['name'] = $recName;
				}

				if(!check_email($recipient['email'])) continue;

				db_insert_data('ko_mailing_recipients', $recipient);
			}

			if (!$notifier->hasErrors()) {
				//$_SESSION["show"] = $_SESSION["show_back"] ? $_SESSION["show_back"] : "show_all";

				// save email for reuse
				$origUserprefKey = date('Y-m-d H:i:s') . ': ' . format_userinput($subject, "text");
				$userprefKey = $origUserprefKey;
				$cnt = 0;
				while (ko_get_userpref($_SESSION['ses_userid'], $userprefKey, 'leute_saved_email')) {
					$cnt++;
					$userprefKey = "{$origUserprefKey} ({$cnt})";
				}

				$up = array(
					'subject' => $subject,
					'text' => $text,
					'date' => date('Y-m-d H:i:s'),
				);
				array_walk_recursive($up, 'utf8_encode_array');
				ko_save_userpref($_SESSION['ses_userid'], $userprefKey, json_encode($up), 'leute_saved_email');

				$notifier->addInfo(4, $do_action);
				ko_log("leute_email", '"' . format_userinput($subject, "text") . '": ' . str_replace(",", ", ", format_userinput($_POST["txt_empfaenger"], "text")) . ", Text: " . format_userinput($plainText, "text"));

				ko_create_crm_contact_from_post();

				$_SESSION['show'] = 'show_all';
			}
		} else {
			$notifier->addTextError("Could not send email. Please contact the administrator", $do_action);
		}
	break;



	case "submit_email_contact_entry":
		// create crm entries
		ko_create_crm_contact_from_post();
		$_SESSION['show'] = 'show_all';
	break;



	//Kleingruppen
	case "neue_kg":
		$_SESSION['show_back'] = $_SESSION['show'];
		$_SESSION['show'] = 'neue_kg';
	break;



	case "list_kg":
		if(ko_module_installed('kg') && $access['kg']['MAX'] > 0) {
			$_SESSION["show"] = "list_kg";
			$_SESSION["show_kg_start"] = 1;
		} else {
			$_SESSION["show"] = "show_all";
		}
	break;


	case "chart_kg":
		if(ko_module_installed('kg') && $access['kg']['MAX'] > 1) {
			$_SESSION["show"] = "chart_kg";
		} else {
			$_SESSION["show"] = "show_all";
		}
	break;


	case "edit_kg":
		$_SESSION["show_back"] = $_SESSION["show"];
		$_SESSION["show"] = "edit_kg";
		$edit_id = format_userinput($_POST["id"], "uint");
	break;


	case "set_kg_filter":
		if(FALSE === ($id = format_userinput($_GET["id"], "uint", TRUE, 4))) {
			trigger_error("Not allowed set_kg_filter-id: ".$_POST["id"], E_USER_ERROR);
		}
		if($access['kg']['MAX'] < 1 || $access['leute']['MAX'] < 1 || !$id) break;

		$_SESSION["filter"] = array();

		$filter_akt = 0;
		ko_get_filters($filters, "leute");
    foreach($filters as $ff) {
      if(!$filter_akt && $ff["_name"] == "smallgroup") {
        $filter_akt = $ff["id"];
        $f = $ff;
      }
    }
		if(!$filter_akt) break;

		$vars = array(1 => $id);
		$_SESSION["filter"][] = array($filter_akt, $vars, 0);

		$_SESSION["show"] = "show_all";
		$_SESSION["show_start"] = 1;
	break;


	case "delete_kg":
		if(FALSE === ($id = format_userinput($_POST["id"], "uint", TRUE, 4))) {
			trigger_error("Not allowed kg-id: ".$_POST["id"], E_USER_ERROR);
		}
		if($access['kg']['MAX'] < 4) break;

		$old_kg = db_select_data("ko_kleingruppen", "WHERE `id` = '$id'");
		db_delete_data("ko_kleingruppen", "WHERE `id` = '$id.'");

		//update people data
		ko_kg_update_people();
		ko_update_kg_filter();

		$notifier->addInfo(14, $do_action);
		ko_log_diff("del_kg", $old_kg[$id]);
	break;



	case "submit_neue_kg":
		if($access['kg']['MAX'] < 4) break;

		kota_submit_multiedit("", "new_kg");
		if(!$notifier->hasErrors()) {
			ko_update_kg_filter();
			$notifier->addInfo(12, $do_action);
			$_SESSION["show"] = $_SESSION["show_back"] ? $_SESSION["show_back"] : "list_kg";
		}
	break;



	case "submit_edit_kg":
		if($access['kg']['MAX'] < 3) break;

		kota_submit_multiedit("", "edit_kg");
		if(!$notifier->hasErrors()) {
			ko_update_kg_filter();
			$notifier->addInfo(11, $do_action);
		}
		$_SESSION["show"] = $_SESSION["show_back"] ? $_SESSION["show_back"] : "list_kg";
	break;




	case 'kg_xls_export':
		if($access['kg']['MAX'] < 2) break;

		//Get selected columns from GET
		$cols = $_GET['sel_xls_cols'];

		if($cols == '_session') {
			$use_cols = $_SESSION['kota_show_cols_ko_kleingruppen'];
		} else if($cols == '_all') {
			$use_cols = array();
			foreach($KOTA['ko_kleingruppen']['_listview'] as $c) {
				if($c['name']) $use_cols[] = $c['name'];
			}
		} else {
			//Get preset from userprefs
			$name = format_userinput($cols, 'js', FALSE, 0, array(), '@');
			if($name == '') break;
			if(substr($name, 0, 3) == '@G@') $preset = ko_get_userpref('-1', substr($name, 3), 'leute_kg_itemset');
			else $preset = ko_get_userpref($_SESSION['ses_userid'], $name, 'leute_kg_itemset');
			$use_cols = explode(',', $preset[0]['value']);
		}
		//Fallback to default columns
		if($use_cols == '') $use_cols = $KOTA['ko_kleingruppen']['_listview_default'];


		//Store currently displayed columns
		$orig_cols = $_SESSION['kota_show_cols_ko_kleingruppen'];
		$_SESSION['kota_show_cols_ko_kleingruppen'] = $use_cols;

		$filename = ko_list_kg(TRUE, 'xls');
		$onload_code = "ko_popup('".$ko_path.'download.php?action=file&amp;file='.substr($filename, 3)."');";

		//Restore columns
		$_SESSION['kota_show_cols_ko_kleingruppen'] = $orig_cols;
	break;





	//Gruppen-Filter
	case "set_group_filter":
		if(FALSE === ($id = format_userinput($_GET["id"], "uint", TRUE, 6))) {
			trigger_error("Not allowed set_group_filter-id: ".$_POST["id"], E_USER_ERROR);
		}
		if(isset($_GET['rid'])) {
			$rid = format_userinput($_GET['rid'], 'uint', TRUE, 6);
		}
		if($access['groups']['MAX'] < 1 || $access['leute']['MAX'] < 1 || !$id) break;

		$_SESSION["filter"] = array();

		//Get group filter
		$filter_akt = 0;
		ko_get_filters($filters, "leute");
		foreach($filters as $ff) {
			if($ff['_name'] == 'group') {
				$filter_akt = $ff["id"];
				$f = $ff;
			}
		}
		if(!$filter_akt) break;

		//Set group filter according to given gid/rid
		$vars = array(1 => 'g'.$id);
		if($rid) $vars[2] = ':r'.$rid;
		$_SESSION["filter"][] = array($filter_akt, $vars, 0);


		//Show this group's column if userpref says so
		if(ko_get_userpref($_SESSION['ses_userid'], 'groups_filterlink_add_column')) {
			//Set show_leute_cols if not set yet (might happen if user opens group module directly after login)
			if($_SESSION['show_leute_cols'] == '') $_SESSION['show_leute_cols'] = explode(',', ko_get_userpref($_SESSION['ses_userid'], 'show_leute_cols'));

			if(!in_array('MODULEgrp'.$id, $_SESSION['show_leute_cols'])) {
				$_SESSION['show_leute_cols'][] = 'MODULEgrp'.$id;

				$cols = array_keys(ko_get_leute_col_name(FALSE, TRUE));
				if(ko_get_userpref($_SESSION['ses_userid'], 'group_shows_datafields') == 1) {
					foreach($cols as $col) {
						if(substr($col, 0, 15) != 'MODULEgrp'.$id) continue;
						if(!in_array($col, $_SESSION['show_leute_cols'])) $_SESSION['show_leute_cols'][] = $col;
					}
				}

				//Put new column in right place
				if(ko_get_userpref($_SESSION['ses_userid'], 'sort_cols_leute') != 0) {
					$new_value = NULL;
					foreach($cols as $col) {
						if(in_array($col, $_SESSION['show_leute_cols'])) $new_value[] = $col;
					}
					$_SESSION['show_leute_cols'] = $new_value;
				}
			}
		}

		$_SESSION["show"] = "show_all";
		$_SESSION['show_back'] = 'show_all';
		$_SESSION["show_start"] = 1;
	break;



	//Role filter
	case 'set_role_filter':
		if(FALSE === ($id = format_userinput($_GET['id'], 'uint', TRUE, 6))) {
			trigger_error('Not allowed set_role_filter-id: '.$_POST['id'], E_USER_ERROR);
		}
		if($access['groups']['MAX'] < 1 || $access['leute']['MAX'] < 1 || !$id) break;

		$_SESSION['filter'] = array();

		//Get role filter
		$filter_akt = 0;
		ko_get_filters($filters, 'leute');
		foreach($filters as $ff) {
			if($ff['_name'] == 'role') {
				$filter_akt = $ff['id'];
				$f = $ff;
			}
		}
		if(!$filter_akt) break;

		//Set filter according to given rid
		$vars = array(1 => 'r'.$id);
		$_SESSION['filter'][] = array($filter_akt, $vars, 0);


		$_SESSION['show'] = 'show_all';
		$_SESSION['show_start'] = 1;
	break;




	case 'set_idfilter':
		if($access['leute']['MAX'] < 1) break;

		//ID from GET
		$id = format_userinput($_GET['id'], 'uint');
		if(!$id) break;

		//Get ID filter
		$f = db_select_data('ko_filter', "WHERE `name` = 'id'", '*', '', '', TRUE);
		if(!$f['id']) break;

		//Apply filter
		$_SESSION['filter'] = array();
		$_SESSION['filter'][] = array($f['id'], array(1 => $id), 0);

		$_SESSION['show'] = 'show_all';
		$_SESSION['show_start'] = 1;
	break;




	case 'set_rotafilter':
		if($access['leute']['MAX'] < 1) break;

		$event_id = format_userinput($_GET['event'], 'uint');
		if(!$event_id) break;

		$where = "WHERE typ = 'leute' AND dbcol = 'ko_rota_schedulling.event_id' AND name ='rota'";
		$f = db_select_data('ko_filter', $where, '*', '', '', TRUE);
		if(!$f['id']) break;
		if(db_get_count("ko_event", "id"," AND id = '" . $event_id . "'") == 0) break;

		//Apply filter
		$_SESSION['filter'] = array();
		$_SESSION['filter'][] = array($f['id'], array(1 => $event_id), 0);

		$_SESSION['show'] = 'show_all';
		$_SESSION['show_start'] = 1;
		break;


	case 'set_famfilter':
		if($access['leute']['MAX'] < 1) break;
		$famid = format_userinput($_GET['famid'], 'uint');
		if($famid <= 0) break;

		$f1 = db_select_data('ko_filter', "WHERE `name` = 'family'", '*', '', '', TRUE);
		if(!$f1['id']) break;

		$_SESSION['filter'] = array();
		$_SESSION['filter'][] = array($f1['id'], array(1 => $famid), 0);

		$_SESSION['show'] = 'show_all';
		$_SESSION['show_start'] = 1;
	break;



	case 'set_dobfilter':
		if($access['leute']['MAX'] < 1) break;
		list($d, $m) = explode('-', format_userinput($_GET['dob'], 'int'));
		if(!$m || !$d || $m < 1 || $m > 12 || $d < 1 || $d > 31) break;

		$f1 = db_select_data('ko_filter', "WHERE `name` = 'birthdate'", '*', '', '', TRUE);
		if(!$f1['id']) break;

		$_SESSION['filter'] = array();
		$_SESSION['filter'][] = array($f1['id'], array(1 => $d, 2 => $m), 0);

		$_SESSION['show'] = 'show_all';
		$_SESSION['show_start'] = 1;
	break;


	case 'set_crm_project_filter':
		if (!ko_module_installed('crm')) break;
		if (!isset($access['crm'])) ko_get_access('crm');
		if($access['crm']['MAX'] < 1) break;
		list($projectId, $statusId) = explode('-', format_userinput($_GET['id'], 'int'));
		if(!$projectId || !$statusId) break;

		$f1 = db_select_data('ko_filter', "WHERE `name` = 'crm_project'", '*', '', '', TRUE);
		if(!$f1['id']) break;

		$_SESSION['filter'] = array();
		$_SESSION['filter'][] = array($f1['id'], array(1 => $projectId, 2 => $statusId), 0);

		$_SESSION['show'] = 'show_all';
		$_SESSION['show_start'] = 1;
	break;


	case 'set_crm_contact_filter':
		if (!ko_module_installed('crm')) break;
		if (!isset($access['crm'])) ko_get_access('crm');
		if($access['crm']['MAX'] < 1) break;
		$contactId = format_userinput($_GET['id'], 'uint');
		if(!$contactId) break;

		$f1 = db_select_data('ko_filter', "WHERE `name` = 'crm_contact'", '*', '', '', TRUE);
		if(!$f1['id']) break;

		$_SESSION['filter'] = array();
		$_SESSION['filter'][] = array($f1['id'], array(1 => $contactId), 0);

		$_SESSION['show'] = 'show_all';
		$_SESSION['show_start'] = 1;
	break;


	case 'set_general_filter':
		if($access['leute']['MAX'] < 1) break;

		$value = format_userinput($_POST['general_filter_value'], 'text');

		$_SESSION['filter'] = array();

		if ($value != '') {
			/*$fast_filter = ko_get_fast_filter();
			foreach($fast_filter as $id) {
				$_SESSION['filter'][] = array($id, array('', str_replace('*', '.*', $value)), 0);
			}*/
			$fast_filter = db_select_data('ko_filter', "WHERE `typ` = 'leute' AND `name` = 'fastfilter'", 'id', '', '', TRUE, TRUE);
			if ($fast_filter['id']) $_SESSION['filter'][] = array($fast_filter['id'], array('', str_replace('*', '.*', $value)), 0);
		}

		$_SESSION['show'] = 'show_all';
		$_SESSION['show_start'] = 1;
	break;


	case 'set_trackingentries_filter':
		$trackingId = format_userinput($_GET['tid'], 'uint');
		$fromDate = format_userinput($_GET['from'], 'date');
		$toDate = format_userinput($_GET['to'], 'date');
		$value = format_userinput($_GET['value'], 'text');

		$f1 = db_select_data('ko_filter', "WHERE `name` = 'trackingentries'", '*', '', '', TRUE);

		$_SESSION['filter'] = array();
		$_SESSION['filter'][] = array($f1['id'], array(1 => $trackingId, 2 => sql2datum($fromDate), 3 => sql2datum($toDate), 4 => $value), 0);

		$_SESSION['show'] = 'show_all';
		$_SESSION['show_start'] = 1;
	break;

	case "import":
	case "importtwo":
	case "importthree":
	case "importfour":
	case "importgoto":
		if(!ko_get_setting('leute_allow_import')) break;

		if($access['leute']['MAX'] > 1) {
		} else break;

		$context = &$_SESSION['leute_import'];

		switch ($do_action) {
			case 'importgoto':
				if ($_SESSION['show'] != 'import') break;

				$toState = $_GET['state'];
				if ($toState == 2) {
					if ($context['state'] == 3) {
						$context['state'] = 2;
					}
				}
			break;
			case 'import':
				if ($_SESSION['show'] != 'import') $_SESSION['show_back'] = $_SESSION['show'];
				$_SESSION['show'] = 'import';

				$context = array('state' => 1);
			break;
			case 'importtwo':
				//file
				ini_set('auto_detect_line_endings', '1'); // for mac-excel line endings (CR)

				// Check for resubmit
				if (!$csvDataString = $context['origData']) {
					$file = $_FILES['csv'];
					$csvFile = $file['tmp_name'];

					if (!$csvFile) {
						$notifier->addError(15);
						$_SESSION['show'] = 'import';
					}

					$csvDataString = implode("\n", file($csvFile));
				}

				$origCsvDataString = $csvDataString;

				if ($_POST['leute_import_manual_parameters']) {
					$manuallySupplied = TRUE;
					$csvParameters = $_POST['parameters'];
					$ignoreFirstLine = $_POST['ignore_first_line'];
					$manualSuccess = ko_parse_general_csv($csvDataString, $csvParameters, TRUE);
					if ($manualSuccess) $csvData = $manualSuccess;
				} else {
					$manuallySupplied = FALSE;
					$manualSuccess = FALSE;
				}

				$autoSuccess = FALSE;
				if (!$manuallySupplied || !$manualSuccess) {
					$autoSuccess = ko_detect_csv_parameters($csvDataString, $csvParameters, $csvData);
				}

				if ($manualSuccess || $autoSuccess) {
					$context['data'] = array_filter($csvData, function($el) {return sizeof(array_filter($el, function($e) {return trim($e) != '';})) > 0;});
					$context['origData'] = $origCsvDataString;
					$context['parameters'] = $csvParameters;
					$context['data_string'] = $csvDataString;
					$context['state'] = 2;

					$firstLine = $csvData[0];
					$firstLine = array_map(function($el){return strtolower($el);}, $firstLine);

					$firstLineMarkers = array('nachname', 'vorname', 'adresse', 'plz', 'ort', 'anrede', 'geschlecht');
					$firstLineMarkers = array_merge($firstLineMarkers, array_map(function($el){return strtolower(getLL("kota_ko_leute_".$el));}, $firstLineMarkers));

					if (!$manuallySupplied) {
						$ignoreFirstLine = FALSE;
						foreach ($firstLine as $e) {
							if (in_array($e, $firstLineMarkers)) $ignoreFirstLine = TRUE;
						}
					}
					$context['ignoreFirstLine'] = $ignoreFirstLine;

					$header = array();
					if ($ignoreFirstLine) {
						$header = $context['data'][0];
						$newCsvData = array();
						foreach ($context['data'] as $k => $d) {
							if ($k == 0) continue;
							else $newCsvData[] = $d;
						}
						$context['data'] = $newCsvData;
					} else {
						for ($i = 0; $i < sizeof($context['data'][0]); $i++) {
							$header[] = 'col_' . ($i + 1);
						}
					}
					$context['header'] = $header;

					if ($manualSuccess) {
						$notifier->addInfo(18);
					} else if (!$manualSuccess && $manuallySupplied) {
						$notifier->addWarning(1);
					} else {
						$notifier->addInfo(18);
					}
				} else {
					$context['state'] = 1;

					if ($manuallySupplied) {
						$notifier->addError(18);
					} else {
						$notifier->addError(26);
					}
				}
			break;
			case 'importthree':
				$context['fieldAssignments'] = $_POST['assign_field'];
				$context['addToGroup'] = format_userinput($_POST['add_to_group'], 'uint');
				$context['createRevision'] = format_userinput($_POST['create_revision'], 'uint');

				$dbCols_ = db_get_columns('ko_leute');
				$dbCols = array();
				foreach ($dbCols_ as $dbCol) {
					$dbCols[$dbCol['Field']] = $dbCol;
				}

				foreach ($context['fieldAssignments'] as $k => $field) {
					if (ko_leute_col_is_mappable($field)) {
						$context['mappings'][$k]['curr'] = $_POST['mappings'][$k];
						foreach ($context['mappings'][$k]['curr'] as $from => $to) {
							if (!in_array($to, $context['mappings'][$k]['possValues'])) {
								$context['mappings'][$k]['possValues'][] = $to;
								$context['mappings'][$k]['possDescs'][] = $to;
								$context['mappings'][$k]['possUserDescs'][] = array(strtolower($to));
							}
						}
					}
				}


				$transformedData = array();
				foreach ($context['data'] as $line) {
					$newLine = array();
					foreach ($line as $k => $value) {
						$column = $context['fieldAssignments'][$k];
						if ($column) {
							foreach ($dbCols as $dbCol) {
								if ($dbCol['Field'] == $column) {
									if ($dbCol['Type'] == 'date') {
										$newValue = ko_parse_date($value);
										if ($newValue === FALSE) $notifier->addWarning(2, $do_action, array($value));
										$value= $newValue ? $newValue : '';
									} else if ($dbCol['Type'] == 'datetime') {
										$newValue = ko_parse_date($value, 'datetime');
										if ($newValue === FALSE) $notifier->addWarning(2, $do_action, array($value));
										$value= $newValue ? $newValue : '';
									}
								}
							}
						}
						$newLine[$k] = $value;
					}
					$transformedData[] = $newLine;
				}
				$context['transformedData'] = $transformedData;

				$context['state'] = 3;

				// Check if there were fields assigned twice
				$assignedFields = array();
				foreach ($context['fieldAssignments'] as $k => $fieldAssignment) {
					if ($fieldAssignment && $assignedFields[$fieldAssignment]) $notifier->addError(27, $do_action, array($fieldAssignment));
					else $assignedFields[$fieldAssignment] = TRUE;
				}

				// Check if at least one field was assigned
				if (sizeof($assignedFields) < 1) $notifier->addError(28);

				if ($notifier->hasErrors()) {
					$context['state'] = 2;
				}
			break;
			case 'importfour':
				// Check if there were fields assigned twice
				$assignedFields = array();
				foreach ($context['fieldAssignments'] as $k => $fieldAssignment) {
					if ($fieldAssignment && $assignedFields[$fieldAssignment]) $notifier->addError(27, $do_action, array($fieldAssignment));
					else $assignedFields[$fieldAssignment] = TRUE;
				}

				// Check if at least one field was assigned
				if (sizeof($assignedFields) < 1) $notifier->addError(28);

				if ($notifier->hasErrors()) {
					$context['state'] = 2;
					break;
				}

				$dbCols_ = db_get_columns('ko_leute');
				$dbCols = array();
				foreach ($dbCols_ as $dbCol) {
					$dbCols[$dbCol['Field']] = $dbCol;
				}

				if (!is_array($access['groups'])) ko_get_access('groups');

				$addGroup = $context['addToGroup'];
				if ($addGroup && ($access['groups']['ALL'] > 1 || $access['groups'][$addGroup] > 1)) {
					$addGroup = ko_groups_decode($addGroup, 'full_gid');
				} else {
					$addGroup = '';
				}

				//Check for leute_admin_groups of current login and add all imported addresses to this group as well
				$gids = ko_get_leute_admin_groups($_SESSION['ses_userid'], 'all');
				if(is_array($gids) && sizeof($gids) > 0) {
					foreach($gids as $gid) {
						if(!$gid) continue;
						if($addGroup) $addGroup .= ','.$gid;
						else $addGroup = $gid;
					}
				}


				$doLdap = ko_do_ldap();
				if($doLdap) $ldap = ko_ldap_connect();


				$insertData = array();
				foreach ($context['data'] as $line) {
					$entry = array('groups' => $addGroup);
					foreach ($line as $k => $value) {
						$column = $context['fieldAssignments'][$k];

						if (!$column) continue;

						if (ko_leute_col_is_mappable($column)) {
							$mappedValue = $context['mappings'][$k]['curr'][$value];
							if (!$mappedValue) continue;

							if (substr($column, 0, strlen('MODULEgrp')) == 'MODULEgrp') {
								$gid = substr($column, -6);
								if ($access['groups']['ALL'] > 1 || $access['groups'][$gid] > 1) {
									$g = ko_groups_decode($gid, 'full_gid');
									if ($mappedValue != 'x') {
										$g .= ':'.$mappedValue;
									}

									if ($entry['groups']) $entry['groups'] .= ','.$g;
									else $entry['groups'] = $g;
								}
							} else {
								$entry[$column] = $mappedValue;
							}
						} else if (array_key_exists($column, $dbCols) && in_array($dbCols[$column]['Type'], array('date', 'datetime'))) {
							$dbCol = $dbCols[$column];
							if ($dbCol['Type'] == 'date') {
								$newValue = ko_parse_date($value);
								if ($newValue === FALSE) $notifier->addWarning(2, $do_action, array($value));
								$entry[$column] = $newValue ? $newValue : '';
							} else if ($dbCol['Type'] == 'datetime') {
								$newValue = ko_parse_date($value, 'datetime');
								if ($newValue === FALSE) $notifier->addWarning(2, $do_action, array($value));
								$entry[$column] = $newValue ? $newValue : '';
							}
						} else {
							if ($value) $entry[$column] = $value;
						}
					}
					$newGroups = implode(',', array_unique(explode(',', $entry['groups'])));
					unset($entry['groups']);

					//Trim all values
					foreach($entry as $k => $v) {
						$entry[$k] = trim($v);
					}

					$entry["crdate"] = date("Y-m-d H:i:s");
					$entry["cruserid"] = $_SESSION["ses_userid"];
					$newId = db_insert_data("ko_leute", $entry);

					ko_groups_get_savestring($newGroups, array("id" => $newId), $log, '');
					db_update_data('ko_leute', "WHERE `id` = '{$newId}'", array('groups' => $newGroups));
					$entry['groups'] = $newGroups;

					//Create LDAP entry
					if($doLdap) ko_ldap_add_person($ldap, $entry, $newId);

					if ($context['createRevision']) {
						// create revision entry
						$revision = array(
							'leute_id' => $newId,
							'crdate' => date('Y-m-d H:i:s'),
							'cruser' => $_SESSION['ses_userid'],
							'reason' => 'leute_import',
						);

						$revisionId = db_insert_data('ko_leute_revisions', $revision);
						$revision['id'] = $revisionId;

						ko_log_diff('new_leute_revision', $revision);
					}

					//Add log entry
					$logData = $entry;
					$logData['id'] = $newId;
					ko_log_diff('imported_address', $logData);
					unset($logData);
				}

				if($doLdap) ko_ldap_close($ldap);


				if (!$notifier->hasErrors()) {
					$notifier->addInfo(19, $do_action, array(sizeof($context['data'])));
					unset($_SESSION['leute_import']);
					$_SESSION['show'] = $_SESSION['show_back'] ? $_SESSION['show_back'] : 'show_all';
				} else {
					$context['state'] = 2;
				}
			break;
		}
	break;



	case "submit_leute_version":
		if($access['leute']['ALL'] < 4) break;

		if($_POST["date_version"] == "") {
			unset($_SESSION["leute_version"]);
		} else {
			$version = format_userinput($_POST["date_version"], "date");
			//empty date
			if(strtotime($version) == 0) break;
			//don't allow future dates
			if(strtotime($version) > time()) break;

			$_SESSION["leute_version"] = sql_datum($version);
		}

		$_SESSION["show"] = "show_all";
	break;  //submit_leute_version



	case "clear_leute_version":
		if($access['leute']['ALL'] < 4) break;

		unset($_SESSION["leute_version"]);
		$_SESSION["show"] = "show_all";
	break;  //clear_leute_version



	//Rollback to old version
	case "rollback":
		$vid = format_userinput($_GET["v"], "uint");
		if(!$vid) break;

		//Retrieve data from old version
		$version = db_select_data("ko_leute_changes", "WHERE `id` = '$vid'", "*", "", "", TRUE);
		if(!isset($version["id"])) break;
		$data = unserialize($version["changes"]);
		$df = unserialize($version["df"]);
		$leute_id = $version["leute_id"];


		//Access check
		if(!($access['leute']['ALL'] > 1 || $access['leute'][$leute_id] > 1)) break;


		//Backwards compatibility for smallgroups
		if(isset($data['kg']) || isset($data['kg_leiter'])) {
			$sg = array();
			foreach(explode(',', $data['kg']) as $kgid) {
				if(!$kgid) continue;
				$sg[] = $kgid.':M';
			}
			foreach(explode(',', $data['kg_leiter']) as $kgid) {
				if(!$kgid) continue;
				$sg[] = $kgid.':L';
			}
			$data['smallgroups'] = implode(',', $sg);
			unset($data['kg']);
			unset($data['kg_leiter']);
		}

		//Backwards compatibility for rota teams
		if(isset($data['dienst']) || isset($data['dienstleiter'])) {
			$grps = explode(',', $data['groups']);
			$role_team = ko_get_setting('rota_teamrole');
			$role_leader = ko_get_setting('rota_leaderrole');
			$all_teams = db_select_data('ko_rota_teams', 'WHERE 1');

			foreach(explode(',', $data['dienst']) as $did) {
				if(!$did) continue;
				//Ignore teams not available anymore
				if(!isset($all_teams[(int)$did])) continue;
				list($gid) = explode(',', $all_teams[(int)$did]['group_id']);
				$full_gid = ko_groups_decode($gid, 'full_gid');
				if($role_team && FALSE === strpos($full_gid, ':r')) $full_gid .= ':r'.$role_team;
				$grps[] = $full_gid;
			}

			foreach(explode(',', $data['dienstleiter']) as $did) {
				if(!$did) continue;
				//Ignore teams not available anymore
				if(!isset($all_teams[(int)$did])) continue;
				list($gid) = explode(',', $all_teams[(int)$did]['group_id']);
				$full_gid = ko_groups_decode($gid, 'full_gid');
				if($role_leader && FALSE === strpos($full_gid, ':r')) $full_gid .= ':r'.$role_leader;
				$grps[] = $full_gid;
			}

			$grps = array_unique($grps);
			$data['groups'] = implode(',', $grps);
			unset($data['dienst']);
			unset($data['dienstleiter']);
		}

		//Get current record
		ko_get_person_by_id($leute_id, $current);

		//Save version history
		ko_save_leute_changes($leute_id);

		//Update record
		db_update_data("ko_leute", "WHERE `id` = '$leute_id'", $data);

		//Update family members
		if($data['famid'] == $current['famid'] || (!$current['famid'] && $data['famid'])) {
			ko_update_familie($data['famid'], $data, $leute_id);
		} else {
			ko_update_leute_in_familie($data['famid']);
			ko_update_leute_in_familie($current['famid']);
		}

		//Create log entry
		ko_log_diff("edit_person", $data, $current);

		//Update datafields
		foreach($df as $dfid => $dfdata) {
			unset($dfdata["id"]);
			//Check for entry to update or insert a new one
			$cur = db_select_data("ko_groups_datafields_data", "WHERE `id` = '$dfid'", "*", "", "", TRUE);
			if($cur["id"] == $dfid) {
				db_update_data("ko_groups_datafields_data", "WHERE `id` = '$dfid'", $dfdata);
			} else {
				db_insert_data("ko_groups_datafields_data", $dfdata);
			}
		}
		//Delete datafields added since this version
		$dfdiff = array_diff(array_keys((array)$df_current), array_keys((array)$df));
		foreach($dfdiff as $dfid) {
			if(!$dfid) continue;
			db_delete_data("ko_groups_datafields_data", "WHERE `id` = '$dfid'");
		}

		//Update group counts for all assigned groups (old and new)
		$changed_groups = array_unique(array_merge(array_diff(explode(',', $data['groups']), explode(',', $current['groups'])), array_diff(explode(',', $current['groups']), explode(',', $data['groups']))));
		foreach($changed_groups as $fullgid) {
			$group = ko_groups_decode($fullgid, 'group');
			if(!$group['maxcount']) continue;
			ko_update_group_count($group['id'], $group['count_role']);
		}
	break;  //rollback




	case 'set_fastfilter':
		$_SESSION['filter'] = array();

		$fast_filter = ko_get_fast_filter();
		foreach($fast_filter as $id) {
			if($_POST['fastfilter'.$id]) {
				$_SESSION['filter'][] = array($id, array('', str_replace('*', '.*', format_userinput($_POST['fastfilter'.$id], 'text'))), 0);
			}
		}
		$_SESSION['show_start'] = 1;
		$_SESSION['show'] = 'show_all';
	break;



	case 'settings':
		if($access['leute']['MAX'] < 1) break;
		$_SESSION['show'] = 'settings';
	break;



	case 'submit_leute_settings':
		if($access['leute']['MAX'] < 1) break;

		ko_save_userpref($_SESSION['ses_userid'], 'default_view_leute', format_userinput($_POST['sel_leute'], 'js'));
		ko_save_userpref($_SESSION['ses_userid'], 'show_limit_leute', format_userinput($_POST['txt_limit_leute'], 'uint'));
		ko_save_userpref($_SESSION['ses_userid'], 'show_limit_kg', format_userinput($_POST['txt_limit_kg'], 'uint'));
		ko_save_userpref($_SESSION['ses_userid'], 'leute_sort_birthdays', format_userinput($_POST['sel_leute_sort_birthdays'], 'alpha'));
		ko_save_userpref($_SESSION['ses_userid'], 'hide_leute_filter', format_userinput($_POST['sel_hide_filter'], 'intlist'));
		ko_save_userpref($_SESSION['ses_userid'], 'leute_fast_filter', format_userinput($_POST['sel_fast_filter'], 'intlist'));
		ko_save_userpref($_SESSION['ses_userid'], 'leute_children_columns', format_userinput($_POST['sel_children_columns'], 'alphanumlist'));
		ko_save_userpref($_SESSION['ses_userid'], 'leute_kg_as_cols', format_userinput($_POST['sel_kg_as_cols'], 'uint'));
		ko_save_userpref($_SESSION['ses_userid'], 'show_passed_groups', format_userinput($_POST['chk_show_passed_groups'], 'uint'));
		ko_save_userpref($_SESSION['ses_userid'], 'group_shows_datafields', format_userinput($_POST['chk_group_shows_datafields'], 'uint'));
		//ko_save_userpref($_SESSION['ses_userid'], 'leute_fam_checkbox', format_userinput($_POST['chk_fam_checkbox'], 'uint'));
		ko_save_userpref($_SESSION['ses_userid'], 'leute_carddav_filter', format_userinput($_POST['sel_carddav_filter'], 'uint'));
		ko_save_userpref($_SESSION['ses_userid'], 'leute_list_persons_not_overlay', format_userinput($_POST['sel_list_persons_not_overlay'], 'alphanumlist'));

		$geb_plus = format_userinput($_POST['txt_geb_plus'], 'uint');
		$geb_plus = max(1, min(366, $geb_plus));
		ko_save_userpref($_SESSION['ses_userid'], 'geburtstagsliste_deadline_plus', $geb_plus);
		$geb_minus = format_userinput($_POST['txt_geb_minus'], 'uint');
		$geb_minus = max(1, min(366, $geb_minus));
		ko_save_userpref($_SESSION['ses_userid'], 'geburtstagsliste_deadline_minus', $geb_minus);

		$birthday_filter = $_POST['sel_birthday_filter'];
		if($birthday_filter == '') {
			ko_save_userpref($_SESSION['ses_userid'], 'birthday_filter', '');
		} else if($birthday_filter == -1) {
			//Don't touch, the current filter was set by a different user and no change has been made
		} else {
			$user_id = substr($birthday_filter, 0, 3) == '@G@' ? -1 : $_SESSION['ses_userid'];
			$key = substr($birthday_filter, 0, 3) == '@G@' ? substr($birthday_filter, 3) : $birthday_filter;
			$filter = ko_get_userpref($user_id, $key, 'filterset');
			$filter = array_pop($filter);
			if($filter['value']) ko_save_userpref($_SESSION['ses_userid'], 'birthday_filter', serialize($filter));
		}

		$v = format_userinput($_POST['sel_leute_force_family_firstname'], 'uint', FALSE, 1);
		if($v == 0 || $v == 1 || $v == 2) ko_save_userpref($_SESSION['ses_userid'], 'leute_force_family_firstname', $v);

		if($access['leute']['ALL'] > 2) {
			ko_set_setting('leute_real_delete', format_userinput($_POST['chk_real_delete'], 'uint'));
			ko_set_setting('leute_delete_revision_address', format_userinput($_POST['chk_delete_revision_address'], 'uint'));
			ko_set_setting('leute_no_delete_columns', format_userinput($_POST['sel_no_delete_columns'], 'alphanumlist'));
			ko_set_setting('leute_assign_global_notification', format_userinput($_POST['txt_assign_global_notification'], 'email', FALSE, 0, array(), ','));
			ko_set_setting('leute_allow_moderation', format_userinput($_POST['chk_allow_moderation'], 'uint'));
			ko_set_setting('leute_allow_import', format_userinput($_POST['chk_allow_import'], 'uint'));
			ko_set_setting('leute_disable_aa_fm', format_userinput($_POST['chk_disable_aa_fm'], 'uint'));
			ko_set_setting('candidate_adults_min_age', format_userinput($_POST['txt_candidate_adults_min_age'], 'uint'));
			ko_set_setting('leute_multiple_delete', format_userinput($_POST['chk_multi_delete'], 'uint'));
			ko_set_setting('leute_information_lock', format_userinput($_POST['chk_leute_information_lock'], 'uint'));

			kota_save_mandatory_fields('ko_leute', $_POST);
		}


		$_SESSION['show'] = 'show_all';
	break;



	case 'global_assign':
		if(!ko_get_leute_admin_assign($_SESSION['ses_userid'], 'all')) break;
		$gid = ko_get_leute_admin_groups($_SESSION['ses_userid'], 'all');
		if(!is_array($gid) || sizeof($gid) < 1) break;

		$lid = format_userinput($_POST['global_assign'], 'uint');
		$gid = array_shift($gid);
		$full_gid = ko_groups_decode($gid, 'full_gid');
		$group = ko_groups_decode($gid, 'group');

		//Update address
		$address = db_select_data('ko_leute', "WHERE `id` = '$lid'", '*', '', '', TRUE);
		$groups = explode(',', $address['groups']);
		$groups[] = $full_gid;
		$groups = array_unique($groups);
		ko_save_leute_changes($lid, $address);
		db_update_data('ko_leute', "WHERE `id` = '$lid'", array('groups' => implode(',', $groups)));

		$address_text = '';
		if($address['firm']) $address_text .= $address['firm']."\n";
		if($address['vorname'] || $address['nachname']) $address_text .= $address['vorname'].' '.$address['nachname']."\n";
		if($address['adresse']) $address_text .= $address['adresse']."\n";
		if($address['adresse_zusatz']) $address_text .= $address['adresse_zusatz']."\n";
		if($address['plz'] || $address['ort']) $address_text .= $address['plz'].' '.$address['ort']."\n";

		//Send notification email
		$email = ko_get_setting('leute_assign_global_notification');
		if($email) {
			//Prepare email text
			$subject = getLL('email_subject_prefix').sprintf(getLL('leute_global_assign_notification_subject'), $login);
			$text = sprintf(getLL('leute_global_assign_notification_text'), $login, $group['name']);
			$text .= "\n\n".$address_text;
			$text .= "\n\n".getLL('leute_global_assign_notification_text_disclaimer');

			//Send notification email to all recipients
			$email = str_replace(';', ',', $email);
			foreach(explode(',', $email) as $e) {
				$e = trim($e);
				if(!check_email($e)) continue;
				ko_send_mail(
					'',
					$e,
					$subject,
					$text
				);
			}
		}

		$notifier->addInfo(16, $do_action);
	break;



	//Default:
	default:
		if(!hook_action_handler($do_action))
      include($ko_path."inc/abuse.inc");
	break;

}//switch(action)


//HOOK: Plugins erlauben, die bestehenden Actions zu erweitern
hook_action_handler_add($do_action);


//Reread access rights (only needed if leute_admin_filter is set (d.h. access[leute] contains more than MAX, ALL, GS))
//Reread access rights (only needed if leute_admin_filter is set (d.h. access[leute] contains more than MAX, ALL, GS))
if(sizeof($access['leute']) > 3 && in_array($do_action, array('submit_neue_person', 'submit_edit_person', 'submit_als_neue_person', 'submit_multiedit', 'submit_gs', 'submit_gs_aa', 'submit_gs_new_person', 'submit_gs_ps', 'submit_gs_ps_aa', 'rollback', 'global_assign'))) {
	ko_get_access('leute', '', TRUE);
}


// If we are handling a request that was redirected by /inc/form.php, then exit here
if ($asyncFormSubmit == 1) {
	throw new Exception('async-form-submit-dummy-exception');
}


//***Default-Settings auslesen, falls in dieser Session noch nicht gesetzt
if($_SESSION["show_leute_cols"] == "") {
	$show_leute_cols_string = ko_get_userpref($_SESSION["ses_userid"], "show_leute_cols");
	if($show_leute_cols_string) {
		$_SESSION["show_leute_cols"] = explode(",", $show_leute_cols_string);
	} else {
		$_SESSION["show_leute_cols"] = explode(",", ko_get_setting("show_leute_cols"));
	}
}

if(!isset($_SESSION['kota_show_cols_ko_kleingruppen'])) {
	$show_kg_cols_string = ko_get_userpref($_SESSION['ses_userid'], 'kota_show_cols_ko_kleingruppen');
	$_SESSION['kota_show_cols_ko_kleingruppen'] = explode(',', $show_kg_cols_string);
}

if($_SESSION["show_leute_chart"] == "") {
	$_SESSION["show_leute_chart"] = $LEUTE_CHART_TYPES;
}
//Set default stats view to number of addresses
if(!$_SESSION['leute_chart_statistics']) {
	$_SESSION['leute_chart_statistics'] = 'addresses';
}

$_SESSION["show_limit"] = ko_get_userpref($_SESSION["ses_userid"], "show_limit_leute");
if(!$_SESSION["show_limit"]) $_SESSION["show_limit"] = ko_get_setting("show_limit_leute");

if(!$_SESSION["show_start"]) {
	$_SESSION["show_start"] = 1;
}

$_SESSION["show_kg_limit"] = ko_get_userpref($_SESSION["ses_userid"], "show_limit_kg");
if(!$_SESSION["show_kg_limit"]) $_SESSION["show_kg_limit"] = ko_get_setting("show_limit_kg");

if(!$_SESSION["show_kg_start"]) {
	$_SESSION["show_kg_start"] = 1;
}

if($_SESSION["sort_leute"][0] == "") {
	$_SESSION["sort_leute"][] = "nachname";
}
if($_SESSION["sort_leute_order"][0] == "") {
	$_SESSION["sort_leute_order"][] = "ASC";
}
if($_SESSION["sort_kg"] == "") {
	$_SESSION["sort_kg"] = "name";
}
if($_SESSION["sort_kg_order"] == "") {
	$_SESSION["sort_kg_order"] = "ASC";
}
if($_SESSION["sort_geburtstage"] == "") {
	$_SESSION["sort_geburtstage"] = "deadline";
}
if(!$_SESSION["filter_akt"]) {
	$hidden_filter = explode(",", ko_get_userpref($_SESSION["ses_userid"], "hide_leute_filter"));
	ko_get_filters($f_, "leute");
	$all_filter = array_keys($f_);
	$shown_filter = array_diff($all_filter, $hidden_filter);
	$first_shown = array_shift($shown_filter);
	$first_all = array_shift($all_filter);
	$_SESSION["filter_akt"] = $first_shown ? $first_shown : $first_all;
}
if(!$_SESSION["show_adressliste_cols"]) {
	$_SESSION["show_adressliste_cols"] = $LEUTE_ADRESSLISTE;
}

if(!isset($_SESSION["my_list"])) {
	if(ko_check_userpref($_SESSION["ses_userid"], "leute_my_list")) {
		$_SESSION['my_list'] = (array)unserialize(ko_get_userpref($_SESSION['ses_userid'], 'leute_my_list'));
	} else {
		$_SESSION["my_list"] = array();
	}
	foreach($_SESSION['my_list'] as $k => $v) if(!$v) unset($_SESSION['my_list'][$k]);
}
if(!$_SESSION["filter"]["link"]) $_SESSION["filter"]["link"] = "and";

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

print ko_include_css(array($ko_path.'inc/chartist/kool-chartist.min.css', $ko_path.'inc/CalendarHeatmap/jquery.CalendarHeatmap.min.css'));

print ko_include_js(
	array(
		$ko_path.'inc/CalendarHeatmap/jquery.CalendarHeatmap.min.js',
		$ko_path.'inc/selectmenu.js',
		$ko_path.'inc/chartist/chartist.js',
		$ko_path.'inc/chartist/plugins/chartist-plugin-legend.js',
		$ko_path.'inc/chartist/plugins/chartist-plugin-tooltip.js',
	), ($_SESSION['show'] == 'list_kg' ? 'kg' : $ko_menu_akt)
);

include($ko_path.'inc/js-sessiontimeout.inc');
include("inc/js-leute.inc");

//prepare group select for formular and group filter
$list_id = 1;

if (ko_module_installed('crm') && in_array($_SESSION['show'], array('etiketten_optionen', 'mobil_versand', 'xls_settings', 'email_versand'))) {
	include($ko_path.'crm/inc/js-selproject.inc');
}
if($_SESSION["show"] == "neue_person" || $_SESSION["show"] == "edit_person") {
	// This code is copied in /inc/form.php (marked by **LEUTE_INDEX_SNIPPET_1**)

	$show_all_types = FALSE;
	$includeMaxedGroups = FALSE;
	//Beim Einteilen die vergangenen Gruppen nie anzeigen
	$orig_value = ko_get_userpref($_SESSION['ses_userid'], 'show_passed_groups');
	ko_save_userpref($_SESSION['ses_userid'], 'show_passed_groups', 0);
	include("inc/js-groupmenu.inc");
	ko_save_userpref($_SESSION['ses_userid'], 'show_passed_groups', $orig_value);
	$loadcode = "initList($list_id, $('.groupselect.groupselect-left')[0]);";
	$onload_code = $loadcode.$onload_code;
} else {
	ko_get_filter_by_id($_SESSION["filter_akt"], $akt_filter);
	if($akt_filter['_name'] == 'group') {
		$loadcode = "initList($list_id, document.getElementsByName('sel1-var1')[0]);";
		$onload_code = $loadcode.$onload_code;
	}
	//Beim Filter die vergangenen Gruppen gemäss Einstellung zeigen
	//allerdings auch Platzhalter-Gruppen anzeigen
	$show_all_types = TRUE;
	$includeMaxedGroups = TRUE;
	include("inc/js-groupmenu.inc");
}
?>
</head>

<body onload="session_time_init();<?php if(isset($onload_code)) print $onload_code; ?>">

<?php
/*
 * Gibt bei erfolgreichem Login das Menü aus, sonst einfach die Loginfelder
 */
include($ko_path . "menu.php");

ko_get_outer_submenu_code('leute');
?>


<!-- Hauptbereich -->
<main class="main">
<form action="index.php" method="post" name="formular" enctype="multipart/form-data">
<input type="hidden" name="action" id="action" value="" />
<input type="hidden" name="id" id="id" value="" />
<div name="main_content" id="main_content">

<?php

if ($notifier->hasNotifications(koNotifier::ALL)) {
	$notifier->notify();
}

hook_show_case_pre($_SESSION["show"]);

switch($_SESSION["show"]) {
	case "show_all":
		ko_list_personen();
	break;

	case "show_adressliste":
		ko_list_personen("adressliste");
	break;

	case "show_my_list":
		ko_list_personen("my_list");
	break;

	case "geburtstagsliste":
		//ko_list_geburtstage();
		ko_list_personen('birthdays');
	break;

	case "single_view":
		ko_leute_show_single($single_id);
	break;

	case "neue_person":
		ko_formular_leute("neu");
	break;

	case "edit_person":
		ko_formular_leute('edit', format_userinput($_POST['id'], 'uint'), ($hide_save_as_new === TRUE ? FALSE : TRUE));
	break;

	case "etiketten_optionen":
		$smarty->assign("help", ko_get_help('leute', 'labels'));
		$smarty->display("ko_formular_etiketten.tpl");
	break;

	case "mailmerge":
		$smarty->assign('label_title', getLL('leute_mailmerge_title').' ('.sizeof($_SESSION['mailmerge_data']).')');
		$smarty->assign('label_title_reuse', getLL('leute_mailmerge_title_reuse'));
		$smarty->assign('label_title_new', getLL('leute_mailmerge_title_new'));
		$smarty->assign('label_title_legend', getLL('leute_mailmerge_title_legend'));
		$smarty->assign('label_title_invalid', getLL('leute_mailmerge_title_invalid'));
		$smarty->assign('label_invalid_addresses', getLL('leute_mailmerge_invalid_addresses'));
		$smarty->assign('label_export_to_mylist', getLL('leute_mailmerge_label_export_to_mylist'));
		$smarty->assign('label_reuse_letter', getLL('leute_mailmerge_reuse_letter'));
		$smarty->assign('label_confirm_reuse', getLL('leute_mailmerge_confirm_reuse'));
		$smarty->assign('label_preset', getLL('leute_mailmerge_preset'));
		$smarty->assign('label_opening', getLL('leute_mailmerge_label_opening'));
		$smarty->assign('label_opening_formal', getLL('leute_mailmerge_label_opening_formal'));
		$smarty->assign('label_opening_informal', getLL('leute_mailmerge_label_opening_informal'));
		$smarty->assign('label_subject', getLL('leute_mailmerge_label_subject'));
		$smarty->assign('label_sender', getLL('leute_mailmerge_label_sender'));
		$smarty->assign('label_text', getLL('leute_mailmerge_label_text'));
		$smarty->assign('label_enlarge_right', getLL('leute_mailmerge_text_enlarge_right'));
		$smarty->assign('label_enlarge_down', getLL('leute_mailmerge_text_enlarge_down'));
		$smarty->assign('label_closing', getLL('leute_mailmerge_label_closing'));
		$smarty->assign('label_signature', getLL('leute_mailmerge_label_signature'));
		$smarty->assign('label_sig_file', getLL('leute_mailmerge_label_sig_file'));
		$smarty->assign('label_chk_sig_file', getLL('leute_mailmerge_label_chk_sig_file'));
		$smarty->assign('label_submit', getLL('leute_mailmerge_submit'));
		$smarty->assign('label_cancel', getLL('cancel'));
		$smarty->assign('tpl_cancel', 'show_all');
		$smarty->assign('sesid', session_id());
		$smarty->assign('help', ko_get_help('leute', 'mailmerge'));
		$smarty->display("ko_mailmerge.tpl");
	break;

	case "mobil_versand":
		$smarty->assign("tpl_send_mobilemessage", getLL('leute_mobilemessage_send'));
		$smarty->assign("tpl_receiver", getLL('leute_sms_receiver'));
		$smarty->assign("tpl_sms_bal", getLL('leute_sms_balance'));
		$smarty->assign('tpl_sms_sender', getLL('leute_sms_sender'));
		$smarty->assign("tpl_sms_add_receiver", getLL('leute_sms_add_receiver'));
		$smarty->assign("tpl_sms_no_number", getLL('leute_sms_no_number'));
		$smarty->assign("tpl_sms_excel_file", getLL('leute_sms_excel_file'));
		$smarty->assign("tpl_sms_my_export", getLL('leute_sms_my_export'));
		$smarty->assign("tpl_sms_my_add", getLL('leute_sms_my_add'));
		$smarty->assign("tpl_sms_text", getLL('leute_sms_text'));
		$smarty->assign("tpl_sms_submit", getLL('leute_sms_submit'));
		$smarty->display("ko_formular_mobilemessage.tpl");
	break;

	case "email_versand":
		ko_formular_leute_mailing();
	break;

	case "export_pdf":
		ko_export_leute_as_pdf_settings($layout_id);
	break;

	case "mutationsliste":
		ko_list_mod_leute();
	break;

	case "groupsubscriptions":
		ko_list_groupsubscriptions();
	break;

	case "list_kg":
		ko_list_kg();
	break;

	case "chart_kg":
		print ko_kg_chart();
	break;

	case "neue_kg":
		ko_kg_formular("neu");
	break;

	case "edit_kg":
		ko_kg_formular("edit", $edit_id);
	break;

	case "multiedit":
		ko_multiedit_formular("ko_leute", $do_columns, $do_ids, $order, array("cancel" => "show_all"));
	break;

	case "multiedit_kg":
		ko_multiedit_formular("ko_kleingruppen", $do_columns, $do_ids, $order, array("cancel" => "list_kg"));
	break;

	case "import":
		ko_leute_import($_SESSION['leute_import']);
	break;  //import

	case "chart":
		print ko_leute_chart();
	break;

	case 'settings':
		ko_leute_settings();
	break;

	case 'xls_settings':
		ko_leute_export_xls_settings();
	break;

	case 'leute_revisions':
		ko_list_leute_revisions();
	break;

	case 'export_details_settings':
		ko_leute_export_details_settings();
	break;

	default:
		//HOOK: Plugins erlauben, neue Show-Cases zu definieren
		hook_show_case($_SESSION["show"]);
	break;
}//switch(show)

//HOOK: Plugins erlauben, neue Show-Cases zu definieren
hook_show_case_add($_SESSION["show"]);

?>
</div>
</form>
</main>

</div>

<?php include($ko_path . "footer.php"); ?>

</body>
</html>
