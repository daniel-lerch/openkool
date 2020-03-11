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

/**
  * Include frontmodule plugins
  */
$hooks = hook_get_by_type('fm');
if(sizeof($hooks) > 0) {
  foreach($hooks as $hook) include_once($ko_path.'plugins/'.$hook.'/'.$hook.'.php');
}



/*
 * Haupt-Funktion, die von extern aufgerufen wird.
 * Erwartet zwei Argumente: $_SESSION["ses_userid"] und die Front-Modul-Bezeichnung
 */
function ko_front_module($uid, $module, $pos = 0, $news_id = 0, $state='open') {
	global $smarty;

	if($uid == ko_get_checkin_user_id()) return FALSE;

	// Auf genügend Rechte überprüfen, ob das gewünschte Modul angezeigt werden darf
	// TODO...

	$smarty->assign('tpl_state', $state);
	$smarty->assign('tpl_module', $module);
	$smarty->assign('tpl_guest', $_SESSION['ses_userid'] == ko_get_guest_id());

	// Richtige Funktion aufrufen, die das gewünschte Front-Modul ausgibt
	switch($module) {
		case "daten_cal":
			$result = ko_fm_daten_cal($uid, $pos);
		break;
		case "geburtstage":
			$result = ko_fm_geburtstage($uid, $pos);
		break;
		case "mod":
			$result = ko_fm_mod($uid);
		break;
		case "news":
			$result = ko_fm_news($uid, $pos, $news_id);
		break;
		case "adressaenderung":
			$result = ko_fm_adresse($uid);
		break;
		case "rota":
			$result = ko_fm_rota($uid);
		break;
		case "today":
			$result = ko_fm_today($uid, $pos);
		break;
		case 'fastfilter':
			$result = ko_fm_fastfilter($uid);
		break;
		case 'absence':
			try {
				$result = ko_fm_absence($uid, $pos);
			} catch(exception $e) {
				$smarty->assign('tpl_fm_title', getLL('fm_absence_title'));
				$result = $smarty->fetch('ko_fm_header.tpl');
				$result.= $e->getMessage();
				$result.= $smarty->fetch('ko_fm_footer.tpl');
			}
		break;


		//Frontmodule from a plugin
		default:
			if(function_exists('my_frontmodule_'.$module)) {
				$result = call_user_func('my_frontmodule_'.$module, $uid, $pos);
			}
			else {
				$result = False;
			}
		break;
	}

	return $result;
}//ko_front_module()




function ko_fm_fastfilter($uid) {
	global $smarty;

	if(!ko_module_installed('leute')) return FALSE;

	$result = '';

	$content = '<div class="input-group">';
	$content .= '<input type="text" class="input-sm form-control" name="general_filter_value" placeholder="'.getLL('general_search_placeholder').'">';
	$content .= '<div class="input-group-btn"><button class="btn btn-sm btn-primary" type="submit" name="submit_fm_fastfilter" value="'.getLL('OK').'">' . getLL('OK') . '</button></div>';
	$content .= '</div>';

	$smarty->assign('tpl_fm_title', getLL('fm_fastfilter_title'));
	$result .= '<form action="leute/index.php?action=set_general_filter" method="POST">';
	$result .= $smarty->fetch('ko_fm_header.tpl');
	$result .= $content;
	$result .= $smarty->fetch('ko_fm_footer.tpl');
	$result .= '</form>';

	return $result;
}//ko_fm_fastfilter()






/**
 * Display form for absence moderation on homepage
 *
 * @param $uid
 * @param $pos
 * @return string
 * @throws Exception
 */
