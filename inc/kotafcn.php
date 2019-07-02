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


/**
  * Liefert Select-Werte für einzelne Tabelle-Spalten
	*/
function kota_get_form($table, $column) {
	global $access;
	global $all_groups;
	global $SMALLGROUPS_ROLES, $RECTYPES, $TRACKING_MODES;

	$data = array();

	switch($table) {
		case 'ko_rota_teams':
			switch($column) {
				case 'groupid':
					$groups = ko_groups_get_recursive(ko_get_groups_zwhere(), TRUE);
					if(!is_array($all_groups)) ko_get_groups($all_groups);
					ko_get_access('groups');
					ko_get_grouproles($roles);
					$add_roles = ko_get_setting('rota_showroles') == 1;
					foreach($groups as $grp) {
						if($access['groups']['ALL'] < 1 && $access['groups'][$grp['id']] < 1) continue;
						//Add prefixes to display hierarchy
						$pre = '';
						$mother_line = ko_groups_get_motherline($grp['id'], $all_groups);
						$depth = sizeof($mother_line);
						for($i=0; $i<$depth; $i++) $pre .= '&nbsp;&nbsp;';
						$data['values'][] = 'g'.$grp['id'];
						$data['descs'][] = $pre.ko_html($grp['name']);
						//Add roles
						if($add_roles && $grp['roles'] != '') {
							foreach(explode(',', $grp['roles']) as $rid) {
								$data['values'][] = 'g'.$grp['id'].':r'.$rid;
								$data['descs'][] = $pre.ko_html($grp['name'].': '.$roles[$rid]['name']);
							}
						}
					}
				break;

				case 'eventgruppen_id':
					kota_ko_event_eventgruppen_id_dynselect($data['values'], $data['descs'], 1);
				break;
			}
		break;  //ko_rota_teams

		case "ko_event":
			switch($column) {
				case "eventgruppen_id":
					kota_ko_event_eventgruppen_id_dynselect($data["values"], $data["descs"], 2);
				break; //eventgruppen_id
			}  //switch(column)
		break;  //ko_event

		case "ko_eventgruppen":
			switch($column) {
				case "calendar_id":
					ko_get_event_calendar($cals);
					//Add empty entry
					$data["values"][] = "";
					$data["descs"][] = "";
					foreach($cals as $id => $cal) {
						if($access['daten']['cal'.$id] > 2) {
							$data["values"][] = $id;
							$data["descs"][] = $cal["name"];
						}
					}
				break;  //calendar_id
				case "moderation":
					$data["values"] = array(0, 1, 2);
					$data["descs"] = array(getLL("res_mod_no"), getLL("res_mod_yes"), getLL("res_mod_yes_mail"));
				break;  //moderation
				case "notify":
					$groups = ko_groups_get_recursive(ko_get_groups_zwhere());
					if(!is_array($all_groups)) ko_get_groups($all_groups);
					ko_get_access('groups');
					foreach($groups as $grp) {
						if($access['groups']['ALL'] < 1 && $access['groups'][$grp['id']] < 1) continue;
						//Hierarchie darstellen
						$pre = "";
						$mother_line = ko_groups_get_motherline($grp["id"], $all_groups);
						$depth = sizeof($mother_line);
						for($i=0; $i<$depth; $i++) $pre .= "&nbsp;&nbsp;";
						$data["values"][] = "g".$grp["id"];
						$data["descs"][] = $pre.ko_html($grp["name"]);
					}
				break;  //notify
				case 'rota_teams':
					//Get list of available rota teams
					$all_teams = db_select_data('ko_rota_teams', '', '*', 'ORDER BY name ASC');
					ko_get_access('rota');
					foreach($all_teams as $team) {
						if($access['rota']['ALL'] < 5 && $access['rota'][$team['id']] < 5) continue;
						$data['values'][] = $team['id'];
						$data['descs'][] = $team['name'];
					}
				break;
			}//switch(column)
		break; //ko_eventgruppen

		case 'ko_event_program':
		case 'ko_eventgruppen_program':
			switch ($column) {
				case 'teams':
					$data['values'][] = '';
					$data['descs'][] = '';
					$all_teams = ko_rota_get_all_teams('userdef', " and `rotatype` = 'event'");
					ko_get_access('rota');
					foreach($all_teams as $team) {
						if($access['rota']['ALL'] < 5 && $access['rota'][$team['id']] < 5) continue;
						$data['values'][] = $team['id'];
						$data['descs'][] = $team['name'];
					}
				break;
			}
		break; // ko_event_program, ko_eventgruppen_program

		case "ko_leute":
			switch($column) {
				case "land":
					$data["values"][] = $data["descs"][] = "";
					ko_get_all_countries($c);
					$data["values"] = $data["descs"] = $c;
				break;
				case 'smallgroups':
					if(!isset($access['kg'])) ko_get_access('kg');
					if($access['kg']['MAX'] < 2) {
						$data['params'] = 'size="7" disabled="disabled"';
						return $data;
					} else if($access['kg']['MAX'] < 3) {
						$data['params'] = 'size="7" disabled="disabled"';
					}
					$kgs = db_select_data('ko_kleingruppen', 'WHERE 1=1', '*', 'ORDER BY name ASC');
					foreach($kgs as $kg) {
						foreach($SMALLGROUPS_ROLES as $role) {
							$data['values'][] = $kg['id'].':'.$role;
							$data['descs'][] = ko_html($kg['name'].': '.getLL('kg_roles_'.$role));
						}
					}
					return $data;
				break;
				case 'rectype':
					$data['values'][] = '';
					$data['descs'][] = getLL('kota_ko_leute_rectype_default');
					foreach($RECTYPES as $k => $v) {
						$data['values'][] = $k;
						$ll_value = getLL('kota_ko_leute_'.$column.'_'.$k);
						$data['descs'][] = $ll_value ? $ll_value : $k;
					}
				break;
			}  //switch(column)
		break;  //ko_leute


		case "ko_kleingruppen":
			switch($column) {
				case "eventGroupID":
					$data["values"][] = $data["descs"][] = "";
					ko_get_eventgruppen($grps, '', "AND `type` = '0'");
					foreach($grps as $grp) {
						$data["values"][] = $grp["id"];
						$data["descs"][] = $grp["name"];
					}
				break;  //eventGroupID
			}  //switch(column)
		break;  //ko_kleingruppen


		case "ko_reservation":
			switch($column) {
				case "item_id":
					kota_ko_reservation_item_id_dynselect($data["values"], $data["descs"], 2);
				break; //item_id

			}//switch(columns)
		break;  //ko_reservation


		case "ko_resitem":
			switch($column) {
				case "gruppen_id":
					ko_get_resgroups($gruppen);
					foreach($gruppen as $i => $g) {
						if($access['reservation']['grp'.$i] < 4) continue;
						$data["values"][] = $i;
						$data["descs"][] = $g["name"];
					}
				break;  //gruppen_id
				case "moderation":
					$data["values"] = array(0, 1, 2);
					$data["descs"] = array(getLL("res_mod_no"), getLL("res_mod_yes"), getLL("res_mod_yes_mail"));
				break;  //moderation
			}//switch column
		break;  //ko_resitem

	
		case "ko_tapes":
			switch($column) {
				case "group_id":
					ko_get_tapegroups($gruppen);
					foreach($gruppen as $i => $g) {
						if($access['tapes']['ALL'] > 2 || $access['tapes'][$i] > 2) {
							$data["values"][] = $i;
							$data["descs"][] = $g["name"];
						}
					}
				break;  //group_id
				case "serie_id":
					ko_get_tapeseries($series);
					if(sizeof($series) > 0) {
						$data["values"][] = "";
						$data["descs"][] = "";
						foreach($series as $s) {
							if(!$s["name"]) continue;
							$data["values"][] = $s["id"];
							$data["descs"][] = $s["name"];
						}
					} else {
						$data["values"] = $data["descs"] = array("");
					}
				break;  //serie_id
			}//switch(columns)
		break;  //ko_tapes


		case "ko_groups":
			switch($column) {
				case "roles":
					ko_get_grouproles($roles);
					foreach($roles as $role) {
						$data["values"][] = $role["id"];
						$data["descs"][] = ko_html($role["name"]);
					}
					return $data;
				break;  //roles
			}//switch(column)
		break;  //ko_groups


		case "ko_donations":
			switch($column) {
				case "account":
					$accounts = db_select_data("ko_donations_accounts", "", "*", "ORDER BY number ASC");
					foreach($accounts as $i => $g) {
						if($access['donations']['ALL'] > 1 || $access['donations'][$i] > 1) {
							$data["values"][] = $i;
							$data["descs"][] = $g["number"]." ".$g["name"];
						}
					}
				break; //account

				case "reoccuring":
					$options = array(0, 7, 14, '1m', '2m', '3m', '4m', '6m', '12m');
					foreach($options as $g) {
						$data["values"][] = $g;
						$data["descs"][] = getLL("kota_ko_donations_reoccuring_".$g);
					}
				break; //reoccuring
			}  //switch(column)
		break;  //ko_donations


		case 'ko_tracking':
			switch($column) {
				case 'group_id':
					$groups = db_select_data('ko_tracking_groups', '', '*');
					//Add empty entry
					$data['values'][] = '';
					$data['descs'][] = '';
					foreach($groups as $id => $group) {
						$data['values'][] = $id;
						$data['descs'][] = $group['name'];
					}
				break;  //group_id

				case 'filter':
					//Filter presets
					if(ko_module_installed('leute')) {
						$data['values'][] = '';
						$data['descs'][] = '--- '.strtoupper(getLL('submenu_leute_title_filter')).' ---';
						$filterset = array_merge((array)ko_get_userpref('-1', '', 'filterset', 'ORDER BY `key` ASC'), (array)ko_get_userpref($_SESSION['ses_userid'], '', 'filterset', 'ORDER BY `key` ASC'));

						foreach($filterset as $f) {
							$this_filter = unserialize($f['value']);
							$global_tag = $f['user_id'] == '-1' ? getLL('leute_filter_global_short').' ' : '';
							$data['values'][] = 'F'.base64_encode($f['value']);
							$data['descs'][] = $global_tag.$f['key'].($this_filter['cols'] ? ' +' : '');
						}
					}
					//Get small groups
					if(ko_module_installed('kg')) {
						$data['values'][] = '';
						$data['descs'][] = '--- '.strtoupper(getLL('kg_list_title')).' ---';
						$kgs = db_select_data('ko_kleingruppen', 'WHERE 1=1', '*', 'ORDER BY name ASC');
						foreach($kgs as $kg) {
							$data['values'][] = $kg['id'];
							$desc = $kg['name'];
							if($kg['wochentag']) $desc .= ' ('.getLL('kota_ko_kleingruppen_wochentag_'.$kg['wochentag']).')';
							$data['descs'][] = ko_html($desc);
						}
					}
					//Get groups
					if(ko_module_installed('groups')) {
						$data['values'][] = '';
						$data['descs'][] = '--- '.strtoupper(getLL('groups_groups')).' ---';
						$groups = ko_groups_get_recursive(ko_get_groups_zwhere(TRUE), TRUE);
						if(!is_array($all_groups)) ko_get_groups($all_groups);
						ko_get_access('groups');
						ko_get_grouproles($roles);
						$add_roles = ko_get_setting('tracking_add_roles') == 1;
						foreach($groups as $grp) {
							if($access['groups']['ALL'] < 1 && $access['groups'][$grp['id']] < 1) continue;
							$data['values'][] = 'g'.$grp['id'];
							$grp_name = ko_groups_decode(ko_groups_decode('g'.$grp['id'], 'full_gid'), 'group_desc_full');
							$data['descs'][] = ko_html($grp_name);
							//Add roles
							if($add_roles && $grp['roles'] != '') {
								foreach(explode(',', $grp['roles']) as $rid) {
									$data['values'][] = 'g'.$grp['id'].':r'.$rid;
									$data['descs'][] = ko_html($grp_name.': '.$roles[$rid]['name']);
								}
							}
						}
					}
				break;

				case 'date_eventgroup':
					$data['values'][] = $data['descs'][] = '';
					ko_get_eventgruppen($grps, '', "AND `type` = '0'");
					foreach($grps as $grp) {
						$data["values"][] = $grp["id"];
						$data["descs"][] = $grp["name"];
					}
					foreach($grps as $grp) {
						$data['values'][] = '-'.$grp['id'];
						$data['descs'][] = '- '.$grp['name'];
					}
				break;

				case 'date_weekdays':
					$data['values'] = array(1,2,3,4,5,6,0);
					$monday = date_find_last_monday(date('Y-m-d'));
					for($i=0; $i<7; $i++) {
						$data['descs'][] = strftime('%A', strtotime(add2date($monday, 'tag', $i, TRUE)));
					}
				break;

				case 'mode':
					foreach($TRACKING_MODES as $m) {
						$data['values'][] = $m;
						$data['descs'][] = getLL('kota_ko_tracking_mode_'.$m);
					}
				break;
			}
		break;  //ko_tracking

		case 'ko_reminder':
			switch($column) {
				case 'deadline':
					$deadlines = kota_reminder_get_deadlines();
					foreach ($deadlines as $key => $deadline) {
						$data['values'][] = $key;
						$data['descs'][] = $deadline;
					}
					break;
				case 'action':
					$data['values'][] = 'email';
					$data['descs'][] = getLL('kota_ko_reminder_action_email');
					break;
				case 'recipients_groups':
					if(ko_module_installed('groups')) {
						$groups = ko_groups_get_recursive(ko_get_groups_zwhere(TRUE), TRUE);
						if(!is_array($all_groups)) ko_get_groups($all_groups);
						ko_get_access('groups');
						ko_get_grouproles($roles);
						$add_roles = ko_get_setting('tracking_add_roles') == 1;
						foreach($groups as $grp) {
							if($access['groups']['ALL'] < 1 && $access['groups'][$grp['id']] < 1) continue;
							$data['values'][] = 'g'.$grp['id'];
							$grp_name = ko_groups_decode(ko_groups_decode('g'.$grp['id'], 'full_gid'), 'group_desc_full');
							$data['descs'][] = ko_html($grp_name);
							//Add roles
							if($add_roles && $grp['roles'] != '') {
								foreach(explode(',', $grp['roles']) as $rid) {
									$data['values'][] = 'g'.$grp['id'].':r'.$rid;
									$data['descs'][] = ko_html($grp_name.': '.$roles[$rid]['name']);
								}
							}
						}
					}
					break;
				case 'filter_event':
					/*$data['values'][] = '';
					$data['descs'][] = '--- ' . strtoupper(getLL('module_daten')) . ' ---';
					$events = null;
					$deadlines = kota_reminder_get_deadlines();
					$maxDeadline = 0;
					foreach ($deadlines as $key => $deadline) {
						if ($key > $maxDeadline) $maxDeadline = $key;
					}
					ko_get_events($events, " AND TIMESTAMPDIFF(HOUR,CONCAT(CONCAT(`ko_event`.`enddatum`, ' '), `ko_event`.`endzeit`), NOW()) <= " . $maxDeadline, '', 'ko_event', ' order by startdatum asc');
					foreach ($events as $event) {
						$data['values'][] = 'EVID' . $event['id'];
						$data['descs'][] = sql2datum($event['startdatum']) . ": " . $event['title'];
					}*/

					$data['values'][] = '';
					$data['descs'][] = '--- ' . strtoupper(getLL('kota_ko_eventgruppen_calendar_id')) . ' ---';
					$calendars = null;
					ko_get_event_calendar($calendars);
					foreach ($calendars as $calendar) {
						if ($access['daten']['cal' . $calendar['id']] == 0) continue;
						$data['values'][] = 'CALE' . $calendar['id'];
						$data['descs'][] = strtoupper($calendar['name']);
					}

					$data['values'][] = '';
					$data['descs'][] = '--- ' . strtoupper(getLL('daten_eventgroup')) . ' ---';
					$eventGroups = null;
					ko_get_eventgruppen($eventGroups);
					foreach ($eventGroups as $eventGroup) {
						if ($access['daten'][$eventGroup['id']] == 0) continue;
						$data['values'][] = 'EVGR' . $eventGroup['id'];
						$data['descs'][] = $eventGroup['name'];
					}

					$data['values'][] = '';
					$data['descs'][] = '--- '.strtoupper(getLL('leute_labels_preset')).' ---';
					$userPresets = ko_get_userpref($_SESSION['ses_userid'], '', 'daten_itemset');
					$globalPresets = ko_get_userpref(-1, '', 'daten_itemset');
					foreach ($userPresets as $userPreset) {
						$data['values'][] = 'EGPR' . $userPreset['key'];
						$data['descs'][] = $userPreset['key'];
					}
					foreach ($globalPresets as $globalPreset) {
						$data['values'][] = 'EGPR[G] ' . $globalPreset['key'];
						$data['descs'][] = $globalPreset['key'];
					}

					break;
			}
			break;

	}  //switch(table)

	return $data;
}//kota_get_form()





