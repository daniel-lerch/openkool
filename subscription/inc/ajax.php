<?php
/*******************************************************************************
*
*    OpenKool - Online church organization tool
*
*    Copyright © 2003-2020 Renzo Lauper (renzo@churchtool.org)
*    Copyright © 2019-2020 Daniel Lerch
*
*    This program is free software; you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation; either version 2 of the License, or
*    (at your option) any later version.
*
*    This program is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*******************************************************************************/

//Set session id from GET (session will be started in ko.inc.php)
if(!isset($_GET["sesid"])) exit;
if(FALSE === session_id($_GET["sesid"])) exit;

//Send headers to ensure UTF-8 charset
header('Content-Type: text/html; charset=UTF-8');

error_reporting(0);
$ko_menu_akt = 'subscription';
$ko_path = "../../";
require __DIR__ . '/../../inc/ko.inc.php';
$ko_path = "../";

array_walk_recursive($_GET,'utf8_decode_array');

ko_get_access('subscription');
if($access['subscription']['MAX'] < 1) exit;
ko_include_kota(array('ko_subscription_forms', 'ko_subscription_form_groups'));

// Plugins einlesen:
$hooks = hook_include_main("subscription");
if(sizeof($hooks) > 0) foreach($hooks as $hook) include_once($hook);
 
//Smarty-Templates-Engine laden
require __DIR__ . '/../../inc/smarty.inc.php';

require __DIR__ . '/subscription.inc.php';

//HOOK: Submenus einlesen
$hooks = hook_include_sm();
foreach($hooks as $hook) include($hook);

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
				ko_save_userpref($_SESSION['ses_userid'], 'show_limit_forms', $_SESSION['show_limit']);
	    }

			print "main_content@@@";
			ko_subscription_form_list();
		break;


		case "setsort":
			$_SESSION["sort_forms"] = format_userinput($_GET["sort"], "alphanum+", TRUE, 80);
			$_SESSION["sort_forms_order"] = format_userinput($_GET["sort_order"], "alpha", TRUE, 4);

			print "main_content@@@";
			ko_subscription_form_list();
		break;
	}

	hook_ajax_post($ko_menu_akt, $action);

}//if(GET[action])