function ko_fm_absence($uid, $pos) {
	global $smarty, $mysql_pass, $BASE_PATH, $BASE_URL, $access;

	require_once($BASE_PATH.'daten/inc/daten.inc');
	ko_include_kota(["ko_event_absence"]);

	ko_get_access('daten');
	$absence_rights = $access['daten']['ABSENCE'];

	if ($_SESSION['ses_userid'] != ko_get_guest_id()) {
		if($absence_rights < 1 || $absence_rights == 1 && !ko_get_logged_in_id()) return '';

		if ($_POST['action'] == "edit_absence") {
			$id = format_userinput($_POST['id'], "uint");
			echo "<script type='text/javascript'>
				window.location = '" . $BASE_URL . "daten/index.php?action=edit_absence&id=". $id ."';
			</script>";
		}

		$absences = ko_daten_get_absence_by_leute_id(ko_get_logged_in_id(), NULL, TRUE);
		if(count($absences) > 0) {
			$_SESSION['kota_show_cols_ko_event_absence'] = ['type', 'from_date', 'to_date',];

			$list = new kOOL_listview();
			$list->init('daten', 'ko_event_absence', ['edit'], 1, 5);
			$list->setAccessRights(FALSE);
			$list->setActions(['edit' => ['action' => 'edit_absence']]);
			$list->setTitle('');
			$list->disableMultiedit();
			$list->disableHeader();

			$content_absence_list = '<form action="index.php" method="post" name="formular" enctype="multipart/form-data">
					<input type="hidden" name="action" id="action" value="">
					<input type="hidden" name="id" id="id" value="">';

			$content_absence_list .= $list->render($absences, 'html_fetch');
			$content_absence_list .= "</form>";
		} else {
			$content_absence_list = "<div class=\"alert alert-warning\">" . getLL('ko_event_absence_no_entries') . "</div>";
		}

		$smarty->assign('tpl_fm_title', getLL('fm_absence_title'));
		$result = $smarty->fetch('ko_fm_header.tpl');
		$result .= $content_absence_list;
		$result .= '<a href="' . $BASE_URL . 'daten/?action=list_absence">' . getLL('placeholder_absence_link') . '</a>';
		$result .= $smarty->fetch('ko_fm_footer.tpl');
		return $result;
	}

	$absence_cookie = json_decode($_COOKIE['fm_absence_persons'], TRUE);
	if(empty($absence_cookie)) $absence_cookie = [];

	// update cookie, so user can stay logged in longer
	setcookie("fm_absence_persons", json_encode($absence_cookie), time()+(86400*365), "/", $_SERVER['HTTP_HOST']);

	foreach ($absence_cookie AS $token => $person_id) {
		if (ko_fm_absence_check_hash($token)) {
			ko_get_person_by_id($person_id, $person);
			$absence_persons[$person_id] = $person;
		}
	}

	if($_GET['action'] == "select_absence_person" && $_GET['id'] > 0) {
		if(!$absence_persons[format_userinput($_GET['id'], 'uint')]) {
			throw new Exception(getLL("ko_event_absence_error_wrong_person"));
		}

		$_SESSION['fm_absence_selected_person'] = format_userinput($_GET['id'], 'uint');
	}

	if($_SESSION['fm_absence_selected_person'] > 0) {
		$leute_id = $_SESSION['fm_absence_selected_person'];
		$hash = array_search($leute_id, json_decode($_COOKIE['fm_absence_persons'], TRUE));
		if (ko_fm_absence_check_hash($hash)) {
			if ($_POST['action'] == "edit_absence") {
				$id = format_userinput($_POST['id'], "uint");
				$absence = ko_daten_get_absence_by_id($id);
				if($absence['leute_id'] != $leute_id) {
					throw new Exception(getLL("ko_event_absence_error_wrong_id"));
				}
			} else if ($_POST['action'] == "submit_absence") {
				[$_table, $_columns, $id] = explode("@", $_POST["id"]);

				if ($id > 0) {
					$absence = ko_daten_get_absence_by_id($id);
					if($absence['leute_id'] != $leute_id) {
						throw new Exception(getLL("ko_event_absence_error_wrong_id"));
					}

					$_POST['koi']['ko_event_absence']['leute_id'][$id] = $leute_id;
					kota_submit_multiedit(0, "edit_absence");
					$id = 0;
				} else {
					if (isset($absence_persons[$leute_id])) {
						$_POST['koi']['ko_event_absence']['leute_id'][0] = $leute_id;
						kota_submit_multiedit('', "new_absence");
					}
				}
			} else {
				$id = 0;
			}

			$formgroup = ko_multiedit_formular("ko_event_absence", "", $id, '', '', TRUE, '', TRUE, 'inline');

			$inputs = FALSE;
			foreach ($formgroup as $i) {
				foreach ($i['row'] as $row) {
					foreach ($row['inputs'] as $in) {
						$inputs[] = $in;
					}
				}
			}

			if (is_array($inputs)) {
				$code = '<br />

				<div class="container-fluid absence_actions">
				  <div class="row">
					<div class="col-md-6 text-left">
						<button type="button" class="btn btn-primary" data-toggle="collapse" data-target="#new_absence_form">' . getLL("ko_event_absence_form_title_new") . '</button>
					</div>
					<div class="col-md-6 text-right absence_actions__delete">
						<a href="/index.php?action=delete_absence_cookie">' . getLL('ko_event_absence_form_delete_cookie') . '</a>&nbsp;<a href="javascript: return false;" '.ko_get_tooltip_code(getLL('ko_event_absence_form_delete_cookie_help')).'><span class="glyphicon glyphicon-info-sign"></span></a>
					</div>
				  </div>
				</div>

				<form name="new_absence_form" id="new_absence_form" action="/index.php" method="POST" class="' . ($id==0 ? 'collapse' : '') . '">
				<input type="hidden" name="action" value="submit_absence" />';

				foreach ($inputs as $input) {
					$columns[] = $input['colname'];
					if ($input['colname'] == "leute_id") continue;

					$input['add_class'] .= " form-element-inline";
					$smarty->assign('input', $input);

					$code .= '<br /><label>' . getLL('kota_ko_event_absence_' . $input['colname']) . '</label>';
					$code .= $smarty->fetch('ko_formular_elements.tmpl');
				}

				sort($columns);
				$formhash = md5(md5($mysql_pass."ko_event_absence".implode(":", $columns).$id));
				$code.= '<input type="hidden" name="id" value="ko_event_absence@'.implode(",",$columns).'@' . $id . '@'.$formhash.'" />';


				$code .= '<br />
						<button type="submit" onclick="disable_onunloadcheck();" class="btn btn-primary">' . getLL("save") . '</button>
					</form>
				';
			}

			$absences = ko_daten_get_absence_by_leute_id($leute_id, NULL, TRUE);
			if(count($absences) > 0) {
				$_SESSION['kota_show_cols_ko_event_absence'] = ['type', 'from_date', 'to_date',];

				$list = new kOOL_listview();
				$list->init('daten', 'ko_event_absence', ['edit', 'delete'], 1, 1000);
				$list->setAccessRights(FALSE);
				$list->setActions(['edit' => ['action' => 'edit_absence'],
						'delete' => ['action' => 'delete_absence', 'confirm' => TRUE]]
				);
				$list->setTitle('');
				$list->disableMultiedit();
				$list->disableHeader();

				$content_absence_list = '<form action="index.php" method="post" name="formular" enctype="multipart/form-data">
				<input type="hidden" name="action" id="action" value="">
				<input type="hidden" name="id" id="id" value="">';

				$content_absence_list .= $list->render($absences, 'html_fetch');
				$content_absence_list .= "</form>";
			} else {
				$content_absence_list = "<div class=\"alert alert-warning\">" . getLL('ko_event_absence_no_entries') . "</div>";
			}
		}
	}

	$result = '';


	$absence_infotext = ko_get_userpref(ko_get_guest_id(),'fm_absence_infotext');
	if(!empty($absence_infotext)) {
		$content = nl2br($absence_infotext) . "<br><br>";
	}

	$content.= '
		<form action="/index.php" method="POST">
		<input type="hidden" name="action" value="send_absence_token" />
		<label for="fm_absence_personadd">'. getLL("fm_absence_add_person") .'</label>
		<div class="input-group input-group-sm">
			<input type="text" class="input-sm form-control" id="fm_absence_personadd" name="fm_absence_personadd" placeholder="E-Mail" aria-label="E-Mail" />
			<div class="input-group-btn">
				<button type="submit" onclick="disable_onunloadcheck();" class="btn btn-primary">'.getLL("fm_absence_add_person_button").'</button>
			</div>
		</div></form><br />';

	if (count($absence_persons) > 0) {
		$content .= '<label for="fm_absence_personselect">' . getLL("fm_absence_select_person") . '</label>';
		$content .= '<select class="form-control" id="fm_absence_personselect" name="fm_absence_personselect">
				<option value="0"></option>';
		foreach ($absence_persons AS $absence_person) {
			$content .= '<option value="' . $absence_person['id'] . '" ' . ($_SESSION['fm_absence_selected_person'] == $absence_person['id'] ? "selected" : "") . '>' . $absence_person['vorname'] . ' ' . $absence_person['nachname'] . '</option>';
		}
		$content .= '</select>';
		$content .= '<div id="fm_absence_list">' . $content_absence_list . '</div>';
	}

	if(!empty($code)) $content.= $code;

	$smarty->assign('tpl_fm_title', getLL('fm_absence_title'));
	$result.= $smarty->fetch('ko_fm_header.tpl');
	$result.= $content;
	$result.= $smarty->fetch('ko_fm_footer.tpl');
	return $result;
}


