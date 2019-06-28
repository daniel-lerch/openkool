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

include_once($BASE_PATH."inc/class.kOOL_listview.php");


function ko_groups_list($output=TRUE) {
	global $smarty, $ko_path;
	global $all_groups, $access, $MAILING_PARAMETER;


	//Motherline für Listen-Titel
	if($_SESSION["show_gid"] == "NULL") {
		$list_title = '<a href="#">'.getLL("groups_list_top").'</a>';
	} else {
		$list_title = '<a href="?action=list_groups&amp;gid=NULL">'.getLL("groups_list_top").'</a>&nbsp;&nbsp;/&nbsp;&nbsp;';
		$m = ko_groups_get_motherline($_SESSION["show_gid"], $all_groups);
		$value = array();
		//Link für jede hierarchisch höher liegende Gruppe
		foreach($m as $g) {
			$list_title .= '<a href="?action=list_groups&amp;gid='.$g.'">'.$all_groups[$g]["name"].'</a>&nbsp;&nbsp;/&nbsp;&nbsp;';
		}
		//Aktuelle Gruppe auch anzeigen
		$list_title .= $all_groups[$_SESSION["show_gid"]]["name"];
	}

	$smarty->assign("tpl_list_subtitle", $list_title);

	//Check for tracking rights
	$show_tracking = FALSE;
	if(ko_module_installed('tracking', $_SESSION['ses_userid'])) {
		ko_get_access('tracking');
		$add_tracking = $access['tracking']['ALL'] > 3;
		$show_tracking = $access['tracking']['MAX'] > 0;
	}

	//Check for mailing rights
	$show_mailing = (ko_module_installed('mailing', $_SESSION['ses_userid']) && $MAILING_PARAMETER['domain'] != '');


	$z_where = $z_limit = "";
	//apply_groups_filter($z_where, $z_limit);
	if($_SESSION["show_gid"] == "NULL") {
		$z_where = "AND `pid` IS NULL";
	} else {
		$z_where = "AND `pid` = '".$_SESSION["show_gid"]."'";
	}
	$rows = db_get_count('ko_groups', 'id', $z_where);

	if($_SESSION['show_start'] > $rows) $_SESSION['show_start'] = 1;
	if($_SESSION['show_start'] && $_SESSION['show_limit']) $z_limit  = 'LIMIT '.($_SESSION['show_start']-1).', '.$_SESSION['show_limit'];

	ko_get_groups($es, $z_where, $z_limit);

	$list = new kOOL_listview();
	$list->init('groups', 'ko_groups', array('chk', 'edit', 'delete', 'tracking_show', 'tracking_add', 'mailing'), $_SESSION['show_start'], $_SESSION['show_limit']);
	$list->setTitle(getLL('groups_groups'));
	$list->setSubTitle($list_title);
	$list->setAccessRights(array('edit' => 3, 'delete' => 4), $access['groups']);
	$list->setActions(array('edit' => array('action' => 'edit_group'),
													'delete' => array('action' => 'delete_group', 'confirm' => TRUE))
										);
	$list->setSort(TRUE, 'setsort', $_SESSION['sort_groups'], $_SESSION['sort_groups_order']);
	$list->setStats($rows);


	$manual_access = array();
	foreach($es as $k => $v) {
		//Exclude groups this user has no access to
		if($access['groups']['ALL'] < 1 && $access['groups'][$k] < 1) {
			unset($es[$k]);
			continue;
		}

		$rowData = array();

		//Set columns nump, numug so kota_process_data processes them
		$es[$k]['nump'] = $es[$k]['numug'] = '';

		//Manual access for deletion
		if(($access['groups']['ALL'] > 3 || $access['groups'][$k] > 3) && db_get_count('ko_groups', "id", "AND `pid` = '$k'") == 0) {
			$manual_access['delete'][$k] = TRUE;
		} else {
			$manual_access['delete'][$k] = FALSE;
		}

		//Manual access for tracking
		if($show_tracking && $v['type'] != 1) {
			//Find a tracking for this group
			$tracking = db_select_data('ko_tracking', "WHERE `filter` REGEXP '^g".$k."[:r0-9]*'", '*', '', 'LIMIT 0,1', TRUE);
			if(isset($tracking['id']) && ($access['tracking']['ALL'] > 0 || $access['tracking'][$tracking['id']] > 0)) {  //Found a tracking with access to it
				$manual_access['tracking_show'][$k] = TRUE;
				$manual_access['tracking_add'][$k] = FALSE;
				$rowData['tracking_id'] = $tracking['id'];
			} else if($add_tracking) {  //No tracking so show add link if access rights are 4@ALL
				$manual_access['tracking_show'][$k] = FALSE;
				$manual_access['tracking_add'][$k] = TRUE;
			} else {  //else don't show anything
				$manual_access['tracking_show'][$k] = FALSE;
				$manual_access['tracking_add'][$k] = FALSE;
			}
		} else {
			$manual_access['tracking_show'][$k] = FALSE;
			$manual_access['tracking_add'][$k] = FALSE;
		}

		//Mailing: Show email links
		if($show_mailing) {
			if($v['mailing_alias'] != '') {
				$link = $v['mailing_alias'].'@'.$MAILING_PARAMETER['domain'];
				$manual_access['mailing'][$k] = TRUE;
			} else if(!ko_get_setting('mailing_only_alias')) {
				$link = 'gr'.$v['id'].'@'.$MAILING_PARAMETER['domain'];
				$manual_access['mailing'][$k] = TRUE;
			} else {
				$link = '';
				$manual_access['mailing'][$k] = FALSE;
			}
			$rowData['mailing_link'] = $link;
		}


		$list->setRowData($rowData, $k);
	}

	$list->setManualAccess('delete', $manual_access['delete']);
	$list->setManualAccess('tracking_show', $manual_access['tracking_show']);
	$list->setManualAccess('tracking_add', $manual_access['tracking_add']);
	$list->setManualAccess('mailing', $manual_access['mailing']);


	$list_footer = $smarty->get_template_vars('list_footer');
	$list->setFooter($list_footer);


	if($output) {
		$list->render($es);
	} else {
		print $list->render($es);
	}
}//ko_groups_list()





function ko_groups_list_datafields() {
	global $smarty, $access, $KOTA;

	if($access['groups']['MAX'] < 3) return;

	if($_SESSION['groups_show_hidden_datafields']) {
		$where = '';
	} else {
		//Get all expired groups
		$expgroups = db_select_data('ko_groups', "WHERE `start` > NOW() OR (`stop` != '0000-00-00' AND `stop` < NOW())", '*');
		$exclude_dfs = array();
		foreach($expgroups as $g) {
			foreach(explode(',', $g['datafields']) as $df) {
				if(!$df) continue;
				$exclude_dfs[] = $df;
			}
		}
		//Build where to exclude datafields of expired groups (but only non-reusable)
		$exclude_dfs = array_unique($exclude_dfs);
		if(sizeof($exclude_dfs) > 0) $where = " AND `reusable` = '1' OR (`reusable` = '0' AND `id` NOT IN (".implode(',', $exclude_dfs).")) ";
		else $where = '';
	}

	//Set filters from KOTA
	$kota_where = kota_apply_filter('ko_groups_datafields');
	if($kota_where != '') $where .= " AND ($kota_where) ";

	$rows = db_get_count('ko_groups_datafields', 'id', $where);
	$datafields = db_select_data('ko_groups_datafields', 'WHERE 1 '.$where, '*', 'ORDER BY preset DESC, reusable DESC, description ASC');

	$df_access = array();
	foreach($datafields as $l_i => $l) {
		//Check groups for use of this datafield. Used datafields may not be deleted
		$groups = db_select_data('ko_groups', "WHERE `datafields` REGEXP '$l_i'");
		$num = sizeof($groups);
		//Build fake access array
		$df_access[$l_i] = $num == 0 ? 2 : 1;

		//Prepare fake column used_in
		$used_in = array();
		foreach($groups as $group) {
			$used_in[] = $group['name'];
		}
		$datafields[$l_i]['used_in'] = ($num > 15) ? '"'.$num.' '.getLL('groups_groups').'"' : ko_html(implode(', ', $used_in));
	}


	$list = new kOOL_listview();

	$list->init('groups', 'ko_groups_datafields', array('chk', 'edit', 'delete'), 1, 1000);
	$list->setTitle(getLL('groups_datafields_list_title'));
	$list->setAccessRights(array('edit' => 1, 'delete' => 2), $df_access, 'id');
	$list->setActions(array('edit' => array('action' => 'edit_datafield'),
													'delete' => array('action' => 'delete_datafield', 'confirm' => TRUE))
										);
	$list->setSort(FALSE);
	$list->setStats($rows);
	$list->setWarning(kota_filter_get_warntext('ko_groups_datafields'));


	if($output) {
		$list->render($datafields);
	} else {
		print $list->render($datafields);
	}
}//ko_groups_list_datafields()





