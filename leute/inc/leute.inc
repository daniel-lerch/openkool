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


//KG-Modul einfügen, damit es nicht manuell eingefügt werden muss
if(ko_module_installed("kg") && file_exists($ko_path."leute/inc/kg.inc"))
  include($ko_path."leute/inc/kg.inc");


//Define basic chart types for leute module (may be extended by plugins)
$LEUTE_CHART_TYPES = array('statistics', 'roles', 'subgroups', 'age_bar', 'lastchange', 'age_pie', 'birthday_months', 'sex', 'civilstatus', 'famfunction', 'city', 'zip', 'country', 'taufbuch', 'rodel');

$mailmerge_signature_ext = array('gif', 'jpg', 'jpeg', 'png', 'pdf');


function ko_formular_leute($mode, $id=0, $show_save_as_new=true) {
	global $smarty, $ko_path, $KOTA;
	global $LEUTE_LAYOUT, $LEUTE_EXCLUDE, $LEUTE_ENUMPLUS, $LEUTE_TEXTSELECT, $LEUTE_EMAIL_FIELDS, $LEUTE_MOBILE_FIELDS;
	global $FAMILIE_EXCLUDE, $COLS_LEUTE_UND_FAMILIE;
	global $access;
	global $SMALLGROUPS_ROLES, $RECTYPES;
	global $LEUTE_NO_FAMILY, $ASYNC_FORM_TAG;

	if ($mode == "edit") {
		ko_get_person_by_id($id, $person);
		if (!$person["id"] || $person["deleted"] == "1") return false;
	}

	$colNames = ko_get_leute_col_name(FALSE, FALSE, 'all');

	//get the cols, for which this user has edit-rights (saved in allowed_cols[edit])
	$allowed_cols = ko_get_leute_admin_spalten($_SESSION['ses_userid'], 'all', ($id === 0 ? -1 : $id));

	// If this is a form to add persons during check-in, adapt the form accordingly
	if (isset($ASYNC_FORM_TAG) && $ASYNC_FORM_TAG == 'checkin_add_person') {
		require_once("{$ko_path}checkin/inc/checkin.inc");
		ko_checkin_set_leute_form($_SESSION['checkin_tracking_id']);
	}

	// Handle family data
	koFormLayoutEditor::unsetField($KOTA, 'ko_leute', 'famfunction');
	$showFamilyData = TRUE;
	if ($LEUTE_NO_FAMILY || (is_array($allowed_cols["view"]) && !in_array("famid", $allowed_cols["view"]))) {
		$showFamilyData = FALSE;
		koFormLayoutEditor::unsetField($KOTA, 'ko_leute', 'family_data');
	}

	// unset father and mother (the form definitions are for multi-edit
	unset($KOTA['ko_leute']['father']['form'], $KOTA['ko_leute']['mother']['form']);

	$smarty->assign("hide_fam", !$showFamilyData);



	foreach ($KOTA['ko_leute'] as $l => $data) {
		if (substr($l, 0, 1) == '_' || in_array($l, array('famid'))) continue;

		if (preg_match('/MODULEgrp\d{6}/', $l)) {
			unset($KOTA['ko_leute'][$l]);
			continue;
		}

		if((is_array($allowed_cols["view"]) && !in_array($l, $allowed_cols["view"])) && (!is_array($allowed_cols["edit"]) || is_array($allowed_cols["edit"]) && !in_array($l, $allowed_cols["edit"]))) {
			if ($l == 'groups') {
				if (!isset($access['groups'])) ko_get_access('groups');
				if ($access['groups']['MAX'] < 1) {
					koFormLayoutEditor::unsetField($KOTA, 'ko_leute', $l);
					continue;
				}
			} else if (substr($l, 0, 6) != 'MODULE' && array_key_exists($l, $colNames)) {
				koFormLayoutEditor::unsetField($KOTA, 'ko_leute', $l);
				continue;
			}
		} else if(is_array($allowed_cols["edit"]) && !in_array($l, $allowed_cols["edit"])) {
			if ($l == 'groups') {
				if (!isset($access['groups'])) ko_get_access('groups');
				if ($access['groups']['MAX'] < 2) {
					$KOTA['ko_leute'][$l]['form']["readonly"] = TRUE;
				}
			} else if (substr($l, 0, 6) != 'MODULE' && array_key_exists($l, $colNames)) {
				$KOTA['ko_leute'][$l]['form']["readonly"] = TRUE;
			}
		}

		//Add checkboxes for email and mobile fields, if multiple are used and field may be edited
		if((in_array($l, $LEUTE_EMAIL_FIELDS) && sizeof($LEUTE_EMAIL_FIELDS) > 1) || (in_array($l, $LEUTE_MOBILE_FIELDS) && sizeof($LEUTE_MOBILE_FIELDS) > 1)) {
			$KOTA['ko_leute'][$l]['form']['type'] = 'html';
			$KOTA['ko_leute'][$l]['pre'] = 'FCN:kota_pre_leute_preferred_field';
		}
	}
	koFormLayoutEditor::collapse($KOTA, 'ko_leute');

	//Add name to title
	$titleAdd = '';
	if($mode == 'edit') {
		if($person['vorname'] || $person['nachname']) {
			$titleAdd = $person['vorname'].' '.$person['nachname'];
			if($person['firm']) $titleAdd .= ' ('.$person['firm'].')';
		} else {
			if($person['firm']) $titleAdd = $person['firm'];
		}
	}

	$form_data["title"] = getLL("form_leute_title").($titleAdd ? ': '.$titleAdd : '');
	$form_data["action"] = $mode == "neu" ? "submit_neue_person" : "submit_edit_person";
	$form_data['label_as_new'] = ($mode == 'edit' && $access['leute']['MAX'] > 1 && $show_save_as_new) ? getLL("form_leute_as_new_person") : '';
	$form_data['action_as_new'] = ($mode == 'edit' && $access['leute']['MAX'] > 1 && $show_save_as_new) ? 'submit_als_neue_person' : '';
	$form_data["cancel"] = $_SESSION['show_back'] ? $_SESSION['show_back'] : 'show_all';

	ko_multiedit_formular('ko_leute', '', $id, '', $form_data, FALSE, '', !$LEUTE_NO_FAMILY);

	// Add warning in when editing family fields
	print '<script>
$(".family-field-header").children("label").append("&nbsp;&nbsp;<span class=\\"family_field_warning\\" style=\\"color:orangered;visibility:hidden\\">'.getLL('leute_warning_family_fields').'</span>");
</script>';

	print "<script>

		$('h3').before(
		 '<div class=\"input-group\" style=\"float:right; width: 100px;; margin-top:-5px;\">' +
			'<span title=\"".getLL('form_addressblock_import_title')."\" class=\"input-group-addon\">". getLL('form_addressblock_import') ." <i class=\"glyphicon glyphicon-paste\"></i></span>' +
			'<textarea title=\"".getLL('form_addressblock_import_title')."\" style=\"resize: none; overflow:hidden; height: 30px; width: 100px;\" class=\"form-control\" rows=\"1\" id=\"address_import\"></textarea>' +
		 '</div>');

		$('textarea#address_import').bind('input propertychange', function(e) {
			var input = $(this).val();
			var lines = input.split(\"\\n\");
			var address = Array();

			// extract phone and mail
			for(var i=lines.length-1; i>=0; i--) {
				lines[i] = lines[i].replace('(at)', '@');
				regex_mail = /[A-Za-z0-9\._-]+[@][A-Za-z0-9\._-]+[\.].[A-Za-z0-9]+/img
				var mails = lines[i].match(regex_mail);
				if (mails !== null) {
					address['email'] = mails[0];
					lines.splice(i,1);
					continue;
				}
				regex_phone = /((\+\s?\d{2}|\(?00\s?\d{2}\)?|\d{3})\s?[\d ]{8,12})/img
				var phone = lines[i].match(regex_phone);
				if (phone !== null) {
					address['telg'] = phone[0];
					lines.splice(i,1);
				}
			}

			for(var i=0; i<lines.length; i++) {
				var line = lines[i];

				var regex_plzort = /(?:ch-)?(\d{4}) +(.*)/mgi;
				var match_plzort = regex_plzort.exec(line);

				var regex_adresse = /(route|str|rue|weg|allee|gasse|weid)/i;
				var match_adresse = regex_adresse.exec(line);

				var regex_adresse2 = /^[im |auf |an ].*[0-9aZ]$/i;
				var match_adresse2 = regex_adresse2.exec(line);

				var regex_firm = /(Ref.|Kirche|Kirchgemeinde|Kath.|Pfarrei|AG|GmbH)/i;
        var match_firm = regex_firm.exec(line);

				if (match_plzort !== null) {
					address['plz'] = match_plzort[1];
					address['ort'] = match_plzort[2];
				} else if (address['plz']) {
					// pass
				} else if (line.toLowerCase().substring(0, 8) === 'postfach') {
				 	address['postfach'] = line;
				} else if (match_adresse !== null) {
				 	address['adresse'] = line;
				} else if (match_adresse2 !== null) {
					address['adresse'] = line;
				} else if (match_firm !== null) {
					address['firm'] = line;
				} else if (i === 0) {
					address['vorname'] = line.split(' ').slice(0, -1).join(' ');
					address['nachname'] = line.split(' ').slice(-1).join(' ');
				} else {
					if (address['adresse'] === null) {
						address['adresse'] = line;
					} else {
						address['adresse_zusatz'] = line;
					}
				}
			}

			for (var k in address){
				if (address.hasOwnProperty(k)) {
					$('input[name=\"koi[ko_leute]['+k+'][".$id."]\"]').val(address[k]);
					$('input[name=\"koi[ko_leute]['+k+'][".$id."]\"]').addClass('address_import_modified');
				}
			}

		    $(this).val('').blur();
		});
	</script>";
}

function ko_formular_leute_mailing() {
	global $smarty, $COLS_LEUTE_UND_FAMILIE, $ko_path, $LEUTE_EMAIL_FIELDS, $BASE_PATH, $BASE_URL, $MODULES;

	$leuteColNames = ko_get_leute_col_name(TRUE, TRUE);

	// header for xls file
	$cols = array("anrede", "vorname", "nachname", "adresse", "adresse_zusatz", "plz", "ort", "telp", "telg");
	$header = array();
	foreach($cols as $c) {
		$header[] = $leuteColNames[$c];
	}

	// fetch recipients
	$xls_data = $array_empfaenger = array();
	$ohne_email = "";
	$row = 0;
	$with_email_ids = array();
	foreach($_SESSION['leute_mailing_people_data'] as $l => $p) {  //Loop über alle Leute
		if(ko_get_leute_email($p, $email)) {
			$with_email_ids[] = $p['id'];
			$array_empfaenger = array_merge($array_empfaenger, $email);
		} else {
			$ohne_email .= $p["vorname"]." ".$p["nachname"].($p["ort"] ? (" ".getLL('from')." ".$p["ort"]) : "").", ";

			$col = 0;
			foreach($cols as $c) {
				$xls_data[$row][$col++] = sql2datum($p[$c]);
			}
			$row++;
		}
	}//foreach(es)

	$array_empfaenger = array_unique($array_empfaenger);
	$txt_empfaenger = implode(",", $array_empfaenger);
	$txt_empfaenger_semicolon = implode(';', $array_empfaenger);

	$ohne_email = substr($ohne_email, 0, -2);
	// create xls file showing all people without email address
	if($ohne_email != "") {
		$dateiname = $ko_path."download/excel/".getLL("export_filename").strftime("%d%m%Y_%H%M%S", time()).".xlsx";
		$dateiname = ko_export_to_xlsx($header, $xls_data, $dateiname, "kOOL");
		$smarty->assign("xls_filename", $dateiname);
	}

	$smarty->assign("txt_empfaenger", ko_html($txt_empfaenger));
	$smarty->assign('txt_empfaenger_semicolon', ko_html($txt_empfaenger_semicolon));
	$smarty->assign("tpl_ohne_email", ($ohne_email == "" ? getLL('form_leute_none') : $ohne_email));
	$smarty->assign('crm_contact_tpl_groups', ko_get_crm_contact_form_group(array(), array('type' => 'email', 'leute_ids' => implode(',', array_unique($with_email_ids)))));


	// prepare content panel of email
	$familyCols = $COLS_LEUTE_UND_FAMILIE;

	$mode = in_array($_SESSION['leute_mailing_sel_auswahl'], array('markierte', 'allep')) ? 'person' : 'family';

	$cols = $mode == 'family' ? array_merge(array('vorname', 'nachname', 'email', 'MODULEsalutation_informal', 'MODULEsalutation_formal'), $familyCols) : $_SESSION['show_leute_cols'];

	if (in_array('telegram', $MODULES)) {
		$cols[] = "telegramlink";
		$leuteColNames["telegramlink"] = getLL('mailing_placeholder_text_telegramlink');
	}

	$placeholders = array('' => '');
	foreach ($cols as $col) {
		$placeholders[] = array('value' => '###'.strtoupper($col).'###', 'desc' => str_replace('&nbsp;', '', trim($leuteColNames[$col])));
	}

	// get reply to addresses
	$replyToAddresses = array();
	$p = ko_get_logged_in_person();
	$emails = ko_get_leute_emails($p['id']);
	foreach($emails as $email) {
		$replyToAddresses[$email] = array('value' => $email, 'desc' => $email);
	}

	// fill values from post (previous submission)
	$initFiles = array();
	if (isset($_POST['leute_mailing_files'])) {
		foreach (explode('@|,|@', $_POST['leute_mailing_files']) as $uuid) {
			if (!$uuid) continue;
			$name = substr($uuid, 37);
			$size = filesize($ko_path . 'my_images/temp/' . $uuid);
			$thumbnailFile = $uuid . '.thumbnail';
			$f = array('name' => $name, 'size' => $size, 'uuid' => $uuid);
			if ($thumbnailFile) $f['thumbnailUrl'] = $BASE_URL . 'my_images/temp/' . $thumbnailFile;
			$initFiles[] = $f;
		}
	}
	$text = isset($_POST['leute_mailing_text']) ? $_POST['leute_mailing_text'] : '';
	$subject = isset($_POST['leute_mailing_subject']) ? $_POST['leute_mailing_subject'] : '';
	$replyTo = isset($_POST['leute_mailing_reply_to']) ? $_POST['leute_mailing_reply_to'] : '';
	$files = isset($_POST['leute_mailing_files']) ? $_POST['leute_mailing_files'] : '';

	array_walk_recursive($files, 'utf8_encode_array');

	// show previously sent emails
	$ups = db_select_data('ko_userprefs', "WHERE `user_id` = {$_SESSION['ses_userid']} AND `type` = 'leute_saved_email' ORDER BY `id` DESC");
	$sentEmails = array();
	$sentEmailsJSON = array();
	$cnt = 0;
	require_once($BASE_PATH . 'inc/class.html2text.php');
	$html2text = new html2text('<body></body>');
	foreach ($ups as $up) {
		$sentEmail = json_decode($up['value'], true);
		array_walk_recursive($sentEmail, 'utf8_decode_array');

		$html2text->set_html($sentEmail['text']);
		$plainText = $html2text->get_text();

		$key = $sentEmail['date'] . zerofill($sentEmail['subject'], 100) . (++$cnt);
		$sentEmails[$key] = array('value' => $cnt, 'desc' => date('d.m.Y H:i', strtotime($sentEmail['date'])).': ' . $sentEmail['subject'], 'title' => $plainText);
		$sentEmailsJSON[$cnt] = array('subject' => $sentEmail['subject'], 'text' => $sentEmail['text']);
	}

	krsort($sentEmails);

	//Add empty entry at the beginning
	array_unshift($sentEmails, array('value' => '', 'desc' => '', 'title' => ''));

	array_merge($sentEmails);
	array_walk_recursive($sentEmailsJSON, 'utf8_encode_array');


	$fineUploaderLabels = array('confirmMessage', 'deletingFailedText', 'deletingStatusText', 'tooManyFilesError', 'unsupportedBrowser', 'autoRetryNote', 'namePromptMessage', 'failureText', 'failUpload', 'formatProgress', 'paused', 'waitingForResponse');
	$smarty->assign('tpl_text', $text);
	$smarty->assign('tpl_fineuploader_labels', $fineUploaderLabels);
	$smarty->assign('tpl_subject', $subject);
	$smarty->assign('tpl_reply_to', $replyTo);
	$smarty->assign('tpl_placeholders', $placeholders);
	$smarty->assign('tpl_init_files', json_encode($initFiles));
	$smarty->assign('tpl_files', $files);
	$smarty->assign('tpl_reply_to_addresses', $replyToAddresses);
	$smarty->assign('tpl_sent_emails', $sentEmails);
	$smarty->assign('tpl_sent_emails_json', json_encode($sentEmailsJSON));

	$smarty->display("ko_formular_email2.tpl");
}




/**
 * Stellt die Leute-Liste dar
 * mode definiert die Ausgabe: liste, my_list, adressliste
 * output: TRUE=Ausgabe erfolgt direkt, FALSE=HTML wird zurückgegeben (Ajax)
 */
function ko_list_personen($mode="liste") {
	global $smarty, $ko_path;
	global $LEUTE_EXCLUDE, $LEUTE_ADRESSLISTE_LAYOUT, $LEUTE_EMAIL_FIELDS, $LEUTE_MOBILE_FIELDS;
	global $KOTA, $ko_menu_akt;
	global $all_groups;
	global $access, $LEUTE_NO_FAMILY;

	if(!is_array($access['leute'])) ko_get_access('leute');
	if($access['leute']['MAX'] < 0) return;

	if ($mode == 'liste' && $access['leute']['MAX'] > 1) $smarty->assign('tpl_list_link_new', '/leute/index.php?action=neue_person');

	if(!$all_groups) ko_get_groups($all_groups);
	$all_datafields = db_select_data("ko_groups_datafields", "WHERE 1=1", "*");

	$leute_col_name = ko_get_leute_col_name($groups_hierarchie=false, $add_group_datafields=true);

	ko_get_access("taxonomy");
	if($access['taxonomy']['ALL'] >= 1) {
		$KOTA['ko_leute']['listview'][] = [
			"name" => "terms",
			"sort" => FALSE,
			"multiedit" => FALSE
		];
	}
	ko_get_access("daten");
	if($access['daten']['ABSENCE'] >= 1) {
		$KOTA['ko_leute']['listview'][] = [
			"name" => "absence",
			"sort" => FALSE,
			"multiedit" => FALSE
		];
	}

	if($mode == "my_list") {  //Daten aus _SESSION[my_list] holen
		//Eigenes z_where gemäss den gespeicherten IDs aufbauen
		foreach($_SESSION['my_list'] as $k => $v) if(!$v) unset($_SESSION['my_list'][$k]);
		if(sizeof($_SESSION['my_list']) > 0) {
			$z_where = "AND `id` IN (".implode(',', $_SESSION['my_list']).") AND `deleted` = '0'".ko_get_leute_hidden_sql();
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
			$dates[substr($d, 5)] = $inc;
			list($month, $day) = explode('-', substr($d, 5));
			$z_where .= " OR (MONTH(`geburtsdatum`) = '$month' AND DAY(`geburtsdatum`) = '$day') ";
		}
		$where = " AND deleted = '0' ".ko_get_leute_hidden_sql()." AND `geburtsdatum` != '0000-00-00' ";
		$where .= " AND (".substr($z_where, 3).") ".ko_get_birthday_filter();

		$rows = db_get_count('ko_leute', 'id', $where);
		$_es = db_select_data('ko_leute', 'WHERE 1=1 '.$where, '*');

		$sort = array();
		foreach($_es as $pid => $p) {
			$sort[$pid] = $dates[substr($p['geburtsdatum'], 5)];
		}
		asort($sort);

		$es = array();
		$row = 0;
		foreach($sort as $pid => $deadline) {
			$p = $_es[$pid];

			$p['deadline'] = $deadline;
			$p['alter'] = (int)substr(add2date(date('Y-m-d'), 'day', $deadline, TRUE), 0, 4) - (int)substr($p['geburtsdatum'], 0, 4);

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
	else {
		$smarty->assign("tpl_list_title", getLL("leute_list_header"));
	}

	$show_control_cols = array('check' => 'check', 'edit' => 'edit');
	$show_overlay_cols = array('delete' => 'delete', 'qrcode' => 'qrcode');


	//hide/show person
	if ($access['leute']['MAX'] > 2) {
		$show_overlay_cols['togglehidden'] = 'togglehidden';
	}

	// donations
	if (ko_module_installed('donations')) {
		ko_get_access('donations');
		if ($access['donations']['MAX'] > 0) $show_overlay_cols['donations'] = 'donations';
	}

	//version history of every record
	if($access['leute']['MAX'] > 1) {
		$show_overlay_cols['version'] = 'version';
		//$smarty->assign("tpl_show_version_col", true);
	}

	//crm entries of every record
	if(ko_module_installed('crm')) {
		ko_get_access('crm');
		if ($access['crm']['MAX'] > 1 && !$leute_show_deleted) {
			$show_overlay_cols['crm'] = 'crm';
		}
	}

	if(!is_array($all_groups)) ko_get_groups($all_groups);

	//Statistik über Suchergebnisse und Anzeige
	if($mode == 'birthdays') {
		$smarty->assign('hide_listlimiticons', TRUE);
		$stats_end = $rows;
	} else {
		$stats_end = ($_SESSION["show_limit"]+$_SESSION["show_start"]-1 > $rows) ? $rows : ($_SESSION["show_limit"]+$_SESSION["show_start"]-1);
	}
	$smarty->assign('tpl_stats', $_SESSION["show_start"]." - ".$stats_end." ".getLL("list_oftotal")." ".number_format($rows, 0, '.', "'"));


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
		$show_overlay_cols['family'] = 'family';
		//$smarty->assign('tpl_show_3cols', true);
		//$smarty->assign('tpl_show_4cols_leute', true);
	} else {
		//$smarty->assign('tpl_show_3cols', true);
	}

	//find number of columns for colspan for version td (as IE can not edit innerHTML of tr elements
	$table_cols = 1;  //checkbox, edit, del, history button
	if(ko_get_userpref($_SESSION['ses_userid'], 'leute_fam_checkbox') == 1 && in_array($mode, array('liste', 'my_list'))) $table_cols++;  //fam checkbox


	if(in_array($mode, array('liste', 'my_list', 'birthdays'))) {
		$smarty->assign("checkbox_code", "select_export_marked();");
		$smarty->assign("checkbox_all_code", "select_export_marked();");
		$h_counter = -1;
		foreach($_SESSION["show_leute_cols"] as $c) {
			if($c != "" && isset($leute_col_name[$c])) {
				$table_cols++;

				$h_counter++;
				if(substr($c, 0, 9) == "MODULEgrp") {
					$tpl_table_header[$h_counter]['sort'] = $mode != 'birthdays' ? $c : '';
					$tpl_table_header[$h_counter]['name'] = $leute_col_name[$c];
					$tpl_table_header[$h_counter]['id'] = 'col_'.$c;
					$tpl_table_header[$h_counter]["db_name"] = "ko_leute:".$c;
					if(false !== strpos($c, ':')) {
						$tpl_table_header[$h_counter]['class'] = 'ko_list ko_list_datafields';
						$tpl_table_header[$h_counter]['title'] = getLL('leute_listheader_df_group').': '.$leute_col_name[substr($c, 0, 15)];
					}
				} elseif(substr($c, 0, 12) == "MODULEparent") {
					$tpl_table_header[$h_counter]["name"] = $leute_col_name[$c];
					$tpl_table_header[$h_counter]['id'] = 'col_'.$c;
					$tpl_table_header[$h_counter]["db_name"] = "ko_leute:".$c;
				} else {
					if($c != "groups" && $mode != 'birthdays') {
						$sort_col = $c;
						if($c == 'geburtsdatum')
							$sort_col = ko_get_userpref($_SESSION['ses_userid'], 'leute_sort_birthdays') == 'year' ? $c : 'MODULE'.$c;
						$tpl_table_header[$h_counter]["sort"] = $sort_col;
					}
					$tpl_table_header[$h_counter]["name"] = $leute_col_name[$c];
					$tpl_table_header[$h_counter]['id'] = 'col_'.$c;
					$tpl_table_header[$h_counter]["db_name"] = "ko_leute:".$c;
				}
			}//if(c)
		}

		//Multisorting (show for list)
		$multisort["select_values"][] = "";
		$multisort["select_descs"][] = "";
		foreach($leute_col_name as $i => $col) {
			if(substr($i, 0, 6) == "MODULE") continue;  //Only add "normal" columns without groups
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
			if(substr($col['sort'], 0, 6) != 'MODULE' || $col['sort'] == 'MODULEgeburtsdatum') continue;
			$multisort["select_values"][] = $col["sort"];
			$multisort["select_descs"][] = $col["name"];
		}
		$multisort["show"] = true;
		$multisort["showLink"] = getLL("list_multisort_showLink");
		$multisort["open"] = (sizeof($_SESSION["sort_leute"]) > 1);
		foreach($_SESSION["sort_leute"] as $i => $col) {
			$multisort["select_selected"][$i] = $col;
			$multisort["columns"][$i] = $i;
			$multisort["order"][$i] = strtoupper($_SESSION["sort_leute_order"][$i]);
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
				if(isset($KOTA['ko_leute'][$col]) && !in_array($col, array('spouse','terms','groups'))) {
					if(!is_array($allowed_cols['edit'])
						|| (is_array($allowed_cols['edit']) && in_array($col, $allowed_cols['edit']))
						|| substr($col, 0, 6) == 'MODULE'

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
	$show_overlay_cols['maps'] = 'maps';
	$smarty->assign('label_google_maps', getLL('leute_label_google_maps'));

	$smarty->assign('label_whatsappclicktochat', getLL('leute_label_whatsappclicktochat'));
	$show_overlay_cols['whatsappclicktochat'] = 'whatsappclicktochat';

	$smarty->assign('label_template', getLL('leute_list_template'));
	$show_overlay_cols['template'] = 'template';

	$show_overlay_cols['clipboard'] = 'clipboard';
	//Button for Hide/Show
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
		$tpl_list_data[$e_i]["rowclass"] = $e["hidden"] ? "row-inactive" : "";

		if($access['taxonomy']['ALL'] >= 1) $e['terms'] = "";

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
					if($e[$ndc] != '' && $e[$ndc] != '0000-00-00') $ok = FALSE;
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

		if (ko_module_installed('donations') && $access['donations']['MAX'] > 0) {
			$tpl_list_data[$e_i]["show_donations_button"] = true;
			$tpl_list_data[$e_i]["alt_donations"] = getLL("leute_labels_donations");
			$tpl_list_data[$e_i]["onclick_donations"] = "javascript:window.location.href='../donations/index.php?action=set_person_filter&id=".$e['id']."&set_show=1';return false;";
			$tpl_list_data[$e_i]["hidden"] = $e['hidden'];
		}

		//hide/show entry button
		if ($access['leute']['ALL'] > 2 || $access['leute'][$e['id']] > 2) {
			$tpl_list_data[$e_i]["show_togglehidden_button"] = true;
			$tpl_list_data[$e_i]["alt_togglehidden"] = ($e['hidden'] ? getLL("leute_labels_toggle_hidden_show") : getLL("leute_labels_toggle_hidden_hide"));
			$tpl_list_data[$e_i]["onclick_togglehidden"] = "sendReq('../leute/inc/ajax.php', 'action,id,sesid', '".($e['hidden'] ? 'unhideperson' : 'hideperson').",".$e["id"].",".session_id()."', do_element);return false;";
			$tpl_list_data[$e_i]["hidden"] = $e['hidden'];
		}

		//version history
		if($access['leute']['ALL'] > 1 || $access['leute'][$e['id']] > 1) {
			$tpl_list_data[$e_i]["alt_version"] = getLL("leute_labels_version_history");
			$tpl_list_data[$e_i]["onclick_version"] = "tr=document.getElementById('version_tr_".$e["id"]."'); if(tr.style.display == 'none') {sendReq('../leute/inc/ajax.php', 'action,id,sesid', 'history,".$e["id"].",".session_id()."', do_element); } change_vis_tr('version_tr_".$e["id"]."');return false;";
		}

		//crm entry
		if($access['crm']['MAX'] > 0) {
			$tpl_list_data[$e_i]['show_crm_button'] = true;
			$tpl_list_data[$e_i]["alt_crm"] = getLL("leute_labels_crm");
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
			$maps_link = 'http://maps.google.com/maps?f=q&hl='.$_SESSION['lang'].'&q='.substr($maps_link, 1);
		}
		$tpl_list_data[$e_i]['maps_link'] = $maps_link;

		//Mobile number for WhatsApp chat
		ko_get_leute_mobile($e, $mobile);
		if($mobile[0]) {
			$mnumber = $mobile[0];
			check_natel($mnumber);
			if($mnumber) $mnumber = '+'.$mnumber;
		} else {
			$mnumber = '';
		}
		if($mnumber) $tpl_list_data[$e_i]['mobilenumber'] = $mnumber;

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
						$anniversaryFilter = db_select_data('ko_filter', "WHERE `name` = 'groupsanniversary'", 'id', '', '', TRUE);
						$anniversaryFilterId = $anniversaryFilter['id'];

						$assignmentFilter = db_select_data('ko_filter', "WHERE `name` = 'groupshistory'", 'id', '', '', TRUE);
						$assignmentFilterId = $assignmentFilter['id'];
						$filterGroupId = NULL;
						$filterGroupIds = array();
						foreach($_SESSION['filter'] as $f_i => $f) {
							if (!is_numeric($f_i)) continue;
							if ($f[0] == $assignmentFilterId || $f[0] == $anniversaryFilterId) {
								$groupId = $f[1][1];
								if ($groupId) {
									if (strpos($groupId, ':') !== FALSE) {
										list($groupId, $roleId) = explode(':', $groupId);
									}
									$filterGroupId = intval(substr($groupId, 1));
									$filterGroupIds[] = $filterGroupId;
									//Get all subgroups as well
									$childrenGroups = ko_groups_get_recursive('', FALSE, $filterGroupId);
									foreach($childrenGroups as $cg) {
										if($cg['id'] && is_numeric($cg['id'])) $filterGroupIds[] = $cg['id'];
									}
								}
							}
						}

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
								$historyHtml = '';
								if (in_array(intval($gid), $filterGroupIds)) {
									$history = db_select_data('ko_groups_assignment_history', "WHERE `person_id` = {$e['id']} AND `group_id` = {$gid}", '*', 'ORDER BY `stop` ASC');
									$historyHtml = array_map(function($el) {
										$role = '';
										if ($el['role_id']) {
											$role = db_select_data('ko_grouproles', "WHERE `id` = {$el['role_id']}", '*', '', '', TRUE);
											$role = $role['name'];
										}

										$timespan = ko_date_format_timespan($el['start'], $el['stop'] == '0000-00-00 00:00:00' ? 'today' : $el['stop']);

										return '<span class="text-danger">['.$timespan . ($role?' ('.$role.')':'').']</span>';
									}, $history);

									$historyHtml = implode(", ", $historyHtml);
								}
								$value[$counter] = '<a class="'.$class.'" href="#" onclick="'."sendReq('../leute/inc/ajax.php', 'action,id,state,sesid', 'itemlist,MODULEgrp".$gid.",switch,".session_id()."', do_element);return false;".'">'.ko_html($group_desc)."</a>" . ($historyHtml?' '.$historyHtml:'');
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
						$value = implode(',<hr style="margin:2px 0px;">', $v);
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
					else if($c == "terms") {
						kota_listview_ko_leute_terms($value, $e);
					}
					else if($c == "absence") {
						kota_listview_ko_leute_absence($value, $e);
					}
					//all other columns are handled in map_leute_daten()
					else {
						$value = map_leute_daten($e[$c], $c, $e, $all_datafields);
						if(substr($c, 0, 9) != "MODULEgrp"
							&& substr($c, 0, 14) != 'MODULEtracking'
							&& !in_array($c, array('MODULEkgpicture', 'MODULEkgmailing_alias', 'picture'))
							&& substr($c, 0, 11) != 'MODULEfamid'
							&& substr($c, 0, 9) != 'MODULEcrm'
							&& substr($c, 0, 12) != 'MODULEfamily'
							&& substr($c, 0, 12) != 'MODULEparent'
							&& substr($c, 0, 12) != 'MODULEplugin'
							&& substr($c, 0, 18) != 'MODULEsubscription'
							&& !$KOTA['ko_leute'][$c]['allow_html']
						) $value = ko_html($value);
					}

					if(is_array($value)) {  //group with datafields, so more than one column has to be added
						foreach($value as $dfid => $v) {
							$tpl_list_cols[$colcounter] = $colcounter;
							$tpl_list_data[$e_i][$colcounter++] = $v;
							if($dfid > 0) {  //Later columns contain group datafields
								$db_cols[] = 'MODULEgdf'.substr($c, 9).$dfid;
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
					if(substr($cc, 0, 1) == "@") {  //Kommentar (beginnend mit @) direkt ausgeben
						$tpl_list_data[$e_i]["daten"][$rowcounter] .= "<i>".ko_html(substr($cc, 1))."</i> ";
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
			//Merge duplicates
			$dup_filters = 0;
			$candidateadults_filters = 0;
			$filters = db_select_data('ko_filter', "WHERE `typ` = 'leute'", '*');
			foreach($_SESSION['filter'] as $k => $v) {
				if(!is_integer($k)) continue;
				if($filters[$v[0]]['name'] == 'duplicates') $dup_filters++;
				if($filters[$v[0]]['name'] == 'candidateadults') $candidateadults_filters++;
			}

			//Button to remove children from their households (candidateadultfilter)
			if ($candidateadults_filters == 1 && $access['leute']['MAX'] > 1) {
				$button_code = '<button class="btn btn-sm btn-default" type="submit" onclick="c=confirm(\''.getLL('leute_list_footer_decouple_from_household_confirm').'\'); if (!c) return false; set_action(\'decouple_from_household\', this);" value="'.getLL('leute_list_footer_decouple_from_household_button').'">'.getLL('leute_list_footer_decouple_from_household_button').'</button>';
				$list_footer[] = array('label' => getLL('leute_list_footer_decouple_from_household'),
					'button' => $button_code);
				$smarty->assign('show_list_footer', true);
			}

			//Only show button, if 1 dup filter is applied (but not if more than 1 is applied)
			if($dup_filters == 1 && $access['leute']['MAX'] > 2) {
				$button_code = '<button class="btn btn-sm btn-default" type="submit" onclick="c=confirm(\''.getLL('leute_list_footer_merge_duplicates_confirm').'\'); if(!c) return false; set_action(\'merge_duplicates\', this);" value="'.getLL('leute_list_footer_merge_duplicates_button').'">'.getLL('leute_list_footer_merge_duplicates_button').'</button>';
				$help = ko_get_help('leute', 'merge_duplicates');
				if($help['show']) $help_link = '&nbsp;'.$help['link'];
				else $help_link = '';
				$list_footer[] = array('label' => getLL('leute_list_footer_merge_duplicates').$help_link,
					'button' => $button_code);
				$smarty->assign('show_list_footer', true);
			} else if ($dup_filters != 1 && $access['leute']['MAX'] > 2) {
				$button_code = '<button class="btn btn-sm btn-default disabled" type="submit" id="merge_duplicates_no_filter_button" onclick="c=confirm(\''.getLL('leute_list_footer_merge_duplicates_confirm').'\'); if(!c) return false; set_action(\'merge_duplicates_no_filter\', this);" value="'.getLL('leute_list_footer_merge_duplicates_no_filter_button').'">'.getLL('leute_list_footer_merge_duplicates_no_filter_button').'</button>';
				$button_code .= "<script>
$('body').on('click', '.list-check input', function() {
	var nChecked = $('.list-check input:checked').length;
	if (nChecked == 2) $('#merge_duplicates_no_filter_button').removeClass('disabled');
	else $('#merge_duplicates_no_filter_button').addClass('disabled');
});
</script>";
				$help = ko_get_help('leute', 'merge_duplicates_no_filter');
				if($help['show']) $help_link = '&nbsp;'.$help['link'];
				else $help_link = '';
				$list_footer[] = array('label' => getLL('leute_list_footer_merge_duplicates_no_filter').$help_link,
					'button' => $button_code);
				$smarty->assign('show_list_footer', true);
			}


			// show button to delete persons
			if($access['leute']['MAX'] >= 3 AND ko_get_setting('leute_multiple_delete')) {
				$button_code = '<button class="btn btn-sm btn-default" type="submit" onclick="c=confirm(\'' . getLL('leute_list_footer_delete_confirm') . '\'); if(!c) {return false;} else {set_action(\'delete_persons\', this); return true; }" value="' . getLL("leute_list_footer_delete_button") . '">' . getLL("leute_list_footer_delete_button") . '</button>';
				$list_footer[] = array("label" => getLL("leute_list_footer_delete"),
					"button" => $button_code);
				$smarty->assign("show_list_footer", true);
			}

			$smarty->assign('list_footer', $list_footer);
		} else if ($rows > 0 && $leute_show_deleted) {
			// show button to delete persons
			if($access['leute']['MAX'] >= 3 && ko_get_setting('leute_multiple_delete') && ko_get_setting("leute_real_delete")) {
				$button_code = '<button class="btn btn-sm btn-default" type="submit" onclick="c=confirm(\'' . getLL('leute_list_footer_delete_confirm') . '\'); if(!c) {return false;} else {set_action(\'delete_persons\', this); return true; }" value="' . getLL("leute_list_footer_delete_button") . '">' . getLL("leute_list_footer_delete_button") . '</button>';
				$list_footer[] = array("label" => getLL("leute_list_footer_delete"),
					"button" => $button_code);
				$smarty->assign("show_list_footer", true);
			}

			$smarty->assign('list_footer', $list_footer);
		}//if(rows > 0)


		// Help for multisorting
		$smarty->assign("help", ko_get_help("leute", $_SESSION['show']));

		//$smarty->assign('overlay', !$leute_show_deleted);
		if (!$leute_show_deleted) {
			$not_overlay_cols = ko_get_userpref($_SESSION['ses_userid'], 'leute_list_persons_not_overlay');
			if ($not_overlay_cols) {
				$not_overlay_cols = explode(',', $not_overlay_cols);
				foreach ($not_overlay_cols as $not_overlay_col) {
					if (!$not_overlay_col) continue;
					if (array_key_exists($not_overlay_col, $show_overlay_cols)) {
						$show_control_cols[$not_overlay_col] = $not_overlay_col;
						unset($show_overlay_cols[$not_overlay_col]);
					}
				}
			}
		} else {
			$show_control_cols  = [];
			if(ko_get_setting("leute_multiple_delete") == 1 && ko_get_setting("leute_real_delete") == 1) {
				$show_control_cols['check'] = "check";
			}

			$show_control_cols['undelete'] = 'undelete';

			if (ko_get_setting('leute_real_delete') == 1) {
				$show_control_cols['delete'] = 'delete';
			}

			$show_control_cols['version'] = 'version';
			$show_overlay_cols = [];
		}

		$colspan_all = $table_cols;

		if (sizeof($show_overlay_cols) > 0) {
			$colspan_all ++;
		}
		$smarty->assign("colspan_all", $colspan_all);

		$smarty->assign('show_overlay', (sizeof($show_overlay_cols) == 0) ? FALSE : TRUE);
		$smarty->assign('show_control_cols', $show_control_cols);
		$smarty->assign('show_overlay_cols', $show_overlay_cols);

		if($mode == "my_list") $smarty->assign("tpl_list_title", getLL("leute_mylist_list_title"));
		else if($mode == 'birthdays') $smarty->assign('tpl_list_title', getLL('leute_birthday_list_title'));
		$smarty->display('ko_list.tpl');
	} else if($mode == "adressliste") {
		$smarty->display('ko_adressliste.tpl');
	}
}//ko_list_personen()




function ko_list_mod_leute() {
	global $smarty, $ko_path, $KOTA;
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
			if(substr($c['Field'], 0, 1) == '_') continue;
			if($p[$c['Field']] == '0' && !$old_p[$c['Field']]) continue;
			if(trim($p[$c['Field']]) == '' && trim($old_p[$c['Field']]) == '') continue;
			if(in_array($c['Field'], array('telegram_id'))) continue;

			if( ( ($p[$c['Field']] != '' && $p[$c['Field']] != '0000-00-00') || $old_p[$c['Field']]) && $p[$c['Field']] != $old_p[$c['Field']]) {
				$mutationen[$counter]['fields'][$fields_counter]['name'] = $c['Field'];
				$mutationen[$counter]['fields'][$fields_counter]['desc'] = $col_names[$c['Field']] ? $col_names[$c['Field']] : $c['Field'];
				if(in_array($KOTA['ko_leute'][$c['Field']]['form']['type'], array('select'))) {
					$mutationen[$counter]['fields'][$fields_counter]['type'] = 'select';
					$mutationen[$counter]['fields'][$fields_counter]['values'] = array_merge(array(''), $KOTA['ko_leute'][$c['Field']]['form']['values']);
					$mutationen[$counter]['fields'][$fields_counter]['descs'] = array_merge(array(''), $KOTA['ko_leute'][$c['Field']]['form']['descs']);
					$translation_for_old_value = getLL('kota_ko_leute_mod_'.$c['Field'].'_'.strtolower($old_p[$c['Field']]));
					$mutationen[$counter]['fields'][$fields_counter]['oldvalue'] = ($translation_for_old_value != '' ? $translation_for_old_value : $old_p[$c['Field']]);
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
	$smarty->assign('help', ko_get_help('leute', 'mutationen'));

	$smarty->assign('tpl_mutationen', $mutationen);
	$smarty->display('ko_adressaenderung.tpl');
}//ko_list_mod_leute()



function ko_list_groupsubscriptions() {
	global $smarty, $ko_path;
	global $DATETIME, $LEUTE_GROUPSUBSCRIPTION_FIELDS;
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
		if($counter > 50) continue;

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
		$gs[$counter]["groupname_full"] = ko_groups_decode(ko_groups_decode($group['id'], 'full_gid'), 'group_desc_full');
		$gs[$counter]["ezmlm"] = $group["ezmlm_list"] != "" ? getLL("ezmlm_ml") : "";
		if($group['maxcount'] > 0) {
			$gs[$counter]['group_limit'] = $group['count'].'/'.$group['maxcount'].($group['count_role'] ? ' '.$role['name'] : '');
		}
		$smarty->assign('tpl_gs_fields', $LEUTE_GROUPSUBSCRIPTION_FIELDS);
		foreach($LEUTE_GROUPSUBSCRIPTION_FIELDS as $gsf) {
			$gs[$counter][$gsf] = ko_html($p[$gsf]);
		}
		//Add age as calculated by the DOB
		$age = (int)date('Y') - (int)substr($p['geburtsdatum'], 0, 4);
		if((int)(substr($p['geburtsdatum'], 5, 2).substr($p['geburtsdatum'], 8, 2)) > (int)(date('md'))) $age--;
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
		$utf8 = false;
		if(false === $df_data) {
			$utf8 = true;
			$df_data = unserialize(utf8_encode($p['_group_datafields']));
		}
		foreach($df_data as $i =>$df) {
			if($utf8) $df_values[$gid][$i] = utf8_decode($df);
			else $df_values[$gid][$i] = $df;
		}
		$gs[$counter]["datafields"] = ko_groups_render_group_datafields($gid, $p["_id"], $df_values, array("hide_title" => true, "add_leute_id" => true));

		//People selects
		$ps = array("gid" => $gid);
		foreach(array("a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z") as $letter) {
			$ps["descs"][] = strtoupper($letter);
			$ps["values"][] = "i".strtoupper($letter);
		}
		$ps["name"] = "ps_".$p["_id"];

		$gs[$counter]["ps"] = $ps;


		//Additional groups
		$avalues = array();
		$gs[$counter]['additional_groups'] = array();
		$gs[$counter]['num_additional_groups'] = sizeof(unserialize($p['_additional_group_ids']));
		if($p['_additional_group_ids'] != '') {
			foreach(unserialize($p['_additional_group_ids']) as $agid => $sel) {
				list($agid, $arid) = explode(":", $agid);
				$agid = format_userinput($agid, "uint");
				$agroup = db_select_data("ko_groups", "WHERE `id` = '$agid'", "*", "", "", true);
				$arid = format_userinput($arid, "uint");
				$arole = $arid ? $all_roles[$arid] : array();

				$agid = 'g'.$agid;
				if($arid) $agid .= ':r'.$arid;

				$title = $agroup['name'];
				$title .= $arole['name'] ? ':'.$arole['name'] : '';
				$gs[$counter]['additional_groups'][] = array('id' => $agid, 'title' => $title, 'checked' => $sel);
				if($sel) $avalues[] = $agid;
			}
		}
		$gs[$counter]['agroups_avalue'] = implode(',', $avalues);

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
	$smarty->assign('label_agroups', getLL('leute_groupsubscriptions_agroups'));
	$smarty->assign('label_all', getLL('all'));
	$smarty->assign('label_filter', getLL('leute_groupsubscriptions_filter'));
	$smarty->assign('current_filter', $_gid);
	$smarty->assign('sesid', session_id());


	$smarty->assign("tpl_list_title", getLL("leute_groupsubscriptions_title"));
	$smarty->assign("tpl_fm_title", getLL("leute_groupsubscriptions_title"));

	$smarty->assign("tpl_gs", $gs);
	$smarty->display("ko_groupsubscription.tpl");
}//ko_list_groupsubscriptions()




function ko_list_leute_revisions() {
	global $smarty, $ko_path, $access, $DATETIME, $LEUTE_REVISIONS_FIELDS;

	$counter=0;
	ko_get_logins($logins);
	ko_get_leute_revisions($revisions, TRUE);
	$mods = array();

	$showFields = $LEUTE_REVISIONS_FIELDS;

	foreach($revisions as $r) {
		if($r['orig_leute_id'] === null) {
			$where = "WHERE id = " . $r['id'];
			db_delete_data("ko_leute_revisions", $where);

			$msg = "Rev.Id: " . $r['id'] . ", Leute_id: " . $r['leute_id'] .", Rev-Info: " . $r['reason'];
			ko_log('del_leute_rev', $msg);

			continue;
		}

		if ($counter > 50) continue;
		$fieldsCounter = 0;

		$mods[$counter]['r'] = $r;
		$mods[$counter]['id'] = $r['id'];

		$reasonHtml = (getLL('leute_revisions_reason_' . $r['reason']) ? getLL('leute_revisions_reason_' . $r['reason']) : $r['reason']);

		if ($r['reason'] == 'groupsubscription' && $r['group_id'] && $fullGroupId = ko_groups_decode($r['group_id'], 'full_gid')) {
			$groupName = ko_groups_decode($fullGroupId, 'group_desc_full');

			$datafields_text = '';
			$group = ko_groups_decode($fullGroupId, 'group');
			if ($group && trim($group['datafields'])) {
				$tempIds = explode(',', $group['datafields']);
				$tempId = implode("','", $tempIds);
				$datafields = db_select_data('ko_groups_datafields', "WHERE `id` IN ('{$tempId}')");
				$datafields_text_array = array();
				foreach ($datafields as $datafield) {
					$datafields_text_ = "<label>{$datafield['description']}:</label><div>";
					$datafield_data = ko_get_datafield_data($group['id'], $datafield['id'], $r['leute_id']);
					if ($datafield['type'] == 'checkbox') {
						$datafields_text_ .= $datafield_data['value'] ? getLL('yes') : getLL('no');
					} else {
						if ($datafield_data['value']) $datafields_text_ .= $datafield_data['value'];
						else $datafields_text_ .= '-';
					}
					$datafields_text_ .= "</div>";
					$datafields_text_array[] = $datafields_text_;
				}
				if ($datafields_text) $datafields_text .= '<hr style="margin: 5px -8px 3px -8px;">';
				$datafields_text .= '<h4 style="text-align:center;">'.$group['name'].'</h4>';
				$datafields_text .= implode('<hr style="margin:3px 0px;">', $datafields_text_array);
			}

			$groupHtml = '<div style="display:inline-block;margin-bottom:2px;" class="label label-primary" ' . ko_get_tooltip_code($datafields_text) . '>' . $groupName . '</div>';
			$reasonHtml = $reasonHtml . '&nbsp;'.$groupHtml;
		}


		$mods[$counter]['p_fields'][$fieldsCounter++] = array(
			"type" => "html",
			'desc' => getLL('leute_revisions_reason'),
			'name' => 'reason_' . $r['id'],
			'value' => $reasonHtml,
		);

		foreach ($showFields as $fieldName) {
			$fieldValue = map_leute_daten($r[$fieldName], $fieldName);
			if (trim($fieldValue) == '') continue;
			$mods[$counter]['p_fields'][$fieldsCounter++] = array(
				"type" => "html",
				'desc' => getLL('kota_ko_leute_' . $fieldName),
				'name' => $fieldName . '_' . $r['id'],
				'value' => $fieldValue,
			);
		}


		$mods[$counter]['p_label'] = implode(' ', array($r['vorname'], $r['nachname']));

		//Try to find person in DB
		if($r['vorname'] && $r['nachname']) {
			$search = array('vorname' => $r['vorname'], 'nachname' => $r['nachname']);

			if ($r['adresse']) {
				$search['adresse'] = $r['adresse'];
				$search['adresse'] = implode(" ", array_map(function($el){return '+'.$el;}, explode(' ', $r['adresse'])));
				if (strpos($r['adresse'], 'str.') !== FALSE) {
					$search['adresse'] = '(' . $search['adresse'] . ') (' . str_replace('str.', 'strasse', $r['adresse']) . ')';
				} else if (strpos($r['adresse'], 'strasse') !== FALSE) {
					$search['adresse'] = '(' . $search['adresse'] . ') (' . str_replace('strasse', 'str.', $r['adresse']) . ')';
				}
			}
		} else if($r['email']) {
			$search = array('email' => $r['email']);
		} else if($r['firm']) {
			$search = array('firm' => $r['firm']);
		}
		$found_dbp = ko_fuzzy_search_2($search, "ko_leute", 1, 5);
		$db = null;
		foreach($found_dbp as $dbp) {
			if ($dbp['id'] == $r['leute_id']) continue;
			$rev = db_select_data('ko_leute_revisions', "WHERE `leute_id` = {$dbp['id']}", '*', '', '', TRUE);
			if ($rev) continue;

			//Prepare tooltip
			$tooltipValues = array();
			foreach($showFields as $fieldName) {
				$fieldValue = map_leute_daten($dbp[$fieldName], $fieldName);
				if(trim($fieldValue) == '') continue;
				$tooltipValues[] = strip_tags($fieldValue);
			}

			$db[] = [
				"_id" => $r["id"],
				"lid" => $dbp["id"],
				"name" => $dbp["vorname"]." ".$dbp["nachname"],
				'firm' => $dbp['firm'],
				'department' => $dbp['department'],
				"adressdaten" => implode(', ', $tooltipValues),
				'hidden' => $dbp['hidden'],
			];
		}


		$mods[$counter]['db'] = $db;
		$mods[$counter]['selectedPerson'] = array(
			"type" => "peoplesearch",
			"single" => TRUE,
			'exclude' => $r['leute_id'],
			'name' => 'add_to_selected_person_' . $r['id'],
		);

		//Show creation date and user
		if($r['crdate'] != '0000-00-00 00:00:00') $mods[$counter]['crdate'] = strftime($DATETIME['dmY'].' %H:%M', strtotime($r['crdate']));
		if($r['cruser'] > 0) $mods[$counter]['cruser'] = getLL('by').' '.$logins[$r['cruser']]['login'];

		$counter++;
	}//foreach(donations as d)


	if(sizeof($revisions) == 0) $smarty->assign('tpl_leute_revisions_empty', true);
	//LL-Values
	$smarty->assign('label_empty', getLL('leute_revisions_list_empty'));
	$smarty->assign('label_submit', getLL('leute_revisions_list_submit'));
	$smarty->assign('label_delete', getLL('leute_revisions_list_delete'));
	$smarty->assign('label_crdate', getLL('leute_revisions_crdate'));
	$smarty->assign('label_confirm_delete', getLL('leute_revisions_confirm_delete'));
	$smarty->assign('ko_path', $ko_path);

	$smarty->assign('tpl_list_title', getLL('leute_revisions_title'));
	$smarty->assign('tpl_title', getLL('leute_revisions_title'));
	$smarty->assign('help', ko_get_help('leute', 'revisions'));

	$smarty->assign('showDeleteAddress', ko_get_setting('leute_delete_revision_address'));

	$smarty->assign('tpl_mods', $mods);
	$smarty->display('ko_leute_revisions.tpl');
} // ko_list_leute_revisions()





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
			$new_code  = '<select name="var1" class="input-sm form-control">';
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
	// send mail if this function is called
	ko_log('familie_filter', "Function ko_update_familie_filter called. should not be used anymore.");
	return FALSE;
}


function ko_leute_import(&$context) {
	global $smarty, $access, $BOOTSTRAP_COLS_PER_ROW, $ko_path;

	if(!ko_get_setting('leute_allow_import')) return;

	$state = &$context['state'];

	switch ($state) {
		case 1:
			$rowcounter = 0;
			$gc = 0;

			$frmgroup[$gc]["row"][$rowcounter]["inputs"][0] = array("desc" => getLL("leute_import_state1_csv"),
				"type" => "file",
				"name" => "csv",
			);

			$frmgroup[++$gc] = array('titel' => getLL('leute_import_state1_additional_settings'), 'state' => 'closed', 'name' => 'additional_settings');
			$frmgroup[$gc]["row"][$rowcounter]["inputs"][0] = array("desc" => getLL("leute_import_state1_csv_separator"),
				"type" => "text",
				"name" => "parameters[separator]",
				"value" => $context['parameters']['separator'] ? $context['parameters']['separator'] : ',',
			);
			$frmgroup[$gc]["row"][$rowcounter++]["inputs"][1] = array("desc" => getLL("leute_import_state1_csv_content_separator"),
				"type" => "text",
				"name" => "parameters[content_separator]",
				"params" => 'size="6"',
				"value" => $context['parameters']['content_separator'] ? $context['parameters']['content_separator'] : '&quot;',
			);
			$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('leute_import_state1_csv_file_encoding'),
				'type' => 'select',
				'name' => 'parameters[file_encoding]',
				'params' => 'size="0"',
				'values' => array('utf-8', 'iso-8859-1', 'macintosh'),
				'descs' => array('Unicode (UTF-8)', 'Latin1 (iso-8859-1)', 'Mac Roman'),
				'value' => $context['parameters']['file_encoding'] ? $context['parameters']['file_encoding'] : 'utf-8',
			);
			$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('leute_import_state1_csv_first_line'),
				'type' => 'switch',
				'name' => 'ignore_first_line',
				'value' => $context['ignoreFirstLine'] == 1 ? '1' : '0',
			);
			$smarty->assign("tpl_titel", getLL("leute_import_state1"));
			$smarty->assign("tpl_hide_cancel", true);
			$smarty->assign("tpl_submit_value", getLL("next"));
			$smarty->assign("tpl_action", "importtwo");
			$smarty->assign("tpl_groups", $frmgroup);
			$smarty->assign("help", ko_get_help('leute', 'import'));
			$smarty->assign("tpl_hidden_inputs", array(array('name' => 'leute_import_manual_parameters', 'value' => isset($_POST['leute_import_manual_parameters']) ? $_POST['leute_import_manual_parameters'] : 0)));
			$smarty->display('ko_formular.tpl');
			print(
"<script>
	$('#group_additional_settings').on('hide.bs.collapse', function () {
		$('input[name=\"leute_import_manual_parameters\"]').val('0');
	});
	$('#group_additional_settings').on('show.bs.collapse', function () {
		$('input[name=\"leute_import_manual_parameters\"]').val('1');
	});
</script>");
		break;
		case 2:
			$data = &$context['data'];
			$assign = &$context['fieldAssignments'];

			$rowcounter = 0;
			$gc = 0;

			$labels = $context['header'];
			$example = $data[0];

			$dontAllow = array("id", "famid", "deleted", "hidden", "picture", "groups", "kinder", "smallgroups", "famfunction", "lastchange", 'crdate', 'cruserid', 'import_id', 'spouse', 'father', 'mother');
			$colNames = ko_get_leute_col_name();

			$values = $descs = array();
			$values[] = '';
			$descs[] = getLL('leute_import_label_column');

			$dbCols = db_get_columns('ko_leute');
			foreach ($dbCols as $field) {
				$value = $field['Field'];
				$desc = $colNames[$value] ? $colNames[$value] : $value;

				if (in_array($value, $dontAllow)) continue;

				$values[] = $value;
				$descs[] = $desc;
			}
			// Add groups to choose from
			$groups = null;
			ko_get_groups($groups, 'and `type` = 0');
			$fullGroupIds = array();
			if (!is_array($access['groups'])) ko_get_access('groups');
			foreach (array_keys($groups) as $groupId) {
				if ($access['groups']['ALL'] < 2 && $access['groups'][$groupId] < 2) continue;
				$fullGroupIds[] = ko_groups_decode($groupId, 'full_gid');
			}
			asort($fullGroupIds);
			$fullGroupIds = array_merge($fullGroupIds);
			foreach ($fullGroupIds as $fullGroupId) {
				$key = 'MODULEgrp' . substr($fullGroupId, -6);
				$values[] = $key;
				$descs[] = ko_groups_decode($fullGroupId, "group_desc_full");
			}

			$html = '<table id="leute-import-mapping" class="ko_list table table-condensed table-bordered"><tbody>';

			$html .= '<tr class="row-info">';
			$html .= '<td><label>'.getLL('daten_import_label_col').'</label></td>';
			$html .= '<td><label>'.getLL('daten_import_label_assign_field').'</label></td>';
			$html .= '<td><label>'.getLL('daten_import_label_value').'</label></td>';
			$html .= '</tr>';

			foreach ($example as $k => $line) {
				$label = $labels[$k];
				$html .= '<tr class="row-success">';
				$html .= '<td>'.$label.'</td>';
				$html .= '<td>';
				$html .= '<select class="input-sm form-control" name="assign_field['.$k.']" onchange="sendReq(\''.$ko_path.'leute/inc/ajax.php\', \'action,field,k,sesid\', \'getimportmappings,\'+this.options[this.selectedIndex].value+\','.$k.','.session_id().'\', do_element);">';
				$active = $assign[$k];
				if (!$active) {
					$searchLabel = trim(strtolower($label));
					$key = array_search($searchLabel, array_map(function($el) {return strtolower($el);}, $descs));
					if (in_array($searchLabel, array_map(function($el) {return strtolower($el);}, $values))) {
						$active = $searchLabel;
					} else if ($key !== FALSE) {
						$active = $values[$key];
					}
				}
				$assign[$k] = $active;
				foreach ($values as $kk => $value) {
					$desc = $descs[$kk];
					$html .= '<option value="'.$value.'"'.($value == $active ? ' selected="selected"' : '').'>'.$desc.'</option>';
				}
				$html .= '</select>';
				$html .= '</td>';
				$html .= '<td>'.$line.'</td>';
				$html .= '</tr>';

				$html .= '<tr><td colspan="3" name="leute-import-mapping-'.$k.'" id="leute-import-mapping-'.$k.'">';
				$html .= ko_leute_import_get_mapping_html($context, $k);
				$html .= '</td></tr>';

				$rowcounter++;
			}

			$html .= '</tbody></table>';

			$frmgroup[$gc]['row'][$rowcounter++]['inputs'][0] = array('desc' => '',
				'type' => 'html',
				'name' => 'data_table',
				'value' => $html,
				'columnWidth' => $BOOTSTRAP_COLS_PER_ROW,
			);

			$value = $context['addToGroup'];
			$values = $descs = array('');
			foreach ($fullGroupIds as $fullGroupId) {
				$key = substr($fullGroupId, -6);
				$values[] = $key;
				$descs[] = ko_groups_decode($fullGroupId, "group_desc_full") . " (".ko_get_group_count($key).")";
			}
			$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('leute_import_label_add_to_group'),
				'type' => 'select',
				'name' => 'add_to_group',
				'value' => $value,
				'values' => $values,
				'descs' => $descs,
				'columnWidth' => $BOOTSTRAP_COLS_PER_ROW / 2,
			);
			$value = $context['createRevision'];
			$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('leute_import_label_create_revision'),
				'type' => 'switch',
				'name' => 'create_revision',
				'value' => $value,
				'columnWidth' => $BOOTSTRAP_COLS_PER_ROW / 2,
			);




			$frmgroup[++$gc] = array('titel' => getLL('leute_import_state1_additional_settings'), 'state' => isset($_POST['leute_import_manual_parameters']) ? ($_POST['leute_import_manual_parameters'] ? 'open' : 'closed') : 'closed', 'name' => 'additional_settings');
			$frmgroup[$gc]["row"][$rowcounter]["inputs"][0] = array("desc" => getLL("leute_import_state1_csv_separator"),
				"type" => "text",
				"name" => "parameters[separator]",
				"value" => $context['parameters']['separator'] ? $context['parameters']['separator'] : ',',
			);
			$frmgroup[$gc]["row"][$rowcounter++]["inputs"][1] = array("desc" => getLL("leute_import_state1_csv_content_separator"),
				"type" => "text",
				"name" => "parameters[content_separator]",
				"params" => 'size="6"',
				"value" => $context['parameters']['content_separator'] ? $context['parameters']['content_separator'] : '&quot;',
			);
			$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('leute_import_state1_csv_file_encoding'),
				'type' => 'select',
				'name' => 'parameters[file_encoding]',
				'params' => 'size="0"',
				'values' => array('utf-8', 'iso-8859-1', 'macintosh'),
				'descs' => array('Unicode (UTF-8)', 'Latin1 (iso-8859-1)', 'Mac Roman'),
				'value' => $context['parameters']['file_encoding'] ? $context['parameters']['file_encoding'] : 'utf-8',
			);
			$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('leute_import_state1_csv_first_line'),
				'type' => 'switch',
				'name' => 'ignore_first_line',
				'value' => $context['ignoreFirstLine'] == 1 ? '1' : '0',
			);
			$frmgroup[$gc]['row'][$rowcounter++]['inputs'][0] = array('desc' => '',
				'type' => 'html',
				'value' =>
'<div class="btn-field">
	<button type="submit" class="btn btn-warning" name="submit" value="submit" onclick="var ok = confirm(\''.getLL('leute_import_confirm_save_parameters').'\'); if (ok) {set_action(\'importtwo\', this)} else return false;">
		'.getLL('save').' <i class="fa fa-save"></i>
	</button>
</div>',
				'columnWidth' => $BOOTSTRAP_COLS_PER_ROW,
			);

			$smarty->assign("tpl_titel", getLL("leute_import_state2"));
			$smarty->assign("tpl_hide_cancel", true);
			$smarty->assign("tpl_submit_value", getLL("next"));
			$smarty->assign("tpl_action", "importthree");
			$smarty->assign("tpl_groups", $frmgroup);
			$smarty->assign("help", ko_get_help('leute', 'import'));
			$smarty->assign("tpl_hidden_inputs", array(array('name' => 'leute_import_manual_parameters', 'value' => isset($_POST['leute_import_manual_parameters']) ? $_POST['leute_import_manual_parameters'] : 0)));
			$smarty->display('ko_formular.tpl');

			print(
"<script>
	$('#group_additional_settings').on('hide.bs.collapse', function () {
		$('input[name=\"leute_import_manual_parameters\"]').val('0');
	});
	$('#group_additional_settings').on('show.bs.collapse', function () {
		$('input[name=\"leute_import_manual_parameters\"]').val('1');
	});
</script>");
		break;
		case 3:
			$data = $context['transformedData'];
			$assign = $context['fieldAssignments'];
			$addToGroup = $context['addToGroup'];
			$createRevision = $context['createRevision'];

			$rowcounter = 0;
			$gc = 0;

			$colNames = ko_get_leute_col_name();

			$gc = $rowcounter = 0;

			$html = '<table class="ko_list table table-condensed table-bordered"><tbody>';

			$html .= '<tr class="row-info">';
			for ($i = 0; $i < sizeof($data[0]); $i++) {
				$column = $assign[$i];
				if (!$column) continue;
				if (substr($column, 0, strlen('MODULEgrp') == 'MODULEgrp')) {
					$groupId = substr($column, strlen('MODULEgrp'));
					$group = db_select_data('ko_groups', "WHERE `id` 0 '{$groupId}'", 'id, name', '', '', TRUE);
					$colName = $group['name'];
				} else {
					$colName = $colNames[$column];
				}
				$html .= '<th>'.$colName.'</th>';
			}
			if ($addToGroup) {
				$fullGid = ko_groups_decode($addToGroup, 'full_gid');
				$html .= '<th>'.ko_groups_decode($fullGid, 'group_desc_full').'</th>';
			}
			$html .= '</tr>';

			foreach ($data as $line) {
				$html .= '<tr>';
				for ($i = 0; $i < sizeof($line); $i++) {
					$column = $assign[$i];
					if (!$column) continue;
					if (ko_leute_col_is_mappable($column)) {
						$mapping = $context['mappings'][$i];
						$value = $mapping['curr'][$line[$i]];
						$key = array_search($value, $mapping['possValues']);
						$value = $mapping['possDescs'][$key];
					} else {
						$value = $line[$i];
					}
					$html .= '<td>'.$value.'</td>';
				}
				if ($addToGroup) {
					$html .= '<td>'.getLL('leute_import_label_group_member').'</td>';
				}
				$html .= '</tr>';
			}

			$html .= '</tbody></table>';

			$frmgroup[$gc]['row'][$rowcounter++]['inputs'][0] = array('desc' => '',
				'type' => 'html',
				'name' => 'data_table',
				'value' => $html,
				'columnWidth' => $BOOTSTRAP_COLS_PER_ROW,
			);

			$smarty->assign("tpl_titel", getLL("leute_import_state3"));
			$smarty->assign("tpl_hide_cancel", true);
			$smarty->assign("tpl_special_submit",
'<a class="btn btn-warning" name="back" value="back" href="?action=importgoto&state=2">
	<i class="fa fa-arrow-left"></i> '.getLL('leute_import_label_back').'
</a>
<button type="submit" class="btn btn-primary" name="submit" value="submit" onclick="var ok = check_mandatory_fields($(this).closest(\'form\')); if (ok) {set_action(\'importfour\', this)} else return false;">
	'.getLL('leute_import_do_import').' <i class="fa fa-save"></i>
</button>');
			$smarty->assign("tpl_submit_value", getLL("next"));
			$smarty->assign("tpl_action", "importfour");
			$smarty->assign("tpl_groups", $frmgroup);
			$smarty->assign("help", ko_get_help('leute', 'import'));
			$smarty->display('ko_formular.tpl');
		break;
	}
}



function ko_leute_import_get_mapping_html(&$context, $nth, $field=NULL) {
	global $KOTA, $smarty;

	$data = &$context['data'];
	$assign = &$context['fieldAssignments'];

	$active = $field ? $field : $assign[$nth];

	$showMapping = ($active && ko_leute_col_is_mappable($active));

	$html = '';
	if ($showMapping) {
		$mapping =  $context['mappings'][$nth];

		if (!$mapping) {
			$mappingFroms = array_unique(ko_array_column($data, $nth));
			$rv = ko_leute_get_possible_values_for_col($active);
			$possValues = $rv['values'];
			$possDescs = $rv['descs'];
			$possUserDescs = $rv['userDescs'];
			$current = array();
			foreach ($mappingFroms as $mappingFrom) {
				$mappingTo = '';
				for ($i = 0; $i < sizeof($possValues); $i++) {
					if (in_array(trim(strtolower($mappingFrom)), $possUserDescs[$i])) $mappingTo = $possValues[$i];
				}
				$current[$mappingFrom] = $mappingTo;
			}

			$mapping = array(
				'possDescs' => $possDescs,
				'possValues' => $possValues,
				'possUserDescs' => $possUserDescs,
				'curr' => $current,
			);
		}

		$context['mappings'][$nth] = $mapping;

		$iHtml = '<div class="form-horizontal">';
		$counter = 0;
		foreach ($mapping['curr'] as $from => $to) {
			$iHtml .= '
<div class="form-group" style="margin-bottom: '.($counter == sizeof($mapping['curr']) -1 ? '0' : '3').'px;">
	<label class="col-sm-4 col-md-2 control-label"><span '.ko_get_tooltip_code(getLL('leute_import_label_value_in_file')).'>'.$from.'</span>&nbsp;&nbsp;&nbsp;--></label>
	<div class="col-sm-6 col-md-7 col-lg-5">';

			if ($KOTA['ko_leute'][$active]['form']['type'] == 'textplus') {
				$smarty->assign('input', array('type' => 'textplus', 'name' => 'mappings['.$nth.']['.$from.']', 'value' => $to, 'values' => $mapping['possValues'], 'descs' => $mapping['possDescs']));
				$iHtml .= $smarty->fetch('ko_formular_elements.tmpl');
			} else {

				$iHtml .= '
		<select class="input-sm form-control" name="mappings['.$nth.']['.$from.']">
			';
				foreach ($mapping['possValues'] as $i => $candTo) {
					$iHtml .= '<option value="'.$candTo.'"'.($candTo == $to ?' selected="selected"':'').'>'.$mapping['possDescs'][$i].'</option>';
				}
				$iHtml .= '
		</select>';
			}

			$iHtml .= '
	</div>
</div>
';

			$counter++;
		}
		$iHtml .= '</div>';

		$html .= $iHtml;

		$html .= '<script>$(\'#leute-import-mapping-'.$nth.'\').show();</script>';
	} else {
		$html .= '<script>$(\'#leute-import-mapping-'.$nth.'\').hide();</script>';
	}

	return $html;
}



function ko_leute_col_is_mappable($col) {
	global $KOTA;

	if (substr($col, 0, strlen('MODULEgrp')) == 'MODULEgrp') {
		return TRUE;
	} else if (in_array($KOTA['ko_leute'][$col]['type'], array('textplus', 'select'))) {
		return TRUE;
	} else {
		return FALSE;
	}
}



function ko_leute_get_possible_values_for_col($col) {
	global $LEUTE_TEXTSELECT, $KOTA;

	$values = array('');
	$descs = array('');
	$userDescs = array('');
	$default = array('values' => $values, 'descs' => $descs, 'userDescs' => $userDescs);

	if (!ko_leute_col_is_mappable($col)) return $default;

	if (substr($col, 0, strlen('MODULEgrp')) == 'MODULEgrp') {
		$groupId = substr($col, -6);
		$group = db_select_data('ko_groups', "WHERE `id` = {$groupId}", '*', '', '', TRUE);
		if ($group['id'] != $groupId) return $default;

		$values[] = 'x';
		$descs[] = getLL('leute_import_label_group_member');
		$userDescs[] = array_map(function($el){return strtolower($el);}, array('x', '1', 'yes', getLL('yes'), 'member', getLL('leute_import_label_group_member')));

		$roleIds = array_filter(explode(',', $group['roles']), function($el){return trim($el) != '';});
		if (sizeof($roleIds) > 0) {
			$roles = db_select_data('ko_grouproles', "WHERE `id` IN (".implode(',', $roleIds).")");
			foreach ($roles as $role) {
				$values[] = "r".$role['id'];
				$descs[] = getLL('groups_role').": ".$role['name'];
				$userDescs[] = array(strtolower($role['name']));
			}
		}
	} else if ($KOTA['ko_leute'][$col]['form']['type'] == 'textplus') {
		$allValues = db_select_distinct('ko_leute', $col, "ORDER BY `{$col}` ASC");
		foreach ($allValues as $value) {
			if (!in_array($value, $values)) {
				$values[] = $value;
				$descs[] = $value;
				$userDescs[] = array(strtolower($value));
			}
		}
	} else if ($KOTA['ko_leute'][$col]['form']['type'] == 'select') {
		foreach ($KOTA['ko_leute'][$col]['form']['values'] as $value) {
			$value = trim($value, "' ");
			if (!in_array($value, $values)) {
				$values[] = $value;
				$desc = getLL('kota_ko_leute_' . $col . '_' . strtolower($value));
				$descs[] = $desc ? $desc : $value;
				$userDesc = array(strtolower($value));
				if ($desc) $userDesc[] = strtolower($desc);
				$userDescs[] = $userDesc;
			}
		}
	} else {
		return $default;
	}
	return array('values' => $values, 'descs' => $descs, 'userDescs' => $userDescs);
}



function ko_leute_import_old($state, $mode) {
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
				if (!is_array($access['groups'])) ko_get_access('groups');
				foreach (array_keys($groups) as $groupId) {
					if ($access['groups']['ALL'] < 2 && $access['groups'][$groupId] < 2) continue;
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
						'type' => 'switch',
						'name' => 'chk_first_line',
						'value' => $_SESSION['import_csv']['first_line'] == 1 ? '1' : '0',
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
				if (substr($key, 0, 9) == "MODULEgrp") {
					$entries .= '<th>'.ko_groups_decode(substr($key, 9), "group_desc_full").'</th>';
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
 *
 * @param int $layout_id
 */
function ko_export_leute_as_pdf_settings($layout_id) {
	global $smarty;
	global $LEUTE_NO_FAMILY;

	$gc = $rowcounter = 0;

	$_layout = db_select_data("ko_pdf_layout", "WHERE `id` = '$layout_id'", "*", "", "", TRUE);
	$layout = unserialize($_layout["data"]);

	//Prepare filter select
	$filter_values = $filter_descs = [];
	if (sizeof($_SESSION['my_list']) > 0) {
		$filter_values[] = '_mylist';
		$filter_descs[] = getLL('leute_export_pdf_filter_mylist');
	}
	if ($layout["filter"]) {
		$filter_values[] = "_layout";
		$filter_descs[] = getLL("leute_export_pdf_filter_layout");
	}

	$filter_values[] = "_current";
	$filter_descs[] = getLL("leute_export_pdf_filter_current");
	$filter_values[] = "_currently_sel";
	$filter_descs[] = getLL("leute_export_pdf_filter_currently_sel");
	$filterset = array_merge((array)ko_get_userpref('-1', '', 'filterset', 'ORDER BY `key` ASC'), (array)ko_get_userpref($_SESSION['ses_userid'], '', 'filterset', 'ORDER BY `key` ASC'));

	foreach ($filterset as $f) {
		$filter_values[] = $f['user_id'] == '-1' ? '@G@' . $f['key'] : $f['key'];
		$filter_descs[] = $f['user_id'] == '-1' ? getLL('itemlist_global_short') . ' ' . $f['key'] : $f['key'];
	}

	if ($layout['filter']) {
		$filter_selected = '_layout';
	} else if ($_SESSION['show_back'] == 'show_my_list' && sizeof($_SESSION['my_list']) > 0) {  //Use my list if entries
		$filter_selected = '_mylist';
	} else if (substr($_POST['sel_auswahl'],0, 8) == "markiert") {
		$filter_selected = "_currently_sel";
	} else {
		$filter_selected = '_current';
	}

	//Prepare columns select
	if ($layout["columns"]) {
		$columns_values[] = "_layout";
		$columns_descs[] = getLL("leute_export_pdf_columns_layout");
	}
	$columns_values[] = "_current";
	$columns_descs[] = getLL("leute_export_pdf_columns_current");
	$itemset = array_merge((array)ko_get_userpref('-1', '', 'leute_itemset', 'ORDER BY `key` ASC'), (array)ko_get_userpref($_SESSION['ses_userid'], '', 'leute_itemset', 'ORDER BY `key` ASC'));
	foreach ($itemset as $f) {
		$columns_values[] = $f['user_id'] == '-1' ? '@G@' . $f['key'] : $f['key'];
		$columns_descs[] = $f['user_id'] == '-1' ? getLL('itemlist_global_short') . ' ' . $f['key'] : $f['key'];
	}

	$group[$gc] = ["titel" => getLL("leute_export_pdf_title_data"), "state" => "open"];
	$group[$gc]["row"][$rowcounter++]["inputs"][0] = [
		"desc" => getLL("leute_export_pdf_filter"),
		"type" => "select",
		"name" => "pdf[filter]",
		"values" => $filter_values,
		"descs" => $filter_descs,
		"value" => $filter_selected,
		"params" => 'size="0"',
	];
	$group[$gc]["row"][$rowcounter]["inputs"][0] = [
		"desc" => getLL("leute_export_pdf_columns"),
		"type" => "select",
		"name" => "pdf[columns]",
		"values" => $columns_values,
		"descs" => $columns_descs,
		"value" => "_layout",
		"params" => 'size="0"',
	];

	//Header and Footer
	$group[++$gc] = ["titel" => getLL("leute_export_pdf_title_headerfooter"), "state" => "open"];
	$group[$gc]["row"][$rowcounter++]["inputs"][0] = [
		"desc" => getLL("help"),
		"type" => "html",
		"value" => getLL("leute_export_pdf_help_headerfooter"),
		"colspan" => 'colspan="3"',
	];
	$group[$gc]["row"][$rowcounter++]["inputs"][0] = [
		"type" => "   ",
		"colspan" => 'colspan="3"'
	];
	$group[$gc]["row"][$rowcounter]["inputs"][0] = [
		"desc" => getLL("leute_export_pdf_header_left"),
		"type" => "text",
		"name" => "pdf[header][left][text]",
		"value" => $layout["header"]["left"]["text"],
		"params" => 'size="50"',
	];
	$group[$gc]["row"][$rowcounter]["inputs"][1] = [
		"desc" => getLL("leute_export_pdf_header_center"),
		"type" => "text",
		"name" => "pdf[header][center][text]",
		"value" => $layout["header"]["center"]["text"],
		"params" => 'size="50"',
	];
	$group[$gc]["row"][$rowcounter++]["inputs"][2] = [
		"desc" => getLL("leute_export_pdf_header_right"),
		"type" => "text",
		"name" => "pdf[header][right][text]",
		"value" => $layout["header"]["right"]["text"],
		"params" => 'size="50"',
	];
	$group[$gc]["row"][$rowcounter]["inputs"][0] = [
		"desc" => getLL("leute_export_pdf_footer_left"),
		"type" => "text",
		"name" => "pdf[footer][left][text]",
		"value" => $layout["footer"]["left"]["text"],
		"params" => 'size="50"',
	];
	$group[$gc]["row"][$rowcounter]["inputs"][1] = [
		"desc" => getLL("leute_export_pdf_footer_center"),
		"type" => "text",
		"name" => "pdf[footer][center][text]",
		"value" => $layout["footer"]["center"]["text"],
		"params" => 'size="50"',
	];
	$group[$gc]["row"][$rowcounter]["inputs"][2] = [
		"desc" => getLL("leute_export_pdf_footer_right"),
		"type" => "text",
		"name" => "pdf[footer][right][text]",
		"value" => $layout["footer"]["right"]["text"],
		"params" => 'size="50"',
	];

	$smarty->assign("tpl_titel", getLL("leute_export_pdf") . ": " . $_layout["name"]);
	$smarty->assign("tpl_submit_value", getLL("leute_export_pdf_submit"));
	$smarty->assign("tpl_action", "do_export_pdf");
	$smarty->assign("tpl_cancel", "show_all");
	$smarty->assign("tpl_groups", $group);
	$smarty->assign("tpl_hidden_inputs", [0 => ["name" => "layout_id", "value" => $layout_id], 1 => ['name' => 'pdf[filter_sel_ids]', 'value' => $_POST['ids']]]);

	$smarty->display('ko_formular.tpl');
}//ko_export_leute_as_pdf_settings()


/**
 * Exports address data as pdf file
 *
 * @param int    $layout_id
 * @param string $settings
 * @param bool   $force
 * @param bool   $fromSession
 * @return bool|string
 */
function ko_export_leute_as_pdf($layout_id, $settings = "", $force = FALSE, $fromSession = FALSE) {
	global $ko_path;
	global $all_groups;
	global $access;
	global $cols_no_map;
	global $RECTYPES;

	$z_where = "";
	if (!$layout_id) return FALSE;

	if (!$all_groups) ko_get_groups($all_groups);
	$all_datafields = db_select_data("ko_groups_datafields", "WHERE 1=1", "*");

	//Get selected layout
	$_layout = db_select_data("ko_pdf_layout", "WHERE `id` = '$layout_id'", "*", "", "", TRUE);
	$layout = unserialize($_layout["data"]);

	$post = $settings ? $settings : $_POST["pdf"];

	/* Columns to be used */
	$cols = [];
	//Get columns from layout
	if ($post["columns"] == "_layout" && $layout["columns"]) {
		$do_cols = $layout["columns"];
	} //Get columns as array from post (used for T3-Extension kool_leute)
	else if (is_array($post["columns"]) && sizeof($post["columns"]) > 0) {
		$do_cols = $post["columns"];
	} //Get columns from userprefs
	else if ($post["columns"] && $post["columns"] != "_current") {
		if (substr($post['columns'], 0, 3) == '@G@') $value = ko_get_userpref('-1', substr($post["columns"], 3), "leute_itemset");
		else $value = ko_get_userpref($_SESSION["ses_userid"], $post["columns"], "leute_itemset");
		$do_cols = explode(",", $value[0]["value"]);
	} //Otherwise use the currently displayed columns
	else {
		$do_cols = $_SESSION["show_leute_cols"];
	}

	//Prepare columns with group/groupdatafield info
	$leute_col_name = ko_get_leute_col_name($groups_hierarchie = FALSE, $add_group_datafields = TRUE, "view", $force);

	foreach ($do_cols as $k => $c) {
		$colName = $leute_col_name[$c];
		if (!$colName) {
			unset($do_cols[$k]);
			continue;
		}
		$cols[$c] = $colName;
	}
	$layout["columns"] = $cols;

	/* Sorting */
	if ($layout["sort"]) {
		$layout["sort"] = [$layout["sort"]];
		$layout["sort_order"] = [$layout["sort_order"]];
	} else if ($post["filter"] == "_layout" && $layout["filter"] && $layout["filter"]['sort']) {
		$layout["sort"] = explode(',', $layout["filter"]["sort"]);
		$layout["sort_order"] = explode(',', $layout["filter"]["sort_order"]);
	} else if ($post["filter"] != "_current" && $post["filter"] != "_currently_sel") {
		if (substr($post["filter"], 0, 3) == '@G@') $value = ko_get_userpref('-1', substr($post["filter"], 3), "filterset");
		else $value = ko_get_userpref($_SESSION["ses_userid"], $post["filter"], "filterset");
		$filter = unserialize($value[0]["value"]);
		if (trim($filter["sort"]) != '') {
			$layout["sort"] = explode(',', $filter["sort"]);
			$layout["sort_order"] = explode(',', $filter["sort_order"]);
		} else {
			$layout["sort"] = $_SESSION["sort_leute"];
			$layout["sort_order"] = $_SESSION["sort_leute_order"];
		}
	} else {
		$layout["sort"] = $_SESSION["sort_leute"];
		$layout["sort_order"] = $_SESSION["sort_leute_order"];
	}
	//Switch sorting for DOB column (according to userpref)
	if (in_array('geburtsdatum', $layout['sort']) && ko_get_userpref($_SESSION['ses_userid'], 'leute_sort_birthdays') == 'monthday') {
		$new = [];
		foreach ($layout['sort'] as $col) {
			if ($col == 'geburtsdatum') $new[] = 'MODULE' . $col;
			else $new[] = $col;
		}
		$layout['sort'] = $new;
	}

	/* Get Filter */
	if ($post["filter"] == "_layout") {
		$do_filter = $layout["filter"];
	} else if ($post['filter'] == '_currently_sel') {
		$ids = $post['filter_sel_ids'];
		if (trim($ids) == '') {
			$z_where = " AND 1=2 ";
		} else {
			$z_where = " AND `id` IN (" . $ids . ') ';
		}
	} //Use my list
	else if ($post['filter'] == '_mylist') {
		if (sizeof($_SESSION['my_list']) > 0) {
			$z_where = " AND `id` IN ('" . implode("','", $_SESSION['my_list']) . "') ";
		} else {
			$z_where = ' AND 1=2 ';
		}
	} //Get filter as array from post (used for T3-Extension kool_leute)
	else if (is_array($post["filter"]) && sizeof($post["filter"]) > 0) {
		$z_where = $post["filter"]["where"];
	} //Get filter from userpref
	else if ($post["filter"] && $post["filter"] != "_current") {
		if (substr($post['filter'], 0, 3) == '@G@') {
			$value = ko_get_userpref('-1', "", "filterset");
			$post['filter'] = substr($post['filter'], 3);
		} else $value = ko_get_userpref($_SESSION["ses_userid"], "", "filterset");
		foreach ($value as $v_i => $v) {
			if ($v["key"] == $post["filter"]) $do_filter = unserialize($value[$v_i]["value"]);
		}
	} //Use current filter
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
	foreach ($layout["sort"] as $i => $col) {
		if (substr($col, 0, 6) != "MODULE") {
			$sort_add[] = $col . " " . $layout["sort_order"][$i];
		}
	}

	if (!in_array("nachname", $layout["sort"])) $sort_add[] = "nachname ASC";
	if (!in_array("vorname", $layout["sort"])) $sort_add[] = "vorname ASC";
	$sql_sort = "ORDER BY " . implode(", ", $sort_add);
	//z_where can be set if called by T3 extension kool_leute through get.php
	if (!$z_where) apply_leute_filter($do_filter, $z_where, $access['leute']['ALL'] < 1);

	ko_get_leute($es, $z_where, "", "", $sql_sort);

	$restricted_leute_ids = ko_apply_leute_information_lock();
	if (!empty($restricted_leute_ids)) {
		foreach($restricted_leute_ids AS $restricted_leute_id) {
			unset($es[$restricted_leute_id]);
		}
	}

	if ($fromSession) {
		$oldPost = $_POST;
		$oldGet = $_GET;
		$_POST = $_SESSION['post_data'];
		$_GET = $_SESSION['get_data'];
		$mapLeuteDatenOptions = [];

		$leute_col_name = ko_get_leute_col_name(FALSE, TRUE);

		// transfer information from $_GET to $_POST
		foreach (['sel_cols', 'sel_auswahl', 'ids', 'id'] as $tf) {
			if (isset($_GET[$tf]) && !isset($_POST[$tf])) $_POST[$tf] = $_GET[$tf];
		}

		// coolumns
		$xls_cols = array_keys($layout['columns']);

		// rows
		if (substr($_POST["sel_auswahl"], 0, 4) == "alle" && $_SESSION["show"] == "show_all") {
			$mode = substr($_POST["sel_auswahl"], 4);
		} else if (($_POST['sel_auswahl'] == 'markierte' || substr($_POST['sel_auswahl'], 0, 4) == 'alle') && $_SESSION['show'] == 'geburtstagsliste') {
			$mode = 'p';
		} else if (substr($_POST["sel_auswahl"], 0, 4) == "alle") {
			$mode = substr($_POST["sel_auswahl"], 4);
		} else if ($_POST["sel_auswahl"] == "markierte") {
			$mode = "f";
		} else if ($_POST["sel_auswahl"] == "markiertef") {
			$mode = "f";
		} else if ($_POST["sel_auswahl"] == "markierteFam2") {
			$mode = "Fam2";
		} else {
			$mode = 'p';
		}

		if (TRUE === ko_manual_sorting($layout["sort"])) {
			$es = ko_leute_sort($es, $layout["sort"], $layout["sort_order"], TRUE, $forceDatafields = TRUE);
		}

		//Keep list of addresses before removing not needed addresses because of family mergings
		$orig_es = $es;

		ko_get_familien($families);
		$all_datafields = db_select_data("ko_groups_datafields", "WHERE 1=1", "*");

		//Preprocess data if alleFam2
		//Unset famid for people where only one member of their family has been found
		//And unset all members of a family except for husband or wife or the first child
		$fam = [];
		foreach ($es as $pid => $p) {
			if (!$p["famid"]) continue;
			$fam[$p["famid"]][] = $pid;  //Save all pids for each family
		}
		if ($mode == "Fam2") {
			//Find
			foreach ($fam as $famid => $pids) {
				//Find families with only one member in filtered people
				if (sizeof($pids) == 1) {
					foreach ($pids as $pid) $es[$pid]["famid"] = "";  //And unset this famid, so it will be exported as person
				} //Export as family if more than one member has been found
				else if (sizeof($pids) > 1) {
					$famroles = [];
					foreach ($pids as $pid) {
						$famroles[] = $es[$pid]["famfunction"];
					}
					if (in_array("husband", $famroles)) $keep = "husband";
					else if (in_array("wife", $famroles)) $keep = "wife";
					else $keep = "";
					$done = FALSE;
					foreach ($pids as $pid) {
						if (($keep == "" && $done)
							|| ($keep != "" && $es[$pid]["famfunction"] != $keep)
						) {
							unset($es[$pid]);
							$done = TRUE;
						}
					}
				}
			}//foreach(fam as famid => pids)
		}//if(alleFam2)

		//Household export and use parents' firstnames in export
		//  then get parents and include them in the export list
		else if ($mode == 'f' && ko_get_userpref($_SESSION['ses_userid'], 'leute_force_family_firstname') == 1) {
			foreach ($fam as $famid => $pids) {
				$parents = (array)db_select_data('ko_leute', "WHERE `famid` = '$famid' AND `famfunction` IN ('husband', 'wife') AND `deleted` = '0'" . ko_get_leute_hidden_sql());
				foreach ($parents as $parent) {
					if (!in_array($parent['id'], $pids)) {
						$es[$parent['id']] = $parent;
						$orig_es[$parent['id']] = $parent;
						$fam[$famid][] = $parent['id'];
					}
				}
			}
		}

		//Force rectype
		if ($_POST['sel_rectype'] && (in_array($_POST['sel_rectype'], array_keys($RECTYPES)) || $_POST['sel_rectype'] == '_default')) {
			$force_rectype = $_POST['sel_rectype'];
		} else {
			$force_rectype = '';
		}

		//Apply rectype here to be able to add more addresses to $es if needed
		foreach ($es as $pid => $p) {
			//Use address as given in rectype (only apply if not _default was selected, which keeps the default address)
			if ($force_rectype != '_default') {
				$p = $es[$pid] = ko_apply_rectype($p, $force_rectype, $addp);
				if (sizeof($addp) > 0) {
					$new = [];
					foreach ($es as $k => $v) {
						$new[$k] = $v;
						if ($k == $pid) {
							foreach ($addp as $addk => $add) {
								$new[$addk] = $add;
							}
						}
					}
					$es = $new;
					unset($new);
				}
			}
		}

		// create crm entries
		$mapLeuteDatenOptions['crmContactId'] = ko_create_crm_contact_from_post(TRUE, ['leute_ids' => implode(',', $_POST['leute_ids'])]);
		if (in_array($_POST["id"], ['xls_settings', 'excel', 'csv'])) $mapLeuteDatenOptions['kota_process_modes'] = 'xls,list';
		if (in_array($_POST["id"], ['mailmerge', 'etiketten', 'etiketten_settings'])) $mapLeuteDatenOptions['kota_process_modes'] = 'pdf,list';

		$row = 0;
		$data = [];
		foreach ($es as $pid => $p) {
			if (($access['leute']['ALL'] < 1 && $access['leute'][$pid] < 1) || !$pid) continue;

			list($addToExport, $isFam) = ko_leute_process_person_for_export($p, $orig_es, $done_fam, $fam, $families, $xls_cols, $mode);
			if (!$addToExport) {
				unset($es[$p['id']]);
				continue;
			}

			if (!$isFam) {
				unset($cols_no_map);
			} else {
				$cols_no_map = ['MODULEsalutation_formal', 'MODULEsalutation_informal'];
			}

			$es[$pid] = $p;

			$col = 0;
			foreach ($xls_cols as $c) {
				if (!$leute_col_name[$c]) continue;

				if($c == "terms") $p['terms'] = TRUE;

				//Check for columns that don't need any more mapping (may be set in plugin above)
				if (in_array($c, $cols_no_map)) {
					$value = $p[$c];
				} else {
					$value = map_leute_daten($p[$c], $c, $p, $all_datafields, FALSE, $mapLeuteDatenOptions);
				}
				if (is_array($value)) {  //group with datafields, so more than one column has to be added
					foreach ($value as $v) $data[$row][$col++] = ko_unhtml(strip_tags($v));
				} else {
					$data[$row][$col++] = ko_unhtml(strip_tags($value));
				}
			}//foreach(xls_cols as col)
			$row++;
		}//foreach(es as pid => p)

		$_POST = $oldPost;
		$_GET = $oldGet;
	} else {
		if (TRUE === ko_manual_sorting($layout["sort"])) {
			$es = ko_leute_sort($es, $layout["sort"], $layout["sort_order"], TRUE, $forceDatafields = TRUE);
		}

		//TODO: Apply rectype (add setting in preset or form)

		//Loop all addresses
		$data = [];
		foreach ($es as $id => $person) {
			$row = [];
			foreach ($layout["columns"] as $col => $colName) {
				$value = map_leute_daten($person[$col], $col, $person, $all_datafields, $force);
				if (is_array($value)) {
					$row[] = ko_unhtml(strip_tags($value[0]));
				} else {
					$row[] = ko_unhtml(strip_tags($value));
				}
			}//foreach(columns as col)

			$data[] = $row;
		}//foreach(all as id => person)
		unset($all);
	}

	$filename = $ko_path . "download/pdf/" . getLL("leute_filename_pdf") . strftime("%d%m%Y_%H%M%S", time()) . ".pdf";
	ko_export_to_pdf($layout, $data, $filename);

	return $filename;
}//ko_export_leute_as_pdf()



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
		$chart_data = call_user_func("ko_leute_chart_".$type, $where_base);
		if($chart_data == FALSE) continue;
		$html[$type] = $chart_data;
	}

	if($_type) {
		$out = $html[$_type];
	} else {
		//Generate HTML output
		$out = '<h3>'.getLL("leute_chart_title").'</h3>';

		$counter = 0;
		foreach($html as $type => $code) {
			if ($counter % 2 == 0) $out .= '<div class="row">';

			if ($type == "statistics") {
				$counter++;
				$out .= '<div class="col-md-12">';
			} else {
				$out .= '<div class="col-md-6">';
			}

			$out .= '<div class="panel panel-default"><div class="panel-heading"><h4 class="panel-title">'.getLL("leute_chart_title_".$type).'</h4></div><div class="panel-body" name="leute_chart_'.$type.'" id="leute_chart_'.$type.'">'.$code.'</div></div>';
			$out .= '</div>';
			$counter++;
			if ($counter % 2 == 0) $out .= '</div>';
		}
		if ($counter % 2 == 1) $out .= '</div>';
	}

	return $out;
}//ko_leute_chart()




function ko_leute_chart_generic_pie_from_data($label, $value, $name) {
	$data = array_map(function($e1, $e2){return array($e1, $e2);}, array_merge(array(array('label' => getLL('label'), 'type' => 'string')), $label), array_merge(array(getLL('value')), $value));
	array_walk_recursive($data, 'utf8_encode_array');
	$dataJSON = json_encode($data, JSON_NUMERIC_CHECK);

	$html = "
<div class=\"fullscreen-elem\" id=\"leute-stats-{$name}-pie\" style=\"height: 500px;\"></div>
<script>
	google.charts.load('current', {packages:['corechart']});
	google.charts.setOnLoadCallback(function() {
		var data = google.visualization.arrayToDataTable({$dataJSON});

		var options = {
			pieHole: 0.4,
			title: ''
		};

		var chart = new google.visualization.PieChart(document.getElementById('leute-stats-{$name}-pie'));

		var \$chart = $('#leute-stats-{$name}-pie');
		google.visualization.events.addListener(chart, 'ready', function () {
			\$chart.data('google.chart', chart);

			if (\$chart.find('.fullscreen-btn').length == 0) {
				\$chart.append('<a class=\"btn btn-default absolute-br-btn discrete-btn google-charts-download-btn\" data-target=\"#leute-stats-{$name}-line\"><i class=\"fa fa-download\"></i></a>');
				\$chart.append('<a class=\"btn btn-default absolute-tr-btn discrete-btn fullscreen-btn\" data-target=\"#leute-stats-{$name}-pie\"><i class=\"fa fa-arrows\"></i></a>');

				\$chart.on('fullscreen.entering', function() {
					$(this).data('orig-width', $(this).width());
					$(this).data('orig-height', $(this).height());
					$(this).width(screen.width).height(screen.height);
					chart.draw(data, options);
				});
				\$chart.on('fullscreen.exited', function() {
					\$chart.find('.fullscreen-btn.is-fullscreen').removeClass('is-fullscreen');
					$(this).width($(this).data('orig-width')).height($(this).data('orig-height'));
					chart.draw(data, options);
				});
			}

			initGoogleDownloadBtn(\$chart, \$chart.find('.google-charts-download-btn'));
		});

		chart.draw(data, options);
	});
</script>
";
	return $html;
} //ko_leute_chart_generic_pie_from_data()



function ko_leute_chart_generic_bar_from_data($label, $value, $name, $barWidth=NULL, $multipleSeries=FALSE, $showLegend=FALSE, $horizontal=FALSE, $seriesBarDistance=NULL) {
	$value = $multipleSeries ? $value : array($value);

	$data = array();
	for ($i = -1; $i < sizeof($label); $i++) {
		if ($i == -1) {
			$row = array(getLL('key'));
			for ($j = 0; $j < sizeof($value); $j++) {
				$t = end($value[$j]);
				if ($t['meta']) {
					$v = $t['meta'] . ' ';
				} else {
					$v = getLL('value');
				}
				$row[] = $v;
			}
		} else {
			$row = array($label[$i]);
			for ($j = 0; $j < sizeof($value); $j++) {
				if (isset($value[$j][$i]['value'])) {
					$v = $value[$j][$i]['value'];
				} else {
					$v = $value[$j][$i];
				}
				$row[] = $v;
			}
		}
		$data[] = $row;
	}
	array_walk_recursive($data, 'utf8_encode_array');
	$dataJSON = json_encode($data, JSON_NUMERIC_CHECK);

	$legend = $showLegend?'top':'none';
	$width = $showLegend?'60%':'80%';

	$html = '';
	$html .= "
<div class=\"fullscreen-elem\" id=\"leute-stats-{$name}-bar\" style=\"height: 500px;\"></div>
<script>
	google.charts.load('current', {packages:['corechart']});
	google.charts.setOnLoadCallback(function() {
		var data = google.visualization.arrayToDataTable({$dataJSON});

		var options = {
			legend: {position:'{$legend}'},
			hAxis: {slantedText: true},
			title: ''
		};

		var chart = new google.visualization.ColumnChart(document.getElementById('leute-stats-{$name}-bar'));

		var \$chart = $('#leute-stats-{$name}-bar');
		google.visualization.events.addListener(chart, 'ready', function () {
			\$chart.data('google.chart', chart);

			if (\$chart.find('.fullscreen-btn').length == 0) {
				\$chart.append('<a class=\"btn btn-default absolute-br-btn discrete-btn google-charts-download-btn\" data-target=\"#leute-stats-{$name}-line\"><i class=\"fa fa-download\"></i></a>');
				\$chart.append('<a class=\"btn btn-default absolute-tr-btn discrete-btn fullscreen-btn\" data-target=\"#leute-stats-{$name}-bar\"><i class=\"fa fa-arrows\"></i></a>');

				\$chart.on('fullscreen.entering', function() {
					$(this).data('orig-width', $(this).width());
					$(this).data('orig-height', $(this).height());
					$(this).width(screen.width).height(screen.height);
					chart.draw(data, options);
				});
				\$chart.on('fullscreen.exited', function() {
					\$chart.find('.fullscreen-btn.is-fullscreen').removeClass('is-fullscreen');
					$(this).width($(this).data('orig-width')).height($(this).data('orig-height'));
					chart.draw(data, options);
				});
			}

			initGoogleDownloadBtn(\$chart, \$chart.find('.google-charts-download-btn'));
		});

		chart.draw(data, options);
	});
</script>
";

	return $html;
} //ko_leute_chart_generic_bar_from_data()



function ko_leute_chart_generic_stackbar_from_data($labels, $values, $name, $barWidth=NULL, $multipleSeries=FALSE, $showLegend=TRUE, $horizontal=FALSE, $seriesBarDistance=NULL) {

	foreach($labels AS $label) {
		$data[0][] = $label;
	}

	$rows = count($values[0]);
	for ($i=1;$i<=$rows;$i++) {
		foreach ($values AS $key => $value) {
			$data[$i][$key] = ($value[($i-1)] == "" ? 0 : $value[($i-1)]);
			if($key == 0) {
				$data[$i][$key] = "new Date(" . strtotime($data[$i][$key]) . " * 1000)";
			}
		}
	}

	array_walk_recursive($data, 'utf8_encode_array');
	$dataJSON = json_encode($data, JSON_NUMERIC_CHECK);

	$legend = $showLegend?'top':'none';

	$html = "
<div class=\"fullscreen-elem\" id=\"leute-stats-{$name}-bar\" style=\"height: 500px;\"></div>
<script>
	google.charts.load('current', {packages:['corechart']});
	google.charts.setOnLoadCallback(function() {
			var dataJSON = {$dataJSON};
			
			if(dataJSON[0][0] === \"Datum\" || dataJSON[0][0] === \"Date\") {
				for(index = 1; (index + 1) < (dataJSON.length + 1); index++) {
					dataJSON[index][0] = eval(dataJSON[index][0]);
				}
			}
		
		var data = google.visualization.arrayToDataTable(dataJSON);
     
		var options = {
			legend: {position:'{$legend}'},
			hAxis: {slantedText: true, format: 'MM.yyyy'},
			title: '',
	        isStacked: true
		};

		var chart = new google.visualization.ColumnChart(document.getElementById('leute-stats-{$name}-bar'));

		var \$chart = $('#leute-stats-{$name}-bar');
		google.visualization.events.addListener(chart, 'ready', function () {
			\$chart.data('google.chart', chart);

			if (\$chart.find('.fullscreen-btn').length == 0) {
				\$chart.append('<a class=\"btn btn-default absolute-br-btn discrete-btn google-charts-download-btn\" data-target=\"#leute-stats-{$name}-line\"><i class=\"fa fa-download\"></i></a>');
				\$chart.append('<a class=\"btn btn-default absolute-tr-btn discrete-btn fullscreen-btn\" data-target=\"#leute-stats-{$name}-bar\"><i class=\"fa fa-arrows\"></i></a>');

				\$chart.on('fullscreen.entering', function() {
					$(this).data('orig-width', $(this).width());
					$(this).data('orig-height', $(this).height());
					$(this).width(screen.width).height(screen.height);
					chart.draw(data, options);
				});
				\$chart.on('fullscreen.exited', function() {
					\$chart.find('.fullscreen-btn.is-fullscreen').removeClass('is-fullscreen');
					$(this).width($(this).data('orig-width')).height($(this).data('orig-height'));
					chart.draw(data, options);
				});
			}

			initGoogleDownloadBtn(\$chart, \$chart.find('.google-charts-download-btn'));
		});

		chart.draw(data, options);
	});
</script>
";

	return $html;
} //ko_leute_chart_generic_bar_from_data()



function ko_leute_chart_generic_line_from_data($label, $value, $name, $multipleSeries=FALSE, $showLegend=FALSE) {
	if (!$multipleSeries) $value = array($value);

	$data = array();
	for ($i = -1; $i < sizeof($label); $i++) {
		if ($i == -1) {
			$row = array(getLL('key'));
			for ($j = 0; $j < sizeof($value); $j++) {
				$t = end($value[$j]);
				if ($t['meta']) {
					$v = $t['meta'] . ' ';
				} else {
					$v = getLL('value');
				}
				$row[] = $v;
			}
		} else {
			$row = array($label[$i]);
			for ($j = 0; $j < sizeof($value); $j++) {
				if (isset($value[$j][$i]['value'])) {
					$v = $value[$j][$i]['value'];
				} else {
					$v = $value[$j][$i];
				}
				$row[] = $v;
			}
		}
		$data[] = $row;
	}
	array_walk_recursive($data, 'utf8_encode_array');
	$dataJSON = json_encode($data, JSON_NUMERIC_CHECK);

	$legend = $showLegend?'top':'none';
	$width = $showLegend?'60%':'80%';

	$html = "
<div class=\"fullscreen-elem\" id=\"leute-stats-{$name}-line\" style=\"height: 500px;\"></div>
<script>
	google.charts.load('current', {packages:['corechart']});
	google.charts.setOnLoadCallback(function() {
		var data = google.visualization.arrayToDataTable({$dataJSON});

		var options = {
			legend: {position:'{$legend}'},
			hAxis: {slantedText: true},
			title: ''
		};

		var chart = new google.visualization.LineChart(document.getElementById('leute-stats-{$name}-line'));

		var \$chart = $('#leute-stats-{$name}-line');
		google.visualization.events.addListener(chart, 'ready', function () {
			\$chart.data('google.chart', chart);

			if (\$chart.find('.fullscreen-btn').length == 0) {
				\$chart.append('<a class=\"btn btn-default absolute-br-btn discrete-btn google-charts-download-btn\" data-target=\"#leute-stats-{$name}-line\"><i class=\"fa fa-download\"></i></a>');
				\$chart.append('<a class=\"btn btn-default absolute-tr-btn discrete-btn fullscreen-btn\" data-target=\"#leute-stats-{$name}-line\"><i class=\"fa fa-arrows\"></i></a>');

				\$chart.on('fullscreen.entering', function() {
					$(this).data('orig-width', $(this).width());
					$(this).data('orig-height', $(this).height());
					$(this).width(screen.width).height(screen.height);
					chart.draw(data, options);
				});
				\$chart.on('fullscreen.exited', function() {
					\$chart.find('.fullscreen-btn.is-fullscreen').removeClass('is-fullscreen');
					$(this).width($(this).data('orig-width')).height($(this).data('orig-height'));
					chart.draw(data, options);
				});
			}

			initGoogleDownloadBtn(\$chart, \$chart.find('.google-charts-download-btn'));
		});

		chart.draw(data, options);
	});
</script>
";
	return $html;
} //ko_leute_chart_generic_line_from_data()



function ko_leute_chart_generic_timeline_from_data($days, $columns, $column_definitions, $name, $multipleSeries=FALSE, $showLegend=FALSE) {

	$legend = $showLegend?'top':'none';

	$html = "
<div class=\"fullscreen-elem\" id=\"leute-stats-{$name}-line\" style=\"height: 500px;\"></div>
<script>
	google.charts.load('current', {packages:['corechart', 'timeline']});
	google.charts.setOnLoadCallback(function() {
        var data = new google.visualization.DataTable(); ";

	foreach($column_definitions AS $column_definition) {
		$html.= $column_definition ."\n";
	}

	$html.= "data.addRows([";

	$rows = [];
	foreach($days AS $key => $day) {
		$rows[$key][0] = "new Date(".$day." * 1000)";
		foreach($columns AS $column_key => $column) {
			$rows[$key][$column_key+1] = $column[$key];
		}
	}

	foreach($rows AS $row) {
		$html.= "[" . implode(",", $row) . "],\n";
	}

	$html.="]);

		var options = {
			legend: {position:'{$legend}'},
			hAxis: {
				title: '',
				slantedText: true,
	            format: 'dd.MM.yyyy',
		    },
            vAxis: {
				minValue: 0
            },
            explorer: {
            	axis: 'horizontal',
            	actions: ['dragToPan', 'scrollToZoom', 'rightClickToReset'], 
            	keepInBounds: true,
            	maxZoomIn: 4.0,
            	maxZoomOut: 1
            },
		  	pointSize: 3,
		};

		var chart = new google.visualization.LineChart(document.getElementById('leute-stats-{$name}-line'));
        
		var \$chart = $('#leute-stats-{$name}-line');
		google.visualization.events.addListener(chart, 'ready', function () {
			\$chart.data('google.chart', chart);

			if (\$chart.find('.fullscreen-btn').length == 0) {
				\$chart.append('<a class=\"btn btn-default absolute-br-btn discrete-btn google-charts-download-btn\" data-target=\"#leute-stats-{$name}-line\"><i class=\"fa fa-download\"></i></a>');
				\$chart.append('<a class=\"btn btn-default absolute-tr-btn discrete-btn fullscreen-btn\" data-target=\"#leute-stats-{$name}-line\"><i class=\"fa fa-arrows\"></i></a>');

				\$chart.on('fullscreen.entering', function() {
					$(this).data('orig-width', $(this).width());
					$(this).data('orig-height', $(this).height());
					$(this).width(screen.width).height(screen.height);
					chart.draw(data, options);
				});
				\$chart.on('fullscreen.exited', function() {
					\$chart.find('.fullscreen-btn.is-fullscreen').removeClass('is-fullscreen');
					$(this).width($(this).data('orig-width')).height($(this).data('orig-height'));
					chart.draw(data, options);
				});
			}

			initGoogleDownloadBtn(\$chart, \$chart.find('.google-charts-download-btn'));
		});

		chart.draw(data, options);
	});
</script>
";
	return $html;
}


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
	$gsel = '<select class="input-sm form-control" name="sel_leute_chart_subgroups_gid" onchange="sendReq(\''.$ko_path.'leute/inc/ajax.php\', \'action,gid,sesid\', \'leutechartsubgroups,\'+this.options[this.selectedIndex].value+\','.session_id().'\', do_element);">';
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

	$html = '';


	//Draw pie chart if a group id is given
	if($_SESSION["leute_chart_subgroups_gid"]) {
		list($gid, $rid) = explode(':', $_SESSION['leute_chart_subgroups_gid']);
		//Get all children groups
		$groups = db_select_data("ko_groups", "WHERE `pid` = '$gid' ".ko_get_groups_zwhere(), "*", "ORDER BY `name` ASC");

		$value1 = $value2 = $label = array();
		foreach($groups as $id => $group) {

			$value1[] = array(
				'value' => db_get_count("ko_leute", "id", $where_base." AND `groups` REGEXP 'g$id".($rid != '' ? '[gr0-9:]*:r'.$rid : '')."'"),
				'meta' => getLL('kg_chart_title_members'),
			);
			$value2[] = array(
				'value' => $group['maxcount'] ? $group['maxcount'] : 0,
				'meta' => getLL('kota_ko_groups_maxcount'),
			);
			$label[] = $group['description'] ? $group['description'] : $group['name'];
		}

		$html = ko_leute_chart_generic_bar_from_data($label, array($value1, $value2), 'subgroups', NULL, TRUE, TRUE);
	}//if(_SESSION[leute_chart_roles_gid])

	return getLL("leute_chart_subgroups_select_group").$gsel.$html;
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
	$gsel = '<select class="input-sm form-control" name="sel_leute_chart_roles_gid" size="0" onchange="sendReq(\''.$ko_path.'leute/inc/ajax.php\', \'action,gid,sesid\', \'leutechartroles,\'+this.options[this.selectedIndex].value+\','.session_id().'\', do_element);">';
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

	$html = '';


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
			$html = ko_leute_chart_generic_bar_from_data($label, $value, 'roles', NULL, FALSE, FALSE, TRUE);
		}
	}//if(_SESSION[leute_chart_roles_gid])

	return getLL("leute_chart_roles_select_group").$gsel.$html;
}//ko_leute_chart_roles()



/**
 * Display data from ko_statistics
 */
function ko_leute_chart_statistics() {
	global $ko_path;

	$where = " WHERE user_id = -1 OR user_id = " . $_SESSION['ses_userid'] . " GROUP BY filter_id ASC";
	$filter_presets = db_select_data('ko_statistics', $where, "title, filter_id, user_id");

	$where = " WHERE filter_id = 0 GROUP BY title ASC";
	$all_other_stats = db_select_data('ko_statistics', $where, "title");



	$select = '<select class="input-sm form-control" name="sel_leute_chart_statistics" onchange="sendReq(\''.$ko_path.'leute/inc/ajax.php\', \'action,stats,sesid\', \'leutechartstatistics,\'+this.options[this.selectedIndex].value+\','.session_id().'\', do_element);">';
	$select .= '<option value=""></option>';

	if (count($filter_presets) > 0) {
		$select .= '<option value="" disabled>-- ' . getLL('leute_chart_stats_select_filters') . ' --</option>';
	}


	foreach($filter_presets as $filter_preset) {
		$sel = $filter_preset["filter_id"] == $_SESSION["leute_chart_statistics"] ? 'selected = "selected"' : '';
		if (empty($userpref_filter)) {
			$select .= '<option value="'.$filter_preset['filter_id'].'" ' . $sel . ' >('.$filter_preset['title'].')</option>';
		} else {
			$select .= '<option value="'.$filter_preset['filter_id'].'" ' . $sel . ' >'.$filter_preset['title'] . ($filter_preset['user_id'] == -1 ? " [G]" : "").'</option>';
		}
	}

	if (count($all_other_stats) > 0) {
		$select .= '<option value="" disabled>-- ' . getLL('leute_chart_stats_select_other') . ' --</option>';
	}
	
	foreach($all_other_stats as $all_other_stat) {
		$sel = $all_other_stat["title"] == $_SESSION["leute_chart_statistics"] ? 'selected = "selected"' : '';
		$select .= '<option value="'.$all_other_stat['title'].'" ' . $sel . ' >'.getLL("leute_chart_stats_select_other_" . $all_other_stat['title']).'</option>';
	}

	$select .= '</select>';

	$chart = '';

	if ($_SESSION["leute_chart_statistics"]) {
		if (is_numeric($_SESSION["leute_chart_statistics"])) {
			$where = "WHERE filter_id = '" . format_userinput($_SESSION['leute_chart_statistics'], 'int') ."'";
			$stats_data = db_select_data("ko_statistics", $where, '*', 'ORDER BY date ASC');
		} else {
			$where = "WHERE title = '" . format_userinput($_SESSION['leute_chart_statistics'], 'alphanum') ."'";
			$stats_data = db_select_data("ko_statistics", $where, '*', 'ORDER BY date ASC');
		}

		$column_definitions = [
			'0' => "data.addColumn('date', 'Datum');",
		];

		$lastfilter = 0;
		$showLegend = FALSE;
		foreach($stats_data AS $key => $stat_data) {
			$days[] = strtotime($stat_data['date']);

			if($_SESSION['leute_chart_statistics'] == "confessions") {
				$results = json_decode($stat_data['result']);
				foreach ($results AS $column_key => $result) {
					if (empty($result->title)) {
						$result->title = "unknown";
						$label = getLL('unknown');
					} else {
						$label = (getLL('kota_ko_leute_confession_' . $result->title) ? getLL('kota_ko_leute_confession_' . $result->title) : $result->title);
					}
					$temp_columns[$result->title][strtotime($stat_data['date'])] = $result->total;
					$temp_column_definitions[$result->title] = "data.addColumn('number', '" . $label . "');";
				}
			} else if ($_SESSION['leute_chart_statistics'] == "terms") {
				$results = json_decode($stat_data['result']);
				$allTerms = ko_taxonomy_get_terms();
				foreach($results AS $term_id => $total) {
					if (empty($allTerms[$term_id]['name'])) {
						$label = getLL('unknown');
					} else {
						$label = $allTerms[$term_id]['name'];
					}
					$temp_columns[$term_id][strtotime($stat_data['date'])] = $total;
					$temp_column_definitions[$term_id] = "data.addColumn('number', '" . $label . "');";
				}
			} else {
				// just one line
				$columns[0][] = $stat_data['result'];
				$label = ($stat_data['filter_id'] != 0 ? getLL('people') : getLL('value'));
				$column_definitions['1'] = "data.addColumn('number', '".$label."');";

				if(is_numeric($_SESSION['leute_chart_statistics'])) {
					$column_definitions['2'] = "data.addColumn({'type': 'string', 'role': 'style'});";
					if(!empty($lastfilter) && $lastfilter != $stat_data['filter_hash']) {
						$columns[1][] = '\'point {dent: 0.1; size: 8; shape-type: triangle; fill-color: #dc3545; }\'';
						$showLegend = TRUE;
					} else {
						$columns[1][] = 'null';
					}
					$lastfilter = $stat_data['filter_hash'];
				}

			}
		}

		if(isset($temp_columns)) {
			$column_id = 0;
			foreach($temp_column_definitions AS $temp_name => $temp_column_definition) {
				foreach($days AS $day) {
					$columns[$column_id][] = (!isset($temp_columns[$temp_name][$day]) ? 0 : $temp_columns[$temp_name][$day]);
					$column_definitions[$column_id+1] = $temp_column_definitions[$temp_name];
				}
				$column_id++;
			}
		}

		$chart = ko_leute_chart_generic_timeline_from_data($days, $columns, $column_definitions, 'statistics');
	}

	if($showLegend) {
		$icon = '<svg xmlns="http://www.w3.org/2000/svg" version="1.0" width="16" height="16">
  <polygon points="1,15 15,15 8,1, 1,15" fill="#dc3545" stroke="none" />
</svg>';
		$legend = '<p>'.$icon.'&nbsp;'.getLL('leute_chart_stats_legend').'</p>';
	} else {
		$legend = '';
	}

	return $select.$chart.$legend;
}



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

	return ko_leute_chart_generic_pie_from_data($label, $value, 'age');

}//ko_leute_chart_age_pie()



/**
 * Chart function for addresses
 * Pie chart showing age distribution
 */
function ko_leute_chart_age_bar($where_base) {
	global $ko_path;

	$value = $label = array();
	/*
	//No birthday given
	$label[] = getLL("leute_chart_none");
	$where = $where_base." AND `geburtsdatum` = '0000-00-00'";
	$value[] = db_get_count("ko_leute", "id", $where);
	*/

	$query = "SELECT (DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(geburtsdatum, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(geburtsdatum, '00-%m-%d'))) AS age, COUNT(`id`) AS `num` FROM `ko_leute` WHERE `geburtsdatum` != '0000-00-00' $where_base GROUP BY `age` ORDER BY `age` ASC";
	$result = mysqli_query(db_get_link(), $query);
	$data = array(); $min = 100; $max = 0;
	while($row = mysqli_fetch_assoc($result)) {
		$data[$row["age"]] = $row["num"];
		$min = min($min, $row["age"]);
		$max = max($max, $row["age"]);
	}
	for($i = $min; $i<= $max; $i++) {
		$value[] = (int)$data[$i];
		$label[] = $i;
	}

	$html = ko_leute_chart_generic_bar_from_data($label, $value, 'age', "3px");

	return $html;
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

	return ko_leute_chart_generic_pie_from_data($label, $value, 'birthday');
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
		return ko_leute_chart_generic_pie_from_data($label, $value, $col);
	}
}//ko_leute_chart_generic_pie_enum()


function ko_leute_chart_sex($where_base) {
	return ko_leute_chart_generic_pie('ko_leute', $where_base, 'geschlecht');
}


function ko_leute_chart_famfunction($where_base) {
	$where_base .= " AND `famid` != '' ";
	return ko_leute_chart_generic_pie('ko_leute', $where_base, 'famfunction');
}

function ko_leute_chart_civilstatus($where_base) {
	return ko_leute_chart_generic_pie('ko_leute', $where_base, 'zivilstand');
}

/**
 * wrapper for ko_leute_chart_pfarrbook
 *
 * @param string $where_base pass forward to ko_leute_chart_pfarrbook
 * @return string html from ko_leute_chart_pfarrbook
 */
function ko_leute_chart_rodel($where_base) {
	return ko_leute_chart_pfarrbook($where_base, "rodel");
}


/**
 * wrapper for ko_leute_chart_pfarrbook
 *
 * @param string $where_base pass forward to ko_leute_chart_pfarrbook
 * @return string html from ko_leute_chart_pfarrbook
 */
function ko_leute_chart_taufbuch($where_base) {
	return ko_leute_chart_pfarrbook($where_base, "taufbuch");
}


/**
 * Create charts for rodel or taufschein
 *
 * @param string $where_base pass currently unused
 * @return string html
 */
function ko_leute_chart_pfarrbook($where_base, $pfarrbook_type) {
	global $KOTA;
	$main_table = "ko_" . $pfarrbook_type;
	ko_include_kota([$main_table]);
	if(array_search($pfarrbook_type, array_column($GLOBALS['PLUGINS'],"name")) == FALSE) {
		return FALSE;
	}

	$pfarrbook_types = [
		1 => "christening",
		2 => "confirmation",
		3 => "wedding",
		4 => "abdication",
		5 => "consecration",
	];

	if($pfarrbook_type == "taufbuch") {
		$pfarrbook_types[2] = "firming";
		$pfarrbook_types[5] = "communion";
	}

	$pfarrbooks = [];
	foreach($pfarrbook_types AS $key => $name) {
		if ($key == 5 && $pfarrbook_type != "taufbuch" && !ko_get_setting("rodel_activate_type_einsegnung")) {
			continue;
		}

		$db_field = $name . "_date";
		if($key == 3) $db_field = "church_wedding_date";

		$pfarrbooks[$name] = db_select_data(
			$main_table,
			"WHERE type = " . $key,
			"type, count(id) AS total, DATE_FORMAT(" . $db_field . ", '%Y-%m-01')  AS id",
			"GROUP BY YEAR(" . $db_field . "), MONTH(" . $db_field . ") DESC;"
		);
	}

	$date['year'] = date("Y", time()) - 1;
	$date['month'] = date("m", time());
	$row_template = [];
	for($i=1; $i<=13;$i++) {
		$row_template[] = ($date['year'] . "-" . zerofill($date['month'],2) . "-01");

		if ($date['month'] == 12) {
			$date['month'] = 1;
			$date['year']++;
		} else {
			$date['month']++;
		}
	}

	$label[] = "Datum";
	$column = 0;

	$values[$column] = $row_template;
	$column = 1;
	foreach($pfarrbooks AS $type =>  $pfarrbook) {
		$label[] = getLL("kota_ko_" . $pfarrbook_type . "_type_" . array_search($type, $pfarrbook_types));
		foreach($row_template AS $date) {
			$values[$column][] = $pfarrbook[$date]['total'];
		}
		$column++;
	}

	$html = ko_leute_chart_generic_stackbar_from_data($label, $values, $pfarrbook_type, "3px");
	return $html;
}



/**
 * Generic stats function for pie chart showing the first $max entries for the given $col
 */
function ko_leute_chart_generic_pie($table, $where_base, $col, $max=12) {
	global $ko_path;

	$value = $label = array();
	$query = "SELECT `$col`, COUNT(`id`) AS num FROM `$table` WHERE `$col` != '' $where_base GROUP BY `$col` ORDER BY `num` DESC";
	$result = mysqli_query(db_get_link(), $query);
	$num = 0; $div = 0;
	while($row = mysqli_fetch_assoc($result)) {
		$num++;
		if($num > $max) {
			$div += $row['num'];
			continue;
		}
		$value[] = $row['num'];
		$ll = getLL('kota_'.$table.'_'.$col.'_'.$row[$col]);
    $label[] = $ll ? $ll : $row[$col];
	}
	if($div) {
		$value[] = $div;
		$label[] = getLL("leute_chart_misc");
	}

	return ko_leute_chart_generic_pie_from_data($label, $value, $col);
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

	return ko_leute_chart_generic_line_from_data($label, $value, 'lastchange');
}



function ko_leute_export_xls_settings() {
	global $smarty, $xls_cols, $es;

	//build form
	$gc = 0;
	$rowcounter = 0;

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

	ko_leute_export_show_warning($smarty, $es);

	//display the form
	$smarty->assign('tpl_titel', getLL('leute_export_xls_settings_title'));
	$smarty->assign('tpl_submit_value', getLL('leute_export_xls_settings_export'));
	$smarty->assign('tpl_action', 'leute_submit_export_xls');
	$cancel = ko_get_userpref($_SESSION['ses_userid'], 'default_view_leute');
	if(!$cancel) $cancel = 'show_all';
	$smarty->assign('tpl_cancel', $cancel);
	$crmContactGroup = ko_get_crm_contact_form_group(array('leute_ids'), array('type' => 'letter'));
	$frmgroup[sizeof($frmgroup)] = $crmContactGroup[0];
	$smarty->assign('tpl_groups', $frmgroup);
	$smarty->display('ko_formular.tpl');
}


/**
 * Check if a warning in addresses-export should be displayed to the user
 *
 * @param Smarty &$smarty template object
 * @param array $es list of adresses to be exported
 * @return bool
 */
function ko_leute_export_show_warning(&$smarty, $es) {
	foreach($es AS $address) {
		if ($address['hidden'] == 1 || $address['deleted'] == 1) {
			$smarty->assign('tpl_export_warning', getLL('leute_export_warning_hiddendeleted'));
			return TRUE;
		}
	}

	return FALSE;
}


function ko_leute_export_details_settings() {
	global $smarty, $ko_path, $es, $access;

	ko_get_access('admin');

	$exportIds = $_SESSION['export_ids'];

	//build form
	$gc = 0;
	$rowcounter = 0;

	$values = $descs = array();
	$exports = ko_leute_get_detail_exports();
	$allTypes = ko_array_ll(array_unique(ko_array_column($exports, 'type')), 'leute_export_details_type_');
	sort($allTypes);
	foreach ($allTypes as $type) {
		$typeExports = array_filter($exports, function($e)use($type){return getLL("leute_export_details_type_{$e['type']}") == $type;});
		usort($typeExports, function($a, $b){return strcmp($a["desc"], $b["desc"]);});
		$values[] = '_DISABLED_';
		$descs[] = $type;
		foreach ($typeExports as $export) {
			$values[] = $export['name'];
			$descs[] = "&nbsp;&nbsp;{$export['desc']}";
		}
	}
	$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('leute_export_details_settings_layout'),
		'type' => 'select',
		'name' => 'sel_detail_export',
		'values' => $values,
		'descs' => $descs,
	);
	if($access['admin']['MAX'] > 1) {
		$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('leute_settings_detailed_person_export'),
			'type' => 'label',
			'value' => '<a href="'.$ko_path.'admin/index.php?action=list_detailed_person_exports">'.getLL('leute_settings_detailed_person_export_text').'</a>',
		);
	}

	ko_leute_export_show_warning($smarty, $es);

	//display the form
	$smarty->assign('tpl_titel', getLL('leute_export_details_settings_title'));
	$smarty->assign('tpl_submit_value', getLL('leute_export_details_settings_export'));
	$smarty->assign('tpl_action', 'export_details');
	$cancel = ko_get_userpref($_SESSION['ses_userid'], 'default_view_leute');
	if(!$cancel) $cancel = 'show_all';
	$smarty->assign('tpl_cancel', $cancel);
	$crmContactGroup = ko_get_crm_contact_form_group(array('leute_ids'));
	$frmgroup[sizeof($frmgroup)] = $crmContactGroup[0];
	$smarty->assign('tpl_groups', $frmgroup);
	$smarty->display('ko_formular.tpl');
}//ko_leute_export_details_settings()




