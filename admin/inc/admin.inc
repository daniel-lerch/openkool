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

require_once($BASE_PATH."inc/class.kOOL_listview.php");


function ko_set_logins_list($output=TRUE) {
	global $smarty, $ko_path;
	global $access;

	if($access['admin']['MAX'] < 5) return FALSE;

	if($_SESSION["ses_username"] != "root") $z_where = " AND `login` != 'root' ";
	else $z_where = "";
	$rows = db_get_count("ko_admin", "id", $z_where);
	if($_SESSION['show_start'] > $rows) $_SESSION['show_start'] = 1;
	$z_limit = 'LIMIT '.((int)$_SESSION['show_start']-1).', '.$_SESSION['show_limit'];
	ko_get_logins($login, $z_where, $z_limit);

	//Statistik über Suchergebnisse und Anzeige
  $stats_end = ($_SESSION["show_limit"]+$_SESSION["show_start"]-1) > $rows ? $rows : ($_SESSION["show_limit"]+$_SESSION["show_start"]-1);
	$smarty->assign("tpl_stats", $_SESSION["show_start"]." - ".$stats_end." ".getLL("list_oftotal")." ".$rows);

	//Links für Prev und Next-Page vorbereiten
  if($_SESSION["show_start"] > 1) {
		$smarty->assign("tpl_prevlink_link", "javascript:sendReq('../admin/inc/ajax.php', 'action,set_start,sesid', 'setstart,".(($_SESSION["show_start"]-$_SESSION["show_limit"] < 1) ? 1 : ($_SESSION["show_start"]-$_SESSION["show_limit"])).",".session_id()."', do_element);");
  } else {
    $smarty->assign('tpl_prevlink_link', '');
  }
  if(($_SESSION["show_start"]+$_SESSION["show_limit"]-1) < $rows) {
		$smarty->assign("tpl_nextlink_link", "javascript:sendReq('../admin/inc/ajax.php', 'action,set_start,sesid', 'setstart,".($_SESSION["show_limit"]+$_SESSION["show_start"]).",".session_id()."', do_element);");
  } else {
    $smarty->assign('tpl_nextlink_link', '');
  }
	$smarty->assign('limitM', $_SESSION['show_limit'] >= 100 ? $_SESSION['show_limit']-50 : max(10, $_SESSION['show_limit']-10));
	$smarty->assign('limitP', $_SESSION['show_limit'] >= 50 ? $_SESSION['show_limit']+50 : $_SESSION['show_limit']+10);


	//Header-Informationen
	$smarty->assign("tpl_show_3cols", TRUE);

	$show_cols = array(getLL("admin_logins_login"), getLL("admin_logins_status"), getLL("admin_logins_modules"), getLL("admin_logins_admingroups"), getLL("admin_logins_person"));
	$sort_cols = array('login', 'MODULEdisabled', 'modules', 'admingroups', 'MODULEleute_id');
  foreach($show_cols as $c_i => $c) {
    $tpl_table_header[$c_i]['sort'] = $sort_cols[$c_i];
    $tpl_table_header[$c_i]["name"] = $show_cols[$c_i];
  }
  $smarty->assign("tpl_table_header", $tpl_table_header);
  $smarty->assign('sort', array('show' => TRUE,
																'action' => 'setsortlogins',
																'akt' => $_SESSION['sort_logins'],
																'akt_order' => $_SESSION['sort_logins_order'])
	);
	$smarty->assign('module', 'admin');
	$smarty->assign('sesid', session_id());


	//Logins in Liste einfüllen
	$guest_id = (int)ko_get_guest_id();
	$root_id = (int)ko_get_root_id();
	foreach($login as $l_i => $l) {
		//root-Login nur anzeigen, wenn root eingeloggt ist
		if($l["login"] == "root" && $_SESSION["ses_username"] != "root") continue;

		//Disabled login
		$tpl_list_data[$l_i]["rowclass"] = $l["disabled"] ? "ko_list_hidden" : "";

		//Checkbox
		$tpl_list_data[$l_i]["show_checkbox"] = TRUE;
							
		//Edit-Button
		$tpl_list_data[$l_i]["show_edit_button"] = TRUE;
		$tpl_list_data[$l_i]["alt_edit"] = getLL("admin_edit_login");
		$tpl_list_data[$l_i]["onclick_edit"] = "javascript:set_action('edit_login');set_hidden_value('id', '$l_i');this.submit";
									
		//Delete-Button
		if((int)$l_i == $guest_id) {  //ko_guest darf nicht gelöscht werden
			$tpl_list_data[$l_i]["show_delete_button"] = FALSE;
		} else {
			$tpl_list_data[$l_i]["show_delete_button"] = TRUE;
			$tpl_list_data[$l_i]["alt_delete"] = getLL("admin_del_login");
			$tpl_list_data[$l_i]["onclick_delete"] = "javascript:c = confirm('".getLL("admin_del_login_confirm")."');if(!c) return;set_action('delete_login');set_hidden_value('id', '$l_i');";
		}

		//Index
		$tpl_list_data[$l_i]["id"] = $l_i;

		//Login-Name
		if($_SESSION["ses_userid"] == $root_id) {
			$link = $ko_path."admin/index.php?action=login_details&amp;id=".$l_i;
			$tpl_list_data[$l_i][1] = '<b><a href="'.$link.'">'.ko_html($l["login"]).'</a></b>';
		} else {
			$tpl_list_data[$l_i][1] = '<b>'.ko_html($l["login"]).'</b>';
		}

		//disable/enable (not for root, ko_guest or logged in login
		if($l_i != $guest_id && $l_i != $root_id && $l_i != $_SESSION['ses_userid']) {
			if($l["disabled"]) {
				$html = '<input type="image" src="'.$ko_path.'images/icon_login_disable.png" onclick="sendReq(\'../admin/inc/ajax.php\', \'action,mode,id,sesid\', \'ablelogin,enabled,'.$l_i.','.session_id().'\', do_element); return false;" />';
			} else {
				$html = '<input type="image" src="'.$ko_path.'images/icon_login_enable.png" onclick="sendReq(\'../admin/inc/ajax.php\', \'action,mode,id,sesid\', \'ablelogin,disabled,'.$l_i.','.session_id().'\', do_element); return false;" />';
			}
		} else {
			$html = '&nbsp;';
		}
		$tpl_list_data[$l_i][2] = $html;

		//Module
		$value = array();
		foreach(explode(",", $l["modules"]) as $module) {
			$value[] = getLL("module_".$module);
		}
		$tpl_list_data[$l_i][3] = ko_html(implode(", ", $value));

		//Admingroups
		$value = array();
		$all_admingroups = ko_get_admingroups($l["id"]);
		foreach(explode(",", $l["admingroups"]) as $ag) {
			$value[] = $all_admingroups[$ag]["name"];
		}
		$tpl_list_data[$l_i][4] = ko_html(implode(", ", $value));

		//assigned person
		if($l["leute_id"] < 1) {
			if($l['email']) {
				$value = '('.ko_html($l['email']).')';
			} else {
				$value = "-";
			}
		} else {
			ko_get_person_by_id($l["leute_id"], $p);
			$value = $p["vorname"]." ".$p["nachname"];
			if($l['email']) $value .= ' ('.ko_html($l['email']).')';
			//Add link to person
			$value = '<a href="'.$ko_path.'leute/index.php?action=set_idfilter&amp;id='.$p['id'].'">'.$value.'</a>';
		}
		$tpl_list_data[$l_i][5] = $value;
		

	}//foreach(login)

	$list_footer = $smarty->get_template_vars('list_footer');
	$list_footer[] = array("label" => getLL("admin_change_user_label"),
												 "button" => '<input type="submit" onclick="set_action(\'sudo_login\');" value="'.getLL("admin_change_user").'" />');
	$smarty->assign("show_list_footer", TRUE);
	$smarty->assign("list_footer", $list_footer);

	$smarty->assign("help", ko_get_help("admin", "show_logins"));

	$smarty->assign("tpl_list_title", getLL("admin_logins_list_title"));
	$smarty->assign('tpl_list_cols', array(1, 2, 3, 4, 5));
  $smarty->assign('tpl_list_data', $tpl_list_data);
	if($output) {
		$smarty->display('ko_list.tpl');
	} else {
		print $smarty->fetch('ko_list.tpl');
	}
}//ko_set_logins_list()




function ko_list_admingroups($output=TRUE) {
	global $smarty;
	global $access;

	if($access['admin']['MAX'] < 5) return FALSE;

	//Add KOTA filter
	$kota_where = kota_apply_filter('ko_admingroups');
	if($kota_where != '') $z_where .= " AND ($kota_where) ";

	$rows = db_get_count('ko_admingroups', 'id', $z_where);
	$es = db_select_data('ko_admingroups', 'WHERE 1 '.$z_where, '*', 'ORDER BY `name` ASC');
	//Set fake column logins so kota_process_data() will process it
	foreach($es as $k => $v) {
		$es[$k]['logins'] = '';
	}

	$list = new kOOL_listview();

	$list->init('admin', 'ko_admingroups', array('chk', 'edit', 'delete'), 1, 1000);
	$list->setTitle(getLL('admin_admingroups_list_title'));
	$list->setAccessRights(FALSE);
	$list->setActions(array('edit' => array('action' => 'edit_admingroup'),
													'delete' => array('action' => 'delete_admingroup', 'confirm' => TRUE))
										);
	$list->setSort(FALSE);
	$list->setStats($rows, '', '', '', TRUE);
	$list->disableMultiedit();

	$list->setWarning(kota_filter_get_warntext('ko_admingroups'));

	//Footer
	$list_footer = $smarty->get_template_vars('list_footer');
	$list->setFooter($list_footer);

	//Output the list
	if($output) {
		$list->render($es);
	} else {
		print $list->render($es);
	}
}//ko_list_admingroups()





function ko_show_logs($output=TRUE) {
	global $access;
	global $smarty;

	if($access['admin']['MAX'] < 4) return;

	$z_where = $z_limit = "";

	//Type-Filter setzen
	$z_where_add = "";
	if($_SESSION["log_type"]) {
		$z_where_add .= "`type`='".$_SESSION["log_type"]."'";
	}
	if($z_where_add) $z_where .= " AND ( " . $z_where_add . " ) ";

	//User-Filter setzen
	$z_where_add = "";
	if($_SESSION["log_user"] > 0) {
		$z_where_add = " `user_id`='".$_SESSION["log_user"]."' ";
	}
	if($z_where_add) $z_where .= " AND ( " . $z_where_add . " ) ";

	//Time-Filter setzen
	if($_SESSION['log_time'] > 0) $z_where_add = '(TO_DAYS(CURDATE()) - TO_DAYS(`date`)) < '.(int)$_SESSION['log_time'];
	if($z_where_add) $z_where .= " AND ( " . $z_where_add . " ) ";

	//Guest aus- oder einblenden
	if($_SESSION["logs_hide_guest"]) {
		$z_where .= " AND `type` != 'guest' ";
	}

	//root nur für root anzeigen
	if($_SESSION["ses_username"] != "root") {
		$z_where .= " AND `user_id` != '".ko_get_root_id()."' ";
	}

	//Add KOTA filter
	$kota_where = kota_apply_filter('ko_log');
	if($kota_where != '') $z_where .= " AND ($kota_where) ";


	//Limit-Filter setzen
	if($_SESSION['show_logs_start'] && $_SESSION['show_logs_limit']) {
		$z_limit = 'LIMIT '.($_SESSION['show_logs_start']-1).', '.$_SESSION['show_logs_limit'];
	}

	$order = 'ORDER BY '.$_SESSION['sort_logs'].' '.$_SESSION['sort_logs_order'];


	$rows = db_get_count('ko_log', 'id', $z_where);
	$es = db_select_data('ko_log', 'WHERE 1 '.$z_where, '*', $order, $z_limit);

	$list = new kOOL_listview();

	$list->init('admin', 'ko_log', '', $_SESSION['show_logs_start'], $_SESSION['show_logs_limit']);
	$list->setTitle(getLL('admin_log_list_title'));
	$list->setAccessRights(FALSE);
	$list->setSort(TRUE, 'setsortlog', $_SESSION['sort_logs'], $_SESSION['sort_logs_order']);
	$list->setStats($rows);
	$list->disableMultiedit();

	$list->setWarning(kota_filter_get_warntext('ko_log'));

	//Output the list
	if($output) {
		$list->render($es);
	} else {
		print $list->render($es);
	}
}//ko_show_logs()





