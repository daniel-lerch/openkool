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
if(!isset($_GET["sesid"])) exit;
if(FALSE === session_id($_GET["sesid"])) exit;

//Send headers to ensure latin1 charset
header('Content-Type: text/html; charset=ISO-8859-1');
 
error_reporting(0);
$ko_menu_akt = 'donations';
$ko_path = "../../";
require($ko_path."inc/ko.inc");
$ko_path = "../";

array_walk_recursive($_GET,'utf8_decode_array');

ko_get_access('donations');
if($access['donations']['MAX'] < 1) exit;
ko_include_kota(array('ko_donations', 'ko_donations_accounts', 'ko_donations_accountgroups'));

// Plugins einlesen:
$hooks = hook_include_main("donations");
if(sizeof($hooks) > 0) foreach($hooks as $hook) include_once($hook);
 
require($BASE_PATH."donations/inc/donations.inc");

//HOOK: Submenus einlesen
$hooks = hook_include_sm();
if(sizeof($hooks) > 0) foreach($hooks as $hook) include($hook);

hook_show_case_pre($_SESSION['show']);

 
if(isset($_GET) && isset($_GET["action"])) {
 	$action = format_userinput($_GET["action"], "alphanum");

	hook_ajax_pre($ko_menu_akt, $action);

 	switch($action) {
 
 		case "setstart":
			//Set list start
			if(isset($_GET['set_start'])) {
				$_SESSION['show_start'] = max(1, format_userinput($_GET['set_start'], 'uint'));
	    }
			//Set list limit
			if(isset($_GET['set_limit'])) {
				$_SESSION['show_limit'] = max(1, format_userinput($_GET['set_limit'], 'uint'));
				ko_save_userpref($_SESSION['ses_userid'], 'show_limit_donations', $_SESSION['show_limit']);
	    }

			print "main_content@@@";
			if($_SESSION["show"] == "list_donations") {
				ko_list_donations();
			} else {
				ko_list_accounts();
			}
		break;


		case "setsort":
			$_SESSION["sort_donations"] = format_userinput($_GET["sort"], "alphanum+", TRUE, 80);
			$_SESSION["sort_donations_order"] = format_userinput($_GET["sort_order"], "alpha", TRUE, 4);

			print "main_content@@@";
			if($_SESSION['show'] == 'list_donations') {
				ko_list_donations();
			} else if($_SESSION['show'] == 'list_reoccuring_donations') {
				ko_list_reoccuring_donations();
			}
		break;


		case "itemlist":
		case 'itemlistRedraw':
		case "itemlistgroup":

			$globalMinRights = 4;
			$groupColumn = 'accountgroup_id';
			$table = 'ko_donations_accounts';
			$tableOrdering = "ORDER BY `number` ASC, name ASC";
			$sessionShowKey = 'show_donations_accounts';
			$sessionStatesKey = 'donations_accounts_group_states';
			$presetType = 'accounts_itemset';
			$module = 'donations';


			if($action == 'itemlistRedraw') {
				$action = 'itemlist';
				$redraw = TRUE;
			}
			//ID and state of the clicked field
			$id = format_userinput($_GET["id"], "js");
			$state = $_GET["state"] == "true" ? "checked" : "";

			//Single group selected
			if($action == "itemlist") {
				if($state == "checked") {  //Select it
					if(!in_array($id, $_SESSION[$sessionShowKey])) $_SESSION[$sessionShowKey][] = $id;
				} else {  //deselect it
					if(in_array($id, $_SESSION[$sessionShowKey])) $_SESSION[$sessionShowKey] = array_diff($_SESSION[$sessionShowKey], array($id));
				}
			}
			//groups selected or unselected
			else if($action == "itemlistgroup") {
				if ($id == '@@archived@@') {
					$elements = db_select_data($table, "WHERE `archived` = 1", "*", $tableOrdering);
				} else {
					$elements = db_select_data($table, "WHERE `{$groupColumn}` = '$id' AND `archived` = 0", "*", $tableOrdering);
				}
				foreach($elements as $eid => $element) {
					if(!$access[$module][$eid]) continue;
					if($state == "checked") {  //Select it
						if(!in_array($eid, $_SESSION[$sessionShowKey])) $_SESSION[$sessionShowKey][] = $eid;
					} else {  //Deselect it
						if(in_array($eid, $_SESSION[$sessionShowKey])) $_SESSION[$sessionShowKey] = array_diff($_SESSION[$sessionShowKey], array($eid));
					}
				}//foreach(elements)
			}

			//Get rid of invalid element ids
			$allElements = db_select_data($table, "WHERE 1=1", "id, {$groupColumn}", '', '', FALSE, TRUE);
			$allElementIds = ko_array_column($allElements, 'id');
			foreach($_SESSION[$sessionShowKey] as $k => $eid) {
				if($access['donations']['ALL'] < 1 && $access['donations'][$eid] < 1) unset($_SESSION[$sessionShowKey][$k]);
				if(!in_array($eid, $allElementIds)) {
					unset($_SESSION[$sessionShowKey][$k]);
				}
			}

			//Save userpref
			ko_save_userpref($_SESSION["ses_userid"], $sessionShowKey, implode(",", $_SESSION[$sessionShowKey]));

			print "main_content@@@";
			switch($_SESSION["show"]) {
				case "list_donations":
					ko_list_donations();
					break;
				case "show_stats":
					ko_donations_stats();
					break;
			}

			//Find position of submenu for redraw
			if($action == "itemlistgroup" || $redraw) {
				if(in_array($_SESSION['show'], array('list_donations', 'show_stats'))) print '@@@';
				print submenu_donations("itemlist_accounts", "open", 2);
				$done = TRUE;
			} else if($allElements[$id][$groupColumn]) {
				//Update number of checked elements for this group
				$groupId = $allElements[$id][$groupColumn];
				$num = 0;
				foreach($allElements as $element) {
					if($access[$module][$element['id']] < 1) continue;
					if($element[$groupColumn] == $groupId && in_array($element['id'], $_SESSION[$sessionShowKey])) $num++;
				}
				if(in_array($_SESSION['show'], array('list_donations', 'show_stats'))) print '@@@';
				print 'group_'.$groupId.'@@@'.$num;
				$done = TRUE;
			}
		break;


		case "itemlistsave":

			//save new value
			if($_GET["name"] == "") break;
			$new_value = implode(",", $_SESSION["show_donations_accounts"]);
			$user_id = ($access['donations']['MAX'] > 3 && $_GET['global'] == 'true') ? '-1' : $_SESSION['ses_userid'];
			$name = format_userinput($_GET["name"], "js", FALSE, 0, array("allquotes"));
			ko_save_userpref($user_id, $name, $new_value, "accounts_itemset");

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
					while (ko_get_userpref($lid, $n, "accounts_itemset")) {
						$c++;
						$n = "{$nameForOthers} - {$c}";
					}
					ko_save_userpref($lid, $n, $new_value, "accounts_itemset");
				}
			}

			print submenu_donations("itemlist_accounts", "open", 2);
		break;


		case "itemlistopen":
			//Find position of submenu for redraw
			if(in_array("itemlist_accounts", explode(",", $_SESSION["submenu_left"]))) $pos = "left";
			else $pos = "right";

			//save new value
			$name = format_userinput($_GET['name'], 'js', FALSE, 0, array(), '@');
			if($name == "") break;

			if($name == '_all_') {
				$accounts = db_select_data('ko_donations_accounts', '');
				$_SESSION["show_donations_accounts"] = array_keys($accounts);
			} else if($name == '_none_') {
				$_SESSION['show_donations_accounts'] = array();
			} else {
				if(substr($name, 0, 3) == '@G@') $value = ko_get_userpref('-1', substr($name, 3), "accounts_itemset");
				else $value = ko_get_userpref($_SESSION['ses_userid'], $name, "accounts_itemset");
				$_SESSION["show_donations_accounts"] = explode(",", $value[0]["value"]);
			}
			//Access check
			foreach($_SESSION['show_donations_accounts'] as $k => $v) {
				if($access['donations']['ALL'] < 1 && $access['donations'][$v] < 1) unset($_SESSION['show_donations_accounts'][$k]);
			}

			ko_save_userpref($_SESSION['ses_userid'], 'show_donations_accounts', implode(',', $_SESSION['show_donations_accounts']));

			print "main_content@@@";
			switch($_SESSION["show"]) {
				case "list_donations":
					ko_list_donations();
				break;
				case "show_stats":
					ko_donations_stats();
				break;
			}
			print "@@@";
			print submenu_donations("itemlist_accounts", "open", 2);
		break;


		case "itemlistdelete":
			//Find position of submenu for redraw
			if(in_array("itemlist_accounts", explode(",", $_SESSION["submenu_left"]))) $pos = "left";
			else $pos = "right";

			//save new value
			$name = format_userinput($_GET['name'], 'js', FALSE, 0, array(), '@');
			if($name == "") break;

			if(substr($name, 0, 3) == '@G@') {
				if($kg_edit) ko_delete_userpref('-1', substr($name, 3), "accounts_itemset");
			} else ko_delete_userpref($_SESSION['ses_userid'], $name, "accounts_itemset");

			print submenu_donations("itemlist_accounts", "open", 2);
		break;

	}//switch(action);

	hook_ajax_post($ko_menu_akt, $action);

}//if(GET[action])
?>
