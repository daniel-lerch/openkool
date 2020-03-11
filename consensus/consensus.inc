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


function ko_consensus_list_consensus() {
	global $KOTA, $smarty, $personId, $start, $span, $team_ids, $DATETIME, $BASE_PATH, $SMARTY_RENDER_TEMPLATE;

	require_once($BASE_PATH . 'inc/kotafcn.php');

	$teams = ko_rota_get_teams($personId);
	$consensus_comments = ko_consensus_get_comments_grouped_by_person($personId);
	// sort out teams which don't support consensus
	$noConsensusAllowed = [];
	foreach ($teams as $k => $team) {
		if (!empty($consensus_comments[$k])) {
			$teams[$k]['consensus_comment'] = $consensus_comments[$k];
		}

		if ($team_ids !== NULL && !in_array($team['id'], $team_ids)) {
			unset($teams[$k]);
		} else {
			if ($team['allow_consensus'] == 0) {
				$noConsensusAllowed[] = $team['name'];
				unset($teams[$k]);
			}
		}
	}

	// prepare where statement for finding the corresponding events
	$egIds = [];
	foreach ($teams as $team_id => $team) {
		$teamEgIds = explode(',', $team['eg_id']);
		$teams[$team_id]['eg_ids'] = $teamEgIds;
		foreach ($teamEgIds as $egId) {
			if ($egId != '') $egIds[] = $egId;
		}
	}

	$egIds = array_unique($egIds);
	if (trim(implode(' ', $egIds)) == '') {
		$where = '1=2';
	} else {
		$where = 'eventgruppen_id in (' . implode(',', $egIds) . ')';
	}

	$personName = ko_get_person_name($personId);

	// handle event-type teams
	$end = ko_consensus_get_end($start, $span);
	// get event group names
	$eventGroups = db_select_data("ko_eventgruppen", "where " . str_replace('eventgruppen_id', 'id', $where), 'id, name');
	// select events, only those in the future and according to timespan
	$events = db_select_data('ko_event', 'where ' . $where . " and startdatum <= '" . $end . "' and enddatum >= '" . $start . "' and CONCAT(CONCAT(`startdatum`,' '),`startzeit`) > NOW() and `rota` = 1", '*', 'order by startdatum asc, startzeit asc', '', FALSE, TRUE);
	// process events
	foreach ($events as $k => $event) {
		$events[$k]['terms'] = $event['terms'] = ko_taxonomy_get_terms_by_node($event['id'], "ko_event");

		$found = FALSE;
		foreach ($teams as $team) {
			if (in_array($event['eventgruppen_id'], $team['eg_ids']) && trim($team['rotatype']) == 'event' && !ko_rota_is_scheduling_disabled($event['id'], $team['id'])) {
				$found = $team['id'];
			}
		}
		if (!$found) {
			unset($events[$k]);
		} else {
			$processedEvent = $event;
			kota_process_data('ko_event', $processedEvent, 'list');
			$events[$k]['_processed'] = $processedEvent;
			$events[$k]['startingDate'] = strftime('%a. ' . $DATETIME['dmy'], strtotime($event['startdatum'] . ' ' . $event['startzeit']));
			$events[$k]['startingTime'] = ($event['startzeit'] == '00:00:00' && $event['endzeit'] == '00:00:00') ? getLL('time_all_day') : strftime('%H:%M', strtotime($event['startdatum'] . ' ' . $event['startzeit']));
			$eventStatus = ko_rota_get_status($event['id']);
			$events[$k]['status'] = $eventStatus;
			$events[$k]['_processed']['eventgruppen_name'] = $eventGroups[$event['eventgruppen_id']]['name'];
		}
	}
	array_merge($events);

	$weeks = ko_consensus_get_answers_for_days($teams, $start, $end, $personId);

	// remove teams which are never used from list
	foreach ($teams as $k => $team) {
		$found = FALSE;
		foreach ($events as $event) {
			if (in_array($event['eventgruppen_id'], $team['eg_ids']) && trim($team['rotatype']) == 'event') {
				$found = TRUE;
			}
		}
		if (!$found) {
			unset($teams[$k]);
		}
	}
	array_merge($teams);

	$data = [];
	$descriptionSectionNeeded = FALSE;
	$event_fields = explode(',', ko_get_setting('consensus_eventfields'));

	// fill in actual table data
	foreach ($events as $i => $event) {
		$event_header = '<td>
					<div class="' . ($event['status'] == 1 ? "open" : "closed") . '">
						' . ($event['status'] == 2 ? "<div class='closed_overlay'><i class='fa fa-lock'></i></div>" : "") . '
						' . '<div class="event_date">' . $event['startingDate'] . ' ' . $event['startingTime'] . '</div> 
						<div class="event_title">' . $event['_processed']['eventgruppen_name'] . '</div>';

		require_once($BASE_PATH . 'daten/inc/daten.inc');
		$absences = array_pop(ko_daten_get_absence_by_leute_id($personId, $event['startdatum']));
		if (!empty($absences)) {
			$event_header .= '<div class="status_absent" title="' .
				prettyDateRange($absences['from_date'], $absences['to_date']) .
				(!empty($absences['description']) ? ": " . $absences['description'] : "") . '">' .
				getLL("kota_ko_event_absence_type_" . $absences['type']) . ' <i class="fa fa-exclamation-triangle"></i></div>';
		}

		foreach ($event_fields AS $field) {
			if (!empty($event[$field])) {
				$event_header .= '<div class="event_comment">
					<strong>' . getLL('kota_ko_event_' . $field) . ':</strong> ' . $event['_processed'][$field] . '
					</div >';
			}
		}

		$event_header .= '</div></td>';
		$data[$i][0] = [
			'type' => 'title',
			'content' => $event_header,
			'event_id' => $event['id'],
			'event_groupid' => $event['eventgruppen_id'],
			'consensus_status' => '',
			'event_status' => $event['status'] == 1 ? "open" : "closed",
		];

		foreach ($teams as $j => $team) {
			$entry = [];

			if (!ko_rota_is_scheduling_disabled($event['id'], $team['id']) &&
				in_array($event['eventgruppen_id'], $team['eg_ids']) && trim($team['rotatype']) == 'event'
			) {
				$entry['type'] = 'entry';
				$entry['content'] = ko_consensus_get_cell_contents($event['id'], $team['id'], $personId);
			} else {
				$entry['type'] = 'empty';
				$entry['content'] = '<td></td><td></td>';
			}
			$data[$i][$j + 1] = $entry;

			if ($entry['type'] != "empty") {
				$data[$i][0]['consensus_status'] .= ko_consensus_get_status($event, $team, $teams, $personId);
			}

			if (trim($team['consensus_description']) != '') {
				$descriptionSectionNeeded = TRUE;
			}
		}

		$where = "WHERE event_id = '" . $event['id'] . "' AND team_id IN(" . implode(",", array_keys($teams)) . ") AND answer = 3";
		$yes_counter = count(db_select_data("ko_rota_consensus", $where));
		if ($yes_counter == 0) {
			$data[$i][0]['consensus_status'] .= 5;
		}
	}

	$status_list = [
		0 => getLL('rota_consensus_filter_all'),
		2 => getLL('rota_consensus_filter_todo'),
		1 => getLL('rota_consensus_filter_answered'),
		3 => getLL('rota_consensus_filter_scheduled'),
		4 => getLL('rota_consensus_filter_nobody'),
		5 => getLL('rota_consensus_filter_noyes'),
	];

	$filter = "
<form class=\"form-inline\">
		 <div class=\"form-group form-group-sm\">
	<label for='sel_consensus_status'>" . getLL('filter_group_status') . ":</label>
		<select class='form-control input-xs' name='sel_consensus_status'>";

	foreach ($status_list AS $status_key => $status) {
		$filter .= "<option value='" . $status_key . "'>" . $status . "</option>";
	}
	$filter .= "</select>
</div>
	";

	$filter .= " &nbsp;
			 <div class=\"form-group form-group-sm\">
		<label for='sel_consensus_group'>" . getLL('daten_eventgroup') . ":</label>
		<select class='form-control input-xs' name='sel_consensus_group'>
			<option value='0'>".getLL('all')."</option>";
			foreach($eventGroups as $eventGroup) {
				$filter.= "<option value='".$eventGroup['id']."'>" . $eventGroup['name'] . "</option>";
			}

	$filter.="</select></div></form>";

	// display ongoing calendar
	if(ko_get_setting("consensus_ongoing_cal")) {
		$ongoing_calendar['urls']['today'] = "?x=".format_userinput($_GET['x'], "text")."&ongoing_start=" . date("Y-m-d", time());

		$new_start = new DateTime($start);
		switch(substr($span,-1)) {
			case "d": $suffix = "ay"; break;
			case "w": $suffix = "eek"; break;
			case "m": $suffix = "onth"; break;
			case "y": $suffix = "ear"; break;
			default: $span = "1m"; $suffix = "onth"; break;
		}

		$new_start->modify("-" . $span.$suffix);
		$ongoing_calendar['urls']['left'] = "?x=".format_userinput($_GET['x'], "text")."&ongoing_start=" . $new_start->format("Y-m-d");
		$new_start->modify("+" . $span.$suffix)->modify("+" . $span.$suffix);
		$ongoing_calendar['urls']['right'] = "?x=".format_userinput($_GET['x'], "text")."&ongoing_start=" . $new_start->format("Y-m-d");
		$ongoing_calendar['urls']['cal'] = "?x=".format_userinput($_GET['x'], "text")."&ongoing_start=";
		$smarty->assign('tpl_ongoing_cal', $ongoing_calendar);
	}

	// General description
	$generalDescription = ko_get_setting('consensus_description');
	if (trim($generalDescription) != '') {
		$descriptionSectionNeeded = TRUE;
	}

	$smarty->assign('tpl_description_needed', $descriptionSectionNeeded);
	$smarty->assign('tpl_general_description', ko_get_setting('consensus_description'));
	$smarty->assign('tpl_submit_value', getLL('save'));
	$smarty->assign('tpl_action', 'submit_consensus_entries');
	$smarty->assign('tpl_timespan', ko_rota_timespan_title($start, $span));
	$smarty->assign('tpl_person_name', $personName);
	$smarty->assign('tpl_filter', $filter);
	$smarty->assign('tpl_data', $data);
	$smarty->assign('tpl_teams', $teams);
	$smarty->assign('tpl_events', $events);
	$smarty->assign('tpl_language', $_SESSION['lang']);
	$smarty->assign('tpl_weeks', $weeks);

	if (sizeof($data) == 0) {
		$smarty->assign('tpl_consensus_message_no_result', getLL('ko_consensus_no_events'));
	}
	else if (sizeof($noConsensusAllowed) > 0) {
		$smarty->assign('tpl_consensus_message_not_allowed', sprintf(getLL('ko_consensus_disabled_teams'), implode(', ', $noConsensusAllowed)));
	}

	$smarty->display('ko_consensus.tpl');
} // ko_consensus_list_consensus ()


