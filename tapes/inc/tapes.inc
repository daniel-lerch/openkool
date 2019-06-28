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

function ko_tapes_list($output=TRUE) {
	global $smarty, $ko_path, $DATETIME;
	global $access;
	global $my_list_footer;

	apply_tapes_filter($z_where, $z_limit);

	$rows = db_get_count("ko_tapes", "id", $z_where);
	ko_get_tapes($tapes, $z_where, $z_limit);

	//Statistik über Suchergebnisse und Anzeige
  $stats_end = ($_SESSION["show_limit"]+$_SESSION["show_start"]-1 > $rows) ? $rows : ($_SESSION["show_limit"]+$_SESSION["show_start"]-1);
  $smarty->assign('tpl_stats', $_SESSION["show_start"]." - ".$stats_end." ".getLL("list_oftotal")." ".$rows);

	//Links für Prev und Next-Page vorbereiten
  if($_SESSION["show_start"] > 1) {
		$smarty->assign("tpl_prevlink_link", "javascript:sendReq('../tapes/inc/ajax.php', 'action,set_start,sesid', 'setstart,".(($_SESSION["show_start"]-$_SESSION["show_limit"] < 1) ? 1 : ($_SESSION["show_start"]-$_SESSION["show_limit"])).",".session_id()."', do_element);");
  } else {
    $smarty->assign('tpl_prevlink_link', '');
  }
  if(($_SESSION["show_start"]+$_SESSION["show_limit"]-1) < $rows) {
		$smarty->assign("tpl_nextlink_link", "javascript:sendReq('../tapes/inc/ajax.php', 'action,set_start,sesid', 'setstart,".($_SESSION["show_limit"]+$_SESSION["show_start"]).",".session_id()."', do_element);");
  } else {
    $smarty->assign('tpl_nextlink_link', '');
  }
	$smarty->assign('limitM', $_SESSION['show_limit'] >= 100 ? $_SESSION['show_limit']-50 : max(10, $_SESSION['show_limit']-10));
	$smarty->assign('limitP', $_SESSION['show_limit'] >= 50 ? $_SESSION['show_limit']+50 : $_SESSION['show_limit']+10);


	//Header-Informationen
	$smarty->assign("tpl_show_3cols", TRUE);

	$show_cols = array(getLL("kota_listview_ko_tapes_date"), getLL("kota_listview_ko_tapes_group_id"), getLL("kota_listview_ko_tapes_title"), getLL("kota_listview_ko_tapes_preacher"), getLL("kota_listview_ko_tapes_serie_id"), getLL("kota_listview_ko_tapes_item_number"));
  $sort_cols = array("date", "group_id", "title", "preacher", "serie_id", "item_number");
  foreach($show_cols as $c_i => $c) {
    $tpl_table_header[$c_i]["sort"] = $sort_cols[$c_i];
    $tpl_table_header[$c_i]["name"] = $show_cols[$c_i];
  }
  $smarty->assign("tpl_table_header", $tpl_table_header);
  $smarty->assign("sort", array("show" => TRUE,
																"action" => "setsorttapes",
																"akt" => $_SESSION["sort_tapes"],
																"akt_order" => $_SESSION["sort_tapes_order"])
	);
	$smarty->assign("module", "tapes");
	$smarty->assign("sesid", session_id());


	//Liste füllen
	foreach($tapes as $l_i => $l) {
		$group = $l["group_id"];

		//Checkbox
		$tpl_list_data[$l_i]["show_checkbox"] = FALSE;

		//Text-Field
		if($access['tapes']['ALL'] > 1 || $access['tapes'][$group] > 1) {
			$tpl_list_data[$l_i]["show_numberfield"] = TRUE;
			$tpl_list_data[$l_i]["numberfield_size"] = 2;
		}

		$tpl_list_data[$l_i]["rowclick_code"]  = "javascript:r = detectMouseButton(event); if(r == 1) addOne($l_i); else if(r == 2 || r == 3) subOne($l_i); ";
		//$tpl_list_data[$l_i]["rowclick_code"]  = "javascript:addOne($l_i);";

		//Edit-Button
		if($access['tapes']['ALL'] > 2 || $access['tapes'][$group] > 2) {
			$tpl_list_data[$l_i]["show_edit_button"] = TRUE;
			$tpl_list_data[$l_i]["alt_edit"] = getLL("tapes_edit_tape");
			$tpl_list_data[$l_i]["onclick_edit"] = "javascript:set_action('edit_tape');set_hidden_value('id', '$l_i');this.submit";
		}

		//Delete-Button
		if($access['tapes']['ALL'] > 2 || $access['tapes'][$group] > 2) {
			$tpl_list_data[$l_i]["show_delete_button"] = TRUE;
			$tpl_list_data[$l_i]["alt_delete"] = getLL("tapes_del_tape");
			$tpl_list_data[$l_i]["onclick_delete"] = "javascript:c = confirm('".getLL("tapes_del_tape_confirm")."');if(!c) return;set_action('delete_tape');set_hidden_value('id', '$l_i');";
		}

		//Index
		$tpl_list_data[$l_i]["id"] = $l_i;

		//Datum
		$tpl_list_data[$l_i][1] = ko_html(strftime($DATETIME["ddmy"], strtotime($l["date"])));

		//Gruppe
		$tpl_list_data[$l_i][2] = ko_html($l["group_name"]);

		//Titel
		$tpl_list_data[$l_i][3] = ko_html($l["title"]);

		//Prediger
		$tpl_list_data[$l_i][4] = ko_html($l["preacher"]);

		//Serie
		$tpl_list_data[$l_i][5] = ko_html($l["serie_name"]);

		//Artikelnummer
		$tpl_list_data[$l_i][6] = ko_html($l["item_number"]);
	}//foreach(login)

	$smarty->assign('tpl_list_cols', array(1, 2, 3, 4, 5, 6));
  $smarty->assign('tpl_list_data', $tpl_list_data);

	//Footer
	if(is_array($my_list_footer) && sizeof($my_list_footer) > 0) {
		$list_footer = $my_list_footer;
	}
	if($access['tapes']['MAX'] > 1) {
		$list_footer[] = array("label" => getLL("tapes_list_footer_print_label"),
													 "button" => '<input type="submit" value="'.getLL("tapes_list_footer_print_button").'" onclick="set_action(\'add_to_printqueue\');">');
		$smarty->assign("show_list_footer", TRUE);
		$smarty->assign("list_footer", $list_footer);
	}

	$smarty->assign("tpl_list_title", getLL("tapes_list_title"));
	if($output) {
		$smarty->display('ko_list.tpl');
	} else {
		print $smarty->fetch('ko_list.tpl');
	}
}//ko_tapes_list()





