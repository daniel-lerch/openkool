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


//KG-Modul einfügen, damit es nicht manuell eingefügt werden muss
if(ko_module_installed('kg') && file_exists(__DIR__ . '/kg.inc.php'))
	include __DIR__ . '/kg.inc.php';


//Define basic chart types for leute module (may be extended by plugins)
$LEUTE_CHART_TYPES = array('roles', 'subgroups', 'age_bar', 'lastchange', 'age_pie', 'birthday_months', 'sex', 'famfunction', 'city', 'zip', 'country');

$mailmerge_signature_ext = array('gif', 'jpg', 'jpeg', 'png', 'pdf');


function ko_formular_leute($mode, $id=0, $show_save_as_new=true) {
	global $smarty, $ko_path, $KOTA;
	global $LEUTE_LAYOUT, $LEUTE_EXCLUDE, $LEUTE_ENUMPLUS, $LEUTE_TEXTSELECT, $LEUTE_EMAIL_FIELDS, $LEUTE_MOBILE_FIELDS;
	global $FAMILIE_EXCLUDE, $COLS_LEUTE_UND_FAMILIE;
	global $js_calendar;
	global $access;
	global $SMALLGROUPS_ROLES, $RECTYPES;
	global $LEUTE_NO_FAMILY;

	if($mode == "edit") {
		ko_get_person_by_id($id, $person);
		if(!$person["id"] || $person["deleted"] == "1") return false;
	}

	//Datenbank-Spalten auslesen
	$leute_cols = db_get_columns("ko_leute");
	foreach($leute_cols as $c_i => $c) {
		if(!in_array($c["Field"], $LEUTE_EXCLUDE)) {
			$endpos = mb_strpos($c["Type"], "(") ? mb_strpos($c["Type"], "(") : mb_strlen($c["Type"]);
			$endpos2 = mb_strpos($c["Type"], ")") ? mb_strpos($c["Type"], ")") : mb_strlen($c["Type"]);
			$input_type[$c["Field"]] = mb_substr($c["Type"], 0, $endpos);
			$input_size[$c["Field"]] = ($endpos && $endpos2) ? mb_substr($c["Type"], ($endpos+1), ($endpos2-$endpos-1)) : 0;
		}
	}

	//get the cols, for which this user has edit-rights (saved in allowed_cols[edit])
	$allowed_cols = ko_get_leute_admin_spalten($_SESSION['ses_userid'], 'all', ($id === 0 ? -1 : $id));


	//Familien-Daten
	if(!is_array($allowed_cols["view"]) || in_array("famid", $allowed_cols["view"])) {
		ko_get_familien($familien);

		//Familien-Select
		$fam_sel["values"][] = "0";
		$fam_sel["descs"][] = getLL("form_leute_none");
		foreach($familien as $f) {
			$fam_sel["values"][] = $f["famid"];
			$fam_sel["descs"][] = $f["id"];
		}

		//Familien-Funktion
		$ffs = db_get_enums_ll("ko_leute", "famfunction");
		foreach($ffs as $f => $ll) {
			$function_sel["values"][] = $f;
			$function_sel["descs"][] = $ll;
		}
		//Aktive Funktion selektieren
		$function_sel["sel"] = $person["famfunction"];

		//Familie Ja oder Nein
		if($person["famid"] != 0) {
			$smarty->assign("fam", true);

			//Familien-ID
			$familie = ko_get_familie($person["famid"]);
			$smarty->assign("fam_id", $familie["id"]);

			//Aktive Familie selektieren
			$fam_sel["sel"] = $person["famid"];
		}//if(person[famid] != 0)

		else {
			$smarty->assign("fam", false);
		}
		$smarty->assign("fam_sel", $fam_sel);
		$smarty->assign("famfunction", $function_sel);

		//Familien-Felder (für edit und neu)
		$familie_col_name = ko_get_family_col_name();
		$familien_cols = db_get_columns("ko_familie");
		$fc = 0;
		foreach($familien_cols as $col_) {
			$col = $col_["Field"];
			$type = $col_["Type"];
			if(!in_array($col, array_merge($FAMILIE_EXCLUDE, $COLS_LEUTE_UND_FAMILIE))) {
				$cols_familie[$fc]["desc"] = $familie_col_name[$col] ? $familie_col_name[$col] : $col;
				$cols_familie[$fc]["name"] = "input_".$col;
				if($mode == "edit" && $person["famid"] != 0) {
					$cols_familie[$fc]["value"] = $familie[$col];
				} else {
					$cols_familie[$fc]["value"] = "";
				}
				if(mb_substr($type, 0, 4) == "enum") {
					$cols_familie[$fc]["type"] = "select";
					$ffs = db_get_enums_ll("ko_familie", $col);
					foreach($ffs as $f => $ll) {
						$cols_familie[$fc]["values"][] = $f;
						$cols_familie[$fc]["descs"][] = $ll;
					}
				} else {
					$cols_familie[$fc]["type"] = "text";
				}
				$fc++;
			}
		}//foreach(familien_cols)
		$smarty->assign("cols_familie", $cols_familie);
		if(is_array($allowed_cols["edit"]) && !in_array("famid", $allowed_cols["edit"])) $smarty->assign("fam_params", ' disabled="disabled" ');
	}//if(in_array(famid, allowed_cols[view]))
	else {
		$smarty->assign("hide_fam", true);
	}



	//Spalten-Namen
	$leute_col_name = ko_get_leute_col_name(false, false, "all");

	$row_counter = 0;
	foreach($LEUTE_LAYOUT as $ll) {
		$inputs = array();
		$add_inputs = null; $add_counter = 0;
		$col_counter = 0;

		foreach($ll as $l) {

			if(mb_substr($l, 0, 3) == '---') {  //Divider
				if(mb_strlen($l) > 3) {
					$inputs[0]['desc'] = '  ';  //will be deleted
					$inputs[0]['type'] = 'header';
					$v = trim(mb_substr($l, 3));
					$inputs[0]['value'] = getLL($v) != '' ? getLL($v) : $v;
				} else {
					$inputs[0]['desc'] = '  ';  //will be deleted
					$inputs[0]['type'] = '   ';
				}
			}

			//Exclude MODULE::kg here, as this is handled by smallgroups below since R40
			else if(mb_substr($l, 0, 8) == "MODULE::" && $l != 'MODULE::kg') {
				$module = mb_substr($l, 8);
				switch($module) {

					/* KG-Seit-Modul */
					case "kg_seit":
						if(!ko_module_installed("kg") || $access['kg']['MAX'] < 2) break;
						//Dabei in Kleingruppen seit
						$inputs[$col_counter]["desc"] = getLL("form_leute_kg_since");
						$inputs[$col_counter]["type"] = "varchar";
						$inputs[$col_counter]["name"] = "input_kg_seit";
						$inputs[$col_counter]["value"] = $person["kg_seit"];
						if($access['kg']['MAX'] > 2) {
							$inputs[$col_counter]["params"] = '';
						} else {
							$inputs[$col_counter]["params"] = 'disabled="disabled"';
						}

						//Dabei als Kleingruppen-Leiter seit
						$col_counter++;
						$inputs[$col_counter]["desc"] = getLL("form_leute_kg_leader_since");
						$inputs[$col_counter]["type"] = "varchar";
						$inputs[$col_counter]["name"] = "input_kgleiter_seit";
						$inputs[$col_counter]["value"] = $person["kgleiter_seit"];
						if($access['kg']['MAX'] > 2) {
							$inputs[$col_counter]["params"] = '';
						} else {
							$inputs[$col_counter]["params"] = 'disabled="disabled"';
						}

					break;


					/* Groups-Modul */
					case "groups":
						if(!ko_module_installed("groups")) break;
						//Gruppen-Select der aktiven Gruppen
						$inputs[$col_counter]["desc"] = getLL("groups");
						$inputs[$col_counter]["type"] = "groupselect";
						$inputs[$col_counter]["name"] = "input_groups";
						$inputs[$col_counter]["colspan"] = 'colspan="2"';
						$inputs[$col_counter]["onclick_2_add"] = 'do_update_df_form(\''.$id.'\',\''.session_id().'\');';
						$inputs[$col_counter]["onclick_del_add"] = 'do_update_df_form(\''.$id.'\',\''.session_id().'\');';
						//Nur gültige IDs auslesen, Rest wird durch js-groupmenu.inc.php erledigt
						//Hier die alten Gruppen immer ausblenden
						$orig_value = ko_get_userpref($_SESSION['ses_userid'], 'show_passed_groups');
						ko_save_userpref($_SESSION['ses_userid'], 'show_passed_groups', 0);
						ko_get_groups($groups, ko_get_groups_zwhere());
						ko_save_userpref($_SESSION['ses_userid'], 'show_passed_groups', $orig_value);
						$valid_ids = array();
						$allow_assign = false;
						foreach($groups as $group) {
							if($access['groups']['ALL'] > 0 || $access['groups'][$group['id']] > 0) $valid_ids[] = $group['id'];
							if(!$allow_assign && ($access['groups']['ALL'] > 1 || $access['groups'][$group['id']] > 1)) $allow_assign = true;
						}
						$smarty->assign("allow_assign", $allow_assign);
						//Bestehende Werte einfüllen
						$do_datafields = null;
						$sort_assigned_groups = array();
						foreach(explode(",", $person["groups"]) as $group) {
							if($group) {
								$group_id = ko_groups_decode($group, "group_id");
								$group_desc = ko_groups_decode($group, "group_desc_full");
								if(in_array($group_id, $valid_ids)) {
									//Prepare for sorting
									$assigned_groups[$group] = $group_desc;
									$sort_assigned_groups[$group_desc] = $group;
									//prepare datafields
									if($group["datafields"]) $do_datafields[] = array_merge($groups[$group_id], array("desc_full" => $group_desc, "group_id" => $group_id));
								}
							}//if(group)
						}//foreach(person[groups])

						//Sort assigned groups alphabetically
						ksort($sort_assigned_groups);
						foreach($sort_assigned_groups as $group) {
							$inputs[$col_counter]["avalues"][] = $group;
							$inputs[$col_counter]["adescs"][] = $assigned_groups[$group];
						}

						if (!empty($inputs[$col_counter]['avalues'])) {
							$inputs[$col_counter]["avalue"] = implode(",", $inputs[$col_counter]["avalues"]);
						}
						if(sizeof($valid_ids) == 0) {
							$inputs[$col_counter]["params"] = 'disabled="disabled"';
						} else {
							$inputs[$col_counter]["params"] = '';
						}



						//Group datafields for selected groups
						$html = ko_groups_render_group_datafields($do_datafields, $id);

						$add_inputs[$add_counter][0]["desc"] = getLL("leute_group_datafield");
						$add_inputs[$add_counter][0]["type"] = "html";
						$add_inputs[$add_counter][0]["colspan"] = 'colspan="2"';
						$add_inputs[$add_counter][0]["value"] = $html;
						$add_counter++;



						//Groups-History
						$col_counter = 0;
						$history_groups_sort = array();
						$add_inputs[$add_counter][$col_counter]["desc"] = getLL("leute_group_bisher");
						$add_inputs[$add_counter][$col_counter]["type"] = "html";
						$add_inputs[$add_counter][$col_counter+1]["desc"] = "";
						$add_inputs[$add_counter][$col_counter+1]["type"] = "html";
						foreach(explode(",", $person["groups"]) as $group) {
							if(!$group) continue;
							$g = ko_groups_decode($group, "group");
							$g_id = ko_groups_decode($group, "group_id");
							if($g["stop"] != "0000-00-00" && (int)str_replace("-", "", $g["stop"]) <= (int)strftime("%Y%m%d", time()) && ($access['groups']['ALL'] > 0 || $access['groups'][$g_id] > 0)) {
								$desc_full = ko_groups_decode($g_id, 'group_desc_full');
								$history_groups[$g_id] = $group;
								$history_groups_sort[$g_id] = $desc_full;
							}
						}
						asort($history_groups_sort);
						$num = 0; $history = array();
						foreach($history_groups_sort as $gid => $start) {
							$group = db_select_data('ko_groups', "WHERE `id` = '$gid'", '*', '', '', true);
							$tooltip = '<p class="title">'.getLL('groups_datafields_list_title').'</p>';
							$found_df = FALSE;
							foreach(explode(',', $group['datafields']) as $dfid) {
								if(!$dfid) continue;
								$df = ko_get_datafield_data($gid, $dfid, $id, '', $temp1, $temp2);
								if(!$df['description']) continue;
								$found_df = TRUE;
								$dfvalue = $df['typ'] == 'checkbox' ? ($df['value'] == '1' ? getLL('yes') : getLL('no')) : ko_html($df['value']);
								$tooltip .= $df['description'].': <b>'.$dfvalue.'</b><br />';
							}

							$history[$num] .= '<div style="font-size:x-small;color:#444;" title="'.ko_groups_decode($history_groups[$gid], "group_description").'" ';
							if($found_df) $history[$num] .= 'onmouseover="tooltip.show(\''.ko_html($tooltip).'\');" onmouseout="tooltip.hide();"';
							$history[$num] .= '>';
							$history[$num] .= ko_groups_decode($history_groups[$gid], "group_desc_full");
							$history[$num] .= "</div>";
							$num++;
						}

						$col = 0;
						foreach($history as $h) {
							$add_inputs[$add_counter][$col]["value"] .= $h;
							$col = $col ? 0 : 1;
						}
					break;

					//Show a save button
					case "save":
						$inputs[$col_counter]["type"] = "_save";
					break;

				}//switch(module)
			}//if(MODULE)

			//Add backwards compatibility case for MODULE::kg (deprecated since R40)
			else if($l == 'smallgroups' || $l == 'MODULE::kg') {
				if(!ko_module_installed('kg') || $access['kg']['MAX'] < 2) continue;
				//Small group select
				$inputs[$col_counter]['desc'] = getLL('kota_ko_leute_smallgroups');
				$inputs[$col_counter]['type'] = 'doubleselect';
				$inputs[$col_counter]['show_moves'] = TRUE;
				$inputs[$col_counter]['name'] = 'input_smallgroups';
				$inputs[$col_counter]['colspan'] = 'colspan="2"';
				$smallgroups = db_select_data('ko_kleingruppen', 'WHERE 1=1', '*', 'ORDER BY name ASC');
				foreach($smallgroups as $sg) {
					foreach($SMALLGROUPS_ROLES as $role) {
						$inputs[$col_counter]['values'][] = $sg['id'].':'.$role;
						$inputs[$col_counter]['descs'][] = ko_html($sg['name'].': '.getLL('kg_roles_'.$role));
					}
				}
				//Show currently selected values
				foreach(explode(',', $person['smallgroups']) as $sg) {
					if(!$sg) continue;
					$inputs[$col_counter]['avalues'][] = $sg;
					list($kgid, $role) = explode(':', $sg);
					$inputs[$col_counter]['adescs'][] = $smallgroups[$kgid]['name'].': '.getLL('kg_roles_'.$role);
				}
				$inputs[$col_counter]['avalue'] = $person['smallgroups'];
				//Marks as disabled if not enough access rights
				if($access['kg']['MAX'] > 2) {
					$inputs[$col_counter]['params'] = 'size="8"';
				} else {
					$inputs[$col_counter]['params'] = 'size="8" disabled="disabled"';
				}
				$col_counter++;
			}//smallgroups

			//Select for recipient type
			else if($l == 'rectype') {
				if(sizeof($RECTYPES) > 0) {
					$inputs[$col_counter]['desc'] = $leute_col_name[$l];
					$inputs[$col_counter]['type'] = 'enum';
					$inputs[$col_counter]['name'] = 'input_'.$l;
					$inputs[$col_counter]['value'] = $person[$l];
					//Add default entry
					$inputs[$col_counter]['values'][] = '';
					$inputs[$col_counter]['descs'][] = getLL('kota_ko_leute_rectype_default');
					foreach($RECTYPES as $k => $v) {
						$inputs[$col_counter]['values'][] = $k;
						$ll_value = getLL('kota_ko_leute_'.$l.'_'.$k);
						$inputs[$col_counter]['descs'][] = $ll_value ? $ll_value : $k;
					}
					$col_counter++;
				}
			}//rectype

			else {
				if(is_array($allowed_cols) && sizeof($allowed_cols) > 0) {
					if((is_array($allowed_cols["view"]) && !in_array($l, $allowed_cols["view"])) && (!is_array($allowed_cols["edit"]) || is_array($allowed_cols["edit"]) && !in_array($l, $allowed_cols["edit"]))) continue;
					if(is_array($allowed_cols["edit"]) && !in_array($l, $allowed_cols["edit"])) $inputs[$col_counter]["params"] = ' disabled="disabled" ';
				}

				//Input-Type und -Namen speichern
				$inputs[$col_counter]["desc"] = $leute_col_name[$l];
				if(in_array($l, $COLS_LEUTE_UND_FAMILIE)) {
					$inputs[$col_counter]["fam_feld"] = true;
					if ($person['famid'] > 0) {
						$inputs[$col_counter]["fam_feld_warn_changes"] = true;
					}
				}

				$inputs[$col_counter]["type"] = $input_type[$l];
				$inputs[$col_counter]["name"] = "input_".$l;

				//Bei Edit: Bisherigen Wert einfüllen:
				if($mode == "edit") {
					if($input_type[$l] == "date") {
						$inputs[$col_counter]["value"] = ko_html(sql2datum($person[$l]));
					} else if($input_type[$l] == "enum") {  //Don't apply ko_html to enum values, as these must not get changed
						$inputs[$col_counter]["value"] = $person[$l];
					} else {
						$inputs[$col_counter]["value"] = ko_html($person[$l]);
					}
				//Bei Neu: Die _POST-Werte wieder einfüllen
				} else if($mode == "neu") {
					if($_POST["action"] == "submit_neue_person") {
						$inputs[$col_counter]["value"] = ko_html($_POST["input_".$l]);
					}
				}

				//Spezial-Optionen zu einzelnen Typen verarbeiten
				switch($input_type[$l]) {

					case "varchar":  //Textfeld
					case "mediumint":
					case "smallint":
						//Allow doubleselect fields
						if($KOTA['ko_leute'][$l]['form']['type'] == 'doubleselect') {
							$inputs[$col_counter]['type'] = 'doubleselect';
							$inputs[$col_counter]['descs'] = $KOTA['ko_leute'][$l]['form']['descs'];
							$inputs[$col_counter]['values'] = $KOTA['ko_leute'][$l]['form']['values'];

							$inputs[$col_counter]['avalue'] = $person[$l];
							foreach(explode(',', $person[$l]) as $ev) {
								if(!$ev || !in_array($ev, $inputs[$col_counter]['values'])) continue;
								$inputs[$col_counter]['avalues'][] = $ev;
								$ll_value = getLL('kota_ko_leute_'.$l.'_'.$ev);
								$inputs[$col_counter]['adescs'][] = $ll_value ? $ll_value : $ev;
							}
						}
						else if($KOTA['ko_leute'][$l]['form']['type'] == 'peoplesearch') {
							$inputs[$col_counter]['type'] = 'peoplesearch';
							$inputs[$col_counter]['avalue'] = $person[$l];
							foreach(explode(',', $person[$l]) as $ps_id) {
								ko_get_person_by_id($ps_id, $ps_person);
								$inputs[$col_counter]['avalues'][] = $ps_id;
								$inputs[$col_counter]['adescs'][] = trim($ps_person['firm'].' '.$ps_person['vorname'].' '.$ps_person['nachname']);
							}
						}
						else {
							//Textfeld, das aber als Select mit zusätzlichem Textfeld angezeigt werden soll
							if(in_array($l, $LEUTE_TEXTSELECT)) {
								$inputs[$col_counter]["type"] = "enum";

								//Leere Option
								$inputs[$col_counter]["values"][] = "";
								$inputs[$col_counter]["descs"][] = "";

								$c = db_select_distinct("ko_leute", $l, "", "", true);
								foreach($c as $cc) {
									$inputs[$col_counter]["values"][] = $cc;
									$inputs[$col_counter]["descs"][] = $cc;
								}
								$inputs[$col_counter]["value"] = $person[$l];

								//Allfällige doppelte Leereinträge rausholen
								$inputs[$col_counter]["values"] = array_unique($inputs[$col_counter]["values"]);
								$inputs[$col_counter]["descs"] = array_unique($inputs[$col_counter]["descs"]);

								//Zusätzliches Textfeld erstellen
								$inputs[++$col_counter]["desc"] = getLL("form_textplus");
								$inputs[$col_counter]["type"] = "varchar";
								$inputs[$col_counter]["name"] = "input_".$l."_2";
								$inputs[$col_counter]["params"] .= $inputs[($col_counter-1)]["params"];  //Copy params from select (e.g. disabled)
								$inputs[$col_counter]["params"] .= 'maxlength="'.$input_size[$l].'"';
							}//if(in_array(l, LEUTE_TEXTSELECT))
							else {
								$inputs[$col_counter]["params"] .= 'maxlength="'.$input_size[$l].'"';
							}
						}
					break;

					case "enum":  //Select
						$enum_values = explode(',', str_replace("'", '', $input_size[$l]));
						foreach($enum_values as $ev) {
							$inputs[$col_counter]['values'][] = $ev;
							$ll_value = getLL('kota_ko_leute_'.$l.'_'.$ev);
							$inputs[$col_counter]['descs'][] = $ll_value ? $ll_value : $ev;
						}
						if(in_array($l, $LEUTE_ENUMPLUS)) {
							$inputs[++$col_counter]["desc"] = getLL("form_textplus");
							$inputs[$col_counter]["type"] = "varchar";
							$inputs[$col_counter]["name"] = "input_".$l."_2";
						}
					break;

					case "date":
						$inputs[$col_counter]["type"] = "html";
						if(false === mb_strpos($inputs[$col_counter]["params"], 'disabled')) {
							$inputs[$col_counter]["value"] = $js_calendar->make_input_field(array(), array("name" => $inputs[$col_counter]["name"], "value" => $inputs[$col_counter]["value"]));
						}
					break;

					case "tinyint":  //Checkbox
						$inputs[$col_counter]["params"] .= $person[$l] ? 'checked="checked"' : '';
					break;

					//picture
					case "tinytext":
						$inputs[$col_counter]["type"] = 'file';
						$inputs[$col_counter]['value'] = ko_pic_get_tooltip($person[$l], 40, 200, 't', 'c', TRUE);
						$inputs[$col_counter]["name2"] = "input_".$l."_DELETE";
						$inputs[$col_counter]["value2"] = getLL("delete");
					break;

					case "blob":
					case "text":
					break;

				}

				//Add checkboxes for email fields, if multiple are used and field may be edited
				if(in_array($l, $LEUTE_EMAIL_FIELDS) && sizeof($LEUTE_EMAIL_FIELDS) > 1) {
					$value = db_select_data('ko_leute_preferred_fields', "WHERE `type` = 'email' AND `lid` = '$id' AND `field` = '$l'", '*', '', '', true);
					if(false === mb_strpos($inputs[$col_counter]['params'], 'disabled')) {
						$code = '<input type="checkbox" name="email_chk_'.$l.'"'.($value['id'] ? ' checked="checked"' : '').' title="'.sprintf(getLL('leute_preferred_fields_email_chk'), $leute_col_name[$LEUTE_EMAIL_FIELDS[0]]).'" />';
						$inputs[$col_counter]['chk_preferred'] = $code;
					} else {
						$inputs[$col_counter]['chk_preferred'] = $value['id'] ? '<img src="'.$ko_path.'images/icon_checked.gif" />' : '';
					}
				}

				//Add checkboxes for mobile fields, if multiple are used
				if(in_array($l, $LEUTE_MOBILE_FIELDS) && sizeof($LEUTE_MOBILE_FIELDS) > 1) {
					$value = db_select_data('ko_leute_preferred_fields', "WHERE `type` = 'mobile' AND `lid` = '$id' AND `field` = '$l'", '*', '', '', true);
					if(false === mb_strpos($inputs[$col_counter]['params'], 'disabled')) {
						$code = '<input type="checkbox" name="mobile_chk_'.$l.'"'.($value['id'] ? ' checked="checked"' : '').' title="'.sprintf(getLL('leute_preferred_fields_mobile_chk'), $leute_col_name[$LEUTE_MOBILE_FIELDS[0]]).'" />';
						$inputs[$col_counter]['chk_preferred'] = $code;
					} else {
						$inputs[$col_counter]['chk_preferred'] = $value['id'] ? '<img src="'.$ko_path.'images/icon_checked.gif" />' : '';
					}
				}

				$col_counter++;
			}//if..else(ll=="---")

		}//foreach(ll)

		$row[$row_counter++]["inputs"] = $inputs;
		if($add_inputs) {
			foreach($add_inputs as $add_input) {
				$row[$row_counter++]["inputs"] = $add_input;
			}
		}
	}//foreach(LEUTE_LAYOUT)


	//Announce changes to kOOL users
	if($mode == 'edit') {
		$announce_values = $announce_descs = array();
		$logins = db_select_data('ko_admin', "WHERE (`disabled` = '' OR `disabled` = '0') AND (`leute_id` > 0 || `email` != '')", '*');
		foreach($logins as $login) {
			if(!$login['email']) {
				ko_get_leute_email($login['leute_id'], $email);
				if($email) {
					$announce_values[] = $login['id'];
					$announce_descs[] = $login['login'];
				}
			} else {
				$announce_values[] = $login['id'];
				$announce_descs[] = $login['login'];
			}
		}
		$smarty->assign('announce_values', $announce_values);
		$smarty->assign('announce_descs', $announce_descs);
		$smarty->assign('label_announce_title', getLL('form_leute_announce_changes_title'));
		$smarty->assign('label_announce_description', getLL('form_leute_announce_changes_description'));
	}

	if ($LEUTE_NO_FAMILY) {
		$smarty->assign("hide_fam", true);
	}

	//LL-Values
	$smarty->assign("label_family", getLL("form_leute_family"));
	$smarty->assign("label_familyrole", getLL("form_leute_familyrole"));
	$smarty->assign("label_family_new", getLL("form_leute_family_new"));
	$smarty->assign("label_ok", getLL("OK"));
	$smarty->assign("label_group", getLL("groups_group"));
	$smarty->assign("label_grouprole", getLL("groups_role"));
	$smarty->assign("label_group_assigned", getLL("groups_assigned"));
	$smarty->assign("label_as_new_person", getLL("form_leute_as_new_person"));

	//Legend for family fields
	$smarty->assign("tpl_legend", getLL("leute_legend_family_fields"));
	$smarty->assign("tpl_legend_icon", "icon_familie.png");

	$smarty->assign("tpl_titel", getLL("form_leute_title")); //Personendaten
	$smarty->assign("tpl_id", $id);
	$smarty->assign("tpl_rows", $row);
	$smarty->assign("tpl_action", $mode == "neu" ? "submit_neue_person" : "submit_edit_person");
	$smarty->assign('tpl_action_neu', ($mode == 'edit' && $access['leute']['MAX'] > 1 && $show_save_as_new) ? 'submit_als_neue_person' : '');

	$smarty->display("ko_formular_leute.tpl");
}//ko_formular_leute()






