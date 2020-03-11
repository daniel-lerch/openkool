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

ob_start();  //Ausgabe-Pufferung starten

$ko_path = '../';
$ko_menu_akt = 'tracking';

include($ko_path.'inc/ko.inc');
include('inc/tracking.inc');

//Redirect to SSL if needed
ko_check_ssl();

if(!ko_module_installed('tracking')) {
	header('Location: '.$BASE_URL.'index.php'); exit;
}

ob_end_flush();  //Puffer flushen

$onload_code = '';
$notifier = koNotifier::Instance();

//Get access rights
ko_get_access('tracking');

//kOOL Table Array
ko_include_kota(array('ko_tracking', 'ko_tracking_entries'));


//*** Plugins einlesen:
$hooks = hook_include_main('tracking');
if(sizeof($hooks) > 0) foreach($hooks as $hook) include_once($hook);


//*** Action auslesen:
if($_POST['action']) {
	$do_action=$_POST['action'];
	$action_mode = 'POST';
} else if($_GET['action']) {
	$do_action=$_GET['action'];
	$action_mode = 'GET';
} else {
	$do_action = $action_mode = '';
}
if(!$do_action) $do_action = 'list_trackings';

//Reset show_start if from another module
if($_SERVER['HTTP_REFERER'] != '' && FALSE === strpos($_SERVER['HTTP_REFERER'], '/'.$ko_menu_akt.'/')) $_SESSION['show_start'] = 1;