function ko_tapes_list_tapegroups($output=TRUE) {
	global $smarty, $ko_path;
	global $access;

	$z_limit = "LIMIT " . ((int)$_SESSION["show_start"]-1) . ", " . $_SESSION["show_limit"];
	$rows = db_get_count("ko_tapes_groups", "id", "");
	ko_get_tapegroups($tapegroups, "", $z_limit);

	//Statistik über Suchergebnisse und Anzeige
  $stats_end = ($_SESSION["show_limit"]+$_SESSION["show_start"]-1 > $rows) ? $rows : ($_SESSION["show_limit"]+$_SESSION["show_start"]-1);
  $smarty->assign('tpl_stats', $_SESSION["show_start"]." - ".$stats_end." ".getLL("list_oftotal")." ".$rows);

	//Links für Prev und Next-Page vorbereiten
  if($_SESSION["show_start"] > 1) {
		$smarty->assign("tpl_prevlink_link", "javascript:sendReq('../tapes/inc/ajax.php', 'action,set_start,sesid', 'setstart,".(($_SESSION["show_start"]-$_SESSION["show_limit"] < 1) ? 1 : ($_SESSION["show_start"]-$_SESSION["show_limit"])).",".session_id()."', do_element);");
  } else {
    $smarty->assign('tpl_prevlink_link', '');
  }
  if(($_SESSION["show_start"]+$_SESSION["show_limit"]-1) < $rows) {
		$smarty->assign("tpl_nextlink_link", "javascript:sendReq('../tapes/inc/ajax.php', 'action,set_start,sesid', 'setstart,".($_SESSION["show_limit"]+$_SESSION["show_start"]).",".session_id()."', do_element);");
  } else {
    $smarty->assign('tpl_nextlink_link', '');
  }
	$smarty->assign('limitM', $_SESSION['show_limit'] >= 100 ? $_SESSION['show_limit']-50 : max(10, $_SESSION['show_limit']-10));
	$smarty->assign('limitP', $_SESSION['show_limit'] >= 50 ? $_SESSION['show_limit']+50 : $_SESSION['show_limit']+10);


	//Header-Informationen
	$smarty->assign("tpl_show_3cols", TRUE);

	$show_cols = array(getLL("tapes_name"), getLL("tapes_printname"));
  foreach($show_cols as $c_i => $c) {
    $tpl_table_header[$c_i]["name"] = $show_cols[$c_i];
  }
  $smarty->assign("tpl_table_header", $tpl_table_header);
	$smarty->assign("sort", array("show" => FALSE));

	$smarty->assign('module', 'tapes');
	$smarty->assign('sesid', session_id());


	//Logins in Liste einfüllen
	foreach($tapegroups as $l_i => $l) {
		$group = $l_i;

		//Checkbox
		$tpl_list_data[$l_i]["show_checkbox"] = TRUE;

		//Edit-Button
		if($access['tapes']['ALL'] > 3 || $access['tapes'][$group] > 3) {
			$tpl_list_data[$l_i]["show_edit_button"] = TRUE;
			$tpl_list_data[$l_i]["alt_edit"] = getLL("tapes_edit_tapegroup");
			$tpl_list_data[$l_i]["onclick_edit"] = "javascript:set_action('edit_tapegroup');set_hidden_value('id', '$l_i');this.submit";
		}

		//Delete-Button
		if($access['tapes']['ALL'] > 3 || $access['tapes'][$group] > 3) {
			$tpl_list_data[$l_i]["show_delete_button"] = TRUE;
			$tpl_list_data[$l_i]["alt_delete"] = getLL("tapes_del_tapegroup");
			$tpl_list_data[$l_i]["onclick_delete"] = "javascript:c = confirm('".getLL("tapes_del_tapegroup_confirm")."');if(!c) return;set_action('delete_tapegroup');set_hidden_value('id', '$l_i');";
		}

		//Index
		$tpl_list_data[$l_i]["id"] = $l_i;

		//Name
		$tpl_list_data[$l_i][1] = '<a href="?action=submit_group_filter&amp;set_filter='.$l_i.'">'.ko_html($l["name"]).'</a>';

		//Druckname
		$tpl_list_data[$l_i][2] = ko_html($l["printname"]);
	}//foreach(tapegroups)

	$smarty->assign('tpl_list_cols', array(1, 2));
  $smarty->assign('tpl_list_data', $tpl_list_data);
	$smarty->assign("tpl_list_title", getLL("tapes_groups_list_title"));
	if($output) {
		$smarty->display('ko_list.tpl');
	} else {
		print $smarty->fetch('ko_list.tpl');
	}
}//ko_tapes_list_tapegroups()







