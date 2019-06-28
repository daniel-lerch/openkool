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

function ko_fileshare_list_shares($uid, $folderid=0, $output=TRUE) {
	global $ko_path, $smarty;
	global $FILESHARE_FOLDER;
	global $access;

	$code = "";
	//Berechtigungen checken
	$rights_view = ko_fileshare_check_permission($_SESSION["ses_userid"], $folderid, "view_file");
	if(!$rights_view) return FALSE;
	$rights_del = ko_fileshare_check_permission($_SESSION["ses_userid"], $folderid, "del_file");


  $rows = db_get_count("ko_fileshare", "id", "AND `parent` = '$folderid'");
	$z_limit = "LIMIT " . ($_SESSION["show_start"]-1) . ", " . $_SESSION["show_limit"];
	$shares = ko_get_shares(" AND `parent` = '$folderid'", $_SESSION["sort"]." ".$_SESSION["sort_order"], $z_limit);


	ko_fileshare_get_folder($af, $folderid);

	//Edit-Button
	if($access['fileshare']['MAX'] > 2) {
		$edit_code = '<input type="image" src="'.$ko_path.'images/button_edit.gif" alt="Edit" title="'.getLL("fileshare_edit_folder").'" onclick="'."set_action('edit_folder');set_hidden_value('id', '$folderid');".'" />';
	} else $edit_code = "";

	//Del-Button
	$empty = (db_get_count("ko_fileshare", "id", "AND `parent` = '$folderid'") == 0);
	$no_subfolder = (db_get_count("ko_fileshare_folders", "id", "AND `parent` = '$folderid'") == 0);
	if($access['fileshare']['MAX'] > 2 && $af["flag"] != "I" && $af["flag"] != "S" && $empty && $no_subfolder) {
		$del_code = '<input type="image" src="'.$ko_path.'images/button_delete.gif" alt="'.getLL("delete").'" title="'.getLL("fileshare_delete_folder").'" onclick="'."c=confirm('".getLL("fileshare_delete_folder_confirm")."');if(!c) return false;set_action('submit_del_folder');set_hidden_value('id', '$folderid');".'" />';
	} else $del_code = "";

	$share = ($af["share_users"] != "" || $af["flag"] == "S") ? "_shared" : "";
	$img_src = db_get_count("ko_fileshare", "id", "AND `parent` = '$folderid'" ) > 0 ? $ko_path."images/tv_inbox".$share.".gif" : $ko_path."images/tv_folder".$share.".gif";
	$path = "";
	foreach(array_reverse(ko_fileshare_get_rootline($folderid, $_SESSION["ses_userid"])) as $p) {
		$path .= "/".$p;
	}
	$title  = '<img src="'.$img_src.'" border="0" width="16" height="16" alt="Folder" />&nbsp;'.ko_html($path);
	$title .= "&nbsp;&nbsp;&nbsp;&nbsp;".$edit_code."&nbsp;".$del_code;

	//Listen-Titel
	$smarty->assign("tpl_list_title", $title);
	$smarty->assign("tpl_list_subtitle", ko_html($af["comment"]));


	//Sortierung auslesen und Links entsprechend setzen
  $smarty->assign("tpl_show_3cols", TRUE);
		  
  $show_cols = array(getLL("fileshare_listheader_file"), getLL("fileshare_listheader_size"), getLL("fileshare_listheader_type"), getLL("fileshare_listheader_creation_date"));
  $daten_sort_cols = array("filename", "filesize", "type", "c_date");
	foreach($show_cols as $c_i => $c) {
    $tpl_table_header[$c_i]["sort"] = $daten_sort_cols[$c_i];
    $tpl_table_header[$c_i]["name"] = $show_cols[$c_i];
  }
  $smarty->assign("tpl_table_header", $tpl_table_header);
  $smarty->assign("sort", array("show" => TRUE,
																"action" => "setsortfileshare",
																"akt" => $_SESSION["sort"],
																"akt_order" => $_SESSION["sort_order"])
	);
	$smarty->assign("module", "fileshare");
	$smarty->assign("sesid", session_id());


	//Statistik über Suchergebnisse und Anzeige
  $stats_end = ($_SESSION["show_limit"]+$_SESSION["show_start"]-1 > $rows) ? $rows : ($_SESSION["show_limit"]+$_SESSION["show_start"]-1);
  $smarty->assign('tpl_stats', $_SESSION["show_start"]." - ".$stats_end." ".getLL("list_oftotal")." ".$rows);


  //Links für Prev und Next-Page vorbereiten
	if($_SESSION["show_start"]> 1) {
			$smarty->assign("tpl_prevlink_link", "javascript:sendReq('../fileshare/inc/ajax.php', 'action,set_start,sesid', 'setstart,".(($_SESSION["show_start"]-$_SESSION["show_limit"] < 1) ? 1 : ($_SESSION["show_start"]-$_SESSION["show_limit"])).",".session_id()."', do_element);");
  } else {
    $smarty->assign('tpl_prevlink_link', '');
  }
  if(($_SESSION["show_start"]+$_SESSION["show_limit"]-1) < $rows) {
		$smarty->assign("tpl_nextlink_link", "javascript:sendReq('../fileshare/inc/ajax.php', 'action,set_start,sesid', 'setstart,".($_SESSION["show_limit"]+$_SESSION["show_start"]).",".session_id()."', do_element);");
  } else {
    $smarty->assign('tpl_nextlink_link', '');
  }
	$smarty->assign('hide_listlimiticons', TRUE);


	foreach($shares as $e_i => $e) {
		//Checkbox
		$tpl_list_data[$e_i]["show_checkbox"] = TRUE;

		//Send-Button
		$tpl_list_data[$e_i]["show_send_button"] = TRUE;
		$tpl_list_data[$e_i]["alt_edit"] = getLL("fileshare_send_file");
		$tpl_list_data[$e_i]["onclick_edit"] = "javascript:set_action('send_file');set_hidden_value('id', '".$e["id"]."');this.submit";

		//Delete-Button
		if($rights_del) {
			$tpl_list_data[$e_i]["show_delete_button"] = TRUE;
			$tpl_list_data[$e_i]["alt_delete"] = getLL("fileshare_delete_file");
			$tpl_list_data[$e_i]["onclick_delete"] = "javascript:c = confirm('".getLL("fileshare_delete_file_confirm")."');if(!c) return false;set_action('delete_share');set_hidden_value('id', '".$e["id"]."');";
		} else {
			$tpl_list_data[$e_i]["show_delete_button"] = FALSE;
		}


		//Index
		$tpl_list_data[$e_i]["id"] = $e["id"];

		//Datei (inkl. Download-Link)
		if(file_exists($FILESHARE_FOLDER.$e["id"])) $file_ok = TRUE;
		else $file_ok = FALSE;

		if($file_ok) $tpl_list_data[$e_i][1]  = '<a href="file.php?di='.$e["id"].'&amp;ei='.$e["id"].'" target="_blank">';
		else $tpl_list_data[$e_i][1] = '! ';
		$tpl_list_data[$e_i][1] .= ko_html($e["filename"]);
		if($file_ok) $tpl_list_data[$e_i][1] .= '</a>';
		else $tpl_list_data[$e_i][1] .= ' !';

		//Filesize
		$size = file_exists($FILESHARE_FOLDER.$e["id"]) ? ko_nice_size(filesize($FILESHARE_FOLDER.$e["id"])) : "";
		if($size) $tpl_list_data[$e_i][2] = $size;

		//Typ
		$tpl_list_data[$e_i][3] = $e["type"];

		//Erstellungszeit
		$tpl_list_data[$e_i][4] = substr(sqldatetime2datum($e["c_date"]), 0, -3);

	}//foreach(shares as e)


	//Footer
	if($rows > 0) {
		if($rights_del || $access['fileshare']['MAX'] > 2) {
			$button_code = '<select name="sel_target">'.ko_fileshare_get_folder_select($_SESSION["ses_userid"], "new").'</select>';
			if($rights_del) $button_code .= '&nbsp;&nbsp;<input type="submit" value="'.getLL("fileshare_list_footer_move_button1").'" onclick="set_hidden_value('."'id', document.formular.sel_target.options[document.formular.sel_target.selectedIndex].value);".'set_action(\'move_shares\');" />';
			if($access['fileshare']['MAX'] > 1) $button_code .= '&nbsp;&nbsp;<input type="submit" value="'.getLL("fileshare_list_footer_move_button2").'" onclick="set_hidden_value('."'id', document.formular.sel_target.options[document.formular.sel_target.selectedIndex].value);".'set_action(\'copy_shares\');" />';
			$list_footer[] = array("label" => getLL("fileshare_list_footer_move_label"), "button" => $button_code);
			$smarty->assign("show_list_footer", TRUE);
		}
		$button_code = '<input type="submit" value="'.getLL("fileshare_list_footer_send_button1").'" onclick="set_action(\'send_file\');" />';
		if($rights_del) $button_code .= '&nbsp;&nbsp;<input type="submit" value="'.getLL("fileshare_list_footer_send_button2").'" onclick="c=confirm('."'".getLL("fileshare_list_footer_send_button2_confirm")."'".');if(!c) return false;set_action(\'delete_shares\');" />';
		$list_footer[] = array("label" => getLL("fileshare_list_footer_send_label"), "button" => $button_code);
	}

	$smarty->assign("list_footer", $list_footer);

	$smarty->assign('tpl_list_cols', array(1, 2, 3, 4));
  $smarty->assign('tpl_list_data', $tpl_list_data);
	if($output) {
	  $smarty->display('ko_list.tpl');
	} else {
	  $code .= $smarty->fetch('ko_list.tpl');
	}

		

	//Share-Settings-Footer
	if($af["share_users"] && $af["user"] == $_SESSION["ses_userid"]) {
		$share_rights_text = array(1 => getLL("fileshare_share_rights_1"), 2 => getLL("fileshare_share_rights_2"), 3 => getLL("fileshare_share_rights_3"));
		$share_code  = '('.$share_rights_text[$af["share_rights"]].': ';
		foreach(explode(",", $af["share_users"]) as $u) {
			$uid = substr($u, 1, -1);
			ko_get_login($uid, $login);
			ko_get_person_by_id($login["leute_id"], $p);
			$users .= $p["vorname"]." ".$p["nachname"].", ";
		}
		$share_code .= substr($users, 0, -2).')';

		$code .= '<br /><br /><hr />';
		$code .= '<span style="font-size:0.75em;font-style:italic;"><b>'.getLL("fileshare_share_settings").' '.$af["name"].':</b><br />';
		$code .= $share_code;
		$code .= '</span>';
	}

	print $code;
}//ko_fileshare_list_shares()






