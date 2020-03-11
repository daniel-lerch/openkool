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

$ko_path = "../";
$ko_menu_akt = "ical";

include($ko_path."inc/ko.inc");
include($ko_path."reservation/inc/reservation.inc");

$auth = FALSE;
if(isset($_GET["ko_guest"])) {  //Stay with guest user
	$auth = TRUE;
}
else if(isset($_GET['user'])) { //User hash given in URL
	$userhash = $_GET['user'];
	if(strlen($userhash) != 32) exit;
	for($i=0; $i<32; $i++) {
		if(!in_array(substr($userhash, $i, 1), array(1,2,3,4,5,6,7,8,9,0,'a','b','c','d','e','f'))) exit;
	}
	if(!defined('KOOL_ENCRYPTION_KEY') || trim(KOOL_ENCRYPTION_KEY) == '') exit;

	ko_get_logins($logins);
	foreach($logins as $login) {
		if($login['ical_hash'] == $userhash || md5($login['id'].$login['password'].KOOL_ENCRYPTION_KEY) == $userhash) {
			$auth = TRUE;
			$_SESSION['ses_username'] = $login['login'];
			$_SESSION['ses_userid']   = $login['id'];
			ko_init();
		}
	}
	unset($logins);
}
else {
	if (!isset($_SERVER['PHP_AUTH_USER'])) {
		header("WWW-Authenticate: Basic realm=\"kOOL\"");
		header("HTTP/1.1 401 Unauthorized");
		exit;
	} else {
		$user = format_userinput($_SERVER["PHP_AUTH_USER"], "text", TRUE, 32);
		$pw = md5($_SERVER["PHP_AUTH_PW"]);
		$where = "WHERE `login` = '$user' AND `password` = '$pw'";
		$result = db_select_data("ko_admin", $where, "id,login", "", "LIMIT 1", TRUE, TRUE);
		if(!empty($result['id'])) {
			if($result["login"] == $user) {
				$auth = TRUE;
				$_SESSION["ses_username"] = $result["login"];
				$_SESSION["ses_userid"]   = $result["id"];
				ko_init();
			}
		}
	}
}

if(!$auth) {
	header("HTTP/1.1 401 Unauthorized"); exit;
}

if(!ko_module_installed("reservation")) {
	header("HTTP/1.1 404 Not Found"); exit;
}

//Get access rights
ko_get_access('reservation', $_SESSION['ses_userid'], TRUE);
ko_include_kota(array('ko_reservation'));

//Set resitems to be shown set by GET or preset named ical
$use_itemset = FALSE;
if(isset($_GET['items'])) {  //use event groups given in URL
	foreach(explode(',', $_GET['items']) as $item) {
		//Preset
		if(substr($item, 0, 1) == 'p') {
			$presetid = format_userinput(substr($item, 1), 'uint');
			if($presetid) {
				$userpref = db_select_data('ko_userprefs', "WHERE `id` = '$presetid'", '*', '', '', TRUE);
				if($userpref['type'] == 'res_itemset' && ($userpref['user_id'] == '-1' || $userpref['user_id'] == $_SESSION['ses_userid'])) {
					foreach(explode(',', $userpref['value']) as $item_id) {
						if(!$item_id) continue;
						$use_itemset[] = $item_id;
					}
				}
			}
		}
		//Res group
		else if(substr($item, 0, 1) == 'g') {
			$gid = format_userinput(substr($item, 1), 'uint');
			if($gid) {
				$group_items = db_select_data('ko_resitem', "WHERE `gruppen_id` = '$gid'");
				foreach($group_items as $resitem) {
					if(!$resitem['id']) continue;
					$use_itemset[] = $resitem['id'];
				}
			}
		}
		//Single res item
		else {
			$item = intval($item);
			if($item > 0) $use_itemset[] = $item;
		}
	}
}
else if(isset($_GET['own']) && $_GET['own'] == 1) {
	$use_itemset = FALSE;
}
else {  //Get ical preset for the logged in user
	$itemsets = ko_get_userpref($_SESSION["ses_userid"], "", "res_itemset");
	foreach($itemsets as $itemset) {
		if(strtolower($itemset["key"]) == "ical") {
			$use_itemset = explode(",", $itemset["value"]);
		}
	}
}

