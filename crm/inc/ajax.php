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

//Set session id from GET (session will be started in ko.inc)
if(!isset($_GET['sesid'])) exit;
if(FALSE === session_id($_GET['sesid'])) exit;

//Send headers to ensure latin1 charset
header('Content-Type: text/html; charset=ISO-8859-1');

error_reporting(0);
$ko_menu_akt = 'crm';
$ko_path = '../../';
require($ko_path.'inc/ko.inc');
$ko_path = '../';

array_walk_recursive($_GET,'utf8_decode_array');

//Get access rights
ko_get_access('crm');
if($access['crm']['MAX'] < 1) exit;

ko_include_kota(array('ko_crm_projects', 'ko_crm_status', 'ko_crm_contacts'));

// Plugins einlesen:
$hooks = hook_include_main('crm');
if(sizeof($hooks) > 0) foreach($hooks as $hook) include_once($hook);

require($BASE_PATH.'crm/inc/crm.inc');

//HOOK: Submenus einlesen
$hooks = hook_include_sm();
if(sizeof($hooks) > 0) foreach($hooks as $hook) include($hook);

hook_show_case_pre($_SESSION['show']);


if(isset($_GET) && isset($_GET['action'])) {
	$action = format_userinput($_GET['action'], 'alphanum');

	hook_ajax_pre($ko_menu_akt, $action);

	switch($action) {

		case 'setfilter':
			$status = $_GET['status'];
			$field = $_GET['field'];
			$state = $_GET['state'];
			if(!in_array($field, array('project_status'))) break;

			switch($field) {
				case 'project_status':
					if(in_array($state, $_SESSION['crm_filter']['project_status'])) {
						$_SESSION['crm_filter']['project_status'] = array_diff($_SESSION['crm_filter']['project_status'], array($state));
					} else {
						$_SESSION['crm_filter']['project_status'][] = $state;
					}
					$_SESSION['crm_filter']['project_status'] = $_SESSION['crm_filter']['project_status'];
					ko_save_userpref($_SESSION['ses_userid'], 'crm_filter_project_status', serialize($_SESSION['crm_filter']['project_status']));
				break;
			}

			print 'main_content@@@';
			if($_SESSION['show'] == 'list_crm_projects') {
				ko_list_crm_projects();
			} else if($_SESSION['show'] == 'list_crm_status') {
				ko_list_crm_status();
			} else if($_SESSION['show'] == 'list_crm_contacts') {
				ko_list_crm_contacts();
			}
		break;

		case 'setstart':
			//Set list start
			if(isset($_GET['set_start'])) {
				$_SESSION['show_start'] = max(1, format_userinput($_GET['set_start'], 'uint'));
			}
			//Set list limit
			if(isset($_GET['set_limit'])) {
				$_SESSION['show_limit'] = max(1, format_userinput($_GET['set_limit'], 'uint'));
				ko_save_userpref($_SESSION['ses_userid'], 'show_limit_crm_contacts', $_SESSION['show_limit']);
			}

			print 'main_content@@@';
			if($_SESSION['show'] == 'list_crm_projects') {
				ko_list_crm_projects();
			} else if($_SESSION['show'] == 'list_crm_status') {
				ko_list_crm_status();
			} else if($_SESSION['show'] == 'list_crm_contacts') {
				ko_list_crm_contacts();
			}
		break;


		case 'setsort':
			if($_SESSION['show'] == 'list_crm_contacts') {
				$_SESSION['sort_crm_contacts'] = format_userinput($_GET['sort'], 'alphanum+', TRUE, 30);
				$_SESSION['sort_crm_contacts_order'] = format_userinput($_GET['sort_order'], 'alpha', TRUE, 4);

				print 'main_content@@@';
				ko_list_crm_contacts();
			}
			else if($_SESSION['show'] == 'list_crm_projects') {
				$_SESSION['sort_crm_projects'] = format_userinput($_GET['sort'], 'alphanum+', TRUE, 30);
				$_SESSION['sort_crm_projects_order'] = format_userinput($_GET['sort_order'], 'alpha', TRUE, 4);

				print 'main_content@@@';
				ko_list_crm_projects();
			}
			else if($_SESSION['show'] == 'list_crm_status') {
				$_SESSION['sort_crm_status'] = format_userinput($_GET['sort'], 'alphanum+', TRUE, 30);
				$_SESSION['sort_crm_status_order'] = format_userinput($_GET['sort_order'], 'alpha', TRUE, 4);

				print 'main_content@@@';
				ko_list_crm_status();
			}
		break;


		case "itemlist":
		case 'itemlistRedraw':
		case "itemlistgroup":

			if($action == 'itemlistRedraw') {
				$action = 'itemlist';
				$redraw = TRUE;
			}
			//ID and state of the clicked field
			$id = format_userinput($_GET["id"], "js");
			$state = $_GET["state"] == "true" ? "checked" : "";

			//Single project selected
			if($action == "itemlist") {
				if($state == "checked") {  //Select it
					if(!in_array($id, $_SESSION["show_crm_projects"])) $_SESSION["show_crm_projects"][] = $id;
				} else {  //deselect it
					if(in_array($id, $_SESSION["show_crm_projects"])) $_SESSION["show_crm_projects"] = array_diff($_SESSION["show_crm_projects"], array($id));
				}
			}
			//Project status selected or unselected
			else if($action == "itemlistgroup") {
				$projects = db_select_data("ko_crm_projects", "WHERE `project_status` = '$id'", "*", "ORDER BY `title` ASC");
				foreach($projects as $pid => $project) {
					if($state == "checked") {  //Select it
						if(!in_array($pid, $_SESSION["show_crm_projects"])) $_SESSION["show_crm_projects"][] = $pid;
					} else {  //Deselect it
						if(in_array($pid, $_SESSION["show_crm_projects"])) $_SESSION["show_crm_projects"] = array_diff($_SESSION["show_crm_projects"], array($pid));
					}
				}//foreach(groups)
			}

			//Get rid of invalid event group ids
			$allProjects = db_select_data('ko_crm_projects', 'WHERE 1', '*');
			$allProjects[0] = [
				"id" => "0",
				"number" => "",
				"title" => getLL('crm_projects_dummy_entry'),
			];

			foreach($_SESSION['show_crm_projects'] as $k => $pid) {
				if(!in_array($pid, array_keys($allProjects))) {
					unset($_SESSION['show_crm_projects'][$k]);
				}
			}

			//Save userpref
			sort($_SESSION["show_crm_projects"]);
			ko_save_userpref($_SESSION["ses_userid"], "show_crm_projects", implode(",", $_SESSION["show_crm_projects"]));

			print 'main_content@@@';
			ko_list_crm_contacts();

			//Find position of submenu for redraw
			if($action == "itemlistgroup" || $redraw) {
				print '@@@';
				print submenu_crm("itemlist_projects", "open", 2);
			}
		break;


		case "itemlisttogglegroup":
			//ID and state of the clicked field
			$id = format_userinput($_GET["id"], "js");
			if(isset($_SESSION["crm_project_status_states"][$id])) {
				$_SESSION["crm_project_status_states"][$id] = $_SESSION["crm_project_status_states"][$id] ? 0 : 1;
			} else {
				$_SESSION["crm_project_status_states"][$id] = ($_GET["state"] == 1 ? 0 : 1);
			}

			//Don't redraw the submenu, as this is done in JS so the list doesn't scroll of the mouse's position
		break;


		case 'itemlistsave':
			//save new value
			if($_GET['name'] == '') break;
			$new_value = implode(',', $_SESSION['show_crm_projects']);
			$user_id = $access['crm']['MAX'] > 3 && $_GET['global'] == 'true' ? '-1' : $_SESSION['ses_userid'];
			$name = format_userinput($_GET['name'], 'js', FALSE, 0, array('allquotes'));
			ko_save_userpref($user_id, $name, $new_value, 'crm_itemset');

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
					while (ko_get_userpref($lid, $n, 'crm_itemset')) {
						$c++;
						$n = "{$nameForOthers} - {$c}";
					}
					ko_save_userpref($lid, $n, $new_value, 'crm_itemset');
				}
			}

			print submenu_crm('itemlist_projects', 'open', 2);
		break;


		case 'itemlistopen':
			//save new value
			$name = format_userinput($_GET['name'], 'js', FALSE, 0, array(), '@');
			if($name == '') break;

			if($name == '_all_') {
				ko_get_crm_projects($projects);
				$_SESSION['show_crm_projects'] = array_merge(array(0), array_keys($projects));
			} else if($name == '_none_') {
				$_SESSION['show_crm_projects'] = array();
			} else {
				if(substr($name, 0, 3) == '@G@') $value = ko_get_userpref('-1', substr($name, 3), 'crm_itemset');
				else $value = ko_get_userpref($_SESSION['ses_userid'], $name, 'crm_itemset');
				$_SESSION['show_crm_projects'] = explode(',', $value[0]['value']);
			}
			ko_save_userpref($_SESSION['ses_userid'], 'show_crm_projects', implode(',', $_SESSION['show_crm_projects']));

			print submenu_crm('itemlist_projects', 'open', 2);
			print '@@@main_content@@@';
			ko_list_crm_contacts();
		break;


		case 'itemlistdelete':
			//Get name
			$name = format_userinput($_GET['name'], 'js', FALSE, 0, array(), '@');
			if($name == '') break;

			if(substr($name, 0, 3) == '@G@') {
				if($access['crm']['MAX'] > 3) ko_delete_userpref('-1', substr($name, 3), 'crm_itemset');
			} else ko_delete_userpref($_SESSION['ses_userid'], $name, 'crm_itemset');

			print submenu_crm('itemlist_projects', 'open', 2);
		break;

	}//switch(action);

	hook_ajax_post($ko_menu_akt, $action);

}//if(GET[action])
?>
