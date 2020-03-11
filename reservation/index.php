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

header('Content-Type: text/html; charset=ISO-8859-1');

ob_start();  //Ausgabe-Pufferung einschalten

$ko_path = "../";
$ko_menu_akt = "reservation";

include_once($ko_path . "inc/ko.inc");
include_once("inc/reservation.inc");

//get notifier instance
$notifier = koNotifier::Instance();

//Redirect to SSL if needed
ko_check_ssl();

//Check for arguments of moderation links. If so don't redirect yet, as the user will only be determined after processing the params
$confirm_link = isset($_GET['u']) && isset($_GET['h']) && strlen($_GET['u']) == 32 && strlen($_GET['h']) == 33;
if(!ko_module_installed("reservation") && !$confirm_link) {
	header("Location: ".$BASE_URL."index.php"); exit;
}

//Check for login from confirm/delete link
if($confirm_link) {
	$u = $_GET['u'];
	$h = substr($_GET['h'], 1);
	$mode = substr($_GET['h'], 0, 1);
	$login = db_select_data('ko_admin', "WHERE MD5(CONCAT(`id`, '".KOOL_ENCRYPTION_KEY."')) = '".mysqli_real_escape_string(db_get_link(), $u)."'", '*', 'LIMIT 0,1', '', TRUE);
	if($login['id'] > 0 && md5($login['id'].KOOL_ENCRYPTION_KEY) == $u) {
		//Check for valid hash
		$res = db_select_data('ko_reservation_mod', "WHERE MD5(CONCAT(`id`, '".KOOL_ENCRYPTION_KEY."')) = '".mysqli_real_escape_string(db_get_link(), $h)."'", '*', 'LIMIT 0,1', '', TRUE);
		if($res['id'] > 0 && md5($res['id'].KOOL_ENCRYPTION_KEY) == $h) {
			//Simulate action to approve or delete moderation request
			$_POST['id'] = intval($res['id']);
			if($mode == 'c') $_POST['action'] = 'res_mod_approve';
			else if($mode == 'd') $_POST['action'] = 'res_mod_delete';
			else exit;
			$_POST['mod_confirm'] = $_GET['c'] == 1 ? 'true' : 'false';

			//Login moderator (this way the code below works the same way as if the user would have been logged in already
			$_SESSION['ses_username'] = $login['login'];
			$_SESSION['ses_userid'] = $login['id'];
			ko_log('login', $_SESSION['ses_username'].' from '.ko_get_user_ip().' via res_mod');
			//TODO: unset(GLOBALS) and call ko_init()??

			//Save user's last login
			$_SESSION['last_login'] = ko_get_last_login($_SESSION['ses_userid']);
			db_update_data('ko_admin', "WHERE `id` = '".$_SESSION['ses_userid']."'", array('last_login' => date('Y-m-d H:i:s')));

			//Use language from userprefs
			$user_lang = ko_get_userpref($_SESSION['ses_userid'], 'lang');
			if($user_lang != '' && in_array($user_lang, $LANGS)) {
				$_SESSION['lang'] = $user_lang;
				include($ko_path.'inc/lang.inc');
			}
		}
	}
}//if(confirm_link)


ob_end_flush();  //Puffer flushen


ko_get_access('reservation');


// Include KOTA definitions
$kotaDefs = array('ko_reservation', 'ko_resitem', 'ko_reservation_mod');
if (ko_module_installed('daten')) $kotaDefs = array_merge($kotaDefs, array('ko_event', 'ko_eventgruppen'));
ko_include_kota($kotaDefs);



//*** Plugins einlesen:
$hooks = hook_include_main("reservation");
if(sizeof($hooks) > 0) foreach($hooks as $hook) include_once($hook);



//***Action auslesen:
if($_POST["action"]) {
	$do_action = $_POST["action"];
	$action_mode = "POST";
}
else if($_GET["action"]) {
	$do_action=$_GET["action"];
	$action_mode = "GET";
}
else {
	$do_action = $action_mode = "";
}

//Reset show_start if from another module
if($_SERVER['HTTP_REFERER'] != '' && FALSE === strpos($_SERVER['HTTP_REFERER'], '/'.$ko_menu_akt.'/')) $_SESSION['show_start'] = 1;

