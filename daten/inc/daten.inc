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

global $BASE_PATH;
require_once($BASE_PATH."inc/class.kOOL_listview.php");

/**
 * @param String &$z_where filter for sql
 * @param String &$z_limit limit for sql
 * @param String|Null $_start Date to narrow sql result
 * @param String|Null $_ende Date to narrow sql result
 * @param String $_tg Termingruppen Ids
 */
function apply_daten_filter(&$z_where, &$z_limit, $_start=NULL, $_ende=NULL, $_tg="") {
	global $access;

	$use_start = ($_start !== NULL) ? $_start : $_SESSION["filter_start"];
	$use_ende = ($_ende !== NULL) ? $_ende : $_SESSION["filter_ende"];
	$use_tg = ($_tg != "") ? $_tg : $_SESSION["show_tg"];
	$taxonomy_filter = $_SESSION["daten_taxonomy_filter"];


	// parse filters
	$use_start = ko_daten_parse_time_filter($use_start);
	$use_ende = ko_daten_parse_time_filter($use_ende, '');

	//Permanenten Filter einfügen, falls vorhanden und nur view-Rechte
	$z_where = "";
	$perm_filter_start = ko_get_setting("daten_perm_filter_start");
	$perm_filter_ende  = ko_get_setting("daten_perm_filter_ende");

	// check, if the login has the 'force_global_filter' flag set to 1
	$forceGlobalTimeFilter = ko_get_force_global_time_filter("daten", $_SESSION['ses_userid']);

	if($forceGlobalTimeFilter != 2 && ($forceGlobalTimeFilter == 1 || $access['daten']['MAX'] < 2) && ($perm_filter_start || $perm_filter_ende)) {
		if($perm_filter_start != "") {
			$z_where .= " AND enddatum >= '".$perm_filter_start."' ";
		}
		if($perm_filter_ende != "") {
			$z_where .= " AND startdatum <= '".$perm_filter_ende."' ";
		}
	}

	//Filter anwenden, falls gesetzt
	$start = ko_daten_parse_time_filter($use_start);
	if ($start != '') $z_where .= "AND enddatum >= '$start'";
	$ende = ko_daten_parse_time_filter($use_ende, '');
	if ($ende != '') $z_where .= "AND startdatum <= '$ende'";

	//Set filters from KOTA
	if(function_exists('kota_apply_filter')) {
		$kota_where = kota_apply_filter('ko_event');
		if($kota_where != '') $z_where .= " AND ($kota_where) ";
	}

	apply_daten_filter_for_teams($z_where);

	//Die gewünschten Termingruppen anzeigen
	$z_where_add = "";
	$found = FALSE;
	$tgs = array();
	if(is_array($use_tg)) {
		foreach($use_tg as $g) {
			if($access['daten'][$g] < 1) continue;
			$tgs[] = $g;
		}
		$z_where_add = " `eventgruppen_id` IN ('".implode("','", $tgs)."') ";
		$found = TRUE;
	}

	if(!empty($taxonomy_filter)) {
		$terms = explode(",", $taxonomy_filter);
		$eventIds_filter_by_term = [];

		foreach($terms AS $term) {
			$child_terms = ko_taxonomy_get_terms_by_parent($term);
			$child_terms[]['id'] = $term;

			foreach($child_terms AS $child_term) {
				$eventIds_filter_by_term = array_merge(ko_taxonomy_get_nodes_by_termid($child_term['id'], "ko_event"), $eventIds_filter_by_term);
			}
		}

		if(empty($eventIds_filter_by_term)) {
			$eventIds_filter_by_term[0]['id'] = -1;
		}

		$found = TRUE;
	}

	if($z_where_add) {
		$z_where .= " AND ( $z_where_add ) ";
	}

	if(!empty($eventIds_filter_by_term)) {
		$z_where .= " AND ko_event.id IN (". implode(",", array_column($eventIds_filter_by_term,"id")) . ")";
	}

	if($found == FALSE) {
		//Falls nichts gefunden, ein FALSE-Bedingung einfügen!
		$z_where = " AND ( 1=2 ) ";
	}

	//Limit bestimmen
	$z_limit = "LIMIT " . ($_SESSION["show_start"]-1) . ", " . $_SESSION["show_limit"];
}

/**
 * Update z_where to filter schedulled teams in event.
 *
 * @param String &$z_where SQL filter
 * @return void
 */
function apply_daten_filter_for_teams(&$z_where) {
	foreach ($_SESSION['kota_filter']["ko_event"] AS $filter_key => $values) {
		$only_events = FALSE;
		if (substr($filter_key, 0, 8) == "rotateam") {
			$teamid = substr($filter_key, 9);

			foreach($values as $value) {
				if (substr($value, 0, 1) == "!") {
					$value = substr($value, 1);
					$negative = TRUE;
				} else {
					$negative = FALSE;
				}

				if (preg_match('/^(g[0-9]{6}|[0-9]{1,6})$/', $value, $matches)) {
					$events = db_select_data("ko_rota_schedulling", "WHERE team_id = '".$teamid."' AND schedule REGEXP '(^|,)$value(,|$)'", "event_id", "", "", "", TRUE);
				} else {
					$events = db_select_data("ko_rota_schedulling", "WHERE team_id = '".$teamid."' AND schedule LIKE '%$value%'", "event_id", "", "", "", TRUE);
				}
				foreach ($events as $event) {
					$only_events[] = $event['event_id'];
				}
			}
		} else {
			continue;
		}

		if ($only_events == FALSE) {
			$z_where .= ' AND 1=2';
		} elseif (count($only_events) > 0) {
			$z_where .= ' AND ko_event.id ' . ($negative ? 'NOT' : '') . " IN ('" . implode("','", $only_events) . "')";
		}
	}
}


/**
 * Shows list of events
 * Using the data from KOTA
 *
 * @param        $method
 * @param string $mode
 * @param bool   $dontApplyLimit
 * @param bool   $showForeignRows only applies for $mode=='xls'
 * @param array  $filter_ids only display IDs from array
 * @return null|boolean
 * @throws Exception
 */
function ko_list_events($method, $mode='html', $dontApplyLimit=FALSE, $showForeignRows=FALSE, $filter_ids = []) {
	global $ko_path, $smarty, $KOTA;
	global $access, $LOCAL_LANG;

	if($method == "mod") {
		ko_list_mod_events("new");
		ko_list_open_new_form();
		ko_list_mod_events("edit");
		ko_list_open_new_form();
		ko_list_mod_events("delete");
		return "";
	}

	if($access['daten']['MAX'] < 1) return FALSE;
	apply_daten_filter($z_where, $z_limit);

	if(!empty($filter_ids)) {
		$z_where.= " AND `ko_event`.`id` IN ('".implode("','", $filter_ids)."') ";
	}

	$list = new kOOL_listview();

	$rows = db_get_count("ko_event", "id", $z_where);
	if($_SESSION['show_start'] > $rows) {
		$_SESSION['show_start'] = 1;
		$z_limit = 'LIMIT '.($_SESSION['show_start']-1).', '.$_SESSION['show_limit'];
	}
	if($dontApplyLimit) $z_limit = '';
	ko_get_events($es, $z_where, $z_limit);

	foreach($es as $k => $event) {
		if($access['taxonomy']['ALL'] >= 1) {
			$es[$k]['terms'] = '';
		}
	}

	$showOverlay = $_SESSION['ses_userid'] != ko_get_guest_id() && $access['daten']['MAX'] > 0;
	$showEditColumns = array('chk');
	if($access['daten']['MAX'] > 1) $showEditColumns[] = 'edit';
	if($access['daten']['MAX'] > 2) $showEditColumns[] = 'delete';
	if($showOverlay) $showEditColumns[] = 'overlay';

	$list->init("daten", "ko_event", $showEditColumns, $_SESSION["show_start"], $_SESSION["show_limit"]);
	$list->showColItemlist();
	$list->setTitle(getLL("daten_list_title"));
	$list->setAccessRights(array('edit' => 2, 'delete' => 2, 'overlay' => 1), $access['daten']);
	$list->setActions(array("edit" => array("action" => "edit_termin"),
													"delete" => array("action" => "delete_termin", "confirm" => TRUE))
										);
	if ($access['daten']['MAX'] > 1 && db_get_count('ko_eventgruppen', 'id', "AND `type` = '0'") > 0) $list->setActionNew('neuer_termin');

	$mark = ko_get_userpref($_SESSION['ses_userid'], 'daten_mark_sunday');
	if($mark == 2) {
		$list->setRowClass("ko_list_color_by_eg_EVENTGRUPPEN_ID", 'return TRUE;');
	}
	else if($mark == 1) { //Mark sunday with extra tr class definition
		$list->setRowClass("ko_list_sunday", 'return strftime("%w", strtotime("STARTDATUM")) == 0;');
	}

	if($method == 'mod') {
		$list->setStats($rows, '', '', '', TRUE);
	} else {
		$list->setStats($rows);
	}
	$list->setSort(TRUE, "setsortevent", $_SESSION["sort_events"], $_SESSION["sort_events_order"]);

	if($showOverlay) {
		foreach ($es as $id => $event) {
			$overlay_icons = array();
			$overlay_icons[] = array('icon' => '<i class="fa fa-file-pdf-o"></i>', 'link' => 'index.php?action=export_single_pdf_settings&id='.$event['id'], 'title' => getLL('daten_label_export_single'), 'text' => getLL('daten_label_export_single_short'));
			$list->setRowData(array('overlay_icons' => $overlay_icons), $id);
		}
	}

	//Footer for event list
	$list_footer = $smarty->get_template_vars('list_footer');
	if($access['daten']['MAX'] > 1) {
		$list_footer[] = array(
			"label" => getLL("daten_list_footer_del_label"),
			"button" => '<button type="submit" class="btn btn-sm btn-danger" onclick="c=confirm('."'".ko_js_save(getLL("daten_list_footer_del_button_confirm"))."'".');if(!c) return false;set_action(\'del_selected\');" value="'.getLL("daten_list_footer_del_button").'">' . getLL("daten_list_footer_del_button") . '</button>'
		);
	}
	$list->setFooter($list_footer);

	$list->setWarning(kota_filter_get_warntext('ko_event'));
	$list->setShowForeignRows($showForeignRows);

	$list->render($es, $mode, getLL('daten_filename_xls'));
	if($mode == 'xls') return $list->xls_file;
}//ko_list_events()



/**
  * Shows list of mod events
	*/
function ko_list_mod_events($mode) {
	global $ko_path, $smarty;
	global $access, $DATETIME;

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

			foreach($es as $mid => $me) {
				if(!$me['resitems']) continue;

				$newitems = array();
				ko_get_resitems($resitems, '', "WHERE ko_resitem.id IN (".implode(',', explode(',', $me['resitems'])).")");
				foreach($resitems as $ri) {
					$tt = strftime($DATETIME['dmY'], strtotime($me['res_startdatum'])).' '.substr($me['res_startzeit'], 0, -3);
					$tt .= ' - '.strftime($DATETIME['dmY'], strtotime($me['res_enddatum'])).' '.substr($me['res_endzeit'], 0, -3);
					$newitems[] = '<a href="#" onclick="return false;" '.ko_get_tooltip_code($tt).'>'.$ri['name'].'</a>';
				}

				$es[$mid]['reservationen'] = implode(', ', $newitems);
			}
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
					} else if(in_array($key, array("endzeit"))) {
						continue;
					} else if($key == 'reservationen') {
						kota_listview_event_mod_res($value, array('dataset' => $me));
						$es[$mid]['reservationen'] = $value;
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

	$list->setTitle(getLL("daten_mod_list_title")." ".getLL("daten_mod_list_title_".$mode));
	$list->setAccessRights(array('check' => 5, 'delete' => 4), $mod_access, '_eventgruppen_id');
	$list->setActions(array("check" => array("action" => $action_check,
																					 "additional_js" => "c1=confirm('".ko_js_save(getLL("daten_mod_confirm_confirm"))."');if(!c1) return false;c = confirm('".ko_js_save(getLL("daten_mod_confirm_confirm2"))."');set_hidden_value('mod_confirm', c);"),
													"delete" => array("action" => $action_delete,
																						"additional_js" => ($access['daten']['MAX'] > 3 ? "c1 = confirm('".ko_js_save(getLL("daten_mod_decline_confirm"))."');if(!c1) return false;c = confirm('".ko_js_save(getLL("daten_mod_decline_confirm2"))."');set_hidden_value('mod_confirm', c);" : "") ))
										);
	$list->disableMultiedit();
	if($mode == "edit") $list->disableKotaProcess();

	$list->setStats($rows, 1, 500);
	$list->setSort(TRUE, "setsortevent", $_SESSION["sort_events"], $_SESSION["sort_events_order"]);

	//Footer for mod events
	$list_footer = $smarty->get_template_vars('list_footer');
	$list_footer[] = array("label" => getLL("daten_list_footer_del_label"), 
												 "button" => '<button type="submit" class="btn btn-sm btn-danger" onclick="c1 = confirm(\''.ko_js_save(getLL("daten_mod_decline_confirm")).'\');if(!c1) return false;'.($access['daten']['MAX'] > 3 ? 'c = confirm(\''.ko_js_save(getLL("daten_mod_decline_confirm2")).'\');set_hidden_value(\'mod_confirm\', c);' : '').'set_action(\''.$action_delete.'_multi\', this);" value="'.getLL("daten_list_footer_del_button").'">' . getLL("daten_list_footer_del_button") . '</button>');
	if($access['daten']['MAX'] > 3) {
		$list_footer[] = array("label" => getLL("daten_list_footer_confirm_label"),
													 "button" => '<button type="submit" class="btn btn-sm btn-success" onclick="c1=confirm(\''.ko_js_save(getLL("daten_mod_confirm_confirm")).'\');if(!c1) return false;c = confirm(\''.ko_js_save(getLL("daten_mod_confirm_confirm2")).'\');set_hidden_value(\'mod_confirm\', c);set_action(\''.$action_check.'_multi\', this);" value="'.getLL("ok").'">' . getLL('ok') . '</button>');
	}
	$list->setFooter($list_footer);


	$list->render($es);
}//ko_list_mod_events()

/**
 * When we use ko_list_(mod)_events multiple times on a page, we need to create a new form.
 * Otherwise button clicks could accidentelly fire action in other forms.
 */
function ko_list_open_new_form() {
	echo '</div></form>
	<form action="index.php" method="post" name="formular" enctype="multipart/form-data">
	<input type="hidden" name="action" id="action" value="" />
	<input type="hidden" name="id" id="id" value="" />
	<input type="hidden" name="mod_confirm" id="mod_confirm" value="" />
	<input type="hidden" name="new_date" id="new_date" value="" />
	<div>';
}

/***
 * lists all reminders
 * @param string $mode
 * @return mixed
 */
function ko_list_reminders($mode='html') {
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
	if ($access['daten']['REMINDER'] >= 1) $list->setActionNew('new_reminder');
	$list->setSort(TRUE, 'setsort', $_SESSION['sort_event_reminders'], $_SESSION['sort_event_reminders_order']);
	$list->setStats($rows);
	$list->setWarning(kota_filter_get_warntext('ko_reminder'));
	$list->setRowClass("ko_list_hidden", 'return STATUS == 0;');
	$list->ShowColItemlist();


	//Output the list
	$list->render($es, $mode);
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

	if($mode == 'edit') {
		$form_data['action_as_new'] = 'submit_as_new_event_reminder';
		$form_data['label_as_new'] = getLL('daten_submit_as_new_reminder');
	}

	ko_multiedit_formular('ko_reminder', '', $id, '', $form_data, FALSE, 1);

}//ko_formular_reminder()


/***
 * @param $mode -- either 'edit' or 'add'
 * @param string $id -- if mode == 'edit', id of the id of the room has to be supplied here
 * @return bool
 */
function ko_formular_room($mode, $id='') {
	if($mode == 'new') {
		$id = 0;
	} else if($mode == 'edit') {
		if(!$id) return FALSE;
	} else {
		return FALSE;
	}

	$form_data['title'] =  $mode == 'new' ? getLL('ko_event_rooms_form_title_new') : getLL('ko_event_rooms_form_title_edit');
	$form_data['submit_value'] = getLL('save');
	$form_data['action'] = $mode == 'new' ? 'submit_new_event_room' : 'submit_edit_event_room';
	$form_data['cancel'] = 'list_rooms';
	ko_multiedit_formular('ko_event_rooms', '', $id, '', $form_data, FALSE, 1);
	return TRUE;
}


/**
 * Get all rooms, apply kota_filter and return in kOOL_listview
 */
function ko_list_rooms() {
	global $access;

	if($access['daten']['MAX'] < 1) return;

	$kota_where = kota_apply_filter('ko_event_rooms');
	$z_where = "";
	if($kota_where) $z_where .= " AND ($kota_where) ";

	$rows = db_get_count("ko_event_rooms", "id", $z_where);
	if($_SESSION['show_start'] > $rows) {
		$_SESSION['show_start'] = 1;
	}
	$z_limit = 'LIMIT '.($_SESSION['show_start']-1).', '.$_SESSION['show_limit'];
	$rooms = ko_get_event_rooms("", "WHERE 1=1 " . $z_where, $z_limit);


	$row = 0;
	foreach($rooms AS $room) {
		$tpl_list_data[$row++]["rowclass"] = $room["hidden"] ? "row-inactive" : "";
	}

	$list = new kOOL_listview();
	$list->init("daten", "ko_event_rooms", array("chk", "edit", "delete"), $_SESSION["show_start"], $_SESSION["show_limit"]);
	$list->setTitle(getLL("daten_rooms_list_title"));
	$list->setAccessRights(array('edit' => '2', 'delete' => 'ALL3'), $access['daten']);
	$list->setActions(array(
			"edit" => array("action" => "edit_room"),
			"delete" => array("action" => "delete_room", "confirm" => TRUE)
		)
	);

	if ($access['daten']['MAX'] > 1) $list->setActionNew('new_room');
	$list->setStats($rows);
	$list->setSortable();
	$list->setSort(TRUE, 'setsortrooms', $_SESSION['sort_rooms'], $_SESSION['sort_rooms_order']);
	$list->setRowClass("row-inactive", 'return ("HIDDEN") == 1;');

	$list->render($rooms);
}