function ko_consensus_get_answers_for_days($teams, $start, $end, $personId) {
	global $BASE_PATH;
	require_once($BASE_PATH . 'daten/inc/daten.inc');

	$start_monday = date_find_last_monday($start);
	$end_sunday = date_find_next_sunday($end);
	$days = ko_rota_get_days($teams, $start_monday, $end_sunday);
	$all_teams = ko_rota_get_all_teams();

	$weeks = [];
	foreach($days AS $date => $day) {
		if($days[$date]['month'] == 12 && $days[$date]['num'] == 1) {
			$week = ($days[$date]['year']+1) . "-" . $days[$date]['num'];
		} else {
			$week = $days[$date]['year']  . "-" . $days[$date]['num'];
		}

		$weeks[$week]['label'] = date("d.m.Y", strtotime(date_find_last_monday($date)));
		$weeks[$week]['label'].= " - " . date("d.m.Y", strtotime(date_find_next_sunday($date)));

		if(empty($weeks[$week]['days'])) {
			$weeks[$week]['days'][1] = sql2datum($date);
		} else {
			$weeks[$week]['days'][] = sql2datum($date);
		}

		foreach($day['teams'] AS $team) {
			$weeks[$week]['teams'][$team]['details'] = $all_teams[$team];
			$weeks[$week]['teams'][$team]['days'][$date] = ko_rota_get_team_members($team);

			$consensus_status = ko_consensus_get_status($date, $all_teams[$team], [$team => $team], $personId);
			$where = "WHERE event_id = '" . $date . "' AND team_id IN(" . implode(",", array_keys($teams)) . ") AND answer = 3";
			$yes_counter = count(db_select_data("ko_rota_consensus", $where));

			if(stristr($all_teams[$team]['days_range'], date("N", strtotime($date))) === FALSE) {
				$consensus_status = 6;
			} else if ($consensus_status != 3 && $yes_counter == 0) {
				$consensus_status = 5;
			}

			$weeks[$week]['teams'][$team]['filter_status'].= $consensus_status;
		}
	}

	$where = "WHERE event_id IN ('" . implode("','", array_column($days,'id')) . "')";
	$consensus_answers = db_select_data("ko_rota_consensus", $where);
	foreach($consensus_answers AS $answer) {
		$month = date("m",strtotime($answer['event_id']));
		$week = date("W",strtotime($answer['event_id']));
		if($month == 12 && $week == 1) {
			$week_id = (date("Y", strtotime($answer['event_id']))+1)  . "-" . $week;
		} else {
			$week_id = date("Y", strtotime($answer['event_id']))  . "-" . $week;
		}

		$weeks[$week_id]['teams'][$answer['team_id']]['days'][$answer['event_id']]['people'][$answer['person_id']]['answer'] = $answer['answer'];
	}

	foreach($weeks AS $week_id => $week) {
		foreach($week['teams'] AS $team_id => $team) {
			$html = ko_consensus_get_week_code($team, $team_id, $week_id, $personId);
			$weeks[$week_id]['teams'][$team_id]['input'].= $html;
		}
	}

	return $weeks;
}



