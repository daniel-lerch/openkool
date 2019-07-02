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


function apply_daten_filter(&$z_where, &$z_limit, $_start="", $_ende="", $_tg="") {
	global $access;

	$use_start = ($_start != "") ? $_start : $_SESSION["filter_start"];
	$use_ende = ($_ende != "") ? $_ende : $_SESSION["filter_ende"];
	$use_tg = ($_tg != "") ? $_tg : $_SESSION["show_tg"];

	//Permanenten Filter einfügen, falls vorhanden und nur view-Rechte
	$z_where = "";
	$perm_filter_start = ko_get_setting("daten_perm_filter_start");
	$perm_filter_ende  = ko_get_setting("daten_perm_filter_ende");

	// check, if the login has the 'force_global_filter' flag set to 1
	$forceGlobalTimeFilter = ko_get_force_global_time_filter("daten", $_SESSION['ses_userid']);

	if(($forceGlobalTimeFilter || $access['daten']['MAX'] < 2) && ($perm_filter_start || $perm_filter_ende)) {
		if($perm_filter_start != "") {
			$z_where .= " AND enddatum >= '".$perm_filter_start."' ";
		}
		if($perm_filter_ende != "") {
			$z_where .= " AND startdatum <= '".$perm_filter_ende."' ";
		}
	}

	//Filter anwenden, falls gesetzt
	if(isset($use_start) && $use_start != "immer") {
		get_heute($tag, $monat, $jahr);
		if($use_start == "today") {
			$start = sql_datum($tag.".".$monat.".".$jahr);
		} else {
			addmonth($monat, $jahr, $use_start);
			$start = sql_datum("1.".$monat.".".$jahr);
		}
		$z_where .= "AND enddatum >= '$start'";
	}
	if(isset($use_ende) && $use_ende != "immer") {
		get_heute($tag, $monat, $jahr);
		if($use_ende == "today") {
			$ende = sql_datum($tag.".".$monat.".".$jahr);
		} else {
			addmonth($monat, $jahr, ($use_ende+1));
			$ende = sql_datum("0.".$monat.".".$jahr);
		}
		$z_where .= " AND startdatum <= '$ende'";
	}

	//Set filters from KOTA
	if(function_exists('kota_apply_filter')) {
		$kota_where = kota_apply_filter('ko_event');
		if($kota_where != '') $z_where .= " AND ($kota_where) ";
	}

	//Die gewünschten Termingruppen anzeigen
	$z_where_add = "";
	$tgs = array();
	if(is_array($use_tg)) {
		foreach($use_tg as $g) {
			if($access['daten'][$g] < 1) continue;
			$tgs[] = $g;
		}
		$z_where_add = " `eventgruppen_id` IN ('".implode("','", $tgs)."') ";
	}

	if($z_where_add) {
		$z_where .= " AND ( $z_where_add ) ";
	} else {
		//Falls nichts gefunden, ein FALSE-Bedingung einfügen!
		$z_where = " AND ( 1=2 ) ";
	}

	//Limit bestimmen
  $z_limit = "LIMIT " . ($_SESSION["show_start"]-1) . ", " . $_SESSION["show_limit"];
}//apply_daten_filter()








/**
  * Shows list of events
	* Using the data from KOTA
	*/
function ko_list_events($method, $output=TRUE, $mode='html', $dontApplyLimit=FALSE) {
	global $ko_path, $smarty;
	global $access;

	if($method == "mod") {
		ko_list_mod_events("new", $output);
		ko_list_mod_events("edit", $output);
		ko_list_mod_events("delete", $output);
		return;
	}

	if($access['daten']['MAX'] < 1) return FALSE;
	apply_daten_filter($z_where, $z_limit);

	$list = new kOOL_listview();

	$rows = db_get_count("ko_event", "id", $z_where);
	if($_SESSION['show_start'] > $rows) {
		$_SESSION['show_start'] = 1;
		$z_limit = 'LIMIT '.($_SESSION['show_start']-1).', '.$_SESSION['show_limit'];
	}
	if($dontApplyLimit) $z_limit = '';
	ko_get_events($es, $z_where, $z_limit);

	$list->init("daten", "ko_event", array("chk", "edit", "delete"), $_SESSION["show_start"], $_SESSION["show_limit"]);
	$list->showColItemlist();
	$list->setTitle(getLL("daten_list_title"));
	$list->setAccessRights(array('edit' => 2, 'delete' => 2), $access['daten']);
	$list->setActions(array("edit" => array("action" => "edit_termin"),
													"delete" => array("action" => "delete_termin", "confirm" => TRUE))
										);
	//Mark sunday with extra tr class definition
	if(ko_get_userpref($_SESSION['ses_userid'], 'daten_mark_sunday')) {
		$list->setRowClass("ko_list_sunday", 'return strftime("%w", strtotime("STARTDATUM")) == 0;');
	}

	if($method == 'mod') {
		$list->setStats($rows, '', '', '', TRUE);
	} else {
		$list->setStats($rows);
	}
	$list->setSort(TRUE, "setsortevent", $_SESSION["sort_events"], $_SESSION["sort_events_order"]);

	//Footer for event list
	$list_footer = $smarty->get_template_vars('list_footer');
	if($access['daten']['MAX'] > 1) {
		$list_footer[] = array("label" => getLL("daten_list_footer_del_label"),
													 "button" => '<input type="submit" onclick="c=confirm('."'".getLL("daten_list_footer_del_button_confirm")."'".');if(!c) return false;set_action(\'del_selected\');" value="'.getLL("daten_list_footer_del_button").'" />');
	}
	$list->setFooter($list_footer);

	$list->setWarning(kota_filter_get_warntext('ko_event'));


	if($output) {
		$list->render($es, $mode, getLL('daten_filename_xls'));
		if($mode == 'xls') return $list->xls_file;
	} else {
		print $list->render($es);
	}
}//ko_list_events()



/**
  * Shows list of mod events
	*/
function ko_list_mod_events($mode, $output=TRUE) {
	global $ko_path, $smarty;
	global $access;

	//Don't allow guest user to see its event moderations
	if($_SESSION["ses_userid"] == ko_get_guest_id()) return FALSE;
	//Only allow moderators or users who can add new moderated events to see moderations
	if($access['daten']['MAX'] < 2) return FALSE;

	if($access['daten']['MAX'] > 3) {
		if($access['daten']['ALL'] >= 4) {
			$z_where = '';
		} else {
			$mod_eg = array();
			foreach($access['daten'] as $k => $v) {
				if(!intval($k)) continue;
				if($v >= 4) $mod_eg[] = $k;
			}
			$z_where = " AND `eventgruppen_id` IN ('".implode("','", $mod_eg)."') ";
		}
	} else {
		$z_where = " AND `_user_id` = '".$_SESSION["ses_userid"]."' ";
	}

	switch($mode) {
		case "new":
			$action_check = "daten_mod_new_approve";
			$action_delete = "daten_mod_delete";
			//Get mod events
			$z_where .= " AND `_event_id` = '0' AND `_delete` = '0'";
			$rows = db_get_count("ko_event_mod", "id", $z_where);
			ko_get_events_mod($es, $z_where, $z_limit);
		break;

		case "edit":
			$action_check = "daten_mod_edit_approve";
			$action_delete = "daten_mod_delete";
			//Get mod events
			$z_where .= " AND `_event_id` != '0' AND `_delete` = '0'";
			$rows = db_get_count("ko_event_mod", "id", $z_where);
			ko_get_events_mod($_es, $z_where, $z_limit);
			$es = array();
			foreach($_es as $mid => $me) {
				//KOTA process data here so it gets displayed nicely in the list view (processing afterwards doesn't work, because of the differences)
				$kme = $me; kota_process_data("ko_event_mod", $kme, "list", $log, $mid);
				$event = db_select_data("ko_event", "WHERE `id` = '".$me["_event_id"]."'", "*", "", "", TRUE);
				$kevent = $event; kota_process_data("ko_event", $kevent, "list", $log, $me["_event_id"]);
				//Add user id from moderated event
				$event["_user_id"] = $me["_user_id"];
				$kevent["_user_id"] = $kme["_user_id"];
				foreach($event as $key => $value) {
					if($key == "id") {
						$es[$mid]["id"] = $mid;
					} else if(in_array($key, array("reservationen", "endzeit"))) {
						continue;
					} else {
						//Merge times into one field
						if($key == "startzeit") {
							$value = $event["startzeit"]." - ".$event["endzeit"];
							$me[$key] = $me["startzeit"]." - ".$me["endzeit"];
						}
						//Keep eventgruppen_id and _user_id for fake access rights
						if($key == "eventgruppen_id") $es[$mid]["_".$key] = $value;
						if($key == "_user_id") $es[$mid]["_".$key] = $me["_user_id"];
						//Compare values and mark differences
						if($value != $me[$key]) {
							$es[$mid][$key] = '<span style="text-decoration: line-through;">'.$kevent[$key]."</span>".($kevent[$key] != "" ? "<br />" : "")."<b>".$kme[$key]."</b>";
						} else {
							$es[$mid][$key] = $kevent[$key];
						}
					}
				}//foreach(event as key => value)
			}//foreach(_es as mid => me)
		break;

		case "delete":
			$action_check = "daten_mod_delete_approve";
			$action_delete = "daten_mod_delete";
			//Get mod events
			$z_where .= " AND `_event_id` != '0' AND `_delete` = '1'";
			$rows = db_get_count("ko_event_mod", "id", $z_where);
			ko_get_events_mod($_es, $z_where, $z_limit);
			//Show values from stored events instead of event_mod entries, as the ones from ko_event might have been edited since the moderated deletion
			$es = array();
			foreach($_es as $mid => $me) {
				$event = db_select_data("ko_event", "WHERE `id` = '".$me["_event_id"]."'", "*", "", "", TRUE);
				//Add user id
				$event["_user_id"] = $me["_user_id"];
				//Reset id to id of moderated event
				$event["id"] = $me["id"];
				$es[$mid] = $event;
			}
		break;
	}
	if($rows == 0) return;

	$list = new kOOL_listview();

	//Build fake accessRights arrays (TODO: Only show check if no "Doppelbelegung" for new)
	$mod_access = array('ALL' => $access['daten']['ALL'] == 4 ? 5 : $access['daten']['ALL']);
	foreach($es as $e) {
		$eg_id = is_numeric($e["eventgruppen_id"]) ? $e["eventgruppen_id"] : $e["_eventgruppen_id"];
		$user_id = is_numeric($e["_user_id"]) ? $e["_user_id"] : $e["__user_id"];
		$mod_access[$eg_id] = $user_id == $_SESSION['ses_userid'] ? 4 : ($access['daten'][$eg_id] == 4 ? 5 : $access['daten'][$eg_id]);
	}
	$list->init("daten", "ko_event_mod", array("chk", "check", "delete"), $_SESSION["show_start"], $_SESSION["show_limit"]);
	$list->disableListCheckAll();
	$list->setTitle(getLL("daten_mod_list_title")." ".getLL("daten_mod_list_title_".$mode));
	$list->setAccessRights(array('check' => 5, 'delete' => 4), $mod_access, '_eventgruppen_id');
	$list->setActions(array("check" => array("action" => $action_check,
																					 "additional_js" => "c1=confirm('".getLL("daten_mod_confirm_confirm")."');if(!c1) return false;c = confirm('".getLL("daten_mod_confirm_confirm2")."');set_hidden_value('mod_confirm', c);"),
													"delete" => array("action" => $action_delete,
																						"additional_js" => ($access['daten']['MAX'] > 3 ? "c1 = confirm('".getLL("daten_mod_decline_confirm")."');if(!c1) return false;c = confirm('".getLL("daten_mod_decline_confirm2")."');set_hidden_value('mod_confirm', c);" : "") ))
										);
	$list->disableMultiedit();
	if($mode == "edit") $list->disableKotaProcess();

	$list->setStats($rows, 1, 500);
	$list->setSort(TRUE, "setsortevent", $_SESSION["sort_events"], $_SESSION["sort_events_order"]);

	//Footer for mod events
	$list_footer = $smarty->get_template_vars('list_footer');
	$list_footer[] = array("label" => getLL("daten_list_footer_del_label"), 
												 "button" => '<input type="submit" onclick="c1 = confirm(\''.getLL("daten_mod_decline_confirm").'\');if(!c1) return false;'.($access['daten']['MAX'] > 3 ? 'c = confirm(\''.getLL("daten_mod_decline_confirm2").'\');set_hidden_value(\'mod_confirm\', c);' : '').'set_action(\''.$action_delete.'_multi\');" value="'.getLL("daten_list_footer_del_button").'" />');
	if($access['daten']['MAX'] > 3) {
		$list_footer[] = array("label" => getLL("daten_list_footer_confirm_label"),
													 "button" => '<input type="submit" onclick="c1=confirm(\''.getLL("daten_mod_confirm_confirm").'\');if(!c1) return false;c = confirm(\''.getLL("daten_mod_confirm_confirm2").'\');set_hidden_value(\'mod_confirm\', c);set_action(\''.$action_check.'_multi\');" value="'.getLL("ok").'" />');
	}
	$list->setFooter($list_footer);


	if($output) {
		$list->render($es);
	} else {
		print $list->render($es);
	}
}//ko_list_mod_events()


/***
 * lists all reminders
 * @param bool $output
 * @param string $mode
 * @return mixed
 */
function ko_list_reminders($output=TRUE, $mode='html') {
	global $smarty, $access, $ko_path;

	ko_get_reminders($es, 1);

	foreach ($es as $k => $reminder) {
		if (!ko_get_reminder_access($reminder)) {
			unset($es[$k]);
		}
	}

	$rows = sizeof($es);

	$list = new kOOL_listview();

	$list->init('daten', 'ko_reminder', array('chk', 'send', 'edit', 'delete'), 1, 1000);
	$list->setTitle(getLL('ko_event_reminder_list_title'));
	$list->setAccessRights(array('edit' => 1, 'delete' => 1, 'send' => 1), $access['daten']['REMINDER']);
	$list->setActions(
		array(
			'edit' => array('action' => 'edit_reminder'),
			'delete' => array('action' => 'delete_reminder', 'confirm' => TRUE),
			'send' => array('action' => 'send_test_reminder')
		)
	);
	$list->setSort(TRUE, 'setsort', $_SESSION['sort_event_reminders'], $_SESSION['sort_event_reminders_order']);
	$list->setStats($rows);
	$list->setWarning(kota_filter_get_warntext('ko_reminder'));
	$list->ShowColItemlist();


	//Output the list
	if($output) {
		$list->render($es, $mode);
	} else {
		print $list->render($es);
	}
}//ko_list_tools()


/***
 * @param $mode -- either 'edit' or 'add'
 * @param string $id -- if mode == 'edit', id of the id of the reminder has to be supplied here
 * @param array $new_data -- hashmap of the updated fields
 * @return bool
 */
function ko_formular_reminder($mode, $id='') {
	global $KOTA, $access;

	if($mode == 'new') {
		$id = 0;
	} else if($mode == 'edit') {
		if(!$id) return FALSE;
	} else {
		return FALSE;
	}

	//Access check
	//if($access['vsasexams']['ALL'] < 2 && $access['vsasexams'][$id] < 2) return FALSE;

	$form_data['title'] =  $mode == 'new' ? getLL('ko_event_reminder_form_title_new') : getLL('ko_event_reminder_form_title_edit');
	$form_data['submit_value'] = getLL('save');
	$form_data['action'] = $mode == 'new' ? 'submit_new_event_reminder' : 'submit_edit_event_reminder';
	$form_data['cancel'] = 'list_reminders';
	$form_data['type'] = '1';

	ko_multiedit_formular('ko_reminder', '', $id, '', $form_data, FALSE, 1);

}//ko_formular_tool()





