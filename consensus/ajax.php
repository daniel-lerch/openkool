<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2003-2017 Renzo Lauper (renzo@churchtool.org)
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
		case 'addconsensusentry':
			$eventId = format_userinput($_POST["eventid"], "alphanum+");
			$teamId = format_userinput($_POST["teamid"], "alphanum");
			$personId = format_userinput($_POST["personid"], "alphanum");
			$answer = format_userinput($_POST["answer"], "alphanum");

			if (ko_rota_person_is_scheduled($teamId, $eventId, $personId)) continue;

			$error = ko_consensus_update_cell($eventId, $teamId, $personId, $answer);
			if ($error) {
				$rv['status'] = 0;
				$rv['message'] = getLL('error_consensus_' . $error);
			}

			$contentHashMap = ko_consensus_get_cell_contents_inner ($mode, $eventId, $teamId, $personId);
			$content = array();
			$content[] = $contentHashMap['person'];
			$content[] = $contentHashMap['team'];

			$rv['contents'] = $content;
			$rv['ids'] = array('#container_person_' . $eventId . '_' . $teamId . '_' . $personId, '#container_team_' . $eventId . '_' . $teamId . '_' . $personId);

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