function ko_leute_settings() {
	global $smarty, $ko_path;
	global $access, $MODULES;
	global $LEUTE_NO_FAMILY;

	if($access['leute']['MAX'] < 1 || $_SESSION['ses_userid'] == ko_get_guest_id()) return false;

	//build form
	$gc = 0;
	$rowcounter = 0;
	$frmgroup[$gc]['tab'] = true;
	$frmgroup[$gc]['titel'] = getLL('settings_title_user');

	//Layout and limit settings
	$frmgroup[$gc]['row'][$rowcounter++]['inputs'][0] = array('desc' => getLL('leute_settings_default_view'),
			'type' => 'select',
			'name' => 'sel_leute',
			'values' => array('show_all', 'geburtstagsliste', 'list_kg'),
			'descs' => array(getLL('submenu_leute_show_all'), getLL('submenu_leute_geburtstagsliste'), getLL('submenu_leute_list_kg')),
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
		$frmgroup[$gc]['row'][$rowcounter++]['inputs'][0] = array('desc' => getLL('admin_settings_options_leute_force_family_firstname'),
			'type' => 'select',
			'name' => 'sel_leute_force_family_firstname',
			'params' => 'size="0"',
			'values' => array(0, 1, 2),
			'descs' => array(getLL('admin_settings_options_leute_force_family_firstname_0'), getLL('admin_settings_options_leute_force_family_firstname_1'), getLL('admin_settings_options_leute_force_family_firstname_2')),
			'value' => ko_get_userpref($_SESSION['ses_userid'], 'leute_force_family_firstname'),
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

	$value = ko_get_userpref($_SESSION['ses_userid'], 'leute_list_persons_not_overlay');
	$avalues = explode(',', $value);
	$values = array('version', 'template', 'maps', 'whatsappclicktochat', 'clipboard', 'delete', 'qrcode', 'togglehidden', 'donations');
	if (ko_module_installed('crm')) $values[] = 'crm';
	$descs = array();
	foreach ($values as $v) {
		$descs[] = getLL('leute_settings_list_persons_not_overlay_' . $v);
	}
	foreach ($avalues as $k => $v) {
		$found = FALSE;
		foreach ($values as $kk => $vv) {
			if ($v == $vv) {
				$adescs[] = $descs[$kk];
				$found = TRUE;
			}
		}
		if (!$found) unset($avalues[$k]);
	}

	$frmgroup[$gc]['row'][$rowcounter++]['inputs'][($LEUTE_NO_FAMILY ? 1 : 0)] = array('desc' => getLL('leute_settings_list_persons_not_overlay'),
		'type' => 'checkboxes',
		'name' => 'sel_list_persons_not_overlay',
		'values' => $values,
		'avalues' => $avalues,
		'avalue' => $value,
		'adescs' => $adescs,
		'descs' => $descs,
		'size' => '7',
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
		$frmgroup[$gc]['tab'] = true;

		if($access['leute']['ALL'] > 2) {

			//Allow deletion of multiple persons
			$value = ko_get_setting('leute_multiple_delete');
			$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('leute_settings_multiple_delete'),
				'type' => 'switch',
				'name' => 'chk_multi_delete',
				'label_0' => getLL('no'),
				'label_1' => getLL('yes'),
				'value' => $value == '' ? 0 : $value,
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

			$value = ko_get_setting('leute_delete_revision_address');
			$frmgroup[$gc]['row'][$rowcounter++]['inputs'][0] = array('desc' => getLL('leute_settings_delete_revision_address'),
					'type' => 'switch',
					'name' => 'chk_delete_revision_address',
					'label_0' => getLL('no'),
					'label_1' => getLL('yes'),
					'value' => $value == '' ? 0 : $value,
					);

			$value = ko_get_setting('leute_assign_global_notification');
			$frmgroup[$gc]['row'][$rowcounter++]['inputs'][0] = array('desc' => getLL('leute_settings_assign_global_notification'),
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
			$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('leute_settings_no_delete_columns'),
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

			$value = ko_get_setting('candidate_adults_min_age');
			$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('leute_settings_candidate_adults_min_age'),
				'type' => 'text',
				'html_type' => 'number',
				'name' => 'txt_candidate_adults_min_age',
				'value' => $value ? $value : 18,
			);

			$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array_merge(array(
				// something missing?
			), kota_get_mandatory_fields_choices_for_sel('ko_leute'));


			//Allow moderation for all users with access > 1
			$value = ko_get_setting('leute_allow_moderation');
			$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('leute_settings_allow_moderation'),
					'type' => 'switch',
					'name' => 'chk_allow_moderation',
					'label_0' => getLL('no'),
					'label_1' => getLL('yes'),
					'value' => $value == '' ? 0 : $value,
					);

			//Allow import
			$value = ko_get_setting('leute_allow_import');
			$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('leute_settings_allow_import'),
				'type' => 'switch',
				'name' => 'chk_allow_import',
				'label_0' => getLL('no'),
				'label_1' => getLL('yes'),
				'value' => $value == '' ? 0 : $value,
			);

			//Mutation front module
			$value = ko_get_setting('leute_disable_aa_fm');
			$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('leute_settings_disable_aa_fm'),
				'type' => 'switch',
				'name' => 'chk_disable_aa_fm',
				'label_0' => getLL('no'),
				'label_1' => getLL('yes'),
				'value' => $value == '' ? 0 : $value,
			);

			$value = ko_get_setting('leute_information_lock');
			$frmgroup[$gc]['row'][$rowcounter++]['inputs'][0] = [
				'desc' => getLL('leute_settings_information_lock'),
				'type' => 'switch',
				'name' => 'chk_leute_information_lock',
				'label_0' => getLL('no'),
				'label_1' => getLL('yes'),
				'value' => $value == '' ? 0 : $value,
			];
		}


		//Links to other settings (in admin module)
		if($admin_max > 1) {
			$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('leute_settings_labels'),
					'type' => 'label',
					'value' => '<a href="'.$ko_path.'admin/index.php?action=list_labels">'.getLL('leute_settings_labels_text').'</a>',
					);
			$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('leute_settings_leute_pdf'),
					'type' => 'label',
					'value' => '<a href="'.$ko_path.'admin/index.php?action=set_leute_pdf">'.getLL('leute_settings_leute_pdf_text').'</a>',
					);
			$frmgroup[$gc]['row'][$rowcounter++]['inputs'][0] = array('desc' => getLL('leute_settings_detailed_person_export'),
				'type' => 'label',
				'value' => '<a href="'.$ko_path.'admin/index.php?action=list_detailed_person_exports">'.getLL('leute_settings_detailed_person_export_text').'</a>',
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

	//Create PHPWord Object
	$phpWord = new \PhpOffice\PhpWord\PhpWord();
	\PhpOffice\PhpWord\Settings::setOutputEscapingEnabled(true);
	$document = $phpWord->loadTemplate($file);

	$map = ko_word_person_array($person);
	foreach($map as $key => $value) {
		$value = utf8_encode($value);
		$document->setValue($key, $value);
	}

	//Output to file
	$filename = format_userinput($person['vorname'].$person['nachname'], 'alphanumlist').'.docx';
	$document->saveAs($BASE_PATH . 'download/word/' . $filename);

	return $filename;
}//ko_word_docx()