function ko_groups_list_roles($output=TRUE) {
	global $smarty, $access;

	if($access['groups']['MAX'] < 1) return;

	$z_where = $z_limit = "";
	$order = 'ORDER BY name ASC';

	$rows = db_get_count('ko_grouproles', 'id', $z_where);
	ko_get_grouproles($roles, $z_where, $z_limit);

	foreach($roles as $l_i => $l) {
		//Build fake access array by checking access to all groups each role is used in
		$groups = db_select_data("ko_groups", "WHERE `roles` REGEXP '$l_i'");
		$used_in = ""; $used_in_num = 0;
		if(sizeof($groups) > 0) {  //Check all groups this role is used in
			$do_edit = TRUE;
			$do_del = TRUE;
			foreach($groups as $group) {
				if($access['groups']['ALL'] < 3 && $access['groups'][$group['id']] < 3) $do_edit = FALSE;
				if($access['groups']['ALL'] < 4 && $access['groups'][$group['id']] < 4) $do_del = FALSE;
				$used_in .= $group["name"].", ";
				$used_in_num++;
			}
		} else {  //Role is currently not assigned to any group
			$do_edit = $access['groups']['ALL'] > 2;
			$do_del = $access['groups']['ALL'] > 3;
		}
		if($do_del) $role_access[$l_i] = 2;
		else if($do_edit) $role_access[$l_i] = 1;
		else $role_access[$l_i] = 0;

		//Add fake column used_in
		$roles[$l_i]['used_in'] = ($used_in_num > 15) ? '('.$used_in_num.' '.getLL('groups_groups').')' : ko_html(substr($used_in, 0, -2));
	}


	$list = new kOOL_listview();

	$list->init('groups', 'ko_grouproles', array('chk', 'edit', 'delete'), 1, 1000);
	$list->setTitle(getLL('groups_roles_list_title'));
	$list->setAccessRights(array('edit' => 1, 'delete' => 2), $role_access, 'id');
	$list->setActions(array('edit' => array('action' => 'edit_role'),
													'delete' => array('action' => 'delete_role', 'confirm' => TRUE))
										);
	$list->setColumnLink('name', '../leute/index.php?action=set_role_filter&amp;id=ID');
	$list->setSort(FALSE);
	$list->setStats($rows);

	if($output) {
		$list->render($roles);
	} else {
		print $list->render($roles);
	}
}//ko_groups_list_roles()