function ko_show_sms_log($output=TRUE) {
	global $access, $smarty;

	if($access['admin']['MAX'] < 1) return false;
	if(!ko_module_installed('sms')) return false;

	$z_where = "AND (`type` = 'sms_sent' OR `type` = 'sms_mark')";
	//Apply filters
	if($_SESSION['log_user'] > 0) $z_where .= " AND `user_id`='".$_SESSION['log_user']."' ";
	if($_SESSION['log_time'] > 0) $z_where .= ' AND (TO_DAYS(CURDATE()) - TO_DAYS(`date`)) < '.(int)$_SESSION['log_time'].' ';
	//show root actions only to the root user
	if($_SESSION['ses_username'] != 'root') $z_where .= " AND `user_id` != '".ko_get_root_id()."' ";
	//For users with access level < 4 only show their own messages
	if($access['admin']['MAX'] < 4) {
		$z_where .= " AND `user_id` = '".$_SESSION['ses_userid']."' ";
	}

	$z_limit = 'LIMIT '.($_SESSION['show_start']-1).', '.$_SESSION['show_limit'];

	$rows = db_get_count("ko_log", "id", $z_where);
	$es = db_select_data('ko_log', 'WHERE 1=1 '.$z_where, '*', 'ORDER BY date DESC', $z_limit);

	ko_get_logins($logins);

	$logs = array();
	$c = 0;
	foreach($es as $log) {
		$logs[$c]['date'] = strftime($GLOBALS['DATETIME']['dmY'].' %H:%M', sql2timestamp($log['date']));
		$logs[$c]['user_id'] = $logins[$log['user_id']]['login'].' ('.$log['user_id'].')';

		if($log['type'] == 'sms_mark') {
			$logs[$c]['credits'] = '';
			$logs[$c]['ratio'] = '';
			$logs[$c]['numbers'] = '';
			$logs[$c]['text'] = '--- MARK ---';
		} else {
			$parts = explode(' - ', $log['comment']);
			$credits = array_pop($parts);
			$problems = array_pop($parts);
			$ratio = array_pop($parts);
			$numbers = explode(', ', array_pop($parts));
			$text = implode(' - ', $parts);

			$logs[$c]['credits'] = $credits;
			$logs[$c]['ratio'] = $ratio;
			$logs[$c]['numbers'] = '<span onmouseover="tooltip.show(\''.implode(', ', $numbers).'\', 500, \'b\');" onmouseout="tooltip.hide();">'.sizeof($numbers).'</span>';
			$logs[$c]['text'] = $text;
		}

		$c++;
	}

	$list = new kOOL_listview();

	$list->init('admin', '_ko_sms_log', array(), $_SESSION['show_start'], $_SESSION['show_limit']);
	$list->setTitle(getLL("admin_sms_log_list_title"));
	$list->setAccessRights(FALSE);
	$list->disableMultiedit();
	$list->disableKotaProcess();
	$list->setSort(FALSE);
	$list->setStats($rows);

	//Footer
	$sum_rec = $sum_credits = array();
	$sum_rec['total'] = $sum_credits['total'] = 0;
	$ratio_done = $ratio_total = 0;

	$mark_total = $mark_done = $mark_credits = 0;
	$marks = array();

	$all = db_select_data('ko_log', 'WHERE 1=1 '.$z_where, '*', 'ORDER BY date DESC');
	foreach($all as $log) {
		if($log['type'] == 'sms_mark') {
			$mark_est = round($mark_credits*($mark_total/$mark_done), 1);
			$marks[$log['date']] = $mark_credits.' ('.$mark_done.' / '.$mark_total.') &rarr; <b>'.$mark_est.'</b>';
			$mark_total = $mark_done = $mark_credits = 0;
		}else {
			$parts = explode(' - ', $log['comment']);
			$credits = array_pop($parts);
			$problems = array_pop($parts);
			$ratio = array_pop($parts);
			$numbers = explode(', ', array_pop($parts));

			$sum_rec['total'] += sizeof($numbers);
			$sum_credits['total'] += $credits;
			$sum_rec[$log['user_id']] += sizeof($numbers);
			$sum_credits[$log['user_id']] += $credits;
			list($done, $total) = explode('/', $ratio);
			$ratio_done += $done;
			$ratio_total += $total;

			$mark_credits += $credits;
			$mark_done += $done;
			$mark_total += $total;
		}
	}
	//Add number from last marker to the start of time
	$mark_est = round($mark_credits*($mark_total/$mark_done), 1);
	$marks['1900-01-01 00:00:00'] = $mark_credits.' ('.$mark_done.' / '.$mark_total.') &rarr; <b>'.$mark_est.'</b>';

	$estimate = round($sum_credits['total']*($ratio_total/$ratio_done), 1);
	//Footer entry with stats over all sms
	$list_footer = $smarty->get_template_vars('list_footer');
	$list_footer[] = array('label' => sprintf(getLL('admin_sms_log_total'), $sum_rec['total'], ($ratio_done.'/'.$ratio_total), $sum_credits['total'], $estimate));
	//Add stats for each single user
	if(sizeof($sum_rec) > 1 && !$_SESSION['log_user']) {
		arsort($sum_rec);
		$user_texts = array();
		foreach($sum_rec as $k => $v) {
			if($k == 'total') continue;
			$user = isset($logins[$k]) ? $logins[$k]['login'] : '<span style="text-decoration:line-through;">'.getLL('admin_deleted_user').'</span>';
			$user_texts[] = sprintf(getLL('admin_sms_log_total_user'), $user.' ('.$k.')', $v, $sum_credits[$k]);
		}
		$list_footer[] = array('label' => implode('<br />&nbsp;', $user_texts));
	}

	//Add possibility for root user to add marks
	if($_SESSION['ses_userid'] == ko_get_root_id()) {
		//Show stats between all markers
		$last = date('Y-m-d');
		$mark_stats = '';
		foreach($marks as $date => $mark) {
			$mark_stats .= '<br />'.substr($date, 0, 10).' - '.$last.': '.$mark;
			$last = substr($date, 0, 10);
		}
		$list_footer[] = array('label' => $mark_stats, 'button' => '');

		//Button to add new marker
		$list_footer[] = array('label' => getLL('admin_sms_log_mark_label'), 'button' => '<input type="submit" name="sms_mark" onclick="set_action(\'sms_log_mark\');" value="'.getLL('OK').'" />');
	}

	$list->setFooter($list_footer);


	if($output) {
		$list->render($logs);
	} else {
		print $list->render($logs);
	}
}//ko_show_sms_log()