/**
 * Creates a hash from userdata for authentication in frontend absence
 * @param $person
 * @return string
 */
function ko_fm_absence_create_hash($person) {
	$hash = strrev(md5($person['nachname'].KOOL_ENCRYPTION_KEY.$person['id']));
	$hash = substr($hash,0,10) . $person['id'];
	return $hash;
}

function ko_fm_absence_check_hash($hash) {
	if(empty($hash)) return FALSE;
	$person_id = substr($hash, 10);
	$hash = substr($hash, 0 ,10);

	$laf = unserialize(ko_get_userpref(ko_get_guest_id(), "fm_absence_restriction"));
	if(!isset($laf["filter"])) $filter_where = " AND `deleted` = '0' AND `hidden` = '0' ";
	else apply_leute_filter($laf["filter"], $filter_where, FALSE, '', ko_get_guest_id());

	$filter_where.= " AND id = " . $person_id;
	$person= db_select_data("ko_leute", "WHERE 1=1 ".$filter_where, '*', '', 'LIMIT 1', TRUE);

	if(empty($person)) return FALSE;
	if(substr(strrev(md5($person['nachname'].KOOL_ENCRYPTION_KEY.$person['id'])),0,10) != $hash) return FALSE;

	return TRUE;
}


/*
 * Gibt einen Kalender mit allen sichtbaren Events aus
 */
function ko_fm_daten_cal($uid, $pos) {
	global $ko_path, $smarty, $access;
	
	ko_get_access('daten');
	include_once($ko_path . "daten/inc/daten.inc");

	// apply filter from userpref
	$filterPresetName = ko_get_userpref($_SESSION['ses_userid'], 'daten_fm_filter');
	$zWhere = '';
	if ($filterPresetName) {
		if (substr($filterPresetName, 0, 4) == '[G] ') {
			$n = substr($filterPresetName, 4);
			$user = -1;
		} else {
			$n = $filterPresetName;
			$user = $_SESSION['ses_userid'];
		}
		$filterPreset = ko_get_userpref($user, $n, 'daten_itemset');

		$allowedGroups = ko_array_filter_empty(explode(',', end($filterPreset)['value']));
	}

	apply_daten_filter($z_where, $z_limit, 'immer', 'immer', $allowedGroups);
	
	$startstamp = mktime(1,1,1, date('m'), 1, date('Y'));
	$endstamp = mktime(1,1,1, (date('m') == 12 ? 1 : date('m')+1), 0, (date('m') == 12 ? date('Y')+1 : date('Y')));
	$z_where .= ' AND `enddatum` >= \''.strftime('%Y-%m-%d', $startstamp).'\' AND `startdatum` <= \''.strftime('%Y-%m-%d', $endstamp).'\'';
	ko_get_events($events, $z_where);

	$data = array();
	foreach($events as $event) {
		$content = array();
		$content['text'] = $event['eventgruppen_name'].($event['kommentar'] ? ': '.$event['kommentar'] : '');

		if($event['startzeit'] == '00:00:00' && $event['endzeit'] == '00:00:00') {
			$content['zeit'] = getLL('time_all_day');
		} else {
			$content['zeit'] = substr($event['startzeit'], 0, -3).'-'.substr($event['endzeit'], 0, -3);
		}

		//Multiday events
		if($event['startdatum'] != $event['enddatum']) {
			$date = $event['startdatum'];
			while((int)str_replace('-', '', $date) <= (int)str_replace('-', '', $event['enddatum'])) {
				if(substr($date, 5, 2) == date('m')) {
					$data[(int)substr($date, -2)][] = $content;
				}
				$date = add2date($date, 'tag', 1, TRUE);
			}
		} else {
			$data[(int)substr($event['startdatum'], -2)][] = $content;
		}
	}//foreach(events)

	//Datums-Berechnungen
	//Start des Monats
	$startdate = date(date('Y')."-".date('m')."-01");
	$today = date("Y-m-d");
	$startofmonth = $date = $startdate;

	//Den letzten Tag dieses Monats finden
	$endofmonth = add2date($date, "monat", 1, TRUE);
	$endofmonth = add2date($endofmonth, "tag", -1, TRUE);
	//Ende der letzten Woche dieses Monats finden
	$enddate = date_find_next_sunday($endofmonth);
	//Start der ersten Woche dieses Monats finden
	$date = date_find_last_monday($date);

	//Table header
	$r  = '<table class="fm_kalender">';
	$r .= '<tr><td>&nbsp;</td>';
	$tempdate = $date;
	for($i=0; $i<7; $i++) {
		$r .= '<td class="kalender_header">'.substr(strftime('%a', strtotime($tempdate)), 0, 1).'</td>';
		$tempdate = add2date($tempdate, 'tag', 1, TRUE);
	}
	$r .= '</tr>';

	$dayofweek = 0;
	$jsmap = array("\n" => ' ', "\r" => ' ', "'" => '', '"' => '');
	while((int)str_replace("-", "", $date) <= (int)str_replace("-", "", $enddate)) {
		if($dayofweek == 0) {
			$r .= '<tr>';
			//Add week number
			$r .= '<td class="kalender_weeks">'.strftime('%V', strtotime($date)).'</td>';
		}
		$class = $today == $date ? 'kalender_tag_aktiv' : 'kalender_tag';
		if(strftime('%m', strtotime($date)) == date('m')) {
			$tooltip = '';
			if(isset($data[substr($date, -2)])) {
				foreach($data[substr($date, -2)] as $entry) {
					$tooltip .= '<b>'.strtr($entry['text'], $jsmap).'</b><br />'.strtr($entry['zeit'], $jsmap).'<br />';
				}
				$ph = $pos == '3' ? 'l' : 'r';
				$r .= '<td class="'.$class.'" onmouseover="tooltip.show(\''.$tooltip.'\', \'\', \'b\', \''.$ph.'\');" onmouseout="tooltip.hide();">';
				$r .= '<b>'.strftime('%d', strtotime($date)).'</b>';
			} else {
				$r .= '<td class="'.$class.'">'.strftime('%d', strtotime($date));
			}
		} else {
			$r .= '<td class="'.$class.'">&nbsp';
		}
		$r .= '</td>';

		$date = add2date($date, "tag", 1, TRUE);
		$dayofweek++;
		if($dayofweek == 7) {
			$r .= '</tr>';
			$dayofweek = 0;
		}
	}
	$r .= '</table>';

	$smarty->assign("tpl_cal_titel", getLL("fm_daten_title")." ".strftime($GLOBALS["DATETIME"]["mY"], time()));
	$smarty->assign('table', $r);
	return $smarty->fetch('ko_fm_daten_cal.tpl');

}//ko_fm_daten_cal()



