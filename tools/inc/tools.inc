<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2003-2020 Renzo Lauper (renzo@churchtool.org)
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



function ko_tools_mailing_mails() {
  $rows = db_get_count('ko_mailing_mails', 'id');
  if($_SESSION['show_start'] > $rows) $_SESSION['show_start'] = 1;
  $z_limit = 'LIMIT '.($_SESSION['show_start']-1).', '.$_SESSION['show_limit'];
  $es = db_select_data('ko_mailing_mails', '', '*, NULL as body', 'ORDER BY id DESC', $z_limit);

  $access_check = $access_delete = array();
  foreach($es as $k => $e) {
    if($e['status'] == 1) {  //new
      $access_check[$k] = TRUE;
      $access_delete[$k] = TRUE;
    } else if($e['status'] == 2) {  //confirmed
      $access_check[$k] = FALSE;
      $access_delete[$k] = TRUE;
    } else {
      $access_check[$k] = FALSE;
      $access_delete[$k] = FALSE;
    }
  }

  $list = new kOOL_listview();

  $list->init('tools', 'ko_mailing_mails', array('chk', 'check', 'delete'), $_SESSION['show_start'], $_SESSION['show_limit']);
  $list->setTitle(getLL('tools_mailing_mails_list_title'));
  $list->setManualAccess('check', $access_check);
  $list->setManualAccess('delete', $access_delete);
  $list->setActions(array('check' => array('action' => 'mailing_confirm', 'confirm' => TRUE),
                          'delete' => array('action' => 'mailing_delete', 'confirm' => TRUE))
                    );
  $list->setStats($rows);
  $list->setSort(FALSE);
  $list->setRowClass("ko_list_hidden", 'return STATUS == 3;');

  //Output the list
	$list->render($es);
}//ko_tools_mailing_mails()





function ko_show_testmail () {
	global $MAIL_TRANSPORT, $BASE_URL;

	$params = $MAIL_TRANSPORT;
	$subject = 'Testmail from kOOL';
	$text = 'This mail was sent to test the outgoing mail settings on ' . $BASE_URL;
	$html = '<h2>Mail settings</h2>';
	$html .= '<table style="align: center">';

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



	$html .= '<h2>SPF cache</h2>';

	$html .= '<b>Blacklisted domains: SPF immer FALSE für diese Domains. Nie als From-Adressen verwendet (Kommaliste)</b><div>';
	$html .= '<input type="text" size="200" name="spf_blacklisted_domains" value="'.ko_get_setting('spf_blacklisted_domains').'" />';
	$html .= '<input type="submit" class="btn btn-sm btn-default" onclick="set_action(\'submit_spf_blacklisted_domains\');" value="Speichern" /></div><br />';


	$html .= '<table class="table table-bordered table-alternating"><tr><th>Domain</th><th>Status</th><th>lastUpdate</th><th>&nbsp;</th></tr>';
	$cached = json_decode(ko_get_setting('spf_domains'), TRUE);
	ksort($cached);
	foreach($cached as $domain => $vals) {
		$class = ($vals['status'] == 1 ? 'row-success' : 'row-danger');
		$html .= '<tr class="'.$class.'">';
		$html .= '<th>'.$domain.'</th>';
		$html .= '<td>'.($vals['status'] == 1 ? 'OK' : '-').'</td>';
		$html .= '<td>'.$vals['lastUpdate'].'</td>';
		$html .= '<td><input type="submit" class="btn btn-sm btn-default" onclick="set_hidden_value(\'id\', \''.$domain.'\');set_action(\'clear_spf_domain\');" value="Clear" /></td>';
		$html .= '</tr>';
	}
	$html .= '</table>';


	print $html;
}


