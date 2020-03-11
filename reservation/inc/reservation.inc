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


function ko_list_reservations() {
	global $smarty, $access;

	if($access['reservation']['MAX'] < 1) return;
	apply_res_filter($z_where, $z_limit);

	$rows = db_get_count('ko_reservation', 'id', $z_where);
	if($_SESSION['show_start'] > $rows) {
		$_SESSION['show_start'] = 1;
		$z_limit = 'LIMIT '.($_SESSION['show_start']-1).', '.$_SESSION['show_limit'];
	}
	ko_get_reservationen($es, $z_where, $z_limit, 'res');

	ko_get_resitems($resitems, "",'WHERE 1 ');


	//Build fake access array for each reservation not just by item_id
	$res_access = array();
	if($access['reservation']['ALL'] > 3) {
		$res_access['ALL'] = $access['reservation']['ALL'];
	} else {
		foreach($es as $e) {
			//Edit and delete
			if($access['reservation'][$e['item_id']] > 3
				|| ($e['user_id'] == $_SESSION['ses_userid'] && $_SESSION['ses_userid'] != ko_get_guest_id() && $access['reservation'][$e['item_id']] > 2)
				|| ($resitems[$e['item_id']]['moderation'] == 0 && $access['reservation'][$e['item_id']] > 2)
				) {
				$res_access[$e['id']] = 3;
			} else {
				$res_access[$e['id']] = 0;
			}
		}
	}


	$list = new kOOL_listview();
	$list->init('reservation', 'ko_reservation', array('chk', 'edit', 'delete'), $_SESSION['show_start'], $_SESSION['show_limit']);
	$list->showColItemlist();
	$list->setTitle(getLL('res_list_title'));
	$list->setAccessRights(array('edit' => 3, 'delete' => 3), $res_access, 'id');


	$resWithEvent = array();
	foreach ($es as $res) {
		ko_get_events($events, "AND ko_event.`reservationen` REGEXP '(^|,){$res['id']}(,|$)'");
		if ($events && sizeof($events) == 1) {
			$resWithEvent[] = $res['id'];
		}
	}


	$list->setActions(array('edit' => array('action' => 'edit_reservation'),
													'delete' => array('action' => 'delete_res',
																						'additional_row_js' => "if ([".implode(',', $resWithEvent)."].indexOf(###ID###) >= 0) {c = confirm('".str_replace("'", "\'", getLL('res_delete_res_confirm_event'))."');if(!c) return false;c3 = confirm('".getLL("res_delete_event_confirm")."');set_hidden_value('mod_confirm', 0);set_hidden_value('del_event', c3);} else {c = confirm('".str_replace("'", "\'", getLL('res_delete_res_confirm'))."');if(!c) return false;set_hidden_value('del_event', 0);if(###SERIE_ID### > 0) {c2 = confirm('".getLL("res_delete_serie_confirm")."');set_hidden_value('mod_confirm', c2);}}"),
																						)
										);
	if ($access['reservation']['MAX'] > 1 && db_get_count('ko_resitem', 'id') > 0) $list->setActionNew('neue_reservation');
	$list->setSort(TRUE, 'setsort', $_SESSION['sort_item'], $_SESSION['sort_item_order']);
	$list->setStats($rows);

	//Footer
	$list_footer = $smarty->get_template_vars('list_footer');
	if($access['reservation']['MAX'] > 3) {
		$list_footer[] = array('label' => getLL('res_list_footer_del_label'), 'button' => '<input type="submit" onclick="c=confirm('."'".getLL("res_list_footer_del_button_confirm")."'".');if(!c) return false;set_action(\'del_selected\');" value="'.getLL("res_list_footer_del_button").'" />');
	}

	if($access['daten']['MAX'] > 1) {
		if($access['daten']['ALL'] < 3) {
			$egs = $egs_mod = array();
			foreach($access['daten'] as $k => $v) {
				if(!intval($k)) continue;
				if($v >= 2) $egs[] = $k;
				if($v >= 3) $egs_mod[] = $k;
			}

			$z_where = " AND (`id` IN ('".implode("','", $egs_mod)."')
			OR (`id` IN ('".implode("','", $egs)."') AND `moderation` = 0))";
		} else {
			$z_where = '';
		}
		ko_get_eventgruppen($eventgroups,'', $z_where);
		$eventgroup_select = "<select name=\"sel_eventgroup\" class=\"form-control input-group-sm\">";

		foreach ($eventgroups AS $eventgroup) {
			$eventgroup_select .= "<option value='" . $eventgroup['id'] . "'>" . $eventgroup['name'] . "</option>";
		}
		$eventgroup_select .= "</select>";

		$list_footer[] = [
			'button' => '
				 <div class="form-group">
				 	<label for="sel_eventgroup">' . getLL('res_list_footer_new_event_label')  . '</label>
				 	<div class="input-group col-xs-3">
						' . $eventgroup_select . '
						<div class="input-group-btn">
							<button class="btn btn-sm btn-primary form-control" onclick="c=confirm(\''.getLL('res_list_footer_new_event_confirm').'\'); if (!c) return false;  set_action(\'new_event_selected\');$(\'form[name=&quot;formular&quot;]\').submit();">'.getLL('res_list_footer_new_event_button').'</button>
						</div>
					</div>
				</div>',
			];
	}

	$list->setFooter($list_footer);
	$list->setWarning(kota_filter_get_warntext('ko_reservation'));

	//Output the list
	$list->render($es);
}//ko_list_reservations()





//Type ist entweder "res" für eine Liste der normalen Reservationen
// oder "mod" für eine Liste der zu moderierenden Reservationen
function ko_show_res_liste($type="res") {
	global $smarty, $DATETIME, $RES_GUEST_FIELDS_FORCE;
	global $access, $KOTA;
		
	if($access['reservation']['MAX'] < 1) return;

	//Set SQL filter for res and mod
	if($type=='res') {
		//Call new function to render list of reservations using KOTA listview
		ko_list_reservations();
		return;
	}

	$z_limit = '';
	if($_SESSION['ses_userid'] != ko_get_guest_id()) {
		if($access['reservation']['ALL'] > 4) {  //Moderator for all groups
			$z_where = ' AND 1=1 ';
		} else if($access['reservation']['MAX'] > 4) {  //Moderator for only a few groups/items
			$mod_items = array();
			foreach($access['reservation'] as $k => $v) {
				if(!intval($k) || $v < 5) continue;
				$mod_items[] = $k;
			}
			$z_where = " AND `user_id` = '".$_SESSION['ses_userid']."' OR `item_id` IN ('".implode("','", $mod_items)."') ";
		} else if($access['reservation']['MAX'] > 1) {  //No moderator but new rights, so show his own moderations
			$z_where = ' AND user_id = \''.$_SESSION['ses_userid'].'\' ';
		} else {  //No right to create new reservations, so don't show anything
			$z_where = ' AND 1=2 ';
		}
	} else {
		$z_where = ' AND 1=2 ';
	}
	$kota_where = kota_apply_filter('ko_reservation_mod');
  if($kota_where) $z_where .= ' AND '.$kota_where;

	//Set time filter
	$start = ko_daten_parse_time_filter($_SESSION['filter_start']);
	if ($start != '') $z_where .= "AND enddatum >= '$start'";
	$ende = ko_daten_parse_time_filter($_SESSION['filter_ende'], '');
	if ($ende != '') $z_where .= "AND startdatum <= '$ende'";

	//Personen-Daten anzeigen oder nicht
	$guest_fields = array_merge(ko_get_setting("res_show_fields_to_guest"), $RES_GUEST_FIELDS_FORCE);
	if ($_SESSION["ses_userid"] == ko_get_guest_id()) {
		foreach ($KOTA['ko_reservation_mod']['_listview'] as $k => $v) {
			if (!in_array($v['name'], $guest_fields)) unset ($KOTA['ko_reservation_mod']['_listview'][$k]);
		}
	}

	$z_sort = '';
	if ($_SESSION['sort_item']) {
		$z_sort = ' ORDER BY `'.$_SESSION['sort_item'].'` ';
		if ($_SESSION['sort_item_order']) {
			$z_sort .= $_SESSION['sort_item_order'] . ' ';
		} else {
			$z_sort .= 'ASC ';
		}
	} else {
		$z_sort = ' ORDER BY `startdatum` ASC ';
	}

	$rows = db_get_count("ko_reservation_mod", 'id', $z_where);
	$z_limit = "LIMIT " . ($_SESSION["show_start"]-1) . ", " . $_SESSION["show_limit"];
	ko_get_reservationen($es, $z_where, $z_limit, 'mod', $z_sort);

	$manual_access = array('check' => array(), 'delete' => array());
	foreach($es as $e_i => $e) {

		//Auf Doppelbelegung testen:
		if(!ko_res_check_double($e["item_id"], $e["startdatum"], $e["enddatum"], $e["startzeit"], $e["endzeit"], $error_txt)) {
			$double = TRUE;
		} else {
			$double = FALSE;
		}

		//Check-Button
		if($access['reservation'][$e['item_id']] > 4 && !$double) {  //Bei Doppelbelegung kein Check-Button anzeigen
			$manual_access['check'][$e_i] = TRUE;
			$tpl_list_data[$e_i]["show_check_button"] = TRUE;
			$tpl_list_data[$e_i]["alt_edit"] = getLL("res_mod_confirm");
			$tpl_list_data[$e_i]["onclick_edit"] = "javascript:c1 = confirm('".getLL("res_mod_confirm_confirm")."');if(!c1) return false;c = confirm('".getLL("res_mod_confirm_confirm2")."');set_hidden_value('mod_confirm', c);set_action('res_mod_approve');set_hidden_value('id', '$e_i');this.submit";
		} else {
			$manual_access['check'][$e_i] = FALSE;
		}

		// edit button
		if($access['reservation'][$e['item_id']] > 4 || ($_SESSION["ses_userid"] != ko_get_guest_id() && $e["user_id"] == $_SESSION["ses_userid"])) {
			$manual_access['edit'][$e_i] = TRUE;
			$tpl_list_data[$e_i]["show_check_button"] = TRUE;
			$tpl_list_data[$e_i]["alt_edit"] = getLL("res_mod_confirm");
			$tpl_list_data[$e_i]["onclick_edit"] = "javascript:c1 = confirm('".getLL("res_mod_confirm_confirm")."');if(!c1) return false;c = confirm('".getLL("res_mod_confirm_confirm2")."');set_hidden_value('mod_confirm', c);set_action('res_mod_approve');set_hidden_value('id', '$e_i');this.submit";
		} else {
			$manual_access['edit'][$e_i] = FALSE;
		}

		//Delete-Button
		if($access['reservation'][$e['item_id']] > 4 || ($_SESSION["ses_userid"] != ko_get_guest_id() && $e["user_id"] == $_SESSION["ses_userid"] && !ko_get_setting('res_access_prevent_lvl2_del'))) {
			$manual_access['delete'][$e_i] = TRUE;
			$tpl_list_data[$e_i]["show_delete_button"] = TRUE;
			$tpl_list_data[$e_i]["alt_delete"] = getLL("res_mod_decline");
			$tpl_list_data[$e_i]["onclick_delete"] = "javascript:c1 = confirm('".getLL("res_mod_decline_confirm")."');if(!c1) return false;";
			if($access['reservation'][$e['item_id']] > 4) $tpl_list_data[$e_i]["onclick_delete"] .= "c = confirm('".getLL("res_mod_decline_confirm2")."');set_hidden_value('mod_confirm', c);";
			$tpl_list_data[$e_i]["onclick_delete"] .= "set_action('res_mod_delete');set_hidden_value('id', '$e_i');";
		} else {
			$manual_access['delete'][$e_i] = FALSE;
		}
	}//foreach(es)

	$list = new kOOL_listview();
	$list->init('reservation', 'ko_reservation_mod', array('chk', 'check', 'edit', 'delete'), $_SESSION['show_start'], $_SESSION['show_limit']);
	$list->setTitle(getLL('res_mod_list_title'));
	$list->setAccessRights(array('check' => 4, 'edit' => 4, 'delete' => 4), $access['reservation']);
	$list->setManualAccess('check', $manual_access['check']);
	$list->setManualAccess('edit', $manual_access['edit']);
	$list->setManualAccess('delete', $manual_access['delete']);
	$list->setActions(
		array(
			'check' => array('action' => 'res_mod_approve', 'confirm' => TRUE, 'additional_js' => "c = confirm('".getLL("res_mod_confirm_confirm2")."');set_hidden_value('mod_confirm', c);"),
			'edit' => array('action' => 'res_mod_edit'),
			'delete' => array('action' => 'res_mod_delete', 'confirm' => TRUE, 'additional_js' => "c = confirm('".getLL("res_mod_decline_confirm2")."');set_hidden_value('mod_confirm', c);"),
		)
	);
	$list->setSort(TRUE, 'setsort', $_SESSION['sort_item'], $_SESSION['sort_item_order']);
	$list->setStats($rows);
	if ($access['reservation']['MAX'] <= 4) {
		$list->disableMultiedit();
	}

	//Footer
	$list_footer = $smarty->get_template_vars('list_footer');
	if($type == "mod") {
		if ($access['reservation']['MAX'] > 1) {
			$list_footer[] = array("label" => getLL("res_list_footer_del_label"),
				"button" => '<input type="submit" onclick="c1 = confirm(\'' . getLL("res_mod_decline_confirm") . '\');if(!c1) return false;' . ($access['reservation']['MAX'] > 4 ? 'c = confirm(\'' . getLL("res_mod_decline_confirm2") . '\');set_hidden_value(\'mod_confirm\', c);' : '') . 'set_action(\'res_mod_delete_multi\');" value="' . getLL("res_list_footer_del_button") . '" />');
		}
		if ($access['reservation']['MAX'] > 4) {
			$list_footer[] = array("label" => getLL("res_list_footer_confirm_label"),
				"button" => '<input type="submit" onclick="c=confirm(' . "'" . getLL("res_mod_confirm_confirm2") . "'" . ');set_hidden_value(\'mod_confirm\', c);set_action(\'res_mod_approve_multi\');" value="' . getLL("ok") . '" />');
		}
		$smarty->assign("show_list_footer", TRUE);
		$smarty->assign("list_footer", $list_footer);

		$smarty->assign("help", ko_get_help("reservation", "show_mod_res"));
		$smarty->assign('tpl_list_title', getLL("res_mod_list_title"));
	}
	$list->setFooter($list_footer);

	//Output the list
	$list->render($es);
}//ko_show_res_liste()