function ko_list_groups($method, $output=TRUE) {
	global $ko_path;
	global $access;

	if($access['daten']['MAX'] < 3) return;

	$list = new kOOL_listview();

	//Nur die erlaubten Termingruppen anzeigen
	if($access['daten']['ALL'] < 3) {
		$egs = array();
		foreach($access['daten'] as $k => $v) {
			if(!intval($k)) continue;
			if($v >= 3) $egs[] = $k;
		}
		$z_where = " AND `id` IN ('".implode("','", $egs)."') ";
	} else {
		$z_where = '';
	}

	//Set filters from KOTA
	$kota_where = kota_apply_filter('ko_eventgruppen');
	if($kota_where != '') $z_where .= " AND ($kota_where) ";

	//Limit bestimmen
  $z_limit = "LIMIT " . ($_SESSION["show_start"]-1) . ", " . $_SESSION["show_limit"];
	$rows = db_get_count("ko_eventgruppen", "id", $z_where);
	ko_get_eventgruppen($es, $z_limit, $z_where);

	$list->init("daten", "ko_eventgruppen", array("chk", "edit", "delete"), $_SESSION["show_start"], $_SESSION["show_limit"]);
	$list->setTitle(getLL("daten_groups_list_title"));
	$list->setAccessRights(array('edit' => 3, 'delete' => 'ALL3'), $access['daten']);
	$list->setActions(array("edit" => array("action" => "edit_gruppe"),
													"delete" => array("action" => "delete_gruppe", "confirm" => TRUE))
										);
	$list->setStats($rows);
	$list->setSort(TRUE, "setsorteventgroups", $_SESSION["sort_tg"], $_SESSION["sort_tg_order"]);

	if($output) {
		$list->render($es);
	} else {
		print $list->render($es);
	}
}//ko_list_groups()





function ko_formular_termin($mode, $id, $data=array()) {
	global $smarty, $KOTA, $ko_path, $EVENTS_SHOW_RES_FIELDS;
	global $access, $all_groups;

	//State of repetition settings: Defaults to closed
	$repetition_state = 'closed';

	if($mode == "edit" && $id != 0) {
		//Get event data to be edited
	  ko_get_event_by_id($id, $e);
		if($access['daten'][$e['eventgruppen_id']] < 2) return;

		//Infos about this event
		if($access['daten']['MAX'] > 3) {
			ko_get_login($e['user_id'], $event_login);
			$event_info = getLL('res_info_cdate').': <b>'.sqldatetime2datum($e['cdate']).'</b>';
			$event_info .= '<br />'.getLL('res_info_user').': <b>'.$event_login['login'].' ('.$e['user_id'].')</b>';
			$event_info .= '<br />'.getLL('res_info_mdate').': <b>'.sqldatetime2datum($e['last_change']).'</b>';
		}

		if(ko_module_installed("reservation")) {
			//Set res times to event times if none are given for even group
			ko_get_eventgruppe_by_id($e["eventgruppen_id"], $tg);
			//Set res times as fallback from eventgroup or event (will be overwritten below if reservations were found)
			foreach($EVENTS_SHOW_RES_FIELDS as $f) {
				${'res_'.$f} = $tg['res_'.$f];
			}

			//Get connected reservations
			if($e["reservationen"]) {
				$do_res_values = $do_res_output = array();
				foreach(explode(",", $e["reservationen"]) as $r) {
					$res_ = "";
					$res = db_select_data("ko_reservation AS r LEFT JOIN ko_resitem AS i ON r.item_id = i.id", "WHERE r.id = '$r'", "r.*, i.name AS item_name", "", "", TRUE);
					//Ignore deleted reservations
					if(!$res["id"]) continue;

					$do_res_values[] = $res["item_id"];
					$do_res_output[] = $res["item_name"];
					//Overwrite res times with actual values from reservations
					foreach($EVENTS_SHOW_RES_FIELDS as $f) {
						${'res_'.$f} = $res[$f];
					}
					// get res dates
					$res_startdatum_delta = ko_get_time_diff('d', $e['startdatum'], $res['startdatum']);
					$res_enddatum_delta = ko_get_time_diff('d', $e['enddatum'], $res['enddatum']);
				}//foreach(res_s as r)
			}//if(e[reservationen])
		}//if(ko_module_installed(reservation))

	}//if(edit && id)

	else if($mode == "neu") {
		if($access['daten']['MAX'] < 2) return;

		//set values given through data[]
		$data["startzeit"] = $data["start_time"];
		$data["endzeit"] = $data["end_time"];
		kota_assign_values("ko_event", $data);
		//Zeit über $data-Array gesetzt (z.B. aus Wochenansicht)
		if(isset($data['start_time'])) $res_startzeit = $data['start_time'];
		if(isset($data['end_time'])) $res_endzeit = $data['end_time'];
		
		//Refill form with submitted values
		if($_POST["submit"]) {
			$form_values = NULL;
			foreach($_POST["koi"]["ko_event"] as $col => $value) {
				if(isset($KOTA["ko_event"][$col])) {
					$form_values[$col] = $value[0];
				}
			}
			kota_assign_values("ko_event", $form_values);

			//Add fields for reservations again
			if(ko_module_installed("reservation")) {
				foreach($EVENTS_SHOW_RES_FIELDS as $f) {
					${'res_'.$f} = $_POST['res_'.$f];
				}

				$res_startdatum_delta = $_POST['res_startdatum_delta'];
				$res_enddatum_delta = $_POST['res_enddatum_delta'];

				//Show repetition settings opened if any had been selected
				if($_POST['rd_wiederholung'] != 'keine') {
					$repetition_state = 'open';
				}

				//Refill selected res items
				if($_POST['sel_do_res'] != '') {
					$_itemids = explode(',', $_POST['sel_do_res']);
					$itemids = array();
					foreach($_itemids as $v) {
						if(!$v || !intval($v)) continue;
						$itemids[] = intval($v);
					}
					if(sizeof($itemids) > 0) {
						$items = db_select_data('ko_resitem', "WHERE `id` IN (".implode(',', $itemids).")");

						foreach($items as $k => $v) {
							$do_res_values[] = $v['id'];
							$do_res_output[] = $v['name'];
						}
					}
				}
			}
		}

	}//if(mode == "neu")
	else return;

	
	//Wiederholungs-Auswahl wieder setzen
	$true = 0;
	switch($_POST["rd_wiederholung"]) {
		case "keine": $true = 0; break;
		case "taeglich": $true = 1; break;
		case "woechentlich": $true = 2; break;
		case "monatlich1": $true = 3; break;
		case "monatlich2": $true = 4; break;
		default: $true = 0;
	}
	for($i=0; $i<5; $i++) {
		$rd_wiederholung_checked[$i] = ($true == $i) ? 'checked="checked"' : "";
	}
	$smarty->assign("rd_wiederholung_checked", $rd_wiederholung_checked);
	$txt_repeat_tag = $_POST["txt_repeat_tag"] ? format_userinput($_POST["txt_repeat_tag"], "uint") : 1;
	$txt_repeat_woche = $_POST["txt_repeat_woche"] ? format_userinput($_POST["txt_repeat_woche"], "uint") : 1;
	$txt_repeat_monat2 = $_POST["txt_repeat_monat2"] ? format_userinput($_POST["txt_repeat_monat2"], "uint") : 1;


	//Select-Inputs für Wiederholungen abfüllen (Auswahl vom letzten Mal wieder setzen
	$values = array(1, 2, 3, 4, 5, 6);
	$output = array("1.", "2.", "3.", "4.", "5.", getLL('daten_repeat_monthly2_every_last'));
	$value = format_userinput($_POST["sel_monat1_nr"], "uint", FALSE, 1);
	$sel1_code = "";
	foreach($values as $i => $v) {
		$sel = ($value == $v) ? ' selected="selected"' : '';
		$sel1_code .= '<option value="'.$v.'"'.$sel.' label="'.$output[$i].'">'.$output[$i].'</option>';
	}

	$values = array(1, 2, 3, 4, 5, 6, 0);
	$monday = date_find_last_monday(date("Y-m-d"));
	$output = NULL; for($i=0; $i<7; $i++) $output[] = strftime("%A", strtotime(add2date($monday, "tag", $i, TRUE)));
	$value = format_userinput($_POST["sel_monat1_tag"], "uint", FALSE, 1);
	$sel2_code = "";
	foreach($values as $i => $v) {
		$sel = ($value == $v) ? ' selected="selected"' : '';
		$sel2_code .= '<option value="'.$v.'"'.$sel.' label="'.$output[$i].'">'.$output[$i].'</option>';
	}

	$repeat_descs[] = getLL("daten_repeat_none");
	$repeat_descs[] = sprintf(getLL("daten_repeat_daily"), ' </label><input type="text" name="txt_repeat_tag" value="'.$txt_repeat_tag.'" size="2" /> ', '<label>');
	$repeat_descs[] = sprintf(getLL("daten_repeat_weekly"), ' </label><input type="text" name="txt_repeat_woche" value="'.$txt_repeat_woche.'" size="2" /> ', '<label>');
	$repeat_descs[] = sprintf(getLL("daten_repeat_monthly1"), ' </label><select name="sel_monat1_nr" size="0">'.$sel1_code.'</select><select name="sel_monat1_tag" size="0">'.$sel2_code.'</select><label>');
	$repeat_descs[] = sprintf(getLL("daten_repeat_monthly2"), ' </label><input type="text" name="txt_repeat_monat2" value="'.$txt_repeat_monat2.'" size="2" /> ', '<label>');


	//Repeat-Stop
	$values = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31);
	$output = $values;
	$value = $_POST["sel_bis_tag"] ? format_userinput($_POST["sel_bis_tag"], "uint", FALSE, 2) : 31;
	$sel_day_code = "";
	foreach($values as $i => $v) {
		$sel = ($value == $v) ? ' selected="selected"' : '';
		$sel_day_code .= '<option value="'.$v.'"'.$sel.' label="'.$output[$i].'">'.$output[$i].'</option>';
	}

	$values = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12);
	$jan = date("Y-m-d", mktime(1,1,1, 1, 1, 2006));
	$output = NULL; for($i=0; $i<12; $i++) $output[] = strftime("%B", strtotime(add2date($jan, "monat", $i, TRUE)));
	$value = $_POST["sel_bis_monat"] ? format_userinput($_POST["sel_bis_monat"], "uint", FALSE, 2) : strftime("%m", time());
	$sel_month_code = "";
	foreach($values as $i => $v) {
		$sel = ($value == $v) ? ' selected="selected"' : '';
		$sel_month_code .= '<option value="'.$v.'"'.$sel.' label="'.$output[$i].'">'.$output[$i].'</option>';
	}

	$values = array();
	for($i=0; $i<10; $i++) {
		$values[] = (int)strftime("%Y", time())+$i;
	}
	$value = $_POST["sel_bis_jahr"] ? format_userinput($_POST["sel_bis_jahr"], "uint", FALSE, 4) : strftime("%Y", time());
	$sel_year_code = "";
	foreach($values as $v) {
		$sel = ($value == $v) ? ' selected="selected"' : '';
		$sel_year_code .= '<option value="'.$v.'"'.$sel.' label="'.$v.'">'.$v.'</option>';
	}

	//Eventgroup select to exclude holidays
	$holiday_code = '<select name="sel_repeat_eg" size="0"><option value=""></option>';
	$cals = db_select_data('ko_event_calendar', 'WHERE 1');
	$egs = db_select_data('ko_eventgruppen', 'WHERE 1', '*', 'ORDER BY `name` ASC');
	$values = $descs = array();
	$value = $_POST['sel_repeat_eg'] ? format_userinput($_POST['sel_repeat_eg'], 'uint') : '';
	foreach($egs as $eg) {
		$sel = $value == $eg['id'] ? 'selected="selected"' : '';
		$holiday_code .= '<option value="'.$eg['id'].'" '.$sel.'>';
		$holiday_code .= ($eg['calendar_id'] > 0 ? strtoupper($cals[$eg['calendar_id']]['name']).': ' : '').$eg['name'];
		$holiday_code .= '</option>';
	}
	$holiday_code .= '</select>';

	$repeat_stop  = '<select name="sel_bis_tag" size="0">'.$sel_day_code.'</select>&nbsp;&nbsp;';
	$repeat_stop .= '<select name="sel_bis_monat" size="0">'.$sel_month_code.'</select>&nbsp;&nbsp;';
	$repeat_stop .= '<select name="sel_bis_jahr" size="0">'.$sel_year_code.'</select>';
	$repeat_stop .= '<br />'.getLL("daten_repeat_or").'<br /><input type="text" name="txt_num_repeats" size="4" maxlength="3" onkeyup="repeat_disable(this.value);" />&nbsp;'.getLL("daten_repeat_iterations");
	$repeat_stop .= '<br /><br />'.getLL('daten_repeat_eg').'<br />'.$holiday_code;



	//Formular aufbauen
	$table = "ko_event";
	$rowcounter = 0;
	$mandatory = explode(",", ko_get_setting("daten_mandatory"));
	$gc = 0;

	//add mandatory-flags
	foreach($KOTA["ko_event"] as $key => $kota_field) {
		if(in_array($key, $mandatory)) $KOTA["ko_event"][$key]["form"]["desc"] = getLL("kota_ko_event_".$key)." *";
	}

	//Spezielle Formular-Einstellungen vornehmen
	if($mode == "neu") $KOTA[$table]["eventgruppen_id"]["form"]["params"] .= ' onchange="javascript:selEventGroup(this.value);"';

	//Get values for room-select from ko_event and ko_eventgruppen
	$values = array_unique(array_merge(db_select_distinct("ko_event", "room", "", $KOTA['ko_event']['room']['form']['where'], TRUE), db_select_distinct("ko_eventgruppen", "room", "", $KOTA['ko_eventgruppen']['room']['form']['where'], TRUE)));
	$KOTA[$table]["room"]["form"]["values"] = $values;
	$KOTA[$table]["room"]["form"]["descs"] = $values;
	

	//get first part of form from kota
	$group = ko_multiedit_formular($table, "", $id, "", "", TRUE);
	$group[$gc]["titel"] = "";
	$rowcounter = sizeof($group[$gc]["row"])+1;


	//Wiederholungen (nur bei Neu)
	if($mode == "neu") {
		$group[++$gc] = array("titel" => getLL("daten_repeat"), "state" => $repetition_state, "colspan" => 'colspan="2"');
		$group[$gc]["row"][$rowcounter]["inputs"][0] = array("desc" => getLL("daten_repeat_title1"),
																 "type" => "radio",
																 "name" => "rd_wiederholung",
																 "values" => array("keine", "taeglich", "woechentlich", "monatlich1", "monatlich2"),
																 "descs" => $repeat_descs,
																 "separator" => "<br />",
																 "value" => isset($_POST["rd_wiederholung"]) ? $_POST["rd_wiederholung"] : "keine"
																 );
		$group[$gc]["row"][$rowcounter++]["inputs"][1] = array("desc" => getLL("daten_repeat_title2"),
																 "type" => "html",
																 "value" => $repeat_stop
																 );
	}


	//Group subscriptions
	if(ko_get_setting('daten_gs_pid') && ko_module_installed('groups') && ($access['groups']['ALL'] > 2 || $access['groups'][ko_get_setting('daten_gs_pid')] > 2)) {
		if($e['gs_gid']) {
			if(!is_array($all_groups)) ko_get_groups($all_groups);
			$ml = ko_groups_get_motherline(ko_groups_decode($e['gs_gid'], 'group_id'), $all_groups);
			$group_desc = ko_groups_decode((sizeof($ml) > 1 ? 'g'.implode(':g', $ml).':' : '').$e['gs_gid'], 'group_desc_full');
			$desc2 = '<a href="'.$ko_path.'groups/index.php?action=edit_group&id='.ko_groups_decode($e['gs_gid'], 'group_id').'">'.$group_desc.'</a>';
		} else {
			$desc2 = '';
		}
		$group[++$gc] = array('titel' => getLL('daten_group_subscription'), 'state' => ($e['gs_gid'] ? 'open' : 'closed'), 'colspan' => 'colspan="2"');
		$group[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('daten_group_subscription_gid'),
																 'type' => 'checkbox',
																 'name' => 'chk_gs_gid',
																 'value' => 1,
																 'params' => $e['gs_gid'] ? 'checked="checked"' : '',
																 'desc2' => $desc2,
																 );

	}

	//Show rota
	//TODO: Access check (needed?, maybe done in ko_rota_get_schedulling_code()?)
	if($mode == 'edit' && $e['rota'] && ko_module_installed('rota')) {
		$teams = array_keys(db_select_data('ko_rota_teams', 'WHERE 1'));
		$code = '<div name="rota_schedule_'.$id.'">'.ko_rota_get_schedulling_code($id, 'event', $teams).'</div>';

		$group[++$gc] = array('titel' => getLL('daten_form_rota_title'), 'state' => 'closed', 'colspan' => 'colspan="2"');
		$group[$gc]['row'][$rowcounter++]['inputs'][0] = array('desc' => '',
																 'type' => 'html',
																 'value' => $code,
																 'colspan' => 'colspan="2"',
																 );
	}


	//Reservationen
	if(ko_module_installed("reservation")) {
		//Prepare dynsoubleselect for res items
		kota_ko_reservation_item_id_dynselect($values, $descs, 2);
		$tpl_res_values = $tpl_res_descs = array();
		foreach($values as $vid => $value) {
			$tpl_res_values[] = $vid;
			$suffix = is_array($value) ? "-->" : "";
			$tpl_res_descs[] = $descs[$vid].$suffix;
		}

		//Add inputs for resitems and res_[start|stop]time
		$group[++$gc]["row"][$rowcounter++]["inputs"][0] = array("type" => "   ");
		$group[$gc]["row"][$rowcounter++]["inputs"][0] = array("desc" => getLL("daten_linked_reservations"),
																 "type" => "doubleselect",
		  													 "js_func_add" => "resgroup_doubleselect_add",
																 "name" => "sel_do_res",
																 "values" => $tpl_res_values,
																 "descs" => $tpl_res_descs,
																 "avalues" => $do_res_values,
																 "avalue" => implode(",", $do_res_values),
																 "adescs" => $do_res_output,
																 "params" => 'size="7"',
																 "colspan" => 'colspan="2"'
																 );
		$t = ko_multiedit_formular('ko_reservation', $EVENTS_SHOW_RES_FIELDS, 0, '', '', TRUE);
		foreach($t[0]['row'] as $row) {
			foreach($row['inputs'] as $k => $input) {
				list($t1, $field, $t2) = explode('][', $input['name']);
				$input['name'] = 'res_'.$field;
				if(in_array($field, array('startzeit', 'endzeit'))) {
					$input['value'] = sql_zeit(${'res_'.$field});
				} else if($input['type'] == 'checkbox') {
					$input['params'] = ${'res_'.$field} ? 'checked="checked"' : '';
				} else {
					$input['value'] = ${'res_'.$field};
				}
				$group[$gc]['row'][$rowcounter]['inputs'][$k] = $input;
			}
			$rowcounter++;
		}
		// delta date for reservations
		$e = getLL('earlier');
		$l = getLL('later');
		$day = getLL('time_day');
		$days = getLL('time_days');
		$values = array(0, -1, -2, -3, -4, -5, -6, -7);
		$descs = array(
			getLL('time_on_same_day'),
			1 . ' ' . $day . ' ' . $e,
			2 . ' ' . $days . ' ' . $e,
			3 . ' ' . $days . ' ' . $e,
			4 . ' ' . $days . ' ' . $e,
			5 . ' ' . $days . ' ' . $e,
			6 . ' ' . $days . ' ' . $e,
			7 . ' ' . $days . ' ' . $e,
		);
		$group[$gc]["row"][$rowcounter]["inputs"][0] = array("desc" => getLL("kota_ko_event_startdatum"),
			"type" => "select",
			"name" => "res_startdatum_delta",
			"values" => $values,
			"descs" => $descs,
			"value" => $res_startdatum_delta,
		);

		$values = array(0, 1, 2, 3, 4, 5, 6, 7);
		$descs = array(
			getLL('time_on_same_day'),
			1 . ' ' . $day . ' ' . $l,
			2 . ' ' . $days . ' ' . $l,
			3 . ' ' . $days . ' ' . $l,
			4 . ' ' . $days . ' ' . $l,
			5 . ' ' . $days . ' ' . $l,
			6 . ' ' . $days . ' ' . $l,
			7 . ' ' . $days . ' ' . $l,
		);
		$group[$gc]["row"][$rowcounter++]["inputs"][1] = array("desc" => getLL("kota_ko_event_enddatum"),
			"type" => "select",
			"name" => "res_enddatum_delta",
			"values" => $values,
			"descs" => $descs,
			"value" => $res_enddatum_delta,
		);
	}


	if($access['daten']['MAX'] > 3 && $mode == 'edit') {
		$group[++$gc]['row'][$rowcounter++]['inputs'][0] = array('type' => '   ');
		$group[$gc]['row'][$rowcounter++]['inputs'][0] = array('desc' => getLL('daten_info_title'),
			'type' => 'html',
			'value' => $event_info,
			'colspan' => 'colspan="2"',
		);
	}


	$smarty->assign("tpl_titel", ( ($mode == "neu") ? getLL("daten_new_event") : getLL("daten_edit_event")) );
	$smarty->assign("tpl_submit_value", getLL("save"));
	$smarty->assign('submit_class', 'daten_'.($mode == 'neu' ? 'neuer_termin' : 'edit_termin'));
	$smarty->assign("tpl_id", $id);
	$smarty->assign("tpl_action", ( ($mode == "neu") ? "submit_neuer_termin" : "submit_edit_termin") );
	//Add button "save as new"
	if($mode == 'edit') {
		$smarty->assign("tpl_submit_as_new", getLL('daten_submit_as_new'));
		$smarty->assign('tpl_action_as_new', 'submit_as_new_event');
	}
	$cancel = $_SESSION['show_back'] ? $_SESSION['show_back'] : ko_get_userpref($_SESSION["ses_userid"], "default_view_daten");
	if(!$cancel) $cancel = "show_cal_monat";
	$smarty->assign("tpl_cancel", $cancel);
	$smarty->assign("tpl_groups", $group);

	$smarty->assign("help", ko_get_help("daten", "form_neuer_termin"));

	$smarty->display("ko_formular.tpl");
}//ko_formular_termin()