/**
  * Formatiert Telefonnummern
	* Removes all "'", " ", "/", ".", "-" from numbers
	* @param array fields to be beautyfied
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
		$result = mysqli_query(db_get_link(), $sql);
		while($row = mysqli_fetch_assoc(db_get_link(), $result)) {
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
				$result2 = mysqli_query(db_get_link(), $sql);
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





function ko_tools_get_submenus($id, $type) {
	$r = array();
	$query = "SELECT * FROM `ko_userprefs` WHERE `user_id` = '$id' AND `key` REGEXP 'submenu_$type'";
	$result = mysqli_query(db_get_link(), $query);
	while($row = mysqli_fetch_assoc($result)) {
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

	// include KOTA (for filters)
	ko_include_kota(array('ko_leute'));

	$table_cols = db_get_columns("ko_leute");
	$col_names = ko_get_leute_col_name();
	ko_get_filters($filters, "leute");

	print '<table class="table table-condensed"><tr>';
	print '<td style="font-weight:bold;">&nbsp;</td>';
	print '<td style="font-weight:bold;width:20%;">'.getLL("tools_listheader_people_field").'</td>';
	print '<td style="font-weight:bold;">'.getLL("tools_listheader_people_filter").'</td>';
	print '</tr>';

	$tabindex = 1;
	foreach($table_cols as $c) {
		if(!in_array($c['Field'], array('deleted', 'cruserid'))) {
			print '<tr>';
			print '<td style="vertical-align:middle;">';
			print '<button type="submit" class="btn btn-sm btn-default" title="'.getLL("tools_people_del_col").'" onclick="c=confirm('."'".getLL("tools_people_del_col_confirm")."'".');if(!c) return false;set_hidden_value('."'id', '".$c["Field"]."');".'set_action('."'delete_leute_col'".');"><i class="fa fa-remove icon-line-height" ></i></button>';
			print '</td>';
			print '<td style="vertical-align:middle;">'.$c["Field"].'</td>';

			//Filter
			print '<td style="vertical-align:middle;" id="leute-filter-settings-dbcol-'.$c["Field"].'" name="leute-filter-settings-dbcol-'.$c["Field"].'">';
			print ko_tools_get_leute_filter_settings_code($c['Field'], NULL, $filters, $filterFound);
			print '</td>';
			print '</tr>';
			if ($filterFound) unset($filters[$filterFound['id']]);
		} else {
			print '<tr><td colspan="2" style="color:grey">'.$c["Field"].'</td></tr>';
		}
	}

	foreach($filters as $f) {
		print '<tr><td>&nbsp;</td><td>&nbsp;</td>';
		print '<td id="leute-filter-settings-filter-'.$f["id"].'" name="leute-filter-settings-filter-'.$f["id"].'">';
		print ko_tools_get_leute_filter_settings_code(NULL, $f, $filters);
		print '</td></tr>';
	}

	print '</table>';
}//ko_tools_list_leute_db()




function ko_tools_get_leute_filter_settings_code($fieldName=NULL, $filter=NULL, &$filters=NULL, &$filterFound=NULL) {
	global $ko_path;

	$html = '';

	if ($fieldName == NULL) {
		$html .= '<table><tr><td><button class="btn btn-sm btn-danger" type="button" title="'.getLL("tools_people_del_filter").'" onclick="c=confirm('."'".getLL("tools_people_del_filter_confirm")."'".');if(!c) return false;sendReq('."'../tools/inc/ajax.php', ['action', 'sesid', 'fid'], ['deleteleutefilter', kOOL.sid, '{$filter['id']}'], do_element".');"><i class="fa fa-remove icon-line-height"></i></button></td>';
		$html .= "<td>";
		$html .= ko_tools_get_filter_group_select($filter);
		$html .= "</td><td>";
		$html .= '<table><tr><td style="white-space:nowrap;">&nbsp;'.$filter["name"]."&nbsp;: ".$filter["sql1"]." (</td><td>".$filter["code1"]."</td><td>)</td></tr></table>";
		$html .= '</td></tr></table>';
	} else {
		if ($filters == NULL) {
			ko_get_filters($filters, "leute");
		}

		$fid = 0;
		$special_filters = array("age", "year", "role"); // this array is also defined in tools/inc/ajax.php
		foreach($filters as $i => $f) {
			if(!$fid && ko_get_filter_column($f["sql1"], $f['dbcol']) == $fieldName && !in_array($f["_name"], $special_filters)) {
				$fid = $i;
			}
		}

		if($fid != 0) {
			ko_get_filter_by_id($fid, $f);
			$filterFound = $f;
			$html .= '<table><tr><td>';
			$html .= '<div class="btn-group">';
			$html .= '<button class="btn btn-sm btn-danger" type="button" title="'.getLL("tools_people_del_filter").'" onclick="c=confirm('."'".getLL("tools_people_del_filter_confirm")."'".');if(!c) return false;sendReq('."'../tools/inc/ajax.php', ['action', 'sesid', 'fid', 'id'], ['deleteleutefilter', kOOL.sid, '{$fid}', '{$fieldName}'], do_element".');"><i class="fa fa-remove icon-line-height"></i></button>';
			$fastFilter = $f['allow_fastfilter'];
			$html .= '<button class="btn btn-sm '.($fastFilter ? 'btn-success' : 'btn-danger').'" type="button" title="'.getLL("tools_people_set_fastfilter").'" onclick="sendReq('."'../tools/inc/ajax.php', ['action', 'sesid', 'fid'], ['ableFastfilter', kOOL.sid, '{$fid}'], do_element".');"><i class="fa fa-search icon-line-height"></i></button>';
			$html .= '<button class="btn btn-default btn-sm" type="button" src="'.$ko_path.'images/icon_reload.png" alt="'.getLL("tools_people_renew_filter").'" title="'.getLL("tools_people_renew_filter").'" onclick="c=confirm('."'".getLL("tools_people_renew_filter_confirm")."'".');if(!c) return false;sendReq('."'../tools/inc/ajax.php', ['action', 'sesid', 'id', 'fid'], ['reloadleutefilter', kOOL.sid, '{$fieldName}', '{$fid}'], do_element".');"><i class="fa fa-refresh icon-line-height"></i></button></div>';
			$html .= '</td><td>';
			$html .= ko_tools_get_filter_group_select($f, $filter);
			$html .= '</td><td>';
			$html .= '<table><tr><td style="white-space:nowrap;">&nbsp;'.$f["name"].": ".$f["sql1"].' (</td><td style="white-space:nowrap;">'.$f["code1"].'</td></tr></table>';
			$html .= '</td></tr></table>';
		} else {
			$filterFound = FALSE;
			$html .= '<button type="button" class="btn btn-sm btn-primary" src="'.$ko_path.'images/icon_plus.png" alt="'.getLL("tools_people_add_filter").'" title="'.getLL("tools_people_add_filter").'" onclick="sendReq('."'../tools/inc/ajax.php', ['action', 'sesid', 'id'], ['addleutefilter', kOOL.sid, '{$fieldName}'], do_element".');"><i class="fa fa-plus icon-line-height"></i></button>';
		}
	}

	return $html;
}


/**
 * Create a select-box with the groups (categories) as options
 *
 * @param array $filter entry from ko_filter
 * @param null $old_filter select option from old setting
 * @return string HTML select-box
 */