/**
  * Stellt die Leute-Liste dar
	* mode definiert die Ausgabe: liste, my_list, adressliste
	* output: TRUE=Ausgabe erfolgt direkt, FALSE=HTML wird zurückgegeben (Ajax)
	*/
function ko_list_personen($mode="liste", $output=true) {
	global $smarty, $ko_path;
	global $LEUTE_EXCLUDE, $LEUTE_ADRESSLISTE_LAYOUT, $LEUTE_EMAIL_FIELDS, $LEUTE_MOBILE_FIELDS;
	global $KOTA, $ko_menu_akt;
	global $all_groups;
	global $access;

	if(!is_array($access['leute'])) ko_get_access('leute');
	if($access['leute']['MAX'] < 0) return;


	if(!$all_groups) ko_get_groups($all_groups);
	$all_datafields = db_select_data("ko_groups_datafields", "WHERE 1=1", "*");

	$leute_col_name = ko_get_leute_col_name($groups_hierarchie=false, $add_group_datafields=true);

	if($mode == "my_list") {  //Daten aus _SESSION[my_list] holen
		//Eigenes z_where gemäss den gespeicherten IDs aufbauen
		foreach($_SESSION['my_list'] as $k => $v) if(!$v) unset($_SESSION['my_list'][$k]);
		if(sizeof($_SESSION['my_list']) > 0) {
			$z_where = "AND `id` IN (".implode(',', $_SESSION['my_list']).')';
			$rows = sizeof($_SESSION['my_list']);
			//Manual sorting for MODULE- and other special columns
			if(true === ko_manual_sorting($_SESSION['sort_leute'])) {
				ko_get_leute($all, $z_where);
				$es = ko_leute_sort($all, $_SESSION['sort_leute'], $_SESSION['sort_leute_order']);
			}
			//Sorting done directly in MySQL
			else {
				if($_SESSION['show_start'] > $rows) $_SESSION['show_start'] = 1;

				if(isset($_SESSION['show_start']) && isset($_SESSION['show_limit']) && $_SESSION['show_limit'] > 0) {
					$z_limit = 'LIMIT '.($_SESSION['show_start']-1).', '.$_SESSION['show_limit'];
				} else {
					$z_limit = 'LIMIT 0,50';
				}
				ko_get_leute($es, $z_where, $z_limit);
			}
		} else {
			$es = array();
			$rows = 0;
		}

	}
	else if($mode == 'birthdays') {
		//Get dealine settings for birthdays
		$deadline_plus = ko_get_userpref($_SESSION['ses_userid'], 'geburtstagsliste_deadline_plus');
		$deadline_minus = ko_get_userpref($_SESSION['ses_userid'], 'geburtstagsliste_deadline_minus');
		if(!$deadline_plus) $deadline_plus = 21;
		if(!$deadline_minus) $deadline_minus = 7;

		$z_where = '';
		$dates = array();
		$today = date('Y-m-d');
		for($inc = -1*$deadline_minus; $inc <= $deadline_plus; $inc++) {
			$d = add2date($today, 'day', $inc, TRUE);
			$dates[mb_substr($d, 5)] = $inc;
			list($month, $day) = explode('-', mb_substr($d, 5));
			$z_where .= " OR (MONTH(`geburtsdatum`) = '$month' AND DAY(`geburtsdatum`) = '$day') ";
		}
		$where = " AND deleted = '0' ".ko_get_leute_hidden_sql()." AND `geburtsdatum` != '0000-00-00' ";
		$where .= " AND (".mb_substr($z_where, 3).") ".ko_get_birthday_filter();

		$rows = db_get_count('ko_leute', 'id', $where);
		$_es = db_select_data('ko_leute', 'WHERE 1=1 '.$where, '*');

		$sort = array();
		foreach($_es as $pid => $p) {
			$sort[$pid] = $dates[mb_substr($p['geburtsdatum'], 5)];
		}
		asort($sort);

		$es = array();
		$row = 0;
		foreach($sort as $pid => $deadline) {
			$p = $_es[$pid];

			$p['deadline'] = $deadline;
			$p['alter'] = (int)mb_substr(add2date(date('Y-m-d'), 'day', $deadline, TRUE), 0, 4) - (int)mb_substr($p['geburtsdatum'], 0, 4);

			$es[$pid] = $p;
		}//foreach(_es)



		//Add columns for birthday list
		if(!in_array('geburtsdatum', $_SESSION['show_leute_cols'])) $_SESSION['show_leute_cols'][] = 'geburtsdatum';
		if(!in_array('alter', $_SESSION['show_leute_cols'])) $_SESSION['show_leute_cols'][] = 'alter';
		if(!in_array('deadline', $_SESSION['show_leute_cols'])) $_SESSION['show_leute_cols'][] = 'deadline';
		$leute_col_name['alter'] = getLL('leute_birthday_list_header_age');
		$leute_col_name['deadline'] = getLL('leute_birthday_list_header_deadline');
	}
	else {  //Daten aus DB holen
		//Filter anwenden
		apply_leute_filter($_SESSION['filter'], $z_where, ($access['leute']['ALL'] < 1));

		//Manual sorting for MODULE- and other special columns
		if(true === ko_manual_sorting($_SESSION["sort_leute"])) {
			$rows = ko_get_leute($all, $z_where);
			$es = ko_leute_sort($all, $_SESSION["sort_leute"], $_SESSION["sort_leute_order"]);
		}
		//Sorting done directly in MySQL
		else {
			$rows = db_get_count('ko_leute', 'id', $z_where);
			if($_SESSION['show_start'] > $rows) $_SESSION['show_start'] = 1;

			if(isset($_SESSION['show_start']) && isset($_SESSION['show_limit']) && $_SESSION['show_limit'] > 0) {
				$z_limit = "LIMIT " . ($_SESSION["show_start"]-1) . ", " . $_SESSION["show_limit"];
			} else {
				$z_limit = 'LIMIT 0,50';
			}
			ko_get_leute($es, $z_where, $z_limit);
		}
		if(sizeof($LEUTE_EMAIL_FIELDS) > 1 || sizeof($LEUTE_MOBILE_FIELDS) > 1) {
			$preferred_fields = ko_get_preferred_fields();
		}
	}

	//show list-title
	$leute_show_deleted = false;
	if($access['leute']['MAX'] > 2 && ko_get_userpref($_SESSION['ses_userid'], 'leute_show_deleted') == 1) {
		$leute_show_deleted = true;
		$smarty->assign("tpl_list_title", getLL("leute_list_header_deleted"));
	}
	else if($access['leute']['MAX'] > 1 && $_SESSION['leute_version']) {
		$smarty->assign("tpl_list_title", getLL("leute_list_header_version"));
		$smarty->assign("tpl_list_title_styles", "background-color: #f4914a;");
		$smarty->assign("tpl_list_subtitle", getLL("leute_list_subheader_version").sql2datum($_SESSION["leute_version"]));
	}


	//version history of every record
	if($access['leute']['MAX'] > 1) {
		$smarty->assign("tpl_show_version_col", true);
	}

	//find number of columns for colspan for version td (as IE can not edit innerHTML of tr elements
	$cols = 4;  //checkbox, edit, del, history button
	if(ko_get_userpref($_SESSION['ses_userid'], 'leute_fam_checkbox') == 1 && in_array($mode, array('liste', 'my_list'))) $cols++;  //fam checkbox
	if(!is_array($all_groups)) ko_get_groups($all_groups);
	foreach($_SESSION["show_leute_cols"] as $col) {
		$cols++;
	}
	$smarty->assign("version_colspan", $cols);


	//Statistik über Suchergebnisse und Anzeige
	if($mode == 'birthdays') {
		$smarty->assign('hide_listlimiticons', TRUE);
	  $stats_end = $rows;
	} else {
	  $stats_end = ($_SESSION["show_limit"]+$_SESSION["show_start"]-1 > $rows) ? $rows : ($_SESSION["show_limit"]+$_SESSION["show_start"]-1);
	}
	$smarty->assign('tpl_stats', $_SESSION["show_start"]." - ".$stats_end." ".getLL("list_oftotal")." ".$rows);


  //Links für Prev und Next-Page vorbereiten
  if($_SESSION["show_start"] > 1 && $mode != 'birthdays') {
		$smarty->assign("tpl_prevlink_link", "javascript:sendReq('../leute/inc/ajax.php', 'action,set_start,sesid', 'setstart,".(($_SESSION["show_start"]-$_SESSION["show_limit"] < 1) ? 1 : ($_SESSION["show_start"]-$_SESSION["show_limit"])).",".session_id()."', do_element);");
  } else {
    $smarty->assign('tpl_prevlink_link', '');
  }
  if(($_SESSION["show_start"]+$_SESSION["show_limit"]-1) < $rows && $mode != 'birthdays') {
		$smarty->assign("tpl_nextlink_link", "javascript:sendReq('../leute/inc/ajax.php', 'action,set_start,sesid', 'setstart,".($_SESSION["show_limit"]+$_SESSION["show_start"]).",".session_id()."', do_element);");
  } else {
    $smarty->assign('tpl_nextlink_link', '');
  }
	$smarty->assign('limitM', $_SESSION['show_limit'] >= 100 ? $_SESSION['show_limit']-50 : max(10, $_SESSION['show_limit']-10));
	$smarty->assign('limitP', $_SESSION['show_limit'] >= 50 ? $_SESSION['show_limit']+50 : $_SESSION['show_limit']+10);


	//page-select
	$pages = ceil($rows/$_SESSION["show_limit"]);
	if($pages > 1 && $mode != 'birthdays') {
		$values = $output = null; $selected = 1;
		for($i=0; $i<$pages; $i++) {
			$start = 1+$i*$_SESSION["show_limit"];
			$values[] = $start;
			$output[] = ($i+1);
		}
		$smarty->assign("show_page_select", true);
		$smarty->assign("show_page_select_label", getLL("page"));
		$smarty->assign("show_page_values", $values);
		$smarty->assign("show_page_output", $output);
		$smarty->assign("show_page_selected", $_SESSION["show_start"]);
	} else {
		$smarty->assign("show_page_select", false);
	}



	//Header
	if(ko_get_userpref($_SESSION['ses_userid'], 'leute_fam_checkbox') == 1 && in_array($mode, array('liste', 'my_list'))) {
		$smarty->assign('tpl_show_3cols', true);
		$smarty->assign('tpl_show_4cols_leute', true);
	} else {
		$smarty->assign('tpl_show_3cols', true);
	}

	if(in_array($mode, array('liste', 'my_list', 'birthdays'))) {
		$smarty->assign("checkbox_code", "select_export_marked();");
		$smarty->assign("checkbox_all_code", "select_export_marked();");
		$h_counter = -1;
		foreach($_SESSION["show_leute_cols"] as $c) {
			if($c != "" && isset($leute_col_name[$c])) {
				$h_counter++;
				if(mb_substr($c, 0, 9) == "MODULEgrp") {
					$tpl_table_header[$h_counter]['sort'] = $mode != 'birthdays' ? $c : '';
					$tpl_table_header[$h_counter]['name'] = $leute_col_name[$c];
					$tpl_table_header[$h_counter]['id'] = 'col_'.$c;
					if(false !== mb_strpos($c, ':')) {
						$tpl_table_header[$h_counter]['class'] = 'ko_list ko_list_datafields';
						$tpl_table_header[$h_counter]['title'] = getLL('leute_listheader_df_group').': '.$leute_col_name[mb_substr($c, 0, 15)];
					}
				} else {
					if($c != "groups" && $mode != 'birthdays') {
						$sort_col = $c;
						if($c == 'geburtsdatum')
							$sort_col = ko_get_userpref($_SESSION['ses_userid'], 'leute_sort_birthdays') == 'year' ? $c : 'MODULE'.$c;
						$tpl_table_header[$h_counter]["sort"] = $sort_col;
					}
					$tpl_table_header[$h_counter]["name"] = $leute_col_name[$c];
					$tpl_table_header[$h_counter]['id'] = 'col_'.$c;
				}
			}//if(c)
		}

		//Multisorting (show for list)
		$multisort["select_values"][] = "";
		$multisort["select_descs"][] = "";
		foreach($leute_col_name as $i => $col) {
			if(mb_substr($i, 0, 6) == "MODULE") continue;  //Only add "normal" columns without groups
			$sort_col = $i;
			if($i == 'geburtsdatum')
				$sort_col = ko_get_userpref($_SESSION['ses_userid'], 'leute_sort_birthdays') == 'year' ? $i : 'MODULE'.$i;
			$multisort["select_values"][] = $sort_col;
			$multisort["select_descs"][] = $col;
		}
		//Add displayed MODULE columns (excluding MODULEgeburtsdatum)
		$multisort["select_values"][] = "";
		$multisort['select_descs'][] = '------';
		foreach($tpl_table_header as $i => $col) {
			if(mb_substr($col['sort'], 0, 6) != 'MODULE' || $col['sort'] == 'MODULEgeburtsdatum') continue;
			$multisort["select_values"][] = $col["sort"];
			$multisort["select_descs"][] = $col["name"];
		}
		$multisort["show"] = true;
		$multisort["showLink"] = getLL("list_multisort_showLink");
		$multisort["open"] = (sizeof($_SESSION["sort_leute"]) > 1);
		foreach($_SESSION["sort_leute"] as $i => $col) {
			$multisort["select_selected"][$i] = $col;
			$multisort["columns"][$i] = $i;
			$multisort["order"][$i] = mb_strtoupper($_SESSION["sort_leute_order"][$i]);
		}

	}//if(mode == liste | my_list)
	else if($mode == "adressliste") {
		$tpl_table_header = array();
	}
	else return false;

	$smarty->assign("tpl_table_header", $tpl_table_header);
	$smarty->assign("sort", array("show" => true,
																"action" => "setsortleute",
																"akt" => $_SESSION["sort_leute"][0],
																"akt_order" => $_SESSION["sort_leute_order"][0])
	);
	$smarty->assign("module", "leute");
	$smarty->assign("sesid", session_id());
	if($mode != 'birthdays') $smarty->assign("multisort", $multisort);


	//Multiedit-Spalten definieren
	//get the cols, for which this user has edit-rights (saved in allowed_cols[edit])
	$allowed_cols = ko_get_leute_admin_spalten($_SESSION["ses_userid"], "all");
	if(!$leute_show_deleted && $access['leute']['MAX'] > 1 && $mode != 'birthdays') {
		$smarty->assign("tpl_show_editrow", true);
		$edit_columns = array();
		foreach($_SESSION["show_leute_cols"] as $col) {
			if($col != '' && isset($leute_col_name[$col])) {
				if(isset($KOTA['ko_leute'][$col])) {
					if(!is_array($allowed_cols['edit'])
						|| (is_array($allowed_cols['edit']) && in_array($col, $allowed_cols['edit']))
						|| mb_substr($col, 0, 6) == 'MODULE'
						) {
						$edit_columns[] = $col;
					} else {
						$edit_columns[] = '';
					}
				} else {
					$edit_columns[] = '';
				}
			}
		}
		$smarty->assign('tpl_edit_columns', $edit_columns);
	} else {
		$smarty->assign('tpl_show_editrow', false);
	}


	//add icons to move columns left an right
	if(ko_get_userpref($_SESSION["ses_userid"], "sort_cols_leute") == "0" && $mode != 'birthdays') {
		$smarty->assign("tpl_show_sort_cols", true);
		$sort_cols = null;
		foreach($_SESSION["show_leute_cols"] as $col) {
			if($col != "" && isset($leute_col_name[$col])) {
				$sort_cols[] = $col;
			}
		}
		$smarty->assign("tpl_sort_cols", $sort_cols);
	} else {
		//no sorting
		$smarty->assign("tpl_show_sort_cols", false);
	}

	//Columns to prevent deletion
	$no_delete_columns = ko_get_setting('leute_no_delete_columns');
	if($no_delete_columns != '') {
		$no_delete_columns = explode(',', $no_delete_columns);
	} else {
		$no_delete_columns = array();
	}


	//Label for QRCode
	$smarty->assign('label_qrcode', getLL('leute_list_qrcode'));
	$smarty->assign('tpl_show_maps_link', true);
	$smarty->assign('label_google_maps', getLL('leute_label_google_maps'));
	$smarty->assign('label_word', getLL('leute_list_word'));
	if(ko_word_get_template() !== FALSE) $smarty->assign('tpl_show_word', true);
	$smarty->assign('tpl_show_clipboard', true);
	$smarty->assign('label_clipboard', getLL('leute_list_clipboard'));

	$login_edit_person = ko_get_setting("login_edit_person");
	$logged_in_leute_id = ko_get_logged_in_id();
	//Eigentliche Daten ausgeben
	$e_i = -1;
	foreach($es as $e) {
		//Nur erlaubte Personen überhaupt anzeigen
		if($access['leute']['ALL'] < 1 && $access['leute'][$e['id']] < 1) continue;

		$e_i++;

		//Hidden row
		$tpl_list_data[$e_i]["rowclass"] = $e["hidden"] ? "ko_list_hidden" : "";

		//Checkbox
    	$tpl_list_data[$e_i]["show_checkbox"] = true;
    	//$tpl_list_data[$e_i]["rowclick_code"] = 'jumpToUrl(\'/leute/index.php?action=single_view&amp;id='.$e["id"].'\');';

		//Familien-Checkbox
		if($e["famid"] > 0) {
    	$tpl_list_data[$e_i]["show_fam_checkbox"] = true;
		}

		//Edit-Button
		if( !$leute_show_deleted && ($access['leute']['ALL'] > 1 || $access['leute'][$e['id']] > 1 || ($login_edit_person == 1 && $e['id'] == $logged_in_leute_id))) {
    		$tpl_list_data[$e_i]["show_edit_button"] = true;
    		$tpl_list_data[$e_i]['alt_edit'] = $_SESSION['ses_userid'] == ko_get_root_id() ? 'ID: '.$e['id'] : getLL('leute_labels_edit_pers');
    		$tpl_list_data[$e_i]["onclick_edit"] = "javascript:set_action('edit_person', this);set_hidden_value('id', '".$e["id"]."', this);this.submit";
    	} else {
			if($leute_show_deleted) {
				$tpl_list_data[$e_i]["show_undelete_button"] = true;
				$tpl_list_data[$e_i]["alt_edit"] = getLL('leute_labels_undel_pers');
				$tpl_list_data[$e_i]["onclick_edit"] = "javascript:set_action('undelete_person', this);set_hidden_value('id', '".$e["id"]."', this);this.submit";
			} else {
			    $tpl_list_data[$e_i]["show_edit_button"] = false;
			}
    	}

    	//Delete-Button
		if(($access['leute']['ALL'] > 2 || $access['leute'][$e['id']] > 2) && (!$leute_show_deleted || ko_get_setting('leute_real_delete') == 1)) {
			$ok = TRUE;
			if(sizeof($no_delete_columns) > 0) {
				foreach($no_delete_columns as $ndc) {
					if($e[$ndc]) $ok = FALSE;
				}
			}
			if($ok) {
				$tpl_list_data[$e_i]["show_delete_button"] = true;
				$tpl_list_data[$e_i]["alt_delete"] = getLL("leute_labels_del_pers");
				$tpl_list_data[$e_i]["onclick_delete"] = "javascript:c = confirm('" . getLL("leute_confirm_del_pers") . "');if(!c) return false;set_action('delete_person', this);set_hidden_value('id', '".$e["id"]."', this);";
			} else {
	      $tpl_list_data[$e_i]["show_delete_button"] = false;
			}
    } else {
      $tpl_list_data[$e_i]["show_delete_button"] = false;
    }

		//version history
		if($access['leute']['ALL'] > 1 || $access['leute'][$e['id']] > 1) {
      $tpl_list_data[$e_i]["alt_version"] = getLL("leute_labels_version_history");
      $tpl_list_data[$e_i]["onclick_version"] = "tr=document.getElementById('version_tr_".$e["id"]."'); if(tr.style.display == 'none') {sendReq('../leute/inc/ajax.php', 'action,id,sesid', 'history,".$e["id"].",".session_id()."', do_element); } change_vis_tr('version_tr_".$e["id"]."');return false;";
		}

	  //Index
    $tpl_list_data[$e_i]["id"] = $e["id"];
    $tpl_list_data[$e_i]["famid"] = $e["famid"];

		//QRCode string and hash
		$vc = 'pid:'.$e['id'];
		$tpl_list_data[$e_i]['qrcode_string'] = base64_encode($vc);
		$tpl_list_data[$e_i]['qrcode_hash'] = md5(KOOL_ENCRYPTION_KEY.$vc);

		//Google Map
		$maps_link = '';
		$replace = array('ö' => 'oe', 'ä' => 'ae', 'ü' => 'ue', 'é' => 'e', 'è' => 'e', 'à' => 'a', 'ç' => 'c', ' ' => '+');
		if($e['adresse']) $maps_link .= '+'.str_replace(array_keys($replace), $replace, $e['adresse']);
		if($e['plz']) $maps_link .= '+'.$e['plz'];
		if($e['ort']) $maps_link .= '+'.str_replace(array_keys($replace), $replace, $e['ort']);
		if($e['land']) $maps_link .= '+'.str_replace(array_keys($replace), $replace, $e['land']);
		if($maps_link != '') {
			$maps_link = 'http://maps.google.com/maps?f=q&hl='.$_SESSION['lang'].'&q='.mb_substr($maps_link, 1);
		}
		$tpl_list_data[$e_i]['maps_link'] = $maps_link;

		//Clipboard content
		$clipboard_content = '';
		$clip_person = ko_apply_rectype($e);
		if($clip_person['firm']) $clipboard_content .= $clip_person['firm']."\n";
		if($clip_person['anrede']) $clipboard_content .= $clip_person['anrede']."\n";
		if($clip_person['vorname'] || $clip_person['nachname']) $clipboard_content .= trim($clip_person['vorname'].' '.$clip_person['nachname'])."\n";
		if($clip_person['adresse']) $clipboard_content .= $clip_person['adresse']."\n";
		if($clip_person['adresse_zusatz']) $clipboard_content .= $clip_person['adresse_zusatz']."\n";
		if($clip_person['plz'] || $clip_person['ort']) $clipboard_content .= trim($clip_person['plz'].' '.$clip_person['ort'])."\n";
		$tpl_list_data[$e_i]['clipboard_content'] = trim($clipboard_content);


		//Anzuzeigende Spalten einfüllen
		$colcounter = -1;
		if(in_array($mode, array('liste', 'my_list', 'birthdays'))) {
			foreach($_SESSION["show_leute_cols"] as $c) {
				if($c != "" && isset($leute_col_name[$c])) {
					$colcounter++;

					//Add links to single groups in groups column
					if($c == "groups") {
						$value = $sort = array();
						$counter = 0;
						foreach(explode(",", $e[$c]) as $g) {
							$gid = ko_groups_decode($g, "group_id");
							if($g
								&& ($access['groups']['ALL'] > 0 || $access['groups'][$gid] > 0)
								&& (ko_get_userpref($_SESSION['ses_userid'], 'show_passed_groups') == 1 || ($all_groups[$gid]['start'] <= date('Y-m-d') && ($all_groups[$gid]['stop'] == '0000-00-00' || $all_groups[$gid]['stop'] > date('Y-m-d'))))
								) {
								$group_desc = ko_groups_decode($g, 'group_desc_full');
								$class = ($all_groups[$gid]['stop'] != '0000-00-00' && (int)str_replace('-', '', $all_groups[$gid]['stop']) < (int)date('Ymd')) ? 'group-passed' : 'group-active';
								$value[$counter] = '<a class="'.$class.'" href="#" onclick="'."sendReq('../leute/inc/ajax.php', 'action,id,state,sesid', 'itemlist,MODULEgrp".$gid.",switch,".session_id()."', do_element);return false;".'">'.ko_html($group_desc)."</a>";
								$sort[$counter] = $group_desc;
								$counter++;
							}
						}
						//Sort groups
						asort($sort);
						$v = array();
						foreach($sort as $id => $s) {
							$v[] = $value[$id];
						}
						$value = implode(", <br />", $v);
					}
					//Add mark for preferred email fields
					else if(in_array($c, $LEUTE_EMAIL_FIELDS)) {
						$value = map_leute_daten($e[$c], $c, $e, $all_datafields);
						if(check_email($value)) $value = '<a href="mailto:'.$value.'" title="'.getLL('leute_labels_email_link').'">'.$value.'</a>';
						if(sizeof($LEUTE_EMAIL_FIELDS) > 1) {
							if($value != '' && in_array($c, $preferred_fields[$e['id']]['email'])) $value = '[x]&nbsp;'.$value;
						}
					}
					//Add mark for preferred mobile fields
					else if(in_array($c, $LEUTE_MOBILE_FIELDS) && sizeof($LEUTE_MOBILE_FIELDS) > 1) {
						$value = map_leute_daten($e[$c], $c, $e, $all_datafields);
						if($value != '' && in_array($c, $preferred_fields[$e['id']]['mobile'])) $value = '[x]&nbsp;'.$value;
					}
					else if($c == 'famid' && $e[$c] > 0) {
						$value = map_leute_daten($e[$c], $c, $e, $all_datafields);
						$value = '<a href="'.$ko_path.'leute/index.php?action=set_famfilter&famid='.intval($e[$c]).'" title="'.getLL('leute_labels_set_famid_filter').'">'.$value.'</a>';
					}
					//all other columns are handled in map_leute_daten()
					else {
						$value = map_leute_daten($e[$c], $c, $e, $all_datafields);
						if(mb_substr($c, 0, 9) != "MODULEgrp"
							&& mb_substr($c, 0, 14) != 'MODULEtracking'
							&& !in_array($c, array('MODULEkgpicture', 'MODULEkgmailing_alias', 'picture'))
							&& mb_substr($c, 0, 11) != 'MODULEfamid'
							&& !$KOTA['ko_leute'][$c]['allow_html']
							) $value = ko_html($value);
					}

					if(is_array($value)) {  //group with datafields, so more than one column has to be added
						foreach($value as $dfid => $v) {
							$tpl_list_cols[$colcounter] = $colcounter;
							$tpl_list_data[$e_i][$colcounter++] = $v;
							if($dfid > 0) {  //Later columns contain group datafields
								$db_cols[] = 'MODULEgdf'.mb_substr($c, 9).$dfid;
							} else {  //First column contains group
								$db_cols[] = $c;
							}
						}
						$colcounter--;
					}
					//normal value (not from group datafield)
					else {
						$tpl_list_cols[$colcounter] = $colcounter;
						$tpl_list_data[$e_i][$colcounter] = $value;
						$db_cols[] = $c;
					}
				}
			}
		}
		else if($mode == "adressliste") {
			$tpl_list_data[$e_i]["vcard_id"] = $e["id"];
			$tpl_list_data[$e_i]["maplinks"] = ko_get_map_links($e);
			$rowcounter = 0;
			foreach($LEUTE_ADRESSLISTE_LAYOUT as $c) {
				$tpl_list_data[$e_i]["daten"][$rowcounter] = "";
				foreach($c as $cc) {
					if(mb_substr($cc, 0, 1) == "@") {  //Kommentar (beginnend mit @) direkt ausgeben
						$tpl_list_data[$e_i]["daten"][$rowcounter] .= "<i>".ko_html(mb_substr($cc, 1))."</i> ";
					} else if(is_string($cc)) {  //Einträge als Personendaten formatiert ausgeben
						//Get preferred email
						if($cc == 'email') {
							ko_get_leute_email($e, $email);
							$tpl_list_data[$e_i]['daten'][$rowcounter] .= ko_html($email[0]).' ';
						}
						//Get preferred mobile
						else if($cc == 'natel') {
							ko_get_leute_mobile($e, $mobile);
							$tpl_list_data[$e_i]['daten'][$rowcounter] .= ko_html($mobile[0]).' ';
						} else {
							$tpl_list_data[$e_i]['daten'][$rowcounter] .= ko_html(map_leute_daten($e[$cc], $cc, $e)).' ';
						}
					}
				}
				$rowcounter++;
			}
		}
		else return false;

	}//foreach(es)

	$smarty->assign('tpl_list_cols', $tpl_list_cols);
	$smarty->assign('tpl_list_data', $tpl_list_data);
	$smarty->assign('db_table', 'ko_leute');
	$smarty->assign('db_cols', $db_cols);

	if(in_array($mode, array('liste', 'my_list', 'birthdays'))) {
		$list_footer = $smarty->get_template_vars('list_footer');

		//Footer:
		if($rows > 0 && (!$leute_show_deleted || $mode == "my_list") && $mode != 'birthdays') {
			//Join in Family
			if($access['leute']['MAX'] > 1 && (!is_array($allowed_cols['edit']) || in_array('famid', $allowed_cols['edit']))) {
				$button_code = '<input type="submit" onclick="c=confirm(\''.getLL('leute_list_footer_join_in_family_confirm').'\'); if(!c) {return false;} else {set_action(\'join_in_family\', this); return true; }" value="'.getLL("leute_list_footer_join_in_family_button").'" />';
				$list_footer[] = array("label" => getLL("leute_list_footer_join_in_family"),
															 "button" => $button_code);
				$smarty->assign("show_list_footer", true);
			}

			$smarty->assign("list_footer", $list_footer);


			//Merge duplicates
		  //Only show button, if 1 dup filter is applied (but not if more than 1 is applied)
			$dup_filters = 0;
			$filters = db_select_data('ko_filter', "WHERE `typ` = 'leute'", '*');
			foreach($_SESSION['filter'] as $k => $v) {
				if(!is_integer($k)) continue;
				if($filters[$v[0]]['name'] == 'duplicates') $dup_filters++;
			}
			if($dup_filters == 1 && $access['leute']['MAX'] > 2) {
				$button_code = '<input type="submit" onclick="set_action(\'merge_duplicates\', this);" value="'.getLL('leute_list_footer_merge_duplicates_button').'" />';
				$help = ko_get_help('leute', 'merge_duplicates');
				if($help['show']) $help_link = '&nbsp;'.$help['link'];
				else $help_link = '';
				$list_footer[] = array('label' => getLL('leute_list_footer_merge_duplicates').$help_link,
															 'button' => $button_code);
				$smarty->assign('show_list_footer', true);
			}


			$smarty->assign('list_footer', $list_footer);
		}//if(rows > 0)


		// Help for multisorting
		$smarty->assign("help", ko_get_help("leute", $_SESSION['show']));

		$smarty->assign('overlay', !$leute_show_deleted);

		if($output) {
			if($mode == "my_list") $smarty->assign("tpl_list_title", getLL("leute_mylist_list_title"));
			else if($mode == 'birthdays') $smarty->assign('tpl_list_title', getLL('leute_birthday_list_title'));
		  $smarty->display('ko_list.tpl');
		} else  {
		  print $smarty->fetch('ko_list.tpl');
		}
	} else if($mode == "adressliste") {
		if($output) {
		  $smarty->display('ko_adressliste.tpl');
		} else {
		  print $smarty->fetch('ko_adressliste.tpl');
		}
	}
}//ko_list_personen()




