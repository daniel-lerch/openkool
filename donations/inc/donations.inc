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

require_once($BASE_PATH."inc/class.kOOL_listview.php");


function ko_list_donations($mode="html", $dontApplyLimit=FALSE) {
	global $smarty, $KOTA;
	global $access;

	if($access['donations']['MAX'] < 1) return;
	apply_donations_filter($z_where, $z_limit);

	if(substr($_SESSION['sort_donations'], 0, 6) == 'MODULE') $order = 'ORDER BY date DESC';
	else $order = 'ORDER BY '.$_SESSION['sort_donations'].' '.$_SESSION['sort_donations_order'];

	$rows = db_get_count('ko_donations', 'id', $z_where);
	if($_SESSION['show_start'] > $rows) {
		$_SESSION['show_start'] = 1;
		$z_limit = 'LIMIT '.($_SESSION['show_start']-1).', '.$_SESSION['show_limit'];
	}
	if($dontApplyLimit) $z_limit = "";
	$es = db_select_data('ko_donations', 'WHERE 1 '.$z_where, '*', $order, $z_limit);

	$list = new kOOL_listview();

	if($_SESSION['donations_filter']['promise'] == 1) {
		$icons = array('chk', 'check', 'edit', 'delete');
		$actions = array(
			'check' => array('action' => 'do_promise'),
			'edit' => array('action' => 'edit_donation'),
			'delete' => array('action' => 'delete_donation', 'confirm' => TRUE)
		);
	} else {
		$icons = array('chk', 'edit', 'delete');
		$actions = array(
			'edit' => array('action' => 'edit_donation'),
			'delete' => array('action' => 'delete_donation', 'confirm' => TRUE)
		);
	}

	$list->init("donations", "ko_donations", $icons, $_SESSION["show_start"], $_SESSION["show_limit"]);
	$list->setTitle(getLL("donations_list_title"));
	$list->showColItemlist();
	$list->setAccessRights(array('edit' => 3, 'delete' => 3), $access['donations']);
	$list->setActions($actions);
	if ($access['donations']['MAX'] > 1 && db_get_count("ko_donations_accounts") > 0) $list->setActionNew('new_donation');
	$list->setSort(TRUE, "setsort", $_SESSION["sort_donations"], $_SESSION["sort_donations_order"]);
	$list->setStats($rows);

	//Find amount column and align right
	$c = 0;
	foreach($KOTA['ko_donations']['_listview'] as $col) {
		if($col['name'] == 'amount') $rightCol = $c;
		$c++;
	}
	if($rightCol > 0) {
		$colParams = array();
		for($i=0; $i<$rightCol; $i++) {
			$colParams[$i] = '';
		}
		$colParams[$rightCol] = 'style="text-align: right; padding-right: 5px"';
		$list->setColParams($colParams);
	}

	$list->setWarning(kota_filter_get_warntext('ko_donations'));


	//Footer
	$_total = db_select_data('ko_donations', "WHERE 1 $z_where", 'SUM(amount) as total', '', '', TRUE, TRUE);
	$total_amount = $_total['total'];

	$result = mysqli_query(db_get_link(), "SELECT DISTINCT `person` FROM `ko_donations` WHERE 1 $z_where");
	$num_person = mysqli_num_rows($result);
	mysqli_free_result($result);

	//Averages
	$avg = $rows ? $total_amount/$rows : 0;
	$avg_person = $num_person ? $total_amount/$num_person : 0;

	$list_footer = $smarty->get_template_vars('list_footer');
	$list_footer[] = [
		"label" => "",
		"button" => sprintf(getLL("donations_list_footer_stats_totals"),
		number_format($total_amount, 2, '.', "'"),
		number_format($rows, 0, '.', "'"),
		number_format($num_person, 0, '.', "'")),
	];
	$list_footer[] = [
		"label" => "",
		"button" => sprintf(getLL("donations_list_footer_stats_averages"),
		number_format($avg, 2, '.', "'"),
		number_format($avg_person, 2, '.', "'")),
	];
	$list_footer[] = [
		"label" => getLL("donations_list_footer_mark_thanked"),
		'button' => '<button class="btn btn-default btn-sm" onclick="c=confirm(\'' . getLL('donations_thanks_confirm_mark_thanked') . '\'); if (c) {set_action(\'mark_thanked\')} else {return false}">OK</button>'];

	$smarty->assign('show_list_footer', TRUE);
	$smarty->assign('list_footer', $list_footer);

	$list->setFooter($list_footer);


	//Output the list
	$list->render($es, $mode, getLL("donations_export_filename"));
	if($mode == "xls") return $list->xls_file;
}//ko_list_donations()


