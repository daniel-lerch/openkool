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

$ko_path = "../";
$ko_menu_akt = "donations";

include($ko_path . "inc/ko.inc");
include("inc/donations.inc");

//Redirect to SSL if needed
ko_check_ssl();

if(!ko_module_installed("donations")) {
	header("Location: ".$BASE_URL."index.php");  //Absolute URL
}

ob_end_flush();  //Puffer flushen

$onload_code = "";
$infos = array();
$info_txt_add = '';

$notifier = koNotifier::Instance();

//*** Rechte auslesen
ko_get_access('donations');


//Smarty-Templates-Engine laden
require("$ko_path/inc/smarty.inc");

//kOOL Table Array
ko_include_kota(array('ko_donations', 'ko_donations_accounts'));


//*** Plugins einlesen:
$hooks = hook_include_main("donations");
if(sizeof($hooks) > 0) foreach($hooks as $hook) include_once($hook);


//*** Action auslesen:
if($_POST["action"]) {
	$do_action=$_POST["action"];
	$action_mode = "POST";
} else if($_GET["action"]) {
	$do_action=$_GET["action"];
	$action_mode = "GET";
} else {
	$do_action = $action_mode = "";
}
if(!$do_action) $do_action = "list_donations";

//Reset show_start if from another module
if($_SERVER['HTTP_REFERER'] != '' && FALSE === strpos($_SERVER['HTTP_REFERER'], '/'.$ko_menu_akt.'/')) $_SESSION['show_start'] = 1;

