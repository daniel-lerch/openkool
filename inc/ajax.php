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

//Allow POST as well
if (isset($_POST['sesid']) && isset($_POST['action'])) $_GET = $_POST;

//Set session id from GET (session will be started in ko.inc)
if (!isset($_GET["sesid"])) exit;
if (FALSE === session_id($_GET["sesid"])) exit;

//Send headers to ensure latin1 charset
header('Content-Type: text/html; charset=ISO-8859-1');

error_reporting(0);
$ko_menu_akt = 'home';
$ko_path = "../";
require($ko_path . "inc/ko.inc");

array_walk_recursive($_GET, 'rawurldecode_array');
array_walk_recursive($_GET, 'utf8_decode_array');

//Include plugin code
$hooks = hook_include_main('_all');
if (sizeof($hooks) > 0) foreach ($hooks as $hook) include_once($hook);

//HOOK: Submenus einlesen
$hooks = hook_include_sm();
if (sizeof($hooks) > 0) foreach ($hooks as $hook) include($hook);

hook_show_case_pre($_SESSION['show']);


if (isset($_GET) && isset($_GET["action"])) {
	$action = format_userinput($_GET["action"], "alphanum");

	hook_ajax_pre($ko_menu_akt, $action);

	switch ($action) {
		case 'togglesidebar':
			//Guest kann Layout nicht ändern
			if ($_SESSION["ses_userid"] == ko_get_guest_id()) return FALSE;
			$toState = format_userinput($_GET["tostate"], "js");

			if (!in_array($toState, ['open', 'closed'])) return FALSE;

			ko_save_userpref($_SESSION["ses_userid"], 'sidebar_state', $toState);
			break;

		case "togglesm":
			//Guest kann Layout nicht ändern
			if ($_SESSION["ses_userid"] == ko_get_guest_id()) return FALSE;

			//Übergebene Daten auslesen
			$id = format_userinput($_GET["id"], "js");
			$sm_module = format_userinput($_GET["mod"], "js");
			$toState = format_userinput($_GET["tostate"], "js");
			if (!in_array($sm_module, $MODULES)) return FALSE;

			//Bestehende SM dieses Benutzers holen
			$submenus = unserialize(ko_get_userpref($_SESSION["ses_userid"], "submenu_" . $sm_module));
			if ($submenus[$id]['state'] == $toState || !in_array($toState, ['open', 'closed'])) break;

			//Close submenu
			$submenus[$id]['state'] = $toState;

			//Werte neu speichern
			ko_save_userpref($_SESSION["ses_userid"], "submenu_" . $sm_module, serialize($submenus));
			break;


		case 'movesm':
			//Guest kann Layout nicht ändern
			if ($_SESSION["ses_userid"] == ko_get_guest_id()) return FALSE;

			//Übergebene Daten auslesen
			$sm_module = format_userinput($_GET["mod"], "js");
			if (!in_array($sm_module, $MODULES)) return FALSE;
			$id = format_userinput($_GET["id"], "js");
			$nextId = format_userinput($_GET["nextId"], "js");
			$prevId = format_userinput($_GET["prevId"], "js");

			$submenus = unserialize(ko_get_userpref($_SESSION["ses_userid"], "submenu_" . $sm_module));
			$newSubmenus = [];

			$placed = FALSE;
			foreach ($submenus as $smName => $smData) {

				if (!$placed && $nextId == $smName) {
					$newSubmenus[$id] = $submenus[$id];
					$newSubmenus[$smName] = $smData;
					$placed = TRUE;
				} else if (!$placed && $prevId == $smName) {
					$newSubmenus[$smName] = $smData;
					$newSubmenus[$id] = $submenus[$id];
					$placed = TRUE;
				} else if ($smName != $id) {
					$newSubmenus[$smName] = $smData;
				}
			}

			//Werte neu speichern
			ko_save_userpref($_SESSION["ses_userid"], "submenu_" . $sm_module, serialize($newSubmenus));
			break;

		case 'togglefm':
			//Guest kann Layout nicht ändern
			if ($_SESSION["ses_userid"] == ko_get_guest_id()) return FALSE;
			$module = format_userinput($_GET["fm"], "js");
			$toState = format_userinput($_GET["tostate"], "js");

			$frontModulesUP = explode(',', ko_get_userpref($_SESSION['ses_userid'], 'front_modules'));

			if ($toState == 'closed') {
				foreach ($frontModulesUP as $k => $frontModuleUP) {
					if ($frontModuleUP == $module) {
						unset($frontModulesUP[$k]);
					}
				}
			} else {
				if (!in_array($module, $frontModulesUP)) {
					$frontModulesUP[] = $module;
				}
			}
			ko_save_userpref($_SESSION['ses_userid'], 'front_modules', implode(',', $frontModulesUP));

			break;
		case "savenote":
			//Guest kann Layout nicht ändern
			if ($_SESSION["ses_userid"] == ko_get_guest_id()) return FALSE;

			//Übergebene Daten auslesen
			$pos = format_userinput($_GET["pos"], "alpha");
			if (!in_array($pos, ["left", "right"])) return FALSE;
			$sm_module = format_userinput($_GET["mod"], "js");

			if ($_GET["notename"] == "" && $_GET["selnote"]) {  //Falls keine Name angegeben, dann die aktuelle Notiz überschreiben...
				$save_key = format_userinput($_GET["selnote"], "alphanum++");
			} else if ($_GET["notename"]) {  //... sonst als neue Notiz speichern
				$save_key = format_userinput($_GET["notename"], "alphanum++");
			} else {
				break;
			}

			ko_save_userpref($_SESSION["ses_userid"], $save_key, format_userinput($_GET["note"], "text"), "notizen");
			$_SESSION["show_notiz"] = $save_key;

			//Neuen HTML-Code für SM ausgeben
			print submenu("gsm_notizen", $pos, "", 2, $sm_module);  //State is always open when saving
			break;


		case "opennote":
			//Guest kann Layout nicht ändern
			if ($_SESSION["ses_userid"] == ko_get_guest_id()) return FALSE;

			//Übergebene Daten auslesen
			$pos = format_userinput($_GET["pos"], "alpha");
			if (!in_array($pos, ["left", "right"])) return FALSE;
			$sm_module = format_userinput($_GET["mod"], "js");

			$save_key = format_userinput($_GET["selnote"], "alphanum++");
			if (!$save_key) break;

			$_SESSION["show_notiz"] = $save_key;

			//Neuen HTML-Code für SM ausgeben
			print submenu("gsm_notizen", $pos, "", 2, $sm_module);  //State is always open when saving
			break;


		case "deletenote":
			//Guest kann Layout nicht ändern
			if ($_SESSION["ses_userid"] == ko_get_guest_id()) return FALSE;

			//Übergebene Daten auslesen
			$pos = format_userinput($_GET["pos"], "alpha");
			if (!in_array($pos, ["left", "right"])) return FALSE;
			$sm_module = format_userinput($_GET["mod"], "js");

			$del_key = format_userinput($_GET["selnote"], "alphanum++");
			if (!$del_key) break;

			ko_delete_userpref($_SESSION["ses_userid"], $del_key, "notizen");
			$_SESSION["show_notiz"] = "";

			//Neuen HTML-Code für SM ausgeben
			print submenu("gsm_notizen", $pos, "", 2, $sm_module);  //State is always open when saving
			break;

		case 'updatesecmenu':
			//Guest kann Layout nicht ändern
			if ($_SESSION["ses_userid"] == ko_get_guest_id()) return FALSE;

			//Übergebene Daten auslesen
			$sm_module = format_userinput($_GET["mod"], "js");
			if (!in_array($sm_module, $MODULES)) return FALSE;
			$id = format_userinput($_GET["id"], "js");
			$nextId = format_userinput($_GET["nextId"], "js");
			$prevId = format_userinput($_GET["prevId"], "js");
			$removed = format_userinput($_GET["removed"], "uint");
			$subMenuId = format_userinput($_GET["smId"], "js");

			$subMenuLinksAll = ko_get_submenu_links($sm_module);
			$links = [];
			foreach ($subMenuLinksAll as $subMenuLinks) {
				foreach ($subMenuLinks['links'] as $subMenuLink) {
					$links[] = $subMenuLink['action'];
				}
			}
			if (!in_array($id, $links)) {
				return FALSE;
			}

			$secMenuLinks = explode(',', ko_get_userpref($_SESSION['ses_userid'], $sm_module . '_menubar_links'));
			$newSubMenuLinks = [];

			$placed = FALSE;
			foreach ($secMenuLinks as $actionName) {
				if ($actionName == '' || !in_array($actionName, $links) || in_array($actionName, $newSubMenuLinks)) continue;

				if (!$placed && $nextId == $actionName) {
					$newSubMenuLinks[] = $id;
					$newSubMenuLinks[] = $actionName;
					$placed = TRUE;
				} else if (!$placed && $prevId == $actionName) {
					$newSubMenuLinks[] = $actionName;
					$newSubMenuLinks[] = $id;
					$placed = TRUE;
				} else if ($actionName != $id) {
					$newSubMenuLinks[] = $actionName;
				}
			}
			if (sizeof($newSubMenuLinks) == 0 && !$removed) {
				$newSubMenuLinks[] = $id;
			}
			ko_save_userpref($_SESSION['ses_userid'], $sm_module . '_menubar_links', implode(',', $newSubMenuLinks));

			if ($subMenuId != '') {
				$state = $_SESSION["submenu"][$sm_module]['state'];
				$r = call_user_func_array("submenu_" . $sm_module, [$subMenuId, $state, 2]);
				print $r;
			}
			break;


		case "exporttomylist":
			$ids = format_userinput(str_replace('@', ',', $_GET['ids']), 'intlist');
			$_SESSION['my_list'] = [];
			foreach (explode(',', $ids) as $id) {
				if ($id) $_SESSION['my_list'][$id] = $id;
			}
			ko_save_userpref($_SESSION['ses_userid'], 'leute_my_list', serialize($_SESSION['my_list']));

			print 'mailmerge_infobox@@@';
			print getLL('leute_mailmerge_mylist_export_ok');
			break;


		case 'inlineform':
			$module = format_userinput($_GET['module'], 'alphanum+');

			list($table, $id, $col) = explode('|', $_GET['id']);
			$table = format_userinput($table, 'alphanum+');
			$id = format_userinput($id, 'uint');
			$col = format_userinput(str_replace(';', ',', $col), 'alphanumlist');
			$cols = explode(',', $col);

			if ($table == '' || $id <= 0 || $col == '') break;

			//Get access and KOTA for this module
			ko_get_access($module);
			ko_include_kota([$table]);

			//Don't show inline form if setting in KOTA is set accordingly
			foreach ($cols as $ci => $column) {
				if ($KOTA[$table][$column]['form']['noinline'] === TRUE) unset($cols[$ci]);
			}

			if (sizeof($cols) == 0) break;

			//Access check
			if (!$KOTA[$table]['_access']['level'] || !isset($KOTA[$table]['_access']['chk_col'])) break;

			$entry = db_select_data($table, "WHERE `id` = '$id'", '*', '', '', TRUE);
			$chk_col = $KOTA[$table]['_access']['chk_col'];
			$rights_level = $KOTA[$table]['_access']['level'];
			$ok = FALSE;
			if (substr($chk_col, 0, 4) == 'ALL&') {
				$ok = ($access[$module]['ALL'] >= $rights_level || $access[$module][$entry[substr($chk_col, 4)]] >= $rights_level);
			} else if ($chk_col != '') {
				$ok = ($access[$module][$entry[$chk_col]] >= $rights_level);
			} else {
				$ok = ($access[$module]['ALL'] >= $rights_level);
			}

			if ($table == "ko_event_absence") {
				require_once('../daten/inc/daten.inc');
				$absence = ko_daten_get_absence_by_id($id);
				if ($access['daten']['ABSENCE'] <= 2 && ko_get_logged_in_id() != $absence['leute_id']) {
					return FALSE;
				}
			}

			//Check for column access for leute module
			if ($table == 'ko_leute') {
				//get the cols, for which this user has edit-rights (saved in allowed_cols[edit])
				$allowed_cols = ko_get_leute_admin_spalten($_SESSION['ses_userid'], 'all');

				//Check for edit access
				if (is_array($allowed_cols) && sizeof($allowed_cols) > 0) {
					foreach ($cols as $ci => $column) {
						//Ignore MODULE column like groups and datafields.
						if (substr($column, 0, 6) == 'MODULE') continue;
						if (is_array($allowed_cols['edit']) && !in_array($column, $allowed_cols['edit'])) unset($cols[$ci]);
					}
					if (sizeof($cols) == 0) $ok = FALSE;
				}
			} else if ($table == "ko_event" && $col == "room") {
				unset($KOTA['ko_event']['room']['form']['async_form']);

				require_once($ko_path . 'daten/inc/daten.inc');
				$hidden_rooms = ko_get_event_rooms("", "WHERE hidden = 1");
				foreach ($KOTA[$table]["room"]["form"]["values"] AS $key => $room_in_select) {
					if (in_array($room_in_select, array_column($hidden_rooms, "id")) && $room_in_select != $entry['room']) {
						unset($KOTA[$table]["room"]["form"]["values"][$key]);
						unset($KOTA[$table]["room"]["form"]["descs"][$key]);
					}
				}
			}

			//Type check: Check for this rows type and if this column may be edited
			if (isset($KOTA[$table]['_types']['field']) && $entry[$KOTA[$table]['_types']['field']] != $KOTA[$table]['_types']['default']) {
				$kota_type = $entry[$KOTA[$table]['_types']['field']];
				foreach ($cols as $column) {
					if (!in_array($column, $KOTA[$table]['_types']['types'][$kota_type]['use_fields'])
						&& !in_array($column, array_keys($KOTA[$table]['_types']['types'][$kota_type]['add_fields']))) {
						$ok = FALSE;
					}
				}
			}

			//Check for access condition
			$replacementEntry = [];
			foreach ($entry as $k => $v) {
				$replacementEntry["@{$k}@"] = $v;
			}
			if (isset($KOTA[$table]['_access']['condition'])) {
				if (FALSE === eval(strtr($KOTA[$table]['_access']['condition'], $replacementEntry))) $ok = FALSE;
			}

			if (!$ok) break;

			if ($table == 'ko_leute' && substr($col, 0, 9) == 'MODULEgrp' && FALSE !== strpos($col, ':')) {
				$dfid = substr($col, 16, 6);
				$df = db_select_data('ko_groups_datafields', "WHERE `id` = '$dfid'", '*', '', '', TRUE);
			}

			$do_save = TRUE;

			//Treat checkbox directly by changing it's value (allow only one checkbox to be treated directy, so check for $col not $cols)
			if (in_array($KOTA[$table][$col]['form']['type'], ['checkbox', 'switch'])) {
				//Update value in db
				$data = db_select_data($table, "WHERE `id` = '$id'", '*', '', '', TRUE);

				//Save changes for ko_leute
				if ($table == 'ko_leute') ko_save_leute_changes($id, $data);

				$old_value = $data[$col];
				$data[$col] = $data[$col] == 0 ? 1 : 0;
				db_update_data($table, "WHERE `id` = '$id'", [$col => $data[$col]]);

				ko_log('inline_edit', "{$table} ({$id}) {$col}: {$old_value} --> {$data[$col]}");

				//Check for redraw condition
				$redraw = FALSE;
				foreach ($KOTA[$table]['_inlineform']['redraw'] as $method => $value) {
					switch ($method) {
						case 'sort':
							if (isset($_SESSION[$value]) && (is_array($_SESSION[$value]) && in_array($col, $_SESSION[$value])) || (!is_array($_SESSION[$value]) && $col == $_SESSION[$value])) $redraw = TRUE;
							break;
						case 'cols':
							if (!is_array($value)) $value = explode(',', $value);
							if (in_array($col, $value)) $redraw = TRUE;
							break;
					}
				}

				//Table's post function
				$ids = $id;
				$columns = $col;
				if (function_exists('kota_post_' . $table)) {
					eval("kota_post_$table(\$ids, \$columns, \$entry, \$do_save);");
				}
				hook_kota_post($table, ['table' => $table, 'ids' => $ids, 'columns' => $columns, 'old' => $entry, 'do_save' => $do_save]);

				//Redraw whole list if need be
				if ($redraw) {
					print 'main_content@@@';
					list($module, $file) = explode('|', $KOTA[$table]['_inlineform']['module']);
					$file = $file != '' ? $file : $module;
					include_once($ko_path . $module . '/inc/' . $file . '.inc');
					eval($KOTA[$table]['_inlineform']['redraw']['fcn']);
				} else {
					//Output new value
					kota_process_data($table, $data, 'list', $log, $id);
					print $_GET['id'] . '@@@' . $data[$col];
				}
			} //Check for GDF checkbox
			else if ($table == 'ko_leute' && substr($col, 0, 9) == 'MODULEgrp' && FALSE !== strpos($col, ':') && $df['type'] == 'checkbox') {
				//Find current entry for GDF
				$dfid = substr($col, 16, 6);
				$gid = substr($col, 9, 6);
				if ($access['groups']['ALL'] < 2 && $access['groups'][$gid] < 2) break;
				$dfdata = db_select_data('ko_groups_datafields_data', "WHERE `datafield_id` = '$dfid' AND `person_id` = '$id' AND `group_id` = '$gid' AND `deleted` = '0'");

				//Check if this person is assigned to the given group
				$person = db_select_data($table, "WHERE `id` = '$id'", '*', '', '', TRUE);
				if (FALSE === strpos($person['groups'], 'g' . $gid)) break;

				//Save changes for ko_leute
				ko_save_leute_changes($id, $person);

				//Update or insert value
				if (sizeof($dfdata) > 0) {
					$dfdata = array_shift($dfdata);
					$value = $dfdata['value'] == 1 ? '' : 1;
					db_update_data('ko_groups_datafields_data', "WHERE `id` = '" . $dfdata['id'] . "'", ['value' => $value]);
				} else {
					$value = 1;
					db_insert_data('ko_groups_datafields_data', ['group_id' => $gid, 'datafield_id' => $dfid, 'person_id' => $id, 'value' => $value]);
				}

				//Table's post function
				$ids = $id;
				$columns = $col;
				if (function_exists('kota_post_' . $table)) {
					eval("kota_post_$table(\$ids, \$columns, \$entry, \$do_save);");
				}
				hook_kota_post($table, ['table' => $table, 'ids' => $ids, 'columns' => $columns, 'old' => $entry, 'do_save' => $do_save]);

				//Output new value
				print $_GET['id'] . '@@@' . ($value ? getLL('yes') : getLL('no'));
			} else if ($table == 'ko_event' && substr($col, 0, 9) == 'rotateam_') {
				if ($access['rota']['MAX'] < 5) break;

				$event = db_select_data($table, "WHERE `id` = '$id'", '*', '', '', TRUE);
				if ($event['rota'] != 1) break;

				list($temp, $tid) = explode('_', $col);
				if ($access['rota'][$tid] < 5) break;

				if (ko_rota_team_is_in_event($tid, $event['id'])) {
					if (ko_rota_is_scheduling_disabled($event['id'], $tid)) {
						ko_rota_enable_scheduling($event['id'], $tid);
						$value = '';
					} else {
						$scheduled = ko_rota_get_schedule_by_event_team($event, $tid);
						if ($scheduled['schedule'] == '') {
							ko_rota_disable_scheduling($event['id'], $tid);
							$value = getLL('rota_status_closed');
						} else {
							print 'ERROR@@@' . getLL('error_rota_2');
							break;
						}
					}
				} else {
					$value = '-';
				}
				print $_GET['id'] . '@@@' . $value;
			} //For all other input types show form
			else {
				//Check for group with no or only one role - add/remove assignment directly
				if ($table == 'ko_leute' && substr($col, 0, 9) == 'MODULEgrp' && strlen($col) == 15) {
					$gid = format_userinput(substr($col, 9), 'uint');
					//Access check
					if ($access['groups']['ALL'] < 2 && $access['groups'][$gid] < 2) break;
					if ($gid) {
						$group = db_select_data('ko_groups', "WHERE `id` = '$gid'", '*', '', '', TRUE);
						$roles = explode(',', $group['roles']);
						if ($group['type'] == '0' && sizeof($roles) < 2) {
							//Get person from db
							$person = db_select_data($table, "WHERE `id` = '$id'", '*', '', '', TRUE);
							//Set only role if group has exactly one
							$rid = $group['roles'];
							if ($rid) $rid = ':r' . $rid;
							//Add or remove person from group
							$full_gid = ko_groups_decode($gid, 'full_gid') . $rid;
							if (!preg_match('/g' . $gid . $rid . '($|,)/', $person['groups'])) {
								$new_groups = $person['groups'] . ($person['groups'] != '' ? ',' : '') . $full_gid;
								$mode = 'add';
							} else {
								$curr_groups = explode(',', $person['groups']);
								foreach ($curr_groups as $k => $cg) {
									if (preg_match('/g' . $gid . $rid . '($|,)/', $cg)) unset($curr_groups[$k]);
								}
								$new_groups = implode(',', $curr_groups);
								$mode = 'remove';
							}

							ko_save_leute_changes($id, $person);
							db_update_data('ko_leute', "WHERE `id` = '$id'", ['groups' => $new_groups, 'lastchange' => date('Y-m-d H:i:s')]);

							// delete datafield data
							if ($mode == 'remove') db_delete_data('ko_groups_datafields_data', "where `person_id` = $id and `group_id` = $gid");

							//Table's post function
							$ids = $id;
							$columns = 'groups';
							if (function_exists('kota_post_' . $table)) {
								eval("kota_post_$table(\$ids, \$columns, \$entry, \$do_save);");
							}
							hook_kota_post($table, ['table' => $table, 'ids' => $ids, 'columns' => $columns, 'old' => $entry, 'do_save' => $do_save]);

							if ($mode == 'add') {
								$value = 'x';
								if ($rid) {
									$role = db_select_data('ko_grouproles', "WHERE `id` = '" . $group['roles'] . "'", '*', '', '', TRUE);
									$value = $role['name'];
								}
							} else {
								$value = '';
							}
							$redrawString = '';
							foreach ($redrawElements as $k => $v) {
								if (in_array($v['column'], $_SESSION["show_leute_cols"])) $redrawString .= '@@@' . $k . '@@@' . $v['value'];
							}
							print $_GET['id'] . '@@@' . $value . $redrawString;

							break;
						}
					}
				}

				$grp = ko_multiedit_formular($table, explode(',', $col), $id, '', '', TRUE, '', TRUE, 'inline');

				$inputs = FALSE;
				foreach ($grp as $i) {
					if ($i['forAll'] == 1) continue;
					foreach ($i['row'] as $row) {  //Get inputs from all rows
						foreach ($row['inputs'] as $in) {
							$inputs[] = $in;
						}
					}
				}

				if (is_array($inputs)) {
					$classes = ['inlineform'];
					$code = '';
					$smarty->assign('tpl_isInline', TRUE);
					foreach ($inputs as $input) {
						$input['add_class'] .= " form-element-inline";
						$smarty->assign('input', $input);
						$code .= $smarty->fetch('ko_formular_elements.tmpl');

						if ($input['type'] == "groupselect") {
							$code .= "<script type=\"text/javascript\">
								$('.groupselect.groupselect-left').each(function() {
									initList(1, $(this));
								});
							</script>";
						}
					}

					//Add submit button for input types doubleselect
					if (in_array($KOTA[$table][$col]['form']['type'], ['doubleselect'])
						|| ($table == 'ko_leute' && substr($col, 0, 9) == 'MODULEgrp' && FALSE !== strpos($col, ':') && $df['type'] == 'multiselect')) {
						$classes[] = 'if-doubleselect';
						$code .= '<div><button type="button" class="if_submit btn btn-primary btn-sm" name="if_submit" value="' . getLL('OK') . '">' . getLL('OK') . '</button></div>';
					}

					if ($code) print $_GET['id'] . '@@@<div class="' . implode(' ', $classes) . '" id="if_' . $_GET['id'] . '">' . $code . '</div>';
				}
			}
			break;


		case 'inlineformblur':
			$module = format_userinput($_GET['module'], 'alphanum+');

			list($table, $id, $col) = explode('|', $_GET['id']);
			$table = format_userinput($table, 'alphanum+');
			$id = format_userinput($id, 'uint');
			$col = format_userinput(str_replace(';', ',', $col), 'alphanumlist');
			$col = array_shift(explode(',', $col));

			if (!$table || !$col || !$id) break;

			ko_get_access($module);
			ko_include_kota([$table]);

			$data = db_select_data($table, "WHERE `id` = '$id'", '*', '', '', TRUE);

			if ($table == "ko_event" || $table == "ko_eventgruppen" || $table == "ko_groups") {
				$data['terms'] = '';
			}

			//Add column if a special column has been shown
			if (substr($col, 0, 6) == 'MODULE') $data[$col] = '';
			$kota_data = $data;
			kota_process_data($table, $kota_data, 'list', $log, $id);

			print $_GET['id'] . '@@@' . $kota_data[$col];
			break;


		case 'inlineformsubmit':
			$module = format_userinput($_GET['module'], 'alphanum+');

			list($table, $id, $col) = explode('|', $_GET['id']);
			$table = format_userinput($table, 'alphanum+');
			$id = format_userinput($id, 'uint');
			$orig_col = $col = format_userinput(str_replace(';', ',', $col), 'alphanumlist');
			//Sort columns (important for check in kota_submit_multiedit)
			$a = explode(',', $col);
			sort($a);
			$col = implode(',', $a);

			ko_get_access($module);
			ko_include_kota([$table]);

			//Replace masked commas
			foreach ($a as $c) {
				$_GET['koi'][$table][$c][$id] = str_replace('|', ',', $_GET['koi'][$table][$c][$id]);
				$_GET['koi'][$table][$c][$id] = str_replace('<br />', "\n", $_GET['koi'][$table][$c][$id]);
			}


			$_POST['koi'] = $_GET['koi'];
			$hash = md5(md5($mysql_pass . $table . str_replace(',', ':', $col) . $id));
			$_POST['id'] = $table . '@' . $col . '@' . $id . '@' . $hash;
			kota_submit_multiedit('', 'inline_edit');

			if (koNotifier::Instance()->hasErrors()) {
				print 'ERROR@@@';
				koNotifier::Instance()->display(TRUE);
				break;
			}

			//Check redraw conditions
			$redraw = FALSE;
			foreach ($KOTA[$table]['_inlineform']['redraw'] as $method => $value) {
				switch ($method) {
					case 'sort':
						foreach (explode(',', $orig_col) as $c) {
							if (isset($_SESSION[$value]) && (is_array($_SESSION[$value]) && in_array($c, $_SESSION[$value])) || (!is_array($_SESSION[$value]) && $c == $_SESSION[$value])) $redraw = TRUE;
						}
						break;
					case 'cols':
						if (!is_array($value)) $value = explode(',', $value);
						foreach (explode(',', $orig_col) as $c) {
							if (in_array($c, $value)) $redraw = TRUE;
						}
						break;
				}
			}

			//Redraw whole list if need be
			if ($redraw) {
				print 'main_content@@@';
				list($module, $file) = explode('|', $KOTA[$table]['_inlineform']['module']);
				$file = $file != '' ? $file : $module;
				include_once($ko_path . $module . '/inc/' . $file . '.inc');
				eval($KOTA[$table]['_inlineform']['redraw']['fcn']);
			} //Just redraw single table cell
			else {
				//Get record from DB
				$data = db_select_data($table, "WHERE `id` = '$id'", '*', '', '', TRUE);
				//Add column if a special column has been edited
				if (substr($col, 0, 6) == 'MODULE') $data[$col] = $_POST['koi'][$table][$col][$id];
				kota_process_data($table, $data, 'list', $log, $id);

				$redrawString = '';
				foreach ($redrawElements as $k => $v) {
					if (in_array($v['column'], $_SESSION["show_leute_cols"])) $redrawString .= '@@@' . $k . '@@@' . $v['value'];
				}

				//Get first column (if multiple) to return it's value
				$col = array_shift(explode(',', $orig_col));
				print $_GET['id'] . '@@@' . $data[$col] . $redrawString;
			}
			break;


		case 'kotafilter':
			$table = format_userinput($_GET['table'], 'alphanum+');
			$cols = format_userinput($_GET['cols'], 'alphanumlist');
			$module = format_userinput($_GET['module'], 'alphanum+');

			$sortEnabled = format_userinput($_GET['sortenabled'], 'uint');
			$filterEnabled = format_userinput($_GET['filterenabled'], 'uint');

			$r = '<form name="' . $table . '_' . $cols . '">';
			$show_clear = FALSE;
			ko_get_access($module);
			ko_include_kota([$table]);

			$supermodule = $KOTA[$table]['_supermodule'];
			if (!$supermodule) $supermodule = $module;

			if ($sortEnabled == '1') {

				$sortCol = format_userinput($_GET['sortcol'], 'text');
				$sortOrder = format_userinput($_GET['sortorder'], 'alphanum+');
				$sortAction = format_userinput($_GET['sortaction'], 'alphanum+');
				$sortBy = format_userinput($_GET['sortby'], 'text');
				if ($sortBy && $sortCol && $sortOrder && $sortAction) {
					$r .= '<div style="margin:0px auto;" class="btn-group btn-group-sm">';
					$r .= '<button class="btn btn-default" disabled>' . getLL('list_sorting') . '</button>';
					$r .= '<button class="btn btn-default" type="button" title="' . getLL('list_sort_desc') . '" ' . (($sortBy != $sortCol || $sortOrder == 'ASC') ? 'onclick="javascript:sendReq(\'../' . $supermodule . '/inc/ajax.php\', [\'action\',\'sort\',\'sort_order\',\'sesid\'], [\'' . $sortAction . '\',\'' . $sortBy . '\',\'DESC\',\'' . session_id() . '\'], do_element);"' : 'disabled') . '><span class="glyphicon glyphicon-chevron-down"></span></button>';
					$r .= '<button class="btn btn-default" type="button" title="' . getLL('list_sort_asc') . '" ' . (($sortBy != $sortCol || $sortOrder == 'DESC') ? 'onclick="javascript:sendReq(\'../' . $supermodule . '/inc/ajax.php\', [\'action\',\'sort\',\'sort_order\',\'sesid\'], [\'' . $sortAction . '\',\'' . $sortBy . '\',\'ASC\',\'' . session_id() . '\'], do_element);"' : 'disabled') . '><span class="glyphicon glyphicon-chevron-up"></span></button>';
					$r .= '</div>';
				}
			}
			$r1 = $r;
			$r = '';


			if ($filterEnabled == '1') {
				$r .= kota_get_filter_form($table, $cols, TRUE, TRUE, '');
			}

			$r = $r1 . ($r1 != '' && $r != '' ? '<hr style="margin-top:8px;margin-bottom:8px;">' : '') . $r;

			if ($r != '') {
				$r = getLL('kota_filter_title') . '@@@' . $r;
			}


			if ($applied_filters = kota_get_applied_filter($table, $cols)) {
				$r .= "<h4>" . getLL('kota_filter_current') . "</h4>";
				$r .= "<ul class='applied_kota_filter'><li>";
				$r .= implode("</li><li>", $applied_filters);
				$r .= "</li></ul>";
			}

			$r .= "</form>";
			print $r;
			break;


		case 'kotafiltersubmit':
			$module = format_userinput($_GET['module'], 'alphanum+');
			ko_get_access($module);

			//Include KOTA for the submitted table
			$ok = FALSE;
			$done = [];
			foreach ($_GET['kota_filter'] as $k => $v) {
				list($table, $col) = explode(':', $k);
				if (in_array($table, $done)) continue;
				$done[] = $table;
				ko_include_kota([$table]);
				if (!isset($KOTA[$table]) || !isset($KOTA[$table][$col])) continue;
				$ok = TRUE;
				break;
			}
			if (!$ok) break;

			//Store filter in session
			foreach ($_GET['kota_filter'] as $k => $v) { // this loop is copied in leute/inc/ajax.inc
				list($table, $col) = explode(':', $k);

				$type = $KOTA[$table][$col]['filter']['type'];
				if (!$type) $type = $KOTA[$table][$col]['form']['type'];

				if (!isset($KOTA[$table]) || !isset($KOTA[$table][$col])) continue;

				$biggestId = -1;
				foreach ($_SESSION['kota_filter'][$table][$col] AS $currentId => $filter) {
					if ($biggestId < $currentId) $biggestId = $currentId;
				}

				$nextId = $biggestId + 1;

				if ($type == 'jsdate') {
					$v_from = $v['from'];
					$v_to = $v['to'];

					if ($_GET['neg'] == 1) {
						$_SESSION['kota_filter'][$table][$col][$nextId]['neg'] = TRUE;
					} else {
						$_SESSION['kota_filter'][$table][$col][$nextId]['neg'] = FALSE;
					}
					// depending on datepicker, do preprocessing
					$_SESSION['kota_filter'][$table][$col][$nextId]['from'] = $v_from;
					$_SESSION['kota_filter'][$table][$col][$nextId]['to'] = $v_to;
				} else {
					//Replace | with , again
					$v = str_replace('|', ',', $v);

					//Add negation if checkbox was set
					if ($_GET['neg'] == 1) $v = '!' . $v;

					$_SESSION['kota_filter'][$table][$col][$nextId] = $v;
				}
			}

			//Store userpref
			if (ko_get_userpref($_SESSION['ses_userid'], 'save_kota_filter') == 1) {
				ko_save_userpref($_SESSION['ses_userid'], 'kota_filter', serialize($_SESSION['kota_filter']));
			}

			print 'main_content@@@';
			list($module, $file) = explode('|', $KOTA[$table]['_inlineform']['module']);
			$file = $file != '' ? $file : $module;
			include_once($ko_path . $module . '/inc/' . $file . '.inc');
			eval($KOTA[$table]['_inlineform']['redraw']['fcn']);

			break;


		case 'kotafilterclear':
			$module = format_userinput($_GET['module'], 'alphanum+');
			list($table, $cols, $id) = explode(':', $_GET['id']);
			ko_get_access($module);
			ko_include_kota([$table]);
			foreach (explode('|', $cols) as $col) {
				if (isset($id)) {
					unset($_SESSION['kota_filter'][$table][$col][$id]);
					if (empty($_SESSION['kota_filter'][$table][$col])) {
						unset($_SESSION['kota_filter'][$table][$col]);
					}
				} else {
					unset($_SESSION['kota_filter'][$table][$col]);
				}
			}

			//Store userpref
			if (ko_get_userpref($_SESSION['ses_userid'], 'save_kota_filter') == 1) {
				ko_save_userpref($_SESSION['ses_userid'], 'kota_filter', serialize($_SESSION['kota_filter']));
			}

			print 'main_content@@@';
			list($module, $file) = explode('|', $KOTA[$table]['_inlineform']['module']);
			$file = $file != '' ? $file : $module;
			include_once($ko_path . $module . '/inc/' . $file . '.inc');
			eval($KOTA[$table]['_inlineform']['redraw']['fcn']);
			break;


		case 'kotaitemlist':
			$table = format_userinput($_GET['table'], 'js');
			ko_include_kota([$table]);
			$module = $KOTA[$table]['_access']['module'];
			ko_get_access($module);
			if (isset($KOTA[$table]['_supermodule'])) $supermodule = $KOTA[$table]['_supermodule'];
			else $supermodule = $module;
			require_once($ko_path . $supermodule . '/inc/' . $module . '.inc');

			//ID and state of the clicked field
			$id = format_userinput($_GET['id'], 'js');
			$state = $_GET['state'] == 'true' ? 'checked' : '';

			if ($state == 'checked') {
				// place the new selected column in correct order according to $KOTA[$table][_listview]
				if (!in_array($id, $_SESSION['kota_show_cols_' . $table])) {
					$found_on_position = FALSE;
					$found = FALSE;
					$ordered_listview = [];
					foreach ($KOTA[$table]['_listview'] AS $db_column) {
						$ordered_listview[] = $db_column['name'];
					}

					$found_on_position = array_search($id, $ordered_listview);
					if ($found_on_position > 0) {
						while ($found_on_position > 0) {
							$found_on_position--;
							if (in_array($ordered_listview[$found_on_position], $_SESSION['kota_show_cols_' . $table])) {
								$found = TRUE;
								break;
							}
						}
					}

					if ($found === TRUE) {
						$previous_column = $ordered_listview[$found_on_position];
						$insert_after = array_search($previous_column, $_SESSION['kota_show_cols_' . $table]) + 1;
						array_splice($_SESSION['kota_show_cols_' . $table], $insert_after, 0, $id);
					} else {
						array_unshift($_SESSION['kota_show_cols_' . $table], $id);
					}
				}
			} else {
				if (in_array($id, $_SESSION['kota_show_cols_' . $table])) {
					$_SESSION['kota_show_cols_' . $table] = array_diff($_SESSION['kota_show_cols_' . $table], [$id]);
				}
			}

			ko_save_userpref($_SESSION['ses_userid'], 'kota_show_cols_' . $table, implode(',', $_SESSION['kota_show_cols_' . $table]));

			print 'main_content@@@';
			eval($KOTA[$table]['_inlineform']['redraw']['fcn']);
			print '<script>$(".popover.in").remove();$("#ko_list_colitemlist_click").popover("show");</script>';
			break;


		case 'kotaitemlistsave':
			$table = format_userinput($_GET['table'], 'js');
			ko_include_kota([$table]);
			$module = $KOTA[$table]['_access']['module'];
			ko_get_access($module);
			if (isset($KOTA[$table]['_supermodule'])) $supermodule = $KOTA[$table]['_supermodule'];
			else $supermodule = $module;
			require_once($ko_path . $supermodule . '/inc/' . $module . '.inc');

			//save new value
			if ($_GET['name'] == '') break;
			$new_value = implode(',', $_SESSION['kota_show_cols_' . $table]);
			$user_id = $access[$module]['MAX'] > 3 && $_GET['global'] == 'true' ? '-1' : $_SESSION['ses_userid'];
			$name = format_userinput($_GET['name'], 'js', FALSE, 0, ['allquotes']);
			ko_save_userpref($user_id, $name, $new_value, $table . '_colitemset');

			ko_get_login($_SESSION['ses_userid'], $loggedIn);
			$nameForOthers = "{$name} ({$loggedIn['login']})";

			$logins = trim($_GET['logins']);
			if ($logins) {
				$logins = explode(',', $logins);
				foreach ($logins as $lid) {
					$lid = format_userinput($lid, 'uint');
					if (!$lid) continue;
					if ($lid == ko_get_root_id() || $lid == $_SESSION['ses_userid']) continue;

					$n = $nameForOthers;
					$c = 0;
					while (ko_get_userpref($lid, $n, $table . '_colitemset')) {
						$c++;
						$n = "{$nameForOthers} - {$c}";
					}
					ko_save_userpref($lid, $n, $new_value, $table . '_colitemset');
				}
			}

			print 'main_content@@@';
			eval($KOTA[$table]['_inlineform']['redraw']['fcn']);
			print '<script>$(".popover.in").remove();$("#ko_list_colitemlist_click").popover("show");</script>';
			break;


		case 'kotaitemlistopen':
			$table = format_userinput($_GET['table'], 'js');
			ko_include_kota([$table]);
			$module = $KOTA[$table]['_access']['module'];
			ko_get_access($module);
			if (isset($KOTA[$table]['_supermodule'])) $supermodule = $KOTA[$table]['_supermodule'];
			else $supermodule = $module;
			require_once($ko_path . $supermodule . '/inc/' . $module . '.inc');

			//save new value
			$name = format_userinput($_GET['name'], 'js', FALSE, 0, [], '@');
			if ($name == '') break;

			if ($name == '_all_') {
				$possible_cols = [];
				foreach ($KOTA[$table]['_listview'] as $c) {
					if ($c['name'] && $c['name'] != 'rotateam_0') $possible_cols[] = $c['name'];
				}
				$_SESSION['kota_show_cols_' . $table] = $possible_cols;
			} else if ($name == '_none_') {
				$_SESSION['kota_show_cols_' . $table] = [];
			} else {
				if (substr($name, 0, 3) == '@G@') $value = ko_get_userpref('-1', substr($name, 3), $table . '_colitemset');
				else $value = ko_get_userpref($_SESSION['ses_userid'], $name, $table . '_colitemset');
				$_SESSION['kota_show_cols_' . $table] = explode(',', $value[0]['value']);
			}
			ko_save_userpref($_SESSION['ses_userid'], 'kota_show_cols_' . $table, $value[0]['value']);

			print 'main_content@@@';
			eval($KOTA[$table]['_inlineform']['redraw']['fcn']);
			print '<script>$(".popover.in").remove();$("#ko_list_colitemlist_click").popover("show");</script>';
			break;


		case 'kotaitemlistdelete':
			$table = format_userinput($_GET['table'], 'js');
			ko_include_kota([$table]);
			$module = $KOTA[$table]['_access']['module'];
			ko_get_access($module);
			if (isset($KOTA[$table]['_supermodule'])) $supermodule = $KOTA[$table]['_supermodule'];
			else $supermodule = $module;
			require_once($ko_path . $supermodule . '/inc/' . $module . '.inc');

			//save new value
			$name = format_userinput($_GET['name'], 'js', FALSE, 0, [], '@');
			if ($name == '') break;

			if (substr($name, 0, 3) == '@G@') {
				if ($access[$module]['MAX'] > 3) ko_delete_userpref('-1', substr($name, 3), $table . '_colitemset');
			} else ko_delete_userpref($_SESSION['ses_userid'], $name, $table . '_colitemset');

			print 'main_content@@@';
			eval($KOTA[$table]['_inlineform']['redraw']['fcn']);
			print '@@@POST@@@$(".popover-overlay.in").remove();$("#ko_list_colitemlist_click").popover("show");';
			break;


		//Show form for new ft entry
		case 'ftnew':
			$field = format_userinput($_GET['field'], 'js');
			list($ptable, $col) = explode('.', $field);
			$after = format_userinput($_GET['after'], 'uint');
			$pid = format_userinput($_GET['pid'], 'alphanum');

			ko_include_kota([$ptable]);
			$table = $KOTA[$ptable][$col]['form']['table'];
			ko_include_kota([$table]);

			if (!$KOTA[$ptable][$field]['form']['readonly'] && !$KOTA[$table]['_access']['readonly']) {
				print 'ft_content_' . $field . '@@@';
				print kota_ft_get_content($field, $pid, $after);

				$active_module = $KOTA[$ptable]['_access']['module'];
				print kota_ajax_convert_textareas_to_rte($active_module);
			}
			break;

		//sort ft entries
		case 'ftsort':
			$field = format_userinput($_GET['field'], 'js');
			list($ptable, $col) = explode('.', $field);
			$pid = format_userinput($_GET['pid'], 'alphanum');

			ko_include_kota([$ptable]);
			$table = $KOTA[$ptable][$col]['form']['table'];
			$sortCols = $KOTA[$ptable][$col]['form']['sort_button'];
			if (!$sortCols) break;

			$entries = db_select_data($table, "WHERE `pid` = '{$pid}'", '*', "ORDER BY {$sortCols}");
			$counter = 0;
			foreach ($entries as $entry) {
				db_update_data($table, "WHERE `id` = '{$entry['id']}'", ['sorting' => ++$counter]);
			}


			ko_log_diff('ft_sort_' . $table, "{$ptable} (id {$pid}) -> {$field}: entries sorted in {$table} by {$sortCols}");

			print 'ft_content_' . $field . '@@@';
			print kota_ft_get_content($field, $pid);

			$active_module = $KOTA[$ptable]['_access']['module'];
			print kota_ajax_convert_textareas_to_rte($active_module);

			break;

		//Add presets to ft
		case 'ftloadpresets':
			$field = format_userinput($_GET['field'], 'js');
			list($ptable, $col) = explode('.', $field);
			$after = format_userinput($_GET['after'], 'uint');
			$pid = format_userinput($_GET['pid'], 'alphanum');
			$presetTable = format_userinput($_GET['preset_table'], 'alphanum+');
			$joinValueLocal = format_userinput($_GET['join_value_local'], 'alphanum+');
			$joinColumnForeign = format_userinput($_GET['join_column_foreign'], 'alphanum+');

			ko_include_kota([$ptable]);

			// Check if presets are allowed
			if (!isset($KOTA[$ptable][$col]['form']['foreign_table_preset'])) break;

			if (substr($KOTA[$ptable][$col]['form']['foreign_table_preset']['check_access'], 0, 4) == 'FCN:') {
				$fcn = substr($KOTA[$ptable][$col]['form']['foreign_table_preset']['check_access'], 4);
				if (function_exists($fcn)) {
					eval("$fcn(\$pid, \$joinValueLocal, \$result);");
					if (!$result) break;
				}
			}

			$lTable = $KOTA[$ptable][$col]['form']['table'];

			// check if there is a `sorting` column, if yes, sort by it
			$foreignColumns = db_get_columns($presetTable);
			$sortingPresent = FALSE;
			foreach ($foreignColumns as $foreignColumn) {
				if ($foreignColumn['Field'] == 'sorting') $sortingPresent = TRUE;
			}
			if ($sortingPresent) {
				$orderBy = 'order by `sorting` asc';
			} else {
				$orderBy = '';
			}

			// get preset values
			$presetValues = db_select_data($presetTable, "where `" . $joinColumnForeign . "` = '" . $joinValueLocal . "'", '*', $orderBy);

			// check if there is a `sorting` column, if yes, continue with max in new entries
			$localColumns = db_get_columns($lTable);
			$sortingPresent = FALSE;
			foreach ($localColumns as $localColumn) {
				if ($localColumn['Field'] == 'sorting') $sortingPresent = TRUE;
			}
			if ($sortingPresent) {
				$sortingMax = db_select_data($lTable, 'where 1=1', 'max(`sorting`)', '', '', TRUE, TRUE);
				$sortingMax = $sortingMax['max(`sorting`)'];
			} else {
				$sortingMax = -1;
			}

			// insert presets into local table
			foreach ($presetValues as $presetValue) {
				if ($sortingMax > -1) $presetValue['sorting'] = ++$sortingMax;

				if ($KOTA[$lTable][$col]['form']['sort_col']) unset($presetValue['sorting']);
				unset($presetValue['id']);
				$presetValue['pid'] = $pid;

				db_insert_data($lTable, $presetValue);
			}

			print 'ft_content_' . $field . '@@@';
			print kota_ft_get_content($field, $pid);

			$active_module = $KOTA[$ptable]['_access']['module'];
			print kota_ajax_convert_textareas_to_rte($active_module);

			break;


		//Store new ft entry in db
		case 'ftsave':
			$field = format_userinput($_GET['field'], 'js');
			list($ptable, $col) = explode('.', $field);
			$after = format_userinput($_GET['after'], 'uint');
			$pid = format_userinput($_GET['pid'], 'alphanum');

			ko_include_kota([$ptable]);

			//Prepare new db entry
			$table = $KOTA[$ptable][$col]['form']['table'];
			ko_include_kota([$table]);

			if (!$KOTA[$ptable][$field]['form']['readonly'] && !$KOTA[$table]['_access']['readonly']) {
				$new = ['pid' => $pid, 'crdate' => date('Y-m-d H:i:s'), 'cruser' => $_SESSION['ses_userid']];
				foreach ($_GET as $k => $v) {
					if (in_array($k, ['action', 'field', 'pid', 'after', 'sesid'])) continue;
					$new[$k] = $v;
				}
				kota_process_data($table, $new, 'post', $log);


				if (!$KOTA[$ptable][$col]['form']['sort_col']) {
					//Find right sorting
					$sorting = 0;
					$inc = 0;
					$max = 0;
					$aa = db_select_data($table, "WHERE `pid` = '$pid'", '*', 'ORDER BY sorting ASC');
					if (sizeof($aa) > 0) {
						foreach ($aa as $a) {
							$max = max($max, $a['sorting']);
							//after==0 --> insert at the beginning (but only once, when inc is still 0)
							if ($after == 0 && $inc == 0) {
								$inc = 1;
								$sorting = $a['sorting'];
							}
							//Move later entries to the back
							if ($inc > 0) {
								db_update_data($table, "WHERE `id` = '" . $a['id'] . "'", ['sorting' => $a['sorting'] + $inc]);
							}
							if ($a['id'] == $after) {
								$inc = 1;
								$sorting = $a['sorting'] + 1;
							}
						}
					}
					if ($sorting == 0) $sorting = $max + 1;

					$new['sorting'] = $sorting;
				}


				$newId = db_insert_data($table, $new);

				// Allow plugins to respond to changes
				hook_kota_post($table, ['table' => $table, 'ids' => $newId, 'columns' => array_keys($new), 'old' => NULL, 'do_save' => TRUE]);

				ko_log_diff('ft_new_' . $table, $new);

				print 'ft_content_' . $field . '@@@';
				print kota_ft_get_content($field, $pid);

				$active_module = $KOTA[$ptable]['_access']['module'];
				print kota_ajax_convert_textareas_to_rte($active_module);
			}
			break;


		//Update ft entry in db
		case 'ftedit':
			$field = format_userinput($_GET['field'], 'js');
			list($ptable, $col) = explode('.', $field);
			$id = format_userinput($_GET['id'], 'uint');
			$pid = format_userinput($_GET['pid'], 'alphanum');

			ko_include_kota([$ptable]);
			$table = $KOTA[$ptable][$col]['form']['table'];
			ko_include_kota([$table]);

			if (!$KOTA[$ptable][$field]['form']['readonly'] && !$KOTA[$table]['_access']['readonly']) {
				//Get current entry
				$old = db_select_data($table, "WHERE `id` = '$id'", '*', '', '', TRUE);
				if (!$old['id'] || $old['id'] != $id) break;

				//Update db entry
				$data = [];
				foreach ($_GET as $k => $v) {
					if (in_array($k, ['action', 'field', 'pid', 'after', 'sesid'])) continue;
					$data[$k] = $v;
				}
				kota_process_data($table, $data, 'post', $log, $id);
				db_update_data($table, "WHERE `id` = '$id'", $data);

				// Allow plugins to respond to changes
				hook_kota_post($table, ['table' => $table, 'ids' => $id, 'columns' => array_keys($data), 'old' => $old, 'do_save' => TRUE]);

				ko_log_diff('ft_edit_' . $table, $data, $old);

				print 'ft_content_' . $field . '@@@';
				print kota_ft_get_content($field, $pid);

				$active_module = $KOTA[$ptable]['_access']['module'];
				print kota_ajax_convert_textareas_to_rte($active_module);
			}
			break;


		//Delete ft entry in db
		case 'ftdelete':
			$field = format_userinput($_GET['field'], 'js');
			list($ptable, $col) = explode('.', $field);
			$id = format_userinput($_GET['id'], 'uint');
			$pid = format_userinput($_GET['pid'], 'alphanum');

			ko_include_kota([$ptable]);
			$table = $KOTA[$ptable][$col]['form']['table'];
			ko_include_kota([$table]);

			if (!$KOTA[$ptable][$field]['form']['readonly'] && !$KOTA[$table]['_access']['readonly'] && !$KOTA[$ptable][$field]['form']['nodelete'] && !$KOTA[$table]['_access']['nodelete']) {
				//Get current entry
				$old = db_select_data($table, "WHERE `id` = '$id'", '*', '', '', TRUE);
				if (!$old['id'] || $old['id'] != $id) break;

				db_delete_data($table, "WHERE `id` = '$id'");
				ko_log_diff('ft_delete_' . $table, $old);

				print 'ft_content_' . $field . '@@@';
				print kota_ft_get_content($field, $pid);

				$active_module = $KOTA[$ptable]['_access']['module'];
				print kota_ajax_convert_textareas_to_rte($active_module);
			}
			break;


		//Move ft entry up or down (in db)
		case 'ftmove':
			$field = format_userinput($_GET['field'], 'js');
			list($ptable, $col) = explode('.', $field);
			$id = format_userinput($_GET['id'], 'uint');
			$pid = format_userinput($_GET['pid'], 'uint');

			ko_include_kota([$ptable]);
			$table = $KOTA[$ptable][$col]['form']['table'];

			if (!$KOTA[$ptable][$field]['form']['readonly'] && !$KOTA[$table]['_access']['readonly']) {
				$direction = $_GET['direction'];
				if (in_array($direction, ['up', 'down'])) {
					$sort = $direction == 'up' ? 'DESC' : 'ASC';
					$aa = array_values(db_select_data($table, "WHERE `pid` = '$pid'", '*', 'ORDER BY sorting ' . $sort));
					if (sizeof($aa) > 1) {
						foreach ($aa as $k => $a) {
							if ($a['id'] == $id && isset($aa[$k + 1])) {
								$id1 = $a['id'];
								$id2 = $aa[$k + 1]['id'];
								$sort1 = $a['sorting'];
								$sort2 = $aa[$k + 1]['sorting'];
								db_update_data($table, "WHERE `id` = '$id1'", ['sorting' => $sort2]);
								db_update_data($table, "WHERE `id` = '$id2'", ['sorting' => $sort1]);
							}
						}
					}
					print 'ft_content_' . $field . '@@@';
					print kota_ft_get_content($field, $pid);

					$active_module = $KOTA[$ptable]['_access']['module'];
					print kota_ajax_convert_textareas_to_rte($active_module);
				}
			}
			break;


		case 'peoplefilterform':
			$fid = format_userinput($_GET['fid'], 'uint');
			if (!$fid) break;
			$field = $_GET['field'];
			$code = ko_get_leute_filter_form($fid, FALSE);
			print 'peoplefilter_vars_' . $field . '@@@' . $code;

			//group filter
			ko_get_filter_by_id($fid, $current_filter);
			if ($current_filter['_name'] == 'group') print "@@@POST@@@filter_group";
			break;


		case 'tablesort':
			$diff = format_userinput($_GET['diff'], 'int');
			$id = format_userinput($_GET['id'], 'uint');
			$module = format_userinput($_GET['module'], 'alphanum');
			$table = format_userinput($_GET['table'], 'alphanum+');
			if (!$id || !$module || !$table || !$diff) break;

			ko_get_access($module);
			ko_include_kota([$table]);
			if (!isset($KOTA[$table])) break;

			$sorted = [];
			$items = db_select_data($table, "WHERE 1", 'id,sort', 'ORDER BY sort ASC');
			$sort = 0;
			foreach ($items as $item) {
				$sorted[$sort] = $item['id'];
				if ($item['id'] == $id) $oldpos = $sort;
				$sort++;
			}
			$newpos = $oldpos + $diff;

			$out = array_splice($sorted, $oldpos, 1);
			array_splice($sorted, $newpos, 0, $out);

			foreach ($sorted as $sort => $id) {
				db_update_data($table, "WHERE `id` = '$id'", ['sort' => $sort + 1]);
			}

			print 'main_content@@@';
			require_once($ko_path . $module . '/inc/' . $module . '.inc');
			eval($KOTA[$table]['_inlineform']['redraw']['fcn']);
			break;

		case 'getcert':
			ko_get_access('tracking');
			ko_get_access('admin');
			if ($access['admin']['MAX'] < 5 && $access['tracking']['MAX'] < 1 && !isset($_SESSION['checkin_printer'])) break;

			header('Content-type: text/plain');
			echo ko_get_self_signed_cert();
			break;

		case 'rsasign':
			ko_get_access('tracking');
			ko_get_access('admin');
			if ($access['admin']['MAX'] < 5 && $access['tracking']['MAX'] < 1 && !isset($_SESSION['checkin_printer'])) break;

			$toSign = $_GET['sign'];

			$rsa = new \phpseclib\Crypt\RSA();
			$rsa->setSignatureMode(\phpseclib\Crypt\RSA::SIGNATURE_PKCS1);
			$rsa->loadKey(ko_get_private_key());

			header('Content-type: text/plain');
			echo base64_encode($rsa->sign($toSign));
			break;

		case 'savecolumnorder':
			$table = "";
			foreach ($_GET AS $key => $order) {
				if (stristr($key, ":")) {
					list($table, $column) = explode(":", $key, 2);
					if (!empty($table) && !empty($column)) {
						$columns[$order] = $column;
					}
				}
			}

			ksort($columns);

			if (sizeof($columns) > 0) {
				if ($table == "ko_leute") {
					$_SESSION['show_leute_cols'] = array_values($columns);
					ko_save_userpref($_SESSION['ses_userid'], 'show_leute_cols', implode(',', $columns));
				} else {
					$_SESSION['kota_show_cols_' . $table] = array_values($columns);
					ko_save_userpref($_SESSION['ses_userid'], 'kota_show_cols_' . $table, implode(',', $columns));
				}
			}
			break;
	}//switch(action);

	hook_ajax_post($ko_menu_akt, $action);

}//if(GET[action])
?>
