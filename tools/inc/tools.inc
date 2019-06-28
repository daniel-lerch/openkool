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


require_once($BASE_PATH.'inc/class.kOOL_listview.php');


function ko_show_testmail () {
	global $MAIL_TRANSPORT, $BASE_URL;

	$params = $MAIL_TRANSPORT;
	$subject = 'Testmail from kOOL';
	$text = 'This mail was sent to test the outgoing mail settings on ' . $BASE_URL;
	$html = '<table style="align: center">';

	foreach ($params as $k => $v) {
		$html .= '<tr><td>'.$k.'</td><td><input type="text" size="50" name="mail_transport['.$k.']" value="' . ($k == 'ssl' || $k == 'tls' ? ($v ? 1 : 0) : $v) . '" /></td></tr>';
	}

	$html .= '<tr><td><input type="submit" onclick="set_action('."'submit_mail_transport'".');" value="'.getLL("save").'" /></td></tr>';

	$html .= '<tr style="height:30px"><td></td></tr>';


	$html .= '<tr><td>'.getLL('tools_testmail_receiver').'</td><td><input type="text" size="50" name="testmail[receiver]" /></td></tr>';
	$html .= '<tr><td>'.getLL('tools_testmail_subject').'</td><td>'.$subject.'</td></tr>';
	$html .= '<tr><td>'.getLL('tools_testmail_text').'</td><td>This mail was sent to test the SMTP settings on ' . $BASE_URL . '</td></tr>';

	$html .= '<tr><td><input type="submit" onclick="set_action('."'submit_testmail'".');" value="'.getLL("send").'" /></td></tr>';
	$html .= '</table>';


	$html .= '<input type="hidden" name="testmail[subject]" value="' . $subject . '" />';
	$html .= '<input type="hidden" name="testmail[text]" value="' . $text . '" />';
	print $html;
}


/**
  * Formatiert Telefonnummern
	* Removes all "'", " ", "/", ".", "-" from numbers
	* @param Array fields to be beautyfied
	* @param Bool Do 01 --> 044 Transform (default: FALSE)
	* @param Bool Add Spaces for Numbers of length 10: XXXXXXXXXX --> XXX XXX XX XX (default: FALSE)
	* @param Int Number of Entry to fetch at a time (default: 100)
	* @param Int Max number of Entries allowed, just as a precaution against endless loops (default: 100'000)
	*/
function beautyfy_telephone($fields = array(), $do_01_044 = FALSE, $do_10_to_spaces = FALSE, $limit = 100, $max_entries = 100000) {
	if(sizeof($fields) <= 0) return FALSE;

	$rows = db_get_count("ko_leute");
	$start = 0;
	$done = 0;

	$base_sql = "SELECT `id`";
	foreach($fields as $f) {
		$base_sql .= ", `$f`";
	}
	$base_sql .= " FROM `ko_leute` ";

	while($done < ($rows-1) && $done < $max_entries) {
		$sql_limit = " LIMIT $start, $limit ";

		$sql = $base_sql.$sql_limit;

		//Daten einlesen, anpassen und wieder schreiben
		$result = mysql_query($sql);
		while($row = mysql_fetch_assoc($result)) {
			$sql_update = "";
			foreach($fields as $f) {
				$changed = FALSE;

				//Bereinigen
				$data = $row[$f];
				$data = str_replace(" ", "", $data);
				$data = str_replace("/", "", $data);
				$data = str_replace("'", "", $data);
				$data = str_replace(".", "", $data);
				$data = str_replace("-", "", $data);

				//Vorwahl Zürich
				if($do_01_044) {
					if(strlen($data) == 9 && substr($data, 0, 2) == "01") {
						$data = "044".substr($data, 2);
						$changed = TRUE;
					}
				}
				//Leerschläge bei schweizer Nummern einfügen
				if($do_10_to_spaces) {
					if(strlen($data) == 10) {
						$data = substr($data, 0, 3)." ".substr($data, 3, 3)." ".substr($data, 6,2)." ".substr($data, 8, 2);
						$changed = TRUE;
					}
				}

				//Nur Werte mit Inhalt und die durch eine der obigen Funktionen bearbeitet wurden updaten.
				if($data && $changed) $sql_update .= "`$f` = '$data', ";

			}//foreach(fields as f)
			if($sql_update) {
				$sql = "UPDATE `ko_leute` SET ".substr($sql_update, 0, -2)." WHERE `id` = '".$row["id"]."' LIMIT 1";
				$result2 = mysql_query($sql);
				if(!$result2) {
					print "ERROR! <br />";
					exit;
				}
			}

			$done++;
		}

		$start += $limit;
	}//while(done<rows)

}//beautyfy_telephone()
/*
$fields = array("telp", "telg", "natel", "fax");
$do_01_044 = TRUE;
$do_10_to_spaces = TRUE;
beautyfy_telephone($fields, $do_01_044, $do_10_to_spaces);
*/






