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


$ko_path = '../';

require($ko_path.'inc/ko.inc.php');
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
