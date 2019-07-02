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

//Set session id from GET (session will be started in ko.inc)
if(!isset($_GET["sesid"])) exit;
if(FALSE === session_id($_GET["sesid"])) exit;

//Send headers to ensure latin1 charset
header('Content-Type: text/html; charset=ISO-8859-1');

error_reporting(0);
$ko_menu_akt = 'tapes';
$ko_path = "../../";
require($ko_path."inc/ko.inc");
$ko_path = "../";

//Get access rights
ko_get_access('tapes');
if($access['tapes']['MAX'] < 1) exit;

// Plugins einlesen:
$hooks = hook_include_main("tapes");
if(sizeof($hooks) > 0) foreach($hooks as $hook) include_once($hook);

//Smarty-Templates-Engine laden
require($BASE_PATH."inc/smarty.inc");

require($BASE_PATH."tapes/inc/tapes.inc");

//HOOK: Submenus einlesen
$hooks = hook_include_sm();
if(sizeof($hooks) > 0) foreach($hooks as $hook) include($hook);

hook_show_case_pre($_SESSION['show']);


if(isset($_GET) && isset($_GET["action"])) {
	$action = format_userinput($_GET["action"], "alphanum");

	hook_ajax_pre($ko_menu_akt, $action);

	switch($action) {

		case "setsorttapes":
			if($access['tapes']['MAX'] < 4) continue;

			$_SESSION["sort_tapes"] = format_userinput($_GET["sort"], "alphanum+", TRUE, 30);
			$_SESSION["sort_tapes_order"] = format_userinput($_GET["sort_order"], "alpha", TRUE, 4);

			print "main_content@@@";
			ko_tapes_list(FALSE);
		break;

		case "setstart":
			//Set list start
			if(isset($_GET['set_start'])) {
				$_SESSION['show_start'] = max(1, format_userinput($_GET['set_start'], 'uint'));
	    }
			//Set list limit
			if(isset($_GET['set_limit'])) {
				$_SESSION['show_limit'] = max(1, format_userinput($_GET['set_limit'], 'uint'));
				ko_save_userpref($_SESSION['ses_userid'], 'show_limit_tapes', $_SESSION['show_limit']);
	    }

			print "main_content@@@";
			if($_SESSION['show'] == 'list_tapes') {
				print ko_tapes_list(FALSE);
			} else if($_SESSION['show'] == 'list_tapegroups') {
				print ko_tapes_list_tapegroups(FALSE);
			} else if($_SESSION['show'] == 'list_series') {
				print ko_tapes_list_series(FALSE);
			}
		break;

	}//switch(action);

	hook_ajax_post($ko_menu_akt, $action);

}//if(GET[action])
?>