function ko_list_donations_mod() {
	global $smarty, $ko_path, $access, $DATETIME;

	$counter=0;
	ko_get_logins($logins);
	ko_get_donations_mod($donations);
	$mods = array();

	foreach($donations as $d) {
		if ($counter > 50) continue;

		if ($access['donations']['ALL'] < 2 && $access['donations'][$d['account']] < 2) continue;

		$fields_counter = 0;

		$mods[$counter]['id'] = $d['id'];

		// Create Spenden fields
		$mods[$counter]['d_fields'][$fields_counter++] = array(
			"type" => "html",
			'desc' => getLL('kota_ko_donations_date'),
			'name' => 'date_' . $d['id'],
			'value' => sql2datum($d['date']),
		);
		$name = 'valutadate_' . $d['id'];
		$date = sql2datum($d['valutadate']);
		$mods[$counter]['d_fields'][$fields_counter++] = array(
			"type" => "datepicker",
			'desc' => getLL('kota_ko_donations_valutadate'),
			'value' => $date,
			"name" => $name
		);
		$mods[$counter]['d_fields'][$fields_counter++] = array(
			"type" => "textplus",
			'desc' => getLL('kota_ko_donations_source'),
			'name' => 'source_' . $d['id'],
			'value' => $d['source'],
			'values' => db_select_distinct('ko_donations', 'source'),
			'descs' => db_select_distinct('ko_donations', 'source'),
		);

		$donation_account_field_id = $fields_counter;
		$mods[$counter]['d_fields'][$fields_counter++] = array_merge(array(
			"type" => "select",
			'desc' => getLL('kota_ko_donations_account'),
			'name' => 'account_' . $d['id'],
			"params" => 'size="0" onchange="javascript:selAccount(this.value, this);"',
			'value' => $d['account'],
		), kota_get_form("ko_donations", "account")
		);
		$mods[$counter]['d_fields'][$fields_counter++] = array(
			"type" => "html",
			'desc' => getLL('kota_ko_donations_amount'),
			'name' => 'amount_' . $d['id'],
			'value' => $d['amount'],
		);
		if($d['_account_number']) {
			$mods[$counter]['d_fields'][$fields_counter++] = array(
				'type' => 'html',
				'desc' => getLL('kota_ko_donations_accounts_number'),
				'name' => '_account_number_'.$d['id'],
				'value' => $d['_account_number'],
			);
		}
		if($d['_account_name']) {
			$mods[$counter]['d_fields'][$fields_counter++] = array(
				'type' => 'html',
				'desc' => getLL('kota_ko_donations_accounts_name'),
				'name' => '_account_name_'.$d['id'],
				'value' => $d['_account_name'],
			);
		}
		$mods[$counter]['d_fields'][$fields_counter++] = array(
			"type" => "textarea",
			'desc' => getLL('kota_ko_donations_comment'),
			'name' => 'comment_' . $d['id'],
			"params" => 'style="height:90px;"',
			'value' => $d['comment'],
		);

		// get Donations Accounts and find best matching for Comment
		if(!$d['account']) {
			$donation_accounts = db_select_data('ko_donations_accounts', 'WHERE archived = 0');
			$best_donation_account_id = ko_fuzzy_search_in_string($d['comment'], $donation_accounts);
			if ($best_donation_account_id !== FALSE) {
				$mods[$counter]['d_fields'][$donation_account_field_id]['value'] = $best_donation_account_id;
			}
		}

		// create Spender form
		$fieldsCounter = 0;
		$dbFields = db_get_columns('ko_donations_mod');
		$fields_for_suggest = ['_p_anrede', '_p_firm', '_p_vorname', '_p_nachname', '_p_adresse', '_p_plz', '_p_ort'];
		foreach ($dbFields as $dbCol) {
			$dbField = $dbCol['Field'];
			if(substr($dbField, 0, 3) != '_p_') continue;

			$fieldName = substr($dbField, 3);
			$fieldValue = trim(map_leute_daten($d[$dbField], $fieldName));

			//Check for all uppercase --> show in better formating
			//  But ignore country codes like "CH"
			if(strtoupper($fieldValue) == $fieldValue
				&& !($fieldName == 'land' && strlen($fieldValue <= 3))
				) {

				$parts = array();
				foreach(explode(' ', $fieldValue) as $part) {
					if(!$part) continue;
					$parts[] = ucfirst(strtolower($part)).' ';
				}
				$fieldValue = implode(' ', $parts);
			}


			if(in_array($dbField, $fields_for_suggest) || $fieldValue != '') {
				if($fieldName == 'anrede') {
					$mods[$counter]['p_fields'][$fieldsCounter++] = array_merge(
						array(
							"type" => "textplus",
							"params" => 'size="0"',
							'desc' => getLL('kota_ko_leute_anrede'),
							'name' => '_p_anrede_' . $d['id'],
							'value' => $fieldValue,
						), kota_get_form('ko_leute', 'anrede')
					);
				} else {
					$mods[$counter]['p_fields'][$fieldsCounter++] = array(
						"type" => "text",
						'desc' => getLL('kota_ko_leute_' . $fieldName),
						'name' => $dbField . '_' . $d['id'],
						'value' => $fieldValue,
					);
				}
			} else {
				if($fieldValue == '') continue;
				$mods[$counter]['p_fields'][$fieldsCounter++] = array(
					'type' => 'html',
					'desc' => getLL('kota_ko_leute_' . $fieldName),
					'name' => $dbField . '_' . $d['id'],
					'value' => $fieldValue,
					);
			}
		}//foreach(dbFields ad dbCol)

		//Try to find person in DB
		if($d['_p_vorname'] && $d['_p_nachname']) {
			$search = array('vorname' => $d['_p_vorname'], 'nachname' => $d['_p_nachname']);
		} else if($d['_p_email']) {
			$search = array('email' => $d['_p_email']);
		} else if($d['_p_firm']) {
			$search = array('firm' => $d['_p_firm']);
		}
		$found_dbp = ko_fuzzy_search($search, "ko_leute", 2, false, 2);
		$db = null;
		foreach($found_dbp as $db_id) {
			ko_get_person_by_id($db_id, $dbp);
			$db[] = array("_id" => $d["_id"], "lid" => $dbp["id"], "name" => $dbp["vorname"]." ".$dbp["nachname"],
				'firm' => $dbp['firm'], 'department' => $dbp['department'],
				"adressdaten" => $dbp["adresse"].", ".$dbp["plz"]." ".$dbp["ort"].
					", ".$dbp["telp"].", ".$dbp["email"].", ".sql2datum($dbp["geburtsdatum"])
			);
		}


		$mods[$counter]['db'] = $db;
		$mods[$counter]['selectedPerson'] = array(
			"type" => "peoplesearch",
			'name' => 'add_to_selected_person_' . $d['id'],
			'exclude_sql' => '`hidden` = 0',
			'single' => TRUE
		);

		$mods[$counter]['addToGroup'] = array(
			"type" => "switch",
			'desc' => getLL('donations_mod_add_new_person_to_group'),
			'name' => 'add_to_group_' . $d['id'],
			'value' => 1,
		);

		//Show creation date and user
		if($d['_crdate'] != '0000-00-00 00:00:00') $mods[$counter]['crdate'] = strftime($DATETIME['dmY'].' %H:%M', strtotime($d['_crdate']));
		if($d['_cruser'] > 0) $mods[$counter]['cruser'] = getLL('by').' '.$logins[$d['_cruser']]['login'];

		$counter++;
	}//foreach(donations as d)


	if(sizeof($donations) == 0) $smarty->assign('tpl_donations_mod_empty', true);
	//LL-Values
	$smarty->assign('label_empty', getLL('donations_mod_list_empty'));
	$smarty->assign('label_add_to_suggested_person', getLL('donations_mod_list_add_to_suggested_person'));
	$smarty->assign('label_add_to_selected_person', getLL('donations_mod_list_add_to_selected_person'));
	$smarty->assign('label_add_to_new_person', getLL('donations_mod_list_add_to_new_person'));
	$smarty->assign('label_submit', getLL('donations_mod_list_submit'));
	$smarty->assign('label_submit_and_mutation', getLL('donations_mod_list_submit_and_mutation'));
	$smarty->assign('label_delete', getLL('donations_mod_list_delete'));
	$smarty->assign('label_crdate', getLL('donations_mod_crdate'));
	$smarty->assign('label_donation_fields', getLL('donations_mod_donations_fields'));
	$smarty->assign('label_person_fields', getLL('donations_mod_person_fields'));
	$smarty->assign('label_assign_fields', getLL('donations_mod_assign_fields'));
	$smarty->assign('label_confirm_delete', getLL('list_label_confirm_delete'));
	$smarty->assign('ko_path', $ko_path);

	$smarty->assign('tpl_list_title', getLL('donations_mod_title'));
	$smarty->assign('tpl_fm_title', getLL('donations_mod_title'));

	$smarty->assign('tpl_mods', $mods);
	$smarty->display('ko_donations_mod.tpl');
} // ko_list_donations_mod()





function ko_list_accounts() {
	global $smarty;
	global $access;

	if($access['donations']['MAX'] < 4) return;

	$rows = db_get_count('ko_donations_accounts', 'id');
	if($_SESSION['show_start'] > $rows) $_SESSION['show_start'] = 1;
  $z_limit = 'LIMIT '.($_SESSION['show_start']-1).', '.$_SESSION['show_limit'];
	$es = db_select_data('ko_donations_accounts', '', '*', 'ORDER BY number ASC, name ASC', $z_limit);
	foreach($es as $k => $v) {
		if($access['donations'][$k] < 1) unset($es[$k]);
	}

	$list = new kOOL_listview();

	$list->init("donations", "ko_donations_accounts", array("chk", "edit", "delete"), $_SESSION["show_start"], $_SESSION["show_limit"]);
	$list->setTitle(getLL("donations_accounts_list_title"));
	$list->setAccessRights(array('edit' => 4, 'delete' => 4), $access['donations'], 'id');
	$list->setActions(array("edit" => array("action" => "edit_account"),
													"delete" => array("action" => "delete_account", "confirm" => TRUE))
										);
	if ($access['donations']['MAX'] > 3) $list->setActionNew('new_account');
	$list->setStats($rows);
	$list->setSort(FALSE);

	//Output the list
	$list->render($es);
}//ko_list_accounts()




function ko_list_accountgroups() {
	global $smarty;
	global $access;

	if($access['donations']['ALL'] < 4) return;

	$rows = db_get_count('ko_donations_accountgroups', 'id');
	if($_SESSION['show_start'] > $rows) $_SESSION['show_start'] = 1;
  $z_limit = 'LIMIT '.($_SESSION['show_start']-1).', '.$_SESSION['show_limit'];
	$es = db_select_data('ko_donations_accountgroups', '', '*', 'ORDER BY `title` ASC', $z_limit);

	$list = new kOOL_listview();

	$list->init("donations", "ko_donations_accountgroups", array("chk", "edit", "delete"), $_SESSION["show_start"], $_SESSION["show_limit"]);
	$list->setTitle(getLL("donations_accountgroups_list_title"));
	$list->setAccessRights(array('edit' => 4, 'delete' => 4), $access['donations'], 'id');
	$list->setActions(array("edit" => array("action" => "edit_accountgroup"),
													"delete" => array("action" => "delete_accountgroup", "confirm" => TRUE))
										);
	$list->setActionNew('new_accountgroup');
	$list->setStats($rows);
	$list->setSort(FALSE);

	//Output the list
	$list->render($es);
}//ko_list_accountgroups()




