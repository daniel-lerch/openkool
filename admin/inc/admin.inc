<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2003-2017 Renzo Lauper (renzo@churchtool.org)
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




function ko_set_logins_list() {
	global $smarty;
	global $access;

	if($access['admin']['MAX'] < 5) return FALSE;

	if($_SESSION["ses_username"] != "root") $z_where = " AND `login` != 'root' ";
	else $z_where = "";

	//Add KOTA filter
	$kota_where = kota_apply_filter('ko_admin');

	if($kota_where != '') $z_where .= " AND ($kota_where) ";

	$rows = db_get_count('ko_admin', 'id', $z_where);

	$z_limit = 'LIMIT ' . ($_SESSION['show_start']-1) . ', ' . $_SESSION['show_limit'];

	if($_SESSION['show_start'] > $rows) {
		$_SESSION['show_start'] = 1;
		$z_limit = 'LIMIT '.($_SESSION['show_start']-1).', '.$_SESSION['show_limit'];
	}

	ko_get_logins($es, $z_where, $z_limit);

	$list = new kOOL_listview();

	$list->init('admin', 'ko_admin', array('chk', 'edit', 'delete'), $_SESSION["show_start"], $_SESSION["show_limit"]);
	$list->setTitle(getLL("admin_logins_list_title"));
	$list->setActions(array('edit' => array('action' => 'edit_login'),
			'delete' => array('action' => 'delete_login', 'confirm' => TRUE))
	);
	if ($access['admin']['ALL'] > 4) $list->setActionNew('set_new_login');
	$list->setSort(TRUE, 'setsortlogins', $_SESSION['sort_logins'], $_SESSION['sort_logins_order']);
	$list->setStats($rows);
	$list->disableMultiedit();

	$manualAccess = array();
	foreach ($es as $k => $v) {
		if ($v['id'] == ko_get_guest_id()) {
			$manual_access[$k] = FALSE;
		}
		else {
			$manual_access[$k] = TRUE;
		}
	}
	$list->setAccessRights(FALSE);
	$list->setManualAccess('delete', $manual_access);

	$list->setRowClass('ko_list_hidden', "return 'DISABLED';");

	$list->setWarning(kota_filter_get_warntext('ko_admin'));

	//Footer
	$list_footer = $smarty->get_template_vars('list_footer');
	$list_footer[] = array("label" => getLL("admin_change_user_label"),
		"button" => '<button type="submit" class="btn btn-sm btn-default" onclick="set_action(\'sudo_login\');" value="'.getLL("admin_change_user").'">' . getLL("admin_change_user") . '</button>');
	$list->setFooter($list_footer);

	//Output the list
	$list->render($es);
}//ko_set_logins_list()




function ko_list_admingroups() {
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
	if ($access['admin']['ALL'] > 4) $list->setActionNew('set_new_admingroup');
	$list->setSort(FALSE);
	$list->setStats($rows, '', '', '', TRUE);
	$list->disableMultiedit();

	$list->setWarning(kota_filter_get_warntext('ko_admingroups'));

	//Footer
	$list_footer = $smarty->get_template_vars('list_footer');
	$list->setFooter($list_footer);

	//Output the list
	$list->render($es);
}//ko_list_admingroups()





function ko_show_logs() {
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

	//Add KOTA filter
	$kota_where = kota_apply_filter('ko_log');
	if($kota_where != '') $z_where .= " AND ($kota_where) ";


	//Limit-Filter setzen
	$rows = db_get_count('ko_log', 'id', $z_where);

	// reset pagination when we dont have results
	if ($_SESSION['show_logs_start'] > $rows) {
		$_SESSION['show_logs_start'] = 1;
	}

	if($_SESSION['show_logs_start'] && $_SESSION['show_logs_limit']) {
		$z_limit = 'LIMIT '.($_SESSION['show_logs_start']-1).', '.$_SESSION['show_logs_limit'];
	}

	$order = 'ORDER BY '.$_SESSION['sort_logs'].' '.$_SESSION['sort_logs_order'].', id '.$_SESSION['sort_logs_order'];
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
	$list->render($es);
}//ko_show_logs()





function ko_show_sms_log() {
	global $access, $smarty;

	if($access['admin']['MAX'] < 1) return false;
	if(!ko_module_installed('sms')) return false;

	$z_where = "AND (`type` = 'sms_sent' OR `type` = 'sms_mark')";
	//Apply filters
	if($_SESSION['log_user'] > 0) $z_where .= " AND `user_id`='".$_SESSION['log_user']."' ";
	if($_SESSION['log_time'] > 0) $z_where .= ' AND (TO_DAYS(CURDATE()) - TO_DAYS(`date`)) < '.(int)$_SESSION['log_time'].' ';
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
		$first = True;
		foreach($marks as $date => $mark) {
			if (!$first) $mark_stats .= '<br />';
			$mark_stats .= substr($date, 0, 10).' - '.$last.': '.$mark;
			$last = substr($date, 0, 10);
			$first = False;
		}
		$list_footer[] = array('label' => $mark_stats, 'button' => '');

		//Button to add new marker
		$list_footer[] = array('label' => getLL('admin_sms_log_mark_label'), 'button' => '<button type="submit" class="btn btn-sm btn-primary" name="sms_mark" onclick="set_action(\'sms_log_mark\');" value="'.getLL('OK').'">' . getLL('OK') . '</button>');
	}

	$list->setFooter($list_footer);


	$list->render($logs);
}//ko_show_sms_log()



function ko_list_google_cloud_printers() {
	global $access, $smarty;

	if($access['admin']['MAX'] < 5) return FALSE;

	$es = ko_get_available_google_cloud_printers();

	$list = new kOOL_listview();
	$list->init('admin', 'ko_google_cloud_printers', array(), 1, sizeof($es));
	$list->setTitle(getLL("admin_google_cloud_printers_list_title"));
	$list->setSort(FALSE);
	$list->setStats(sizeof($es));
	$list->disableMultiedit();

	//Footer
	$list_footer = $smarty->get_template_vars('list_footer');
	$list_footer[] = array("label" => getLL("admin_refresh_google_cloud_printers_label"),
		"button" => '<button type="button" class="btn btn-sm btn-primary" onclick="jumpToUrl(\'?action=refresh_google_cloud_printers\');">' . getLL("admin_refresh_google_cloud_printers") . '</button>');
	$list->setFooter($list_footer);

	//Output the list
	$list->render($es);
}


function ko_list_qz_tray_printers() {
	global $access, $BASE_PATH;

	if($access['admin']['MAX'] < 5) return FALSE;

	$host = ko_get_setting('qz_tray_host') ?: 'localhost';
?>
	<h3>QZ Tray Printers</h3>
	<table class="table table-bordered table-condensed">
		<thead>
			<tr class="row-info">
				<th><?= getLL('admin_qz_tray_printer_name') ?></th>
				<th></th>
			</tr>
		</thead>
		<tbody id="qzTrayPrinters">
		</tbody>
	</table>
	<script src="/inc/qz-tray/rsvp-3.1.0.min.js" charset="UTF-8"></script>
	<script src="/inc/qz-tray/sha-256.min.js" charset="UTF-8"></script>
	<script src="/inc/qz-tray/qz-tray.js" charset="UTF-8"></script>
	<script>
qz.security.setCertificatePromise(function(resolve,reject) {
	$.ajax({url:'/inc/ajax.php',cache:false,dataType:'text',data:{sesid:'<?= session_id() ?>',action:'getcert'}}).then(resolve,reject);
});
qz.security.setSignaturePromise(function(toSign) {
	return function(resolve,reject) {
		$.ajax({url:'/inc/ajax.php',data:{sesid:'<?= session_id() ?>',action:'rsasign',sign:toSign}}).then(resolve,reject);
	};
});
qz.websocket.connect({host:'<?= $host ?>'}).then(function() {
	qz.printers.find().then(function(data) {
		var testpage = '<h1><center><?= getLL('admin_qz_tray_printer_testpage') ?></center></h1><center><img src="data:image/jpeg;base64,<?= base64_encode(file_get_contents($BASE_PATH.'images/logo_200.jpg')) ?>" /></center>';
		data.forEach(function(name) {
			var testPrintButton = $('<a>').addClass('btn btn-default btn-sm').text('<?= getLL('admin_qz_tray_printer_testpage') ?>').click(function() {
				qz.print(
					qz.configs.create(
						name,
						{
							units:'mm',
							margins:20
						}
					),
					[{
						type:'html',
						format:'plain',
						data:testpage
					}]
				).catch(function(error) {
					alert(error);
				});
			});
			$('#qzTrayPrinters').append(
				$('<tr>').append(
					$('<td>').text(name)
				).append(
					$('<td>').append(testPrintButton)
				)
			);
		});
	});
});
	</script>
<?php
}




