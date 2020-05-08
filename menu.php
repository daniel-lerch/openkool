<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2003-2015 Renzo Lauper (renzo@churchtool.org)
*  (c) 2019-2020 Daniel Lerch
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
?>
<!-- Message-Box for ajax-requests -->
<!-- position:fixed IE-Hack von annevankesteren.nl/test/examples/ie/position-fixed.html -->
<div style="display:none;padding:10px;margin:5px 180px 10px 10px;background-color:#ddd;border:2px solid #3586bd;position:fixed;_position:absolute;right:0;top:0;_top:expression(eval(document.body.scrollTop));z-index:900;width:125px;text-align:center;" name="wait_message" id="wait_message"><img src="<?php print $ko_path; ?>images/load_anim.gif" /></div>
<!-- Session timeout warning -->
<div style="visibility:hidden;display:none;padding:6px;margin:5px 180px 10px 10px;background-color:#ffff00;border:3px solid #c80202;position:fixed;_position:absolute;right:0;top:0;_top:expression(eval(document.body.scrollTop));z-index:900;width:180px;text-align:center;" name="session_timeout" id="session_timeout"><?php print getLL("session_timeout"); ?></div>

<div id="login">
<table cellpadding="0" cellspacing="0" style="padding: 0px; margin: 0px;" border="0"><tr><td height="50px" style="vertical-align:bottom;">
<?php
//Show login fields if not logged in yet
if(!$_SESSION["ses_username"] || $_SESSION["ses_username"] == "ko_guest") {
	print '<form method="post" action="'.$ko_path.'index.php"><div style="white-space:nowrap;">';
  print '<div>'.getLL("login_username").'<br /><input type="text" name="username" size="10" /></div>';
  print '<div>'.getLL("login_password").'<br /><input type="password" name="password" size="10" /></div>';
  print '<div><br /><input type="submit" value="'.getLL("login").'" name="Login" /></div>';
  print '</div></form>';
}
//Otherwise show logout link
else {
  print '<b>[ ' . $_SESSION['ses_username'] . ' ]</b>';
  print '&nbsp;&nbsp;<a href="'.$ko_path.'index.php?action=logout">';
	print '<i>'.getLL('login_logout').'</i></a>';
	$do_guest = FALSE;
}
?>
</td></tr></table>
</div>

<div id="kool-text">
<a href="http://www.churchtool.org">
<img src="<?php print $ko_path.$FILE_LOGO_SMALL; ?>" border="0" alt="kOOL" title="kOOL" />
</a>
</div>


<div id="lang-select">
<table cellpadding="0" cellspacing="0" style="padding: 0px; margin: 0px;" border="0"><tr><td height="50px" style="vertical-align:bottom;">
<?php
//Lang-Selection
if(sizeof($LANGS) > 1) {
	$lang_code =  '[&nbsp;';
	foreach($LANGS as $lang) {
		$pre  = ($lang == $_SESSION["lang"]) ? '<b>' : '';
		$post = ($lang == $_SESSION["lang"]) ? '</b>' : '';
		$lang_code .= '<a href="index.php?set_lang='.$lang.'">'.$pre.strtoupper($lang).$post.'</a>&nbsp;';
	}
	$lang_code .= ']';
	print $lang_code;
}//if(sizeof(LANGS) > 1)
?>
</td></tr></table>
</div>


<div id="header">
<?php include __DIR__ . '/config/header.php'; ?>
</div>

<?php
$do_dropdown = ko_get_userpref($_SESSION["ses_userid"], "modules_dropdown");

$user_menu_ = explode(",", ko_get_userpref($_SESSION["ses_userid"], "menu_order"));
$user_menu = array_merge($user_menu_, array_diff($MODULES, $user_menu_));

$menu_counter = 0;
foreach($user_menu as $m) {
	if(!in_array($m, $MODULES)) continue;
	if(in_array($m, array('sms', 'kg', 'mailing')) || trim($m) == '') continue;
	if(substr($m, 0, 3) == 'my_') continue;  //Don't show menus from plugins in main navigation (yet)
	if($m == 'tools' && $_SESSION['ses_userid'] != ko_get_root_id()) continue;
	if(ko_module_installed($m)) {
		$menu[$menu_counter]["id"] = $m;
		$menu[$menu_counter]["name"] = getLL("module_".$m);
		$action = ko_get_userpref($_SESSION["ses_userid"], "default_view_".$m);
		if(!$action) $action = ko_get_setting("default_view_".$m);
		//Handle special links (e.g. webfolders)
		if(substr($action, 0, 8) == "SPECIAL_") {
			switch(substr($action, 8)) {
				case "webfolder":
					$menu[$menu_counter]["link"] = "";
					$menu[$menu_counter]["link_param"] = 'FOLDER="'.$BASE_URL.str_replace($BASE_PATH, "", $WEBFOLDERS_BASE).'" style="behavior: url(#default#AnchorClick);"';
				break;
			}
		} else {
			$menu[$menu_counter]["link"] = $ko_path.$m."/index.php?action=$action";
		}

		//Dropdown-Menu
		if($do_dropdown == "ja") {
			$sm = NULL;
			//Get submenu-array
			if(function_exists('submenu_'.$m)) {
				eval(("\$dd_sm = submenu_".$m.'("'.implode(",", ko_get_submenus(($m."_dropdown"))).'", "", "open", 3);'));
				//Get open user-submenus in the right order
				$user_sm = array_merge(explode(",", ko_get_userpref($_SESSION["ses_userid"], "submenu_".$m."_left")), explode(",", ko_get_userpref($_SESSION["ses_userid"], "submenu_".$m."_right")));
				//Each entry is single submenu
				foreach($user_sm as $usm) {
					$entry = NULL;
					foreach($dd_sm as $dd) if($usm == $dd["id"]) $entry = $dd;
					if(!$entry) continue;

					$sm[] = array("name" => $entry["titel"], "link" => "");
					//Each non-empty output-element ist one entry from the submenu with a corresponding link-entry
					foreach($entry["output"] as $e_i => $e) {
						if($e) $sm[] = array("name" => $e, "link" => $entry["link"][$e_i]);
					}
				}
				$menu[$menu_counter]["menu"] = $sm;
			}
		}//if(do_dropdown == "ja")
	}//if(ko_module_installed(m)

	$menu_counter++;
}//foreach(MODULES as m)

$menu[] = "";

$smarty->assign("ko_path", $ko_path);
$smarty->assign("tpl_menu", $menu);
$smarty->assign("tpl_menu_akt", $ko_menu_akt);

$smarty->display("ko_menu.tpl");
?>