/*
 * Submenus
 */
function ko_tools_add_submenu($sm, $users, $module, $pos_="") {
	foreach($sm as $submenu) {
		foreach($users as $user) {
			//Globale Submenus für Guest nicht erlauben (z.B. Notizen)
			if($user == ko_get_guest_id() && substr($submenu, 0, 4) == "gsm_") continue;

			$pref1 = explode(",", ko_get_userpref($user, "submenu_".$module."_left"));
			$pref2 = explode(",", ko_get_userpref($user, "submenu_".$module."_left_closed"));
			$pref3 = explode(",", ko_get_userpref($user, "submenu_".$module."_right"));
			$pref4 = explode(",", ko_get_userpref($user, "submenu_".$module."_right_closed"));
			$all_prefs = array_merge($pref1, $pref2, $pref3, $pref4);

			//Falls sm schon sonst wo (links, rechts oder auch geschlossen) vorhanden ist, nicht doppelt einfügen
			if(in_array($submenu, $all_prefs)) continue;

			//Find position according to defaults in submenu.inc, if not given as argument
			if($pos_ == "") {
				$left = ko_get_submenus($module."_left");
				if(in_array($submenu, $left)) $position = "left";
				else $position = "right";
			} else {
				$position = $pos_;
			}

			$pref = ko_get_userpref($user, "submenu_".$module."_".$position);
			$prefs = $pref=="" ? array() : explode(",", ko_get_userpref($user, "submenu_".$module."_".$position));
			$new = implode(",", array_unique(array_merge($prefs, (array)$submenu)));
			ko_save_userpref($user, "submenu_".$module."_".$position, $new);
		}
	}
}//ko_tools_add_submenu()




function ko_tools_list_submenus($output=TRUE) {
	global $smarty, $ko_path;
	global $MODULES;

	$show_modules = $show_module_names = array();
	foreach($MODULES as $m) {
		if($m != "sms" && $m != "kg") {
			$show_modules[] = $m;
			$show_module_names[] = getLL("module_".$m);
		}
	}
  $rows = db_get_count("ko_admin");
  $z_where = "";
  $z_limit = "";
  ko_get_logins($login, $z_where, $z_limit, "ORDER BY `login` ASC");

  //Statistik über Suchergebnisse und Anzeige
  $smarty->assign('tpl_stats', "1 - ".$rows." ".getLL("list_oftotal")." ".$rows);

  //Links für Prev und Next-Page vorbereiten
  $smarty->assign('tpl_prevlink_link', '');
  $smarty->assign('tpl_nextlink_link', '');

	//Header-Informationen
  $smarty->assign("tpl_show_3cols", TRUE);

  $show_cols = array_merge(array(getLL("login")), $show_module_names);
  $sort_cols = array();
  foreach($show_cols as $c_i => $c) {
    $tpl_table_header[$c_i]["sort"] = $sort_cols[$c_i];
    $tpl_table_header[$c_i]["name"] = $show_cols[$c_i];
  }
  $smarty->assign("tpl_table_header", $tpl_table_header);
  $smarty->assign("show_sort", FALSE);


	//Logins in Liste einfüllen
  foreach($login as $l_i => $l) {
    //Checkbox
    $tpl_list_data[$l_i]["show_checkbox"] = TRUE;

    //Edit-Button
    $tpl_list_data[$l_i]["show_edit_button"] = FALSE;

    //Delete-Button
    $tpl_list_data[$l_i]["show_delete_button"] = FALSE;

    //Index
    $tpl_list_data[$l_i]["id"] = $l_i;

		//Login-Name
		$tpl_list_data[$l_i][1] = "<b>".ko_html($l["login"])."</b> (".$l_i.")";

		//Daten-Submenus
		$counter = 1;
		foreach($show_modules as $m) {
			//Vorhandene Submenu anzeigen
			$sm = ko_tools_get_submenus($l_i, $m);
			$data  = str_replace(",", ", ", $sm["submenu_".$m."_left"]);
			$data .= ' <font color="green">('.str_replace(",", ", ", $sm["submenu_".$m."_left_closed"]);
			$data .= ")</font><br />".str_replace(",", ", ", $sm["submenu_".$m."_right"]);
			$data .= ' <font color="green">('.str_replace(",", ", ", $sm["submenu_".$m."_right_closed"]).")</font>";

			//Fehlende anzeigen
			$sm_shown = array_unique(array_merge(explode(",", $sm["submenu_".$m."_left"]), explode(",", $sm["submenu_".$m."_left_closed"]), explode(",", $sm["submenu_".$m."_right"]), explode(",", $sm["submenu_".$m."_right_closed"])));
			$sm_all = ko_get_submenus($m);
			$diff = array_diff($sm_all, $sm_shown);
			$add = "";
			foreach($diff as $d) {
				if($d) {
					$add .= '<font color="red">';
					$add .= '<a href="index.php?action=add_sm&amp;sm='.$d.'&amp;lid='.$l_i.'&amp;mid='.$m.'&amp;pos=left"><font color="red">';
					$add .= substr($d, 0, 2)."</font></a>";
					$add .= substr($d, 2, -2);
					$add .= '<a href="index.php?action=add_sm&amp;sm='.$d.'&amp;lid='.$l_i.'&amp;mid='.$m.'&amp;pos=right"><font color="red">';
					$add .= substr($d, -2, 2)."</font></a>";
					$add .= '</font>, ';
				}
			}
			if($add) $data .= '<br />'.substr($add, 0, -2);

			$tpl_list_data[$l_i][++$counter] = $data;
		}//foreach(MODULES as m)

	}//foreach(login as l)

	$tpl_list_cols = array(1);
	$counter = 1;
	foreach($show_modules as $m) {
		$tpl_list_cols[] = ++$counter;
	}

	//Optionen für Anpassung ganzer Module anzeigen:
  $button_code  = '<select name="sel_add_modul" size="0">';
	foreach($show_modules as $m) {
		$button_code .= '<option value="'.$m.'">'.getLL("module_".$m).'</option>';
	}
	$button_code .= '</select>&nbsp;&nbsp;<input type="submit" onclick="set_action(\'add_to_modul\');" value="'.getLL("ok").'" />';

	$list_footer[] = array("label" => getLL("tools_list_footer_complete"),
												 "button" => $button_code);
	$smarty->assign("show_list_footer", TRUE);
	$smarty->assign("list_footer", $list_footer);

	$smarty->assign('tpl_list_cols', $tpl_list_cols);
  $smarty->assign('tpl_list_data', $tpl_list_data);
	$smarty->assign('hide_listlimiticons', TRUE);
	if($output) {
	  $smarty->display('ko_list.tpl');
	} else {
	  print $smarty->fetch('ko_list.tpl');
	}
}//ko_tools_list_submenus()




