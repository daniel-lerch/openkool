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
$ko_menu_akt = "groups";

require __DIR__ . '/../inc/ko.inc.php';
require __DIR__ . '/inc/groups.inc.php';
use OpenKool\koNotifier;

//Redirect to SSL if needed
ko_check_ssl();

if(!ko_module_installed("groups")) {
	header("Location: ".$BASE_URL."index.php");  //Absolute URL
}

ob_end_flush();  //Puffer flushen

$notifier = koNotifier::Instance();

//Get access rights
ko_get_access('groups');

//kOOL Table Array
ko_include_kota(array('ko_groups', 'ko_grouproles', 'ko_groups_datafields'));


//Alle Gruppen einlesen
ko_get_groups($all_groups);


//*** Plugins einlesen:
$hooks = hook_include_main("groups");
if(sizeof($hooks) > 0) foreach($hooks as $hook) include_once($hook);


//***Action auslesen:
if($_POST["action"]) $do_action = $_POST["action"];
else if($_GET["action"]) $do_action = $_GET["action"];
else $do_action = "";

//Reset show_start if from another module
if($_SERVER['HTTP_REFERER'] != '' && FALSE === mb_strpos($_SERVER['HTTP_REFERER'], '/'.$ko_menu_akt.'/')) $_SESSION['show_start'] = 1;