/**
  * Speichert die Änderungen von einem Multiedit-Formular
	*/
function kota_submit_multiedit($rights_level='', $log_type="", $lastchange_col="", &$changes) {
	global $KOTA, $mysql_pass;
	global $access;

	$notifier = koNotifier::Instance();

	list($table, $columns, $ids, $hash) = explode("@", $_POST["id"]);
	if(!$table || !$columns || $ids == "" || strlen($hash) != 32) $notifier->addError(58);
	$query = $log_message = array();

	//new entry
	$new_entry = FALSE;
	if($ids == 0) {
		$new_entry = TRUE;

		$new_data = array('id' => 'NULL');
		//Add creation date if set in KOTA
		if($KOTA[$table]['_special_cols']['crdate']) $new_data[$KOTA[$table]['_special_cols']['crdate']] = date('Y-m-d H:i:s');
		//Add creation user if set in KOTA
		if($KOTA[$table]['_special_cols']['cruser']) $new_data[$KOTA[$table]['_special_cols']['cruser']] = $_SESSION['ses_userid'];

		$new_id = $ids = db_insert_data($table, $new_data);
		$rights = '';  //Access rights to add new entries have to be checked in index.php before calling kota_submit_multiedit()
	}
	else {
		//Prepare to check for access rights to edit the selected items (check for new entries above has to be done in index.php)
		$rights = array();
		$module = $KOTA[$table]['_access']['module'];
		$chk_col = $KOTA[$table]['_access']['chk_col'];
		$rights_level = $rights_level !== '' ? $rights_level : $KOTA[$table]['_access']['level'];
		$entries = db_select_data($table, "WHERE `id` IN ('".implode("','", explode(',', $ids))."')");
		foreach($entries as $entry) {
			if(substr($chk_col, 0, 4) == 'ALL&') {
				$rights[$entry['id']] = ($access[$module]['ALL'] >= $rights_level || $access[$module][$entry[substr($chk_col, 4)]] >= $rights_level);
			} else if($chk_col != '') {
				$rights[$entry['id']] = ($access[$module][$entry[$chk_col]] >= $rights_level);
			} else {
				$rights[$entry['id']] = ($access[$module]['ALL'] >= $rights_level);
			}
		}
	}

	//Switch form according to type
	if(isset($KOTA[$table]['_types'])) {
		//New: Check for passed type in form_data
		if($new_entry) {
			$kota_type = $_POST['kota_type'];
		}
		//Edit form: Check for type of db record
		else {
			$entry = db_select_data($table, "WHERE `id` = '$ids'", '*', '', '', TRUE);
			$kota_type = $entry[$KOTA[$table]['_types']['field']];
		}

		//Reorganize form according to type
		if($kota_type != $KOTA[$table]['_types']['default']) {
			//Unset fields not needed for this type
			foreach($KOTA[$table] as $kota_col => $array) {
				if(substr($kota_col, 0, 1) == "_") continue;
				if(!isset($array['form']) || $array['form']['ignore']) continue;

				if(!in_array($kota_col, $KOTA[$table]['_types']['types'][$kota_type]['use_fields'])) {
					unset($KOTA[$table][$kota_col]);
				}
			}
			//Add fields
			foreach($KOTA[$table]['_types']['types'][$kota_type]['add_fields'] as $kota_col => $array) {
				$KOTA[$table][$kota_col] = $array;
			}
		}
	}//if(KOTA[table][_types])


	//Prepare query addition to store last change (if given)
	if($lastchange_col) $lastchange_query = ", `$lastchange_col` = NOW() ";

	//Übergebene Werte
	$koi = $_POST["koi"][$table];

	//See, whether forAll has been checked
	if($koi["doForAll"]) $doForAll = TRUE;
	else $doForAll = FALSE;

	//add checkboxes. If they are deselected, then they are not in koi
	foreach(explode(",", $ids) as $id) {
		foreach(explode(",", $columns) as $kota_col_name) {
			$kota_col = $KOTA[$table][$kota_col_name];
			if($kota_col["form"]["type"] == "checkbox" && !isset($koi[$kota_col_name][($new_entry?0:$id)])) {
				$koi[$kota_col_name][($new_entry?0:$id)] = '0';
			}
		}//foreach(columns as kota_col_name)
	}//foreach(ids as id)

	//Alle POST-Werte durchgehen
	foreach($koi as $col => $values) {
		if($col == "forAll") continue;

		$col = format_userinput($col, "js");
		//Spalten mit _PLUS hinten gehören zu einem Textplus-Feld, werden deshalb schon bearbeitet (in kota_process_data())
		if(substr($col, -5) == "_PLUS") {
			//Add id for textplus fields, if they are not present. Can happen, if no values had been entered so far.
			if(!in_array($col, $test["columns"])) $test["columns"][] = substr($col, 0, -5);
			continue;
		}
		//only allow values set in KOTA
		if(!isset($KOTA[$table][$col])) continue;
		$test["columns"][] = $col;

		foreach($values as $id => $value) {
			//If "dontsave" is set, then don't store the value here but add column to test, so check will work. Also call 'post' if any
			if($KOTA[$table][$col]['form']['dontsave']) {
				//Call post
				if($KOTA[$table][$col]['post']) {
					if(substr($KOTA[$table][$col]['post'], 0, 4) == 'FCN:') {
						$fcn = substr($KOTA[$table][$col]['post'], 4);
						if(function_exists($fcn)) {
							eval("$fcn(\$table, \$col, (\$new_entry?\$new_id:\$id), \$value);");
						}
					}
				}
				continue;
			}
			//Save for all
			if($doForAll) {
				$value = $koi[$col]["forAll"];
				if($koi[$col."_PLUS"]["forAll"]) $koi[$col."_PLUS"][$id] = $koi[$col."_PLUS"]["forAll"];
			}
			//ID und Berechtigung
			$id = format_userinput($id, "uint");
			if($rights != "" && !$rights[$id]) continue;

			//Wert formatieren
			$log = "";
			$process_data = array($col => $value);
			if($koi[$col."_PLUS"][$id]) $process_data[$col."_PLUS"] = $koi[$col."_PLUS"][$id];
			kota_process_data($table, $process_data, "post", $log, ($new_entry?$new_id:$id));
			$value = $process_data[$col];

			//Log-Message vorbereiten:
			if(!$log && substr($col, 0, 6) != "MODULE") {
				$old_v = db_select_data($table, "WHERE `id` = '$id'", '`'.$col.'`', '', '', TRUE);
				$old_value = $old_v[$col];
				if($old_value != $value) {
					$log_message[$id] .= "$col: ".$old_value." --> $value, ";
				}
			} else {
				$log_message[$id] .= $log;
			}

			//Query aufbauen, aber noch nicht ausführen
			if(substr($col, 0, 9) == 'MODULEgrp' && FALSE === strpos($col, ':')) $db_col = 'groups';
			else $db_col = $col;
			if($db_col && substr($db_col,-7) != '_DELETE') { // ignore fields which contain information about deletion of FILES
				$query[] = "UPDATE $table SET `$db_col` = '$value' $lastchange_query WHERE `id` = '".($new_entry?$new_id:$id)."'";
				$changes[$table][($new_entry?$new_id:$id)][$db_col] = $value;
			}
			if($id != "") $test["ids"][] = $id;
		}
	}//foreach(koi as col => values)
	$test["ids"] = array_unique($test["ids"]);

	//IDs prüfen, die nun nicht mehr vorkommen.
	//Es könnte sich um Checkboxen handeln, die deaktiviert wurden, denn dann wird der Wert von HTML gar nicht gesetzt
	$chk_ids = array_diff(explode(",", $ids), (array)$test["ids"]);
	foreach($chk_ids as $id) {
		if(!$id || $new_entry) continue;  //Don't check for new entries
		foreach(explode(",", $columns) as $col) {
			if($KOTA[$table][$col]["form"]["dontsave"]) continue;
			if($KOTA[$table][$col]["form"]["type"] == "checkbox") {
				$query[] = "UPDATE $table SET `$col` = '0' $lastchange_query WHERE `id` = '$id'";
				$test["ids"][] = $id;
				$test["columns"][] = $col;
			}
			//Also add ids for Group datafields for people, that don't have this group. There will be no input field, so no koi entry.
			else if(substr($col, 0, 9) == 'MODULEgrp' && FALSE !== strpos($col, ':') && !$koi[$col][$id]) {
				$test["ids"][] = $id;
				$test["columns"][] = $col;
				ko_get_person_by_id($id, $gdf_person);  //Get person's data to only process people who are assigned to the given group
				if(FALSE !== strpos($gdf_person["groups"], "g".substr($col, 9, 6))) {
					//Set checkbox as given by forAll or if forAll is not being used then set it to 0
					if($doForAll) $process_data = array($col => $koi[$col]["forAll"]);
					else $process_data = array($col => 0);
					kota_process_data($table, $process_data, "post", $log, $id);
				}
			}
		}
	}

	//Auf file-uploads testen
	foreach(explode(",", $columns) as $col) {
		if($KOTA[$table][$col]["form"]["type"] == "file") {
			if($new_entry) $ids = 0;
			foreach(explode(",", $ids) as $id) {
				//only save newly submitted files
				if($_FILES["koi"]["tmp_name"][$table][$col][$id]) {
					$data = array("table" => $table, "col" => $col, "id" => $id);
					kota_save_file($value, $data, $new_id);
					$query[] = "UPDATE $table SET `$col` = '".$value."' $lastchange_query WHERE `id` = '".($new_entry?$new_id:$id)."'";
				}
				//check for delete-checkbox for this file (only possible for edit)
				else if(!$new_entry && $koi[$col."_DELETE"][$id] == 1) {
					$col_value = db_select_data($table, "WHERE `id` = '$id'", '`'.$col.'`', "", "", TRUE);
					$data = array("table" => $table, "col" => $col, "id" => $id);
					kota_delete_file($col_value[$col], $data);
					$query[] = "UPDATE $table SET `$col` = '' $lastchange_query WHERE `id` = '$id'";
				}
				$test["ids"][] = $id;
			}//foreach(test[ids] as id)
			$test["columns"][] = $col;
		}
	}//foreach(columns as col)
	$test["ids"] = array_unique($test["ids"]);
	$test["columns"] = array_unique($test["columns"]);

	//Übereinstimmung mit Hash-Wert checken
	sort($test["columns"]);
	sort($test["ids"]);
	if($new_entry) $test["ids"] = array(0);
	$test_string = $mysql_pass.$table.implode(":", $test["columns"]).implode(":", $test["ids"]);
	if(md5(md5($test_string)) != $hash) $notifier->addError(59);

	//print "new: '$test_string'<br />";
	//print "post: '".$_POST["id"]."'<br />";

	//Get old entry before storing new values (for post function)
	if(sizeof($test['ids']) > 0) $old = db_select_data($table, "WHERE `id` IN ('".implode("','", $test['ids'])."')");

	$do_save = TRUE;

	//Table's post function
	if(function_exists('kota_presave_'.$table)) {
		eval("\$ret = kota_presave_$table(\$ids, \$columns, \$old, \$changes);");
		if($ret > 0) {
			$do_save = FALSE;
			$notifier->addError($ret);
		}
	}

	//DB aktualisieren
	if($do_save && !$notifier->hasNotification(58) && !$notifier->hasNotification(59)) {
		//Log-Meldung
		$log_type = $log_type ? $log_type : "multiedit@$table";
		foreach($log_message as $id => $log) {
			if(!$log) continue;
			//Create title for current record with _multititle definition from KOTA
			$log_title = array();
			foreach($KOTA[$table]['_multititle'] as $k => $v) {
				if($old[$id][$k]) $log_title[] = $old[$id][$k];
			}
			$log = "$table ($id)".(sizeof($log_title) > 0 ? ' '.implode(' ', $log_title) : '').": $log";
			ko_log($log_type, $log);
			//Store version for ko_leute
			if($table == "ko_leute") ko_save_leute_changes($id);
		}//foreach(log_message as log)

		//Perform querys
		foreach($query as $q) mysql_query($q);
	}//if(!error)

	//Table's post function
	if(function_exists('kota_post_'.$table)) {
		eval("kota_post_$table(\$ids, \$columns, \$old, \$do_save);");
	}
	//Call plugins
	hook_kota_post($table, array('table' => $table, 'ids' => $ids, 'columns' => $columns, 'old' => $old, 'do_save' => $do_save));

	if($new_entry) return $new_id;
}//kota_submit_multiedit()