function ko_formular_termingruppe($mode, $id=0) {
	global $smarty, $KOTA;
	global $access;

	if($access['daten']['MAX'] < 3) return;

	if($mode == "edit" && $id != 0) {
		//ok
	} else if($mode == "neu") {
		$id = 0;
	}//if(mode == "neu")
	else return;

	//Get values for room-select from ko_event and ko_eventgruppen
	$values = array_unique(array_merge(db_select_distinct('ko_event', 'room', '', "WHERE `import_id` = ''", TRUE), db_select_distinct('ko_eventgruppen', 'room', '', '', TRUE)));
	$KOTA["ko_eventgruppen"]["room"]["form"]["values"] = $values;
	$KOTA["ko_eventgruppen"]["room"]["form"]["descs"] = $values;
	
	$form_data["title"] = $mode == "neu" ? getLL("daten_new_eventgroup") : getLL("daten_edit_eventgroup");
  $form_data["submit_value"] = getLL("save");
  $form_data["action"] = $mode == "neu" ? "submit_neue_gruppe" : "submit_edit_gruppe";
  $form_data["cancel"] = "all_groups";

	ko_multiedit_formular("ko_eventgruppen", "", $id, "", $form_data);
}//ko_formular_termingruppe()





function ko_formular_googlecal($mode, $id=0) {
	global $smarty, $KOTA;
	global $access;

	if($access['daten']['MAX'] < 3) return;

	if($mode == 'edit' && $id != 0) {
		//ok
	} else if($mode == 'new') {
		$id = 0;
	}//if(mode == 'neu')
	else return;

	$form_data['title'] = $mode == 'new' ? getLL('daten_new_googlecal') : getLL('daten_edit_googlecal');
	$form_data['submit_value'] = getLL('save');
	$form_data['action'] = $mode == 'new' ? 'submit_new_googlecal' : 'submit_edit_googlecal';
	$form_data['cancel'] = 'all_groups';
	$form_data['type'] = '1';

	ko_multiedit_formular('ko_eventgruppen', '', $id, '', $form_data, FALSE, 1);
}//ko_formular_googlecal()




function ko_formular_ical($mode) {
	global $smarty, $KOTA;
	global $access;

	if($mode != 'new') return;
	if($access['daten']['MAX'] < 3) return;

	$form_data['title'] = getLL('daten_new_ical');
	$form_data['submit_value'] = getLL('save');
	$form_data['action'] = 'submit_new_ical';
	$form_data['cancel'] = 'all_groups';
	$form_data['type'] = '3';

	ko_multiedit_formular('ko_eventgruppen', '', 0, '', $form_data, FALSE, 3);
}//ko_formular_ical()




/**
  * Displays settings for the events
	*/
