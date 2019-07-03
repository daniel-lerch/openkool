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


$ko_path = '../';
$ko_menu_akt = "consensus";
$SMARTY_RENDER_TEMPLATE = null;

require($ko_path.'inc/ko.inc.php');
require($ko_path.'rota/inc/rota.inc.php');
require($ko_path.'inc/smarty.inc.php');
require_once('consensus.inc.php');


$notifier = koNotifier::Instance();

error_reporting(0);


//***Action auslesen:
if($_POST["action"]) {
	$do_action=$_POST["action"];
	$action_mode = "POST";
} else if($_GET["action"]) {
	$do_action=$_GET["action"];
	$action_mode = "GET";
} else {
	$do_action = $action_mode = "";
}

if(FALSE === format_userinput($do_action, "alpha+", TRUE, 30)) trigger_error("invalid action: ".$do_action, E_USER_ERROR);

switch ($do_action) {
	case '':
		$get = explode('x', $_GET['x']);


		$personId = $get[0];
		$start = substr($get[1], 0, 4) . '-' . substr($get[1], -4, -2) . '-' . substr($get[1], 6, 8);
		$span = $get[2];
		$key = $get[3];

		$pass = substr(md5($personId . $start . $span . KOOL_ENCRYPTION_KEY), 0, 6) == $key;


		if ($pass) {
			$_SESSION['show'] = 'list_consensus';
		}
		else {
			$notifier->addError(1);
		}
}

?>

<!DOCTYPE html
	PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php print $_SESSION['lang']; ?>" lang="<?php print $_SESSION['lang']; ?>">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
	<title><?php print $HTML_TITLE.': '.getLL('ko_consensus'); ?></title>
<?php
print '<script type="text/javascript" src="' . $ko_path . 'consensus/consensus.js?'.filemtime($ko_path.'consensus/consensus.js').'"></script>';
print '<script type="text/javascript" src="' . $ko_path . 'inc/jquery/jquery.js?'.filemtime($ko_path.'inc/jquery/jquery.js').'"></script>';
print '<script type="text/javascript" src="' . $ko_path . 'inc/tooltip.js?'.filemtime($ko_path.'inc/tooltip.js').'"></script>';
include("js-consensus.inc.php");
print '<link rel="stylesheet" type="text/css" href="'.$ko_path.'consensus/consensus.css?'.filemtime($ko_path.'consensus/consensus.css').'" />';
?>
</head>

<body>
	<div id="header">
		<div id="kool-text">
			<a href="http://www.churchtool.org">
				<img src="<?= $ko_path . $FILE_LOGO_SMALL ?>">
			</a>
		</div>
		<div id="title">
			<h1><?= getLL('ko_consensus') ?></h1>
		</div>
		<div id="logo">
			<?php include($BASE_PATH . 'header.php') ?>
		</div>
		<div style="display:none;padding:10px;margin:5px 180px 10px 10px;background-color:#ddd;border:2px solid #3586bd;position:fixed;_position:absolute;right:0;top:0;_top:expression(eval(document.body.scrollTop));z-index:900;width:125px;text-align:center;" name="wait_message" id="wait_message">
			<img src="<?= $ko_path ?>images/load_anim.gif" />
		</div>
		<br clear="all">
	</div>
	<div id="main">
		<?php

		$notifier->notify();

		switch ($_SESSION['show']) {
			case 'list_consensus':
				if (!$notifier->hasErrors()) {
					ko_consensus_list_consensus();
				}
				break;
		}

		?>
	</div>

</body>
</html>
