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

require_once($BASE_PATH."inc/class.kOOL_listview.php");
require_once($BASE_PATH."consensus/consensus.inc");



/**
 * Show scheduling for events
 */
function ko_rota_schedule($output=TRUE) {
	global $access, $smarty, $ko_path, $ROTA_TIMESPANS, $js_calendar, $PLUGINS;

	if(sizeof($_SESSION['rota_teams']) == 0) return FALSE;
	if($access['rota']['MAX'] < 1) return FALSE;

	$smarty->assign('ko_path', $ko_path);
	$smarty->assign('sesid', session_id());
	$smarty->assign('title', getLL('rota_title_schedule').' '.ko_rota_timespan_title($_SESSION['rota_timestart'], $_SESSION['rota_timespan']));
	$smarty->assign('help', ko_get_help('rota', 'schedule'));

	foreach($ROTA_TIMESPANS as $k => $v) {
		if($v == $_SESSION['rota_timespan']) $current_timespan = $k;
		$ts_labels[$k] = getLL('rota_timespan_'.$v);
	}

	$exports = array();
	if($access['rota']['MAX'] > 1) {
		$exports[] = array(
			'link' => "javascript:sendReq('../rota/inc/ajax.php', 'action,mode,id,sesid', 'export,event,###event_id###,###session_id###', show_box);",
			'html' => '<i class="fa fa-file-excel-o"></i>',
			'title' => getLL('rota_event_export'),
		);
	}
	if($access['rota']['MAX'] > 3) {
		$exports[] = array(
			'link' => "index.php?action=show_filesend&filetype=event:###event_id###",
			'html' => '<i class="fa fa-envelope"></i>',
			'title' => getLL('rota_event_email'),
		);
	}
	$exports[] = array(
		'link' => "javascript:sendReq('../rota/inc/ajax.php', 'action,id,sesid', 'eventmylist,###event_id###,###session_id###', do_element);",
		//'img' => "../images/icon_exportadd_my_list.png",
		'html' => '<span class="fa-stack"><i class="fa fa-list fa-stack-1x"></i><i class="fa fa-chevron-right fa-stack-sm fa-stack-bottom-left text-success"></i><i class="fa fa-plus-circle fa-stack-sm fa-stack-top-right text-success"></i></span>',
		'title' => getLL('rota_event_mylist'),
	);

	//Allow plugins to add new exports per event
	$plugins = hook_get_by_type('rota');
	foreach($plugins as $plugin) {
		if(function_exists('my_rota_event_export_'.$plugin)) {
			call_user_func_array('my_rota_event_export_'.$plugin, array(&$exports));
		}
	}

	//Get events to be schedulled
	$patterns = array('/###event_id###/', '/###session_id###/');
	$events = ko_rota_get_events('', '', FALSE, TRUE);
	foreach($events as $ei => $event) {
		$events[$ei]['schedulling_code'] = ko_rota_get_schedulling_code($event);
		//Process KOTA fields for event subtitles
		$kota_event = $event;
		kota_process_data('ko_event', $kota_event, 'list');
		$events[$ei]['_processed'] = $kota_event;

		$replacements = array($event['id'], session_id());
		$eExports = $exports;
		foreach ($eExports as $k => $eExport) {
			$eExports[$k]['link'] = preg_replace($patterns, $replacements, $eExports[$k]['link']);
		}
		$events[$ei]['exports'] = $eExports;
	}
	$smarty->assign('events', $events);


	//Get weeks for weekly teams
	$order = 'ORDER BY '.$_SESSION['sort_rota_teams'].' '.$_SESSION['sort_rota_teams_order'];
	$rota_teams = db_select_data('ko_rota_teams', "WHERE `id` IN (".implode(',', $_SESSION['rota_teams']).")", '*', $order);
	$show_weeks = FALSE;
	if($_SESSION['rota_timespan'] != '1d') {
		foreach($rota_teams as $team) {
			if($team['rotatype'] == 'week') $show_weeks = TRUE;
		}
	}
	$smarty->assign('show_weeks', $show_weeks);

	if($show_weeks) {
		$weeks = ko_rota_get_weeks($rota_teams, '', TRUE);
		foreach($weeks as $wi => $week) {
			$weeks[$wi]['schedulling_code'] = ko_rota_get_schedulling_code($week, 'week');
		}
	}
	$smarty->assign('weeks', $weeks);


	//Set some stats for navigation etc
	$smarty->assign('stats', array('start' => 1,
																 'end' => sizeof($events),
																 'oftotal' => getLL('list_oftotal'),
																 'total' => sizeof($events),
																 'prevts' => $ROTA_TIMESPANS[max(0, $current_timespan-1)],
																 'nextts' => $ROTA_TIMESPANS[min(sizeof($ROTA_TIMESPANS), $current_timespan+1)],
																 ));
	$smarty->assign('timespans', array('values' => $ROTA_TIMESPANS, 'output' => $ts_labels, 'selected' => $_SESSION['rota_timespan']));


	//Create list of export methods
	$exports = array();

	//List of events as xls
	$exports[] = array('mode' => 'eventlist', 'html' => '<i class="fa fa-file-excel-o"></i>', 'label' => getLL('rota_export_excel_eventlist'));

	//Landscape export if not only one day is displayed
	if($_SESSION['rota_timespan'] != '1d') $exports[] = array('mode' => 'eventtable', 'html' => '<i class="fa fa-file-excel-o"></i>', 'label' => getLL('rota_export_excel_eventtable'));

	//Export with weeks if only weekly teams are displayed
	$show_weektable = TRUE;
	foreach($rota_teams as $team) {
		if($team['rotatype'] == 'event') $show_weektable = FALSE;
	}
	if($show_weektable) $exports[] = array('mode' => 'weektable', 'html' => '<i class="fa fa-file-excel-o"></i>', 'label' => getLL('rota_export_excel_weektable'));

	// helper overview
	$exports[] = array(
		'mode' => 'helperoverviewl',
		'html' => '<i class="fa fa-file-excel-o"></i>',
		'label' => getLL('rota_helperoverview_l_export'),
	);
	$exports[] = array(
		'mode' => 'helperoverviewp',
		'html' => '<i class="fa fa-file-excel-o"></i>',
		'label' => getLL('rota_helperoverview_p_export'),
	);

	//PDF export
	$exports[] = array('mode' => 'pdftable', 'html' => '<i class="fa fa-file-pdf-o"></i>', 'label' => getLL('rota_export_excel_pdftable'));


	//Allow plugins to add new exports
	$plugins = hook_get_by_type('rota');
	foreach($plugins as $plugin) {
		if(function_exists('my_rota_export_'.$plugin)) {
			call_user_func_array('my_rota_export_'.$plugin, array(&$exports));
		}
	}

	$smarty->assign('exports', $exports);


	$smarty->assign('label_statusallevents', getLL('rota_change_status_for_all_events'));
	$smarty->assign('label_week', getLL('rota_calweek'));
	$smarty->assign('label_export_excel_portrait', getLL('rota_export_excel_portrait'));
	$smarty->assign('label_export_excel_landscape', getLL('rota_export_excel_landscape'));

	$smarty->assign('show_eventfields', explode(',', ko_get_userpref($_SESSION['ses_userid'], 'rota_eventfields')));
	$labels = array();
	foreach(explode(',', ko_get_userpref($_SESSION['ses_userid'], 'rota_eventfields')) as $field) {
		$labels[$field] = getLL('kota_ko_event_'.$field);
	}
	$smarty->assign('eventfield_labels', $labels);

	if($access['rota']['MAX'] > 4) $smarty->assign('access_status', TRUE);
	if($access['rota']['MAX'] > 3) $smarty->assign('access_send', TRUE);
	if($access['rota']['MAX'] > 1) $smarty->assign('access_export', TRUE);

	$smarty->assign('label_status_e_opened', getLL('rota_status_e_opened'));
	$smarty->assign('label_status_e_open', getLL('rota_status_e_open'));
	$smarty->assign('label_status_e_closed', getLL('rota_status_e_closed'));
	$smarty->assign('label_status_e_close', getLL('rota_status_e_close'));
	$smarty->assign('label_status_w_opened', getLL('rota_status_w_opened'));
	$smarty->assign('label_status_w_open', getLL('rota_status_w_open'));
	$smarty->assign('label_status_w_closed', getLL('rota_status_w_closed'));
	$smarty->assign('label_status_w_close', getLL('rota_status_w_close'));
	$smarty->assign('label_status_all_close', getLL('rota_status_all_close'));
	$smarty->assign('label_status_all_open', getLL('rota_status_all_open'));
	$smarty->assign('label_increase_timespan', getLL('rota_increase_timespan'));
	$smarty->assign('label_decrease_timespan', getLL('rota_decrease_timespan'));
	$smarty->assign('label_event_export', getLL('rota_event_export'));
	$smarty->assign('label_event_email', getLL('rota_event_email'));
	$smarty->assign('label_event_mylist', getLL('rota_event_mylist'));

	//Date filter (only future events or all)
	if(ko_get_userpref($_SESSION['ses_userid'], 'rota_date_future') == 1) {
		$smarty->assign('label_date_future', getLL('rota_label_date_future_d'));
		$smarty->assign('icon_date_future', 'date_future.png');
		$smarty->assign('action_date_future', 'datefutured');
	} else {
		$smarty->assign('label_date_future', getLL('rota_label_date_future'));
		$smarty->assign('icon_date_future', 'date_future_d.png');
		$smarty->assign('action_date_future', 'datefuture');
	}

	if($output) $smarty->display('ko_rota_schedule.tpl');
	else return $smarty->fetch('ko_rota_schedule.tpl');
}//ko_rota_schedule()






/**
 * Show a list of all rota teams
 */
function ko_rota_list_teams($output=TRUE) {
	global $access;

	if($access['rota']['MAX'] < 5) return;

	$z_where = '';

	//Apply filter according to access level
	$aids = array();
	if($access['rota']['ALL'] < 1) {
		foreach($access['rota'] as $id => $level) {
			$id = intval($id);
			if(!$id) continue;
			if($level > 0) $aids[] = $id;
		}
	}
	if(sizeof($aids) > 0) {
		$z_where .= " AND `id` IN (".implode(',', $aids).") ";
	}

	//Set filters from KOTA
	$kota_where = kota_apply_filter('ko_rota_teams');
	if($kota_where != '') $z_where .= " AND ($kota_where) ";

	$rows = db_get_count('ko_rota_teams', 'id');
	$order = 'ORDER BY '.$_SESSION['sort_rota_teams'].' '.$_SESSION['sort_rota_teams_order'];
	$es = db_select_data('ko_rota_teams', 'WHERE 1=1 '.$z_where, '*', $order);


	$list = new kOOL_listview();

	$list->init('rota', 'ko_rota_teams', array('chk', 'edit', 'delete'), 1, 9999);
	$list->setTitle(getLL('rota_teams_list_title'));
	$list->setAccessRights(array('edit' => 5, 'delete' => 5), $access['rota']);
	$list->setActions(array('edit' => array('action' => 'edit_team'),
													'delete' => array('action' => 'delete_team', 'confirm' => TRUE))
										);
	if ($access['rota']['MAX'] > 4) $list->setActionNew('new_team');
	$list->setStats($rows, '', '', '', TRUE);
	if(!ko_get_setting('rota_manual_ordering')) {
		$list->setSort(TRUE, 'setsort', $_SESSION['sort_rota_teams'], $_SESSION['sort_rota_teams_order']);
	}

	if($output) {
		$list->render($es);
	} else {
		print $list->render($es);
	}
}//ko_rota_list_teams()




/**
 * Form to edit or create a new rota team
 */
function ko_rota_form_team($mode, $id='') {
	global $KOTA, $access;

	if($access['rota']['MAX'] < 5) return;

	if($mode == 'new') {
		$id = 0;
	} else if($mode == 'edit') {
		if(!$id) return FALSE;
	} else {
		return FALSE;
	}

	$form_data['title'] =  $mode == 'new' ? getLL('form_ko_rota_teams_title_new') : getLL('form_ko_rota_teams_title_edit');
	$form_data['submit_value'] = getLL('save');
	$form_data['action'] = $mode == 'new' ? 'submit_new_team' : 'submit_edit_team';
	$form_data['cancel'] = 'list_teams';

	ko_multiedit_formular('ko_rota_teams', '', $id, '', $form_data);
}//ko_rota_form_team()





/**
 * Show user prefs and global settings for the rota module
 */
