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

function ko_get_submenus($type) {
	//$access has to stand here so it is available in the plugins which are included below with hook_include_sm()
	global $ko_path, $access;
	global $GSM_SUBMENUS, $my_submenu, $DISABLE_SM;

	$sm['daten']                 = array('termine', 'termingruppen', 'export', 'reminder', 'filter', 'itemlist_termingruppen', 'list_rooms');
	$sm['daten_dropdown']        = array('termine','termingruppen', 'reminder');

	$sm['leute']                 = array('leute', 'meine_liste', 'aktionen', 'kg', 'filter', 'itemlist_spalten');
	$sm['leute_dropdown']        = array('leute,kg');

	$sm['reservation']           = array('reservationen','objekte','export', 'filter','objektbeschreibungen','itemlist_objekte');
	$sm['reservation_dropdown']  = array('reservationen', 'objekte');

	$sm['rota']                  = array('rota', 'itemlist_teams', 'itemlist_eventgroups');
	$sm['rota_dropdown']         = array('rota');

	$sm['admin']                 = array('logins', 'news', 'logs', 'presets', 'vesr', 'cloud_printing', 'filter', 'pubkey');
	$sm['admin_dropdown']        = array('logins', 'logs', 'presets', 'vesr', 'news');

	$sm['tools']                 = array('submenus', 'leute-db', 'ldap', 'locallang', 'plugins', 'scheduler', 'typo3');
	$sm['tools_dropdown']        = array('submenus', 'leute-db', 'ldap', 'locallang', 'plugins', 'scheduler', 'typo3');

	$sm['groups']                = array('groups', 'roles', 'export', 'dffilter');
	$sm['groups_dropdown']       = array('groups', 'roles');

	$sm['taxonomy']              = array('taxonomy');
	$sm['taxonomy_dropdown']     = array('taxonomy');

	$sm['donations']             = array('donations', 'accounts', 'export', 'filter', 'itemlist_accounts');
	$sm['donations_dropdown']    = array('donations', 'accounts');

	$sm['tracking']              = array('trackings', 'filter', 'export', 'itemlist_trackinggroups');
	$sm['tracking_dropdown']     = array('trackings');

	$sm['projects']              = array('projects', 'filter', 'hosting', 'stats');
	$sm['projects_dropdown']     = array('projects', 'hosting');

	$sm['crm']                   = array('projects', 'status', 'contacts', 'filter', 'itemlist_projects');
	$sm['crm_dropdown']          = array('projects', 'status', 'contacts');

	$sm['subscription']          = array('forms', 'form_groups', 'double_opt_in');
	$sm['subscription_dropdown'] = array('forms', 'form_groups', 'double_opt_in');


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
		if(substr($type, -9) == "_dropdown") {
			return $sm[$type];
		} else {
			return array_merge($sm[$type], $gsm);
		}
	} else {
		return array();
	}
}//ko_get_submenus()



/**
 * @param string $module the module for which the subMenus should be returned
 * @param string $action the current action (disabled subMenus depend on action)
 * @return array an array with the names of the subMenus
 */
function ko_get_active_submenus($module, $action) {
	global $DISABLE_SM;

	$disabledSM = $DISABLE_SM[$module][$action];
	$subMenus = ko_get_submenus($module);
	foreach ($subMenus as $k => $subMenu) {
		if (in_array($subMenu, $disabledSM)) {
			unset ($subMenus[$k]);
		}
	}
	return $subMenus;
}



function ko_set_submenues($uid="") {
	global $ko_menu_akt, $GSM_SUBMENUS;

	if(!$uid) $uid = $_SESSION["ses_userid"];
	$module = $ko_menu_akt;

	//Get current submenues for this user
	$smString = trim(ko_get_userpref($uid, "submenu_".$module));
	if ($smString == '') $sm = array();
	else $sm = unserialize($smString);
	if (!$sm) $sm = array();

	//Get all available submenues for this module
	if($uid == ko_get_guest_id()) $GSM_SUBMENUS = array();  //Don't use GSM for ko_guest
	$asm = ko_get_submenus($module);

	//Compare them and add any missing
	$diff = array_diff($asm, array_keys($sm));
	if(sizeof($diff) > 0) {
		foreach($diff as $smMissing) {
			$sm[$smMissing] = array('state' => 'open');
		}//foreach(diff as sm)
	}//if(sizeof(diff))


	//Clean up submenus and store them
	foreach($sm as $key => $value) if(!$value || !in_array($key, $asm)) unset($sm[$key]);
	ko_save_userpref($uid, "submenu_".$module, serialize($sm));

	//Store in session
	$_SESSION["submenu"] = $sm;
}//ko_set_submenues()




function ko_check_submenu($sm, $module) {
	global $MODULES;

	if(!in_array($module, $MODULES)) return FALSE;
	if(!in_array($sm, ko_get_submenus($module))) return FALSE;
	return TRUE;
}//ko_check_submenu()


function ko_get_outer_submenu_code($module) {
	$state = ko_get_userpref($_SESSION["ses_userid"], 'sidebar_state');
	if ($state != 'closed') $state = 'open';
	print '<div id="main-table-layout">
	<div' . ($state == 'open' ? ' class="in shown"' : '') . ' id="sidebar-container">
		<nav data-container="#sidebar-container" id="sidebar">
			<div class="panel-group sortable" id="accordion" role="tablist" aria-multiselectable="true">';

	ko_get_submenu_code($module);

	print '</div>
		</nav>
	</div>';
}



function ko_clean_submenu($module, &$sm, &$found) {
	$secMenuLinks = ko_array_column(ko_get_secmenu_links($module), 'action');
	foreach ($sm as $k1 => $smEntry) {
		foreach ($smEntry as $k2 => $link) {
			if ($link['type'] == 'link' && in_array($link['action'], $secMenuLinks)) {
				unset($sm[$k1][$k2]);
			}
		}
		$loopCounter = 0;
		$lastWasSep = false;
		foreach ($sm[$k1] as $k2 => $link) {
			if ($link['type'] == 'seperator') {
				if ($lastWasSep) {
					unset($sm[$k1][$k2]);
				}
				if ($loopCounter == 0 || $loopCounter == sizeof($sm[$k1]) - 1) {

					unset($sm[$k1][$k2]);
				}
				$lastWasSep = true;
			}
			else {
				$lastWasSep = false;
			}
			$loopCounter ++;
		}
	}
	if (sizeof($sm['items']) == 0) $found = false;
}



function ko_get_submenu_code($module) {
	global $DISABLE_SM, $BASE_PATH;

	$return = "";
	foreach($_SESSION["submenu"] as $smName => $smData) {
		if(in_array($smName, $DISABLE_SM[$module][$_SESSION["show"]]) || $smName == "") {
			unset($_SESSION["submenu"][$smName]);
		}
	}
	if(sizeof($_SESSION["submenu"]) > 0) {
		foreach ($_SESSION["submenu"] as $smName => $smData) {
			if (function_exists("submenu_".$module)) {
				$r = call_user_func_array("submenu_".$module, array($smName, $smData['state']));
				$return .= $r;
			}
		}
	}
	return $return;
}//ko_get_submenu_code()


/**
 * @param        $module
 * @param string $subMenus
 * @return array            return a structured array containing information about all subMenu entries of type link
 */
function ko_get_submenu_links($module, $subMenus = '', $doClean = false) {
	$result = array();
	if (!is_array($subMenus)) $subMenus = ko_get_submenus($module);
	$key = $module . ',' . implode(',', $subMenus) . ($doClean ? 'doClean' : 'doNotClean');
	if (array_key_exists($key, $GLOBALS)) return $GLOBALS[$key];
	if (!function_exists("submenu_".$module)) return $result;
	$r = call_user_func_array("submenu_".$module, array($subMenus, 'open', 3, $doClean));
	if (is_array($r)) {
		foreach ($r as $submenuEntries) {
			$result[$submenuEntries['id']] = array('title' => $submenuEntries['titel'], 'links' => array());
			foreach ($submenuEntries['items'] as $smEntry) {
				if ($smEntry['type'] == 'link') {
					$result[$submenuEntries['id']]['links'][] = $smEntry;
				}
			}
		}
	}
	$GLOBALS[$key] = $result;
	return $result;
} // ko_get_submenu_links()




/**
 * @param         $module
 * @param string  $subMenus
 * @return array             returns an array of arrays which contain the the action as well as other information
 *                           about a link entry in the secondary menu bar.
 */
function ko_get_secmenu_links($module, $action = '') {
	$secmenuLinksS = ko_get_userpref($_SESSION["ses_userid"], $module . '_menubar_links');
	$secmenuLinksS = explode(',', $secmenuLinksS);
	$secmenuLinks = array();
	$subMenus = '';
	if ($action != '') $subMenus = ko_get_active_submenus($module, $action);
	$subMenus = ko_get_submenu_links($module, $subMenus);
	foreach ($secmenuLinksS as $secmenuLinkS) {
		if (trim($secmenuLinkS) == '') continue;
		foreach ($subMenus as $k => $subMenu) {
			foreach ($subMenu['links'] as $subMenuLink) {
				if ($subMenuLink['action'] == $secmenuLinkS) {
					$x = $subMenuLink;
					$x['sm_id'] = $k;
					$secmenuLinks[] = $x;
				}
			}
		}
	}
	return $secmenuLinks;
}




function submenu($menu, $state, $display=3, $module="") {
	global $ko_path, $smarty, $PLUGINS;

	switch($menu) {
	case "gsm_notizen":
		//Guest darf keine Notizen machen
		if($_SESSION["ses_userid"] == ko_get_guest_id()) break;

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
				$submenu = call_user_func('my_submenu_'.$p['name'].'_'.$menu, $state);
			}
		}
	}//switch(menu)

	$submenu["key"] = $module."_".$menu;
	$submenu["id"] = $menu;
	$submenu["mod"] = $module;
	$submenu["sesid"] = session_id();
	$submenu["state"] = $state;

	if($display == 1) {
		//$smarty->assign("sm", $submenu[$menucounter]);
		//$smarty->assign("ko_path", $ko_path);
		//$smarty->display("ko_submenu.tpl");
	} else if($display == 2) {
		$smarty->assign("sm", $submenu);
		$smarty->assign('hideWrappingDiv', TRUE);
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
 * state ist geschlossen oder offen (gilt für alle aus namen)
 * display gibt an, wie der Code zurückgegeben werden soll:
 * 1: normale Ausgabe über smarty, 2: Rückgabe des HTML-Codes für Ajax, 3: Array für Dropdown-Menu
 */
function submenu_daten($namen, $state, $display=1, $doClean = true) {
	global $ko_path, $smarty, $ko_menu_akt;
	global $access;
	global $my_submenu;

	$return = "";

	if($ko_menu_akt == 'daten') {
		$all_rights = $access['daten']['ALL'];
		$max_rights = $access['daten']['MAX'];
		$absence_rights = $access['daten']['ABSENCE'];
	} else {
		$all_rights = ko_get_access_all('event_admin', '', $max_rights);
		$login = db_select_data('ko_admin', 'WHERE `id` = \''.$_SESSION['ses_userid'].'\'', 'id,event_absence_rights,admingroups', '', '', TRUE);
		$absence_rights = $login['event_absence_rights'];
		//Check admin groups
		if($login['admingroups'] != '') {
			$ags = db_select_data('ko_admingroups', "WHERE `id` IN (".$login['admingroups'].") AND `event_absence_rights` > 0");
			foreach($ags as $ag) {
				$absence_rights = max($absence_rights, $ag['event_absence_rights']);
			}
		}
	}



	if($max_rights < 1) return FALSE;

	if (!is_array($namen)) {
		$namen = explode(",", $namen);
	}

	$menucounter = 0;
	foreach($namen as $menu) {
		$found = FALSE;

		switch($menu) {

		case "termine":
			$found = TRUE;
			$submenu[$menucounter]["titel"] = getLL("submenu_daten_title_termine");

			if($max_rights > 1 && db_get_count('ko_eventgruppen', 'id', "AND `type` = '0'") > 0) {
				$submenu[$menucounter]["items"][] = ko_get_menuitem("daten", "neuer_termin");
			}
			if($max_rights > 0) {
				$submenu[$menucounter]["items"][] = ko_get_menuitem("daten", "all_events");
				$submenu[$menucounter]["items"][] = ko_get_menuitem("daten", "calendar");
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
				$submenu[$menucounter]["items"][] = ko_get_menuitem_link('daten', 'list_events_mod', $num_mod, '', ($num_mod <= 0));
			}

			if($max_rights > 0 && ($_SESSION['ses_userid'] != ko_get_guest_id() || ko_get_setting('daten_show_ical_links_to_guest'))) {
				$submenu[$menucounter]['items'][] = ko_get_menuitem('daten', 'ical_links');
			}

			if($max_rights > 1 && db_get_count('ko_eventgruppen', 'id', "AND `type` = '0'") > 0) {
				$submenu[$menucounter]['items'][] = ko_get_menuitem('daten', 'import');
			}

			$submenu[$menucounter]['items'][] = ko_get_menuitem('daten', 'list_rooms');

			if($absence_rights >= 1) {
				$submenu[$menucounter]['items'][] = ko_get_menuitem('daten', 'list_absence');
			}
		break;



		case "termingruppen":
			if($max_rights > 2) {
				$found = TRUE;
				$submenu[$menucounter]["titel"] = getLL("submenu_daten_title_termingruppen");

				if($all_rights > 2) {
					$submenu[$menucounter]['items'][] = ko_get_menuitem('daten', 'neue_gruppe');
					$submenu[$menucounter]['items'][] = ko_get_menuitem('daten', 'new_ical');
					$submenu[$menucounter]['items'][] = ko_get_menuitem_seperator();
				}
				$submenu[$menucounter]["items"][] = ko_get_menuitem("daten", "all_groups");
			}
		break;



		case "export":
			//Only show for logged in users
			if($max_rights > 0 && $_SESSION["ses_userid"] != ko_get_guest_id()) {
				$found = TRUE;

				$submenu[$menucounter]["titel"] = getLL("submenu_daten_title_export");
				//PDF exports for daily, weekly, monthly and yearly calendar
				if($max_rights > 0) {
					$c = '<select name="sel_pdf_export" class="input-sm form-control" onchange="jumpToUrl(\'?action=export_pdf&mode=\'+this.options[this.selectedIndex].value);">';
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
					$c .= '<option value="s-minus1:1">1/'.(date('Y')-1).'</option>';
					$c .= '<option value="s-minus1:7">2/'.(date('Y')-1).'</option>';
					$c .= '<option value="s-0:1">1/'.date('Y').'</option>';
					$c .= '<option value="s-0:7">2/'.date('Y').'</option>';
					$c .= '<option value="s-1:1">1/'.(date('Y')+1).'</option>';
					$c .= '<option value="s-1:7">2/'.(date('Y')+1).'</option>';
					$c .= '</optgroup>';
					$c .= '<optgroup label="'.getLL('year').'">';
					$c .= '<option value="y-minus1">'.getLL('time_last').'</option>';
					$c .= '<option value="y-0">'.getLL('time_current').'</option>';
					$c .= '<option value="y-1">'.getLL('time_next').'</option>';
					$c .= '<option value="y-2">'.getLL('time_after_next').'</option>';
					$c .= '</optgroup>';
					//TODO: Allow plugins to add new export options
					$c .= '</select>';
					$submenu[$menucounter]["items"][] = ko_get_menuitem_html($c);
				}

				//Excel-Export
				if($_SESSION['show'] == 'all_events' && $max_rights > 0) {
					$includeProgram = ko_get_setting('activate_event_program') ? TRUE : FALSE;
					$c = '
						<form action="index.php" method="POST">
						<input type="hidden" name="action" value="">
						<input type="hidden" name="ids" value="">
																											
						<select name="sel_xls_cols" class="input-sm form-control" onchange="set_ids_from_chk(this);jumpToUrl(\'?action=export_xls_daten&sel_xls_cols=\'+this.options[this.selectedIndex].value+\'&chk=\'+document.getElementsByName(\'ids\')[0].value);">';
					$c .= '<option value="">'.getLL('submenu_daten_xls_export').'</option>';

					$c .= '<optgroup label="'.getLL('columns').'">';
					$c .= '<option value="n_session">'.getLL('shown').'</option>';
					if ($includeProgram) {
						$c .= '<option value="p_session">'.getLL('shown').' ('.getLL('submenu_daten_label_export_include_program').')</option>';
					}
					$itemset = array_merge((array)ko_get_userpref('-1', '', 'ko_event_colitemset', 'ORDER by `key` ASC'), (array)ko_get_userpref($_SESSION['ses_userid'], '', 'ko_event_colitemset', 'ORDER by `key` ASC'));
					foreach($itemset as $i) {
						$value = $i['user_id'] == '-1' ? '@G@'.$i['key'] : $i['key'];
						$output = $i['user_id'] == '-1' ? getLL('itemlist_global_short').' '.$i['key'] : $i['key'];
						$c .= '<option value="n'.$value.'">"'.$output.'"</option>';
						if ($includeProgram) {
							$c .= '<option value="p'.$value.'">"'.$output.' ('.getLL('submenu_daten_label_export_include_program').')</option>';
						}
					}
					$c .= "</optgroup>";
					$c .= '</select></form>';
					$submenu[$menucounter]["items"][] = ko_get_menuitem_html($c);
				}

			}
		break;

		case 'reminder':
			if ($access['daten']['REMINDER'] < 1) break;

			$found = TRUE;
			$submenu[$menucounter]["titel"] = getLL("submenu_daten_title_reminder");

			$submenu[$menucounter]["items"][] = ko_get_menuitem("daten", "new_reminder");
			$submenu[$menucounter]["items"][] = ko_get_menuitem("daten", "list_reminders");

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

			$akt_von = ko_daten_parse_time_filter($_SESSION["filter_start"], 'today', FALSE);
			if ($akt_von == 'today') $akt_von = date("d.m.Y", time());
			else $akt_von = sql2datum($akt_von);
			$akt_bis = ko_daten_parse_time_filter($_SESSION["filter_ende"], '', FALSE);
			if ($akt_bis == 'today') $akt_von = date("d.m.Y", time());
			else $akt_bis = sql2datum($akt_bis);

			$code =
"<script>
	$('body').on('keydown', '#daten_filter_start-input, #daten_filter_end-input', function(e) {
		if (e.which == 13) document.getElementById('submit_filter').click();
		else if (e.which == 84) {
			$(this).val($(this).val() + 't');
			e.preventDefault();
			return false;
		}
	});
</script>";

			$datePickerInput = array('type' => 'datepicker', 'value' => $akt_von, 'name' => 'daten_filter[date1]', 'html_id' => 'daten_filter_start', 'add_class' => $akt_von ? 'jsdate-active' : '');
			$datePickerInput['sibling'] = "daten_filter_end";
			$smarty->assign('input', $datePickerInput);
			$code .= $smarty->fetch('ko_formular_elements.tmpl');
			$code .= '<div style="width:20px;margin:0px auto;border-left:1px solid rgb(204,204,204);border-right:1px solid rgb(204,204,204);text-align:center;">-</div>';
			$datePickerInput = array('type' => 'datepicker', 'value' => $akt_bis, 'name' => 'daten_filter[date2]', 'html_id' => 'daten_filter_end', 'add_class' => $akt_bis ? 'jsdate-active' : '');
			if(!empty($akt_von) && empty($akt_bis)) {
				$datePickerInput['viewDate'] = sql_datum($akt_von);
			}

			$smarty->assign('input', $datePickerInput);
			$code .= $smarty->fetch('ko_formular_elements.tmpl');
			$code .=
"<script>
	$('#daten_filter_start-input-group, #daten_filter_end-input-group').on('dp.change', function(date, oldDate) {
		$('#submit_filter').click();
	});
</script>";

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

				$code .= '<div style="padding-top:5px;'.$style.'">';
				$code .= '<div class="checkbox">';
				$code .= '<label for="chk_perm_filter">';
				$code .= '<input type="checkbox" name="chk_perm_filter" id="chk_perm_filter" '.$checked.'>';
				//Bestehenden permanenten Filter anzeigen, falls eingeschaltet
				if($perm_filter_desc) {
					$code .= getLL('filter_use_globally_applied').'<br>'.$perm_filter_desc;
				} else {
					$code .= getLL('filter_use_globally');
				}
				$code .= '</label>';
				$code .= '</div>';
				$code .= "</div>";

			}//if(group_mod)


			$code .= '<button style="display:none;" type="submit" class="btn btn-sm btn-default full-width" value="'.getLL("filter_refresh").'" name="submit_filter" id="submit_filter" onclick="set_action(\'submit_filter\', this);">' . getLL("filter_refresh") . '</button>';

			$submenu[$menucounter]['form'] = TRUE;
			$submenu[$menucounter]['form_hidden_inputs'] = array(array('name' => 'action', 'value' => ''),
				array('name' => 'ids', 'value' => ''));
			$submenu[$menucounter]["titel"] = getLL("submenu_daten_title_filter");

			$submenu[$menucounter]['items'][] = ko_get_menuitem_link('daten', 'set_filter_today', null, '',false, getLL('filter_from_today'));
			$submenu[$menucounter]["items"][] = ko_get_menuitem_html($code, getLL("time_filter"));
		break;



		case "itemlist_termingruppen":
			if($max_rights <= 0) break;
			$itemlist_content = array();
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
						if($_SESSION['show'] != 'calendar' && $group['type'] == 1) continue;
						$groups[$gid] = $group;
					}
					//Find selected groups
					$selected = $local_ids = array();
					foreach($groups as $gid => $group) {
						if(in_array($gid, $_SESSION["show_tg"])) $selected[$gid] = TRUE;
						$local_ids[] = $gid;
					}
					//Don't show whole calendar if no local event groups for displays other than calendar (calendar would contain no eventgroups)
					if(($_SESSION['show'] != 'calendar' && sizeof($local_ids) == 0) || sizeof($groups) == 0) continue;

					$itemlist[$counter]["type"] = "group";
					$itemlist[$counter]["name"] = $cal["name"].'<sup> (<span name="calnum_'.$cal['id'].'">'.sizeof($selected).'</span>)</sup>';
					$itemlist[$counter]["aktiv"] = (sizeof($groups) == sizeof($selected) ? 1 : 0);
					$itemlist[$counter]["value"] = $cid;
					$itemlist[$counter]["open"] = isset($_SESSION["daten_calendar_states"][$cid]) ? $_SESSION["daten_calendar_states"][$cid] : 0;

					$counter++;

					foreach($groups as $i_i => $i) {
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
				$itemlist[$counter]["name"] = ko_html($i["name"]);
				$itemlist[$counter]["prename"] = '<span style="margin-right:2px;background-color:#'.($i["farbe"]?$i["farbe"]:"fff").';">&emsp;</span>';
				$itemlist[$counter]["aktiv"] = in_array($i_i, $_SESSION["show_tg"]) ? 1 : 0;
				$itemlist[$counter++]["value"] = $i_i;
			}//foreach(groups)


			submenu_itemlist_absences($itemlist, $absence_rights, "daten");
			submenu_itemlist_amtstage($itemlist, "daten");
			$itemlist_content['tpl_itemlist_select'] = $itemlist;

			//Get all presets
			$akt_value = implode(",", $_SESSION["show_tg"]);
			$itemset = array_merge((array)ko_get_userpref('-1', '', 'daten_itemset', 'ORDER BY `key` ASC'), (array)ko_get_userpref($_SESSION['ses_userid'], '', 'daten_itemset', 'ORDER BY `key` ASC'));
			foreach($itemset as $i) {
				$value = $i['user_id'] == '-1' ? '@G@'.$i['key'] : $i['key'];
				$itemselect_values[] = $value;
				$itemselect_output[] = $i['user_id'] == '-1' ? getLL('itemlist_global_short').' '.$i['key'] : $i['key'];
				if($i["value"] == $akt_value) $itemselect_selected = $value;
			}
			$itemlist_content['tpl_itemlist_values'] = $itemselect_values;
			$itemlist_content['tpl_itemlist_output'] = $itemselect_output;
			$itemlist_content['tpl_itemlist_selected'] = $itemselect_selected;
			if($max_rights > 3) $itemlist_content['allow_global'] = TRUE;

			if(ko_module_installed("taxonomy") && $access['taxonomy']['MAX'] >= 1) {
				$taxonomy_filter = ko_taxonomy_form_field("", "ko_event", explode(",", $_SESSION['daten_taxonomy_filter']));
				$taxonomy_filter['name'] = "submenu_taxonomy_filter";
				$taxonomy_filter['allowParentselect'] = TRUE;
				unset($taxonomy_filter['ajaxHandler']['actions']['insert']);
				$localSmarty = clone($smarty);
				$localSmarty->assign('input', $taxonomy_filter);
				$taxonomy_filter_code = $localSmarty->fetch('ko_formular_elements.tmpl');
				$taxonomy_filter_code.= "<script>
					$('#submenu_taxonomy_filter').on('change', function() {
						sendReq('../daten/inc/ajax.php', ['action','id','sesid'], ['itemlisttaxonomy', $(this).val(),'".session_id()."'], do_element);
					});
				</script>";

				$taxonomy_filter_label = getLL('submenu_daten_filter_taxonomy_label');
				$itemlist_content['taxonomy_filter'] = "<br />" . $taxonomy_filter_label  . ":<br />" . $taxonomy_filter_code;
			}

			$submenu[$menucounter]["titel"] = getLL("submenu_daten_title_itemlist_termingruppen");

			$submenu[$menucounter]["items"][] = ko_get_menuitem_itemlist($itemlist_content);
		break;


		default:
			if($menu) {
				$submenu[$menucounter] = submenu($menu, $state, 3, 'daten');
				$found = (is_array($submenu[$menucounter]));
			}
		}//switch($menu)

		//Plugins erlauben, Menuitems hinzuzufügen
		hook_submenu("daten", $menu, $submenu, $menucounter);

		// Clean up submenu
		if ($doClean) ko_clean_submenu('daten', $submenu[$menucounter], $found);

		if($found) {
			$submenu[$menucounter]["key"] = "daten_".$menu;
			$submenu[$menucounter]["id"] = $menu;
			$submenu[$menucounter]["mod"] = "daten";
			$submenu[$menucounter]["sesid"] = session_id();
			$submenu[$menucounter]["state"] = $state;

			$smarty->assign("help", ko_get_help("daten", "submenu_".$menu));
			if($display == 1) {
				$smarty->assign("sm", $submenu[$menucounter]);
				$smarty->assign("ko_path", $ko_path);
				$smarty->display("ko_submenu.tpl");
			} else if($display == 2) {
				$smarty->assign("sm", $submenu[$menucounter]);
				$smarty->assign('hideWrappingDiv', TRUE);
				$smarty->assign("ko_path", $ko_path);
				$return = "sm_daten_".$menu."@@@";
				$return .= $smarty->fetch("ko_submenu.tpl");
			}

			$smarty->clear_assign("help");
			$menucounter++;
		}
		else {
			if ($display == 2) {
				$return = "sm_daten_".$menu."@@@";
			}
		}
	}//foreach(namen as menu)

	if($display == 2) {
		return $return;
	} else if($display == 3) {
		return $submenu;
	}

}//submenu_daten()




