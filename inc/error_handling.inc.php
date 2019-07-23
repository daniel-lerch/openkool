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

// Fehlerbehandlungsfunktion
function kOOL_ErrorHandler($errno, $errstr, $errfile, $errline) {
	global $ko_path, $mysql_db, $ko_menu_akt, $FILE_LOGO_BIG;

	$die = FALSE;

	switch($errno) {
		case E_ERROR:
		case E_USER_ERROR:
			print '<table width="50%" align="center" class="error">';
			print '<tr><td><img src="'.$ko_path.$FILE_LOGO_BIG.'/></td><th>'.getLL("error_title").'</th></tr>';
			$basic = get_basic_error_information($errno, $errstr, $errfile, $errline);
			$additional = get_additional_error_information();
			foreach ($basic as $key => $value) {
				print '<tr><td>' . $key . '</td><td>' . $value . '</td></tr>';
			}
			if (!defined("WARRANTY_EMAIL") && WARRANTY_EMAIL != "" && $ko_menu_akt != "install") {
				print '<tr><td colspan="2">';
				print getLL("error_msg_1").'<br />';
				print sprintf(getLL("error_msg_2"), WARRANTY_EMAIL).'<br /><br />';
				print getLL("error_msg_3");
				print '</td></tr>';
				
				$mailtext = 'OpenKool Error Report ' . strftime('%d.%m.%Y %H:%M:%S') . "\n\n";
				foreach ($basic as $key => $value) {
					$mailtext .= $key . ': ' . $value;
				}
				foreach ($additional as $key => $value) {
					$mailtext .= $key . ': ' . $value;
				}
				ko_send_mail(WARRANTY_EMAIL, WARRANTY_EMAIL, 'OpenKool Error', $mailtext);
			}
			print '<tr><td colspan="2">' . getLL("error_msg_4") . '</td></tr>';
			foreach ($additional as $key => $value) {
				print '<tr><td>' . $key . '</td><td>' . $value . '</td></tr>';
			}
			print '</table>';
			$die = TRUE;
		break;

		case E_WARNING:
		case E_USER_WARNING:
			//print '<b>kOOL Warnung</b>: '.$errno.': '.$errstr.' in '.$errfile.' ('.$errline.')<br />';
		break;

		case E_NOTICE:
		case E_USER_NOTICE:
			//print '<b>kOOL Notice</b>: '.$errno.': '.$errstr.' in '.$errfile.' ('.$errline.')<br />';
		break;
	}

	if($die) exit();
}

/**
 * Collects basic information for an error report
 * @return array: An associative array of important properties and their values
 */
function get_basic_error_information($errno, $errstr, $errfile, $errline) {
	return array(
		'Error No.' => $errno,
		'Error Msg' => $errstr,
		'Error File' => $errfile,
		'Error Line' => $errline,
		'User ID' => $_SESSION["ses_userid"],
		'DB Name' => $mysql_db,
		'IP' => $ko_get_user_ip()
	);
}

/**
 * Collects additional information for an error report
 * @return array: An associative array of important properties and their values
 */
function get_additional_error_information() {
	return array(
		'Stacktrace' => debug_get_backtrace(),
		'$_GET' => var_export($_GET, TRUE),
		'$_POST' => var_export($_POST, TRUE),
		'$_SESSION' => var_export($_SESSION, TRUE),
		'$_COOKIE' => var_export($_COOKIE, TRUE),
		'$_SERVER' => var_export($_SERVER, TRUE)
	);
}

function debug_get_backtrace() {
	$trace = '';
	foreach(debug_backtrace() as $k => $v) {
		if($v['function'] == "include" || $v['function'] == "include_once" || $v['function'] == "require_once" || $v['function'] == "require") {
			$trace .= "#".$k." ".$v['function']."(".$v['args'][0].") called at [".$v['file'].":".$v['line']."]\n";
		} else {
			$trace .= "#".$k." ".$v['function']."() called at [".$v['file'].":".$v['line']."]\n";
		}
	} 
	return $trace;
}


// auf die benutzerdefinierte Fehlerbehandlung umstellen
$old_error_handler = set_error_handler("kOOL_ErrorHandler");
?>