function ko_rota_settings() {
	global $smarty;
	global $access, $MODULES, $KOTA;

	if($access['rota']['MAX'] < 2 || $_SESSION['ses_userid'] == ko_get_guest_id()) return FALSE;

	//build form
	$gc = 0;
	$rowcounter = 0;
	$frmgroup[$gc]['titel'] = getLL('settings_title_user');
	$frmgroup[$gc]['tab'] = True;

	$frmgroup[$gc]['row'][$rowcounter++]['inputs'][0] = array('desc' => getLL('rota_settings_default_view'),
		'type' => 'select',
		'name' => 'sel_rota_default_view',
		'values' => array('schedule', 'list_teams'),
		'descs' => array(getLL('submenu_rota_schedule'), getLL('submenu_rota_list_teams')),
		'value' => ko_html(ko_get_userpref($_SESSION['ses_userid'], 'default_view_rota'))
	);

	$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('rota_settings_delimiter'),
		'type' => 'text',
		'params' => 'size="10"',
		'name' => 'txt_delimiter',
		'value' => ko_html(ko_get_userpref($_SESSION['ses_userid'], 'rota_delimiter'))
	);
	$value = ko_get_userpref($_SESSION['ses_userid'], 'rota_markempty');
	$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('rota_settings_markempty'),
		'name' => 'markempty',
		'type' => 'switch',
		'label_0' => getLL('no'),
		'label_1' => getLL('yes'),
		'value' => $value == '' ? 0 : $value,
	);
	if($_SESSION['ses_userid'] != ko_get_guest_id()) {
		$value = ko_html(ko_get_userpref($_SESSION['ses_userid'], 'rota_ical_deadline'));
		$frmgroup[$gc]['row'][$rowcounter++]['inputs'][0] = array('desc' => getLL('rota_settings_ical_deadline'),
			'type' => 'select',
			'name' => 'sel_ical_deadline',
			'values' => array(0, -1, -2, -3, -6, -12, -9999),
			'descs' => array(getLL('daten_settings_ical_deadline_0'), getLL('daten_settings_ical_deadline_1'), getLL('daten_settings_ical_deadline_2'), getLL('daten_settings_ical_deadline_3'), getLL('daten_settings_ical_deadline_6'), getLL('daten_settings_ical_deadline_12'), getLL('daten_settings_ical_deadline_9999')),
			'value' => $value,
		);
	}

	if($access['rota']['MAX'] > 2) {
		$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('rota_settings_orderby'),
			'type' => 'select',
			'name' => 'orderby',
			'values' => array('vorname', 'nachname'),
			'descs' => array(getLL('kota_ko_leute_vorname'), getLL('kota_ko_leute_nachname')),
			'value' => ko_get_userpref($_SESSION['ses_userid'], 'rota_orderby'),
		);
		$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('rota_settings_pdf_names'),
			'name' => 'pdf_names',
			'type' => 'select',
			'values' => array(1,2,3,4,5),
			'descs' => array(getLL('rota_settings_pdf_names_1'), getLL('rota_settings_pdf_names_2'), getLL('rota_settings_pdf_names_3'), getLL('rota_settings_pdf_names_4'), getLL('rota_settings_pdf_names_5')),
			'value' => ko_get_userpref($_SESSION['ses_userid'], 'rota_pdf_names'),
		);

		$value = ko_get_userpref($_SESSION['ses_userid'], 'rota_schedule_subgroup_members');
		$frmgroup[$gc]['row'][$rowcounter++]['inputs'][0] = array('desc' => getLL('rota_settings_schedule_subgroup_members'),
			'name' => 'schedule_subgroup_members',
			'type' => 'switch',
			'label_0' => getLL('no'),
			'label_1' => getLL('yes'),
			'value' => $value == '' ? 0 : $value,
		);
	}

	if($access['rota']['MAX'] > 1) {
		$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('rota_settings_pdf_fontsize'),
			'name' => 'pdf_fontsize',
			'type' => 'select',
			'values' => array(7,8,9,10,11,12,13,14,15,16,17,18,19,20),
			'descs' => array(7,8,9,10,11,12,13,14,15,16,17,18,19,20),
			'value' => ko_get_userpref($_SESSION['ses_userid'], 'rota_pdf_fontsize'),
		);
		$value = ko_get_userpref($_SESSION['ses_userid'], 'rota_pdf_use_colors');
		$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('rota_settings_pdf_use_colors'),
			'name' => 'pdf_use_colors',
			'type' => 'switch',
			'label_0' => getLL('no'),
			'label_1' => getLL('yes'),
			'value' => $value == '' ? 0 : $value,
		);
	}

	$values = $descs = $avalues = $adescs = array();
	$exclude = array('eventgruppen_id', 'startdatum', 'enddatum', 'startzeit', 'endzeit', 'room', 'rota', 'reservationen');
	foreach($KOTA['ko_event'] as $field => $data) {
		if(substr($field, 0, 1) == '_' || in_array($field, $exclude)) continue;
		if(substr($field, 0, 9) == 'rotateam_') continue;
		$values[] = $field;
		$descs[] = getLL('kota_ko_event_'.$field) ? getLL('kota_ko_event_'.$field) : $field;
	}
	$avalues = explode(',', ko_get_userpref($_SESSION['ses_userid'], 'rota_eventfields'));
	foreach($avalues as $v) {
		$adescs[] = getLL('kota_ko_event_'.$v);
	}
	$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('rota_settings_eventfields'),
		'type' => 'doubleselect',
		'js_func_add' => 'double_select_add',
		'name' => 'eventfields',
		'values' => $values,
		'descs' => $descs,
		'avalues' => $avalues,
		'avalue' => implode(',', $avalues),
		'adescs' => $adescs,
		'params' => 'size="5"',
		'show_moves' => TRUE,
	);
	$frmgroup[$gc]['row'][$rowcounter]['inputs'][1] = array('desc' => getLL('rota_settings_show_participation'),
		'name' => 'sel_show_participation',
		'type' => 'select',
		'values' => array('no', 'past', 'all'),
		'descs' => array(getLL('rota_settings_show_participation_no'), getLL('rota_settings_show_participation_past'), getLL('rota_settings_show_participation_all')),
		'value' => ko_get_userpref($_SESSION['ses_userid'], 'rota_show_participation'),
	);



	//Add global settings
	if($access['rota']['MAX'] > 4) {
		$gc++;
		$frmgroup[$gc]['titel'] = getLL('settings_title_global');
		$frmgroup[$gc]['tab'] = True;


		$value = ko_get_setting('rota_showroles');
		$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('rota_settings_showroles'),
			'name' => 'showroles',
			'type' => 'switch',
			'label_0' => getLL('no'),
			'label_1' => getLL('yes'),
			'value' => $value == '' ? 0 : $value,
		);
		$value = ko_get_setting('rota_manual_ordering');
		$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('rota_settings_manual_ordering'),
			'name' => 'manual_ordering',
			'type' => 'switch',
			'label_0' => getLL('no'),
			'label_1' => getLL('yes'),
			'value' => $value == '' ? 0 : $value,
		);

		ko_get_grouproles($roles);
		$values = array('');
		$descs = array('');
		foreach($roles as $id => $role) {
			$values[] = $id;
			$descs[] = $role['name'];
		}
		$params = ko_get_setting('rota_showroles') == 1 ? 'disabled="disabled"' : '';
		$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('rota_settings_teamrole'),
			'type' => 'select',
			'name' => 'teamrole',
			'values' => $values,
			'descs' => $descs,
			'value' => ko_get_setting('rota_teamrole'),
			'params' => $params,
		);
		$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('rota_settings_leaderrole'),
			'type' => 'select',
			'name' => 'leaderrole',
			'values' => $values,
			'descs' => $descs,
			'value' => ko_get_setting('rota_leaderrole'),
		);

		$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('rota_settings_weekstart'),
			'type' => 'select',
			'name' => 'weekstart',
			'values' => array(0, 1, 2, 3, -3, -2, -1),
			'descs' => array(getLL('rota_settings_weekstart_0'), getLL('rota_settings_weekstart_1'), getLL('rota_settings_weekstart_2'), getLL('rota_settings_weekstart_3'), getLL('rota_settings_weekstart_-3'), getLL('rota_settings_weekstart_-2'), getLL('rota_settings_weekstart_-1')),
			'value' => ko_get_setting('rota_weekstart'),
		);
		$value = ko_get_setting('rota_export_weekly_teams');
		$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('rota_settings_export_weekly_teams'),
			'name' => 'export_weekly_teams',
			'type' => 'switch',
			'label_0' => getLL('no'),
			'label_1' => getLL('yes'),
			'value' => $value == '' ? 0 : $value,
		);


		// Consensus settings
		$values = $descs = $avalues = $adescs = array();
		$exclude = array('eventgruppen_id', 'startdatum', 'enddatum', 'startzeit', 'endzeit', 'room', 'rota', 'reservationen');
		foreach($KOTA['ko_event'] as $field => $data) {
			if(substr($field, 0, 1) == '_' || in_array($field, $exclude)) continue;
			if(substr($field, 0, 9) == 'rotateam_') continue;
			$values[] = $field;
			$descs[] = getLL('kota_ko_event_'.$field) ? getLL('kota_ko_event_'.$field) : $field;
		}
		$avalues = explode(',', ko_get_setting('consensus_eventfields'));
		foreach($avalues as $v) {
			$adescs[] = getLL('kota_ko_event_'.$v);
		}
		$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('rota_settings_consensus_eventfields'),
			'type' => 'doubleselect',
			'js_func_add' => 'double_select_add',
			'name' => 'consensus_eventfields',
			'values' => $values,
			'descs' => $descs,
			'avalues' => $avalues,
			'avalue' => implode(',', $avalues),
			'adescs' => $adescs,
			'params' => 'size="5"',
			'show_moves' => TRUE,
		);
		$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('rota_settings_consensus_description'),
			'name' => 'consensus_description',
			'type' => 'richtexteditor',
			'value' => ko_get_setting('consensus_description'),
		);
		$frmgroup[$gc]['row'][$rowcounter++]['inputs'][0] = array('desc' => getLL('rota_settings_restrict_link'),
			'name' => 'consensus_restrict_link',
			'type' => 'switch',
			'value' => ko_get_setting('consensus_restrict_link'),
		);

	}

	//Allow plugins to add further settings
	hook_form('rota_settings', $frmgroup, '', '');


	//display the form
	$smarty->assign('tpl_titel', getLL('rota_settings_form_title'));
	$smarty->assign('tpl_submit_value', getLL('save'));
	$smarty->assign('tpl_action', 'submit_rota_settings');
	$cancel = ko_get_userpref($_SESSION['ses_userid'], 'default_view_rota');
	if(!$cancel) $cancel = 'list_teams';
	$smarty->assign('tpl_cancel', $cancel);
	$smarty->assign('tpl_groups', $frmgroup);
	$smarty->assign('help', ko_get_help('rota', 'settings'));

	$smarty->display('ko_formular.tpl');
}//ko_rota_settings()






/**
 * Export all weekly teams as events
 */
function rota_export_weekly_teams() {
	global $access;

	//Check for access. This can only be called after changing the setting, which needs access level 5 to edit
	if($access['rota']['MAX'] < 5) return;

	if(ko_get_setting('rota_export_weekly_teams') != 1) return;

	//Check for calendar and create new if none is set yet
	$calid = ko_get_setting('rota_export_calid');
	if(!$calid) {
		$calid = db_insert_data('ko_event_calendar', array('name' => getLL('rota_weekly_export_calendar'), 'type' => 2));
		ko_set_setting('rota_export_calid', $calid);
	}

	//Get all current event groups
	$eventgroups = db_select_data('ko_eventgruppen', "WHERE `calendar_id` = '$calid'");

	$order = 'ORDER BY '.$_SESSION['sort_rota_teams'].' '.$_SESSION['sort_rota_teams_order'];
	$teams = db_select_data('ko_rota_teams', "WHERE `rotatype` = 'week'", '*', $order);
	foreach($teams as $team) {

		//Check for event group assigned to this team
		if($team['export_eg'] && in_array($team['export_eg'], array_keys($eventgroups))) {
			$egid = $team['export_eg'];
			$update = TRUE;
		} else {
			$egid = db_insert_data('ko_eventgruppen', array('calendar_id' => $calid, 'name' => $team['name'], 'type' => 2));
			db_update_data('ko_rota_teams', "WHERE `id` = '".$team['id']."'", array('export_eg' => $egid));
			$update = FALSE;
		}

		//Get all scheduled data
		$scheduled = db_select_data('ko_rota_schedulling', "WHERE `team_id` = '".$team['id']."'", '*', '', '', FALSE, TRUE);
		//Create new events
		foreach($scheduled as $s) {
			if($s['schedule'] == '' || $s['event_id'] == '') continue;
			ko_rota_create_weekly_event($s['event_id'], $team['id'], $egid, $s['schedule'], $update);
		}
	}//foreach(teams)

}//rota_export_weekly_teams()





function ko_rota_create_weekly_event($event_id, $team_id, $eg_id, $schedule, $update=TRUE) {
	list($start, $stop) = ko_rota_week_get_startstop($event_id);
	$new_event = array('eventgruppen_id' => $eg_id,
										 'startdatum' => date('Y-m-d', $start+(ko_get_setting('rota_weekstart')*3600*24)),
										 'enddatum' => date('Y-m-d', $stop+(ko_get_setting('rota_weekstart')*3600*24)), 
										 'startzeit' => '00:00:00',
										 'endzeit' => '00:00:00',
										 'title' => implode(', ', ko_rota_schedulled_text($schedule)),
										 'cdate' => date('Y-m-d H:i:s'),
										 'last_change' => date('Y-m-d H:i:s'),
										 'import_id' => 'rota:t'.$team_id.':week'.$event_id,
										 );
	if($update) {
		//Find event by import_id
		$stored_event = db_select_data('ko_event', "WHERE `eventgruppen_id` = '$eg_id' AND `import_id` = 'rota:t$team_id:week$event_id'");
		if(sizeof($stored_event) == 1) {
			if($new_event['title'] != '') {  //Update event if at least one schedulled entry
				unset($new_event['cdate']);
				db_update_data('ko_event', "WHERE `eventgruppen_id` = '$eg_id' AND `import_id` = 'rota:t$team_id:week$event_id'", $new_event);
			} else {  //If last scheduling was deleted then also delete whole event
				db_delete_data('ko_event', "WHERE `eventgruppen_id` = '$eg_id' AND `import_id` = 'rota:t$team_id:week$event_id'");
			}
		} else {
			db_insert_data('ko_event', $new_event);
		}
	} else {
		db_insert_data('ko_event', $new_event);
	}
}//ko_rota_create_weekly_event()




/**
 * Delete all exported events from weekly rota teams
 */
function rota_delete_weekly_export() {
	global $access;

	//Check for access. This can only be called after changing the setting, which needs access level 5 to edit
	if($access['rota']['MAX'] < 5) return;

	if(ko_get_setting('rota_export_weekly_teams') != 0) return;

	//Check for calendar id
	$calid = ko_get_setting('rota_export_calid');
	if(!$calid) return;

	//Get all current event groups
	$eventgroups = db_select_data('ko_eventgruppen', "WHERE `calendar_id` = '$calid'");
	if(sizeof($eventgroups) == 0) return;

	//Delete events
	db_delete_data('ko_event', "WHERE `eventgruppen_id` IN (".implode(',', array_keys($eventgroups)).")");
	//Delete event groups
	db_delete_data('ko_eventgruppen', "WHERE `id` IN (".implode(',', array_keys($eventgroups)).")");
	//Delete calendar
	db_delete_data('ko_event_calendar', "WHERE `id` = '$calid'");
	//Delete export_eg field for rota teams
	db_update_data('ko_rota_teams', 'WHERE 1', array('export_eg' => 0));

	ko_set_setting('rota_export_calid', '');
}//rota_delete_weekly_export()





/**
 * Creates an excel file for a single event
 */
