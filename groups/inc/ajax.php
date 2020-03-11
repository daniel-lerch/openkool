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

if($_GET["action"] != "grouproleselectfilter") {
	//Set session id from GET (session will be started in ko.inc)
	if(!isset($_GET["sesid"])) exit;
	if(FALSE === session_id($_GET["sesid"])) exit;
}

//Send headers to ensure latin1 charset
header('Content-Type: text/html; charset=ISO-8859-1');

error_reporting(0);
$ko_menu_akt = 'groups';
$ko_path = "../../";
require($ko_path."inc/ko.inc");
$ko_path = "../";

array_walk_recursive($_GET,'utf8_decode_array');

//Get access rights
ko_get_access('groups');

ko_include_kota(array('ko_groups', 'ko_grouproles', 'ko_groups_datafields'));

// Plugins einlesen:
$hooks = hook_include_main("groups");
if(sizeof($hooks) > 0) foreach($hooks as $hook) include_once($hook);

require($BASE_PATH."groups/inc/groups.inc");

//HOOK: Submenus einlesen
$hooks = hook_include_sm();
if(sizeof($hooks) > 0) foreach($hooks as $hook) include($hook);

hook_show_case_pre($_SESSION['show']);


if(isset($_GET) && isset($_GET["action"])) {
	$action = format_userinput($_GET["action"], "alphanum");

	hook_ajax_pre($ko_menu_akt, $action);

	switch($action) {
		case "grouproleselect":
			$data = array();
			$groupid = format_userinput($_GET["group_id"], "uint", FALSE, 0, array(), "gr:");
			$group = ko_groups_decode($groupid, "group");

			//Gruppe selber hinzufügen
			$data[] = array("value" => $groupid, "desc" => $group["name"]);

			//Check for maxcount
			$group_full = FALSE;
			$role_full = '';
			if($group['maxcount'] > 0 && $group['count'] >= $group['maxcount']) {
				if($group['count_role']) $role_full = $group['count_role'];
				else $group_full = TRUE;
			}

			if(!$group_full && ($access['groups']['ALL'] > 1 || $access['groups'][$group['id']] > 1)) {
				foreach(explode(",", $group["roles"]) as $role) {
					if($role != "" && (string)$role != "000000" && $role_full != $role) {
						ko_get_grouproles($roles, "AND `id` = '$role'");
						$data[] = array("value" => ($groupid.":r".$roles[$role]["id"]), "desc" => $group["name"].": ".$roles[$role]["name"]);
					}
				}

				$r = "";
				foreach($data as $line) {
					$r .= $line["value"].",".$line["desc"];
					$r .= "#";
				}
				$r = substr($r, 0, -1);

				print $r;
			}//if(access)
		break;

		case "grouproleselectfilter":
			$data = array();
			$groupid = format_userinput($_GET["group_id"], "uint", FALSE, 0, array(), "gr:");
			$group = ko_groups_decode($groupid, "group");

			//Gruppe selber hinzufügen
			$data[] = array("value" => "", "desc" => getLL("all"));

			//Berechtigungen checken
			foreach(explode(",", $group["roles"]) as $role) {
				if($role != "" && (string)$role != "000000") {
					ko_get_grouproles($roles, "AND `id` = '$role'");
					$data[] = array("value" => (":r".$roles[$role]["id"]), "desc" => $group["name"].": ".$roles[$role]["name"]);
				}
			}

			$r = "";
			foreach($data as $line) {
				$r .= $line["value"].",".$line["desc"];
				$r .= "#";
			}
			$r = substr($r, 0, -1);

			print $r;
		break;


		case 'setsort':
			if($access['groups']['MAX'] < 3) break;

			$_SESSION['sort_groups'] = format_userinput($_GET['sort'], 'alphanum+', TRUE, 30);
			ko_save_userpref($_SESSION['ses_userid'], 'sort_groups', $_SESSION['sort_groups']);
			$_SESSION['sort_groups_order'] = format_userinput($_GET['sort_order'], 'alpha', TRUE, 4);
			ko_save_userpref($_SESSION['ses_userid'], 'sort_groups_order', $_SESSION['sort_groups_order']);

			print 'main_content@@@';
			ko_groups_list();
		break;

		case "setstart":
			if($access['groups']['MAX'] < 1) break;

			//Set list start
			if(isset($_GET['set_start'])) {
				$_SESSION['show_start'] = max(1, format_userinput($_GET['set_start'], 'uint'));
	    }
			//Set list limit
			if(isset($_GET['set_limit'])) {
				$_SESSION['show_limit'] = max(1, format_userinput($_GET['set_limit'], 'uint'));
				ko_save_userpref($_SESSION['ses_userid'], 'show_limit_groups', $_SESSION['show_limit']);
	    }

			print "main_content@@@";
			ko_groups_list();
		break;


		case "adddatafield":
			if($access['groups']['MAX'] < 2) break;

			$description = format_userinput(urldecode($_GET['descr']), 'text', FALSE, 0, array('allquotes' => TRUE));
			$type = format_userinput(urldecode($_GET['type']), 'alpha');
			$reusable = format_userinput($_GET['reusable'], 'uint');
			$private = format_userinput($_GET['private'], 'uint');
			$preset = format_userinput($_GET['preset'], 'uint');
			if($type == 'select' || $type == 'multiselect') {
				$options = explode("\n", urldecode($_GET['options']));
				$save_options = NULL;
				foreach($options as $o) $save_options[] = trim($o);
				$options = serialize($save_options);
			}
			else $options = '';

			$prefix = '';
			if($preset) $prefix .= '['.getLL('groups_datafields_preset_short').'] ';
			if($reusable) $prefix .= '['.getLL('groups_datafields_reusable_short').'] ';
			if($private) $prefix .= '['.getLL('groups_datafields_private_short').'] ';

			$new_id = zerofill(db_insert_data('ko_groups_datafields', array('description' => $description, 'type' => $type, 'reusable' => $reusable, 'private' => $private, 'preset' => $preset, 'options' => $options)), 6);

			print $new_id.'#'.$prefix.$description.' ('.getLL('groups_datafields_'.$type).')';
		break;


		/**
		  * Adds the filter options for a specified group datafield according to its type
			*/
		case "groupdatafieldsfilter":
			$dfid = format_userinput($_GET["dfid"], "uint");
			$df = db_select_data("ko_groups_datafields", "WHERE `id` = '$dfid'", "*", "", "", TRUE);
			if(!$df["type"]) break;

			switch($df["type"]) {
				case "checkbox":
					$code = '<select name="var2" size="0"><option value="1">'.getLL("yes").'</option><option value="">'.getLL("no").'</option></select>';
				break;

				case "select":
				case "multiselect":
					$code = '<select name="var2" size="0"><option value=""></option>';
					foreach(unserialize($df["options"]) as $option) {
						$code .= '<option value="'.$option.'">'.$option.'</option>';
					}
					$code .= '</select>';
				break;

				case "text":
				case "textarea":
					$code = '<input type="text" name="var2" size="12" maxlength="200" onkeydown="if ((event.which == 13) || (event.keyCode == 13)) { this.form.submit_filter.click(); return false;} else return true;" />';
				break;
			}

			print "groups_datafields_filter@@@";
			print '<div name="groups_datafields_filter">'.$code.'</div>';
		break;



		case 'addgrouptracking':
			$id = format_userinput($_GET['id'], 'uint');

			$group = db_select_data('ko_groups', 'WHERE `id` = \''.$id.'\'', '*', '', '', TRUE);
			if(!$group['id']) break;

			//Find event this group holds subscriptions for
			//If so, then use the dates of this event for the tracking as well
			$event = db_select_data('ko_event', "WHERE `gs_gid` = 'g$id'", '*', '', '', TRUE);
			if($event['id'] > 0) {
				$_dates = array();
				$date1 = str_replace('-', '', $event['startdatum']);
				$date2 = str_replace('-', '', $event['enddatum']);
				while($date1 <= $date2) {
					$date = substr($date1, 0, 4).'-'.substr($date1, 4, 2).'-'.substr($date1, 6, 2);
					$_dates[] = $date;
					$date1 = str_replace('-', '', add2date($date, 'day', 1, TRUE));
				}
				$dates = implode(',', $_dates);
			} else {
				$dates = '';
			}

			$tracking = array('name' => $group['name'],
												'mode' => 'simple',
												'filter' => 'g'.$group['id'],
												'dates' => $dates,
												);

			//Call plugin if set
			$data = array('tracking' => $tracking, 'group' => $group);
			hook_ajax_inline($ko_menu_akt, $action, $data);
			$tracking = $data['tracking'];

			$new_id = db_insert_data('ko_tracking', $tracking);
			ko_log_diff('new_tracking', $tracking);

			//Call plugin again after insert
			$data = array('tracking' => $tracking, 'group' => $group, 'new_id' => $new_id);
			hook_ajax_inline($ko_menu_akt, $action, $data);

			print 'main_content@@@';
			ko_groups_list();
		break;


		case 'submitgeneralsearch':
			if($access['groups']['MAX'] < 1) break;
			$value = format_userinput($_GET['value'], 'text');

			$_SESSION['groups_search'] = $value;

			print 'general-search-li@@@';
			print ko_get_searchbox_code('groups', 'general_only');

			// draw result list
			print '@@@';
			print 'main_content@@@';
			ko_groups_list_search_results();
		break;


		case 'submittaxonomysearch':
			ko_get_access("taxonomy");
			if($access['taxonomy']['MAX'] < 1) break;
			$value = format_userinput($_GET['value'], 'text');

			$_SESSION['taxonomy_search'] = $value;


			print 'general-search-li@@@';
			print ko_get_searchbox_code('groups', 'general_only');

			// draw result list
			print '@@@';
			print 'main_content@@@';
			print ko_groups_list_taxonomy_search_results();
		break;


		case 'getassignmenthistory':
			$groupId = format_userinput($_GET['gid'], 'uint');
			if ($access['groups']['ALL'] < 1 && $access['groups'][zerofill($groupId, 6)] < 1) break;

			print ko_groups_get_assignment_timeline('group', 'groups-assignment-history', $groupId);
		break;


		case 'groupsearchids':
			$ids = explode(',', $_GET['ids']);
			$result = kota_groupselect($ids);
			array_walk_recursive($result, 'utf8_encode_array');

			print json_encode($result);
		break;


		case 'groupsearch':
			if($access['groups']['MAX'] < 1) break;

			$string = format_userinput($_GET['query'], 'text');
			if(!$string) {
				$string = '';
			}
			$includeRoles = $_GET['includeroles'] ? TRUE : FALSE;

			// Do we need the whole hierarchy?! No in case of group id
			$createHierarchy = TRUE;


			list($mode, $token) = explode('-', $_GET['token']);
			if($mode == 'all' && $token != '' && $token == $_SESSION['groupsearch_access_token']) {
				$accessAll = TRUE;
			} else {
				$accessAll = FALSE;
			}


			if ($_GET['name'] == 'sel_linked_group') {
				$input_name = "sel_linked_group[ko_groups][linked_groups]";
			} else {
				$input_name = format_userinput(substr($_GET['name'], 0, strrpos($_GET['name'], '[')), 'text');
			}

			$allGroups = db_select_data('ko_groups', "WHERE 1=1");
			if ($includeRoles) $allRoles = db_select_data('ko_grouproles', "WHERE 1=1");

			//print("All groups: ".sizeof($allGroups)."\n");
			$leafGroups = db_select_data('ko_groups g1', "WHERE NOT EXISTS (SELECT `id` FROM ko_groups g2 where g2.`pid` = g1.`id`)", 'g1.id as id', '', '', FALSE, TRUE);
			$leafGroupIds = array();
			foreach ($leafGroups as $lg) {
				$leafGroupIds[] = intval($lg['id']);
			}

			//Apply filters set in KOTA
			list($temp, $table, $field) = explode('[', $input_name);
			$table = substr($table, 0, -1);
			$field = substr($field, 0, -1);
			if(!isset($KOTA[$table][$field])) {
				ko_include_kota(array($table));
			}
			if($KOTA[$table][$field]['form']['additional_where']) {
				$kotaWhere = $KOTA[$table][$field]['form']['additional_where'];
			} else {
				$kotaWhere = '';
			}


			$excludeIds = format_userinput($_GET['exclude'], 'intlist');
			$excludeIds = array_filter(array_map(function($el) {return trim($el);}, explode(',', $excludeIds)), function($el) {return $el != '';});
			$excludeWhere = '';
			if (sizeof($excludeIds) > 0) {
				$excludeWhere .= " AND `id` NOT IN ('".implode("','", $excludeIds)."') ";
			}
			$excludeSql = str_replace(array('#', '--', '/*', '//'), array('', '', '', ''), $_GET['excludesql']);
			if ($excludeSql) {
				$excludeWhere .= " AND ({$excludeSql})";
			}

			$zWhere = $kotaWhere.' '.$excludeWhere;
			$allowGroups = db_select_data('ko_groups', "WHERE 1=1 $zWhere", 'id');
			$allowIds = array_map(function($el){return $el['id'];}, $allowGroups);

			$groupParts = explode(':', $string);

			// Check if a group id was supplied (6 numbers)
			if (is_numeric($string) && strlen($string) == 6) {
				$leafs = array();
				if (in_array($string, $allowIds) && db_get_count('ko_groups', 'id', "AND `id` = {$string}") == 1) $leafs[] = intval($string);
				$includeRoles = FALSE;
				$createHierarchy = FALSE;
			} else {
				$selects = array();
				$tables = array();
				$wheres = array();

				$counter = 0;
				foreach ($groupParts as $groupPart) {
					$groupWhere = array();
					$groupPart = trim($groupPart);
					if (!$groupPart) {
						$groupWhere = "1=1";
					} else {
						foreach (explode(' ', $groupPart) as $word) {
							$word = trim($word);
							if (!$word) continue;
							$groupWhere[] = "g{$counter}.`name` LIKE '%".str_replace(array("'", '%'), array("\'", '\%'), $word)."%'";
						}

						$groupWhere = "(" . implode(" AND ", $groupWhere) . ")";
					}

					if (sizeof($allowIds) > 0) {
						$groupWhere = "(" . $groupWhere . " AND g{$counter}.`id` IN (" . implode(',', $allowIds) . ") )";
					} else {
						$groupWhere = "(" . $groupWhere . " AND 1=2 )";
					}

					$wheres[] = $groupWhere;
					$tables[] = "`ko_groups` g{$counter}";
					$selects[] = "g{$counter}.`id` as `id{$counter}`";

					$counter++;
				}

				if (sizeof($tables) > 1) {
					for ($i = 1; $i < sizeof($tables); $i++) {
						$wheres[] = "g{$i}.`pid` = g".($i-1).".`id`";
					}
				}

				$result = db_query("SELECT ".implode(', ', $selects)." FROM ".implode(', ', $tables)." WHERE ".implode(" AND ", $wheres));

				$leafs = array();
				$inspect = array();
				foreach ($result as $r) {
					$id = intval($r['id'.($counter-1)]);
					if (in_array($id, $leafGroupIds)) {
						$leafs[] = $id;
					} else {
						$inspect[] = $id;
					}
				}
				while (sizeof($inspect) > 0) {
					$id = array_pop($inspect);
					if (in_array($id, $leafGroupIds)) {
						$leafs[] = $id;
					} else {
						$children = db_select_data('ko_groups', "WHERE `pid` = {$id} AND `id` IN (" . implode(',', $allowIds) . ")", 'id');
						foreach ($children as $child) {
							$inspect[] = intval($child['id']);
						}
					}
				}

				$leafs = array_unique($leafs);

				// Check if we also consider roles
				if ($includeRoles) {
					$selects = array();
					$tables = array();
					$wheres = array();

					$counter = 0;
					foreach ($groupParts as $groupPart) {
						$groupPart = trim($groupPart);
						$onlyHandleRole = FALSE;

						$groupWhere = array();

						if ($counter == sizeof($groupParts) - 1) {

							if ($counter == 0) {
								$c = $counter;
							} else {
								$c = $counter - 1;
								$onlyHandleRole = TRUE;
							}

							if (!$groupPart) {
								$groupWhere = "1=1";
							} else {
								foreach (explode(' ', $groupPart) as $word) {
									$word = trim($word);
									if (!$word) continue;
									$groupWhere[] = "r.`name` LIKE '%".str_replace(array("'", '%'), array("\'", '\%'), $word)."%'";
								}

								$groupWhere = "(" . implode(" AND ", $groupWhere) . ")";
							}

							$matchedRoleIds = array_map(function($el){return $el['id'];}, db_select_data('ko_grouproles r', "WHERE 1=1", 'id'));
							$groupWhere = "EXISTS (SELECT r.`id` FROM `ko_grouproles` r WHERE g".($c).".`roles` REGEXP CONCAT('(^|,)', CONCAT(r.`id`, '(,|$)')) AND {$groupWhere})";
						} else {
							if (!$groupPart) {
								$groupWhere = "1=1";
							} else {
								foreach (explode(' ', $groupPart) as $word) {
									$word = trim($word);
									if (!$word) continue;
									$groupWhere[] = "g{$counter}.`name` LIKE '%".str_replace(array("'", '%'), array("\'", '\%'), $word)."%'";
								}

								$groupWhere = "(" . implode(" AND ", $groupWhere) . ")";
							}
						}

						if (!$onlyHandleRole) {
							if (sizeof($allowIds) > 0) {
								$groupWhere = "(" . $groupWhere . " AND g{$counter}.`id` IN (" . implode(',', $allowIds) . ") )";
							} else {
								$groupWhere = "(" . $groupWhere . " AND 1=2 )";
							}

							$tables[] = "`ko_groups` g{$counter}";
							$selects[] = "g{$counter}.`id` as `id{$counter}`";
						}

						$wheres[] = $groupWhere;

						$counter++;
					}

					if (sizeof($tables) > 1) {
						for ($i = 1; $i < sizeof($tables); $i++) {
							$wheres[] = "g{$i}.`pid` = g".($i-1).".`id`";
						}
					}

					$result = db_query("SELECT ".implode(', ', $selects)." FROM ".implode(', ', $tables)." WHERE ".implode(" AND ", $wheres));

					$inspect = array();
					foreach ($result as $r) {
						$lastGroupIndex = sizeof($groupParts) == 1 ? ($counter-1) : ($counter-2);
						$id = intval($r['id'.$lastGroupIndex]);
						if (in_array($id, $leafGroupIds)) {
							$leafs[] = $id;
						} else {
							$inspect[] = $id;
						}
					}
					while (sizeof($inspect) > 0) {
						$id = array_pop($inspect);
						if (in_array($id, $leafGroupIds)) {
							$leafs[] = $id;
						} else {
							$children = db_select_data('ko_groups', "WHERE `pid` = {$id} AND `id` IN (" . implode(',', $allowIds) . ")", 'id');
							foreach ($children as $child) {
								$inspect[] = intval($child['id']);
							}
						}
					}

					$leafs = array_unique($leafs);
				}
			}

			//print("Results 1: ".sizeof($result)."\n");

			//print_r($result);
			//print("SELECT ".implode(', ', $selects)." FROM ".implode(', ', $tables)." WHERE ".implode(" AND ", $wheres)."\n");



			//print("Leafs: ".sizeof($leafs)."\n");

			$allFullGids = array();
			$allFullGroupNames = array();

			$hierarchy = array('id' => 0, 'children' => array());
			foreach ($leafs as $leaf) {
				if (!isset($allFullGids['g'.zerofill($leaf, 6)])) {
					$allFullGids['g'.zerofill($leaf, 6)] = ko_groups_decode(zerofill($leaf, 6), 'full_gid');
				}
				$fullGid = $allFullGids['g'.zerofill($leaf, 6)];
				$h = &$hierarchy['children'];

				if ($createHierarchy) $bloodline = explode(':', $fullGid);
				else $bloodline = array('g'.zerofill($leaf, 6));
				foreach ($bloodline as $gid) {
					if (!isset($allFullGids[$gid])) {
						$allFullGids[$gid] = ko_groups_decode(substr($gid, 1), 'full_gid');
					}
					$currentFullGid = $allFullGids[$gid];
					if (!isset($allFullGroupNames[$gid])) {
						$allFullGroupNames[$gid] = ko_groups_decode($currentFullGid, 'group_desc_full');
					}
					$currentFullGroupName = $allFullGroupNames[$gid];
					$group = &$allGroups[substr($gid, 1)];
					$placeholder = ($group['type'] == 1 ? TRUE : FALSE);
					$groupName = $group['name'];
					$addItems = array(
						array('id' => $gid, 'name' => $groupName, 'displayName' => $groupName, 'title' => $currentFullGroupName, 'placeholder' => $placeholder),
					);
					if ($includeRoles) {
						if (!isset($group['rolesProcessed'])) {
							$group['rolesProcessed'] = array();
							$roleIds = array_filter(explode(',', $group['roles']), function($el){return trim($el)?TRUE:FALSE;});
							foreach ($roleIds as $roleId) {
								$roleId = zerofill($roleId, 6);
								if (in_array($roleId, $matchedRoleIds)) {
									$role = $allRoles[$roleId];
									if ($role['id'] == $roleId) {
										$group['rolesProcessed'][$roleId] = $role;
									}
								}
							}
						}
						foreach ($group['rolesProcessed'] as $roleId => $role) {
							$addItems[] = array('id' => $gid . ':r' . zerofill($roleId, 6), 'displayName' => "<span class=\"text-hidden\">{$groupName} ({$role['name']})</span>", 'title' => "{$currentFullGroupName}:{$role['name']}", 'name' => "{$groupName} ({$role['name']})");
						}
					}

					foreach ($addItems as $item) {
						$id = $item['id'];
						$name = $item['name'];
						$displayName = $item['displayName'];
						$title = $item['title'];
						$placeholder = $item['placeholder'];

						if (!isset($h[$name])) {
							$h[$name] = array('id' => $id, 'name' => $name, 'displayName' => $displayName, 'title' => $title, 'placeholder' => $placeholder, 'children' => array());
						} else if ($h[$name]['id'] != $id) {
							$origName = $name;
							$cnt = 0;
							while (isset($h[$name]) && $h[$name]['id'] != $id) {
								$name = $origName . $cnt;
								$cnt++;
							}
							if (!isset($h[$name])) {
								$h[$name] = array('id' => $id, 'name' => $origName, 'displayName' => $displayName, 'title' => $title, 'placeholder' => $placeholder, 'children' => array());
							}
						}
					}

					$h = &$h[$groupName]['children'];
				}
			}


			$result = array();

			$indices = array();
			$parents = array();
			$level = 0;
			$h = &$hierarchy;
			$done = false;
			$iters = 0;
			ksort($h['children']);
			while (!$done && $iters < 1000) {
				$keys = array_keys($h['children']);
				$parentKeys = array_keys($parents[$level]['children']);

				//print ("\n\n-----------------\nLOOP START\n-------------------\n\n");

				/*print("Group name: {$allGroups[$h['id']]['name']} ({$h['id']})\n");

				print("Indices\n");
				print_r($indices);
				print("Level: $level\n");*/

				if ($h['id'] && !$h['printed']) {
					$h['printed'] = TRUE;
					$prefix = '';
					for ($i = 1; $i < $level; $i++) {
						$prefix .= '&nbsp;&nbsp;';
					}
					$name = $allGroups[$h['id']]['name'];
					$result[] = array('id' => utf8_encode($h['id']), 'name' => $prefix . utf8_encode($h['displayName']), 'title' => utf8_encode($h['title']), 'placeholder' => $h['placeholder']);
				}
				if (sizeof($h['children']) > 0 && !$h['childrenVisited']) {
					//print("If: 1\n");
					//print_r($keys);

					$h['childrenVisited'] = true;

					$parents[$level+1] = &$h;
					//print_r('blabb');
					//print_r($keys[$indices[$level]]);
					//print_r('blubb');
					$level++;
					$h = &$h['children'][$keys[$indices[$level]?$indices[$level]:0]];
					ksort($h['children']);
				} else if (($indices[$level]?$indices[$level]:0) + 1 < sizeof($parentKeys) && isset($parents[$level]['children'][$parentKeys[($indices[$level]?$indices[$level]:0) + 1]])) {
					/*print("If: 2\n");
					print("Indices before cleaning\n");
					print_r($indices);*/
					for ($i = $level+1, $stop = max(array_keys($indices)); $i <= $stop; $i++) unset($indices[$i]);
					/*print("Indices after cleaning\n");
					print_r($indices);
					print("Keys\n");
					print_r($parentKeys);
					print("Siblings:\n");
					print_r($parents[$level]['children']);*/
					$indices[$level] = $indices[$level] ? ($indices[$level] + 1) : (1);
					$h = &$parents[$level]['children'][$parentKeys[$indices[$level]?$indices[$level]:0]];
					ksort($h['children']);
				} else {
					//print("If: 3\n");
					$h = &$parents[$level];
					$level--;
					//print("Indices before cleaning\n");
					//print_r($indices);
					for ($i = $level+1, $stop = max(array_keys($indices)); $i < $stop; $i++) unset($indices[$i]);
					//print("Indices after cleaning\n");
					//print_r($indices);
				}

				if (!$h['id']) {
					//print_r(array('abort'));
					//print_r($h);
					$done = true;
				}

				//print("Indices\n");
				//print_r($indices);

				$iters++;
			}

			//print("Results: ".sizeof($result)."\n");

			print json_encode($result);
		break;

	}//switch(action);

	hook_ajax_post($ko_menu_akt, $action);

}//if(GET[action])