function ko_show_items_liste() {
	global $smarty, $access;

	if($access['reservation']['MAX'] < 4) return;

	//Set filters from KOTA
	$z_where = '';
	$kota_where = kota_apply_filter('ko_resitem');
	if($kota_where != '') $z_where .= " AND ($kota_where) ";

	$rows = db_get_count("ko_resitem", 'id', $z_where);
	$z_limit = "LIMIT " . ($_SESSION["show_start"]-1) . ", " . $_SESSION["show_limit"];
	ko_get_resitems($res, $z_limit, 'WHERE 1 '.$z_where);

	$list = new kOOL_listview();
	$list->init('reservation', 'ko_resitem', array('chk', 'edit', 'delete'), $_SESSION['show_start'], $_SESSION['show_limit']);
	$list->setTitle(getLL('res_items_list_title'));
	$list->setAccessRights(array('edit' => 4, 'delete' => 4), $access['reservation']);
	$list->setActions(array('edit' => array('action' => 'edit_item'),
		'delete' => array('action' => 'delete_item', 'confirm' => TRUE))
		);
	if ($access['reservation']['MAX'] > 3) $list->setActionNew('new_item');
	$list->setSort(TRUE, 'setsortresgroups', $_SESSION['sort_group'], $_SESSION['sort_group_order']);
	$list->setStats($rows);
	$list->setWarning(kota_filter_get_warntext('ko_resitem'));
	//Output the list
	$list->render($res);
}//ko_show_items_liste()





/**
  * Erstellt das Formular zum Bearbeiten und Hinzufügen von Reservationen
	*/
function ko_formular_reservation($mode, $id, $data=array()) {
	global $smarty, $KOTA, $HOLIDAYS;
	global $access;

	// table on which to operate. either ko_reservation or ko_reservation_mod
	$table = 'ko_reservation';
	//Falls eine Reservation editiert werden soll, diese auslesen und die Felder mit seinen Details füllen
	if($mode=="edit" && $id != 0) {
		if($access['reservation']['MAX'] < 2) return;

		ko_get_res_by_id($id, $r_);
		$r = $r_[$id];
	}
	else if ($mode == 'edit_mod' && $id) {
		$r = db_select_data('ko_reservation_mod', "WHERE `id` = '{$id}'", '*', '', '', TRUE, TRUE);

		if($access['reservation']['ALL'] < 4 && $access['reservation'][$r['item_id']] < 4 && ($_SESSION['ses_userid'] == ko_get_guest_id() || $r['user_id'] != $_SESSION['ses_userid'])) return;

		unset($r['_event_id']); // TODO: what to do with _event_id?

		//Info über Reservation für Moderator
		if($access['reservation']['MAX'] > 1 && $_SESSION['ses_userid'] != ko_get_guest_id()) {
			ko_get_login($r['user_id'], $res_login);
			if($r['lastchange_user']) ko_get_login($r['lastchange_user'], $change_login);
			$res_info  = getLL('res_info_cdate').': <b>'.sqldatetime2datum($r['cdate']).'</b>';
			$res_info .= '<br />'.getLL('res_info_user').': <b>'.$res_login['login'].' ('.$r['user_id'].')</b>';
			$res_info .= '<br />'.getLL('res_info_mdate').': <b>'.sqldatetime2datum($r['last_change']).'</b>';
			if($change_login['login']) $res_info .= '<br />'.getLL('res_info_muser').': <b>'.$change_login['login'].' ('.$r['lastchange_user'].')</b>';
			//$res_info .= '<br />'.getLL('res_info_code').': <b>'.$r['code'].'</b>';
		}

		$table = 'ko_reservation_mod';
	}//if(mode==edit_mod && id)
	else if($mode == "neu") {
		if($access['reservation']['MAX'] < 2) return;

		//fill in values if POST-Values are set
		if($_POST["submit"]) {
			$form_values = NULL;
			foreach($_POST["koi"]["ko_reservation"] as $col => $value) {
				if(isset($KOTA["ko_reservation"][$col])) {
					$form_values[$col] = $value[0];
				}
			}
			kota_assign_values("ko_reservation", $form_values);

		//Or else use Data of logged in user
		} else {
			$p = ko_get_logged_in_person('', FALSE);
			if($p['id'] || $p['email']) {
				$email = ko_get_userpref($_SESSION['ses_userid'], 'res_prefill_email');
				if (!$email) $email = 'email';
				$phone = ko_get_userpref($_SESSION['ses_userid'], 'res_prefill_tel');
				if (!$phone) $phone = $p['telg']?'telg':'telp';
				$name = ko_get_userpref($_SESSION['ses_userid'], 'res_prefill_name');
				if(trim($name) == '-') $fillName = '';
				else $fillName = ($name ? $name : ($p['vorname'].' '.$p['nachname']));
				kota_assign_values(
					'ko_reservation',
					array(
						'name' => $fillName,
						'email' => ($email == '_none' ? '' : $p[$email]),
						'telefon' => ($phone == '_none' ? '' : $p[$phone]),
					)
				);
			}
		}

		//given as argument in Funktion (by GET-Values)
		if(isset($data["start_time"])) kota_assign_values("ko_reservation", array("startzeit" => $data["start_time"]));
		if(isset($data["end_time"])) kota_assign_values("ko_reservation", array("endzeit" => $data["end_time"]));
		if(isset($data['item_id'])) kota_assign_values('ko_reservation', array('item_id' => $data['item_id']));
	}
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
	$output = NULL; $monday = date_find_last_monday(date("Y-m-d"));
	for($i=0; $i<7; $i++) $output[] = strftime("%A", strtotime(add2date($monday, "tag", $i, TRUE)));
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
	$gc = 0;

	//allow multiple objects to be reserved at once
	if($mode == "neu" && ($_SESSION["ses_userid"] != ko_get_guest_id() || ko_get_setting("res_allow_multires_for_guest")) ) {
		$KOTA[$table]["item_id"]["form"]["type"] = "dyndoubleselect";
		$KOTA[$table]["item_id"]["form"]["js_func_add"] = "resgroup_doubleselect_add";
	}
	$KOTA[$table]["item_id"]["form"]["params"] = "size=\"5\" onchange=\"javascript:changeResItem($(this).data('value'));\"";

	//get first part of form from kota
	$group = ko_multiedit_formular($table, "", $id, "", "", TRUE, '', TRUE, 'formular', '2');
	$group[0]['groups'][$gc]["title"] = "";
	$group[0]['groups'][$gc]["group"] = TRUE;
	$group[0]['groups'][$gc]["state"] = 'open';

	$rowcounter = sizeof($group[0]['groups'][$gc]["rows"])+1;

	//Wiederholungen (nur bei Neu)
	if($mode == "neu") {
		$group[0]['groups'][++$gc] = array("titel" => getLL("daten_repeat"), "group" => TRUE, "state" => $true == 0 ? "closed" : "open", "colspan" => 'colspan="2"');
		$group[0]['groups'][$gc]["rows"][$rowcounter]["inputs"][0] = array(
			"desc" => getLL("daten_repeat_title1"),
			"type" => "radio",
			"name" => "rd_wiederholung",
			"values" => array("keine", "taeglich", "woechentlich", "monatlich1", "monatlich2", "holidays", "dates"),
			"descs" => $repeat_descs,
			"separator" => "<br />",
			"add_class" => "res-conflict-field",
			"value" => isset($_POST["rd_wiederholung"]) ? $_POST["rd_wiederholung"] : "keine"
		);
		$group[0]['groups'][$gc]["rows"][$rowcounter++]["inputs"][1] = array("desc" => getLL("daten_repeat_title2"),
			"type" => "html",
			"value" => $repeat_stop
		);
	}
	if(in_array($mode, array("edit", 'edit_mod')) && $r["serie_id"]) {
		$group[0]['groups'][$gc]["rows"][$rowcounter++]["inputs"][0] = array("type" => "   ");
		$group[0]['groups'][$gc]["rows"][$rowcounter++]["inputs"][0] = array(
			"desc" => getLL("res_serie_title"),
			"type" => "checkbox",
			"name" => "chk_serie",
			"value" => "1",
			"desc2" => getLL("res_serie_apply")." (".db_get_count($table, "id", "AND `serie_id` = '".$r["serie_id"]."'").")",
			"colspan" => 'colspan="2"',
			"add_class" => "res-conflict-field",
		);
	}

	$group[0]['groups'][++$gc] = array("titel" => getLL("res_conflicts"), "group" => TRUE, "state" => "open", "colspan" => 'colspan="2"', 'name' => 'res-conflicts', 'appearance' => 'danger');
	$group[0]['groups'][$gc]['rows'][$rowcounter++]['inputs'][0] = array(
		'desc' => '',
		'type' => 'html',
		'value' => '<script>$("#group_res-conflicts").hide();</script>',
	);

	switch ($mode) {
		case 'edit':
			if(
				$access['reservation'][$r['item_id']] > 3 ||
				(
					$_SESSION["ses_userid"] == $r["user_id"] &&
					$_SESSION["ses_userid"] != ko_get_guest_id() &&
					$access['reservation'][$r['item_id']] > 2
				)
			) {
				if(empty($group[0]['groups'][0]['options'])) {
					unset($group[0]['groups'][0]['options']);
				}
				$group[0]['groups'][0]['options']['delete_action'] = 'delete_res';
			}

			$smarty->assign("tpl_titel", getLL("res_edit_reservation"));
			$smarty->assign("tpl_action", "submit_edit_reservation");

			$smarty->assign("tpl_submit_as_new", getLL('res_submit_as_new'));
			$smarty->assign("tpl_action_as_new", "submit_neue_reservation");
			break;
		case 'edit_mod':
			$smarty->assign("tpl_titel", getLL("res_edit_mod"));

			$specialSubmit =
				'<button type="submit" class="btn btn-primary" name="submit" class="ko_form_submit" value="'.getLL("res_edit_mod").'" onclick="var ok = check_mandatory_fields($(this).closest(\'form\')); if (ok) {disable_onunloadcheck(); set_action(\'submit_edit_res_mod\', this)} else return false;">
	'.getLL("save").' <i class="fa fa-save"></i>
</button>';
			if ($access['reservation']['ALL'] > 3 || $access['reservation'][$r['item_id']] > 3) {
				$specialSubmit .=
					'<button type="submit" class="btn btn-success" name="submit" class="ko_form_submit" value="'.getLL("res_edit_and_approve_mod").'" onclick="var ok = check_mandatory_fields($(this).closest(\'form\')); if (ok) {disable_onunloadcheck(); set_action(\'submit_approve_edit_res_mod\', this)} else return false;">
	'.getLL("res_edit_and_approve_mod").' <i class="fa fa-check"></i>
</button>';
			}
			$smarty->assign("tpl_special_submit", $specialSubmit);
			break;
		case 'neu':
			$smarty->assign("tpl_titel", getLL("res_new_res"));
			$smarty->assign("tpl_action", "submit_neue_reservation");
			break;
	}

	$smarty->assign("tpl_submit_value", getLL("save"));
	$smarty->assign("tpl_id", $id);

	$cancel = $_SESSION['show_back'] ? $_SESSION['show_back'] : ko_get_userpref($_SESSION["ses_userid"], "default_view_reservation");
	if(!$cancel) $cancel = "show_cal_monat";
	$smarty->assign("tpl_cancel", $cancel);
	$smarty->assign("tpl_groups", $group);

	$smarty->assign("help", ko_get_help("reservation", "neue_reservation"));

	$smarty->display("ko_formular2.tpl");
}//ko_formular_reservation()