switch($do_action) {

	// Display
	case 'list_trackings':
		if($access['tracking']['MAX'] < 1) break;
		if($_SESSION['show'] == 'list_trackings') $_SESSION['show_start'] = 1;
		$_SESSION['show'] = 'list_trackings';
	break;



	//Neu:
	case 'new_tracking':
		if($access['tracking']['MAX'] < 4) break;

		$_SESSION['show'] = 'new_tracking';
		$onload_code = 'form_set_first_input();'.$onload_code;
	break;

	case 'submit_new_tracking':
	case 'submit_as_new_tracking':
		if($access['tracking']['MAX'] < 4) break;

		if($do_action == 'submit_as_new_tracking') {
			list($table, $columns, $ids, $hash) = explode('@', $_POST['id']);
			//Fake POST[id] for kota_submit_multiedit() to remove the id from the id. Otherwise this entry will be edited
			$new_hash = md5(md5($mysql_pass.$table.implode(':', explode(',', $columns)).'0'));
			$_POST['id'] = $table.'@'.$columns.'@0@'.$new_hash;
		}

		kota_submit_multiedit('', 'new_tracking');
		if(!$notifier->hasErrors()) {
			$_SESSION['show'] = 'list_trackings';
		}
	break;




	//Bearbeiten
	case 'edit_tracking':
		$id = format_userinput($_POST['id'], 'uint');
		if($access['tracking']['ALL'] < 3 && $access['tracking'][$id] < 3) break;

		$_SESSION['show'] = 'edit_tracking';
		$onload_code = 'form_set_first_input();'.$onload_code;
	break;

	case 'submit_edit_tracking':
		if($access['tracking']['MAX'] < 3) break;

		kota_submit_multiedit('', 'edit_tracking');
		if(!$notifier->hasErrors()) {
			$_SESSION['show'] = 'list_trackings';
		}
	break;



	//Neu:
	case 'new_tracking_entry':
		if($access['tracking']['MAX'] < 2) break;

		$_SESSION['show'] = 'new_tracking_entry';
		$onload_code = 'form_set_first_input();'.$onload_code;
	break;

	case 'submit_new_tracking_entry':
	case 'submit_as_new_tracking_entry':
		if($access['tracking']['MAX'] < 2) break;

		//Access check for given trackingID
		$ok = TRUE;
		if($access['tracking']['ALL'] < 2) {
			$tid = intval($_POST['koi']['ko_tracking_entries']['tid'][0]);
			if($access['tracking'][$tid] < 2) $ok = FALSE;
		}

		if($do_action == 'submit_as_new_tracking_entry') {
			list($table, $columns, $ids, $hash) = explode('@', $_POST['id']);
			//Fake POST[id] for kota_submit_multiedit() to remove the id from the id. Otherwise this entry will be edited
			$new_hash = md5(md5($mysql_pass.$table.implode(':', explode(',', $columns)).'0'));
			$_POST['id'] = $table.'@'.$columns.'@0@'.$new_hash;
		}

		if($ok) kota_submit_multiedit('', 'enter_tracking');
		$_SESSION['show'] = 'list_trackings_entries';
	break;



	//Bearbeiten
	case 'edit_tracking_entry':
		$_SESSION['show'] = 'edit_tracking_entry';
		$edit_id = format_userinput($_POST['id'], 'uint');
		$onload_code = 'form_set_first_input();'.$onload_code;
	break;

	case 'submit_edit_tracking_entry':
		if($access['tracking']['MAX'] < 3) break;

		kota_submit_multiedit('', 'edit_tracking_entry');
		if(!$notifier->hasErrors()) {
			$_SESSION['show'] = 'list_tracking_entries';
		}
	break;





	//Entering
	case 'enter_tracking':
		if($access['tracking']['MAX'] < 1) break;

		//Get tracking id from GET
		if(isset($_GET['id']) && (int)$_GET['id'] == $_GET['id'] && ($access['tracking']['ALL'] > 0 || $access['tracking'][$_GET['id']] > 0)) {
			//Unset filter if switching trackings (might be for a group not in the current tracking, which gives empty list of persons)
			if($_GET['id'] != $_SESSION['tracking_id']) {
				unset($_SESSION['tracking_filter']['filter']);
			}
			$_SESSION['tracking_id'] = $_GET['id'];
			$_SESSION['show'] = 'enter_tracking';
		}
	break;


	case 'setdate':
		if($access['tracking']['MAX'] < 1) break;

		$newdate = format_userinput($_GET['date'], 'date');
		if(strtotime($newdate) > 0) {
			$_SESSION['date_start'] = $newdate;
		}
	break;


	case 'select_tracking':
		if($access['tracking']['MAX'] < 1) break;

		//Get tracking id from GET
		if(isset($_GET['id']) && (int)$_GET['id'] == $_GET['id'] && ($access['tracking']['ALL'] > 0 || $access['tracking'][$_GET['id']] > 0)) {
			//Unset filter if switching trackings (might be for a group not in the current tracking, which gives empty list of persons)
			if($_GET['id'] != $_SESSION['tracking_id']) {
				unset($_SESSION['tracking_filter']['filter']);
			}
			$_SESSION['tracking_id'] = $_GET['id'];

			if(!in_array($_SESSION['show'], array('enter_tracking', 'list_tracking_entries'))) {
				$show = ko_get_userpref($_SESSION['ses_userid'], 'tracking_default_show');
				if(!$show) $show = 'enter_tracking';
				$_SESSION['show'] = $show;
			}
		}
	break;



	case 'list_tracking_entries':
		if($access['tracking']['MAX'] < 1) break;

		$_SESSION['show'] = 'list_tracking_entries';
	break;






	//Löschen
	case 'delete_tracking':
		if($access['tracking']['MAX'] < 4) break;

		$id = format_userinput($_POST['id'], 'uint');
		if(!$id || ($access['tracking']['ALL'] < 4 && $access['tracking'][$id] < 4)) break;

		$old = db_select_data('ko_tracking', "WHERE `id` = '$id'", '*', '', '', TRUE);

		db_delete_data('ko_tracking', "WHERE `id` = '$id'");
		db_delete_data('ko_tracking_entries', "WHERE `tid` = '$id'");
		ko_log_diff('del_tracking', $old);
	break;



	case 'confirm_tracking_entry':
		if($access['tracking']['MAX'] < 2) break;

		$id = format_userinput($_POST['id'], 'uint');
		if(!$id) break;

		$tentry = db_select_data('ko_tracking_entries', "WHERE `id` = '$id'", '*', '', '', TRUE);
		if(!$tentry['id'] || $tentry['id'] != $id || $tentry['status'] == 0) break;
		if($access['tracking']['ALL'] < 2 && $access['tracking'][$tentry['tid']] < 2) break;

		db_update_data('ko_tracking_entries', "WHERE `id` = '$id'", array('status' => 0, 'last_change' => date('Y-m-d H:i:s')));
		ko_log_diff('confirm_entered_tracking', $tentry);
		$notifier->addInfo(1, $do_action);
	break;



	case 'delete_tracking_entry':
		if($access['tracking']['MAX'] < 2) break;

		$id = format_userinput($_POST['id'], 'uint');
		if(!$id) break;

		$tentry = db_select_data('ko_tracking_entries', "WHERE `id` = '$id'", '*', '', '', TRUE);
		if(!$tentry['id'] || $tentry['id'] != $id) break;
		if($access['tracking']['ALL'] < 2 && $access['tracking'][$tentry['tid']] < 2) break;

		db_delete_data('ko_tracking_entries', "WHERE `id` = '$id'");
		ko_log_diff('del_entered_tracking', $tentry);
		$notifier->addInfo(2, $do_action);
	break;




	//Export
	case 'export_tracking_xls':
	case 'export_tracking_pdf':
		if($access['tracking']['MAX'] < 1) break;

		$mode = $do_action == 'export_tracking_xls' ? 'xls' : 'pdf';
		$folder = $mode == 'xls' ? 'excel' : 'pdf';

		//Check for pdftk to merge PDF files
		if($mode == 'pdf') $merge_available = ko_check_for_pdftk();
		else $merge_available = FALSE;
		
		//Check for checked trackings out of the list
		$tid = explode(',', $_POST['ids']);
		if($_SESSION['show'] == 'list_trackings') {
			if(sizeof($tid) == 0) {
				$notifier->addError(3, $do_action);
				break;
			} else if(sizeof($tid) > 1 && !$merge_available) {  //Only allow multiple selection if pdftk is available
				$notifier->addError(4, $do_action);
				break;
			}
		} else {
			$tid = $_SESSION['tracking_id'];
		}
		if(is_array($tid) && sizeof($tid) > 0) {
			if($mode == 'pdf') {  //Merge PDF files
				//Merge single PDF files into one if several have been selected (pdftk must be available in PATH, see above)
				$filenames = array();
				foreach($tid as $id) {
					$file = $ko_path.'download/'.$folder.'/'.getLL('tracking_export_filename').$id.'_'.strftime('%d%m%Y_%H%M%S', time()).'.'.$mode;
					ko_tracking_export($mode, $file, $id, $_POST['sel_cols'], $_POST['sel_dates'], $_POST['sel_layout'], $_POST['sel_addrows'], $_POST['chk_family'], $_POST['sel_sums']);
					$filenames[] = basename($file);
				}
				$filename = getLL('tracking_export_filename').strftime('%d%m%Y_%H%M%S', time()).'.'.$mode;
				if($merge_available) {
					//Merge multiple PDF files using pdftk (if available)
					system('cd '.$BASE_PATH.'/download/'.$folder.'/ && pdftk '.implode(' ', $filenames).' cat output '.$filename);
				} else {
					//Just use first (and only) file
					$filename = array_shift($filenames);
				}
				$filename = $ko_path.'download/'.$folder.'/'.$filename;
			} else {
				$format = ko_get_userpref($_SESSION['ses_userid'], 'export_table_format');
				if(!$format) $format = 'xlsx';
				$filename = $ko_path.'download/'.$folder.'/'.getLL('tracking_export_filename').strftime('%d%m%Y_%H%M%S', time()).'.'. ($mode == 'xls' ? $format : $mode);
				ko_tracking_export($mode, $filename, $tid[0], $_POST['sel_cols'], $_POST['sel_dates'], $_POST['sel_layout'], $_POST['sel_addrows'], $_POST['chk_family'], $_POST['sel_sums']);
			}
		} else {
			//Only one selected so just create this and return it
			$format = ko_get_userpref($_SESSION['ses_userid'], 'export_table_format');
			if(!$format) $format = 'xlsx';
			$filename = $ko_path.'download/'.$folder.'/'.getLL('tracking_export_filename').strftime('%d%m%Y_%H%M%S', time()).'.'. ($mode == 'xls' ? $format : $mode);
			ko_tracking_export($mode, $filename, $tid, $_POST['sel_cols'], $_POST['sel_dates'], $_POST['sel_layout'], $_POST['sel_addrows'], $_POST['chk_family'], $_POST['sel_sums']);
		}

		if(!$notifier->hasErrors()) {
			$onload_code = "ko_popup('".$ko_path.'download.php?action=file&amp;file='.substr($filename, 3)."');";
		}

		//Set userprefs with current selections
		ko_save_userpref($_SESSION['ses_userid'], 'tracking_export_cols', $_POST['sel_cols']);
		ko_save_userpref($_SESSION['ses_userid'], 'tracking_export_layout', $_POST['sel_layout']);
		ko_save_userpref($_SESSION['ses_userid'], 'tracking_export_addrows', $_POST['sel_addrows']);
		ko_save_userpref($_SESSION['ses_userid'], 'tracking_export_family', $_POST['chk_family']);
		ko_save_userpref($_SESSION['ses_userid'], 'tracking_export_sums', $_POST['sel_sums']);
	break;


	//Export multiple trackings in a zip file
	case 'export_tracking_xls_zip':
	case 'export_tracking_pdf_zip':
		if($access['tracking']['MAX'] < 1) break;

		$mode = $do_action == 'export_tracking_xls_zip' ? 'xls' : 'pdf';
		$folder = $mode == 'xls' ? 'excel' : 'pdf';

		$ids = explode(',', format_userinput($_POST['ids'], 'intlist'));
		foreach($ids as $k => $v) if(!$v) unset($ids[$k]);
		if(sizeof($ids) == 0) $notifier->addError(3, $do_action);

		if(!$notifier->hasErrors()) {
			//Start zip file
			$zip = new ZipArchive();
			$zip_filename = $ko_path.'download/'.$folder.'/'.getLL('tracking_export_filename').strftime('%d%m%Y_%H%M%S', time()).'.zip';
			$zip->open($zip_filename, ZIPARCHIVE::CREATE);

			$orig_filter = $_SESSION['tracking_filter'];
			foreach($ids as $id) {
				if(!$id) continue;
				$tracking = db_select_data('ko_tracking', "WHERE `id` = '$id'", '*', '', '', TRUE);
				//Fake date mode "all" for continuous trackings by setting the filter according to entered data
				if($tracking['date_weekdays'] != '' && $_POST['sel_dates'] == 'all') {
					$first = db_select_data('ko_tracking_entries', "WHERE `tid` = '$id'", '*', 'ORDER BY `date` ASC', 'LIMIT 0,1', TRUE);
					$last = db_select_data('ko_tracking_entries', "WHERE `tid` = '$id'", '*', 'ORDER BY `date` DESC', 'LIMIT 0,1', TRUE);
					$_SESSION['tracking_filter']['date1'] = $first['date'];
					$_SESSION['tracking_filter']['date2'] = $last['date'];
					$date_mode = 'filter';
					$reset_filter = TRUE;
				} else {
					$date_mode = $_POST['sel_dates'];
					$reset_filter = FALSE;
				}
				$format = ko_get_userpref($_SESSION['ses_userid'], 'export_table_format');
				if(!$format) $format = 'xlsx';
				$filename = $ko_path.'download/'.$folder.'/'.getLL('tracking_export_filename').strftime('%d%m%Y_%H%M%S', time()).'_'.$id.'.'. ($mode == 'xls' ? $format : $mode);
				ko_tracking_export($mode, $filename, $id, $_POST['sel_cols'], $date_mode, $_POST['sel_layout'], $_POST['sel_addrows'], $_POST['chk_family'], $_POST['sel_sums']);
				$zip->addFile($filename, getLL('tracking_export_filename').$tracking['name'].'.'. ($mode == 'xls' ? $format : $mode));
				//Reset filter
				if($reset_filter) $_SESSION['tracking_filter'] = $orig_filter;
			}

			$zip->close();
			$onload_code = "ko_popup('".$ko_path.'download.php?action=file&amp;file='.substr($zip_filename, 3)."');";
		}
	break;



	//Settings
	case 'tracking_settings':
		if($access['tracking']['MAX'] < 1) break;
		$_SESSION['show_back'] = $_SESSION['show'];
		$_SESSION['show'] = 'tracking_settings';
	break;

	case 'submit_tracking_settings':
		if($access['tracking']['MAX'] < 1) break;

		ko_save_userpref($_SESSION['ses_userid'], 'show_limit_trackings', format_userinput($_POST['txt_limit_trackings'], 'uint'));
		ko_save_userpref($_SESSION['ses_userid'], 'tracking_date_limit', format_userinput($_POST['txt_limit_tracking_dates'], 'uint'));
		
		ko_save_userpref($_SESSION['ses_userid'], 'tracking_order_people', format_userinput($_POST['sel_order_people'], 'alpha'));
		ko_save_userpref($_SESSION['ses_userid'], 'tracking_show_inactive', format_userinput($_POST['sel_show_inactive'], 'uint'));

		ko_save_userpref($_SESSION['ses_userid'], 'tracking_dateformat', format_userinput($_POST['sel_dateformat'], 'alpha'));
		ko_save_userpref($_SESSION['ses_userid'], 'tracking_default_show', format_userinput($_POST['sel_default_show'], 'alpha+'));
		if(ko_module_installed('leute')) {
			ko_save_userpref($_SESSION['ses_userid'], 'tracking_show_cols', format_userinput($_POST['sel_show_cols'], 'js', FALSE, 0, array('allquotes'), '@'));
		}

		if($access['tracking']['MAX'] > 3) {
			ko_set_setting('tracking_add_roles', format_userinput($_POST['sel_add_roles'], 'uint'));
			ko_set_setting('checkin_display_leute_fields', format_userinput($_POST['sel_checkin_display_leute_fields'], 'alphanumlist'));
			ko_set_setting('checkin_max_results', format_userinput($_POST['txt_checkin_max_results'], 'int'));

			if ($_SESSION['ses_userid'] == ko_get_root_id()) {
				ko_set_setting('tracking_enable_checkin', format_userinput($_POST['chk_enable_checkin'], 'uint'));
			}
		}

		$_SESSION['show'] = $_SESSION['show_back'] ? $_SESSION['show_back'] : 'list_trackings';
	break;


	// Mod
	case 'mod_entries':
		if($access['tracking']['MAX'] < 2) break;

		$_SESSION['show'] = 'mod_entries';
	break;

	case 'edit_tracking_mod':
		$id = format_userinput($_POST['id'], 'uint');
		if (!$id) $id = format_userinput($_GET['id'], 'uint');
		if (!$id) break;

		$trackingMod = db_select_data('ko_tracking_entries', "WHERE `id` = '{$id}'", 'id,tid,cruser', '', '', TRUE, TRUE);
		if ($trackingMod['id'] != $id) break;

		if($access['tracking']['ALL'] < 2 && $access['tracking'][$trackingMod['tid']] < 2) break;

		$edit_id = $id;

		if(!is_array($KOTA['ko_tracking_entries'])) ko_include_kota(array('ko_tracking_entries'));

		$_SESSION['show_back'] = $_SESSION['show'];
		$_SESSION['show'] = 'edit_tracking_mod';
	break;

	case 'submit_edit_tracking_mod':
	case 'submit_edit_approve_tracking_mod':
		if($access['tracking']['MAX'] < 2) break;

		kota_submit_multiedit('', 'edit_tracking_mod');

		if ($do_action == 'submit_edit_approve_tracking_mod') {
			list($table, $col, $id, $hash) = explode('@', $_POST['id']);
			$id = format_userinput($id, 'uint');
			if(!$id) break;

			$tentry = db_select_data('ko_tracking_entries', "WHERE `id` = '$id'", '*', '', '', TRUE);
			if(!$tentry['id'] || $tentry['id'] != $id || $tentry['status'] == 0) break;
			if($access['tracking']['ALL'] < 2 && $access['tracking'][$tentry['tid']] < 2) break;

			db_update_data('ko_tracking_entries', "WHERE `id` = '$id'", array('status' => 0, 'last_change' => date('Y-m-d H:i:s')));
			ko_log_diff('confirm_entered_tracking', $tentry);
			$notifier->addInfo(1, $do_action);
		}

		if(!$notifier->hasErrors()) {
			$_SESSION['show'] = 'mod_entries';
		}
	break;




	//Multiedit
	case 'multiedit':
		//Zu bearbeitende Spalten
		$columns = explode(',', format_userinput($_POST['id'], 'alphanumlist'));
		foreach($columns as $column) {
			$do_columns[] = $column;
		}
		if(sizeof($do_columns) < 1) $notifier->addError(4, $do_action);

		//Zu bearbeitende Einträge
		$do_ids = array();
		foreach($_POST['chk'] as $c_i => $c) {
			if($c) {
				if(FALSE === ($edit_id = format_userinput($c_i, 'uint', TRUE))) {
					trigger_error('Not allowed multiedit_id: '.$c_i, E_USER_ERROR);
				}
				if($access['tracking']['ALL'] > 2 || $access['tracking'][$edit_id] > 2) $do_ids[] = $edit_id;
			}
		}
		if(sizeof($do_ids) < 1) $notifier->addError(4, $do_action);

		if($_SESSION['show'] == 'mod_entries') {
			if($access['tracking']['MAX'] < 2) break;
			if(!$notifier->hasErrors()) {
				$_SESSION['show_back'] = $_SESSION['show'];
				$order = 'ORDER BY '.$_SESSION['sort_modtrackings'].' '.$_SESSION['sort_modtrackings_order'];
				$_SESSION['show'] = 'multiedit_mod';
			}
		} else {
			if($access['tracking']['MAX'] < 3) break;
			if(!$notifier->hasErrors()) {
				$_SESSION['show_back'] = $_SESSION['show'];
				$order = 'ORDER BY '.$_SESSION['sort_trackings'].' '.$_SESSION['sort_trackings_order'];
				$_SESSION['show'] = 'multiedit';
			}
		}
		$onload_code = 'form_set_first_input();'.$onload_code;
	break;



	case 'submit_multiedit':
		if($_SESSION['show'] == 'multiedit_mod') {
			if($access['tracking']['MAX'] < 2) break;
			kota_submit_multiedit(2);

			$_SESSION['show'] = $_SESSION['show_back'] ? $_SESSION['show_back'] : 'mod_entries';
		} else {
			if($access['tracking']['MAX'] < 3) break;
			kota_submit_multiedit(3);

			$_SESSION['show'] = $_SESSION['show_back'] ? $_SESSION['show_back'] : 'list_trackings';
		}
	break;





	//Filter
	case 'set_filter':
		if($access['tracking']['MAX'] < 1) break;

		foreach($_POST['tracking_filter'] as $key => $value) {
			if(!$value) {  //No value means unset the filter
				unset($_SESSION['tracking_filter'][$key]);
			} else {  //otherwise set filter
				if(in_array($key, array('date1', 'date2'))) $value = strftime('%Y-%m-%d', strtotime($value));
				$_SESSION['tracking_filter'][$key] = $value;
			}
		}
		if(!$_SESSION['show']) $_SESSION['show'] = 'enter_tracking';
		if($_SESSION['tracking_filter']['date1']) $_SESSION['date_start'] = $_SESSION['tracking_filter']['date1'];
	break;


	case 'clear_filter':
		unset($_SESSION['tracking_filter']);
		if(!$_SESSION['show']) $_SESSION['show'] = 'enter_tracking';
	break;


	//Default:
  default:
		if(!hook_action_handler($do_action))
      include($ko_path.'inc/abuse.inc');
  break;


}//switch(do_action)


