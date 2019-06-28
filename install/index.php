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

ob_start();  //Ausgabe-Pufferung starten

//check for disabled install tool
if(!file_exists(getcwd()."/ENABLE_INSTALL")) {
	die("Installation is disabled, please create the file ENABLE_INSTALL in the /install directory");
}

$ko_path = "../";
$ko_menu_akt = "install";

include($ko_path . "inc/ko.inc");
//include("inc/install.inc");

$notifier = koNotifier::Instance();

ob_end_flush();  //Puffer flushen

//***Action auslesen:
if($_POST["action"]) {
	$do_action = $_POST["action"];
	$action_mode = "POST";
} else if($_GET["action"]) {
	$do_action = $_GET["action"];
	$action_mode = "GET";
} else {
	$do_action = "select_language";
	$action_mode = "";
}

switch($do_action) {
	case "select_language":
		$cur_state = 1;
		$_SESSION["show"] = "select_language";
	break;


	case "set_lang":
		$cur_state = 2;
		$_SESSION["show"] = "checks";
	break;


	case "set_db":
		$cur_state = 3;
		$_SESSION["show"] = "set_db";
	break;


	case "submit_set_db":
		$cur_state = 3;

		$user = format_userinput($_POST["txt_user"], "text");
		$pass = format_userinput($_POST["txt_pass"], "text");
		$server = format_userinput($_POST["txt_server"], "text");
		$database = format_userinput($_POST["sel_db"], "text");

		if(!$user || !$pass || !$server) $notifier->addError(1, $do_action);
		if(!$notifier->hasErrors()) {
			$data  = '$mysql_user = "'.$user.'";'."\n";
			$data .= '$mysql_pass = "'.$pass.'";'."\n";
			$data .= '$mysql_server = "'.$server.'";'."\n";
			$data .= '$mysql_db = "'.$database.'";'."\n";

			ko_update_ko_config("db", $data);

			//make these settings available in this script
			$mysql_user = $user; $mysql_pass = $pass; $mysql_server = $server; $mysql_db = $database;
		}//if(!error)
	break;


	case "submit_db_import":
		//Import SQL
		$sql_filename = $ko_path."install/kOOL_db.sql";
		if(file_exists($sql_filename)) {
			$sql = file_get_contents($sql_filename);
			db_import_sql($sql);
		}

		//Add SQL for chosen language
		$sql_ll_filename = $ko_path."install/db_".$_SESSION["lang"].".sql";
		if(file_exists($sql_ll_filename)) {
			$sql_ll = file_get_contents($sql_ll_filename);
			db_import_sql($sql_ll);
		}

		$cur_state = 4;
		$_SESSION["show"] = "settings";
	break;


	case "submit_db_import_skip":
		$cur_state = 4;
		$_SESSION["show"] = "settings";
	break;


	case "submit_settings":
		//Paths
		$html_title = format_userinput($_POST["txt_html_title"], "text");
		if($html_title) ko_update_ko_config("html_title", ('$HTML_TITLE = "'.$html_title.'";'."\n"));
		$base_url = format_userinput($_POST["txt_base_url"], "text");
		if($base_url) ko_update_ko_config("base_url", ('$BASE_URL = "'.$base_url.'";'."\n"));
		$base_path = format_userinput($_POST["txt_base_path"], "text");
		if(substr($base_path, -1) != "/") $base_path .= "/";
		if($base_path) ko_update_ko_config("base_path", ('$BASE_PATH = "'.$base_path.'";'."\n"));

		//modules
		$sel_modules = explode(",", $_POST["sel_modules"]);
		foreach($sel_modules as $mod) {
			if(in_array($mod, $LIB_MODULES)) $save_modules[] = $mod;
		}
		$data = '$MODULES = array(';
		foreach($save_modules as $mod) {
			$data .= '"'.$mod.'", ';
		}
		if (strlen($data) >= 2) {
			$data = substr($data, 0, -2).");";
		}
		ko_update_ko_config("modules", $data."\n");
		$MODULES = $save_modules;

		//languages
		$sel_lang = explode(",", $_POST["sel_lang"]);
		foreach($sel_lang as $lang) {
			list($l, $l2) = explode('_', $lang);
			if(in_array($l, $LIB_LANGS) && in_array($l2, $LIB_LANGS2[$l])) $save_lang[] = $lang;
		}
		$data = '$WEB_LANGS = array(';
		if(sizeof($save_lang) > 0) {
			foreach($save_lang as $lang) {
				$data .= '"'.$lang.'", ';
			}
			$data = substr($data, 0, -2).");";
		} else {
			$data .= ');';
		}
		ko_update_ko_config("web_langs", $data."\n");

		//Get language from browser
		$data  = '$GET_LANG_FROM_BROWSER = ';
		$data .= $_POST["chk_lang_from_browser"] ? "TRUE;\n" : "FALSE;\n";
		ko_update_ko_config("get_lang_from_browser", $data);

		//SMS parameters
		$sms_api_id = format_userinput($_POST["txt_sms_api_id"], "uint");
		$sms_user = format_userinput($_POST["txt_sms_user"], "text");
		$sms_pass = format_userinput($_POST["txt_sms_pass"], "text");
		$data  = sprintf('$SMS_PARAMETER = array("user" => "%s", "pass" => "%s", "api_id" => "%s");', $sms_user, $sms_pass, $sms_api_id)."\n";
		ko_update_ko_config("sms", $data);

		//warranty
		$warranty_giver = format_userinput($_POST["txt_warranty_giver"], "text");
		$warranty_url = $_POST["txt_warranty_url"];
		$warranty_email = $_POST["txt_warranty_email"];
		if(substr($warranty_url, 0, 7) != "http://") $warranty_url = "http://".$warranty_url;
		$data  = sprintf('@define("WARRANTY_GIVER", "%s");', $warranty_giver)."\n";
		$data .= sprintf('@define("WARRANTY_EMAIL", "%s");', $warranty_email)."\n";
		$data .= sprintf('@define("WARRANTY_URL", "%s");', $warranty_url)."\n";
		ko_update_ko_config("warranty", $data);

		//webfolders
		$use_webfolders = $_POST["chk_webfolders"] ? "TRUE" : "FALSE";
		$data = sprintf('@define("WEBFOLDERS", %s);', $use_webfolders)."\n";
		ko_update_ko_config("webfolders", $data);


		//root user
		if($_POST["txt_root_pass1"] != "" && $_POST["txt_root_pass1"] == $_POST["txt_root_pass2"]) {
			$root_pass = $_POST["txt_root_pass1"];
			if(ko_get_root_id()) {  //already root account in db
				ko_save_admin("password", ko_get_root_id(), md5($root_pass));
			} else {  //add new entry for root
				$data = NULL;
				$data["login"] = "root";
				$data["password"] = md5($root_pass);
				$data["modules"] = implode(",", $MODULES);
				foreach($MODULES as $mod) {
					if(in_array($mod, array('sms', 'tools', 'mailing'))) continue;

					if($mod == "daten") $key = "event_admin";
					elseif($mod == "reservation") $key = "res_admin";
					elseif($mod == "admin") $key = "admin";
					else $key = $mod."_admin";

					if(in_array($mod, array('admin', 'reservation', 'rota'))) $value = 5;
					else $value = 4;

					$data[$key] = $value;
				}

				$id = db_insert_data("ko_admin", $data);

				//Submenus als Userprefs speichern
				foreach($MODULES as $m) {
					$sm = implode(",", ko_get_submenus($m."_left"));
					ko_save_userpref($id, "submenu_".$m."_left", $sm, "");
					$sm = implode(",", ko_get_submenus($m."_right"));
					ko_save_userpref($id, "submenu_".$m."_right", $sm, "");
				}
				//Zusätzliche Userpref-Defaults setzen
				foreach($DEFAULT_USERPREFS as $d) {
					ko_save_userpref($id, $d["key"], $d["value"], $d["type"]);
				}
			}
		}//if(pass1 && pass1 == pass2)

		include($ko_path."config/ko-config.php");

		//check all the necessary data
		if(!$BASE_PATH) $notifier->addError(3, $do_action);
		if(!$BASE_URL) $notifier->addError(4, $do_action);
		if(sizeof($MODULES) < 1) $notifier->addError(5, $do_action);
		if(sizeof($WEB_LANGS) < 1) $notifier->addError(6, $do_action);

		if(!$notifier->hasErrors()) {
			//disable install-tool
			@unlink($BASE_PATH."install/ENABLE_INSTALL");
			//display done-message
			$cur_state = 5;
			$_SESSION["show"] = "done";
		} else {
			$cur_state = 4;
		}
	break;


	//Default:
  default:
    include($ko_path."inc/abuse.inc");
  break;
}//switch(do_action)