function ko_daten_settings() {
	global $smarty;
	global $access, $MODULES;

	if($access['daten']['MAX'] < 1 || $_SESSION['ses_userid'] == ko_get_guest_id()) return FALSE;

	//build form
	$gc = 0;
	$rowcounter = 0;
	$frmgroup[$gc]['titel'] = getLL('settings_title_user');

	$frmgroup[$gc]['row'][$rowcounter++]['inputs'][0] = array('desc' => getLL('daten_settings_default_view'),
			'type' => 'select',
			'name' => 'sel_daten',
			'values' => array('all_events', 'show_cal_jahr', 'show_cal_monat', 'show_cal_woche'),
			'descs' => array(getLL('submenu_daten_all_events'), getLL('submenu_daten_cal_year'), getLL('submenu_daten_cal_monat'), getLL('submenu_daten_cal_week')),
			'value' => ko_html(ko_get_userpref($_SESSION['ses_userid'], 'default_view_daten'))
			);
	$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('admin_settings_limits_numberof_events'),
			'type' => 'text',
			'params' => 'size="10"',
			'name' => 'txt_limit_daten',
			'value' => ko_html(ko_get_userpref($_SESSION['ses_userid'], 'show_limit_daten'))
			);

	$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('admin_settings_limits_numberof_yearcal'),
			'type' => 'select',
			'name' => 'sel_cal_jahr_num',
			'values' => array(3, 4, 6, 12),
			'descs' => array('3', '4', '6', '12'),
			'value' => ko_html(ko_get_userpref($_SESSION['ses_userid'], 'cal_jahr_num'))
			);

	$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('admin_settings_view_weekcal_start'),
			'type' => 'text',
			'params' => 'size="10"',
			'name' => 'txt_cal_woche_start',
			'value' => ko_html(ko_get_userpref($_SESSION['ses_userid'], 'cal_woche_start'))
			);
	$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('admin_settings_view_weekcal_stop'),
			'type' => 'text',
			'params' => 'size="10"',
			'name' => 'txt_cal_woche_end',
			'value' => ko_html(ko_get_userpref($_SESSION['ses_userid'], 'cal_woche_end'))
			);

	$frmgroup[$gc]['row'][$rowcounter++]['inputs'][0] = array('type' => '   ');

	$value = ko_get_userpref($_SESSION['ses_userid'], 'daten_monthly_title');
	$frmgroup[$gc]["row"][$rowcounter]["inputs"][0] = array("desc" => getLL("daten_settings_monthly_title"),
			"type" => "select",
			"name" => "sel_monthly_title",
			'values' => array('eventgruppen_id', 'title', 'kommentar', 'both', 'eventgruppen_id_kommentar', 'eventgruppen_id_kommentar2', 'title_kommentar'),
			'descs' => array(getLL('kota_ko_event_eventgruppen_id'), getLL('kota_ko_event_title'), getLL('kota_ko_event_kommentar'), getLL('kota_ko_event_eventgruppen_id').'+'.getLL('kota_ko_event_title'), getLL('kota_ko_event_eventgruppen_id').'+'.getLL('kota_ko_event_kommentar'), getLL('kota_ko_event_eventgruppen_id').'+'.getLL('kota_ko_event_kommentar2'), getLL('kota_ko_event_title').'+'.getLL('kota_ko_event_kommentar')),
			"value" => $value,
			);
	$value = ko_html(ko_get_userpref($_SESSION['ses_userid'], 'daten_title_length'));
	$frmgroup[$gc]["row"][$rowcounter++]["inputs"][1] = array("desc" => getLL("daten_settings_title_length"),
			"type" => "text",
			"name" => "txt_title_length",
			"value" => $value,
			"params" => 'size="10"',
			);

	$value = ko_get_userpref($_SESSION['ses_userid'], 'daten_mark_sunday');
	$frmgroup[$gc]["row"][$rowcounter]["inputs"][0] = array("desc" => getLL("daten_settings_mark_sunday"),
			'type' => 'switch',
			'name' => 'sel_mark_sunday',
			'label_0' => getLL('no'),
			'label_1' => getLL('yes'),
			'value' => $value == '' ? 0 : $value,
			);
	if(ko_module_installed('leute')) {
		$value = ko_get_userpref($_SESSION['ses_userid'], 'show_birthdays');
		if($value == '') $value = 0;
		$frmgroup[$gc]['row'][$rowcounter]['inputs'][1] = array('desc' => getLL('admin_settings_misc_birthdays_cal'),
				'type' => 'switch',
				'name' => 'sel_show_birthdays',
				'label_0' => getLL('no'),
				'label_1' => getLL('yes'),
				'value' => $value == '' ? 0 : $value,
				);
	}
	$rowcounter++;

	$value = ko_get_userpref($_SESSION['ses_userid'], 'daten_no_cals_in_itemlist');
	$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('daten_settings_no_cals_in_itemlist'),
			'type' => 'switch',
			'name' => 'sel_no_cals_in_itemlist',
			'label_0' => getLL('no'),
			'label_1' => getLL('yes'),
			'value' => $value == '' ? 0 : $value,
			);
	if(ko_module_installed('reservation')) {
		$value = ko_get_userpref($_SESSION['ses_userid'], 'daten_show_res_in_tooltip');
		$frmgroup[$gc]['row'][$rowcounter]['inputs'][1] = array('desc' => getLL('daten_settings_show_res_in_tooltip'),
				'type' => 'switch',
				'name' => 'sel_show_res_in_tooltip',
				'label_0' => getLL('no'),
				'label_1' => getLL('yes'),
				'value' => $value == '' ? 0 : $value,
				);
	}
	$rowcounter++;

	$value = ko_get_userpref($_SESSION['ses_userid'], 'daten_rooms_only_future');
	$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('daten_settings_rooms_only_future'),
			'type' => 'switch',
			'name' => 'chk_daten_rooms_only_future',
			'label_0' => getLL('no'),
			'label_1' => getLL('yes'),
			'value' => $value == '' ? 0 : $value,
			);
	$rowcounter++;

	$frmgroup[$gc]['row'][$rowcounter++]['inputs'][0] = array('type' => '   ');

	$value = ko_get_userpref($_SESSION['ses_userid'], 'daten_pdf_show_time');
	$frmgroup[$gc]["row"][$rowcounter]["inputs"][0] = array("desc" => getLL("daten_settings_pdf_show_time"),
			"type" => "select",
			"name" => "sel_pdf_show_time",
			'values' => array('2', '1', '0'),
			'descs' => array(getLL('daten_settings_pdf_show_time_2'), getLL('daten_settings_pdf_show_time_1'), getLL('no')),
			"value" => $value,
			);
	$value = ko_get_userpref($_SESSION['ses_userid'], 'daten_pdf_use_shortname');
	$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('daten_settings_pdf_use_shortname'),
			'type' => 'switch',
			'name' => 'sel_pdf_use_shortname',
			'label_0' => getLL('no'),
			'label_1' => getLL('yes'),
			'value' => $value == '' ? 0 : $value,
			);

	$value = ko_get_userpref($_SESSION['ses_userid'], 'daten_pdf_week_start');
	$monday = date_find_last_monday(date('Y-m-d'));
	$daynames[] = strftime('%A', strtotime($monday));
	for($i=1; $i<7; $i++) {
		$daynames[] = strftime('%A', strtotime(add2date($monday, 'tag', $i, TRUE)));
	}
	$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('daten_settings_pdf_week_start'),
			'type' => 'select',
			'name' => 'sel_pdf_week_start',
			'values' => array(1,2,3,4,5,6,0),
			'descs' => $daynames,
			'value' => $value,
			);
	$value = ko_get_userpref($_SESSION['ses_userid'], 'daten_pdf_week_length');
	$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('daten_settings_pdf_week_length'),
			'type' => 'select',
			'name' => 'sel_pdf_week_length',
			'values' => array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21),
			'descs' => array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21),
			'value' => $value,
			);

	$value = ko_get_userpref($_SESSION['ses_userid'], 'daten_export_show_legend');
	$frmgroup[$gc]['row'][$rowcounter++]['inputs'][0] = array('desc' => getLL('daten_settings_export_show_legend'),
			'type' => 'switch',
			'name' => 'sel_export_show_legend',
			'label_0' => getLL('no'),
			'label_1' => getLL('yes'),
			'value' => $value == '' ? 0 : $value,
			);

	$frmgroup[$gc]['row'][$rowcounter++]['inputs'][0] = array('type' => '   ');

	if($_SESSION['ses_userid'] != ko_get_guest_id()) {
		$value = ko_html(ko_get_userpref($_SESSION['ses_userid'], 'daten_ical_deadline'));
		$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('daten_settings_ical_deadline'),
				'type' => 'select',
				'name' => 'sel_ical_deadline',
				'values' => array(0, -1, -2, -3, -6, -12, -9999),
				'descs' => array(getLL('daten_settings_ical_deadline_0'), getLL('daten_settings_ical_deadline_1'), getLL('daten_settings_ical_deadline_2'), getLL('daten_settings_ical_deadline_3'), getLL('daten_settings_ical_deadline_6'), getLL('daten_settings_ical_deadline_12'), getLL('daten_settings_ical_deadline_9999')),
				'value' => $value,
				);
	}
	if($_SESSION['ses_userid'] != ko_get_guest_id() && $access['daten']['MAX'] > 3) {
		$value = ko_get_userpref($_SESSION['ses_userid'], 'do_mod_email_for_edit_daten');
		if($value == '') $value = 0;
		$frmgroup[$gc]['row'][$rowcounter]['inputs'][1] = array('desc' => getLL('admin_settings_misc_eventemail_3'),
				'type' => 'switch',
				'name' => 'sel_do_mod_email_for_edit_daten',
				'label_0' => getLL('no'),
				'label_1' => getLL('yes'),
				'value' => $value == '' ? 0 : $value,
				);
	}
	$rowcounter++;


	$avalues = $adescs = array();
	$value = ko_get_userpref($_SESSION['ses_userid'], 'daten_ical_description_fields');
	foreach(explode(',', $value) as $v) {
		if(!$v) continue;
		$avalues[] = $v;
		$adescs[] = getLL('kota_ko_event_'.$v);
	}

	$exclude_dbfields = array('id', 'reservationen', 'gs_gid', 'cdate', 'last_change', 'import_id');
	$values = $descs = array();
	$_dbfields = db_get_columns('ko_event');
	foreach($_dbfields as $f) {
		if(in_array($f['Field'], $exclude_dbfields)) continue;
		$values[] = $f['Field'];
		$descs[] = getLL('kota_ko_event_'.$f['Field']) ? getLL('kota_ko_event_'.$f['Field']) : $f['Field'];
	}
	//Add rota teams
	if(ko_module_installed('rota')) {
		$rota_teams = db_select_data('ko_rota_teams', "WHERE 1", '*', 'ORDER BY name ASC');
		if(!isset($access['rota'])) ko_get_access('rota');
		foreach($rota_teams as $rt) {
			$values[] = 'rotateam_'.$rt['id'];
			$descs[] = getLL('kota_ko_event_rotateam_'.$rt['id']);
		}
	}

	$frmgroup[$gc]['row'][$rowcounter++]['inputs'][0] = array('desc' => getLL('daten_settings_ical_description_fields'),
			'type' => 'doubleselect',
			 'js_func_add' => 'double_select_add',
			 'name' => 'sel_ical_description_fields',
			 'values' => $values,
			 'descs' => $descs,
			 'avalues' => $avalues,
			 'avalue' => implode(',', $avalues),
			 'adescs' => $adescs,
			 'params' => 'size="6"',
			 'show_moves' => TRUE,
			);


	//Global settings
	if($access['daten']['MAX'] > 3) {
		$gc++;
		$rowcounter = 0;
		$frmgroup[$gc]['titel'] = getLL('settings_title_global');


		//Settings for group subscriptions
		if(in_array('groups', $MODULES)) {
			$gs_values = $gs_descs = array('');
			$groups = ko_groups_get_recursive(ko_get_groups_zwhere(), TRUE);
			if(!is_array($all_groups)) ko_get_groups($all_groups);
			ko_get_access('groups');
			foreach($groups as $grp) {
				if($access['groups']['ALL'] < 1 && $access['groups'][$grp['id']] < 1) continue;
				$pre = '';
				$mother_line = ko_groups_get_motherline($grp['id'], $all_groups);
				$depth = sizeof($mother_line);
				for($i=0; $i<$depth; $i++) $pre .= '&nbsp;&nbsp;';
				$gs_values[] = $grp['id'];
				$gs_descs[] = $pre.ko_html($grp['name']);
			}

			$value = ko_html(ko_get_setting('daten_gs_pid'));
			$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('daten_settings_gs_pid'),
					'type' => 'select',
					'name' => 'sel_gs_pid',
					'values' => $gs_values,
					'descs' => $gs_descs,
					'value' => $value,
					);

			$roles = db_select_data('ko_grouproles', 'WHERE 1', '*', 'ORDER BY `name` ASC');
			$roles_values = $roles_descs = array('');
			foreach($roles as $r) {
				$roles_values[] = $r['id'];
				$roles_descs[] = $r['name'];
			}
			$value = ko_html(ko_get_setting('daten_gs_role'));
			$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('daten_settings_gs_role'),
					'type' => 'select',
					'name' => 'sel_gs_role',
					'values' => $roles_values,
					'descs' => $roles_descs,
					'value' => $value,
					);

			// list of available roles in group for this event
			$value = ko_html(ko_get_setting('daten_gs_available_roles'));
			$adescs = $avalues = array();
			foreach(explode(',', $value) as $v) {
				if(in_array($v, $roles_values)) {
					$avalues[] = $v;
					foreach($roles_values as $kk => $vv) {
						if($vv == $v) {
							$adescs[] = $roles_descs[$kk];
							continue;
						}
					}
				}
			}
			unset($roles_values[0]);
			unset($roles_descs[0]);
			$frmgroup[$gc]['row'][$rowcounter++]['inputs'][0] = array('desc' => getLL('daten_settings_gs_available_roles'),
				'type' => 'doubleselect',
				'js_func_add' => 'double_select_add',
				'name' => 'sel_gs_available_roles',
				'values' => $roles_values,
				'descs' => $roles_descs,
				'avalue' => $value,
				'avalues' => $avalues,
				'adescs' => $adescs,
				'params' => 'size="7"',
				'show_moves' => TRUE,
			);
		}


		// mandatory fields
		$mandatoryFields = db_get_columns('ko_event');
		$mandatoryFieldsExclude = array('id', 'reservationen', 'rota', 'gs_gid', 'cdate', 'user_id', 'last_change', 'import_id');
		$mandatory_values = array();
		foreach ($mandatoryFields as $mandatoryField) {
			if (!in_array($mandatoryField['Field'], $mandatoryFieldsExclude)) {
				$mandatory_values[] = $mandatoryField['Field'];
				$mandatory_descs[] = (trim(getLL('kota_ko_event_' . $mandatoryField['Field'])) == '' ? $mandatoryField['Field'] : getLL('kota_ko_event_' . $mandatoryField['Field']));
			}
		}
		$mandatory_avalues = explode(',', ko_get_setting('daten_mandatory'));
		foreach($mandatory_avalues as $v_i => $v) {
			$mandatory_adescs[$v_i] = (trim(getLL('kota_ko_event_'.$v)) == '' ? $v : getLL('kota_ko_event_'.$v));
		}
		$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('daten_settings_mandatory'),
			'type' => 'checkboxes',
			'name' => 'sel_mandatory',
			'values' => $mandatory_values,
			'descs' => $mandatory_descs,
			'avalues' => $mandatory_avalues,
			'avalue' => implode(',', $mandatory_avalues),
			'size' => '6',
		);


		$avalues = $adescs = array();
		$value = ko_get_setting('daten_mod_exclude_fields');
		foreach(explode(',', $value) as $v) {
			if(!$v) continue;
			$avalues[] = $v;
			$adescs[] = getLL('kota_ko_event_'.$v);
		}

		$exclude_dbfields = array('id', 'reservationen', 'gs_gid', 'cdate', 'last_change', 'import_id', 'user_id');
		$values = $descs = array();
		$_dbfields = db_get_columns('ko_event');
		foreach($_dbfields as $f) {
			if(in_array($f['Field'], $exclude_dbfields)) continue;
			$values[] = $f['Field'];
			$descs[] = getLL('kota_ko_event_'.$f['Field']) ? getLL('kota_ko_event_'.$f['Field']) : $f['Field'];
		}

		$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('daten_settings_mod_exclude_fields'),
				'type' => 'checkboxes',
				 'name' => 'sel_mod_exclude_fields',
				 'values' => $values,
				 'descs' => $descs,
				 'avalues' => $avalues,
				 'avalue' => implode(',', $avalues),
				 'size' => '6',
				);


		//Access settings
		$value = ko_html(ko_get_setting('daten_access_calendar'));
		$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('admin_settings_options_calendar_access'),
				'type' => 'select',
				'name' => 'sel_calendar_access',
				'params' => 'size="0"',
				'values' => array(1, 0),
				'descs' => array(getLL('admin_settings_options_calendar_access_1'), getLL('admin_settings_options_calendar_access_0')),
				'value' => $value,
				);


		//Moderation settings
		$value = ko_html(ko_get_setting('daten_show_mod_to_all'));
		$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('daten_settings_show_mod_to_all'),
				'type' => 'switch',
				'name' => 'sel_show_mod_to_all',
				'label_0' => getLL('no'),
				'label_1' => getLL('yes'),
				'value' => $value == '' ? 0 : $value,
		);



		if ($_SESSION['ses_userid'] == ko_get_root_id()) {
			// avtivate event program tool
			$value = ko_html(ko_get_setting('activate_event_program'));
			$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('daten_settings_activate_event_program'),
				'type' => 'switch',
				'name' => 'sel_activate_event_program',
				'label_0' => getLL('no'),
				'label_1' => getLL('yes'),
				'value' => $value == '' ? 0 : $value,
			);
		}
	}

	//Allow plugins to add further settings
	hook_form('daten_settings', $frmgroup, '', '');

	//display the form
	$smarty->assign("tpl_titel", getLL("daten_settings_form_title"));
	$smarty->assign("tpl_submit_value", getLL("save"));
	$smarty->assign("tpl_action", "submit_daten_settings");
	$cancel = ko_get_userpref($_SESSION["ses_userid"], "default_view_daten");
	if(!$cancel) $cancel = "show_cal_monat";
	$smarty->assign("tpl_cancel", $cancel);
	$smarty->assign("tpl_groups", $frmgroup);
	$smarty->assign("help", ko_get_help("daten", "daten_settings"));

	$smarty->display('ko_formular.tpl');
}//ko_daten_settings()





function check_daten_entries(&$data) {

	$notifier = koNotifier::Instance();

	ko_get_eventgruppe_by_id($data["eventgruppen_id"], $eventgroup);
	if(!$eventgroup["name"]) {
		$notifier->addError(8);
		return TRUE;
	}

	//Datumsformat überprüfen
	if(!check_datum($data["startdatum"])) {
		$notifier->addError(1);
		return TRUE;
	}

	if($data["enddatum"] != "") {
		if(!check_datum($data["enddatum"])) {
			$notifier->addError(1);
			return TRUE;
		}
		//Events longer than a day without endtime must be all day events
		if($data["enddatum"] != $data["startdatum"] && ($data["endzeit"] == "" || !check_zeit($data["endzeit"])) ) {
			$data["startzeit"] = $data["endzeit"] = "";
		}

		//Startdatum < Enddatum
		$date_s = explode(".", $data["startdatum"]);
		$date_e = explode(".", $data["enddatum"]);
		if( (int)($date_s[2] . str_to_2($date_s[1]) . str_to_2($date_s[0])) > (int)($date_e[2] . str_to_2($date_e[1]) . str_to_2($date_e[0])) ) {
			$notifier->addError(4);
			return TRUE;
		};
	}//if(data["enddatum"] != "")

	//Zeit überprüfen
	if(trim($data["startzeit"]) == "" || !check_zeit($data["startzeit"])) {
		//if first time is empty, set second one empty as well (all day event)
		$data["endzeit"] = "";
	} else {
		if($data["endzeit"] != "") {
			if(!check_zeit($data["endzeit"])) {
				$notifier->addError(2);
				return TRUE;
			}
			//Bei eintägigem Anlass muss die Endzeit grösser sein als die Startzeit
			$time_s1 = str_replace(":", "", $data["startzeit"]);
			$time_s2 = str_replace(":", "", $data["endzeit"]);
			if((trim($data["enddatum"]) == "" || $data["startdatum"] == $data["enddatum"]) && (int)$time_s1 > (int)$time_s2) {
				$notifier->addError(2);
				return TRUE;
			};
		}
	}

	//check for all the mandatory fields
	foreach(explode(",", ko_get_setting("daten_mandatory")) as $man) {
		if($man && !$data[$man]) {
			$notifier->addError(12);
			return TRUE;
		}
	}

	return FALSE;
}//check_daten_entries()





/**
  * Display the calendar div which will be filled by fullCalendar JS
	*/
function ko_daten_calendar() {
	global $ko_path;

	//Add the link to the year view
	$code  = '';
	$code .= '<div id="ko_calendar">';
	$code .= '<div class="fc-mwselect">';
	$code .= '<div name="mwselect" id="mwselect">';
	$code .= ko_calendar_mwselect($_SESSION['cal_view']);
	$code .= '</div>';
	$code .= '<a href="index.php?action=show_cal_jahr"><img src="'.$ko_path.'images/cal_jahr.gif" border="0" title="'.getLL('daten_cal_year').'"></a>';
	$code .= '</div>';
	$code .= '</div>';

	//Add PDF link
	$code .= '<table style="margin-left:12px" cellspacing="0" cellpadding="3">';
	$code .= '<tr><td style="border-left-style:solid;border-left-width:1px">&nbsp;</td></tr>';
	$code .= '<tr><td style="border-left-style:solid;border-left-width:1px;border-bottom-width:1px;border-bottom-style:solid;">';
	$code .= '<a href="" onclick="sendReq(\'inc/ajax.php\', \'action\', \'pdfcalendar\', show_box); return false;">';
	$code .= '<img src="'.$ko_path.'images/create_pdf.png" border="0" />&nbsp;'.getLL("res_list_footer_pdf_label").'</a>';
	$code .= '<span name="daten_pdf_link" id="daten_pdf_link">&nbsp;</span>';
	$code .= '</td></tr>';
	$code .= '</table>';

	print $code;
}//ko_daten_calendar()