function ko_login_formular($mode, $id=0, $type="login") {
	global $smarty, $ko_path, $MODULES, $MODULES_GROUP_ACCESS, $BASE_PATH, $BOOTSTRAP_COLS_PER_ROW;
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
			$_POST["chk_disable_password_change"] = $admingroups[$id]["disable_password_change"];

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
			$_POST["chk_disable_password_change"] = $login["disable_password_change"];
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
	$group[$gc]["row"][$rowcounter]["inputs"][0] = array("desc" => $type == "login" ? getLL("admin_logins_name") : getLL("admin_admingroup"),
																 "type" => "text",
																 "name" => "txt_name",
																 "value" => ko_html($_POST["txt_name"]),
															 	 "colspan" => 'colspan="3"',
																 );
	$group[$gc]['row'][$rowcounter++]['inputs'][1] = array(
		'desc' => getLL("admin_logins_disable_password_change"),
		'type' => 'switch',
		'name' => 'chk_disable_password_change',
		'value' => ($_POST['chk_disable_password_change'] == 1 ? 1 : 0),
	);

	//Password
	if(!$hide_password && $type == "login") {
		$group[$gc]["row"][$rowcounter]["inputs"][0] = array("desc" => getLL("admin_logins_new_password"),
																	 "type" => "password",
																	 "name" => "txt_pwd1",
																	 "value" => "",
																	 "params" => 'size="40" maxlength="40" autocomplete="false"',
																 	 "colspan" => 'colspan="3"',
																	 );
		$group[$gc]["row"][$rowcounter++]["inputs"][1] = array("desc" => getLL("admin_logins_new_password2"),
																	 "type" => "password",
																	 "name" => "txt_pwd2",
																	 "value" => "",
																	 "params" => 'size="40" maxlength="40" autocomplete="false"',
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
																	 "params" => 'maxlength="255"',
																	 "colspan" => 'colspan="3"',
																	 );
		$group[$gc]['row'][$rowcounter]['inputs'][1] = array('desc' => getLL('admin_logins_mobile'),
																	 'type' => 'text',
																	 'name' => 'txt_mobile',
																	 'value' => ko_html($_POST['txt_mobile']),
																	 'params' => 'maxlength="255"',
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
			"colspan" => 'colspan="6"',
			"columnWidth" => $BOOTSTRAP_COLS_PER_ROW,
		);
		$values = $descs = array(0, 1, 2, 3, 4);
		$group[$gc]["row"][$rowcounter]["inputs"][0] = array("desc" => strtoupper(getLL("all")),
			"type" => "select",
			"name" => "sel_rechte_leute",
			"all_class" => 'sel_rechte',
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
				"all_class" => 'sel_rechte',
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
						'type' => 'switch',
						'name' => 'chk_leute_admin_assign',
						'value' => $value ? '1' : '0',
						'colspan' => 'colspan="2"',
						);


			//Setting to enable group subscriptions for users without level 4
			$value = $type == 'login' ? $login['leute_admin_gs'] : $admingroups[$id]['leute_admin_gs'];
			$group[$gc]["row"][$rowcounter++]["inputs"][0] = array("desc" => getLL("admin_logins_rights_leute_gs"),
						"type" => "switch",
						"name" => "chk_leute_admin_gs",
						'value' => $value ? '1' : '0',
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
			case "donations": $gruppen = db_select_data("ko_donations_accounts", "", "*", "ORDER BY number ASC, name ASC"); break;
			case 'tracking': $gruppen = db_select_data('ko_tracking', '', '*', 'ORDER BY name ASC'); break;
			case 'crm':
				ko_get_crm_projects($gruppen, '', '', 'ORDER BY `title` ASC');
				foreach ($gruppen as $k => $g) {
					$gruppen[$k]['name'] = $g['title'];
				}
			break;
			case 'subscription': $gruppen = db_select_data('ko_subscription_form_groups','','*','ORDER BY name ASC'); break;

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
			"colspan" => 'colspan="6"',
			"columnWidth" => $BOOTSTRAP_COLS_PER_ROW,
		);
		$values = $descs = array(0, 1, 2, 3, 4);
		if(in_array($module, array('reservation', 'rota', 'crm'))) $values = $descs = array(0, 1, 2, 3, 4, 5);
		if($module == 'subscription') $values = $descs = array(0,1,2);
		//Get levels from function (e.g. for plugins)
		if(TRUE === hook_access_get_levels($module, $_values, $_descs)) {
			$values = $_values;
			$descs = $_descs;
		}

		//Allow plugins to change access levels
		hook_access_levels_preprocess($module, $user_access[$module], 'login', $id);

		$group[$gc]["row"][$rowcounter]["inputs"][0] = array("desc" => strtoupper(getLL("all")),
			"type" => "select",
			"name" => "sel_rechte_".$module."_0",
			"add_class" => 'sel_rechte',
			"values" => $values,
			"descs" => $descs,
			"value" => $user_access[$module]['ALL'],
			"params" => 'size="0"',
			'buttons' => '<div class="input-group-btn"><button class="btn btn-default access_apply_all" id="rechte_'.$module.'_0" title="'.getLL('admin_logins_apply_all_title').'"><span class="glyphicon glyphicon-chevron-right"></span></button></div>',
		);

		//Gruppen-Rechte
		$col = 1;
		foreach($gruppen as $g_i => $g) {
			$group[$gc]["row"][$rowcounter]["inputs"][$col++] = array("desc" => ko_html($g["name"]),
				"type" => "select",
				"name" => "sel_rechte_".$module."_".$g_i,
				"add_class" => 'sel_rechte',
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
				"type" => "select",
				"name" => "sel_force_global_".$module,
				"values" => array('0', '1', '2'),
				"descs" => array(getLL('force_global_filter_0'), getLL('force_global_filter_1'), getLL('force_global_filter_2')), 
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

				if(($type == 'login' && $id != ko_get_guest_id()) || $type == 'admingroup') {
					$absence_rights = db_select_data('ko_admin' . ($type == "login" ? '' : 'groups'), "where `id` = $id", "event_absence_rights", '', '', TRUE, TRUE);
					$group[$gc]["row"][++$rowcounter]["inputs"][0] = array("desc" => getLL('absence_rights'),
						"type" => "select",
						"name" => "sel_absence_rights_daten",
						"values" => array('0', '1', '2', '3'),
						"descs" => array(getLL('absence_rights_0'), getLL('absence_rights_1'), getLL('absence_rights_2'), getLL('absence_rights_3')),
						"value" => $absence_rights['event_absence_rights']
					);
				}
			}
		}


		if($module == 'daten') {
			$group[$gc]['row'][++$rowcounter]['inputs'][0] = ko_access_get_kota_columns_form($id, 'ko_event', $type);
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
			'colspan' => 'colspan="6"',
			"columnWidth" => $BOOTSTRAP_COLS_PER_ROW,
		);
		$values = $descs = array(0, 1, 2, 3, 4);
		$group[$gc]['row'][$rowcounter++]['inputs'][0] = array('desc' => strtoupper(getLL('all')),
			'type' => 'select',
			'name' => 'sel_rechte_groups',
			"add_class" => 'sel_rechte',
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
			"colspan" => 'colspan="6"',
			"columnWidth" => $BOOTSTRAP_COLS_PER_ROW,
		);
		$values = $descs = array(0, 1, 2, 3, 4);
		if(in_array($module , array('admin'))) $values = $descs = array(0, 1, 2, 3, 4, 5);
		else if(in_array($module , array('vesr','taxonomy'))) $values = $descs = array(0, 1, 2);
		//Get levels from function (e.g. for plugins)
		if(TRUE === hook_access_get_levels($module, $_values, $_descs)) {
			$values = $_values;
			$descs = $_descs;
		}
		$group[$gc]["row"][$rowcounter]["inputs"][0] = array("desc" => "",
			"type" => "select",
			"name" => "sel_rechte_".$module,
			"add_class" => 'sel_rechte',
			"values" => $values,
			"descs" => $descs,
			"value" => $user_access[$module]['ALL'],
			"params" => 'size="0"',
		);

		//Show KOTA columns this user should have access to. So far only for ko_kleingruppen
		if($module == 'kg') {
			$group[$gc]['row'][++$rowcounter]['inputs'][0] = ko_access_get_kota_columns_form($id, 'ko_kleingruppen', $type);
		}

	}


	//Allow plugins to change form
	hook_form('ko_admingroups', $group, $mode, $id, array('type' => $type));


	if($type == "login") {
		$smarty->assign("tpl_titel", ( ($mode == "neu") ? getLL("admin_new_login") : getLL("admin_edit_login")) );
		$smarty->assign('help', ko_get_help('admin', 'set_new_login'));
	} else {
		$smarty->assign("tpl_titel", ( ($mode == "neu") ? getLL("admin_new_admingroup") : getLL("admin_edit_admingroup")) );
		$smarty->assign('help', ko_get_help('admin', 'set_new_admingroup'));
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



function ko_show_vesr_import() {
	global $access, $BASE_PATH;

	if($access['vesr'] < 1) return FALSE;

	//Upload form
	$c = '';
	$c .= '<h1>'.getLL('vesr_title').'</h1>';
	$c .= '<p>'.getLL('vesr_description').'</p>';
	$c .= '<form action="index.php" method="post" name="formular" enctype="multipart/form-data">';
	$c .= '<input type="hidden" name="action" value="submit_vesr_import" />';
	$c .= '<input type="file" name="esrpayment_file" /><br />';

	if ($_SESSION['ses_userid'] == ko_get_root_id()) {
		$c .= getLL('vesr_preview_message') . ' <input type="checkbox" name="preview" /><br /><br />';
	}

	$c .= '<input type="submit" value="'.getLL('OK').'" />';
	$c .= '</form>';

	print $c;


	if(db_get_count('ko_vesr', 'id') > 0) {
		$order = 'ORDER BY `crdate` DESC';
		$rows = db_get_count('ko_vesr', 'id');
		$es = db_select_data('ko_vesr', 'WHERE 1=1', '*', $order);

		$list = new kOOL_listview();
		$list->init('admin', 'ko_vesr', array('delete'), 1, 1000);
		$list->setTitle(getLL('vesr_v11_list_title'));
		$list->setActions(array('delete' => array('action' => 'delete_v11', 'confirm' => TRUE)));
		$list->setSort(FALSE);
		$list->setStats($rows, '', '', '', TRUE);
		$list->disableMultiedit();

		//TODO: Add payed-icons to billed_amount and amount columns if billing_id is set on vesr entry

		print '<br clear="all" /><br /><br />';
		print $list->render($es);
	}


	if(db_get_count('ko_vesr_camt', 'id') > 0) {
		$order = 'ORDER BY `crdate` DESC';
		$rows = db_get_count('ko_vesr_camt', 'id');
		$es = db_select_data('ko_vesr_camt', 'WHERE 1=1', '*', $order);

		$list = new kOOL_listview();
		$list->init('admin', 'ko_vesr_camt', array('delete'), 1, 1000);
		$list->setTitle(getLL('vesr_camt_list_title'));
		$list->setActions(array('delete' => array('action' => 'delete_camt', 'confirm' => TRUE)));
		$list->setSort(FALSE);
		$list->setStats($rows, '', '', '', TRUE);
		$list->disableMultiedit();

		//TODO: Add payed-icons to billed_amount and amount columns if billing_id is set on vesr entry

		print '<br clear="all" /><br /><br />';
		print $list->render($es);
	}
}



function ko_vesr_settings() {
	global $BASE_PATH, $smarty;

	$gc = $rowcounter = 0;
	$frmgroup = array($gc => array('titel' => getLL('vesr_settings_group_import_email')));

	$value = ko_get_setting('vesr_import_email_host');
	$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('vesr_settings_import_email_host'),
		'type' => 'text',
		'name' => 'txt_vesr_import_email_host',
		'params' => 'size="60"',
		'value' => $value,
	);
	$value = ko_get_setting('vesr_import_email_port');
	$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('vesr_settings_import_email_port'),
		'type' => 'text',
		'name' => 'chk_vesr_import_email_port',
		'value' => $value,
	);

	$value = ko_get_setting('vesr_import_email_user');
	$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('vesr_settings_import_email_user'),
		'type' => 'text',
		'name' => 'txt_vesr_import_email_user',
		'params' => 'size="60"',
		'value' => $value,
	);

	$value = ko_get_setting('vesr_import_email_pass');
	// decrypt password
	include_once($BASE_PATH.'inc/class.mcrypt.php');
	$crypt = new mcrypt('aes');
	$crypt->setKey(KOOL_ENCRYPTION_KEY);
	$value = trim($crypt->decrypt($value));
	$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('vesr_settings_import_email_pass'),
		'type' => 'text',
		'name' => 'txt_vesr_import_email_pass',
		'value' => $value,
	);

	$value = ko_get_setting('vesr_import_email_ssl');
	$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('vesr_settings_import_email_ssl'),
		'type' => 'switch',
		'name' => 'chk_vesr_import_email_ssl',
		'value' => $value,
	);

	$value = ko_get_setting('vesr_import_email_report_address');
	$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('vesr_settings_import_email_report_address'),
		'type' => 'text',
		'name' => 'txt_vesr_import_email_report_address',
		'value' => $value,
	);

	if ($_SESSION['ses_userid'] == ko_get_root_id()) {
		$gc++;
		$frmgroup[$gc] = array('titel' => getLL('vesr_settings_group_import_camt'));

		$value = ko_get_setting('camt_import_host');
		$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('vesr_settings_import_camt_import_host'),
			'type' => 'text',
			'name' => 'txt_camt_import_host',
			'value' => $value,
		);

		$value = ko_get_setting('camt_import_port');
		$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('vesr_settings_import_camt_import_port'),
			'type' => 'text',
			'name' => 'txt_camt_import_port',
			'value' => $value,
		);

		$value = ko_get_setting('camt_import_user');
		$frmgroup[$gc]['row'][$rowcounter++]['inputs'][0] = array('desc' => getLL('vesr_settings_import_camt_import_user'),
			'type' => 'text',
			'name' => 'txt_camt_import_user',
			'value' => $value,
		);

		$value = str_replace("\r", '', ko_get_setting('camt_import_private_key'));
		$html = '<textarea class="form-control input-sm" rows="10" name="txt_camt_import_private_key">' . $value . '</textarea>';
		$html .= '<button type="button" class="btn btn-sm btn-danger" onclick="c = confirm(\''.getLL('vesr_settings_import_camt_regenerate_keys_confirm').'\'); if (!c) return false; else sendReq(\'../admin/inc/ajax.php\', [\'action\', \'sesid\'], [\'regeneratecamtkeys\', kOOL.sid], do_element);">' . getLL('vesr_settings_import_camt_regenerate_keys') . '</button>';
		$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('vesr_settings_import_camt_import_private_key'),
			'type' => 'html',
			'name' => 'txt_camt_import_private_key',
			'value' => $html,
		);

		$value = str_replace("\r", '', ko_get_setting('camt_import_public_key'));
		$html = '<textarea class="input-sm form-control" rows="10" name="txt_camt_import_public_key">' . $value . '</textarea>';
		$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('vesr_settings_import_camt_import_public_key'),
			'type' => 'html',
			'name' => 'txt_camt_import_public_key',
			'value' => $html,
		);


		$gc++;
		$frmgroup[$gc] = array('titel' => getLL('vesr_settings_donation'));

		$value = ko_get_setting('currencyconverterapi_key');
		$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('vesr_settings_currencyconverterapi_key'),
			'type' => 'text',
			'name' => 'txt_currencyconverterapi_key',
			'value' => $value,
		);
	}

	//Allow plugins to add further settings
	hook_form('vesr_settings', $frmgroup, '', '');


	//display the form
	$smarty->assign('tpl_titel', getLL('vesr_settings_form_title'));
	$smarty->assign('tpl_submit_value', getLL('save'));
	$smarty->assign('tpl_action', 'submit_vesr_settings');
	$smarty->assign('tpl_cancel', $_SESSION['show_back']?$_SESSION['show_back']:'show_logins');
	$smarty->assign('tpl_groups', $frmgroup);

	$smarty->display("ko_formular.tpl");
}