function my_kota_pre_ko_leute_crm_entries_html(&$value, $data) {
	global $access;

	if ($data['id'] == ko_get_logged_in_id($_SESSION['ses_userid']) && $access['crm']['MAX'] < 2) return false;
	if ($data['id'] != ko_get_logged_in_id($_SESSION['ses_userid']) && $access['crm']['MAX'] < 5) return false;

	$value = leute_get_crm_entries_html($data['id']);
}

function leute_get_crm_entries_html($id, $rows_only=FALSE) {
	global $KOTA, $smarty, $access;
	if (!isset($access['crm'])) ko_get_access('crm');
	ko_include_kota(array('ko_crm_contacts'));

	$es = db_query("SELECT c.* FROM ko_crm_contacts c JOIN ko_crm_mapping m ON (c.id = m.contact_id) WHERE m.leute_id = '" . $id . "' ORDER BY c.`date` DESC");

	//Check for access, otherwise don't show
	foreach($es as $cid => $contact) {
		if(!ko_get_crm_contacts_access($contact, 'view')) {
			unset($es[$cid]);
		}
	}

	foreach ($KOTA['ko_crm_contacts']['_listview'] as $k => $kotaEntry) {
		if ($kotaEntry['name'] == 'leute_id') unset($KOTA['ko_crm_contacts']['_listview'][$k]);
	}

	$admingroups = array();
	$crusers = array('' => getLL('all'));
	ko_get_login($_SESSION['ses_userid'], $l);
	$crusers[$_SESSION['ses_userid']] = $l['login'];
	$ags_ = ko_get_admingroups($_SESSION['ses_userid']);
	foreach ($ags_ as $k => $ag_) {
		$admingroups['a'.$k.'g'] = $ag_['name'];
	}
	foreach ($es as $k => $esEntry) {
		$orig = $esEntry;
		kota_process_data('ko_crm_contacts', $esEntry, 'list');
		$hiddenValues = array();
		$ags_ = ko_get_admingroups($orig['cruser']);
		$ags = array();
		foreach ($ags_ as $kk => $ag_) {
			$ags[] = 'a'.$kk.'g';
			$admingroups['a'.$kk.'g'] = '['.$ag_['name'].']';
		}
		$hiddenValues['admingroups'] = implode(',', $ags);
		$ac = ko_get_crm_contacts_access($contact, 'edit');
		$es[$k] = array('value' => $orig, 'processed_value' => $esEntry, 'edit' => $ac, 'delete' => $ac, 'hidden_values' => $hiddenValues);
		$projects[$orig['project_id']] = $esEntry['project_id'];
		$status[$orig['status_id']] = $esEntry['status_id'];
		$crusers[$orig['cruser']] = $esEntry['cruser'];
	}


	$crusers = $crusers + $admingroups;
	ko_get_crm_projects($projectsEs);
	$projects = array();
	foreach ($projectsEs as $p) {
		if ($access['crm'][$p['id']] < 1 && $access['crm']['ALL'] < 1) continue;
		$projects[$p['id']] = $p['title'];
	}
	ko_get_crm_status($statusEs);
	$status = array();
	foreach ($statusEs as $s) {
		$status[$s['id']] = $s['title'];
	}

	$header = array();
	foreach ($KOTA['ko_crm_contacts']['_listview'] as $kotaEntry) {
		$header[$kotaEntry['name']] = getLL('kota_listview_ko_crm_contacts_' . $kotaEntry['name']);
	}

	ko_get_person_by_id($id, $person);

	$smarty->assign('data', array('header' => $header, 'data' => $es));
	$smarty->assign('person', $person);
	$smarty->assign('projects', $projects);
	$smarty->assign('status', $status);
	$smarty->assign('crusers', $crusers);
	$smarty->assign('parent_row_id', $id);
	$smarty->assign('table', 'leute');
	$smarty->assign('rows_only', $rows_only);

	return $smarty->fetch('ko_leute_crm_entries.tpl');
}