switch($do_action) {

	// Display
	case 'list_donations':
		if($access['donations']['MAX'] < 1) continue;
		if($_SESSION['show'] == 'list_donations') $_SESSION['show_start'] = 1;
		$_SESSION['show'] = 'list_donations';
	break;

	case "list_accounts":
		if($access['donations']['MAX'] < 4) continue;
		if($_SESSION['show'] == 'list_accounts') $_SESSION['show_start'] = 1;
		$_SESSION['show'] = 'list_accounts';
	break;

	case "list_reoccuring_donations":
		if($access['donations']['MAX'] < 1) continue;
		$_SESSION["show"] = "list_reoccuring_donations";
		$_SESSION["show_start"] = 1;
	break;





	//Neu:
	case 'new_donation':
		if($access['donations']['MAX'] < 2) continue;

		$_SESSION['show'] = 'new_donation';
		$onload_code = 'form_set_first_input();'.$onload_code;
	break;

	case 'new_promise':
		if($access['donations']['MAX'] < 2) continue;

		$_SESSION['show'] = 'new_promise';
		$onload_code = 'form_set_first_input();'.$onload_code;
	break;

	case 'submit_new_donation':
		if($access['donations']['MAX'] < 2) continue;

		kota_submit_multiedit('', 'new_donation', '', $changes);
		if(!$notifier->hasErrors()) {
			$infos[] = 1;
			$onload_code = 'form_set_first_input();'.$onload_code;

			//Add info text showing the data of the saved donation
			$entry = kota_get_list(array_pop($changes['ko_donations']), 'ko_donations');
			foreach($entry as $k => $v) {
				if(!trim($v)) continue;
				$info_txt_add .= $k.': <b>'.$v.'</b>, ';
			}
			if($info_txt_add != '') $info_txt_add = '<br />'.substr($info_txt_add, 0, -2);
		}
	break;

	case 'submit_as_new_donation':
		if($access['donations']['MAX'] < 2) continue;

		list($table, $columns, $ids, $hash) = explode('@', $_POST['id']);
		//Fake POST[id] for kota_submit_multiedit() to remove the id from the id. Otherwise this entry will be edited
		$new_hash = md5(md5($mysql_pass.$table.implode(':', explode(',', $columns)).'0'));
		$_POST['id'] = $table.'@'.$columns.'@0@'.$new_hash;

		kota_submit_multiedit('', 'new_donation');
		if(!$notifier->hasErrors()) {
			$infos[] = 1;
			$onload_code = 'form_set_first_input();'.$onload_code;
		}
	break;


	case 'submit_new_promise':
		if($access['donations']['MAX'] < 2) continue;

		$id = kota_submit_multiedit('', 'new_donation');
		if(!$notifier->hasErrors()) {
			$infos[] = 2;
			$onload_code = 'form_set_first_input();'.$onload_code;

			//Set promise flag
			db_update_data('ko_donations', "WHERE `id` = '$id'", array('promise' => 1));

			//Add info text showing the data of the saved donation
			$entry = kota_get_list(array_pop($changes['ko_donations']), 'ko_donations');
			foreach($entry as $k => $v) {
				if(!trim($v)) continue;
				$info_txt_add .= $k.': <b>'.$v.'</b>, ';
			}
			if($info_txt_add != '') $info_txt_add = '<br />'.substr($info_txt_add, 0, -2);
		}
	break;


	case 'do_promise':
		if($access['donations']['MAX'] < 2) continue;

		$id = format_userinput($_POST['id'], 'uint');
		if(!$id) continue;

		$old = db_select_data('ko_donations', "WHERE `id` = '$id'", '*', '', '', TRUE);
		if($access['donations']['ALL'] < 2 && $access['donations'][$old['account']] < 2) continue;

		//Set promise flag
		db_update_data('ko_donations', "WHERE `id` = '$id'", array('promise' => 0));

		//Log
		ko_log_diff('donation_from_promise', $old);

		$infos[] = 3;
	break;



	case "new_account":
		if($access['donations']['MAX'] < 4) continue;

		$_SESSION["show"] = "new_account";
		$onload_code = "form_set_first_input();".$onload_code;
	break;


	case "submit_new_account":
	case "submit_edit_account":
		if($access['donations']['MAX'] < 4) continue;

		$mode = $do_action == "submit_edit_account" ? "edit" : "new";

		list($table, $cols, $id, $hash) = explode("@", $_POST["id"]);
		if($mode == "edit" && !$id) {
			$notifier->addError(1, $do_action);
		} else {
			$new_id = kota_submit_multiedit("", ($mode == "edit" ? "edit_account" : "new_account"));

			$_SESSION["show"] = "list_accounts";
			$_SESSION["show_accounts"][] = $new_id;
		}
	break;




	//Bearbeiten
	case "edit_donation":
		if($access['donations']['MAX'] < 3) continue;

		$id = format_userinput($_POST["id"], "uint");
		$_SESSION["show"] = "edit_donation";
		$onload_code = "form_set_first_input();".$onload_code;
	break;

	case "submit_edit_donation":
		if($access['donations']['MAX'] < 3) continue;

		kota_submit_multiedit("", "edit_donation");
		if(!$notifier->hasErrors()) {
			$_SESSION["show"] = "list_donations";
		}
	break;



	case "edit_account":
		if($access['donations']['MAX'] < 4) continue;

		$id = format_userinput($_POST["id"], "uint");
		$_SESSION["show"] = "edit_account";
		$onload_code = "form_set_first_input();".$onload_code;
	break;





	//Merging
	case "merge":
		if($access['donations']['MAX'] < 3) continue;
		
		$_SESSION["show"] = "merge";
	break;


	case "submit_merge":
		if($access['donations']['MAX'] < 3) continue;

		//Get selected people
		$person1 = format_userinput($_POST["merge_person1"], "uint");
		if(!$person1) continue;
		$person2 = format_userinput($_POST["merge_person2"], "intlist");
		if(!$person2) continue;
		$merge_from = explode(",", $person2);

		//Prepare IN string for DB query
		$in = "";
		foreach($merge_from as $id) {
			$in .= "'$id', ";
		}
		$in = substr($in, 0, -2);

		//Get all donations of these people and reassign them to person1
		$donations2 = db_select_data("ko_donations", "WHERE `person` IN ($id)", "*");
		foreach($donations2 as $donation) {
			db_update_data("ko_donations", "WHERE `id` = '".$donation["id"]."'", array("person" => $person1));
		}

		ko_log("merge_donations", "$person2 --> $person1");
		$_SESSION["show"] = "list_donations";
	break;





	//Reoccuring donations
	case "do_reoccuring_donation":
	case "do_reoccuring_donations":
		if($access['donations']['MAX'] < 2) continue;

		$use_date = FALSE;
		$use_amount = FALSE;

		if($do_action == "do_reoccuring_donation") {
			$id = format_userinput($_POST["id"], "uint");
			if(!$id) continue;
			$donation = db_select_data('ko_donations', 'WHERE `id` = \''.$id.'\'', '*', '', '', TRUE);
			if($access['donations']['ALL'] < 2 && $access['donations'][$donation['account']] < 2) continue;

			$ids = array($id);

			$date = sql_datum(format_userinput($_POST['recurring_date'], 'date'));
			if(strtotime($date) > 0) $use_date = $date;

			$amount = format_userinput($_POST['recurring_amount'], 'float');
			if($amount > 0) $use_amount = $amount;
		} else {
			$ids = array();
			foreach($_POST["chk"] as $c_i => $c) {
				if($c) $ids[] = format_userinput($c_i, 'uint');
			}
			if(sizeof($ids) < 1) {
				$notifier->addError(4, $do_action);
			} else {
				$donations = db_select_data('ko_donations', 'WHERE `id` IN(\''.implode("','", $ids).'\')');
				if($access['donations']['ALL'] < 2) {
					foreach($donations as $did => $donation) {
						if($access['donations'][$donation['account']] < 2) unset($donations[$did]);
					}
				}
			}
		}

		//Process donations
		foreach($ids as $id) {
			//Get donation
			$data = db_select_data("ko_donations", "WHERE `id` = '$id'", "*", "", "", TRUE);

			//Add new data
			if($use_date) $data['date'] = $use_date;
			else $data['date'] = date('Y-m-d');
			if($use_amount) $data['amount'] = $use_amount;

			//Store new donation
			unset($data["id"]);
			db_insert_data("ko_donations", $data);

			//Set original donation to not reoccuring
			db_update_data("ko_donations", "WHERE `id` = '$id'", array("reoccuring" => 0));

			//Store log entry
			ko_log("new_donation_reoccured", $data);
		}//foreach(ids as id)


		$infos[] = 1;
		if($do_action == 'do_reoccuring_donation') {
			//Add info text showing the data of the saved donation
			$entry = kota_get_list($data, 'ko_donations');
			foreach($entry as $k => $v) {
				if(!trim($v)) continue;
				$info_txt_add .= $k.': <b>'.$v.'</b>, ';
			}
			if($info_txt_add != '') $info_txt_add = '<br />'.substr($info_txt_add, 0, -2);
		}
	break;





	//Löschen
	case "delete_donation":
		if($access['donations']['MAX'] < 3) continue;

		$id = format_userinput($_POST["id"], "uint");
		if(!$id) continue;

		$old = db_select_data("ko_donations", "WHERE `id` = '$id'", "*", "", "", TRUE);
		if($access['donations']['ALL'] < 3 && $access['donations'][$old['account']] < 3) continue;

		db_delete_data("ko_donations", "WHERE `id` = '$id'");
		ko_log_diff("del_donation", $old);
	break;


	case "delete_account":
		if($access['donations']['MAX'] < 4) continue;

		$id = format_userinput($_POST["id"], "uint");
		if(!$id) continue;

		$old = db_select_data("ko_donations_accounts", "WHERE `id` = '$id'", "*", "", "", TRUE);

		db_delete_data("ko_donations_accounts", "WHERE `id` = '$id'");
		db_delete_data("ko_donations", "WHERE `account` = '$id'");
		ko_log_diff("del_donation_account", $old);
	break;


	case "delete_reoccuring_donation":
		if($access['donations']['MAX'] < 2) continue;

		$id = format_userinput($_POST["id"], "uint");
		if(!$id) continue;
		$donation = db_select_data('ko_donations', "WHERE `id` = '$id'", '*', '', '', TRUE);
		if($access['donations']['ALL'] < 3 && $access['donations'][$donation['account']] < 3) continue;

		db_update_data("ko_donations", "WHERE `id` = '$id'", array("reoccuring" => 0));
		ko_log("edit_donation", "id: $id: reoccuring --> 0");
	break;





	//Multiedit
	case "multiedit":
		if($_SESSION["show"] == "list_accounts" && $access['donations']['MAX'] < 4) continue;
		if($_SESSION["show"] == "list_donations" && $access['donations']['MAX'] < 3) continue;

		//Zu bearbeitende Spalten
		$columns = explode(",", format_userinput($_POST["id"], "alphanumlist"));
		foreach($columns as $column) {
			$do_columns[] = $column;
		}
		if(sizeof($do_columns) < 1) $notifier->addError(4, $do_action);

		//Zu bearbeitende Einträge
		$do_ids = array();
		foreach($_POST["chk"] as $c_i => $c) {
			$edit_id = format_userinput($c_i, 'uint', TRUE);
			if(!$c || !$edit_id) continue;
			if($_SESSION['show'] == 'list_donations') {
				if($access['donations']['ALL'] > 2) {
					$do_ids[] = $edit_id;
				} else {
					$donation = db_select_data('ko_donations', "WHERE `id` = '$edit_id'", '*', '', '', TRUE);
					if($access['donations'][$donation['account']] > 2) $do_ids[] = $edit_id;
				}
			} else {
				if($access['donations']['ALL'] > 3 || $access['donations'][$edit_id] > 3) $do_ids[] = $edit_id;
			}
		}
		if(sizeof($do_ids) < 1) $notifier->addError(4, $do_action);

		//Daten für Formular-Aufruf vorbereiten
		if(!$notifier->hasErrors()) {
			$_SESSION["show_back"] = $_SESSION["show"];

			if($_SESSION["show"] == "list_accounts") {
				$order = "ORDER BY number ASC";
				$_SESSION["show"] = "multiedit_accounts";
			} else if($_SESSION["show"] == "list_donations") {
				if(substr($_SESSION['sort_donations'], 0, 6) == 'MODULE') $order = 'ORDER BY date DESC';
				else $order = 'ORDER BY '.$_SESSION['sort_donations'].' '.$_SESSION['sort_donations_order'];
				$_SESSION["show"] = "multiedit";
			}
		}

		$onload_code = "form_set_first_input();".$onload_code;
	break;



	case "submit_multiedit":
		if($_SESSION["show"] == "multiedit_accounts") {
			if($access['donations']['MAX'] < 4) continue;
			kota_submit_multiedit(4);
		} else if($_SESSION["show"] == "multiedit") {
			if($access['donations']['MAX'] < 3) continue;
			kota_submit_multiedit(3);
		}

		$_SESSION["show"] = $_SESSION["show_back"] ? $_SESSION["show_back"] : "list_donations";
	break;





	//Filter
	case "set_filter":
		if($access['donations']['MAX'] < 1) continue;

		foreach($_POST["donations_filter"] as $key => $value) {
			if(!$value) {  //No value means unset the filter
				unset($_SESSION["donations_filter"][$key]);
			} else {  //otherwise set filter
				$_SESSION["donations_filter"][$key] = $value;
			}
		}
		//Manually unset promise as unset checkbox is not set in _POST
		if(!isset($_POST['donations_filter']['promise'])) unset($_SESSION['donations_filter']['promise']);

		if(!$_SESSION["show"]) $_SESSION["show"] = "list_donations";
		$_SESSION["show_start"] = 1;
	break;


	case "clear_filter":
		if($access['donations']['MAX'] < 1) continue;

		unset($_SESSION["donations_filter"]);
		if(!$_SESSION["show"]) $_SESSION["show"] = "list_donations";
		$_SESSION["show_start"] = 1;
	break;


	case "set_person_filter":
		if($access['donations']['MAX'] < 1) continue;

		$id = format_userinput($_GET["id"], "uint");
		if(!$id) continue;

		$_SESSION["donations_filter"]["person"] = $id;
		if(!$_SESSION["show"]) $_SESSION["show"] = "list_donations";
		$_SESSION["show_start"] = 1;
	break;


	case "clear_person_filter":
		if($access['donations']['MAX'] < 1) continue;

		unset($_SESSION["donations_filter"]["person"]);
		if(!$_SESSION["show"]) $_SESSION["show"] = "list_donations";
		$_SESSION["show_start"] = 1;
	break;





	//Stats
	case "show_stats":
		if($access['donations']['MAX'] < 4) continue;

		$_SESSION["show"] = "show_stats";
	break;


	case "set_stats_year":
		if($access['donations']['MAX'] < 4) continue;

		$year = format_userinput($_GET["year"], "uint", FALSE, 4);
		if(!$year) $year = date("Y");
		$_SESSION["stats_year"] = $year;
		$_SESSION["stats_mode"] = "year";
		$_SESSION["show"] = "show_stats";
	break;






	//Export
	case "export_donations":
		if($access['donations']['MAX'] < 1) continue;

		$mode = format_userinput($_GET['export_mode'], 'alphanum');
		if(!$mode) continue;

		switch($mode) {
			case "person":
				$filename = ko_donations_export_person('person');
			break;  //person

			case 'family':
				$filename = ko_donations_export_person('family');
			break;  //family

			case 'couple':
				$filename = ko_donations_export_person('couple');
			break;  //couple

			case "all":
				$filename = ko_list_donations(TRUE, "xls", TRUE);
			break;  //all

			case "statsM":
				$filename = ko_donations_export_monthly();
			break;  //monthly

			default:  //statsYYEAR
				if(substr($mode, 0, 6) == 'statsY') {
					$year = intval(substr($mode, 6));
					if(!$year) $year = date('Y');
					$filename = ko_donations_stats(TRUE, 'xls', $year);
				}
				else if($mode == intval($mode) && $mode > 0) {
					//Check for ID from ko_pdf_layout
					$layout_id = intval($mode);
					$pdf_layout = db_select_data('ko_pdf_layout', "WHERE `id` = '$layout_id' AND `type` = 'donations'", '*', '', '', TRUE);
					if($pdf_layout['id'] > 0 && $pdf_layout['id'] == $layout_id && substr($pdf_layout['data'], 0, 4) == 'FCN:' && function_exists(substr($pdf_layout['data'], 4))) {
						$filename = call_user_func(substr($pdf_layout['data'], 4), $_GET);
					}
				}
			break;  //stats
			
		}
		if($filename) {
			$filename = substr($filename, 3);
			$onload_code = "ko_popup('".$ko_path."download.php?action=file&amp;file=$filename');";
			ko_log("export_donations", $filename);
		}
	break;



	case 'donation_settings':
		if($access['donations']['MAX'] < 1) continue;

		$_SESSION['show_back'] = $_SESSION['show'];
		$_SESSION['show'] = 'donation_settings';
	break;



	case 'submit_settings':
		if($access['donations']['MAX'] < 1) continue;

		ko_save_userpref($_SESSION['ses_userid'], 'default_view_donations', format_userinput($_POST['sel_donations'], 'js'));
		ko_save_userpref($_SESSION['ses_userid'], 'show_limit_donations', format_userinput($_POST['txt_limit_donations'], 'uint'));
		ko_save_userpref($_SESSION['ses_userid'], 'donations_stats_show_num', format_userinput($_POST['chk_stats_show_num'], 'uint'));
		ko_save_userpref($_SESSION['ses_userid'], 'donations_export_combine_accounts', format_userinput($_POST['chk_export_combine_accounts'], 'uint'));
		ko_save_userpref($_SESSION['ses_userid'], 'donations_recurring_prompt', format_userinput($_POST['chk_recurring_prompt'], 'uint'));
		ko_save_userpref($_SESSION['ses_userid'], 'donations_date_field', format_userinput($_POST['sel_date_field'], 'alpha'));

		if($access['donations']['MAX'] > 3) {
			$id = substr($_POST['sel_ps_filter'], 0, 3) == '@G@' ? '-1' : $_SESSION['ses_userid'];
			$key = str_replace('@G@', '', $_POST['sel_ps_filter']);
			//Only store, if new filterset has been selected (-1 is the value for a saved filter preset not available anymore)
			if($key != -1) {
				$filter = ko_get_userpref($id, $key, 'filterset');
				ko_set_setting('ps_filter_sel_ds1_koi[ko_donations][person]', $filter[0]['value']);
			}
			ko_set_setting('donations_use_promise', format_userinput($_POST['chk_use_promise'], 'uint'));
		}

		$_SESSION['show'] = $_SESSION['show_back'] ? $_SESSION['show_back'] : 'donation_settings';
	break;




	//Submenus
  case "move_sm_left":
  case "move_sm_right":
    ko_submenu_actions("donations", $do_action);
  break;


	//Default:
  default:
		if(!hook_action_handler($do_action))
      include($ko_path."inc/abuse.inc");
  break;


}//switch(do_action)