/**
 * This function is called before storing the submitted values for the given table
 * It checks for overlapping reservations resulting from saving the new values. If so an error is returned which
 * prevents these changes from being saved.
 */
function kota_presave_ko_reservation($ids, $columns, $old, $changes) {
	global $BASE_PATH;

	require_once($BASE_PATH.'reservation/inc/reservation.inc');

	if(!is_array($ids)) $ids = explode(',', $ids);

	$all_ok = TRUE;
	foreach($ids as $id) {
		$datum1 = isset($changes['ko_reservation'][$id]['startdatum']) ? $changes['ko_reservation'][$id]['startdatum'] : $old[$id]['startdatum'];
		$datum2 = isset($changes['ko_reservation'][$id]['enddatum']) ? $changes['ko_reservation'][$id]['enddatum'] : $old[$id]['enddatum'];
		$zeit1 = isset($changes['ko_reservation'][$id]['startzeit']) ? $changes['ko_reservation'][$id]['startzeit'] : $old[$id]['startzeit'];
		$zeit2 = isset($changes['ko_reservation'][$id]['endzeit']) ? $changes['ko_reservation'][$id]['endzeit'] : $old[$id]['endzeit'];
		$item_id = isset($changes['ko_reservation'][$id]['item_id']) ? $changes['ko_reservation'][$id]['item_id'] : $old[$id]['item_id'];
		$ok = ko_res_check_double($item_id, $datum1, $datum2, $zeit1, $zeit2, $error_txt, $id);
		if(!$ok) $all_ok = FALSE;
	}
	if(!$all_ok) return 4;
	else return 0;
}//kota_presave_ko_reservation()




/**
 * This function is called before storing the submitted values for the given table
 * It updates the purpose of reservations attached to events if any title field has changed
 */
function kota_presave_ko_event($ids, $columns, $old, $changes) {
	global $BASE_PATH;
	
	$error = 0;

	//Only update reservations if event group or title has changed
	if(!in_array($columns, array('eventgruppen_id', 'title'))) return;

	if(!is_array($ids)) $ids = array($ids);
	foreach($ids as $id) {
		//Don't handle event with no reservations
		if($old[$id]['reservationen'] == '') continue;

		$title = isset($changes['ko_event'][$id]['title']) ? $changes['ko_event'][$id]['title'] : $old[$id]['title'];
		$eventgruppen_id = isset($changes['ko_event'][$id]['eventgruppen_id']) ? $changes['ko_event'][$id]['eventgruppen_id'] : $old[$id]['eventgruppen_id'];
		$purpose = $title.' ('.ko_get_eventgruppen_name($eventgruppen_id).')';
		db_update_data('ko_reservation', "WHERE `id` IN (".$old[$id]['reservationen'].")", array('zweck' => $purpose));
	}

	return $error;
}//kota_presave_ko_reservation()




/**
 * Post processing function for table ko_leute
 * Called from kota_submit_multiedit after submitting entry for this table
 * @param ids string Comma separated list of edited ids (one for normal or inline editing)
 * @param columns string Comma separated list of edited columns.
 */
function kota_post_ko_leute($ids, $columns, $old, $do_save) {
	global $LDAP_ATTRIB, $LEUTE_EMAIL_FIELDS, $all_groups, $COLS_LEUTE_UND_FAMILIE, $redrawElements;

	if (!isset($redrawElements)) $redrawElements = array();

	$ids = explode(',', $ids);
	$columns = explode(',', $columns);

	$groupModification = false;
	foreach ($columns as $column) {
		if (substr($column, 0, 9) == 'MODULEgrp') {
			$groupModification = TRUE;
		}
	}
	$groupModification = $groupModification || in_array('groups', $columns);
	if ($groupModification) {
		// Add entries for linked groups
		$myOld = $old;
		if (isset($myOld['id'])) {
			$myOld = array($myOld);
		}
		$new = array();
		foreach($myOld as $op) {
			ko_get_person_by_id($op['id'], $np);
			$new[] = $np;

			// explode groups and get rid of empty strings
			$og = explode(',', $op['groups']);
			foreach ($og as $k => $oo) {
				if (trim($oo) == '') unset($og[$k]);
			}
			$ng = explode(',', $np['groups']);
			foreach ($ng as $k => $nn) {
				if (trim($nn) == '') unset($ng[$k]);
			}
			$linkedGroupsAdded = array();
			foreach ($ng as $newGroup) {
				if (!in_array($newGroup, $og) && trim($newGroup) != '') {
					$linkedGroups = ko_groups_get_linked_groups($newGroup);
					foreach ($linkedGroups as $linkedGroupId => $linkedGroupEntry) {
						$redrawElements['ko_leute|' . $op['id'] . '|MODULEgrp' . $linkedGroupId] = $linkedGroupId;
						$linkedGroupsAdded[] = $linkedGroupEntry;
					}
				}
			}
			$ng = array_unique(array_merge($linkedGroupsAdded,$ng));

			$ng = ko_groups_remove_spare_norole($ng);


			db_update_data('ko_leute', 'where id = ' . $op['id'], array('groups' => implode(',', $ng)));
			$p = null;
			ko_get_person_by_id($op['id'], $p);

			foreach ($linkedGroupsAdded as $linkedGroupAdded) {
				$g = format_userinput(ko_groups_decode($linkedGroupAdded, 'group_id'), 'uint');
				if ($g == '' || $g == 0) continue;
				$group = db_select_data('ko_groups', "where id = " . $g, 'maxcount, count_role', '', '', TRUE, TRUE);
				if ($group === null) continue;
				if($group['maxcount'] > 0) {
					ko_update_group_count($linkedGroupAdded, $group['count_role']);
				}
			}
			foreach ($redrawElements as $k => $redrawElement) {
				$redrawElements[$k] = array('column' => 'MODULEgrp'.$linkedGroupId, 'value' => map_leute_daten(implode(',', $ng), 'MODULEgrp'.$linkedGroupId, $p));
			}
		}
	}


	if(!$do_save) return;

	//Update entry in LDAP
	if(ko_do_ldap()) {
		//Find columns exported to LDAP
		$ldap_cols = array_keys($LDAP_ATTRIB);

		$export_to_ldap = FALSE;
		foreach($columns as $col) {
			if(in_array($col, $ldap_cols)) $export_to_ldap = TRUE;
		}

		if($export_to_ldap) {
			$ldap = ko_ldap_connect();
			foreach($ids as $id) {
				ko_get_person_by_id($id, $person);
				ko_ldap_add_person($ldap, $person, $id, TRUE);
			}
			ko_ldap_close($ldap);
		}
	}//if(ko_do_ldap())


	//Update family if family field has been edited
	foreach($ids as $id) {
		if(!$old[$id]['famid']) continue;
		ko_get_person_by_id($id, $person);

		$famdata = array();
		$updateFam = $updateOnlyChildren = FALSE;
		foreach($columns as $col) {
			if(in_array($col, $COLS_LEUTE_UND_FAMILIE)) {
				$famdata[$col] = $person[$col];
				$updateFam = TRUE;
			}
			else if($col == 'famfunction') $updateOnlyChildren = TRUE;
		}
		if($updateFam) ko_update_familie($old[$id]['famid'], $famdata, $id);

		//Update number of children if famfunction has been changed
		//(For other family fields ko_update_familie() will update number of children)
		if($updateOnlyChildren) {
			$num_kids = ko_get_personen_by_familie($old[$id]['famid'], $children, 'child');
			ko_get_personen_by_familie($old[$id]['famid'], $famMembers);
			foreach($famMembers as $m) {
				$data = array();
				if(in_array($m['famfunction'], array('husband', 'wife'))) {
					$data['kinder'] = $num_kids;
				} else {
					$data['kinder'] = 0;
				}
				db_update_data('ko_leute', "WHERE `id` = '".$m['id']."'", $data);
			}
		}
	}


	//EZMLM
	if(defined('EXPORT2EZMLM') && EXPORT2EZMLM) {
		$export_ezmlm = FALSE;
		//Check for email columns (group columns are handled in ko_groups_get_savestring())
		foreach($columns as $col) {
			if(in_array($col, $LEUTE_EMAIL_FIELDS)) $export_ezmlm = TRUE;
		}
		if($export_ezmlm) {
			if(!is_array($all_groups)) ko_get_groups($all_groups);
			foreach($old as $op) {
				ko_get_person_by_id($op['id'], $np);
				if($op['email'] != $np['email'] && check_email($np['email'])) {
					foreach(explode(',', $np['groups']) as $group) {  //Check this user's groups for one with an ML assigned
						$gid = ko_groups_decode($group, 'group_id');
						if($all_groups[$gid]['ezmlm_list']) {
							//Un- and resubscribe
							ko_ezmlm_unsubscribe($all_groups[$gid]['ezmlm_list'], $all_groups[$gid]['ezmlm_moderator'], $op['email']);
							ko_ezmlm_subscribe($all_groups[$gid]['ezmlm_list'], $all_groups[$gid]['ezmlm_moderator'], $np['email']);
						}
					}
				}
			}
		}
	}//if(EXPORT2EZMLM)


	//Update count for groups this person is/was assigned to
	if(!is_array($all_groups)) ko_get_groups($all_groups);
	foreach($old as $op) {
		ko_get_person_by_id($op['id'], $np);
		$ag = array_unique(array_merge(explode(',', $op['groups']), explode(',', $np['groups'])));
		foreach($ag as $g) {
			$g = ko_groups_decode($g, 'group_id');
			if($all_groups[$g]['maxcount'] > 0) {
				ko_update_group_count($g, $all_groups[$g]['count_role']);
			}
		}
	}


	//TODO: Set default tracking values if assigned to a new group with a tracking attached to it

}//kota_post_ko_leute()




/**
 * Post function for groups
 * Checks for changed roles assigned to edited groups. If roles have changed, the addresses assigned to a removed role in this group
 * will only be left assigned to the group itself.
 * ko_update_groups_and_roles() updates motherline and other things as well.
 */
