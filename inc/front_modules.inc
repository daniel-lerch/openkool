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
function ko_front_module($uid, $module, $pos = "", $news_id = 0) {
	global $smarty;

	// Auf genügend Rechte überprüfen, ob das gewünschte Modul angezeigt werden darf
	// TODO...

	// Richtige Funktion aufrufen, die das gewünschte Front-Modul ausgibt
	switch($module) {
		case "daten_cal":
			ko_fm_daten_cal($uid, $pos);
		break;
		case "geburtstage":
			ko_fm_geburtstage($uid, $pos);
		break;
		case "mod":
			ko_fm_mod($uid);
		break;
		case "news":
			ko_fm_news($uid, $pos, $news_id);
		break;
		case "adressaenderung":
			ko_fm_adresse($uid);
		break;
		case "today":
			ko_fm_today($uid, $pos);
		break;
		case "fileshare":
			if(!ENABLE_FILESHARE) continue;
			ko_fm_fileshare($uid, $pos);
		break;
		case 'fastfilter':
			ko_fm_fastfilter($uid);
		break;

		//Frontmodule from a plugin
		default:
			if(function_exists('my_frontmodule_'.$module)) {
				call_user_func('my_frontmodule_'.$module, $uid, $pos);
			}
	}
}//ko_front_module()




function ko_fm_fastfilter($uid) {
	global $smarty;

	$content = '';
	$fast_filter = ko_get_fast_filter();
	foreach($fast_filter as $id) {
		ko_get_filter_by_id($id, $ff);
		$content .= $ff['name'].':<br />';;
		$ff_code = str_replace('var1', ('fastfilter'.$id), $ff['code1']);
		$ff_code = str_replace('submit_filter', 'set_fastfilter', $ff_code);
		$content .= $ff_code.'<br />';
	}

	$content .= '<p align="center">';
	$content .= '<input type="submit" name="submit_fm_fastfilter" value="'.getLL('ok').'" />';
	$content .= '</p>';

	$smarty->assign('tpl_fm_title', getLL('fm_fastfilter_title'));
	print '<form action="leute/index.php?action=set_fastfilter" method="POST">';
	$smarty->display('ko_fm_header.tpl');
	print $content;
	$smarty->display('ko_fm_footer.tpl');
	print '</form>';
}//ko_fm_fastfilter()




/*
 * Gibt einen Kalender mit allen sichtbaren Events aus
 */
