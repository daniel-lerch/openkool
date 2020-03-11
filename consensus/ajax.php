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

error_reporting(0);

$ko_path = '../';

require($ko_path.'inc/ko.inc');
require($ko_path.'rota/inc/rota.inc');
require_once('consensus.inc');

//Send headers to ensure latin1 charset
header('Content-Type: text/html; charset=ISO-8859-1');

if(isset($_POST) && isset($_POST["action"])) {
	$action = format_userinput($_POST["action"], "alphanum");

	$rv = array('status' => 1, 'contents' => '', 'ids' => '');

	switch ($action) {
		case 'addweekconsensus':
			list($year, $week) = explode("-", format_userinput($_REQUEST["week"], "alphanum+"));
			$teamId = format_userinput($_REQUEST["teamid"], "alphanum");
			$answer = format_userinput($_REQUEST["type"], "alpha");
			$selected_days = explode("-", format_userinput($_REQUEST["answers"], "alphanum+"));

			list($pass, $personId, $team_ids, $start, $span) = ko_consensus_check_hash($_REQUEST['x']);

			try {
				$date = new DateTime();
				$date->setISODate($year,$week);
				$start = $date->format("Y-m-d");

				for($day=1; $day<=7; $day++) {
					$eventId = $date->format("Y-m-d");
					if (!ko_rota_person_is_scheduled($teamId, $eventId, $personId)) {
						if (in_array($day, $selected_days)) {
							$error = ko_consensus_update_cell($eventId, $teamId, $personId, $answer);
							if ($error) {
								$rv['status'] = 0;
								$rv['message'] = getLL('error_consensus_' . $error);
							}
						}
					}
					$end = $date->format("Y-m-d");
					$date->modify("+1 day");
				}
				unset($date);

				$team['details'] = db_select_data("ko_rota_teams", "WHERE id = '".$teamId."'", "*", "", "LIMIT 1", TRUE, TRUE);

				$days = ko_rota_get_days([$team['details']], $start, $end);

				$counter = 1;
				foreach($days AS $date => $day) {
					if($counter++ > 7) continue;
					$team['days'][$date] = ko_rota_get_team_members($teamId);

					$consensus_status = ko_consensus_get_status($date, $team['details'], [$team['details']['id'] => $team['details']['id']], $personId);
					$where = "WHERE event_id = '" . $date . "' AND team_id IN(" . $team['details']['id'] . ") AND answer = 3";
					$yes_counter = count(db_select_data("ko_rota_consensus", $where));
					if(stristr($team['details']['days_range'], date("N", strtotime($date))) === FALSE) {
						$consensus_status = 6;
					} else if ($consensus_status != 3 && $yes_counter == 0) {
						$consensus_status = 5;
					}

					$team['filter_status'].= $consensus_status;
				}

				$where = "WHERE event_id IN ('" . implode("','", array_column($days,'id')) . "') AND team_id = '" . $teamId . "'";
				$consensus_answers = db_select_data("ko_rota_consensus", $where);
				foreach($consensus_answers AS $answer) {
					$team['days'][$answer['event_id']]['people'][$answer['person_id']]['answer'] = $answer['answer'];
				}

				$html = ko_consensus_get_week_code($team, $teamId, $year."-".$week, $personId);
				$html = "<th class=\"header_team\">" . $team['details']['name'] . "</th><th>" . $html . "</th>";
				$js = "<script>
					var _selectRange = false, _deselectQueue = [];";
				$js.= ko_consensus_get_jquery_selectable();
				$js.= "</script>";

				$rv['contents'] = [
					$html.$js,
					$team['filter_status'],
				];
				$rv['ids']  = [
					"#". $teamId."_".$year."-".$week."_list",
					"attr:" . $teamId . "_" . $year . "-" . $week ."_list",
				];

				array_walk_recursive($rv, "utf8_encode_array");
				print json_encode($rv);
			} catch (Exception $e) {
			}

			break;
		case 'addconsensusentry':
			$eventId = format_userinput($_POST["eventid"], "alphanum+");
			$teamId = format_userinput($_POST["teamid"], "alphanum");
			$answer = format_userinput($_POST["answer"], "alphanum");

			list($pass, $personId, $team_ids, $start, $span) = ko_consensus_check_hash($_POST['x']);

			if (ko_rota_person_is_scheduled($teamId, $eventId, $personId)) break;

			$error = ko_consensus_update_cell($eventId, $teamId, $personId, $answer);
			if ($error) {
				$rv['status'] = 0;
				$rv['message'] = getLL('error_consensus_' . $error);
			}

			$contentHashMap = ko_consensus_get_cell_contents_inner($eventId, $teamId, $personId);
			$content = array();
			$content[] = $contentHashMap['person'];
			$content[] = $contentHashMap['team'];

			$teams = ko_rota_get_teams($personId);

			foreach ($teams as $k => $team) {
				if ($team_ids !== NULL && !in_array($team['id'], $team_ids)) {
					unset($teams[$k]);
				} else {
					if ($team['allow_consensus'] == 0) {
						unset($teams[$k]);
					}
				}
			}

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

			$end = ko_consensus_get_end($start, $span);
			$events = db_select_data('ko_event', 'where ' . $where . " and startdatum <= '" . $end . "' and enddatum >= '" . $start . "' and CONCAT(CONCAT(`startdatum`,' '),`startzeit`) > NOW() and `rota` = 1", '*', 'order by startdatum asc, startzeit asc', '', FALSE, TRUE);

			foreach ($events as $k => $event) {
				$found = false;
				foreach ($teams as $team) {
					if (in_array($event['eventgruppen_id'], $team['eg_ids']) && trim($team['rotatype']) == 'event' && !ko_rota_is_scheduling_disabled($event['id'], $team['id'])) {
						$found = $team['id'];
					}
				}
				if (!$found) {
					unset($events[$k]);
				}
				else {
					$eventStatus = ko_rota_get_status($event['id']);
					$events[$k]['status'] = $eventStatus;
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

			ko_get_event_by_id($eventId, $event);
			$consensus_status = "";
			foreach ($teams as $j => $team) {
				$consensus_status .= ko_consensus_get_status($event, $team, $teams, $personId);
			}

			$yes_counter = 0;
			if(sizeof($teams) > 0) {
				$where = "WHERE event_id = '" . $event['id'] . "' AND team_id IN(".implode(",", array_keys($teams)).") AND answer = 3";
				$yes_counter = count(db_select_data("ko_rota_consensus", $where));
			}
			if($yes_counter == 0) {
				$consensus_status .= 5;
			}

			$content[] = $consensus_status;

			$rv['contents'] = $content;
			$rv['ids'] = [
				'#container_person_' . $eventId . '_' . $teamId . '_' . $personId,
				'#container_team_' . $eventId . '_' . $teamId . '_' . $personId,
				'attr:event_' . $eventId,
			];

			array_walk_recursive($rv, "utf8_encode_array");
			print json_encode($rv);
		break;

		case "savecomment":
			$teamId = format_userinput($_POST["team_id"], "int");
			$comment = format_userinput($_POST["comment"], "text");
			$x = format_userinput($_POST["x"], "text");

			list($pass, $personId, $team_ids, $start, $span) = ko_consensus_check_hash($x);
			if($pass) {
				ko_consensus_save_comment($teamId, $personId, $comment);
			}

			break;
	}
}