/*
 * Geburtstagsliste
 */
function ko_fm_geburtstage($uid, $pos) {
	global $smarty, $ko_path, $access;

	if(!ko_module_installed('leute')) return FALSE;

	//Check for access to birthday column
	$columns = ko_get_leute_admin_spalten($uid, 'all');
	if(is_array($columns['view']) && !in_array('geburtsdatum', $columns['view'])) return FALSE;

	$all_rights = ko_get_access_all('leute_admin', $uid, $max_rights);
	if($max_rights < 1) return FALSE;
	if($all_rights > 0) {  //No access restrictions if all rights 1 or more
		$z_where = " AND `deleted` = '0' AND `hidden` = '0' ";
	} else {  //Else apply admin filter for the query
		apply_leute_filter('', $z_where, TRUE, $i);
	}

	//Get dealine settings for birthdays
	$deadline_plus = ko_get_userpref($uid, 'geburtstagsliste_deadline_plus');
	$deadline_minus = ko_get_userpref($uid, 'geburtstagsliste_deadline_minus');
	if(!$deadline_plus) $deadline_plus = 21;
	if(!$deadline_minus) $deadline_minus = 7;

	$where = '';
	$dates = array();
	$today = date('Y-m-d');
	for($inc = -1*$deadline_minus; $inc <= $deadline_plus; $inc++) {
		$d = add2date($today, 'day', $inc, TRUE);
		$dates[substr($d, 5)] = $inc;
		[$month, $day] = explode('-', substr($d, 5));
		$where .= " OR (MONTH(`geburtsdatum`) = '$month' AND DAY(`geburtsdatum`) = '$day') ";
	}
	$where = " AND (".substr($where, 3).") ".ko_get_birthday_filter();
	
	$es = db_select_data('ko_leute', 'WHERE 1=1 '.$where.$z_where, '*');

	$sort = array();
	foreach($es as $pid => $p) {
		$sort[$pid] = $dates[substr($p['geburtsdatum'], 5)];
	}
	asort($sort);

	$data = array();
	$row = 0;
	foreach($sort as $pid => $deadline) {
		$p = $es[$pid];

		$p['deadline'] = $deadline;
		$p['alter'] = (int)substr(add2date(date('Y-m-d'), 'day', $deadline, TRUE), 0, 4) - (int)substr($p['geburtsdatum'], 0, 4);

		$data[$row] = $p;
		$data[$row]['geburtsdatum'] = sql2datum($p['geburtsdatum']);

		//Overlib-Text mit ko_html2 für FM
		$ttText  = '&lt;b&gt;'.ko_html2($p['vorname']).' '.ko_html2($p['nachname']).'&lt;/b&gt; ';
		$ttText .= '('.$p['alter'].')&lt;br /&gt;'.sql2datum($p['geburtsdatum']);

		$data[$row]['_tooltip'] = ko_get_tooltip_code($ttText);

		//Link
		$data[$row]['_link'] = 'leute/index.php?action=set_idfilter&amp;id='.$p['id'];

		$row++;
	}//foreach(es)

	$smarty->assign('people', $data);
	$smarty->assign('tpl_fm_title', getLL('fm_birthdays_title'));
	$smarty->assign('label_years', getLL('fm_birthdays_label_years'));
	$smarty->assign('tpl_fm_pos', $pos);
	$smarty->assign('ttpos', $pos == '3' ? 'l' : 'r');
	return $smarty->fetch('ko_fm_geburtstage.tpl');
}//ko_fm_geburtstage()