//display: 1: Normal, 2: Ajax, 3: SlideoutMenu
function submenu_leute($namen, $state, $display=1, $doClean = true) {
	global $ko_path, $smarty;
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
	if(ko_module_installed('kg')) $kg_all_rights = ko_get_access_all('kg_admin');
	if(ko_module_installed('groups')) $group_all_rights = ko_get_access_all('groups_admin', '', $group_max_rights);

	if($max_rights < 1) return FALSE;

	if (!is_array($namen)) {
		$namen = explode(",", $namen);
	}

	$menucounter = 0;
	foreach($namen as $menu) {
		$found = FALSE;

		switch($menu) {

		case "leute":
			$found = TRUE;
			$submenu[$menucounter]["titel"] = getLL("submenu_leute_title_leute");
			if($max_rights > 1) {
				$submenu[$menucounter]["items"][] = ko_get_menuitem("leute", "neue_person");
			}
			$submenu[$menucounter]["items"][] = ko_get_menuitem("leute", "show_all");

			//$submenu[$menucounter]["items"][] = ko_get_menuitem("leute", "show_adressliste");

			$allowed_cols = ko_get_leute_admin_spalten($_SESSION['ses_userid'], 'all');
			if(!is_array($allowed_cols['view']) || in_array('geburtsdatum', $allowed_cols['view'])) {
				$submenu[$menucounter]["items"][] = ko_get_menuitem("leute", "geburtstagsliste");
			}

			if($all_rights > 3 || ($gs_rights && $group_max_rights > 1)) $submenu[$menucounter]["items"][] = ko_get_menuitem_seperator();

			//Adress-Aenderungen
			if((ko_get_setting('leute_allow_moderation') && $max_rights > 1) || $max_rights > 3) {
				ko_get_mod_leute($aa);
				//For logins with edit access to only some addresses exclude those they don't have access to
				if($all_rights < 2) {
					if(!is_array($access['leute']) && ko_module_installed('leute')) ko_get_access('leute');
					foreach($aa as $aid => $a) {
						if($access['leute'][$a['_leute_id']] < 2 || $a['_leute_id'] < 1) unset($aa[$aid]);
					}
				}
				$nr_mod = sizeof($aa);
				$submenu[$menucounter]["items"][] = ko_get_menuitem_link('leute', 'mutationsliste', $nr_mod, $ko_path."leute/index.php?action=show_aa", !($nr_mod>0));
			}
			//Gruppen-Anmeldungen
			if($all_rights > 3 || ($gs_rights && $group_max_rights > 1)) {
				ko_get_groupsubscriptions($gs, "", $_SESSION["ses_userid"]);
				$nr_mod = sizeof($gs);
				$submenu[$menucounter]["items"][] = ko_get_menuitem_link("leute", "groupsubscriptions", $nr_mod, '', !($nr_mod>0));
			}
			//Address cleansing
			if($all_rights > 3) {
				ko_get_leute_revisions($revisions);
				$nr_mod = sizeof($revisions);
				$submenu[$menucounter]["items"][] = ko_get_menuitem_link("leute", "revisions", $nr_mod, '', !($nr_mod>0));
			}
			//Import (allow with max rights 2)
			if($max_rights > 1 && ko_get_setting('leute_allow_import') == 1) {
				$submenu[$menucounter]["items"][] = ko_get_menuitem_seperator();
				$submenu[$menucounter]["items"][] = ko_get_menuitem_link("leute", "import", null, $ko_path."leute/index.php?action=import&amp;state=1");
			}

			//AssignToOwnGroup
			if($display == 1 && ko_get_leute_admin_assign($_SESSION['ses_userid'], 'all')) {

				$agroups = ko_get_leute_admin_groups($_SESSION['ses_userid'], 'all');
				$agroup = ko_groups_decode(array_shift($agroups), 'group');
				$token = md5(uniqid('', TRUE));
				$_SESSION['peoplesearch_access_token'] = $token;
				$code = '
<form action="index.php" method="POST">
	<input type="hidden" name="action" value="global_assign" />
	<input type="hidden" name="global_assign" id="global_assign">
	<script>
		$(\'#global_assign\').peoplesearch({
			multiple: false,
			accessToken: "'.$token.'",
			mode: "all",
		});
	</script>
	<button class="btn btn-sm btn-default full-width" type="submit" name="submit_global_assign" value="'.getLL('submenu_leute_global_assign_button').'">'.getLL('submenu_leute_global_assign_button').'</button>
</form>';
				$submenu[$menucounter]['items'][] = ko_get_menuitem_seperator();
				$submenu[$menucounter]['items'][] = ko_get_menuitem_html($code, getLL('submenu_leute_global_assign'));
			}

			$submenu[$menucounter]['items'][] = ko_get_menuitem_link('leute', 'leute_chart');
		break;

		case "meine_liste":
			$found = TRUE;
			$submenu[$menucounter]['form'] = TRUE;
			$submenu[$menucounter]['form_hidden_inputs'] = array(array('name' => 'action', 'value' => ''),
				array('name' => 'ids', 'value' => ''));

			$submenu[$menucounter]["titel"] = getLL("submenu_leute_title_meine_liste");
			$submenu[$menucounter]["items"][] = ko_get_menuitem_html('<div style="width:100%;text-align:center;"><div class="btn-group btn-group-sm"><button class="btn btn-default" disabled>' . getLL('mylist_selected') . '</button><button type="submit" name="add_my_list" class="btn btn-primary" value="'.getLL("mylist_add_selected").'" title="'.getLL("mylist_add_selected").'" onclick="set_ids_from_chk(this);set_action('."'add_to_my_list'".', this)"><i class="fa fa-plus"></i></button><button type="submit" name="del_from_my_list" class="btn btn-danger" value="'.getLL("mylist_del_selected").'" title="'.getLL("mylist_del_selected").'" onclick="set_ids_from_chk(this);set_action('."'del_from_my_list'".', this)"><i class="fa fa-minus"></i></button></div></div>');
			//Add mailing link
			if(ko_module_installed('mailing') && $MAILING_PARAMETER['domain'] != '' && sizeof($_SESSION['my_list']) > 0) {
				$add = '<a class="btn btn-primary" href="mailto:ml@'.$MAILING_PARAMETER['domain'].'" title="'.getLL('mylist_send_email').'"><i class="fa fa-send"></i></a>';
			} else {
				$add = '';
			}

			//Check addresses in my_list for deleted ones and show correct number
			$num = 0;
			if(sizeof($_SESSION['my_list']) > 0) {
				foreach($_SESSION['my_list'] as $k => $v) if(!$v) unset($_SESSION['my_list'][$k]);
				if(sizeof($_SESSION['my_list']) > 0) {
					$z_where = "AND `id` IN (".implode(',', $_SESSION['my_list']).") AND `deleted` = '0'".ko_get_leute_hidden_sql();
					$num = db_get_count('ko_leute', 'id', $z_where);
				}

				$code  = '<div style="width:100%;text-align:center;"><div class="btn-group btn-group-sm">';
				$code .= '<a class="btn btn-primary" href="index.php?action=show_my_list">'.getLL('submenu_leute_show_my_list').' ('.$num.')</a>';
				$code .= '<a class="btn btn-danger" href="index.php?action=clear_my_list">'.getLL('submenu_leute_clear_my_list').'</a>';
				$code .= $add;
				$code .= '</div></div>';
				$submenu[$menucounter]["items"][] = ko_get_menuitem_seperator(true);
				$submenu[$menucounter]["items"][] = ko_get_menuitem_html($code);

			}
			//Show presets
			$code = '';
			$presets = ko_get_userpref($_SESSION['ses_userid'], '', 'leute_my_list', 'ORDER BY `key` ASC', TRUE);
			if($presets === '') $presets = [];
			if(sizeof($presets) > 0 || $num > 0) {
				$code  = '<div style="width:100%;text-align:center;"><div class="input-group input-group-sm" style="width: 100%;">';

				if(sizeof($_SESSION['my_list']) > 0) {
					$code .= '<div class="input-group-addon" style="padding: 0; border: 0;">';
					$code .= '<button type="button" title="'.getLL('itemlist_save_preset').'" class="btn btn-default btn-sm" onclick="$(\'#mylist_preset\').toggle();"><i class="fa fa-plus" style="line-height:1.5;"></i></button>';
					$code .= '</div>';
				}

				$code .= '<select name="sel_mylist_preset" id="sel_mylist_preset" class="form-control" size="0" onchange="sendReq(\'../leute/inc/ajax.php\', [\'action\',\'id\',\'sesid\'], [\'mylistpresetopen\',this.options[this.selectedIndex].value,\''.session_id().'\'], do_element);"><option value=""></option>';
				$presetActive = FALSE;
				foreach($presets as $preset) {
					$sel = '';
					if($_SESSION['show'] == 'show_my_list' && implode(',', $_SESSION['my_list']) == $preset['value']) {
						$presetActive = TRUE;
						$sel = 'selected="selected"';
					}
					$code .= '<option '.$sel.' value="'.$preset['id'].'">'.$preset['key'].'</option>';
				}
				$code .= '</select>';

				if($presetActive) {
					$code .= '<div class="input-group-addon" style="padding: 0; border: 0;">';
					$code .= '<button type="button" title="'.getLL('itemlist_delete_preset').'" class="btn btn-default btn-sm" onclick="sendReq(\'../leute/inc/ajax.php\', [\'action\',\'id\',\'sesid\'], [\'mylistpresetdelete\',document.getElementById(\'sel_mylist_preset\').options[document.getElementById(\'sel_mylist_preset\').selectedIndex].value,\''.session_id().'\'], do_element); return false;"><i class="fa fa-trash" style="line-height:1.5;"></i></button>';
					$code .= '</div>';
				}
				$code .= '</div></div>';

				$code .= '<div style="display: none;" id="mylist_preset">';
				$code .= '<div class="input-group input-group-sm">';
				$code .= '<input type="text" class="input-sm form-control" name="txt_mylist_preset_new" id="txt_mylist_preset_new" placeholder="'.getLL('itemlist_preset_placeholder').'" />';
				$code .= '<div class="input-group-btn">';
				$code .= '<button type="button" class="btn btn-default" title="'.getLL('itemlist_save_preset').'" onclick="sendReq(\'../leute/inc/ajax.php\', [\'action\',\'name\',\'sesid\'], [\'mylistpresetnew\',document.getElementById(\'txt_mylist_preset_new\').value,\''.session_id().'\'], do_element); return false;"><i class="fa fa-save"></i></button>';
				$code .= '</div>';
				$code .= '</div>';

				$code .= '</div>';
				$submenu[$menucounter]['items'][] = ko_get_menuitem_html($code, 'Vorlage');
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

			$html  = '<select class="input-sm form-control" name="sel_auswahl">';
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
			$submenu[$menucounter]["items"][] = ko_get_menuitem_html($html."</select>", getLL("rows"));

			$html  = '<select class="input-sm form-control" name="sel_cols">';
			$html .= '<option value="alle">'.getLL("all").'</option>';
			$html .= '<option value="angezeigte" selected="selected">'.getLL("shown").'</option>';
			$itemset = array_merge((array)ko_get_userpref('-1', '', 'leute_itemset', 'ORDER BY `key` ASC'), (array)ko_get_userpref($_SESSION['ses_userid'], '', 'leute_itemset', 'ORDER BY `key` ASC'));

			$colNames = ko_get_leute_col_name(FALSE, TRUE);
			foreach($itemset as $i) {
				$cols = explode(',', $i['value']);
				$llCols = array();
				foreach($cols as $col) {
					$llCols[] = $colNames[$col];
				}
				$value = $i['user_id'] == '-1' ? '@G@'.$i['key'] : $i['key'];
				$desc = $i['user_id'] == '-1' ? getLL('itemlist_global_short').' '.$i['key'] : $i['key'];
				$html .= '<option value="set_'.$value.'" title="'.ko_html(implode(', ', $llCols)).'">&quot;'.$desc.'&quot;</option>';
			}
			$html .= '</select>';
			$submenu[$menucounter]["items"][] = ko_get_menuitem_html($html, getLL("columns"));

			//Add select to choose rectype
			if(sizeof($RECTYPES) > 0) {
				$html  = '<select class="input-sm form-control" name="sel_rectype">';
				$html .= '<option value="">'.getLL("leute_export_rectype_defined").'</option>';
				$html .= '<option value="" disabled="disabled">------</option>';
				$html .= '<option value="_default">'.getLL("kota_ko_leute_rectype_default").'</option>';
				foreach($RECTYPES as $k => $v) {
					if($k == '_default') continue;
					$html .= '<option value="'.$k.'">'.getLL('kota_ko_leute_rectype_'.$k).'</option>';
				}
				$html .= '</select>';
				$submenu[$menucounter]["items"][] = ko_get_menuitem_html($html, getLL("leute_export_force_rectype"));
			}

			if(ko_get_setting("leute_information_lock") && $access["leute"]["ALLOW_BYPASS_INFORMATION_LOCK"] == 1) {
				$html = '<input type="checkbox" class="switch" name="actions_information_lock" data-size="small" data-off-text="' . getLL('no') . '" data-on-text="' . getLL('yes') . '" value="1" ' . (ko_get_userpref($_SESSION['ses_userid'], "leute_apply_informationlock") ? "checked" : "") . '>
				<script>
					$(\'input[name="actions_information_lock"]\').bootstrapSwitch();
					$(\'input[name="actions_information_lock"]\').on("switchChange.bootstrapSwitch", function(e) {
						var status = e.target.checked;
						sendReq("../leute/inc/ajax.php", "action,value,sesid", "informationlockfilter,"+status+",' . session_id() . '", do_element);
					});
				</script>';
				$submenu[$menucounter]["items"][] = ko_get_menuitem_html($html, getLL("leute_action_information_lock"));
			}

			$submenu[$menucounter]["items"][] = ko_get_menuitem_seperator(true);
			$buttons[] = '<button type="submit" class="btn btn-primary" onclick="set_ids_from_chk(this);set_action(\'leute_action\', this);set_hidden_value(\'id\', \'excel\', this);" title="'.getLL("export_excel").'"><i class="fa fa-file-excel-o"></i>&nbsp;<small>'.getLL("export_excel_short").'</small></button>';
			$buttons[] = '<button type="submit" class="btn btn-primary" onclick="set_ids_from_chk(this);set_action(\'leute_action\', this);set_hidden_value(\'id\', \'xls_settings\', this);" title="'.getLL("export_excel_settings").'"><span class="fa-stack"><i class="fa fa-file-excel-o fa-stack-1x"></i><i class="fa fa-plus-circle fa-stack-sm fa-stack-bottom-right"></i></span>&nbsp;&nbsp;<small>'.getLL("export_excel_settings_short").'</small></button>';

			if(
				(defined('ALLOW_SEND_EMAIL') && ALLOW_SEND_EMAIL === FALSE) === FALSE &&
				(db_get_count('ko_scheduler_tasks', 'id', 'AND name = "Mailing" AND status = 1') >= 1) &&
				!(!is_array($MAILING_PARAMETER) || sizeof($MAILING_PARAMETER) < 3)
			) {
				$buttons[] = '<button type="submit" class="btn btn-primary" onclick="set_ids_from_chk(this);set_action(\'leute_action\', this);set_hidden_value(\'id\', \'email\', this);" title="' . getLL("export_email") . '"><i class="fa fa-envelope"></i>&nbsp;<small>' . getLL("export_email_short") . '</small></button>';
			}

			$buttons[] = '<button type="submit" class="btn btn-primary" onclick="set_ids_from_chk(this);set_action(\'leute_action\', this);set_hidden_value(\'id\', \'etiketten_settings\', this);" title="'.getLL("export_etiketten").'"><i class="fa fa-print"></i>&nbsp;<small>'.getLL("export_etiketten_short").'</small></button>';
			//TODO: Remove completely: $buttons[] = '<button type="submit" class="btn btn-primary" onclick="set_ids_from_chk(this);set_action(\'leute_action\', this);set_hidden_value(\'id\', \'mailmerge\', this);" title="'.getLL("export_mailmerge").'"><i class="fa fa-files-o"></i>&nbsp;<small>'.getLL("export_mailmerge_short").'</small></button>';
			$buttons[] = '<button type="submit" class="btn btn-primary" onclick="set_ids_from_chk(this);set_action(\'leute_action\', this);set_hidden_value(\'id\', \'details_settings\', this);" title="'.getLL("export_details").'"><i class="fa fa-file-text-o"></i>&nbsp;<small>'.getLL("export_details_short").'</small></button>';
			if(ko_module_installed('sms') && $SMS_PARAMETER['user'] != '' && $SMS_PARAMETER['pass'] != '') {
				$buttons[] = '<button type="submit" class="btn btn-primary" onclick="set_ids_from_chk(this);set_action(\'leute_action\', this);set_hidden_value(\'id\', \'sms\', this);" title="'.getLL("export_sms").'"><i class="fa fa-mobile-phone"></i>&nbsp;<small>'.getLL("export_sms_short").'</small></button>';
			}
			if(ko_module_installed('telegram') && ko_get_setting('telegram_token')) {
				$buttons[] = '<button type="submit" class="btn btn-primary" onclick="set_ids_from_chk(this);set_action(\'leute_action\', this);set_hidden_value(\'id\', \'telegram\', this);" title="'.getLL("export_telegram_short").'"><i class="fa fa-send"></i>&nbsp;<small>'.getLL("export_telegram_short").'</small></button>';
			}
			$buttons[] = '<button type="submit" class="btn btn-primary" onclick="set_ids_from_chk(this);set_action(\'leute_action\', this);set_hidden_value(\'id\', \'vcard\', this);" title="'.getLL("export_vcard").'"><i class="fa fa-credit-card"></i>&nbsp;<small>'.getLL("export_vcard_short").'</small></button>';
			$buttons[] = '<button type="submit" class="btn btn-primary" onclick="set_ids_from_chk(this);set_action(\'leute_action\', this);set_hidden_value(\'id\', \'csv\', this);" title="'.getLL("export_csv").'"><i class="fa fa-file-o"></i>&nbsp;<small>'.getLL("export_csv_short").'</small></button>';

			if(ko_get_userpref($_SESSION["ses_userid"], "leute_show_deleted") || ko_get_userpref($_SESSION["ses_userid"], "leute_show_hidden")) {
				$leute_warning_export_box = TRUE;
			} else {
				$leute_warning_export_box = FALSE;
			}

			$html = '<div class="alert alert-danger" role="alert" style="display:'.($leute_warning_export_box ? "block" : "none").';" id="leute-warning-export">'.getLL("leute_export_warning_hiddendeleted").'</div> <div id="leute-actions-icons"><div class="btn-group" style="width:100%;">';
			$i = 0;
			foreach ($buttons as $button) {
				if ($i == 3) {
					$html .= '</div>';
					$html .= '<div class="btn-group">';
					$i = 0;
				}
				$html .= $button;
				$i++;
			}
			$html .= '</div></div>';
			$submenu[$menucounter]["items"][] = ko_get_menuitem_html($html);

			//PDF Export layouts
			if($_SESSION['show'] != 'geburtstagsliste') {
				$pdf_layouts = db_select_data("ko_pdf_layout", "WHERE `type` = 'leute'", "id, name", "ORDER BY `name` ASC");
				if(sizeof($pdf_layouts) > 0) {
					$html = '<select name="pdf_layout_id" class="input-sm form-control" onchange="set_ids_from_chk(this);set_action(\'leute_action\', this);set_hidden_value(\'id\', \'pdf_settings\', this);jQuery(this).closest(\'form\').submit();">';
					$html .= '<option value=""></option>';
					foreach($pdf_layouts as $l) {
						$html .= '<option value="'.$l["id"].'">'.ko_html($l["name"]).'</option>';
					}
					$html .= '</select>';
					$submenu[$menucounter]["items"][] = ko_get_menuitem_html($html, getLL("leute_export_pdf"));
				}
			}
		break;


		case "filter":
			$found = TRUE;
			//Show select with all saved filter presets
			$code_vorlage_open = '<div class="input-group input-group-sm">';
			$code_vorlage_open .= '<select name="sel_filterset" class="input-sm form-control" onchange="sendReq(\'../leute/inc/ajax.php\', \'action,name,sesid\', \'leuteopenfilterset,\'+this.options[this.selectedIndex].value+\','.session_id().'\', do_element);">';
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
			$code_vorlage_open .= '<div class="input-group-btn"><button type="button" class="btn btn-default" title="'.getLL("itemlist_delete_preset").'" onclick="c = confirm(\''.getLL("itemlist_delete_preset_confirm").'\');if(!c) return false; sendReq(\'../leute/inc/ajax.php\', \'action,name,sesid\', \'leutedelfilterset,\'+document.getElementsByName(\'sel_filterset\')[0].options[document.getElementsByName(\'sel_filterset\')[0].selectedIndex].value+\','.session_id().'\', do_element);return false;"><i class="fa fa-trash"></i></button></div>';
			$code_vorlage_open .= '</div>';

			//Mailing:
			if(ko_module_installed('mailing') && $MAILING_PARAMETER['domain'] != '') {
				if(is_array($current_filterset)) {
					if($current_filterset['mailing_alias']) {
						$mailing_address = $current_filterset['mailing_alias'].'@'.$MAILING_PARAMETER['domain'];
					} else {
						$mailing_address = 'fp'.$current_filterset['id'].'@'.$MAILING_PARAMETER['domain'];
					}
					$code_vorlage_open .= '<div class="btn-group btn-group-sm btn-group-infilling" style="margin-top:2px;">';
					$code_vorlage_open .= '<div class="btn-group btn-main"><button class="btn btn-sm btn-default disabled">'.getLL('module_mailing').'</button></div>';
					$code_vorlage_open .= '<a class="btn btn-primary btn-sm" href="mailto:'.$mailing_address.'" title="'.getLL('mailing_send_email').'"><i class="fa fa-send"></i></a>';
					$code_vorlage_open .= '<div class="btn-group"><button class="btn btn-sm btn-success" type="button" title="'.getLL('mailing_edit_settings').'" id="fp_alias_switch" class="cursor_pointer"><i class="fa fa-edit"></i></button></div>';
					$code_vorlage_open .= '</div><i class="clearfix"></i>';

					$code_vorlage_open .= '<div style="display: none;" id="fp_alias_container">';
					$code_vorlage_open .= '<div class="input-group input-group-sm" style="margin-top:2px;">';
					$code_vorlage_open .= '<span class="input-group-addon">'.getLL('mailing_alias').':</span>';
					$code_vorlage_open .= '<input type="text" class="input-sm form-control" name="txt_fp_alias" id="fpalias" value="'.$current_filterset['mailing_alias'].'" />';
					$code_vorlage_open .= '<div class="input-group-btn"><button class="btn btn-primary btn-sm" type="button" title="'.getLL('save').'" onclick="sendReq(\''.$ko_path.'leute/inc/ajax.php\', \'action,fpid,alias,sesid\', \'savefpalias,'.$current_filterset['id'].',\'+$(\'#fpalias\').val()+\','.session_id().'\', do_element);"><i class="fa fa-save"></i></button></div>';
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
			$code_filterliste = '<div class="panel-group" id="leuteFilter">';
			foreach($f_ as $fi => $ff) {
				//Skip hidden filters
				if(in_array($fi, $hide_filter)) continue;
				//Don't show filters with no code1 (can be used for hidden filters only used in manually stored preset)
				if($ff['code1'] == '') continue;

				if($ff['group'] != $cg) {
					if($group_code) {
						$code_filterliste .= '<div class="panel panel-default">';
						$code_filterliste .= '<div class="panel-heading' . ($active ? ' active' : '') . '">';
						$code_filterliste .= '<h5 class="panel-title"><a data-toggle="collapse" data-parent="#leuteFilter" href="#fg'.$cg.'">' . getLL('filter_group_'.$cg) . '</a></h5>';
						$code_filterliste .= '</div>';
						$code_filterliste .= '<div class="panel-collapse collapse' . ($active ? ' in' : '') . '" id="fg'.$cg.'" role="tabpanel">';
						$code_filterliste .= '<div class="panel-body">';
						$code_filterliste .= $group_code;
						$code_filterliste .= '</div>';
						$code_filterliste .= '</div>';
						$code_filterliste .= '</div>';
					}
					$cg = $ff['group'];
					$group_code = '';
					$active = FALSE;
				}
				if($_SESSION['filter_akt'] == $ff['id']) {
					$active = TRUE;
					$class = 'filter-button active';
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
						$class = 'filter-button active';
					} else {
						$class = 'filter-button';
					}
					$group_code .= '<div class="'.$class.'" onclick="sendReq(\'../leute/inc/ajax.php\', \'action,fid,sesid\', \'leutefilterform,'.$ff['id'].','.session_id().'\', do_element);">'.$ff['name'].'</div>';
				}
				if($group_code) {
					$code_filterliste .= '<div class="panel panel-default">';
					$code_filterliste .= '<div class="panel-heading' . ($active ? ' active' : '') . '">';
					$code_filterliste .= '<h5 class="panel-title"><a data-toggle="collapse" data-parent="#leuteFilter" href="#fghidden">' . getLL('filter_group_hidden') . '</a></h5>';
					$code_filterliste .= '</div>';
					$code_filterliste .= '<div class="panel-collapse collapse' . ($active ? ' in' : '') . '" id="fghidden" role="tabpanel">';
					$code_filterliste .= '<div class="panel-body">';
					$code_filterliste .= $group_code;
					$code_filterliste .= '<i class="clearfix"></i>';
					$code_filterliste .= '</div>';
					$code_filterliste .= '</div>';
					$code_filterliste .= '</div>';
				}
			}

			$code_filterliste .= '</div>';


			//Variablen zu aktivem Filter anzeigen
			$filter_form_code = ko_get_leute_filter_form($_SESSION["filter_akt"]);

			//Vorlage speichern
			$code_vorlage_save = '<div class="checkbox"><label><input type="checkbox" name="chk_filterset_new_with_col" id="chk_filterset_new_with_col" value="1">'.getLL('leute_filter_save_with_cols').'</label></div>';
			if($max_rights > 2) $code_vorlage_save .= '<div class="checkbox"><label><input type="checkbox" name="chk_filterset_global" id="chk_filterset_global" value="1">'.getLL('leute_filter_global').'</label></div>';
			$code_vorlage_save .= '<div class="input-group input-group-sm">';
			$code_vorlage_save .= '<input type="text" class="input-sm form-control" name="txt_filterset_new" id="txt_filterset_new">';

			//Save filter for other users
			$code_vorlage_save .= '<div class="input-group-btn"><button type="button" class="btn btn-default" title="'.getLL("itemlist_save_preset").'" onclick="sendReq(\'../leute/inc/ajax.php\', [\'action\',\'name\',\'withcols\',\'logins\',\'global\',\'sesid\'], [\'leutesavefilterset\',document.getElementById(\'txt_filterset_new\').value,document.getElementById(\'chk_filterset_new_with_col\').checked,document.getElementsByName(\'chk-filter-for-logins\')[0].value,$(\'#chk_filterset_global\').is(\':checked\'),\''.session_id().'\'], do_element);return false;"><i class="fa fa-save"></i></button></div>';
			if($max_rights > 2) {
				$code_vorlage_save .= '<div class="input-group-btn"><button type="button" class="btn btn-default" onclick="change_vis(\'filter_for_logins\');" alt="options"><i class="fa fa-arrow-down"></i></button></div>';
			}
			$code_vorlage_save .= '</div>';
			$code_vorlage_save .= '<div name="filter_for_logins" id="filter_for_logins" style="display:none;visibility:hidden;">';
			$code_vorlage_save .= '<h5 for="chk-filter-for-logins">'.getLL("leute_filter_save_for_logins").'</h5>';

			ko_get_logins($logins);
			$values = $descs = $avalues = array();
			$avalue = '';
			foreach ($logins as $l) {
				if ($l['id'] == ko_get_root_id() || $l['id'] == $_SESSION['ses_userid'] || $l['disabled']) continue;
				$values[] = $l['id'];
				$descs[] = $l['login'];
			}
			$input = array(
				'name' => 'chk-filter-for-logins',
				'type' => 'checkboxes',
				'size' => 6,
				'values' => $values,
				'descs' => $descs,
				'avalues' => $avalues,
				'avalue' => $avalue,
			);
			$smartyClone = clone($smarty);
			$smartyClone->assign('input', $input);
			$checkboxesHtml = $smartyClone->fetch('ko_formular_elements.tmpl');

			$code_vorlage_save .= $checkboxesHtml;
			$code_vorlage_save .= '</div>';

			//Angewandte Filter anzeigen
			if(sizeof($_SESSION["filter"]) > 0) {
				$found_filter = FALSE;
				foreach($_SESSION["filter"] as $f_i => $f) {
					if(!is_numeric($f_i)) continue;

					if(!$found_filter) {
						$found_filter = TRUE;
						$code_akt_filter = '';
						$size = 0;
						foreach($_SESSION['filter'] as $k => $v) {
							if(!is_numeric($k)) continue;
							$size++;
						}
						$code_akt_filter .= '<label for="sel_filter">'.getLL('leute_filter_current').':</label>';
						$code_akt_filter .= '<select class="input-sm form-control" size="'.max(2, $size).'" name="sel_filter" id="sel_filter" multiple>';
					}

					ko_get_filter_by_id($f[0], $f_);

					//Name des Filters
					$f_name = $f_["name"];

					$processedValues = array();
					//Tabellen-Name, auf den dieser Filter am ehesten wirkt, auslesen/erraten:
					$col = array();
					if ($f_['sql1'] == 'kota_filter') {
						ko_leute_filter_make_bw_compatible($f_, $f);
						$kotaFilterData = $f[1][1]['kota_filter_data']['ko_leute'];
						$f[1] = array();
						$c = 1;
						foreach ($kotaFilterData as $column => $value) {
							if ($KOTA['ko_leute'][$column]['form']['type'] == 'jsdate' || $KOTA['ko_leute'][$column]['filter']['type'] == 'jsdate') {
								$valueString = "";
								if ($value['neg']) $valueString .= '!';
								$valueString .= sql2datum($value['from']);
								$valueString .= '-'.sql2datum($value['to']);
								$processedValues[$column] = $valueString;
							}
							$f[1][$c] = $value;
							$col[$c] = $column;
							$c++;
						}
					} else {
						for($c=1; $c<5; $c++) {
							list($col[$c]) = explode(' ', $f_['sql'.$c]);
						}
					}

					//Variablen auslesen
					$vars = "";
					$fulltitle = '';
					$t1 = $t2 = '';
					for($i = 1; $i <= sizeof($f[1]); $i++) {
						if ($processedValues[$col[$i]]) {
							$v = $processedValues[$col[$i]];
						} else if($f_['_name'] == "billing") {
							if($i == 1) {
								if(is_numeric($f[1][1])) {
									$dossiers = ko_billing_get_dossiers();
									$v = $dossiers[$f[1][1]]['title'];
								} else {
									$v = '';
								}
							}
							if($i == 2) {
								if(is_numeric($f[1][2])) {
									$billing_status = ko_billing_get_status();
									$v = $billing_status[$f[1][2]]['title'];
								} else {
									$v = '';
								}
							}
						} else if($f_['_name'] == "taxonomy") {
							if($i == 1) {
								$term = ko_taxonomy_get_term_by_id($f[1][1]);
								$v = $term['name'];
							}
							if($i == 2) {
								$role_id = format_userinput($f[1][2], "int");
								if(is_numeric($role_id)) {
									$role = db_select_data("ko_grouproles", "WHERE id = " . $role_id, "name", "", "", TRUE, TRUE);
									$v = $role['name'];
								}
							}
						} else {
							$v = map_leute_daten($f[1][$i], ($col[$i] ? $col[$i] : $col[1]), $t1, $t2, FALSE, array('num' => $i));
						}
						if (!$v) $v = $f[1][$i];
						$v = strip_tags($v);
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
					if($f[2] == 1 && $f_['sql1'] != 'kota_filter') $neg = "!";
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

					$code_akt_filter .= '<label style="display:block;">' . getLL('filter_combine').':';
					$code_akt_filter .= '&nbsp;<button type="button" class="icon" onclick="change_vis(\'filter_link\'); change_vis(\'adv_filter_link\'); change_vis(\'adv_filter_link_help\');" style="cursor: pointer;" title="'.getLL('leute_filter_link_adv_title').'"><i class="fa fa-wrench"></i></button>';
					$help = ko_get_help('leute', 'filter_link_adv', array('ph' => 'r'));
					if($help['show']) $code_akt_filter .= '&nbsp;<span id="adv_filter_link_help" '.$vis_link_adv.'>'.$help['link'].'</span>';
					$code_akt_filter .= '</label>';

					//Regular filter link with AND/OR
					if(!$filter_link['and'] && !$filter_link['or']) $filter_link['and'] = 'checked="checked"';

					$help = ko_get_help('leute', 'filter_link');
					$code_akt_filter .= '<div style="display: block; float: right;">'.$help['link'].'</div>';

					$code_akt_filter .= '<div id="filter_link" '.$vis_link.'>';
					$code_akt_filter .= '<label class="radio-inline"><input type="radio" name="rd_filter_link" id="rd_filter_link_and" value="and" '.$filter_link['and'].' onclick="sendReq(\'../leute/inc/ajax.php\', \'action,link,sesid\', \'leutefilterlink,and,'.session_id().'\', do_element);">' . getLL('filter_AND') . '</label>';
					$code_akt_filter .= '<label class="radio-inline"><input type="radio" name="rd_filter_link" id="rd_filter_link_or" value="or" '.$filter_link['or'].' onclick="sendReq(\'../leute/inc/ajax.php\', \'action,link,sesid\', \'leutefilterlink,or,'.session_id().'\', do_element);">' . getLL('filter_OR') . '</label>';
					$code_akt_filter .= '</div>';
					//Input for advanced linking
					$code_akt_filter .= '<div id="adv_filter_link" '.$vis_link_adv.'>';
					$code_akt_filter .= '<div class="input-group input-group-sm">';
					$code_akt_filter .= '<input class="input-sm form-control" type="text" id="input_filter_link_adv" '.$class_link_adv.' title="'.$title_link_adv.'" value="'.$_SESSION['filter']['link_adv'].'">';
					$code_akt_filter .= '<div class="input-group-btn">';
					$code_akt_filter .= '<button type="button" class="btn btn-default" onclick="sendReq(\'../leute/inc/ajax.php\', \'action,link,sesid\', \'leutefilterlinkadv,\'+document.getElementById(\'input_filter_link_adv\').value+\','.session_id().'\', do_element); return false;">'.getLL('OK').'</button>';
					$code_akt_filter .= '</div>';
					$code_akt_filter .= '</div>';
					$code_akt_filter .= '</div>';
					//Buttons to delete applied filters
					$code_akt_filter .= '<div class="btn-field">';
					$code_akt_filter .= '<button type="button" class="btn btn-sm btn-danger" value="'.getLL('delete').'" name="submit_del_filter" onclick="sendReq('."'../leute/inc/ajax.php', 'action,id,sesid', 'leutefilterdel,'+document.getElementById('sel_filter').options[document.getElementById('sel_filter').selectedIndex].value+',".session_id()."', do_element);".'">' . getLL('delete') . '</button>';
					$code_akt_filter .= '<button type="button" class="btn btn-sm btn-danger" value="'.getLL('delete_all').'" name="submit_del_all_filter" onclick="sendReq(\'../leute/inc/ajax.php\', \'action,sesid\', \'leutefilterdelall,'.session_id().'\', do_element);">' . getLL('delete_all') . '</button>';
					$code_akt_filter .= '</div>';
				}
			}//if(sizeof(filter)>0)


			$submenu[$menucounter]['form'] = TRUE;
			$submenu[$menucounter]['form_hidden_inputs'] = array(array('name' => 'action', 'value' => ''),
				array('name' => 'ids', 'value' => ''));

			$submenu[$menucounter]["titel"] = getLL("submenu_daten_title_filter");
			$submenu[$menucounter]["items"][] = ko_get_menuitem_html($code_vorlage_open, getLL("itemlist_open_preset"));
			$submenu[$menucounter]["items"][] = ko_get_menuitem_html($code_filterliste);
			$submenu[$menucounter]["items"][] = ko_get_menuitem_html($filter_form_code);
			$submenu[$menucounter]["items"][] = ko_get_menuitem_html($code_vorlage_save, getLL("itemlist_save_preset"));
			$submenu[$menucounter]["items"][] = ko_get_menuitem_html($code_akt_filter);

		break;

		case "itemlist_spalten":
			$itemlist_content = array();
			$found = TRUE;

			// define html skeleton for title entries
			$titleSkeleton = "<span class=\"bg-info\" style=\"display:block;text-align:center;padding:0px 0 0px 0;\"><b>%s</b></span>";

			//Alle Spalten auslesen
			$counter = 0;
			$cols = ko_get_leute_col_name(TRUE, TRUE, 'view', FALSE, $rawgdata, 'form', TRUE);
			$groups = $smallgroups = FALSE;
			$seenPlugins = array();
			$open_ids = $only_open_ids = array();
			foreach($cols as $i_i => $i) {
				if($i == '') continue;

				$aktiv = 0;
				foreach($_SESSION["show_leute_cols"] as $s) {
					if($s == $i_i) {
						$aktiv = 1;
					}
				}

				//Add title for layout groups in leute form
				if(substr($i_i, 0, 9) == '___title_') {
					$itemlist[$counter]['html'] = sprintf($titleSkeleton, $i);
					$itemlist[$counter++]['type'] = "html";
					continue;
				}

				//Add title above first smallgroup (disabled)
				if(substr($i_i, 0, 8) == 'MODULEkg' && !$smallgroups) {
					$smallgroups = TRUE;
					$itemlist[$counter]['html'] = sprintf($titleSkeleton, getLL('module_kg'));
					$itemlist[$counter++]['type'] = "html";
				}
				//Add title above first group (disabled)
				if(substr($i_i, 0, 9) == "MODULEgrp" && !$groups) {
					$groups = TRUE;
					$itemlist[$counter]['html'] = sprintf($titleSkeleton, getLL('groups'));
					$itemlist[$counter++]['type'] = "html";
				}
				//Allow plugins to add header by defining LL key my_PLUGIN-leute_column_header
				// which will be added before first MODULEpluginPLUGIN_* entry
				if(substr($i_i, 0, 12) == "MODULEplugin") {
					preg_match('/MODULEplugin([a-z]*)_.*/', $i_i, $matches);
					$plugin = $matches[1];
					if($plugin && !in_array($plugin, $seenPlugins)) {
						$seenPlugins[] = $plugin;
						$llTitle = getLL('my_'.$plugin.'_leute_column_header');
						if($llTitle) {
							$itemlist[$counter]['html'] = sprintf($titleSkeleton, $llTitle);
							$itemlist[$counter++]['type'] = 'html';
						}
					}
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
				if (!is_array($tgs)) $tgs = array();
				$tgs[] = array('id' => 0, 'name' => getLL('tracking_itemlist_no_group'));
				foreach($tgs as $tg) {
					$trackings = db_select_data('ko_tracking', "WHERE `group_id` = '".$tg['id']."'", '*', 'ORDER BY name');
					$tg_code = '';
					$group_state = 'closed';
					foreach($trackings as $t) {
						if($access['tracking']['ALL'] < 1 && $access['tracking'][$t['id']] < 1) continue;

						//Check for selected tracking
						$checked = '';
						foreach($_SESSION['show_leute_cols'] as $s) {
							if($s == 'MODULEtracking'.$t['id']) {
								$group_state = 'open';
								$checked = 'checked="checked"';
							}
						}

						$tracking_state = 'closed';
						$fr_code = '';
						foreach ($filters as $filter) {
							$filter_checked = '';
							foreach($_SESSION['show_leute_cols'] as $s) {
								if($s == 'MODULEtracking'.$t['id'].'f'.$filter['id']) {
									$group_state = 'open';
									$tracking_state = 'open';
									$filter_checked = 'checked="checked"';
								}
							}

							$fr_code .= '<li class="gtree gtree_1 gtree_state_open gtree_leaf" id="t'.$t['id'].'f'.$filter['id'].'">';
							$fr_code .= '<input type="checkbox" class="itemlist_chk" id="MODULEtracking'.$t['id'].'f'.$filter['id'].'" '.$filter_checked.' />';
							$fr_code .= '<label for="MODULEtracking'.$t['id'].'f'.$filter['id'].'">'.ko_html($filter['key']).'</label>';

							$fr_code .= '</li>';

						}


						$tg_code .= '<li class="gtree gtree_0 gtree_state_' . $tracking_state .' gtree_state_' . $tracking_state .'" id="t'.$t['id'].'">';
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
				$itemlist[$counter]['html'] = sprintf($titleSkeleton, getLL('module_tracking'));
				$itemlist[$counter++]['type'] = "html";

				$itemlist[$counter]['type'] = 'html';
				$itemlist[$counter++]['html'] = $c;
			}




			//Donations
			if(ko_module_installed('donations')) {
				if(!is_array($access['donations'])) ko_get_access('donations');
				$accounts = db_select_data('ko_donations_accounts', 'WHERE 1=1', '*', 'ORDER BY number ASC, `name` ASC');
				$c  = '<ul class="gtree gtree_NULL">';
				foreach($accounts as $account) {
					if($access['donations']['ALL'] < 1 && $access['donations'][$account['id']] < 1) continue;

					//Check for select ed account
					$checked = '';
					foreach($_SESSION['show_leute_cols'] as $s) if($s == 'MODULEdonationsa'.$account['id']) {
						$checked = 'checked="checked"';
					}

					$c .= '<li class="gtree gtree_0 gtree_state_open gtree_leaf" id="sa'.$account['id'].'">';
					$c .= '<input type="checkbox" class="itemlist_chk" id="MODULEdonationsa'.$account['id'].'" '.$checked.' />';
					$c .= '<label for="MODULEdonationsa'.$account['id'].'">'.ko_html($account['name']).'</label>';

					$c .= '</li>';
				}
				$years = db_select_distinct('ko_donations', "YEAR(`date`)", "ORDER BY `date` DESC");
				foreach($years as $year) {
					$tg_code = '';
					$group_state = 'closed';
					foreach($accounts as $account) {
						if($access['donations']['ALL'] < 1 && $access['donations'][$account['id']] < 1) continue;

						//Check for select ed account
						$checked = '';
						foreach($_SESSION['show_leute_cols'] as $s) if($s == 'MODULEdonations'.$year.$account['id']) {
							$group_state = 'open';
							$checked = 'checked="checked"';
						}

						$tg_code .= '<li class="gtree gtree_1 gtree_state_open gtree_leaf" id="t'.$account['id'].'">';
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
				$itemlist[$counter]['html'] = sprintf($titleSkeleton, getLL('module_donations'));
				$itemlist[$counter++]['type'] = "html";

				$itemlist[$counter]['type'] = 'html';
				$itemlist[$counter++]['html'] = $c;
			}


			//Donations reference number
			if(ko_module_installed('donations') && ko_module_installed('vesr')) {
				if(!is_array($access['donations'])) ko_get_access('donations');
				if(!is_array($access['crm'])) ko_get_access('crm');
				if(!isset($access['vesr'])) ko_get_access('vesr');

				if ($access['vesr'] > 0) {
					$accounts = db_select_data('ko_donations_accounts', 'WHERE 1=1', '*', 'ORDER BY number ASC, `name` ASC');
					$c  = '<ul class="gtree gtree_NULL">';
					if(ko_module_installed('crm')) {
						$crmProjects = db_select_data('ko_crm_projects', 'WHERE 1', '*', 'ORDER BY `number` ASC, `title` ASC');
					}

					foreach($accounts as $account) {
						if($access['donations']['ALL'] < 1 && $access['donations'][$account['id']] < 1) continue;

						$tg_code = '';
						$group_state = 'closed';
						foreach($crmProjects as $project) {
							if($access['crm']['ALL'] < 1 && $access['crm'][$project['id']] < 1) continue;

							//Check for selected project
							$checked = '';
							foreach($_SESSION['show_leute_cols'] as $s) if($s == 'MODULErefno_donations'.$account['id'].':'.$project['id']) {
								$group_state = 'open';
								$checked = 'checked="checked"';
							}

							$tg_code .= '<li class="gtree gtree_1 gtree_state_open gtree_leaf" id="trefno1'.$project['id'].'">';
							$tg_code .= '<input type="checkbox" class="itemlist_chk" id="MODULErefno_donations'.$account['id'].':'.$project['id'].'" '.$checked.' />';
							$tg_code .= '<label for="MODULErefno_donations'.$account['id'].':'.$project['id'].'">'.ko_html(trim($project['number'].' '.$project['title'])).'</label>';

							$tg_code .= '</li>';
						}
						if($tg_code != '') {
							//Check for selected account
							$checked = '';
							foreach($_SESSION['show_leute_cols'] as $s) if($s == 'MODULErefno_donations'.$account['id']) {
								$checked = 'checked="checked"';
							}

							$c .= '<li class="gtree gtree_0 gtree_state_'.$group_state.'" id="trefno'.$account['id'].'">';
							$c .= '<input type="checkbox" class="itemlist_chk" id="MODULErefno_donations'.$account['id'].'" '.$checked.' />';
							$c .= ko_html($account['name']);

							$c .= '<ul class="gtree gtree_1 gtree_state_'.$group_state.'" id="trefnou'.$account['id'].'">';
							$c .= $tg_code;
							$c .= '</ul>';

							$c .= '</li>';
						} else {
							//Check for selected account
							$checked = '';
							foreach($_SESSION['show_leute_cols'] as $s) if($s == 'MODULErefno_donations'.$account['id']) {
								$checked = 'checked="checked"';
							}

							$c .= '<li class="gtree gtree_0 gtree_state_'.$group_state.' gtree_leaf" id="trefno'.$account['id'].'">';
							$c .= '<input type="checkbox" class="itemlist_chk" id="MODULErefno_donations'.$account['id'].'" '.$checked.' />';
							$c .= '<label for="MODULErefno_donations'.$account['id'].'">'.ko_html($account['name']).'</label>';
							$c .= '</li>';
						}
					}
					$c .= '</ul>';

					//Add title above years (disabled)
					$itemlist[$counter]['html'] = sprintf($titleSkeleton, getLL('leute_itemlist_donations_refno'));
					$itemlist[$counter++]['type'] = "html";

					$itemlist[$counter]['type'] = 'html';
					$itemlist[$counter++]['html'] = $c;
				}
			}


			//CRM
			if(ko_module_installed('crm')) {
				if(!is_array($access['crm'])) ko_get_access('crm');

				// add title
				$itemlist[$counter]['html'] = sprintf($titleSkeleton, getLL('module_crm'));
				$itemlist[$counter++]['type'] = "html";

				$active = 0;
				foreach($_SESSION["show_leute_cols"] as $s) {
					if($s == 'MODULEcrm') {
						$active = 1;
					}
				}

				$itemlist[$counter]["prename"] = '';
				$itemlist[$counter]["name"] = getLL('module_crm');
				$itemlist[$counter]["aktiv"] = $active;
				$itemlist[$counter++]["value"] = 'MODULEcrm';
			}

			//subscription
			if(ko_module_installed('subscription') || $force) {
				ko_get_access('subscription');
				if($access['subscription']['MAX'] > 0) {
					$itemlist[$counter]['html'] = sprintf($titleSkeleton,getLL('module_subscription'));
					$itemlist[$counter++]['type'] = 'html';

					$forms = db_select_data('ko_subscription_forms','','id,title,form_group,cruser');
					foreach($forms as $form) {
						$formAccess = max($access['subscription']['ALL'],$access['subscription'][$form['form_group']]);
						if($formAccess > 1 || ($formAccess == 1 && $form['cruser'] == $_SESSION['ses_userid'])) {
							$value = 'MODULEsubscription_'.$form['id'];
							$itemlist[$counter]['prename'] = '';
							$itemlist[$counter]['name'] = $form['title'];
							$itemlist[$counter]['aktiv'] = in_array($value,$_SESSION['show_leute_cols']);
							$itemlist[$counter++]['value'] = $value;
						}
					}
				}
			}



			$itemlist_content['tpl_itemlist_select'] = $itemlist;


			//Alle Vorlagen auslesen
			$akt_value = implode(",", $_SESSION["show_leute_cols"]);
			$itemset = array_merge((array)ko_get_userpref('-1', '', 'leute_itemset', 'ORDER BY `key` ASC'), (array)ko_get_userpref($_SESSION["ses_userid"], "", "leute_itemset", 'ORDER BY `key` ASC'));
			$colNames = ko_get_leute_col_name(FALSE, TRUE);
			foreach($itemset as $i) {
				$cols = explode(',', $i['value']);
				$llCols = array();
				foreach($cols as $col) {
					$llCols[] = $colNames[$col];
				}
				$value = $i['user_id'] == '-1' ? '@G@'.$i['key'] : $i['key'];
				$itemselect_values[] = $value;
				$itemselect_output[] = $i['user_id'] == '-1' ? getLL('itemlist_global_short').' '.$i['key'] : $i['key'];
				$itemselect_title[] = implode(', ', $llCols);
				if($i["value"] == $akt_value) $itemselect_selected = $value;
			}
			$itemlist_content['tpl_itemlist_values'] = $itemselect_values;
			$itemlist_content['tpl_itemlist_output'] = $itemselect_output;
			$itemlist_content['tpl_itemlist_selected'] = $itemselect_selected;
			$itemlist_content['tpl_itemlist_title'] = $itemselect_title;
			if($_SESSION['show'] != 'geburtstagsliste') $itemlist_content['show_sort_cols'] = TRUE;
			$itemlist_content['sort_cols_checked'] = ko_get_userpref($_SESSION["ses_userid"], "sort_cols_leute") == "0" ? '' : 'checked="checked"';
			if($max_rights > 2) $itemlist_content['allow_global'] = TRUE;

			$submenu[$menucounter]["titel"] = getLL("submenu_leute_title_itemlist_spalten");

			$submenu[$menucounter]["items"][] = ko_get_menuitem_itemlist($itemlist_content);
		break;


		case "kg":
			if(ko_module_installed("kg") && $kg_all_rights > 0) {
				$found = TRUE;
				$submenu[$menucounter]["titel"] = getLL("submenu_leute_title_kg");
				if($kg_all_rights > 3) {
					$submenu[$menucounter]["items"][] = ko_get_menuitem("leute", "neue_kg");
				}
				$submenu[$menucounter]["items"][] = ko_get_menuitem("leute", "list_kg");

				$submenu[$menucounter]['items'][] = ko_get_menuitem_seperator();

				if($kg_all_rights > 1 && extension_loaded('gd')) {
					$submenu[$menucounter]["items"][] = ko_get_menuitem("leute", "chart_kg");
				}

				if($kg_all_rights > 1) {
					$c = '<select name="sel_xls_cols" class="input-sm form-control" onchange="jumpToUrl(\'?action=kg_xls_export&sel_xls_cols=\'+this.options[this.selectedIndex].value);">';
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
					$c .= '</select>';
					$submenu[$menucounter]['items'][] = ko_get_menuitem_html($c);
				}

			}
		break;


		case 'itemlist_spalten_kg':
			if(ko_module_installed('kg') && $kg_all_rights > 0) {
				$itemlist_content = array();
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
				$itemlist_content['tpl_itemlist_select'] = $itemlist;

				//Get all presets
				$akt_value = implode(',', $_SESSION['kota_show_cols_ko_kleingruppen']);
				$itemset = array_merge((array)ko_get_userpref('-1', '', 'leute_kg_itemset', 'ORDER BY `key` ASC'), (array)ko_get_userpref($_SESSION['ses_userid'], '', 'leute_kg_itemset', 'ORDER BY `key` ASC'));
				foreach($itemset as $i) {
					$value = $i['user_id'] == '-1' ? '@G@'.$i['key'] : $i['key'];
					$itemselect_values[] = $value;
					$itemselect_output[] = $i['user_id'] == '-1' ? getLL('itemlist_global_short').' '.$i['key'] : $i['key'];
					if($i['value'] == $akt_value) $itemselect_selected = $value;
				}
				$itemlist_content['tpl_itemlist_values'] = $itemselect_values;
				$itemlist_content['tpl_itemlist_output'] = $itemselect_output;
				$itemlist_content['tpl_itemlist_selected'] = $itemselect_selected;
				if($kg_all_rights > 2) $itemlist_content['allow_global'] = TRUE;

				$submenu[$menucounter]['titel'] = getLL('submenu_leute_title_itemlist_spalten_kg');

				$submenu[$menucounter]['items'][] = ko_get_menuitem_itemlist($itemlist_content);
			}
		break;



		default:
			if($menu) {
				$submenu[$menucounter] = submenu($menu, $state, 3, 'leute');
				$found = (is_array($submenu[$menucounter]));
			}
		}//switch(menu)

		//Plugins erlauben, Menuitems hinzuzufügen
		hook_submenu("leute", $menu, $submenu, $menucounter);

		// Clean up submenu
		if ($doClean) ko_clean_submenu('leute', $submenu[$menucounter], $found);

		//Submenu darstellen
		if($found) {
			$submenu[$menucounter]["mod"] = "leute";
			$submenu[$menucounter]["id"] = $menu;
			$submenu[$menucounter]["sesid"] = session_id();
			$submenu[$menucounter]["state"] = $state;

			if($display == 1) {
				$smarty->assign("sm", $submenu[$menucounter]);
				$smarty->assign("ko_path", $ko_path);
				$smarty->assign("help", ko_get_help("leute", "submenu_".$menu));
				$smarty->display("ko_submenu.tpl");
			} else if($display == 2) {
				$smarty->assign("sm", $submenu[$menucounter]);
				$smarty->assign('hideWrappingDiv', TRUE);
				$smarty->assign("ko_path", $ko_path);
				$return = "sm_leute_".$menu."@@@";
				$return .= $smarty->fetch("ko_submenu.tpl");
			}

			$smarty->clear_assign("help");
			$menucounter++;
		}
		else {
			if ($display == 2) {
				$return = "sm_leute_".$menu."@@@";
			}
		}

	}//foreach(namen as menu)


	if($display == 2) {
		return $return;
	} else if($display == 3) {
		return $submenu;
	}
}//submenu_leute()




function submenu_reservation($namen, $state, $display=1, $doClean = true) {
	global $ko_path, $smarty;
	global $RESITEM_IMAGE_WIDTH;
	global $ko_menu_akt, $access;
	global $my_submenu;

	$return = "";

	$all_rights = ko_get_access_all('res_admin', '', $max_rights);
	if($max_rights < 1) return FALSE;

	if (!is_array($namen)) $namen = explode(",", $namen);

	$menucounter = 0;
	foreach($namen as $menu) {
		$found = FALSE;

		switch($menu) {

		case "reservationen":
			$found = TRUE;
			$submenu[$menucounter]["titel"] = getLL("submenu_reservation_title_reservationen");
			if($max_rights > 1 && db_get_count('ko_resitem', 'id') > 0) {
				$submenu[$menucounter]["items"][] = ko_get_menuitem("reservation", "neue_reservation");
			}
			if($max_rights > 0) {
				$submenu[$menucounter]["items"][] = ko_get_menuitem("reservation", "liste");
				$submenu[$menucounter]["items"][] = ko_get_menuitem("reservation", "calendar");
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
				$submenu[$menucounter]["items"][] = ko_get_menuitem_link("reservation", "show_mod_res", sizeof($res_mod), '', (sizeof($res_mod) <= 0));
			}
			if($max_rights > 0 && ($_SESSION['ses_userid'] != ko_get_guest_id() || ko_get_setting('res_show_ical_links_to_guest'))) {
				$submenu[$menucounter]['items'][] = ko_get_menuitem('reservation', 'ical_links');
			}
		break;



		case "objekte":
			if($max_rights > 3) {
				$found = TRUE;
				$submenu[$menucounter]["titel"] = getLL("submenu_reservation_title_objekte");
				$submenu[$menucounter]["items"][] = ko_get_menuitem("reservation", "new_item");
				$submenu[$menucounter]["items"][] = ko_get_menuitem("reservation", "list_items");
			}
		break;




		case "export":
			$allowedExports = array();
			if($_SESSION['ses_userid'] != ko_get_guest_id()) $allowedExports = array('pdf', 'xls');
			elseif(ko_get_setting('res_allow_exports_for_guest')) $allowedExports = explode(',', ko_get_setting('res_allow_exports_for_guest'));
			else $allowedExports = array();

			if($max_rights > 0 && sizeof($allowedExports) > 0) {
				$found = TRUE;
				$submenu[$menucounter]["titel"] = getLL("submenu_reservation_title_export");

				//PDF exports for daily, weekly, monthly and yearly calendar
				if(in_array('pdf', $allowedExports)) {
					$c = '<select name="sel_pdf_export" class="input-sm form-control" onchange="jumpToUrl(\'?action=export_pdf&mode=\'+this.options[this.selectedIndex].value);">';
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
					$c .= '<option value="s-minus1:1">1/'.(date('Y')-1).'</option>';
					$c .= '<option value="s-minus1:7">2/'.(date('Y')-1).'</option>';
					$c .= '<option value="s-0:1">1/'.date('Y').'</option>';
					$c .= '<option value="s-0:7">2/'.date('Y').'</option>';
					$c .= '<option value="s-1:1">1/'.(date('Y')+1).'</option>';
					$c .= '<option value="s-1:7">2/'.(date('Y')+1).'</option>';
					$c .= '</optgroup>';
					$c .= '<optgroup label="'.getLL('year').'">';
					$c .= '<option value="y-minus1">'.getLL('time_last').'</option>';
					$c .= '<option value="y-0">'.getLL('time_current').'</option>';
					$c .= '<option value="y-1">'.getLL('time_next').'</option>';
					$c .= '<option value="y-2">'.getLL('time_after_next').'</option>';
					$c .= '</optgroup>';
					$c .= '</select>';
					$submenu[$menucounter]["items"][] = ko_get_menuitem_html($c);
				}


				//Excel-Export
				if($_SESSION['show'] == 'liste' && in_array('xls', $allowedExports)) {
					$submenu[$menucounter]['items'][] = ko_get_menuitem_seperator();

					$submenu[$menucounter]['form'] = TRUE;
					$submenu[$menucounter]['form_hidden_inputs'] = array(array('name' => 'action', 'value' => ''),
						array('name' => 'ids', 'value' => ''));

					$colitemsets = array_merge((array)ko_get_userpref('-1', '', 'ko_reservation_colitemset', 'ORDER by `key` ASC'), (array)ko_get_userpref($_SESSION['ses_userid'], '', 'ko_reservation_colitemset', 'ORDER by `key` ASC'));

					$c = '<select name="sel_xls_rows" class="input-sm form-control" onchange="set_ids_from_chk(this); jumpToUrl(\'?action=export_xls_reservation&sel_xls_rows=\'+this.options[this.selectedIndex].value+\'&chk=\'+document.getElementsByName(\'ids\')[0].value);">';
					$c .= '<option value="">'.getLL('submenu_reservation_xls_export').'</option>';
					$c .= '<option value="" disabled="disabled">----------</option>';

					$optgroups = array('alle' => getLL('all'), 'markierte' => getLL('selected'));
					if($_SESSION['ses_userid'] != ko_get_guest_id()) $optgroups['meine'] = getLL('mine');
					foreach($optgroups as $value => $label) {
						$c .= '<optgroup label="'.$label.'">';
						$c .= '<option value="'.$value.':shown">'.getLL('shown_columns').'</option>';
						$c .= '<option value="'.$value.':all">'.getLL('all_columns').'</option>';
						foreach($colitemsets as $colitemset) {
							$c .= '<option value="'.$value.':'.$colitemset['id'].'">'.$colitemset['key'].'</option>';
						}
						$c .= '</optgroup>';
					}

					$c .= '</select>';
					$submenu[$menucounter]["items"][] = ko_get_menuitem_html($c);
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

			$akt_von = ko_daten_parse_time_filter($_SESSION["filter_start"], 'today', FALSE);
			if ($akt_von == 'today') $akt_von = date("d.m.Y", time());
			else $akt_von = sql2datum($akt_von);
			$akt_bis = ko_daten_parse_time_filter($_SESSION["filter_ende"], '', FALSE);
			if ($akt_von == 'today') $akt_von = date("d.m.Y", time());
			else $akt_bis = sql2datum($akt_bis);

			$code =
"<script>
	$('body').on('keydown', '#res_filter_start-input, #res_filter_end-input', function(e) {
		if (e.which == 13) document.getElementById('submit_filter').click();
		else if (e.which == 84) {
			$(this).val($(this).val() + 't');
			e.preventDefault();
			return false;
		}
	});
</script>";

			$datePickerInput = array('type' => 'datepicker', 'value' => $akt_von, 'name' => 'res_filter[date1]', 'html_id' => 'res_filter_start', 'add_class' => $akt_von ? 'jsdate-active' : '');
			$datePickerInput['sibling'] = "res_filter_end";
			$smarty->assign('input', $datePickerInput);
			$code .= $smarty->fetch('ko_formular_elements.tmpl');
			$code .= '<div style="width:20px;margin:0px auto;border-left:1px solid rgb(204,204,204);border-right:1px solid rgb(204,204,204);text-align:center;">-</div>';
			$datePickerInput = array('type' => 'datepicker', 'value' => $akt_bis, 'name' => 'res_filter[date2]', 'html_id' => 'res_filter_end', 'add_class' => $akt_bis ? 'jsdate-active' : '');

			if(!empty($akt_von) && empty($akt_bis)) {
				$datePickerInput['viewDate'] = sql_datum($akt_von);
			}

			$smarty->assign('input', $datePickerInput);
			$code .= $smarty->fetch('ko_formular_elements.tmpl');
			$code .=
"<script>
	$('#res_filter_start-input-group, #res_filter_end-input-group').on('dp.change', function(date, oldDate) {
		$('#submit_filter').click();
	});
</script>";

			//Permanenter Filter bei Stufe 4
			if($max_rights > 3 && $_SESSION['show'] != 'show_mod_res') {
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

				$code .= '<div style="padding-top:5px;'.$style.'">';
				$code .= '<div class="checkbox">';
				$code .= '<label for="chk_perm_filter">';
				$code .= '<input type="checkbox" name="chk_perm_filter" id="chk_perm_filter" '.$checked.'>';
				//Bestehenden permanenten Filter anzeigen, falls eingeschaltet
				if($perm_filter_desc) {
					$code .= getLL('filter_use_globally_applied').'<br>'.$perm_filter_desc;
				} else {
					$code .= getLL('filter_use_globally');
				}
				$code .= '</label>';
				$code .= '</div>';
				$code .= "</div>";
			}//if(group_mod)


			$code .= '<button style="display: none;" type="submit" class="btn btn-sm btn-default" value="'.getLL("filter_refresh").'" name="submit_filter" id="submit_filter" onclick="set_action(\'submit_filter\', this);">' . getLL("filter_refresh") . '</button>';

			$submenu[$menucounter]["titel"] = getLL("submenu_reservation_title_filter");
			$submenu[$menucounter]['form'] = TRUE;
			$submenu[$menucounter]['form_hidden_inputs'] = array(array('name' => 'action', 'value' => ''),
				array('name' => 'ids', 'value' => ''));

			$submenu[$menucounter]['items'][] = ko_get_menuitem_link('reservation', 'set_filter_today', null, $ko_path."reservation/index.php?action=set_filter_today", false, getLL('filter_from_today'));

			$submenu[$menucounter]["items"][] = ko_get_menuitem_html($code, getLL("time_filter"));
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

			$absence_rights = $access['daten']['ABSENCE'];
			submenu_itemlist_absences($itemlist, $absence_rights, "reservation");

			$itemlist_content['tpl_itemlist_select'] = $itemlist;

			//Get all presets
			$akt_value = implode(",", $_SESSION["show_items"]);
			$itemset = array_merge((array)ko_get_userpref('-1', '', 'res_itemset', 'ORDER BY `key` ASC'), (array)ko_get_userpref($_SESSION['ses_userid'], '', 'res_itemset', 'ORDER BY `key` ASC'));
			foreach($itemset as $i) {
				$value = $i['user_id'] == '-1' ? '@G@'.$i['key'] : $i['key'];
				$itemselect_values[] = $value;
				$itemselect_output[] = $i['user_id'] == '-1' ? getLL('itemlist_global_short').' '.$i['key'] : $i['key'];
				if($i["value"] == $akt_value) $itemselect_selected = $value;
			}
			$itemlist_content['tpl_itemlist_values'] = $itemselect_values;
			$itemlist_content['tpl_itemlist_output'] = $itemselect_output;
			$itemlist_content['tpl_itemlist_selected'] = $itemselect_selected;
			if($max_rights > 3) $itemlist_content['allow_global'] = TRUE;

			$submenu[$menucounter]["titel"] = getLL("submenu_reservation_title_itemlist_objekte");

			$submenu[$menucounter]["items"][] = ko_get_menuitem_itemlist($itemlist_content);
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

							$escape_code = '';
							$escape_code .= '<b>'.$item['name'].'</b>';
							$escape_code .= '<br />';
							if($item['bild'] && file_exists($ko_path.$item['bild'])) {
								$preview = ko_pic_get_thumbnail($item['bild'], 800, FALSE);
								$thumb = ko_pic_get_thumbnail($item['bild'], $RESITEM_IMAGE_WIDTH, FALSE);

								$escape_code .= "<a href='#' onclick=".'"'."TINY.box.show({image:'$preview'});".' ">';
								$escape_code .= "<img src='$thumb' border='0' alt='".getLL('description')."' /><br />";
								$escape_code .= '</a>';
							}
							$line = array(); foreach(explode("\n", $item['beschreibung']) as $l) $line[] = trim($l);
							$escape_code .= implode('<br />', $line);
							//moderation
							$escape_code .= '<br /><br />' . getLL('moderated') . ': ' . (((int)$item['moderation'] > 0) ? getLL('yes') : getLL('no'));
							//linked items
							$linked_items = '';
							if($item['linked_items']) {
								foreach(explode(',', $item['linked_items']) as $litem) {
									$linked_items .= $all_items[$litem]['name'].', ';
								}
								$linked_items = substr($linked_items, 0, -2);
							}
							if($linked_items) $escape_code .= '<br /><br />'.getLL('res_linked_items').': '.$linked_items;

							$f_code .= str_replace('"', '\"', $escape_code) . '";}';
					}
					$f_code .= '}
				-->
				</script>';

			$submenu[$menucounter]["titel"] = getLL("submenu_reservation_title_objektbeschreibungen");

			$submenu[$menucounter]["items"][] = ko_get_menuitem_html($f_code);
		break;



		default:
			if($menu) {
				$submenu[$menucounter] = submenu($menu, $state, 3, 'reservation');
				$found = (is_array($submenu[$menucounter]));
			}
		}//switch(menu)

		//Plugins erlauben, Menuitems hinzuzufügen
		hook_submenu("reservation", $menu, $submenu, $menucounter);

		// Clean up submenu
		if ($doClean) ko_clean_submenu('reservation', $submenu[$menucounter], $found);

		if($found) {
			$submenu[$menucounter]["key"] = "reservation_".$menu;
			$submenu[$menucounter]["id"] = $menu;
			$submenu[$menucounter]["mod"] = "reservation";
			$submenu[$menucounter]["sesid"] = session_id();
			$submenu[$menucounter]["state"] = $state;

			if($display == 1) {
				$smarty->assign("sm", $submenu[$menucounter]);
				$smarty->assign("ko_path", $ko_path);
				$smarty->assign("help", ko_get_help("reservation", "submenu_".$menu));
				$smarty->display("ko_submenu.tpl");
			} else if($display == 2) {
				$smarty->assign("sm", $submenu[$menucounter]);
				$smarty->assign('hideWrappingDiv', TRUE);
				$smarty->assign("ko_path", $ko_path);
				$return = "sm_reservation_".$menu."@@@";
				$return .= $smarty->fetch("ko_submenu.tpl");
			}

			$smarty->clear_assign("help");
			$menucounter++;
		}
		else {
			if ($display == 2) {
				$return = "sm_reservation_".$menu."@@@";
			}
		}

	}//foreach(namen as menu)

	if($display == 2) {
		return $return;
	} else if($display == 3) {
		return $submenu;
	}
}//submenu_reservation()




function submenu_rota($namen, $state, $display=1, $doClean=TRUE) {
	global $ko_path, $smarty, $KOTA;
	global $ko_menu_akt, $access;
	global $my_submenu;

	$return = '';

	$all_rights = ko_get_access_all('rota_admin', '', $max_rights);
	if($max_rights < 1) return FALSE;
	ko_get_access('daten');

	if (!is_array($namen)) $namen = explode(',', $namen);

	$menucounter = 0;
	foreach($namen as $menu) {

		$found = 0;
		switch($menu) {

		case 'rota':
			$found = TRUE;
			$submenu[$menucounter]['titel'] = getLL('submenu_rota_title_rota');
			if($max_rights > 4) {
				$submenu[$menucounter]['items'][] = ko_get_menuitem('rota', 'new_team');
			}

			if($max_rights > 4) {
				$submenu[$menucounter]['items'][] = ko_get_menuitem('rota', 'list_teams');
			}

			$submenu[$menucounter]['items'][] = ko_get_menuitem('rota', 'schedule');
			$submenu[$menucounter]['items'][] = ko_get_menuitem('rota', 'planning');

			if($max_rights > 0) {
				$submenu[$menucounter]['items'][] = ko_get_menuitem('rota', 'ical_links');
			}
		break;


		case 'itemlist_teams':
			$itemlist_content = array();
			$found = TRUE;

			$counter = 0;
			$orderCol = ko_get_setting('rota_manual_ordering') ? 'sort' : 'name';
			$teams = db_select_data('ko_rota_teams', 'WHERE 1', '*', 'ORDER BY '.$orderCol.' ASC');
			foreach($teams as $d_i => $d) {
				if($all_rights > 0 || $access['rota'][$d_i] > 0) {
					$active = in_array($d_i, $_SESSION['rota_teams']) ? 1 : 0;

					if($d['rotatype'] == "day") {
						$itemlist[$counter]['name'] = $d['name'] . ' <sup>('.getLL('rota_day_short').')</sup> (' . getLL("rota_day_itemlist_schedule").')';
						$itemlist[$counter]['aktiv'] = $active;
						$itemlist[$counter]['value'] = $d_i;
						$itemlist[$counter++]['action'] = 'itemlistteams';

						if(!empty($d['eg_id']) && $_SESSION["show"] != "planning") {
							$active = in_array($d_i, $_SESSION['rota_teams_readonly']) ? 1 : 0;
							$itemlist[$counter]['name'] = $d['name'] . ' ('.getLL("rota_day_itemlist_display").')';
							$itemlist[$counter]['aktiv'] = $active;
							$itemlist[$counter]['value'] = $d_i . "_ro";
							$itemlist[$counter++]['action'] = 'itemlistteams';
						}
					} else {
						$itemlist[$counter]['name'] = $d['name'];
						$itemlist[$counter]['aktiv'] = $active;
						$itemlist[$counter]['value'] = $d_i;
						$itemlist[$counter++]['action'] = 'itemlistteams';
					}
				}
			}//foreach(teams)
			$itemlist_content['tpl_itemlist_select'] = $itemlist;

			//Get all presets
			$akt_value = implode(',', $_SESSION['rota_teams']);
			if(!empty($_SESSION['rota_teams_readonly'])) {
				$akt_value.= ",ro_" . implode(',ro_', $_SESSION['rota_teams_readonly']);
			}

			$itemset = array_merge((array)ko_get_userpref('-1', '', 'rota_itemset', 'ORDER by `key` ASC'), (array)ko_get_userpref($_SESSION['ses_userid'], '', 'rota_itemset', 'ORDER by `key` ASC'));
			$itemselect_values = $itemselect_output = array();
			foreach($itemset as $i) {
				$value = $i['user_id'] == '-1' ? '@G@'.$i['key'] : $i['key'];
				$itemselect_values[] = $value;
				$itemselect_output[] = $i['user_id'] == '-1' ? getLL('itemlist_global_short').' '.$i['key'] : $i['key'];
				if($i['value'] == $akt_value) $itemselect_selected = $value;
			}
			$itemlist_content['tpl_itemlist_values'] = $itemselect_values;
			$itemlist_content['tpl_itemlist_output'] = $itemselect_output;
			$itemlist_content['tpl_itemlist_selected'] = $itemselect_selected;
			if($max_rights > 3) $itemlist_content['allow_global'] = TRUE;
			$itemlist_content['action_suffix'] = 'teams';

			$submenu[$menucounter]['titel'] = getLL('submenu_rota_title_itemlist_teams');

			$submenu[$menucounter]['items'][] = ko_get_menuitem_itemlist($itemlist_content);
		break;


		case 'itemlist_eventgroups':
			$itemlist_content = array();
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
						$local_ids[] = $gid;
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
				if($i['rota'] == 0 && db_get_count('ko_event', 'id', "AND `eventgruppen_id` = '$i_i' AND `rota` = '1'") == 0) continue;
				$itemlist[$counter]['name'] = ko_html($i['name']);
				$itemlist[$counter]['prename'] = '<span style="margin-right:2px;background-color:#'.($i['farbe']?$i['farbe']:'fff').';">&emsp;</span>';
				$itemlist[$counter]['aktiv'] = in_array($i_i, $_SESSION['rota_egs']) ? 1 : 0;
				$itemlist[$counter]['value'] = $i_i;
				$itemlist[$counter++]['action'] = 'itemlistegs';
			}//foreach(groups)

			$itemlist_content['tpl_itemlist_select'] = $itemlist;

			$room_filter = $KOTA['ko_event']['room']["form"];
			$room_filter['name'] = "submenu_room_filter";
			$room_filter['value'] = $_SESSION['daten_room_filter'];
			$room_filter['type'] = "select";
			$localSmarty = clone($smarty);
			$localSmarty->assign('input', $room_filter);
			$room_filter_code = $localSmarty->fetch('ko_formular_elements.tmpl');
			$room_filter_code.= "<script>
				$('select[name=\'submenu_room_filter\'').on('change', function() {
					sendReq('../rota/inc/ajax.php', ['action','id','sesid'], ['itemlistroom', $(this).val(),'".session_id()."'], do_element);
				});
			</script>";

			$itemlist_content['room_filter'] = "<br />" . getLL('daten_location')  . ":<br />" . $room_filter_code;

			if(ko_module_installed("taxonomy") && $access['taxonomy']['MAX'] >= 1) {
				$taxonomy_filter = ko_taxonomy_form_field("", "ko_event", explode(",", $_SESSION['daten_taxonomy_filter']));
				$taxonomy_filter['name'] = "submenu_taxonomy_filter";
				$taxonomy_filter['allowParentselect'] = TRUE;
				unset($taxonomy_filter['ajaxHandler']['actions']['insert']);
				$localSmarty = clone($smarty);
				$localSmarty->assign('input', $taxonomy_filter);
				$taxonomy_filter_code = $localSmarty->fetch('ko_formular_elements.tmpl');
				$taxonomy_filter_code.= "<script>
					$('#submenu_taxonomy_filter').on('change', function() {
						sendReq('../rota/inc/ajax.php', ['action','id','sesid'], ['itemlisttaxonomy', $(this).val(),'".session_id()."'], do_element);
					});
				</script>";

				$taxonomy_filter_label = getLL('submenu_daten_filter_taxonomy_label');
				$itemlist_content['taxonomy_filter'] = "<br />" . $taxonomy_filter_label  . ":<br />" . $taxonomy_filter_code;
			}

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
			$itemlist_content['tpl_itemlist_values'] = $itemselect_values;
			$itemlist_content['tpl_itemlist_output'] = $itemselect_output;
			$itemlist_content['tpl_itemlist_selected'] = $itemselect_selected;
			if($access['daten']['MAX'] > 3) $itemlist_content['allow_global'] = TRUE;
			$itemlist_content['action_suffix'] = 'egs';

			$submenu[$menucounter]['titel'] = getLL('submenu_daten_title_itemlist_termingruppen');

			$submenu[$menucounter]['items'][] = ko_get_menuitem_itemlist($itemlist_content);
		break;

		default:
			if($menu) {
				$submenu[$menucounter] = submenu($menu, $state, 3, 'rota');
				$found = (is_array($submenu[$menucounter]));
			}
		}//switch(menu)

		//Plugins erlauben, Menuitems hinzuzufügen
		hook_submenu('rota', $menu, $submenu, $menucounter);

		// Clean up submenu
		if($doClean) ko_clean_submenu('rota', $submenu[$menucounter], $found);

		if($found) {
			$submenu[$menucounter]['key'] = 'rota_'.$menu;
			$submenu[$menucounter]['id'] = $menu;
			$submenu[$menucounter]['mod'] = 'rota';
			$submenu[$menucounter]['sesid'] = session_id();
			$submenu[$menucounter]['state'] = $state;

			if($display == 1) {
				$smarty->assign('sm', $submenu[$menucounter]);
				$smarty->assign('ko_path', $ko_path);
				$smarty->assign('help', ko_get_help('rota', 'submenu_'.$menu));
				$smarty->display('ko_submenu.tpl');
			} else if($display == 2) {
				$smarty->assign('sm', $submenu[$menucounter]);
				$smarty->assign('hideWrappingDiv', TRUE);
				$smarty->assign("ko_path", $ko_path);
				$smarty->assign("help", ko_get_help("rota", "submenu_".$menu));
				$return = 'sm_rota_'.$menu.'@@@';
				$return .= $smarty->fetch('ko_submenu.tpl');
			}

			$smarty->clear_assign("help");
			$menucounter++;
		}
		else {
			if ($display == 2) {
				$return = "sm_rota_".$menu."@@@";
			}
		}

	}//foreach(namen as menu)

	if($display == 2) {
		return $return;
	} else if($display == 3) {
		return $submenu;
	}
}//submenu_rota()



function submenu_admin($namen, $state, $display=1, $doClean=TRUE) {
	global $ko_path, $smarty, $access;
	global $my_submenu;

	$return = "";

	$all_rights = ko_get_access_all('admin', '', $max_rights);
	$all_rights_vesr = ko_get_access_all('vesr');
	if($all_rights < 1 && $all_rights_vesr < 1) return FALSE;

	if(!is_array($namen)) {
		$namen = explode(',', $namen);
	}
	$menucounter = 0;
	foreach($namen as $menu) {
		$found = FALSE;

		switch($menu) {

		case "logins":
			if($all_rights > 4) {
				$found = TRUE;
				$submenu[$menucounter]["titel"] = getLL("submenu_admin_title_logins");

				$submenu[$menucounter]["items"][] = ko_get_menuitem("admin", "set_new_login");
				$submenu[$menucounter]["items"][] = ko_get_menuitem("admin", "set_show_logins");
				$submenu[$menucounter]["items"][] = ko_get_menuitem_seperator();
				$submenu[$menucounter]["items"][] = ko_get_menuitem("admin", "set_new_admingroup");
				$submenu[$menucounter]["items"][] = ko_get_menuitem("admin", "set_show_admingroups");
			}
		break;


		case 'news':
			if($all_rights > 1) {
				$found = TRUE;
				$submenu[$menucounter]['titel'] = getLL('submenu_admin_title_news');
				$submenu[$menucounter]['items'][] = ko_get_menuitem('admin', 'new_news');
				$submenu[$menucounter]['items'][] = ko_get_menuitem('admin', 'list_news');
			}
		break;


		case "logs":
			if($all_rights > 3 || ($all_rights > 0 && ko_module_installed('sms'))) {
				$found = TRUE;
				$submenu[$menucounter]["titel"] = getLL("submenu_admin_title_logs");
			}

			if($all_rights > 3) {
				$submenu[$menucounter]["items"][] = ko_get_menuitem("admin", "show_logs");
			}
			if(ko_module_installed('sms')) {
				$submenu[$menucounter]['items'][] = ko_get_menuitem('admin', 'show_sms_log');
			}

			if(ko_module_installed('telegram')) {
				$submenu[$menucounter]['items'][] = ko_get_menuitem('admin', 'show_telegram_log');
			}
		break;


		case "filter":
			if(( $_SESSION['show'] == 'show_logs' && $all_rights < 4) ||
				($_SESSION['show'] == 'show_sms_log' && $all_rights < 1) ||
				($_SESSION['show'] == 'show_telegram_log' && $all_rights < 1) ||
				!in_array($_SESSION['show'], array('show_logs', 'show_sms_log', 'show_telegram_log'))
			) break;

			$found = TRUE;
			//Typ
			$code_typ  = '<select class="input-sm form-control" name="sel_log_filter_type" onchange="jumpToUrl(\'?action=submit_log_filter&amp;set_log_filter=\'+this.options[this.selectedIndex].value);">';
			$code_typ .= '<option value=""></option>';
			//Don't show types created only be root
			$where = $_SESSION['ses_userid'] == ko_get_root_id() ? '' : "WHERE `user_id` != '".ko_get_root_id()."'";
			$rows = db_select_distinct('ko_log', 'type', 'ORDER BY `type` ASC', $where);
			foreach($rows as $type) {
				$sel = ($_SESSION["log_type"] == $type) ? 'selected="selected"' : "";
				$code_typ .= '<option value="'.$type.'" '.$sel.'>'.$type.'</option>';
			}
			$code_typ .= '</select>';

			//User
			ko_get_logins($logins);
			$code_user .= '<select class="input-sm form-control" name="sel_log_filter_user" onchange="jumpToUrl(\'?action=submit_user_filter&amp;set_user_filter=\'+this.options[this.selectedIndex].value);">';
			$code_user .= '	<option value=""></option>';
			foreach($logins as $id => $login) {
				$sel = ($_SESSION["log_user"] == $id) ? 'selected="selected"' : "";
				if($login['login'] && ($login['login'] != 'root' || $_SESSION['ses_username'] == 'root')) {
					$code_user .= '<option value="'.$id.'" '.$sel.'>'.$login['login']." ($id)".'</option>';
				}
			}
			$code_user .= '</select>';

			//Zeit
			$code_zeit = '<select class="input-sm form-control" name="sel_log_filter_time" size="0" onchange="jumpToUrl(\'?action=submit_time_filter&amp;set_time_filter=\'+this.options[this.selectedIndex].value);">';
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
			$code_zeit .= '</select>';


			$submenu[$menucounter]["titel"] = getLL("submenu_admin_title_filter");
			//Only show for logs but not for sms logs
			if($_SESSION['show'] == 'show_logs') {
				$submenu[$menucounter]["items"][] = ko_get_menuitem_html($code_typ, getLL("admin_logfilter_type"));
			}
			if($_SESSION['show'] == 'show_logs' || $all_rights > 3) {
				$submenu[$menucounter]["items"][] = ko_get_menuitem_html($code_user, getLL("admin_logfilter_user"));
			}
			$submenu[$menucounter]["items"][] = ko_get_menuitem_html($code_zeit, getLL("admin_logfilter_time"));

			//Hide Guest
			if($_SESSION['show'] == 'show_logs') {
				$submenu[$menucounter]["items"][] = ko_get_menuitem_html('<input type="checkbox" name="chk_hide_guest" id="chk_hide_guest" '.($_SESSION["logs_hide_guest"]?'checked="checked"':"").' onclick="jumpToUrl(\'?action=submit_hide_guest&amp;logs_hide_status=\'+this.checked);" /><label for="chk_hide_guest">'.getLL("admin_logfilter_hide_guest").'</label>');

				//Root: Guest-Einträge löschen
				if($_SESSION["ses_username"] == "root") {
					$submenu[$menucounter]["items"][] = ko_get_menuitem_html('<p align="center"><input type="button" value="'.getLL("admin_logfilter_del_guest").'" onclick="jumpToUrl(\'?action=submit_clear_guest\');" /></p>');
				}
			}
		break;

		case "presets":
			if($all_rights > 1) {
				$found = TRUE;
				$submenu[$menucounter]["titel"] = getLL("submenu_admin_title_presets");

				$submenu[$menucounter]["items"][] = ko_get_menuitem("admin", "new_label");
				$submenu[$menucounter]["items"][] = ko_get_menuitem("admin", "list_labels");
				$submenu[$menucounter]["items"][] = ko_get_menuitem_seperator();
				$submenu[$menucounter]["items"][] = ko_get_menuitem("admin", "set_leute_pdf_new");
				$submenu[$menucounter]["items"][] = ko_get_menuitem("admin", "set_leute_pdf");
				$submenu[$menucounter]["items"][] = ko_get_menuitem_seperator();
				$submenu[$menucounter]["items"][] = ko_get_menuitem("admin", "new_detailed_person_export");
				$submenu[$menucounter]["items"][] = ko_get_menuitem("admin", "list_detailed_person_exports");
			}
		break;

		case "vesr":
			if (ko_module_installed('vesr') && $all_rights_vesr > 0) {
				$found = TRUE;
				$submenu[$menucounter]["titel"] = getLL("submenu_admin_title_vesr");
				$submenu[$menucounter]["items"][] = ko_get_menuitem("admin", "vesr_import");
				$submenu[$menucounter]["items"][] = ko_get_menuitem("admin", "vesr_archive");

				if($all_rights_vesr > 1) {
					$submenu[$menucounter]["items"][] = ko_get_menuitem("admin", "vesr_settings");
				}
			}
		break;

		case "cloud_printing":
			if ($max_rights > 4) {
				$found = TRUE;
				$submenu[$menucounter]["titel"] = getLL("submenu_admin_title_cloud_printing");
				$submenu[$menucounter]["items"][] = ko_get_menuitem("admin", "google_cloud_printers");
				if(ko_get_setting('qz_tray_enable')) {
					$submenu[$menucounter]["items"][] = ko_get_menuitem("admin", "qz_tray_printers");
				}
			}
			break;

		case "pubkey":

			if($_SESSION['ses_userid'] == ko_get_root_id()) {
				$found = TRUE;
				$submenu[$menucounter]['titel'] = getLL('submenu_admin_title_pubkey');
				$submenu[$menucounter]['items'][] = ko_get_menuitem('admin','show_pubkey');
			}
			break;

		default:
			if($menu) {
				$submenu[$menucounter] = submenu($menu, $state, 3, 'admin');
				$found = (is_array($submenu[$menucounter]));
			}
		}//switch(menu)

		//Plugins erlauben, Menuitems hinzuzufügen
		hook_submenu("admin", $menu, $submenu, $menucounter);

		// Clean up submenu
		if($doClean) ko_clean_submenu('admin', $submenu[$menucounter], $found);

		if($found) {
			$submenu[$menucounter]["key"] = "admin_".$menu;
			$submenu[$menucounter]["id"] = $menu;
			$submenu[$menucounter]["mod"] = "admin";
			$submenu[$menucounter]["sesid"] = session_id();
			$submenu[$menucounter]["state"] = $state;

			if($display == 1) {
				$smarty->assign("sm", $submenu[$menucounter]);
				$smarty->assign("ko_path", $ko_path);
				$smarty->assign("help", ko_get_help("admin", "submenu_".$menu));
				$smarty->display("ko_submenu.tpl");
			} else if($display == 2) {
				$smarty->assign("sm", $submenu[$menucounter]);
				$smarty->assign('hideWrappingDiv', TRUE);
				$smarty->assign("ko_path", $ko_path);
				$smarty->assign("help", ko_get_help("admin", "submenu_".$menu));
				$return = "sm_admin_".$menu."@@@";
				$return .= $smarty->fetch("ko_submenu.tpl");
			}

			$smarty->clear_assign("help");
			$menucounter++;
		}
		else {
			if ($display == 2) {
				$return = "sm_admin_".$menu."@@@";
			}
		}


	}//foreach(namen as menu)

	if($display == 2) {
		return $return;
	} else if($display == 3) {
		return $submenu;
	}
}//submenu_admin()





function submenu_tools($namen, $state, $display=1, $doClean = true) {
	global $ko_path, $smarty;
	global $my_submenu;

	$return = "";

	if(!ko_module_installed("tools")) return FALSE;

	if (!is_array($namen)) {
		$namen = explode(",", $namen);
	}

	$menucounter = 0;
	foreach($namen as $menu) {
		$found = FALSE;


		switch($menu) {

		case "submenus":
			$found = TRUE;
			$submenu[$menucounter]["titel"] = getLL("submenu_tools_title_submenus");
			$submenu[$menucounter]['items'][] = ko_get_menuitem('tools', 'misc');
			$submenu[$menucounter]["items"][] = ko_get_menuitem("tools", "testmail");
			$submenu[$menucounter]["items"][] = ko_get_menuitem("tools", "mailing_mails");
			$submenu[$menucounter]["items"][] = ko_get_menuitem("tools", "kota_fields");
			$submenu[$menucounter]['items'][] = ko_get_menuitem('tools', 'list_updates');
		break;

		case "leute-db":
			$found = TRUE;
			$submenu[$menucounter]["titel"] = getLL("submenu_tools_title_leute-db");
			$submenu[$menucounter]["items"][] = ko_get_menuitem("tools", "show_leute_db");
			$submenu[$menucounter]["items"][] = ko_get_menuitem("tools", "show_familie_db");
		break;

		case "ldap":
			if(ko_do_ldap()) {
				$found = TRUE;
				$submenu[$menucounter]["titel"] = getLL("submenu_tools_title_ldap");
				$submenu[$menucounter]["items"][] = ko_get_menuitem("tools", "list_ldap_logins");
				$submenu[$menucounter]["items"][] = ko_get_menuitem("tools", "ldap_export");
			}
		break;

		case "locallang":
			$found = TRUE;
			$submenu[$menucounter]["titel"] = getLL("submenu_tools_title_locallang");
			$submenu[$menucounter]["items"][] = ko_get_menuitem("tools", "ll_overview");
		break;

		case "plugins":
			$found = TRUE;
			$submenu[$menucounter]["titel"] = getLL("submenu_tools_title_plugins");
			$submenu[$menucounter]["items"][] = ko_get_menuitem("tools", "plugins_list");
		break;

		case 'scheduler':
			$found = TRUE;
			$submenu[$menucounter]['titel'] = getLL('submenu_tools_title_scheduler');
			$submenu[$menucounter]['items'][] = ko_get_menuitem('tools', 'scheduler_add');
			$submenu[$menucounter]['items'][] = ko_get_menuitem('tools', 'scheduler_list');
		break;

		case 'typo3':
			$found = TRUE;
			$submenu[$menucounter]['titel'] = getLL('submenu_tools_title_typo3');
			$submenu[$menucounter]['items'][] = ko_get_menuitem('tools', 'typo3_connection');
		break;

		case 'payments':
			if(mysqli_query(db_get_link(),'SELECT 1 FROM ko_vesr UNION SELECT 1 FROM ko_leute LIMIT 1')->num_rows) {
				$found = TRUE;
				$submenu[$menucounter]['titel'] = getLL('submenu_tools_title_payments');
				$submenu[$menucounter]['items'][] = ko_get_menuitem('tools','payments_list');
			}
		break;

		default:
			if($menu) {
				$submenu[$menucounter] = submenu($menu, $state, 3, 'tools');
				$found = (is_array($submenu[$menucounter]));
			}
		}//switch(menu)

		//Plugins erlauben, Menuitems hinzuzufügen
		hook_submenu("tools", $menu, $submenu, $menucounter);

		// Clean up submenu
		if ($doClean) ko_clean_submenu('tools', $submenu[$menucounter], $found);

		if($found) {
			$submenu[$menucounter]["key"] = "tools_".$menu;
			$submenu[$menucounter]["id"] = $menu;
			$submenu[$menucounter]["mod"] = "tools";
			$submenu[$menucounter]["sesid"] = session_id();
			$submenu[$menucounter]["state"] = $state;

			if($display == 1) {
				$smarty->assign("sm", $submenu[$menucounter]);
				$smarty->assign("ko_path", $ko_path);
				$smarty->assign("help", ko_get_help("tools", "submenu_".$menu));
				$smarty->display("ko_submenu.tpl");
			} else if($display == 2) {
				$smarty->assign("sm", $submenu[$menucounter]);
				$smarty->assign('hideWrappingDiv', TRUE);
				$smarty->assign("ko_path", $ko_path);
				$return = "sm_tools_".$menu."@@@";
				$return .= $smarty->fetch("ko_submenu.tpl");
			}

			$smarty->clear_assign("help");
			$menucounter++;
		}
		else {
			if ($display == 2) {
				$return = "sm_tools_".$menu."@@@";
			}
		}

	}//foreach(namen as menu)

	if($display == 2) {
		return $return;
	} else if($display == 3) {
		return $submenu;
	}
}//submenu_tools()




function submenu_taxonomy($namen, $state, $display=1, $doClean = true) {
	global $ko_path, $smarty;
	global $my_submenu;

	$return = "";

	if(!ko_module_installed("taxonomy")) return FALSE;

	$all_rights = ko_get_access_all('taxonomy_admin', '', $max_rights);
	if($max_rights < 1) return FALSE;

	if (!is_array($namen)) {
		$namen = explode(",", $namen);
	}

	$menucounter = 0;
	foreach($namen as $menu) {
		$found = FALSE;


		switch($menu) {

			case "taxonomy":
				$found = TRUE;
				$submenu[$menucounter]["titel"] = getLL("submenu_taxonomy_title_taxonomy");
				if ($all_rights > 1) {
					$submenu[$menucounter]['items'][] = ko_get_menuitem("taxonomy", "new_term");
				}

				if ($all_rights >= 1) {
					$submenu[$menucounter]["items"][] = ko_get_menuitem("taxonomy", "list_terms");
				}
				break;

			default:
				if($menu) {
					$submenu[$menucounter] = submenu($menu, $state, 3, 'taxonomy');
					$found = (is_array($submenu[$menucounter]));
				}
		}//switch(menu)

		//Plugins erlauben, Menuitems hinzuzufügen
		hook_submenu("taxonomy", $menu, $submenu, $menucounter);

		// Clean up submenu
		if ($doClean) ko_clean_submenu('taxonomy', $submenu[$menucounter], $found);

		if($found) {
			$submenu[$menucounter]["key"] = "taxonomy_".$menu;
			$submenu[$menucounter]["id"] = $menu;
			$submenu[$menucounter]["mod"] = "taxonomy";
			$submenu[$menucounter]["sesid"] = session_id();
			$submenu[$menucounter]["state"] = $state;

			if($display == 1) {
				$smarty->assign("sm", $submenu[$menucounter]);
				$smarty->assign("ko_path", $ko_path);
				$smarty->assign("help", ko_get_help("taxonomy", "submenu_".$menu));
				$smarty->display("ko_submenu.tpl");
			} else if($display == 2) {
				$smarty->assign("sm", $submenu[$menucounter]);
				$smarty->assign('hideWrappingDiv', TRUE);
				$smarty->assign("ko_path", $ko_path);
				$return = "sm_taxonomy_".$menu."@@@";
				$return .= $smarty->fetch("ko_submenu.tpl");
			}

			$smarty->clear_assign("help");
			$menucounter++;
		}
		else {
			if ($display == 2) {
				$return = "sm_taxonomy_".$menu."@@@";
			}
		}

	}//foreach(namen as menu)

	if($display == 2) {
		return $return;
	} else if($display == 3) {
		return $submenu;
	}
}//submenu_taxonomy()




function submenu_groups($namen, $state, $display=1, $doClean=TRUE) {
	global $ko_path, $smarty;
	global $my_submenu;

	$return = "";

	if(!ko_module_installed("groups")) return FALSE;
	$all_rights = ko_get_access_all('groups_admin', '', $max_rights);

	if(!is_array($namen)) {
		$namen = explode(',', $namen);
	}
	$menucounter = 0;
	foreach($namen as $menu) {
		$found = FALSE;

		switch($menu) {

		case "groups":
			$found = TRUE;
			$submenu[$menucounter]["titel"] = getLL("submenu_groups_title_groups");
			if($max_rights > 2) {
				$submenu[$menucounter]["items"][] = ko_get_menuitem("groups", "new_group");
			}
			$submenu[$menucounter]["items"][] = ko_get_menuitem("groups", "list_groups");
			if($all_rights > 2) {
				$submenu[$menucounter]["items"][] = ko_get_menuitem("groups", "list_datafields");
			}
		break;


		case "roles":
			$found = TRUE;
			$submenu[$menucounter]["titel"] = getLL("submenu_groups_title_roles");
			if($all_rights > 2) {
				$submenu[$menucounter]["items"][] = ko_get_menuitem("groups", "new_role");
			}
			$submenu[$menucounter]["items"][] = ko_get_menuitem("groups", "list_roles");
		break;


		case "rights":
			if($all_rights > 2) {
				$found = TRUE;
				$submenu[$menucounter]["titel"] = getLL("submenu_groups_title_rights");
				$submenu[$menucounter]["items"][] = ko_get_menuitem("groups", "list_rights");
			}
		break;


		case 'dffilter':
			if($all_rights > 2) {
				$found = TRUE;
				$submenu[$menucounter]['titel'] = getLL('submenu_groups_title_dffilter');

				$checked = $_SESSION['groups_show_hidden_datafields'] ? 'checked' : '';
				$code =
'<div class="checkbox">
	<label>
		<input type="checkbox" name="groups_dffilter" id="chk_groups_dffilter" ' . $checked . ' />
		' . getLL('submenu_groups_dffilter_yes') . '
	</label>
</div>';
				$submenu[$menucounter]['items'][] = ko_get_menuitem_html($code, getLL('submenu_groups_dffilter'));
			}
		break;



		case 'export':
			$found = TRUE;
			$submenu[$menucounter]['titel'] = getLL('submenu_groups_title_export');
			$submenu[$menucounter]['form'] = TRUE;
			$submenu[$menucounter]['form_hidden_inputs'] = array(array('name' => 'ids', 'value' => ''));

			$submenu[$menucounter]['items'][] = ko_get_menuitem('groups', 'exportxls');

			$pdf_layouts = db_select_data('ko_pdf_layout', "WHERE `type` = 'groups'", 'id, name', 'ORDER BY `name` ASC');
			if(sizeof($pdf_layouts) > 0) {
				$html = '<select name="pdf_layout_id" class="input-sm form-control" onchange="set_ids_from_chk(this); jumpToUrl(\'?action=export_pdf&layout_id=\'+this.options[this.selectedIndex].value+\'&gid=\'+document.getElementsByName(\'ids\')[0].value);">';
				$html .= '<option value=""></option>';
				foreach($pdf_layouts as $l) {
					$html .= '<option value="'.$l['id'].'">'.ko_html($l['name']).'</option>';
				}
				$html .= '</select>';

				$submenu[$menucounter]['items'][] = ko_get_menuitem_seperator();
				$submenu[$menucounter]['items'][] = ko_get_menuitem_html($html, getLL('submenu_groups_export_pdf'));
			}

			$presets = array_merge((array)ko_get_userpref('-1', '', "leute_itemset"), (array)ko_get_userpref($_SESSION['ses_userid'], '', "leute_itemset"));
			$options = array();
			$colNames = ko_get_leute_col_name(FALSE, TRUE);
			foreach($presets as $preset) {
				$cols = explode(',', $preset['value']);
				$llCols = array();
				foreach($cols as $col) {
					$llCols[] = $colNames[$col];
				}
				$options[] = '<option value="'.$preset['id'].'" title="'.implode(', ', $llCols).'">'.($preset['user_id'] == '-1' ? '[G] ' : '').$preset['key'].'</option>';
			}
			$html = '<select name="leute_col_presets" class="input-sm form-control" onchange="set_ids_from_chk(this); jumpToUrl(\'?action=export_xls_with_people&preset_id=\'+this.options[this.selectedIndex].value+\'&ids=\'+document.getElementsByName(\'ids\')[0].value);">';
			$html .= '<option value="">'.getLL('submenu_groups_export_xls_with_people_col_preset').'</option>';
			$html .= '<option value="default">'.getLL('submenu_groups_export_xls_with_people_col_default').'</option>';
			if(sizeof($options) > 0) $html .= implode('', $options);
			$html .= '</select>';

			$submenu[$menucounter]['items'][] = ko_get_menuitem_seperator();
			$submenu[$menucounter]['items'][] = ko_get_menuitem_html($html, getLL('submenu_groups_export_xls_with_people'));
		break;


		default:
			if($menu) {
				$submenu[$menucounter] = submenu($menu, $state, 3, 'groups');
				$found = (is_array($submenu[$menucounter]));
			}
		}//switch(menu)

		//Plugins erlauben, Menuitems hinzuzufügen
		hook_submenu("groups", $menu, $submenu, $menucounter);

		// Clean up submenu
		if($doClean) ko_clean_submenu('groups', $submenu[$menucounter], $found);

		if($found) {
			$submenu[$menucounter]["key"] = "groups_".$menu;
			$submenu[$menucounter]["id"] = $menu;
			$submenu[$menucounter]["mod"] = "groups";
			$submenu[$menucounter]["sesid"] = session_id();
			$submenu[$menucounter]["state"] = $state;

			if($display == 1) {
				$smarty->assign("sm", $submenu[$menucounter]);
				$smarty->assign("ko_path", $ko_path);
				$smarty->assign("help", ko_get_help("groups", "submenu_".$menu));
				$smarty->display("ko_submenu.tpl");
			} else if($display == 2) {
				$smarty->assign("sm", $submenu[$menucounter]);
				$smarty->assign('hideWrappingDiv', TRUE);
				$smarty->assign("ko_path", $ko_path);
				$return = "sm_groups_".$menu."@@@";
				$return .= $smarty->fetch("ko_submenu.tpl");
			}

			$smarty->clear_assign("help");
			$menucounter++;
		}
		else {
			if ($display == 2) {
				$return = "sm_groups_".$menu."@@@";
			}
		}


	}//foreach(namen as menu)

	if($display == 2) {
		return $return;
	} else if($display == 3) {
		return $submenu;
	}
}//submenu_groups()








function submenu_donations($namen, $state, $display=1, $doClean = true) {
	global $ko_path, $smarty;
	global $ko_menu_akt, $access;
	global $my_submenu;

	$stateSaved = $state;
	$state = 'open';

	$return = "";

	$all_rights = ko_get_access_all('donations_admin', '', $max_rights);
	if($max_rights < 1) return FALSE;

	if (!is_array($namen)) {
		$namen = explode(",", $namen);
	}

	$menucounter = 0;
	foreach($namen as $menu) {
		$found = FALSE;

    switch($menu) {

		case "donations":
			$found = TRUE;
			$submenu[$menucounter]["titel"] = getLL("submenu_donations_title_donations");
			if($max_rights > 1 && db_get_count("ko_donations_accounts") > 0) {
				$submenu[$menucounter]["items"][] = ko_get_menuitem("donations", "new_donation");

				if(ko_get_setting('donations_use_promise')) {
					$submenu[$menucounter]["items"][] = ko_get_menuitem('donations', 'new_promise');
				}
			}
			$submenu[$menucounter]["items"][] = ko_get_menuitem("donations", "list_donations");
			//Donations mod
			$all_rights_leute = ko_get_access_all('leute_admin', '', $max_rights_leute);
			if($max_rights > 1 && $max_rights_leute > 1) {
				ko_get_donations_mod($modDonations);
				//For logins with edit access to only some addresses exclude those they don't have access to
				if($all_rights < 2) {
					if(!is_array($access['donations'])) ko_get_access('donations');
					foreach($modDonations as $aid => $a) {
						if($access['donations'][$a['_account_id']] < 2 || $a['_account_id'] < 1) unset($modDonations[$aid]);
					}
				}
				$nr_mod = sizeof($modDonations);
				$submenu[$menucounter]["items"][] = ko_get_menuitem_link('donations', 'list_donations_mod', $nr_mod, '', !($nr_mod>0));
			}
			if($max_rights > 1 && ko_get_setting('donations_use_repetition')) {
				$submenu[$menucounter]['items'][] = ko_get_menuitem_seperator();
				$submenu[$menucounter]['items'][] = ko_get_menuitem_link('donations', 'list_reoccuring_donations');
			}
			if($max_rights > 2) {
				$submenu[$menucounter]["items"][] = ko_get_menuitem_seperator();
				$submenu[$menucounter]["items"][] = ko_get_menuitem_link("donations", "merge");
			}
			if($max_rights > 0) {
				$submenu[$menucounter]["items"][] = ko_get_menuitem_seperator();
				$submenu[$menucounter]["items"][] = ko_get_menuitem_link("donations", "show_stats");
			}
		break;

		case "accounts":
			if($max_rights > 3) {
				$found = TRUE;
				$submenu[$menucounter]["titel"] = getLL("submenu_donations_title_accounts");
				$submenu[$menucounter]["items"][] = ko_get_menuitem_link("donations", "new_account");
				$submenu[$menucounter]["items"][] = ko_get_menuitem_link("donations", "list_accounts");
			}

			if($all_rights > 3) {
				$submenu[$menucounter]["items"][] = ko_get_menuitem_seperator();
				$submenu[$menucounter]['items'][] = ko_get_menuitem_link('donations', 'new_accountgroup');
				$submenu[$menucounter]['items'][] = ko_get_menuitem_link('donations', 'list_accountgroups');
			}
		break;

		case "itemlist_accounts":
			if($max_rights < 1) break;
			$found = TRUE;

			// prepare data for itemlist
			$globalMinRights = 4;
			$groupColumn = 'accountgroup_id';
			$table = 'ko_donations_accounts';
			$tableOrdering = "ORDER BY `number` ASC, name ASC";
			$sessionShowKey = 'show_donations_accounts';
			$sessionStatesKey = 'donations_accounts_group_states';
			$presetType = 'accounts_itemset';

			$groups = db_select_data('ko_donations_accountgroups', "WHERE 1", '*', 'ORDER BY `title` ASC');
			foreach ($groups as $gk => &$group) {
				if($access['donations']['ag'.$gk] < 1) unset($groups[$gk]);
			}
			foreach ($groups as $gk => &$group) {
				$group['_title'] = $group['title'];
				$group['_elements'] = db_select_data($table, "WHERE `{$groupColumn}` = '{$group['id']}'", "*", $tableOrdering);
				foreach ($group['_elements'] as $eid => &$element) {
					if($access['donations']['ALL'] < 1 && $access['donations'][$element['id']] < 1) unset($group['_elements'][$eid]);
					$element['_title'] = ko_html($element["number"])."&nbsp;".ko_html($element["name"]);
				}
			}

			$archivedAccounts = db_select_data('ko_donations_accounts', "WHERE `archived` = 1", '*', $tableOrdering);
			foreach($archivedAccounts as $aaid => $aa) {
				if($access['donations']['ALL'] < 1 && $access['donations'][$aa['id']] < 1) unset($archivedAccounts[$aaid]);
			}
			if (sizeof($archivedAccounts) > 0) {
				$archivedGroup = array('id' => '@@archived@@', '_title' => getLL('kota_ko_donations_accounts_archived'), '_elements' => $archivedAccounts);
				foreach ($archivedGroup['_elements'] as &$element) {
					$element['_title'] = ko_html($element["number"])."&nbsp;".ko_html($element["name"]);
				}
				$groups[] = $archivedGroup;
			}


			$noGroupElements = db_select_data($table, "WHERE `{$groupColumn}` = '0' AND `archived` = 0", "*", $tableOrdering);
			foreach ($noGroupElements as $eid => &$element) {
				if($access['donations']['ALL'] < 1 && $access['donations'][$element['id']] < 1) unset($noGroupElements[$eid]);
				$element['_title'] = ko_html($element["number"])."&nbsp;".ko_html($element["name"]);
			}

			// fill in itemlist
			ko_prepare_itemlist($itemlist_content, $groups, $noGroupElements, $sessionShowKey, $sessionStatesKey, $presetType, $globalMinRights, $max_rights);

			// create entry in submenu
			$submenu[$menucounter]["titel"] = getLL("submenu_donations_title_itemlist_accounts");
			$submenu[$menucounter]["items"][] = ko_get_menuitem_itemlist($itemlist_content);
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

			$code = '';
			$datePickerInput = array('type' => 'datepicker', 'value' => sql2datum($_SESSION["donations_filter"]["date1"]), 'name' => 'donations_filter[date1]', 'html_id' => 'donations_filter_date_1');
			$datePickerInput['sibling'] = "donations_filter_date_2";

			$smarty->assign('input', $datePickerInput);
			$code .= $smarty->fetch('ko_formular_elements.tmpl');
			$code .= '<div style="width:20px;margin:0px auto;border-left:1px solid rgb(204,204,204);border-right:1px solid rgb(204,204,204);text-align:center;">-</div>';
			$datePickerInput = array('type' => 'datepicker', 'value' => sql2datum($_SESSION["donations_filter"]["date2"]), 'name' => 'donations_filter[date2]', 'html_id' => 'donations_filter_date_2');
			$smarty->assign('input', $datePickerInput);
			$code .= $smarty->fetch('ko_formular_elements.tmpl');
			$pre  = $_SESSION["donations_filter"]["date1"] || $_SESSION["donations_filter"]["date2"] ? '<mark>' : "";
			$post = $_SESSION["donations_filter"]["date1"] || $_SESSION["donations_filter"]["date2"] ? '</mark>' : "";
			$submenu[$menucounter]['items'][] = ko_get_menuitem_html($code, $pre.getLL('kota_ko_donations_'.$date_field).':'.$post);

			//Leute-modul filter
			$code  = '<select class="input-sm form-control" name="donations_filter[leute]" onchange="$(\'#submit_donations_filter\').click();"><option value=""></option>';
			$filterset = array_merge((array)ko_get_userpref('-1', '', 'filterset', 'ORDER BY `key` ASC'), (array)ko_get_userpref($_SESSION['ses_userid'], '', 'filterset', 'ORDER BY `key` ASC'));
			foreach($filterset as $f) {
				$value = $f['user_id'] == '-1' ? '@G@'.$f['key'] : $f['key'];
				$desc = $f['user_id'] == '-1' ? getLL('itemlist_global_short').' '.$f['key'] : $f['key'];
				if($_SESSION["donations_filter"]["leute"] == $value) $sel = 'selected="selected"'; else $sel = "";
				$code .= '<option value="'.$value.'" '.$sel.'>'.$desc.'</option>';
			}
			$code .= '</select>';
			$pre  = $_SESSION["donations_filter"]["leute"] ? '<mark>' : "";
			$post = $_SESSION["donations_filter"]["leute"] ? '</mark>' : "";
			$submenu[$menucounter]["items"][] = ko_get_menuitem_html($code, $pre.getLL("donations_filter_leute").$post);

			//Person-search filter
			$code  = '<input class="input-sm form-control" type="text" name="donations_filter[personString]" value="'.htmlspecialchars($_SESSION["donations_filter"]["personString"], ENT_COMPAT | ENT_HTML401, 'ISO-8859-1').'" size="17">';

			$pre  = $_SESSION["donations_filter"]["personString"] ? '<mark>' : "";
			$post = $_SESSION["donations_filter"]["personString"] ? '</mark>' : "";
			$submenu[$menucounter]["items"][] = ko_get_menuitem_html($code, $pre.getLL("donations_filter_personstring").$post);

			//Display person filter
			if($_SESSION["donations_filter"]["person"]) {
				ko_get_person_by_id($_SESSION["donations_filter"]["person"], $fp, TRUE);
				$name = trim($fp['firm'].' '.$fp['vorname'].' '.$fp['nachname']);
				$submenu[$menucounter]["items"][] = ko_get_menuitem_html('<a href="index.php?action=clear_person_filter">'.$name.'&nbsp;<img src="'.$ko_path.'images/icon_trash.png" border="0" alt="del" /></a>', '<mark>'.getLL("donations_filter_person").'</mark>');
			}

			//Search for amount
			$code  = '<input class="input-sm form-control" type="text" name="donations_filter[amount]" value="'.htmlspecialchars($_SESSION['donations_filter']['amount'], ENT_COMPAT | ENT_HTML401, 'ISO-8859-1').'">';
			$help = ko_get_help('donations', 'submenu_filter_amount', array('ph' => 'l'));

			$pre  = $_SESSION['donations_filter']['amount'] ? '<mark>' : '';
			$post = $_SESSION['donations_filter']['amount'] ? '</mark>' : '';
			$post .= '&nbsp;' . ($help['show'] ? $help['link'] : '');
			$submenu[$menucounter]['items'][] = ko_get_menuitem_html($code, $pre.getLL('donations_filter_amount').$post);

			//Promise
			if (ko_get_setting('donations_use_promise')) {
				$checked = $_SESSION['donations_filter']['promise'] == 1 ? 'checked="checked"' : '';
				$code  = '<input type="checkbox" name="donations_filter[promise]" onchange="$(\'#submit_donations_filter\').click();" value="1" '.$checked.' />';
				$help = ko_get_help('donations', 'submenu_filter_promise', array('ph' => 'l'));

				$pre  = $_SESSION['donations_filter']['promise'] ? '<mark>' : '';
				$post = $_SESSION['donations_filter']['promise'] ? '</mark>' : '';
				$post .= '&nbsp;' . ($help['show'] ? $help['link'] : '');
				$submenu[$menucounter]['items'][] = ko_get_menuitem_html($code, $pre.getLL('donations_filter_promise').$post);
			}

			//Thanked
			$value = $_SESSION['donations_filter']['thanked'];
			$values = array('', 'no', 'yes');
			$descs = array('', getLL('no'), getLL('yes'));
			$code  = '<select class="input-sm form-control" name="donations_filter[thanked]" onchange="$(\'#submit_donations_filter\').click();">';
			foreach ($values as $k => $v) {
				$d = $descs[$k];
				$code .= '<option value="'.$v.'"'.($v==$value?' selected="selected"':'').'>'.$d.'</option>';
			}
			$code .= '</select>';

			$pre  = $_SESSION['donations_filter']['thanked'] ? '<mark>' : '';
			$post = $_SESSION['donations_filter']['thanked'] ? '</mark>' : '';
			$submenu[$menucounter]['items'][] = ko_get_menuitem_html($code, $pre.getLL('donations_filter_thanked').$post);

			// Submit
			$html = '<div class="btn-field">';
			$html .= '<button class="btn btn-sm btn-primary" type="submit" name="submit_donations_filter" id="submit_donations_filter" value="'.getLL("donations_filter_submit").'" onclick="set_action(\'set_filter\', this);this.submit;" style="margin: 5px;">';
			$html .= getLL("donations_filter_submit");
			$html .= '</button>';
			// Clear
			$html .= '<button class="btn btn-sm btn-danger" type="submit" name="clear_donations_filter" value="'.getLL("donations_filter_clear").'" onclick="set_action(\'clear_filter\', this);this.submit;">';
			$html .= getLL("donations_filter_clear");
			$html .= '</button>';
			$html .= '</div>';
			$submenu[$menucounter]["items"][] = ko_get_menuitem_html($html);
		break;


		case "export":
			if($max_rights > 0) {
				$found = TRUE;
				$submenu[$menucounter]["titel"] = getLL("submenu_donations_title_export");
				$code  = '<select class="input-sm form-control" name="export_mode" size="0" onchange="jumpToUrl(\'?action=export_donations&export_mode=\'+escape(this.options[this.selectedIndex].value));">';
				$code .= '<option value="">'.getLL('donations_export_submit').'</option>';
				$code .= '<option value="" disabled="disabled">----------</option>';
				$code .= '<option value="person">'.getLL('donations_export_mode_person').'</option>';
				$code .= '<option value="family">'.getLL('donations_export_mode_family').'</option>';
				$code .= '<option value="couple">'.getLL('donations_export_mode_couple').'</option>';
				$code .= '<option value="all">'.getLL('donations_export_mode_all').'</option>';

				//Yearly stats
        //Get all years for which donations are available
        $date_field = ko_get_userpref($_SESSION['ses_userid'], 'donations_date_field');
        if(!$date_field || !in_array($date_field, array('date', 'valutadate'))) $date_field = 'date';
        $years = db_select_distinct("ko_donations", "YEAR(`$date_field`)", '', "WHERE `promise` = '0'");
        foreach($years as $key => $value) if(!$value) unset($years[$key]);
        if(sizeof($years) == 0) $years[] = date('Y');
        rsort($years);
        foreach($years as $year) {
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

				$code .= '</select>';
				$submenu[$menucounter]["items"][] = ko_get_menuitem_html($code);


				if(ko_module_installed('leute')) {
					$all_rights = ko_get_access_all('leute_admin', '', $max_rights);
					if($max_rights > 0) {
						$submenu[$menucounter]['form'] = TRUE;
						$submenu[$menucounter]['form_hidden_inputs'] = array(
							array('name' => 'action', 'value' => ''),
							array('name' => 'ids', 'value' => ''),
						);

						$code = '<button type="submit" name="add_to_my_list" class="btn btn-primary" value="add_to_my_list" title="'.getLL("donations_export_to_my_list_title").'" onclick="set_ids_from_chk(this);set_action('."'add_to_my_list'".', this)">'.getLL('donations_export_to_my_list').'</button>';
						$submenu[$menucounter]["items"][] = ko_get_menuitem_seperator(true);
						$submenu[$menucounter]['items'][] = ko_get_menuitem_html($code);
					}
				}
			}
		break;


		default:
			if($menu) {
				$submenu[$menucounter] = submenu($menu, $state, 3, 'donations');
				$found = (is_array($submenu[$menucounter]));
			}
		}//switch(menu)

		//Plugins erlauben, Menuitems hinzuzufügen
		hook_submenu("donations", $menu, $submenu, $menucounter);

		// Clean up submenu
		if ($doClean) ko_clean_submenu('donations', $submenu[$menucounter], $found);

		if($found) {
			$submenu[$menucounter]["key"] = "donations_".$menu;
			$submenu[$menucounter]["id"] = $menu;
			$submenu[$menucounter]["mod"] = "donations";
			$submenu[$menucounter]["sesid"] = session_id();
			$submenu[$menucounter]["state"] = $stateSaved;

			if($display == 1) {
				$smarty->assign("sm", $submenu[$menucounter]);
				$smarty->assign("ko_path", $ko_path);
				$smarty->assign("help", ko_get_help("donations", "submenu_".$menu));
				$smarty->display("ko_submenu.tpl");
			} else if($display == 2) {
				$smarty->assign("sm", $submenu[$menucounter]);
				$smarty->assign('hideWrappingDiv', TRUE);
				$smarty->assign("ko_path", $ko_path);
				$smarty->assign("help", ko_get_help("donations", "submenu_".$menu));
				$return = "sm_donations_".$menu."@@@";
				$return .= $smarty->fetch("ko_submenu.tpl");
			}

			$smarty->clear_assign("help");
			$menucounter++;
		}
		else {
			if ($display == 2) {
				$return = "sm_donations_".$menu."@@@";
			}
		}



	}//foreach(namen as menu)

	if($display == 2) {
		return $return;
	} else if($display == 3) {
		return $submenu;
	}
}//submenu_donations()




function submenu_tracking($namen, $state, $display=1, $doClean = true) {
	global $ko_path, $smarty;
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

		switch($menu) {

		case 'trackings':
			$found = TRUE;
			$submenu[$menucounter]['titel'] = getLL('submenu_tracking_title_trackings');
			if($max_rights > 3) {
				$submenu[$menucounter]['items'][] = ko_get_menuitem('tracking', 'new_tracking');
			}
			$submenu[$menucounter]['items'][] = ko_get_menuitem('tracking', 'list_trackings');

			$submenu[$menucounter]['items'][] = ko_get_menuitem_seperator();
			$submenu[$menucounter]['items'][] = ko_get_menuitem('tracking', 'list_tracking_entries');

			if($max_rights > 1) {
				$rows = db_get_count('ko_tracking_entries', 'id', " AND `status` = '1'");
				$submenu[$menucounter]['items'][] = ko_get_menuitem('tracking', 'mod_entries', 'link', '', '', $rows);
			}

			if($max_rights > 0 && $ko_menu_akt == 'tracking') {
				$code = '<select name="sel_tracking" class="input-sm form-control" onchange="jumpToUrl(\'?action=select_tracking&amp;id=\'+this.options[this.selectedIndex].value);">';
				$code .= '<option value=""></option>';
				//Get all tracking groups first
				$tgroups = db_select_data('ko_tracking_groups', '', '*', 'ORDER by name ASC');
				foreach($tgroups as $group) {
					$code .= '<option value="" disabled="disabled">'.strtoupper($group['name']).'</option>';
					$trackings = db_select_data('ko_tracking', "WHERE `group_id` = '".$group['id']."'", '*', 'ORDER by name ASC');
					foreach($trackings as $t_i => $t) {
						if($all_rights < 1 && $access['tracking'][$t_i] < 1) continue;
						$active = ($t_i == $_SESSION['tracking_id'] && in_array($_SESSION['show'], array('enter_tracking', 'list_tracking_entries')));
						$code .= '<option value="'.$t_i.'" '.($active?'selected="selected"':'').'>&nbsp;&nbsp;'.ko_html($t['name']).'</option>';
					}//foreach(items)
				}
				//Get all trackings without a group
				$trackings = db_select_data('ko_tracking', 'WHERE `group_id` = \'0\'', '*', 'ORDER by name ASC');
				foreach($trackings as $t_i => $t) {
					if($all_rights < 1 && $access['tracking'][$t_i] < 1) continue;
					$active = ($t_i == $_SESSION['tracking_id'] && in_array($_SESSION['show'], array('enter_tracking', 'list_tracking_entries')));
					$code .= '<option value="'.$t_i.'" '.($active?'selected="selected"':'').'>'.ko_html($t['name']).'</option>';
				}//foreach(items)

				$code .= '</select>';

				$submenu[$menucounter]['items'][] = ko_get_menuitem_seperator();
				$pre = $_SESSION['show'] == 'enter_tracking' ? '<b>' : '';
				$post = $_SESSION['show'] == 'enter_tracking' ? '</b>' : '';
				$submenu[$menucounter]['items'][] = ko_get_menuitem_html($code, $pre.getLL('submenu_tracking_enter_tracking').$post);
			}

			// show option do display hidden entries (only in list_entries view)
			if ($_SESSION['show'] == 'list_trackings' && db_get_count('ko_tracking', 'id', ' and `hidden` = 1') > 0) {
				$checked = $_SESSION['tracking_filter']['show_hidden'];
				$submenu[$menucounter]["items"][] = ko_get_menuitem_html(
					'<div class="checkbox">
							<label for="chk_f_hidden">
								<input type="checkbox" id="chk_f_hidden" name="chk_f_hidden" '.($checked == 1 ? 'checked="checked"' : '').' onchange="sendReq(\'../tracking/inc/ajax.php\', \'action,showhidden,sesid\', \'setfilterhidden,\'+this.checked+\','.session_id().'\', do_element);" />
								<strong>'.getLL('tracking_filter_show_hidden').'</strong>
							</label>
						</div>');
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

			//Group/smallgroup/preset filter
			if($_SESSION['show'] == 'enter_tracking' && $_SESSION['tracking_id'] > 0) {
				$tracking = db_select_data('ko_tracking', 'WHERE `id` = \''.$_SESSION['tracking_id'].'\'', '*', '', '', TRUE);
				$filters = explode(',', $tracking['filter']);
				if(sizeof($filters) > 1) {
					$html  = '<select name="tracking_filter[filter]" class="input-sm form-control" onchange="document.getElementById(\'submit_tracking_filter\').click()">';
					$html .= '<option value="" title="'.getLL('all').'">'.getLL('all').'</option>';
					foreach($filters as $filter) {
						$sel = $_SESSION['tracking_filter']['filter'] == $filter ? 'selected="selected"' : '';
						list($name, $title) = ko_tracking_get_filter_name($filter, TRUE);
						$html .= '<option value="'.$filter.'" title="'.$title.'" '.$sel.'>'.$name.'</option>';
					}
					$html .= '</select>';

					$submenu[$menucounter]['items'][] = ko_get_menuitem_html($html, getLL('tracking_filter_filter'));
				}

				$value = $_SESSION['tracking_filter']['peoplesearch'];
				$html = '<input type="text" class="input-sm form-control" name="tracking_filter[peoplesearch]" id="tracking_filter_peoplesearch" value="'.$value.'">';
				$submenu[$menucounter]['items'][] = ko_get_menuitem_html($html, getLL('tracking_filter_peoplesearch'));
			}

			//Get all presets
			$akt_value = trim($_SESSION['tracking_filter']['date1']) . ',' . trim($_SESSION['tracking_filter']['date2']);
			$itemset = array_merge((array)ko_get_userpref('-1', '', 'tracking_filterpreset', 'ORDER BY `key` ASC'), (array)ko_get_userpref($_SESSION['ses_userid'], '', 'tracking_filterpreset', 'ORDER BY `key` ASC'));
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

			$pre_select = '<div class="input-group input-group-sm">';
			$pre_select .= '<select name="sel_filterpreset" class="input-sm form-control" onchange="sendReq(\'../tracking/inc/ajax.php\', \'action,name,sesid\', \'filterpresetopen,\'+this.options[this.selectedIndex].value+\','.session_id().'\', do_element);">';
			$pre_select .= '<option value=""></option>';
			foreach ($itemselect_values as $k => $v) {
				$pre_select .= '<option value="' . $v . '" ' . ($v == $itemselect_selected ? 'selected="selected"' : '') . '>' . $itemselect_output[$k] . '</option>';
			}
			$pre_select .= '</select>';

			// delete date preset
			if (!$ko_guest) {
				$pre_select .= '<div class="input-group-btn">';
				$pre_select .= '<button type="button" class="btn btn-default" alt="' . getLL('itemlist_delete_preset') . '" title="' . getLL('itemlist_delete_preset') . '" onclick="c = confirm(\'' . getLL('itemlist_delete_preset_confirm') . '\');if(!c) return false; sendReq(\'../tracking/inc/ajax.php\', \'action,name,sesid\', \'filterpresetdelete,\'+document.getElementsByName(\'sel_filterpreset\')[0].options[document.getElementsByName(\'sel_filterpreset\')[0].selectedIndex].value+\',' . session_id() . '\', do_element); return false;"><i class="fa fa-remove"></i></button>';
				$pre_select .= '</div>';
			}
			$pre_select .= '</div>';

			$submenu[$menucounter]['items'][] = ko_get_menuitem_html($pre_select, getLL('itemlist_open_preset'));


			//Date filter
			$datePickerInput = array('type' => 'datepicker', 'value' => ($_SESSION['tracking_filter']['date1'] ? strftime('%d.%m.%Y', strtotime($_SESSION['tracking_filter']['date1'])) : ''), 'name' => 'tracking_filter[date1]', 'html_id' => "tracking_filter_date1");

			$datePickerInput['sibling'] = "tracking_filter_date2";

			$smarty->assign('input', $datePickerInput);
			$dateField1 = $smarty->fetch('ko_formular_elements.tmpl');
			$datePickerInput = array('type' => 'datepicker', 'value' => ($_SESSION['tracking_filter']['date2'] ? strftime('%d.%m.%Y', strtotime($_SESSION['tracking_filter']['date2'])) : ''), 'name' => 'tracking_filter[date2]', 'html_id' => "tracking_filter_date2");
			$smarty->assign('input', $datePickerInput);
			$dateField2 = $smarty->fetch('ko_formular_elements.tmpl');

			$pre  = $_SESSION['tracking_filter']['date1'] || $_SESSION['tracking_filter']['date2'] ? '<mark>' : '';
			$post = $_SESSION['tracking_filter']['date1'] || $_SESSION['tracking_filter']['date2'] ? '</mark>' : '';
			$submenu[$menucounter]['items'][] = ko_get_menuitem_html($dateField1, $pre.getLL('tracking_filter_date').$post.': '.getLL('time_from'));
			$submenu[$menucounter]['items'][] = ko_get_menuitem_html($dateField2, getLL('time_to'));

			//Submit
			$html = '<div width="100%" style="margin-top:5px;" class="text-center"><button type="submit" class="btn btn-primary btn-sm" name="submit_tracking_filter" id="submit_tracking_filter" value="'.getLL('tracking_filter_submit').'" onclick="set_action(\'set_filter\', this);this.submit;">' . getLL('tracking_filter_submit') . '</button>';
			//Clear
			if($showSaveForm) {
				$html .= '&nbsp;<button type="submit" class="btn btn-danger btn-sm" name="clear_tracking_filter" value="'.getLL('tracking_filter_clear').'" onclick="set_action(\'clear_filter\', this);this.submit;">' . getLL('tracking_filter_clear') . '</button>';
			}

			$html .= '</div>';
			$submenu[$menucounter]["items"][] = ko_get_menuitem_html($html);


			$html = '';
			if ($showSaveForm) {
				// save new date preset
				if (!$ko_guest) {
					if ($allow_global) {
						$html .= '<div class="checkbox"><label for="chk_itemlist_global"><input type="checkbox" id="chk_itemlist_global" name="chk_itemlist_global" value="1">' . getLL('itemlist_global') . '</label></div>';
					}
					$html .= '<div class="input-group input-group-sm">';
					$html .= '<input type="text"  class="form-control" name="txt_itemlist_new">';
					$html .= '<span class="input-group-btn">';
					$html .= '<button class="btn btn-default" type="button" id="save_itemlist_" alt="' . getLL('itemlist_save_preset') . '" title="' . getLL('itemlist_save_preset') . '" onclick="sendReq(\'../tracking/inc/ajax.php\', \'action,name' . ($allow_global ? ',global' : '') . ',sesid\', \'filterpresetsave,\'+document.getElementsByName(\'txt_itemlist_new\')[0].value+' . ($allow_global ? '\',\'+document.getElementsByName(\'chk_itemlist_global\')[0].checked+' : '') . '\',' . session_id() . '\', do_element); return false;">';
					$html .= '<span class="glyphicon glyphicon-floppy-save"></span>';
					$html .= '</button>';
					$html .= '</span>';
					$html .= '</div>';
				}

				$submenu[$menucounter]['items'][] = ko_get_menuitem_html($html, getLL("itemlist_save_preset"));
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
				$html  = '<select class="input-sm form-control" name="sel_dates" id="sel_dates">';
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
				$submenu[$menucounter]['items'][] = ko_get_menuitem_html($html, getLL('tracking_export_dates'));
			}

			//Select address columns to be used for export
			$userpref = ko_get_userpref($_SESSION['ses_userid'], 'tracking_export_cols');
			$html  = '<select class="input-sm form-control" name="sel_cols">';
			$html .= '<option value="name" '.($userpref == 'name' ? 'selected="selected"' : '').'>'.getLL('tracking_export_columns_name').'</option>';
			$itemset = array_merge((array)ko_get_userpref('-1', '', 'leute_itemset', 'ORDER BY `key` ASC'), (array)ko_get_userpref($_SESSION['ses_userid'], '', 'leute_itemset', 'ORDER BY `key` ASC'));
			foreach($itemset as $i) {
				$value = $i['user_id'] == '-1' ? '@G@'.$i['key'] : $i['key'];
				$desc = $i['user_id'] == '-1' ? getLL('itemlist_global_short').' '.$i['key'] : $i['key'];
				$sel = $userpref == 'set_'.$value ? 'selected="selected"' : '';
				$html .= '<option value="set_'.$value.'" '.$sel.'>&quot;'.$desc.'&quot;</option>';
			}
			$html .= '</select>';
			$submenu[$menucounter]['items'][] = ko_get_menuitem_html($html, getLL('tracking_export_columns'));

			//Layout select
			$userpref = ko_get_userpref($_SESSION['ses_userid'], 'tracking_export_layout');
			$html  = '<select class="input-sm form-control" name="sel_layout">';
			$html .= '<option value="L" '.($userpref == 'L' ? 'selected="selected"' : '').'>'.getLL('tracking_export_layout_L').'</option>';
			$html .= '<option value="P" '.($userpref == 'P' ? 'selected="selected"' : '').'>'.getLL('tracking_export_layout_P').'</option>';
			$html .= '</select>';
			$submenu[$menucounter]['items'][] = ko_get_menuitem_html($html, getLL('tracking_export_layout'));

				//Add empty rows
			$userpref = ko_get_userpref($_SESSION['ses_userid'], 'tracking_export_addrows');
			$html  = '<select class="input-sm form-control" name="sel_addrows">';
			$html .= '<option value="0" '.($userpref == 0 ? 'selected="selected"' : '').'>0</option>';
			$html .= '<option value="-1" '.($userpref == -1 ? 'selected="selected"' : '').'>'.getLL('tracking_export_addrows_autofill').'</option>';
			for($i=1; $i<=20; $i++) {
				$html .= '<option value="'.$i.'" '.($userpref == $i ? 'selected="selected"' : '').'>'.$i.'</option>';
			}
			$html .= '</select>';
			$submenu[$menucounter]['items'][] = ko_get_menuitem_html($html, getLL('tracking_export_addrows'));

			//Add sums column select
			$userpref = ko_get_userpref($_SESSION['ses_userid'], 'tracking_export_sums');
			$html  = '<select class="input-sm form-control" id="sel_sums" name="sel_sums">';
			for($i=0; $i<3; $i++) {
				$sel = $userpref == $i ? 'selected="selected"' : '';
				$html .= '<option value="'.$i.'" '.$sel.'>'.getLL('tracking_export_sums_'.$i).'</option>';
			}
			$html .= '</select>';
			$submenu[$menucounter]['items'][] = ko_get_menuitem_html($html, getLL('tracking_export_sums'));

			if (!$LEUTE_NO_FAMILY) {
				//Combine families checkbox
				$userpref = ko_get_userpref($_SESSION['ses_userid'], 'tracking_export_family');
				$html  = '<div class="checkbox"><label for="chk_family"><input type="checkbox" id="chk_family" name="chk_family" value="1" '.($userpref == 1 ? 'checked="checked"' : '').'>'.getLL('tracking_export_family').'</label></div>';
				$submenu[$menucounter]['items'][] = ko_get_menuitem_html($html);
			}

			//Add export icons
			$buttons  = array();
			$buttons[] = '<button type="submit" class="btn btn-primary" onclick="set_ids_from_chk(this);set_action(\'export_tracking_xls\', this);" title="'.getLL("tracking_export_excel").'"><i class="fa fa-file-excel-o"></i></button>';
			$buttons[] = '<button type="submit" class="btn btn-primary" onclick="set_ids_from_chk(this);set_action(\'export_tracking_pdf\', this);" title="'.getLL("tracking_export_pdf").'"><i class="fa fa-file-pdf-o"></i></button>';
			//Show icons for mass export
			if($_SESSION['show'] == 'list_trackings') {
				$buttons[] = '<button class="btn btn-primary" id="export_tracking_xls_zip" onclick="set_ids_from_chk(this);set_action(\'export_tracking_xls_zip\', this);" title="'.getLL("tracking_export_excel_zip").'"><span class="fa-stack"><i class="fa fa-file-excel-o fa-stack-1x"></i><i style="background-color: #337ab7;" class="fa fa-file-zip-o fa-stack-xs fa-stack-bottom-right"></i></span></button>';
				$buttons[] = '<button class="btn btn-primary" id="export_tracking_pdf_zip" onclick="set_ids_from_chk(this);set_action(\'export_tracking_pdf_zip\', this);" title="'.getLL("tracking_export_pdf_zip").'"><span class="fa-stack"><i class="fa fa-file-pdf-o fa-stack-1x"></i><i style="background-color: #337ab7;" class="fa fa-file-zip-o fa-stack-xs fa-stack-bottom-right"></i></span></button>';
			}
			$buttonsPerRow = ($_SESSION['show'] == 'list_trackings' ? 4 : 2);
			$html = '<div class="action-icons action-icons-'.$buttonsPerRow.'"><div class="btn-group" style="width:100%;">';
			$i = 0;
			foreach ($buttons as $button) {
				if ($i == $buttonsPerRow) {
					$html .= '</div>';
					$html .= '<div class="btn-group">';
					$i = 0;
				}
				$html .= $button;
				$i++;
			}
			$html .= '</div></div>';
			$submenu[$menucounter]['items'][] = ko_get_menuitem_html($html);
		break;



		case 'itemlist_trackinggroups':
			$itemlist_content = array();
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

			$itemlist_content['tpl_itemlist_select'] = $itemlist;


			//Get all presets
			$akt_value = implode(',', $_SESSION['show_tracking_groups']);
			$itemset = array_merge((array)ko_get_userpref('-1', '', 'tracking_itemset', 'ORDER BY `key` ASC'), (array)ko_get_userpref($_SESSION['ses_userid'], '', 'tracking_itemset', 'ORDER BY `key` ASC'));
			foreach($itemset as $i) {
				$value = $i['user_id'] == '-1' ? '@G@'.$i['key'] : $i['key'];
				$itemselect_values[] = $value;
				$itemselect_output[] = $i['user_id'] == '-1' ? getLL('itemlist_global_short').' '.$i['key'] : $i['key'];
				if($i['value'] == $akt_value) $itemselect_selected = $value;
			}
			$itemlist_content['tpl_itemlist_values'] = $itemselect_values;
			$itemlist_content['tpl_itemlist_output'] = $itemselect_output;
			$itemlist_content['tpl_itemlist_selected'] = $itemselect_selected;
			if($max_rights > 3) $itemlist_content['allow_global'] = TRUE;

			$submenu[$menucounter]['titel'] = getLL('submenu_tracking_title_itemlist_trackinggroups');

			$submenu[$menucounter]['items'][] = ko_get_menuitem_itemlist($itemlist_content);
		break;



		default:
			if($menu) {
				$submenu[$menucounter] = submenu($menu, $state, 3, 'tracking');
				$found = (is_array($submenu[$menucounter]));
			}
		}//switch(menu)

		//Plugins erlauben, Menuitems hinzuzufügen
		hook_submenu('tracking', $menu, $submenu, $menucounter);

		// Clean up submenu
		if ($doClean) ko_clean_submenu('tracking', $submenu[$menucounter], $found);

		if($found) {
			$submenu[$menucounter]['key'] = 'tracking_'.$menu;
			$submenu[$menucounter]['id'] = $menu;
			$submenu[$menucounter]['mod'] = 'tracking';
			$submenu[$menucounter]['sesid'] = session_id();
			$submenu[$menucounter]['state'] = $state;

			if($display == 1) {
				$smarty->assign('sm', $submenu[$menucounter]);
				$smarty->assign('ko_path', $ko_path);
				$smarty->assign('help', ko_get_help('tracking', 'submenu_'.$menu));
				$smarty->display('ko_submenu.tpl');
			} else if($display == 2) {
				$smarty->assign('sm', $submenu[$menucounter]);
				$smarty->assign('hideWrappingDiv', TRUE);
				$smarty->assign("ko_path", $ko_path);
				$smarty->assign("help", ko_get_help("tracking", "submenu_".$menu));
				$return = 'sm_tracking_'.$menu.'@@@';
				$return .= $smarty->fetch('ko_submenu.tpl');
			}

			$smarty->clear_assign("help");
			$menucounter++;
		}
		else {
			if ($display == 2) {
				$return = "sm_tracking_".$menu."@@@";
			}
		}


	}//foreach(namen as menu)

	if($display == 2) {
		return $return;
	} else if($display == 3) {
		return $submenu;
	}
}//submenu_tracking()




/**
 * Gibt ein CRM-Submenu mittels dem Template ko_menu.tpl aus.
 * namen ist eine ,-getrennte Liste der anzuzeigenden Submenus
 * state ist geschlossen oder offen (gilt für alle aus namen)
 * display gibt an, wie der Code zurückgegeben werden soll:
 * 1: normale Ausgabe über smarty, 2: Rückgabe des HTML-Codes für Ajax, 3: Array für Dropdown-Menu
 */
function submenu_crm($namen, $state, $display=1, $doClean = true) {
	global $ko_path, $smarty;
	global $access;
	global $my_submenu;

	$return = "";

	$all_rights = ko_get_access_all('crm_admin', '', $max_rights);

	if($max_rights < 1) return FALSE;

	if (!is_array($namen)) {
		$namen = explode(",", $namen);
	}

	$menucounter = 0;
	foreach($namen as $menu) {
		$found = FALSE;

		switch($menu) {

			case "projects":
				if ($max_rights < 5) break;
				$found = TRUE;
				$submenu[$menucounter]["titel"] = getLL("submenu_crm_title_projects");
				$submenu[$menucounter]['items'][] = ko_get_menuitem('crm', 'new_project');
				$submenu[$menucounter]['items'][] = ko_get_menuitem('crm', 'list_projects');
			break;
			case "status":
				if ($max_rights < 5) break;
				$found = TRUE;
				$submenu[$menucounter]["titel"] = getLL("submenu_crm_title_status");
				$submenu[$menucounter]['items'][] = ko_get_menuitem('crm', 'new_status');
				$submenu[$menucounter]['items'][] = ko_get_menuitem('crm', 'list_status');
			break;
			case "contacts":
				$found = TRUE;
				$submenu[$menucounter]["titel"] = getLL("submenu_crm_title_contacts");
				$submenu[$menucounter]['items'][] = ko_get_menuitem('crm', 'new_contact');
				$submenu[$menucounter]['items'][] = ko_get_menuitem('crm', 'list_contacts');
				$submenu[$menucounter]['items'][] = ko_get_menuitem('crm', 'list_todos');
			break;
			case "filter":
				$found = TRUE;
				$submenu[$menucounter]["titel"] = getLL("submenu_crm_title_filter");

				//Filter: Status
				$c = '';
				$projectStatus = db_select_distinct('ko_crm_projects', 'project_status', "ORDER BY `project_status` ASC");
				foreach($projectStatus as $status) {
					$js = 'sendReq(\'../crm/inc/ajax.php\', [\'sesid\', \'action\', \'field\', \'state\'], [kOOL.sid, \'setfilter\', \'project_status\', \''.(str_replace("'", "\\'", $status)).'\'], do_element);';
					$chk = in_array($status, $_SESSION['crm_filter']['project_status']) ? 'checked="checked"' : '';
					$c .= '<div class="checkbox">';
					$c .= '<label>';
					$c .= '<input type="checkbox" name="chk_project_filter_status_'.$status.'" id="chk_project_filter_status_'.$status.'" value="1" '.$chk.' onclick="'.$js.'">';
					$c .= $status ? $status : '[ '.getLL('crm_filter_no_status').' ]';
					$c .= '</label>';
					$c .= '</div>';
				}
				$submenu[$menucounter]['items'][] = ko_get_menuitem_html($c, getLL('submenu_crm_filter_project_status'));
			break;
			case "itemlist_projects":
				if($max_rights <= 0) break;
				$itemlist_content = array();
				$found = TRUE;

				//Alle Objekte auslesen

				$itemlist[0] = [
					"name" => getLL('crm_projects_dummy_entry'),
					"aktiv" => (in_array(0, $_SESSION["show_crm_projects"]) ? 1 : 0),
					"value" => "0",
				];

				$counter = 1;

				$projectStatus = db_select_distinct('ko_crm_projects', 'project_status', ' ORDER BY `project_status` ASC', " WHERE `project_status` <> ''");
				foreach($projectStatus as $ps) {
					$projects = db_select_data("ko_crm_projects", "WHERE `project_status` = '{$ps}'", "*", "ORDER BY `title` ASC");

					//Find selected projects
					$selected = $local_ids = array();
					foreach($projects as $pid => $project) {
						if(in_array($pid, $_SESSION["show_crm_projects"])) $selected[$pid] = TRUE;
						$local_ids[] = $pid;
					}

					$itemlist[$counter]["type"] = "group";
					$itemlist[$counter]["name"] = $ps.'<sup> (<span name="projectstatus_'.$ps.'">'.sizeof($selected).'</span>)</sup>';
					$itemlist[$counter]["aktiv"] = (sizeof($projects) == sizeof($selected) ? 1 : 0);
					$itemlist[$counter]["value"] = $ps;
					$itemlist[$counter]["open"] = isset($_SESSION["crm_project_status_states"][$ps]) ? $_SESSION["crm_project_status_states"][$ps] : 0;

					$counter++;

					foreach($projects as $i_i => $i) {
						$itemlist[$counter]["name"] = ko_html($i["title"]);
						$itemlist[$counter]["aktiv"] = in_array($i_i, $_SESSION["show_crm_projects"]) ? 1 : 0;
						$itemlist[$counter]["parent"] = TRUE;  //Is subitem to a project status
						$itemlist[$counter++]["value"] = $i_i;
					}//foreach(groups)
					$itemlist[$counter-1]["last"] = TRUE;
				}//foreach(cals)


				//Add event groups without a calendar
				$groups = db_select_data("ko_crm_projects", "WHERE `project_status` = ''", "*", "ORDER BY `title` ASC");

				foreach($groups as $i_i => $i) {
					$itemlist[$counter]["name"] = ko_html($i["title"]);
					$itemlist[$counter]["aktiv"] = in_array($i_i, $_SESSION["show_crm_projects"]) ? 1 : 0;
					$itemlist[$counter++]["value"] = $i_i;
				}//foreach(projects)

				$itemlist_content['tpl_itemlist_select'] = $itemlist;


				//Get all presets
				$akt_value = implode(",", $_SESSION["show_crm_projects"]);
				$itemset = array_merge((array)ko_get_userpref('-1', '', 'crm_itemset', 'ORDER BY `key` ASC'), (array)ko_get_userpref($_SESSION['ses_userid'], '', 'crm_itemset', 'ORDER BY `key` ASC'));
				foreach($itemset as $i) {
					$value = $i['user_id'] == '-1' ? '@G@'.$i['key'] : $i['key'];
					$itemselect_values[] = $value;
					$itemselect_output[] = $i['user_id'] == '-1' ? getLL('itemlist_global_short').' '.$i['key'] : $i['key'];
					if($i["value"] == $akt_value) $itemselect_selected = $value;
				}
				$itemlist_content['tpl_itemlist_values'] = $itemselect_values;
				$itemlist_content['tpl_itemlist_output'] = $itemselect_output;
				$itemlist_content['tpl_itemlist_selected'] = $itemselect_selected;
				if($max_rights > 3) $itemlist_content['allow_global'] = TRUE;

				$submenu[$menucounter]['titel'] = getLL('submenu_crm_title_itemlist_projects');

				$submenu[$menucounter]['items'][] = ko_get_menuitem_itemlist($itemlist_content);
			break;
			default:
				if($menu) {
					$submenu[$menucounter] = submenu($menu, $state, 3, 'crm');
					$found = (is_array($submenu[$menucounter]));
				}
			break;
		}//switch(menu)

		//Plugins erlauben, Menuitems hinzuzufügen
		hook_submenu('tracking', $menu, $submenu, $menucounter);

		// Clean up submenu
		if ($doClean) ko_clean_submenu('tracking', $submenu[$menucounter], $found);

		if($found) {
			$submenu[$menucounter]['key'] = 'crm_'.$menu;
			$submenu[$menucounter]['id'] = $menu;
			$submenu[$menucounter]['mod'] = 'crm';
			$submenu[$menucounter]['sesid'] = session_id();
			$submenu[$menucounter]['state'] = $state;

			if($display == 1) {
				$smarty->assign('sm', $submenu[$menucounter]);
				$smarty->assign('ko_path', $ko_path);
				$smarty->assign('help', ko_get_help('tracking', 'submenu_'.$menu));
				$smarty->display('ko_submenu.tpl');
			} else if($display == 2) {
				$smarty->assign('sm', $submenu[$menucounter]);
				$smarty->assign('hideWrappingDiv', TRUE);
				$smarty->assign("ko_path", $ko_path);
				$smarty->assign("help", ko_get_help("tracking", "submenu_".$menu));
				$return = 'sm_crm_'.$menu.'@@@';
				$return .= $smarty->fetch('ko_submenu.tpl');
			}

			$smarty->clear_assign("help");
			$menucounter++;
		}
		else {
			if ($display == 2) {
				$return = "sm_crm_".$menu."@@@";
			}
		}


	}//foreach(namen as menu)

	if($display == 2) {
		return $return;
	} else if($display == 3) {
		return $submenu;
	}
}//submenu_crm()


function submenu_subscription($namen, $state, $display=1, $doClean = true) {
	global $ko_path, $smarty, $access;

	ko_get_access('subscription');
	if($access['subscription']['MAX'] < 1) return FALSE;

	if(!is_array($namen)) {
		$namen = explode(',', $namen);
	}
	$menucounter = 0;
	foreach($namen as $menu) {
		$found = FALSE;

		switch($menu) {
			case "forms":
				$found = true;
				$submenu[$menucounter]["titel"] = getLL("submenu_subscription_title_forms");
				$submenu[$menucounter]["items"][] = ko_get_menuitem("subscription","new_form");
				$submenu[$menucounter]["items"][] = ko_get_menuitem("subscription","list_forms");
			break;

			case "form_groups":
				if($access['subscription']['MAX'] > 1) {
					$found = true;
					$submenu[$menucounter]["titel"] = getLL("submenu_subscription_title_form_groups");
					$submenu[$menucounter]["items"][] = ko_get_menuitem("subscription","new_form_group");
					$submenu[$menucounter]["items"][] = ko_get_menuitem("subscription","list_form_groups");
				}
			break;

			case "double_opt_in":
				$found = true;
				$submenu[$menucounter]["titel"] = getLL("submenu_subscription_title_double_opt_in");
				if($access['subscription']['ALL'] >= 2) {
					list($num) = mysqli_query(db_get_link(),"SELECT COUNT(id) FROM ko_subscription_double_opt_in WHERE status=0")->fetch_row();
				} else {
					$fgids = array_keys(db_select_data('ko_subscription_form_groups','','id'));
					$addAll = $addOwn = [];
					foreach($fgids as $fgid) {
						$formAccess = max($access['subscription']['ALL'],$access['subscription'][$fgid]);
						if($formAccess >= 2) {
							$addAll[] = $fgid;
						} else if($formAccess == 1) {
							$addOwn[] = $fgid;
						}
					}
					$where = [];
					if($addAll) {
						$where[] = 'f.form_group IN ('.implode(',',$addAll).')';
					}
					if($addOwn) {
						$where[] = '(f.form_group IN ('.implode(',',$addOwn).') AND f.cruser = '.$_SESSION['ses_userid'].')';
					}
					if(sizeof($where) > 0) {
						list($num) = mysqli_query(db_get_link(),"SELECT COUNT(doi.id) FROM ko_subscription_double_opt_in doi JOIN ko_subscription_forms f ON(doi.form=f.id) WHERE doi.status=0 AND (".implode(' OR ',$where).")")->fetch_row();
					} else {
						$num = 0;
					}
				}
				$submenu[$menucounter]["items"][] = ko_get_menuitem_link("subscription","list_unconfirmed_double_opt_ins",$num,'',$num <= 0);
			break;

			default:
				if($menu) {
					$submenu[$menucounter] = submenu($menu, $state, 3, 'subscription');
					$found = (is_array($submenu[$menucounter]));
				}
		}//switch(menu)

		//Plugins erlauben, Menuitems hinzuzufügen
		hook_submenu("subscription", $menu, $submenu, $menucounter);

		// Clean up submenu
		if($doClean) ko_clean_submenu('subscription', $submenu[$menucounter], $found);

		if($found) {
			$submenu[$menucounter]["key"] = "subscription_".$menu;
			$submenu[$menucounter]["id"] = $menu;
			$submenu[$menucounter]["mod"] = "subscription";
			$submenu[$menucounter]["sesid"] = session_id();
			$submenu[$menucounter]["state"] = $state;

			if($display == 1) {
				$smarty->assign("sm", $submenu[$menucounter]);
				$smarty->assign("ko_path", $ko_path);
				$smarty->assign("help", ko_get_help("subscription", "submenu_".$menu));
				$smarty->display("ko_submenu.tpl");
			} else if($display == 2) {
				$smarty->assign("sm", $submenu[$menucounter]);
				$smarty->assign('hideWrappingDiv', TRUE);
				$smarty->assign("ko_path", $ko_path);
				$smarty->assign("help", ko_get_help("subscription", "submenu_".$menu));
				$return = "sm_admin_".$menu."@@@";
				$return .= $smarty->fetch("ko_submenu.tpl");
			}

			$smarty->clear_assign("help");
			$menucounter++;
		}
		else {
			if ($display == 2) {
				$return = "sm_subscription_".$menu."@@@";
			}
		}
	}//foreach(namen as menu)

	if($display == 2) {
		return $return;
	} else if($display == 3) {
		return $submenu;
	}
}


/**
 * Add items for absences (filter, all, own) to filter-itemlist
 *
 * @param &$itemlist
 * @param $absence_rights
 * @param $module
 */
function submenu_itemlist_absences(&$itemlist, $absence_rights, $module) {
	end($itemlist);
	$counter = key($itemlist) + 1;
	reset($itemlist);

	if ($module == "reservation") {
		$session_absences = $_SESSION["show_absences_res"];
	} else {
		$session_absences = $_SESSION["show_absences"];
	}

	$absence_color = ko_get_setting('absence_color');

	$show_absences_in_pages = ['calendar',];
	if(in_array($_SESSION['show'], $show_absences_in_pages) && $_SESSION['ses_userid'] != ko_get_guest_id() && $absence_rights > 0) {
		if ($absence_rights > 1) {
			$absence_group_status = 0;
			foreach($session_absences AS $absence) {
				if(is_numeric($absence)) {
					$absence_group_status = 1;
				}
			}

			$itemlist[$counter] = [
				"type" => "group",
				"name" => getLL('absence_eventgroup_filter'),
				"prename" => '<span style="margin-right:2px;background-color:' . $absence_color . '">&emsp;</span>',
				"aktiv" => $absence_group_status,
				"value" => "absence",
				"open" => isset($_SESSION["daten_calendar_states"]["absence"]) ? $_SESSION["daten_calendar_states"]["absence"] : 0,
			];
			$counter++;

			$absence_filters = array_merge((array)ko_get_userpref('-1', '', 'filterset', 'ORDER BY `key` ASC'), (array)ko_get_userpref($_SESSION['ses_userid'], '', 'filterset', 'ORDER BY `key` ASC'));

			foreach ($absence_filters AS $absence_filter) {
				$label = ($absence_filter['user_id'] == '-1' ? getLL('itemlist_global_short').$absence_filter["key"] : $absence_filter["key"]);
				$itemlist[$counter++] = [
					"name" => $label,
					"aktiv" => in_array($absence_filter["id"], $session_absences) ? 1 : 0,
					"value" => "absence_" . $absence_filter["id"],
					"parent" => TRUE,
				];
			}
			$itemlist[$counter-1]["last"] = TRUE;

			$itemlist[$counter++] = [
				"name" => getLL('absence_eventgroup_all'),
				"prename" => '<span style="margin-right:2px;background-color:' . $absence_color . '">&emsp;</span>',
				"aktiv" => in_array("all", $session_absences) ? 1 : 0,
				"value" => "absence_all",
			];
		}

		if ($absence_rights >= 1) {
			$itemlist[$counter] = [
				"name" => getLL('absence_eventgroup_own'),
				"prename" => '<span style="margin-right:2px;background-color:' . $absence_color . '">&emsp;</span>',
				"aktiv" => in_array("own", $session_absences) ? 1 : 0,
				"value" => "absence_own",
			];
		}
	}
}


/**
 * Add items for amtstage (teams) to filter-itemlist
 *
 * @param &$itemlist
 * @param $module
 * @return bool FALSE if user has not enough rights
 */
function submenu_itemlist_amtstage(&$itemlist, $module) {
	global $access;
	end($itemlist);
	$counter = key($itemlist) + 1;
	reset($itemlist);

	if ($module == "reservation") {
		$session_amtstage = $_SESSION["show_amtstage_res"];
	} else {
		$session_amtstage = $_SESSION["show_amtstage"];
	}

	ko_get_access("rota");
	$allowed_team_ids = [];
	foreach($access['rota'] AS $id => $rights) {
		if(is_numeric($id) && $rights >= 1) {
			$allowed_team_ids[] = $id;
		}
	}

	if(empty($allowed_team_ids)) return FALSE;

	$show_amtstage_in_pages = ['calendar',];
	if(in_array($_SESSION['show'], $show_amtstage_in_pages) && $_SESSION['ses_userid'] != ko_get_guest_id()) {
		$amtstag_group_status = 0;
		foreach($session_amtstage AS $amtstag) {
			if(is_numeric($amtstag)) {
				$amtstag_group_status = 1;
			}
		}

		$where = " AND rotatype = 'day' AND id IN(" . implode(",", $allowed_team_ids) . ")";
		$amtstage_teams = ko_rota_get_all_teams($where);
		if(count($amtstage_teams) == 0) return FALSE;

		$itemlist[$counter] = [
			"type" => "group",
			"name" => getLL('amtstage_eventgroup_filter'),
			"prename" => '<span style="margin-right:2px;background-color:#ffffff">&emsp;</span>',
			"aktiv" => $amtstag_group_status,
			"value" => "amtstag",
			"open" => isset($_SESSION["daten_calendar_states"]["amtstag"]) ? $_SESSION["daten_calendar_states"]["amtstag"] : 0,
		];
		$counter++;

		foreach ($amtstage_teams AS $amtstage_team) {
			$label = $amtstage_team['name'];
			$itemlist[$counter++] = [
				"name" => $label,
				"prename" => '<span style="margin-right:2px;background-color:#' . $amtstage_team['farbe'] . '">&emsp;</span>',
				"aktiv" => in_array($amtstage_team["id"], $session_amtstage) ? 1 : 0,
				"value" => "amtstag_" . $amtstage_team["id"],
				"parent" => TRUE,
			];
		}
		$itemlist[$counter-1]["last"] = TRUE;
	}
}



$DISABLE_SM = array(
	"admin" => array(
		"admin_settings" => array("filter"),
		"change_password" => array("filter"),
		"show_logins" => array("filter"),
		"edit_login" => array("filter"),
		"new_login" => array("filter"),
		"show_logs" => array(),
		'new_news' => array('filter'),
		'edit_news' => array('filter'),
		'list_news' => array('filter'),
		'show_sms_log' => array(),
		'show_telegram_log' => array(),
		'show_admingroups' => array('filter'),
		'new_admingroup' => array('filter'),
	),
	"daten" => array(
		"all_events" => array(),
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
		'list_absence' => array('export', 'itemlist_termingruppen'),
		'list_rooms' => array('filter', 'export', 'itemlist_termingruppen'),
	),
	'rota' => array(
		'list_teams' => array('itemlist_teams', 'itemlist_eventgroups'),
		'edit_team' => array('itemlist_teams', 'itemlist_eventgroups'),
		'new_team' => array('itemlist_teams', 'itemlist_eventgroups'),
		'settings' => array('itemlist_teams', 'itemlist_eventgroups'),
		'show_filesend' => array('itemlist_teams', 'itemlist_eventgroups'),
	),
	'groups' => array(
		'list_groups' => array('dffilter'),
		'new_group' => array('dffilter', 'export'),
		'edit_group' => array('dffilter', 'export'),
		'list_roles' => array('dffilter', 'export'),
		'new_role' => array('dffilter', 'export'),
		'edit_role' => array('dffilter', 'export'),
		'delete_role' => array('dffilter', 'export'),
		'edit_datafield' => array('dffilter', 'export'),
		'multiedit' => array('dffilter', 'export'),
		'multiedit_roles' => array('dffilter', 'export'),
		'list_datafields' => array('export'),
		'groups_settings' => array('dffilter', 'export'),
	),
	"leute" => array(
		"show_all" => array(),
		"show_my_list" => array(),
		"show_adressliste" => array("itemlist_spalten", "aktionen"),
		"geburtstagsliste" => array("filter"),
		"single_view" => array("filter", "itemlist_spalten", "aktionen"),
		"neue_person" => array("filter", "itemlist_spalten", "aktionen", "meine_liste"),
		"edit_person" => array("filter", "itemlist_spalten", "aktionen", "meine_liste"),
		"email_versand" => array("filter", "itemlist_spalten", "aktionen", "meine_liste"),
		"edit_kg" => array("filter", "itemlist_spalten", "aktionen", "meine_liste"),
		"neue_kg" => array("filter", "itemlist_spalten", "aktionen", "meine_liste"),
		"etiketten_optionen" => array("filter", "itemlist_spalten", "aktionen", "meine_liste"),
		"xls_settings" => array("filter", "itemlist_spalten", "aktionen", "meine_liste"),
		"sms_versand" => array("filter", "itemlist_spalten", "aktionen", "meine_liste"),
		"list_kg" => array("filter", "meine_liste", "itemlist_spalten", "aktionen"),
		"chart_kg" => array("filter", "meine_liste", "itemlist_spalten", "aktionen"),
		"mutationsliste" => array("filter", "itemlist_spalten"),
		"groupsubscriptions" => array("filter", "itemlist_spalten", "aktionen"),
		"multiedit" => array("filter", "itemlist_spalten", "aktionen", "meine_liste"),
		"import" => array("filter", "itemlist_spalten", "aktionen", "meine_liste"),
		"export_pdf" => array("filter", "itemlist_spalten", "aktionen", "meine_liste"),
		"chart" => array("itemlist_spalten", "aktionen", "meine_liste"),
		"mailmerge" => array("filter", "itemlist_spalten", "aktionen", "meine_liste"),
		'settings' => array('filter', 'itemlist_spalten', 'aktionen', 'itemlist_spalten_kg', 'meine_liste'),
	),
	"reservation" => array(
		"neue_reservation" => array("filter", "itemlist_objekte", "export"),
		"edit_reservation" => array("filter", "itemlist_objekte", "export"),
		"liste" => array("objektbeschreibungen"),
		"calendar" => array("objektbeschreibungen", "filter"),
		"cal_jahr" => array("objektbeschreibungen", "filter"),
		"show_mod_res" => array("objektbeschreibungen", "itemlist_objekte", "export"),
		"list_items" => array("objektbeschreibungen", "filter", "itemlist_objekte", "export"),
		"new_item" => array("objektbeschreibungen", "filter", "itemlist_objekte", "export"),
		"edit_item" => array("objektbeschreibungen", "filter", "itemlist_objekte", "export"),
		"email_confirm" => array("objektbeschreibungen", "filter", "itemlist_objekte", "export"),
		"multiedit" => array("objektbeschreibungen", "filter", "itemlist_objekte", "export"),
		"multiedit_group" => array("objektbeschreibungen", "filter", "itemlist_objekte", "export"),
		"res_settings" => array("objektbeschreibungen", "filter", "itemlist_objekte"),
		'ical_links' => array('objektbeschreibungen', 'filter'),
	),
	"tools" => array(),
	"donations" => array(
		"list_accounts" => array("itemlist_accounts", "filter"),
		'list_donations_mod' => array('itemlist_accounts', 'filter', 'export'),
		'new_account' => array('itemlist_accounts', 'filter', 'export'),
		'edit_account' => array('itemlist_accounts', 'filter', 'export'),
		'new_donation' => array('itemlist_accounts', 'filter', 'export'),
		'edit_donation' => array('itemlist_accounts', 'filter', 'export'),
		'list_reoccuring_donations' => array('itemlist_accounts', 'filter', 'export'),
		"show_stats" => array('export'),
		'donation_settings' => array('itemlist_accounts', 'filter', 'export'),
		'list_accountgroups' => array('itemlist_accounts', 'filter'),
		'new_accountgroup' => array('itemlist_accounts', 'filter', 'export'),
		'edit_accountgroup' => array('itemlist_accounts', 'filter', 'export'),
	),
	'tracking' => array(
		'list_trackings' => array(),
		'new_tracking' => array('export', 'filter', 'itemlist_trackinggroups'),
		'edit_tracking' => array('export', 'filter', 'itemlist_trackinggroups'),
		'enter_tracking' => array('itemlist_trackinggroups'),
		'list_tracking_entries' => array('itemlist_trackinggroups', 'export'),
		'tracking_settings' => array('export', 'filter', 'itemlist_trackinggroups'),
		'mod_entries' => array('export', 'filter'),
	),
	'crm' => array(
		'list_crm_projects' => array('itemlist_projects'),
		'new_crm_project' => array('itemlist_projects', 'filter'),
		'list_crm_status' => array('itemlist_projects', 'filter'),
		'new_crm_status' => array('itemlist_projects', 'filter'),
		'list_crm_contacts' => array('filter'),
		'new_crm_contact' => array('itemlist_projects', 'filter'),
	),
);

?>