function ko_daten_cal_jahr($num=6, $start=1, $output=TRUE) {
	global $smarty, $ko_path;
	global $access;

	//Heute
	get_heute($h_tag, $h_monat, $h_jahr);
	$j = $_SESSION["cal_jahr_jahr"] ? $_SESSION["cal_jahr_jahr"] : $h_jahr;
	$t = 1;
	$m = $start;
	unset($day);

	//Monate
	unset($cal_month);
	for($i = $start; $i < ($start+$num); $i++) {
		$ii = $i > 12 ? ($i-12) : $i;
		$jj = ($ii != $i) ? ($j+1) : $j;
		$cal_month[] = array("code" => (str_to_2($ii)."-".$jj), "name" => strftime("%B", mktime(1,1,1, $i, 1, $j)));
	}
	$next = add2date("$t.$m.$j", "monat", $num);
	$prev = add2date("$t.$m.$j", "monat", "-$num");
	$today_start = 1;
  while( ((int)$today_start+$num-1) < $h_monat) {
    $today_start += $num;
  }

	$end_code = $next[2].str_to_2($next[1]).str_to_2($next[0]);

	$tpl_prev_link = "javascript:sendReq('../daten/inc/ajax.php', 'action,set_year,set_start,sesid', 'setjahr,".$prev[2].",".(int)$prev[1].",".session_id()."', do_element);";
	$tpl_next_link = "javascript:sendReq('../daten/inc/ajax.php', 'action,set_year,set_start,sesid', 'setjahr,".$next[2].",".(int)$next[1].",".session_id()."', do_element);";
	$tpl_today_link = "javascript:sendReq('../daten/inc/ajax.php', 'action,set_year,set_start,sesid', 'setjahr,$h_jahr,$today_start,".session_id()."', do_element);";
	

	$smarty->assign("tpl_cal_month", $cal_month);
	$smarty->assign("tpl_cal_month_width", (int)(90/$num));
	$smarty->assign("tpl_cal_titel", strftime("%Y", mktime(1,1,1, 1,1,$j)));

	$smarty->assign("tpl_prev_link", $tpl_prev_link);
	$smarty->assign("tpl_next_link", $tpl_next_link);
	$smarty->assign("tpl_today_link", $tpl_today_link);


	//Termingruppen-Namen
	ko_get_eventgruppen($gruppen, '', "AND `type` != '1'");
	foreach($gruppen as $g_i => $g) {
		if($access['daten'][$g_i] < 1 || !in_array($g_i, $_SESSION["show_tg"])) {
      unset($gruppen[$g_i]);
      continue;
    }
		$day[$g_i]["name"] = ko_html($g["name"]);
		$day[$g_i]["tip"] = ko_html2($g["name"]);
	}

	//Permanente Filter anwenden
	apply_daten_filter($z_where, $z_limit, 'immer', 'immer');

	//Termine einfüllen
	$day_code = $j.str_to_2($m).str_to_2($t);
	while($day_code < $end_code) {
		//Wochentag vorausfüllen
		$wt = strftime("%w", mktime(1,1,1, $m, $t, $j));
		if($wt == 0) $style = "background:#cccccc;";
		else if($wt == 6) $style = "background:#dddddd;";
		else $style = "";
		foreach($gruppen as $g_i => $g) {
			$day[(int)$g_i]["events"][$m]["days"][$t]["style"] = $style;
		}
		
		//Termine einfüllen
		ko_get_events_by_date($t, $m, $j, $dates, $z_where);
		if(sizeof($dates) > 0) {
			foreach($gruppen as $g_i => $g) {
				$day_text = "";
				foreach($dates as $date) {
					if((int)$date["eventgruppen_id"] == (int)$g_i) {

						$desc = "";
						if($date["startzeit"] == "00:00:00" && $date["endzeit"] == "00:00:00") {
							$desc .= getLL("time_all_day");
						}
						else if($date["startdatum"] != $date["enddatum"]) {  //Mehrtägige Termine
							if($t == substr($date["startdatum"], 8, 2) && $m == substr($date["startdatum"], 5, 2))
								$desc .= getLL("time_from")." ".substr($date["startzeit"], 0, -3);
							else if($t == substr($date["enddatum"], 8, 2) && $m == substr($date["enddatum"], 5, 2))
								$desc .= getLL("time_to")." ".substr($date["endzeit"], 0, -3);
							else
								$desc .= getLL("time_all_day");
						} else {  //eintägig
							$desc .= ($date["startzeit"] != "00:00:00" ? substr($date["startzeit"], 0, -3) : "") . (($date["endzeit"] != "00:00:00") ? ("-" . substr($date["endzeit"], 0, -3)) : "");
						}
						$day_text .= ($desc != "") ? "<br /><b>- ".$desc."</b>" : "<br />";

						$day_text .= $date["title"] ? ": &quot;".ko_html2($date["title"]).'&quot;' : "";
					}
				}//foreach(dates)

				if($day_text) {
					$day[(int)$g_i]["events"][$m]["days"][$t]["tip"]  = "<b>".strftime("%A, %d. %B %Y", mktime(1,1,1, $m,$t,$j))."</b><br />".ko_html2($g["name"]);
					$day[(int)$g_i]["events"][$m]["days"][$t]["tip"] .= $day_text;
					$day[(int)$g_i]["events"][$m]["days"][$t]["style"] = "background:#".($g["farbe"] ? $g["farbe"] : "999999");
				}
			}//foreach(gruppen)
		}
		unset($dates);

		//Tag inkrementieren
		$datum = add2date("$t.$m.$j", "tag", 1);
		$t = (int)$datum[0];
		$m = (int)$datum[1];
		$j = (int)$datum[2];
		$day_code = $j.str_to_2($m).str_to_2($t);
	}//while(day_code < end_code)

	//LL-Values
	$smarty->assign("label_cal_year", getLL("daten_cal_year"));
	$smarty->assign("label_cal_month", getLL("daten_cal_month"));
	$smarty->assign("label_cal_week", getLL("daten_cal_week"));
	$smarty->assign("label_item", getLL("daten_cal_event"));
	$smarty->assign("label_today", getLL("time_today"));

	$smarty->assign("tpl_day", $day);

	//PDF-Export-Link anzeigen:
	$button_code .= '&nbsp;<a href="" onclick="sendReq(\'../daten/inc/ajax.php\', \'action\', \'pdfcalendar\', show_box); return false;">';
	$button_code .= '<img src="'.$ko_path.'images/create_pdf.png" border="0" />&nbsp;'.getLL("res_list_footer_pdf_label").'</a>';
	$button_code .= '<span name="daten_pdf_link" id="daten_pdf_link">&nbsp;</span>';
	$list_footer = $smarty->get_template_vars('list_footer');
	$list_footer[] = array("label" => "", "button" => $button_code);
	$smarty->assign("show_list_footer", TRUE);
	$smarty->assign("list_footer", $list_footer);

	$smarty->assign('warning', kota_filter_get_warntext('ko_event'));

	if($output) {
		$smarty->display('ko_cal_jahr.tpl');
	} else {
		print $smarty->fetch('ko_cal_jahr.tpl');
	}
}//ko_daten_cal_jahr()






function do_del_termin($del_id) {
	global $access;

  ko_get_event_by_id($del_id, $del_event);
	if($access['daten'][$del_event['eventgruppen_id']] < 2) return FALSE;
  ko_get_eventgruppe_by_id($del_event["eventgruppen_id"], $del_eventgruppe);

	//Moderated events may only be deleted directly with edit rights
	if($del_eventgruppe["moderation"] > 0 && $access['daten'][$del_event['eventgruppen_id']] < 3) {
		//Moderated delete
		$mod_event = $del_event;
		unset($mod_event["id"]);
		$mod_event["_event_id"] = $del_id;
		$mod_event["_delete"] = 1;
		$mod_event["resitems"] = $mod_event["reservationen"]; unset($mod_event["reservationen"]);

		ko_daten_store_moderation(array($mod_event));
		return "mod";
	}

	//Really delete event
	else {
		//Die verbundenen Reservationen löschen
		if($del_event["reservationen"]) {
			$del_res = explode(",", $del_event["reservationen"]);
			foreach($del_res as $d) {
				ko_get_res_by_id($d, $r_);
				$r = $r_[$d];
				ko_get_resitem_by_id($r["item_id"], $ri);
				$log_message2  = $d." (Event-Res ".$del_id."): ".$ri[$r["item_id"]]["name"].", ";
				$log_message2 .= sql2datum($r["startdatum"]).($r["startdatum"]!=$r["enddatum"]?"-".sql2datum($r["enddatum"]):"").", ";
				$log_message2 .= substr(format_userinput($r["startzeit"], "text"),0,-3)."-".substr(format_userinput($r["endzeit"], "text"),0,-3);
				$log_message2 .= ', "'.format_userinput($r["zweck"], "text").'", ';
				$log_message2 .= format_userinput($r["name"], "text")." (".format_userinput($r["email"], "text").", ".format_userinput($r["telefon"], "text").")";

				db_delete_data("ko_reservation", "WHERE `id` = '$d'");

				ko_log("delete_res", $log_message2);
			}//foreach(del_res as d)
		}//if(del_event[reservationen])

		//Delete group with group subscriptions if no members
		ko_daten_gs_delete_group($del_event);

		//Delete event itself
		db_delete_data("ko_event", "WHERE `id` = '$del_id'");

		//Delete mod entries for this event
		db_delete_data("ko_event_mod", "WHERE `_event_id` = '$del_id'");

		//Send notification
		ko_daten_send_notification($del_event, 'delete');

		ko_log_diff('delete_termin', $del_event);
		return 'del';
	}
}//do_del_termin()



/**
  * Finds and deletes empty calendars (withouth any event groups)
	* Must be called after editing and deleting event groups
	*/
function ko_delete_empty_calendars() {
	ko_get_event_calendar($cals);
	foreach($cals as $id => $cal) {
		$count = db_get_count("ko_eventgruppen", "id", "AND `calendar_id` = '$id'");
		if($count == 0) db_delete_data("ko_event_calendar", "WHERE `id` = '$id'");
	}
}//ko_delete_empty_calendars()



function ko_daten_store_moderation($data) {
	$txt = array();
	$egs = db_select_data("ko_eventgruppen", "WHERE 1=1", "*");

	foreach($data as $event) {
		$eg = $egs[$event["eventgruppen_id"]];

		unset($event["id"]);
		unset($event['import_id']);
		// Fow now: drop event program for moderated events
		unset($event['programEntries']); // TODO : maybe support program entries in moderated events
		$event["_user_id"] = $_SESSION["ses_userid"];
		$event["_crdate"] = strftime("%Y-%m-%d %H:%M:%S", time());

		db_insert_data("ko_event_mod", $event);

		if($eg["moderation"] == 2) {
			$txt = ko_daten_infotext($event);
			$mod_txt[$eg["id"]] .= $txt."\n\n";
			if($event["_delete"] == 1) {
				$subject = getLL("daten_email_mod_delete_mod_subject");
			} else if($event["_event_id"] != 0) {
				$subject = getLL("daten_email_mod_edit_mod_subject");
			} else {
				$subject = getLL("daten_email_mod_subject");
			}
		}
	}

	//Get email of currently logged in user to use it as sender for the mails to the moderators
	if($_SESSION["ses_userid"] != ko_get_guest_id()) {
		$p = ko_get_logged_in_person();
		if($p['email']) {
			if($p['vorname'] || $p['nachname']) {
				$sender_email = array($p['email'] => trim($p['vorname'].' '.$p['nachname']));
			} else {
				$sender_email = array($p['email'] => $_SESSION['ses_username']);
			}
		} else {
			$sender_email = ko_get_setting('info_email');
		}
	} else {
		$sender_email = ko_get_setting('info_email');
	}

	//Send email to moderators
	$done = array();
	foreach($mod_txt as $gid => $txt) {
		if(!$txt || !$gid) continue;
		$mailtext = str_replace("[DATA]", "\n\n".$txt, getLL("daten_email_mod_text"));
		$mods = ko_get_moderators_by_eventgroup($gid);
		foreach($mods as $mod) {
			if(!$mod['email'] || in_array($mod['email'], $done)) continue;

			ko_send_mail($sender_email, $mod['email'], '[kOOL] ' . $subject, ko_emailtext($mailtext));
			$done[] = $mod['email'];
		}
	}
}//ko_daten_store_moderation()