//HOOK: Plugins erlauben, die bestehenden Actions zu erweitern
hook_action_handler_add($do_action);



//Reread access rights if necessary
if(in_array($do_action, array('delete_tracking', 'submit_multiedit', 'submit_new_tracking', 'submit_as_new_tracking'))) {
	ko_get_access('tracking', '', TRUE);
}



//*** Default-Werte auslesen
$_SESSION['show_limit'] = ko_get_userpref($_SESSION['ses_userid'], 'show_limit_trackings');
if(!$_SESSION['show_limit']) $_SESSION['show_limit'] = 20;

if(!$_SESSION['show_start']) $_SESSION['show_start'] = 1;

if($_SESSION['sort_trackings'] == '') {
	$_SESSION['sort_trackings']= 'name';
	$_SESSION['sort_trackings_order'] = 'ASC';
}
if($_SESSION['sort_modtrackings'] == '') {
	$_SESSION['sort_modtrackings']= 'crdate';
	$_SESSION['sort_modtrackings_order'] = 'DESC';
}
if($_SESSION['sort_tracking_entries'] == '') {
	$_SESSION['sort_tracking_entries']= 'date';
	$_SESSION['sort_tracking_entries_order'] = 'DESC';
}
if(!isset($_SESSION['show_tracking_groups'])) {
	$_SESSION['show_tracking_groups'] = explode(',', ko_get_userpref($_SESSION['ses_userid'], 'show_tracking_groups'));
	if(sizeof($_SESSION['show_tracking_groups']) == 0) {
		$groups = db_select_data('ko_tracking_groups', '', '*');
		$_SESSION['show_tracking_groups'] = array_merge(array(0), array_keys($groups));
	}
}

