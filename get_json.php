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

error_reporting(0);

$ko_path = "./";
$ko_menu_akt = 'get.php';

require($ko_path."config/ko-config.php");

//Get request from _POST or _GET (for backwards compatibility)
$q = '';
if(isset($_POST['q'])) $q = $_POST['q'];
if(isset($_GET['q'])) $q = $_GET['q'];
if(!$q) exit;

//Decrypt request
if(!KOOL_ENCRYPTION_KEY) exit;
$no_enc = defined("KOOL_NO_ENCRYPTION") && KOOL_NO_ENCRYPTION;

//No encryption
if($no_enc) {
	$request_xml = base64_decode($q);
	//Don't allow direct db access to these tables
	$deny_tables = array("ko_admin","ko_donations","ko_familie","ko_kleingruppen","ko_leute","ko_leute_changes","ko_log","ko_news");
}
//Use encryption
else {
	require_once($ko_path."inc/class.openssl.php");
	$crypt = new openssl('AES-256-CBC');
	$crypt->setKey(KOOL_ENCRYPTION_KEY);
	$request_json = $crypt->decrypt(base64_decode($q));
	//Don't allow direct db access to these tables
	$deny_tables = array("ko_admin");
}

include($ko_path."inc/ko.inc");

//Parse JSON into an array
$req = json_decode($request_json,true);
array_walk_recursive($req,function(&$v,$k) {$v = utf8_decode($v);});

//Get action
$action = $req["action"];
if(!$action) exit;

//Check for valid encryption hash if no encryption is used
if($no_enc) {
	if(!$req["encKey"] || md5(KOOL_ENCRYPTION_KEY) != $req["encKey"]) exit;
}

//Get lang
$_SESSION["lang"] = $req["language"];

//Include KOTA
ko_include_kota(array('ko_leute', 'ko_kleingruppen'));

