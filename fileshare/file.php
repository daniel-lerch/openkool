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

include("../inc/session.inc");
$ko_path = "../";
require($ko_path."inc/ko.inc");

function sanitize($s) {
  $allowed = "abcdefABCDEF1234567890";  //MD5-Wert ist eine Hex-Zahl
  $new = "";
  for($i=0; $i<strlen($s); $i++) {
    if(FALSE !== strstr($allowed, substr($s, $i, 1))) {
      $new .= substr($s, $i, 1);
		}
  }
  return $new;
}

$error = 0;
$error_txt = array(
		1 => getLL("error_fileshare_file_1"),
		2 => getLL("error_fileshare_file_2"),
		);

//GET-Daten auslesen und analysieren
if(!isset($_GET["di"]) || !isset($_GET["ei"])) $error = 1;
if(!$error) {
	$file_id = sanitize($_GET["di"]);
	$recipient_id = sanitize($_GET["ei"]);
	if(strlen($file_id) != 32 || strlen($recipient_id) != 32) {
		$error = 1;
	}
}

if(!$error) {
	$row = db_select_data("ko_fileshare", "WHERE `id` = '$file_id'");
	if(sizeof($row[$file_id]) == 0 || $row[$file_id]["id"] != $file_id) {
		$error = 2;
	}
	else {
		$found = "";

		//Auf eingeloggten User prüfen
		if($_SESSION["ses_userid"] && $_SESSION["ses_userid"] != ko_get_guest_id()) {
			//Auf Besitzer testen
			if($_SESSION["ses_userid"] == $row[$file_id]["user_id"]) {
				$found = TRUE;
			} else {  //Auf erlaubte Share-Users testen
				//Parent-Folder
				$folder_ = db_select_data("ko_fileshare_folders", "WHERE `id` = '".(int)($row[$file_id]["parent"])."'");
				$folder = $folder_[(int)($row[$file_id]["parent"])];
				$allowed_users[] = array();
				if($folder["share_rights"] >= 1) {
					foreach(explode(",", $folder["share_users"]) as $u) {
						$allowed_users[] = substr($u, 1, -1);
					}
					if(in_array($_SESSION["ses_userid"], $allowed_users)) {
						$found = TRUE;
					}
				}
			}
		}//if(_SESSION[ses_userid])
		else
		{//Nicht eingeloggt, also externer Empfänger
			$query = "SELECT * FROM ko_fileshare_sent WHERE `file_id` = '$file_id' AND `recipient_id` = '$recipient_id'";
			$result2 = mysql_query($query);
			if(mysql_num_rows($result2) == 0) {
				$error = 1;
			} else {
				$file_sent = mysql_fetch_assoc($result2);
				$found = $file_sent["recipient"];
				//Done-Eintrag speichern
				db_update_data('ko_fileshare_sent', "WHERE `file_id` = '$file_id' AND `recipient_id` = '$recipient_id'", array('d_date' => date('Y-m-d H:i:s')));;
			}
			mysql_free_result($result2);
		}//if..else(_SESSION[ses_userid])



		if(!$found) {
			$error = 2;
		} else {
			//Datei ausgeben
			ko_returnfile($file_id, $BASE_PATH."fileshare/files/", $row[$file_id]["filename"]);

		}//if..else(!found)
	}//if..else(sizeof(row)==0)
}//if(!error)

if($error) {
	$log_message = "Error: ".$error_txt[$error]." ($error), URL: ".$_SERVER['REQUEST_URI'].", IP: ".$_SERVER['REMOTE_ADDR'];
	ko_log("fileshare_failed", $log_message);
	print getLL("fileshare_file_error").": ".$error_txt[$error];
} else {
	$log_message = "File: ".$row[$file_id]["filename"]." User: ".$row[$file_id]["user_id"].", Empfänger: ".$found;
	ko_log("fileshare_done", $log_message);
}

?>