function ko_groups_formular_group($mode, $id = "") {
	global $ko_path, $smarty;
  global $access, $MAILING_PARAMETER;
	global $all_groups;
	global $js_calendar;

  if($mode == "edit" && $id) {
		if($access['groups']['MAX'] < 3) return;

		//Daten zu dieser Gruppe auslesen
		$group = $all_groups[$id];
		$txt_name = ko_html($group["name"]);
		$txt_description = ko_html($group["description"]);
		$groups_selected = $group["pid"];
		$linkedGroup = $group['linked_group'];
		$chk_type = ko_html($group["type"]);
		$start = $group["start"] == "0000-00-00" ? "" : sql2datum($group["start"]);
		$stop = $group["stop"] == "0000-00-00" ? "" : sql2datum($group["stop"]);
		$txt_mailing_alias = ko_html($group['mailing_alias']);
		$mailing_mod_logins = format_userinput($group['mailing_mod_logins'], 'uint');
		$mailing_mod_members = format_userinput($group['mailing_mod_members'], 'uint');
		$mailing_mod_others = format_userinput($group['mailing_mod_others'], 'uint');
		$mailing_mod_role = format_userinput($group['mailing_mod_role'], 'uint');
		$mailing_reply_to = format_userinput($group['mailing_reply_to'], 'alpha');
  	$mailing_modify_rcpts = format_userinput($group['mailing_modify_rcpts'], 'uint');
		$maxcount = format_userinput($group['maxcount'], 'uint');
		$count_role = format_userinput($group['count_role'], 'uint');

		//Berechtigungen
		foreach(array('view', 'new', 'edit', 'del') as $level) {
			${'rights_'.$level.'_avalues'} = ${'rights_'.$level.'_aoutput'} = array();
			foreach(explode(",", $group["rights_".$level]) as $gid) {
				if(!$gid) continue;
				//Admingroup
				if(substr($gid, 0, 1) == 'g') {
					$ag_ = ko_get_admingroups(substr($gid, 1));
					$ag = $ag_[substr($gid, 1)];
					$label = getLL('admin_admingroup').': '.$ag['name'];
				}
				//Login
				else {
					ko_get_login($gid, $login);
					if($login["leute_id"]) {
						ko_get_person_by_id($login["leute_id"], $p);
						$label = $p['vorname'].' '.$p['nachname'];
					} else {
						$label = $login['login'];
					}
				}
				${'rights_'.$level.'_avalues'}[] = $gid;
				${'rights_'.$level.'_aoutput'}[] = $label;
			}
		}

		//Roles
		$roles_avalues = $roles_aoutput = array();
		foreach(explode(",", $group["roles"]) as $rid) {
			ko_get_grouproles($role, "AND `id` = '$rid'");
			$roles_avalues[] = $rid;
			$roles_aoutput[] = $role[$rid]["name"];
		}
		//datafields for this group
		$datafields_avalues = $datafields_aoutput = array(); $ids = array();
		foreach(explode(",", $group["datafields"]) as $f) {
			if(!$f) continue;
			$ids[] = $f;
		}
		if($ids != "") {
			$where = "WHERE `id` IN ('".implode("','", $ids)."')";
		} else {
			$where = "WHERE 1=2";
		}
		$fields = db_select_data("ko_groups_datafields", $where, "*");
		foreach($ids as $_id) {
			$datafields_avalues[] = $fields[$_id]["id"];
			$prefix = '';
			if($fields[$_id]['reusable'] == 1) $prefix .= '['.getLL('groups_datafields_reusable_short').'] ';
			if($fields[$_id]['private'] == 1) $prefix .= '['.getLL('groups_datafields_private_short').'] ';
			$datafields_aoutput[] = $prefix.$fields[$_id]["description"]." (".getLL("groups_datafields_".$fields[$_id]["type"]).")";
		}

  } else if($mode == "new") {
		if($access['groups']['MAX'] < 3) return;

		//select displayed group as mothergroup
		$groups_selected = $_SESSION["show_gid"];

  	$mailing_modify_rcpts = 1;

		//Find roles from parent group to preselect them
		if($_SESSION['show_gid'] != 'NULL') {
			$motherline = ko_groups_get_motherline($_SESSION['show_gid'], $all_groups);
			$motherline[] = $_SESSION['show_gid'];
			$motherline = array_reverse($motherline);
			foreach($motherline as $mid) {
				if(!$mid) continue;
				//If dummy group with defined roles has been found in motherline then preselect this group's roles
				if($all_groups[$mid]['type'] == 1 && $all_groups[$mid]['roles'] != '') {
					$roles_avalues = $roles_aoutput = array();
					foreach(explode(',', $all_groups[$mid]['roles']) as $rid) {
						ko_get_grouproles($role, "AND `id` = '$rid'");
						$roles_avalues[] = $rid;
						$roles_aoutput[] = $role[$rid]['name'];
					}
				}
			}
		}
  } else return;


	//Gruppen
	$groups_values = $groups_output = array();
	//Only allow groups on top level with ALL rights 3
	if($access['groups']['ALL'] > 2) {
		$groups_values[] = '';
		$groups_output[] = '';
	}
	$groups = ko_groups_get_recursive(ko_get_groups_zwhere());
	foreach($groups as $grp) {
		if($grp['id'] == $id) continue;
		if($access['groups']['ALL'] < 3 && $access['groups'][$grp['id']] < 3) continue;
	  //Kein Kreis-Vererbungen erlauben
		$mother_line = ko_groups_get_motherline($grp["id"], $all_groups);
		if(in_array($id, $mother_line)) continue;
		//Hierarchie darstellen
		$pre = "";
		$depth = sizeof($mother_line);
		for($i=0; $i<$depth; $i++) $pre .= "&nbsp;&nbsp;";
		$groups_values[] = $grp["id"];
		$groups_output[] = $pre.ko_html($grp["name"]);
	}

	//Rollen
	$roles_values = $roles_output = array();
	ko_get_grouproles($roles);
	foreach($roles as $role) {
		$roles_values[] = $role["id"];
		$roles_output[] = $role["name"];
	}

	//Logins mit Groups-Modul
	$logins_values = $logins_output = array();
	ko_get_logins($logins, "AND (`disabled` = '' OR `disabled` = '0')");
	foreach($logins as $l) {
		if(!ko_module_installed("groups", $l["id"])) continue;
		if($l["leute_id"]) {
			ko_get_person_by_id($l["leute_id"], $p);
		} else {
			$p = array("vorname" => $l["login"], "nachname" => "");
		}
		//Für die jeweiligen Stufen nur die Benutzer anzeigen, die nicht eh schon globale Rechte für diese Stufe haben
		$all_rights = ko_get_access_all('groups', $l['id'], $max_rights);
		if($all_rights < 1){
			$logins_view_values[] = $logins_new_values[] = $logins_edit_values[] = $logins_del_values[] = $l["id"];
			$logins_view_output[] = $logins_new_output[] = $logins_edit_output[] = $logins_del_output[] = $p["vorname"]." ".$p["nachname"];
		} else if($all_rights < 2){
			$logins_new_values[] = $logins_edit_values[] = $logins_del_values[] = $l["id"];
			$logins_new_output[] = $logins_edit_output[] = $logins_del_output[] = $p["vorname"]." ".$p["nachname"];
		} else if($all_rights < 3){
			$logins_edit_values[] = $logins_del_values[] = $l["id"];
			$logins_edit_output[] = $logins_del_output[] = $p["vorname"]." ".$p["nachname"];
		} else if($all_rights < 4){
			$logins_del_values[] = $l["id"];
			$logins_del_output[] = $p["vorname"]." ".$p["nachname"];
		}
	}

	//Add admin groups to select
	$admingroups = ko_get_admingroups();
	foreach($admingroups as $ag) {
		if(!in_array('groups', explode(',', $ag['modules']))) continue;

		$label = getLL('admin_admingroup').': '.$ag['name'];
		$value = 'g'.$ag['id'];
		//Für die jeweiligen Stufen nur die Benutzer anzeigen, die nicht eh schon globale Rechte für diese Stufe haben
		if($ag['groups_admin'] < 1) {
			$logins_view_values[] = $logins_new_values[] = $logins_edit_values[] = $logins_del_values[] = $value;
			$logins_view_output[] = $logins_new_output[] = $logins_edit_output[] = $logins_del_output[] = $label;
		} else if($ag['groups_admin'] < 2) {
			$logins_new_values[] = $logins_edit_values[] = $logins_del_values[] = $value;
			$logins_new_output[] = $logins_edit_output[] = $logins_del_output[] = $label;
		} else if($ag['groups_admin'] < 3) {
			$logins_edit_values[] = $logins_del_values[] = $value;
			$logins_edit_output[] = $logins_del_output[] = $label;
		} else if($ag['groups_admin'] < 4) {
			$logins_del_values[] = $value;
			$logins_del_output[] = $label;
		}
	}

	//datafields
	$datafields_values = $datafields_output = array();

	$p_fields = db_select_data('ko_groups_datafields', "WHERE preset = '1'", '*', 'ORDER BY description ASC');
	foreach($p_fields as $f) {
		$datafields_values[] = $f['id'];
		$prefix = '['.getLL('groups_datafields_preset_short').'] ';
		if($f['private'] == 1) $prefix .= '['.getLL('groups_datafields_private_short').'] ';
		$datafields_output[] = $prefix.$f['description'].' ('.getLL('groups_datafields_'.$f['type']).')';
	}

	$r_fields = db_select_data('ko_groups_datafields', "WHERE reusable = '1'", '*', 'ORDER BY description ASC');
	foreach($r_fields as $f) {
		$datafields_values[] = $f['id'];
		$prefix = '['.getLL('groups_datafields_reusable_short').'] ';
		if($f['private'] == 1) $prefix .= '['.getLL('groups_datafields_private_short').'] ';
		$datafields_output[] = $prefix.$f['description'].' ('.getLL('groups_datafields_'.$f['type']).')';
	}


	//Formular aufbauen
	$rowcounter = 0;
	$gc = 0;
	$frmgroup[$gc]["row"][$rowcounter]["inputs"][0] = array("desc" => getLL("form_groups_name"),
															 "type" => "text",
															 "name" => "txt_name",
															 "value" => (isset($_POST["txt_name"]) ? ko_html($_POST["txt_name"]) : $txt_name),
															 "params" => 'size="40"',
															 );
	$frmgroup[$gc]["row"][$rowcounter++]["inputs"][1] = array("desc" => getLL("form_groups_description"),
															 "type" => "textarea",
															 "name" => "txt_description",
															 "value" => (isset($_POST["txt_description"]) ? ko_html($_POST["txt_description"]) : $txt_description),
															 "params" => 'cols="40" rows="4"',
															 );
	$frmgroup[$gc]["row"][$rowcounter]["inputs"][0] = array("desc" => getLL("form_groups_parentgroup"),
															 "type" => "select",
															 "name" => "sel_parentgroup",
															 "values" => $groups_values,
															 "descs" => $groups_output,
															 "value" => $groups_selected,
															 "params" => 'size="0"',
															 );
	$frmgroup[$gc]["row"][$rowcounter++]["inputs"][1] = array("desc" => getLL("form_groups_type"),
															 "type" => "checkbox",
															 "name" => "chk_type",
															 "value" => 1,
															 "params" => 'onclick="if(this.checked) unset_vis(\'frmgrp_1\'); else set_vis(\'frmgrp_1\');" '.($chk_type==1 ? 'checked="checked"' : ''),
															 );
	$frmgroup[$gc]["row"][$rowcounter]["inputs"][0] = array("desc" => getLL("form_groups_cal1"),
															 "type" => "html",
															 "value" => $js_calendar->make_input_field(array(), array("name" => "txt_datum", "value" => $start)),
															 );
	$frmgroup[$gc]["row"][$rowcounter++]["inputs"][1] = array("desc" => getLL("form_groups_cal2"),
															 "type" => "html",
															 "value" => $js_calendar->make_input_field(array(), array("name" => "txt_datum2", "value" => $stop)),
															 );
	$frmgroup[$gc]["row"][$rowcounter]["inputs"][0] = array("desc" => getLL("form_groups_roles"),
															 "type" => "doubleselect",
															 "js_func_add" => "double_select_add",
															 "name" => "sel_roles",
															 "values" => $roles_values,
															 "descs" => $roles_output,
															 "avalues" => $roles_avalues,
															 "avalue" => implode(",", $roles_avalues),
															 "adescs" => $roles_aoutput,
															 "params" => 'size="7"',
															 "show_moves" => TRUE,
															 );
	$kota_field_help = ko_get_help('groups', 'kota.ko_groups.linked_group');
	$frmgroup[$gc]['row'][$rowcounter]['inputs'][1] = array('desc' => getLL('form_groups_linked_group'),
		'type' => 'select',
		'name' => 'sel_linked_group',
		'values' => $groups_values,
		'descs' => $groups_output,
		'value' => $linkedGroup,
		'params' => 'size="0"',
		'help' => $kota_field_help['show'] ? $kota_field_help['link'] : '',
	);

	//Max count
	$rowcounter++;
	$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('kota_ko_groups_maxcount'),
															 'type' => 'text',
															 'name' => 'txt_maxcount',
															 'value' => (isset($_POST['txt_maxcount']) ? ko_html($_POST['txt_maxcount']) : $maxcount),
															 'params' => 'size="5"',
															 );
	$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('kota_ko_groups_count_role'),
															 'type' => 'select',
															 'name' => 'sel_count_role',
															 'values' => array_merge(array(''), $roles_values),
															 'descs' => array_merge(array(''), $roles_output),
															 'value' => $count_role,
															 'params' => 'size="0"'
															 );

	//Mailing alias
	if(ko_module_installed('mailing') && is_array($MAILING_PARAMETER) && $MAILING_PARAMETER['domain'] != '') {
		$frmgroup[++$gc] = array('titel' => getLL('form_groups_mailing'), 'state' => 'open', 'colspan' => 'colspan="2"');

		$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('form_groups_mailing_alias'),
																 'type' => 'text',
																 'name' => 'txt_mailing_alias',
																 'value' => (isset($_POST['txt_mailing_alias']) ? ko_html($_POST['txt_mailing_alias']) : $txt_mailing_alias),
																 'params' => 'size="40"',
																 );
		if($mode == 'edit') {
			$link = 'gr'.$group['id'].'@'.$MAILING_PARAMETER['domain'];
			$address = '<b>'.getLL('form_groups_mailing_address_whole_group').'</b>: <a href="mailto:'.$link.'">'.$link.'</a>';
			if(sizeof($roles_avalues) > 0) {
				foreach($roles_avalues as $k => $rid) {
					if(!$rid) continue;
					$link = 'gr'.$group['id'].'.'.$rid.'@'.$MAILING_PARAMETER['domain'];
					$address .= '<br />'.$roles_aoutput[$k].': <a href="mailto:'.$link.'">'.$link.'</a>';
				}
			}
			$frmgroup[$gc]['row'][$rowcounter]['inputs'][1] = array('desc' => getLL('form_groups_mailing_address'),
																	 'type' => 'html',
																	 'value' => $address,
																	 );
		}
		$rowcounter++;

		//Mailing: Moderation settings
		$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('form_groups_mailing_mod_logins'),
																 'type' => 'select',
																 'name' => 'sel_mailing_mod_logins',
																 'values' => array(0, 1),
																 'descs' => array(getLL('form_groups_mailing_mod_moderated'), getLL('form_groups_mailing_mod_nonmoderated')),
																 'value' => (isset($_POST['sel_mailing_mod_logins']) ? ko_html($_POST['sel_mailing_mod_logins']) : $mailing_mod_logins),
																 'params' => 'size="0"'
																 );
		$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('form_groups_mailing_mod_role'),
																 'type' => 'select',
																 'name' => 'sel_mailing_mod_role',
																 'values' => array_merge(array('', '_none'), $roles_values),
																 'descs' => array_merge(array('', getLL('form_groups_mailing_mod_role_none')), $roles_output),
																 'value' => (isset($_POST['sel_mailing_mod_role']) ? ko_html($_POST['sel_mailing_mod_role']) : $mailing_mod_role),
																 'params' => 'size="0"'
																 );

		$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('form_groups_mailing_mod_members'),
																 'type' => 'select',
																 'name' => 'sel_mailing_mod_members',
																 'values' => array(0, 1, 2),
																 'descs' => array(getLL('no'), getLL('form_groups_mailing_mod_moderated'), getLL('form_groups_mailing_mod_nonmoderated')),
																 'value' => (isset($_POST['sel_mailing_mod_members']) ? ko_html($_POST['sel_mailing_mod_members']) : $mailing_mod_members),
																 'params' => 'size="0"'
																 );
		$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('form_groups_mailing_mod_others'),
																 'type' => 'select',
																 'name' => 'sel_mailing_mod_others',
																 'values' => array(0, 1, 2),
																 'descs' => array(getLL('no'), getLL('form_groups_mailing_mod_moderated'), getLL('form_groups_mailing_mod_nonmoderated')),
																 'value' => (isset($_POST['sel_mailing_mod_others']) ? ko_html($_POST['sel_mailing_mod_others']) : $mailing_mod_others),
																 'params' => 'size="0"'
																 );

		$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('form_groups_mailing_reply_to'),
																 'type' => 'select',
																 'name' => 'sel_mailing_reply_to',
																 'value' => (isset($_POST['sel_mailing_reply_to']) ? ko_html($_POST['sel_mailing_reply_to']) : $mailing_reply_to),
																 'params' => 'size="0"',
																 'values' => array('', 'sender', 'list'),
																 'descs' => array(getLL('form_groups_mailing_reply_to_'), getLL('form_groups_mailing_reply_to_sender'), getLL('form_groups_mailing_reply_to_list')),
																 );

		$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('form_groups_mailing_modify_rcpts'),
			'type' => 'select',
			'name' => 'sel_mailing_modify_rcpts',
			'value' => (isset($_POST['sel_mailing_reply_to']) ? ko_html($_POST['sel_mailing_modify_rcpts']) : $mailing_modify_rcpts),
			'params' => 'size="0"',
			'values' => array(1, 0),
			'descs' => array(getLL('form_groups_mailing_do_modify_rcpts'), getLL('form_groups_mailing_dont_modify_rcpts')),
		);
	}




	//data fields
	$frmgroup[++$gc] = array("titel" => getLL("form_groups_datafields"), "state" => ($chk_type==1 ? "closed" : "open"), "colspan" => 'colspan="2"');
	$frmgroup[$gc]["row"][$rowcounter]["inputs"][0] = array("desc" => getLL("form_groups_datafields_select"),
															 "type" => "doubleselect",
															 "js_func_add" => "double_select_add",
															 "headerclass" => "formular_header_datafields",
															 "name" => "sel_datafields",
															 "values" => $datafields_values,
															 "descs" => $datafields_output,
															 "avalues" => $datafields_avalues,
															 "avalue" => implode(",", $datafields_avalues),
															 "adescs" => $datafields_aoutput,
															 "params" => 'size="7"',
															 "show_moves" => TRUE,
															 );
	$html  = '<table border="0"><tr><td valign="top">';
	$html .= '<div style="font-size:9px;font-weight:700;">'.getLL("form_groups_datafields_new_description").'</div>';
	$html .= '<input type="text" size="25" name="txt_new_datafield" /><br />';
	$html .= '<div style="font-size:9px;font-weight:700;">'.getLL("form_groups_datafields_new_type").'</div>';
	$html .= '<select size="0" name="sel_new_datafield" onchange="if(this.selectedIndex==3 || this.selectedIndex==4) set_vis(\'new_datafield_options\'); else unset_vis(\'new_datafield_options\');" />';
	$html .= '<option value="text">'.getLL("groups_datafields_text").'</option>';
	$html .= '<option value="textarea">'.getLL("groups_datafields_textarea").'</option>';
	$html .= '<option value="checkbox">'.getLL("groups_datafields_checkbox").'</option>';
	$html .= '<option value="select">'.getLL("groups_datafields_select").'</option>';
	$html .= '<option value="multiselect">'.getLL("groups_datafields_multiselect").'</option>';
	$html .= '</select>';
	$html .= '<br /><input type="checkbox" name="chk_new_datafield_preset" id="chk_new_datafield_preset" value="1" /><label for="chk_new_datafield_preset">'.getLL("groups_datafields_preset").'</label>';
	$html .= '<br /><input type="checkbox" name="chk_new_datafield_private" id="chk_new_datafield_private" value="1" /><label for="chk_new_datafield_private">'.getLL("groups_datafields_private").'</label>';
	$html .= '<br /><input type="checkbox" name="chk_new_datafield_reusable" id="chk_new_datafield_reusable" value="1" /><label for="chk_new_datafield_reusable">'.getLL("groups_datafields_reusable").'</label><br />';
	$html .= '<div style="margin-top:8px;"><input type="button" onclick="do_submit_new_datafield(\''.session_id().'\');" value="'.getLL("form_groups_datafields_new_create").'" /></div>';
	$html .= '</td><td valign="top">';
	$html .= '<div name="new_datafield_options" id="new_datafield_options" style="visibility:hidden; display:none;">';
	$html .= '<div style="font-size:9px;font-weight:700;">'.getLL("form_groups_datafields_new_options").'</div>';
	$html .= '<textarea name="txt_new_datafield_options" cols="20" rows="5"></textarea>';
	$html .= '</div>';
	$html .= '</td></tr></table>';
	$frmgroup[$gc]["row"][$rowcounter]["inputs"][1] = array("desc" => getLL("form_groups_datafields_create"),
															 "type" => "html",
															 "headerclass" => "formular_header_datafields",
															 "value" => $html,
															 );
	//user-rights
	if($access['groups']['ALL'] > 2) {
		$state = sizeof($rights_view_avalues) > 0 || sizeof($rights_new_avalues) > 0  || sizeof($rights_edit_avalues) > 0  || sizeof($rights_del_avalues) > 0 ? 'open' : 'closed';
		$frmgroup[++$gc] = array("titel" => getLL("form_groups_rights"), "state" => $state, "colspan" => 'colspan="2"');
		$frmgroup[$gc]["row"][$rowcounter]["inputs"][0] = array("desc" => getLL("form_groups_rights_view"),
																 "type" => "doubleselect",
															 	 "js_func_add" => "double_select_add",
																 "name" => "sel_rights_view",
																 "values" => $logins_view_values,
																 "descs" => $logins_view_output,
																 "avalues" => $rights_view_avalues,
																 "avalue" => implode(",", $rights_view_avalues),
																 "adescs" => $rights_view_aoutput,
																 "params" => 'size="7"'
																 );
		$frmgroup[$gc]["row"][$rowcounter++]["inputs"][1] = array("desc" => getLL("form_groups_rights_new"),
																 "type" => "doubleselect",
															 	 "js_func_add" => "double_select_add",
																 "name" => "sel_rights_new",
																 "values" => $logins_new_values,
																 "descs" => $logins_new_output,
																 "avalues" => $rights_new_avalues,
																 "avalue" => implode(",", $rights_new_avalues),
																 "adescs" => $rights_new_aoutput,
																 "params" => 'size="7"'
																 );
		$frmgroup[$gc]["row"][$rowcounter]["inputs"][0] = array("desc" => getLL("form_groups_rights_edit"),
																 "type" => "doubleselect",
															 	 "js_func_add" => "double_select_add",
																 "name" => "sel_rights_edit",
																 "values" => $logins_edit_values,
																 "descs" => $logins_edit_output,
																 "avalues" => $rights_edit_avalues,
																 "avalue" => implode(",", $rights_edit_avalues),
																 "adescs" => $rights_edit_aoutput,
																 "params" => 'size="7"'
																 );
		$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('form_groups_rights_del'),
																 'type' => 'doubleselect',
															 	 'js_func_add' => 'double_select_add',
																 'name' => 'sel_rights_del',
																 'values' => $logins_del_values,
																 'descs' => $logins_del_output,
																 'avalues' => $rights_del_avalues,
																 'avalue' => implode(',', $rights_del_avalues),
																 'adescs' => $rights_del_aoutput,
																 'params' => 'size="7"'
																 );
	}//if(access[ALL] > 2)

	//EZMLM export
	if(defined("EXPORT2EZMLM") && EXPORT2EZMLM) {
		$frmgroup[++$gc] = array("titel" => getLL("form_groups_ezmlm"), "state" => $group["ezmlm_list"] == "" ? "closed" : "open", "colspan" => 'colspan="2"');
		$frmgroup[$gc]["row"][$rowcounter]["inputs"][0] = array("desc" => getLL("form_groups_ezmlm_list"),
																 "type" => "text",
																 "name" => "txt_ezmlm_list",
														 		 "value" => (isset($_POST["txt_ezmlm_list"]) ? ko_html($_POST["txt_ezmlm_list"]) : $group["ezmlm_list"]),
																 "params" => 'size="40"'
																 );
		$frmgroup[$gc]["row"][$rowcounter++]["inputs"][1] = array("desc" => getLL("form_groups_ezmlm_moderator"),
																 "type" => "text",
																 "name" => "txt_ezmlm_moderator",
														 		 "value" => (isset($_POST["txt_ezmlm_moderator"]) ? ko_html($_POST["txt_ezmlm_moderator"]) : $group["ezmlm_moderator"]),
																 "params" => 'size="40"'
																 );
		$frmgroup[$gc]["row"][$rowcounter++]["inputs"][0] = array("desc" => getLL("form_groups_ezmlm_export"),
																 "type" => "checkbox",
																 "name" => "chk_ezmlm_export",
																 "value" => 1,
																 );
	}


	//Allow plugins to change form
	hook_form('ko_groups', $frmgroup, $mode, $id);


	$smarty->assign("tpl_titel", ($mode == "new") ? getLL("groups_new_group") : getLL("groups_edit_group"));
	$smarty->assign("tpl_submit_value", getLL("save"));
	$smarty->assign("tpl_id", $id);
	$smarty->assign("tpl_action", ( ($mode == "new") ? "submit_new_group" : "submit_edit_group") );
	if($mode == 'edit') {
		$smarty->assign('tpl_submit_as_new', getLL('groups_as_new_group'));
		$smarty->assign('tpl_action_as_new', 'submit_new_group');
	}
	$smarty->assign("tpl_cancel", "list_groups");
	$smarty->assign("tpl_groups", $frmgroup);
	$smarty->assign("help", ko_get_help("groups", "new_group"));

	$smarty->display('ko_formular.tpl');
}//ko_groups_formular_group()