switch($do_action) {

	/**
	  * Anzeige
		*/
	case "list_groups":
		if(isset($_GET["gid"])) {
			if($_GET["gid"] == "NULL") $_SESSION["show_gid"] = "NULL";
			else $_SESSION["show_gid"] = format_userinput($_GET["gid"], "uint");
		} else {
			if(!isset($_SESSION["show_gid"]) || ko_get_userpref($_SESSION["ses_userid"], "groups_show_top") == 1) $_SESSION["show_gid"] = "NULL";
		}

		$id = $_SESSION["show_gid"];
		if( ($id == 'NULL' && $access['groups']['MAX'] > 0) || ($id != '' && ($access['groups']['ALL'] > 0 || $access['groups'][$id] > 0))) {
			if($_SESSION['show'] == 'list_groups') $_SESSION['show_start'] = 1;
			$_SESSION['show'] = 'list_groups';
			$_SESSION['show_back'] = $_SESSION['show'];
		}
	break;

	case "list_roles":
		if($access['groups']['MAX'] < 1) break;
		$_SESSION["show"] = "list_roles";
		$_SESSION["show_back"] = $_SESSION["show"];
	break;

	case "list_datafields":
		if($access['groups']['MAX'] < 3) break;
		$_SESSION["show"] = "list_datafields";
		$_SESSION["show_back"] = $_SESSION["show"];
	break;

	case "groups_settings":
		$_SESSION["show"] = "groups_settings";
	break;

	case "exportxls":
		if($access['groups']['MAX'] < 1) break;

		//Gruppen
		ko_get_grouproles($roles);
		$groups = ko_groups_get_recursive(ko_get_groups_zwhere(), TRUE, $_SESSION['show_gid']);
		$rowcounter = 0;

		//Add parent groups if export does not start at top
		if($_SESSION['show_gid'] != 'NULL') {
			$pid = $_SESSION['show_gid'];
			do {
				$parent = db_select_data('ko_groups', "WHERE `id` = '$pid'", '*', '', '', TRUE);
				$pid = $parent['pid'];
				array_unshift($groups, $parent);
			} while ($parent['pid'] != NULL);
		}

		//Find deepest level for intending
		$deepest_level = 0;
		foreach($groups as $gid => $grp) {
			if($access['groups']['ALL'] < 1 && $access['groups'][$grp['id']] < 1) {
				unset($groups[$gid]);
				continue;
			}
			//Kein Kreis-Vererbungen erlauben
			$mother_line = ko_groups_get_motherline($grp["id"], $all_groups);
			$groups[$gid]['_motherline'] = $mother_line;
			$deepest_level = max($deepest_level, sizeof($mother_line));
		}

		//Add header row
		$headerrow = array(getLL('kota_ko_groups_name'));
		for($i=0; $i<$deepest_level; $i++) {
			$headerrow[] = "";
		}
		$gs_pid = ko_get_setting('daten_gs_pid');
		$headerrow[] = getLL('kota_ko_groups_description');
		if($gs_pid) $headerrow[] = getLL('groups_listheader_event');
		$headerrow[] = getLL('kota_ko_groups_start');
		$headerrow[] = getLL('kota_ko_groups_stop');
		$headerrow[] = getLL('kota_listview_ko_groups_nump');
		$headerrow[] = getLL('kota_ko_groups_maxcount');
		$headerrow[] = getLL('kota_ko_groups_roles');


		//Prefetch all events with gs_gid
		if($gs_pid) {
			$gs_events = array();
			$events = db_select_data('ko_event', "WHERE `gs_gid` != ''");
			foreach($events as $event) {
				$gs_events[ko_groups_decode($event['gs_gid'], 'group_id')] = $event;
			}
		}

		//Prepare output, every group on a row
		foreach($groups as $grp) {
			//Display hierarchy
			for($i=0; $i<sizeof($grp['_motherline']); $i++) $data[$rowcounter][] = "";
			//Name
			$data[$rowcounter][] = $grp['name'];

			//Add empty rows according to the depth of this group's level
			for($i=0; $i<($deepest_level-sizeof($grp['_motherline'])); $i++) {
				$data[$rowcounter][] = "";
			}

			//Description
			$data[$rowcounter][] = $grp["description"] ? $grp["description"] : "-";

			//Event
			if($gs_pid) {
				$event = $gs_events[$grp['id']];
				if($event['startdatum']) {
					if($event['startdatum'] != $event['enddatum']) {
						$data[$rowcounter][] = strftime($DATETIME['dmY'], strtotime($event['startdatum'])).' - '.strftime($DATETIME['dmY'], strtotime($event['enddatum']));
					} else {
						$data[$rowcounter][] = strftime($DATETIME['dmY'], strtotime($event['startdatum']));
					}
				} else {
					$data[$rowcounter][] = '';
				}
			}

			//Start and stop
			$data[$rowcounter][] = $grp['start'] != '0000-00-00' ? strftime($DATETIME['dmY'], strtotime($grp['start'])) : '-';
			$data[$rowcounter][] = $grp['stop'] != '0000-00-00' ? strftime($DATETIME['dmY'], strtotime($grp['stop'])) : '-';

			//Total number of assigned addresses
			$num = db_get_count('ko_leute', 'id', "AND `deleted` = '0' AND `hidden` = '0' AND `groups` REGEXP 'g".$grp['id']."'");
			$data[$rowcounter][] = $num;

			//Max number
			if($grp['maxcount'] > 0) {
				$max = $grp['maxcount'];
				if($grp['count_role'] > 0) $max .= ' '.$roles[$grp['count_role']]['name'];
				$data[$rowcounter][] = $max;
			} else {
				$data[$rowcounter][] = '';
			}


			if($grp["type"]) {  //Dummy group
				//Stop here
			}
			//not a dummy group
			else {
				//Roles
				foreach(explode(",", $grp["roles"]) as $rid) {
					if(!$rid) continue;
					$num = db_get_count("ko_leute", "id", "AND `deleted` = '0' AND `hidden` = '0' AND `groups` REGEXP 'g".$grp["id"].":r".$rid."'");
					$data[$rowcounter][] = $num."x".$roles[$rid]["name"];
				}
			}
			$rowcounter++;
		}

		$filename = getLL("groups_filename_all").strftime("%d%m%Y_%H%M%S", time()).".xlsx";
		$title = getLL("groups_exportxls_title");
		$filename = basename(ko_export_to_xlsx($headerrow, $data, ($ko_path."download/excel/".$filename), $title));

		$onload_code = "ko_popup('".$ko_path."download.php?action=file&amp;file=download/excel/$filename');";
	break;



	/**
	  * Neu
		*/
	case "new_group":
		if($access['groups']['MAX'] < 3) break;
		$_SESSION["show"] = "new_group";
		$onload_code = "form_set_first_input();".$onload_code;
	break;


	case "submit_new_group":
		if($access['groups']['MAX'] < 3) break;

		$data = array();
		$data['pid']             = format_userinput($_POST['sel_parentgroup'], 'uint');
		$data['pid']             = $data['pid'] ? $data['pid'] : 'NULL';

		//Access check for pid
		if($data['pid'] == 'NULL' && $access['groups']['ALL'] < 3) break;
		if($data['pid'] != 'NULL' && $access['groups']['ALL'] < 3 && $access['groups'][$data['pid']] < 3) break;

		$data['linked_group']             = format_userinput($_POST['sel_linked_group'], 'uint');
		$data['linked_group']             = $data['linked_group'] ? $data['linked_group'] : 'NULL';

		//Access check for pid
		if($data['linked_group'] == 'NULL' && $access['groups']['ALL'] < 3) break;
		if($data['linked_group'] != 'NULL' && $access['groups']['ALL'] < 3 && $access['groups'][$data['linked_group']] < 3) break;

		$data["name"]            = format_userinput($_POST["txt_name"], "js");
		$data["description"]     = format_userinput($_POST["txt_description"], "text");
		$data["start"]           = sql_datum(format_userinput($_POST["txt_datum"], "date"));
		$data["stop"]            = sql_datum(format_userinput($_POST["txt_datum2"], "date"));
		$data["roles"]           = format_userinput($_POST["sel_roles"], "intlist");
		$data['rights_view']     = format_userinput($_POST['sel_rights_view'], 'intlist', FALSE, 0, array(), 'g');
		$data['rights_new']      = format_userinput($_POST['sel_rights_new'], 'intlist', FALSE, 0, array(), 'g');
		$data['rights_edit']     = format_userinput($_POST['sel_rights_edit'], 'intlist', FALSE, 0, array(), 'g');
		$data['rights_del']      = format_userinput($_POST['sel_rights_del'], 'intlist', FALSE, 0, array(), 'g');
		$data["crdate"]          = strftime("%Y-%m-%d %H:%M:%S", time());
		$data["type"]            = format_userinput($_POST["chk_type"], "uint");
		$data["ezmlm_list"]      = format_userinput($_POST["txt_ezmlm_list"], "email");
		$data["ezmlm_moderator"] = format_userinput($_POST["txt_ezmlm_moderator"], "email");
		if(ko_module_installed('mailing')) {
			$alias = str_replace('@', '', format_userinput($_POST['txt_mailing_alias'], 'email'));
			kota_mailing_check_unique_alias($alias, array('table' => 'ko_groups', 'id' => 0));
			$data['mailing_alias']   = $alias;

			$data['mailing_mod_logins'] = format_userinput($_POST['sel_mailing_mod_logins'], 'uint');
			$data['mailing_mod_role'] = format_userinput($_POST['sel_mailing_mod_role'], 'uint');
			$data['mailing_mod_members'] = format_userinput($_POST['sel_mailing_mod_members'], 'uint');
			$data['mailing_mod_others'] = format_userinput($_POST['sel_mailing_mod_others'], 'uint');
			$data['mailing_reply_to'] = format_userinput($_POST['sel_mailing_reply_to'], 'alpha');
			$data['mailing_modify_rcpts'] = format_userinput($_POST['sel_mailing_modify_rcpts'], 'uint');
		}
		$data['maxcount'] = format_userinput($_POST['txt_maxcount'], 'uint');
		$data['count_role'] = format_userinput($_POST['sel_count_role'], 'uint');

		//Datafields
		$dfids = explode(',', format_userinput($_POST['sel_datafields'], 'intlist'));
		foreach($dfids as $k => $v) {
			if(!$v) unset($dfids[$k]);
		}
		if(sizeof($dfids) > 0) {
			$datafields = db_select_data('ko_groups_datafields', "WHERE `id` IN (".implode(',', $dfids).")");
			foreach($dfids as $dfkey => $dfid) {
				if($datafields[$dfid]['preset'] == 1) {
					$new = $datafields[$dfid];
					unset($new['id']);
					unset($new['reusable']);
					unset($new['preset']);
					$new_dfid = zerofill(db_insert_data('ko_groups_datafields', $new), 6);
					$dfids[$dfkey] = $new_dfid;
				}
			}
			$data['datafields'] = implode(',', $dfids);
		} else {
			$data['datafields'] = '';
		}

		//In DB Speichern
		$new_id = db_insert_data("ko_groups", $data);
		ko_get_groups($all_groups);
		//Loggen
		ko_log_diff("new_group", $data);

		$_SESSION["show"] = "list_groups";
		$notifier->addInfo(3, $do_action);
	break;


	case "new_role":
		if($access['groups']['MAX'] < 3) break;

		$_SESSION["show"] = "new_role";
		$onload_code = "form_set_first_input();".$onload_code;
	break;

	case 'submit_new_role':
		if($access['groups']['MAX'] < 3) break;

		kota_submit_multiedit('', 'new_role', '', $changes);
		if(!$notifier->hasErrors()) {
			ko_update_grouprole_filter();
			$_SESSION['show'] = 'list_roles';
			$notifier->addInfo(4, $do_action);
		}
	break;


	case 'submit_as_new_role':
		if($access['groups']['MAX'] < 3) break;

		list($table, $columns, $ids, $hash) = explode('@', $_POST['id']);
		//Fake POST[id] for kota_submit_multiedit() to remove the id from the data. Otherwise this entry will be edited.
		$new_hash = md5(md5($mysql_pass.$table.implode(':', explode(',', $columns)).'0'));
		$_POST['id'] = $table.'@'.$columns.'@0@'.$new_hash;

		kota_submit_multiedit('', 'new_role', '', $changes);
		if(!$notifier->hasErrors()) {
			ko_update_grouprole_filter();
			$_SESSION['show'] = 'list_roles';
			$notifier->addInfo(4, $do_action);
		}
	break;




	/**
	  * Bearbeiten
		*/
	case "edit_group":
		$edit_id = format_userinput($_POST["id"], "uint");
		if(!$edit_id && isset($_GET['id']) && $_GET['action'] == 'edit_group') $edit_id = format_userinput($_GET['id'], 'uint');
		if($access['groups']['ALL'] > 2 || $access['groups'][$edit_id] > 2) {
			$_SESSION["show"] = "edit_group";
			$onload_code = "form_set_first_input();".$onload_code;
		}
	break;


	case "submit_edit_group":
		$id = format_userinput($_POST["id"], "uint");
		if($access['groups']['ALL'] < 3 && $access['groups'][$id] < 3) break;

		$old_group = $all_groups[$id];

		$data = array();
		$data["name"]            = format_userinput($_POST["txt_name"], "js");
		$data["description"]     = format_userinput($_POST["txt_description"], "text");
		$data["start"]           = sql_datum(format_userinput($_POST["txt_datum"], "date"));
		$data["stop"]            = sql_datum(format_userinput($_POST["txt_datum2"], "date"));
		$data["linked_group"]    = format_userinput($_POST["sel_linked_group"], "uint");
		$data["linked_group"]    = $data["linked_group"] ? $data["linked_group"] : "NULL";
		$data["pid"]             = format_userinput($_POST["sel_parentgroup"], "uint");
		$data["pid"]             = $data["pid"] ? $data["pid"] : "NULL";
		if(ko_module_installed('mailing')) {
			$alias = str_replace('@', '', format_userinput($_POST['txt_mailing_alias'], 'email'));
			kota_mailing_check_unique_alias($alias, array('table' => 'ko_groups', 'id' => $id));
			$data['mailing_alias']   = $alias;

			$data['mailing_mod_logins'] = format_userinput($_POST['sel_mailing_mod_logins'], 'uint');
			$data['mailing_mod_role'] = format_userinput($_POST['sel_mailing_mod_role'], 'uint');
			$data['mailing_mod_members'] = format_userinput($_POST['sel_mailing_mod_members'], 'uint');
			$data['mailing_mod_others'] = format_userinput($_POST['sel_mailing_mod_others'], 'uint');
			$data['mailing_reply_to'] = format_userinput($_POST['sel_mailing_reply_to'], 'alpha');
			$data['mailing_modify_rcpts'] = format_userinput($_POST['sel_mailing_modify_rcpts'], 'uint');
		}
		$data["roles"]           = format_userinput($_POST["sel_roles"], "intlist");
		$data["type"]            = format_userinput($_POST["chk_type"], "uint");
		$data["ezmlm_list"]      = format_userinput($_POST["txt_ezmlm_list"], "email");
		$data["ezmlm_moderator"] = format_userinput($_POST["txt_ezmlm_moderator"], "email");
		$data['maxcount']        = format_userinput($_POST['txt_maxcount'], 'uint');
		$data['count_role']      = format_userinput($_POST['sel_count_role'], 'uint');

		//Datafields
		$dfids = explode(',', format_userinput($_POST['sel_datafields'], 'intlist'));
		foreach($dfids as $k => $v) {
			if(!$v) unset($dfids[$k]);
		}
		if(sizeof($dfids) > 0) {
			$datafields = db_select_data('ko_groups_datafields', "WHERE `id` IN (".implode(',', $dfids).")");
			foreach($dfids as $dfkey => $dfid) {
				if($datafields[$dfid]['preset'] == 1) {
					$new = $datafields[$dfid];
					unset($new['id']);
					unset($new['reusable']);
					unset($new['preset']);
					$new_dfid = zerofill(db_insert_data('ko_groups_datafields', $new), 6);
					$dfids[$dfkey] = $new_dfid;
				}
			}
			$data['datafields'] = implode(',', $dfids);
		} else {
			$data['datafields'] = '';
		}

		//Rechte
    if(isset($_POST["sel_rights_view"])) {  //Only handle rights, if set in form
      $data['rights_view'] = format_userinput($_POST['sel_rights_view'], 'intlist', FALSE, 0, array(), 'g');
      $data['rights_new']  = format_userinput($_POST['sel_rights_new'], 'intlist', FALSE, 0, array(), 'g');
      $data['rights_edit'] = format_userinput($_POST['sel_rights_edit'], 'intlist', FALSE, 0, array(), 'g');
      $data['rights_del']  = format_userinput($_POST['sel_rights_del'], 'intlist', FALSE, 0, array(), 'g');
      //Rechte aufräumen
      $rv = explode(",", $data["rights_view"]);
      $rn = explode(",", $data["rights_new"]);
      $re = explode(",", $data["rights_edit"]);
      $rd = explode(',', $data['rights_del']);
      foreach($rv as $i => $v) if(in_array($v, $rn) || in_array($v, $re) || in_array($v, $rd)) unset($rv[$i]);
      foreach($rn as $i => $v) if(in_array($v, $re) || in_array($v, $rd)) unset($rn[$i]);
      foreach($re as $i => $v) if(in_array($v, $rd)) unset($re[$i]);
      $data["rights_view"] = implode(",", $rv);
      $data["rights_new"] = implode(",", $rn);
      $data['rights_edit'] = implode(',', $re);
    }
	
		//In DB speichern
		db_update_data("ko_groups", "WHERE `id` = '$id'", $data);
		ko_get_groups($all_groups);
		//Loggen
		ko_log_diff("edit_group", $data, $old_group);
		//Eingetragene Gruppen/Rollen in Personendaten aktualisieren
		ko_update_groups_and_roles($id);
		//Delete entries of datafields not used anymore
		$new_df = explode(",", $data["datafields"]);
		foreach(explode(",", $old_group["datafields"]) as $df) {
			if(in_array($df, $new_df)) continue;
			else db_delete_data("ko_groups_datafields_data", "WHERE `group_id` = '$id' AND `datafield_id` = '$df'");
		}
		//Initial export to ezmlm if given
		if($_POST["chk_ezmlm_export"]) ko_export_group_to_ezmlm($id);

		//Update stored filter presets containing group, as the filters store the full group path, which might have changed.
		if($old_group['pid'] != $data['pid']) {
			ko_update_group_filterpresets();
		}

		//Update group's count (needed if count_role has changed)
		ko_update_group_count($id, $data['count_role']);

		$_SESSION["show"] = "list_groups";
		$notifier->addInfo(2, $do_action);
	break;


	case "edit_role":
		$edit_id = format_userinput($_POST["id"], "uint");
		if($access['groups']['MAX'] < 3) break;

		//Check for access level 3 for all group this role is being used in
		$groups = db_select_data("ko_groups", "WHERE `roles` LIKE '$edit_id'");
		$do_edit = TRUE;
		foreach($groups as $group) {
			if($access['groups']['ALL'] < 3 && $access['groups'][$group['id']] < 3) $do_edit = FALSE;
		}
		if($do_edit) {
			$_SESSION["show"] = "edit_role";
			$onload_code = "form_set_first_input();".$onload_code;
		}
	break;


	case 'submit_edit_role':
		if($access['groups']['MAX'] < 3) break;

		list($table, $col, $edit_id) = explode('@', $_POST['id']);

		//Check for access level 3 for all group this role is being used in
		$groups = db_select_data("ko_groups", "WHERE `roles` LIKE '$edit_id'");
		$do_edit = TRUE;
		foreach($groups as $group) {
			if($access['groups']['ALL'] < 3 && $access['groups'][$group['id']] < 3) $do_edit = FALSE;
		}
		if($do_edit) {
			kota_submit_multiedit(0, 'edit_role');
			if(!$notifier->hasErrors()) {
				ko_update_grouprole_filter();
				$_SESSION['show'] = 'list_roles';
				$notifier->addInfo(2, $do_action);
			}
		}
	break;



	case "edit_datafield":
		$edit_id = format_userinput($_POST["id"], "uint");
		if($access['groups']['MAX'] < 3) break;

		$_SESSION["show"] = "edit_datafield";
		$onload_code = "form_set_first_input();".$onload_code;
	break;


	case "submit_edit_datafield":
		if($access['groups']['MAX'] < 3) break;

		kota_submit_multiedit('', 'edit_datafield');
		if(!$notifier->hasErrors()) {
			ko_update_grouprole_filter();
			$notifier->addInfo(2, $do_action);
		}
		$_SESSION['show'] = 'list_datafields';
	break;





	/**
	  * Delete a group
		*/
	case 'delete_group':
		$del_id = format_userinput($_POST['id'], 'uint');
		if(($access['groups']['ALL'] > 3 || $access['groups'][$del_id] > 3) && db_get_count('ko_groups', 'id', "AND `pid` = '$del_id'") == 0 ) {
			//Delete group itself
			$old = $all_groups[$del_id];
			db_delete_data('ko_groups', "WHERE `id` = '$del_id'");
			//Delete group datafields' data
			db_delete_data('ko_groups_datafields_data', "WHERE `group_id` = '$del_id'");
			//Delete all pid entries in other groups, that were subgroups of this (should not be the case)
			db_update_data('ko_groups', "WHERE `pid` = '$del_id'", array('pid' => 'NULL'));
			ko_get_groups($all_groups);
			//Create log entry
			ko_log_diff('del_group', $old);
			//Update group assignments of addresses (e.g. if group's position in the hierarchy changed)
			ko_update_groups_and_roles($del_id);
			//Remove connection of events to this group (from group subscriptions)
			db_update_data('ko_event', "WHERE `gs_gid` LIKE 'g$del_id%'", array('gs_gid' => ''));

			$notifier->addInfo(1, $do_action);
		}
	break;


	//Sicherheits-Abfrage!
	case "delete_role":
		$del_id = format_userinput($_POST["id"], "uint");

		//Check for access level 3 for all group this role is being used in
		$groups = db_select_data('ko_groups', "WHERE `roles` LIKE '$del_id'");
		$do_del = TRUE;
		foreach($groups as $group) {
			if($access['groups']['ALL'] < 4 && $access['groups'][$group['id']] < 4) $do_del = FALSE;
		}
		if($do_del) {
			$_SESSION['show'] = 'delete_role';
		}
	break;


	//Wirklich löschen
	case "do_delete_role":
		$del_id = format_userinput($_POST['id'], 'uint');

		//Check for access level 3 for all group this role is being used in
		$groups = db_select_data('ko_groups', "WHERE `roles` LIKE '$del_id'");
		$do_del = TRUE;
		foreach($groups as $group) {
			if($access['groups']['ALL'] < 4 && $access['groups'][$group['id']] < 4) $do_del = FALSE;
		}
		if($do_del) {
			ko_get_grouproles($old_role, "AND `id` = '$del_id'");
			//Rolle löschen
			db_delete_data("ko_grouproles", "WHERE `id` = '$del_id'");
			//Rollen in alle Gruppen löschen, in denen sie vorkommt
			$gruppen = db_select_data("ko_groups", "WHERE `roles` REGEXP '$del_id'");
			foreach($gruppen as $gruppe) {
				$roles = explode(",", $gruppe["roles"]);
				foreach($roles as $r_i => $role) if($role == $del_id) unset($roles[$r_i]);
				db_update_data("ko_groups", "WHERE `id` = '".$gruppe["id"]."'", array("roles" => implode(",", $roles)));
			}
			//Gespeicherte Gruppenzuteilungen der Leute aktualisieren
			ko_update_groups_and_roles('', $del_id);
			//Loggen
			ko_log_diff("del_grouprole", $old_role[$del_id]);
			ko_update_grouprole_filter();

			$notifier->addInfo(1, $do_action);
		}
		$_SESSION["show"] = "list_roles";
	break;



	case "delete_datafield":
		$del_id = format_userinput($_POST["id"], "uint");
		if($access['groups']['MAX'] < 3) break;

		//Prüfen, ob Datenfeld noch irgendwo verwendet wird, dann kann man es nicht löschen
		$num = db_get_count("ko_groups", "id", "AND `datafields` REGEXP '$del_id'");
		if($num == 0) {
			$old = db_select_data("ko_groups_datafields", "WHERE `id` = '$del_id'");
			db_delete_data("ko_groups_datafields", "WHERE `id` = '$del_id'");
			ko_log("del_datafield", $old["description"]." (".$old["type"].")");
		}
	break;





	/**
	  * Berechtigungen
		*/
	case "list_rights":
		if($access['groups']['MAX'] < 3) break;
		$_SESSION["show_start"] = 1;
		$_SESSION["show"] = "list_rights";
		$_SESSION["show_back"] = $_SESSION["show"];
	break;


	case "edit_login_rights":
		$edit_id = format_userinput($_POST["id"], "uint");
		if(!$edit_id) break;
		$_SESSION["show"] = "edit_login_rights";
	break;


	case "submit_edit_login_rights":
		if($access['groups']['ALL'] < 3) break;
		$login_id = format_userinput($_POST["id"], "uint");
		if(!$login_id || ($login_id == ko_get_root_id() && $_SESSION["ses_userid"] != ko_get_root_id()) ) break;

		//Loop über die drei Rechte-Stufen
		$mode = array('', 'view', 'new', 'edit', 'del');
		for($i=3; $i>0; $i--) {
			if(isset($_POST["sel_rights_".$mode[$i]])) {
				//Nur Änderungen bearbeiten
				$old = explode(",", format_userinput($_POST["old_sel_rights_".$mode[$i]], "intlist", FALSE, 0, array(), ":"));
				$new = explode(",", format_userinput($_POST["sel_rights_".$mode[$i]], "intlist", FALSE, 0, array(), ":"));
				$deleted = array_diff($old, $new);
				$added = array_diff($new, $old);
			
				//Login aus gelöschten Gruppen entfernen
				foreach($deleted as $id) {
					$id = mb_substr($id, -6);  //Nur letzte ID verwenden, davor steht die Motherline
					//bisherige Rechte auslesen
					$group = db_select_data("ko_groups", "WHERE `id` = '$id'", "id,rights_".$mode[$i]);
					$rights_array = explode(",", $group[$id]["rights_".$mode[$i]]);
					//Zu löschendes Login finden und entfernen
					foreach($rights_array as $index => $right) if($right == $login_id) unset($rights_array[$index]);
					foreach($rights_array as $a => $b) if(!$b) unset($rights_array[$a]);  //Leere Einträge löschen
					//Neuer Eintrag in Gruppe speichern
					db_update_data("ko_groups", "WHERE `id` = '$id'", array("rights_".$mode[$i] => implode(",", $rights_array)));
					$all_groups[$id]['rights_'.$mode[$i]] = implode(',', $rights_array);
				}

				//Login in neu hinzugefügten Gruppen hinzufügen
				foreach($added as $id) {
					$id = mb_substr($id, -6);  //Nur letzte ID verwenden, davor steht die Motherline
					//Bestehende Rechte auslesen
					$group = db_select_data("ko_groups", "WHERE `id` = '$id'", "id,rights_".$mode[$i]);
					$rights_array = explode(",", $group[$id]["rights_".$mode[$i]]);
					//Überprüfen, ob Login schon vorhanden ist (sollte nicht)
					$add = TRUE;
					foreach($rights_array as $right) if($right == $login_id) $add = FALSE;
					if($add) $rights_array[] = $login_id;
					foreach($rights_array as $a => $b) if(!$b) unset($rights_array[$a]);  //Leere Einträge löschen
					//Neue Liste der Logins in Gruppe speichern
					db_update_data("ko_groups", "WHERE `id` = '$id'", array("rights_".$mode[$i] => implode(",", $rights_array)));
					$all_groups[$id]['rights_'.$mode[$i]] = implode(',', $rights_array);
				}
			}//if(isset(_POST[sel_rights_*]))
		}//for(i=1..3)

		$_SESSION["show"] = 'list_rights';
	break;



	case 'submit_groups_settings':
		ko_save_userpref($_SESSION['ses_userid'], 'show_limit_groups', format_userinput($_POST['txt_limit_groups'], 'uint'));
		ko_save_userpref($_SESSION['ses_userid'], 'default_view_groups', format_userinput($_POST['sel_default_view'], 'js'));
		ko_save_userpref($_SESSION['ses_userid'], 'show_passed_groups', format_userinput($_POST['chk_show_passed_groups'], 'uint'));
		ko_save_userpref($_SESSION['ses_userid'], 'groups_filterlink_add_column', format_userinput($_POST['chk_groups_filterlink_add_column'], 'uint'));
		ko_save_userpref($_SESSION['ses_userid'], 'groups_show_top', format_userinput($_POST['chk_groups_show_top'], 'uint'));

		$_SESSION['show'] = $_SESSION['show_back'] ? $_SESSION['show_back'] : 'groups_settings';
	break;




	case "multiedit":
		if($access['groups']['MAX'] < 3) break;

		//Zu bearbeitende Spalten
		$columns = explode(",", format_userinput($_POST["id"], "alphanumlist"));
		foreach($columns as $column) {
			$do_columns[] = $column;
		}
		if(sizeof($do_columns) < 1) $notifier->addError(8, $do_action);

		if($_SESSION["show"] == "list_groups") {
			//Zu bearbeitende Einträge
			$do_ids = array();
			foreach($_POST["chk"] as $c_i => $c) {
				if($c) {
					if(FALSE === ($edit_id = format_userinput($c_i, "uint", TRUE))) {
						trigger_error("Not allowed multiedit_id: ".$c_i, E_USER_ERROR);
					}
					if($access['groups']['ALL'] > 2 || $access['groups'][$edit_id] > 2) $do_ids[] = $edit_id;
				}
			}
			if(sizeof($do_ids) < 1) $notifier->addError(2, $do_action);

			//Daten für Formular-Aufruf vorbereiten
			if(!$notifier->hasErrors()) {
				$order = "ORDER BY ".$_SESSION["sort_groups"]." ".$_SESSION["sort_groups_order"];
				$_SESSION["show_back"] = $_SESSION["show"];
				$_SESSION["show"] = "multiedit";
			}


		/* Rollen */
		} else if($_SESSION['show'] == 'list_roles') {
			//Zu bearbeitende Einträge
			$do_ids = array();
			foreach($_POST['chk'] as $c_i => $c) {
				if($c) {
					if(FALSE === ($edit_id = format_userinput($c_i, 'uint', TRUE))) {
						trigger_error('Not allowed multiedit_id: '.$c_i, E_USER_ERROR);
					}
					if($access['groups']['ALL'] > 2) $do_ids[] = $edit_id;
				}
			}
			if(sizeof($do_ids) < 1) $notifier->addError(2, $do_action);

			//Daten für Formular-Aufruf vorbereiten
			if(!$notifier->hasErrors()) {
				$order = 'ORDER BY name ASC';
				$_SESSION['show_back'] = $_SESSION['show'];
				$_SESSION['show'] = 'multiedit_roles';
			}
		}

		$onload_code = "form_set_first_input();".$onload_code;
	break;



	case "submit_multiedit":
		if($_SESSION["show"] == "multiedit") {
			if($access['groups']['MAX'] < 3) break;
			kota_submit_multiedit(3);
		} else if($_SESSION["show"] == "multiedit_roles") {
			if($access['groups']['MAX'] < 3) break;
			kota_submit_multiedit(3);
		}

		if(!$notifier->hasErrors()) $notifier->addInfo(6, $do_action);
		$_SESSION["show"] = $_SESSION["show_back"] ? $_SESSION["show_back"] : "list_groups";
	break;



	case 'set_dffilter':
		if($access['groups']['ALL'] < 3) break;
		$_SESSION['groups_show_hidden_datafields'] = TRUE;
		ko_save_userpref($_SESSION['ses_userid'], 'groups_show_hidden_datafields', 1);
	break;


	case 'unset_dffilter':
		if($access['groups']['ALL'] < 3) break;
		$_SESSION['groups_show_hidden_datafields'] = FALSE;
		ko_save_userpref($_SESSION['ses_userid'], 'groups_show_hidden_datafields', 0);
	break;




	case 'export_pdf':
		$layout_id = format_userinput($_GET['layout_id'], 'uint');
		if(!$layout_id) break;

		$layout = db_select_data('ko_pdf_layout', "WHERE `id` = '$layout_id'", '*', '', '', TRUE);
		if($layout['data'] != '' && mb_substr($layout['data'], 0, 4) == 'FCN:' && function_exists(mb_substr($layout['data'], 4))) {
			$filename = call_user_func(mb_substr($layout['data'], 4), $_GET);
		}

		if($filename) {
			$onload_code = "ko_popup('".$ko_path.'download.php?action=file&amp;file='.mb_substr($filename, 3)."');";
		} else {
			$notifier->addError(3, $do_action);
		}
		$_SESSION['show'] = 'list_groups';
	break;





	//Submenus
  case "move_sm_left":
  case "move_sm_right":
    ko_submenu_actions("groups", $do_action);
  break;


	//Default:
	default:
		hook_action_handler($do_action);
	break;


}//switch(do_action)

