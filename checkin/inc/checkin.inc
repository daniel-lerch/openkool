<?php

function ko_checkin_form_login() {
	global $smarty, $trackingId;

	$smarty->assign('trackingId', $trackingId);
	$smarty->display('ko_checkin_login.tpl');
}

function ko_checkin_show_checkin() {
	global $smarty;

	$smarty->assign('user', $_SESSION['checkin_user']);
	if($_SESSION['checkin_mode'] == 1) {
		$smarty->assign('results', ko_checkin_show_results($_SESSION['checkin_tracking_id'], '_all'));
	}
	$smarty->display('ko_checkin_checkin.tpl');
}

function ko_checkin_show_results($trackingId, $query, $date=NULL) {
	global $smarty;

	if ($date == NULL) $date = ko_checkin_get_date($trackingId);

	$personsByFamid = array();
	$tooManyResults = FALSE;

	$query = trim($query);
	if (strlen($query) > 2) {
		$tracking = db_select_data('ko_tracking', "WHERE `id` = {$trackingId}", '*', '', '', TRUE);
		$dates = array();
		$people = ko_tracking_get_people($tracking['filter'], $dates, $tracking['id']);

		if (is_array($people)) {
			$ids = ko_array_column($people, 'id');
		} else {
			$ids = array();
		}

		if (sizeof($ids)) {
			$where = "WHERE `deleted` = 0 AND `hidden` = 0 AND `id` IN ('" . implode("','", $ids) . "')";

			$wheres = array();
			if($query != '_all') {
				$queryParts = explode(' ', utf8_decode($query));
				foreach ($queryParts as $p) {
					$p = trim($p);
					if (!$p) continue;
					$subWhereParts = array();
					foreach (array('vorname', 'nachname') as $f) {
						$subWhereParts[] = "`{$f}` LIKE '%{$p}%'";
					}
					$wheres[] = implode(' OR ', $subWhereParts);
				}
				$where .= ' AND (' . implode(') AND (', $wheres) . ')';
			}

			$persons = db_select_data('ko_leute', $where, '*', 'ORDER BY `vorname` ASC, `nachname` ASC');
			ko_checkin_filter_search_result($trackingId, $persons, $date);
			$tooManyResults = sizeof($persons) > intval(ko_get_setting('checkin_max_results'));

			$showFields = ko_array_filter_empty(explode(',', ko_get_setting('checkin_display_leute_fields')));
			$fieldsLayout = array(
				array('vorname', 'nachname', 'geburtsdatum'),
				array('adresse'),
				array('ort', 'plz'),
			);
			$personsByFamid = array();
			$personsWithoutFamid = array();
			foreach ($persons as $p) {
				foreach ($showFields as $f) {
					$p[$f] = map_leute_daten($p[$f], $f, $p);
				}
				$descLines = array();
				foreach ($fieldsLayout as $line) {
					$doLine = FALSE;
					foreach ($line as $f) {
						if ($p[$f] && in_array($f, $showFields)) $doLine = TRUE;
					}
					if ($doLine) {
						$fields = array();
						foreach ($line as $f) {
							if ($p[$f] && in_array($f, $showFields)) {
								switch ($f) {
									case 'geburtsdatum':
										$v = "({$p[$f]})";
									break;
									case 'vorname':
									case 'nachname':
										$v = "<b>{$p[$f]}</b>";
									break;
									default:
										$v = $p[$f];
									break;
								}
								$fields[] = $v;
							}
						}
						$descLines[] = implode(' ', $fields);
					}
				}
				$p['desc'] = implode('<br>', $descLines);
				if($p['_info']['checked_in']) {
					$p['_info']['checkout_confirm'] = sprintf(getLL('checkin_checkout_confirm'),strip_tags($p['desc']));
				}
				if($p['famid'] && $_SESSION['checkin_mode'] != 1) {
					$personsByFamid[$p['famid']]['persons'][] = $p;
				} else {
					$personsWithoutFamid[] = [
						'persons' => [$p],
						'family' => $p['nachname'],
					];
				}

			}
			foreach ($personsByFamid as $famid => &$info) {
				if($famid) {
					$fam = db_select_data('ko_familie', "WHERE `famid` = '$famid'", '*', '', 'LIMIT 1', TRUE);
					if(!empty($fam['famlastname'])) {
						$info['family'] = $fam['famlastname'];
					} else {
						$parents = db_select_data('ko_leute',"WHERE `famid` = '$famid' AND `famfunction` IN('husband','wife')",'nachname');
						$info['family'] = implode('-',array_unique(array_column($parents,'nachname')));
					}
				}
			}
		}
	}

	$smarty->assign('tooManyResults', $tooManyResults);

	if($query == '_all') {
		$data = array();
		foreach($personsWithoutFamid as $person) {
			$data[] = $person['persons'][0];
		}
		$smarty->assign('data', $data);

		return $smarty->fetch('ko_checkin_results_open.tpl');
	} else {
		$smarty->assign('data', array_merge($personsByFamid,$personsWithoutFamid));
		$smarty->display('ko_checkin_results.tpl');
	}
}