function ko_fileshare_formular_folder($mode, $id=0) {
	global $ko_path, $smarty, $access;

	if($access['fileshare']['MAX'] < 3) return FALSE;

	//Daten auslesen bei Edit
	if($mode == "edit") {
		$id = format_userinput($id, "uint");
		ko_fileshare_get_folder($afolder, $id);
		$txt_name = $afolder["name"];
		$txt_comment = $afolder["comment"];
		foreach(explode(",", $afolder["share_users"]) as $user) {
			if(substr($user, 0, 1) != "@" || substr($user, -1, 1) != "@") continue;
			$login_avalues[] = substr($user, 1, -1);
			ko_get_login(substr($user, 1, -1), $al);
			ko_get_person_by_id($al["leute_id"], $ap);
			$login_adescs[] = $ap["vorname"]." ".$ap["nachname"];
		}
		$share_rights = $afolder["share_rights"];
	} else {
		$share_rights = 1;
		$sel = ko_fileshare_get_folder_select($_SESSION["ses_userid"], "mod", $parent_values_, $parent_descs_, FALSE);
		$parent_values = array_merge(array(0), $parent_values_);
		$parent_descs = array_merge(array("- ".getLL("fileshare_top")." -"), $parent_descs_);
	}

	ko_get_logins($logins, "AND `login` != 'root'");
	foreach($logins as $login) {
		if(!ko_module_installed("fileshare", $login["id"])) continue;

		//use loginname, if no person assigned to this login
		if($login["leute_id"] <= 0) {
			$login_values[] = $login["id"];
			$login_descs[] = $login["login"];
		}
		//else use the real name
		else {
			ko_get_person_by_id($login["leute_id"], $person);
			$login_values[] = $login["id"];
			$login_descs[] = $person["vorname"]." ".$person["nachname"];
		}
	}//foreach(logins as login)

	$rowcounter = 0;
	$gc = 0;
	if($mode == "neu") {
		$group[$gc]["row"][$rowcounter++]["inputs"][0] = array("desc" => getLL("form_fileshare_subfolder"),
																 "type" => "select",
																 "name" => "sel_parent",
																 "values" => $parent_values,
																 "descs" => $parent_descs,
																 "value" => $_SESSION["folderid"],
																 "params" => 'size="0"'
																 );
	}
	$group[$gc]["row"][$rowcounter++]["inputs"][0] = array("desc" => getLL("form_fileshare_name"),
															 "type" => "text",
															 "name" => "txt_name",
															 "value" => $txt_name,
															 "params" => 'size="60" maxlength="100"'
															 );
	$group[$gc]["row"][$rowcounter++]["inputs"][0] = array("desc" => getLL("form_fileshare_comment"),
															 "type" => "text",
															 "name" => "txt_comment",
															 "value" => $txt_comment,
															 "params" => 'size="60"'
															 );
	$group[$gc]["row"][$rowcounter++]["inputs"][0] = array("type" => "   ");
	$group[$gc]["row"][$rowcounter++]["inputs"][0] = array("desc" => getLL("form_fileshare_share_users"),
															 "type" => "doubleselect",
															 "js_func_add" => "double_select_add",
															 "name" => "sel_share_users",
															 "values" => $login_values,
															 "descs" => $login_descs,
															 "avalues" => $login_avalues,
															 "avalue" => implode(",", $login_avalues),
															 "adescs" => $login_adescs,
															 "params" => 'size="7"'
															 );
	$group[$gc]["row"][$rowcounter++]["inputs"][0] = array("desc" => getLL("form_fileshare_share_rights"),
															 "type" => "select",
															 "name" => "sel_share_rights",
															 "values" => array("", 1, 2, 3),
															 "descs" => array("", getLL("form_fileshare_share_rights_1"), getLL("form_fileshare_share_rights_2"), getLL("form_fileshare_share_rights_3")),
															 "value" => $share_rights,
															 "params" => 'size="0"'
															 );

	$smarty->assign("tpl_titel", ($mode=="neu")?getLL("fileshare_new_folder"):getLL("fileshare_edit_folder"));
	$smarty->assign("tpl_groups", $group);
	$smarty->assign("tpl_id", $id);
	$smarty->assign("tpl_action", $mode == "neu" ? "submit_new_folder" : "submit_edit_folder");
	$smarty->assign("tpl_cancel", "show_folder");
	$smarty->assign("tpl_submit_value", getLL("save"));
	$smarty->display("ko_formular.tpl");

	return TRUE;
}//ko_fileshare_fomular_folder()