function ko_login_formular($mode, $id=0, $type="login") {
	global $smarty, $ko_path, $MODULES, $MODULES_GROUP_ACCESS, $BASE_PATH;
	global $access, $all_groups, $KOTA;

	if($access['admin']['MAX'] < 5) return false;

	//root darf nur von root bearbeitet werden
	if($type == "login" && ($id == ko_get_root_id() && $_SESSION["ses_username"] != "root")) return false;

	$admingroups = ko_get_admingroups();
	$status = ""; $hide_password = FALSE;
	$logins_values = $logins_output = array();
	if($mode == "edit" && $id != 0) {
		if($type == "admingroup") {
			//Name
			$_POST["txt_name"] = $admingroups[$id]["name"];

			//Module-Doubleselect vorbereiten
			$user_modules = explode(",", $admingroups[$id]["modules"]);
			$modules_values = $modules_descs = $modules_avalues = $modules_adescs = array();
			foreach($MODULES as $m_i => $m) {
				if($m == 'tools') continue;

				$modules_values[] = $m;
				$modules_descs[] = getLL("module_".$m);
				if(in_array($m, $user_modules)) {
					$modules_avalues[] = $m;
					$modules_adescs[] = getLL("module_".$m);
				}
			}
		} else {
			ko_get_login($id, $login);
			$_POST["txt_name"] = $login["login"];
			$_POST['txt_email'] = $login['email'];
			$_POST['txt_mobile'] = $login['mobile'];
			$user_modules = explode(",", $login["modules"]);
			//Module-Doubleselect vorbereiten
			$modules_values = $modules_descs = $modules_avalues = $modules_adescs = array();
			foreach($MODULES as $m_i => $m) {
				if($m == 'tools' && $id != ko_get_root_id()) continue;

				$modules_values[] = $m;
				$modules_descs[] = getLL("module_".$m);
				if(in_array($m, $user_modules)) {
					$modules_avalues[] = $m;
					$modules_adescs[] = getLL("module_".$m);
				}
			}
			//show status
			if($login["disabled"]) {
				$status = '<img src="'.$ko_path.'images/icon_login_disable.png" border="0" />';
				//hide password change when login is disabled
				$hide_password = TRUE;
			} else {
				$status  = '<img src="'.$ko_path.'images/icon_login_enable.png" border="0" />';
			}
			//admingroups
			$admingroups_values = $admingroups_descs = $admingroups_avalues = $admingroups_adescs = array();
			foreach($admingroups as $g) {
				$admingroups_descs[] = $g["name"];
				$admingroups_values[] = $g["id"];
				if(in_array($g["id"], explode(",", $login["admingroups"]))) {
					$admingroups_adescs[] = $g["name"];
					$admingroups_avalues[] = $g["id"];
				}
			}
		}
	}
	else if($mode == "neu") {
		if($type == "admingroup") {
			//Module-Doubleselect vorbereiten
			foreach($MODULES as $m_i => $m) {
				if($m == 'tools') continue;
				//Module zur Auswahl anzeigen
				$modules_values[] = $m;
				$modules_descs[] = getLL("module_".$m);
			}
		} else {
			//Module-Doubleselect vorbereiten
			foreach($MODULES as $m_i => $m) {
				if($m == 'tools') continue;
				//Module zur Auswahl anzeigen
				$modules_values[] = $m;
				$modules_descs[] = getLL("module_".$m);
			}

			//Logins anzeigen, von denen Berechtigungen oder Userprefs kopiert werden können
			if($_SESSION["ses_username"] != "root") $z_where = " AND `login` != 'root' "; else $z_where = "";
			$z_where .= " AND (`disabled` = '' OR `disabled` = '0')";
			ko_get_logins($logins, $z_where);
			$logins_values = array("");
			$logins_output = array("");
			foreach($logins as $l) {
				$logins_values[$l["id"]] = $l["id"];
				$logins_output[$l["id"]] = $l["login"];
			}
			//admingroups
			$admingroups_values = $admingroups_descs = $admingroups_avalues = $admingroups_adescs = array();
			foreach($admingroups as $g) {
				$admingroups_descs[] = $g["name"];
				$admingroups_values[] = $g["id"];
			}
		}
	} else {
		return FALSE;
	}

	//build form for login data
	$gc = 0;
	$rowcounter = 0;

	//Name and Status
	$group[$gc]["row"][$rowcounter]["inputs"][0] = array("desc" => $type == "login" ? getLL("admin_logins_name") : getLL("admin_admingroups_name"),
																 "type" => "text",
																 "name" => "txt_name",
																 "value" => ko_html($_POST["txt_name"]),
																 "params" => 'size="40" maxlength="40"',
															 	 "colspan" => 'colspan="3"',
																 );
	if($status && $type == "login") {
		$group[$gc]["row"][$rowcounter++]["inputs"][1] = array("desc" => getLL("admin_logins_status"),
																	 "type" => "html",
																	 "value" => $status,
																 	 "colspan" => 'colspan="3"',
																	 );
	} else {
		$rowcounter++;
	}

	//Password
	if(!$hide_password && $type == "login") {
		$group[$gc]["row"][$rowcounter]["inputs"][0] = array("desc" => getLL("admin_logins_new_password"),
																	 "type" => "password",
																	 "name" => "txt_pwd1",
																	 "value" => "",
																	 "params" => 'size="40" maxlength="40" autocomplete="off"',
																 	 "colspan" => 'colspan="3"',
																	 );
		$group[$gc]["row"][$rowcounter++]["inputs"][1] = array("desc" => getLL("admin_logins_new_password2"),
																	 "type" => "password",
																	 "name" => "txt_pwd2",
																	 "value" => "",
																	 "params" => 'size="40" maxlength="40" autocomplete="off"',
																 	 "colspan" => 'colspan="3"',
																	 );
	}

	//Copy rights/userprefs from login
	if(sizeof($logins_values) > 0 && $type == "login") {
		$group[$gc]["row"][$rowcounter]["inputs"][0] = array("desc" => getLL("admin_logins_copy_rights_from"),
																 "type" => "select",
																 "name" => "sel_copy_rights",
																 "values" => $logins_values,
																 "descs" => $logins_output,
																 "params" => 'size="0"',
																 "colspan" => 'colspan="3"',
																 );
		$group[$gc]["row"][$rowcounter++]["inputs"][1] = array("desc" => getLL("admin_logins_copy_settings_from"),
																 "type" => "select",
																 "name" => "sel_copy_userprefs",
																 "values" => $logins_values,
																 "descs" => $logins_output,
																 "params" => 'size="0"',
																 "colspan" => 'colspan="3"',
																 );
	}

	//Modules and admingroups
	$group[$gc]["row"][$rowcounter]["inputs"][0] = array("desc" => getLL("admin_logins_modules"),
																 "type" => "checkboxes",
																 "name" => "sel_modules",
																 "values" => $modules_values,
																 "descs" => $modules_descs,
																 "avalues" => $modules_avalues,
																 "avalue" => implode(",", $modules_avalues),
																 "size" => min(7, sizeof($modules_values)),
																 "colspan" => 'colspan="3"',
																 );
	if($type == "login") {
		$group[$gc]["row"][$rowcounter++]["inputs"][1] = array("desc" => getLL("admin_logins_admingroups"),
																	 "type" => "checkboxes",
																	 "name" => "sel_admingroups",
																	 "values" => $admingroups_values,
																	 "descs" => $admingroups_descs,
																	 "avalues" => $admingroups_avalues,
																	 "avalue" => implode(",", $admingroups_avalues),
																	 "size" => min(7, sizeof($admingroups_values)),
																	 "colspan" => 'colspan="3"',
																	 );
	} else {
		$rowcounter++;
	}

	//Assigned person and admin email
	if($type == 'login' && $id != ko_get_guest_id()) {
		if(ko_module_installed('leute')) {
			list($avalues, $adescs) = kota_peopleselect(array($login["leute_id"]));
			$group[$gc]["row"][$rowcounter]["inputs"][0] = array("desc" => getLL("admin_logins_leute_id"),
																		 "type" => "peoplesearch",
																		 "name" => "sel_leute_id",
																		 "avalues" => $avalues,
																		 "avalue" => $login["leute_id"],
																		 "adescs" => $adescs,
																		 "colspan" => 'colspan="3"',
																		 );
		}
		$group[$gc]['row'][++$rowcounter]['inputs'][0] = array('desc' => getLL('admin_logins_email'),
																	 "type" => "text",
																	 "name" => "txt_email",
																	 "value" => ko_html($_POST["txt_email"]),
																	 "params" => 'size="40" maxlength="255"',
																	 "colspan" => 'colspan="3"',
																	 );
		$group[$gc]['row'][$rowcounter]['inputs'][1] = array('desc' => getLL('admin_logins_mobile'),
																	 'type' => 'text',
																	 'name' => 'txt_mobile',
																	 'value' => ko_html($_POST['txt_mobile']),
																	 'params' => 'size="40" maxlength="255"',
																	 'colspan' => 'colspan="3"',
																	 );
	}

	/**
	  * Leute-Berechtigungen:
		*/
	$done_modules = array();
	if(in_array("leute", $user_modules)) {
		$done_modules[] = 'leute';
		if($mode == 'edit') $user_access = ko_get_access('leute', $id, TRUE, FALSE, $type, FALSE);
		$group[++$gc] = array("titel" => getLL("module_leute"), "state" => "open", "colspan" => 'colspan="6"');
		$help = ko_get_help("admin", "login_rights_leute");
		$group[$gc]["row"][$rowcounter++]["inputs"][0] = array("desc" => "",
																													 "type" => "html",
																													 "value" => $help["link"]."&nbsp;".getLL("admin_logins_rights_leute"),
																													 "colspan" => 'colspan="6"'
																													);
		$values = $descs = array(0, 1, 2, 3, 4);
		$group[$gc]["row"][$rowcounter]["inputs"][0] = array("desc" => strtoupper(getLL("all")),
																 "type" => "select",
																 "name" => "sel_rechte_leute",
																 "values" => $values,
																 "descs" => $descs,
																 "value" => $user_access['leute']['ALL'],
																 "params" => 'size="0"',
																 );


		//Stufen-Berechtigungen nach Filtern
		$filterset = array_merge((array)ko_get_userpref('-1', '', 'filterset', 'ORDER BY `key` ASC'), (array)ko_get_userpref($_SESSION['ses_userid'], '', 'filterset', 'ORDER BY `key` ASC'));
		$values_template[] = $descs_template[] = "";
		$values_template[] = '';
		$descs_template[] = '--- '.getLL('filter_filterpreset').' ---';
		foreach($filterset as $f) {
			$values_template[$f["key"]] = $f['key'];
			$descs_template[$f["key"]] = $f['user_id'] == '-1' ? getLL('itemlist_global_short').' '.$f["key"] : $f['key'];
		}
		//Add small groups to be selected directly for filters
		if(ko_module_installed('kg')) {
			$values_template[] = '';
			$descs_template[] = '--- '.getLL('kg_list_title').' ---';
			$smallgroups = db_select_data('ko_kleingruppen', 'WHERE 1', '*', 'ORDER BY `name` ASC');
			foreach($smallgroups as $sg) {
				$values_template['sg'.$sg['id']] = 'sg'.$sg['id'];
				$descs_template['sg'.$sg['id']] = $sg['name'];
			}
		}
		//Add groups to be selected directly for filters
		if(ko_module_installed('groups')) {
			ko_get_groups($all_groups);
			$groups_values = $groups_descs = array();
			$groups = ko_groups_get_recursive(ko_get_groups_zwhere());
			foreach($groups as $g) {
				//Full id including parent relationship
				$motherline = ko_groups_get_motherline($g['id'], $all_groups);
				$mids = array();
				foreach($motherline as $mg) {
					$mids[] = 'g'.$all_groups[$mg]['id'];
				}
				$groups_values[] = (sizeof($mids) > 0 ? implode(':', $mids).':' : '').'g'.$g['id'];

				//Name
				$desc = '';
				$depth = sizeof($motherline);
				for($i=0; $i<$depth; $i++) $desc .= '&nbsp;&nbsp;';
				$desc .= $g['name'];
				$groups_descs[] = $desc;
			}
			//add groups to select
			$values_template[] = '';
			$descs_template[] = '--- '.getLL('groups').' ---';
			$values_template = array_merge($values_template, $groups_values);
			$descs_template = array_merge($descs_template, $groups_descs);
		}

		//Create select for filters to be applied for this login/admingroup
		$laf = ko_get_leute_admin_filter($id, $type);
		for($i = 1; $i < 4; $i++) {
			$l_values[$i] = $values_template;
			$l_descs[$i] = $descs_template;
			if(isset($laf[$i])) {
				if(in_array($laf[$i]['value'], $values_template)) {  //Falls Filterset noch vorhanden, diesen auswählen...
					$l_sel[$i] = $laf[$i]['value'];
				} else {  //... sonst zu Liste hinzufügen
					$l_values[$i][-1] = -1;
					$l_descs[$i][-1] = $laf[$i]["name"];
					$l_sel[$i] = -1;
				}
			}
			$group[$gc]["row"][$rowcounter]["inputs"][$i] = array("desc" => getLL("admin_logins_rights_leute_level")." ".$i,
																	 "type" => "select",
																	 "name" => "sel_rechte_leute_".$i,
																	 "values" => $l_values[$i],
																	 "descs" => $l_descs[$i],
																	 "value" => $l_sel[$i],
																	 "params" => 'size="0"',
																	 );
		}
		$rowcounter++;

		//Spalten-Vorlage für Berechtigungen für einzelne Spalten
		$col_presets = array_merge((array)ko_get_userpref('-1', '', 'leute_itemset', 'ORDER BY `key` ASC'), (array)ko_get_userpref($_SESSION['ses_userid'], '', 'leute_itemset', 'ORDER BY `key` ASC'));
		$col_presets_values[] = $col_presets_descs[] = "";
		foreach($col_presets as $f) {
			$col_presets_values[$f["key"]] = $f["key"];
			$col_presets_descs[$f['key']] = $f['user_id'] == '-1' ? getLL('leute_filter_global_short').' '.$f['key'] : $f['key'];
		}
		$col_presets_values_orig = $col_presets_values;
		$las = ko_get_leute_admin_spalten($id, $type);
		//view
		if(in_array($las["view_name"], $col_presets_values_orig)) {
			$col_sel = $las["view_name"];
		} else {
			$col_sel = -1;
			$col_presets_values[-1] = -1;
			$col_presets_descs[-1] = $las["view_name"];
		}
		$group[$gc]["row"][$rowcounter]["inputs"][0] = array("desc" => getLL("admin_logins_rights_leute_cols_view"),
																 "type" => "select",
																 "name" => "sel_leute_cols_view",
																 "values" => $col_presets_values,
																 "descs" => $col_presets_descs,
																 "value" => $col_sel,
																 "params" => 'size="0"',
																 "colspan" => 'colspan="2"'
																 );
		//edit
		if(in_array($las["edit_name"], $col_presets_values_orig)) {
			$col_sel = $las["edit_name"];
		} else {
			$col_sel = -1;
			$col_presets_values[-1] = -1;
			$col_presets_descs[-1] = $las["edit_name"];
		}
		$group[$gc]["row"][$rowcounter++]["inputs"][1] = array("desc" => getLL("admin_logins_rights_leute_cols_edit"),
																 "type" => "select",
																 "name" => "sel_leute_cols_edit",
																 "values" => $col_presets_values,
																 "descs" => $col_presets_descs,
																 "value" => $col_sel,
																 "params" => 'size="0"',
																 "colspan" => 'colspan="2"'
																 );


		//Display group select to select a leute_admin_group
		if(ko_module_installed('groups')) {
			$group[$gc]["row"][$rowcounter]["inputs"][0] = array("desc" => getLL("admin_logins_rights_leute_groups"),
						"type" => "select",
						"name" => "sel_leute_admin_group",
						"params" => 'size="0"',
						"values" => array_merge(array(''), $groups_values),
						"descs" => array_merge(array(''), $groups_descs),
						"value" => implode(',', ko_get_leute_admin_groups($id, $type)),
						"colspan" => 'colspan="2"',
						);
			$value = $type == 'login' ? $login['leute_admin_assign'] : $admingroups[$id]['leute_admin_assign'];
			$group[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('admin_logins_rights_leute_assign'),
						'type' => 'checkbox',
						'name' => 'chk_leute_admin_assign',
						'value' => 1,
						'params' => $value ? 'checked="checked"' : '',
						'colspan' => 'colspan="2"',
						);


			//Setting to enable group subscriptions for users without level 4
			$value = $type == 'login' ? $login['leute_admin_gs'] : $admingroups[$id]['leute_admin_gs'];
			$group[$gc]["row"][$rowcounter++]["inputs"][0] = array("desc" => getLL("admin_logins_rights_leute_gs"),
						"type" => "checkbox",
						"name" => "chk_leute_admin_gs",
						"value" => "1",
						"params" => $value ? 'checked="checked"' : "",
						"colspan" => 'colspan="4"',
						);
		}

	} //if(in_array(leute, user_modules))


	/**
	  * Rechte für Module mit Gruppen-Rechten
		*/
	foreach($MODULES_GROUP_ACCESS as $module) {
		$done_modules[] = $module;
		if(!in_array($module, $user_modules)) continue;

		unset($gruppen);
		switch($module) {
			case "daten":
				if(ko_get_setting('daten_access_calendar') == 1) {
					//First get calendars
					$cals = db_select_data('ko_event_calendar', 'WHERE 1=1', '*', 'ORDER BY name ASC');
					foreach($cals as $cid => $cal) $gruppen['cal'.$cid] = $cal;
					//Then add event groups without calendar
					$egs = db_select_data('ko_eventgruppen', "WHERE `calendar_id` = '0'", '*', 'ORDER BY name ASC');
					foreach($egs as $eid => $eg) $gruppen[$eid] = $eg;
				} else {
					$egs = db_select_data('ko_eventgruppen AS g LEFT JOIN ko_event_calendar AS c ON g.calendar_id = c.id', 'WHERE 1=1', 'g.*, c.name AS calendar_name', 'ORDER BY calendar_name ASC, g.name ASC', '', FALSE, TRUE);
					foreach($egs as $eg) {
						$gruppen[$eg['id']] = $eg;
						if($eg['calendar_id'] > 0) $gruppen[$eg['id']]['name'] = $eg['calendar_name'].': '.$eg['name'];
					}
				}
			break;
			case "reservation":
				if(ko_get_setting('res_access_mode') == 1) {
					//Resitems
					$resgroups = db_select_data('ko_resgruppen', 'WHERE 1', '*', 'ORDER BY `name` ASC');
					foreach($resgroups as $rg) {
						$items = db_select_data('ko_resitem', "WHERE `gruppen_id` = '".$rg['id']."'", '*', 'ORDER BY `name` ASC');
						foreach($items as $item_id => $item) {
							$item['name'] = $rg['name'].': '.$item['name'];
							$gruppen[$item_id] = $item;
						}
					}
				} else {
					//Resgroups
					ko_get_resgroups($resgroups);
					foreach($resgroups as $gid => $g) $gruppen['grp'.$gid] = $g;
				}
			break;
			case 'rota': $gruppen = db_select_data('ko_rota_teams', '', '*', 'ORDER BY name ASC'); break;
			case "tapes": ko_get_tapegroups($gruppen); break;
			case "donations": $gruppen = db_select_data("ko_donations_accounts", "", "*", "ORDER BY number ASC"); break;
			case 'tracking': $gruppen = db_select_data('ko_tracking', '', '*', 'ORDER BY name ASC'); break;

			default:
				$gruppen = hook_access_get_groups($module);
		}

		//Alle-Rechte auslesen
		if($mode == 'edit') $user_access = ko_get_access($module, $id, TRUE, FALSE, $type, FALSE);
		$help = ko_get_help("admin", "login_rights_".$module);
		$group[++$gc] = array("titel" => getLL("module_".$module), "state" => "open", "colspan" => 'colspan="6"');
		$group[$gc]["row"][$rowcounter++]["inputs"][0] = array("desc" => "",
																													 "type" => "html",
																													 "value" => $help["link"]."&nbsp;".getLL("admin_logins_rights_".$module),
																													 "colspan" => 'colspan="6"'
																													);
		$values = $descs = array(0, 1, 2, 3, 4);
		if(in_array($module, array('reservation', 'rota'))) $values = $descs = array(0, 1, 2, 3, 4, 5);
		//Get levels from function (e.g. for plugins)
		if(TRUE === hook_access_get_levels($module, $_values, $_descs)) {
			$values = $_values;
			$descs = $_descs;
		}

		$group[$gc]["row"][$rowcounter]["inputs"][0] = array("desc" => strtoupper(getLL("all")),
																 "type" => "select",
																 "name" => "sel_rechte_".$module."_0",
																 "values" => $values,
																 "descs" => $descs,
																 "value" => $user_access[$module]['ALL'],
																 "params" => 'size="0"',
																 'buttons' => '<img src="'.$ko_path.'/images/icon_arrow_right.png" border="0" class="access_apply_all cursor_pointer" id="rechte_'.$module.'_0" title="'.getLL('admin_logins_apply_all_title').'" />',
																 );

		//Gruppen-Rechte
		$col = 1;
		foreach($gruppen as $g_i => $g) {
			$group[$gc]["row"][$rowcounter]["inputs"][$col++] = array("desc" => ko_html($g["name"]),
																	 "type" => "select",
																	 "name" => "sel_rechte_".$module."_".$g_i,
																	 "values" => $values,
																	 "descs" => $descs,
																	 "value" => $user_access[$module][$g_i],
																	 "params" => 'size="0"',
																	 );
			if($col == 6) {
				$col = 0;
				$rowcounter++;
			}
		}//foreach(gruppen)

		// in the modules event and reservation, add setting that can let the login see only events/reservations specified by the global time filter
		if ($module == 'daten' || $module == 'reservation') {
			$mTable = array('reservation' => 'res', 'daten' => 'event');
			$res = db_select_data('ko_admin' . ($type == "login" ? '' : 'groups'), "where `id` = $id", $mTable[$module] . "_force_global", '', '', TRUE, TRUE);
			$value = $res[$mTable[$module] . "_force_global"];
			$group[$gc]["row"][++$rowcounter]["inputs"][0] = array("desc" => getLL('force_global_filter'),
				"type" => "switch",
				"name" => "sel_force_global_".$module,
				"label_0" => getLL('no'),
				"label_1" => getLL('yes'),
				"value" => $value
			);

			if ($module == 'daten') {
				$res = db_select_data('ko_admin' . ($type == "login" ? '' : 'groups'), "where `id` = $id", $mTable[$module] . "_reminder_rights", '', '', TRUE, TRUE);
				$value = $res[$mTable[$module] . "_reminder_rights"];
				$group[$gc]["row"][$rowcounter]["inputs"][1] = array("desc" => getLL('reminder_rights'),
					"type" => "switch",
					"name" => "sel_reminder_rights_".$module,
					"label_0" => getLL('no'),
					"label_1" => getLL('yes'),
					"value" => $value
				);
			}
		}

	}


	//Groups module
	if(in_array('groups', $user_modules)) {
		$done_modules[] = 'groups';
		if($mode == 'edit') $user_access = ko_get_access('groups', $id, TRUE, FALSE, $type, FALSE);
		$group[++$gc] = array('titel' => getLL('module_groups'), 'state' => 'open', 'colspan' => 'colspan="6"');
		$help = ko_get_help('admin', 'login_rights_groups');
		$group[$gc]['row'][$rowcounter++]['inputs'][0] = array('desc' => '',
																													 'type' => 'html',
																													 'value' => $help['link'].'&nbsp;'.getLL('admin_logins_rights_groups'),
																													 'colspan' => 'colspan="6"'
																													);
		$values = $descs = array(0, 1, 2, 3, 4);
		$group[$gc]['row'][$rowcounter++]['inputs'][0] = array('desc' => strtoupper(getLL('all')),
																 'type' => 'select',
																 'name' => 'sel_rechte_groups',
																 'values' => $values,
																 'descs' => $descs,
																 'value' => $user_access['groups']['ALL'],
																 'params' => 'size="0"',
																 );

		include_once($ko_path.'groups/inc/groups.inc');
		if(!is_array($all_groups)) ko_get_groups($all_groups);
		$col = 0;
		foreach(array('view', 'new', 'edit', 'del') as $level_num => $rights_level) {
			$groups_rights_avalues = $groups_rights_adescs = array();
			if($user_access['groups']['ALL'] < ($level_num+1)) {
				$show_groups = array();
				$accessable_groups = db_select_data('ko_groups', "WHERE `rights_$rights_level` REGEXP '(^|,)".($type=='login' ? '' : 'g')."$id(,|$)'");
				$sort_groups = array();
				foreach($accessable_groups as $g) {
					$motherline = ko_groups_get_motherline($g['id'], $all_groups);
					$fullgid = sizeof($motherline) > 0 ? 'g'.implode(':g', $motherline).':g'.$g['id'] : 'g'.$g['id'];
					$name = ko_groups_decode($fullgid, 'group_desc_full');
					$show_groups[$g['id']] = array('p' => $g['pid'], 'v' => $g['id'], 'o' => $name);
					$sort_groups[$g['id']] = $name;
				}
				//Sort groups
				asort($sort_groups);
				$new = array();
				foreach($sort_groups as $temp_id => $name) {
					$groups_rights_avalues[] = $temp_id;
					$groups_rights_adescs[] = $name;
				}
				unset($sort_groups);

				$group[$gc]['row'][$rowcounter]['inputs'][$col] = array('desc' => getLL('form_groups_rights_rights_'.$rights_level),
																														 'type' => 'dyndoubleselect',
																														 'js_func_add' => 'double_select_add',
																														 'name' => 'sel_groups_rights_'.$rights_level,
																														 'avalues' => $groups_rights_avalues,
																														 'avalue' => implode(',', $groups_rights_avalues),
																														 'adescs' => $groups_rights_adescs,
																														 'params' => 'size="10"',
																														 'nochecklist' => TRUE,
																														 );
			} else {
				$group[$gc]['row'][$rowcounter]['inputs'][$col] = array('desc' => getLL('form_groups_rights_rights_'.$rights_level),
																														 'type' => 'html',
																														 'value' => getLL('form_groups_all_groups'),
																														 );
			}

			$col++;
			if($col > 1) {
				$rowcounter++;
				$col = 0;
			}
		}

	} //if(in_array(groups, user_modules))



	/**
	  * Rechte für Module ohne Gruppen-Rechten
		*/
	foreach($MODULES as $module) {
		if(in_array($module, $done_modules)) continue;
		$done_modules[] = $module;
		if(in_array($module, array('sms', 'mailing', 'tools'))) continue;
		if(!in_array($module, $user_modules)) continue;

		//Alle-Rechte auslesen
		if($mode == 'edit') $user_access = ko_get_access($module, $id, TRUE, FALSE, $type, FALSE);
		$help = ko_get_help("admin", "login_rights_".$module);
		$group[++$gc] = array("titel" => getLL("module_".$module), "state" => "open", "colspan" => 'colspan="6"');
		$group[$gc]["row"][$rowcounter++]["inputs"][0] = array("desc" => "",
																													 "type" => "html",
																													 "value" => $help["link"]."&nbsp;".getLL("admin_logins_rights_".$module),
																													 "colspan" => 'colspan="6"'
																													);
		$values = $descs = array(0, 1, 2, 3, 4);
		if($module == 'admin') $values = $descs = array(0, 1, 2, 3, 4, 5);
		//Get levels from function (e.g. for plugins)
		if(TRUE === hook_access_get_levels($module, $_values, $_descs)) {
			$values = $_values;
			$descs = $_descs;
		}
		$group[$gc]["row"][$rowcounter]["inputs"][0] = array("desc" => "",
																 "type" => "select",
																 "name" => "sel_rechte_".$module,
																 "values" => $values,
																 "descs" => $descs,
																 "value" => $user_access[$module]['ALL'],
																 "params" => 'size="0"',
																 );

		//Show KOTA columns this user should have access to. So far only for ko_kleingruppen
		if($module == 'kg') {
			$coltable = 'ko_kleingruppen';
			ko_include_kota(array($coltable));
			$col_values = $col_descs = array();
			foreach($KOTA[$coltable] as $k => $v) {
				if(substr($k, 0, 1) == '_') continue;
				$ll = getLL('kota_'.$coltable.'_'.$k);
				if(!$ll) continue;
				$col_values[] = $k;
				$col_descs[] = $ll ? $ll : $k;
			}
			$col_adescs = array();
			$col_avalues = ko_access_get_kota_columns($id, $coltable, 'login');
			foreach($col_avalues as $avalue) {
				$ll = getLL('kota_'.$coltable.'_'.$avalue);
				if(!$ll) continue;
				$col_adescs[] = $ll ? $ll : $avalue;
			}
			$group[$gc]["row"][++$rowcounter]["inputs"][0] = array("desc" => getLL("admin_logins_kota_columns"),
																		 "type" => "checkboxes",
																		 "name" => 'kota_columns_'.$coltable,
																		 "values" => $col_values,
																		 "descs" => $col_descs,
																		 "avalues" => $col_avalues,
																		 "avalue" => implode(",", $col_avalues),
																		 "size" => min(7, sizeof($col_values)),
																		 "colspan" => 'colspan="3"',
																		 );
		}

	}


	//Allow plugins to change form
	hook_form('ko_admingroups', $group, $mode, $id, array('type' => $type));


	if($type == "login") {
		$smarty->assign("tpl_titel", ( ($mode == "neu") ? getLL("admin_new_login") : getLL("admin_edit_login")) );
	} else {
		$smarty->assign("tpl_titel", ( ($mode == "neu") ? getLL("admin_new_admingroup") : getLL("admin_edit_admingroup")) );
	}
	$smarty->assign("tpl_submit_value", getLL("save"));
	$smarty->assign("tpl_id", $id);
	if($type == "login") {
		$smarty->assign("tpl_action", ( ($mode == "neu") ? "submit_neues_login" : "submit_edit_login") );
		$smarty->assign("tpl_cancel", "set_show_logins");
	} else {
		$smarty->assign("tpl_action", ( ($mode == "neu") ? "submit_new_admingroup" : "submit_edit_admingroup") );
		$smarty->assign("tpl_cancel", "set_show_admingroups");
	}
	$smarty->assign("tpl_groups", $group);
	$smarty->display("ko_formular.tpl");
}//ko_login_formular()



