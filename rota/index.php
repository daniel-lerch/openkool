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

ob_start();

$ko_path = '../';
$ko_menu_akt = 'rota';

include($ko_path . 'inc/ko.inc');
include('inc/rota.inc');

//Redirect to SSL if needed
ko_check_ssl();

if(!ko_module_installed('rota')) { header('Location: '.$BASE_URL.'index.php'); exit; }

ob_end_flush();

$onload_code = '';
$notifier = koNotifier::Instance();

//Get access rights
ko_get_access('daten');
ko_get_access('rota');

//kOOL Table Array (ko_event used for settings to select event fields)
ko_include_kota(array('ko_rota_teams', 'ko_event'));


$hooks = hook_include_main('rota');
if(sizeof($hooks) > 0) foreach($hooks as $hook) include_once($hook);


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
if($_SERVER['HTTP_REFERER'] != '' && FALSE === strpos($_SERVER['HTTP_REFERER'], '/'.$ko_menu_akt.'/')) $_SESSION['show_start'] = 1;

switch($do_action) {

	case 'schedule':
		if($access['rota']['MAX'] < 1) break;

		$_SESSION['show'] = 'schedule';
	break;

	case 'planning':
		if($access['rota']['MAX'] < 1) break;

		$_SESSION['show'] = 'planning';
	break;

	case 'settings':
		if($access['rota']['MAX'] < 1) break;

		$_SESSION['show_back'] = $_SESSION['show'];
		$_SESSION['show'] = 'settings';
	break;

	case 'submit_rota_settings':
		if($access['rota']['MAX'] < 1) break;

		//User settings
		ko_save_userpref($_SESSION['ses_userid'], 'default_view_rota', format_userinput($_POST['sel_rota_default_view'], 'js'));
		ko_save_userpref($_SESSION['ses_userid'], 'rota_delimiter', format_userinput($_POST['txt_delimiter'], 'text'));
		ko_save_userpref($_SESSION['ses_userid'], 'rota_eventfields', format_userinput($_POST['eventfields'], 'alphanumlist'));

		if($access['rota']['MAX'] > 2) {
			ko_save_userpref($_SESSION['ses_userid'], 'rota_orderby', format_userinput($_POST['orderby'], 'alpha'));
			ko_save_userpref($_SESSION['ses_userid'], 'rota_pdf_names', format_userinput($_POST['pdf_names'], 'uint'));
			ko_save_userpref($_SESSION['ses_userid'], 'rota_show_participation', format_userinput($_POST['sel_show_participation'], 'alphanum'));
		}
		if($access['rota']['MAX'] > 1) {
			ko_save_userpref($_SESSION['ses_userid'], 'rota_markempty', format_userinput($_POST['markempty'], 'uint'));
			ko_save_userpref($_SESSION['ses_userid'], 'rota_pdf_title', format_userinput($_POST['pdf_title'], 'alpha+'));
			ko_save_userpref($_SESSION['ses_userid'], 'rota_pdf_fontsize', format_userinput($_POST['pdf_fontsize'], 'uint'));
			ko_save_userpref($_SESSION['ses_userid'], 'rota_pdf_use_colors', format_userinput($_POST['pdf_use_colors'], 'uint'));
		}

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

			// Save consensus settings
			ko_set_setting('consensus_eventfields', format_userinput($_POST['consensus_eventfields'], 'alphanumlist'));
			ko_set_setting('consensus_description', format_userinput($_POST['consensus_description'], 'text'));
			ko_set_setting('consensus_restrict_link', format_userinput($_POST['consensus_restrict_link'], 'uint'));
			ko_set_setting('consensus_ongoing_cal', format_userinput($_POST['consensus_ongoing_cal'], 'uint'));
			ko_set_setting('consensus_ongoing_cal_timespan', format_userinput($_POST['consensus_ongoing_cal_timespan'], 'text'));
			ko_set_setting('consensus_display_participation', format_userinput($_POST['consensus_display_participation'], 'uint'));
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
		$old_team = db_select_data('ko_rota_teams', "WHERE `id` = '$id'", '*', '', '', TRUE);
		if ($old_team['id'] != $id) break;

		kota_submit_multiedit('', 'edit_rota_team');

		if (!$notifier->hasErrors()) {
			$notifier->addInfo(3, $do_action);
			$_SESSION['show'] = 'list_teams';

			//If rotatype has changed export/delete events
			$new_team = db_select_data('ko_rota_teams', 'WHERE `id` = '.$id, '*', '', '', TRUE);
			if ($old_team['rotatype'] != $new_team['rotatype']) {
				//Delete all scheduling entries, as scheduling entries can not really be converted
				db_delete_data('ko_rota_schedulling', "WHERE `team_id` = '$id'");
			}

			if ($old_team['days_range'] != $new_team['days_range']) {
				// remove schedulled days, which are now deactivated in saved team
				$removed_days = array_diff(explode(",", $old_team['days_range']), explode(",", $new_team['days_range']));
				$where = "WHERE team_id = '" . $id . "' AND event_id >= '" . date("Y-m-d", time()) . "'";
				$schedullings = db_select_data("ko_rota_schedulling", $where);
				foreach($schedullings AS $schedulling) {
					if (in_array(date("N", strtotime($schedulling['event_id'])), $removed_days)) {
						$where = " WHERE team_id = '" . $id . "' AND event_id = '" . $schedulling['event_id'] . "'";
						db_delete_data("ko_rota_schedulling", $where);
						ko_log("rota_schedule_delete", json_encode($schedulling));
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

		//Delete scheduling data
		db_delete_data('ko_rota_schedulling', "WHERE `team_id` = '$id'");
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


	case 'ical_links':
		if($access['rota']['MAX'] < 1) break;

		$_SESSION['show'] = 'ical_links';
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
			$ext = strtolower($ext_[sizeof($ext_)-1]);
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
		ko_rota_filesend_parse_post('SEND', $text, $subject, $recipients, $eventid, $restrict_to_teams, $from, $send_files);

		//Send file to recipients
		$subject = strtr($subject, array("\n" => '', "\r" => ''));


		$no_email = $email_recipients = $noemail_recipients = $failed = array();
		foreach($recipients as $recipient) {
			if($recipient['_has_mail']) {
				$success = ko_rota_filesend_send_mail('SEND', $text, $subject, $recipient, $send_files, $eventid, $from, $restrict_to_teams);

				if ($success) $email_recipients[] = $success['email'].' ('.$recipient['id'].')';
				else $failed[] = $recipient;
			} else {
				$no_email[] = $recipient;
				$noemail_recipients[] = $recipient['vorname'].' '.$recipient['nachname'].' ('.$recipient['id'].')';
			}
		}

		$msgF = array();
		if (sizeof($failed) > 0) {
			foreach ($failed as $f) {
				$msgF[] = "{$f['vorname']} {$f['nachname']} ({$f['id']})";
			}
			$notifier->addWarning(1, '', array(implode(', ', $msgF)));
		}
		$msgNE = array();
		if (sizeof($no_email) > 0) {
			foreach ($no_email as $f) {
				$msgNE[] = "{$f['vorname']} {$f['nachname']} ({$f['id']})";
			}
			$notifier->addWarning(2, '', array(implode(', ', $msgNE)));
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

		ko_log('rota_sendfile', 'From: '.$from.', Subject: '.$subject.', Raw text: '.$_POST['text'].', File: '.$filetype.' ('.implode(',', array_keys($send_files)).'), Recipients type: '.$_POST['recipients'].', Recipients: '.implode(', ', $email_recipients).', No Email: '.implode(', ', $noemail_recipients).', Failed: '.implode(', ', $msgF), $log_id);

		ko_create_crm_contact_from_post(TRUE, array('reference' => 'ko_log:'.$log_id, 'leute_ids' => implode(',', $rec_ids)));

		$_SESSION['show'] = 'schedule';
	break;

	case 'download_single_event_export':
		$filename = $_GET['filename'];

		$onload_code = "ko_popup('".$ko_path."download.php?action=file&amp;file=download/pdf/".$filename."');";
		$_SESSION['show'] = 'schedule';
	break;


	//Default:
  default:
		if(!hook_action_handler($do_action))
      include($ko_path.'inc/abuse.inc');
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
		//Access check
		if($access['rota']['ALL'] < 1) {
			foreach($all_teams as $tid => $team) {
				if($access['rota'][$tid] < 1) unset($all_teams[$tid]);
			}
		}
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
	if(substr($_SESSION['rota_timespan'], -1) == 'w') $_SESSION['rota_timestart'] = date_find_last_monday($_SESSION['rota_timestart']);
	else if(substr($_SESSION['rota_timespan'], -1) == 'm') $_SESSION['rota_timestart'] = substr($_SESSION['rota_timestart'], 0, -2).'01';
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
if ($_SESSION['show'] == 'show_filesend' && ko_module_installed('crm')) {
	include($ko_path.'crm/inc/js-selproject.inc');
}
print ko_include_js(array($ko_path.'inc/ckeditor/ckeditor.js', $ko_path.'inc/ckeditor/adapters/jquery.js'));

print ko_include_css();
include($ko_path.'inc/js-sessiontimeout.inc');
include($ko_path.'rota/inc/js-rota.inc');
?>
</head>

<body onload="session_time_init();<?php if(isset($onload_code)) print $onload_code; ?>">

<?php
/*
 * Gibt bei erfolgreichem Login das Menü aus, sonst einfach die Loginfelder
 */
include($ko_path . "menu.php");
ko_get_outer_submenu_code('rota');

?>


<main class="main">
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

	case 'ical_links':
		ko_rota_ical_links();
	break;

	case 'planning':
		ko_rota_planning_list();
	break;

	default:
    hook_show_case($_SESSION['show']);
}//switch(show)


hook_show_case_add($_SESSION['show']);

?>
</div>
</form>
	</main>

	</div>

	<?php include($ko_path . "footer.php"); ?>

</body>
</html>
