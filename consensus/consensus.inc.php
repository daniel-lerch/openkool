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


function ko_consensus_list_consensus () {
	global $smarty, $personId, $start, $span, $DATETIME, $BASE_PATH, $SMARTY_RENDER_TEMPLATE;

	require_once($BASE_PATH . 'inc/kotafcn.php');

	$teams = ko_rota_get_teams($personId);

	// sort out teams which don't support consensus
	$noConsensusAllowed = array();
	foreach ($teams as $k => $team) {
		if ($team['allow_consensus'] == 0) {
			$noConsensusAllowed[] = $team['name'];
			unset($teams[$k]);
		}
	}

	// prepare where statement for finding the corresponding events
	$egIds = array();
	foreach($teams as $k => $team) {
		$teamEgIds = explode(',', $team['eg_id']);
		$teams[$k]['eg_ids'] = $teamEgIds;
		foreach ($teamEgIds as $egId) {
			if ($egId != '') $egIds[] = $egId;
		}
	}
	$egIds = array_unique($egIds);
	if (trim(implode(' ', $egIds)) == '') {
		$where = '1=2';
	}
	else {
		$where = 'eventgruppen_id in (' . implode(',', $egIds) . ')';
	}

	// get person
	$personName = ko_get_person_name($personId);
	// handle event-type teams
	$end = ko_consensus_get_end($start, $span);
	// get event group names
	$eventGroups = db_select_data("ko_eventgruppen", "where " . str_replace('eventgruppen_id', 'id', $where), 'id, name');
	// select events, only those in the future and according to timespan
	$events = db_select_data('ko_event', 'where ' . $where . " and startdatum <= '" . $end . "' and enddatum >= '" . $start . "' and CONCAT(CONCAT(`startdatum`,' '),`startzeit`) > NOW() and `rota` = 1", '*', 'order by startdatum asc, startzeit asc', '', FALSE, TRUE);
	// process events
	foreach ($events as $k => $event) {
		$found = false;
		foreach ($teams as $team) {
			if (in_array($event['eventgruppen_id'], $team['eg_ids']) && trim($team['rotatype']) == 'event') {
				$found = $team['id'];
			}
		}
		if (!$found) {
			unset($events[$k]);
		}
		else {
			$processedEvent = $event;
			kota_process_data('ko_event', $processedEvent, 'list');
			$events[$k]['_processed'] = $processedEvent;
			$events[$k]['startingDate'] = strftime('%A ' . $DATETIME['dmy'], strtotime($event['startdatum'] . ' ' . $event['startzeit']));
			$events[$k]['startingTime'] = ($event['startzeit'] == '00:00:00' && $event['endzeit'] == '00:00:00') ? getLL('time_all_day') : strftime('%H:%M', strtotime($event['startdatum'] . ' ' . $event['startzeit']));
			$eventStatus = ko_rota_get_status($found, $event['id']);
			$events[$k]['status'] = $eventStatus;
			$events[$k]['_processed']['eventgruppen_name'] = $eventGroups[$event['eventgruppen_id']]['name'];
		}
	}
	array_merge($events);


	// remove teams which are never used from list
	foreach ($teams as $k => $team) {
		$found = false;
		foreach ($events as $event) {
			if (in_array($event['eventgruppen_id'], $team['eg_ids']) && trim($team['rotatype']) == 'event') {
				$found = true;
			}
		}
		if (!$found) {
			unset($teams[$k]);
		}
	}
	array_merge($teams);

	$data = array();

	// variable, which is set to true if a description section is needed
	$descriptionNeeded = false;
	// fill in actual table data
	foreach ($teams as $i => $team) {
		$name = $team['name'];
		$data[$i][0] = array('type' => 'title', 'content' => '<td><h3>' . $name. '</h3></td>');
		foreach ($events as $j => $event) {
			$entry = array();
			if (in_array($event['eventgruppen_id'], $team['eg_ids']) && trim($team['rotatype']) == 'event') {
				$entry['type'] = 'entry';
				$entry['content'] = ko_consensus_get_cell_contents('event', $event['id'], $team['id'], $personId);
			}
			else {
				$entry['type'] = 'empty';
				$entry['content'] = '<td></td><td></td>';
			}
			$data[$i][$j + 1] = $entry;
		}
		if (trim($team['consensus_description']) != '') {
			$descriptionNeeded = true;
		}
	}


	// General description
	$generalDescription = ko_get_setting('consensus_description');
	if (trim($generalDescription) != '') {
		$descriptionNeeded = true;
	}
	$smarty->assign('tpl_description_needed', $descriptionNeeded);
	$smarty->assign('tpl_general_description', ko_get_setting('consensus_description'));

	// Prepare smarty
	$smarty->assign('tpl_show_eventfields', explode(',', ko_get_setting('consensus_eventfields')));
	foreach(explode(',', ko_get_setting('consensus_eventfields')) as $field) {
		$labels[$field] = getLL('kota_ko_event_'.$field);
	}
	$smarty->assign('tpl_eventfield_labels', $labels);
	$smarty->assign('tpl_submit_value', getLL('save'));
	$smarty->assign('tpl_action', 'submit_consensus_entries');
	$smarty->assign('tpl_timespan', ko_rota_timespan_title($start, $span));
	$smarty->assign('tpl_person_name', $personName);
	$smarty->assign('tpl_data', $data);
	$smarty->assign('tpl_teams', $teams);
	$smarty->assign('tpl_events', $events);

	if (sizeof($data) == 0) {
		$smarty->assign('tpl_consensus_message', getLL('ko_consensus_no_events'));
	}
	else if (sizeof($noConsensusAllowed) > 0) {
		$smarty->assign('tpl_consensus_message', sprintf(getLL('ko_consensus_disabled_teams'), implode(', ', $noConsensusAllowed)));
	}

	$smarty->display('ko_consensus.tpl');
} // ko_consensus_list_consensus ()