function ko_list_mod_leute() {
	global $smarty, $ko_path;
	global $DATETIME, $access, $LEUTE_ADMIN_SPALTEN_CONDITION;
	global $COLS_LEUTE_UND_FAMILIE;

	$cols = db_get_columns("ko_leute_mod");
	$col_names = ko_get_leute_col_name(FALSE, FALSE, 'all');

	$individual_admin_spalten = TRUE;
	if(!is_array($LEUTE_ADMIN_SPALTEN_CONDITION)) {
		$allowed_cols = ko_get_leute_admin_spalten($_SESSION['ses_userid'], 'all');
		$individual_admin_spalten = FALSE;
	}

	$counter=0;
	ko_get_logins($logins);
	ko_get_mod_leute($leute);
	foreach($leute as $p) {
		if($counter > 50) continue;

		//Get allowed_cols for every person (if needed)
		if($individual_admin_spalten) $allowed_cols = ko_get_leute_admin_spalten($_SESSION['ses_userid'], 'all', $p['_leute_id']);

		if($access['leute']['ALL'] < 2 && ($access['leute'][$p['_leute_id']] < 2 || $p['_leute_id'] < 1)) continue;

		$fields_counter=0;

		if($p["_leute_id"] == -1) {  //Neu
			$old_p = array();
			$mutationen[$counter]["name"] = getLL("leute_aa_new").": ".ko_html($p['firm']).' '.ko_html($p["vorname"])." ".ko_html($p["nachname"]);
		} else {  //bisherige Adresse geändert
			ko_get_person_by_id($p["_leute_id"], $old_p);
			$mutationen[$counter]["name"] = ko_html($old_p['firm']).' '.ko_html($old_p["vorname"])." ".ko_html($old_p["nachname"]);
		}
		$mutationen[$counter]["id"] = $p["_id"];

		foreach($cols as $c) {
			if(mb_substr($c['Field'], 0, 1) == '_') continue;

			if( ( ($p[$c['Field']] != '' && $p[$c['Field']] != '0000-00-00') || $old_p[$c['Field']]) && $p[$c['Field']] != $old_p[$c['Field']]) {
				$mutationen[$counter]['fields'][$fields_counter]['name'] = $c['Field'];
				$mutationen[$counter]['fields'][$fields_counter]['desc'] = $col_names[$c['Field']];
				if(mb_substr($c['Type'], 0, 4) == 'enum') {
					$mutationen[$counter]['fields'][$fields_counter]['type'] = 'select';
					$mutationen[$counter]['fields'][$fields_counter]['values'] = array_merge(array(''), db_get_enums('ko_leute_mod', $c['Field']));
					$mutationen[$counter]['fields'][$fields_counter]['descs'] = array_merge(array(''), db_get_enums_ll('ko_leute_mod', $c['Field']));
					$mutationen[$counter]['fields'][$fields_counter]['oldvalue'] = getLL('kota_ko_leute_mod_'.$c['Field'].'_'.$old_p[$c['Field']]);
					$mutationen[$counter]['fields'][$fields_counter]['newvalue'] = $p[$c['Field']];
				} else {
					$mutationen[$counter]['fields'][$fields_counter]['type'] = 'input';
					$mutationen[$counter]['fields'][$fields_counter]['oldvalue'] = ko_html($old_p[$c['Field']]);
					$mutationen[$counter]['fields'][$fields_counter]['newvalue'] = ko_html($p[$c['Field']]);
				}
				//Mark as not editable
				if(is_array($allowed_cols['edit']) && !in_array($c['Field'], $allowed_cols['edit'])) {
					$mutationen[$counter]['fields'][$fields_counter]['readonly'] = TRUE;
				}

				$mutationen[$counter]['fields'][$fields_counter]['isFamilyField'] = in_array($c['Field'], $COLS_LEUTE_UND_FAMILIE);

				$fields_counter++;
			}//if(p != old_p)
		}//foreach(cols as c)

		$family = null;
		if ($old_p['famid'] != 0) {
			$mutationen[$counter]['family'] = ko_get_familie($old_p['famid']);
		}

		//Bemerkungen zu dieser Mutation anzeigen
		$mutationen[$counter]['bemerkung'] = ko_html($p['_bemerkung']);

		//Show creation date and user
		if($p['_crdate'] != '0000-00-00 00:00:00') $mutationen[$counter]['crdate'] = strftime($DATETIME['dmY'].' %H:%M', strtotime($p['_crdate']));
		if($p['_cruserid'] > 0) $mutationen[$counter]['cruserid'] = getLL('by').' '.$logins[$p['_cruserid']]['login'];

		$counter++;
	}//foreach(leute as p)


	if(sizeof($leute) == 0) $smarty->assign('tpl_aa_empty', true);
	//LL-Values
	$smarty->assign('label_empty', getLL('aa_list_empty'));
	$smarty->assign('label_comments', getLL('aa_list_comments'));
	$smarty->assign('label_submit', getLL('aa_list_submit'));
	$smarty->assign('label_delete', getLL('aa_list_delete'));
	$smarty->assign('label_crdate', getLL('leute_labels_crdate'));

	$smarty->assign('tpl_list_title', getLL('leute_mod_title'));
	$smarty->assign('tpl_fm_title', getLL('leute_mod_title'));

	$smarty->assign('tpl_mutationen', $mutationen);
	$smarty->display('ko_adressaenderung.tpl');
}//ko_list_mod_leute()