/**
 * Computes the value to insert into ko_tracking_entries, based on tracking, person and date and updates the database.
 * This function implements a default behaviour by retrieving the value of the entry from the global array
 * $CHECKIN_DEFAULT_VALUES and additionally checks if there are kOOL plugins that implement the computation of entry.
 *
 * @param $trackingId integer
 * @param $personId integer
 * @param $date string sql-formatted date-string
 * @param $trackingEntry
 * @return integer error number. 0 means no error
 */
function ko_checkin_set_tracking_entry($trackingId, $personId, $date, &$trackingEntry) {
	global $CHECKIN_DEFAULT_VALUES, $PLUGINS;

	$tracking = db_select_data('ko_tracking', "WHERE `id` = {$trackingId}", '*', '', '', TRUE);
	ko_get_person_by_id($personId, $person, TRUE);

	$trackingEntry = $CHECKIN_DEFAULT_VALUES[$tracking['mode']];

	//Yield error if there is already a tracking entry for this person, tracking and date TODO: how to relax this?
	$existingTracking = db_select_data('ko_tracking_entries', "WHERE `lid` = '{$personId}' AND `tid` = {$trackingId} AND `date` = '{$date}'");
	$updateId = NULL;
	if (is_array($existingTracking) && sizeof($existingTracking) > 0) {
		if (sizeof($existingTracking) > 1) {
			//Error
			return 6;
		} else {
			$updateId = end($existingTracking);
			$updateId = $updateId['id'];
		}
	}

	//Allow plugins to overwrite these calculations
	foreach($PLUGINS as $plugin) {
		if(function_exists('my_checkin_compute_tracking_entry_'.$plugin['name'])) {
			call_user_func_array('my_checkin_compute_tracking_entry_'.$plugin['name'], array(&$tracking, &$person, $date, &$trackingEntry));
		}
	}

	//These fields are not allowed to be set by plugin, so simply set them to the correct values
	$trackingEntry['date'] = $date;
	$trackingEntry['lid'] = $personId;
	$trackingEntry['tid'] = $trackingId;

	if ($updateId) {
		$trackingEntry['last_change'] = date('Y-m-d H:i:s');
		db_update_data('ko_tracking_entries', "WHERE `id` = {$updateId}", $trackingEntry);
	} else {
		$trackingEntry['crdate'] = date('Y-m-d H:i:s');
		$trackingEntry['cruser'] = $_SESSION['ses_userid'];
		db_insert_data('ko_tracking_entries', $trackingEntry);
	}

	//No error
	return 0;
}


function ko_checkin_unset_tracking_entry($trackingId, $personId, $date) {
	global $CHECKIN_DEFAULT_VALUES, $PLUGINS;

	$tracking = db_select_data('ko_tracking', "WHERE `id` = {$trackingId}", '*', '', '', TRUE);

	//Allow plugins to overwrite these calculations
	$handledByPlugin = FALSE;
	foreach($PLUGINS as $plugin) {
		if(function_exists('my_checkin_unset_tracking_entry_'.$plugin['name'])) {
			$handledByPlugin = $handledByPlugin || call_user_func_array('my_checkin_unset_tracking_entry_'.$plugin['name'], array($trackingId, $personId, $date));
		}
	}

	//Default behaviour: filter out people who already have an entry for this tracking and date
	if (!$handledByPlugin) {
		$defaultEntry = $CHECKIN_DEFAULT_VALUES[$tracking['mode']];
		$wheres = array();
		foreach ($defaultEntry as $k => $v) {
			$wheres[] = "`{$k}` = '{$v}'";
		}
		$where = "WHERE `lid` = '{$personId}' AND `date` = '{$date}' AND `tid` = {$trackingId} AND (".implode(' AND ', $wheres).")";
		db_delete_data('ko_tracking_entries',$where);
	}

	//No error
	return 0;
}


