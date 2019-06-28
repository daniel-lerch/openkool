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

function responseError($id, $section = null) {
	$response = array();
	$response['status'] = 'error';
	if ($section === null) {
		$llKey = 'error_post_';
	}
	else {
		$llKey = 'error_' . 'post_' . $section . '_';
	}
	$response['message'] = getLL($llKey . $id);
	print json_encode($response);
	exit;
}

error_reporting(0);
$ko_path = "./";
$ko_menu_akt = 'post.php';


require($ko_path."config/ko-config.php");
require($ko_path."inc/ko.inc");



/*
// Small testsuite, set $test to {1..9}
$test = 0;

// error
if ($test == 1) {
	$_POST['action'] = 'sddas';
	$_POST['request'] = '';
	$_POST['key'] = md5($_POST['action'] . KOOL_ENCRYPTION_KEY . $_POST['request']);
}
// error
else if ($test == 2) {
	$_POST['action'] = 'newgroupsubscription';
	$_POST['request'] = '';
	$_POST['key'] = md5($_POST['action'] . KOOL_ENCRYPTION_KEY . $_POST['request']);
}
// error
else if ($test == 3) {
	$_POST['action'] = 'newgroupsubscription';
	$_POST['request'] = '';
	$_POST['key'] = md5($_POST['action'] . KOOL_ENCRYPTION_KEY . 'sdfsdf' . $_POST['request']);
}
// success
else if ($test == 4) {
	$_POST['action'] = 'newgroupsubscription';
	$requestArray = array(
		'_group_id' => 'g000607',
		'_bemerkung' => 'Test Bemerkung',
		'vorname' => 'John',
		'nachname' => 'Doe',
	);
	$_POST['request'] = json_encode($requestArray);
	$_POST['key'] = md5($_POST['action'] . KOOL_ENCRYPTION_KEY . $_POST['request']);
}
// error
else if ($test == 5) {
	$_POST['action'] = 'newgroupsubscription';
	$requestArray = array(
		'_group_id' => '000607',
		'_bemerkung' => 'Test Bemerkung',
		'vorname' => 'John',
		'nachname' => 'Doe',
	);
	$_POST['request'] = json_encode($requestArray);
	$_POST['key'] = md5($_POST['action'] . KOOL_ENCRYPTION_KEY . $_POST['request']);
}
// success
else if ($test == 6) {
	$_POST['action'] = 'newgroupsubscription';
	$requestArray = array(
		'_group_id' => 'g000607',
		'_bemerkung' => 'Test Bemerkung',
		'vorname' => 'John',
		'nachname' => 'Doe',
		'_group_datafields' => array('000008' => utf8_encode('Test Eintrag für Datenfeld "test_mod"')),
	);
	$_POST['request'] = json_encode($requestArray);
	$_POST['key'] = md5($_POST['action'] . KOOL_ENCRYPTION_KEY . $_POST['request']);
}
// error
else if ($test == 7) {
	$_POST['action'] = 'newgroupsubscription';
	$requestArray = array(
		'_group_id' => 'g000607',
		'_bemerkung' => 'Test Bemerkung',
		'vorname' => 'John',
		'nachname' => 'Doe',
		'_group_datafields' => array('000006' => utf8_encode('Test Eintrag für Datenfeld "test_mod"')),
	);
	$_POST['request'] = json_encode($requestArray);
	$_POST['key'] = md5($_POST['action'] . KOOL_ENCRYPTION_KEY . $_POST['request']);
}
// error
else if ($test == 8) {
	$_POST['action'] = 'newgroupsubscription';
	$requestArray = array(
		'_group_id' => 'g000607:r000400',
		'_bemerkung' => 'Test Bemerkung',
		'vorname' => 'John',
		'nachname' => 'Doe',
		'_group_datafields' => array('000005' => 'Test Eintrag fur Datenfeld "test_mod"'),
	);
	$_POST['request'] = json_encode($requestArray);
	$_POST['key'] = md5($_POST['action'] . KOOL_ENCRYPTION_KEY . $_POST['request']);
}
// success
else if ($test == 9) {
	$_POST['action'] = 'newgroupsubscription';
	$requestArray = array(
		'_group_id' => 'g000607:r000001',
		'_bemerkung' => 'Test Bemerkung',
		'vorname' => 'John',
		'nachname' => 'Doe',
	);
	$_POST['request'] = json_encode($requestArray);
	$_POST['key'] = md5($_POST['action'] . KOOL_ENCRYPTION_KEY . $_POST['request']);
}
*/




