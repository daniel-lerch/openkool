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

require_once($BASE_PATH."inc/class.kOOL_listview.php");
require_once($BASE_PATH."consensus/consensus.inc");

/**
 * Show scheduling for events
 */
function ko_rota_schedule() {
	global $access, $smarty, $ko_path;

	if(sizeof($_SESSION['rota_teams']) == 0 && sizeof($_SESSION['rota_teams_readonly']) == 0) return FALSE;
	if($access['rota']['MAX'] < 1) return FALSE;

	if($amtstag_start = format_userinput($_GET['amtstag'],'uint')) {
		$_SESSION['rota_timestart'] = date("Y-m-d", substr($amtstag_start,0,10));
	}

	$smarty->assign('ko_path', $ko_path);
	$smarty->assign('sesid', session_id());
	$smarty->assign('title', getLL('rota_title_schedule').' '.ko_rota_timespan_title($_SESSION['rota_timestart'], $_SESSION['rota_timespan']));
	$smarty->assign('help', ko_get_help('rota', 'schedule'));

	$order = 'ORDER BY '.$_SESSION['sort_rota_teams'].' '.$_SESSION['sort_rota_teams_order'];
	$rota_teams = db_select_data('ko_rota_teams', "WHERE `id` IN ('".implode("','", $_SESSION['rota_teams'])."')", '*', $order);
	$show_days = FALSE;
	if($_SESSION['rota_timespan'] != '1d') {
		foreach($rota_teams as $team) {
			if($team['rotatype'] == 'day') $show_days = TRUE;
		}
	}
	$smarty->assign('show_days', $show_days);

	[$start, $stop] = rota_timespan_startstop($_SESSION['rota_timestart'], $_SESSION['rota_timespan']);
	$start = new \DateTime($start);
	$stop = new \DateTime($stop);

	$weeks = [];
	while ($start < $stop) {
		$week_key = $start->format("YW");
		$weeks[$week_key] = [];
		$start->add(new \DateInterval('P1W'));
	}

	if($show_days) {
		$all_teams = db_select_data('ko_rota_teams', "WHERE rotatype='day'");
		$days = ko_rota_get_days($rota_teams, '', '',TRUE);
		foreach($days as $key => $day) {
			if($days[$key]['month'] == 12 && $days[$key]['num'] == 1) {
				$week_key = ($days[$key]['year']+1) . $days[$key]['num'];
			} else {
				$week_key = $days[$key]['year'] . $days[$key]['num'];
			}
			if($week_key < date("YW",strtotime($_SESSION['rota_timestart']))) continue;
			if(isset($weeks[$week_key]['days'])) {
				$weeks[$week_key]['days'][] = $days[$key];
				$weeks[$week_key]['rotastatus'] = 1;
			} else {
				$weeks[$week_key]['days'][1] = $days[$key];
				$weeks[$week_key]['teams'] = [];
			}

			$weeks[$week_key]['_stats']['done']+= count($day['schedule']);
			$weeks[$week_key]['teams'] = array_unique(array_merge($weeks[$week_key]['teams'], $day['teams']));

			if ($days[$key]['rotastatus'] == 2) {
				$weeks[$week_key]['rotastatus'] = 2;
			}

			$week_start = new DateTime();
			$week_start->setISODate($days[$key]['year'], $days[$key]['num']);
			$week_stop = clone $week_start;
			$week_stop->modify("+6 days");
			$weeks[$week_key]['label'] = $week_start->format('d.m.Y') . " - " . $week_stop->format('d.m.Y');
			$weeks[$week_key]['id'] = $days[$key]['year'] . "-" . $days[$key]['num'];
		}

		foreach($weeks AS $week_key => $week) {
			$active_days_in_week = 0;

			foreach($week['teams'] aS $team) {
				$active_days_in_week+= count(explode(",",$all_teams[$team]['days_range']));
			}

			$weeks[$week_key]['_stats']['total'] = $active_days_in_week;

			$weeks[$week_key]['schedulling_code'] = ko_rota_get_schedulling_code_days($week);
			$weeks[$week_key]['exports'][0] = [
				'link' => "javascript:sendReq('../rota/inc/ajax.php', 'action,id,sesid', 'eventmylist,".$week['id'].",". session_id() ."', do_element);",
				'html' => '<span class="fa-stack"><i class="fa fa-list fa-stack-1x"></i><i class="fa fa-chevron-right fa-stack-sm fa-stack-bottom-left text-success"></i><i class="fa fa-plus-circle fa-stack-sm fa-stack-top-right text-success"></i></span>',
				'title' => getLL('rota_event_mylist_week'),
			];
		}
	}

	$events = ko_rota_get_events('', '', FALSE, TRUE);

	if(!empty($_SESSION['rota_teams_readonly'])) {
		$read_only_events = ko_rota_get_events($_SESSION['rota_teams_readonly'], '', TRUE, TRUE);
		foreach($read_only_events AS $read_only_event) {
			if(!in_array($read_only_event['id'],array_column($events, "id"))) {
				array_push($events, $read_only_event);
			}
		}
	}

	ko_rota_get_pagestats($events, $rota_teams);
	ko_rota_prepare_events($events);

	foreach($events AS $event) {
		$week_key = date("YW", strtotime($event['startdatum']));
		$weeks[$week_key]['events'][] = $event;
	}

	ko_rota_get_eventfields();

	ksort($weeks);
	$smarty->assign("weeks", $weeks);
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

	$smarty->assign("type", "schedule");
	$smarty->display('ko_rota_schedule.tpl');
}//ko_rota_schedule()


function ko_rota_prepare_events(&$events, $type = "schedule") {
	global $smarty, $access, $BASE_PATH;
	include_once($BASE_PATH . "/daten/inc/daten.inc");

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
		'link' => "/daten/index.php?action=export_single_pdf_settings&module=rota&id=###event_id###",
		'html' => '<i class="fa fa-file-pdf-o"></i>',
		'title' => getLL('rota_event_export_single_event'),
	);


	$exports[] = array(
		'link' => "javascript:sendReq('../rota/inc/ajax.php', 'action,id,sesid', 'eventmylist,###event_id###,###session_id###', do_element);",
		'html' => '<span class="fa-stack"><i class="fa fa-list fa-stack-1x"></i><i class="fa fa-chevron-right fa-stack-sm fa-stack-bottom-left text-success"></i><i class="fa fa-plus-circle fa-stack-sm fa-stack-top-right text-success"></i></span>',
		'title' => getLL('rota_event_mylist'),
	);

	$exports[] = array(
		'link' => "/leute/index.php?action=set_rotafilter&event=###event_id###",
		'html' => '<i class="fa fa-user"></i>',
		'title' => getLL('rota_event_leutefilter'),
	);

	//Allow plugins to add new exports per event
	$plugins = hook_get_by_type('rota');
	foreach($plugins as $plugin) {
		if(function_exists('my_rota_event_export_'.$plugin)) {
			call_user_func_array('my_rota_event_export_'.$plugin, array(&$exports));
		}
	}

	$show_eventfields = explode(',', ko_get_userpref($_SESSION['ses_userid'], 'rota_eventfields'));
	$patterns = array('/###event_id###/', '/###session_id###/');
	foreach($events as $ei => $event) {
		if ($type == "schedule") {
			$events[$ei]['schedulling_code'] = ko_rota_get_schedulling_code($event);
		}
		//Process KOTA fields for event subtitles
		$kota_event = [];
		foreach($show_eventfields as $field) {
			$kota_event[$field] = $event[$field];
		}
		kota_process_data('ko_event', $kota_event, 'list', $kotaLog, $event['id'], FALSE, $event);
		$events[$ei]['_processed'] = $kota_event;

		if(is_numeric($events[$ei]['room'])) {
			$events[$ei]['room'] = ko_get_event_rooms($events[$ei]['room']);
		}

		if($type == "planning") {
			foreach($events[$ei]['_processed'] AS $key => $value) {
				$events[$ei]['_processed'][$key] = str_replace("'", "\'", $value);
			}
		}

		$replacements = array($event['id'], session_id());
		$eExports = $exports;
		foreach ($eExports as $k => $eExport) {
			$eExports[$k]['link'] = preg_replace($patterns, $replacements, $eExports[$k]['link']);
		}
		$events[$ei]['exports'] = $eExports;

		if($access['daten']['ALL'] > 1 || $access['daten'][$event['eventgruppen_id']] > 1) {
			$events[$ei]['can_edit'] = TRUE;
		} else {
			$events[$ei]['can_edit'] = FALSE;
		}
	}
}

/**
 * Assign label and data from eventfields to smarty
 *
 */
function ko_rota_get_eventfields() {
	global $smarty;

	$show_eventfields = explode(',', ko_get_userpref($_SESSION['ses_userid'], 'rota_eventfields'));
	$smarty->assign('show_eventfields', $show_eventfields);
	$labels = [];

	foreach($show_eventfields as $field) {
		$labels[$field] = getLL('kota_ko_event_'.$field);
	}

	$smarty->assign('eventfield_labels', $labels);
}


/**
 * Assign the header width consensus-links, date-select and exports to smarty
 *
 * @param $events array of events
 * @param $rota_teams array of teams
 */
function ko_rota_get_pagestats($events, $rota_teams) {
	global $smarty, $ROTA_TIMESPANS, $access;

	$current_timespan = '';
	$ts_labels = [];
	foreach($ROTA_TIMESPANS as $k => $v) {
		if($v == $_SESSION['rota_timespan']) $current_timespan = $k;
		$ts_labels[$k] = getLL('rota_timespan_'.$v);
	}

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

	//Set some stats for navigation etc
	$smarty->assign('stats', array('start' => 1,
		'end' => sizeof($events),
		'oftotal' => getLL('list_oftotal'),
		'total' => sizeof($events),
		'prevts' => $ROTA_TIMESPANS[max(0, $current_timespan-1)],
		'nextts' => $ROTA_TIMESPANS[min(sizeof($ROTA_TIMESPANS), $current_timespan+1)],
	));
	$smarty->assign('timespans', array('values' => $ROTA_TIMESPANS, 'output' => $ts_labels, 'selected' => $_SESSION['rota_timespan']));

	$consensus_links = ko_rota_get_consensus_links();
	$smarty->assign('consensus_links', $consensus_links);

	$smarty->assign('label_statusallevents', getLL('rota_change_status_for_all_events'));
	$smarty->assign('label_week', getLL('rota_calweek'));
	$smarty->assign('label_export_excel_portrait', getLL('rota_export_excel_portrait'));
	$smarty->assign('label_export_excel_landscape', getLL('rota_export_excel_landscape'));

	if($access['rota']['MAX'] > 4 && $_SESSION['show'] != "planning") $smarty->assign('access_status', TRUE);
	if($access['rota']['MAX'] > 3) $smarty->assign('access_send', TRUE);
	if($access['rota']['MAX'] > 1) $smarty->assign('access_export', TRUE);

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

}


/**
 * Controller to fetch events/teams-data and display smarty template
 *
 */