function ko_groups_formular_role($mode, $id = "") {
	global $KOTA;

  if($mode == 'edit') {
		if(!$id) return FALSE;
  } else if($mode == 'new') {
		$id = 0;
  } else return FALSE;

	$form_data['title'] =  $mode == 'new' ? getLL('groups_new_role') : getLL('groups_edit_role');
	$form_data['submit_value'] = getLL('save');
	$form_data['action'] = $mode == 'new' ? 'submit_new_role' : 'submit_edit_role';
	if($mode == 'edit') {
		$form_data['action_as_new'] = 'submit_as_new_role';
		$form_data['label_as_new'] = getLL('grouproles_submit_as_new');
	}
	$form_data['cancel'] = 'list_roles';

	ko_multiedit_formular('ko_grouproles', '', $id, '', $form_data);
}//ko_groups_formular_role()




function ko_groups_formular_datafield($mode, $id = "") {
  global $KOTA, $access;

	if($mode != 'edit' || !$id || $access['groups']['MAX'] < 3) return FALSE;

	$form_data['title'] = getLL('groups_edit_datafield');
	$form_data['submit_value'] = getLL('save');
	$form_data['action'] = 'submit_edit_datafield';
	$form_data['cancel'] = 'list_datafields';

	ko_multiedit_formular('ko_groups_datafields', '', $id, '', $form_data);
}//ko_groups_formular_datafield()