function ko_list_groupsubscriptions() {
	global $smarty, $ko_path;
	global $DATETIME;
	global $access;

	$_gid = format_userinput($_SESSION['leute_gs_filter'], 'uint');
	$all_roles = db_select_data('ko_grouproles', 'WHERE 1');


	//Get all subscriptions to find all groups for filter
	ko_get_groupsubscriptions($leute, '', $_SESSION['ses_userid']);
	$gids = array();
	foreach($leute as $p) {
		//Group- and Role-ID
		list($gid, $rid) = explode(':', $p['_group_id']);
		$gid = format_userinput($gid, 'uint');
		$group = db_select_data('ko_groups', "WHERE `id` = '$gid'", '*', '', '', TRUE);

		//Store all groups for filter select
		$gids[$gid] = $group;
		$gid_counter[$gid] += 1;
	}

	$counter=0;
	ko_get_groupsubscriptions($leute, '', $_SESSION['ses_userid'], $_gid);
	foreach($leute as $p) {
		if($counter > 25) continue;

		//Group- and Role-ID
		list($gid, $rid) = explode(":", $p["_group_id"]);
		$gid = format_userinput($gid, "uint");
		$group = db_select_data("ko_groups", "WHERE `id` = '$gid'", "*", "", "", true);
		$rid = format_userinput($rid, "uint");
		$role = $rid ? $all_roles[$rid] : array();

		$fields_counter = 0;

		//Prepare role select to manually assign person to a role
		if($group['roles'] != '') {
			$role_options = '<option value=""></option>';
			foreach(explode(',', $group['roles']) as $role_id) {
				$sel = $role_id == $rid ? 'selected="selected"' : '';
				$role_options .= '<option value="'.$role_id.'" '.$sel.'>'.$all_roles[$role_id]['name'].'</option>';
			}
			$smarty->assign('hide_roles', FALSE);
			$gs[$counter]['_role_options'] = $role_options;
		} else {
			$smarty->assign('hide_roles', TRUE);
		}

		//Try to find person in DB
		if($p['vorname'] && $p['nachname']) {
			$search = array('vorname' => $p['vorname'], 'nachname' => $p['nachname']);
		} else if($p['email']) {
			$search = array('email' => $p['email']);
		} else if($p['firm']) {
			$search = array('firm' => $p['firm']);
		}
		$found_dbp = ko_fuzzy_search($search, "ko_leute", 2, false, 2);
		$db = null;
		foreach($found_dbp as $db_id) {
			ko_get_person_by_id($db_id, $dbp);
			$db[] = array("gid" => $gid, "_id" => $p["_id"], "lid" => $dbp["id"], "name" => $dbp["vorname"]." ".$dbp["nachname"],
										'firm' => $dbp['firm'], 'department' => $dbp['department'],
										"adressdaten" => $dbp["adresse"].", ".$dbp["plz"]." ".$dbp["ort"].
																		 ", ".$dbp["telp"].", ".$dbp["email"].", ".sql2datum($dbp["geburtsdatum"])
									 );
		}


		$gs[$counter]["_id"] = $p["_id"];
		$gs[$counter]["groupname"] = $group["name"].($rid ? ": ".$role["name"] : "");
		$gs[$counter]["ezmlm"] = $group["ezmlm_list"] != "" ? getLL("ezmlm_ml") : "";
		if($group['maxcount'] > 0) {
			$gs[$counter]['group_limit'] = $group['count'].'/'.$group['maxcount'].($group['count_role'] ? ' '.$role['name'] : '');
		}
		$gs[$counter]['firm'] = ko_html($p['firm']).($p['department'] ? ' ('.ko_html($p['department']).')' : '');
		$gs[$counter]["name"] = ko_html($p["vorname"])." ".ko_html($p["nachname"]);
		$gs[$counter]["address"] = $p["adresse"];
		$gs[$counter]["plz"] = $p["plz"];
		$gs[$counter]["ort"] = $p["ort"];
		$gs[$counter]["telp"] = $p["telp"];
		$gs[$counter]["email"] = $p["email"];
		$gs[$counter]["geburtsdatum"] = sql2datum($p["geburtsdatum"]);
		//Add age as calculated by the DOB
		$age = (int)date('Y') - (int)mb_substr($p['geburtsdatum'], 0, 4);
		if((int)(mb_substr($p['geburtsdatum'], 5, 2).mb_substr($p['geburtsdatum'], 8, 2)) > (int)(date('md'))) $age--;
		$gs[$counter]['_age'] = $age;

		$gs[$counter]["_bemerkung"] = ko_html($p["_bemerkung"]);
		//Show creation date
		if($p["_crdate"] != "0000-00-00 00:00:00") $gs[$counter]["_crdate"] = strftime($DATETIME["dmY"]." %H:%M", strtotime($p["_crdate"]));

		//Check for full group
		if($group['maxcount'] > 0 && $group['count'] >= $group['maxcount'] && (!$group['count_role'] || $group['count_role'] == $rid)) {
			$gs[$counter]['group_full'] = true;
		}

		if(sizeof($found_dbp) > 0) {
			$gs[$counter]["db"] = $db;
		} else {
			$gs[$counter]["empty"] = true;
		}
		//datafields
		$df_values = array();
		$df_data = unserialize($p['_group_datafields']);
		foreach($df_data as $i =>$df) {
			$df_values[$gid][$i] = $df;
		}
		$gs[$counter]["datafields"] = ko_groups_render_group_datafields($gid, $p["_id"], $df_values, array("hide_title" => true, "add_leute_id" => true));

		//People selects
		$ps = array("gid" => $gid);
		foreach(array("a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z") as $letter) {
			$ps["descs"][] = mb_strtoupper($letter);
			$ps["values"][] = "i".mb_strtoupper($letter);
		}
		$ps["name"] = "ps_".$p["_id"];

		$gs[$counter]["ps"] = $ps;

		$counter++;
	}//foreach(leute as p)

	if(sizeof($leute) == 0) {
		$smarty->assign("tpl_list_empty", true);
		$smarty->assign("label_no_entries", getLL("groups_mod_no_entries"));
	}

	//Group filter
	$smarty->assign('gids', $gids);
	$smarty->assign('gid_counter', $gid_counter);
	$smarty->assign('gid_counter_total', array_sum($gid_counter));

	//LL-Values
	$smarty->assign("label_no_person_in_db", getLL("groups_mod_no_person_in_db"));
	$smarty->assign("label_entered_data", getLL("groups_mod_entered_data"));
	$smarty->assign("label_ok", getLL("OK"));
	$smarty->assign("label_ok_and_mutation", getLL("groups_mod_ok_and_mutation"));
	$smarty->assign("label_add_person", getLL("groups_mod_add_person"));
	$smarty->assign("label_add_person_submit", getLL("groups_mod_add_person_submit"));
	$smarty->assign("label_delete_entry", getLL("groups_mod_delete_entry"));
	$smarty->assign("label_delete_entry_confirm", getLL("groups_mod_delete_entry_confirm"));
	$smarty->assign("label_new_groupsubscription", getLL("groups_mod_new_groupsubscription"));
	$smarty->assign("label_possible_db_hits", getLL("groups_mod_possible_db_hits"));
	$smarty->assign("label_ps", getLL("groups_mod_peopleselect"));
	$smarty->assign("label_crdate", getLL("leute_labels_crdate"));
	$smarty->assign('label_group_full', getLL('leute_groupsubscriptions_group_full'));
	$smarty->assign('label_role', getLL('groups_role'));
	$smarty->assign('label_all', getLL('all'));
	$smarty->assign('label_filter', getLL('leute_groupsubscriptions_filter'));
	$smarty->assign('current_filter', $_gid);
	$smarty->assign('sesid', session_id());


	$smarty->assign("tpl_list_title", getLL("leute_groupsubscriptions_title"));
	$smarty->assign("tpl_fm_title", getLL("leute_groupsubscriptions_title"));

	$smarty->assign("tpl_gs", $gs);
	$smarty->display("ko_groupsubscription.tpl");
}//ko_list_groupsubscriptions()





function ko_leute_show_single($id) {
	global $smarty, $ko_path;

	ko_get_person_by_id($id, $person);
	//TODO: Get all other infos like family, groups, datafields, kg etc.
	$smarty->assign("person", $person);
	$smarty->display("ko_leute_single_view.tpl");
}//ko_leute_show_single()





