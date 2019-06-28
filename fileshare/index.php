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

ob_start();  //Ausgabe-Pufferung einschalten

$ko_path = "../";
$ko_menu_akt = "fileshare";

include($ko_path . "inc/ko.inc");
include("inc/fileshare.inc");

//Redirect to SSL if needed
ko_check_ssl();

if(!ko_module_installed("fileshare")) {
	header("Location: ".$BASE_URL."index.php");  //Absolute URL
}

ob_end_flush();

//Endlose Ausführung
set_time_limit(0);		


$info = $error = 0;

//***Rechte auslesen:
ko_get_access('fileshare');

//*** Plugins einlesen:
$hooks = hook_include_main("fileshare");
if(sizeof($hooks) > 0) foreach($hooks as $hook) include_once($hook);


//***Action auslesen:
if($_POST["action"]) $do_action=$_POST["action"];
else if($_GET["action"]) $do_action=$_GET["action"];
else $do_action="";

switch($do_action) {

	case "new_share":
		if($access['fileshare']['MAX'] < 1) continue;
		$_SESSION["show"] = "new_share";
	break;

	case "new_folder":
		if($access['fileshare']['MAX'] < 3) continue;
		$_SESSION["show"] = "new_folder";
	break;

	case "edit_folder":
		if($access['fileshare']['MAX'] < 3) continue;
		$_SESSION["show"] = "edit_folder";
	break;

	case "send_file":
		if($access['fileshare']['MAX'] < 1) continue;

		$shares = array();
		$id = format_userinput($_POST["id"], "alphanum");
		if(!$id) {
			//Ausgewählte Dateien finden
			foreach($_POST["chk"] as $c_i => $c) {
				if($c) $shares[] = format_userinput($c_i, "alphanum", FALSE, 32);
			}
		} else {
			$shares[] = $id;
		}

		if(sizeof($shares) == 0) {
			$error = 3;
		} else {
			$_SESSION["show"] = "send_file";
			$file_ids = $shares;
		}
	break;





	case "delete_share":
	case "delete_shares":
		if($access['fileshare']['MAX'] < 2) continue;

		$ids = array();
		if($do_action == "delete_shares") {
			foreach($_POST["chk"] as $c_i => $c) {
				if($c) $ids[] = format_userinput($c_i, "alphanum");
			}
		} else {
			$id = format_userinput($_POST["id"], "alphanum");
			$ids = array($id);
		}

		foreach($ids as $id) {
			if(!$id || strlen($id) != 32) continue;

			$filter = " AND `id` = '$id' ";
			$share_ = ko_get_shares($filter); $share = $share_[0];

			$error = 0;
			if(!ko_fileshare_check_permission($_SESSION["ses_userid"], $share["parent"], "del_file")) $error = 6;

			if(!$error) {
				//DB-Eintrag löschen
				db_delete_data("ko_fileshare", "WHERE `id` = '$id' AND `user_id` = '".$_SESSION["ses_userid"]."'");;

				//Datei löschen
				if(file_exists($FILESHARE_FOLDER.$id)) {
					system("rm ".$FILESHARE_FOLDER.$id);
				}

				$log_message = $_SESSION["ses_username"]." (".$_SESSION["ses_userid"]."): File: ".$share["filename"] . ", ".$id;
				ko_log("delete_share", $log_message);
			}
		}//foreach(ids as id)
	break;



	case "move_shares":
		$ids = array();
		foreach($_POST["chk"] as $c_i => $c) {
			if($c) $ids[] = format_userinput($c_i, "alphanum");
		}
		$target_id = format_userinput($_POST["id"], "uint");

		if(!ko_fileshare_check_permission($_SESSION["ses_userid"], $target_id, "new_file") || !ko_fileshare_check_permission($_SESSION["ses_userid"], $_SESSION["folderid"], "del_file")) $error = 6;

		if(sizeof($ids) == 0) $error = 3;

		if(!$error) {
			foreach($ids as $id) {
				if(!$id || strlen($id) != 32) continue;
				db_update_data("ko_fileshare", "WHERE `id` = '$id'", array("parent" => $target_id));
			}
			$info = 3;
		}//if(!error)
	break;



	case "copy_shares":
		$ids = array();
		foreach($_POST["chk"] as $c_i => $c) {
			if($c) $ids[] = format_userinput($c_i, "alphanum");
		}
		$target_id = format_userinput($_POST["id"], "uint");

		if(!ko_fileshare_check_permission($_SESSION["ses_userid"], $target_id, "new_file")) $error = 6;
		if(sizeof($ids) == 0) $error = 3;

		if(!$error) {
			foreach($ids as $id) {
				if(!$id || strlen($id) != 32) continue;
				$file_ = ko_get_shares(" AND `id` = '$id'");
				$file = $file_[0];
      	$new_id = md5($file["filename"][$i].microtime());
				system("cd $FILESHARE_FOLDER && ln $id $new_id");
				ko_fileshare_save_share($new_id, $_SESSION["ses_userid"], $file["filename"], $file["type"], $target_id, $file["filesize"]);
			}
			$info = 6;
		}//if(!error)
	break;



	case "submit_upload2":
		//sid von Mega-Upload (mit Progressbar)
		$sid = $_REQUEST['sid'];
		$qstr = join("",file("/tmp/{$sid}_qstring"));
		unlink("/tmp/{$sid}_qstring");

		parse_str($qstr);

		//Ziel
		$save_target = format_userinput($sel_target, "uint");

		if(!ko_fileshare_check_permission($_SESSION["ses_userid"], $save_target, "new_file")) $error = 6;
		if($error) continue;

		$k = count($file['name']);
		for($i=0 ; $i < $k ; $i++) {
			$save_filename = ko_get_filename($file["name"][$i]);
      $save_id = md5($file["name"][$i].microtime());

			//Type
			$filename = $file["name"][$i];
			$idx = strtolower(end(explode('.', $filename)));
      $mimet = array(
          'doc' =>'application/msword',
          'dot' =>'application/msword',
          'xls' =>'application/msexcel',
          'xla' =>'application/msexcel',
          'ppt' =>'application/mspowerpoint',
          'pps' =>'application/mspowerpoint',
          'ppz' =>'application/mspowerpoint',
          'pot' =>'application/mspowerpoint',
          'pdf' =>'application/pdf',
          'zip' =>'application/zip',
          'exe' =>'application/octet-stream',
          'bin' =>'application/octet-stream',
          'com' =>'application/octet-stream',
          'dll' =>'application/octet-stream',
          'class' =>'application/octet-stream',
          'wav' =>'audio/x-wav',
          'gif' =>'image/gif',
          'jpg' =>'image/jpeg',
          'jpeg' =>'image/jpeg',
          'png' =>'image/png',
          'tif' =>'image/tiff',
          'tiff' =>'image/tiff',
          'mpg' =>'video/mpeg',
          'mpeg' =>'video/mpeg',
          'avi' =>'video/x-msvideo',
      );

      if (isset($mimet[$idx])) {
        $save_type = $mimet[$idx];
      } else {
        $save_type = 'application/octet-stream';
				//$save_type = mime_content_type($file["tmp_name"][$i]);
				//$save_type = exec("file -ib ".escapeshellcmd($file["tmp_name"][$i]));
      }

			//Dateigrösse holen
			clearstatcache();
			$file_size = filesize($file["tmp_name"][$i]);

			//Datei in richtigen Ordner kopieren und Rechte setzen
			rename($file['tmp_name'][$i], $FILESHARE_FOLDER.$save_id);
			chmod($FILESHARE_FOLDER.$save_id, 0644);

			if(!$error) {
				ko_fileshare_save_share($save_id, $_SESSION["ses_userid"], $save_filename, $save_type, $save_target, $file_size);
				$log_message = "$save_id: $save_filename ($file_size), $save_type";
				ko_log("new_share", $log_message);
			}//if(!error)
		}//for(i=0..count(file[]))

		if(!$error) {
			$info = 1;
			$_SESSION["folderid"] = $save_target;
		}//if(!error)

		$_SESSION["show"] = "list_shares";
	break;



	case "submit_send_file":
		//File-IDs wieder speichern, damit sie nach einem Fehler wieder vorhanden wären
		$file_ids = array();
		foreach(explode(",", $_POST["hid_files"]) as $f) {
			$file_ids[] = format_userinput($f, "alphanum", FALSE, 32);
		}

		//Eingaben überprüfen
		if(!$_POST["txt_absender"]) {
			$error = 2;
			continue;
		}
		$links = array();

		//External Recipients
		$rec = str_replace(";", ",", $_POST["txt_rec_extern"]);
    $recipients_ext = explode(",", $rec);

		//Internal Recipients
		foreach(explode(",", $_POST["sel_rec_intern"]) as $r) {
			$lid = format_userinput($r, "uint");
			$rec_logins[] = $lid;
			if($_POST["chk_email"]) {
				ko_get_login($lid, $l);
				ko_get_person_by_id($l["leute_id"], $p);
				$recipients_int[$lid] = $p["email"];
			}
		}

		foreach($file_ids as $save_id) {
			$share_ = ko_get_shares(" AND `id` = '$save_id' ");
			$share = $share_[0];

			//Interne Empfänger: Neue Datei erstellen
			foreach($rec_logins as $lid) {
      	$new_id = md5($share["filename"].microtime());
				system("cd $FILESHARE_FOLDER && ln $save_id $new_id");
				$inbox = ko_fileshare_get_inbox($lid);
				ko_fileshare_save_share($new_id, $lid, $share["filename"], $share["type"], $inbox["id"], $share["filesize"]);

				if($_POST["chk_email"]) {
					$r = trim($recipients_int[$lid]);
					if(!$r) continue;
					$rec_id = md5($r.$new_id);
					$links[$r] .= $share["filename"]." (".ko_nice_size($share["filesize"])."): ".$BASE_URL."fileshare/file.php?di=$new_id&ei=$rec_id\n";
					$recipients_email[] = $r;
					ko_fileshare_send_file($new_id, $r, $rec_id);
				}
			}
			//Links für Externe Empfänger und Interne, die per Email informiert werden sollen
			foreach($recipients_ext as $r) {
				$r = trim($r);
				$rec_id = md5($r.$save_id);
				$links[$r] .= $share["filename"]." (".ko_nice_size($share["filesize"])."): ".$BASE_URL."fileshare/file.php?di=$save_id&ei=$rec_id\n";
				$recipients_email[] = $r;
				ko_fileshare_send_file($save_id, $r, $rec_id);
			}
		}//foreach(file_ids as save_id)

		//Mails verschicken
		ko_get_person_by_id(ko_get_logged_in_id(), $p);
		if($p["email"]) $use_email = $p["email"];
		else $use_email = ko_get_setting("info_email");
		$recipients_email = array_unique($recipients_email);
		foreach($recipients_email as $r) {
			if(!$r) continue;
			$r = trim($r);
			$link = $links[$r];
			$replace = array("<LINK>" => $link,
					"<ABSENDER>" => $_POST["txt_absender"],
					"<ABSENDEREMAIL>" => $_POST["txt_absender_email"],
					"<TEXT>" => $_POST["txt_text"]);
			$message_txt = strtr(ko_get_setting("fileshare_mailtext"), $replace);
			$subject = getLL("fileshare_email_subject").": ".$_POST["txt_betreff"];

			ko_send_mail($use_email, $r, $subject, ko_emailtext($message_txt));
			//ko_send_email($r, $subject, ko_emailtext($message_txt), $headers);
		}//foreach(recipients as r)

		$info = 7;
		$_SESSION["show"] = "list_shares";
	break;




	//Folders
	case "show_folder":
		$_SESSION["folderid"] = format_userinput($_GET["id"], "uint");
		$_SESSION["show"] = "list_shares";
	break;


	case "submit_new_folder":
		$save_parent = format_userinput($_POST["sel_parent"], "uint");

		if(!ko_fileshare_check_permission($_SESSION["ses_userid"], $save_parent, "new_folder")) $error = 6;

		if(!$error) {
			$save_name = format_userinput($_POST["txt_name"], "js");
			$save_comment = format_userinput($_POST["txt_comment"], "text");
			$users = explode(",", format_userinput($_POST["sel_share_users"], "intlist"));
			foreach($users as $u) if($u) $save_share_users_a[] = "@".$u."@";
			$save_share_users = implode(",", $save_share_users_a);
			$save_share_rights = format_userinput($_POST["sel_share_rights"], "uint", FALSE, 1);

			$data = array("parent" => $save_parent,
										"user" => $_SESSION["ses_userid"],
										"name" => $save_name,
										"c_date" => "NOW()",
										"share_users" => $save_share_users,
										"comment" => $save_comment,
										"share_rights" => $save_share_rights
										);
			$q_id = db_insert_data("ko_fileshare_folders", $data);

			//Log-Eintrag
			$log_message = "Name: $save_name, Parent: $save_parent, Comment: $save_comment";
			ko_log("new_fileshare_folder", $log_message);
			
			$info = 4;
			$_SESSION["folderid"] = $q_id;
			$_SESSION["show"] = "list_shares";
		}//if(!error)
	break;


	case "submit_edit_folder":
		$save_id = format_userinput($_POST["id"], "uint");

		if(!ko_fileshare_check_permission($_SESSION["ses_userid"], $save_id, "edit_folder")) $error = 6;

		if(!$error) {
			$save_name = format_userinput($_POST["txt_name"], "text");
			$save_comment = format_userinput($_POST["txt_comment"], "text");
			$users = explode(",", format_userinput($_POST["sel_share_users"], "intlist"));
			foreach($users as $u) if($u) $save_share_users_a[] = "@".$u."@";
			$save_share_users = implode(",", $save_share_users_a);
			$save_share_rights = format_userinput($_POST["sel_share_rights"], "uint", FALSE, 1);

			$data = array("name" => $save_name,
										"share_users" => $save_share_users,
										"comment" => $save_comment,
										"share_rights" => $save_share_rights
										);
			db_update_data("ko_fileshare_folders", "WHERE `id` = '$save_id'", $data);

			//Log-Eintrag
			$log_message = "Name: $save_name ($save_id), Share-Users: $save_share_users, Share-Rights: $save_share_rights";
			ko_log("edit_fileshare_folder", $log_message);
			
			$info = 4;
			$_SESSION["show"] = "list_shares";
		}//if(!error)
	break;


	case "submit_del_folder":
		$save_id = format_userinput($_POST["id"], "uint");

		if(!ko_fileshare_check_permission($_SESSION["ses_userid"], $save_id, "del_folder")) $error = 6;
		if(db_get_count("ko_fileshare", "id", "AND `parent` = '$save_id'") > 0) $error = 7;
		if(db_get_count("ko_fileshare_folders", "id", "AND `parent` = '$save_id'") > 0) $error = 7;
		
		if(!$error) {
			ko_fileshare_get_folder($folder, $save_id);
			$log_message = "Name: ".$folder["name"].", Kommentar: ".$folder["comment"];

			db_delete_data("ko_fileshare_folders", "WHERE `id` = '$save_id'");

			ko_log("del_fileshare_folder", $log_message);
			$info = 5;
			$inbox = ko_fileshare_get_inbox($_SESSION["ses_userid"]);
			$_SESSION["folderid"] = $folder["parent"] ? $folder["parent"] : $inbox["id"];
			$_SESSION["show"] = "list_shares";
		}//if(!error)
	break;



	//Standard-Action --> Inbox anzeigen
	case "inbox":
		if(ENABLE_FILESHARE) {
			$inbox_id = ko_fileshare_get_inbox($_SESSION['ses_userid']);
			$_SESSION['folderid'] = $inbox_id['id'];
			$_SESSION['show'] = 'list_shares';
		} else if($access['fileshare']['MAX'] > 3 && WEBFOLDERS) {
			$_SESSION['show'] = 'list_webfolders';
			$_SESSION['show_start'] = 1;
		}
	break;
	


	// Webfolders
	case "new_webfolder":
		if($access['fileshare']['MAX'] < 4 || !WEBFOLDERS) continue;
		$_SESSION["show"] = "new_webfolder";
		$onload_code = "form_set_first_input();".$onload_code;
	break;

	case "submit_new_webfolder":
	case "submit_edit_webfolder":
		if($access['fileshare']['MAX'] < 4 || !WEBFOLDERS) continue;
		$name = format_userinput(urldecode($_POST["txt_name"]), "dir");
		$path = urldecode($_POST["hid_path"]);

		$rights_read = $_POST["sel_rights_read"];
		$rights_write = $_POST["sel_rights_write"];
		if(!$rights_write) $error = 9;
		if(trim($name) == "") $error = 10;

		if(!$error) {
			//prepare rights
			if($_SESSION["ses_userid"] == ko_get_root_id()) $z_where = "AND `login` != 'ko_guest'";
			else $z_where = "AND `login` != 'root' AND `login` != 'ko_guest'";
			$z_where .= " AND (`disabled` = '' OR `disabled` = '0')";
			ko_get_logins($logins, $z_where);


			$write = explode(",", $rights_write);
			if(in_array("_all", $write)) {
				$rights_write = "_all";
			} else {
				$rights_write = "";
				foreach($write as $id) {
					$username = $logins[$id]["login"];
					if(!$username) continue;
					$rights_write .= '"'.$username.'" ';
				}
			}
			$read = array_unique(array_merge($write, explode(",", $rights_read)));
			if(in_array("_all", $read)) {
				$rights_read = "_all";
			} else {
				$rights_read = "";
				foreach($read as $id) {
					$username = $logins[$id]["login"];
					if(!$username) continue;
					$rights_read .= '"'.$username.'" ';
				}
			}

			//create folders
			if($do_action == "submit_new_webfolder") {
				mkdir($WEBFOLDERS_BASE.$path.$name, 0750);
				mkdir($WEBFOLDERS_BASE_HTACCESS.$path.$name, 0750);
			}
			//or rename existing one if being edited
			else {
				$old_name = format_userinput($_POST["hid_origname"], "dir");
				$name = $WEBFOLDERS_BASE.$path.$name;
				if(substr($name, 0, strlen($WEBFOLDERS_BASE)) != $WEBFOLDERS_BASE) {
					//error
				} else {
					//rename folder and relink .htaccess
					$name = str_replace($WEBFOLDERS_BASE.$path, "", $name);
					if(file_exists($WEBFOLDERS_BASE.$path.$old_name) && !file_exists($WEBFOLDERS_BASE.$path.$name)) {
						//Remove old .htaccess if existing
						if(file_exists($WEBFOLDERS_BASE.$path.$old_name."/.htaccess")) unlink($WEBFOLDERS_BASE.$path.$old_name."/.htaccess");
						//Rename real folder
						rename($WEBFOLDERS_BASE.$path.$old_name, $WEBFOLDERS_BASE.$path.$name);
						//Rename .-ordner or create if not existing
						if(!file_exists($WEBFOLDERS_BASE_HTACCESS.$path.$old_name)) {
							mkdir($WEBFOLDERS_BASE_HTACCESS.$path.$name, 0750);
						} else {
							rename($WEBFOLDERS_BASE_HTACCESS.$path.$old_name, $WEBFOLDERS_BASE_HTACCESS.$path.$name);
						}
						//Create new link for .htaccess
						symlink($WEBFOLDERS_BASE_HTACCESS.$path.$name."/.htaccess", $WEBFOLDERS_BASE.$path.$name."/.htaccess");
					}
				}
			}
			//Create folder in .webfolder if not there yet
			if(!file_exists($WEBFOLDERS_BASE_HTACCESS.$path.$name)) {
				$check_path = $WEBFOLDERS_BASE_HTACCESS;
				$check_path = substr($check_path, -1) == "/" ? substr($check_path, 0, -1) : $check_path;
				foreach(explode("/", $path) as $path_element) {
					if(!$path_element) continue;
					$check_path = $check_path."/".$path_element;
					if(!file_exists($check_path)) {
						mkdir($check_path, 0750);
					}
				}
				mkdir($WEBFOLDERS_BASE_HTACCESS.$path.$name, 0750);
			}
			//apply rights in .htaccess
			if(file_exists($WEBFOLDERS_BASE_HTACCESS.$path.$name."/.htaccess")) {
				chmod($WEBFOLDERS_BASE_HTACCESS.$path.$name."/.htaccess", 0640);
			}
			$fp = fopen($WEBFOLDERS_BASE_HTACCESS.$path.$name."/.htaccess", "w");
			if($rights_read == "_all") {
				fputs($fp, "<Limit GET HEAD OPTIONS PROPFIND>\nRequire valid-user\n</Limit>\n");
			} else if($rights_read != "") {
				fputs($fp, "<Limit GET HEAD OPTIONS PROPFIND>\nRequire user ".$rights_read."\n</Limit>\n");
			}
			if($rights_write == "_all") {
				fputs($fp, "<LimitExcept GET HEAD OPTIONS PROPFIND>\nRequire valid-user\n</LimitExcept>\n");
			} else if($rights_write != "") {
				fputs($fp, "<LimitExcept GET HEAD OPTIONS PROPFIND>\nRequire user ".$rights_write."\n</LimitExcept>\n");
			}
			fclose($fp);
			//make .htaccess read-only, so it can not be overwritten
			chmod($WEBFOLDERS_BASE_HTACCESS.$path.$name."/.htaccess", 0440);

			//create symlink for .htaccess
			if(!file_exists($WEBFOLDERS_BASE.$path.$name."/.htaccess")) {
				symlink($WEBFOLDERS_BASE_HTACCESS.$path.$name."/.htaccess", $WEBFOLDERS_BASE.$path.$name."/.htaccess");
			}

			$info = 8;
			$_SESSION["show"] = "list_webfolders";
		}//if(!error)
	break;



	case "clear_webfolder_rights":
		if($access['fileshare']['MAX'] < 4 || !WEBFOLDERS) continue;
		
		$folder = urldecode($_GET["id"]);
		$folder = realpath($WEBFOLDERS_BASE.$folder);
		if(substr($folder, 0, strlen($WEBFOLDERS_BASE)) != $WEBFOLDERS_BASE) {
			//error
		} else {
			$folder = str_replace($WEBFOLDERS_BASE, "", $folder);
			unlink($WEBFOLDERS_BASE.$folder."/.htaccess");
			unlink($WEBFOLDERS_BASE_HTACCESS.$folder."/.htaccess");
		}
		$details_id = substr($folder, 0, strpos($folder, "/"))."/";
		$_SESSION["show"] = "webfolder_details";
	break;



	case "edit_webfolder":
		if($access['fileshare']['MAX'] < 4 || !WEBFOLDERS) continue;

		//check for correct passed folder name
		//$possible_folders = ko_fileshare_get_webfolders();
		//if($folder == "" || !in_array($folder, $possible_folders)) {

		$folder = urldecode($_POST["id"]);
		$folder = realpath($WEBFOLDERS_BASE.$folder);
		if(substr($folder, 0, strlen($WEBFOLDERS_BASE)) != $WEBFOLDERS_BASE) {
			//error
		} else {
			$folder = str_replace($WEBFOLDERS_BASE, "", $folder);
			$edit_id = $folder;
			$_SESSION["show"] = "edit_webfolder";
		}
		$onload_code = "form_set_first_input();".$onload_code;
	break;



	case "delete_webfolder":
		if($access['fileshare']['MAX'] < 4 || !WEBFOLDERS) continue;

		//check for correct passed folder name
		$possible_folders = ko_fileshare_get_webfolders();
		$folder = format_userinput($_POST["id"], "dir", FALSE, 0, array(), "/ ");
		if($folder == "" || !in_array($folder, $possible_folders)) {
			//error
		} else {
			//check for folder being empty
			ko_fileshare_get_folder_content($WEBFOLDERS_BASE.$folder, $folders, $files);
			if(sizeof($folders) == 0 && sizeof($files) == 0) {
				rmdir($WEBFOLDERS_BASE.$folder."/.DAV");
				unlink($WEBFOLDERS_BASE.$folder."/.htaccess");
				rmdir($WEBFOLDERS_BASE.$folder);
				unlink($WEBFOLDERS_BASE_HTACCESS.$folder."/.htaccess");
				rmdir($WEBFOLDERS_BASE_HTACCESS.$folder);
			}
		}
	break;




	case "list_webfolders":
		if($access['fileshare']['MAX'] < 4 || !WEBFOLDERS) continue;

		$_SESSION["show"] = "list_webfolders";
		$_SESSION["show_start"] = 1;
	break;



	case "webfolder_details":
		if($access['fileshare']['MAX'] < 4 || !WEBFOLDERS) continue;

		//check for correct passed folder name
		$possible_folders = ko_fileshare_get_webfolders();
		$folder = format_userinput(urldecode($_GET["id"]), "dir", FALSE, 0, array(), "/");
		if($folder == "" || !in_array($folder, $possible_folders)) {
			//error
		} else {
			$details_id = $folder;
			$_SESSION["show"] = "webfolder_details";
		}
	break;




	//Submenus
  case "move_sm_left":
  case "move_sm_right":
    ko_submenu_actions("fileshare", $do_action);
  break;


	//Default:
  default:
		if(!hook_action_handler($do_action))
      include($ko_path."inc/abuse.inc");
  break;

}//switch(action)