function ko_rota_export_event_xls($eventid) {
	global $access, $ko_path, $DATETIME;

	if($access['rota']['MAX'] < 2) return;

	$event = ko_rota_get_events('', $eventid, TRUE);

	$w = date('Y-W', (strtotime($event['startdatum'])-(ko_get_setting('rota_weekstart')*3600*24)));
	$week = ko_rota_get_weeks('', $w);
	$order = 'ORDER BY '.$_SESSION['sort_rota_teams'].' '.$_SESSION['sort_rota_teams_order'];
	$all_teams = db_select_data('ko_rota_teams', 'WHERE 1', '*', $order);

	$formatting = array('formats' => array('bold' => array('bold' => 1), 'italic' => array('italic' => 1)));
	$data = array();
	$row = 1;

	$data[$row++] = array($event['_date'].' '.getLL('time_at').' '.$event['_time'].' '.getLL('time_oclock'));

	//Add comment rows
	$add_cols = explode(',', ko_get_userpref($_SESSION['ses_userid'], 'rota_eventfields'));
	if(sizeof($add_cols) > 0) {
		$processed_event = $event;
		kota_process_data('ko_event', $processed_event, 'list');
		$data[$row++] = array('');
		foreach($add_cols as $col) {
			if($processed_event[$col]) {
				$formatting['rows'][$row] = 'italic';
				$data[$row++] = array(getLL('kota_ko_event_'.$col), $processed_event[$col]);
			}
		}
	}

	$data[$row++] = array('');


	//Add all teams and the schedulled data
	$log_teams = array();
	foreach($event['teams'] as $tid) {
		if($access['rota']['ALL'] < 2 && $access['rota'][$tid] < 2) continue;
		$log_teams[] = $all_teams[$tid]['name'];

		$formatting['cells'][$row.':0'] = 'bold';
		$datarow = array($all_teams[$tid]['name']);
		if($all_teams[$tid]['rotatype'] == 'event') {
			$schedulled = ko_rota_schedulled_text($event['schedule'][$tid]);
		} else if($all_teams[$tid]['rotatype'] == 'week') {
			$schedulled = ko_rota_schedulled_text($week['schedule'][$tid]);
		}
		foreach($schedulled as $entry) {
			if(ko_get_userpref($_SESSION['ses_userid'], 'rota_markempty') == 1 && $entry == '') {
				$datarow[] = getLL('rota_empty');
			} else {
				$datarow[] = $entry;
			}
		}
		$data[$row++] = $datarow;
	}

	//Create Excel file
	$header = array($event['eventgruppen_name']);
	$title = getLL('rota_export_title').' '.strftime($DATETIME['dmY'], strtotime($event['startdatum']));
	$ko_path = '../../';
	$filename = $ko_path.'download/excel/'.getLL('rota_filename').strftime('%d%m%Y_%H%M%S', time()).'.xlsx';
	$filename = ko_export_to_xlsx($header, $data, $filename, $title, 'portrait', array(), $formatting);
	$ko_path = '../';

	ko_log('rota_export', 'event_xls: '.$eventid.': '.$event['eventgruppen_name'].' ('.$event['_date'].') - teams: '.implode(', ', $log_teams));

	return basename($filename);
}//ko_rota_export_event_xls()






/**
 * Creates an excel file for a list of events
 */
function ko_rota_export_events_xls($date) {
	global $access, $ko_path, $DATETIME;

	if($access['rota']['MAX'] < 2) return;

	$events = ko_rota_get_events('', '', TRUE);
	$weeks = ko_rota_get_weeks();
	$order = 'ORDER BY '.$_SESSION['sort_rota_teams'].' '.$_SESSION['sort_rota_teams_order'];
	$all_teams = db_select_data('ko_rota_teams', 'WHERE 1', '*', $order);

	$formatting = array('formats' => array('bold' => array('bold' => 1), 'italic' => array('italic' => 1)));
	$data = array();
	$row = 1;
	foreach($events as $event) {
		$w = date('Y-W', (strtotime($event['startdatum'])-(ko_get_setting('rota_weekstart')*3600*24)));
		$data[$row++] = array('');
		$formatting['rows'][$row] = 'bold';

		if($_SESSION['rota_timespan'] == '1d') {
			$data[$row++] = array($event['eventgruppen_name'].' '.getLL('time_at').' '.$event['_time']);
		} else {
			$data[$row++] = array(strftime($DATETIME['ddmy'], strtotime($event['startdatum'])).' '.getLL('time_at').' '.$event['_time'], $event['eventgruppen_name']);
		}


		//Add comment rows
		$add_cols = explode(',', ko_get_userpref($_SESSION['ses_userid'], 'rota_eventfields'));
		if(sizeof($add_cols) > 0) {
			$processed_event = $event;
			kota_process_data('ko_event', $processed_event, 'list');
			foreach($add_cols as $col) {
				if($processed_event[$col]) {
					$formatting['rows'][$row] = 'italic';
					$data[$row++] = array(getLL('kota_ko_event_'.$col), $processed_event[$col]);
				}
			}
		}


		//Add all teams and the schedulled data
		foreach($event['teams'] as $tid) {
			if($access['rota']['ALL'] < 2 && $access['rota'][$tid] < 2) continue;

			$datarow = array($all_teams[$tid]['name']);
			if($all_teams[$tid]['rotatype'] == 'event') {
				$schedulled = ko_rota_schedulled_text($event['schedule'][$tid]);
			} else if($all_teams[$tid]['rotatype'] == 'week') {
				$schedulled = ko_rota_schedulled_text($weeks[$w]['schedule'][$tid]);
			}
			foreach($schedulled as $entry) {
				if(ko_get_userpref($_SESSION['ses_userid'], 'rota_markempty') == 1 && $entry == '') {
					$datarow[] = getLL('rota_empty');
				} else {
					$datarow[] = $entry;
				}
			}
			$data[$row++] = $datarow;
		}
	}//foreach(events as event)

	//Create Excel file
	$title = getLL('rota_export_title').' '.strftime($DATETIME['dmY'], strtotime($date));
	if($_SESSION['rota_timespan'] == '1d') {
		$header = array(strftime($DATETIME['DdMY'], strtotime($date)));
	} else {
		$header = array(ko_rota_timespan_title($_SESSION['rota_timestart'], $_SESSION['rota_timespan']));
	}
	$ko_path = '../../';
	$filename = $ko_path.'download/excel/'.getLL('rota_filename').strftime('%d%m%Y_%H%M%S', time()).'.xlsx';
	$filename = ko_export_to_xlsx($header, $data, $filename, $title, 'portrait', array(), $formatting);
	$ko_path = '../';

	ko_log('rota_export', 'events_xls: '.$date.' - '.getLL('rota_timespan_'.$_SESSION['rota_timespan']));

	return basename($filename);
}//ko_rota_export_events_xls()






function ko_rota_export_landscape_xls($date, $mode) {
	global $access, $ko_path, $DATETIME;

	if($access['rota']['MAX'] < 2) return;

	$events = ko_rota_get_events('', '', TRUE);
	$weeks = ko_rota_get_weeks();

	$order = 'ORDER BY '.$_SESSION['sort_rota_teams'].' '.$_SESSION['sort_rota_teams_order'];
	$all_teams = db_select_data('ko_rota_teams', 'WHERE 1', '*', $order);

	$formatting = array('formats' => array('bold' => array('bold' => 1), 'italic' => array('italic' => 1)));
	$data = array();
	$row = 1;
	$delimiter = strtr(ko_get_userpref($_SESSION['ses_userid'], 'rota_delimiter'), array('<br />' => "\n", '<br>' => "\n"));
	
	if($mode == 'events') {
		//Header row
		$headerrow = array(getLL('kota_listview_ko_event_startdatum'), getLL('kota_listview_ko_event_eventgruppen_id'));

		//Add event fields
		$add_cols = explode(',', ko_get_userpref($_SESSION['ses_userid'], 'rota_eventfields'));
		foreach($add_cols as $k => $v) {
			if(!$v) unset($add_cols[$k]);
		}
		if(sizeof($add_cols) > 0) {
			foreach($add_cols as $col) {
				$headerrow[] = getLL('kota_ko_event_'.$col);
			}
		}

		//Rota teams' names in header
		foreach($all_teams as $tid => $team) {
			if(!in_array($tid, $_SESSION['rota_teams'])) continue;
			if($access['rota']['ALL'] < 2 && $access['rota'][$tid] < 2) continue;
			$headerrow[] = $all_teams[$tid]['name'];
		}
		$formatting['rows'][$row] = 'bold';
		$data[$row++] = $headerrow;


		//All events
		foreach($events as $event) {
			$event_teamids = array_keys(db_select_data('ko_rota_teams', "WHERE `eg_id` REGEXP '(^|,)".$event['eventgruppen_id']."(,|$)'"));

			$datarow = array($event['_date'].' '.$event['_time'], $event['eventgruppen_name']);
			//Add event fields
			if(sizeof($add_cols) > 0) {
				$processed_event = $event;
				kota_process_data('ko_event', $processed_event, 'list');
				foreach($add_cols as $col) {
					$datarow[] = $processed_event[$col];
				}
			}

			$w = date('Y-W', (strtotime($event['startdatum'])-(ko_get_setting('rota_weekstart')*3600*24)));

			//Add all teams and the scheduled data
			foreach($all_teams as $tid => $team) {
				if(!in_array($tid, $_SESSION['rota_teams'])) continue;
				if($access['rota']['ALL'] < 2 && $access['rota'][$tid] < 2) continue;
				if(!in_array($tid, $event_teamids)) {
					$datarow[] = '';
					continue;
				}

				if($team['rotatype'] == 'event') {
					$entry = implode($delimiter, ko_rota_schedulled_text($event['schedule'][$tid]));
					if(ko_get_userpref($_SESSION['ses_userid'], 'rota_markempty') == 1 && $entry == '') {
						$datarow[] = getLL('rota_empty');
					} else {
						$datarow[] = $entry;
					}
				} else if($team['rotatype'] == 'week') {
					$entry = implode($delimiter, ko_rota_schedulled_text($weeks[$w]['schedule'][$tid]));
					if(ko_get_userpref($_SESSION['ses_userid'], 'rota_markempty') == 1 && $entry == '') {
						$datarow[] = getLL('rota_empty');
					} else {
						$datarow[] = $entry;
					}
				} else {
					$datarow[] = '';
				}
			}//foreach(all_teams as tid)
			$data[$row++] = $datarow;
		}//foreach(events as event)
	}

	else if($mode == 'weeks') {
		//Header row
		$headerrow = array(getLL('rota_header_weeknum'), getLL('rota_header_weeknum_date'));
		foreach($_SESSION['rota_teams'] as $tid) {
			if($access['rota']['ALL'] < 2 && $access['rota'][$tid] < 2) continue;
			$headerrow[] = $all_teams[$tid]['name'];
		}
		$formatting['rows'][$row] = 'bold';
		$data[$row++] = $headerrow;

		//All weeks
		foreach($weeks as $week) {
			$datarow = array($week['num'].'-'.$week['year'], $week['_date']);

			//Add all teams and the schedulled data
			foreach($_SESSION['rota_teams'] as $tid) {
				if($access['rota']['ALL'] < 2 && $access['rota'][$tid] < 2) continue;
				if(!in_array($tid, $week['teams'])) continue;
				$entry = implode($delimiter, ko_rota_schedulled_text($week['schedule'][$tid]));
				if(ko_get_userpref($_SESSION['ses_userid'], 'rota_markempty') == 1 && $entry == '') {
					$datarow[] = getLL('rota_empty');
				} else {
					$datarow[] = $entry;
				}
			}//foreach(all_teams as tid)
			$data[$row++] = $datarow;
		}//foreach(weeks as week)
	}

	//Create Excel file
	$header = array(getLL('rota_title_schedule').' '.ko_rota_timespan_title($_SESSION['rota_timestart'], $_SESSION['rota_timespan']));
	$title = getLL('rota_export_title').' '.strftime($DATETIME['dmY'], strtotime($date));
	$ko_path = '../../';
	$filename = $ko_path.'download/excel/'.getLL('rota_filename').strftime('%d%m%Y_%H%M%S', time()).'.xlsx';
	$filename = ko_export_to_xlsx($header, $data, $filename, $title, 'landscape', array(), $formatting);
	$ko_path = '../';

	ko_log('rota_export', 'landscape_xls: '.$mode.': '.$date.' - '.getLL('rota_timespan_'.$_SESSION['rota_timespan']));

	return basename($filename);
}//ko_rota_export_landscape_xls()





/**
 *
 *
 * Always include all teams not just those active in the session. For this, an excel export can be sent.
 */