//HOOK: Plugins erlauben, die bestehenden Actions zu erweitern
hook_action_handler_add($do_action);


//Reread access rights if necessary
if(in_array($do_action, array('submit_new_group', 'submit_edit_group', 'delete_group', 'submit_edit_login_rights'))) {
	ko_get_access('groups', '', TRUE);
	ko_get_groups($all_groups);
}


//***Defaults einlesen
if(!$_SESSION['sort_groups']) {
	$_SESSION['sort_groups'] = ko_get_userpref($_SESSION['ses_userid'], 'sort_groups');
	if(!$_SESSION['sort_groups']) $_SESSION['sort_groups'] = 'name';
}
if(!$_SESSION['sort_groups_order']) {
	$_SESSION['sort_groups_order'] = ko_get_userpref($_SESSION['ses_userid'], 'sort_groups_order');
	if(!$_SESSION['sort_groups_order']) $_SESSION['sort_groups_order'] = 'ASC';
}
if(!$_SESSION["show_start"]) $_SESSION["show_start"] = 1;
$_SESSION["show_limit"] = ko_get_userpref($_SESSION["ses_userid"], "show_limit_groups");
if(!$_SESSION["show_limit"]) $_SESSION["show_limit"] = ko_get_setting("show_limit_groups");
if(!isset($_SESSION['groups_show_hidden_datafields'])) $_SESSION['groups_show_hidden_datafields'] = ko_get_userpref($_SESSION['ses_userid'], 'groups_show_hidden_datafields');