function ko_tools_get_filter_group_select($filter, $old_filter = NULL) {
	global $ko_path;

	if($filter['group'] == 'misc' AND $old_filter !== NULL) {
		$filter['group'] = $old_filter['group'];
	}

	//Select to change the filter's group
	$filter_groups = '<select name="filter_group" class="input-sm form-control" onchange="sendReq(\''.$ko_path.'/tools/inc/ajax.php\', \'action,id,group,sesid\', \'setfiltergroup,'.$filter['id'].',\'+this.options[this.selectedIndex].value+\','.session_id().'\', do_element);">';
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
  global $ko_path, $default_lang, $BASE_PATH;

  if($edit_lang != 'de') $default_lang = 'de';
  $csv = array();

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

    $csv[] = array($d_i, $d_v);
  }
  print '</table>';
  if($found) print '<div align="center"><input type="submit" name="submit" onclick="set_hidden_value('."'id', '$edit_lang');set_action('ll_edit_submit'".');" value="'.getLL("save").'" />';

  $header = array('key', $default_lang, $edit_lang);
  $filename = $BASE_PATH.'download/excel/ll_'.date('YmdHis').'.csv';
  ko_export_to_csv($header, $csv, $filename);
  print '<p><a href="/download/excel/'.basename($filename).'" target="_blank">CSV-Download</a></p>';
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
*  (c) 2003-2020 Renzo Lauper (renzo@churchtool.org)
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



