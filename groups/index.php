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
$ko_menu_akt = "groups";

include_once($ko_path . "inc/ko.inc");
include_once("inc/groups.inc");

//Redirect to SSL if needed
ko_check_ssl();

if(!ko_module_installed("groups")) {
	header("Location: ".$BASE_URL."index.php"); exit;
}

//Don't allow checkin user
if($_SESSION['ses_userid'] == ko_get_checkin_user_id()) {
	header('Location: '.$BASE_URL.'index.php'); exit;
}

ob_end_flush();  //Puffer flushen

$notifier = koNotifier::Instance();

//Get access rights
ko_get_access('groups');

//kOOL Table Array
ko_include_kota(array('ko_groups', 'ko_grouproles', 'ko_groups_datafields', 'ko_groups_assignment_history'));


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
if($_SERVER['HTTP_REFERER'] != '' && FALSE === strpos($_SERVER['HTTP_REFERER'], '/'.$ko_menu_akt.'/')) $_SESSION['show_start'] = 1;

// This variable may contain the id of a group that should be highlighted when displaying the list
$highlight_group = NULL;

switch($do_action) {

	/**
	  * Anzeige
		*/
	case "list_groups":
		if(isset($_GET["gid"])) {
			if($_GET["gid"] == "NULL") $_SESSION["show_gid"] = "NULL";
			else $_SESSION["show_gid"] = format_userinput($_GET["gid"], "uint");
		} else {
			if(!isset($_SESSION["show_gid"])) {
				$_SESSION['show_gid'] = 'NULL';
				if(ko_get_userpref($_SESSION["ses_userid"], "groups_show_top") == 1) {
					$_SESSION["show_gid"] = "NULL";
				} else {
					//If read access is not given for all groups:
					// Go as deep into the group structure as possible, by opening all groups where there
					// is access to only one group on this level
					if($access['groups']['ALL'] < 1) {
						$allIds = array_filter(array_keys($access['groups']), function($e) {return is_numeric($e);});
						$topIds = array();
						if(sizeof($allIds) > 0) {
							$topIds = db_select_data('ko_groups', "WHERE `pid` IS NULL AND `id` IN (".implode(',', $allIds).")", 'id,pid');
						}
						if(sizeof($topIds) == 1) {
							$stop = FALSE;
							$parentId = array_pop(array_keys($topIds));
							$counter = 0;
							while(!$stop) {
								$counter++;
								if($counter > 100) $stop = TRUE;
								$childrenIds = db_select_data('ko_groups', "WHERE `pid` = '$parentId' AND `id` IN (".implode(',', $allIds).")", 'id,pid');
								if(sizeof($childrenIds) > 1) {
									$stop = TRUE;
								} else if(sizeof($childrenIds) < 1) {
									$stop = TRUE;
									$parentId = $lastParentId;
								} else {
									$lastParentId = $parentId ? $parentId : 'NULL';
									$parentId = array_pop(array_keys($childrenIds));
								}
							}
							$_SESSION['show_gid'] = $parentId;
						}
					}
				}

			}
		}

		$id = $_SESSION["show_gid"];
		if( ($id == 'NULL' && $access['groups']['MAX'] > 0) || ($id != '' && ($access['groups']['ALL'] > 0 || $access['groups'][$id] > 0))) {
			if($_SESSION['show'] == 'list_groups') $_SESSION['show_start'] = 1;
			$_SESSION['show'] = 'list_groups';
			$_SESSION['show_back'] = $_SESSION['show'];
		}

		// hightlight certain group (used for group search)
		if ($_GET['highlight']) {
			$highlight_group = intval($_GET['highlight']);
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

			//Stop
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
		//Access check for linked group
		if($access['groups']['ALL'] < 2 && $access['groups'][$data['linked_group']] < 2) $data['linked_group'] = '';

		$data["name"]            = format_userinput($_POST["txt_name"], "js");
		$data["description"]     = format_userinput($_POST["txt_description"], "text");
		$data["stop"]            = sql_datum(format_userinput($_POST["txt_datum2"], "date"));
		$data['deadline']        = sql_datum(format_userinput($_POST['txt_deadline'], 'date'));
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
			$data['mailing_rectype'] = format_userinput($_POST['sel_mailing_rectype'], 'alpha');
			$data['mailing_crm_project_id'] = format_userinput($_POST['sel_mailing_crm_project_id'],'uint');

			$data['mailing_prefix']  = format_userinput($_POST["txt_mailing_prefix"], "text");
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

		if(ko_module_installed("taxonomy")) {
			$taxonomy_terms = explode(",", format_userinput($_POST["terms"], "intlist"));
			ko_taxonomy_clear_terms_on_node("ko_groups", $new_id);
			ko_taxonomy_attach_terms_to_node($taxonomy_terms, "ko_groups", $new_id);
		}

		$GLOBALS['insertedIds']['ko_groups'][] = $new_id;
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

		kota_submit_multiedit('', 'new_role', $changes);
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

		kota_submit_multiedit('', 'new_role', $changes);
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
		$data["stop"]            = sql_datum(format_userinput($_POST["txt_datum2"], "date"));
		$data['deadline']        = sql_datum(format_userinput($_POST['txt_deadline'], 'date'));
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
			$data['mailing_rectype'] = format_userinput($_POST['sel_mailing_rectype'], 'alpha');
			$data['mailing_crm_project_id'] = format_userinput($_POST['sel_mailing_crm_project_id'],'uint');

			$data['mailing_prefix']  = format_userinput($_POST["txt_mailing_prefix"], "text");
		}

		if(ko_module_installed("taxonomy")) {
			$taxonomy_terms = explode(",", format_userinput($_POST["terms"], "intlist"));
			ko_taxonomy_clear_terms_on_node("ko_groups", $id);
			ko_taxonomy_attach_terms_to_node($taxonomy_terms, "ko_groups", $id);
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
		$old_df = explode(",", $old_group["datafields"]);
		foreach($old_df as $df) {
			if(in_array($df, $new_df)) continue;
			else db_delete_data("ko_groups_datafields_data", "WHERE `group_id` = '$id' AND `datafield_id` = '$df'");
		}
		// initialize datafield entries for group members in case new datafields were added
		foreach ($new_df as $df) {
			if (in_array($df, $old_df)) continue;
			else {
				$persons = db_select_data('ko_leute', "where `groups` regexp 'g" . $id . "($|:r.*)'", "id");
				foreach ($persons as $person) {
					if (!$person['id']) continue;
					db_insert_data('ko_groups_datafields_data', array('group_id' => $id, 'person_id' => $person['id'], 'datafield_id' => $df));
				}
			}
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

	case "submit_delete_assignment_history_entry":
		list($table, $col, $edit_id) = explode('@', $_POST['id']);
		$where = " AND `id` = {$edit_id} AND stop < NOW() AND stop IS NOT null";
		if(db_get_count('ko_groups_assignment_history', 'id',$where) != 1) break;
		db_delete_data("ko_groups_assignment_history", "WHERE 1=1 " . $where);
	break;

	case "submit_edit_assignment_history_entry":
		list($table, $col, $edit_id) = explode('@', $_POST['id']);

		if ($table != 'ko_groups_assignment_history') break;
		$editEntry = db_select_data('ko_groups_assignment_history', "WHERE `id` = {$edit_id}", '*', '', '', TRUE);
		if (!$editEntry || $editEntry['id'] != $edit_id) break;
		$group_id = zerofill($editEntry['group_id'], 6);

		if (!$access['leute']) ko_get_access('leute');
		$accessGroups = max($access['groups']['ALL'], $access['groups'][$group_id]);
		$accessLeute = max($access['leute']['ALL'], $access['leute'][$editEntry['person_id']]);
		if($accessGroups < 2 || $accessLeute < 2) break;

		$entry = array();
		foreach($_POST['koi']['ko_groups_assignment_history'] as $col => $v) {
			if ($col == 'start' || $col == 'stop') {
				$entry[$col] = sql_datetime($v[$edit_id]);
			} else {
				$entry[$col] = $v[$edit_id];
			}
		}
		foreach (array('id', 'person_id', 'group_id', 'role_id', 'start_is_exact', 'stop_is_exact') as $f) {
			$entry[$f] = $editEntry[$f];
		}

		$error = ko_groups_assignment_history_check_entry($entry);
		if ($error) {
			switch ($error) {
				case 1:
					$notifier->addError(6, $do_action);
					break;
				case 2:
					$notifier->addError(7, $do_action);
					break;
				case 3:
					$notifier->addError(8, $do_action);
					break;
				case 4:
					$notifier->addError(9, $do_action);
					break;
				case 5:
					$notifier->addError(10, $do_action);
					break;
			}
		} else {
			kota_submit_multiedit('', 'edit_assignment_history_entry');
		}

		if(!$notifier->hasErrors()) {
			$notifier->addInfo(8, $do_action);
		}
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

			ko_taxonomy_delete_node($del_id, "ko_groups");

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

			ko_groups_assignment_history_handle_removed_roles();

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
			$old = db_select_data("ko_groups_datafields", "WHERE `id` = '$del_id'", '*', '', '', TRUE);
			db_delete_data("ko_groups_datafields", "WHERE `id` = '$del_id'");
			ko_log_diff("del_datafield", $old);
		}
	break;




	case 'submit_groups_settings':
		ko_save_userpref($_SESSION['ses_userid'], 'show_limit_groups', format_userinput($_POST['txt_limit_groups'], 'uint'));
		ko_save_userpref($_SESSION['ses_userid'], 'default_view_groups', format_userinput($_POST['sel_default_view'], 'js'));
		ko_save_userpref($_SESSION['ses_userid'], 'show_passed_groups', format_userinput($_POST['chk_show_passed_groups'], 'uint'));
		ko_save_userpref($_SESSION['ses_userid'], 'groups_people_include_start', format_userinput($_POST['chk_groups_people_include_start'], 'uint'));
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
		if($layout['data'] != '' && substr($layout['data'], 0, 4) == 'FCN:' && function_exists(substr($layout['data'], 4))) {
			$filename = call_user_func(substr($layout['data'], 4), $_GET);
		}

		if($filename) {
			$onload_code = "ko_popup('".$ko_path.'download.php?action=file&amp;file='.substr($filename, 3)."');";
		} else {
			$notifier->addError(3, $do_action);
		}
		$_SESSION['show'] = 'list_groups';
	break;


	case 'export_xls_with_people':
		$presetId = format_userinput($_GET['preset_id'], 'uint');
		if ($presetId != '') {
			$preset = db_select_data('ko_userprefs', "WHERE `id` = {$presetId}", '*', '', '', TRUE);
			if (!$preset || $preset['id'] != $presetId || !in_array($preset['user_id'], array(-1, $_SESSION['ses_userid']))) break;
		} else {
			// no presetId given, so we show default cols
			$preset['value'] = ko_get_setting("show_leute_cols");
		}

		$cols = explode(',', $preset['value']);

		if ($_GET['ids']) {
			$ids = explode(',', format_userinput($_GET['ids'], 'intlist'));
			$ids_ = array();
			foreach ($ids as $v) {
				if (!$v) continue;
				$v = zerofill($v, 6);

				//Check access
				if ($access['groups']['ALL'] < 1 && $access['groups'][$v] < 1) continue;

				$ids_[] = $v;
			}
			if(sizeof($ids_) > 0) {
				$ids = $ids_;
			} else {
				$ids = array($_SESSION['show_gid']);
			}
		} else {
			$ids = array($_SESSION['show_gid']);
		}

		$filename = ko_groups_export_xls_with_people($ids, $cols);
		if($filename) {
			$onload_code = "ko_popup('".$ko_path.'download.php?action=file&amp;file='.substr($filename, 3)."');";
		} else {
			$notifier->addError(3, $do_action);
		}
		$_SESSION['show'] = 'list_groups';
	break;


	case 'copy_group':
		if (sizeof($_POST['chk']) != 1) {
			$notifier->addError(4);
			break;
		}
		$id = key($_POST['chk']);
		if ($access['groups']['ALL'] < 3 && $access['groups'][$id] < 3) {
			$notifier->addError(5);
			break;
		}

		list($oldToNew, $hierarchy) = ko_copy_group_recursively($id, ($_POST['copy_group_type']==2 ? TRUE:FALSE));
		if (!$notifier->hasErrors()) {
			ko_groups_get_hierarchy_lines($hierarchy, $lines);
			$msg = implode("<br>", $lines);
			$notifier->addInfo(7, '', array($msg));

			reset($oldToNew);
			$edit_id = current($oldToNew);
			while (strlen($edit_id) < 6) $edit_id = '0' . $edit_id;
			$_SESSION['show'] = 'edit_group';
			$onload_code = "form_set_first_input();".$onload_code;

			// reload all groups as there are new groups
			ko_get_groups($all_groups);
		}
	break;

	//Default:
	default:
		if(!hook_action_handler($do_action))
		include($ko_path."inc/abuse.inc");
	break;


}//switch(do_action)

//HOOK: Plugins erlauben, die bestehenden Actions zu erweitern
hook_action_handler_add($do_action);


//Reread access rights if necessary
if(in_array($do_action, array('submit_new_group', 'submit_edit_group', 'delete_group'))) {
	ko_get_access('groups', '', TRUE);
	ko_get_groups($all_groups);
}



// If we are handling a request that was redirected by /inc/form.php, then exit here
if ($asyncFormSubmit == 1) {
	throw new Exception('async-form-submit-dummy-exception');
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
print ko_include_js($js_files);

include($ko_path.'inc/js-sessiontimeout.inc');
include('inc/js-groups.inc');

?>
</head>

<body onload="session_time_init();<?php print $onload_code; ?>">

<?php
/*
 * Gibt bei erfolgreichem Login das Menü aus, sonst einfach die Loginfelder
 */
include($ko_path . "menu.php");

ko_get_outer_submenu_code('groups');
?>


<!-- Hauptbereich -->
<main class="main">
<form action="index.php" method="post" name="formular" enctype="multipart/form-data">  <!-- Hauptformular -->
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
		ko_groups_list($highlight_group);
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
</div>
</form>
</main>

</div>

<?php include($ko_path . "footer.php"); ?>

</body>
</html>