switch($do_action) {

	// NEU:
	case 'neue_reservation':
		//Get new date and time from GET param dayDate
		if(isset($_GET['dayDate'])) $start_stamp = $end_stamp = strtotime($_GET['dayDate']);
		if(isset($_GET['endDate'])) $end_stamp = strtotime($_GET['endDate']);
		if(!$start_stamp) $start_stamp = $end_stamp = time();
		if(isset($_GET['item']) && $_GET['item'] > 0) $item_id = format_userinput($_GET['item'], 'uint');

		$new_date_start = strftime('%d.%m.%Y', $start_stamp);
		$new_date_end   = strftime('%d.%m.%Y', $end_stamp);
		$new_time_start = strftime('%H:%M', $start_stamp);
		if($new_time_start == '00:00') {  //All day
			$new_time_end = '';
		} else {  //time given
			$new_time_end = $end_stamp != $start_stamp ? strftime('%H:%M', $end_stamp) : strftime('%H:00', (int)$end_stamp+3600);
		}
																					
		kota_assign_values('ko_reservation', array('startdatum' => $new_date_start));
		if($new_date_start != $new_date_end) kota_assign_values('ko_reservation', array('enddatum' => $new_date_end));

		$new_res_data = array('start_time' => $new_time_start, 'end_time' => $new_time_end);

		if($item_id) {
			kota_assign_values('ko_reservation', array('item_id' => $item_id));
			$new_res_data['item_id'] = $item_id;
		}

		//Manual moderation for admins
		if($access['reservation']['MAX'] > 3) {
			$manualModerationButton = '<br /><button type="submit" class="btn btn-success" name="submit_mm" value="1" onclick="var ok = check_mandatory_fields($(this).closest(\'form\')); if (ok) {disable_onunloadcheck(); set_action(\'submit_manual_moderation\', this)} else return false;">'.getLL('res_submit_manual_moderation').'</button>';
			$smarty->assign('additional_button', $manualModerationButton);
		}

		if($_SESSION['show'] != 'neue_reservation') $_SESSION['show_back'] = $_SESSION['show'];
		$_SESSION['show'] = 'neue_reservation';
		$onload_code = 'form_set_first_input();'.$onload_code;
	break;





	case "submit_neue_reservation":
	case 'submit_manual_moderation':
		if($access['reservation']['MAX'] < 2) break;

		$koi = $_POST["koi"]["ko_reservation"];
		kota_process_data("ko_reservation", $koi, "post", $log);
		if($koi["enddatum"] == "0000-00-00" || trim($koi["enddatum"]) == "") $koi["enddatum"] = $koi["startdatum"];

		$err = check_entries($koi);
		if($err > 0) {
			$notifier->addError($err, $do_action);
			break;
		}

		//Wiederholungen berechnen
		switch($_POST["rd_wiederholung"]) {
			case "taeglich":     $inc = $_POST["txt_repeat_tag"]; break;
			case "woechentlich": $inc = $_POST["txt_repeat_woche"]; break;
			case "monatlich1":   $inc = $_POST["sel_monat1_nr"]. "@".format_userinput($_POST["sel_monat1_tag"], "uint", FALSE, 1); break; 
			case "monatlich2":   $inc = $_POST["txt_repeat_monat2"]; break;
			case "holidays":     $inc = format_userinput($_POST["sel_repeat_holidays"], "alphanum+").'@'.format_userinput($_POST["sel_repeat_holidays_offset"], "alphanum+"); break;
			case "dates":     $inc = format_userinput($_POST["sel_repeat_dates"], "alphanumlist"); break;
		}
		ko_get_wiederholung($koi["startdatum"], $koi["enddatum"], $_POST["rd_wiederholung"], $inc,
												$_POST["sel_bis_tag"], $_POST["sel_bis_monat"], $_POST["sel_bis_jahr"],
												$repeat, ($_POST["txt_num_repeats"] ? $_POST["txt_num_repeats"] : ""),
												format_userinput($_POST['sel_repeat_eg'], 'uint'));
											
		if(sizeof($repeat) <= 0) $notifier->addError(7, $do_action);
		if($notifier->hasErrors()) break;

		$sendModerationEmails = TRUE;
		$res_confirm_mailtext = "";
		$res_data = $koi;  //Data for the single item
		$data = $mod_data = array();   //Will hold all res_datas for all items on all repeated days
		$items = db_select_data("ko_resitem", "WHERE 1=1", "*");

		//New serie-ID
		$serie_id = ko_get_new_serie_id("reservation");

		$modForConflicts = format_userinput($_POST['manual_moderation_for_conflicts'], 'uint');

		//Go through all selected objects that should be reserved
		foreach(explode(",", $koi["item_id"]) as $item_id) {
			//Check for valid item_id and access rights
			$item_id = format_userinput($item_id, "uint");
			if(!$item_id || $access['reservation'][$item_id] < 2) continue;
			$res_data["item_id"] = $item_id;

			//Check for moderation
			$moderation = $items[$item_id]["moderation"];

			//New serie-ID for every item
			//Increment it manually as ko_get_new_serie_id() would give the same id, as it calculates it from db, where the new res is not stored yet
			if(sizeof($repeat) > 2) $res_data["serie_id"] = $serie_id++;
			else unset($res_data["serie_id"]);

			//Loop through all repetitions
			for($i=0; $i<sizeof($repeat); $i+=2) {
				if(FALSE === ko_res_check_double($item_id,
										 $repeat[$i], $repeat[$i+1], $koi["startzeit"], $koi["endzeit"],
										 $double_error_txt)) {  //Check for double
					if (!$modForConflicts || $_SESSION['ses_userid'] == ko_get_guest_id()) {
						$notifier->addError(4, $do_action);
						$notifier->addTextError('<b>'.$repeat[$i].'</b>: '.getLL('res_collision').' <i>'.$double_error_txt.'</i><br />', $do_action);
					} else { // save as moderated res
						$notifier->addWarning(1, $do_action, array('<br><b>'.$repeat[$i].'</b>: '.getLL('res_collision').' <i>'.$double_error_txt.'</i><br />'));

						$res_data["startdatum"] = sql_datum($repeat[$i]);
						$res_data["enddatum"] = $repeat[$i+1] != "" ? sql_datum($repeat[$i+1]) : $res_data["startdatum"];

						$mod_data[] = $res_data;
						$sendModerationEmails = FALSE;
					}
				} else {
					$res_data["startdatum"] = sql_datum($repeat[$i]);
					$res_data["enddatum"] = $repeat[$i+1] != "" ? sql_datum($repeat[$i+1]) : $res_data["startdatum"];

					//Check for moderation
					if($do_action == 'submit_manual_moderation') {
						$mod_data[] = $res_data;
						$sendModerationEmails = FALSE;
					} else if($moderation == 0 || $access['reservation'][$item_id] > 3) {
						//No moderation needed
						$data[] = $res_data;
					} else {
						//Moderation needed
						$mod_data[] = $res_data;
					}
				}//if..else(ko_res_check_double())
			}//for(i=0..sizeof(repeat))

		}//foreach(items_to_be_reserved)

		//Store reservations
		if(sizeof($data) > 0) {
			if($_SESSION["ses_userid"] == ko_get_guest_id()) {
				//Always send email to guest user for non moderated reservatinos
				$user_email = TRUE;
			} else {
				//Check user setting for logged in users
				$user_email = ko_get_userpref($_SESSION['ses_userid'], 'do_res_email') != 0;
			}

			ko_res_store_reservation($data, $user_email);
			foreach($serie_ids = array_column($data,"serie_id", "serie_id") AS $id) {
				ko_res_update_serie_files($id);
			}
			$notifier->addInfo(1, $do_action);
		}
		//Store moderations
		if(sizeof($mod_data) > 0) {
			ko_res_store_moderation($mod_data, $sendModerationEmails);
			foreach($serie_ids = array_column($mod_data,"serie_id", "serie_id") AS $id) {
				ko_res_update_serie_files($id);
			}
			$notifier->addWarning(5, $do_action);
		}

		$_SESSION["show"] = $_SESSION["show_back"] ? $_SESSION["show_back"] : "calendar";
	break;



	case "new_item":
		$_SESSION["show_back"] = $_SESSION["show"];
		$_SESSION["show"]= "new_item";
		$onload_code = "form_set_first_input();".$onload_code;
	break;



	case "submit_new_item":
	case "submit_edit_item":
		if($access['reservation']['MAX'] < 4) break;

		if($do_action == "submit_edit_item") {
			list($table, $columns, $id, $hash) = explode("@", $_POST["id"]);
			if(FALSE === ($id = format_userinput($id, "uint", TRUE))) break;
		} else {
			$id = 0;
		}

		$txt_group = trim(format_userinput($_POST["koi"]["ko_resitem"]["gruppen_id"][$id], "text"));

		//Auf neue Gruppe prüfen
		$found = FALSE;
		if ($txt_group != "") {
			ko_get_resgroups($resg);
			foreach ($resg as $r) {
				if ($r["name"] == $txt_group) {  //Gruppe mit selbem Namen besteht bereits
					$found = TRUE;
					$new_group = $r["id"];  //Bestehende Gruppe verwenden
				}//if(r[name] == txt_group)
			}//foreach(resg)

			if ($found) {
				$allowed = TRUE;
			}
			//Falls angegebene Gruppe nicht schon vorhanden, neue erstellen
			else {
				//Sicherstellen, dass der User ALL-Mod-Rechte hat, denn sonst kann er nach der Erstellung
				//einer neuen Gruppe diese anschliessend nicht mehr bearbeiten.
				$allowed = (ko_get_access_all("res_admin") >= 4);

				if ($allowed) {
					$new_group = db_insert_data("ko_resgruppen", array("name" => $txt_group));

					//Log
					ko_log("new_resgroup", "$new_group: $txt_group");
				}
			}//if..else(found)
		}
		else {
			$allowed = FALSE;
			$notifier->addError(11, $do_action);
		}


		if($allowed && (!$found || $access['reservation']['grp'.$new_group] >= 4)) {  //Rechte überprüfen, ob dieser Gruppe Res-Objekte hinzugefügt werden dürfen
			$old_item = db_select_data('ko_resitem', "WHERE `id` = '$id'", '*', '', '', TRUE);

			$new_id = kota_submit_multiedit('', ($do_action == 'submit_new_item' ? 'new_resitem' : 'edit_resitem'));

			if($do_action == "submit_edit_item") {
				$notifier->addInfo(7, $do_action);
				ko_delete_empty_resgroups();

				$item = db_select_data('ko_resitem', "WHERE `id` = '$id'", '*', '', '', TRUE);

				//Update linked items in all stored reservations if this group's linked_items have changed
				if($old_item['linked_items'] != $item['linked_items']) {
					db_update_data('ko_reservation', "WHERE `item_id` = '$id'", array('linked_items' => $item['linked_items']));
				}
			} else {
				$_SESSION['show_items'][] = $new_id;
				$notifier->addInfo(8, $do_action);
			}

			$_SESSION["show"] = "list_items";

		} else {
			if(!$notifier->hasErrors()) $notifier->addError(6, $do_action);
		}
	break;



	// Bearbeiten
	case 'edit_res':  //Backwards compatibility
	case 'edit_reservation':
		if($access['reservation']['MAX'] < 2) break;

		if($action_mode == "POST") $do_id = format_userinput($_POST["id"], "uint");
		else if($action_mode == "GET") $do_id = format_userinput($_GET["id"], "uint");
		else break;

		if(!$do_id) break;

		ko_get_res_by_id($do_id, $r_);
    	$r = $r_[$do_id];
		$onload_code .= "changeResItem(".$r["item_id"].");";

		$_SESSION["show_back"] = $_SESSION["show"];
		$_SESSION["show"]= "edit_reservation";
		$onload_code = "form_set_first_input();".$onload_code;
	break;


	case 'submit_edit_res_mod':
	case 'submit_approve_edit_res_mod':
		$id = format_userinput($_POST["id"], "uint");

		//Get the old data for 'serie_id'
		$old_res = db_select_data('ko_reservation_mod', "WHERE `id` = '{$id}'", '*', '', '', TRUE, TRUE);

		if ($do_action == 'submit_edit_res_mod') {
			if ($access['reservation']['MAX'] < 5 && ($_SESSION["ses_userid"] == ko_get_guest_id() || $old_res["user_id"] != $_SESSION["ses_userid"])) break;
		} else {
			if ($access['reservation']['MAX'] < 5) break;
		}


		$data = $_POST["koi"]["ko_reservation_mod"];
		kota_process_data("ko_reservation_mod", $data, "post", $log, $id);
		$err = check_entries($data);
		if($err > 0) {
			$notifier->addError($err, $do_action);
			break;
		}
		if ($data['enddatum']) $data['enddatum'] = sql_datum($data['enddatum']);
		if ($data['startdatum']) $data['startdatum'] = sql_datum($data['startdatum']);

		if($data["enddatum"] == "0000-00-00" || trim($data["enddatum"]) == "") $data["enddatum"] = $data["startdatum"];
		$data["last_change"] = strftime("%Y-%m-%d %H:%M:%S", time());
		$data['lastchange_user'] = $_SESSION['ses_userid'];

		if ($do_action == 'submit_edit_res_mod') {
			$allowOld = $access['reservation'][$old_res['item_id']] > 1;
			$allowNew = $access['reservation'][$data['item_id']] > 1;
			if ($access['reservation']['MAX'] < 5 && ($_SESSION["ses_userid"] == ko_get_guest_id() || $old_res["user_id"] != $_SESSION["ses_userid"] || !$allowOld || !$allowNew)) break;
		} else {
			if ($access['reservation']['MAX'] < 5 && ($access['reservation'][$old_res['item_id']] < 5 || $access['reservation'][$data['item_id']] < 5)) break;
		}

		//Check and give an error if a reservation of a series or a simple reservation is double
		//Save id of double records, so we don't save them
		if ($do_action == 'submit_approve_edit_res_mod') {
			if($_POST['chk_serie'] && $old_res['serie_id']) {
				$error_count = 0;
				$error_in_id = array();
				$serie_res = db_select_data('ko_reservation_mod', "WHERE `serie_id` = '".$old_res['serie_id']."'");
				foreach($serie_res as $s) {
					if(FALSE === ko_res_check_double($data['item_id'], $s['startdatum'], $s['enddatum'], $data['startzeit'], $data['endzeit'], $double_error_txt, $s['id'])) {
						$notifier->addError(4, $do_action);
						$notifier->addTextError('<b>'.$data['startdatum']. '</b>: '.getLL('res_collision').' <i>' . $double_error_txt . '</i><br />', $do_action);
						$error_count++;
						$error_in_id[] = $s['id'];
					}
				}

				ko_res_update_serie_files($old_res['serie_id'], $id);

				//Stop here if all records are double
				if(sizeof($serie_res) == $error_count) break;
			} else {
				if(FALSE === ko_res_check_double($data['item_id'], $data['startdatum'], $data['enddatum'], $data['startzeit'], $data['endzeit'], $double_error_txt, $id)) {
					$notifier->addError(4, $do_action);
					$notifier->addTextError('<b>'.$data['startdatum']. '</b>: '.getLL('res_collision').' <i>' . $double_error_txt . '</i><br />', $do_action);
					break;
				}
			}
		}

		ko_get_resitem_by_id($old_res[$id]['item_id'], $old_resitem);
		//Check for moderation using the current data as the resitem might have changed
		ko_get_resitem_by_id($data['item_id'], $resitem);
		$moderation = $resitem[$data['item_id']]['moderation'];

		//set linked_items new from new item
		$data['linked_items'] = $resitem[$data['item_id']]['linked_items'];
		$data['startdatum'] = sql_datum($data['startdatum']);
		$data['enddatum'] = sql_datum($data['enddatum']);

		//Log-Meldung
		ko_log_diff('edit_res_mod', $data, $old_res);

		$ids = array();
		//change whole serie, if selected, otherwise only the single record
		if($_POST['chk_serie'] && $old_res['serie_id']) {
			unset($data['startdatum']); unset($data['enddatum']);

			$es = db_select_data('ko_reservation_mod', "WHERE `serie_id` = '".$old_res['serie_id']."'");
			foreach ($es as $e) {
				$ids[] = $e['id'];
			}

			//change only if the id is not in $error_in_id
			db_update_data('ko_reservation_mod', "WHERE `serie_id` = '".$old_res['serie_id']."' AND `id` NOT IN('".implode("','", $error_in_id)."')", $data);
			ko_res_update_serie_files($old_res['serie_id'], $id);
		} else {
			$ids[] = $id;
			db_update_data('ko_reservation_mod', "WHERE `id` = '$id'", $data);
		}

		//If no errors occurred show good message
		$notifier->addInfo(4, $do_action);

		$_SESSION['show'] = $_SESSION['show_back'] ? $_SESSION['show_back'] : 'show_mod_res';

		if ($do_action == 'submit_approve_edit_res_mod') ko_res_mod_approve($ids, TRUE);
	break;


	case "submit_edit_reservation":
		if($access['reservation']['MAX'] < 2) break;

		$id = format_userinput($_POST["id"], "uint");
		$data = $_POST["koi"]["ko_reservation"];
		kota_process_data("ko_reservation", $data, "post", $log, $id);
		$err = check_entries($data);
		if($err > 0) {
			$notifier->addError($err, $do_action);
			break;
		}

		if($data["enddatum"] == "0000-00-00" || trim($data["enddatum"]) == "") $data["enddatum"] = $data["startdatum"];
		$data["last_change"] = strftime("%Y-%m-%d %H:%M:%S", time());
		$data['lastchange_user'] = $_SESSION['ses_userid'];

		//Get the old data for 'serie_id'
		ko_get_res_by_id($id, $old_res);

		//Check and give an error if a reservation of a series or a simple reservation is double
		//Save id of double records, so we don't save them
		if($_POST['chk_serie'] && $old_res[$id]['serie_id']) {
			$error_count = 0;
			$error_in_id = array();
			$serie_res = db_select_data('ko_reservation', "WHERE `serie_id` = '".$old_res[$id]['serie_id']."'");
			foreach($serie_res as $s) {
				if(FALSE === ko_res_check_double($data['item_id'], $s['startdatum'], $s['enddatum'], $data['startzeit'], $data['endzeit'], $double_error_txt, $s['id'])) {
					$notifier->addError(4, $do_action);
					$notifier->addTextError('<b>'.$data['startdatum']. '</b>: '.getLL('res_collision').' <i>' . $double_error_txt . '</i><br />', $do_action);
					$error_count++;
					$error_in_id[] = $s['id'];
				}
			}
			//Stop here if all records are double
			if(sizeof($serie_res) == $error_count) break;
		} else {
			if(FALSE === ko_res_check_double($data['item_id'], $data['startdatum'], $data['enddatum'], $data['startzeit'], $data['endzeit'], $double_error_txt, $id)) {
				$notifier->addError(4, $do_action);
				$notifier->addTextError('<b>'.$data['startdatum']. '</b>: '.getLL('res_collision').' <i>' . $double_error_txt . '</i><br />', $do_action);
				break;
			}
		}

		ko_get_resitem_by_id($old_res[$id]['item_id'], $old_resitem);
		//Check for moderation using the current data as the resitem might have changed
		ko_get_resitem_by_id($data['item_id'], $resitem);
		$moderation = $resitem[$data['item_id']]['moderation'];

		//set linked_items new from new item
		$data['linked_items'] = $resitem[$data['item_id']]['linked_items'];
		$data['startdatum'] = sql_datum($data['startdatum']);
		$data['enddatum'] = sql_datum($data['enddatum']);

		//No moderation needed
		if(($moderation == 0 && $access['reservation'][$data['item_id']] > 2)
			|| $access['reservation'][$data['item_id']] > 3
			|| ($_SESSION['ses_userid'] != ko_get_guest_id() && $access['reservation'][$data['item_id']] > 2 && $data['user_id'] == $_SESSION['ses_userid']) ) {
			//Log-Meldung
			ko_log_diff('edit_res', $data, $old_res[$id]);

			//change whole serie, if selected, otherwise only the single record
			if($_POST['chk_serie'] && $old_res[$id]['serie_id']) {
				unset($data['startdatum']); unset($data['enddatum']);
				//change only if the id is not in $error_in_id
				db_update_data('ko_reservation', "WHERE `serie_id` = '".$old_res[$id]['serie_id']."' AND `id` NOT IN('".implode("','", $error_in_id)."')", $data);
				ko_res_update_serie_files($old_res[$id]['serie_id'], $id);
			} else {
				db_update_data('ko_reservation', "WHERE `id` = '$id'", $data);
			}

			//If no errors occurred show good message
			$notifier->addInfo(4, $do_action);

		} else {  //Moderation needed
			$mod_data = array();

			//Store original reservation's id in moderation request
			//$data['_orig_res_id'] = $id;

			//Handle all serie members
			if($_POST['chk_serie'] && $old_res[$id]['serie_id']) {
				$data['serie_id'] = $old_res[$id]['serie_id'];
				foreach($serie_res as $s) {
					//Handle record only if the id is not in $error_in_id
					if(FALSE === strstr($error_in_id, $s['id'])) {
						db_delete_data('ko_reservation', "WHERE `id` = '".$s['id']."'");
						$data['startdatum'] = $s['startdatum'];
						$data['enddatum'] = $s['enddatum'];
						$mod_data[] = $data;
					}
				}
			} else {
				//Delete current reservation
				db_delete_data('ko_reservation', "WHERE `id`='$id'");
				$mod_data[] = $data;
			}

			//Store new moderation
			ko_res_store_moderation($mod_data);

			//Log-Meldung
			$log_message  = $id.'MOD: '.$old_resitem[$old_res[$id]['item_id']]['name'].'-->'.$resitem[$data['item_id']]['name'].', ';
			$log_message .= sql2datum($data['startdatum']).($data['startdatum']!=$data['enddatum'] ?  '-'.sql2datum($data['enddatum']) : '').', ';
			$log_message .= $data['startzeit'].'-'.$data['endzeit'].', ';
			$log_message .= '"'.$data['zweck'].'", '.$data['name'].'('.$data['email'].', '.$data['telefon'].'), ';
			ko_log('edit_res', $log_message);

			//If no errors occurred show good message
			$notifier->addWarning(5, $do_action);
		}//if..else(moderation)

		$_SESSION['show'] = $_SESSION['show_back'] ? $_SESSION['show_back'] : 'calendar';
	break;


	case "check_edit_code":
		$id = format_userinput($_POST["id"], "uint");
		$res_code = format_userinput($_POST["res_code"], "alphanum");
		if(trim($res_code) == "" || trim($id) == "") break;
		ko_get_res_by_id($id, $res);
		if($res[$id]["code"] == $res_code) {
			$_SESSION["show"]= "edit_reservation";
			kota_assign_values("ko_reservation", array("startdatum" => $res[$id]["startdatum"]));
			kota_assign_values("ko_reservation", array("enddatum" => $res[$id]["enddatum"]));
			$action = "edit_reservation";
		} else {
			$notifier->addError(3, $do_action);
		}
	break;


	case "edit_item":
		$_SESSION["show_back"] = $_SESSION["show"];
		$_SESSION["show"]= "edit_item";
		$onload_code = "form_set_first_input();".$onload_code;
	break;




	case "multiedit":
		if($_SESSION["show"] == "liste") {
			if($access['reservation']['MAX'] < 2) break;

			//Get columns to be edited
			$columns = explode(",", format_userinput($_POST["id"], "alphanumlist"));
			foreach($columns as $column) {
				$do_columns[] = $column;
			}
			if(sizeof($do_columns) < 1) $notifier->addError(58, $do_action);

			//Get ticked rows
			$do_ids = array();
			foreach($_POST["chk"] as $c_i => $c) {
				if($c) {
					if(FALSE === ($edit_id = format_userinput($c_i, "uint", TRUE))) {
						trigger_error("Not allowed multiedit_id: ".$c_i, E_USER_ERROR);
					}
					ko_get_res_by_id($edit_id, $res);
					$item_id = $res[$edit_id]['item_id'];
					if($access['reservation'][$item_id] > 3 || ($_SESSION['ses_userid'] != ko_get_guest_id() && $res[$edit_id]['user_id'] == $_SESSION['ses_userid'] && $access['reservation'][$item_id] > 2)) {
						$do_ids[] = $edit_id;
					}
				}
			}
			if(sizeof($do_ids) < 1) $notifier->addError(10, $do_action);

			//Daten für Formular-Aufruf vorbereiten
			if(!$notifier->hasErrors()) {
				$order = "ORDER BY ".$_SESSION["sort_item"]." ".$_SESSION["sort_item_order"];
				$_SESSION["show_back"] = $_SESSION["show"];
				$_SESSION["show"] = "multiedit";
			}


		// Res objects
		} else if($_SESSION["show"] == "list_items") {
			if($access['reservation']['MAX'] < 4) break;

			//Zu bearbeitende Spalten
			$columns = explode(",", format_userinput($_POST["id"], "alphanumlist"));
			foreach($columns as $column) {
				$do_columns[] = $column;
			}
			if(sizeof($do_columns) < 1) $notifier->addError(8, $do_action);

			//Zu bearbeitende Einträge
			$do_ids = array();
			foreach($_POST["chk"] as $c_i => $c) {
				if($c) {
					if(FALSE === ($edit_id = format_userinput($c_i, "uint", TRUE))) {
						trigger_error("Not allowed multiedit_id: ".$c_i, E_USER_ERROR);
					}
					ko_get_resitem_by_id($edit_id, $group);
					if($access['reservation']['grp'.$group[$edit_id]['gruppen_id']] >= 4) $do_ids[] = $edit_id;
				}
			}
			if(sizeof($do_ids) < 1) $notifier->addError(8, $do_action);

			//Daten für Formular-Aufruf vorbereiten
			if(!$notifier->hasErrors()) {
				$order = "ORDER BY ".$_SESSION["sort_group"]." ".$_SESSION["sort_group_order"];
				$_SESSION["show_back"] = $_SESSION["show"];
				$_SESSION["show"] = "multiedit_group";
			}
		}

		/* Moderated entries */
		else if($_SESSION["show"] == "show_mod_res") {
			if($access['reservation']['MAX'] < 5) break;

			//Zu bearbeitende Spalten
			$columns = explode(",", format_userinput($_POST["id"], "alphanumlist"));
			foreach($columns as $column) {
				$do_columns[] = $column;
			}
			if(sizeof($do_columns) < 1) $notifier->addError(58, $do_action);

			//Zu bearbeitende Einträge
			$do_ids = array();
			foreach($_POST["chk"] as $c_i => $c) {
				if($c) {
					if(FALSE === ($edit_id = format_userinput($c_i, "uint", TRUE))) {
						trigger_error("Not allowed multiedit_id: ".$c_i, E_USER_ERROR);
					}
					ko_get_res_mod_by_id($res, $edit_id);
					if($access['reservation'][$res[$edit_id]['item_id']] > 4) $do_ids[] = $edit_id;
				}
			}
			if(sizeof($do_ids) < 1) $notifier->addError(10, $do_action);

			//Daten für Formular-Aufruf vorbereiten
			if(!$notifier->hasErrors()) {
				$order = "ORDER BY ".$_SESSION["sort_item"]." ".$_SESSION["sort_item_order"];
				$_SESSION["show_back"] = $_SESSION["show"];
				$_SESSION["show"] = "multiedit_mod";
			}
		}


		$onload_code = "form_set_first_input();".$onload_code;
	break;



	case "submit_multiedit":
		if($_SESSION["show"] == "multiedit") {
			if($access['reservation']['MAX'] < 3) break;
			kota_submit_multiedit(3);
			if(!$notifier->hasErrors()) $notifier->addInfo(12, $do_action);

		} else if($_SESSION["show"] == "multiedit_group") {
			if($access['reservation']['MAX'] < 4) break;
			kota_submit_multiedit(4);
			if(!$notifier->hasErrors()) $notifier->addInfo(12, $do_action);

		} else if($_SESSION["show"] == "multiedit_mod") {
			if($access['reservation']['MAX'] < 5) break;
			kota_submit_multiedit(5);
			if(!$notifier->hasErrors()) $notifier->addInfo(12, $do_action);
		}

		$_SESSION['show'] = $_SESSION['show_back'] ? $_SESSION['show_back'] : 'liste';
	break;





	// Delete single reservation
	case "delete_res":
		if(FALSE === ($id = format_userinput($_POST["id"], "uint", TRUE))) {
			trigger_error("Not allowed del_id: ".$c_i, E_USER_ERROR);
		}
		$del_serie = ($_POST["mod_confirm"] == "true");
		$delEvent = ($_POST["del_event"] == "true");

		do_del_res($id, $del_serie, $delEvent);

		$notifier->addInfo(2, $do_action);
		if ($_SESSION["show"] == "edit_reservation") {
			$_SESSION["show"] = "liste";
		}
	break;


	// Delete selected reservations
	case "del_selected":
	foreach($_POST["chk"] as $c_i => $c) {
		if($c) {
		if(FALSE === ($del_id = format_userinput($c_i, "uint", TRUE))) {
		  trigger_error("Not allowed del_id (multiple): ".$c_i, E_USER_ERROR);
		}
		do_del_res($del_id);
	  }
	}
	$notifier->addInfo(11, $do_action);
	break;



	case "check_del_code":
		$id = format_userinput($_POST["id"], "uint");
		$res_code = format_userinput($_POST["res_code"], "alphanum");

		ko_get_res_by_id($id, $res);
		if($res[$id]["code"] == $res_code) {
			do_del_res($id);
			$notifier->addInfo(3, $do_action);
		} else {
			$notifier->addError(3, $do_action);
		}
	break;


	case "delete_item":
		if($access['reservation']['MAX'] < 4) break;

		$id = format_userinput($_POST["id"], "uint");

		if($id) {
			//Bisheriges Objekt holen, für Logmeldung
			ko_get_resitem_by_id($id, $old_item);
			//Objekt selber löschen
			db_delete_data("ko_resitem", "WHERE `id`='$id'");
			//Alle Reservationen für dieses Objekt löschen
			db_delete_data("ko_reservation", "WHERE `item_id`='$id'");

			//Delete this res item from all event groups (don't consider access rights of the current user,
			//as it just has to be removed as the item doesn't exist anymore)
			if(in_array('daten', $MODULES)) {
				ko_get_eventgruppen($egrps, '', " AND `resitems` REGEXP '(^|,)$id(,|$)' ");
				foreach($egrps as $g_i => $g) {
					$resitems = explode(',', $g['resitems']);
					foreach($resitems as $i => $itemid) {
						if($itemid == $id) unset($resitems[$i]);
					}
					db_update_data('ko_eventgruppen', "WHERE `id` = '$g_i'", array('resitems' => implode(',', $resitems)));
				}
			}

			//Delete entry in items where this item used to be a linked item
			$items = db_select_data("ko_resitem", "WHERE `linked_items` REGEXP '(,|^)$id(,|$)'");
			foreach($items as $item) {
				$new_li = array();
				foreach(explode(",", $item["linked_items"]) as $i) {
					if(!$i || $i == $id) continue;
					$new_li[] = $i;
				}
				db_update_data("ko_resitem", "WHERE `id` = '".$item["id"]."'", array("linked_items" => implode(",", $new_li)));
			}

			//Delete res group if this was the last item from this group
			ko_delete_empty_resgroups();
			
			ko_log_diff("delete_resitem", $old_item[$id]);
		}//if(id)
	break;

	case "new_event_selected":
		if($access['daten']['MAX'] < 2) break;
		$eventgroup_id = format_userinput($_POST['sel_eventgroup'], 'uint');
		include_once("../daten/inc/daten.inc");

		ko_get_eventgruppe_by_id($eventgroup_id,$eventgroup );

		if (empty($_POST['chk']) || empty($eventgroup)) {
			$notifier->addError(62, $do_action);
		}

		if ($access['daten']['ALL'] < 3) {
			if (
				($eventgroup['moderation'] != 0 && $access['daten'][$eventgroup_id] < 3) ||
				($access['daten'][$eventgroup_id] < 2)
			) {
				$notifier->addError(61, $do_action);
			}
		}

		if (!$notifier->hasErrors()) {
			foreach ($_POST["chk"] as $c_i => $c) {
				if ($c) {
					if (FALSE === ($res_id = format_userinput($c_i, "uint", TRUE))) {
						trigger_error("Not allowed res_id (multiple): " . $c_i, E_USER_ERROR);
					}

					$possibleEvents = db_select_data("ko_event","WHERE find_in_set(".$res_id.",reservationen) > 0");
          if(sizeof($possibleEvents) > 0) {
						// skip if res is already assigned to an event
						continue;
					}

					ko_get_res_by_id($res_id, $res);
					$event = [
						'eventgruppen_id' => $eventgroup_id,
						'title' => $res[$res_id]['zweck'],
						'startdatum' => $res[$res_id]['startdatum'],
						'enddatum' => $res[$res_id]['enddatum'],
						'startzeit' => $res[$res_id]['startzeit'],
						'endzeit' => $res[$res_id]['endzeit'],
					];

					$key = $res[$res_id]['startdatum'] . $res[$res_id]['enddatum'] . $res[$res_id]['startzeit'] . $res[$res_id]['endzeit'] . $res[$res_id]['zweck'];
					$new_events[$key]['event_data'] = $event;
					$new_events[$key]['res_ids'][] = $res_id;
				}
			}

			foreach ($new_events AS $event) {
				$store_data = array($event['event_data']);
				$errorOut = ko_daten_store_event($store_data);
				if (!$errorOut && is_numeric($store_data[0]['id'])) {
					$notifier->addInfo(1, $do_action);
					$where = "WHERE id = " . $store_data[0]['id'];
					$data = ['reservationen' => implode(',', $event['res_ids'])];
					db_update_data("ko_event", $where, $data);
				}
			}

			if (count($new_events) >= 1 && !$notifier->hasErrors()) {
				// jump to last insert event
				header('Location: ' . '/daten/index.php?action=edit_termin&id=' . $store_data[0]['id'], TRUE, 302);
			} else {
				$notifier->addTextWarning(getLL('res_to_event_create_problem'), $do_action);
			}
		}

		break;


	//Settings
	case "res_settings":
		if($access['reservation']['MAX'] < 1) break;
		$_SESSION["show_back"] = $_SESSION["show"];
		$_SESSION["show"] = "res_settings";
	break;

	case "submit_res_settings":
		if($access['reservation']['MAX'] < 1 || $_SESSION['ses_userid'] == ko_get_guest_id()) break;

		ko_save_userpref($_SESSION['ses_userid'], 'default_view_reservation', format_userinput($_POST['sel_reservation'], 'js'));
		ko_save_userpref($_SESSION['ses_userid'], 'show_limit_reservation', format_userinput($_POST['txt_limit_reservation'], 'uint'));
		ko_save_userpref($_SESSION['ses_userid'], 'cal_woche_start', min(23, format_userinput($_POST['txt_cal_woche_start'], 'uint')));
		ko_save_userpref($_SESSION['ses_userid'], 'cal_woche_end', min(24, format_userinput($_POST['txt_cal_woche_end'], 'uint')));
		ko_save_userpref($_SESSION['ses_userid'], 'show_dateres_combined', format_userinput($_POST['sel_show_dateres_combined'], 'uint', FALSE, 1));
		ko_save_userpref($_SESSION['ses_userid'], 'res_pdf_show_time', format_userinput($_POST['sel_pdf_show_time'], 'uint', FALSE, 1));
		ko_save_userpref($_SESSION['ses_userid'], 'res_pdf_show_comment', format_userinput($_POST['sel_pdf_show_comment'], 'uint', FALSE, 1));
		ko_save_userpref($_SESSION['ses_userid'], 'res_pdf_week_start', format_userinput($_POST['sel_pdf_week_start'], 'uint', FALSE, 1));
		ko_save_userpref($_SESSION['ses_userid'], 'res_pdf_week_length', format_userinput($_POST['sel_pdf_week_length'], 'uint', FALSE, 2));
		ko_save_userpref($_SESSION['ses_userid'], 'res_export_show_legend', format_userinput($_POST['sel_export_show_legend'], 'uint', FALSE, 1));
		ko_save_userpref($_SESSION['ses_userid'], 'res_mark_sunday', format_userinput($_POST['sel_mark_sunday'], 'uint', FALSE, 1));
		ko_save_userpref($_SESSION['ses_userid'], 'res_monthly_title', format_userinput($_POST['sel_monthly_title'], 'js'));
		ko_save_userpref($_SESSION['ses_userid'], 'res_name_in_pdffooter', (format_userinput($_POST['sel_name_in_pdffooter'], 'uint') == 1 ? 1 : 0));
		ko_save_userpref($_SESSION['ses_userid'], 'res_contact_in_export', format_userinput($_POST['sel_contact_in_export'], 'uint'));

		// check format of intermediate times
		$it = $_POST['txt_cal_woche_intermediate_times'];
		$itA = explode(';', $it);
		$pattern = '/^([0-1][0-9]|2[0-4]):[0-5][0-9]$/';
		$match = 0;
		foreach ($itA as $a) {
			$match += preg_match($pattern, $a);
		}
		if ($match == sizeof($itA) || (sizeof($itA) == 1 && $itA[0] == '') || sizeof($itA) == 0) {
			ko_save_userpref($_SESSION['ses_userid'], 'cal_woche_intermediate_times', $_POST['txt_cal_woche_intermediate_times']);
		}
		else {
			$notifier->addError(15);
		}


		ko_save_userpref($_SESSION['ses_userid'], 'res_ical_deadline', format_userinput($_POST['sel_ical_deadline'], 'int'));
		ko_save_userpref($_SESSION['ses_userid'], 'res_fm_filter', format_userinput($_POST['sel_fm_filter'], 'text'));
		if($access['reservation']['MAX'] > 1) {
			ko_save_userpref($_SESSION['ses_userid'], "do_res_email", format_userinput($_POST["sel_do_res_email"], "uint", FALSE, 1));
		}
		if($access['reservation']['MAX'] > 4) {
			ko_save_userpref($_SESSION['ses_userid'], "do_mod_email_for_edit_res", format_userinput($_POST["sel_do_mod_email_for_edit_res"], "uint", FALSE, 1));
		}
		if(in_array('leute', $MODULES)) {
			ko_save_userpref($_SESSION['ses_userid'], 'res_prefill_name', format_userinput($_POST['txt_prefill_name'], 'text'));
			ko_save_userpref($_SESSION['ses_userid'], 'res_prefill_tel', format_userinput($_POST['sel_prefill_tel'], 'js'));
			if (isset($_POST['sel_prefill_email'])) {
				ko_save_userpref($_SESSION['ses_userid'], 'res_prefill_email', format_userinput($_POST['sel_prefill_email'], 'js'));
			}
		}
		if($access['reservation']['MAX'] > 4) {
			ko_save_userpref($_SESSION['ses_userid'], 'res_mod_email', format_userinput($_POST['sel_mod_email'], 'alphanum'));
		}

		if($access['reservation']['ALL'] > 3) {
			ko_set_setting('res_show_fields_to_guest', format_userinput($_POST['sel_res_show_fields_to_guest'], 'alphanumlist'));
			ko_set_setting('res_allow_multires_for_guest', format_userinput($_POST['sel_allow_multires'], 'uint', FALSE, 1));
			kota_save_mandatory_fields('ko_reservation', $_POST);
			kota_save_mandatory_fields('ko_reservation', $_POST, TRUE);
			ko_set_setting('res_send_email', format_userinput($_POST['txt_send_email'], 'email', FALSE, 0, array(), ' ,'));
			ko_set_setting('res_show_mod_to_all', format_userinput($_POST['sel_show_mod_to_all'], 'uint', FALSE, 1));
			ko_set_setting('res_attach_ics_for_user', format_userinput($_POST['sel_attach_ics_for_user'], 'uint', FALSE, 1));
			ko_set_setting('res_access_mode', format_userinput($_POST['sel_access_mode'], 'uint', FALSE, 1));
			ko_set_setting('res_show_ical_links_to_guest', format_userinput($_POST['sel_show_ical_links_to_guest'], 'uint'));
			ko_set_setting('res_access_prevent_lvl2_del', format_userinput($_POST['chk_prevent_lvl2_del'], 'uint'));
			ko_set_setting('res_allow_exports_for_guest', format_userinput($_POST['sel_allow_exports_for_guest'], 'alphanumlist'));
		}


		if(!isset($access['admin'])) ko_get_access('admin');
		if($access['admin']['ALL'] > 2) {
			$uid = ko_get_guest_id();
			ko_save_userpref($uid, 'default_view_reservation', format_userinput($_POST['guest_sel_reservation'], 'js'));
			ko_save_userpref($uid, 'show_limit_reservation', format_userinput($_POST['guest_txt_limit_reservation'], 'uint'));
			ko_save_userpref($uid, 'cal_woche_start', min(23, format_userinput($_POST['guest_txt_cal_woche_start'], 'uint')));
			ko_save_userpref($uid, 'cal_woche_end', min(24, format_userinput($_POST['guest_txt_cal_woche_end'], 'uint')));
			ko_save_userpref($uid, 'res_monthly_title', format_userinput($_POST['guest_sel_monthly_title'], 'js'));
			ko_save_userpref($uid, 'res_mark_sunday', format_userinput($_POST['guest_sel_mark_sunday'], 'uint', FALSE, 1));
			ko_save_userpref($uid, 'show_dateres_combined', format_userinput($_POST['guest_sel_show_dateres_combined'], 'uint', FALSE, 1));
			ko_save_userpref($uid, 'res_pdf_show_time', format_userinput($_POST['guest_sel_pdf_show_time'], 'uint', FALSE, 1));
			ko_save_userpref($uid, 'res_pdf_show_comment', format_userinput($_POST['guest_sel_pdf_show_comment'], 'uint', FALSE, 1));
			ko_save_userpref($uid, 'res_pdf_week_start', format_userinput($_POST['guest_sel_pdf_week_start'], 'uint', FALSE, 1));
			ko_save_userpref($uid, 'res_pdf_week_length', format_userinput($_POST['guest_sel_pdf_week_length'], 'uint', FALSE, 2));
			ko_save_userpref($uid, 'res_export_show_legend', format_userinput($_POST['guest_sel_export_show_legend'], 'uint', FALSE, 1));

			// check format of intermediate times
			$it = $_POST['txt_cal_woche_intermediate_times'];
			$itA = explode(';', $it);
			$pattern = '/^([0-1][0-9]|2[0-4]):[0-5][0-9]$/';
			$match = 0;
			foreach ($itA as $a) {
				$match += preg_match($pattern, $a);
			}
			if ($match == sizeof($itA) || (sizeof($itA) == 1 && $itA[0] == '') || sizeof($itA) == 0) {
				ko_save_userpref($uid, 'cal_woche_intermediate_times', $_POST['txt_cal_woche_intermediate_times']);
			}
			else {
				$notifier->addError(15);
			}
		}



		if (!$notifier->hasErrors()) {
			$_SESSION["show"] = $_SESSION["show_back"] ? $_SESSION["show_back"] : "res_settings";
		}
	break;



	// Anzeige
	case 'show_liste':  //Backwards compatibility
	case 'liste':
		if($_SESSION['show'] == 'liste') $_SESSION['show_start'] = 1;
		$_SESSION['show']= 'liste';
	break;

	case 'show_calendar':  //Backwards compatibility
	case 'calendar':
		if($access['reservation']['MAX'] < 1) break;

		$_SESSION["show"] = "calendar";
		$wt = kota_filter_get_warntext('ko_reservation');
		if (trim($wt) != '') $notifier->addTextWarning($wt);
	break;

	case 'show_cal_monat':
		if($access['reservation']['MAX'] < 1) break;

		if($_GET['set_month']) {
			if(FALSE === ($new_month = format_userinput($_GET['set_month'], 'int', TRUE, 7))) {
				trigger_error('Not allowed set_month: '.$_GET['set_month'], E_USER_ERROR);
			}
			$_SESSION['cal_tag'] = 1;
			$_SESSION['cal_monat'] = (int)substr($new_month, 0, 2);
			$_SESSION['cal_jahr'] = (int)substr($new_month, -4);
		}

		$_SESSION['cal_view']= 'month';
		$_SESSION['show']= 'calendar';
		$wt = kota_filter_get_warntext('ko_reservation');
		if (trim($wt) != '') $notifier->addTextWarning($wt);
	break;

	case 'show_cal_woche':
		if($access['reservation']['MAX'] < 1) break;

    $_SESSION['cal_view'] = 'agendaWeek';
    $_SESSION['show'] = 'calendar';
		$wt = kota_filter_get_warntext('ko_reservation');
		if (trim($wt) != '') $notifier->addTextWarning($wt);
  break;

	case 'show_resource_day':
		if($access['reservation']['MAX'] < 1) break;

    $_SESSION['show'] = 'calendar';
    $_SESSION['cal_view'] = 'timelineDay';
	break;

	case 'show_resource_week':
		if($access['reservation']['MAX'] < 1) break;

    $_SESSION['show'] = 'calendar';
    $_SESSION['cal_view'] = 'timelineWeek';
	break;

	case 'show_resource_month':
		if($access['reservation']['MAX'] < 1) break;

    $_SESSION['show'] = 'calendar';
    $_SESSION['cal_view'] = 'timelineMonth';
	break;


	case "list_items":
		$_SESSION["show"]= "list_items";
		$_SESSION["show_start"]= 1;
	break;

	case 'ical_links':
		if($access['reservation']['MAX'] < 1) break;
		$_SESSION['show'] = 'ical_links';
	break;
	case 'ical_links_revoke':
		require_once($BASE_PATH.'admin/inc/admin.inc');
		$login['ical_hash'] = ko_admin_revoke_ical_hash($_SESSION['ses_userid']);
		$notifier->addTextInfo(getLL("ical_links_revoked"));
		$_SESSION['show'] = 'ical_links';
	break;



	//Filter
	case "set_filter_today":
		$_SESSION['filter_start'] = 'today';
		$_SESSION['filter_ende'] = 'immer';

		ko_save_userpref($_SESSION['ses_userid'], 'res_filter_start', $_SESSION['filter_start']);
		ko_save_userpref($_SESSION['ses_userid'], 'res_filter_ende', $_SESSION['filter_ende']);
	break;

	case "submit_filter":
		$start = ko_daten_parse_time_filter(format_userinput($_POST["res_filter"]['date1'], "text"), 'today', FALSE);
		if ($start === NULL) {
			$notifier->addError(22);
			break;
		} else $_SESSION["filter_start"] = $start;

		$end = ko_daten_parse_time_filter(format_userinput($_POST["res_filter"]['date2'], "text"), '', FALSE);
		if ($end === NULL) {
			$notifier->addError(22);
			break;
		} else $_SESSION["filter_ende"] = $end;

		ko_save_userpref($_SESSION['ses_userid'], 'res_filter_start', $_SESSION['filter_start']);
		ko_save_userpref($_SESSION['ses_userid'], 'res_filter_ende', $_SESSION['filter_ende']);
	break;


	case 'set_perm_filter':
		//Permanenter Filter
		if($access['reservation']['MAX'] > 3) {
			$pfs = ko_daten_parse_time_filter($_SESSION['filter_start']);
			$pfe = ko_daten_parse_time_filter($_SESSION['filter_ende'], '');

			ko_set_setting("res_perm_filter_start", $pfs);
			ko_set_setting("res_perm_filter_ende", $pfe);
		}//if(access)
  break;


	case "unset_perm_filter":
		if($access['reservation']['MAX'] > 3) {
			ko_set_setting("res_perm_filter_start", "");
			ko_set_setting("res_perm_filter_ende", "");
		}
	break;




	// Moderation
	case "show_mod_res":
		if($_SESSION["ses_userid"] == ko_get_guest_id()) break;
		$_SESSION["show"]= "show_mod_res";
		$_SESSION["show_start"] = 1;
	break;


	case "res_mod_approve":
	case "res_mod_approve_multi":
		if($access['reservation']['MAX'] < 5) break;

		$ids = array();
		
		if($do_action == "res_mod_approve") {
			$ids[] = format_userinput($_POST["id"], "uint");
		}
		else if($do_action == "res_mod_approve_multi") {
			foreach($_POST["chk"] as $c_i => $c) {
				if($c) $ids[] = format_userinput($c_i, "uint");
			}
		}
		if(!$ids[0]) break;

		$notification = format_userinput($_POST["mod_confirm"], "alpha", FALSE, 5) == "true";

		ko_res_mod_approve($ids, $notification);
	break;  //res_mod_approve



	case "res_mod_delete":
	case "res_mod_delete_multi":
		if($access['reservation']['MAX'] < 2 || ko_get_setting('res_access_prevent_lvl2_del')) break;

		$ids = $email_rec = $noemail_rec = array();
		$res_text = "";

		//Get IDs of reservations to be deleted
		if($do_action == "res_mod_delete") {
			$ids[] = format_userinput($_POST["id"], "uint");
		}
		else if($do_action == "res_mod_delete_multi") {
			foreach($_POST["chk"] as $c_i => $c) {
				if($c) $ids[] = format_userinput($c_i, "uint");
			}
		}
		if(!$ids[0]) break;

		//Only allow notification for moderators
		if($access['reservation']['MAX'] > 4) $notification = format_userinput($_POST['mod_confirm'], 'alpha', FALSE, 5) == 'true';
		else $notification = FALSE;

		$mod_res = db_select_data("ko_reservation_mod", "WHERE `id` IN ('".implode("','", $ids)."')", "*");
		foreach($mod_res as $id => $r) {
			//Check for access rights
			if($access['reservation'][$r['item_id']] < 5 && $r['user_id'] != $_SESSION['ses_userid']) continue;

			//Delete reservation
			db_delete_data("ko_reservation_mod", "WHERE `id` = '$id'");
			$notifier->addInfo(9, $do_action);

			//Log
			ko_get_login($r["user_id"], $log_login);
			$r["user"] = $log_login["login"];
			$r["notification"] = $notification ? getLL("yes") : getLL("no");
			ko_log_diff("mod_res_deleted", $r);

			//email recipients
			if($r["email"]) $email_rec[] = $r["email"];
			else $noemail_rec[] = ko_html($r["name"]).", ".ko_html($r["telefon"]);

			//confirmation-text about the done reservations
			$res_text .= ko_get_res_infotext($r)."\n\n";
		}

		//Benachrichtigung an Beantragenden schicken, falls gewünscht:
		if($notification) {
			$smarty->assign("txt_empfaenger", implode(", ", array_unique($email_rec)));
			$smarty->assign('txt_empfaenger_semicolon', implode('; ', array_unique($email_rec)));
			$smarty->assign("tpl_ohne_email", ($r["email"] == "" ? implode(", ", array_unique($noemail_rec)) : getLL("res_mod_no")) );
			$p = ko_get_logged_in_person();
			$smarty->assign("tpl_show_bcc_an_mich", ($p["email"] ? TRUE : FALSE));
			$smarty->assign("tpl_show_send", TRUE);
			$smarty->assign('txt_betreff', (getLL('email_subject_prefix').(sizeof($ids) > 1 ? getLL('res_emails_mod_delete_subject') : getLL('res_email_mod_delete_subject'))) );

			$smarty->assign('txt_emailtext', ((sizeof($ids) > 1 ? getLL('res_emails_mod_delete_text') : getLL('res_email_mod_delete_text'))."\n\n".ko_html($res_text)) );

			$smarty->assign("tpl_show_rec_link", TRUE);
			$_SESSION["show"]= "email_confirm";
		}//if(mod_confirm)

	break; //res_mod_delete



	//Perfom a moderated edit (update the event associated with the given mod event according to the given data)
	case "res_mod_edit":
		$id = format_userinput($_POST['id'], 'uint');
		if (!$id) $id = format_userinput($_GET['id'], 'uint');
		if (!$id) break;

		$resMod = db_select_data('ko_reservation_mod', "WHERE `id` = '{$id}'", 'id,user_id,item_id', '', '', TRUE, TRUE);
		if (!$resMod) break;

		if($access['reservation']['ALL'] < 4 && $access['reservation'][$resMod['item_id']] < 4 && ($_SESSION['ses_userid'] == ko_get_guest_id() || $resMod['user_id'] != $_SESSION['ses_userid'])) break;

		$edit_id = $id;

		if(!is_array($KOTA['ko_reservation_mod'])) ko_include_kota(array('ko_reservation_mod'));

		$_SESSION['show_back'] = $_SESSION['show'];
		$_SESSION['show'] = 'res_mod_edit';
	break;



	//Email-Versand
	case "submit_email":
		if($_POST['rd_bcc_an_mich'] == 'ja') {
			$p = ko_get_logged_in_person();
			if(check_email($p['email'])) {
				$_POST['txt_bcc'] .= ($_POST['txt_bcc'] == '') ? $p['email'] : ','.$p['email'];
			}
		}

		$recipients = explode(',', str_replace(";", ",", $_POST["txt_empfaenger"]));
		if($_POST["txt_cc"] != "") $cc = explode(',', (str_replace(";", ",", $_POST["txt_cc"])));
		if($_POST["txt_bcc"] != "") $bcc = explode(',', nl2br(str_replace(";", ",", $_POST["txt_bcc"])));

		foreach($recipients AS $key => $value) $recipients[$key] = trim($value);
		foreach($cc AS $key => $value) $cc[$key] = trim($value);
		foreach($bcc AS $key => $value) $bcc[$key] = trim($value);

		$text = ko_emailtext($_POST["txt_emailtext"]);

		//ICS file
		if($_POST['res_ids'] != '' && ko_get_setting('res_attach_ics_for_user')) {
			$ids = explode(',', $_POST['res_ids']);
			foreach($ids as $k => $v) {
				$ids[$k] = format_userinput($v, 'uint');
				if(!$ids[$k]) unset($ids[$k]);
			}
			if(sizeof($ids) > 0) {
				ko_get_reservationen($ics_res, " AND ko_reservation.id IN (".implode(',', $ids).") ");
				$ics_filename = ko_get_ics_file('res', $ics_res, TRUE);
				$file = array($BASE_PATH.'download/'.$ics_filename => getLL('res_ical_filename'));
			}
		} else {
			$file = array();
		}

		ko_send_mail(
			'',
			$recipients,
			$_POST["txt_betreff"],
			$text,
			$file,
			$cc,
			$bcc
		);

		if (!$notifier->hasErrors()) {
	  		$notifier->addInfo(10, $do_action);
			$_SESSION['show'] = $_SESSION['show_back'] ? $_SESSION['show_back'] : 'show_mod_res';
		}
		ko_log("mod_res_approve_email", '"'.format_userinput($_POST["txt_betreff"], "text").'": '.format_userinput($_POST["txt_empfaenger"], "text").", cc: ".format_userinput($_POST["txt_cc"], "text").", bcc: ".format_userinput($_POST['txt_bcc'], "text"));
	break;




	//Export
	case "export_xls_reservation":
		$allowedExports = array();
		if($_SESSION['ses_userid'] != ko_get_guest_id()) $allowedExports = array('pdf', 'xls');
		elseif(ko_get_setting('res_allow_exports_for_guest')) $allowedExports = explode(',', ko_get_setting('res_allow_exports_for_guest'));
		else $allowedExports = array();

		if($access['reservation']['MAX'] < 1 || !in_array('xls', $allowedExports)) break;

		list($rowSelection,$colSelection) = explode(':',$_GET['sel_xls_rows'],2);
		switch($colSelection) {
			case 'all':
				$columns = array();
				ksort($KOTA['ko_reservation']['_listview']);
				foreach($KOTA['ko_reservation']['_listview'] as $kc) {
					$columns[] = $kc['name'];
				}
			break;

			case 'shown':
				$columns = $_SESSION['kota_show_cols_ko_reservation'];
			break;

			default:
				$userperf = db_select_data('ko_userprefs',"WHERE id='".$colSelection."'",'value','','',true);
				$columns = explode(',',$userperf['value']);
		}

		switch ($rowSelection) {
			case "alle":
			case "meine":
				apply_res_filter($z_where, $z_limit);

				if ($rowSelection == "meine") {
					$z_where .= " AND `user_id` = '" . $_SESSION["ses_userid"] . "' ";
				}

				ko_get_reservationen($es, $z_where);
			break;

			case "markierte":
				$ids = array();
				foreach (explode(',', $_GET['chk']) as $c_i) {
					$id = format_userinput($c_i, 'uint');
					if ($id) $ids[] = $id;
				}
				ko_get_reservationen($es, 'AND ko_reservation.id IN (\'' . implode("','", $ids) . '\')');
			break;
		}//switch(sel_xls_rows)

		if (sizeof($es) == 0) $notifier->addError(8, $do_action);

		if (!$notifier->hasErrors()) {
			$columns_sorted = [];


			if(is_array($_SESSION['kota_show_cols_ko_reservation'])) {
				foreach($_SESSION['kota_show_cols_ko_reservation'] as $db_column) {
					if (!in_array($db_column, array_column($KOTA["ko_reservation"]['_listview'], "name"))) continue;
					$columns_sorted[] = $db_column;
				}
			} else {
				foreach ($KOTA["ko_reservation"]["_listview"] as $col) {
					if (!in_array($col['name'], $columns)) continue;
					$columns_sorted[] = $col['name'];
				}
			}
			// force enddatum, endzeit to be in list if startdatum, startzeit is in list
			if(($p = array_search('startdatum',$columns_sorted)) !== false) {
				array_splice($columns_sorted,$p+1,0,array('enddatum'));
			}
			if(($p = array_search('startzeit',$columns_sorted)) !== false) {
				array_splice($columns_sorted,$p+1,0,array('endzeit'));
			}

			$header = array();
			$colTypes = array('');
			foreach($columns_sorted as $col) {
				$header[] = getLL('kota_listview_ko_reservation_'.$col);
				switch($col) {
					case 'startdatum':
					case 'enddatum':
						$colTypes[] = 'date';
						break;
					case 'startzeit':
					case 'endzeit':
						$colTypes[] = 'time';
						break;
					case 'cdate':
						$colTypes[] = 'datetime';
						break;
					default:
						$colTypes[] = 'text';
				}
			}

			//Create XLS file
			$row = 0;
			foreach($es as $e) {
				kota_process_data('ko_reservation', $e, 'xls,list', $log, $e['id']);
				foreach($columns_sorted as $c => $col) {
					$data[$row][] = $e[$col];
				}

				$row++;
			}//foreach(liste)

			$filename = '../download/excel/'.getLL('res_filename_xls').strftime('%d%m%Y_%H%M%S', time()).'.xlsx';
			$filename = ko_export_to_xlsx($header, $data, $filename, 'kOOL','landscape',array(),array(),array(),$colTypes);
			$onload_code = "ko_popup('".$ko_path."download.php?action=file&amp;file=".substr($filename, 3)."');";
		}
	break;


	case 'export_pdf':
		$allowedExports = array();
		if($_SESSION['ses_userid'] != ko_get_guest_id()) $allowedExports = array('pdf', 'xls');
		elseif(ko_get_setting('res_allow_exports_for_guest')) $allowedExports = explode(',', ko_get_setting('res_allow_exports_for_guest'));
		else $allowedExports = array();

		if($access['reservation']['MAX'] < 1 || !in_array('pdf', $allowedExports)) break;

		list($mode, $inc, $re) = explode('-', $_GET['mode']);
		$resourceExport = ($re == 'r' ? true : false);
		switch($mode) {
			case 'd':
				$inc = intval($inc);
				$start = add2date(date('Y-m-d'), 'day', $inc, TRUE);
				if ($resourceExport) {
					$filename = ko_export_cal_weekly_view_resource(1, $start);
				}
				else {
					$filename = ko_export_cal_weekly_view('reservation', 1, $start);
				}
				$onload_code = "ko_popup('".$ko_path."download.php?action=file&amp;file=download/pdf/".$filename."');";
			break;

			case 'w':
				$inc = intval($inc);
				$start = date_find_last_monday(date('Y-m-d'));
				$start = add2date($start, 'week', $inc, TRUE);
				if ($resourceExport) {
					$filename = ko_export_cal_weekly_view_resource_2(7, $start);
				}
				else {
					$filename = ko_export_cal_weekly_view('reservation', 7, $start);
				}
				$onload_code = "ko_popup('".$ko_path."download.php?action=file&amp;file=download/pdf/".$filename."');";
			break;

			case 'm':
				$inc = intval($inc);
				$start = add2date(date('Y-m-01'), 'month', $inc, TRUE);
				$filename = basename(ko_reservation_export_months(1, date('m', strtotime($start)), date('Y', strtotime($start))));
				$onload_code = "ko_popup('".$ko_path."download.php?action=file&amp;file=download/pdf/".$filename."');";
			break;

			case 'y':
				if ($inc == "minus1") {
					$inc = -1;
				} else {
					$inc = intval($inc);
				}
				$filename = basename(ko_export_cal_pdf_year('reservation', 1, (int)date('Y')+$inc));
				$onload_code = "ko_popup('".$ko_path."download.php?action=file&amp;file=download/pdf/".$filename."');";
			break;

			case 's':
				list($inc, $month) = explode(':', $inc);
				if ($inc == "minus1") {
					$inc = -1;
				} else {
					$inc = intval($inc);
				}
				$month = intval($month);
				$filename = basename(ko_export_cal_pdf_year('reservation', $month, (int)date('Y')+$inc, 6));
				$onload_code = "ko_popup('".$ko_path."download.php?action=file&amp;file=download/pdf/".$filename."');";
			break;

			case '12m':
				$inc = intval($inc);
				$filename = basename(ko_reservation_export_months(12, '', (int)date('Y')+$inc));
				$onload_code = "ko_popup('".$ko_path."download.php?action=file&amp;file=download/pdf/".$filename."');";
			break;
		}
	break;



	//Backwards compatibility
	case 'set_no_filter':
	break;



	//Default:
  default:
		if(!hook_action_handler($do_action))
      include($ko_path."inc/abuse.inc");
  break;

}//switch(action)