function ko_fileshare_formular() {
	global $ko_path, $smarty, $access, $sid;

	if($access['fileshare']['MAX'] < 2) return FALSE;

	//Target-Select
	ko_fileshare_get_folder_select($_SESSION["ses_userid"], "new", $target_values, $target_descs);

	$rowcounter = 0;
	$gc = 0;
	$group[$gc]["row"][$rowcounter++]["inputs"][0] = array("desc" => getLL("form_fileshare_target"),
															 "type" => "select",
															 "name" => "sel_target",
															 "values" => $target_values,
															 "descs" => $target_descs,
															 "value" => $_SESSION["folderid"],
															 "params" => 'size="0"'
															 );
	$group[$gc]["row"][$rowcounter++]["inputs"][0] = array("desc" => getLL("form_fileshare_file1"),
															 "type" => "file",
															 "name" => "file",
															 "params" => 'size="60" id="file[0]"'
															 );
	$group[$gc]["row"][$rowcounter++]["inputs"][0] = array("desc" => getLL("form_fileshare_file2"),
															 "type" => "file",
															 "name" => "file2",
															 "params" => 'size="60" id="file[1]"'
															 );
	$group[$gc]["row"][$rowcounter++]["inputs"][0] = array("desc" => getLL("form_fileshare_file3"),
															 "type" => "file",
															 "name" => "file3",
															 "params" => 'size="60" id="file[2]"'
															 );

	$special_submit  = '<input type="hidden" name="sessionid" value="'.$sid.'" />';
	$special_submit .= '<input type="button" value="'.getLL("save").'" onclick="postIt();" />';


	$smarty->assign("tpl_titel", getLL("fileshare_add_share_title"));
	$smarty->assign("tpl_groups", $group);
	$smarty->assign("tpl_special_submit", $special_submit);
	$smarty->assign("tpl_cancel", "show_folder");
	$smarty->display("ko_formular.tpl");

	return TRUE;
}//ko_fileshare_formular()