function ko_list_reoccuring_donations() {
	global $KOTA, $access, $DATETIME;

	if($access['donations']['MAX'] < 1) return;

	if(substr($_SESSION['sort_donations'], 0, 6) == 'MODULE') $order = '';
	else $order = "ORDER BY ".$_SESSION["sort_donations"]." ".$_SESSION["sort_donations_order"];
	$z_where = " AND reoccuring > 0 ";

	$rows = db_get_count("ko_donations", "id", $z_where);
	$es = db_select_data("ko_donations", "WHERE 1 ".$z_where, "*", $order);


	//Add new column deadline
	$KOTA["ko_donations"]["due"] = array("list" => "none");
	$KOTA["ko_donations"]["_listview"][5] = array("name" => "due", 'sort' => 'MODULEdue');

	if(is_array($_SESSION['kota_show_cols_ko_donations'])) {
		array_unshift($_SESSION['kota_show_cols_ko_donations'], 'due');
	} else {
		array_unshift($KOTA["ko_donations"]["_listview_default"], 'due');
	}
	unset($KOTA["ko_donations"]["_listview"][10]);  //Don't show date of last donation
	unset($KOTA["ko_donations"]["_listview"][20]);  //Don't show valuta date of last donation

	//Prepare due date as new column
	foreach($es as $i => $e) {
		if(substr($e['reoccuring'], -1) == 'm') {
			$due = add2date($e['date'], 'month', substr($e['reoccuring'], 0, -1), TRUE);
		} else {
			$due = add2date($e['date'], 'day', $e['reoccuring'], TRUE);
		}
		if(date("Ymd") >= str_replace("-", "", $due)) {
			$pre = '<span style="color: red; font-weight: 900;">';
			$post = '</span>';
		} else {
			$pre = $post = "";
		}
		$es[$i]["due"] = $pre.sql2datum($due).$post;
		$es[$i]['due_dmY'] = strftime($DATETIME['dmY'], strtotime($due));
		$sort[$i] = $due;
	}
	//Sort entries for due
	if($_SESSION['sort_donations'] == 'MODULEdue') {
		if($_SESSION['sort_donations_order'] == 'ASC') asort($sort);
		else arsort($sort);
		$new = array();
		foreach($sort as $i => $due) {
			$new[$i] = $es[$i];
		}
		$es = $new;
	}


	$list = new kOOL_listview();

	$list->init("donations", "ko_donations", array("chk", "check", "delete"), 1, $rows);
	$list->setTitle(getLL("donations_reoccuring_list_title"));
	$list->setAccessRights(array('check' => 2, 'delete' => 3), $access['donations']);

	$check = array('action' => 'do_reoccuring_donation');
	if(ko_get_userpref($_SESSION['ses_userid'], 'donations_recurring_prompt') == 1) {
		$check['additional_row_js'] = "ret = donation_recurring('###DUE_DMY###', '###AMOUNT###'); if(ret == false) { return false; }";
	}
	$list->setActions(array('check' => $check,
													'delete' => array('action' => 'delete_reoccuring_donation', 'confirm' => TRUE))
										);
	$list->setSort(TRUE, "setsort", $_SESSION["sort_donations"], $_SESSION["sort_donations_order"]);
	$list->setStats($rows, '', '', '', TRUE);

	//Footer
	$list_footer[] = array("label" => getLL("donations_list_footer_do_reoccuring"),
												 "button" => '<button type="submit" class="btn btn-sm btn-primary" name="submit_do_reoccuring" onclick="set_action(\'do_reoccuring_donations\');this.submit;" value="'.getLL("donations_list_footer_do_reoccuring_button").'">' . getLL("donations_list_footer_do_reoccuring_button") . '</button',
												 );

	$list->setFooter($list_footer);

	//Output the list
	$list->render($es);
}//ko_list_reoccuring_donations()




function ko_formular_donation($mode, $id='', $promise=FALSE) {
	global $KOTA;

	if($mode == 'new') {
		$id = 0;
	} else if($mode == 'edit') {
		if(!$id) return FALSE;
	} else {
		return FALSE;
	}

	if($promise) {
		$form_data['title'] =  $mode == 'new' ? getLL('form_donation_promise_title_new') : getLL('form_donation_promise_title_edit');
		$form_data['action'] = $mode == 'new' ? 'submit_new_promise' : 'submit_edit_promise';
	} else {
		$form_data['title'] =  $mode == 'new' ? getLL('form_donation_title_new') : getLL('form_donation_title_edit');
		$form_data['action'] = $mode == 'new' ? 'submit_new_donation' : 'submit_edit_donation';
	}
	$form_data['submit_value'] = $promise ? getLL('form_donation_promise_save') : getLL('save');

	if($mode == 'edit') {
		if(!$promise) {
			$form_data['action_as_new'] = 'submit_as_new_donation';
			$form_data['label_as_new'] = getLL('donations_submit_as_new');
		}
	} else {
		$KOTA['ko_donations']['date']['form']['value'] = date('d.m.Y');
	}
	$form_data['cancel'] = 'list_donations';

	ko_multiedit_formular('ko_donations', '', $id, '', $form_data);
}//ko_formular_donation()



function ko_formular_account($mode, $id="") {
	global $KOTA;

	if($mode == "new") {
		$id = 0;
	} else if($mode == "edit") {
		if(!$id) return FALSE;
	} else {
		return FALSE;
	}

	$form_data["title"] =  $mode == "new" ? getLL("form_donation_title_new_account") : getLL("form_donation_title_edit_account");
	$form_data["submit_value"] = getLL("save");
	$form_data["action"] = $mode == "new" ? "submit_new_account" : "submit_edit_account";
	$form_data["cancel"] = "list_accounts";

	ko_multiedit_formular("ko_donations_accounts", "", $id, "", $form_data);
}//ko_formular_account()




function ko_formular_accountgroup($mode, $id="") {
	global $KOTA;

	if($mode == 'new') {
		$id = 0;
	} else if($mode == 'edit') {
		if(!$id) return FALSE;
	} else {
		return FALSE;
	}

	$form_data['title'] =  $mode == 'new' ? getLL('form_donation_title_new_accountgroup') : getLL('form_donation_title_edit_accountgroup');
	$form_data['submit_value'] = getLL('save');
	$form_data['action'] = $mode == 'new' ? 'submit_new_accountgroup' : 'submit_edit_accountgroup';
	$form_data['cancel'] = 'list_accountgroups';

	ko_multiedit_formular('ko_donations_accountgroups', '', $id, '', $form_data);
}//ko_formular_accountgroup()