function ko_consensus_get_week_code($team, $team_id, $week_id, $person_id) {
	global $ko_path;
	$counter = 1;

	$holiday_eg = ko_get_setting("holidays_eg");
	if(!empty($holiday_eg)) {
		$where = "WHERE eventgruppen_id = '" . $holiday_eg . "' AND (
			startdatum BETWEEN CAST('".array_key_first($team['days'])."' AS DATE) AND CAST('".array_key_last($team['days'])."' AS DATE) OR
			enddatum BETWEEN CAST('".array_key_first($team['days'])."' AS DATE) AND CAST('".array_key_last($team['days'])."' AS DATE))";
		$holidays = db_select_data("ko_event", $where);
	}

	foreach($team['days'] AS $date => $day) {
		$tooltips[$counter] = "<strong>" . sql2datum($date) . "</strong><br>";
		$absences_flag[$counter] = FALSE;

		require_once($ko_path . "daten/inc/daten.inc");
		$absences = ko_daten_get_absence_by_leute_id($person_id, $date);
		if (!empty($absences)) {
			$absence_text = [];
			foreach($absences AS $absence) {
				$absence_text[].= prettyDateRange($absence['from_date'], $absence['to_date']) . ": " .
					getLL("kota_ko_event_absence_type_" . $absence['type']) .
					(!empty($absence['description']) ? " (" . $absence['description'] . ")" : "");
			}

			if(!empty($absence_text)) {
				$tooltips[$counter] .= "<h4>".getLL("kota_ko_leute_absence")."</h4><p>" . implode("<br />", $absence_text) . "</p>";
			}

			$absences_flag[$counter] = TRUE;
		}

		$holiday_id = array_search($date, array_column($holidays, "startdatum", "id"));
		if($holiday_id) $tooltips[$counter] .= "<br>&nbsp;<p>" . getLL("holiday") . ": " . $holidays[$holiday_id]['title'] . "</p>";
		$counter++;
	}


	if($team['details']['consensus_disable_maybe_option'] == 1) {
		$consensus_types = ["yes", "no"];
	} else {
		$consensus_types = ["yes", "maybe", "no"];
	}
	$html = "";
	foreach($consensus_types AS $type) {
		$html.= '<div class="consensus_range_wrapper"><ol class="btn-group daysrange consensus_range ui-selectable consensus_type_'.$type.'" data-toggle="buttons" data-team="'.$team_id.'" data-week="'. $week_id .'" data-type="'.$type.'">';

		$counter = 1;
		foreach($team['days'] AS $date => $day) {
			switch($day['people'][$person_id]["answer"]) {
				case 3: $answer = "yes"; break;
				case 2: $answer = "maybe"; break;
				case 1: $answer = "no"; break;
				default: $answer = "";
			}
			$additional_classes = [];
			if(!stristr($team['details']['days_range'], (string)$counter)) {
				$additional_classes[] = "disabled";
			}

			if(ko_rota_person_is_scheduled($team_id, $date, $person_id)) {
				$additional_classes[] = "scheduled";
				if($type == "yes") {
					$tooltips[$counter].= "<p>" . getLL("ko_rota_consensus_already_scheduled") . "</p>";
				}
			}

			if(empty($additional_classes) && $answer == $type) {
				$additional_classes[] = "ui-selected";
			}

			$html .= '<li id="'.$team_id.'_'.$week_id.'_'.$type.'_'.$counter.'" class="ui-widget-content btn btn-default rota-tooltip '.(!empty($absences_flag[$counter]) ? "absence" : "").' btn-xs ' . implode(" ", $additional_classes) .'" data-day="'.$counter.'"  data-tooltip-code="'.$tooltips[$counter].'">'. getLL($type).'</li>';
			$counter++;
		}

		$html.='</ol></div>';
	}

	return $html;
}