function ko_update_kg_filter() {
	ko_get_filters($filters, 'leute');
	foreach($filters as $ff) {
		if($ff['_name'] == 'smallgroup') {  //small groups
			$new_code  = '<select name="var1" size="0">';
			$new_code .= '<option value=""></option>';
			$kgs = db_select_data('ko_kleingruppen', 'WHERE 1=1', '*', 'ORDER BY name ASC');
			foreach($kgs as $kg) {
				$new_code .= '<option value="'.$kg['id'].'" title="'.$kg['name'].'">'.$kg['name'].'</option>';
			}
			$new_code .= '</select>';
			db_update_data('ko_filter', "WHERE `id` = '".$ff['id']."'", array('code1' => $new_code));
		}
	}
}//ko_update_kg_filter()




function ko_update_familie_filter() {
	ko_get_familien($fams);
	$new_code  = '<select name="var1" size="0">';
	$new_code .= '<option value="0"></option>';
	foreach($fams as $fam) {
		$new_code .= '<option value="'.$fam["famid"].'" title="'.$fam['id'].'">'.$fam["id"].'</option>';
	}
	$new_code .= '</select>';

	db_update_data("ko_filter", "WHERE `typ`='leute' AND `name`='family'", array("code1" => $new_code));
}//ko_update_familie_filter()



function ko_leute_import($state, $mode) {
	global $ko_path, $smarty;
	global $all_groups;
	global $access;

	switch($state) {
		case 1:
			$code  = "<h1>".getLL("leute_import_state1")."</h1>";
			$code .= getLL("leute_import_state1_header")."<br />";
			$code .= '<div class="install_select_lang">';
			$code .= '<a href="index.php?action=import&amp;state=2&amp;mode=vcard">';
			$code .= '<img src="'.$ko_path.'images/vcard_big.gif" border="0" /><br /><br />'.getLL("leute_import_state1_vcard");
			$code .= '</a></div>';
			$code .= '<div class="install_select_lang">';
			$code .= '<a href="index.php?action=import&amp;state=2&amp;mode=csv">';
			$code .= '<img src="'.$ko_path.'images/csv.jpg" border="0" /><br /><br />'.getLL("leute_import_state1_csv");
			$code .= '</a></div>';

			print $code;
		break;  //1

		case 2:
			if($mode == "vcard") {
				$rowcounter = 0;
				$gc = 0;
				$frmgroup[$gc]["row"][$rowcounter++]["inputs"][0] = array("desc" => getLL("leute_import_state1_vcard"),
						"type" => "file",
						"name" => "vcf",
						"params" => 'size="60"',
						);
				$smarty->assign("tpl_titel", getLL("leute_import_state2"));
				$smarty->assign("tpl_hide_cancel", true);
				$smarty->assign("tpl_submit_value", getLL("next"));
				$smarty->assign("tpl_action", "import");
				$smarty->assign("tpl_groups", $frmgroup);
				$smarty->display('ko_formular.tpl');
			}  //vcard

			else if($mode == "csv") {
				$rowcounter = 0;
				$gc = 0;

				$values = $descs = array();
				$table_cols = db_get_columns("ko_leute");
				$col_names = ko_get_leute_col_name();
				$dont_allow = array("id", "famid", "deleted", "hidden", "picture", "groups", "kinder", "smallgroups", "famfunction", "lastchange", 'crdate', 'cruserid');
				foreach($table_cols as $c) {
					if(!in_array($c["Field"], $dont_allow)) {
						$values[$c['Field']] = $c["Field"];
						$descs[$c['Field']] = $col_names[$c["Field"]] ? $col_names[$c["Field"]] : $c["Field"];
					}
				}
				// Add groups to choose from
				$groups = null;
				ko_get_groups($groups, 'and `type` = 0');
				$fullGroupIds = array();
				foreach (array_keys($groups) as $groupId) {
					$fullGroupIds[] = ko_groups_decode($groupId, 'full_gid');
				}
				asort($fullGroupIds);
				$fullGroupIds = array_merge($fullGroupIds);
				foreach ($fullGroupIds as $fullGroupId) {
					$key = 'MODULEgrp' . $fullGroupId;
					$values[$key] = $key;
					$descs[$key] = ko_groups_decode($fullGroupId, "group_desc_full");
				}

				$avalues = $adescs = array();
				foreach($_SESSION['import_csv']['dbcols'] as $col) {
					if(!$col || !in_array($col, $values)) continue;
					$avalues[$col] = $col;
					$adescs[$col] = $descs[$col];
				}

				$frmgroup[$gc]["row"][$rowcounter]["inputs"][0] = array("desc" => getLL("leute_import_state1_csv_dbcols"),
						"type" => "doubleselect",
						"js_func_add" => "double_select_add",
						"name" => "sel_dbcols",
						"params" => 'size="7"',
						"show_moves" => true,
						"values" => $values,
						"descs" => $descs,
						"avalues" => $avalues,
						"adescs" => $adescs,
						'avalue' => implode(',', $avalues),
						);
				$frmgroup[$gc]["row"][$rowcounter++]["inputs"][1] = array("desc" => getLL("leute_import_state1_csv"),
						"type" => "file",
						"name" => "csv",
						"params" => 'size="60"',
						);
				$frmgroup[$gc]["row"][$rowcounter]["inputs"][0] = array("desc" => getLL("leute_import_state1_csv_separator"),
						"type" => "text",
						"name" => "txt_separator",
						"params" => 'size="6"',
						"value" => $_SESSION['import_csv']['separator'] ? $_SESSION['import_csv']['separator'] : ',',
						);
				$frmgroup[$gc]["row"][$rowcounter++]["inputs"][1] = array("desc" => getLL("leute_import_state1_csv_content_separator"),
						"type" => "text",
						"name" => "txt_content_separator",
						"params" => 'size="6"',
						"value" => $_SESSION['import_csv']['content_separator'] ? $_SESSION['import_csv']['content_separator'] : '&quot;',
						);
				$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('leute_import_state1_csv_first_line'),
						'type' => 'checkbox',
						'name' => 'chk_first_line',
						'value' => '1',
						'params' => $_SESSION['import_csv']['first_line'] == 1 ? 'checked="checked"' : '',
						);
				$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('leute_import_state1_csv_file_encoding'),
						'type' => 'select',
						'name' => 'sel_file_encoding',
						'params' => 'size="0"',
						'values' => array('utf-8', 'latin1', 'macintosh'),
						'descs' => array('Unicode (UTF-8)', 'Latin1 (iso-8859-1)', 'Mac Roman'),
						'value' => $_SESSION['import_csv']['file_encoding'],
						);
				$smarty->assign("tpl_titel", getLL("leute_import_state2"));
				$smarty->assign("tpl_hide_cancel", true);
				$smarty->assign("tpl_submit_value", getLL("next"));
				$smarty->assign("tpl_action", "import");
				$smarty->assign("tpl_groups", $frmgroup);
				$smarty->display('ko_formular.tpl');
			}  //csv
		break;  //2

		case 3:
			//Kept for CSV-Settings like date_*, mgrp_*, bgrp_*
		break;  //3

		case 4:
			$num_entries = sizeof($_SESSION["import_data"]);

			//found entries
			$entries = "<table><tr>";
			foreach($_SESSION["import_data"][0] as $key => $value) {
				if (mb_substr($key, 0, 9) == "MODULEgrp") {
					$entries .= '<th>'.ko_groups_decode(mb_substr($key, 9), "group_desc_full").'</th>';
				}
				else {
					$entries .= '<th>'.getLL("kota_ko_leute_".$key).'</th>';
				}
			}
			$entries .= "</tr>";
			for($i=0; $i<5; $i++) {
				if($_SESSION["import_data"][$i]) {
					$entries .= '<tr><td>'.implode("</td><td>", $_SESSION["import_data"][$i])."</td></tr>";
				}
			}
			$entries .= "</table>";

			//assign to group
			if(ko_module_installed('groups')) {
				//Get access rights for groups module
				if(!is_array($access['groups'])) ko_get_access('groups');
				//Read in all groups
				if(!is_array($all_groups)) ko_get_groups($all_groups);
				$values = $descs = array(0 => '');
				if(!$groups) $groups = ko_groups_get_recursive(ko_get_groups_zwhere(), TRUE);
				ko_get_grouproles($all_roles);
				foreach($groups as $g) {
					if($access['groups']['ALL'] < 2 && $access['groups'][$g['id']] < 2) continue;
					//Full id including parent relationship
					$motherline = ko_groups_get_motherline($g['id'], $all_groups);
					$mids = array();
					foreach($motherline as $mg) {
						$mids[] = 'g'.$all_groups[$mg]['id'];
					}

					//Name
					$desc = '';
					$depth = sizeof($motherline);
					for($i=0; $i<$depth; $i++) $desc .= '&nbsp;&nbsp;';
					$desc .= $g['name'];

					if($g['type'] == 1) {
						$values[] = '_DISABLED_';
						$descs[] = $desc;
					} else {
						$values[] = (sizeof($mids) > 0 ? implode(':', $mids).':' : '').'g'.$g['id'];
						$descs[] = $desc;
						if($g['roles']) {
							$roles = explode(',', $g['roles']);
							foreach($roles as $rid) {
								$values[] = (sizeof($mids) > 0 ? implode(':', $mids).':' : '').'g'.$g['id'].':r'.$rid;
								$descs[] = $desc.': '.$all_roles[$rid]['name'];
							}
						}
					}
				}

				$rowcounter = 0;
				$gc = 0;
				$frmgroup[$gc]['row'][$rowcounter++]['inputs'][0] = array('desc' => getLL('leute_import_state4_header').' '.$num_entries.'<br />',
						'type' => 'label',
						'value' => $entries,
						);
				$frmgroup[$gc]['row'][$rowcounter++]['inputs'][0] = array('desc' => getLL('leute_import_state4_group'),
						'type' => 'select',
						'name' => 'sel_group',
						'params' => 'size="0"',
						'values' => $values,
						'descs' => $descs,
						);
			}//if(ko_module_installed(groups))

			$smarty->assign('tpl_titel', getLL('leute_import_state4'));
			$smarty->assign('tpl_hide_cancel', true);
			$smarty->assign('tpl_submit_value', getLL('leute_import_do_import'));
			$smarty->assign('tpl_action', 'do_import');
			$smarty->assign('tpl_groups', $frmgroup);
			$smarty->display('ko_formular.tpl');
		break;  //4
	}
}//ko_leute_import()






/**
 * Show settings for PDF export of address data
 */
function ko_export_leute_as_pdf_settings($layout_id) {
	global $smarty;
	global $LEUTE_NO_FAMILY;

	$gc = $rowcounter = 0;

	//Get layout from db
	$_layout = db_select_data("ko_pdf_layout", "WHERE `id` = '$layout_id'", "*", "", "", true);
	$layout = unserialize($_layout["data"]);

	//Prepare filter select
	$filter_values = $filter_descs = array();
	if(sizeof($_SESSION['my_list']) > 0) {
		$filter_values[] = '_mylist';
		$filter_descs[] = getLL('leute_export_pdf_filter_mylist');
	}
	if($layout["filter"]) {
		$filter_values[] = "_layout";
		$filter_descs[] = getLL("leute_export_pdf_filter_layout");
	}
	$filter_values[] = "_current";
	$filter_descs[] = getLL("leute_export_pdf_filter_current");
	$filter_values[] = "_currently_sel";
	$filter_descs[] = getLL("leute_export_pdf_filter_currently_sel");
	$filterset = array_merge((array)ko_get_userpref('-1', '', 'filterset', 'ORDER BY `key` ASC'), (array)ko_get_userpref($_SESSION['ses_userid'], '', 'filterset', 'ORDER BY `key` ASC'));
	foreach($filterset as $f) {
		$filter_values[] = $f['user_id'] == '-1' ? '@G@'.$f['key'] : $f['key'];
		$filter_descs[] = $f['user_id'] == '-1' ? getLL('itemlist_global_short').' '.$f['key'] : $f['key'];
	}
	//Set current selection
	if($layout['filter']) {  //Filter as set for this pdf layout
		$filter_selected = '_layout';
	} else if($_SESSION['show_back'] == 'show_my_list' && sizeof($_SESSION['my_list']) > 0) {  //Use my list if entries
		$filter_selected = '_mylist';
	} else {  //Otherwise use currently applied filter
		$filter_selected = '_current';
	}

	//Prepare columns select
	if($layout["columns"]) {
		$columns_values[] = "_layout";
		$columns_descs[] = getLL("leute_export_pdf_columns_layout");
	}
	$columns_values[] = "_current";
	$columns_descs[] = getLL("leute_export_pdf_columns_current");
	$itemset = array_merge((array)ko_get_userpref('-1', '', 'leute_itemset', 'ORDER BY `key` ASC'), (array)ko_get_userpref($_SESSION['ses_userid'], '', 'leute_itemset', 'ORDER BY `key` ASC'));
	foreach($itemset as $f) {
		$columns_values[] = $f['user_id'] == '-1' ? '@G@'.$f['key'] : $f['key'];
		$columns_descs[] = $f['user_id'] == '-1' ? getLL('itemlist_global_short').' '.$f['key'] : $f['key'];
	}

	$group[$gc] = array("titel" => getLL("leute_export_pdf_title_data"), "state" => "open");
	$group[$gc]["row"][$rowcounter++]["inputs"][0] = array("desc" => getLL("leute_export_pdf_filter"),
																												 "type" => "select",
																												 "name" => "pdf[filter]",
																												 "values" => $filter_values,
																												 "descs" => $filter_descs,
																												 "value" => $filter_selected,
																												 "params" => 'size="0"'
																												 );
	$group[$gc]["row"][$rowcounter]["inputs"][0] = array("desc" => getLL("leute_export_pdf_columns"),
																											 "type" => "select",
																											 "name" => "pdf[columns]",
																											 "values" => $columns_values,
																											 "descs" => $columns_descs,
																											 "value" => "_layout",
																											 "params" => 'size="0"'
																											 );

	$children_check = $layout["columns_children"] ? true : false;
	if (!$LEUTE_NO_FAMILY) {
		$group[$gc]["row"][$rowcounter]["inputs"][1] = array("desc" => getLL("leute_export_pdf_show_children"),
			"type" => "checkbox",
			"name" => "pdf[columns_children]",
			"value" => "1",
			"params" => $children_check ? 'checked="checked"' : "",
		);
	}

	//Header and Footer
	$group[++$gc] = array("titel" => getLL("leute_export_pdf_title_headerfooter"), "state" => "open");
	$group[$gc]["row"][$rowcounter++]["inputs"][0] = array("desc" => getLL("help"),
																												 "type" => "html",
																												 "value" => getLL("leute_export_pdf_help_headerfooter"),
																												 "colspan" => 'colspan="3"',
																												 );
	$group[$gc]["row"][$rowcounter++]["inputs"][0] = array("type" => "   ", "colspan" => 'colspan="3"');
	$group[$gc]["row"][$rowcounter]["inputs"][0] = array("desc" => getLL("leute_export_pdf_header_left"),
																												 "type" => "text",
																												 "name" => "pdf[header][left][text]",
																												 "value" => $layout["header"]["left"]["text"],
																												 "params" => 'size="50"',
																												 );
	$group[$gc]["row"][$rowcounter]["inputs"][1] = array("desc" => getLL("leute_export_pdf_header_center"),
																												 "type" => "text",
																												 "name" => "pdf[header][center][text]",
																												 "value" => $layout["header"]["center"]["text"],
																												 "params" => 'size="50"',
																												 );
	$group[$gc]["row"][$rowcounter++]["inputs"][2] = array("desc" => getLL("leute_export_pdf_header_right"),
																												 "type" => "text",
																												 "name" => "pdf[header][right][text]",
																												 "value" => $layout["header"]["right"]["text"],
																												 "params" => 'size="50"',
																												 );
	$group[$gc]["row"][$rowcounter]["inputs"][0] = array("desc" => getLL("leute_export_pdf_footer_left"),
																												 "type" => "text",
																												 "name" => "pdf[footer][left][text]",
																												 "value" => $layout["footer"]["left"]["text"],
																												 "params" => 'size="50"',
																												 );
	$group[$gc]["row"][$rowcounter]["inputs"][1] = array("desc" => getLL("leute_export_pdf_footer_center"),
																												 "type" => "text",
																												 "name" => "pdf[footer][center][text]",
																												 "value" => $layout["footer"]["center"]["text"],
																												 "params" => 'size="50"',
																												 );
	$group[$gc]["row"][$rowcounter++]["inputs"][2] = array("desc" => getLL("leute_export_pdf_footer_right"),
																												 "type" => "text",
																												 "name" => "pdf[footer][right][text]",
																												 "value" => $layout["footer"]["right"]["text"],
																												 "params" => 'size="50"',
																												 );

	$smarty->assign("tpl_titel", getLL("leute_export_pdf").": ".$_layout["name"]);
	$smarty->assign("tpl_submit_value", getLL("leute_export_pdf_submit"));
	$smarty->assign("tpl_action", "do_export_pdf");
	$smarty->assign("tpl_cancel", "show_all");
	$smarty->assign("tpl_groups", $group);
	$smarty->assign("tpl_hidden_inputs", array(0 => array("name" => "layout_id", "value" => $layout_id), 1 => array('name' => 'pdf[filter_sel_ids]', 'value' => $_POST['ids'])));

	$smarty->display('ko_formular.tpl');
}//ko_export_leute_as_pdf_settings()




/**
 * Exports address data as pdf file
 */