function kota_post_ko_groups($ids, $columns, $old, $do_save) {
	global $all_groups;

	if(!$do_save) return;

	//Reread all_groups from DB
	ko_get_groups($all_groups);

	$new = db_select_data('ko_groups', "WHERE `id` IN ($ids)");

	//Check for removed roles within a group
	foreach(explode(',', $ids) as $id) {
		if(!$id) continue;
		if(!$old[$id]['roles'] && !$new[$id]['roles']) continue;

		$old_roles = explode(',', $old[$id]['roles']);
		sort($old_roles);
		$old_roles = implode(',', $old_roles);
		$new_roles = explode(',', $new[$id]['roles']);
		sort($new_roles);
		$new_roles = implode(',', $new_roles);

		//Update group/role assignments of addresses if roles of the group have changed
		if($old_roles != $new_roles) {
			ko_update_groups_and_roles($id);
		}
	}
}//kota_post_ko_groups()





/**
 * POST function ko_tracking (normal, multi- and inline edit)
 */
function kota_post_ko_tracking($ids, $columns, $old, $do_save) {
	//Delete empty tracking groups
	$tgroups = db_select_data('ko_tracking_groups', 'WHERE 1');
	foreach($tgroups as $g) {
		if(db_get_count('ko_tracking', 'id', "AND `group_id` = '".$g['id']."'") == 0) {
			db_delete_data('ko_tracking_groups', "WHERE `id` = '".$g['id']."'");
		}
	}
}//kota_post_ko_tracking()




function kota_post_ko_scheduler_tasks($ids, $columns, $old, $do_save) {
	//Delete empty tracking groups
	db_update_data('ko_scheduler_tasks', "WHERE `status` = '0'", array('next_call' => '0000-00-00 00:00:00'));
	if(!is_array($ids)) $ids = explode(',', $ids);
	foreach($ids as $id) {
		if(!$id) continue;
		ko_scheduler_set_next_call($id);
	}
}//kota_post_ko_scheduler_tasks()




/**
  * Funktionen zur Nach-Behandlung (post) eines File-Uploads
	*/
function kota_save_file(&$value, $data, $new_id=0) {
	global $BASE_PATH;

	$dissallow_ext = array('php', 'php3', 'inc');

	$tmp = $_FILES["koi"]["tmp_name"][$data["table"]][$data["col"]][$data["id"]];
	if(!$tmp) return FALSE;
	$upload_name = $_FILES["koi"]["name"][$data["table"]][$data["col"]][$data["id"]];
	$ext_ = explode(".", $upload_name);
	$ext = $ext_[sizeof($ext_)-1];
	if(in_array($ext, $dissallow_ext)) return FALSE;

	$path = $BASE_PATH."my_images/";
	$filename = "kota_".$data["table"]."_".$data["col"]."_".($new_id?$new_id:$data["id"]).".".$ext;
	$dest = $path.$filename;

	$ret = move_uploaded_file($tmp, $dest);
	if($ret) {
		$value = "my_images/".$filename;
		chmod($dest, 0644);
	} else {
		$value = "";
	}

	//Unset _FILES values for this upload (so further processing, e.g. by kota_process_data() don't treat this again)
	unset($_FILES['koi']['name'][$data['table']][$data['col']][$data['id']]);
	unset($_FILES['koi']['type'][$data['table']][$data['col']][$data['id']]);
	unset($_FILES['koi']['tmp_name'][$data['table']][$data['col']][$data['id']]);
	unset($_FILES['koi']['error'][$data['table']][$data['col']][$data['id']]);
	unset($_FILES['koi']['size'][$data['table']][$data['col']][$data['id']]);
}//kota_save_file()


function kota_delete_file($value, $data) {
	global $BASE_PATH;

	$ext_ = explode(".", $value);
	$ext = $ext_[sizeof($ext_)-1];

	$path = $BASE_PATH."my_images/";
	$filename = "kota_".$data["table"]."_".$data["col"]."_".$data["id"].".".$ext;
	if(file_exists($path.$filename)) unlink($path.$filename);
}//kota_delete_file()




/**
  * Funktionen zur Nach-Behandlung (post) einzelner Formularfelder für Multiedit
	*/
function ko_multiedit_familie(&$value, $data) {
	ko_get_person_by_id($data["id"], $p);
	if(!$p["famid"]) return FALSE;
	$fam_data[$data["col"]] = $value;
	ko_update_familie($p["famid"], $fam_data);
	return TRUE;
}//ko_multiedit_familie()



/**
 * Create new serie if new value has been entered
 */
function ko_multiedit_tapeserie(&$value, $data) {
	if((int)$value && format_userinput($value, "uint") == (int)$value) {
		//Do nothing, serie has been selected by id
	} else if(trim($value) != "") {
		$new_serie = format_userinput($value, "text");
		$value = db_insert_data("ko_tapes_series", array("name" => $new_serie, "printname" => $new_serie));
	} else {
		$value = "";
	}
}//ko_multiedit_tapeserie()



function ko_multiedit_tracking_group(&$value, $data) {
	if((int)$value && format_userinput($value, 'uint') == (int)$value) {
		//Do nothing, serie has been selected by id
	} else if(trim($value) != '') {
		$new_serie = format_userinput($value, 'text');
		$value = db_insert_data('ko_tracking_groups', array('name' => $new_serie));
	} else {
		$value = '';
	}
}//ko_multiedit_tracking_group()



function kota_mailing_check_unique_alias(&$value, $data) {

	if($value == '') return FALSE;
	//Enforce lowercase aliases
	$value = strtolower($value);

	//Check for disallowed aliases
	if(  1 == preg_match('/^sg([0-9]{4})([a-zA-Z.]*)$/', $value, $m)  //small group
		|| 1 == preg_match('/^gr([0-9.]*$)/', $value, $m)               //group
		|| $value == 'ml'                                               //my list
		|| substr($value, 0, strlen('confirm-')) == 'confirm-'          //confirm emails start with confirm-
		|| substr($value, 0, strlen('sms.')) == 'sms.'                  //Send sms instead of email
		|| substr($value, 0, strlen('filter.')) == 'filter.'            //Filter preset from people module
		|| FALSE !== strpos($value, '+')                                //No plus sign allowed, used for automatically authorized emails
		) {
		$value = '';
		koNotifier::Instance()->addError(66);
		return FALSE;
	}

	//Get all current aliases for groups and small groups
	$aliases = array();

	$where_add = $data['table'] == 'ko_groups' ? " AND `id` != '".$data['id']."'" : '';
	$gr_aliases = db_select_data('ko_groups', "WHERE `mailing_alias` != ''".$where_add);
	foreach($gr_aliases as $g) $aliases[] = $g['mailing_alias'];

	$where_add = $data['table'] == 'ko_kleingruppen' ? " AND `id` != '".$data['id']."'" : '';
	$sg_aliases = db_select_data('ko_kleingruppen', "WHERE `mailing_alias` != ''".$where_add);
	foreach($sg_aliases as $g) $aliases[] = $g['mailing_alias'];

	$where_add = $data['table'] == 'ko_userprefs' ? " AND `id` != '".$data['id']."'" : '';
	$up_aliases = db_select_data('ko_userprefs', "WHERE `mailing_alias` != ''".$where_add);
	foreach($up_aliases as $g) $aliases[] = $g['mailing_alias'];


	//Unset alias if already present
	if(in_array($value, $aliases)) {
		$value = '';
		koNotifier::Instance()->addError(67);
		return FALSE;
	}
	return TRUE;
}//kota_mailing_check_unique_alias()


function kota_mailing_link_alias(&$value, $data) {
	global $MAILING_PARAMETER;

	if(!ko_module_installed('mailing', $_SESSION['ses_userid'])) return;
	if($MAILING_PARAMETER['domain'] == '') return;

	$value = '<a href="mailto:'.$value.'@'.$MAILING_PARAMETER['domain'].'">'.$value.'</a>';
}//kota_mailing_link_alias()



function kota_pic_tooltip(&$value, $data) {
	if(substr($value, 0, strlen('my_images')) == 'my_images') {
		$value = ko_pic_get_tooltip($value, 25, 200, 'm', 'l');
	}
}//kota_pic_tooltip()



function kota_sort_comma_list(&$value, $data) {
	if(!$value) return FALSE;
	$a = explode(',', $value);
	asort($a);
	$value = implode(',', $a);
}//kota_sort_comma_list()



/**
 * KOTA POST function for ko_kleingruppen.members_ROLE
 *
 * Handles submission of peoplesearch for small groups.
 * For every small group role one people select is shown
 * and this function stores the selection in ko_leute.smallgroups
 */
function kota_smallgroup_members_post($table, $col, $kgid, $value) {
	$kgid = zerofill($kgid, 4);
	$new_ids = explode(',', $value);
	$role = substr($col, -1);
	//Get all current small group members for this role
	$current = db_select_data('ko_leute', "WHERE `smallgroups` LIKE '%$kgid:$role%'", '*');
	foreach($new_ids as $pid) {
		if(!$pid) continue;
		if(!in_array($pid, array_keys($current))) {
			$old = $p = db_select_data('ko_leute', "WHERE `id` = '$pid'", '*', '', '', TRUE);
			$p['smallgroups'] .= $p['smallgroups'] != '' ? ','.$kgid.':'.$role : $kgid.':'.$role;
			db_update_data('ko_leute', "WHERE `id` = '$pid'", array('smallgroups' => $p['smallgroups']));

			//Store leute_changes and add log message
			ko_save_leute_changes($pid, $old);
			$log_message  = $pid.' ('.$p['vorname'].' '.$p['nachname'].'): '.getLL('kota_ko_leute_smallgroups').': ';
			$log_message .= ko_kgliste($old['smallgroups']).' --> '.ko_kgliste($p['smallgroups']);
			ko_log('edit_person', $log_message);
		} else {
			unset($current[$pid]);
		}
	}
	//Delete all remaining entries, if their not selected anymore
	foreach($current as $pid => $p) {
		if(!$pid) continue;
		$p = db_select_data('ko_leute', "WHERE `id` = '$pid'", '*', '', '', TRUE);
		$new = array();
		foreach(explode(',', $p['smallgroups']) as $kg) {
			if($kg == $kgid.':'.$role) continue;
			$new[] = $kg;
		}
		db_update_data('ko_leute', "WHERE `id` = '$pid'", array('smallgroups' => implode(',', $new)));

		//Store leute_changes and add log message
		ko_save_leute_changes($pid, $p);
		$log_message  = $pid.' ('.$p['vorname'].' '.$p['nachname'].'): '.getLL('kota_ko_leute_smallgroups').': ';
		$log_message .= ko_kgliste($p['smallgroups']).' --> '.ko_kgliste(implode(',', $new));
		ko_log('edit_person', $log_message);
	}
}//kota_smallgroup_members_post()




/**
 * Post processing function for groups_datafields.options
 * Formats options as serialized array and ensures correct type (select or multiselect)
 */
function kota_post_groups_datafields_options(&$value, $data) {
	//Get selected type from POST (or DB if not set in POST, which is the case for inline editing)
	if(isset($_POST['koi'][$data['table']]['type'])) {
		$type = $_POST['koi'][$data['table']]['type'][$data['id']];
	} else {
		$entry = db_select_data('ko_groups_datafields', "WHERE `id` = '".$data['id']."'", '*', '', '', TRUE);
		$type = $entry['type'];
	}

	//Check for select type
	if(in_array($type, array('select', 'multiselect'))) {
		//Store options for types select and multiselect
		$opts = array();
		foreach(explode("\n", $value) as $o) {
			$opts[] = format_userinput($o, 'js');
		}
		$value = serialize($opts);
	} else {
		//Set options to '' for other types
		$value = '';
	}
}//kota_post_groups_datafields_options()



/**
 * Post processing for groups_datafields.type
 * Empties options column if no select type
 */
function kota_post_groups_datafields_type(&$value, $data) {
	$value = format_userinput($value, 'alpha');
	//Reset options if no select type
	if(!in_array($value, array('select', 'multiselect'))) {
		db_update_data($data['table'], "WHERE `id` = '".$data['id']."'", array('options' => ''));
	}
}//kota_post_groups_datafields_type()




/**
 * Post processing for rota_teams assigned in an event group
 * Stores the event group id in ko_rota_teams
 */
function kota_eventgruppen_post_rota_teams($table, $col, $id, $value) {
	global $access;

	if(!isset($access['rota'])) ko_get_access('rota');

	$new_ids = explode(',', $value);
	$old_ids = array_keys(db_select_data('ko_rota_teams', "WHERE `eg_id` REGEXP '(^|,)$id(,|$)'"));
	$all_teams = db_select_data('ko_rota_teams');
	foreach($all_teams as $tid => $team) {
		if($access['rota']['ALL'] < 5 && $access['rota'][$tid] < 5) continue;

		//Added teams
		if(in_array($tid, $new_ids) && !in_array($tid, $old_ids)) {
			$new_eg_id = array_unique(array_merge(explode(',', $team['eg_id']), array($id)));
			foreach($new_eg_id as $k => $v) {
				if(!$v) unset($new_eg_id[$k]);
			}
			db_update_data('ko_rota_teams', "WHERE `id` = '$tid'", array('eg_id' => implode(',', $new_eg_id)));
		}

		//Removed teams
		else if(in_array($tid, $old_ids) && !in_array($tid, $new_ids)) {
			$new_eg_id = explode(',', $team['eg_id']);
			foreach($new_eg_id as $k => $v) {
				if(!$v || $v == $id) unset($new_eg_id[$k]);
			}
			db_update_data('ko_rota_teams', "WHERE `id` = '$tid'", array('eg_id' => implode(',', $new_eg_id)));
		}
	}//foreach(all_teams as tid => team)

}//kota_eventgruppen_post_rota_teams()