function ko_fileshare_formular_send($files) {
	global $ko_path, $smarty, $access;

	if($access['fileshare']['MAX'] < 2) return FALSE;
	$label_sendfiles = "";

	//File-IDs übergeben
	foreach($files as $file) {
		$file_ = format_userinput($file, "alphanum", FALSE, 32);
		if(strlen($file_) == 32) $tpl_files[] = $file;
		//Label erstellen, mit allen zu sendenen Datein
		$as_ = ko_get_shares(" AND `id` = '$file'"); $as = $as_[0];
		$label_sendfiles .= "<div>".$as["filename"]." (".ko_nice_size($as["filesize"]).")</div>";
	}
	$hidden_inputs[] = array("name" => "hid_files", "value" => implode(",", $tpl_files));

	//Absender-Information
	ko_get_person_by_id(ko_get_logged_in_id(), $ap);
	$txt_absender = $ap["vorname"]." ".$ap["nachname"];
	$txt_absender_email = $ap["email"];

	//My-List Import vorbereiten
	if(ko_module_installed("leute") && isset($_SESSION["my_list"]) && sizeof($_SESSION["my_list"]) > 0) {
		foreach($_SESSION["my_list"] as $m) {
			ko_get_person_by_id($m, $p);
			if($p["email"]) {
				$imported_recipients .= $p["email"].",";
			}
		}//foreach(my_list as m)
		$imported_recipients = substr($imported_recipients, 0, -1);
	}

	//Logins für interne Empfänger
	ko_get_logins($logins, "AND `login` != 'root'");
	foreach($logins as $login) {
		if(!ko_module_installed("fileshare", $login["id"])) continue;

		if($login["leute_id"] <= 0) {
			$login_values[] = $login["id"];
			$login_descs[] = $login["login"];
		} else {
			ko_get_person_by_id($login["leute_id"], $person);
			$login_values[] = $login["id"];
			$login_descs[] = $person["vorname"]." ".$person["nachname"];
		}
	}//foreach(logins as login)


	$rowcounter = 0;
	$gc = 0;
	$group[$gc]["row"][$rowcounter++]["inputs"][0] = array("desc" => getLL("form_fileshare_send_files"),
															 "type" => "label",
															 "value" => $label_sendfiles,
															 "colspan" => 'colspan="2"'
															 );
	$group[$gc]["row"][$rowcounter++]["inputs"][0] = array("type" => "   ");
	$group[$gc]["row"][$rowcounter]["inputs"][0] = array("desc" => getLL("form_fileshare_send_files_sender"),
															 "type" => "text",
															 "name" => "txt_absender",
															 "value" => $txt_absender,
															 "params" => 'size="60" maxlength="100"'
															 );
	$group[$gc]["row"][$rowcounter++]["inputs"][1] = array("desc" => getLL("form_fileshare_send_files_sender_email"),
															 "type" => "text",
															 "name" => "txt_absender_email",
															 "value" => $txt_absender_email,
															 "params" => 'size="60"'
															 );
	$group[$gc]["row"][$rowcounter]["inputs"][0] = array("desc" => getLL("form_fileshare_send_files_rec_intern"),
															 "type" => "doubleselect",
															 "js_func_add" => "double_select_add",
															 "name" => "sel_rec_intern",
															 "values" => $login_values,
															 "descs" => $login_descs,
															 "params" => 'size="7"'
															 );
	$group[$gc]["row"][$rowcounter++]["inputs"][1] = array("desc" => getLL("form_fileshare_send_files_rec_extern"),
															 "type" => "text_mylist",
															 "name" => "txt_rec_extern",
															 "params" => 'size="60"',
															 "mylist" => $imported_recipients
															 );
	$group[$gc]["row"][$rowcounter++]["inputs"][0] = array("desc" => getLL("form_fileshare_send_files_email"),
															 "type" => "checkbox",
															 "name" => "chk_email",
															 "value" => 1,
															 "params" => 'checked="checked"'
															 );
	$group[$gc]["row"][$rowcounter++]["inputs"][0] = array("type" => "   ");
	$group[$gc]["row"][$rowcounter++]["inputs"][0] = array("desc" => getLL("form_fileshare_send_files_subject"),
															 "type" => "text",
															 "name" => "txt_betreff",
															 "params" => 'size="60"',
															 "colspan" => 'colspan="2"'
															 );
	$group[$gc]["row"][$rowcounter++]["inputs"][0] = array("desc" => getLL("form_fileshare_send_files_text"),
															 "type" => "textarea",
															 "name" => "txt_text",
															 "params" => 'cols="60" rows="8"',
															 "colspan" => 'colspan="2"'
															 );

	$smarty->assign("tpl_titel", getLL("fileshare_send_file_title"));
	$smarty->assign("tpl_groups", $group);
	$smarty->assign("tpl_hidden_inputs", $hidden_inputs);
	$smarty->assign("tpl_submit_value", getLL("fileshare_send_file"));
	$smarty->assign("tpl_action", "submit_send_file");
	$smarty->assign("tpl_cancel", "show_folder");
	$smarty->display("ko_formular.tpl");

	return TRUE;
}//ko_fileshare_formular_send()




