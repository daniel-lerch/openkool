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


$ko_path = '../';

require($ko_path.'inc/ko.inc.php');
require($ko_path.'rota/inc/rota.inc.php');
require_once('consensus.inc.php');

//Send headers to ensure latin1 charset
header('Content-Type: text/html; charset=UTF-8');


if(isset($_POST) && isset($_POST["action"])) {
	$action = format_userinput($_POST["action"], "alphanum");

	$rv = array('status' => 1, 'contents' => '', 'ids' => '');

	switch ($action) {
		case 'addconsensusentry':
			$eventId = format_userinput($_POST["eventid"], "alphanum+");
			$teamId = format_userinput($_POST["teamid"], "alphanum");
			$personId = format_userinput($_POST["personid"], "alphanum");
			$answer = format_userinput($_POST["answer"], "alphanum");

			ko_consensus_update_cell($eventId, $teamId, $personId, $answer);

			$contentHashMap = ko_consensus_get_cell_contents_inner ($mode, $eventId, $teamId, $personId);
			$content = array();
			$content[] = $contentHashMap['person'];
			$content[] = $contentHashMap['team'];

			$rv['contents'] = $content;
			$rv['ids'] = array('#container_person_' . $eventId . '_' . $teamId . '_' . $personId, '#container_team_' . $eventId . '_' . $teamId . '_' . $personId);

			print json_encode($rv);
		break;
	}
}