/**
 * KOTA FILL function for ko_kleingruppen.members_ROLE
 *
 * Fills the peoplesearch input for ko_kleingruppen.members_ROLE
 * Gets people from ko_leute assigned to the selected small group / role
 */
function kota_smallgroup_members_fill(&$row, $col) {
	$role = substr($col, -1);
	ko_get_kleingruppen($kg_, '', $row['id']);
	$kg = $kg_[$row['id']];

	$row['members_'.$role] = $kg['role_'.$role];
}//kota_smallgroup_members_fill()



/**
 * Fill double select rota_teams for event groups
 * Get all rota teams the current user has access to and return their ids, so they show as selected
 */
function kota_eventgruppen_fill_rota_teams(&$row, $col) {
	global $access;

	if(!isset($access['rota'])) ko_get_access('rota');

	$teams = db_select_data('ko_rota_teams', "WHERE `eg_id` REGEXP '(^|,)".$row['id']."(,|$)'", '*', 'ORDER BY name ASC');
	foreach($teams as $tid => $team) {
		if($access['rota']['ALL'] < 5 && $access['rota'][$tid] < 5) unset($teams[$tid]);
	}
	$row['rota_teams'] = implode(',', array_keys($teams));
}//kota_eventgruppen_fill_rota_teams()





/**
  * Functions to format some standard values displayed in lists
	*/

function kota_listview_date(&$value, $data) {
	global $DATETIME, $KOTA;

	$key = $KOTA[$data['table']][$data['col']]['list_options'];
	if(!$key) $key = 'ddmy';

	$d = $data["dataset"];
	if($d["enddatum"] != $d["startdatum"] && isset($d['enddatum'])) {
		$value = strftime($DATETIME[$key], strtotime($d["startdatum"])) . "-" . strftime($DATETIME[$key], strtotime($d["enddatum"]));
	} else {
		$value = strftime($DATETIME[$key], strtotime($d["startdatum"]));
	}
}//kota_listview_date()


function kota_listview_datecol(&$value, $data, $right=FALSE) {
	global $DATETIME, $KOTA;

	if($value == '0000-00-00' || $value == '0000-00-00 00:00:00') {
		$value = '';
	} else {
		$key = $KOTA[$data['table']][$data['col']]['list_options'];
		if(!$key) $key = 'ddmy';
		$value = strftime($DATETIME[$key], strtotime($value));
	}
	if($right) {
		$value = '<span style="float: right; padding-right: 4px;">'.$value.'</span>';
	}
}//kota_listview_datecol()


function kota_listview_datecol_right(&$value, $data) {
	kota_listview_datecol($value, $data, TRUE);
}


function kota_listview_datetimecol(&$value, $data) {
	global $DATETIME;
	if($value == '0000-00-00' || $value == '0000-00-00 00:00:00') {
		$value = '';
	} else {
		$value = strftime($DATETIME['ddmy'].' %H:%M:%S', strtotime($value));
	}
}//kota_listview_datetimecol()


function kota_listview_datespan(&$value, $data) {
	global $DATETIME;

	$d = $data['dataset'];
	$value = '';
	if($d['start'] != '0000-00-00') $value = strftime($DATETIME['dmY'], strtotime($d['start']));
	if($d['stop'] != '0000-00-00') $value .= ' - '.strftime($DATETIME['dmY'], strtotime($d['stop']));
}//kota_listview_datespan()


function kota_listview_time(&$value, $data) {
	$d = $data["dataset"];

	//Add seconds which might have been cut of by kota_process_data already
	if(strlen($d["startzeit"]) == 5) $d["startzeit"] .= ":00";
	if(strlen($d["endzeit"]) == 5) $d["endzeit"] .= ":00";

	if($d["startzeit"] == "00:00:00") {
		$value = getLL("time_all_day");
	} else if($d["endzeit"] != "" && $d["endzeit"] != "00:00:00") {
		$value = substr($d["startzeit"], 0, -3) . " - " . substr($d["endzeit"], 0, -3);
	} else {
		$value = substr($d["startzeit"], 0, -3);
	}
}//kota_listview_time()


function kota_listview_boolyesno(&$value, $data) {
	$value = $value?getLL("yes"):getLL("no");
}//kota_listview_boolyesno()


function kota_listview_boolx(&$value, $data) {
	$value = $value?"x":"";
}//kota_listview_boolx()


function kota_listview_people(&$value, $data, $link=FALSE) {
	global $ko_path;

	$id = $data["dataset"][$data["col"]];
	$ids = explode(",", $id);
	$a_value = array();
	$persons = db_select_data('ko_leute', "WHERE `id` IN ('".implode("','", $ids)."')");
	foreach($persons as $p) {
		//Mark deleted persons but still show them
		$pre = $p['deleted'] == 1 ? '<span style="text-decoration: line-through;" title="'.getLL('leute_labels_deleted').'">' : '';
		$post = $p['deleted'] == 1 ? '</span>' : '';
		//Add link
		if($link && ko_module_installed('leute')) {
			$pre .= '<a href="'.$ko_path.'leute/index.php?action=set_idfilter&id='.intval($p['id']).'">';
			$post .= '</a>';
		}
		if(trim($p['vorname']) == '' && trim($p['nachname']) == '') {
			$a_value[] = $pre.$p["firm"].($p['department'] ? ' ('.$p['department'].')' : '').$post;
		} else {
			$a_value[] = $pre.$p["vorname"]." ".$p["nachname"].$post;
		}
	}
	$value = implode(", ", $a_value);
}//kota_listview_people()


function kota_listview_firm(&$value, $data, $link=FALSE) {
	global $ko_path;

	$id = $data["dataset"][$data["col"]];
	$ids = explode(",", $id);
	$a_value = array();
	$persons = db_select_data('ko_leute', "WHERE `id` IN ('".implode("','", $ids)."')");
	foreach($persons as $p) {
		//Mark deleted persons but still show them
		$pre = $p['deleted'] == 1 ? '<span style="text-decoration: line-through;" title="'.getLL('leute_labels_deleted').'">' : '';
		$post = $p['deleted'] == 1 ? '</span>' : '';
		//Add link
		if($link && ko_module_installed('leute')) {
			$pre .= '<a href="'.$ko_path.'leute/index.php?action=set_idfilter&id='.intval($p['id']).'">';
			$post .= '</a>';
		}
		if(trim($p['firm']) == '') {
			$a_value[] = $pre.$p["vorname"]." ".$p["nachname"].$post;
		} else {
			$a_value[] = $pre.$p["firm"].($p['department'] ? ' ('.$p['department'].')' : '').$post;
		}
	}
	$value = implode(", ", $a_value);
}//kota_listview_firm()


function kota_listview_people_link(&$value, $data) {
	kota_listview_people($value, $data, TRUE);
}//kota_listview_people_link()


function kota_listview_login(&$value, $data) {
	$login = db_select_data("ko_admin", "WHERE `id` ='$value'", "login", "", "", TRUE);
	if($login['login']) $value = $login["login"];
}//kota_listview_login()


function kota_listview_longtext25(&$value, $data) {
	$value = '<span title="'.ko_html($value).'">'.substr($value, 0, 25).(strlen($value)>25?"..":"").'</span>';
}//kota_listview_longtext25()


function kota_listview_ll(&$value, $data) {
	$prefix = "kota_".$data["table"]."_".$data["col"]."_";
	$value = $value != "" ? getLL($prefix.$value) : "";
}//kota_listview_ll()


function kota_listview_eventgroup_name(&$value, $data) {
	$row = db_select_data('ko_eventgruppen', "WHERE `id` = '$value'", 'name', '', '', TRUE);
	$value = $row['name'] ? $row['name'] : '';
}//kota_listview_eventgroup_name()


function kota_map_leute_daten(&$value, $data) {
	$v = map_leute_daten('', $data['col'], $data['dataset']);
	if(is_array($v)) $value = array_shift($v);
	else $value = $v;
}//kota_map_leute_daten()


function kota_listview_smallgroups(&$value, $data) {
	$value = ko_kgliste($value);
}//kota_listview_smallgroups()


function kota_listview_rootid(&$value, $data) {
	if($_SESSION['ses_userid'] == ko_get_root_id()) {
		$title = $data['id'];
		$value = '<span title="id: '.$title.'">'.$value.'</span>';
	} else {
		$value = $value;
	}
}//kota_listview_rootid()



/**
 * Show number of reservations for a single event in list view. Tooltip shows single reservation's details
 */
function kota_listview_event_reservations(&$value, $data) {
	$ids = explode(',', $value);
	foreach($ids as $k => $v) {
		if(!$v) unset($ids[$k]);
	}
	if(sizeof($ids) > 0) {
		//Get reservation infos
		$res = db_select_data('ko_reservation AS r LEFT JOIN ko_resitem AS i ON r.item_id = i.id', "WHERE r.id IN ('".implode("','", $ids)."')", 'i.name AS resitem_name, r.startzeit, r.endzeit, r.zweck', '', '', FALSE, TRUE);
		$txt = '';
		foreach($res as $r) {
			//Add resitem
			$txt .= '<b>- '.$r['resitem_name'].'</b><br />';
			//Format time
			if($r['startzeit'] == '00:00:00' && $r['endzeit'] == '00:00:00') {
				$time = getLL('time_all_day');
			} else {
				$time = substr($r['startzeit'], 0, -3);
				if($r['endzeit'] != '00:00:00') $time .= ' - '.substr($r['endzeit'], 0, -3);
			}
			$txt .= $time.'<br />';
			//Add purpose of reservation
			if($r['zweck']) $txt .= strtr(trim($r['zweck']), array("\n" => "<br />", "\r" => "", "\t" => "", "'" => "\'")).'<br />';
			$txt .= '<br />';
		}
		if($txt != '') {
			//Add title
			$txt = getLL('daten_list_reservations_title').':<br /><br />'.htmlspecialchars($txt);
			$value = '<a href="#" onclick="return false;" onmouseover="tooltip.show(\''.$txt.'\', \'auto\', \'b\', \'l\');" onmouseout="tooltip.hide();">&nbsp;'.sizeof($ids).'&nbsp;</a>';
		} else {
			$value = '';
		}
	} else {
		$value = '';
	}
}//kota_listview_event_reservations()



function kota_listview_rota_schedule(&$value, $data) {
	$event = db_select_data('ko_event', "WHERE `id` = '".$data['id']."'", '*', '', '', TRUE);
	$col = $data['col'];
	list($temp, $tid) = explode('_', $col);
	if($event['rota'] != 1) return;

	$scheduled = db_select_data('ko_rota_schedulling', "WHERE `event_id` = '".$event['id']."' AND `team_id` = '$tid'", '*', '', '', TRUE, TRUE);
	if($scheduled['schedule'] != '') $value = implode(', ', ko_rota_schedulled_text($scheduled['schedule']));
}//kota_listview_rota_schedule()



function kota_listview_ko_groups_name(&$value, $data) {
	global $all_groups;

	$g = $data['dataset'];
	//Name
	if($_SESSION['ses_userid'] == ko_get_root_id()) {
		if(!is_array($all_groups)) ko_get_groups($all_groups);
		$title = '';
		$m = ko_groups_get_motherline($_SESSION['show_gid'], $all_groups);
		if(sizeof($m) > 0) $title .= 'g'.implode(':g', $m);
		if($_SESSION['show_gid'] != 'NULL') $title .= ($title == '' ? '' : ':').'g'.$all_groups[$_SESSION['show_gid']]['id'];
		$title .= ($title == '' ? '' : ':').'g'.$g['id'];
		$name = '<span title="'.$title.'">'.ko_html($g['name']).'</span>';
	} else {
		$name = ko_html($g['name']);
	}
	$num_subgroups = db_get_count('ko_groups', 'id', "AND `pid` = '".$g['id']."'");
	if($num_subgroups > 0) {
		$name = '<a href="?action=list_groups&amp;gid='.$g['id'].'"><b>'.$name.'</b></a>';
	}
	$value = $name;
}//kota_listview_ko_groups_name()