$_SESSION['date_limit'] = ko_get_userpref($_SESSION['ses_userid'], 'tracking_date_limit');
if(!$_SESSION['date_limit']) $_SESSION['date_limit'] = 5;

if(!$_SESSION['tracking_id']) {
	$tracking = db_select_data('ko_tracking', '', '*', 'ORDER BY name ASC', 'LIMIT 0,1', TRUE);
	$_SESSION['tracking_id'] = $tracking['id'];
}

//Include submenus
ko_set_submenues();
?>
<!DOCTYPE html 
  PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php print $_SESSION['lang']; ?>" lang="<?php print $_SESSION['lang']; ?>">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title><?php print $HTML_TITLE.': '.getLL('module_'.$ko_menu_akt); ?></title>
<?php
print ko_include_css();

print ko_include_js();

include($ko_path.'inc/js-sessiontimeout.inc');
include('inc/js-tracking.inc');
?>
</head>

<body onload="session_time_init();<?php if(isset($onload_code)) print $onload_code; ?>">

<?php
/*
 * Gibt bei erfolgreichem Login das Menü aus, sonst einfach die Loginfelder
 */
include($ko_path . "menu.php");
ko_get_outer_submenu_code('tracking');

?>

<main class="main">
<form action="index.php" method="post" name="formular" enctype="multipart/form-data">
<input type="hidden" name="action" id="action" value="" />
<input type="hidden" name="id" id="id" value="" />
<div name="main_content" id="main_content">