//HOOK: Plugins erlauben, die bestehenden Actions zu erweitern
hook_action_handler_add($do_action);


//Reread access rights if needed
if(in_array($do_action, array('submit_new_item', 'submit_edit_item', 'submit_multiedit', 'delete_item'))) {
	ko_get_access('reservation', '', TRUE);
}


// If we are handling a request that was redirected by /inc/form.php, then exit here
if ($asyncFormSubmit == 1) {
	throw new Exception('async-form-submit-dummy-exception');
}



//*** Default-Werte auslesen
if(!isset($_SESSION["show_items"])) {
	$show_items_string = ko_get_userpref($_SESSION["ses_userid"], "show_res_items");
	//Get items from userpref or else show them all
	if($show_items_string) {
		$items = explode(",", $show_items_string);
	} else {
		ko_get_resitems($_items);
		$items = array_keys($_items);
	}
	//Only allow items this user has view rights for
	$show_items = array();
	foreach($items as $itemid) {
		if($access['reservation'][$itemid] > 0) $show_items[] = $itemid;
	}
	$_SESSION["show_items"] = $show_items;
}

if(!isset($_SESSION["show_monat"]) || !isset($_SESSION["show_jahr"])) {
	$_SESSION["show_monat"] = date('n');
	$_SESSION["show_jahr "] = date('Y');
}