function ko_donations_stats($mode="html", $_year='') {
	global $access, $smarty, $ko_path;

	if($access['donations']['MAX'] < 1) return FALSE;

	$date_field = ko_get_userpref($_SESSION['ses_userid'], 'donations_date_field');
	if(!$date_field || !in_array($date_field, array('date', 'valutadate'))) $date_field = 'date';

	$combine_accounts = ko_get_userpref($_SESSION['ses_userid'], 'donations_export_combine_accounts');

	switch($_SESSION["stats_mode"]) {
		case "year":
			//Get data for current year
			$use_year = $_year ? $_year : $_SESSION['stats_year'];
			$use_year = intval($use_year);
			$data = ko_donations_get_stats_year($use_year);

			//Row title (account)
			$a_where = sizeof($_SESSION['show_donations_accounts']) > 0 ? "WHERE `id` IN ('".implode("','", $_SESSION["show_donations_accounts"])."')" : "WHERE 1=2";

			$years = db_select_distinct("ko_donations", "YEAR(`$date_field`)", '', sizeof($_SESSION['show_donations_accounts']) > 0 ? "WHERE `account` IN ('".implode("','", $_SESSION["show_donations_accounts"])."')" : "WHERE 1=2"." AND `promise` = '0'");
			foreach($years as $key => $value) if(!$value) unset($years[$key]);

			$all_accounts = db_select_data("ko_donations_accounts", $a_where, "*", "ORDER BY number ASC, name ASC");
			foreach($all_accounts as $a) {
				if($access['donations']['ALL'] < 1 && $access['donations'][$a['id']] < 1) continue;
				$data["accounts"][$a["id"]]["name"] = $a["number"]." ".$a["name"];
				$data['accounts'][$a['id']]['id'] = $a['id'];
			}

			//header
			$header = array();
			for($m = 1; $m <= 12; $m++) {
				$header[] = strftime("%b", mktime(1,1,1, $m, 1, date("Y")));
			}

			//Export to Excel
			if($mode == "xls") {
				$xls_data = array();
				$row = 0;
				//Data for each account
				if (!$combine_accounts) {
					foreach($data["accounts"] as $id => $a) {
						$xls_data[$row][] = $a["name"];
						for($m=1; $m<=12; $m++) {
							$xls_data[$row][] = $a[$m]["amount"];
						}
						$xls_data[$row][] = $a["total"]["amount"];
						$row++;
					}
				}
				//Add row with totals
				$xls_data[$row][] = getLL("total");
				for($m=1; $m<=12; $m++) {
					$xls_data[$row][] = $data["total"][$m]["amount"];
				}
				$xls_data[$row][] = $data["grand_total"]["amount"];
				//XLS Headers
				$xls_header = array_merge(array($use_year), $header, array(getLL("total")));

				$filename = $ko_path."download/excel/".getLL("donations_export_filename").strftime("%d%m%Y_%H%M%S", time()).".xlsx";
				$filename = ko_export_to_xlsx($xls_header, $xls_data, $filename, getLL("donations_export_title"));
				return $filename;

			} else if($mode == "html") {
				//Draw BarChart
				$BCdata = array();
				$BClegend = array();
				foreach($data["total"] as $m => $values) {
					$BCdata[$m] = ko_round05($values["amount"]);
					$BClegend[$m] = strftime("%B", mktime(1,1,1, $m, 1, date("Y")));
				}
				$barChart = ko_bar_chart($BCdata, $BClegend, "", 800);
				$year_data = array();
				for ($i = 0; $i <= 12; $i++) {
					if ($i == 0) {
						$row = array('key');
						foreach ($data['accounts'] as $account_id => $account) {
							$row[] = $all_accounts[$account_id]['name'] . '(' . $all_accounts[$account_id]['number'] . ')';
						}
					} else {
						$row = array($header[$i-1]);
						foreach ($data['accounts'] as $account_id => $account) {
							$row[] = $account[$i]['amount'] ? floatval($account[$i]['amount']) : 0.0;;
						}
					}
					$year_data[] = $row;
				}
				array_walk_recursive($year_data, 'utf8_encode_array');
				$smarty->assign("year_data_js", json_encode($year_data, JSON_NUMERIC_CHECK));

				$year_stop = date('Y');
				$year_start = $year_stop - 4;
				$accountsData = ko_donations_get_stats_accounts($year_start, $year_stop);
				array_walk_recursive($accountsData, 'utf8_encode_array');
				$smarty->assign("accounts_data_js", json_encode($accountsData, JSON_NUMERIC_CHECK));
				$smarty->assign("img_year", $barChart);
				$smarty->assign("img_year_title", sprintf(getLL("donations_stats_img_year_title"), $_SESSION["stats_year"]));
				$smarty->assign("img_accounts_title", sprintf(getLL("donations_stats_img_accounts_title"), $year_start, $year_stop));

				$smarty->assign("table_year_title", sprintf(getLL("donations_stats_table_year_title"), $_SESSION["stats_year"]));
				$smarty->assign("label_total", getLL("total"));
				$smarty->assign("tpl_years", $years);
				$smarty->assign("cur_year", $_SESSION["stats_year"]);
				$smarty->assign("tpl_header", $header);
				$smarty->assign("tpl_data", $data);
				$smarty->assign('show_num', ko_get_userpref($_SESSION['ses_userid'], 'donations_stats_show_num'));
				array_walk_recursive($header, 'utf8_encode_array');
				$smarty->assign("months_js", json_encode($header));
				$tpl_file = "ko_donations_stats.tpl";
			}
		break;  //year

		case "month":
		break;  //month
	}//switch(stats_mode)

	//Output the list
	$smarty->display($tpl_file);
}//ko_donations_stats()




function ko_donations_get_stats_year($year) {
	$date_field = ko_get_userpref($_SESSION['ses_userid'], 'donations_date_field');
	if(!$date_field || !in_array($date_field, array('date', 'valutadate'))) $date_field = 'date';

	$a_where = "WHERE `id` IN ('".implode("','", $_SESSION["show_donations_accounts"])."')";
	$all_accounts = db_select_data("ko_donations_accounts", $a_where, "*", "ORDER BY number ASC, name ASC");
	$data = array();

	//promise filter
	$where = " `promise` = '0' ";

	$all_sources = db_select_distinct('ko_donations', 'source', '', "WHERE $where");

	apply_donations_filter($z_where, $z_limit);
	for($m = 1; $m <= 12; $m++) {
		$all_donations = db_select_data("ko_donations", "WHERE (YEAR(`$date_field`) = '$year' AND MONTH(`$date_field`) = '$m') $z_where", "*");
		$total_donations = $total_amount = 0;
		$person = $donations = $amounts = $sources = array();

		foreach($all_donations as $donation) {
			$person[$donation["person"]] += 1;
			$donations[$donation["account"]] += 1;
			$amounts[$donation["account"]] += $donation["amount"];

			$total_donations += 1;
			$total_amount += $donation["amount"];

			$sources[$donation['account']][$donation['source']]['amount'] += $donation['amount'];
			$sources[$donation['account']][$donation['source']]['num'] += 1;
		}

		$data["total"][$m] = array("donations" => $total_donations, "amount" => $total_amount);
		foreach($all_accounts as $account) {
			$data["accounts"][$account["id"]][$m] = array("donations" => $donations[$account["id"]],
																										"amount" => $amounts[$account["id"]]);
			$data["accounts"][$account["id"]]["total"]["donations"] += $donations[$account["id"]];
			$data["accounts"][$account["id"]]["total"]["amount"] += $amounts[$account["id"]];

			foreach($all_sources as $source) {
				$data['accounts'][$account['id']]['sources'][$source]['name'] = $source;
				$data['accounts'][$account['id']]['sources'][$source][$m] = $sources[$account['id']][$source];
				$data['accounts'][$account['id']]['sources'][$source]['total']['amount'] += $sources[$account['id']][$source]['amount'];
				$data['accounts'][$account['id']]['sources'][$source]['total']['num'] += $sources[$account['id']][$source]['num'];
			}
		}

		foreach ($all_sources as $source) {
			$data["total"]['sources'][$source]['name'] = $source;
			foreach ($all_accounts as $account) {
				$data["total"]['sources'][$source][$m]['amount'] += $data['accounts'][$account['id']]['sources'][$source][$m]['amount'];
				$data["total"]['sources'][$source][$m]['num'] += $data['accounts'][$account['id']]['sources'][$source][$m]['num'];
				$data["total"]['sources'][$source]['total']['amount'] += $data['accounts'][$account['id']]['sources'][$source][$m]['amount'];
				$data["total"]['sources'][$source]['total']['num'] += $data['accounts'][$account['id']]['sources'][$source][$m]['num'];
			}
		}

		$data["grand_total"]["donations"] += $total_donations;
		$data["grand_total"]["amount"] += $total_amount;
	}//for(m=1..12)


	return $data;
}//ko_donations_get_stats_year()