function ko_fileshare_list_webfolders($output=TRUE) {
	global $smarty, $ko_path;
	global $access, $BASE_URL, $BASE_PATH, $WEBFOLDERS_BASE;

	if($access['fileshare']['MAX'] < 4) return FALSE;

	$folders = ko_fileshare_get_webfolders();
	$rows = sizeof($folders);

	//Statistik über Suchergebnisse und Anzeige
  $stats_end = ($_SESSION["show_limit"]+$_SESSION["show_start"]-1) > $rows ? $rows : ($_SESSION["show_limit"]+$_SESSION["show_start"]-1);
	$smarty->assign("tpl_stats", $_SESSION["show_start"]." - ".$stats_end." ".getLL("list_oftotal")." ".$rows);

	if($_SESSION["show_start"] > 1) {
		$smarty->assign("tpl_prevlink_link", "javascript:sendReq('../fileshare/inc/ajax.php', 'action,set_start,sesid', 'setstartwebfolders,".(($_SESSION["show_start"]-$_SESSION["show_limit"] < 1) ? 1 : ($_SESSION["show_start"]-$_SESSION["show_limit"])).",".session_id()."', do_element);");
  } else {
    $smarty->assign('tpl_prevlink_link', '');
  }
  if(($_SESSION["show_start"]+$_SESSION["show_limit"]-1) < $rows) {
		$smarty->assign("tpl_nextlink_link", "javascript:sendReq('../fileshare/inc/ajax.php', 'action,set_start,sesid', 'setstartwebfolders,".($_SESSION["show_limit"]+$_SESSION["show_start"]).",".session_id()."', do_element);");
  } else {
    $smarty->assign('tpl_nextlink_link', '');
  }
	$smarty->assign('hide_listlimiticons', TRUE);

	//Header-Informationen
	$smarty->assign("tpl_show_3cols", TRUE);

	$show_cols = array(getLL("fileshare_webfolders_webfolder"), getLL("fileshare_webfolders_contents"), getLL("fileshare_webfolders_users_read"), getLL("fileshare_webfolders_users_write"));
  foreach($show_cols as $c_i => $c) {
    $tpl_table_header[$c_i]["name"] = $show_cols[$c_i];
  }
  $smarty->assign("tpl_table_header", $tpl_table_header);
  $smarty->assign("sort", array("show" => FALSE));


	//Liste einfüllen
	$counter = 0;
	foreach($folders as $l_i => $l) {
		$counter++;
		if($counter < $_SESSION["show_start"] || $counter > ($_SESSION["show_start"] + $_SESSION["show_limit"] - 1)) continue;

		$folders = $files = NULL;
		ko_fileshare_get_folder_content($WEBFOLDERS_BASE.$l, $folders, $files);

		//Checkbox
		$tpl_list_data[$l_i]["show_checkbox"] = TRUE;
							
		//Edit-Button
		$tpl_list_data[$l_i]["show_edit_button"] = TRUE;
		$tpl_list_data[$l_i]["alt_edit"] = getLL("fileshare_webfolders_edit");
		$tpl_list_data[$l_i]["onclick_edit"] = "javascript:set_action('edit_webfolder');set_hidden_value('id', '$l');this.submit";
									
		//Delete-Button
		if(sizeof($folders) == 0 && sizeof($files) == 0) {
			$tpl_list_data[$l_i]["show_delete_button"] = TRUE;
			$tpl_list_data[$l_i]["alt_delete"] = getLL("fileshare_webfolders_delete");
			$tpl_list_data[$l_i]["onclick_delete"] = "javascript:c = confirm('".getLL("fileshare_webfolders_delete_confirm")."');if(!c) return false;set_action('delete_webfolder');set_hidden_value('id', '$l');";
		} else {
			$tpl_list_data[$l_i]["show_delete_button"] = FALSE;
		}

		//Index
		$tpl_list_data[$l_i]["id"] = $l_i;

		//Name
		if(sizeof($folders) > 0) {
			$link = $ko_path."fileshare/index.php?action=webfolder_details&amp;id=".urlencode($l);
			$tpl_list_data[$l_i][1] = '<a href="'.$link.'">'.ko_html(str_replace("/", "", $l)).'</a>';
		} else {
			$tpl_list_data[$l_i][1] = ko_html(str_replace("/", "", $l));
		}

		//Contents
		$size = ko_nice_size(dirsize(str_replace($BASE_PATH, "", $WEBFOLDERS_BASE).$l));
		$size = $size < 0 ? 0 : $size;
		$tpl_list_data[$l_i][2] = sizeof($folders)." ".getLL("fileshare_folders").", ".sizeof($files)." ".getLL("fileshare_files")." ($size)";

		//Read rights
		list($users_read, $users_write) = ko_fileshare_webfolders_rights($l);
		if($users_read == "_all") $users_read = array(getLL("all"));
		$tpl_list_data[$l_i][3] = sizeof($users_read) > 10 ? sizeof($users_read)." ".getLL("users") : implode(", ", $users_read);

		//Write rights
		if($users_write == "_all") $users_write = array(getLL("all"));
		$tpl_list_data[$l_i][4] = sizeof($users_write) > 10 ? sizeof($users_write)." ".getLL("users") : implode(", ", $users_write);
	}//foreach(folders)

	$list_footer[] = array("label" => getLL("fileshare_webfolders_footer").$BASE_URL.str_replace($BASE_PATH, "", $WEBFOLDERS_BASE).strtoupper(getLL("name")));
	$smarty->assign("show_list_footer", TRUE);
	$smarty->assign("list_footer", $list_footer);

	$smarty->assign("tpl_list_title", getLL("fileshare_list_title"));
	$smarty->assign('tpl_list_cols', array(1, 2, 3, 4));
  $smarty->assign('tpl_list_data', $tpl_list_data);
	if($output) {
		$smarty->display('ko_list.tpl');
	} else {
		print $smarty->fetch('ko_list.tpl');
	}
}//ko_fileshare_list_webfolders()