function ko_tools_get_submenus($id, $type) {
	$r = array();
	$query = "SELECT * FROM `ko_userprefs` WHERE `user_id` = '$id' AND `key` REGEXP 'submenu_$type'";
	$result = mysql_query($query);
	while($row = mysql_fetch_assoc($result)) {
		$r[$row["key"]] = $row["value"];
	}
	return $r;
}//ko_tools_get_submenus()






/*
 * Person DB
 */
function ko_tools_list_leute_db() {
	global $smarty, $ko_path;
	global $WEB_LANGS;

	$table_cols = db_get_columns("ko_leute");
	$col_names = unserialize(ko_get_setting("leute_col_name"));
	ko_get_filters($filters, "leute");

	print '<table border="0"><tr>';
	print '<td style="font-weight:bold;">&nbsp;</td>';
	print '<td style="font-weight:bold;">'.getLL("tools_listheader_people_field").'</td>';
	foreach($WEB_LANGS as $lang) print '<td style="font-weight:bold;">'.getLL("tools_listheader_people_name")." ".strtoupper(substr($lang, 0, 2)).'</td>';
	print '<td style="font-weight:bold;">'.getLL("tools_listheader_people_filter").'</td>';
	print '</tr>';

	$tabindex = 1;
	foreach($table_cols as $c) {
		if(!in_array($c["Field"], array("deleted"))) {
			print '<tr>';
			print '<td><input type="image" src="'.$ko_path.'images/button_delete.gif" alt="'.getLL("tools_people_del_col").'" title="'.getLL("tools_people_del_col").'" onclick="c=confirm('."'".getLL("tools_people_del_col_confirm")."'".');if(!c) return false;set_hidden_value('."'id', '".$c["Field"]."');".'set_action('."'delete_leute_col'".');" /></td>';
			print '<td>'.$c["Field"].'</td>';
			foreach($WEB_LANGS as $lang) {
				list($l, $l2) = explode('_', $lang);
				print '<td><input type="text" value="'.$col_names[$l][$c["Field"]].'" name="txt_col_name['.$l.']['.$c["Field"].']" tabindex="'.$tabindex.'" /></td>';
				$tabindex++;
			}

			//Filter
			$fid = 0;
			$special_filters = array("age", "year", "role");
			foreach($filters as $i => $f) {
				if(!$fid && ko_get_filter_column($f["sql1"]) == $c["Field"] && !in_array($f["_name"], $special_filters)) {
					$fid = $i;
				}
			}
			if($fid != 0) {
				ko_get_filter_by_id($fid, $f);
				print '<td>';
				print '<input type="image" src="'.$ko_path.'images/button_delete.gif" alt="'.getLL("tools_people_del_filter").'" title="'.getLL("tools_people_del_filter").'" onclick="c=confirm('."'".getLL("tools_people_del_filter_confirm")."'".');if(!c) return false;set_hidden_value('."'id', $fid);".'set_action('."'delete_leute_filter'".');">';
				print "&nbsp;";
				print '<input type="image" src="'.$ko_path.'images/icon_reload.png" alt="'.getLL("tools_people_renew_filter").'" title="'.getLL("tools_people_renew_filter").'" onclick="c=confirm('."'".getLL("tools_people_renew_filter_confirm")."'".');if(!c) return false;set_hidden_value('."'fid', $fid);set_hidden_value('id', '".$c["Field"]."'".');set_action('."'reload_leute_filter'".');">';
				print "&nbsp;";
				print ko_tools_get_filter_group_select($f);
				print "&nbsp;";
				print $f["name"].": ".$f["sql1"]." (".$f["code1"].")";
				print '</td>';
				unset($filters[$fid]);
			} else {
				print '<td>';
				print '<input type="image" src="'.$ko_path.'images/icon_plus.png" alt="'.getLL("tools_people_add_filter").'" title="'.getLL("tools_people_add_filter").'" onclick="set_hidden_value('."'id', '".$c["Field"]."');".'set_action('."'add_leute_filter'".');">';
				print '</td>';
			}
			print '</tr>';
		} else {
			print '<tr><td colspan="2" style="color:grey">'.$c["Field"].'</td></tr>';
		}
	}

	foreach($filters as $f) {
		print '<tr><td>&nbsp;</td><td>&nbsp;</td>';
		foreach($WEB_LANGS as $lang) print '<td>&nbsp;</td>';
		print '<td>';
		print '<input type="image" src="'.$ko_path.'images/button_delete.gif" alt="'.getLL("tools_people_del_filter").'" title="'.getLL("tools_people_del_filter").'" onclick="c=confirm('."'".getLL("tools_people_del_filter_confirm")."'".');if(!c) return false;set_hidden_value('."'id', ".$f["id"].");".'set_action('."'delete_leute_filter'".');">';
		print "&nbsp;";
		print ko_tools_get_filter_group_select($f);
		print "&nbsp;";
		print $f["name"].": ".$f["sql1"]." (".$f["code1"].")";
		print '</td></tr>';
	}

	print '</table>';

	print '<input type="submit" onclick="set_action('."'save_leute_db'".');" value="'.getLL("save").'">';
}//ko_tools_list_leute_db()




