<?php
/*******************************************************************************
*
*    OpenKool - Online church organization tool
*
*    Copyright © 2003-2015 Renzo Lauper (renzo@churchtool.org)
*    Copyright © 2019      Daniel Lerch
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
if(!isset($_GET['sesid'])) exit;
if(FALSE === session_id($_GET['sesid'])) exit;

//Send headers to ensure UTF-8 charset
header('Content-Type: text/html; charset=UTF-8');
 
error_reporting(0);
$ko_menu_akt = 'tools';
$ko_path = '../../';
require __DIR__ . '/../../inc/ko.inc.php';
$ko_path = '../';

ko_include_kota(array('ko_scheduler_tasks'));

// Plugins einlesen:
$hooks = hook_include_main('tools');
foreach($hooks as $hook) include_once($hook);
 
//Smarty-Templates-Engine laden
require __DIR__ . '/../../inc/smarty.inc.php';
 
require __DIR__ . '/tools.inc.php';

//HOOK: Submenus einlesen
$hooks = hook_include_sm();
foreach($hooks as $hook) include($hook);

hook_show_case_pre($_SESSION['show']);

 
if(isset($_GET) && isset($_GET['action'])) {
 	$action = format_userinput($_GET['action'], 'alphanum');

	hook_ajax_pre($ko_menu_akt, $action);

 	switch($action) {
 
		case 'setsort':
			$_SESSION['sort_tasks'] = format_userinput($_GET['sort'], 'alphanum+', TRUE, 30);
			$_SESSION['sort_tasks_order'] = format_userinput($_GET['sort_order'], 'alpha', TRUE, 4);

			print 'main_content@@@';
			print ko_list_tasks(FALSE);
		break;


		case 'setfiltergroup':
			$id = format_userinput($_GET['id'], 'uint');
			$group = format_userinput($_GET['group'], 'alpha');
			if($id > 0 && getLL('filter_group_'.$group) != '') {
				db_update_data('ko_filter', "WHERE `id` = '$id'", array('group' => $group));
				print 'INFO@@@Filter group has been updated.';
			} else {
				print 'ERROR@@@Update not possible.';
			}
		break;

	}//switch(action);

	hook_ajax_post($ko_menu_akt, $action);

}//if(GET[action])
?>