/**
  * Erstellt das Formular zum Bearbeiten und Hinzufügen von Reservations-Items
	*/
function ko_formular_item($mode, $id) {
	global $smarty;
	global $KOTA;
	global $access;

	if($mode == "edit") {
		if($access['reservation']['MAX'] < 4) return;
	} else if($mode == "neu") {
		if($access['reservation']['MAX'] < 4) return;
		$id = 0;
	} else return;

	//Select for linked items
	$where = "AND `linked_items` = '' ";  //no hierarchical linking
	if($mode == "edit") $where .= " AND `id` <> '$id'";  //no linking to itself
	kota_ko_reservation_item_id_dynselect($values, $descs, 1);
	$KOTA["ko_resitem"]["linked_items"]["form"]["values"] = $values;
	$KOTA["ko_resitem"]["linked_items"]["form"]["descs"] = $descs;

	//Only show text input for adding res group if ALL edit rights is set
	if($access['reservation']['ALL'] < 4) {
		//no textplus, so no new group can be added
		$KOTA["ko_resitem"]["gruppen_id"]["form"]["type"] = "select";
		$KOTA["ko_resitem"]["gruppen_id"]["form"]["params"] = 'size="0"';
		$KOTA["ko_resitem"]["gruppen_id"]["form"]["new_line"] = TRUE;
		//Use descs as values so code for storing will work, as this expects the name of the group and not the id
		$KOTA["ko_resitem"]["gruppen_id"]["form"]["values"] = $KOTA["ko_resitem"]["gruppen_id"]["form"]["descs"];

		//Mark current option as selected when editing
		if($mode == 'edit') {
			ko_get_resitem_by_id($id, $resitem_);
			$resitem = $resitem_[$id];
			$resgroup = db_select_data('ko_resgruppen', "WHERE `id` = '".$resitem['gruppen_id']."'", '*', '', '', TRUE);
			$KOTA['ko_resitem']['gruppen_id']['form']['value'] = $resgroup['name'];
		}
	}


	$form_data["title"] = $mode == "neu" ? getLL("res_new_object") : getLL("res_edit_object");
  $form_data["submit_value"] = getLL("save");
  $form_data["action"] = $mode == "neu" ? "submit_new_item" : "submit_edit_item";
  $form_data["cancel"] = "list_items";

	ko_multiedit_formular("ko_resitem", "", $id, "", $form_data);
}//ko_formular_item()


/**
 * Stores new reservations to be moderated
 *
 * @param array $reservations
 * @param bool $sendEmails
 */
function ko_res_store_moderation($reservations, $sendEmails = TRUE) {
	$clientEmail = array();
	$mod_obj = array();
	$items = db_select_data("ko_resitem", "WHERE 1=1", "*");

	foreach($reservations as $reservation) {
		$resitem = $items[$reservation["item_id"]];
		//add other reservations
		$reservation["code"] = substr(md5($reservation["name"].microtime()), 2, 8);
		$reservation["cdate"] = strftime("%Y-%m-%d %H:%M:%S", time());
		$reservation["last_change"] = strftime("%Y-%m-%d %H:%M:%S", time());
		$reservation['lastchange_user'] = $_SESSION['ses_userid'];
		$reservation["linked_items"] = $resitem["linked_items"];
		if(!$reservation["user_id"]) $reservation["user_id"] = $_SESSION["ses_userid"];

		//Store moderation
		$new_id = db_insert_data("ko_reservation_mod", $reservation);

		//Add log entry
		$log_data = $reservation;
		$log_data['id'] = $new_id;
		ko_log_diff('new_mod_res', $log_data);

		//Prepare email text for moderators
		if($resitem['moderation'] == 2 && $sendEmails) {
			$txt = ko_get_res_infotext($reservation);
			//Add links to confirm and delete reservation
			$txt .= "\n".ko_get_mod_links($reservation, $new_id);
			$mod_obj[$resitem["gruppen_id"]]['items'][$resitem['id']].= $txt."\n\n";
			if(check_email($reservation['email']) && sizeof($clientEmail) < 1) {
				$clientEmail[$reservation['email']] = $reservation['name'];
			}
		}
	}//foreach(reservations as reservation)

	//Inform all moderators for the different resgroups
	if($sendEmails) {
		ko_res_send_mod_mail($mod_obj, $clientEmail);
	}
}//ko_res_store_moderation()

/**
 * Send moderation emails containing objects. Mails will be grouped for each moderator.
 *
 * @param array $objects grouped reservations
 */
function ko_res_send_mod_mail($objects, $clientEmail = array()) {
	global $MAIL_TRANSPORT;

	$mods = db_select_data("ko_admin AS a LEFT JOIN ko_leute as l ON a.leute_id = l.id",
		"WHERE (a.disabled = '0' OR a.disabled = '')",
		"a.id AS id, a.email AS admin_email, l.vorname as vorname, l.nachname as nachname, l.id as leute_id");

	//Set proper emails for moderators
	ko_get_moderators_email($mods);

	foreach($mods as $mod) {
		$mod['user_access'] = ko_get_access('reservation', $mod['id'], TRUE, TRUE, 'login', FALSE);
		if(!$mod['user_access']) continue;

		$group_txt_full = '';
		$res_access_mode = ko_get_setting("res_access_mode");
		foreach($objects as $gid => $items) {
			if(!$gid) continue;
			foreach($items['items'] as $item_id => $item) {
				if($mod['user_access']['reservation']['ALL'] < 5) {
					if (
						($res_access_mode == 0 && $mod['user_access']['reservation']['grp' . $gid] < 5) ||
						($res_access_mode == 1 && $mod['user_access']['reservation'][$item_id] < 5)
					) continue;
				}

				$group_txt_full .= $item;
			}
		}

		if(empty($group_txt_full)) continue;

		if(!check_email($mod['email'])) continue;

		$mailtext = str_replace("[RES]", "\n\n".$group_txt_full, getLL("res_email_mod_text"));
		//Replace USERHASH in confirm/delete links for each moderator
		$text = str_replace('###USERHASH###', md5($mod['id'].KOOL_ENCRYPTION_KEY), $mailtext);

    if(sizeof($clientEmail) > 0) $replyTo = $clientEmail;
    else $replyTo = array();

		ko_send_mail('', $mod['email'], getLL('email_subject_prefix').getLL("res_email_mod_subject"), ko_emailtext($text), array(), array(), array(), $replyTo);
	}
}//ko_res_send_mod_mail()




function ko_get_mod_links(&$res, $id) {
	global $BASE_URL;

	$r = '';

	if(!$id) return $r;
	if(!defined('KOOL_ENCRYPTION_KEY') || KOOL_ENCRYPTION_KEY == '') return $r;

	$hash = md5($id.KOOL_ENCRYPTION_KEY);
	$r .= getLL('res_mod_link_confirm').": \n";
	$r .= '- '.getLL('res_mod_with_notification').': <'.$BASE_URL.'reservation/?u=###USERHASH###&h=c'.$hash."&c=1>\n";
	$r .= '- '.getLL('res_mod_without_notification').': <'.$BASE_URL.'reservation/?u=###USERHASH###&h=c'.$hash.">\n";

	$r .= getLL('res_mod_link_delete').": \n";
	$r .= '- '.getLL('res_mod_with_notification').': <'.$BASE_URL.'reservation/?u=###USERHASH###&h=d'.$hash."&c=1>\n";
	$r .= '- '.getLL('res_mod_without_notification').': <'.$BASE_URL.'reservation/?u=###USERHASH###&h=d'.$hash.">\n";

	return $r;
}