function ko_rota_get_placeholders($p, $eventid='', $consensus_teams=NULL) {
	global $ko_path, $BASE_URL;

	//Set all to empty so the markers won't show up in the mailtext if not set below
	$r = array('[[FIRSTNAME]]' => '',
		'[[LASTNAME]]' => '',
		'[[_SALUTATION]]' => '',
		'[[_SALUTATION_FORMAL]]' => '',
		'[[TEAM_NAME]]' => '',
		'[[LEADER_TEAM_NAME]]' => '',
		'[[ALL_EVENTS]]' => '',
		'[[ALL_EVENTS_SCHEDULE]]' => '',
		'[[TEAM_EVENTS]]' => '',
		'[[TEAM_EVENTS_SCHEDULE]]' => '',
		'[[LEADER_TEAM_EVENTS]]' => '',
		'[[LEADER_TEAM_EVENTS_SCHEDULE]]' => '',
		'[[PERSONAL_SCHEDULE]]' => '',
		'[[CONSENSUS_LINK]]' => '',
		'[[ICAL_LINK]]' => '',
	);

	$all_events = ko_rota_get_events('', $eventid, TRUE);
	//ko_rota_get_events() return single event if one event_id is given, so create an array of events again
	if(!is_array($eventid) && $eventid > 0) $all_events = array($all_events);

	$weeks = ko_rota_get_weeks();
	$all_teams = db_select_data('ko_rota_teams', 'WHERE 1');

	//All teams for this person
	$leader_role = ko_get_setting('rota_leaderrole');
	$personal_teams = $leader_teams = array();
	foreach($all_teams as $team) {
		$members = ko_rota_get_team_members($team, TRUE, 'all');
		if(in_array($p['id'], array_keys($members['people']))) {
			$personal_teams[$team['id']] = $team;
		}

		if($leader_role) {
			$leaders = ko_rota_get_team_members($team, TRUE, $leader_role);
			if(in_array($p['id'], array_keys($leaders['people']))) {
				$leader_teams[$team['id']] = $team;
			}
		}
	}

	//Consensus
	$consensus_link = $BASE_URL;
	if(substr($BASE_URL, -1) != '/') $consensus_link .= '/';
	$teams_url = '';
	if ($consensus_teams !== NULL) {
		$teams_url = implode('-', $consensus_teams);
	}
	$r['[[CONSENSUS_LINK]]'] = '<a href="'.$consensus_link.'consensus/?x=' . $p['id'] . 'x' . str_replace('-', '', $_SESSION['rota_timestart']) . 'x' . $_SESSION['rota_timespan'] . ($teams_url ? 'x' . $teams_url : '') . 'x' . substr(md5($p['id'] . $_SESSION['rota_timestart'] . $_SESSION['rota_timespan'] . $teams_url . KOOL_ENCRYPTION_KEY), 0, 6).'">'.getLL('ko_consensus').'</a>';

	$ical_link = $BASE_URL;
	if(substr($BASE_URL, -1) != '/') $ical_link .= '/';
	$ical_link .= 'rotaical/index.php?person='.$p['id'].'x'.strtolower(substr(md5($p['id'] . KOOL_ENCRYPTION_KEY . 'rotaIcal' . KOOL_ENCRYPTION_KEY . 'rotaIcal' . KOOL_ENCRYPTION_KEY . $p['id']), 0, 10));
	$r['[[ICAL_LINK]]'] = $ical_link;

	$r['[[FIRSTNAME]]'] = $p['vorname'];
	$r['[[LASTNAME]]'] = $p['nachname'];
	$r['[[_SALUTATION]]'] = getLL('mailing_salutation_'.$p['geschlecht']);
	$r['[[_SALUTATION_FORMAL]]'] = getLL('mailing_salutation_formal_'.$p['geschlecht']);
	foreach($personal_teams as $team) {
		$r['[[TEAM_NAME]]'] .= $team['name'].', ';
	}
	$r['[[TEAM_NAME]]'] = substr($r['[[TEAM_NAME]]'], 0, -2);
	foreach($leader_teams as $team) {
		$r['[[LEADER_TEAM_NAME]]'] .= $team['name'].', ';
	}
	$r['[[LEADER_TEAM_NAME]]'] = substr($r['[[LEADER_TEAM_NAME]]'], 0, -2);


	foreach($all_events as $event) {
		$w = date('Y-W', (strtotime($event['startdatum'])-(ko_get_setting('rota_weekstart')*3600*24)));

		$txt_event = $event['_date'].' ('.$event['_time'].'): '.$event['eventgruppen_name'].' ('.($event['title']?$event['title']:$event['kommentar']).')';

		$txt_schedule = $txt_schedule_leader = '';
		foreach($personal_teams as $tid => $team) {
			if($all_teams[$tid]['rotatype'] == 'event') {
				$schedulled = ko_rota_schedulled_text($event['schedule'][$tid]);
			} else if($all_teams[$tid]['rotatype'] == 'week') {
				$schedulled = ko_rota_schedulled_text($weeks[$w]['schedule'][$tid]);
			}
			$txt_schedule .= $team['name'].': '.implode(ko_get_userpref($_SESSION['ses_userid'], 'rota_delimiter'), $schedulled)."\n";
			if(in_array($tid, array_keys($leader_teams))) {
				$txt_schedule_leader .= $team['name'].': '.implode(ko_get_userpref($_SESSION['ses_userid'], 'rota_delimiter'), $schedulled)."\n";
			}
		}


		//ALL_EVENTS: Include all rota events
		$r['[[ALL_EVENTS]]'] .= $txt_event."\n";
		$r['[[ALL_EVENTS_SCHEDULE]]'] .= strtoupper($txt_event)."\n".$txt_schedule."\n";

		//TEAM_EVENTS: Only include event, if person is assigned to one of this event's teams
		$found = FALSE;
		foreach($personal_teams as $tid => $team) {
			if(in_array($tid, $event['teams'])) $found = TRUE;
		}
		if($found) {
			$r['[[TEAM_EVENTS]]'] .= $txt_event."\n";
			$r['[[TEAM_EVENTS_SCHEDULE]]'] .= strtoupper($txt_event)."\n".$txt_schedule."\n";
		}

		//TEAM_EVENTS_LEADER: Only include event, if person is assigned to one of this event's teams as leader
		$found = FALSE;
		foreach($leader_teams as $tid => $team) {
			if(in_array($tid, $event['teams'])) $found = TRUE;
		}
		if($found) {
			$r['[[LEADER_TEAM_EVENTS]]'] .= $txt_event."\n";
			$r['[[LEADER_TEAM_EVENTS_SCHEDULE]]'] .= strtoupper($txt_event)."\n".$txt_schedule_leader."\n";
		}

		//PERSONAL: Only show event, where this person is scheduled
		$found = FALSE;
		$schedulled = ko_rota_get_recipients_by_event_by_teams($event['id']);
		$txt = '';
		foreach($schedulled as $tid => $people) {
			if(in_array($p['id'], array_keys($people))) {
				$found = TRUE;

				if($all_teams[$tid]['rotatype'] == 'event') {
					$txt .= $all_teams[$tid]['name'].': '.implode(ko_get_userpref($_SESSION['ses_userid'], 'rota_delimiter'), ko_rota_schedulled_text($event['schedule'][$tid]))."\n";
				} else if($all_teams[$tid]['rotatype'] == 'week') {
					$txt .= $all_teams[$tid]['name'].': '.implode(ko_get_userpref($_SESSION['ses_userid'], 'rota_delimiter'), ko_rota_schedulled_text($weeks[$w]['schedule'][$tid]))."\n";
				}
			}
		}
		if($found) {
			$r['[[PERSONAL_SCHEDULE]]'] .= strtoupper($txt_event)."\n".$txt."\n";
		}
	}

	foreach($r as $k => $v) {
		$r[$k] = nl2br(trim($v));
	}

	return $r;
}//ko_rota_get_placeholders()






/**
 * Create a nice date title with the given startdate and timespan
 * @param start date Start date of the timespan
 * @param ts string Timespan code (see switch statement for possible values)
 */
function ko_rota_timespan_title($start, $ts) {
	global $DATETIME;

	switch(substr($ts, -1)) {
		case 'd':
			$inc = substr($ts, 0, -1);
			$sT = strtotime($start);
			$eT = strtotime(add2date(add2date($start, 'day', $inc, TRUE), 'day', -1, TRUE));
		break;

		case 'w':
			$inc = substr($ts, 0, -1);
			$sT = strtotime($start);
			$eT = strtotime(add2date(add2date($start, 'week', $inc, TRUE), 'day', -1, TRUE));
		break;

		case 'm':
			$inc = substr($ts, 0, -1);
			$sT = strtotime($start);
			$eT = strtotime(add2date(add2date($start, 'month', $inc, TRUE), 'day', -1, TRUE));
		break;
	}

	if($sT == $eT) {
		$r = strftime($DATETIME['DdMY'], $sT);
	} else if(date('m', $sT) == date('m', $eT)) {
		$r = strftime('%d.', $sT).' - '.strftime($DATETIME['dMY'], $eT);
	} else if(date('Y', $sT) == date('Y', $eT)) {
		$r = strftime($DATETIME['dM'], $sT).' - '.strftime($DATETIME['dMY'], $eT);
	} else {
		$r = strftime($DATETIME['dMY'], $sT).' - '.strftime($DATETIME['dMY'], $eT);
	}

	return $r;
}//ko_rota_timespan_title()





/**
 * Calculate start and stop date for a given week id
 * Remember to correct dates according to setting rota_weekstart by "+(ko_get_setting('rota_weekstart')*3600*24)"
 *
 * @param week_id string YYYY-MM
 * @returns array array($start, $stop), $start and $stop are timestamps
 */
function ko_rota_week_get_startstop($week_id) {
	$one_day = 24*3600;
	$one_week = $one_day*7;
	$year = substr($week_id, 0, 4);
	$week = substr($week_id, 5);
	$test = strtotime(date_find_last_monday($year.'-01-01'));
	if(date('W', $test) > 1) $test += $one_week;
	$start = $test+($week-1)*$one_week;
	$stop = $start+$one_week-$one_day;

	return array($start, $stop);
}//ko_rota_week_get_startstop()







/**
 * PDF export: Table with events as columns and rota teams as rows
 */
function ko_rota_export_landscape_pdf() {
	global $access, $ko_path, $BASE_PATH, $BASE_URL, $DATETIME;

	if($access['rota']['MAX'] < 2) return;

	$events = ko_rota_get_events('', '', TRUE);
	$weeks = ko_rota_get_weeks();

	$order = 'ORDER BY '.$_SESSION['sort_rota_teams'].' '.$_SESSION['sort_rota_teams_order'];
	$all_teams = db_select_data('ko_rota_teams', 'WHERE 1', '*', $order);


	//Start new PDF export
	define('FPDF_FONTPATH',$BASE_PATH.'fpdf/schriften/');
	require_once($BASE_PATH.'fpdf/mc_table.php');
	$pdf=new PDF_MC_Table('L', 'mm', 'A4');
	$pdf->Open();
	$pdf->SetAutoPageBreak(true, 1);
	$pdf->AddFont('fontn','','arial.php');
	$pdf->AddFont('fontb','','arialb.php');
	$pdf->AddPage();
	$pdf->calculateHeight(TRUE);
	$pdf->border(TRUE);
	$PDF_border_x = 5;
	$PDF_border_y = 15;
	$pdf->SetMargins($PDF_border_x, $PDF_border_y, $PDF_border_x);
	$pdf->SetY($PDF_border_y);


	//Page size
	$page_width = 297 - 2*$PDF_border_x;
	$page_height  = 210 - 2*$PDF_border_y;
	$col_w = (int)($page_width / (sizeof($events)+1));
	$cols = array();
	for($i=0; $i<(sizeof($events)+1); $i++) {
		$cols[] = $col_w;
	}


	$pdf->SetWidths($cols);  //Columns widths
	$font_size = ko_get_userpref($_SESSION['ses_userid'], 'rota_pdf_fontsize');
	if(!$font_size) $font_size = 11;
	$pdf->SetFont('fontn', '', $font_size);
	$pdf->SetZeilenhoehe(0.4*$font_size);


	//Formating for title row
	$title_aligns = array();
	$title_aligns[] = 'C';
	$title_fills = array();
	$title_fills[] = 0;

	$title_colors = array();
	$title_colors[] = 'ffffff';
	$title_text_colors = array();
	$title_text_colors[] = '000000';

	$comment_aligns = array();
	$comment_aligns[] = 'L';

	$text_aligns = array();
	$text_aligns[] = 'L';

	//Get list separator setting
	$list_separator = strtr(ko_get_userpref($_SESSION['ses_userid'], 'rota_delimiter'), array('<br />' => "\n", '<br>' => "\n"));



	$data = array();
	$row = 1;
	
	//Header row
	$headerrow = array(getLL('rota_export_title')."\n".ko_rota_timespan_title($_SESSION['rota_timestart'], $_SESSION['rota_timespan']));
	foreach($events as $e) {
		//Get rota teams assigned to this event's group
		$event_teamids[$e['eventgruppen_id']] = array_keys(db_select_data('ko_rota_teams', "WHERE `eg_id` REGEXP '(^|,)".$e['eventgruppen_id']."(,|$)'"));

		//Process event data
		$processed_events[$e['id']] = $e;
		kota_process_data('ko_event', $processed_events[$e['id']], 'list');
		$comment_aligns[] = 'L';

		//Header
		if(defined('DP_HEADER_FORMAT')) {
			$e_title = strtr(DP_HEADER_FORMAT, array('DATE' => sql2datum($e['startdatum']), 'TIME' => substr($e['startzeit'], 0, -3), 'OCLOCK' => getLL('time_oclock'), 'EG_NAME' => $grp['name'], 'ROOM' => $e['room']));
		} else {
			$e_title  = sql2datum($e['startdatum'])."\n";
			$e_title .= $e['eventgruppen_name']."\n";
			$e_title .= substr($e['startzeit'], 0, -3).' '.getLL('time_oclock');
		}
		$headerrow[] = $e_title;

		$title_aligns[] = 'C';
		$text_aligns[] = 'L';
		$title_fills[] = 1;
		$bg_color = $e['eventgruppen_farbe'] ? $e['eventgruppen_farbe'] : 'ffffff';
		$text_color = ko_get_userpref($_SESSION['ses_userid'], 'rota_pdf_use_colors') == 0 ? '000000' : ko_get_contrast_color($bg_color, '000000', 'ffffff');
		$title_colors[] = $bg_color;
		$title_text_colors[] = $text_color;
	}
	$formatting['rows'][$row] = 'bold';

	//Add rows for additional event fields
	$crow = 0;
	$comments = array();
	$event_fields = explode(',', ko_get_userpref($_SESSION['ses_userid'], 'rota_eventfields'));
	foreach($event_fields as $k => $v) {
		if(!$v) unset($event_fields[$k]);
	}
	if(sizeof($event_fields) > 0) {
		foreach($event_fields as $field) {
			$comments[$crow][] = getLL('kota_ko_event_'.$field);
			foreach($events as $event) {
				$comments[$crow][] = $processed_events[$event['id']][$field];
			}
			$crow++;
		}
	}

	//Draw title row
	if(ko_get_userpref($_SESSION['ses_userid'], 'rota_pdf_use_colors') == 1) $pdf->SetFillColors($title_colors);
	else $pdf->SetFillColor(200);
	$pdf->SetFills($title_fills);
	$pdf->SetTextColors($title_text_colors);
	$pdf->SetAligns($title_aligns);
	$pdf->SetFont('fontb', '', $font_size-1);
	$pdf->Row($headerrow);

	//Add comment rows
	$pdf->SetFont('fontn', '', $font_size-1);
	if(sizeof($comments) > 0) {
		$pdf->SetAligns($comment_aligns);
		foreach($comments as $crow) $pdf->Row($crow);
	}
	//Reset normal font
	$pdf->SetFont('fontn', '', $font_size);
	$pdf->UnsetFillColors();
	$pdf->UnsetTextColors();


	//Eigentliche Daten ausgeben
	$pdf->SetFillColor(200);
	//Set text color back to black
	$pdf->UnsetTextColors();
	$pdf->SetTextColor(0);
	$pdf->SetAligns($text_aligns);

	foreach($all_teams as $tid => $team) {
		if(!in_array($tid, $_SESSION['rota_teams'])) continue;
		if($access['rota']['ALL'] < 2 && $access['rota'][$tid] < 2) continue;

		$fills = array();
		$fills[] = 1;  //Mark first column containing the team's name

		$datarow = array($all_teams[$tid]['name']);

		//All events
		foreach($events as $event) {
			$w = date('Y-W', (strtotime($event['startdatum'])-(ko_get_setting('rota_weekstart')*3600*24)));

			if(!in_array($tid, $event_teamids[$event['eventgruppen_id']])) {
				$datarow[] = '';
				continue;
			}

			if($all_teams[$tid]['rotatype'] == 'event') {
				$entry = implode($list_separator, ko_rota_schedulled_text($event['schedule'][$tid]));
				if(ko_get_userpref($_SESSION['ses_userid'], 'rota_markempty') == 1 && $entry == '') {
					$datarow[] = getLL('rota_empty');
				} else {
					$datarow[] = $entry;
				}
			} else if($all_teams[$tid]['rotatype'] == 'week') {
				$entry = implode($list_separator, ko_rota_schedulled_text($weeks[$w]['schedule'][$tid]));
				if(ko_get_userpref($_SESSION['ses_userid'], 'rota_markempty') == 1 && $entry == '') {
					$datarow[] = getLL('rota_empty');
				} else {
					$datarow[] = $entry;
				}
			} else {
				$datarow[] = '';
			}

			$fills[] = 0;
		}//foreach(events as event)
		$pdf->SetFills($fills);
		$pdf->Row($datarow);
	}//foreach(SESSION[rota_teams] as tid)

	//footer right
	$pdf->SetFont('fontn', '', 8);
	$person = ko_get_logged_in_person();
	$creator = $person['vorname'] ? $person['vorname'].' '.$person['nachname'] : $_SESSION['ses_username'];
	$footerRight = sprintf(getLL('tracking_export_label_created'), strftime($DATETIME['dmY'].' %H:%M', time()), $creator);
	$footerStart = $page_width+$PDF_border_x - $pdf->GetStringWidth($footerRight);
	$pdf->Text($footerStart, 210-5, $footerRight);

	//footer left
	$pdf->Text($PDF_border_x, 210-5, $BASE_URL);

	//Logo
	$logo = ko_get_pdf_logo();
	if($logo) {
		$pic = getimagesize($BASE_PATH.'my_images'.'/'.$logo);
		$picWidth = 9 / $pic[1] * $pic[0];
		$pdf->Image($BASE_PATH.'my_images'.'/'.$logo , $page_width+$PDF_border_x-$picWidth, 4, $picWidth);
	}

	ko_log('rota_export', 'landscape_pdf: '.getLL('rota_timespan_'.$_SESSION['rota_timespan']));

	$ko_path = '../../';
	$filename = $ko_path.'download/pdf/'.getLL('rota_filename').strftime('%d%m%Y_%H%M%S', time()).'.pdf';
	$ko_path = '../';
	$pdf->Output($filename);
	return basename($filename);
}//ko_rota_export_landscape_pdf()





