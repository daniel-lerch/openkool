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
$ko_menu_akt = 'admin';
$ko_path = "../../";
require($ko_path."inc/ko.inc");
$ko_path = "../";

//Get access rights
ko_get_access('admin');

//Include KOTA for sms log
ko_include_kota(array('_ko_sms_log', 'ko_log'));

// Plugins einlesen:
$hooks = hook_include_main("admin");
if(sizeof($hooks) > 0) foreach($hooks as $hook) include_once($hook);

//Smarty-Templates-Engine laden
require($BASE_PATH."inc/smarty.inc");

require($BASE_PATH."admin/inc/admin.inc");

//HOOK: Submenus einlesen
$hooks = hook_include_sm();
if(sizeof($hooks) > 0) foreach($hooks as $hook) include($hook);

hook_show_case_pre($_SESSION['show']);


if(isset($_GET) && isset($_GET["action"])) {
	$action = format_userinput($_GET["action"], "alphanum");

	hook_ajax_pre($ko_menu_akt, $action);

	switch($action) {

		case 'setsortlogins':
			if($access['admin']['MAX'] < 5) continue;

			$_SESSION['sort_logins'] = format_userinput($_GET['sort'], 'alphanum+', TRUE, 30);
			$_SESSION['sort_logins_order'] = format_userinput($_GET['sort_order'], 'alpha', TRUE, 4);

			print 'main_content@@@';
			ko_set_logins_list(FALSE);
		break;

		case "setsortlog":
			if($access['admin']['MAX'] < 4) continue;

			$_SESSION["sort_logs"] = format_userinput($_GET["sort"], "alphanum+", TRUE, 30);
			$_SESSION["sort_logs_order"] = format_userinput($_GET["sort_order"], "alpha", TRUE, 4);

			print "main_content@@@";
			print ko_show_logs(FALSE);
		break;


		case "setstart":
			if($_SESSION['show'] == 'show_logins' || $_SESSION['show'] == 'show_sms_log') {
				if($access['admin']['MAX'] < 5) continue;

				//Set list start
				if(isset($_GET['set_start'])) {
					$_SESSION['show_start'] = max(1, format_userinput($_GET['set_start'], 'uint'));
				}
				//Set list limit
				if(isset($_GET['set_limit'])) {
					$_SESSION['show_limit'] = max(1, format_userinput($_GET['set_limit'], 'uint'));
					ko_save_userpref($_SESSION['ses_userid'], 'show_limit_logins', $_SESSION['show_limit']);
				}

				print "main_content@@@";
				if($_SESSION['show'] == 'show_sms_log') {
					print ko_show_sms_log(FALSE);
				} else {
					print ko_set_logins_list(FALSE);
				}
			}
			else if($_SESSION['show'] == 'show_logs') {
				if($access['admin']['MAX'] < 4) continue;

				//Set list start
				if(isset($_GET['set_start'])) {
					$_SESSION['show_logs_start'] = max(1, format_userinput($_GET['set_start'], 'uint'));
				}
				//Set list limit
				if(isset($_GET['set_limit'])) {
					$_SESSION['show_logs_limit'] = max(1, format_userinput($_GET['set_limit'], 'uint'));
					ko_save_userpref($_SESSION['ses_userid'], 'show_limit_logs', $_SESSION['show_logs_limit']);
				}

				print "main_content@@@";
				print ko_show_logs(FALSE);
			}
		break;


		case "ablelogin":
			if($access['admin']['MAX'] < 4) continue;

			$id = format_userinput($_GET["id"], "uint");
			if($id == ko_get_root_id()) {
				print 'ERROR@@@'.getLL('error_admin_disable_root');
				continue;
			}
			if($id == ko_get_guest_id()) {
				print 'ERROR@@@'.getLL('error_admin_disable_guest');
				continue;
			}
			if($id == $_SESSION['ses_userid']) {
				print 'ERROR@@@'.getLL('error_admin_disable_self');
				continue;
			}

			if($_GET["mode"] == "enabled") {
				$orig_hash = db_select_data("ko_admin", "WHERE `id` = '$id'", "login,disabled", "", "", TRUE);
				$data = array("password" => $orig_hash["disabled"], "disabled" => "");
				ko_log('enable_login', 'ID: '.$id.', '.$orig_hash['login']);
			} else if($_GET["mode"] == "disabled") {
				$orig_hash = db_select_data("ko_admin", "WHERE `id` = '$id'", "login,password", "", "", TRUE);
				$data = array("password" => md5($orig_hash), "disabled" => $orig_hash["password"]);
				ko_log('disable_login', 'ID: '.$id.', '.$orig_hash['login']);
			} else continue;

			db_update_data("ko_admin", "WHERE `id` = '$id'", $data);

			print "main_content@@@";
			print ko_set_logins_list(FALSE);
		break;


		case 'deletepic':
			if($access['admin']['MAX'] < 2) continue;

			$id = format_userinput($_GET['id'], 'alphanum');
			if(!$id) continue;

			//Check for picture for this label preset
			ko_get_etiketten_vorlage($id, $v);
			if($v['pic_file'] && file_exists($BASE_PATH.$v['pic_file']) && $BASE_PATH == substr(realpath($BASE_PATH.$v['pic_file']), 0, strlen($BASE_PATH))) {
				//Remove image from label preset
				db_update_data('ko_etiketten', "WHERE `vorlage` = '$id' AND `key` = 'pic_file'", array('value' => ''));
				//Remove file from my_images
				unlink(realpath($BASE_PATH.$v['pic_file']));
				//Clean up cached files
				ko_pic_cleanup_cache();
			}

			//Empty span containing preview
			print 'label_pic@@@ ';
		break;
	}//switch(action);

	hook_ajax_post($ko_menu_akt, $action);

}//if(GET[action])
?>