function ko_daten_update_event($id, &$data) {
	global $access, $EVENTS_SHOW_RES_FIELDS;

	$error = FALSE;
	$ok = TRUE;

	$userid = $data["_user_id"] ? $data["_user_id"] : $_SESSION["ses_userid"];

	ko_get_event_by_id($id, $event);
	ko_get_eventgruppe_by_id($data["eventgruppen_id"], $eg);
	$event["startzeit"] = sql_zeit($event["startzeit"]);
	$event["endzeit"] = sql_zeit($event["endzeit"]);

	$current_res = db_select_data("ko_reservation", "WHERE `id` IN ('".implode("','", explode(",", $event["reservationen"]))."')", "*");
	if(sizeof($current_res) > 0 || $data["resitems"] != "") {
		//Get access rights for reservations
		ko_get_access('reservation');

		//Check for changes which force an update for the reservations for this event
		$update_res = array();
		foreach(explode(",", $event["reservationen"]) as $iid) {
			if(!$iid) continue;
			$r = db_select_data('ko_reservation', 'where id = ' . $iid, '*', '', '', TRUE, TRUE);
			if($r["startdatum"] != $data['res_startdatum']) $update_res["startdatum"] = $data['res_startdatum'];
			if($r["enddatum"] != $data['res_enddatum']) $update_res["enddatum"] = $data['res_enddatum'];
		}
		if($event["eventgruppen_id"] != $data["eventgruppen_id"] || $event["title"] != $data["title"]) {
			$update_res["zweck"] = $data["title"].' ('.$eg["name"].')';
		}
		foreach($EVENTS_SHOW_RES_FIELDS as $f) {
			if($data['res_'.$f]) $update_res[$f] = $data['res_'.$f];
		}

		$store_res = $store_mod = $upd_res = array();
		$event_res = array();
		foreach(explode(",", $data["resitems"]) as $iid) {
			$found = FALSE;
			foreach($current_res as $cid => $cr) {
				if($cr["item_id"] == $iid) {
					$found = TRUE;
					$event_res[] = $cid;
					//Overlapping check (Don't move event if reservations can not be stored)
					if(FALSE === ko_res_check_double($iid, $update_res['startdatum'], $update_res['enddatum'], $update_res['startzeit'], $update_res['endzeit'], $double_error_txt, $cid)) {
						$ok = FALSE;
						koNotifier::Instance()->addError(4, '', array($double_error_txt), 'reservation');
						$error = TRUE;
					}
					$upd_res[$cid] = $update_res;
					unset($current_res[$cid]);
				}
			}
			if(!$found) {
				if($access['reservation'][$iid] < 2) continue;
				//Add new reservation
				$p = ko_get_logged_in_person();
				$res_data = array("item_id" => $iid,
						"startdatum" => $data["startdatum"],
						"enddatum" => $data["enddatum"],
						"zweck" => $data["title"].' ('.$eg['name'].')',
						"name" => $p["vorname"]." ".$p["nachname"],
						"email" => $p["email"],
						"telefon" => $p["telp"]);
				foreach($EVENTS_SHOW_RES_FIELDS as $f) {
					if($data['res_'.$f]) $res_data[$f] = $data['res_'.$f];
				}
				$resitem = db_select_data("ko_resitem", "WHERE `id` = '$iid'", "*", "", "", TRUE);
				//Check for res colision
				ko_res_check_double($iid, $res_data["startdatum"], $res_data["enddatum"], $res_data["startzeit"], $res_data["endzeit"], $double_error_txt);
				if($double_error_txt) {
					koNotifier::Instance()->addError(4, '', array($double_error_txt), 'reservation');
					$error = TRUE;
				} else {
					//Check for moderation
					if($resitem["moderation"] > 0 && $access['reservation'][$iid] < 4) {
						$res_data["_event_id"] = $id;
						$store_mod[] = $res_data;
					} else {
						unset($res_data["_event_id"]);
						$store_res[] = $res_data;
					}
				}
			}//if(!found)
		}//foreach(resitems)

		//Store new reservations
		if($ok) {
			if(sizeof($store_res) > 0) {
				$send_user_email = ko_get_userpref($userid, 'do_res_email') != 0;
				$new_ids = ko_res_store_reservation($store_res, $send_user_email);
				$event_res = array_merge($event_res, $new_ids);
			}
			if(sizeof($store_mod) > 0) {
				ko_res_store_moderation($store_mod);
			}
			if(sizeof($upd_res) > 0) {
				foreach($upd_res as $cid => $update_res) {
					db_update_data('ko_reservation', "WHERE `id` = '$cid'", $update_res);
				}
			}
			//Delete reservations not selected anymore
			if(sizeof($current_res) > 0) {
				foreach($current_res as $cr) db_delete_data("ko_reservation", "WHERE `id` = '".$cr["id"]."'");
			}

			$data["reservationen"] = implode(",", $event_res);
		}
	}//if(handle_res)

	//Update event itself
	if($ok) {
		unset($data['resitems']); unset($data['id']);
		foreach($data as $key => $value) {
			if(substr($key, 0, 1) == '_') unset($data[$key]);
		}
		foreach($EVENTS_SHOW_RES_FIELDS as $f) {
			unset($data['res_'.$f]);
		}

		//Group subscription
		if(isset($data['gs_gid']) && ko_get_setting('daten_gs_pid')) {
			if($data['gs_gid'] == 1 && $event['gs_gid'] == '') {  //Newly selected
				$data['gs_gid'] = ko_daten_gs_get_gid_for_event($event);
			} else if($event['gs_gid'] != '' && $data['gs_gid'] == 0) {  //Deselected
				$data['gs_gid'] = '';
				ko_daten_gs_delete_group($event);
			} else if($data['gs_gid'] == 1 && $event['gs_gid'] != '') {  //Still selected with no change
				unset($data['gs_gid']);  //Unset gs_gid so it won't be updated and set to '1'
			}
		}

		//Add last_change
		$data['last_change'] = date('Y-m-d H:i:s');

		// unset res_dates
		if (isset($data['res_startdatum'])) unset($data['res_startdatum']);
		if (isset($data['res_enddatum'])) unset($data['res_enddatum']);

		db_update_data("ko_event", "WHERE `id` = '$id'", $data);
		unset($data['last_change']);

		//Call KOTA post
		$ids = array($id);
		$columns = array_keys($data);
		$do_save = 1;
		if(function_exists('kota_post_ko_event')) {
			eval("kota_post_ko_event(\$ids, \$columns, \$event, \$do_save);");
		}
		hook_kota_post('ko_event', array('table' => 'ko_event', 'ids' => $ids, 'columns' => $columns, 'old' => $event, 'do_save' => $do_save));

		//Send notification
		ko_daten_send_notification($data, 'update', $event);

		//Log-Meldung erstellen
		ko_log_diff("edit_termin", array_merge(array("name" => $eventgroup["name"]), $data), $event);
	}

	return $error;
}//ko_daten_update_event()





function ko_daten_infotext($data) {
	$txt = "";
	$list = kota_get_list($data, "ko_event");
	foreach($list as $key => $value) {
		if($key == getLL("kota_ko_event_enddatum") || $key == getLL("kota_ko_event_endzeit")) continue;
		if($value) $txt .= "$key: ".strip_tags(ko_unhtml($value))."\n";
	}

	return $txt;
}//ko_daten_infotext()



function ko_daten_store_event(&$data) {
	global $access, $EVENTS_SHOW_RES_FIELDS;

	$error = false;
	//Get access rights for reservations (new reservations are done with current user, which is the moderator for moderations)
	ko_get_access('reservation');

	$resitems = db_select_data("ko_resitem", "WHERE 1=1", "*");
	$egs = db_select_data("ko_eventgruppen", "WHERE 1=1", "*");


	$txt3 = array();
	foreach($data as $e_id => $event) {
		$last_userid = $userid;
		//Use the userid of the user given in data
		//If this is from a moderated event, then the reservations will be done as the original user and not the moderator
		$userid = $event["_user_id"] ? $event["_user_id"] : $_SESSION["ses_userid"];

		//Only get settings again if this event is done by another userid
		if($userid != $last_userid) {
			//Get person data for user who created this event (currently logged in or the one who created the mod event)
			$p = ko_get_logged_in_person($userid);
			//Get user setting to send emails for reservations
			$send_user_email = ko_get_userpref($userid, 'do_res_email') != 0;
		}

		$eg = $egs[$event["eventgruppen_id"]];

		//Prepare reservations
		$event_res = $res_store = $res_mod = array();
		if($event["resitems"]) {
			//set data for reservations

			$res_data["startdatum"] = $event["res_startdatum"];
			$res_data["enddatum"] = $event["res_enddatum"];
			$res_data["zweck"] = $event["title"].' ('.$eg['name'].')';
			$res_data["name"] = $p["vorname"]." ".$p["nachname"];
			$res_data["email"] = $p["email"];
			$res_data["telefon"] = $p["telp"];
			$res_data["user_id"] = $userid;  //Store userid also for reservations

			foreach($EVENTS_SHOW_RES_FIELDS as $f) {
				if(isset($event['res_'.$f]) && !in_array($event['res_'.$f], array('', '0'))) $res_data[$f] = $event['res_'.$f];
			}

			$res_mod = $res_store = array();
			foreach(explode(",", $event["resitems"]) as $r) {
				if(!$r) continue;
				$res_data = array("item_id" => $r)+$res_data;

				//Auf Doppelbelegung prüfen
				$do_res = TRUE;
				ko_res_check_double($r, $res_data["startdatum"], $res_data["enddatum"], $res_data["startzeit"], $res_data["endzeit"], $double_error_txt);
				if($double_error_txt) {
					koNotifier::Instance()->addError(4, '', array("<br />".$double_error_txt), 'reservation');
					$do_res = FALSE;
					$error = true;
				}

				if($do_res) {
					if($resitems[$r]["moderation"] == 0 || $access['reservation'][$r] > 3) {
						$res_store[] = $res_data;
					} else {
						$res_mod[] = $res_data;
					}
				}//if(do_res)
			}//foreach(res_s as r)

			//Store reservations for this event
			if(sizeof($res_store) > 0) {
				$event_res = ko_res_store_reservation($res_store, $send_user_email);
			}
		}//if(sizeof(resitems))

		//Unset values not needed for ko_event (might be from ko_event_mod or just from the submitted form)
		$unset_keys = array("resitems", "id");
		foreach($event as $key => $value) {
			if(substr($key, 0, 1) == "_" || in_array($key, $unset_keys)) unset($event[$key]);
		}
		foreach($EVENTS_SHOW_RES_FIELDS as $f) unset($event['res_'.$f]);
		$event["reservationen"] = implode(",", $event_res);

		//Group subscription
		if(ko_get_setting('daten_gs_pid') && ($event['gs_gid'] == 1 || substr($event['gs_gid'], 0, 4) == 'COPY')) {
			$event['gs_gid'] = ko_daten_gs_get_gid_for_event($event);
		}

		//Add creation date and last update
		$event['cdate'] = date('Y-m-d H:i:s');
		$event['last_change'] = date('Y-m-d H:i:s');
		$event['user_id'] = $userid;

		// unset res_dates
		if (isset($event['res_startdatum'])) unset($event['res_startdatum']);
		if (isset($event['res_enddatum'])) unset($event['res_enddatum']);

		// unset programEntries
		if (isset($event['programEntries'])) {
			$programEntries = $event['programEntries'];
			unset($event['programEntries']);
		}

		//Store new event
		$new_id = db_insert_data("ko_event", $event);
		unset($data['cdate']); unset($data['last_change']);
		// set pid of program entries and insert them into db
		if (isset($programEntries)) {
			foreach ($programEntries as $k => $programEntry) {
				$programEntry['pid'] = $new_id;
				db_insert_data('ko_event_program', $programEntry);
			}
		}

		//Call KOTA post
		$ids = array($new_id);
		$columns = array_keys($event);
		$old = array();
		$do_save = 1;
		if(function_exists('kota_post_ko_event')) {
			eval("kota_post_ko_event(\$ids, \$columns, \$old, \$do_save);");
		}
		hook_kota_post('ko_event', array('table' => 'ko_event', 'ids' => $ids, 'columns' => $columns, 'old' => $old, 'do_save' => $do_save));

		if($egs[$event['eventgruppen_id']]['moderation'] > 0 && $access['daten'][$event['eventgruppen_id']] == 3) {
			$txt3[$event["eventgruppen_id"]] .= ko_daten_infotext($event)."\n\n";
		}

		//Store reservation which need moderation (only after new events has been stored, so _event_id may be set
		if(sizeof($res_mod) > 0) {
			//Add event id for these reservations, so they will be associated with the event after moderation
			foreach($res_mod as $res_id => $res) $res_mod[$res_id]["_event_id"] = $new_id;
			ko_res_store_moderation($res_mod);
		}

		//Send notification
		ko_daten_send_notification($event, 'new');

		//Log-Meldung
		ko_log_diff("new_termin", array_merge(array("id" => $new_id), $event));

		//Store ID in original events array. This way plugins will have access to the ID of the newly created event.
		$data[$e_id]['id'] = $new_id;
	}//foreach(data as event)

	//Mail for moderator, if user with access level 3 has made a reservation of a moderated object
	if(sizeof($txt3) > 0) {
		foreach($txt3 as $gid => $txt) {
			$mailtext = sprintf(getLL("daten_email_mod3_text"), ("\n\n".$txt));
			$mods = ko_get_moderators_by_eventgroup($gid);
			foreach($mods as $mod) {
				//Check user_pref for this moderator
				if(ko_get_userpref($mod["id"], "do_mod_email_for_edit_daten") == 1) {
					ko_send_mail(ko_get_setting("info_email"), $mod['email'], '[kOOL] ' . getLL('daten_email_mod3_subject'), ko_emailtext($mailtext));
				}
			}
		}
	}

	return $error;
}//ko_daten_store_event()





/**
  * Send notification for event updates if set in the given eventgroup
	* @param array data: new event
	* @param string mode: new, update or delete used for message
	* @param array old: old event data. Used for mode==update to show differences
	*/
function ko_daten_send_notification($data, $mode, $old='') {
	global $DATETIME;

	//Get event if only id is given (used after multiediting)
	if(!is_array($data)) {
		$id = $data;
		ko_get_event_by_id($id, $data);
	}

	//Check eventgroup for notification
	ko_get_eventgruppe_by_id($data['eventgruppen_id'], $eg);
	if($eg['notify'] == '') return;

	//Build SQL to find the persons for the selected groups
	$groups = explode(',', $eg['notify']);
	$sql = '';
	foreach($groups as $gid) {
		if(!$gid) continue;
		$sql .= ' `groups` REGEXP \''.$gid.'\' OR ';
	}
	$sql = substr($sql, 0, -3);
	if(!$sql) return;

	//Get people from DB to send notification to
	ko_get_leute($p, "AND ($sql) AND `deleted` = '0' AND `hidden` = '0'");
	if(sizeof($p) == 0) return;
	$to = array();
	foreach($p as $person) {
		if(FALSE === ko_get_leute_email($person, $email)) continue;
		$to = array_merge($to, $email);
	}
	if(sizeof($to) == 0) return;

	//Send email
	$subject = getLL('daten_notify_subject').': '.trim($eg['name']).' '.strftime($DATETIME['dmy'], strtotime($data['startdatum']));
	$message = getLL('daten_notify_message_'.$mode)."\n\n";
	if($mode == 'update' && is_array($old)) {
		$list_new = kota_get_list($data, 'ko_event');
		$list_old = kota_get_list($old, 'ko_event');
		$diff = '';
		foreach($list_old as $k => $v) {
			if($k == getLL('kota_ko_event_enddatum') || $k == getLL('kota_ko_event_endzeit')) continue;
			if($list_new[$k] != $v) $diff .= $k.': '.$v.' -> '.$list_new[$k]."\n";
		}
		if($diff) $message .= $diff."\n";
	}
	$message .= ko_daten_infotext($data);

	foreach($to as $recipient) {
		ko_send_mail(ko_get_setting('info_email'), $recipient, $subject, $message);
	}
}//ko_daten_send_notification()





function ko_daten_ical_links() {
	global $ICAL_URL, $BASE_URL, $access;

	if($_SESSION['ses_userid'] == ko_get_guest_id()) return FALSE;
	if(!defined('KOOL_ENCRYPTION_KEY') || trim(KOOL_ENCRYPTION_KEY) == '') {
		print 'ERROR: '.getLL('error_daten_9');
		return FALSE;
	}

	ko_get_login($_SESSION['ses_userid'], $login);
	$base_link = ($ICAL_URL ? $ICAL_URL : $BASE_URL.'ical/').'?user='.md5($_SESSION['ses_userid'].$login['password'].KOOL_ENCRYPTION_KEY);

	$help  = ko_get_help('daten', 'ical_links');
	$help2 = ko_get_help('daten', 'ical_links2');

	$content = '';
	$content .= '<div class="ical-links">';
	$content .= '<h1>'.getLL('daten_ical_links_title').($help['show'] == 1 ? '&nbsp;'.$help['link'] : '').'</h1>';
	$content .= '<p>'.getLL('daten_ical_links_description').'</p>';

	$content .= '<h4>'.getLL('daten_ical_links_title_all').($help2['show'] == 1 ? '&nbsp;'.$help2['link'] : '').'</h4>';
	$content .= ko_get_ical_link($base_link, getLL('all')).'<br />';

	$link = $base_link.'&egs='.implode(',', $_SESSION['show_tg']);
	//Add current KOTA filter if set
	if(sizeof($_SESSION['kota_filter']['ko_event']) > 0) {
		foreach($_SESSION['kota_filter']['ko_event'] as $k => $v) {
			if(!$v) continue;
			$link .= '&kota_'.$k.'='.urlencode($v);
		}
	}
	$content .= ko_get_ical_link($link, getLL('daten_ical_links_current')).'<br />';

	$content .= '<h4>'.getLL('daten_ical_links_title_presets').($help2['show'] == 1 ? '&nbsp;'.$help2['link'] : '').'</h4>';
	$itemset = array_merge((array)ko_get_userpref('-1', '', 'daten_itemset', 'ORDER BY `key` ASC'), (array)ko_get_userpref($_SESSION['ses_userid'], '', 'daten_itemset', 'ORDER BY `key` ASC'));
	foreach($itemset as $i) {
		if(!$i['key'] || !$i['value']) continue;
		$label = ($i['user_id'] == '-1' ? getLL('itemlist_global_short').$i['key'] : $i['key']);
		$content .= ko_get_ical_link($base_link.'&egs=p'.$i['id'], $label).'<br />';
	}

	$content .= '<h4>'.getLL('daten_ical_links_title_single').($help2['show'] == 1 ? '&nbsp;'.$help2['link'] : '').'</h4>';
	$cals = db_select_data('ko_event_calendar', 'WHERE 1', '*', 'ORDER BY name ASC');
	foreach($cals as $cal) {
		if($access['daten']['ALL'] < 1 && $access['daten']['cal'.$cal['id']] < 1) continue;
		$content .= '<div style="font-weight: bold; margin-top: 8px;">';
		$content .= ko_get_ical_link($base_link.'&egs=c'.$cal['id'], $cal['name']).'</div>';

		$egs = db_select_data('ko_eventgruppen', "WHERE `type` != '1' AND `calendar_id` = '".$cal['id']."'", '*', 'ORDER BY name ASC');
		foreach($egs as $id => $eg) {
			if($access['daten']['ALL'] < 1 && $access['daten'][$id] < 1) continue;
			if(!$eg['id']) continue;
			$content .= ko_get_ical_link($base_link.'&egs='.$eg['id'], $eg['name']).'<br />';
		}
	}

	$content .= '<br />';
	$egs = db_select_data('ko_eventgruppen', "WHERE `type` != '1' AND `calendar_id` = ''", '*', 'ORDER BY name ASC');
	foreach($egs as $id => $eg) {
		if($access['daten']['ALL'] < 1 && $access['daten'][$id] < 1) continue;
		if(!$eg['id']) continue;
		$content .= ko_get_ical_link($base_link.'&egs='.$eg['id'], $eg['name']).'<br />';
	}

	$content .= '</div>';

	print $content;
}//ko_daten_ical_links()