function ko_res_mod_approve($ids, $notification=TRUE) {
	global $do_action, $access, $smarty;

	$items = db_select_data("ko_resitem", "WHERE 1=1", "*");

	$res_text = '';
	$email_rec = $noemail_rec = array();
	$res_data = array();
	foreach($ids as $id) {
		//Get mod entry
		ko_get_res_mod_by_id($res, $id);
		$r = $res[$id];
		if(!$r["item_id"]) continue;

		$res_backup[] = $r;

		//Access check for this item
		if($access['reservation']['ALL'] < 5 && $access['reservation'][$r['item_id']] < 5) continue;

		//Neue Reservation speichern
		$r["last_change"] = strftime("%Y-%m-%d %H:%M:%S", time());
		$r['lastchange_user'] = $_SESSION['ses_userid'];
		unset($r['_orig_res_id']);
		$res_data[] = $r;

		//Mod-Eintrag löschen
		//db_delete_data("ko_reservation_mod", "WHERE `id` = '$id'");

		//Log
		ko_get_login($r["user_id"], $log_login);
		$r["user"] = $log_login["login"];
		$r["notification"] = $notification ? getLL("yes") : getLL("no");
		ko_log_diff("mod_res_approved", $r);

		//Store information for notification email
		if($notification) {
			if($r["email"]) $email_rec[] = $r["email"];
			else $noemail_rec[] = ko_html($r["name"]).", ".ko_html($r["telefon"]);
			$res_text .= ko_get_res_infotext($r)."\n\n";
		}
	}

	$new_ids = ko_res_store_reservation($res_data, FALSE, $double_error_txt, $success, $failure);
	// delete moderations that were successfully transformed into real reservations
	foreach ($success as $sId) {
		db_delete_data("ko_reservation_mod", "WHERE `id` = '{$sId}'");
	}
	if($double_error_txt) {
		koNotifier::Instance()->addError(4, $do_action);
		koNotifier::Instance()->addTextError(getLL('res_collision').' <i>'.$double_error_txt.'</i><br />', $do_action);
	}
	else {
		koNotifier::Instance()->addInfo(6, $do_action);
	}

	//Show email form to send notification
	if($notification) {
		$smarty->assign("txt_empfaenger", implode(", ", array_unique($email_rec)));
		$smarty->assign('txt_empfaenger_semicolon', implode('; ', array_unique($email_rec)));
		$smarty->assign("tpl_ohne_email", ($r["email"] == "" ? implode(", ", array_unique($noemail_rec)) : getLL("res_mod_no")) );
		$p = ko_get_logged_in_person('', FALSE);
		$smarty->assign("tpl_show_bcc_an_mich", ($p["email"] ? TRUE : FALSE));
		$smarty->assign("tpl_show_send", TRUE);
		$smarty->assign('txt_betreff', (getLL('email_subject_prefix').(sizeof($new_ids) > 1 ? getLL('res_emails_mod_confirm_subject') : getLL('res_email_mod_confirm_subject'))) );
		$smarty->assign('tpl_res_ids', implode(',', $new_ids));

		$smarty->assign('txt_emailtext', ((sizeof($new_ids) > 1 ? getLL('res_emails_mod_confirm_text') : getLL('res_email_mod_confirm_text'))."\n\n".ko_html($res_text)) );

		$smarty->assign("tpl_show_rec_link", TRUE);

		$_SESSION["show"]= "email_confirm";
	}//if(notification)
}




/**
  * Stores a new reservation and sends all necessary emails
	*/
function ko_res_store_reservation($data, $send_user_email, &$double_error_txt = null, &$success = null, &$failure = null) {
	global $access, $BASE_PATH, $MAIL_TRANSPORT;

	$ids = array();  //Will hold the ids of the new reservations
	$res_txt = "";
	$user_email = "";
	$res3_txt = array();
	$info_txt = array();
	$items = db_select_data("ko_resitem", "WHERE 1=1", "*");
	$double_error_txt = '';

	foreach($data as $res) {
		$oldId = $res['id'];

		//Double check (needed as fallback and also e.g. if more than one reservation is given in data,
		// they might overlap internally
		if(FALSE === ko_res_check_double($res['item_id'], $res['startdatum'], $res['enddatum'], $res['startzeit'], $res['endzeit'], $_double_error_txt)) {
			$double_error_txt .= $_double_error_txt;
			$failure[] = $oldId;
			continue;
		}

		// unset id if set
		unset($res['id']);

		$resitem = $items[$res["item_id"]];
		//add other data
		if(!$res["code"]) $res["code"] = substr(md5($res["name"].microtime()), 2, 8);
		$res["last_change"] = strftime("%Y-%m-%d %H:%M:%S", time());
		$res['lastchange_user'] = $_SESSION['ses_userid'];
		$res["linked_items"] = $resitem["linked_items"];
		if(!$res["cdate"]) $res["cdate"] = strftime("%Y-%m-%d %H:%M:%S", time());
		if(!$res["user_id"]) $res["user_id"] = $_SESSION["ses_userid"];
		$do_event = $res["_event_id"]; unset($res["_event_id"]);
		unset($res['event_id']);  //Only used for list view

		$new_id = db_insert_data("ko_reservation", $res);
		$txt = ko_get_res_infotext($res);
		$res_txt .= $txt."\n\n";
		//Level 3 user making a reservation for a moderated item
		if($resitem["moderation"] > 0 && $access['reservation'][$res['item_id']] == 4) {
			$res3_txt[$resitem["gruppen_id"]] .= $txt."\n\n";
		}
		//Info email by item
		if($resitem['email_recipient']) {
			$info_txt[$resitem['id']]['text'] .= $txt."\n\n";
			$info_txt[$resitem['id']]['email'] = $resitem['email_recipient'];
			$info_txt[$resitem['id']]['leadtext'] = $resitem['email_text'];
		} else if(ko_get_setting('res_send_email')) {
			$info_txt['_default']['text'] .= $txt."\n\n";
			$info_txt['_default']['email'] = ko_get_setting('res_send_email');
		}

		//Store user's email address
		if($send_user_email && !$user_email && $res['email']) {
			$user_email = $res['email'];
			$user_name = $res['name'];
		}

		//Update event, this reservation belongs to
		if($do_event) {
			$event = db_select_data("ko_event", "WHERE `id` = '$do_event'", "*", "", "", TRUE);
			$new_reservationen = $event["reservationen"] ? ($event["reservationen"].",$new_id") : $new_id;
			db_update_data("ko_event", "WHERE `id` = '$do_event'", array("reservationen" => $new_reservationen, 'last_change' => date('Y-m-d H:i:s'), 'lastchange_user' => $_SESSION['ses_userid']));
		}

		//Log entry
		$log_message = $new_id.": ".$resitem["name"].", ".$res["startdatum"].", ".$res["zweck"].", ".$res["name"];
		ko_log("new_res", $log_message);

		$ids[] = $new_id;
		$success[] = $oldId;
	}//foreach(data as res)

	$replyTo = check_email($user_email) ? $user_email : '';

	//1: Mail to user who did the reservation
	if($send_user_email && $user_email) {
		$mailtext = getLL("res_email_confirm_text")."\n\n".$res_txt;
		if(ko_get_setting('res_attach_ics_for_user')) {
			ko_get_reservationen($ics_res, "AND ko_reservation.id IN (".implode(',', $ids).")");
			$ics_filename = ko_get_ics_file('res', $ics_res, TRUE);
			$file = array($BASE_PATH.'download/'.$ics_filename => getLL('res_ical_filename'));
		} else {
			$file = NULL;
		}

		ko_send_mail(ko_get_setting('info_email'), $user_email, getLL('email_subject_prefix').getLL("res_email_confirm_subject"), $mailtext, $file);
	}

	//2: Mail for moderator, if user with access level 4 has made a reservation of a moderated object
	if(sizeof($res3_txt) > 0) {
		foreach($res3_txt as $gid => $txt) {
			$mailtext = sprintf(getLL("res_email_mod3_text"), ("\n\n".$txt));
			$mods = ko_get_moderators_by_resgroup($gid);
			foreach($mods as $mod) {
				//Check user_pref for this moderator
				if (!$mod['email']) continue;
				if(ko_get_userpref($mod["id"], "do_mod_email_for_edit_res") == 1) {
					ko_send_mail('', $mod["email"], getLL('email_subject_prefix').getLL("res_email_mod3_subject"), $mailtext, array(), array(), array(), $replyTo);
				}
			}
		}
	}

	//3: Mail to given addresses for each reservation
	foreach($info_txt as $data) {
		if(!$data['email'] || !$data['text']) continue;
		$recipients = explode(',', str_replace(';', ',', $data['email']));
		foreach($recipients as $rec) {
			$rec = trim($rec);
			if(!check_email($rec)) continue;
			ko_send_mail('', $rec, getLL('res_email_confirm_subject'), getLL('res_email_confirm_text')."\n\n".$data['leadtext']."\n\n".$data['text'], array(), array(), array(), $replyTo);
		}//foreach(recipients)
	}

	return $ids;
}//ko_res_store_reservation()




/**
  * Displays settings for the reservations
	*/