function ko_rota_export_helper_overview($orientation='portrait') {
	global $access, $ko_path, $BASE_PATH, $BASE_URL, $DATETIME;

	// TODO: why is $ko_path incorrect??
	require_once $ko_path . '../inc/phpexcel/PHPExcel.php';
	require_once $ko_path . '../inc/phpexcel/PHPExcel/Writer/Excel2007.php';

	if($access['rota']['MAX'] < 2) return;

	$events = ko_rota_get_events('', '', TRUE);
	array_map(function($e) {kota_process_data('ko_event', $e, 'xls'); return $e;}, $events);

	$teams = $_SESSION['rota_teams'];

	if (sizeof($teams) == 0) {
		return FALSE;
	} else {
		$order = 'ORDER BY '.$_SESSION['sort_rota_teams'].' '.$_SESSION['sort_rota_teams_order'];
		$all_teams = db_select_data('ko_rota_teams', 'WHERE `id` in ('.implode(',', $teams).')', '*', $order);
	}

	foreach ($all_teams as $tk => $team) {
		if ($access['rota']['ALL'] < 2 && $access['rota'][$team['id']] < 2) {
			unset($all_teams[$tk]);
		};
	}

	$data = array('events' => $events, 'teams' => $all_teams, 'data' => array());

	foreach ($all_teams as $tk => $team) {
		$team_entry = array('members' => ko_rota_get_team_members($team['id'], TRUE));

		// Add all helpers that were added using free text
		foreach ($events as $event) {
			$data_entry = array();
			if (in_array($team['id'], $event['teams'])) {
				$helpers = ko_rota_get_helpers_by_event_team($event['id'], $team['id']);
				foreach ($helpers as $k => $helper) {
					if ($helper['is_free_text']) {
						$team_entry['members']['people']["manualId_e{$event['id']}_t{$team['id']}_u{$k}"] = array('vorname' => $helper['name'], 'nachname' => '', 'uid' => "e{$event['id']}_t{$team['id']}_u{$k}");
					}
				}
			}
		}

		foreach ($events as $event) {
			$data_entry = array();
			if (!in_array($team['id'], $event['teams'])) {
				$data_entry['disabled'] = TRUE;
			} else {
				$entries = array();
				$helpers = ko_rota_get_helpers_by_event_team($event['id'], $team['id']);
				foreach ($team_entry['members']['people'] as $member) {
					$found = FALSE;
					foreach ($helpers as $k => $helper) {
						if ($helper['id'] == $member['id'] || ($helper['is_free_text'] && $member['uid'] == "e{$event['id']}_t{$team['id']}_u{$k}")) {
							$found = 'TRUE';
							break;
						}
					}
					$entries[] = $found;
				}
				$data_entry['event_entries'] = $entries;
			}
			$team_entry['team_entries'][] = $data_entry;
		}
		$data['data'][] = $team_entry;
	}

	$title = utf8_encode(getLL('rota_helperoverview_title')."\n".ko_rota_timespan_title($_SESSION['rota_timestart'], $_SESSION['rota_timespan']));

	$event_fields = explode(',', ko_get_userpref($_SESSION['ses_userid'], 'rota_eventfields'));
	array_filter($event_fields, function($el) {return trim($el) != '';});

	$use_colors = ko_get_userpref($_SESSION['ses_userid'], 'rota_pdf_use_colors');

	$mark = utf8_encode(getLL('rota_helperoverview_mark'));

	$person = ko_get_logged_in_person();
	$xls_default_font = ko_get_setting('xls_default_font');
	$name = $person['vorname'] . ' ' . $person['nachname'];

	$objPHPExcel = new PHPExcel();
	$objPHPExcel->getProperties()->setCreator(utf8_encode($name));
	$objPHPExcel->getProperties()->setLastModifiedBy(utf8_encode($name));
	$objPHPExcel->getProperties()->setTitle(utf8_encode($title));
	$objPHPExcel->getProperties()->setSubject('kOOL-Export');
	$objPHPExcel->getProperties()->setDescription('');

	$sheet = $objPHPExcel->setActiveSheetIndex(0);
	$sheet->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
	if ($orientation == 'landscape') {
		$sheet->getPageSetup()->setOrientation(
			PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE
		);
	} else {
		$sheet->getPageSetup()->setOrientation(
			PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT
		);
	}

	if ($xls_default_font) {
		$sheet->getDefaultStyle()->getFont()->setName($xls_default_font);
	} else {
		$sheet->getDefaultStyle()->getFont()->setName('Arial');
	}

	$cellstyle = array(
		'title' => array(
			'font' => array(
				'bold' => ko_get_setting('xls_title_bold') ? true : false,
				'size' => 14,
				'name' => utf8_encode(ko_get_setting('xls_title_font'))
			),
			'alignment' => array(
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
			),
		),
		'eventfield' => array(
			'font' => array(
				'size' => 8,
			),
		),
		'teamtitle' => array(
			'font' => array(
				'bold' => ko_get_setting('xls_title_bold') ? true : false,
				'size' => 12,
				'name' => utf8_encode(ko_get_setting('xls_title_font')),
				'color' => array(
					'rgb' => '000000',
				),
			),
			'fill' => array(
				'type' => PHPExcel_Style_Fill::FILL_SOLID,
				'startcolor' => array(
					'rgb' => 'EEEEEE',
				),
			),
			'alignment' => array(
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
			),
		),
		'disabled' => array(
			'fill' => array(
				'type' => PHPExcel_Style_Fill::FILL_SOLID,
				'startcolor' => array(
					'rgb' => 'DDDDDD',
				),
			),
		),
		'bordertop' => array(
			'borders' => array(
				'top' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
					'color' => array('rgb' => '000000'),
				),
			),
		),
		'borderleft' => array(
			'borders' => array(
				'left' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
					'color' => array('rgb' => '000000'),
				),
			),
		),
		'borderright' => array(
			'borders' => array(
				'right' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
					'color' => array('rgb' => '000000'),
				),
			),
		),
		'borderbottom' => array(
			'borders' => array(
				'bottom' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
					'color' => array('rgb' => '000000'),
				),
			),
		),
		'entry' => array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			)
		),
		'borderleft_small' => array(
			'borders' => array(
				'left' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
					'color' => array('rgb' => 'CCCCCC'),
				),
			),
		),
	);

	$cellstyle_p = $cellstyle;
	unset($cellstyle_p['teamtitle']['fill']);
	unset($cellstyle_p['teamtitle']['alignment']);
	$cellstyle_p['teamtitle']['font'] = array(
		'bold' => false,
		'size' => 8
	);

	//Add Logo
	$logo1Path = '../my_images/'.ko_get_pdf_logo();

	if ($orientation == 'landscape') {
		if (file_exists($ko_path . $logo1Path)) {
			$logo1Path = $ko_path . $logo1Path;
			$objDrawing = new PHPExcel_Worksheet_Drawing();
			$objDrawing->setName('Logo');
			$objDrawing->setDescription('Logo');
			$objDrawing->setPath($logo1Path);
			$objDrawing->setCoordinates('A1');
			$objDrawing->setResizeProportional(true);
			$objDrawing->setWidthAndHeight(300, 70);
			$objDrawing->setWorksheet($sheet);
		}

		$sheet->getColumnDimension('A')->setWidth(30);

		//title
		$sheet->getRowDimension(1)->setRowHeight(60);
		$sheet->setCellValueByColumnAndRow(2,1, $title);
		$sheet->getStyle('C1')->applyFromArray($cellstyle['title']);

		$row = 2;
		$col = 0;
		$field_titles = array(getLL('kota_listview_ko_event_eventgruppen_id'), getLL('kota_listview_ko_event_startdatum'), getLL('kota_listview_ko_event_startzeit'));
		foreach ($event_fields as $event_field) {
			$field_titles[] = getLL("kota_listview_ko_event_{$event_field}");
		}
		foreach ($field_titles as $field_title) {
			$sheet->setCellValueByColumnAndRow($col, $row, utf8_encode($field_title));
			$sheet->getStyleByColumnAndRow($col, $row)->applyFromArray($cellstyle['eventfield']);
			$sheet->getStyleByColumnAndRow($col, $row)->getAlignment()->setWrapText(true);
			$row++;
		}

		$row = 2;
		$col = 1;
		foreach ($data['events'] as $event) {
			$row = 2;

			if ($use_colors && $event['eventgruppen_farbe']) {
				$fillstyle = array(
					'fill' => array(
						'type' => PHPExcel_Style_Fill::FILL_SOLID,
						'startcolor' => array(
							'rgb' => strtoupper($event['eventgruppen_farbe']),
						),
					),
					'font' => array(
						'color' => array(
							'rgb' => ko_get_contrast_color($event['eventgruppen_farbe'], '000000', 'FFFFFF'),
						),
					),
				);

				for ($k = $row; $k < $row + sizeof($field_titles); $k++) {
					$sheet->getStyleByColumnAndRow($col, $k)->applyFromArray($fillstyle);
				}
			}

			$fields = array();
			$fields[] = $event['eventgruppen_name'];
			$fields[] = strftime('%a., %d.%m.%Y', strtotime($event['startdatum']));
			$fields[] = ' '.ko_get_time_as_string($event, 1, 'default', FALSE);

			foreach ($event_fields as $event_field) {
				$fields[] = $event[$event_field];
			}
			foreach ($fields as $field) {

				$sheet->setCellValueByColumnAndRow($col, $row, utf8_encode($field));
				$sheet->getStyleByColumnAndRow($col, $row)->applyFromArray($cellstyle['eventfield']);
				$sheet->getStyleByColumnAndRow($col, $row)->getAlignment()->setWrapText(true);

				$row++;
			}
			$sheet->getColumnDimensionByColumn($col)->setWidth(15);
			$col++;
		}

		$k = 0;
		foreach ($data['teams'] as $team) {
			$old_row = $row;
			$col = 0;
			$sheet->getRowDimension($row)->setRowHeight(19);
			$sheet->setCellValueByColumnAndRow($col, $row, utf8_encode($team['name']));
			for ($j = $col; $j <= $col + sizeof($data['events']); $j++) {
				$sheet->getStyleByColumnAndRow($j, $row)->applyFromArray($cellstyle['teamtitle']);
			}
			$row++;

			$team_data = $data['data'][$k];
			foreach ($team_data['members']['people'] as $member) {
				$sheet->setCellValueByColumnAndRow($col, $row, utf8_encode(trim($member['vorname'].' '.$member['nachname'])));
				$row++;
			}
			$col++;

			foreach ($team_data['team_entries'] as $event_entries) {
				$row = $old_row + 1;
				if ($event_entries['disabled']) {
					for ($i = 0; $i < sizeof($team_data['members']['people']); $i++) {
						$sheet->getStyleByColumnAndRow($col, $row)->applyFromArray($cellstyle['disabled']);
						$row++;
					}
				} else {
					foreach ($event_entries['event_entries'] as $helper_entry) {
						if ($helper_entry) {
							$sheet->getStyleByColumnAndRow($col, $row)->applyFromArray($cellstyle['entry']);
							$sheet->setCellValueByColumnAndRow($col, $row, $mark);
						}
						$row++;
					}
				}
				$col++;
			}
			$k++;
		}
	} else {
		if (file_exists($ko_path . $logo1Path)) {
			$logo1Path = $ko_path . $logo1Path;
			$objDrawing = new PHPExcel_Worksheet_Drawing();
			$objDrawing->setName('Logo');
			$objDrawing->setDescription('Logo');
			$objDrawing->setPath($logo1Path);
			$objDrawing->setCoordinates('A1');
			$objDrawing->setResizeProportional(true);
			$objDrawing->setWidthAndHeight(300, 70);
			$objDrawing->setWorksheet($sheet);
		}

		$sheet->getColumnDimension('A')->setWidth(20);
		$sheet->getRowDimension(1)->setRowHeight(60);

		//title
		$sheet->setCellValueByColumnAndRow(3,1, $title);
		$sheet->getStyle('D1')->applyFromArray($cellstyle_p['title']);

		$row = 2;
		$col = 0;
		$field_titles = array(getLL('kota_listview_ko_event_eventgruppen_id'), getLL('kota_listview_ko_event_startdatum'));
		foreach ($event_fields as $event_field) {
			$field_titles[] = getLL("kota_listview_ko_event_{$event_field}");
		}
		foreach ($field_titles as $field_title) {
			$sheet->setCellValueByColumnAndRow($col, $row, utf8_encode($field_title));
			$sheet->getStyleByColumnAndRow($col, $row)->applyFromArray($cellstyle['eventfield']);
			//$sheet->getStyleByColumnAndRow($col, $row)->getAlignment()->setWrapText(true);
			$col++;
		}

		$row = 4;
		$col = 0;
		foreach ($data['events'] as $event) {
			$col = 0;

			if ($use_colors && $event['eventgruppen_farbe']) {
				$fillstyle = array(
					'fill' => array(
						'type' => PHPExcel_Style_Fill::FILL_SOLID,
						'startcolor' => array(
							'rgb' => strtoupper($event['eventgruppen_farbe']),
						),
					),
					'font' => array(
						'color' => array(
							'rgb' => ko_get_contrast_color($event['eventgruppen_farbe'], '000000', 'FFFFFF'),
						),
					),
				);

				for ($k = $col; $k < $col + sizeof($field_titles); $k++) {
					$sheet->getStyleByColumnAndRow($k, $row)->applyFromArray($fillstyle);
				}
			}

			$fields = array();
			$fields[] = $event['eventgruppen_name'];
			$fields[] = strftime('%d.%m.%y', strtotime($event['startdatum'])).' '.ko_get_time_as_string($event, 1, 'default', FALSE);

			foreach ($event_fields as $event_field) {
				$fields[] = $event[$event_field];
			}
			foreach ($fields as $field) {
				$sheet->getColumnDimensionByColumn($col)->setWidth(13);
				$sheet->setCellValueByColumnAndRow($col, $row, utf8_encode($field));
				$sheet->getStyleByColumnAndRow($col, $row)->applyFromArray($cellstyle_p['eventfield']);
				$sheet->getStyleByColumnAndRow($col, $row)->getAlignment()->setWrapText(true);

				$col++;
			}
			$row++;
		}

		$k = 0;
		foreach ($data['teams'] as $team) {
			$old_col = $col;
			$row = 2;
			$sheet->setCellValueByColumnAndRow($col, $row, utf8_encode($team['name']));
			$sheet->getStyleByColumnAndRow($col, $row)->applyFromArray($cellstyle_p['teamtitle']);
			for ($j = $row; $j <= $row + sizeof($data['events']) + 1; $j++) {
				$sheet->getStyleByColumnAndRow($col, $j)->applyFromArray($cellstyle_p['borderleft']);
			}
			$row++;

			$team_data = $data['data'][$k];
			$c = 0;
			foreach ($team_data['members']['people'] as $member) {
				if ($c > 0) {
					for ($j = $row; $j <= $row + sizeof($data['events']); $j++) {
						$sheet->getStyleByColumnAndRow($col, $j)->applyFromArray($cellstyle_p['borderleft_small']);
					}
				}
				$sheet->getColumnDimensionByColumn($col)->setWidth(4);
				$sheet->setCellValueByColumnAndRow($col, $row, utf8_encode($member['vorname'].' '.$member['nachname']));
				$sheet->getStyleByColumnAndRow($col, $row)->getAlignment()->setTextRotation(90);
				$col++;
				$c++;
			}
			$row++;

			foreach ($team_data['team_entries'] as $event_entries) {
				$col = $old_col;
				if ($event_entries['disabled']) {
					for ($i = 0; $i < sizeof($team_data['members']['people']); $i++) {
						$sheet->getStyleByColumnAndRow($col, $row)->getFill()->applyFromArray($cellstyle_p['disabled']['fill']);
						$col++;
					}
				} else {
					foreach ($event_entries['event_entries'] as $helper_entry) {
						if ($helper_entry) {
							$sheet->setCellValueByColumnAndRow($col, $row, $mark);
							$sheet->getStyleByColumnAndRow($col, $row)->applyFromArray($cellstyle['entry']);
						}
						$col++;
					}
				}
				$row++;
			}
			$k++;
		}
	}


	$filename = $ko_path.'../download/excel/'.getLL('rota_filename').date('YmdHis').'.xlsx';
	//simple fileformat for testing
	//$filename = $ko_path.'download/excel/bk.xlsx';
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter->save($filename);

	return $filename;
}