$_SESSION["show_limit"]= ko_get_userpref($_SESSION["ses_userid"], "show_limit_reservation");
if(!$_SESSION["show_limit"]) $_SESSION["show_limit"]= ko_get_setting("show_limit_reservation");

if(!$_SESSION["show_start"]) {
	$_SESSION["show_start"] = 1;
}
if($_SESSION["sort_item"] == "") {
	$_SESSION["sort_item"] = "startdatum";
	$_SESSION["sort_item_order"] = "ASC";
}
if($_SESSION["sort_group"] == "") {
	$_SESSION["sort_group"] = "name";
	$_SESSION["sort_group_order"] = "ASC";
}
if($_SESSION['cal_view'] == '') {
	$userpref = ko_get_userpref($_SESSION['ses_userid'], 'default_view_reservation');
	if($userpref == 'show_cal_woche') $_SESSION['cal_view'] = 'agendaWeek';
	else if($userpref == 'show_cal_monat') $_SESSION['cal_view'] = 'month';
	else if($userpref == 'show_resource_month') $_SESSION['cal_view'] = 'timelineMonth';
	else if($userpref == 'show_resource_week') $_SESSION['cal_view'] = 'timelineWeek';
	else if($userpref == 'show_resource_day') $_SESSION['cal_view'] = 'timelineDay';
	else $_SESSION['cal_view'] = 'month';
}
if($_SESSION['cal_tag'] == '') {
	$_SESSION['cal_tag'] = strftime('%d', time());
}
if($_SESSION["cal_monat"] == "") {
	$_SESSION["cal_monat"] = strftime("%m", time());
}
if($_SESSION["cal_jahr"] == "") {
	$_SESSION["cal_jahr"] = strftime("%Y", time());
}
if($_SESSION["cal_woche"] == "") {
  $_SESSION["cal_woche"] = strftime("%V", time());
}
if($_SESSION["cal_woche_jahr"] == "") {
  $_SESSION["cal_woche_jahr"] = strftime("%Y", time());
}
if($_SESSION['filter_start'] === NULL) {
	$_SESSION['filter_start'] = 'today';
	$_SESSION['filter_ende'] = 'immer';
	ko_save_userpref($_SESSION['ses_userid'], 'res_filter_start', $_SESSION['filter_start']);
	ko_save_userpref($_SESSION['ses_userid'], 'res_filter_ende', $_SESSION['filter_ende']);
}
$_SESSION["show_birthdays"] = ko_get_userpref($_SESSION["ses_userid"], "show_birthdays");
if(!isset($_SESSION["show_birthdays"])) $_SESSION["show_birthdays"] = FALSE;