function ko_tapes_list_series($output=TRUE) {
	global $smarty, $ko_path;
	global $access;

	$z_limit = "LIMIT " . ((int)$_SESSION["show_start"]-1) . ", " . $_SESSION["show_limit"];
	$rows = db_get_count("ko_tapes_series", "id", "");
	ko_get_tapeseries($tapeseries, "", $z_limit);

	//Statistik über Suchergebnisse und Anzeige
  $stats_end = ($_SESSION["show_limit"]+$_SESSION["show_start"]-1 > $rows) ? $rows : ($_SESSION["show_limit"]+$_SESSION["show_start"]-1);
  $smarty->assign('tpl_stats', $_SESSION["show_start"]." - ".$stats_end." ".getLL("list_oftotal")." ".$rows);

	//Links für Prev und Next-Page vorbereiten
  if($_SESSION["show_start"] > 1) {
		$smarty->assign("tpl_prevlink_link", "javascript:sendReq('../tapes/inc/ajax.php', 'action,set_start,sesid', 'setstart,".(($_SESSION["show_start"]-$_SESSION["show_limit"] < 1) ? 1 : ($_SESSION["show_start"]-$_SESSION["show_limit"])).",".session_id()."', do_element);");
  } else {
    $smarty->assign('tpl_prevlink_link', '');
  }
  if(($_SESSION["show_start"]+$_SESSION["show_limit"]-1) < $rows) {
		$smarty->assign("tpl_nextlink_link", "javascript:sendReq('../tapes/inc/ajax.php', 'action,set_start,sesid', 'setstart,".($_SESSION["show_limit"]+$_SESSION["show_start"]).",".session_id()."', do_element);");
  } else {
    $smarty->assign('tpl_nextlink_link', '');
  }
	$smarty->assign('limitM', $_SESSION['show_limit'] >= 100 ? $_SESSION['show_limit']-50 : max(10, $_SESSION['show_limit']-10));
	$smarty->assign('limitP', $_SESSION['show_limit'] >= 50 ? $_SESSION['show_limit']+50 : $_SESSION['show_limit']+10);


	//Header-Informationen
	$smarty->assign("tpl_show_3cols", TRUE);

	$show_cols = array(getLL("tapes_name"), getLL("tapes_printname"));
  foreach($show_cols as $c_i => $c) {
    $tpl_table_header[$c_i]["name"] = $show_cols[$c_i];
  }
  $smarty->assign("tpl_table_header", $tpl_table_header);
	$smarty->assign("sort", array("show" => FALSE));

	$smarty->assign('module', 'tapes');
	$smarty->assign('sesid', session_id());


	//Logins in Liste einfüllen
	foreach($tapeseries as $l_i => $l) {
		//Checkbox
		$tpl_list_data[$l_i]["show_checkbox"] = TRUE;

		//Edit-Button
		if($access['tapes']['MAX'] > 2) {
			$tpl_list_data[$l_i]["show_edit_button"] = TRUE;
			$tpl_list_data[$l_i]["alt_edit"] = getLL("tapes_edit_serie");
			$tpl_list_data[$l_i]["onclick_edit"] = "javascript:set_action('edit_serie');set_hidden_value('id', '$l_i');this.submit";
		}

		//Delete-Button
		if($access['tapes']['MAX'] > 2) {
			$tpl_list_data[$l_i]["show_delete_button"] = TRUE;
			$tpl_list_data[$l_i]["alt_delete"] = getLL("tapes_del_serie");
			$tpl_list_data[$l_i]["onclick_delete"] = "javascript:c = confirm('".getLL("tapes_del_serie_confirm")."');if(!c) return;set_action('delete_serie');set_hidden_value('id', '$l_i');";
		}

		//Index
		$tpl_list_data[$l_i]["id"] = $l_i;

		//Name
		$tpl_list_data[$l_i][1] = '<a href="?action=submit_serie_filter&amp;set_filter='.$l_i.'">'.ko_html($l["name"]).'</a>';

		//Druckname
		$tpl_list_data[$l_i][2] = ko_html($l["printname"]);
	}//foreach(tapeseries)

	$smarty->assign('tpl_list_cols', array(1, 2));
  $smarty->assign('tpl_list_data', $tpl_list_data);
	$smarty->assign("tpl_list_title", getLL("tapes_series_list_title"));
	if($output) {
		$smarty->display('ko_list.tpl');
	} else {
		print $smarty->fetch('ko_list.tpl');
	}
}//ko_tapes_list_series()