function ko_res_settings() {
	global $smarty;
	global $access, $MODULES;
	global $LEUTE_MOBILE_FIELDS;
	global $LEUTE_EMAIL_FIELDS;
	global $RES_GUEST_FIELDS_FORCE;

	if($access['reservation']['MAX'] < 1 || $_SESSION['ses_userid'] == ko_get_guest_id()) return FALSE;

	//build form
	$gc = 0;
	$rowcounter = 0;
	$frmgroup[$gc]['titel'] = getLL('settings_title_user');
	$frmgroup[$gc]['tab'] = True;

	$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('res_settings_default_view'),
			'type' => 'select',
			'name' => 'sel_reservation',
			'values' => array('liste', 'show_cal_monat', 'show_cal_woche', 'show_resource_month', 'show_resource_week', 'show_resource_day'),
			'descs' => array(getLL('submenu_reservation_liste'), getLL('submenu_reservation_cal_month'), getLL('submenu_reservation_cal_week'), getLL('daten_resource_month'), getLL('daten_resource_week'), getLL('daten_resource_day')),
			'value' => ko_html(ko_get_userpref($_SESSION['ses_userid'], 'default_view_reservation'))
			);
	$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('admin_settings_limits_numberof_reservations'),
			'type' => 'text',
			'params' => 'size="10"',
			'name' => 'txt_limit_reservation',
			'value' => ko_html(ko_get_userpref($_SESSION['ses_userid'], 'show_limit_reservation'))
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

	$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('admin_settings_view_weekcal_intermediate_times'),
		'type' => 'text',
		'params' => 'size="30"',
		'name' => 'txt_cal_woche_intermediate_times',
		'value' => ko_html(ko_get_userpref($_SESSION['ses_userid'], 'cal_woche_intermediate_times'))
	);

	$frmgroup[$gc]['row'][$rowcounter++]['inputs'][0] = array('type' => '   ');

	$value = ko_get_userpref($_SESSION['ses_userid'], 'res_monthly_title');
	$frmgroup[$gc]['row'][$rowcounter++]['inputs'][0] = array('desc' => getLL('res_settings_monthly_title'),
			'type' => 'select',
			'name' => 'sel_monthly_title',
			'values' => array('item_id', 'zweck', 'name', 'item_id_zweck'),
			'descs' => array(getLL('kota_ko_reservation_item_id'), getLL('kota_ko_reservation_zweck'), getLL('kota_ko_reservation_name'), getLL('kota_ko_reservation_item_id_zweck')),
			'value' => $value,
			);
	$value = ko_get_userpref($_SESSION['ses_userid'], 'res_mark_sunday');
	$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('res_settings_mark_sunday'),
			'type' => 'switch',
			'name' => 'sel_mark_sunday',
			'label_0' => getLL('no'),
			'label_1' => getLL('yes'),
			'value' => $value == '' ? 0 : $value,
			);
	$value = ko_html(ko_get_userpref($_SESSION['ses_userid'], 'show_dateres_combined'));
	$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('admin_settings_options_dateres_combined'),
			'type' => 'switch',
			'name' => 'sel_show_dateres_combined',
			'label_0' => getLL('no'),
			'label_1' => getLL('yes'),
			'value' => $value == '' ? 0 : $value,
			);

	$frmgroup[$gc]['row'][$rowcounter++]['inputs'][0] = array('type' => '   ');

	$value = ko_get_userpref($_SESSION['ses_userid'], 'res_pdf_show_time');
	$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('res_settings_pdf_show_time'),
			'type' => 'select',
			'name' => 'sel_pdf_show_time',
			'values' => array('2', '1', '0'),
			'descs' => array(getLL('res_settings_pdf_show_time_2'), getLL('res_settings_pdf_show_time_1'), getLL('no')),
			'value' => $value
			);
	$value = ko_get_userpref($_SESSION['ses_userid'], 'res_pdf_show_comment');
	$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('res_settings_pdf_show_comment'),
			'type' => 'switch',
			'name' => 'sel_pdf_show_comment',
			'label_0' => getLL('no'),
			'label_1' => getLL('yes'),
			'value' => $value == '' ? 0 : $value,
			);
	$value = ko_get_userpref($_SESSION['ses_userid'], 'res_pdf_week_start');
	$monday = date_find_last_monday(date('Y-m-d'));
	$daynames[] = strftime('%A', strtotime($monday));
	for($i=1; $i<7; $i++) {
		$daynames[] = strftime('%A', strtotime(add2date($monday, 'tag', $i, TRUE)));
	}
	$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('res_settings_pdf_week_start'),
			'type' => 'select',
			'name' => 'sel_pdf_week_start',
			'values' => array(1,2,3,4,5,6,0),
			'descs' => $daynames,
			'value' => $value,
			);
	$value = ko_get_userpref($_SESSION['ses_userid'], 'res_pdf_week_length');
	$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('res_settings_pdf_week_length'),
			'type' => 'select',
			'name' => 'sel_pdf_week_length',
			'values' => array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21),
			'descs' => array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21),
			'value' => $value,
			);
	$value = ko_get_userpref($_SESSION['ses_userid'], 'res_export_show_legend');
	$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('res_settings_export_show_legend'),
			'type' => 'switch',
			'name' => 'sel_export_show_legend',
			'label_0' => getLL('no'),
			'label_1' => getLL('yes'),
			'value' => $value == '' ? 0 : $value,
			);

	$value = ko_get_userpref($_SESSION['ses_userid'], 'res_name_in_pdffooter');
	$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('res_settings_name_in_pdffooter'),
		'type' => 'switch',
		'name' => 'sel_name_in_pdffooter',
		'label_0' => getLL('no'),
		'label_1' => getLL('yes'),
		'value' => $value === null ? 1 : $value,
	);

	$value = ko_get_userpref($_SESSION['ses_userid'], 'res_contact_in_export');
	$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('res_settings_contact_in_export'),
		'type' => 'switch',
		'name' => 'sel_contact_in_export',
		'label_0' => getLL('no'),
		'label_1' => getLL('yes'),
		'value' => $value == '' ? 0 : $value,
	);

	$frmgroup[$gc]['row'][$rowcounter++]['inputs'][0] = array('type' => '   ');

	if($_SESSION['ses_userid'] != ko_get_guest_id()) {
		if($access['reservation']['MAX'] > 1) {
			$value = ko_get_userpref($_SESSION['ses_userid'], 'do_res_email');
			if($value == '') $value = 0;
			$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('admin_settings_misc_resemail'),
					'type' => 'switch',
					'name' => 'sel_do_res_email',
					'label_0' => getLL('no'),
					'label_1' => getLL('yes'),
					'value' => $value == '' ? 0 : $value,
					);
		}

		if($access['reservation']['MAX'] > 4) {
			$value = ko_get_userpref($_SESSION['ses_userid'], 'do_mod_email_for_edit_res');
			if($value == '') $value = 0;
			$frmgroup[$gc]['row'][$rowcounter]['inputs'][1] = array('desc' => getLL('admin_settings_misc_resemail_3'),
					'type' => 'switch',
					'name' => 'sel_do_mod_email_for_edit_res',
					'label_0' => getLL('no'),
					'label_1' => getLL('yes'),
					'value' => $value == '' ? 0 : $value,
					);
		}
		$rowcounter++;
	}

	if($_SESSION['ses_userid'] != ko_get_guest_id()) {
		$value = ko_html(ko_get_userpref($_SESSION['ses_userid'], 'res_ical_deadline'));
		$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('res_settings_ical_deadline'),
				'type' => 'select',
				'name' => 'sel_ical_deadline',
				'values' => array(0, -1, -2, -3, -6, -12),
				'descs' => array(getLL('res_settings_ical_deadline_0'), getLL('res_settings_ical_deadline_1'), getLL('res_settings_ical_deadline_2'), getLL('res_settings_ical_deadline_3'), getLL('res_settings_ical_deadline_6'), getLL('res_settings_ical_deadline_12')),
				'value' => $value,
				);

		if(in_array('leute', $MODULES)) {
			//Prefill: name
			$value = ko_get_userpref($_SESSION['ses_userid'], 'res_prefill_name');
			$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('res_settings_prefill_name'),
				'type' => 'text',
				'name' => 'txt_prefill_name',
				'value' => $value,
			);

			//Prefill: phone
			$values = array_unique(array_merge(array('_none', 'telp', 'telg'), $LEUTE_MOBILE_FIELDS));
			$descs = array();
			foreach ($values as $v) {
				if($v == '_none') {
					$descs[] = getLL('none');
				} else {
					$descs[] = getLL('kota_ko_leute_'.$v);
				}
			}
			$value = ko_get_userpref($_SESSION['ses_userid'], 'res_prefill_tel');
			$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('res_settings_prefill_tel'),
				'type' => 'select',
				'name' => 'sel_prefill_tel',
				'values' => $values,
				'descs' => $descs,
				'value' => $value,
			);

			//Prefill: email
			$values = array_merge(array('_none'), $LEUTE_EMAIL_FIELDS);
			$descs = array();
			foreach ($values as $v) {
				if($v == '_none') {
					$descs[] = getLL('none');
				} else {
					$descs[] = getLL('kota_ko_leute_'.$v);
				}
			}
			$value = ko_get_userpref($_SESSION['ses_userid'], 'res_prefill_email');
			if(!$value) $value = 'email';
			$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('res_settings_prefill_email'),
				'type' => 'select',
				'name' => 'sel_prefill_email',
				'values' => $values,
				'descs' => $descs,
				'value' => $value,
			);
		}

		// preset for filtering reservations in home
		$presets = ko_get_userpref($_SESSION['ses_userid'], '', 'res_itemset');
		$presets = $presets?$presets:array();
		$globalPresets = ko_get_userpref(-1, '', 'res_itemset');
		$globalPresets = $globalPresets?$globalPresets:array();
		$presetNames = array_merge(array(''), array_column($presets, 'key'), array_map(function($e){return "[G] {$e['key']}";}, $globalPresets));
		$values = $descs = $presetNames;
		$value = ko_get_userpref($_SESSION['ses_userid'], 'res_fm_filter');
		$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('res_settings_fm_filter'),
			'type' => 'select',
			'name' => 'sel_fm_filter',
			'values' => $values,
			'descs' => $descs,
			'value' => $value
		);

		//Email to be used for moderation emails
		if($access['reservation']['MAX'] > 4) {
			$values = $LEUTE_EMAIL_FIELDS;
			$descs = array();
			foreach ($values as $v) {
				$descs[] = getLL('kota_ko_leute_'.$v);
			}
			//Add admin email from login as option
			array_unshift($values, 'admin');
			array_unshift($descs, getLL('admin_logins_email'));

			//Add option to not get any emails
			array_push($values, 'none');
			array_push($descs, getLL('res_settings_mod_email_none'));

			$value = ko_get_userpref($_SESSION['ses_userid'], 'res_mod_email');
			$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('res_settings_mod_email'),
				'type' => 'select',
				'name' => 'sel_mod_email',
				'values' => $values,
				'descs' => $descs,
				'value' => $value,
			);
		}
	}




	if($access['reservation']['ALL'] > 3) {
		$gc++;
		$frmgroup[$gc]['titel'] = getLL('settings_title_global');
		$frmgroup[$gc]['tab'] = TRUE;

		$value = ko_html(ko_get_setting('res_send_email'));
		$frmgroup[$gc]['row'][$rowcounter++]['inputs'][0] = array('desc' => getLL('res_settings_send_email'),
				'type' => 'text',
				'name' => 'txt_send_email',
				'value' => $value,
				'params' => 'size="60"',
				);

		// mandatory fields
		$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = kota_get_mandatory_fields_choices_for_sel('ko_reservation');
		$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = kota_get_mandatory_fields_choices_for_sel('ko_reservation', TRUE);


		$resFieldsExclude = array('id', 'item_id', 'cdate', 'user_id', 'last_change', 'lastchange_user', 'code', 'serie_id', 'linked_items');
		$fields = db_get_columns('ko_reservation');
		$values = $descs = array();
		foreach ($fields as $field) {
			if (!in_array($field['Field'], $resFieldsExclude) && !in_array($field['Field'], $RES_GUEST_FIELDS_FORCE)) {
				$values[] = $field['Field'];
				$descs[] = (trim(getLL('kota_ko_reservation_' . $field['Field'])) == '' ? $field['Field'] : getLL('kota_ko_reservation_' . $field['Field']));
			}
		}
		$value = ko_get_setting('res_show_fields_to_guest');
		$avalues = array_filter(array_map(function($el) {return trim($el);}, explode(',', $value)), function($el) {return $el != '';});
		$adescs = array();
		foreach ($avalues as $k => $av) {
			if (!in_array($av, $values)) unset($avalues[$k]);
			else $adescs[] = getLL('kota_ko_reservation_'.$av);
		}
		$avalue = implode(',', $avalues);
		$frmgroup[$gc]['row'][$rowcounter++]['inputs'][0] = array('desc' => getLL('admin_settings_misc_show_resdata'),
			'type' => 'checkboxes',
			'name' => 'sel_res_show_fields_to_guest',
			'values' => $values,
			'avalues' => $avalues,
			'descs' => $descs,
			'adescs' => $adescs,
			'avalue' => $avalue,
			'size' => '6',
		);

		$value = ko_get_setting('res_allow_multires_for_guest');
		$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('res_settings_allow_multires_for_guest'),
				'type' => 'switch',
				'name' => 'sel_allow_multires',
				'label_0' => getLL('no'),
				'label_1' => getLL('yes'),
				'value' => $value == '' ? 0 : $value,
				);
		$value = ko_get_setting('res_show_ical_links_to_guest');
		$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('res_settings_show_ical_links_to_guest'),
			'type' => 'switch',
			'name' => 'sel_show_ical_links_to_guest',
			'value' => $value,
		);

		$value = ko_get_setting('res_allow_exports_for_guest');
		$avalues = array_filter(array_map(function($el) {return trim($el);}, explode(',', $value)), function($el) {return $el != '';});
		$adescs = array();
		foreach($avalues as $k => $av) {
			$adescs[] = getLL('res_settings_allow_exports_for_guest_'.$av);
		}
		$avalue = implode(',', $avalues);
		$frmgroup[$gc]['row'][$rowcounter++]['inputs'][0] = array('desc' => getLL('res_settings_allow_exports_for_guest'),
			'type' => 'checkboxes',
			'size' => 2,
			'name' => 'sel_allow_exports_for_guest',
			'avalues' => $avalues,
			'avalue' => $avalue,
			'adescs' => $adescs,
			'values' => array('xls', 'pdf'),
			'descs' => array(getLL('res_settings_allow_exports_for_guest_xls'), getLL('res_settings_allow_exports_for_guest_pdf')),
		);


		$value = ko_get_setting('res_show_mod_to_all');
		$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('res_settings_show_mod_to_all'),
				'type' => 'switch',
				'name' => 'sel_show_mod_to_all',
				'label_0' => getLL('no'),
				'label_1' => getLL('yes'),
				'value' => $value == '' ? 0 : $value,
				);
		$value = ko_get_setting('res_attach_ics_for_user');
		$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('res_settings_attach_ics_for_user'),
				'type' => 'switch',
				'name' => 'sel_attach_ics_for_user',
				'label_0' => getLL('no'),
				'label_1' => getLL('yes'),
				'value' => $value == '' ? 0 : $value,
				);

		$value = ko_html(ko_get_setting('res_access_mode'));
		$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('res_settings_access_mode'),
				'type' => 'select',
				'name' => 'sel_access_mode',
				'values' => array(0, 1),
				'descs' => array(getLL('res_settings_access_mode_0'), getLL('res_settings_access_mode_1')),
				'value' => $value,
				);

		$value = ko_get_setting('res_access_prevent_lvl2_del');
		$frmgroup[$gc]['row'][$rowcounter]['inputs'][1] = array('desc' => getLL('res_settings_prevent_lvl2_del'),
			'type' => 'switch',
			'name' => 'chk_prevent_lvl2_del',
			'value' => $value,
		);
	}



	if(!isset($access['admin'])) ko_get_access('admin');
	if($access['admin']['ALL'] > 2) {
		$gc++;
		$frmgroup[$gc]['titel'] = getLL('settings_title_guest');
		$frmgroup[$gc]['tab'] = TRUE;

		$uid = ko_get_guest_id();

		$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('res_settings_default_view'),
				'type' => 'select',
				'name' => 'guest_sel_reservation',
				'values' => array('liste', 'show_cal_monat', 'show_cal_woche', 'show_resource_month', 'show_resource_week', 'show_resource_day'),
				'descs' => array(getLL('submenu_reservation_liste'), getLL('submenu_reservation_cal_month'), getLL('submenu_reservation_cal_week'), getLL('daten_resource_month'), getLL('daten_resource_week'), getLL('daten_resource_day')),
				'value' => ko_html(ko_get_userpref($uid, 'default_view_reservation'))
				);
		$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('admin_settings_limits_numberof_reservations'),
				'type' => 'text',
				'params' => 'size="10"',
				'name' => 'guest_txt_limit_reservation',
				'value' => ko_html(ko_get_userpref($uid, 'show_limit_reservation'))
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

		$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('admin_settings_view_weekcal_intermediate_times'),
			'type' => 'text',
			'params' => 'size="30"',
			'name' => 'guest_txt_cal_woche_intermediate_times',
			'value' => ko_html(ko_get_userpref($uid, 'cal_woche_intermediate_times'))
		);

		$frmgroup[$gc]['row'][$rowcounter++]['inputs'][0] = array('type' => '   ');

		$value = ko_get_userpref($uid, 'res_monthly_title');
		$frmgroup[$gc]['row'][$rowcounter++]['inputs'][0] = array('desc' => getLL('res_settings_monthly_title'),
				'type' => 'select',
				'name' => 'guest_sel_monthly_title',
				'values' => array('item_id', 'zweck', 'name', 'item_id_zweck'),
				'descs' => array(getLL('kota_ko_reservation_item_id'), getLL('kota_ko_reservation_zweck'), getLL('kota_ko_reservation_name'), getLL('kota_ko_reservation_item_id_zweck')),
				'value' => $value,
				);
		$value = ko_get_userpref($uid, 'res_mark_sunday');
		$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('res_settings_mark_sunday'),
				'type' => 'switch',
				'name' => 'guest_sel_mark_sunday',
				'label_0' => getLL('no'),
				'label_1' => getLL('yes'),
				'value' => $value == '' ? 0 : $value,
				);
		$value = ko_html(ko_get_userpref($uid, 'show_dateres_combined'));
		$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('admin_settings_options_dateres_combined'),
				'type' => 'switch',
				'name' => 'guest_sel_show_dateres_combined',
				'label_0' => getLL('no'),
				'label_1' => getLL('yes'),
				'value' => $value == '' ? 0 : $value,
				);

		$frmgroup[$gc]['row'][$rowcounter++]['inputs'][0] = array('type' => '   ');

		$value = ko_get_userpref($uid, 'res_pdf_show_time');
		$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('res_settings_pdf_show_time'),
				'type' => 'select',
				'name' => 'guest_sel_pdf_show_time',
				'values' => array('2', '1', '0'),
				'descs' => array(getLL('res_settings_pdf_show_time_2'), getLL('res_settings_pdf_show_time_1'), getLL('no')),
				'value' => $value
				);
		$value = ko_get_userpref($uid, 'res_pdf_show_comment');
		$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('res_settings_pdf_show_comment'),
				'type' => 'switch',
				'name' => 'guest_sel_pdf_show_comment',
				'label_0' => getLL('no'),
				'label_1' => getLL('yes'),
				'value' => $value == '' ? 0 : $value,
				);
		$value = ko_get_userpref($uid, 'res_pdf_week_start');
		$monday = date_find_last_monday(date('Y-m-d'));
		$daynames[] = strftime('%A', strtotime($monday));
		for($i=1; $i<7; $i++) {
			$daynames[] = strftime('%A', strtotime(add2date($monday, 'tag', $i, TRUE)));
		}
		$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('res_settings_pdf_week_start'),
				'type' => 'select',
				'name' => 'guest_sel_pdf_week_start',
				'values' => array(1,2,3,4,5,6,0),
				'descs' => $daynames,
				'value' => $value,
				);
		$value = ko_get_userpref($uid, 'res_pdf_week_length');
		$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('res_settings_pdf_week_length'),
				'type' => 'select',
				'name' => 'guest_sel_pdf_week_length',
				'values' => array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21),
				'descs' => array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21),
				'value' => $value,
				);
		$value = ko_get_userpref($uid, 'res_export_show_legend');
		$frmgroup[$gc]['row'][$rowcounter++]['inputs'][0] = array('desc' => getLL('res_settings_export_show_legend'),
				'type' => 'switch',
				'name' => 'guest_sel_export_show_legend',
				'label_0' => getLL('no'),
				'label_1' => getLL('yes'),
				'value' => $value == '' ? 0 : $value,
				);
	}


	//Allow plugins to add further settings
	hook_form('reservation_settings', $frmgroup, '', '');

	//display the form
	$smarty->assign("tpl_titel", getLL("res_settings_form_title"));
	$smarty->assign("tpl_submit_value", getLL("save"));
	$smarty->assign("tpl_action", "submit_res_settings");
	$cancel = ko_get_userpref($_SESSION["ses_userid"], "default_view_reservation");
	if(!$cancel) $cancel = "show_cal_monat";
  $smarty->assign("tpl_cancel", $cancel);
	$smarty->assign("tpl_groups", $frmgroup);

	$smarty->assign("help", ko_get_help("reservation", "res_settings"));

	$smarty->display('ko_formular.tpl');
}//ko_res_settings()