/**
 * return all teams where $id is a member / leader
 *
 * @param $id the id of the person or group
 * @return array the requested teams
 */
function ko_rota_get_teams($id) {
	$allTeams = db_select_data('ko_rota_teams', 'where 1=1');
	$teams = array();
	foreach ($allTeams as $k => $team) {
		$members = ko_rota_get_team_members($team, true);
		if (array_key_exists($id, $members['people'])) {
			$teams[$k] =  $team;
		}
	}
	return $teams;
}



function ko_consensus_update_cell ($eventId, $teamId, $personId, $answer, $force = false) {
	if ($force == false && ko_rota_get_status($teamId, $eventId) != 1) {
		return;
	}

	$am = array ('no_answer' => 0, 'no' => 1, 'maybe' => 2, 'yes' => 3);

	$entry = db_select_data('ko_rota_consensus', 'where event_id = \'' . $eventId . '\' and team_id = ' . $teamId . ' and person_id = ' . $personId, 'team_id', '', '', TRUE, TRUE);
	if ($entry === null) {
		db_insert_data('ko_rota_consensus', array('event_id' => $eventId, 'team_id' => $teamId, 'person_id' => $personId, 'answer' => $am[$answer]));
	}
	else {
		db_update_data('ko_rota_consensus', 'where event_id = \'' . $eventId . '\' and team_id = ' . $teamId . ' and person_id = ' . $personId, array('answer' => $am[$answer]));
	}
} // ko_consensus_update_cell ()



function ko_consensus_get_cell_contents($mode, $eventId, $teamId, $personId) {

	$inner = ko_consensus_get_cell_contents_inner ($mode, $eventId, $teamId, $personId);
	$person = $inner['person'];
	$team = $inner['team'];

	$htmlPerson = '<td class="left_cell" id="container_person_' . $eventId . '_' . $teamId . '_' . $personId . '" >';
	$htmlPerson .= $person;
	$htmlPerson .= '</td>';

	$htmlTeam = '<td class="right_cell" id="container_team_' . $eventId . '_' . $teamId . '_' . $personId . '" >';
	$htmlTeam .= $team;
	$htmlTeam .= '</td>';

	return array('person' => $htmlPerson, 'team' => $htmlTeam);
} // ko_consensus_get_cell_contents()


/**
 * @param string $mode: either 'person' or 'team' or 'group'
 * @param $eventId
 * @param $teamId
 * @param string $id
 * @return array: $mode == 'person' ? 0, 1, 2, 3 : array(no_answer s, no s, maybe s, yes s)
 */
function ko_consensus_get_answers($mode = 'person', $eventId, $teamId, $id = '') {
	if ($mode == 'person') {
		$answer = db_select_data('ko_rota_consensus', ' where team_id = ' . $teamId . ' and event_id = \'' . $eventId . '\' and person_id = ' . $id, 'answer', 'order by person_id asc', '', TRUE, TRUE);
		return ($answer === null ? 0 : $answer['answer']);
	}
	else if ($mode == 'team') {
		$answers = db_select_data('ko_rota_consensus', ' where team_id = ' . $teamId . ' and event_id = \'' . $eventId . '\'', 'answer, person_id', 'order by person_id asc', '', FALSE, TRUE);
		$teamAnswers = array(0, 0, 0, 0);
		foreach ($answers as $answer) {
			$teamAnswers[$answer['answer']]++;
		}
		return $teamAnswers;
	}
	else if ($mode == 'group') {
		$role = ko_get_setting('rota_teamrole');
		$roleString = (trim($role) == '' ? '' : ':r' . $role);
		ko_get_leute($persons, "and `groups` regexp 'g" . trim($id) . '(:g[0-9]{6})*' . $roleString . "'");
		$groupAnswers = array(0, 0, 0, 0);
		foreach ($persons as $person) {
			$groupAnswers[ko_consensus_get_answers('person', $eventId, $teamId, $person['id'])]++;
		}
		return $groupAnswers;
	}

} // ko_consensus_get_answers()



