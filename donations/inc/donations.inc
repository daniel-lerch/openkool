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


function ko_list_donations($output=TRUE, $mode="html", $dontApplyLimit=FALSE) {
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
	$list->setAccessRights(array('edit' => 3, 'delete' => 3), $access['donations']);
	$list->setActions($actions);
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

	$result = mysql_query("SELECT DISTINCT `person` FROM `ko_donations` WHERE 1 $z_where");
	$num_person = mysql_num_rows($result);
	mysql_free_result($result);

	//Averages
	$avg = $rows ? $total_amount/$rows : 0;
	$avg_person = $num_person ? $total_amount/$num_person : 0;
	
	$list_footer = $smarty->get_template_vars('list_footer');
	$list_footer[] = array("label" => "", "button" => sprintf(getLL("donations_list_footer_stats_totals"),
																														number_format($total_amount, 2, '.', "'"),
																														number_format($rows, 0, '.', "'"),
																														number_format($num_person, 0, '.', "'"))
	);
	$list_footer[] = array("label" => "", "button" => sprintf(getLL("donations_list_footer_stats_averages"),
																														number_format($avg, 2, '.', "'"),
																														number_format($avg_person, 2, '.', "'"))
	);

	$list->setFooter($list_footer);


	//Output the list
	if($output) {
		$list->render($es, $mode, getLL("donations_export_filename"));
		if($mode == "xls") return $list->xls_file;
	} else {
		print $list->render($es);
	}
}//ko_list_donations()





function ko_list_accounts($output=TRUE) {
	global $smarty;
	global $access;

	if($access['donations']['MAX'] < 4) return;

	$rows = db_get_count('ko_donations_accounts', 'id');
	if($_SESSION['show_start'] > $rows) $_SESSION['show_start'] = 1;
  $z_limit = 'LIMIT '.($_SESSION['show_start']-1).', '.$_SESSION['show_limit'];
	$es = db_select_data('ko_donations_accounts', '', '*', 'ORDER BY number ASC', $z_limit);

	$list = new kOOL_listview();

	$list->init("donations", "ko_donations_accounts", array("chk", "edit", "delete"), $_SESSION["show_start"], $_SESSION["show_limit"]);
	$list->setTitle(getLL("donations_accounts_list_title"));
	$list->setAccessRights(FALSE);
	$list->setActions(array("edit" => array("action" => "edit_account"),
													"delete" => array("action" => "delete_account", "confirm" => TRUE))
										);
	$list->setStats($rows);
	$list->setSort(FALSE);

	//Output the list
	if($output) {
		$list->render($es);
	} else {
		print $list->render($es);
	}
}//ko_list_accounts()




function ko_list_reoccuring_donations($output=TRUE) {
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
	unset($KOTA["ko_donations"]["_listview"][10]);  //Don't show date of last donation

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
												 "button" => '<input type="submit" name="submit_do_reoccuring" onclick="set_action(\'do_reoccuring_donations\');this.submit;" value="'.getLL("donations_list_footer_do_reoccuring_button").'" />',
												 );

	$list->setFooter($list_footer);

	//Output the list
	if($output) {
		$list->render($es);
	} else {
		print $list->render($es);
	}
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