function leute_mailmerge_pdf_layout_default($general, $data, $filename) {
	global $ko_path;

	define('FPDF_FONTPATH',$ko_path.'fpdf/schriften/');
	require_once($ko_path.'fpdf/PDF_HTML.php');


	$pdf = new PDF_HTML('P', 'mm', array(210, 297));
	$pdf->AddFont('arial','','arial.php');
	$pdf->AddFont('arial','B','arialb.php');
	$pdf->fontlist = array('arial');

	if (substr($general['sender'], 0, 6) == 'church') {
		$general['return_address'] = trim(ko_get_setting('info_zip')) . ' ' . trim(ko_get_setting('info_city')) . ', ' . trim(ko_get_setting('info_name')) . ', ' . trim(ko_get_setting('info_address'));
	} else if(substr($general['sender'], 0, 4) == 'user') {
		$p = ko_get_logged_in_person();
		// TODO: what data should be used here
		$general['return_address'] = trim($p['plz']) . ' ' . trim($p['ort']) . ', ' . trim($p['vorname']) . ' ' . trim($p['nachname']) . ', ' . trim($p['adresse']);
	}

	foreach($data as $entry) {
		leute_mailmerge_pdf_layout_default_add_address_to_pdf($pdf, $general, $entry);
	}
	$pdf->Output($filename, false);

	return $filename;
}