function ko_consensus_get_cell_contents_inner ($mode, $eventId, $teamId, $personId) {
	$status = ko_rota_get_status($teamId, $eventId);
	// status map
	$sm = array(1 => 'open', 2 => 'closed');

	// Answer map
	$am = array ('' => 'no_answer', 0 => 'no_answer', 1 => 'no', 2 => 'maybe', 3 => 'yes');
	$amLL = array('' => '-', 0 => getLL('no'), 1 => '(' . getLL('yes') . ')', 2 => getLL('yes'));

	$answerPerson = ko_consensus_get_answers('person', $eventId, $teamId, $personId);
	$answersTeam = ko_consensus_get_answers('team', $eventId, $teamId);

	$htmlPerson =
	'<div id="person_' . $eventId . '_' . $teamId . '_' . $personId . '" class="person ' . $am[$answerPerson] . ' ' . $sm[$status] . '">
		<p id="' . $eventId . '_' . $teamId . '_' . $personId . '_yes" class="button yes ' . ($am[$answerPerson] == 'yes' ? ' active' : '') . '">'.$amLL[2].'</p>
		<p id="' . $eventId . '_' . $teamId . '_' . $personId . '_maybe" class="button maybe ' . ($am[$answerPerson] == 'maybe' ? ' active' : '') . '">'.$amLL[1].'</p>
		<p id="' . $eventId . '_' . $teamId . '_' . $personId . '_no" class="button no' . ($am[$answerPerson] == 'no' ? ' active' : '') . '">'.$amLL[0].'</p>
	</div>';

	$htmlTeam = '<ul class="team">';
	for ($i = 3; $i > 0; $i--) {
		$htmlTeam .= '<li><p class="team_members ' . $am[$i] . '">' . $answersTeam[$i] . '</p></li>';
	}
	$htmlTeam .= '</ul>';

	return array('person' => $htmlPerson, 'team' => $htmlTeam);
} // ko_consensus_get_cell_contents_inner ()



function ko_consensus_get_end($start, $ts) {
	global $DATETIME;

	switch($ts) {
		case '1d':
			break;

		case '1w':
		case '2w':
			$inc = substr($ts, 0, -1);
			$eT = strtotime(add2date(add2date($start, 'week', $inc, TRUE), 'day', -1, TRUE));
			break;

		case '1m':
		case '2m':
		case '3m':
		case '6m':
		case '12m':
			$inc = substr($ts, 0, -1);
			$eT = strtotime(add2date(add2date($start, 'month', $inc, TRUE), 'day', -1, TRUE));
			break;
	}

	$r = strftime("%Y-%m-%d", $eT);
	return $r;
}//ko_consensus_get_end()


/**
 * Create a nice date title with the given startdate and timespan
 * @param start date Start date of the timespan
 * @param ts string Timespan code (see switch statement for possible values)
 */
function ko_consensus_timespan_title($start, $ts) {
	global $DATETIME;

	switch($ts) {
		case '1d':
			$sT = $eT = strtotime($start);
			break;

		case '1w':
		case '2w':
			$inc = substr($ts, 0, -1);
			$sT = strtotime($start);
			$eT = strtotime(add2date(add2date($start, 'week', $inc, TRUE), 'day', -1, TRUE));
			break;

		case '1m':
		case '2m':
		case '3m':
		case '6m':
		case '12m':
			$inc = substr($ts, 0, -1);
			$sT = strtotime($start);
			$eT = strtotime(add2date(add2date($start, 'month', $inc, TRUE), 'day', -1, TRUE));
			break;
	}

		if($sT == $eT) {
			$r = strftime($DATETIME['DdMY'], $sT);
		} else if(date('m', $sT) == date('m', $eT)) {
			$r = strftime('%e.', $sT).' - '.strftime($DATETIME['dbY'], $eT);
		} else if(date('Y', $sT) == date('Y', $eT)) {
			$r = strftime($DATETIME['db'], $sT).' - '.strftime($DATETIME['dbY'], $eT);
		} else {
			$r = strftime($DATETIME['dbY'], $sT).' - '.strftime($DATETIME['dbY'], $eT);
		}

	return $r;
}//ko_rota_timespan_title()




?>