function ko_fileshare_formular_webfolder($mode="new", $id="") {
	global $ko_path, $smarty, $access;
	global $WEBFOLDERS_BASE;

	if($access['fileshare']['MAX'] < 4) return FALSE;

	$logins = array();
	if($mode == "new") {
		$txt_name = "";
		$login_read_avalues = "";
		$login_read_adescs = "";
		$login_write_avalues = "";
		$login_write_adescs = "";
	}
	else if($mode == "edit") {
		if($id == "") return FALSE;

		//$txt_name = str_replace("/", "", $id);
		$path_elements = explode("/", $id);
		$txt_name = array_pop($path_elements);
		$path = "";
		foreach($path_elements as $e) {
			if(!$e) continue;
			$path .= $e."/";
		}
		//store orig name in hidden input
		$hidden_inputs = array(array("name" => "hid_origname", "value" => $txt_name), array("name" => "hid_path", "value" => urlencode($path)));
		$smarty->assign("tpl_hidden_inputs", $hidden_inputs);

		list($users_read, $users_write) = ko_fileshare_webfolders_rights($id);
		if($users_read == "_all") {
			$login_read_avalues = array("_all");
			$login_read_adescs = array(getLL("all"));
		} else {
			foreach($users_read as $username) {
				$login = db_select_data("ko_admin", "WHERE `login` = '$username'", "id,login,leute_id", "", "", TRUE);
				$login_read_avalues[] = $login["id"];
				if($login["leute_id"] <= 0) {
					$login_read_adescs[] = $login["login"];
				} else {
					ko_get_person_by_id($login["leute_id"], $person);
					if($person["vorname"] != "" || $person["nachname"] != "") {
						$login_read_adescs[] = $person["vorname"]." ".$person["nachname"];
					} else {
						$login_read_adescs[] = $login["login"];
					}
				}
			}
		}
		if($users_write == "_all") {
			$login_write_avalues = array("_all");
			$login_write_adescs = array(getLL("all"));
		} else {
			foreach($users_write as $username) {
				$login = db_select_data("ko_admin", "WHERE `login` = '$username'", "id,login,leute_id", "", "", TRUE);
				$login_write_avalues[] = $login["id"];
				if($login["leute_id"] <= 0) {
					$login_write_adescs[] = $login["login"];
				} else {
					ko_get_person_by_id($login["leute_id"], $person);
					if($person["vorname"] != "" || $person["nachname"] != "") {
						$login_write_adescs[] = $person["vorname"]." ".$person["nachname"];
					} else {
						$login_write_adescs[] = $login["login"];
					}
				}
			}
		}
	} else {
		return FALSE;
	}

	//get logins for user rights
	$login_values = array("_all");
	$login_descs = array(getLL("all"));

	if($_SESSION["ses_userid"] == ko_get_root_id()) $z_where = "AND `login` != 'ko_guest'";
	else $z_where = "AND `login` != 'root' AND `login` != 'ko_guest'";
	$z_where .= " AND (`disabled` = '' OR `disabled` = '0')";
	ko_get_logins($logins, $z_where);

	foreach($logins as $login) {
		//use loginname, if no person assigned to this login
		if($login["leute_id"] <= 0) {
			$login_values[] = $login["id"];
			$login_descs[] = $login["login"];
		}
		//else use the real name
		else {
			ko_get_person_by_id($login["leute_id"], $person);
			$login_values[] = $login["id"];
			if($person["vorname"] != "" || $person["nachname"] != "") {
				$login_descs[] = $person["vorname"]." ".$person["nachname"];
			} else {
				$login_descs[] = $login["login"];
			}
		}
	}//foreach(logins as login)


	//Parent-Select
	$folders = array_merge(array(""), ko_fileshare_get_webfolders());

	$rowcounter = 0;
	$gc = 0;
	$group[$gc]["row"][$rowcounter++]["inputs"][0] = array("desc" => getLL("form_fileshare_webfolder_name"),
															 "type" => "text",
															 "name" => "txt_name",
															 "value" => $txt_name,
															 "params" => 'size="60" maxlength="100"',
															 "colspan" => 'colspan="2"',
															 );
	$group[$gc]["row"][$rowcounter]["inputs"][0] = array("desc" => getLL("form_fileshare_webfolder_rights_read"),
															 "type" => "doubleselect",
															 "js_func_add" => "double_select_add",
															 "name" => "sel_rights_read",
															 "values" => $login_values,
															 "descs" => $login_descs,
															 "avalues" => $login_read_avalues,
															 "avalue" => implode(",", $login_read_avalues),
															 "adescs" => $login_read_adescs,
															 "params" => 'size="7"'
															 );
	$group[$gc]["row"][$rowcounter++]["inputs"][1] = array("desc" => getLL("form_fileshare_webfolder_rights_write"),
															 "type" => "doubleselect",
															 "js_func_add" => "double_select_add",
															 "name" => "sel_rights_write",
															 "values" => $login_values,
															 "descs" => $login_descs,
															 "avalues" => $login_write_avalues,
															 "avalue" => implode(",", $login_write_avalues),
															 "adescs" => $login_write_adescs,
															 "params" => 'size="7"'
															 );

	$smarty->assign("tpl_titel", getLL("fileshare_add_webfolder_title"));
	$smarty->assign("tpl_groups", $group);
	$smarty->assign("tpl_submit_value", getLL("save"));
	$smarty->assign("tpl_action", $mode == "new" ? "submit_new_webfolder" : "submit_edit_webfolder");
	$smarty->assign("tpl_cancel", "list_webfolders");
	$smarty->display("ko_formular.tpl");

	return TRUE;
}//ko_fileshare_formular_webfolder()