function leute_mailmerge_pdf_layout_default_add_address_to_pdf(PDF_HTML &$pdf, $general, $entry, $showLogo=TRUE) {
	global $ko_path;

	// layout
	$fontSizeHeader = 9;
	$fontSizeNormal = 10;
	$fontSizeTitle = 13;
	$fontSizePP = 10;

	$lineHeightNormal = 1.3;

	$marginTop = 13;
	$marginLeft = 13;
	$marginRight = 17;
	$marginBottom = 10.5;

	$pageWidth = 210;
	$availableWidth = $pageWidth - $marginLeft - $marginRight;
	$pageHeight = 297;

	$logoPath = $ko_path . 'my_images/pdf_logo.png';
	$logoHeight = 9;
	$logoSize = getimagesize($logoPath);
	$logoWidth = $logoSize[0] / $logoSize[1] * $logoHeight;

	$signatureHeight = 12;
	$signaturePath = $general['signature_file'];

	$addressLeft = 110;
	$addressTop = 41;

	$rectWidth = 4;
	$rectHeight = $pageHeight - $marginTop - $logoHeight - 5 - $marginBottom - 1;

	$leftD = $marginLeft;// + $rectWidth + 2;

	$weirdOffset = 1;


	// data
	$returnAddress = $general['return_address'];
	$sender = $general['sender'];

	$title = $entry['_subject_'];


	$personData = $entry;

	// address evaluation
	$address = '';
	if ($personData['anrede'] == 'Familie') {
		$address .= 'Familie' . "\n";
		$address .= $personData['nachname'] . "\n";
	}
	else if ($personData['firm']) {
		$address .= $personData['firm'] . "\n";
		if ($personData['nachname']) {
			if ($personData['vorname']) {
				$address .= $personData['vorname'] . ' ';
			}
			else if ($personData['anrede']) {
				$address .= $personData['anrede'] . ' ';
			}
			$address .= $personData['nachname'] . "\n";
		}
	}
	else {
		if ($personData['anrede']) {
			$address .= $personData['anrede'] . "\n";
		}
		if ($personData['nachname']) {
			if ($personData['vorname']) {
				$address .= $personData['vorname'] . ' ';
			}
			$address .= $personData['nachname'] . "\n";
		}
	}
	if ($personData['adresse']) {
		$address .= $personData['adresse'] . "\n";
	}
	if ($personData['adresse_zusatz']) {
		$address .= $personData['adresse_zusatz'] . "\n";
	}
	if ($personData['plz'] && $personData['ort']) {
		$address .= $personData['plz'] . ' ' . $personData['ort'] . "\n";
	}
	if (($land = $personData['land']) && !in_array(strtolower($land), array('schweiz', 'ch', 'switzerland', 'suisse', 'svizzera'))) { // check if country is set and is not ch
		$address .= $personData['land'] . "\n";
	}

	$salutation = $personData['_opening_'];


	/*  --- CREATE PAGE ---  */

	$pdf->AddPage();
	$pdf->SetDrawColor(0);
	$pdf->SetLineWidth(0.1);

	$left = $marginLeft;
	$top = $marginTop;


	// logo
	if($showLogo) {
		$left = $availableWidth - $logoWidth + $marginLeft;
		$top = $marginTop;
		$pdf->Image($logoPath, $left, $top, $logoWidth);
	}


	// bar
	/*$top = $marginTop + $logoHeight + 5;
	$left = $marginLeft;
	$pdf->SetFillColor(70, 135, 206);
	$pdf->Rect($left, $top, $rectWidth, $rectHeight, "F");*/


	// addressblock
	$left = $addressLeft;
	$top = $addressTop;
	$pdf->SetXY($left, $top);

	if($sender != '' && $sender != 'none') {
		if(substr($sender, -3) == '_pp') {
			$pdf->SetFont('arial', 'B', $fontSizePP);
			$pdf->Write($lineHeightNormal * ko_fontsize_to_mm($fontSizePP), 'P.P. ');
			$top += 0.45;
		}
		$pdf->SetFont('arial', '', $fontSizeHeader);
		$left = $pdf->GetX();
		$pdf->SetXY($left, $top);
		//$pdf->Write($lineHeightNormal * ko_fontsize_to_mm($fontSizeHeader), $returnAddress);
		$pdf->Text($left+2, $top+2.8, $returnAddress);
		$leftAfter = $left + $pdf->getStringWidth($returnAddress);

		$pdf->Line($addressLeft+1, $top + $lineHeightNormal * ko_fontsize_to_mm($fontSizeHeader), $leftAfter+2, $top + $lineHeightNormal * ko_fontsize_to_mm($fontSizeHeader));
	}

	$top += 9;
	$left = $addressLeft;
	$pdf->SetFont('arial', '', $fontSizeNormal);
	$pdf->SetXY($left, $top);
	$pdf->MultiCell(0, $lineHeightNormal * ko_fontsize_to_mm($fontSizeNormal), $address);

	$top = $addressTop + 50;
	$left = $addressLeft;
	$pdf->Text($left + $weirdOffset, $top, ($general['sender'] === NULL ? ko_get_setting('info_city') : $general['sender']['city']) . ', ' . strftime('%d. %B %Y'));


	// title
	$left = $leftD;
	$top = 120;
	$pdf->SetFont('arial', 'B', $fontSizeTitle);
	$pdf->Text($left + $weirdOffset, $top, $title);


	// salutation
	$left = $leftD;
	$top += 14;
	$pdf->SetFont('arial', '', $fontSizeNormal);
	$pdf->Text($left + $weirdOffset, $top, $salutation);

	// main text
	$left = $leftD;
	$top += 1.2;
	$pdf->SetMargins($leftD, $marginTop, $marginRight);
	$pdf->SetXY($left, $top);
	$pdf->WriteHtml(html_entity_decode($entry['_text_'], null, "ISO-8859-1"));

	$top = $pdf->GetY() + 3.4;
	$pdf->SetMargins($marginLeft, $marginTop, $marginRight);

	// greetings
	$top = 235;
	$topRemember = $top;
	$left = $leftD;
	$pdf->Text($left + $weirdOffset, $top, $personData['_closing_']);
	$top += 4;
	if ($signaturePath) {
		$pdf->Image($signaturePath, $left + 3, $top, '', $signatureHeight);
	}
	$top += 24;
	$pdf->SetFont('arial', '', $fontSizeNormal);
	$pdf->Text($left + $weirdOffset, $top, $general['signature']);

}//add_address_to_pdf()