/*
 * Moderationen (Reservationen, Adressänderungen)
 */
function ko_fm_mod($uid) {
	global $ko_path, $smarty;
	global $access;

	if($uid == ko_get_guest_id()) return FALSE;

	//Reservations awaiting moderation
	include($ko_path.'reservation/inc/reservation.inc');
	ko_get_access('reservation', $uid);
	
  if($access['reservation']['MAX'] > 4) {  //Moderator for at least one item
		$show_res_mod = TRUE;
		$mod_items = array();
		foreach($access['reservation'] as $k => $v) {
			if(intval($k) && $v > 4) $mod_items[] = $k;
		}
	  ko_get_res_mod($res_mod, $mod_items);
  } else if($access['reservation']['MAX'] > 1) {
		$show_res_mod = TRUE;
	  ko_get_res_mod($res_mod, '', $uid);
	}


	//Adressänderungen:
	ko_get_access('leute', $uid);
	if($access['leute']['MAX'] > 1) {
		$show_aa = TRUE;
		ko_get_mod_leute($aa);
		//For logins with edit access to only some addresses exclude those they don't have access to
		if($access['leute']['ALL'] < 2) {
			foreach($aa as $aid => $a) {
				if($access['leute'][$a['_leute_id']] < 2 || $a['_leute_id'] < 1) unset($aa[$aid]);
			}
		}
		$aa_mod_count = sizeof($aa);
	} else {
		$show_aa = FALSE;
	}

	//group subscriptions
	if($access['leute']['ALL'] > 3 || ($access['leute']['MAX'] > 1 && $access['leute']['GS'])) {
		ko_get_groupsubscriptions($gs, "", $uid);
		$num_group_mod = sizeof($gs);
		$show_group_mod = TRUE;
	} else {
		$show_group_mod = FALSE;
	}


	//Event moderations
	ko_get_access('daten', $uid);
	if($access['daten']['MAX'] > 3) {
		$show_event_mod = TRUE;
		$mod_items = array();
		foreach($access['daten'] as $k => $v) {
			if(intval($k) && $v > 3) $mod_items[] = $k;
		}
		$where = " AND `eventgruppen_id` IN ('".implode("','", $mod_items)."') ";
  } else if($access['daten']['MAX'] > 1) {
		$show_event_mod = TRUE;
		$where = " AND `_user_id` = '$uid' ";
	}
	$num_event_mod = db_get_count('ko_event_mod', 'id', $where);


	//Donations
	$all_rights_donations = ko_get_access_all('donations_admin', '', $max_rights_donations);
	if($max_rights_donations > 1 && $access['leute']['MAX'] > 1) {
		$show_donations_mod = TRUE;
		ko_get_donations_mod($modDonations);
		//For logins with edit access to only some accounts exclude those they don't have access to
		if($all_rights_donations < 2) {
			if(!is_array($access['donations'])) ko_get_access('donations');
			foreach($modDonations as $aid => $a) {
				if($access['donations'][$a['_account_id']] < 2 || $a['_account_id'] < 1) unset($modDonations[$aid]);
			}
		}
		$num_donations_mod = sizeof($modDonations);
	}



	$smarty->assign("tpl_fm_title", getLL("fm_mod_title"));

	//Text und Link für Reservationen
	$smarty->assign("tpl_show_res", $show_res_mod);
	$smarty->assign("tpl_num_res", sizeof($res_mod));
	$smarty->assign("tpl_text_res", getLL("fm_mod_open_res"));
  $smarty->assign("tpl_open_mod_res", (sizeof($res_mod) > 0) ? TRUE : FALSE);

	//Text und Link für Adressänderungen
	$smarty->assign("tpl_show_aa", $show_aa);
	$smarty->assign("tpl_num_aa", $aa_mod_count);
	$smarty->assign("tpl_text_aa", getLL("fm_mod_open_aa"));
  $smarty->assign("tpl_open_mod_aa", ($aa_mod_count > 0) ? TRUE : FALSE);

	//Text und Link für Gruppen-Anmeldungen
	$smarty->assign("tpl_show_groups", $show_group_mod);
	$smarty->assign("tpl_num_groups", $num_group_mod);
	$smarty->assign("tpl_text_groups", getLL("fm_mod_open_group"));
  $smarty->assign("tpl_open_mod_groups", ($num_group_mod > 0) ? TRUE : FALSE);

	//Text und Link für Events
	$smarty->assign("tpl_show_event", $show_event_mod);
	$smarty->assign("tpl_num_event", $num_event_mod);
	$smarty->assign("tpl_text_event", getLL("fm_mod_open_events"));
  $smarty->assign("tpl_open_mod_event", $num_event_mod > 0 ? TRUE : FALSE);

	//Text and link for donations
	$smarty->assign("tpl_show_donations", $show_donations_mod);
	$smarty->assign("tpl_num_donations", $num_donations_mod);
	$smarty->assign("tpl_text_donations", getLL("fm_mod_open_donations"));
  $smarty->assign("tpl_open_mod_donations", $num_donations_mod > 0 ? TRUE : FALSE);

	return $smarty->fetch("ko_fm_mod.tpl");
}//ko_fm_mod_res()


/**
 * Block "Today" on frontpage
 * Display events, reservations, leute changes
 *
 *
 */
