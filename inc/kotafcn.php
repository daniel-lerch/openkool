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

				case "responsible_for_res":
					$data['values'][] = '';
					$data['descs'][] = '';
					ko_get_logins($logins);
					foreach ($logins as $login) {
						if($login['id'] == ko_get_guest_id()) continue;
						if($login['id'] == ko_get_root_id() && $_SESSION['ses_userid'] != ko_get_root_id()) continue;

						$name = $login['login'];
						$leuteId = $login['leute_id'];
						if($leuteId) {
							ko_get_person_by_id($leuteId, $person);
							if(is_array($person) && ($person['vorname'] || $person['nachname'])) {
								$name = trim($person['vorname'].' '.$person['nachname']);
								$name .= ' ('.$login['login'].')';
							}
						}
						$data['values'][] = $login['id'];
						$data['descs'][] = $name;
					}
				break;
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
				case "responsible_for_res":
					ko_get_logins($logins);
					$data['values'][] = 0;
					$data['descs'][] = '';
					foreach ($logins as $login) {
						if($login['id'] == ko_get_guest_id()) continue;
						if($login['id'] == ko_get_root_id() && $_SESSION['ses_userid'] != ko_get_root_id()) continue;

						$name = $login['login'];
						$leuteId = $login['leute_id'];
						if($leuteId) {
							ko_get_person_by_id($leuteId, $person);
							if(is_array($person) && ($person['vorname'] || $person['nachname'])) {
								$name = trim($person['vorname'].' '.$person['nachname']);
								$name .= ' ('.$login['login'].')';
							}
						}
						$data['values'][] = $login['id'];
						$data['descs'][] = $name;
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
					$all_teams = ko_rota_get_all_teams(" and `rotatype` = 'event'");
					ko_get_access('rota');
					foreach($all_teams as $team) {
						if($access['rota']['ALL'] < 5 && $access['rota'][$team['id']] < 5) continue;
						$data['values'][] = $team['id'];
						$data['descs'][] = $team['name'];
					}
				break;
			}
		break; // ko_event_program, ko_eventgruppen_program
		case 'ko_event_rooms':
			$rooms = db_select_data("ko_event_rooms","WHERE 1=1", "id,title", "ORDER BY title ASC");
			$data["values"][0] = 0;
			$data["descs"][0] = "";

			foreach($rooms as $room) {
				$data["values"][] = $room['id'];
				$data["descs"][] = $room['title'];
			}
			break;

		case "ko_leute":
			switch($column) {
				case "land":
					$data["values"][] = $data["descs"][] = "";
					ko_get_all_countries($c);
					$data["values"] = $data["descs"] = $c;
					break;
				case "anrede":
					ko_get_all_anreden($c);
					foreach($c as $d) {
						if($d == '') continue;
						$data["values"][] = $d;
						$data["descs"][] = $d;
					}
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
						if($k == '_default') continue;
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
					$accounts = db_select_data("ko_donations_accounts", "WHERE `archived` = 0", "*", "ORDER BY number ASC, name ASC");
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


		case "ko_donations_accounts":
			switch($column) {
				case "group_id":
					$groups = ko_groups_get_recursive(ko_get_groups_zwhere(), TRUE);
					if(!is_array($all_groups)) ko_get_groups($all_groups);
					ko_get_access('groups');
					ko_get_grouproles($roles);
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
						if($grp['roles'] != '') {
							foreach(explode(',', $grp['roles']) as $rid) {
								$data['values'][] = 'g'.$grp['id'].':r'.$rid;
								$data['descs'][] = $pre.ko_html($grp['name'].': '.$roles[$rid]['name']);
							}
						}
					}
				break;
			}
		break;

		case 'ko_donations_accountgroups':
			$groups = db_select_data("ko_donations_accountgroups", "WHERE 1", "id,title", "ORDER BY title ASC");

			if($access['donations']['ALL'] > 3) {
				$data["values"][0] = 0;
				$data["descs"][0] = "";
			}

			foreach($groups as $group) {
				if($access['donations']['ALL'] < 4 && $access['donations']['ag'.$group['id']] < 4) continue;

				$data["values"][] = $group['id'];
				$data["descs"][] = $group['title'];
			}
		break;


		case 'ko_tracking_entries':
			switch($column) {
				case 'tid':
					$trackings = db_select_data('ko_tracking', '', '*', 'ORDER BY `name` ASC');
					//Add empty entry
					$data['values'][] = '';
					$data['descs'][] = '';
					foreach($trackings as $id => $tracking) {
						$data['values'][] = $id;
						$data['descs'][] = $tracking['name'];
					}
				break;
			}
		break;


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
						$groups = ko_groups_get_recursive(ko_get_groups_zwhere(), TRUE);
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
						$add_roles = TRUE;
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

					if(ko_module_installed("taxonomy")) {
						$data['values'][] = '';
						$data['descs'][] = '--- ' . strtoupper(getLL('kota_ko_event_terms')) . ' ---';
						$terms = ko_taxonomy_get_terms();
						$structuredTerms = ko_taxonomy_terms_sort_hierarchically($terms);
						foreach ($structuredTerms AS $structuredTerm) {
							if(!empty($structuredTerm['children'])) {
								$data['values'][] = 'TERM' . $structuredTerm['data']['id'];
								$data['descs'][] = $structuredTerm['data']['name'];

								foreach($structuredTerm['children'] AS $childTerm) {
									$data['values'][] = 'TERM' . $childTerm['id'];
									$data['descs'][] = "&nbsp; &nbsp; " . $childTerm['name'];
								}
							} else {
								$data['values'][] = 'TERM' . $structuredTerm['data']['id'];
								$data['descs'][] = $structuredTerm['data']['name'];
							}
						}
					}

					$data['values'][] = '';
					$data['descs'][] = '--- '.strtoupper(getLL('leute_labels_preset')).' ---';
					$userPresets = ko_get_userpref($_SESSION['ses_userid'], '', 'daten_itemset');
					$globalPresets = ko_get_userpref(-1, '', 'daten_itemset');
					if($userPresets) {
						foreach ($userPresets as $userPreset) {
							$data['values'][] = 'EGPR' . $userPreset['key'];
							$data['descs'][] = $userPreset['key'];
						}
					}
					if($globalPresets) {
						foreach ($globalPresets as $globalPreset) {
							$data['values'][] = 'EGPR[G] ' . $globalPreset['key'];
							$data['descs'][] = $globalPreset['key'];
						}
					}

					break;
			}
		break; // ko_reminder

		case 'ko_labels':
			switch($column) {
				case 'page_format':
					$data['values'] = array('A4', 'A5', 'A6', 'C6', 'B6', 'C65', 'C5', 'B5', 'C4', 'B4');
					$data['descs'] = array('A4', 'A5', 'A6', 'C6', 'B6', 'C65', 'C5', 'B5', 'C4', 'B4');
				break;

				case 'page_orientation':
					$data['values'] = array('P', 'L');
					foreach ($data['values'] as $value) {
						$data['descs'][] = getLL("kota_ko_labels_page_orientation_".$value);
					}
				break;

				case 'align_horiz':
					$data['values'] = array("L", "C", "R");
					foreach ($data['values'] as $value) {
						$data['descs'][] = getLL("kota_ko_labels_align_horiz_".$value);
					}
				break;

				case 'align_vert':
					$data['values'] = array("T", "C", "B");
					foreach ($data['values'] as $value) {
						$data['descs'][] = getLL("kota_ko_labels_align_vert_".$value);
					}
				break;

				case 'ra_font':
				case 'font':
					$_values = ko_get_pdf_fonts();
					$_descs = array();
					foreach($_values as $font) {
						$_descs[] = $font['name'];
					}
					$data['values'] = array_keys($_values);
					$data['descs'] = $_descs;
				break;

				case 'textsize':
					$textsizes = array();
					for($i=7; $i<=50; $i++) {
						$textsizes[] = $i;
					}
					$data['values'] = $textsizes;
					$data['descs'] = $textsizes;
				break;

				case 'ra_textsize':
					$values = array(6, 7, 8, 9, 10, 11, 12);
					$data['values'] = $values;
					$data['descs'] = $values;
				break;

				case 'pp_position':
					$data['values'] = array('address', 'stamp');
					$data['descs'] = array();
					foreach ($data['values'] as $v) {
						$data['descs'][] = getLL('kota_ko_labels_pp_position_'.$v);
					}
					break;
			}
		break; // ko_labels


		case 'ko_crm_projects':
			switch($column) {
				case 'status_ids':
					$res = db_select_data('ko_crm_status', "WHERE 1=1", 'id, title');
					foreach ($res as $status) {
						if (!$status) continue;
						$data['values'][] = $status['id'];
						$data['descs'][] = $status['title'];
					}
				break;
				case 'title':
					$projects = db_select_data('ko_crm_projects', "WHERE 1=1", 'id, title');
					$data['values'][0] = "0";
					$data['descs'][0] = "";
					foreach ($projects as $project) {
						$data['values'][] = $project['id'];
						$data['descs'][] = $project['title'];
					}
					break;
			}
		break;


		case 'ko_crm_contacts':
			switch($column) {
				case 'project_id':
					if (!isset($access['crm'])) ko_get_access('crm');
					$data['values'][] = '';
					$data['descs'][] = '';
					$res = db_select_data('ko_crm_projects', "WHERE 1=1", 'id, number, title', 'ORDER BY `number` ASC, `title` ASC');
					foreach ($res as $project) {
						if (!$project) continue;
						// need at least access level 2 to add a new contact entry to a project
						if ($access['crm'][$project['id']] < 2 && $access['crm']['ALL'] < 2) continue;
						$data['values'][] = $project['id'];
						$data['descs'][] = trim($project['number'].' '.$project['title']);
					}
				break;
			}
		break;


		case 'ko_log':
			switch ($column) {
				case 'user_id':
					ko_get_logins($logins);
					foreach ($logins as $login) {
						$data['descs'][] = $login['login'];
						$data['values'][] = $login['id'];
					}
				break;
			}
		break;

		case 'ko_taxonomy_terms':
			switch($column) {
				case 'parent':
					$terms = db_select_data('ko_taxonomy_terms', "WHERE name != '' AND parent = 0 ORDER BY `name` ASC");

					$data['values'][] = 0;
					$data['descs'][] = ' - Ohne Obergruppe - ';

					foreach ($terms as $term) {
						$data['values'][] = $term['id'];
						$data['descs'][] = $term['name'];
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
function kota_submit_multiedit($rights_level='', $log_type="", &$changes = null, $save_as_new = false) {
	global $KOTA, $mysql_pass;
	global $access;

	$notifier = koNotifier::Instance();

	list($table, $columns, $ids, $hash) = explode("@", $_POST["id"]);
	if(!$table || !$columns || $ids == "" || strlen($hash) != 32) $notifier->addError(58);
	$query = $log_message = array();

	//new entry
	$new_entry = FALSE;
	$new_id = 0;
	if($ids == 0 || $save_as_new) {
		$new_entry = TRUE;

		$new_data = array('id' => 'NULL');
		//Add creation date if set in KOTA
		if($KOTA[$table]['_special_cols']['crdate']) $new_data[$KOTA[$table]['_special_cols']['crdate']] = date('Y-m-d H:i:s');
		//Add creation user if set in KOTA
		if($KOTA[$table]['_special_cols']['cruser']) $new_data[$KOTA[$table]['_special_cols']['cruser']] = $_SESSION['ses_userid'];
		//Add lastchange date if set in KOTA
		if($KOTA[$table]['_special_cols']['lastchange']) $new_data[$KOTA[$table]['_special_cols']['lastchange']] = date('Y-m-d H:i:s');
		//Add lastchange_user if set in KOTA
		if($KOTA[$table]['_special_cols']['lastchange_user']) $new_data[$KOTA[$table]['_special_cols']['lastchange_user']] = $_SESSION['ses_userid'];

		$new_id = $ids = db_insert_data($table, $new_data);
		$rights = '';  //Access rights to add new entries have to be checked in index.php before calling kota_submit_multiedit()

		$GLOBALS['insertedIds'][$table][] = $new_id;
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
	$lastchange_query = '';
	//Add lastchange date if set in KOTA
	if($KOTA[$table]['_special_cols']['lastchange']) $lastchange_query .= ", `" . $KOTA[$table]['_special_cols']['lastchange'] . "` = NOW() ";
	//Add lastchange_user if set in KOTA
	if($KOTA[$table]['_special_cols']['lastchange_user']) $lastchange_query .= ", `" . $KOTA[$table]['_special_cols']['lastchange_user'] . "` = '" . $_SESSION['ses_userid'] . "' ";

	//Übergebene Werte
	$koi = $_POST["koi"][$table];

	//See, whether forAll has been checked
	if($koi["doForAll"]) $doForAll = TRUE;
	else $doForAll = FALSE;

	//add checkboxes. If they are deselected, then they are not in koi
	foreach(explode(",", $ids) as $id) {
		foreach(explode(",", $columns) as $kota_col_name) {
			$kota_col = $KOTA[$table][$kota_col_name];
			if(($kota_col["form"]["type"] == "checkbox" || $kota_col["form"]["type"] == "switch") && !isset($koi[$kota_col_name][($new_entry?0:$id)])) {
				$koi[$kota_col_name][($new_entry?0:$id)] = '0';
			}
		}//foreach(columns as kota_col_name)
	}//foreach(ids as id)

	$newEntries = array();

	//Alle POST-Werte durchgehen
	foreach($koi as $col => $values) {
		if($col == "forAll" || $col == "doForAll") continue;

		$col = format_userinput($col, "js");

		//only allow values set in KOTA
		if(!isset($KOTA[$table][$col])) continue;

		if (!$KOTA[$table][$col]['form']['ignore_test']) {
			$test["columns"][] = $col;
		}

		foreach($values as $id => $value) {
			//If "dontsave" is set, then don't store the value here but add column to test, so check will work. Also call 'post' if any
			if($KOTA[$table][$col]['form']['dontsave']) {
				//Call post
				if($KOTA[$table][$col]['post']) {
					if(substr($KOTA[$table][$col]['post'], 0, 4) == 'FCN:') {
						$fcn = substr($KOTA[$table][$col]['post'], 4);
						if(function_exists($fcn)) {
							eval("$fcn(\$table, \$col, (\$new_entry?\$new_id:\$id), \$value, \$new_entry);");
						}
					}
				}
				continue;
			}
			//Save for all
			if($doForAll) {
				$value = $koi[$col]["forAll"];
			}
			//ID und Berechtigung
			$id = format_userinput($id, "uint");
			if($rights != "" && !$rights[$id]) continue;

			//Wert formatieren
			$log = "";
			$process_data = array($col => $value);
			kota_process_data($table, $process_data, "post", $log, ($new_entry?$new_id:$id), $new_entry);
			$value = $process_data[$col];

			if($col == "terms") {
				// special processing for taxonomies
				$terms = explode(",", $value);
				$old_terms = ko_taxonomy_get_terms_by_node($id, $table);
				ko_taxonomy_clear_terms_on_node($table, (int) $id);
				ko_taxonomy_attach_terms_to_node($terms, $table, (int) $id);
				$log_message[$id] .= "terms: " . json_encode($old_terms) . " --> " . $value;
			} else {
				//Log-Message vorbereiten:
				if (!$log && substr($col, 0, 6) != "MODULE") {
					$old_v = db_select_data($table, "WHERE `id` = '$id'", '`' . $col . '`', '', '', TRUE);
					$old_value = $old_v[$col];
					if ($old_value != $value) {
						$log_message[$id] .= "$col: " . $old_value . " --> $value, ";
					}
				} else {
					$log_message[$id] .= $log;
				}

				//Query aufbauen, aber noch nicht ausführen
				if (substr($col, 0, 9) == 'MODULEgrp' && FALSE === strpos($col, ':')) $db_col = 'groups';
				else $db_col = $col;
				if ($db_col && substr($db_col, -7) != '_DELETE') { // ignore fields which contain information about deletion of FILES
					$query[] = "UPDATE $table SET `$db_col` = '" . mysqli_real_escape_string(db_get_link(), $value) . "' $lastchange_query WHERE `id` = '" . ($new_entry ? $new_id : $id) . "'";
					$changes[$table][($new_entry ? $new_id : $id)][$db_col] = $value;
				}
			}

			if ($id != "") $test["ids"][] = $id;
			$newEntries[$id][$col] = $value;
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
					kota_process_data($table, $process_data, "post", $log, $id, $new_entry);
				}
			}
		}
	}

	//Auf file-uploads testen
	foreach(explode(",", $columns) as $col) {
		if($KOTA[$table][$col]["form"]["type"] == "file") {
			if($new_entry) $ids = 0;
			foreach(explode(",", $ids) as $id) {
				if($doForAll) {
					// because move_uploaded_file wont let us duplicate files in $_FILES, we need to copy them.
					$file = $_FILES["koi"]["tmp_name"][$table][$col]['forAll'];
					$file_new = $file."id_".$id;
					copy($file, $file_new);
					$_FILES["koi"]["tmp_name"][$table][$col][$id] = $file_new;
					$_FILES["koi"]["name"][$table][$col][$id] = $_FILES["koi"]["name"][$table][$col]['forAll'];
					$_FILES["koi"]["size"][$table][$col][$id] = $_FILES["koi"]["size"][$table][$col]['forAll'];
					$_FILES["koi"]["type"][$table][$col][$id] = $_FILES["koi"]["type"][$table][$col]['forAll'];
					$doCopy = TRUE;
				} else {
					$doCopy = FALSE;
				}

				//only save newly submitted files
				if($_FILES["koi"]["tmp_name"][$table][$col][$id]) {
					$data = array("table" => $table, "col" => $col, "id" => $id);
					kota_save_file($value, $data, $new_id, $doCopy);
					$query[] = "UPDATE $table SET `$col` = '".str_replace("'", "\'", $value)."' $lastchange_query WHERE `id` = '".($new_entry?$new_id:$id)."'";
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

	if($new_entry) {
		if($save_as_new) {
			// try to get the ids right in case of save_as_new
			if(count($test['ids']) > 1) {
				$test['ids'] = array_filter($test['ids']);
			}
		} else {
			$test["ids"] = array(0);
		}
	}

	if(empty($test["columns"]) && empty($test["ids"])) {
		$test_string = $mysql_pass.$table.str_replace(',', ':', $col).$id;
	} else {
		$test_string = $mysql_pass.$table.implode(":", $test["columns"]).implode(":", $test["ids"]);
	}
	if(md5(md5($test_string)) != $hash && $col != "terms") {
		$notifier->addError(59);
	}

	// Mandatory fields
	$mandatoryOk = TRUE;
	foreach ($newEntries as $id => $newEntry) {
		$mandatoryOk = $mandatoryOk && kota_check_mandatory_fields($table, $id, $newEntry);
	}
	if (!$mandatoryOk) {
		$notifier->addError(60);
	}

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
	if($do_save && !$notifier->hasNotification(58) && !$notifier->hasNotification(59) && !$notifier->hasNotification(60)) {
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
		foreach($query as $q) mysqli_query(db_get_link(), $q);
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
 *
 * @param string|array $ids indexed array with ids; or CSV
 * @param string $columns to be changed column
 * @param array $old reservation with unchanged values
 * @param array $changes new submitted changes to be saved
 * @return int error code
 */
function kota_presave_ko_reservation($ids, $columns, $old, $changes) {
	global $BASE_PATH;

	require_once($BASE_PATH.'reservation/inc/reservation.inc');

	if(!is_array($ids)) $ids = explode(',', $ids);

	$all_ok = TRUE;
	$timerange_to_check = [];
	foreach($ids as $id) {
		$datum1 = isset($changes['ko_reservation'][$id]['startdatum']) ? $changes['ko_reservation'][$id]['startdatum'] : $old[$id]['startdatum'];
		$datum2 = isset($changes['ko_reservation'][$id]['enddatum']) ? $changes['ko_reservation'][$id]['enddatum'] : $old[$id]['enddatum'];
		$zeit1 = isset($changes['ko_reservation'][$id]['startzeit']) ? $changes['ko_reservation'][$id]['startzeit'] : $old[$id]['startzeit'];
		$zeit2 = isset($changes['ko_reservation'][$id]['endzeit']) ? $changes['ko_reservation'][$id]['endzeit'] : $old[$id]['endzeit'];
		$item_id = isset($changes['ko_reservation'][$id]['item_id']) ? $changes['ko_reservation'][$id]['item_id'] : $old[$id]['item_id'];
		$ok = ko_res_check_double($item_id, $datum1, $datum2, $zeit1, $zeit2, $error_txt, $id);
		if(!$ok) $all_ok = FALSE;

		if($columns == "item_id") {
			// check not yet saved reservations for conflicts
			$times_item_is_used = array_count_values(array_column($changes['ko_reservation'], 'item_id'))[$item_id];
			if($times_item_is_used > 1) {
				if($changes['ko_reservation'][$id]['item_id'] == $item_id) {
					$timerange_to_check[] = [
						"start" => ($datum1 . " " . $zeit1),
						"end" => ($datum2 . " " . $zeit2),
					];
				}
			}
		}
	}

	foreach ($timerange_to_check as $key => $range) {
		$r1s = $range['start'];
		$r1e = $range['end'];

		foreach ($timerange_to_check as $key2 => $range2) {
			if ($key != $key2) {
				$r2s = $range2['start'];
				$r2e = $range2['end'];

				if (
					$r1s >= $r2s && $r1s <= $r2e ||
					$r1e >= $r2s && $r1e <= $r2e ||
					$r2s >= $r1s && $r2s <= $r1e ||
					$r2e >= $r1s && $r2e <= $r1e
				) {
					$all_ok = FALSE;
					break;
				}
			}
		}
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
}//kota_presave_ko_event()




function kota_post_ko_event($ids, $columns, $old, $do_save) {
	if(!is_array($ids)) $ids = explode(',', $ids);
	if(!is_array($columns)) $columns = explode(',', $columns);

	//Write slug (used for lpc_kool_events)
	if(in_array('title', $columns)) {
		foreach($ids as $id) {
			ko_daten_set_event_slug($id);
		}
	}
}//kota_post_ko_event()



function kota_post_ko_reservation($ids, $columns, $old, $do_save) {
	if(!is_array($ids)) $ids = explode(',', $ids);
	if(!is_array($columns)) $columns = explode(',', $columns);

	//Update linked_items for new object
	if(in_array('item_id', $columns)) {
		foreach($ids as $id) {
			$res = db_select_data('ko_reservation', "WHERE `id` = '$id'", '*', '', '', TRUE);
			if($res['item_id']) {
				$resitem = db_select_data('ko_resitem', "WHERE `id` = '".$res['item_id']."'", '*', '', '', TRUE);
				db_update_data('ko_reservation', "WHERE `id` = '$id'", array('linked_items' => $resitem['linked_items']));
			}
		}
	}
}





/**
 * This method is called after the submission of a form via iframe
 *
 * @param $module
 * @param $action
 */
function kota_post_async_form_submit($module, $action) {
	if (!koNotifier::Instance()->hasErrors()) {
		?><?php
	}
}




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

	// Update group counts (also for linked groups)
	$groupModification = false;
	foreach ($columns as $column) {
		if (substr($column, 0, 9) == 'MODULEgrp') {
			$groupModification = TRUE;
		}
	}
	$groupModification = $groupModification || in_array('groups', $columns);
	if ($groupModification) {
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

			// Update group counts
			$groupsAdded = array_diff($ng, $og);
			foreach ($groupsAdded as $groupAdded) {
				$g = format_userinput(ko_groups_decode($groupAdded, 'group_id'), 'uint');
				if ($g == '' || $g == 0) continue;
				$group = db_select_data('ko_groups', "where id = " . $g, 'maxcount, count_role', '', '', TRUE, TRUE);
				if ($group === null) continue;
				if($group['maxcount'] > 0) {
					ko_update_group_count($g, $group['count_role']);
				}
			}
			$groupsRemoved = array_diff($og, $ng);
			foreach ($groupsRemoved as $groupRemoved) {
				$g = format_userinput(ko_groups_decode($groupRemoved, 'group_id'), 'uint');
				if ($g == '' || $g == 0) continue;
				$group = db_select_data('ko_groups', "where id = " . $g, 'maxcount, count_role', '', '', TRUE, TRUE);
				if ($group === null) continue;
				if($group['maxcount'] > 0) {
					ko_update_group_count($g, $group['count_role']);
				}
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
					ko_update_group_count($g, $group['count_role']);
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

	// Update groups history
	foreach($ids as $id) {
		//ko_create_groups_snapshot($id, NULL, NULL, TRUE);
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
	global $all_groups, $BASE_PATH;

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
			include_once($BASE_PATH.'groups/inc/groups.inc');
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
 * Save File-Uploads into data-dir my_images/
 *
 * @param Array $value of data returned
 * @param Array $data to find specific uploaded file
 * @param int $new_id set new id in filename
 * @param bool $copy if true, we use copy instead of move_uploaded_file
 * @return bool
 */
function kota_save_file(&$value, $data, $new_id=0, $copy=FALSE) {
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

	if($copy) {
		$ret = copy($tmp, $dest);
	} else {
		$ret = move_uploaded_file($tmp, $dest);
	}
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



function ko_multiedit_tracking_group(&$value, $data) {
	$tgs = db_select_data('ko_tracking_groups', 'WHERE 1=1');
	$tg_id = FALSE;
	foreach ($tgs as $id => $tg) {
		if ($tg['name'] == $value) {
			$tg_id = $id;
		}
	}
	if($tg_id !== FALSE) {
		$value = $tg_id;
	} else if(trim($value) != '') {
		$newSerie = format_userinput($value, 'text');
		$value = db_insert_data('ko_tracking_groups', array('name' => $newSerie));
		ko_log("new_tracking_serie", "$value: $newSerie");
	} else {
		$value = '';
	}
}//ko_multiedit_tracking_group()



function ko_multiedit_resitem_group(&$value, $data) {
	$gs = db_select_data('ko_resgruppen', 'WHERE 1=1');
	$gId = FALSE;
	foreach ($gs as $id => $g) {
		if ($g['name'] == $value) {
			$gId = $id;
		}
	}
	if($gId !== FALSE) {
		$value = $gId;
	} else if(trim($value) != '') {
		$allowed = (ko_get_access_all("res_admin") >= 4);
		if ($allowed) {
			$newGroup = format_userinput($value, 'text');
			$value = db_insert_data('ko_resgruppen', array('name' => $newGroup));
			ko_log("new_resgroup", "$value: $newGroup");
		}
	} else {
		$value = '';
	}
}//ko_multiedit_resitem_group()



function kota_mailing_check_unique_alias(&$value, $data) {

	if($value == '') return FALSE;
	//Enforce lowercase aliases
	$value = strtolower($value);

	//Check for disallowed aliases
	if(ko_mailing_check_disallowed_alias_patterns($value)) {
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
	$current = db_select_data('ko_leute', "WHERE `smallgroups` LIKE '%$kgid:$role%' AND `deleted` = '0'".ko_get_leute_hidden_sql(), '*');
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
	//Delete all remaining entries, if they are not selected anymore
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

function kota_listview_html(&$value, $data) {
	global $BASE_PATH;

	// first make sure that $value contains clean html
	$value = trim($value);
	if (strpos($value, "\n") !== FALSE) {
		$value = '<p>'.str_replace("\n", '</p><p>', $value).'</p>';
	}

	require_once($BASE_PATH . 'inc/class.html2text.php');

	$html2Text = new html2text($value);
	$value = preg_replace("/\n+/", '<br>', trim($html2Text->get_text()));
}//kota_listview_html()

function kota_html_to_text(&$value, $data) {
	global $ko_path;

	// first make sure that $value contains clean html
	$value = trim($value);
	if (strpos($value, "\n") !== FALSE && strpos($value, '<p') === FALSE) {
		$value = '<p>'.str_replace("\n", '</p><p>', $value).'</p>';
	}

	require_once($ko_path . 'inc/class.html2text.php');

	$html2Text = new html2text($value);
	$value = trim($html2Text->get_text());
}//kota_html_to_text()

function kota_sanitize_html(&$value, $data) {
	// make sure that $value contains clean html
	$value = trim($value);
	if (strpos($value, "\n") !== FALSE) {
		$value = '<p>'.str_replace("\n", '</p><p>', $value).'</p>';
	}
}//kota_sanitize_html()

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


function kota_listview_dayofweek(&$value, $data) {
	$days = array(
		2 => strftime('%A', mktime(1,1,1, 1, 1, 2018)),  //Monday
		3 => strftime('%A', mktime(1,1,1, 1, 2, 2018)),
		4 => strftime('%A', mktime(1,1,1, 1, 3, 2018)),
		5 => strftime('%A', mktime(1,1,1, 1, 4, 2018)),
		6 => strftime('%A', mktime(1,1,1, 1, 5, 2018)),
		7 => strftime('%A', mktime(1,1,1, 1, 6, 2018)),
		1 => strftime('%A', mktime(1,1,1, 1, 7, 2018)),  //Sunday
	);
	$value = $days[$value];
}//kota_listview_dayofweek()


function kota_listview_datecol(&$value, $data, $right=FALSE) {
	global $DATETIME, $KOTA;

	$newValue = array();
	foreach(explode(',', $value) as $dateValue) {
		if(!$dateValue) continue;

		if($dateValue == '0000-00-00' || $dateValue == '0000-00-00 00:00:00') {
			$newValue[] = '';
		} else {
			$key = $KOTA[$data['table']][$data['col']]['list_options'];
			if(!$key) $key = 'ddmy';
			$newValue[] = strftime($DATETIME[$key], strtotime($dateValue));
		}
	}

	$value = implode(', ', $newValue);
	if($right) {
		$value = '<span style="float: right; padding-right: 4px;">'.$value.'</span>';
	}
}//kota_listview_datecol()


function kota_listview_datecol_right(&$value, $data) {
	kota_listview_datecol($value, $data, TRUE);
}


function kota_listview_datetimecol(&$value, $data) {
	global $DATETIME, $KOTA;

	if($value == '0000-00-00' || $value == '0000-00-00 00:00:00') {
		$value = '';
	} else {
		$key = $KOTA[$data['table']][$data['col']]['list_options'];
		if(!$key) $key = 'ddmy';
		$value = strftime($DATETIME[$key].' %H:%M:%S', strtotime($value));
	}
}//kota_listview_datetimecol()


function kota_listview_datespan(&$value, $data) {
	global $DATETIME, $KOTA;

	$d = $data['dataset'];
	$value = '';

	$start = '0000-00-00';
	if (array_key_exists('start', $d)) $start = $d['start'];
	else if (array_key_exists('startdatum', $d)) $start = $d['startdatum'];
	else if (array_key_exists('startdate', $d)) $start = $d['startdate'];

	$stop = '0000-00-00';
	if (array_key_exists('stop', $d)) $stop = $d['stop'];
	else if (array_key_exists('enddatum', $d)) $stop = $d['enddatum'];
	else if (array_key_exists('enddate', $d)) $stop = $d['enddate'];
	else if (array_key_exists('stopdatum', $d)) $stop = $d['stopdatum'];
	else if (array_key_exists('stopdate', $d)) $stop = $d['stopdate'];

	$format = $KOTA[$data['table']][$data['col']]['list_options'];
	if(!$format) $format = 'dmY';

	if($start != '0000-00-00') $value = strftime($DATETIME[$format], strtotime($start));
	if($stop != '0000-00-00' && $stop != $start) $value .= ' - '.strftime($DATETIME[$format], strtotime($stop));
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


function kota_listview_people(&$value, $data, $log = '', $orig_data ='', $link=FALSE) {
	global $ko_path, $KOTA;

	$col = $KOTA[$data['table']][$data['col']]['list_col'];
	if(!$col) $col = $data["col"];

	$id = $orig_data[$col];
	if(!$id) $id = $data['dataset'][$col];
	$ids = explode(",", $id);
	$a_value = array();
	$persons = db_select_data('ko_leute', "WHERE `id` IN ('".implode("','", $ids)."')");
	foreach($persons as $p) {
		//Mark deleted/hidden persons but still show them
		$pre = $post = '';
		if ($p['deleted'] == 1) {
			$pre = '<span class="text-deleted" title="'.getLL('leute_labels_deleted').'">';
			$post = '</span>';
		} else if ($p['hidden'] == 1) {
			$pre = '<span class="text-hidden" title="'.getLL('leute_labels_hidden').'">';
			$post = '</span>';
		}

		//Add link
		if($link && ko_module_installed('leute')) {
			$pre .= '<a href="'.$ko_path.'leute/index.php?action=set_idfilter&id='.intval($p['id']).'">';
			$post .= '</a>';
		}
		$peopleFields = $KOTA[$data['table']][$data['col']]['list_options'];
		$lastnameFirst = $KOTA[$data['table']][$data['col']]['lastnameFirst'];
		if($peopleFields) {
			$avals = array();
			foreach(explode(',', $peopleFields) as $peopleField) {
				$peopleField = trim($peopleField);
				if(!$peopleField) continue;
				$avals[] = $p[$peopleField];
			}
			$aval  = trim(implode(' ', $avals));
		} else {
			$aval  = trim($p['firm'].($p['department'] ? ' ('.$p['department'].')' : ''));
			if($lastnameFirst) {
				$aval .= ' '.trim($p['nachname'].' '.$p['vorname']);
			} else {
				$aval .= ' '.trim($p['vorname'].' '.$p['nachname']);
			}
		}
    $a_value[] = $pre.trim($aval).$post;
	}
	$value = implode(", ", $a_value);
}//kota_listview_people()


function kota_listview_firm(&$value, $data, $link=FALSE) {
	global $ko_path;

	$id = $data["dataset"][$data["col"]];
	$ids = explode(",", $id);
	$a_value = array();
	if(sizeof($ids) > 0) $persons = db_select_data('ko_leute', "WHERE `id` IN ('".implode("','", $ids)."')");
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


function kota_listview_people_link(&$value, $data, $log, $orig_data, $link = TRUE) {
	kota_listview_people($value, $data, $log, $orig_data, $link);
}//kota_listview_people_link()

function kota_listview_telegram_token(&$value, $data) {
	$value = ko_create_telegram_link($data['id'], true);
}

/**
 * If person hasn't yet a telegram_id in ko_leute,
 * a link to send mail/sms with telegram-link to specific person will be set
 *
 * @param int $value telegram_id
 * @param array $data person
 */
function kota_listview_telegram_registration(&$value, $data) {
	global $MODULES;

	if($value > 0) {
		$value = getLL("yes");
	} else {
		if (ko_get_leute_email($data['dataset'], $email)) {
			$link[] = "<a href='#' onclick=\"c=confirm('".sprintf(getLL('telegram_send_registration_link'), 'E-Mail')."'); if (c) {sendReq('../leute/inc/ajax.php', 'action,mode,id,sesid', 'sendtelegramlink,email,".$data['dataset']['id'].",".session_id()."', do_element); return false;}\">E-Mail</a>";
		}

		if (in_array('sms', $MODULES) && ko_get_leute_mobile($data['dataset'], $mobile)) {
			$link[] = "<a href='#' onclick=\"c=confirm('".sprintf(getLL('telegram_send_registration_link'), 'SMS') ."'); if (c) {sendReq('../leute/inc/ajax.php', 'action,mode,id,sesid', 'sendtelegramlink,sms,".$data['dataset']['id'].",".session_id()."', do_element); return false;}\">SMS</a>";
		}

		if (count($link) > 0) {
			$value = sprintf(getLL("telegram_send_registration_via"), implode(" / ", $link));
		} else {
			$value = getLL("no");
		}
	}
}


function kota_listview_father(&$value, $data, $log, $orig_data, $link=FALSE) {
	if($value) {
		kota_listview_people_link($value, $data, $log, $orig_data, $link);
	} else if($data['dataset']['name_vater']) {
		$value = $data['dataset']['name_vater'];
	} else {
		$value = '';
	}
}//kota_listview_father()

function kota_listview_father_link(&$value, $data, $log, $orig_data) {
	kota_listview_father($value, $data, $log, $orig_data, TRUE);
}

function kota_listview_mother(&$value, $data, $log, $orig_data, $link=FALSE) {
	if($value) {
		kota_listview_people_link($value, $data, $log, $orig_data, $link);
	} else if($data['dataset']['name_mutter']) {
		$value = $data['dataset']['name_mutter'];
	} else {
		$value = '';
	}
}//kota_listview_mother()

function kota_listview_mother_link(&$value, $data, $log, $orig_data) {
	kota_listview_mother($value, $data, $log, $orig_data, TRUE);
}



function kota_listview_login(&$value, $data) {
	$login = db_select_data("ko_admin", "WHERE `id` ='$value'", "login", "", "", TRUE);
	if($login['login']) $value = $login["login"];
}//kota_listview_login()



function kota_listview_ko_log_request_data(&$value, $data) {
	$value = '<span '.ko_get_tooltip_code('<pre>'.$value.'</pre>', 'left').' >'. strlen($value).'</span>';
}


function kota_listview_login_status(&$value, $data) {
	global $ko_path;

	$entry = $data['dataset'];
	//disable/enable (not for root, ko_guest or logged in login
	if($entry['id'] != ko_get_guest_id() && $entry['id'] != ko_get_root_id() && $entry['id'] != $_SESSION['ses_userid']) {
		if($entry["disabled"]) {
			$html = '<input type="image" src="'.$ko_path.'images/icon_login_disable.png" onclick="sendReq(\'../admin/inc/ajax.php\', \'action,mode,id,sesid\', \'ablelogin,enabled,'.$entry['id'].','.session_id().'\', do_element); return false;" />';
		} else {
			$html = '<input type="image" src="'.$ko_path.'images/icon_login_enable.png" onclick="sendReq(\'../admin/inc/ajax.php\', \'action,mode,id,sesid\', \'ablelogin,disabled,'.$entry['id'].','.session_id().'\', do_element); return false;" />';
		}
	} else {
		$html = '&nbsp;';
	}
	$value = $html;
}//kota_listview_login_status

function kota_listview_admingroups4login(&$value, $data) {
	if(!isset($data['id'])) {
		$id = $value;
		$admingroupIds = $id;
	} else {
		$id = intval($data['id']);
		$entry = $data['dataset'];
		$admingroupIds = trim($entry['admingroups']);
	}

	if(!$id) return;

	$admingroups = [];
	if ($admingroupIds) {
		$admingroups = db_select_data('ko_admingroups', "WHERE `id` IN (" . $admingroupIds . ")");
	}
	$r = array();
	foreach($admingroups as $admingroup) {
		$r[] = $admingroup['name'];
	}
	$value = implode(', ', $r);
}//kota_listview_admingroups4login()

function kota_listview_person4login(&$value, $data) {
	global $ko_path;

	$entry = $data['dataset'];
	if($entry['leute_id'] < 1) {
		if($entry['email']) {
			$value = '('.ko_html($entry['email']).')';
		} else {
			$value = "-";
		}
	} else {
		ko_get_person_by_id($entry["leute_id"], $p, TRUE);
		$value = $p["vorname"]." ".$p["nachname"];
		if($entry['email']) $value .= ' ('.ko_html($entry['email']).')';
		if($p['deleted']) {
			$pre = '<span style="text-decoration: line-through;" title="'.getLL('leute_labels_deleted').'">';
			$post = '</span>';
		} else if($p['hidden']) {
			$pre = '<span style="color: #888;" title="'.getLL('leute_log_hide').'">';
			$post = '</span>';
		} else {
			$pre = $post = '';
		}
		//Add link to person
		$value = '<a href="'.$ko_path.'leute/index.php?action=set_idfilter&amp;id='.$p['id'].'">'.$pre.$value.$post.'</a>';
	}
}//kota_listview_admingroups4login()

function kota_listview_longtext25(&$value, $data) {
	$value = '<span title="'.ko_html($value).'">'.substr($value, 0, 25).(strlen($value)>25?"..":"").'</span>';
}//kota_listview_longtext25()


function kota_no_fcn_listview_description($value, $length=25) {
	$short = strlen($value) <= $length ? $value : substr($value, 0, $length);
	$shortened = strlen($short) < strlen($value);
	$value = '<span ' . ($shortened ? ko_get_tooltip_code($value) : '') . ' >'. $short . ($shortened ? ' ...' : '') . '</span>';
	return $value;
}//kota_no_fcn_listview_description()


function kota_listview_ll(&$value, $data) {
	$prefix = "kota_".$data["table"]."_".$data["col"]."_";
	$value = $value != "" ? getLL($prefix.$value) : "";
}//kota_listview_ll()


function kota_listview_ll_list(&$value, $data) {
	$prefix = "kota_".$data["table"]."_".$data["col"]."_";
	$ds = array();
	foreach (explode(',', $value) as $v) {
		if ($d = getLL($prefix.$v)) $ds[] = $d;
	}
	$value = implode(', ', $ds);
}//kota_listview_ll_list()


function kota_listview_eventgroup_name(&$value, $data) {
	$row = db_select_data('ko_eventgruppen', "WHERE `id` = '$value'", 'name', '', '', TRUE);
	$value = $row['name'] ? $row['name'] : '';
}//kota_listview_eventgroup_name()



function kota_listview_events_by_eventgroup(&$value, $data, &$log, $orig_data) {
	global $DATETIME, $access;

	if($orig_data['id']) $event = $orig_data;
	else $event = $data['dataset'];

	$col = $data['col'];
	if(substr($col, 0, 9) == 'calendar_') {
		$calID = format_userinput(substr($col, 9), 'int');
		if($calID === '' || $calID == -1) return FALSE;
		if($access['daten']['ALL'] < 1 && $access['daten']['cal'.$calID] < 1) return FALSE;

		$eventGroups = db_select_data('ko_eventgruppen', "WHERE `calendar_id` = '$calID'");
		if(sizeof($eventGroups) == 0) return FALSE;

		$events = db_select_data('ko_event', "WHERE `eventgruppen_id` IN (".implode(',', array_keys($eventGroups)).") AND ((`startdatum` >= '".$event['startdatum']."' AND `startdatum` <= '".$event['enddatum']."') OR (`enddatum` >= '".$event['startdatum']."' AND `enddatum` <= '".$event['enddatum']."'))", '*', 'ORDER BY `startdatum` ASC, `startzeit` ASC');
	}
	else if(substr($col, 0, 11) == 'eventgroup_') {
		$egID = format_userinput(substr($col, 11), 'int');
		if(!$egID) return FALSE;
		if($access['daten']['ALL'] < 1 && $access['daten'][$egID] < 1) return FALSE;

		$events = db_select_data('ko_event', "WHERE `eventgruppen_id` = '$egID' AND (`startdatum` <= '".$event['startdatum']."' AND `enddatum` >= '".$event['enddatum']."')", '*', 'ORDER BY `startdatum` ASC, `startzeit` ASC');
	}

	$output = array();
	if(sizeof($events) > 0) {
		foreach($events as $event) {
			$title = $event['title'] ? $event['title'] : $event['kommentar'];
			$date = strftime($DATETIME['dmy'], strtotime($event['startdatum']));
			if($event['enddatum'] != $event['startdatum']) $date .= ' - '.strftime($DATETIME['dmy'], strtotime($event['enddatum']));
			$output[] = $title.' ('.$date.')';
		}
		$value = implode(', ', $output);
	}
}



function kota_listview_ko_event_rooms_used_in(&$value, $data, &$log, $orig_data) {
	$room = $data['dataset'];

	$numEvents = db_get_count('ko_event', 'id', "AND `room` = '".$room['id']."'");
	$numEventgroups = db_get_count('ko_eventgruppen', 'id', "AND `room` = '".$room['id']."'");

	$value = '';
	$link = '/daten/index.php?action=all_events&kota_filter=room:'.$room['id'];
	if($numEvents > 0) $value .= '<a href="'.$link.'">'.$numEvents.' '.getLL('daten_events').'</a>';
	if($numEventgroups > 0) $value .= ($value != '' ? '<br />' : '').$numEventgroups.' '.getLL('daten_groups_list_title');

	return $value;
}//kota_listview_ko_event_rooms_used_in()



function kota_map_leute_daten(&$value, $data, &$log, $orig_data) {
	$v = map_leute_daten('', $data['col'], $orig_data);
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


function kota_listview_salutation_formal(&$value, $data, $first=TRUE) {
	if($first === NULL) $first = TRUE;

	$p = $data['dataset'];

	$gender = $p['geschlecht'];
	if(!$gender && $p['anrede'] == 'Herr') $gender = 'm';
	if(!$gender && $p['anrede'] == 'Frau') $gender = 'w';

	if(!$gender) {
		$r = getLL('leute_salutation_formal_');
	} else {
		$selector = $gender;

		if (in_array($p['zivilstand'], array('married', 'widowed'))) $selector .= '_married';
		else $selector .= '_unmarried';

		//Check for minor
		if($p['geburtsdatum'] != '0000-00-00' && ((int)substr($p['geburtsdatum'], 0, 4) + 18) > (int)date('Y')) {
			$r = getLL('leute_salutation_informal_'.$selector).' '.$p['vorname'];
		} else {
			$r = getLL('leute_salutation_formal_'.$selector).' '.$p['nachname'];
		}
	}

	if($first) {
		$value = $r;
	} else {
		$value = strtolower(substr($r, 0, 1)).substr($r, 1);
	}
}//my_leute_column_map()


function kota_listview_salutation_informal(&$value, $data, $first=TRUE) {
	if($first === NULL) $first = TRUE;

	$p = $data['dataset'];

	$gender = $p['geschlecht'];
	if(!$gender && $p['anrede'] == 'Herr') $gender = 'm';
	if(!$gender && $p['anrede'] == 'Frau') $gender = 'w';

	if(!$gender) {
		$r = getLL('leute_salutation_informal_');
	} else {
		$selector = $gender;

		if (in_array($p['zivilstand'], array('married', 'widowed'))) $selector .= '_married';
		else $selector .= '_unmarried';
		$r = getLL('leute_salutation_informal_'.$selector).' '.$p['vorname'];
	}

	if($first) {
		$value = $r;
	} else {
		$value = strtolower(substr($r, 0, 1)).substr($r, 1);
	}
}//my_leute_column_map()


function kota_xls_title_household(&$value, $data, &$log, &$orig_data) {
	kota_listview_title_household($value, $data, $log, $orig_data, '', '', "\n");
}


function kota_listview_title_household(&$value, $data, &$log, &$orig_data, $dummy1 = null, $dummy2 = null, $delimiter='<br>') {
	if (!isset($GLOBALS['kOOL']['families'])) ko_get_familien($GLOBALS['kOOL']['families']);
	$fields = array('anrede', 'vorname', 'nachname');
	ko_get_person_by_id($data['id'], $person, TRUE);
	$es = array();
	$pidsByFamid = array($person['famid'] => array());
	if ($person['famid']) {
		$es = db_select_data("ko_leute", "WHERE `famid` = '".$person["famid"]."' AND `famfunction` IN ('husband', 'wife') AND `deleted` = '0'".ko_get_leute_hidden_sql(), "*");
		$pidsByFamid[$person['famid']] = $es ? ko_array_column($es, 'id') : [];
	}
	$es[$person['id']] = $person;
	if(!in_array($person['id'], $pidsByFamid[$person['famid']])) $pidsByFamid[$person['famid']][] = $person['id'];
	$doneFam = array();
	list($addToExport, $isFam) = ko_leute_process_person_for_export($person, $es, $doneFam, $pidsByFamid, $GLOBALS['kOOL']['families'], $fields, 'Fam2', 1);
	$values = array();
	foreach ($fields as $f) if ($person[$f]) $values[] = $person[$f];
	$value = implode($delimiter, $values);
}


function kota_listview_reservation_event_id(&$value, $data) {
	global $EVENT_TOOLTIP_FIELDS, $DATETIME, $access;

	$id = $data['id'];
	$value = '';
	if (!$id) return;

	$event = NULL;
	// different fetching of event depending on whether we in context of ko_reservation or ko_reservation_mod
	if ($data['table'] == 'ko_reservation_mod') {
		ko_get_events($events, " AND `ko_event`.`id` = {$data['dataset']['_event_id']}");
		if ($events) $event = end($events);
	} else {
		ko_get_events($events, " AND `reservationen` REGEXP '(^|,){$id}(,|$)'");
		if ($events) $event = end($events);
	}

	if ($event) {
		if (!isset($access['daten'])) ko_get_access('daten');
		if ($access['daten']['ALL'] < 1 && $access['daten'][$event['eventgruppen_id']] < 1) return;

		// This code block is copied from the file daten/inc/ajax.inc -> jsongetevents
		$tooltip_res = ko_get_userpref($_SESSION['ses_userid'], 'daten_show_res_in_tooltip');
		$monthly_title = ko_get_userpref($_SESSION['ses_userid'], 'daten_monthly_title');

		//Set title according to setting
		$fake_eg = array('name' => $event['eventgruppen_name']);
		$event_title = ko_daten_get_event_title($event, $fake_eg, $monthly_title);
		$title = $event_title['text'];

		//Format time for tooltip
		if($event['startzeit'] == '00:00:00' && $event['endzeit'] == '00:00:00') $time = getLL('time_all_day');
		else $time = substr($event['startzeit'], 0, -3).' - '.substr($event['endzeit'], 0, -3);

		$comment = $event['kommentar'] ? nl2br($event['kommentar']) : '';

		$mapping = ['room' => $event["room"]];
		ko_include_kota(['ko_event']);
		kota_process_data("ko_event", $mapping, "list");
		$room = $mapping['room'];

		$tooltip = '';

		//Tooltip-Text
		$tooltip .= '<p class="title">'.($event['title'] ? $event['title'].' ('.$event['eventgruppen_name'].')' : $event['eventgruppen_name']).'</p>';
		$tooltip .= '<p class="datetime">'.strftime($DATETIME['ddmy'], strtotime($event['startdatum']));
		if($event['startdatum'] != $event['enddatum']) $tooltip .= ' - '.strftime($DATETIME['ddmy'], strtotime($event['enddatum']));
		$tooltip .= '<br />'.getLL('kota_listview_ko_reservation_startzeit').': '.$time.'</p>';
		if($comment) $tooltip .= '<p class="comment">'.$comment.'</p>';
		if($room) $tooltip .= '<p class="room">'.getLL('daten_location').': '.$room.'</p>';
		//Add custom tooltip fields if defined
		if(is_array($EVENT_TOOLTIP_FIELDS)) {
			foreach($EVENT_TOOLTIP_FIELDS as $field) {
				if(!isset($event[$field])) continue;
				$tooltip .= $event[$field] ? '<br /><br /><b>'.getLL('kota_ko_event_'.$field).'</b>: '.nl2br($event[$field]) : '';
			}
		}

		//Add reservations in tooltip
		if($tooltip_res) {
			$ids = explode(',', $event['reservationen']);
			foreach($ids as $k => $v) {
				if(!$v) unset($ids[$k]);
			}
			if(sizeof($ids) > 0) {
				//Get reservation infos
				$res = db_select_data('ko_reservation AS r LEFT JOIN ko_resitem AS i ON r.item_id = i.id', "WHERE r.id IN ('".implode("','", $ids)."')", 'i.name AS resitem_name, r.startzeit, r.endzeit, r.zweck', '', '', FALSE, TRUE);
				$tooltip .= '<p class="res_title">'.getLL('res_list_title').':</p><div class="event_res">';
				foreach($res as $r) {
					//Format time
					if($r['startzeit'] == '00:00:00' && $r['endzeit'] == '00:00:00') {
						$time = getLL('time_all_day');
					} else {
						$time = substr($r['startzeit'], 0, -3);
						if($r['endzeit'] != '00:00:00') $time .= ' - '.substr($r['endzeit'], 0, -3);
					}
					$tooltip .= '- '.$r['resitem_name'].': '.$time.'<br />';
				}
				$tooltip .= '</div>';
			}
		}

		//Add Groupsubscription info in tooltip
		if($event['gs_gid']) {
			$gid = format_userinput(ko_groups_decode($event['gs_gid'], 'group_id'), 'uint');
			$group = db_select_data('ko_groups', "WHERE `id` = '$gid'", '*', '', '', TRUE);
			if($group['id'] && $group['id'] == $gid) {
				$tooltip .= '<p class="res_title"><b>'.getLL('leute_groupsubscriptions_title').': </b>';
				$tooltip .= $group['count'];
				if($group['maxcount'] > 0) $tooltip .= ' / '.$group['maxcount'];
				if($group['count_role']) {
					$role = db_select_data('ko_grouproles', "WHERE `id` = '".$group['count_role']."'", '*', '', '', TRUE);
					if($role['id'] && $role['id'] == $group['count_role']) {
						$tooltip .= ' ('.$role['name'].')';
					}
				}
				$tooltip .= '</p>';

				//Find open subscriptions (ko_leute_mod)
				$gs_mod = db_select_data('ko_leute_mod', "WHERE `_group_id` LIKE 'g".$gid."%'", '_id');
				if(sizeof($gs_mod) > 0) {
					$tooltip .= '<b>'.getLL('fm_mod_open_group').':</b> '.sizeof($gs_mod);
				}

				$tooltip .= '<br />';
			}
		}
		if ($access['daten']['ALL'] < 2 && $access['daten'][$event['eventgruppen_id']] < 2) {
			$edit = '';
		} else {
			$edit = "&nbsp;<a href=\"../daten/index.php?action=edit_termin&id={$event['id']}\" title=\"".getLL('list_label_edit_entry')."\"><i class=\"fa fa-edit\"></i></a>";
		}

		$value = trim("<span ".ko_get_tooltip_code($tooltip).">{$title}{$edit}</span>");
	}
}//kota_listview_reservation_event_id()




function kota_listview_event_reservations_xls(&$value, $data) {
	kota_listview_event_reservations($value, $data, TRUE);
}


/**
 * Show number of reservations for a single event in list view. Tooltip shows single reservation's details
 */
function kota_listview_event_reservations(&$value, $data, $xls=FALSE) {
	global $ko_menu_akt;
	$ids = explode(',', $value);
	foreach($ids as $k => $v) {
		if(!$v) unset($ids[$k]);
	}
	if(sizeof($ids) > 0) {
		//Get reservation infos
		$res = db_select_data('ko_reservation AS r LEFT JOIN ko_resitem AS i ON r.item_id = i.id', "WHERE r.id IN ('".implode("','", $ids)."')", 'i.name AS resitem_name, r.startzeit, r.endzeit, r.zweck', '', '', FALSE, TRUE);
		$txt = '';
		$objectNames = array();
		foreach($res as $r) {
			//Add resitem
			$objectNames[] = $r['resitem_name'];

			//Format time
			if ($r['startzeit'] == '00:00:00' && $r['endzeit'] == '00:00:00') {
				$time = getLL('time_all_day');
			} else {
				$time = substr($r['startzeit'], 0, -3);
				if ($r['endzeit'] != '00:00:00') $time .= ' - ' . substr($r['endzeit'], 0, -3);
			}

			if ($ko_menu_akt == "ical") {
				$txt .= "- " . $r['resitem_name'] . " (" . $time .")<br />";
			} else {
				$txt .= '<b>- ' . $r['resitem_name'] . '</b><br />';
				$txt .= $time . '<br />';
				//Add purpose of reservation
				if ($r['zweck']) $txt .= strtr(trim($r['zweck']), ["\n" => "<br />", "\r" => "", "\t" => "", "'" => "\'"]) . '<br />';
				$txt .= '<br />';
			}
		}
		if($txt != '') {
			//Add title
			if ($ko_menu_akt == "ical") {
				$value = htmlspecialchars("<br />" . $txt, ENT_COMPAT | ENT_HTML401, 'ISO-8859-1');
			} else {
				$txt = getLL('daten_list_reservations_title') . ':<br /><br />' . htmlspecialchars($txt, ENT_COMPAT | ENT_HTML401, 'ISO-8859-1');
				$label = (sizeof($ids) > 2 && !$xls) ? sizeof($ids) : implode(', ', $objectNames);
				$value = '<a href="#" onclick="return false;" ' . ko_get_tooltip_code($txt) . '>' . $label . '</a>';
			}
		} else {
			$value = '';
		}
	} else {
		$value = '';
	}
}//kota_listview_event_reservations()



function kota_pdf_event_reservations(&$value, $data) {
	$ids = explode(',', $value);
	foreach($ids as $k => $v) {
		if(!$v) unset($ids[$k]);
	}
	if(sizeof($ids) > 0) {
		//Get reservation infos
		$res = db_select_data('ko_reservation AS r LEFT JOIN ko_resitem AS i ON r.item_id = i.id', "WHERE r.id IN ('".implode("','", $ids)."')", 'i.name AS resitem_name, r.startzeit, r.endzeit, r.zweck', '', '', FALSE, TRUE);
		$lines = array();
		foreach($res as $r) {
			$line = '';
			//Add resitem
			$line .= $r['resitem_name'];
			//Format time
			if($r['startzeit'] == '00:00:00' && $r['endzeit'] == '00:00:00') {
				$time = getLL('time_all_day');
			} else {
				$time = substr($r['startzeit'], 0, -3);
				if($r['endzeit'] != '00:00:00') $time .= ' - '.substr($r['endzeit'], 0, -3);
			}
			$line .= ": {$time}";
			//Add purpose of reservation
			if($r['zweck']) $line .= " (".trim($r['zweck']).")";
			$lines[] = $line;
		}
		$value = implode('<br />', $lines);
	} else {
		$value = '';
	}
}



function kota_listview_event_mod_res(&$value, $data) {
	global $DATETIME;

	$me = $data['dataset'];

	$resids = array();
	foreach(explode(',', $value) as $resid) {
		$resid = intval($resid);
		if(!$resid) continue;
		$resids[] = $resid;
	}
	if(sizeof($resids) > 0) ko_get_reservationen($res, "AND ko_reservation.id IN (".implode(',', $resids).")");
	$modres = array();
	foreach($res as $r) {
		$tt = strftime($DATETIME['dmY'], strtotime($r['startdatum'])).' '.substr($r['startzeit'], 0, -3);
		$tt .= ' - '.strftime($DATETIME['dmY'], strtotime($r['enddatum'])).' '.substr($r['endzeit'], 0, -3);
		$modres[] = '<a href="#" onclick="return false;" '.ko_get_tooltip_code($tt).'>'.$r['item_name'].'</a>';
	}

	$newitems = array();
	if($me['resitems']) {
		ko_get_resitems($resitems, '', "WHERE ko_resitem.id IN (".implode(',', explode(',', $me['resitems'])).")");
		foreach($resitems as $ri) {
			$tt = strftime($DATETIME['dmY'], strtotime($me['res_startdatum'])).' '.substr($me['res_startzeit'], 0, -3);
			$tt .= ' - '.strftime($DATETIME['dmY'], strtotime($me['res_enddatum'])).' '.substr($me['res_endzeit'], 0, -3);
			$newitems[] = '<a href="#" onclick="return false;" '.ko_get_tooltip_code($tt).'>'.$ri['name'].'</a>';
		}
	}

	$value = '';
	if($modres) $value .= getLL('daten_mod_list_old').': '.implode(', ', $modres).'<br />';
	if($newitems) $value .= getLL('daten_mod_list_new').': '.implode(', ', $newitems);
}//kota_listivew_event_mod_res()



function kota_listview_rota_schedule(&$value, $data) {
	$event = db_select_data('ko_event', "WHERE `id` = '".$data['id']."'", '*', '', '', TRUE);
	$col = $data['col'];
	list($temp, $tid) = explode('_', $col);
	if($event['rota'] != 1) return;

	if(ko_rota_team_is_in_event($tid, $event['id'])) {
		if(ko_rota_is_scheduling_disabled($event['id'], $tid)) {
			$value = getLL('rota_status_closed');
		} else {
			$scheduled = ko_rota_get_schedule_by_event_team($event, $tid);
			if($scheduled['schedule'] != '') $value = implode(', ', ko_rota_schedulled_text($scheduled['schedule']));
		}
	} else {
		$value = '-';
	}
}//kota_listview_rota_schedule()


function kota_filter_rota_schedule(&$value, $data) {
	$value = implode(', ',ko_rota_schedulled_text($value));
}

function kota_filter_form_rota_schedule($table, $col) {
	global $KOTA;

	if(substr($col, 0, 9) != 'rotateam_') return FALSE;
	$teamID = intval(substr($col, 9));
	if(!$teamID) return FALSE;

	$members = ko_rota_get_team_members($teamID);
	$data = ['-1' => ''];
	foreach($members['groups'] as $group) {
		$data['g'.$group['id']] = '[' . $group['name'] .']';
	}
	foreach($members['people'] as $person) {
		$data[$person['id']] = $person['vorname'] . ' ' . $person['nachname'];
	}

	$KOTA[$table][$col]['filter']['data'] = $data;
}//kota_filter_form_rota_schedule()


function kota_listview_rota_teams(&$value, $data) {
	$teams = explode(",", $value);

	foreach($teams as $team) {
		if (is_numeric($team)) {
			$ids[] = $team;
		} else {
			// manual entry
			$values[] = "\"" . $team ."\"";
		}
	}

	if (count($ids) > 0) {
		$where = "WHERE `id` IN (". implode(",", $ids).")";
		$db_team = db_select_data('ko_rota_teams', $where, 'name', 'ORDER BY sort ASC', '', FALSE, TRUE);
		foreach($db_team as $entry) {
			$values[] = $entry['name'];
		}

		if (!ko_get_setting('rota_manual_ordering')) {
			asort($values);
		}
	}

	$value = implode("<br />", $values);

}



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
	global $access;

	if (!is_array($access['leute'])) ko_get_access('leute');

	$g = $data['dataset'];
	if ($access['leute']['ALL'] >= 1) {
		$num = db_get_count('ko_leute', 'id', "AND `groups` REGEXP 'g".$data['id']."' AND deleted = '0' AND `hidden` = '0' ");
	} else if ($access['leute']['MAX'] < 1) {
		$num = 0;
	} else {
		$num = db_get_count('ko_leute', 'id', "AND `groups` REGEXP 'g".$data['id']."' AND deleted = '0' AND `hidden` = '0' AND `id` in ('".implode("','", array_keys($access['leute']))."')");
	}

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
	if($num > 0) {
		$value = $num;
	} else if($g['type'] == 1 || $g['type'] === 'x') {
		$link = '/groups/index.php?action=new_group&pid='.$g['id'];
		$value = '<a href="'.$link.'"><i class="fa fa-plus"></i></a>';
	} else {
		$value = '';
	}
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


function kota_listview_ko_groups_placeholder(&$value, $data) {
	$subgroups_count = db_get_count('ko_groups', 'id', "AND `pid` = '".$data['dataset']['id']."'");
	if($data["dataset"]['type'] == 0 && $subgroups_count > 0) {
		$value = "<a href='https://kool.help/module/gruppen#platzhalter-gruppen' target='_blank'><i class=\"fa fa-exclamation-triangle\" style=\"color:grey;\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"".getLL("kota_listview_ko_groups_type_help")."\"></i></a>";
	} else if($value == 1) {
		$value = "x";
	} else {
		$value = "";
	}
}


function kota_listview_ko_groups_mailing_mod_role(&$value, $data) {
	$where = "WHERE id = '" . (int) $value ."'";
	$role = db_select_data("ko_grouproles", $where, "*", "", "LIMIT 1", TRUE, TRUE);
	$value = $role['name'];
}

function kota_listview_ko_groups_mailing_rectype(&$value, $data) {
	$value = getLL("kota_ko_leute_rectype_" . $value);
}

function kota_listview_ko_groups_crm_project_id(&$value, $data) {
	$where = "WHERE id = '" . $value . "'";
	$project = db_select_data("ko_crm_projects", $where, "*", "", "LIMIT 1", TRUE, TRUE);
	$value = $project['number'] . " " . $project['title'];
}

function kota_listview_ko_groups_linked_group(&$value, $data) {
	$where = "WHERE id = '" . $value ."'";
	$group = db_select_data("ko_groups", $where, "*", "", "LIMIT 1", TRUE, TRUE);
	$value = $group['name'];
}


function kota_listview_groups_datafields(&$value, $data) {
	if($value == '') return;

	$gdfs = db_select_data('ko_groups_datafields', "WHERE `id` IN (".$value.")");
	$labels = array();
	foreach($gdfs as $gdf) {
		$labels[] = $gdf['description'];
	}
	$value = implode('<br />', $labels);
}



function kota_listview_ko_taxonomy_terms(&$value, $data) {
	$allTerms = ko_taxonomy_get_terms_by_node((int)$data['id'],$data['table']);

	if(count($allTerms) > 10) {
		$htmlTerms[] = sprintf(getLL("kota_ko_taxonomy_terms_used_in_label"), count($allTerms));
	} else {
		$htmlTerms = [];
		foreach($allTerms as $term) {
			$htmlTerms[] = "<span onclick=\"sendReq('inc/ajax.php', ['sesid','action','value'], ['".session_id()."','submittaxonomysearch', ".$term['id'] ."], do_element);
		\" style=\"cursor: pointer;\" class=\"label label-info taxonomy-term__label\" title=\"".getLL('taxonomy_set_filter_for_groups')."\">" . $term['name'] . "</span>";
		}
	}

	$value = implode(" ", $htmlTerms);
}

function kota_listview_ko_taxonomy_terms_filter(&$value, $data) {
	$term = ko_taxonomy_get_term_by_id($value);
	$value = $term['name'];
}


function kota_listview_ko_leute_terms(&$value, $data) {
	global $all_groups;

	if(!empty($data['dataset'])) {
		$data = $data['dataset'];
	}

	preg_match_all('/g[0-9]{6}/m', $data['groups'], $groups, PREG_SET_ORDER, 0);

	$allTerms = [];
	foreach($groups as $group) {
		$group_id = (int) substr($group[0],1);
		$terms = (array) ko_taxonomy_get_terms_by_node((int)$group_id,"ko_groups");
		if(sizeof($terms) == 0) continue;

		//Get group path for tooltip
		$groupTitle = ko_groups_decode(ko_groups_decode(zerofill($group_id, 6), 'full_gid'), 'group_desc_full');
		foreach($terms as $term) {
			if(!isset($allTerms[$term['id']])) $allTerms[$term['id']] = $term;
			$allTerms[$term['id']]['_groups'][] = $groupTitle;
		}
	}

	if(count($allTerms) > 10) {
		//Show terms including groups in tooltip
		$termTitles = array();
		foreach($allTerms as $term) {
			$termTitles[] = '<b>'.$term['name'].'</b>: '.implode(', ', $term['_groups']);
		}

		$htmlTerms[] = '<span '.ko_get_tooltip_code(implode('<br />', $termTitles)).'>'.sprintf(getLL("kota_ko_taxonomy_terms_used_in_groups_label"), count($allTerms)).'</span>';
	}
	else {
		//Show labels and group as tooltip
		$htmlTerms = [];
		foreach($allTerms as $term) {
			$title = implode('<br />', $term['_groups']);
			$htmlTerms[] = "<span class=\"label label-info taxonomy-term__label\" ".ko_get_tooltip_code($title).">" . $term['name'] . "</span>";
		}
	}

	$value = implode(" ", $htmlTerms);
}


function kota_listview_ko_event_absence_person(&$value, $data) {
	ko_get_person_by_id($value, $person);
	$name = (empty($person['vorname']) ? $person['anrede'] : $person['vorname']) . " " . $person['nachname'];
	$value = '<a href="index.php?action=list_absence&set_person_filter='.$value.'">'.$name.'</a>';
}


function kota_listview_ko_leute_absence(&$value, $person) {
	global $BASE_PATH, $BASE_URL, $access;

	if ($access['daten']['ABSENCE'] <= 1 && ko_get_logged_in_id() != $person['id']) {
		$value = '';
		return;
	}

	require_once($BASE_PATH.'daten/inc/daten.inc');
	$absences = ko_daten_get_absence_by_leute_id($person['id']);

	foreach($absences AS $absence) {
		if($absence['to_date'] < date('Y-m-d', time())) continue;

		$absence_link = '';
		if ($access['daten']['ABSENCE'] >= 3 || ko_get_logged_in_id() == $person['id']) {
			$absence_link = "<a href=\"" . $BASE_URL . "/daten/index.php?action=edit_absence&id=" . $absence['id'] . "\" title=\"" . getLL("placeholder_absence_link") . "\"><i class=\"fa fa-calendar-plus-o\"></i></a> ";
		}

		$absence_html[] = $absence_link . prettyDateRange($absence['from_date'], $absence['to_date']) . " " . getLL("kota_ko_event_absence_type_" . $absence['type']);
	}

	$value = implode("<br />", $absence_html);
}


/**
 * Create a list of absences for mailing
 *
 * @param array $person
 * @return string
 */
function kota_mailing_ko_leute_absence($person) {
	global $BASE_PATH, $BASE_URL;
	require_once($BASE_PATH . 'daten/inc/daten.inc');
	$absences = ko_daten_get_absence_by_leute_id($person['id']);
	foreach ($absences AS $absence) {
		if ($absence['to_date'] < date('Y-m-d', time())) continue;
		if (db_get_count("ko_admin", "id", "AND leute_id = " . $person['id']) > 0) {
			$absence_link = $BASE_URL . "/daten/index.php?action=edit_absence&id=" . $absence['id'];
		} else {
			$absence_link = $BASE_URL . "/index.php?action=edit_absence&id=" . $absence['id'];
		}
		$absence_html[] = substr(sqldatetime2datum($absence['from_date']), 0, 10) . "-" . substr(sqldatetime2datum($absence['to_date']), 0, 10) . " <a href='" . $absence_link . "'>" . getLL("kota_ko_event_absence_type_" . $absence['type']) . "</a>";
	}

	if (empty($absence_html)) {
		if (db_get_count("ko_admin", "id", "AND leute_id = " . $person['id']) > 0) {
			$absence_html[] = " <a href='" . $BASE_URL . "/daten/index.php?action=new_absence'>" . getLL('placeholder_absence_link') . "</a>";
		} else {
			$absence_html[] = " <a href='" . $BASE_URL . "/index.php?action=new_absence'>" . getLL('placeholder_absence_link') . "</a>";
		}
	}

	return implode("<br />", $absence_html);
}

function kota_listview_ko_event_absences(&$value, $data) {
	global $access;
	if($access['daten']['ABSENCE'] == 0) return FALSE;

	ko_get_event_by_id($data['id'], $event);
	$absences = ko_daten_get_absence_by_date($event['startdatum'], $event['enddatum']);

	$leute_ids = [];
	if(substr($data['col'],0, 16) == "absences_filter_") {
		$filter = substr($data['col'], 16);
		$where = "WHERE id = '" . $filter . "'";
		$filterset = db_select_data("ko_userprefs", $where, "*", "", "LIMIT 1", TRUE, TRUE);
		$filter = unserialize($filterset["value"]);
		apply_leute_filter($filter, $leute_where);
		$leute_ids = db_select_data("ko_leute", "WHERE 1=1 " . $leute_where, "id");
	}

	$absenceList = [];
	foreach($absences AS $absence) {
		if($access['daten']['ABSENCE'] == 1 && $absence['leute_id'] != ko_get_logged_in_id()) continue;
		if(!empty($filter) && empty($leute_ids[$absence['leute_id']])) continue;
		$tooltip = "<h3>" . getLL("kota_ko_event_absence_type_" . $absence['type']) . "</h3>" .
			prettyDateRange($absence['from_date'], $absence['to_date']) .
			(!empty($absence['description']) ? "<br />" . $absence['description'] : "");

		$absenceList[] = "<a href='index.php?action=list_absence&set_person_filter=".$absence['leute_id']."' data-toggle='tooltip' title='' data-html='true' data-original-title='".$tooltip."'>".$absence['name']."</a>";
	}

	$value = implode("<br>", $absenceList);
	return TRUE;
}

function kota_listview_ko_event_terms(&$value, $data) {
	if (!empty($data['dataset']['terms'])) {
		$allTerms = $data['dataset']['terms'];
	} else {
		$allTerms = ko_taxonomy_get_terms_by_node($data['id'], $data['table']);
	}

	$htmlTerms = [];
	if (count($allTerms) > 10) {
		$termTitles = array();
		foreach ($allTerms as $term) {
			$termTitles[] = '<b>' . $term['name'] . '</b>';
		}

		$htmlTerms[] = '<span ' . ko_get_tooltip_code(implode('<br />', $termTitles)) . '>' . sprintf(getLL("kota_ko_taxonomy_terms_used_in_groups_label"), count($allTerms)) . '</span>';
	} else {
		foreach ($allTerms as $term) {
			$htmlTerms[] = "<span class=\"label label-info taxonomy-term__label\">" . $term['name'] . "</span>";
		}
	}

	$value = implode(" ", $htmlTerms);
}


function kota_listview_ko_taxonomy_terms_parents(&$value, $data) {
	$parent = db_select_data('ko_taxonomy_terms', "WHERE `id` = '".$value."'", "*", "", "LIMIT 1", TRUE);
	$value = $parent['name'];
}



/**
 * list nodes (group, person, etc.) where the term is used
 *
 * @param $value
 * @param $data
 */
function kota_listview_ko_taxonomy_terms_used_in(&$value, $data) {
	$tableValues = array();
	$where = "WHERE id = ". $data['id'];
	$usages = db_select_data('ko_taxonomy_index', $where, '*', "", "", FALSE, TRUE);

	$tables = db_select_distinct('ko_taxonomy_index', 'table');
	foreach($tables as $table) {
		$usage = array();
		foreach($usages as $u) {
			if($u['table'] == $table) $usage[] = $u;
		}
		if(sizeof($usage) == 0) continue;

		$values = array();
		switch($table) {
			case 'ko_groups':
				if(count($usage) > 10) {
					$title = '';
					$values[] = sprintf(getLL("kota_ko_taxonomy_terms_used_in_label_ko_groups"), count($usage));
				} else {
					foreach($usage as $node) {
						$title = getLL('groups_list_title');
						$group = db_select_data($table, "WHERE id = '" . zerofill($node['node_id'],6) . "'", "name", "", "", TRUE, TRUE);
						$values[] = $group['name'];
					}
				}
			break;
			case 'ko_eventgruppen':
				if(count($usage) > 10) {
					$title = '';
					$values[] = sprintf(getLL("kota_ko_taxonomy_terms_used_in_label_ko_eventgruppen"), count($usage));
				} else {
					foreach($usage as $node) {
						$title = getLL('daten_groups_list_title');
						$group = db_select_data($table, "WHERE id = '" . $node['node_id'] . "'", "name", "", "", TRUE, TRUE);
						$values[] = $group['name'];
					}
				}
			break;
			case 'ko_event':
				$title = '';
				$values[] = sprintf(getLL("kota_ko_taxonomy_terms_used_in_label_ko_event"), count($usage));
			break;
		}
		$tableValues[] = ($title ? $title.': ' : '').implode(', ', $values);
	}

	$value = implode('<br />', $tableValues);

}//kota_listview_ko_taxonomy_terms_used_in()



function kota_listview_ko_reservation_zweck(&$value, $data) {
	$e = $data['dataset'];
	if($e['comments'] != '') {
		if($_SESSION['ses_userid'] == ko_get_guest_id()) {
			//Don't show comments for guest user
			$value = ko_html($e['zweck']);
		} else {
			$value = '<a style="cursor:pointer;"' . ko_get_tooltip_code("&lt;b&gt;".getLL('kota_ko_reservation_comments')."&lt;/b&gt;: ".preg_replace("/(\r\n)+|(\n|\r)+/", '<br />', ko_html($e['comments']))) . '>'.ko_html($e['zweck']).'</a>';
		}
	}
}//kota_listview_ko_reservation_zweck()



function kota_listview_ko_resitem_name(&$value, $data) {
	$e = $data['dataset'];
	if($e['linked_items'] != '') {
		$value = $e["name"]." (+".sizeof(explode(",", $e["linked_items"])).")";
	}
}//kota_listview_ko_resitem_name()



function kota_listview_ko_reservation_mod_item_id(&$value, $data) {
	$e = $data['dataset'];
	//Auf Doppelbelegung testen:
	$double = FALSE;
	$double_error = '';
	if(!ko_res_check_double($e["item_id"], $e["startdatum"], $e["enddatum"], $e["startzeit"], $e["endzeit"], $error_txt)) {
		$double = TRUE;
		$double_error = $error_txt;
	} else {
		$double = FALSE;
	}

	if ($value) {
		$item_name = ko_get_resitem_name($value);
	} else {
		$item_name = '';
	}

	if(!$double) {  //Falls keine Doppelbelegung vorliegt
		$value = ko_html($item_name);
	} else {  //Bei Doppelbelegung Meldung anzeigen
		$value  = '<a href="#" '.ko_get_tooltip_code('<b>'.getLL("res_collision_text").'</b><br><br>'.ko_html2($double_error)).'>';
		$value .= "<b> ! ".ko_html($item_name)." ! </b></a>";;
	}
}



function kota_listview_ko_reservation_mod_name(&$value, $data) {
	$e = $data['dataset'];
	if($e["telefon"] || $e["email"]) {
		$value = '<a href="#" '.ko_get_tooltip_code(format_email(ko_html2($e["email"]))."<br>".ko_html2($e["telefon"])).'>'.ko_html($e["name"]).'</a>';
	} else {
		$value = ko_html($e["name"]);
	}
}

/**
 * Make Telephone Numbers "Click-to-Call" (as described in RFC3966)
 * @param &$value phone-number
 */
function kota_listview_tel(&$value) {
	$value = '<a href="tel:'.$value.'">'.$value.'</a>';
}


function kota_listview_ko_leute_groups(&$value, $data) {
	global $access;

	$gs = $value;

	if(substr($gs, 0, 1) == "r" || substr($gs, 0, 2) == ":r") {  //Rolle
		ko_get_grouproles($role, "AND `id` = '".substr($gs, (strpos($gs, "r")+1))."'");
		$value = $role[substr($gs, (strpos($gs, "r")+1))]["name"];
	} else {  //Gruppe(n)
		if(!isset($access['groups'])) ko_get_access('groups');

		$value = array();

		foreach(explode(',', $gs) as $g) {
			$g = trim($g);
			if (!$g) continue;
			$gid = ko_groups_decode($g, 'group_id');
			if (!$gid) continue;
			$group = db_select_data('ko_groups', "WHERE `id` = {$gid}", '*', '', '', TRUE);

			if($g
				&& ($access['groups']['ALL'] > 0 || $access['groups'][$gid] > 0)
				&& (ko_get_userpref($_SESSION['ses_userid'], 'show_passed_groups') == 1 || ($group['start'] <= date('Y-m-d') && ($group['stop'] == '0000-00-00' || $group['stop'] > date('Y-m-d'))))
			) {
				$value[] = ko_groups_decode($g, 'group_desc_full');
			}
		}
		sort($value);
		$value = '<div class="row">'.implode('', array_map(function($el){return '<div class="col-sm-6">'.$el.'</div>';}, $value)).'</div>';
	}
}


function kota_xls_ko_leute_groups(&$value, $data) {
	kota_listview_ko_leute_groups($value, $data);
	$value = strip_tags(str_replace("</div><div class=\"col-sm-6\">","\n",$value));
}



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
	if(empty($data)) {
		$data['id'] = $value;
	}

	$_value = $value;
	$prefix = 'kota_ko_resitem_moderation_';
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




function kota_listview_ko_donations_account_group(&$value, $data) {
	$donation = db_select_data('ko_donations', "WHERE `id` = '".intval($data['id'])."'", '*', '', '', TRUE);
	$account = db_select_data('ko_donations_accounts', "WHERE `id` = '".intval($donation['account'])."'", '*', '', '', TRUE);
	if(!$account['accountgroup_id']) return '';

	$account_group = db_select_data('ko_donations_accountgroups', "WHERE `id` = '".intval($account['accountgroup_id'])."'", '*', '', '', TRUE);
	$value = $account_group['title'];
}


/**
 * @param string &$value
 * @param array $data
 * @param string $priority
 */
function kota_listview_ko_donations_person(&$value, $data, $priority='name') {
	global $ko_path;

	if(!isset($data['dataset'])) {
		$id = $value;
	} else {
		$id = intval($data['dataset'][$data['col']]);
	}

	$p = db_select_data('ko_leute', "WHERE `id` = '$id'", '*', '', '', TRUE);

	//Mark deleted persons but still show them
	$pre = $p['deleted'] == 1 ? '<span style="text-decoration: line-through;" title="'.getLL('leute_labels_deleted').'">' : '';
	$post = $p['deleted'] == 1 ? '</span>' : '';

	//Add link to person
	$link1 = '';
	if(ko_module_installed('leute') && $p['deleted'] != 1) {
		$link1  = '<a href="'.$ko_path.'leute/index.php?action=set_idfilter&id='.intval($p['id']).'" title="'.getLL('donations_title_pfilter').'" style="margin-right:6px;">';
		$link1 .= '<img src="'.$ko_path.'images/external_link.png" border="0" />';
		$link1 .= '</a>';
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
	global $all_groups, $SMALLGROUPS_ROLES_LEADER;

	if(!empty($data['dataset'])) {
		$g = $data['dataset'];
		$value = '<a href="index.php?action=set_kg_filter&amp;id=' . $g['id'] . '"><b>' . ko_html($g['name']) . '</b></a>';

		$show_leiter = '';
		$done = [];
		foreach ($SMALLGROUPS_ROLES_LEADER as $role) {
			foreach (explode(',', $g['role_' . $role]) as $l) {
				if (!$l) continue;
				if (in_array($l, $done)) continue;

				ko_get_person_by_id($l, $p);
				if ($p['vorname'] && $p['nachname']) $show_leiter .= $p['vorname'] . ' ' . $p['nachname'] . ', ';
				$done[] = $l;
			}
		}
		$value .= $show_leiter ? ' (' . substr($show_leiter, 0, -2) . ')' : '';
	}
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
		$value .= '&nbsp;<i class="fa fa-comment" '.ko_get_tooltip_code(getLL('daten_notify_hint').' '.$names).'></i>';
	}

	//Add icon to mark Google calendars
	if($g['type'] == 1) $value .= '&nbsp;<img src="'.$ko_path.'images/googlecal.png" border="0" title="'.getLL('daten_eventgroup_google').'" />';
	if($g['type'] == 2) $value .= '&nbsp;<sup title="'.getLL('daten_eventgroup_rota').'">('.getLL('rota_shortname').')</sup>';
	if($g['type'] == 3) {
		if (empty($g['ical_url'])) {
			$value .= '&nbsp;<i class="fa fa-rss-square ical__no_link" title="' . getLL('daten_eventgroup_ical_no_link') . '"></i>';
		} else {
			$value .= '&nbsp;<i class="fa fa-rss-square" title="' . getLL('daten_eventgroup_ical') . '"></i>';
		}
	}
}//kota_listview_eventgroup_name()




function kota_listview_file(&$value, $data) {
	global $BASE_PATH, $BASE_URL;

	if(!$value) return;
	if(!file_exists($BASE_PATH.$value)) {
		$value = '<span ' . ko_get_tooltip_code(sprintf(getLL('kota_warning_file_not_found'), $value)) . '><i class="fa fa-warning"></i></span>';
	} else {
		$ext = strtolower(substr($value, (strrpos($value, '.')+1)));
		if(file_exists($BASE_PATH.'images/mime/'.$ext.'.png')) {
			$icon = '/images/mime/'.$ext.'.png';
		} else {
			$icon = '/images/mime/_default.png';
		}
		if(substr($value, 0, 1) == '/') $value = substr($value, 1);
		$link = $BASE_URL.$value;
		$mtime = filemtime($BASE_PATH.$value);

		$value = '<a href="'.$link.'?m='.$mtime.'" target="_blank"><img src="'.$icon.'" border="0" /></a>';
	}
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



function kota_listview_crm_project_status_ids(&$value, $data) {
	global $ko_path;

	$d = $data['dataset'];

	$projectId = $d['id'];

	$output = '';
	foreach (explode(',', $d['status_ids']) as $statusId) {
		if (!$statusId) continue;
		$status = db_select_data('ko_crm_status', "WHERE `id` = '" . $statusId . "'", 'title', '', '', TRUE, TRUE);
		if (!$status) continue;
		$leuteIds = ko_get_leute_from_crm_project($projectId, $statusId);
		$output .= $status['title'] . ':&nbsp;<a href="'.$ko_path.'leute/index.php?action=set_crm_project_filter&amp;id='.$projectId.'-'.$statusId.'">'.sizeof($leuteIds).'</a><br>';
	}
	if ($output) $output = substr($output, 0, -4);
	$value = $output;
}


/**
 * Return the sum of donations received for this crm project
 *
 * @param $value
 * @param $data
 */
function kota_listview_crm_project_total_amount(&$value, $data) {
	$where = "WHERE crm_project_id = " . $data['id'];
	$project = db_select_data("ko_donations", $where, "sum(amount) AS total_amount", "", "", FALSE, TRUE);
	if($project[0]['total_amount'] > 0) {
		$value = $project[0]['total_amount'];
	} else {
		$value = '0.00';
	}
}


function kota_listview_crm_contacts_leute_ids(&$value, $data) {
	global $ko_path;
	$d = $data['dataset'];

	$contactId = $d['id'];
	$leuteIds = db_get_count('ko_crm_mapping', 'leute_id', "AND `contact_id` = '".$contactId."'");
	$value = '<a href="'.$ko_path.'leute/index.php?action=set_crm_contact_filter&amp;id='.$contactId.'" class="text-right" style="display:block;" title="'.getLL('crm_label_show_leute_ids').'">'.$leuteIds.'</a>';
}


function kota_listview_crm_contacts_leute_values(&$value, $kota_data, $log, $orig_data) {
	global $ko_path;
	$contactId = $kota_data['dataset']['id'];
	$values = array();
	$tooltip = array();

	if ($contactId) {
		$entries = db_select_data('ko_crm_mapping', "WHERE `contact_id` = '" . $contactId . "'");
		// todo: create left join for names of leute

		if(count($entries) == 0) {
			$value = "";
		} else if(count($entries) <= 2) {
			foreach ($entries as $entry) {
				$person = db_select_data('ko_leute', "WHERE `id` = '" . $entry['leute_id'] . "'");
				$values[] = '<a href="'.$ko_path.'leute/index.php?action=set_idfilter&id='.$entry["leute_id"].'" class="text-right" style="display:block;" title="'.getLL('crm_label_show_leute_ids').'">'.$person[$entry['leute_id']]["vorname"] . " " . $person[$entry['leute_id']]["nachname"] .'</a>';
			}

			$value = implode(" ", $values);

		} else if(count($entries) <= 10) {
			foreach ($entries as $entry) {
				$person = db_select_data('ko_leute', "WHERE `id` = '" . $entry['leute_id'] . "'");
				$tooltip[] = $person[$entry['leute_id']]["vorname"] . " " . $person[$entry['leute_id']]["nachname"];
			}
			$value = '<a href="'.$ko_path.'leute/index.php?action=set_crm_contact_filter&amp;id='.$contactId.'" class="text-right" style="display:block;" title="'. implode(", ", $tooltip) .'">'.count($entries).'</a>';
		} else {
			$value = '<a href="'.$ko_path.'leute/index.php?action=set_crm_contact_filter&amp;id='.$contactId.'" class="text-right" style="display:block;" title="'. getLL('crm_label_show_leute_ids') .'">'.count($entries).'</a>';
		}
	}
}



function kota_listview_crm_project_title(&$value, $data, $link=FALSE) {
	global $ko_path;

	if(!empty($data)) {
		$title = $data['dataset']['title'];
		$pre = '<a href="' . $ko_path . 'crm/index.php?action=set_projectfilter&id=' . intval($data['dataset']['id']) . '">';
		$post = '</a>';
		$value = $pre.$title.$post;
	}
}//kota_listview_crm_project_title()




function kota_listview_crm_deadline(&$value, $data, $log, $orig_data) {
	global $DATETIME;

	if(!$value || $value == '0000-00-00') {
		$v = '';
	} else {
		$v = strftime($DATETIME['ddmy'], strtotime($value));

		$target = str_replace('-', '', $value);
		$today = date('Ymd');
		if($target == $today) {
			$v .= ' ('.getLL('time_today').')';
		}
		else if($target > $today) {
			$days = 0;
			while($today < $target) {
				$days++;
				$today = str_replace('-', '', add2date(substr($today, 0, 4).'-'.substr($today, 4, 2).'-'.substr($today, 6, 2), 'day', 1, TRUE));
			}
			$v .= ' (+'.$days.' '.($days == 1 ? getLL('time_day') : getLL('time_days')).')';
		}
		else {
			$days = 0;
			while($today > $target) {
				$days++;
				$today = str_replace('-', '', add2date(substr($today, 0, 4).'-'.substr($today, 4, 2).'-'.substr($today, 6, 2), 'day', -1, TRUE));
			}
			$v .= ' (-'.$days.' '.($days == 1 ? getLL('time_day') : getLL('time_days')).')';
		}
	}
	$value = $v;
}//kota_listview_crm_deadline()




function kota_listview_checkin_links(&$value, $data) {
	global $BASE_PATH, $BASE_URL;

	$tracking = db_select_data('ko_tracking', "WHERE `id` = {$data['id']}", '*', '', '', TRUE);

	if (ko_get_setting('tracking_enable_checkin') && $tracking['enable_checkin']) {
		$lines = array(
			'0' => '<a target="_blank" href="'.$BASE_URL.'checkin?t='.$data['id'].'">'.getLL('checkin_label_link_no_printer').'</a>',
			'1' => '<a target="_blank" href="'.$BASE_URL.'checkin?t='.$data['id'].'&m=1">'.getLL('checkin_label_link_open').'</a>',
		);
		$printers = ko_get_available_google_cloud_printers();
		foreach ($printers as $printer) {
			if ($printer['google_id'] != '__google__docs') {
				$lines[$printer['google_id']] = '<a target="_blank" href="'.$BASE_URL.'checkin?t='.$data['id'].'&p='.$printer['id'].'">'.$printer['name'].' ('.substr($printer['google_id'], 0, 5).')</a>';
			}
		}
		$value = implode('<br>', $lines);

		if(ko_get_setting('qz_tray_enable')) {
			$value .= '<div class="qzCheckinLinks" data-id="'.$data['id'].'"></div>';
		}
	}
}








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
			[$avalues, $adescs, $astatus] =  kota_peopleselect($lids, $KOTA[$table][$col]['form']['sort']);
			$KOTA[$table][$col]["form"]["avalues"] = $avalues;
			$KOTA[$table][$col]["form"]["adescs"] = $adescs;
			$KOTA[$table][$col]["form"]["astatus"] = $astatus;
			$KOTA[$table][$col]['form']['avalue'] = $value;
		}
	}
}//kota_assign_values()



/**
  * processes a data array according to the rules in KOTA
	* modes can be pre, post or list
	* modes can also be a comma list of the above values, then the first available is applied, but only one
	*/
function kota_process_data($table, &$data, $modes, &$log = null, $id=0, $new_entry=FALSE, &$fullData=array()) {
	global $KOTA;

	if(!is_array($modes)) $modes = explode(",", $modes);

	$orig_data = $data;
	foreach($data as $col => $value) {

		if(!isset($KOTA[$table][$col])) continue;
		if(substr($col, -7) == '_DELETE') continue;  //File deletion will be handled below
		$x = null;
		if (sizeof($fullData)) $x = $fullData;
		else $x = $data;

		$kota_data = array("table" => $table, "col" => $col, "id" => $id, "new_entry" => $new_entry, "dataset" => &$x);

		//get first array element, if value is array
		if(is_array($value)) $value = array_shift($value);

		//Replace "wrong" version of apostroph with "normal" one
		if($value) {
			$new = str_replace(chr(145), "'", $value);
			if($new) $value = $new;
		}

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
			}
		}//foreach(explode(",", modes))
		$data[$col] = $value;
	}//foreach(data as col => value)

	//Check for file uploads. Not included in _POST[koi] so process separately
	foreach($KOTA[$table] as $col => $kdata) {
		if(substr($col, 0, 1) == '_') continue;
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

			if ($_FILES['koi']['error'][$table][$col][$id] > 0 && $_FILES['koi']['error'][$table][$col][$id] != 4) {
				koNotifier::Instance()->addTextWarning(getLL("warning_upload_error_code_" . $_FILES['koi']['error'][$table][$col][$id]));
			}

			if($tmp_file) {
				$fdata = array('table' => $table, 'col' => $col, 'id' => $files_id);
				//For new entry: Get ID of new event (next auto_increment value) to store file with correct ID
				$new_id = $id == 0 ? db_get_next_id($table) : $id;
				$orig_files = $_FILES;
				kota_save_file($value, $fdata, $new_id);
				if($id > 0) {
					db_update_data($table, "WHERE `id` = '$id'", array($col => $value));
				} else {
					ko_log('kota_file', "table: $table, id: $id, col: $col, value: $value. ".json_encode(ko_utf8_encode_assoc($orig_files)));
				}
				$data[$col] = $value;
			}
			//check for delete-checkbox for this file (only possible for edit)
			else if($data[$col.'_DELETE'][$id] == 1 || $data[$col.'_DELETE'] == 1) {
				$col_value = db_select_data($table, "WHERE `id` = '$id'", '`'.$col.'`', '', '', TRUE);
				$fdata = array('table' => $table, 'col' => $col, 'id' => $id);
				kota_delete_file($col_value[$col], $fdata);
				db_update_data($table, "WHERE `id` = '$id'", array($col => ''));
				unset($data[$col.'_DELETE']);
				$data[$col] = '';
			}
		}
	}

	if (in_array('post', $modes) || in_array('pre', $modes)) {
		//Check for checkboxes (don't show up in $data if not set (anymore))
		foreach($KOTA[$table] as $col => $v) {
			if(substr($col, 0, 1) == '_') continue;
			if(isset($data[$col])) continue;
			if($v['form']['type'] == 'checkbox' || $v['form']['type'] == 'switch') {
				$data[$col] = 0;
			}
		}
	}

	//Trim all values
	foreach($data as $k => $v) {
		$data[$k] = trim($v);
	}
}//kota_process_data()


/**
 * get the name of a specific font
 */
function kota_font_name(&$value, $data) {
	$fonts = ko_get_pdf_fonts();
	return $fonts[$value]['name'];
}




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


function kota_xls_enddate(&$value, $data) {
	$e = db_select_data($data['table'], "WHERE `id` = {$data['id']}", '*', '', '', TRUE);
	if ($e['startdatum'] == $e['enddatum']) $d = '';
	else $d = $e['enddatum'];
	$value = sql2datum($d);
}


/**
 * creates html content that gives information about content in cols defined as _special_cols in kota
 *
 * @param $value
 * @param $data
 */
function kota_pre_metadata(&$value, $data) {
	global $KOTA;

	$value = array();
	$table = $data['table'];
	$specialCols = $KOTA[$table]['_special_cols'];

	if (sizeof($specialCols) == 0) return;

	$entry = db_select_data($table, "WHERE `id` = '{$data['id']}'", '`'.implode('`,`', $specialCols).'`', '', '', TRUE, TRUE);
	if (!$entry) return;

	foreach ($specialCols as $k => $specialCol) {
		$ll = getLL('kota_'.$table.'_'.$specialCol);
		$v = $entry[$specialCol];

		if ($v) {
			if (strpos($k, 'user') !== FALSE) {
				$login = db_select_data("ko_admin", "WHERE `id` ='$v'", "login", "", "", TRUE);
				if($login['login']) $v = $login["login"];
			} else {
				$v = sql2datetime($v);
			}
		} else {
			continue;
		}

		if (!$ll) $ll = getLL('kota_specialcols_'.$k);
		$value[] = '<b>'.$ll.'</b>: ' . $v;
	}
	$value = implode('<br>', $value);
}



function kota_pre_leute_preferred_field(&$value, $data) {
	global $LEUTE_EMAIL_FIELDS, $KOTA;

	$person = $data['dataset'];
	$l = $data['col'];
	$id = $data['id'];
	$origVal = $value;

	$type = in_array($l, $LEUTE_EMAIL_FIELDS) ? 'email' : 'mobile';
	$dbValue = db_select_data('ko_leute_preferred_fields', "WHERE `type` = '{$type}' AND `lid` = '$id' AND `field` = '$l'", '*', '', '', true);

	$value = '<div class="input-group input-group-sm">';
	if (strpos($KOTA['ko_leute'][$l]['form']['params'], 'disabled') === FALSE && !$KOTA['ko_leute'][$l]['form']['readonly'] && !$KOTA['ko_leute']['_access']['readonly']) {
		$value .= '<div class="input-group-addon"><input type="checkbox" name="'.$type.'_chk_'.$l.'"'.($dbValue['id'] ? ' checked="checked"' : '').' title="'.sprintf(getLL('leute_preferred_fields_'.$type.'_chk'), getLL('kota_ko_leute_'.$l)).'" /></div>';
	} else {
		$value .= '<span class="input-group-addon"><i class="fa fa-'.($dbValue['id'] ? 'check-square-o' : 'square-o').'" title="'.sprintf(getLL('leute_preferred_fields_'.$type.'_chk'), getLL('kota_ko_leute_'.$l)).'"></i></span>';
	}

	$value .= '<input type="'.($type == 'mobile' ? 'tel' : 'email').'" name="koi[ko_leute]['.$l.']['.($id ? $id : '0').']" value="'.$origVal.'" class="input-sm form-control">';
	$value .= '</div>';
}




function kota_pre_leute_family_data(&$value, $data) {
	global $smarty, $access, $KOTA;
	global $FAMILIE_EXCLUDE, $COLS_LEUTE_UND_FAMILIE;
	global $LEUTE_NO_FAMILY, $ASYNC_FORM_TAG;

	$id = $data['id'];

	$mode = ($id == 0 ? 'neu' : 'edit');
	if ($mode == 'edit') {
		ko_get_person_by_id($data['id'], $person, TRUE);
	} else {
		$person = $data['dataset'];
	}

	//get the cols, for which this user has edit-rights (saved in allowed_cols[edit])
	$allowed_cols = ko_get_leute_admin_spalten($_SESSION['ses_userid'], 'all', ($id === 0 ? -1 : $id));
	//Familien-Daten
	if ($ASYNC_FORM_TAG == 'family_relative') {
		koFormLayoutEditor::unsetGroup($KOTA, 'ko_leute', 'general', 'family');
	} else if (!is_array($allowed_cols["view"]) || in_array("famid", $allowed_cols["view"])) {
		ko_get_familien($familien);

		$famReadonly = is_array($allowed_cols["edit"]) && !in_array("famid", $allowed_cols["edit"]);

		//Familien-Select
		$fam_sel["values"][] = "0";
		$fam_sel["descs"][] = getLL("form_leute_none");
		$fam_sel["titles"][] = getLL("form_leute_none");
		foreach($familien as $f) {
			$fam_sel["values"][] = $f["famid"];
			$fam_sel["descs"][] = $f["id"];
			$fam_sel["titles"][] = $f["detailed_id"];
		}

		//Familien-Funktion
		$ffs = kota_get_select_descs_assoc("ko_leute", "famfunction");
		foreach($ffs as $f => $ll) {
			$function_sel["values"][] = $f;
			$function_sel["descs"][] = $ll;
		}
		//Aktive Funktion selektieren
		$function_sel["sel"] = $person["famfunction"];

		//Familie Ja oder Nein
		if($person["famid"] != 0) {
			$smarty->assign("fam", true);

			//Familien-ID
			$familie = ko_get_familie($person["famid"]);
			$smarty->assign("fam_id", $familie["id"]);

			//Aktive Familie selektieren
			$fam_sel["sel"] = $person["famid"];
		}//if(person[famid] != 0)

		else {
			$smarty->assign("fam", false);
		}
		$smarty->assign("fam_sel", $fam_sel);
		$smarty->assign("famfunction", $function_sel);

		//Familien-Felder (für edit und neu)
		$familie_col_name = ko_get_family_col_name();
		$familien_cols = db_get_columns("ko_familie");
		$fc = 0;
		foreach($familien_cols as $col_) {
			$col = $col_["Field"];
			$type = $col_["Type"];
			if(!in_array($col, array_merge($FAMILIE_EXCLUDE, $COLS_LEUTE_UND_FAMILIE))) {
				$cols_familie[$fc]["desc"] = $familie_col_name[$col] ? $familie_col_name[$col] : $col;
				$cols_familie[$fc]["name"] = "input_".$col;
				if($mode == "edit" && $person["famid"] != 0) {
					$cols_familie[$fc]["value"] = $familie[$col];
				} else {
					$cols_familie[$fc]["value"] = "";
				}
				if(substr($type, 0, 4) == "enum") {
					$cols_familie[$fc]["type"] = "select";
					$ffs = db_get_enums_ll("ko_familie", $col);
					foreach($ffs as $f => $ll) {
						$cols_familie[$fc]["values"][] = $f;
						$cols_familie[$fc]["descs"][] = $ll;
					}
				} else {
					$cols_familie[$fc]["type"] = "text";
				}
				$fc++;
			}
		}//foreach(familien_cols)
		$smarty->assign("cols_familie", $cols_familie);
		if(is_array($allowed_cols["edit"]) && !in_array("famid", $allowed_cols["edit"])) $smarty->assign("fam_params", ' disabled="disabled" ');

		$family_ = ko_get_family2($person['id']);
		$family = array();
		foreach($family_ as $k => $p) {
			if ($access['leute']['ALL'] < 1 && $access['leute'][$p['id']] < 1) continue;
			$title  = $p['firm'].' '.$p['vorname'].' '.$p['nachname'];
			$title .= $p['adresse'] != '' ? ' - '.$p['adresse'] : '';
			$title .= $p['plz'] != '' || $p['ort'] != '' ? ' - '.$p['plz'].' '.$p['ort'] : '';
			$title = trim(format_userinput($title, 'js'));
			$label = trim(format_userinput($p['firm'].' '.$p['vorname'].' '.$p['nachname'], 'js'));
			$astatus =  ($p['deleted'] == 1 ? "deleted" : ($p['hidden'] == 1 ? "hidden" : "active"));
			$input = array(
				'type' => 'peoplesearch',
				'name' => 'input_'.$k,
				'avalues' => array($p['id']),
				'adescs' => array($label),
				'atitles' => array($title),
				'astatus' => array($astatus),
				'single' => TRUE,
				'show_add' => $famReadonly?FALSE:TRUE,
				'disabled' => $famReadonly?TRUE:FALSE,
				'add_class' => 'family-relative-id',
				'async_form_tag' => 'family_relative',
			);
			$family[$k] = array('id' => $p['id'], 'name' => $label, 'title' => $title, 'input' => $input);
		}
		foreach (array('father', 'mother', 'spouse') as $rel) {
			if (!array_key_exists($rel, $family)) {
				$input = array(
					'type' => 'peoplesearch',
					'name' => 'input_'.$rel,
					'avalues' => array(),
					'adescs' => array(),
					'atitles' => array(),
					'astatus' => array(),
					'single' => TRUE,
					'show_add' => $famReadonly?FALSE:TRUE,
					'disabled' => $famReadonly?TRUE:FALSE,
					'add_class' => 'family-relative-id',
					'async_form_tag' => 'family_relative',
				);
				$family[$rel] = array('input' => $input, 'absent' => TRUE);
			}
		}

		if($person['famid'] > 0) {
			$where = "WHERE famid = '" . $person['famid'] . "' AND id != '" . $person['id'] . "'";
			$householdmembers = db_select_data("ko_leute", $where);
			foreach($householdmembers AS $key => $householdmember) {
				kota_process_data("ko_leute", $householdmember, "list");
				$householdmembers[$key] = $householdmember;
			}
			$smarty->assign('householdmembers', $householdmembers);
		}

		$smarty->assign('family', $family);
		$smarty->assign('label_spouse', getLL('form_leute_family_spouse'));
		$smarty->assign('label_father', getLL('form_leute_family_father'));
		$smarty->assign('label_mother', getLL('form_leute_family_mother'));
		$smarty->assign('tpl_action', $id ? 'submit_edit_person' : 'submit_neue_person');
		$smarty->assign('help_famid', ko_get_help('leute', 'kota.ko_leute.famid'));

		if($famReadonly) $smarty->assign("fam_readonly", 1);

		$value = $smarty->fetch('ko_formular_leute_family.tpl');
	} else {
		$value = '';
	}
}



function kota_pre_leute_groups_datafields(&$value, $data) {
	global $access;

	if (!ko_module_installed("groups")) return;
	if (!isset($access['groups'])) ko_get_access('groups');

	$id = $data['id'];

	$mode = ($id == 0 ? 'neu' : 'edit');
	if ($mode == 'edit') {
		ko_get_person_by_id($data['id'], $person, TRUE);
	} else {
		$person = $data['dataset'];
	}

	ko_get_groups($groups, ko_get_groups_zwhere());

	$valid_ids = array();
	foreach ($groups as $group) {
		if ($access['groups']['ALL'] > 0 || $access['groups'][$group['id']] > 0) $valid_ids[] = $group['id'];
	}
	//Bestehende Werte einfüllen
	$do_datafields = null;
	foreach (explode(",", $person["groups"]) as $group) {
		if ($group) {
			$group_id = ko_groups_decode($group, "group_id");

			if ($groups[$group_id]['stop'] != '0000-00-00' && $groups[$group_id]['stop'] < date('Y-m-d')) {
				continue;
			}

			$group_desc = ko_groups_decode($group, "group_desc_full");
			if (in_array($group_id, $valid_ids)) {
				if ($group["datafields"]) $do_datafields[] = array_merge($groups[$group_id], array("desc_full" => $group_desc, "group_id" => $group_id));
			}
		}//if(group)
	}//foreach(person[groups])

	//Group datafields for selected groups
	$html = ko_groups_render_group_datafields($do_datafields, $id);

	$value = $html;
}

function kota_pre_leute_groups_history(&$value, $data) {
	global $access;

	$id = $data['id'];

	$mode = ($id == 0 ? 'neu' : 'edit');
	if ($mode == 'edit') {
		ko_get_person_by_id($data['id'], $person, TRUE);
	} else {
		$person = $data['dataset'];
	}

	if (!ko_module_installed("groups")) return;
	if (!isset($access['groups'])) ko_get_access('groups');
	//Groups-History
	foreach(explode(",", $person["groups"]) as $group) {
		if(!$group) continue;
		$g = ko_groups_decode($group, "group");
		$g_id = ko_groups_decode($group, "group_id");
		if($g["stop"] != "0000-00-00" && (int)str_replace("-", "", $g["stop"]) <= (int)strftime("%Y%m%d", time()) && ($access['groups']['ALL'] > 0 || $access['groups'][$g_id] > 0)) {
			$desc_full = ko_groups_decode($g_id, 'group_desc_full');
			$history_groups[$g_id] = $group;
			$history_groups_sort[$g_id] = $desc_full;
		}
	}
	asort($history_groups_sort);
	$num = 0; $history = array();
	foreach($history_groups_sort as $gid => $start) {
		$group = db_select_data('ko_groups', "WHERE `id` = '$gid'", '*', '', '', true);
		$converted_groupid = (int) $gid;
		$history_data = db_select_data('ko_groups_assignment_history', "WHERE `person_id` = {$person['id']} AND `group_id` = $converted_groupid", "*", "", "LIMIT 1", TRUE);

		$start_time = $history_data['start'];
		if ($history_data['stop'] == '0000-00-00 00:00:00') {
			$stop_time = $group['stop'];
		} else {
			$stop_time = $history_data['stop'];
		}

		$dates = ko_date_format_timespan($start_time, $stop_time);

		$tooltip = '<p class="title">' . getLL("filter_groupshistory") . '</p>' . $dates .'
			<br><b>'.getLL('groups_assignment_history_label_duration') . '</b>: ' . ko_nice_timeperiod($start_time, $stop_time) .'
			<br /><br />';

		$found_df = $datafields = FALSE;

		foreach(explode(',', $group['datafields']) as $dfid) {
			if(!$dfid) continue;
			$df = ko_get_datafield_data($gid, $dfid, $id, '', $temp1, $temp2);
			if(!$df['description']) continue;
			$found_df = TRUE;
			$dfvalue = $df['typ'] == 'checkbox' ? ($df['value'] == '1' ? getLL('yes') : getLL('no')) : ko_html($df['value']);
			$datafields .= $df['description'].': <b>'.$dfvalue.'</b><br />';
		}

		if($found_df) {
			$tooltip .= '<p class="title">' . getLL('groups_datafields_list_title') . '</p>' . $datafields;
		}

		$ttCode = ko_get_tooltip_code($tooltip);

		$history[$num] .= '<div class="col-md-6" style="padding:2px 15px;">';
		$history[$num] .= '<div class="label label-default" style="font-size:x-small;margin:3px;text-align:center;display:block;" '.$ttCode.'>';
		$history[$num] .= ko_groups_decode($history_groups[$gid], "group_desc_full");
		$history[$num] .= "</div>";
		$history[$num] .= "</div>";
		$num++;
	}

	$val = '<div class="panel panel-primary">';
	$val .= '<div class="row">';
	foreach($history as $h) {
		$val .= $h;
	}
	$val .= '</div>';
	$val .= '</div>';
	$value = $val;
}




function kota_pre_leute_MODULEkg_seit (&$value, $data) {
	global $access, $smarty;

	$person = $data['dataset'];
	$id = $data['id'];

	if(!ko_module_installed("kg") || $access['kg']['MAX'] < 2) return;

	//Dabei in Kleingruppen seit
	$grp[0]['row']['inputs'][0]["desc"] = getLL("form_leute_kg_since");
	$grp[0]['row']['inputs'][0]["type"] = "varchar";
	$grp[0]['row']['inputs'][0]["name"] = "input_kg_seit";
	$grp[0]['row']['inputs'][0]["value"] = $person["kg_seit"];
	if($access['kg']['MAX'] > 2) {
		$grp[0]['row']['inputs'][0]["params"] = '';
	} else {
		$grp[0]['row']['inputs'][0]["params"] = 'disabled="disabled"';
	}

	//Dabei als Kleingruppen-Leiter seit
	$grp[0]['row']['inputs'][1]["desc"] = getLL("form_leute_kg_leader_since");
	$grp[0]['row']['inputs'][1]["type"] = "varchar";
	$grp[0]['row']['inputs'][1]["name"] = "input_kgleiter_seit";
	$grp[0]['row']['inputs'][1]["value"] = $person["kgleiter_seit"];
	if($access['kg']['MAX'] > 2) {
		$grp[0]['row']['inputs'][1]["params"] = '';
	} else {
		$grp[0]['row']['inputs'][1]["params"] = 'disabled="disabled"';
	}

	$smarty->assign('tpl_submit_value', getLL('save'));
	$smarty->assign('tpl_groups', $grp);
	$smarty->assign('tpl_action', $id ? 'submit_edit_person' : 'submit_neue_person');
	$value = $smarty->fetch('ko_formular.tpl');
}



/**
  * returns an array of formatted values with the array-key being the description of the KOTA field
	*/
function kota_get_list($data, $table, $allowHtml=TRUE) {
	global $KOTA;

	if ($allowHtml) {
		kota_process_data($table, $data, "list,pre");
	} else {
		kota_process_data($table, $data, "pdf,xls,list,pre");
	}

	$list = array();
	foreach($data as $col => $value) {
		$ll_value = getLL("kota_".$table."_".$col);
		if(!$ll_value) continue;

		$list[$ll_value] = $allowHtml?$value:strip_tags($value);
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




function kota_apply_filter($table, $kotaFilterData=NULL) {
	global $KOTA;

	if (!is_array($kotaFilterData)) {
		$kotaFilterData = $_SESSION['kota_filter'][$table];
	}

	$kota_where = '';
	if(is_array($kotaFilterData)) {
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

		$filters_and = [];
		foreach($kotaFilterData as $col => $filters) {

			if($table == "ko_leute") {
				$tmp_leute_filter = $filters;
				unset($filters);
				$filters[0] = $tmp_leute_filter;
			}

			$filters_or = [];
			foreach($filters AS $filter) {
				if ($col && ($table == 'ko_leute' || in_array($col, $filter_cols))) {
					$type = $KOTA[$table][$col]['filter']['type'];
					if (!$type) $type = $KOTA[$table][$col]['form']['type'];

					if (substr($type, 0, 4) == 'FCN:') {
						list($temp, $fcn, $type) = explode(':', $type);
					}

					if ($type == 'jsdate') {
						$not = $filter['neg'];
						$from = $filter['from'];
						$to = $filter['to'];
						$jsdate_where = [];
						if ($from) {
							// make sure, we have a well-formatted date
							$from = date("Y-m-d", strtotime($from));
							$jsdate_where[] = "$table.$col >= '" . mysqli_real_escape_string(db_get_link(), $from) . "'";
						}
						if ($to) {
							// make sure, we have a well-formatted date
							$to = date("Y-m-d", strtotime($to));
							$jsdate_where[] = "$table.$col <= '" . mysqli_real_escape_string(db_get_link(), $to) . " 23:59:59.999'";
						}
						if (sizeof($jsdate_where) < 1) continue;
						$filters_or[] = " " . ($not ? 'NOT ' : '') . " (" . implode(" AND ", $jsdate_where) . ")";
					}
					//Time column. Only set filter if value given, don't allow to filter for empty time.
					// Empty values not allowed so filter for ONLY start or ONLY end time works.
					else if ($type == 'time') {
						if ($filter) {
							$sqlval = mysqli_real_escape_string(db_get_link(), $filter);
							if ($sqlval) $sqlval = '%' . $sqlval . '%';
							$filters_or[] = " $table.$col $not LIKE '" . $sqlval . "'";
						}
					} else {
						$not = $notEqual = '';
						if (substr($filter, 0, 1) == '!') {
							$filter = substr($filter, 1);
							$not = 'NOT';
							$notEqual = '!';
						}
						$sqlval = mysqli_real_escape_string(db_get_link(), $filter);
						//Apply custom SQL from KOTA
						if ($KOTA[$table][$col]['filter']['sql']) {
							$sql = $KOTA[$table][$col]['filter']['sql'];
							$map = ['[TABLE]' => $table, '[FIELD]' => $col, '[NOT]' => $not, '[NOTEQUAL]' => $notEqual, '[VALUE]' => $sqlval];
							$filters_or[] = " (" . strtr($sql, $map) . ")";
						} //Use special SQL for peoplsearch
						else if ($type == 'peoplesearch') {
							$filters_or[] = " $table.$col $not REGEXP '(^|,)" . $sqlval . "(,|$)'";
						}
						else if ($type == 'dynamicsearch') {
							if($col == "terms") {
								$ids = ko_taxonomy_get_nodes_by_termid($sqlval, $table);
								$filters_or[] = $table.".id $not IN ('" . implode("','",array_column($ids,"id")) . "')";
							}
						} //Select and textplus: Use exact search (without %)
						else if ($type == 'select' || $type == 'textplus') {
							$filters_or[] = " $table.$col $not LIKE '" . $sqlval . "'";
						} else if ($type == 'selectplus') {
							if($col=="room") {
								$filters_or[] = " $table.$col $not LIKE '".$sqlval."'";
							}
						} else if ($type == "switch") {
							if ($sqlval == 1) {
								$filters_or[] = " $table.$col = '1'";
							} else {
								$filters_or[] = " ($table.$col = '0' OR $table.$col = '')";
							}
						} //Default SQL: LIKE %v%
						else {
							if ($sqlval) $sqlval = '%' . $sqlval . '%';
							$filters_or[] = " $table.$col $not LIKE '" . $sqlval . "'";
						}
					}
				}
			}

			if(sizeof($filters_or) > 0) $filters_and[] = implode(" OR ", $filters_or);
		}

		if(!empty($filters_and)) {
			$kota_where = "(" . implode(") AND (", $filters_and) . ")";
		}
	}

	return $kota_where;
}//kota_apply_filter()



function kota_filter_get_warntext($table) {
	global $KOTA;

	$r = '';
	if(is_array($_SESSION['kota_filter'][$table])) {
		$is_filtered = FALSE;
		$txts = array();
		foreach($_SESSION['kota_filter'][$table] as $colname => $col) {
			if($colname != '' && $col != '') {
				$showHeadline = TRUE;
				foreach($col AS $filteritem) {
					if (substr($KOTA[$table][$colname]['filter']['list'], 0, 4) == "FCN:" && !is_array($filteritem)) {
						$fcn = substr($KOTA[$table][$colname]['filter']['list'], 4);
						if (function_exists($fcn)) {
							$ll = $filteritem;
							if($fcn == "kota_listview_ll") {
								$data = [
									"table" => $table,
									"col" => $colname,
									"dataset"=> [
										$colname => $ll,
									]
								];
								$fcn($ll, $data, [], []);
							} else {
								$fcn($ll, [], [], []);
							}
						}
					} else if (substr($KOTA[$table][$colname]['list'], 0, 4) == "FCN:" && !is_array($filteritem)) {
						$fcn = substr($KOTA[$table][$colname]['list'], 4);
						if (function_exists($fcn)) {
							$ll = $filteritem;
							if($fcn == "kota_listview_ll" || $fcn == "kota_listview_people_link") {
								$data = [
									"table" => $table,
									"col" => $colname,
									"dataset"=> [
										$colname => $ll,
									]
								];
								$fcn($ll, $data, [], []);
							} else {
								$fcn($ll, [], [], []);
							}
						}
					} else if (substr($KOTA[$table][$colname]['list'], 0, 13) == "db_get_column" && !is_array($filteritem)) {
						$ll = $filteritem;
						eval("\$ll=".str_replace('@VALUE@', addslashes($ll), $KOTA[$table][$colname]['list']).';');
					}
					else {
						$type = $KOTA[$table][$colname]['filter']['type'];
						if (!$type) $type = $KOTA[$table][$colname]['form']['type'];

						if ($type == 'checkbox') {
							$ll = $filteritem ? getLL('yes') : getLL('no');
						} else if ($type == 'jsdate') {
							$ll = ($filteritem['neg'] ? '! (' : '') . ($filteritem['from'] ? $filteritem['from'] : getLL('filter_always')) . ' - ' . ($filteritem['to'] ? $filteritem['to'] : getLL('filter_always')) . ($filteritem['neg'] ? ')' : '');
						} else {
							$ll = getLL('kota_' . $table . '_' . $colname . '_' . $filteritem);
						}
					}

					if (!$ll) $ll = $filteritem;

					$txts[] = ($showHeadline ? getLL('kota_' . $table . '_' . $colname) . ': ' : "") . ko_html(strip_tags($ll));
					$showHeadline = FALSE;
					$is_filtered = TRUE;
				}
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

	if($new !== FALSE) $new = intval($new);

	if($new === 0 && !$KOTA[$table]['_readonly'] && !$KOTA[$ptable][$col]['readonly']) {
		$kota_fields = array();
		foreach($KOTA[$table] as $k => $v) {
			if(substr($k, 0, 1) == '_' || $v['form']['type'] == 'html' || $v['form']['dontsave']) continue;
			$kota_fields[] = $k;
		}
		if (isset($KOTA[$table]['_types']['field'])) {
			$type = '';
			$useType = is_array($KOTA[$table]['_types']['types'][$type]) && $type != $KOTA[$table]['_types']['default'];
			if ($useType) {
				foreach ($kota_fields as $k => $v) {
					if (!in_array($v, $KOTA[$table]['_types']['types'][$type]['use_fields'])) {
						unset($kota_fields[$k]);
					}
				}
				foreach ($KOTA[$table]['_types']['types'][$type]['add_fields'] as $v) {
					$kota_fields[] = $v;
				}
			}
		}

		$submit_new = '<button type="button" class="btn btn-sm btn-primary form_ft_save" value="'.getLL('save').'" data-field="'.$field.'" data-table="'.$table.'" data-after="0" data-pid="'.$pid.'" data-fields="'.implode(',', $kota_fields).'">' . getLL('save') . '</button>';
		$content .= kota_ft_get_row($table, 0, $submit_new, getLL('form_ft_title_new'), $field, '', true);
	}

	if ($KOTA[$ptable][$col]['form']['sort_col']) {
		$rows = db_select_data($table, "WHERE `pid` = '$pid'");
		list($sort, $dir) = explode(' ', $KOTA[$ptable][$col]['form']['sort_col']);
		$sortBys = explode('->', $sort);
		$dir = strtolower(trim($dir));

		$addedIds = array();
		$sorted = array();
		foreach ($sortBys as $k => $col) {
			$col = strtolower(trim($col, " \t\n`"));

			foreach ($rows as $row) {
				if ((($row[$col] && !in_array($row[$col], array('0000-00-00', '00:00:00', '0000-00-00 00:00:00'))) || $k == sizeof($sortBys) - 1) && !in_array($row['id'], $addedIds)) {
					$sorted[$row[$col]][] = $row;
					$addedIds[] = $row['id'];
				}
			}
		}
		if ($dir == 'asc') ksort($sorted);
		else krsort($sorted);

		$rows = array();
		foreach ($sorted as $row_) {
			foreach ($row_ as $row) {
				$rows[$row['id']] = $row;
			}
		}

	} else {
		$rows = db_select_data($table, "WHERE `pid` = '$pid'", '*', "ORDER BY `sorting` ASC");
	}


	foreach($rows as $row) {
		$kota_fields = array();
		foreach($KOTA[$table] as $k => $v) {
			if(substr($k, 0, 1) == '_' || $v['form']['type'] == 'html' || $v['form']['dontsave']) continue;
			$kota_fields[] = $k;
		}
		if (isset($KOTA[$table]['_types']['field'])) {
			$type = $row[$KOTA[$table]['_types']['field']];

			$useType = is_array($KOTA[$table]['_types']['types'][$type]) && $type != $KOTA[$table]['_types']['default'];
			if ($useType) {
				foreach ($kota_fields as $k => $v) {
					if (!in_array($v, $KOTA[$table]['_types']['types'][$type]['use_fields'])) {
						unset($kota_fields[$k]);
					}
				}
				foreach ($KOTA[$table]['_types']['types'][$type]['add_fields'] as $v) {
					$kota_fields[] = $v;
				}
			}
		}

		$submit_edit = '<button type="button" class="btn btn-sm btn-primary form_ft_save" value="'.getLL('save').'" data-field="'.$field.'" data-table="'.$table.'" data-after="'.$new.'" data-pid="'.$pid.'" data-fields="'.implode(',', $kota_fields).'" data-id="'.$row['id'].'">' . getLL('save') . '</button>';

		$formTitle = kota_get_multititle($table, $row);

		$content .= kota_ft_get_row($table, $row['id'], $submit_edit, $formTitle, $field, $pid);

		if($new == $row['id'] && !$KOTA[$table]['_readonly'] && !$KOTA[$ptable][$col]['readonly']) {
			$kota_fields = array();
			foreach($KOTA[$table] as $k => $v) {
				if(substr($k, 0, 1) == '_' || $v['form']['type'] == 'html' || $v['form']['dontsave']) continue;
				$kota_fields[] = $k;
			}
			if (isset($KOTA[$table]['_types']['field'])) {
				$type = '';

				$useType = is_array($KOTA[$table]['_types']['types'][$type]) && $type != $KOTA[$table]['_types']['default'];
				if ($useType) {
					foreach ($kota_fields as $k => $v) {
						if (!in_array($v, $KOTA[$table]['_types']['types'][$type]['use_fields'])) {
							unset($kota_fields[$k]);
						}
					}
					foreach ($KOTA[$table]['_types']['types'][$type]['add_fields'] as $v) {
						$kota_fields[] = $v;
					}
				}
			}

			$submit_new = '<button type="button" class="btn btn-sm btn-primary form_ft_save" value="'.getLL('save').'" data-field="'.$field.'" data-table="'.$table.'" data-after="'.$new.'" data-pid="'.$pid.'" data-fields="'.implode(',', $kota_fields).'" data-id="">' . getLL('save') . '</button>';
			$content .= kota_ft_get_row($table, 0, $submit_new, getLL('form_ft_title_new'), $field, '', true);
		}
	}

	return $content;
}//kota_ft_get_content()




function kota_ft_get_row($table, $id, $submit, $title='', $field='', $pid='', $new=false) {
	global $smarty, $ko_path, $KOTA;

	//Work with local copy to not interfere with main form
	$local_smarty = clone $smarty;

	$readOnly = $noDelete = FALSE;
	$allowSorting = TRUE;
	if ($field) {
		list($ptable, $col) = explode('.', $field);
		if ($KOTA[$ptable][$col]['form']['sort_col']) {
			$allowSorting = FALSE;
		}

		$readOnly = $KOTA[$table]['_access']['readonly'] || $KOTA[$ptable][$col]['form']['readonly'];
		$noDelete = $KOTA[$table]['_access']['nodelete'] || $KOTA[$ptable][$col]['form']['nodelete'] || $readOnly;
	}

	$grp = ko_multiedit_formular($table, '', $id, '', '', TRUE, '', TRUE, 'formular', '1', $readOnly?'readonly':'');
	$local_smarty->assign('tpl_special_submit', $readOnly?' ':$submit);
	$local_smarty->assign('tpl_titel', trim($title));
	$local_smarty->assign('tpl_hide_cancel', TRUE);
	$local_smarty->assign('tpl_groups', $grp);
	$content = $local_smarty->fetch('ko_formular.tpl');

	if (preg_match('/<h3 class="ko_list_title">([^<]*)<\/h3>/', $content, $matches)) {
		$title = $matches[1];
		$content = str_replace('<h3 class="ko_list_title">'.$title.'</h3>', '', $content);
	} else {
		$title = '';
	}


	if($id > 0 && !$readOnly) {
		$delete = '<div title="'.getLL('form_ft_button_delete_title').'" class="ft-header-btn ft-header-btn-clickable form_ft_delete btn-danger" data-field="'.($new?'':$field).'" data-pid="'.$pid.'" data-id="'.$id.'"><i class="fa fa-trash"></i></div>';
		$add = '<div title="'.getLL('form_ft_button_new_title').'" class="ft-header-btn ft-header-btn-clickable form_ft_add" data-after="'.$id.'" data-field="'.($new?'':$field).'" data-pid="'.$pid.'"><i class="fa fa-plus"></i></div>';

		$moveup = '<div title="'.getLL('form_ft_button_moveup_title').'" class="ft-header-btn ft-header-btn-clickable form_ft_moveup" data-field="'.($new?'':$field).'" data-pid="'.$pid.'" data-id="'.$id.'"><i class="fa fa-angle-up"></i></div>';
		$movedown = '<div title="'.getLL('form_ft_button_movedown_title').'" class="ft-header-btn ft-header-btn-clickable form_ft_movedown" data-field="'.($new?'':$field).'" data-pid="'.$pid.'" data-id="'.$id.'"><i class="fa fa-angle-down"></i></div>';

		$editicons = '';

		if ($allowSorting) {
			$editicons .= $add;
			$editicons .= $movedown;
			$editicons .= $moveup;
		}

		if (!$noDelete) {
			$editicons .= $delete;
		}

		$editicons .= '';
	}

	$c = '<div class="panel panel-' . ($new ? 'success' : 'primary') . ' form_ft_row">';
		if ($editicons || $title) $c .= '<div class="panel-heading form_ft_header">'.$editicons.'<h4 class="panel-title">'.($title?$title:'&nbsp;').'</h4></div>';

		$c .= '<div class="panel-body form_ft_content">';
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
					if($link) $link .= '&rid=' . $role['id'];
				}
			}
			if($link) $result = '<a href="'.$link.'">'.$result.'</a>';

			//Find open subscriptions (ko_leute_mod)
			$gs_mod = db_select_data('ko_leute_mod', "WHERE `_group_id` LIKE 'g".$gid."%'", '_id');
			if(sizeof($gs_mod) > 0) {
				$result .= '<br /><b>'.getLL('fm_mod_open_group').':</b> '.sizeof($gs_mod);
			}
		}
	}
	$value = $result;
}

function kota_listview_eventgroup_color(&$value, $data, $log, $orig_data) {
	$dataset = $data['dataset'];
	$value = '<div style="display:block;height:16px;min-width:12px;background-color:#'.$dataset['eventgruppen_farbe'].' !important;"></div>';
}

function kota_listview_eventgroup_color_xls(&$value, $data, $log, $orig_data) {
	$dataset = $data['dataset'];
	$value = $dataset['eventgruppen_farbe'];
}


function kota_listview_ko_event_rooms(&$value, $data) {
	if(isset($data['dataset']['room'])) {
		$search = $data['dataset']['room'];
	} else {
		if(substr($value,0,1) == "!") {
			$search = substr($value,1);
		} else {
			$search = $value;
		}
	}
	if($search == "0") {
		$value = '';
	} else if(is_numeric($search)) {
		$where = "WHERE id = " . $search;
		$room = db_select_data("ko_event_rooms",$where,"title", "", "LIMIT 1", TRUE, TRUE);
		if(!empty($room['title'])) {
			if(substr($value,0,1) == "!") {
				$value = "!" . $room['title'];
			} else {
				$value = $room['title'];
			}
		}
	}
}




function kota_listview_ko_donations_accountgroups(&$value, $data) {
	if(isset($data['dataset']['accountgroup_id'])) {
		$search = $data['dataset']['accountgroup_id'];
	} else {
		if(substr($value,0,1) == "!") {
			$search = substr($value,1);
		} else {
			$search = $value;
		}
	}
	if($search == "0") {
		$value = '';
	} else if(is_numeric($search)) {
		$where = "WHERE `id` = '".$search."'";
		$group = db_select_data("ko_donations_accountgroups", $where, "id,title", "", "LIMIT 1", TRUE, TRUE);
		if(!empty($group['title'])) {
			if(substr($value,0,1) == "!") {
				$value = "!".$group['title'];
			} else {
				$value = $group['title'];
			}
		}
	}
}


function kota_listview_ko_mailing_mails_size(&$value, $data, $log, $orig_data) {
	if(!$value) {
		// be backwards compatible, emails before 2017-10-11 have no size set and an uncompressed body
		$result = db_select_data('ko_mailing_mails',"WHERE `id`='".$data['id']."'",'LENGTH(header)+LENGTH(body) AS size','','',true);
		$value = $result['size']+4; // +4: 2x CRLF between header and body
	}
	$value = ko_nice_size($value);
}//mailing_mails_body()





/**
 * fetches all options to be displayed as deadline in the reminder form
 *
 * @param null $key
 * @return array
 */
function kota_reminder_get_deadlines ($key = null) {
	$data = array();
	// 1, 2, 3, 6, 12, 18 h

	$data[-672] = sprintf("4 %s %s", getLL('weeks'), getLL('before'));
	$data[-504] = sprintf("3 %s %s", getLL('weeks'), getLL('before'));

	$data[-336] = sprintf("14 %s %s", getLL('days'), getLL('before'));
	$data[-312] = sprintf("13 %s %s", getLL('days'), getLL('before'));
	$data[-288] = sprintf("12 %s %s", getLL('days'), getLL('before'));
	$data[-264] = sprintf("11 %s %s", getLL('days'), getLL('before'));
	$data[-240] = sprintf("10 %s %s", getLL('days'), getLL('before'));
	$data[-216] = sprintf("9 %s %s", getLL('days'), getLL('before'));
	$data[-192] = sprintf("8 %s %s", getLL('days'), getLL('before'));
	$data[-168] = sprintf("7 %s %s", getLL('days'), getLL('before'));
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
	$data[168] = sprintf("7 %s %s", getLL('days'), getLL('after'));
	$data[192] = sprintf("8 %s %s", getLL('days'), getLL('after'));
	$data[216] = sprintf("9 %s %s", getLL('days'), getLL('after'));
	$data[240] = sprintf("10 %s %s", getLL('days'), getLL('after'));
	$data[264] = sprintf("11 %s %s", getLL('days'), getLL('after'));
	$data[288] = sprintf("12 %s %s", getLL('days'), getLL('after'));
	$data[312] = sprintf("13 %s %s", getLL('days'), getLL('after'));
	$data[336] = sprintf("14 %s %s", getLL('days'), getLL('after'));

	$data[504] = sprintf("3 %s %s", getLL('weeks'), getLL('after'));
	$data[672] = sprintf("4 %s %s", getLL('weeks'), getLL('after'));

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


function kota_form_data_crm_contacts_status_id($row) {
	global $KOTA;

	$values = array('');
	$descs = array('');
	$defaultResult = array('values' => $values, 'descs' => $descs);

	//Get projectID from DB (edit entry)
	$projectId = $row['project_id'];

	//Get value from KOTA for new entries, maybe it was set there
	if(!$projectId && $KOTA['ko_crm_contacts']['project_id']['form']['value']) {
		$projectId = intval($KOTA['ko_crm_contacts']['project_id']['form']['value']);
	}

	if (!$projectId) {
		if (!$row['id']) return $defaultResult;
		else {
			ko_get_crm_contacts($contact, " AND `id` = '".$row['id']."'", '', '', TRUE, TRUE);
			if (!$contact || !$contact['project_id']) return $defaultResult;
			$projectId = $contact['project_id'];
		}
	}
	ko_get_crm_projects($project, " AND `id` = '" . $projectId . "'", '', '', TRUE, TRUE);
	if (!$project) return $defaultResult;

	$statusIds = $project['status_ids'];
	foreach (explode(',', $statusIds) as $statusId) {

		if (!$statusId) continue;
		ko_get_crm_status($status, " AND `id` = '" . $statusId . "'", '', '', TRUE, TRUE);
		if (!$status) continue;
		$values[] = $status['id'];
		$descs[] = $status['title'];
	}

	return array('values' => $values, 'descs' => $descs);
}


function kota_pre_detailed_person_exports_instructions(&$value, $kota_data, $log, $orig_data) {
	$person = ko_get_logged_in_person();

	$html = '<div class="panel panel-default"><div class="panel-body"><table class="full-width table-striped"><thrad><tr></tr>';
	$html .= '<th style="padding-right:10px;">'.getLL('admin_detailed_person_export_label_colname').'</th>';
	$html .= '<th style="padding-right:10px;">'.getLL('admin_detailed_person_export_label_placeholder').'</th>';
	$html .= '<th>'.sprintf(getLL('admin_detailed_person_export_label_example_value'), "<i>{$person['vorname']} {$person['nachname']}</i>").'</th>';
	$html .= '</tr></thead><tbody>';

	$map = ko_word_person_array($person);

	$allCols = ko_get_leute_col_name();
	foreach($allCols as $k => $v) {
		$allCols['${address_'.strtolower($k).'}'] = $v;
	}

	ksort($map);
	$cnt = 0;
	foreach ($map as $placeholder => $value) {
		$html .= '<tr>';
		$html .= '<td style="padding-right:10px;">'.$allCols[$placeholder].'</td>';
		$html .= '<td style="padding-right:10px;">'.$placeholder.'</td>';
		$html .= '<td>'.(strlen($value)>30?(substr($value, 0, 30).'...'):$value).'</td>';
		$html .= '</tr>';
		$cnt++;
	}
	$html .= '</tbody></table></div></div>';

	$value = $html;
}


function kota_pre_crm_contacts_leute_ids(&$value, $kota_data, $log, $orig_data) {
	$id = $kota_data['dataset']['id'];
	$values = array();
	if ($id) {
		$entries = db_select_data('ko_crm_mapping', "WHERE `contact_id` = '" . $id . "'");
		foreach ($entries as $entry) {
			$values[] = $entry['leute_id'];
		}
	}
	kota_assign_values('ko_crm_contacts', array('leute_ids' => implode(',', $values)), FALSE);
}


function kota_post_crm_contacts_leute_ids($table, $col, $id, $value) {
	$contactId = $id;
	if (!$contactId) return;
	$leuteIds = explode(',', $value);
	foreach ($leuteIds as $k => $leuteId) {
		if (!$leuteId) {
			unset($leuteIds[$k]);
			continue;
		}
		if (db_get_count('ko_crm_mapping', "id", " AND `contact_id` = '" . $contactId . "' AND `leute_id` = '" . $leuteId . "'") == 0) {
			db_insert_data('ko_crm_mapping', array('contact_id' => $contactId, 'leute_id' => $leuteId));
		}
	}

	$z_where = '';
	if (sizeof($leuteIds) > 0) {
		$z_where = " AND `leute_id` NOT IN ('" . implode("','", $leuteIds) . "')";
	}
	db_delete_data('ko_crm_mapping', "WHERE `contact_id` = '" . $contactId . "'" . $z_where);
}


function resolve_sep_references($table, $col, $value, $id_col='id', $sep=',', $return_sep=', ') {
	if (!$value) return '';
	$values = explode($sep, $value);
	$es = db_select_distinct($table, $col, '', "WHERE `".$id_col."` in ('".implode(',', $values)."')");
	return implode(', ', $es);
}

/*
 * loads the value from a userpref (for new form)
 */
function kota_pre_from_userpref(&$value, $data) {
	if ($data['new_entry']) {
		$identifier = 'kota_' . $data['table'] . '_' . $data['col'];
		$value = ko_get_userpref($_SESSION['ses_userid'], $identifier);
	}
}//kota_pre_from_userpref()
/*
 * saves the value to a userpref (on submission of new form)
 */
function kota_post_to_userpref(&$value, $data) {
	if ($data['new_entry']) {
		$identifier = 'kota_' . $data['table'] . '_' . $data['col'];
		ko_save_userpref($_SESSION['ses_userid'], $identifier, $value);
	}
}//kota_post_to_userpref()

function kota_post_filter_word_xml(&$value, $data) {
	$value = strip_tags($value, '<body><p><br><ul><ol><li><b><i><span><h1><h2><h3><h4><h5><h6><sup><sub><strong><em>');
	$value = preg_replace('/&lt;!\[if[^\[]*&lt;!\[endif\]&gt;/', '', $value);
	$value = preg_replace('/&lt;!--\[if[^&]*\]&gt;/', '', $value);
	$value = preg_replace('/&lt;[a-z]:[^<]*/', '', $value);
	return $value;
}


$kotaAsyncFormCounter = 0;
/**
 * @param        $table
 * @param        $mode
 * @param int    $id
 * @param string $btnContext
 * @param string $title
 * @param string $addClass
 * @param string $minHeight
 * @param string $height
 * @param int    $specificAsyncFormCounterId Set a specific Id for HtmlId, because autoincrement doesnt work in every case
 * @return array
 */
function kota_get_async_modal_code($table, $mode, $id=0, $btnContext='success', $title='', $addClass='', $minHeight='400px', $height='fitParent', $specificAsyncFormCounterId = 0) {
	global $kotaAsyncFormCounter;

	if ($specificAsyncFormCounterId > 0) {
		$htmlId = 'auto-generated-kota-async-form-' . $specificAsyncFormCounterId ;
	} else {
		if (!$kotaAsyncFormCounter) $kotaAsyncFormCounter = 0;
		$htmlId = 'auto-generated-kota-async-form-' . ($kotaAsyncFormCounter++);
	}

	$title = $title === '' ? getLL('kota_async_form_label_btn') : $title;

	$btnHtml =
	'<button type="button" class="btn btn-'.$btnContext.' btn-sm '.$addClass.'" id="'.$htmlId.'-btn" data-target="#'.$htmlId.'" data-af-entry-id="'.$id.'" data-af-table="'.$table.'" data-af-mode="'.$mode.'" data-min-height="'.$minHeight.'" data-height="'.$height.'">
		'.$title.'
	</button>';

	$js =
	'<script>
		$("#'.$htmlId.'-btn").asyncform();
	</script>';

	return array('html' => "{$btnHtml}\n{$js}", 'btnHtml' => $btnHtml, 'initJs' => $js, 'htmlId' => $htmlId, 'btnId' => $htmlId.'-btn', 'table' => $table, 'mode' => $mode, 'id' => $id);
}


function kota_pre_groups_assignment_history_group_id(&$value, $data) {
	$id = $data['id'];
	if ($id) {
		$entry = db_select_data('ko_groups_assignment_history', "WHERE `id` = {$id}", 'id,group_id,role_id', '', '', TRUE);
		$value = $entry['group_id'];
		$fullGid = ko_groups_decode(zerofill($value, 6), 'full_gid');
		$fullDesc = ko_groups_decode($fullGid, 'group_desc_full');
		$value = $fullDesc;

		if ($role = db_select_data('ko_grouproles', "WHERE `id` = {$entry['role_id']}", '*', '', '', TRUE)) {
			$value .= " ({$role['name']})";
		}
	} else {
		$value = '';
	}
}


function kota_pre_groups_assignment_history_person_id(&$value, $data) {
	$id = $data['id'];
	if ($id) {
		$entry = db_select_data('ko_groups_assignment_history', "WHERE `id` = {$id}", 'id,person_id', '', '', TRUE);
		$value = $entry['person_id'];
	} else {
		$value = '';
	}

	if ($value) {
		ko_get_person_by_id($value, $person);
		if ($person) {
			$value = trim("{$person['vorname']} {$person['nachname']}");
			if ($person['adresse'] || $person['ort']) {
				$vo = trim("{$person['adresse']} {$person['ort']}");
				$value .= " ({$vo})";
			}
		} else {
			$value = '';
		}
	} else {
		$value = '';
	}
}


function kota_listview_refnr(&$value, $data) {
	$value = ko_nice_refnr($value);
}


function kota_listview_vesr_reason(&$value, $data) {
	$type = $data['dataset']['type'];
	$reason = $data['dataset']['reason'];

	$value = getLL("kota_ko_vesr_reason_{$reason}");
	if (!$value) {
		$value = getLL("kota_ko_vesr_reason_{$type}_{$reason}");
	}
}


function kota_pre_ko_leute_assignment_history(&$value, $data) {
	if ($data['id']) {
		$value = "<div id=\"groups-assignment-history\"></div>".ko_groups_get_assignment_timeline('person', 'groups-assignment-history', NULL, $data['id']);
	}
}

function kota_listview_group_names(&$value, $data) {
	global $all_groups;
	$all_grouproles = null;
	$ids = explode(',',$data['dataset'][$data['col']]);
	$value = '';
	foreach($ids as $id) {
		if(!$id) continue;
		$p = explode(':',$id);
		$gid = substr($p[0],1);
		$rid = count($p) > 1 && $p[1][0] == 'r' ? substr($p[1],1) : false;
		if(!is_array($all_groups)) ko_get_groups($all_groups);
		if($value) $value .= ', ';
		$value .= $all_groups[$gid]['name'];
		if($rid) {
			if(!is_array($all_grouproles)) ko_get_grouproles($all_grouproles);
			$value .= ' ('.$all_grouproles[$rid]['name'].')';
		}
	}
}


function kota_pre_ko_leute_info_donations(&$value, $data) {
	global $access;

	if($data['new_entry']) return FALSE;

	if(!ko_module_installed('donations')) return FALSE;

	if(!is_array($access['donations'])) ko_get_access('donations');
	if($access['donations']['MAX'] < 1) return FALSE;

	$lid = intval($data['id']);
	if(!$lid) return FALSE;

	//Access check: Limit to accounts with read access
	if($access['donations']['ALL'] > 0) {
		$accessWhere = '';
	} else {
		$allowedAccounts = array();
		foreach($access['donations'] as $k => $v) {
			if(!is_int($k)) continue;
			if($v > 0) $allowedAccounts[] = $k;
		}
		if(sizeof($allowedAccounts) == 0) return FALSE;
		$accessWhere = " AND `account` IN (".implode(',', $allowedAccounts).") ";
	}

	$donations = db_select_data('ko_donations', "WHERE `person` = '$lid'".$accessWhere, '*', 'ORDER BY `date` ASC');
	if(sizeof($donations) == 0) return FALSE;

	$years = $accountIDs = $sums = $yearSums = array();
	foreach($donations as $d) {
		$y = substr($d['date'], 0, 4);
		$a = $d['account'];
		if(!in_array($y, $years)) $years[] = $y;
		if(!in_array($a, $accountIDs)) $accountIDs[] = $a;
		$sums[$a][$y] += $d['amount'];
		$yearSums[$y] += $d['amount'];
	}
	if(sizeof($accountIDs) == 0) return FALSE;
	$accounts = db_select_data('ko_donations_accounts', "WHERE `id` IN (".implode(',', $accountIDs).")", '*', 'ORDER BY `name` ASC');


	$table  = '<table class="table table-sm table-bordered">';
	$table .= '<tr class="info"><th>'.getLL('kota_ko_donations_account').'</th>';
	foreach($years as $year) {
		$table .= '<th>'.$year.'</th>';
	}
	$table .= '<th>&Sigma;</th>';
	$table .= '</tr>';

	foreach($accounts as $account) {
		$table .= '<tr><th>'.$account['name'].'</th>';
		foreach($years as $year) {
			if($sums[$account['id']][$year] > 0) {
				$table .= '<td>'.ko_nice_money_amount($sums[$account['id']][$year]).'</td>';
			} else {
				$table .= '<td>&nbsp;</td>';
			}
		}
		$table .= '<td class="warning">'.ko_nice_money_amount(array_sum($sums[$account['id']])).'</td>';
		$table .= '</tr>';
	}

	$table .= '<tr class="warning"><th>&Sigma;</th>';
	foreach($years as $year) {
		$table .= '<td>'.ko_nice_money_amount($yearSums[$year]).'</td>';
	}
	$table .= '<td><span style="border-bottom: 3px double #000;">'.ko_nice_money_amount(array_sum($yearSums)).'</span></td>';
	$table .= '</tr>';
	$table .= '</table>';



	$chartData = array();
	foreach($years as $y) {
		$chartData[] = array('meta' => $y.': '.ko_nice_money_amount($yearSums[$y]), 'value' => $yearSums[$y]);
	}

	$chart  = '<div class="ko-leute-info-donations-chart chartist-chart"></div>';
	$chart .= '<script>';
	$chart .= 'var chartData = '.json_encode($chartData, JSON_NUMERIC_CHECK).';';
	$chart .= "var data = {
labels: ['".implode("','", $years)."'],
series: [ chartData ]
};
new Chartist.Bar('.ko-leute-info-donations-chart', data,
{
	plugins: [
		Chartist.plugins.tooltip()
	]
});
</script>";


	$value = '<div class="row">';
	$value .= '<div class="col-md-6 col-sm-12">';
	$value .= $table;
	$value .= '</div>';
	$value .= '<div class="col-md-6 col-sm-12">';
	$value .= $chart;
	$value .= '</div>';
	$value .= '</div>';
}//kota_pre_ko_leute_info_donations





function kota_pre_ko_leute_info_ref(&$value, $data) {
	global $access, $KOTA;

	if(!ko_module_installed('leute')) return FALSE;

	if(!$data['id']) return FALSE;

	$relationFields = array(
		'ko_leute.father',
		'ko_leute.mother',
		'ko_leute.spouse',
	);
	foreach($KOTA['ko_leute'] as $field => $def) {
		if(substr($field, 0, 1) != '_' && isset($def['form']) && $def['form']['type'] == 'peoplesearch' && !$def['form']['dontsave']) {
			$relationFields[] = 'ko_leute.'.$field;
		}
	}
	$relationFields[] = 'ko_admin.leute_id';


	$table  = '<table class="table">';
	foreach($relationFields as $def) {
		list($tbl, $field) = explode('.', $def);

		$del = $tbl == 'ko_leute' ? " AND `deleted` = '0' " : '';
		$refs = db_select_data($tbl, "WHERE `$field` REGEXP '(^|,)".$data['id']."(,|$)' ".$del, '*');;
		if(sizeof($refs) == 0) continue;

		$names = array();
		switch($tbl) {
			case 'ko_leute':
				$table .= '<tr><th>'.getLL('kota_'.$tbl.'_'.$field).'</th>';
				foreach($refs as $ref) {
					$names[] = trim($ref['vorname'].' '.$ref['nachname']) . ($ref['firm'] ? ' ('.$ref['firm'].')' : '');
				}
			break;
			case 'ko_admin':
				$table .= '<tr><th>'.getLL('admin_logins_login').' ('.getLL('kota_'.$tbl.'_'.$field).')</th>';
				foreach($refs as $ref) {
					$names[] = $ref['login'];
				}
			break;
		}
		$table .= '<td>'.implode('<br />', $names).'</td>';
		$table .= '</tr>';
	}
	$table .= '</table>';


	$value .= $table;
}//kota_pre_ko_leute_info_ref()





function kota_pre_ko_leute_info_login(&$value, $data) {
	global $access;

	if($access['admin']['ALL'] < 4) return;

	$logins = db_select_data('ko_admin', "WHERE `leute_id` REGEXP '(^|,)".$data['id']."(,|$)'", '*');;
	if(sizeof($logins) == 0) return;

	$value = '';
	foreach($logins as $login) {
		$logs = db_select_data('ko_log', "WHERE `type` = 'login' AND `user_id` = '".$login['id']."'", 'id,date', 'ORDER BY `date` ASC');
		$data = array();
		foreach($logs as $log) {
			$data[substr($log['date'], 0, 10)] += 1;
		}

		$dataString = '';
		foreach($data as $date => $num) {
			$dataString .= '"'.$date.'": '.$num.', ';
		}
		$dataString = substr($dataString, 0, -2);
		if(!$dataString) return;

		$value .= '<h4>Logins für '.$login['login'].'</h4><div class="login_stats_'.$login['id'].'"></div>';
		$value .= '<script>
			var data = {'.$dataString.'};
			$(".login_stats_'.$login['id'].'").CalendarHeatmap(data, {weekStartDay: 1, coloring: "red", legend: {minLabel: "'.getLL('less').'", maxLabel: "'.getLL('more').'"} });
		</script>';
	}
}//kota_pre_ko_leute_info_login()






function kota_pre_ko_leute_info_taxonomy(&$value, $data) {
	global $all_groups, $access;

	if(!ko_module_installed('taxonomy')) return FALSE;

	if(!is_array($access['taxonomy'])) ko_get_access('taxonomy');
	if($access['taxonomy']['MAX'] < 1) return FALSE;

	preg_match_all('/g[0-9]{6}/m', $data['dataset']['groups'], $groups, PREG_SET_ORDER, 0);

	$allTerms = [];
	foreach($groups as $group) {
		$group_id = (int) substr($group[0],1);
		$terms = (array) ko_taxonomy_get_terms_by_node((int)$group_id, 'ko_groups');
		if(sizeof($terms) == 0) continue;

		//Get group path for tooltip
		$groupTitle = ko_groups_decode(ko_groups_decode(zerofill($group_id, 6), 'full_gid'), 'group_desc_full');
		$groupName = ko_groups_decode(ko_groups_decode(zerofill($group_id, 6), 'full_gid'), 'group_desc');
		foreach($terms as $term) {
			if(!isset($allTerms[$term['id']])) $allTerms[$term['id']] = $term;
			$allTerms[$term['id']]['_groups'][] = array('name' => $groupName, 'full_path' => $groupTitle);
		}
	}


	$table  = '<table class="table">';
	foreach($allTerms as $term) {
		$table .= '<tr><th><span class="label label-info taxonomy-term__label">'.$term['name'].'</span></th>';
		$groupLabels = array();
		foreach($term['_groups'] as $g) {
			$groupLabels[] = '<span '.ko_get_tooltip_code($g['full_path']).'>'.$g['name'].'</span>';
		}
		$table .= '<td>'.implode(', ', $groupLabels).'</td>';
		$table .= '</tr>';
	}
	$table .= '</table>';


	$value = '<div class="row">';
	$value .= '<div class="col-md-12 col-sm-12">';
	$value .= $table;
	$value .= '</div>';
	$value .= '</div>';
}//kota_pre_ko_leute_info_taxonomy()




//TODO: Add list of open group subscriptions
function kota_pre_ko_leute_info_gs(&$value, $data) {
	global $DATETIME, $access;

	if(!ko_module_installed('groups')) return FALSE;

	if(!is_array($access['groups'])) ko_get_access('groups');
	if($access['groups']['MAX'] < 1) return FALSE;


	$gs_pid = ko_get_setting('daten_gs_pid');
	if(!$gs_pid) return FALSE;

	$yearGroups = db_select_data('ko_groups', "WHERE `pid` = '$gs_pid'");

	$subscriptions = array();
	foreach(explode(',', $data['dataset']['groups']) as $gid) {
		if(FALSE === strpos($gid, 'g'.$gs_pid)) continue;

		$group_id = ko_groups_decode($gid, 'group_id');
		if($access['groups']['ALL'] < 1 && $access['groups'][$group_id] < 1) continue;

		$yearGid = substr($gid, strpos('g'.$gs_pid, $gid)+9, 6);
		$year = $yearGroups[$yearGid]['name'];

		$event = db_select_data('ko_event', "WHERE `gs_gid` LIKE '%g".$group_id."%'", '*', '', '', TRUE);

		$subscriptions[$year][] = array(
			'title' => ko_groups_decode($gid, 'group_desc'),
			'full_path' => ko_groups_decode($gid, 'group_desc_full'),
			'event' => $event,
		);
	}
	ksort($subscription);


	$table  = '<table class="table">';
	$table .= '<tr><th>'.getLL('year').'</th><th>'.getLL('groups_group').'</th><th>'.getLL('kota_ko_reservation_event_id').'</th></tr>';
	foreach($subscriptions as $year => $info) {
		$first = TRUE;
		foreach($info as $i) {
			if($first) {
				$first = FALSE;
				$table .= '<th>'.$year.'</th>';
			} else {
				$table .= '<th>&nbsp;</th>';
			}
			$table .= '<td><span '.ko_get_tooltip_code($i['full_path']).'>'.$i['title'].'</span></td>';

			if($i['event']['id']) {
				if($i['event']['startdatum'] != $i['event']['enddatum']) {
					$eventInfo = ko_date_format_timespan($i['event']['startdatum'], $i['event']['enddatum']);
				} else {
					$eventInfo = strftime($DATETIME['dMY'], strtotime($i['event']['startdatum']));
				}
				$eventInfo .= ': '.$i['event']['title'];
				$table .= '<td>'.$eventInfo.'</td>';
			} else {
				$table .= '<td>-</td>';
			}

			$table .= '</tr>';
		}
	}
	$table .= '</table>';


	$value = '<div class="row">';
	$value .= '<div class="col-md-12 col-sm-12">';
	$value .= $table;
	$value .= '</div>';
	$value .= '</div>';
}//kota_pre_ko_leute_info_gs()




function kota_pre_ko_leute_info_rota_1(&$value, $data) {
	global $BASE_PATH, $DATETIME, $access;

	if(!ko_module_installed('rota')) return FALSE;

	if(!is_array($access['rota'])) ko_get_access('rota');
	if($access['rota']['MAX'] < 1) return FALSE;

	include_once($BASE_PATH.'rota/inc/rota.inc');

	$lid = $data['id'];

	$teamRole = ko_get_setting('rota_teamrole');
	$roleWhere = $teamRole ? " AND `role_id` = '$teamRole' " : '';

	//Check access to rota teams
	$accessWhere = '';
	if($access['rota']['ALL'] < 1) {
		$allowedTeamIDs = array();
		foreach($access['rota'] as $k => $v) {
			if(!is_int($k)) continue;
			if($v > 0) $allowedTeamIDs[] = $k;
		}
		if(sizeof($allowedTeamIDs) > 0) {
			$accessWhere = " AND `id` IN (".implode(',', $allowedTeamIDs).") ";
		} else {
			return FALSE;
		}
	}

	//Get old groups for this person
	$oldGroups = array();
	$old = db_select_data('ko_groups_assignment_history', "WHERE `person_id` = '$lid' AND `stop` != '0000-00-00 00:00:00'".$roleWhere, '*', 'ORDER BY `start` ASC');
	foreach($old as $o) {
		if(!in_array($o['group_id'], $oldGroups)) $oldGroups[] = $o['group_id'];
	}


	$allTeams = ko_rota_get_all_teams($accessWhere);
	$teams = array();
	$oldTeams = array();
	$historyData = array();
	foreach($allTeams as $team) {
		$teamGroups = array();
		$oldTeamGroups = array();
		foreach(explode(',', $team['group_id']) as $gid) {
			$group_id = ko_groups_decode($gid, 'group_id');
			if($group_id) $teamGroups[] = $group_id;
		}

		$members = ko_rota_get_team_members($team, TRUE);
		if(in_array($lid, array_keys($members['people']))) {
			$teams[] = $team;
		} else {
			foreach($teamGroups as $tg) {
				if(in_array($tg, $oldGroups)) $oldTeamGroups[] = $tg;
			}
			if(sizeof($oldTeamGroups) > 0) {
				$oldTeams[] = $team;
			}
			else {
				continue;
			}
		}

		$allGroups = array_merge($teamGroups, $oldTeamGroups);
		if(sizeof($allGroups) > 0) {
			$e = db_select_data('ko_groups_assignment_history', "WHERE `person_id` = '$lid' AND `group_id` IN (".implode(',', $allGroups).") ".$roleWhere, '*', 'ORDER BY `start` ASC');
			$history = array();
			foreach($e as $entry) {
				$historyData[$team['id']][] = ko_date_format_timespan($entry['start'], ($entry['stop'] == '0000-00-00 00:00:00' ? 'today' : $entry['stop']));
			}
		}
	}

	$table  = '<table class="table">
	<tr><th>'.getLL('leute_info_rota_team').'</th><th>'.getLL('leute_info_rota_membership').'</th><th>'.getLL('leute_info_rota_chart').'</th></tr>';
	foreach($teams as $team) {
		$table .= '<tr><th>'.$team['name'].'</th>';
		$table .= '<td>'.implode('<br />', $historyData[$team['id']]).'</td>';
		$chart = str_replace('<svg ', '<svg style="background: black;" ', ko_rota_get_participation_chart($lid, $team['id'], 'team'));
		$table .= '<td>'.$chart.'</td>';
		$table .= '</tr>';
	}
	foreach($oldTeams as $team) {
		$table .= '<tr><th>('.$team['name'].')</th>';
		$table .= '<td>'.implode('<br />', $historyData[$team['id']]).'</td>';
		$table .= '<td>&nbsp;</td>';
		$table .= '</tr>';
	}
	$table .= '</table>';


	$value .= $table;
}//kota_pre_ko_leute_info_rota_1()





function kota_pre_ko_leute_info_rota_2(&$value, $data) {
	global $BASE_PATH, $DATETIME;

	include_once($BASE_PATH.'rota/inc/rota.inc');

	$lid = $data['id'];
	$allTeams = ko_rota_get_all_teams();

	$max = 10; $c = 0;
	$schedule_ = ko_rota_get_scheduled_events($lid);
	$schedule = array();
	foreach ($schedule_ as $eventId => $s_) {
		$c++;
		if($c > $max) continue;

		$s = $s_;
		$event = db_select_data('ko_event', "WHERE `id` = '$eventId'", '*', '', '', TRUE);
		$s['event'] = $event;
		foreach ($s_['in_teams'] as $team) {
			$helpers = ko_rota_get_helpers_by_event_team($eventId, $team);
			$helperString = $allTeams[$team]['name'].': ';
			foreach($helpers as $h) {
				$helperString .= $h['vorname'].' '.$h['nachname'].', ';
			}
			$s['helpers'] = substr($helperString, 0, -2);
		}
		$schedule[] = $s;
	}

	
	$next  = '<table class="table">
	<tr><th>'.getLL('kota_listview_ko_event_startdatum').'</th><th>'.getLL('kota_listview_ko_event_startzeit').'</th><th>'.getLL('kota_listview_ko_event_title').'</th><th>'.getLL('leute_info_rota_helpers').'</th></tr>';
	foreach($schedule as $s) {
		$next .= '<tr>';
		$next .= '<td>'.strftime($DATETIME['DdmY'], strtotime($s['event']['startdatum'])).'</td>';
		$next .= '<td>'.substr($s['event']['startzeit'], 0, -3).'</td>';
		$next .= '<td>'.$s['event']['title'].'</td>';
		$next .= '<td>'.$s['helpers'].'</td>';
		$next .= '</tr>';
	}
	$next .= '</table>';


	$value .= $next;
}//kota_pre_ko_leute_info_rota_2()



function kota_array_ll($array, $table, $col) {
	return ko_array_ll($array, "kota_{$table}_{$col}_");
}

function kota_subscription_form_fields_render(&$value, $kota_data, $log, $orig_data) {
	global $KOTA,$BASE_PATH;

	$inputName = 'koi['.$kota_data['table'].']['.$kota_data['col'].']['.$kota_data['id'].']';

	$excludeLeuteFields = ['rectype','famfunction'];

	// get leute fields
	ko_include_kota(array('ko_leute'));
	$leuteFields = array();
	foreach($KOTA['ko_leute'] as $name => $column) {
		if($name[0] != '_' && isset($column['form']['type']) && in_array($column['form']['type'],array('text','select','textarea','textplus','jsdate')) && !in_array($name,$excludeLeuteFields)) {
			$leuteFields[] = $name;
		}
	}

	// get groups
	$groups = array();
	$all_groups = array();
	ko_get_groups($all_groups);
	ko_get_grouproles($all_roles);
	foreach($all_groups as $row) {
		$group = array(
			'name' => $row['name'],
			'datafields' => $row['datafields'],
			'placeholder' => $row['type'] == 1,
		);
		foreach(explode(',',$row['roles']) as $roleId) {
			if($roleId) {
				$group['roles'][$roleId] = $all_roles[$roleId]['name'];
			}
		}
		$groups[$row['pid']][$row['id']] = $group;
	}

	// get datafields
	$datafields = db_select_data('ko_groups_datafields','WHERE private=0');
	array_walk($datafields,function(&$datafield) {
		if(!empty($datafield['options'])) $datafield['options'] = unserialize($datafield['options']);
	});

	// get added fields
	if(isset($_POST['koi']['ko_subscription_forms']['fields'])) {
		$fields = json_encode_latin1(reset($_POST['koi']['ko_subscription_forms']['fields']));
	} else {
		$fields = $value;
	}
	if(!$fields) {
		$fields = '[]';
	}

	// render
	ob_start();
	require($BASE_PATH.'subscription/inc/fields_edit.php');
	$value = ob_get_clean();
}

function kota_subscription_form_fields_store(&$data, $kota_data, $log, $orig_data) {
	$fields = array();
	foreach($orig_data['fields'] as $key => $values) {
		$field = array();
		if(substr($key,0,5) == '_text' || substr($key,0,8) == '_caption') {
			$field = format_userinput($values,'text');
		} else if(substr($key,0,3) == '_hr') {
			$field = 'hr';
		} else if(substr($key,0,6) == '_check') {
			$field = strip_tags($values,'<a><b><strong><i><em><p><br><span>');
		} else {
			if(preg_match('/^g([0-9]{6}):d(.{6})$/',$key,$matches)) {
				$dfid = $matches[2];
				if($dfid == 'ADDALL') {
					$groupId = format_userinput($matches[1],'uint');
					$group = db_select_data('ko_groups','WHERE id='.$groupId,'datafields','','',true);
					if($group['datafields']) {
						$datafields = db_select_data('ko_groups_datafields','WHERE id IN('.$group['datafields'].') AND private=0');
					} else {
						$datafields = [];
					}
					if(isset($values['includeDatafields'])) {
						$field['excludeDatafields'] = array_values(array_diff(array_column($datafields,'id'),$values['includeDatafields']));
					} else {
						$field['excludeDatafields'] = $datafields;
					}
					foreach($datafields as $datafield) {
						if($datafield['type'] == 'select' || $datafield['type'] == 'multiselect') {
							$options = unserialize($datafield['options']);
							if(isset($values['includeOptions'][$datafield['id']])) {
								$field['excludeOptions'][$datafield['id']] = array_values(array_diff($options,$values['includeOptions'][$datafield['id']]));
							} else {
								$field['excludeOptions'][$datafield['id']] = $options;
							}
						}
					}
					unset($values['includeDatafields']);
				} else {
					$dfid = format_userinput($dfid,'uint');
					$datafield = db_select_data('ko_groups_datafields','WHERE id='.$dfid,'*','','',true);
					if($datafield['type'] == 'select' || $datafield['type'] == 'multiselect') {
						$options = unserialize($datafield['options']);
						if(isset($values['includeOptions'])) {
							$field['excludeOptions'] = array_values(array_diff($options,$values['includeOptions']));
						} else {
							$field['excludeOptions'] = $options;
						}
					}
				}
				unset($values['includeOptions']);
			}
			foreach($values as $name => $value) {
				switch($name) {
					case 'mandatory':
						$value = format_userinput($value,'uint');
						break;
					default:
						$value = format_userinput($value,'text');
				}
				$field[$name] = $value;
			}
		}
		$fields[$key] = $field;
	}
	if($_POST['koi']['ko_subscription_forms']['double_opt_in']) {
		if(empty($fields['email']['mandatory'])) {
			throw new \kOOL\Subscription\FormKotaException(getLL('subscription_kota_error_double_opt_in_without_email'));
		}
	}
	$data = json_encode_latin1($fields);
}



function kota_subscription_get_form_layout_select_options($row) {
	global $PLUGINS;

	$layouts = [];
	foreach($PLUGINS as $plugin) {
		if(function_exists('my_'.$plugin['name'].'_subscription_form_get_layouts')) {
			$ret = call_user_func('my_'.$plugin['name'].'_subscription_form_get_layouts');
			foreach($ret as $l) {
				$layouts[] = $l;
			}
		}
	}
	$options = ['values' => [''],'descs' => ['']];
	foreach($layouts as $l) {
		$options['descs'][] = $l['label'];
		$options['values'][] = $l['file'];
	}

	return $options;
}


function kota_listview_subscription_form_layout(&$value, $data) {
	global $PLUGINS;

	if(!$value) return $value;

	foreach($PLUGINS as $plugin) {
		if(function_exists('my_'.$plugin['name'].'_subscription_form_get_layouts')) {
			$layouts = call_user_func('my_'.$plugin['name'].'_subscription_form_get_layouts');
			foreach($layouts as $layout) {
				if($layout['file'] == $value) {
					$value = $layout['label'];
					return;
				}
			}
		}
	}
}



function kota_listview_subscription_form_groups(&$value, $data) {
	$all_groups = ko_get_all_subscription_form_groups();
	$value = $all_groups[$value]['name'];
}

function kota_subscription_get_form_group_select_options($row) {
	global $access;
	$options = array();
	$options['values'][] = '';
	$options['descs'][] = '';
	foreach(ko_get_all_subscription_form_groups() as $group) {
		$isOwn = empty($row['id']) || $row['cruser'] == $_SESSION['ses_userid'];
		$groupAccess = max($access['subscription']['ALL'],$access['subscription'][$group['id']]);
		if($groupAccess >= 2 || ($groupAccess == 1 && $isOwn)) {
			$options['values'][] = $group['id'];
			$options['descs'][] = $group['name'];
		}
	}
	return $options;
}

function kota_subscription_form_save_url_segment(&$data, $kota_data, $log, $orig_data) {
	$forms = db_select_data('ko_subscription_forms','WHERE id <> '.$kota_data['id'],'url_segment');
	$segments = array_column($forms,'url_segment');
	$title = $kota_data['dataset']['title'];
	$title = mb_strtolower($title,'latin1');
	$title = str_replace(array('ä','ö','ü'),array('ae','oe','ue'),$title);
	$title = preg_replace(
		array('/[\s-]+/','/[àáâãå]/','/[èéêë]/','/[ìíîï]/','/[òóôõ]/','/[ùúû]/'),
		array('-','a','e','i','o','u'),
		$title
	);
	$title = preg_replace('/[^a-z0-9_-]/','',$title);
	$postfix = '';
	if(sizeof($segments) > 0) {
		while(array_search($title.$postfix,$segments) !== false) {
			$postfix++;
		}
	}
	db_update_data('ko_subscription_forms','WHERE id='.$kota_data['id'],array('url_segment' => $title.$postfix));
}

function kota_subscription_form_link(&$value, $kota_data, $log, $orig_data) {
	$url = ($_SERVER['HTTPS'] ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'].'/form/'.$value;
	if($kota_data['dataset']['protected'] || empty($kota_data['dataset']['groups'])) {
		$admin = db_select_data('ko_admin','WHERE id='.$_SESSION['ses_userid'],'leute_id','','',true);
		if(!empty($admin['leute_id'])) {
			$url .= '/edit/'.ko_subscription_generate_key($kota_data['id'],$admin['leute_id'],'','edit_link');
			$person = db_select_data('ko_leute','WHERE id='.$admin['leute_id'],'vorname,nachname','','',true);
			$linktext = sprintf(getLL('subscription_create_personal_link'),$person['vorname'].' '.$person['nachname']);
			$value = '<a href="" onclick="var url=\''.$url.'\'; if(this.href != url) {this.href=url; this.innerText=url; return false;}" target="_blank">'.$linktext.'</a>';
		} else {
			$value = '';
		}
	} else {
		$value = '<a href="'.$url.'" target="_blank">'.$url.'</a>';
	}
}

function kota_subscription_form_get_iframe_code(&$value, $kota_data, $log, $orig_data) {
	if($kota_data['dataset']['protected'] || empty($kota_data['dataset']['groups'])) {
		$value = '<textarea class="form-control" disabled>'.getLL('subscription_form_no_iframe').'</textarea>';
	} else {
		$base = ($_SERVER['HTTPS'] ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'];
		$url = $base.'/form/'.$kota_data['dataset']['url_segment'];
		$id = 'kOOLForm'.substr(base_convert(md5($url),16,10),0,8);
		$code = '<iframe id="'.$id.'" src="'.$url.'?mode=iframe" scrolling="no" frameborder="0" width="100%"></iframe>';
		$code .= '<script src="'.$base.'/subscription/inc/iframeResizer.min.js"></script>';
		$code .= '<script>iFrameResize({},\'#'.$id.'\');</script>';
		$value = '<textarea class="form-control" readonly>'.htmlentities($code).'</textarea>';
	}
}