//Smarty-Templates-Engine laden
require __DIR__ . '/../inc/smarty.inc.php';

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

$js_files = array($ko_path.'inc/jquery/jquery.js', $ko_path.'inc/kOOL.js');
if($_SESSION['show'] == 'edit_login_rights') $js_files[] = $ko_path.'inc/selectmenu.js';
print ko_include_js($js_files);

include __DIR__ . '/../inc/js-sessiontimeout.inc.php';
include __DIR__ . '/inc/js-groups.inc.php';
$js_calendar->load_files();

//Bei der Bearbeitung von Login-Rechten Ajax einbinden und alles für die drei selectmenus
if($_SESSION['show'] == 'edit_login_rights') {
	//Show dummy-groups (Platzhalter) because the rights will be propagated downwards to all children
	$show_all_types = TRUE;
	//Show all groups, also terminated ones:
	$show_passed_groups = ko_get_userpref($_SESSION['ses_userid'], 'show_passed_groups');
	ko_save_userpref($_SESSION['ses_userid'], 'show_passed_groups', 1);
	//View
	$list_id = 1;
	include __DIR__ . '/../leute/inc/js-groupmenu.inc.php';
	$loadcode = "initList($list_id, document.formular.sel_ds1_sel_rights_view);";
	//New
	$list_id = 2;
	include __DIR__ . '/../leute/inc/js-groupmenu.inc.php';
	$loadcode .= "initList($list_id, document.formular.sel_ds1_sel_rights_new);";
	//Edit
	$list_id = 3;
	include __DIR__ . '/../leute/inc/js-groupmenu.inc.php';
	$loadcode .= "initList($list_id, document.formular.sel_ds1_sel_rights_edit);";
	//Del
	$list_id = 4;
	include __DIR__ . '/../leute/inc/js-groupmenu.inc.php';
	$loadcode .= "initList($list_id, document.formular.sel_ds1_sel_rights_del);";
	$onload_code = $loadcode.$onload_code;
	//Reset setting to original value
	ko_save_userpref($_SESSION['ses_userid'], 'show_passed_groups', $show_passed_groups);
}
?>
</head>