/**
 * 0 all
 * 1 answered
 * 2 to do
 * 3 scheduled
 * 4 nobody
 * 5 noyes
 *
 * @param array|string $event array or date for amtstag
 * @param array $team
 * @param array $teams ids
 * @param int $personId
 * @return string
 */
function ko_consensus_get_status($event, $team, $teams, $personId) {
	if(isset($event['id'])) {
		$event_id = $event['id'];
	} else {
		$event_id = $event;
	}

	if ($event['status'] === 2) {
		$status = 1; // answered
	} else if (ko_rota_person_is_scheduled($team['id'], $event_id, $personId)) {
		$status = 3; // scheduled
	} else if (!in_array(ko_consensus_get_answers('person', $event_id, $team['id'], $personId), [1, 2, 3])) {
		$status = 2; // to-do

		$zwhere = "AND event_id = '" . $event_id . "' AND team_id IN(" . implode(",", array_keys($teams)) . ")";
		$event_answers_count = db_get_count("ko_rota_consensus", "event_id", $zwhere);

		if ($event_answers_count == 0) {
			$status = 4; // nobody
		}
	} else {
		$status = 1; // answered
	}

	return $status;
}

/**
 * return all teams where $id is a member / leader
 *
 * @param int $id the id of the person or group
 * @return array the requested teams
 */
