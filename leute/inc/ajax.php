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

//Set session id from GET (session will be started in ko.inc.php)
if(!isset($_GET["sesid"])) exit;
if(FALSE === session_id($_GET["sesid"])) exit;

//Send headers to ensure UTF-8 charset
header('Content-Type: text/html; charset=UTF-8');

error_reporting(0);
$ko_menu_akt = 'leute';
$ko_path = "../../";
require __DIR__ . '/../../inc/ko.inc.php';
$ko_path = "../";

ko_get_access('leute');
ko_get_access('groups');
if(ko_module_installed('kg')) ko_get_access('kg');

ko_include_kota(array('ko_leute', 'ko_kleingruppen'));

//Smarty-Templates-Engine laden
require __DIR__ . '/../../inc/smarty.inc.php';

require __DIR__ . '/leute.inc.php';
if(ko_module_installed("kg")) require __DIR__ . '/kg.inc.php';

// Plugins einlesen:
$hooks = hook_include_main("leute,kg");
foreach($hooks as $hook) include_once($hook);


//HOOK: Submenus einlesen
$hooks = hook_include_sm();
foreach($hooks as $hook) include($hook);

hook_show_case_pre($_SESSION['show']);

if(isset($_GET) && isset($_GET["action"])) {
	$action = format_userinput($_GET["action"], "alphanum");

	hook_ajax_pre($ko_menu_akt, $action);

	switch($action) {

		case "leutefilterform":
		case "leutesavefilterset":
		case "leutedelfilterset":
			if($access['leute']['MAX'] < 1) break;
			//Position des Filter-SM finden
			if(in_array("filter", explode(",", $_SESSION["submenu_left"]))) $pos = "left";
			else $pos = "right";

			if($action == "leutefilterform") {
				//Neuen Filter aktiv setzen
				$fid = format_userinput($_GET["fid"], "uint");
				$_SESSION["filter_akt"] = $fid;
			}
			else if($action == "leutesavefilterset") {
				if(trim($_GET["name"]) == "") break;

				//save cols if needed
				if($_GET["withcols"] == "true") {
					$_SESSION["filter"]["cols"] = implode(",", $_SESSION["show_leute_cols"]);
					$_SESSION['filter']['sort'] = implode(',', $_SESSION['sort_leute']);
					$_SESSION['filter']['sort_order'] = implode(',', $_SESSION['sort_leute_order']);
				} else {
					unset($_SESSION["filter"]["cols"]);
					unset($_SESSION['filter']['sort']);
					unset($_SESSION['filter']['sort_order']);
				}
				$new_value = serialize($_SESSION["filter"]);
				//Save filter for all selected logins
				if($access['leute']['MAX'] > 3) {
					$for_logins = format_userinput(str_replace("MULTIPLE", ",", $_GET["logins"]), "intlist");
					foreach(explode(",", $for_logins) as $login) {
						if($login) ko_save_userpref($login, format_userinput($_GET["name"], "js"), $new_value, "filterset");
					}
				}
				//Set user_id to -1 if to be stored globally
				$user_id = ($access['leute']['MAX'] > 3 && $_GET['global'] == 'true') ? '-1' : $_SESSION['ses_userid'];
				//Store filter as userpref
				ko_save_userpref($user_id, format_userinput($_GET["name"], "js"), $new_value, "filterset");
			}
			else if($action == "leutedelfilterset") {
				if($_GET["name"] == "") break;
				//Check for global filter
				if(mb_substr($_GET['name'], 0, 3) == '@G@') {
					if($access['leute']['MAX'] > 3) {
						ko_delete_userpref('-1', format_userinput(mb_substr($_GET['name'], 3), 'js'), 'filterset');
					}
				} else {
					ko_delete_userpref($_SESSION["ses_userid"], format_userinput($_GET["name"], "js"), "filterset");
				}
			}

			//Neuen HTML-Code für SM ausgeben
			print submenu_leute("filter", $pos, "open", 2);

			//group filter
			ko_get_filter_by_id($_SESSION["filter_akt"], $akt_filter);
			if($akt_filter['_name'] == 'group') print "@@@POST@@@filter_group";
		break;


		case "setstart":
			if($access['leute']['MAX'] < 1) break;

			//Set list start
			if(isset($_GET['set_start'])) {
				$_SESSION['show_start'] = max(1, format_userinput($_GET['set_start'], 'uint'));
	    }
			//Set list limit
			if(isset($_GET['set_limit'])) {
				$_SESSION['show_limit'] = max(1, format_userinput($_GET['set_limit'], 'uint'));
				ko_save_userpref($_SESSION['ses_userid'], 'show_limit_leute', $_SESSION['show_limit']);
	    }

			//Neuen HTML-Code für SM ausgeben
			print "main_content@@@";
			if($_SESSION["show"] == "show_all") {
				print ko_list_personen("liste", FALSE);
			} else if($_SESSION["show"] == "show_my_list") {
				print ko_list_personen("my_list", FALSE);
			} else if($_SESSION['show'] == 'geburtstagsliste') {
				print ko_list_personen('birthdays', FALSE);
			} else if($_SESSION["show"] == "show_adressliste") {
				print ko_list_personen("adressliste", FALSE);
			}
		break;


		case "setstartkg":
			$kg_all_rights = ko_get_access_all('kg', '', $kg_max_rights);
			if($kg_max_rights < 1) break;

			//Set list start
			if(isset($_GET['set_start'])) {
				$_SESSION['show_kg_start'] = max(1, format_userinput($_GET['set_start'], 'uint'));
	    }
			//Set list limit
			if(isset($_GET['set_limit'])) {
				$_SESSION['show_kg_limit'] = max(1, format_userinput($_GET['set_limit'], 'uint'));
				ko_save_userpref($_SESSION['ses_userid'], 'show_limit_kg', $_SESSION['show_kg_limit']);
	    }

			//Neuen HTML-Code für SM ausgeben
			print "main_content@@@";
			print ko_list_kg(FALSE);
		break;


		case "leutefilter":
		case "leutefilternew":
		case "leutefilterdelall":
		case "leutefilterdel":
		case "leutefilterlink":
		case 'leutefilterlinkadv':
		case "leuteschnellfilter":
		case "leuteopenfilterset":
			if($access['leute']['MAX'] < 1) break;

			//Position des Filter-SM finden
			if(in_array("filter", explode(",", $_SESSION["submenu_left"]))) $pos = "left";
			else $pos = "right";

			//Filter löschen, falls Neu Anwenden geklickt wurde
			if($action == "leutefilternew" || $action == "leutefilterdelall") {
				//Link speichern
				$link = $_SESSION["filter"]["link"];
				$_SESSION["filter"] = array("link" => $link);
			}
			//Set link to AND for fast filter
			if($action == "leuteschnellfilter") $_SESSION["filter"] = array("link" => "and");

			//Get back to list view if coming from my_list
			if($_SESSION['show'] == 'show_my_list') $_SESSION['show'] = 'show_all';

			if($action == "leutefilter" || $action == "leutefilternew") {
				//Daten auslesen
				$data["var1"] = format_userinput($_GET["var1"], "text");
				$data["var2"] = format_userinput($_GET["var2"], "text");
				$data["var3"] = format_userinput($_GET["var3"], "text");
				if(isset($_GET["neg"]) && $_GET["neg"] == "true") $neg = 1;
				else $neg = 0;

				//Daten in Filter einbauen
				ko_get_filter_by_id($_SESSION["filter_akt"], $f);
				$vars = array();
				for($i = 1; $i <= $f["numvars"]; $i++) {
					$vars[$i] = $data["var".$i];
					$vars[$i] = str_replace("*", ".*", $vars[$i]);  //* mit .* ersetzen, damit lau*er trotzdem geht.
				}
				$_SESSION["filter"][] = array($_SESSION["filter_akt"], $vars, $neg);
				$_SESSION["show_start"] = 1;
				$_SESSION['filter']['use_link_adv'] = FALSE;
			}
			//Delete a single selected filter
			else if($action == "leutefilterdel") {
				foreach($_SESSION["filter"] as $key => $value) {
					if(is_numeric($key)) {
						if($key != (int)$_GET["id"]) $new[] = $value;
					} else {
						$new[$key] = $value;
					}
				}
				$_SESSION["filter"] = $new;
				$_SESSION['filter']['use_link_adv'] = FALSE;
			}
			//Filter-Verknüpfung setzen
			else if($action == "leutefilterlink") {
				if($_GET["link"] == "or") {
					$_SESSION["filter"]["link"] = "or";
				} else {
					$_SESSION["filter"]["link"] = "and";
				}
				$_SESSION['filter']['use_link_adv'] = FALSE;
			}
			//Set advanced filter link
			else if($action == 'leutefilterlinkadv') {
				$_SESSION['filter']['link_adv'] = $_GET['link'];
				$_SESSION['filter']['use_link_adv'] = TRUE;
			}
			//Schnell-Filter
			else if($action == "leuteschnellfilter") {
				$fast_filter = ko_get_fast_filter();
				foreach($fast_filter as $id) {
					if($_GET["fastfilter".$id]) $_SESSION["filter"][] = array($id, array('', str_replace("*", ".*", format_userinput($_GET["fastfilter".$id], "text"))), 0);
				}
				$_SESSION["show_start"] = 1;  //Liste von vorne her anzeigen
				if($_SESSION["show"] != "show_all" && $_SESSION["show"] != "show_adressliste") {
					$_SESSION["show"] = "show_all";
				}
				$_SESSION['filter']['use_link_adv'] = FALSE;
			}
			//Filter-Vorlage öffnen
			else if($action == "leuteopenfilterset") {
				$name = mb_substr($_GET['name'], 0, 3) == '@G@' ? mb_substr($_GET['name'], 3) : $_GET['name'];
				$value = mb_substr($_GET['name'], 0, 3) == '@G@' ? (array)ko_get_userpref('-1', '', 'filterset') : (array)ko_get_userpref($_SESSION['ses_userid'], '', 'filterset');
				$_SESSION["filter"] = array();
				foreach($value as $v_i => $v) {
					if($v["key"] == $name) {
						$restore_filter = unserialize($value[$v_i]["value"]);
						if($restore_filter["cols"]) {
							$_SESSION["show_leute_cols"] = explode(",", $restore_filter["cols"]);
							//Save userpref
							ko_save_userpref($_SESSION["ses_userid"], "show_leute_cols", implode(",", $_SESSION["show_leute_cols"]));
						}
						if($restore_filter['sort']) {
							$_SESSION['sort_leute'] = explode(',', $restore_filter['sort']);
							$_SESSION['sort_leute_order'] = explode(',', $restore_filter['sort_order']);
						}
						$_SESSION["filter"] = $restore_filter;
					}
				}
			}

			//Neuen HTML-Code für Main ausgeben
			print "main_content@@@";
			if($_SESSION["show"] == "chart") {
				print ko_leute_chart();
			} else {
				print ko_list_personen(($_SESSION["show"] == "show_adressliste"?"adressliste":"liste"), FALSE);
			}

			//Neuen HTML-Code für SM ausgeben
			print "@@@";
			print submenu_leute("filter", $pos, "open", 2);

			//Redraw cols-submenu
			print "@@@";
			print submenu_leute("itemlist_spalten", $pos, "open", 2);

			//group filter
			ko_get_filter_by_id($_SESSION["filter_akt"], $akt_filter);
			if($akt_filter["sql1"] == "groups REGEXP '[VAR1][g:0-9]*[VAR2]'") print "@@@POST@@@filter_group";
		break;


		case "setsortleute":
			if($access['leute']['MAX'] < 1) break;

			$_SESSION['sort_leute'] = array(format_userinput($_GET['sort'], 'alphanumlist', TRUE));
			$_SESSION["sort_leute_order"] = array(format_userinput($_GET["sort_order"], "alpha", TRUE, 4));

			//Modus finden
			if($_SESSION["show"] == "show_my_list") $mode = "my_list";
			else $mode = "liste";

			print "main_content@@@";
			print ko_list_personen($mode, FALSE);
		break;


		case "setmultisort":
			if($access['leute']['MAX'] < 1) break;

			$col = format_userinput($_GET["col"], "uint");
			$sort = format_userinput($_GET['sort'], 'alphanum+', TRUE, 0, array(), ':');
			$sort_order = format_userinput($_GET["order"], "alpha", TRUE, 4);

			if(isset($_GET["sort"]) && $sort) {  //Set sort column if column was given (onchange on select)
				$_SESSION["sort_leute"][$col] = $sort;
				$_SESSION["sort_leute_order"][$col] = "ASC";
			} else if(isset($_GET["order"])) {  //Only order is given, so the order-icon has been clicked
				if(!in_array($sort_order, array("ASC", "DESC"))) break;
				$_SESSION["sort_leute_order"][$col] = $sort_order;
			} else {  //Otherwise the select was set to empty, which means: deactivate this column
				unset($_SESSION["sort_leute"][$col]);
				unset($_SESSION["sort_leute_order"][$col]);
			}
			//Set default sorting, if all was deselected
			if(sizeof($_SESSION["sort_leute"]) == 0) {
				$_SESSION["sort_leute"][0] = "nachname";
				$_SESSION["sort_leute_order"][0] = "ASC";
			}
			//Recreate numeric index
			$_SESSION["sort_leute"] = array_merge($_SESSION["sort_leute"]);
			$_SESSION["sort_leute_order"] = array_merge($_SESSION["sort_leute_order"]);

			print "main_content@@@";
			print ko_list_personen("liste", FALSE);
		break;


		case "setsortkg":
			$kg_all_rights = ko_get_access_all('kg', '', $kg_max_rights);
			if($kg_max_rights < 1) break;

			$_SESSION['sort_kg'] = format_userinput($_GET['sort'], 'alphanum+', TRUE);
			$_SESSION["sort_kg_order"] = format_userinput($_GET["sort_order"], "alpha", TRUE, 4);

			print "main_content@@@";
			print ko_list_kg(FALSE);
		break;


		case "itemlist":
			//Modus finden
			if($_SESSION["show"] == "show_my_list") $mode = "my_list";
			else if($_SESSION['show'] == 'geburtstagsliste') $mode = 'birthdays';
			else $mode = "liste";

			//ID and state of the clicked field
			$id = format_userinput($_GET["id"], "js");
			if($_GET["state"] == "true") {
				$state = "checked";
			} else if($_GET["state"] == "switch") {
				$state = in_array($id, $_SESSION["show_leute_cols"]) ? "" : "checked";
			} else {
				$state = "";
			}

			$redraw = $_GET['redraw'] == 1;

			//sg's or people's list
			if($_SESSION["show"] == "list_kg") {
				if($state == "checked") {  //Select it
					if(!in_array($id, $_SESSION["kota_show_cols_ko_kleingruppen"])) $_SESSION["kota_show_cols_ko_kleingruppen"][] = $id;
					//Move it to the place according to the list-order
					$new_value = NULL;
					foreach($KOTA['ko_kleingruppen']['_listview'] as $col) {
						$i = $col['name'];
						if($col['name'] != 'id' && in_array($col['name'], $_SESSION["kota_show_cols_ko_kleingruppen"])) {
							$new_value[] = $col['name'];
						}
					}
					$_SESSION["kota_show_cols_ko_kleingruppen"] = $new_value;
				} else {  //deselect it
					if(in_array($id, $_SESSION["kota_show_cols_ko_kleingruppen"])) $_SESSION["kota_show_cols_ko_kleingruppen"] = array_diff($_SESSION["kota_show_cols_ko_kleingruppen"], array($id));
				}
				//Rebuild clean index starting with 0
				$_SESSION['kota_show_cols_ko_kleingruppen'] = array_merge($_SESSION['kota_show_cols_ko_kleingruppen']);
				//Save userpref
				ko_save_userpref($_SESSION["ses_userid"], "kota_show_cols_ko_kleingruppen", implode(",", $_SESSION["kota_show_cols_ko_kleingruppen"]));

				print "main_content@@@";
				print ko_list_kg(FALSE);
			}
			else if($_SESSION["show"] == "chart") {
				if($access['leute']['MAX'] < 1) break;
				if($state == "checked") {  //Select it
					if(!in_array($id, $_SESSION["show_leute_chart"])) $_SESSION["show_leute_chart"][] = $id;
					//Move it to the place according to the list-order
					$new_value = NULL;
					foreach($_SESSION["show_leute_chart"] as $i) {
						if(in_array($i, $LEUTE_CHART_TYPES)) $new_value[] = $i;
					}
					$_SESSION["show_leute_chart"] = $new_value;
				} else {  //deselect it
					if(in_array($id, $_SESSION["show_leute_chart"])) $_SESSION["show_leute_chart"] = array_diff($_SESSION["show_leute_chart"], array($id));
				}
				//Save userpref
				ko_save_userpref($_SESSION["ses_userid"], "show_leute_chart", implode(",", $_SESSION["show_leute_chart"]));

				print "main_content@@@";
				print ko_leute_chart();
			}
			else {
				if($access['leute']['MAX'] < 1) break;

				$cols = array_keys(ko_get_leute_col_name(FALSE, TRUE));
				if($state == "checked") {  //Select it
					if(!in_array($id, $_SESSION["show_leute_cols"])) $_SESSION["show_leute_cols"][] = $id;

					//group column to show all datafields as well
					if(ko_get_userpref($_SESSION['ses_userid'], 'group_shows_datafields') == 1
						&& mb_substr($id, 0, 9) == 'MODULEgrp'
						&& FALSE === mb_strpos($id, ':')
						) {
						foreach($cols as $col) {
							if(mb_substr($col, 0, 15) != mb_substr($id, 0, 15)) continue;
							if(!in_array($col, $_SESSION['show_leute_cols'])) $_SESSION['show_leute_cols'][] = $col;
						}
					}

					$new_value = NULL;
					if(ko_get_userpref($_SESSION["ses_userid"], "sort_cols_leute") == "0") {
						//Only check for valid columns, so no invalid (deleted) can stay in the list
						foreach($_SESSION["show_leute_cols"] as $col) {
							if(in_array($col, $cols)) $new_value[] = $col;
						}
					} else {
						//Move it to the place according to the list-order
						foreach($cols as $col) {
							if(in_array($col, $_SESSION["show_leute_cols"])) $new_value[] = $col;
						}
					}
					//Store new value in session
					$_SESSION["show_leute_cols"] = $new_value;
				} else {  //deselect it
					if(in_array($id, $_SESSION["show_leute_cols"])) {
						$_SESSION["show_leute_cols"] = array_diff($_SESSION["show_leute_cols"], array($id));
					}

					//group column to show all datafields as well
					if(ko_get_userpref($_SESSION['ses_userid'], 'group_shows_datafields') == 1
						&& mb_substr($id, 0, 9) == 'MODULEgrp'
						&& FALSE === mb_strpos($id, ':')
						) {
						foreach($cols as $col) {
							if(mb_substr($col, 0, 15) != mb_substr($id, 0, 15)) continue;
							if(in_array($col, $_SESSION['show_leute_cols'])) {
								$_SESSION["show_leute_cols"] = array_diff($_SESSION["show_leute_cols"], array($col));
							}
						}
					}

				}
				//Save userpref
				ko_save_userpref($_SESSION["ses_userid"], "show_leute_cols", implode(",", $_SESSION["show_leute_cols"]));

				print "main_content@@@";
				print ko_list_personen($mode, FALSE);

				//Redraw itemlist if needed (if clicked in table header)
				if($redraw) {
					if(in_array('itemlist_spalten', explode(',', $_SESSION['submenu_left']))) $pos = 'left';
					else $pos = "right";
					print '@@@'.submenu_leute('itemlist_spalten', $pos, 'open', 2);
				}
			}

		break;  //itemlist


		case "itemlistsort":
			if($access['leute']['MAX'] < 1) break;

			//Modus finden
			if($_SESSION["show"] == "show_my_list") $mode = "my_list";
			else $mode = "liste";

			$state = $_GET["state"] == "true" ? "checked" : "";
			if($state == "checked") {
				ko_save_userpref($_SESSION["ses_userid"], "sort_cols_leute", 1);
				//Sort the cols according to the list-order
				$new_value = NULL;
				$cols = ko_get_leute_col_name($groups_hierarchie=TRUE, TRUE);
				foreach($cols as $i_i => $i) {
					if(in_array($i_i, $_SESSION["show_leute_cols"])) $new_value[] = $i_i;
				}
				$_SESSION["show_leute_cols"] = $new_value;
			} else {
				ko_save_userpref($_SESSION["ses_userid"], "sort_cols_leute", 0);
			}

			print "main_content@@@";
			print ko_list_personen($mode, FALSE);
		break;  //itemlistsort


		case "movecolleft":
		case "movecolright":
			if($access['leute']['MAX'] < 1) break;

			//Modus finden
			if($_SESSION["show"] == "show_my_list") $mode = "my_list";
			else $mode = "liste";

			//ID and state of the clicked field
			$col = format_userinput($_GET["col"], "js");

			$new_value = NULL;
			$cols = $_SESSION["show_leute_cols"];

			$add = FALSE;
			if($action == "movecolleft") $cols = array_reverse($cols);
			//test for overflow and if not last element, proceed
			if(end($cols) != $col) {
				reset($cols);
				foreach($cols as $i) {
					if($add) {
						$new_value[] = $i;
						$new_value[] = $col;
						$add = FALSE;
					} else {
						if($i == $col) $add = TRUE;
						else $new_value[] = $i;
					}
				}
			} else {
				//already last element, so don't do anything
				$new_value = $cols;
			}
			if($action == "movecolleft") $new_value = array_reverse($new_value);

			$_SESSION["show_leute_cols"] = $new_value;
			
			//Save userpref
			ko_save_userpref($_SESSION["ses_userid"], "show_leute_cols", implode(",", $_SESSION["show_leute_cols"]));

			print "main_content@@@";
			print ko_list_personen($mode, FALSE);
		break;  //movecolleft|right



		case "itemlistsave":
			//Modus finden
			if($_SESSION["show"] == "show_my_list") $mode = "my_list";
			else if($_SESSION['show'] == 'geburtstagsliste') $mode = 'birthdays';
			else $mode = "liste";

			//Find position of submenu for redraw
			if(in_array("itemlist_spalten", explode(",", $_SESSION["submenu_left"]))) $pos = "left";
			else $pos = "right";

			//save new value
			if($_GET["name"] == "") break;
			$global = $_GET['global'] == 'true';
			if($_SESSION["show"] == "list_kg") {
				if($global) $kg_all_rights = ko_get_access_all('kg', '', $kg_max_rights);
				$new_value = implode(",", $_SESSION["kota_show_cols_ko_kleingruppen"]);
				//Set user_id to -1 if to be stored globally
				$user_id = ($global && $kg_max_rights > 2) ? '-1' : $_SESSION['ses_userid'];
				ko_save_userpref($user_id, format_userinput($_GET["name"], "js", FALSE, 0, array("allquotes")), $new_value, "leute_kg_itemset");

				print submenu_leute("itemlist_spalten_kg", $pos, "open", 2);
			} else if($_SESSION["show"] == "chart") {
				if($access['leute']['MAX'] < 1) break;
				$new_value = implode(",", $_SESSION["show_leute_chart"]);
				$user_id = ($access['leute']['MAX'] > 3 && $global) ? '-1' : $_SESSION['ses_userid'];
				ko_save_userpref($user_id, format_userinput($_GET["name"], "js", FALSE, 0, array("allquotes")), $new_value, "leute_chart_itemset");

				print submenu_leute("itemlist_chart", $pos, "open", 2);
			} else {
				if($access['leute']['MAX'] < 1) break;
				$new_value = implode(",", $_SESSION["show_leute_cols"]);
				//Set user_id to -1 if to be stored globally
				$user_id = ($access['leute']['MAX'] > 3 && $global) ? '-1' : $_SESSION['ses_userid'];
				ko_save_userpref($user_id, format_userinput($_GET["name"], "js", FALSE, 0, array("allquotes")), $new_value, "leute_itemset");

				print submenu_leute("itemlist_spalten", $pos, "open", 2);
			}

		break;


		case "itemlistopen":
			//Modus finden
			if($_SESSION["show"] == "show_my_list") $mode = "my_list";
			else if($_SESSION['show'] == 'geburtstagsliste') $mode = 'birthdays';
			else $mode = "liste";

			//Find position of submenu for redraw
			if(in_array("itemlist_spalten", explode(",", $_SESSION["submenu_left"]))) $pos = "left";
			else $pos = "right";

			//save new value
			$name = format_userinput($_GET["name"], "js", FALSE, 0, array(), '@');
			if($name == "") break;

			if($_SESSION["show"] == "list_kg") {
				if($name == '_all_') {
					$cols = $KOTA['ko_kleingruppen']['_listview'];
					$kgcols = array();
					foreach($cols as $c) {
						$kgcols[] = $c['name'];
					}
					$_SESSION['kota_show_cols_ko_kleingruppen'] = $kgcols;
				} else if($name == '_none_') {
					$_SESSION['kota_show_cols_ko_kleingruppen'] = array();
				} else {
					if(mb_substr($name, 0, 3) == '@G@') $value = ko_get_userpref('-1', mb_substr($name, 3), "leute_kg_itemset");
					else $value = ko_get_userpref($_SESSION['ses_userid'], $name, "leute_kg_itemset");
					$_SESSION["kota_show_cols_ko_kleingruppen"] = explode(",", $value[0]["value"]);
				}
				ko_save_userpref($_SESSION['ses_userid'], 'kota_show_cols_ko_kleingruppen', implode(',', $_SESSION['kota_show_cols_ko_kleingruppen']));

				print "main_content@@@";
				print ko_list_kg(FALSE);
				print "@@@";
				print submenu_leute("itemlist_spalten_kg", $pos, "open", 2);
			} else if($_SESSION["show"] == "chart") {
				if($access['leute']['MAX'] < 1) break;
				if($name == '_all_') {
					$_SESSION['show_leute_chart'] = $LEUTE_CHART_TYPES;
				} else if($name == '_none_') {
					$_SESSION['show_leute_chart'] = array();
				} else {
					if(mb_substr($name, 0, 3) == '@G@') $value = ko_get_userpref('-1', mb_substr($name, 3), "leute_chart_itemset");
					else $value = ko_get_userpref($_SESSION['ses_userid'], $name, "leute_chart_itemset");
					$_SESSION["show_leute_chart"] = explode(",", $value[0]["value"]);
				}
				ko_save_userpref($_SESSION['ses_userid'], 'show_leute_chart', implode(',', $_SESSION['show_leute_chart']));

				print "main_content@@@";
				print ko_leute_chart();
				print "@@@";
				print submenu_leute("itemlist_chart", $pos, "open", 2);
			} else {
				if($access['leute']['MAX'] < 1) break;
				if($name == '_all_') {
					$cols = ko_get_leute_col_name();
					//Remove small group and group columns
					foreach($cols as $k => $v) {
						if(mb_substr($k, 0, 6) == 'MODULE') unset($cols[$k]);
					}
					$_SESSION['show_leute_cols'] = array_keys($cols);
				} else if($name == '_none_') {
					$_SESSION['show_leute_cols'] = array();
				} else {
					//global or user itemlist
					if(mb_substr($name, 0, 3) == '@G@') $value = ko_get_userpref('-1', mb_substr($name, 3), "leute_itemset");
					else $value = ko_get_userpref($_SESSION['ses_userid'], $name, "leute_itemset");
					$_SESSION["show_leute_cols"] = explode(",", $value[0]["value"]);
				}
				ko_save_userpref($_SESSION['ses_userid'], 'show_leute_cols', implode(',', $_SESSION['show_leute_cols']));

				print "main_content@@@";
				print ko_list_personen($mode, FALSE);
				print "@@@";
				print submenu_leute("itemlist_spalten", $pos, "open", 2);
			}

		break;


		case "itemlistdelete":
			//Modus finden
			if($_SESSION["show"] == "show_my_list") $mode = "my_list";
			else if($_SESSION['show'] == 'geburtstagsliste') $mode = 'birthdays';
			else $mode = "liste";

			//Find position of submenu for redraw
			if(in_array("itemlist_spalten", explode(",", $_SESSION["submenu_left"]))) $pos = "left";
			else $pos = "right";

			//save new value
			$name = format_userinput($_GET["name"], "js", FALSE, 0, array(), '@');
			if($name == "") break;

			if($_SESSION["show"] == "list_kg") {
				if(mb_substr($name, 0, 3) == '@G@') {
					$kg_all_rights = ko_get_access_all('kg', '', $kg_max_rights);
					if($kg_max_rights > 2) ko_delete_userpref('-1', mb_substr($name, 3), "leute_kg_itemset");
				} else ko_delete_userpref($_SESSION['ses_userid'], $name, "leute_kg_itemset");
				print submenu_leute("itemlist_spalten_kg", $pos, "open", 2);
			} else if($_SESSION["show"] == "chart") {
				if($access['leute']['MAX'] < 1) break;
				if(mb_substr($name, 0, 3) == '@G@') {
					if($access['leute']['MAX'] > 3) ko_delete_userpref('-1', mb_substr($name, 3), "leute_chart_itemset");
				} else ko_delete_userpref($_SESSION['ses_userid'], $name, "leute_chart_itemset");
				print submenu_leute("itemlist_chart", $pos, "open", 2);
			} else {
				if($access['leute']['MAX'] < 1) break;
				if(mb_substr($name, 0, 3) == '@G@') {
					if($access['leute']['MAX'] > 3) ko_delete_userpref('-1', mb_substr($name, 3), "leute_itemset");
				} else ko_delete_userpref($_SESSION['ses_userid'], $name, "leute_itemset");
				print submenu_leute("itemlist_spalten", $pos, "open", 2);
			}

		break;


		case "updatedfform":
			if($access['groups']['MAX'] < 2) break;

			$_GET["groups"] = str_replace("A", ",", $_GET["groups"]);
			if(FALSE === $groups = format_userinput($_GET["groups"], "intlist", TRUE, 0, array(), "g:r")) break;
			$id = format_userinput($_GET["id"], "uint");

			print "datafields_form@@@";
			print ko_groups_render_group_datafields($groups, $id);
		break;  //updatedfform



		case "showdeleted":
			if($access['leute']['MAX'] < 3) break;

			//Modus immer auf liste
			$mode = "liste";

			$state = $_GET["state"] == "true" ? "checked" : "";
			if($state == "checked") {
				ko_save_userpref($_SESSION["ses_userid"], "leute_show_deleted", 1);
			} else {
				ko_save_userpref($_SESSION["ses_userid"], "leute_show_deleted", 0);
			}

			$_SESSION['show_start'] = 1;
			print "main_content@@@";
			print ko_list_personen($mode, FALSE);
		break;  //showdeleted



		case "showhidden":
			if($access['leute']['MAX'] < 1) break;

			//Modus immer auf liste
			$mode = "liste";

			if($_GET['state'] == 'true') {
				ko_save_userpref($_SESSION["ses_userid"], "leute_show_hidden", 1);
			} else {
				ko_save_userpref($_SESSION["ses_userid"], "leute_show_hidden", 0);
			}

			print "main_content@@@";
			print ko_list_personen($mode, FALSE);
		break;  //showhidden



		case 'peoplesearch':
			if($access['leute']['MAX'] < 1) break;

			$limit = 30;

			$string = format_userinput($_GET['string'], 'text');
			if(!$string || mb_strlen($string) < 3) {
				print '';
				break;
			}

			list($mode, $token) = explode('-', $_GET['token']);
			if($mode == 'all' && $token != '' && $token == $_SESSION['peoplesearch_access_token']) {
				$accessAll = TRUE;
			} else {
				$accessAll = FALSE;
			}

			$input_name = format_userinput(mb_substr($_GET['name'], 0, strrpos($_GET['name'], '[')), 'text');
			$name = str_replace('txt_', 'sel_ds1_', $input_name);
			$filter = unserialize(ko_get_setting('ps_filter_'.$name));
			apply_leute_filter($filter, $base_where, ($access['leute']['ALL'] < 1 && !$accessAll));


			//Apply filters set in KOTA
			list($temp, $table, $field) = explode('[', $input_name);
			$table = mb_substr($table, 0, -1);
			$field = mb_substr($field, 0, -1);
			if(!isset($KOTA[$table][$field])) {
				ko_include_kota(array($table));
			}
			if($KOTA[$table][$field]['form']['additional_where']) {
				$kota_where = $KOTA[$table][$field]['form']['additional_where'];
			} else {
				$kota_where = '';
			}


			$parts = explode(' ', $string);
			$where_parts = array();
			foreach($parts as $s) {
				if(!$s) continue;
				$where_parts[] = " (`vorname` LIKE '%$s%' OR `nachname` LIKE '%$s%' OR `firm` LIKE '%$s%') ";
			}
			$z_where = implode(' AND ', $where_parts).' '.$base_where.' '.$kota_where;
			$people = db_select_data('ko_leute', "WHERE $z_where", '*', 'ORDER BY nachname, vorname ASC');
			if(sizeof($people) > $limit) {
				print '<option disabled="disabled" value="">'.getLL('peoplesearch_toomany').'</option>';
			} else if(sizeof($people) == 0) {
				print '<option disabled="disabled" value="">'.getLL('peoplesearch_none').'</option>';
			} else {
				$class = 'odd';
				foreach($people as $p) {
					$title  = $p['firm'].' '.$p['vorname'].' '.$p['nachname'];
					$title .= $p['adresse'] != '' ? ' - '.$p['adresse'] : '';
					$title .= $p['plz'] != '' || $p['ort'] != '' ? ' - '.$p['plz'].' '.$p['ort'] : '';
					$title = trim(format_userinput($title, 'js'));
					$label = trim(format_userinput($p['firm'].' '.$p['vorname'].' '.$p['nachname'], 'js'));
					print '<option class="peoplesearchresultentry '.$class.'" value="'.$p['id'].'" label="'.$label.'" title="'.$title.'">'.$label.'</option>';

					$class = $class == 'odd' ? 'even' : 'odd';
				}
			}
		break;


		case "history":
			$id = format_userinput($_GET["id"], "uint");
			if($access['leute']['ALL'] < 2 && $access['leute'][$id] < 2) break;

			$limit = format_userinput($_GET["limit"], "uint");
			if(!$limit) $limit = 5;
			$step = 10;
			$start = 0;

			if($_SESSION["leute_version"]) {
				$z_where = "AND `leute_id` = '$id' AND `date` <= '".date("Y-m-d", strtotime($_SESSION["leute_version"]))."'";
			} else {
				$z_where = "AND `leute_id` = '$id'";
			}
			//Don't display root's changes to other users
			if($_SESSION["ses_userid"] != ko_get_root_id()) $z_where .= " AND `user_id` != '".ko_get_root_id()."' ";
			//Get all versions for this user
			$versions = db_select_data("ko_leute_changes", "WHERE 1=1 $z_where", "*", 'ORDER BY `date` DESC');
			$total_num = sizeof($versions);

			//Get current data
			ko_get_person_by_id($id, $data);
			$cur_data = $data;
			$df = ko_get_datafields($id);

			//Find creation date
			$created = ($cur_data['crdate'] != '' && $cur_data['crdate'] != '0000-00-00 00:00:00');

			if($total_num == 0 && !$created) {
				//Only show this message if no changes and also no creation date was found
				$value = getLL("leute_labels_no_history");
			}
			else {
				//Get leute col names
				$leute_col_name = ko_get_leute_col_name($groups_hierarchie=FALSE, $add_group_datafields=TRUE);
				$_db_columns = db_get_columns('ko_leute');
				$db_columns = array();
        foreach($_db_columns as $col) {
          $db_columns[] = $col['Field'];
        }
				unset($_db_columns);

				//Display version history
				$diff_value = "";
				$done = 0;
				foreach($versions as $version) {
					//Apply limit
					if($done >= $limit) continue;

					$data_old = unserialize($version["changes"]);
					$df_old = unserialize($version["df"]);

					$df_done = FALSE;
					if($version["user_id"] == ko_get_root_id() && $_SESSION["ses_userid"] != ko_get_root_id()) {
						//dont display root's changes to others
					} else {
						$do_row = FALSE;
						$row_value  = '<tr>';
						//Rollback
						$row_value .= '<td width="16">';
						$row_value .= '<a href="?action=rollback&amp;v='.$version["id"].'">';
						$row_value .= '<img src="'.$ko_path.'images/undelete.png" border="0" alt="Rollback" title="'.getLL("leute_labels_rollback").'" /></a>';
						$row_value .= '</td>';
						//change date
						$row_value .= '<td width="80">';
						$row_value .= '<span title="'.strftime('%H:%M', strtotime($version['date'])).' '.getLL('time_oclock').'">'.strftime($DATETIME["dmY"], strtotime($version["date"])).'</span>';
						$row_value .= '</td><td width="140">';
						//change user
						ko_get_login($version["user_id"], $login);
						$row_value .= $login["login"];
						$row_value .= "</td><td>";

						//Changes and new values (deletions are handled below)
						$diff = array_diff($data, $data_old);

						//Check for changes in datafields
						foreach($df as $dfid => $dfdata) {
							//Check whether this df has been set in the old version already
							if(isset($df_old[$dfid])) {
								$dfdiff = array_diff($dfdata, $df_old[$dfid]);
								if(sizeof($dfdiff) > 0) {
									$diff["_df_".$dfid] = array("old" => $df_old[$dfid], "new" => $dfdata);
								}
							} else {  //Newly added df
								$diff["_df_".$dfid] = array("old" => array(), "new" => $dfdata);
							}
						}

						if (sizeof($df) == 0) {
							foreach($df_old as $dfid => $df_old_data) {
								//Check whether this df has been set in the old version already
								if(isset($df[$dfid])) {
									$dfdiff = array_diff($df_old_data, $df[$dfid]);
									if(sizeof($dfdiff) > 0) {
										$diff["_df_".$dfid] = array("old" => $df_old_data, "new" => $df[$dfid]);
									}
								} else {  //Newly added df
									$diff["_df_".$dfid] = array("old" => $df_old_data, "new" => array());
								}
							}
						}

						foreach($diff as $c => $d) {
							//Don't treat columns not in ko_leute (anymore) (but go on for _df_ columns (changed datafields))
							if(mb_substr($c, 0, 4) != '_df_' && !in_array($c, $db_columns)) continue;

							//Entry deleted
							if($c == "deleted" && $d == 1) {
								$row_value .= '<b>'.getLL("leute_labels_deleted").'</b>, ';
								$do_row = TRUE;
							}
							//Exclude unwanted cols (like hidden, lastchange, etc)
							if(in_array($c, $LEUTE_EXCLUDE) && $c != 'famid') continue;

							//special columns
							//groups
							if($c == "groups") {
								ko_groups_get_savestring($d, array("id" => $id), $log, $data_old[$c], $apply_start_stop=FALSE, $store=FALSE);
								$row_value .= '<b>'.getLL("groups").'</b>:'.mb_substr($log, 0, -2).", ";
								$do_row = TRUE;
							}
							//rota (keep to display old changes)
							else if($c == "dienst" || $c == "dienstleiter") {
								$add = array();
								foreach(explode(",", $d) as $e) {
									if(!$e) continue;
									if(!in_array($e, explode(",", $data_old[$c]))) $add[] = $e;
								}
								if(sizeof($add) > 0) {
									$row_value .= '<b>'.$leute_col_name[$c].'</b>:';
									$row_value .= " +".ko_dienstliste(implode(", ", $add)).", ";
									$do_row = TRUE;
								}
							}
							//small groups
							else if($c == "smallgroups") {
								$add = array();
								foreach(explode(",", $d) as $e) {
									if(!$e) continue;
									if(!in_array($e, explode(",", $data_old[$c]))) $add[] = $e;
								}
								if(sizeof($add) > 0) {
									$row_value .= '<b>'.$leute_col_name[$c].'</b>:';
									$row_value .= ' +'.ko_kgliste(implode(',', $add)).', ';
									$do_row = TRUE;
								}
							}
							//Family
							else if($c == 'famid') {
								$fam = ko_get_familie($data['famid']);
								$fam_old = ko_get_familie($data_old['famid']);
								$row_value .= '<b>'.$leute_col_name[$c].'</b>: ';
								$row_value .= $fam_old['id'].' &rarr; '.$fam['id'].', ';
								$do_row = TRUE;
							}
							//Datafields
							else if(mb_substr($c, 0, 4) == "_df_") {
								if($d["old"]["value"] == "" && $d["new"]["value"] == "") continue;
								//Title
								if(!$df_done) {
									$row_value .= '<b>'.getLL("form_groups_datafields").'</b>: ';
									$df_done = TRUE;
								}
								$dfdiff = array_diff($d["new"], $d["old"]);
								$dfdiffR = array_diff($d["old"], $d["new"]);

								// if datafields have been added or changed
								if (sizeof($dfdiff) > 0) {
									//Add group's and df's name
									if(!is_array($all_datafields)) $all_datafields = db_select_data("ko_groups_datafields", "WHERE 1=1", "*");
									if(!is_array($all_groups)) ko_get_groups($all_groups);
									$row_value .= $all_groups[$d["new"]["group_id"]]["name"]." (".$all_datafields[$d["new"]["datafield_id"]]["description"]."): ";
									//show all changes
									foreach($dfdiff as $dfc => $dfv) {
										if($dfc == "value") {
											if($all_datafields[$d["new"]["datafield_id"]]["type"] == "checkbox") {
												$row_value .= ($d["old"]["value"] == "1" ? getLL("yes") : getLL("no"))." &rarr; ".($d["new"]["value"] == "1" ? getLL("yes") : getLL("no")).", ";
											} else {
												$row_value .= $d["old"]["value"]." &rarr; ".$d["new"]["value"].", ";
											}
										} else if($dfc == "deleted") {
											if($d["new"]["deleted"] == 1) $row_value .= getLL("leute_labels_deleted").", ";
										}
									}
								}
								// if datafields have been deleted
								else if (sizeof($dfdiff) == 0 && sizeof($dfdiffR) > 0) {
									//Add group's and df's name
									if(!is_array($all_datafields)) $all_datafields = db_select_data("ko_groups_datafields", "WHERE 1=1", "*");
									if(!is_array($all_groups)) ko_get_groups($all_groups);
									$row_value .= $all_groups[$d["old"]["group_id"]]["name"]." (".$all_datafields[$d["old"]["datafield_id"]]["description"]."): ";
									//show all changes
									if($all_datafields[$d["old"]["datafield_id"]]["type"] == "checkbox") {
										$row_value .= ($d["old"]["value"] == "1" ? getLL("yes") : getLL("no"))." &rarr; h";
									} else {
										$row_value .= $d["old"]["value"]." &rarr; h";
									}
								}
								$do_row = TRUE;
							}
							//normal columns
							else {
								$ll = getLL('kota_ko_leute_'.$c.'_'.$d);
								$ll = $ll ? $ll : $d;
								$row_value .= "<b>".$leute_col_name[$c]."</b>: ".$data_old[$c]." &rarr; $ll, ";
								if($ll != $data_old[$c]) $do_row = TRUE;
							}
						}

						//find deleted values
						$diff_sub = array_diff($data_old, $data);
						foreach($diff_sub as $c => $d) {
							//Don't treat columns not in ko_leute (anymore)
							if(!in_array($c, $db_columns)) continue;

							if($d == '0') $d = '';
							if($data[$c] == '0') $data[$c] = '';

							//Entry deleted
							if($c == "deleted" && $d == 0) {
								$row_value .= '<b>'.getLL("leute_labels_undeleted").'</b>, ';
								$do_row = TRUE;
							}
							//Exclude unwanted cols (like hidden, lastchange, etc)
							if(in_array($c, $LEUTE_EXCLUDE) && $c != 'famid') continue;

							//special columns
							//groups
							if($c == "groups") {
								//Deletion of groups only have to be handled here if it was the last group that was deleted from this person's record
								if($data["groups"] == "" && $data_old["groups"] != "") {
									$row_value .= '<b>'.getLL("groups").'</b>:';
									foreach(explode(",", $data_old[$c]) as $gid) {
										$row_value .= ' -'.ko_groups_decode($gid, "group_desc").", ";
									}
								}
								$do_row = TRUE;
							}
							//Rota (keep to display old changes)
							else if($c == "dienst" || $c == "dienstleiter") {
								$add = array();
								foreach(explode(",", $d) as $e) {
									if(!$e) continue;
									if(!in_array($e, explode(",", $data[$c]))) $add[] = $e;
								}
								if(sizeof($add) > 0) {
									$row_value .= '<b>'.$leute_col_name[$c].'</b>:';
									$row_value .= " -".ko_dienstliste(implode(", ", $add)).", ";
									$do_row = TRUE;
								}
							}
							//small groups
							else if($c == "smallgroups") {
								$add = array();
								foreach(explode(",", $d) as $e) {
									if(!$e) continue;
									if(!in_array($e, explode(",", $data[$c]))) $add[] = $e;
								}
								if(sizeof($add) > 0) {
									$row_value .= '<b>'.$leute_col_name[$c].'</b>:';
									$row_value .= " -".ko_kgliste(implode(", ", $add)).", ";
									$do_row = TRUE;
								}
							}
							//Family
							else if($c == 'famid') {
								$row_value .= '<b>'.$leute_col_name[$c].'</b>: ';
								$fam = ko_get_familie($data_old['famid']);
								$row_value .= $fam['id'].' &rarr;, ';
								$do_row = TRUE;
							}
							//normal columns
							else {
								//Only handle entries with one empty value (old or new) as the changes have been handled above
								if($d != "" && $data[$c] != "") continue;

								$ll = getLL('kota_ko_leute_'.$c.'_'.$d);
								$ll = $ll ? $ll : $d;
								$row_value .= "<b>".$leute_col_name[$c]."</b>: ".$d." &rarr; ".$data[$c].", ";
								if($ll != $data[$c]) $do_row = TRUE;
							}
						}

						$row_value = mb_substr($row_value, 0, -2).'</td></tr>';

						if($do_row) {
							$diff_value .= $row_value;
							$done++;
						} else {
							$total_num--;
						}
					}

					$data = $data_old;
					$df = $df_old;
				}
				//Add creation date
				if($cur_data["crdate"] != "" && $cur_data["crdate"] != "0000-00-00 00:00:00") {
					$total_num++;
					if($limit >= $total_num) {  //Only show crdate on last page
						$diff_value .= '<tr>';
						$diff_value .= '<td width="16">&nbsp;</td>';  //No undo button
						$diff_value .= '<td width="80">'.strftime($DATETIME["dmY"], strtotime($cur_data["crdate"])).'</td>';
						if($cur_data["cruserid"]) {
							ko_get_login($cur_data["cruserid"], $login);
							$diff_value .= '<td width="140">'.$login['login'].'</td>';
						} else {
							$diff_value .= "<td>-</td>";
						}
						$diff_value .= "<td>".getLL("leute_labels_crdate")."</td>";
						$diff_value .= '</tr>';
					}
				}


				//Title
				$help = ko_get_help("leute", "version_history");
				$header_value  = $help["link"]."&nbsp;";
				$header_value .= getLL("leute_labels_version_history_title");

				//paging stats
				if(($start+$limit) >= $total_num) {
					$stats_value = ($start+1)."-".min(($start+$limit), $total_num)." ".getLL("list_oftotal")." ".$total_num;
				} else {
					$stats_value = ($start+1)."-".min(($start+$limit), $total_num)." ".getLL("list_oftotal")." ".$total_num;
					$stats_value .= '<input type="image" src="'.$ko_path.'images/icon_arrow_right.png" onclick="sendReq(\'../leute/inc/ajax.php\', \'action,id,limit,sesid\', \'history,'.$id.','.($limit+$step).','.session_id().'\', do_element); return false;" />';
				}


				//Put everything together
				$value  = '<div style="font-weight:900;">';
				$value .= $header_value;
				$value .= '<span style="font-weight: 100; font-size: 85%; margin: 0 5px 0 10px;">'.$stats_value.'</span>';
				$value .= '</div>';
				$value .= '<table width="100%" class="people-version" cellpadding="2">';
				$value .= $diff_value;
				$value .= '</table>';
			}//if..else(total_num == 0)

			print "version_$id@@@$value";
		break;  //history


		case "leutechartroles":
			$gid = format_userinput($_GET["gid"], "uint");
			$_SESSION["leute_chart_roles_gid"] = $gid;

			print "leute_chart_roles@@@";
			print ko_leute_chart("roles");
		break;

		case "leutechartsubgroups":
			$gid = format_userinput($_GET['gid'], 'group_role');
			$_SESSION["leute_chart_subgroups_gid"] = $gid;

			print "leute_chart_subgroups@@@";
			print ko_leute_chart("subgroups");
		break;

		case 'mailmergereuse':
			$id = format_userinput($_GET['id'], 'uint');

			$letter = db_select_data('ko_mailmerge', 'WHERE `id` = \''.$id.'\' AND `user_id` = \''.$_SESSION['ses_userid'].'\'', '*', '', '', TRUE);
			if(!$letter['id']) print '';
			else print $letter['preset'].'@@@'.$letter['salutation'].'@@@'.$letter['subject'].'@@@'.$letter['text'].'@@@'.$letter['closing'].'@@@'.$letter['signature'].'@@@'.$letter['sig_file'];
		break;


		case 'addkgtracking':
			$kg_all_rights = ko_get_access_all('kg', '', $kg_max_rights);
			if($kg_max_rights < 1) break;

			$id = format_userinput($_GET['id'], 'uint');

			$kg = db_select_data('ko_kleingruppen', 'WHERE `id` = \''.$id.'\'', '*', '', '', TRUE);
			if(!$kg['id']) break;

			$mapWeekdays = array('monday' => 1, 'tuesday' => 2, 'wednesday' => 3, 'thursday' => 4, 'friday' => 5, 'saturday' => 6, 'sunday' => 0);

			$tracking = array('name' => $kg['name'],
												'mode' => 'simple',
												'filter' => $kg['id'],
												'date_weekdays' => $mapWeekdays[$kg['wochentag']]
												);
			db_insert_data('ko_tracking', $tracking);
			ko_log_diff('new_tracking', $tracking);

			print 'main_content@@@';
			print ko_list_kg(FALSE);
		break;


		//Create word document for given address id
		case 'addressdoc':
			$pid = format_userinput($_GET['pid'], 'uint');
			if($access['leute']['ALL'] < 1 && $access['leute'][$pid] < 1) break;

			$filename = ko_word_address($pid);
			print 'DOWNLOAD@@@'.mb_substr($ko_path.'download/word/'.$filename, 3);
		break;


		case 'savefpalias':
			if($access['leute']['MAX'] < 1) break;
			if(!ko_module_installed('mailing')) break;

			//Position des Filter-SM finden
			if(in_array('filter', explode(',', $_SESSION['submenu_left']))) $pos = 'left';
			else $pos = 'right';

			//Get preset id
			$fpid = format_userinput($_GET['fpid'], 'uint');
			if(!$fpid) break;

			//Get alias and check for uniqueness
			$alias = str_replace('@', '', format_userinput($_GET['alias'], 'email'));
			$ok = kota_mailing_check_unique_alias($alias, array('table' => 'ko_userprefs', 'id' => $fpid));
			if(!$ok) {
				print 'ERROR@@@'.getLL('mailing_error_7');
				break;
			}

			//Get filterset and check for valid
			$filterset = db_select_data('ko_userprefs', "WHERE `id` = '$fpid'", '*', '', '', TRUE);
			if(!$filterset['id'] || $filterset['id'] != $fpid || $filterset['type'] != 'filterset') break;

			db_update_data('ko_userprefs', "WHERE `id` = '$fpid'", array('mailing_alias' => $alias));

			//Update cached global array of userprefs so submenu below uses new value
			$filterset['mailing_alias'] = $alias;
			$GLOBALS['kOOL']['ko_userprefs']['TYPE@filterset'][$filterset['key']] = $filterset;

			//Redraw whole submenu
			print submenu_leute('filter', $pos, 'open', 2);
		break;

	}//switch(action);

	hook_ajax_post($ko_menu_akt, $action);

}//if(GET[action])
?>