function kota_listview_ko_groups_nump(&$value, $data) {
	global $ko_path;

	$g = $data['dataset'];
	$num = db_get_count('ko_leute', 'id', "AND `groups` REGEXP 'g".$data['id']."' AND deleted = '0' ".ko_get_leute_hidden_sql());
	if($g['maxcount'] > 0) {
		$role = db_select_data('ko_grouproles', "WHERE `id` = '".$g['count_role']."'", '*', '', '', TRUE);
		$suffix = ' ('.$g['count'].'/'.$g['maxcount'].($g['count_role'] ? ' '.$role['name'] : '').')';
	} else {
		$suffix = '';
	}
	$value = '&nbsp;<a href="'.$ko_path.'leute/index.php?action=set_group_filter&amp;id='.$data['id'].'">'.$num.'</a>'.$suffix;
}//kota_listview_ko_groups_nump()




function kota_listview_ko_groups_numug(&$value, $data) {
	$g = $data['dataset'];
	$num = db_get_count('ko_groups', 'id', "AND `pid` = '".$g['id']."'");
	$value = $num > 0 ? $num : '';
}//kota_listview_ko_groups_numug()





function kota_listview_ko_groups_roles(&$value, $data) {
	global $ko_path;

	$values = array();
	$group = db_select_data('ko_groups', "WHERE `id` = '".$data['id']."'", '*', '', '', TRUE);
	foreach(explode(',', $group['roles']) as $role) {
		ko_get_grouproles($roles, "AND `id` = '$role'");
		$link = $ko_path.'leute/index.php?action=set_group_filter&amp;id='.$data['id'].'&amp;rid='.$role;
		$title = '';
		if($_SESSION['ses_userid'] == ko_get_root_id()) $title = 'title="r'.$roles[$role]['id'].'"';
		$values[] = '<a href="'.$link.'" '.$title.'>'.ko_html($roles[$role]['name']).'</a>';
	}
	$value = implode(', ', $values);
}//kota_listview_ko_groups_roles()




function kota_listview_ko_reservation_zweck(&$value, $data) {
	$e = $data['dataset'];
	if($e['comments'] != '') {
		if($_SESSION['ses_userid'] == ko_get_guest_id()) {
			//Don't show comments for guest user
			$value = ko_html($e['zweck']);
		} else {
			$value = "<a href=\"#\" onmouseover=\"return tooltip.show('&lt;b&gt;".getLL('kota_ko_reservation_comments')."&lt;/b&gt;: ".preg_replace("/(\r\n)+|(\n|\r)+/", '<br />', ko_html($e['comments']))."');\" onmouseout=\"return tooltip.hide();\">".ko_html($e['zweck']).'</a>';
		}
	}
}//kota_listview_ko_reservation_zweck()



function kota_listview_ko_resitem_name(&$value, $data) {
	$e = $data['dataset'];
	if($e['linked_items'] != '') {
		$value = $e["name"]." (+".sizeof(explode(",", $e["linked_items"])).")";
	}
}//kota_listview_ko_resitem_name()



function kota_listview_eventgroups(&$value, $data) {
	if($value == '') return '';
	$ids = explode(',', $value);
	if(sizeof($ids) == 0) return '';

	$egs = db_select_data('ko_eventgruppen', "WHERE `id` IN (".implode(',', $ids).")", '*', 'ORDER BY `name` ASC');
	$r = array();
	foreach($egs as $eg) {
		$r[] = $eg['name'];
	}
	$value = implode(', ', $r);
}//kota_listview_eventgroups()



function kota_listview_scheduler_task_next_call(&$value, $data) {
	$link = 'index.php?action=call_task&id='.$data['id'];
	$value = '<a href="'.$link.'">'.sql2datetime($value).'</a>';
}//kota_listview_scheduler_task_next_call()



function kota_listview_pdf_layout(&$value, $data) {
	$preset = unserialize($data['dataset']['data']);

	switch($data['col']) {
		case 'layout':
			$value = getLL('daten_export_preset_layout_'.$preset['layout']);
			if(!$value) $value = $preset['layout'];
		break;

		case 'start':
			if($preset[$preset['layout']]['start2']) {
				$value = $preset[$preset['layout']]['start2'];
			} else {
				$value = getLL('daten_export_preset_'.$preset['layout'].'_start_'.$preset[$preset['layout']]['start']);
			}
		break;

		case 'length':
			$value = $preset[$preset['layout']]['length'];
		break;
	}
}//kota_listview_pdf_layout()




function kota_listview_ko_resitem_moderation(&$value, $data) {
	$_value = $value;
	$prefix = 'kota_'.$data['table'].'_'.$data['col'].'_';
	$value = $value != '' ? getLL($prefix.$value) : '';

	//Add number and names of moderators if moderation is active
	if($_value != 0) {
		$mods = ko_get_moderators_by_resitem($data['id']);
		if(sizeof($mods) > 0) {
			$tooltip = '<p class="title">'.getLL('res_moderators_for_item').'</p>';
			foreach($mods as $mod) {
				$tooltip .= '<p style="margin-bottom: 6px;">';
				$tooltip .= '- Login: <b>'.$mod['login'].'</b>';
				if($mod['leute_id'] > 0) $tooltip .= ' ('.$mod['vorname'].' '.$mod['nachname'].')';
				$tooltip .= '<br />'.$mod['email'];
				$tooltip .= '</p>';
			}
			$value = '<span onmouseover="tooltip.show(\''.ko_html($tooltip).'\', \'\', \'b\', \'l\');" onmouseout="tooltip.hide();">'.$value.' ('.sizeof($mods).' '.getLL('res_moderators_short').')</span>';
		} else {
			$value .= ' ( ! '.getLL('res_no_moderator_for_item').')';
		}
	}
}//kota_listview_ko_resitem_moderation()





function kota_listview_ko_donations_person(&$value, $data, $priority='name') {
	global $ko_path;

	$v = '';
	$id = intval($data['dataset'][$data['col']]);
	$p = db_select_data('ko_leute', "WHERE `id` = '$id'", '*', '', '', TRUE);

	//Mark deleted persons but still show them
	$pre = $p['deleted'] == 1 ? '<span style="text-decoration: line-through;" title="'.getLL('leute_labels_deleted').'">' : '';
	$post = $p['deleted'] == 1 ? '</span>' : '';

	//Add link to person
	$link1 = '';
	if(ko_module_installed('leute') && $p['deleted'] != 1) {
		$link1  = '<a href="'.$ko_path.'leute/index.php?action=set_idfilter&id='.intval($p['id']).'" title="'.getLL('donations_title_pfilter').'">';
		$link1 .= '<img src="'.$ko_path.'images/external_link.png" border="0" />';
		$link1 .= '</a>&nbsp;&nbsp;';
	}

	//Add link to filter for this person's donations
	$link2a = '<a href="index.php?action=set_person_filter&amp;id='.$id.'" title="'.getLL('donations_title_apply_person_filter').'">';
	$link2b = '</a>';

	if($priority == 'name') {
		if(trim($p['vorname']) == '' && trim($p['nachname']) == '') {
			$v = $p['firm'].($p['department'] ? ' ('.$p['department'].')' : '');
		} else {
			$v = $p['vorname'].' '.$p['nachname'];
		}
	} else {
		if(trim($p['firm']) == '') {
			$v = $p['vorname'].' '.$p['nachname'];
		} else {
			$v = $p['firm'].($p['department'] ? ' ('.$p['department'].')' : '');
		}
	}

	$value = $link1.$link2a.$pre.$v.$post.$link2b;
}//kota_listview_ko_donations_person()




function kota_listview_ko_kleingruppen_name(&$value, $data) {
	global $all_groups;

	$g = $data['dataset'];
	$value = '<a href="index.php?action=set_kg_filter&amp;id='.$g['id'].'"><b>'.ko_html($g['name']).'</b></a>';

	$show_leiter = '';
	foreach(explode(',', $g['role_L']) as $l) {
		if(!$l) continue;
		ko_get_person_by_id($l, $p);
		if($p['vorname'] && $p['nachname']) $show_leiter .= $p['vorname'].' '.$p['nachname'].', ';
	}
	$value .= $show_leiter ? ' ('.substr($show_leiter, 0, -2).')' : '';
}//kota_listview_ko_kleingruppen_name()



function kota_listview_textmultiplus(&$value, $data) {
	$value = str_replace(',', ', ', $value);
}




function kota_listview_ko_tracking_filter(&$value, $data) {
	$parts = explode(',', $value);
	$names = array();
	foreach($parts as $part) {
		if(!$part) continue;

		if(substr($part, 0, 1) == 'F') {
			$fvalue = base64_decode(substr($part, 1));
			$filterset = db_select_data('ko_userprefs', "WHERE `value` = '$fvalue' AND `user_id` IN (-1, ".$_SESSION['ses_userid'].")", '*', '', '', TRUE);
			if($filterset['id']) {
				$global_tag = $filterset['user_id'] == '-1' ? getLL('leute_filter_global_short').' ' : '';
				$names[] = getLL('tracking_filter_short_filter').' "'.$global_tag.$filterset['key'].'"';
			}
		}
		else if(substr($part, 0, 1) == 'g') {
			$gid = substr($part, 1, 6);
			if(FALSE !== strpos($part, ':r')) {
				$rid = substr($part, -6);
				$role = db_select_data('ko_grouproles', "WHERE `id` = '$rid'", '*', '', '', TRUE);
			} else {
				$rid = '';
			}

			$_name = ko_groups_decode(ko_groups_decode('g'.$gid, 'full_gid'), 'group_desc_full');
			if($rid) $_name .= ':'.$role['name'];
			$names[] = getLL('tracking_filter_short_group').' "'.$_name.'"';
		}
		else if(strlen($part) == 4) {
			$kg = db_select_data('ko_kleingruppen', "WHERE `id` = '".intval($part)."'", '*', '', '', TRUE);
			$names[] = getLL('tracking_filter_short_smallgroup').' "'.$kg['name'].'"';
		}
	}

	$value = implode('<br />', $names);
}//kota_listview_ko_tracking_filter()



function kota_listview_ko_eventgruppen_name(&$value, $data) {
	global $ko_path;

	$value = stripslashes($value);
	kota_listview_rootid($value, $data);

	$id = intval($data['id']);
	$g = db_select_data('ko_eventgruppen', "WHERE `id` = '$id'", '*', '', '', TRUE);

	if($g['notify']) {
		$ids = array();
		foreach(explode(',', $g['notify']) as $gid) $ids[] = substr($gid, 1);
		ko_get_groups($groups, ' AND `id` IN (\''.implode("','", $ids).'\')');
		$names = '';
		foreach($groups as $group) $names .= $group['name'].', ';
		$names = substr($names, 0, -2);
		$value .= '&nbsp;<span onmouseover="tooltip.show(\''.getLL('daten_notify_hint').'<br /><b>'.$names.'</b>\');" onmouseout="tooltip.hide();"><img src="'.$ko_path.'images/comment.png" border="0" /></span>';
	}

	//Add icon to mark Google calendars
	if($g['type'] == 1) $value .= '&nbsp;<img src="'.$ko_path.'images/googlecal.png" border="0" title="'.getLL('daten_eventgroup_google').'" />';
	if($g['type'] == 2) $value .= '&nbsp;<sup title="'.getLL('daten_eventgroup_rota').'">('.getLL('rota_shortname').')</sup>';
	if($g['type'] == 3) $value .= '&nbsp;<img src="'.$ko_path.'images/feed.png" border="0" title="'.getLL('daten_eventgroup_ical').'" />';
}//kota_listview_eventgroup_name()




function kota_listview_file(&$value, $data) {
	global $BASE_PATH, $BASE_URL;

	if(!$value) return;
	if(!file_exists($BASE_PATH.$value)) return;

	$ext = strtolower(substr($value, (strrpos($value, '.')+1)));
	if(file_exists($BASE_PATH.'images/mime/'.$ext.'.png')) {
		$icon = '/images/mime/'.$ext.'.png';
	} else {
		$icon = '/images/mime/_default.png';
	}
	if(substr($value, 0, 1) == '/') $value = substr($value, 1);
	$link = $BASE_URL.$value;

	$value = '<a href="'.$link.'" target="_blank"><img src="'.$icon.'" border="0" /></a>';
}//kota_listview_file()



function kota_listview_money(&$value, $data) {
	$value = '<span style="float: right; padding-right: 4px;">'.number_format($value, 2, '.', "'").'</span>';
}



function kota_listview_color(&$value, $data) {
	if(!$value || strlen($value) != 6) {
		$value = '';
	} else {
		$value = '<span class="kota-listview-color" style="font-family: monospace; font-size: 11px; background-color: #'.$value.'; color: '.ko_get_contrast_color($value).';">'.$value.'</span>';
	}
}




function kota_listview_modules(&$value, $data) {
	global $MODULES;

	if(!$value) return;
	$modules = array();
	foreach(explode(',', $value) as $m) {
		if(!in_array($m, $MODULES)) continue;
		$ll = getLL('module_'.$m);
		if(!$ll) $ll = $m;
		$modules[] = $ll;
	}
	$value = implode(', ', $modules);
}//listview_modules()




function kota_listview_logins4admingroup(&$value, $data) {
	$id = intval($data['id']);
	if(!$id) return;

	$logins = db_select_data('ko_admin', "WHERE `admingroups` REGEXP '(^|,)$id(,|$)'", 'login, admingroups');
	$r = array();
	foreach($logins as $login) {
		$r[] = $login['login'];
	}
	$value = implode(', ', $r);
}//listview_logins2admingroups()