function ko_login_details($id) {
	global $MODULES;

	ko_get_login($id, $login);
	print '<h2>'.getLL("admin_logins_details_header").'"'.$login["login"].'"</h2>';

	//$admingroups = ko_get_admingroups();
	//ko_get_login($id, $login);
	foreach($MODULES as $m) {
		if(!ko_module_installed($m, $id)) continue;
		print "<br /><b>".getLL("module").": ".getLL("module_".$m)."</b><br />";
		$user_access = ko_get_access($m, $id, TRUE, TRUE, 'login', FALSE);
		for($level=1; $level<=4; $level++) {
			if($user_access[$m]['ALL'] >= $level) {
				print $level.": &radic;<br />";
			} else if($user_access[$m]['MAX'] >= $level) {
				$rights = "";
				foreach($user_access[$m] as $k => $v) {
					if(!intval($k) || $v < $level) continue;
					$rights .= $k.', ';
				}
				print $level.': '.($rights != '' ? substr($rights, 0, -2) : '&radic;').'<br />';
			} else {
				print $level.':<br />';
			}
		}
	}

}//ko_login_details()






function ko_show_set_allgemein() {
	global $smarty, $ko_path;
	global $access, $MODULES, $SMS_PARAMETER, $MAILING_PARAMETER;

	if($access['admin']['MAX'] < 2) return;

	$smarty->assign("tpl_titel", getLL("admin_settings_general_settings"));

	$pc = 0;
	$parts[$pc]['titel'] = getLL('admin_settings_contact');
	$contact_fields = array('name', 'address', 'zip', 'city', 'phone', 'url', 'email');
	$cc = 0;
	foreach($contact_fields as $field) {
		$setting[$cc]['desc'] = getLL('admin_settings_contact_'.$field);
		$setting[$cc]['type'] = 'text';
		$setting[$cc]['name'] = 'txt_contact_'.$field;
		$setting[$cc]['params'] = 'size="40"';
		$setting[$cc]['value'] = ko_html(ko_get_setting('info_'.$field));
		$cc++;
	}

	$parts[$pc]['settings'] = $setting;


	unset($setting);
	$counter = 0;
	$parts[++$pc]["titel"] = getLL("admin_settings_options");
	if(in_array('leute', $MODULES)) {
		$setting[$counter]["desc"] = getLL("admin_settings_options_login_edit_person");
		$setting[$counter]["type"] = "radio";
		$setting[$counter]["name"] = "rd_login_edit_person";
		$setting[$counter]["value"] = array(1, 0);
		$setting[$counter]["output"] = array(getLL("yes"), getLL("no"));
		$setting[$counter]["checked"] = ko_html(ko_get_setting("login_edit_person"));
		$counter++;
	}

	$setting[$counter]["desc"] = getLL("admin_settings_options_change_password");
	$setting[$counter]["type"] = "radio";
	$setting[$counter]["name"] = "rd_change_password";
	$setting[$counter]["value"] = array(1, 0);
	$setting[$counter]["output"] = array(getLL("yes"), getLL("no"));
	$setting[$counter]["checked"] = ko_html(ko_get_setting("change_password"));
	$counter++;

	$parts[$pc]["settings"] = $setting;



	if(in_array('sms', $MODULES)) {
		unset($setting);
		$counter = 0;
		$parts[++$pc]['titel'] = getLL('admin_settings_sms');

		$setting[$counter]['desc'] = getLL('admin_settings_sms_sender_ids');
		$setting[$counter]['type'] = 'html';
		$sender_ids = explode(',', ko_get_setting('sms_sender_ids'));
		$v = array();
		foreach($sender_ids as $id) {
			$v[] = ko_html($id).' <a href="#" onclick="c=confirm(\''.getLL('admin_settings_sms_confirm_delete_sender_id').'\'); if(!c) { return false; } else { jumpToUrl(\'index.php?action=delete_sms_sender_id&sender_id='.urlencode($id).'\'); }"><img src="'.$ko_path.'images/icon_trash.png" border="0" /></a>';
		}
		$setting[$counter]['value'] = implode(', ', $v);
		$counter++;

		if($SMS_PARAMETER['provider'] == 'aspsms') {
			$setting[$counter]['desc'] = getLL('admin_settings_sms_new_sender_id');
			$setting[$counter]['type'] = 'html';
			$v = '';
			if(check_natel($_POST['sms_sender_id']) && $_POST['submit_sms_sender_id']) {
				$v .= '<input type="text" name="sms_sender_id" value="'.$_POST['sms_sender_id'].'" size="20" maxlength="11" />';
				$v .= '&nbsp;&nbsp;&nbsp;';
				$v .= getLL('admin_settings_sms_new_sender_id_code').': <input type="text" name="sms_sender_id_code" size="10" />';
			} else {
				$v .= '<input type="text" name="sms_sender_id" value="" size="20" maxlength="11" />';
			}
			$v .= '&nbsp;&nbsp;<input type="submit" name="submit_sms_sender_id" onclick="set_action(\'submit_sms_sender_id\');" value="'.getLL('OK').'" />';
			$setting[$counter]['value'] = $v;
			$counter++;
		} else {
			$setting[$counter]['desc'] = getLL('admin_settings_sms_new_sender_id_clickatell');
			$setting[$counter]['type'] = 'html';
			$v  = '<input type="text" name="sms_sender_id" value="" size="20" />';
			$v .= '&nbsp;&nbsp;<input type="submit" name="submit_sms_sender_id_clickatell" onclick="set_action(\'submit_sms_sender_id_clickatell\');" value="'.getLL('OK').'" />';
			$setting[$counter]['value'] = $v;
			$counter++;
		}

		$setting[$counter]['desc'] = getLL('admin_settings_sms_country_code');
		$setting[$counter]['type'] = 'text';
		$setting[$counter]['name'] = 'txt_sms_country_code';
		$setting[$counter]['params'] = '';
		$setting[$counter]['value'] = ko_html(ko_get_setting('sms_country_code'));
		$counter++;

		$parts[$pc]['settings'] = $setting;
	}


	if(in_array('mailing', $MODULES) && is_array($MAILING_PARAMETER) && $MAILING_PARAMETER['domain'] != '') {
		unset($setting);
		$counter = 0;
		$parts[++$pc]['titel'] = getLL('admin_settings_mailing');

		$setting[$counter]['desc'] = getLL('admin_settings_mailing_mails_per_cycle');
		$setting[$counter]['type'] = 'text';
		$setting[$counter]['name'] = 'txt_mailing_mails_per_cycle';
		$setting[$counter]['params'] = 'size="10"';
		$setting[$counter]['value'] = ko_html(ko_get_setting('mailing_mails_per_cycle'));
		$counter++;

		$setting[$counter]['desc'] = getLL('admin_settings_mailing_max_recipients');
		$setting[$counter]['type'] = 'text';
		$setting[$counter]['name'] = 'txt_mailing_max_recipients';
		$setting[$counter]['params'] = 'size="10"';
		$setting[$counter]['value'] = ko_html(ko_get_setting('mailing_max_recipients'));
		$counter++;

		$setting[$counter]['desc'] = getLL('admin_settings_mailing_only_alias');
		$setting[$counter]['type'] = 'checkbox';
		$setting[$counter]['name'] = 'chk_mailing_only_alias';
		$value = ko_get_setting('mailing_only_alias');
		$setting[$counter]['params'] = $value ? 'checked="checked"' : '';
		$counter++;

		$setting[$counter]['desc'] = getLL('admin_settings_mailing_allow_double');
		$setting[$counter]['type'] = 'checkbox';
		$setting[$counter]['name'] = 'chk_mailing_allow_double';
		$value = ko_get_setting('mailing_allow_double');
		$setting[$counter]['params'] = $value ? 'checked="checked"' : '';
		$counter++;

		$parts[$pc]['settings'] = $setting;
	}



	//XLS export settings
	unset($setting);
	$counter = 0;
	$parts[++$pc]['titel'] = getLL('admin_settings_xls');

	$setting[$counter]['desc'] = getLL('admin_settings_xls_default_font');
	$setting[$counter]['type'] = 'text';
	$setting[$counter]['name'] = 'txt_xls_default_font';
	$setting[$counter]['params'] = 'size="30"';
	$setting[$counter]['value'] = ko_html(ko_get_setting('xls_default_font'));
	$counter++;

	$setting[$counter]['desc'] = getLL('admin_settings_xls_title_font');
	$setting[$counter]['type'] = 'text';
	$setting[$counter]['name'] = 'txt_xls_title_font';
	$setting[$counter]['params'] = 'size="30"';
	$setting[$counter]['value'] = ko_html(ko_get_setting('xls_title_font'));
	$counter++;

	$setting[$counter]['desc'] = getLL('admin_settings_xls_title_bold');
	$setting[$counter]['type'] = 'checkbox';
	$setting[$counter]['name'] = 'chk_xls_title_bold';
	$value = ko_get_setting('xls_title_bold');
	$setting[$counter]['params'] = $value ? 'checked="checked"' : '';
	$counter++;

	$setting[$counter]['desc'] = getLL('admin_settings_xls_title_color');
	$setting[$counter]['type'] = 'select';
	$setting[$counter]['name'] = 'txt_xls_title_color';
	$setting[$counter]['params'] = 'size="0"';
	$setting[$counter]['values'] = array('blue', 'black', 'cyan', 'brown', 'magenta', 'grey', 'green', 'orange', 'purple', 'red', 'yellow');
	$setting[$counter]['descs'] = array(getLL('admin_settings_xls_title_color_blue'), getLL('admin_settings_xls_title_color_black'), getLL('admin_settings_xls_title_color_cyan'), getLL('admin_settings_xls_title_color_brown'), getLL('admin_settings_xls_title_color_magenta'), getLL('admin_settings_xls_title_color_grey'), getLL('admin_settings_xls_title_color_green'), getLL('admin_settings_xls_title_color_orange'), getLL('admin_settings_xls_title_color_purple'), getLL('admin_settings_xls_title_color_red'), getLL('admin_settings_xls_title_color_yellow'));
	$setting[$counter]['value'] = ko_get_setting('xls_title_color');
	$counter++;

	$parts[$pc]['settings'] = $setting;



	//Add help link
	$smarty->assign("help", ko_get_help("admin", "set_allgemein"));

	$smarty->assign("tpl_parts", $parts);
	$smarty->assign("tpl_action", "save_set_allgemein");
	$smarty->display("ko_settings.tpl");
}//ko_show_set_allgemein()