//HOOK: Plugins erlauben, die bestehenden Actions zu erweitern
hook_action_handler_add($do_action);



//Reread access rights if necessary
if(in_array($do_action, array('delete_account', 'submit_new_account'))) {
	ko_get_access('donations', '', TRUE);
}



//*** Default-Werte auslesen
if(!isset($_SESSION["show_accounts"]) || $_SESSION["show_accounts"] == "") {
  $show_accounts_string = ko_get_userpref($_SESSION["ses_userid"], "show_donations_accounts");
  if($show_accounts_string) {
    $_SESSION["show_accounts"] = explode(",", $show_accounts_string);
  } else {
		$accounts = db_select_data("ko_donations_accounts", "", "*");
    $_SESSION["show_accounts"] = array_keys($accounts);
  }
}
$_SESSION["show_limit"] = ko_get_userpref($_SESSION["ses_userid"], "show_limit_donations");
if(!$_SESSION["show_limit"]) $_SESSION["show_limit"] = ko_get_setting("show_limit_donations");

if(!$_SESSION["show_start"]) $_SESSION["show_start"] = 1;

if($_SESSION["sort_donations"] == "") {
	$_SESSION["sort_donations"]= "date";
	$_SESSION["sort_donations_order"] = "DESC";
}

if(!$_SESSION["stats_mode"]) $_SESSION["stats_mode"] = "year";
if(!$_SESSION["stats_year"]) $_SESSION["stats_year"] = date("Y");


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
print ko_include_css();
print ko_include_js(array($ko_path.'inc/jquery/jquery.js', $ko_path.'inc/kOOL.js', $ko_path.'inc/ckeditor/ckeditor.js', $ko_path.'inc/ckeditor/adapters/jquery.js'));
include($ko_path.'inc/js-sessiontimeout.inc');
include('inc/js-donations.inc');
$js_calendar->load_files();
?>
</head>