function ko_tapes_list_printlayouts($output=TRUE) {
	global $smarty, $ko_path;
	global $access;

	if($access['tapes']['MAX'] < 4) return FALSE;

	$rows = db_get_count("ko_tapes_printlayout", "id");
	$layouts = ko_get_tape_printlayout();

	//Statistik über Suchergebnisse und Anzeige
  $smarty->assign('tpl_stats', "1 - ".$rows." ".getLL("list_oftotal")." ".$rows);

	//Links für Prev und Next-Page vorbereiten
  $smarty->assign('tpl_prevlink_link', '');
  $smarty->assign('tpl_nextlink_link', '');


	//Header-Informationen
	$smarty->assign("tpl_show_3cols", TRUE);

	$show_cols = array("Name");
  $sort_cols = array("name");
  $tpl_table_header[0]["name"] = $show_cols[0];
  $smarty->assign("tpl_table_header", $tpl_table_header);
	$smarty->assign("sort", array("show" => FALSE));
	$smarty->assign('hide_listlimiticons', TRUE);


	//Liste füllen
	foreach($layouts as $l_i => $l) {
		//Checkbox
		$tpl_list_data[$l_i]["show_checkbox"] = FALSE;

		//Edit-Button
		$tpl_list_data[$l_i]["show_edit_button"] = TRUE;
		$tpl_list_data[$l_i]["alt_edit"] = getLL("tapes_edit_layout");
		$tpl_list_data[$l_i]["onclick_edit"] = "javascript:set_action('edit_printlayout');set_hidden_value('id', '$l_i');this.submit";

		//Delete-Button
		$tpl_list_data[$l_i]["show_delete_button"] = TRUE;
		$tpl_list_data[$l_i]["alt_delete"] = getLL("tapes_del_layout");
		$tpl_list_data[$l_i]["onclick_delete"] = "javascript:c = confirm('".getLL("tapes_del_layout_confirm")."');if(!c) return;set_action('delete_printlayout');set_hidden_value('id', '$l_i');";

		//Index
		$tpl_list_data[$l_i]["id"] = $l_i;

		//Name
		$tpl_list_data[$l_i][1] = ko_html($l["name"]);
	}//foreach(layouts)

	$list_footer[] = array("label" => getLL("tapes_list_footer_add_printlayout_label"),
												 "button" => '<input type="submit" value="'.getLL("tapes_list_footer_add_printlayout_button").'" onclick="set_action(\'new_printlayout\');">');
	$smarty->assign("show_list_footer", TRUE);
	$smarty->assign("list_footer", $list_footer);

	$smarty->assign('tpl_list_cols', array(1));
  $smarty->assign('tpl_list_data', $tpl_list_data);
	$smarty->assign("tpl_list_title", getLL("tapes_print_list_title"));
	if($output) {
		$smarty->display('ko_list.tpl');
	} else {
		print $smarty->fetch('ko_list.tpl');
	}
}//ko_tapes_list()