function ko_fileshare_webfolder_details($id) {
	global $ko_path, $WEBFOLDERS_BASE;


	ko_fileshare_get_folder_content($WEBFOLDERS_BASE.$id, $folders, $files, "details");
	$folders = array_merge(array($WEBFOLDERS_BASE.$id), $folders);
	sort($files, SORT_LOCALE_STRING);

	print '<h2>'.$id.'</h2>';

	foreach($folders as $folder) {
		$name = str_replace($WEBFOLDERS_BASE, "", $folder);
		$name = substr($name, strlen($id));
		if(!$name) continue;

		print '<div>';
		print '<input type="image" src="'.$ko_path.'images/button_edit.gif" alt="edit" onclick="javascript:set_action(\'edit_webfolder\');set_hidden_value(\'id\', \''.urlencode($id.$name).'\');this.submit" />';
		print "&nbsp;<b>".ko_html(utf8_decode($name))."</b>";

		//Read and Write rights
		list($users_read, $users_write) = ko_fileshare_webfolders_rights($id.$name);
		if(sizeof($users_write) > 0 || sizeof($users_read) > 0) {
			//Link to purge rights
			print '&nbsp;-&nbsp;<a href="?action=clear_webfolder_rights&amp;id='.urlencode($id.$name).'">Rechte entfernen</a>';
			//Display rights as text
			print '<div style="font-style:italic; padding-left: 20px;">';
			if($users_read == "_all") $users_read = array(getLL("all"));
			print getLL("fileshare_webfolders_users_read").": ";
			print sizeof($users_read) > 10 ? sizeof($users_read)." ".getLL("users") : implode(", ", $users_read);
			print "<br />".getLL("fileshare_webfolders_users_write").": ";
			if($users_write == "_all") $users_write = array(getLL("all"));
			print sizeof($users_write) > 10 ? sizeof($users_write)." ".getLL("users") : implode(", ", $users_write);
			print '</div>';
		}

		print '</div>';
		/*
		print "<ul>";
		reset($files);
		foreach($files as $file) {
			if(strstr($file["name"], $WEBFOLDERS_BASE.$name) !== FALSE && strstr(str_replace($WEBFOLDERS_BASE.$name, "", $file["name"]), "/") === FALSE) {
				print "<li>".str_replace($WEBFOLDERS_BASE.$name, "", $file["name"])."</li>";
			}
		}
		print "</ul>";
		*/
	}
}//ko_fileshare_webfolder_details()