//HOOK: Plugins erlauben, die bestehenden Actions zu erweitern
hook_action_handler_add($do_action);



//***Defaults einlesen
if(!$_SESSION["sort"]) $_SESSION["sort"] = "c_date";
if(!$_SESSION["sort_order"]) $_SESSION["sort_order"] = "DESC";

if(!$_SESSION["show_start"]) $_SESSION["show_start"] = 1;
$_SESSION["show_limit"]= ko_get_userpref($_SESSION["ses_userid"], "show_limit_fileshare");
if(!$_SESSION["show_limit"]) $_SESSION["show_limit"] = ko_get_setting("show_limit_fileshare");
//folderid auf Eingang setzen
if(!$_SESSION["folderid"]) {
	$inbox = ko_fileshare_get_inbox($_SESSION["ses_userid"]);
	$_SESSION["folderid"] = $inbox["id"];
}

//Aktuellen Folder im Treeview anzeigen:
if(ENABLE_FILESHARE) {
	if(isset($_SESSION["folderid"]) && $_SESSION["folderid"] != 0) {
		$rootline = ko_fileshare_get_rootline($_SESSION["folderid"], $_SESSION["ses_userid"]);
		foreach($rootline as $f) {
			$onload_code .= ";Toggle('$f')";
		}
	}
}