function ko_donations_get_stats_accounts($yearStart=NULL, $yearEnd=NULL) {
	if ($yearEnd === NULL) $yearEnd = date('Y');
	if ($yearStart === NULL) $yearStart = $yearEnd - 4;

	$date_field = ko_get_userpref($_SESSION['ses_userid'], 'donations_date_field');
	if(!$date_field || !in_array($date_field, array('date', 'valutadate'))) $date_field = 'date';

	$a_where = "WHERE `id` IN ('".implode("','", $_SESSION["show_donations_accounts"])."')";
	$all_accounts = db_select_data("ko_donations_accounts", $a_where, "*", "ORDER BY number ASC, name ASC");

	$account_data = array();
	foreach ($all_accounts as $account) {
		$account_data[$account['id']] = array();
	}
	apply_donations_filter($z_where, $z_limit);
	for($y = $yearStart; $y <= $yearEnd; $y++) {
		$es = db_select_data("ko_donations", "WHERE (YEAR(`$date_field`) = '$y') $z_where", "account, sum(`amount`) as `amount_sum`", 'GROUP BY `account`');
		$amount_per_account = array();
		foreach ($es as $e) {
			$amount_per_account[$e['account']] = $e['amount_sum'];
		}
		foreach ($all_accounts as $account) {
			$amount = $amount_per_account[$account['id']];
			$account_data[$account['id']][$y] = $amount ? floatval($amount) : 0.0;
		}
	}

	$data = array();
	$row = array('key');
	for($y = $yearStart; $y <= $yearEnd; $y++) {
		$row[] = $y . ' ';
	}
	$data[] = $row;
	foreach ($all_accounts as $account) {
		$row = array();
		$row[] = $account['name'] . '(' . $account['number'] . ')';
		for($y = $yearStart; $y <= $yearEnd; $y++) {
			$row[] = $account_data[$account['id']][$y];;
		}
		$data[] = $row;
	}

	return $data;
}//ko_donations_get_stats_accounts()




function ko_donations_merge() {
	global $smarty;

	//Get donators
	$includeIds = array();
	$donators = db_select_data("ko_donations", "WHERE 1", "person, COUNT(*) as num", "GROUP by `person`");
	foreach($donators as $person) {
		$includeIds[] = $person['person'];
	}
	$excludeSql = '';
	if (sizeof($includeIds) > 0) {
		$excludeSql = "`id` IN (".implode(',', $includeIds).")";
	}

	$gc = $rowcounter = 0;
	$group[$gc] = array("titel" => '', "state" => "open");

	$group[$gc]["row"][$rowcounter++]["inputs"][0] = array("desc" => getLL("donations_merge_person2"),
		"type" => "peoplesearch",
		"name" => "merge_person2",
		"exclude_sql" => $excludeSql,
	);
	$group[$gc]["row"][$rowcounter++]["inputs"][0] = array("desc" => getLL("donations_merge_person1"),
		"type" => "peoplesearch",
		"name" => "merge_person1",
		"exclude_sql" => $excludeSql,
		"single" => TRUE,
	);

	$smarty->assign("tpl_titel", getLL("donations_merge_title"));
	$smarty->assign("tpl_submit_value", getLL("donations_merge_submit"));
	$smarty->assign("tpl_action", "submit_merge");
	$smarty->assign("tpl_cancel", "list_donations");
	$smarty->assign("tpl_groups", $group);
	$smarty->assign("help", ko_get_help("donations", "merge"));

	$smarty->display('ko_formular.tpl');
}//ko_donations_merge()




function ko_donations_export_person($mode) {
	global $access, $ko_path;

	apply_donations_filter($z_where, $z_limit);
	$address_columns = array('firm', 'department', 'anrede', 'MODULEsalutation_formal', 'MODULEsalutation_informal', 'vorname', 'nachname', 'adresse', 'adresse_zusatz', 'plz', 'ort', 'land');
	if ($mode == 'person') {
		$address_columns[] = 'id';
		$address_columns[] = 'deleted';
	}

	$combine_accounts = ko_get_userpref($_SESSION['ses_userid'], 'donations_export_combine_accounts');

	//Get donators
	$donators = db_select_distinct('ko_donations', 'person', '', 'WHERE 1 '.$z_where);

	//Get accounts
	$ids = array();
	foreach($_SESSION['show_donations_accounts'] as $d) {
		if($access['donations']['ALL'] > 0 || $access['donations'][$d] > 0) $ids[] = $d;
	}
	if(sizeof($ids) > 0) {
		$a_where = " `id` IN ('".implode("','", $ids)."') ";
	} else {
		$a_where = ' 1=2 ';
	}
	$accounts = db_select_data('ko_donations_accounts', 'WHERE '.$a_where, '*', 'ORDER BY number ASC, name ASC');
	$sum_only = sizeof($accounts) > 50 || $combine_accounts;

	$newDonationsAccount = $_POST['donation_account'];
	$sortKey2MainPerson = array();

	//Handle every single donator
	$rowcounter = 0;
	$done_famids = array();

	$restricted_leute_ids = ko_apply_leute_information_lock();
	foreach($donators as $pid) {
		//Address data
		ko_get_person_by_id($pid, $person, TRUE);  //Include deleted addresses

		//Add salutations
		kota_listview_salutation_informal($person['MODULEsalutation_informal'], array('dataset' => $person));
		kota_listview_salutation_formal($person['MODULEsalutation_formal'], array('dataset' => $person));

		//Merge couples
		if( ($mode == 'couple' && $person['famid'] > 0 && in_array($person['famfunction'], array('husband', 'wife')))
			|| ($mode == 'family' && $person['famid'] > 0) ) {
			if(in_array($person['famid'], $done_famids)) continue;
			$done_famids[] = $person['famid'];

			$famfunctions = $mode == 'couple' ? array('husband', 'wife') : '';
			ko_get_personen_by_familie($person['famid'], $members, $famfunctions);

			$mainPerson = NULL;
			foreach ($members as $member) {
				if ($member['famfunction'] == 'husband') $husband = $member;
				else if ($member['famfunction'] == 'wife') $wife = $member;
				$other = $member;
			}
			if (isset($husband)) $mainPerson = $husband;
			else if (isset($wife)) $mainPerson = $wife;
			else $mainPerson = $other;

			$pids = array_keys($members);

			$family = ko_get_familie($person['famid']);
			$person['anrede'] = $family['famanrede'] ? $family['famanrede'] : getLL('ko_leute_anrede_family');
			$person['nachname'] = $family['famlastname'] ? $family['famlastname'] : $family['nachname'];
			//If no special family values are given, set first name to empty ('Fam', '', 'Lastname')
			if(!$family['famanrede'] && !$family['famfirstname'] && !$family['famlastname'] && ko_get_userpref($_SESSION['ses_userid'], 'leute_force_family_firstname') == 0) {
				$person['vorname'] = '';
			} else {
				if($family['famfirstname']) {
					$person['vorname'] = $family['famfirstname'];
				} else {
					//use first names of parents for firstname-col
					$parents = db_select_data('ko_leute', "WHERE `famid` = '".$person['famid']."' AND `famfunction` IN ('husband', 'wife') AND `deleted` = '0'".ko_get_leute_hidden_sql(), 'famfunction,vorname', 'ORDER BY famfunction ASC');
					$parent_values = array();
					foreach($parents as $parent) $parent_values[] = $parent['vorname'];
					$person['vorname'] = implode((' '.getLL('family_link').' '), $parent_values);
				}
			}
		}
		//Export as single person
		else {
			$mainPerson = $person;
			$pids = array($pid);
		}

		// If persons in household are all hidden/deleted, we dont get list of pids, so skip the donation
		if(!is_array($pids) || sizeof($pids) == 0) continue;

		//Sort key
		$sort_key = $person['nachname'].$person['vorname'].'_'.$rowcounter;

		if(!empty($restricted_leute_ids)) {
			if (in_array($pid, $restricted_leute_ids)) {
				$data[$sort_key][] = getLL("yes");
			} else {
				$data[$sort_key][] = getLL("no");
			}
		}

		foreach($address_columns as $col) {
			if($col == 'deleted') {
				$data[$sort_key][] = $person[$col] == 1 ? getLL('yes') : getLL('no');
			} else {
				$data[$sort_key][] = $person[$col];
			}
		}

		$total_amount = $total_num = 0;
		//Get donations
		$donations = db_select_data('ko_donations', "WHERE `person` IN (".implode(',', $pids).") ".$z_where, '*');
		if($sum_only) {
			$total_num = sizeof($donations);
			foreach($donations as $d) $total_amount += $d['amount'];
		} else {
			$dons = array();
			foreach($donations as $d) {
				$dons[$d['account']][] = $d['amount'];
			}
			foreach($accounts as $account) {
				$data[$sort_key][] = sizeof($dons[$account['id']]);
				$data[$sort_key][] = array_sum($dons[$account['id']]);

				$total_num += sizeof($dons[$account['id']]);
				$total_amount += array_sum($dons[$account['id']]);
			}
		}

		$data[$sort_key][] = $total_num;
		$data[$sort_key][] = $total_amount;

		$sortKey2MainPerson[$sort_key] = $mainPerson;

		$rowcounter++;
	}//foreach(donators as pid)

	ksort($data);

	$contactPids = array();
	foreach ($sortKey2MainPerson as $sortKey => $person) {
		$contactPids[] = $person['id'];
	}

	// create crm contact and
	$contactId = ko_create_crm_contact_from_post(TRUE, array('leute_ids' => implode(',', $contactPids)));

	// compute reference number for new donations
	if ($newDonationsAccount && ko_module_installed('vesr')) {
		$doCrm = $contactId ? TRUE : FALSE;
		foreach ($sortKey2MainPerson as $sortKey => $person) {
			$refnumber = ko_donations_get_refnumber($newDonationsAccount, $person['id'], $doCrm, $contactId);

			$data[$sortKey][] = $refnumber;
		}
	}

	//prepare header
	if(!empty($restricted_leute_ids)) {
		$header[] = getLL('kota_ko_leute_information_lock');
	}

	foreach($address_columns as $col) {
		$header[] = getLL('kota_ko_leute_'.$col);
	}
	if(!$sum_only) {
		foreach($accounts as $account) {
			$header[] = $account['number'].' '.$account['name'].' ('.getLL('donations_export_num').')';
			$header[] = $account['number'].' '.$account['name'].' ('.getLL('donations_export_amount').')';
		}
	}
	$header[] = getLL('total').' ('.getLL('donations_export_num').')';
	$header[] = getLL('total').' ('.getLL('donations_export_amount').')';

	if ($newDonationsAccount && ko_module_installed('vesr')) {
		$header[] = getLL('donations_export_refnumber');
	}

	//Export to Excel
	$filename = $ko_path.'download/excel/'.getLL('donations_export_filename').strftime('%d%m%Y_%H%M%S', time()).'.xlsx';
	$filename = ko_export_to_xlsx($header, $data, $filename, getLL('donations_export_title'));

	return $filename;
}//ko_donations_export_person()