function ko_fm_today($uid, $pos) {
	global $ko_path, $smarty, $access;


	//*** DATEN ***
	ko_get_access('daten');
	if ($access['daten']['MAX'] > 0) {
		// apply filter from userpref
		$filterPresetName = ko_get_userpref($_SESSION['ses_userid'], 'daten_fm_filter');
		$zWhere = '';
		if ($filterPresetName) {
			if (substr($filterPresetName, 0, 4) == '[G] ') {
				$n = substr($filterPresetName, 4);
				$user = -1;
			} else {
				$n = $filterPresetName;
				$user = $_SESSION['ses_userid'];
			}
			$filterPreset = ko_get_userpref($user, $n, 'daten_itemset');

			$allowedGroups = ko_array_filter_empty(explode(',', end($filterPreset)['value']));
			if (sizeof($allowedGroups) > 0) {
				$zWhere = " AND `eventgruppen_id` IN (" . implode(',', $allowedGroups) . ")";
			}
		}

		//Datum heute:
		$smarty->assign("datum_heute", strftime($GLOBALS["DATETIME"]["dmy"], time()));

		//Termine von heute
		$smarty->assign("title_event_today", getLL("fm_today_events_today"));
		ko_get_events_by_date(date("d"), date("m"), date("Y"), $events_heute, $zWhere);
		foreach ($events_heute as $h_i => $h) {
			if ($access['daten']['ALL'] < 1 && $access['daten'][$h['eventgruppen_id']] < 1) {
				unset($events_heute[$h_i]);
				continue;
			}

			$mapping = ['room' => $h["room"]];
			ko_include_kota(["ko_event"]);
			kota_process_data("ko_event", $mapping, "list");
			$events_heute[$h_i]["raum"] = ko_html($mapping['room']);

			$events_heute[$h_i]["eventgruppe"] = ko_html($h["eventgruppen_name"]);
			$events_heute[$h_i]['kommentar'] = $h['kommentar'];
			$events_heute[$h_i]['title'] = ko_html($h['title']);

			if ($h["startzeit"] == "00:00:00" && $h["endzeit"] == "00:00:00") {
				$events_heute[$h_i]["startzeit"] = getLL("time_all_day");
				$events_heute[$h_i]["endzeit"] = "";
			} else {
				$events_heute[$h_i]["startzeit"] = sql_zeit($h["startzeit"]);
				$events_heute[$h_i]["endzeit"] = sql_zeit($h["endzeit"]);
			}
		}
		if (sizeof($events_heute) > 0) {
			$smarty->assign("today_daten_heute", $events_heute);
			$smarty->assign("show_daten_heute", TRUE);
		} else {
			$smarty->assign("show_daten_heute", FALSE);
		}


		//Termine diese Woche
		$smarty->assign("title_event_week", getLL("fm_today_events_week"));
		$heute = date("d.m.Y");
		$events_woche = [];

		for ($i = 1; $i <= 7; $i++) {
			$tag = add2date($heute, "tag", $i);
			unset($temp);
			ko_get_events_by_date($tag[0], $tag[1], $tag[2], $temp, $zWhere);
			if (sizeof($temp) > 0) $events_woche = array_merge($events_woche, $temp);
		}

		$done = [];
		foreach ($events_woche as $w_i => $w) {
			//Termine nicht doppelt anzeigen - würde bei mehrtägigen passieren
			if (($access['daten']['ALL'] < 1 && $access['daten'][$w['eventgruppen_id']] < 1) || in_array($w["id"], $done)) {
				unset($events_woche[$w_i]);
				continue;
			}
			$done[] = $w["id"];

			$mapping = ['room' => $w["room"]];
			ko_include_kota(["ko_event"]);
			kota_process_data("ko_event", $mapping, "list");
			$events_woche[$w_i]["raum"] = ko_html($mapping['room']);

			$events_woche[$w_i]["eventgruppe"] = ko_html($w["eventgruppen_name"]);
			$events_woche[$w_i]['kommentar'] = $w['kommentar'];
			$events_woche[$w_i]['title'] = ko_html($w['title']);

			$tag = explode("-", $w["startdatum"]);
			$events_woche[$w_i]["wochentag"] = strftime("%A", mktime(1, 1, 1, $tag[1], $tag[2], $tag[0]));

			if ($w["startdatum"] == $w["enddatum"]) $events_woche[$w_i]["enddatum"] = "";
			else $events_woche[$w_i]["enddatum"] = sql2datum($w["enddatum"]);
			$events_woche[$w_i]["startdatum"] = sql2datum($w["startdatum"]);

			if ($w["startzeit"] == "00:00:00" && $w["endzeit"] == "00:00:00") {
				$events_woche[$w_i]["startzeit"] = getLL("time_all_day");
				$events_woche[$w_i]["endzeit"] = "";
			} else {
				$events_woche[$w_i]["startzeit"] = sql_zeit($w["startzeit"]);
				$events_woche[$w_i]["endzeit"] = sql_zeit($w["endzeit"]);
			}
		}
		if (sizeof($events_woche) > 0) {
			$smarty->assign("today_daten_woche", $events_woche);
			$smarty->assign("show_daten_woche", TRUE);
		} else {
			$smarty->assign("show_daten_woche", FALSE);
		}
	}//if(d_view)
	else {
		$smarty->assign("show_daten_heute", FALSE);
		$smarty->assign("show_daten_woche", FALSE);
	}


	//*** RESERVATIONEN ***
	//(Eigene oder bei Mod, die gemachten)
	ko_get_access('reservation');
//	if ($access['reservation']['MAX'] > 1 && $_SESSION["ses_userid"] != ko_get_guest_id()) {
//		//Reservationen diese Woche
//		$smarty->assign("title_res_week", getLL("fm_today_res_week"));
//		$smarty->assign("show_res", TRUE);
//	}//if(sizeof(res))


	//Bei Moderatoren die neuen/geänderten seit letztem Login anzeigen
	if ($access['reservation']['MAX'] > 4 && $_SESSION["ses_userid"] != ko_get_guest_id()) {
		//Alle geänderten aus DB holen
		$smarty->assign("title_res_new", getLL("fm_today_res_new"));
		$z_where = "AND `last_change` > '" . $_SESSION["last_login"] . "'";
		ko_get_reservationen($res, $z_where, 'LIMIT 0,50', "res",'ORDER BY `last_change` DESC');

		foreach ($res as $w_i => $w) {
			if ($access['reservation']['ALL'] > 4 || $access['reservation'][$w['item_id']] > 4) {
				$res_woche_mod[$w_i]["item"] = ko_html($w["item_name"]);
				$res_woche_mod[$w_i]["zweck"] = ko_html($w["zweck"]);
				$res_woche_mod[$w_i]["name"] = ko_html($w["name"]);
				$res_woche_mod[$w_i]["email"] = ko_html($w["email"]);
				$res_woche_mod[$w_i]["telefon"] = ko_html($w["telefon"]);

				$tag = explode("-", $w["startdatum"]);
				$res_woche_mod[$w_i]["wochentag"] = strftime("%A", mktime(1, 1, 1, $tag[1], $tag[2], $tag[0]));

				if ($w["startdatum"] == $w["enddatum"]) $res_woche_mod[$w_i]["enddatum"] = "";
				else $res_woche_mod[$w_i]["enddatum"] = sql2datum($w["enddatum"]);
				$res_woche_mod[$w_i]["startdatum"] = sql2datum($w["startdatum"]);

				if ($w["startzeit"] == "00:00:00" && $w["endzeit"] == "00:00:00") {
					$res_woche_mod[$w_i]["startzeit"] = getLL("time_all_day");
					$res_woche_mod[$w_i]["endzeit"] = "";
				} else {
					$res_woche_mod[$w_i]["startzeit"] = sql_zeit($w["startzeit"]);
					$res_woche_mod[$w_i]["endzeit"] = sql_zeit($w["endzeit"]);
				}
			}
		}//foreach(res as w)

		if (sizeof($res_woche_mod) > 0) {
			$smarty->assign("show_res_mod", TRUE);
			$smarty->assign("today_res_mod", $res_woche_mod);
		} else {
			$smarty->assign("show_res_mod", FALSE);
		}
	}//if(sizeof(res))


	/* Letzte Leute-Änderungen */
	$found = FALSE;
	$smarty->assign("title_people_new", getLL("fm_today_people_new"));
	ko_get_access('leute');
	if ($access['leute']['MAX'] >= 1) {
		$logs = db_select_data('ko_log', "WHERE `type` = 'edit_person' AND `date` >= '" . $_SESSION['last_login'] . "' " . $where_add, '*', 'ORDER BY `date` DESC', 'LIMIT 0,30');
		if (sizeof($logs) > 0) {
			$p_counter = 0;
			$found = TRUE;
			ko_get_logins($logins);
			$lids = [];
			foreach ($logs as $logid => $log) {
				$logs[$logid]['_leute_id'] = $lids[] = (int)substr($log['comment'], 0, strpos($log['comment'], ' '));
			}
			ko_get_leute($people, " AND `id` IN ('" . implode("','", $lids) . "')");

			$allowed_columns = ko_get_leute_admin_spalten($_SESSION['ses_userid'], "login");
			for ($i = 0; $i < count($allowed_columns['view']); $i++) {
				$allowed_columns['view'][$i] = strtolower(getLL("kota_ko_leute_" . $allowed_columns['view'][$i]));
			}

			foreach ($logs as $log) {
				// Do access check
				if ($access['leute']['ALL'] == 0 && $access['leute'][$log['_leute_id']] == 0) continue;

				if (is_array($allowed_columns['view'])) {  //Only check for individual columns if column restriction is set
					preg_match_all(
						"/\s?([\w\s.\-@,:\/]+):\s\\\\\'([\w\s.\-@,:\/]*)\\\\\'\s-->\s\\\\\'([\w\s.\-@,:\/]*)\\\\\'[,]?/im",
						substr($log['comment'], strpos($log['comment'], '): ') + 3),
						$comments,
						PREG_SET_ORDER,
						0
					);

					$foundChanges = FALSE;
					foreach ($comments AS $key => $comment) {
						if ($allowed_columns['view'] === NULL ||
							in_array(strtolower($comment[1]), $allowed_columns['view'])
						) {
							$tpl_person[$p_counter]['log'] .= "<br />" . $comment[1] . ": \"" . $comment[2] . "\" => \"" . $comment[3] . "\"";
							$foundChanges = TRUE;
						}
					}
					if (!$foundChanges) continue;
				} else {  //..else show all changes
					$tpl_person[$p_counter]['log'] = '<br />' . ko_html(substr($log['comment'], strpos($log['comment'], ':') + 2));
				}

				$tpl_person[$p_counter]['user'] = $logins[$log['user_id']]['login'];

				//Name of the edited person
				$person = $people[$log['_leute_id']];
				if (isset($person['firm']) && $person['firm']) {
					$tpl_person[$p_counter]['name'] = $person['firm'] . ' ' . $person['department'];
					$tpl_person[$p_counter]['link'] = 'leute/index.php?action=set_idfilter&amp;id=' . $person['id'];
					if ($person['nachname']) {
						$tpl_person[$p_counter]['name'] .= ': ' . $person['vorname'] . ' ' . $person['nachname'];
						$tpl_person[$p_counter]['link'] .= '&amp;ln=' . urlencode($person['nachname']) . '&amp;fn=' . urlencode($person['vorname']);
					}
				} else {
					$tpl_person[$p_counter]['name'] = $person['vorname'] . ' ' . $person['nachname'];
					$tpl_person[$p_counter]['link'] = 'leute/index.php?action=set_idfilter&amp;id=' . $person['id'];
				}
				$p_counter++;
			}
			$smarty->assign('today_leute_change', $tpl_person);
		}//if(sizeof(logs) > 0)
	}//if(l_edit)
	$smarty->assign('show_leute_change', $found);

	$smarty->assign("tpl_fm_pos", $pos);
	$smarty->assign("tpl_fm_title", getLL("fm_name_today"));
	return $smarty->fetch("ko_fm_today.tpl");

}//ko_fm_today()