/**
 * Show selected groups nicely for rights form
 */
function rec_groups_select(&$show_groups, $pid='', &$v, &$o) {
	foreach($show_groups as $id => $g) {
		if($g['p'] == $pid) {
			$v[] = 'g'.$g['v'];
			$o[] = $g['o'];
			unset($show_groups[$id]);
			rec_groups_select($show_groups, $g['v'], $v, $o);
			reset($show_groups);
		}
	}
	//Add all groups not attached to a group with NULL as pid
	if($pid == '') {
		foreach($show_groups as $id => $g) {
			$v[] = 'g'.$g['v'];
			$o[] = $g['o'];
		}
	}
}//rec_groups_select()




function ko_groups_rights_formular($id) {
	global $ko_path, $smarty;
	global $all_groups, $access;

	if($access['groups']['ALL'] < 3) return FALSE;
	if(!$id || ($id == ko_get_root_id() && $_SESSION["ses_userid"] != ko_get_root_id()) ) return FALSE;

	//Get access rights for selected login
	$login = db_select_data("ko_admin", "WHERE `id` = '$id'", "id,login,leute_id");

	$access_all = ko_get_access_all('groups', $id);

	foreach(array('view', 'new', 'edit', 'del') as $numlevel => $level) {
		${'show_'.$level} = $access_all < ($numlevel+1);
		${'rights_'.$level.'_avalues'} = ${'rights_'.$level.'_aoutput'} = array();
		if(${'show_'.$level}) {
			$show_groups = array();
			$accessable_groups = db_select_data('ko_groups', "WHERE `rights_$level` REGEXP '(^|,)$id(,|$)'");
			foreach($accessable_groups as $g) {
				$motherline = ko_groups_get_motherline($g['id'], $all_groups);
				$fullgid = sizeof($motherline) > 0 ? 'g'.implode(':g', $motherline).':g'.$g['id'] : 'g'.$g['id'];
				$name = ko_groups_decode($fullgid, 'group_desc_full');
				$show_groups[$g['id']] = array('p' => $g['pid'], 'v' => $g['id'], 'o' => $name);
			}
			rec_groups_select($show_groups, '', ${'rights_'.$level.'_avalues'}, ${'rights_'.$level.'_aoutput'});
		}
	}

	if($login[$id]["leute_id"]) {
		ko_get_person_by_id($login[$id]["leute_id"], $p);
		$login_name = $p["vorname"]." ".$p["nachname"]." (".$login[$id]["login"].")";
	} else {
		$login_name = $login[$id]["login"];
	}


	//Formular aufbauen
	$rowcounter = 0;
	$gc = 0;
	$frmgroup[$gc]["row"][$rowcounter++]["inputs"][0] = array("desc" => getLL("form_groups_rights_login"),
															 "type" => "html",
															 "value" => $login_name,
															 "colspan" => 'colspan="2"',
															 );
	$frmgroup[$gc]["row"][$rowcounter++]["inputs"][0] = array("type" => "   ");

	$frmgroup[$gc]['row'][$rowcounter++]['inputs'][0] = array('desc' => getLL('form_groups_rights_help_title'),
															 'type' => 'html',
															 'value' => getLL('form_groups_rights_help'),
															 "colspan" => 'colspan="2"',
															 );
	$frmgroup[$gc]['row'][$rowcounter++]['inputs'][0] = array('type' => '   ');

	if($show_view) {
		$frmgroup[$gc]["row"][$rowcounter]["inputs"][0] = array("desc" => getLL("form_groups_rights_rights_view"),
																 "type" => "dyndoubleselect",
															 	 "js_func_add" => "double_select_add",
																 "name" => "sel_rights_view",
																 "avalues" => $rights_view_avalues,
																 "avalue" => implode(",", $rights_view_avalues),
																 "adescs" => $rights_view_aoutput,
																 "params" => 'size="10"',
																 "nochecklist" => TRUE,
																 );
	}//if(show_view)
	else {
		$frmgroup[$gc]["row"][$rowcounter]["inputs"][0] = array("desc" => getLL("form_groups_rights_rights_view"),
																 "type" => "html",
																 "value" => getLL("form_groups_all_groups")
																 );
	}
	if($show_new) {
		$frmgroup[$gc]["row"][$rowcounter++]["inputs"][1] = array("desc" => getLL("form_groups_rights_rights_new"),
																 "type" => "dyndoubleselect",
															 	 "js_func_add" => "double_select_add",
																 "name" => "sel_rights_new",
																 "avalues" => $rights_new_avalues,
																 "avalue" => implode(",", $rights_new_avalues),
																 "adescs" => $rights_new_aoutput,
																 "params" => 'size="10"',
																 "nochecklist" => TRUE,
																 );
	}//if(show_new)
	else {
		$frmgroup[$gc]["row"][$rowcounter++]["inputs"][1] = array("desc" => getLL("form_groups_rights_rights_new"),
																 "type" => "html",
																 "value" => getLL("form_groups_all_groups")
																 );
	}

	if($show_edit) {
		$frmgroup[$gc]["row"][$rowcounter]["inputs"][0] = array("desc" => getLL("form_groups_rights_rights_edit"),
																 "type" => "dyndoubleselect",
															 	 "js_func_add" => "double_select_add",
																 "name" => "sel_rights_edit",
																 "avalues" => $rights_edit_avalues,
																 "avalue" => implode(",", $rights_edit_avalues),
																 "adescs" => $rights_edit_aoutput,
																 "params" => 'size="10"',
																 "nochecklist" => TRUE,
																 );
	}//if(show_edit)
	else {
		$frmgroup[$gc]["row"][$rowcounter]["inputs"][0] = array("desc" => getLL("form_groups_rights_rights_edit"),
																 "type" => "html",
																 "value" => getLL("form_groups_all_groups")
																 );
	}

	if($show_del) {
		$frmgroup[$gc]["row"][$rowcounter++]["inputs"][1] = array("desc" => getLL("form_groups_rights_rights_del"),
																 "type" => "dyndoubleselect",
															 	 "js_func_add" => "double_select_add",
																 "name" => "sel_rights_del",
																 "avalues" => $rights_del_avalues,
																 "avalue" => implode(",", $rights_del_avalues),
																 "adescs" => $rights_del_aoutput,
																 "params" => 'size="10"',
																 "nochecklist" => TRUE,
																 );
	}//if(show_del)
	else {
		$frmgroup[$gc]["row"][$rowcounter++]["inputs"][1] = array("desc" => getLL("form_groups_rights_rights_del"),
																 "type" => "html",
																 "value" => getLL("form_groups_all_groups")
																 );
	}


	$smarty->assign("tpl_titel", getLL("groups_rights_title"));
	$smarty->assign("tpl_submit_value", getLL("save"));
	$smarty->assign("tpl_id", $id);
	$smarty->assign("tpl_action", "submit_edit_login_rights");
	$smarty->assign("tpl_cancel", "list_rights");
	$smarty->assign("tpl_groups", $frmgroup);

	$smarty->display('ko_formular.tpl');
}//ko_groups_rights_formular()