//get all Res items
ko_get_resitems($resitems);
$use_items = array();
foreach($resitems as $item) {
	if($use_itemset) {
		if(in_array($item['id'], $use_itemset) && ($access['reservation']['ALL'] > 0 || $access['reservation'][$item['id']] > 0)) $use_items[] = $item['id'];
	} else {
		if($access['reservation']['ALL'] > 0 || $access['reservation'][$item['id']] > 0) $use_items[] = $item['id'];
	}
}
//apply filter
$z_where = "";
$perm_filter_start = ko_get_setting("res_perm_filter_start");
$perm_filter_ende  = ko_get_setting("res_perm_filter_ende");
if($access['reservation']['MAX'] < 2 && ($perm_filter_start || $perm_filter_ende)) {
	if($perm_filter_start != "") $z_where .= " AND startdatum >= '".$perm_filter_start."' ";
	if($perm_filter_ende != "") $z_where .= " AND startdatum <= '".$perm_filter_ende."' ";
}
$z_where .= " AND item_id IN ('".implode("','", $use_items)."') ";
//Apply ical deadline from setting
$ical_deadline = ko_get_userpref($_SESSION['ses_userid'], 'res_ical_deadline');
if($ical_deadline >= 0) {
	$start = date('Y-m-d');
} else {
	$start = add2date(date('Y-m-d'), 'month', $ical_deadline, TRUE);
}
$z_where .= " AND startdatum >= '$start' ";

//Set KOTA filter from GET
unset($_SESSION['kota_filter']['ko_reservation']);
foreach($_GET as $k => $v) {
	if(substr($k, 0, 5) != 'kota_') continue;
	$key = substr($k, 5);
	//Check for valid KOTA field
	$ok = FALSE;
	foreach($KOTA['ko_reservation']['_listview'] as $klv) {
		if($klv['name'] === $key && $klv['filter'] === TRUE) $ok = TRUE;
	}
	if(!$ok) continue;

	if(count(explode("||",urldecode($v))) > 1) {
		$_SESSION['kota_filter']['ko_reservation'][$key] = explode("||", urldecode($v));
	} else {
		$_SESSION['kota_filter']['ko_reservation'][$key][0] = urldecode($v);
	}
}
$kota_where = kota_apply_filter('ko_reservation');
if($kota_where != '') $z_where .= " AND ($kota_where) ";


//Filter to only show user's own entries
if(isset($_GET['own']) && $_GET['own'] == 1) {
	$userid = intval($_SESSION['ses_userid']);
	if($userid > 0) {
		$z_where .= " AND `user_id` = '$userid' ";
	} else {
		$z_where .= " AND 1=2 ";
	}
}


//get reservations
ko_get_reservationen($res, $z_where);

$ical = ko_get_ics_for_res($res);


//Set charset to utf-8, but not for google calendar (there seem to be problems with utf-8 for google as of 2010-08)
if(FALSE === strpos($_SERVER['HTTP_USER_AGENT'], 'Googlebot')) {
	$charset = 'utf-8';
	$ical = utf8_encode($ical);
} else {
	$charset = 'latin1';
}


//Output
if (isset($_SERVER["HTTP_USER_AGENT"]) && strpos($_SERVER["HTTP_USER_AGENT"], "MSIE")) {
	// IE cannot download from sessions without a cache
	header("Cache-Control: public");
	// q316431 - Don't set no-cache when over HTTPS
	if (	!isset($_SERVER["HTTPS"]) || $_SERVER["HTTPS"] != "on") {
		header("Pragma: no-cache");
	}
}
else {
	header("Cache-Control: no-cache, must-revalidate");
	header("Pragma: no-cache");
}
header('Content-Type: text/calendar; charset='.$charset, TRUE);
header('Content-Disposition: attachment; filename="kOOLres.ics"');
header("Content-Length: ".strlen($ical));
print $ical;


//Clear session
session_destroy();
unset($_SESSION);
unset($GLOBALS['kOOL']);
?>
