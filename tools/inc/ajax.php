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

//Set session id from GET (session will be started in ko.inc)
if(!isset($_GET['sesid'])) exit;
if(FALSE === session_id($_GET['sesid'])) exit;

//Send headers to ensure latin1 charset
header('Content-Type: text/html; charset=ISO-8859-1');
 
error_reporting(0);
$ko_menu_akt = 'tools';
$ko_path = '../../';
require($ko_path.'inc/ko.inc');
$ko_path = '../';

array_walk_recursive($_GET,'utf8_decode_array');

ko_include_kota(array('ko_scheduler_tasks','ko_mailing_mails'));

// Plugins einlesen:
$hooks = hook_include_main('tools');
if(sizeof($hooks) > 0) foreach($hooks as $hook) include_once($hook);
 
require($BASE_PATH.'tools/inc/tools.inc');

//HOOK: Submenus einlesen
$hooks = hook_include_sm();
if(sizeof($hooks) > 0) foreach($hooks as $hook) include($hook);

hook_show_case_pre($_SESSION['show']);

 
if(isset($_GET) && isset($_GET['action'])) {
 	$action = format_userinput($_GET['action'], 'alphanum');

	hook_ajax_pre($ko_menu_akt, $action);

 	switch($action) {
		case 'setstart':
			if(isset($_GET['set_start'])) {
				$_SESSION['show_start'] = max(1, format_userinput($_GET['set_start'], 'uint'));
			}
			print 'main_content@@@';
			ko_tools_mailing_mails();
			break;
		case 'setsort':
			$_SESSION['sort_tasks'] = format_userinput($_GET['sort'], 'alphanum+', TRUE, 30);
			$_SESSION['sort_tasks_order'] = format_userinput($_GET['sort_order'], 'alpha', TRUE, 4);

			print 'main_content@@@';
			ko_list_tasks();
		break;


		case 'setfiltergroup':
			$id = format_userinput($_GET['id'], 'uint');
			$group = format_userinput($_GET['group'], 'alpha');
			if($id > 0 && getLL('filter_group_'.$group) != '') {
				db_update_data('ko_filter', "WHERE `id` = '$id'", array('group' => $group));
			} else {
				print 'ERROR@@@Update not possible.';
			}
		break;


		case "addleutefilter":
		case "reloadleutefilter":
			$delete_filter = FALSE;
			$old_filter = [];
			if(FALSE === $id = format_userinput($_GET["id"], "alphanum+", TRUE)) {
				trigger_error("Ungültige id für add_leute_filter: ".$_GET["id"], E_USER_ERROR);
			}
			if($action == "reloadleutefilter") {
				if(FALSE === $fid = format_userinput($_GET["fid"], "uint", TRUE)) {
					trigger_error("Ungültige fid für reload_leute_filter: ".$_GET["fid"], E_USER_ERROR);
				}

				$old_filter = db_select_data("ko_filter", "WHERE id = '" . $fid ."'", "*", "", "LIMIT 1", TRUE);
				$delete_filter = TRUE;
			} else $fid = "";

			// include KOTA (for filters)
			ko_include_kota(array('ko_leute'));

			$table_cols = db_get_columns("ko_leute");
			$col = NULL;
			foreach($table_cols as $c) {
				if($c["Field"] == $id) $col = $c;
			}
			if (!$col) break;

			$special_filters = array("age", "year", "role"); // this array is also defined in tools/inc/tools.inc
			if (in_array($col['Field'], $special_filters)) break;

			if ($delete_filter) db_delete_data("ko_filter", "WHERE id = '" . $fid ."'");

			$col_names = ko_get_leute_col_name();
			$col_name = $col_names[$id];

			$newFilter = array(
				'id' => $fid,
				'typ' => 'leute',
				'dbcol' => $col['Field'],
				'name' => $col_name,
				'allow_neg' => '1',
				'sql1' => 'kota_filter',
				'numvars' => '1',
				'var1' => $col_name,
				'code1' => 'FCN:ko_specialfilter_kota:ko_leute:'.$col['Field'],
				'allow_fastfilter' => 1,
				'group' => $old_filter['group']
			);
			$newFilterId = db_insert_data('ko_filter', $newFilter);

			print "leute-filter-settings-dbcol-{$col['Field']}@@@";
			print ko_tools_get_leute_filter_settings_code($col['Field'], $old_filter);
		break;


		case "deleteleutefilter":
			if(FALSE === $fid = format_userinput($_GET["fid"], "uint", TRUE)) {
				trigger_error("Ungültige id für delete_leute_filter: ".$_GET["fid"], E_USER_ERROR);
			}

			$id = '';
			if ($_GET['id']) $id = format_userinput($_GET['id'], 'alphanum+');

			db_delete_data('ko_filter', "WHERE `typ` = 'leute' AND `id` = '$fid'");

			if ($id) {
				print "leute-filter-settings-dbcol-{$id}@@@";
				print ko_tools_get_leute_filter_settings_code($id);
			} else {
				print "leute-filter-settings-filter-{$fid}@@@";
				print "<script>$('#leute-filter-settings-filter-{$fid}').closest('tr').remove();</script>";
			}
		break;



		case "ableFastfilter":
			if(FALSE === $fid = format_userinput($_GET["fid"], "uint", TRUE)) {
				trigger_error("Invalid filterID for ableFastfilter: ".$_GET["fid"], E_USER_ERROR);
			}

			$filter = db_select_data('ko_filter', "WHERE `id` = '$fid'", '*', '', '', TRUE);
			if(!$filter['id'] || $filter['id'] != $fid) break;

			$able = $filter['allow_fastfilter'] ? 0 : 1;
			db_update_data('ko_filter', "WHERE `id` = '$fid'", array('allow_fastfilter' => $able));

			print "leute-filter-settings-dbcol-{$filter['dbcol']}@@@";
			print ko_tools_get_leute_filter_settings_code($filter['dbcol']);
		break;


		case "selkotatable":
			$table = format_userinput($_GET['table'], 'alphanum+');
			$_SESSION['tools_kota_fields_table'] = $table;

			print "main_content@@@";
			ko_tools_kota_fields();
		break;

	}//switch(action);

	hook_ajax_post($ko_menu_akt, $action);


}//if(GET[action])
?>