/* TODO: Rework for new rota module, if necessary */
function ko_dp_create_dienste_pdf($dienste_, $monate) {
	global $ko_path, $BASE_PATH, $DATETIME;

	//PDF starten
  define('FPDF_FONTPATH',$BASE_PATH.'fpdf/schriften/');
  require_once($BASE_PATH.'fpdf/mc_table.php');
  $pdf=new PDF_MC_Table('P', 'mm', 'A4');
  $pdf->Open();
  $pdf->SetAutoPageBreak(true, 1);
  $pdf->AddFont('arial','','arial.php');
	$pdf->AddFont('arialb','','arialb.php');
  $pdf->AddPage();
  $pdf->calculateHeight(TRUE);
  $pdf->border(TRUE);
  $pdf->SetMargins(10,5,5);

	$PDF_fontsize_header = 12;
	$PDF_fontsize_event = 11;
	$PDF_fontsize_einteilung = ko_get_setting('dp_fontsize');
  if(!$PDF_fontsize_einteilung) $PDF_fontsize_einteilung = 9;

	$PDF_space_row = 4;
	$PDF_space_title = 1.5;
	$PDF_space_after_table = 6;
	$PDF_rand_rechts = 10;

	$PDF_page_start = 10;
	$PDF_page_end = 277;


	$freetext_dienste = explode(",", ko_get_setting("dp_freetext_dienste"));
	$max_cols = 1;
	foreach($dienste_ as $d_) {
		ko_get_dienste($d__, $d_);
		$d = $d__[$d_];
		$dienste[$d["id"]] = $d;
		//Spaltenanzahl aus Max-Leute
		$num = $d["maxleute"];
		if(in_array($d["id"], $freetext_dienste)) $num++;
		$max_cols = max($max_cols, $num);
		//Eventgruppen
		$temp = explode(",", $d["tg"]);
		foreach($temp as $t) {
			$tgs[] = $t;
		}
	}
	//Eventgruppen, die für die gewählten Dienste relevant sind
	$tgs = array_unique($tgs);

	//Anzahl Spalten herausfinden
	$width = 190/($max_cols+1);
	for($i=0; $i<($max_cols+1); $i++) {
		$cols[] = $width;
	}
	$pdf->SetWidths($cols);


	$PDF_y = $PDF_page_start;


	//Header ausgeben
	$header = getLL("dp_roster")." ";
	if(sizeof($monate) == 1) {
		$header .= $monate[0];
	} else {
		$header .= $monate[0]." - ".$monate[sizeof($monate)-1];
	}
	$pdf->SetFont('arialb', '', $PDF_fontsize_header);
	$pdf->Text($PDF_rand_rechts, $PDF_y, $header);

	//Add logo
	$logo = ko_get_pdf_logo();
	if($logo) {
		$pic = getimageSize($ko_path.'my_images/'.$logo);
		$picWidth = 15 / $pic[1] * $pic[0];
		$pdf->Image($ko_path.'my_images/'.$logo, 200-$picWidth, 5, $picWidth);
	}


	//Add creation date
	$pdf->SetFont('arial', '', 9);
	$pdf->SetFillColor(0);
	$text = getLL("res_info_cdate").": ".strftime($DATETIME["dMY"], time());
	$pdf->Text(200 - $pdf->getStringWidth($text), 292, $text);

	$PDF_y += 10;

	$commentrow = explode(',', ko_get_userpref($_SESSION['ses_userid'], 'rota_eventfields'));
	
	//Durch alle Monate durchgehen
	foreach($monate as $m) {
		list($monat, $jahr) = explode("-", $m);
		ko_get_dp_events($events, $monat, $jahr);
		foreach($events as $e_i => $e) {
			if(!in_array($e["eventgruppen_id"], $tgs)) continue;

			//Anlass
			ko_get_eventgruppe_by_id($e["eventgruppen_id"], $tg);
			$pdf->SetFont('arialb', '', $PDF_fontsize_event);
			$title  = strftime($GLOBALS["DATETIME"]["dMY"], strtotime($e["startdatum"]));
			$title .= ", ".$tg["name"];
			$title .= " (".substr($e["startzeit"], 0, -3).")";

			//Seitenumbruch
			if(($PDF_y+sizeof($dienste)*$PDF_space_row) > $PDF_page_end) {
				$pdf->AddPage();
				$PDF_y = $PDF_page_start;
				$pdf->setY($PDF_y);
			}

			$pdf->Text($PDF_rand_rechts, $PDF_y, $title);

			//Add comment
			$comment = "";
			foreach($commentrow as $crow) {
				if($e[$crow]) $comment .= $e[$crow]." / ";
			}
			if($comment != "") {
				$PDF_y += 2*$PDF_space_title;
				$pdf->SetFont('arial', '', $PDF_fontsize_einteilung);
				$pdf->Text($PDF_rand_rechts, ($PDF_y+0.5*$PDF_space_title), ("  ".substr($comment, 0, -3)) );
			}

			//Einteilungen
			$pdf->setY($PDF_y + $PDF_space_title);
			$pdf->SetFont('arial', '', $PDF_fontsize_einteilung);
			foreach($dienste as $d) {
				//Dienst nur anzeigen, wenn er für diese TG aktiv ist
				$d_tgs = explode(",", $d["tg"]);
				if(!in_array($e["eventgruppen_id"], $d_tgs)) continue;
				
				//Einteilung auslesen und in einzelnen Spalten ausgeben
				ko_get_einteilung($leute, $d["id"], $e["id"]);
				for($i=0; $i<($max_cols+1); $i++) $row[$i] = " ";
				$row[0] = $d["name"];
				$l_count = 0;
				foreach($leute as $l) {
					if(is_numeric($l)) {
						ko_get_person_by_id($l, $p);
						$row[++$l_count] = $p["vorname"]." ".$p["nachname"];
					} else {
						$row[++$l_count] = $l;
					}
				}
				$pdf->Row($row);
				
				//$PDF_y += $PDF_space_row;
			}//foreach(dienste)
			$PDF_y = $pdf->GetY() + $PDF_space_after_table;
		}//foreach(events)

	}//foreach(monate as m)

	$pdf_filename = $ko_path."download/pdf/".getLL("dp_filename_dp_teams").strftime("%d%m%Y_%H%M%S", time()).".pdf";
  $pdf->Output($pdf_filename, false);

	return $pdf_filename;
}//ko_dp_create_dienste_pdf()