function ko_leute_export_details_user_template($export, $personIds) {
	global $BASE_PATH;

	$file = $export['user_template'];
	if ($file && file_exists($file) && is_readable($file)) {
		$doZip = sizeof($personIds) > 1;
		if ($doZip) {
			// prepare zip archive
			$filename = "{$BASE_PATH}download/people_".strtolower(str_replace(array('/', '\\'), array('_', '_'), $export['name']))."_".date('Ymd_His').".zip";
			$zip = new ZipArchive();
			$zip->open($filename, ZIPARCHIVE::CREATE);
		}
		foreach ($personIds as $personId) {
			ko_get_person_by_id($personId, $person, TRUE);

			//Rectype
			$person = ko_apply_rectype($person);
			$fn = ko_word_docx($file, $person);
			if ($doZip) {
				$zip->addFile($BASE_PATH.'download/word/'.$fn, basename($fn));
			} else {
				$filename = $BASE_PATH.'download/word/'.$fn;
			}
		}
		if ($doZip) {
			$zip->close();
		}

		return $filename;
	} else {
		return FALSE;
	}
}

function ko_leute_export_details_personal_form_extended($export, $personIds) {
	return ko_leute_export_details_personal_form($export, $personIds, TRUE);
}

function ko_leute_export_details_personal_form($export, $personIds, $extend = FALSE) {
	global $ko_path, $BASE_PATH, $PLUGINS, $KOTA, $LEUTE_EMAIL_FIELDS, $LEUTE_MOBILE_FIELDS;

	require_once($ko_path.'inc/tcpdf/tcpdf.php');

	$mainLayout = array(
		array('nachname' => 'B'),
		array('vorname' => 'B'),
		array('_hline' => ''),
		array('adresse' => ''),
		array('ort' => '', 'plz' => ''),
		array('_hline' => ''),
		array('vornamen' => ''),
		array('geschlecht' => '', 'id' => ''),
		array('zivilstand' => '', 'geburtsdatum' => ''),
		array('hometown' => '', 'zuzug' => ''),
		array('confession' => '', 'wegzug' => ''),
		array('death_date' => ''),
		array('father' => '', 'famfunction' => ''),
		array('mother' => ''),
	);
	$othersLayout = array(
		'nachname' => '20%',
		'vorname' => '20%',
		'famfunction' => '15%',
		'geschlecht' => '10%',
		'geburtsdatum' => '15%',
		'confession' => '20%',
	);

	if ($extend === TRUE) {
		$additional_fields = array_unique(array_merge(array('telp','telg','natel','email',), $LEUTE_EMAIL_FIELDS, $LEUTE_MOBILE_FIELDS));
		$newline = 0;
		foreach ($additional_fields as $field) {
			$last_id = end(array_keys($mainLayout));
			$newline = ($newline == 0 ? 1 : 0);
			$mainLayout[$last_id+$newline][$field] = '';
		}
	}

	foreach($PLUGINS as $plugin) {
		if(function_exists('my_leute_export_details_personal_form_layout_'.$plugin['name'])) {
			call_user_func_array('my_leute_export_details_personal_form_layout_' . $plugin['name'], array(&$mainLayout, &$othersLayout, $extend));
		}
	}

	// unset fields in layout that are not present in this kOOL installation
	if (!is_array($KOTA['ko_leute']['vorname'])) {
		ko_include_kota(array('ko_leute'));
	}

	//Get allowed columns
	$allowed_cols = ko_get_leute_admin_spalten($_SESSION['ses_userid'], 'all');

	$fieldsPresent = array_keys($KOTA['ko_leute']);
	foreach ($mainLayout as $rowKey => $row) {
		//Check access for each column
		$newRow = array();
		foreach ($row as $colKey => $col) {
			$noAccess = FALSE;
			if((is_array($allowed_cols["view"]) && !in_array($colKey, $allowed_cols["view"])) && (!is_array($allowed_cols["edit"]) || is_array($allowed_cols["edit"]) && !in_array($colKey, $allowed_cols["edit"]))) {
				$noAccess = TRUE;
			}

			if ( (!in_array($colKey, $fieldsPresent) || $noAccess) && substr($colKey, 0, 1) != '_') $newRow['_empty'] = '';
			else $newRow[$colKey] = $col;
		}
		$mainLayout[$rowKey] = $newRow;
	}
	foreach ($othersLayout as $colKey => $col) {
		$noAccess = FALSE;
		if((is_array($allowed_cols["view"]) && !in_array($colKey, $allowed_cols["view"])) && (!is_array($allowed_cols["edit"]) || is_array($allowed_cols["edit"]) && !in_array($colKey, $allowed_cols["edit"]))) {
			$noAccess = TRUE;
		}
		if (!in_array($colKey, $fieldsPresent) || $noAccess) unset($othersLayout[$colKey]);
	}

	class kOOLPeoplePersonalFormTCPDF extends TCPDF {
		public $mainLayout;
		public $othersLayout;
		public $fontSizeLabel = 7;
		public $fontSize = 8;
		public $lineHeightLabel = 3;
		public $lineHeight = 4;

		public function Header() {
			global $BASE_PATH;
			$logoFile = ko_get_pdf_logo();
			if ($logoFile) {
				$logoPath = $BASE_PATH.'my_images/'.$logoFile;
				$this->Image($logoPath, $this->lMargin, $this->tMargin, 0, 12, '', '', 'L');
			}
		}
		public function Footer() {
			global $DATETIME;

			$this->SetFont('', '', $this->fontSize);

			$this->SetXY($this->lMargin, $this->h - 15);
			$this->MultiCell(60, '', ko_get_setting('info_name'), 0, 'L');

			$this->SetXY($this->w - $this->rMargin - 60, $this->h - 15);
			$this->MultiCell(60, '', strftime($DATETIME['dmY']) . ' ' . date('H:i'), 0, 'R');
		}

		public function SetMainLayout($mainLayout) {
			$this->mainLayout = $mainLayout;
		}
		public function SetOthersLayout($othersLayout) {
			$this->othersLayout = $othersLayout;
		}

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

		public function AddPerson($person) {
			global $access;

			$this->AddPage();

			$this->SetFont('', '', 14);
			$this->SetTextColor(0);
			$this->SetXY($this->w - $this->rMargin - 160, 13.5);
			$this->Cell(160, 0, trim("{$person['vorname']} {$person['nachname']}"), 0, 0, 'R');

			if ($person['famid']) {
				$members = db_select_data('ko_leute', "WHERE `famid` = {$person['famid']} AND `id` <> {$person['id']}", '*', "ORDER BY `famfunction` DESC");
			} else {
				$members = array();
			}

			$mains = array();
			$twoColumnLayout = FALSE;
			if ($members && sizeof($members) > 0) {
				if (in_array($person['famfunction'], array('wife', 'husband'))) {
					$spouseFound = FALSE;
					$spouse = NULL;
					$others = array();
					foreach ($members as $member) {
						if (in_array($member['famfunction'], array('wife', 'husband'))) {
							$spouseFound = TRUE;
							$spouse = $member;
						} else {
							$others[] = $member;
						}
					}
					if ($spouseFound) {
						$mains[] = $person;
						$mains[] = $spouse;
						$twoColumnLayout = TRUE;
					} else {
						$mains[] = $person;
					}
				} else {
					$mains[] = $person;
					$others = $members;
				}
			} else {
				$mains[] = $person;
			}

			$labelWidth1 = 30;
			$labelWidth2 = 20;
			if ($twoColumnLayout) {
				$lefts = array($this->lMargin + $labelWidth1, ($this->w - $labelWidth1) / 2 + $labelWidth1);
				$rights = array(($this->w - $labelWidth1) / 2 + $labelWidth1, $this->w - $this->rMargin);
			} else {
				$lefts = array($this->lMargin + $labelWidth1);
				$rights = array($this->w - $this->rMargin);
			}

			$this->SetXY($this->lMargin, 23);
			$yInitial = $this->GetY();
			$this->SetFont('', '', $this->fontSize);
			$this->SetTextColor(0);
			$this->setLastH($this->lineHeight);
			$this->SetCellPaddings(1, 0, 1, 0);

			foreach ($this->mainLayout as $line) {
				$yBefore = $this->GetY();
				$yMax = $yBefore;

				$fieldName = array_keys($line)[0];
				if ($fieldName == '_hline') {
					$this->SetY($yBefore);
					$this->SetLineWidth(0.2);
					$this->Line($this->lMargin, $this->GetY(), $this->w - $this->rMargin, $this->GetY());
				} else if (!in_array($fieldName, array('_empty'))) {
					$ll = getLL('kota_ko_leute_'.$fieldName);
					$ll = $ll?$ll:$fieldName;
					$this->SetXY($this->lMargin, $yBefore);
					$this->SetFont('', 'I', $this->fontSizeLabel);
					$this->MultiCell($labelWidth1, $this->lineHeightLabel, $ll, 0, 'L');
					$yMax = max($yMax, $this->GetY());
				}

				$nFields = sizeof($line);
				foreach ($mains as $mainIdx => $main) {
					$totalWidth = $rights[$mainIdx] - $lefts[$mainIdx];
					$fieldsLeft = $lefts[$mainIdx];
					$fieldsRight = $rights[$mainIdx];

					$fieldIdx = 0;
					foreach ($line as $fieldName => $fieldInfo) {
						if ($fieldIdx > 0) {
							if (!in_array($fieldName, array('_empty', '_line'))) {
								$this->SetXY($fieldsLeft + $totalWidth / 2, $yBefore);
								$ll = getLL('kota_ko_leute_'.$fieldName);
								$ll = $ll?$ll:$fieldName;
								$this->SetFont('', 'I', $this->fontSizeLabel);
								$this->MultiCell($labelWidth2, $this->lineHeightLabel, $ll, 0, 'L');
								$yMax = max($yMax, $this->GetY());
							}

							$left = $fieldsLeft + $totalWidth / 2 + $labelWidth2;
							$right = $fieldsRight;
						} else {
							if ($nFields == 1) {
								$left = $fieldsLeft;
								$right = $fieldsRight;
							} else {
								$left = $fieldsLeft;
								$right = $fieldsLeft + $totalWidth / 2;
							}
						}
						$fieldWidth = $right - $left;

						if ($fieldName == '_hline') {
							// pass
						} else if ($fieldName == '_empty') {
							// pass
						} else {
							$this->SetXY($left, $yBefore);
							$this->SetFont('', $fieldInfo, $this->fontSize);
							$entry = strip_tags(map_leute_daten($mains[$mainIdx][$fieldName], $fieldName, $mains[$mainIdx], $datafields, FALSE, array('kota_process_modes' => 'pdf,list')));
							$this->MultiCell($fieldWidth, $this->lineHeight, $entry, 0, 'L');
							$yMax = max($yMax, $this->GetY());
						}
						$fieldIdx++;
					}
				}
				$this->SetXY($this->lMargin, $yMax + 0.5);
			}
			if ($twoColumnLayout && isset($yMax)) {
				$this->SetLineWidth(0.2);
				$this->Line(($this->w - $labelWidth1) / 2 + $labelWidth1, $yInitial, ($this->w - $labelWidth1) / 2 + $labelWidth1, $yMax);
				$this->Line($this->lMargin + $labelWidth1, $yInitial, $this->lMargin + $labelWidth1, $yMax);
			}
			$this->Ln(4);

			// add other family members
			if (isset($others) && sizeof($others) > 0) {
				$this->SetLineWidth(0.2);
				$this->Line($this->lMargin, $this->GetY(), $this->w - $this->rMargin, $this->GetY());
				$this->Ln(4);

				$this->SetFont('', 'B', $this->fontSize);
				$this->Write($this->lineHeight, getLL('leute_export_details_personal_form_other_family_members'));
				$this->Ln(4);

				$this->SetCellPaddings(1, 0.5, 1, 0.5);
				$this->SetFont('', 'I', $this->fontSize);
				$this->MultiRow(ko_array_ll(array_keys($this->othersLayout), 'kota_ko_leute_'), array_values($this->othersLayout));

				$this->SetLineWidth(0.1);
				$this->Line($this->lMargin, $this->GetY(), $this->w - $this->rMargin, $this->GetY());

				$this->SetFont('', '');
				$layout = $this->othersLayout;
				foreach ($others as $other) {
					$otherProcessed = array_map(function($e)use($other){return map_leute_daten($other[$e], $e, $other, $datafields, FALSE, array('kota_process_modes' => 'pdf,list'));}, array_keys($layout));
					$this->MultiRow($otherProcessed, array_values($this->othersLayout));
				}
				$this->Ln(4);
			}

			// add children that are not in same household
			$otherChildren = db_select_data('ko_leute', "WHERE (`father` = {$person['id']} OR `mother` = {$person['id']}) AND (`famid` = 0 OR `famid` <> {$person['famid']})");
			// add other family members
			if (isset($otherChildren) && sizeof($otherChildren) > 0) {
				$this->SetLineWidth(0.2);
				$this->Line($this->lMargin, $this->GetY(), $this->w - $this->rMargin, $this->GetY());
				$this->Ln(4);

				$this->SetFont('', 'B', $this->fontSize);
				$this->Write($this->lineHeight, getLL('leute_export_details_personal_form_other_children'));
				$this->Ln(4);

				$this->SetCellPaddings(1, 0.5, 1, 0.5);
				$this->SetFont('', 'I', $this->fontSize);
				$this->MultiRow(ko_array_ll(array_keys($this->othersLayout), 'kota_ko_leute_'), array_values($this->othersLayout));

				$this->SetLineWidth(0.1);
				$this->Line($this->lMargin, $this->GetY(), $this->w - $this->rMargin, $this->GetY());

				$this->SetFont('', '');
				$layout = $this->othersLayout;
				foreach ($otherChildren as $other) {
					$otherProcessed = array_map(function($e)use($other){return map_leute_daten($other[$e], $e, $other, $datafields, FALSE, array('kota_process_modes' => 'pdf,list'));}, array_keys($layout));
					$this->MultiRow($otherProcessed, array_values($this->othersLayout));
				}
				$this->Ln(4);
			}

			// add groups
			$ahesFormer = db_select_data('ko_groups_assignment_history', "WHERE `person_id` = {$person['id']} AND `stop` <> '0000-00-00 00:00:00'", '*', "ORDER BY `stop` DESC, `start` DESC");
			$ahesCurrent = db_select_data('ko_groups_assignment_history', "WHERE `person_id` = {$person['id']} AND `stop` = '0000-00-00 00:00:00'", '*', "ORDER BY `start` DESC");

			$ahes = array_merge($ahesCurrent?$ahesCurrent:array(), $ahesFormer?$ahesFormer:array());
			if (sizeof($ahes) > 0) {
				$widths = array('80%', '10%', '10%');

				$this->SetLineWidth(0.2);
				$this->Line($this->lMargin, $this->GetY(), $this->w - $this->rMargin, $this->GetY());
				$this->Ln(4);

				$this->SetFont('', 'B', $this->fontSize);
				$this->Write($this->lineHeight, getLL('leute_export_details_personal_form_groups'));
				$this->Ln(4);

				$this->SetCellPaddings(1, 0.5, 1, 0.5);
				$this->SetFont('', 'I', $this->fontSize);
				$this->MultiRow(ko_array_ll(array('group_id', 'start', 'stop'), 'kota_listview_ko_groups_assignment_history_'), $widths);

				$this->SetLineWidth(0.1);
				$this->Line($this->lMargin, $this->GetY(), $this->w - $this->rMargin, $this->GetY());

				$this->SetFont('', '');
				$aheIndex = 0;
				foreach ($ahes as $ahe) {
					if($access['groups']['ALL'] < 1 && $access['groups'][zerofill($ahe['group_id'], 6)] < 1) continue;

					// skip deleted groups for display in pdf
					if (db_get_count("ko_groups", "id", "AND id = '". zerofill($ahe['group_id'], 6) ."'") == 0) continue;

					if ($this->GetY() > $this->h - 30) {
						$this->MultiRow(array('...', '', ''), $widths);
						break;
					}
					if ($ahe['stop'] != '0000-00-00 00:00:00') $this->SetTextColor(120);
					if ($ahe['role_id']) {
						$roleString = 'r'.zerofill($ahe['role_id'], 6);
					} else {
						$roleString = '';
					}
					$fullGid = ko_groups_decode(zerofill($ahe['group_id'], 6), 'full_gid');
					if ($roleString) $fullGid .= ":{$roleString}";
					$groupString = ko_groups_decode($fullGid, 'group_desc_full');;

					$row = array($groupString, sql2datum(substr($ahe['start'], 0, 10)), sql2datum(substr($ahe['stop'], 0, 10)));
					$this->MultiRow($row, $widths);
					$aheIndex++;
				}
				$this->Ln(4);
			}
		}
	}

	$pdf = new kOOLPeoplePersonalFormTCPDF('P', 'mm', 'A4', false, 'ISO-8859-1', false);
	$pdf->SetMainLayout($mainLayout);
	$pdf->SetOthersLayout($othersLayout);
	$pdf->resetLastH();

	// set document information
	$pdf->SetCreator(PDF_CREATOR);
	$pdf->SetAuthor(ko_get_setting('info_name'));

	$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

	$pdf->SetMargins(9, 7, 9);
	$pdf->SetHeaderMargin(0);
	$pdf->SetFooterMargin(0);

	$pdf->SetAutoPageBreak(TRUE, 10);

	$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
	foreach ($personIds as $personId) {
		ko_get_person_by_id($personId, $person, TRUE);
		$pdf->AddPerson($person);
	}

	$filename = $BASE_PATH.'download/pdf/personalblatt_'.strftime('%d%m%Y_%H%M%S', time()).'.pdf';
	$pdf->Output($filename, 'F');

	return $filename;
}








?>
