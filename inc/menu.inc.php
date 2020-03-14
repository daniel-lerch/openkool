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

?>
<!-- Message-Box for ajax-requests -->
<!-- position:fixed IE-Hack von annevankesteren.nl/test/examples/ie/position-fixed.html -->
<div style="display:none;padding:10px;margin:5px 180px 10px 10px;background-color:#ddd;border:2px solid #3586bd;position:fixed;_position:absolute;right:0;top:0;_top:expression(eval(document.body.scrollTop));z-index:900;width:125px;text-align:center;" name="wait_message" id="wait_message"><img src="<?php print $ko_path; ?>images/load_anim.gif" /></div>
<!-- Session timeout warning -->
<div style="visibility:hidden;display:none;padding:6px;margin:5px 180px 10px 10px;background-color:#ffff00;border:3px solid #c80202;position:fixed;_position:absolute;right:0;top:0;_top:expression(eval(document.body.scrollTop));z-index:900;width:180px;text-align:center;" name="session_timeout" id="session_timeout"><?php print getLL("session_timeout"); ?></div>

for($i=0; $i<count($USER_MENU); $i++) {
	if($_SESSION['disable_password_change'] == 1 && $USER_MENU[$i]['action'] == "change_password") {
		unset($USER_MENU[$i]);
	}
}

if (sizeof($USER_MENU) > 0) {
	$USER_MENU[] = ko_get_menuitem_seperator();
}
$USER_MENU[] = ko_get_menuitem_link('', '', '', $ko_path.'index.php?action=logout', false, getLL('login_logout'));


$do_guest = $_SESSION["ses_username"] && $_SESSION["ses_username"] != "ko_guest";

$smarty->assign("user_menu", $USER_MENU);
$smarty->assign("ses_lang", $_SESSION['lang']);
$smarty->assign("langs", $LANGS);
$smarty->assign("file_logo_small", $FILE_LOGO_SMALL);
$smarty->assign("pre", $pre);
$smarty->assign("post", $post);
$smarty->assign("ses_username", $_SESSION["ses_username"]);

// include header
ob_start();
include __DIR__ . '/../config/header.php'
$smarty->assign("header_code", ob_get_clean());

$user_menu_ = explode(",", ko_get_userpref($_SESSION["ses_userid"], "menu_order"));
$user_menu = array_merge($user_menu_, array_diff($MODULES, $user_menu_));

$menu_counter = 0;
foreach($user_menu as $m) {
	if(!in_array($m, $MODULES)) continue;
	if(in_array($m, array('telegram', 'sms', 'kg', 'mailing', 'vesr')) || trim($m) == '') continue;
	if(substr($m, 0, 3) == 'my_') continue;  //Don't show menus from plugins in main navigation (yet)
	if($m == 'tools' && $_SESSION['ses_userid'] != ko_get_root_id()) continue;
	if($_SESSION['ses_userid'] == ko_get_checkin_user_id()) continue;
	if(ko_module_installed($m)) {
		$menu[$menu_counter]["id"] = $m;
		$menu[$menu_counter]["name"] = getLL("module_".$m);
		$action = ko_get_userpref($_SESSION["ses_userid"], "default_view_".$m);
		if(!$action) $action = ko_get_setting("default_view_".$m);
		$menu[$menu_counter]["link"] = $ko_path.$m."/index.php?action=$action";

		//Dropdown-Menu
		$sm = NULL;
		//Get submenu-array
		if(function_exists('submenu_'.$m)) {
			$dd_sm = call_user_func_array('submenu_' . $m, array(implode(",", ko_get_submenus(($m."_dropdown"))), 'open', 3, false));
			// get user pref order for submenus
			$user_sm = unserialize(ko_get_userpref($_SESSION['ses_userid'], 'submenu_' . $m));
			//Each entry is single submenu
			foreach($user_sm as $usmName => $usmData) {
				$entry = NULL;
				foreach($dd_sm as $dd) if($usmName == $dd["id"]) $entry = $dd;
				if(!$entry) continue;

				$sm[] = array("title" => $entry["titel"]);
				//Each non-empty output-element ist one entry from the submenu with a corresponding link-entry
				foreach($entry["items"] as $e) {
					if($e && $e['type'] == 'link') {
						$sm[] = $e;
					}
				}
			}
			$menu[$menu_counter]["menu"] = $sm;
		}
	}//if(ko_module_installed(m)

	$menu_counter++;
}//foreach(MODULES as m)

// Settings page

if($_SESSION['ses_userid'] != ko_get_guest_id()) {
	$settingsPage = $MODULE_SETTINGS_ACTION[$ko_menu_akt];
	if (!$settingsPage) {
		$settingsLL = getLL($ko_menu_akt . '_settings');
		if ($settingsLL) $settingsPage = $ko_menu_akt . '_settings';
	}
	$smarty->assign('settings_page', $settingsPage);
}

$module_settings_action = array();
foreach ($MODULE_SETTINGS_ACTION as $k => $v) {
	switch ($k) {
		case 'donations':
			$all_rights = ko_get_access_all('donations_admin', '', $max_rights);
			if ($max_rights < 1 || $_SESSION['ses_userid'] == ko_get_guest_id()) continue 2;
			break;
		case 'daten':
			$all_rights = ko_get_access_all('event_admin', '', $max_rights);
			if ($max_rights < 1 || $_SESSION['ses_userid'] == ko_get_guest_id()) continue 2;
			break;
		case 'reservation':
			$all_rights = ko_get_access_all('res_admin', '', $max_rights);
			if ($max_rights < 1 || $_SESSION['ses_userid'] == ko_get_guest_id()) continue 2;
			break;
		case 'tracking':
			$all_rights = ko_get_access_all('tracking_admin', '', $max_rights);
			if ($max_rights < 1 || $_SESSION['ses_userid'] == ko_get_guest_id()) continue 2;
			break;
		case 'rota':
			if($_SESSION['ses_userid'] == ko_get_guest_id()) continue 2;
			break;
	}
	$module_settings_action[$k] = $v;
}
$smarty->assign('module_settings_action', $module_settings_action);

$searchbox = ko_get_searchbox($ko_menu_akt);

ko_get_access("taxonomy");

if(ko_module_installed("taxonomy") && $access['taxonomy']['MAX'] >= 1 &&
$ko_menu_akt == "groups" && $_SESSION['show'] == "list_groups") {
	$searchbox_taxonomy = ko_get_searchbox_for_taxonomy_terms();
	$searchbox['taxonomy_select'] = $searchbox_taxonomy;
}

$smarty->assign('searchbox', $searchbox);

$menubarLinks = ko_get_secmenu_links($ko_menu_akt, $do_action);
$smarty->assign("tpl_menubar_links", $menubarLinks);

$state = ko_get_userpref($_SESSION["ses_userid"], 'sidebar_state');
if ($state != 'closed') $sidebarActive = true;
else $sidebarActive = false;
$smarty->assign('tpl_sidebar_active', $sidebarActive);
$smarty->assign("ko_path", $ko_path);
$smarty->assign("tpl_menu", $menu);
$smarty->assign("tpl_menu_akt", $ko_menu_akt);
$smarty->assign('tpl_ses_show', $_SESSION['show']);
$smarty->assign("tpl_action", $do_action);

$smarty->display("ko_menu.tpl");


?>