//Smarty-Templates-Engine laden
require("$ko_path/inc/smarty.inc");

//Include submenus
ko_set_submenues();
?>
<!DOCTYPE html 
  PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php print $_SESSION["lang"]; ?>" lang="<?php print $_SESSION["lang"]; ?>">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
<title><?php print "$HTML_TITLE: ".getLL("module_".$ko_menu_akt); ?></title>
<?php
if(ENABLE_FILESHARE) print '<script language="javascript" type="text/javascript" src="'.$ko_path.'fileshare/inc/tree_view.js"></script>';

print ko_include_css();
print ko_include_js(array($ko_path.'inc/jquery/jquery.js', $ko_path.'inc/kOOL.js'));

include($ko_path.'inc/js-sessiontimeout.inc');
include($ko_path."fileshare/inc/js-fileshare.inc");
?>
</head>

<body onload="session_time_init();<?php if($onload_code) print $onload_code; ?>">

<?php
/*
 * Gibt bei erfolgreichem Login das Menü aus, sonst einfach die Loginfelder
 */
include($ko_path . "menu.php");


if($_SESSION["show"] == "new_share") {
	$sid = md5(uniqid(rand()));
	print '<form name="formular" method="post" enctype="multipart/form-data" action="/fileshare/cgi/upload.cgi?sid='.$sid.'">';
} else {
	print '<form name="formular" method="post" enctype="multipart/form-data" action="index.php">';
}
?>


