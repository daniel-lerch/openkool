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

function ko_get_submenus($type) {
	//$access has to stand here so it is available in the plugins which are included below with hook_include_sm()
	global $ko_path, $access;
	global $GSM_SUBMENUS, $my_submenu, $DISABLE_SM;

	$sm["daten_left"]            = array("termine", "termingruppen", "export", "reminder");
	$sm["daten_right"]           = array("filter", "itemlist_termingruppen");
	$sm["daten"]                 = array_merge($sm["daten_left"], $sm["daten_right"]);
	$sm["daten_dropdown"]        = array("termine","termingruppen", "reminder");

	$sm["leute_left"]            = array("leute", "schnellfilter", "meine_liste", "aktionen", "kg");
	$sm["leute_right"]           = array("filter", "itemlist_spalten", "itemlist_spalten_kg", "itemlist_chart");
	$sm["leute"]                 = array_merge($sm["leute_left"], $sm["leute_right"]);
	$sm["leute_dropdown"]        = array("leute,kg");

	$sm["reservation_left"]      = array("reservationen","objekte","export");
	$sm["reservation_right"]     = array("filter","objektbeschreibungen","itemlist_objekte");
	$sm["reservation"]           = array_merge($sm["reservation_left"], $sm["reservation_right"]);
	$sm["reservation_dropdown"]  = array("reservationen", "objekte");

	$sm['rota_left']             = array('rota', 'itemlist_teams', 'itemlist_eventgroups');
	$sm['rota_right']            = array();
	$sm['rota']                  = array_merge($sm['rota_left'], $sm['rota_right']);
	$sm['rota_dropdown']         = array('rota');

	$sm['admin_left']            = array('allgemein', 'logins', 'news', 'logs');
	$sm["admin_right"]           = array("filter");
	$sm["admin"]                 = array_merge($sm["admin_left"], $sm["admin_right"]);
	$sm['admin_dropdown']        = array('allgemein', 'logins', 'logs', 'news');

	if(ENABLE_FILESHARE) {
		$sm["fileshare_left"]      = array("foldertree", "shares", "webfolders");
		$sm["fileshare_dropdown"]  = array("shares", "webfolders");
	} else {
		$sm["fileshare_left"]      = array("webfolders");
		$sm["fileshare_dropdown"]  = array("webfolders");
	}
	$sm["fileshare_right"]       = array();
	$sm["fileshare"]             = array_merge($sm["fileshare_left"], $sm["fileshare_right"]);

	$sm['tools_left']            = array('submenus', 'leute-db', 'ldap', 'locallang', 'plugins', 'scheduler', 'typo3');
	$sm['tools_right']           = array();
	$sm['tools']                 = array_merge($sm['tools_left'], $sm['tools_right']);
	$sm['tools_dropdown']        = array('submenus', 'leute-db', 'ldap', 'locallang', 'plugins', 'scheduler', 'typo3');

	$sm["tapes_left"]            = array("tapes", "series", "groups");
	$sm["tapes_right"]           = array("print", "filter", "settings");
	$sm["tapes"]                 = array_merge($sm["tapes_left"], $sm["tapes_right"]);
	$sm["tapes_dropdown"]        = array("tapes", "series", "groups");

	$sm['groups_left']           = array('groups', 'roles', 'export', 'rights', 'dffilter');
	$sm["groups_right"]          = array();
	$sm["groups"]                = array_merge($sm["groups_left"], $sm["groups_right"]);
	$sm["groups_dropdown"]       = array("groups", "roles", "rights");

	$sm["donations_left"]        = array("donations", "accounts", "export");
	$sm["donations_right"]       = array("filter", "itemlist_accounts");
	$sm["donations"]             = array_merge($sm["donations_left"], $sm["donations_right"]);
	$sm["donations_dropdown"]    = array("donations", "accounts");

	$sm['tracking_left']        = array('trackings', 'filter', 'export');
	$sm['tracking_right']       = array('itemlist_trackinggroups');
	$sm['tracking']             = array_merge($sm['tracking_left'], $sm['tracking_right']);
	$sm['tracking_dropdown']    = array('trackings');

	$sm["projects_left"]         = array("projects", "filter", "hosting", 'stats');
	$sm["projects_right"]        = array();
	$sm["projects"]              = array_merge($sm["projects_left"], $sm["projects_right"]);
	$sm["projects_dropdown"]     = array("projects", "hosting");


	//HOOK: Include submenus from plugins
	$hooks = hook_include_sm();
	if(sizeof($hooks) > 0) foreach($hooks as $hook) include($hook);


	if(isset($GSM_SUBMENUS)) {
		$gsm = $GSM_SUBMENUS;
	} else {
		//Only show notes submenu if userpref is activated
		if(ko_get_userpref($_SESSION['ses_userid'], 'show_notes') == 1) {
			$gsm = array("gsm_notizen");
		} else {
			$gsm = array();
		}
	}

	if($sm[$type]) {
		if(substr($type, -6) == "_right" || substr($type, -9) == "_dropdown") {
			return $sm[$type];
		} else {
			return array_merge($sm[$type], $gsm);
		}
	} else {
		return array();
	}
}//ko_get_submenus()



function ko_set_submenues($uid="") {
	global $ko_menu_akt, $GSM_SUBMENUS;

	if(!$uid) $uid = $_SESSION["ses_userid"];
	$module = $ko_menu_akt;

	//Get current submenues for this user
	$sm_left = explode(",", ko_get_userpref($uid, "submenu_".$module."_left"));
	$sm_right = explode(",", ko_get_userpref($uid, "submenu_".$module."_right"));
	$sm_left_closed = explode(",", ko_get_userpref($uid, "submenu_".$module."_left_closed"));
	$sm_right_closed = explode(",", ko_get_userpref($uid, "submenu_".$module."_right_closed"));
	$sm_all = array_merge($sm_left, $sm_right, $sm_left_closed, $sm_right_closed);

	//Get all available submenues for this module
	if($uid == ko_get_guest_id()) $GSM_SUBMENUS = array();  //Don't use GSM for ko_guest
	$asm_left = ko_get_submenus($module."_left");
	$asm_right = ko_get_submenus($module."_right");
	$asm_all = array_merge($asm_left, $asm_right);

	//Compare them and add any missing
	$diff = array_diff($asm_all, $sm_all);
	if(sizeof($diff) > 0) {
		foreach($diff as $sm) {
			if(in_array($sm, $asm_left)) $sm_left[] = $sm;
			else $sm_right[] = $sm;
		}//foreach(diff as sm)
	}//if(sizeof(diff))

	//Clean up submenus and store them
	foreach($sm_left as $key => $value) if(!$value || !in_array($value, $asm_all)) unset($sm_left[$key]);
	foreach($sm_right as $key => $value) if(!$value || !in_array($value, $asm_all)) unset($sm_right[$key]);
	foreach($sm_left_closed as $key => $value) if(!$value || !in_array($value, $asm_all)) unset($sm_left_closed[$key]);
	foreach($sm_right_closed as $key => $value) if(!$value || !in_array($value, $asm_all)) unset($sm_right_closed[$key]);
	ko_save_userpref($uid, "submenu_".$module."_left", implode(",", $sm_left));
	ko_save_userpref($uid, "submenu_".$module."_right", implode(",", $sm_right));
	ko_save_userpref($uid, "submenu_".$module."_left_closed", implode(",", $sm_left_closed));
	ko_save_userpref($uid, "submenu_".$module."_right_closed", implode(",", $sm_right_closed));

	//Store in session
	$_SESSION["submenu_left"] = implode(",", $sm_left);
	$_SESSION["submenu_right"] = implode(",", $sm_right);
	$_SESSION["submenu_left_closed"] = implode(",", $sm_left_closed);
	$_SESSION["submenu_right_closed"] = implode(",", $sm_right_closed);
}//ko_set_submenues()




function ko_check_submenu($sm, $module) {
	global $MODULES;

	if(!in_array($module, $MODULES)) return FALSE;
	if(!in_array($sm, ko_get_submenus($module))) return FALSE;
	return TRUE;
}//ko_check_submenu()



function ko_get_submenu_code($module, $pos) {
	global $DISABLE_SM;

	$return = "";

	//Offene Submenüs anzeigen
	$new = "";
	foreach(explode(",",$_SESSION["submenu_".$pos]) as $sm) {
		if(!in_array($sm, $DISABLE_SM[$module][$_SESSION["show"]]) && $sm != "") {
			$new .= $sm.",";
		}
	}
	if($new != "") {
		$_SESSION["submenu_".$pos] = substr($new, 0, -1);
		eval("\$r=submenu_".$module.'($_SESSION["submenu_".$pos], $pos, "open");');
		$return .= $r;
	}

	//Geschlossene Submenüs anzeigen
	$new = "";
	foreach(explode(",",$_SESSION["submenu_".$pos."_closed"]) as $sm) {
		if(!in_array($sm, $DISABLE_SM[$module][$_SESSION["show"]]) && $sm != "") {
			$new .= $sm.",";
		}
	}
	if($new != "") {
		$_SESSION["submenu_".$pos."_closed"] = substr($new, 0, -1);
		eval("\$r=submenu_".$module.'($_SESSION["submenu_".$pos."_closed"], $pos, "closed");');
		$return .= $r;
	}

	return $return;
}//ko_get_submenu_code()



function submenu($menu, $position, $state, $display=3, $module="") {
	global $ko_path, $smarty, $PLUGINS;

	switch($menu) {
		case "gsm_notizen":
			//Guest darf keine Notizen machen
			if($_SESSION["ses_userid"] == ko_get_guest_id()) continue;

			$submenu["titel"] = getLL("submenu_title_notizen");
			$submenu["output"][0] = "[notizen]";

			//Alle Notizen in Select abfüllen
			$notizen = ko_get_userpref($_SESSION["ses_userid"], "", ("notizen"));
			if(is_array($notizen)) {
				foreach($notizen as $n) {
					$tpl_notizen_values[] = $n["key"];
					$tpl_notizen_output[] = $n["key"];
					if($n["key"] == $_SESSION["show_notiz"]) $tpl_selected = $n["key"];
				}
			}

			$smarty->assign("tpl_notizen_values", $tpl_notizen_values);
      $smarty->assign("tpl_notizen_output", $tpl_notizen_output);
      $smarty->assign("tpl_notizen_selected", $tpl_selected);

			//Falls Notiz aktiv, diese auslesen und anzeigen
			if($_SESSION["show_notiz"]) {
				$notiz = ko_get_userpref($_SESSION["ses_userid"], $_SESSION["show_notiz"], "notizen");
				$smarty->assign("tpl_text", ko_html($notiz[0]["value"]));
			}
		break;

		//Allow plugins to add new submenus
		default:
			foreach($PLUGINS as $p) {
				if(function_exists('my_submenu_'.$p['name'].'_'.$menu)) {
					$submenu = call_user_func('my_submenu_'.$p['name'].'_'.$menu, $position, $state);
				}
			}
	}//switch(menu)

	$submenu["key"] = $module."_".$menu;
	$submenu["id"] = $menu;
	$submenu["mod"] = $module;
	$submenu["sesid"] = session_id();
	$submenu["position"] = $position;
	$submenu["state"] = $state;

	if($display == 1) {
		//$smarty->assign("sm", $submenu[$menucounter]);
		//$smarty->assign("ko_path", $ko_path);
		//$smarty->display("ko_submenu.tpl");
	} else if($display == 2) {
		$smarty->assign("sm", $submenu);
		$smarty->assign("ko_path", $ko_path);
		$return = "sm_".$module."_".$menu."@@@";
		$return .= $smarty->fetch("ko_submenu.tpl");
		return $return;
	} else if($display == 3) {  //Default for submenu_*()
		return $submenu;
	}
}//submenu()



/**
	* Gibt ein Daten-Submenu mittels dem Template ko_menu.tpl aus.
	* namen ist eine ,-getrennte Liste der anzuzeigenden Submenus
	* position ist links oder rechts
	* state ist geschlossen oder offen (gilt für alle aus namen)
	* display gibt an, wie der Code zurückgegeben werden soll:
	* 1: normale Ausgabe über smarty, 2: Rückgabe des HTML-Codes für Ajax, 3: Array für Dropdown-Menu
	*/
function submenu_daten($namen, $position, $state, $display=1) {
	global $ko_path, $smarty;
	global $access;
	global $my_submenu;

	$return = "";

	$all_rights = ko_get_access_all('event_admin', '', $max_rights);
	if($max_rights < 1) return FALSE;

	$namen = explode(",", $namen);


	$menucounter = 0;
	foreach($namen as $menu) {
		$found = FALSE;

		$itemcounter = 0;
		switch($menu) {

			case "termine":
				$found = TRUE;
				$submenu[$menucounter]["titel"] = getLL("submenu_daten_title_termine");
				if($state == "closed") continue;

				if($max_rights > 1 && db_get_count('ko_eventgruppen', 'id', "AND `type` = '0'") > 0) {
					$submenu[$menucounter]["output"][$itemcounter] = ko_menuitem("daten", "neuer_termin");
					$submenu[$menucounter]["link"][$itemcounter++] = $ko_path."daten/index.php?action=neuer_termin";
				}
				if($max_rights > 0) {
					$submenu[$menucounter]["output"][$itemcounter] = ko_menuitem("daten", "all_events");
					$submenu[$menucounter]["link"][$itemcounter++] = $ko_path."daten/index.php?action=all_events";
					$submenu[$menucounter]["output"][$itemcounter] = ko_menuitem("daten", "calendar");
					$submenu[$menucounter]["link"][$itemcounter++] = $ko_path."daten/index.php?action=calendar";
				}
				if($max_rights > 3 || ($max_rights > 1 && $_SESSION["ses_userid"] != ko_get_guest_id()) ) {
					if($max_rights > 3) {
						if($all_rights >= 4) {  //Moderator for all cals/groups
							$where = ' AND 1=1 ';
						} else {  //Only moderating several cals/groups
							//Get Admin-rights if not already set
							if(!isset($access['daten'])) ko_get_access('daten');
							$show_egs = array();
							$egs = db_select_data('ko_eventgruppen', "WHERE `type` = '0'", '*');
							foreach($egs as $gid => $eg) if($access['daten'][$gid] > 3) $show_egs[] = $eg['id'];
							$where = sizeof($show_egs) > 0 ? " AND `eventgruppen_id` IN ('".implode("','", $show_egs)."') " : ' AND 1=2 ';
						}
					} else {
						//Apply filter for user_id for non-moderators
						$where = " AND `_user_id` = '".$_SESSION["ses_userid"]."' ";
					}
					$num_mod = db_get_count("ko_event_mod", "id", $where);
					$submenu[$menucounter]["output"][$itemcounter] = ko_menuitem("daten", "list_events_mod")." ($num_mod)";
					$submenu[$menucounter]["link"][$itemcounter++] = $num_mod > 0 ? $ko_path."daten/index.php?action=list_events_mod" : "";
				}

				if($max_rights > 0 && $_SESSION['ses_userid'] != ko_get_guest_id()) {
					$submenu[$menucounter]['output'][$itemcounter] = ko_menuitem('daten', 'daten_settings');
					$submenu[$menucounter]['link'][$itemcounter++] = $ko_path.'daten/index.php?action=daten_settings';

					$submenu[$menucounter]['output'][$itemcounter++] = '';
					$submenu[$menucounter]['output'][$itemcounter] = ko_menuitem('daten', 'ical_links');
					$submenu[$menucounter]['link'][$itemcounter++] = $ko_path.'daten/index.php?action=daten_ical_links';
				}
			break;



			case "termingruppen":
				if($max_rights > 2) {
					$found = TRUE;
					$submenu[$menucounter]["titel"] = getLL("submenu_daten_title_termingruppen");
					if($state == "closed") continue;

					if($all_rights > 2) {
						$submenu[$menucounter]['output'][$itemcounter] = ko_menuitem('daten', 'neue_gruppe');
						$submenu[$menucounter]['link'][$itemcounter++] = $ko_path.'daten/index.php?action=neue_gruppe';
						$submenu[$menucounter]['output'][$itemcounter] = ko_menuitem('daten', 'new_googlecal');
						$submenu[$menucounter]['link'][$itemcounter++] = $ko_path.'daten/index.php?action=new_googlecal';
						$submenu[$menucounter]['output'][$itemcounter] = ko_menuitem('daten', 'new_ical');
						$submenu[$menucounter]['link'][$itemcounter++] = $ko_path.'daten/index.php?action=new_ical';
						$submenu[$menucounter]['output'][$itemcounter++] = '';
					}
					$submenu[$menucounter]["output"][$itemcounter] = ko_menuitem("daten", "all_groups");
					$submenu[$menucounter]["link"][$itemcounter++] = $ko_path."daten/index.php?action=all_groups";
				}
			break;



			case "export":
				//Only show for logged in users
				if($max_rights > 0 && $_SESSION["ses_userid"] != ko_get_guest_id()) {
					$found = TRUE;
					$submenu[$menucounter]["titel"] = getLL("submenu_daten_title_export");
					if($state == "closed") continue;

					//PDF exports for daily, weekly, monthly and yearly calendar
					if($max_rights > 0) {
						$submenu[$menucounter]["output"][$itemcounter] = ' ';
						$submenu[$menucounter]["no_ul"][$itemcounter] = TRUE;
						$submenu[$menucounter]["link"][$itemcounter] = "";
						$c = '<select name="sel_pdf_export" size="0" onchange="jumpToUrl(\'?action=export_pdf&mode=\'+this.options[this.selectedIndex].value);" style="width: 170px;">';
						$c .= '<option value="">'.getLL('submenu_daten_pdf_export').'</option>';
						$c .= '<option value="" disabled="disabled">----------</option>';

						//Presets
						$presets = db_select_data('ko_pdf_layout', "WHERE `type` = 'daten'", '*', 'ORDER BY `name` ASC');
						if(sizeof($presets) > 0 || $max_rights > 3) {
							$c .= '<optgroup label="'.getLL('submenu_daten_export_presets').'">';
							foreach($presets as $p) {
								//TODO: Add symbol to mark presets with defined EG preset as opposed to those exporting the currenlty visible EGs
								$c .= '<option value="preset'.$p['id'].'">'.$p['name'].'</option>';
							}
							if($max_rights > 3) {
								$c .= '<option value="" disabled="disabled">----------</option>';
								$c .= '<option value="newpreset">'.getLL('submenu_daten_export_new_preset').'</option>';
								$c .= '<option value="listpresets">'.getLL('submenu_daten_export_list_presets').'</option>';
							}
							$c .= '</optgroup>';
						}

						$c .= '<optgroup label="'.getLL('day').'">';
						$c .= '<option value="d-0">'.getLL('time_today').'</option>';
						$c .= '<option value="d-1">'.getLL('time_tomorrow').'</option>';
						$c .= '</optgroup>';
						$c .= '<optgroup label="'.getLL('week').'">';
						$c .= '<option value="w-0">'.getLL('time_current').'</option>';
						$c .= '<option value="w-1">'.getLL('time_next').'</option>';
						$c .= '</optgroup>';
						$c .= '<optgroup label="'.getLL('month').'">';
						$c .= '<option value="m-0">'.getLL('time_current').'</option>';
						$c .= '<option value="m-1">'.getLL('time_next').'</option>';
						$c .= '</optgroup>';
						$c .= '<optgroup label="'.getLL('twelvemonths').'">';
						$c .= '<option value="12m-0">'.getLL('time_current').'</option>';
						$c .= '<option value="12m-1">'.getLL('time_next').'</option>';
						$c .= '</optgroup>';
						$c .= '<optgroup label="'.getLL('halfayear').'">';
						$c .= '<option value="s-0:1">1/'.date('Y').'</option>';
						$c .= '<option value="s-0:7">2/'.date('Y').'</option>';
						$c .= '<option value="s-1:1">1/'.(date('Y')+1).'</option>';
						$c .= '<option value="s-1:7">2/'.(date('Y')+1).'</option>';
						$c .= '</optgroup>';
						$c .= '<optgroup label="'.getLL('year').'">';
						$c .= '<option value="y-0">'.getLL('time_current').'</option>';
						$c .= '<option value="y-1">'.getLL('time_next').'</option>';
						$c .= '</optgroup>';
						//TODO: Allow plugins to add new export options
						$c .= '</select><br />';
						$submenu[$menucounter]["html"][$itemcounter++] = $c;
					}

					//Excel-Export
					if($_SESSION['show'] == 'all_events' && $max_rights > 0) {
						$submenu[$menucounter]['output'][$itemcounter++] = '';

						$submenu[$menucounter]["output"][$itemcounter] = ' ';
						$submenu[$menucounter]["no_ul"][$itemcounter] = TRUE;
						$submenu[$menucounter]["link"][$itemcounter] = "";
						$c = '<select name="sel_xls_cols" size="0" onchange="jumpToUrl(\'?action=export_xls_daten&sel_xls_cols=\'+this.options[this.selectedIndex].value);">';
						$c .= '<option value="">'.getLL('submenu_daten_xls_export').'</option>';
						$c .= '<option value="" disabled="disabled">--- '.getLL('columns').' ---</option>';
						$c .= '<option value="_session">'.getLL('shown').'</option>';
						$itemset = array_merge((array)ko_get_userpref('-1', '', 'ko_event_colitemset', 'ORDER by `key` ASC'), (array)ko_get_userpref($_SESSION['ses_userid'], '', 'ko_event_colitemset', 'ORDER by `key` ASC'));
						foreach($itemset as $i) {
							$value = $i['user_id'] == '-1' ? '@G@'.$i['key'] : $i['key'];
							$output = $i['user_id'] == '-1' ? getLL('itemlist_global_short').' '.$i['key'] : $i['key'];
							$c .= '<option value="'.$value.'">"'.$output.'"</option>';
						}
						$c .= '</select><br />';
						$submenu[$menucounter]["html"][$itemcounter++] = $c;
					}

				}
			break;

			case 'reminder':
				if ($access['daten']['REMINDER'] < 1) continue;

				$found = TRUE;
				$submenu[$menucounter]["titel"] = getLL("submenu_daten_title_reminder");
				if($state == "closed") continue;

				$submenu[$menucounter]["output"][$itemcounter] = ko_menuitem("daten", "new_reminder");
				$submenu[$menucounter]["link"][$itemcounter++] = $ko_path."daten/index.php?action=new_reminder";
				$submenu[$menucounter]["output"][$itemcounter] = ko_menuitem("daten", "list_reminders");
				$submenu[$menucounter]["link"][$itemcounter++] = $ko_path."daten/index.php?action=list_reminders";

				break;



			case "filter":
				if($max_rights <= 0) break;
				$found = TRUE;

				//Permanente Filter
				$perm_filter_start = ko_get_setting("daten_perm_filter_start");
				$perm_filter_ende  = ko_get_setting("daten_perm_filter_ende");

				//Code für Select erstellen
				get_heute($tag, $monat, $jahr);
			  addmonth($monat, $jahr, -18);

			  if(isset($_SESSION["filter_start"])) $akt_von = $_SESSION["filter_start"];
			  else $akt_von = "today";
			  if(isset($_SESSION["filter_ende"])) $akt_bis = $_SESSION["filter_ende"];
			  else $akt_bis = "immer";

				$von_code = '<select name="sel_filter_start" size="0" onchange="document.getElementById(\'submit_filter\').click();">';
				$bis_code = '<select name="sel_filter_ende" size="0" onchange="document.getElementById(\'submit_filter\').click();">';

			  if($akt_von == "immer") $sel = 'selected="selected"';
			  else $sel = "";
			  $von_code .= '<option value="immer" ' . $sel . '>'.getLL("filter_always").'</option>';


				for($i=-18; $i<36; $i++) {
			    if((string)$i == (string)$akt_von && (string)$akt_von != "immer") $sel_von = 'selected="selected"';
			    else $sel_von = "";
			    if((string)$i == (string)$akt_bis && (string)$akt_bis != "immer") $sel_bis = 'selected="selected"';
			    else $sel_bis = "";
			    $von_code .= '<option value="' . $i . '" ' . $sel_von.">";
			    $von_code .= strftime($GLOBALS["DATETIME"]["nY"], mktime(1, 1, 1, $monat, 1, $jahr));
			    $von_code .= '</option>';
			    $bis_code .= '<option value="' . $i . '" ' . $sel_bis.">";
			    $bis_code .= strftime($GLOBALS["DATETIME"]["nY"], mktime(1, 1, 1, $monat, 1, $jahr));
			    $bis_code .= '</option>';
			    addmonth($monat, $jahr, 1);
					//Add filter "from today"
					if($i == 0) {
						$sel_von = $akt_von == "today" ? 'selected="selected"' : '';
						$von_code .= '<option value="today" '.$sel_von.' style="font-weight:600; font-color:#224;">'.getLL("filter_from_today").'</option>';
						$sel_bis = $akt_bis == "today" ? 'selected="selected"' : '';
						$bis_code .= '<option value="today" '.$sel_bis.' style="font-weight:600; font-color:#224;">'.getLL("filter_until_today").'</option>';
					}
			  }
				
			  if($akt_bis == "immer") $sel = 'selected="selected"';
			  else $sel = "";
			  $bis_code .= '<option value="immer" ' . $sel . '>'.getLL("filter_always").'</option>';

				$von_code .= '</select>';
				$bis_code .= '</select>';

				//Permanenter Filter bei Stufe 4
				if(
					$max_rights > 3) {
					if($perm_filter_start || $perm_filter_ende) {
						$style = 'color:red;font-weight:900;';

						//Start und Ende formatieren
						$pfs = $perm_filter_start ? strftime($GLOBALS["DATETIME"]["nY"], strtotime($perm_filter_start)) : getLL("filter_always");
						$pfe = $perm_filter_ende ? strftime($GLOBALS["DATETIME"]["nY"], strtotime($perm_filter_ende)) : getLL("filter_always");

						$perm_filter_desc = "$pfs - $pfe";
						$checked = 'checked="checked"';
					} else {
						$perm_filter_desc = $style = "";
						$checked = '';
					}

					$bis_code .= '<div style="padding-top:5px;'.$style.'">';
					$bis_code .= '<input type="checkbox" name="chk_perm_filter" id="chk_perm_filter" '.$checked.' />';
					//Bestehenden permanenten Filter anzeigen, falls eingeschaltet
					if($perm_filter_desc) {
						$bis_code .= '<label for="chk_perm_filter">'.getLL('filter_use_globally_applied').'</label>';
						$bis_code .= "<br />$perm_filter_desc";
					} else {
						$bis_code .= '<label for="chk_perm_filter">'.getLL('filter_use_globally').'</label>';
					}
					$bis_code .= "</div>";
				}//if(group_mod)


				$bis_code .= '<input style="display: none;" type="submit" value="'.getLL("filter_refresh").'" name="submit_filter" id="submit_filter" onclick="set_action(\'submit_filter\', this);" />';

				$submenu[$menucounter]['form'] = TRUE;
				$submenu[$menucounter]['form_hidden_inputs'] = array(array('name' => 'action', 'value' => ''),
																														 array('name' => 'ids', 'value' => ''));
				$submenu[$menucounter]["titel"] = getLL("submenu_daten_title_filter");
				if($state == "closed") continue;

				$submenu[$menucounter]['output'][$itemcounter] = getLL('filter_from_today');
				$submenu[$menucounter]["link"][$itemcounter++] = $ko_path."daten/index.php?action=set_filter_today";
				$submenu[$menucounter]["output"][$itemcounter] = getLL("filter_start");
				$submenu[$menucounter]["link"][$itemcounter] = "";
				$submenu[$menucounter]["html"][$itemcounter++] = $von_code;
				$submenu[$menucounter]["output"][$itemcounter++] = "";
				$submenu[$menucounter]["output"][$itemcounter] = getLL("filter_stop");
				$submenu[$menucounter]["link"][$itemcounter] = "";
				$submenu[$menucounter]["html"][$itemcounter++] = $bis_code;
			break;



			case "itemlist_termingruppen":
				if($max_rights <= 0) break;
				$found = TRUE;

				if($all_rights < 1 && !isset($access['daten'])) ko_get_access('daten');

				$show_cals = !(ko_get_userpref($_SESSION['ses_userid'], 'daten_no_cals_in_itemlist') == 1);

				//Alle Objekte auslesen
				$counter = 0;
				if($show_cals) {
					ko_get_event_calendar($cals);
					foreach($cals as $cid => $cal) {
						if($all_rights < 1 && $access['daten']['cal'.$cid] < 1) continue;
						$_groups = db_select_data("ko_eventgruppen", "WHERE `calendar_id` = '$cid'", "*", "ORDER BY name ASC");
						//Only keep event groups this user has access to
						$groups = array();
						foreach($_groups as $gid => $group) {
							if($all_rights < 1 && $access['daten'][$gid] < 1) continue;
							if($_SESSION['show'] != 'calendar' && $group['type'] == 1) continue;  //Don't show google cal except in fullcalendar
							$groups[$gid] = $group;
						}
						//Find selected groups
						$selected = $local_ids = $gcal_ids = array();
						foreach($groups as $gid => $group) {
							if(in_array($gid, $_SESSION["show_tg"])) $selected[$gid] = TRUE;
							if($group['gcal_url'] != '') $gcal_ids[] = $gid;
							else $local_ids[] = $gid;
						}
						//Don't show whole calendar if no local event groups for displays other than calendar (calendar would contain no eventgroups)
						if(($_SESSION['show'] != 'calendar' && sizeof($local_ids) == 0) || sizeof($groups) == 0) continue;

						$itemlist[$counter]["type"] = "group";
						$itemlist[$counter]["name"] = $cal["name"].'<sup> (<span name="calnum_'.$cal['id'].'">'.sizeof($selected).'</span>)</sup>';
						$itemlist[$counter]["aktiv"] = (sizeof($groups) == sizeof($selected) ? 1 : 0);
						$itemlist[$counter]["value"] = $cid;
						$itemlist[$counter]["open"] = isset($_SESSION["daten_calendar_states"][$cid]) ? $_SESSION["daten_calendar_states"][$cid] : 0;
						foreach($gcal_ids as $gcal_id) {
							$itemlist[$counter]['onclick_post'] .= sizeof($groups) == sizeof($selected) ? '$(\'#ko_calendar\').fullCalendar(\'removeEventSource\', gcalSource'.$gcal_id.');' : '$(\'#ko_calendar\').fullCalendar(\'addEventSource\', gcalSource'.$gcal_id.', false);';
						}
						$counter++;

						foreach($groups as $i_i => $i) {
							if($i['gcal_url'] != '') {  //External Google Calendars
								if($_SESSION['show'] != 'calendar') continue;  //Only show them in calendar (fullcalendar)
								$post = in_array($i_i, $_SESSION['show_tg']) ? '$(\'#ko_calendar\').fullCalendar(\'removeEventSource\', gcalSource'.$i_i.')' : '$(\'#ko_calendar\').fullCalendar(\'addEventSource\', gcalSource'.$i_i.')';
								$itemlist[$counter]['onclick_post'] = $post;
								$itemlist[$counter]['action'] = 'itemlistRedraw';
							}
							$itemlist[$counter]["name"] = ko_html($i["name"]);
							$itemlist[$counter]["prename"] = '<span style="margin-right:2px;background-color:#'.($i["farbe"]?$i["farbe"]:"fff").';">&emsp;</span>';
							$itemlist[$counter]["aktiv"] = in_array($i_i, $_SESSION["show_tg"]) ? 1 : 0;
							$itemlist[$counter]["parent"] = TRUE;  //Is subitem to a calendar
							$itemlist[$counter++]["value"] = $i_i;
						}//foreach(groups)
						$itemlist[$counter-1]["last"] = TRUE;
					}//foreach(cals)
				}//if(show_cals)


				//Add event groups without a calendar
				if($show_cals) {
					$groups = db_select_data("ko_eventgruppen", "WHERE `calendar_id` = '0'", "*", "ORDER BY name ASC");
				} else {
					//Get all eventgroups if calendars are to be hidden
					$groups = db_select_data("ko_eventgruppen", "WHERE 1", "*", "ORDER BY name ASC");
				}
				foreach($groups as $i_i => $i) {
					if($all_rights < 1 && $access['daten'][$i_i] < 1) continue;
					if($i['gcal_url'] != '') {  //External Google Calendars
						if($_SESSION['show'] != 'calendar') continue;  //Only show them in calendar (fullcalendar)
						$post = in_array($i_i, $_SESSION['show_tg']) ? '$(\'#ko_calendar\').fullCalendar(\'removeEventSource\', gcalSource'.$i_i.')' : '$(\'#ko_calendar\').fullCalendar(\'addEventSource\', gcalSource'.$i_i.')';
						$itemlist[$counter]['onclick_post'] = $post;
						$itemlist[$counter]['action'] = 'itemlistRedraw';
					}
					$itemlist[$counter]["name"] = ko_html($i["name"]);
					$itemlist[$counter]["prename"] = '<span style="margin-right:2px;background-color:#'.($i["farbe"]?$i["farbe"]:"fff").';">&emsp;</span>';
					$itemlist[$counter]["aktiv"] = in_array($i_i, $_SESSION["show_tg"]) ? 1 : 0;
					$itemlist[$counter++]["value"] = $i_i;
				}//foreach(groups)

				$smarty->assign("tpl_itemlist_select", $itemlist);


				//Get all presets
				$akt_value = $_SESSION["show_tg"];
				$akt_value = implode(",", $_SESSION["show_tg"]);
				$itemset = array_merge((array)ko_get_userpref('-1', '', 'daten_itemset', 'ORDER BY `key` ASC'), (array)ko_get_userpref($_SESSION['ses_userid'], '', 'daten_itemset', 'ORDER BY `key` ASC'));
				foreach($itemset as $i) {
					$value = $i['user_id'] == '-1' ? '@G@'.$i['key'] : $i['key'];
			    $itemselect_values[] = $value;
			    $itemselect_output[] = $i['user_id'] == '-1' ? getLL('itemlist_global_short').' '.$i['key'] : $i['key'];
			    if($i["value"] == $akt_value) $itemselect_selected = $value;
				}
				$smarty->assign("tpl_itemlist_values", $itemselect_values);
				$smarty->assign("tpl_itemlist_output", $itemselect_output);
				$smarty->assign("tpl_itemlist_selected", $itemselect_selected);
				if($max_rights > 3) $smarty->assign('allow_global', TRUE);
																
				$submenu[$menucounter]["titel"] = getLL("submenu_daten_title_itemlist_termingruppen");
				if($state == "closed") continue;

				$submenu[$menucounter]["output"][$itemcounter++] = "[itemlist]";
			break;


			default:
				if($menu) {
          $submenu[$menucounter] = submenu($menu, $position, $state, 3, 'daten');
					$found = (is_array($submenu[$menucounter]));
				}
		}//switch($menu)

		//Plugins erlauben, Menuitems hinzuzufügen
		hook_submenu("daten", $menu, $submenu, $menucounter, $itemcounter);

		if($found) {
			$submenu[$menucounter]["key"] = "daten_".$menu;
			$submenu[$menucounter]["id"] = $menu;
			$submenu[$menucounter]["mod"] = "daten";
			$submenu[$menucounter]["sesid"] = session_id();
      $submenu[$menucounter]["position"] = $position;
      $submenu[$menucounter]["state"] = $state;

			if($display == 1) {
				$smarty->assign("sm", $submenu[$menucounter]);
				$smarty->assign("ko_path", $ko_path);
				$smarty->assign("help", ko_get_help("daten", "submenu_".$menu));
				$smarty->display("ko_submenu.tpl");
			} else if($display == 2) {
				$smarty->assign("sm", $submenu[$menucounter]);
				$smarty->assign("ko_path", $ko_path);
				$return = "sm_daten_".$menu."@@@";
				$return .= $smarty->fetch("ko_submenu.tpl");
			}

			$smarty->clear_assign("help");
			$menucounter++;
    }
	}//foreach(namen as menu)

	if($display == 2) {
		return $return;
	} else if($display == 3) {
		return $submenu;
	}

}//submenu_daten()