//Get request from _POST
$action = $_POST['action'];
$requestJSON = $_POST['request'];
$key = $_POST['key'];

if (!isset($action) || ! isset($key)) {
	responseError(1);
}

// Check if encryption is set
if(!KOOL_ENCRYPTION_KEY) {
	responseError(2);
}

// Check if request is valid
$checkSum = md5($action . KOOL_ENCRYPTION_KEY . $requestJSON);
if ($checkSum != $key) {
	responseError(1);
}

$response = array();
switch ($action) {
	case 'newgroupsubscription':

		if ($requestJSON == '') {
			responseError(1);
		}

		$request = json_decode($requestJSON, true);

		// Decode UTF8 values
		array_walk_recursive($request, "utf8_decode_array");

		$groupString = format_userinput($request['_group_id'], 'alphanumlist');

		if ($groupString == '') {
			responseError(1, $action);
		}

		if (strpos($groupString, ':') !== false) {

			list($groupId, $roleId) = explode(':', $groupString);
			if (substr($groupId, 0, 1) != 'g') {
				responseError(1, $action);
			}
			$groupId = substr($groupId, 1);
			if (substr($roleId, 0, 1) != 'r') {
				responseError(2, $action);
			}
			$roleId = substr($roleId, 1);
		}
		else {
			if (substr($groupString, 0, 1) != 'g') {
				responseError(1, $action);
			}
			$groupId = substr($groupString, 1);
			$roleId = null;
		}

		$group = db_select_data('ko_groups', 'where id = ' . $groupId, 'id, roles, datafields, maxcount, count', '', '', TRUE, TRUE);

		// Check if group id is valid
		if ($group === null) {
			responseError(1, $action);
		}

		// check if role id is valid
		if ($roleId !== null && !in_array($roleId, explode(',', $group['roles']))) {
			responseError(2, $action);
		}

		// Check if group if full
		if ($group['maxcount'] != 0 && $group['count'] >= $group['maxcount']) {
			responseError(3, $action);
		}

		// Check if all fields exist in ko_leute
		$dbColumnsRaw = db_get_columns("ko_leute");
		$dbColumns = array();
		foreach ($dbColumnsRaw as $dbColumnRaw) $dbColumns[] = $dbColumnRaw['Field'];
		foreach ($request as $requestColumn => $v) {
			if (substr($requestColumn, 0, 1) != '_' && !in_array($requestColumn, $dbColumns)) {
				responseError(4, $action);
			}
		}

		// Check if datafields exist
		$dbDataFields = explode(',', $group['datafields']);
		$requestDataFields = $request['_group_datafields'];
		foreach ($requestDataFields as $dataFieldName => $dataFieldValue) {
			if ($dataFieldName != '' && !in_array($dataFieldName, $dbDataFields)) {
				responseError(6, $action);
			}
		}


		// Insert data into ko_leute_mod
		$allowFilling = array('_bemerkung', '_group_id', '_group_datafields');
		$insert = array();
		foreach ($request as $requestColumn => $v) {
			// Check if column is writable
			if (substr($requestColumn, 0, 1) == '_' && !in_array($requestColumn, $allowFilling)) {
				responseError(5, $action);
			}

			if ($requestColumn == '_group_datafields') {
				$insert[$requestColumn] = serialize($v);
			}
			else {
				$insert[$requestColumn] = $v;
			}
		}
		$insert['_crdate'] = date("Y-m-d H:i:s");

		// Insert new entry into ko_leute_mod
		db_insert_data('ko_leute_mod', $insert);


		$response['status'] = 'success';

		break;
	default:
		responseError(1);

		break;
}

print json_encode($response);