function ko_export_leute_as_pdf($layout_id, $settings="", $force=false) {
	global $ko_path;
	global $all_groups;
	global $access;

	$z_where = "";
	if(!$layout_id) return false;

	if(!$all_groups) ko_get_groups($all_groups);
	$all_datafields = db_select_data("ko_groups_datafields", "WHERE 1=1", "*");


	//Get selected layout
	$_layout = db_select_data("ko_pdf_layout", "WHERE `id` = '$layout_id'", "*", "", "", true);
	$layout = unserialize($_layout["data"]);
	/**
	 * Array as it is stored in ko_pdf_layout
	 **
	$layout = array("page" => array("orientation" => "L", "margin_left" => 10, "margin_top" => 10, "margin_right" => 10, "margin_bottom" => 10),
									"header" => array("left" => array("font" => "arialb", "fontsize" => "13"), "center" => array(), "right" => array()),
									"footer" => array("left" => array("font" => "arial", "fontsize" => "10"), "center" => array(), "right" => array()),
									"headerrow" => array("font" => "arialb", "fontsize" => "11", "fillcolor" => "255"),
									"columns" => array("vorname", "nachname", "plz", "ort"),
									"columns_children" => FALSE,
									"filter" => array(),
									"sort" => "nachname",
									"sort_order" => "ASC",
									"col_template" => array("_default" => array("font" => "arial", "fontsize" => "11")),
									);
	**/


	//GET-Data
	$post = $settings ? $settings : $_POST["pdf"];

	/* Columns to be used */
	$cols = array();
	//Get columns from layout
	if($post["columns"] == "_layout" && $layout["columns"]) {
		$do_cols = $layout["columns"];
	}
	//Get columns as array from post (used for T3-Extension kool_leute)
	else if(is_array($post["columns"]) && sizeof($post["columns"]) > 0) {
		$do_cols = $post["columns"];
	}
	//Get columns from userprefs
	else if($post["columns"] && $post["columns"] != "_current") {
		if(mb_substr($post['columns'], 0, 3) == '@G@') $value = ko_get_userpref('-1', mb_substr($post["columns"], 3), "leute_itemset");
		else $value = ko_get_userpref($_SESSION["ses_userid"], $post["columns"], "leute_itemset");
		$do_cols = explode(",", $value[0]["value"]);
	}
	//Otherwise use the currently displayed columns
	else {
		$do_cols = $_SESSION["show_leute_cols"];
	}
	$layout["columns_children"] = $post["columns_children"] ? true : false;

	//Prepare columns with group/groupdatafield info
	$leute_col_name = ko_get_leute_col_name($groups_hierarchie=false, $add_group_datafields=true, "view", $force);

	//Add children columns if given
	if($layout["columns_children"]) {
		foreach(explode(',', ko_get_userpref($_SESSION['ses_userid'], 'leute_children_columns')) as $col) {
			$ll = getLL("leute_children_col".$col);
			if(!$ll) {
				if(in_array(mb_substr($col, 0, 8), array('_father_', '_mother_'))) {
					$ll = $leute_col_name[mb_substr($col, 8)].' '.getLL('leute_children_col'.mb_substr($col, 0, 7));
				} else {
					$ll = $leute_col_name[mb_substr($col, 1)];
				}
			}
			$leute_col_name_add[$col] = $ll ? $ll : mb_substr($col, 1);
			$do_cols[] = $col;
		}
	}
	$leute_col_name = array_merge($leute_col_name, (array)$leute_col_name_add);

	foreach($do_cols as $c) {
		$cols[$c] = $leute_col_name[$c];
	}
	$layout["columns"] = $cols;



	/* Sorting */
	if($layout["sort"]) {
		$layout["sort"] = array($layout["sort"]);
		$layout["sort_order"] = array($layout["sort_order"]);
	} else if ($post["filter"] == "_layout" && $layout["filter"] && $layout["filter"]['sort']) {
		$layout["sort"] = explode(',', $layout["filter"]["sort"]);
		$layout["sort_order"] = explode(',', $layout["filter"]["sort_order"]);
	} else if ($post["filter"] != "_current" && $post["filter"] != "_currently_sel") {
		if(mb_substr($post["filter"], 0, 3) == '@G@') $value = ko_get_userpref('-1', mb_substr($post["filter"], 3), "filterset");
		else $value = ko_get_userpref($_SESSION["ses_userid"], $post["filter"], "filterset");
		$filter = unserialize($value[0]["value"]);
		if (trim($filter["sort"]) != '') {
			$layout["sort"] = explode(',', $filter["sort"]);
			$layout["sort_order"] = explode(',', $filter["sort_order"]);
		}
		else {
			$layout["sort"] = $_SESSION["sort_leute"];
			$layout["sort_order"] = $_SESSION["sort_leute_order"];
		}
	} else {
		$layout["sort"] = $_SESSION["sort_leute"];
		$layout["sort_order"] = $_SESSION["sort_leute_order"];
	}
	//Switch sorting for DOB column (according to userpref)
	if(in_array('geburtsdatum', $layout['sort']) && ko_get_userpref($_SESSION['ses_userid'], 'leute_sort_birthdays') == 'monthday') {
		$new = array();
		foreach($layout['sort'] as $col) {
			if($col == 'geburtsdatum') $new[] = 'MODULE'.$col;
			else $new[] = $col;
		}
		$layout['sort'] = $new;
	}



	/* Get Filter */
	if($post["filter"] == "_layout") {
		$do_filter = $layout["filter"];
	}
	else if ($post['filter'] == '_currently_sel') {
		$ids = $post['filter_sel_ids'];
		if (trim($ids) == '') {
			$z_where = " AND 1=2 ";
		}
		else {
			$z_where = " AND `id` IN (" . $ids . ') ';
		}
	}
	//Use my list
	else if($post['filter'] == '_mylist') {
		if(sizeof($_SESSION['my_list']) > 0) {
			$z_where = " AND `id` IN ('".implode("','", $_SESSION['my_list'])."') ";
		} else {
			$z_where = ' AND 1=2 ';
		}
	}
	//Get filter as array from post (used for T3-Extension kool_leute)
	else if(is_array($post["filter"]) && sizeof($post["filter"]) > 0) {
		$z_where = $post["filter"]["where"];
	}
	//Get filter from userpref
	else if($post["filter"] && $post["filter"] != "_current") {
		if(mb_substr($post['filter'], 0, 3) == '@G@') {
			$value = ko_get_userpref('-1', "", "filterset");
			$post['filter'] = mb_substr($post['filter'], 3);
		} else $value = ko_get_userpref($_SESSION["ses_userid"], "", "filterset");
		foreach($value as $v_i => $v) {
			if($v["key"] == $post["filter"]) $do_filter = unserialize($value[$v_i]["value"]);
		}
	}
	//Use current filter
	else {
		$do_filter = $_SESSION["filter"];
	}



	//Header and Footer texts
	$layout["header"]["left"]["text"] = $post["header"]["left"]["text"];
	$layout["header"]["center"]["text"] = $post["header"]["center"]["text"];
	$layout["header"]["right"]["text"] = $post["header"]["right"]["text"];
	$layout["footer"]["left"]["text"] = $post["footer"]["left"]["text"];
	$layout["footer"]["center"]["text"] = $post["footer"]["center"]["text"];
	$layout["footer"]["right"]["text"] = $post["footer"]["right"]["text"];



	//Get data from ko_leute
	foreach($layout["sort"] as $i => $col) {
		if(mb_substr($col, 0, 6) != "MODULE") {
			$sort_add[] = $col." ".$layout["sort_order"][$i];
		}
	}
	if(!in_array("nachname", $layout["sort"])) $sort_add[] = "nachname ASC";
	if(!in_array("vorname", $layout["sort"])) $sort_add[] = "vorname ASC";
	$sql_sort = "ORDER BY ".implode(", ", $sort_add);
	//z_where can be set if called by T3 extension kool_leute through get.php
	if(!$z_where) apply_leute_filter($do_filter, $z_where, $access['leute']['ALL'] < 1);
	ko_get_leute($all, $z_where, "", "", $sql_sort);
	if(true === ko_manual_sorting($layout["sort"])) {
		$all = ko_leute_sort($all, $layout["sort"], $layout["sort_order"], true, $forceDatafields=true);
	}


	//TODO: Apply rectype (add setting in preset or form)

	//Loop all addresses
	$data = array();
	foreach($all as $id => $person) {
		$row = array();
		foreach($layout["columns"] as $col => $colName) {
			//TODO: Set layouts for different columns (needs change in settings and in fpdf/mc_table.php)
			$value = map_leute_daten($person[$col], $col, $person, $all_datafields, $force);
			if(is_array($value)) {
				$row[] = ko_unhtml(strip_tags($value[0]));
			} else {
				$row[] = ko_unhtml(strip_tags($value));
			}
		}//foreach(columns as col)

		$data[] = $row;
	}//foreach(all as id => person)
	unset($all);


	$filename = $ko_path."download/pdf/".getLL("leute_filename_pdf").strftime("%d%m%Y_%H%M%S", time()).".pdf";
	ko_export_to_pdf($layout, $data, $filename);

	return $filename;
}//ko_export_leute_as_pdf()






function ko_leute_delete_person($del_id) {
	ko_get_person_by_id($del_id, $del_person);

	//Check for column which prevent address from being deleted
	$ok = TRUE;
	$del_cols = explode(',', ko_get_setting('leute_no_delete_columns'));
	if(sizeof($del_cols) > 0) {
		foreach($del_cols as $c) {
			if(!$c) continue;
			if($del_person[$c]) $ok = FALSE;
		}
	}
	if(!$ok) return FALSE;

	if($del_person['deleted'] == 1) {  //really delete already deleted entry
		//Check for setting if this is allowed
		if(ko_get_setting('leute_real_delete') != 1) return false;

		db_delete_data('ko_leute', "WHERE `id` = '$del_id'");

		//delete group datafields
		db_delete_data('ko_groups_datafields_data', "WHERE `person_id` = '$del_id'");
	}
	else {
		//add version entry
		ko_save_leute_changes($del_id, $del_person);

		//mark as deleted
		db_update_data('ko_leute', "WHERE `id` = '$del_id'", array('deleted' => '1', 'famid' => '0'));

		//unset assigned login
		$login = db_select_data('ko_admin', "WHERE `leute_id` = '$del_id'", 'id,leute_id', '', '', true);
		if(is_array($login) && $login['leute_id'] == $del_id) {
			db_update_data('ko_admin', "WHERE `id` = '".$login['id']."'", array('leute_id' => '0'));
		}

		//unsubscribe from ezmlm
		if(defined('EXPORT2EZMLM') && EXPORT2EZMLM) {
			foreach(explode(',', $del_person['groups']) as $grp) {
				$gid = ko_groups_decode($grp, 'group_id');
				if($all_groups[$gid]['ezmlm_list']) ko_ezmlm_unsubscribe($all_groups[$gid]['ezmlm_list'], $all_groups[$gid]['ezmlm_moderator'], $del_person['email']);
			}
		}

		//set group datafields to deleted
		db_update_data('ko_groups_datafields_data', "WHERE `person_id` = '$del_id'", array('deleted' => '1'));

		//Update group count
		foreach(explode(',', $del_person['groups']) as $fullgid) {
			$group = ko_groups_decode($fullgid, 'group');
			if(!$group['maxcount']) continue;
			ko_update_group_count($group['id'], $group['count_role']);
		}
	}//if(deleted == 0)


	//LDAP
	if(ko_do_ldap()) {
		$ldap = ko_ldap_connect();
		ko_ldap_del_person($ldap, $del_id);
		ko_ldap_close($ldap);
	}

	//Create log entry
	ko_log_diff('delete_person', $del_person);

	//Delete family if the deleted person was the last member
	if($del_person['famid'] > 0) {
		$num = ko_get_personen_by_familie($del_person['famid'], $asdf);
		if($num <= 0) {
			db_update_data('ko_leute', "WHERE `famid` = '".$del_person['famid']."'", array('famid' => '0'));
			db_delete_data('ko_familie', "WHERE `famid` = '".$del_person['famid']."'");
		}
	}

	return TRUE;
}//ko_leute_delete_person()




/**
 * Shows address charts
 */
function ko_leute_chart($_type="") {
	global $LEUTE_CHART_TYPES;
	global $access;

	//Get SQL for current filter
	apply_leute_filter($_SESSION["filter"], $where_base, $access['leute']['ALL'] < 1);

	$do_types = $_type ? array($_type) : $_SESSION["show_leute_chart"];

	//Call all chart functions
	$html = array();
	foreach($do_types as $type) {
		if(!function_exists("ko_leute_chart_".$type) || !in_array($type, $LEUTE_CHART_TYPES)) continue;
		$html[$type] = call_user_func("ko_leute_chart_".$type, $where_base);
	}

	if($_type) {
		$out = '<label>'.getLL("leute_chart_title_".$type).'</label>'.$html[$type];
	} else {
		//Generate HTML output
		$out = '<div class="list_title">'.getLL("leute_chart_title").'</div><br clear="all" />';
		foreach($html as $type => $code) {
			$out .= '<div class="leute_chart" name="leute_chart_'.$type.'" id="leute_chart_'.$type.'"><label>'.getLL("leute_chart_title_".$type).'</label>'.$code.'</div>';
		}
		$out .= '<br clear="all" />';
	}

	return $out;
}//ko_leute_chart()





/**
 * Display the number of persons in all childgroups of a given group
 */
function ko_leute_chart_subgroups($where_base) {
	global $all_groups, $ko_path, $access;

	//Get access rights and all groups
	if(!is_array($all_groups)) ko_get_groups($all_groups);

	$roles = db_select_data('ko_grouproles', 'WHERE 1');

	//Find leave groups
	$not_leaves = db_select_distinct('ko_groups', 'pid');
	$all = db_select_distinct('ko_groups', 'id');
	$leaves = array_diff($all, $not_leaves);

	//Prepare group select
	$groups = ko_groups_get_recursive(ko_get_groups_zwhere(), true);
	$gsel = '<select name="sel_leute_chart_subgroups_gid" size="0" onchange="sendReq(\''.$ko_path.'leute/inc/ajax.php\', \'action,gid,sesid\', \'leutechartsubgroups,\'+this.options[this.selectedIndex].value+\','.session_id().'\', do_element);">';
	$gsel .= '<option value=""></option>';
	foreach($groups as $grp) {
		//Don't show leaves as these would produce empty chart
		if(in_array($grp['id'], $leaves)) continue;
		if($access['groups']['ALL'] < 1 && $access['groups'][$grp['id']] < 1) continue;
		$mother_line = ko_groups_get_motherline($grp["id"], $all_groups);
		//Display hierarchy
		$pre = "";
		$depth = sizeof($mother_line);
		for($i=0; $i<$depth; $i++) $pre .= "&nbsp;&nbsp;";
		//Add entry with no role
		$sel = $grp["id"] == $_SESSION["leute_chart_subgroups_gid"] ? 'selected = "selected"' : '';
		$gsel .= '<option value="'.$grp["id"].'" '.$sel.'>'.$pre.ko_html($grp["name"]).'</option>';
		//Add entries for each role (if any)
		if($grp['roles'] != '') {
			foreach(explode(',', $grp['roles']) as $rid) {
				$sel = $grp['id'].':'.$rid == $_SESSION['leute_chart_subgroups_gid'] ? 'selected = "selected"' : '';
				$gsel .= '<option value="'.$grp['id'].':'.$rid.'" '.$sel.'>'.$pre.ko_html($grp['name'].': '.$roles[$rid]['name']).'</option>';
			}
		}
	}
	$gsel .= '</select>';


	//Draw pie chart if a group id is given
	if($_SESSION["leute_chart_subgroups_gid"]) {
		list($gid, $rid) = explode(':', $_SESSION['leute_chart_subgroups_gid']);
		//Get all children groups
		$groups = db_select_data("ko_groups", "WHERE `pid` = '$gid' ".ko_get_groups_zwhere(), "*", "ORDER BY `name` ASC");

		$value = $label = array();
		foreach($groups as $id => $group) {
			$value[] = db_get_count("ko_leute", "id", $where_base." AND `groups` REGEXP 'g$id".($rid != '' ? '[gr0-9:]*:r'.$rid : '')."'");
			$label[] = mb_strlen($group["name"]) > 15 ? ko_truncate($group["name"], 15, 4) : $group["name"];
		}

		//Create img link for preview chart
		$r  = '<img border="0" src="'.$ko_path.'inc/graph_bar.php?data='.implode("*", $value).'&label='.urlencode(implode("*", $label));
		$r .= '&size=400x250&yValueMode=3&textXOrientation=vertical';
		$r .= '" />';

		//Create img link for popup to show bar chart a bit bigger
		$p  = $ko_path.'inc/graph_bar.php?data='.implode("*", $value).'&label='.urlencode(implode("*", $label));
		$p .= '&size=1000x450&yValueMode=3&textXOrientation=vertical';

		$graph = '<a href="'.$p.'" target="_blank">'.$r.'</a>';
	}//if(_SESSION[leute_chart_roles_gid])

	return getLL("leute_chart_subgroups_select_group")."<br />".$gsel."<br />".$graph;
}//ko_leute_chart_subgroups()




/**
 * Display roles for the selected group and all its subgroups
 */