/**
  * Assigns values to a certain KOTA entry
	* Multiple assignement possible
	*/
function kota_assign_values($table, $cols, $pre_process=TRUE) {
	global $KOTA;

	foreach($cols as $col => $value) {
		if(!is_array($KOTA[$table][$col]["form"])) continue;
		$process_data = array($col => $value);
		$KOTA[$table][$col]['form']['ovalue'] = $value;
		if($pre_process) kota_process_data($table, $process_data, "pre");
		$KOTA[$table][$col]["form"]["value"] = $process_data[$col];
		//doubleselect
		if($KOTA[$table][$col]["form"]["type"] == "doubleselect") {
			foreach(explode(",", $process_data[$col]) as $v) {
				$KOTA[$table][$col]["form"]["avalues"][] = $v;
				$valuesi = array_flip($KOTA[$table][$col]["form"]["values"]);
				$KOTA[$table][$col]["form"]["adescs"][] = $KOTA[$table][$col]["form"]["descs"][$valuesi[$v]];
			}
			$KOTA[$table][$col]["form"]["avalue"] = $process_data[$col];
		}
		else if($KOTA[$table][$col]['form']['type'] == 'peoplesearch') {
			$lids = explode(',', $value);
			list($av, $ad) = kota_peopleselect($lids, $KOTA[$table][$col]['form']['sort']);
			$KOTA[$table][$col]['form']['avalues'] = $av;
			$KOTA[$table][$col]['form']['adescs'] = $ad;

			$KOTA[$table][$col]['form']['avalue'] = $value;

		}
	}
}//kota_assign_values()



/**
  * processes a data array according to the rules in KOTA
	* modes can be pre, post or list
	* modes can also be a comma list of the above values, then the first available is applied, but only one
	*/
function kota_process_data($table, &$data, $modes, &$log, $id=0) {
	global $KOTA;

	if(!is_array($modes)) $modes = explode(",", $modes);

	$orig_data = $data;
	foreach($data as $col => $value) {
		if(!isset($KOTA[$table][$col])) continue;
		if(substr($col, -7) == '_DELETE') continue;  //File deletion will be handled below

		$kota_data = array("table" => $table, "col" => $col, "id" => $id, "dataset" => &$data);

		//Bei Textplus-feldern auf Wert im Text-feld überprüfen und diesen verwenden falls != ""
		if($KOTA[$table][$col]["form"]["type"] == "textplus") {
			$plus_value = $data[$col."_PLUS"];
			if(is_array($plus_value)) $plus_value = array_shift($plus_value);
			if($plus_value != "") $value = $plus_value;
			unset($data[$col."_PLUS"]);
		}

		//get first array element, if value is array
		if(is_array($value)) $value = array_shift($value);

		//Modules
		if(substr($col, 0, 6) == "MODULE") {
			if(substr($col, 6, 3) == "grp") {
				if(FALSE === strpos($col, ':')) {
					//Get id of edited group
					$gid = substr($col, 9, 6);
					//Get new save string
					ko_groups_get_savestring($value, array("id" => $id, "col" => $col), $log, NULL, TRUE, (in_array("post", $modes)));
					//If group has been deselected then delete all group datafields for this group
					if(in_array('post', $modes) && $gid && FALSE === strpos($value, 'g'.$gid)) db_delete_data('ko_groups_datafields_data', "WHERE `group_id` = '$gid' AND `person_id` = '".$id."'");
				}
				else {
					//Get current datafield data
					$gid = substr($col, 9, 6);
					$fid = substr($col, 16, 6);
					$cdf = db_select_data('ko_groups_datafields_data', "WHERE `datafield_id` = '$fid' AND `person_id` = '$id' AND `group_id` = '$gid'", '*', '', '', TRUE);
					if(in_array('list', $modes)) {
						//Get datafield definition
						$datafield = db_select_data('ko_groups_datafields', "WHERE `id` = '$fid'", '*', '', '', TRUE);
						//Return value for list view
						if($datafield['type'] == 'checkbox') $value = $cdf['value'] == 1 ? ko_html(getLL('yes')) : ko_html(getLL('no'));
						else $value = ko_html($cdf['value']);
					} else {
						//Store group datafields data
						if(!$cdf) {  //Add new entry
							db_insert_data('ko_groups_datafields_data', array('group_id' => $gid, 'person_id' => $id, 'datafield_id' => $fid, 'value' => $value));
							$log = getLL('leute_log_datafields')." (g$gid:$fid) for $id: $value";
						} else {  //Update current entry
							if($cdf['value'] != $value) {
								db_update_data('ko_groups_datafields_data', "WHERE `datafield_id` = '$fid' AND `person_id` = '$id' AND `group_id` = '$gid'", array('value' => $value));
								$log = getLL('leute_log_datafields')." (g$gid:$fid) for $id: $value";
							}
						}
					}
				}//group..else gdf
			}//grp
		}//MODULE

		$fu_modes = array("uint", "int", "int@", "intlist", "alphanumlist", "float", "alphanum", "alphanum+", "alphanum++", "email", "dir", "js", "alpha", "alpha+", "alpha++", "date");
		$done = FALSE;
		foreach($modes as $mode) {
			if($done) continue;

			//process data
			if($KOTA[$table][$col][$mode]) {
				$done = TRUE;
				foreach(explode(';', $KOTA[$table][$col][$mode]) as $applyMe) {
					//Separate Funktion aufrufen
					if(substr($applyMe, 0, 4) == 'FCN:') {
						$fcn = substr($applyMe, 4);
						if(function_exists($fcn)) {
							eval("$fcn(\$value, \$kota_data, \$log, \$orig_data);");
						}
					} else {  //Apply function
						if($applyMe == 'ko_html') {  //Just apply ko_html (usually used for pre)
							$value = ko_html($value);
						} else if(in_array($applyMe, $fu_modes)) {  //simple format_userinput()
							$value = format_userinput($value, $applyMe);
						} else if($applyMe == 'none') {
							//Do nothing
						} else {  //Other, user-defined function, where @VALUE@ is substituted
							eval("\$value=".str_replace('@VALUE@', addslashes($value), $applyMe).';');
						}
					}
				}
			} else {
				$value = format_userinput($value, "text");
			}
		}//foreach(explode(",", modes))
		$data[$col] = $value;
	}//foreach(data as col => value)

	//Check for file uploads. Not included in _POST[koi] so process separately
	foreach($KOTA[$table] as $col => $kdata) {
		$value = $data[$col];
		if($kdata['form']['type'] == 'file') {
			//only save newly submitted files
			if($id == 0 && sizeof($_FILES['koi']['tmp_name'][$table][$col]) == 1) {
				//When using save_as_new the ID in koi (POST) is from the old entry
				// So just use the first entry from _FILES
				$files_copy = $_FILES['koi']['tmp_name'][$table][$col];
				list($files_id) = array_keys($files_copy);
				$tmp_file = array_shift($files_copy);
				unset($files_copy);
			} else {
				$tmp_file = $_FILES['koi']['tmp_name'][$table][$col][$id];
				$files_id = $id;
			}
			if($tmp_file) {
				$fdata = array('table' => $table, 'col' => $col, 'id' => $files_id);
				//For new entry: Get ID of new event (next auto_increment value) to store file with correct ID
				$new_id = $id == 0 ? db_get_next_id($table) : $id;
				kota_save_file($value, $fdata, $new_id);
				if($id > 0) {
					db_update_data($table, "WHERE `id` = '$id'", array($col => $value));
				} else {
					$data[$col] = $value;
				}
			}
			//check for delete-checkbox for this file (only possible for edit)
			else if($data[$col.'_DELETE'][$id] == 1 || $data[$col.'_DELETE'] == 1) {
				$col_value = db_select_data($table, "WHERE `id` = '$id'", '`'.$col.'`', '', '', TRUE);
				$fdata = array('table' => $table, 'col' => $col, 'id' => $id);
				kota_delete_file($col_value[$col], $fdata);
				db_update_data($table, "WHERE `id` = '$id'", array($col => ''));
				unset($data[$col.'_DELETE']);
			}
		}
	}

	//Check for checkboxes (don't show up in $data if not set (anymore))
	foreach($KOTA[$table] as $col => $v) {
		if(isset($data[$col])) continue;
		if($v['form']['type'] == 'checkbox') {
			$data[$col] = 0;
		}
	}


}//kota_process_data()




/**
 * POST function for enddate columns
 * If no value is given for the enddate then this function get the value for startdate and uses it for enddate
 */
function kota_post_enddate(&$value, $data) {
	$value = sql_datum($value);
	if(trim($value) == '') {
		if($data['id'] == 0 && $_POST['id'] != '' && intval($_POST['id']) == $_POST['id']) {
			//submit_as_new
			$id = intval($_POST['id']);
		} else {
			//edit, new
			$id = $data['id'];
		}
		$value = sql_datum($_POST['koi'][$data['table']]['startdatum'][$id]);
	}
}//kota_post_enddate()



/**
 * PRE function for enddate columns
 * If start and enddate are the same then set enddate to empty
 */
function kota_pre_enddate(&$value, $data) {
	if(sql_datum($data['dataset']['startdatum']) == $value) $value = '';
	else $value = sql2datum($value);
}//kota_pre_enddate()



/**
  * returns an array of formatted values with the array-key being the description of the KOTA field
	*/
function kota_get_list($data, $table) {
	global $KOTA;

	kota_process_data($table, $data, "list,pre");

	$list = array();
	foreach($data as $col => $value) {
		$ll_value = getLL("kota_".$table."_".$col);
		if(!$ll_value) continue;

		$list[$ll_value] = $value;
	}
	return $list;
}//kota_get_list()




/**
  * Convert the values and descs used for a dynselect to values/descs used for a select with optgroups (used for multiediting)
	* See kota_ko_event_eventgruppen_id_dynselect or kota_ko_reservation_item_id_dynselect for examples
	*/
function kota_convert_dynselect_select($values, $descs) {
	$new_values = $new_descs = array();
	//convert all top level elements with their children elements
	foreach($values as $ivid => $value) {
		if(substr($ivid, 0, 1) != "i") continue;
		foreach($value as $gid) {
			$new_values[$gid] = $gid;
			$new_descs[$gid] = $descs[$gid];
		}
	}
	//Add top level elements without children
	foreach($values as $gid) {
		if(substr($gid, 0, 1) == "i") continue;
		$new_values[$gid] = $gid;
		$new_descs[$gid] = $descs[$gid];
	}
	return array($new_values, $new_descs);
}//kota_convert_dynselect_select()




function kota_apply_filter($table) {
	global $KOTA;

	$kota_where = '';
	if(is_array($_SESSION['kota_filter'][$table])) {
		//Find all columns with filtering enabled
		$filter_cols = array();
		foreach($KOTA[$table]['_listview'] as $col) {
			if(($col['name'] || $col['multiedit']) && $col['filter']) {
				//Use multiedit columns if set, otherwise use name (multiple columns may be given, separated by comma)
				$fields = $col['multiedit'] ? $col['multiedit'] : $col['name'];
				foreach(explode(',', $fields) as $f) {
					if(!$f) continue;
					$filter_cols[] = $f;
				}
			}
		}
		foreach($_SESSION['kota_filter'][$table] as $col => $v) {
			if($col && in_array($col, $filter_cols) && $v != '') {
				$not = '';
				if(substr($v, 0, 1) == '!') {
					$v = substr($v, 1);
					$not = 'NOT';
				}
				//Apply custom SQL from KOTA
				if($KOTA[$table][$col]['filter']['sql']) {
					$sql = $KOTA[$table][$col]['filter']['sql'];
					$map = array('[TABLE]' => $table, '[FIELD]' => $col, '[NOT]' => $not, '[VALUE]' => $v);
					$kota_where .= " (".strtr($sql, $map).") AND ";
				}
				//Use special SQL for peoplsearch
				else if($KOTA[$table][$col]['form']['type'] == 'peoplesearch') {
					$kota_where .= " $table.$col $not REGEXP '(^|,)".mysql_real_escape_string($v)."(,|$)' AND ";
				}
				//Default SQL: LIKE %v%
				else {
					$kota_where .= " $table.$col $not LIKE '%".mysql_real_escape_string($v)."%' AND ";
				}
			}
		}
		if($kota_where) $kota_where = substr($kota_where, 0, -4);
	}

	return $kota_where;
}//kota_apply_filter()



function kota_filter_get_warntext($table) {
	global $KOTA;

	$r = '';
	if(is_array($_SESSION['kota_filter'][$table])) {
		$is_filtered = FALSE;
		$txts = array();
		foreach($_SESSION['kota_filter'][$table] as $k => $v) {
			if($k != '' && $v != '') {
				//TODO: Add kota_processing for all values, so list view is outputted (e.g. for peoplesearch)
				if($KOTA[$table][$k]['form']['type'] == 'checkbox') {
					$ll = $v ? getLL('yes') : getLL('no');
				} else {
					$ll = getLL('kota_'.$table.'_'.$k.'_'.$v);
				}
				if(!$ll) $ll = $v;
				$txts[] = getLL('kota_'.$table.'_'.$k).': '.ko_html($ll);
				$is_filtered = TRUE;
			}
		}
		if($is_filtered) {
			$r = '<b>'.getLL('kota_filter_applied').': </b>'.implode(', ', $txts);
		}
	}
	return $r;
}//kota_filter_get_warntext()