function ko_rota_get_teams($id) {
	$allTeams = db_select_data('ko_rota_teams', 'where 1=1');
	$teams = [];
	foreach ($allTeams as $k => $team) {
		$members = ko_rota_get_team_members($team, TRUE);
		if (array_key_exists($id, $members['people'])) {
			$teams[$k] = $team;
		}
	}
	return $teams;
}


function ko_consensus_update_cell($eventId, $teamId, $personId, $answer, $force = FALSE) {
	if ($force == FALSE && ko_rota_get_status($eventId) != 1) {
		return 'event_closed';
	}
	$team = db_select_data('ko_rota_teams', "WHERE `id` = '{$teamId}'", '*', '', '', TRUE);
	if ($force == FALSE && $team['consensus_disable_maybe_option'] && $answer == 'maybe') {
		return 'maybe_option_disabled';
	}
	$maxPromises = $team['consensus_max_promises'];
	if ($force == FALSE && $maxPromises > 0 && $answer == 'yes') {
		$promises = db_get_count('ko_rota_consensus', 'person_id', "AND `team_id` = '{$teamId}' AND `event_id` = '{$eventId}' AND `person_id` <> '{$personId}' AND `answer` = 3");
		if ($promises >= $maxPromises) {
			return 'max_promises_reached';
		}
	}

	$am = ['no_answer' => 0, 'no' => 1, 'maybe' => 2, 'yes' => 3];

	$entry = db_select_data('ko_rota_consensus', 'where event_id = \'' . $eventId . '\' and team_id = ' . $teamId . ' and person_id = ' . $personId, 'team_id', '', '', TRUE, TRUE);
	if ($entry === NULL) {
		db_insert_data('ko_rota_consensus', ['event_id' => $eventId, 'team_id' => $teamId, 'person_id' => $personId, 'answer' => $am[$answer]]);
	} else {
		db_update_data('ko_rota_consensus', 'where event_id = \'' . $eventId . '\' and team_id = ' . $teamId . ' and person_id = ' . $personId, ['answer' => $am[$answer]]);
	}

	return 0;
} // ko_consensus_update_cell ()


function ko_consensus_get_cell_contents($eventId, $teamId, $personId) {
	$inner = ko_consensus_get_cell_contents_inner($eventId, $teamId, $personId);
	$person = $inner['person'];
	$team = $inner['team'];

	$htmlPerson = '<td class="left_cell" id="container_person_' . $eventId . '_' . $teamId . '_' . $personId . '" >';
	$htmlPerson .= $person;
	$htmlPerson .= '</td>';

	$htmlTeam = '<td class="right_cell" id="container_team_' . $eventId . '_' . $teamId . '_' . $personId . '" >';
	$htmlTeam .= $team;
	$htmlTeam .= '</td>';

	return ['person' => $htmlPerson, 'team' => $htmlTeam];
} // ko_consensus_get_cell_contents()


/**
 * @param string $mode : either 'person' or 'team' or 'group'
 * @param        $eventId
 * @param        $teamId
 * @param string $id
 * @return array|int: $mode == 'person' ? 0, 1, 2, 3 : array(no_answer s, no s, maybe s, yes s)
 */