function ko_donations_stats($output=TRUE, $mode="html", $_year='') {
	global $access, $smarty, $ko_path;

	if($access['donations']['MAX'] < 4) return FALSE;

	$date_field = ko_get_userpref($_SESSION['ses_userid'], 'donations_date_field');
	if(!$date_field || !in_array($date_field, array('date', 'valutadate'))) $date_field = 'date';

	//Promise condition
	$where = "`promise` = '0' ";

	switch($_SESSION["stats_mode"]) {
		case "year":
			$years = db_select_distinct("ko_donations", "YEAR(`$date_field`)", '', "WHERE $where");
			foreach($years as $key => $value) if(!$value) unset($years[$key]);

			//Get data for current year
			$use_year = $_year ? $_year : $_SESSION['stats_year'];
			$use_year = intval($use_year);
			$data = ko_donations_get_stats_year($use_year);

			//Row title (account)
			$a_where = sizeof($_SESSION['show_accounts']) > 0 ? "WHERE `id` IN ('".implode("','", $_SESSION["show_accounts"])."')" : "WHERE 1=2";
			$all_accounts = db_select_data("ko_donations_accounts", $a_where, "*", "ORDER BY number ASC");
			foreach($all_accounts as $a) {
				$data["accounts"][$a["id"]]["name"] = $a["number"]." ".$a["name"];
				$data['accounts'][$a['id']]['id'] = $a['id'];
			}
			//header
			for($m = 1; $m <= 12; $m++) {
				$header[] = strftime("%B", mktime(1,1,1, $m, 1, date("Y")));
			}

			//Export to Excel
			if($mode == "xls") {
				//Data for each account
				$xls_data = array();
				$row = 0;
				foreach($data["accounts"] as $id => $a) {
					$xls_data[$row][] = $a["name"];
					for($m=1; $m<=12; $m++) {
						$xls_data[$row][] = $a[$m]["amount"];
					}
					$xls_data[$row][] = $a["total"]["amount"];
					$row++;
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
				$smarty->assign("img_year", $barChart);
				$smarty->assign("img_year_title", sprintf(getLL("donations_stats_img_year_title"), $_SESSION["stats_year"]));

				$smarty->assign("table_year_title", sprintf(getLL("donations_stats_table_year_title"), $_SESSION["stats_year"]));
				$smarty->assign("label_total", getLL("total"));
				$smarty->assign("tpl_years", $years);
				$smarty->assign("cur_year", $_SESSION["stats_year"]);
				$smarty->assign("tpl_header", $header);
				$smarty->assign("tpl_data", $data);
				$smarty->assign('show_num', ko_get_userpref($_SESSION['ses_userid'], 'donations_stats_show_num'));
				$tpl_file = "ko_donations_stats.tpl";
			}
		break;  //year

		case "month":
		break;  //month
	}//switch(stats_mode)

	//Output the list
	if($output) {
		$smarty->display($tpl_file);
	} else {
		print $smarty->fetch($tpl_file);
	}
}//ko_donations_stats()




function ko_donations_get_stats_year($year) {
	$date_field = ko_get_userpref($_SESSION['ses_userid'], 'donations_date_field');
	if(!$date_field || !in_array($date_field, array('date', 'valutadate'))) $date_field = 'date';

	$a_where = "WHERE `id` IN ('".implode("','", $_SESSION["show_accounts"])."')";
	$all_accounts = db_select_data("ko_donations_accounts", $a_where, "*", "ORDER BY number ASC");
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

		$data["grand_total"]["donations"] += $total_donations;
		$data["grand_total"]["amount"] += $total_amount;
	}//for(m=1..12)

	return $data;
}//ko_donations_get_stats_year()