function kota_get_textmultiplus_values($table, $col) {
	global $KOTA;

	$fullvalues = db_select_distinct($table, $col, '', $KOTA[$table][$col]['form']['where'], $KOTA[$table][$col]['form']['select_case_sensitive'] ? TRUE : FALSE);
	$values = array();
	foreach($fullvalues as $fv) {
		$vs = explode(',', $fv);
		foreach($vs as $v) {
			$v = trim($v);
			if(!$v) continue;
			if(!in_array($v, $values)) $values[] = $v;
		}
	}
	if($KOTA[$table][$col]['form']['sort'] == 'DESC') {
		rsort($values);
	} else {
		sort($values);
	}

	return $values;
}//kota_get_textmultiplus_values()





function kota_ft_get_content($field, $pid, $new=FALSE) {
	global $KOTA;

	list($ptable, $col) = explode('.', $field);
	$table = $KOTA[$ptable][$col]['form']['table'];

	$content = '';

	$content .= '<input type="hidden" name="ft_'.$field.'_id" value="'.$pid.'" />';

	$kota_fields = array();
	foreach($KOTA[$table] as $k => $v) {
		if(substr($k, 0, 1) == '_') continue;
		$kota_fields[] = $k;
		if($v['form']['type'] == 'textplus') $kota_fields[] = $k.'_PLUS';
	}

	if($new !== FALSE) $new = intval($new);
	if($new === 0) {
		$submit_new = '<input type="submit" value="'.getLL('save').'" class="form_ft_save" data-field="'.$field.'" data-table="'.$table.'" data-after="0" data-pid="'.$pid.'" data-fields="'.implode(',', $kota_fields).'" />';
		$content .= kota_ft_get_row($table, 0, $submit_new, getLL('form_ft_title_new'));
	}

	$rows = db_select_data($table, "WHERE `pid` = '$pid'", '*', 'ORDER BY `sorting` ASC');
	foreach($rows as $row) {
		$submit_edit = '<input type="submit" value="'.getLL('save').'" class="form_ft_save" data-field="'.$field.'" data-table="'.$table.'" data-after="'.$new.'" data-pid="'.$pid.'" data-fields="'.implode(',', $kota_fields).'" data-id="'.$row['id'].'" />';
		$content .= kota_ft_get_row($table, $row['id'], $submit_edit, '', $field, $pid);

		if($new == $row['id']) {
			$submit_new = '<input type="submit" value="'.getLL('save').'" class="form_ft_save" data-field="'.$field.'" data-table="'.$table.'" data-after="'.$new.'" data-pid="'.$pid.'" data-fields="'.implode(',', $kota_fields).'" data-id="" />';
			$content .= kota_ft_get_row($table, 0, $submit_new, getLL('form_ft_title_new'));
		}
	}

	return $content;
}//kota_ft_get_content()




function kota_ft_get_row($table, $id, $submit, $title='', $field='', $pid='') {
	global $smarty, $ko_path;

	//Work with local copy to not interfere with main form
	$local_smarty = clone $smarty;

	$grp = ko_multiedit_formular($table, '', $id, '', '', TRUE);
	$local_smarty->assign('tpl_special_submit', $submit);
	$local_smarty->assign('tpl_titel', $title);
	$local_smarty->assign('tpl_hide_cancel', TRUE);
	$local_smarty->assign('tpl_groups', $grp);
	$content = $local_smarty->fetch('ko_formular.tpl');

	if($id > 0) {
		$delete = '<img src="'.$ko_path.'images/button_delete.gif" border="0" title="'.getLL('form_ft_button_delete_title').'" class="form_ft_delete" data-field="'.$field.'" data-pid="'.$pid.'" data-id="'.$id.'" />';
		$add = '<img src="'.$ko_path.'images/icon_plus.png" border="0" title="'.getLL('form_ft_button_new_title').'" class="form_ft_add" data-after="'.$id.'" data-field="'.$field.'" data-pid="'.$pid.'" border="0" />';
		$moveup = '<img src="'.$ko_path.'images/icon_arrow_up_big_enabled.png" border="0" title="'.getLL('form_ft_button_moveup_title').'" class="form_ft_moveup" data-field="'.$field.'" data-pid="'.$pid.'" data-id="'.$id.'" />';
		$movedown = '<img src="'.$ko_path.'images/icon_arrow_down_big_enabled.png" border="0" title="'.getLL('form_ft_button_movedown_title').'" class="form_ft_movedown" data-field="'.$field.'" data-pid="'.$pid.'" data-id="'.$id.'" />';

		$editicons  = '<div class="form_ft_header_button">'.$add.'</div>';
		$editicons .= '<div class="form_ft_header_button">'.$movedown.'</div>';
		$editicons .= '<div class="form_ft_header_button">'.$moveup.'</div>';
		$editicons .= '<div class="form_ft_header_button">'.$delete.'</div>';
	}

	$c = '<div class="form_ft_row">';
		$c .= '<div class="form_ft_header">'.$editicons.'</div>';

		$c .= '<div class="form_ft_content">';
			$c .= '<form action="index.php" action="POST">'.$content.'</form>';
		$c .= '</div>';
	$c .= '</div>';

	return $c;
}



function kota_peoplefilter2filterarray($orig) {
	if(!$orig) return FALSE;

	$filterArray = array();
	foreach(explode(',', $orig) as $row) {
		list($fid, $var1, $var2, $var3, $neg) = explode('|', $row);
		$vars = array();
		$vars[1] = $var1;
		$vars[2] = $var2;
		$vars[3] = $var3;
		$filterArray[] = array(0 => $fid, 1 => $vars, 2 => $neg);
	}

	//TODO
	$filterArray['link'] = 'and';

	return $filterArray;
}

function kota_ajax_convert_textareas_to_rte ($active_module) {
	global $ko_path;
	$options = '';
	if (file_exists($ko_path . $active_module . '/inc/ckeditor_custom_config.js')) {
		$options = '{customConfig : \'/'.$active_module.'/inc/ckeditor_custom_config.js\' }';
	}
	return '@@@POST@@@$(\'.richtexteditor\').ckeditor('.$options.');';
}


/**
 * returns a string (with filter link if access on leute is >= 1) giving information about the registrations of a certain event.
 * captures only events linked to a group with a maxcount
 *
 * @param $value
 * @param $kota_data
 * @param $log
 * @param $orig_data
 */
function kota_listview_ko_event_registrations (&$value, $data, $log, $orig_data) {
	global $ko_path, $access;

	$id = $data['id'];
	$gs_gid = $data['dataset']['gs_gid'];
	if(!$gs_gid) {
		$result = '';
	}
	else {
		$result = '';

		$gid = format_userinput(ko_groups_decode($gs_gid, 'group_id'), 'uint');
		$group = db_select_data('ko_groups', "WHERE `id` = '$gid'", '*', '', '', TRUE);
		if($group['id'] && $group['id'] == $gid) {
			if (!isset($access['leute'])) {
				ko_get_access('leute');
			}
			if ($access['leute']['ALL'] >= 1) {
				$link = $ko_path.'leute/index.php?action=set_group_filter&amp;id='.$group['id'];
			} else {
				$link = '';
			}
			$result .= $group['count'];

			if($group['maxcount'] > 0) $result .= ' / '.$group['maxcount'];
			if($group['count_role']) {
				$role = db_select_data('ko_grouproles', "WHERE `id` = '".$group['count_role']."'", '*', '', '', TRUE);
				if($role['id'] && $role['id'] == $group['count_role']) {
					$result .= ' ('.$role['name'].')';
					if($link) $link += '&rid=' . $role['id'];
				}
			}
			if($link) $result = '<a href="'.$link.'">'.$result.'</a>';

			//Find open subscriptions (ko_leute_mod)
			$gs_mod = db_select_data('ko_leute_mod', "WHERE `_group_id` LIKE 'g".$gid."%'");
			if(sizeof($gs_mod) > 0) {
				$result .= '<br /><b>'.getLL('fm_mod_open_group').':</b> '.sizeof($gs_mod);
			}
		}
	}
	$value = $result;
}

/**
 * fetches all options to be displayed as deadline in the reminder form
 *
 * @param null $key
 * @return array
 */
function kota_reminder_get_deadlines ($key = null) {
	$data = array();
	// 1, 2, 3, 6, 12, 18 h

	$data[-504] = sprintf("3 %s %s", getLL('weeks'), getLL('before'));
	$data[-336] = sprintf("2 %s %s", getLL('weeks'), getLL('before'));
	$data[-168] = sprintf("1 %s %s", getLL('week'), getLL('before'));

	$data[-144] = sprintf("6 %s %s", getLL('days'), getLL('before'));
	$data[-120] = sprintf("5 %s %s", getLL('days'), getLL('before'));
	$data[-96] = sprintf("4 %s %s", getLL('days'), getLL('before'));
	$data[-72] = sprintf("3 %s %s", getLL('days'), getLL('before'));
	$data[-48] = sprintf("2 %s %s", getLL('days'), getLL('before'));
	$data[-24] = sprintf("1 %s %s", getLL('day'), getLL('before'));

	$data[-18] = sprintf("18 %s %s", getLL('time_hours'), getLL('before'));
	$data[-12] = sprintf("12 %s %s", getLL('time_hours'), getLL('before'));
	$data[-6] = sprintf("6 %s %s", getLL('time_hours'), getLL('before'));
	$data[-3] = sprintf("3 %s %s", getLL('time_hours'), getLL('before'));
	$data[-2] = sprintf("2 %s %s", getLL('time_hours'), getLL('before'));
	$data[-1] = sprintf("1 %s %s", getLL('time_hour'), getLL('before'));

	$data[0] = sprintf("0 %s %s", getLL('time_hours'), getLL('before'));

	$data[1] = sprintf("1 %s %s", getLL('time_hour'), getLL('after'));
	$data[2] = sprintf("2 %s %s", getLL('time_hours'), getLL('after'));
	$data[3] = sprintf("3 %s %s", getLL('time_hours'), getLL('after'));
	$data[6] = sprintf("6 %s %s", getLL('time_hours'), getLL('after'));
	$data[12] = sprintf("12 %s %s", getLL('time_hours'), getLL('after'));
	$data[18] = sprintf("18 %s %s", getLL('time_hours'), getLL('after'));

	$data[24] = sprintf("1 %s %s", getLL('day'), getLL('after'));
	$data[48] = sprintf("2 %s %s", getLL('days'), getLL('after'));
	$data[72] = sprintf("3 %s %s", getLL('days'), getLL('after'));
	$data[96] = sprintf("4 %s %s", getLL('days'), getLL('after'));
	$data[120] = sprintf("5 %s %s", getLL('days'), getLL('after'));
	$data[144] = sprintf("6 %s %s", getLL('days'), getLL('after'));

	$data[168] = sprintf("1 %s %s", getLL('week'), getLL('after'));
	$data[336] = sprintf("2 %s %s", getLL('weeks'), getLL('after'));
	$data[504] = sprintf("3 %s %s", getLL('weeks'), getLL('after'));

	if ($key == null) {
		return $data;
	}
	else {
		return $data[$key];
	}
}

/**
 * return a comma separated list of all recipients of the supplied reminder
 *
 * @param $value
 * @param $data
 */
function kota_reminder_get_recipients (&$value, $data) {
	$result = array();

	foreach (explode(',', $data['dataset']['recipients_groups']) as $recGroup) {
		if (trim($recGroup) == '') continue;
		$roleDesc = ko_groups_decode($recGroup, "role_desc");
		$result[] = getLL('groups_datafields_reusable_short') . "[" . ko_groups_decode($recGroup, "group_desc") . ($roleDesc ? ":" . $roleDesc : '') . ']';
	}

	foreach (explode(',', $data['dataset']['recipients_leute']) as $recPerson) {
		if (trim($recPerson) == '') continue;
		ko_get_person_by_id($recPerson, $person);
		$result[] = $person['nachname'] . ' ' . $person['vorname'];
	}

	foreach (explode(',', $data['dataset']['recipients_mails']) as $recMail) {
		if (trim($recMail) == '') continue;
		$result[] = $recMail;
	}

	$value = implode(', ', $result);

}

/**
 * removes whitespace between entries in a comma separated list
 *
 * @param $v
 * @param $data
 */
function kota_explode_trim_implode(&$v, $data) {
	$v = ko_explode_trim_implode($v, ',');
}

/**
 * @param $pid
 * @param $joinValueLocal
 * @param $result
 */
function kota_event_program_check_access($pid, $joinValueLocal, &$result) {
	global $access;
	if (!isset($access['daten'])) ko_get_access('daten');
	ko_get_eventgruppe_by_id($joinValueLocal, $eg);
	if ($eg['moderation'] != 0 && $access['daten'][$joinValueLocal] < 3) $result = false;
	else $result = true;
}