function ko_admin_settings() {
	global $smarty, $ko_path;
	global $access, $MODULES, $SMS_PARAMETER, $MAILING_PARAMETER;


	//build form
	$gc = 0;
	$rowcounter = 0;

	if($access['admin']['MAX'] < 2) {
		$doGeneral = false;
	}
	else {
		$doGeneral = true;
		$titleGeneral = getLL('admin_settings_general_settings');
		$helpGeneral = ko_get_help("admin", "set_allgemein");
	}

	if($access['admin']['MAX'] < 1) {
		$doLayout = false;
	}
	else {
		$doLayout = true;
		$titleLayout = getLL("admin_settings_layout");
		$helpLayout = ko_get_help("admin", "set_layout");
	}

	if ($access['admin']['MAX'] < 3) {
		$doGuest = false;
	}
	else {
		$doGuest = true;
		$titleGuest = getLL("admin_settings_layout_guest");
		$helpGuest = ko_get_help("admin", "set_layout_guest");
	}

	$doSomething = $doGeneral || $doLayout || $doGuest;
	$doOther = ($doGeneral + $doLayout + $doGuest) > 1;




	if ($doGeneral) {
		if ($doOther) {
			$frmgroup[$gc]['titel'] = $titleGeneral;
			$frmgroup[$gc]['help'] = $helpGeneral;
			$frmgroup[$gc]['tab'] = true;
			$gc++;
		}

		$frmgroup[$gc]['titel'] = getLL('admin_settings_contact');

		$contact_fields = array('name', 'address', 'zip', 'city', 'phone', 'url', 'email');
		$colCounter = 0;
		foreach($contact_fields as $field) {
			$frmgroup[$gc]['row'][$rowcounter]['inputs'][$colCounter] = array('desc' => getLL('admin_settings_contact_'.$field),
				'type' => 'text',
				'name' => 'txt_contact_'.$field,
				'value' => ko_html(ko_get_setting('info_'.$field)),
			);
			$colCounter++;
			if ($colCounter > 1) {
				$colCounter = 0;
				$rowcounter++;
			}
		}

		$frmgroup[$gc]['row'][$rowcounter]['inputs'][$colCounter] = array('desc' => getLL('admin_settings_pp_addresses'),
			'type' => 'textarea',
			'name' => 'txt_pp_addresses',
			'value' => ko_get_setting('pp_addresses'),
			'params' => 'rows="4"',
		);

		$gc++;
		$frmgroup[$gc]['titel'] = getLL("admin_settings_options");
		$rowcounter = 0;

		if(in_array('leute', $MODULES)) {
			$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL("admin_settings_options_login_edit_person"),
				'type' => 'switch',
				'name' => 'rd_login_edit_person',
				'value' => ko_html(ko_get_setting("login_edit_person")),
			);
		}

		$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL("admin_settings_options_change_password"),
			'type' => 'switch',
			'name' => 'rd_change_password',
			'value' => ko_html(ko_get_setting("change_password")),
		);

		if(in_array('sms', $MODULES)) {
			$gc++;
			$frmgroup[$gc]['titel'] = getLL('admin_settings_sms');
			$rowcounter = 0;

			$sender_ids = array_filter(explode(',', ko_get_setting('sms_sender_ids')), function($e) {return $e?TRUE:FALSE;});
			$v = array();
			foreach($sender_ids as $id) {
				$v[] =
					'<div class="btn-group btn-group-sm">
	<button class="btn btn-default" disabled>
		'.ko_html($id).'
	</button>
	<button class="btn btn-danger" type="button" onclick="c=confirm(\''.getLL('admin_settings_sms_confirm_delete_sender_id').'\'); if(!c) { return false; } else { jumpToUrl(\'index.php?action=delete_sms_sender_id&sender_id='.urlencode($id).'\'); }">
		<i class="fa fa-trash"></i>
	</button>
</div>';
			}
			if (sizeof($v) > 0) {
				$v = implode('&nbsp;', $v);
			} else {
				$v = getLL('none');
			}

			$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('admin_settings_sms_sender_ids'),
				'type' => 'html',
				'value' => $v,
			);

			if($SMS_PARAMETER['provider'] == 'aspsms') {
				$v = '<div class="input-group input-group-sm">';
				if((check_natel($_POST['sms_sender_id']) && $_POST['submit_sms_sender_id'])) {
					$v .= '<input class="input-sm form-control" type="text" name="sms_sender_id" value="'.$_POST['sms_sender_id'].'" maxlength="11">';
					$v .= '<span class="input-group-addon">' . getLL('admin_settings_sms_new_sender_id_code').'</span><input class="input-sm form-control" type="text" name="sms_sender_id_code" size="10">';
				} else {
					$v .= '<input class="input-sm form-control" type="text" name="sms_sender_id" value="" maxlength="11">';
				}
				$v .= '<div class="input-group-btn"><button type="submit" class="btn btn-primary" name="submit_sms_sender_id" onclick="set_action(\'submit_sms_sender_id\');" value="'.getLL('OK').'">' . getLL('OK') . '</button></div>';
				$v .= '</div>';
				$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('admin_settings_sms_new_sender_id'),
					'type' => 'html',
					'value' => $v,
				);
			} else {
				$v = '<div class="input-group input-group-sm">';
				$v .= '<input class="input-sm form-control" type="text" name="sms_sender_id" value="">';
				$v .= '<div class="input-group-btn">';
				$v .= '<button class="btn btn-primary" type="submit" name="submit_sms_sender_id_clickatell" onclick="set_action(\'submit_sms_sender_id_clickatell\');" value="'.getLL('OK').'">';
				$v .= getLL('OK');
				$v .= '</button>';
				$v .= '</div>';
				$v .= '</div>';

				$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('admin_settings_sms_new_sender_id_clickatell'),
					'type' => 'html',
					'value' => $v,
				);
			}

			$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('admin_settings_sms_country_code'),
				'type' => 'text',
				'name' => 'txt_sms_country_code',
				'value' => ko_html(ko_get_setting('sms_country_code')),
			);
		}


		if(in_array('mailing', $MODULES) && is_array($MAILING_PARAMETER) && $MAILING_PARAMETER['domain'] != '') {
			$gc++;
			$frmgroup[$gc]['titel'] = getLL('admin_settings_mailing');
			$rowcounter = 0;

			$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('admin_settings_mailing_mails_per_cycle'),
				'type' => 'text',
				'name' => 'txt_mailing_mails_per_cycle',
				'value' => ko_html(ko_get_setting('mailing_mails_per_cycle')),
			);

			$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('admin_settings_mailing_max_recipients'),
				'type' => 'text',
				'name' => 'txt_mailing_max_recipients',
				'value' => ko_html(ko_get_setting('mailing_max_recipients')),
			);

			$value = ko_get_setting('mailing_only_alias');
			$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('admin_settings_mailing_only_alias'),
				'type' => 'switch',
				'value' => $value ? '1' : '0',
				'name' => 'chk_mailing_only_alias',
			);

			$value = ko_get_setting('mailing_allow_double');
			$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('admin_settings_mailing_allow_double'),
				'type' => 'switch',
				'name' => 'chk_mailing_allow_double',
				'value' => $value ? '1' : '0',
			);

			$value = ko_get_setting('mailing_from_email');
			$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array(
				'desc' => getLL('admin_settings_mailing_from_email'),
				'type' => 'text',
				'name' => 'txt_mailing_from_email',
				'value' => $value,
			);
			$value = ko_get_setting('force_mail_from');
			$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array(
				'desc' => getLL('admin_settings_force_mail_from'),
				'type' => 'switch',
				'name' => 'chk_force_mail_from',
				'value' => $value,
			);

			$value = ko_get_setting('mailing_max_attempts');
			$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array(
				'desc' => getLL('admin_settings_mailing_max_attempts'),
				'type' => 'text',
				'name' => 'txt_mailing_max_attempts',
				'value' => $value,
			);
		}



		//XLS export settings
		$gc++;
		$frmgroup[$gc]['titel'] = getLL('admin_settings_xls');
		$rowcounter = 0;

		$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('admin_settings_xls_default_font'),
			'type' => 'text',
			'name' => 'txt_xls_default_font',
			'value' => ko_html(ko_get_setting('xls_default_font')),
		);

		$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('admin_settings_xls_title_font'),
			'type' => 'text',
			'name' => 'txt_xls_title_font',
			'value' => ko_html(ko_get_setting('xls_title_font')),
		);

		$value = ko_get_setting('xls_title_bold');
		$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('admin_settings_xls_title_bold'),
			'type' => 'switch',
			'name' => 'chk_xls_title_bold',
			'value' => $value ? '1' : '0',
		);

		$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('admin_settings_xls_title_color'),
			'type' => 'select',
			'name' => 'txt_xls_title_color',
			'values' => array('blue', 'black', 'cyan', 'brown', 'magenta', 'grey', 'green', 'orange', 'purple', 'red', 'yellow'),
			'descs' => array(getLL('admin_settings_xls_title_color_blue'), getLL('admin_settings_xls_title_color_black'), getLL('admin_settings_xls_title_color_cyan'), getLL('admin_settings_xls_title_color_brown'), getLL('admin_settings_xls_title_color_magenta'), getLL('admin_settings_xls_title_color_grey'), getLL('admin_settings_xls_title_color_green'), getLL('admin_settings_xls_title_color_orange'), getLL('admin_settings_xls_title_color_purple'), getLL('admin_settings_xls_title_color_red'), getLL('admin_settings_xls_title_color_yellow')),
			'value' => ko_get_setting('xls_title_color'),
			'params' => $value ? 'checked="checked"' : '',
		);

		if($access['admin']['MAX'] >= 5) {
			$gc++;
			$frmgroup[$gc]['titel'] = getLL('admin_settings_qz_tray');
			$frmgroup[$gc]['row'][0]['inputs'][0] = [
				'name' => 'qz_tray_enable',
				'desc' => getLL('admin_settings_qz_tray_enable'),
				'type' => 'switch',
				'value' => ko_get_setting('qz_tray_enable'),
			];
			$frmgroup[$gc]['row'][0]['inputs'][1] = [
				'name' => 'qz_tray_host',
				'desc' => getLL('admin_settings_qz_tray_host'),
				'type' => 'text',
				'value' => ko_get_setting('qz_tray_host'),
				'params' => 'placeholder="localhost"',
			];
		}

		$gc++;

	}





	if ($doLayout) {
		if ($doOther) {
			$frmgroup[$gc]['titel'] = $titleLayout;
			$frmgroup[$gc]['help'] = $helpLayout;
			$frmgroup[$gc]['tab'] = true;
			$gc++;
		}
		$uid = $_SESSION['ses_userid'];
		//Default-Seiten pro Modul
		$settings = array();
		//Default module after login
		$values = $descs = array('');
		foreach($MODULES as $m) {
			if(in_array($m, array('sms', 'kg', 'mailing'))) continue;
			if(!ko_module_installed($m, $uid)) continue;
			$values[] = $m;
			$descs[] = getLL('module_'.$m);
		}
		$settings[] = array('desc' => getLL('admin_settings_default_module'),
			'type' => 'select',
			'name' => 'sel_default_module',
			'values' => $values,
			'descs' => $descs,
			'value' => ko_get_userpref($uid, 'default_module')
		);
		if(ko_module_installed("admin", $uid)) {
			$descs = $values = array();
			ko_get_access_all('admin', $uid, $max);
			if($max > 1) {
				$values[] = 'admin_settings';
				$values[] = 'list_news';
				$descs[] = getLL('submenu_admin_admin_settings');
				$descs[] = getLL('submenu_admin_list_news');
			}
			if($max > 3) {
				$values[] = 'set_show_logins';
				$values[] = 'show_logs';
				$descs[] = getLL('submenu_admin_set_show_logins');
				$descs[] = getLL('submenu_admin_show_logs');
			}
			if (sizeof($values) > 0) {
				$settings[] = array('desc' => getLL('module_admin'),
					'type' => 'select',
					'name' => 'sel_admin',
					'values' => $values,
					'descs' => $descs,
					'value' => ko_html(ko_get_userpref($uid, 'default_view_admin'))
				);
			}
			$settings[] = array('desc' => getLL('admin_settings_limits_numberof_logins'),
				'type' => 'text',
				'name' => 'show_limit_logins',
				'value' => ko_get_userpref($uid, "show_limit_logins"),
			);
		}

		if(sizeof($settings) > 0) {
			$frmgroup[$gc]['titel'] = getLL("admin_settings_default");
			$colCounter = 0;
			foreach ($settings as $setting) {
				$frmgroup[$gc]["row"][$rowcounter]["inputs"][$colCounter++] = $setting;
				if ($colCounter > 1) {
					$colCounter = 0;
					$rowcounter++;
				}
			}
			$gc++;
		}


		//settings for the menu/dropdown
		$settings = array();

		//menu order
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
		$settings[] = array("desc" => getLL("admin_settings_menu_order").":",
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

		if(sizeof($settings) > 0) {
			$frmgroup[$gc]['titel'] = getLL("admin_settings_menu");
			$colCounter = 0;
			foreach ($settings as $setting) {
				$frmgroup[$gc]["row"][$rowcounter]["inputs"][$colCounter++] = $setting;
				if ($colCounter > 1) {
					$colCounter = 0;
					$rowcounter++;
				}
			}
			$gc++;
		}



		//Diverses
		$settings = array();

		//Show gsm notes
		if($uid != ko_get_guest_id()) {
			$value = ko_get_userpref($_SESSION['ses_userid'], 'save_kota_filter');
			$settings[] = array('desc' => getLL('admin_settings_save_kota_filter'),
				'type' => 'switch',
				'name' => 'save_kota_filter',
				'label_0' => getLL('no'),
				'label_1' => getLL('yes'),
				'value' => $value == '' ? 0 : $value,
			);
			$value = ko_get_userpref($_SESSION['ses_userid'], 'export_table_format');
			$settings[] = array('desc' => getLL('admin_settings_export_table_format'),
				'type' => 'select',
				'name' => 'export_table_format',
				'values' => array('xlsx', 'xls'),
				'descs' => array(getLL('admin_settings_export_table_format_xlsx'), getLL('admin_settings_export_table_format_xls')),
				'value' => $value
			);
		}
		$value = ko_get_userpref($_SESSION['ses_userid'], 'download_not_directly');
		$settings[] = array('desc' => getLL('admin_settings_download_not_directly'),
			'type' => 'switch',
			'name' => 'download_not_directly',
			'label_0' => getLL('no'),
			'label_1' => getLL('yes'),
			'value' => $value == '' ? 0 : $value,
		);

		if(sizeof($settings) > 0) {
			$frmgroup[$gc]['titel'] = getLL("admin_settings_misc");
			$colCounter = 0;
			foreach ($settings as $setting) {
				$frmgroup[$gc]["row"][$rowcounter]["inputs"][$colCounter++] = $setting;
				if ($colCounter > 1) {
					$colCounter = 0;
					$rowcounter++;
				}
			}
			$gc++;
		}
	}





	if ($doGuest) {
		$uid = ko_get_guest_id();
		if ($doOther) {
			$frmgroup[$gc]['titel'] = $titleGuest;
			$frmgroup[$gc]['help'] = $helpGuest;
			$frmgroup[$gc]['tab'] = true;
			$gc++;
		}
		//Default-Seiten pro Modul
		$settings = array();
		// frontmodules shown to guest
		$values = $descs = $avalues = $adescs = array();
		$values = array(
			'adressaenderung', 'daten_cal', 'news', 'today', 'absence'
		);
		foreach ($values as $value) {
			$descs[] = getLL('fm_name_' . $value);
		}
		$value = ko_get_userpref($uid, 'front_modules');
		foreach (explode(',', $value) as $aavalue) {
			for ($i = 0; $i < sizeof($values); $i++) {
				if ($values[$i] == $aavalue) {
					$avalues[] = $values[$i];
					$adescs[] = $descs[$i];
				}
			}
		}
		$settings[] = array('desc' => getLL('admin_settings_fm'),
			'type' => 'checkboxes',
			'name' => 'chks_front_modules_guest',
			'values' => $values,
			'descs' => $descs,
			'avalues' => $avalues,
			'adescs' => $adescs,
			'avalue' => $value,
			'size' => sizeof($values),
		);

		//Default module after login
		$values = $descs = array('');
		foreach($MODULES as $m) {
			if(in_array($m, array('sms', 'kg', 'mailing'))) continue;
			if(!ko_module_installed($m, $uid)) continue;
			$values[] = $m;
			$descs[] = getLL('module_'.$m);
		}
		$settings[] = array('desc' => getLL('admin_settings_default_module'),
			'type' => 'select',
			'name' => 'sel_default_module_guest',
			'values' => $values,
			'descs' => $descs,
			'value' => ko_get_userpref($uid, 'default_module')
		);

		$settings[] = [
			'desc' => getLL('admin_settings_fm_absence_text'),
			'type' => 'textarea',
			'name' => 'txt_fm_absence_infotext',
			'value' => ko_get_userpref($uid,'fm_absence_infotext'),
			'params' => 'rows="4"',
		];

		$filterset = array_merge((array)ko_get_userpref('-1', '', 'filterset', 'ORDER BY `key` ASC'), (array)ko_get_userpref($_SESSION['ses_userid'], '', 'filterset', 'ORDER BY `key` ASC'));
		$values_template[] = $descs_template[] = "";
		$values_template[] = '';
		$descs_template[] = '--- '.getLL('filter_filterpreset').' ---';
		foreach($filterset as $f) {
			$values_template[$f["key"]] = $f['key'];
			$descs_template[$f["key"]] = $f['user_id'] == '-1' ? getLL('itemlist_global_short').' '.$f["key"] : $f['key'];
		}

		if(ko_module_installed('groups')) {
			ko_get_groups($all_groups);
			$groups_values = $groups_descs = array();
			$groups = ko_groups_get_recursive(ko_get_groups_zwhere());
			foreach($groups as $g) {
				//Full id including parent relationship
				$motherline = ko_groups_get_motherline($g['id'], $all_groups);
				$groups_values[] = 'g'.$g['id'];

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

		$laf = unserialize(ko_get_userpref($uid, "fm_absence_restriction"));
		$l_values = $values_template;
		$l_descs = $descs_template;
		if(isset($laf)) {
			if(in_array($laf['value'], $values_template)) {  //Falls Filterset noch vorhanden, diesen auswählen...
				$l_sel = $laf['value'];
			} else {  //... sonst zu Liste hinzufügen
				$l_values[-1] = -1;
				$l_descs[-1] = $laf["name"];
				$l_sel = -1;
			}
		}
		$settings[] = [
			"desc" => getLL('admin_settings_fm_absence_registration_restriction'),
			"type" => "select",
			'name' => 'sel_fm_absence_restriction',
			"all_class" => 'sel_rechte',
			"values" => $l_values,
			"descs" => $l_descs,
			"value" => $l_sel,
			"params" => 'size="0"',
		];

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
			$settings[] = array('desc' => getLL('module_admin'),
				'type' => 'select',
				'name' => 'sel_admin_guest',
				'values' => $values,
				'descs' => $descs,
				'value' => ko_html(ko_get_userpref($uid, 'default_view_admin'))
			);
		}

		if(sizeof($settings) > 0) {
			$frmgroup[$gc]['titel'] = getLL("admin_settings_default");
			$colCounter = 0;
			foreach ($settings as $setting) {
				$frmgroup[$gc]["row"][$rowcounter]["inputs"][$colCounter++] = $setting;
				if ($colCounter > 1) {
					$colCounter = 0;
					$rowcounter++;
				}
			}
			$gc++;
		}


		//settings for the menu/dropdown
		$settings = array();

		//menu order
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
		$settings[] = array("desc" => getLL("admin_settings_menu_order").":",
			"type" => "doubleselect",
			"js_func_add" => "double_select_add",
			"show_moves" => TRUE,
			"name" => "sel_menu_order_guest",
			"values" => $values,
			"descs" => $descs,
			"avalues" => $avalues,
			"adescs" => $adescs,
			"avalue" => $value,
			"params" => 'size="7"',
		);

		if(sizeof($settings) > 0) {
			$frmgroup[$gc]['titel'] = getLL("admin_settings_menu");
			$colCounter = 0;
			foreach ($settings as $setting) {
				$frmgroup[$gc]["row"][$rowcounter]["inputs"][$colCounter++] = $setting;
				if ($colCounter > 1) {
					$colCounter = 0;
					$rowcounter++;
				}
			}
			$gc++;
		}



		//Diverses
		$settings = array();

		//Show gsm notes
		$value = ko_get_userpref($uid, 'download_not_directly');
		$settings[] = array('desc' => getLL('admin_settings_download_not_directly'),
			'type' => 'switch',
			'name' => 'download_not_directly_guest',
			'label_0' => getLL('no'),
			'label_1' => getLL('yes'),
			'value' => $value == '' ? 0 : $value,
		);

		if(sizeof($settings) > 0) {
			$frmgroup[$gc]['titel'] = getLL("admin_settings_misc");
			$colCounter = 0;
			foreach ($settings as $setting) {
				$frmgroup[$gc]["row"][$rowcounter]["inputs"][$colCounter++] = $setting;
				if ($colCounter > 1) {
					$colCounter = 0;
					$rowcounter++;
				}
			}
			$gc++;
		}
	}




	if ($doSomething) {
		//Allow plugins to add further settings
		hook_form('admin_settings', $frmgroup, '', '');


		//display the form
		if ($doOther) {
			$smarty->assign('tpl_titel', getLL("admin_settings_title"));
			$smarty->assign('help', '');
		}
		else if ($doGeneral) {
			$smarty->assign('tpl_titel', $titleGeneral);
			$smarty->assign('help', $helpGeneral);
		}
		else if ($doGuest) {
			$smarty->assign('tpl_titel', $titleGuest);
			$smarty->assign('help', $helpGuest);
		}
		else {
			$smarty->assign('tpl_titel', $titleLayout);
			$smarty->assign('help', $helpLayout);
		}
		$smarty->assign('tpl_submit_value', getLL('save'));
		$smarty->assign('tpl_action', 'submit_admin_settings');
		$smarty->assign('tpl_cancel', 'set_show_logins');
		$smarty->assign('tpl_groups', $frmgroup);

		$smarty->display('ko_formular.tpl');
	}

}//ko_admin_settings()



function ko_show_set_layout($uid) {
	global $smarty;
	global $MODULES;
	global $access;

	if($uid == ko_get_guest_id()) {
		if($access['admin']['MAX'] < 3) return;
		$smarty->assign("tpl_titel", getLL("admin_settings_layout_guest"));
	}
	else {
		if($access['admin']['MAX'] < 1) return;
		$smarty->assign("tpl_titel", getLL("admin_settings_layout"));
	}


	$gc = $rowcounter = 0;



	//Default-Seiten pro Modul
	$settings = array();
	//Default module after login
	$values = $descs = array('');
	foreach($MODULES as $m) {
		if(in_array($m, array('sms', 'kg', 'mailing'))) continue;
		if(!ko_module_installed($m, $uid)) continue;
		$values[] = $m;
		$descs[] = getLL('module_'.$m);
	}
	$settings[] = array('desc' => getLL('admin_settings_default_module'),
		'type' => 'select',
		'name' => 'sel_default_module',
		'values' => $values,
		'descs' => $descs,
		'value' => ko_get_userpref($uid, 'default_module')
	);
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
		$settings[] = array('desc' => getLL('module_admin'),
			'type' => 'select',
			'name' => 'sel_admin',
			'values' => $values,
			'descs' => $descs,
			'value' => ko_html(ko_get_userpref($uid, 'default_view_admin'))
		);
	}

	if(sizeof($settings) > 0) {
		$group[$gc]['titel'] = getLL("admin_settings_default");
		$colCounter = 0;
		foreach ($settings as $setting) {
			$group[$gc]["row"][$rowcounter]["inputs"][$colCounter++] = $setting;
			if ($colCounter > 1) {
				$colCounter = 0;
				$rowcounter++;
			}
		}
		$gc++;
	}


	//settings for the menu/dropdown
	$settings = array();

	//menu order
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
	$settings[] = array("desc" => getLL("admin_settings_menu_order").":",
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

	if(sizeof($settings) > 0) {
		$group[$gc]['titel'] = getLL("admin_settings_menu");
		$colCounter = 0;
		foreach ($settings as $setting) {
			$group[$gc]["row"][$rowcounter]["inputs"][$colCounter++] = $setting;
			if ($colCounter > 1) {
				$colCounter = 0;
				$rowcounter++;
			}
		}
		$gc++;
	}



	//Diverses
	$settings = array();

	//Show gsm notes
	if($uid != ko_get_guest_id()) {
		$value = ko_get_userpref($_SESSION['ses_userid'], 'save_kota_filter');
		$settings[] = array('desc' => getLL('admin_settings_save_kota_filter'),
			'type' => 'switch',
			'name' => 'save_kota_filter',
			'label_0' => getLL('no'),
			'label_1' => getLL('yes'),
			'value' => $value == '' ? 0 : $value,
		);
		$value = ko_get_userpref($_SESSION['ses_userid'], 'export_table_format');
		$settings[] = array('desc' => getLL('admin_settings_export_table_format'),
			'type' => 'select',
			'name' => 'export_table_format',
			'values' => array('xlsx', 'xls'),
			'descs' => array(getLL('admin_settings_export_table_format_xlsx'), getLL('admin_settings_export_table_format_xls')),
			'value' => $value
		);
	}
	$value = ko_get_userpref($_SESSION['ses_userid'], 'download_not_directly');
	$settings[] = array('desc' => getLL('admin_settings_download_not_directly'),
		'type' => 'switch',
		'name' => 'download_not_directly',
		'label_0' => getLL('no'),
		'label_1' => getLL('yes'),
		'value' => $value == '' ? 0 : $value,
	);

	if(sizeof($settings) > 0) {
		$group[$gc]['titel'] = getLL("admin_settings_misc");
		$colCounter = 0;
		foreach ($settings as $setting) {
			$group[$gc]["row"][$rowcounter]["inputs"][$colCounter++] = $setting;
			if ($colCounter > 1) {
				$colCounter = 0;
				$rowcounter++;
			}
		}
		$gc++;
	}


	//Allow plugins to add further settings
	hook_form('admin_layout_settings', $frmgroup, '', '');


	//display the form
	$smarty->assign('tpl_submit_value', getLL('save'));
	$smarty->assign('tpl_action', 'save_set_allgemein');
	$smarty->assign('tpl_cancel', 'list_groups');
	$smarty->assign('tpl_groups', $group);

	if($uid == ko_get_guest_id()) {
		$smarty->assign("help", ko_get_help("admin", "set_layout_guest"));
		$smarty->assign("tpl_action", "save_set_layout_guest");
	} else {
		$smarty->assign("help", ko_get_help("admin", "set_layout"));
		$smarty->assign("tpl_action", "save_set_layout");
	}

	$smarty->display("ko_formular.tpl");
}//ko_show_set_layout()




function ko_admin_list_detailed_person_exports() {
	global $smarty;
	global $access;

	if($access['admin']['MAX'] < 2) return;

	$es = db_select_data("ko_detailed_person_exports", "WHERE 1=1", "*", "ORDER BY `name` ASC");
	$rows = sizeof($es);

	$list = new kOOL_listview();

	$list->init("admin", "ko_detailed_person_exports", array("chk", "edit", "delete"), 1, 1000);
	$list->setTitle(getLL("admin_detailed_person_export_title"));
	$list->setAccessRights(FALSE);
	$list->setActions(array("edit" => array("action" => "edit_detailed_person_export"),
			"delete" => array("action" => "delete_detailed_person_export", "confirm" => TRUE))
	);
	$list->setSort(FALSE);
	$list->disableMultiedit();
	$list->setStats($rows, '', '', '', TRUE);
	if ($access['admin']['ALL'] > 1) $list->setActionNew('new_detailed_person_export');

	//Footer
	//Totals
	$list_footer = $smarty->get_template_vars('list_footer');
	$list->setFooter($list_footer);


	//Output the list
	$list->render($es);
}//ko_admin_list_detailed_person_exports()





function ko_admin_formular_detailed_person_export($mode="new", $id='') {
	global $access, $KOTA;

	if($access['admin']['MAX'] < 2) return;

	if($mode == 'new') {
		$id = 0;
	} else if($mode == 'edit') {
		if(!$id) return FALSE;
	} else {
		return FALSE;
	}

	$form_data['title'] =  $mode == 'new' ? getLL('admin_detailed_person_export_form_title_new') : getLL('admin_detailed_person_export_form_title_edit');
	$form_data['submit_value'] = getLL('save');
	$form_data['action'] = $mode == 'new' ? 'submit_new_detailed_person_export' : 'submit_edit_detailed_person_export';
	if($mode == 'edit') {
		$form_data['action_as_new'] = 'submit_as_new_detailed_person_export';
		$form_data['label_as_new'] = getLL('admin_detailed_person_export_form_submit_as_new');
	}
	$form_data['cancel'] = 'list_detailed_person_exports';

	ko_multiedit_formular('ko_detailed_person_exports', '', $id, '', $form_data);
}//ko_admin_formular_detailed_person_export()




function ko_admin_list_labels() {
	global $smarty;
	global $access;

	if($access['admin']['MAX'] < 2) return;

	$es = db_select_data("ko_labels", "WHERE 1=1", "*", "ORDER BY `name` ASC");
	$rows = sizeof($es);

	$list = new kOOL_listview();

	$list->init("admin", "ko_labels", array("chk", "edit", "delete"), 1, 1000);
	$list->setTitle(getLL("admin_labels_title"));
	$list->setAccessRights(FALSE);
	$list->setActions(array("edit" => array("action" => "edit_label"),
			"delete" => array("action" => "delete_label", "confirm" => TRUE))
	);
	$list->setSort(FALSE);
	$list->disableMultiedit();
	$list->setStats($rows, '', '', '', TRUE);
	if ($access['admin']['ALL'] > 1) $list->setActionNew('new_label');

	//Footer
	//Totals
	$list_footer = $smarty->get_template_vars('list_footer');
	$list->setFooter($list_footer);


	//Output the list
	$list->render($es);
}//ko_admin_list_labels()





function ko_admin_formular_labels($mode="new", $id='') {
	global $access;

	if($access['admin']['MAX'] < 2) return;

	if($mode == 'new') {
		$id = 0;
	} else if($mode == 'edit') {
		if(!$id) return FALSE;
	} else {
		return FALSE;
	}

	$form_data['title'] =  $mode == 'new' ? getLL('admin_labels_form_title_new') : getLL('admin_labels_form_title_edit');
	$form_data['submit_value'] = getLL('save');
	$form_data['action'] = $mode == 'new' ? 'submit_new_label' : 'submit_edit_label';
	if($mode == 'edit') {
		$form_data['action_as_new'] = 'submit_as_new_label';
		$form_data['label_as_new'] = getLL('admin_labels_form_submit_as_new');
	}
	$form_data['cancel'] = 'list_labels';

	ko_multiedit_formular('ko_labels', '', $id, '', $form_data);
}//ko_formular_etiketten_settings()





function ko_list_leute_pdf() {
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
	if ($access['admin']['ALL'] > 4) $list->setActionNew('set_leute_pdf_new');


	//Footer
	$list_footer = $smarty->get_template_vars('list_footer');
	$list->setFooter($list_footer);


	//Output the list
	$list->render($es);
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
			"type" => "switch",
			"name" => "pdf[columns_children]",
			'value' => $layout["columns_children"] ? '1' : '0',
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
	if($mode == 'edit') {
		$smarty->assign("tpl_submit_as_new", getLL("admin_settings_leute_pdf_submit_as_new"));
		$smarty->assign("tpl_action_as_new", "submit_as_new_leute_pdf");
	}
	$smarty->assign("tpl_submit_value", getLL("save"));
	$smarty->assign("tpl_action", ($mode == "new" ? "submit_new_leute_pdf" : "submit_edit_leute_pdf"));
	$smarty->assign("tpl_cancel", "set_leute_pdf");
	$smarty->assign("tpl_groups", $group);
	$smarty->assign("tpl_hidden_inputs", array(0 => array("name" => "layout_id", "value" => $layout_id)));

	$smarty->display('ko_formular.tpl');
}//ko_formular_leute_pdf()











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

	if(ko_get_setting("change_password") < 1 || $_SESSION['disable_password_change'] == 1 || $_SESSION['ses_userid'] == ko_get_guest_id()) return FALSE;

	$gc = $rowcounter = 0;

	$frmgroup[$gc]['row'][$rowcounter++]['inputs'][0] = array('desc' => getLL("admin_change_password_old"),
		'type' => "password",
		'name' => "txt_pwd_old",
		'params' => 'maxlength="40" autocomplete="false"',
		'value' => '',
		);

	$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL("admin_change_password_new1"),
		'type' => "password",
		'name' => "txt_pwd_new1",
		'params' => 'maxlength="40" autocomplete="false"',
		'value' => '',
	);

	$frmgroup[$gc]['row'][$rowcounter]['inputs'][1] = array('desc' => getLL("admin_change_password_new2"),
		'type' => "password",
		'name' => "txt_pwd_new2",
		'params' => 'maxlength="40" autocomplete="false"',
		'value' => '',
	);


	$smarty->assign("help", ko_get_help("admin", "change_password"));
	$smarty->assign("tpl_titel", getLL("admin_change_password"));
	$smarty->assign("tpl_submit_value", getLL("save"));
	$smarty->assign("tpl_groups", $frmgroup);
	$smarty->assign("tpl_action", "submit_change_password");
	$smarty->display("ko_formular.tpl");
}//ko_change_password()