function ko_leute_chart_roles($where_base) {
	global $all_groups, $ko_path, $access;

	//Get access rights and all groups
	if(!is_array($all_groups)) ko_get_groups($all_groups);

	//Prepare group select
	$groups = ko_groups_get_recursive(ko_get_groups_zwhere());
	$gsel = '<select name="sel_leute_chart_roles_gid" size="0" onchange="sendReq(\''.$ko_path.'leute/inc/ajax.php\', \'action,gid,sesid\', \'leutechartroles,\'+this.options[this.selectedIndex].value+\','.session_id().'\', do_element);">';
	$gsel .= '<option value=""></option>';
	foreach($groups as $grp) {
		if($access['groups']['ALL'] < 1 && $access['groups'][$grp['id']] < 1) continue;
		$mother_line = ko_groups_get_motherline($grp["id"], $all_groups);
		//Display hierarchy
		$pre = "";
		$depth = sizeof($mother_line);
		for($i=0; $i<$depth; $i++) $pre .= "&nbsp;&nbsp;";
		//Build select
		$sel = $grp["id"] == $_SESSION["leute_chart_roles_gid"] ? 'selected = "selected"' : '';
		$gsel .= '<option value="'.$grp["id"].'" '.$sel.'>'.$pre.ko_html($grp["name"]).'</option>';
	}
	$gsel .= '</select>';


	//Draw pie chart if a group id is given
	if($_SESSION["leute_chart_roles_gid"]) {
		$_value = $_label = array();
		$gid = $_SESSION["leute_chart_roles_gid"];
		$group = $all_groups[$gid];

		//Go through all roles but only display those with at least one entry
		//This way also roles of subgroups will get displayed, even if a dummy group was selected
		ko_get_grouproles($roles);
		foreach($roles as $role) {
			$num = db_get_count("ko_leute", "id", $where_base." AND `groups` REGEXP 'g".$gid."[g:0-9]*r".$role["id"]."'");
			if($num) {
				$_value[] = $num;
				$_label[] = $role["name"];
			}
		}
		//Add all persons assigned without a role
		$num = db_get_count("ko_leute", "id", $where_base." AND `groups` REGEXP 'g".$gid."[g:0-9]*' AND `groups` NOT REGEXP 'g".$gid."[g:0-9]*r[0-9]{6}'");
		if($num) {
			$_value[] = $num;
			$_label[] = getLL("leute_chart_none");
		}

		//Sort descending by num
		arsort($_value);
		$value = $label = array();
		foreach($_value as $vi => $v) {
			$value[] = $v;
			$label[] = $_label[$vi];
		}

		if(sizeof($value) > 0) {
			$graph = '<img src="'.$ko_path.'inc/graph_piechart.php?data='.implode("*", $value).'&label='.urlencode(implode("*", $label)).'" />';
		} else {
			$graph = '';
		}
	}//if(_SESSION[leute_chart_roles_gid])

	return getLL("leute_chart_roles_select_group")."<br />".$gsel."<br />".$graph;
}//ko_leute_chart_roles()



/**
 * Chart function for addresses
 * Pie chart showing age distribution
 */
function ko_leute_chart_age_pie($where_base) {
	global $ko_path;

	$value = $label = array();
	/*
	//No birthday given
	$label[] = getLL("leute_chart_none");
	$where = $where_base." AND `geburtsdatum` = '0000-00-00'";
	$value[] = db_get_count("ko_leute", "id", $where);
	*/

	//Get number of people for these age spans
	$ages = array(array(0,10), array(11,20), array(21,30), array(31,40), array(41,50), array(51,60), array(61,70), array(71,120));
	foreach($ages as $span) {
		$where = $where_base."AND `geburtsdatum` != '0000-00-00' AND (DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(geburtsdatum, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(geburtsdatum, '00-%m-%d'))) >= ".$span[0]." AND (DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(geburtsdatum, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(geburtsdatum, '00-%m-%d'))) <= ".$span[1];
		$value[] = db_get_count("ko_leute", "id", $where);
		$label[] = $span[0]."-".$span[1];
	}
	return '<img src="'.$ko_path.'inc/graph_piechart.php?data='.implode("*", $value).'&label='.urlencode(implode("*", $label)).'" />';
}//ko_leute_chart_age_pie()



/**
 * Chart function for addresses
 * Pie chart showing age distribution
 */
function ko_leute_chart_age_bar($where_base) {
	global $db_connection, $ko_path;

	$value = $label = array();
	/*
	//No birthday given
	$label[] = getLL("leute_chart_none");
	$where = $where_base." AND `geburtsdatum` = '0000-00-00'";
	$value[] = db_get_count("ko_leute", "id", $where);
	*/

	$query = "SELECT (DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(geburtsdatum, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(geburtsdatum, '00-%m-%d'))) AS age, COUNT(`id`) AS `num` FROM `ko_leute` WHERE `geburtsdatum` != '0000-00-00' $where_base GROUP BY `age` ORDER BY `age` ASC";
	$result = mysqli_query($db_connection, $query);
	$data = array(); $min = 100; $max = 0;
	while($row = mysqli_fetch_assoc($result)) {
		$data[$row["age"]] = $row["num"];
		$min = min($min, $row["age"]);
		$max = max($max, $row["age"]);
	}
	for($i = $min; $i<= $max; $i++) {
		$value[] = $data[$i];
		$label[] = $i;
	}
	//Create img link for preview chart
	$r  = '<img border="0" src="'.$ko_path.'inc/graph_bar.php?data='.implode("*", $value).'&label='.urlencode(implode("*", $label));
	$r .= '&size=400x250&yValueMode=0&xStep=10';
	$r .= '&title='.urlencode(getLL("leute_chart_title_age_bar"));
	$r .= '&title_x='.urlencode(getLL("leute_chart_title_age_bar_x"));
	$r .= '&title_y='.urlencode(getLL("leute_chart_title_age_bar_y"));
	$r .= '" />';

	//Create img link for popup to show bar chart a bit bigger
	$p  = $ko_path.'inc/graph_bar.php?data='.implode("*", $value).'&label='.urlencode(implode("*", $label));
	$p .= '&size=1000x450&yValueMode=3&xStep=5';
	$p .= '&title='.urlencode(getLL("leute_chart_title_age_bar"));
	$p .= '&title_x='.urlencode(getLL("leute_chart_title_age_bar_x"));
	$p .= '&title_y='.urlencode(getLL("leute_chart_title_age_bar_y"));

	$out = '<a href="'.$p.'" target="_blank">'.$r.'</a>';

	return $out;
}//ko_leute_chart_age_bar()




/**
 * Chart function for addresses
 * Pie chart showing birthday months
 */
function ko_leute_chart_birthday_months($where_base) {
	global $ko_path;

	/* Birthday months */
	$value = $label = array();
	for($m=1; $m<=12; $m++) {
		$where = $where_base."AND `geburtsdatum` != '0000-00-00' AND MONTH(`geburtsdatum`) = '$m'";
		$value[] = db_get_count("ko_leute", "id", $where);
		$label[] = strftime("%B", mktime(1,1,1, $m, 1, 2000));
	}
	return '<img src="'.$ko_path.'inc/graph_piechart.php?data='.implode("*", $value).'&label='.urlencode(implode("*", $label)).'" />';
}//ko_leute_chart_birthday_months()






function ko_leute_chart_generic_pie_enum($table, $where_base, $col, $ll_prefix="", $return_data=false) {
	global $ko_path;

	$enums = db_get_enums($table, $col);
	$value = $label = array();
	foreach($enums as $v) {
		$value[] = db_get_count($table, "id", $where_base." AND `$col` = '$v'");
		if($v) {
			$ll = getLL($ll_prefix.$v);
			$label[] = $ll ? $ll : $v;
		} else {
			$label[] = getLL("leute_chart_none");
		}
	}

	if($return_data) {
		return array("value" => $value, "label" => $label);
	} else {
		return '<img src="'.$ko_path.'inc/graph_piechart.php?data='.implode("*", $value).'&label='.urlencode(implode("*", $label)).'" />';
	}
}//ko_leute_chart_generic_pie_enum()


function ko_leute_chart_sex($where_base) {
	return ko_leute_chart_generic_pie_enum('ko_leute', $where_base, 'geschlecht', 'kota_ko_leute_geschlecht_');
}


function ko_leute_chart_famfunction($where_base) {
	$where_base .= " AND `famid` != '' ";
	return ko_leute_chart_generic_pie_enum('ko_leute', $where_base, 'famfunction', 'kota_ko_leute_famfunction_');
}




/**
 * Generic stats function for pie chart showing the first $max entries for the given $col
 */
function ko_leute_chart_generic_pie($table, $where_base, $col, $max=12) {
	global $db_connection, $ko_path;

	$value = $label = array();
	$query = "SELECT `$col`, COUNT(`id`) AS num FROM `$table` WHERE `$col` != '' $where_base GROUP BY `$col` ORDER BY `num` DESC";
	$result = mysqli_query($db_connection, $query);
	$num = 0; $div = 0;
	while($row = mysqli_fetch_assoc($result)) {
		$num++;
		if($num > $max) {
			$div += $row["num"];
			continue;
		}
		$value[] = $row["num"];
		$label[] = $row[$col];
	}
	if($div) {
		$value[] = $div;
		$label[] = getLL("leute_chart_misc");
	}

	return '<img src="'.$ko_path.'inc/graph_piechart.php?data='.implode("*", $value).'&label='.urlencode(implode("*", $label)).'" />';
}//ko_leute_chart_generic_pie()


function ko_leute_chart_city($where_base) {
	return ko_leute_chart_generic_pie("ko_leute", $where_base, "ort", 12);
}


function ko_leute_chart_zip($where_base) {
	return ko_leute_chart_generic_pie("ko_leute", $where_base, "plz", 12);
}


function ko_leute_chart_country($where_base) {
	return ko_leute_chart_generic_pie("ko_leute", $where_base, "land", 12);
}



function ko_leute_chart_lastchange($where_base) {
	global $ko_path, $DATETIME;

	$span = 30;
	$value = $label = array();
	$time = strtotime("-$span days");
	for($i=$span; $i>=0; $i--) {
		$value[] = db_get_count("ko_leute", "id", $where_base." AND `lastchange` REGEXP '".strftime("%Y-%m-%d", $time)."'");
		$label[] = strftime($DATETIME["dmy"], $time);
		$time = strtotime("+1 day", $time);
	}

	//Create img link for preview chart
	$r  = '<img border="0" src="'.$ko_path.'inc/graph_bar.php?data='.implode("*", $value).'&label='.urlencode(implode("*", $label));
	$r .= '&size=400x250&yValueMode=0&textXOrientation=vertical&xStep=2';
	$r .= '&title='.urlencode(getLL("leute_chart_title_lastchange"));
	$r .= '&title_x='.urlencode(getLL("leute_chart_title_lastchange_x"));
	$r .= '&title_y='.urlencode(getLL("leute_chart_title_lastchange_y"));
	$r .= '" />';

	return $r;
}



function ko_leute_export_xls_settings() {
	global $smarty, $ko_path, $xls_cols;
	global $access, $MODULES;

	//build form
	$gc = 0;
	$rowcounter = 0;
	//$frmgroup[$gc]['titel'] = getLL('leute_export_xls_settings');



	if ($_POST['sel_auswahl'] == 'allef' || $_POST['sel_auswahl'] == 'alleFam2' || $_POST['sel_auswahl'] == 'markiertef' || $_POST['sel_auswahl'] == 'markierteFam2') {
		//Family firstname
		$frmgroup[$gc]['row'][$rowcounter++]['inputs'][0] = array('desc' => getLL('admin_settings_options_leute_force_family_firstname'),
			'type' => 'select',
			'name' => 'sel_leute_force_family_firstname',
			'params' => 'size="0"',
			'values' => array(0, 1, 2),
			'descs' => array(getLL('admin_settings_options_leute_force_family_firstname_0'), getLL('admin_settings_options_leute_force_family_firstname_1'), getLL('admin_settings_options_leute_force_family_firstname_2')),
			'value' => ko_get_userpref($_SESSION['ses_userid'], 'leute_force_family_firstname'),
		);
	}



	$leute_col_name = ko_get_leute_col_name();
	$cols = db_get_columns('ko_leute');
	$exclude = array('id', 'famid', 'smallgroups', 'lastchange', 'kg_seit', 'kgleiter_seit', 'famfunction', 'picture', 'groups', 'deleted', 'hidden', 'crdate', 'cruserid');




	if ($_POST['sel_cols'] == 'children') {
		//Children columns
		$values = $descs = $avalues = $adescs = null;
		$values = array('_father', '_mother');
		$descs = array(getLL('leute_children_col_father'), getLL('leute_children_col_mother'));
		$value = ko_get_userpref($_SESSION['ses_userid'], 'leute_children_columns');

		foreach($cols as $_col) {
			$col = $_col['Field'];
			if(in_array($col, $exclude)) continue;
			if($leute_col_name[$col] == '') continue;
			$values[] = '_'.$col;
			$descs[] = $leute_col_name[$col];
			$values[] = '_father_'.$col;
			$descs[] = $leute_col_name[$col].' ('.getLL('leute_children_col_father').')';
			$values[] = '_mother_'.$col;
			$descs[] = $leute_col_name[$col].' ('.getLL('leute_children_col_mother').')';
		}

		//Prepare list with selected parents columns
		foreach(explode(',', $value) as $v) {
			if(in_array($v, $values)) {
				$avalues[] = $v;
				foreach($values as $kk => $vv) {
					if($vv == $v) {
						$adescs[] = $descs[$kk];
						continue;
					}
				}
			}
		}

		$frmgroup[$gc]['row'][$rowcounter++]['inputs'][0] = array('desc' => getLL('leute_settings_children_columns'),
			'type' => 'doubleselect',
			'js_func_add' => 'double_select_add',
			'name' => 'sel_children_columns',
			'values' => $values,
			'descs' => $descs,
			'avalue' => $value,
			'avalues' => $avalues,
			'adescs' => $adescs,
			'params' => 'size="7"',
			'show_moves' => TRUE,
		);
	}

	//Prepare list with linebreak columns
	$linebreak_avalues = array();
	$linebreak_adescs = array();
	$linebreak_values = array();
	$linebreak_descs = array();
	$linebreak_value = ko_get_userpref($_SESSION['ses_userid'], 'leute_linebreak_columns');
	foreach ($xls_cols as $col) {
		$linebreak_values[] = $col;
		$linebreak_descs[] = $leute_col_name[$col];
	}
	foreach(explode(',', $linebreak_value) as $v) {
		if(in_array($v, $linebreak_values)) {
			$linebreak_avalues[] = $v;
			foreach($linebreak_values as $kk => $vv) {
				if($vv == $v) {
					$linebreak_adescs[] = $linebreak_descs[$kk];
					continue;
				}
			}
		}
	}
	$frmgroup[$gc]['row'][$rowcounter++]['inputs'][0] = array('desc' => getLL('leute_settings_linebreak_columns'),
		'type' => 'doubleselect',
		'js_func_add' => 'double_select_add',
		'name' => 'sel_linebreak_columns',
		'values' => $linebreak_values,
		'descs' => $linebreak_descs,
		'avalue' => $linebreak_value,
		'avalues' => $linebreak_avalues,
		'adescs' => $linebreak_adescs,
		'params' => 'size="7"',
		'show_moves' => TRUE,
	);


	$frmgroup[$gc]['row'][$rowcounter++]['inputs'][0] = array('desc' => getLL('admin_settings_export_table_format'),
		'type' => 'select',
		'name' => 'export_table_format',
		'values' => array('xlsx', 'xls'),
		'descs' => array(getLL('admin_settings_export_table_format_xlsx'), getLL('admin_settings_export_table_format_xls')),
		'value' => ko_get_userpref($_SESSION['ses_userid'], 'export_table_format'),
	);


	//display the form
	$smarty->assign('tpl_titel', getLL('leute_export_xls_settings_title'));
	$smarty->assign('tpl_submit_value', getLL('leute_export_xls_settings_export'));
	$smarty->assign('tpl_action', 'leute_submit_export_xls');
	$cancel = ko_get_userpref($_SESSION['ses_userid'], 'default_view_leute');
	if(!$cancel) $cancel = 'show_all';
	$smarty->assign('tpl_cancel', $cancel);
	$smarty->assign('tpl_groups', $frmgroup);
	$smarty->display('ko_formular.tpl');
}