function ko_groups_list_rights() {
	global $smarty, $ko_path, $access;

	if($access['groups']['ALL'] < 3) return FALSE;

	$z_where = $z_limit = "";
	$z_where = " AND 1=1 ";

	if($_SESSION["ses_userid"] != ko_get_root_id()) {
		$z_where = " AND `id` <> '".ko_get_root_id()."' ";
	}

	$rows = db_get_count("ko_admin", "id", $z_where);
	$select_where = "WHERE".substr($z_where, 4);  //Erstes AND mit WHERE ersetzen
	$select_where .= " AND (`disabled` = '' OR `disabled` = '0')";
	$_logins = db_select_data("ko_admin", $select_where, "id,login,leute_id", "ORDER BY login ASC");
	//Find logins with group module installed
	$logins = array();
	foreach($_logins as $login) {
		if(ko_module_installed("groups", $login["id"])) {
			$logins[$login["id"]] = $login;
		} else {
			$rows--;
		}
	}

	//Statistik über Suchergebnisse und Anzeige
  $stats_end = $rows;
  $smarty->assign('tpl_stats', "1 - ".$stats_end." ".getLL("list_oftotal")." ".$rows);

	//Don't show page browser here
  $smarty->assign('tpl_prevlink_link', '');
  $smarty->assign('tpl_nextlink_link', '');
	$smarty->assign('hide_listlimiticons', TRUE);


	//Header-Informationen
	$smarty->assign("tpl_show_3cols", TRUE);

	$show_cols = array(getLL('groups_listheader_rights_user'), getLL('groups_listheader_rights_read'), getLL('groups_listheader_rights_assign'), getLL('groups_listheader_rights_edit'), getLL('groups_listheader_rights_del'));
  foreach($show_cols as $c_i => $c) {
    $tpl_table_header[$c_i]["sort"] = "";
    $tpl_table_header[$c_i]["name"] = $show_cols[$c_i];
  }
  $smarty->assign("tpl_table_header", $tpl_table_header);
  $smarty->assign("show_sort", FALSE);


	//Liste füllen
	foreach($logins as $l_i => $l) {
		//Get access rights for this login
		$user_access = ko_get_access('groups', $l['id'], TRUE, TRUE, 'login', FALSE);

		//Checkbox
		$tpl_list_data[$l_i]["show_checkbox"] = TRUE;

		//Edit-Button
		if($user_access['groups']['ALL'] > 2) {  //Wenn schon alle ALL-Rechte vergeben sind, dann nicht bearbeiten lassen
			$tpl_list_data[$l_i]["show_edit_button"] = FALSE;
		} else {
			$tpl_list_data[$l_i]["show_edit_button"] = TRUE;
			$tpl_list_data[$l_i]["alt_edit"] = getLL("groups_edit_rights");
			$tpl_list_data[$l_i]["onclick_edit"] = "javascript:set_action('edit_login_rights', this);set_hidden_value('id', '$l_i', this);this.submit";
		}

		//Delete-Button
		$tpl_list_data[$l_i]["show_delete_button"] = FALSE;

		//Index
		$tpl_list_data[$l_i]["id"] = $l_i;

		//Name
		if($l["leute_id"]) {
			ko_get_person_by_id($l["leute_id"], $p);
			$login_name = $p["vorname"]." ".$p["nachname"]." (".$l["login"].")";
		} else {
			$login_name = $l["login"];
		}
		$tpl_list_data[$l_i][1] = ko_html($login_name);

		//View-Rechte
		if($user_access['groups']['ALL'] > 0) {
			$tpl_list_data[$l_i][2] = getLL("groups_rights_all_groups");
		} else if($user_access['groups']['MAX'] > 0) {
			$num = db_get_count('ko_groups', 'id', "AND `rights_view` REGEXP '(^|,)$l_i(,|$)'");
			$num2 = 0;
			foreach($user_access['groups'] as $k => $v) if(intval($k) && $v > 0) $num2++;
			$tpl_list_data[$l_i][2] = $num.' => '.$num2.' '.getLL('groups_groups');
		}

		//New-Rechte
		if($user_access['groups']['ALL'] > 1) {
			$tpl_list_data[$l_i][3] = getLL("groups_rights_all_groups");
		} else if($user_access['groups']['MAX'] > 1) {
			$num = db_get_count('ko_groups', 'id', "AND `rights_new` REGEXP '(^|,)$l_i(,|$)'");
			$num2 = 0;
			foreach($user_access['groups'] as $k => $v) if(intval($k) && $v > 1) $num2++;
			$tpl_list_data[$l_i][3] = $num.' => '.$num2.' '.getLL('groups_groups');
		}

		//Edit-Rechte
		if($user_access['groups']['ALL'] > 2) {
			$tpl_list_data[$l_i][4] = getLL("groups_rights_all_groups");
		} else if($user_access['groups']['MAX'] > 2) {
			$num = db_get_count('ko_groups', 'id', "AND `rights_edit` REGEXP '(^|,)$l_i(,|$)'");
			$num2 = 0;
			foreach($user_access['groups'] as $k => $v) if(intval($k) && $v > 2) $num2++;
			$tpl_list_data[$l_i][4] = $num.' => '.$num2.' '.getLL('groups_groups');
		}

		//Del-Rechte
		if($user_access['groups']['ALL'] > 3) {
			$tpl_list_data[$l_i][5] = getLL("groups_rights_all_groups");
		} else if($user_access['groups']['MAX'] > 2) {
			$num = db_get_count('ko_groups', 'id', "AND `rights_del` REGEXP '(^|,)$l_i(,|$)'");
			$num2 = 0;
			foreach($user_access['groups'] as $k => $v) if(intval($k) && $v > 3) $num2++;
			$tpl_list_data[$l_i][5] = $num.' => '.$num2.' '.getLL('groups_groups');
		}

	}//foreach(groups)

	$smarty->assign("help", ko_get_help("groups", "list_rights"));

	$smarty->assign("tpl_list_title", getLL("groups_rights_list_title"));
	$smarty->assign('tpl_list_cols', array(1, 2, 3, 4, 5));
  $smarty->assign('tpl_list_data', $tpl_list_data);
	$smarty->display('ko_list.tpl');
}//ko_groups_list_rights()