function ko_show_set_etiketten($id="") {
	global $smarty, $BASE_PATH;
	global $access;

	if($access['admin']['MAX'] < 2) return;

	//Select-Felder füllen
	$pageformat = array('values' => array('A4', 'A5', 'A6', 'C5'),
		'output' => array('A4', 'A5', 'A6', 'C5'));
	$pageorientation = array('values' => array('P', 'L'),
		'output' => array(getLL('portrait'), getLL('landscape')));
	$textalignh = array( "values" => array("L", "C", "R"),
		"output" => array(getLL("left"), getLL("center"), getLL("right")));
	$textalignv = array( "values" => array("T", "C", "B"),
		"output" => array(getLL("top"), getLL("center"), getLL("bottom")));
	$textsizes = array(); for($i=7; $i<=50; $i++) $textsizes[] = $i;
	$textsize = array( 'values' => $textsizes, 'output' => $textsizes);
	$ra_textsize = array( 'values' => array(6, 7, 8, 9, 10, 11, 12),
		'output' => array(6, 7, 8, 9, 10, 11, 12));
	$_fonts = ko_get_pdf_fonts();
	$font_output = array(); foreach($_fonts as $font) $font_output[] = $font['name'];
	$ra_fonts = $fonts = array('values' => array_keys($_fonts), 'output' => $font_output);

	//Vorlage-Namen auslesen (für Dropdown-Listen)
	$vorlagen["values"][] = "";
	$vorlagen["output"][] = "";
	ko_get_etiketten_vorlagen($vorlagen_);
	foreach($vorlagen_ as $v) {
		$vorlagen["values"][] = $v["vorlage"];
		$vorlagen["output"][] = $v["value"];
	}//foreach(vorlagen as v)

	//Werte für anzuzeigende Vorlage auslesen
	if($id != "") {
		ko_get_etiketten_vorlage($id, $v);
		$vorlagen["value"] = $id;
		$textalignh["value"] = $v["align_horiz"];
		$textalignv["value"] = $v["align_vert"];
		$pageformat['value'] = $v['page_format'];
		$pageorientation['value'] = $v['page_orientation'];
		$fonts['value'] = $v['font'];
		$textsize["value"] = $v["textsize"];
		$ra_fonts['value'] = $v['ra_font'];
		$ra_textsize["value"] = $v['ra_textsize'];
		$smarty->assign("txt_per_row", ko_html($v["per_row"]));
		$smarty->assign("txt_per_col", ko_html($v["per_col"]));
		$smarty->assign("txt_border_top", ko_html($v["border_top"]));
		$smarty->assign("txt_border_right", ko_html($v["border_right"]));
		$smarty->assign("txt_border_bottom", ko_html($v["border_bottom"]));
		$smarty->assign("txt_border_left", ko_html($v["border_left"]));
		$smarty->assign("txt_spacing_horiz", ko_html($v["spacing_horiz"]));
		$smarty->assign("txt_spacing_vert", ko_html($v["spacing_vert"]));
		$smarty->assign('txt_ra_margin_top', ko_html($v['ra_margin_top']));
		$smarty->assign('txt_ra_margin_left', ko_html($v['ra_margin_left']));
		$smarty->assign('txt_pic_x', ko_html($v['pic_x']));
		$smarty->assign('txt_pic_y', ko_html($v['pic_y']));
		$smarty->assign('txt_pic_w', ko_html($v['pic_w']));
		$smarty->assign('pic_file', ko_pic_get_tooltip($v['pic_file']));
	}//if(id)


	$smarty->assign("tpl_titel", getLL("admin_labels_title"));

	$smarty->assign("vorlagen", $vorlagen);

	$smarty->assign('page_format', $pageformat);
	$smarty->assign('page_orientation', $pageorientation);
	$smarty->assign("textalignh", $textalignh);
	$smarty->assign("textalignv", $textalignv);
	$smarty->assign("textsize", $textsize);
	$smarty->assign('ra_textsize', $ra_textsize);
	$smarty->assign('font', $fonts);
	$smarty->assign('ra_font', $ra_fonts);

	//LL-Values
	$smarty->assign("label_open", getLL("admin_settings_label_open"));
	$smarty->assign("label_delete", getLL('admin_settings_label_delete'));
	$smarty->assign("label_delete_confirm", getLL('admin_settings_label_delete_confirm'));
	$smarty->assign('label_page_format', getLL('admin_settings_label_page_format'));
	$smarty->assign('label_page_orientation', getLL('admin_settings_label_page_orientation'));
	$smarty->assign("label_per_row", getLL('admin_settings_label_per_row'));
	$smarty->assign("label_per_col", getLL('admin_settings_label_per_col'));
	$smarty->assign("label_border_top", getLL('admin_settings_label_border_top'));
	$smarty->assign("label_border_right", getLL('admin_settings_label_border_right'));
	$smarty->assign("label_border_bottom", getLL('admin_settings_label_border_bottom'));
	$smarty->assign("label_border_left", getLL('admin_settings_label_border_left'));
	$smarty->assign("label_spacing_horiz", getLL('admin_settings_label_spacing_horiz'));
	$smarty->assign("label_spacing_vert", getLL('admin_settings_label_spacing_vert'));
	$smarty->assign("label_align_horiz", getLL('admin_settings_label_align_horiz'));
	$smarty->assign("label_align_vert", getLL('admin_settings_label_align_vert'));
	$smarty->assign("label_font", getLL('admin_settings_label_font'));
	$smarty->assign("label_textsize", getLL('admin_settings_label_textsize'));
	$smarty->assign("label_ra_font", getLL('admin_settings_label_ra_font'));
	$smarty->assign('label_ra_size', getLL('admin_settings_label_return_address_size'));
	$smarty->assign('label_ra_margin_top', getLL('admin_settings_label_return_address_margin_top'));
	$smarty->assign('label_ra_margin_left', getLL('admin_settings_label_return_address_margin_left'));
	$smarty->assign('label_pic_file', getLL('admin_settings_label_pic_file'));
	$smarty->assign('label_pic_x', getLL('admin_settings_label_pic_x'));
	$smarty->assign('label_pic_y', getLL('admin_settings_label_pic_y'));
	$smarty->assign('label_pic_w', getLL('admin_settings_label_pic_w'));
	$smarty->assign("label_or_new", getLL('admin_settings_label_or_new'));
	$smarty->assign("label_preset", getLL('admin_settings_label_preset'));

	//Add help link
	$smarty->assign("help", ko_get_help("admin", "set_etiketten"));

	$smarty->assign("tpl_action", "save_etiketten");
	$smarty->assign("tpl_action_open", "open_etiketten");
	$smarty->assign('sesid', session_id());
	$smarty->display("ko_settings_etiketten.tpl");
}//ko_show_set_etiketten()






