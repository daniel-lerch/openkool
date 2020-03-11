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

//Send headers to ensure latin1 charset
header('Content-Type: text/html; charset=ISO-8859-1');

error_reporting(0);
$ko_menu_akt = 'home';
$ko_path = "../";
require_once($ko_path."inc/ko.inc");

//Include plugin code
$hooks = hook_include_main('_all');
if(sizeof($hooks) > 0) foreach($hooks as $hook) include_once($hook);

//HOOK: Submenus einlesen
$hooks = hook_include_sm();
if(sizeof($hooks) > 0) foreach($hooks as $hook) include_once($hook);

hook_show_case_pre($_SESSION['show']);

$showForm = TRUE;
if ($_POST['async_form'] == 1) {
	$table = format_userinput($_POST['async_form_table'], 'text');
	$mode = format_userinput($_POST['async_form_mode'], 'text');
	$id = format_userinput($_POST['async_form_id'], 'uint');
	$target = format_userinput($_POST['async_form_target'], 'text');
	$tag = format_userinput($_POST['async_form_tag'], 'text');
} else {
	$table = format_userinput($_GET['table'], 'text');
	$mode = format_userinput($_GET['mode'], 'text');
	$id = format_userinput($_GET['id'], 'uint');
	$target = format_userinput($_GET['target'], 'text');
	$tag = format_userinput($_GET['tag'], 'text');
}
$id = $id ? $id : 0;


// set global variable that can be used by modules
$tagParts = explode(':', $tag);
$ASYNC_FORM_TAG = $tagParts[0];
$ASYNC_FORM_PARAMS = array_slice($tagParts, 1);


ko_include_kota(array($table));

if (!isset($KOTA[$table]) || !isset($KOTA[$table]['_form']['redraw']['fcn'])) {
	exit;
}

$module = $KOTA[$table]['_form']['module'];
require_once($ko_path . $module . '/inc/' . $module . '.inc');

// get access
ko_get_access($module);

if ($_POST['async_form'] == 1) {
	$sessionBackup = $_SESSION;
	$asyncFormSubmit = 1;
	try {
		require_once($ko_path . $module . '/index.php');
	} catch (Exception $e) {
		if ($e->getMessage() != 'async-form-submit-dummy-exception') {
			throw $e;
		}
	}
	$_SESSION = $sessionBackup;

	if (!koNotifier::Instance()->hasErrors()) {
		$showForm = FALSE;
		if (!koNotifier::Instance()->hasInfos()) {
			koNotifier::Instance()->addTextInfo(getLL('kota_async_form_info_entry_created'));
		}
	}
}


?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php print $_SESSION["lang"]; ?>" lang="<?php print $_SESSION["lang"]; ?>">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title><?php print "$HTML_TITLE: ".getLL("module_".$ko_menu_akt); ?></title>

	<?php

	$js_files = array();
	if ($showForm) {
		switch ($table) {
			case 'ko_leute':
				$js_files[] = $ko_path.'inc/selectmenu.js';
			break;
			default:
			break;
		}
	}

	print ko_include_js($js_files);

	print ko_include_css();

	if (file_exists("{$ko_path}{$module}/inc/js-{$module}.inc")) {
		require_once("{$ko_path}{$module}/inc/js-{$module}.inc");
	}
	include($ko_path.'inc/js-sessiontimeout.inc');

	switch ($table) {
		case 'ko_leute':
			if ($showForm) {
				// This code is a copy of a snippet in /leute/index.php marked by **LEUTE_INDEX_SNIPPET_1**

				$list_id = 1;
				$show_all_types = FALSE;
				//Beim Einteilen die vergangenen Gruppen nie anzeigen
				$orig_value = ko_get_userpref($_SESSION['ses_userid'], 'show_passed_groups');
				ko_save_userpref($_SESSION['ses_userid'], 'show_passed_groups', 0);
				include("{$ko_path}leute/inc/js-groupmenu.inc");
				ko_save_userpref($_SESSION['ses_userid'], 'show_passed_groups', $orig_value);
				$loadcode = "initList($list_id, $('.groupselect.groupselect-left')[0]);";
				$onload_code = $loadcode.$onload_code;
			}
		break;
		default:
		break;
	}

	?>