function ko_daten_gs_get_gid_for_event($event) {
	global $onload_code, $BASE_URL;

	//Get top group under which subscription groups should be created
	$pid = ko_get_setting('daten_gs_pid');

	$year = substr($event['enddatum'], 0, 4);  //Year

	//Get all subgroups of pid
	$subgroups = db_select_data('ko_groups', "WHERE `pid` = '$pid'");
	$found = FALSE;
	$new_pid = '';
	foreach($subgroups as $sg) {
		if($sg['name'] == $year) {
			$found = TRUE;
			$new_pid = $sg['id'];
		}
	}
	//Create new subgroup for this year if none was found
	if(!$found) {
		$new_pid = zerofill(db_insert_data('ko_groups', array('pid' => $pid, 'name' => $year, 'type' => '1', 'crdate' => date('Y-m-d H:i:s'))), 6);
	}
	if(!$new_pid) return '';

	//Get role from setting
	$roleid = ko_get_setting('daten_gs_role');

	// get other available roles from setting
	$avalRoles = explode(',', ko_get_setting('daten_gs_available_roles'));

	// merge role for members with available roles in group
	$userWantedRoles = array_unique(array_merge($avalRoles, array($roleid)));
	foreach ($userWantedRoles as $k => $uwr) {
		if (trim($uwr) == '') {
			unset($userWantedRoles[$k]);
		}
	}
	$userWantedRoles = array_merge($userWantedRoles);

	//Prepare new gs group
	$new_group = array('pid' => $new_pid, 'name' => $event['title'].' '.$year, 'crdate' => date('Y-m-d H:i:s'));
	//If event was copied then copy the group from the orig event
	if(substr($event['gs_gid'], 0, 4) == 'COPY') {
		$orig_gid = ko_groups_decode(substr($event['gs_gid'], 4), 'group_id');
		$orig_group = db_select_data('ko_groups', "WHERE `id` = '$orig_gid'", '*', '', '', TRUE);
		$new_group['description'] = $orig_group['description'];
		$origGroupRoles = explode(',', $orig_group['roles']);
		//Check for group from setting and add if not present in orig group
		$newGroupRoles = array_unique(array_merge($userWantedRoles, $origGroupRoles));
		foreach ($newGroupRoles as $k => $uwr) {
			if (trim($uwr) == '') {
				unset($newGroupRoles[$k]);
			}
		}
		$newGroupRoles = array_merge($newGroupRoles);
		$new_group['roles'] = implode(',', $newGroupRoles);
		$new_group['count_role'] = $orig_group['count_role'];
		$new_group['maxcount'] = $orig_group['maxcount'];
		$new_group['rights_view'] = $orig_group['rights_view'];
		$new_group['rights_new'] = $orig_group['rights_new'];
		$new_group['rights_edit'] = $orig_group['rights_edit'];
		$new_group['rights_del'] = $orig_group['rights_del'];
		//Copy or reuse datafields for new group
		if($orig_group['datafields'] != '') {
			$dfs = db_select_data('ko_groups_datafields', "WHERE `id` IN (".$orig_group['datafields'].")");
			$new_datafields = array();
			foreach($dfs as $df) {
				if($df['reusable'] == 1) {  //Reuse reusable datafields
					$new_datafields[] = $df['id'];
				} else {  //Copy others
					$new_df = $df;
					unset($new_df['id']);
					$new_datafields[] = zerofill(db_insert_data('ko_groups_datafields', $new_df), 6);
				}
			}
			$new_group['datafields'] = implode(',', $new_datafields);
		}
	}
	//Else only add the role from the setting
	else {
		$new_group['roles'] = implode(',', $userWantedRoles);
		if (trim($roleid) > 0) $new_group['count_role'] = trim($roleid);
	}

	$hookData = array('event' => $event, 'new_group' => $new_group);
	hook_function_inline('ko_daten_gs_get_gid_for_event', $hookData);
	$new_group = $hookData['new_group'];

	//Create new group
	$new_id = zerofill(db_insert_data('ko_groups', $new_group), 6);

	//Prepare redirect to edit the newly created group
	$onload_code = 'jumpToUrl(\''.$BASE_URL.'groups/index.php?action=edit_group&id='.$new_id.'\');';

	return 'g'.$new_id.($roleid ? ':r'.$roleid : '');
}//ko_daten_gs_get_gid_for_event()





function ko_daten_gs_delete_group($event) {
	$gid = ko_groups_decode($event['gs_gid'], 'group_id');
	if(!$gid) return FALSE;
	//Delete group if no subgroups and no members
	if(db_get_count('ko_groups', 'id', "AND `pid` = '$gid'") == 0 && db_get_count('ko_leute', 'id', "AND `groups` REGEXP 'g$gid'") == 0) {
		db_delete_data('ko_groups', "WHERE `id` = '$gid'");
	}
}//ko_daten_gs_delete_group()




/**
 * Create a pdf file of the events calendar. Yearly and monthly views are possible
 * @param int Number of months. 1 for a single month and 12 for a whole year
 * @param int month
 * @param int year
 */
function ko_daten_export_months($num, $month, $year) {
	global $BASE_PATH;

	//Start pdf
	define('FPDF_FONTPATH',$BASE_PATH.'fpdf/schriften/');
	require($BASE_PATH.'fpdf/fpdf.php');
	$pdf = new FPDF('L', 'mm', 'A4');
	$pdf->Open();
	$pdf->SetAutoPageBreak(true, 10);
	$pdf->SetMargins(5, 5, 5);  //left, top, right
	$pdf->AddFont('fontn','','arial.php');
	$pdf->AddFont('fontb','','arialb.php');

	//Set months and filename for year and month view
	for($i=0; $i<$num; $i++) {
		$m = ($month+$i);
		$y = $year;
		while($m > 12) { $y++; $m-=12; }
		$months[] = $m.'-'.$y;
	}
	$filename = getLL('daten_filename_pdf').str_to_2($month).'_'.$year.'_'.strftime('%d%m%Y_%H%M%S', time()).'.pdf';

	ko_get_eventgruppen($egs);

	$monthly_title = ko_get_userpref($_SESSION['ses_userid'], 'daten_monthly_title');
	$show_time = ko_get_userpref($_SESSION['ses_userid'], 'daten_pdf_show_time');



	foreach($months as $_month) {
		list($month, $year) = explode('-', $_month);
		$month = str_to_2($month);
		$data = array();
		$legend = array();

		//Get all events
		apply_daten_filter($z_where, $z_limit, 'immer', 'immer');
		$startstamp = mktime(1,1,1, (int)$month, 1, $year);
		$endstamp = mktime(1,1,1, ($month == 12 ? 1 : $month+1), 0, ($month == 12 ? $year+1 : $year));
		$z_where .= ' AND `enddatum` >= \''.strftime('%Y-%m-%d', $startstamp).'\' AND `startdatum` <= \''.strftime('%Y-%m-%d', $endstamp).'\'';
		ko_get_events($events, $z_where);

		//Calendar weeks
		$kw = array();
		$week_inc = 7*24*60*60;
		$stamp = $startstamp;
		while($stamp < $endstamp+$week_inc) {
			$kw[] = str_to_2(strftime('%V', $stamp));
			$stamp += $week_inc;
		}
		foreach($events as $event) {
			//Set title according to setting
			$content = ko_daten_get_event_title($event, $egs[$event['eventgruppen_id']], $monthly_title);

			//color
			$content['farbe'] = $event['eventgruppen_farbe'];

			//Multiday events
			if($event['startdatum'] != $event['enddatum']) {
				$date = $event['startdatum'];
				$mode = 'first';
				while((int)str_replace('-', '', $date) <= (int)str_replace('-', '', $event['enddatum'])) {
					if($date != $event['startdatum'] && $date != $event['enddatum']) {
						$mode = 'middle';
					} else if($date == $event['enddatum']) {
						$mode = 'last';
					}
					if(substr($date, 5, 2) == $month) {
						$content['zeit'] = ko_get_time_as_string($event, $show_time, $mode);
						$data[(int)substr($date, -2)]['inhalt'][] = $content;
					}
					$date = add2date($date, 'tag', 1, TRUE);
				}
			} else {
				//Time
				$content['zeit'] = ko_get_time_as_string($event, $show_time, 'default');
				$data[(int)substr($event['startdatum'], -2)]['inhalt'][] = $content;
			}

			//Legend
			ko_add_color_legend_entry($legend, $event, $egs[$event['eventgruppen_id']]);
		}//foreach(events)

		$show_legend = ko_get_userpref($_SESSION['ses_userid'], 'daten_export_show_legend') == 1;
		ko_export_cal_one_month($pdf, $month, $year, $kw, $data, getLL('daten_events'), FALSE, $show_legend, $legend);
	}//foreach(months)

	$file = $BASE_PATH.'download/pdf/'.$filename;
	$ret = $pdf->Output($file);

	return 'download/pdf/'.$filename;
}//ko_daten_export_months()





/**
 * Creates an events title, e.g. for calendar view in exports
 *
 * @param array $event Event to be processed
 * @param array $eg Event group of the given event
 * @param string $mode Title mode as set by userpref ('kommentar', 'eventgruppen_id' or 'both')
 * @return array $title An array holding the text and the short text which can be used for the event's title
 */
function ko_daten_get_event_title($event, $eg, $mode) {
	$title = array();

	if(!isset($eg['name'])) $eg = $eg[$event['eventgruppen_id']];

	//User event group name if no short name is given (so titles won't end up empty)
	if($eg['shortname'] == '') $eg['shortname'] = $eg['name'];

	//Set default value if userpref is still empty
	if($mode == '') $mode = 'both';

	if($mode == 'kommentar') {
		$title['text'] = $event['kommentar'] ? $event['kommentar'] : $eg['name'];
		$title['short'] = $event['kommentar'] ? $event['kommentar'] : $eg['shortname'];
	}
	else if($mode == 'title') {
		$title['text'] = $event['title'] ? $event['title'] : $eg['name'];
		$title['short'] = $event['title'] ? $event['title'] : $eg['shortname'];
	}
	else if($mode == 'eventgruppen_id') {
		$title['text'] = $eg['name'];
		$title['short'] = $eg['shortname'];
	}
	else if($mode == 'both') {
		$title['text'] = $eg['name'].($event['title'] ? ': '.$event['title'] : '');
		$title['short'] = $eg['shortname'].($event['title'] ? ': '.$event['title'] : '');
	}
	else if($mode == 'eventgruppen_id_kommentar') {
		$title['text'] = $eg['name'].($event['kommentar'] ? ': '.$event['kommentar'] : '');
		$title['short'] = $eg['shortname'].($event['kommentar'] ? ': '.$event['kommentar'] : '');
	}
	else if($mode == 'eventgruppen_id_kommentar2') {
		$title['text'] = $eg['name'].($event['kommentar2'] ? ': '.$event['kommentar2'] : '');
		$title['short'] = $eg['shortname'].($event['kommentar2'] ? ': '.$event['kommentar2'] : '');
	}
	else if($mode == 'title_kommentar') {
		$title['text'] = implode(': ', array($event['title'], $event['kommentar']));
		$title['short'] = implode(': ', array($event['title'], $event['kommentar']));
	}
	else {
		if($event[$mode]) $title['text'] = $title['short'] = $event[$mode];
		else $title['text'] = $title['short'] = '';
	}
	//Prevent empty short text
	if($title['short'] == '') $title['short'] = $title['text'];

	return $title;
}//ko_daten_get_event_title()




/**
 * Import events for a given event group using the ical_url set for this event group
 */
function ko_daten_import_ical($eg) {
	global $ko_path;

	if(is_array($eg)) {
		$egid = $eg['id'];
	} else {
		$egid = $eg;
		$eg = db_select_data('ko_eventgruppen', "WHERE `id` = '$egid'", '*', '', '', TRUE);
	}
	if(!$eg['id'] || $egid != $eg['id']) return FALSE;
	if($eg['type'] != 3 || !$eg['ical_url']) return FALSE;

	//Get stream data and save in file
	$icalData = ko_fetch_url($eg['ical_url']);
	if(!$icalData) return;

	//Convert iCal data to array of kOOL events
	require_once($ko_path.'inc/class.iCalReader.php');
	$ical = new iCalReader();
	$events = $ical->getEvents($icalData, $egid);


	//Get all currently imported events for the given event group
	$all_ids = array();
	$current_events = db_select_data('ko_event', "WHERE `import_id` LIKE 'eventgroup$egid:%' AND `enddatum` >= CURDATE()", 'id,import_id');
	foreach($current_events as $e) {
		$all_ids[$e['import_id']] = $e['import_id'];
	}
	unset($current_events);


	//Update or insert new events
	foreach($events as $new) {
		if(!$new['import_id']) continue;

		if(in_array($new['import_id'], $all_ids)) {
			//Update event
			db_update_data('ko_event', "WHERE `import_id` = '".$new['import_id']."'", $new);
			unset($all_ids[$new['import_id']]);
		} else {
			//Create new event
			$new['cdate'] = $new['last_change'];
			db_insert_data('ko_event', $new);
		}
	}


	//Remove events from the future which are not in iCal anymore (past events are not deleted)
	if(sizeof($all_ids) > 0) {
		db_delete_data('ko_event', "WHERE `eventgruppen_id` = '$egid' AND `import_id` != '' AND `import_id` IN ('".implode("','", $all_ids)."') AND `enddatum` > NOW()");
	}

}//ko_daten_import_ical()