function ko_fm_daten_cal($uid, $pos) {
	global $ko_path, $smarty, $access;
	
	ko_get_access('daten');
	include($ko_path . "daten/inc/daten.inc");

	$egs = array();
	if($access['daten']['ALL'] > 0) {
		$z_where = '';
	} else {
		//Get all eventgroups, access check will be done in apply_daten_filter()
		ko_get_eventgruppen($egs, '', "AND `type` = '0'");
		apply_daten_filter($z_where, $z_limit, 'immer', 'immer', array_keys($egs));
	}
	
	$title_length = ko_get_userpref($_SESSION['ses_userid'], 'daten_title_length');
	$startstamp = mktime(1,1,1, date('m'), 1, date('Y'));
	$endstamp = mktime(1,1,1, (date('m') == 12 ? 1 : date('m')+1), 0, (date('m') == 12 ? date('Y')+1 : date('Y')));
	$z_where .= ' AND `enddatum` >= \''.strftime('%Y-%m-%d', $startstamp).'\' AND `startdatum` <= \''.strftime('%Y-%m-%d', $endstamp).'\'';
	ko_get_events($events, $z_where);

	$data = array();
	foreach($events as $event) {
		$content = array();
		$content['text'] = $event['eventgruppen_name'].($event['kommentar'] ? ': '.$event['kommentar'] : '');
		if(strlen($content['text']) > $title_length) $content['text'] = substr($content['text'], 0, $title_length).'..';

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
	$r  = '<table width="100%" cellspacing="0" border="1">';
	$r .= '<tr><td kalender_header>&nbsp;</td>';
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
				$ph = $pos == 'r' ? 'l' : 'r';
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
	$smarty->display('ko_fm_daten_cal.tpl');

}//ko_fm_daten_cal()



/*
 * Geburtstagsliste
 */
function ko_fm_geburtstage($uid, $pos) {
	global $smarty, $ko_path, $access;

	if(!ko_module_installed('leute')) return FALSE;

	//Check for access to birthday column
	$columns = ko_get_leute_admin_spalten($uid);
	if(is_array($columns['view']) && !in_array('geburtsdatum', $columns['view'])) return FALSE;

	$all_rights = ko_get_access_all('leute_admin', $uid);
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
		list($month, $day) = explode('-', substr($d, 5));
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
		$data[$row]['_tooltip']  = '&lt;b&gt;'.ko_html2($p['vorname']).' '.ko_html2($p['nachname']).'&lt;/b&gt; ';
		$data[$row]['_tooltip'] .= '('.$p['alter'].')&lt;br /&gt;'.sql2datum($p['geburtsdatum']);

		//Link
		$data[$row]['_link'] = 'leute/index.php?action=set_idfilter&amp;id='.$p['id'];

		$row++;
	}//foreach(es)

	$smarty->assign('people', $data);
	$smarty->assign('tpl_fm_title', getLL('fm_birthdays_title'));
	$smarty->assign('label_years', getLL('fm_birthdays_label_years'));
	$smarty->assign('tpl_fm_pos', $pos);
	$smarty->assign('ttpos', $pos == 'r' ? 'l' : 'r');
	$smarty->display('ko_fm_geburtstage.tpl');
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



	$smarty->assign("tpl_fm_title", getLL("fm_mod_title"));

	//Text und Link für Reservationen
	$smarty->assign("tpl_show_res", $show_res_mod);
	$smarty->assign("tpl_text_res", getLL("fm_mod_open_res")."&nbsp;(".sizeof($res_mod).")<br />");
  $smarty->assign("tpl_open_mod_res", (sizeof($res_mod) > 0) ? TRUE : FALSE);

	//Text und Link für Adressänderungen
	$smarty->assign("tpl_show_aa", $show_aa);
	$smarty->assign("tpl_text_aa", getLL("fm_mod_open_aa")."&nbsp;($aa_mod_count)<br />");
  $smarty->assign("tpl_open_mod_aa", ($aa_mod_count > 0) ? TRUE : FALSE);

	//Text und Link für Gruppen-Anmeldungen
	$smarty->assign("tpl_show_groups", $show_group_mod);
	$smarty->assign("tpl_text_groups", getLL("fm_mod_open_group")."&nbsp;($num_group_mod)<br />");
  $smarty->assign("tpl_open_mod_groups", ($num_group_mod > 0) ? TRUE : FALSE);

	//Text und Link für Events
	$smarty->assign("tpl_show_event", $show_event_mod);
	$smarty->assign("tpl_text_event", getLL("fm_mod_open_events")."&nbsp;(".$num_event_mod.")<br />");
  $smarty->assign("tpl_open_mod_event", $num_event_mod > 0 ? TRUE : FALSE);

	$smarty->display("ko_fm_mod.tpl");
}//ko_fm_mod_res()





/**
  * Today
	*/