//Include submenus
ko_set_submenues();
?>
<!DOCTYPE html 
  PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php print $_SESSION["lang"]; ?>" lang="<?php print $_SESSION["lang"]; ?>">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title><?php print "$HTML_TITLE: ".getLL("module_".$ko_menu_akt); ?></title>
<?php
$js_files = array();
//Color picker for event group form
if(in_array($_SESSION['show'], array('new_item', 'edit_item', 'multiedit_group'))) {
	print '<script language="javaScript" type="text/javascript">ko_path = \''.$ko_path.'\'</script>';
}
if($_SESSION['show'] == 'calendar') {
	$js_files[] = $ko_path.'inc/fullcalendar/lib/fullcalendar.min.js';
	$js_files[] = $ko_path.'inc/fullcalendar/scheduler.min.js';
}
$js_files[] = $ko_path.'inc/ckeditor/ckeditor.js';
$js_files[] = $ko_path.'inc/ckeditor/adapters/jquery.js';
print ko_include_js($js_files);

$css_files = array();
if($_SESSION['show'] == 'calendar') {
	$css_files[] = $ko_path.'inc/fullcalendar/lib/fullcalendar.min.css';
	$css_files[] = $ko_path.'inc/fullcalendar/scheduler.min.css';
	//print ko_get_resitems_css(); -> dont use this anymore since new fullcalendar with timeline
}
print ko_include_css($css_files);
include($ko_path.'inc/js-sessiontimeout.inc');
include("inc/js-reservation.inc");
?>
</head>