function ko_list_leute_pdf($output=TRUE) {
	global $smarty;
	global $access;

	if($access['admin']['MAX'] < 2) return;

	$es = db_select_data("ko_pdf_layout", "WHERE `type` = 'leute'", "*", "ORDER BY `name` ASC");
	$rows = sizeof($es);

	$list = new kOOL_listview();

	$list->init("admin", "ko_pdf_layout", array("chk", "edit", "delete"), 1, 1000);
	$list->setTitle(getLL("admin_pdf_list_title"));
	$list->setAccessRights(FALSE);
	$list->setActions(array("edit" => array("action" => "edit_leute_pdf"),
													"delete" => array("action" => "delete_leute_pdf", "confirm" => TRUE))
										);
	$list->setSort(FALSE);
	$list->disableMultiedit();
	$list->setStats($rows, '', '', '', TRUE);


	//Footer
	//Totals
	$list_footer = $smarty->get_template_vars('list_footer');
	$list_footer[] = array("label" => getLL("admin_settings_leute_pdf_new"), "button" => '<input type="submit" onclick="set_action(\'set_leute_pdf_new\');" value="'.getLL("OK").'" />');

	$list->setFooter($list_footer);


	//Output the list
	if($output) {
		$list->render($es);
	} else {
		print $list->render($es);
	}
}//ko_list_leute_pdf()






