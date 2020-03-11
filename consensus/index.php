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

header('Content-Type: text/html; charset=ISO-8859-1');

$ko_path = '../';
$ko_menu_akt = "consensus";
$SMARTY_RENDER_TEMPLATE = NULL;

require($ko_path . 'inc/ko.inc');
require($ko_path . 'rota/inc/rota.inc');
ko_include_kota(['ko_rota_teams', 'ko_event']);
require_once('consensus.inc');

$notifier = koNotifier::Instance();

error_reporting(0);

//***Action auslesen:
if ($_POST["action"]) {
	$do_action = $_POST["action"];
	$action_mode = "POST";
} else if ($_GET["action"]) {
	$do_action = $_GET["action"];
	$action_mode = "GET";
} else {
	$do_action = $action_mode = "";
}

if (FALSE === format_userinput($do_action, "alpha+", TRUE, 30)) {
	trigger_error("invalid action: " . $do_action, E_USER_ERROR);
}

if ($do_action == '') {
	list($pass, $personId, $team_ids, $start, $span) = ko_consensus_check_hash($_GET['x']);

	if ($pass) {
		if (ko_get_setting("consensus_ongoing_cal")) {
			// overwrite timespan if ongoing calendar is activated
			if(empty($_GET['ongoing_start'])) {
				$start = date("Y-m-01", time());
			} else {
				$start = date("Y-m-d", strtotime($_GET['ongoing_start']));
			}
			$span = ko_get_setting("consensus_ongoing_cal_timespan");
		}

		$_SESSION['show'] = 'list_consensus';
	} else {
		$notifier->addError(1);
	}
}

?>
<!DOCTYPE html
		PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
		"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php print $_SESSION['lang']; ?>"
	  lang="<?php print $_SESSION['lang']; ?>">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1"/>
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title><?php print $HTML_TITLE.': '.getLL('ko_consensus'); ?></title>
<?php
print '<script type="text/javascript" src="' . $ko_path . 'inc/jquery/jquery.js?'.filemtime($ko_path.'inc/jquery/jquery.js').'"></script>';
print '<script type="text/javascript" src="' . $ko_path . 'inc/jquery/jquery-ui.js?' . filemtime($ko_path . 'inc/jquery/jquery-ui.js') . '"></script>';
print '<script type="text/javascript" src="' . $ko_path . 'consensus/consensus.js?'.filemtime($ko_path.'consensus/consensus.js').'"></script>';
print '<script type="text/javascript" src="' . $ko_path . 'inc/bootstrap/core/js/bootstrap.min.js?'.filemtime($ko_path.'inc/bootstrap/core/js/bootstrap.min.js').'"></script>';
print '<script type="text/javascript" src="' . $ko_path . 'inc/moment/moment.js?'.filemtime($ko_path.'inc/moment/moment.js').'"></script>';

switch(substr($_SESSION['lang'], 0, 2)) {
	case 'en':
	case 'nl':
		$moment_language_file = 'en-ca.js';
		break;
	case 'fr':
		$moment_language_file = 'fr.js';
		break;
	default:
		$moment_language_file = 'de.js';
}

print '<script type="text/javascript" src="' . $ko_path . 'inc/moment/'.$moment_language_file.'?'.filemtime($ko_path.'inc/moment/'.$moment_language_file.'').'"></script>';

print '<script type="text/javascript" src="' . $ko_path . 'inc/bootstrap/plugins/bootstrap-datetimepicker-master/js/bootstrap-datetimepicker.js?'.filemtime($ko_path.'inc/bootstrap/plugins/bootstrap-datetimepicker-master/js/bootstrap-datetimepicker.js').'"></script>';

print '<script type="text/javascript" src="' . $ko_path . 'inc/tooltip.js?'.filemtime($ko_path.'inc/tooltip.js').'"></script>';
include("js-consensus.inc");
print '<link rel="stylesheet" type="text/css" href="'.$ko_path.'kool-base.css?'.filemtime($ko_path.'consensus/consensus.css').'" />';
print '<link rel="stylesheet" type="text/css" href="'.$ko_path.'consensus/consensus.css?'.filemtime($ko_path.'consensus/consensus.css').'" />';
foreach($PLUGINS as $p) {
	$css_file = $ko_path.'plugins/'.$p['name'].'/consensus.css';
	if(file_exists($css_file)) {
		print '<link rel="stylesheet" type="text/css" href="'.$css_file.'?'.filemtime($css_file).'" />'."\n";
	}
	$js_file = $ko_path.'plugins/'.$p['name'].'/consensus.js';
	if(file_exists($js_file)) {
		print '<script type="text/javascript" src="'.$js_file.'?'.filemtime($js_file).'"></script>'."\n";
	}
}
?>
</head>

<body class="standalone">
	<div class="page">
		<div name="wait_message" id="wait_message">
			<img src="<?= $ko_path ?>images/load_anim.gif" alt="wait animation"/>
		</div>

		<div class="container-fluid" id="header">
			<div class="row">
				<div class="col-sm-7 col-xs-12" id="logo">
					<?php include($BASE_PATH . 'header.php') ?>
				</div>
				<div class="col-sm-5 hidden-xs" id="title">
					<h2><?= getLL('ko_consensus') ?></h2>
				</div>
			</div>
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

	</div>
</body>
</html>
