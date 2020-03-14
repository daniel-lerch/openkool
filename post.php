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


$POST_ERRORS_MESSAGES = array(
	'1' => 'Invalid request',
	'2' => 'kOOL encryption key is not set',
	'3' => 'Invalid request key',
	'gs_1' => 'Invalid group',
	'gs_2' => 'Invalid role',
	'gs_3' => 'Group is full',
	'gs_4' => 'Your request contains invalid fields',
	'gs_5' => 'You are not allowed to write to some fields',
	'gs_6' => 'Your request contains invalid group datafields',
);


function responseError($id=null, $message=null) {
	global $POST_ERRORS_MESSAGES;
	$response = array();
	$response['status'] = 'error';
	if ($message === null) {
		if ($id !== null) {
			$response['message'] = $POST_ERRORS_MESSAGES[$id];
		} else {
			$response['message'] = 'none';
		}
	} else {
		$response['message'] = $message;
	}

	print json_encode($response);
	exit;
}

error_reporting(1);
$ko_path = "./";
$ko_menu_akt = 'post.php';


require($ko_path."config/ko-config.php");
require($ko_path."inc/ko.inc");
require($ko_path."inc/kotafcn.php");



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

set $request['_moderated'] to 0 or 1
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
	responseError(3);
}

if ($requestJSON) {
	$request = json_decode($requestJSON, true);
	array_walk_recursive($request, 'utf8_decode_array');
} else {
	$request = NULL;
}

ko_log('post.php', "action: {$action}, request: ".utf8_decode($requestJSON));

$response = array();
switch ($action) {
	case 'newgroupsubscription':

		if ($request === null) {
			responseError(1);
		}

		$groupString = format_userinput($request['_group_id'], 'alphanumlist');

		if ($groupString == '') {
			responseError(1, $action);
		}

		if (strpos($groupString, ':') !== false) {

			list($groupId, $roleId) = explode(':', $groupString);
			if (substr($groupId, 0, 1) != 'g') {
				responseError('gs_1');
			}
			$groupId = substr($groupId, 1);
			if (substr($roleId, 0, 1) != 'r') {
				responseError('gs_2');
			}
			$roleId = substr($roleId, 1);
		}
		else {
			if (substr($groupString, 0, 1) != 'g') {
				responseError('gs_1');
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
			responseError('gs_2');
		}

		// Check if group is full
		if ($group['maxcount'] != 0 && $group['count'] >= $group['maxcount']) {
			responseError('gs_3');
		}

		// Check if all fields exist in ko_leute
		$dbColumnsRaw = db_get_columns("ko_leute");
		$dbColumns = array();
		foreach ($dbColumnsRaw as $dbColumnRaw) $dbColumns[] = $dbColumnRaw['Field'];
		foreach ($request as $requestColumn => $v) {
			if (substr($requestColumn, 0, 1) != '_' && !in_array($requestColumn, $dbColumns)) {
				responseError('gs_4');
			}
		}

		// Check if datafields exist
		$dbDataFields = explode(',', $group['datafields']);
		$requestDataFields = $request['_group_datafields'];
		foreach ($requestDataFields as $dataFieldName => $dataFieldValue) {
			if ($dataFieldName != '' && !in_array($dataFieldName, $dbDataFields)) {
				responseError('gs_6');
			}
		}

		$moderated = $request['_moderated'];
		unset($request['_moderated']);

		//Moderation (store in ko_leute_mod)
		if($moderated) {

			// Insert data into ko_leute_mod
			$allowFilling = array('_bemerkung', '_group_id', '_group_datafields', '_additional_group_ids', '_additional_group_datafields');

			$insert = array();
			foreach ($request as $requestColumn => $v) {
				// Check if column is writable
				if (substr($requestColumn, 0, 1) == '_' && !in_array($requestColumn, $allowFilling)) {
					responseError('gs_5');
				}

				if ($requestColumn == '_group_datafields') {
					$insert[$requestColumn] = serialize($v);
				}
				else if (in_array($requestColumn, array('_additional_group_ids', '_additional_group_datafields'))) {
					$insert[$requestColumn] = serialize($v);
				}
				else {
					$insert[$requestColumn] = $v;
				}
			}
			$insert['_crdate'] = date("Y-m-d H:i:s");

			// Insert new entry into ko_leute_mod
			db_insert_data('ko_leute_mod', $insert);
		}

		//No moderation (store in ko_leute and ko_leute_revisions)
		else {
			$insert = array();
			$gdfs = array();

			//Normal address columns
			foreach($request as $requestColumn => $v) {
				if(substr($requestColumn, 0, 1) == '_') continue;
				$insert[$requestColumn] = $v;
			}
			$insert['crdate'] = date('Y-m-d H:i:s');
			$insert['lastchange'] = date('Y-m-d H:i:s');

			//Main group
			if($request['_group_id']) {
				$fullGid = ko_groups_decode($request['_group_id'], 'full_gid');
				$group_id = ko_groups_decode($request['_group_id'], 'group_id');
				$insert['groups'][] = $fullGid;

				//datafields
				foreach($request['_group_datafields'] as $gdfid => $gdfvalue) {
					$gdfs[] = array('group_id' => $group_id, 'datafield_id' => $gdfid, 'value' => $gdfvalue);
				}
			}

			//Additional groups
			if($request['_additional_group_ids']) {
				foreach($request['_additional_group_ids'] as $agid => $checked) {
					if(!$checked) continue;
					$fullGid = ko_groups_decode($agid, 'full_gid');
					$group_id = ko_groups_decode($fullGid, 'group_id');
					$insert['groups'][] = $fullGid;

					//gdf
					foreach($request['_additional_group_datafields'][$group_id] as $gdfid => $gdfvalue) {
						$gdfs[] = array('group_id' => $group_id, 'datafield_id' => $gdfid, 'value' => $gdfvalue);
					}
				}
			}

			$insert['groups'] = implode(',', $insert['groups']);


			//Store in ko_leute
			$leute_id = db_insert_data('ko_leute', $insert);

			//leute_revision
			//TODO: Add remarks
			db_insert_data('ko_leute_revisions', array('leute_id' => $leute_id, 'reason' => 'groupsubscription', 'crdate' => date('Y-m-d H:i:s'), 'group_id' => $request['_group_id']));

			//gdfs
			foreach($gdfs as $gdf) {
				$gdf['person_id'] = $leute_id;
				//Check on current value and update, or insert new entry
				if(db_get_count('ko_groups_datafields_data', 'id', " AND `group_id` = '".$gdf['groups_id']."' AND `datafield_id` = '".$gdf['datafield_id']."' AND `person_id` = '".$gdf['person_id']."'") > 0) {
					db_update_data('ko_groups_datafields_data', "WHERE `group_id` = '".$gdf['groups_id']."' AND `datafield_id` = '".$gdf['datafield_id']."' AND `person_id` = '".$gdf['person_id']."'", array('value' => $gdf['value']));
				} else {
					db_insert_data('ko_groups_datafields_data', $gdf);
				}
			}
		}//if..else($request['_moderated'])

		$response['status'] = 'success';
		if($leute_id) $response['leute_id'] = $leute_id;

		break;
	default:

		// example requests can be found in the action handlers
		if (!hook_post_action($action, $request, $response)) responseError(1);

		break;
}


/**
 * the response is json encoded and contains a status (success|error) and in case of error a message
 */
array_walk_recursive($response, 'utf8_encode_array');
print json_encode($response);