/**
  * Fragt vor dem Löschen einer Rolle nochmals genau nach
	*/
function ko_delete_role($id) {
	if(!$id) return;

	print '<div style="padding:10px; background-color:#aa5500; color:white; font-weight:900; display:block;">';
	print getLL("groups_delete_role_confirm1").'<br />';
	print getLL("groups_delete_role_confirm2").'<br /><br />';
	print getLL("groups_delete_role_confirm3").'<br />';
	print '</div>';
	print '<input type="submit" name="ja" value="'.getLL("yes").'" onclick="javascript:'."set_action('do_delete_role', this);set_hidden_value('id', '$id', this);".'" />&nbsp;&nbsp;&nbsp;';
	print '<input type="submit" name="nein" value="'.getLL("no").'" onclick="javascript:'."set_action('list_roles', this);".'" />';
}//ko_delete_role()







/**
  * Überprüft bestehende Gruppeneinteilungen aller Leute und bereinigt Probleme mit nicht mehr vorhandenen Gruppen/Rollen
	*/
function ko_update_groups_and_roles($edit_gid='', $edit_rid='') {
	global $all_groups;

	//Alle Gruppen und Rollen
	$all_group_ids = array_keys($all_groups);
	ko_get_grouproles($all_roles);
	$all_role_ids = array_keys($all_roles);

	//Find all addresses which need to be updated
	if($edit_gid != '') {  //Only update addresses assigned to the given group
		$leute = db_select_data("ko_leute", "WHERE `groups` LIKE '%g$edit_gid%'");
	} else if($edit_rid != '') {  //Only update addresses assigned to the given role
		$leute = db_select_data("ko_leute", "WHERE `groups` LIKE '%r$edit_rid%'");
	} else {  //Update all addresses
		$leute = db_select_data("ko_leute", "WHERE `groups` <> ''");
	}
	foreach($leute as $p) {
		$groups = explode(",", $p["groups"]);
		foreach($groups as $g_i => $group) {
			if(!$group) continue;
			$g = ko_groups_decode($group, "group_id");
			$r = ko_groups_decode($group, "role_id");
			//current motherline
			$m = ko_groups_decode($group, "mother_line");
			//correct motherline
			$motherline = ko_groups_get_motherline($g, $all_groups);

			if(!in_array($g, $all_group_ids)) {  //Group has been deleted
				$g = "";
			} else if(array_unique(array_merge($motherline, $m)) != $m) {  //Fix Parent-Relationsships (group or a group in the motherline has been moved)
				$m = $motherline;
			} else if(implode(':', $motherline) == '' && implode(':', $m) != '') {  //Group moved to the top, $motherline is empty array
				$m = $motherline;
			}

			//Remove role if role does not exist anymore or is not assigned to this group anymore
			if($r != '' && (!in_array($r, $all_role_ids) || !in_array($r, explode(',', $all_groups[$g]['roles'])))) {
				$r = '';
			}
			//Neuen Wert zusammenbauen
			if($g) {
				$groups[$g_i]  = sizeof($m) > 0 ? "g".implode(":g", $m).":" : "";
				$groups[$g_i] .= "g$g";
				$groups[$g_i] .= $r ? ":r$r" : "";
			} else {
				unset($groups[$g_i]);
			}
		}//foreach(groups as g_i => group)

		//Falls geändert, so in DB speichern
		$groups = array_unique($groups);
		if(implode(",", $groups) != $p["groups"]) {
			db_update_data("ko_leute", "WHERE `id` = '".$p["id"]."'", array("groups" => implode(",", $groups)));
		}

	}//foreach(leute as p)
}//ko_update_groups_and_roles()