function ko_donations_export_monthly() {
	global $access, $ko_path;
	
	$date_field = ko_get_userpref($_SESSION['ses_userid'], 'donations_date_field');
	if(!$date_field || !in_array($date_field, array('date', 'valutadate'))) $date_field = 'date';

	if($_SESSION["donations_filter"]["date1"] == '') {
		$resetStart = 1;
		$_SESSION["donations_filter"]["date1"] = date('Y').'-01-01';
	}
	$startDate = $_SESSION["donations_filter"]["date1"];
	if ($_SESSION["donations_filter"]["date2"] == '') {
		$resetEnd = 1;
		$_SESSION["donations_filter"]["date2"] = date('Y').'-12-31';
	}
	$endDate = $_SESSION["donations_filter"]["date2"];

	apply_donations_filter($z_where, $z_limit);
		
	$start = substr( $startDate , 0 , 4 ).substr( $startDate , 5 , 2 );
	$end = substr( $endDate , 0 , 4 ).substr( $endDate , 5 , 2 );
	
	//if sessiondates are set to defaultdate, set back
	if ($resetStart == 1) $_SESSION["donations_filter"]["date1"] = '';
	if ($resetEnd == 1) $_SESSION["donations_filter"]["date2"] = '';	

	$combine_accounts = ko_get_userpref($_SESSION['ses_userid'], 'donations_export_combine_accounts');
	

	$address_columns = array( "vorname", "nachname", "adresse",  "plz", "ort" );

	//Get donators
	$donators = db_select_distinct("ko_donations", "person", "", "WHERE 1 ".$z_where);

	//Get accounts
	$ids = array();
	foreach($_SESSION['show_donations_accounts'] as $d) {
		if($access['donations']['ALL'] > 0) {
			$ids[] = $d;
		} else if ($access['donations'][$d] > 0) {
			 $ids[] = $d;
		}
	}
	if(sizeof($ids) > 0) {
		$a_where = " `id` IN ('".implode("','", $ids)."') ";
	} else {
		$a_where = ' 1=2 ';
	}
	
	$accounts = db_select_data('ko_donations_accounts', 'WHERE '.$a_where, '*', 'ORDER BY number ASC, name ASC');

	//Promise filter
	$where = " `promise` = '0' ";

	//Handle every single donator
	$rowcounter = 0;

	$restricted_leute_ids = ko_apply_leute_information_lock();
	foreach($donators as $pid) {
		//Address data
		$person = db_select_data('ko_leute', "WHERE `id` = '$pid'", '*', '', '', TRUE);
		//Sort key
		$sort_key = $person["nachname"].$person["vorname"]."_".$rowcounter;

		if(ko_get_setting('leute_information_lock')) {
			if (in_array($pid, $restricted_leute_ids)) {
				$data[$sort_key][] = getLL("yes");
			} else {
				$data[$sort_key][] = getLL("no");
			}
		}

		foreach($address_columns as $col) {
			$data[$sort_key][] = $person[$col];
		}

		$sum = 0;
		$accountSum = array();
		$monthcounter = $start;
		while ($monthcounter <= $end ) {
			$month = substr($monthcounter, 4, 2);
			$year = substr($monthcounter, 0, 4);

			if($combine_accounts) {
				$donations = db_select_data("ko_donations", "WHERE `person` = '$pid' AND `account` IN (".implode(',', array_keys($accounts)).") AND MONTH(`$date_field`) = '$month' AND YEAR(`$date_field`) = '$year' AND $where", "*, SUM(`amount`) AS total", 'GROUP BY person');
				$row = array_shift($donations);
		
				//Sum up the amounts
				//$accountSum[$account['id']] += (float)$row['total'];
				$sum += (float)$row['total'];
				$data[$sort_key][] = number_format((float)$row['total'], 2, '.', '');
			} else {
				//Get donations for this user for each account
				foreach($accounts as $account) {
					//Get number of donations
					$donations = db_select_data("ko_donations", "WHERE `person` = '$pid' AND `account` = '".$account["id"]."' AND MONTH(`$date_field`) = '$month' AND YEAR(`$date_field`) = '$year' AND $where", "*, SUM(`amount`) AS total", 'GROUP BY person');
					$row = array_shift($donations);
			
					//Sum up the amounts
					$accountSum[$account['id']] += (float)$row['total'];
					$sum += (float)$row['total'];
					$data[$sort_key][] = number_format((float)$row['total'], 2, '.', '');
				}
			}
			$monthcounter++;
			if (substr($monthcounter, 4, 2) == '13') $monthcounter = intval($monthcounter) + 100 - 12; // 100 = add a year - 12 = set to january (01)
		}
		
		foreach($accountSum as $s) {
			$data[$sort_key][] = $s;
		}
		$data[$sort_key][] = $sum;
		$rowcounter++;
	}//foreach(donators as pid)

	ksort($data, SORT_LOCALE_STRING);

	//prepare header
	if(!empty($restricted_leute_ids)) {
		$header1[] = getLL('kota_ko_leute_information_lock');
	}

	foreach($address_columns as $col) {
		$header1[] = getLL("kota_ko_leute_".$col);
	}
	$monthcounter = $start;
	while($monthcounter <= $end ) {
		$month = substr($monthcounter , 4,2);
		$month = strftime('%B',mktime(1,1,1, $month, 01, date('Y')));
		$year = substr($monthcounter , 0,4);
		$set = 0;
		if($combine_accounts) {
			$header1[] = $month.' '.$year;
		} else {
			foreach($accounts as $account) {
				if ($set != 1) {
					$header1[] = $month.' '.$year;
					$set = 1;
				} else {
					$header1[] = '';
				}
			}
		}
		$monthcounter++;
		if (substr($monthcounter , 4,2) == '13') $monthcounter = intval($monthcounter) + 100 - 12; // 100 = add a year - 12 = set to january (01)
	}
	$set = 0;
	if($combine_accounts) {
		$header1[] = getLL('total').':';
	} else {
		foreach($accounts as $account) {
			if ($set != 1) {
				$header1[] = getLL('total').':';
				$set = 1;
			} else {
				$header1[] = '';
			}
		}
	}
	$header1[] = "";


	foreach($address_columns as $col) {
		$header2[] = "";
	}
	$monthcounter = $start;
	while ($monthcounter <= $end ) {
		foreach($accounts as $account) {
			$header2[] = $account["number"]." ".$account["name"];
		}
		$monthcounter++;
		if (substr($monthcounter , 4,2) == '13') $monthcounter = intval($monthcounter) + 100 - 12; // 100 = add a year - 12 = set to january (01)
	}
	foreach($accounts as $account) {
		$header2[] = $account["number"]." ".$account["name"];
	}
	$header2[] = getLL("donations_export_allaccounts");	


	//Add sum for each column
	$cols = array();
	$letters = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
	foreach(array_merge(array(''), $letters) as $letter1) {
		foreach($letters as $letter2) {
			$cols[] = $letter1.$letter2;
		}
	}

	if (sizeof($data) > 0) {
		$sum_row = array();
		foreach($address_columns as $col) {
			$sum_row[] = '';
		}
		$colc = sizeof($address_columns);
		$limit = $combine_accounts ? 13 : (13*sizeof($accounts)+1);
		for($i = 0; $i < $limit; $i++) {  //12 for all months plus one for the totals column
			$sum_row[] = '=SUM('.$cols[$colc].($combine_accounts ? 2 : 3).':'.$cols[$colc].(sizeof($data)+($combine_accounts ? 1 : 2)).')';
			$colc++;
		}
		$data['_sums'] = $sum_row;

		//Add formating for sum rows and columns
		$formatting = array('formats' => array('colsum' => array('bold' => 1, 'top' => 1), 'rowsum' => array('bold' => 1, 'left' => 1)));
		$formatting['rows'][sizeof($data)+($combine_accounts ? 0 : 1)] = 'colsum';

		$col = sizeof($address_columns) + ($combine_accounts ? 12 : 12*sizeof($accounts));
		for($row = 0; $row < sizeof($data)+($combine_accounts ? 1 : 2); $row++) {
			if($combine_accounts) {
				$formatting['cells'][$row.':'.$col] = 'rowsum';
			} else {
				$c = $col;
				for($i=0; $i < sizeof($accounts)+1; $i++) {
					$formatting['cells'][$row.':'.$c] = 'rowsum';
					$c++;
				}
			}
		}
	}
		
	//Export to Excel
	if($combine_accounts) {
		$header = $header1;
	} else {
		$header = array($header1, $header2);
	}
	$filename = $ko_path."download/excel/".getLL("donations_export_filename").strftime("%d%m%Y_%H%M%S", time()).".xlsx";
	$filename = ko_export_to_xlsx($header, $data, $filename, getLL('donations_export_title'), 'landscape', array(), $formatting);

	return $filename;
}//ko_donations_export_monthly()




