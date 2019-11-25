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

ob_start();

$ko_path = '../';
$ko_menu_akt = 'rota';

require __DIR__ . '/../inc/ko.inc.php';
require __DIR__ . '/inc/rota.inc.php';
use OpenKool\koNotifier;

//Redirect to SSL if needed
ko_check_ssl();

if(!ko_module_installed('rota')) header('Location: '.$BASE_URL.'index.php');

ob_end_flush();

$onload_code = '';
$notifier = koNotifier::Instance();

//Get access rights
ko_get_access('daten');
ko_get_access('rota');

//Smarty-Templates-Engine laden
require __DIR__ . '/../inc/smarty.inc.php';

//kOOL Table Array (ko_event used for settings to select event fields)
ko_include_kota(array('ko_rota_teams', 'ko_event'));


$hooks = hook_include_main('rota');
foreach($hooks as $hook) include_once($hook);


//Action
if($_POST['action']) {
	$do_action=$_POST['action'];
	$action_mode = 'POST';
} else if($_GET['action']) {
	$do_action=$_GET['action'];
	$action_mode = 'GET';
} else {
	$do_action = $action_mode = '';
}


//Reset show_start if from another module
if($_SERVER['HTTP_REFERER'] != '' && FALSE === mb_strpos($_SERVER['HTTP_REFERER'], '/'.$ko_menu_akt.'/')) $_SESSION['show_start'] = 1;