<body onload="session_time_init();<?php if(isset($onload_code)) print $onload_code; ?>">

<?php
/*
 * Gibt bei erfolgreichem Login das Menü aus, sonst einfach die Loginfelder
 */
include($ko_path . "menu.php");
?>


<table width="100%">
<tr>

<td class="main_left" name="main_left" id="main_left">
<?php
print ko_get_submenu_code("donations", "left");
?>
</td>


<td class="main">
<form action="index.php" method="post" name="formular" enctype="multipart/form-data">
<input type="hidden" name="action" id="action" value="" />
<input type="hidden" name="id" id="id" value="" />
<input type="hidden" name="recurring_amount" id="recurring_amount" value="" />
<input type="hidden" name="recurring_date" id="recurring_date" value="" />
<div name="main_content" id="main_content">

<?php
foreach ($infos as $info) {
	$notifier->addInfo($info, $do_action, array($info_txt_add));
}
if ($notifier->hasNotifications(koNotifier::ALL)) {
	$notifier->notify();
}

hook_show_case_pre($_SESSION["show"]);

switch($_SESSION["show"]) {
	case "list_donations":
		ko_list_donations();
	break;

	case "list_accounts":
		ko_list_accounts();
	break;

	case "list_reoccuring_donations":
		ko_list_reoccuring_donations();
	break;

	case 'new_donation':
		ko_formular_donation('new');
	break;

	case 'edit_donation':
		ko_formular_donation('edit', $id);
	break;

	case 'new_promise':
		ko_formular_donation('new', '', TRUE);
	break;

	case "new_account":
		ko_formular_account("new");
	break;

	case "edit_account":
		ko_formular_account("edit", $id);
	break;

	case "show_stats":
		ko_donations_stats();
	break;

	case "merge":
		ko_donations_merge();
	break;

	case "multiedit":
		ko_multiedit_formular("ko_donations", $do_columns, $do_ids, $order, array("cancel" => "list_donations"));
	break;

	case "multiedit_accounts":
		ko_multiedit_formular("ko_donations_accounts", $do_columns, $do_ids, $order, array("cancel" => "list_accounts"));
	break;

	case 'donation_settings':
		ko_donations_settings();
	break;


	default:
		//HOOK: Plugins erlauben, neue Show-Cases zu definieren
    hook_show_case($_SESSION["show"]);
  break;
}//switch(show)

//HOOK: Plugins erlauben, die bestehenden Show-Cases zu erweitern
hook_show_case_add($_SESSION["show"]);

?>
</div>
</form>
</td>

<td class="main_right" name="main_right" id="main_right">

<?php
print ko_get_submenu_code("donations", "right");
?>

</td>
</tr>

<?php include($ko_path . "footer.php"); ?>

</table>

</body>
</html>