function ko_delete_event_room($id) {
	$room = ko_get_event_rooms($id);
	if(!empty($room['id'])) {
		$where = "WHERE `id` = '$id'";
		db_delete_data("ko_event_rooms", $where);
		ko_log_diff('delete_event_room', $id, $room);
		return TRUE;
	}

	return FALSE;
}


function ko_list_groups() {
	global $access;

	if($access['daten']['MAX'] < 3) return;

	$list = new kOOL_listview();

	//Nur die erlaubten Termingruppen anzeigen
	if($access['daten']['ALL'] < 3) {
		$egs = array();
		foreach($access['daten'] as $k => $v) {
			if(!intval($k)) continue;
			if($v >= 1) $egs[] = $k;
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

	foreach($es as $k => $event) {
		if($access['taxonomy']['ALL'] >= 1) {
			$es[$k]['terms'] = '';
		}
	}

	$list->init("daten", "ko_eventgruppen", array("chk", "edit", "delete"), $_SESSION["show_start"], $_SESSION["show_limit"]);
	$list->setTitle(getLL("daten_groups_list_title"));
	$list->setAccessRights(array('edit' => '3', 'delete' => 'ALL3'), $access['daten']);
	$list->setActions(array(
			"edit" => array("action" => "edit_gruppe"),
			"delete" => array("action" => "delete_gruppe", "confirm" => TRUE)
		)
	);

	if ($access['daten']['ALL'] > 2) $list->setActionNew('neue_gruppe');
	$list->setStats($rows);
	$list->setSort(TRUE, "setsorteventgroups", $_SESSION["sort_tg"], $_SESSION["sort_tg_order"]);
	$list->setWarning(kota_filter_get_warntext('ko_eventgruppen'));

	$list->render($es);
}//ko_list_groups()





function ko_formular_termin($mode, $id, $data=array()) {
	global $smarty, $KOTA, $ko_path, $EVENTS_SHOW_RES_FIELDS;
	global $access, $all_groups, $HOLIDAYS;

	//State of repetition settings: Defaults to closed
	$repetition_state = 'closed';

	if($mode == "edit" && $id != 0) {
		//Get event data to be edited
		ko_get_event_by_id($id, $e);
		if($access['daten'][$e['eventgruppen_id']] < 2) return;

		if(ko_module_installed("reservation")) {
			//Set res times to event times if none are given for even group
			ko_get_eventgruppe_by_id($e["eventgruppen_id"], $tg);
			//Set res times as fallback from eventgroup or event (will be overwritten below if reservations were found)
			foreach($EVENTS_SHOW_RES_FIELDS as $f) {
				${'res_'.$f} = $tg['res_'.$f];
			}

			$responsibleForRes = '';
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

					if ($responsibleForRes == '' && $res["user_id"]) {
						$responsibleForRes = $res["user_id"];
					}
				}//foreach(res_s as r)
			}//if(e[reservationen])
			if (!$responsibleForRes) {
				$responsibleForRes = $tg['responsible_for_res'];
			}
			if (!$responsibleForRes) {
				$responsibleForRes = $_SESSION['ses_userid'];
			}
		}//if(ko_module_installed(reservation))

	}//if(edit && id)

	else if($mode == "neu") {
		if($access['daten']['MAX'] < 2) return;

		//set values given through data[]
		$data["startzeit"] = $data["start_time"];
		$data["endzeit"] = $data["end_time"];
		// set responsible person for reservations to current login
		$responsibleForRes = $_SESSION['ses_userid'];
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
			$responsibleForRes = $_POST['sel_responsible_for_res'];

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
		} else {
			//Set default values from KOTA for res fields
			if(ko_module_installed("reservation")) {
				foreach($EVENTS_SHOW_RES_FIELDS as $f) {
					${'res_'.$f} = $KOTA['ko_reservation'][$f]['form']['default'];
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
		case "holidays": $true = 5; break;
		case "dates": $true = 6; break;
		default: $true = 0;
	}
	for($i=0; $i<6; $i++) {
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

	$sel_code_holidays = '';
	$value = format_userinput($_POST["sel_repeat_holidays"], "alphanum");
	foreach ($HOLIDAYS as $name => $holiday) {
		$sel_code_holidays .= '<option value="'.$name.'"'.($value==$name?' selected="selected"':'').'>'.getLL('holiday_'.$name).'</option>';
	}
	$sel_code_holidays_offset = '';
	for ($i = -21; $i < 22; $i++) {
		$sel_code_holidays_offset .= '<option value="'.sprintf("%+d", $i).'"'.($i == 0?' selected="selected"':'').'>'.sprintf("%+d", $i).'</option>';
	}

	// dates
	$localSmarty = clone($smarty);
	$sel_code_dates_input = array(
		'type' => 'multidateselect',
		'name' => 'sel_repeat_dates',
		'avalue' => $_POST['sel_repeat_dates'],
		'params' => 'size="8"',
	);

	$localSmarty->assign('input', $sel_code_dates_input);
	$sel_code_dates = $localSmarty->fetch('ko_formular_elements.tmpl');

	$repeat_descs[] = " " . getLL("daten_repeat_none");
	$repeat_descs[] = " " . sprintf(getLL("daten_repeat_daily"), ' </label><input class="res-conflict-field" type="text" name="txt_repeat_tag" value="'.$txt_repeat_tag.'" size="2" /> ', '<label>');
	$repeat_descs[] = " " . sprintf(getLL("daten_repeat_weekly"), ' </label><input class="res-conflict-field" type="text" name="txt_repeat_woche" value="'.$txt_repeat_woche.'" size="2" /> ', '<label>');
	$repeat_descs[] = " " . sprintf(getLL("daten_repeat_monthly1"), ' </label><select class="res-conflict-field" name="sel_monat1_nr" size="0">'.$sel1_code.'</select><select class="res-conflict-field" name="sel_monat1_tag" size="0">'.$sel2_code.'</select><label>');
	$repeat_descs[] = " " . sprintf(getLL("daten_repeat_monthly2"), ' </label><input class="res-conflict-field" type="text" name="txt_repeat_monat2" value="'.$txt_repeat_monat2.'" size="2" /> ', '<label>');
	$repeat_descs[] = " " . sprintf(getLL("daten_repeat_holidays"), ' </label><select class="res-conflict-field" name="sel_repeat_holidays">'.$sel_code_holidays.'</select><select class="res-conflict-field" name="sel_repeat_holidays_offset">'.$sel_code_holidays_offset.'</select>'.getLL('days').' <label>');
	$repeat_descs[] = " " . sprintf(getLL("daten_repeat_dates"), ' </label>'.$sel_code_dates.'<label>');

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
	$holiday_code = '<select class="form-control res-conflict-field" name="sel_repeat_eg" size="0"><option value=""></option>';
	$value = $_POST['sel_repeat_eg'] ? format_userinput($_POST['sel_repeat_eg'], 'uint') : '';
	foreach(ko_get_eventgruppen_for_select() as $entry) {
		$sel = $value == $entry['value'] && !$entry['disabled'] ? ' selected="selected"' : '';
		$dis = $entry['disabled'] ? ' disabled="disabled"' : '';
		$holiday_code .= '<option value="'.$entry['value'].'"'.$sel.$dis.'>';
		$holiday_code .= $entry['desc'];
		$holiday_code .= '</option>';
	}
	$holiday_code .= '</select>';

	$repeat_stop  = '
<div class="input-group">
<select class="form-control res-conflict-field" name="sel_bis_tag" style="width:50px;" size="0">'.$sel_day_code.'</select>&nbsp;&nbsp;';
	$repeat_stop .= '<select class="form-control res-conflict-field" name="sel_bis_monat" style="width:130px;" size="0">'.$sel_month_code.'</select>&nbsp;&nbsp;';
	$repeat_stop .= '<select class="form-control res-conflict-field" name="sel_bis_jahr" style="width:80px;" size="0">'.$sel_year_code.'</select></div>';
	$repeat_stop .= '<br />'.getLL("daten_repeat_or").'<br />
<input class="res-conflict-field" type="text" name="txt_num_repeats" size="4" maxlength="3" value="'.($_POST['txt_num_repeats'] ? $_POST['txt_num_repeats'] : '').'" onkeyup="repeat_disable(this.value);" />&nbsp;'.getLL("daten_repeat_iterations").' <a href="#" onclick="return false;" '.ko_get_tooltip_code(getLL('res_repeat_iterations_help')).'><i class="fa fa-question-circle"></i></a>';
	$repeat_stop .= '<script>repeat_disable($(\'[name="txt_num_repeats"]\')[0].value);</script>';
	$repeat_stop .= '<br /><br />'.getLL('daten_repeat_eg').'<br />'.$holiday_code;



	//Formular aufbauen
	$table = "ko_event";
	$rowcounter = 0;
	$gc = 0;

	//Spezielle Formular-Einstellungen vornehmen
	$KOTA[$table]["eventgruppen_id"]["form"]["js_func_before_change"] .= 'seleventgroup_before_change';

	$hidden_rooms = ko_get_event_rooms("", "WHERE hidden = 1");
	foreach($KOTA[$table]["room"]["form"]["values"] AS $key => $room_in_select) {
		if (in_array($room_in_select, array_column($hidden_rooms, "id")) && $room_in_select != $e['room']) {
			unset($KOTA[$table]["room"]["form"]["values"][$key]);
			unset($KOTA[$table]["room"]["form"]["descs"][$key]);
		}
	}

	//get first part of form from kota
	$group = ko_multiedit_formular($table, "", $id, "", "", TRUE, '', TRUE, 'formular', '2');
	$group[0]['groups'][$gc]["title"] = "";
	$rowcounter = sizeof($group[0]['groups'][$gc]["rows"])+1;

	if ($access['daten']['MAX'] > 2 || $access['daten'][$e['eventgruppen_id']] > 2 ) {
		if(empty($group[0]['groups'][0]['options'])) {
			unset($group[0]['groups'][0]['options']);
		}
		$group[0]['groups'][0]['options']['delete_action'] = 'delete_termin';
	}

	//Wiederholungen (nur bei Neu)
	if($mode == "neu") {
		$group[0]['groups'][++$gc] = array("titel" => getLL("daten_repeat"), "state" => $repetition_state, 'group' => TRUE);
		$group[0]['groups'][$gc]["rows"][$rowcounter]["inputs"][0] = array("desc" => getLL("daten_repeat_title1"),
																 "type" => "radio",
																 "name" => "rd_wiederholung",
																 "values" => array("keine", "taeglich", "woechentlich", "monatlich1", "monatlich2", "holidays", "dates"),
																 "descs" => $repeat_descs,
																 "separator" => "<br />",
																 'add_class' => 'res-conflict-field',
																 "value" => isset($_POST["rd_wiederholung"]) ? $_POST["rd_wiederholung"] : "keine"
																 );
		$group[0]['groups'][$gc]["rows"][$rowcounter++]["inputs"][1] = array("desc" => getLL("daten_repeat_title2"),
																 "type" => "html",
																 "value" => $repeat_stop
																 );
	}


	//Group subscriptions
	if(!isset($access['groups'])) ko_get_access('groups');
	if(ko_get_setting('daten_gs_pid') && ko_module_installed('groups') && ($access['groups']['ALL'] > 2 || $access['groups'][ko_get_setting('daten_gs_pid')] > 2)) {
		if($e['gs_gid']) {
			if(!is_array($all_groups)) ko_get_groups($all_groups);
			$ml = ko_groups_get_motherline(ko_groups_decode($e['gs_gid'], 'group_id'), $all_groups);
			$group_desc = ko_groups_decode((sizeof($ml) > 1 ? 'g'.implode(':g', $ml).':' : '').$e['gs_gid'], 'group_desc_full');
			$desc2 = '<a href="'.$ko_path.'groups/index.php?action=edit_group&id='.ko_groups_decode($e['gs_gid'], 'group_id').'">'.$group_desc.'</a>';
		} else {
			$desc2 = '';
		}
		$group[0]['groups'][++$gc] = array('titel' => getLL('daten_group_subscription'), 'state' => ($e['gs_gid'] ? 'open' : 'closed'), 'group' => TRUE);
		$group[0]['groups'][$gc]['rows'][$rowcounter]['inputs'][0] = array('desc' => getLL('daten_group_subscription_gid'),
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

		$group[0]['groups'][++$gc] = array('titel' => getLL('daten_form_rota_title'), 'state' => 'closed', 'group' => TRUE);
		$group[0]['groups'][$gc]['rows'][$rowcounter++]['inputs'][0] = array('desc' => '',
																 'columnWidth' => 12,
																 'type' => 'html',
																 'value' => $code,
																 'colspan' => 'colspan="2"',
																 );
	}


	//Reservationen
	if(ko_module_installed("reservation")) {

		//Prepare dynsoubleselect for res items
		kota_ko_reservation_item_id_dynselect($values, $descs, 2);

		// Only show if there is at least one resitem
		if (sizeof($values) > 0) {
			$group[0]['groups'][++$gc] = array("titel" => getLL("module_reservation"), "state" => 'open', 'group' => TRUE);

			//Prepare dynsoubleselect for res items
			kota_ko_reservation_item_id_dynselect($values, $descs, 2);
			$tpl_res_values = $tpl_res_descs = array();
			foreach ($values as $vid => $value) {
				$tpl_res_values[] = $vid;
				$suffix = is_array($value) ? "-->" : "";
				$tpl_res_descs[] = $descs[$vid] . $suffix;
			}

			//Add inputs for resitems and res_[start|stop]time
			$group[0]['groups'][$gc]["rows"][$rowcounter]["inputs"][0] = array("desc" => getLL("daten_linked_reservations"),
				"type" => "doubleselect",
				"js_func_add" => "resgroup_doubleselect_add",
				"name" => "sel_do_res",
				"values" => $tpl_res_values,
				"descs" => $tpl_res_descs,
				"avalues" => $do_res_values,
				"avalue" => implode(",", $do_res_values),
				"adescs" => $do_res_output,
				"params" => 'size="7"',
				'add_class' => 'res-conflict-field',
			);
			$responsibleForResData = array_merge(array('value' => $responsibleForRes), kota_get_form('ko_event', 'responsible_for_res'));
			$group[0]['groups'][$gc]['rows'][$rowcounter++]['inputs'][1] = array(
				'desc' => getLL('kota_ko_event_responsible_for_res') . ' *',
				'name' => 'sel_responsible_for_res',
				'type' => 'select',
				'value' => $responsibleForResData['value'],
				'values' => $responsibleForResData['values'],
				'descs' => $responsibleForResData['descs'],
			);
			$t = ko_multiedit_formular('ko_reservation', $EVENTS_SHOW_RES_FIELDS, 0, '', '', TRUE, '', TRUE, 'formular', '1', FALSE, TRUE);
			foreach ($t[0]['row'] as $row) {
				foreach ($row['inputs'] as $k => $input) {
					list($t1, $field, $t2) = explode('][', $input['name']);
					$input['name'] = 'res_' . $field;
					if (in_array($field, array('startzeit', 'endzeit'))) {
						$input['value'] = sql_zeit(${'res_' . $field});
					} else if ($input['type'] == 'checkbox') {
						$input['params'] = ${'res_' . $field} ? 'checked="checked"' : '';
					} else {
						$input['value'] = ${'res_' . $field};
					}
					$group[0]['groups'][$gc]['rows'][$rowcounter]['inputs'][$k] = $input;
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
			$group[0]['groups'][$gc]["rows"][$rowcounter]["inputs"][0] = array("desc" => getLL("kota_ko_event_startdatum") . ' *',
				"type" => "select",
				"name" => "res_startdatum_delta",
				"values" => $values,
				"descs" => $descs,
				"value" => $res_startdatum_delta,
				'add_class' => 'res-conflict-field',
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
			$group[0]['groups'][$gc]["rows"][$rowcounter++]["inputs"][1] = array("desc" => getLL("kota_ko_event_enddatum") . ' *',
				"type" => "select",
				"name" => "res_enddatum_delta",
				"values" => $values,
				"descs" => $descs,
				"value" => $res_enddatum_delta,
				'add_class' => 'res-conflict-field',
			);

			$group[0]['groups'][++$gc] = array("titel" => getLL("res_conflicts"), "state" => "open", 'group' => TRUE, 'name' => 'res-conflicts', 'appearance' => 'danger');
			$group[0]['groups'][$gc]['rows'][$rowcounter++]['inputs'][0] = array(
				'desc' => '',
				'type' => 'html',
				'value' => '<script>$("#group_res-conflicts").hide();</script>',
			);
		}
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

	$smarty->display("ko_formular2.tpl");

	//  check if do_notify field should be hidden
	if (isset($_POST["koi"]["ko_event"])) $tgId = $_POST["koi"]["ko_event"]['eventgruppen_id'][$id];
	else if ($mode == 'edit') {
		ko_get_event_by_id($id, $event);
		$tgId = $event['eventgruppen_id'];
	} else $tgId = NULL;
	if ($tgId) ko_get_eventgruppe_by_id($tgId, $tg);
	else {
		$tg = NULL;
		$id = 0;
	}
	if (!$tg['notify']) print "<script>$(document.getElementsByName('koi[ko_event][do_notify][".$id."]')[0]).closest('.formular-cell').hide();</script>";
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

	$KOTA["ko_eventgruppen"]["room"]["form"]["values"] = [""];
	$KOTA["ko_eventgruppen"]["room"]["form"]["descs"] = [""];

	$rooms = db_select_data("ko_event_rooms","WHERE 1=1", "id,title", "ORDER BY title ASC");
	foreach($rooms as $room) {
		$KOTA["ko_eventgruppen"]["room"]["form"]["values"][] = $room['id'];
		$KOTA["ko_eventgruppen"]["room"]["form"]["descs"][] = $room['title'];
	}

	$form_data["title"] = $mode == "neu" ? getLL("daten_new_eventgroup") : getLL("daten_edit_eventgroup");
	$form_data["submit_value"] = getLL("save");
	$form_data["action"] = $mode == "neu" ? "submit_neue_gruppe" : "submit_edit_gruppe";
	$form_data["cancel"] = "all_groups";

	ko_multiedit_formular("ko_eventgruppen", "", $id, "", $form_data);
}//ko_formular_termingruppe()




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
	$frmgroup[$gc]['tab'] = True;

	$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('daten_settings_default_view'),
			'type' => 'select',
			'name' => 'sel_daten',
			'values' => array('all_events', 'show_cal_monat', 'show_cal_woche'),
			'descs' => array(getLL('submenu_daten_all_events'), getLL('submenu_daten_cal_monat'), getLL('submenu_daten_cal_week')),
			'value' => ko_html(ko_get_userpref($_SESSION['ses_userid'], 'default_view_daten'))
			);
	$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('admin_settings_limits_numberof_events'),
			'type' => 'text',
			'params' => 'size="10"',
			'name' => 'txt_limit_daten',
			'value' => ko_html(ko_get_userpref($_SESSION['ses_userid'], 'show_limit_daten'))
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
	$frmgroup[$gc]["row"][$rowcounter++]["inputs"][0] = array("desc" => getLL("daten_settings_monthly_title"),
			"type" => "select",
			"name" => "sel_monthly_title",
			'values' => array('eventgruppen_id', 'title', 'kommentar', 'both', 'eventgruppen_id_kommentar', 'eventgruppen_id_kommentar2', 'title_kommentar', 'title_kommentar2'),
			'descs' => array(getLL('kota_ko_event_eventgruppen_id'), getLL('kota_ko_event_title'), getLL('kota_ko_event_kommentar'), getLL('kota_ko_event_eventgruppen_id').'+'.getLL('kota_ko_event_title'), getLL('kota_ko_event_eventgruppen_id').'+'.getLL('kota_ko_event_kommentar'), getLL('kota_ko_event_eventgruppen_id').'+'.getLL('kota_ko_event_kommentar2'), getLL('kota_ko_event_title').'+'.getLL('kota_ko_event_kommentar'), getLL('kota_ko_event_title').'+'.getLL('kota_ko_event_kommentar2')),
			"value" => $value,
			);

	$value = ko_get_userpref($_SESSION['ses_userid'], 'daten_mark_sunday');
	if (!$value) $value = 0;
	$frmgroup[$gc]["row"][$rowcounter]["inputs"][0] = array("desc" => getLL("daten_settings_mark_rows"),
			'type' => 'select',
			'name' => 'sel_mark_sunday',
			'values' => array(0, 1, 2),
			'descs' => array(getLL("daten_settings_mark_nothing"), getLL("daten_settings_mark_sunday"), getLL("daten_settings_mark_with_eg_color")),
			'value' => $value,
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
				'type' => 'select',
				'name' => 'sel_show_res_in_tooltip',
				'values' => [0, 1, 2],
				'descs' => [
					getLL('daten_res_tooltip_no'),
					getLL('daten_res_tooltip_yes_1'),
					getLL('daten_res_tooltip_yes_2'),
				],
				'value' => $value == '' ? 0 : $value,
				);
	}
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
	$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('daten_settings_export_show_legend'),
			'type' => 'switch',
			'name' => 'sel_export_show_legend',
			'label_0' => getLL('no'),
			'label_1' => getLL('yes'),
			'value' => $value == '' ? 0 : $value,
			);

	$value = ko_get_userpref($_SESSION['ses_userid'], 'daten_name_in_pdffooter');
	$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('daten_settings_name_in_pdffooter'),
			'type' => 'switch',
			'name' => 'sel_name_in_pdffooter',
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

	list($values, $descs) = ko_daten_get_user_assignable_cols();

	//Add taxonomy terms
	$values[] = 'terms';
	$descs[] = getLL('kota_ko_event_terms');

	//Add rota teams
	if(ko_module_installed('rota')) {
		$rota_teams = db_select_data('ko_rota_teams', "WHERE 1", '*', 'ORDER BY name ASC');
		if(!isset($access['rota'])) ko_get_access('rota');
		foreach($rota_teams as $rt) {
			$values[] = 'rotateam_'.$rt['id'];
			$descs[] = getLL('kota_ko_event_rotateam_'.$rt['id']);
		}
	}

	$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('daten_settings_ical_description_fields'),
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

	$avalues = $adescs = array();
	$value = ko_get_userpref($_SESSION['ses_userid'], 'daten_tooltip_fields');
	foreach(explode(',', $value) as $v) {
		if(!trim($v)) continue;
		$avalues[] = trim($v);
		$adescs[] = getLL('kota_ko_event_'.trim($v));
	}
	foreach ($values as $k => $v) {
		if (in_array($v, array('eventgruppen_id', 'title', 'startdatum', 'enddatum', 'startzeit', 'endzeit', 'room', 'kommentar')) || !$descs[$k]) {
			unset($values[$k], $descs[$k]);
		}
	}
	$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('daten_settings_tooltip_fields'),
		'type' => 'doubleselect',
		'js_func_add' => 'double_select_add',
		'name' => 'sel_tooltip_fields',
		'values' => $values,
		'descs' => $descs,
		'avalues' => $avalues,
		'avalue' => implode(',', $avalues),
		'adescs' => $adescs,
		'params' => 'size="6"',
		'show_moves' => TRUE,
	);

	// preset for filtering events in home
	$presets = ko_get_userpref($_SESSION['ses_userid'], '', 'daten_itemset');
	$presets=$presets?$presets:array();
	$globalPresets = ko_get_userpref(-1, '', 'daten_itemset');
	$globalPresets = $globalPresets?$globalPresets:array();
	$presetNames = array_merge(array(''), array_column($presets, 'key'), array_map(function($e){return "[G] {$e['key']}";}, $globalPresets));
	$values = $descs = $presetNames;
	$value = ko_get_userpref($_SESSION['ses_userid'], 'daten_fm_filter');
	$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('daten_settings_fm_filter'),
		'type' => 'select',
		'name' => 'sel_fm_filter',
		'values' => $values,
		'descs' => $descs,
		'value' => $value
	);

	if($access['daten']['ABSENCE'] > 0) {
		$help = ko_get_help("daten", "ical_links_absence");
		$label = "<strong>" . getLL('daten_settings_absence_ical_url') . "</strong>&nbsp;<span class=\"help-icon\">".$help['link']."</span>";
		if(ko_get_logged_in_person()) {
			$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = [
				'desc' => $label,
				'type' => 'text',
				'params' => 'size="10"',
				'name' => 'txt_absence_ical_url',
				'value' => ko_html(ko_get_userpref($_SESSION['ses_userid'], 'absence_ical_url'))
			];
		} else {
			$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = [
				'type' => 'label',
				'desc' => $label,
				'value' => getLL("daten_settings_absence_ical_url_no_leute_id")
			];
		}
	}

	//Global settings
	if($access['daten']['MAX'] > 3) {
		$gc++;
		$rowcounter = 0;
		$frmgroup[$gc]['titel'] = getLL('settings_title_global');
		$frmgroup[$gc]['tab'] = True;


		//Settings for group subscriptions
		if(in_array('groups', $MODULES)) {
			$gs_values = $gs_descs = array('');
			$groups = ko_groups_get_recursive(ko_get_groups_zwhere(), TRUE);
			if(!isset($all_groups) || !is_array($all_groups)) ko_get_groups($all_groups);
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

			$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = [
				'desc' => getLL('daten_settings_absence_color'),
				'name' => 'absence_color',
				'type' => 'color',
				'value' => ko_get_setting('absence_color'),
			];

		}

		// mandatory fields
		$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = kota_get_mandatory_fields_choices_for_sel('ko_event');

		$avalues = $adescs = array();
		$value = ko_get_setting('daten_mod_exclude_fields');
		foreach(explode(',', $value) as $v) {
			if(!$v) continue;
			$avalues[] = $v;
			$adescs[] = getLL('kota_ko_event_'.$v);
		}

		$exclude_dbfields = array('id', 'reservationen', 'gs_gid', 'cdate', 'last_change', 'lastchange_user', 'import_id', 'user_id');
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


		// ical settings
		$value = ko_get_setting('daten_show_ical_links_to_guest');
		$frmgroup[$gc]['row'][$rowcounter++]['inputs'][0] = array('desc' => getLL('daten_settings_show_ical_links_to_guest'),
			'type' => 'switch',
			'name' => 'sel_show_ical_links_to_guest',
			'value' => $value,
		);


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




	//Guest userprefs
	if(!isset($access['admin'])) ko_get_access('admin');
	if($access['admin']['ALL'] > 2) {
		$gc++;
		$rowcounter = 0;
		$frmgroup[$gc]['titel'] = getLL('settings_title_guest');
		$frmgroup[$gc]['tab'] = True;

		$uid = ko_get_guest_id();

		$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('daten_settings_default_view'),
				'type' => 'select',
				'name' => 'guest_sel_daten',
				'values' => array('all_events', 'show_cal_monat', 'show_cal_woche'),
				'descs' => array(getLL('submenu_daten_all_events'), getLL('submenu_daten_cal_monat'), getLL('submenu_daten_cal_week')),
				'value' => ko_html(ko_get_userpref($uid, 'default_view_daten'))
				);
		$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('admin_settings_limits_numberof_events'),
				'type' => 'text',
				'params' => 'size="10"',
				'name' => 'guest_txt_limit_daten',
				'value' => ko_html(ko_get_userpref($uid, 'show_limit_daten'))
				);

		$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('admin_settings_view_weekcal_start'),
				'type' => 'text',
				'params' => 'size="10"',
				'name' => 'guest_txt_cal_woche_start',
				'value' => ko_html(ko_get_userpref($uid, 'cal_woche_start'))
				);
		$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('admin_settings_view_weekcal_stop'),
				'type' => 'text',
				'params' => 'size="10"',
				'name' => 'guest_txt_cal_woche_end',
				'value' => ko_html(ko_get_userpref($uid, 'cal_woche_end'))
				);

		$frmgroup[$gc]['row'][$rowcounter++]['inputs'][0] = array('type' => '   ');

		$value = ko_get_userpref($uid, 'daten_monthly_title');
		$frmgroup[$gc]["row"][$rowcounter]["inputs"][0] = array("desc" => getLL("daten_settings_monthly_title"),
			"type" => "select",
			"name" => "guest_sel_monthly_title",
			'values' => array('eventgruppen_id', 'title', 'kommentar', 'both', 'eventgruppen_id_kommentar', 'eventgruppen_id_kommentar2', 'title_kommentar', 'title_kommentar2'),
			'descs' => array(getLL('kota_ko_event_eventgruppen_id'), getLL('kota_ko_event_title'), getLL('kota_ko_event_kommentar'), getLL('kota_ko_event_eventgruppen_id').'+'.getLL('kota_ko_event_title'), getLL('kota_ko_event_eventgruppen_id').'+'.getLL('kota_ko_event_kommentar'), getLL('kota_ko_event_eventgruppen_id').'+'.getLL('kota_ko_event_kommentar2'), getLL('kota_ko_event_title').'+'.getLL('kota_ko_event_kommentar'), getLL('kota_ko_event_title').'+'.getLL('kota_ko_event_kommentar2')),
			"value" => $value,
		);

		$value = ko_get_userpref($uid, 'daten_mark_sunday');
		if (!$value) $value = 0;
		$frmgroup[$gc]["row"][$rowcounter++]["inputs"][1] = array("desc" => getLL("daten_settings_mark_rows"),
			'type' => 'select',
			'name' => 'guest_sel_mark_sunday',
			'values' => array(0, 1, 2),
			'descs' => array(getLL("daten_settings_mark_nothing"), getLL("daten_settings_mark_sunday"), getLL("daten_settings_mark_with_eg_color")),
			'value' => $value,
		);

		$value = ko_get_userpref($uid, 'daten_no_cals_in_itemlist');
		$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('daten_settings_no_cals_in_itemlist'),
			'type' => 'switch',
			'name' => 'guest_sel_no_cals_in_itemlist',
			'label_0' => getLL('no'),
			'label_1' => getLL('yes'),
			'value' => $value == '' ? 0 : $value,
		);
		if(ko_module_installed('reservation', $uid)) {
			$value = ko_get_userpref($uid, 'daten_show_res_in_tooltip');
			$frmgroup[$gc]['row'][$rowcounter]['inputs'][1] = array('desc' => getLL('daten_settings_show_res_in_tooltip'),
				'type' => 'switch',
				'name' => 'guest_sel_show_res_in_tooltip',
				'label_0' => getLL('no'),
				'label_1' => getLL('yes'),
				'value' => $value == '' ? 0 : $value,
			);
		}
		$rowcounter++;

		$frmgroup[$gc]['row'][$rowcounter++]['inputs'][0] = array('type' => '   ');

		$value = ko_get_userpref($uid, 'daten_pdf_show_time');
		$frmgroup[$gc]["row"][$rowcounter]["inputs"][0] = array("desc" => getLL("daten_settings_pdf_show_time"),
				"type" => "select",
				"name" => "guest_sel_pdf_show_time",
				'values' => array('2', '1', '0'),
				'descs' => array(getLL('daten_settings_pdf_show_time_2'), getLL('daten_settings_pdf_show_time_1'), getLL('no')),
				"value" => $value,
				);
		$value = ko_get_userpref($uid, 'daten_pdf_use_shortname');
		$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('daten_settings_pdf_use_shortname'),
				'type' => 'switch',
				'name' => 'guest_sel_pdf_use_shortname',
				'label_0' => getLL('no'),
				'label_1' => getLL('yes'),
				'value' => $value == '' ? 0 : $value,
				);

		$value = ko_get_userpref($uid, 'daten_pdf_week_start');
		$monday = date_find_last_monday(date('Y-m-d'));
		$daynames[] = strftime('%A', strtotime($monday));
		for($i=1; $i<7; $i++) {
			$daynames[] = strftime('%A', strtotime(add2date($monday, 'tag', $i, TRUE)));
		}
		$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('daten_settings_pdf_week_start'),
				'type' => 'select',
				'name' => 'guest_sel_pdf_week_start',
				'values' => array(1,2,3,4,5,6,0),
				'descs' => $daynames,
				'value' => $value,
				);
		$value = ko_get_userpref($uid, 'daten_pdf_week_length');
		$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('daten_settings_pdf_week_length'),
				'type' => 'select',
				'name' => 'guest_sel_pdf_week_length',
				'values' => array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21),
				'descs' => array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21),
				'value' => $value,
				);

		$value = ko_get_userpref($uid, 'daten_export_show_legend');
		$frmgroup[$gc]['row'][$rowcounter++]['inputs'][0] = array('desc' => getLL('daten_settings_export_show_legend'),
				'type' => 'switch',
				'name' => 'guest_sel_export_show_legend',
				'label_0' => getLL('no'),
				'label_1' => getLL('yes'),
				'value' => $value == '' ? 0 : $value,
				);

		$frmgroup[$gc]['row'][$rowcounter++]['inputs'][0] = array('type' => '   ');

		$avalues = $adescs = array();
		$value = ko_get_userpref($uid, 'daten_ical_description_fields');
		foreach(explode(',', $value) as $v) {
			if(!$v) continue;
			$avalues[] = $v;
			$adescs[] = getLL('kota_ko_event_'.$v);
		}

		list($values, $descs) = ko_daten_get_user_assignable_cols();
		//Add rota teams
		if(ko_module_installed('rota', $uid)) {
			$rota_teams = db_select_data('ko_rota_teams', "WHERE 1", '*', 'ORDER BY name ASC');
			if(!isset($access['rota'])) ko_get_access('rota');
			foreach($rota_teams as $rt) {
				$values[] = 'rotateam_'.$rt['id'];
				$descs[] = getLL('kota_ko_event_rotateam_'.$rt['id']);
			}
		}

		$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('daten_settings_ical_description_fields'),
				'type' => 'doubleselect',
				 'js_func_add' => 'double_select_add',
				 'name' => 'guest_sel_ical_description_fields',
				 'values' => $values,
				 'descs' => $descs,
				 'avalues' => $avalues,
				 'avalue' => implode(',', $avalues),
				 'adescs' => $adescs,
				 'params' => 'size="6"',
				 'show_moves' => TRUE,
				);

		$avalues = $adescs = array();
		$value = ko_get_userpref($uid, 'daten_tooltip_fields');
		foreach(explode(',', $value) as $v) {
			if(!trim($v)) continue;
			$avalues[] = trim($v);
			$adescs[] = getLL('kota_ko_event_'.trim($v));
		}
		foreach ($values as $k => $v) {
			if (in_array($v, array('eventgruppen_id', 'title', 'startdatum', 'enddatum', 'startzeit', 'endzeit', 'room', 'kommentar')) || !$descs[$k]) {
				unset($values[$k], $descs[$k]);
			}
		}
		$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('daten_settings_tooltip_fields'),
			'type' => 'doubleselect',
			'js_func_add' => 'double_select_add',
			'name' => 'guest_sel_tooltip_fields',
			'values' => $values,
			'descs' => $descs,
			'avalues' => $avalues,
			'avalue' => implode(',', $avalues),
			'adescs' => $adescs,
			'params' => 'size="6"',
			'show_moves' => TRUE,
		);
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
	global $KOTA;

	$notifier = koNotifier::Instance();

	ko_get_eventgruppe_by_id($data["eventgruppen_id"], $eventgroup);
	if(!$eventgroup["name"]) {
		$notifier->addError(8);
		return TRUE;
	}

	//Check date
	if(!check_datum($data["startdatum"])) {
		$notifier->addError(1);
		return TRUE;
	}

	if($data['enddatum'] != '') {
		if(!check_datum($data['enddatum'])) {
			$notifier->addError(1);
			return TRUE;
		}
		//Events longer than a day without endtime must be all day events
		if($data['enddatum'] != $data['startdatum'] && ($data['endzeit'] == '' || !check_zeit($data['endzeit'])) ) {
			$data['startzeit'] = $data['endzeit'] = '';
		}

		//Startdatum < Enddatum
		$date_s = explode('.', $data['startdatum']);
		$date_e = explode('.', $data['enddatum']);
		if( (int)($date_s[2] . str_to_2($date_s[1]) . str_to_2($date_s[0])) > (int)($date_e[2] . str_to_2($date_e[1]) . str_to_2($date_e[0])) ) {
			$notifier->addError(4);
			return TRUE;
		};
	}//if(data['enddatum'] != '')

	//Check time
	if(trim($data['startzeit']) == '' || !check_zeit($data['startzeit'])) {
		//if first time is empty, set second one empty as well (all day event)
		$data['endzeit'] = '';
	} else {
		if($data['endzeit'] != '') {
			if(!check_zeit($data['endzeit'])) {
				$notifier->addError(2);
				return TRUE;
			}
			//Bei eintägigem Anlass muss die Endzeit grösser sein als die Startzeit
			$time_s1 = str_replace(':', '', $data['startzeit']);
			$time_s2 = str_replace(':', '', $data['endzeit']);
			if((trim($data['enddatum']) == '' || $data['startdatum'] == $data['enddatum']) && (int)$time_s1 > (int)$time_s2) {
				$notifier->addError(2);
				return TRUE;
			};
		}
	}

	//check for all the mandatory fields
	if (!kota_check_mandatory_fields('ko_event', $data['id'], $data)) {
		$notifier->addError(60);
		return TRUE;
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
	$code .= ko_calendar_mwselect();
	$code .= '</div>';
	$code .= '</div>';
	$code .= '</div>';

	//Add PDF link
	$code .= '<table style="margin-left:12px" cellspacing="0" cellpadding="3">';
	$code .= '<tr><td style="border-left-style:solid;border-left-width:1px">&nbsp;</td></tr>';
	$code .= '<tr><td style="border-left-style:solid;border-left-width:1px;border-bottom-width:1px;border-bottom-style:solid;">';
	$code .= '<a href="" onclick="sendReq(\'inc/ajax.php\', \'action\', \'pdfcalendar\', show_box); return false;">';
	$code .= '&nbsp;<i class="fa fa-file-pdf-o"></i>&nbsp;'.getLL("res_list_footer_pdf_label").'</a>';
	$code .= '<span name="daten_pdf_link" id="daten_pdf_link">&nbsp;</span>';
	$code .= '</td></tr>';
	$code .= '</table>';

	print $code;
}//ko_daten_calendar()




