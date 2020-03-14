<?php
/*******************************************************************************
*
*    OpenKool - Online church organization tool
*
*    Copyright © 2003-2020 Renzo Lauper (renzo@churchtool.org)
*    Copyright © 2019-2020 Daniel Lerch
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


//Send headers to ensure UTF-8 charset
header('Content-Type: text/html; charset=UTF-8');

$ko_path = './';
$ko_menu_akt = FALSE;
require __DIR__ . '/inc/ko.inc.php';

switch($_GET["action"]) {
	case "file":
		//Nur Dateien aus dem Download-Verzeichnis des Webverzeichnises erlauben
		$full_path = realpath($_GET["file"]);
		//Find empty file
		if($full_path == '') ko_die('No file found');
		//Replace \ with / for windows systems otherwise the check below will always trigger an error
		if(DIRECTORY_SEPARATOR == '\\') $full_path = str_replace('\\', '/', $full_path);
		if(mb_substr($full_path, 0, mb_strlen($BASE_PATH."download")) != ($BASE_PATH."download")) {
			trigger_error('Not allowed download file: '.$_GET['file'], E_USER_ERROR);
			exit;
		}
		if(!file_exists($_GET["file"])) {
			exit;
		}
		if(mb_substr($_GET["file"], 0, 1) == "/") {
			exit;
		}

		$dateiname = basename($_GET["file"]);
	break;  //case "file"


	case "passthrough":
		ko_returnfile($_GET["file"]);
		exit;
	break;


	default:
		exit;
}//switch(action)
?>
<!DOCTYPE html 
  PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title><?php print getLL("download_title")." ".$dateiname; ?></title>
</head>

<body>
<div class="panel panel-primary nomargin">
	<!-- Default panel contents -->
	<div class="panel-heading"><?= getLL("download_file_header") ?></div>
	<div class="panel-body">
		<p>
			<?= getLL("download_file") ?>
		</p>
		<br>
		<p>
			<b><?= getLL('download_file_file') ?></b>
			<a href="<?= $BASE_URL.$_GET["file"]?>" target="_blank"><?= ko_html($dateiname) ?></a>
		</p>
		<br>
		<p align="center">
			<a href="javascript:TINY.box.hide()"><?= getLL("download_close_window") ?></a>
		</p>

		<?php if (!ko_get_userpref($_SESSION['ses_userid'], 'download_not_directly')) { ?>
		<iframe src="<?= $BASE_URL.$_GET['file'] ?>" style="display: none;"></iframe>
		<?php } ?>

		<?php if (isset($_GET['send'])) { ?>
		<p>
			<a href="?action=show_filesend&file=<?= $_GET['file']?>&filetype=<?=$_GET['filetype']?>">
				<b>&raquo;&nbsp;<?= getLL('download_send')?></b>
			</a>
		</p>
		<?php } ?>
	</div>
</div>

</body>
</html>