function ko_formular_leute_pdf($mode="new", $layout_id=0) {
	global $smarty;
	global $LEUTE_NO_FAMILY;

	if($mode == "new") {
	} else {
		if(!$layout_id) return FALSE;
		$_layout = db_select_data('ko_pdf_layout', "WHERE `id` = '$layout_id' AND `type` = 'leute'", '*', '', '', TRUE);
		$layout = unserialize($_layout["data"]);
	}

	//Prepare filter select
	$filterset = array_merge((array)ko_get_userpref('-1', '', 'filterset', 'ORDER BY `key` ASC'), (array)ko_get_userpref($_SESSION['ses_userid'], '', 'filterset', 'ORDER BY `key` ASC'));
	$current_filter = "";
	$filter_values[] = $filter_descs[] = "";
	foreach($filterset as $f) {
		$value = $f['user_id'] == '-1' ? '@G@'.$f['key'] : $f['key'];
		$filter_values[] = $value;
		$filter_descs[] = $f['user_id'] == '-1' ? getLL('itemlist_global_short').' '.$f['key'] : $f['key'];
		if($mode == "edit" && $f["value"] == serialize($layout["filter"])) $current_filter = $value;
	}

	//Prepare columns select
	$itemset = array_merge((array)ko_get_userpref('-1', '', 'leute_itemset', 'ORDER BY `key` ASC'), (array)ko_get_userpref($_SESSION['ses_userid'], '', 'leute_itemset', 'ORDER BY `key` ASC'));
	$current_columns = "";
	$columns_values[] = $columns_descs[] = "";
	foreach($itemset as $f) {
		$value = $f['user_id'] == '-1' ? '@G@'.$f['key'] : $f['key'];
		$columns_values[] = $value;
		$columns_descs[] = $f['user_id'] == '-1' ? getLL('itemlist_global_short').' '.$f["key"] : $f['key'];
		if($mode == "edit" && $f["value"] == implode(",", $layout["columns"])) $current_columns = $value;
	}

	//Prepare select for sorting
	$leute_col_name = ko_get_leute_col_name($groups_hierarchie=TRUE, $add_group_datafields=FALSE);
	$sort_values = $sort_descs = array();
	$sort_values[] = $sort_descs[] = "";
	foreach($leute_col_name as $col => $name) {
		if(!$col || $col == "groups") continue;
		$sort_values[] = $col;
		$sort_descs[] = $name ? $name : $col;
	}

	//Prepare available fonts
	$fonts_values = $fonts_descs = array();
	$fonts = ko_get_pdf_fonts();
	foreach($fonts as $font) {
		$fonts_values[] = $font["id"];
		$fonts_descs[]  = $font["name"];
	}
	$fontsizes = array(7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25);


	$gc = $rowcounter = 0;

	$group[$gc] = array("titel" => getLL("admin_settings_leute_pdf_title_name"), "state" => "open");
	$group[$gc]["row"][$rowcounter++]["inputs"][0] = array("desc" => getLL("admin_settings_leute_pdf_name_name"),
																												 "type" => "text",
																												 "name" => "pdf[name]",
																												 "value" => $_layout["name"],
																												 "params" => 'size="40"'
																												 );

	$group[++$gc] = array("titel" => getLL("admin_settings_leute_pdf_title_page"), "state" => "open");
	$group[$gc]["row"][$rowcounter++]["inputs"][0] = array("desc" => getLL("admin_settings_leute_pdf_page_orientation"),
																												 "type" => "select",
																												 "name" => "pdf[page][orientation]",
																												 "values" => array("L", "P"),
																												 "descs" => array(getLL("admin_settings_leute_pdf_page_orientation_L"), getLL("admin_settings_leute_pdf_page_orientation_P")),
																												 "value" => $layout["page"]["orientation"],
																												 "params" => 'size="0"'
																												 );
	$group[$gc]["row"][$rowcounter]["inputs"][0] = array("desc" => getLL("admin_settings_leute_pdf_page_margin_left"),
																											 "type" => "text",
																											 "name" => "pdf[page][margin_left]",
																											 "value" => $layout["page"]["margin_left"],
																											 "params" => 'size="10"',
																											 );
	$group[$gc]["row"][$rowcounter++]["inputs"][1] = array("desc" => getLL("admin_settings_leute_pdf_page_margin_top"),
																											 "type" => "text",
																											 "name" => "pdf[page][margin_top]",
																											 "value" => $layout["page"]["margin_top"],
																											 "params" => 'size="10"',
																											 );
	$group[$gc]["row"][$rowcounter]["inputs"][0] = array("desc" => getLL("admin_settings_leute_pdf_page_margin_right"),
																											 "type" => "text",
																											 "name" => "pdf[page][margin_right]",
																											 "value" => $layout["page"]["margin_right"],
																											 "params" => 'size="10"',
																											 );
	$group[$gc]["row"][$rowcounter++]["inputs"][1] = array("desc" => getLL("admin_settings_leute_pdf_page_margin_bottom"),
																											 "type" => "text",
																											 "name" => "pdf[page][margin_bottom]",
																											 "value" => $layout["page"]["margin_bottom"],
																											 "params" => 'size="10"',
																											 );

	//Header
	$group[++$gc] = array("titel" => getLL("admin_settings_leute_pdf_title_header"), "state" => "open");
	$group[$gc]["row"][$rowcounter++]["inputs"][0] = array("desc" => getLL("help"),
																												 "type" => "html",
																												 "value" => getLL("leute_export_pdf_help_headerfooter"),
																												 "colspan" => 'colspan="3"',
																												 );
	$group[$gc]["row"][$rowcounter++]["inputs"][0] = array("type" => "   ", "colspan" => 'colspan="3"');
	$group[$gc]["row"][$rowcounter]["inputs"][0] = array("desc" => getLL("leute_export_pdf_header_left"),
																												 "type" => "text",
																												 "name" => "pdf[header][left][text]",
																												 "value" => $layout["header"]["left"]["text"],
																												 "params" => 'size="50"',
																												 );
	$group[$gc]["row"][$rowcounter]["inputs"][1] = array("desc" => getLL("leute_export_pdf_header_center"),
																												 "type" => "text",
																												 "name" => "pdf[header][center][text]",
																												 "value" => $layout["header"]["center"]["text"],
																												 "params" => 'size="50"',
																												 );
	$group[$gc]["row"][$rowcounter++]["inputs"][2] = array("desc" => getLL("leute_export_pdf_header_right"),
																												 "type" => "text",
																												 "name" => "pdf[header][right][text]",
																												 "value" => $layout["header"]["right"]["text"],
																												 "params" => 'size="50"',
																												 );
	$group[$gc]["row"][$rowcounter]["inputs"][0] = array("desc" => getLL("admin_settings_leute_pdf_header_left_font"),
																												 "type" => "select",
																												 "name" => "pdf[header][left][font]",
																												 "values" => $fonts_values,
																												 "descs" => $fonts_descs,
																												 "value" => $layout["header"]["left"]["font"],
																												 "params" => 'size="0"',
																												 );
	$group[$gc]["row"][$rowcounter]["inputs"][1] = array("desc" => getLL("admin_settings_leute_pdf_header_center_font"),
																												 "type" => "select",
																												 "name" => "pdf[header][center][font]",
																												 "values" => $fonts_values,
																												 "descs" => $fonts_descs,
																												 "value" => $layout["header"]["center"]["font"],
																												 "params" => 'size="0"',
																												 );
	$group[$gc]["row"][$rowcounter++]["inputs"][2] = array("desc" => getLL("admin_settings_leute_pdf_header_right_font"),
																												 "type" => "select",
																												 "name" => "pdf[header][right][font]",
																												 "values" => $fonts_values,
																												 "descs" => $fonts_descs,
																												 "value" => $layout["header"]["right"]["font"],
																												 "params" => 'size="0"',
																												 );
	$group[$gc]["row"][$rowcounter]["inputs"][0] = array("desc" => getLL("admin_settings_leute_pdf_header_left_fontsize"),
																												 "type" => "select",
																												 "name" => "pdf[header][left][fontsize]",
																												 "values" => $fontsizes,
																												 "descs" => $fontsizes,
																												 "value" => $layout["header"]["left"]["fontsize"] ? $layout["header"]["left"]["fontsize"] : 11,
																												 "params" => 'size="0"',
																												 );
	$group[$gc]["row"][$rowcounter]["inputs"][2] = array("desc" => getLL("admin_settings_leute_pdf_header_center_fontsize"),
																												 "type" => "select",
																												 "name" => "pdf[header][center][fontsize]",
																												 "values" => $fontsizes,
																												 "descs" => $fontsizes,
																												 "value" => $layout["header"]["center"]["fontsize"] ? $layout["header"]["center"]["fontsize"] : 11,
																												 "params" => 'size="0"',
																												 );
	$group[$gc]["row"][$rowcounter++]["inputs"][3] = array("desc" => getLL("admin_settings_leute_pdf_header_right_fontsize"),
																												 "type" => "select",
																												 "name" => "pdf[header][right][fontsize]",
																												 "values" => $fontsizes,
																												 "descs" => $fontsizes,
																												 "value" => $layout["header"]["right"]["fontsize"] ? $layout["header"]["right"]["fontsize"] : 11,
																												 "params" => 'size="0"',
																												 );

	//Header-Row
	$group[++$gc] = array("titel" => getLL("admin_settings_leute_pdf_title_headerrow"), "state" => "open");
	$group[$gc]["row"][$rowcounter]["inputs"][0] = array("desc" => getLL("admin_settings_leute_pdf_headerrow_font"),
																												 "type" => "select",
																												 "name" => "pdf[headerrow][font]",
																												 "values" => $fonts_values,
																												 "descs" => $fonts_descs,
																												 "value" => $layout["headerrow"]["font"],
																												 "params" => 'size="0"',
																												 );
	$group[$gc]["row"][$rowcounter]["inputs"][1] = array("desc" => getLL("admin_settings_leute_pdf_headerrow_fontsize"),
																												 "type" => "select",
																												 "name" => "pdf[headerrow][fontsize]",
																												 "values" => $fontsizes,
																												 "descs" => $fontsizes,
																												 "value" => $layout["headerrow"]["fontsize"] ? $layout["headerrow"]["fontsize"] : 11,
																												 "params" => 'size="0"',
																												 );
	$group[$gc]["row"][$rowcounter++]["inputs"][2] = array("desc" => getLL("admin_settings_leute_pdf_headerrow_fillcolor"),
																												 "type" => "select",
																												 "name" => "pdf[headerrow][fillcolor]",
																												 "values" => array("255", "230", "204", "179", "153", "128"),
																												 "descs" => array(getLL("grey_100"), getLL("grey_90"), getLL("grey_80"), getLL("grey_70"), getLL("grey_60"), getLL("grey_50")),
																												 "value" => $layout["headerrow"]["fillcolor"],
																												 "params" => 'size="0"',
																												 );

	//Data
	$group[++$gc] = array("titel" => getLL("leute_export_pdf_title_data"), "state" => "open");
	$group[$gc]["row"][$rowcounter++]["inputs"][0] = array("desc" => getLL("leute_export_pdf_filter"),
																												 "type" => "select",
																												 "name" => "pdf[filter]",
																												 "values" => $filter_values,
																												 "descs" => $filter_descs,
																												 "value" => $current_filter,
																												 "params" => 'size="0"'
																												 );
	$group[$gc]["row"][$rowcounter]["inputs"][0] = array("desc" => getLL("leute_export_pdf_columns"),
																											 "type" => "select",
																											 "name" => "pdf[columns]",
																											 "values" => $columns_values,
																											 "descs" => $columns_descs,
																											 "value" => $current_columns,
																											 "params" => 'size="0"'
																											 );
	if (!$LEUTE_NO_FAMILY) {
		$group[$gc]["row"][$rowcounter++]["inputs"][1] = array("desc" => getLL("leute_export_pdf_show_children"),
			"type" => "checkbox",
			"name" => "pdf[columns_children]",
			"value" => "1",
			"params" => $layout["columns_children"] ? 'checked="checked"' : "",
		);
	}
	$group[$gc]["row"][$rowcounter]["inputs"][0] = array("desc" => getLL("admin_settings_leute_pdf_sort"),
																											 "type" => "select",
																											 "name" => "pdf[sort]",
																											 "values" => $sort_values,
																											 "descs" => $sort_descs,
																											 "value" => $mode == "edit" ? $layout["sort"] : $_SESSION["sort_leute"],
																											 "params" => 'size="0"'
																											 );
	$group[$gc]["row"][$rowcounter++]["inputs"][1] = array("desc" => getLL("admin_settings_leute_pdf_sort_order"),
																												 "type" => "select",
																												 "name" => "pdf[sort_order]",
																												 "values" => array("", "ASC", "DESC"),
																												 "descs" => array("", getLL("list_sort_asc"), getLL("list_sort_desc")),
																												 "value" => $mode == "edit" ? $layout["sort_order"] : $_SESSION["sort_leute_order"],
																												 "params" => 'size="0"'
																												 );
	$group[$gc]["row"][$rowcounter]["inputs"][0] = array("desc" => getLL("admin_settings_leute_pdf_default_font"),
																												 "type" => "select",
																												 "name" => "pdf[col_template][_default][font]",
																												 "values" => $fonts_values,
																												 "descs" => $fonts_descs,
																												 "value" => $layout["col_template"]["_default"]["font"],
																												 "params" => 'size="0"',
																												 );
	$group[$gc]["row"][$rowcounter]["inputs"][1] = array("desc" => getLL("admin_settings_leute_pdf_default_fontsize"),
																												 "type" => "select",
																												 "name" => "pdf[col_template][_default][fontsize]",
																												 "values" => $fontsizes,
																												 "descs" => $fontsizes,
																												 "value" => $layout["col_template"]["_default"]["fontsize"] ? $layout["col_template"]["_default"]["fontsize"] : 11,
																												 "params" => 'size="0"',
																												 );
	
	//Footer
	$group[++$gc] = array("titel" => getLL("admin_settings_leute_pdf_title_footer"), "state" => "open");
	$group[$gc]["row"][$rowcounter++]["inputs"][0] = array("desc" => getLL("help"),
																												 "type" => "html",
																												 "value" => getLL("leute_export_pdf_help_headerfooter"),
																												 "colspan" => 'colspan="3"',
																												 );
	$group[$gc]["row"][$rowcounter++]["inputs"][0] = array("type" => "   ", "colspan" => 'colspan="3"');
	$group[$gc]["row"][$rowcounter]["inputs"][0] = array("desc" => getLL("leute_export_pdf_footer_left"),
																												 "type" => "text",
																												 "name" => "pdf[footer][left][text]",
																												 "value" => $layout["footer"]["left"]["text"],
																												 "params" => 'size="50"',
																												 );
	$group[$gc]["row"][$rowcounter]["inputs"][1] = array("desc" => getLL("leute_export_pdf_footer_center"),
																												 "type" => "text",
																												 "name" => "pdf[footer][center][text]",
																												 "value" => $layout["footer"]["center"]["text"],
																												 "params" => 'size="50"',
																												 );
	$group[$gc]["row"][$rowcounter++]["inputs"][2] = array("desc" => getLL("leute_export_pdf_footer_right"),
																												 "type" => "text",
																												 "name" => "pdf[footer][right][text]",
																												 "value" => $layout["footer"]["right"]["text"],
																												 "params" => 'size="50"',
																												 );
	$group[$gc]["row"][$rowcounter]["inputs"][0] = array("desc" => getLL("admin_settings_leute_pdf_footer_left_font"),
																												 "type" => "select",
																												 "name" => "pdf[footer][left][font]",
																												 "values" => $fonts_values,
																												 "descs" => $fonts_descs,
																												 "value" => $layout["footer"]["left"]["font"],
																												 "params" => 'size="0"',
																												 );
	$group[$gc]["row"][$rowcounter]["inputs"][1] = array("desc" => getLL("admin_settings_leute_pdf_footer_center_font"),
																												 "type" => "select",
																												 "name" => "pdf[footer][center][font]",
																												 "values" => $fonts_values,
																												 "descs" => $fonts_descs,
																												 "value" => $layout["footer"]["center"]["font"],
																												 "params" => 'size="0"',
																												 );
	$group[$gc]["row"][$rowcounter++]["inputs"][2] = array("desc" => getLL("admin_settings_leute_pdf_footer_right_font"),
																												 "type" => "select",
																												 "name" => "pdf[footer][right][font]",
																												 "values" => $fonts_values,
																												 "descs" => $fonts_descs,
																												 "value" => $layout["footer"]["right"]["font"],
																												 "params" => 'size="0"',
																												 );
	$group[$gc]["row"][$rowcounter]["inputs"][0] = array("desc" => getLL("admin_settings_leute_pdf_footer_left_fontsize"),
																												 "type" => "select",
																												 "name" => "pdf[footer][left][fontsize]",
																												 "values" => $fontsizes,
																												 "descs" => $fontsizes,
																												 "value" => $layout["footer"]["left"]["fontsize"] ? $layout["footer"]["left"]["fontsize"] : 11,
																												 "params" => 'size="0"',
																												 );
	$group[$gc]["row"][$rowcounter]["inputs"][2] = array("desc" => getLL("admin_settings_leute_pdf_footer_center_fontsize"),
																												 "type" => "select",
																												 "name" => "pdf[footer][center][fontsize]",
																												 "values" => $fontsizes,
																												 "descs" => $fontsizes,
																												 "value" => $layout["footer"]["center"]["fontsize"] ? $layout["footer"]["center"]["fontsize"] : 11,
																												 "params" => 'size="0"',
																												 );
	$group[$gc]["row"][$rowcounter++]["inputs"][3] = array("desc" => getLL("admin_settings_leute_pdf_footer_right_fontsize"),
																												 "type" => "select",
																												 "name" => "pdf[footer][right][fontsize]",
																												 "values" => $fontsizes,
																												 "descs" => $fontsizes,
																												 "value" => $layout["footer"]["right"]["fontsize"] ? $layout["footer"]["right"]["fontsize"] : 11,
																												 "params" => 'size="0"',
																												 );

	$smarty->assign("tpl_titel", getLL("admin_settings_leute_pdf"));
	$smarty->assign("tpl_submit_value", getLL("save"));
	$smarty->assign("tpl_action", ($mode == "new" ? "submit_new_leute_pdf" : "submit_edit_leute_pdf"));
	$smarty->assign("tpl_cancel", "set_leute_pdf");
	$smarty->assign("tpl_groups", $group);
	$smarty->assign("tpl_hidden_inputs", array(0 => array("name" => "layout_id", "value" => $layout_id)));

	$smarty->display('ko_formular.tpl');
}//ko_formular_leute_pdf()