//display: 1: Normal, 2: Ajax, 3: SlideoutMenu
function submenu_leute($namen, $position, $state, $display=1) {
	global $ko_path, $smarty;
	global $js_calendar;
	global $LEUTE_CHART_TYPES, $RECTYPES;
	global $ko_menu_akt, $access;
	global $SMS_PARAMETER, $MAILING_PARAMETER;
	global $my_submenu, $KOTA;
	global $ENABLE_VERSIONING_FASTFILTER;
	global $LEUTE_NO_FAMILY;

	$return = '';

	if($ko_menu_akt == 'leute') {
		$all_rights = $access['leute']['ALL'];
		$max_rights = $access['leute']['MAX'];
		$gs_rights  = $access['leute']['GS'];
	} else {
		$all_rights = ko_get_access_all('leute_admin', '', $max_rights);
		$login = db_select_data('ko_admin', 'WHERE `id` = \''.$_SESSION['ses_userid'].'\'', 'id,leute_admin_gs,admingroups', '', '', TRUE);
		$gs_rights = $login['leute_admin_gs'] == 1;
		//Check admin groups for leute_admin_gs
		if(!$gs_rights && $login['admingroups'] != '') {
			$ags = db_select_data('ko_admingroups', "WHERE `id` IN (".$login['admingroups'].") AND `leute_admin_gs` = 1");
			$gs_rights = sizeof($ags) > 0;
		}
	}
	$leute_admin_groups = ko_get_leute_admin_groups($_SESSION['ses_userid'], 'all');

	if(ko_module_installed('kg')) $kg_all_rights = ko_get_access_all('kg_admin');
	if(ko_module_installed('groups')) $group_all_rights = ko_get_access_all('groups_admin', '', $group_max_rights);

	if($max_rights < 1) return FALSE;

	$namen = explode(",", $namen);
	$menucounter = 0;
	foreach($namen as $menu) {
		$found = FALSE;

		$itemcounter = 0;
		switch($menu) {

			case "leute":
				$found = TRUE;
				$submenu[$menucounter]["titel"] = getLL("submenu_leute_title_leute");
				if($max_rights > 1) {
					$submenu[$menucounter]["output"][$itemcounter] = ko_menuitem("leute", "neue_person");
					$submenu[$menucounter]["link"][$itemcounter++] = $ko_path."leute/index.php?action=neue_person";
				}
				$submenu[$menucounter]["output"][$itemcounter] = ko_menuitem("leute", "show_all");
				$submenu[$menucounter]["link"][$itemcounter++] = $ko_path."leute/index.php?action=show_all";

				$submenu[$menucounter]["output"][$itemcounter] = ko_menuitem("leute", "show_adressliste");
				$submenu[$menucounter]["link"][$itemcounter++] = $ko_path."leute/index.php?action=show_adressliste";

				$allowed_cols = ko_get_leute_admin_spalten($_SESSION['ses_userid'], 'all');
				if(!is_array($allowed_cols['view']) || in_array('geburtsdatum', $allowed_cols['view'])) {
					$submenu[$menucounter]["output"][$itemcounter] = ko_menuitem("leute", "geburtstagsliste");
					$submenu[$menucounter]["link"][$itemcounter++] = $ko_path."leute/index.php?action=show_geburtstagsliste";
				}

				if($all_rights > 3 || ($gs_rights && $group_max_rights > 1)) $submenu[$menucounter]["output"][$itemcounter++] = "";

				//Adress-Aenderungen
				if($max_rights > 1) {
					ko_get_mod_leute($aa);
					//For logins with edit access to only some addresses exclude those they don't have access to
					if($all_rights < 2) {
						if(!is_array($access['leute']) && ko_module_installed('leute')) ko_get_access('leute');
						foreach($aa as $aid => $a) {
							if($access['leute'][$a['_leute_id']] < 2 || $a['_leute_id'] < 1) unset($aa[$aid]);
						}
					}
					$nr_mod = sizeof($aa);
					$submenu[$menucounter]["output"][$itemcounter] = ko_menuitem("leute", "mutationsliste")." (".$nr_mod.")";
					$submenu[$menucounter]["link"][$itemcounter++] = ($nr_mod>0)?$ko_path."leute/index.php?action=show_aa":"";
				}
				//Gruppen-Anmeldungen
				if($all_rights > 3 || ($gs_rights && $group_max_rights > 1)) {
					ko_get_groupsubscriptions($gs, "", $_SESSION["ses_userid"]);
					$nr_mod = sizeof($gs);
					$submenu[$menucounter]["output"][$itemcounter] = ko_menuitem("leute", "groupsubscriptions")." (".$nr_mod.")";
					$submenu[$menucounter]["link"][$itemcounter++] = ($nr_mod>0)?$ko_path."leute/index.php?action=show_groupsubscriptions":"";
				}
				//Settings
				$submenu[$menucounter]['output'][$itemcounter++] = '';
				$submenu[$menucounter]['output'][$itemcounter] = ko_menuitem('leute', 'settings');
				$submenu[$menucounter]['link'][$itemcounter++] = $ko_path.'leute/index.php?action=settings';
				//Import (allow with all rights 2 or with max rights 2 and leute_admin_groups. This ensures that user will be able to see imported addresses)
				if($all_rights > 1 || ($max_rights > 1 && $leute_admin_groups !== FALSE)) {
					$submenu[$menucounter]["output"][$itemcounter] = ko_menuitem("leute", "import");
					$submenu[$menucounter]["link"][$itemcounter++] = $ko_path."leute/index.php?action=import&amp;state=1";
				}

				//AssignToOwnGroup
				if(ko_get_leute_admin_assign($_SESSION['ses_userid'], 'all')) {
					$submenu[$menucounter]['output'][$itemcounter++] = '';
					$submenu[$menucounter]['output'][$itemcounter] = getLL('submenu_leute_global_assign');
					$submenu[$menucounter]['link'][$itemcounter] = '';

					$agroups = ko_get_leute_admin_groups($_SESSION['ses_userid'], 'all');
					$agroup = ko_groups_decode(array_shift($agroups), 'group');
					$token = md5(uniqid('', TRUE));
					$_SESSION['peoplesearch_access_token'] = $token;
					$code = '
<form action="index.php" method="POST">
	<div class="peoplesearchwrap">
		<input type="hidden" name="action" value="global_assign" />
		<input type="text" class="peoplesearch" style="width:165px;" name="txt_global_assign" autocomplete="off" data-source="all-'.$token.'" /><br />
		<select class="peoplesearchresult" size="2" name="sel_ds1_global_assign"></select>
		<input type="hidden" name="global_assign" value="" />
		<input type="hidden" name="old_global_assign" value="" />
		<select name="sel_ds2_global_assign" class="peoplesearchact" style="display: none;"></select>

		<div class="peoplesearchresult" style="display: none;"> '.
			getLL('submenu_leute_global_assign_label').' "'.$agroup['name'].'" 
			<input type="submit" name="submit_global_assign" value="'.getLL('submenu_leute_global_assign_button').'" />
		</div>
	</div>
</form>';
					$submenu[$menucounter]['html'][$itemcounter++] = $code;
				}
			break;

			case "meine_liste":
				$found = TRUE;
				$submenu[$menucounter]['form'] = TRUE;
				$submenu[$menucounter]['form_hidden_inputs'] = array(array('name' => 'action', 'value' => ''),
																														 array('name' => 'ids', 'value' => ''));

				$submenu[$menucounter]["titel"] = getLL("submenu_leute_title_meine_liste");
				$submenu[$menucounter]["output"][$itemcounter] = " ";
				$submenu[$menucounter]["no_ul"][$itemcounter] = TRUE;
				$submenu[$menucounter]["link"][$itemcounter] = "";
				$submenu[$menucounter]["html"][$itemcounter++] = '<input type="submit" name="add_my_list" style="border:0px;background-color:white;" value="'.getLL("mylist_add_selected").'" onclick="set_ids_from_chk(this);set_action('."'add_to_my_list'".', this)" /><br /><input type="submit" name="del_from_my_list" style="border:0px;background-color:white;" value="'.getLL("mylist_del_selected").'" onclick="set_ids_from_chk(this);set_action('."'del_from_my_list'".', this)" /><br />';
				//Add mailing link
				if(ko_module_installed('mailing') && $MAILING_PARAMETER['domain'] != '' && sizeof($_SESSION['my_list']) > 0) {
					$add = '&nbsp;&nbsp;<a href="mailto:ml@'.$MAILING_PARAMETER['domain'].'"><img src="'.$ko_path.'/images/send_email.png" border="0" title="'.getLL('mailing_send_email').'" /></a>';
				} else {
					$add = '';
				}
				$submenu[$menucounter]['output'][$itemcounter] = ko_menuitem('leute', 'show_my_list').' ('.sizeof($_SESSION['my_list']).')'.$add;
				$submenu[$menucounter]["no_ul"][$itemcounter] = FALSE;
				$submenu[$menucounter]["link"][$itemcounter++] = $ko_path."leute/index.php?action=show_my_list";
				$submenu[$menucounter]["output"][$itemcounter] = getLL("mylist_empty");
				$submenu[$menucounter]["no_ul"][$itemcounter] = FALSE;
				$submenu[$menucounter]["link"][$itemcounter++] = $ko_path."leute/index.php?action=clear_my_list";
			break;

			case "schnellfilter":
				$found = TRUE;
				$submenu[$menucounter]["titel"] = getLL("submenu_leute_title_schnellfilter");

				$submenu[$menucounter]['form'] = TRUE;
				$submenu[$menucounter]['form_hidden_inputs'] = array(0 => array('name' => 'action', 'value' => ''));

				$ajax1 = $ajax2 = "";
				$fast_filter = ko_get_fast_filter();
				foreach($fast_filter as $id) {
					ko_get_filter_by_id($id, $ff);
					$submenu[$menucounter]["output"][$itemcounter] = $ff["name"];
					$submenu[$menucounter]["link"][$itemcounter] = "";
					$ff_code = str_replace("var1", ("fastfilter".$id), $ff["code1"]);
					$ff_code = str_replace("submit_filter", ("submit_fast_filter"), $ff_code);
					$submenu[$menucounter]["html"][$itemcounter++] = $ff_code.'<br />';
					$ajax1 .= "fastfilter".$id.",";
					$ajax2 .= '\'+escape(this.form.fastfilter'.$id.'.value.trim())+\',';
				}
				$submenu[$menucounter]["html"][--$itemcounter] .= '<p align="center"><input type="submit" name="submit_fast_filter" value="'.getLL("OK").'" onclick="sendReq(\'../leute/inc/ajax.php\', \'action,'.$ajax1.'sesid\', \'leuteschnellfilter,'.$ajax2.session_id().'\', do_element); return false;" /></p>';
				$itemcounter++;
				//Show hidden
				if($max_rights > 0 && ko_get_setting("leute_hidden_mode") == 2) {
					$submenu[$menucounter]["output"][$itemcounter] = " ";
					$submenu[$menucounter]["link"][$itemcounter] = "";
					$submenu[$menucounter]["html"][$itemcounter++]  = '<input type="checkbox" id="chk_ff_hidden" name="chk_ff_hidden" '.(ko_get_userpref($_SESSION["ses_userid"], "leute_show_hidden") == 1 ? 'checked="checked"' : '').' onclick="sendReq(\'../leute/inc/ajax.php\', \'action,state,sesid\', \'showhidden,\'+this.checked+\','.session_id().'\', do_element);" /><label for="chk_ff_hidden">'.getLL("fastfilter_hidden")."</label><br />";
				}
				//Show deleted
				if($max_rights > 2) {
					$submenu[$menucounter]["output"][$itemcounter] = " ";
					$submenu[$menucounter]["link"][$itemcounter] = "";
					$submenu[$menucounter]["html"][$itemcounter++]  = '<input type="checkbox" id="chk_ff_deleted" name="chk_ff_deleted" '.(ko_get_userpref($_SESSION["ses_userid"], "leute_show_deleted") == 1 ? 'checked="checked"' : '').' onclick="sendReq(\'../leute/inc/ajax.php\', \'action,state,sesid\', \'showdeleted,\'+this.checked+\','.session_id().'\', do_element);" /><label for="chk_ff_deleted">'.getLL("fastfilter_deleted")."</label><br />";
				}
				//Versioning
				if($ENABLE_VERSIONING_FASTFILTER && $max_rights > 1) {
					$version_set = $_SESSION["leute_version"] ? TRUE : FALSE;
					$submenu[$menucounter]["output"][$itemcounter] = getLL("fastfilter_version");
					$submenu[$menucounter]["link"][$itemcounter] = "";
					$code  = $js_calendar->make_input_field(array(), array("name" => "date_version", "value" => sql2datum($_SESSION["leute_version"]), "size" => 12, "style" => ($version_set ? "background-color: #f4914a;" : "")));
					$code .= '<br /><input type="submit" name="submit_leute_version" onclick="set_action(\'submit_leute_version\', this);" value="'.getLL("OK").'" />';
					if($_SESSION["leute_version"]) $code .= '&nbsp;<input type="submit" name="submit_leute_version_clear" value="'.getLL("delete").'" onclick="set_action(\'clear_leute_version\', this);" />';
					$submenu[$menucounter]["html"][$itemcounter++] = $code;
				}
			break;

			case "aktionen":
				//Check whether to show "by setting" or not
				$show_def = FALSE;
				$db_cols = db_get_columns("ko_familie");
				foreach($db_cols as $col) if($col["Field"] == "famgembrief") $show_def = TRUE;

				$found = TRUE;
				$submenu[$menucounter]["titel"] = getLL("submenu_leute_title_aktionen");

				$submenu[$menucounter]['form'] = TRUE;
				$submenu[$menucounter]['form_hidden_inputs'] = array(array('name' => 'action', 'value' => ''),
																														 array('name' => 'id', 'value' => ''),
																														 array('name' => 'ids', 'value' => ''));

				$submenu[$menucounter]["output"][$itemcounter] = getLL("rows");
				$submenu[$menucounter]["link"][$itemcounter] = "";
				$html  = '<select name="sel_auswahl" size="0" style="width:170px;">';
				$html .= '<option value="allep" selected="selected">'.getLL("filtered").' ('.getLL("people").')</option>';
				if($_SESSION['show'] != 'geburtstagsliste' && !$LEUTE_NO_FAMILY) {
					$html .= '<option value="allef">'.getLL("filtered").' ('.getLL("families").')</option>';
					$html .= '<option value="alleFam2">'.getLL("filtered").' ('.getLL("families_2_or_more").')</option>';
					if($show_def) $html .= '<option value="alleDef">'.getLL("filtered").' ('.getLL("setting").')</option>';
				}
				$html .= '<option value="markierte">'.getLL("selected").'</option>';
				if($_SESSION['show'] != 'geburtstagsliste' && !$LEUTE_NO_FAMILY) {
					$html .= '<option value="markiertef">'.getLL("selected").' ('.getLL("families").')</option>';
					$html .= '<option value="markierteFam2">'.getLL("selected").' ('.getLL("families_2_or_more").')</option>';
				}
				$submenu[$menucounter]["html"][$itemcounter++] = $html."</select>";

				$submenu[$menucounter]["output"][$itemcounter++] = "";

				$submenu[$menucounter]["output"][$itemcounter] = getLL("columns");
				$submenu[$menucounter]["link"][$itemcounter] = "";
				$html  = '<select name="sel_cols" size="0" style="width:170px;">';
				$html .= '<option value="alle">'.getLL("all").'</option>';
				$html .= '<option value="angezeigte" selected="selected">'.getLL("shown").'</option>';
				if (!$LEUTE_NO_FAMILY) $html .= '<option value="children">'.getLL("children").'</option>';
				$itemset = array_merge((array)ko_get_userpref('-1', '', 'leute_itemset', 'ORDER BY `key` ASC'), (array)ko_get_userpref($_SESSION['ses_userid'], '', 'leute_itemset', 'ORDER BY `key` ASC'));
			  foreach($itemset as $i) {
					$value = $i['user_id'] == '-1' ? '@G@'.$i['key'] : $i['key'];
					$desc = $i['user_id'] == '-1' ? getLL('itemlist_global_short').' '.$i['key'] : $i['key'];
					$html .= '<option value="set_'.$value.'" title="'.ko_html($desc).'">&quot;'.$desc.'&quot;</option>';
				}
				$html .= '</select>';
				$submenu[$menucounter]["html"][$itemcounter++] = $html."<br />";

				//Add select to choose rectype
				if(sizeof($RECTYPES) > 0) {
					$submenu[$menucounter]["output"][$itemcounter] = getLL("leute_export_force_rectype");
					$submenu[$menucounter]["link"][$itemcounter] = "";
					$html  = '<select name="sel_rectype" size="0">';
					$html .= '<option value="">'.getLL("leute_export_rectype_defined").'</option>';
					$html .= '<option value="" disabled="disabled">------</option>';
					$html .= '<option value="_default">'.getLL("kota_ko_leute_rectype_default").'</option>';
					foreach($RECTYPES as $k => $v) {
						$html .= '<option value="'.$k.'">'.getLL('kota_ko_leute_rectype_'.$k).'</option>';
					}
					$html .= '</select>';
					$submenu[$menucounter]["html"][$itemcounter++] = $html."<br />";
				}

				$submenu[$menucounter]["output"][$itemcounter++] = "";
				$submenu[$menucounter]["output"][$itemcounter] = " ";
				$submenu[$menucounter]["link"][$itemcounter] = "";
				$html  = '<input type="image" src="'.$ko_path.'images/icon_excel.png" onclick="set_ids_from_chk(this);set_action(\'leute_action\', this);set_hidden_value(\'id\', \'excel\', this);" title="'.getLL("export_excel").'">&nbsp;';
				$html .= '<input type="image" src="'.$ko_path.'images/icon_excel_plus.png" onclick="set_ids_from_chk(this);set_action(\'leute_action\', this);set_hidden_value(\'id\', \'xls_settings\', this);" title="'.getLL("export_excel_settings").'">&nbsp;';
				$html .= '<input type="image" src="'.$ko_path.'images/icon_email.png" onclick="set_ids_from_chk(this);set_action(\'leute_action\', this);set_hidden_value(\'id\', \'email\', this);" title="'.getLL("export_email").'">&nbsp;';
				$html .= '<input type="image" src="'.$ko_path.'images/icon_etiketten.png" onclick="set_ids_from_chk(this);set_action(\'leute_action\', this);set_hidden_value(\'id\', \'etiketten\', this);" title="'.getLL("export_etiketten").'">&nbsp;';
				if(ko_latex_check()) {
					$html .= '<input type="image" src="'.$ko_path.'images/icon_mailmerge.png" onclick="set_ids_from_chk(this);set_action(\'leute_action\', this);set_hidden_value(\'id\', \'mailmerge\', this);" title="'.getLL("export_mailmerge").'">&nbsp;';
				}
				if(ko_module_installed('sms') && $SMS_PARAMETER['user'] != '' && $SMS_PARAMETER['pass'] != '') {
					$html .= '<input type="image" src="'.$ko_path.'images/icon_sms.png" onclick="set_ids_from_chk(this);set_action(\'leute_action\', this);set_hidden_value(\'id\', \'sms\', this);" title="'.getLL("export_sms").'">&nbsp;';
				}
				$html .= '<input type="image" src="'.$ko_path.'images/icon_vcard.png" onclick="set_ids_from_chk(this);set_action(\'leute_action\', this);set_hidden_value(\'id\', \'vcard\', this);" title="'.getLL("export_vcard").'">&nbsp;';
				if(extension_loaded('gd')) {
					$html .= '<a href="?action=leute_chart"><img src="'.$ko_path.'images/icon_chart.png" onclick="set_action(\'leute_action\', this);set_hidden_value(\'id\', \'chart\', this);" title="'.getLL("export_chart").'" border="0"></a>&nbsp;';
				}
				$html .= '<input type="image" src="'.$ko_path.'images/icon_csv.png" onclick="set_ids_from_chk(this);set_action(\'leute_action\', this);set_hidden_value(\'id\', \'csv\', this);" title="'.getLL('export_csv').'">&nbsp;';
				$submenu[$menucounter]["html"][$itemcounter++] = $html."<br />";

				//PDF Export layouts
				if($_SESSION['show'] != 'geburtstagsliste') {
					$pdf_layouts = db_select_data("ko_pdf_layout", "WHERE `type` = 'leute'", "id, name", "ORDER BY `name` ASC");
					if(sizeof($pdf_layouts) > 0) {
						$html = '<select name="pdf_layout_id" size="0" onchange="set_ids_from_chk(this);set_action(\'export_pdf\', this);jQuery(this).closest(\'form\').submit();">';
						$html .= '<option value=""></option>';
						foreach($pdf_layouts as $l) {
							$html .= '<option value="'.$l["id"].'">'.ko_html($l["name"]).'</option>';
						}
						$html .= '</select><br />';
						$submenu[$menucounter]["output"][$itemcounter] = getLL("leute_export_pdf");
						$submenu[$menucounter]["link"][$itemcounter] = "";
						$submenu[$menucounter]["html"][$itemcounter++] = $html;
					}
				}
			break;



			case "filter":
				$found = TRUE;
				//Show select with all saved filter presets
				$code_vorlage_open  = '<select name="sel_filterset" size="0" onchange="sendReq(\'../leute/inc/ajax.php\', \'action,name,sesid\', \'leuteopenfilterset,\'+this.options[this.selectedIndex].value+\','.session_id().'\', do_element);" style="width:150px;">';
				$code_vorlage_open .= '<option value=""></option>';
				//Reread userprefs from DB (using 5th param set to true. Otherwise newly created entries don't get returned
				$filterset = array_merge((array)ko_get_userpref('-1', "", "filterset", "ORDER BY `key` ASC", TRUE), (array)ko_get_userpref($_SESSION["ses_userid"], "", "filterset", "ORDER BY `key` ASC", TRUE));

				$current_filterset = NULL;
				foreach($filterset as $f) {
				  if(serialize($_SESSION["filter"]) == $f["value"]) {
						$current_filterset = $f;
						$sel = 'selected="selected"';
					} else {
						$sel = '';
					}
					$this_filter = unserialize($f["value"]);
					$global_tag = $f['user_id'] == '-1' ? getLL('leute_filter_global_short') : '';
					$value = $f['user_id'] == '-1' ? '@G@'.$f['key'] : $f['key'];
				  $label = $global_tag.' '.$f['key'].($this_filter['cols'] ? ' +' : '');
				  $code_vorlage_open .= '<option value="'.$value.'" '.$sel.' title="'.ko_html($label).'">'.$label.'</option>';
				}

				$code_vorlage_open .= '</select>';
				//Delete button
				$code_vorlage_open .= '&nbsp;<input type="image" src="'.$ko_path.'images/icon_trash.png" title="'.getLL("itemlist_delete_preset").'" onclick="c = confirm(\''.getLL("itemlist_delete_preset_confirm").'\');if(!c) return false; sendReq(\'../leute/inc/ajax.php\', \'action,name,sesid\', \'leutedelfilterset,\'+document.getElementsByName(\'sel_filterset\')[0].options[document.getElementsByName(\'sel_filterset\')[0].selectedIndex].value+\','.session_id().'\', do_element);return false;" /><br />';

				//Mailing:
				if(ko_module_installed('mailing') && $MAILING_PARAMETER['domain'] != '') {
					if(is_array($current_filterset)) {
						if($current_filterset['mailing_alias']) {
							$mailing_address = $current_filterset['mailing_alias'].'@'.$MAILING_PARAMETER['domain'];
						} else {
							$mailing_address = 'fp'.$current_filterset['id'].'@'.$MAILING_PARAMETER['domain'];
						}
						$code_vorlage_open .= '<div>'.getLL('module_mailing').':&nbsp;&nbsp;';
						$code_vorlage_open .= '<a href="mailto:'.$mailing_address.'">
																	 <img src="'.$ko_path.'/images/send_email.png" border="0" title="'.getLL('mailing_send_email').'" />
																	 </a>';
						$code_vorlage_open .= '&nbsp;&nbsp;<img src="'.$ko_path.'/images/button_edit.gif" border="0" title="'.getLL('mailing_edit_settings').'" id="fp_alias_switch" class="cursor_pointer" />';
						$code_vorlage_open .= '<div style="display: none;" id="fp_alias_container">';
						$code_vorlage_open .= getLL('mailing_alias').': ';
						$code_vorlage_open .= '<input type="text" size="12" name="txt_fp_alias" id="fpalias" value="'.$current_filterset['mailing_alias'].'" />';
						$code_vorlage_open .= '<input type="image" src="'.$ko_path.'images/icon_save.gif" title="'.getLL('save').'" onclick="sendReq(\''.$ko_path.'leute/inc/ajax.php\', \'action,fpid,alias,sesid\', \'savefpalias,'.$current_filterset['id'].',\'+$(\'#fpalias\').val()+\','.session_id().'\', do_element); return false;" />';
						$code_vorlage_open .= '</div>';

						$code_vorlage_open .= '</div>';
					}
				}

				//Show list of available filters. Mark active one
				$hide_filter = explode(",", ko_get_userpref($_SESSION["ses_userid"], "hide_leute_filter"));
				foreach($hide_filter as $h_i => $h) if(!$h) unset($hide_filter[$h_i]);
				ko_get_filters($f_, "leute", FALSE, 'group');
				$counter = 0;
				$cg = '';
				$f_[] = array('group' => 'dummy', 'code1' => 'dummycode');
				$code_filterliste .= '<div style="clear: both; height: 6px;"></div>';
				foreach($f_ as $fi => $ff) {
					//Skip hidden filters
					if(in_array($fi, $hide_filter)) continue;
					//Don't show filters with no code1 (can be used for hidden filters only used in manually stored preset)
					if($ff['code1'] == '') continue;

					if($ff['group'] != $cg) {
						if($group_code) {
							$style = $active ? 'style="display:block;"' : 'style="display:none;"';
							$class = $active ? 'filter-divider-active' : '';
							$code_filterliste .= '<div class="filter-divider '.$class.'" id="'.$cg.'">&raquo;&nbsp;'.getLL('filter_group_'.$cg).'</div>';
							$code_filterliste .= '<div class="filter-group" id="fg'.$cg.'" '.$style.'>'.$group_code.'</div>';
						}
						$cg = $ff['group'];
						$group_code = '';
						$active = FALSE;
					}
					if($_SESSION['filter_akt'] == $ff['id']) {
						$active = TRUE;
						$class = 'filter-active';
					} else {
						$class = 'filter-button';
					}
					$group_code .= '<div class="'.$class.'" onclick="sendReq(\'../leute/inc/ajax.php\', \'action,fid,sesid\', \'leutefilterform,'.$ff['id'].','.session_id().'\', do_element);" title="'.$ff['name'].'">';
					$group_code .= '<div class="filter-text"><span>'.$ff['name'].'</span></div>';
					$group_code .= '</div>';
				}

				if(sizeof($hide_filter) > 0) {
					//Show hidden filters
					$group_code = "";
					$counter = 0;
					$active = FALSE;
					foreach($f_ as $fi => $ff) {
						//Skip not hidden filters
						if(!in_array($fi, $hide_filter)) continue;

						if($_SESSION['filter_akt'] == $ff['id']) {
							$active = TRUE;
							$class = 'filter-active';
						} else {
							$class = 'filter-button';
						}
						$group_code .= '<div class="'.$class.'" onclick="sendReq(\'../leute/inc/ajax.php\', \'action,fid,sesid\', \'leutefilterform,'.$ff['id'].','.session_id().'\', do_element);">'.$ff['name'].'</div>';
					}
					if($group_code) {
						$style = $active ? 'style="display:block;"' : 'style="display:none;"';
						$class = $active ? 'filter-divider-active' : '';
						$code_filterliste .= '<div class="filter-divider '.$class.'" id="hidden">&raquo;&nbsp;'.getLL('filter_group_hidden').'</div>';
						$code_filterliste .= '<div class="filter-group" id="fghidden" '.$style.'>'.$group_code.'</div>';
					}
				}

				$code_filterliste .= '<div style="clear: both; height: 6px;"></div>';


				//Variablen zu aktivem Filter anzeigen
				$filter_form_code = ko_get_leute_filter_form($_SESSION["filter_akt"]);

				//Vorlage speichern
				$code_vorlage_save  = '<div style="font-size:9px;"><input type="checkbox" name="chk_filterset_new_with_col" id="chk_filterset_new_with_col" value="1" /><label for="chk_filterset_new_with_col">'.getLL('leute_filter_save_with_cols').'</label></div>';
				if($max_rights > 3) $code_vorlage_save .= '<div style="font-size:9px;"><input type="checkbox" name="chk_filterset_global" id="chk_filterset_global" value="1" /><label for="chk_filterset_global">'.getLL('leute_filter_global').'</label></div>';
				$code_vorlage_save .= '<input type="text" name="txt_filterset_new" id="txt_filterset_new" size="15" />';
				//Save filter for other users
				if($max_rights > 3) {
					$code_vorlage_save .= '&nbsp;<input type="image" src="'.$ko_path.'images/icon_save.gif" title="'.getLL("itemlist_save_preset").'" onclick="sendReq(\'../leute/inc/ajax.php\', \'action,name,withcols,logins,global,sesid\', \'leutesavefilterset,\'+document.getElementById(\'txt_filterset_new\').value+\',\'+document.getElementById(\'chk_filterset_new_with_col\').checked+\',\'+getMultiple(document.getElementById(\'sel_filter_for_logins\'))+\',\'+document.getElementById(\'chk_filterset_global\').checked+\','.session_id().'\', do_element);return false;" />';
					$code_vorlage_save .= '&nbsp;&nbsp;<img src="'.$ko_path.'images/ds_down.gif" onclick="change_vis(\'filter_for_logins\');" alt="options" style="cursor: pointer;" /><br />';

					$code_vorlage_save .= '<div name="filter_for_logins" id="filter_for_logins" style="display:none; visibility:hidden; background:#f1f3f5; width:100%;">';
					$code_vorlage_save .= getLL("leute_filter_save_for_logins")."<br />";
					$code_vorlage_save .= '<select name="sel_filter_for_logins" id="sel_filter_for_logins" size="5" multiple="multiple">';
					$logins_where = ($_SESSION["ses_userid"] != ko_get_root_id() ? "AND `id` != '".ko_get_root_id()."' " : "");
					$logins_where .= " AND (`disabled` = '' OR `disabled` = '0')";
					ko_get_logins($logins, $logins_where);
					foreach($logins as $login) {
						if(!ko_module_installed("leute", $login["id"])) continue;
						$code_vorlage_save .= '<option value="'.$login["id"].'">'.$login["login"].'</option>';
					}
					$code_vorlage_save .= '</select>';
					$code_vorlage_save .= '</div>';
				} else {
					$code_vorlage_save .= '&nbsp;<input type="image" src="'.$ko_path.'images/icon_save.gif" title="'.getLL("itemlist_save_preset").'" onclick="sendReq(\'../leute/inc/ajax.php\', \'action,name,withcols,sesid\', \'leutesavefilterset,\'+document.getElementById(\'txt_filterset_new\').value+\',\'+document.getElementById(\'chk_filterset_new_with_col\').checked+\','.session_id().'\', do_element);return false;" />';
					$code_vorlage_save .= "<br />";
				}


				//Angewandte Filter anzeigen
				if(sizeof($_SESSION["filter"]) > 0) {
					$found_filter = FALSE;
				  foreach($_SESSION["filter"] as $f_i => $f) {
						if(!is_numeric($f_i)) continue;

						if(!$found_filter) {
							$found_filter = TRUE;
							$code_akt_filter = '<hr width="100%">';
							$size = 0;
							foreach($_SESSION['filter'] as $k => $v) {
								if(!is_numeric($k)) continue;
								$size++;
							}
							$code_akt_filter .= '<b>&raquo;&nbsp;'.getLL('leute_filter_current').':</b><br />';
							$code_akt_filter .= '<select size="'.max(2, $size).'" name="sel_filter" id="sel_filter" style="width: 170px;">';
						}

				    ko_get_filter_by_id($f[0], $f_);

						//Name des Filters
				    $f_name = $f_["name"];

						//Tabellen-Name, auf den dieser Filter am ehesten wirkt, auslesen/erraten:
						for($c=1; $c<4; $c++) {
							list($col[$c]) = explode(' ', $f_['sql'.$c]);
						}

						//Variablen auslesen
				    $vars = "";
						$fulltitle = '';
						$t1 = $t2 = '';
				    for($i = 1; $i <= sizeof($f[1]); $i++) {
							$v = map_leute_daten($f[1][$i], ($col[$i] ? $col[$i] : $col[1]), $t1, $t2, FALSE, array('num' => $i));
							//Limit length of group name for filter list
							if(!$fulltitle) $fulltitle = $v;
							else $fulltitle .= ', '.$v;
							if($col[$i] == 'groups' && strlen($v) > 25) {
								$v = substr($v, 0, 10).'[..]'.substr($v, -10);
							}
							$vars .= $v.', ';
						}
				    $vars = substr($vars, 0, -2);

				    //Negative Filter markieren
				    if($f[2] == 1) $neg = "!";
				    else $neg = "";

				    $label = ko_html($f_i.':'.$f_name.': '.$neg.$vars);
				    $fulltitle = ko_html($f_name.': '.$neg.$fulltitle);
				    $code_akt_filter .= '<option value="'.$f_i.'" title="'.$fulltitle.'">'.$label.'</option>';
				  }//foreach(filter)

				  if($found_filter) {
						$code_akt_filter .= '</select>';

						if($_SESSION['filter']['use_link_adv'] === TRUE && $_SESSION['filter']['link_adv'] != '') {
							$filter_link['and'] = $filter_link['or'] = '';
							$vis_link = 'style="display: none; visibility: hidden;"';
							$vis_link_adv = '';
						} else {
							$filter_link['and'] = ($_SESSION['filter']['link'] == 'and') ? 'checked="checked"' : '';
							$filter_link['or']  = ($_SESSION['filter']['link'] == 'or')  ? 'checked="checked"' : '';
							$vis_link = '';
							$vis_link_adv = 'style="display: none; visibility: hidden;"';
						}

						if($_SESSION['filter']['link_adv'] != '' && $_SESSION['filter']['use_link_adv'] !== TRUE) {
							$class_link_adv = 'class="filter_link_adv_error"';
							$title_link_adv = getLL('leute_filter_link_adv_error');
						} else if($_SESSION['filter']['link_adv'] != '' && $_SESSION['filter']['use_link_adv'] === TRUE) {
							$class_link_adv = 'class="filter_link_adv_ok"';
							$title_link_adv = getLL('leute_filter_link_adv_ok');
						}

						$code_akt_filter .= '<br />'.getLL('filter_combine').':';
						$code_akt_filter .= '&nbsp;<img src="'.$ko_path.'images/textfield_link.png" border="0" onclick="change_vis(\'filter_link\'); change_vis(\'adv_filter_link\'); change_vis(\'adv_filter_link_help\');" style="cursor: pointer;" title="'.getLL('leute_filter_link_adv_title').'" />';
						$help = ko_get_help('leute', 'filter_link_adv', array('ph' => ($position == 'right' ? 'l' : 'r')));
						if($help['show']) $code_akt_filter .= '&nbsp;<span id="adv_filter_link_help" '.$vis_link_adv.'>'.$help['link'].'</span>&nbsp;';

						//Regular filter link with AND/OR
						$code_akt_filter .= '<div id="filter_link" '.$vis_link.'>';
						$code_akt_filter .= '<input type="radio" name="rd_filter_link" id="rd_filter_link_and" value="and" '.$filter_link['and'].' onclick="sendReq(\'../leute/inc/ajax.php\', \'action,link,sesid\', \'leutefilterlink,and,'.session_id().'\', do_element);" />';
						$code_akt_filter .= '<label for="rd_filter_link_and">'.getLL('filter_AND').'</label>';
						$code_akt_filter .= '&nbsp;&nbsp;<input type="radio" name="rd_filter_link" id="rd_filter_link_or" value="or" '.$filter_link['or'].' onclick="sendReq(\'../leute/inc/ajax.php\', \'action,link,sesid\', \'leutefilterlink,or,'.session_id().'\', do_element);" />';
						$code_akt_filter .= '<label for="rd_filter_link_or">'.getLL('filter_OR').'</label>';
						$code_akt_filter .= '</div>';
						//Input for advanced linking
						$code_akt_filter .= '<div id="adv_filter_link" '.$vis_link_adv.'>';
						$code_akt_filter .= '<input type="text" id="input_filter_link_adv" style="width: 122px;" '.$class_link_adv.' title="'.$title_link_adv.'" value="'.$_SESSION['filter']['link_adv'].'" />';
						$code_akt_filter .= '<button style="width: 35px;" onclick="sendReq(\'../leute/inc/ajax.php\', \'action,link,sesid\', \'leutefilterlinkadv,\'+document.getElementById(\'input_filter_link_adv\').value+\','.session_id().'\', do_element); return false;">'.getLL('OK').'</button>';
						$code_akt_filter .= '</div>';
						//Buttons to delete applied filters
						$code_akt_filter .= '<p align="center">';
						$code_akt_filter .= '<input type="button" value="'.getLL('delete').'" name="submit_del_filter" onclick="sendReq('."'../leute/inc/ajax.php', 'action,id,sesid', 'leutefilterdel,'+document.getElementById('sel_filter').options[document.getElementById('sel_filter').selectedIndex].value+',".session_id()."', do_element);".'" />';
						$code_akt_filter .= '&nbsp;<input type="button" value="'.getLL('delete_all').'" name="submit_del_all_filter" onclick="sendReq(\'../leute/inc/ajax.php\', \'action,sesid\', \'leutefilterdelall,'.session_id().'\', do_element);" />';
						$code_akt_filter .= '</p>';
					}
				}//if(sizeof(filter)>0)



				$submenu[$menucounter]['form'] = TRUE;
				$submenu[$menucounter]['form_hidden_inputs'] = array(array('name' => 'action', 'value' => ''),
																														 array('name' => 'ids', 'value' => ''));

				$submenu[$menucounter]["titel"] = getLL("submenu_daten_title_filter");
				$submenu[$menucounter]["output"][$itemcounter] = '<strong><big>&middot;</big></strong>'.getLL("itemlist_open_preset").':';
				$submenu[$menucounter]["no_ul"][$itemcounter] = TRUE;
				$submenu[$menucounter]["html"][$itemcounter++] = $code_vorlage_open;
				$submenu[$menucounter]["output"][$itemcounter] = " ";
				$submenu[$menucounter]["no_ul"][$itemcounter] = TRUE;
				$submenu[$menucounter]["html"][$itemcounter++] = $code_filterliste;
				$submenu[$menucounter]["output"][$itemcounter] = " ";
				$submenu[$menucounter]["no_ul"][$itemcounter] = TRUE;
				$submenu[$menucounter]["html"][$itemcounter++] = $filter_form_code;
				$submenu[$menucounter]["output"][$itemcounter] = '<strong><big>&middot;</big></strong>'.getLL("itemlist_save_preset");
				$submenu[$menucounter]["no_ul"][$itemcounter] = TRUE;
				$submenu[$menucounter]["html"][$itemcounter++] = $code_vorlage_save;
				$submenu[$menucounter]["output"][$itemcounter] = " ";
				$submenu[$menucounter]["no_ul"][$itemcounter] = TRUE;
				$submenu[$menucounter]["html"][$itemcounter++] = $code_akt_filter;

			break;

			case "itemlist_spalten":
				$found = TRUE;

			  //Alle Spalten auslesen
			  $counter = 0;
			  $cols = ko_get_leute_col_name(TRUE, TRUE, 'view', FALSE, $rawgdata);
				$groups = $smallgroups = FALSE;
				$open_ids = $only_open_ids = array();
			  foreach($cols as $i_i => $i) {
			    if($i == '') continue;

		      $aktiv = 0;
		      foreach($_SESSION["show_leute_cols"] as $s) if($s == $i_i) $aktiv = 1;

					//Add title above first smallgroup (disabled)
					if(substr($i_i, 0, 8) == 'MODULEkg' && !$smallgroups) {
						$smallgroups = TRUE;
						$itemlist[$counter]['name'] = '---'.getLL('module_kg').'---';
						$itemlist[$counter]['params'] = 'disabled="disabled"';
						$itemlist[$counter++]['value'] = '';
					}
					//Add title above first group (disabled)
					if(substr($i_i, 0, 9) == "MODULEgrp" && !$groups) {
						$groups = TRUE;
						$itemlist[$counter]["name"] = "---".getLL("groups")."---";
						$itemlist[$counter]["params"] = 'disabled="disabled"';
						$itemlist[$counter++]["value"] = "";
					}

					//Group columns: only collect group ids to set the state below
					if($groups) {
						if($aktiv) {
							if(FALSE === strpos($i_i, ':')) {
								$open_ids[] = substr($i_i, 9);
							} else {
								$open_dfs[] = substr($i_i, 9);
								//Add group id to open, so group will show opened also if only df is selected
								$only_open_ids[] = substr($i_i, 9, 6);
							}
						}
						continue;
					}

					//Einrückung bei Gruppen-Spalten separat übergeben, damit es nicht zur truncate-Länge (Smarty) gezählt wird
					$pre = $name = "";
					while(substr($i, 0, 6) == "&nbsp;") {
						$pre .= "&nbsp;";
						$i = substr($i, 6);
					}

		      $itemlist[$counter]["prename"] = $pre;
		      $itemlist[$counter]["name"] = $i;
		      $itemlist[$counter]["aktiv"] = $aktiv;
					if(substr($i, 0, 3) == '---') $itemlist[$counter]['params'] = 'disabled="disabled"';
		      $itemlist[$counter++]["value"] = $i_i;
			  }//foreach(cols)

				//Get IDs of groups to be shown opened (including whole motherline)
				if(!is_array($all_groups)) ko_get_groups($all_groups);
				$open_gids = array();
				$_open_ids = array_merge((array)$open_ids, (array)$only_open_ids);
				if(sizeof($_open_ids) > 0) {
					$_open_ids = array_unique($_open_ids);
					foreach($_open_ids as $id) {
						$ml = ko_groups_get_motherline($id, $all_groups);
						$open_gids[] = $id;
						foreach($ml as $m) {
							$open_gids[] = $m;
						}
					}
				}

				//Find groupIDs of leaves
				$gids = db_select_distinct('ko_groups', 'id');
				$not_leaves = db_select_distinct('ko_groups', 'pid');
				$leaves = array_diff($gids, $not_leaves);
				unset($gids); unset($not_leaves);

				//Build hierarchical ul
				$c = '<ul class="gtree gtree_NULL">';
				$cl = 0;
				$first = TRUE;
				foreach($rawgdata as $g) {
					$gtstate = in_array($g['id'], $open_gids) ? 'open' : 'closed';
					$pidstate = in_array($g['pid'], $open_gids) ? 'open' : 'closed';
					$dfstate = 'closed';

					//Find selected df to set state
					if(is_array($g['df']) && sizeof($g['df']) > 0) {
						foreach($g['df'] as $df) {
							if(in_array($g['id'].':'.$df['id'], $open_dfs)) $dfstate = 'open';
						}
					}

					if($first) {
						$first = FALSE;
					} else if($g['depth'] > $cl) {
						$c .= '<ul class="gtree gtree_'.$g['depth'].' gtree_state_'.$pidstate.'" id="u'.$g['pid'].'">';
					} else if($g['depth'] == $cl) {
						$c .= '</li>';
					} else if($g['depth'] < $cl) {
						while($cl > $g['depth']) {
							$c .= '</li></ul>';
							$cl--;
						}
					}

					$leaf = '';
					if(in_array($g['id'], $leaves)) {
						if(!is_array($g['df'])) {
							$leaf = 'gtree_leaf';
						} else {
							if($dfstate == 'closed') {
								$gtstate = 'closed';
							} else {
								$gtstate = 'open';
							}
						}
					}
					$leaf = (in_array($g['id'], $leaves) && !is_array($g['df'])) ? 'gtree_leaf' : '';
					$c .= '<li class="gtree gtree_'.$g['depth'].' gtree_state_'.$gtstate.' '.$leaf.'" id="g'.$g['id'].'">';
					$checked = in_array($g['id'], $open_ids) ? 'checked="checked"' : '';
					$c .= '<input type="checkbox" class="itemlist_chk" id="MODULEgrp'.$g['id'].'" '.$checked.' />';
					if($leaf != '') {
						$c .= '<label for="MODULEgrp'.$g['id'].'">'.ko_html($g['name']).'</label>';
					} else {
						$c .= ko_html($g['name']);
					}

					//Datafields
					if(is_array($g['df']) && sizeof($g['df']) > 0) {
						$c .= '<ul class="gtree_datafield gtree_state_'.$dfstate.'">';
						foreach($g['df'] as $df) {
							$c .= '<li class="gtree gtree_datafield">';
							$checked = in_array($g['id'].':'.$df['id'], $open_dfs) ? 'checked="checked"' : '';
							$c .= '<input type="checkbox" class="itemlist_chk" id="MODULEgrp'.$g['id'].':'.$df['id'].'" '.$checked.' />';
							$c .= '<label for="MODULEgrp'.$g['id'].':'.$df['id'].'">'.ko_html($df['description']).'</label>';
							$c .= '</li>';
						}
						$c .= '</ul>';
					}

					$cl = $g['depth'];
				}
				$c .= '</li></ul>';

				$itemlist[$counter]['type'] = 'html';
				$itemlist[$counter++]['html'] = $c;


				//Tracking
				if(ko_module_installed('tracking')) {
					if(!is_array($access['tracking'])) ko_get_access('tracking');
					$c  = '<ul class="gtree gtree_NULL">';
					$filters = db_select_data('ko_userprefs', 'WHERE `type` = \'tracking_filterpreset\' and (`user_id` = ' . $_SESSION['ses_userid'] . ' or `user_id` = -1)', '`id`,`key`,`value`');
					$tgs = db_select_data('ko_tracking_groups', 'WHERE 1', '*', 'ORDER BY name ASC');
					array_unshift($tgs, array('id' => 0, 'name' => getLL('tracking_itemlist_no_group')));
					foreach($tgs as $tg) {
						$trackings = db_select_data('ko_tracking', "WHERE `group_id` = '".$tg['id']."'", '*', 'ORDER BY name');
						$tg_code = '';
						$group_state = 'closed';
						foreach($trackings as $t) {
							if($access['tracking']['ALL'] < 1 && $access['tracking'][$t['id']] < 1) continue;

							//Check for selected tracking
							$tracking_checked = '';
							foreach($_SESSION['show_leute_cols'] as $s) {
								if($s == 'MODULEtracking'.$t['id']) {
									$group_state = 'open';
									$checked = 'checked="checked"';
								}
							}

							$tracking_state = 'closed';
							$fr_code = '';
							foreach ($filters as $filter) {
								foreach($_SESSION['show_leute_cols'] as $s) {
									if($s == 'MODULEtracking'.$t['id'].'f'.$filter['id']) {
										$group_state = 'open';
										$tracking_state = 'open';
										$filter_checked = 'checked="checked"';
									}
								}

								$fr_code .= '<li class="gtree gtree_1 gree_state_open gtree_leaf" id="t'.$t['id'].'f'.$filter['id'].'">';
								$fr_code .= '<input type="checkbox" class="itemlist_chk" id="MODULEtracking'.$t['id'].'f'.$filter['id'].'" '.$filter_checked.' />';
								$fr_code .= '<label for="MODULEtracking'.$t['id'].'f'.$filter['id'].'">'.ko_html($filter['key']).'</label>';

								$fr_code .= '</li>';

							}


							$tg_code .= '<li class="gtree gtree_0 gree_state_' . $tracking_state .' gtree_state_' . $tracking_state .'" id="t'.$t['id'].'">';
							$tg_code .= '<input type="checkbox" class="itemlist_chk" id="MODULEtracking'.$t['id'].'" '.$checked.' />';
							$tg_code .= '<label for="MODULEtracking'.$t['id'].'">'.ko_html($t['name']).'</label>';

							$tg_code .= '<ul class="gtree gtree_1 gtree_state_'.$tracking_state.'" id="tru'.$t['id'].'">';
							$tg_code .= $fr_code;
							$tg_code .= '</ul>';

							$tg_code .= '</li>';
						}
						if($tg_code != '') {
							$c .= '<li class="gtree gtree_0 gtree_state_'.$group_state.'" id="tg'.$tg['id'].'">';
							$c .= $tg['name'];

							$c .= '<ul class="gtree gtree_1 gtree_state_'.$group_state.'" id="tu'.$tg['id'].'">';
							$c .= $tg_code;
							$c .= '</ul>';

							$c .= '</li>';
						}
					}
					$c .= '</ul>';

					//Add title above trackings (disabled)
					$itemlist[$counter]['name'] = '---'.getLL('module_tracking').'---';
					$itemlist[$counter]['params'] = 'disabled="disabled"';
					$itemlist[$counter++]['value'] = '';

					$itemlist[$counter]['type'] = 'html';
					$itemlist[$counter++]['html'] = $c;
				}




				//Donations
				if(ko_module_installed('donations')) {
					if(!is_array($access['donations'])) ko_get_access('donations');
					$accounts = db_select_data('ko_donations_accounts', 'WHERE 1=1', '*', 'ORDER BY `name` ASC');
					$c  = '<ul class="gtree gtree_NULL">';
					$years = db_select_distinct('ko_donations', "YEAR(`date`)", "ORDER BY `date` DESC");
					foreach($years as $year) {
						$tg_code = '';
						$group_state = 'closed';
						foreach($accounts as $account) {
							if($access['donations']['ALL'] < 1 && $access['donations'][$account['id']] < 1) continue;

							//Check for selected account
							$checked = '';
							foreach($_SESSION['show_leute_cols'] as $s) if($s == 'MODULEdonations'.$year.$account['id']) {
								$group_state = 'open';
								$checked = 'checked="checked"';
							}

							$tg_code .= '<li class="gtree gtree_1 gree_state_open gtree_leaf" id="t'.$account['id'].'">';
							$tg_code .= '<input type="checkbox" class="itemlist_chk" id="MODULEdonations'.$year.$account['id'].'" '.$checked.' />';
							$tg_code .= '<label for="MODULEdonations'.$year.$account['id'].'">'.ko_html($account['name']).'</label>';

							$tg_code .= '</li>';
						}
						if($tg_code != '') {
							//Check for selected account
							$checked = '';
							foreach($_SESSION['show_leute_cols'] as $s) if($s == 'MODULEdonations'.$year) {
								$checked = 'checked="checked"';
							}

							$c .= '<li class="gtree gtree_0 gtree_state_'.$group_state.'" id="tg'.$year.'">';
							$c .= '<input type="checkbox" class="itemlist_chk" id="MODULEdonations'.$year.'" '.$checked.' />';
							$c .= $year;

							$c .= '<ul class="gtree gtree_1 gtree_state_'.$group_state.'" id="tu'.$year.'">';
							$c .= $tg_code;
							$c .= '</ul>';

							$c .= '</li>';
						}
					}
					$c .= '</ul>';

					//Add title above years (disabled)
					$itemlist[$counter]['name'] = '---'.getLL('module_donations').'---';
					$itemlist[$counter]['params'] = 'disabled="disabled"';
					$itemlist[$counter++]['value'] = '';

					$itemlist[$counter]['type'] = 'html';
					$itemlist[$counter++]['html'] = $c;
				}



			  $smarty->assign("tpl_itemlist_select", $itemlist);


			  //Alle Vorlagen auslesen
			  $akt_value = implode(",", $_SESSION["show_leute_cols"]);
			  $itemset = array_merge((array)ko_get_userpref('-1', '', 'leute_itemset', 'ORDER BY `key` ASC'), (array)ko_get_userpref($_SESSION["ses_userid"], "", "leute_itemset", 'ORDER BY `key` ASC'));
			  foreach($itemset as $i) {
			    $value = $i['user_id'] == '-1' ? '@G@'.$i['key'] : $i['key'];
			    $itemselect_values[] = $value;
			    $itemselect_output[] = $i['user_id'] == '-1' ? getLL('itemlist_global_short').' '.$i['key'] : $i['key'];
			    if($i["value"] == $akt_value) $itemselect_selected = $value;
			  }
			  $smarty->assign("tpl_itemlist_values", $itemselect_values);
			  $smarty->assign("tpl_itemlist_output", $itemselect_output);
			  $smarty->assign("tpl_itemlist_selected", $itemselect_selected);
			  if($_SESSION['show'] != 'geburtstagsliste') $smarty->assign("show_sort_cols", TRUE);
			  $smarty->assign("sort_cols_checked", ko_get_userpref($_SESSION["ses_userid"], "sort_cols_leute") == "0" ? '' : 'checked="checked"');
				if($max_rights > 3) $smarty->assign('allow_global', TRUE);

				$submenu[$menucounter]["titel"] = getLL("submenu_leute_title_itemlist_spalten");
        if($state == "closed") continue;

        $submenu[$menucounter]["output"][$itemcounter++] = "[itemlist]";
			break;


			case "kg":
				if(ko_module_installed("kg") && $kg_all_rights > 0) {
					$found = TRUE;
					$submenu[$menucounter]["titel"] = getLL("submenu_leute_title_kg");
					if($kg_all_rights > 3) {
						$submenu[$menucounter]["output"][$itemcounter] = ko_menuitem("leute", "neue_kg");
						$submenu[$menucounter]["link"][$itemcounter++] = $ko_path."leute/index.php?action=neue_kg";
					}
					$submenu[$menucounter]["output"][$itemcounter] = ko_menuitem("leute", "list_kg");
					$submenu[$menucounter]["link"][$itemcounter++] = $ko_path."leute/index.php?action=list_kg";

					$submenu[$menucounter]['output'][$itemcounter++] = '';

					if($kg_all_rights > 1 && extension_loaded('gd')) {
						$submenu[$menucounter]["output"][$itemcounter] = ko_menuitem("leute", "chart_kg");
						$submenu[$menucounter]["link"][$itemcounter++] = $ko_path."leute/index.php?action=chart_kg";
					}

					if($kg_all_rights > 1) {
						$submenu[$menucounter]['output'][$itemcounter] = ' ';
						$submenu[$menucounter]['no_ul'][$itemcounter] = TRUE;
						$submenu[$menucounter]['link'][$itemcounter] = '';
						$c = '<select name="sel_xls_cols" size="0" onchange="jumpToUrl(\'?action=kg_xls_export&sel_xls_cols=\'+this.options[this.selectedIndex].value);">';
						$c .= '<option value="">'.getLL('submenu_leute_kg_xls_export').'</option>';
						$c .= '<option value="" disabled="disabled">--- '.getLL('columns').' ---</option>';
						$c .= '<option value="_all">'.getLL('all_columns').'</option>';
						$c .= '<option value="_session">'.getLL('shown_columns').'</option>';
						$itemset = array_merge((array)ko_get_userpref('-1', '', 'leute_kg_itemset', 'ORDER by `key` ASC'), (array)ko_get_userpref($_SESSION['ses_userid'], '', 'leute_kg_itemset', 'ORDER by `key` ASC'));
						foreach($itemset as $i) {
							$value = $i['user_id'] == '-1' ? '@G@'.$i['key'] : $i['key'];
							$output = $i['user_id'] == '-1' ? getLL('itemlist_global_short').' '.$i['key'] : $i['key'];
							$c .= '<option value="'.$value.'">"'.$output.'"</option>';
						}
						$c .= '</select><br />';
						$submenu[$menucounter]['html'][$itemcounter++] = $c;
					}

				}
			break;


			case 'itemlist_spalten_kg':
				if(ko_module_installed('kg') && $kg_all_rights > 0) {
					$found = TRUE;

					//Alle Spalten auslesen
					$counter = 0;
					$cols = $KOTA['ko_kleingruppen']['_listview'];
					
					foreach($cols as $col) {
						$aktiv = 0;
						foreach($_SESSION['kota_show_cols_ko_kleingruppen'] as $s) if($s == $col['name']) $aktiv = 1;
						$itemlist[$counter]['name'] = getLL('kota_listview_ko_kleingruppen_'.$col['name']);
						$itemlist[$counter]['aktiv'] = $aktiv;
						$itemlist[$counter++]['value'] = $col['name'];
					}//foreach(items)
					$smarty->assign('tpl_itemlist_select', $itemlist);

					//Get all presets
					$akt_value = implode(',', $_SESSION['kota_show_cols_ko_kleingruppen']);
					$itemset = array_merge((array)ko_get_userpref('-1', '', 'leute_kg_itemset', 'ORDER BY `key` ASC'), (array)ko_get_userpref($_SESSION['ses_userid'], '', 'leute_kg_itemset', 'ORDER BY `key` ASC'));
					foreach($itemset as $i) {
						$value = $i['user_id'] == '-1' ? '@G@'.$i['key'] : $i['key'];
						$itemselect_values[] = $value;
						$itemselect_output[] = $i['user_id'] == '-1' ? getLL('itemlist_global_short').' '.$i['key'] : $i['key'];
				    if($i['value'] == $akt_value) $itemselect_selected = $value;
					}
					$smarty->assign('tpl_itemlist_values', $itemselect_values);
					$smarty->assign('tpl_itemlist_output', $itemselect_output);
					$smarty->assign('tpl_itemlist_selected', $itemselect_selected);
					if($kg_all_rights > 2) $smarty->assign('allow_global', TRUE);

					$submenu[$menucounter]['titel'] = getLL('submenu_leute_title_itemlist_spalten_kg');
					if($state == 'closed') continue;

					$submenu[$menucounter]['output'][$itemcounter++] = '[itemlist]';
				}
			break;



			case "itemlist_chart":
				if($_SESSION["show"] != "chart") continue;

				$found = TRUE;
				$counter = 0;
				foreach($LEUTE_CHART_TYPES as $type) {
					$itemlist[$counter]["name"] = getLL("leute_chart_title_".$type);
					$itemlist[$counter]["aktiv"] = in_array($type, $_SESSION["show_leute_chart"]) ? 1 : 0;
					$itemlist[$counter++]["value"] = $type;
				}
				$smarty->assign("tpl_itemlist_select", $itemlist);

				//Get user's presets
				$akt_value = implode(",", $_SESSION["show_leute_chart"]);
				$itemset = array_merge((array)ko_get_userpref('-1', '', 'leute_chart_itemset', 'ORDER BY `key` ASC'), (array)ko_get_userpref($_SESSION['ses_userid'], '', 'leute_chart_itemset', 'ORDER BY `key` ASC'));
				sort($itemset);
				foreach($itemset as $i) {
					$value = $i['user_id'] == '-1' ? '@G@'.$i['key'] : $i['key'];
					$itemselect_values[] = $value;
					$itemselect_output[] = $i['user_id'] == '-1' ? getLL('itemlist_global_short').' '.$i['key'] : $i['key'];
			    if($i["value"] == $akt_value) $itemselect_selected = $value;
				}
				$smarty->assign("tpl_itemlist_values", $itemselect_values);
				$smarty->assign("tpl_itemlist_output", $itemselect_output);
				$smarty->assign("tpl_itemlist_selected", $itemselect_selected);
					if($kg_all_rights > 2) $smarty->assign('allow_global', TRUE);

				$submenu[$menucounter]["titel"] = getLL("submenu_leute_title_itemlist_chart");
				if($state == "closed") continue;

				$submenu[$menucounter]["output"][$itemcounter++] = "[itemlist]";
			break;




			default:
        if($menu) { 
          $submenu[$menucounter] = submenu($menu, $position, $state, 3, 'leute');
          $found = (is_array($submenu[$menucounter])); 
        }
		}//switch(menu)

		//Plugins erlauben, Menuitems hinzuzufügen
		hook_submenu("leute", $menu, $submenu, $menucounter, $itemcounter);

		//Submenu darstellen
		if($found) {
			$submenu[$menucounter]["mod"] = "leute";
			$submenu[$menucounter]["id"] = $menu;
			$submenu[$menucounter]["sesid"] = session_id();
      $submenu[$menucounter]["position"] = $position;
      $submenu[$menucounter]["state"] = $state;

			if($display == 1) {
				$smarty->assign("sm", $submenu[$menucounter]);
				$smarty->assign("ko_path", $ko_path);
				$smarty->assign("help", ko_get_help("leute", "submenu_".$menu));
				$smarty->display("ko_submenu.tpl");
			} else if($display == 2) {
				$smarty->assign("sm", $submenu[$menucounter]);
				$smarty->assign("ko_path", $ko_path);
				$return = "sm_leute_".$menu."@@@";
				$return .= $smarty->fetch("ko_submenu.tpl");
			}

			$smarty->clear_assign("help");
			$menucounter++;
    }

	}//foreach(namen as menu)


	if($display == 2) {
		return $return;
	} else if($display == 3) {
		return $submenu;
	}
}//submenu_leute()