function ko_tapes_formular_tape($mode, $id=0) {
	global $ko_path, $smarty;
  global $access, $KOTA;

  if($mode == "edit" && $id) {
    if($access['tapes']['MAX'] < 3) return;
  } else if($mode == "neu") {
    if($access['tapes']['MAX'] < 3) return;

		$minus = ko_get_setting("tapes_new_minus");
		$plus = ko_get_setting("tapes_new_plus");
		$query = "SELECT ko_event.*, ko_eventgruppen.name as eventgruppe FROM `ko_event`, `ko_eventgruppen` WHERE ko_event.eventgruppen_id = ko_eventgruppen.id AND ko_eventgruppen.tapes = '1' AND TO_DAYS(CURDATE())-TO_DAYS(ko_event.startdatum) < '$minus' AND TO_DAYS(ko_event.startdatum)-TO_DAYS(CURDATE()) < '$plus' ORDER BY ko_event.startdatum DESC";
		$result = mysql_query($query);


		$counter = 0;
		ko_get_tapegroups($groups);
		ko_get_tapeseries($series);
		ko_get_preachers($preachers);

		while($row = mysql_fetch_assoc($result)) {
			$list_data[$counter]["id"] = $row["id"];

			//Gruppe erraten
			$gruppe_id = $gruppe = "";
			$gc = 0;
			foreach($groups as $group_id => $group) {
				if($access['tapes']['ALL'] < 3 && $access['tapes'][$group['id']] < 3) continue;
				if(strtolower($group["name"]) == strtolower($row["eventgruppe"])) {
					$gruppe = $group["name"];
					$gruppe_id = $group_id;
					$group_js_index = $gc;
				}
				$gc++;
			}
			//Serie erraten, falls gewünscht
			$serie_id = $serie = "";
			if(ko_get_setting("tapes_guess_series") == 1) {
				$sc = 1;
				if($row["kommentar"]) {
					foreach($series as $s) {
						if(strstr(strtolower(format_userinput($row["kommentar"], "alphanum")), strtolower(format_userinput($s["name"], "alphanum")))) {
							$serie = $s["name"];
							$serie_id = $sc;
						}
						$sc++;
					}
				}
			}//if(ko_get_setting(tapes_guess_series))

			//JS erstellen, das das Formular automatisch ausfüllt
			$map_js = array("\n" => ' ', "\r" => '', "\t" => ' ', "'" => "\'");
			$list_data[$counter]["rowclick_code"]  = "a = document.getElementsByName('koi[ko_tapes][title][0]'); a[0].value = '".strtr($row["kommentar"], $map_js)."';";
			$list_data[$counter]["rowclick_code"] .= "b = document.getElementsByName('koi[ko_tapes][date][0]'); b[0].value = '".sql2datum($row["startdatum"])."';";
			$list_data[$counter]["rowclick_code"] .= "c = document.getElementsByName('koi[ko_tapes][group_id][0]'); c[0].value = '".$group_js_index."';";
			if($serie_id) $list_data[$counter]["rowclick_code"] .= "d = document.getElementsByName('koi[ko_tapes][serie_id_PLUS][0]'); d.value = ''; e = document.getElementsByName('koi[ko_tapes][serie_id][0]'); e[0].selectedIndex = '$serie_id';";
			else $list_data[$counter]["rowclick_code"] .= "f = document.getElementsByName('koi[ko_tapes][serie_id_PLUS][0]'); f.value = ''; g = document.getElementsByName('koi[ko_tapes][serie_id][0]'); g[0].selectedIndex = '';";
			$list_data[$counter]["rowclick_code"] .= "j = document.getElementsByName('koi[ko_tapes][preacher_PLUS][0]'); j[0].value = '$ps'; k = document.getElementsByName('koi[ko_tapes][preacher][0]'); k[0].selectedIndex = '';";

			//Datum und Titel anzeigen
			$list_data[$counter][1] = sql2datum($row["startdatum"]);
			$list_data[$counter][2] = $row["kommentar"];

			//Erratene Gruppe und Serie anzeigen
			$list_data[$counter][3] = $gruppe;
			$list_data[$counter][4] = $serie;
			$counter++;
		}//while(row = fetchRow)
	
	  $table_header[0]["name"] = getLL("kota_listview_ko_tapes_date");
	  $table_header[1]["name"] = getLL("kota_listview_ko_tapes_title");
		$table_header[2]["name"] = getLL("kota_listview_ko_tapes_group_id");
		$table_header[3]["name"] = getLL("kota_listview_ko_tapes_serie_id");
	  $smarty->assign("tpl_table_header", $table_header);
	  $smarty->assign("show_sort", FALSE);
		$smarty->assign('tpl_list_cols', array(1, 2, 3, 4));
	  $smarty->assign('tpl_list_data', $list_data);
	  $smarty->assign('tpl_hide_header', TRUE);
		$smarty->display('ko_list.tpl');

  } else return;


	$form_data["title"] =  $mode == "neu" ? getLL("form_tapes_title_new") : getLL("form_tapes_title_edit");
	$form_data["submit_value"] = getLL("save");
	$form_data["cancel"] = "list_tapes";
	$form_data["action"] = $mode == "neu" ? "submit_new_tape" : "submit_edit_tape";

	ko_multiedit_formular("ko_tapes", "", $id, "", $form_data);
}//ko_tapes_formular_tape()





