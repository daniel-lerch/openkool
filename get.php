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
	require($ko_path."inc/class.mcrypt.php");
	$crypt = new mcrypt("aes");
	$crypt->setKey(KOOL_ENCRYPTION_KEY);
	$request_xml = $crypt->decrypt(base64_decode($q));
	//Don't allow direct db access to these tables
	$deny_tables = array("ko_admin");
}

//Parse XML into an array
$request = XMLtoArray($request_xml);
$req = $request["kOOLRequest"];

//Get action
$action = $req["action"][0];
if(!$action) exit;

//Check for valid encryption hash if no encryption is used
if($no_enc) {
	if(!$req["encKey"][0] || md5(KOOL_ENCRYPTION_KEY) != $req["encKey"][0]) exit;
}

//Get lang
$_SESSION["lang"] = $req["language"][0];

include($ko_path."inc/ko.inc");

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


	//Get a list of small group roles
	case 'smallgroupRoles':
		foreach($SMALLGROUPS_ROLES as $role) {
			if(!$role || !getLL('kg_roles_'.$role)) continue;
			$r['smallgroupRoles'][] = array('id' => $role, 'name' => getLL('kg_roles_'.$role));
		}
	break;


	//Call ko_save_leute_changes(). Used if changes are being made to address records
	case 'saveLeuteChanges':
		$id = format_userinput($req['id'][0], 'uint');
		if(!$id) continue;
		ko_save_leute_changes($id);
	break;


	//Update group count for given group ids: ids => array(id1, id2, ...)
	case 'updateGroupCount':
		foreach(explode(',', $req['ids'][0]) as $id) {
			$id = format_userinput($id, 'uint');
			if(!$id) continue;
			$group = ko_groups_decode($id, 'group');
			if(!$group['id'] || !$group['maxcount']) continue;
			ko_update_group_count($group['id'], $group['count_role']);
		}
	break;


	case 'sendSMS':
		if(!in_array('sms', $MODULES)) continue;

		$recipients = explode(',', $req['recipients'][0]);
		$text = utf8_decode($req['smstext'][0]);
		$from = $req['from'][0];
		send_aspsms($recipients, $text, $from);
	break;


	case 'trackingDates':
		if(!in_array('tracking', $MODULES)) continue;

		$tid = intval($req['tracking_id'][0]);
		if(!$tid) continue;
		$tracking = db_select_data('ko_tracking', "WHERE `id` = '$tid'", '*', '', '', TRUE);
		if(!$tracking['id'] || $tracking['id'] != $tid) continue;

		$start = $req['start'][0] ? $req['start'][0] : date('Y-m-d');
		$limit = $req['limit'][0] ? $req['limit'][0] : 100;

		include($ko_path.'tracking/inc/tracking.inc');
		$dates = ko_tracking_get_dates($tracking, $start, $limit, $prev, $next, $prev1, FALSE);
		$r['TRACKING_DATES'] = $dates;
	break;


	case 'trackingPeople':
		if(!in_array('tracking', $MODULES)) continue;

		$tid = intval($req['tracking_id'][0]);
		if(!$tid) continue;
		$tracking = db_select_data('ko_tracking', "WHERE `id` = '$tid'", '*', '', '', TRUE);
		if(!$tracking['id'] || $tracking['id'] != $tid) continue;

		$filter = $req['filter'][0];
		if(!$filter) continue;

		include($ko_path.'tracking/inc/tracking.inc');
		$people = ko_tracking_get_people($filter, $dates, $tid, FALSE);
		$r['TRACKING_PEOPLE'] = $people;
	break;


	case 'storeReservation':
		if(!in_array('reservation', $MODULES)) continue;
		$moderated = $req['moderated'][0];
		$res = json_decode($req['data'][0], TRUE);

		//UTF-8 decode, because XML request data is always in UTF-8
		foreach($res as $rid => $r) {
			foreach($r as $k => $v) {
				$res[$rid][$k] = utf8_decode($v);
			}
		}

		include($ko_path.'reservation/inc/reservation.inc');

		if($moderated) {
			ko_res_store_moderation($res, FALSE, $double_error);
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
		if(!in_array('reservation', $MODULES)) continue;

		$id = intval($req['id'][0]);
		if(!$id) {
			$r = array('error' => 1, 'error_txt' => 'No reservation found');
			continue;
		}

		include($ko_path.'reservation/inc/reservation.inc');

		ko_get_res_by_id($id, $r_); $r = $r_[$id];
		db_delete_data("ko_reservation", "WHERE `id` = '$id'");
		ko_log_diff("delete_res", $r);

		$r = 'OK';
	break;


	//Make a SELECT query to the kOOL DB
	case "DB_SELECT":
		if(!$req["table"][0] || !$req["where"][0] || in_array($req["table"][0], $deny_tables)) continue;

		$cols   = isset($req["columns"][0]) ? $req["columns"][0] : "*";
		$single = isset($req["single"][0])  ? $req["single"][0]  : FALSE;

		$res = db_select_data($req["table"][0], $req["where"][0], $cols, $req["order"][0], $req["limit"][0], $req["single"][0]);
		foreach($res as $rid => $rv) {
			$r["DB_SELECT"][] = array("content" => $rv);
		}
	break;


	//Get the columns for a kOOL DB table
	case "DB_GET_COLUMNS":
		if(!$req["table"][0] || in_array($req["table"][0], $deny_tables)) continue;

		$res = db_get_columns($req["table"][0], $req["field"][0]);
		foreach($res as $rid => $rv) {
			$r["DB_GET_COLUMNS"][] = array("content" => $rv);
		}
	break;


	//Call a function
	case "FCN":
		$fcn = $req["function"][0];
		if(function_exists($fcn)) {
			$f = call_user_func($fcn, $req["value"][0]);
			$r[$fcn][] = array("content" => $f);
		}
	break;


	case 'getConfig':
		foreach($req['value'] as $key) {
			if(!in_array($key, array('LEUTE_EMAIL_FIELDS'))) continue;
			$r['getConfig'][] = array('key' => $key, 'content' => json_encode(${$key}));
		}
	break;


	//Get addresses from ko_leute as a list or as xls or pdf files
	case "getPerson":
	case "getPersonXLS":
	case "getPersonPDF":
		$sort = $req["sql_sort"][0];
		$sort = $sort ? $sort : "nachname";
		$sortOrder = $req["sql_sortOrder"][0];
		$sortOrder = $sortOrder ? $sortOrder : "ASC";
		if($req["sql_columns"][0]) $columns = explode(",", $req["sql_columns"][0]);
		else $columns = array();

		$sql = ltrim($req["sql_where"][0]);
		if($sql) {
			$where = str_replace("WHERE", "AND", $sql);
		} else {
			$ids = format_userinput($req["id"][0], "intlist");
			//Multiple ids can be supplied separated by comma
			foreach(explode(",", $ids) as $id) {
				if(!$id) continue;
				$use_ids[] = (int)$id;
			}
			$where = "AND `id` IN ('".implode("', '", $use_ids)."')";
		}
		$where = utf8_decode($where);

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

			ko_get_leute($all, $where);
			$_persons = ko_leute_sort($all, $sort, $sortOrder, TRUE, $forceDatafields=TRUE);
		}
		//sorting done directly in MySQL
		else {
			ko_get_leute($_persons, $where, "", "", "ORDER BY $sort $sortOrder");
		}

		foreach($_persons as $_person) {
			$person = array();

			//Get the given columns
			if(sizeof($columns) > 0) {
				if(!in_array("id", $columns)) array_unshift($columns, "id");
				foreach($columns as $col) {
					if(FALSE !== strpos($col, ":")) continue;  //Datafields (MODULEgrp000001:000002) are being return with their group (MODULEgrp000001)

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
						if(in_array($col, array('picture')) || in_array($col, explode(',', $req['noMapping'][0]))) {  //Don't map picture, as this creates the thumbnail
							$person[$col] = $_person[$col];
						} else if(in_array($col, explode(',', $req['allowHTML'][0]))) {
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

			$r["getPerson"][] = array("content" => $person);
		}

		//Create XLS
		if($action == "getPersonXLS") {
			//Data
			$temp = $r["getPerson"];
			foreach($temp as $_row) {
				$row = $_row["content"];
				unset($row["id"]);
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
			list($layout_id) = $req["pdf_layout_id"];
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
		exit;
}//switch(action)


//Create XML response
$response = generateXMLResponse($r);

//Encrypt and return data
if($no_enc) {
	$encrypted = base64_encode($response);
} else {
	$encrypted = base64_encode($crypt->encrypt($response));
}
print $encrypted;

/*
Request:
<?xml version="1.0" encoding="iso-8859-1" standalone="yes" ?>
<kOOLRequest>
	<language>de</language>
	<action>getLL</action>
	<key>ko_leute_famfunction_husband</key>
</kOOLRequest>

Return:
<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>
<kOOLData>
	<getLL key="ko_leute_famfunction_husband">Mann</getLL>
</kOOLData>
*/


function generateXMLResponse($data) {
	$xml  = '<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>'."\n";
	$xml .= "<kOOLData>\n";
	foreach($data as $key => $entries) {
		if(is_array($entries)) {
			foreach($entries as $entry) {
				$args = "";
				foreach($entry as $arg => $value) {
					if($arg == "content") continue;
					$args .= " ".$arg.'="'.utf8_encode(xmlspecialchars($value)).'"';
				}
				if(is_array($entry["content"])) {
					$xml .= "\t<$key".$args.">\n";
					foreach($entry["content"] as $ekey => $evalue) {
						$xml .= "\t\t<$ekey>".utf8_encode(xmlspecialchars($evalue))."</$ekey>\n";
					}
					$xml .= "\t</$key>\n";
				} else {
					$xml .= "\t<$key".$args.">".utf8_encode(xmlspecialchars($entry["content"]))."</$key>\n";
				}
			}
		} else {
			$xml .= "\t<$key>".utf8_encode($entries)."</$key>\n";
		}
	}
	$xml .= "</kOOLData>\n";

	return $xml;
}//generateXMLResponse()



function xmlspecialchars($text) {
	return str_replace('&#039;', '&apos;', htmlspecialchars($text, ENT_QUOTES));
}



/**
 * Wandelt eine XML-Sequenz in ein assoziatives Array um
 * @param string XML-Sequenz
 * @return array Array
 */
function XMLtoArray($XML) {
	$XML = utf8_encode($XML);
  $xml_parser = xml_parser_create();
	xml_parser_set_option($xml_parser, XML_OPTION_CASE_FOLDING, false);
  xml_parse_into_struct($xml_parser, $XML, $vals);
  xml_parser_free($xml_parser);
  // wyznaczamy tablice z powtarzajacymi sie tagami na tym samym poziomie
  $_tmp='';
  foreach ($vals as $xml_elem) {
    $x_tag=$xml_elem['tag'];
    $x_level=$xml_elem['level'];
    $x_type=$xml_elem['type'];
    if ($x_level!=1 && $x_type == 'close') {
      if (isset($multi_key[$x_tag][$x_level]))
        $multi_key[$x_tag][$x_level]=1;
      else
        $multi_key[$x_tag][$x_level]=0;
    }
    if ($x_level!=1 && $x_type == 'complete') {
      if ($_tmp==$x_tag)
        $multi_key[$x_tag][$x_level]=1;
      $_tmp=$x_tag;
    }
  }
  // jedziemy po tablicy
  foreach ($vals as $xml_elem) {
    $x_tag=$xml_elem['tag'];
    $x_level=$xml_elem['level'];
    $x_type=$xml_elem['type'];
    if ($x_type == 'open')
      $level[$x_level] = $x_tag;
    $start_level = 1;
    $php_stmt = '$xml_array';
    if ($x_type=='close' && $x_level!=1)
      $multi_key[$x_tag][$x_level]++;
    while($start_level < $x_level) {
      $php_stmt .= '[$level['.$start_level.']]';
      if (isset($multi_key[$level[$start_level]][$start_level]) && $multi_key[$level[$start_level]][$start_level])
        $php_stmt .= '['.($multi_key[$level[$start_level]][$start_level]-1).']';
      $start_level++;
    }
    $add='';
    if (!isset($multi_key2[$x_tag][$x_level]))
      $multi_key2[$x_tag][$x_level]=0;
    else
      $multi_key2[$x_tag][$x_level]++;
    $add='['.$multi_key2[$x_tag][$x_level].']';
    if (isset($xml_elem['value']) && trim($xml_elem['value'])!='' && !array_key_exists('attributes',$xml_elem)) {
      if ($x_type == 'open')
        $php_stmt_main=$php_stmt.'[$x_type]'.$add.'[\'content\'] = $xml_elem[\'value\'];';
      else
        $php_stmt_main=$php_stmt.'[$x_tag]'.$add.' = $xml_elem[\'value\'];';
      eval($php_stmt_main);
    }
    if (array_key_exists('attributes',$xml_elem)) {
      if (isset($xml_elem['value'])) {
        $php_stmt_main=$php_stmt.'[$x_tag]'.$add.'[\'content\'] = $xml_elem[\'value\'];';
        eval($php_stmt_main);
      }
      foreach ($xml_elem['attributes'] as $key=>$value) {
        $php_stmt_att=$php_stmt.'[$x_tag]'.$add.'[$key] = $value;';
        eval($php_stmt_att);
      }
    }
  }
  return $xml_array;
}//XMLtoArray()
?>