</head>
<body onload="session_time_init();<?php if(isset($onload_code)) print $onload_code; ?>">
<main class="main kota-async-form-main" style="overflow-x:hidden;">
	<form action="form.php" method="post" name="formular" target="<?php print $target; ?>-frame" enctype="multipart/form-data">
		<input type="hidden" name="action" id="action" value="" />
		<input type="hidden" name="id" id="id" value="" />
		<input type="hidden" name="mod_confirm" id="mod_confirm" value="" />  <!-- Confirm a moderated reservation -->
		<input type="hidden" name="new_date" id="new_date" value="" />  <!-- Neuer Termin an Datum -->
		<input type="hidden" name="async_form" id="async_form" value="1" />
		<input type="hidden" name="async_form_id" id="async_form_id" value="<?php print $id; ?>" />
		<input type="hidden" name="async_form_table" id="async_form_table" value="<?php print $table; ?>" />
		<input type="hidden" name="async_form_mode" id="async_form_mode" value="<?php print $mode; ?>" />
		<input type="hidden" name="async_form_target" id="async_form_target" value="<?php print $target; ?>" />
		<input type="hidden" name="async_form_tag" id="async_form_tag" value="<?php print $tag; ?>" />
		<div name="main_content" id="main_content">

<?php
if ($showForm) {
	if (koNotifier::Instance()->hasNotifications(koNotifier::ALL)) {
		koNotifier::Instance()->notify();
	}

	if (isset($KOTA[$table]['_form']['redraw']['mode_map'][$mode])) $mode = $KOTA[$table]['_form']['redraw']['mode_map'][$mode];

	$fcnRaw = trim($KOTA[$table]['_form']['redraw']['fcn']);

	$fcnName = trim(substr($fcnRaw, 0, strpos($fcnRaw, '(')));
	$fcn = str_replace(array('@MODE@', '@ID@'), array($mode, $id), $fcnRaw);

	if (function_exists($fcnName)) {
		if (substr($fcn, -1) != ';') $fcn .= ';';

		ko_get_access($module);

		eval($fcn);
		print ("<script>$('button[name=\"cancel\"]').click(function(){parent.$('#{$target}').modal('hide');return false;})</script>");
	}

	print "<script>setTimeout(function() {parent.$('#{$target}-btn').asyncform('onFormLoad');}, 100);</script>";

} else {
	$return = array(
		'notifications' => koNotifier::Instance()->notify(koNotifier::NOTIFYALL, FALSE),
		'table' => $table,
		'target' => $target,
		'tag' => $tag,
		'id' => $id,
		'mode' => $mode,
		'actions' => array(),
	);
	if (koNotifier::Instance()->hasErrors()) {
		$return['status'] = 'error';
	} else {
		$return['status'] = 'success';

		foreach ($GLOBALS['insertedIds'] as $t => $ids) {
			$return['actions'][$t]['insert']['ids'] = $ids;
		}

		if (sizeof($GLOBALS['insertedIds'][$table]) > 0) {
			$entries = array();
			$e_ = db_select_data($table, "WHERE `id` IN (".implode(',', $GLOBALS['insertedIds'][$table]).")");
			foreach ($e_ as $k => $v) {
				$entries[intval($k)] = $v;
			}
			$return['actions'][$table]['insert']['entries'] = $entries;
		}


		if ($id > 0) {
			$return['actions'][$table]['edit'] = array(
				'ids' => $id,
				'entries' => db_select_data($table, "WHERE `id` = {$id}"),
			);
		}
	}

	array_walk_recursive($return, 'utf8_encode_array');
	print "<script>parent.$('#{$target}-btn').asyncform('onResponse', ".json_encode($return).");</script>";
}

?>
			</div>
		</form>
	</main>
	<script>
		if (typeof(changeResItem) != 'function') {
			function changeResItem(item) {}
		}
	</script>
</body>
</html>
