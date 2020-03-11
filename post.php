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
	'res_1' => "Invalid date, please enter in the form 14.3.2008.",
	'res_2' => "Invalid time.",
	'res_3' => "The given code doesn't match the selected reservation.",
	'res_4' => "The following reservations could not be saved because they are colliding with existing ones. %s",
	'res_5' => "No stop time given. For reservations that last longer than one day, you have to enter start and stop times.",
	'res_6' => "Your permissions are not sufficient to assign this item to the selected group.",
	'res_7' => "No events found in the given time span.",
	'res_8' => "No reservations selected.",
	'res_9' => "No object selected.",
	'res_10' => "No objects selected.",
	'res_11' => "No group selected. Every object has to belong to a group.",
	'res_12' => "Not all obligatory fields are completed.",
	'res_13' => "No encryption key set in ko-config.php",
	'res_14' => "Enddate is before startdate which is not allowed.",
	'res_15' => "Invalid format of intermediate times",
	'res_58' => "Invalid columns selected, can not continue.",
	'res_59' => "Can not continue.",
	'res_60' => "Please make sure you fill in all the mandatory fields.",
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

	array_walk_recursive($response, 'utf8_encode_array');
	print json_encode($response);
	exit;
}

error_reporting(1);
$ko_path = "./";
$ko_menu_akt = 'post.php';


require($ko_path."config/ko-config.php");
require($ko_path."inc/ko.inc");
require_once($ko_path."inc/kotafcn.php");



/*
// Small testsuite, set $test to {1..11}
$test = 11;

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
// success
else if ($test == 10) {
	$_POST['action'] = 'newgroupsubscription';
	$requestArray = array(
		'_group_id' => 'g001252',
		'_bemerkung' => 'Test Bemerkung',
		'_moderated' => 1,
		'vorname' => 'John',
		'nachname' => 'Doe',
	);
	$_POST['request'] = json_encode($requestArray);
	$_POST['key'] = md5($_POST['action'] . KOOL_ENCRYPTION_KEY . $_POST['request']);
}
// error
else if ($test == 11) {
	$_POST['action'] = 'newgroupsubscription';
	$requestArray = array(
		'_group_id' => 'g001252',
		'_bemerkung' => 'Test Bemerkung',
		'_moderated' => 0,
		'vorname' => 'John',
		'nachname' => 'Doe',
	);
	$_POST['request'] = json_encode($requestArray);
	$_POST['key'] = md5($_POST['action'] . KOOL_ENCRYPTION_KEY . $_POST['request']);
}

//set $request['_moderated'] to 0 or 1
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

		$moderated = $request['_moderated'];
		unset($request['_moderated']);

		$overflow = isset($request['_overflow']) ? $request['_overflow'] : $moderated;
		unset($request['_overflow']);

		require_once($ko_path.'subscription/inc/subscription.inc');
		ko_log('subscription_post',ko_subscription_get_log_message($request));
		try {
			$leute_id = ko_subscription_store_subscription($request,$moderated,$overflow);
		} catch(kOOL\Subscription\FormException $ex) {
			responseError($ex->getError(),$ex->getMessage());
		}

		$response['status'] = 'success';
		if($leute_id) $response['leute_id'] = $leute_id;

		break;

	case 'newreservation':
		require($ko_path."reservation/inc/reservation.inc");

		$moderation = format_userinput($request['_moderate'], 'uint');
		unset($request['_moderate']);

		kota_process_data("ko_reservation", $request, "post");
		if($request["enddatum"] == "0000-00-00" || trim($request["enddatum"]) == "") $request["enddatum"] = $request["startdatum"];

		$err = check_entries($request);
		if($err > 0) {
			responseError('res_'.$err);
		}

		$sendModerationEmails = TRUE;
		$res_confirm_mailtext = "";
		$res_data = $request;  //Data for the single item
		$data = $mod_data = array();   //Will hold all res_datas for all items on all repeated days
		$items = db_select_data("ko_resitem", "WHERE 1=1", "*");

		//Check for valid item_id and access rights
		$item_id = format_userinput($request['item_id'], "uint");
		$res_data["item_id"] = $item_id;

		//Loop through all repetitions
		if(FALSE === ko_res_check_double($item_id,$request['startdatum'], $request['enddatum'], $request["startzeit"], $request["endzeit"],$double_error_txt)) {  //Check for double
			$response['conflict'] = 1;
			$response['message'] = $request['startdatum'].': '.getLL('res_collision').' '.$double_error_txt.'';
			if (!$moderation) {
				$response['status'] = 'error';
			} else { // save as moderated res
				$res_data["startdatum"] = sql_datum($request['startdatum']);
				$res_data["enddatum"] = $request['enddatum'] != "" ? sql_datum($request['enddatum']) : $res_data["startdatum"];

				$mod_data[] = $res_data;
				$sendModerationEmails = FALSE;
				$response['status'] = 'success';
			}
		} else {
			$res_data["startdatum"] = sql_datum($request['startdatum']);
			$res_data["enddatum"] = $request['enddatum'] != "" ? sql_datum($request['enddatum']) : $res_data["startdatum"];

			if($moderation == 0) {
				//No moderation needed
				$data[] = $res_data;
			} else {
				//Moderation needed
				$mod_data[] = $res_data;
			}
			$response['status'] = 'success';
		}//if..else(ko_res_check_double())


		//Store reservations
		if(sizeof($data) > 0) {
			ko_res_store_reservation($data,null);
		}
		//Store moderations
		if(sizeof($mod_data) > 0) {
			ko_res_store_moderation($mod_data, $sendModerationEmails);
		}

		break;

	case 'postFile':
		$checkedFiles = [];
		foreach($_FILES as $file) {
			if(isset($request[$file['name']])) {
				if(md5_file($file['tmp_name']) == $request[$file['name']]) {
					$checkedFiles[] = $file;
				} else {
					reportError(null, 'checksum for file '.$file['name'].' is incorrect.');
				}
			}
		}
		foreach($checkedFiles as $file) {
			$path = 'my_images/'.$file['name'];
			move_uploaded_file($file['tmp_name'],$BASE_PATH.$path);
			$response[$file['name']] = $path;
		}
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