function ko_tapes_formular_tapegroup($mode, $id = "") {
  global $access, $KOTA;

  if($access['tapes']['MAX'] < 4) return;

	$form_data["title"] =  $mode == "neu" ? getLL("form_tapegroups_title_new") : getLL("form_tapegroups_title_edit");
	$form_data["submit_value"] = getLL("save");
	$form_data["cancel"] = "list_tapegroups";
	$form_data["action"] = $mode == "neu" ? "submit_new_tapegroup" : "submit_edit_tapegroup";

	ko_multiedit_formular("ko_tapes_groups", "", $id, "", $form_data);
}//ko_tapes_formular_tapegroup()




function ko_tapes_formular_serie($mode, $id = "") {
  global $access, $KOTA;

  if($access['tapes']['MAX'] < 3) return;

	$form_data["title"] =  $mode == "neu" ? getLL("form_tapeseries_title_new") : getLL("form_tapeseries_title_edit");
	$form_data["submit_value"] = getLL("save");
	$form_data["cancel"] = "list_series";
	$form_data["action"] = $mode == "neu" ? "submit_new_serie" : "submit_edit_serie";

	ko_multiedit_formular("ko_tapes_series", "", $id, "", $form_data);
}//ko_tapes_formular_tapegroup()




function ko_tapes_formular_printlayout($mode, $id = "") {
	global $ko_path, $smarty;
  global $access;

  if($access['tapes']['MAX'] < 4) return;

  if($mode == "edit" && $id) {

		$layout = ko_get_tape_printlayout($id);
		$txt_name = $layout["name"] ? ko_html($layout["name"]) : ko_html($_POST["txt_name"]);
		$frm = $layout["layout"];
		$txt_page_width = $layout["page_width"];
		$txt_page_height = $layout["page_height"];
		$txt_items = $layout["items"];
		$txt_rootx = implode(",", $layout["rootx"]);
		$txt_rooty = implode(",", $layout["rooty"]);

  } else if($mode == "neu") {
  } else return;

	$fonts = ko_get_pdf_fonts();
	foreach($fonts as $font) {
		$fonts_values[] = $font["id"];
		$fonts_descs[]  = $font["name"];
	}
	$fontsize_select_values = array(7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25);
	$align_select_values = array(getLL("tapes_printlayout_left"), getLL("tapes_printlayout_center"), getLL("tapes_printlayout_right"));

	$frm_elements = array("title" => getLL("kota_ko_tapes_title"), "subtitle" => getLL("kota_ko_tapes_subtitle"), "date" => getLL("kota_ko_tapes_date"), "group" => getLL("kota_ko_tapes_group_id"), "serie" => getLL("kota_ko_tapes_serie_id"), "preacher" => getLL("kota_ko_tapes_preacher"), "item_number" => getLL("kota_ko_tapes_item_number"), "price" => getLL("kota_ko_tapes_price"));

	$gc = $rowcounter = 0;
	$group[$gc]["row"][$rowcounter++]["inputs"][0] = array("desc" => getLL("tapes_settings_label_layout_name"),
																 "type" => "text",
																 "name" => "txt_name",
																 "value" => $txt_name,
																 "params" => 'size="60" maxlength="255"'
																 );
	$group[++$gc] = array("titel" => getLL('tapes_settings_label_layout'));
	foreach($frm_elements as $key => $value) {
		$group[$gc]["row"][$rowcounter]["inputs"][0] = array("desc" => "$value: " . getLL("tapes_settings_label_pos_x"),
																	 "type" => "text",
																	 "name" => "frm[$key][x]",
																	 "value" => $frm[$key]["x"],
																	 "params" => 'size="3" maxlength="3"'
																	 );
		$group[$gc]["row"][$rowcounter++]["inputs"][1] = array("desc" => "$value: " . getLL("tapes_settings_label_pos_y"),
																	 "type" => "text",
																	 "name" => "frm[$key][y]",
																	 "value" => $frm[$key]["y"],
																	 "params" => 'size="3" maxlength="3"'
																	 );
		$group[$gc]["row"][$rowcounter]["inputs"][0] = array("desc" => "$value: " . getLL("tapes_settings_label_font"),
																	 "type" => "select",
																	 "name" => "frm[$key][font]",
																	 "values" => $fonts_values,
																	 "descs" => $fonts_descs,
																	 "value" => $frm[$key]["font"],
																	 "params" => 'size="0"'
																	 );
		$group[$gc]["row"][$rowcounter++]["inputs"][1] = array("desc" => "$value: " . getLL("tapes_settings_label_font_size"),
																	 "type" => "select",
																	 "name" => "frm[$key][fontsize]",
																	 "values" => $fontsize_select_values,
																	 "descs" => $fontsize_select_values,
																	 "value" => $frm[$key]["fontsize"],
																	 "params" => 'size="0"'
																	 );
		$group[$gc]["row"][$rowcounter]["inputs"][0] = array("desc" => "$value: " . getLL("tapes_settings_label_justification"),
																	 "type" => "select",
																	 "name" => "frm[$key][align]",
																	 "values" => $align_select_values,
																	 "descs" => $align_select_values,
																	 "value" => $frm[$key]["align"],
																	 "params" => 'size="0"'
																	 );
		$group[$gc]["row"][$rowcounter++]["inputs"][1] = array("desc" => "$value: " . getLL("tapes_settings_label_hide"),
																	 "type" => "checkbox",
																	 "name" => "frm[$key][do]",
																	 "value" => 1,
																	 "params" => ($frm[$key]["do"] || $mode == "neu") ? '' : 'checked="checked"'
																	 );
		$group[$gc]["row"][$rowcounter++]["inputs"][0] = array("type" => "   ");
	}


	$group[++$gc] = array("titel" => getLL("tapes_settings_label_sheet_layout"));
	$group[$gc]["row"][$rowcounter]["inputs"][0] = array("desc" => getLL("tapes_settings_label_sheet_width"),
																 "type" => "text",
																 "name" => "txt_page_width",
																 "value" => $txt_page_width,
																 "params" => 'size="5" maxlength="10"'
																 );
	$group[$gc]["row"][$rowcounter++]["inputs"][1] = array("desc" => getLL("tapes_settings_label_sheet_height"),
																 "type" => "text",
																 "name" => "txt_page_height",
																 "value" => $txt_page_height,
																 "params" => 'size="5" maxlength="10"'
																 );
	$group[$gc]["row"][$rowcounter++]["inputs"][0] = array("desc" => getLL("tapes_settings_label_sheet_labels"),
																 "type" => "text",
																 "name" => "txt_items",
																 "value" => $txt_items,
																 "params" => 'size="3" maxlength="3"'
																 );
	$group[$gc]["row"][$rowcounter++]["inputs"][0] = array("desc" => getLL("tapes_settings_label_sheet_xpos"),
																 "type" => "text",
																 "name" => "txt_rootx",
																 "value" => $txt_rootx,
																 "params" => 'size="120" maxlength="500"',
																 "colspan" => 'colspan="2"',
																 );
	$group[$gc]["row"][$rowcounter++]["inputs"][0] = array("desc" => getLL("tapes_settings_label_sheet_ypos"),
																 "type" => "text",
																 "name" => "txt_rooty",
																 "value" => $txt_rooty,
																 "params" => 'size="120" maxlength="500"',
																 "colspan" => 'colspan="2"',
																 );


	$smarty->assign("tpl_titel", ( ($mode == "neu") ? getLL("tapes_new_printlayout") : getLL("tapes_edit_printlayout")) );
	$smarty->assign("tpl_submit_value", getLL("save"));
	$smarty->assign("tpl_id", $id);
	$smarty->assign("tpl_action", ( ($mode == "neu") ? "submit_new_printlayout" : "submit_edit_printlayout") );
	$smarty->assign("tpl_cancel", "list_printlayouts");
	$smarty->assign("tpl_groups", $group);
																																							  
	$smarty->display('ko_formular.tpl');
}//ko_tapes_formular_printlayout()




