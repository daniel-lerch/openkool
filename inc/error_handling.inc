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

// Fehlerbehandlungsfunktion
function kOOL_ErrorHandler($errno, $errstr, $errfile, $errline) {
	global $ko_path, $mysql_db, $ko_menu_akt, $FILE_LOGO_BIG;

	$do_mail = FALSE;
	$die = FALSE;

	switch($errno) {
		case E_ERROR:
		case E_USER_ERROR:
			print '<table width="50%" align="center" class="error">';
			print '<tr><td><img src="'.$ko_path.$FILE_LOGO_BIG.'" width="200" /><br /><h2>'.getLL("error_title").'</h2></td>';
			print '<td>';
			print "<h3>".getLL("error_header")."</h3>";

			if( (!defined("WARRANTY_EMAIL") || WARRANTY_EMAIL == "" ) || $ko_menu_akt == "install") {
				print "Error-Nr.: " . $errno . "<br />";
				print "Error-Str.: " . $errstr . "<br />";
				print "Error-File: " . $errfile . "<br />";
				print "Error-Line: " . $errline . "<br />";
				print '</td></tr></table>';

				$die = TRUE;
			} else {
				print getLL("error_msg_1").'<br />';
				print sprintf(getLL("error_msg_2"), WARRANTY_EMAIL).'<br /><br />';;
				print getLL("error_msg_3").'<br />';
				print '</td></tr></table>';

				$do_mail = TRUE;
				$die = TRUE;
			}
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

	if($do_mail) {
		$mailtxt  = "kOOL Error Report: " . strftime($GLOBALS["DATETIME"]["DdMY"]."  -  %T") . "\n\n";
		$mailtxt .= "Error-Nr.: " . $errno . "\n\n";
		$mailtxt .= "Error-Str.: " . $errstr . "\n\n";
		$mailtxt .= "Error-File: " . $errfile . "\n\n";
		$mailtxt .= "Error-Line: " . $errline . "\n\n";

		$mailtxt .= "User-ID: " . $_SESSION["ses_userid"] . "\n";
		$mailtxt .= "DB-Name: " . $mysql_db . "\n";
		$mailtxt .= "IP: ".ko_get_user_ip()."\n\n";

	    $mailtxt .= "\n\n_POST:\n";
		$mailtxt .= var_export($_POST, TRUE);
	    $mailtxt .= "\n\n_GET:\n";
		$mailtxt .= var_export($_GET, TRUE);
	    $mailtxt .= "\n\nBACKTRACE:\n";
		$mailtxt .= debug_get_backtrace();
	    $mailtxt .= "\n\n_SESSION:\n";
		$mailtxt .= var_export($_SESSION, TRUE);
	    $mailtxt .= "\n\n_COOKIE:\n";
		$mailtxt .= var_export($_COOKIE, TRUE);
	    $mailtxt .= "\n\n_SERVER:\n";
		$mailtxt .= var_export($_SERVER, TRUE);

		ko_send_mail(WARRANTY_EMAIL, WARRANTY_EMAIL, '[kOOL Error]', $mailtxt);
	}//if(do_mail)

	if($die) exit();
}


function debug_get_backtrace() {
	$r = '';
	foreach(debug_backtrace() as $k => $v) {
		if($v['function'] == "include" || $v['function'] == "include_once" || $v['function'] == "require_once" || $v['function'] == "require") {
			$r .= "#".$k." ".$v['function']."(".$v['args'][0].") called at [".$v['file'].":".$v['line']."]\n";
		} else {
			$r .= "#".$k." ".$v['function']."() called at [".$v['file'].":".$v['line']."]\n";
		}
	} 
	return $r;
}


// auf die benutzerdefinierte Fehlerbehandlung umstellen
$old_error_handler = set_error_handler("kOOL_ErrorHandler");
?>