function ko_tools_plugins_list() {
	global $ko_path, $smarty, $PLUGIN_CONF, $UPDATER_CONF;

	$plugins_available = ko_tools_plugins_get_available();
	$plugins_installed = ko_tools_plugins_get_installed($plugins_available);

	$rows = sizeof($plugins_available);

	$manual_access = array('add' => array(), 'remove' => array());

	require_once($ko_path.'inc/class.dbStructUpdater.php');
	$updater = new dbStructUpdater($UPDATER_CONF);

	$es = array();
	foreach ($plugins_available as $plugin) {
		//Conf-Datei einlesen
		$hidden = FALSE;
		$conf_file = $ko_path."plugins/".$plugin."/config.php";
		if(!file_exists($conf_file)) $hidden = TRUE;
		else include($conf_file);
		$installed = in_array($plugin, $plugins_installed);

		$link1 = $hidden?"":'<a href="?action=plugins_'.($installed?"delete":"install").'&amp;id='.$plugin.'">';
		$link2 = $hidden?"":'</a>';
		$pre  = '<div class="'.($installed?"plugins_installed":"").($hidden?" plugins_hidden":"").'">';
		$post = '</div>';

		if ($installed && file_exists($ko_path."plugins/".$plugin."/db.sql")) {
			$sqlDiffs = $updater->getAllSQL(file_get_contents($ko_path."plugins/".$plugin."/db.sql"));
			$sqlDiffs = array_merge($sqlDiffs['inserts'], array_merge($sqlDiffs['updates'], $sqlDiffs['alters']));
			if (sizeof($sqlDiffs) > 0) $sqlDiffsHtml = "<a href=\"?action=plugins_show_sql_diffs&plugin={$plugin}\" ".ko_get_tooltip_code(implode("<br>---------------------------------------------<br>", $sqlDiffs))."><i class=\"fa fa-refresh\"></i></a>";
			else $sqlDiffsHtml = '';
		} else {
			$sqlDiffsHtml = '';
		}

		//Find update scripts
		$updates = db_select_data('ko_updates', "WHERE `plugin` = '$plugin' AND `status` = '0'");
		if(sizeof($updates) > 0) {
			$desc = '<h4>UPDATES:</h4>';
			foreach($updates as $update) {
				$desc .= '<p><b>'.$update['name'].'</b>: '.$update['description'].'</p>';
			}
			$updatesHtml = "<a href=\"?action=plugins_show_updates&plugin={$plugin}\" ".ko_get_tooltip_code($desc)."><i class=\"fa fa-wrench\"></i></a>";
		} else {
			$updatesHtml = '';
		}

		$es[$plugin] = array(
			'id' => $plugin,
			'sql_diffs' => $sqlDiffsHtml,
			'updates' => $updatesHtml,
			'key' => $link1.$pre.ko_html($plugin).$post.$link2,
			'title' => ko_html($PLUGIN_CONF[$plugin]["title"]),
			'description' => ko_html($PLUGIN_CONF[$plugin]["description"]),
			'dependencies' => ko_html($PLUGIN_CONF[$plugin]["dependencies"]),
			'version' => ko_html($PLUGIN_CONF[$plugin]["version"]),
			'status' => ko_html($PLUGIN_CONF[$plugin]["state"]),
			'installed' => $installed ? '1' : '0',
		);

		$manual_access['add'][$plugin] = $installed ? FALSE : TRUE;
		$manual_access['remove'][$plugin] = $installed ? TRUE : FALSE;
	}

	$list = new kOOL_listview();

	$list->init('tools', 'ko_plugins', array('add', 'remove'), 1, sizeof($es));
	$list->setTitle(getLL("tools_plugins"));
	$list->disableMultiedit();
	$list->setManualAccess('add', $manual_access['add']);
	$list->setManualAccess('remove', $manual_access['remove']);
	$list->setActions(
		array(
			'add' => array('action' => 'plugins_install'),
			'remove' => array('action' => 'plugins_delete', 'confirm' => TRUE)
		)
	);
	$list->setStats($rows, '', '', '', TRUE);
	$list->setSort(FALSE);
	$list->setRowClass('row-success', 'return (INSTALLED == 1);');

	//Output the list
	$list->render($es);
}//ko_tools_plugins_list()