<?php
if($notifier->hasNotifications(koNotifier::ALL)) {
	$notifier->notify();
}

hook_show_case_pre($_SESSION["show"]);

switch($_SESSION['show']) {
	case 'list_trackings':
		ko_list_trackings();
	break;

	case 'new_tracking':
		ko_formular_tracking('new');
	break;

	case 'edit_tracking':
		ko_formular_tracking('edit', $id);
	break;

	case 'enter_tracking':
		ko_tracking_enter_form();
	break;

	case 'multiedit':
		ko_multiedit_formular('ko_tracking', $do_columns, $do_ids, $order, array('cancel' => 'list_trackings'));
	break;

	case 'multiedit_mod':
		ko_multiedit_formular('ko_tracking_entries', $do_columns, $do_ids, $order, array('cancel' => 'mod_entries'));
	break;

	case 'tracking_settings':
		ko_tracking_settings();
	break;

	case 'list_tracking_entries':
		ko_list_tracking_entries();
	break;

	case 'new_tracking_entry':
		ko_formular_tracking_entry('new');
	break;

	case 'edit_tracking_entry':
		ko_formular_tracking_entry('edit', $edit_id);
	break;

	case 'mod_entries':
		ko_list_tracking_mod_entries();
	break;

	case 'edit_tracking_mod':
		ko_formular_tracking_entry_mod($edit_id);
	break;


	default:
		//HOOK: Plugins erlauben, neue Show-Cases zu definieren
    hook_show_case($_SESSION['show']);
  break;
}//switch(show)

//HOOK: Plugins erlauben, die bestehenden Show-Cases zu erweitern
hook_show_case_add($_SESSION['show']);

?>
</div>
</form>
</main>

</div>

<?php include($ko_path . "footer.php"); ?>

</body>
</html>