function ko_fm_rota($uid) {
	global $BASE_URL, $smarty;

	$person = ko_get_logged_in_person($uid);

	if(!$person['id']) return FALSE;

	/* get schedulling */
	$data = ["id" => $person['id']];
	kota_pre_ko_leute_info_rota_2($rota_html, $data);
	$smarty->assign("label_link", getLL("fm_news_link"));
	$smarty->assign("tpl_fm_title", getLL("module_rota"));
	$smarty->assign("tpl_rota", $rota_html);

	/* get ical */
	if(defined('KOOL_ENCRYPTION_KEY') && trim(KOOL_ENCRYPTION_KEY) !== '') {
		$link = $BASE_URL . 'rotaical/index.php?person=' . $person['id'] . 'x' . strtolower(substr(md5($person['id'] . KOOL_ENCRYPTION_KEY . 'rotaIcal' . KOOL_ENCRYPTION_KEY . 'rotaIcal' . KOOL_ENCRYPTION_KEY . $person['id']), 0, 10));
		$label =  getLL('fm_name_rota_ical');
		$rota_ical = ko_get_ical_link($link, $label);
		$rota_ical.= " <a href='https://kool.help/module/dienstplan#ical-abo' target='_new'><span class=\"glyphicon glyphicon-info-sign\"></span></a>";
		$smarty->assign("tpl_ical", $rota_ical);
	}

	/* get consensus */
	if(ko_get_setting("consensus_ongoing_cal")) {
		$consensus_link = $BASE_URL;
		if(substr($BASE_URL, -1) != '/') $consensus_link .= '/';
		$timestart = date("Y-m-d", time());
		$consensus_link.= 'consensus/?x=' . $person['id'] . 'x' . str_replace("-","", $timestart) . 'x1wx' .
			substr(md5($person['id'] . $timestart . '1w' . KOOL_ENCRYPTION_KEY), 0, 6);

		$teams = ko_rota_get_teams($person['id']);
		foreach($teams AS $team) {
			if($team['allow_consensus'] == 1) {
				$smarty->assign("tpl_consensus", $consensus_link);
			}
		}
	}

	return $smarty->fetch("ko_fm_rota.tpl");
}