<input type="hidden" name="action" id="action" value="" />
<input type="hidden" name="id" id="id" value="" />

<table width="100%">
<tr> 

<td class="main_left" name="main_left" id="main_left">
<?php
print ko_get_submenu_code("fileshare", "left");
?>
</td>


<!-- Hauptbereich -->
<td class="main" name="main_content" id="main_content">
<?php
if($info) {
	$info_txt = getLL("info_fileshare_".$info);
	print '<div class="infotxt">'.$info_txt.'</div><br />';
}

if($error) {
	$error_txt = getLL("error_fileshare_".$error);
	ko_error_log(getLL("module_fileshare"), $error, $error_txt, $do_action);
	print '<div class="errortxt">'.$error_txt.'</div><br />';
}

hook_show_case_pre($_SESSION["show"]);

switch($_SESSION["show"]) {
	case "list_shares":
		ko_fileshare_list_shares($_SESSION["ses_userid"], $_SESSION["folderid"]);
	break;

	case "new_share":
			ko_fileshare_formular();
	break;

	case "new_folder":
		ko_fileshare_formular_folder("neu");
	break;

	case "edit_folder":
		ko_fileshare_formular_folder("edit", $_POST["id"]);
	break;

	case "send_file":
		if(sizeof($file_ids) > 0) {
			ko_fileshare_formular_send($file_ids);
		}
	break;

	case "new_webfolder":
		ko_fileshare_formular_webfolder("new");
	break;

	case "edit_webfolder":
		ko_fileshare_formular_webfolder("edit", $edit_id);
	break;

	case "list_webfolders":
		ko_fileshare_list_webfolders();
	break;

	case "webfolder_details":
		ko_fileshare_webfolder_details($details_id);
	break;

	default:
		//HOOK: Plugins erlauben, neue Show-Cases zu definieren
    hook_show_case($_SESSION["show"]);
  break;
}//switch(show)

//HOOK: Plugins erlauben, die bestehenden Show-Cases zu erweitern
hook_show_case_add($_SESSION["show"]);

?>
</td>

<td class="main_right" name="main_right" id="main_right">

<?php
print ko_get_submenu_code("fileshare", "right");
?>

</td>
</tr>

<?php include($ko_path . "footer.php"); ?>

</table>
</form> <!-- //Hauptformular -->

</body>
</html>