//TODO: Select an EG preset (ALL, userprefs, or currently visible (default))
function ko_daten_export_preset_form($mode='new', $id=0) {
	global $smarty;

	if($mode == 'new') {
	} else {
		if(!$id) return FALSE;
		$_preset = db_select_data('ko_pdf_layout', "WHERE `id` = '$id' AND `type` = 'daten'", '*', '', '', TRUE);
		$preset = unserialize($_preset['data']);
	}


	$gc = $rowcounter = 0;

	$group[$gc] = array('titel' => getLL('daten_export_preset_title'), 'state' => 'open');
	$group[$gc]['row'][$rowcounter++]['inputs'][0] = array('desc' => getLL('daten_export_preset_name'),
																												 'type' => 'text',
																												 'name' => 'preset[name]',
																												 'value' => $_preset['name'],
																												 'params' => 'size="40"',
																												 'colspan' => 'colspan="3"',
																												 );

	$group[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('daten_export_preset_layout'),
																											 'type' => 'radio',
																											 'name' => 'preset[layout]',
																											 'values' => array('week'),
																											 'descs' => array(getLL('daten_export_preset_layout_week')),
																											 'value' => $preset['layout'],
																											 );
	$group[$gc]['row'][$rowcounter]['inputs'][1] = array('desc' => '',
																											 'type' => 'radio',
																											 'name' => 'preset[layout]',
																											 'values' => array('month'),
																											 'descs' => array(getLL('daten_export_preset_layout_month')),
																											 'value' => $preset['layout'],
																											 );
	$group[$gc]['row'][$rowcounter]['inputs'][2] = array('desc' => '',
																												 'type' => 'radio',
																												 'name' => 'preset[layout]',
																												 'values' => array('year'),
																												 'descs' => array(getLL('daten_export_preset_layout_year')),
																												 'value' => $preset['layout'],
																												 );
	$rowcounter++;

	$group[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('daten_export_preset_week_start'),
																											 'type' => 'select',
																											 'name' => 'preset[week][start]',
																											 'values' => array('today', 'tomorrow', 'shown', 'p1', 'p2', 'p3', 'p4', 'p5', 'p6', 'p7', 'n1', 'n2', 'n3', 'n4', 'n5', 'n6', 'n7'),
																											 'descs' => array(					getLL('daten_export_preset_week_start_today'),
																											 									getLL('daten_export_preset_week_start_tomorrow'),
																												 								getLL('daten_export_preset_week_start_shown'),
																											 									getLL('daten_export_preset_week_start_p1'),
																											 									getLL('daten_export_preset_week_start_p2'),
																											 									getLL('daten_export_preset_week_start_p3'),
																											 									getLL('daten_export_preset_week_start_p4'),
																											 									getLL('daten_export_preset_week_start_p5'),
																											 									getLL('daten_export_preset_week_start_p6'),
																											 									getLL('daten_export_preset_week_start_p7'),
																											 									getLL('daten_export_preset_week_start_n1'),
																											 									getLL('daten_export_preset_week_start_n2'),
																											 									getLL('daten_export_preset_week_start_n3'),
																											 									getLL('daten_export_preset_week_start_n4'),
																											 									getLL('daten_export_preset_week_start_n5'),
																											 									getLL('daten_export_preset_week_start_n6'),
																											 									getLL('daten_export_preset_week_start_n7'),
																																				),

																											 'value' => $preset['week']['start'],
																											 );
	$group[$gc]['row'][$rowcounter]['inputs'][1] = array('desc' => getLL('daten_export_preset_month_start'),
																											 'type' => 'select',
																											 'name' => 'preset[month][start]',
																											 'values' => array('0', '-1', '1', 'shown', 'n1', 'n2', 'n3', 'n4', 'n5', 'n6', 'n7', 'n8', 'n9', 'n10', 'n11', 'n12', 'p1', 'p2', 'p3', 'p4', 'p5', 'p6', 'p7', 'p8', 'p9', 'p10', 'p11', 'p12'),
																											 'descs' => array(					getLL('daten_export_preset_month_start_0'),
																											 									getLL('daten_export_preset_month_start_-1'),
																											 									getLL('daten_export_preset_month_start_1'),
																												 								getLL('daten_export_preset_month_start_shown'),
																												 								getLL('daten_export_preset_month_start_n1'),
																											 									getLL('daten_export_preset_month_start_n2'),
																											 									getLL('daten_export_preset_month_start_n3'),
																											 									getLL('daten_export_preset_month_start_n4'),
																											 									getLL('daten_export_preset_month_start_n5'),
																											 									getLL('daten_export_preset_month_start_n6'),
																											 									getLL('daten_export_preset_month_start_n7'),
																											 									getLL('daten_export_preset_month_start_n8'),
																											 									getLL('daten_export_preset_month_start_n9'),
																											 									getLL('daten_export_preset_month_start_n10'),
																											 									getLL('daten_export_preset_month_start_n11'),
																											 									getLL('daten_export_preset_month_start_n12'),
																											 									getLL('daten_export_preset_month_start_p1'),
																											 									getLL('daten_export_preset_month_start_p2'),
																											 									getLL('daten_export_preset_month_start_p3'),
																											 									getLL('daten_export_preset_month_start_p4'),
																											 									getLL('daten_export_preset_month_start_p5'),
																											 									getLL('daten_export_preset_month_start_p6'),
																											 									getLL('daten_export_preset_month_start_p7'),
																											 									getLL('daten_export_preset_month_start_p8'),
																											 									getLL('daten_export_preset_month_start_p9'),
																											 									getLL('daten_export_preset_month_start_p10'),
																											 									getLL('daten_export_preset_month_start_p11'),
																											 									getLL('daten_export_preset_month_start_p12'),
																																				),
																											 'value' => $preset['month']['start'],
																											 );
	$group[$gc]['row'][$rowcounter]['inputs'][2] = array('desc' => getLL('daten_export_preset_year_start'),
																											 'type' => 'select',
																											 'name' => 'preset[year][start]',
																											 'values' => array('0', '-1', '1', 'shown', 'n1', 'n2', 'n3', 'n4', 'n5', 'n6', 'n7', 'n8', 'n9', 'n10', 'n11', 'n12', 'p1', 'p2', 'p3', 'p4', 'p5', 'p6', 'p7', 'p8', 'p9', 'p10', 'p11', 'p12'),
																											 'descs' => array(					getLL('daten_export_preset_month_start_0'),
																											 									getLL('daten_export_preset_month_start_-1'),
																											 									getLL('daten_export_preset_month_start_1'),
																												 								getLL('daten_export_preset_month_start_shown'),
																											 									getLL('daten_export_preset_month_start_n1'),
																											 									getLL('daten_export_preset_month_start_n2'),
																											 									getLL('daten_export_preset_month_start_n3'),
																											 									getLL('daten_export_preset_month_start_n4'),
																											 									getLL('daten_export_preset_month_start_n5'),
																											 									getLL('daten_export_preset_month_start_n6'),
																											 									getLL('daten_export_preset_month_start_n7'),
																											 									getLL('daten_export_preset_month_start_n8'),
																											 									getLL('daten_export_preset_month_start_n9'),
																											 									getLL('daten_export_preset_month_start_n10'),
																											 									getLL('daten_export_preset_month_start_n11'),
																											 									getLL('daten_export_preset_month_start_n12'),
																											 									getLL('daten_export_preset_month_start_p1'),
																											 									getLL('daten_export_preset_month_start_p2'),
																											 									getLL('daten_export_preset_month_start_p3'),
																											 									getLL('daten_export_preset_month_start_p4'),
																											 									getLL('daten_export_preset_month_start_p5'),
																											 									getLL('daten_export_preset_month_start_p6'),
																											 									getLL('daten_export_preset_month_start_p7'),
																											 									getLL('daten_export_preset_month_start_p8'),
																											 									getLL('daten_export_preset_month_start_p9'),
																											 									getLL('daten_export_preset_month_start_p10'),
																											 									getLL('daten_export_preset_month_start_p11'),
																											 									getLL('daten_export_preset_month_start_p12'),
																																				),
																											 'value' => $preset['year']['start'],
																											 );
	$rowcounter++;

	$group[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('daten_export_preset_number_pages'),
																											'type' => 'select',
																											'name' => 'preset[week][pages]',
																											'values' => array(1,2,3,4,5,6,7,8,9,10),
																											'descs' => array(1,2,3,4,5,6,7,8,9,10),
																											'value' => ($preset['week']['pages'] == '' ? 1 : $preset['week']['pages']),
																										);
	$group[$gc]['row'][$rowcounter]['inputs'][1] = array('desc' => getLL('daten_export_preset_month_start2'),
																											 'type' => 'text',
																											 'name' => 'preset[month][start2]',
																											 'value' => $preset['month']['start2'],
																											 );
	$group[$gc]['row'][$rowcounter]['inputs'][2] = array('desc' => getLL('daten_export_preset_year_start2'),
																											 'type' => 'text',
																											 'name' => 'preset[year][start2]',
																											 'value' => $preset['year']['start2'],
																											 );
	$rowcounter++;



	$group[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('daten_export_preset_week_length'),
																											 'type' => 'select',
																											 'name' => 'preset[week][length]',
																											 'values' => array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21),
																											 'descs' => array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21),
																											 'value' => $preset['week']['length'],
																											 );
	$group[$gc]['row'][$rowcounter]['inputs'][1] = array('desc' => getLL('daten_export_preset_month_length'),
																											 'type' => 'select',
																											 'name' => 'preset[month][length]',
																											 'values' => array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24),
																											 'descs' => array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24),
																											 'value' => $preset['month']['length'],
																											 );
	$group[$gc]['row'][$rowcounter]['inputs'][2] = array('desc' => getLL('daten_export_preset_year_length'),
																											 'type' => 'select',
																											 'name' => 'preset[year][length]',
																											 'values' => array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18),
																											 'descs' => array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18),
																											 'value' => $preset['year']['length'],
																											 );
	$rowcounter++;



	$smarty->assign('tpl_titel', getLL('daten_export_title'));
	$smarty->assign('tpl_submit_value', getLL('save'));
	$smarty->assign('tpl_action', ($mode == 'new' ? 'submit_new_export_preset' : 'submit_edit_export_preset'));
	$smarty->assign('tpl_cancel', 'listpresets');
	$smarty->assign('tpl_groups', $group);
	$smarty->assign('tpl_hidden_inputs', array(0 => array('name' => 'preset_id', 'value' => $id)));

	$smarty->display('ko_formular.tpl'); // TODO: set values corresponding to the setting we are editing
}//ko_daten_export_preset_form()





function ko_daten_list_export_presets() {
	global $smarty;
	global $access;

	if($access['daten']['MAX'] < 4) return;

	$es = db_select_data('ko_pdf_layout', "WHERE `type` = 'daten'", '*', 'ORDER BY `name` ASC');
	$rows = sizeof($es);

	$list = new kOOL_listview();

	$list->init('daten', 'ko_pdf_layout', array('chk', 'edit', 'delete'), 1, 1000);
	$list->setTitle(getLL('submenu_daten_export_presets'));
	$list->setAccessRights(FALSE);
	$list->setActions(array('edit' => array('action' => 'edit_export_preset'),
													'delete' => array('action' => 'delete_export_preset', 'confirm' => TRUE))
										);
	$list->setSort(FALSE);
	$list->disableMultiedit();
	$list->setStats($rows, '', '', '', TRUE);


	//Footer to create new export
	$list_footer = $smarty->get_template_vars('list_footer');
	$list_footer[] = array('label' => getLL('submenu_daten_export_new_preset'), 'button' => '<input type="submit" onclick="set_action(\'new_export_preset\');" value="'.getLL('OK').'" />');
	$list->setFooter($list_footer);


	//Output the list
	if($output) {
		$list->render($es);
	} else {
		print $list->render($es);
	}
}//ko_daten_list_export_presets()





/**
 * Creates a PDF export according to a given preset
 * @param $data array holding the preset data ($data = unserialize($preset[data]);
 * @return string filename of created PDF file
 */
function ko_daten_export_preset($data) {
	if($data['layout'] == 'week') {
		//Calculate start date
		$_start = $data['week']['start'];
		if($_start == 'today') {
			$start = date('Y-m-d');
		} else if($_start == 'tomorrow') {
			$start = add2date(date('Y-m-d'), 'day', 1, TRUE);
		}
		//Next weekday (1-7)
		else if(substr($_start, 0, 1) == 'n') {
			$target = substr($_start, 1);
			$start = date('Y-m-d');
			$wd = date('w', strtotime($start));

			if($target > $wd) $inc = $target - $wd;
			else $inc = $wd + 7 - $target;

			$start = add2date($start, 'day', $inc, TRUE);
		}
		//Past weekday (1-7)
		else if(substr($_start, 0, 1) == 'p') {
			$target = substr($_start, 1);
			$start = date('Y-m-d');
			$wd = date('w', strtotime($start));

			if($target < $wd) $inc = -1*($wd - $target);
			else $inc = -7 + ($target - $wd);

			$start = add2date($start, 'day', $inc, TRUE);
		}

		//Shown Day
		else if(substr($_start, 0, 1) == 's') {
			$date = date_create_from_format('d.m.Y', $_SESSION['cal_tag'] . '.' . $_SESSION['cal_monat'] . '.' . $_SESSION['cal_jahr']);
			$start = date_format($date, 'Y-m-d');
		}

		$_pages = $data['week']['pages'];

		//Number of days
		$length = intval($data['week']['length']);
		if($length < 1 || $length > 21) $length = 7;

		return ko_export_cal_weekly_view('daten', $length, $start, $_pages);
	}//week


	else if($data['layout'] == 'month') {
		$length = intval($data['month']['length']);
		$_start = $data['month']['start'];

		if($length < 1 || $length > 24) $length = 1;

		//Manually set start month-year
		if($data['month']['start2']) {
			list($month, $year) = explode('-', $data['month']['start2']);
			$month = intval($month); $year = intval($year);
			if(!$month) $month = 1;
			if(!$year) $year = date('Y');
		}
		//Shown Month
		else if(substr($_start, 0, 1) == 's') {
			$date = date_create_from_format('d.m.Y', $_SESSION['cal_tag'] . '.' . $_SESSION['cal_monat'] . '.' . $_SESSION['cal_jahr']);
			$month = intval(date_format($date, 'm'));
			$year = date_format($date, 'Y');
		}
		//Relative selection
		else {
			$_start = $data['month']['start'];

			if(in_array($_start, array('0', '-1', '1'))) {
				$inc = intval($_start);
			}
			//Next month (1-12)
			else if(substr($_start, 0, 1) == 'n') {
				$target = intval(substr($_start, 1));
				if($target > intval(date('m'))) $inc = $target - intval(date('m'));
				else $inc = 12 - (intval(date('m')) - $target);
			}
			//Past month (1-12)
			else if(substr($_start, 0, 1) == 'p') {
				$target = intval(substr($_start, 1));
				if($target < intval(date('m'))) $inc = $target - intval(date('m'));
				else $inc = -12 + ($target - intval(date('m')));
			}

			$year = date('Y');
			$month = intval(date('m')) + intval($inc);
		}

		while($month > 12) { $year++; $month -= 12; }
		while($month < 1) { $year--; $month += 12; }

		return ko_daten_export_months($length, $month, $year);
	}//month


	else if($data['layout'] == 'year') {
		$length = intval($data['year']['length']);
		$_start = $data['year']['start'];

		if($length < 1 || $length > 24) $length = 1;

		//Manually set start month-year
		if($data['year']['start2']) {
			list($month, $year) = explode('-', $data['year']['start2']);
			$month = intval($month); $year = intval($year);
			if(!$month) $month = 1;
			if(!$year) $year = date('Y');
		}
		//Shown Year
		else if(substr($_start, 0, 1) == 's') {
			$date = date_create_from_format('d.m.Y', $_SESSION['cal_tag'] . '.' . $_SESSION['cal_monat'] . '.' . $_SESSION['cal_jahr']);
			$month = intval(date_format($date, 'm'));
			$year = date_format($date, 'Y');
		}
		else {
			$_start = $data['year']['start'];
			if(in_array($_start, array('0', '-1', '1'))) {
				$inc = intval($_start);
			}
			//Next month (1-12)
			else if(substr($_start, 0, 1) == 'n') {
				$target = intval(substr($_start, 1));
				if($target > intval(date('m'))) $inc = $target - intval(date('m'));
				else $inc = 12 - (intval(date('m')) - $target);
			}
			//Past month (1-12)
			else if(substr($_start, 0, 1) == 'p') {
				$target = intval(substr($_start, 1));
				if($target < intval(date('m'))) $inc = $target - intval(date('m'));
				else $inc = -12 + ($target - intval(date('m')));
			}

			$year = date('Y');
			$month = intval(date('m')) + intval($inc);
		}
		while($month > 12) { $year++; $month -= 12; }
		while($month < 1) { $year--; $month += 12; }

		return ko_export_cal_pdf_year('daten', $month, $year, $length);
	}//year

}//ko_daten_export_preset()


?>