switch($do_action) {

	case 'schedule':
		if($access['rota']['MAX'] < 1) break;

		$_SESSION['show'] = 'schedule';
	break;


	case 'settings':
		if($access['rota']['MAX'] < 2) break;

		$_SESSION['show_back'] = $_SESSION['show'];
		$_SESSION['show'] = 'settings';
	break;

	case 'submit_rota_settings':
		if($access['rota']['MAX'] < 2) break;

		//User settings
		ko_save_userpref($_SESSION['ses_userid'], 'default_view_rota', format_userinput($_POST['sel_rota_default_view'], 'js'));
		ko_save_userpref($_SESSION['ses_userid'], 'rota_delimiter', format_userinput($_POST['txt_delimiter'], 'text'));
		ko_save_userpref($_SESSION['ses_userid'], 'rota_markempty', format_userinput($_POST['markempty'], 'uint'));
		if($access['rota']['MAX'] > 2) {
			ko_save_userpref($_SESSION['ses_userid'], 'rota_orderby', format_userinput($_POST['orderby'], 'alpha'));
			ko_save_userpref($_SESSION['ses_userid'], 'rota_pdf_names', format_userinput($_POST['pdf_names'], 'uint'));
			ko_save_userpref($_SESSION['ses_userid'], 'rota_schedule_subgroup_members', format_userinput($_POST['schedule_subgroup_members'], 'uint'));
		}
		if($access['rota']['MAX'] > 1) {
			ko_save_userpref($_SESSION['ses_userid'], 'rota_pdf_fontsize', format_userinput($_POST['pdf_fontsize'], 'uint'));
			ko_save_userpref($_SESSION['ses_userid'], 'rota_pdf_use_colors', format_userinput($_POST['pdf_use_colors'], 'uint'));
		}
		ko_save_userpref($_SESSION['ses_userid'], 'rota_eventfields', format_userinput($_POST['eventfields'], 'alphanumlist'));

		//Global settings
		if($access['rota']['MAX'] > 4) {
			ko_set_setting('rota_showroles', format_userinput($_POST['showroles'], 'uint'));
			if($_POST['showroles'] == 1) ko_set_setting('rota_teamrole', '');
			else ko_set_setting('rota_teamrole', format_userinput($_POST['teamrole'], 'uint'));
			ko_set_setting('rota_leaderrole', format_userinput($_POST['leaderrole'], 'uint'));

			ko_set_setting('rota_manual_ordering', format_userinput($_POST['manual_ordering'], 'uint'));
			if(ko_get_setting('rota_manual_ordering')) {
				$_SESSION['sort_rota_teams'] = 'sort';
				ko_list_set_sorting('ko_rota_teams', 'name');
			} else {
				$_SESSION['sort_rota_teams'] = 'name';
			}


			$weekly_delete = $weekly_export = FALSE;
			//Week start (update export of weekly scheduling if changed)
			$current_value = ko_get_setting('rota_weekstart');
			$new_value = format_userinput($_POST['weekstart'], 'int');
			ko_set_setting('rota_weekstart', $new_value);
			if($current_value != $new_value) $weekly_export = TRUE;

			//Export weekly teams
			$current_value = ko_get_setting('rota_export_weekly_teams');
			$new_value = format_userinput($_POST['export_weekly_teams'], 'uint');
			ko_set_setting('rota_export_weekly_teams', $new_value);
			if($current_value != $new_value) {
				if($new_value == 1) $weekly_export = TRUE;
				else if($new_value == 0) $weekly_delete = TRUE;
			}

			if($weekly_delete) rota_delete_weekly_export();
			else if($weekly_export) rota_export_weekly_teams();

			// Save consensus settings
			ko_set_setting('consensus_eventfields', format_userinput($_POST['consensus_eventfields'], 'alphanumlist'));
			ko_set_setting('consensus_description', format_userinput($_POST['consensus_description'], 'text'));
		}

		$_SESSION['show'] = $_SESSION['show_back'] ? $_SESSION['show_back'] : 'settings';
	break;


	case 'new_team':
		if($access['rota']['MAX'] < 5) break;

		$_SESSION['show'] = 'new_team';
		$onload_code = 'form_set_first_input();'.$onload_code;
	break;


	case 'submit_new_team':
		if($access['rota']['MAX'] < 5) break;

		$new_id = kota_submit_multiedit('', 'new_rota_team');
		if(!$notifier->hasErrors() && $new_id) {
			$notifier->addInfo(2, $do_action);
			$_SESSION['show'] = 'list_teams';
			$_SESSION['rota_teams'][] = $new_id;

			//Set sorting if necessary
			if(ko_get_setting('rota_manual_ordering')) ko_list_set_sorting('ko_rota_teams', 'name');

			$new_team = db_select_data('ko_rota_teams', 'WHERE `id` = '.$new_id, '*', '', '', TRUE);
			ko_log_diff('rota_new_team', $new_team);

			//Create new event group for weekly team
			if($new_team['rotatype'] == 'week' && ko_get_setting('rota_export_weekly_teams') == 1) {
				$egid = db_insert_data('ko_eventgruppen', array('calendar_id' => ko_get_setting('rota_export_calid'), 'name' => $new_team['name'], 'type' => 2));
				db_update_data('ko_rota_teams', "WHERE `id` = '$new_id'", array('export_eg' => $egid));
			}
		}
	break;
	

	case 'edit_team':
		if($access['rota']['MAX'] < 5) break;

		$_SESSION['show'] = 'edit_team';
		$onload_code = 'form_set_first_input();'.$onload_code;
	break;


	case 'submit_edit_team':
		if($access['rota']['MAX'] < 5) break;

		list($table, $columns, $id, $hash) = explode('@', $_POST['id']);
		$id = format_userinput($id, 'uint');
		if(!$id) break;
		$old = db_select_data('ko_rota_teams', "WHERE `id` = '$id'", '*', '', '', TRUE);
		if($old['id'] != $id) break;

		kota_submit_multiedit('', 'edit_rota_team');

		if(!$notifier->hasErrors()) {
			$notifier->addInfo(3, $do_action);
			$_SESSION['show'] = 'list_teams';

			//If rotatype has changed export/delete events
			$new_team = db_select_data('ko_rota_teams', 'WHERE `id` = '.$id, '*', '', '', TRUE);
			if($old['rotatype'] != $new_team['rotatype']) {
				//Delete all scheduling entries, as scheduling entries can not really be converted
				db_delete_data('ko_rota_schedulling', "WHERE `team_id` = '$id'");


				if(ko_get_setting('rota_export_weekly_teams') == 1) {
					//Create new event group if new type is week
					if($new_team['rotatype'] == 'week') {
						$egid = db_insert_data('ko_eventgruppen', array('calendar_id' => ko_get_setting('rota_export_calid'), 'name' => $new_team['name'], 'type' => 2));
						db_update_data('ko_rota_teams', "WHERE `id` = '$newid'", array('export_eg' => $egid));
					}
					//Delete event group if not week type anymore
					else if($old['rotatype'] == 'week') {
						$egid = $old['export_eg'];
						if($egid) {
							db_delete_data('ko_event', "WHERE `eventgruppen_id` = '$egid'");
							db_delete_data('ko_eventgruppen', "WHERE `id` = '$egid'");
						}
						db_update_data('ko_rota_teams', "WHERE `id` = '$newid'", array('export_eg' => '0'));
					}
				}
			}

		}
	break;


	case 'delete_team':
		if($access['rota']['MAX'] < 5) break;

		$id = format_userinput($_POST['id'], 'uint');
		if($access['rota']['ALL'] < 5 && $access['rota'][$id] < 5) break;

		$old = db_select_data('ko_rota_teams', "WHERE `id` = '$id'", '*', '', '', TRUE);
		db_delete_data('ko_rota_teams', "WHERE `id` = '$id'");

		ko_log_diff('rota_delete_team', $old);


		//Delete events for this rota team
		if(ko_get_setting('rota_export_weekly_teams') == 1 && $old['rotatype'] == 'week') {
			$egid = $old['export_eg'];
			if($egid) {
				db_delete_data('ko_event', "WHERE `eventgruppen_id` = '$egid'");
				db_delete_data('ko_eventgruppen', "WHERE `id` = '$egid'");
			}
		}
	break;


	case 'list_teams':
		if($access['rota']['MAX'] < 5) break;
		$_SESSION['show'] = 'list_teams';
	break;


	case 'multiedit':
		if($access['rota']['MAX'] < 5) break;

		//Columns to be edited
		$columns = explode(',', format_userinput($_POST['id'], 'alphanumlist'));
		foreach($columns as $column) {
			$do_columns[] = $column;
		}
		if(sizeof($do_columns) < 1) $notifier->addError(1, $do_action);

		//Get selected rows
		$do_ids = array();
		foreach($_POST['chk'] as $c_i => $c) {
			if($c) {
				if(FALSE === ($edit_id = format_userinput($c_i, 'uint', TRUE))) {
					trigger_error('Not allowed multiedit_id: '.$c_i, E_USER_ERROR);
				}
				if($access['rota']['ALL'] > 2 || $access['rota'][$edit_id] > 2) $do_ids[] = $edit_id;
			}
		}
		if(sizeof($do_ids) < 1) $notifier->addError(1, $do_action);

		if(!$notifier->hasErrors()) {
			$_SESSION['show_back'] = $_SESSION['show'];

			$order = 'ORDER BY '.$_SESSION['sort_rota_teams'].' '.$_SESSION['sort_rota_teams_order'];
			$_SESSION['show'] = 'multiedit';
		}

		$onload_code = 'form_set_first_input();'.$onload_code;
	break;


	case 'submit_multiedit':
		if($access['rota']['MAX'] < 5) break;

		kota_submit_multiedit(5);
		if(!$notifier->hasErrors()) $notifier->addInfo(1, $do_action);
		$_SESSION['show'] = 'list_teams';
	break;





	case 'show_filesend':
		if($access['rota']['MAX'] < 4) break;

		$get_data = $_GET;
		$_SESSION['show'] = 'show_filesend';
	break;


	case 'filesend_upload':
		if($access['rota']['MAX'] < 4) break;

		$get_data = $_POST;
		$_SESSION['show'] = 'show_filesend';

		//Upload file and show form again
		$dissallow_ext = array('php', 'php3', 'inc', 'sh', 'pl');
		$tmp = $_FILES['new_file']['tmp_name'];
		if($tmp) {
			$upload_name = $_FILES['new_file']['name'];
			$ext_ = explode('.', $upload_name);
			$ext = mb_strtolower($ext_[sizeof($ext_)-1]);
			if(in_array($ext, $dissallow_ext)) break;

			$path = $BASE_PATH.'download/pdf/';
			$filename = format_userinput($upload_name, 'alphanumlist', FALSE, 0, array(), '.');
			$ret = move_uploaded_file($tmp, $path.$filename);
			if($ret) {
				$get_data['files'][] = str_replace($BASE_PATH, '', $path.$filename);
			}
		}
	break;



	case 'filesend_delfile':
		if($access['rota']['MAX'] < 4) break;

		$get_data = $_POST;

		$fid = format_userinput($_POST['id'], 'uint');
		if($fid != '') {
			unset($get_data['files'][$fid]);
		}

		$_SESSION['show'] = 'show_filesend';
	break;




	case 'filesend':
		if($access['rota']['MAX'] < 4) break;

		//Get logged in person and he's email addresses
		$p = ko_get_logged_in_person();
		if(!ko_get_leute_email($p, $emails)) $emails = array(ko_get_setting('info_email'));

		//Check for valid sender address
		$from_email = format_userinput($_POST['sender'], 'email');
		if(!check_email($from_email) || !in_array($from_email, $emails)) break;

		//Build sender header with name and email address
		$from_name = $p['vorname'] || $p['nachname'] ? $p['vorname'].' '.$p['nachname'] : $p['firm'];
		$from = array($from_email => $from_name);


		//Get file and filetype from submitted form
		$filetype = $_POST['filetype'];
		$send_files = array();
		foreach($_POST['files'] as $k => $file) {
			$file = realpath($BASE_PATH.$file);
			if(mb_substr($file, 0, mb_strlen($BASE_PATH)) != $BASE_PATH) continue;
			$send_files[$file] = basename($file);
		}


		//Get recipients according to recipients mode
		if(in_array($_POST['recipients'], array('schedulled', 'selectedschedulled', 'manualschedulled'))) {
			if(mb_substr($_POST['filetype'], 0, 5) == 'event') {
				list($mode, $eventid) = explode(':', $filetype);
			} else {
				$events = ko_rota_get_events();
				$eventid = array();
				foreach($events as $e) {
					$eventid[] = $e['id'];
				}
			}
			//Only include shown rota teams
			if($_POST['recipients'] == 'selectedschedulled') {
				$team_ids = $_SESSION['rota_teams'];
			} else if($_POST['recipients'] == 'manualschedulled') {
				$team_ids = $_POST['sel_teams_schedulled'];
			} else {
				$team_ids = '';
			}
			$recipients = ko_rota_get_recipients_by_event($eventid, $team_ids, 4);
		}
		else if($_POST['recipients'] == 'single') {
			$recipients = array();
			foreach($_POST['single_id'] as $sid) {
				$sid = format_userinput($sid, 'uint');
				if(!$sid) continue;
				ko_get_person_by_id($sid, $p);
				if(!$p['id']) continue;
				$recipients[] = $p;
			}
		}
		else {
			$roleid = '';
			switch($_POST['recipients']) {
				case 'selectedmembers':
					$teams = $_SESSION['rota_teams'];
				break;
				case 'selectedleaders':
					$teams = $_SESSION['rota_teams'];
					$roleid = ko_get_setting('rota_leaderrole');
				break;
				case 'allrotamembers':
					$teams = array_keys(db_select_data('ko_rota_teams', 'WHERE 1'));
				break;
				case 'allrotaleaders':
					$teams = array_keys(db_select_data('ko_rota_teams', 'WHERE 1'));
					$roleid = ko_get_setting('rota_leaderrole');
				break;
				case 'manualmembers':
					$teams = $_POST['sel_teams_members'];
				break;
				case 'manualleaders':
					$teams = $_POST['sel_teams_leaders'];
					$roleid = ko_get_setting('rota_leaderrole');
				break;
			}
			$recipients = array();
			foreach($teams as $team) {
				if($access['rota']['ALL'] < 4 && $access['rota'][$team['id']] < 4) continue;
				$rec = ko_rota_get_team_members($team, TRUE, $roleid);
				$recipients = array_merge($recipients, $rec['people']);
			}
		}

		//Add members from selected group (if any)
		if($_POST['recipients_group']) {
			$gid = format_userinput($_POST['recipients_group'], 'uint');
			if($gid) {
				$group = db_select_data('ko_groups', "WHERE `id` = '$gid'", '*', '', '', TRUE);
				if($group['id'] > 0 && $group['id'] == $gid) {
					//Save userpref
					ko_save_userpref($_SESSION['ses_userid'], 'rota_recipients_group', $gid);
					//Get all group members
					$group_members = db_select_data('ko_leute', "WHERE `deleted` = '0' AND `hidden` = '0' AND `groups` LIKE '%g$gid%'");
					foreach($group_members as $member) {
						$recipients[] = $member;
					}
				}
			}
		} else {
			ko_save_userpref($_SESSION['ses_userid'], 'rota_recipients_group', '');
		}

		//Remove double entries
		$rec_ids = array();
		foreach($recipients as $k => $v) {
			if(in_array($v['id'], $rec_ids)) unset($recipients[$k]);
			$rec_ids[] = $v['id'];
		}

		//Save text as template
		if($_POST['save_preset']) {
			$uid = $access['rota']['MAX'] > 4 && $_POST['chk_global'] ? -1 : $_SESSION['ses_userid'];
			ko_save_userpref($uid, format_userinput($_POST['save_preset'], 'js'), $_POST['text'], 'rota_emailtext_presets');
		}


		//Send file to recipients
		$subject = strtr($_POST['subject'], array("\n" => '', "\r" => ''));

		$no_email = $email_recipients = $noemail_recipients = array();
		foreach($recipients as $recipient) {
			$found = ko_get_leute_email($recipient, $emails);
			if($found) {
				//Email text
				$emailtext = strtr($_POST['text'], ko_rota_get_placeholders($recipient, $eventid));

				ko_send_html_mail($from, $emails[0], $subject, ko_emailtext($emailtext), $send_files);
				$email_recipients[] = $emails[0].' ('.$recipient['id'].')';
			} else {
				$no_email[] = $recipient;
				$noemail_recipients[] = $recipient['vorname'].' '.$recipient['nachname'].' ('.$recipient['id'].')';
			}
		}

		if (!$notifier->hasErrors()) {
			$notifier->addInfo(4, $do_action);
		}

		//Show info with recipients without email address
		if(sizeof($no_email) > 0) {
			$my_info_txt = getLL('download_send_info_no_email');
			$missing = array();
			foreach($no_email as $p) {
				$missing[$p['id']] = ($p['vorname'] || $p['nachname']) ? $p['vorname'].' '.$p['nachname'] : $p['firm'];
			}
			$my_info_txt .= ':<br />'.implode(', ', $missing);

			//Store as my_list if empty, otherwise show option to store in my_list
			if(ko_module_installed('leute')) {
				$_SESSION['rota_my_list'] = array_keys($missing);
				$my_info_txt .= '<br /><a href="javascript:sendReq(\'inc/ajax.php\', \'action,sesid\', \'storeinmylist,'.session_id().'\', do_element);">'.getLL('rota_store_in_mylist').'</a>';
			}
		}

		ko_log('rota_sendfile', 'From: '.$from_email.', Subject: '.$subject.', Raw text: '.$_POST['text'].', File: '.$filetype.' ('.$filename.'), Recipients type: '.$_POST['recipients'].', Recipients: '.implode(', ', $email_recipients).', No Email: '.implode(', ', $noemail_recipients));

		$_SESSION['show'] = 'schedule';
	break;




	//Submenus
  case 'move_sm_left':
  case 'move_sm_right':
    ko_submenu_actions('rota', $do_action);
  break;


	//Default:
	default:
		hook_action_handler($do_action);
	break;


}//switch(do_action)