function submenu_reservation($namen, $position, $state, $display=1) {
	global $ko_path, $smarty;
	global $RESITEM_IMAGE_WIDTH;
	global $ko_menu_akt, $access;
	global $my_submenu;

	$return = "";

	$all_rights = ko_get_access_all('res_admin', '', $max_rights);
	if($max_rights < 1) return FALSE;

	$namen = explode(",", $namen);
	$menucounter = 0;
	foreach($namen as $menu) {
		$found = FALSE;

		$itemcounter = 0;
		switch($menu) {

			case "reservationen":
				$found = TRUE;
				$submenu[$menucounter]["titel"] = getLL("submenu_reservation_title_reservationen");
				if($max_rights > 1 && db_get_count('ko_resitem', 'id') > 0) {
					$submenu[$menucounter]["output"][$itemcounter] = ko_menuitem("reservation", "neue_reservation");
					$submenu[$menucounter]["link"][$itemcounter++] = $ko_path."reservation/index.php?action=neue_reservation";
				}
				if($max_rights > 0) {
					$submenu[$menucounter]["output"][$itemcounter] = ko_menuitem("reservation", "liste");
					$submenu[$menucounter]["link"][$itemcounter++] = $ko_path."reservation/index.php?action=liste";
					$submenu[$menucounter]["output"][$itemcounter] = ko_menuitem("reservation", "calendar");
					$submenu[$menucounter]["link"][$itemcounter++] = $ko_path."reservation/index.php?action=calendar";
				}
				if($max_rights > 4 || ($max_rights > 1 && $_SESSION["ses_userid"] != ko_get_guest_id()) ) {
					if($max_rights > 4) {  //Moderator for at least one item
						if($all_rights >= 5) {  //Moderator for all items, so don't limit display to certain items
							$mod_items = '';
						} else {  //Only moderator for certain items, so only show number of item user is responsible for
							$mod_items = array();
							foreach($access['reservation'] as $k => $v) {
								if(intval($k) && $v > 4) $mod_items[] = $k;
							}
						}
						ko_get_res_mod($res_mod, $mod_items);
					} else {
						ko_get_res_mod($res_mod, "", $_SESSION["ses_userid"]);
					}
					$submenu[$menucounter]["output"][$itemcounter] = ko_menuitem("reservation", "show_mod_res")." (".sizeof($res_mod).")";
					$submenu[$menucounter]["link"][$itemcounter++] = (sizeof($res_mod) > 0) ? $ko_path."reservation/index.php?action=show_mod_res" : "";
				}
				if($max_rights > 0 && $_SESSION['ses_userid'] != ko_get_guest_id()) {
					$submenu[$menucounter]["output"][$itemcounter] = ko_menuitem("reservation", "res_settings");
					$submenu[$menucounter]["link"][$itemcounter++] = $ko_path."reservation/index.php?action=res_settings";

					$submenu[$menucounter]['output'][$itemcounter++] = '';
					$submenu[$menucounter]['output'][$itemcounter] = ko_menuitem('reservation', 'ical_links');
					$submenu[$menucounter]['link'][$itemcounter++] = $ko_path.'reservation/index.php?action=res_ical_links';
				}
			break;



			case "objekte":
				if($max_rights > 3) {
					$found = TRUE;
					$submenu[$menucounter]["titel"] = getLL("submenu_reservation_title_objekte");
					$submenu[$menucounter]["output"][$itemcounter] = ko_menuitem("reservation", "new_item");
					$submenu[$menucounter]["link"][$itemcounter++] = $ko_path."reservation/index.php?action=new_item";
					$submenu[$menucounter]["output"][$itemcounter] = ko_menuitem("reservation", "list_items");
					$submenu[$menucounter]["link"][$itemcounter++] = $ko_path."reservation/index.php?action=list_items";
				}
			break;




			case "export":
				if($max_rights > 0 && $_SESSION['ses_userid'] != ko_get_guest_id()) {
					$found = TRUE;
					$submenu[$menucounter]["titel"] = getLL("submenu_reservation_title_export");
					if($state == "closed") continue;

					//PDF exports for daily, weekly, monthly and yearly calendar
					if($max_rights > 0) {
						$submenu[$menucounter]["output"][$itemcounter] = ' ';
						$submenu[$menucounter]["no_ul"][$itemcounter] = TRUE;
						$submenu[$menucounter]["link"][$itemcounter] = "";
						$c = '<select name="sel_pdf_export" size="0" onchange="jumpToUrl(\'?action=export_pdf&mode=\'+this.options[this.selectedIndex].value);">';
						$c .= '<option value="">'.getLL('submenu_reservation_pdf_export').'</option>';
						$c .= '<option value="" disabled="disabled">----------</option>';

						$c .= '<optgroup label="'.getLL('day').'">';
						$c .= '<option value="d-0">'.getLL('time_today').'</option>';
						$c .= '<option value="d-1">'.getLL('time_tomorrow').'</option>';
						$c .= '<option value="d-0-r">'.getLL('time_today').' ('.getLL('reservation_export_pdf_resource').')</option>';
						$c .= '<option value="d-1-r">'.getLL('time_tomorrow').' ('.getLL('reservation_export_pdf_resource').')</option>';
						$c .= '</optgroup>';
						$c .= '<optgroup label="'.getLL('week').'">';
						$c .= '<option value="w-0">'.getLL('time_current').'</option>';
						$c .= '<option value="w-1">'.getLL('time_next').'</option>';
						$c .= '<option value="w-0-r">'.getLL('time_current').' ('.getLL('reservation_export_pdf_resource').')</option>';
						$c .= '<option value="w-1-r">'.getLL('time_next').' ('.getLL('reservation_export_pdf_resource').')</option>';
						$c .= '</optgroup>';
						$c .= '<optgroup label="'.getLL('month').'">';
						$c .= '<option value="m-0">'.getLL('time_current').'</option>';
						$c .= '<option value="m-1">'.getLL('time_next').'</option>';
						$c .= '</optgroup>';
						$c .= '<optgroup label="'.getLL('twelvemonths').'">';
						$c .= '<option value="12m-0">'.getLL('time_current').'</option>';
						$c .= '<option value="12m-1">'.getLL('time_next').'</option>';
						$c .= '</optgroup>';
						$c .= '<optgroup label="'.getLL('halfayear').'">';
						$c .= '<option value="s-0:1">1/'.date('Y').'</option>';
						$c .= '<option value="s-0:7">2/'.date('Y').'</option>';
						$c .= '<option value="s-1:1">1/'.(date('Y')+1).'</option>';
						$c .= '<option value="s-1:7">2/'.(date('Y')+1).'</option>';
						$c .= '</optgroup>';
						$c .= '<optgroup label="'.getLL('year').'">';
						$c .= '<option value="y-0">'.getLL('time_current').'</option>';
						$c .= '<option value="y-1">'.getLL('time_next').'</option>';
						$c .= '</optgroup>';
						$c .= '</select><br />';
						$submenu[$menucounter]["html"][$itemcounter++] = $c;
					}


					//Excel-Export
					if($_SESSION['show'] == 'liste' && $max_rights > 0) {
						$submenu[$menucounter]['output'][$itemcounter++] = '';

						$submenu[$menucounter]['form'] = TRUE;
						$submenu[$menucounter]['form_hidden_inputs'] = array(array('name' => 'action', 'value' => ''),
																																 array('name' => 'ids', 'value' => ''));

						$submenu[$menucounter]["output"][$itemcounter] = ' ';
						$submenu[$menucounter]["no_ul"][$itemcounter] = TRUE;
						$submenu[$menucounter]["link"][$itemcounter] = "";
						$c = '<select name="sel_xls_rows" size="0" onchange="set_ids_from_chk(this); jumpToUrl(\'?action=export_xls_reservation&sel_xls_rows=\'+this.options[this.selectedIndex].value+\'&chk=\'+document.getElementsByName(\'ids\')[0].value);">';
						$c .= '<option value="">'.getLL('submenu_reservation_xls_export').'</option>';
						$c .= '<option value="" disabled="disabled">----------</option>';
						$c .= '<option value="alle">'.getLL("all").'</option>';
						$c .= '<option value="markierte">'.getLL("selected").'</option>';
						$c .= '<option value="meine">'.getLL("mine").'</option>';
						$c .= '</select><br />';
						$submenu[$menucounter]["html"][$itemcounter++] = $c;
					}
				}
			break;



			case "filter":
				$found = TRUE;

				//Permanente Filter
				$perm_filter_start = ko_get_setting("res_perm_filter_start");
				$perm_filter_ende  = ko_get_setting("res_perm_filter_ende");

				//Code für Select erstellen
        get_heute($tag, $monat, $jahr);
        addmonth($monat, $jahr, -18);

        if(isset($_SESSION["filter_start"])) $akt_von = $_SESSION["filter_start"];
        else $akt_von = "today";
        if(isset($_SESSION["filter_ende"])) $akt_bis = $_SESSION["filter_ende"];
        else $akt_bis = "immer";

				$von_code = '<select name="sel_filter_start" size="0" onchange="document.getElementById(\'submit_filter\').click();">';
				$bis_code = '<select name="sel_filter_ende" size="0" onchange="document.getElementById(\'submit_filter\').click();">';

        if($akt_von == "immer") $sel = 'selected="selected"';
        else $sel = "";
        $von_code .= '<option value="immer" ' . $sel . '>'.getLL("filter_always").'</option>';

        for($i=-18; $i<36; $i++) {
			    if((string)$i == (string)$akt_von && (string)$akt_von != "immer") $sel_von = 'selected="selected"';
			    else $sel_von = "";
			    if((string)$i == (string)$akt_bis && (string)$akt_bis != "immer") $sel_bis = 'selected="selected"';
			    else $sel_bis = "";
          $von_code .= '<option value="' . $i . '" ' . $sel_von.">";
          $von_code .= strftime($GLOBALS["DATETIME"]["nY"], mktime(1, 1, 1, $monat, 1, $jahr));
          $von_code .= '</option>';
          $bis_code .= '<option value="' . $i . '" ' . $sel_bis.">";
	        $bis_code .= strftime($GLOBALS["DATETIME"]["nY"], mktime(1, 1, 1, $monat, 1, $jahr));
          $bis_code .= '</option>';
	        addmonth($monat, $jahr, 1);
					//Add filter "from today"
					if($i == 0) {
						$sel_von = $akt_von == "today" ? 'selected="selected"' : '';
						$von_code .= '<option value="today" '.$sel_von.' style="font-weight:600; font-color:#224;">'.getLL("filter_from_today").'</option>';
						$sel_bis = $akt_bis == "today" ? 'selected="selected"' : '';
						$bis_code .= '<option value="today" '.$sel_bis.' style="font-weight:600; font-color:#224;">'.getLL("filter_until_today").'</option>';
					}
        }

	      if($akt_bis == "immer") $sel = 'selected="selected"';
        else $sel = "";
        $bis_code .= '<option value="immer" ' . $sel . '>'.getLL("filter_always").'</option>';

        $von_code .= '</select>';
        $bis_code .= '</select>';

				//Permanenter Filter bei Stufe 4
				if($max_rights > 3) {
					if($perm_filter_start || $perm_filter_ende) {
						$style = 'color:red;font-weight:900;';

						//Start und Ende formatieren
						$pfs = $perm_filter_start ? strftime($GLOBALS["DATETIME"]["nY"], strtotime($perm_filter_start)) : getLL("filter_always");
						$pfe = $perm_filter_ende ? strftime($GLOBALS["DATETIME"]["nY"], strtotime($perm_filter_ende)) : getLL("filter_always");

						$perm_filter_desc = "$pfs - $pfe";
						$checked = 'checked="checked"';
					} else {
						$perm_filter_desc = $style = "";
						$checked = '';
					}

					$bis_code .= '<div style="padding-top:5px;'.$style.'">';
					$bis_code .= '<input type="checkbox" name="chk_perm_filter" id="chk_perm_filter" '.$checked.' />';
					//Bestehenden permanenten Filter anzeigen, falls eingeschaltet
					if($perm_filter_desc) {
						$bis_code .= '<label for="chk_perm_filter">'.getLL('filter_use_globally_applied').'</label>';
						$bis_code .= "<br />$perm_filter_desc";
					} else {
						$bis_code .= '<label for="chk_perm_filter">'.getLL('filter_use_globally').'</label>';
					}
					$bis_code .= "</div>";
				}//if(group_mod)


        $bis_code .= '<input style="display: none;" type="submit" value="'.getLL("filter_refresh").'" name="submit_filter" id="submit_filter" onclick="set_action(\'submit_filter\', this);" />';

        $submenu[$menucounter]["titel"] = getLL("submenu_reservation_title_filter");
        if($state == "closed") continue;
				$submenu[$menucounter]['form'] = TRUE;
				$submenu[$menucounter]['form_hidden_inputs'] = array(array('name' => 'action', 'value' => ''),
																														 array('name' => 'ids', 'value' => ''));

				$submenu[$menucounter]['output'][$itemcounter] = getLL('filter_from_today');
				$submenu[$menucounter]["link"][$itemcounter++] = $ko_path."reservation/index.php?action=set_filter_today";
        $submenu[$menucounter]["output"][$itemcounter] = getLL("filter_start");
	      $submenu[$menucounter]["link"][$itemcounter] = "";
        $submenu[$menucounter]["html"][$itemcounter++] = $von_code;
        $submenu[$menucounter]["output"][$itemcounter++] = "";
        $submenu[$menucounter]["output"][$itemcounter] = getLL("filter_stop");
        $submenu[$menucounter]["link"][$itemcounter] = "";
        $submenu[$menucounter]["html"][$itemcounter++] = $bis_code;
			break;



			case "itemlist_objekte":
				$found = TRUE;

			  //Alle Objekte auslesen
			  $counter = 0;
				ko_get_resitems($all_items);
				ko_get_resgroups($groups);
				foreach($groups as $gid => $group) {
					//Check view rights
					if($all_rights < 1 && $access['reservation']['grp'.$gid] < 1) continue;
					//Get all items for this group
					$items = array();
					foreach($all_items as $item) {
						if($item['gruppen_id'] == $gid) $items[$item['id']] = $item;
					}
					//Find selected items
					$selected = array();
					foreach($items as $iid => $item) {
						if(in_array($iid, $_SESSION["show_items"])) $selected[$iid] = TRUE;
					}
					$itemlist[$counter]["type"] = "group";
					$itemlist[$counter]["name"] = $group["name"];
					$itemlist[$counter]["aktiv"] = (sizeof($items) == sizeof($selected) ? 1 : 0);
					$itemlist[$counter]["value"] = $gid;
					$itemlist[$counter]["open"] = isset($_SESSION["res_group_states"][$gid]) ? $_SESSION["res_group_states"][$gid] : 0;
					$counter++;

					foreach($items as $i_i => $i) {
						if($all_rights < 1 && $access['reservation'][$i_i] < 1) continue;
						$itemlist[$counter]["name"] = $i["name"];
						$itemlist[$counter]["prename"] = '<span style="margin-right:2px;background-color:#'.($i["farbe"]?$i["farbe"]:"fff").';">&emsp;</span>';
						$itemlist[$counter]["aktiv"] = in_array($i_i, $_SESSION["show_items"]) ? 1 : 0;
						$itemlist[$counter]["parent"] = TRUE;  //Is subitem to a res group
						$itemlist[$counter++]["value"] = $i_i;
					}//foreach(items)
					$itemlist[$counter-1]["last"] = TRUE;
				}//foreach(groups)


			  $smarty->assign("tpl_itemlist_select", $itemlist);

			  //Get all presets
			  $akt_value = implode(",", $_SESSION["show_items"]);
			  $itemset = array_merge((array)ko_get_userpref('-1', '', 'res_itemset', 'ORDER BY `key` ASC'), (array)ko_get_userpref($_SESSION['ses_userid'], '', 'res_itemset', 'ORDER BY `key` ASC'));
			  foreach($itemset as $i) {
					$value = $i['user_id'] == '-1' ? '@G@'.$i['key'] : $i['key'];
					$itemselect_values[] = $value;
					$itemselect_output[] = $i['user_id'] == '-1' ? getLL('itemlist_global_short').' '.$i['key'] : $i['key'];
			    if($i["value"] == $akt_value) $itemselect_selected = $value;
			  }
			  $smarty->assign("tpl_itemlist_values", $itemselect_values);
			  $smarty->assign("tpl_itemlist_output", $itemselect_output);
			  $smarty->assign("tpl_itemlist_selected", $itemselect_selected);
				if($max_rights > 3) $smarty->assign('allow_global', TRUE);

				$submenu[$menucounter]["titel"] = getLL("submenu_reservation_title_itemlist_objekte");
        if($state == "closed") continue;

        $submenu[$menucounter]["output"][$itemcounter++] = "[itemlist]";
			break;



			case "objektbeschreibungen":
				$found = TRUE;
				$f_code = "";
				$f_code .= '<div id="res_beschreibung">';
				$f_code .= '</div>';
				$f_code .= "\n".'<script language="JavaScript" type="text/javascript">
				<!--
				function changeResItem(item) {';

        ko_get_resitems($all_items);
				foreach($access['reservation'] as $id => $level) {
					if(!intval($id) || $level < 2) continue;
					$item = $all_items[$id];

					$f_code .= 'if(item == '.$item['id'].') {
						document.getElementById("res_beschreibung").innerHTML= "';
					$f_code .= '<b>'.$item['name'].'</b>';
					$f_code .= '<br />';
					if($item['bild'] && file_exists($ko_path.$item['bild'])) {
						$preview = ko_pic_get_thumbnail($item['bild'], 800, FALSE);
						$thumb = ko_pic_get_thumbnail($item['bild'], $RESITEM_IMAGE_WIDTH, FALSE);

						$f_code .= "<a href='#' onclick=".'\"'."TINY.box.show({image:'$preview'});".' \">';
						$f_code .= "<img src='$thumb' border='0' alt='".getLL('description')."' /><br />";
						$f_code .= '</a>';
					}
					$line = array(); foreach(explode("\n", $item['beschreibung']) as $l) $line[] = trim($l);
					$f_code .= implode('<br />', $line);
					//moderation
					$f_code .= '<br /><br />' . getLL('moderated') . ': ' . (((int)$item['moderation'] > 0) ? getLL('yes') : getLL('no'));
					//linked items
					$linked_items = '';
					if($item['linked_items']) {
						foreach(explode(',', $item['linked_items']) as $litem) {
							$linked_items .= $all_items[$litem]['name'].', ';
						}
						$linked_items = substr($linked_items, 0, -2);
					}
					if($linked_items) $f_code .= '<br /><br />'.getLL('res_linked_items').': '.$linked_items;

					$f_code .= '";}';
				}
				$f_code .= '
				}
				-->
				</script>';

        $submenu[$menucounter]["titel"] = getLL("submenu_reservation_title_objektbeschreibungen");
        if($state == "closed") continue;

        $submenu[$menucounter]["output"][$itemcounter] = " ";
        $submenu[$menucounter]["no_ul"][$itemcounter] = TRUE;
	      $submenu[$menucounter]["html"][$itemcounter++] = $f_code;
			break;



      default:
        if($menu) { 
          $submenu[$menucounter] = submenu($menu, $position, $state, 3, 'reservation');
          $found = (is_array($submenu[$menucounter])); 
        }
		}//switch(menu)

		//Plugins erlauben, Menuitems hinzuzufügen
		hook_submenu("reservation", $menu, $submenu, $menucounter, $itemcounter);

		if($found) {
			$submenu[$menucounter]["key"] = "reservation_".$menu;
			$submenu[$menucounter]["id"] = $menu;
			$submenu[$menucounter]["mod"] = "reservation";
			$submenu[$menucounter]["sesid"] = session_id();
      $submenu[$menucounter]["position"] = $position;
      $submenu[$menucounter]["state"] = $state;

			if($display == 1) {
				$smarty->assign("sm", $submenu[$menucounter]);
				$smarty->assign("ko_path", $ko_path);
				$smarty->assign("help", ko_get_help("reservation", "submenu_".$menu));
				$smarty->display("ko_submenu.tpl");
			} else if($display == 2) {
				$smarty->assign("sm", $submenu[$menucounter]);
				$smarty->assign("ko_path", $ko_path);
				$return = "sm_reservation_".$menu."@@@";
				$return .= $smarty->fetch("ko_submenu.tpl");
			}

			$smarty->clear_assign("help");
			$menucounter++;
    }

	}//foreach(namen as menu)

	if($display == 2) {
		return $return;
	} else if($display == 3) {
		return $submenu;
	}
}//submenu_reservation()