function ko_rota_planning_list() {
	global $access, $smarty, $ko_path, $BASE_PATH;

	$smarty->assign('ko_path', $ko_path);
	$smarty->assign('sesid', session_id());

	$z_where = 'WHERE 1=1';

	$team_ids = array();
	if($access['rota']['ALL'] < 1) {
		foreach($access['rota'] as $id => $level) {
			$id = intval($id);
			if(!$id) continue;
			if($level > 0) $team_ids[] = $id;
		}
	}
	if(sizeof($team_ids) > 0) {
		$z_where .= " AND `id` IN (".implode(',', $team_ids).") ";
	}

	if($_GET['add_person'] && is_numeric($_GET['team_id'])) {
		if($access['rota'][$_GET['team_id']] >= 1) {
			$day_teams = ko_rota_get_teams_day();
			$event_id = format_userinput($_GET['event_id'], "text");
			if(is_numeric($_GET['event_id']) && !empty($day_teams[$_GET['team_id']])) {
				// schedule as amtstag
				ko_get_event_by_id($event_id, $event);
				$event_id = $event['startdatum'];
			}

			$where = "WHERE team_id = " . $_GET['team_id'] ." AND event_id = '" . $event_id . "'";
			$schedule = db_select_data("ko_rota_schedulling", $where, "schedule", "", "LIMIT 1", TRUE, TRUE);

			if(isset($schedule['schedule'])) {
				$new_schedule = explode(",", $schedule['schedule']);
				$new_schedule[] = format_userinput($_GET['add_person'], "text");

				foreach($new_schedule as $k => $v) {
					if(!$v) unset($new_schedule[$k]);
				}

				$data = [
					'schedule' => implode(",", array_unique($new_schedule)),
				];
				db_update_data("ko_rota_schedulling", $where, $data);
			} else {
				$data = [
					"team_id" => $_GET['team_id'],
					"event_id" => $_GET['event_id'],
					'schedule' => format_userinput($_GET['add_person'], "text"),
				];
				db_insert_data("ko_rota_schedulling", $data);
			}
		}
	}

	$kota_where = kota_apply_filter('ko_rota_teams');
	if($kota_where != '') $z_where .= " AND ($kota_where) ";

	if($_SESSION['sort_rota_teams']) {
		$order = 'ORDER BY '.$_SESSION['sort_rota_teams'].' '.$_SESSION['sort_rota_teams_order'];
	} else {
		$order = 'ORDER BY '.(ko_get_setting('rota_manual_ordering') ? 'sort' : 'name').' ASC';
	}

	$teams = db_select_data('ko_rota_teams', $z_where, '*', $order);

	foreach($teams AS $id => $team) {
		$members = ko_rota_get_team_members($id);
		$reordered_groups = [];
		foreach($members['groups'] AS $group) {
			$reordered_groups["g" . $group['id']] = $group;
		}
		$teams[$id]['groups'] = $reordered_groups;
		$teams[$id]['people'] = $members['people'];

		if (!in_array($id, $_SESSION['rota_teams'])) {
			$teams[$id]['hide'] = TRUE;
		}
		$teams[$id]['edit_class'] = ($access['rota']['ALL'] > 2 || $access['rota'][$id] > 2) ? 'rw' : 'ro';
	}

	$all_teams = ko_rota_get_all_teams();
	$events = ko_rota_get_events(array_keys($all_teams), '', TRUE, TRUE);
	ko_rota_get_pagestats($events, $teams);
	ko_rota_prepare_events($events, 'planning');
	$smarty->assign("events", $events);
	ko_rota_get_eventfields();
	include_once($BASE_PATH . "/daten/inc/daten.inc");

	$columns = "concat(team_id,'_',event_id) AS id, schedule";
	$db_schedules = db_select_data("ko_rota_schedulling", "WHERE schedule != ''", $columns);
	foreach($db_schedules AS $key =>$db_schedule) {
		$db_schedules[$key]['scheduled_items'] = explode(",", $db_schedule['schedule']);
	}

	foreach ($events AS $event) {
		$absences = ko_daten_get_absence_by_date($event['startdatum'], $event['enddatum']);

		foreach ($teams AS $teamId => $team) {
			if(!in_array($teamId, $event['teams'])) {
				$teams[$teamId]['events'][$event['id']]['status'] = "disabled";
			}

			$teams[$teamId]['events'][$event['id']]['sum_available'] = 0;
			$teams[$teamId]['events'][$event['id']]['sum_scheduled'] = 0;
			$teams[$teamId]['sum_scheduled'] = 0;

			foreach ($teams[$teamId]['groups'] AS $groupId => $group) {
				if (!isset($teams[$teamId]['groups'][$groupId]['consensus']['sum'])) {
					$teams[$teamId]['groups'][$groupId]['consensus']['sum']['scheduled'] = 0;
				}
				if (ko_rota_is_scheduling_disabled($event['id'], $teamId)) {
					$teams[$teamId]['groups'][$groupId]['consensus'][$event['id']]['status'] = "disabled";
				} else {
					$teams[$teamId]['groups'][$groupId]['consensus'][$event['id']]['status'] = "active";
				}
				$isScheduled = ko_rota_group_is_scheduled($teamId, $event['id'], $groupId);
				if ($isScheduled === TRUE) {
					$teams[$teamId]['groups'][$groupId]['consensus'][$event['id']]['answer'] = 0;
					$teams[$teamId]['groups'][$groupId]['consensus'][$event['id']]['scheduled'] = TRUE;
					$teams[$teamId]['groups'][$groupId]['consensus']['sum']['scheduled']++;
					$teams[$teamId]['events'][$event['id']]['sum_scheduled']++;
				}
				$teams[$teamId]['events'][$event['id']]['sum_available']++;
			}

			foreach ($teams[$teamId]['people'] AS $personId => $person) {
				if (!isset($teams[$teamId]['people'][$personId]['consensus']['sum'])) {
					$teams[$teamId]['people'][$personId]['consensus']['sum']['scheduled'] = 0;
					$teams[$teamId]['people'][$personId]['consensus']['sum']['noanswer'] = 0;
					$teams[$teamId]['people'][$personId]['consensus']['sum']['no'] = 0;
					$teams[$teamId]['people'][$personId]['consensus']['sum']['perhaps'] = 0;
					$teams[$teamId]['people'][$personId]['consensus']['sum']['yes'] = 0;
				}

				$answerPerson = ko_consensus_get_answers('person', $event['id'], $teamId, $personId);

				if($all_teams[$teamId]['rotatype'] == "day") {
					$isScheduled = ko_rota_person_is_scheduled($teamId, $event['startdatum'], $personId);
				} else {
					$isScheduled = ko_rota_person_is_scheduled($teamId, $event['id'], $personId);
				}
				$isAbsent = array_search($personId, array_column($absences, "leute_id"));

				if ($isAbsent !== FALSE) {
					if ($absences[$isAbsent]['from_date'] != $absences[$isAbsent]['to_date']) {
						$absenceSpan = date("d.m.Y", strtotime($absences[$isAbsent]['from_date'])) . "-" . date("d.m.Y", strtotime($absences[$isAbsent]['to_date']));
					} else {
						$absenceSpan = date("d.m.Y", strtotime($absences[$isAbsent]['from_date']));
					}

					$absenceDescription = "<br><strong>" . getLL("kota_ko_event_absence_type_" . $absences[$isAbsent]['type']) . " (" . $absenceSpan . ")</strong>";
					if (!empty($absences[$isAbsent]['description'])) {
						$absenceDescription .= "<br>" . $absences[$isAbsent]['description'];
					}
					$teams[$teamId]['people'][$personId]['consensus'][$event['id']]['absence'] = $absenceDescription;
				}

				if (ko_rota_is_scheduling_disabled($event['id'], $teamId)) {
					$teams[$teamId]['people'][$personId]['consensus'][$event['id']]['status'] = "disabled";
				} else {
					$teams[$teamId]['people'][$personId]['consensus'][$event['id']]['status'] = "active";
				}

				if ($isScheduled === TRUE) {
					$teams[$teamId]['people'][$personId]['consensus'][$event['id']]['scheduled'] = TRUE;
					$teams[$teamId]['people'][$personId]['consensus']['sum']['scheduled']++;
					$teams[$teamId]['events'][$event['id']]['sum_scheduled']++;
				}
				$teams[$teamId]['events'][$event['id']]['sum_available']++;

				$teams[$teamId]['people'][$personId]['consensus'][$event['id']]['answer'] .= $answerPerson;
				switch ($answerPerson) {
					case 0:
					case '':
						$teams[$teamId]['people'][$personId]['consensus']['sum']['noanwser']++;
						break;
					case 1:
						$teams[$teamId]['people'][$personId]['consensus']['sum']['no']++;
						break;
					case 2:
						$teams[$teamId]['people'][$personId]['consensus']['sum']['perhaps']++;
						break;
					case 3:
						$teams[$teamId]['people'][$personId]['consensus']['sum']['yes']++;
						break;
				}

			}

			foreach($db_schedules[$teamId."_".$event['id']]['scheduled_items'] AS $free_text) {
				if (!is_numeric($free_text) && substr($free_text,0,1) != "g") {
					$free_text_key = md5($free_text);
					$teams[$teamId]['free_text'][$free_text_key]['name'] = $free_text;
					$teams[$teamId]['free_text'][$free_text_key]['consensus'][$event['id']]['answer'] = 0;
					$teams[$teamId]['free_text'][$free_text_key]['consensus']['sum']['scheduled']++;
					$teams[$teamId]['free_text'][$free_text_key]['consensus'][$event['id']]['scheduled'] = TRUE;
					$teams[$teamId]['events'][$event['id']]['sum_scheduled']++;
				}
			}
			foreach($db_schedules[$teamId."_".$event['startdatum']]['scheduled_items'] AS $free_text) {
				if (!is_numeric($free_text) && substr($free_text,0,1) != "g") {
					$free_text_key = md5($free_text);
					$teams[$teamId]['free_text'][$free_text_key]['name'] = $free_text;
					$teams[$teamId]['free_text'][$free_text_key]['consensus'][$event['id']]['answer'] = 0;
					$teams[$teamId]['free_text'][$free_text_key]['consensus']['sum']['scheduled']++;
					$teams[$teamId]['free_text'][$free_text_key]['consensus'][$event['id']]['scheduled'] = TRUE;
					$teams[$teamId]['events'][$event['id']]['sum_scheduled']++;
				}
			}
		}
	}

	foreach($teams AS $team_id => $team) {
		$teams[$team_id]['sum_events'] = 0;

		foreach($team['events'] AS $event_id => $event) {
			if (!in_array($team_id, $events[array_search($event_id,array_column($events,'id'))]['teams'])) {
				$teams[$team_id]['events'][$event_id]['status'] = "inactive";
			} elseif (ko_rota_is_scheduling_disabled($event_id, $team_id)) {
				$teams[$team_id]['events'][$event_id]['status'] = "disabled";
			} else {
				$teams[$team_id]['events'][$event_id]['status'] = "active";
				$teams[$team_id]['sum_events']++;
			}

			if($event['sum_scheduled'] > 0) {
				$teams[$team_id]['sum_scheduled']++;
			}
		}
	}

	$smarty->assign('title', getLL('rota_planning_title').' '.ko_rota_timespan_title($_SESSION['rota_timestart'], $_SESSION['rota_timespan']));
	$smarty->assign("type", "planning");
	$smarty->assign("teams", $teams);
	$smarty->assign('access', $access);
	$smarty->display('ko_rota_planning.tpl');
}

/**
 * Get consensus links of persons. Filtered by allowed teams to view.
 *
 * @return mixed Array with consensus links
 */
function ko_rota_get_consensus_links() {
	global $BASE_URL, $access;
	$consensus_members = [];
	$where = "WHERE allow_consensus = 1";
	$consensus_teams = db_select_data("ko_rota_teams", $where);
	foreach($consensus_teams AS $consensus_team) {
		if ($access['rota']['ALL'] < 4 && $access['rota'][$consensus_team['id']] < 4) continue;
		$members = ko_rota_get_team_members($consensus_team['id'],true);
		$consensus_members+= $members["people"];
	}

	array_multisort( array_column($consensus_members, "vorname"), SORT_ASC, $consensus_members );

	$consensus_links = [];
	foreach($consensus_members AS $p) {
		$consensus_link = $BASE_URL;
		if(substr($BASE_URL, -1) != '/') $consensus_link .= '/';
		$consensus_link.= 'consensus/?x=' . $p['id'] . 'x' . str_replace('-', '', $_SESSION['rota_timestart']) . 'x' . $_SESSION['rota_timespan'] . 'x' . substr(md5($p['id'] . $_SESSION['rota_timestart'] . $_SESSION['rota_timespan'] . KOOL_ENCRYPTION_KEY), 0, 6);
		$consensus_links[$consensus_link] = $p['vorname'] . ' ' . $p['nachname'];
	}

	return $consensus_links;
}


/**
 * Show a list of all rota teams
 */