function ko_tapes_settings() {
	global $ko_path, $smarty;
	global $access;

  if($access['tapes']['MAX'] < 4) return;

	$smarty->assign("tpl_titel", getLL("tapes_settings_title"));
	    
  $parts[0]["titel"] = getLL("tapes_settings_new_title"); 
  $setting[0]["desc"] = getLL("tapes_settings_new_minus");
  $setting[0]["type"] = "text";
  $setting[0]["name"] = "txt_new_minus";
  $setting[0]["params"] = '';
  $setting[0]["value"] = ko_html(ko_get_setting("tapes_new_minus"));

  $setting[1]["desc"] = getLL("tapes_settings_new_plus");
  $setting[1]["type"] = "text";
  $setting[1]["name"] = "txt_new_plus";
  $setting[1]["params"] = '';
  $setting[1]["value"] = ko_html(ko_get_setting("tapes_new_plus"));

  $setting[2]["desc"] = getLL("tapes_settings_new_guess_serie");
  $setting[2]["type"] = "radio";
  $setting[2]["name"] = "rd_guess_series";
  $setting[2]["value"] = array(1, 0);
  $setting[2]["output"] = array(getLL("yes"), getLL("no"));
  $setting[2]["checked"] = ko_html(ko_get_setting("tapes_guess_series"));

	$parts[0]["settings"] = $setting;


	unset($setting);
  $parts[1]["titel"] = getLL("tapes_settings_print_title"); 
  $setting[0]["desc"] = getLL("tapes_settings_print_clear_queue");
  $setting[0]["type"] = "radio";
  $setting[0]["name"] = "rd_clear_printqueue";
  $setting[0]["value"] = array(1, 0);
  $setting[0]["output"] = array(getLL("yes"), getLL("no"));
  $setting[0]["checked"] = ko_html(ko_get_setting("tapes_clear_printqueue"));

	$layout_ids = $layout_names = array(); $layout_default = "";
	$layouts = ko_get_tape_printlayout();
	foreach($layouts as $l) {
		$layout_ids[] = $l["id"];
		$layout_names[] = $l["name"];
		if($l["default"] == 1) $layout_default = $l["id"];
	}
	$setting[1]["desc"] = getLL("tapes_settings_print_default_preset");
	$setting[1]["type"] = "select";
	$setting[1]["name"] = "sel_default_printlayout";
	$setting[1]["values"] = $layout_ids;
	$setting[1]["descs"] = $layout_names;
	$setting[1]["value"] = $layout_default;

	$parts[1]["settings"] = $setting;


  $smarty->assign("tpl_parts", $parts);
  $smarty->assign("tpl_action", "save_settings");
  $smarty->display("ko_settings.tpl");
}//ko_tapes_settings()