function ko_consensus_get_answers($mode, $eventId, $teamId, $id = '') {
	static $answerCache = [];
	if ($mode != 'group') {
		if (!isset($answerCache[$teamId][$eventId])) {
			$answerCache[$teamId][$eventId] = db_select_data('ko_rota_consensus', ' where team_id = ' . $teamId . ' and event_id = \'' . $eventId . '\'', 'person_id, answer', 'order by person_id asc') ?: [];
		}
	}
	if ($mode == 'person') {
		return isset($answerCache[$teamId][$eventId][$id]) ? $answerCache[$teamId][$eventId][$id]['answer'] : 0;
	} else if ($mode == 'team') {
		$teamAnswers = [0, 0, 0, 0];
		foreach ($answerCache[$teamId][$eventId] as $answer) {
			$teamAnswers[$answer['answer']]++;
		}
		return $teamAnswers;
	} else if ($mode == 'group') {
		$role = ko_get_setting('rota_teamrole');
		$roleString = (trim($role) == '' ? '' : ':r' . $role);
		ko_get_leute($persons, "and `groups` regexp 'g" . trim($id) . '(:g[0-9]{6})*' . $roleString . "'");
		$groupAnswers = [0, 0, 0, 0];
		foreach ($persons as $person) {
			$groupAnswers[ko_consensus_get_answers('person', $eventId, $teamId, $person['id'])]++;
		}
		return $groupAnswers;
	}

} // ko_consensus_get_answers()


function ko_consensus_get_cell_contents_inner($eventId, $teamId, $personId) {
	$status = ko_rota_get_status($eventId);
	// status map
	$sm = [1 => 'open', 2 => 'closed'];

	// Answer map
	$am = ['' => 'no_answer', 0 => 'no_answer', 1 => 'no', 2 => 'maybe', 3 => 'yes'];
	$amLL = ['' => '-', 0 => getLL('no'), 1 => '(' . getLL('yes') . ')', 2 => getLL('yes')];

	$answerPerson = ko_consensus_get_answers('person', $eventId, $teamId, $personId);
	$answersTeam = ko_consensus_get_answers('team', $eventId, $teamId);

	$isScheduled = ko_rota_person_is_scheduled($teamId, $eventId, $personId);
	$isLocked = $status == 2;

	$team = db_select_data('ko_rota_teams', "WHERE `id` = '{$teamId}'", '*', '', '', TRUE);
	$disableMaybeOption = $team['consensus_disable_maybe_option'];

	$editable = $isScheduled || $isLocked ? ' not-editable' : ' editable';
	$editable1 = $editable;
	$editable2 = $editable;
	$editable3 = $editable;

	$tt1 = '';
	$maxPromises = $team['consensus_max_promises'];
	if ($maxPromises > 0 && $editable == ' editable' && $answerPerson != 3) {
		$promises = db_get_count('ko_rota_consensus', 'person_id', "AND `team_id` = '{$teamId}' AND `event_id` = '{$eventId}' AND `answer` = 3");
		if ($promises >= $maxPromises) {
			$editable1 = ' not-editable';
			$tt1 = $tooltip = ' onmouseover="tooltip.show(\'' . getLL('ko_consensus_max_promises_reached_warning') . '\');" onmouseout="tooltip.hide();"';
		}
	}

	$tooltip = '';
	if ($isLocked) {
		$tooltip = ' onmouseover="tooltip.show(\'' . getLL('ko_consensus_event_closed_warning') . '\');" onmouseout="tooltip.hide();"';
	} else if ($isScheduled) {
		$tooltip = ' onmouseover="tooltip.show(\'' . getLL('ko_consensus_already_scheduled_warning') . '\');" onmouseout="tooltip.hide();"';
	}

	$htmlPerson = '<div id="person_' . $eventId . '_' . $teamId . '_' . $personId . '" class="person ' . $am[$answerPerson] . ' ' . $sm[$status] . '"' . $tooltip . '>';
	$htmlPerson .= '<button id="' . $eventId . '_' . $teamId . '_' . $personId . '_yes" class="btn btn-default button yes ' . ($am[$answerPerson] == 'yes' ? ' active' : '') . $editable1 . '" ' . $tt1 . '>' . $amLL[2] . '</button>';
	if (!$disableMaybeOption) {
		$htmlPerson .= '<button id="' . $eventId . '_' . $teamId . '_' . $personId . '_maybe" class="btn btn-default button maybe ' . ($am[$answerPerson] == 'maybe' ? ' active' : '') . $editable2 . '">' . $amLL[1] . '</button>';
	}
	$htmlPerson .= '<button id="' . $eventId . '_' . $teamId . '_' . $personId . '_no" class="btn btn-default button no' . ($am[$answerPerson] == 'no' ? ' active' : '') . $editable3 . '">' . $amLL[0] . '</button>';
	$htmlPerson .= '</div>';

	$display_participation = ko_get_setting("consensus_display_participation");
	$htmlTeam = '<ul class="team">';


	//Don't show anything about given answers
	if ($display_participation == 2) {
		$htmlTeam .= "";
	}
	//Show names
	else if ($display_participation == 1) {
		//First try to find scheduled person
		$scheduled = ko_rota_get_schedule_by_event_team($eventId, $teamId);
		//If scheduled then output these persons
		if($scheduled['schedule'] != '') {
			$assigned = ko_rota_schedulled_text($scheduled['schedule'], 4);

			$htmlAssigned = '<div class="assigned"><h5>'.getLL('rota_consensus_filter_scheduled').':</h5>';
			foreach($assigned AS $assignee) {
				$htmlAssigned .= '<div class="btn btn-sm btn-primary rota-entry assigned-person">'.$assignee.'</div>';
			}
			$htmlAssigned .= '</div>';
			$htmlTeam .= $htmlAssigned;
		}
		//Otherwise show number of answers and the names
		else {
			$where = "WHERE team_id = '" . $teamId . "' AND event_id = '" . $eventId . "'";
			$join = "LEFT JOIN ko_leute ON ko_leute.id = ko_rota_consensus.person_id";
			$participants = db_select_data("ko_rota_consensus " . $join, $where, "*", "ORDER BY ko_leute.vorname ASC");
			$participants_tooltips = [];
			foreach ($participants AS $participant) {
				$participants_tooltips[$participant['answer']][] = $participant['vorname'] . " " . $participant['nachname'];
			}

			for ($i = 3; $i > 0; $i--) {
				if ($i != 2 || !$disableMaybeOption) {
					$participants_tooltip = implode("<br>", $participants_tooltips[$i]);
					if (!empty($participants_tooltip)) {
						$participants_tooltip = 'onmouseover="tooltip.show(\'' . $participants_tooltip . '\');" onmouseout="tooltip.hide();"';
					}
					$htmlTeam .= '<li ' . $participants_tooltip . '><p class="team_members ' . $am[$i] . '" data-members="' . $answersTeam[$i] . '">' . ($answersTeam[$i] > 0 ? $answersTeam[$i] : '&nbsp;') . '</p></li>';
				}
			}
		}
	}
	//Only show numbers of answers
	else {
		for ($i = 3; $i > 0; $i--) {
			if ($i != 2 || !$disableMaybeOption) {
				$htmlTeam .= '<li><p class="team_members ' . $am[$i] . '" data-members="' . $answersTeam[$i] . '">' . ($answersTeam[$i] > 0 ? $answersTeam[$i] : '&nbsp;') . '</p></li>';
			}
		}
	}

	$htmlTeam .= '</ul>';

	return ['person' => $htmlPerson, 'team' => $htmlTeam];
} // ko_consensus_get_cell_contents_inner ()