function ko_tools_list_sql_diffs($plugin) {
	global $ko_path, $UPDATER_CONF;

	require_once($ko_path.'inc/class.dbStructUpdater.php');
	$updater = new dbStructUpdater($UPDATER_CONF);

	$sqlDiffs = $updater->getAllSQL(file_get_contents($ko_path."plugins/".$plugin."/db.sql"));
	$sqlDiffs = array_merge($sqlDiffs['inserts'], array_merge($sqlDiffs['updates'], $sqlDiffs['alters']));

	$html = "<h3>SQL Differences for Plugin {$plugin}</h3>";
	$html .= "<table class=\"table table-condensed\"><tbody>";
	$html .= '<table><tr><td style="vertical-align:top;text-align:right;"><a href="#" onclick="$(\'input[type=&quot;checkbox&quot;]\').each(function(){$(this).prop(\'checked\', !$(this).prop(\'checked\'));});return false;"><i class="fa fa-check-square-o"></i></a></td><td></td></tr>';
	foreach ($sqlDiffs as $sqlDiff) {
		$html .= '<tr>';
		$html .= '<td style="vertical-align:top;text-align:right"><input type="checkbox" checked="checked" name="'.md5($sqlDiff).'"></td>';
		$html .= '<td style="vertical-align:top;text-align:left">'.$sqlDiff.'</td>';
		$html .= '</tr>';
	}
	$html .= '</tbody></table>';
	$html .= '<div class="btn-field"><button class="btn btn-primary" type="submit" onclick="$(\'input[name=&quot;id&quot;]\').val(\''.$plugin.'\');set_action(\'submit_plugins_sql_diffs\', this);">OK&nbsp;<i class="fa fa-check"></i></button>';

	print $html;
}




function ko_tools_list_updates_for_plugin($plugin) {
	$updates = db_select_data('ko_updates', "WHERE `plugin` = '$plugin' AND `status` = '0'");

	$html = "<h3>Updates for Plugin {$plugin}</h3>";
	$html .= "<table class=\"table table-condensed\"><tbody>";
	$html .= '<table><tr><td style="vertical-align:top;text-align:right; padding-right: 10px; padding-bottom: 8px;"><a href="#" onclick="$(\'input[type=&quot;checkbox&quot;]\').each(function(){$(this).prop(\'checked\', !$(this).prop(\'checked\'));});return false;"><i class="fa fa-check-square-o"></i></a></td><td></td></tr>';
	foreach($updates as $update) {
		$html .= '<tr>';
		$html .= '<td style="vertical-align:top;text-align:right; padding-right: 10px;"><input type="checkbox" checked="checked" name="'.$update['name'].'"></td>';
		$html .= '<td style="vertical-align:top;text-align:left"><b>'.$update['name'].'</b><br />'.$update['description'].'</td>';
		$html .= '</tr>';
	}
	$html .= '</tbody></table>';
	$html .= '<div class="btn-field"><button class="btn btn-primary" type="submit" onclick="$(\'input[name=&quot;id&quot;]\').val(\''.$plugin.'\');set_action(\'submit_plugins_updates\', this);">OK&nbsp;<i class="fa fa-check"></i></button>';

	print $html;
}//ko_tools_list_updates_for_plugin()