//Hook
hook_action_handler_add($do_action);



//Reread access rights
if(in_array($do_action, array('submit_new_team'))) {
	ko_get_access('rota', '', TRUE);
}



//Set some defaults
if(!isset($_SESSION['rota_teams']) || $_SESSION['rota_teams'] == '') {
  $user_teams = ko_get_userpref($_SESSION['ses_userid'], 'rota_teams');
  if($user_teams) {
    $_SESSION['rota_teams'] = explode(',', $user_teams);
  } else {
		$all_teams = db_select_data('ko_rota_teams');
    $_SESSION['rota_teams'] = array_keys($all_teams);
  }
	foreach($_SESSION['rota_teams'] as $k => $v) if($v == '') unset($_SESSION['rota_teams'][$k]);
}

if(!isset($_SESSION['rota_egs']) || $_SESSION['rota_egs'] == '') {
  $user_egs = ko_get_userpref($_SESSION['ses_userid'], 'rota_egs');
  if($user_egs) {
    $_SESSION['rota_egs'] = explode(',', $user_egs);
  } else {
		$all_egs = db_select_data('ko_eventgruppen');
    $_SESSION['rota_egs'] = array_keys($all_egs);
  }
}

if(!$_SESSION['sort_rota_teams']) {
	if(ko_get_setting('rota_manual_ordering')) {
		$_SESSION['sort_rota_teams'] = 'sort';
	} else {
		$_SESSION['sort_rota_teams'] = 'name';
	}
}
if(!$_SESSION['sort_rota_teams_order']) $_SESSION['sort_rota_teams_order'] = 'ASC';