function ko_consensus_get_end($start, $ts) {
	switch (substr($ts, -1)) {
		case 'd':
			$inc = substr($ts, 0, -1);
			$eT = strtotime(add2date(add2date($start, 'day', $inc, TRUE), 'day', -1, TRUE));
			break;

		case 'w':
			$inc = substr($ts, 0, -1);
			$eT = strtotime(add2date(add2date($start, 'week', $inc, TRUE), 'day', -1, TRUE));
			break;

		case 'm':
			$inc = substr($ts, 0, -1);
			$eT = strtotime(add2date(add2date($start, 'month', $inc, TRUE), 'day', -1, TRUE));
			break;

		default:
			$eT = strtotime(add2date(add2date($start, 'day', 1, TRUE), 'day', -1, TRUE));
			break;
	}

	$r = strftime("%Y-%m-%d", $eT);
	return $r;
}//ko_consensus_get_end()


/**
 * Create a nice date title with the given startdate and timespan
 * @param string $start date Start date of the timespan
 * @param string $ts Timespan code (see switch statement for possible values)
 */
function ko_consensus_timespan_title($start, $ts) {
	global $DATETIME;

	switch (substr($ts, -1)) {
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

		default:
			$sT = strtotime($start);
			$eT = strtotime(add2date(add2date($start, 'day', 1, TRUE), 'day', -1, TRUE));
			break;
	}

	if ($sT == $eT) {
		$r = strftime($DATETIME['DdMY'], $sT);
	} else if (date('m', $sT) == date('m', $eT)) {
		$r = strftime('%d.', $sT) . ' - ' . strftime($DATETIME['dMY'], $eT);
	} else if (date('Y', $sT) == date('Y', $eT)) {
		$r = strftime($DATETIME['dM'], $sT) . ' - ' . strftime($DATETIME['dMY'], $eT);
	} else {
		$r = strftime($DATETIME['dMY'], $sT) . ' - ' . strftime($DATETIME['dMY'], $eT);
	}

	return $r;
}//ko_rota_timespan_title()