/*
 * News
 */
function ko_fm_news($uid, $pos, $id) {
	global $ko_path, $smarty;

	if($id > 0) {
		$news_ = db_select_data('ko_news', "WHERE `id` = '$id'", '*');
	} else {

		if($uid == ko_get_guest_id()) {  //ko-Guest
			$z_where = "AND `type` = '1'";
		} else {  //Logged-in user
			$z_where = "AND `type` IN ('1', '2')";
		}

		$news_ = db_select_data('ko_news', 'WHERE 1=1 '.$z_where, '*', 'ORDER BY cdate DESC');
	}//if..else(id>0)

	//Get status from userpref - to mark news as read/new for current user
	$newsStatus = json_decode(ko_get_userpref($_SESSION['ses_userid'], 'news_status'), TRUE);

	$categories = array();
	foreach($news_ as $n_i => $n) {
		$news[$n_i]["text"] = $n["text"];
		$news[$n_i]["subtitle"] = nl2br(ko_html($n["subtitle"]));
		$news[$n_i]["title"] = nl2br(ko_html($n["title"]));
		$news[$n_i]["link"] = ko_html($n['link']);
		$news[$n_i]["author"] = ko_html($n["author"]);
		if($n['cdate'] != '0000-00-00') $news[$n_i]['cdate'] = sql2datum($n['cdate']);
		$news[$n_i]["id"] = $n["id"];
		
		$news[$n_i]['category'] = ko_html($n['category']);
		if($n['category']) $categories[] = $n['category'];

		if($uid == ko_get_guest_id()) {
			$news[$n_i]['statusRead'] = 0;
		} else {
			$news[$n_i]['statusRead'] = $newsStatus[$n['id']] == 1 ? 1 : 0;
		}
	}
	$categories = array_unique($categories);
	asort($categories);

	$smarty->assign("label_link", getLL("fm_news_link"));
	$smarty->assign("tpl_fm_title", "News");
	$smarty->assign("tpl_news", $news);
	$smarty->assign("tpl_fm_pos", $pos);
	$smarty->assign("is_guest", $uid == ko_get_guest_id());
	$smarty->assign('categories', $categories);

	return $smarty->fetch("ko_fm_news.tpl");
}//ko_fm_news()




/*
 * Adress-Aenderung
 */
function ko_fm_adresse($uid) {
	global $ko_path, $smarty, $access;

	if(!ko_module_installed('leute')) return FALSE;
	if(ko_get_setting('leute_disable_aa_fm')) return FALSE;

	//Don't show mutation form for users with global access 2 or more for the address module (as they can make changes to the addresses directly)
	$rights_all = ko_get_access_all('leute_admin', $uid);
	if($rights_all >= 2) {
		$allowed_cols = ko_get_leute_admin_spalten($_SESSION['ses_userid'], 'all');
		if(!is_array($allowed_cols['edit'])) return FALSE;
	}

	$smarty->assign("label_name", getLL("fm_aa_name"));
	$smarty->assign("label_firstname", getLL("fm_aa_firstname"));
	$smarty->assign("label_ok", getLL("OK"));
	$smarty->assign("label_reset", getLL("reset"));
	$smarty->assign("title_edit", getLL("fm_aa_comment_edit"));
	$smarty->assign("title_new", getLL("fm_aa_comment_new"));
	$smarty->assign("label_comment", getLL("fm_aa_comment"));

	$smarty->assign("tpl_aa_show", "name");
	$smarty->assign("tpl_fm_title", getLL("fm_aa_title"));
	return $smarty->fetch("ko_fm_adressaenderung.tpl");
}//ko_fm_adresse()