//Smarty-Templates-Engine laden
$smarty_dir = $ko_path;
require("$ko_path/inc/smarty.inc");


?>
<!DOCTYPE html 
  PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php print $_SESSION["lang"]; ?>" lang="<?php print $_SESSION["lang"]; ?>">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
<title><?php print getLL("install_welcome"); ?></title>
<?php
print ko_include_css();

print ko_include_js(array($ko_path.'inc/jquery/jquery.js', $ko_path.'inc/kOOL.js'));

include($ko_path.'inc/js-sessiontimeout.inc');
?>
</head>

<body onload="<?php print $onload_code; ?>">

<?php
//Don't display menu and header, but install-header:
?>
<!-- Message-Box for ajax-requests -->
<!-- position:fixed IE-Hack von annevankesteren.nl/test/examples/ie/position-fixed.html -->
<div style="visibility:hidden;display:none;padding:10px;margin:5px 170px 10px 10px;background-color:#ddd;border:2px solid #3586bd;position:fixed;_position:absolute;right:0;top:0;_top:expression(eval(document.body.scrollTop));z-index:900;width:125px;text-align:center;" name="wait_message" id="wait_message"><img src="<?php print $ko_path; ?>images/load_anim.gif" /></div>

<div id="kool-text">
<a href="http://www.churchtool.org">
<img src="<?php print $ko_path.$FILE_LOGO_SMALL; ?>" border="0" alt="kOOL" title="kOOL" />
</a>
</div>

