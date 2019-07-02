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

if($do_action && defined('WARRANTY_EMAIL') && WARRANTY_EMAIL != "") {
	$message  = getLL("abuse_invalid_action")." $do_action\n";
	$message .= getLL("abuse_date")." ".strftime($GLOBALS["DATETIME"]["dmY"]." %H:%M:%S", time())."\n";
	$message .= "IP: ".ko_get_user_ip()."\n";
	$message .= "\n\n_POST:\n";
	$message .= var_export($_POST, TRUE);
	$message .= "\n\n_GET:\n";
	$message .= var_export($_GET, TRUE);
	$message .= "\n\nBACKTRACE:\n";
	$message .= var_export(debug_backtrace(), TRUE);
	$message .= "\n\n_SESSION:\n";
	$message .= var_export($_SESSION, TRUE);
	$message .= "\n\n_COOKIE:\n";
	$message .= var_export($_COOKIE, TRUE);
	$message .= "\n\n_SERVER:\n";
	$message .= var_export($_SERVER, TRUE);
																																																			 
	ko_send_mail(WARRANTY_EMAIL, WARRANTY_EMAIL, "[kOOL Abuse] $do_action", $message);
}//if($do_action)
?>
