<?php
/*******************************************************************************
*
*    OpenKool - Online church organization tool
*
*    Copyright © 2003-2015 Renzo Lauper (renzo@churchtool.org)
*    Copyright © 2019      Daniel Lerch
*
*    This program is free software; you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation; either version 2 of the License, or
*    (at your option) any later version.
*
*    This program is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*******************************************************************************/


require_once($BASE_PATH.'inc/class.kOOL_listview.php');


/**
  * Jahres-Kalender
	*/
function ko_res_cal_jahr($num=6, $start=1, $output=TRUE) {
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
    $cal_month[] = array("code" => (str_to_2($ii)."-".$jj), "name" => strftime("%B", mktime(1,1,1, $ii, 1, $jj)));
  }
  $next = add2date("$t.$m.$j", "monat", $num);
  $prev = add2date("$t.$m.$j", "monat", "-$num");
  $today_start = 1;
  while( ((int)$today_start+$num-1) < $h_monat) {
    $today_start += $num;
  }

  $end_code = $next[2].str_to_2($next[1]).str_to_2($next[0]);

	$tpl_prev_link = "javascript:sendReq('../reservation/inc/ajax.php', 'action,set_year,set_start,sesid', 'setjahr,".$prev[2].",".(int)$prev[1].",".session_id()."', do_element);";
	$tpl_next_link = "javascript:sendReq('../reservation/inc/ajax.php', 'action,set_year,set_start,sesid', 'setjahr,".$next[2].",".(int)$next[1].",".session_id()."', do_element);";
	$tpl_today_link = "javascript:sendReq('../reservation/inc/ajax.php', 'action,set_year,set_start,sesid', 'setjahr,$h_jahr,$today_start,".session_id()."', do_element);";


  $smarty->assign("tpl_cal_month", $cal_month);
  $smarty->assign("tpl_cal_month_width", (int)(90/$num));
  $smarty->assign("tpl_cal_titel", strftime("%Y", mktime(1,1,1, 1,1,$j)));

  $smarty->assign("tpl_prev_link", $tpl_prev_link);
  $smarty->assign("tpl_next_link", $tpl_next_link);
  $smarty->assign("tpl_today_link", $tpl_today_link);


	//Reservations-Gruppen Namen
	ko_get_resitems($_gruppen);
	$gruppen = array();
	foreach($_gruppen as $g) {
		if($access['reservation'][$g['id']] > 0 && in_array($g['id'], $_SESSION['show_items'])) $gruppen[$g['id']] = $g;
	}
	foreach($gruppen as $g_i => $g) {
    $day[$g_i]["name"] = ko_html($g["name"]);
    $day[$g_i]["tip"] = ko_html2($g["name"]);
  }

	//Personen-Daten anzeigen oder nicht
	$show_persondata = ($_SESSION["ses_userid"] != ko_get_guest_id() || ko_get_setting("res_show_persondata"));

	//Permanente Filter anwenden
	$z_where = "";
	$perm_filter_start = ko_get_setting("res_perm_filter_start");
	$perm_filter_ende  = ko_get_setting("res_perm_filter_ende");

	$forceGlobalTimeFilter = ko_get_force_global_time_filter("reservation", $_SESSION['ses_userid']);
	if($forceGlobalTimeFilter || $access['reservation']['MAX'] < 4) {
		if($perm_filter_start != "") {
			$z_where .= " AND startdatum >= '".$perm_filter_start."' ";
		}
		if($perm_filter_ende != "") {
			$z_where .= " AND startdatum <= '".$perm_filter_ende."' ";
		}
	}

	//Set filters from KOTA
	$kota_where = kota_apply_filter('ko_reservation');
	if($kota_where != '') $z_where .= " AND ($kota_where) ";

	//Reservationen einfüllen
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
    ko_get_res_by_date($t, $m, $j, $dates, TRUE, "res", $z_where);
    if(sizeof($dates) > 0) {
			foreach($gruppen as $g_i => $g) {
				$day_text = "";
				foreach($dates as $date) {
					if((int)$date["item_id"] == (int)$g_i) {

						$desc = "";
						if($date["startzeit"] == "00:00:00" && $date["endzeit"] == "00:00:00") {
              $desc .= getLL("time_all_day");
						} else if($date["startdatum"] != $date["enddatum"]) {  //Mehrtägige Termine
              if($t == mb_substr($date["startdatum"], 8, 2) && $m == mb_substr($date["startdatum"], 5, 2))
                $desc .= getLL("time_from")." ".mb_substr($date["startzeit"], 0, -3);
              else if($t == mb_substr($date["enddatum"], 8, 2) && $m == mb_substr($date["enddatum"], 5, 2))
                $desc .= getLL("time_to")." ".mb_substr($date["endzeit"], 0, -3);
              else
                $desc .= getLL("time_all_day");
            } else {  //eintägig
              $desc .= ($date["startzeit"] != "00:00:00" ? mb_substr($date["startzeit"], 0, -3) : "") . (($date["endzeit"] != "00:00:00") ? ("-" . mb_substr($date["endzeit"], 0, -3)) : "");
            }
            $day_text .= ($desc != "") ? "<br /><b>- ".$desc."</b>" : "<br />";


						if($show_persondata) $day_text .= ": ".($date["name"] ? (ko_html2($date["name"]).", ") : "");
						$day_text .= ($date["zweck"] ? "&quot;".ko_html2($date["zweck"])."&quot;" : "");
          }
	      }//foreach(dates)
				if($day_text) {
					$day[(int)$g_i]["events"][$m]["days"][$t]["tip"]  = "<b>".strftime($GLOBALS["DATETIME"]["DdMY"], mktime(1,1,1, $m,$t,$j))."</b><br />";
					$day[(int)$g_i]["events"][$m]["days"][$t]["tip"] .= ko_html2($g["name"]).$day_text;
          $day[(int)$g_i]["events"][$m]["days"][$t]["style"] = "background:#".$g["farbe"];
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
  }

	//LL-Values
	$smarty->assign("label_cal_year", getLL("res_cal_year"));
	$smarty->assign("label_cal_month", getLL("res_cal_month"));
	$smarty->assign("label_cal_week", getLL("res_cal_week"));
	$smarty->assign("label_item", getLL("res_cal_object"));
	$smarty->assign("label_today", getLL("time_today"));

	//PDF-Export-Link anzeigen:
	$button_code  = '&nbsp;<a href="" onclick="sendReq(\'../reservation/inc/ajax.php\', \'action\', \'pdfcalendar\', show_box); return false;">';
	$button_code .= '<img src="'.$ko_path.'images/create_pdf.png" border="0" />&nbsp;'.getLL("res_list_footer_pdf_label").'</a>';
	$button_code .= '<span name="res_pdf_link" id="res_pdf_link">&nbsp;</span>';
	$list_footer = $smarty->get_template_vars('list_footer');
	$list_footer[] = array("label" => "", "button" => $button_code);
	$smarty->assign("show_list_footer", TRUE);
	$smarty->assign("list_footer", $list_footer);

	$smarty->assign('warning', kota_filter_get_warntext('ko_reservation'));

	$smarty->assign("tpl_day", $day);
	if($output) {
	  $smarty->display('ko_cal_jahr.tpl');
	} else {
	  print $smarty->fetch('ko_cal_jahr.tpl');
	}
}//ko_res_cal_jahr()





function ko_list_reservations($output=TRUE) {
	global $smarty, $access;

	if($access['reservation']['MAX'] < 1) return;
	apply_res_filter($z_where, $z_limit);

	$rows = db_get_count('ko_reservation', 'id', $z_where);
	if($_SESSION['show_start'] > $rows) {
		$_SESSION['show_start'] = 1;
		$z_limit = 'LIMIT '.($_SESSION['show_start']-1).', '.$_SESSION['show_limit'];
	}
	ko_get_reservationen($es, $z_where, $z_limit, 'res');


	//Build fake access array for each reservation not just by item_id
	$res_access = array();
	if($access['reservation']['ALL'] > 3) {
		$res_access['ALL'] = $access['reservation']['ALL'];
	} else {
		foreach($es as $e) {
			//Edit and delete
			if($access['reservation'][$e['item_id']] > 3 || ($e['user_id'] == $_SESSION['ses_userid'] && $_SESSION['ses_userid'] != ko_get_guest_id() && $access['reservation'][$e['item_id']] > 2)) {
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


	$list->setActions(array('edit' => array('action' => 'edit_reservation'),
													'delete' => array('action' => 'delete_res',
																						'confirm' => TRUE,
																						'additional_row_js' => "if(###SERIE_ID### > 0) {c2 = confirm('".getLL("res_delete_serie_confirm")."');set_hidden_value('mod_confirm', c2);}"),
																						)
										);
	$list->setSort(TRUE, 'setsort', $_SESSION['sort_item'], $_SESSION['sort_item_order']);
	$list->setStats($rows);

	//Footer
	$list_footer = $smarty->get_template_vars('list_footer');
	if($access['reservation']['MAX'] > 3) {
		$list_footer[] = array('label' => getLL('res_list_footer_del_label'), 'button' => '<input type="submit" onclick="c=confirm('."'".getLL("res_list_footer_del_button_confirm")."'".');if(!c) return false;set_action(\'del_selected\');" value="'.getLL("res_list_footer_del_button").'" />');

	}
	$list->setFooter($list_footer);

	$list->setWarning(kota_filter_get_warntext('ko_reservation'));


	//Output the list
	if($output) {
		$list->render($es);
	} else {
		print $list->render($es);
	}

}//ko_list_reservations()





//Type ist entweder "res" für eine Liste der normalen Reservationen
// oder "mod" für eine Liste der zu moderierenden Reservationen
function ko_show_res_liste($type="res", $output=TRUE) {
	global $smarty, $DATETIME;
	global $access;
		
	if($access['reservation']['MAX'] < 1) return;

	//Set SQL filter for res and mod
	if($type=='res') {
		//Call new function to render list of reservations using KOTA listview
		ko_list_reservations();
		return;
		apply_res_filter($z_where, $z_limit);
	}
	else if($type=='mod') {
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
	}

	//Personen-Daten anzeigen oder nicht
	$show_persondata = ($_SESSION["ses_userid"] != ko_get_guest_id() || ko_get_setting("res_show_persondata"));

	$rows = db_get_count( (($type=="res")?"ko_reservation":"ko_reservation_mod"), "id", $z_where);
	unset($es);
	if($_SESSION['show_start'] > $rows) {
		$_SESSION['show_start'] = 1;
		$z_limit = 'LIMIT '.($_SESSION['show_start']-1).', '.$_SESSION['show_limit'];
	}
	ko_get_reservationen($es, $z_where, $z_limit, $type);

	//Statistik über Suchergebnisse und Anzeige
	if($type == 'mod') {
	  $stats_end = $rows;
	} else {
	  $stats_end = ($_SESSION["show_limit"]+$_SESSION["show_start"]-1 > $rows) ? $rows : ($_SESSION["show_limit"]+$_SESSION["show_start"]-1);
	}
  $smarty->assign('tpl_stats', $_SESSION["show_start"]." - ".$stats_end." ".getLL("list_oftotal")." ".$rows);

	//Links für Prev und Next-Page vorbereiten
	if($_SESSION["show_start"] > 1 && $type != 'mod') {
		$smarty->assign("tpl_prevlink_link", "javascript:sendReq('../reservation/inc/ajax.php', 'action,set_start,sesid', 'setstart,".(($_SESSION["show_start"]-$_SESSION["show_limit"] < 1) ? 1 : ($_SESSION["show_start"]-$_SESSION["show_limit"])).",".session_id()."', do_element);");
	} else {
    $smarty->assign('tpl_prevlink_link', '');
  }
  if(($_SESSION["show_start"]+$_SESSION["show_limit"]-1) < $rows && $type != 'mod') {
		$smarty->assign("tpl_nextlink_link", "javascript:sendReq('../reservation/inc/ajax.php', 'action,set_start,sesid', 'setstart,".($_SESSION["show_limit"]+$_SESSION["show_start"]).",".session_id()."', do_element);");
  } else {
    $smarty->assign('tpl_nextlink_link', '');
  }
	if($type == 'mod') {
		$smarty->assign('hide_listlimiticons', TRUE);
	} else {
		$smarty->assign('limitM', $_SESSION['show_limit'] >= 100 ? $_SESSION['show_limit']-50 : max(10, $_SESSION['show_limit']-10));
		$smarty->assign('limitP', $_SESSION['show_limit'] >= 50 ? $_SESSION['show_limit']+50 : $_SESSION['show_limit']+10);
	}


	//Header-Daten
	if($show_persondata) {
		$num_cols = 6;
		$show_cols = array(getLL('kota_listview_ko_reservation_item_id'), getLL('kota_listview_ko_reservation_startdatum'), getLL('kota_listview_ko_reservation_startzeit'), getLL('kota_listview_ko_reservation_zweck'), getLL('kota_listview_ko_reservation_name'), getLL('kota_listview_ko_reservation_comments'));
		$show_sort = array('item_id', 'startdatum', 'startzeit', 'zweck', 'name', 'comments');
	} else {
		$num_cols = 4;
		$show_cols = array(getLL("kota_listview_ko_reservation_item_id"), getLL("kota_listview_ko_reservation_startdatum"), getLL("kota_listview_ko_reservation_startzeit"), getLL("kota_listview_ko_reservation_zweck"));
		$show_sort = array("item_id", "startdatum", "startzeit", "zweck");
	}
	for($i=0; $i<$num_cols;$i++) {
		$tpl_table_header[$i]["sort"] = $show_sort[$i];
		$tpl_table_header[$i]["name"] = $show_cols[$i];
  }
  $smarty->assign("tpl_table_header", $tpl_table_header);
	$smarty->assign("tpl_show_3cols", TRUE);
  $smarty->assign("sort", array("show" => TRUE,
																"action" => "setsort",
																"akt" => $_SESSION["sort_item"],
																"akt_order" => $_SESSION["sort_item_order"])
	);
	$smarty->assign("module", "reservation");
	$smarty->assign("sesid", session_id());


	//Multiedit-Spalten definieren
	if(($type == 'res' && $access['reservation']['MAX'] > 3) || ($type == 'mod' && $access['reservation']['MAX'] > 4)) {
		$smarty->assign("tpl_show_editrow", TRUE);
		if($show_persondata) {
			$edit_columns = array('item_id', 'startdatum,enddatum', 'startzeit,endzeit', 'zweck,comments', 'name,email,telefon', 'comments');
		} else {
			$edit_columns = array('item_id', 'startdatum,enddatum', 'startzeit,endzeit', 'zweck,comments');
		}
		$smarty->assign("tpl_edit_columns", $edit_columns);
	} else {
		$smarty->assign("tpl_show_editrow", FALSE);
	}


	$guest_id = ko_get_guest_id();
	if(is_array($es)) {
		foreach($es as $e_i => $e) {

			if($type=="mod") {
				//Auf Doppelbelegung testen:
				if(!ko_res_check_double($e["item_id"], $e["startdatum"], $e["enddatum"], $e["startzeit"], $e["endzeit"], $error_txt)) {
					$double = TRUE;
					$double_error = $error_txt;
				} else {
					$double = FALSE;
				}
			}

			//Checkbox
			$tpl_list_data[$e_i]["show_checkbox"] = TRUE;

			//Für Reservationen Edit- und Delete-Button ausgeben
			if($type=="res") {
				//Edit-Button
				if($access['reservation'][$e['item_id']] > 3 || ($e['user_id'] == $_SESSION['ses_userid'] && $_SESSION['ses_userid'] != ko_get_guest_id() && $access['reservation'][$e['item_id']] > 2)) {
					$tpl_list_data[$e_i]["show_edit_button"] = TRUE;
					$tpl_list_data[$e_i]["alt_edit"] = getLL("res_edit_reservation");

					$tpl_list_data[$e_i]["onclick_edit"] = "javascript:set_action('edit_reservation');set_hidden_value('id', '$e_i');this.submit";
				}

				//Delete-Button
				if($access['reservation'][$e['item_id']] > 3 || ($e['user_id'] == $_SESSION['ses_userid'] && $_SESSION['ses_userid'] != ko_get_guest_id() && $access['reservation'][$e['item_id']] > 2)) {
					$tpl_list_data[$e_i]["show_delete_button"] = TRUE;
					$tpl_list_data[$e_i]["alt_delete"] = getLL("res_delete_res");
					$del_link  = "javascript:c = confirm('".getLL("res_delete_res_confirm")."');if(!c) return false;";
					if($e["serie_id"]) $del_link .= "c2 = confirm('".getLL("res_delete_serie_confirm")."');set_hidden_value('mod_confirm', c2);";
					$del_link .= "set_action('delete_res');set_hidden_value('id', '$e_i');";
					$tpl_list_data[$e_i]["onclick_delete"] = $del_link;
				}

				//Mark sundays
				if(ko_get_userpref($_SESSION['ses_userid'], "res_mark_sunday") && strftime("%w", strtotime($e["startdatum"])) == 0) {
					$tpl_list_data[$e_i]["rowclass"] = "ko_list_sunday";
				} else {
					$tpl_list_data[$e_i]["rowclass"] = "";
				}


			} elseif($type=="mod") { //Für Mod-Liste einen Bestätigungs- und einen Delete-Button ausgeben
				//Check-Button
				if($access['reservation'][$e['item_id']] > 4 && !$double) {  //Bei Doppelbelegung kein Check-Button anzeigen
					$tpl_list_data[$e_i]["show_check_button"] = TRUE;
					$tpl_list_data[$e_i]["alt_edit"] = getLL("res_mod_confirm");
					$tpl_list_data[$e_i]["onclick_edit"] = "javascript:c1 = confirm('".getLL("res_mod_confirm_confirm")."');if(!c1) return false;c = confirm('".getLL("res_mod_confirm_confirm2")."');set_hidden_value('mod_confirm', c);set_action('res_mod_approve');set_hidden_value('id', '$e_i');this.submit";
				}

				//Delete-Button
				if($access['reservation'][$e['item_id']] > 4 || ($_SESSION["ses_userid"] != ko_get_guest_id() && $e["user_id"] == $_SESSION["ses_userid"])) {
					$tpl_list_data[$e_i]["show_delete_button"] = TRUE;
					$tpl_list_data[$e_i]["alt_delete"] = getLL("res_mod_decline");
					$tpl_list_data[$e_i]["onclick_delete"] = "javascript:c1 = confirm('".getLL("res_mod_decline_confirm")."');if(!c1) return false;";
					if($access['reservation'][$e['item_id']] > 4) $tpl_list_data[$e_i]["onclick_delete"] .= "c = confirm('".getLL("res_mod_decline_confirm2")."');set_hidden_value('mod_confirm', c);";
					$tpl_list_data[$e_i]["onclick_delete"] .= "set_action('res_mod_delete');set_hidden_value('id', '$e_i');";
				}

			}//if(type==res)..elseif(type==mod)


			//Index
	    $tpl_list_data[$e_i]["id"] = $e_i;


			//Objekt
			if(!$double) {  //Falls keine Doppelbelegung vorliegt
				$tpl_list_data[$e_i][0] = ko_html($e["item_name"]);
			} else {  //Bei Doppelbelegung Meldung anzeigen
				$tpl_list_data[$e_i][0]  = '<a href="#" onmouseover="tooltip.show(\'&lt;b&gt;'.getLL("res_collision_text").'&lt;/b&gt;&lt;br /&gt;&lt;br /&gt;';
				$tpl_list_data[$e_i][0] .= ko_html2($double_error).'\')" OnMouseOut="tooltip.hide()">';
				$tpl_list_data[$e_i][0] .= "<b> ! ".ko_html($e["item_name"])." ! </b></a>";;
			}


			//Datum
			if($e["startdatum"] != $e["enddatum"]) {
				$datum = strftime($DATETIME["ddmy"], strtotime($e["startdatum"])) . "-" . strftime($DATETIME["ddmy"], strtotime($e["enddatum"]));
			} else {
				$datum = strftime($DATETIME["ddmy"], strtotime($e["startdatum"]));
			}
			$tpl_list_data[$e_i][1] = $datum;


			//Zeit
			if($e["startzeit"] == "00:00:00") {
				$zeit = getLL("time_all_day");
			} else if($e["endzeit"] != "" && $e["endzeit"] != "00:00:00") {
				$zeit = mb_substr($e["startzeit"], 0, -3) . " - " . mb_substr($e["endzeit"], 0, -3);
			} else {
				$zeit = mb_substr($e["startzeit"], 0, -3);
			}
			$tpl_list_data[$e_i][2] = $zeit;


			//Zweck
			$tpl_list_data[$e_i][3] = ko_html($e["zweck"]);



			//Wer
			if($show_persondata) {
				if($e["telefon"] || $e["email"]) {
					$tpl_list_data[$e_i][4] = "<a href=\"#\" onmouseover=\"return tooltip.show('".format_email(ko_html2($e["email"]))."&lt;br /&gt;".ko_html2($e["telefon"])."');\" onmouseout=\"return tooltip.hide();\">".ko_html($e["name"])."</a>";
				} else {
					$tpl_list_data[$e_i][4] = ko_html($e["name"]);
				}
			}


			//Comments
			$tpl_list_data[$e_i][5] = ko_html($e['comments']);


		}//foreach(es)
	}//if..else(is_array(es))

	if($show_persondata) {
		$smarty->assign('tpl_list_cols', array(0, 1, 2, 3, 4, 5));
	} else {
		$smarty->assign('tpl_list_cols', array(0, 1, 2, 3));
	}

	//Footer
	$list_footer = $smarty->get_template_vars('list_footer');
	if($type == 'res' && $access['reservation']['MAX'] > 3) {
		$list_footer[] = array("label" => getLL("res_list_footer_del_label"),
													 "button" => '<input type="submit" onclick="c=confirm('."'".getLL("res_list_footer_del_button_confirm")."'".');if(!c) return false;set_action(\'del_selected\');" value="'.getLL("res_list_footer_del_button").'" />');
		$smarty->assign("show_list_footer", TRUE);
		$smarty->assign("list_footer", $list_footer);
	}
	else if($type == "mod") {
		if($access['reservation']['MAX'] > 1) {
			$list_footer[] = array("label" => getLL("res_list_footer_del_label"),
														 "button" => '<input type="submit" onclick="c1 = confirm(\''.getLL("res_mod_decline_confirm").'\');if(!c1) return false;'.($access['reservation']['MAX'] > 4 ? 'c = confirm(\''.getLL("res_mod_decline_confirm2").'\');set_hidden_value(\'mod_confirm\', c);' : '').'set_action(\'res_mod_delete_multi\');" value="'.getLL("res_list_footer_del_button").'" />');
		}
		if($access['reservation']['MAX'] > 4) {
			$list_footer[] = array("label" => getLL("res_list_footer_confirm_label"),
														 "button" => '<input type="submit" onclick="c=confirm('."'".getLL("res_mod_confirm_confirm2")."'".');set_hidden_value(\'mod_confirm\', c);set_action(\'res_mod_approve_multi\');" value="'.getLL("ok").'" />');
		}
		$smarty->assign("show_list_footer", TRUE);
		$smarty->assign("list_footer", $list_footer);
	}

	if($type == "mod") {
		$smarty->assign("help", ko_get_help("reservation", "show_mod_res"));
		$smarty->assign('tpl_list_title', getLL("res_mod_list_title"));
	} else {
		$smarty->assign('tpl_list_title', getLL("res_list_title"));
	}

	$smarty->assign('tpl_list_data', $tpl_list_data);
	if($output) {
		$smarty->display('ko_list.tpl');
	} else {
		print $smarty->fetch('ko_list.tpl');
	}
}//ko_show_res_liste()




function ko_show_items_liste($output=TRUE) {
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
	$list->setSort(TRUE, 'setsortresgroups', $_SESSION['sort_group'], $_SESSION['sort_group_order']);
	$list->setStats($rows);
	
	//Output the list
	if($output) {
		$list->render($res);
	} else {
		print $list->render($res);
	}
}//ko_show_items_liste()





/**
  * Erstellt das Formular zum Bearbeiten und Hinzufügen von Reservationen
	*/
function ko_formular_reservation($mode, $id, $data=array()) {
	global $smarty, $KOTA;
	global $access;

	//Falls eine Reservation editiert werden soll, diese auslesen und die Felder mit seinen Details füllen
	if($mode=="edit" && $id != 0) {
		if($access['reservation']['MAX'] < 2) return;

	  ko_get_res_by_id($id, $r_);
	  $r = $r_[$id];

		//Info über Reservation für Moderator
		if($access['reservation']['MAX'] > 3) {
			ko_get_login($r['user_id'], $res_login);
			$res_info  = getLL('res_info_cdate').': <b>'.sqldatetime2datum($r['cdate']).'</b>';
			$res_info .= '<br />'.getLL('res_info_user').': <b>'.$res_login['login'].' ('.$r['user_id'].')</b>';
			$res_info .= '<br />'.getLL('res_info_mdate').': <b>'.sqldatetime2datum($r['last_change']).'</b>';
			//$res_info .= '<br />'.getLL('res_info_code').': <b>'.$r['code'].'</b>';
		}
	}//if(mode==edit && id)
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
			$p = ko_get_logged_in_person();
			if($p['id'] || $p['email']) {
				kota_assign_values(
					'ko_reservation',
					array(
						'name' => $p['vorname'].' '.$p['nachname'],
						'email' => $p['email'],
						'telefon' => ($p['telg']?$p['telg']:$p['telp'])
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
	$output = NULL; $monday = date_find_last_monday(date("Y-m-d"));
	for($i=0; $i<7; $i++) $output[] = strftime("%A", strtotime(add2date($monday, "tag", $i, TRUE)));
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

	$repeat_stop  = '<select name="sel_bis_tag" size="0">'.$sel_day_code.'</select>&nbsp;&nbsp;';
	$repeat_stop .= '<select name="sel_bis_monat" size="0">'.$sel_month_code.'</select>&nbsp;&nbsp;';
	$repeat_stop .= '<select name="sel_bis_jahr" size="0">'.$sel_year_code.'</select>';
	$repeat_stop .= '<br />'.getLL("daten_repeat_or").'<br /><input type="text" name="txt_num_repeats" size="4" maxlength="3" onkeyup="repeat_disable(this.value);" />&nbsp;'.getLL("daten_repeat_iterations");



	//Formular aufbauen
	$mandatory = explode(",", ko_get_setting("res_mandatory"));
	$gc = 0;

	//add mandatory-flags
	foreach($KOTA["ko_reservation"] as $key => $kota_field) {
		if(in_array($key, $mandatory)) $KOTA["ko_reservation"][$key]["form"]["desc"] = getLL("kota_ko_reservation_".$key)." *";
	}

	//allow multiple objects to be reserved at once
	if($mode == "neu" && ($_SESSION["ses_userid"] != ko_get_guest_id() || ko_get_setting("res_allow_multires_for_guest")) ) {
		$KOTA["ko_reservation"]["item_id"]["form"]["type"] = "dyndoubleselect";
		$KOTA["ko_reservation"]["item_id"]["form"]["js_func_add"] = "resgroup_doubleselect_add";
	}
	$KOTA["ko_reservation"]["item_id"]["form"]["params"] = 'size="5" onchange="javascript:changeResItem(this.value);"';

	//get first part of form from kota
	$group = ko_multiedit_formular("ko_reservation", NULL, $id, "", "", TRUE);
	$group[$gc]["titel"] = "";
	$rowcounter = sizeof($group[$gc]["row"])+1;
	

	//Wiederholungen (nur bei Neu)
	if($mode == "neu") {
		$group[++$gc] = array("titel" => getLL("daten_repeat"), "state" => "closed", "colspan" => 'colspan="2"');
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
	if($mode == "edit" && $r["serie_id"]) {
		$group[$gc]["row"][$rowcounter++]["inputs"][0] = array("type" => "   ");
		$group[$gc]["row"][$rowcounter++]["inputs"][0] = array("desc" => getLL("res_serie_title"),
																 "type" => "checkbox",
																 "name" => "chk_serie",
																 "value" => "1",
																 "desc2" => getLL("res_serie_apply")." (".db_get_count("ko_reservation", "id", "AND `serie_id` = '".$r["serie_id"]."'").")",
																 "colspan" => 'colspan="2"',
																 );
	}
	if($access['reservation']['MAX'] > 3 && $mode == "edit") {
		$group[$gc]["row"][$rowcounter++]["inputs"][0] = array("type" => "   ");
		$group[$gc]["row"][$rowcounter++]["inputs"][0] = array("desc" => getLL("res_info_title"),
																 "type" => "html",
																 "value" => $res_info,
																 "colspan" => 'colspan="2"',
																 );
	}




	$smarty->assign("tpl_titel", ( ($mode == "neu") ? getLL("res_new_res") : getLL("res_edit_reservation")) );
  $smarty->assign("tpl_submit_value", getLL("save"));
  $smarty->assign("tpl_id", $id);
  $smarty->assign("tpl_action", ( ($mode == "neu") ? "submit_neue_reservation" : "submit_edit_reservation") );
	//Add button "save as new"
	if($mode == 'edit') {
		$smarty->assign("tpl_submit_as_new", getLL('res_submit_as_new'));
		$smarty->assign("tpl_action_as_new", "submit_neue_reservation");
	}
	$cancel = $_SESSION['show_back'] ? $_SESSION['show_back'] : ko_get_userpref($_SESSION["ses_userid"], "default_view_reservation");
	if(!$cancel) $cancel = "show_cal_monat";
  $smarty->assign("tpl_cancel", $cancel);
  $smarty->assign("tpl_groups", $group);

	$smarty->assign("help", ko_get_help("reservation", "neue_reservation"));

	$smarty->display("ko_formular.tpl");
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
	}


	$form_data["title"] = $mode == "neu" ? getLL("res_new_object") : getLL("res_edit_object");
  $form_data["submit_value"] = getLL("save");
  $form_data["action"] = $mode == "neu" ? "submit_new_item" : "submit_edit_item";
  $form_data["cancel"] = "list_items";

	ko_multiedit_formular("ko_resitem", NULL, $id, "", $form_data);
}//ko_formular_item()




/**
  * Stores a new reservation to be moderated
	*/
function ko_res_store_moderation($data) {
	$mod_txt = array();
	$items = db_select_data("ko_resitem", "WHERE 1=1", "*");

	foreach($data as $res) {
		$resitem = $items[$res["item_id"]];
		//add other data
		$res["code"] = mb_substr(md5($res["name"].microtime()), 2, 8);
		$res["cdate"] = strftime("%Y-%m-%d %H:%M:%S", time());
		$res["last_change"] = strftime("%Y-%m-%d %H:%M:%S", time());
		$res["linked_items"] = $resitem["linked_items"];
		if(!$res["user_id"]) $res["user_id"] = $_SESSION["ses_userid"];

		//Store moderation
		$new_id = db_insert_data("ko_reservation_mod", $res);

		//Add log entry
		$log_data = $res;
		$log_data['id'] = $new_id;
		ko_log_diff('new_mod_res', $log_data);

		//Prepare email text for moderators
		if($resitem["moderation"] == 2) {
			$txt = ko_get_res_infotext($res);
			//Add links to confirm and delete reservation
			$txt .= "\n".ko_get_mod_links($res, $new_id);
			$mod_txt[$resitem["gruppen_id"]] .= $txt."\n\n";
		}
	}//foreach(data as res)


	//Inform all moderators for the different resgroups
	$done = array();
	foreach($mod_txt as $gid => $txt) {
		if(!$txt || !$gid) continue;
		$mailtext = str_replace("[RES]", "\n\n".$txt, getLL("res_email_mod_text"));
		$mods = ko_get_moderators_by_resgroup($gid);
		foreach($mods as $mod) {
			//Send moderation emails to same email address only once
			if(!$mod['email'] || in_array($mod['email'], $done)) continue;

			//Replace USERHASH in confirm/delete links for each moderator
			$text = str_replace('###USERHASH###', md5($mod['id'].KOOL_ENCRYPTION_KEY), $mailtext);
			ko_send_mail(ko_get_setting("info_email"), $mod["email"], "[kOOL] ".getLL("res_email_mod_subject"), ko_emailtext($text));
			$done[] = $mod['email'];
		}
	}
}//ko_res_store_moderation()



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




/**
  * Stores a new reservation and sends all necessary emails
	*/
function ko_res_store_reservation($data, $send_user_email, &$double_error_txt='') {
	global $access, $BASE_PATH;

	$ids = array();  //Will hold the ids of the new reservations
	$res_txt = "";
	$user_email = "";
	$res3_txt = array();
	$info_txt = array();
	$items = db_select_data("ko_resitem", "WHERE 1=1", "*");
	$double_error_txt = '';

	foreach($data as $res) {
		//Double check (needed as fallback and also e.g. if more than one reservation is given in data,
		// they might overlap internally
		if(FALSE === ko_res_check_double($res['item_id'], $res['startdatum'], $res['enddatum'], $res['startzeit'], $res['endzeit'], $_double_error_txt)) {
			$double_error_txt .= $_double_error_txt;
			continue;
		}

		$resitem = $items[$res["item_id"]];
		//add other data
		if(!$res["code"]) $res["code"] = mb_substr(md5($res["name"].microtime()), 2, 8);
		$res["last_change"] = strftime("%Y-%m-%d %H:%M:%S", time());
		$res["linked_items"] = $resitem["linked_items"];
		if(!$res["cdate"]) $res["cdate"] = strftime("%Y-%m-%d %H:%M:%S", time());
		if(!$res["user_id"]) $res["user_id"] = $_SESSION["ses_userid"];
		$do_event = $res["_event_id"]; unset($res["_event_id"]);

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
		if($send_user_email && !$user_email && $res["email"]) $user_email = $res["email"];

		//Update event, this reservation belongs to
		if($do_event) {
			$event = db_select_data("ko_event", "WHERE `id` = '$do_event'", "*", "", "", TRUE);
			$new_reservationen = $event["reservationen"] ? ($event["reservationen"].",$new_id") : $new_id;
			db_update_data("ko_event", "WHERE `id` = '$do_event'", array("reservationen" => $new_reservationen));
		}

		//Log entry
		$log_message = $new_id.": ".$resitem["name"].", ".$res["startdatum"].", ".$res["zweck"].", ".$res["name"];
		ko_log("new_res", $log_message);

		$ids[] = $new_id;
	}//foreach(data as res)


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
		ko_send_mail(ko_get_setting("info_email"), $user_email, "[kOOL] ".getLL("res_email_confirm_subject"), $mailtext, $file);
	}

	//2: Mail for moderator, if user with access level 4 has made a reservation of a moderated object
	if(sizeof($res3_txt) > 0) {
		foreach($res3_txt as $gid => $txt) {
			$mailtext = sprintf(getLL("res_email_mod3_text"), ("\n\n".$txt));
			$mods = ko_get_moderators_by_resgroup($gid);
			foreach($mods as $mod) {
				//Check user_pref for this moderator
				if(ko_get_userpref($mod["id"], "do_mod_email_for_edit_res") == 1) {
					ko_send_mail(ko_get_setting("info_email"), $mod["email"], "[kOOL] ".getLL("res_email_mod3_subject"), $mailtext);
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
			ko_send_mail(ko_get_setting('info_email'), $rec, getLL('res_email_confirm_subject'), getLL('res_email_confirm_text')."\n\n".$data['leadtext']."\n\n".$data['text']);
		}//foreach(recipients)
	}

	return $ids;
}//ko_res_store_reservation()




/**
  * Displays settings for the reservations
	*/
function ko_res_settings() {
	global $smarty;
	global $access;

	if($access['reservation']['MAX'] < 1 || $_SESSION['ses_userid'] == ko_get_guest_id()) return FALSE;

	//build form
	$gc = 0;
	$rowcounter = 0;
	$frmgroup[$gc]['titel'] = getLL('settings_title_user');

	$frmgroup[$gc]['row'][$rowcounter++]['inputs'][0] = array('desc' => getLL('res_settings_default_view'),
			'type' => 'select',
			'name' => 'sel_reservation',
			'values' => array('liste', 'show_cal_jahr', 'show_cal_monat', 'show_cal_woche', 'show_resource_month', 'show_resource_week', 'show_resource_day'),
			'descs' => array(getLL('submenu_reservation_liste'), getLL('submenu_reservation_cal_year'), getLL('submenu_reservation_cal_month'), getLL('submenu_reservation_cal_week'), getLL('daten_resource_month'), getLL('daten_resource_week'), getLL('daten_resource_day')),
			'value' => ko_html(ko_get_userpref($_SESSION['ses_userid'], 'default_view_reservation'))
			);
	$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('admin_settings_limits_numberof_reservations'),
			'type' => 'text',
			'params' => 'size="10"',
			'name' => 'txt_limit_reservation',
			'value' => ko_html(ko_get_userpref($_SESSION['ses_userid'], 'show_limit_reservation'))
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

	$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('admin_settings_view_weekcal_intermediate_times'),
		'type' => 'text',
		'params' => 'size="30"',
		'name' => 'txt_cal_woche_intermediate_times',
		'value' => ko_html(ko_get_userpref($_SESSION['ses_userid'], 'cal_woche_intermediate_times'))
	);

	$frmgroup[$gc]['row'][$rowcounter++]['inputs'][0] = array('type' => '   ');

	$value = ko_get_userpref($_SESSION['ses_userid'], 'res_monthly_title');
	$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('res_settings_monthly_title'),
			'type' => 'select',
			'name' => 'sel_monthly_title',
			'values' => array('item_id', 'zweck', 'name'),
			'descs' => array(getLL('kota_ko_reservation_item_id'), getLL('kota_ko_reservation_zweck'), getLL('kota_ko_reservation_name')),
			'value' => $value,
			);
	$value = ko_html(ko_get_userpref($_SESSION['ses_userid'], 'res_title_length'));
	$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('res_settings_title_length'),
			'type' => 'text',
			'name' => 'txt_title_length',
			'value' => $value,
			'params' => 'size="10"',
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
	$frmgroup[$gc]['row'][$rowcounter++]['inputs'][0] = array('desc' => getLL('res_settings_export_show_legend'),
			'type' => 'switch',
			'name' => 'sel_export_show_legend',
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
		$frmgroup[$gc]['row'][$rowcounter++]['inputs'][0] = array('desc' => getLL('res_settings_ical_deadline'),
				'type' => 'select',
				'name' => 'sel_ical_deadline',
				'values' => array(0, -1, -2, -3, -6, -12),
				'descs' => array(getLL('res_settings_ical_deadline_0'), getLL('res_settings_ical_deadline_1'), getLL('res_settings_ical_deadline_2'), getLL('res_settings_ical_deadline_3'), getLL('res_settings_ical_deadline_6'), getLL('res_settings_ical_deadline_12')),
				'value' => $value,
				);
	}


	if($access['reservation']['ALL'] > 3) {
		$gc++;
		$frmgroup[$gc]['titel'] = getLL('settings_title_global');

		// mandatory fields
		$mandatoryFields = db_get_columns('ko_reservation');
		$mandatoryFieldsExclude = array('id', 'item_id', 'cdate', 'user_id', 'last_change', 'code', 'serie_id', 'linked_items');
		$mandatory_values = array();
		foreach ($mandatoryFields as $mandatoryField) {
			if (!in_array($mandatoryField['Field'], $mandatoryFieldsExclude)) {
				$mandatory_values[] = $mandatoryField['Field'];
				$mandatory_descs[] = (trim(getLL('kota_ko_reservation_' . $mandatoryField['Field'])) == '' ? $mandatoryField['Field'] : getLL('kota_ko_reservation_' . $mandatoryField['Field']));
			}
		}
		$mandatory_avalues = explode(',', ko_get_setting('res_mandatory'));
		foreach($mandatory_avalues as $v_i => $v) {
			$mandatory_adescs[$v_i] = (trim(getLL('kota_ko_reservation_'.$v)) == '' ? $v : getLL('kota_ko_reservation_'.$v));
		}

		$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('res_settings_mandatory'),
			 'type' => 'checkboxes',
			 'name' => 'sel_mandatory',
			 'values' => $mandatory_values,
			 'descs' => $mandatory_descs,
			 'avalues' => $mandatory_avalues,
			 'avalue' => implode(',', $mandatory_avalues),
			 'size' => '6',
			 );

		$value = ko_html(ko_get_setting('res_send_email'));
		$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('res_settings_send_email'),
				'type' => 'text',
				'name' => 'txt_send_email',
				'value' => $value,
				'params' => 'size="60"',
				);

		$value = ko_get_setting('res_show_persondata');
		$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('admin_settings_misc_show_resdata'),
				'type' => 'switch',
				'name' => 'sel_res_show_persondata',
				'label_0' => getLL('no'),
				'label_1' => getLL('yes'),
				'value' => $value == '' ? 0 : $value,
				);
		$value = ko_get_setting('res_show_purpose');
		$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('res_settings_show_purpose'),
				'type' => 'switch',
				'name' => 'sel_res_show_purpose',
				'label_0' => getLL('no'),
				'label_1' => getLL('yes'),
				'value' => $value == '' ? 0 : $value,
				);
		$value = ko_get_setting('res_allow_multires_for_guest');
		$frmgroup[$gc]['row'][$rowcounter++]['inputs'][0] = array('desc' => getLL('res_settings_allow_multires_for_guest'),
				'type' => 'switch',
				'name' => 'sel_allow_multires',
				'label_0' => getLL('no'),
				'label_1' => getLL('yes'),
				'value' => $value == '' ? 0 : $value,
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
		$frmgroup[$gc]['row'][$rowcounter++]['inputs'][0] = array('desc' => getLL('res_settings_access_mode'),
				'type' => 'select',
				'name' => 'sel_access_mode',
				'values' => array(0, 1),
				'descs' => array(getLL('res_settings_access_mode_0'), getLL('res_settings_access_mode_1')),
				'value' => $value,
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
	foreach(explode(",", ko_get_setting("res_mandatory")) as $man) {
		if($man && !$koi[$man]) return 12;
	}

	return 0;
}//check_entries()




/**
  * Überprüft auf Doppelbelegungen
	*/
function ko_res_check_double($item, $datum1, $datum2, $zeit1, $zeit2, &$error_txt, $id=0) {
	$datum1 = sql_datum($datum1);
	$datum2 = ($datum2) ? sql_datum($datum2) : $datum1;
	$zeit1  = sql_zeit($zeit1);
	$zeit2  = sql_zeit($zeit2);
	//Reservations without time last all day long, so set endtime to midnight
	if($zeit1 == "" && $zeit2 == "") $zeit2 = "23:59";

	//Mit id Selbsttest ausschliessen
	$where  = "WHERE `id`!='$id'";

	//check for the right item and possibly linked items
	ko_get_resitem_by_id($item, $resitem_);
	$resitem = $resitem_[$item];
	if($resitem["linked_items"] != "") {  //item with linked items
		//check for all linked items in all linked items of the made reservations
		$or = array();
		foreach(explode(",", $resitem["linked_items"]) as $linked_item) {
			$or[] = "`linked_items` REGEXP '(^|,)".$linked_item."($|,)'";
		}
		//and check for item and the linked items
		$check_itemids = $item.",".$resitem["linked_items"];
		$where .= " AND (`item_id` IN ($check_itemids) OR (".implode(" OR ", $or).")) ";
	}
	else {  //no linked items, so only check for item and linked items of made reservations
		$where .= "AND (`item_id`='$item' OR `linked_items` REGEXP '(^|,)$item($|,)') ";
	}

	//check for overlapping date and time
	$where .= " AND (
		( DATE_ADD(`startdatum`, INTERVAL `startzeit` HOUR_SECOND) >= DATE_ADD('$datum1', INTERVAL '$zeit1' HOUR_MINUTE) 
			AND DATE_ADD(`startdatum`, INTERVAL `startzeit` HOUR_SECOND) < DATE_ADD('$datum2', INTERVAL '$zeit2' HOUR_MINUTE) )
		OR ( DATE_ADD(`enddatum`, INTERVAL IF(`endzeit`='00:00:00','23:59:59',`endzeit`) HOUR_SECOND) > DATE_ADD('$datum1', INTERVAL '$zeit1' HOUR_MINUTE) 
			AND DATE_ADD(`enddatum`, INTERVAL IF(`endzeit`='00:00:00','23:59:59',`endzeit`) HOUR_SECOND) <= DATE_ADD('$datum2', INTERVAL '$zeit2' HOUR_MINUTE) )
		OR ( DATE_ADD(`startdatum`, INTERVAL `startzeit` HOUR_SECOND) < DATE_ADD('$datum1', INTERVAL '$zeit1' HOUR_MINUTE) 
				AND DATE_ADD(`enddatum`, INTERVAL IF(`endzeit`='00:00:00','23:59:59',`endzeit`) HOUR_SECOND) > DATE_ADD('$datum2', INTERVAL '$zeit2' HOUR_MINUTE) )
		)";

	$rows = db_select_data("ko_reservation", $where);

	$error_txt = '';
	foreach($rows as $row) {
		$error_txt .= $resitem['name'].' - ';
		$error_txt .= sql2datum($row['startdatum']) . ( ($row['startdatum']==$row['enddatum']) ? '' : ('-' . sql2datum($row['enddatum'])) );
		if($row['startzeit'] == '00:00:00' && $row['endzeit'] == '00:00:00') {
			$error_txt .= ' '.getLL('time_all_day');
		} else {
			$error_txt .= ' '.mb_substr($row['startzeit'],0,-3).'-'.mb_substr($row['endzeit'],0,-3);
		}
		//Only show purpose (zweck) if setting allows it for guest user
		if(($_SESSION['ses_userid'] != ko_get_guest_id() || ko_get_setting('res_show_purpose')) && trim($row['zweck']) != '') {
			$error_txt .= ' "'.(mb_strlen($row['zweck']) > 30 ? mb_substr($row['zweck'], 0, 30).'..' : $row['zweck']).'"';
		}
		//Only show details about person if setting allows it for guest-user
		if(($_SESSION['ses_userid'] != ko_get_guest_id() || ko_get_setting('res_show_persondata')) && trim($row['name']) != '') {
			$error_txt .= ' '.getLL('by').' '.$row['name'].', '.$row['email'].', '.$row['telefon'];
		}
	}
	return ($error_txt) ? FALSE : TRUE;
}//ko_res_check_double()



/**
  * Liefert eine textliche Zusammenstellung einer Reservation für die Bestätigungs-Emails
	*/
function ko_get_res_infotext($data) {
	$txt = "";
	$list = kota_get_list($data, "ko_reservation");
	foreach($list as $key => $value) {
		if($key == getLL("kota_ko_reservation_enddatum") || $key == getLL("kota_ko_reservation_endzeit")) continue;
		if($value) $txt .= "$key: ".strip_tags(ko_unhtml($value))."\n";
	}
	if($data["code"]) $txt .= getLL("res_code").": ".$data["code"]."\n";

	return $txt;
}//ko_get_res_infotext()



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
function apply_res_filter(&$z_where, &$z_limit, $_start="", $_ende="", $user_id="") {
	global $access, $KOTA;

	$use_start = ($_start != "") ? $_start : $_SESSION["filter_start"];
  $use_ende = ($_ende != "") ? $_ende : $_SESSION["filter_ende"];
	$use_items = $_SESSION['show_items'];

	//Permanenten Filter einfügen, falls vorhanden und nur view-Rechte
	$z_where = "";
	$perm_filter_start = ko_get_setting("res_perm_filter_start");
	$perm_filter_ende  = ko_get_setting("res_perm_filter_ende");

	// check, if the login has the 'force_global_filter' flag set to 1
	$forceGlobalTimeFilter = ko_get_force_global_time_filter("reservation", $_SESSION['ses_userid']);

	if(($forceGlobalTimeFilter || $access['reservation']['MAX'] < 4) && ($perm_filter_start || $perm_filter_ende)) {
		if($perm_filter_start != "") {
			$z_where .= " AND enddatum >= '".$perm_filter_start."' ";
		}
		if($perm_filter_ende != "") {
			$z_where .= " AND startdatum <= '".$perm_filter_ende."' ";
		}
	}


	if(isset($use_start) && $use_start != "immer") {
	  get_heute($tag, $monat, $jahr);
		if($use_start == "today") {
			$start = sql_datum($tag.".".$monat.".".$jahr);
		} else {
			addmonth($monat, $jahr, $use_start);
			$start = sql_datum("1.".$monat.".".$jahr);
		}
    $z_where .= "AND `enddatum` >= '$start'";
  }
  if(isset($use_ende) && $use_ende != "immer") {
    get_heute($tag, $monat, $jahr);
		if($use_ende == "today") {
			$ende = sql_datum($tag.".".$monat.".".$jahr);
		} else {
			addmonth($monat, $jahr, ($use_ende+1));
			$ende = sql_datum("0.".$monat.".".$jahr);
		}
    $z_where .= " AND `startdatum` <= '$ende'";
  }

	//Check for proper access
	$show_items = array();
	foreach($use_items as $item) {
		if($item && $access['reservation'][$item] > 0) $show_items[] = $item;
	}

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
function do_del_res($id, $del_serie=FALSE) {
	global $access;

	//Reservation auslesen für Rechte-Check und Log-Meldung
  ko_get_res_by_id($id, $r_); $r = $r_[$id];

	//Check access rights
	if($access['reservation'][$r['item_id']] > 3 || ($_SESSION["ses_userid"] == $r["user_id"] && $_SESSION["ses_userid"] != ko_get_guest_id() && $access['reservation'][$r['item_id']] > 2) ) {
		//Delete reservation
		db_delete_data("ko_reservation", "WHERE `id` = '$id'");
		ko_log_diff("delete_res", $r);

		//Delete whole serie if set
		if(intval($r["serie_id"]) > 0 && $del_serie) {
			//Get all reservations for logging
			$all_res = db_select_data('ko_reservation', "WHERE `serie_id` = '".intval($r['serie_id'])."'", '*', 'ORDER BY `startdatum` ASC');

			//Delete whole serie
			db_delete_data("ko_reservation", "WHERE `serie_id` = '".intval($r["serie_id"])."'");

			foreach($all_res as $r) {
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

	//Add the link to the year view
	$code  = '';
	$code .= '<div id="ko_calendar">';
	$code .= '<div class="fc-mwselect">';
	$code .= '<div name="mwselect" id="mwselect">';
	$code .= ko_calendar_mwselect($_SESSION['cal_view']);
	$code .= '</div>';
	$code .= '<a href="index.php?action=show_cal_jahr"><img src="'.$ko_path.'images/cal_jahr.gif" border="0" title="'.getLL('res_cal_year').'"></a>';
	$code .= '</div>';
	$code .= '</div>';

	//Add PDF link
	$code .= '<table style="margin-left:12px" cellspacing="0" cellpadding="3">';
	$code .= '<tr><td style="border-left-style:solid;border-left-width:1px">&nbsp;</td></tr>';
	$code .= '<tr><td style="border-left-style:solid;border-left-width:1px;border-bottom-width:1px;border-bottom-style:solid;">';
	$code .= '<a href="" onclick="sendReq(\'inc/ajax.php\', \'action\', \'pdfcalendar\', show_box); return false;">';
	$code .= '<img src="'.$ko_path.'images/create_pdf.png" border="0" />&nbsp;'.getLL("res_list_footer_pdf_label").'</a>';
	$code .= '<span name="res_pdf_link" id="res_pdf_link">&nbsp;</span>';
	$code .= '</td></tr>';
	$code .= '</table>';

	print $code;
}//ko_res_calendar()





function ko_get_resitems_css() {
	$r = '';

	$items = db_select_data("ko_resitem", "WHERE 1=1", "*");
	foreach($items as $item) {
		$color = $item['farbe'];
		if(!$color) $color = 'aaaaaa';

		$cr = hexdec(mb_substr($color, 0, 2));
		$cg = hexdec(mb_substr($color, 2, 2));
		$cb = hexdec(mb_substr($color, 4, 2));

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
	global $RESICAL_URL, $BASE_URL, $access;

	if($_SESSION['ses_userid'] == ko_get_guest_id()) return FALSE;
	if(!defined('KOOL_ENCRYPTION_KEY') || trim(KOOL_ENCRYPTION_KEY) == '') {
		print 'ERROR: '.getLL('error_res_13');
	}

	ko_get_login($_SESSION['ses_userid'], $login);
	$base_link = ($RESICAL_URL ? $RESICAL_URL : $BASE_URL.'resical/').'?user='.md5($_SESSION['ses_userid'].$login['password'].KOOL_ENCRYPTION_KEY);

	$help  = ko_get_help('reservation', 'ical_links');
	$help2 = ko_get_help('reservation', 'ical_links2');

	$content = '';
	$content .= '<div class="ical-links">';
	$content .= '<h1>'.getLL('res_ical_links_title').($help['show'] == 1 ? '&nbsp;'.$help['link'] : '').'</h1>';
	$content .= '<p>'.getLL('res_ical_links_description').'</p>';

	$content .= '<h4>'.getLL('res_ical_links_title_all').($help2['show'] == 1 ? '&nbsp;'.$help2['link'] : '').'</h4>';
	$content .= ko_get_ical_link($base_link, getLL('all')).'<br />';

	$link = $base_link.'&items='.implode(',', $_SESSION['show_items']);
	//Add current KOTA filter if set
	if(sizeof($_SESSION['kota_filter']['ko_reservation']) > 0) {
		foreach($_SESSION['kota_filter']['ko_reservation'] as $k => $v) {
			if(!$v) continue;
			$link .= '&kota_'.$k.'='.urlencode($v);
		}
	}
	$content .= ko_get_ical_link($link, getLL('res_ical_links_current')).'<br />';

	//The user's own reservations
	$link = $base_link.'&own=1';
	$content .= ko_get_ical_link($link, getLL('res_ical_links_own')).'<br />';

	$content .= '<h4>'.getLL('res_ical_links_title_presets').($help2['show'] == 1 ? '&nbsp;'.$help2['link'] : '').'</h4>';
	$itemset = array_merge((array)ko_get_userpref('-1', '', 'res_itemset', 'ORDER BY `key` ASC'), (array)ko_get_userpref($_SESSION['ses_userid'], '', 'res_itemset', 'ORDER BY `key` ASC'));
	foreach($itemset as $i) {
		if(!$i['key'] || !$i['value']) continue;
		$label = ($i['user_id'] == '-1' ? getLL('itemlist_global_short').$i['key'] : $i['key']);
		$content .= ko_get_ical_link($base_link.'&items=p'.$i['id'], $label).'<br />';
	}

	$content .= '<h4>'.getLL('res_ical_links_title_single').($help2['show'] == 1 ? '&nbsp;'.$help2['link'] : '').'</h4>';
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

	print $content;
}//ko_res_ical_links()




function ko_reservation_export_months($num, $month, $year) {
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
	if($num == 12) {
		for($i=1; $i<=12; $i++) {
			$months[] = $i;
		}
		$filename = getLL('res_filename_pdf').$year.'_'.strftime('%d%m%Y_%H%M%S', time()).'.pdf';
	} else if($num == 1) {
		$months[] = $month;
		$filename = getLL('res_filename_pdf').str_to_2($month).'_'.$year.'_'.strftime('%d%m%Y_%H%M%S', time()).'.pdf';
	} else throw new InvalidArgumentException('$num must be either 1 or 12');

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
					$res['zweck'] = mb_substr($res['zweck'], 0, -2);
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
					if(mb_substr($date, 5, 2) == $month) {
						$content['zeit'] = ko_get_time_as_string($res, $show_time, $mode);
						$data[(int)mb_substr($date, -2)]['inhalt'][] = $content;
					}
					$date = add2date($date, 'tag', 1, TRUE);
				}
			} else {
				$content['zeit'] = ko_get_time_as_string($res, $show_time, 'default');
				$data[(int)mb_substr($res['startdatum'], -2)]['inhalt'][] = $content;
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
	else {
		if($res[$mode]) $title['text'] = $res[$mode];
		else $title['text'] = '';
	}

	$title['short'] = $title['text'];

	if($show_comment) $title['kommentar'] = $res['zweck'];
	else $title['kommentar'] = '';

	return $title;
}//ko_reservation_get_title()
?>