function ko_rota_list_teams() {
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

	$list->setWarning(kota_filter_get_warntext('ko_rota_teams'));

	$list->render($es);
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
	global $access, $MODULES, $KOTA, $ROTA_TIMESPANS;

	if($access['rota']['MAX'] < 1 || $_SESSION['ses_userid'] == ko_get_guest_id()) return FALSE;

	//build form
	$gc = 0;
	$rowcounter = 0;
	$frmgroup[$gc]['titel'] = getLL('settings_title_user');
	$frmgroup[$gc]['tab'] = True;

	$frmgroup[$gc]['row'][$rowcounter++]['inputs'][0] = array('desc' => getLL('rota_settings_default_view'),
		'type' => 'select',
		'name' => 'sel_rota_default_view',
		'values' => array('schedule', 'planning', 'list_teams'),
		'descs' => array(getLL('submenu_rota_schedule'), getLL('submenu_rota_planning'), getLL('submenu_rota_list_teams')),
		'value' => ko_html(ko_get_userpref($_SESSION['ses_userid'], 'default_view_rota'))
	);

	$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('rota_settings_delimiter'),
		'type' => 'text',
		'params' => 'size="10"',
		'name' => 'txt_delimiter',
		'value' => ko_html(ko_get_userpref($_SESSION['ses_userid'], 'rota_delimiter'))
	);
	if($access['rota']['MAX'] > 1) {
		$value = ko_get_userpref($_SESSION['ses_userid'], 'rota_markempty');
		$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('rota_settings_markempty'),
			'name' => 'markempty',
			'type' => 'switch',
			'label_0' => getLL('no'),
			'label_1' => getLL('yes'),
			'value' => $value == '' ? 0 : $value,
		);
	} else {
		$rowcounter++;
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
			'values' => array(1,2,3,9,4,5,6,7,8),
			'descs' => array(getLL('rota_settings_pdf_names_1'), getLL('rota_settings_pdf_names_2'), getLL('rota_settings_pdf_names_3'), getLL('rota_settings_pdf_names_9'), getLL('rota_settings_pdf_names_4'), getLL('rota_settings_pdf_names_5'), getLL('rota_settings_pdf_names_6'), getLL('rota_settings_pdf_names_7'), getLL('rota_settings_pdf_names_8')),
			'value' => ko_get_userpref($_SESSION['ses_userid'], 'rota_pdf_names'),
		);
	}

	if($access['rota']['MAX'] > 1) {
		$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('rota_settings_pdf_title'),
			'name' => 'pdf_title',
			'type' => 'select',
			'values' => array('eventgruppen_name', 'title', 'eventgruppen_shortname'),
			'descs' => array(getLL('rota_settings_pdf_title_eg'), getLL('rota_settings_pdf_title_title'), getLL('rota_settings_pdf_title_shortname')),
			'value' => ko_get_userpref($_SESSION['ses_userid'], 'rota_pdf_title'),
		);
		$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('rota_settings_pdf_fontsize'),
			'name' => 'pdf_fontsize',
			'type' => 'select',
			'values' => array(7,8,9,10,11,12,13,14,15,16,17,18,19,20),
			'descs' => array(7,8,9,10,11,12,13,14,15,16,17,18,19,20),
			'value' => ko_get_userpref($_SESSION['ses_userid'], 'rota_pdf_fontsize'),
		);
		$value = ko_get_userpref($_SESSION['ses_userid'], 'rota_pdf_use_colors');
		$frmgroup[$gc]['row'][$rowcounter++]['inputs'][0] = array('desc' => getLL('rota_settings_pdf_use_colors'),
			'name' => 'pdf_use_colors',
			'type' => 'switch',
			'label_0' => getLL('no'),
			'label_1' => getLL('yes'),
			'value' => $value == '' ? 0 : $value,
		);
	}

	$values = $descs = $avalues = $adescs = array();
	$exclude = array('eventgruppen_id', 'startdatum', 'enddatum', 'startzeit', 'endzeit', 'do_notify', 'rota', 'reservationen', 'registrations', 'eg_color', 'cdate', 'user_id');
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
	if($access['rota']['MAX'] > 2) {
		$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('rota_settings_show_participation'),
			'name' => 'sel_show_participation',
			'type' => 'select',
			'values' => array('no', 'past', 'all'),
			'descs' => array(getLL('rota_settings_show_participation_no'), getLL('rota_settings_show_participation_past'), getLL('rota_settings_show_participation_all')),
			'value' => ko_get_userpref($_SESSION['ses_userid'], 'rota_show_participation'),
		);
	} else {
		$rowcounter++;
	}



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
		$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('rota_settings_restrict_link'),
			'name' => 'consensus_restrict_link',
			'type' => 'switch',
			'value' => ko_get_setting('consensus_restrict_link'),
		);

		$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('rota_settings_consensus_display_participation'),
			'name' => 'consensus_display_participation',
			'type' => 'select',
			'values' => [0, 1, 2],
			'descs' => [
				getLL('rota_settings_consensus_display_participation_0'),
				getLL('rota_settings_consensus_display_participation_1'),
				getLL('rota_settings_consensus_display_participation_2'),
			],
			'value' => ko_get_setting('consensus_display_participation'),

		);

		$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array(
			'desc' => getLL('rota_settings_consensus_ongoing_cal'),
			'name' => 'consensus_ongoing_cal',
			'type' => 'switch',
			'value' => ko_get_setting('consensus_ongoing_cal'),
		);


		$rota_timespan_labels = [];
		foreach($ROTA_TIMESPANS as $k => $v) $rota_timespan_labels[$k] = getLL('rota_timespan_'.$v);

		$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = [
			'desc' => getLL('rota_settings_consensus_ongoing_cal_timespan'),
			'name' => 'consensus_ongoing_cal_timespan',
			'type' => 'select',
			'value' => ko_get_setting('consensus_ongoing_cal_timespan'),
			'values' => $ROTA_TIMESPANS,
			'descs' => $rota_timespan_labels,
		];
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
 * Creates an excel file for a single event
 *
 * @param $eventid
 * @return string|void
 * @throws Exception
 */
function ko_rota_export_event_xls($eventid) {
	global $access, $ko_path, $DATETIME;

	if ($access['rota']['MAX'] < 2) return;

	$event = ko_rota_get_events('', $eventid, TRUE);
	$day = ko_rota_get_days('', $event['startdatum']);
	$order = 'ORDER BY ' . $_SESSION['sort_rota_teams'] . ' ' . $_SESSION['sort_rota_teams_order'];
	$all_teams = db_select_data('ko_rota_teams', 'WHERE 1', '*', $order);

	$formatting = ['formats' => ['bold' => ['bold' => 1], 'italic' => ['italic' => 1]]];
	$data = [];
	$row = 1;

	$data[$row++] = [$event['_date'] . ' ' . getLL('time_at') . ' ' . $event['_time'] . ' ' . getLL('time_oclock')];

	//Add comment rows
	$add_cols = explode(',', ko_get_userpref($_SESSION['ses_userid'], 'rota_eventfields'));
	if (sizeof($add_cols) > 0) {
		$processed_event = $event;
		foreach ($add_cols as $addCol) {
			if (!isset($processed_event[$addCol])) $processed_event[$addCol] = '';
		}
		kota_process_data('ko_event', $processed_event, 'list', $kotaLog, $event['id'], FALSE, $event);
		$data[$row++] = [''];
		foreach ($add_cols as $col) {
			if ($processed_event[$col]) {
				$formatting['rows'][$row] = 'italic';
				$data[$row++] = [getLL('kota_ko_event_' . $col), $processed_event[$col]];
			}
		}
	}

	$data[$row++] = [''];

	//Add all teams and the schedulled data
	$log_teams = [];
	foreach ($event['teams'] as $tid) {
		if ($access['rota']['ALL'] < 2 && $access['rota'][$tid] < 2) continue;
		$log_teams[] = $all_teams[$tid]['name'];

		$formatting['cells'][$row . ':0'] = 'bold';
		$datarow = [$all_teams[$tid]['name']];
		if ($all_teams[$tid]['rotatype'] == 'event') {
			$schedulled = ko_rota_schedulled_text($event['schedule'][$tid]);
		} else if ($all_teams[$tid]['rotatype'] == 'day') {
			$schedulled = ko_rota_schedulled_text($day[$event['startdatum']]['schedule'][$tid]);
		}
		foreach ($schedulled as $entry) {
			if (ko_get_userpref($_SESSION['ses_userid'], 'rota_markempty') == 1 && $entry == '') {
				$datarow[] = getLL('rota_empty');
			} else {
				$datarow[] = $entry;
			}
		}
		$data[$row++] = $datarow;
	}

	//Create Excel file
	$header = [$event['eventgruppen_name']];
	$title = getLL('rota_export_title') . ' ' . strftime($DATETIME['dmY'], strtotime($event['startdatum']));
	$ko_path = '../../';
	$filename = $ko_path . 'download/excel/' . getLL('rota_filename') . strftime('%d%m%Y_%H%M%S', time()) . '.xlsx';
	$filename = ko_export_to_xlsx($header, $data, $filename, $title, 'portrait', [], $formatting);
	$ko_path = '../';

	ko_log('rota_export', 'event_xls: ' . $eventid . ': ' . $event['eventgruppen_name'] . ' (' . $event['_date'] . ') - teams: ' . implode(', ', $log_teams));

	return basename($filename);
}//ko_rota_export_event_xls()


/**
 * Creates an excel file for a list of events
 *
 * @param string $date for displaying in title and filename
 * @return string|void empty on error
 * @throws Exception
 */
function ko_rota_export_events_xls($date) {
	global $access, $ko_path, $DATETIME;

	if ($access['rota']['MAX'] < 2) return;

	$events = ko_rota_get_events('', '', TRUE);
	$order = 'ORDER BY ' . $_SESSION['sort_rota_teams'] . ' ' . $_SESSION['sort_rota_teams_order'];
	$all_teams = db_select_data('ko_rota_teams', 'WHERE 1', '*', $order);

	$days = ko_rota_get_days();
	$events = ko_rota_combine_events_with_days($events, $days);

	$formatting = ['formats' => ['bold' => ['bold' => 1], 'italic' => ['italic' => 1]]];
	$rows = [];
	$row = 1;
	foreach ($events as $event) {
		$rows[$row++] = [''];
		$formatting['rows'][$row] = 'bold';

		if ($_SESSION['rota_timespan'] == '1d') {
			$rows[$row++] = [$event['eventgruppen_name'] . ' ' . getLL('time_at') . ' ' . $event['_time']];
		} else {
			$rows[$row++] = [strftime($DATETIME['ddmy'], strtotime($event['startdatum'])) . ' ' . getLL('time_at') . ' ' . $event['_time'], $event['eventgruppen_name']];
		}


		//Add comment rows
		$add_cols = explode(',', ko_get_userpref($_SESSION['ses_userid'], 'rota_eventfields'));
		if (sizeof($add_cols) > 0) {
			$processed_event = $event;
			foreach ($add_cols as $addCol) {
				if (!isset($processed_event[$addCol])) $processed_event[$addCol] = '';
			}
			kota_process_data('ko_event', $processed_event, 'list', $kotaLog, $event['id'], FALSE, $event);
			foreach ($add_cols as $col) {
				if ($processed_event[$col]) {
					$formatting['rows'][$row] = 'italic';
					$rows[$row++] = [getLL('kota_ko_event_' . $col), $processed_event[$col]];
				}
			}
		}


		//Add all teams and the schedulled data
		foreach ($event['teams'] as $tid) {
			if ($access['rota']['ALL'] < 2 && $access['rota'][$tid] < 2) continue;

			$datarow = [$all_teams[$tid]['name']];

			//Check for disabled team for this event
			if (ko_rota_is_scheduling_disabled($event['id'], $tid)) {
				$datarow[] = getLL('rota_marker_for_disabled');
			} else {
				$schedulled = ko_rota_schedulled_text($event['schedule'][$tid]);

				foreach ($schedulled as $entry) {
					if (ko_get_userpref($_SESSION['ses_userid'], 'rota_markempty') == 1 && $entry == '') {
						$datarow[] = getLL('rota_empty');
					} else {
						$datarow[] = $entry;
					}
				}
			}
			$rows[$row++] = $datarow;
		}
	}//foreach(events as event)

	//Create Excel file
	$title = getLL('rota_export_title') . ' ' . strftime($DATETIME['dmY'], strtotime($date));
	if ($_SESSION['rota_timespan'] == '1d') {
		$header = [strftime($DATETIME['DdMY'], strtotime($date))];
	} else {
		$header = [ko_rota_timespan_title($_SESSION['rota_timestart'], $_SESSION['rota_timespan'])];
	}
	$ko_path = '../../';
	$filename = $ko_path . 'download/excel/' . getLL('rota_filename') . strftime('%d%m%Y_%H%M%S', time()) . '.xlsx';
	$filename = ko_export_to_xlsx($header, $rows, $filename, $title, 'portrait', [], $formatting);
	$ko_path = '../';

	ko_log('rota_export', 'events_xls: ' . $date . ' - ' . getLL('rota_timespan_' . $_SESSION['rota_timespan']));

	return basename($filename);
}//ko_rota_export_events_xls()