function ko_consensus_check_hash($x) {
	$get = explode('x', $x);

	$personId = $get[0];
	$start = substr($get[1], 0, 4) . '-' . substr($get[1], -4, -2) . '-' . substr($get[1], 6, 8);
	$span = $get[2];
	$team_ids = NULL;
	if (sizeof($get) == 5) {
		if ($get[3]) {
			$team_ids = explode('-', $get[3]);
		}
		$key = $get[4];
	} else {
		$key = $get[3];
	}

	$pass = substr(md5($personId . $start . $span . ($team_ids == NULL ? '' : implode('-', $team_ids)) . KOOL_ENCRYPTION_KEY), 0, 6) == $key;

	return [$pass, $personId, $team_ids, $start, $span];
}


/**
 * Return all consensus comments groups by [$person_id][$team_id]
 *
 * @param int $person_id if set: just return comments array for this person
 * @return array|mixed list of comments sorted by team_id
 */
function ko_consensus_get_comments_grouped_by_person($person_id = NULL) {
	if (is_numeric($person_id)) {
		$where = "WHERE person_id = " . $person_id;
	} else {
		$where = '';
	}

	$comments = db_select_data("ko_rota_consensus_comment", $where);
	$comments_by_person = [];
	foreach ($comments AS $comment) {
		$comments_by_person[$comment['person_id']][$comment['team_id']] = $comment['comment'];
	}

	if (is_numeric($person_id)) {
		return $comments_by_person[$person_id];
	} else {
		return $comments_by_person;
	}
}


/**
 * Save a comment for a team and person, if already set: update
 *
 * @param $team_id
 * @param $person_id
 * @param $comment
 */
function ko_consensus_save_comment($team_id, $person_id, $comment) {
	$comments = ko_consensus_get_comments_grouped_by_person($person_id);

	$data = [
		"team_id" => $team_id,
		"person_id" => $person_id,
		"comment" => $comment,
	];

	if (!empty($comments[$team_id])) {
		$where = "WHERE person_id = " . $person_id . " AND team_id = " . $team_id;
		db_update_data("ko_rota_consensus_comment", $where, $data);
	} else {
		db_insert_data("ko_rota_consensus_comment", $data);
	}
}

/**
 * Get jquery code to use in ajax.php and js-consensus.inc
 *
 * @return string
 */
function ko_consensus_get_jquery_selectable() {
	return "$(\".consensus_range_wrapper ol.consensus_range\").selectable({
			selecting: function (event, ui) {
				if (event.detail == 0) {
					_selectRange = true;
					return true;
				}
				if ($(ui.selecting).hasClass('ui-selected')) {
					_deselectQueue.push(ui.selecting);
				}
			},
			unselecting: function (event, ui) {
				$(ui.unselecting).addClass('ui-selected');
			},
			stop: function (event, ui) {
				var selected = [];
				$(\".ui-selected\", this).each(function () {
					selected.push($(this).data(\"day\"));
				});

				var team = $(this).data(\"team\");
				var week = $(this).data(\"week\");
				var type = $(this).data(\"type\");

				$(selected).each(function () {
					var field = $('#' + team + \"_\" + week + \"_\" + type + \"_\" + this.toString());
					if (field.hasClass('disabled') || field.hasClass(\"scheduled\")) {
						field.removeClass(\"ui-selected\");
						return false;
					}

					var remove_types = ['yes', 'maybe', 'no'];
					for (var i = 0; i < remove_types.length; i++) {
						if (remove_types[i] !== type) {
							$('#' + team + \"_\" + week + \"_\" + remove_types[i] + \"_\" + this.toString()).removeClass('ui-selected');
						}
					}
					field.removeClass(\"btn-primary\");
				}, team, week, type);

				var selected_days = [];
				$(this).find(\"li\").each(function () {
					if ($(this).hasClass(\"ui-selected\")) {
						selected_days.push($(this).data(\"day\"));
					}
				});

				sendReq(\"../consensus/ajax.php\", \"action,week,teamid,type,answers,x\", \"addweekconsensus,\" + week + \",\" + team + \",\" + type + \",\" + selected_days.join(\"-\") + \"," . $_REQUEST['x'] ."\", do_element_content);
				consensus_filter();
			},
		});";
}
