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

$ko_path = "../";
require __DIR__ . '/../inc/ko.inc.php';

function sanitize($s) {
	$allowed = "abcdefABCDEF1234567890";  //MD5-Wert ist eine Hex-Zahl
	$new = "";
	for ($i = 0; $i < mb_strlen($s); $i++) {
		if (FALSE !== mb_strstr($allowed, mb_substr($s, $i, 1))) {
			$new .= mb_substr($s, $i, 1);
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
	if(mb_strlen($file_id) != 32 || mb_strlen($recipient_id) != 32) {
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
						$allowed_users[] = mb_substr($u, 1, -1);
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
			$result2 = mysqli_query($db_connection, $query);
			if(mysqli_num_rows($result2) == 0) {
				$error = 1;
			} else {
				$file_sent = mysqli_fetch_assoc($result2);
				$found = $file_sent["recipient"];
				//Done-Eintrag speichern
				db_update_data('ko_fileshare_sent', "WHERE `file_id` = '$file_id' AND `recipient_id` = '$recipient_id'", array('d_date' => date('Y-m-d H:i:s')));;
			}
			mysqli_free_result($result2);
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
