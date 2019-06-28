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

//Get access rights
ko_get_access('groups');

ko_include_kota(array('ko_groups', 'ko_grouproles', 'ko_groups_datafields'));

// Plugins einlesen:
$hooks = hook_include_main("groups");
if(sizeof($hooks) > 0) foreach($hooks as $hook) include_once($hook);

//Smarty-Templates-Engine laden
require($BASE_PATH."inc/smarty.inc");

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
			if($access['groups']['MAX'] < 3) continue;

			$_SESSION['sort_groups'] = format_userinput($_GET['sort'], 'alphanum+', TRUE, 30);
			ko_save_userpref($_SESSION['ses_userid'], 'sort_groups', $_SESSION['sort_groups']);
			$_SESSION['sort_groups_order'] = format_userinput($_GET['sort_order'], 'alpha', TRUE, 4);
			ko_save_userpref($_SESSION['ses_userid'], 'sort_groups_order', $_SESSION['sort_groups_order']);

			print 'main_content@@@';
			print ko_groups_list(FALSE);
		break;

		case "setstart":
			if($access['groups']['MAX'] < 1) continue;

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
			print ko_groups_list(FALSE);
		break;


		case "adddatafield":
			if($access['groups']['MAX'] < 2) continue;

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
			if(!$df["type"]) continue;

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
			print ko_groups_list(FALSE);
		break;

	}//switch(action);

	hook_ajax_post($ko_menu_akt, $action);

}//if(GET[action])