function submenu_rota($namen, $position, $state, $display=1) {
	global $ko_path, $smarty;
	global $ko_menu_akt, $access;
	global $my_submenu;

	$return = '';

	$all_rights = ko_get_access_all('rota_admin', '', $max_rights);
	if($max_rights < 1) return FALSE;
	ko_get_access('daten');

	$namen = explode(',', $namen);
	$menucounter = 0;
  foreach($namen as $menu) {

    $itemcounter = 0;
		$found = 0;
    switch($menu) {

			case 'rota':
				$found = TRUE;
				$submenu[$menucounter]['titel'] = getLL('submenu_rota_title_rota');
				if($max_rights > 4) {
					$submenu[$menucounter]['output'][$itemcounter] = ko_menuitem('rota', 'new_team');
					$submenu[$menucounter]['link'][$itemcounter++] = $ko_path.'rota/index.php?action=new_team';
				}

				if($max_rights > 4) {
					$submenu[$menucounter]['output'][$itemcounter] = ko_menuitem('rota', 'list_teams');
					$submenu[$menucounter]['link'][$itemcounter++] = $ko_path.'rota/index.php?action=list_teams';
				}

				$submenu[$menucounter]['output'][$itemcounter] = ko_menuitem('rota', 'schedule');
				$submenu[$menucounter]['link'][$itemcounter++] = $ko_path.'rota/index.php?action=schedule';

				if($_SESSION['ses_userid'] != ko_get_guest_id()) {
					$submenu[$menucounter]['output'][$itemcounter] = ko_menuitem('rota', 'settings');
					$submenu[$menucounter]['link'][$itemcounter++] = $ko_path.'rota/index.php?action=settings';
				}
			break;


			case 'itemlist_teams':
				$found = TRUE;

				$counter = 0;
				$orderCol = ko_get_setting('rota_manual_ordering') ? 'sort' : 'name';
				$teams = db_select_data('ko_rota_teams', 'WHERE 1', '*', 'ORDER BY '.$orderCol.' ASC');
				foreach($teams as $d_i => $d) {
					if($all_rights > 0 || $access['rota'][$d_i] > 0) {
						$active = 0;
						foreach($_SESSION['rota_teams'] as $s) if($s == $d_i) $active = 1;
						$itemlist[$counter]['name'] = $d['name'].($d['rotatype'] == 'week' ? ' <sup>('.getLL('rota_week_short').')</sup>' : '');
						$itemlist[$counter]['aktiv'] = $active;
						$itemlist[$counter]['value'] = $d_i;
						$itemlist[$counter++]['action'] = 'itemlistteams';
					}
				}//foreach(teams)
				$smarty->assign('tpl_itemlist_select', $itemlist);

				//Get all presets
				$akt_value = implode(',', $_SESSION['rota_teams']);
				$itemset = array_merge((array)ko_get_userpref('-1', '', 'rota_itemset', 'ORDER by `key` ASC'), (array)ko_get_userpref($_SESSION['ses_userid'], '', 'rota_itemset', 'ORDER by `key` ASC'));
				$itemselect_values = $itemselect_output = array();
				foreach($itemset as $i) {
					$value = $i['user_id'] == '-1' ? '@G@'.$i['key'] : $i['key'];
					$itemselect_values[] = $value;
					$itemselect_output[] = $i['user_id'] == '-1' ? getLL('itemlist_global_short').' '.$i['key'] : $i['key'];
					if($i['value'] == $akt_value) $itemselect_selected = $value;
				}
				$smarty->assign('tpl_itemlist_values', $itemselect_values);
				$smarty->assign('tpl_itemlist_output', $itemselect_output);
				$smarty->assign('tpl_itemlist_selected', $itemselect_selected);
				if($max_rights > 3) $smarty->assign('allow_global', TRUE);
				$smarty->assign('action_suffix', 'teams');

				$submenu[$menucounter]['titel'] = getLL('submenu_rota_title_itemlist_teams');
				if($state == 'closed') continue;

				$submenu[$menucounter]['output'][$itemcounter] = '[itemlist]';
			break;


			case 'itemlist_eventgroups':
				$found = TRUE;
				$itemlist = array();

				$show_cals = !(ko_get_userpref($_SESSION['ses_userid'], 'daten_no_cals_in_itemlist') == 1);

				$counter = 0;
				if($show_cals) {
					ko_get_event_calendar($cals);
					foreach($cals as $cid => $cal) {
						if($access['daten']['ALL'] < 1 && $access['daten']['cal'.$cid] < 1) continue;
						$_groups = db_select_data('ko_eventgruppen', "WHERE `calendar_id` = '$cid'", '*', 'ORDER BY name ASC');
						//Only keep event groups this user has access to and that have at least one event which is active in rota
						$groups = array();
						foreach($_groups as $gid => $group) {
							if($access['daten']['ALL'] < 1 && $access['daten'][$gid] < 1) continue;
							if($group['type'] != 0) continue;
							if($group['rota'] == 0 && db_get_count('ko_event', 'id', "AND `eventgruppen_id` = '$gid' AND `rota` = '1'") == 0) continue;
							$groups[$gid] = $group;
						}
						if(sizeof($groups) == 0) continue;
						//Find selected groups
						$selected = $local_ids = array();
						foreach($groups as $gid => $group) {
							if(in_array($gid, $_SESSION['rota_egs'])) $selected[$gid] = TRUE;
							if($group['gcal_url'] != '') continue;
							else $local_ids[] = $gid;
						}
						//Don't show whole calendar if no event groups
						if(sizeof($local_ids) == 0 || sizeof($groups) == 0) continue;

						$itemlist[$counter]['type'] = 'group';
						$itemlist[$counter]['name'] = $cal['name'].'<sup> (<span name="calnum_'.$cal['id'].'">'.sizeof($selected).'</span>)</sup>';
						$itemlist[$counter]['aktiv'] = (sizeof($groups) == sizeof($selected) ? 1 : 0);
						$itemlist[$counter]['value'] = $cid;
						$itemlist[$counter]['open'] = isset($_SESSION['daten_calendar_states'][$cid]) ? $_SESSION['daten_calendar_states'][$cid] : 0;
						$counter++;

						foreach($groups as $i_i => $i) {
							if($i['gcal_url'] != '') continue;
							$itemlist[$counter]['name'] = ko_html($i['name']);
							$itemlist[$counter]['prename'] = '<span style="margin-right:2px;background-color:#'.($i['farbe']?$i['farbe']:'fff').';">&emsp;</span>';
							$itemlist[$counter]['aktiv'] = in_array($i_i, $_SESSION['rota_egs']) ? 1 : 0;
							$itemlist[$counter]['parent'] = TRUE;  //Is subitem to a calendar
							$itemlist[$counter]['value'] = $i_i;
							$itemlist[$counter++]['action'] = 'itemlistegs';
						}//foreach(groups)
						$itemlist[$counter-1]['last'] = TRUE;
					}//foreach(cals)
				}//if(show_cals)


				//Add event groups without a calendar
				if($show_cals) {
					$groups = db_select_data('ko_eventgruppen', "WHERE `calendar_id` = '0'", '*', 'ORDER BY name ASC');
				} else {
					$groups = db_select_data('ko_eventgruppen', "WHERE 1", '*', 'ORDER BY name ASC');
				}
				foreach($groups as $i_i => $i) {
					if($access['daten']['ALL'] < 1 && $access['daten'][$i_i] < 1) continue;
					if($i['gcal_url'] != '') continue;
					if($i['rota'] == 0 && db_get_count('ko_event', 'id', "AND `eventgruppen_id` = '$i_i' AND `rota` = '1'") == 0) continue;
					$itemlist[$counter]['name'] = ko_html($i['name']);
					$itemlist[$counter]['prename'] = '<span style="margin-right:2px;background-color:#'.($i['farbe']?$i['farbe']:'fff').';">&emsp;</span>';
					$itemlist[$counter]['aktiv'] = in_array($i_i, $_SESSION['rota_egs']) ? 1 : 0;
					$itemlist[$counter]['value'] = $i_i;
					$itemlist[$counter++]['action'] = 'itemlistegs';
				}//foreach(groups)

				$smarty->assign('tpl_itemlist_select', $itemlist);


				//Get all presets
				$akt_value = implode(',', $_SESSION['rota_egs']);
				$itemset = array_merge((array)ko_get_userpref('-1', '', 'daten_itemset', 'ORDER BY `key` ASC'), (array)ko_get_userpref($_SESSION['ses_userid'], '', 'daten_itemset', 'ORDER BY `key` ASC'));
				$itemselect_values = $itemselect_output = array();
				foreach($itemset as $i) {
					$value = $i['user_id'] == '-1' ? '@G@'.$i['key'] : $i['key'];
			    $itemselect_values[] = $value;
			    $itemselect_output[] = $i['user_id'] == '-1' ? getLL('itemlist_global_short').' '.$i['key'] : $i['key'];
			    if($i['value'] == $akt_value) $itemselect_selected = $value;
				}
				$smarty->assign('tpl_itemlist_values', $itemselect_values);
				$smarty->assign('tpl_itemlist_output', $itemselect_output);
				$smarty->assign('tpl_itemlist_selected', $itemselect_selected);
				if($access['daten']['MAX'] > 3) $smarty->assign('allow_global', TRUE);
				$smarty->assign('action_suffix', 'egs');
																
				$submenu[$menucounter]['titel'] = getLL('submenu_daten_title_itemlist_termingruppen');
				if($state == 'closed') continue;

				$submenu[$menucounter]['output'][$itemcounter++] = '[itemlist]';
			break;

      default:
 	    	if($menu) { 
          $submenu[$menucounter] = submenu($menu, $position, $state, 3, 'rota');
          $found = (is_array($submenu[$menucounter])); 
        }
		}//switch(menu)

		//Plugins erlauben, Menuitems hinzuzufügen
		hook_submenu('rota', $menu, $submenu, $menucounter, $itemcounter);

		if($found) {
			$submenu[$menucounter]['key'] = 'rota_'.$menu;
			$submenu[$menucounter]['id'] = $menu;
			$submenu[$menucounter]['mod'] = 'rota';
			$submenu[$menucounter]['sesid'] = session_id();
      $submenu[$menucounter]['position'] = $position;
      $submenu[$menucounter]['state'] = $state;

			if($display == 1) {
				$smarty->assign('sm', $submenu[$menucounter]);
				$smarty->assign('ko_path', $ko_path);
				$smarty->assign('help', ko_get_help('rota', 'submenu_'.$menu));
				$smarty->display('ko_submenu.tpl');
			} else if($display == 2) {
				$smarty->assign('sm', $submenu[$menucounter]);
				$smarty->assign('ko_path', $ko_path);
				$return = 'sm_rota_'.$menu.'@@@';
				$return .= $smarty->fetch('ko_submenu.tpl');
			}

			$smarty->clear_assign("help");
			$menucounter++;
    }

	}//foreach(namen as menu)

	if($display == 2) {
		return $return;
	} else if($display == 3) {
		return $submenu;
	}
}//submenu_rota()