function ko_list_updates() {
	$rows = db_get_count('ko_updates', 'id');
	$es = db_select_data('ko_updates', '', '*', 'ORDER BY `status` ASC, `crdate` DESC');

	$list = new kOOL_listview();

	$list->init('tools', 'ko_updates', array('chk', 'check'), 1, sizeof($es));
	$list->setTitle(getLL('tools_updates_list_title'));
	$list->setAccessRights(FALSE);
	$list->setActions(
		array(
			'check' => array('action' => 'call_update', 'confirm' => TRUE),
		)
	);
	$list->disableMultiedit();
	$list->setStats($rows, '', '', '', TRUE);
	$list->setSort(FALSE);
	$list->setRowClass("row-success", 'return STATUS == 0;');

	//Output the list
	$list->render($es);
}//ko_list_updates()




function ko_list_tasks() {
	$rows = db_get_count('ko_scheduler_tasks', 'id');
	$es = db_select_data('ko_scheduler_tasks', '', '*', 'ORDER BY name ASC');

	$list = new kOOL_listview();

	$list->init('tools', 'ko_scheduler_tasks', array('chk', 'edit', 'delete'), 1, sizeof($es));
	$list->setTitle(getLL('tools_scheduler_tasks_list_title'));
	$list->setAccessRights(FALSE);
	$list->setActions(array('edit' => array('action' => 'edit_task'),
			'delete' => array('action' => 'delete_task', 'confirm' => TRUE))
	);
	$list->disableMultiedit();
	$list->setStats($rows, '', '', '', TRUE);
	$list->setSort(TRUE, 'setsort', $_SESSION['sort_tasks'], $_SESSION['sort_tasks_order']);
	$list->setRowClass("ko_list_hidden", 'return STATUS == 0;');

	//Output the list
	$list->render($es);
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
	global $smarty, $ko_path, $BASE_PATH;

	$gc = $rowcounter = $colCounter = 0;

	$groups[$gc]['titel'] = getLL('tools_typo3_connection_title');
	$fields = array('typo3_host', 'typo3_db', 'typo3_user', 'typo3_pwd');
	$cc = 0;
	$setting = array();
	foreach($fields as $field) {
		$setting[$cc]['desc'] = getLL('tools_typo3_connection_'.$field);
		$setting[$cc]['type'] = 'text';
		$setting[$cc]['name'] = $field;

		if($field == 'typo3_pwd') {
			$pwd_enc = ko_get_setting('typo3_pwd');
			if($pwd_enc != '') {
				require_once($BASE_PATH.'inc/class.openssl.php');
				$crypt = new openssl('AES-256-CBC');
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

	foreach ($setting as $s) {
		$groups[$gc]['row'][$rowcounter]['inputs'][$colCounter] = $s;

		$colCounter++;
		if ($colCounter > 1) {
			$rowcounter ++;
			$colCounter = 0;
		}
	}

	$smarty->assign('tpl_titel', getLL('tools_typo3_connection'));
	$smarty->assign('tpl_groups', $groups);
	$smarty->assign('tpl_submit_value', getLL('save'));
	$smarty->assign('tpl_action', 'submit_typo3_connection');
	$smarty->display('ko_formular.tpl');
}//ko_tools_typo3_connection()


/**
 * Wrapper function to call miscellaneous small scripts to perform cleanups
 * Implements Hook functions from tools/inc/clean.inc containing ko_tools_cleanup_
 * and search for functions like my_tools_cleanup_" . $plugin["name"] in Tools-Plugins.
 */
function ko_tools_misc() {
	global $PLUGINS, $notifier;

	include_once("cleanup.inc");
	$functions = get_defined_functions();
	$call_function = FALSE;
	$sel_cleanup_functions = '<select name="call" class="input-sm form-control">';

	foreach($functions['user'] AS $function) {
		if (substr($function,0,17) == 'ko_tools_cleanup_') {
			if ($_POST['call'] == $function) $call_function = $_POST['call'];
			$sel_cleanup_functions.= '<option value="' . $function . '">' . getLL($function) . '</option>';
		}
	}

	$hooks = hook_include_main("tools");
	foreach($hooks as $hook) include_once($hook);
	foreach($PLUGINS as $plugin) {
		$function = "my_tools_cleanup_" . $plugin["name"];
		if(function_exists($function)) {
			if ($_POST['call'] == $function) $call_function = $_POST['call'];
			$sel_cleanup_functions.= '<option value="' . $function . '">' . getLL($function) . '</option>';
		}
	}

	$sel_cleanup_functions.= "</select>";

	$c = '<h1>Cleanup</h1>
	<div class="row">
	  <div class="col-md-6">
    	<div class="input-group">
			' . $sel_cleanup_functions . '
		  <span class="input-group-btn">
			<button type="submit" class="btn btn-default" onclick="set_action(\'misc\', this)" >Go!</button>
		  </span>
		</div>
	  </div>
	</div></form><br />';

	if($call_function !== false) {
		$c.= '<br /><div class="panel panel-primary">
  			<div class="panel-heading">' . getLL($call_function) . '</div>
  			<div class="panel-body">';
		$c.= call_user_func($call_function);
		$c.= '</div></div>';
	}

	if ($notifier->hasNotifications(koNotifier::ALL)) {
		$notifier->notify();
	}


	print $c;
}



function ko_tools_kota_fields() {
	global $KOTA, $smarty;

	ko_include_kota('_all');

	$activeTable = $_SESSION['tools_kota_fields_table'];
	if (!$activeTable) $activeTable = 'ko_leute';

	$allTables = array_keys($KOTA);
	sort($allTables);
	$tableSelectInput = array(
		'type' => 'select',
		'name' => 'table_select',
		'value' => $activeTable,
		'values' => $allTables,
		'descs' => $allTables,
		'params' => ' onchange="sendReq(\'../tools/inc/ajax.php\', [\'action\', \'table\', \'sesid\'], [\'selkotatable\', $(this).val(), kOOL.sid], do_element);"',
	);

	$kotaListFields = isset($KOTA[$activeTable]['_listview']) ? ko_array_column($KOTA[$activeTable]['_listview'], 'name') : [];
	$formLayout = $KOTA[$activeTable]['_form_layout'];
	$dbFields_ = db_get_columns($activeTable);
	$dbFields = array();
	$noKotaListFields = $noKotaFormFields = array();
	foreach ($dbFields_ as $dbField_) {
		$dbField = $dbField_['Field'];
		$dbFields[] = $dbField;

		if (!in_array($dbField, $kotaListFields)) $noKotaListFields[] = $dbField;
		if (!is_array($KOTA[$activeTable][$dbField]['form'])) $noKotaFormFields[] = $dbField;
	}

	sort($noKotaFormFields);
	sort($noKotaListFields);

	$smarty->assign('active_table', $activeTable);
	$smarty->assign('form_layout', $formLayout);
	$smarty->assign('no_kota_form_fields', implode(', ', $noKotaFormFields));
	$smarty->assign('no_kota_list_fields', implode(', ', $noKotaListFields));
	$smarty->assign('table_select_input', $tableSelectInput);
	$smarty->display('ko_tools_kota_fields.tpl');
}


?>