<body onload="session_time_init();<?php if(isset($onload_code)) print $onload_code; ?>">

<?php
/*
 * Gibt bei erfolgreichem Login das Menü aus, sonst einfach die Loginfelder
 */
include($ko_path . "menu.php");
ko_get_outer_submenu_code('reservation');

?>


<main class="main">
<form action="index.php" method="post" name="formular" enctype="multipart/form-data">
<input type="hidden" name="action" id="action" value="" />
<input type="hidden" name="id" id="id" value="" />
<input type="hidden" name="del_event" id="del_event" value="" />  <!-- Delete corresponding event -->
<input type="hidden" name="mod_confirm" id="mod_confirm" value="" />  <!-- Confirm a moderated reservation -->
<input type="hidden" name="res_code" id="res_code" value="" />  <!-- Code für Bearbeitung -->
<input type="hidden" name="new_date" id="new_date" value="" />  <!-- Neue Res an Datum -->

<div name="main_content" id="main_content">

<?php

if ($notifier->hasNotifications(koNotifier::ALL)) {
	$notifier->notify();
}

hook_show_case_pre($_SESSION["show"]);

switch($_SESSION["show"]) {
	case "neue_reservation":
		ko_formular_reservation("neu", "", $new_res_data);
	break;

	case "edit_reservation":
		if($action_mode == "POST") $do_id = $_POST["id"];
		else if($action_mode == "GET") $do_id = $_GET["id"];
		else break;
		ko_formular_reservation("edit", format_userinput($do_id, "uint"));
	break;

	case "res_mod_edit":
		ko_formular_reservation("edit_mod", $edit_id);
	break;

	case "liste":
		ko_show_res_liste();
	break;

	case 'calendar':
		ko_res_calendar();
	break;

	case "show_mod_res":
		ko_show_res_liste("mod");
	break;

	case "list_items":
		ko_show_items_liste();
	break;

	case "new_item":
		ko_formular_item("neu", format_userinput($_POST["id"], "uint"));
	break;

	case "edit_item":
		ko_formular_item("edit", format_userinput($_POST["id"], "uint"));
	break;

	case "email_confirm":
		$smarty->assign("tpl_title1", getLL('leute_email_title1'));
		$smarty->assign("tpl_body1", getLL('leute_email_body1'));
		$smarty->assign("tpl_all_recip", getLL('leute_email_all_recipients'));
		$smarty->assign('tpl_all_recip_semicolon', getLL('leute_email_all_recipients_semicolon'));
		$smarty->assign("tpl_no_email", getLL('leute_email_no_email'));
		$smarty->assign("tpl_xls_file", getLL('leute_email_xls_file'));
		$smarty->assign("tpl_title2", getLL('leute_email_title2'));
		$smarty->assign("tpl_body2", getLL('leute_email_body2'));
		$smarty->assign("tpl_more", getLL('leute_email_more'));
		$smarty->assign("tpl_to", getLL('leute_email_to'));
		$smarty->assign("tpl_cc", getLL('leute_email_cc'));
		$smarty->assign("tpl_bcc", getLL('leute_email_bcc'));
		$smarty->assign("tpl_subject", getLL('leute_email_subject'));
		$smarty->assign("tpl_text", getLL('leute_email_text'));
		$smarty->assign("tpl_bcc_me", getLL('leute_email_bcc_me'));
		$smarty->assign("tpl_yes", getLL('yes'));
		$smarty->assign("tpl_no", getLL('no'));
		$smarty->assign("tpl_send", getLL('leute_email_send'));
		$smarty->assign("tpl_error_no_subject", getLL("leute_email_error_no_subject"));
		$smarty->display("ko_formular_email.tpl");
	break;

	case "multiedit":
		ko_multiedit_formular("ko_reservation", $do_columns, $do_ids, $order, array("cancel" => "liste"));
	break;
	
	case "multiedit_group":
		ko_multiedit_formular("ko_resitem", $do_columns, $do_ids, $order, array("cancel" => "list_items"));
	break;

	case "multiedit_mod":
		ko_multiedit_formular("ko_reservation_mod", $do_columns, $do_ids, $order, array("cancel" => "show_mod_res"));
	break;

	case "res_settings":
		ko_res_settings();
	break;

	case 'ical_links':
		ko_res_ical_links();
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
	</main>

	</div>

	<?php include($ko_path . "footer.php"); ?>

</body>
</html>