function submenu_admin($namen, $position, $state, $display=1) {
	global $ko_path, $smarty;
	global $my_submenu;

	$return = "";

	$all_rights = ko_get_access_all('admin');
	if($all_rights < 1) return FALSE;

	$namen = explode(",", $namen);
	$menucounter = 0;
  foreach($namen as $menu) {
		$found = FALSE;
			  
    $itemcounter = 0;
    switch($menu) {

			case "allgemein":
				$found = TRUE;
				$submenu[$menucounter]["titel"] = getLL("submenu_admin_title_allgemein");
				if($all_rights > 1) {
					$submenu[$menucounter]["output"][$itemcounter] = ko_menuitem("admin", "set_allgemein");
					$submenu[$menucounter]["link"][$itemcounter++] = $ko_path."admin/index.php?action=set_allgemein";

					$submenu[$menucounter]["output"][$itemcounter] = ko_menuitem("admin", "set_etiketten");
					$submenu[$menucounter]["link"][$itemcounter++] = $ko_path."admin/index.php?action=set_etiketten";

					$submenu[$menucounter]["output"][$itemcounter] = ko_menuitem("admin", "set_leute_pdf");
					$submenu[$menucounter]["link"][$itemcounter++] = $ko_path."admin/index.php?action=set_leute_pdf";
					$submenu[$menucounter]["output"][$itemcounter++] = "";
				}
				$submenu[$menucounter]["output"][$itemcounter] = ko_menuitem("admin", "set_layout");
				$submenu[$menucounter]["link"][$itemcounter++] = $ko_path."admin/index.php?action=set_layout";
				if($all_rights > 2) {
					$submenu[$menucounter]["output"][$itemcounter] = ko_menuitem("admin", "set_layout_guest");
					$submenu[$menucounter]["link"][$itemcounter++] = $ko_path."admin/index.php?action=set_layout_guest";
				}
				if(ko_get_setting("change_password") == 1) {
					$submenu[$menucounter]["output"][$itemcounter] = ko_menuitem("admin", "change_password");
					$submenu[$menucounter]["link"][$itemcounter++] = $ko_path."admin/index.php?action=change_password";
				}
			break;


			case "logins":
				if($all_rights > 4) {
					$found = TRUE;
					$submenu[$menucounter]["titel"] = getLL("submenu_admin_title_logins");
					$submenu[$menucounter]["output"][$itemcounter] = ko_menuitem("admin", "new_login");
					$submenu[$menucounter]["link"][$itemcounter++] = $ko_path."admin/index.php?action=set_new_login";
					$submenu[$menucounter]["output"][$itemcounter] = ko_menuitem("admin", "show_logins");
					$submenu[$menucounter]["link"][$itemcounter++] = $ko_path."admin/index.php?action=set_show_logins";
					$submenu[$menucounter]["output"][$itemcounter++] = "";
					$submenu[$menucounter]["output"][$itemcounter] = ko_menuitem("admin", "new_admingroup");
					$submenu[$menucounter]["link"][$itemcounter++] = $ko_path."admin/index.php?action=set_new_admingroup";
					$submenu[$menucounter]["output"][$itemcounter] = ko_menuitem("admin", "show_admingroups");
					$submenu[$menucounter]["link"][$itemcounter++] = $ko_path."admin/index.php?action=set_show_admingroups";
				}
			break;


			case 'news':
				if($all_rights > 1) {
					$found = TRUE;
					$submenu[$menucounter]['titel'] = getLL('submenu_admin_title_news');
					$submenu[$menucounter]['output'][$itemcounter] = ko_menuitem('admin', 'new_news');
					$submenu[$menucounter]['link'][$itemcounter++] = $ko_path.'admin/index.php?action=new_news';
					$submenu[$menucounter]['output'][$itemcounter] = ko_menuitem('admin', 'list_news');
					$submenu[$menucounter]['link'][$itemcounter++] = $ko_path.'admin/index.php?action=list_news';
				}
			break;


			case "logs":
				if($all_rights > 3 || ($all_rights > 0 && ko_module_installed('sms'))) {
					$found = TRUE;
					$submenu[$menucounter]["titel"] = getLL("submenu_admin_title_logs");
				}

				if($all_rights > 3) {
					$submenu[$menucounter]["output"][$itemcounter] = ko_menuitem("admin", "show_logs");
					$submenu[$menucounter]["link"][$itemcounter++] = $ko_path."admin/index.php?action=show_logs";
				}
				if(ko_module_installed('sms')) {
					$submenu[$menucounter]['output'][$itemcounter] = ko_menuitem('admin', 'show_sms_log');
					$submenu[$menucounter]['link'][$itemcounter++] = $ko_path.'admin/index.php?action=show_sms_log';
				}
			break;


			case "filter":
				if(($_SESSION['show'] == 'show_logs' && $all_rights < 4) || ($_SESSION['show'] == 'show_sms_log' && $all_rights < 1)) continue;

				$found = TRUE;
				//Typ
				$code_typ  = '<select name="sel_log_filter_type" size="0" onchange="jumpToUrl(\'?action=submit_log_filter&amp;set_log_filter=\'+this.options[this.selectedIndex].value);">';
				$code_typ .= '<option value=""></option>';
				//Don't show types created only be root
				$where = $_SESSION['ses_userid'] == ko_get_root_id() ? '' : "WHERE `user_id` != '".ko_get_root_id()."'";
				$rows = db_select_distinct('ko_log', 'type', 'ORDER BY `type` ASC', $where);
				foreach($rows as $type) {
					$sel = ($_SESSION["log_type"] == $type) ? 'selected="selected"' : "";
					$code_typ .= '<option value="'.$type.'" '.$sel.'>'.$type.'</option>';
				}
				$code_typ .= '</select><br />';

				//User
				ko_get_logins($logins);
				$code_user .= '<select name="sel_log_filter_user" size="0" onchange="jumpToUrl(\'?action=submit_user_filter&amp;set_user_filter=\'+this.options[this.selectedIndex].value);">';
				$code_user .= '	<option value=""></option>';
				foreach($logins as $id => $login) {
				  $sel = ($_SESSION["log_user"] == $id) ? 'selected="selected"' : "";
				  if($login['login'] && ($login['login'] != 'root' || $_SESSION['ses_username'] == 'root')) {
				    $code_user .= '<option value="'.$id.'" '.$sel.'>'.$login['login']." ($id)".'</option>';
				  }
				}
				$code_user .= '</select><br />';

				//Zeit
				$code_zeit = '<select name="sel_log_filter_time" size="0" onchange="jumpToUrl(\'?action=submit_time_filter&amp;set_time_filter=\'+this.options[this.selectedIndex].value);">';
				$code_zeit .= '<option value=""></option>';
				$sel = ($_SESSION["log_time"] == 1) ? 'selected="selected"' : "";
				$code_zeit .= '<option value="1" '.$sel.'>'.getLL("admin_logfilter_today").'</option>';
				$sel = ($_SESSION["log_time"] == 2) ? 'selected="selected"' : "";
				$code_zeit .= '<option value="2" '.$sel.'>'.getLL("admin_logfilter_yesterday").'</option>';
				$sel = ($_SESSION["log_time"] == 7) ? 'selected="selected"' : "";
				$code_zeit .= '<option value="7" '.$sel.'>'.getLL("admin_logfilter_week").'</option>';
				$sel = ($_SESSION["log_time"] == 14) ? 'selected="selected"' : "";
				$code_zeit .= '<option value="14" '.$sel.'>'.getLL("admin_logfilter_twoweeks").'</option>';

				$sel = ($_SESSION['log_time'] == 30) ? 'selected="selected"' : '';
				$code_zeit .= '<option value="30" '.$sel.'>'.getLL('admin_logfilter_month').'</option>';
				$sel = ($_SESSION['log_time'] == 90) ? 'selected="selected"' : '';
				$code_zeit .= '<option value="90" '.$sel.'>'.getLL('admin_logfilter_quarter').'</option>';
				$sel = ($_SESSION['log_time'] == 183) ? 'selected="selected"' : '';
				$code_zeit .= '<option value="183" '.$sel.'>'.getLL('admin_logfilter_halfyear').'</option>';
				$sel = ($_SESSION['log_time'] == 365) ? 'selected="selected"' : '';
				$code_zeit .= '<option value="365" '.$sel.'>'.getLL('admin_logfilter_year').'</option>';
				$code_zeit .= '</select><br />';


				$submenu[$menucounter]["titel"] = getLL("submenu_admin_title_filter");
				//Only show for logs but not for sms logs
				if($_SESSION['show'] == 'show_logs') {
					$submenu[$menucounter]["output"][$itemcounter] = getLL("admin_logfilter_type");
					$submenu[$menucounter]["html"][$itemcounter++] = $code_typ;
				}
				if($_SESSION['show'] == 'show_logs' || $all_rights > 3) {
					$submenu[$menucounter]["output"][$itemcounter] = getLL("admin_logfilter_user");
					$submenu[$menucounter]["html"][$itemcounter++] = $code_user;
				}
				$submenu[$menucounter]["output"][$itemcounter] = getLL("admin_logfilter_time");
				$submenu[$menucounter]["html"][$itemcounter++] = $code_zeit;

				//Hide Guest
				if($_SESSION['show'] == 'show_logs') {
					$submenu[$menucounter]["output"][$itemcounter] = " ";
					$submenu[$menucounter]["no_ul"][$itemcounter] = TRUE;
					$submenu[$menucounter]["html"][$itemcounter++] = '<input type="checkbox" name="chk_hide_guest" id="chk_hide_guest" '.($_SESSION["logs_hide_guest"]?'checked="checked"':"").' onclick="jumpToUrl(\'?action=submit_hide_guest&amp;logs_hide_status=\'+this.checked);" /><label for="chk_hide_guest">'.getLL("admin_logfilter_hide_guest").'</label>';

					//Root: Guest-Einträge löschen
					if($_SESSION["ses_username"] == "root") {
						$submenu[$menucounter]["output"][$itemcounter] = " ";
						$submenu[$menucounter]["no_ul"][$itemcounter] = TRUE;
						$submenu[$menucounter]["html"][$itemcounter++] = '<p align="center"><input type="button" value="'.getLL("admin_logfilter_del_guest").'" onclick="jumpToUrl(\'?action=submit_clear_guest\');" /></p>';
					}
				}
			break;

			default:
        if($menu) { 
          $submenu[$menucounter] = submenu($menu, $position, $state, 3, 'admin');
          $found = (is_array($submenu[$menucounter])); 
        }
		}//switch(menu)

		//Plugins erlauben, Menuitems hinzuzufügen
		hook_submenu("admin", $menu, $submenu, $menucounter, $itemcounter);

    if($found) {
			$submenu[$menucounter]["key"] = "admin_".$menu;
			$submenu[$menucounter]["id"] = $menu;
			$submenu[$menucounter]["mod"] = "admin";
			$submenu[$menucounter]["sesid"] = session_id();
      $submenu[$menucounter]["position"] = $position;
      $submenu[$menucounter]["state"] = $state;

			if($display == 1) {
				$smarty->assign("sm", $submenu[$menucounter]);
				$smarty->assign("ko_path", $ko_path);
				$smarty->assign("help", ko_get_help("admin", "submenu_".$menu));
				$smarty->display("ko_submenu.tpl");
			} else if($display == 2) {
				$smarty->assign("sm", $submenu[$menucounter]);
				$smarty->assign("ko_path", $ko_path);
				$return = "sm_admin_".$menu."@@@";
				$return .= $smarty->fetch("ko_submenu.tpl");
			}

			$smarty->clear_assign("help");
			$menucounter++;
    }
			    

	}//foreach(namen as menu)

	if($display == 2) {
		return $return;
	} else if($display == 3) {
		return $submenu;
	}
}//submenu_admin()