function ko_update_group_filterpresets() {
	global $all_groups;

	//Get group filter
	$group_filter = db_select_data('ko_filter', "WHERE `name` = 'group'", '*', '', '', TRUE);
	$group_filter_id = $group_filter['id'];


	// Update userprefs where a filter presets storing the sorting for a group datafield column (indexing changed)
	$prefs = db_select_data('ko_userprefs', "WHERE `type` = 'filterset'", '*', '', '', FALSE, TRUE);
	foreach($prefs as $pref) {
		$set = unserialize($pref['value']);

		$update = FALSE;
		foreach($set as $k => $v) {
			if(!is_numeric($k)) continue;  //ignore link, sort, cols, etc
			if($v[0] != $group_filter_id) continue;  //Ignore other filters
			if(FALSE === strpos($v[1][1], ':')) continue;  //Ignore group filters with a group on the top level

			$old_gid = $v[1][1];
			$gid = ko_groups_decode($old_gid, 'group_id');
			$rid = ko_groups_decode($old_gid, 'role_id');

			//Get new full gid
			$motherline = ko_groups_get_motherline($gid, $all_groups);
			$mids = array();
			foreach($motherline as $mg) {
				$mids[] = 'g'.$all_groups[$mg]['id'];
			}
			$full_id = (sizeof($mids) > 0 ? implode(':', $mids).':' : '').'g'.$gid;
			if($rid) $full_id .= ':r'.$rid;

			//Update filter if group has been moved
			if($full_id != $old_gid) {
				$update = TRUE;
				$set[$k][1][1] = $full_id;
			}
		}

		//Update preset
		if($update) {
			db_update_data('ko_userprefs', "WHERE `user_id` = '".$pref['user_id']."' AND `type` = 'filterset' AND `key` = '".$pref['key']."'", array('value' => serialize($set)));
		}
	}

}//ko_update_group_filterpresets()





/**
  * Displays settings
	*/
function ko_groups_settings() {
	global $smarty, $ko_path;

	//build form
	$gc = 0;
	$rowcounter = 0;
	$frmgroup[$gc]['titel'] = getLL('settings_title_user');

	//Default view and list limit
	$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('groups_settings_default_view'),
			'type' => 'select',
			'name' => 'sel_default_view',
			'values' => array('list_groups', 'list_roles', 'list_rights'),
			'descs' => array(getLL('submenu_groups_list_groups'), getLL('submenu_groups_list_roles'), getLL('submenu_groups_list_rights')),
			'value' => ko_html(ko_get_userpref($_SESSION['ses_userid'], 'default_view_groups'))
			);
	$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('admin_settings_limits_numberof_groups'),
			'type' => 'text',
			'params' => 'size="10"',
			'name' => 'txt_limit_groups',
			'value' => ko_html(ko_get_userpref($_SESSION['ses_userid'], 'show_limit_groups'))
			);

	//Show passed groups
	$value = ko_get_userpref($_SESSION['ses_userid'], 'show_passed_groups');
	$frmgroup[$gc]['row'][$rowcounter++]['inputs'][0] = array('desc' => getLL('admin_settings_options_show_passed_groups'),
			'type' => 'switch',
			'name' => 'chk_show_passed_groups',
			'label_0' => getLL('no'),
			'label_1' => getLL('yes'),
			'value' => $value == '' ? 0 : $value,
			);
	//Add column with filter when switch to people module
	$value = ko_get_userpref($_SESSION['ses_userid'], 'groups_filterlink_add_column');
	$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('groups_settings_filterlink_add_column'),
			'type' => 'switch',
			'name' => 'chk_groups_filterlink_add_column',
			'label_0' => getLL('no'),
			'label_1' => getLL('yes'),
			'value' => $value == '' ? 0 : $value,
			);
	//Show top level
	$value = ko_get_userpref($_SESSION['ses_userid'], 'groups_show_top');
	$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('groups_settings_show_top'),
			'type' => 'switch',
			'name' => 'chk_groups_show_top',
			'label_0' => getLL('no'),
			'label_1' => getLL('yes'),
			'value' => $value == '' ? 0 : $value,
			);


	//Allow plugins to add further settings
	hook_form('groups_settings', $frmgroup, '', '');


	//display the form
	$smarty->assign('tpl_titel', getLL('groups_settings_form_title'));
	$smarty->assign('tpl_submit_value', getLL('save'));
	$smarty->assign('tpl_action', 'submit_groups_settings');
	$smarty->assign('tpl_cancel', 'list_groups');
	$smarty->assign('tpl_groups', $frmgroup);
	$smarty->assign('help', ko_get_help('groups', 'groups_settings'));

	$smarty->display('ko_formular.tpl');
}//ko_groups_settings()



/**
 * Export address to EZMLM mailinglist
 * All members of the given group will be added to the appropriate ezmlm mailinglist set for this group
 */
function ko_export_group_to_ezmlm($gid) {
	$group = db_select_data("ko_groups", "WHERE `id` = '$gid' ".ko_get_groups_zwhere(), "*", "", "", TRUE);
	if(!check_email($group["ezmlm_list"]) || !check_email($group["ezmlm_moderator"])) return FALSE;

	$emails = array();
	$persons = db_select_data("ko_leute", "WHERE `groups` REGEXP 'g$gid'", "id,email");
	foreach($persons as $person) {
		if(check_email($person["email"])) $emails[] = strtolower($person["email"]);
	}
	$emails = array_unique($emails);
	foreach($emails as $email) {
		ko_ezmlm_subscribe($group["ezmlm_list"], $group["ezmlm_moderator"], $email);
	}
}//ko_export_group_to_ezmlm()





/**
 * Given a group ID to start with this returns an array with all it's subgroups (recursively) and it's members by roles.
 * @param string group ID (e.g. 000023) to start with
 * @return array
 */
function ko_groups_get_recursive_with_members($startgid) {
	$data = array();
	$counter = 0;
	$level = 0;

	$roles = db_select_data('ko_grouproles', 'WHERE 1');

	if(is_array($startgid)) {
		ko_groups_rec_members($startgid, $data, $counter, $roles, $level);
	} else {
		$where = $startgid == 'NULL' ? '`pid` IS NULL' : "`pid` = '$startgid'";
		$groups = db_select_data('ko_groups', 'WHERE '.$where, '*', 'ORDER BY `name` ASC');
		ko_groups_rec_members($groups, $data, $counter, $roles, $level);
	}

	return $data;
}//ko_groups_get_recursive_with_members()



/**
 * Recursion function for ko_groups_get_recursive_with_members()
 * Goes through all given groups (1st argument) and recursively through it's subgroups.
 * For each group it adds all the assigned addresses groups by roles.
 * @param array All groups to go through of the first level
 * @param &array Data array which will be filled with all groups, roles and addresses
 * @param &int Counter for the groups that are stored in $data
 * @param &array An array of all roles, used to populate $data array
 * @param &int Recursion level which is stored together with each group, which represents each group's hierarchy level
 */
function ko_groups_rec_members($groups, &$data, &$counter, &$roles, &$level) {
	$deleted = " AND `deleted` = '0' AND `hidden` = '0' ";

	$level++;
	foreach($groups as $group) {
		$useGroup = FALSE;
		$fullgid = ko_groups_decode($group['id'], 'full_gid');
		$data[$counter] = array('group' => $group, 'level' => $level);
		foreach(explode(',', $group['roles']) as $rid) {
			$people = db_select_data('ko_leute', "WHERE `groups` REGEXP '$fullgid:r$rid(,|$)'".$deleted, '*', 'ORDER BY nachname, vorname ASC');
			if(sizeof($people) > 0) {
				$useGroup = TRUE;
				$data[$counter]['roles'][$rid]['role'] = $roles[$rid];
				$data[$counter]['roles'][$rid]['people'] = $people;
			}
		}
		//Get all people assigned to this group without role
		$people = db_select_data('ko_leute', "WHERE `groups` REGEXP '$fullgid(,|$)'".$deleted, '*', 'ORDER BY nachname, vorname ASC');
		if(sizeof($people) > 0) {
			$useGroup = TRUE;
			$data[$counter]['people'] = $people;
		}

		//Get subgroups
		$subgroups = db_select_data('ko_groups', "WHERE `pid` = '".$group['id']."'", '*', 'ORDER BY `name` ASC');

		if(sizeof($subgroups) > 0) {
			//Include group with subgroups for the structure
			$counter++;
			//Add subgroups by recursive call
			ko_groups_rec_members($subgroups, $data, $counter, $roles, $level);
		} else {
			//Only include group with no subgroups if members have been found
			if($useGroup) $counter++;
		}
	}
	$level--;

}//ko_groups_rec_members()
?>