if(!isset($_SESSION['rota_timespan'])) {
	$_SESSION['rota_timespan'] = ko_get_userpref($_SESSION['ses_userid'], 'rota_timespan');
	if(!$_SESSION['rota_timespan']) $_SESSION['rota_timespan'] = '1m';
}
if(!isset($_SESSION['rota_timestart'])) {
	$_SESSION['rota_timestart'] = date('Y-m-d');
	if(mb_substr($_SESSION['rota_timespan'], -1) == 'w') $_SESSION['rota_timestart'] = date_find_last_monday($_SESSION['rota_timestart']);
	else if(mb_substr($_SESSION['rota_timespan'], -1) == 'm') $_SESSION['rota_timestart'] = mb_substr($_SESSION['rota_timestart'], 0, -2).'01';
}


//Include submenus
ko_set_submenues();
?>
<!DOCTYPE html 
  PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php print $_SESSION['lang']; ?>" lang="<?php print $_SESSION['lang']; ?>">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title><?php print $HTML_TITLE.': '.getLL('module_'.$ko_menu_akt); ?></title>
<?php
print ko_include_js(array($ko_path.'inc/jquery/jquery.js', $ko_path.'inc/jquery/jquery-ui.js', $ko_path.'inc/kOOL.js', $ko_path.'inc/ckeditor/ckeditor.js', $ko_path.'inc/ckeditor/adapters/jquery.js'));