function ko_fm_today($uid, $pos) {
	global $ko_path, $smarty, $access;


	//*** DATEN ***
	ko_get_access('daten');
	if($access['daten']['MAX'] > 0) {

		//Datum heute:
		$smarty->assign("datum_heute", strftime($GLOBALS["DATETIME"]["dmy"], time()));

		//Termine von heute
		$smarty->assign("title_event_today", getLL("fm_today_events_today"));
		ko_get_events_by_date(date("d"), date("m"), date("Y"), $events_heute);
		foreach($events_heute as $h_i => $h) {
			if($access['daten']['ALL'] < 1 && $access['daten'][$h['eventgruppen_id']] < 1) {
				unset($events_heute[$h_i]);
				continue;
			}

			$events_heute[$h_i]["raum"] = ko_html($h["room"]);
			$events_heute[$h_i]["eventgruppe"] = ko_html($h["eventgruppen_name"]);
			$events_heute[$h_i]["kommentar"] = ko_html($h["kommentar"]);

			if($h["startzeit"] == "00:00:00" && $h["endzeit"] == "00:00:00") {
				$events_heute[$h_i]["startzeit"] = getLL("time_all_day");
				$events_heute[$h_i]["endzeit"] = "";
			} else {
				$events_heute[$h_i]["startzeit"] = sql_zeit($h["startzeit"]);
				$events_heute[$h_i]["endzeit"] = sql_zeit($h["endzeit"]);
			}
		}
		if(sizeof($events_heute) > 0) {
			$smarty->assign("today_daten_heute", $events_heute);
			$smarty->assign("show_daten_heute", TRUE);
		} else {
			$smarty->assign("show_daten_heute", FALSE);
		}


		//Termine diese Woche
		$smarty->assign("title_event_week", getLL("fm_today_events_week"));
		$heute = date("d.m.Y");
		$events_woche = array();

		for($i = 1; $i <= 7; $i++) {
			$tag = add2date($heute, "tag", $i);
			unset($temp);
			ko_get_events_by_date($tag[0], $tag[1], $tag[2], $temp);
			if(sizeof($temp) > 0) $events_woche = array_merge($events_woche, $temp);
		}

		$done = array();
		foreach($events_woche as $w_i => $w) {
			//Termine nicht doppelt anzeigen - würde bei mehrtägigen passieren
			if(($access['daten']['ALL'] < 1 && $access['daten'][$w['eventgruppen_id']] < 1) || in_array($w["id"], $done)) {
				unset($events_woche[$w_i]);
				continue;
			}
			$done[] = $w["id"];

			$events_woche[$w_i]["raum"] = ko_html($w["room"]);
			$events_woche[$w_i]["eventgruppe"] = ko_html($w["eventgruppen_name"]);
			$events_woche[$w_i]["kommentar"] = ko_html($w["kommentar"]);

			$tag = explode("-", $w["startdatum"]);
			$events_woche[$w_i]["wochentag"] = strftime("%A", mktime(1, 1, 1, $tag[1], $tag[2], $tag[0]));

			if($w["startdatum"] == $w["enddatum"]) $events_woche[$w_i]["enddatum"] = "";
			else $events_woche[$w_i]["enddatum"] = sql2datum($w["enddatum"]);
			$events_woche[$w_i]["startdatum"] = sql2datum($w["startdatum"]);

			if($w["startzeit"] == "00:00:00" && $w["endzeit"] == "00:00:00") {
				$events_woche[$w_i]["startzeit"] = getLL("time_all_day");
				$events_woche[$w_i]["endzeit"] = "";
			} else {
				$events_woche[$w_i]["startzeit"] = sql_zeit($w["startzeit"]);
				$events_woche[$w_i]["endzeit"] = sql_zeit($w["endzeit"]);
			}
		}
		if(sizeof($events_woche) > 0) {
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
	if($access['reservation']['MAX'] > 1 && $_SESSION["ses_userid"] != ko_get_guest_id()) {
		//Reservationen diese Woche
		$smarty->assign("title_res_week", getLL("fm_today_res_week"));
		$heute = date("d.m.Y");
		$res_woche = array();
		$res_woche_mod = array();

		for($i = 0; $i <= 7; $i++) {
			$tag = add2date($heute, "tag", $i);
			unset($temp);
			ko_get_res_by_date($tag[0], $tag[1], $tag[2], $temp);
			if(sizeof($temp) > 0) $res_woche = array_merge($res_woche, $temp);
		}

		$done = array();
		ko_get_resitems($resitems);
		foreach($res_woche as $w_i => $w) {
			$item = $resitems[$w["item_id"]];
			if(($access['reservation']['ALL'] < 1 && $access['reservation'][$w['item_id']] < 1)
					|| $w["user_id"] != $_SESSION["ses_userid"]  //Nur eigene anzeigen
					|| in_array($w["id"], $done)  //mehrtägige Reservationen nicht mehrfach anzeigen
					) {
				unset($res_woche[$w_i]);
				continue;
			}
			$done[] = $w["id"];

			$res_woche[$w_i]["item"] = ko_html($item["name"]);
			$res_woche[$w_i]["zweck"] = ko_html($w["zweck"]);
			$res_woche[$w_i]["name"] = ko_html($w["name"]);
			$res_woche[$w_i]["email"] = ko_html($w["email"]);
			$res_woche[$w_i]["telefon"] = ko_html($w["telefon"]);

			$tag = explode("-", $w["startdatum"]);
			$res_woche[$w_i]["wochentag"] = strftime("%A", mktime(1, 1, 1, $tag[1], $tag[2], $tag[0]));

			if($w["startdatum"] == $w["enddatum"]) $res_woche[$w_i]["enddatum"] = "";
			else $res_woche[$w_i]["enddatum"] = sql2datum($w["enddatum"]);
			$res_woche[$w_i]["startdatum"] = sql2datum($w["startdatum"]);

			if($w["startzeit"] == "00:00:00" && $w["endzeit"] == "00:00:00") {
				$res_woche[$w_i]["startzeit"] = getLL("time_all_day");
				$res_woche[$w_i]["endzeit"] = "";
			} else {
				$res_woche[$w_i]["startzeit"] = sql_zeit($w["startzeit"]);
				$res_woche[$w_i]["endzeit"] = sql_zeit($w["endzeit"]);
			}
		}//foreach(res_woche)

		if(sizeof($res_woche) > 0) {
			$smarty->assign("show_res", TRUE);
			$smarty->assign("today_res_woche", $res_woche);
		} else {
			$smarty->assign("show_res", FALSE);
		}
	}//if(sizeof(res))




	//Bei Moderatoren die neuen/geänderten seit letztem Login anzeigen
	if($access['reservation']['MAX'] > 4 && $_SESSION["ses_userid"] != ko_get_guest_id()) {
		//Alle geänderten aus DB holen
		$smarty->assign("title_res_new", getLL("fm_today_res_new"));
		$z_where = "AND `last_change` > '".$_SESSION["last_login"]."'";
		ko_get_reservationen($res, $z_where, 'LIMIT 0,50', 'ORDER BY `last_change` DESC');

		foreach($res as $w_i => $w) {
			if($access['reservation']['ALL'] > 4 || $access['reservation'][$w['item_id']] > 4) {
				$res_woche_mod[$w_i]["item"] = ko_html($w["item_name"]);
				$res_woche_mod[$w_i]["zweck"] = ko_html($w["zweck"]);
				$res_woche_mod[$w_i]["name"] = ko_html($w["name"]);
				$res_woche_mod[$w_i]["email"] = ko_html($w["email"]);
				$res_woche_mod[$w_i]["telefon"] = ko_html($w["telefon"]);

				$tag = explode("-", $w["startdatum"]);
				$res_woche_mod[$w_i]["wochentag"] = strftime("%A", mktime(1, 1, 1, $tag[1], $tag[2], $tag[0]));

				if($w["startdatum"] == $w["enddatum"]) $res_woche_mod[$w_i]["enddatum"] = "";
				else $res_woche_mod[$w_i]["enddatum"] = sql2datum($w["enddatum"]);
				$res_woche_mod[$w_i]["startdatum"] = sql2datum($w["startdatum"]);

				if($w["startzeit"] == "00:00:00" && $w["endzeit"] == "00:00:00") {
					$res_woche_mod[$w_i]["startzeit"] = getLL("time_all_day");
					$res_woche_mod[$w_i]["endzeit"] = "";
				} else {
					$res_woche_mod[$w_i]["startzeit"] = sql_zeit($w["startzeit"]);
					$res_woche_mod[$w_i]["endzeit"] = sql_zeit($w["endzeit"]);
				}
			}
		}//foreach(res as w)

		if(sizeof($res_woche_mod) > 0) {
			$smarty->assign("show_res_mod", TRUE);
			$smarty->assign("today_res_mod", $res_woche_mod);
		} else {
			$smarty->assign("show_res_mod", FALSE);
		}
	}//if(sizeof(res))



	/* Letzte Leute-Änderungen */
	$found = FALSE;
	$smarty->assign("title_people_new", getLL("fm_today_people_new"));
	ko_get_access_all('leute', '', $leute_max_rights);
	if($leute_max_rights > 1) {
		//Don't show changes done by root to other users
		$where_add = ($_SESSION["ses_userid"] != ko_get_root_id()) ? " AND user_id != '".ko_get_root_id()."' " : "";
		$logs = db_select_data('ko_log', "WHERE `type` = 'edit_person' AND `date` >= '".$_SESSION['last_login']."' ".$where_add, '*', 'ORDER BY date DESC', 'LIMIT 0,30');
		if(sizeof($logs) > 0) {
			$p_counter = 0;
			$found = TRUE;
			ko_get_logins($logins);
			$lids = array();
			foreach($logs as $logid => $log) {
				$logs[$logid]['_leute_id'] = $lids[] = (int)substr($log['comment'], 0, strpos($log['comment'], ' '));
			}
			ko_get_leute($people, " AND `id` IN ('".implode("','", $lids)."')");
			foreach($logs as $log) {
				$tpl_person[$p_counter]['user'] = $logins[$log['user_id']]['login'];
				$tpl_person[$p_counter]['log'] = ko_html(substr($log['comment'], strpos($log['comment'], ':')+2));
				//Name of the edited person
				$person = $people[$log['_leute_id']];
				if(isset($person['firm']) && $person['firm']) {
					$tpl_person[$p_counter]['name']  = $person['firm'].' '.$person['department'];
					$tpl_person[$p_counter]['link'] = 'leute/index.php?action=set_idfilter&amp;id='.$person['id'];
					if($person['nachname']) {
						$tpl_person[$p_counter]['name'] .= ': '.$person['vorname'].' '.$person['nachname'];
						$tpl_person[$p_counter]['link'] .= '&amp;ln='.urlencode($person['nachname']).'&amp;fn='.urlencode($person['vorname']);
					}
				} else {
					$tpl_person[$p_counter]['name'] = $person['vorname'].' '.$person['nachname'];
					$tpl_person[$p_counter]['link'] = 'leute/index.php?action=set_idfilter&amp;id='.$person['id'];
				}
				$p_counter++;
			}
			$smarty->assign('today_leute_change', $tpl_person);
		}//if(sizeof(logs) > 0)
	}//if(l_edit)
	$smarty->assign('show_leute_change', $found);








	//TODO: Rota: Show own scheduling and maybe open schedulings for team leaders



	$smarty->assign("tpl_fm_pos", $pos);
	$smarty->assign("tpl_fm_title", getLL("fm_name_today") );
	$smarty->display("ko_fm_today.tpl");

}//ko_fm_today()




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

	foreach($news_ as $n_i => $n) {
		$news[$n_i]["text"] = nl2br(ko_html($n["text"]));
		$news[$n_i]["subtitle"] = nl2br(ko_html($n["subtitle"]));
		$news[$n_i]["title"] = nl2br(ko_html($n["title"]));
		$news[$n_i]["link"] = ko_html($n["link"]);
		$news[$n_i]["author"] = ko_html($n["author"]);
		if($n['cdate'] != '0000-00-00') $news[$n_i]['cdate'] = sql2datum($n['cdate']);
		$news[$n_i]["id"] = $n["id"];
	}
	$smarty->assign("label_link", getLL("fm_news_link"));
	$smarty->assign("tpl_fm_title", "News");
	$smarty->assign("tpl_news", $news);
	$smarty->assign("tpl_fm_pos", $pos);

	$smarty->display("ko_fm_news.tpl");
}//ko_fm_news()




/*
 * Adress-Aenderung
 */
function ko_fm_adresse($uid) {
	global $ko_path, $smarty, $access;

	//Don't show mutation form for users with global access 2 or more for the address module (as they can make changes to the addresses directly)
	$rights_all = ko_get_access_all('leute_admin', $uid);
	if($rights_all >= 2) return;

	$smarty->assign("label_name", getLL("fm_aa_name"));
	$smarty->assign("label_firstname", getLL("fm_aa_firstname"));
	$smarty->assign("label_ok", getLL("OK"));
	$smarty->assign("label_reset", getLL("reset"));
	$smarty->assign("title_edit", getLL("fm_aa_comment_edit"));
	$smarty->assign("title_new", getLL("fm_aa_comment_new"));
	$smarty->assign("label_comment", getLL("fm_aa_comment"));

	$smarty->assign("tpl_aa_show", "name");
	$smarty->assign("tpl_fm_title", getLL("fm_aa_title"));
	$smarty->display("ko_fm_adressaenderung.tpl");
}//ko_fm_adresse()



/*
 * Fileshare
 */
function ko_fm_fileshare($uid, $pos) {
	global $ko_path, $smarty, $FILESHARE_FOLDER;

	//Berechtigungen checken
	if(!ko_module_installed("fileshare")) return FALSE;

	//FM-Titel
	$smarty->assign("tpl_fm_title", getLL("fm_fileshare_title"));
	$smarty->display("ko_fm_header.tpl");

	//Durch alle Ordner gehen
	$found = FALSE;
	$code = "";
	$folders = ko_fileshare_get_folders($_SESSION["ses_userid"]);
	foreach($folders as $folder) {
		$shares = ko_get_shares(" AND `parent` = '".$folder["id"]."' AND `c_date` >= '".$_SESSION["last_login"]."'", "c_date DESC");
		if(sizeof($shares) > 0) {
			$found = TRUE;
			if($folder["user"] != $_SESSION["ses_userid"]) {
				$code .= '&nbsp;&nbsp;<img src="images/tv_inbox_shared.gif" border="0" alt="'.getLL("fileshare_folder").'" title="'.$folder["name"].'" />&nbsp;';
			} else {
				$code .= '&nbsp;&nbsp;<img src="images/tv_inbox.gif" border="0" alt="'.getLL("fileshare_folder").'" title="'.$folder["name"].'" />&nbsp;';
			}
			$code .= '<a href="fileshare/index.php?action=show_folder&id='.$folder["id"].'">'.$folder["name"].'</a><br />';

		  //Mitte: Liste der einzelnen Dateien anzeigen
			if($pos == "m") {
				$code .= '<ul type="square">';
				foreach($shares as $share) {
					if(file_exists($FILESHARE_FOLDER.$share["id"])) $file_ok = TRUE;
					else $file_ok = FALSE;

					$code .= "<li>";
					if($file_ok) $code .= '<a href="fileshare/file.php?di='.$share["id"].'&amp;ei='.$share["id"].'" target="_blank">';
					$code .= $share["filename"]." (".ko_nice_size($share["filesize"]).")";
					if($file_ok) $code .= '</a>';
					$code .= "</li>";
				}
				$code .= "</ul>";
			}
		}//if(sizeof(shares) > 0)
	}//foreach(folders as folder)

	if($found) {
		print '<p style="font-weight:600;padding:1px;margin:2px;">'.getLL("fm_fileshare_new").'</p>';
		print $code;
	} else {
		print getLL("fm_fileshare_none");
	}
	
	$smarty->display("ko_fm_footer.tpl");
}//ko_fm_fileshare()

?>