/**
  * Überprüft auf korrekte Angaben bei der Res-Erfassung
	*/
function check_entries(&$koi) {
	if(!$koi["item_id"]) return 9;

  if(!check_datum($koi["startdatum"])) return 1;
  if($koi["enddatum"] != "" && !check_datum($koi["enddatum"])) return 1;
  if($koi['enddatum'] != '' && str_replace('-', '', sql_datum($koi['startdatum'])) > str_replace('-', '', sql_datum($koi['enddatum']))) return 14;

	//Zeitangaben testen
  if($koi["startzeit"] != "" && !check_zeit($koi["startzeit"])) return 2;  //Eine Zeit wird verlangt
	if($koi["enddatum"] != "" && $koi["enddatum"] != $koi["startdatum"]) {
	} else {
	  if($koi["endzeit"] != "" && !check_zeit($koi["endzeit"])) return 2;
		//Bei eintägigem Anlass muss die Endzeit grösser sein als die Startzeit
    $time_s1 = str_replace(":", "", $koi["startzeit"]);
    $time_s2 = str_replace(":", "", $koi["endzeit"]);
    if((trim($koi["enddatum"]) == "" || $koi["startdatum"] == $koi["enddatum"]) && (int)$time_s1 > (int)$time_s2) return 2;
	}

	//check for all the mandatory fields
	if (!kota_check_mandatory_fields('ko_reservation', $koi['id'], $koi)) {
		return 60;
	}

	return 0;
}//check_entries()


/**
 * Create a text with all informations about given reservation.
 * Excluding fields which are set through globa var $RES_EXCLUDED_FIELDS_IN_INFOTEXT
 * @param $data array information about the rservation
 *
 * @return string containing key:value with translations
 */
function ko_get_res_infotext($data) {
	global $RES_EXCLUDED_FIELDS_IN_INFOTEXT;

	kota_process_data("ko_reservation", $data, "pdf,xls,list,pre");

	$txt = "";
	foreach($data as $key => $value) {
		if(in_array($key, $RES_EXCLUDED_FIELDS_IN_INFOTEXT)) continue;

		$ll_key = getLL("kota_ko_reservation_".$key);
		if(!$ll_key) continue;
		if(!$value) continue;

		$txt .= "$ll_key: ".strip_tags(ko_unhtml($value))."\n";
	}

	return $txt;
}



/**
  * Löscht allfällig leere Reservations-Gruppen.
	* Wird nach Löschen und Bearbeiten von Res-Items aufgerufen.
	*/
