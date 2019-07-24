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


/**
 * Collects basic information for an error report
 * @return array: An associative array of important properties and their values
 */
function get_basic_error_information($errno, $errstr, $backtrace) {
	return array(
		'Error No.' => $errno,
		'Error Msg' => $errstr,
		'Stacktrace' => format_backtrace($backtrace),
		'User ID' => $_SESSION["ses_userid"],
		'IP' => ko_get_user_ip()
	);
}

/**
 * Collects additional information for an error report
 * @return array: An associative array of important properties and their values
 */
function get_additional_error_information() {
	return array(
		'$_GET' => var_export($_GET, TRUE),
		'$_POST' => var_export($_POST, TRUE),
		'$_SESSION' => var_export($_SESSION, TRUE),
		'$_COOKIE' => var_export($_COOKIE, TRUE),
		'$_SERVER' => var_export($_SERVER, TRUE)
	);
}

function format_backtrace($backtrace) {
	$trace = '';
	foreach($backtrace as $k => $v) {
		if($v['function'] == "include" || $v['function'] == "include_once" || $v['function'] == "require_once" || $v['function'] == "require") {
			$trace .= "#".$k." ".$v['function']."(".$v['args'][0].") called at [".$v['file'].":".$v['line']."]\n";
		} else {
			$trace .= "#".$k." ".$v['function']."() called at [".$v['file'].":".$v['line']."]\n";
		}
	} 
	return $trace;
}
?>

<!DOCTYPE html 
  PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php print $_SESSION["lang"]; ?>" lang="<?php print $_SESSION["lang"]; ?>">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title><?php print getLL('error_title'); ?></title>
	<link rel="stylesheet" href="<?php print $ko_path . 'kOOL.css'; ?>" />
	<style>
		table.error {
			margin: 20px;
			border-collapse: collapse;
			line-height: 16pt;
			font-size: 12pt;
		}
		table.error th {
			padding: 5px;
		}
		table.error td {
			padding: 5px;
			border: 1px solid black;
		}
		table.error td pre {
			line-height: 14pt;
			font-size: 10pt;
			white-space: pre-wrap;
			/*word-wrap: break-word;*/
		}
		table.error tr.additional {
			display: none;
		}
	</style>
	<script>
		function expand() {
			let button = document.getElementById("expand");
			let display = null;
			if (button.innerText == "+") {
				button.innerText = "-";
				display = "table-row";
			} else {
				button.innerText = "+";
				display = "none";
			}
			for (node of document.getElementsByClassName("additional")) {
				node.style.display = display;
			}
		}
	</script>
</head>
<body>
	<table class="error">
		<tr>
			<th><img src="<?php print $ko_path . $FILE_LOGO_BIG; ?>"/></th>
			<th><h1><?php print getLL("error_title"); ?></h1></th>
		</tr>
		<?php
			$basic = get_basic_error_information($errno, $errstr, $backtrace);
			$additional = get_additional_error_information();
			foreach ($basic as $key => $value) {
				print '<tr><td>' . $key . '</td><td>' . str_replace("\n", '<br />', $value) . '</td></tr>';
			}
			if (!defined("WARRANTY_EMAIL") && WARRANTY_EMAIL != "" && $ko_menu_akt != "install") {
				print '<tr><td colspan="2">';
				print getLL("error_msg_1").'<br />';
				print sprintf(getLL("error_msg_2"), WARRANTY_EMAIL) . '<br /><br />';
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
		?>
		<tr>
			<th><button id="expand" onclick="expand()">+</button></th>
			<th><h1><?php print getLL("error_msg_4"); ?></h1></th>
		</tr>
		<?php
			foreach ($additional as $key => $value) {
				print '<tr class="additional"><td>' . $key . '</td><td><pre>' . $value . '</pre></td></tr>';
			}
		?>
	</table>
</body>
</html>
<?php die(); ?>