function ko_tools_get_filter_group_select($filter) {
	global $ko_path;

	//Select to change the filter's group
	$filter_groups = '<select name="filter_group" size="0" onchange="sendReq(\''.$ko_path.'/tools/inc/ajax.php\', \'action,id,group,sesid\', \'setfiltergroup,'.$filter['id'].',\'+this.options[this.selectedIndex].value+\','.session_id().'\', do_element);">';
	$filter_groups .= '<option value=""></option>';
	$gids = db_select_distinct('ko_filter', '`group`');
	foreach($gids as $gid) {
		$sel = $gid == $filter['group'] ? 'selected="selected"' : '';
		$filter_groups .= '<option value="'.$gid.'" '.$sel.'>'.getLL('filter_group_'.$gid).'</option>';
	}
	$filter_groups .= '</select>';

	return $filter_groups;
}//ko_tools_get_filter_group_select()





function ko_tools_list_familie_db() {
	global $smarty, $ko_path;
	global $WEB_LANGS;

	$table_cols = db_get_columns("ko_familie");
	$col_names = unserialize(ko_get_setting("familie_col_name"));

	print '<table border="0"><tr>';
	print '<td style="font-weight:bold;">&nbsp;</td>';
	print '<td style="font-weight:bold;">'.getLL("tools_listheader_people_field").'</td>';
	foreach($WEB_LANGS as $lang) print '<td style="font-weight:bold;">'.getLL("tools_listheader_people_name")." ".strtoupper(substr($lang, 0, 2)).'</td>';
	print '</tr>';

	$tabindex = 1;
	foreach($table_cols as $c_i => $c) {
		if($c["Field"] != "famid") {
			print '<tr>';
			print '<td><input type="image" src="'.$ko_path.'images/button_delete.gif" alt="'.getLL("tools_people_del_col").'" title="'.getLL("tools_people_del_col").'" onclick="c=confirm('."'".getLL("tools_people_del_col_confirm")."'".');if(!c) return false;set_hidden_value('."'id', '".$c["Field"]."');".'set_action('."'delete_familie_col'".');"></td>';
			print '<td>'.$c["Field"].'</td>';
			foreach($WEB_LANGS as $lang) {
				list($l, $l2) = explode('_', $lang);
				print '<td><input type="text" value="'.$col_names[$l][$c["Field"]].'" name="txt_col_name['.$l.']['.$c["Field"].']" tabindex="'.$tabindex.'" /></td>';
				$tabindex++;
			}
			print '</tr>';
		} else {
			print '<tr><td colspan="2" style="color:grey">'.$c["Field"].'</td></tr>';
		}
	}
	print '</table>';

	print '<input type="submit" onclick="set_action('."'save_familie_db'".');" value="'.getLL("save").'">';
}//ko_tools_list_familie_db()