function ko_rota_send_file_form($get) {
	global $ko_path, $BASE_PATH, $DATETIME, $access, $smarty;

	if($access['rota']['MAX'] < 4) return FALSE;

	$c = '';

	$c .= '<h3>'.getLL('download_send_title').'</h3>';
	$c .= '<input type="hidden" name="filetype" value="'.$get['filetype'].'" />';
	$c .= '<div class="row">';
	$c .= '<div class="col-xs-12 col-md-8">';
	$c .= '<div class="form-group">';
	$c .= '<label>'.getLL('download_send_sender').':</label>';
	$c .= '<select name="sender" class="input-sm form-control">';
	//Sender: one of the email addresses of this login
	$p = ko_get_logged_in_person();
	if(ko_get_leute_email($p, $emails)) {
		foreach($emails as $email) {
			if(!$email) continue;
			$name = $p['vorname'] || $p['nachname'] ? $p['vorname'].' '.$p['nachname'] : $p['firm'];
			$c .= '<option value="'.$email.'">&quot;'.$name.'&quot; &lt;'.$email.'&gt;</option>';
		}
	} else {
		$info_email = ko_get_setting('info_email');
		$c .= '<option value="'.$info_email.'">'.$info_email.'</option>';
	}
	$c .= '</select>';
	$c .= '</div>';
	$c .= '</div>';
	$c .= '</div>';


	//Possible recipient options
	$c .= '<div class="row">';
	$c .= '<div class="col-xs-12 col-md-8">';
	$c .= '<div class="form-group">';
	$c .= '<label>'.getLL('download_send_recipients').':</label>';
	$c .= '<select class="input-sm form-control" name="recipients" id="recipients">';
	$sel = array($get['recipients'] => 'selected="selected"');

	$c .= '<option value="schedulled" '.$sel['schedulled'].' title="'.getLL('rota_send_schedulled').'">'.getLL('rota_send_schedulled').'</option>';
	$c .= '<option value="selectedschedulled" '.$sel['selectedschedulled'].' title="'.getLL('rota_send_selectedschedulled').'">'.getLL('rota_send_selectedschedulled').'</option>';
	$c .= '<option value="selectedmembers" '.$sel['selectedmembers'].' title="'.getLL('rota_send_selectedmembers').'">'.getLL('rota_send_selectedmembers').'</option>';
	$c .= '<option value="selectedleaders" '.$sel['selectedleaders'].' title="'.getLL('rota_send_selectedleaders').'">'.getLL('rota_send_selectedleaders').'</option>';
	if($access['rota']['ALL'] > 3) {
		$c .= '<option value="allrotamembers" '.$sel['allrotamembers'].' title="'.getLL('rota_send_allrotamembers').'">'.getLL('rota_send_allrotamembers').'</option>';
		$c .= '<option value="allrotamembersconsensus" '.$sel['allrotamembersconsensus'].' title="'.getLL('rota_send_allrotamembersconsensus').'">'.getLL('rota_send_allrotamembersconsensus').'</option>';
		$c .= '<option value="allrotaleaders" '.$sel['allrotaleaders'].' title="'.getLL('rota_send_allrotaleaders').'">'.getLL('rota_send_allrotaleaders').'</option>';
	}
	$c .= '<option value="manualschedulled" '.$sel['manualschedulled'].' title="'.getLL('rota_send_manualschedulled').'">'.getLL('rota_send_manualschedulled').'</option>';
	$c .= '<option value="manualmembers" '.$sel['manualmembers'].' title="'.getLL('rota_send_manualmembers').'">'.getLL('rota_send_manualmembers').'</option>';
	$c .= '<option value="manualleaders" '.$sel['manualleaders'].' title="'.getLL('rota_send_manualleaders').'">'.getLL('rota_send_manualleaders').'</option>';
	$c .= '<option value="single" '.$sel['single'].' title="'.getLL('rota_send_single').'">'.getLL('rota_send_single').'</option>';


	if($get['subject']) {
		$subject = $get['subject'];
	}
	//Set default subject
	else {
		if(substr($get['filetype'], 0, 5) == 'event') {
			list($mode, $eventid) = explode(':', $get['filetype']);
			$event = db_select_data('ko_event AS e, ko_eventgruppen AS eg', "WHERE e.id = '$eventid' AND eg.id = e.eventgruppen_id", 'e.*, eg.name AS eventgruppen_name', '', '', TRUE);
			$subject = '[kOOL] '.getLL('download_send_subject_default').' '.$event['eventgruppen_name'].' '.strftime($DATETIME['dMY'], strtotime($event['startdatum']));
		} else {
			$subject = '[kOOL] '.getLL('download_send_subject_default').' '.ko_rota_timespan_title($_SESSION['rota_timestart'], $_SESSION['rota_timespan']);
		}
	}


	//Add select for recipient mode single
	//TODO: Reset from $get
	$options['single']  = '<div class="row"><div class="col-xs-12 col-md-8"><div class="form-group"><label>'.getLL('rota_send_single_title').':</label>';

	//Get all rota team members
	$order = 'ORDER BY '.$_SESSION['sort_rota_teams'].' '.$_SESSION['sort_rota_teams_order'];
	$teams = db_select_data('ko_rota_teams', 'WHERE 1', '*', $order);
	$people = array();
	foreach($teams as $tid => $team) {
		if($access['rota']['ALL'] < 4 && $access['rota'][$tid] < 4) continue;
		$rec = ko_rota_get_team_members($tid, TRUE);
		$people = array_merge($people, (array)$rec['people']);
	}
	//Sort all possible recipients
	$sort_keys = array();
	foreach($people as $p) {
		$sorted_people[$p['id']] = $p['vorname'].' '.$p['nachname'];
		if(ko_get_userpref($_SESSION['ses_userid'], 'rota_orderby') == 'nachname') $sort_keys[$p['id']] = $p['nachname'].$p['vorname'];
		else $sort_keys[$p['id']] = $p['vorname'].$p['nachname'];
	}
	asort($sort_keys);

	$submittedV = array_filter(explode(',', $_POST['single_id']), function($e){return trim($e)?TRUE:FALSE;});
	$values = $descs = $avalues = $adescs = array();
	foreach($sort_keys as $id => $v) {
		$p = $sorted_people[$id];

		if (isset($sel['single']) && in_array($id, $submittedV)) {
			$avalues[] = $id;
			$avalues[] = $p;
		}
		$values[] = $id;
		$descs[] = $p;
	}
	$value = implode(',', $avalues);
	$localSm = clone($smarty);
	$localSm->assign('input', array('type' => 'doubleselect', 'values' => $values, 'descs' => $descs, 'avalues' => $avalues, 'adescs' => $adescs, 'value' => $value, 'name' => 'single_id', 'html_id' => 'single_id', 'js_func_add' => 'double_select_add'));
	$inputHtml = $localSm->fetch('ko_formular_elements.tmpl');
	$options['single'] .= $inputHtml;
	$options['single'] .= '</div></div></div>';

	//Add a list of all teams to select from (for members)
	$options['manualmembers']  = '<div class="row"><div class="col-xs-12 col-md-8"><div class="form-group"><label>'.getLL('rota_send_manualmembers_title').':</label>';
	$submittedV = array_filter(explode(',', $_POST['sel_teams_members']), function($e){return trim($e)?TRUE:FALSE;});
	$values = $descs = $avalues = $adescs = array();
	//Get all rota teams
	foreach($teams as $team) {
		if($access['rota']['ALL'] < 4 && $access['rota'][$team['id']] < 4) continue;
		if (isset($sel['manualmembers']) && in_array($team['id'], $submittedV)) {
			$avalues[] = $team['id'];
			$avalues[] = $team['name'];
		}
		$values[] = $team['id'];
		$descs[] = $team['name'];
	}
	$value = implode(',', $avalues);
	$localSm->assign('input', array('type' => 'doubleselect', 'values' => $values, 'descs' => $descs, 'avalues' => $avalues, 'adescs' => $adescs, 'value' => $value, 'name' => 'sel_teams_members', 'js_func_add' => 'double_select_add'));
	$inputHtml = $localSm->fetch('ko_formular_elements.tmpl');
	$options['manualmembers'] .= $inputHtml;
	$options['manualmembers'] .= '</div></div></div>';

	//Add a list of all teams to select from (for leaders)
	$options['manualleaders']  = '<div class="row"><div class="col-xs-12 col-md-8"><div class="form-group"><label>'.getLL('rota_send_manualleaders_title').':</label>';
	$submittedV = array_filter(explode(',', $_POST['sel_teams_leaders']), function($e){return trim($e)?TRUE:FALSE;});
	$values = $descs = $avalues = $adescs = array();
	//Get all rota teams
	foreach($teams as $team) {
		if($access['rota']['ALL'] < 4 && $access['rota'][$team['id']] < 4) continue;
		if (isset($sel['manualleaders']) && in_array($team['id'], $submittedV)) {
			$avalues[] = $team['id'];
			$avalues[] = $team['name'];
		}
		$values[] = $team['id'];
		$descs[] = $team['name'];
	}
	$value = implode(',', $avalues);
	$localSm->assign('input', array('type' => 'doubleselect', 'values' => $values, 'descs' => $descs, 'avalues' => $avalues, 'adescs' => $adescs, 'value' => $value, 'name' => 'sel_teams_leaders', 'js_func_add' => 'double_select_add'));
	$inputHtml = $localSm->fetch('ko_formular_elements.tmpl');
	$options['manualleaders'] .= $inputHtml;
	$options['manualleaders'] .= '</div></div></div>';

	//Add a list of all teams to select from (for leaders)
	$options['manualschedulled']  = '<div class="row"><div class="col-xs-12 col-md-8"><div class="form-group"><label>'.getLL('rota_send_manualschedulled_title').':</label>';
	$submittedV = array_filter(explode(',', $_POST['sel_teams_schedulled']), function($e){return trim($e)?TRUE:FALSE;});
	$values = $descs = $avalues = $adescs = array();
	//Get all rota teams
	foreach($teams as $team) {
		if($access['rota']['ALL'] < 4 && $access['rota'][$team['id']] < 4) continue;
		if (isset($sel['manualschedulled']) && in_array($team['id'], $submittedV)) {
			$avalues[] = $team['id'];
			$avalues[] = $team['name'];
		}
		$values[] = $team['id'];
		$descs[] = $team['name'];
	}
	$value = implode(',', $avalues);
	$localSm->assign('input', array('type' => 'doubleselect', 'values' => $values, 'descs' => $descs, 'avalues' => $avalues, 'adescs' => $adescs, 'value' => $value, 'name' => 'sel_teams_schedulled', 'js_func_add' => 'double_select_add'));
	$inputHtml = $localSm->fetch('ko_formular_elements.tmpl');
	$options['manualschedulled'] .= $inputHtml;
	$options['manualschedulled'] .= '</div></div></div>';

	$c .= '</select>';
	$c .= '</div>';
	$c .= '</div>';
	$c .= '</div>';

	if(is_array($options)) {
		foreach($options as $key => $code) {
			$style = isset($sel[$key]) ? '' : 'display: none;';
			$c .= '<div class="recipients_options" style="'.$style.'" id="options_'.$key.'">'.$code.'</div>';
		}
	}
	$c .= '</div>';


	//Group as additional recipients
	$c .= '<div class="row">';
	$c .= '<div class="col-md-8">';
	$c .= '<div class="form-group">';
	$c .= '<label>'.getLL('download_send_recipients_group').':</label>';
	$c .= '<select name="recipients_group" id="recipients_group" class="input-sm form-control">';
	$c .= '<option value=""></option>';

	//Get selected group from POST or userpref
	$cur = $_POST['recipients_group'];
	if(!$cur) $cur = ko_get_userpref($_SESSION['ses_userid'], 'rota_recipients_group');

	$all_groups = db_select_data('ko_groups', 'WHERE 1');
	$groups_values = $groups_output = array();
	$groups = ko_groups_get_recursive(ko_get_groups_zwhere());
	foreach($groups as $grp) {
		if($access['groups']['ALL'] < 1 && $access['groups'][$grp['id']] < 1) continue;

		$pre = '';
		$depth = sizeof(ko_groups_get_motherline($grp['id'], $all_groups));
		for($i=0; $i<$depth; $i++) $pre .= '&nbsp;&nbsp;';

		$sel = $cur == $grp['id'] ? 'selected="selected"' : '';
		$c .= '<option value="'.$grp['id'].'" '.$sel.'>'.$pre.ko_html($grp['name']).'</option>';
	}
	$c .= '</select>';

	$c .= '</div>';
	$c .= '</div>';
	$c .= '</div>';


	//Subject
	$c .= '<div class="row">';
	$c .= '<div class="col-md-8">';
	$c .= '<div class="form-group">';
	$c .= '<label>'.getLL('download_send_subject').':</label>';
	$c .= '<input type="text" class="input-sm form-control" name="subject" value="'.$subject.'">';
	$c .= '</div>';
	$c .= '</div>';
	$c .= '</div>';


	//Files
	$c .= '<div class="form-group">';
	$c .= '<label>'.getLL('download_send_files').':</label>';
	if($get['file'] && !is_array($get['files'])) {
		$files = array($get['file']);
	} else {
		$files = $get['files'];
	}
	$fc = 0;
	$filelist = array();
	foreach($files as $file) {
		if(!file_exists($ko_path.$file)) continue;
		$check = realpath($ko_path.$file);
		if(substr($check, 0, strlen($BASE_PATH)) != $BASE_PATH) continue;

		$filelist[$fc]  = '<a href="'.$ko_path.$file.'" target="_blank">'.basename($file).'</a>&nbsp;&nbsp;';
		$filelist[$fc] .= '<input type="image" onclick="set_action(\'filesend_delfile\', this); set_hidden_value(\'id\', '.$fc.', this);" src="'.$ko_path.'images/icon_trash.png" border="0" />';
		$filelist[$fc] .= '<br />';

		$c .= '<input type="hidden" name="files['.$fc.']" value="'.$file.'" />';
		$fc++;
	}
	if(sizeof($filelist) > 0) {
		$c .= '<ul class="list-group"><li class="list-group-item">'.implode('</li><li class="list-group-item">', $filelist).'</li></ul>';
	}

	//Upload form for new file
	$c .= '<input type="file" name="new_file">';
	$c .= '<button type="submit" class="btn btn-primary" name="submit_file" onclick="set_action(\'filesend_upload\', this);" value="'.getLL('download_send_upload_submit').'">'.getLL('download_send_upload_submit').'</button>';
	$c .= '</div>';

	$c .= '<div class="row">';
	$c .= '<div class="col-md-8">';
	$c .= '<div class="form-group">';
	$c .= '<label>'.getLL('download_send_text').':</label>';
	//Show select with placeholders for rota
	$c .= '<select name="placeholder" class="input-sm form-control" id="placeholder" onchange="richtexteditor_insert_text(\'emailtext\', \'[[\'+this.value+\']]\');">';
	$c .= '<option value="">'.getLL('download_send_insert_placeholder').'</option>';
	$c .= '<option value="" disabled="disabled">-------------------------</option>';
	foreach(array('_SALUTATION', '_SALUTATION_FORMAL', 'FIRSTNAME', 'LASTNAME', 'TEAM_NAME', 'LEADER_TEAM_NAME', 'PERSONAL_SCHEDULE', 'TEAM_EVENTS', 'TEAM_EVENTS_SCHEDULE', 'LEADER_TEAM_EVENTS', 'LEADER_TEAM_EVENTS_SCHEDULE', 'ALL_EVENTS', 'ALL_EVENTS_SCHEDULE', 'CONSENSUS_LINK', 'ICAL_LINK') as $key) {
		$c .= '<option value="'.$key.'">[['.$key.']]: '.getLL('rota_placeholder_'.$key).'</option>';
	}
	$c .= '</select>';

	//Show select for presets for the mail text
	$c .= '<div class="input-group input-group-sm">';
	$c .= '<select name="preset" class="input-sm form-control" id="preset" name="preset" onchange="richtexteditor_insert_html(\'emailtext\', this.value);">';
	$c .= '<option value="">'.getLL('download_send_insert_preset').'</option>';
	$c .= '<option value="" disabled="disabled">-------------------------</option>';
	$presets = array_merge((array)ko_get_userpref('-1', '', 'rota_emailtext_presets', 'ORDER by `key` ASC'), (array)ko_get_userpref($_SESSION['ses_userid'], '', 'rota_emailtext_presets', 'ORDER by `key` ASC'));
	foreach($presets as $preset) {
		$prefix = $preset['user_id'] == -1 ? getLL('itemlist_global_short').' ' : '';
		$c .= '<option id="preset_'.$preset['id'].'" value="'.ko_js_escape(utf8_encode($preset['value'])).'">'.$prefix.$preset['key'].'</option>';
	}
	$c .= '</select>';
	//Icon to delete presets
	$c .= '<div class="input-group-btn"><button name="del_preset" class="btn btn-danger" type="button" onclick="sendReq(\'../rota/inc/ajax.php\', \'action,id,sesid\', \'delpreset,\'+document.getElementById(\'preset\').options[document.getElementById(\'preset\').selectedIndex].id+\','.session_id().'\', do_element); return false;"><i class="fa fa-trash"></i></button></div>';
	$c .= '</div>';

	$c .= '<textarea name="text" id="emailtext" class="richtexteditor" style="height: 170px;">'.$get['text'].'</textarea>';
	$c .= '</div>';
	$c .= '</div>';
	$c .= '</div>';

	//Save as new text template
	$c .= '<div class="row">';
	$c .= '<div class="col-md-6 col-md-offset-2 col-lg-4 col-lg-offset-4">';
	$c .= '<div class="input-group input-group-sm">';
	$c .= '<input type="text" class="input-sm form-control" name="save_preset" id="save_preset_name" placeholder="'.getLL('download_send_new_preset').'">';
	if($access['rota']['MAX'] > 4) $c .= '<span class="input-group-addon"><input type="checkbox" name="chk_global" id="preset_global" value="1">&nbsp;'.getLL('itemlist_global').'</span>';
	$c .= '<div class="input-group-btn"><button type="button" class="btn btn-success" name="btn_save_template" value="'.getLL('save').'" id="btn_save_template">'.getLL('save').'</button></div>';
	$c .= '</div>';
	$c .= '</div>';
	$c .= '</div>';

	$c .= '<br>';

	$c .= '<h4>'.getLL('rota_filesend_recipient_preview').':</h4>';
	$c .= '<div class="row"><div class="col-md-8" id="filesend_preview"></div></div>';

	// add crm contact form
	$crm_group = ko_get_crm_contact_form_group(array('leute_ids'), array('type' => 'email'));
	if ($crm_group) {
		$smarty->assign("tpl_groups", $crm_group);
		$smarty->assign("tpl_hide_cancel", TRUE);
		$smarty->assign("tpl_special_submit", "&nbsp;");
		$c .= $smarty->fetch("ko_formular.tpl");
	}

	$c .= '<div class="row">';
	$c .= '<div class="col-md-8">';
	$c .= '<div class="btn-field"><button type="submit" class="btn btn-primary" name="submit_send" value="'.getLL('download_send_send').'" onclick="set_action(\'filesend\', this);">'.getLL('download_send_send').'</button></div>';
	$c .= '</div>';
	$c .= '</div>';
	$c .= '</div>';


	$c .= '</form></div>';

	print $c;
}//ko_rota_send_file_form()