<body onload="session_time_init();<?php print $onload_code; ?>">

<?php
/*
 * Gibt bei erfolgreichem Login das Menü aus, sonst einfach die Loginfelder
 */
require __DIR__ . '/../inc/menu.inc.php';
?>


<table width="100%">
<tr> 

<!-- Submenu -->
<td class="main_left" name="main_left" id="main_left">
<?php
print ko_get_submenu_code("groups", "left");
?>
&nbsp;
</td>


<!-- Hauptbereich -->
<td class="main">

<form action="index.php" method="post" name="formular">  <!-- Hauptformular -->
<input type="hidden" name="action" id="action" value="" />
<input type="hidden" name="id" id="id" value="" />
<div name="main_content" id="main_content">

<?php
if($notifier->hasNotifications(koNotifier::ALL)) {
	$notifier->notify();
}

hook_show_case_pre($_SESSION["show"]);

switch($_SESSION["show"]) {

	case "list_groups":
		ko_groups_list();
	break;

	case "new_group":
		ko_groups_formular_group("new");
	break;

	case "edit_group":
		ko_groups_formular_group("edit", $edit_id);
	break;

	case "list_roles":
		ko_groups_list_roles();
	break;

	case "new_role":
		ko_groups_formular_role('new');
	break;

	case "edit_role":
		ko_groups_formular_role("edit", $edit_id);
	break;

	case "delete_role":
		ko_delete_role($del_id);
	break;

	case "list_datafields":
		ko_groups_list_datafields();
	break;

	case "edit_datafield":
		ko_groups_formular_datafield("edit", $edit_id);
	break;

	case "multiedit":
		ko_multiedit_formular("ko_groups", $do_columns, $do_ids, $order, array("cancel" => "list_groups"));
	break;

	case 'multiedit_roles':
		ko_multiedit_formular('ko_grouproles', $do_columns, $do_ids, $order, array('cancel' => 'list_roles'));
	break;

	case "list_rights":
		ko_groups_list_rights();
	break;

	case "edit_login_rights":
		ko_groups_rights_formular($edit_id);
	break;

	case "groups_settings":
		ko_groups_settings();
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
print ko_get_submenu_code("groups", "right");
?>
&nbsp;
</td>
</tr>

<?php include __DIR__ . '/../config/footer.php' ?>

</table>

</body>
</html>