function apply_tapes_filter(&$z_where, &$z_limit) {
	global $access;

	$z_limit = "LIMIT " . ((int)$_SESSION["show_start"]-1) . ", " . $_SESSION["show_limit"];


	$z_where = "";
	//Nur erlaubte Gruppen anzeigen
	if($access['tapes']['ALL'] > 0) {
		$z_where = '';
	} else if($access['tapes']['MAX'] > 0) {
		$grps = array();
		foreach($access['tapes'] as $g_i => $g) {
			if(!intval($g_i) || $g < 1) continue;
			$grps[] = $g_i;
		}
		if(sizeof($grps) > 0) $z_where = " AND ko_tapes.group_id IN ('".implode("','", $grps)."') ";
		else $z_where = ' AND 1=2 ';
	} else {
		$z_where = " AND 1=2 ";
		return;
	}

	//Gruppen-Filter
	if(isset($_SESSION["tape_group_filter"])) {
		$z_where .= " AND ko_tapes.group_id = '".$_SESSION["tape_group_filter"]."' ";
	}
	//Serie-Filter
	if(isset($_SESSION["tape_serie_filter"])) {
		$z_where .= " AND ko_tapes.serie_id = '".$_SESSION["tape_serie_filter"]."' ";
	}
	//Title-Filter
	if(isset($_SESSION["tape_title_filter"])) {
		$z_where .= " AND ko_tapes.title REGEXP '".$_SESSION["tape_title_filter"]."' ";
	}
	//Untertitle-Filter
	if(isset($_SESSION["tape_subtitle_filter"])) {
		$z_where .= " AND ko_tapes.subtitle REGEXP '".$_SESSION["tape_subtitle_filter"]."' ";
	}
	//Serie-Filter
	if(isset($_SESSION["tape_preacher_filter"])) {
		$z_where .= " AND ko_tapes.preacher = '".$_SESSION["tape_preacher_filter"]."' ";
	}
}//apply_tapes_filter()





function ko_tape_print(&$pdf, $layout, $data) {
	$pdf->AddPage();

	for($i=0; $i<$layout["items"]; $i++) {
		if(!isset($data[$i]["id"])) continue;

		$rootx = $layout["rootx"][$i];
		$rooty = $layout["rooty"][$i];
		ko_get_tapes($tape_, "AND ko_tapes.id = '".$data[$i]["id"]."'");
		$tape = $tape_[$data[$i]["id"]];
		$tape["group"] = $tape["group_name"];
		ko_get_tapeseries($serie, "AND `id` = '".$tape["serie_id"]."'");
		$tape["serie"] = $serie[$tape["serie_id"]]["name"];

		//Datum
		$c = $layout["layout"]["date"];
		if($c["do"]) {
			$pdf->SetTextColor(0);
			$pdf->SetFont($c["font"], "", $c["fontsize"]);
			//Align
			if($c["align"] == "R") $add = -1*$pdf->GetStringWidth(sql2datum($tape["date"]));
			else if($c["align"] == "C") $add = -0.5*$pdf->GetStringWidth(sql2datum($tape["date"]));
			else $add = 0;
			$pdf->Text(($rootx+$c["x"]+$add), ($rooty+$c["y"]), sql2datum($tape["date"]));
		}
	

		$contents = array("title", "subtitle", "preacher", "group", "serie", "item_number");
		foreach($contents as $typ) {
			$c = $layout["layout"][$typ];
			if($c["do"] == 1) {
				$pdf->SetTextColor(0);
				$pdf->SetFont($c["font"], "", $c["fontsize"]);
				//Align
				if($c["align"] == "R") $add = -1*$pdf->GetStringWidth($tape[$typ]);
				else if($c["align"] == "C") $add = -0.5*$pdf->GetStringWidth($tape[$typ]);
				else $add = 0;
				$pdf->Text(($rootx+$c["x"]+$add), ($rooty+$c["y"]), $tape[$typ]);
			}
		}//foreach(contents as typ)

	}//for(i=0..layout[items])
}//ko_tape_print()


?>