/**
 * This function adds contextual information to people from the array $people based on whether they are already checked
 * in receive an entry in the tracking with id $trackingId on date $date. The contextual information is added to the
 * field '_info' on each person
 *
 * @param $trackingId integer
 * @param $people array
 * @param $date string sql-formatted date-string
 * @return integer error number. 0 means no error
 */
function ko_checkin_filter_search_result($trackingId, &$people, $date) {
	global $CHECKIN_DEFAULT_VALUES, $PLUGINS;

	$tracking = db_select_data('ko_tracking', "WHERE `id` = {$trackingId}", '*', '', '', TRUE);

	//Allow plugins to overwrite these calculations
	$handledByPlugin = FALSE;
	foreach($PLUGINS as $plugin) {
		if(function_exists('my_checkin_filter_search_result_'.$plugin['name'])) {
			$handledByPlugin = $handledByPlugin || call_user_func_array('my_checkin_filter_search_result_'.$plugin['name'], array(&$tracking, &$people, $date));
		}
	}

	//Default behaviour: filter out people who already have an entry for this tracking and date
	if (!$handledByPlugin) {
		if (sizeof($people) > 0) {
			$defaultEntry = $CHECKIN_DEFAULT_VALUES[$tracking['mode']];
			$wheres = array();
			foreach ($defaultEntry as $k => $v) {
				$wheres[] = "`{$k}` = '{$v}'";
			}
			$where = "WHERE `date` = '{$date}' AND `tid` = {$trackingId} AND (".implode(' AND ', $wheres).")";
			$entries = db_select_data('ko_tracking_entries', $where, 'id,lid');
			foreach ($entries as $e) {
				if (array_key_exists($e['lid'], $people)) {
					$people[$e['lid']]['_info'] = array(
						'checked_in' => TRUE,
					);
				}
			}
		}
	}

	//No error
	return 0;
}


/**
 * This function returns the date in context of the current check-in session. In case that we later want to allow to
 * statically set the date of a check-in session, we can simply adjust this function in the desired way.
 *
 * @param $trackingId integer
 * @return string
 */
function ko_checkin_get_date($trackingId) {
	return date('Y-m-d');
}


/**
 * This function adapts the KOTA definition for ko_leute to match the requirements of the form to add new persons in
 * admin-mode during checkin.
 *
 * @param $trackingId integer
 */
function ko_checkin_set_leute_form($trackingId) {
	global $PLUGINS, $KOTA;

	$tracking = db_select_data('ko_tracking', "WHERE `id` = {$trackingId}", '*', '', '', TRUE);

	$data = array();
	foreach (explode(',', $tracking['filter']) as $filter) {
		if(strlen($filter) >= 7 && substr($filter, 0, 1) == 'g' && preg_match('/^[g0-9:r,]*$/', $filter)) {
			list($gid, $rid) = explode(':', $filter);
			$gid = ko_groups_decode($filter, 'full_gid');
			$data[ko_groups_decode($gid, 'group_desc_full')] = $gid;
		}
	}
	ksort($data);
	$descs = array_keys($data);
	$values = array_values($data);
	$KOTA['ko_leute']['groups'] = array(
		'form' => array(
			'type' => 'checkboxes',
			'values' => $values,
			'descs' => $descs,
			'size' => 5,
			'mandatory' => TRUE,
		),
	);

	//Allow plugins to overwrite these calculations
	$handledByPlugin = FALSE;
	foreach($PLUGINS as $plugin) {
		if(function_exists('my_checkin_set_leute_form_'.$plugin['name'])) {
			$handledByPlugin = $handledByPlugin || call_user_func_array('my_checkin_set_leute_form_'.$plugin['name'], array());
		}
	}

	if (!$handledByPlugin) {
		$fl = &$KOTA['ko_leute']['_form_layout'];
		$fl = array(
			'general' => array(
				'group' => FALSE,
				'sorting' => 10,
				'groups' => array(
					'general' => array(
						'group' => FALSE,
						'sorting' => 10,
						'rows' => array(
							10 => array(
								'vorname' => 6,
								'nachname' => 6,
							),
							20 => array(
								'adresse' => 6,
							),
							30 => array(
								'plz' => 6,
								'ort' => 6,
							),
							40 => array(
								'geburtsdatum' => 6,
								'geschlecht' => 6,
							),
							50 => array(
								'telp' => 6,
								'natel' => 6,
							),
							60 => array(
								'groups' => 6,
								'memo1' => 6,
							),
						)
					)
				)
			),
			'_ignore_fields' => array(),
		);

		$useFields = array();
		foreach ($fl['general']['groups']['general']['rows'] as $r) {
			foreach ($r as $f => $w) $useFields[] = $f;
		}

		foreach ($KOTA['ko_leute'] as $n => $d) {
			if (substr($n, 0, 1) == '_') continue;
			if (!in_array($n, $useFields)) $fl['_ignore_fields'][] = $n;
		}
	}
}