function ko_tools_leute_formular() {
	global $ko_path, $LEUTE_EXCLUDE;

	$file = file($ko_path."config/leute_formular.inc");
	$content = implode("", $file);
	print '<textarea cols="100" rows="50" name="txt_leute_formular">';
	print $content;
	print '</textarea><br /><br />';
	print '<input type="submit" name="submit" value="'.getLL("save").'" onclick="'."set_action('submit_save_leute_formular');".'" />';
	print '&nbsp;&nbsp;<input type="reset" name="reset" value="'.getLL("reset").'" /><br /><br />';

	print "<b>".getLL("tools_people_form_not_found")."</b><br />";
	$cols = db_get_columns("ko_leute");
	$exclude = array_merge($LEUTE_EXCLUDE, array('famfunction'));
	foreach($cols as $c) {
		$found = FALSE;
		foreach($file as $f) {
			if(strstr($f, $c["Field"])) $found = TRUE;
		}
		if(!$found && !in_array($c["Field"], $exclude)) print $c["Field"].", ";
	}
}//ko_tools_leute_formular()





/*
 * LDAP-Functions
 */
function ko_tools_ldap_export() {
	global $ldap_enabled, $ldap_dn;

	if(!$ldap_enabled) return FALSE;

	//Anzahl Personen im LDAP
	$ldap = ko_ldap_connect();
	if($ldap) {
		$result = ldap_search($ldap, $ldap_dn, "uid=*");
		$lnum = ldap_count_entries($ldap, $result);
	} else {
		$lnum = FALSE;
	}
	ko_ldap_close();

	//Anzahl Personen in der DB
	$dbnum = db_get_count("ko_leute", "id", "AND deleted = '0'");

	if($ldap) {
		print getLL("tools_ldap_num_ldap")." ".$lnum."<br />";
	} else {
		print '<div class="errortxt">' . getLL("tools_ldap_connect_error") . "</div> <br />";
	}
	print getLL("tools_ldap_num_db")." ".$dbnum."<br />";

	// Do not show export button if we can not connect to LDAP.
	if($ldap) {
		print '<br /><input type="submit" name="submit" value="'.getLL("tools_ldap_export").'" onclick="'."javascript: c=confirm('".getLL("tools_ldap_export_confirm")."'); if(!c) return false; set_action('ldap_do_export');".'" />';
	}
}//ko_tools_ldap_export()



function ko_tools_ldap_logins() {
	global $ldap_enabled, $ldap_dn;

	if(!$ldap_enabled) return FALSE;

	//Get LDAP-Logins
	$ldap = ko_ldap_connect();
	if ( $ldap ) {
		$result = ldap_search($ldap, "ou=login,".$ldap_dn, "cn=*");
		$ldap_logins = ldap_get_entries($ldap, $result);
	} else {
		print '<div class="errortxt">'.getLL('tools_ldap_connect_error').'</div><br />';
	}
	ko_ldap_close();
	for($i=0; $i<$ldap_logins["count"]; $i++) {
		$ll[$i]["login"] = $ldap_logins[$i]["cn"][0];
		$ll[$i]["password"] = $ldap_logins[$i]["userpassword"][0];
	}

	ko_get_logins($l);
	
	$max = max(sizeof($l), sizeof($ll));
	print '<table border="1">';
	print '<tr><td align="center">'.sizeof($l).'</td><td align="center">'.sizeof($ll).'</td></tr>';
	foreach($l as $login) {
		print '<tr><td>';

		//Check for access rights in login and assigned admingroups
		$all = ko_get_access_all('leute_admin', $login['id'], $max);

		//Display login
		if($max >= 1) {
			$pre = '<b><a href="index.php?action=export_to_ldap&amp;id='.$login["id"].'">';
			$post = '</a></b>';
		} else {
			$pre = $post = "";
		}
		print $pre.$login["login"]." ($max)".$post;
		print '</td><td>&nbsp;';
		foreach($ll as $id => $ll_) {
			if($ll_["login"] == $login["login"]) {
				print '<b><a href="index.php?action=delete_from_ldap&amp;id='.$login["login"].'">'.$ll_["login"]."</a></b> (".$ll_["password"].")";
				unset($ll[$id]);
			}
		}
		print '</td></tr>';
	}//for(i=0..max)

	//LDAP-Logins, die nicht im kOOL auftauchen
	for($i=0; $i<sizeof($ll); $i++) {
		if(!$ll[$i]['login']) continue;
		print '<tr><td>&nbsp;</td><td>';
		if($ll[$i]["login"] != "kOOL_ldapadmin") {
			print '<b><a href="index.php?action=delete_from_ldap&amp;id='.$ll[$i]["login"].'">'.$ll[$i]["login"]."</a></b> (".$ll[$i]["password"].")";
		} else {
			print '<b>'.$ll[$i]["login"]."</b> (".$ll[$i]["password"].")";
		}
		print '</td></tr>';
	}
	print '</table>';
}//ko_tools_ldap_logins()