/**
	* Filter und Limit anwenden
	*/
function apply_donations_filter(&$z_where, &$z_limit) {
	global $access;

	$date_field = ko_get_userpref($_SESSION['ses_userid'], 'donations_date_field');
	if(!$date_field || !in_array($date_field, array('date', 'valutadate'))) $date_field = 'date';

	//Accounts from itemlist
	$z_where = "";
	foreach($_SESSION['show_donations_accounts'] as $d) {
		if($access['donations']['ALL'] > 0 || $access['donations'][$d] > 0) $z_where .= " `account` = '$d' OR ";
	}
	if($z_where) $z_where = " AND (".substr($z_where, 0, -3).") ";
	else $z_where = " AND 1=2 ";


	//Promise
	if($_SESSION['donations_filter']['promise'] == 1) {
		$z_where .= " AND `promise` = '1' ";
	} else {
		$z_where .= " AND `promise` = '0' ";
	}


	//Apply filters
	foreach($_SESSION["donations_filter"] as $key => $value) {
		if(!$value) continue;
		switch($key) {
			case "date1":
				ko_guess_date($_SESSION["donations_filter"][$key], "first");
				$z_where .= " AND `$date_field` >= '".$_SESSION["donations_filter"][$key]."' ";
			break;

			case "date2":
				ko_guess_date($_SESSION["donations_filter"][$key], "last");
				$z_where .= " AND `$date_field` <= '".$_SESSION["donations_filter"][$key]."' ";
			break;

			case "leute":
				if(substr($_SESSION['donations_filter'][$key], 0, 3) == '@G@') $filterset = ko_get_userpref('-1', substr($_SESSION['donations_filter'][$key], 3), 'filterset');
				else $filterset = ko_get_userpref($_SESSION["ses_userid"], $_SESSION["donations_filter"][$key], "filterset");
				$filter = unserialize($filterset[0]["value"]);
				//Include deleted addresses here (last TRUE) as we are searching for donations
				if(TRUE === apply_leute_filter($filter, $leute_where, TRUE, '', '', TRUE)) {
					$leute = db_select_data("ko_leute", "WHERE 1 ".$leute_where, "id");
					if(sizeof($leute) == 0) {
						$z_where .= " AND 1=2 ";
					} else {
						$z_where .= " AND `person` IN (".implode(",", array_keys($leute)).") ";
					}
				}
			break;

			case "personString":
				$stringparts = explode(" ",$_SESSION["donations_filter"][$key]);
				
				$personStringWhere = "";
				foreach($stringparts as $part) {
					$personStringWhere .= "OR `nachname` LIKE '%$part%' OR `vorname` LIKE '%$part%' OR `firm` LIKE '%$part%' ";
				}
				$personStringWhere = substr_replace( $personStringWhere , 'WHERE (' , 0 , 2 ).")";
				$personString = db_select_data("ko_leute",$personStringWhere, "id");
				if(sizeof($personString) == 0) {
					$z_where .= " AND 1=2 ";
				} else {
					$z_where .= " AND `person` IN (".implode(",", array_keys($personString)).") ";
				}
			break;

			case "person":
				$z_where .= " AND `person` = '".$_SESSION["donations_filter"][$key]."' ";
			break;

			case "amount":
				$v = $_SESSION['donations_filter'][$key];
				if(in_array(substr($v, 0, 1), array('>', '<', '='))) {
					$a = intval(substr($v, 1));
					$o = substr($v, 0, 1);
					if($o == '<' || $o == '>') $o .= '=';
					$z_where .= " AND `amount` ".substr($v, 0, 1)." '$a' ";
				} else if(FALSE !== strpos($v, '-')) {
					list($a1, $a2) = explode('-', $v);
					$a1 = intval($a1); $a2 = intval($a2);
					if($a2 < $a1) {
						$t = $a1; $a1 = $a2; $a2 = $t;
					}
					$z_where .= " AND `amount` >= '$a1' AND `amount` <= '$a2' ";
				} else {
					$z_where .= " AND `amount` LIKE '".str_replace('*', '%', $_SESSION['donations_filter'][$key])."%' ";
				}
			break;

			case "thanked":
				if ($value == 'yes') {
					$z_where .= " AND `thanked` = 1 ";
				} else if ($value == 'no') {
					$z_where .= " AND `thanked` = 0 ";
				}
		}//switch(key)
	}//foreach(SESSION[filter])

	$kota_where = kota_apply_filter('ko_donations');
	if($kota_where != '') $z_where .= " AND ($kota_where) ";

	//print "where: $z_where<br />";

	//Limit bestimmen
  $z_limit = "LIMIT " . ($_SESSION["show_start"]-1) . ", " . $_SESSION["show_limit"];
}//apply_donations_filter()




