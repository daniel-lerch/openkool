<?php
/*
* UPDATE SCRIPT FOR kOOL RELEASE R47
*
* Description:
* Performs necessary update steps from R46 to R47.
* 
* Instructions:
* - Update the source to R46 first
* - Call install/kOOL_setup.sh from your web root
* - Update database by calling php install/update.phpsh first
* - Set the path to your kOOL installation below and run the script on your kOOL server.
*
*/


/* Settings */

// Set path to your kOOL installation (absolute or relative)
$kOOL_path = './';

/* End of settings */



$ko_path = $kOOL_path;
$basePlugin = '';

require_once("{$ko_path}inc/ko.inc");


kool_update_r46_r47_mandatory_fields();
kool_update_r46_r47_enum_filters();
kool_update_r46_r47_word_templates();
kool_update_r46_r47_leute_email_json();
kool_update_r46_r47_mailing_from();
kool_update_r46_r47_sortcols();





function kool_update_r46_r47_sortcols() {
	error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED & ~E_NOTICE);
	$_SESSION['ses_userid'] = ko_get_root_id();


	//Fix itemlists and set sorting according to _listview.
	$tables = array('ko_event', 'ko_reservation', 'ko_tracking');
	foreach($tables as $table) {
		ko_include_kota(array($table));

		$sorted = array();
		foreach($KOTA[$table]['_listview'] as $k => $v) {
			$sorted[] = $v['name'];
		}

		$itemlists = db_select_data('ko_userprefs', "WHERE `type` = '".$table."_colitemset'", '*');
		foreach($itemlists as $itemlist) {
			$new = array();
			$cols = explode(',', $itemlist['value']);
			foreach($sorted as $s) {
				if(in_array($s, $cols)) $new[] = $s;
			}

			db_update_data('ko_userprefs', "WHERE `id` = '".$itemlist['id']."'", array('value' => implode(',', $new)));
		}
	}


	//Fix kota_show_cols and set proper sorting according to _listview.
	$itemlists = db_select_data('ko_userprefs', "WHERE `key` LIKE 'kota_show_cols_%'", '*');
	foreach($itemlists as $itemlist) {
		if(!$itemlist['value']) continue;

		$table = substr($itemlist['key'], 15);
		if(!$table) continue;

		ko_include_kota(array($table));

		if(!is_array($KOTA[$table]['_listview'])) continue;

		$sorted = array();
		foreach($KOTA[$table]['_listview'] as $k => $v) {
			$sorted[] = $v['name'];
		}

		$new = array();
		$cols = explode(',', $itemlist['value']);
		foreach($sorted as $s) {
			if(in_array($s, $cols)) $new[] = $s;
		}

		//print 'OLD: '.implode(', ', $cols)."\n";
		//print 'NEW: '.implode(', ', $new)."\n";
		//print "\n";
		db_update_data('ko_userprefs', "WHERE `id` = '".$itemlist['id']."'", array('value' => implode(',', $new)));
	}
}





function kool_update_r46_r47_mailing_from() {
	$old = ko_get_setting('group_mailing_from_email');
	$new = ko_get_setting('mailing_from_email');
	if($old && !$new) {
		ko_set_setting('mailing_from_email', $old);
		db_delete_data('ko_settings', "WHERE `key` = 'group_mailing_from_email'");
	}
}




function kool_update_r46_r47_mandatory_fields() {
	global $ko_path;

	foreach (array('daten_mandatory' => 'ko_event', 'res_mandatory' => 'ko_reservation') as $from => $to) {
		$old = ko_get_setting($from);
		ko_set_setting("kota_{$to}_mandatory_fields", $old);
	}
}

function kool_update_r46_r47_enum_filters() {
	global $ko_path, $KOTA;

	$oldFilters = db_select_data('ko_filter', "WHERE `code1` LIKE '%ko_specialfilter_enum%' AND `numvars` = 1");
	foreach ($oldFilters as $oldFilter) {
		if (strpos($oldFilter['dbcol'], '.') === FALSE) {
			$table = 'ko_leute';
			$col = $oldFilter['dbcol'];
		} else {
			list($table, $col) = explode('.', $oldFilter['dbcol']);
		}

		$dbColumns_ = db_get_columns($table);
		$dbColumns = array();
		foreach ($dbColumns_ as $c) {
			$dbColumns[$c['Field']] = $c;
		}

		if (!is_array($KOTA[$table])) ko_include_kota(array($table));
		if (array_key_exists($col, $dbColumns)) {
			$type = $dbColumns[$col]['Type'];
			if (strpos($type, 'enum') === FALSE) {
				if ($table === 'ko_leute') {
					$update = array('dbcol' => $col, 'code1' => "FCN:ko_specialfilter_kota:ko_leute:{$col}", 'sql1' => 'kota_filter');
				} else {
					$update = array('code1' => str_replace('ko_specialfilter_enum', 'ko_specialfilter_select', $oldFilter['code1']));
				}

				db_update_data('ko_filter', "WHERE `id` = {$oldFilter['id']}", $update);
			}
		}
	}
}

function kool_update_r46_r47_word_templates() {
	global $ko_path;

	$filesPath = "{$ko_path}config/";
	if ($handle = opendir($filesPath)) {
		while (false !== ($file = readdir($handle))) {
			if (preg_match('/^address([_\d]*).docx$/', $file, $matches)) {
				$userId = trim($matches[1]);
				if ($userId) $userId = substr($userId, 1);
				if ($userId) {
					ko_get_login($userId, $login);
					if ($login) {
						$name = $login['login'];
					} else {
						$name = $userId;
					}
					$name = "Login: {$name}";
				} else {
					$name = ko_get_setting('info_name');
				}

				$newId = db_insert_data('ko_detailed_person_exports', array(
					'name' => $name,
					'crdate' => date('Y-m-d H:i:s'),
				));

				rename("{$filesPath}{$file}", "{$ko_path}my_images/kota_ko_detailed_person_exports_template_{$newId}");
				db_update_data('ko_detailed_person_exports', "WHERE `id` = {$newId}", array('template' => "my_images/kota_ko_detailed_person_exports_template_{$newId}"));
			}
		}
		closedir($handle);
	}
}

function kool_update_r46_r47_leute_email_json() {
	global $ko_path;

	$ups = db_select_data('ko_userprefs', "WHERE `type` = 'leute_saved_email'", '*', '', '', FALSE, TRUE);
	foreach ($ups as $up) {
		$content = stripslashes($up['value']);
		array_walk_recursive($content, 'utf8_decode_array');
		if (preg_match('/^\{"subject":"(.*)","text":"(.*)","date":"(.*)"\}$/', $content, $matches)) {
			list($_all, $subject, $text, $date) = $matches;
			$subject = str_replace(array('u00e4', 'u00f6', 'u00fc'), array('ä', 'ö', 'ü'), $subject);
			$text = str_replace(array('u00e4', 'u00f6', 'u00fc'), array('ä', 'ö', 'ü'), $text);

			$newValue = array('subject' => $subject, 'text' => $text, 'date' => $date);

			array_walk_recursive($newValue, 'utf8_encode_array');
			$json = json_encode($newValue);
			print "from:  {$up['value']}\n";
			print "to:    {$json}\n";
			db_update_data('ko_userprefs', "WHERE `type` = 'leute_saved_email' AND `key` = '{$up['key']}'", array('value' => $json));
		}
	}
}