/*
 * LL-Functions
 */
function ko_tools_ll_overview() {
	global $LIB_LANGS, $WEB_LANGS;
	global $ko_path;

	print "<b>".getLL("tools_ll_available_lib")."</b>: ".sizeof($LIB_LANGS)." - ".implode(", ", $LIB_LANGS).'<br />';
	print "<b>".getLL("tools_ll_available_web")."</b>: ".sizeof($WEB_LANGS)." - ".implode(", ", $WEB_LANGS).'<br /><br />';
	$max = 0;
	foreach($LIB_LANGS as $l) {
		$ll[$l]["name"] = $l;
		include($ko_path."locallang/locallang.$l.php");
		$ll[$l]["num"] = sizeof($LL[$l]);
		unset($LL[$l]);
		$max = max($max, $ll[$l]["num"]);
	}

	print '<table border="1">';
	foreach($LIB_LANGS as $l) {
		print '<tr>';
		print '<td><b><a href="?action=ll_edit_all&amp;lang='.$l.'">'.$ll[$l]["name"].'</a></b></td>';
		print '<td>'.$ll[$l]["num"].'</td>';
		print '<td><a href="?action=ll_edit&amp;lang='.$l.'">'.(int)($ll[$l]["num"]/$max*100).'%</a></td>';
		print '</tr>';
	}
	print '</table>';
}//ko_tools_ll_overview()


function ko_tools_ll_edit($edit_lang, $edit_mode="all") {
	global $ko_path, $default_lang;

	print '<b>'.$edit_lang.'</b><br /><br />';
	
	if($edit_lang != $default_lang) include($ko_path."locallang/locallang.$default_lang.php");
	include($ko_path."locallang/locallang.$edit_lang.php");
	print '<table border="1">';
	$found = FALSE;
	foreach($LL[$default_lang] as $d_i => $d_v) {
		if($edit_mode == "empty" && isset($LL[$edit_lang][$d_i])) continue;
		$found = TRUE;

		$style = isset($LL[$edit_lang][$d_i]) ? 'color:green;' : 'color:red;font-weight:900';
		print '<tr>';
		print '<td style="'.$style.'">'.$d_i.'</td>';
		print '<td style="'.$style.'">'.$d_v.'</td>';
		$input_name = str_replace(" ", "@", str_replace(".", "@@", $d_i));
		print '<td><input type="text" size="60" name="ll_txt_'.$input_name.'" value="'.str_replace('"', '', $LL[$edit_lang][$d_i]).'" /></td>';
		print '</tr>';
	}
	print '</table>';
	if($found) print '<div align="center"><input type="submit" name="submit" onclick="set_hidden_value('."'id', '$edit_lang');set_action('ll_edit_submit'".');" value="'.getLL("save").'" />';
}//ko_tools_ll_edit()



function ko_tools_ll_value($value) {
	$r = str_replace('"', '', $value);
	return $r;
}//ko_tools_ll_value()