/**
 * @param string $mode either SEND or PREVIEW
 * @return bool
 */
function ko_rota_filesend_parse_post($mode='SEND', &$text, &$subject, &$recipients, &$eventid, &$restrict_to_teams, &$from, &$send_files) {
	global $access, $BASE_PATH;

	if($access['rota']['MAX'] < 4) return FALSE;

	//Get logged in person and he's email addresses
	$p = ko_get_logged_in_person();
	if(!ko_get_leute_email($p, $emails)) $emails = array(ko_get_setting('info_email'));

	//Check for valid sender address
	$from_email = format_userinput($_POST['sender'], 'email');
	if(!check_email($from_email) || !in_array($from_email, $emails)) return FALSE;

	//Build sender header with name and email address
	$from_name = $p['vorname'] || $p['nachname'] ? $p['vorname'].' '.$p['nachname'] : $p['firm'];
	$from = array($from_email => $from_name);


	//Get file and filetype from submitted form
	$filetype = $_POST['filetype'];
	$send_files = array();
	foreach($_POST['files'] as $k => $file) {
		$file = realpath($BASE_PATH.$file);
		if(substr($file, 0, strlen($BASE_PATH)) != $BASE_PATH) return FALSE;
		$send_files[$file] = basename($file);
	}

	$restrict_consensus_link = ko_get_setting('consensus_restrict_link') == 1 ? TRUE : FALSE;
	$restrict_to_teams = NULL;
	//Get recipients according to recipients mode
	if(in_array($_POST['recipients'], array('schedulled', 'selectedschedulled', 'manualschedulled'))) {
		if(substr($_POST['filetype'], 0, 5) == 'event') {
			list($m, $eventid) = explode(':', $filetype);
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
			if ($restrict_consensus_link) $restrict_to_teams = $team_ids;
		} else if($_POST['recipients'] == 'manualschedulled') {
			$team_ids = array_filter(explode(',', $_POST['sel_teams_schedulled']), function($e){return trim($e)?TRUE:FALSE;});
			if ($restrict_consensus_link) $restrict_to_teams = $team_ids;
		} else {
			$team_ids = '';
		}
		$recipients = ko_rota_get_recipients_by_event($eventid, $team_ids, 4);
	}
	else if($_POST['recipients'] == 'single') {
		$recipients = array();
		foreach(explode(',', $_POST['single_id']) as $sid) {
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
				if ($restrict_consensus_link) $restrict_to_teams = $teams;
				break;
			case 'selectedleaders':
				$teams = $_SESSION['rota_teams'];
				if ($restrict_consensus_link) $restrict_to_teams = $teams;
				$roleid = ko_get_setting('rota_leaderrole');
				break;
			case 'allrotamembers':
				$teams = array_keys(db_select_data('ko_rota_teams', 'WHERE 1'));
				break;
			case 'allrotamembersconsensus':
				$teams = array_keys(db_select_data('ko_rota_teams', 'WHERE `allow_consensus` = "1"'));
				break;
			case 'allrotaleaders':
				$teams = array_keys(db_select_data('ko_rota_teams', 'WHERE 1'));
				$roleid = ko_get_setting('rota_leaderrole');
				break;
			case 'manualmembers':
				$teams = $team_ids = array_filter(explode(',', $_POST['sel_teams_members']), function($e){return trim($e)?TRUE:FALSE;});
				if ($restrict_consensus_link) $restrict_to_teams = $teams;
				break;
			case 'manualleaders':
				$teams = $teams = $team_ids = array_filter(explode(',', $_POST['sel_teams_leaders']), function($e){return trim($e)?TRUE:FALSE;});
				if ($restrict_consensus_link) $restrict_to_teams = $teams;
				$roleid = ko_get_setting('rota_leaderrole');
				break;
		}
		$recipients = array();
		foreach($teams as $teamID) {
			if($access['rota']['ALL'] < 4 && $access['rota'][$teamID] < 4) return FALSE;
			$rec = ko_rota_get_team_members($teamID, TRUE, $roleid);
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
				if ($mode != 'PREVIEW') ko_save_userpref($_SESSION['ses_userid'], 'rota_recipients_group', $gid);
				//Get all group members
				$group_members = db_select_data('ko_leute', "WHERE `deleted` = '0' AND `hidden` = '0' AND `groups` LIKE '%g$gid%'");
				foreach($group_members as $member) {
					$recipients[] = $member;
				}
			}
		}
	} else {
		if ($mode != 'PREVIEW') ko_save_userpref($_SESSION['ses_userid'], 'rota_recipients_group', '');
	}

	// If set, check all teams that should be visible in consensus for access of user
	if($restrict_consensus_link) {
		if($restrict_to_teams === NULL) {
			$all_teams = db_select_data('ko_rota_teams', 'WHERE 1=1');
			$restrict_to_teams = array_keys($all_teams);
		}
		if($access['rota']['ALL'] < 3) {
			foreach($restrict_to_teams as $k => $t) {
				if($access['rota'][$t] < 3) unset($restrict_to_teams[$k]);
			}
		}
	}


	//Remove double entries
	$rec_ids = array();
	foreach($recipients as $k => $v) {
		if(in_array($v['id'], $rec_ids)) unset($recipients[$k]);
		else {
			if ($found = ko_get_leute_email($v, $emails)) $recipients[$k]['_has_mail'] = TRUE;
			else $recipients[$k]['_has_mail'] = FALSE;
		}
		$rec_ids[] = $v['id'];
	}

	$text = $_POST['text'];
	$subject = $_POST['subject'];

}

function ko_rota_filesend_send_mail($mode='SEND', $text, $subject, $recipient, $send_files, $eventid, $from, $restrict_to_teams) {
	//Send file to recipients
	$subject = strtr($subject, array("\n" => '', "\r" => ''));

	$found = ko_get_leute_email($recipient, $emails);
	if($found) {
		//Email text
		$emailtext = strtr($text, ko_rota_get_placeholders($recipient, $eventid, $restrict_to_teams));

		if ($mode == 'SEND') ko_send_html_mail($from, $emails[0], $subject, ko_emailtext($emailtext), $send_files);
		return array('recipient' => $recipient, 'email' => $emails[0], 'emailSubject' => $subject, 'emailText' => ko_emailtext($emailtext));
		//$email_recipients[] = $emails[0].' ('.$recipient['id'].')';
	} else {
		return FALSE;
		//$noemail_recipients[] = $recipient['vorname'].' '.$recipient['nachname'].' ('.$recipient['id'].')';
	}

}



function ko_rota_ical_links() {
	global $ICAL_URL, $BASE_URL, $access;

	if(!defined('KOOL_ENCRYPTION_KEY') || trim(KOOL_ENCRYPTION_KEY) == '') {
		print 'ERROR: '.getLL('error_rota_1');
		return FALSE;
	}

	ko_get_login($_SESSION['ses_userid'], $login);
	$base_link = $BASE_URL.'rotaical/index.php?user='.md5($_SESSION['ses_userid'].$login['password'].KOOL_ENCRYPTION_KEY);

	$content = '';
	$content .= '<div class="ical-links">';
	$content .= '<h1>'.getLL('rota_ical_links_title').'</h1>';
	$content .= '<p>'.getLL('rota_ical_links_description').'</p>';

	/*
	$content .= '<h4>'.getLL('daten_ical_links_title_all').'</h4>';
	$content .= ko_get_ical_link($base_link, getLL('all')).'<br />';

	$link = $base_link.'&teams='.implode(',', $_SESSION['show_tg']);
	//Add current KOTA filter if set
	if(sizeof($_SESSION['kota_filter']['ko_rota_teams']) > 0) {
		foreach($_SESSION['kota_filter']['ko_rota_teams'] as $k => $v) {
			if(!$v) continue;
			$link .= '&kota_'.$k.'='.urlencode($v);
		}
	}
	$content .= ko_get_ical_link($link, getLL('daten_ical_links_current')).'<br />';

	$content .= '<h4>'.getLL('daten_ical_links_title_presets').'</h4>';
	$itemset = array_merge((array)ko_get_userpref('-1', '', 'rota_itemset', 'ORDER BY `key` ASC'), (array)ko_get_userpref($_SESSION['ses_userid'], '', 'rota_itemset', 'ORDER BY `key` ASC'));
	foreach($itemset as $i) {
		if(!$i['key'] || !$i['value']) continue;
		$label = ($i['user_id'] == '-1' ? getLL('itemlist_global_short').$i['key'] : $i['key']);
		$content .= ko_get_ical_link($base_link.'&teams=p'.$i['id'], $label).'<br />';
	}

	$content .= '<h4>'.getLL('daten_ical_links_title_single').'</h4>';
	$teams = db_select_data('ko_rota_teams', "WHERE 1=1", '*', 'ORDER BY `sort` ASC');
	foreach($teams as $id => $t) {
		if($access['rota']['ALL'] < 1 && $access['daten'][$id] < 1) continue;
		if(!$t['id']) continue;
		$content .= ko_get_ical_link($base_link.'&teams='.$t['id'], $t['name']).'<br />';
	}

	$content .= '</div>';
	*/


	$teams = array_keys(db_select_data('ko_rota_teams', 'WHERE 1'));
	$ppl = array();
	foreach($teams as $teamID) {
		if($access['rota']['ALL'] < 4 && $access['rota'][$teamID] < 4) return FALSE;
		$rec = ko_rota_get_team_members($teamID, TRUE);
		$ppl = array_merge($ppl, $rec['people']);
	}

	$orderfield = ko_get_userpref($_SESSION['ses_userid'], 'rota_orderby');
	if(!$orderfield) $orderfield = 'nachname';

	if (sizeof($ppl) > 0) {
		$max_length_lastname = array_reduce($ppl, function ($carry, $item) {
			return max(strlen($item['nachname']), $carry);
		});
		$max_length_firstname = array_reduce($ppl, function ($carry, $item) {
			return max(strlen($item['vorname']), $carry);
		});
		$ppl_ = array();
		foreach ($ppl as $p) {
			//Don't use addresses with no name
			if(trim($p['vorname']) == '' && trim($p['nachname']) == '') continue;

			if($orderfield == 'nachname') {
				$ppl_[str_fill($p['nachname'], $max_length_lastname, ' ', 'append').str_fill($p['vorname'], $max_length_firstname, ' ', 'append').$p['id']] = $p;
			} else {
				$ppl_[str_fill($p['vorname'], $max_length_firstname, ' ', 'append').str_fill($p['nachname'], $max_length_lastname, ' ', 'append').$p['id']] = $p;
			}
		}
		$ppl = $ppl_;
		ksort($ppl);

		$content .= '<h4>'.getLL('rota_ical_links_title_personal').'</h4>';
		$content .= '<div class="panel-group" id="rota-individual-ical-links">';
		$lastStart = '';
		$first = TRUE;

		foreach($ppl as $p) {
			if (strtoupper(substr($p[$orderfield], 0, 1)) != $lastStart) {
				if (!$first) {
						$content .=
'</div>
	</div>
</div>';
				}
				$lastStart = strtoupper(substr($p[$orderfield], 0, 1));
				$content .=
'<div class="panel panel-default">
	<div class="panel-heading" role="tab" id="heading'.$lastStart.'">
		<h4 class="panel-title">
			<a style="display:block;" data-toggle="collapse" data-parent="#rota-individual-ical-links" href="#collapse'.$lastStart.'">
				'.$lastStart.'
			</a>
		</h4>
	</div>
	<div id="collapse'.$lastStart.'" class="panel-collapse collapse">
		<div class="panel-body">
			';
				$first = FALSE;
			}
			$link = $BASE_URL . 'rotaical/index.php?person='.$p['id'].'x'.strtolower(substr(md5($p['id'] . KOOL_ENCRYPTION_KEY . 'rotaIcal' . KOOL_ENCRYPTION_KEY . 'rotaIcal' . KOOL_ENCRYPTION_KEY . $p['id']), 0, 10));
			if($orderfield == 'nachname') {
				$label = "{$p['nachname']} {$p['vorname']}";
			} else {
				$label = "{$p['vorname']} {$p['nachname']}";
			}
			$adr = trim($p['adresse'].' '.$p['plz'].' '.$p['ort']);
			$bd = ($p['geburtsdatum'] && $p['geburtsdatum'] != '0000-00-00') ? sql2datum($p['geburtsdatum']) : '';
			if ($adr && $bd) $title = $adr.", ".$bd;
			else $title = $adr . $bd;
			$content .= '<div class="col-md-6">'.ko_get_ical_link($link, $label, $title).'</div>';
		}
		$content .=
'</div>
	</div>
</div>';

		$content .= '</div>';
		$content .= '</div>';
	}

	print $content;
}//ko_rota_ical_links()