function do_del_termin($del_id) {
	global $access, $do_action;

	ko_get_event_by_id($del_id, $del_event);
	if($access['daten'][$del_event['eventgruppen_id']] < 2) return FALSE;
	ko_get_eventgruppe_by_id($del_event["eventgruppen_id"], $del_eventgruppe);
	if ($del_eventgruppe['type'] == 3) {
		koNotifier::Instance()->addWarning(1);
		return FALSE;
	}

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

		//Delete open res moderations for this event
		db_delete_data('ko_reservation_mod', "WHERE `_event_id` = '$del_id'");

		//Delete group with group subscriptions if no members
		ko_daten_gs_delete_group($del_event);

		//Delete event itself
		db_delete_data("ko_event", "WHERE `id` = '$del_id'");

		//Delete mod entries for this event
		db_delete_data("ko_event_mod", "WHERE `_event_id` = '$del_id'");

		ko_taxonomy_delete_node($del_id, "ko_event");
		ko_taxonomy_delete_node($del_id, "ko_event_mod");

		//Send notification
		ko_daten_send_notification($del_event, 'delete');

		ko_log_diff('delete_termin', $del_event);

		$hookData = array('del_id' => $del_id, 'del_event' => $del_event);
		hook_function_inline('do_del_termin', $hookData);

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



function ko_daten_store_moderation($data, $forceNoEmail=FALSE) {
	global $MAIL_TRANSPORT;
	$txt = array();
	$egs = db_select_data("ko_eventgruppen", "WHERE 1=1", "*");

	$mod_txt = array();

	foreach($data as $event) {
		$eg = $egs[$event["eventgruppen_id"]];

		unset($event["id"]);
		unset($event['import_id']);
		// Fow now: drop event program for moderated events
		unset($event['programEntries']); // TODO : maybe support program entries in moderated events
		$event["_user_id"] = $_SESSION["ses_userid"];
		$event["_crdate"] = strftime("%Y-%m-%d %H:%M:%S", time());

		$taxonomy_terms = explode(",", format_userinput($event['terms'], "intlist"));
		unset($event["terms"]);

		$new_id = db_insert_data("ko_event_mod", $event);

		if(ko_module_installed("taxonomy")) {
			ko_taxonomy_clear_terms_on_node("ko_event_mod", $new_id);
			ko_taxonomy_attach_terms_to_node($taxonomy_terms, "ko_event_mod", $new_id);
		}

		if(!$forceNoEmail && $eg["moderation"] == 2) {
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
	$reply_to = '';
	if($_SESSION["ses_userid"] != ko_get_guest_id()) {
		$p = ko_get_logged_in_person();
		if($p['email']) {
			if($p['vorname'] || $p['nachname']) {
				$reply_to = array($p['email'] => trim($p['vorname'].' '.$p['nachname']));
			} else {
				$reply_to = array($p['email'] => $_SESSION['ses_username']);
			}
		}
	}

	//Send email to moderators
	$done = array();
	foreach($mod_txt as $gid => $txt) {
		if(!$txt || !$gid) continue;
		$mailtext = str_replace("[DATA]", "\n\n".$txt, getLL("daten_email_mod_text"));
		$mods = ko_get_moderators_by_eventgroup($gid);
		foreach($mods as $mod) {
			if(!$mod['email'] || in_array($mod['email'], $done)) continue;

			ko_send_mail(
				'',
				$mod['email'],
				getLL('email_subject_prefix') . $subject,
				ko_emailtext($mailtext),
				null,
				null,
				null,
				$reply_to);
			$done[] = $mod['email'];
		}
	}
}//ko_daten_store_moderation()


/**
 * @param int $id
 * @param array $data event item
 * @param bool $forceIgnoreRes
 * @return bool
 */
function ko_daten_update_event($id, &$data, $forceIgnoreRes = FALSE) {
	global $access, $EVENTS_SHOW_RES_FIELDS;

	$error = FALSE;
	$ok = TRUE;

	$userid = $data["_user_id"] ? $data["_user_id"] : $_SESSION["ses_userid"];
	$resp_for_res = $data['responsible_for_res'] ? $data['responsible_for_res'] : $userid;

	ko_get_event_by_id($id, $event);
	ko_get_eventgruppe_by_id($data["eventgruppen_id"], $eg);
	$event["startzeit"] = sql_zeit($event["startzeit"]);
	$event["endzeit"] = sql_zeit($event["endzeit"]);

	$current_reservations = db_select_data("ko_reservation", "WHERE `id` IN ('" . implode("','", explode(",", $event["reservationen"])) . "')", "*");
	if (ko_module_installed('reservation') && (sizeof($current_reservations) > 0 || $data["resitems"] != "") && !$forceIgnoreRes) {
		//Get access rights for reservations
		ko_get_access('reservation');

		//Check for changes which force an update for the reservations for this event
		$update_res = [];
		foreach (explode(",", $event["reservationen"]) as $res_id) {
			if (!$res_id) continue;
			$r = db_select_data('ko_reservation', 'where id = ' . $res_id, '*', '', '', TRUE, TRUE);
			if ($r["startdatum"] != $data['res_startdatum']) $update_res["startdatum"] = $data['res_startdatum'];
			if ($r["enddatum"] != $data['res_enddatum']) $update_res["enddatum"] = $data['res_enddatum'];
			if ($r['user_id'] != $resp_for_res) $update_res['user_id'] = $resp_for_res;
		}

		if ($event["eventgruppen_id"] != $data["eventgruppen_id"] || $event["title"] != $data["title"]) {
			$update_res['zweck'] = ko_daten_get_zweck_for_res($data);
		}
		foreach ($EVENTS_SHOW_RES_FIELDS as $f) {
			if ($data['res_' . $f]) $update_res[$f] = $data['res_' . $f];
		}

		$store_reservations = $store_mod_reservations = $update_reservations = [];
		$event_res_ids = [];
		foreach (explode(",", $data["resitems"]) as $resitem_id) {
			$found = FALSE;
			foreach ($current_reservations as $res_id => $current_reservation) {
				if ($current_reservation["item_id"] == $resitem_id) {
					$found = TRUE;
					$event_res_ids[] = $res_id;
					//Overlapping check (Don't move event if reservations can not be stored)
					if (FALSE === ko_res_check_double($resitem_id, $data['res_startdatum'], $data['res_enddatum'], $data['res_startzeit'], $data['res_endzeit'], $double_error_txt, $res_id)) {
						$ok = FALSE;
						koNotifier::Instance()->addError(4, '', [$double_error_txt], 'reservation');
						$error = TRUE;
					}
					$update_reservations[$res_id] = $update_res;
					unset($current_reservations[$res_id]);
				}
			}

			// when the resitem is moderated, the reservation might need to be saved as mod
			ko_get_resitems($all_resitems);
			foreach ($update_reservations AS $res_id => $update_reservation) {
				$current_reservation = db_select_data("ko_reservation", "WHERE id = '" . $res_id . "'", "*", "", "LIMIT 1", TRUE, TRUE);
				$current_resitem = $all_resitems[$current_reservation['item_id']];
				if ($current_resitem['moderation'] > 0) {
					if($access['reservation'][$resitem_id] >= 4) {
						// don't save as mod reservation
						continue;
					}

					unset($current_reservation['id']);
					$current_reservation["_event_id"] = $event['id'];
					$current_reservation['zweck'] = ko_daten_get_zweck_for_res($data);

					$needs_update = FALSE;
					foreach ($EVENTS_SHOW_RES_FIELDS as $field) {
						if (substr($field,-4) == "zeit" && strlen($data["res_" . $field]) == 5) $data["res_" . $field] .= ":00";
						if ($data["res_" . $field] != $current_reservation[$field]) $needs_update = TRUE;
						if ($data['res_' . $field]) $current_reservation[$field] = $data['res_' . $field];
					}

					if ($needs_update == FALSE) continue;

					$store_mod_reservations[] = $current_reservation;
					db_delete_data("ko_reservation", "WHERE id = '" . $res_id . "'");
					unset($update_reservations[$res_id]);
				}
			}

			if (!$found) {
				if ($access['reservation'][$resitem_id] < 2) continue;
				//Add new reservation
				$p = ko_get_logged_in_person();

				$phonetype = ko_get_userpref($_SESSION['ses_userid'], 'res_prefill_tel');
				if (!$phonetype) $phonetype = ($p['telp']?'telp': ($p['telg']?'telg':'natel'));

				$res_data = [
					"item_id" => $resitem_id,
					"startdatum" => $data['res_startdatum'],
					"enddatum" => $data['res_enddatum'],
					'zweck' => ko_daten_get_zweck_for_res($data),
					"name" => $p["vorname"] . " " . $p["nachname"],
					"email" => $p["email"],
					"telefon" => $p[$phonetype],
					"user_id" => $resp_for_res,
					];

				foreach ($EVENTS_SHOW_RES_FIELDS as $f) {
					if ($data['res_' . $f]) $res_data[$f] = $data['res_' . $f];
				}

				$resitem = db_select_data("ko_resitem", "WHERE `id` = '$resitem_id'", "*", "", "", TRUE);
				//Check for res colision
				ko_res_check_double($resitem_id, $res_data["startdatum"], $res_data["enddatum"], $res_data["startzeit"], $res_data["endzeit"], $double_error_txt);
				if ($double_error_txt) {
					koNotifier::Instance()->addError(4, '', [$double_error_txt], 'reservation');
					$error = TRUE;
				} else {
					//Check for moderation
					if ($resitem["moderation"] > 0 && $access['reservation'][$resitem_id] < 4) {
						$res_data["_event_id"] = $id;
						$store_mod_reservations[] = $res_data;
					} else {
						unset($res_data["_event_id"]);
						$store_reservations[] = $res_data;
					}
				}
			}//if(!found)
		}//foreach(resitems)

		//Store new reservations
		if ($ok) {
			if (sizeof($store_reservations) > 0) {
				$send_user_email = ko_get_userpref($userid, 'do_res_email') != 0;
				$new_ids = ko_res_store_reservation($store_reservations, $send_user_email);
				$event_res_ids = array_merge($event_res_ids, $new_ids);
			}
			if (sizeof($store_mod_reservations) > 0) {
				ko_res_store_moderation($store_mod_reservations);
			}
			if (sizeof($update_reservations) > 0) {
				foreach ($update_reservations as $res_id => $update_res) {
					//Log-Meldung erstellen
					$update_res['last_change'] = date('Y-m-d H:i:s');
					ko_log_diff("edit_res", $update_res, db_select_data('ko_reservation', "WHERE `id` = '{$res_id}'", '*', '', '', TRUE));

					db_update_data('ko_reservation', "WHERE `id` = '$res_id'", $update_res);
				}
			}
			//Delete reservations not selected anymore
			if (sizeof($current_reservations) > 0) {
				foreach ($current_reservations as $current_reservation) db_delete_data("ko_reservation", "WHERE `id` = '" . $current_reservation["id"] . "'");
			}

			$data["reservationen"] = implode(",", $event_res_ids);
		}
	}//if(handle_res)

	//Update event itself
	if ($ok) {
		unset($data['resitems']);
		unset($data['id']);
		foreach ($data as $key => $value) {
			if (substr($key, 0, 1) == '_') unset($data[$key]);
		}
		foreach ($EVENTS_SHOW_RES_FIELDS as $f) {
			unset($data['res_' . $f]);
		}
		unset($data['responsible_for_res']);

		//Group subscription
		if (isset($data['gs_gid']) && ko_get_setting('daten_gs_pid')) {
			if ($data['gs_gid'] == 1 && $event['gs_gid'] == '') {  //Newly selected
				$data['gs_gid'] = ko_daten_gs_get_gid_for_event($event);
			} else if ($event['gs_gid'] != '' && $data['gs_gid'] == 0) {  //Deselected
				$data['gs_gid'] = '';
				ko_daten_gs_delete_group($event);
			} else if ($data['gs_gid'] == 1 && $event['gs_gid'] != '') {  //Still selected with no change
				unset($data['gs_gid']);  //Unset gs_gid so it won't be updated and set to '1'
			}
		}

		//Add last_change
		$data['last_change'] = date('Y-m-d H:i:s');
		$data['lastchange_user'] = $_SESSION['ses_userid'];

		// unset res_dates
		if (isset($data['res_startdatum'])) unset($data['res_startdatum']);
		if (isset($data['res_enddatum'])) unset($data['res_enddatum']);

		if (ko_module_installed("taxonomy")) {
			$taxonomy_terms = explode(",", format_userinput($data["terms"], "intlist"));
			ko_taxonomy_clear_terms_on_node("ko_event", $id);
			ko_taxonomy_attach_terms_to_node($taxonomy_terms, "ko_event", $id);
			unset($data['terms']);
		}

		db_update_data("ko_event", "WHERE `id` = '$id'", $data);
		unset($data['last_change']);
		unset($data['lastchange_user']);

		//Call KOTA post
		$ids = [$id];
		$columns = array_keys($data);
		$do_save = 1;
		if (function_exists('kota_post_ko_event')) {
			eval("kota_post_ko_event(\$ids, \$columns, \$event, \$do_save);");
		}
		hook_kota_post('ko_event', ['table' => 'ko_event', 'ids' => $ids, 'columns' => $columns, 'old' => $event, 'do_save' => $do_save]);

		//Send notification
		ko_daten_send_notification($data, 'update', $event);

		//Log-Meldung erstellen
		ko_log_diff("edit_termin", array_merge(array("name" => $eventgroup["name"]), $data), $event);
	}

	return $error;
}//ko_daten_update_event()


function ko_daten_import($state) {
	global $ko_path, $smarty;
	global $all_groups;
	global $access;
	global $BOOTSTRAP_COLS_PER_ROW;

	switch($state) {
		case 1:
			$ses = $_SESSION['import_daten_csv_1'];

			$rowcounter = 0;
			$gc = 0;

			// Create Field for event group
			$eg = array_merge(
				array(
					"desc" => getLL("kota_ko_event_eventgruppen_id"),
					"type" => "select",
					"js_func_add" => "event_cal_select_add",
					"params" => 'size="5"',
					'new_row' => true,
					'name' => 'eventgroup_id',
				),
				kota_get_form("ko_event", "eventgruppen_id")
			);

			$eg["multiple"] = true;
			$values = $eg["values"];
			$descs = $eg["descs"];
			unset($eg["values"]);
			unset($eg["descs"]);
			if($ses['eventgroup_id']) {  //Current value given
				$eg["avalues"] = $eg["adescs"] = array();
				$eg["avalue"] = $ses['eventgroup_id'];
				//If current value is not found on top level then go through all lower levels to find it and display this level
				if(!in_array($ses['eventgroup_id'], array_keys($values))) {
					foreach($values as $vid => $value) {
						if(substr($vid, 0, 1) != "i") continue;
						if(in_array($ses['eventgroup_id'], $value)) {
							$values = array("i-" => "i-");
							//Add all values from this level
							foreach($value as $v) $values[$v] = $v;
							//Add link to go back up to the index
							$descs["i-"] = getLL("form_peopleselect_up");
							break;
						}
					}
				}//if(!in_array(row[col], values))
			}//if(row[col])
			//Build top level of select
			foreach($values as $vid => $value) {
				$eg["values"][] = $vid;
				$suffix = is_array($value) ? "-->" : "";
				$eg["descs"][] = $descs[$vid].$suffix;
			}

			$frmgroup[$gc]["row"][$rowcounter]["inputs"][0] = $eg;

			// CSV file
			$frmgroup[$gc]["row"][$rowcounter++]["inputs"][1] = array("desc" => getLL("leute_import_state1_csv"),
				"type" => "file",
				"name" => "csv",
			);
			$frmgroup[$gc]["row"][$rowcounter]["inputs"][0] = array("desc" => getLL("leute_import_state1_csv_separator"),
				"type" => "text",
				"name" => "txt_separator",
				"params" => 'size="6"',
				"value" => $ses['separator'] ? $ses['separator'] : ',',
			);
			$frmgroup[$gc]["row"][$rowcounter++]["inputs"][1] = array("desc" => getLL("leute_import_state1_csv_content_separator"),
				"type" => "text",
				"name" => "txt_content_separator",
				"params" => 'size="6"',
				"value" => isset($ses['content_separator']) ? $ses['content_separator'] : '&quot;',
			);
			$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('leute_import_state1_csv_first_line'),
				'type' => 'switch',
				'name' => 'chk_first_line',
				'value' => $ses['first_line'] == 1 ? '1' : '0',
			);
			$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('leute_import_state1_csv_file_encoding'),
				'type' => 'select',
				'name' => 'sel_file_encoding',
				'params' => 'size="0"',
				'values' => array('utf-8', 'latin1', 'macintosh'),
				'descs' => array('Unicode (UTF-8)', 'Latin1 (iso-8859-1)', 'Mac Roman'),
				'value' => $ses['file_encoding'],
			);

			$mandatoryFields = kota_get_mandatory_fields_choices_for_sel('ko_event');
			$mandatory_avalues = $mandatoryFields['avalues'];
			$mandatory_adescs = $mandatoryFields['adescs'];
			if (sizeof($mandatory_adescs) > 0) {
				$mandatoryFieldsString = implode(', ', $mandatory_adescs);
			} else {
				$mandatoryFieldsString = getLL('none');
			}
			$frmgroup[$gc]['row'][$rowcounter++]['inputs'][0] = array('desc' => getLL('daten_settings_mandatory'),
				'type' => 'html',
				'name' => 'mandatory_fields',
				'value' => $mandatoryFieldsString,
			);

			$smarty->assign("tpl_titel", getLL("daten_import_state_1"));
			$smarty->assign("tpl_hide_cancel", true);
			$smarty->assign("tpl_submit_value", getLL("next"));
			$smarty->assign("tpl_action", "importtwo");
			$smarty->assign("tpl_groups", $frmgroup);
			$smarty->display('ko_formular.tpl');
		break;  //1

		case 2:
			$ses1 = $_SESSION['import_daten_csv_1'];
			$ses2 = $_SESSION['import_daten_csv_2'];
			$data = $_SESSION["import_daten_data"];

			$rowcounter = 0;
			$gc = 0;

			if ($ses1['first_line']) {
				$labels = $data[0];
				$example = $data[1];
			} else {
				$labels = array();
				for ($i = 0; $i < sizeof($data[0]); $i++) {
					$labels[] = 'col_' . ($i + 1);
				}
				$example = $data[0];
			}

			list($values, $descs) = ko_daten_get_user_assignable_cols();
			array_unshift($values, '');
			array_unshift($descs, '');
			$html = '<table id="daten-import-mapping"><tbody>';

			$html .= '<tr>';
			$html .= '<td><label>'.getLL('daten_import_label_col').'</label></td>';
			$html .= '<td><label>'.getLL('daten_import_label_assign_field').'</label></td>';
			$html .= '<td><label>'.getLL('daten_import_label_value').'</label></td>';
			$html .= '</tr>';

			foreach ($example as $k => $line) {
				$label = $labels[$k];
				$html .= '<tr>';
				$html .= '<td>'.$label.'</td>';
				$html .= '<td>';
				$html .= '<select class="input-sm form-control" name="assign_field['.$k.']">';
				$active = $ses2['assign_field['.$k.']'];
				if (!$active) {
					if (in_array($label, $values)) {
						$active = $label;
					} else if (in_array($label, $descs)) {
						$key = array_search($label, $descs);
						$active = $values[$key];
					}
				}
				foreach ($values as $kk => $value) {
					$desc = $descs[$kk];
					$html .= '<option value="'.$value.'"'.($value == $active ? ' selected="selected"' : '').'>'.$desc.'</option>';
				}
				$html .= '</select>';
				$html .= '</td>';
				$html .= '<td>'.$line.'</td>';
				$html .= '</tr>';
				$rowcounter++;
			}

			$html .= '</tbody></table>';

			$frmgroup[$gc]['row'][$rowcounter++]['inputs'][0] = array('desc' => '',
				'type' => 'html',
				'name' => 'data_table',
				'value' => $html,
				'columnWidth' => $BOOTSTRAP_COLS_PER_ROW,
			);

			$mandatoryFields = kota_get_mandatory_fields_choices_for_sel('ko_event');
			$mandatory_avalues = $mandatoryFields['avalues'];
			$mandatory_adescs = $mandatoryFields['adescs'];
			if (sizeof($mandatory_adescs) > 0) {
				$mandatoryFieldsString = implode(', ', $mandatory_adescs);
			} else {
				$mandatoryFieldsString = getLL('none');
			}
			$frmgroup[$gc]['row'][$rowcounter++]['inputs'][0] = array('desc' => getLL('daten_settings_mandatory'),
				'type' => 'html',
				'name' => 'mandatory_fields',
				'value' => $mandatoryFieldsString,
			);

			$smarty->assign("tpl_titel", getLL("daten_import_state_2"));
			$smarty->assign("tpl_hide_cancel", true);
			$smarty->assign("tpl_submit_value", getLL("next"));
			$smarty->assign("tpl_action", "importthree");
			$smarty->assign("tpl_groups", $frmgroup);
			$smarty->display('ko_formular.tpl');

		break;  //2
	}
}//ko_daten_import()





function ko_daten_infotext($data, $allowHtml=TRUE) {
	$txt = "";
	$list = kota_get_list($data, "ko_event", $allowHtml);
	foreach($list as $key => $value) {
		if($key == getLL("kota_ko_event_enddatum") || $key == getLL("kota_ko_event_endzeit")) continue;
		if($value) $txt .= "$key: ".strip_tags(ko_unhtml($value))."\n";
	}

	return $txt;
}//ko_daten_infotext()



function ko_daten_store_event(&$data, $modResForConflicts=FALSE) {
	global $access, $EVENTS_SHOW_RES_FIELDS, $MAIL_TRANSPORT;

	$modResForConflicts_ = $modResForConflicts;

	$error = false;
	//Get access rights for reservations (new reservations are done with current user, which is the moderator for moderations)
	ko_get_access('reservation');

	$resitems = db_select_data("ko_resitem", "WHERE 1=1", "*");
	$egs = db_select_data("ko_eventgruppen", "WHERE 1=1", "*");


	$txt3 = array();
	foreach($data as $e_id => $event) {
		// check whether we should create moderasted reservations in case of conflicts
		$modResForConflicts = $modResForConflicts_ || $event['_res_mod_on_conflict'];
		unset($event['_res_mod_on_conflict']);

		$last_res_userid = $res_userid;
		//Use the userid of the user given in data
		//If this is from a moderated event, then the reservations will be done as the original user and not the moderator
		$userid = $event["_user_id"] ? $event["_user_id"] : $_SESSION["ses_userid"];
		if ($event["_user_id"]) {
			$res_userid = $event["_user_id"];
		} else if ($event['responsible_for_res']) {
			$res_userid = $event['responsible_for_res'];
		} else {
			$res_userid = $_SESSION["ses_userid"];
		}

		//Only get settings again if this event is done by another userid
		if($res_userid != $last_res_userid) {
			//Get person data for user who created this event (currently logged in or the one who created the mod event)
			$p = ko_get_logged_in_person($res_userid);
			//Get user setting to send emails for reservations
			$send_user_email = ko_get_userpref($res_userid, 'do_res_email') != 0;
		}

		$eg = $egs[$event["eventgruppen_id"]];

		//Prepare reservations
		$event_res = $res_store = $res_mod = array();
		if($event["resitems"]) {
			//set data for reservations
			$res_data["startdatum"] = $event["res_startdatum"] ? $event['res_startdatum'] : $event['startdatum'];
			$res_data["enddatum"] = $event["res_enddatum"] ? $event['res_enddatum'] : $event['enddatum'];
			$res_data['zweck'] = ko_daten_get_zweck_for_res($event);
			$res_data["name"] = $p["vorname"]." ".$p["nachname"];
			$res_data["email"] = $p["email"];

			$phone = ko_get_userpref($res_userid, 'res_prefill_tel');
			if (!$phone) $phone = ($p['telp']?'telp': ($p['telg']?'telg':'natel'));
			$res_data["telefon"] = $p[$phone];
			$res_data["user_id"] = $res_userid;

			foreach($EVENTS_SHOW_RES_FIELDS as $f) {
				if(isset($event['res_'.$f]) && !in_array($event['res_'.$f], array('', '0'))) $res_data[$f] = $event['res_'.$f];
			}

			$res_mod = $res_store = array();
			foreach(explode(",", $event["resitems"]) as $r) {
				if(!$r) continue;
				$res_data = array("item_id" => $r)+$res_data;

				//Auf Doppelbelegung prüfen
				$saveModerated = FALSE;
				$do_res = TRUE;
				ko_res_check_double($r, $res_data["startdatum"], $res_data["enddatum"], $res_data["startzeit"], $res_data["endzeit"], $double_error_txt);
				if($double_error_txt) {
					if (!$modResForConflicts) {
						koNotifier::Instance()->addError(4, 'submit_neuer_termin', '', 'reservation');
						koNotifier::Instance()->addTextError('<b>'.$res_data['startdatum'].'</b>: '.getLL('res_collision').' <i>'.$double_error_txt.'</i><br />', 'submit_neuer_termin');

						$do_res = FALSE;
						$error = true;
					} else { // save as moderated res
						koNotifier::Instance()->addWarning(1, 'submit_neuer_termin', array('<br><b>'.$res_data['startdatum'].'</b>: '.getLL('res_collision').' <i>'.$double_error_txt.'</i><br />'), 'reservation');
						$saveModerated = TRUE;
					}
				}

				if($do_res) {
					if(!$saveModerated && ($resitems[$r]["moderation"] == 0 || $access['reservation'][$r] > 3)) {
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
		$event['lastchange_user'] = $_SESSION['ses_userid'];
		$event['user_id'] = $userid;

		// unset res_dates
		if (isset($event['res_startdatum'])) unset($event['res_startdatum']);
		if (isset($event['res_enddatum'])) unset($event['res_enddatum']);
		// unset responsible person for reservations
		if (isset($event['responsible_for_res'])) unset($event['responsible_for_res']);

		// unset programEntries
		$programEntries = NULL;
		if (isset($event['programEntries'])) {
			$programEntries = $event['programEntries'];
		}
		unset($event['programEntries']);

		$taxonomy_terms = explode(",", format_userinput($event['terms'], "intlist"));
		unset($event["terms"]);

		//Store new event
		$new_id = db_insert_data("ko_event", $event);
		unset($data['cdate']);
		unset($data['last_change']);
		unset($data['lastchange_user']);

		// set pid of program entries and insert them into db
		if (isset($programEntries)) {
			foreach ($programEntries as $k => $programEntry) {
				$programEntry['pid'] = $new_id;
				db_insert_data('ko_event_program', $programEntry);
			}
		}

		if(ko_module_installed("taxonomy")) {
			ko_taxonomy_clear_terms_on_node("ko_event", $new_id);
			ko_taxonomy_attach_terms_to_node($taxonomy_terms, "ko_event", $new_id);
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
			foreach($res_mod as $res_id => $res) {
				$res_mod[$res_id]["_event_id"] = $new_id;
			}
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
		$reply_to = '';

		if($_SESSION['ses_userid'] != ko_get_guest_id()) {
			$p = ko_get_logged_in_person();
			if($p['email']) {
				if($p['vorname'] || $p['nachname']) {
					$reply_to = array($p['email'] => trim($p['vorname'].' '.$p['nachname']));
				} else {
					$reply_to = array($p['email'] => $_SESSION['ses_username']);
				}
			}
		}


		foreach($txt3 as $gid => $txt) {
			$mailtext = sprintf(getLL("daten_email_mod3_text"), ("\n\n".$txt));
			$mods = ko_get_moderators_by_eventgroup($gid);
			foreach($mods as $mod) {
				//Check user_pref for this moderator
				if(ko_get_userpref($mod["id"], "do_mod_email_for_edit_daten") == 1) {
					ko_send_mail(
						'',
						$mod['email'],
						getLL('email_subject_prefix') . getLL('daten_email_mod3_subject'),
						ko_emailtext($mailtext),
						array(),
						array(),
						array(),
						$reply_to
					);
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
	global $DATETIME, $MAIL_TRANSPORT, $KOTA;

	//Get event if only id is given (used after multiediting)
	if(!is_array($data)) {
		$id = $data;
		ko_get_event_by_id($id, $data);
	}

	//Check eventgroup for notification
	ko_get_eventgruppe_by_id($data['eventgruppen_id'], $eg);
	if($eg['notify'] == '') return;
	if(!$data['do_notify']) return;

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
	$message = '<h2>'.getLL('daten_notify_message_'.$mode).'</h2>';
	if($mode == 'update' && is_array($old)) {
		$list_new = kota_get_list($data, 'ko_event', FALSE);
		$list_old = kota_get_list($old, 'ko_event', FALSE);
		$diff = array();
		foreach($list_old as $k => $v) {
			if($k == getLL('kota_ko_event_enddatum') || $k == getLL('kota_ko_event_endzeit')) continue;
			if($k == getLL('kota_ko_event_cdate') || $k == getLL('kota_ko_event_last_change') || $k == getLL('kota_ko_event_user_id')) continue;
			if($list_new[$k] != $v) $diff[] = array($k, strip_tags(ko_unhtml($v)), strip_tags(ko_unhtml($list_new[$k])));
		}
		if(sizeof($diff) > 0) {
			$message .= '<h3>'.getLL('daten_notify_title_changes').'</h3>';
			$message .= '<table style="width: 100%;" cellpadding="3" cellspacing="3">';
			$message .= '<tr style="border-bottom: 1px solid #333;"><th style="text-align: left;">&nbsp;</th><th style="text-align: left;">'.getLL('daten_notify_before').'</th><th style="text-align: left;">'.getLL('daten_notify_after').'</th></tr>';
			$c = 0;
			foreach($diff as $d) {
				$message .= '<tr style="border-bottom: 1px solid #999; background-color: #'.($c%2 == 0 ? 'f1f1f1' : 'ffffff').';">';
				$message .= '<td>'.$d[0].'</td><td>'.nl2br($d[1]).'</td><td><b>'.nl2br($d[2]).'</b></td>';
				$message .= '</tr>';
				$c++;
			}
			$message .= '</table>';
		}
	}
	//Only keep fields from _listview_default so email doesn't show ALL information about event
	foreach($data as $k => $v) {
		if(!in_array($k, $KOTA['ko_event']['_listview_default'])) unset($data[$k]);
	}
	$list = kota_get_list($data, "ko_event", FALSE);
	$message .= '<h3>'.getLL('daten_notify_title_event').'</h3>';
	$message .= '<table style="width: 100%;" cellpadding="3" cellspacing="3">';
	$c = 0;
	foreach($list as $k => $v) {
		if($k == getLL('kota_ko_event_enddatum') || $k == getLL('kota_ko_event_endzeit')) continue;
		if($k == getLL('kota_ko_event_cdate') || $k == getLL('kota_ko_event_last_change') || $k == getLL('kota_ko_event_user_id')) continue;

		$message .= '<tr style="border-bottom: 1px solid #999; background-color: #'.($c%2 == 0? 'f1f1f1' : 'ffffff').';">';
		$message .= '<th style="text-align: left;">'.$k.'</th><td>'.nl2br($v).'</td>';
		$message .= '</tr>';
		$c++;
	}
	$message .= '</table>';

	$reply_to = '';
	if($_SESSION['ses_userid'] != ko_get_guest_id()) {
		$p = ko_get_logged_in_person();
		if($p['email']) {
			if($p['vorname'] || $p['nachname']) {
				$reply_to = array($p['email'] => trim($p['vorname'].' '.$p['nachname']));
			} else {
				$reply_to = array($p['email'] => $_SESSION['ses_username']);
			}
		}
	}

	foreach($to as $recipient) {
		ko_send_html_mail('', $recipient, $subject, $message, array(), array(), array(), $reply_to);
	}
}//ko_daten_send_notification()





function ko_daten_ical_links() {
	global $ICAL_URL, $BASE_URL, $access, $BASE_PATH;

	if($_SESSION['ses_userid'] == ko_get_guest_id() && !ko_get_setting('daten_show_ical_links_to_guest')) return FALSE;
	if(!defined('KOOL_ENCRYPTION_KEY') || trim(KOOL_ENCRYPTION_KEY) == '') {
		print 'ERROR: '.getLL('error_daten_9');
		return FALSE;
	}

	ko_get_login($_SESSION['ses_userid'], $login);

	if(empty($login['ical_hash'])) {
		require_once($BASE_PATH.'admin/inc/admin.inc');
		$login['ical_hash'] = ko_admin_revoke_ical_hash($_SESSION['ses_userid']);
	}

	$base_link = ($ICAL_URL ? $ICAL_URL : $BASE_URL . 'ical/') . '?' . ($_SESSION['ses_userid'] == ko_get_guest_id() ? 'guest=1' : 'user=' . $login['ical_hash']);

	$help  = ko_get_help('daten', 'ical_links');
	$help2 = ko_get_help('daten', 'ical_links2');

	$content = '<div class="container-fluid"><div class="row">';
	$content .= '<div class="ical-links col-md-8">';
	$content .= '<h3 class="ko_list_title">'.($help['show'] ? '&nbsp;<span class="pull-left help-icon">'.$help['link'].'</span>' : '').'<span class="pull-left">'.getLL('daten_ical_links_title').'</span><br clear="all" /></h3>';
	$content .= '<p>'.getLL('daten_ical_links_description').'</p>';

	$content .= '<h4>'.($help2['show'] == 1 ? $help2['link'].'&nbsp;' : '').getLL('daten_ical_links_title_all').'</h4>';
	$content .= ko_get_ical_link($base_link, getLL('all')).'<br />';

	$link = $base_link.'&egs='.implode(',', $_SESSION['show_tg']);
	$link.= "&terms=".$_SESSION['daten_taxonomy_filter'];

	//Add current KOTA filter if set
	if(sizeof($_SESSION['kota_filter']['ko_event']) > 0) {
		foreach($_SESSION['kota_filter']['ko_event'] as $k => $v) {
			if(!$v) continue;
			$link .= '&kota_'.$k.'='.urlencode(implode("||",$v));
		}
	}

	$content .= ko_get_ical_link($link, getLL('daten_ical_links_current')) . '<br />';


	if($access['daten']['ABSENCE'] >= 1) {
		$content .= '<h4>'.($help2['show'] == 1 ? $help2['link'].'&nbsp;' : '').getLL('daten_ical_links_title_absences').'</h4>';
		$content .= ko_get_ical_link($base_link . '&absences=own', getLL('daten_ical_links_title_absence_own')) . '<br />';
	}
	if($access['daten']['ABSENCE'] > 1) {
		$content .= ko_get_ical_link($base_link . '&absences=all', getLL('daten_ical_links_title_absence_all')) . '<br />';

		$itemset = array_merge((array)ko_get_userpref('-1', '', 'filterset', 'ORDER BY `key` ASC'), (array)ko_get_userpref($_SESSION['ses_userid'], '', 'filterset', 'ORDER BY `key` ASC'));
		foreach($itemset as $i) {
			if(!$i['key'] || !$i['value']) continue;
			$label = getLL("daten_absence_list_title") . " (" . getLL("leute_labels_preset") . ": " . ($i['user_id'] == '-1' ? getLL('itemlist_global_short').$i['key'] : $i['key']) . ")";
			$content .= ko_get_ical_link($base_link . '&absences=preset&id=' . $i['id'], $label) . '<br />';
		}
	}

	$content .= '<h4>'.($help2['show'] == 1 ? $help2['link'].'&nbsp;' : '').getLL('daten_ical_links_title_presets').'</h4>';
	$itemset = array_merge((array)ko_get_userpref('-1', '', 'daten_itemset', 'ORDER BY `key` ASC'), (array)ko_get_userpref($_SESSION['ses_userid'], '', 'daten_itemset', 'ORDER BY `key` ASC'));
	foreach($itemset as $i) {
		if(!$i['key'] || !$i['value']) continue;
		$label = ($i['user_id'] == '-1' ? getLL('itemlist_global_short').$i['key'] : $i['key']);
		$content .= ko_get_ical_link($base_link.'&egs=p'.$i['id'], $label).'<br />';
	}

	$content .= '<h4>'.($help2['show'] == 1 ? $help2['link'].'&nbsp;' : '').getLL('daten_ical_links_title_single_tg').'</h4>';
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

	if (ko_module_installed("taxonomy") && $access['taxonomy']['ALL'] >= 1) {
		$content .= '<h4>'.($help2['show'] == 1 ? $help2['link'].'&nbsp;' : '').getLL('daten_ical_links_title_single_term').'</h4>';
		$terms = ko_taxonomy_get_terms();
		$structuredTerms = ko_taxonomy_terms_sort_hierarchically($terms);
		foreach ($structuredTerms AS $structuredTerm) {
			if(!empty($structuredTerm['children'])) {
				$content .= ko_get_ical_link($base_link.'&term='.$structuredTerm['data']['id'], $structuredTerm['data']['name']).'<br />';

				foreach($structuredTerm['children'] AS $childTerm) {
					$content .= " &nbsp; &nbsp; &nbsp;" . ko_get_ical_link($base_link.'&term='.$childTerm['id'], $childTerm['name']).'<br />';
				}
			} else {
				$content .= ko_get_ical_link($base_link.'&term='.$structuredTerm['data']['id'], $structuredTerm['data']['name']).'<br />';
			}
		}
	}

	if (ko_module_installed("rota") && $access['rota']['ALL'] >= 1) {
		$content .= '<h4>' . ($help2['show'] == 1 ? $help2['link'].'&nbsp;' : '') . getLL('rota_ical_links_title_teams') . '</h4>';
		$content .= '<div>';
		$teams = ko_rota_get_all_teams();
		foreach ($teams AS $team) {
			if($team['rotatype'] != "day") continue;
			$link = $BASE_URL . 'rotaical/index.php?team=' . $team['id'] . 'x' . strtolower(substr(md5($team['id'] . KOOL_ENCRYPTION_KEY . 'rotaIcal' . KOOL_ENCRYPTION_KEY . 'rotaIcal' . KOOL_ENCRYPTION_KEY . $team['id']), 0, 10));
			$content .= ko_get_ical_link($link, $team['name'], "") . "<br/>";
		}

		$content .= "</div>";
	}

	$content .= '</div>';

	if($_SESSION['ses_userid'] != ko_get_guest_id()) {
		$content .= '<div class="col-md-4">';
		$content .= '
		<h3>' . getLL("ical_links_revoke_title") . '</h3>
		<p>' . getLL("ical_links_revoke_text") . '</p>
		<button type="submit" class="btn btn-sm btn-danger" onclick="c=confirm(\''.ko_js_save(getLL("ical_links_revoke_jsquestion")).'\');if(!c) return false;set_action(\'ical_links_revoke\');" value="revoke">'.getLL("ical_links_revoke_button").'</button>';
		$content .= '</div>';
	}

	$content .= '</div></div>';

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
	require_once($BASE_PATH.'fpdf/fpdf.php');
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

		$absence_color = substr(ko_get_setting('absence_color'), 1);

		//Absence
		if(!empty($_SESSION['show_absences'])) {
			$absenceEvents = ko_get_absences_for_calendar(date('Y-m-d', $startstamp), date('Y-m-d', $endstamp), 'ko_event');
			if(sizeof($absenceEvents) > 0) {
				$egs['absence'] = [
					'id' => 'absence',
					'farbe' => $absence_color,
					'name' => getLL('absence_eventgroup'),
					'shortname' => getLL('absence_eventgroup_short')
				];
				$events = array_merge($events, $absenceEvents);
			}
		}

		list($amtstageEvents, $amtstageEgs) = ko_get_amtstageevents_for_calendar($startstamp, $endstamp, TRUE);
		if(!empty($amtstageEvents)) {
			$events = array_merge($amtstageEvents, $events);
			$egs = array_merge($amtstageEgs, $egs);
		}

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
						if(substr($event['id'],0,1) == "d") {
							$content["zeit"] = "";
						} else {
							$content['zeit'] = ko_get_time_as_string($event, $show_time, $mode);
						}
						$data[(int)substr($date, -2)]['inhalt'][] = $content;
					}
					$date = add2date($date, 'tag', 1, TRUE);
				}
			} else {
				//Time
				if(substr($event['id'],0,1) == "d") {
					$content["zeit"] = "";
				} else {
					$content['zeit'] = ko_get_time_as_string($event, $show_time, 'default', TRUE, FALSE);
				}
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




function ko_daten_formular_export_single_pdf_settings() {
	global $smarty, $eventId, $fromRota, $access;

	if($access['daten']['MAX'] < 1 || $_SESSION['ses_userid'] == ko_get_guest_id()) return FALSE;

	//build form
	$gc = 0;
	$rowcounter = 0;

	$values = array('_current');
	$descs = array(getLL('leute_export_pdf_columns_current'));
	foreach (ko_get_userpref($_SESSION['ses_userid'], '', 'ko_event_colitemset') as $up) {
		$values[] = 'colitemset_'.$up['key'];
		$descs[] = $up['key'];
	}
	foreach (ko_get_userpref('-1', '', 'ko_event_colitemset') as $up) {
		$values[] = 'gcolitemset_'.$up['key'];
		$descs[] = '[G]'.$up['key'];
	}
	$value = ko_get_userpref($_SESSION['ses_userid'], 'daten_export_single_pdf_event_cols');
	$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('daten_export_single_pdf_settings_event_cols'),
		'type' => 'select',
		'name' => 'sel_event_cols',
		'values' => $values,
		'descs' => $descs,
		'value' => $value ? $value : '_current'
	);

	$rota_list[] = '_all';
	$rota_list_descs[] = getLL('daten_export_single_pdf_settings_rota_default');

	$rota_daten_export_itemset_selected = ko_get_userpref($_SESSION['ses_userid'], 'rota_daten_export_itemset_selected');

	foreach (ko_get_userpref($_SESSION['ses_userid'], '', 'rota_itemset') as $up) {
		$rota_list[] = $up['id'];
		$rota_list_descs[] = $up['key'];
	}

	foreach (ko_get_userpref(-1, '', 'rota_itemset') as $up) {
		$rota_list[] = $up['id'];
		$rota_list_descs[] = '[G]' . $up['key'];
	}

	$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('daten_export_single_pdf_settings_rota'),
		'type' => 'select',
		'name' => 'sel_rota',
		'values' => $rota_list,
		'descs' => $rota_list_descs,
		'value' => ($rota_daten_export_itemset_selected ? $rota_daten_export_itemset_selected : '_all')
	);

	$value = ko_get_userpref($_SESSION['ses_userid'], 'daten_export_single_pdf_contact');
	$frmgroup[$gc]['row'][$rowcounter++]['inputs'][0] = array('desc' => getLL('daten_export_single_pdf_settings_contact'),
		'type' => 'switch',
		'name' => 'sel_contact',
		'label_0' => getLL('no'),
		'label_1' => getLL('yes'),
		'value' => ($value == '' ? 0 : $value)
		);

	$value = ko_get_userpref($_SESSION['ses_userid'], 'daten_export_single_pdf_text_before');
	$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('daten_export_single_pdf_settings_text_before'),
		'type' => 'richtexteditor',
		'name' => 'txt_text_before',
		'value' => $value
	);

	$value = ko_get_userpref($_SESSION['ses_userid'], 'daten_export_single_pdf_text_after');
	$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('daten_export_single_pdf_settings_text_after'),
		'type' => 'richtexteditor',
		'name' => 'txt_text_after',
		'value' => $value
	);

	//Allow plugins to add further settings
	hook_form('daten_settings_export_single_pdf_settings', $frmgroup, '', '');

	//display the form
	$smarty->assign("tpl_titel", getLL("daten_export_single_pdf_settings_form_title"));
	$smarty->assign("tpl_submit_value", getLL("leute_labels_submit"));
	$smarty->assign("tpl_action", "export_single_pdf");
	$cancel = ko_get_userpref($_SESSION["ses_userid"], "default_view_daten");
	if(!$cancel) $cancel = "list_events";
	$smarty->assign("tpl_cancel", $cancel);
	$smarty->assign("tpl_groups", $frmgroup);
	$smarty->assign("tpl_hidden_inputs", array(array('name' => 'id', 'value' => $eventId), array('name' => 'from_rota', 'value' => $fromRota?'1':'')));

	$smarty->display('ko_formular.tpl');
}


/**
 * @param $event
 * @param $settings
 * @return string
 */
function ko_daten_export_single_pdf($event, $settings) {
	global $ko_path, $BASE_PATH, $KOTA, $DATETIME;

	if (!isset($KOTA['ko_event_program'])) ko_include_kota(array('ko_event_program'));

	if (isset($settings['rotaFilterlist'])) {
		ko_save_userpref($_SESSION['ses_userid'], 'rota_daten_export_itemset_selected', $settings['rotaFilterlist']);
	}

	$showContact = ($_POST['sel_contact'] == 1 ? 1 : 0);
	ko_save_userpref($_SESSION['ses_userid'], 'daten_export_single_pdf_contact', $showContact);

	// preprocess event
	$eventProcessed = $event;
	kota_process_data('ko_event', $eventProcessed, 'pdf,list', $_, $event['id']);
	$eventKeys = $settings['eventCols'];
	$eventHeader = array_map(function($e){return getLL('kota_listview_ko_event_'.$e);}, $eventKeys);

	// fetch and pre process program entries
	$programEntries = db_select_data('ko_event_program', "WHERE `pid` = {$event['id']}", '*', "ORDER BY `sorting` ASC");
	$programEntriesProcessed = $programEntries;
	if(sizeof($programEntriesProcessed) > 0) {
		foreach ($programEntriesProcessed as $epId => &$ep) {
			kota_process_data('ko_event_program', $ep, 'pdf,list', $_, $epId);
		}
		$programEntriesKeys = ko_array_column($KOTA['ko_event_program']['_listview'], 'name');
		$programEntriesHeader = array_map(function($e){return getLL('kota_listview_ko_event_program_'.$e);}, $programEntriesKeys);
	}

	$fontSize = 10;
	$fontSizeH1 = 16;
	$fontSizeH2 = 14;

	require_once($ko_path.'inc/tcpdf/tcpdf.php');

	class MyTCPDF extends TCPDF {
		public function Header() {
			global $BASE_PATH;
			$logoFile = ko_get_pdf_logo();
			if ($logoFile) {
				$logoPath = $BASE_PATH.'my_images/'.$logoFile;
				$this->Image($logoPath, $this->lMargin, 8, 0, 15);
			}
		}
		public function Footer() {}
		public function MultiRow($row, $widths, $borders=NULL, $lns=NULL, $fills=NULL, $aligns=NULL, $autoPaddings=NULL) {
			$page_start = $this->getPage();
			$yStart = $this->GetY();

			foreach ($widths as $k => $w) {
				if (substr($w, -1) == '%') $widths[$k] = floatval(substr($w, 0, -1)) / 100 * ($this->w - $this->lMargin - $this->GetX());
			}

			$maxPage = 0;
			$maxByPage = array();
			foreach ($row as $k => $value) {
				$left = $li = 0;
				while ($li < $k) {
					$left += $widths[$li];
					$li++;
				}
				$this->writeHTMLCell($widths[$k], 0, $this->GetX() + $left, $yStart, $value, $borders[$k]?$borders[$k]:0, $lns[$k]?$lns[$k]:1, $fills[$k]?$fills[$k]:false, true, $aligns[$k]?$aligns[$k]:'', $autoPaddings[$k]?$autoPaddings[$k]:true);
				$maxPage = max($maxPage, $this->getPage());
				if (!$maxByPage[$this->getPage()]) $maxByPage[$this->getPage()] = $this->GetY();
				else $maxByPage[$this->getPage()] = max($maxByPage[$this->getPage()], $this->GetY());
				$this->setPage($page_start);
			}

			$this->setPage($maxPage);
			$this->SetXY($this->GetX(),$maxByPage[$maxPage]);
		}
	}


	$pdf = new MyTCPDF('P', 'mm', 'A4', false, 'ISO-8859-1', false);
	$pdf->resetLastH();

	// set document information
	$pdf->SetCreator(PDF_CREATOR);
	$pdf->SetAuthor(ko_get_setting('info_name'));

	$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

	$pdf->SetMargins(10, 30, 10);
	$pdf->SetHeaderMargin(0);
	$pdf->SetFooterMargin(0);

	$pdf->SetAutoPageBreak(TRUE, 10);

	$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

	$pdf->SetFont('helvetica', '', $fontSize);

	$pdf->AddPage();
	$pdf->lastPage();
	$pdf->SetCellPaddings(0,0.5,2,0.5);

	// title
	$title = $event['title'] ? $event['title'] : $event['eventgruppen_name'];
	$title = trim(strftime($DATETIME['DdmY'], strtotime($event['startdatum'].' '.$event['startzeit'])).' '.$title);
	$pdf->SetFont('helvetica', 'B', $fontSizeH1);
	$pdf->Write($pdf->getLastH(), $title, '', false, '', true);

	// text at beginning
	if ($settings['textBefore']) {
		$pdf->SetFont('helvetica', '', $fontSize);
		$pdf->writeHTML($settings['textBefore'], true, false, true);
		$pdf->resetLastH();
	}

	// event fields
	$pdf->Ln();
	$pdf->SetFont('helvetica', '', $fontSize);
	foreach ($eventKeys as $ekk => $eventKey) {
		$ll = $eventHeader[$ekk];
		$v = $eventProcessed[$eventKey];

		$pdf->MultiRow(array("<b>{$ll}</b>", $v), array('25%', '75%'));
	}

	// rota schedule
	$teams = ko_rota_get_all_teams(" AND `eg_id` REGEXP ('(^|,){$event['eventgruppen_id']}(,|$)')");
	if ($event['rota'] && sizeof($teams) > 0) {
		$pdf->Ln();
		$pdf->SetFont('helvetica', 'B', $fontSizeH2);
		$pdf->Write($pdf->getLastH(), getLL('rota_title_schedule'), '', false, '', true);
		$pdf->SetFont('helvetica', '', $fontSize);

		if($settings['rotaFilterlist'] != '_all') {
			$userpref_rota_list = db_select_data('ko_userprefs', "WHERE `id` LIKE '".$settings['rotaFilterlist']."'",'value','','LIMIT 1',TRUE);
			$rotaFilterlist = explode(',', $userpref_rota_list['value']);
		}

		foreach ($teams as $team) {
			// filter teams if pre-selected in Detail-Export
			if (is_array($rotaFilterlist) && $settings['rotaFilterlist'] != '_all') {
				if(!in_array($team['id'], $rotaFilterlist)) {
					continue;
				}
			}

			if (ko_rota_is_scheduling_disabled($event['id'],$team['id'])) {
				continue;
			}

			$helpers = ko_rota_get_helpers_by_event_team($event['id'], $team['id']);
			$helperNames = [];
			foreach ($helpers as $h) {
				if ($h['id']) {
					$contactInfoMerged = '';
					$contactInfo = [];
					if ($showContact) {
						if (ko_get_leute_mobile($h, $mobile)) {
							$contactInfo[] = $mobile[0];
						} elseif(!empty($h['telp'])) {
							$contactInfo[] = $h['telp'];
						} elseif(!empty($h['telg'])) {
							$contactInfo[] = $h['telg'];
						}

						if (ko_get_leute_email($h, $email)) {
							$contactInfo[] = $email[0];
						}

						if (count($contactInfo) > 0) {
							$contactInfoMerged = " (" . implode(", ", $contactInfo) . ")";
						}
					}

					$helperNames[] = $h['vorname'] . ' ' . $h['nachname'] . $contactInfoMerged;
				} else {
					$helperNames[] = $h['name'];
				}
			}
			if ($showContact) {
				$pdf->MultiRow(["<b>{$team['name']}</b>", implode('<br />', $helperNames)], ['25%', '75%']);
			} else {
				$pdf->MultiRow(["<b>{$team['name']}</b>", implode(', ', $helperNames)], ['25%', '75%']);
			}
		}
	}

	// event program
	if (sizeof($programEntriesProcessed) > 0) {
		$pdf->Ln();
		$pdf->SetFont('helvetica', 'B', $fontSizeH2);
		$pdf->Write($pdf->getLastH(), getLL('kota_ko_event_program'), '', false, '', true);
		$pdf->SetFont('helvetica', '', $fontSize);
		$pdf->SetCellPaddings(2, 2, 2, 2);
		$table_program = "<table border=\"0\" cellpadding=\"4\"><tr>";
		foreach ($programEntriesHeader as $headline) {
			$table_program.= '<td style="border-bottom: 1px solid #333;"><b>'. $headline ."</b></td>";
		}
		$table_program.= "</tr>";

		foreach ($programEntriesProcessed as $pe) {
			$table_program.= '<tr>';
			foreach ($programEntriesKeys as $pek) {
				$table_program.= '<td style="border-bottom: 1px solid #333;">'. $pe[$pek] ."</td>";
			}
			$table_program.= "</tr>";
		}

		$table_program.= "</table>";
		$pdf->writeHTML($table_program, true, false, false, false, '');
		$pdf->lastPage();
	}

	// text at end
	$pdf->SetFont('helvetica', '', $fontSize);
	if ($settings['textAfter']) {
		$pdf->Ln();
		$pdf->writeHTML($settings['textAfter']);
		$pdf->resetLastH();
	}

	$filename = getLL('daten_filename_pdf').$event['id'].'_'.date('Ymd_His').'.pdf';

	$file = $BASE_PATH.'download/pdf/'.$filename;
	$pdf->Output($file, 'F');

	return $filename;
}//ko_daten_export_single_pdf()




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

	//replace webcal:// with https://
	if(substr($eg['ical_url'], 0, 9) == 'webcal://') {
		$eg['ical_url'] = str_replace('webcal://', 'https://', $eg['ical_url']);
		db_update_data('ko_eventgruppen', "WHERE `id` = '$egid'", array('ical_url' => $eg['ical_url']));
	}

	//Get stream data and save in file
	if(ini_get('allow_url_fopen')) {
		$icalData = fopen($eg['ical_url'],'r');
	} else {
		$icalData = ko_fetch_url($eg['ical_url']);
	}
	if(!$icalData) return;

	//Convert iCal data to array of kOOL events
	require_once($ko_path.'inc/ICalReader/ICalReader.php');
	$ical = new kOOL\ICalReader();
	$events = $ical->getEvents($icalData, $egid, $eg['ical_title']);

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
			$modified_event = db_select_data("ko_event", "WHERE import_id = '".$new['import_id']."'", "id", "", "LIMIT 1", TRUE);
			$id = $modified_event['id'];
			unset($all_ids[$new['import_id']]);
		} else {
			//Create new event
			$new['cdate'] = $new['last_change'];
			$id = db_insert_data('ko_event', $new);
		}

		if(ko_module_installed("taxonomy")) {
			$terms = array_keys(ko_taxonomy_get_terms_by_node($eg['id'],"ko_eventgruppen"));
			ko_taxonomy_clear_terms_on_node("ko_event", $id);
			ko_taxonomy_attach_terms_to_node($terms,"ko_event", $id);
		}
	}


	//Remove events from the future which are not in iCal anymore (past events are not deleted)
	if(sizeof($all_ids) > 0) {
		db_delete_data('ko_event', "WHERE `eventgruppen_id` = '$egid' AND `import_id` != '' AND `import_id` IN ('".implode("','", $all_ids)."') AND `enddatum` > NOW()");
	}

}//ko_daten_import_ical()



function ko_daten_import_absences() {
	global $ko_path;
	require_once($ko_path.'inc/ICalReader/ICalReader.php');

	$where = "WHERE `key` = 'absence_ical_url' AND value != ''";
	$ical_urls = db_select_data("ko_userprefs", $where);

	foreach ($ical_urls AS $ical_url) {
		$leute_id = ko_get_logged_in_id($ical_url['user_id']);
		if(empty($leute_id)) continue;

		$icalData = ko_fetch_url($ical_url['value']);
		if(!$icalData) continue;

		$icalReader = new kOOL\ICalReader();

		try {
			$absences = $icalReader->getAbsences($icalData);

			foreach($absences AS $absence) {
				$absence['leute_id'] = $leute_id;
				$absence['cruser'] = $_SESSION['ses_userid'];

				$where = "WHERE ical_id = '" . $absence['ical_id'] . "' AND to_date >= '".date("Y-m-d", time())."'";
				if(empty(db_select_data("ko_event_absence", $where))) {
					db_insert_data("ko_event_absence", $absence);
				} else {
					db_update_data("ko_event_absence", $where, $absence);
				}
			}

			$ical_ids = array_column($absences, "ical_id");
			$where = "WHERE ical_id != '' AND ical_id NOT IN ('" . implode("','", $ical_ids) . "') AND to_date >= '".date("Y-m-d", time())."'";
			db_delete_data("ko_event_absence", $where);
		} catch (Exception $e) {
			ko_log("absence_ical_import", $e->getMessage());
		}
	}
}

/**
 * Return the type for an absence
 *
 * @param $input string to search through
 * @return string
 */
function ko_daten_absence_map_type($input) {
	$ABSENCE_TYPES = [
		"ferien" => "vacation",
		"urlaub" => "vacation",
		"weiterbildung" => "training",
		"ausbildung" => "training",
		"kurs" => "training",
		"frei" => "free",
		"kompensation" => "compensation",
		"kompensiere" => "compensation",
		"militär" => "army",
		"armee" => "army",
		"rekrut" => "army",
		"wk" => "army",
		"zivildienst" => "civilservice",
		"krank" => "sick",
		"unfall" => "sick",
		"arzt" => "sick",
		"spital" => "sick",
		];

	global $PLUGINS;

	foreach($PLUGINS as $plugin) {
		if(function_exists('my_absence_map_type_'.$plugin['name'])) {
			call_user_func_array('my_absence_map_type'.$plugin['name'], [&$ABSENCE_TYPES]);
		}
	}

	foreach($ABSENCE_TYPES AS $search_for => $type) {
		if(stristr($input, $search_for)) {
			return $type;
		}
	}

	return "other";
}


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
	$group[$gc]['row'][$rowcounter]['inputs'][1] = array('desc' => '&nbsp;',
																											 'type' => 'radio',
																											 'name' => 'preset[layout]',
																											 'values' => array('month'),
																											 'descs' => array(getLL('daten_export_preset_layout_month')),
																											 'value' => $preset['layout'],
																											 );
	$group[$gc]['row'][$rowcounter]['inputs'][2] = array('desc' => '&nbsp;',
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
	if ($access['daten']['MAX'] > 3) $list->setActionNew('new_export_preset');


	//Footer
	$list_footer = $smarty->get_template_vars('list_footer');
	$list->setFooter($list_footer);


	//Output the list
	$list->render($es);
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


function daten_get_eg_color_css() {
	ko_get_eventgruppen($egs);
	$output = '';
	$output .= '<style>';
	foreach ($egs as $eg) {
		if (!$eg) continue;
		$output .= 'tr.ko_list_color_by_eg_' . $eg['id'] . ' td {border-bottom: 2px solid #' . $eg['farbe'] . ' !important;} ';
	}
	$output .= '</style>';
	return $output;
}


/**
 * Returns user assignable cols
 *
 * @return array An array containing the column names and ll-descs of all user assignable cols
 */
function ko_daten_get_user_assignable_cols() {
	global $DATEN_EXCLUDE_DBFIELDS, $access;

	if(isset($access['reservation']['MAX'])) $resMax = $access['reservation']['MAX'];
	else ko_get_access_all('reservation', '', $resMax);

	$values = $descs = array();
	$_dbfields = db_get_columns('ko_event');
	foreach($_dbfields as $f) {
		if(in_array($f['Field'], $DATEN_EXCLUDE_DBFIELDS)) continue;
		if($f['Field'] == 'reservationen' && (!ko_module_installed('reservation') || $resMax < 1)) continue;
		$values[] = $f['Field'];
		$descs[] = getLL('kota_ko_event_'.$f['Field']) ? getLL('kota_ko_event_'.$f['Field']) : $f['Field'];
	}
	return array($values, $descs);
}



function ko_daten_insert_new_event($do_action, $ep) {
	global $EVENTS_SHOW_RES_FIELDS, $access, $ko_path, $KOTA;

	//Formulardaten
	$data = $ep["koi"]["ko_event"];
	kota_process_data("ko_event", $data, "post");

	$data["resitems"] = format_userinput($ep["sel_do_res"], "intlist");
	foreach($EVENTS_SHOW_RES_FIELDS as $f) {
		if(in_array($f, array('startzeit', 'endzeit'))) {
			$data['res_'.$f] = $ep['res_'.$f] ? sql_zeit($ep['res_'.$f]) : $data[$f];
		} else {
			$data['res_'.$f] = $ep['res_'.$f];
		}
	}

	//Group subscription
	$data['gs_gid'] = isset($ep['chk_gs_gid']) ? 1 : '';
	//Copy event: Copy GS group as well
	if($do_action == 'submit_as_new_event') {
		$id = format_userinput($ep['id'], 'uint');
		$orig_event = db_select_data('ko_event', "WHERE `id` = '$id'", '*', '', '', TRUE);
		if($data['gs_gid'] == 1 && $orig_event['gs_gid'] != '') {
			$data['gs_gid'] = 'COPY'.$orig_event['gs_gid'];
		}
	}

	// get program of new event (if there is any)
	if (ko_get_setting('activate_event_program') == 1) {
		if ($do_action == 'submit_as_new_event') {
			$programFromId = $orig_event['id'];
		}
		else {
			$programFromId = format_userinput($ep['ft_ko_event_program_id'], 'alphanum+');
		}
		$programEntries = db_select_data('ko_event_program', "where `pid` = '" . $programFromId . "'");
		foreach ($programEntries as $k => $programEntry) {
			unset($programEntries[$k]['pid']);
			unset($programEntries[$k]['id']);
		}
		$data['programEntries'] = $programEntries;
	}

	// set responsible person for reservations
	$data['responsible_for_res'] = format_userinput($ep['sel_responsible_for_res'], 'uint');

	//Eingaben überprüfen
	$errorOut = check_daten_entries($data);
	if($errorOut) return FALSE;

	//Get repetition
	switch($ep["rd_wiederholung"]) {
		case "taeglich":     $inc = format_userinput($ep["txt_repeat_tag"], "uint"); break;
		case "woechentlich": $inc = format_userinput($ep["txt_repeat_woche"], "uint"); break;
		case "monatlich1":   $inc = format_userinput($ep["sel_monat1_nr"], "uint")."@".format_userinput($ep["sel_monat1_tag"], "uint"); break;
		case "monatlich2":   $inc = format_userinput($ep["txt_repeat_monat2"], "uint"); break;
		case "holidays":     $inc = format_userinput($ep["sel_repeat_holidays"], "alphanum+")."@".format_userinput($ep["sel_repeat_holidays_offset"], "alphanum+"); break;
		case "dates":     $inc = format_userinput($ep["sel_repeat_dates"], "alphanumlist"); break;
	}
	ko_get_wiederholung($data["startdatum"], $data["enddatum"], $ep["rd_wiederholung"], $inc,
		$ep["sel_bis_tag"], $ep["sel_bis_monat"], $ep["sel_bis_jahr"],
		$repeat, ($ep["txt_num_repeats"] ? $ep["txt_num_repeats"] : ""),
		format_userinput($ep['sel_repeat_eg'], 'uint'));

	if(sizeof($repeat) <= 0) koNotifier::Instance()->addError(5, $do_action);
	if(koNotifier::Instance()->hasErrors()) return FALSE;


	//Find moderation from event group
	$eg = db_select_data("ko_eventgruppen", "WHERE `id` = '".$data["eventgruppen_id"]."'", "*", "", "", TRUE);
	$moderation = $eg["moderation"];

	$modResForConflicts = format_userinput($_POST['manual_moderation_for_conflicts'], 'uint');
	$event_data = $mod_data = array();

	//Loop through all repetitions
	for($i=0; $i<sizeof($repeat); $i+=2) {
		$data["startdatum"] = sql_datum($repeat[$i]);
		$data["enddatum"] = $repeat[$i+1] != "" ? sql_datum($repeat[$i+1]) : $data["startdatum"];

		$data['res_startdatum'] = date('Y-m-d', ($ep['res_startdatum_delta']*24*3600 + strtotime($data["startdatum"])));
		$data['res_enddatum'] = date('Y-m-d', ($ep['res_enddatum_delta']*24*3600 + strtotime($data["enddatum"])));

		if($do_action != 'submit_manual_moderation' && ($moderation == 0 || $access['daten'][$data['eventgruppen_id']] > 2)) {
			$event_data[] = $data;
		} else {
			$data['_res_mod_on_conflict'] = $modResForConflicts;
			$mod_data[] = $data;
		}
	}//for(i=0..sizeof(repeat))

	if (sizeof($event_data) > 0) {
		$errorOut = ko_daten_store_event($event_data, $modResForConflicts);
		if(!$errorOut) {
			// copy images from base event in case action == submit_as_new_event
			if ($do_action == 'submit_as_new_event') {
				$fileFields = array();
				foreach ($KOTA['ko_event'] as $k => $v) {
					if (substr($k, 0, 1) == '_') continue;
					if ($v['form']['type'] != 'file')  continue;
					if ($orig_event[$k] == '') continue;
					$fileFields[] = $k;
				}
				foreach ($event_data as $savedEvent) {
					$updateData = array();
					foreach ($fileFields as $fileField) {
						if ($savedEvent[$fileField] != '') continue;
						if (array_key_exists($fileField . '_DELETE', $savedEvent)) continue;
						$origName = $orig_event[$fileField];
						$ext = trim(end((explode(".", $origName))));
						$newName = 'my_images/kota_ko_event_' . $fileField . '_' . $savedEvent['id'] . ($ext == '' ? '' : '.' . $ext);
						copy($ko_path . $origName, $ko_path . $newName);
						$updateData[$fileField] = $newName;
					}
					db_update_data('ko_event', 'where id = ' . $savedEvent['id'], $updateData);
				}
			}

			koNotifier::Instance()->addInfo(1, $do_action);
		}
	}
	if (sizeof($mod_data) > 0) {
		ko_daten_store_moderation($mod_data, $do_action=='submit_manual_moderation');
		koNotifier::Instance()->addWarning(9, $do_action);
	}

	// delete program with dummy pid
	if (!koNotifier::Instance()->hasErrors() && ko_get_setting('activate_event_program') == 1 && $do_action == 'submit_neuer_termin') {
		db_delete_data('ko_event_program', "where `pid` = '" . $data['programEntries'] . "'");
	}

	return $event_data;
}


/**
 * Shows list of absences
 *
 * @param string $mode html or xls export
 * @return bool
 * @throws Exception
 */
function ko_daten_list_absence($mode = "html") {
	global $access, $notifier;
	$_SESSION['kota_show_cols_ko_event_absence'] = ['leute_id', 'from_date', 'to_date', 'type', 'description', ];

	if($access['daten']['ABSENCE'] < 1) return FALSE;
	$leute_id = ko_get_logged_in_id();

	if($leute_id == 0 && $access['daten']['ABSENCE'] <= 2) {
		$notifier->addTextError(getLL("absence_error_no_person"));
		$notifier->notify();
	} else if($leute_id == 0) {
		$notifier->addTextWarning(getLL("absence_warning_no_person"));
		$notifier->notify();
	}

	$list = new kOOL_listview();
	$filter = kota_apply_filter("ko_event_absence");

	if(!empty($filter)) {
		$where = " AND " . $filter;
	}

	if ($access['daten']['ABSENCE'] <= 1) {
		$where.= " AND leute_id = " . $leute_id;
	}

	$start = ko_daten_parse_time_filter($_SESSION['filter_start']);
	if ($start != '') $where.= " AND to_date >= '$start'";
	$ende = ko_daten_parse_time_filter($_SESSION['filter_ende'], '');
	if ($ende != '') $where.= " AND from_date <= '$ende'";


	$rows = db_get_count("ko_event_absence", "id", $where);
	if($_SESSION['show_start'] > $rows) {
		$_SESSION['show_start'] = 1;
		$z_limit = 'LIMIT '.($_SESSION['show_start']-1).', '.$_SESSION['show_limit'];
	}

	ko_get_absence($absence_rows, $where, $z_limit);

	$showEditColumns = ['chk'];
	$showEditColumns[] = 'edit';
	$showEditColumns[] = 'delete';

	$fake_access = [];
	foreach($absence_rows as $l_i => $l) {
		if(($l['leute_id'] == $leute_id || $access['daten']['ABSENCE'] > 2) && $l['ical_id'] == '') {
			$fake_access[$l_i] = 3;
		} else {
			$fake_access[$l_i] = 0;
		}
	}

	$list->init("daten", "ko_event_absence", $showEditColumns, $_SESSION["show_start"], $_SESSION["show_limit"]);
	$list->setTitle(getLL("daten_absence_list_title"));
	$list->setAccessRights(array('edit' => 3, 'delete' => 3), $fake_access, 'id');

	$list->setActions([
		"edit" => ["action" => "edit_absence"],
		"delete" => ["action" => "delete_absence", "confirm" => TRUE]
	]);

	if(($leute_id != 0 && $access['daten']['ABSENCE'] >= 1) || ($access['daten']['ABSENCE'] >= 3)) {
		$list->setActionNew('add_absence');
	}

	$list->setWarning(kota_filter_get_warntext('ko_event_absence'));

	$list_footer[] = [
			'label' => getLL('ko_event_absence_export_label'),
			'button' => '
				<a type="button" class="btn btn-primary" href="/daten/index.php?action=list_absence&mode=xls">' . getLL("ko_event_absence_export_button") . '</a>',
	];


	$help = ko_get_help("daten", "ical_links_absence");
	$label = "<strong>" . getLL('daten_settings_absence_ical_url') . "</strong>&nbsp;<span class=\"help-icon\">".$help['link']."</span>";
	if(ko_get_logged_in_person()) {
		$absence_ical_url = ko_get_userpref($_SESSION['ses_userid'], 'absence_ical_url');
		$list_footer[] = [
			'label' => $label,
			'button' => '
			<div class="row">
				<div class="col-lg-5 col-sm-6">
					<div class="input-group">
					<form>
						<input type="text" name="absence_ical_url" class="form-control" value="' . $absence_ical_url . '"/>
						<span class="input-group-btn">
							<button class="btn btn-primary" type="button" onclick="set_action(\'set_absence_ical_url\');this.form.submit();">' . getLL("save") . '</button>
						</span>
					</form>
					</div>
				</div>
			</div>',
		];
	} else {
		$list_footer[] = [
			'label' => $label,
			'button' => "<br>" . getLL("daten_settings_absence_ical_url_no_leute_id"),
		];
	}

	$list->setFooter($list_footer);

	$list->setStats($rows);
	if($mode == 'xls') {
		$list->render($absence_rows, $mode, getLL('ko_event_absence_filename_xls'));
		return $list->xls_file;
	} else {
		$list->render($absence_rows);
	}
}

/***
 * @param $mode -- either 'edit' or 'add'
 * @param string $id -- if mode == 'edit', id of the id of the reminder has to be supplied here
 * @param array $new_data -- hashmap of the updated fields
 * @return bool
 */
function ko_daten_formular_absence($mode, $id='') {
	global $KOTA, $access;

	if($access['daten']['ABSENCE'] <= 2) {
		$KOTA['ko_event_absence']['leute_id']['form']['disabled'] = TRUE;
	}

	if($mode == 'new') {
		$id = 0;
		if (empty($_POST['koi']['ko_event_absence']['leute_id'][0]) && $login = ko_get_logged_in_person()) {
			$_POST['koi']['ko_event_absence']['leute_id'][0] = $login['id'];
		}
	} else if($mode == 'edit') {
		if(!$id) return FALSE;
	} else {
		return FALSE;
	}

	$form_data['title'] =  $mode == 'new' ? getLL('ko_event_absence_form_title_new') : getLL('ko_event_absence_form_title_edit');
	$form_data['submit_value'] = getLL('save');
	$form_data['action'] = $mode == 'new' ? 'submit_new_absence' : 'submit_edit_absence';
	$form_data['cancel'] = 'list_absence';

	ko_multiedit_formular('ko_event_absence', '', $id, '', $form_data);
	return TRUE;
}


/**
 * Return absence by id
 *
 * @param int $id
 * @return array|null absence entry
 */
function ko_daten_get_absence_by_id($id) {
	$where = "WHERE id = " . $id;
	$absence = db_select_data('ko_event_absence', $where, "*", "", "LIMIT 1", TRUE);
	return $absence;
}


/**
 * Delete absence only if not used in nodes
 *
 * @param $absence_id
 * @return bool
 */
function ko_daten_delete_absence($absence_id) {
	global $access;
	$absence = ko_daten_get_absence_by_id($absence_id);
	if(empty($absence)) return FALSE;

	if($access['daten']['ABSENCE'] == 0) return FALSE;
	if($access['daten']['ABSENCE'] <= 2 && ko_get_logged_in_id() != $absence['leute_id']) return FALSE;

	$where = "WHERE id = " . $absence_id;
	db_delete_data("ko_event_absence", $where);

	ko_log("delete_absence", "id: $absence_id, ".json_encode($absence));
	return TRUE;
}


/**
 * Return absence by id
 *
 * @param int $leute_id
 * @param String|null $within_date sql datetime
 * @param bool $only_future_absences absences not yet ended
 * @param string $sort Field name and ASC or DESC
 * @return array|null absence entry
 */
function ko_daten_get_absence_by_leute_id($leute_id, $within_date = NULL, $only_future_absences = FALSE, $sort = 'from_date ASC') {
	$where = "WHERE leute_id = " . $leute_id;

	if($within_date != NULL) {
		$where.= " AND ('" . $within_date ."' BETWEEN from_date AND to_date)";
	}

	if($only_future_absences) {
		$where.= " AND to_date > '" . date('Y-m-d',time()) ."'";
	}

	$order = "ORDER BY " . $sort;
	$absence = db_select_data('ko_event_absence', $where, "*", $order);
	return $absence;
}



/**
 * Return absences grouped by date
 *
 * @param string $from_date well formated sql date
 * @param string $to_date well formated sql date
 * @param int $leute_id optional filter for specific person
 * @return array|null absence entries
 */
function ko_daten_get_absence_by_date($from_date, $to_date = NULL, $leute_id = NULL) {
	if (empty($to_date) || $to_date == '0000-00-00') {
		$to_date = $from_date;
	}

	$where = "WHERE 
		((from_date BETWEEN '" . $from_date . "' AND '" . $to_date . "') OR 
		(to_date BETWEEN '" . $from_date . "' AND '" . $to_date . "') OR
		('" . $from_date . "' BETWEEN from_date AND to_date) OR 
		('" . $to_date . "' BETWEEN from_date AND to_date))";

	if($leute_id != NULL) {
		$where.= " AND ko_event_absence.leute_id = '" . (int)$leute_id ."'";
	}

	$order = "ORDER BY from_date ASC";
	$join = " LEFT JOIN ko_leute ON ko_leute.id = ko_event_absence.leute_id";
	$columns = "ko_event_absence.*, CONCAT(ko_leute.vorname, \" \", ko_leute.nachname) AS name";
	$absence = db_select_data('ko_event_absence' . $join, $where, $columns, $order);
	return $absence;
}


/**
 * Create data for $data in jscalendar
 *
 * @param String $startDate
 * @param String $endDate
 * @param bool   $dbFormat table schema to create $data
 * @return array $data to use in json
 * @throws Exception
 */
function ko_get_absences_for_calendar($startDate, $endDate, $dbFormat=FALSE) {
	global $access;

	$absences = ko_daten_get_absence_by_date(format_userinput($startDate, 'date'), format_userinput($endDate, 'date'));
	$loggedin_person = ko_get_logged_in_person($_SESSION['ses_userid']);
	$data = [];
	$leute_ids = [];

	if($dbFormat == "ko_event" || $_REQUEST['action'] == "jsongetevents") {
		$show_absences = $_SESSION['show_absences'];
	} else {
		$show_absences = $_SESSION['show_absences_res'];
	}

	if(!empty($show_absences)) {
		if (!in_array("all", $show_absences)){
			foreach ($show_absences AS $absence_filter_id) {
				$where = "WHERE id = '".$absence_filter_id."'";
				$filterset = db_select_data("ko_userprefs", $where, "*", "", "LIMIT 1", TRUE, TRUE);
				if ($filterset['user_id'] == -1 || $filterset['user_id'] == $_SESSION['ses_userid']) {
					$filter = unserialize($filterset["value"]);
					apply_leute_filter($filter, $leute_where);
					$leute_ids = array_merge(db_select_data("ko_leute", "WHERE 1=1 " . $leute_where, "id"), $leute_ids);
				}
			}
		}
	}

	foreach ($absences AS $absence) {
		$readonly = FALSE;
		if ($access['daten']['ABSENCE'] == 0) continue;
		if ($access['daten']['ABSENCE'] == 1 && $absence['leute_id'] != $loggedin_person['id']) continue;
		if ($access['daten']['ABSENCE'] == 2 && $absence['leute_id'] != $loggedin_person['id']) $readonly = TRUE;
		if (!ko_module_installed('leute') && $absence['leute_id'] != $loggedin_person['id']) $readonly = TRUE;

		if (!in_array("all", $show_absences)) {
			if (in_array("own", $show_absences)) {
				if($absence['leute_id'] != $loggedin_person['id']) continue;
			} else {
				if (!in_array($absence['leute_id'], array_column($leute_ids, "id"))) continue;
			}
		}

		$absence_color = substr(ko_get_setting('absence_color'), 1);

		//Return db format, similar to a db_select_data(ko_event, ...)
		if($dbFormat == "ko_event") {
			$datetime1 = new DateTime($absence['from_date']);
			$datetime2 = new DateTime($absence['to_date']);
			$difference = $datetime1->diff($datetime2);
			$data['a'.$absence['id']] = [
				'id' => 'a'.$absence['id'],
				'eventgruppen_id' => 'absence',
				'startdatum' => substr($absence['from_date'], 0, 10),
				'enddatum' => substr($absence['to_date'], 0, 10),
				'startzeit' => '00:00:00',
				'endzeit' => '00:00:00',
				'title' => getLL('kota_ko_event_absence_type_' . $absence['type']) . " " . $absence['name'],
				'kommentar' => $absence['description'],
				'duration' => ($difference->d + 1),
				'eventgruppen_farbe' => $absence_color,
			];
		} else if ($dbFormat == 'ko_reservation') {
			$datetime1 = new DateTime($absence['from_date']);
			$datetime2 = new DateTime($absence['to_date']);
			$difference = $datetime1->diff($datetime2);
			$data['a'.$absence['id']] = [
				'id' => 'a'.$absence['id'],
				'item_id' => 'absence',
				'startdatum' => substr($absence['from_date'], 0, 10),
				'enddatum' => substr($absence['to_date'], 0, 10),
				'startzeit' => '00:00:00',
				'endzeit' => '00:00:00',
				'zweck' => getLL('kota_ko_event_absence_type_' . $absence['type']) . " " . $absence['name'],
				'comments' => $absence['description'],
				'duration' => ($difference->d + 1),
				'item_farbe' => $absence_color,
			];
		}
		//Called from ajax.php jsongetevents. So fullCalendar data array is returned
		else {
			$tooltip = '<h3>' . getLL('kota_ko_event_absence_type_' . $absence['type']) . '</h3> ' .
				'<p>' . substr(sqldatetime2datum($absence['from_date']), 0, 10) . " - " . substr(sqldatetime2datum($absence['to_date']), 0, 10) .'</p><p>' . $absence['name'] . '</p>' .
				(!empty($absence['description']) ? "<fieldset><legend>".getLL('daten_comment')."</legend>".$absence['description'] ."</fieldset>": "");

			$absence['to_date'] = date('Y-m-d', strtotime("+ 1 day", strtotime($absence['to_date'])));
			if($dbFormat == "resource") {
				$absence['from_date'] = str_replace(" ", "T", $absence['from_date']);
				$absence['to_date'] = $absence['to_date']."T23:59:59";
			}

			$data[] = [
				'id' => $absence['id'],
				'start' => $absence['from_date'],
				'end' => $absence['to_date'],
				'title' => utf8_encode(getLL('kota_ko_event_absence_type_' . $absence['type']) . " " . $absence['name']),
				'allDay' => TRUE,
				'editable' => FALSE,
				'className' => 'fc-absence' . ($readonly == TRUE ? ' fc-absence-readonly' : ''),
				'color' => '#'.$absence_color,
				'textColor' => '#'.ko_get_contrast_color($absence_color, '000000', 'ffffff'),
				'kOOL_tooltip' => utf8_encode($tooltip),
				'resourceId' => 'absences',
			];
		}
	}

	return $data;
}




/**
 * Updates all access settings in ko_admin and admingroups after setting daten_access_calendar has been changed.
 * Only applies changes from X@calY when changing daten_access_calendar from 1 to 0, so all calendar access levels
 * will be propagated to the appropriate event groups.
 */
function ko_daten_propagate_cal_access() {
	if(ko_get_setting('daten_access_calendar') == 1) return;

	$egs = db_select_data('ko_eventgruppen', 'WHERE 1=1');

	$logins = db_select_data('ko_admin', "WHERE `modules` LIKE '%daten%'");
	foreach($logins as $login) {
		$newAccess = array();
		$allAccess = 0;
		foreach(explode(',', $login['event_admin']) as $eventAccess) {
			if(FALSE === strpos($eventAccess, '@')) {
				$newAccess[] = $eventAccess;
				$allAccess = $eventAccess;
				continue;
			} else {
				$parts = explode('@', $eventAccess);
				if(substr($parts[1], 0, 3) == 'cal') {
					$calID = intval(substr($parts[1], 3));
					foreach($egs as $eg) {
						if($eg['calendar_id'] != $calID) continue;
						$newAccess[] = max($allAccess, $parts[0]).'@'.$eg['id'];
					}
				} else {
					$newAccess[] = $eventAccess;
				}
			}
		}
		db_update_data('ko_admin', "WHERE `id` = '".$login['id']."'", array('event_admin' => implode(',', $newAccess)));
	}


	$admingroups = db_select_data('ko_admingroups', "WHERE `modules` LIKE '%daten%'");
	foreach($admingroups as $admingroup) {
		$newAccess = array();
		$allAccess = 0;
		foreach(explode(',', $admingroup['event_admin']) as $eventAccess) {
			if(FALSE === strpos($eventAccess, '@')) {
				$newAccess[] = $eventAccess;
				$allAccess = $eventAccess;
				continue;
			} else {
				$parts = explode('@', $eventAccess);
				if(substr($parts[1], 0, 3) == 'cal') {
					$calID = intval(substr($parts[1], 3));
					foreach($egs as $eg) {
						if($eg['calendar_id'] != $calID) continue;
						$newAccess[] = max($allAccess, $parts[0]).'@'.$eg['id'];
					}
				} else {
					$newAccess[] = $eventAccess;
				}
			}
		}
		db_update_data('ko_admingroups', "WHERE `id` = '".$admingroup['id']."'", array('event_admin' => implode(',', $newAccess)));
	}

}//ko_daten_propagate_cal_access()


?>