function rec_webfolders($root_dir, &$folders) {
	$dir = opendir($root_dir);
	while ($f = readdir($dir)) {
		if ($f <> '.' && $f <> '..' && $f <> '.DAV') {
			if (is_dir($root_dir.$f)) {
				$folders[] = realpath($root_dir.$f).'/';
				//rec_webfolders($root_dir.$f.'/', $folders);
			}
		}
	}
	closedir($dir);
}//rec_webfolders()


function ko_fileshare_get_webfolders() {
	global $WEBFOLDERS_BASE;

	rec_webfolders($WEBFOLDERS_BASE, $folders);
	$folders = array_unique($folders);
	sort($folders);

	$return = array();
	foreach($folders as $folder) {
		$return[] = str_replace($WEBFOLDERS_BASE, "", $folder);
	}
	return $return;
}//ko_fileshare_get_webfolders()


function ko_fileshare_get_folder_content($root_dir, &$folders, &$files, $mode="") {
	clearstatcache();

	$dir = opendir($root_dir);
	while ($f = readdir($dir)) {
		if ($f <> '.' && $f <> '..' && $f <> '.DAV' && $f <> '.htaccess') {
			if (is_dir($root_dir.$f)) {
				$folders[] = realpath($root_dir.$f).'/';
				ko_fileshare_get_folder_content($root_dir.$f.'/', $folders, $files, $mode);
			} else {
				if($mode == "details") {
					$files[] = array_merge(array("name" => $root_dir.$f), stat($root_dir.$f));
				} else {
					$files[] = $root_dir.$f;
				}
			}
		}
	}
	closedir($dir);
}//ko_fileshare_get_folder_content()


function ko_fileshare_webfolders_rights($folder) {
	global $WEBFOLDERS_BASE_HTACCESS;

	if(substr($folder, -1) != "/") $folder = $folder."/";
	$contents = file($WEBFOLDERS_BASE_HTACCESS.$folder.".htaccess");
	if(sizeof($contents) == 0) return FALSE;

	$do_read = $do_write = FALSE;
	foreach($contents as $line) {
		if(strstr($line, "<LimitExcept") !== FALSE) {
			$do_write = TRUE;
		} else if(strstr($line, "<Limit") !== FALSE) {
			$do_read = TRUE;
		} else {
			if($do_read) {
				if(strstr($line, "valid-user") !== FALSE) $read = "_all";
				else if(strstr($line, "Require user") !== FALSE) {
					$users = explode(' "', $line);
					unset($users[0]);
					foreach($users as $user) {
						$read[] = trim(str_replace('"', '', $user));
					}
				}
			} else if($do_write) {
				if(strstr($line, "valid-user") !== FALSE) $write = "_all";
				else if(strstr($line, "Require user") !== FALSE) {
					$users = explode(' "', $line);
					unset($users[0]);
					foreach($users as $user) {
						$write[] = trim(str_replace('"', '', $user));
					}
				}
			}
			$do_read = $do_write = FALSE;
		}
	}

	return array($read, $write);
}//ko_fileshare_webfolders_rights()
?>