function submenu_fileshare($namen, $position, $state, $display=1) {
	global $ko_path, $smarty;
	global $my_submenu;

	$return = "";

	$all_rights = ko_get_access_all('fileshare_admin');
	if($all_rights < 1) return FALSE;

	$namen = explode(",", $namen);
	$menucounter = 0;
  foreach($namen as $menu) {
		$found = FALSE;

    $itemcounter = 0;
    switch($menu) {
			
			case "shares":
				if(ENABLE_FILESHARE && $all_rights > 1) {
					$found = TRUE;
					$submenu[$menucounter]["titel"] = getLL("submenu_fileshare_title_shares");
					$submenu[$menucounter]["output"][$itemcounter] = ko_menuitem("fileshare", "new_share");
					$submenu[$menucounter]["link"][$itemcounter++] = $ko_path."fileshare/index.php?action=new_share";
					if($all_rights > 3) {
						$submenu[$menucounter]["output"][$itemcounter] = ko_menuitem("fileshare", "new_folder");
						$submenu[$menucounter]["link"][$itemcounter++] = $ko_path."fileshare/index.php?action=new_folder";
					}
				}
			break;


			case "webfolders":
				if($all_rights > 3 && WEBFOLDERS) {
					$found = TRUE;
					$submenu[$menucounter]["titel"] = getLL("submenu_fileshare_title_webfolders");
					$submenu[$menucounter]["output"][$itemcounter] = ko_menuitem("fileshare", "new_webfolder");
					$submenu[$menucounter]["link"][$itemcounter++] = $ko_path."fileshare/index.php?action=new_webfolder";
					$submenu[$menucounter]["output"][$itemcounter] = ko_menuitem("fileshare", "list_webfolders");
					$submenu[$menucounter]["link"][$itemcounter++] = $ko_path."fileshare/index.php?action=list_webfolders";
				}
			break;


			case "foldertree":
				if(ENABLE_FILESHARE && $all_rights > 0) {
					if($display) {  //Ohne Popup-Menu, normaler JS-TreeView erstellen
						$found = TRUE;
						$submenu[$menucounter]["titel"] = getLL("submenu_fileshare_title_foldertree");
						$submenu[$menucounter]["output"][$itemcounter] = " ";
						$submenu[$menucounter]["no_ul"][$itemcounter] = TRUE;
						$html = '<div id="treeview"><script language="javascript" type="text/javascript">CreateProjectExplorer();</script></div>';
						$submenu[$menucounter]["html"][$itemcounter] = $html;
					} else {  //Code für Popup-Menü
						$found = TRUE;
						$submenu[$menucounter]["titel"] = getLL("submenu_fileshare_title_foldertree");
						$folders = ko_fileshare_get_folders($_SESSION["ses_userid"]);
						foreach($folders as $folder) {
							if($folder["parent"] == 0 && $itemcounter < 7) {
								$submenu[$menucounter]["output"][$itemcounter] = $folder["name"];
								$submenu[$menucounter]["link"][$itemcounter++] = $ko_path."fileshare/index.php?action=show_folder&amp;id=".$folder["id"];
							}
						}//foreach(folders as folder)
					}//if..else(display)
				}//if(group_view)
			break;

			default:
        if($menu) { 
          $submenu[$menucounter] = submenu($menu, $position, $state, 3, 'fileshare');
          $found = (is_array($submenu[$menucounter])); 
        }
		}//switch(menu)

		//Plugins erlauben, Menuitems hinzuzufügen
		hook_submenu("fileshare", $menu, $submenu, $menucounter, $itemcounter);

    if($found) {
			$submenu[$menucounter]["key"] = "fileshare_".$menu;
			$submenu[$menucounter]["id"] = $menu;
			$submenu[$menucounter]["mod"] = "fileshare";
			$submenu[$menucounter]["sesid"] = session_id();
      $submenu[$menucounter]["position"] = $position;
      $submenu[$menucounter]["state"] = $state;

			if($display == 1) {
				$smarty->assign("sm", $submenu[$menucounter]);
				$smarty->assign("ko_path", $ko_path);
				$smarty->assign("help", ko_get_help("fileshare", "submenu_".$menu));
				$smarty->display("ko_submenu.tpl");
			} else if($display == 2) {
				$smarty->assign("sm", $submenu[$menucounter]);
				$smarty->assign("ko_path", $ko_path);
				$return = "sm_fileshare_".$menu."@@@";
				$return .= $smarty->fetch("ko_submenu.tpl");
			}

			$smarty->clear_assign("help");
			$menucounter++;
    }


	}//foreach(namen as menu)

	if($display == 2) {
		return $return;
	} else if($display == 3) {
		return $submenu;
	}
}//submenu_fileshare()





function submenu_tools($namen, $position, $state, $display=1) {
	global $ko_path, $smarty;
	global $my_submenu;

	$return = "";

	if(!ko_module_installed("tools")) return FALSE;

	$namen = explode(",", $namen);

	$menucounter = 0;
  foreach($namen as $menu) {
		$found = FALSE;

    $itemcounter = 0;
    switch($menu) {
			
			case "submenus":
				$found = TRUE;
				$submenu[$menucounter]["titel"] = getLL("submenu_tools_title_submenus");
				$submenu[$menucounter]['output'][$itemcounter] = ko_menuitem('tools', 'misc');
				$submenu[$menucounter]['link'][$itemcounter++] = $ko_path.'tools/index.php?action=misc';
				$submenu[$menucounter]["output"][$itemcounter] = ko_menuitem("tools", "list_submenus");
				$submenu[$menucounter]["link"][$itemcounter++] = $ko_path."tools/index.php?action=list_submenus";
				$submenu[$menucounter]["output"][$itemcounter] = ko_menuitem("tools", "testmail");
				$submenu[$menucounter]["link"][$itemcounter++] = $ko_path."tools/index.php?action=testmail";
			break;

			case "leute-db":
				$found = TRUE;
				$submenu[$menucounter]["titel"] = getLL("submenu_tools_title_leute-db");
				$submenu[$menucounter]["output"][$itemcounter] = ko_menuitem("tools", "show_leute_db");
				$submenu[$menucounter]["link"][$itemcounter++] = $ko_path."tools/index.php?action=show_leute_db";
				$submenu[$menucounter]["output"][$itemcounter] = ko_menuitem("tools", "show_leute_formular");
				$submenu[$menucounter]["link"][$itemcounter++] = $ko_path."tools/index.php?action=show_leute_formular";
				$submenu[$menucounter]["output"][$itemcounter] = ko_menuitem("tools", "show_familie_db");
				$submenu[$menucounter]["link"][$itemcounter++] = $ko_path."tools/index.php?action=show_familie_db";
			break;

			case "ldap":
				if(ko_do_ldap()) {
					$found = TRUE;
					$submenu[$menucounter]["titel"] = getLL("submenu_tools_title_ldap");
					$submenu[$menucounter]["output"][$itemcounter] = ko_menuitem("tools", "list_ldap_logins");
					$submenu[$menucounter]["link"][$itemcounter++] = $ko_path."tools/index.php?action=list_ldap_logins";
					$submenu[$menucounter]["output"][$itemcounter] = ko_menuitem("tools", "ldap_export");
					$submenu[$menucounter]["link"][$itemcounter++] = $ko_path."tools/index.php?action=ldap_export";
				}
			break;

			case "locallang":
				$found = TRUE;
				$submenu[$menucounter]["titel"] = getLL("submenu_tools_title_locallang");
				$submenu[$menucounter]["output"][$itemcounter] = ko_menuitem("tools", "ll_overview");
				$submenu[$menucounter]["link"][$itemcounter++] = $ko_path."tools/index.php?action=ll_overview";
			break;

			case "plugins":
				$found = TRUE;
				$submenu[$menucounter]["titel"] = getLL("submenu_tools_title_plugins");
				$submenu[$menucounter]["output"][$itemcounter] = ko_menuitem("tools", "plugins_list");
				$submenu[$menucounter]["link"][$itemcounter++] = $ko_path."tools/index.php?action=plugins_list";
			break;

			case 'scheduler':
				$found = TRUE;
				$submenu[$menucounter]['titel'] = getLL('submenu_tools_title_scheduler');
				$submenu[$menucounter]['output'][$itemcounter] = ko_menuitem('tools', 'scheduler_add');
				$submenu[$menucounter]['link'][$itemcounter++] = $ko_path.'tools/index.php?action=scheduler_add';
				$submenu[$menucounter]['output'][$itemcounter] = ko_menuitem('tools', 'scheduler_list');
				$submenu[$menucounter]['link'][$itemcounter++] = $ko_path.'tools/index.php?action=scheduler_list';
			break;

			case 'typo3':
				$found = TRUE;
				$submenu[$menucounter]['titel'] = getLL('submenu_tools_title_typo3');
				$submenu[$menucounter]['output'][$itemcounter] = ko_menuitem('tools', 'typo3_connection');
				$submenu[$menucounter]['link'][$itemcounter++] = $ko_path.'tools/index.php?action=typo3_connection';
			break;

			default:
        if($menu) { 
          $submenu[$menucounter] = submenu($menu, $position, $state, 3, 'tools');
          $found = (is_array($submenu[$menucounter])); 
        }
		}//switch(menu)

		//Plugins erlauben, Menuitems hinzuzufügen
		hook_submenu("tools", $menu, $submenu, $menucounter, $itemcounter);

		if($found) {
			$submenu[$menucounter]["key"] = "tools_".$menu;
			$submenu[$menucounter]["id"] = $menu;
			$submenu[$menucounter]["mod"] = "tools";
			$submenu[$menucounter]["sesid"] = session_id();
      $submenu[$menucounter]["position"] = $position;
      $submenu[$menucounter]["state"] = $state;

			if($display == 1) {
				$smarty->assign("sm", $submenu[$menucounter]);
				$smarty->assign("ko_path", $ko_path);
				$smarty->assign("help", ko_get_help("tools", "submenu_".$menu));
				$smarty->display("ko_submenu.tpl");
			} else if($display == 2) {
				$smarty->assign("sm", $submenu[$menucounter]);
				$smarty->assign("ko_path", $ko_path);
				$return = "sm_tools_".$menu."@@@";
				$return .= $smarty->fetch("ko_submenu.tpl");
			}

			$smarty->clear_assign("help");
			$menucounter++;
    }

	}//foreach(namen as menu)
	
	if($display == 2) {
		return $return;
	} else if($display == 3) {
		return $submenu;
	}
}//submenu_tools()





function submenu_tapes($namen, $position, $state, $display=1) {
	global $ko_path, $smarty;
	global $ko_menu_akt, $access;
	global $my_submenu;

	$return = "";

	$all_rights = ko_get_access_all('tapes_admin', '', $max_rights);
	if($max_rights < 1) return FALSE;

	$namen = explode(",", $namen);
	$menucounter = 0;
  foreach($namen as $menu) {
		$found = FALSE;

    $itemcounter = 0;
    switch($menu) {
			
			case "tapes":
				$found = TRUE;
				$submenu[$menucounter]["titel"] = getLL("submenu_tapes_title_tapes");
				if($max_rights > 2 && db_get_count('ko_tapes_groups') > 0) {  //Ohne Tapegruppen keine neuen Predigten erfassen lassen
					$submenu[$menucounter]["output"][$itemcounter] = ko_menuitem("tapes", "new_tape");
					$submenu[$menucounter]["link"][$itemcounter++] = $ko_path."tapes/index.php?action=new_tape";
				}
				$submenu[$menucounter]["output"][$itemcounter] = ko_menuitem("tapes", "list_tapes");
				$submenu[$menucounter]["link"][$itemcounter++] = $ko_path."tapes/index.php?action=list_tapes";
			break;

			
			case "series":
				$found = TRUE;
				$submenu[$menucounter]["titel"] = getLL("submenu_tapes_title_series");
				if($max_rights > 2) {
					$submenu[$menucounter]["output"][$itemcounter] = ko_menuitem("tapes", "new_serie");
					$submenu[$menucounter]["link"][$itemcounter++] = $ko_path."tapes/index.php?action=new_serie";
				}
				$submenu[$menucounter]["output"][$itemcounter] = ko_menuitem("tapes", "list_series");
				$submenu[$menucounter]["link"][$itemcounter++] = $ko_path."tapes/index.php?action=list_series";
			break;


			case "groups":
				if($max_rights > 3) {
					$found = TRUE;
					$submenu[$menucounter]["titel"] = getLL("submenu_tapes_title_groups");
					$submenu[$menucounter]["output"][$itemcounter] = ko_menuitem("tapes", "new_tapegroup");
					$submenu[$menucounter]["link"][$itemcounter++] = $ko_path."tapes/index.php?action=new_tapegroup";
					$submenu[$menucounter]["output"][$itemcounter] = ko_menuitem("tapes", "list_tapegroups");
					$submenu[$menucounter]["link"][$itemcounter++] = $ko_path."tapes/index.php?action=list_tapegroups";
				}
			break;


			case "print":
				if($max_rights > 1) {
					$found = TRUE;

					//Print-Queue durchgehen und Select aufbauen
					$total_num = 0;
					$sel_code = "";
					if(is_array($_SESSION["printqueue"])) {
						foreach($_SESSION["printqueue"] as $tape_id => $num_) {
							$num = format_userinput($num_, "uint", FALSE, 2);
							if($num > 0) {
								$total_num += $num;
								ko_get_tapes($tape_, "AND ko_tapes.id = '$tape_id'");
								$tape = $tape_[$tape_id];
								$sel_code .= '<option value="'.$tape_id.'">'.$num."x".strftime($GLOBALS["DATETIME"]["dm"], strtotime($tape["date"]));
								$sel_code .= ': '.substr($tape["title"], 0, 15).(strlen($tape["title"])>15?"..":"").'</option>';
							}
						}//foreach(_SESSION[printqueue])
					}

					$submenu[$menucounter]["titel"] = getLL("submenu_tapes_title_print");
					if($total_num > 0) {
						$layouts = ko_get_tape_printlayout();
						$print_code  = '<select name="sel_printqueue" size="'.min(4, max(2,sizeof($_SESSION["printqueue"]))).'">'.$sel_code.'</select>';
						$print_code .= '<p style="white-space:nowrap;text-align:center;">';
						$print_code .= '<input type="submit" value="'.getLL("delete").'" onclick="set_action(\'del_from_printqueue\', this);" />&nbsp;&nbsp;';
						$print_code .= '<input type="submit" value="'.getLL("delete_all").'" onclick="set_action(\'clear_printqueue\', this);" /></p>';

						$submenu[$menucounter]["output"][$itemcounter] = getLL("total").": ".$total_num;
						$submenu[$menucounter]["html"][$itemcounter++] = $print_code;

						$template_code = '<select name="sel_printlayout" size="0">';
						foreach($layouts as $l) {
							if($l["default"] == 1) $sel = 'selected="selected"'; else $sel = "";
							$template_code .= '<option value="'.$l["id"].'" '.$sel.'>'.$l["name"].'</option>';
						}
						$template_code .= '</select><br />';

						$submenu[$menucounter]["output"][$itemcounter] = getLL("tapes_print_preset").":";
						$submenu[$menucounter]["html"][$itemcounter++] = $template_code;

						$start_code = '<select name="sel_printstart" size="0">';
						for($i=0; $i<12; $i++) {
							$start_code .= '<option value="'.$i.'">'.($i+1).'</option>';
						}
						$start_code .= '</select>';

						$submenu[$menucounter]["output"][$itemcounter] = getLL("tapes_print_start").":";
						$submenu[$menucounter]["html"][$itemcounter++] = $start_code;

						$submenu[$menucounter]["output"][$itemcounter] = " ";
						$submenu[$menucounter]["no_ul"][$itemcounter] = TRUE;
						$submenu[$menucounter]["html"][$itemcounter++] = '<p align="center"><input type="submit" value="'.getLL("tapes_print").'" onclick="set_action(\'do_print\', this);" /></p>';

					}//if(total_num > 0)
					else {
						$submenu[$menucounter]["output"][$itemcounter] = getLL("tapes_print_empty");
						$submenu[$menucounter]["link"][$itemcounter++] = "";
					}
				}//if(group_new)
			break;



			case "filter":
				$found = TRUE;
				//Gruppe
 				$code_group  = '<select name="sel_group_filter" size="0" onchange="jumpToUrl(\'?action=submit_group_filter&amp;set_filter=\'+this.options[this.selectedIndex].value);">';
 				$code_group .= '<option value=""></option>';
 				ko_get_tapegroups($groups, $z_where);
				foreach($groups as $i => $group) {
					if($all_rights > 0 || $access['tapes'][$i] > 0) {
						$sel = $_SESSION["tape_group_filter"] == $i ? 'selected="selected"' : "";
						$code_group .= '<option value="'.$i.'" '.$sel.'>'.substr($group["name"], 0, ITEMLIST_LENGTH_MAX).(strlen($group["name"])>ITEMLIST_LENGTH_MAX?"..":"").'</option>';
					}
				}
				$code_group .= '</select><br />';

				//Serie
 				$code_serie  = '<select name="sel_serie_filter" size="0" onchange="jumpToUrl(\'?action=submit_serie_filter&amp;set_filter=\'+this.options[this.selectedIndex].value);">';
 				$code_serie .= '<option value=""></option>';
 				ko_get_tapeseries($series);
				foreach($series as $i => $serie) {
				  $sel = $_SESSION["tape_serie_filter"] == $i ? 'selected="selected"' : "";
				  $code_serie .= '<option value="'.$i.'" '.$sel.'>'.substr($serie["name"], 0, ITEMLIST_LENGTH_MAX).(strlen($serie["name"])>ITEMLIST_LENGTH_MAX?"..":"").'</option>';
				}
				$code_serie .= '</select><br />';

				//Titel
 				$code_title .= '<input type="text" name="txt_title_filter" size="15" onkeydown="if ((event.which == 13) || (event.keyCode == 13)) { this.form.submit_title_filter.click(); return false; } else return true;">';
				$code_title .= '<input type="submit" name="submit_title_filter" value="OK" onclick="set_action(\'submit_title_filter\', this);set_hidden_value(\'id\', this.form.txt_title_filter.value, this); this.submit;" />';
				$code_title .= '<br />';

				//Untertitel
 				$code_subtitle .= '<input type="text" name="txt_subtitle_filter" size="15" onkeydown="if ((event.which == 13) || (event.keyCode == 13)) { this.form.submit_subtitle_filter.click(); return false; } else return true;">';
				$code_subtitle .= '<input type="submit" name="submit_subtitle_filter" value="OK" onclick="set_action(\'submit_subtitle_filter\', this);set_hidden_value(\'id\', this.form.txt_subtitle_filter.value, this); this.submit;" />';
				$code_subtitle .= '<br />';

				//Prediger
 				$code_preacher  = '<select name="sel_preacher_filter" size="0" onchange="jumpToUrl(\'?action=submit_preacher_filter&amp;set_filter=\'+this.options[this.selectedIndex].value);">';
 				$code_preacher .= '<option value=""></option>';
 				ko_get_preachers($preachers);
				foreach($preachers as $i => $preacher) {
				  $sel = $_SESSION["tape_preacher_filter"] == $preacher ? 'selected="selected"' : "";
				  $code_preacher .= '<option value="'.urlencode($preacher).'" '.$sel.'>'.substr($preacher, 0, ITEMLIST_LENGTH_MAX).(strlen($preacher)>ITEMLIST_LENGTH_MAX?"..":"").'</option>';
				}
				$code_preacher .= '</select><br />';


				$submenu[$menucounter]["titel"] = getLL("submenu_tapes_title_filter");

				if(isset($_SESSION["tape_group_filter"])) {$pre="<b>";$post="</b>";} else $pre=$post="";
				$submenu[$menucounter]["output"][$itemcounter] = $pre.getLL("kota_ko_tapes_group_id").$post;
				$submenu[$menucounter]["html"][$itemcounter++] = $code_group;

				if(isset($_SESSION["tape_serie_filter"])) {$pre="<b>";$post="</b>";} else $pre=$post="";
				$submenu[$menucounter]["output"][$itemcounter] = $pre.getLL("kota_ko_tapes_serie_id").$post;
				$submenu[$menucounter]["html"][$itemcounter++] = $code_serie;

				if(isset($_SESSION["tape_title_filter"])) {$pre="<b>";$post="</b>: '".ko_html($_SESSION["tape_title_filter"])."'";} else $pre=$post="";
				$submenu[$menucounter]["output"][$itemcounter] = $pre.getLL("kota_ko_tapes_title").$post;
				$submenu[$menucounter]["html"][$itemcounter++] = $code_title;

				if(isset($_SESSION["tape_subtitle_filter"])) {$pre="<b>";$post="</b>: '".ko_html($_SESSION["tape_subtitle_filter"])."'";} else $pre=$post="";
				$submenu[$menucounter]["output"][$itemcounter] = $pre.getLL("kota_ko_tapes_subtitle").$post;
				$submenu[$menucounter]["html"][$itemcounter++] = $code_subtitle;

				if(isset($_SESSION["tape_preacher_filter"])) {$pre="<b>";$post="</b>";} else $pre=$post="";
				$submenu[$menucounter]["output"][$itemcounter] = $pre.getLL("kota_ko_tapes_preacher").$post;
				$submenu[$menucounter]["html"][$itemcounter++] = $code_preacher;

				$submenu[$menucounter]["output"][$itemcounter++] = "";
				$submenu[$menucounter]["output"][$itemcounter] = getLL("tapes_remove_all_filters");
				$submenu[$menucounter]["link"][$itemcounter++] = $ko_path."tapes/index.php?action=clear_filters";
			break;


			case "settings":
				if($max_rights > 3) {
					$found = TRUE;
					$submenu[$menucounter]["titel"] = getLL("submenu_tapes_title_settings");
					$submenu[$menucounter]["output"][$itemcounter] = ko_menuitem("tapes", "settings");
					$submenu[$menucounter]["link"][$itemcounter++] = $ko_path."tapes/index.php?action=settings";
					$submenu[$menucounter]["output"][$itemcounter] = ko_menuitem("tapes", "list_printlayouts");
					$submenu[$menucounter]["link"][$itemcounter++] = $ko_path."tapes/index.php?action=list_printlayouts";
				}
			break;


			default:
        if($menu) { 
          $submenu[$menucounter] = submenu($menu, $position, $state, 3, 'tapes');
          $found = (is_array($submenu[$menucounter])); 
        }
		}//switch(menu)

		//Plugins erlauben, Menuitems hinzuzufügen
		hook_submenu("tapes", $menu, $submenu, $menucounter, $itemcounter);

    if($found) {
			$submenu[$menucounter]["key"] = "tapes_".$menu;
			$submenu[$menucounter]["id"] = $menu;
			$submenu[$menucounter]["mod"] = "tapes";
			$submenu[$menucounter]["sesid"] = session_id();
      $submenu[$menucounter]["position"] = $position;
      $submenu[$menucounter]["state"] = $state;

			if($display == 1) {
				$smarty->assign("sm", $submenu[$menucounter]);
				$smarty->assign("ko_path", $ko_path);
				$smarty->assign("help", ko_get_help("tapes", "submenu_".$menu));
				$smarty->display("ko_submenu.tpl");
			} else if($display == 2) {
				$smarty->assign("sm", $submenu[$menucounter]);
				$smarty->assign("ko_path", $ko_path);
				$return = "sm_tapes_".$menu."@@@";
				$return .= $smarty->fetch("ko_submenu.tpl");
			}

			$smarty->clear_assign("help");
			$menucounter++;
    }


	}//foreach(namen as menu)

	if($display == 2) {
		return $return;
	} else if($display == 3) {
		return $submenu;
	}
}//submenu_tapes()





function submenu_groups($namen, $position, $state, $display=1) {
	global $ko_path, $smarty;
	global $my_submenu;

	$return = "";

	if(!ko_module_installed("groups")) return FALSE;
	$all_rights = ko_get_access_all('groups_admin', '', $max_rights);

	$namen = explode(",", $namen);
	$menucounter = 0;
  foreach($namen as $menu) {
		$found = FALSE;

    $itemcounter = 0;
    switch($menu) {
			
			case "groups":
				$found = TRUE;
				$submenu[$menucounter]["titel"] = getLL("submenu_groups_title_groups");
				if($max_rights > 2) {
					$submenu[$menucounter]["output"][$itemcounter] = ko_menuitem("groups", "new_group");
					$submenu[$menucounter]["link"][$itemcounter++] = $ko_path."groups/index.php?action=new_group";
				}
				$submenu[$menucounter]["output"][$itemcounter] = ko_menuitem("groups", "list_groups");
				$submenu[$menucounter]["link"][$itemcounter++] = $ko_path."groups/index.php?action=list_groups";
				if($all_rights > 2) {
					$submenu[$menucounter]["output"][$itemcounter] = ko_menuitem("groups", "list_datafields");
					$submenu[$menucounter]["link"][$itemcounter++] = $ko_path."groups/index.php?action=list_datafields";
				}
				$submenu[$menucounter]["output"][$itemcounter] = ko_menuitem("groups", "groups_settings");
				$submenu[$menucounter]["link"][$itemcounter++] = $ko_path."groups/index.php?action=groups_settings";
			break;


			case "roles":
				$found = TRUE;
				$submenu[$menucounter]["titel"] = getLL("submenu_groups_title_roles");
				if($all_rights > 2) {
					$submenu[$menucounter]["output"][$itemcounter] = ko_menuitem("groups", "new_role");
					$submenu[$menucounter]["link"][$itemcounter++] = $ko_path."groups/index.php?action=new_role";
				}
				$submenu[$menucounter]["output"][$itemcounter] = ko_menuitem("groups", "list_roles");
				$submenu[$menucounter]["link"][$itemcounter++] = $ko_path."groups/index.php?action=list_roles";
			break;


			case "rights":
				if($all_rights > 2) {
					$found = TRUE;
					$submenu[$menucounter]["titel"] = getLL("submenu_groups_title_rights");
					$submenu[$menucounter]["output"][$itemcounter] = ko_menuitem("groups", "list_rights");
					$submenu[$menucounter]["link"][$itemcounter++] = $ko_path."groups/index.php?action=list_rights";
				}
			break;


			case 'dffilter':
				if($all_rights > 2) {
					$found = TRUE;
					$submenu[$menucounter]['titel'] = getLL('submenu_groups_title_dffilter');

					$checked = $_SESSION['groups_show_hidden_datafields'] ? 'checked="checked"' : '';
					$code = '<input type="checkbox" name="groups_dffilter" id="chk_groups_dffilter" '.$checked.' />';
					$code .= '<label for="chk_groups_dffilter">'.getLL('submenu_groups_dffilter_yes').'</label>';
					$submenu[$menucounter]['output'][$itemcounter] = getLL('submenu_groups_dffilter');
					$submenu[$menucounter]['link'][$itemcounter] = '';
					$submenu[$menucounter]['html'][$itemcounter++] = $code;
				}
			break;



			case 'export':
				$found = TRUE;
				$submenu[$menucounter]['titel'] = getLL('submenu_groups_title_export');
				$submenu[$menucounter]['form'] = TRUE;
				$submenu[$menucounter]['form_hidden_inputs'] = array(array('name' => 'ids', 'value' => ''));

				$submenu[$menucounter]['output'][$itemcounter] = ko_menuitem('groups', 'exportxls');
				$submenu[$menucounter]['link'][$itemcounter++] = $ko_path.'groups/index.php?action=exportxls';

				$pdf_layouts = db_select_data('ko_pdf_layout', "WHERE `type` = 'groups'", 'id, name', 'ORDER BY `name` ASC');
				if(sizeof($pdf_layouts) > 0) {
					$html = '<select name="pdf_layout_id" size="0" onchange="set_ids_from_chk(this); jumpToUrl(\'?action=export_pdf&layout_id=\'+this.options[this.selectedIndex].value+\'&gid=\'+document.getElementsByName(\'ids\')[0].value);">';
					$html .= '<option value=""></option>';
					foreach($pdf_layouts as $l) {
						$html .= '<option value="'.$l['id'].'">'.ko_html($l['name']).'</option>';
					}
					$html .= '</select><br />';

					$submenu[$menucounter]['output'][$itemcounter++] = '';

					$submenu[$menucounter]['output'][$itemcounter] = getLL('submenu_groups_export_pdf');
					$submenu[$menucounter]['link'][$itemcounter] = '';
					$submenu[$menucounter]['html'][$itemcounter++] = $html;
				}
			break;


			default:
        if($menu) { 
          $submenu[$menucounter] = submenu($menu, $position, $state, 3, 'groups');
          $found = (is_array($submenu[$menucounter])); 
        }
		}//switch(menu)

		//Plugins erlauben, Menuitems hinzuzufügen
		hook_submenu("groups", $menu, $submenu, $menucounter, $itemcounter);

    if($found) {
			$submenu[$menucounter]["key"] = "groups_".$menu;
			$submenu[$menucounter]["id"] = $menu;
			$submenu[$menucounter]["mod"] = "groups";
			$submenu[$menucounter]["sesid"] = session_id();
      $submenu[$menucounter]["position"] = $position;
      $submenu[$menucounter]["state"] = $state;

			if($display == 1) {
				$smarty->assign("sm", $submenu[$menucounter]);
				$smarty->assign("ko_path", $ko_path);
				$smarty->assign("help", ko_get_help("groups", "submenu_".$menu));
				$smarty->display("ko_submenu.tpl");
			} else if($display == 2) {
				$smarty->assign("sm", $submenu[$menucounter]);
				$smarty->assign("ko_path", $ko_path);
				$return = "sm_groups_".$menu."@@@";
				$return .= $smarty->fetch("ko_submenu.tpl");
			}

			$smarty->clear_assign("help");
			$menucounter++;
    }


	}//foreach(namen as menu)

	if($display == 2) {
		return $return;
	} else if($display == 3) {
		return $submenu;
	}
}//submenu_groups()