function ko_show_set_layout($uid) {
	global $smarty;
	global $FRONTMODULES, $MODULES;
	global $access;

	if($uid == ko_get_guest_id()) {
		if($access['admin']['MAX'] < 3) return;
		$smarty->assign("tpl_titel", getLL("admin_settings_layout_guest"));
	}
	else {
		if($access['admin']['MAX'] < 1) return;
		$smarty->assign("tpl_titel", getLL("admin_settings_layout"));
	}

	$partscounter = 0;

	//Front-Modules
	$values = array("nicht", "left", "center", "right");
	$output = array(getLL("not"), getLL("left"), getLL("center"), getLL("right"));

	foreach($FRONTMODULES as $fm_i => $fm) {
		//check for neded modules for this FM
		if(!ko_check_fm_for_user($fm_i, $uid)) continue;

		if($fm_i == 'adressaenderung') {
			$rights_all = ko_get_access_all('leute_admin', $uid);
			if($rights_all >= 2) continue;
		}

		$checked = "nicht";

		//Bestehende Einstellungen auslesen
		$fm_left = explode(",", ko_get_userpref($uid, "front_modules_left"));
		if(in_array($fm_i, $fm_left)) $checked = "left";
		$fm_center = explode(",", ko_get_userpref($uid, "front_modules_center"));
		if(in_array($fm_i, $fm_center)) $checked = "center";
		$fm_right = explode(",", ko_get_userpref($uid, "front_modules_right"));
		if(in_array($fm_i, $fm_right)) $checked = "right";

		$setting[] = array("desc" => $fm["name"],
				"type" => "radio",
				"name" => "rd_fm_$fm_i",
				"value" => $values,
				"output" => $output,
				"checked" => $checked
				);
	}

	if(sizeof($setting) > 0) {
		$parts[$partscounter]["titel"] = getLL("admin_settings_fm");
		$parts[$partscounter]["settings"] = $setting;
	}


	//Limit-Einstellungen
	unset($setting);
	$s_counter = 0;
	if(ko_module_installed("admin", $uid)) {
		ko_get_access_all('admin', $uid, $max);
		if($max > 3) {
			$setting[$s_counter++] = array("desc" => getLL("admin_settings_limits_numberof_logins"),
					"type" => "text",
					"name" => "txt_limit_logins",
					"value" => ko_html(ko_get_userpref($uid, "show_limit_logins"))
					);
		}
	}
	if(ko_module_installed("fileshare", $uid)) {
		$setting[$s_counter++] = array("desc" => getLL("admin_settings_limits_numberof_shares"),
				"type" => "text",
				"name" => "txt_limit_fileshare",
				"value" => ko_html(ko_get_userpref($uid, "show_limit_fileshare"))
				);
	}
	if(ko_module_installed("tapes", $uid)) {
		$setting[$s_counter++] = array("desc" => getLL("admin_settings_limits_numberof_tapes"),
				"type" => "text",
				"name" => "txt_limit_tapes",
				"value" => ko_html(ko_get_userpref($uid, "show_limit_tapes"))
				);
	}

	if(sizeof($setting) > 0) {
		$parts[++$partscounter]["titel"] = getLL("admin_settings_limits");
		$parts[$partscounter]["settings"] = $setting;
	}



	//Default-Seiten pro Modul
	unset($setting);

	$m_counter = 0;
	if(ko_module_installed("admin", $uid)) {
		$descs = $values = array();
		ko_get_access_all('admin', $uid, $max);
		$values[] = 'set_layout';
		$descs[] = 'Layout';
		if($max > 1) {
			$values[] = 'set_allgemein';
			$values[] = 'list_news';
			$descs[] = getLL('submenu_admin_set_allgemein');
			$descs[] = getLL('submenu_admin_list_news');
		}
		if($max > 3) {
			$values[] = 'set_show_logins';
			$values[] = 'show_logs';
			$descs[] = getLL('submenu_admin_show_logins');
			$descs[] = getLL('submenu_admin_show_logs');
		}
		$setting[$m_counter++] = array('desc' => getLL('module_admin'),
				'type' => 'select',
				'name' => 'sel_admin',
				'values' => $values,
				'descs' => $descs,
				'value' => ko_html(ko_get_userpref($uid, 'default_view_admin'))
				);
	}
	if(ko_module_installed("tapes", $uid)) {
		$setting[$m_counter++] = array('desc' => getLL('module_tapes'),
				'type' => 'select',
				'name' => 'sel_tapes',
				'values' => array('list_tapes', 'list_series'),
				'descs' => array(getLL('submenu_tapes_list_tapes'), getLL('submenu_tapes_list_series')),
				'value' => ko_html(ko_get_userpref($uid, 'default_view_tapes'))
				);
	}
	if(ko_module_installed("fileshare", $uid)) {
		if(ENABLE_FILESHARE) {
			$values = array('inbox', 'list_webfolders', 'SPECIAL_webfolder');
			$descs = array(getLL('fileshare_inbox'), getLL('submenu_fileshare_list_webfolders'), getLL('fileshare_default_view_webfolder'));
		} else {
			$values = array('list_webfolders', 'SPECIAL_webfolder');
			$descs = array(getLL('submenu_fileshare_list_webfolders'), getLL('fileshare_default_view_webfolder'));
		}
		$setting[$m_counter++] = array('desc' => getLL('module_fileshare'),
				'type' => 'select',
				'name' => 'sel_fileshare',
				'values' => $values,
				'descs' => $descs,
				'value' => ko_html(ko_get_userpref($uid, 'default_view_fileshare'))
				);
	}

	//Default module after login
	$values = $descs = array('');
	foreach($MODULES as $m) {
		if(in_array($m, array('sms', 'kg', 'mailing'))) continue;
		if(!ko_module_installed($m, $uid)) continue;
		$values[] = $m;
		$descs[] = getLL('module_'.$m);
	}
	$setting[$m_counter++] = array('desc' => getLL('admin_settings_default_module'),
			'type' => 'select',
			'name' => 'sel_default_module',
			'values' => $values,
			'descs' => $descs,
			'value' => ko_get_userpref($uid, 'default_module')
			);


	if(sizeof($setting) > 0) {
		$parts[++$partscounter]["titel"] = getLL("admin_settings_default");
		$parts[$partscounter]["settings"] = $setting;
	}


	//settings for the menu/dropdown
	$values = $descs = $avalues = $adescs = NULL;
	unset($setting);
	$value = ko_get_userpref($uid, "modules_dropdown");  //Wert aus Userpref auslesen...
	if(!$value) $value = ko_get_setting("modules_dropdown");  //...oder falls nicht definiert aus Setting holen
	$setting[] = array("desc" => getLL("admin_settings_menu_submenus").":",
			"type" => "select",
			"name" => "sel_modules_dropdown",
			"values" => array("ja", "nein"),
			"descs" => array(getLL("admin_settings_menu_submenus_dd"), getLL("admin_settings_menu_submenus_nodd")),
			"value" => $value
			);

	//menu order
	$value = NULL;
	$value = ko_get_userpref($uid, "menu_order");  //Wert aus Userpref auslesen...
	//available modules for this user
	$values = $descs = $avalues = $adescs = NULL;
	foreach($MODULES as $m) {
		if(ko_module_installed($m, $uid)) {
			$values[] = $m;
			$descs[] = getLL("module_".$m);
		}
	}
	//selected menus
	foreach(explode(",", $value) as $m) {
		$avalues[] = $m;
		$adescs[] = getLL("module_".$m);
	}
	$setting[] = array("desc" => getLL("admin_settings_menu_order").":",
			"type" => "doubleselect",
		  "js_func_add" => "double_select_add",
			"show_moves" => TRUE,
			"name" => "sel_menu_order",
			"values" => $values,
			"descs" => $descs,
			"avalues" => $avalues,
			"adescs" => $adescs,
			"avalue" => $value,
			"params" => 'size="7"',
			);

	if(sizeof($setting) > 0) {
		$parts[++$partscounter]["titel"] = getLL("admin_settings_menu");
		$parts[$partscounter]["settings"] = $setting;
	}



	//Diverses
	unset($setting);

	//Show gsm notes
	if($uid != ko_get_guest_id()) {
		$value = ko_get_userpref($_SESSION['ses_userid'], 'show_notes');
		$setting[] = array('desc' => getLL('admin_settings_show_notes'),
			'type' => 'switch',
			'name' => 'show_notes',
			'label_0' => getLL('no'),
			'label_1' => getLL('yes'),
			'value' => $value == '' ? 0 : $value,
		);
		$value = ko_get_userpref($_SESSION['ses_userid'], 'save_kota_filter');
		$setting[] = array('desc' => getLL('admin_settings_save_kota_filter'),
			'type' => 'switch',
			'name' => 'save_kota_filter',
			'label_0' => getLL('no'),
			'label_1' => getLL('yes'),
			'value' => $value == '' ? 0 : $value,
		);
		$value = ko_get_userpref($_SESSION['ses_userid'], 'export_table_format');
		$setting[] = array('desc' => getLL('admin_settings_export_table_format'),
			'type' => 'select',
			'name' => 'export_table_format',
			'values' => array('xlsx', 'xls'),
			'descs' => array(getLL('admin_settings_export_table_format_xlsx'), getLL('admin_settings_export_table_format_xls')),
			'value' => $value
		);
	}
	$value = ko_get_userpref($_SESSION['ses_userid'], 'download_not_directly');
	$setting[] = array('desc' => getLL('admin_settings_download_not_directly'),
		'type' => 'switch',
		'name' => 'download_not_directly',
		'label_0' => getLL('no'),
		'label_1' => getLL('yes'),
		'value' => $value == '' ? 0 : $value,
	);

	//Erstellte Dateien in Fileshare-Folder speichern
	if(ENABLE_FILESHARE && ko_module_installed("fileshare", $uid) && $uid != ko_get_guest_id()) {
		ko_get_access_all('fileshare', $uid, $max);
		if($max > 1) {
			$value = ko_get_userpref($uid, "save_files_as_share");  //Wert aus Userpref auslesen...
			if(!isset($value)) $value = 2;  //...oder falls nicht definiert auf Nein setzen
			$setting[] = array("desc" => getLL("admin_settings_misc_save_fileshare"),
					"type" => "select",
					"name" => "sel_save_files_as_share",
					"values" => array("1", "2"),
					"descs" => array(getLL("yes"), getLL("no")),
					"value" => $value
					);
		}
	}

	if(sizeof($setting) > 0) {
		$parts[++$partscounter]["titel"] = getLL("admin_settings_misc");
		$parts[$partscounter]["settings"] = $setting;
	}



	$smarty->assign("tpl_parts", $parts);
	if($uid == ko_get_guest_id()) {
		$smarty->assign("help", ko_get_help("admin", "set_layout_guest"));
		$smarty->assign("tpl_action", "save_set_layout_guest");
	} else {
		$smarty->assign("help", ko_get_help("admin", "set_layout"));
		$smarty->assign("tpl_action", "save_set_layout");
	}

	$smarty->display("ko_settings.tpl");
}//ko_show_set_layout()





function ko_admin_check_ldap_login($login) {
	if(!ko_do_ldap()) return;

	$ldap = ko_ldap_connect();

	//Get login if id is given
	if(!is_array($login)) {
		ko_get_login($login, $_login);
		$login = $_login;
	}

	//Check for LDAP access right (Level 1 in login or one of the admingroups)
	$all_rights = ko_get_access_all('leute_admin', $login['id'], $max_rights);

	//Delete LDAP login
	if(ko_ldap_check_login($ldap, $login["login"])) {
		ko_ldap_del_login($ldap, $login["login"]);
	}
	//Add new ldap login if access is permitted
	if($max_rights > 0 || (defined('LDAP_EXPORT_ALL_LOGINS') && LDAP_EXPORT_ALL_LOGINS)) {
		$data = array("cn" => $login["login"], "sn" => $login["login"], "userPassword" => $login["password"]);
		//Add name and email if a person is assigned to this login
		if($login['leute_id'] > 0) {
			ko_get_person_by_id($login['leute_id'], $p);
			if($p['email']) $data['mail'] = $p['email'];
			if($p['vorname'] || $p['nachname']) $data['displayName'] = $p['vorname'].' '.$p['nachname'];
		}
		ko_ldap_add_login($ldap, $data);
	}

	ko_ldap_close($ldap);
}//ko_admin_check_ldap_login()




function ko_change_password() {
	global $smarty;
	global $access;

	if($access['admin']['MAX'] < 1) return;

	$smarty->assign("tpl_titel", getLL("admin_change_password"));

	$parts[0]["titel"] = getLL("admin_change_password");
	$setting[0]["desc"] = getLL("admin_change_password_old");
	$setting[0]["type"] = "password";
	$setting[0]["name"] = "txt_pwd_old";
	$setting[0]['params'] = 'size="40" maxlength="40" autocomplete="off"';
	$setting[0]["value"] = "";

	$setting[1]["desc"] = getLL("admin_change_password_new1");
	$setting[1]["type"] = "password";
	$setting[1]["name"] = "txt_pwd_new1";
	$setting[1]['params'] = 'size="40" maxlength="40" autocomplete="off"';
	$setting[1]["value"] = "";

	$setting[2]["desc"] = getLL("admin_change_password_new2");
	$setting[2]["type"] = "password";
	$setting[2]["name"] = "txt_pwd_new2";
	$setting[2]['params'] = 'size="40" maxlength="40" autocomplete="off"';
	$setting[2]["value"] = "";

	$parts[0]["settings"] = $setting;

	$smarty->assign("help", ko_get_help("admin", "change_password"));

	$smarty->assign("tpl_parts", $parts);
	$smarty->assign("tpl_action", "submit_change_password");
	$smarty->display("ko_settings.tpl");
}//ko_change_password()




/**
 * List currently available news
 */
function ko_list_news($output=TRUE) {
	global $access;

	if($access['admin']['MAX'] < 2) return;

	$order = 'ORDER BY '.$_SESSION['sort_news'].' '.$_SESSION['sort_news_order'];
	$rows = db_get_count('ko_news', 'id', '');
	$es = db_select_data('ko_news', 'WHERE 1=1', '*', $order);

	$list = new kOOL_listview();

	$list->init('admin', 'ko_news', array('chk', 'edit', 'delete'), 1, 100);
	$list->disableMultiedit();
	$list->setTitle(getLL('admin_news_list_title'));
	$list->setAccessRights(array('edit' => 2, 'delete' => 2), $access['admin']);
	$list->setActions(array('edit' => array('action' => 'edit_news'),
													'delete' => array('action' => 'delete_news', 'confirm' => TRUE))
										);
	$list->setSort(TRUE, 'setsort', $_SESSION['sort_news'], $_SESSION['sort_news_order']);
	$list->setStats($rows, '', '', '', TRUE);


	//Output the list
	if($output) {
		$list->render($es);
	} else {
		print $list->render($es);
	}
}//ko_list_news()



/**
 * Show form to enter and edit news. Uses fields as defined in KOTA
 */
function ko_formular_news($mode, $id='') {
	global $KOTA, $access;

	if($access['admin']['MAX'] < 2) return;

	if($mode == 'new') {
		$id = 0;
	} else if($mode == 'edit') {
		if(!$id) return FALSE;
	} else {
		return FALSE;
	}

	$form_data['title'] =  $mode == 'new' ? getLL('admin_news_form_title_new') : getLL('admin_news_form_title_edit');
	$form_data['submit_value'] = getLL('save');
	$form_data['action'] = $mode == 'new' ? 'submit_new_news' : 'submit_edit_news';
	if($mode == 'edit') {
		$form_data['action_as_new'] = 'submit_as_new_news';
		$form_data['label_as_new'] = getLL('admin_news_form_submit_as_new');
	}
	$form_data['cancel'] = 'list_news';

	ko_multiedit_formular('ko_news', '', $id, '', $form_data);
}//ko_formular_news()



?>