function ko_delete_empty_resgroups() {
	ko_get_resgroups($res_groups);
	foreach($res_groups as $r_i => $r) {
		ko_get_resitems_by_group($r_i, $resitems);
		if(sizeof($resitems) == 0) {
			db_delete_data("ko_resgruppen", "WHERE `id` = '$r_i'");
		}
	}
}//ko_delete_empty_resgroups()




/**
  * Wendet Res-Filter an und gibt WHERE und LIMIT-String für SQL zurück
	* Wendet permanente Zeitfilter und Filter für die darzustellenden Res-Items an.
	*/
function apply_res_filter(&$z_where, &$z_limit, $_start=NULL, $_ende=NULL, $user_id="") {
	global $access, $KOTA;

	$use_start = ($_start !== NULL) ? $_start : $_SESSION["filter_start"];
	$use_ende = ($_ende !== NULL) ? $_ende : $_SESSION["filter_ende"];
	$use_items = $_SESSION['show_items'];

	//Permanenten Filter einfügen, falls vorhanden und nur view-Rechte
	$z_where = "";
	$perm_filter_start = ko_get_setting("res_perm_filter_start");
	$perm_filter_ende  = ko_get_setting("res_perm_filter_ende");

	// check, if the login has the 'force_global_filter' flag set to 1
	$forceGlobalTimeFilter = ko_get_force_global_time_filter("reservation", $_SESSION['ses_userid']);

	if($forceGlobalTimeFilter != 2 && ($forceGlobalTimeFilter == 1 || $access['reservation']['MAX'] < 4) && ($perm_filter_start || $perm_filter_ende)) {
		if($perm_filter_start != "") {
			$z_where .= " AND enddatum >= '".$perm_filter_start."' ";
		}
		if($perm_filter_ende != "") {
			$z_where .= " AND startdatum <= '".$perm_filter_ende."' ";
		}
	}

	$start = ko_daten_parse_time_filter($use_start);
	if ($start != '') $z_where .= "AND enddatum >= '$start'";
	$ende = ko_daten_parse_time_filter($use_ende, '');
	if ($ende != '') $z_where .= "AND startdatum <= '$ende'";

	$show_items = ko_get_resitems_with_linked_items($use_items);

	if(sizeof($show_items) == 0) {
		if($user_id > 0 && $user_id != ko_get_guest_id()) $z_where .= " AND `user_id` = '$user_id' ";
		else $z_where .= " AND 1=2 ";
	}
	else {
		$item_where = " AND (`item_id` IN ('".implode("','", $show_items)."') ";
		if($user_id > 0 && $user_id != ko_get_guest_id()) $item_where .= " OR `user_id` = '$user_id' ";
		$item_where .= ") ";
	}
	$z_where .= $item_where;

	//Set filters from KOTA
	$kota_where = kota_apply_filter('ko_reservation');
	if($kota_where != '') $z_where .= " AND ($kota_where) ";


	//Set limit
	if($_SESSION["show_start"] > 0 && $_SESSION["show_limit"]) {
		$z_limit = "LIMIT " . ($_SESSION["show_start"]-1) . ", " . $_SESSION["show_limit"];
	}
}//apply_res_filter()




/**
  * Löscht eine Reservation gemäss der übergebenen ID
	* Führt Rechte-Check durch
	*/
function do_del_res($id, $del_serie=FALSE, $delEvent=FALSE) {
	global $access, $BASE_PATH;

	//Reservation auslesen für Rechte-Check und Log-Meldung
	ko_get_res_by_id($id, $r_); $r = $r_[$id];

	//Check access rights
	if($access['reservation'][$r['item_id']] > 3 || ($_SESSION["ses_userid"] == $r["user_id"] && $_SESSION["ses_userid"] != ko_get_guest_id() && $access['reservation'][$r['item_id']] > 2) ) {
		//Delete reservation
		db_delete_data("ko_reservation", "WHERE `id` = '$id'");
		ko_log_diff("delete_res", $r);

		if (trim($id) && $delEvent && ko_module_installed('daten')) {
			require_once("{$BASE_PATH}daten/inc/daten.inc");
			ko_get_access('daten');

			$events = db_select_data('ko_event', "WHERE `reservationen` REGEXP '(^|,){$id}(,|$)'");
			foreach ($events as $event) {
				$resIds = ko_array_filter_empty(explode(',', $event['reservationen']));
				foreach ($resIds as $resId) {
					do_del_res($resId);
				}

				do_del_termin($event['id']);
			}
		}

		//Delete whole serie if set
		if(intval($r["serie_id"]) > 0 && $del_serie) {
			//Get all reservations for logging
			$all_res = db_select_data('ko_reservation', "WHERE `serie_id` = '".intval($r['serie_id'])."'", '*', 'ORDER BY `startdatum` ASC');

			//Delete whole serie
			db_delete_data("ko_reservation", "WHERE `serie_id` = '".intval($r["serie_id"])."'");

			foreach($all_res as $r) {
				$id = $r['id'];
				if (trim($id) && $delEvent && ko_module_installed('daten')) {
					require_once("{$BASE_PATH}daten/inc/daten.inc");
					ko_get_access('daten');

					$events = db_select_data('ko_event', "WHERE `reservationen` REGEXP '(^|,){$id}(,|$)'");
					foreach ($events as $event) {
						$resIds = ko_array_filter_empty(explode(',', $event['reservationen']));
						foreach ($resIds as $resId) {
							do_del_res($resId);
						}

						do_del_termin($event['id']);
					}
				}
				ko_log_diff('delete_res', $r);
			}
		}
	} else if($access['reservation'][$r['item_id']] > 1) {
		//TODO: Store moderation for deletion
	}
}//do_del_res()





/**
  * Display the calendar div which will be filled by fullCalendar JS
	*/
function ko_res_calendar() {
	global $ko_path;

	//Add Select for months / weeks / days
	$code  = '';
	$code .= '<div id="ko_calendar">';
	$code .= '<div class="fc-mwselect fc-mwselect-res">';
	$code .= '<div name="mwselect" id="mwselect">';
	$code .= ko_calendar_mwselect();
	$code .= '</div>';
	$code .= '</div>';
	$code .= '</div>';

	//Add PDF link
	if($_SESSION['ses_userid'] != ko_get_guest_id() || FALSE !== strpos(ko_get_setting('res_allow_exports_for_guest'), 'pdf')) {
		$code .= '<table style="margin-left:12px" cellspacing="0" cellpadding="3" id="pdf_link_footer">';
		$code .= '<tr><td style="border-left-style:solid;border-left-width:1px">&nbsp;</td></tr>';
		$code .= '<tr><td style="border-left-style:solid;border-left-width:1px;border-bottom-width:1px;border-bottom-style:solid;">';
		$code .= '<a href="" onclick="sendReq(\'inc/ajax.php\', \'action\', \'pdfcalendar\', show_box); return false;">';
		$code .= '&nbsp;<i class="fa fa-file-pdf-o"></i>&nbsp;'.getLL("res_list_footer_pdf_label").'</a>';
		$code .= '<span name="res_pdf_link" id="res_pdf_link">&nbsp;</span>';
		$code .= '</td></tr>';
		$code .= '</table>';
	}

	print $code;
}//ko_res_calendar()





function ko_get_resitems_css() {
	$r = '';

	$items = db_select_data("ko_resitem", "WHERE 1=1", "*");
	foreach($items as $item) {
		$color = $item['farbe'];
		if(!$color) $color = 'aaaaaa';

		$cr = hexdec(substr($color, 0, 2));
		$cg = hexdec(substr($color, 2, 2));
		$cb = hexdec(substr($color, 4, 2));

		foreach(array('Day', 'Week', 'Month') as $mode) {
			$r .= 'div.fc-view-resource'.$mode.' tr.fc-week'.$item['id'].' td.fc-resourceName { background-color: #'.$color.'; color: '.ko_get_contrast_color($color).'; }'."\n";
			$r .= 'div.fc-view-resource'.$mode.' tr.fc-week'.$item['id'].' td { border-bottom: 1px solid #'.$color.'; background-color: rgba('.$cr.', '.$cg.', '.$cb.', 0.1);  }'."\n";
		}
	}

	$r = '<style type="text/css">
<!--
'.$r.'
-->
</style>';

	return $r;
}//ko_get_resitems_css()




function ko_res_ical_links() {
	global $RESICAL_URL, $BASE_URL, $access, $BASE_PATH;

	if($_SESSION['ses_userid'] == ko_get_guest_id() && !ko_get_setting('res_show_ical_links_to_guest')) return FALSE;
	if(!defined('KOOL_ENCRYPTION_KEY') || trim(KOOL_ENCRYPTION_KEY) == '') {
		print 'ERROR: '.getLL('error_res_13');
	}

	ko_get_login($_SESSION['ses_userid'], $login);

	if(empty($login['ical_hash'])) {
		require_once($BASE_PATH.'admin/inc/admin.inc');
		$login['ical_hash'] = ko_admin_revoke_ical_hash($_SESSION['ses_userid']);
	}

	$base_link = ($RESICAL_URL ? $RESICAL_URL : $BASE_URL . 'resical/') . '?' . ($_SESSION['ses_userid'] == ko_get_guest_id() ? 'guest=1' : 'user=' . $login['ical_hash']);

	$help  = ko_get_help('reservation', 'ical_links');
	$help2 = ko_get_help('reservation', 'ical_links2');

	$content = '<div class="container-fluid"><div class="row">';
	$content .= '<div class="ical-links col-md-8">';
	$content .= '<h3 class="ko_list_title">'.($help['show'] ? '&nbsp;<span class="pull-left help-icon">'.$help['link'].'</span>' : '').'<span class="pull-left">'.getLL('res_ical_links_title').'</span><br clear="all" /></h3>';
	$content .= '<p>'.getLL('res_ical_links_description').'</p>';

	$content .= '<h4>'.($help2['show'] == 1 ? $help2['link'].'&nbsp;' : '').getLL('res_ical_links_title_all').'</h4>';
	$content .= ko_get_ical_link($base_link, getLL('all')).'<br />';

	$link = $base_link.'&items='.implode(',', $_SESSION['show_items']);
	//Add current KOTA filter if set
	if(sizeof($_SESSION['kota_filter']['ko_reservation']) > 0) {
		foreach($_SESSION['kota_filter']['ko_reservation'] as $k => $v) {
			if(!$v) continue;
			$link .= '&kota_'.$k.'='.urlencode(implode("||",$v));
		}
	}
	$content .= ko_get_ical_link($link, getLL('res_ical_links_current')).'<br />';

	//The user's own reservations
	$link = $base_link.'&own=1';
	$content .= ko_get_ical_link($link, getLL('res_ical_links_own')).'<br />';

	$content .= '<h4>'.($help2['show'] == 1 ? $help2['link'].'&nbsp;' : '').getLL('res_ical_links_title_presets').'</h4>';
	$itemset = array_merge((array)ko_get_userpref('-1', '', 'res_itemset', 'ORDER BY `key` ASC'), (array)ko_get_userpref($_SESSION['ses_userid'], '', 'res_itemset', 'ORDER BY `key` ASC'));
	foreach($itemset as $i) {
		if(!$i['key'] || !$i['value']) continue;
		$label = ($i['user_id'] == '-1' ? getLL('itemlist_global_short').$i['key'] : $i['key']);
		$content .= ko_get_ical_link($base_link.'&items=p'.$i['id'], $label).'<br />';
	}

	$content .= '<h4>'.($help2['show'] == 1 ? $help2['link'].'&nbsp;' : '').getLL('res_ical_links_title_single').'</h4>';
	ko_get_resgroups($resgroups);
	foreach($resgroups as $gid => $group) {
		if($access['reservation']['ALL'] < 1 && $access['reservation']['grp'.$gid] < 1) continue;
		$content .= '<div style="font-weight: bold; margin-top: 8px;">';
		$content .= ko_get_ical_link($base_link.'&items=g'.$gid, $group['name']).'</div>';
		ko_get_resitems_by_group($gid, $all_items);
		foreach($all_items as $id => $item) {
			if($access['reservation']['ALL'] < 1 && $access['reservation'][$id] < 1) continue;
			if(!$item['id']) continue;
			$content .= ko_get_ical_link($base_link.'&items='.$item['id'], $item['name']).'<br />';
		}
	}

	$content .= '</div>';

	if($_SESSION['ses_userid'] != ko_get_guest_id()) {
		$content .= '<div class="col-md-4">';
		$content .= '
		<h3>' . getLL("ical_links_revoke_title") . '</h3>
		<p>' . getLL("ical_links_revoke_text") . '</p>
		<button type="submit" class="btn btn-sm btn-danger" onclick="c=confirm(\''.getLL("ical_links_revoke_jsquestion").'\');if(!c) return false;set_action(\'ical_links_revoke\');" value="revoke">'.getLL("ical_links_revoke_button").'</button>';
		$content .= '</div>';
	}

	$content .= '</div></div>';

	print $content;
}//ko_res_ical_links()