function ko_donations_settings() {
	global $smarty;
	global $access;

	if($access['donations']['MAX'] < 1) return FALSE;

	//build form
	$gc = 0;
	$rowcounter = 0;
	$frmgroup[$gc]['titel'] = getLL('settings_title_user');
	$frmgroup[$gc]['tab'] = true;

	$values = array('list_donations');
	$descs = array(getLL('submenu_donations_list_donations'));
	if($access['donations']['MAX'] > 3) {
		$values[] = 'list_accounts';
		$descs[] = getLL('submenu_donations_list_accounts');
	}
	if(ko_get_setting('donations_use_repetition')) {
		$values[] = 'list_reoccuring_donations';
		$descs[] = getLL('submenu_donations_list_reoccuring_donations');
	}
	$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('donations_settings_default_view'),
		'type' => 'select',
		'name' => 'sel_donations',
		'values' => $values,
		'descs' => $descs,
		'value' => ko_html(ko_get_userpref($_SESSION['ses_userid'], 'default_view_donations'))
	);
	$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('admin_settings_limits_numberof_donations'),
		'type' => 'text',
		'params' => 'size="10"',
		'name' => 'txt_limit_donations',
		'value' => ko_html(ko_get_userpref($_SESSION['ses_userid'], 'show_limit_donations'))
	);

	$value = ko_get_userpref($_SESSION['ses_userid'], 'donations_stats_show_num');
	$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('donations_settings_stats_show_num'),
		'type' => 'switch',
		'label_0' => getLL('no'),
		'label_1' => getLL('yes'),
		'name' => 'chk_stats_show_num',
		'value' => $value == '' ? 0 : $value,
	);
	$value = ko_get_userpref($_SESSION['ses_userid'], 'donations_export_combine_accounts');
	$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('donations_settings_export_combine_accounts'),
		'type' => 'switch',
		'label_0' => getLL('no'),
		'label_1' => getLL('yes'),
		'name' => 'chk_export_combine_accounts',
		'value' => $value == '' ? 0 : $value,
	);

	if(ko_get_setting('donations_use_repetition')) {
		$value = ko_get_userpref($_SESSION['ses_userid'], 'donations_recurring_prompt');
		$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('donations_settings_recurring_prompt'),
			'type' => 'switch',
			'label_0' => getLL('no'),
			'label_1' => getLL('yes'),
			'name' => 'chk_recurring_prompt',
			'value' => $value == '' ? 0 : $value,
		);
	}
	$value = ko_get_userpref($_SESSION['ses_userid'], 'donations_date_field');
	$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('donations_settings_date_field'),
		'type' => 'select',
		'name' => 'sel_date_field',
		'values' => array('date', 'valutadate'),
		'descs' => array(getLL('kota_ko_donations_date'), getLL('kota_ko_donations_valutadate')),
		'value' => ko_html($value),
	);

	if($access['donations']['MAX'] > 3) {
		$gc++;
		$rowcounter = 0;
		$frmgroup[$gc]['titel'] = getLL('settings_title_global');
		$frmgroup[$gc]['tab'] = true;

		//Get filter presets for this user and the global ones to select from
		$values = array();
		$descs = array();
		$values[] = $descs[] = '';
		$filterset = array_merge((array)ko_get_userpref('-1', '', 'filterset', 'ORDER BY `key` ASC'), (array)ko_get_userpref($_SESSION['ses_userid'], '', 'filterset', 'ORDER BY `key` ASC'));
		//Get the currently stored filter
		$value = ko_get_setting('ps_filter_sel_ds1_koi[ko_donations][person]');
		$sel_value = '';
		foreach($filterset as $f) {
			$values[] = $f['user_id'] == '-1' ? '@G@'.$f['key'] : $f['key'];
			$descs[] = $f['user_id'] == '-1' ? getLL('itemlist_global_short').' '.$f['key'] : $f['key'];
			if($value == $f['value']) $sel_value = $f['user_id'] == '-1' ? '@G@'.$f['key'] : $f['key'];
		}
		//Add entry for a stored filter preset not available anymore (or stored by another user)
		if($sel_value == '' && $value != '') {
			$values[] = '-1';
			$descs[] = '['.getLL('other').']';
			$sel_value = '-1';
		}

		$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('donations_settings_ps_filter'),
			'type' => 'select',
			'name' => 'sel_ps_filter',
			'values' => $values,
			'descs' => $descs,
			'value' => $sel_value,
		);
		$value = ko_get_setting('donations_show_export_page');
		$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('donations_settings_show_intermediate_export_page'),
			'type' => 'switch',
			'label_0' => getLL('no'),
			'label_1' => getLL('yes'),
			'name' => 'chk_show_export_page',
			'value' => $value == '' ? 0 : $value,
		);

		$value = ko_get_setting('donations_use_promise');
		$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('donations_settings_use_promise'),
			'type' => 'switch',
			'label_0' => getLL('no'),
			'label_1' => getLL('yes'),
			'name' => 'chk_use_promise',
			'value' => $value == '' ? 0 : $value,
		);
		$value = ko_get_setting('donations_use_repetition');
		$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('donations_settings_use_repetition'),
			'type' => 'switch',
			'label_0' => getLL('no'),
			'label_1' => getLL('yes'),
			'name' => 'chk_use_repetition',
			'value' => $value == '' ? 0 : $value,
		);

	}

	//Allow plugins to add further settings
	hook_form('donation_settings', $frmgroup, '', '');


	//display the form
	$smarty->assign('tpl_titel', getLL('donations_settings_form_title'));
	$smarty->assign('tpl_submit_value', getLL('save'));
	$smarty->assign('tpl_action', 'submit_settings');
	$cancel = ko_get_userpref($_SESSION['ses_userid'], 'default_view_donations');
	if(!$cancel) $cancel = 'list_donations';
  $smarty->assign('tpl_cancel', $cancel);
	$smarty->assign('tpl_groups', $frmgroup);
	$smarty->assign('help', ko_get_help('donations', 'donation_settings'));

	$smarty->display('ko_formular.tpl');

}//ko_donation_settings()


function ko_donations_export_settings() {
	global $smarty, $access;

	$mode = $_SESSION['export_donations']['export_mode'];

	//build form
	$gc = 0;
	$rowcounter = 0;
	//$frmgroup[$gc]['titel'] = getLL('leute_export_xls_settings');

	$values = $descs = array('');
	$allAccounts = db_select_data('ko_donations_accounts', "WHERE 1=1");
	foreach ($allAccounts as $account) {
		if ($access['donations'][$account['id']] > 1 || $access['donations']['ALL'] > 1) {
			$values[] = $account['id'];
			$descs[] = "{$account['name']} ({$account['number']})";
		}
	}

	$value = $_POST['donation_account'];
	if (!$value) $value = '';
	$frmgroup[$gc]['row'][$rowcounter++]['inputs'][0] = array('desc' => getLL('donations_export_settings_account'),
		'type' => 'select',
		'name' => 'donation_account',
		'values' => $values,
		'descs' => $descs,
		'value' => $values,
	);

	//display the form
	$smarty->assign('tpl_titel', getLL('donations_export_settings_title'));
	$smarty->assign('tpl_hidden_inputs', array(array('name' => 'export_mode', 'value' => $mode)));
	$smarty->assign('tpl_submit_value', getLL('ok'));
	$smarty->assign('tpl_action', 'export_donations');
	$cancel = ko_get_userpref($_SESSION['ses_userid'], 'default_view_donations');
	if(!$cancel) $cancel = 'list_donations';
	$smarty->assign('tpl_cancel', $cancel);
	$crmContactGroup = ko_get_crm_contact_form_group(array('leute_ids'), array('type' => 'letter'));
	$frmgroup[sizeof($frmgroup)] = $crmContactGroup[0];
	$smarty->assign('tpl_groups', $frmgroup);
	$smarty->display('ko_formular.tpl');
}

?>