function ko_leute_settings() {
	global $smarty, $ko_path;
	global $access, $MODULES;
	global $LEUTE_NO_FAMILY;

	if($access['leute']['MAX'] < 1 || $_SESSION['ses_userid'] == ko_get_guest_id()) return false;

	//build form
	$gc = 0;
	$rowcounter = 0;
	$frmgroup[$gc]['titel'] = getLL('settings_title_user');

	//Layout and limit settings
	$frmgroup[$gc]['row'][$rowcounter++]['inputs'][0] = array('desc' => getLL('leute_settings_default_view'),
			'type' => 'select',
			'name' => 'sel_leute',
			'values' => array('show_all', 'show_adressliste', 'show_geburtstagsliste', 'list_kg'),
			'descs' => array(getLL('submenu_leute_show_all'), getLL('submenu_leute_show_adressliste'), getLL('submenu_leute_geburtstagsliste'), getLL('submenu_leute_list_kg')),
			'value' => ko_html(ko_get_userpref($_SESSION['ses_userid'], 'default_view_leute'))
			);
	$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('admin_settings_limits_numberof_people'),
			'type' => 'text',
			'params' => 'size="10"',
			'name' => 'txt_limit_leute',
			'value' => ko_html(ko_get_userpref($_SESSION['ses_userid'], 'show_limit_leute'))
			);
	$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('admin_settings_limits_numberof_smallgroups'),
			'type' => 'text',
			'params' => 'size="10"',
			'name' => 'txt_limit_kg',
			'value' => ko_html(ko_get_userpref($_SESSION['ses_userid'], 'show_limit_kg'))
			);

	$frmgroup[$gc]['row'][$rowcounter++]['inputs'][0] = array('type' => '   ');

	//Birthday settings
	$value = ko_get_userpref($_SESSION['ses_userid'], 'leute_sort_birthdays');
	if(!isset($value)) $value = 'monthday';
	$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('admin_settings_misc_sort_birthdays'),
			'type' => 'select',
			'name' => 'sel_leute_sort_birthdays',
			'values' => array('monthday', 'year'),
			'descs' => array(getLL('admin_settings_misc_sort_birthdays_monthday'), getLL('admin_settings_misc_sort_birthdays_year')),
			'value' => $value,
			);

	$filterset = array_merge((array)ko_get_userpref('-1', '', 'filterset', 'ORDER BY `key` ASC'), (array)ko_get_userpref($_SESSION['ses_userid'], '', 'filterset', 'ORDER BY `key` ASC'));
	$filter = unserialize(ko_get_userpref($_SESSION['ses_userid'], 'birthday_filter'));
	$values = $descs = array();
	$found = false;
	foreach($filterset as $f) {
		if($f['key'] == $filter['key']) $found = true;
		$global_tag = $f['user_id'] == '-1' ? getLL('leute_filter_global_short') : '';
		$values[] = $f['user_id'] == '-1' ? '@G@'.$f['key'] : $f['key'];
		$descs[] = $global_tag.' '.$f['key'];
	}
	//If filter preset from settings can not be found for this user, display it with value -1
	if(!$found && $filter['key']) {
		array_unshift($values, -1);
		array_unshift($descs, $filter['key']);
		$selected = -1;
	} else {
		$selected = $filter['user_id'] == -1 ? '@G@'.$filter['key'] : $filter['key'];
	}
	array_unshift($values, '');
	array_unshift($descs, '');

	$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('admin_settings_view_birthday_filter'),
			'type' => 'select',
			'name' => 'sel_birthday_filter',
			'params' => 'size="0"',
			'values' => $values,
			'descs' => $descs,
			'value' => $selected,
			);

	$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('admin_settings_view_birthdays').' +',
			'type' => 'text',
			'params' => 'size="10"',
			'name' => 'txt_geb_plus',
			'value' => ko_html(ko_get_userpref($_SESSION['ses_userid'], 'geburtstagsliste_deadline_plus'))
			);
	$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('admin_settings_view_birthdays').' -',
			'type' => 'text',
			'params' => 'size="10"',
			'name' => 'txt_geb_minus',
			'value' => ko_html(ko_get_userpref($_SESSION['ses_userid'], 'geburtstagsliste_deadline_minus'))
			);

	$frmgroup[$gc]['row'][$rowcounter++]['inputs'][0] = array('type' => '   ');


	// check whether LEUTE_NO_FAMILY is set
	if (!$LEUTE_NO_FAMILY) {

		//Family firstname
		$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('admin_settings_options_leute_force_family_firstname'),
			'type' => 'select',
			'name' => 'sel_leute_force_family_firstname',
			'params' => 'size="0"',
			'values' => array(0, 1, 2),
			'descs' => array(getLL('admin_settings_options_leute_force_family_firstname_0'), getLL('admin_settings_options_leute_force_family_firstname_1'), getLL('admin_settings_options_leute_force_family_firstname_2')),
			'value' => ko_get_userpref($_SESSION['ses_userid'], 'leute_force_family_firstname'),
		);


		//Children columns
		$values = $descs = $avalues = $adescs = null;
		$values = array('_father', '_mother');
		$descs = array(getLL('leute_children_col_father'), getLL('leute_children_col_mother'));
		$value = ko_get_userpref($_SESSION['ses_userid'], 'leute_children_columns');

		$leute_col_name = ko_get_leute_col_name();
		$cols = db_get_columns('ko_leute');
		$exclude = array('id', 'famid', 'smallgroups', 'lastchange', 'kg_seit', 'kgleiter_seit', 'famfunction', 'picture', 'groups', 'deleted', 'hidden', 'crdate', 'cruserid');
		foreach($cols as $_col) {
			$col = $_col['Field'];
			if(in_array($col, $exclude)) continue;
			if($leute_col_name[$col] == '') continue;
			$values[] = '_'.$col;
			$descs[] = $leute_col_name[$col];
			$values[] = '_father_'.$col;
			$descs[] = $leute_col_name[$col].' ('.getLL('leute_children_col_father').')';
			$values[] = '_mother_'.$col;
			$descs[] = $leute_col_name[$col].' ('.getLL('leute_children_col_mother').')';
		}
		//Prepare list with selected columns
		foreach(explode(',', $value) as $v) {
			if(in_array($v, $values)) {
				$avalues[] = $v;
				foreach($values as $kk => $vv) {
					if($vv == $v) {
						$adescs[] = $descs[$kk];
						continue;
					}
				}
			}
		}
		$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('leute_settings_children_columns'),
			'type' => 'doubleselect',
			'js_func_add' => 'double_select_add',
			'name' => 'sel_children_columns',
			'values' => $values,
			'descs' => $descs,
			'avalue' => $value,
			'avalues' => $avalues,
			'adescs' => $adescs,
			'params' => 'size="7"',
			'show_moves' => TRUE,
		);


		$frmgroup[$gc]['row'][$rowcounter++]['inputs'][0] = array('type' => '   ');
	}


	//Select filters used for fast filter
	$tpl_values = $tpl_output = $avalues = $adescs = null;
	$value = explode(',', ko_get_userpref($_SESSION['ses_userid'], 'leute_fast_filter'));
	//Prepare list of all filters
	ko_get_filters($f_, 'leute');
	foreach($f_ as $fi => $ff) {
		if(!$ff['allow_fastfilter']) continue;
		$tpl_values[] = $fi;
		$tpl_output[] = $ff['name'];
		//Currently disselected
		if(in_array($fi, $value)) {
			$avalues[] = $fi;
			$adescs[] = $ff['name'];
		}
	}
	$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('leute_settings_fast_filter'),
			'type' => 'doubleselect',
			'js_func_add' => 'double_select_add',
			'name' => 'sel_fast_filter',
			'values' => $tpl_values,
			'descs' => $tpl_output,
			'avalue' => implode(',', $value),
			'avalues' => $avalues,
			'adescs' => $adescs,
			'params' => 'size="7"',
			'show_moves' => TRUE,
			);

	//Select filters to be hidden
	$tpl_values = $tpl_output = $avalues = $adescs = null;
	$value = explode(',', ko_get_userpref($_SESSION['ses_userid'], 'hide_leute_filter'));
	//Prepare list of all filters
	ko_get_filters($f_, 'leute');
	foreach($f_ as $fi => $ff) {
		$tpl_values[] = $fi;
		$tpl_output[] = $ff['name'];
		//Currently disselected
		if(in_array($fi, $value)) {
			$avalues[] = $fi;
			$adescs[] = $ff['name'];
		}
	}
	$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('admin_settings_filter_hide'),
			'type' => 'checkboxes',
			'name' => 'sel_hide_filter',
			'values' => $tpl_values,
			'descs' => $tpl_output,
			'avalue' => implode(',', $value),
			'avalues' => $avalues,
			'size' => '6',
			);

	if (!$LEUTE_NO_FAMILY) {
		$value = ko_get_userpref($_SESSION['ses_userid'], 'leute_fam_checkbox');
		$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('leute_settings_fam_checkbox'),
			'type' => 'switch',
			'name' => 'chk_fam_checkbox',
			'label_0' => getLL('no'),
			'label_1' => getLL('yes'),
			'value' => $value == '' ? 0 : $value,
		);
	}

	$filterset = array_merge((array)ko_get_userpref('-1', '', 'filterset', 'ORDER BY `key` ASC'), (array)ko_get_userpref($_SESSION['ses_userid'], '', 'filterset', 'ORDER BY `key` ASC'));

	$values = $descs = array();
	$values[] = $descs[] = '';
	foreach($filterset as $f) {
		$values[] = $f['id'];
		$global_tag = $f['user_id'] == '-1' ? getLL('leute_filter_global_short') : '';
		$descs[] = $global_tag.' '.$f['key'];
	}
	$frmgroup[$gc]['row'][($LEUTE_NO_FAMILY ? $rowcounter : $rowcounter++)]['inputs'][($LEUTE_NO_FAMILY ? 0 : 1)] = array('desc' => getLL('leute_settings_carddav_filter'),
			'type' => 'select',
			'name' => 'sel_carddav_filter',
			'params' => 'size="0"',
			'values' => $values,
			'descs' => $descs,
			'value' => ko_get_userpref($_SESSION['ses_userid'], 'leute_carddav_filter'),
	);



	if(ko_module_installed('kg')) {
		$value = ko_get_userpref($_SESSION['ses_userid'], 'leute_kg_as_cols');
		$frmgroup[$gc]['row'][$rowcounter++]['inputs'][0] = array('desc' => getLL('leute_settings_kg_as_cols'),
				'type' => 'switch',
				'name' => 'sel_kg_as_cols',
				'label_0' => getLL('no'),
				'label_1' => getLL('yes'),
				'value' => $value == '' ? 0 : $value,
				);
	}



	if(ko_module_installed('groups')) {
		$value = ko_get_userpref($_SESSION['ses_userid'], 'show_passed_groups');
		$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('admin_settings_options_show_passed_groups'),
				'type' => 'switch',
				'name' => 'chk_show_passed_groups',
				'label_0' => getLL('no'),
				'label_1' => getLL('yes'),
				'value' => $value == '' ? 0 : $value,
				);

		$value = ko_get_userpref($_SESSION['ses_userid'], 'group_shows_datafields');
		$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('leute_settings_group_shows_datafields'),
				'type' => 'switch',
				'name' => 'chk_group_shows_datafields',
				'label_0' => getLL('no'),
				'label_1' => getLL('yes'),
				'value' => $value == '' ? 0 : $value,
				);
	}



	//Add global settings
	$admin_all = ko_get_access_all('admin', '', $admin_max);
	if($access['leute']['ALL'] > 2 || $admin_max > 1) {
		$gc++;
		$frmgroup[$gc]['titel'] = getLL('settings_title_global');

		if($access['leute']['ALL'] > 2) {
			//Hidden mode
			$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('admin_settings_options_leute_hidden_mode'),
					'type' => 'select',
					'name' => 'sel_leute_hidden_mode',
					'values' => array(0, 1, 2),
					'descs' => array(getLL('admin_settings_options_leute_hidden_mode_0'), getLL('admin_settings_options_leute_hidden_mode_1'), getLL('admin_settings_options_leute_hidden_mode_2')),
					'value' => ko_get_setting('leute_hidden_mode'),
					);
			//Allow permanent deletion
			$value = ko_get_setting('leute_real_delete');
			$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('leute_settings_real_delete'),
					'type' => 'switch',
					'name' => 'chk_real_delete',
					'label_0' => getLL('no'),
					'label_1' => getLL('yes'),
					'value' => $value == '' ? 0 : $value,
					);

			$value = ko_get_setting('leute_assign_global_notification');
			$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('leute_settings_assign_global_notification'),
					'type' => 'text',
					'params' => 'size="40"',
					'name' => 'txt_assign_global_notification',
					'value' => $value,
					);

			$value = ko_get_setting('leute_no_delete_columns');
			$leute_col_name = ko_get_leute_col_name();
			$cols = db_get_columns('ko_leute');
			$exclude = array('id', 'smallgroups', 'lastchange', 'kg_seit', 'kgleiter_seit', 'famfunction', 'picture', 'deleted', 'hidden', 'crdate', 'cruserid');
			$values = $descs = $avalues = $adescs = array();
			foreach($cols as $_col) {
				$col = $_col['Field'];
				if(in_array($col, $exclude)) continue;
				if($leute_col_name[$col] == '') continue;
				$values[] = $col;
				$descs[] = $leute_col_name[$col];
			}
			//Prepare list with selected columns
			foreach(explode(',', $value) as $v) {
				if(in_array($v, $values)) {
					$avalues[] = $v;
					foreach($values as $kk => $vv) {
						if($vv == $v) {
							$adescs[] = $descs[$kk];
							continue;
						}
					}
				}
			}
			$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('leute_settings_no_delete_columns'),
				'type' => 'doubleselect',
				'js_func_add' => 'double_select_add',
				'name' => 'sel_no_delete_columns',
				'values' => $values,
				'descs' => $descs,
				'avalue' => $value,
				'avalues' => $avalues,
				'adescs' => $adescs,
				'params' => 'size="7"',
			);
		}

		//Links to other settings (in admin module)
		if($admin_max > 1) {
			$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('leute_settings_labels'),
					'type' => 'label',
					'value' => '<a href="'.$ko_path.'admin/index.php?action=set_etiketten">'.getLL('leute_settings_labels_text').'</a>',
					);
			$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('leute_settings_leute_pdf'),
					'type' => 'label',
					'value' => '<a href="'.$ko_path.'admin/index.php?action=set_leute_pdf">'.getLL('leute_settings_leute_pdf_text').'</a>',
					);
		}
	}

	//Allow plugins to add further settings
	hook_form('leute_settings', $frmgroup, '', '');


	//display the form
	$smarty->assign('tpl_titel', getLL('leute_settings_form_title'));
	$smarty->assign('tpl_submit_value', getLL('save'));
	$smarty->assign('tpl_action', 'submit_leute_settings');
	$cancel = ko_get_userpref($_SESSION['ses_userid'], 'default_view_leute');
	if(!$cancel) $cancel = 'show_all';
	$smarty->assign('tpl_cancel', $cancel);
	$smarty->assign('tpl_groups', $frmgroup);
	$smarty->assign('help', ko_get_help('leute', 'leute_settings'));

	$smarty->display('ko_formular.tpl');
}//ko_leute_settings()




function ko_word_get_template() {
	global $BASE_PATH;

	$files = array();
	$files[] = $BASE_PATH.'config/address_'.intval($_SESSION['ses_userid']).'.docx';
	$files[] = $BASE_PATH.'config/address.docx';
	$files[] = $BASE_PATH.'config/address.rtf';

	foreach($files as $file) {
		if(file_exists($file)) return $file;
	}
	return FALSE;
}//ko_word_get_template()




/**
 * Create a word document addressed to the given person
 * @param int/array $pid: ID of an address or whole address as array
 */
function ko_word_address($pid) {
	global $BASE_PATH;

	//Check for present address template
	if(ko_word_get_template() === FALSE) return;

	if(is_array($pid)) {
		$person = $pid;
		$pid = $person['id'];
	} else {
		ko_get_person_by_id($pid, $person);
	}

	//Rectype
	$person = ko_apply_rectype($person);

	$file = ko_word_get_template();
	if(mb_substr($file, -4) == 'docx') return ko_word_docx($file, $person);
	else return ko_word_rtf($person);
}//ko_word_address()




/**
 * Create a rtf document addressed to the given person
 * @param int/array $person: Array of the person
 */
function ko_word_rtf($person) {
	global $BASE_PATH;
	$map = ko_word_person_array($person);

	//Create RTF as string
	$rtf = file_get_contents($BASE_PATH.'config/address.rtf');
	$rtf = str_replace(array_keys($map), $map, $rtf);

	//Output to file
	$filename = format_userinput($person['vorname'].$person['nachname'], 'alphanumlist').'.doc';
	$fp = fopen($BASE_PATH.'download/word/'.$filename, 'w');
	fputs($fp, $rtf);
	fclose($fp);

	return $filename;
}//ko_word_rtf()




/**
 * Create a docx document addressed to the given person
 * @param int/array $person: Array of the person
 */
function ko_word_docx($file, $person) {
	global $BASE_PATH;

	if(!file_exists($file)) return FALSE;

	$map = ko_word_person_array($person);

	\PhpOffice\PhpWord\Settings::loadConfig();

	//Create PHPWord Object
	$phpWord = new \PhpOffice\PhpWord\PhpWord();


	$document = $phpWord->loadTemplate($file);
	foreach($map as $key => $value) {
		$document->setValue($key, $value);
	}

	//Output to file
	$filename = format_userinput($person['vorname'].$person['nachname'], 'alphanumlist').'.docx';
	$document->saveAs($BASE_PATH . 'download/word/' . $filename);

	return $filename;
}//ko_word_docx()




function ko_word_person_array($person) {
	global $DATETIME, $LEUTE_WORD_ADDRESSBLOCK;

	$map = array();

	//Address fields of recipient (${address_...})
	foreach($person as $k => $v) {
		$map['${address_'.mb_strtolower($k).'}'] = $v;
	}

	// Addressblock. expressed in lines because PHPWord can't insert line breaks into templates,
	// LINES START WITH 0!!
	$maxLines = sizeof($LEUTE_WORD_ADDRESSBLOCK);
	$lineCounter = 0;
	foreach ($LEUTE_WORD_ADDRESSBLOCK as $line) {
		$lineString = '';
		$cellCounter = 0;
		foreach ($line as $infoArray) {
			if (trim($person[$infoArray['field']]) != '') {
				$cellContent = trim($person[$infoArray['field']]);
			}
			else if (isset($infoArray['ifEmpty']) && trim($person[$infoArray['ifEmpty']]) != '') {
				$cellContent = trim($person[$infoArray['ifEmpty']]);
			}
			else {
				continue;
			}

			if ($cellCounter == 0) {
				$lineString .= $cellContent;
			}
			else {
				$lineString .= ' ' . $cellContent;
			}

			$cellCounter ++;
		}
		if (trim($lineString != '')) $map['${line' . $lineCounter++ . '}'] = $lineString;
	}
	for ($i = $lineCounter; $i < $maxLines; $i ++) {
		$map['${line' . $i . '}'] = '';
	}

	// Salutations
	$geschlechtMap = array('Herr' => 'm', 'Frau' => 'w');
	$vorname = trim($person['vorname']);
	$nachname = trim($person['nachname']);
	$geschlecht = $person['geschlecht'] != '' ? $person['geschlecht'] : $geschlechtMap[$person['anrede']];
	$map['${address__salutation_formal_name}'] = getLL('mailing_salutation_formal_' . ($nachname != '' ? $geschlecht : '')) . ($nachname == '' ? '' : ' ' . $nachname);
	$map['${address__salutation_name}'] = getLL('mailing_salutation_' . ($vorname != '' ? $geschlecht : '')) . ($vorname == '' ? '' : ' ' . $vorname);

	//Salutation
	$map['${address__salutation}'] = getLL('mailing_salutation_'.$person['geschlecht']);
	$map['${address__salutation_formal}'] = getLL('mailing_salutation_formal_'.$person['geschlecht']);


	//Add current date
	$map['${date}'] = strftime($DATETIME['dMY'], time());
	$map['${date_dmY}'] = strftime($DATETIME['dmY'], time());

	//Add contact fields (from general settings)
	$contact_fields = array('name', 'address', 'zip', 'city', 'phone', 'url', 'email');
	foreach($contact_fields as $field) {
		$map['${contact_'.mb_strtolower($field).'}'] = ko_get_setting('info_'.$field);
	}

	//Add sender fields of current user
	$sender = ko_get_logged_in_person();
	foreach($sender as $k => $v) {
		$map['${user_'.mb_strtolower($k).'}'] = $v;
	}

	return $map;
}//ko_word_person_array()


?>