function ko_tools_write_ll_file($LL, $l) {
	global $ko_path;

	$fp = fopen($ko_path."locallang/locallang.$l.php", "w");
	fputs($fp, '<?php'."\n");
	fputs($fp, '/***************************************************************
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
');

	fputs($fp, '$LL["'.$l.'"] = array('."\n");
	foreach($LL as $key => $value) {
		fputs($fp, "'".$key."' => ".'"'.$value.'",'."\n");
	}
	fputs($fp, '); //'.$l."\n");
	fputs($fp, '?>'."\n");
}//ko_tools_write_ll_file()





/*
 * Plugins
 */
function ko_tools_plugins_get_available() {
	global $ko_path;

	$plugins_available = array();
	$dir = $ko_path."plugins/";
	if(is_dir($dir)) {
		if($dh = opendir($dir)) {
			while(($file = readdir($dh)) !== false) {
				if(filetype($dir.$file) == "dir" && $file != ".." && $file != ".") {
					$plugins_available[] = $file;
				}
			}
			closedir($dh);
		}//if(dh = opendir())
	}//if(is_dir($dir)

	sort($plugins_available, SORT_LOCALE_STRING);

	return $plugins_available;
}//ko_tools_plugins_get_available()


function ko_tools_plugins_get_installed(&$plugins_available) {
	global $PLUGINS;

	$plugins_installed = array();
	//Find all installed plugins
	foreach($PLUGINS as $plugin) {
		if(in_array($plugin["name"], $plugins_available)) {
			$plugins_installed[] = $plugin["name"];
		}
	}//foreach(PLUGINS)

	return $plugins_installed;
}//ko_tools_plugins_get_installed()


function ko_tools_plugins_list($output=TRUE) {
	global $ko_path, $smarty;

	$plugins_available = ko_tools_plugins_get_available();
	$plugins_installed = ko_tools_plugins_get_installed($plugins_available);

	$rows = sizeof($plugins_available);
	//Statistik über Suchergebnisse und Anzeige
  $smarty->assign('tpl_stats', "1 - ".$rows." ".getLL("list_oftotal")." ".$rows);
	$smarty->assign('tpl_prevlink_link', '');
	$smarty->assign('tpl_nextlink_link', '');
	$smarty->assign("tpl_list_title", getLL("tools_plugins"));

	//Header-Informationen
	$smarty->assign("tpl_show_3cols", TRUE);

	$show_cols = array(getLL("tools_plugins_listheader_key"), getLL("tools_plugins_listheader_title"), getLL("tools_plugins_listheader_description"), getLL("tools_plugins_listheader_dependencies"), getLL("tools_plugins_listheader_version"), getLL("tools_plugins_listheader_state"));
  $sort_cols = array();
  foreach($show_cols as $c_i => $c) {
    $tpl_table_header[$c_i]["sort"] = $sort_cols[$c_i];
    $tpl_table_header[$c_i]["name"] = $show_cols[$c_i];
  }
  $smarty->assign("tpl_table_header", $tpl_table_header);
  $smarty->assign("sort", array("show" => FALSE));
	$smarty->assign("tpl_show_editrow", FALSE);

	//Liste ausgeben
	$p_counter = 0;
	foreach($plugins_available as $plugin) {
		//Conf-Datei einlesen
		$hidden = FALSE;
		$conf_file = $ko_path."plugins/".$plugin."/config.php";
		if(!file_exists($conf_file)) $hidden = TRUE;
		else include($conf_file);

		$p_counter++;
		$installed = in_array($plugin, $plugins_installed);

		//Mark not installed ones
		$tpl_list_data[$p_counter]["rowclass"] = $installed ? "" : "ko_list_hidden";

		//Checkbox
		$tpl_list_data[$p_counter]["show_checkbox"] = FALSE;

		//Edit-Button
		if(!$installed && !$hidden) {
			$tpl_list_data[$p_counter]["show_check_button"] = TRUE;
			$tpl_list_data[$p_counter]["alt_edit"] = getLL("tools_plugins_install");
			$tpl_list_data[$p_counter]["onclick_edit"] = "javascript:set_action('plugins_install');set_hidden_value('id', '$plugin');this.submit";
		}

		//Delete-Button
		if($installed && !$hidden) {
			$tpl_list_data[$p_counter]["show_delete_button"] = TRUE;
			$tpl_list_data[$p_counter]["alt_delete"] = getLL("tools_plugins_delete");
			$tpl_list_data[$p_counter]["onclick_delete"] = "javascript:c = confirm('".getLL("tools_plugins_delete_confirm")."');if(!c) return;set_action('plugins_delete');set_hidden_value('id', '$plugin');";
		}

		//Index
		$tpl_list_data[$p_counter]["id"] = $plugin;

		//Key
		$link1 = $hidden?"":'<a href="?action=plugins_'.($installed?"delete":"install").'&amp;id='.$plugin.'">';
		$link2 = $hidden?"":'</a>';
		$pre  = '<div class="'.($installed?"plugins_installed":"").($hidden?" plugins_hidden":"").'">';
		$post = '</div>';
		$tpl_list_data[$p_counter][1] = $link1.$pre.ko_html($plugin).$post.$link2;
		
		//Title
		$tpl_list_data[$p_counter][2] = ko_html($PLUGIN_CONF[$plugin]["title"]);
		
		//Description
		$tpl_list_data[$p_counter][3] = ko_html($PLUGIN_CONF[$plugin]["description"]);
		
		//Dependencies
		$tpl_list_data[$p_counter][4] = ko_html($PLUGIN_CONF[$plugin]["dependencies"]);
		
		//Version
		$tpl_list_data[$p_counter][5] = ko_html($PLUGIN_CONF[$plugin]["version"]);
		
		//State
		$tpl_list_data[$p_counter][6] = ko_html($PLUGIN_CONF[$plugin]["state"]);
		
	}//foreach(plugins)

	$smarty->assign('tpl_list_cols', array(1, 2, 3, 4, 5, 6));
  $smarty->assign('tpl_list_data', $tpl_list_data);
	$smarty->assign('hide_listlimiticons', TRUE);
	if($output) {
		$smarty->display('ko_list.tpl');
	} else {
		print $smarty->fetch('ko_list.tpl');
	}

}//ko_tools_plugins_list()




function ko_list_tasks($output=TRUE) {
	global $smarty;
	global $access;

	$rows = db_get_count('ko_scheduler_tasks', 'id');
	if($_SESSION['show_start'] > $rows) $_SESSION['show_start'] = 1;
  $z_limit = 'LIMIT '.($_SESSION['show_start']-1).', '.$_SESSION['show_limit'];
	$es = db_select_data('ko_scheduler_tasks', '', '*', 'ORDER BY name ASC', $z_limit);

	$list = new kOOL_listview();

	$list->init('tools', 'ko_scheduler_tasks', array('chk', 'edit', 'delete'), $_SESSION['show_start'], $_SESSION['show_limit']);
	$list->setTitle(getLL('tools_scheduler_tasks_list_title'));
	$list->setAccessRights(FALSE);
	$list->setActions(array('edit' => array('action' => 'edit_task'),
													'delete' => array('action' => 'delete_task', 'confirm' => TRUE))
										);
	$list->setStats($rows);
	$list->setSort(TRUE, 'setsort', $_SESSION['sort_tasks'], $_SESSION['sort_tasks_order']);
	$list->setRowClass("ko_list_hidden", 'return STATUS == 0;');

	//Output the list
	if($output) {
		$list->render($es);
	} else {
		print $list->render($es);
	}
}//ko_list_tasks()





function ko_formular_task($mode, $id='') {
	global $KOTA;

	if($mode == 'new') {
		$id = 0;
	} else if($mode == 'edit') {
		if(!$id) return FALSE;
	} else {
		return FALSE;
	}

	$form_data['title'] =  $mode == 'new' ? getLL('form_scheduler_title_new_task') : getLL('form_scheduler_title_edit_task');
	$form_data['submit_value'] = getLL('save');
	$form_data['action'] = $mode == 'new' ? 'submit_new_task' : 'submit_edit_task';
	$form_data['cancel'] = 'scheduler_list';

	ko_multiedit_formular('ko_scheduler_tasks', '', $id, '', $form_data);
}//ko_formular_task()





function ko_tools_typo3_connection() {
	global $smarty, $ko_path;

	$smarty->assign('tpl_titel', getLL('tools_typo3_connection'));

	$pc = 0;
	$parts[$pc]['titel'] = getLL('tools_typo3_connection_title');
	$fields = array('typo3_host', 'typo3_db', 'typo3_user', 'typo3_pwd');
	$cc = 0;
	$setting = array();
	foreach($fields as $field) {
		$setting[$cc]['desc'] = getLL('tools_typo3_connection_'.$field);
		$setting[$cc]['type'] = 'text';
		$setting[$cc]['name'] = $field;
		$setting[$cc]['params'] = 'size="40"';

		if($field == 'typo3_pwd') {
			$pwd_enc = ko_get_setting('typo3_pwd');
			if($pwd_enc != '') {
				include_once($ko_path.'inc/class.mcrypt.php');
				$crypt = new mcrypt('aes');
				$crypt->setKey(KOOL_ENCRYPTION_KEY);
				$pwd = trim($crypt->decrypt($pwd_enc));
			} else {
				$pwd = '';
			}

			$setting[$cc]['value'] = $pwd;
		} else {
			$setting[$cc]['value'] = ko_html(ko_get_setting($field));
		}
		$cc++;
	}

	$parts[$pc]['settings'] = $setting;

	$smarty->assign('tpl_parts', $parts);
	$smarty->assign('tpl_action', 'submit_typo3_connection');
	$smarty->display('ko_settings.tpl');
}//ko_tools_typo3_connection()




function ko_tools_misc() {
	$c = '';
	$c .= '<h1>Set userprefs for all users</h1>';
	$c .= '<form action="'.$ko_path.'tools/index.php" method="POST">';
	$c .= 'Key: <select name="sel_userpref_key" size="0"><option value=""></option>';
	$keys = db_select_distinct('ko_userprefs', '`key`', 'ORDER BY `key` ASC', 'WHERE `type` = \'\'');
	foreach($keys as $key) {
		$c .= '<option value="'.$key.'">'.$key.'</option>';
	}
	$c .= '</select><br />';
	$c .= 'Value: <input type="text" name="txt_userpref_value" /><br />';
	$c .= '<input type="submit" value="Save" onclick="set_action(\'submit_userpref\', this)" />';
	$c .= '</form>';

	print $c;
}//ko_tools_misc()


?>