function ko_donations_merge() {
	global $smarty;

	//Get donators
	$donators = db_select_data("ko_donations", "WHERE 1", "person, COUNT(*) as num", "GROUP by `person`");
	foreach($donators as $person) {
		ko_get_person_by_id($person["person"], $p);
		$sort_key = $p["nachname"].$p["vorname"].$person["person"];
		$d_values[$sort_key] = $person["person"];
		$d_descs[$sort_key] = $p["nachname"]." ".$p["vorname"].($p["ort"] ? (" ".getLL("from")." ".$p["ort"]) : "")." (".$person["num"].")";
	}
	ksort($d_values);
	ksort($d_descs);

	$gc = $rowcounter = 0;
	$group[$gc] = array("titel" => getLL("donations_merge_title"), "state" => "open");
	$group[$gc]["row"][$rowcounter++]["inputs"][0] = array("desc" => getLL("donations_merge_person1"),
																												 "type" => "select",
																												 "name" => "merge_person1",
																												 "values" => $d_values,
																												 "descs" => $d_descs,
																												 "params" => 'size="0"'
																												 );
	$group[$gc]["row"][$rowcounter++]["inputs"][0] = array("desc" => getLL("donations_merge_person2"),
																												 "type" => "doubleselect",
		  													 												 "js_func_add" => "double_select_add",
																												 "name" => "merge_person2",
																												 "values" => $d_values,
																												 "descs" => $d_descs,
																												 "params" => 'size="7"'
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
	$address_columns = array('firm', 'department', 'anrede', 'vorname', 'nachname', 'adresse', 'adresse_zusatz', 'plz', 'ort', 'land');

	//Get donators
	$donators = db_select_distinct('ko_donations', 'person', '', 'WHERE 1 '.$z_where);

	//Get accounts
	$ids = array();
	foreach($_SESSION['show_accounts'] as $d) {
		if($access['donations']['ALL'] > 0 || $access['donations'][$d] > 0) $ids[] = $d;
	}
	if(sizeof($ids) > 0) {
		$a_where = " `id` IN ('".implode("','", $ids)."') ";
	} else {
		$a_where = ' 1=2 ';
	}
	$accounts = db_select_data('ko_donations_accounts', 'WHERE '.$a_where, '*', 'ORDER BY number ASC');
	$sum_only = sizeof($accounts) > 50;

	//Handle every single donator
	$rowcounter = 0;
	$done_famids = array();
	foreach($donators as $pid) {
		//Address data
		ko_get_person_by_id($pid, $person, TRUE);  //Include deleted addresses

		//Merge couples
		if( ($mode == 'couple' && $person['famid'] > 0 && in_array($person['famfunction'], array('husband', 'wife')))
			|| ($mode == 'family' && $person['famid'] > 0) ) {
			if(in_array($person['famid'], $done_famids)) continue;
			$done_famids[] = $person['famid'];

			$famfunctions = $mode == 'couple' ? array('husband', 'wife') : '';
			ko_get_personen_by_familie($person['famid'], $members, $famfunctions);
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
					$parents = db_select_data('ko_leute', "WHERE `famid` = '".$person['famid']."' AND `famfunction` IN ('husband', 'wife')", 'famfunction,vorname', 'ORDER BY famfunction ASC');
					$parent_values = array();
					foreach($parents as $parent) $parent_values[] = $parent['vorname'];
					$person['vorname'] = implode((' '.getLL('family_link').' '), $parent_values);
				}
			}
		}
		//Export as single person
		else {
			$pids = array($pid);
		}

		//Sort key
		$sort_key = $person['nachname'].$person['vorname'].'_'.$rowcounter;
		foreach($address_columns as $col) {
			$data[$sort_key][] = $person[$col];
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
		$rowcounter++;
	}//foreach(donators as pid)

	ksort($data);

	//prepare header
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
	foreach($_SESSION['show_accounts'] as $d) {
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
	
	$accounts = db_select_data('ko_donations_accounts', 'WHERE '.$a_where, '*', 'ORDER BY number ASC');

	//Promise filter
	$where = " `promise` = '0' ";

	//Handle every single donator
	$rowcounter = 0;
	foreach($donators as $pid) {
		//Address data
		$person = db_select_data('ko_leute', "WHERE `id` = '$pid'", '*', '', '', TRUE);
		//Sort key
		$sort_key = $person["nachname"].$person["vorname"]."_".$rowcounter;
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
				$data[$sort_key][] = sprintf("%.2f", (float)$row['total']);
			} else {
				//Get donations for this user for each account
				foreach($accounts as $account) {
					//Get number of donations
					$donations = db_select_data("ko_donations", "WHERE `person` = '$pid' AND `account` = '".$account["id"]."' AND MONTH(`$date_field`) = '$month' AND YEAR(`$date_field`) = '$year' AND $where", "*, SUM(`amount`) AS total", 'GROUP BY person');
					$row = array_shift($donations);
			
					//Sum up the amounts
					$accountSum[$account['id']] += (float)$row['total'];
					$sum += (float)$row['total'];
					$data[$sort_key][] = sprintf("%.2f", (float)$row['total']);
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
	foreach($_SESSION["show_accounts"] as $d) {
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
				if(TRUE === apply_leute_filter($filter, $leute_where)) {
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
				$personStringWhere .= ko_get_leute_hidden_sql();
				$personStringWhere .= " AND `deleted` = 0 ";
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

	$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('donations_settings_default_view'),
		'type' => 'select',
		'name' => 'sel_donations',
		'values' => array('list_donations', 'list_accounts', 'list_reoccuring_donations'),
		'descs' => array(getLL('submenu_donations_list_donations'), getLL('submenu_donations_list_accounts'), getLL('submenu_donations_list_reoccuring_donations')),
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

	$value = ko_get_userpref($_SESSION['ses_userid'], 'donations_recurring_prompt');
	$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('donations_settings_recurring_prompt'),
		'type' => 'switch',
		'label_0' => getLL('no'),
		'label_1' => getLL('yes'),
		'name' => 'chk_recurring_prompt',
		'value' => $value == '' ? 0 : $value,
	);
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


		$value = ko_get_setting('donations_use_promise');
		$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('donations_settings_use_promise'),
			'type' => 'switch',
			'label_0' => getLL('no'),
			'label_1' => getLL('yes'),
			'name' => 'chk_use_promise',
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

?>