/**
 * List currently available news
 */
function ko_list_news() {
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
	if ($access['admin']['ALL'] > 1) $list->setActionNew('new_news');
	$list->setSort(TRUE, 'setsort', $_SESSION['sort_news'], $_SESSION['sort_news_order']);
	$list->setStats($rows, '', '', '', TRUE);


	//Output the list
	$list->render($es);
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




function ko_access_get_kota_columns_form($id, $coltable, $type='login') {
	global $KOTA;

	ko_include_kota(array($coltable));
	$col_values = $col_descs = array();
	foreach($KOTA[$coltable] as $k => $v) {
		if(substr($k, 0, 1) == '_') continue;
		if($v['exclude_from_access'] || !isset($v['form'])) continue;
		$ll = getLL('kota_'.$coltable.'_'.$k);
		if(!$ll) continue;
		$col_values[] = $k;
		$col_descs[] = $ll ? $ll : $k;
	}
	$col_adescs = array();
	$col_avalues = ko_access_get_kota_columns($id, $coltable, $type);
	foreach($col_avalues as $avalue) {
		$ll = getLL('kota_'.$coltable.'_'.$avalue);
		if(!$ll) continue;
		$col_adescs[] = $ll ? $ll : $avalue;
	}

	return array('desc' => getLL('admin_logins_kota_columns'),
		'type' => 'checkboxes',
		'name' => 'kota_columns_'.$coltable,
		'values' => $col_values,
		'descs' => $col_descs,
		'avalues' => $col_avalues,
		'avalue' => implode(',', $col_avalues),
		'size' => min(7, sizeof($col_values)),
		'colspan' => 'colspan="3"',
	);
}//ko_access_get_kota_columns_form()

function ko_admin_vesr_archive() {
	global $BASE_PATH;

	$dirs = [
		'plugins/billing/v11',
		'my_images/v11',
		'my_images/camt/done',
	];

	$files = [];
	foreach($dirs as $dir) {
		foreach(scandir($BASE_PATH.$dir) as $file) {
			if($file[0] == '.') continue;
			$path = $dir.'/'.$file;
			$ext = strtolower(substr($file,-4));
			if($ext == '.v11') {
				$f = fopen($BASE_PATH.$path,'r');
				while(!feof($f) && strlen(fgets($f,72)) == 0);
				$dt = fread($f,6);
				if(ctype_digit($dt)) {
					$dt = '20'.substr($dt,0,2).'-'.substr($dt,2,2).'-'.substr($dt,4,2);
				} else {
					$dt = '????-??-??';
				}
				$files[$path] = $dt;
				fclose($f);
			} else if($ext == '.xml') {
				$f = fopen($BASE_PATH.$path,'r');
				while(!feof($f) && strlen(stream_get_line($f,4096,'<CreDtTm>')) == 4096) fseek($f,-8,SEEK_CUR);
				if(feof($f)) {
					$dt = '????-??-??';
				} else {
					$dt = fread($f,10);
				}
				$files[$path] = $dt;
				fclose($f);
			}
		}
	}
	arsort($files);

	echo '<div class="paymentList">';
	echo '<h2>'.getLL('payments_list_title').'</h2>';

	$month = null;
	$year = null;
	foreach($files as $path => $dttm) {
		$m = substr($dttm,0,7);
		if($m == '????-??') {
			$m = 'UNKNOWN';
		}
		if($m != $month) {
			if($month) {
				echo '</ul></div>';
			}
			$y = substr($dttm,0,4);
			if($y == '????') {
				$y = getLL('unknown');
			}
			if($y != $year) {
				echo '<h3>'.$y.'</h3>';
				$year = $y;
			}
			echo '<div class="panel panel-default"><div class="panel-heading" data-toggle="collapse" href="#payments'.$m.'" style="cursor:pointer;">'.($m == 'UNKNOWN' ? getLL('unknown') : strftime('%B %Y',strtotime($m))).'</div><ul class="list-group collapse" id="payments'.$m.'">';
			$month = $m;
		}
		echo '<li class="list-group-item payment" data-path="'.$path.'"><div class="title" style="cursor:pointer;"><b style="margin-right:2em;">'.($dttm == '????-??-??' ? '' : strftime('%d. %B',strtotime($dttm))).'</b>'.basename($path).'</div></li>';
	}
	echo '</ul></div>';
	echo '</div>';
?>
<script>

$(document).ready(function() {
	$('li.payment .title').click(function() {
		var payment = $(this).parent();
		var details = payment.find('.details');
		var togglePayment = function() {
			payment.toggleClass('in');
			details.slideToggle(payment.hasClass('in'));
			$('li.payment .details:visible').last().closest('li.payment').css('page-break-after','auto');
		}
		if(details.length) {
			togglePayment();
		} else {
			$.get('inc/ajax.php',{action:'paymentDetails',file:payment.data('path'),sesid:'<?= session_id() ?>'},function(data) {
				details = $('<div>').addClass('details').html(data).hide().appendTo(payment);
				togglePayment();
			});
		}
	});
});

</script>
<?php
}

function ko_admin_show_pubkey() {
	$formats = ['PKCS1','XML','OPENSSH','PKCS8'];
	$format = isset($_POST['format']) ? $_POST['format'] : \phpseclib\Crypt\RSA::PUBLIC_FORMAT_OPENSSH;
	echo '<div class="form-group">';
	echo '<label>'.getLL('admin_show_pubkey_format').'</label>';
	echo '<select name="format" method="get" onchange="this.form.submit()" class="form-control">';
	foreach($formats as $f) {
		$v = constant(\phpseclib\Crypt\RSA::class.'::PUBLIC_FORMAT_'.$f);
		echo '<option value="'.$v.'"'.($v == $format ? ' selected' : '').'>'.$f.'</option>';
	}
	echo '</select>';
	echo '</div><div class="form-group">';
	echo '<label>'.getLL('admin_show_pubkey_key').'</label>';
	echo '<textarea class="form-control" rows="30">'.ko_get_public_key($format).'</textarea>';
	echo '</div>';
}

/**
 * Create new hash for ical link access
 * @param int $user_id
 *
 * @return string $ical_hash
 */
function ko_admin_revoke_ical_hash($user_id) {
	if(!is_numeric($user_id)) return FALSE;

	$where = "WHERE id = " . $user_id;

	$ical_hash = md5(uniqid(KOOL_ENCRYPTION_KEY.$user_id).time());
	$data = [
		"ical_hash" => $ical_hash,
	];
	db_update_data("ko_admin", $where, $data);

	return $ical_hash;
}