<div class="menu">
<ul id="nav"><li><a class="first"><?php print getLL("install_title"); ?></a></li></ul>
</div>
<br clear="all" />


<form action="index.php" method="post" name="formular">  <!-- Hauptformular -->
<input type="hidden" name="action" id="action" value="" />
<input type="hidden" name="id" id="id" value="" />

<table width="100%">
<tr> 

<!-- Hauptbereich -->
<td class="main" name="main_content" id="main_content">
<?php
$states = array(
	array("id" => 1, "name" => "lang"),
	array("id" => 2, "name" => "checks"),
	array("id" => 3, "name" => "db"),
	array("id" => 4, "name" => "settings"),
	array("id" => 5, "name" => "done")
	);

print '<div class="install_progress">';
foreach($states as $state) {
	$active = ($state["id"] <= $cur_state);
	print '<img src="'.$ko_path.'images/install_state'.($active?'':'_disabled').'.gif" border="0" />';
	print ($active?'<b>':'').getLL("install_state_name_".$state["name"]).($active?'</b>':'')."&nbsp;&nbsp;&nbsp;";
}
print '</div><br />';

switch($_SESSION["show"]) {
	case "select_language":
		print getLL("install_lang_header").'<br />';
		//Lang-Selection
		if(sizeof($LIB_LANGS) > 1) {
			$lang_code = "";
			foreach($LIB_LANGS as $lang) {
				print '<div class="install_select_lang">';
				print '<a href="index.php?action=set_lang&amp;set_lang='.$lang.'">';
				print '<img src="'.$ko_path.'images/flag_'.$lang.'.png" border="0"><br /><br />'.getLL("install_langname_$lang");
				print '</a></div>';
			}
		}//if(sizeof(LANGS) > 1)
	break;  //select_language


	case "checks":
		print '<h1>'.getLL("install_checks_header").'</h1>';

		//Check for smarty
		print '<div>'.getLL("install_checks_smarty")."</div>";
		if(FALSE === include_once("Smarty.class.php")) {
			print '<div style="color: red;">'.getLL("install_checks_smarty_error").'</div>';
			$notifier->addError(7, $do_action);
		} else print '<div style="color: green;">'.getLL("OK")."</div>";

		//Check for filesystem permissions
		$check_files = array("config/ko-config.php", "config/leute_formular.inc",
												 "download/excel", "download/pdf",
												 "my_images",
												 "templates_c",
												 "webfolders", ".webfolders"
												 );
		foreach($check_files as $file) {
			print '<div>'.getLL("install_checks_files_writable").": <b>".$file.'</b></div>';
			if(is_writable($ko_path.$file)) {
				print '<div style="color: green;">'.getLL("OK").'</div>';
			} else {
				$notifier->addError(7, $do_action);
				print '<div style="color: red;">'.getLL("install_checks_files_writable_error").'</div>';
			}
		}//foreach(check_files as file)

		//Check for safe_mode
		print '<div>'.getLL("install_checks_safe_mode")."</div>";
		if(ini_get('safe_mode')) {
			$notifier->addError(7, $do_action);
			print '<div style="color: red;">'.getLL("install_checks_safe_mode_on").'</div>';
		} else {
			print '<div style="color: green;">'.getLL("OK").'</div>';
		}


		if(!$notifier->hasErrors()) {
			print '<br /><div align="center">';
			print '<input type="submit" name="submit" onclick="set_action(\'set_db\');" value="'.getLL("next").'" />';
			print '</div>';
		}

	break;  //checks


	case "set_db":
		//Try to connect to MySQL-Server
		if($mysql_user && $mysql_pass && $mysql_server) {
			$d = mysql_connect($mysql_server, $mysql_user, $mysql_pass);
			if(!$d) {
				$error_txt_add .= mysql_error();
				$notifier->addError(1, $do_action, array($error_txt_add));
			} else {
				$result = mysql_query("SHOW DATABASES");
				$databases = NULL;
				while($row = mysql_fetch_array($result)) {
					$databases[] = $row[0];
				}
			}
		} else {
			$notifier->addError(2, $do_action);
		}

		//Display error if needed
		if($notifier->hasErrors()) {
			$notifier->logToFile();
			$notifier->display();
		}

		//Formular anzeigen
		$rowcounter = 0;
		$gc = 0;
		$frmgroup[$gc]["row"][$rowcounter]["inputs"][0] = array("desc" => getLL("install_db_user"),
																 "type" => "text",
																 "name" => "txt_user",
																 "value" => (isset($_POST["txt_user"]) ? ko_html($_POST["txt_user"]) : $mysql_user),
																 "params" => 'size="40"',
																 );
		$frmgroup[$gc]["row"][$rowcounter++]["inputs"][1] = array("desc" => getLL("install_db_password"),
																 "type" => "password",
																 "name" => "txt_pass",
																 "value" => (isset($_POST["txt_pass"]) ? ko_html($_POST["txt_pass"]) : $mysql_pass),
																 "params" => 'size="40"',
																 );
		$frmgroup[$gc]["row"][$rowcounter]["inputs"][0] = array("desc" => getLL("install_db_server"),
																 "type" => "text",
																 "name" => "txt_server",
																 "value" => (isset($_POST["txt_server"]) ? ko_html($_POST["txt_server"]) : $mysql_server),
																 "params" => 'size="40"',
																 );
		if($d) {
			$frmgroup[$gc]["row"][$rowcounter++]["inputs"][1] = array("desc" => getLL("install_db_db"),
																	 "type" => "select",
																	 "name" => "sel_db",
																	 "values" => $databases,
																	 "descs" => $databases,
																	 "value" => (isset($_POST["sel_db"]) ? ko_html($_POST["sel_db"]) : $mysql_db),
																	 "params" => 'size="0"',
																	 );
		}
		//Display Button to continue if db connection can be established
		if(!$notifier->hasErrors() && $mysql_db) {
			$ok  = '<b>'.getLL("install_db_ok").'</b>';
			$ok .= '<br /><br />'.sprintf(getLL("install_db_import_text"), $mysql_db);
			$ok .= '<br />'.getLL("install_db_import_text_2");
			$ok .= '<br /><br /><input type="submit" value="'.getLL("install_db_import_button").'" name="submit_db_import" onclick="set_action(\'submit_db_import\');this.submit;" style="font-weight: 900;" />';
			$ok .= '&nbsp;&nbsp;&nbsp;<input type="submit" value="'.getLL("install_db_import_button_2").'" name="submit_db_import_skip" onclick="set_action(\'submit_db_import_skip\');this.submit;" />';
			$frmgroup[$gc]["row"][$rowcounter++]["inputs"][0] = array("type" => "   ");
			$frmgroup[$gc]["row"][$rowcounter]["inputs"][0] = array("desc" => getLL("install_db_ok_header"),
																	 "type" => "html",
																	 "value" => $ok,
																	 "colspan" => 'colspan="2"',
																	 );
		}

		$smarty->assign("tpl_titel", getLL("install_db_form_title"));
		$smarty->assign("tpl_hide_cancel", TRUE);
		$smarty->assign("tpl_submit_value", getLL("save"));
		$smarty->assign("tpl_action", "submit_set_db");
		$smarty->assign("tpl_groups", $frmgroup);

		$smarty->display('ko_formular.tpl');

	break;  //set_db



	case "settings":
		//Display error if needed
		if($notifier->hasErrors()) {
			$notifier->logToFile();
			$notifier->display();
		}

		//build form
		$gc = 0;
		$rowcounter = 0;

		//html title and paths
		$frmgroup[$gc]["row"][$rowcounter]["inputs"][0] = array("desc" => getLL("install_settings_paths_htmltitle"),
																 "type" => "text",
																 "name" => "txt_html_title",
																 "value" => $HTML_TITLE,
																 "params" => 'size="40"',
																 );
		$frmgroup[$gc]["row"][$rowcounter++]["inputs"][1] = array("desc" => getLL("install_settings_paths_baseurl"),
																 "type" => "text",
																 "name" => "txt_base_url",
																 "value" => $BASE_URL ? $BASE_URL : ("http://".$_SERVER["HTTP_HOST"]."/"),
																 "params" => 'size="40"',
																 );
		$doc_root = substr($_SERVER["DOCUMENT_ROOT"], -1) == "/" ? $_SERVER["DOCUMENT_ROOT"] : ($_SERVER["DOCUMENT_ROOT"]."/");
		$frmgroup[$gc]["row"][$rowcounter++]["inputs"][0] = array("desc" => getLL("install_settings_paths_basepath"),
																 "type" => "text",
																 "name" => "txt_base_path",
																 "value" => $BASE_PATH ? $BASE_PATH : $doc_root,
																 "params" => 'size="40"',
																 );

		//root account
		$frmgroup[$gc]["row"][$rowcounter++]["inputs"][0] = array("type" => "   ");
		$frmgroup[$gc]["row"][$rowcounter]["inputs"][0] = array("desc" => getLL("install_settings_root_password"),
																 "type" => "password",
																 "name" => "txt_root_pass1",
																 "value" => "",
																 "params" => 'size="40"',
																 );
		$frmgroup[$gc]["row"][$rowcounter++]["inputs"][1] = array("desc" => getLL("install_settings_root_password_2"),
																 "type" => "password",
																 "name" => "txt_root_pass2",
																 "value" => "",
																 "params" => 'size="40"',
																 );

		//modules for this installation
		$frmgroup[$gc]["row"][$rowcounter++]["inputs"][0] = array("type" => "   ");
		$descs = NULL; foreach($LIB_MODULES as $mod) $descs[] = getLL("module_".$mod);
		$adescs = NULL; foreach($MODULES as $mod) $adescs[] = getLL("module_".$mod);
		$frmgroup[$gc]["row"][$rowcounter++]["inputs"][0] = array("desc" => getLL("install_settings_modules_select"),
															 "type" => "doubleselect",
															 "js_func_add" => "double_select_add",
															 "name" => "sel_modules",
															 "values" => $LIB_MODULES,
															 "descs" => $descs,
															 "avalues" => $MODULES,
															 "avalue" => implode(",", $MODULES),
															 "adescs" => $adescs,
															 "params" => 'size="7"'
															 );
		//languages for this installation
		$frmgroup[$gc]["row"][$rowcounter++]["inputs"][0] = array("type" => "   ");
		//Get available languages
		$values = $descs = NULL;
		foreach($LIB_LANGS as $lang) {
			foreach($LIB_LANGS2[$lang] as $lang2) {
				$values[] = $lang.'_'.$lang2;
				$descs[] = getLL('install_langname_'.$lang.'_'.$lang2);
			}
		}
		//Get selected languages
		$avalues = $adescs = NULL;
		if(sizeof($WEB_LANGS) > 0) {
			foreach($WEB_LANGS as $lang) {
				list($l, $l2) = explode('_', $lang);
				if($l2 == '') continue;  //Only show 'new' language settings including region (de_CH)
				$adescs[] = getLL('install_langname_'.$lang);
				$avalues[] = $lang;
			}
			$avalue = implode(',', $avalues);
		} else {
			$avalues[] = $_SESSION['lang'].'_'.$LIB_LANGS2[$_SESSION['lang']][0];
			$adescs[] = getLL('install_langname_'.$_SESSION['lang'].'_'.$LIB_LANGS2[$_SESSION['lang']][0]);
			$avalue = $_SESSION['lang'].'_'.$LIB_LANGS2[$_SESSION['lang']][0];
		}
		$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('install_settings_lang_select'),
															 'type' => 'doubleselect',
															 'js_func_add' => 'double_select_add',
															 'name' => 'sel_lang',
															 'values' => $values,
															 'descs' => $descs,
															 'avalues' => $avalues,
															 'avalue' => $avalue,
															 'adescs' => $adescs,
															 'params' => 'size="5"'
															 );
		$frmgroup[$gc]["row"][$rowcounter++]["inputs"][1] = array("desc" => getLL("install_settings_lang_from_browser"),
															 "type" => "checkbox",
															 "name" => "chk_lang_from_browser",
															 "value" => "1",
															 "params" => $GET_LANG_FROM_BROWSER ? 'checked="checked"' : '',
															 );

		//sms
		$frmgroup[$gc]["row"][$rowcounter++]["inputs"][0] = array("type" => "   ");
		$frmgroup[$gc]["row"][$rowcounter]["inputs"][0] = array("desc" => getLL("install_settings_sms_apiid"),
																 "type" => "text",
																 "name" => "txt_sms_api_id",
																 "value" => $SMS_PARAMETER["api_id"],
																 "params" => 'size="40"',
																 );
		$frmgroup[$gc]["row"][$rowcounter++]["inputs"][1] = array("desc" => getLL("install_settings_sms_user"),
																 "type" => "text",
																 "name" => "txt_sms_user",
																 "value" => $SMS_PARAMETER["user"],
																 "params" => 'size="40"',
																 );
		$frmgroup[$gc]["row"][$rowcounter++]["inputs"][0] = array("desc" => getLL("install_settings_sms_pass"),
																 "type" => "password",
																 "name" => "txt_sms_pass",
																 "value" => $SMS_PARAMETER["pass"],
																 "params" => 'size="40"',
																 );
		//webfolders
		$frmgroup[$gc]["row"][$rowcounter++]["inputs"][0] = array("type" => "   ");
		$frmgroup[$gc]["row"][$rowcounter++]["inputs"][0] = array("desc" => getLL("install_settings_use_webfolders"),
																 "type" => "checkbox",
																 "name" => "chk_webfolders",
																 "value" => 1,
																 "params" => WEBFOLDERS ? 'checked="checked"' : '',
																 );

		//warranty
		$frmgroup[$gc]["row"][$rowcounter++]["inputs"][0] = array("type" => "   ");
		$frmgroup[$gc]["row"][$rowcounter]["inputs"][0] = array("desc" => getLL("install_settings_warranty_giver"),
																 "type" => "text",
																 "name" => "txt_warranty_giver",
																 "value" => defined("WARRANTY_GIVER") ? WARRANTY_GIVER : '',
																 "params" => 'size="40"',
																 );
		$frmgroup[$gc]["row"][$rowcounter++]["inputs"][1] = array("desc" => getLL("install_settings_warranty_url"),
																 "type" => "text",
																 "name" => "txt_warranty_url",
																 "value" => defined("WARRANTY_URL") ? WARRANTY_URL : '',
																 "params" => 'size="40"',
																 );
		$frmgroup[$gc]["row"][$rowcounter++]["inputs"][0] = array("desc" => getLL("install_settings_warranty_email"),
																 "type" => "text",
																 "name" => "txt_warranty_email",
																 "value" => defined("WARRANTY_EMAIL") ? WARRANTY_EMAIL : '',
																 "params" => 'size="40"',
																 );

		//display the form
		$smarty->assign("tpl_titel", getLL("install_settings_form_title"));
		$smarty->assign("tpl_hide_cancel", TRUE);
		$smarty->assign("tpl_submit_value", getLL("save"));
		$smarty->assign("tpl_action", "submit_settings");
		$smarty->assign("tpl_groups", $frmgroup);

		$smarty->display('ko_formular.tpl');
	break;  //settings



	case "done":
		print '<h2>'.getLL("install_done_header").'</h2>';
		print '<b>'.sprintf(getLL("install_done_warning"), ($BASE_PATH."install/")).'</b><br /><br />';
		print getLL("install_done_text");
		print '<br /><br />';
		print '<a href="'.$BASE_URL.'">'.getLL("install_done_link").'</a>';
	break;

}//switch(show)
?>
&nbsp;
</td>

</tr>

<?php
//--- copyright notice on frontpage:
//--- Obstructing the appearance of this notice is prohibited by law.
print '<tr><td colspan="3" class="copyright">';
print '<a href="https://sourceforge.net/projects/kool"><b>'.getLL("kool").'</b></a> '.sprintf(getLL("copyright_notice"), VERSION).'<br />';
if(defined("WARRANTY_GIVER")) {
	print sprintf(getLL("copyright_warranty"), '<a href="'.WARRANTY_URL.'">'.WARRANTY_GIVER.'</a> ');
} else {
	print getLL("copyright_no_warranty")." ";
}
print sprintf(getLL("copyright_free_software"), '<a href="http://www.fsf.org/licensing/licenses/gpl.html">', '</a>')."<br />";
print getLL("copyright_obstruction");
print '</td></tr>';
//--- end of copyright notice
?>

</table>
</form> <!-- //Hauptformular -->

</body>
</html>