//Perform given action
switch($action) {
	//Get an LL string
	case "getLL":
		foreach($req["value"] as $key) {
			$r["getLL"][] = array("key" => $key, "content" => getLL($key));
		}
	break;


	case 'smsStats':
		//Get last sms_mark
		$lastMarkLog = db_select_data('ko_log', "WHERE `type` = 'sms_mark'", '*', 'ORDER BY `date` DESC', 'LIMIT 0,1', TRUE);
		if($lastMarkLog['id']) {
			$where = "`date` > '".$lastMarkLog['date']."'";
		} else {
			$where = '';
		}

		//Get sent sms since last mark
		$smsSentLogs = db_select_data('ko_log', "WHERE `type` = 'sms_sent' ".($where ? " AND $where" : ''));

		//Sum up credits and sent messages
		$totalCredits = $totalMessages = 0;
		if(sizeof($smsSentLogs) > 0) {
			foreach($smsSentLogs as $log) {
				$parts = explode(' - ', $log['comment']);
				$credits = array_pop($parts);
				$problems = array_pop($parts);
				$ratio = array_pop($parts);
				list($done, $total) = explode('/', $ratio);

				$totalCredits += $credits;
				$totalMessages += $total;
			}
		}

		$r = array(
			'smsStats' => array(
				'last' => array('credits' => $totalCredits, 'messages' => $totalMessages),
			),
		);

		//Add new mark
		if($req['mark']) {
			db_insert_data('ko_log', array('type' => 'sms_mark', 'user_id' => ko_get_guest_id(), 'date' => date('Y-m-d H:i:s')));
		}
  break;


	case 'isModuleInstalled':
		$m = $req['module'];
		if (isset($req['user']) && isset($req['user'])) {
			$userId = format_userinput($req['user'], 'uint');
			$r['isModuleInstalled'] = ko_module_installed($m, $userId)?TRUE:FALSE;
		} else {
			$r['isModuleInstalled'] = in_array($m, $MODULES);
		}
	break;


	case 'getKOTALabels':
		$table = $req['table'];
		$mode = $req['mode'];
		if($mode != 'listview') $mode = '';
		if($mode != '') $mode .= '_';

		$r = array();
		ko_include_kota(array($table));
		foreach($KOTA[$table] as $k => $v) {
			if(substr($k, 0, 1) == '_') continue;
			$ll = getLL('kota_'.$mode.$table.'_'.$k);
			if(!$ll && $mode != '') {
				$ll = getLL('kota_'.$table.'_'.$k);
			}
			$r['getKOTALabels'][$k] = $ll;
		}
	break;


	case 'getKOTA':
		$table = $req['table'];
		$field = isset($req['field']) ? $req['field'] : false;

		ko_include_kota(array($table));
		$kota = $field ? array($field => $KOTA[$table][$field]) : $KOTA[$table];
		$r['getKOTA'] = $kota;
	break;


	//Get a list of small group roles
	case 'smallgroupRoles':
		foreach($SMALLGROUPS_ROLES as $role) {
			if(!$role || !getLL('kg_roles_'.$role)) continue;
			$r['smallgroupRoles'] = array('id' => $role, 'name' => getLL('kg_roles_'.$role));
		}
	break;


	//Get a list of small group roles
	case 'getSmallgroupRoles':
		foreach($SMALLGROUPS_ROLES as $role) {
			if(!$role) continue;
			$r['smallgroupRoles'][] = [
				'id' => $role,
				'name' => getLL('kg_roles_'.$role),
				'leader' => in_array($role,$SMALLGROUPS_ROLES_LEADER),
				'member' => in_array($role,$SMALLGROUPS_ROLES_FOR_NUM),
			];
		}
	break;


	//Call ko_save_leute_changes(). Used if changes are being made to address records
	case 'saveLeuteChanges':
		$id = format_userinput($req['id'], 'uint');
		if(!$id) break;
		ko_save_leute_changes($id);
	break;



	//Call ko_create_groups_snapshot() after ko_leute.groups has been changed directly (e.g. lpc_wahlkurse)
	case 'saveGroupsSnapshot':
		$id = format_userinput($req['id'], 'uint');
		if(!$id) break;
		ko_create_groups_snapshot($id);
	break;


	//Update group count for given group ids: ids => array(id1, id2, ...)
	case 'updateGroupCount':
		foreach(explode(',', $req['ids']) as $id) {
			$id = format_userinput($id, 'uint');
			if(!$id) continue;
			$group = ko_groups_decode($id, 'group');
			if(!$group['id'] || !$group['maxcount']) continue;
			ko_update_group_count($group['id'], $group['count_role']);
		}
	break;


	case 'sendSMS':
		if(!in_array('sms', $MODULES)) break;

		$recipients = explode(',', $req['recipients']);
		$text = $req['smstext'];
		$from = $req['from'];
		send_aspsms($recipients, $text, $from, $num, $credits, $log_id);
	break;

	case 'sendTelegram':
		if(!in_array('telegram', $MODULES)) break;

		$textTelegram = $req['telegramtext'];
		$textSMS = $req['telegramtext'];
		$from = $req['from'];

		$telegramRecipients = [];
		foreach(($recipients = explode(',', $req['recipients'])) AS $recipient_id) {
			ko_get_person_by_id($recipient_id, $person);

			ko_get_leute_mobile($person, $mobile);
			$natel = $mobile[0];

			if ($person['telegram_id'] > 0) {
				$telegramRecipients[] = $person;
			} else if (check_natel($natel)) {
				send_aspsms(array($natel), $textSMS, $from, $num, $credits, $log_id);
			} else {
				ko_log('error', 'No telegram ID and no valid mobile number found for get.php:sendTelegram, RecipientID: '.$recipient_id);
			}
		}

		if(!empty($telegramRecipients)) {
			send_telegram_message($telegramRecipients, $textTelegram);
		}
	break;

	case 'trackingDates':
		if(!in_array('tracking', $MODULES)) break;

		$tid = intval($req['tracking_id']);
		if(!$tid) break;
		$tracking = db_select_data('ko_tracking', "WHERE `id` = '$tid'", '*', '', '', TRUE);
		if(!$tracking['id'] || $tracking['id'] != $tid) break;

		$start = $req['start'] ? $req['start'] : date('Y-m-d');
		$limit = $req['limit'] ? $req['limit'] : 100;

		include($ko_path.'tracking/inc/tracking.inc');
		$dates = ko_tracking_get_dates($tracking, $start, $limit, $prev, $next, $prev1, FALSE);
		$r['TRACKING_DATES'] = $dates;
	break;


	case 'trackingPeople':
		if(!in_array('tracking', $MODULES)) break;

		$tid = intval($req['tracking_id']);
		if(!$tid) break;
		$tracking = db_select_data('ko_tracking', "WHERE `id` = '$tid'", '*', '', '', TRUE);
		if(!$tracking['id'] || $tracking['id'] != $tid) break;

		$filter = $tracking['filter'];
		if(!$filter) break;

		include($ko_path.'tracking/inc/tracking.inc');
		$people = ko_tracking_get_people($filter, $dates, $tid, FALSE);
		$r['TRACKING_PEOPLE'] = $people;
	break;


	case 'storeReservation':
		if(!in_array('reservation', $MODULES)) break;
		$moderated = $req['moderated'];
		$res = json_decode($req['data'], TRUE);

		//UTF-8 decode, because XML request data is always in UTF-8
		foreach($res as $rid => $r) {
			foreach($r as $k => $v) {
				$res[$rid][$k] = $v;
			}
		}

		include($ko_path.'reservation/inc/reservation.inc');

		if($moderated) {
			ko_res_store_moderation($res, FALSE);
		} else {
			ko_res_store_reservation($res, FALSE, $double_error);
		}
		if($double_error != '') {
			$r = array('error' => 1, 'error_txt' => $double_error);
		} else {
			$r = 'OK';
		}
	break;


	case 'deleteReservation':
		if(!in_array('reservation', $MODULES)) break;

		$id = intval($req['id']);
		if(!$id) {
			$r = array('error' => 1, 'error_txt' => 'No reservation found');
			break;
		}

		include($ko_path.'reservation/inc/reservation.inc');

		ko_get_res_by_id($id, $r_); $r = $r_[$id];
		db_delete_data("ko_reservation", "WHERE `id` = '$id'");
		ko_log_diff("delete_res", $r);

		$r = 'OK';
	break;


	case 'getConfig':
		foreach($req['value'] as $key) {
			if(!in_array($key, array('LEUTE_EMAIL_FIELDS'))) continue;
			$r['getConfig'][$key] = ${$key};
		}
	break;

	case "getLdapLeute":
		$login = $req['login'];

		$admin = db_select_data('ko_admin',"WHERE login='".mysqli_real_escape_string(db_get_link(),$login)."'",'id','','',true);
		if(!$admin) {
			$r['status'] = 'error';
			$r['message'] = 'no such user';
			break;
		}

		$access = ko_get_access('leute',$admin['id'],false,true,'login',false);
		if($access['leute']['MAX'] < 1) {
			$r['status'] = 'error';
			$r['message'] = 'login has no access to leute module';
			break;
		}

		apply_leute_filter([],$where,($access['leute']['ALL'] < 1), '', $admin['id']);

		$where .= $req['sql_where'];
		$limit = $req['sql_limit'];

		ko_get_leute($leute,$where,$limit);

		$r['leute'] = $leute;
		$r['status'] = 'success';
	break;

	//Get addresses from ko_leute as a list or as xls or pdf files
	case "getPerson":
	case "getPersonXLS":
	case "getPersonPDF":
		$sort = $req["sql_sort"];
		$sort = $sort ? $sort : "nachname";
		$sortOrder = $req["sql_sortOrder"];
		$sortOrder = $sortOrder ? $sortOrder : "ASC";
		$limit = $req['sql_limit'] ? 'LIMIT '.$req['sql_limit'] : '';
		if($req["sql_columns"]) $columns = explode(",", $req["sql_columns"]);
		else $columns = array();

		$sql = ltrim($req["sql_where"]);
		if($sql) {
			$where = str_replace("WHERE", "AND", $sql);
		} else {
			$ids = format_userinput($req["id"], "intlist");
			//Multiple ids can be supplied separated by comma
			foreach(explode(",", $ids) as $id) {
				if(!$id) continue;
				$use_ids[] = (int)$id;
			}
			$where = "AND `id` IN ('".implode("', '", $use_ids)."')";
		}
		$where = $where;

		//Get all groups and datafields
		ko_get_groups($all_groups);
		$all_datafields = db_select_data("ko_groups_datafields", "WHERE 1=1", "*");

		//manual sort for MODULE-Columns
		if(TRUE === ko_manual_sorting(array($sort))) {
			//Datafields
			if(FALSE !== strpos($sort, ":")) {
				list($prefix, $dfid) = explode(":", $sort);
				$counter = 0;
				foreach(explode(",", $all_groups[substr($prefix, 9)]["datafields"]) as $_dfid) {
					$counter++;
					if($dfid == $_dfid) break;
				}
				$sort = $prefix."datafield".$counter;
			}
			//Make sorting an array, as ko_leute_sort() expects an array for multi column sorting
			$sort = array($sort);
			$sortOrder = array($sortOrder);

			ko_get_leute($all, $where, $limit);
			$_persons = ko_leute_sort($all, $sort, $sortOrder, TRUE, $forceDatafields=TRUE);
		}
		//sorting done directly in MySQL
		else {
			ko_get_leute($_persons, $where, $limit, "", "ORDER BY $sort $sortOrder");
		}

		foreach($_persons as $_person) {
			$person = array();

			//Get the given columns
			if(sizeof($columns) > 0) {
				if($action == 'getPerson' && !in_array("id", $columns)) array_unshift($columns, "id");
				foreach($columns as $col) {
					if(in_array($col, array('groups', 'smallgroups'))) $person[$col.'_raw'] = $_person[$col];
					$value = map_leute_daten($_person[$col], $col, $_person, $all_datafields, $forceDatafields=TRUE, array('MODULEkg_firstOnly' => TRUE));
					if(is_array($value)) {  //Group with datafields is returned as array
						$gid = substr($col, 9);
						$person[$col] = array_shift($value);
						foreach(explode(",", $all_groups[$gid]["datafields"]) as $dfid) {
							if(!$dfid) continue;
							if(in_array("MODULEgrp$gid:$dfid", $columns)) {
								$person[$col.":".$dfid] = ko_unhtml(strip_tags(array_shift($value)));
							} else {
								array_shift($value);
							}
						}
					}
					else {  //normal column
						if(in_array($col, array('picture')) || in_array($col, explode(',', $req['noMapping']))) {  //Don't map picture, as this creates the thumbnail
							$person[$col] = $_person[$col];
						} else if(in_array($col, explode(',', $req['allowHTML']))) {
              $person[$col] = ko_unhtml($value);
						} else {
							$person[$col] = ko_unhtml(strip_tags($value));
						}
					}
				}//foreach(columns as col)
			}//if(sizeof(columns))
			//Get all columns except for group data
			else {
				foreach($_person as $key => $value) {
					if(in_array($key, array('picture'))) {  //Don't map picture, as this creates the thumbnail
						$person[$key] = $value;
					} else {
						$person[$key] = map_leute_daten($value, $key, $_person, $adf, FALSE, array('MODULEkg_firstOnly' => TRUE));
					}
				}
			}

			$r["getPerson"][] = $person;
		}

		//Create XLS
		if($action == "getPersonXLS") {
			//Data
			$temp = $r["getPerson"];
			foreach($temp as $row) {
				$data[] = $row;
			}
			unset($r);

			//Header
			$leute_col_name = ko_get_leute_col_name(FALSE, TRUE, "view", TRUE);
			foreach($columns as $c) {
				if(!$c || $c == "id") continue;
				$header[] = $leute_col_name[$c];
				//add group-datafields if needed
				if(substr($c, 0, 9) == "MODULEgrp" && $all_groups[substr($c, 9)]["datafields"]) {
					list($gid, $fid) = explode(":", substr($c, 9));
					if(!isset($all_datafields[$fid])) continue;
					$header[] = $leute_col_name[$c];
				}
			}//foreach(cols as c)

			//Export
			$filename = $ko_path."download/excel/".getLL("export_filename").strftime("%d%m%Y_%H%M%S", time()).".xlsx";
			$filename = ko_export_to_xlsx($header, $data, $filename, "kOOL");
            $fp = fopen ($filename, "r");
			$r["filename"] = basename($filename);
				if (substr($filename, -1) == 'x') {
					$r["filetype"] = "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";
				} else {
					$r["filetype"] = 'application/vnd.ms-excel';
				}

			$r["filecontent"] = base64_encode(fread($fp, filesize($filename)));
			fclose($fp);
		}
		//Create PDF
		else if($action == "getPersonPDF") {
			$layout_id = $req["pdf_layout_id"];
			if(!$layout_id) return FALSE;
			include($ko_path."leute/inc/leute.inc");

			//Get layout
			$_layout = db_select_data("ko_pdf_layout", "WHERE `id` = '$layout_id'", "*", "", "", TRUE);
			$layout = unserialize($_layout["data"]);

			/* Fake POST settings */
			//Columns
			if(sizeof($layout["columns"]) > 0) {
				$settings["columns"] = "_layout";
			} else {
				$cols = array();
				foreach($columns as $col) {
					if($col == "id") continue;  //Exclude ID
					$cols[] = $col;
				}
				$cols = array_unique($cols);
				$settings["columns"] = $cols;
			}
			//Sorting
			if(!$layout["sort"]) {
				$settings["sort"] = $sort;
				$settings["sort_order"] = $sortOrder;
			}
			//Filter
			//TODO
			if(!$layout["filter"]) {
				$settings["filter"] = array("where" => $where);
			} else {
				$settings["filter"] = "_layout";
			}
			//Header and Footer texts
			$settings["header"]["left"]["text"] = $layout["header"]["left"]["text"];
			$settings["header"]["center"]["text"] = $layout["header"]["center"]["text"];
			$settings["header"]["right"]["text"] = $layout["header"]["right"]["text"];
			$settings["footer"]["left"]["text"] = $layout["footer"]["left"]["text"];
			$settings["footer"]["center"]["text"] = $layout["footer"]["center"]["text"];
			$settings["footer"]["right"]["text"] = $layout["footer"]["right"]["text"];

			//Create PDF
			$group_view = TRUE;
			$filename = ko_export_leute_as_pdf($layout_id, $settings, $force=TRUE);
			$fp = fopen ($filename, "r");
			$r["filename"] = basename($filename);
			$r["filetype"] = "application/pdf";
			$r["filecontent"] = base64_encode(fread($fp, filesize($filename)));
			fclose($fp);
		}
	break;

	default:

		// example requests can be found in the action handlers
		if (!hook_get_action($action, $req, $r)) {
			$r = 'no such action';
		}

		break;

}//switch(action)


//Create JSON response
if(is_array($r)) {
	array_walk_recursive($r,function(&$v,$k) {$v = utf8_encode($v);});
} else {
	$r = utf8_encode($r);
}
$response = json_encode($r);

//Encrypt and return data
if($no_enc) {
	$encrypted = base64_encode($response);
} else {
	$encrypted = base64_encode($crypt->encrypt($response));
}
print $encrypted;

/*
Request:
{
	"language":"de",
	"action":"getLL",
	"key":"ko_leute_famfunction_husband"
}

Return:
{
TODO
}
<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>
<kOOLData>
	<getLL key="ko_leute_famfunction_husband">Mann</getLL>
</kOOLData>
*/



function xmlspecialchars($text) {
	return str_replace('&#039;', '&apos;', htmlspecialchars($text, ENT_QUOTES, 'iso-8859-1'));
}


?>