function submenu_donations($namen, $position, $state, $display=1) {
	global $ko_path, $smarty;
	global $ko_menu_akt, $access;
	global $my_submenu;

	$return = "";

	$all_rights = ko_get_access_all('donations_admin', '', $max_rights);
	if($max_rights < 1) return FALSE;

	$namen = explode(",", $namen);
	$menucounter = 0;
  foreach($namen as $menu) {
		$found = FALSE;

    $itemcounter = 0;
    switch($menu) {
			
			case "donations":
				$found = TRUE;
				$submenu[$menucounter]["titel"] = getLL("submenu_donations_title_donations");
				if($max_rights > 1 && db_get_count("ko_donations_accounts") > 0) {
					$submenu[$menucounter]["output"][$itemcounter] = ko_menuitem("donations", "new_donation");
					$submenu[$menucounter]["link"][$itemcounter++] = $ko_path."donations/index.php?action=new_donation";

					if(ko_get_setting('donations_use_promise')) {
						$submenu[$menucounter]['output'][$itemcounter] = ko_menuitem('donations', 'new_promise');
						$submenu[$menucounter]['link'][$itemcounter++] = $ko_path.'donations/index.php?action=new_promise';
					}
				}
				$submenu[$menucounter]["output"][$itemcounter] = ko_menuitem("donations", "list_donations");
				$submenu[$menucounter]["link"][$itemcounter++] = $ko_path."donations/index.php?action=list_donations";
				if($max_rights > 1) {
					$submenu[$menucounter]["output"][$itemcounter++] = "";
					$submenu[$menucounter]["output"][$itemcounter] = ko_menuitem("donations", "list_reoccuring_donations");
					$submenu[$menucounter]["link"][$itemcounter++] = $ko_path."donations/index.php?action=list_reoccuring_donations";
				}
				if($max_rights > 2) {
					$submenu[$menucounter]["output"][$itemcounter++] = "";
					$submenu[$menucounter]["output"][$itemcounter] = ko_menuitem("donations", "merge");
					$submenu[$menucounter]["link"][$itemcounter++] = $ko_path."donations/index.php?action=merge";
				}
				if($max_rights > 3) {
					$submenu[$menucounter]["output"][$itemcounter++] = "";
					$submenu[$menucounter]["output"][$itemcounter] = ko_menuitem("donations", "show_stats");
					$submenu[$menucounter]["link"][$itemcounter++] = $ko_path."donations/index.php?action=show_stats";
				}
				if($max_rights > 0) {
					$submenu[$menucounter]['output'][$itemcounter] = ko_menuitem('donations', 'donation_settings');
					$submenu[$menucounter]['link'][$itemcounter++] = $ko_path.'donations/index.php?action=donation_settings';
				}
			break;

			case "accounts":
				if($max_rights > 3) {
					$found = TRUE;
					$submenu[$menucounter]["titel"] = getLL("submenu_donations_title_accounts");
					$submenu[$menucounter]["output"][$itemcounter] = ko_menuitem("donations", "new_account");
					$submenu[$menucounter]["link"][$itemcounter++] = $ko_path."donations/index.php?action=new_account";
					$submenu[$menucounter]["output"][$itemcounter] = ko_menuitem("donations", "list_accounts");
					$submenu[$menucounter]["link"][$itemcounter++] = $ko_path."donations/index.php?action=list_accounts";
				}
			break;

			case "itemlist_accounts":
				if($max_rights < 1) break;
				$found = TRUE;

				//Alle Objekte auslesen
				$counter = 0;
				$gruppen = db_select_data("ko_donations_accounts", "", "*", "ORDER by number ASC");
				foreach($gruppen as $i_i => $i) {
					if($all_rights > 0 || $access['donations'][$i_i] > 0) {
						$aktiv = 0;
						foreach($_SESSION["show_accounts"]as $s) if($s == $i_i) $aktiv = 1;
						$itemlist[$counter]["name"] = ko_html($i["number"])."&nbsp;".ko_html($i["name"]);
						$itemlist[$counter]["aktiv"] = $aktiv;
						$itemlist[$counter++]["value"] = $i_i;
					}//if(show_view[i_i])
				}//foreach(items)
				$smarty->assign("tpl_itemlist_select", $itemlist);
										
				//Get all presets
				$akt_value = $_SESSION["show_accounts"];
				$akt_value = implode(",", $_SESSION["show_accounts"]);
				$itemset = array_merge((array)ko_get_userpref('-1', '', 'accounts_itemset', 'ORDER BY `key` ASC'), (array)ko_get_userpref($_SESSION['ses_userid'], '', 'accounts_itemset', 'ORDER BY `key` ASC'));
				foreach($itemset as $i) {
					$value = $i['user_id'] == '-1' ? '@G@'.$i['key'] : $i['key'];
					$itemselect_values[] = $value;
					$itemselect_output[] = $i['user_id'] == '-1' ? getLL('itemlist_global_short').' '.$i['key'] : $i['key'];
			    if($i["value"] == $akt_value) $itemselect_selected = $value;
				}
				$smarty->assign("tpl_itemlist_values", $itemselect_values);
				$smarty->assign("tpl_itemlist_output", $itemselect_output);
				$smarty->assign("tpl_itemlist_selected", $itemselect_selected);
				if($max_rights > 3) $smarty->assign('allow_global', TRUE);
																
				$submenu[$menucounter]["titel"] = getLL("submenu_donations_title_itemlist_accounts");
				if($state == "closed") continue;

				$submenu[$menucounter]["output"][$itemcounter++] = "[itemlist]";
			break;

			
			case "filter":
				if($max_rights < 1) break;
				$found = TRUE;

				$submenu[$menucounter]['form'] = TRUE;
				$submenu[$menucounter]['form_hidden_inputs'] = array(array('name' => 'action', 'value' => ''),
																														 array('name' => 'ids', 'value' => ''));

				$submenu[$menucounter]["titel"] = getLL("submenu_donations_title_filter");

				//Date filter
				$date_field = ko_get_userpref($_SESSION['ses_userid'], 'donations_date_field');
				if(!$date_field || !in_array($date_field, array('date', 'valutadate'))) $date_field = 'date';

				$code  = '<input type="text" name="donations_filter[date1]" value="'.sql2datum($_SESSION["donations_filter"]["date1"]).'" size="10" /> - ';
				$code .= '<input type="text" name="donations_filter[date2]" value="'.sql2datum($_SESSION["donations_filter"]["date2"]).'" size="10" /><br />';
				$pre  = $_SESSION["donations_filter"]["date1"] || $_SESSION["donations_filter"]["date2"] ? '<b>' : "";
				$post = $_SESSION["donations_filter"]["date1"] || $_SESSION["donations_filter"]["date2"] ? '</b>' : "";
				$submenu[$menucounter]['output'][$itemcounter] = $pre.getLL('kota_ko_donations_'.$date_field).':'.$post;
				$submenu[$menucounter]["html"][$itemcounter++] = $code;

				//Leute-modul filter
				$code  = '<select size="0" name="donations_filter[leute]"><option value=""></option>';
				$filterset = array_merge((array)ko_get_userpref('-1', '', 'filterset', 'ORDER BY `key` ASC'), (array)ko_get_userpref($_SESSION['ses_userid'], '', 'filterset', 'ORDER BY `key` ASC'));
				foreach($filterset as $f) {
					$value = $f['user_id'] == '-1' ? '@G@'.$f['key'] : $f['key'];
					$desc = $f['user_id'] == '-1' ? getLL('itemlist_global_short').' '.$f['key'] : $f['key'];
				  if($_SESSION["donations_filter"]["leute"] == $value) $sel = 'selected="selected"'; else $sel = "";
				  $code .= '<option value="'.$value.'" '.$sel.'>'.$desc.'</option>';
				}
				$code .= '</select><br />';
				$pre  = $_SESSION["donations_filter"]["leute"] ? '<b>' : "";
				$post = $_SESSION["donations_filter"]["leute"] ? '</b>' : "";
				$submenu[$menucounter]["output"][$itemcounter] = $pre.getLL("donations_filter_leute").$post;
				$submenu[$menucounter]["html"][$itemcounter++] = $code;
				
				//Person-search filter
				$code  = '<input type="text" name="donations_filter[personString]" value="'.htmlspecialchars($_SESSION["donations_filter"]["personString"]).'" size="17" /><br />';
				
				$pre  = $_SESSION["donations_filter"]["personString"] ? '<b>' : "";
				$post = $_SESSION["donations_filter"]["personString"] ? '</b>' : "";
				$submenu[$menucounter]["output"][$itemcounter] = $pre.getLL("donations_filter_personstring").$post;
				$submenu[$menucounter]["html"][$itemcounter++] = $code;

				//Display person filter
				if($_SESSION["donations_filter"]["person"]) {
					ko_get_person_by_id($_SESSION["donations_filter"]["person"], $fp, TRUE);
					$name = trim($fp['firm'].' '.$fp['vorname'].' '.$fp['nachname']);
					$submenu[$menucounter]["output"][$itemcounter] = '<b>'.getLL("donations_filter_person").'</b>';
					$submenu[$menucounter]["html"][$itemcounter++] = '<a href="index.php?action=clear_person_filter">'.$name.'&nbsp;<img src="'.$ko_path.'images/icon_trash.png" border="0" alt="del" /></a><br />';
				}

				//Search for amount
				$code  = '<input type="text" name="donations_filter[amount]" value="'.htmlspecialchars($_SESSION['donations_filter']['amount']).'" size="15" />';
				$help = ko_get_help('donations', 'submenu_filter_amount', array('ph' => 'l'));
				if($help['show']) $code .= $help['link'];
				$code .= '<br />';
				
				$pre  = $_SESSION['donations_filter']['amount'] ? '<b>' : '';
				$post = $_SESSION['donations_filter']['amount'] ? '</b>' : '';
				$submenu[$menucounter]['output'][$itemcounter] = $pre.getLL('donations_filter_amount').$post;
				$submenu[$menucounter]['html'][$itemcounter++] = $code;


				//Promise
				$checked = $_SESSION['donations_filter']['promise'] == 1 ? 'checked="checked"' : '';
				$code  = '<input type="checkbox" name="donations_filter[promise]" value="1" '.$checked.' />';
				$help = ko_get_help('donations', 'submenu_filter_promise', array('ph' => 'l'));
				if($help['show']) $code .= $help['link'];
				$code .= '<br />';
				
				$pre  = $_SESSION['donations_filter']['promise'] ? '<b>' : '';
				$post = $_SESSION['donations_filter']['promise'] ? '</b>' : '';
				$submenu[$menucounter]['output'][$itemcounter] = $pre.getLL('donations_filter_promise').$post;
				$submenu[$menucounter]['html'][$itemcounter++] = $code;


				//Submit
				$submenu[$menucounter]["output"][$itemcounter] = " ";
				$submenu[$menucounter]["no_ul"][$itemcounter] = TRUE;
				$submenu[$menucounter]["html"][$itemcounter] = '<div align="center"><input type="submit" name="submit_donations_filter" id="submit_donations_filter" value="'.getLL("donations_filter_submit").'" onclick="set_action(\'set_filter\', this);this.submit;" style="margin: 5px;" />';
				//Clear
				$submenu[$menucounter]["html"][$itemcounter] .= '<br /><input type="submit" name="clear_donations_filter" value="'.getLL("donations_filter_clear").'" onclick="set_action(\'clear_filter\', this);this.submit;"/></div>';
			break;


			case "export":
				if($max_rights > 0) {
					$found = TRUE;
					$submenu[$menucounter]["titel"] = getLL("submenu_donations_title_export");
					$submenu[$menucounter]["output"][$itemcounter] = " ";
					$submenu[$menucounter]['no_ul'][$itemcounter] = TRUE;
					$code  = '<select name="export_mode" size="0" onchange="jumpToUrl(\'?action=export_donations&export_mode=\'+escape(this.options[this.selectedIndex].value));">';
					$code .= '<option value="">'.getLL('donations_export_submit').'</option>';
					$code .= '<option value="" disabled="disabled">----------</option>';
					$code .= '<option value="person">'.getLL('donations_export_mode_person').'</option>';
					$code .= '<option value="family">'.getLL('donations_export_mode_family').'</option>';
					$code .= '<option value="couple">'.getLL('donations_export_mode_couple').'</option>';
					$code .= '<option value="all">'.getLL('donations_export_mode_all').'</option>';
					for($i=0; $i<2; $i++) {
						$year = date('Y')-$i;
						$code .= '<option value="statsY'.$year.'">'.getLL('donations_export_mode_statsY').' '.$year.'</option>';
					}
					$code .= '<option value="statsM">'.getLL("donations_export_mode_statsM").'</option>';

					//Add exports from DB (can be added by plugins)
					$pdf_layouts = db_select_data('ko_pdf_layout', "WHERE `type` = 'donations'", 'id, name', 'ORDER BY `name` ASC');
					if(sizeof($pdf_layouts) > 0) {
						$code .= '<option value="" disabled="disabled">----------</option>';
						foreach($pdf_layouts as $layout) {
							$code .= '<option value="'.$layout['id'].'">'.ko_html($layout['name']).'</option>';
						}
					}

					$code .= '</select><br />';
					$submenu[$menucounter]["html"][$itemcounter] = $code;
				}
			break;


			default:
        if($menu) { 
          $submenu[$menucounter] = submenu($menu, $position, $state, 3, 'donations');
          $found = (is_array($submenu[$menucounter])); 
        }
		}//switch(menu)

		//Plugins erlauben, Menuitems hinzuzufügen
		hook_submenu("donations", $menu, $submenu, $menucounter, $itemcounter);

    if($found) {
			$submenu[$menucounter]["key"] = "donations_".$menu;
			$submenu[$menucounter]["id"] = $menu;
			$submenu[$menucounter]["mod"] = "donations";
			$submenu[$menucounter]["sesid"] = session_id();
      $submenu[$menucounter]["position"] = $position;
      $submenu[$menucounter]["state"] = $state;

			if($display == 1) {
				$smarty->assign("sm", $submenu[$menucounter]);
				$smarty->assign("ko_path", $ko_path);
				$smarty->assign("help", ko_get_help("donations", "submenu_".$menu));
				$smarty->display("ko_submenu.tpl");
			} else if($display == 2) {
				$smarty->assign("sm", $submenu[$menucounter]);
				$smarty->assign("ko_path", $ko_path);
				$return = "sm_donations_".$menu."@@@";
				$return .= $smarty->fetch("ko_submenu.tpl");
			}

			$smarty->clear_assign("help");
			$menucounter++;
    }


	}//foreach(namen as menu)

	if($display == 2) {
		return $return;
	} else if($display == 3) {
		return $submenu;
	}
}//submenu_donations()




function submenu_tracking($namen, $position, $state, $display=1) {
	global $ko_path, $smarty;
	global $js_calendar;
	global $ko_menu_akt, $access;
	global $my_submenu;
	global $LEUTE_NO_FAMILY;

	$return = '';

	$all_rights = ko_get_access_all('tracking_admin', '', $max_rights);
	if($max_rights < 1) return FALSE;

	$namen = explode(',', $namen);
	$menucounter = 0;
  foreach($namen as $menu) {
		$found = FALSE;

    $itemcounter = 0;
    switch($menu) {
			
			case 'trackings':
				$found = TRUE;
				$submenu[$menucounter]['titel'] = getLL('submenu_tracking_title_trackings');
				if($max_rights > 3) {
					$submenu[$menucounter]['output'][$itemcounter] = ko_menuitem('tracking', 'new_tracking');
					$submenu[$menucounter]['link'][$itemcounter++] = $ko_path.'tracking/index.php?action=new_tracking';
				}
				$submenu[$menucounter]['output'][$itemcounter] = ko_menuitem('tracking', 'list_trackings');
				$submenu[$menucounter]['link'][$itemcounter++] = $ko_path.'tracking/index.php?action=list_trackings';

				if($max_rights > 1) {
					$submenu[$menucounter]['output'][$itemcounter] = ko_menuitem('tracking', 'mod_entries');
					$submenu[$menucounter]['link'][$itemcounter++] = $ko_path.'tracking/index.php?action=mod_entries';
				}

				$submenu[$menucounter]['output'][$itemcounter] = ko_menuitem('tracking', 'tracking_settings');
				$submenu[$menucounter]['link'][$itemcounter++] = $ko_path.'tracking/index.php?action=tracking_settings';

				if($max_rights > 0 && $ko_menu_akt == 'tracking') {
					$code = '<select name="sel_tracking" style="width:130px;" size="1" onchange="jumpToUrl(\'?action=enter_tracking&amp;id=\'+this.options[this.selectedIndex].value);">';
					$code .= '<option value=""></option>';
					//Get all tracking groups first
					$tgroups = db_select_data('ko_tracking_groups', '', '*', 'ORDER by name ASC');
					foreach($tgroups as $group) {
						$code .= '<option value="" disabled="disabled">'.strtoupper($group['name']).'</option>';
						$trackings = db_select_data('ko_tracking', "WHERE `group_id` = '".$group['id']."'", '*', 'ORDER by name ASC');
						foreach($trackings as $t_i => $t) {
							if($all_rights < 1 && $access['tracking'][$t_i] < 1) continue;
							$active = ($t_i == $_SESSION['tracking_id'] && $_SESSION['show'] == 'enter_tracking');
							$code .= '<option value="'.$t_i.'" '.($active?'selected="selected"':'').'>&nbsp;&nbsp;'.ko_html($t['name']).'</option>';
						}//foreach(items)
					}
					//Get all trackings without a group
					$trackings = db_select_data('ko_tracking', 'WHERE `group_id` = \'0\'', '*', 'ORDER by name ASC');
					foreach($trackings as $t_i => $t) {
						if($all_rights < 1 && $access['tracking'][$t_i] < 1) continue;
						$active = ($t_i == $_SESSION['tracking_id'] && $_SESSION['show'] == 'enter_tracking');
						$code .= '<option value="'.$t_i.'" '.($active?'selected="selected"':'').'>'.ko_html($t['name']).'</option>';
					}//foreach(items)

					$code .= '</select><br />' . ($_SESSION['show'] == 'list_trackings' ? '<br />' : '');

					$submenu[$menucounter]['output'][$itemcounter++] = '';
					$pre = $_SESSION['show'] == 'enter_tracking' ? '<b>' : '';
					$post = $_SESSION['show'] == 'enter_tracking' ? '</b>' : '';
					$submenu[$menucounter]['output'][$itemcounter] = $pre.getLL('submenu_tracking_enter_tracking').$post;
					$submenu[$menucounter]['html'][$itemcounter++] = $code;
				}

				// show option do display hidden entries (only in list_entries view)
				if ($_SESSION['show'] == 'list_trackings' && db_get_count('ko_tracking', 'id', ' and `hidden` = 1') > 0) {
					$submenu[$menucounter]["output"][$itemcounter] = " ";
					$submenu[$menucounter]["link"][$itemcounter] = "";
					$checked = $_SESSION['tracking_filter']['show_hidden'];
					$submenu[$menucounter]["html"][$itemcounter++]  =
						'<input type="checkbox" id="chk_f_hidden" name="chk_f_hidden" '.($checked == 1 ? 'checked="checked"' : '').' onchange="sendReq(\'../tracking/inc/ajax.php\', \'action,showhidden,sesid\', \'setfilterhidden,\'+this.checked+\','.session_id().'\', do_element);" />
					<label for="chk_f_hidden">'.getLL('tracking_filter_show_hidden')."</label>
					<br />";
				}

			break;

		case 'filter':
			$found = TRUE;
			$ko_guest = $_SESSION["ses_userid"] == ko_get_guest_id();

			$submenu[$menucounter]['form'] = TRUE;
			$submenu[$menucounter]['form_hidden_inputs'] = array(array('name' => 'action', 'value' => ''),
				array('name' => 'ids', 'value' => ''));

			$submenu[$menucounter]['titel'] = getLL('submenu_donations_title_filter');

			$showSaveForm = ($_SESSION['tracking_filter']['date1'] != '' || $_SESSION['tracking_filter']['date2'] != '');

			//Get all presets
			$akt_value = trim($_SESSION['tracking_filter']['date1']) . ',' . trim($_SESSION['tracking_filter']['date2']);
			$itemset = array_merge((array)ko_get_userpref('-1', '', 'tracking_filterpreset', 'ORDER BY `key` ASC'), (array)ko_get_userpref($_SESSION['ses_userid'], '', 'tracking_filterpreset', 'ORDER BY `key` ASC'));
			koNotifier::Instance()->addTextDebug(print_r($itemset, True));
			foreach($itemset as $i) {
				$value = $i['user_id'] == '-1' ? '@G@'.$i['key'] : $i['key'];
				$itemselect_values[] = $value;
				$itemselect_output[] = $i['user_id'] == '-1' ? getLL('itemlist_global_short').' '.$i['key'] : $i['key'];
				if(trim($i['value']) == $akt_value) $itemselect_selected = $value;
			}

			if($max_rights > 3)  {
				$smarty->assign('allow_global', TRUE);
				$allow_global = TRUE;
			}

			$pre_select = '<select name="sel_filterpreset" size="0" onchange="sendReq(\'../tracking/inc/ajax.php\', \'action,name,sesid\', \'filterpresetopen,\'+this.options[this.selectedIndex].value+\','.session_id().'\', do_element);" style="width: 150px;">"';
			$pre_select .= '<option value=""></option>';
			foreach ($itemselect_values as $k => $v) {
				$pre_select .= '<option value="' . $v . '" ' . ($v == $itemselect_selected ? 'selected="selected"' : '') . '>' . $itemselect_output[$k] . '</option>';
			}
			$pre_select .= '</select>';

			// delete date preset
			if (!$ko_guest) {
				$pre_select .= '<input style="margin-left:3px" type="image" src="' . $ko_path . 'images/icon_trash.png" alt="' . getLL('itemlist_delete_preset') . '" title="' . getLL('itemlist_delete_preset') . '" onclick="c = confirm(\'' . getLL('itemlist_delete_preset_confirm') . '\');if(!c) return false; sendReq(\'../tracking/inc/ajax.php\', \'action,name,sesid\', \'filterpresetdelete,\'+document.getElementsByName(\'sel_filterpreset\')[0].options[document.getElementsByName(\'sel_filterpreset\')[0].selectedIndex].value+\',' . session_id() . '\', do_element); return false;" />';
				$pre_select .= '<br /><br />';
			}

			$submenu[$menucounter]['output'][$itemcounter] = getLL("itemlist_open_preset");
			$submenu[$menucounter]['html'][$itemcounter++] = $pre_select;



			//Date filter
			$code1 = $js_calendar->make_input_field(array('ifFormat' => '%Y-%m-%d'), array('name' => 'tracking_filter[date1]', 'value' => $_SESSION['tracking_filter']['date1'], 'size' => 12));
			$code2 = $js_calendar->make_input_field(array('ifFormat' => '%Y-%m-%d'), array('name' => 'tracking_filter[date2]', 'value' => $_SESSION['tracking_filter']['date2'], 'size' => 12));
			$pre  = $_SESSION['tracking_filter']['date1'] || $_SESSION['tracking_filter']['date2'] ? '<b>' : '';
			$post = $_SESSION['tracking_filter']['date1'] || $_SESSION['tracking_filter']['date2'] ? '</b>' : '';
			$submenu[$menucounter]['output'][$itemcounter] = $pre.getLL('tracking_filter_date').$post.': '.getLL('time_from');
			$submenu[$menucounter]['html'][$itemcounter++] = $code1.'<br />';
			$submenu[$menucounter]['output'][$itemcounter] = getLL('time_to');
			$submenu[$menucounter]['html'][$itemcounter++] = $code2.'<br /><br />';


			//Group/smallgroup/preset filter
			if($_SESSION['show'] == 'enter_tracking' && $_SESSION['tracking_id'] > 0) {
				$tracking = db_select_data('ko_tracking', 'WHERE `id` = \''.$_SESSION['tracking_id'].'\'', '*', '', '', TRUE);
				$filters = explode(',', $tracking['filter']);
				if(sizeof($filters) > 1) {
					$html  = '<select name="tracking_filter[filter]" size="0" onchange="document.getElementById(\'submit_tracking_filter\').click()">';
					$html .= '<option value="">'.getLL('all').'</option>';
					foreach($filters as $filter) {
						$sel = $_SESSION['tracking_filter']['filter'] == $filter ? 'selected="selected"' : '';
						$html .= '<option value="'.$filter.'" '.$sel.'>'.ko_tracking_get_filter_name($filter).'</option>';
					}
					$html .= '</select>';

					$submenu[$menucounter]['output'][$itemcounter] = getLL('tracking_filter_filter');
					$submenu[$menucounter]['link'][$itemcounter] = '';
					$submenu[$menucounter]['html'][$itemcounter++] = $html.'<br />';
				}
			}

			//Submit
			$submenu[$menucounter]['output'][$itemcounter] = ' ';
			$submenu[$menucounter]['no_ul'][$itemcounter] = TRUE;
			$submenu[$menucounter]['html'][$itemcounter] = '<div style="width:170px;padding:1px" align="center"><input type="submit" name="submit_tracking_filter" id="submit_tracking_filter" value="'.getLL('tracking_filter_submit').'" onclick="set_action(\'set_filter\', this);this.submit;" style="margin:5px" />';
			//Clear
			if($showSaveForm) {
				$submenu[$menucounter]["html"][$itemcounter] .= '&nbsp;<br /><input style="margin:0px 5px" type="submit" name="clear_tracking_filter" value="'.getLL('tracking_filter_clear').'" onclick="set_action(\'clear_filter\', this);this.submit;"/>';
			}
			$submenu[$menucounter]["html"][$itemcounter++] .= '</div><br />';


			if ($showSaveForm) {
				// save new date preset
				if (!$ko_guest) {
					if ($allow_global) {
						$html .= '<div style="font-size:9px;"><input type="checkbox" name="chk_itemlist_global" value="1" />' . getLL('itemlist_global') . '</div>';
					}
					$html .= '<input type="text" name="txt_itemlist_new" style="width:135px;" />';
					$html .= '<input style="margin-left:3px" type="image" src="' . $ko_path . 'images/icon_save.gif" id="save_itemlist_" alt="' . getLL('itemlist_save_preset') . '" title="' . getLL('itemlist_save_preset') . '" onclick="sendReq(\'../tracking/inc/ajax.php\', \'action,name' . ($allow_global ? ',global' : '') . ',sesid\', \'filterpresetsave,\'+document.getElementsByName(\'txt_itemlist_new\')[0].value+' . ($allow_global ? '\',\'+document.getElementsByName(\'chk_itemlist_global\')[0].checked+' : '') . '\',' . session_id() . '\', do_element); return false;" /><br />';
				}

				$submenu[$menucounter]['output'][$itemcounter] = getLL("itemlist_save_preset");
				$submenu[$menucounter]['html'][$itemcounter++] = $html;
			}


		break;


			case 'export':
				$found = TRUE;

				$submenu[$menucounter]['form'] = TRUE;
				$submenu[$menucounter]['form_hidden_inputs'] = array(array('name' => 'action', 'value' => ''),
																														 array('name' => 'ids', 'value' => ''));

				$submenu[$menucounter]['titel'] = getLL('submenu_tracking_title_export');

				//Select dates to be used for export
				$show_export_dates = FALSE;
				if($_SESSION['show'] == 'list_trackings') {
					$show_export_dates = $_SESSION['tracking_filter']['date1'] || $_SESSION['tracking_filter']['date2'];
				} else {
					$tracking = db_select_data('ko_tracking', 'WHERE `id` = \''.$_SESSION['tracking_id'].'\'', '*', '', '', TRUE);
					if($tracking['date_weekdays'] != '' && !($_SESSION['tracking_filter']['date1'] || $_SESSION['tracking_filter']['date2'])) {
						$show_export_dates = FALSE;
					} else  {
						$show_export_dates = TRUE;
					}
				}
				if($show_export_dates) {
					$submenu[$menucounter]['output'][$itemcounter] = getLL('tracking_export_dates');
					$submenu[$menucounter]['link'][$itemcounter] = '';
					$html  = '<select name="sel_dates" id="sel_dates" size="0">';
					//Mark filter if a filter is set, otherwise mark current
					$sel1 = ($_SESSION['tracking_filter']['date1'] || $_SESSION['tracking_filter']['date2']) ? '' : 'selected="selected"';
					$sel2 = ($_SESSION['tracking_filter']['date1'] || $_SESSION['tracking_filter']['date2']) ? 'selected="selected"' : '';
					if($_SESSION['tracking_filter']['date1'] || $_SESSION['tracking_filter']['date2']) {
						$html .= '<option value="filter" '.$sel2.'>'.getLL('tracking_export_dates_filter').'</option>';
					}
					//Add "all" selection if date mode is not continuos
					if($tracking['date_weekdays'] == '' || $_SESSION['show'] == 'list_trackings') {
						$html .= '<option value="all">'.getLL('tracking_export_dates_all').'</option>';
					}
					//Export shown: Only while entering tracking
					if($_SESSION['show'] != 'list_trackings') $html .= '<option value="current" '.$sel1.'>'.getLL('tracking_export_dates_current').'</option>';
					$html .= '</select>';
					$submenu[$menucounter]['html'][$itemcounter++] = $html.'<br />';
				}

				//Select address columns to be used for export
				$userpref = ko_get_userpref($_SESSION['ses_userid'], 'tracking_export_cols');
				$submenu[$menucounter]['output'][$itemcounter] = getLL('tracking_export_columns');
				$submenu[$menucounter]['link'][$itemcounter] = '';
				$html  = '<select name="sel_cols" size="0">';
				$html .= '<option value="name" '.($userpref == 'name' ? 'selected="selected"' : '').'>'.getLL('tracking_export_columns_name').'</option>';
				$itemset = array_merge((array)ko_get_userpref('-1', '', 'leute_itemset', 'ORDER BY `key` ASC'), (array)ko_get_userpref($_SESSION['ses_userid'], '', 'leute_itemset', 'ORDER BY `key` ASC'));
			  foreach($itemset as $i) {
					$value = $i['user_id'] == '-1' ? '@G@'.$i['key'] : $i['key'];
					$desc = $i['user_id'] == '-1' ? getLL('itemlist_global_short').' '.$i['key'] : $i['key'];
					$sel = $userpref == 'set_'.$value ? 'selected="selected"' : '';
					$html .= '<option value="set_'.$value.'" '.$sel.'>&quot;'.$desc.'&quot;</option>';
				}
				$html .= '</select>';
				$submenu[$menucounter]['html'][$itemcounter++] = $html.'<br />';

				//Layout select
				$userpref = ko_get_userpref($_SESSION['ses_userid'], 'tracking_export_layout');
				$submenu[$menucounter]['output'][$itemcounter] = getLL('tracking_export_layout');
				$submenu[$menucounter]['link'][$itemcounter] = '';
				$html  = '<select name="sel_layout" size="0">';
				$html .= '<option value="L" '.($userpref == 'L' ? 'selected="selected"' : '').'>'.getLL('tracking_export_layout_L').'</option>';
				$html .= '<option value="P" '.($userpref == 'P' ? 'selected="selected"' : '').'>'.getLL('tracking_export_layout_P').'</option>';
				$html .= '</select>';
				$submenu[$menucounter]['html'][$itemcounter++] = $html.'<br />';

				//Add empty rows
				$userpref = ko_get_userpref($_SESSION['ses_userid'], 'tracking_export_addrows');
				$submenu[$menucounter]['output'][$itemcounter] = getLL('tracking_export_addrows');
				$submenu[$menucounter]['link'][$itemcounter] = '';
				$html  = '<select name="sel_addrows" size="0">';
				$html .= '<option value="0" '.($userpref == 0 ? 'selected="selected"' : '').'>0</option>';
				$html .= '<option value="-1" '.($userpref == -1 ? 'selected="selected"' : '').'>'.getLL('tracking_export_addrows_autofill').'</option>';
				for($i=1; $i<=20; $i++) {
					$html .= '<option value="'.$i.'" '.($userpref == $i ? 'selected="selected"' : '').'>'.$i.'</option>';
				}
				$html .= '</select>';
				$submenu[$menucounter]['html'][$itemcounter++] = $html.'<br />';

				if (!$LEUTE_NO_FAMILY) {
					//Combine families checkbox
					$userpref = ko_get_userpref($_SESSION['ses_userid'], 'tracking_export_family');
					$submenu[$menucounter]['output'][$itemcounter] = ' ';
					$submenu[$menucounter]['link'][$itemcounter] = '';
					$submenu[$menucounter]["no_ul"][$itemcounter] = TRUE;
					$html  = '<input type="checkbox" id="chk_family" name="chk_family" value="1" '.($userpref == 1 ? 'checked="checked"' : '').' /><label for="chk_family">'.getLL('tracking_export_family').'</label>';
					$submenu[$menucounter]['html'][$itemcounter++] = $html.'<br />';
				}

				//Add sums column checkbox
				$userpref = ko_get_userpref($_SESSION['ses_userid'], 'tracking_export_sums');
				$submenu[$menucounter]['output'][$itemcounter] = ' ';
				$submenu[$menucounter]['link'][$itemcounter] = '';
				$submenu[$menucounter]["no_ul"][$itemcounter] = TRUE;
				$html  = '<input type="checkbox" id="chk_sums" name="chk_sums" value="1" '.($userpref == 1 ? 'checked="checked"' : '').' /><label for="chk_sums">'.getLL('tracking_export_sums').'</label>';
				$submenu[$menucounter]['html'][$itemcounter++] = $html.'<br /><br />';

				//Add export icons
				$submenu[$menucounter]['output'][$itemcounter] = ' ';
				$code  = '<input type="image" src="'.$ko_path.'images/icon_excel.png" onclick="set_ids_from_chk(this);set_action(\'export_tracking_xls\', this);" title="'.getLL("tracking_export_excel").'">&nbsp;&nbsp;';
				$code .= '<input type="image" src="'.$ko_path.'images/create_pdf.png" onclick="set_ids_from_chk(this);set_action(\'export_tracking_pdf\', this);" title="'.getLL("tracking_export_pdf").'">&nbsp;&nbsp;';
				//Show icons for mass export
				if($_SESSION['show'] == 'list_trackings') {
					$code .= '<input type="image" id="export_tracking_xls_zip" src="'.$ko_path.'images/icon_excel_zip.png" onclick="set_ids_from_chk(this);set_action(\'export_tracking_xls_zip\', this);" title="'.getLL("tracking_export_excel_zip").'">&nbsp;&nbsp;';
					$code .= '<input type="image" id="export_tracking_pdf_zip" src="'.$ko_path.'images/icon_pdf_zip.png" onclick="set_ids_from_chk(this);set_action(\'export_tracking_pdf_zip\', this);" title="'.getLL("tracking_export_pdf_zip").'">&nbsp;&nbsp;';
				}
				$submenu[$menucounter]['html'][$itemcounter++] = $code.'<br />';
			break;



			case 'itemlist_trackinggroups':
				$found = TRUE;

				//Get all groups
				$counter = 0;
				//Add a group for all trackings without a group
				$itemlist[$counter]['name'] = ko_html(getLL('tracking_itemlist_no_group'));
				$itemlist[$counter]['aktiv'] = in_array(0, $_SESSION['show_tracking_groups']) ? 1 : 0;
				$itemlist[$counter++]['value'] = 0;
				//Add all tracking groups
				$groups = db_select_data('ko_tracking_groups', '', '*', 'ORDER BY `name` ASC');
				foreach($groups as $gid => $group) {
					$itemlist[$counter]['name'] = ko_html($group['name']);
					$itemlist[$counter]['aktiv'] = in_array($gid, $_SESSION['show_tracking_groups']) ? 1 : 0;
					$itemlist[$counter++]['value'] = $gid;
				}//foreach(groups)

				$smarty->assign('tpl_itemlist_select', $itemlist);


				//Get all presets
				$akt_value = implode(',', $_SESSION['show_tracking_groups']);
				$itemset = array_merge((array)ko_get_userpref('-1', '', 'tracking_itemset', 'ORDER BY `key` ASC'), (array)ko_get_userpref($_SESSION['ses_userid'], '', 'tracking_itemset', 'ORDER BY `key` ASC'));
				foreach($itemset as $i) {
					$value = $i['user_id'] == '-1' ? '@G@'.$i['key'] : $i['key'];
			    $itemselect_values[] = $value;
			    $itemselect_output[] = $i['user_id'] == '-1' ? getLL('itemlist_global_short').' '.$i['key'] : $i['key'];
			    if($i['value'] == $akt_value) $itemselect_selected = $value;
				}
				$smarty->assign('tpl_itemlist_values', $itemselect_values);
				$smarty->assign('tpl_itemlist_output', $itemselect_output);
				$smarty->assign('tpl_itemlist_selected', $itemselect_selected);
				if($max_rights > 3) $smarty->assign('allow_global', TRUE);
																
				$submenu[$menucounter]['titel'] = getLL('submenu_tracking_title_itemlist_trackinggroups');
				if($state == 'closed') continue;

				$submenu[$menucounter]['output'][$itemcounter++] = '[itemlist]';
			break;



			default:
        if($menu) { 
          $submenu[$menucounter] = submenu($menu, $position, $state, 3, 'tracking');
          $found = (is_array($submenu[$menucounter])); 
        }
		}//switch(menu)

		//Plugins erlauben, Menuitems hinzuzufügen
		hook_submenu('tracking', $menu, $submenu, $menucounter, $itemcounter);

    if($found) {
			$submenu[$menucounter]['key'] = 'tracking_'.$menu;
			$submenu[$menucounter]['id'] = $menu;
			$submenu[$menucounter]['mod'] = 'tracking';
			$submenu[$menucounter]['sesid'] = session_id();
      $submenu[$menucounter]['position'] = $position;
      $submenu[$menucounter]['state'] = $state;

			if($display == 1) {
				$smarty->assign('sm', $submenu[$menucounter]);
				$smarty->assign('ko_path', $ko_path);
				$smarty->assign('help', ko_get_help('tracking', 'submenu_'.$menu));
				$smarty->display('ko_submenu.tpl');
			} else if($display == 2) {
				$smarty->assign('sm', $submenu[$menucounter]);
				$smarty->assign('ko_path', $ko_path);
				$return = 'sm_tracking_'.$menu.'@@@';
				$return .= $smarty->fetch('ko_submenu.tpl');
			}

			$smarty->clear_assign("help");
			$menucounter++;
    }


	}//foreach(namen as menu)

	if($display == 2) {
		return $return;
	} else if($display == 3) {
		return $submenu;
	}
}//submenu_tracking()