function ko_reservation_export_months($num, $month, $year) {
	global $BASE_PATH;
	$absence_color = substr(ko_get_setting('absence_color'), 1);

	//Start pdf
	define('FPDF_FONTPATH',$BASE_PATH.'fpdf/schriften/');
	include_once($BASE_PATH.'fpdf/fpdf.php');
	$pdf = new FPDF('L', 'mm', 'A4');
	$pdf->Open();
	$pdf->SetAutoPageBreak(true, 10);
	$pdf->SetMargins(5, 5, 5);  //left, top, right
	$pdf->AddFont('fontn','','arial.php');
	$pdf->AddFont('fontb','','arialb.php');

	//Set months and filename for year and month view
	if($num == 12) {
		for($i=1; $i<=12; $i++) {
			$months[] = $i;
		}
		$filename = getLL('res_filename_pdf').$year.'_'.strftime('%d%m%Y_%H%M%S', time()).'.pdf';
	} else if($num == 1) {
		$months[] = $month;
		$filename = getLL('res_filename_pdf').str_to_2($month).'_'.$year.'_'.strftime('%d%m%Y_%H%M%S', time()).'.pdf';
	} else return FALSE;

	ko_get_resitems($resitems);

	$monthly_title = ko_get_userpref($_SESSION['ses_userid'], 'res_monthly_title');
	$show_comment = ko_get_userpref($_SESSION['ses_userid'], "res_pdf_show_comment");
	$show_time = ko_get_userpref($_SESSION['ses_userid'], "res_pdf_show_time");
	foreach($months as $month) {
		$month = str_to_2($month);
		$data = array();
		$legend = array();

		apply_res_filter($z_where, $z_limit, 'immer', 'immer');
		$startstamp = mktime(1,1,1, (int)$month, 1, $year);
		$endstamp = mktime(1,1,1, ($month == 12 ? 1 : $month+1), 0, ($month == 12 ? $year+1 : $year));
		$z_where .= ' AND `enddatum` >= \''.strftime('%Y-%m-%d', $startstamp).'\' AND `startdatum` <= \''.strftime('%Y-%m-%d', $endstamp).'\'';
		ko_get_reservationen($reservations, $z_where, '', 'res', 'ORDER BY startzeit,item_name ASC');

		//Absence
		if(ko_module_installed('daten') && $_SESSION['show_absences_res']) {
			require_once($BASE_PATH . 'daten/inc/daten.inc');
			$absenceEvents = ko_get_absences_for_calendar(date("Y-m-d", $startstamp), date("Y-m-d", $endstamp), "ko_reservation");
			if(sizeof($absenceEvents) > 0) {
				$items['absence'] = [
					'id' => 'absence',
					'farbe' => $absence_color,
					'name' => getLL('absence_eventgroup'),
					'shortname' => getLL('absence_eventgroup_short')
				];
				$reservations = $reservations + $absenceEvents;
			}
		}

		//Calendar weeks
		$kw = array();
		$week_inc = 7*24*60*60;
		$stamp = $startstamp;
		while($stamp < $endstamp+$week_inc) {
			$kw[] = str_to_2(strftime('%V', $stamp));
			$stamp += $week_inc;
		}

		$done_res = array();
		foreach($reservations as $res) {
			$content = array();
			//Create combined reservations for events
			if(ko_get_userpref($_SESSION['ses_userid'], 'show_dateres_combined') == 1) {
				//Skip already processed reservations (linked to an event already processed)
				if(in_array($res['id'], $done_res)) continue;
				//Find an event with the current reservation
				ko_get_events($event, 'AND `reservationen` REGEXP \'(^|,)'.$res['id'].'(,|$)\'');
				$event = array_shift($event);
				if($event['id'] && $event['res_combined']) {
					//Mark all linked reservations as done and build purpose text as sum of all items
					$res['_orig_zweck'] = $res['zweck'];
					$res['zweck'] = '';
					foreach(explode(',', $event['reservationen']) as $resid) {
						if($resid) $done_res[] = $resid;
						ko_get_res_by_id($resid, $thisres_); $thisres = $thisres_[$resid];
						$res['zweck'] .= $resitems[$thisres['item_id']]['name'].', ';
					}
					$res['zweck'] = substr($res['zweck'], 0, -2);
					//Reset color and name according to event group
					$res['item_farbe'] = $event['eventgruppen_farbe'];
					$res['item_name'] = getLL('res_cal_combined').' '.$event['eventgruppen_name'];
					$res['_combined'] = $event['eventgruppen_id'];
				}
			}//if(res_combined)

			//Set title according to setting
			$content = ko_reservation_get_title($res, $resitems[$res['item_id']], $monthly_title);

			//color
			$content['farbe'] = $res['item_farbe'];

			//Legend
			ko_add_color_legend_entry($legend, $res, $resitems[$res['item_id']]);

			//Multiday reservations
			if($res['startdatum'] != $res['enddatum']) {
				$date = $res['startdatum'];
				$mode = 'first';
				while((int)str_replace('-', '', $date) <= (int)str_replace('-', '', $res['enddatum'])) {
					if($date != $res['startdatum'] && $date != $res['enddatum']) {
						$mode = 'middle';
					} else if($date == $res['enddatum']) {
						$mode = 'last';
					}
					if(substr($date, 5, 2) == $month) {
						$content['zeit'] = ko_get_time_as_string($res, $show_time, $mode);
						$data[(int)substr($date, -2)]['inhalt'][] = $content;
					}
					$date = add2date($date, 'tag', 1, TRUE);
				}
			} else {
				$content['zeit'] = ko_get_time_as_string($res, $show_time, 'default');
				$data[(int)substr($res['startdatum'], -2)]['inhalt'][] = $content;
			}
		}//foreach(res)

		$show_legend = ko_get_userpref($_SESSION['ses_userid'], 'res_export_show_legend') == 1;
		ko_export_cal_one_month($pdf, $month, $year, $kw, $data, getLL('res_reservations'), $show_comment, $show_legend, $legend);
	}//foreach(months)

	$file = $BASE_PATH.'download/pdf/'.$filename;
	$ret = $pdf->Output($file);

	return 'download/pdf/'.$filename;
}//ko_reservation_export_months()




function ko_reservation_get_title($res, $item, $mode) {
	$title = array();

	if(!isset($item['name'])) $item = $item[$res['item_id']];
	$item_name = $item['name'];

	//Combined reservations: Use original purpose, as zweck holds a list of items and use purpose as item name
	if($res['_combined'] > 0) {
		$item_name = $res['zweck'];
		$res['zweck'] = $res['_orig_zweck'];
	}

	$show_comment = ko_get_userpref($_SESSION['ses_userid'], 'res_pdf_show_comment');

	//Set default value if userpref is still empty
	if($mode == '') $mode = 'item_id';

	if($mode == 'zweck') {
		//Don't allow purpose as main title if show comments is set (this would result in showing the purpose twice)
		if($show_comment) $title['text'] = $item_name;
		else $title['text'] = $res['zweck'] ? $res['zweck'] : $item_name;
	}
	else if($mode == 'item_id') {
		$title['text'] = $item_name;
	}
	else if($mode == 'name') {
		$title['text'] = $res['name'] ? $res['name'] : $item_name;
	}
	else if($mode == 'item_id_zweck') {
		$title['text'] = $item_name.($res['zweck'] ? ': '.$res['zweck'] : '');
	}
	else {
		if($res[$mode]) $title['text'] = $res[$mode];
		else $title['text'] = '';
	}

	$title['short'] = $title['text'];

	if($show_comment) $title['kommentar'] = $res['zweck'];
	else $title['kommentar'] = '';

	// Strip HTML Tags
	foreach ($title as $k => $v) {
		$title[$k] = strip_tags($v);
	}

	return $title;
}//ko_reservation_get_title()


/**
 * Set uploaded files of a new/modified reservation series to all reservations.
 * Update Database field and copy file.
 *
 * @param Integer $serie_id Id of serie
 * @param Integer|null $origin Reservation Id to use as parent
 * @return bool
 */
function ko_res_update_serie_files($serie_id, $origin = NULL) {
	global $KOTA, $BASE_PATH;

	$table = "ko_reservation";
	$series = db_select_data($table, "WHERE serie_id = ". $serie_id, "*", "ORDER BY item_id ASC");
	if(count($series) == 0) {
		$table = "ko_reservation_mod";
		$series = db_select_data($table, "WHERE serie_id = ". $serie_id, "*", "ORDER BY item_id ASC");
	}

	if(count($series) == 0) {
		return FALSE;
	}

	$columns = db_get_columns("ko_reservation");
	$file_origin = $file_origin_id = NULL;

	// copy files for every res in series
	foreach ($columns AS $kota_field) {
		if ($KOTA['ko_reservation'][$kota_field['Field']]['form']['type'] == 'file') {

			if ($origin != NULL) {
				$file_origin = $series[$origin][$kota_field['Field']];
				$file_origin_id = $origin;
			}

			foreach($series AS $serie) {
				if ($file_origin === NULL) {
					$file_origin = $serie[$kota_field['Field']];
					$file_origin_id = $serie['id'];
					continue;
				}

				$file_destination = str_replace($file_origin_id, $serie['id'], $file_origin);
				copy($BASE_PATH . "/" . $file_origin, $BASE_PATH . "/" . $file_destination);
				db_update_data($table, "WHERE `id` = ".$serie['id'], array($kota_field['Field'] => $file_destination));
			}
		}
	}

	return TRUE;
}

?>
