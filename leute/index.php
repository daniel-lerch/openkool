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

ob_start();  //Ausgabe-Pufferung einschalten

$ko_path = "../";
$ko_menu_akt = "leute";

include($ko_path . "inc/ko.inc.php");
include("inc/leute.inc.php");

//get notifier instance
$notifier = koNotifier::Instance();

//Redirect to SSL if needed
ko_check_ssl();

if(!ko_module_installed("leute")) {
	header("Location: ".$BASE_URL."index.php");  //Absolute URL
}

ob_end_flush();

ko_get_access('leute');
if(ko_module_installed('kg')) ko_get_access('kg');
if(ko_module_installed('groups')) {
	ko_get_access('groups');
	ko_get_groups($all_groups);
}

//Smarty-Templates-Engine laden
require("$ko_path/inc/smarty.inc.php");



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
	unset($_SESSION['leute_export_xls_post']);
	$_POST['action'] = 'leute_action';
	$_POST['id'] = 'excel';
	$_POST['from_settings'] = 'true';

	// save userprefs (if necessary)
	if ($_POST['sel_children_columns'] != '') {
		ko_save_userpref($_SESSION['ses_userid'], 'leute_children_columns', format_userinput($_POST['sel_children_columns'], 'alphanumlist'));
	}
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
		//Find first fast filter to set focus on it
		$fast_filter = ko_get_fast_filter();
		$first_fast_filter = array_shift($fast_filter);
		$onload_code = "form_set_focus('fastfilter$first_fast_filter');".$onload_code;
	break;


	case "show_adressliste":
		$_SESSION["show"] = "show_adressliste";
		$_SESSION["show_start"] = 1;
		//Find first fast filter to set focus on it
		$fast_filter = ko_get_fast_filter();
		$first_fast_filter = array_shift($fast_filter);
		$onload_code = "form_set_focus('fastfilter$first_fast_filter');".$onload_code;
	break;


	case "show_geburtstagsliste":
		if($access['leute']['MAX'] < 1) continue;
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
		if($access['leute']['MAX'] < 2) continue;
		$_SESSION['show'] = $_SESSION['show_back'] = 'mutationsliste';
	break;

	case "show_groupsubscriptions":
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
		//Beim Editieren auf korrekte Leute-ID prüfen
		if($do_action == "submit_edit_person") {
			if(!$_POST["leute_id"] || !format_userinput($_POST["leute_id"], "uint", TRUE, 10)) continue;
			$leute_id = format_userinput($_POST["leute_id"], "uint");
			ko_get_person_by_id($leute_id, $person);
			$action = "submit_edit_person";
		}
		//New person or "save as new person" for an existing one
		else if($do_action == "submit_als_neue_person" || $do_action == "submit_neue_person") {
			$action = "submit_neue_person";
			$leute_id = -1;
		  	//Almost everything as when editing, but a new LDAP entry has to be created
			$ldap_new_entry = TRUE;
		}
		else break;
		

		if($access['leute']['MAX'] <= 1 && (ko_get_setting("login_edit_person") == 0 || $leute_id == ko_get_logged_in_id())) break;

		//Funktion (kundenspezifisch) einlesen, die Variablen speziell behandeln lässt
		if(file_exists($ko_path."leute/inc/my_fcn_leute.inc")) {
			include($ko_path."leute/inc/my_fcn_leute.inc");
		}
		$log_message = "";


		//LDAP
		$do_ldap = ko_do_ldap();


		//Datenbank-Spalten auslesen
		$leute_cols = db_get_columns("ko_leute");
		$col_namen = ko_get_leute_col_name();

		//get the cols, for which this user has edit-rights (saved in allowed_cols[edit])
		$allowed_cols = ko_get_leute_admin_spalten($_SESSION["ses_userid"], "all", $leute_id);
		//add cols of MODULES, if allowed
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
				$save_famid = db_insert_data("ko_familie", array(
					'famfirstname' => format_userinput($_POST['input_famfirstname'], 'text'),
					'famlastname' => format_userinput($_POST['input_famlastname'], 'text')
				));
				$new_familie = TRUE;
			} else {
				$save_famid = format_userinput($_POST["sel_familie"], "uint");
			}

			//Familie geändert
			if($save_famid != $old_famid) {
				$log_message .= getLL("leute_log_family").": $old_famid --> $save_famid, ";

				//Alte Familie löschen, falls keine Mitglieder mehr
				if($old_famid != 0) {
					$num = ko_get_personen_by_familie($old_famid, $asdf);
					if($num <= 1) {
						db_update_data('ko_leute', "WHERE `famid` = '$old_famid'", array('famid' => '0', 'kinder' => '0'));
						db_delete_data('ko_familie', "WHERE `famid` = '$old_famid'");
					}
				}
			}
		}//if(in_array(famid, allowed_cols[edit]))
		else {
			$save_famid = $person['famid'];
		}

		//Gleiche Familie oder eine neue, dann Familiendaten speichern
		if($save_famid == $old_famid || $new_familie) {
			$familien_cols = db_get_columns('ko_familie');
			foreach($familien_cols as $col_) {
				$col = $col_['Field'];
				if($col && in_array($col, $LEUTE_TEXTSELECT) && $_POST['input_'.$col.'_2']) {
					$fam_data[$col] = format_userinput($_POST['input_'.$col.'_2'], 'text');
				} else if($col && isset($_POST['input_'.$col])) {
					$fam_data[$col] = format_userinput($_POST['input_'.$col], 'text');
				}
			}
			$do_update_familie = TRUE;
		}//if..else(save_famid != old_famid)



		foreach($leute_cols as $c) {
	    	if(in_array($c["Field"], $LEUTE_EXCLUDE)) continue;
			//don't save not allowed columns (edit)
			if(is_array($allowed_cols["edit"])) {
				if(!in_array($c["Field"], $allowed_cols["edit"])) continue;
			} else if(is_array($allowed_cols['view'])) {
				if(!in_array($c["Field"], $allowed_cols["view"])) continue;
			}

			// Remove type details e.g. 'tinyint(4) unsigned' to 'tinyint'
			$endpos = strpos($c["Type"], "(") ? strpos($c["Type"], "(") : strlen($c["Type"]);
			$input_type = substr($c["Type"], 0, $endpos);

			switch($input_type) {

				case "varchar": //Textfeld
				case "tinyint":
				case "smallint":
				case "mediumint":
				case "blob":
				case "enum":
				case "text":
					if(in_array($c["Field"], $LEUTE_TEXTSELECT) && $_POST["input_".$c["Field"]."_2"]) {
						$data[$c["Field"]] = format_userinput($_POST["input_".$c["Field"]."_2"], "text");
					}
					else if(in_array($c["Field"], $LEUTE_ENUMPLUS) && $_POST["input_".$c["Field"]."_2"]) {
						//Enum-Definition neu erstellen
						$options = db_get_enums("ko_leute", $c["Field"]);
						$options[] = format_userinput($_POST["input_".$c["Field"]."_2"], "text");
						sort($options);
						reset($options);
						$enum_code = "";
						foreach($options as $o) {
							$enum_code .= "'$o', ";
						}
						$enum_code = substr($enum_code, 0, -2);
						db_alter_table("ko_leute", "CHANGE `".$c["Field"]."` `".$c["Field"]."` ENUM( $enum_code ) DEFAULT NULL ");

						if(function_exists("my_enumplus")) {
							my_enumplus($c["Field"], format_userinput($_POST["input_".$c["Field"]], "text"), format_userinput($_POST["input_".$c["Field"]."_2"], "text"));
						}

						$data[$c["Field"]] = format_userinput($_POST["input_".$c["Field"]."_2"], "text");
					} else if (in_array($c['Field'], $LEUTE_CHECKBOXES) && empty($_POST['input_' . $c['Field']])) {
						$data[$c['Field']] = '0'; // Unchecked checkboxes are not submitted in POST requests
					} else if($KOTA['ko_leute'][$c['Field']]['form']['type'] == 'peoplesearch') {
						$data[$c['Field']] = format_userinput($_POST['input_'.$c['Field']], 'intlist');
					} else {
						$data[$c["Field"]] = format_userinput($_POST["input_".$c["Field"]], "text");
					}
				break;

				case "date":
					$data[$c["Field"]] = sql_datum($_POST['input_' . $c['Field']]);
				break;

			}//switch(input_type)


			//Kunden-spezifische Behandlung der Leute-Felder
			if(function_exists("my_fcn_leute")) {
				my_fcn_leute($c["Field"], format_userinput($_POST["input_".$c["Field"]], "text"), format_userinput($_POST["input_".$c["Field"]."_2"], "text"));
			}


			//Log-Message
			if(!$dont_log) {
				if($action == "submit_neue_person") {  //Alle gemachten Angaben loggen
					if($_POST["input_".$c["Field"]]) {
						$label = $col_namen[$c["Field"]];
						$value = format_userinput($_POST["input_".$c["Field"]], "text");
					} else if($_POST["input_".$c["Field"]."_2"]) {
						$label = $col_namen[$c["Field"]];
						$value = format_userinput($_POST["input_".$c["Field"]."_2"], "text");
					}
					if($value) $log_message .= "$label: '$value', ";

				} else {  //Bei Editieren nur die loggen, die geändert wurden
					$new  = format_userinput($_POST["input_".$c["Field"]], "text");
					$new2 = format_userinput($_POST["input_".$c["Field"]."_2"], "text");
					$old  = $c["Type"] == "date" ? map_leute_daten($person[$c["Field"]], $c["Field"], $person) : $person[$c["Field"]];
					if( ($old || $new) && $new != $old) {
						$ll_old = getLL('kota_ko_leute_'.$c['Field'].'_'.$old);
						$ll_new = getLL('kota_ko_leute_'.$c['Field'].'_'.$new);
						$log_message .= $col_namen[$c["Field"]].": '".($ll_old ? $ll_old : $old)."' --> '".($ll_new ? $ll_new : $new)."', ";
					} else if($new2 && $old != $new2) {
						$log_message .= $col_namen[$c["Field"]].": '$new2', ";
					}
				}
			}//if(!dont_log)
		}//foreach(leute_cols as c_i => c)


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
		} else { // submit_neue_person
			$data['crdate'] = date('Y-m-d H:i:s');
			$data['cruserid'] = $_SESSION['ses_userid'];
			$leute_id = db_insert_data("ko_leute", $data);
		}


		//Familien-Daten
		//Erst nachher updaten, damit Personendaten nicht Familiendaten überschreiben
		if($save_famid > 0 || $old_famid > 0) {
			if($do_update_familie) ko_update_familie($save_famid, $fam_data, $leute_id);
			else {
				ko_update_leute_in_familie($save_famid);
				ko_update_leute_in_familie($old_famid);
			}
			ko_update_familie_filter();
		}

		// Save group fields and smallgroups after inserting new entities
		foreach ($leute_cols as $c) {
			if(in_array($c["Field"], $LEUTE_EXCLUDE)) continue;
			//don't save not allowed columns (edit)
			if(is_array($allowed_cols["edit"])) {
				if(!in_array($c["Field"], $allowed_cols["edit"])) continue;
			} else if(is_array($allowed_cols['view'])) {
				if(!in_array($c["Field"], $allowed_cols["view"])) continue;
			}

			$dont_log = FALSE;

			//Groups-Modul
			if($c["Field"] == "groups") {
				//Nötige rechte Checken
				$go_on = (ko_module_installed("groups") && $access['groups']['MAX'] > 1);
				if(!$go_on) continue;
				ko_groups_get_savestring($_POST["input_groups"], array("id" => $leute_id), $log, $person["groups"]);
				//Store current datafields for versioning (stored with ko_save_leute_changes())
				$datafields = ko_get_datafields($leute_id);
				//Store datafields in DB
				ko_groups_save_datafields($_POST["group_datafields"], array("id" => $leute_id, "groups" => $_POST["input_groups"], "old_groups" => $person["groups"]), $log2);
				//Log-Message:
				$dont_log = TRUE;
				if(trim($log) != "") $log_message .= getLL("leute_log_groups").": $log";
				if(trim($log2) != "") $log_message .= getLL("leute_log_datafields").": $log2";
			}
			
			//Small groups
			if($c['Field'] == 'smallgroups') {
				$go_on = (ko_module_installed('kg') && $access['kg']['MAX'] > 2);
				if(!$go_on) continue;
				$bisher = $person[$c['Field']];
				$submitted = $_POST['input_'.$c['Field']];
				$dont_log = TRUE;
				if( ($bisher || $submitted) && $bisher != $submitted ) {
					$log_message .= $col_namen[$c['Field']].': '.ko_kgliste($bisher).' --> '.ko_kgliste($submitted).', ';
				}
			}
		}


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
			} else { // submit_neue_person
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
					$sender = ko_get_logged_in_person();
					foreach($_POST['sel_announce_changes'] as $lid) {
						$lid = (int)$lid;
						if(!$lid) continue;

						$person = ko_get_logged_in_person($lid);
						$text = sprintf(getLL('leute_announce_changes_email_text'), $sender['vorname'].' '.$sender['nachname'], $name)."\n\n".str_replace(',', "\n", substr($log_message, 0, -2));
						ko_send_mail(array($sender['email'] => $sender['vorname'] . ' ' . $sender['nachname']), $person['email'], getLL('leute_announce_changes_email_subject'), $text);
					}
				}//if(POST[sel_announce_changes])

			}//if(log_message)
			if (!$notifier->hasErrors()) {
				$notifier->addInfo(2, $do_action);
			}
		}

		//Set show case
		$_SESSION["show"] = $_SESSION["show_back"] ? $_SESSION["show_back"] : "show_all";
	break;



	//Bearbeiten
	case "edit_person":
		if($action_mode == 'POST') $leute_id = format_userinput($_POST['id'], 'uint');
		else if($action_mode == 'GET') $leute_id = format_userinput($_GET['id'], 'uint');
		if($access['leute']['ALL'] > 1 || $access['leute'][$leute_id] > 1 || (ko_get_setting('login_edit_person') == 1 && $leute_id == ko_get_logged_in_id())) {} else continue;

		$_SESSION["show_back"] = $_SESSION["show"];
		$_POST['id'] = $leute_id;
		$_SESSION["show"] = "edit_person";
	break;



	//Save multpile people in family
	case "join_in_family":
		$allowed_cols = ko_get_leute_admin_spalten($_SESSION['ses_userid'], 'all');
		if(!is_array($allowed_cols["edit"]) || in_array("famid", $allowed_cols["edit"])) {
			//add all selected people to family
			$do_ids = array();
			foreach($_POST["chk"] as $c_i => $c) {
				if($c) {
					if(FALSE === ($edit_id = format_userinput($c_i, "uint", TRUE))) {
						trigger_error("Not allowed join_in_familiy_id: ".$c_i, E_USER_ERROR);
					}
					if($access['leute']['ALL'] > 0 || $access['leute'][$edit_id] > 0) $do_ids[] = $edit_id;
				}
			}
			$got_data = FALSE;
			$fam_leute = NULL;
			$_fam_data = array();
			$familien_cols = db_get_columns("ko_familie");
			$pcounter = 0;
			foreach($do_ids as $id) {
				if(!$id) continue;
				//don't override family-ids
				ko_get_person_by_id($id, $p);
				if($p["famid"] != 0) continue;

				//Save fam-data
				foreach($familien_cols as $col_) {
					$col = $col_["Field"];
					if($col) $_fam_data[$pcounter][$col] = $p[$col];
				}
				$fam_leute[] = $id;
				$pcounter++;
			}

			$max = 0;
			$famkey = -1;
			foreach($_fam_data as $k => $d) {
				$dc = 0; foreach($d as $v) if($v) $dc++;
				if($dc > $max) {
					$max = $dc;
					$famkey = $k;
				}
			}
			$fam_data = $_fam_data[$famkey];

			//New fam-id and members
			if(sizeof($fam_leute) > 0 && sizeof($fam_data) > 0) {
				$save_famid = db_insert_data("ko_familie", array("nachname" => ""));
				foreach($fam_leute as $id) {
					db_update_data("ko_leute", "WHERE `id` = '$id'", array("famid" => $save_famid));
				}
				//save in fam and update members
				ko_update_familie($save_famid, $fam_data);

				//update filter
				ko_update_familie_filter();
			}

			//TODO: Set famfunction if empty according to gender and age

		}//if(allowed)

	break;




	case 'merge_duplicates':
		//TODO: Merge history of both address records?
		if($access['leute']['MAX'] < 3) continue;

		//Find marked persons and test for access level 3 (edit and delete)
		$do_ids = array();
		foreach($_POST['chk'] as $c_i => $c) {
			if(!$c) continue;
			$dup_id = format_userinput($c_i, 'uint');
			if($dup_id > 0 && ($access['leute']['ALL'] > 2 || $access['leute'][$dup_id] > 2)) $do_ids[] = $dup_id;
		}
		if(sizeof($do_ids) <= 0) {
			$notifier->addError(5, $do_action);
			continue;
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
		if(sizeof($dups) <= 0) continue;

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

		$cols = db_get_columns('ko_leute_mod');
		$mod_cols = array();
		foreach($cols as $col) {
			$mod_cols[] = $col['Field'];
		}

		$showMutations = FALSE;
		foreach($duplicates as $ids) {
			if(sizeof($ids) < 2) continue;
			$groups = $smallgroups = $num_fields = array();
			foreach($ids as $id) {
				ko_get_person_by_id($id, $person);
				foreach($person as $k => $v) {
					if(in_array($k, $LEUTE_EXCLUDE)) continue;
					if($k == 'groups') {
						$groups = array_merge($groups, explode(',', $v));
						$num_fields[$id] += sizeof(explode(',', $v));
					} else if($k == 'smallgroups') {
						$smallgroups = array_merge($smallgroups, explode(',', $v));
						$num_fields[$id] += sizeof(explode(',', $v));
					} else {
						if((string)$v != '' && (string)$v != '0' && (string)$v != '0000-00-00') $num_fields[$id]++;
					}
				}
			}
			arsort($num_fields);
			$first = TRUE;
			foreach($num_fields as $id => $num) {
				if($first) {
					$first = FALSE;

					//Get address record to be kept
					$merged_id = $id;
					ko_get_person_by_id($merged_id, $merged_person);
					$merged_df = ko_get_datafields($merged_id);

					//Store current data for person's history
					ko_save_leute_changes($merged_id, $merged_person, $merged_df);

					//Clean array containing merged groups, smallgroups etc.
					$groups = array_unique($groups);
					foreach($groups as $k => $v) {
						if(!$v) unset($groups[$k]);
					}
					$smallgroups = array_unique($smallgroups);
					foreach($smallgroups as $k => $v) {
						if(!$v) unset($smallgroups[$k]);
					}

					//Update record with merged group, smallgroup etc. data
					db_update_data('ko_leute', "WHERE `id` = '$id'", array('groups' => implode(',', $groups),
																																 'smallgroups' => implode(',', $smallgroups),
																																 'lastchange' => date('Y-m-d H:i:s')));
				}
				else {
					ko_get_person_by_id($id, $person);
					$new = $test = array();
					foreach($person as $k => $v) {
						if(!in_array($k, $mod_cols)) continue;
						$test[$k] = $merged_person[$k];
						$new[$k] = $v;
					}
					//Check for differences in address record. Only create moderation entry if differences are present
					$doublediff = array_merge(array_diff($test, $new), array_diff($new, $test));
					if(sizeof($new) > 0 && sizeof($doublediff) > 0) {
						$new['_leute_id'] = $merged_id;
						$new['_crdate'] = date('Y-m-d H:i:s');
						$new['_bemerkung'] = getLL('leute_merged_comment');
						db_insert_data('ko_leute_mod', $new);
						$showMutations = TRUE;
					}

					//Group datafield data
					$all_dfs = db_select_data('ko_groups_datafields', 'WHERE 1');
					$df = ko_get_datafields($id);
					foreach($df as $dfid => $data) {
						$found = FALSE;
						foreach($merged_df as $mid => $mdata) {
							if($mdata['group_id'] == $data['group_id'] && $mdata['datafield_id'] == $data['datafield_id']) {
								$found = TRUE;
								if($data['value'] != $mdata['value']) {
									if($mdata['value'] == '') {  //Entry of kept person is empty so use the value from the double entry
										db_update_data('ko_groups_datafields_data', "WHERE `group_id` = '".$mdata['group_id']."' AND `datafield_id` = '".$mdata['datafield_id']."' AND `person_id` = '".$mdata['person_id']."'", array('value' => $data['value']));
									} else if($data['value'] == '') {  //Entry of the double entry is empty so keep the other
										//Do nothing
									} else {  //Both value contain something, so store both
										//Merge values for multiselect fields
										if($all_datafields[$data['datafield_id']]['type'] == 'multiselect') {
											db_update_data('ko_groups_datafields_data', "WHERE `group_id` = '".$mdata['group_id']."' AND `datafield_id` = '".$mdata['datafield_id']."' AND `person_id` = '".$mdata['person_id']."'", array('value' => array_unique(array_merge(explode(',', $mdata['value']), explode(',', $data['value'])))));
										} else if($all_datafields[$data['datafield_id']]['type'] == 'select') {
											//Do nothing with different select values
										} else {
											//Concatenate values with ,
											db_update_data('ko_groups_datafields_data', "WHERE `group_id` = '".$mdata['group_id']."' AND `datafield_id` = '".$mdata['datafield_id']."' AND `person_id` = '".$mdata['person_id']."'", array('value' => $mdata['value'].', '.$data['value']));
										}
									}
								}
							}
						}
						if(!$found) {  //If kept person has no such record, then copy it
							unset($data['id']);
							$data['person_id'] = $merged_id;
							db_insert_data('ko_groups_datafields_data', $data);
						}
					}//foreach(df)


					//Check if this person had been assigned to a login. If so, assign the new person to the same login
					db_update_data('ko_admin', "WHERE `leute_id` = '$id'", array('leute_id' => $merged_id));

					//Reassign all donations of this person to the new one
					db_update_data('ko_donations', "WHERE `person` = '$id'", array('person' => $merged_id));

					//Reassign all tracking entries of this person to the new one
					db_update_data('ko_tracking_entries', "WHERE `lid` = '$id'", array('lid' => $merged_id));

					//Preferred fields
					$entries = db_select_data('ko_leute_preferred_fields', "WHERE `lid` = '$id'");
					foreach($entries as $entry) {
						if(db_get_count('ko_leute_preferred_fields', 'id', "AND `type` = '".$entry['type']."' AND `lid` = '$merged_id'") == 0) {
							db_insert_data('ko_leute_preferred_fields', array('type' => $entry['type'], 'lid' => $merged_id, 'field' => $entry['field']));
						}
					}

					//Update address relations on other addresses
					$relationFields = array();
					foreach($KOTA['ko_leute'] as $colID => $col) {
						if($col['form']['type'] == 'peoplesearch') $relationFields[] = $colID;
					}
					if(sizeof($relationFields) > 0) {
						foreach($relationFields as $field) {
							$toBeMerged = db_select_data('ko_leute', "WHERE `$field` REGEXP '(^|,)$id(,|$)'");
							if(sizeof($toBeMerged) == 0) continue;
							foreach($toBeMerged as $merge) {
								$cIDs = explode(',', $merge[$field]);
								foreach($cIDs as $k => $v) {
									if($v == $id) $cIDs[$k] = $merged_id;
								}
								db_update_data('ko_leute', "WHERE `id` = '".$merge['id']."'", array($field => implode(',', $cIDs)));
							}
						}
					}

					//Hook: Allow plugins to add merging logic
					hook_leute_merge($id, $merged_id);


					//Delete person
					ko_leute_delete_person($id);

				}//if..else(first)
			}//foreach(num_fields)
		}//foreach(duplicates)

		//Show moderations
		if($showMutations && $access['leute']['MAX'] > 1) $_SESSION['show'] = 'mutationsliste';
	break;




	case "multiedit":
		/* Leute-Multiedit */
		if($_SESSION["show"] == "show_all" || $_SESSION["show"] == "show_my_list") {
			if($access['leute']['MAX'] < 2) continue;

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
			if($access['kg']['MAX'] < 3) continue;

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
			kota_submit_multiedit(2, '', 'lastchange');
		} else if($_SESSION["show"] == "multiedit_kg") {
			if($access['kg']['MAX'] < 3) continue;
			kota_submit_multiedit(2);
		}
		if(!$notifier->hasErrors()) $notifier->addInfo(11, $do_action);
		$_SESSION["show"] = $_SESSION["show_back"] ? $_SESSION["show_back"] : "show_all";
	break;




	//Löschen
	case "delete_person":
		$del_id = format_userinput($_POST["id"], "uint", TRUE);
		if(!$del_id) continue;
		if($access['leute']['MAX'] < 3) continue;

		$ok = ko_leute_delete_person($del_id);

		if($ok) {
			$notifier->addInfo(3, $do_action);
		} else {
			$notifier->addError(4, $do_action);
		}
	break;


	case 'undelete_person':
		$del_id = format_userinput($_POST['id'], 'uint', TRUE);
		if(!$del_id) continue;
		if(!($access['leute']['ALL'] > 2 || $access['leute'][$del_id] > 2)) continue;

		ko_get_person_by_id($del_id, $del_person);
		if($del_person['deleted'] != 1) continue;

		ko_save_leute_changes($del_id, $del_person);
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
	break;




	//Mutationen
	case "submit_mutation":
		if($access['leute']['MAX'] < 2) continue;
		if(!$_POST["aa_id"]) continue;

		//Initialisierung
		$do_ldap = ko_do_ldap();

		$mod_aa_id = format_userinput($_POST["aa_id"], "uint");

		//Mod- und alte Daten auslesen
		ko_get_mod_leute($_mod_p, $mod_aa_id);
		$mod_p = $_mod_p[$mod_aa_id];

		if($access['leute']['ALL'] < 2 && ($access['leute'][$mod_p['_leute_id']] < 2 || $mod_p['_leute_id'] < 1)) continue;


		if($mod_p["_leute_id"] == -1) { //Neu:
			$new_entry = TRUE;
			$old_p = array();
		} else {
			$new_entry = FALSE;
			ko_get_person_by_id($mod_p["_leute_id"], $old_p);
		}

		$found = FALSE;
		foreach($_POST["chk_".$mod_aa_id] as $field => $field_state) {
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
		if(!$found) continue;  //Keine markierte CheckBox gefunden

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

		if($old_p["famid"] != 0) {
			ko_update_familie_filter();
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


		//Mod-Eintrag löschen
		db_delete_data("ko_leute_mod", "WHERE `_id`='$mod_aa_id'");

		ko_log_diff("aa_insert", $data, $old_p);

		if($_SESSION['show_back']) $_SESSION['show'] = $_SESSION['show_back'];
	break;


	case 'submit_del_mutation':
		if($access['leute']['MAX'] < 2) continue;
		if(!$_POST['aa_id']) continue;

		$aa_id = format_userinput($_POST['aa_id'], 'uint');

		//Check for access
		ko_get_mod_leute($_mod_p, $aa_id);
		$mod_p = $_mod_p[$aa_id];
		if($access['leute']['ALL'] < 2 && ($access['leute'][$mod_p['_leute_id']] < 2 || $mod_p['_leute_id'] < 1)) continue;

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
		if($access['leute']['ALL'] < 4 && !($access['leute']['GS'] && $access['groups']['MAX'] > 1)) continue;
		if(!$_POST["_id"]) continue;

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


		//get person-data
		if($do_action == "submit_gs_new_person") {
			//insert data as new person
			$new_person = $_p;
			//Unset fields not to be stored in ko_leute
			foreach($new_person as $k => $v) {
				if(substr($k, 0, 1) == "_") unset($new_person[$k]);
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
		if($notifier->hasErrors()) continue;

		foreach($lids as $lid) {
			ko_get_person_by_id($lid, $p);

			//update group-data of person
			$groups = explode(",", $p["groups"]);
			if(!in_array($save_group_id, $groups)) {
				//Save changes for versioning (but not for new person)
				if($do_action != "submit_gs_new_person") {
					ko_save_leute_changes($lid);
				}

				$data = array("groups" => implode(",", array_merge($groups, array($save_group_id))));
				db_update_data("ko_leute", "WHERE `id`='$lid'", $data);

				//Update group count
				ko_update_group_count($gid, $all_groups[$gid]['count_role']);

				//Add ezmlm subscription if set for this group
				if(defined("EXPORT2EZMLM") && EXPORT2EZMLM && $all_groups[$gid]["ezmlm_list"] != "") {
					ko_get_person_by_id($lid, $np);
					ko_ezmlm_subscribe($all_groups[$gid]["ezmlm_list"], $all_groups[$gid]["ezmlm_moderator"], $np["email"]);
				}
			}

			//insert group datafields data
			foreach(explode(",", $all_groups[$gid]["datafields"]) as $fid) {
				$value = $_POST["group_datafields"][$_id][$gid][$fid];
				db_delete_data("ko_groups_datafields_data", "WHERE `datafield_id` = '$fid' AND `person_id` = '$lid' AND `group_id` = '$gid'");
				db_insert_data("ko_groups_datafields_data", array("group_id" => $gid, "person_id" => $lid, "datafield_id" => $fid, "value" => $value));
			}

			//Set default tracking data
			if(in_array('tracking', $MODULES)) {
				$tracking = db_select_data('ko_tracking', "WHERE `filter` REGEXP '^g".$gid."[:r0-9]*'", '*', '', 'LIMIT 0,1', TRUE);
				if(isset($tracking['id'])) {
					include_once($ko_path.'tracking/inc/tracking.inc.php');
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
		if($access['leute']['ALL'] < 4) continue;
		if(!$_POST["_id"]) continue;

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


	//Aktionen
	case "leute_action":
		set_time_limit(0);
		$fam = array();
		$person = array();

    $leute_col_name = ko_get_leute_col_name(FALSE, TRUE);

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

			case "children":
				if($_SESSION["show"] == "show_adressliste")
					$xls_cols = $_SESSION["show_adressliste_cols"];
				else
					$xls_cols = $_SESSION["show_leute_cols"];

				foreach(explode(',', ko_get_userpref($_SESSION['ses_userid'], 'leute_children_columns')) as $col) {
					$ll = getLL("leute_children_col".$col);
					if(!$ll) {
						if(in_array(substr($col, 0, 8), array("_father_", "_mother_"))) {
							$ll = getLL("kota_ko_leute_".substr($col, 8))." ".getLL("leute_children_col".substr($col, 0, 7));
						} else {
							$ll = getLL("kota_ko_leute".$col);
						}
					}
					$leute_col_name_add[$col] = $ll ? $ll : substr($col, 1);
					$xls_cols[] = $col;
				}
				$leute_col_name = array_merge($leute_col_name, (array)$leute_col_name_add);
			break;  //children

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
		if(substr($_POST["sel_auswahl"], 0, 4) == "alle" && ($_SESSION["show"] == "show_all" || $_SESSION["show"] == "xls_settings")) {
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

		//Clear famid if selected persons should get exported as persons
		if($clear_famid_for_pidlist) {
			foreach($clear_pidlist as $pid) {
				$es[$pid]["_famid"] = $es[$pid]["famid"];
				$es[$pid]["famid"] = "";
			}
		}


		if(sizeof($es) == 0) $notifier->addError(5, $do_action);
		if($notifier->hasErrors()) continue;

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




		$row = 0;
		$data = array();
		foreach($es as $pid => $p) {
			if(($access['leute']['ALL'] < 1 && $access['leute'][$pid] < 1) || !$pid) continue;

			//Check for single person or family to be exported
			if( ($mode == "f" && $p["famid"])
				||
				($mode == "Def" && $p["famid"]
					&& ($families[$p["famid"]]["famgembrief"] == "ja" || !isset($families[$p["famid"]]["famgembrief"]))
				)
				||
				($mode == "Fam2" && $p["famid"])
			) {

				if($done_fam[$p["famid"]]) {
					unset($es[$pid]);
					continue;
				}

				$famFunctions = array();
				$lastNames = array();
				foreach ($fam[$p['famid']] as $famMember) {
					$famFunctions[$orig_es[$famMember]['famfunction']] = $famMember;
					$lastNames[$orig_es[$famMember]['famfunction']] = $orig_es[$famMember]['nachname'];
				}
				if ( // ehepaar export
					$famFunctions['husband'] &&
					$famFunctions['wife'] &&
					sizeof($fam[$p['famid']]) == 2 &&
					in_array("vorname", $xls_cols) &&
					in_array("nachname", $xls_cols) &&
					$lastNames['husband'] != $lastNames['wife'] &&
					$families[$p['famid']]['famfirstname'] == '' &&
					$families[$p['famid']]['famlastname'] == ''
				) {
					// set andrede to ''
					if (in_array('anrede', $xls_cols)) {
						if ($families[$p['famid']]['famanrede'] == '') {
							$p['anrede'] = '';
							$p['vorname'] = getLL('leute_salutation_m') . ' '; // set prefix of husband's name to Mr.
							$p['nachname'] = getLL('leute_salutation_w') . ' '; // set prefix of wifes name to Mrs.
						}
						else {
							$p['anrede'] = $families[$p['famid']]['famanrede'];
						}
					}
					if (ko_get_userpref($_SESSION['ses_userid'], 'leute_force_family_firstname') > 0) {
						$p['vorname'] .= $orig_es[$famFunctions['husband']]['vorname'] . ' ';
						$p['nachname'] .= $orig_es[$famFunctions['wife']]['vorname'] . ' ';
					}
					$p['vorname'] .= $orig_es[$famFunctions['husband']]['nachname'] . ' ' . getLL('and');
					$p['nachname'] .= $orig_es[$famFunctions['wife']]['nachname'];
				}
				else { // not ehepaar export
					if(in_array('anrede', $xls_cols)) {
						//Get family salutation from family data (if set)
						if($families[$p['famid']]['famanrede']) {
							$p['anrede'] = $families[$p['famid']]['famanrede'];
						} else {
							//Use generic salutation (depending on members in list)
							$child = FALSE;
							foreach($fam[$p['famid']] as $member_id) {
								if(!in_array($orig_es[$member_id]['famfunction'], array('husband', 'wife'))) $child = TRUE;
							}
							if($child) $p['anrede'] = getLL('ko_leute_anrede_family');
							else $p['anrede'] = getLL('ko_leute_anrede_family_no_children');
						}
						//$p["anrede"] = $families[$p["famid"]]["famanrede"] ? $families[$p["famid"]]["famanrede"] : getLL("ko_leute_anrede_family");
					}//anrede
					if(in_array("vorname", $xls_cols)) {
						//If no special family values are given, set first name to empty ("Fam", "", "Lastname")
						if(!$families[$p["famid"]]["famanrede"] && !$families[$p["famid"]]["famfirstname"] && $families[$p["famid"]]["famlastname"] && ko_get_userpref($_SESSION['ses_userid'], 'leute_force_family_firstname') == 0) {
							$p["vorname"] = "";
						} else {
							if(ko_get_userpref($_SESSION['ses_userid'], 'leute_force_family_firstname') == 2) {
								//Use first names of all members found in the current list
								$familyMembers = (array)db_select_data('ko_leute', "WHERE `famid` = '".$p['famid']."' AND `famfunction` IN ('husband', 'wife')", 'id,famfunction,vorname', 'ORDER BY famfunction ASC');
								$familyMembers = array_merge($familyMembers, (array)db_select_data('ko_leute', "WHERE `famid` = '".$p['famid']."' AND `famfunction` IN ('child', '')", 'id,famfunction,vorname', 'ORDER BY famfunction DESC, geburtsdatum DESC'));
								$foundMembers = array();
								foreach($familyMembers as $oneMember) {
									if(in_array($oneMember['id'], array_keys($orig_es))) $foundMembers[] = $oneMember['vorname'];
								}
								$p['vorname'] = implode((' '.getLL('family_link').' '), $foundMembers);
							} else {
								if($families[$p["famid"]]["famfirstname"]) {
									$p["vorname"] = $families[$p["famid"]]["famfirstname"];
								} else {
									//use first names of parents for firstname-col
									$parents = db_select_data("ko_leute", "WHERE `famid` = '".$p["famid"]."' AND `famfunction` IN ('husband', 'wife')", "famfunction,vorname", "ORDER BY famfunction ASC");
									$parent_values = array();
									foreach($parents as $parent) $parent_values[] = $parent["vorname"];
									$p["vorname"] = implode((" ".getLL("family_link")." "), $parent_values);
								}
							}
						}
					}//vorname
					if(in_array("nachname", $xls_cols)) {
						$p["nachname"] = $families[$p["famid"]]["famlastname"] ? $families[$p["famid"]]["famlastname"] : $families[$p["famid"]]["nachname"];
					}//nachname
				}

				if(in_array('email', $xls_cols) || $_POST['id'] == 'email') {
					if($families[$p['famid']]['famemail']) {  //Get family email address if set
						$parent = db_select_data('ko_leute', ("WHERE `famid` = '".$p['famid']."' AND `famfunction` = '".$families[$p['famid']]['famemail']."' AND `deleted` = '0'".ko_get_leute_hidden_sql()), '*', '', 'LIMIT 0,1', TRUE);
						ko_get_leute_email($parent, $email);
						if($email[0]) $p['email'] = $email[0];
					} else if($p['famfunction'] == 'child') {  //if no family email is set but the person is a child, use the email address of one of the parents
						$parents = db_select_data('ko_leute', ("WHERE `famid` = '".$p['famid']."' AND `famfunction` IN ('husband', 'wife') AND `deleted` = '0'".ko_get_leute_hidden_sql()), '*', 'ORDER BY famfunction ASC');
						$done_parent = FALSE;
						foreach($parents as $parent) {
							ko_get_leute_email($parent, $email);
							if($email[0] && !$done_parent) {
								$p['email'] = $email[0];
								$done_parent = TRUE;
							}
						}
					}
				}//email
				$hookData = array('_es' => $es, '_xls_cols' => $xls_cols, 'p' => $p, '_orig_es' => $orig_es, 'cols_no_map' => array());
				hook_function_inline('leute_export_fam', $hookData);
				$p = $hookData['p'];
				$cols_no_map = $hookData['cols_no_map'];
				unset($hookData);

				$done_fam[$p["famid"]] = TRUE;

			}//if(fam)
			else {
				unset($cols_no_map);
			}

			$es[$pid] = $p;


			//Restore famid if selected persons should get exported as persons (needed in map_leute_daten())
			if($clear_famid_for_pidlist) {
				$p["famid"] = $p["_famid"];
				unset($p["_famid"]);
			}

			$col = 0;
			foreach($xls_cols as $c) {
				if(!$leute_col_name[$c]) continue;

				//Check for columns that don't need any more mapping (may be set in plugin above)
				if(in_array($c, $cols_no_map)) {
					$value = $p[$c];
				} else {
					$value = map_leute_daten($p[$c], $c, $p, $all_datafields);
				}
				if(is_array($value)) {  //group with datafields, so more than one column has to be added
					foreach($value as $v) $data[$row][$col++] = strip_tags($v);
				} else {
					$data[$row][$col++] = strip_tags($value);
				}
			}//foreach(xls_cols as col)
			$row++;
		}//foreach(es as pid => p)

		if(sizeof($data) == 0) $notifier->addError(5, $do_action);


		if(!$notifier->hasErrors()) {
			switch($_POST["id"]) {

				case "xls_settings":
					$_SESSION['leute_export_xls_post'] = $_POST;
					$_SESSION['show'] = 'xls_settings';
				break;

				case "excel":
				case 'csv':
					$leute_col_name = ko_get_leute_col_name(FALSE, TRUE);
					$leute_col_name = array_merge($leute_col_name, (array)$leute_col_name_add);
					$header = array();
					$wrap = array();
					foreach($xls_cols as $c) {
						if(!$leute_col_name[$c]) continue;
						$header[] = $leute_col_name[$c];
						//Define wrapping for excel cells (only wrap for group columns)
						if($c == 'groups' || substr($c, 0, 9) == 'MODULEgrp') {
							$wrap[] = TRUE;
						} else {
							$wrap[] = FALSE;
						}
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
						$filename = ko_export_to_xlsx($header, $data, $filename, "kOOL", 'landscape', $wrap, array(), $linebreak_columns);
					} else {
						$filename = $ko_path.'download/excel/'.getLL('export_filename').strftime('%d%m%Y_%H%M%S', time()).'.csv';
	          ko_export_to_csv($header, $data, $filename);
					}

					//Fileshare-Datei speichern, falls gewünscht
					if(ko_module_installed("fileshare", $_SESSION["ses_userid"]) && ko_get_userpref($_SESSION["ses_userid"], "save_files_as_share") == 1) {
						ko_fileshare_save_file_as_share($_SESSION["ses_userid"], $filename);
					}

					$show = ko_get_userpref($_SESSION['ses_userid'], 'default_view_leute');
					if(!$show) $show = 'show_all';
					$_SESSION['show'] = $show;

					$onload_code = "ko_popup('".$ko_path."download.php?action=file&amp;file=".substr($filename, 3)."');";
				break;


				case "sms":
					$smarty->assign("sms_balance", ko_html(get_cache_sms_balance()));

					$smarty->assign("ko_path", $ko_path);

					//Leute-cols für Excel-Datei auslesen
					$cols = array("anrede", "vorname", "nachname", "adresse", "adresse_zusatz", "plz", "ort", "telp", "telg", "natel");
		      $header = array();
		      foreach($cols as $c) {
		        $header[] = $leute_col_name[$c];
		      }

					//Empfänger auslesen
					$rec_invalid = $rec_invalid_ids = $recipients_names = "";
					$row = 0;
					$xls_data = $array_empfaenger = array();
					foreach($es as $l_id => $l) {
						$invalid = FALSE;
						ko_get_leute_mobile($l, $mobiles);
						if(sizeof($mobiles) > 0) {
							foreach($mobiles as $mobile) {
								if(check_natel($mobile)) {
									$array_empfaenger[] = $mobile;
									$recipients_names .= $l["vorname"]." ".$l["nachname"].", ";
								} else {
									$invalid = TRUE;
								}
							}
						} else {
							$invalid = TRUE;
						}
						if($invalid) {
							$rec_invalid .= $l["vorname"]." ".$l["nachname"].($l["ort"] ? (" ".getLL('from')." ".$l["ort"]) : "").", ";
							$rec_invalid_ids .= $l["id"].",";

							$col = 0;
							foreach($cols as $c) {
								$xls_data[$row][$col++] = sql2datum($l[$c]);
							}
							$row++;
						}

					}//foreach(leute as l_id)
					$smarty->assign("tpl_show_recipients", 1);
					$smarty->assign("tpl_show_sendbutton", 1);
					$smarty->assign("tpl_show_header", 1);
					$smarty->assign("tpl_recipients_names", ko_html(substr($recipients_names, 0, -2)));

					//XLS-Datei aller Leute ohne GSM-Nummer erstellen
					if($rec_invalid != "") {
						$rec_invalid = substr($rec_invalid, 0, -2);
						$rec_invalid_ids = substr($rec_invalid_ids, 0, -1);
						$smarty->assign("tpl_recipients_invalid", ko_html($rec_invalid));
						$smarty->assign("tpl_recipients_invalid_ids", $rec_invalid_ids);

						$filename = $ko_path."download/excel/".getLL("export_filename").strftime("%d%m%Y_%H%M%S", time()).".xlsx";
						$filename = ko_export_to_xlsx($header, $xls_data, $filename, "kOOL");
						$smarty->assign("xls_filename", $filename);
					}

					//Sender select
					$senders = array();
					//Check for admin mobile
					ko_get_login($_SESSION['ses_userid'], $login);
					$sender_ids = explode(',', ko_get_setting('sms_sender_ids'));
					if($login['mobile'] && in_array($login['mobile'], $sender_ids)) $senders[] = $login['mobile'];
					//Check for assigned person
					ko_get_leute_mobile(ko_get_logged_in_id(), $mobiles);
					foreach($mobiles as $mobile) {
						if(!check_natel($mobile)) continue;
						if(!in_array($mobile, $sender_ids)) continue;
						if(in_array($mobile, $senders)) continue;
						$senders[] = $mobile;
					}
					if(sizeof($senders) > 1) {
						$smarty->assign('tpl_show_sender', TRUE);
						$smarty->assign('tpl_senders', $senders);
					}

					//Empfänger zuweisen
					$array_empfaenger = array_unique($array_empfaenger);
					foreach($array_empfaenger as $e) {
						$tpl_recipients .= $e . ",";
					}
					$tpl_recipients = substr($tpl_recipients, 0, -1);
					$smarty->assign("tpl_recipients", ko_html($tpl_recipients));
					$smarty->assign('tpl_num_recipients', sizeof($array_empfaenger));

					$_SESSION["show_back"] = $_SESSION["show"];
					$_SESSION["show"] = "sms_versand";
				break;


				case "etiketten":
					//Vorlage-Namen auslesen (für Dropdown-Listen)
					//$vorlagen["values"][] = "";
					//$vorlagen["output"][] = "";
					ko_get_etiketten_vorlagen($vorlagen_);
					foreach($vorlagen_ as $v) {
						$vorlagen["values"][] = $v["vorlage"];
						$vorlagen["output"][] = $v["value"];
					}//foreach(vorlagen as v)
					$smarty->assign("vorlagen", $vorlagen);

					//Store data in session to be used on submission of label export settings
					$_SESSION["etiketten_data"] = $es;

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
					$smarty->assign("label_limiter", getLL("leute_labels_limiter"));
					$smarty->assign("label_limiter_newline", getLL("leute_labels_limiter_newline"));
					$smarty->assign("label_limiter_doublenewline", getLL("leute_labels_limiter_doublenewline"));
					$smarty->assign("label_limiter_space", getLL("leute_labels_limiter_space"));
					$smarty->assign("label_limiter_nothing", getLL("leute_labels_limiter_nothing"));
					$smarty->assign("label_submit", getLL("leute_labels_submit"));


					$_SESSION["show_back"] = $_SESSION["show"];
					$_SESSION["show"] = "etiketten_optionen";
				break;


				case 'mailmerge':
					//Get layouts
					$layouts = ko_latex_get_layouts('letter');
					$smarty->assign('layouts', $layouts);

					//Show previously uploaded signature file
					foreach($mailmerge_signature_ext as $ext) {
						if(file_exists($ko_path.'latex/images/signature_'.$_SESSION['ses_userid'].'.'.$ext)) {
							$smarty->assign('show_sig_file', $ko_path.'latex/images/signature_'.$_SESSION['ses_userid'].'.'.$ext);
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
						$smarty->assign('show_sender', TRUE);
						$address_user = $p['vorname'].' '.$p['nachname'].', '.$p['adresse'].', '.$p['plz'].' '.$p['ort'];
						$smarty->assign('sender_address_user', $address_user);
						$address_church = ko_get_setting('info_name').', '.ko_get_setting('info_address').', '.ko_get_setting('info_zip').' '.ko_get_setting('info_city');
						$smarty->assign('sender_address_church', $address_church);
					} else {
						$smarty->assign('show_sender', FALSE);
					}
					$smarty->assign('signature', $p['vorname'].' '.$p['nachname']);

					//Store data in session to be used on submission of label export settings
					$_SESSION['mailmerge_data'] = $es;
					$_SESSION['mailmerge_cols'] = $xls_cols;
					$_SESSION['mailmerge_famids'] = $done_fam;

					$_SESSION["show_back"] = $_SESSION["show"];
					$_SESSION["show"] = "mailmerge";
				break;


				case "email":
					//Titeldaten für XLS-Datei
					$cols = array("anrede", "vorname", "nachname", "adresse", "adresse_zusatz", "plz", "ort", "telp", "telg");
		      $header = array();
		      foreach($cols as $c) {
		        $header[] = $leute_col_name[$c];
		      }

					$txt_empfaenger = "";
					$xls_data = $array_empfaenger = array();
					$ohne_email = "";
					$row = 0;
					foreach($es as $l => $p) {  //Loop über alle Leute
						if(ko_get_leute_email($p, $email)) {
							$array_empfaenger = array_merge($array_empfaenger, $email);
						} else {
							$ohne_email .= $p["vorname"]." ".$p["nachname"].($p["ort"] ? (" ".getLL('from')." ".$p["ort"]) : "").", ";

							$col = 0;
							foreach($cols as $c) {
			          $xls_data[$row][$col++] = sql2datum($p[$c]);
			        }
			        $row++;
						}
					}//foreach(es)

					$array_empfaenger = array_unique($array_empfaenger);
					$txt_empfaenger = implode(",", $array_empfaenger);
					$txt_empfaenger_semicolon = implode(';', $array_empfaenger);

					$ohne_email = substr($ohne_email, 0, -2);
					//XLS-Datei aller Leute ohne Email erstellen
					if($ohne_email != "") {
						$dateiname = $ko_path."download/excel/".getLL("export_filename").strftime("%d%m%Y_%H%M%S", time()).".xlsx";
                        $dateiname = ko_export_to_xlsx($header, $xls_data, $dateiname, "kOOL");
						$smarty->assign("xls_filename", $dateiname);
					}

					$smarty->assign("tpl_show_header", TRUE);
					$smarty->assign("tpl_show_rec_link", TRUE);

					$smarty->assign("txt_empfaenger", ko_html($txt_empfaenger));
					$smarty->assign('txt_empfaenger_semicolon', ko_html($txt_empfaenger_semicolon));
					$smarty->assign("tpl_ohne_email", ($ohne_email == "" ? getLL('form_leute_none') : $ohne_email));
					$p = ko_get_logged_in_person();
					$smarty->assign("tpl_show_bcc_an_mich", ($p["email"] ? TRUE : FALSE));
					$smarty->assign("tpl_show_send", TRUE);
					$smarty->assign("tpl_show_to_bcc", TRUE);
					$smarty->assign("tpl_info_email", ko_get_setting("info_email"));

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



	case "export_pdf":
		$layout_id = format_userinput($_POST["pdf_layout_id"], "uint");
		if(!$layout_id) continue;

		$_SESSION["show_back"] = $_SESSION["show"];
		$_SESSION["show"] = "export_pdf";
	break;



	case "do_export_pdf":
		$layout_id = format_userinput($_POST["layout_id"], "uint");
		if(!$layout_id) continue;

		$filename = ko_export_leute_as_pdf($layout_id);

		$onload_code = "ko_popup('".$ko_path."download.php?action=file&amp;file=".substr($filename, 3)."');";
		$_SESSION["show"] = $_SESSION["show_back"] ? $_SESSION["show_back"] : "show_all";
	break;





	case "submit_sms":
		//Empfaenger
		$empfaenger_ = explode(",", format_userinput($_POST["txt_recipients"], "intlist"));
		$empfaenger__ = explode(",", format_userinput($_POST["txt_recipients_add"], "intlist"));
		$empfaenger = array_unique(array_merge($empfaenger_, $empfaenger__));

		//Text
		$text = $_POST["txt_smstext"];


		//Get possible senders
		$senders = array();
		//Check for admin mobile
		ko_get_login($_SESSION['ses_userid'], $login);
		$sender_ids = explode(',', ko_get_setting('sms_sender_ids'));
		if($login['mobile'] && in_array($login['mobile'], $sender_ids)) $senders[] = $login['mobile'];
		//Check for assigned person
		ko_get_leute_mobile(ko_get_logged_in_id(), $mobiles);
		foreach($mobiles as $mobile) {
			if(!check_natel($mobile)) continue;
			if(!in_array($mobile, $sender_ids)) continue;
			if(in_array($mobile, $senders)) continue;
			$senders[] = $mobile;
		}

		//Absender
    $p = ko_get_logged_in_person();
    if($_POST['sel_sender'] && in_array($_POST['sel_sender'], $senders)) {  //User selected sener from GUI if set
      $from = $_POST['sel_sender'];
    } else if(sizeof($senders) > 0) {  //Use first (possibly only) entry of senders
      $from = array_shift($senders);
    } else if(!ko_get_logged_in_id()) {  //If no person is assigned just use the login name
      $from = $_SESSION["ses_username"];
    } else {
			$from = '';
		}

		if($SMS_PARAMETER['provider'] == 'aspsms') {
			$ret = send_aspsms($empfaenger, $text, $from, $num, $charges);
			if($ret) {
				$info_txt = getLL('info_leute_99a').$num.getLL('info_leute_99b');
				$info_txt .= getLL('info_leute_99d').' '.$charges;
				$notifier->addTextInfo($info_txt, $do_action);

			}
		}
		//Use Clickatell as default (for backwards compatibility, if no provider is set in $SMS_PARAMETER)
		else {
			$climsgid = time()."_".$p["id"];
			$msg_type = "SMS_TEXT";
			$ret = send_sms($empfaenger, $text, $from, $climsgid, $msg_type, $success, $done, $problems, $charges, $error_message);

			if($ret) {
				$notifier->addInfo(99, $do_action);
				$info_txt = getLL('info_leute_99a').$success.'/'.$done. getLL('info_leute_99b');
				if($problems) $info_txt .= getLL('info_leute_99c').substr($problems, 0, -2);
				$info_txt .= getLL('info_leute_99d').' '.$charges;
			} else {
				$my_error_txt = $error_message;
				$notifier->addTextError($my_error_text, $do_action);
			}
		}

		$_SESSION['show'] = $_SESSION['show_back'] ? $_SESSION['show_back'] : 'show_all';
	break;



	case "export_sms_to_mylist":
	case "exportadd_sms_to_mylist":
		if($_POST["hid_recipients_invalid_ids"]) {
			if($do_action == "export_sms_to_mylist") $_SESSION["my_list"] = array();
			foreach(explode(",", format_userinput($_POST["hid_recipients_invalid_ids"], "intlist")) as $c) {
				if($c) $_SESSION["my_list"][$c] = $c;
			}
			ko_save_userpref($_SESSION["ses_userid"], "leute_my_list", serialize($_SESSION["my_list"]));
		}

		$smarty->assign("sms_balance", format_userinput($_POST["hid_sms_balance"], "float"));
		$smarty->assign("tpl_recipients", format_userinput($_POST["txt_recipients"], "intlist"));
		$smarty->assign("tpl_recipients_add", format_userinput($_POST["txt_recipients_add"], "intlist"));
		$smarty->assign("txt_smstext", ko_html($_POST["txt_smstext"]));
		$smarty->assign("txt_letters", format_userinput($_POST["txt_letters"], "uint"));
		$smarty->assign("tpl_recipients_invalid_ids", format_userinput($_POST["hid_recipients_invalid_ids"], "intlist"));
		$smarty->assign("tpl_recipients_invalid", ko_html($_POST["hid_recipients_invalid"]));
		$smarty->assign("tpl_recipients_names", ko_html($_POST["hid_recipients_names"]));
		$smarty->assign("xls_filename", ko_html($_POST["hid_xls_filename"]));

		$smarty->assign("tpl_show_recipients", 1);
		$smarty->assign("tpl_show_sendbutton", 1);
		$smarty->assign("tpl_show_header", 1);
	break;



	case "submit_etiketten":
		if(!$_POST["sel_vorlage"]) continue;

		$labels_data = array(); $row = 0;

		$data = $_SESSION["etiketten_data"];
		$cols = $_SESSION["etiketten_cols"];

		//Parse data
		$all_datafields = db_select_data("ko_groups_datafields", "WHERE 1=1", "*");
		foreach($data as $p_id => $p) {
			$temp = "";
			foreach($cols as $i => $col) {
				if(substr($col, 0, 6) == "MODULE") {
					$value = map_leute_daten($p[$col], $col, $p, $all_datafields);
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
					$value = map_leute_daten($p[$col], $col, $p, $all_datafields);
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

		//Etiketten erstellen
		$filename = ko_export_etiketten($_POST['sel_vorlage'], $_POST['txt_start'], $_POST['rd_rahmen'], $labels_data, ($_POST['chk_fill_page']?$_POST['txt_fill_page']:0), $_POST['txt_multiply'], $retChk == 1, $retSel, $retTxt);

		//Fileshare-Datei speichern, falls gewünscht
		if(ko_module_installed("fileshare", $_SESSION["ses_userid"]) && ko_get_userpref($_SESSION["ses_userid"], "save_files_as_share") == 1) {
			ko_fileshare_save_file_as_share($_SESSION["ses_userid"], "../".$filename);  //Führendes "../" vorstellen
		}

		//Store return address selection in userprefs
		ko_save_userpref($_SESSION['ses_userid'], 'labels_return_address', $retChk . '@@@' . $retSel . '@@@' . $retTxt);

		$onload_code = "ko_popup('".$ko_path."download.php?action=file&amp;file=$filename');";
		$_SESSION["show"] = $_SESSION["show_back"] ? $_SESSION["show_back"] : "show_all";
		break;



	case 'submit_mailmerge':
		$preset = $_POST['sel_preset'] ? format_userinput($_POST['sel_preset'], 'dir') : 'default';

		$mmdata = array(); $counter = 0;
		$data = $_SESSION['mailmerge_data'];
		$cols = $_SESSION['mailmerge_cols'];
		$fam_ids = $_SESSION['mailmerge_famids'];

		//Get latex preset
		$latex = file_get_contents($ko_path.'latex/layouts/mailmerge.tex');
		list($pre, $rest) = explode('\begin{letter}', $latex);
		list($letter, $post) = explode('\end{letter}', $rest);
		$letter = '\begin{letter}'.$letter.'\end{letter}';
		$latex = '';

		//Signature file
		$sig_file = '';
		if($_FILES['file_sig_file']['tmp_name']) {
			$upload_name = $_FILES['file_sig_file']['name'];
			$tmp = $_FILES['file_sig_file']['tmp_name'];
			$ext_ = explode('.', $upload_name);
			$ext = strtolower($ext_[sizeof($ext_)-1]);

			if(in_array($ext, $mailmerge_signature_ext)) {
				$path = $BASE_PATH.'latex/images/';
				$filename = 'signature_'.$_SESSION['ses_userid'].'.'.$ext;
				$dest = $path.$filename;

				$ret = move_uploaded_file($tmp, $dest);
				if($ret) {
					$sig_file = '../images/'.$filename;
					chmod($dest, 0644);
				}
			}
		}
		//Get already stored file
		else if($_POST['chk_sig_file']) {
			foreach($mailmerge_signature_ext as $ext) {
				if(is_file($BASE_PATH.'latex/images/signature_'.$_SESSION['ses_userid'].'.'.$ext)) {
					$sig_file = '../images/signature_'.$_SESSION['ses_userid'].'.'.$ext;
				}
			}
		}

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
				if($p['famid'] != 0 && in_array($p['famid'], $fam_ids)) {  //Family
					$opening = getLL('leute_mailmerge_opening_formal_fam').' '.$p['anrede'].' '.$p['nachname'].getLL('leute_mailmerge_opening_formal_sep');
				} else {  //Single person
					if(!$p['anrede'] && $p['geschlecht']) $p['anrede'] = getLL('leute_salutation_'.$p['geschlecht']);
					$opening = getLL('leute_mailmerge_opening_formal_'.$p['geschlecht']).' '.$p['anrede'].' '.$p['nachname'].getLL('leute_mailmerge_opening_formal_sep');
				}
			} else {
				if($p['famid'] != 0 && in_array($p['famid'], $fam_ids)) {  //Family
					$opening = getLL('leute_mailmerge_opening_informal_fam').' '.$p['anrede'].' '.$p['nachname'].getLL('leute_mailmerge_opening_informal_sep');
				} else {  //Single person
					$opening = getLL('leute_mailmerge_opening_informal_'.$p['geschlecht']).' '.$p['vorname'].getLL('leute_mailmerge_opening_informal_sep');
				}
			}
			//TODO: Add hook to allow the opening to be changed by a plugin
			$map['###OPENING###'] = $opening;

			//Default fields which are alwys set for TeX templates for the recipient's address
			foreach(array('firm', 'anrede', 'vorname', 'nachname', 'adresse', 'adresse_zusatz', 'plz', 'ort', 'land') as $field) {
				if($p[$field]) {
					$map['###'.strtoupper($field).'###'] = ko_latex_escape_chars($p[$field]);
				} else {
					$map['###'.strtoupper($field).'###'] = '';
				}
			}

			//Prepare sender information (from address)
			if($_POST['rd_sender'] == 'user' && $sender['id']) {
				foreach($COLS_LEUTE_LATEX_FROM as $field) {
					$map['###FROM_'.strtoupper($field).'###'] = ko_latex_escape_chars($sender[$field]);
				}
			} else {
				//Set global address as sender
				$map['###FROM_VORNAME###'] = ko_latex_escape_chars(ko_get_setting('info_name'));
				$map['###FROM_NACHNAME###'] = '';
				$map['###FROM_ADRESSE###'] = ko_latex_escape_chars(ko_get_setting('info_address'));
				$map['###FROM_PLZ###'] = ko_latex_escape_chars(ko_get_setting('info_zip'));
				$map['###FROM_ORT###'] = ko_latex_escape_chars(ko_get_setting('info_city'));
				$map['###FROM_TELP###'] = ko_latex_escape_chars(ko_get_setting('info_phone'));
				$map['###FROM_EMAIL###'] = ko_latex_escape_chars(ko_get_setting('info_email'));
				$map['###FROM_WEB###'] = ko_latex_escape_chars(ko_get_setting('info_url'));
			}

			$map['###LAYOUT###'] = 'letter_'.$_POST['sel_layout'];
			$map['###SUBJECT###'] = ko_latex_escape_chars(strtr($_POST['txt_subject'], $mapCols));
			//Replace markers in text
			$text = strtr($_POST['txt_text'], $mapCols);
			//Escape LaTeX specific chars
			$text = ko_latex_escape_chars($text);
			//Replace formating code
			$mapFormat = array('[B]' => '\textbf{', '[/B]' => '}', '[b]' => '\textbf{', '[/b]' => '}',
												 '[I]' => '\textit{', '[/I]' => '}', '[i]' => '\textit{', '[/i]' => '}',
												 '[C]' => '\begin{center}', '[/C]' => '\end{center}', '[c]' => '\begin{center}', '[/c]' => '\end{center}');
			$text = strtr($text, $mapFormat);
			//Create lists (itemize) and paragraphs (\par)
			$txt = '';
			foreach(explode("\n", $text) as $line) {
				$line = trim($line);
				if($line == '') {
					//Add a \par for new lines and remove the last "\\" (if not within a list)
					if(!$itemize) {
						while(substr($txt, -1) == '\\') $txt = substr($txt, 0, -1);
						$txt .= "\n".'\par'."\n";
					}
				} else {
					if(substr($line, 0, 1) == '-') {  //List
						//item number > 1
						if($itemize) $txt .= '\item '.substr($line, 1)."\n";
						//First item
						else {
							$itemize = TRUE;
							$txt .= '\begin{itemize}'."\n";
							$txt .= '\item '.substr($line, 1)."\n";
						}
					} else if($itemize) {  //no more item but still inside a list, so finish the list
						$itemize = FALSE;
						$txt .= '\end{itemize}'."\n".$line."\n";
					} else {  //regular line. Add linebreak
						$txt .= $line.'\\\\';
					}
				}
			}
			//Remove any newlines at the end of the text
			while(substr($txt, -2) == '\\\\') $txt = substr($txt, 0, -2);

			$text = $txt;

			$map['###TEXT###'] = $text;
			$map['###CLOSING###'] = $_POST['txt_closing'];
			$map['###SIGNATURE###'] = ($sig_file ? '\includegraphics[height=4em]{'.$sig_file.'}\\\\' : '').$_POST['txt_signature'];

			$latex .= strtr($letter, $map)."\n\n";

			$counter++;
		}//foreach(data)

		$latex = strtr($pre, $map).$latex.strtr($post, $map);


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

		//Write Latex and create PDF
		$file = md5(uniqid(mt_rand(), true));
		if(!$fh = fopen($BASE_PATH.'latex/compile/'.$file.'.tex', 'w')) {
			$notifier->addError(20, $do_action);
			break;
		}
		if(!fwrite($fh, $latex)) {
			$notifier->addError(20, $do_action);
			break;
		}
		ko_latex_compile($file);
		foreach(array('.aux', '.log', '.tex') as $type) {
			$f = $BASE_PATH.'latex/compile/'.$file.$type;
			if(is_file($f)) unlink($f);
		}
		if(is_file($ko_path.'latex/compile/'.$file.'.pdf')) {
			$filename = $ko_path.'download/pdf/'.getLL('leute_mailmerge_filename').strftime('%d%m%Y_%H%M%S', time()).'.pdf';
			copy($ko_path.'latex/compile/'.$file.'.pdf', $filename);
			unlink($ko_path.'latex/compile/'.$file.'.pdf');
			$onload_code = "ko_popup('".$ko_path."download.php?action=file&amp;file=".substr($filename, 3)."');";
		} else {
			$notifier->addError(21, $do_action);
			break;
		}
	break;



	case "submit_email":
		$m = ko_get_logged_in_person();
		if($m["email"] == "") $email = ko_get_setting("info_email");
		else $email = $m["email"];

		if($_POST["rd_bcc_an_mich"] == "ja") $_POST["txt_bcc"] .= ($_POST["txt_bcc"] == "") ? $email : ", $email";

		$headers = array("From" => $email);
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
			$text,
			array(),
			$headers['CC'],
			$headers['BCC']
		);

		$_SESSION["show"] = $_SESSION["show_back"] ? $_SESSION["show_back"] : "show_all";
		if (!$notifier->hasErrors()) {
			$notifier->addInfo(4, $do_action);
			ko_log("leute_email", '"' . format_userinput($_POST["txt_betreff"], "text") . '": '.str_replace(",", ", ", format_userinput($_POST["txt_empfaenger"], "text")).", cc: ".str_replace(",", ", ", format_userinput($_POST["txt_cc"], "text")).", bcc: ".str_replace(",", ", ", format_userinput($_POST["txt_bcc"], "text")).", Text: ".format_userinput($_POST["txt_emailtext"], "text"));
		}
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
		if($access['kg']['MAX'] < 1 || $access['leute']['MAX'] < 1 || !$id) continue;

		$_SESSION["filter"] = array();

		$filter_akt = 0;
		ko_get_filters($filters, "leute");
    foreach($filters as $ff) {
      if(!$filter_akt && $ff["_name"] == "smallgroup") {
        $filter_akt = $ff["id"];
        $f = $ff;
      }
    }
		if(!$filter_akt) continue;

		$vars = array(1 => $id);
		$_SESSION["filter"][] = array($filter_akt, $vars, 0);

		$_SESSION["show"] = "show_all";
		$_SESSION["show_start"] = 1;
	break;


	case "delete_kg":
		if(FALSE === ($id = format_userinput($_POST["id"], "uint", TRUE, 4))) {
			trigger_error("Not allowed kg-id: ".$_POST["id"], E_USER_ERROR);
		}
		if($access['kg']['MAX'] < 4) continue;

		$old_kg = db_select_data("ko_kleingruppen", "WHERE `id` = '$id'");
		db_delete_data("ko_kleingruppen", "WHERE `id` = '$id.'");

		//update people data
		ko_kg_update_people();
		ko_update_kg_filter();

		$notifier->addInfo(14, $do_action);
		ko_log_diff("del_kg", $old_kg[$id]);
	break;



	case "submit_neue_kg":
		if($access['kg']['MAX'] < 4) continue;

		kota_submit_multiedit("", "new_kg");
		if(!$notifier->hasErrors()) {
			ko_update_kg_filter();
			$notifier->addInfo(12, $do_action);
			$_SESSION["show"] = $_SESSION["show_back"] ? $_SESSION["show_back"] : "list_kg";
		}
	break;



	case "submit_edit_kg":
		if($access['kg']['MAX'] < 3) continue;

		kota_submit_multiedit("", "edit_kg");
		if(!$notifier->hasErrors()) {
			ko_update_kg_filter();
			$notifier->addInfo(11, $do_action);
		}
		$_SESSION["show"] = $_SESSION["show_back"] ? $_SESSION["show_back"] : "list_kg";
	break;




	case 'kg_xls_export':
		if($access['kg']['MAX'] < 2) continue;

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
			if($name == '') continue;
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
		if($access['groups']['MAX'] < 1 || $access['leute']['MAX'] < 1 || !$id) continue;

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
		if(!$filter_akt) continue;

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
		$_SESSION["show_start"] = 1;
	break;



	//Role filter
	case 'set_role_filter':
		if(FALSE === ($id = format_userinput($_GET['id'], 'uint', TRUE, 6))) {
			trigger_error('Not allowed set_role_filter-id: '.$_POST['id'], E_USER_ERROR);
		}
		if($access['groups']['MAX'] < 1 || $access['leute']['MAX'] < 1 || !$id) continue;

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
		if(!$filter_akt) continue;

		//Set filter according to given rid
		$vars = array(1 => 'r'.$id);
		$_SESSION['filter'][] = array($filter_akt, $vars, 0);


		$_SESSION['show'] = 'show_all';
		$_SESSION['show_start'] = 1;
	break;




	case 'set_idfilter':
		if($access['leute']['MAX'] < 1) continue;

		//ID from GET
		$id = format_userinput($_GET['id'], 'uint');
		if(!$id) continue;

		//Get ID filter
		$f = db_select_data('ko_filter', "WHERE `name` = 'id'", '*', '', '', TRUE);
		if(!$f['id']) continue;

		//Apply filter
		$_SESSION['filter'] = array();
		$_SESSION['filter'][] = array($f['id'], array(1 => $id), 0);

		$_SESSION['show'] = 'show_all';
		$_SESSION['show_start'] = 1;
	break;




	case 'set_famfilter':
		if($access['leute']['MAX'] < 1) continue;
		$famid = format_userinput($_GET['famid'], 'uint');
		if($famid <= 0) continue;

		$f1 = db_select_data('ko_filter', "WHERE `name` = 'family'", '*', '', '', TRUE);
		if(!$f1['id']) continue;

		$_SESSION['filter'] = array();
		$_SESSION['filter'][] = array($f1['id'], array(1 => $famid), 0);

		$_SESSION['show'] = 'show_all';
		$_SESSION['show_start'] = 1;
	break;



	case 'set_dobfilter':
		if($access['leute']['MAX'] < 1) continue;
		list($d, $m) = explode('-', format_userinput($_GET['dob'], 'int'));
		if(!$m || !$d || $m < 1 || $m > 12 || $d < 1 || $d > 31) continue;

		$f1 = db_select_data('ko_filter', "WHERE `name` = 'birthdate'", '*', '', '', TRUE);
		if(!$f1['id']) continue;

		$_SESSION['filter'] = array();
		$_SESSION['filter'][] = array($f1['id'], array(1 => $d, 2 => $m), 0);

		$_SESSION['show'] = 'show_all';
		$_SESSION['show_start'] = 1;
	break;





	//Import
	case "import":
		if($access['leute']['ALL'] > 1 || ($access['leute']['MAX'] > 1 && ko_get_leute_admin_groups($_SESSION['ses_userid'], 'all') !== FALSE)) {
		} else continue;

		if($_GET["state"]) $_SESSION["import_state"] = format_userinput($_GET["state"], "uint");
		if($_GET["mode"]) $_SESSION["import_mode"] = format_userinput($_GET["mode"], "alpha");

		if($_POST["submit"]) {
			if($_SESSION["import_state"] == 2) {
				if($_SESSION["import_mode"] == "vcard") {
					//file
					$file = $_FILES["vcf"];
					if(substr($file["type"], 0, 4) == "text") {  //text/x-vcard, text/directory
						$content = file($file["tmp_name"]);
						$_SESSION["import_data"] = ko_parse_vcf($content);
						$_SESSION["import_state"] = 4;
					} else {
						$notifier->addError(15, $do_action);
					}
				}//vcard

				else if($_SESSION["import_mode"] == "csv") {
					//db columns
					if($_POST["sel_dbcols"]) {
						$_SESSION["import_csv"]["dbcols"] = explode(",", format_userinput($_POST["sel_dbcols"], "alphanumlist"));
					} else {
						$notifier->addError(17, $do_action);
					}
					//file
					ini_set('auto_detect_line_endings', '1'); // for mac-excel line endings (CR)
					$file = $_FILES["csv"];
					$content = file($file["tmp_name"]);
					//separator
					$sep = $_POST["txt_separator"];
					if(strlen($sep) == 1) {
						$_SESSION["import_csv"]["separator"] = $sep;
					} else {
						$notifier->addError(16, $do_action);
					}
					//content separator
					$sep = $_POST["txt_content_separator"];
					$_SESSION["import_csv"]["content_separator"] = $sep;
					//first line
					if($_POST["chk_first_line"]) {
						$_SESSION["import_csv"]["first_line"] = 1;
					} else {
						$_SESSION["import_csv"]["first_line"] = 0;
					}
					//File encoding
					if(in_array($_POST['sel_file_encoding'], array('utf-8', 'latin1', 'macintosh'))) {
						$_SESSION['import_csv']['file_encoding'] = $_POST['sel_file_encoding'];
					} else {
						$_SESSION['import_csv']['file_encoding'] = 'latin1';
					}

					if(!$notifier->hasErrors()) {
						if(ko_parse_csv($content, $_SESSION["import_csv"], TRUE)) {
							$_SESSION["import_data"] = ko_parse_csv($content, $_SESSION["import_csv"]);
							$_SESSION["import_state"] = 4;
						} else {
							$notifier->addError(18, $do_action);
						}
					}

				}//csv
			}//if(state == 2)
		}//if(submit)

		$_SESSION["show"] = "import";
	break;  //import


	case "do_import":
		if($access['leute']['ALL'] > 1 || ($access['leute']['MAX'] > 1 && ko_get_leute_admin_groups($_SESSION['ses_userid'], 'all') !== FALSE)) {
		} else continue;

		//Add all imported addresses to the selected group
		if($_POST["sel_group"]) {
			$add_group = format_userinput($_POST['sel_group'], 'group_role');
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
						if (!$added && (in_array($entryLower, $yes) || $entryLower != '')) {
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




	case "submit_leute_version":
		if($access['leute']['ALL'] < 4) continue;

		if($_POST["date_version"] == "") {
			unset($_SESSION["leute_version"]);
		} else {
			$version = format_userinput($_POST["date_version"], "date");
			//empty date
			if(strtotime($version) == 0) continue;
			//don't allow future dates
			if(strtotime($version) > time()) continue;

			$_SESSION["leute_version"] = sql_datum($version);
		}

		$_SESSION["show"] = "show_all";
	break;  //submit_leute_version



	case "clear_leute_version":
		if($access['leute']['ALL'] < 4) continue;

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
		if(!($access['leute']['ALL'] > 1 || $access['leute'][$leute_id] > 1)) continue;


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
		ko_update_familie_filter();

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
		ko_save_userpref($_SESSION['ses_userid'], 'leute_fam_checkbox', format_userinput($_POST['chk_fam_checkbox'], 'uint'));
		ko_save_userpref($_SESSION['ses_userid'], 'leute_carddav_filter', format_userinput($_POST['sel_carddav_filter'], 'uint'));

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
			$v = format_userinput($_POST['sel_leute_hidden_mode'], 'uint', FALSE, 1);
			if($v == 0 || $v == 1 || $v == 2) ko_set_setting('leute_hidden_mode', $v);
			ko_set_setting('leute_real_delete', format_userinput($_POST['chk_real_delete'], 'uint'));
			ko_set_setting('leute_no_delete_columns', format_userinput($_POST['sel_no_delete_columns'], 'alphanumlist'));
			ko_set_setting('leute_assign_global_notification', format_userinput($_POST['txt_assign_global_notification'], 'email', FALSE, 0, array(), ','));
		}


		$_SESSION['show'] = 'show_all';
	break;



	case 'global_assign':
		if(!ko_get_leute_admin_assign($_SESSION['ses_userid'], 'all')) continue;
		$gid = ko_get_leute_admin_groups($_SESSION['ses_userid'], 'all');
		if(!is_array($gid) || sizeof($gid) < 1) continue;

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
			//Get sender email (current user)
			$p = ko_get_logged_in_person();
			$sender = check_email($p['email']) ? $p['email'] : ko_get_setting('info_email');
			if($p['vorname'] || $p['nachname']) {
				$login = $p['vorname'].' '.$p['nachname'].' ('.$_SESSION['ses_username'].')';
			} else {
				$login = $_SESSION['ses_username'];
			}

			//Prepare email text
			$subject = '[kOOL] '.sprintf(getLL('leute_global_assign_notification_subject'), $login);
			$text = sprintf(getLL('leute_global_assign_notification_text'), $login, $group['name']);
			$text .= "\n\n".$address_text;
			$text .= "\n\n".getLL('leute_global_assign_notification_text_disclaimer');

			//Send notification email to all recipients
			$email = str_replace(';', ',', $email);
			foreach(explode(',', $email) as $e) {
				$e = trim($e);
				if(!check_email($e)) continue;
				//ko_send_mail($sender, $e, $subject, $text);
				ko_send_email($e, $subject, $text);
			}
		}

		$notifier->addInfo(16, $do_action);
	break;





	//Submenus
  case "move_sm_left":
  case "move_sm_right":
    ko_submenu_actions("leute", $do_action);
  break;



	//Default:
	default:
		if(!hook_action_handler($do_action))
      include($ko_path."inc/abuse.inc.php");
	break;

}//switch(action)


//HOOK: Plugins erlauben, die bestehenden Actions zu erweitern
hook_action_handler_add($do_action);


//Reread access rights (only needed if leute_admin_filter is set (d.h. access[leute] contains more than MAX, ALL, GS))
if(sizeof($access['leute']) > 3 && in_array($do_action, array('submit_neue_person', 'submit_edit_person', 'submit_als_neue_person', 'submit_multiedit', 'submit_gs', 'submit_gs_aa', 'submit_gs_new_person', 'submit_gs_ps', 'submit_gs_ps_aa', 'rollback', 'global_assign'))) {
	ko_get_access('leute', '', TRUE);
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
	$userpref = ko_get_userpref($_SESSION["ses_userid"], "show_leute_chart");
	if($userpref) {
		$_SESSION["show_leute_chart"] = explode(",", $userpref);
		foreach($_SESSION["show_leute_chart"] as $i => $chart) {
			if(!in_array($chart, $LEUTE_CHART_TYPES)) unset($_SESSION["show_leute_chart"][$i]);
		}
	} else {
		$_SESSION["show_leute_chart"] = $LEUTE_CHART_TYPES;
	}
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
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title><?php print "$HTML_TITLE: ".getLL("module_".$ko_menu_akt); ?></title>
<?php
print ko_include_css();

print ko_include_js(array($ko_path.'inc/jquery/jquery.js',
													$ko_path.'inc/kOOL.js',
													$ko_path.'inc/selectmenu.js',
													$ko_path.'inc/ZeroClipboard.min.js'
													),
										($_SESSION['show'] == 'list_kg' ? 'kg' : $ko_menu_akt));

include($ko_path.'inc/js-sessiontimeout.inc.php');
include("inc/js-leute.inc.php");
//Load JS files for js_calendar
$js_calendar->load_files();

//prepare group select for formular and group filter
$list_id = 1;

if($_SESSION["show"] == "neue_person" || $_SESSION["show"] == "edit_person") {
	$show_all_types = FALSE;
	//Beim Einteilen die vergangenen Gruppen nie anzeigen
	$orig_value = ko_get_userpref($_SESSION['ses_userid'], 'show_passed_groups');
	ko_save_userpref($_SESSION['ses_userid'], 'show_passed_groups', 0);
	include("inc/js-groupmenu.inc.php");
	ko_save_userpref($_SESSION['ses_userid'], 'show_passed_groups', $orig_value);
	$loadcode = "initList($list_id, document.formular.sel_ds0_input_groups);";
	$onload_code = $loadcode.$onload_code;
} else {
	ko_get_filter_by_id($_SESSION["filter_akt"], $akt_filter);
	if($akt_filter['_name'] == 'group') {
		$loadcode = "initList($list_id, document.getElementsByName('var1')[0]);";
		$onload_code = $loadcode.$onload_code;
	}
	//Beim Filter die vergangenen Gruppen gemäss Einstellung zeigen
	//allerdings auch Platzhalter-Gruppen anzeigen
	$show_all_types = TRUE;
	include("inc/js-groupmenu.inc.php");
}
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
print ko_get_submenu_code("leute", "left");
?>
</td>


<td class="main">
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

	case "sms_versand":
		$smarty->assign("tpl_send_sms", getLL('leute_sms_send'));
		$smarty->assign("tpl_sms_bal", getLL('leute_sms_balance'));
		$smarty->assign("tpl_sms_receiver", getLL('leute_sms_receiver'));
		$smarty->assign('tpl_sms_sender', getLL('leute_sms_sender'));
		$smarty->assign("tpl_sms_add_receiver", getLL('leute_sms_add_receiver'));
		$smarty->assign("tpl_sms_no_number", getLL('leute_sms_no_number'));
		$smarty->assign("tpl_sms_excel_file", getLL('leute_sms_excel_file'));
		$smarty->assign("tpl_sms_my_export", getLL('leute_sms_my_export'));
		$smarty->assign("tpl_sms_my_add", getLL('leute_sms_my_add'));
		$smarty->assign("tpl_sms_text", getLL('leute_sms_text'));
		$smarty->assign("tpl_sms_submit", getLL('leute_sms_submit'));
		$smarty->display("ko_formular_sms.tpl");
	break;

	case "email_versand":
		$smarty->assign("tpl_title1", getLL('leute_email_title1'));
		$smarty->assign("tpl_body1", getLL('leute_email_body1'));
		$smarty->assign("tpl_all_recip", getLL('leute_email_all_recipients'));
		$smarty->assign("tpl_all_recip_semicolon", getLL('leute_email_all_recipients_semicolon'));
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

	case "export_pdf":
		ko_export_leute_as_pdf_settings($layout_id);
	break;

	case "mutationsliste":
		ko_list_mod_leute();
	break;

	case "groupsubscriptions":
		ko_list_groupsubscriptions($_GET['gid']);
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
		ko_leute_import($_SESSION["import_state"], $_SESSION["import_mode"]);
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
</td>

<td class="main_right" name="main_right" id="main_right">

<?php
print ko_get_submenu_code("leute", "right");
?>

</td>
</tr>

<?php include($ko_path . "footer.php"); ?>

</table>

</body>
</html>