function submenu_projects($namen, $position, $state, $display=1) {
	global $ko_path, $smarty;
	global $project_stati;
	global $my_submenu;

	$return = "";

	if(!ko_module_installed("projects")) return FALSE;

	ko_get_access_all('projects');

	$namen = explode(",", $namen);

	$menucounter = 0;
  foreach($namen as $menu) {
		$found = FALSE;

    $itemcounter = 0;
    switch($menu) {
			
			case "projects":
				$found = TRUE;
				$submenu[$menucounter]["titel"] = getLL("submenu_projects_title_projects");
				$submenu[$menucounter]["output"][$itemcounter] = ko_menuitem("projects", "new_project");
				$submenu[$menucounter]["link"][$itemcounter++] = $ko_path."projects/index.php?action=new_project";
				$submenu[$menucounter]["output"][$itemcounter] = ko_menuitem("projects", "show_projects");
				$submenu[$menucounter]["link"][$itemcounter++] = $ko_path."projects/index.php?action=show_projects";
				$submenu[$menucounter]["output"][$itemcounter] = ko_menuitem("projects", "show_todo");
				$submenu[$menucounter]["link"][$itemcounter++] = $ko_path."projects/index.php?action=show_todo";
				$submenu[$menucounter]["output"][$itemcounter] = ko_menuitem("projects", "show_settings");
				$submenu[$menucounter]["link"][$itemcounter++] = $ko_path."projects/index.php?action=show_settings";
			break;

			case "filter":
				$found = TRUE;

				$submenu[$menucounter]["titel"] = getLL("submenu_projects_title_filter");

				//State
				$code = "";
				foreach($project_stati as $i => $status) {
					$code .= '<div style="white-space:nowrap;">';
					$chk = in_array($status, $_SESSION["projects_filter_state"]) ? 'checked="checked"' : '';
					$code .= '<input type="checkbox" id="chk_projects_filter_'.$i.'" name="chk_projects_filter_'.$i.'" onclick="sendReq(\'../projects/inc/ajax.php\', \'action,filters,sesid\', \'setfilter,\'+projects_get_filters()+\','.session_id().'\', do_element);" '.$chk.' /><label for="chk_projects_filter_'.$i.'">'.getLL("projects_status_".$status)." (".getLL("projects_status_short_".$status).")</label>";
					$code .= "</div>";
				}

				$submenu[$menucounter]["output"][$itemcounter] = getLL("projects_listheader_status");
				$submenu[$menucounter]["html"][$itemcounter++] = $code;
			break;


			case "hosting":
				$found = TRUE;
				$submenu[$menucounter]["titel"] = getLL("submenu_projects_title_hosting");
				$submenu[$menucounter]["output"][$itemcounter] = ko_menuitem("projects", "new_hosting");
				$submenu[$menucounter]["link"][$itemcounter++] = $ko_path."projects/index.php?action=new_hosting";
				$submenu[$menucounter]["output"][$itemcounter] = ko_menuitem("projects", "show_hostings");
				$submenu[$menucounter]["link"][$itemcounter++] = $ko_path."projects/index.php?action=show_hostings";

				$submenu[$menucounter]["output"][$itemcounter] = ko_menuitem("projects", "new_typo3");
        $submenu[$menucounter]["link"][$itemcounter++] = $ko_path."projects/index.php?action=new_typo3";
        $submenu[$menucounter]["output"][$itemcounter] = ko_menuitem("projects", "show_typo3");
        $submenu[$menucounter]["link"][$itemcounter++] = $ko_path."projects/index.php?action=show_typo3";

				$submenu[$menucounter]['output'][$itemcounter] = ko_menuitem('projects', 'new_ssl');
        $submenu[$menucounter]['link'][$itemcounter++] = $ko_path.'projects/index.php?action=new_ssl';
        $submenu[$menucounter]['output'][$itemcounter] = ko_menuitem('projects', 'show_ssl');
        $submenu[$menucounter]['link'][$itemcounter++] = $ko_path.'projects/index.php?action=show_ssl';
			break;


			case 'stats':
				$found = TRUE;
				$submenu[$menucounter]['titel'] = getLL('submenu_projects_title_stats');

				//Open amount from projects
				$amount_projects = 0.0;
				$details = array();
				$projects = db_select_data('ko_projects', 'WHERE `status` IN (\'r\', \'p\', \'e\')', '*');
				foreach($projects as $pid => $project) {
					$open = $date = array();
					$logs = db_select_data('ko_projects_logs', 'WHERE `project_id` = \''.$pid.'\' AND `type` IN (\'billed\', \'payment\')', '*', 'ORDER BY `time` ASC');
					foreach($logs as $log) {
						if($log['type'] == 'billed') {
							$split = explode(' ', $log['comment']);
							$open[] = $split[0];
							$date[] = add2date($log['time'], 'tag', 30, TRUE);
						} else {
							$split = explode(' ', $log['comment']);
							foreach($open as $oid => $o) {
								if($o == $split[0]) {
									unset($open[$oid]);
									unset($date[$oid]);
								}
							}
						}
					}
					foreach($open as $oid => $amount) {
						if(!$amount) continue;
						$amount_projects += $amount;
						(float)$details[substr($date[$oid], 0, 10)] += (float)$amount;
					}
				}

				ksort($details);
				$tooltip = '';
				foreach($details as $date => $amount) {
					$tooltip .= $date.': <b>'.$amount.'</b><br />';
				}

				$submenu[$menucounter]['output'][$itemcounter] = getLL('projects_stats_amount_projects');
				$submenu[$menucounter]['html'][$itemcounter++] = '<span onmouseover="tooltip.show(\''.$tooltip.'\');" onmouseout="tooltip.hide();">'.$amount_projects.'</span><br />';


				//Open amount from hostings
				$details = array();
				$hostings = db_select_data('ko_projects_hostings', 'WHERE `billed` > `payed`', '*', 'ORDER BY `billed` ASC');
				foreach($hostings as $hosting) {
					$details[$hosting['billed']] += $hosting['price'];
				}
				$tooltip = '';
				$amount_hostings = 0.0;
				foreach($details as $date => $amount) {
					$tooltip .= add2date($date, 'tag', 30, TRUE).': <b>'.$amount.'</b><br />';
					$amount_hostings += $amount;
				}

				$submenu[$menucounter]['output'][$itemcounter] = getLL('projects_stats_amount_hostings');
				$submenu[$menucounter]['html'][$itemcounter++] = '<span onmouseover="tooltip.show(\''.$tooltip.'\');" onmouseout="tooltip.hide();">'.$amount_hostings.'</span><br />';
			break;


			default:
        if($menu) { 
          $submenu[$menucounter] = submenu($menu, $position, $state, 3, 'projects');
          $found = (is_array($submenu[$menucounter])); 
        }
		}//switch(menu)

		//Plugins erlauben, Menuitems hinzuzufügen
		hook_submenu("projects", $menu, $submenu, $menucounter, $itemcounter);

    if($found) {
			$submenu[$menucounter]["key"] = "projects_".$menu;
			$submenu[$menucounter]["id"] = $menu;
			$submenu[$menucounter]["mod"] = "projects";
			$submenu[$menucounter]["sesid"] = session_id();
      $submenu[$menucounter]["position"] = $position;
      $submenu[$menucounter]["state"] = $state;

			if($display == 1) {
				$smarty->assign("sm", $submenu[$menucounter]);
				$smarty->assign("ko_path", $ko_path);
				$smarty->assign("help", ko_get_help("projects", "submenu_".$menu));
				$smarty->display("ko_submenu.tpl");
			} else if($display == 2) {
				$smarty->assign("sm", $submenu[$menucounter]);
				$smarty->assign("ko_path", $ko_path);
				$return = "sm_projects_".$menu."@@@";
				$return .= $smarty->fetch("ko_submenu.tpl");
			}

			$smarty->clear_assign("help");
			$menucounter++;
    }


	}//foreach(namen as menu)

	if($display == 2) {
		return $return;
	} else if($display == 3) {
		return $submenu;
	}
}//submenu_projects()




$DISABLE_SM = array("admin" => array("set_allgemein" => array("filter"),
																		 "set_etiketten" => array("filter"),
																		 "set_etiketten_open" => array("filter"),
																		 "set_leute_pdf" => array("filter"),
																		 "new_leute_pdf" => array("filter"),
																		 "edit_leute_pdf" => array("filter"),
																		 "set_layout" => array("filter"),
																		 "change_password" => array("filter"),
							 											 "set_layout_guest" => array("filter"),
														 				 "show_logins" => array("filter"),
							 											 "edit_login" => array("filter"),
							 											 "new_login" => array("filter"),
														 				 "show_logs" => array(),
																		 'new_news' => array('filter'),
																		 'edit_news' => array('filter'),
																		 'list_news' => array('filter'),
														 				 'show_sms_log' => array(),
																		 'show_admingroups' => array('filter'),
																		 'new_admingroup' => array('filter'),
																		 ),
										"daten" => array("all_events" => array(),
																		 "all_groups" => array("filter", "itemlist_termingruppen", "export"),
																		 "neuer_termin" => array("filter", "itemlist_termingruppen", "export"),
																		 "edit_termin" => array("filter", "itemlist_termingruppen", "export"),
																		 "neue_gruppe" => array("filter", "itemlist_termingruppen", "export"),
																		 "edit_gruppe" => array("filter", "itemlist_termingruppen", "export"),
																		 'new_ical' => array('filter', 'itemlist_termingruppen', 'export'),
																		 "calendar" => array("filter"),
																		 "cal_monat" => array("filter"),
																		 "cal_woche" => array("filter"),
																		 "cal_jahr" => array("filter"),
																		 "multiedit" => array("filter", "itemlist_termingruppen"),
																		 "multiedit_tg" => array("filter", "itemlist_termingruppen"),
																		 "daten_settings" => array("filter", "itemlist_termingruppen"),
																		 "list_events_mod" => array("filter", "itemlist_termingruppen"),
																		 'ical_links' => array('filter'),
																		 'new_reminder' => array('filter', 'export', 'itemlist_termingruppen'),
																		 'edit_reminder' => array('filter', 'export', 'itemlist_termingruppen'),
																		 'list_reminders' => array('filter', 'export', 'itemlist_termingruppen'),
																		 ),
										'rota' => array('list_teams' => array('itemlist_teams', 'itemlist_eventgroups'),
																		'edit_team' => array('itemlist_teams', 'itemlist_eventgroups'),
																		'new_team' => array('itemlist_teams', 'itemlist_eventgroups'),
																		'settings' => array('itemlist_teams', 'itemlist_eventgroups'),
																		'show_filesend' => array('itemlist_teams', 'itemlist_eventgroups'),
																	),
										"fileshare" => array(),
										'groups' => array('list_groups' => array('dffilter'),
																			'new_group' => array('dffilter', 'export'),
																			'edit_group' => array('dffilter', 'export'),
																			'list_roles' => array('dffilter', 'export'),
																			'new_role' => array('dffilter', 'export'),
																			'edit_role' => array('dffilter', 'export'),
																			'delete_role' => array('dffilter', 'export'),
																			'edit_datafield' => array('dffilter', 'export'),
																			'multiedit' => array('dffilter', 'export'),
																			'multiedit_roles' => array('dffilter', 'export'),
																			'list_rights' => array('dffilter', 'export'),
																			'edit_login_rights' => array('dffilter', 'export'),
																			'groups_settings' => array('dffilter', 'export'),
																			),
										"leute" => array("show_all" => array("itemlist_spalten_kg", "itemlist_chart"),
																		 "show_my_list" => array("itemlist_spalten_kg", "itemlist_chart"),
																		 "show_adressliste" => array("itemlist_spalten", "aktionen", "itemlist_spalten_kg", "itemlist_chart"),
																		 "geburtstagsliste" => array("filter", "itemlist_spalten_kg", "itemlist_chart"),
																		 "single_view" => array("filter", "itemlist_spalten", "aktionen", "itemlist_spalten_kg", "itemlist_chart"),
																		 "neue_person" => array("filter", "itemlist_spalten", "aktionen", "itemlist_spalten_kg", "meine_liste", "itemlist_chart"),
																		 "edit_person" => array("filter", "itemlist_spalten", "aktionen", "itemlist_spalten_kg", "meine_liste", "itemlist_chart"),
																		 "email_versand" => array("filter", "itemlist_spalten", "aktionen", "itemlist_spalten_kg", "meine_liste", "itemlist_chart"),
																		 "edit_kg" => array("filter", "itemlist_spalten", "aktionen", "itemlist_spalten_kg", "meine_liste", "itemlist_chart"),
																		 "neue_kg" => array("filter", "itemlist_spalten", "aktionen", "itemlist_spalten_kg", "meine_liste", "itemlist_chart"),
																		 "etiketten_optionen" => array("filter", "itemlist_spalten", "aktionen", "itemlist_spalten_kg", "meine_liste", "itemlist_chart"),
																		 "xls_settings" => array("filter", "itemlist_spalten", "aktionen", "itemlist_spalten_kg", "meine_liste", "itemlist_chart"),
																		 "sms_versand" => array("filter", "itemlist_spalten", "aktionen", "itemlist_spalten_kg", "meine_liste", "itemlist_chart"),
																		 "list_kg" => array("filter", "meine_liste", "itemlist_spalten", "aktionen", "itemlist_chart"),
																		 "chart_kg" => array("filter", "meine_liste", "itemlist_spalten", "aktionen", "itemlist_chart", "itemlist_spalten_kg"),
																		 "mutationsliste" => array("filter", "itemlist_spalten", "itemlist_spalten_kg", "itemlist_chart"),
																		 "groupsubscriptions" => array("filter", "itemlist_spalten", "aktionen", "itemlist_spalten_kg", "itemlist_chart"),
																		 "multiedit" => array("filter", "itemlist_spalten", "aktionen", "itemlist_spalten_kg", "meine_liste", "itemlist_chart"),
																		 "import" => array("filter", "itemlist_spalten", "aktionen", "itemlist_spalten_kg", "meine_liste", "itemlist_chart"),
																		 "export_pdf" => array("filter", "itemlist_spalten", "aktionen", "itemlist_spalten_kg", "meine_liste", "itemlist_chart"),
																		 "chart" => array("itemlist_spalten", "aktionen", "itemlist_spalten_kg", "meine_liste"),
																		 "mailmerge" => array("filter", "itemlist_spalten", "aktionen", "itemlist_spalten_kg", "meine_liste", "itemlist_chart"),
																		 'settings' => array('filter', 'itemlist_spalten', 'aktionen', 'itemlist_spalten_kg', 'meine_liste', 'itemlist_chart'),
																		 ),
										"reservation" => array("neue_reservation" => array("filter", "itemlist_objekte", "export"),
																					 "edit_reservation" => array("filter", "itemlist_objekte", "export"),
																					 "liste" => array("objektbeschreibungen"),
																					 "calendar" => array("objektbeschreibungen", "filter"),
																					 "cal_jahr" => array("objektbeschreibungen", "filter"),
																					 "show_mod_res" => array("objektbeschreibungen", "filter", "itemlist_objekte", "export"),
																					 "list_items" => array("objektbeschreibungen", "filter", "itemlist_objekte", "export"),
																					 "new_item" => array("objektbeschreibungen", "filter", "itemlist_objekte", "export"),
																					 "edit_item" => array("objektbeschreibungen", "filter", "itemlist_objekte", "export"),
																					 "email_confirm" => array("objektbeschreibungen", "filter", "itemlist_objekte", "export"),
																					 "multiedit" => array("objektbeschreibungen", "filter", "itemlist_objekte", "export"),
																					 "multiedit_group" => array("objektbeschreibungen", "filter", "itemlist_objekte", "export"),
																					 "res_settings" => array("objektbeschreibungen", "filter", "itemlist_objekte"),
																		 			 'ical_links' => array('objektbeschreibungen', 'filter'),
																					 ),
											"tapes" => array("list_tapegroups" => array("filter", "print"),
																			 "list_series" => array("filter", "print"),
																			 "new_tape" => array("filter", "print"),
																			 "new_serie" => array("filter", "print"),
																			 "new_tapegroup" => array("filter", "print"),
																			 "edit_tape" => array("filter", "print"),
																			 "edit_tapegroup" => array("filter", "print"),
																			 "edit_serie" => array("filter", "print"),
																			 "settings" => array("filter", "print"),
																			 "edit_printlayout" => array("filter", "print"),
																			 "new_printlayout" => array("filter", "print"),
																			 "list_printlayouts" => array("filter", "print"),
																			 ),
											"tools" => array(),
											"donations" => array("list_accounts" => array("itemlist_accounts", "filter"),
																					 'new_account' => array('itemlist_accounts', 'filter', 'export'),
																					 'edit_account' => array('itemlist_accounts', 'filter', 'export'),
																					 'new_donation' => array('itemlist_accounts', 'filter', 'export'),
																					 'edit_donation' => array('itemlist_accounts', 'filter', 'export'),
																					 'list_reoccuring_donations' => array('itemlist_accounts', 'filter', 'export'),
																					 "show_stats" => array('export'),
																					 'donation_settings' => array('itemlist_accounts', 'filter', 'export'),
																					 ),
											'tracking' => array('list_trackings' => array(),
																					'new_tracking' => array('export', 'filter', 'itemlist_trackinggroups'),
																					'edit_tracking' => array('export', 'filter', 'itemlist_trackinggroups'),
																					'enter_tracking' => array('itemlist_trackinggroups'),
																					'tracking_settings' => array('export', 'filter', 'itemlist_trackinggroups'),
																					'mod_entries' => array('export', 'filter'),
																					),
											'projects' => array('show_todo' => array('filter'),
                                          ),
											);

?>