function ko_checkin_get_person_checkin_number($person) {
	if ($person['checkin_number']) return $person['checkin_number'];
	else {
		$maxId = db_select_data('ko_leute', 'WHERE 1=1', 'MAX(`checkin_number`) AS `maxId`', '', '', TRUE, TRUE);
		db_update_data('ko_leute', "WHERE `id` = '{$person['id']}'", array('checkin_number' => $maxId['maxId'] + 1));
		return $maxId['maxId'] + 1;
	}
}


function ko_checkin_create_labels($trackingEntries, $date, $trackingId, $printerId, $printerType) {
	global $PLUGINS, $BASE_PATH;

	$personIds = array_keys($trackingEntries);

	$filename = "{$BASE_PATH}my_images/checkin_label_{$trackingId}_".md5(implode(',', $personIds)).".pdf";

	//Allow plugins to overwrite these calculations
	$pluginFcn = NULL;
	foreach($PLUGINS as $plugin) {
		if(function_exists('my_checkin_create_labels_'.$plugin['name'].'_'.str_replace('-', '', $printerId))) {
			$pluginFcn = 'my_checkin_create_labels_'.$plugin['name'].'_'.str_replace('-', '', $printerId);
		} else if(function_exists('my_checkin_create_labels_'.$plugin['name'].'_'.$printerType)) {
			$pluginFcn = 'my_checkin_create_labels_'.$plugin['name'].'_'.$printerType;
		} else if(function_exists('my_checkin_create_labels_'.$plugin['name'])) {
			$pluginFcn = 'my_checkin_create_labels_'.$plugin['name'];
		}
		if ($pluginFcn) break;
	}

	if ($pluginFcn) {
		call_user_func_array($pluginFcn, array($trackingId, $personIds, $trackingEntries, $date, $printerId, $filename));
	} else {
		require_once($BASE_PATH.'inc/tcpdf/tcpdf.php');

		class CheckinLabelTCPDF extends TCPDF {
			public function Header() {}
			public function Footer() {}
		}

		$pdf = new CheckinLabelTCPDF('P', 'mm', 'A4', false, 'ISO-8859-1', false);
		$pdf->resetLastH();

		// set document information
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor(ko_get_setting('info_name'));

		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

		$pdf->SetMargins(9, 7, 9);
		$pdf->SetHeaderMargin(0);
		$pdf->SetFooterMargin(0);

		$pdf->SetAutoPageBreak(TRUE, 10);

		foreach ($personIds as $personId) {
			ko_get_person_by_id($personId, $person, TRUE);
			$checkinNumber = ko_checkin_get_person_checkin_number($person);

			$labelText = "{$person['vorname']} {$person['nachname']}\n{$checkinNumber}";

			$pdf->AddPage();
			$pdf->SetXY(15, 15);
			$pdf->MultiCell(60, 5, $labelText);
		}

		$pdf->Output($filename, 'F');
	}

	return $filename;
}

function ko_checkin_get_print_options($trackingEntries, $date, $trackingId, $printerId, $printerType) {
	global $PLUGINS;

	$options = array();

	//Allow plugins to overwrite these calculations
	$pluginFcn = NULL;
	foreach($PLUGINS as $plugin) {
		if(function_exists('my_checkin_get_print_options_'.$plugin['name'].'_'.str_replace('-', '', $printerId))) {
			$pluginFcn = 'my_checkin_get_print_options_'.$plugin['name'].'_'.str_replace('-', '', $printerId);
		} else if(function_exists('my_checkin_get_print_options_'.$plugin['name'].'_'.$printerType)) {
			$pluginFcn = 'my_checkin_get_print_options_'.$plugin['name'].'_'.$printerType;
		} else if(function_exists('my_checkin_get_print_options_'.$plugin['name'])) {
			$pluginFcn = 'my_checkin_get_print_options_'.$plugin['name'];
		}
		if ($pluginFcn) break;
	}

	if ($pluginFcn) {
		call_user_func_array($pluginFcn, array(&$options, $trackingEntries, $date, $trackingId, $printerId));
	}

	return $options;
}