print ko_include_css();
include __DIR__ . '/../inc/js-sessiontimeout.inc.php';
include __DIR__ . '/inc/js-rota.inc.php';
$js_calendar->load_files();
?>
</head>

<body onload="session_time_init();<?php if(isset($onload_code)) print $onload_code; ?>">

<?php require __DIR__ . '/../inc/menu.inc.php' ?>


<table width="100%">
<tr>

<td class="main_left" name="main_left" id="main_left">
<?php
print ko_get_submenu_code('rota', 'left');
?>
</td>


<td class="main">
<form action="index.php" method="post" name="formular" enctype="multipart/form-data">
<input type="hidden" name="action" id="action" value="" />
<input type="hidden" name="id" id="id" value="" />
<input type="hidden" name="event_id" id="event_id" value="" />
<div name="main_content" id="main_content">

<?php
if($notifier->hasNotifications(koNotifier::ALL)) {
	$notifier->notify();
}

hook_show_case_pre($_SESSION['show']);

switch($_SESSION['show']) {
	case 'schedule':
		ko_rota_schedule();
	break;

	case 'settings':
		ko_rota_settings();
	break;

	case 'list_teams':
		ko_rota_list_teams();
	break;

	case 'new_team':
		ko_rota_form_team('new');
	break;

	case 'edit_team':
		ko_rota_form_team('edit', format_userinput($_POST['id'], 'uint'));
	break;

	case 'multiedit':
		ko_multiedit_formular('ko_rota_teams', $do_columns, $do_ids, $order, array('cancel' => 'list_teams'));
	break;

	case 'show_filesend':
		ko_rota_send_file_form($get_data);
	break;


	default:
    hook_show_case($_SESSION['show']);
}//switch(show)


hook_show_case_add($_SESSION['show']);

?>
</div>
</form>
</td>

<td class="main_right" name="main_right" id="main_right">

<?php
print ko_get_submenu_code('rota', 'right');
?>

</td>
</tr>

<?php include __DIR__ . '/../config/footer.php' ?>

</table>

</body>
</html>