function ko_rota_export_landscape_xls($date, $mode) {
	global $access, $ko_path, $DATETIME;

	if($access['rota']['MAX'] < 2) return;

	$events = ko_rota_get_events('', '', TRUE);
	$weeks = ko_rota_get_days();

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

			$datarow = array(strftime($DATETIME['ddmy'], strtotime($event['startdatum'])).' '.$event['_time'], $event['eventgruppen_name']);
			//Add event fields
			if(sizeof($add_cols) > 0) {
				$processed_event = $event;
				foreach($add_cols as $addCol) {
					if(!isset($processed_event[$addCol])) $processed_event[$addCol] = '';
				}
				kota_process_data('ko_event', $processed_event, 'list', $kotaLog, $event['id'], FALSE, $event);
				foreach($add_cols as $col) {
					$datarow[] = $processed_event[$col];
				}
			}

			$w = $event['startdatum'];

			//Add all teams and the scheduled data
			foreach($all_teams as $tid => $team) {
				if(!in_array($tid, $_SESSION['rota_teams'])) continue;
				if($access['rota']['ALL'] < 2 && $access['rota'][$tid] < 2) continue;
				if(!in_array($tid, $event_teamids)) {
					$datarow[] = '';
					continue;
				}

				
				//Check for disabled team for this event
				if(ko_rota_is_scheduling_disabled($event['id'], $tid)) {
					$datarow[] = getLL('rota_marker_for_disabled');
				} else {
					if($team['rotatype'] == 'event') {
						$entry = implode($delimiter, ko_rota_schedulled_text($event['schedule'][$tid]));
						if(ko_get_userpref($_SESSION['ses_userid'], 'rota_markempty') == 1 && $entry == '') {
							$datarow[] = getLL('rota_empty');
						} else {
							$datarow[] = $entry;
						}
					} else if($team['rotatype'] == 'day') {
						$entry = implode($delimiter, ko_rota_schedulled_text($weeks[$w]['schedule'][$tid]));
						if(ko_get_userpref($_SESSION['ses_userid'], 'rota_markempty') == 1 && $entry == '') {
							$datarow[] = getLL('rota_empty');
						} else {
							$datarow[] = $entry;
						}
					} else {
						$datarow[] = '';
					}
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

		[$start, $stop] = rota_timespan_startstop($_SESSION['rota_timestart'], $_SESSION['rota_timespan']);
		foreach($weeks as $week) {
			if($week['id'] < $start || $week['id'] >= $stop) continue;
			$datarow = [$week['num'].'-'.$week['year'], $week['_date']];

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
 * Create a array with key = placeholder-key and the text which should be replace in mailtexts.
 *
 * Always include all teams not just those active in the session. For this, an excel export can be sent.
 *
 * @param array	 $p person array
 * @param string $eventid
 * @param array $consensus_teams
 * @return array
 */
function ko_rota_get_placeholders($p, $eventid='', $consensus_teams=NULL) {
	global $ko_path, $BASE_URL;

	//Set all to empty so the markers won't show up in the mailtext if not set below
	$r = [
		'[[FIRSTNAME]]' => '',
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
		'[[ABSENCE_LINK]]' => '',
	];

	$all_events = ko_rota_get_events('', $eventid, TRUE);
	//ko_rota_get_events() return single event if one event_id is given, so create an array of events again
	if(!is_array($eventid) && $eventid > 0) $all_events = array($all_events);

	$days = ko_rota_get_days('', $all_events[0]['startdatum'], $all_events[0]['startdatum']);
	$all_events = ko_rota_combine_events_with_days($all_events, $days);
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


	$where = "WHERE leute_id = ". $p['id'];
	$admin = db_select_data("ko_admin", $where);
	// when user has a login, give link to termin medule, else just to front module
	if(!empty($admin)) {
		$absence_link = "<a href=\"" . $BASE_URL . "/daten/index.php?action=list_absence\">" . getLL("placeholder_absence_link") . "</a>";
	} else {
		$absence_link = "<a href=\"" . $BASE_URL . "/index.php\">" . getLL("placeholder_absence_link") . "</a>";
	}
	$r['[[ABSENCE_LINK]]'] = $absence_link;

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
		$txt_event = $event['_date'].' ('.$event['_time'].'): '.$event['eventgruppen_name'].' ('.($event['title']?$event['title']:$event['kommentar']).')';

		$txt_schedule = $txt_schedule_leader = '';
		foreach($personal_teams as $tid => $team) {
			if(ko_rota_is_scheduling_disabled($event['id'], $team['id'])) continue;

			if($all_teams[$tid]['rotatype'] == 'event') {
				$schedulled = ko_rota_schedulled_text($event['schedule'][$tid]);
			} else if($all_teams[$tid]['rotatype'] == 'day') {
				$schedulled = ko_rota_schedulled_text($days[$event['startdatum']]['schedule'][$tid]);
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
			if(ko_rota_is_scheduling_disabled($event['id'], $team['id'])) continue;

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
				} else if($all_teams[$tid]['rotatype'] == 'day') {
					$txt .= $all_teams[$tid]['name'].': '.implode(ko_get_userpref($_SESSION['ses_userid'], 'rota_delimiter'), ko_rota_schedulled_text($days[$event['startdatum']]['schedule'][$tid]))."\n";
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
 * @param string $start date Start date of the timespan
 * @param string $ts string Timespan code (see switch statement for possible values)
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
	} else if(date('m', $sT) == date('m', $eT) && date('Y', $sT) == date('Y', $eT)) {
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
 * @param $week_id string YYYY-MM
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

	if ($access['rota']['MAX'] < 2) return FALSE;

	$events = ko_rota_get_events('', '', TRUE);
	$days = ko_rota_get_days();
	$events = ko_rota_combine_events_with_days($events, $days);

	foreach($events AS $key => $event) {
		if(strpos($event['id'],"-") != FALSE) {
			unset($events[$key]);
		}
	}

	$order = 'ORDER BY ' . $_SESSION['sort_rota_teams'] . ' ' . $_SESSION['sort_rota_teams_order'];
	$all_teams = db_select_data('ko_rota_teams', 'WHERE 1', '*', $order);

	define('FPDF_FONTPATH', $BASE_PATH . 'fpdf/schriften/');
	require_once($BASE_PATH . 'fpdf/mc_table.php');
	$pdf = new PDF_MC_Table('L', 'mm', 'A4');
	$pdf->Open();
	$pdf->SetAutoPageBreak(TRUE, 1);
	$pdf->AddFont('fontn', '', 'arial.php');
	$pdf->AddFont('fontb', '', 'arialb.php');
	$pdf->calculateHeight(TRUE);
	$pdf->border(TRUE);
	$PDF_border_x = 5;
	$PDF_border_y = 15;
	$pdf->SetMargins($PDF_border_x, $PDF_border_y, $PDF_border_x);
	$pdf->SetY($PDF_border_y);

	//Page size
	$page_width = 297 - 2 * $PDF_border_x;

	if (sizeof($events) >= 8) {
		$event_counter = 9;
	} else {
		$event_counter = sizeof($events) + 1;
	}
	$col_w = (int)($page_width / $event_counter);
	$cols = [];
	for ($i = 0; $i < $event_counter; $i++) {
		$cols[] = $col_w;
	}

	$pdf->SetWidths($cols);  //Columns widths
	$font_size = ko_get_userpref($_SESSION['ses_userid'], 'rota_pdf_fontsize');
	if (!$font_size) $font_size = 11;
	$pdf->SetFont('fontn', '', $font_size);
	$pdf->SetZeilenhoehe(0.4 * $font_size);

	//Formating for title row
	$title_aligns = [];
	$title_aligns[] = 'C';
	$title_fills = [];
	$title_fills[] = 0;

	$title_colors = [];
	$title_colors[] = 'ffffff';
	$title_text_colors = [];
	$title_text_colors[] = '000000';

	$comment_aligns = [];
	$comment_aligns[] = 'L';

	$text_aligns = [];
	$text_aligns[] = 'L';

	//Get list separator setting
	$list_separator = strtr(ko_get_userpref($_SESSION['ses_userid'], 'rota_delimiter'), ['<br />' => "\n", '<br>' => "\n"]);

	$event_fields = explode(',', ko_get_userpref($_SESSION['ses_userid'], 'rota_eventfields'));

	$row = 1;

	$all_events = $events;
	$event_slice_offset = 0;
	for ($event_counter = sizeof($all_events); $event_counter >= $event_slice_offset; $event_slice_offset += 8) {
		$pdf->addPage();
		$events = array_slice($all_events, $event_slice_offset, 8);

		//Header row
		$headerrow = [getLL('rota_export_title') . "\n" . ko_rota_timespan_title($_SESSION['rota_timestart'], $_SESSION['rota_timespan'])];
		foreach ($events as $e) {
			//Get rota teams assigned to this event's group
			$event_teamids[$e['eventgruppen_id']] = array_keys(db_select_data('ko_rota_teams', "WHERE `eg_id` REGEXP '(^|,)" . $e['eventgruppen_id'] . "(,|$)'"));

			//Process event data
			$processed_events[$e['id']] = $e;
			foreach ($event_fields as $addCol) {
				if (!isset($processed_events[$e['id']][$addCol])) $processed_events[$e['id']][$addCol] = '';
			}
			kota_process_data('ko_event', $processed_events[$e['id']], 'list', $kotaLog, $e['id'], FALSE, $e);
			$comment_aligns[] = 'L';

			//Header
			if (defined('DP_HEADER_FORMAT')) {
				$e_title = strtr(DP_HEADER_FORMAT, ['DATE' => sql2datum($e['startdatum']), 'TIME' => substr($e['startzeit'], 0, -3), 'OCLOCK' => getLL('time_oclock'), 'EG_NAME' => $grp['name'], 'ROOM' => $e['room']]);
			} else {
				$e_title = sql2datum($e['startdatum']) . "\n";

				$eventTitle = $e[ko_get_userpref($_SESSION['ses_userid'], 'rota_pdf_title')];
				if (!$eventTitle) $eventTitle = $e['eventgruppen_name'];
				$e_title .= $eventTitle . "\n";

				if (!empty($e['startzeit'])) {
					$e_title .= substr($e['startzeit'], 0, -3) . ' ' . getLL('time_oclock');
				} else {
					$e_title .= "ganztags";
				}
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
		$comments = [];
		foreach ($event_fields as $k => $v) {
			if (!$v) unset($event_fields[$k]);
		}

		if (sizeof($event_fields) > 0) {
			require_once($BASE_PATH . 'inc/class.html2text.php');
			foreach ($event_fields as $field) {
				$comments[$crow][] = getLL('kota_ko_event_' . $field);
				foreach ($events as $event) {
					$value = strip_tags(html_entity_decode($processed_events[$event['id']][$field], ENT_COMPAT | ENT_HTML401, 'iso-8859-1'), '<br><br/>');
					$html2text = new html2text($value);
					$value = $html2text->get_text();
					$comments[$crow][] = $value;
				}
				$crow++;
			}
		}

		//Draw title row
		if (ko_get_userpref($_SESSION['ses_userid'], 'rota_pdf_use_colors') == 1) {
			$pdf->SetFillColors($title_colors);
		} else {
			$pdf->SetFillColor(200);
		}

		$pdf->SetFills($title_fills);
		$pdf->SetTextColors($title_text_colors);
		$pdf->SetAligns($title_aligns);
		$pdf->SetFont('fontb', '', $font_size - 1);
		$pdf->Row($headerrow);

		//Add comment rows
		$pdf->SetFont('fontn', '', $font_size - 1);
		if (sizeof($comments) > 0) {
			$pdf->SetAligns($comment_aligns);
			foreach ($comments as $crow) $pdf->Row($crow);
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

		foreach ($all_teams as $tid => $team) {
			if (!in_array($tid, $_SESSION['rota_teams']) && !in_array($tid, $_SESSION['rota_teams_readonly'])) continue;
			if ($access['rota']['ALL'] < 2 && $access['rota'][$tid] < 2) continue;

			$fills = [];
			$fills[] = 1;  //Mark first column containing the team's name

			$datarow = [$all_teams[$tid]['name']];

			foreach ($events as $event) {
				$day_id = $event['startdatum'];
				//Check for disabled team for this event
				if (ko_rota_is_scheduling_disabled($event['id'], $tid)) {
					$datarow[] = getLL('rota_marker_for_disabled');
				} else {
					if ($all_teams[$tid]['rotatype'] == 'event') {
						$entry = implode($list_separator, ko_rota_schedulled_text($event['schedule'][$tid]));
						if (ko_get_userpref($_SESSION['ses_userid'], 'rota_markempty') == 1 && $entry == '') {
							$datarow[] = getLL('rota_empty');
						} else {
							$datarow[] = $entry;
						}
					} else if ($all_teams[$tid]['rotatype'] == 'day') {
						$entry = implode($list_separator, ko_rota_schedulled_text($days[$day_id]['schedule'][$tid]));
						if (ko_get_userpref($_SESSION['ses_userid'], 'rota_markempty') == 1 && $entry == '') {
							$datarow[] = getLL('rota_empty');
						} else {
							$datarow[] = $entry;
						}
					} else {
						$datarow[] = '';
					}
				}

				$fills[] = 0;
			}//foreach(events as event)
			$pdf->SetFills($fills);
			$pdf->Row($datarow);
		}//foreach(SESSION[rota_teams] as tid)

		//footer right
		$pdf->SetFont('fontn', '', 8);
		$person = ko_get_logged_in_person();
		$creator = $person['vorname'] ? $person['vorname'] . ' ' . $person['nachname'] : $_SESSION['ses_username'];
		$footerRight = sprintf(getLL('tracking_export_label_created'), strftime($DATETIME['dmY'] . ' %H:%M', time()), $creator);
		$footerStart = $page_width + $PDF_border_x - $pdf->GetStringWidth($footerRight);
		$pdf->Text($footerStart, 210 - 5, $footerRight);

		//footer left
		$pdf->Text($PDF_border_x, 210 - 5, $BASE_URL);

		//Logo
		$logo = ko_get_pdf_logo();
		if ($logo) {
			$pic = getimagesize($BASE_PATH . 'my_images' . '/' . $logo);
			$picWidth = 9 / $pic[1] * $pic[0];
			$pdf->Image($BASE_PATH . 'my_images' . '/' . $logo, $page_width + $PDF_border_x - $picWidth, 4, $picWidth);
		}
	}

	ko_log('rota_export', 'landscape_pdf: ' . getLL('rota_timespan_' . $_SESSION['rota_timespan']));

	$ko_path = '../../';
	$filename = $ko_path . 'download/pdf/' . getLL('rota_filename') . strftime('%d%m%Y_%H%M%S', time()) . '.pdf';
	$ko_path = '../';
	$pdf->Output($filename);
	return basename($filename);
}//ko_rota_export_landscape_pdf()


/**
 * Create Excel-Export "Mitarbeiter-Übersicht" either in portrait or landscape orientation
 *
 * @param string $orientation
 * @return bool|string|void
 * @throws PHPExcel_Exception
 * @throws PHPExcel_Reader_Exception
 * @throws PHPExcel_Writer_Exception
 */
function ko_rota_export_helper_overview($orientation = 'portrait') {
	global $access, $ko_path, $DATETIME;

	require_once $ko_path . '../inc/phpexcel/PHPExcel.php';
	require_once $ko_path . '../inc/phpexcel/PHPExcel/Writer/Excel2007.php';

	if ($access['rota']['MAX'] < 2) return;

	$event_fields = explode(',', ko_get_userpref($_SESSION['ses_userid'], 'rota_eventfields'));
	array_filter($event_fields, function ($el) {
		return trim($el) != '';
	});

	$dbEvents = ko_rota_get_events('', '', TRUE);
	$days = ko_rota_get_days();
	$dbEvents = ko_rota_combine_events_with_days($dbEvents, $days);

	$processedEvents = $dbEvents;
	foreach ($processedEvents as $id => $e) {
		foreach ($event_fields as $addCol) {
			if (!isset($processedEvents[$id][$addCol])) $processedEvents[$id][$addCol] = '';
		}
		kota_process_data('ko_event', $processedEvents[$id], 'xls,list', $kotaLog, $e['id'], FALSE, $processedEvents[$id]);
	}

	$teams = $_SESSION['rota_teams'];

	if (sizeof($teams) == 0) {
		return FALSE;
	} else {
		$order = 'ORDER BY ' . $_SESSION['sort_rota_teams'] . ' ' . $_SESSION['sort_rota_teams_order'];
		$all_teams = db_select_data('ko_rota_teams', 'WHERE `id` in (' . implode(',', $teams) . ')', '*', $order);
	}

	foreach ($all_teams as $tk => $team) {
		if ($access['rota']['ALL'] < 2 && $access['rota'][$team['id']] < 2) {
			unset($all_teams[$tk]);
		};
	}

	$data = ['events' => $processedEvents, 'teams' => $all_teams, 'data' => []];

	foreach ($all_teams as $tk => $team) {
		$team_entry = ['members' => ko_rota_get_team_members($team['id'], TRUE)];

		// Add all helpers that were added using free text
		foreach ($dbEvents as $event) {
			if (in_array($team['id'], $event['teams'])) {
				$helpers = ko_rota_get_helpers_by_event_team($event['id'], $team['id']);
				foreach ($helpers as $k => $helper) {
					if ($helper['is_free_text']) {
						$team_entry['members']['people'][$helper['name']] = ['vorname' => $helper['name'], 'nachname' => '', 'uid' => "e{$event['id']}_t{$team['id']}_u{$k}", 'is_free_text' => TRUE];
					}
				}
			}

			// amtstage helper
			foreach(explode(",", $event['schedule'][$team['id']]) AS $k => $scheduled_person) {
				if(empty($scheduled_person)) continue;
				if(is_numeric($scheduled_person)) {
					ko_get_person_by_id($scheduled_person, $person);
					$team_entry['members']['people'][$scheduled_person] = $person;
					$team_entry['members']['people'][$scheduled_person]['rotatype'] = "day";
				} else {
					$team_entry['members']['people'][$scheduled_person] = ['vorname' => $scheduled_person, 'nachname' => '', 'uid' => "e{$event['id']}_t{$team['id']}_u{$k}", 'is_free_text' => TRUE, 'rotatype' => "day",];
				}
			}
		}

		foreach ($dbEvents as $event) {
			$data_entry = [];
			if ($team['rotatype'] != "day" && is_numeric($event['id']) && !in_array($team['id'], $event['teams'])) {
				$data_entry['disabled'] = TRUE;
			} else if (is_numeric($event['id']) && ko_rota_is_scheduling_disabled($event['id'], $team['id'])) {
				$data_entry['disabled'] = TRUE;
			} else if (!is_numeric($event['id']) && !in_array($team['id'], $event['teams'])) {
				$data_entry['disabled'] = TRUE;
			} else if ($team['rotatype'] == "day" && !is_numeric($event['id']) && !in_array(date("N", strtotime($event['id'])), explode(",",$team['days_range']))) {
				$data_entry['disabled'] = TRUE;
			} else {
				$entries = [];
				$helpers = ko_rota_get_helpers_by_event_team($event['id'], $team['id']);
				foreach ($team_entry['members']['people'] as $member) {
					$found = FALSE;
					foreach ($helpers as $k => $helper) {
						if ($helper['id'] == $member['id'] || ($helper['is_free_text'] && $member['uid'] == "e{$event['id']}_t{$team['id']}_u{$k}")) {
							$found = 'TRUE';
							break;
						}
					}

					if($member['rotatype'] == "day") {
						if(is_numeric($member['id']) && in_array($member['id'], explode(",", $event['schedule'][$team['id']]))) {
							$found = 'TRUE';
						} elseif(in_array($member['vorname'], explode(",", $event['schedule'][$team['id']]))) {
							$found = 'TRUE';
						}
					}

					//Only get consensus if it is active for this team and entered value is not free text
					if ($team['allow_consensus'] && $member['is_free_text'] !== TRUE) {
						$consensus_answer = ko_consensus_get_answers('person', $event['id'], $team['id'], $member['id']);
					} else {
						$consensus_answer = '';
					}
					$entries[] = ['scheduled' => $found, 'consensus' => $consensus_answer];
				}
				$data_entry['event_entries'] = $entries;
			}
			$team_entry['team_entries'][] = $data_entry;
		}
		$data['data'][] = $team_entry;
	}

	$title = utf8_encode(getLL('rota_helperoverview_title') . "\n" . ko_rota_timespan_title($_SESSION['rota_timestart'], $_SESSION['rota_timespan']));

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

	$cellstyle = [
		'title' => [
			'font' => [
				'bold' => ko_get_setting('xls_title_bold') ? TRUE : FALSE,
				'size' => 14,
				'name' => utf8_encode(ko_get_setting('xls_title_font')),
			],
			'alignment' => [
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
			],
		],
		'eventfield' => [
			'font' => [
				'size' => 8,
			],
		],
		'teamtitle' => [
			'font' => [
				'bold' => ko_get_setting('xls_title_bold') ? TRUE : FALSE,
				'size' => 12,
				'name' => utf8_encode(ko_get_setting('xls_title_font')),
				'color' => [
					'rgb' => '000000',
				],
			],
			'fill' => [
				'type' => PHPExcel_Style_Fill::FILL_SOLID,
				'startcolor' => [
					'rgb' => 'EEEEEE',
				],
			],
			'alignment' => [
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
			],
		],
		'consensus' => [
			'1' => [
				'fill' => [
					'type' => PHPExcel_Style_Fill::FILL_SOLID,
					'startcolor' => [
						'rgb' => 'f2dede',
					],
				],
			],
			'2' => [
				'fill' => [
					'type' => PHPExcel_Style_Fill::FILL_SOLID,
					'startcolor' => [
						'rgb' => 'f9f1c7',
					],
				],
			],
			'3' => [
				'fill' => [
					'type' => PHPExcel_Style_Fill::FILL_SOLID,
					'startcolor' => [
						'rgb' => 'dff0d8',
					],
				],
			],
		],
		'disabled' => [
			'fill' => [
				'type' => PHPExcel_Style_Fill::FILL_SOLID,
				'startcolor' => [
					'rgb' => 'DDDDDD',
				],
			],
		],
		'bordertop' => [
			'borders' => [
				'top' => [
					'style' => PHPExcel_Style_Border::BORDER_THIN,
					'color' => ['rgb' => '000000'],
				],
			],
		],
		'borderleft' => [
			'borders' => [
				'left' => [
					'style' => PHPExcel_Style_Border::BORDER_THIN,
					'color' => ['rgb' => '000000'],
				],
			],
		],
		'borderright' => [
			'borders' => [
				'right' => [
					'style' => PHPExcel_Style_Border::BORDER_THIN,
					'color' => ['rgb' => '000000'],
				],
			],
		],
		'borderbottom' => [
			'borders' => [
				'bottom' => [
					'style' => PHPExcel_Style_Border::BORDER_THIN,
					'color' => ['rgb' => '000000'],
				],
			],
		],
		'entry' => [
			'alignment' => [
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			],
		],
		'borderleft_small' => [
			'borders' => [
				'left' => [
					'style' => PHPExcel_Style_Border::BORDER_THIN,
					'color' => ['rgb' => 'CCCCCC'],
				],
			],
		],
	];

	$cellstyle_p = $cellstyle;
	unset($cellstyle_p['teamtitle']['fill']);
	unset($cellstyle_p['teamtitle']['alignment']);
	$cellstyle_p['teamtitle']['font'] = [
		'bold' => FALSE,
		'size' => 8,
	];

	//Add Logo
	$logo1Path = '../my_images/' . ko_get_pdf_logo();

	if ($orientation == 'landscape') {
		if (file_exists($ko_path . $logo1Path)) {
			$logo1Path = $ko_path . $logo1Path;
			$objDrawing = new PHPExcel_Worksheet_Drawing();
			$objDrawing->setName('Logo');
			$objDrawing->setDescription('Logo');
			$objDrawing->setPath($logo1Path);
			$objDrawing->setCoordinates('A1');
			$objDrawing->setResizeProportional(TRUE);
			$objDrawing->setWidthAndHeight(300, 70);
			$objDrawing->setWorksheet($sheet);
		}

		$sheet->getColumnDimension('A')->setWidth(30);

		//title
		$sheet->getRowDimension(1)->setRowHeight(60);
		$sheet->setCellValueByColumnAndRow(2, 1, $title);
		$sheet->getStyle('C1')->applyFromArray($cellstyle['title']);

		$row = 2;
		$col = 0;
		$field_titles = [getLL('kota_listview_ko_event_eventgruppen_id'), getLL('kota_listview_ko_event_startdatum'), getLL('kota_listview_ko_event_startzeit')];
		foreach ($event_fields as $event_field) {
			$ll = getLL("kota_listview_ko_event_{$event_field}");
			if (!$ll) $ll = getLL("kota_ko_event_{$event_field}");
			$field_titles[] = $ll;
		}
		foreach ($field_titles as $field_title) {
			$sheet->setCellValueByColumnAndRow($col, $row, utf8_encode($field_title));
			$sheet->getStyleByColumnAndRow($col, $row)->applyFromArray($cellstyle['eventfield']);
			$sheet->getStyleByColumnAndRow($col, $row)->getAlignment()->setWrapText(TRUE);
			$row++;
		}

		$row = 2;
		$col = 1;
		foreach ($data['events'] as $event) {
			$row = 2;

			if ($use_colors && $event['eventgruppen_farbe']) {
				$fillstyle = [
					'fill' => [
						'type' => PHPExcel_Style_Fill::FILL_SOLID,
						'startcolor' => [
							'rgb' => strtoupper($event['eventgruppen_farbe']),
						],
					],
					'font' => [
						'color' => [
							'rgb' => ko_get_contrast_color($event['eventgruppen_farbe'], '000000', 'FFFFFF'),
						],
					],
				];

				for ($k = $row; $k < $row + sizeof($field_titles); $k++) {
					$sheet->getStyleByColumnAndRow($col, $k)->applyFromArray($fillstyle);
				}
			}

			$fields = [];
			$fields[] = $event['eventgruppen_name'];
			$fields[] = strftime($DATETIME['ddmy'], strtotime($event['startdatum']));
			$fields[] = $event['_time'];

			foreach ($event_fields as $event_field) {
				$fields[] = $event[$event_field];
			}
			foreach ($fields as $field) {

				$sheet->setCellValueByColumnAndRow($col, $row, utf8_encode($field));
				$sheet->getStyleByColumnAndRow($col, $row)->applyFromArray($cellstyle['eventfield']);
				$sheet->getStyleByColumnAndRow($col, $row)->getAlignment()->setWrapText(TRUE);

				$row++;
			}
			$sheet->getColumnDimensionByColumn($col)->setWidth(15);
			$col++;
		}

		$minCol = NULL;
		$maxCol = 0;
		$minRow = $row;
		$maxRow = $minRow;
		$k = 0;
		foreach ($data['teams'] as $team) {
			$old_row = $row;
			$col = 0;
			$sheet->getRowDimension($row)->setRowHeight(19);
			$sheet->setCellValueByColumnAndRow($col, $row, utf8_encode($team['name']));
			for ($j = $col; $j <= $col + sizeof($data['events']); $j++) {
				$sheet->getStyleByColumnAndRow($j, $row)->applyFromArray($cellstyle['teamtitle']);
			}
			$col2 = 1;
			foreach ($data['events'] as $event) {
				//Check for disabled team for this event
				if (ko_rota_is_scheduling_disabled($event['id'], $team['id'])) {
					$sheet->setCellValueByColumnAndRow($col2, $row, utf8_encode(getLL('rota_marker_for_disabled')));
				}
				$col2++;
			}

			$row++;
			if ($minCol === NULL || $col < $minCol) $minCol = $col;

			$team_data = $data['data'][$k];
			foreach ($team_data['members']['people'] as $member) {
				$roles = ko_rota_get_member_roles_in_team($member, $team);
				$roleName = implode(', ', $roles);
				if ($roleName) $roleName = ' (' . $roleName . ')';

				if ($member['is_free_text']) {
					$sheet->setCellValueByColumnAndRow($col, $row, utf8_encode("\"" . trim($member['vorname'] . ' ' . $member['nachname'] . $roleName)) ."\"");
				} else {
					$sheet->setCellValueByColumnAndRow($col, $row, utf8_encode(trim($member['vorname'] . ' ' . $member['nachname'] . $roleName)));
				}
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
						if ($helper_entry['consensus']) {
							$consensusStyle = $cellstyle['consensus'][$helper_entry['consensus']];
						} else {
							$consensusStyle = [];
						}
						$sheet->getStyleByColumnAndRow($col, $row)->applyFromArray(array_merge($cellstyle['entry'], $consensusStyle));
						if ($helper_entry['scheduled']) {
							$sheet->setCellValueByColumnAndRow($col, $row, $mark);
						}
						$row++;
					}
				}
				$col++;
			}
			$maxCol = max($maxCol, $col);
			$maxRow = max($maxRow, $row);
			$k++;
		}
		$minCol++;
		$maxCol--;
		$minRow++;
		$maxRow--;

		// add totals
		for ($c = $minCol; $c <= $maxCol; $c++) {
			$colString = PHPExcel_Cell::stringFromColumnIndex($c);
			$sheet->setCellValueByColumnAndRow($c, $maxRow + 1, '=COUNTIF(' . $colString . $minRow . ':' . $colString . $maxRow . ',"x")');
		}
		$startColString = PHPExcel_Cell::stringFromColumnIndex($minCol);
		$stopColString = PHPExcel_Cell::stringFromColumnIndex($maxCol);
		for ($r = $minRow; $r <= $maxRow; $r++) {
			$sheet->setCellValueByColumnAndRow($maxCol + 1, $r, '=COUNTIF(' . $startColString . $r . ':' . $stopColString . $r . ',"x")');
		}

	} else {
		if (file_exists($ko_path . $logo1Path)) {
			$logo1Path = $ko_path . $logo1Path;
			$objDrawing = new PHPExcel_Worksheet_Drawing();
			$objDrawing->setName('Logo');
			$objDrawing->setDescription('Logo');
			$objDrawing->setPath($logo1Path);
			$objDrawing->setCoordinates('A1');
			$objDrawing->setResizeProportional(TRUE);
			$objDrawing->setWidthAndHeight(300, 70);
			$objDrawing->setWorksheet($sheet);
		}

		$sheet->getColumnDimension('A')->setWidth(20);
		$sheet->getRowDimension(1)->setRowHeight(60);

		//title
		$sheet->setCellValueByColumnAndRow(3, 1, $title);
		$sheet->getStyle('D1')->applyFromArray($cellstyle_p['title']);

		$row = 2;
		$col = 0;

		$sheet->getRowDimension(3)->setRowHeight(-1);

		$field_titles = array(getLL('kota_listview_ko_event_eventgruppen_id'), getLL('kota_listview_ko_event_startdatum'), getLL('kota_listview_ko_event_startzeit'));
		foreach ($event_fields as $event_field) {
			$ll = getLL("kota_listview_ko_event_{$event_field}");
			if (!$ll) $ll = getLL("kota_ko_event_{$event_field}");
			$field_titles[] = $ll;
		}
		foreach ($field_titles as $field_title) {
			$sheet->setCellValueByColumnAndRow($col, $row, utf8_encode($field_title));
			$sheet->getStyleByColumnAndRow($col, $row)->applyFromArray($cellstyle['eventfield']);
			$col++;
		}

		$row = 4;
		$col = 0;
		foreach ($data['events'] as $event) {
			$col = 0;

			if ($use_colors && $event['eventgruppen_farbe']) {
				$fillstyle = [
					'fill' => [
						'type' => PHPExcel_Style_Fill::FILL_SOLID,
						'startcolor' => [
							'rgb' => strtoupper($event['eventgruppen_farbe']),
						],
					],
					'font' => [
						'color' => [
							'rgb' => ko_get_contrast_color($event['eventgruppen_farbe'], '000000', 'FFFFFF'),
						],
					],
				];

				for ($k = $col; $k < $col + sizeof($field_titles); $k++) {
					$sheet->getStyleByColumnAndRow($k, $row)->applyFromArray($fillstyle);
				}
			}

			$fields = [];
			$fields[] = $event['eventgruppen_name'];
			$fields[] = strftime($DATETIME['ddmy'], strtotime($event['startdatum']));
			$fields[] = $event['_time'];

			foreach ($event_fields as $event_field) {
				$fields[] = $event[$event_field];
			}
			foreach ($fields as $field) {
				$sheet->getColumnDimensionByColumn($col)->setWidth(13);
				$sheet->setCellValueByColumnAndRow($col, $row, utf8_encode($field));
				$sheet->getStyleByColumnAndRow($col, $row)->applyFromArray($cellstyle_p['eventfield']);
				$sheet->getStyleByColumnAndRow($col, $row)->getAlignment()->setWrapText(TRUE);

				$col++;
			}
			$row++;
		}

		$k = 0;
		$minCol = $col;
		$maxCol = $minCol;
		$minRow = NULL;
		$maxRow = 0;
		foreach ($data['teams'] as $team) {
			$old_col = $col;
			$row = 2;
			$sheet->setCellValueByColumnAndRow($col, $row, utf8_encode($team['name']));
			$sheet->getStyleByColumnAndRow($col, $row)->applyFromArray($cellstyle_p['teamtitle']);
			for ($j = $row; $j <= $row + sizeof($data['events']) + 1; $j++) {
				$sheet->getStyleByColumnAndRow($col, $j)->applyFromArray($cellstyle_p['borderleft']);
			}
			$row++;
			if ($minRow === NULL || $row < $minRow) $minRow = $row;

			$disabled = ko_rota_is_scheduling_disabled($event['id'], $team['id']);

			$team_data = $data['data'][$k];
			$c = 0;
			foreach ($team_data['members']['people'] as $member) {
				if ($c > 0) {
					for ($j = $row; $j <= $row + sizeof($data['events']); $j++) {
						$sheet->getStyleByColumnAndRow($col, $j)->applyFromArray($cellstyle_p['borderleft_small']);
					}
				}

				$roles = ko_rota_get_member_roles_in_team($member, $team);
				$roleName = implode(', ', $roles);
				if ($roleName) $roleName = ' (' . $roleName . ')';

				$sheet->getColumnDimensionByColumn($col)->setWidth(4);
				$sheet->setCellValueByColumnAndRow($col, $row, utf8_encode($member['vorname'] . ' ' . $member['nachname'] . $roleName));
				$sheet->getStyleByColumnAndRow($col, $row)->getAlignment()->setTextRotation(90);
				$col++;
				$c++;
			}
			$row++;
			$maxCol = max($maxCol, $col);

			foreach ($team_data['team_entries'] as $event_entries) {
				$col = $old_col;
				//Team is not active in this event
				if ($event_entries['disabled']) {
					for ($i = 0; $i < sizeof($team_data['members']['people']); $i++) {
						$sheet->setCellValueByColumnAndRow($col, $row, utf8_encode(getLL('rota_marker_for_disabled')));
						$sheet->getStyleByColumnAndRow($col, $row)->getFill()->applyFromArray($cellstyle_p['disabled']['fill']);
						$col++;
					}
				} else {
					foreach ($event_entries['event_entries'] as $helper_entry) {
						if ($helper_entry['consensus']) {
							$consensusStyle = $cellstyle['consensus'][$helper_entry['consensus']];
						} else {
							$consensusStyle = [];
						}
						$sheet->getStyleByColumnAndRow($col, $row)->applyFromArray(array_merge($cellstyle['entry'], $consensusStyle));
						if ($helper_entry['scheduled']) {
							$sheet->setCellValueByColumnAndRow($col, $row, $mark);
						}
						$col++;
					}
				}
				$row++;
				$maxCol = max($maxCol, $col);
			}
			$maxRow = max($maxRow, $row);
			$k++;
		}
		$maxCol--;
		$minRow++;
		$maxRow--;

		//print_d(array($minCol, $maxCol, $minRow, $maxRow));

		// add totals
		for ($c = $minCol; $c <= $maxCol; $c++) {
			$colString = PHPExcel_Cell::stringFromColumnIndex($c);
			$sheet->setCellValueByColumnAndRow($c, $maxRow + 1, '=COUNTIF(' . $colString . $minRow . ':' . $colString . $maxRow . ',"x")');
		}
		$startColString = PHPExcel_Cell::stringFromColumnIndex($minCol);
		$stopColString = PHPExcel_Cell::stringFromColumnIndex($maxCol);
		for ($r = $minRow; $r <= $maxRow; $r++) {
			$sheet->setCellValueByColumnAndRow($maxCol + 1, $r, '=COUNTIF(' . $startColString . $r . ':' . $stopColString . $r . ',"x")');
		}
	}


	$filename = $ko_path . '../download/excel/' . getLL('rota_filename') . date('YmdHis') . '.xlsx';
	//simple fileformat for testing
	//$filename = $ko_path.'download/excel/bk.xlsx';
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter->setPreCalculateFormulas(TRUE);
	$objWriter->save($filename);

	return $filename;
}


function ko_rota_send_file_form($get) {
	global $ko_path, $BASE_PATH, $DATETIME, $access, $smarty, $BASE_URL;

	if($access['rota']['MAX'] < 4) return FALSE;

	$c  = '<h3>'.getLL('download_send_title').'</h3>';
	$c .= '<input type="hidden" name="filetype" value="'.$get['filetype'].'" />';
	$c .= '<div class="row">';
	$c .= '<div class="col-xs-12 col-md-8">';
	$c .= '<div class="form-group">';
	$c .= '<label>'.getLL('download_send_sender').':</label>';
	$c .= '<select name="sender" class="input-sm form-control">';
	//Sender: one of the email addresses of this login
	$p = ko_get_logged_in_person();
	$emails = ko_get_leute_emails($p['id']);
	if(sizeof($emails) > 0) {
		foreach($emails as $email) {
			if(!$email) continue;
			$name = $p['vorname'] || $p['nachname'] ? $p['vorname'].' '.$p['nachname'] : $p['firm'];
			$c .= '<option value="'.$email.'">'.($name ? '&quot;'.$name.'&quot; ' : '').'&lt;'.$email.'&gt;</option>';
		}
	}
	$info_email = ko_get_setting('info_email');
	$info_name = ko_get_setting('info_name');
	if($info_email) {
		$c .= '<option value="'.$info_email.'">'.($info_name ? '&quot;'.$info_name.'&quot; ' : '').'&lt;'.$info_email.'&gt;</option>';
	}

	$c .= '</select>';
	$c .= '</div>';
	$c .= '</div>';
	$c .= '</div>';

	$leaderRole = ko_get_setting('rota_leaderrole');
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
	if($leaderRole) {
		$c .= '<option value="selectedleaders" '.$sel['selectedleaders'].' title="'.getLL('rota_send_selectedleaders').'">'.getLL('rota_send_selectedleaders').'</option>';
	}
	if($access['rota']['ALL'] > 3) {
		$c .= '<option value="allrotamembers" '.$sel['allrotamembers'].' title="'.getLL('rota_send_allrotamembers').'">'.getLL('rota_send_allrotamembers').'</option>';
		$c .= '<option value="allrotamembersconsensus" '.$sel['allrotamembersconsensus'].' title="'.getLL('rota_send_allrotamembersconsensus').'">'.getLL('rota_send_allrotamembersconsensus').'</option>';
		if($leaderRole) {
			$c .= '<option value="allrotaleaders" '.$sel['allrotaleaders'].' title="'.getLL('rota_send_allrotaleaders').'">'.getLL('rota_send_allrotaleaders').'</option>';
		}
	}
	$c .= '<option value="manualschedulled" '.$sel['manualschedulled'].' title="'.getLL('rota_send_manualschedulled').'">'.getLL('rota_send_manualschedulled').'</option>';
	$c .= '<option value="manualmembers" '.$sel['manualmembers'].' title="'.getLL('rota_send_manualmembers').'">'.getLL('rota_send_manualmembers').'</option>';
	if($loaderRole) {
		$c .= '<option value="manualleaders" '.$sel['manualleaders'].' title="'.getLL('rota_send_manualleaders').'">'.getLL('rota_send_manualleaders').'</option>';
	}
	$c .= '<option value="single" '.$sel['single'].' title="'.getLL('rota_send_single').'">'.getLL('rota_send_single').'</option>';


	if($get['subject']) {
		$subject = $get['subject'];
	}
	//Set default subject
	else {
		if(substr($get['filetype'], 0, 5) == 'event') {
			[$mode, $eventid] = explode(':', $get['filetype']);
			$event = db_select_data('ko_event AS e, ko_eventgruppen AS eg', "WHERE e.id = '$eventid' AND eg.id = e.eventgruppen_id", 'e.*, eg.name AS eventgruppen_name', '', '', TRUE);
			$subject = getLL('email_subject_prefix').getLL('download_send_subject_default').' '.$event['eventgruppen_name'].' '.strftime($DATETIME['dMY'], strtotime($event['startdatum']));
		} else {
			$subject = getLL('email_subject_prefix').getLL('download_send_subject_default').' '.ko_rota_timespan_title($_SESSION['rota_timestart'], $_SESSION['rota_timespan']);
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
	$localSm->assign('input',
		[
			'type' => 'doubleselect',
			'values' => $values,
			'descs' => $descs,
			'avalues' => $avalues,
			'adescs' => $adescs,
			'value' => $value,
			'name' => 'single_id',
			'html_id' => 'single_id',
			"show_filter" => TRUE,
			"size" => [
				"for_filter" => 6,
				"normal" => 8,
			],
			'js_func_add' => 'double_select_add'
	]);

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
	$c .= '<div class="groupsearch-wrapper"><input type="hidden" name="recipients_group" id="recipients_group" value="">
			<button type="button" class="groupsearch-button btn btn-default btn-sm full-width" style="display: none;"></button>
			<ul class="typeahead dropdown-menu" role="listbox" style="display: none;">';

	//Get selected group from POST or userpref
	$cur = $_POST['recipients_group'];
	if(!$cur) $cur = ko_get_userpref($_SESSION['ses_userid'], 'rota_recipients_group');

	$all_groups = db_select_data('ko_groups', 'WHERE 1');
	$groups = ko_groups_get_recursive(ko_get_groups_zwhere());
	foreach($groups as $grp) {
		if($access['groups']['ALL'] < 1 && $access['groups'][$grp['id']] < 1) continue;

		$pre = '';
		$depth = sizeof(ko_groups_get_motherline($grp['id'], $all_groups));
		for($i=0; $i<$depth; $i++) $pre .= '&nbsp;&nbsp;';

		$c .= '<li><a href="#" role="option" title="'.$pre.ko_html($grp['name']).'">'.$pre.ko_html($grp['name']).'</a></li>';
	}

	$c .= '</ul></div>
			<script>
					$(\'#recipients_group\').groupsearch({
						multiple: false	});
			</script>';

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
	$c .= '<div class="row">';
	$c .= '<div class="col-md-8">';
	$c .= '<div class="form-group">';
	$c .= '<label>'.getLL('download_send_files').':</label>';

	// prepare initial files
	if($get['file']) {
		$filename = basename($get['file']);
		copy($ko_path . $get['file'], $ko_path . 'my_images/temp/00000000-0000-0000-0000-000000000000_' . $filename );
		$_POST['rota_filesend_files'] = '00000000-0000-0000-0000-000000000000_' . $filename ;
	}

	//Upload form for new file
	$initValue = isset($_POST['rota_filesend_files']) ? $_POST['rota_filesend_files'] : '';
	$initFiles = array();
	if (isset($_POST['rota_filesend_files'])) {
		foreach (explode('@|,|@', $_POST['rota_filesend_files']) as $uuid) {
			if (!$uuid) continue;
			$name = substr($uuid, 37);
			$size = filesize($ko_path . 'my_images/temp/' . $uuid);
			$thumbnailFile = $uuid . '.thumbnail';
			$f = array(
				'name' => $name,
				'size' => $size,
				'uuid' => $uuid,
				'thumbnailUrl' => $BASE_URL . 'my_images/temp/' . $thumbnailFile,
			);
			$initFiles[] = $f;
		}
	}
	$fineUploaderLabels = array('confirmMessage', 'deletingFailedText', 'deletingStatusText', 'tooManyFilesError', 'unsupportedBrowser', 'autoRetryNote', 'namePromptMessage', 'failureText', 'failUpload', 'formatProgress', 'paused', 'waitingForResponse');
	$fineUploaderLL = array();
	foreach ($fineUploaderLabels as $l) {
		$fineUploaderLL[] = "{$l}: '".getLL("fine_uploader_label_{$l}")."'";
	}
	$labels = implode(",\n", $fineUploaderLL);
	$c .= '<div id="rota_filesend_files"></div>
			<input type="hidden" name="rota_filesend_files" value="'.$initValue.'">
			<script>
			 	var uploader = new qq.FineUploader({
    	        	element: document.getElementById("rota_filesend_files"),
					'.($labels).',
					debug: true,
					request: {
						endpoint: \'../inc/upload.php\'
						},
					thumbnails: {
						placeholders: {
							waitingPath: "../inc/fine-uploader/placeholders/waiting-generic.png",
									notAvailablePath: "../inc/fine-uploader/placeholders/not_available-generic.png"
							}
						},
					deleteFile: {
						enabled: true,
								method: \'POST\',
								endpoint: \'/inc/upload.php\'
						},
					retry: {
						enableAuto: true
						},
					callbacks: {
						onAllComplete: function(succeeded, failed) {
							var v = [];
							this.getUploads({status: qq.status.UPLOAD_SUCCESSFUL}).forEach(function(e) {
								v.push(e.uuid);
							});
							$(\'[name="rota_filesend_files"]\').val(v.join(\'@|,|@\'));
							},
						onDeleteComplete: function(id, xhr, isError) {
							var v = [];
							this.getUploads({status: qq.status.UPLOAD_SUCCESSFUL}).forEach(function(e) {
								v.push(e.uuid);
							});
							$(\'[name="rota_filesend_files"]\').val(v.join(\'@|,|@\'));
							}
						}
					});
					';

	if (count($initFiles) > 0) {
		$c .= '		uploader.addInitialFiles([
					{
						\'name\' : \'' . $initFiles[0]["name"] . '\',
						\'size\' : \'' . $initFiles[0]["size"] . '\',
						\'uuid\' : \'' . $initFiles[0]["uuid"] . '\',
						\'thumbnailUrl\' : \'' . $initFiles[0]["thumbnailUrl"] . '\'
					}
				]);';
	}

	$c .= '</script>
			</div>';
	$c .= '</div>';
	$c .= '</div>';

	$c .= '<div class="row">';
	$c .= '<div class="col-md-8">';
	$c .= '<div class="form-group">';
	$c .= '<label>'.getLL('download_send_text').':</label>';
	//Show select with placeholders for rota
	$c .= '<select name="placeholder" class="input-sm form-control" id="placeholder" onchange="richtexteditor_insert_text(\'emailtext\', \'[[\'+this.value+\']]\');">';
	$c .= '<option value="">'.getLL('download_send_insert_placeholder').'</option>';
	$c .= '<option value="" disabled="disabled">-------------------------</option>';
	foreach(array('_SALUTATION', '_SALUTATION_FORMAL', 'FIRSTNAME', 'LASTNAME', 'TEAM_NAME', 'LEADER_TEAM_NAME', 'PERSONAL_SCHEDULE', 'TEAM_EVENTS', 'TEAM_EVENTS_SCHEDULE', 'LEADER_TEAM_EVENTS', 'LEADER_TEAM_EVENTS_SCHEDULE', 'ALL_EVENTS', 'ALL_EVENTS_SCHEDULE', 'CONSENSUS_LINK', 'ICAL_LINK', 'ABSENCE_LINK') as $key) {
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
	global $access, $BASE_PATH, $MAIL_TRANSPORT;

	if($access['rota']['MAX'] < 4) return FALSE;

	$from_email = format_userinput($_POST['sender'], 'email');
	//Get logged in person and his email addresses
	$p = ko_get_logged_in_person();
	$emails = ko_get_leute_emails($p['id']);
	if(sizeof($emails) > 0 && in_array($from_email, $emails)) {
		$from_name = $p['vorname'] || $p['nachname'] ? $p['vorname'].' '.$p['nachname'] : $p['firm'];
	} else {
		//If no valid personal address was selected fall back to info email
		$from_email = ko_get_setting('info_email');
		$from_name = ko_get_setting('info_name');
	}
	if(!check_email($from_email)) return FALSE;
	$from = array($from_email => $from_name);

	//Get file and filetype from submitted form
	$filetype = $_POST['filetype'];
	$send_files = array();
	$fileNames = array_filter(explode('@|,|@', $_POST['rota_filesend_files']), function($e){return $e?true:false;});
	foreach ($fileNames as $file) {
		$send_files[$BASE_PATH.'my_images/temp/'.$file] = substr($file, 37);
	}

	$restrict_consensus_link = ko_get_setting('consensus_restrict_link') == 1 ? TRUE : FALSE;
	$restrict_to_teams = NULL;
	//Get recipients according to recipients mode
	if(in_array($_POST['recipients'], array('schedulled', 'selectedschedulled', 'manualschedulled'))) {
		if(substr($_POST['filetype'], 0, 5) == 'event') {
			[$m, $eventid] = explode(':', $filetype);
		} else {
			$events = ko_rota_get_events('','',true);
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
			if($access['rota']['ALL'] < 4 && $access['rota'][$teamID] < 4) continue;
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

	$restricted_leute_ids = ko_apply_leute_information_lock();
	if (!empty($restricted_leute_ids)) {
		foreach($restricted_leute_ids AS $restricted_leute_id) {
			unset($recipients[$restricted_leute_id]);
		}
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

	$help = ko_get_help('rota', 'ical_links');
	$content = '';
	$content .= '<div class="ical-links">';
	$content .= '<h2>'.getLL('rota_ical_links_title').($help['show'] ? '&nbsp;&nbsp;'.$help['link'] : '').'</h2>';
	$content .= '<p>'.getLL('rota_ical_links_description').'</p>';

	$content .= '<h4>'.getLL('rota_ical_links_title_teams').'</h4>';
	$content .= '<ul id="rota-teams-ical-links">';
	$teams = ko_rota_get_all_teams();
	foreach($teams AS $team) {
		$link = $BASE_URL . 'rotaical/index.php?team='.$team['id'].'x'.strtolower(substr(md5($team['id'] . KOOL_ENCRYPTION_KEY . 'rotaIcal' . KOOL_ENCRYPTION_KEY . 'rotaIcal' . KOOL_ENCRYPTION_KEY . $team['id']), 0, 10));
		$content .= "<li>" . ko_get_ical_link($link, $team['name'], "") . " ".($team['rotatype'] == "day" ? "<sup>(AT)</sup>" : "")."</li>";
	}

	$content .= "</ul>";

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




function ko_rota_get_member_roles_in_team($member, $team) {
	$roleIds = array();
	foreach(explode(',', $team['group_id']) as $gid) {
		foreach(explode(',', $member['groups']) as $mgid) {
			if(FALSE !== strpos($mgid, $gid)) {
				$roleIds[] = substr($mgid, -6);
			}
		}
	}
	$roleIds = array_unique($roleIds);

	ko_get_grouproles($roles);
	$r = array();
	foreach($roleIds as $rid) {
		$r[$rid] = $roles[$rid]['name'];
	}
	return $r;
}//ko_rota_get_member_role_in_team()

/**
 * @param int    $personId
 * @param int    $teamId
 * @param string $mode : all (default) or team to only show chart for given teamId
 * @return string $chart as SVG
 * @throws Exception
 */
function ko_rota_get_participation_chart($personId, $teamId, $mode='all') {
	$getParticipationMode = ko_get_userpref($_SESSION['ses_userid'], 'rota_show_participation');
	$start = date('Y-m-d H:i:s', strtotime('-1 year'));
	$end = $getParticipationMode == 'all' ? date('Y-m-d H:i:s',strtotime('+1 year')) : date('Y-m-d H:i:s');

	$team = db_select_data('ko_rota_teams',"WHERE id='".$teamId."'",'name','','',true);

	$events = ko_rota_get_scheduled_events($personId,$start,$end);
	$now = time();
	if($mode == 'team') {
		$svgHeight = 90;
		$chartHeight = 150;
		$count = $months = $total = ['team' => []];
	} else {
		$svgHeight = 180;
		$chartHeight = 300;
		$count = $months = $total = ['all' => [],'team' => []];
	}
	$bwidth = 7;
	foreach($events as $event) {
		$t = (strtotime($event['startdatum'])+strtotime($event['enddatum']))/2;
		$day = intval(($t-$now)/86400);
		$month = date('Y-m',$t);
		$inTeam = in_array($teamId, $event['in_teams']);
		for($i = -$bwidth+1; $i <= $bwidth-1; $i++) {
			$q = $i/$bwidth;
			$q = $q*$q;
			$d = ($q*$q-$q*2+1)/$bwidth;
			if($mode == 'all') $count['all'][$day+$i] += $d;
			if($inTeam) {
				$count['team'][$day+$i] += $d;
			}
		}
		if($mode == 'all') $months['all'][$month]++;
		if($mode == 'all') $total['all'][intval($t >= $now)]++;
		if($inTeam) {
			$months['team'][$month]++;
			$total['team'][intval($t >= $now)]++;
		}

	}

	$dtstart = new \DateTime($start);
	$dtstart->modify('first day of this month');
	$dtend = new \DateTime($end);
	$oneMonth = new \DateInterval('P1M');
	$xmax = $getParticipationMode == 'all' ? 365 : 0;

	$colors = ['all' => ['#ffb266','#ff8000','#663300'], 'team' => ['#66b2ff','#0080ff','#003366']];

	$yOffset = ['all' => 260,'team' => 130];

	$labels = ['all' => getLL('rota_consensus_all_teams'),'team' => $team['name']];

	$chart = '<svg xmlns="http://www.w3.org/2000/svg" width="'.(($xmax+365)*0.6).'" height="'.$svgHeight.'" viewBox="-365 0 '.($xmax+365).' '.$chartHeight.'">
	<g id="grid">';
	for($dt = clone $dtstart; $dt < $dtend; $dt->add($oneMonth)) {
		$x = ($dt->getTimestamp()-$now)/86400;
		$chart .= '<line x1="'.$x.'" x2="'.$x.'" y1="20" y2="280" stroke="#808080" stroke-width="1px" vector-effect="non-scaling-stroke" shape-rendering="crispEdges" />';
		if(substr($_SESSION['rota_timestart'], 0, 7) == $dt->format('Y-m')) {
			$chart .= '<line x1="'.$x.'" x2="'.$x.'" y1="20" y2="280" stroke="#4675fc" stroke-width="2px" vector-effect="non-scaling-stroke" shape-rendering="crispEdges" />';
		}
		$chart .= '<text x="'.($x+15).'" y="16" fill="#dddddd" text-anchor="middle" style="font-size:15; font-family:sans-serif;">'.utf8_encode(str_replace('ä', 'a', strftime('%b',$dt->getTimestamp()))).'</text>';
	}
	if($getParticipationMode == 'all') {
		$chart .= '<line x1="0" x2="0" y1="20" y2="280" stroke="#ff0000" stroke-width="1px" vector-effect="non-scaling-stroke" shape-rendering="crispEdges" />';
	}
	foreach($months as $key => $num) {
		foreach($num as $month => $c) {
			$x = (strtotime($month.'-1')-$now)/86400;
			$chart .= '<text x="'.($x+16).'" y="'.($yOffset[$key]-95).'" fill="'.$colors[$key][0].'" text-anchor="middle" style="font-size:14; font-family:sans-serif;">'.$c.'</text>';
		}
	}
	$chart .= '</g>';
	foreach($count as $key => $days) {
		$chart .= '<rect x="-365" y="'.$yOffset[$key].'" width="'.($xmax+365).'" height="20" fill="'.$colors[$key][2].'" />';
		$chart .= '<text x="'.($getParticipationMode == 'all' ? 0 : -360).'" y="'.($yOffset[$key]+15).'" fill="#ffffff" style="font-size:14; font-family:sans-serif;" text-anchor="'.($getParticipationMode == 'all' ? 'middle' : 'start').'">'.utf8_encode(htmlentities($labels[$key], ENT_QUOTES|ENT_XML1,"ISO-8859-1",false)).'</text>';
		$chart .= '<text x="'.($getParticipationMode == 'all' ? -360 : -5).'" y="'.($yOffset[$key]+15).'" fill="#ffffff" style="font-size:14; font-family:sans-serif;" text-anchor="'.($getParticipationMode == 'all' ? 'start' : 'end').'">'.$total[$key][0].'</text>';
		if($getParticipationMode == 'all') {
			$chart .= '<text x="360" y="'.($yOffset[$key]+15).'" fill="#ffffff" style="font-size:14; font-family:sans-serif;" text-anchor="end">'.$total[$key][1].'</text>';
		}
		$chart .= '<path d="M -365 '.$yOffset[$key];
		for($x = -365; $x <= $xmax; $x++) {
			$chart .= ' L '.$x.' '.($yOffset[$key]-log($days[$x]+1)*100);
		}
		$chart .= ' L '.$xmax.' '.$yOffset[$key].' z" stroke="'.$colors[$key][1].'" fill="'.$colors[$key][1].'" stroke-width="0" />';
	}
	$chart .= '</svg>';

	return $chart;
}//ko_rota_get_participation_chart();



function encodeFreeTextName($freeTextName) {
	$freeTextName = str_replace(" ", "--1--", $freeTextName);
	$freeTextName = str_replace("_", "--2--", $freeTextName);
	return ($freeTextName);
}

function decodeFreeTextName($freeTextName) {
	$freeTextName = str_replace("--1--", " ", $freeTextName);
	$freeTextName = str_replace("--2--", "_", $freeTextName);
	return ($freeTextName);
}
