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

if(in_array('ko_event', $KOTA_TABLES)) {
	$KOTA['ko_event'] = array(
		'_access' => array(
			'module' => 'daten',
			'chk_col' => 'eventgruppen_id',
			'level' => 3,
			'condition' => "return '@import_id@' == '';",  //Imported events may not be edited
		),
		"_multititle" => array(
			'title' => '',
			"eventgruppen_id" => "ko_get_eventgruppen_name('@VALUE@')",
			"startdatum" => "sql2datum('@VALUE@')",
			"startzeit" => "sql_zeit('@VALUE@')",
		),
		'_inlineform' => array(
			'redraw' => array(
				'sort' => 'sort_events',
				'fcn' => 'ko_list_events(\'all\');'
			),
			'module' => 'daten',
		),
		'_special_cols' => array(
			'crdate' => 'cdate',
			'cruser' => 'user_id',
			'lastchange' => 'last_change',
			'lastchange_user' => 'lastchange_user',
		),
		"_listview" => array(
			1 => array("name" => "eg_color", "sort" => FALSE, "multiedit" => FALSE),
			10 => array("name" => "startdatum", "sort" => "startdatum", "multiedit" => "startdatum,enddatum", 'filter' => 'startdatum'),
			15 => array("name" => "enddatum", "sort" => "enddatum", "multiedit" => "enddatum", 'filter' => 'enddatum'),
			20 => array("name" => "eventgruppen_id", "sort" => "eventgruppen_id"),
			22 => array("name" => "url", "sort" => "url", 'filter' => TRUE),
			25 => array('name' => 'title', 'sort' => 'title', 'filter' => TRUE),
			30 => array("name" => "kommentar", "sort" => "kommentar", 'filter' => TRUE),
			//35 for kommentar2 if not ko_guest
			40 => array("name" => "startzeit", "sort" => "startzeit", "multiedit" => "startzeit,endzeit", 'filter' => TRUE),
			50 => array("name" => "room", "sort" => "ko_event_rooms.title", 'filter' => TRUE),
			//60 is reserved for rota (set further down) only if rota module is installed
			//70 is reserved for reservations (set further down) only if res module is installed
			80 => array("name" => "registrations", "sort" => FALSE, "filter" => TRUE),
			100 => array("name" => "cdate", "sort" => 'cdate', "filter" => TRUE),
			101 => array("name" => "last_change", "sort" => 'last_change', "filter" => TRUE),
			110 => array("name" => "user_id", "sort" => 'user_id', "filter" => TRUE),
		),
		'_listview_default' => array('startdatum', 'eventgruppen_id', 'title', 'startzeit', 'room', 'rota', 'reservationen'),

		"eventgruppen_id" => array(
			"list" => 'db_get_column("ko_eventgruppen", "@VALUE@", "name")',
			"post" => 'uint',
			"form" => array_merge(array(
				"type" => "dynselect",
				"js_func_add" => "event_cal_select_add",
				"params" => 'size="5"',
				"add_class" => 'res-conflict-field',
				"mandatory" => TRUE,
			), kota_get_form("ko_event", "eventgruppen_id")),
		),
		"do_notify" => array(
			'form' => array(
				'type' => 'switch',
				'default' => 1,
			),
		),
		'title' => array(
			'xls' => 'none',
			'list' => 'ko_html',
			'pre' => 'ko_html',
			'form' => array(
				'type' => 'text',
				'params' => 'size="60" maxlength="255"',
			),
		),
		"url" => array(
			"pre" => "",
			"form" => array(
				"type" => "text",
				"params" => 'size="60"',
			),
		),
		"startdatum" => array(
			"list" => 'FCN:kota_listview_date',
			'xls' => "sql2datum('@VALUE@')",
			"pre" => "sql2datum('@VALUE@')",
			"post" => "sql_datum('@VALUE@')",
			"form" => array(
				"type" => "jsdate",
				'noinline' => TRUE,
				'add_class' => 'res-conflict-field',
				'mandatory' => TRUE,
				'sibling' => 'enddatum',
			),
			'filter' => array(
				'type' => 'select',
				'data' => array(
					2 => strftime('%A', mktime(1,1,1, 1, 1, 2018)),  //Monday
					3 => strftime('%A', mktime(1,1,1, 1, 2, 2018)),
					4 => strftime('%A', mktime(1,1,1, 1, 3, 2018)),
					5 => strftime('%A', mktime(1,1,1, 1, 4, 2018)),
					6 => strftime('%A', mktime(1,1,1, 1, 5, 2018)),
					7 => strftime('%A', mktime(1,1,1, 1, 6, 2018)),
					1 => strftime('%A', mktime(1,1,1, 1, 7, 2018)),  //Sunday
				),
				'sql' => "DAYOFWEEK([TABLE].[FIELD]) [NOTEQUAL]= '[VALUE]'",
				'list' => 'FCN:kota_listview_dayofweek',
			),
		),
		"enddatum" => array(
			'list' => "sql2datum('@VALUE@')",
			"xls" => "FCN:kota_xls_enddate",
			"pre" => 'FCN:kota_pre_enddate',
			'post' => 'FCN:kota_post_enddate',
			"form" => array(
				"type" => "jsdate",
				'noinline' => TRUE,
				'add_class' => 'res-conflict-field',
			),
			'filter' => array(
				'type' => 'select',
				'data' => array(
					2 => strftime('%A', mktime(1,1,1, 1, 1, 2018)),  //Monday
					3 => strftime('%A', mktime(1,1,1, 1, 2, 2018)),
					4 => strftime('%A', mktime(1,1,1, 1, 3, 2018)),
					5 => strftime('%A', mktime(1,1,1, 1, 4, 2018)),
					6 => strftime('%A', mktime(1,1,1, 1, 5, 2018)),
					7 => strftime('%A', mktime(1,1,1, 1, 6, 2018)),
					1 => strftime('%A', mktime(1,1,1, 1, 7, 2018)),  //Sunday
				),
				'sql' => "DAYOFWEEK([TABLE].[FIELD]) [NOTEQUAL]= '[VALUE]'",
				'list' => 'FCN:kota_listview_dayofweek',
			),
		),
		"startzeit" => array(
			"list" => 'FCN:kota_listview_time',
			"pre" => "sql_zeit('@VALUE@')",
			"post" => "sql_zeit('@VALUE@')",
			'filter' => array(
				'type' => 'time',
			),
			"form" => array(
				"type" => "text",
				"params" => 'size="11" maxlength="11"',
				'add_class' => 'res-conflict-field',
				"descicon" => array(
					'icon' => "star",
					'context' => 'highlight',
				),
			),
		),
		"endzeit" => array(
			"pre" => "sql_zeit('@VALUE@')",
			"post" => "sql_zeit('@VALUE@')",
			'filter' => array(
				'type' => 'time',
			),
			"form" => array(
				"type" => "text",
				"params" => 'size="11" maxlength="11"',
				'add_class' => 'res-conflict-field',
				"descicon" => array(
					'icon' => "star",
					'context' => 'highlight',
				),
			),
		),
		"room" => array(
			'xls' => 'FCN:kota_listview_ko_event_rooms',
			"list" => "FCN:kota_listview_ko_event_rooms",
			"form" => array_merge(array(
				"type" => "selectplus",
				"id_link" => "ko_event_rooms",
				"async_form" => [
					"tag" => "add_room",
					"table" => "ko_event_rooms",
				],
				"params" => 'size="0"',
			), kota_get_form("ko_event_rooms", "title")),
		),
		"kommentar" => array(
			'xls' => 'none',
			'list' => 'ko_html',
			'pdf' => "nl2br('@VALUE@')",
			"pre" => "ko_html",
			"form" => array(
				"type" => "textarea",
				"params" => 'cols="50" rows="4"',
			),
		),
		"kommentar2" => array(
			'xls' => 'none',
			"list" => "ko_html",
			"pre" => "ko_html",
			"form" => array(
				"type" => "textarea",
				"params" => 'cols="50" rows="4"',
			),
		),
		"registrations" => array(
			"list" => "FCN:kota_listview_ko_event_registrations",
			'exclude_from_access' => 1,
		),
		"eg_color" => array(
			"list" => "FCN:kota_listview_eventgroup_color",
			"xls" => "FCN:kota_listview_eventgroup_color_xls",
			'exclude_from_access' => 1,
		),
		"cdate" => array(
			'list' => 'FCN:kota_listview_datetimecol',
			'filter' => array(
				'type' => 'jsdate',
			),
		),
		"last_change" => array(
			'list' => 'FCN:kota_listview_datetimecol',
			'filter' => array(
				'type' => 'jsdate',
			),
		),
		"user_id" => array(
			'list' => 'FCN:kota_listview_login',
		),
	);

	if (ko_module_installed("taxonomy") && $access['taxonomy']['ALL'] >= 1) {
		$KOTA['ko_event']['terms'] = [
			'list' => 'FCN:kota_listview_ko_event_terms',
			'form' => array(
				"desc" => getLL("form_taxonomy_title"),
				'type' => "dynamicsearch",
				"name" => "terms",
				"descicon" => array(
					'icon' => "star",
					'context' => 'highlight',
				),
				"module" => "taxonomy",
				'ajaxHandler' => [
					'url' => "../taxonomy/inc/ajax.php",
					'actions' => ['search' => "termsearch"]
				]
			),
		];

		$KOTA['ko_event']['_listview'][] = [
			"name"=> "terms",
			"sort" => FALSE,
			"multiedit" => ($access['daten']['MAX'] >= 2 ? "terms" : FALSE),
			"filter" => FALSE,
		];
	}

	//Add event groups as columns
	$calendars = db_select_data('ko_event_calendar', "WHERE 1", '*', 'ORDER BY `name` ASC');
	array_unshift($calendars, array('id' => 0, 'name' => getLL('daten_no_calendar')));
	$eventGroupsWithoutCalendar = db_select_data('ko_eventgruppen', "WHERE `calendar_id` = '0'", '*', 'ORDER BY `name` ASC');
	$tc = 20010;

	if(sizeof($calendars) > 0 && $access['daten']['MAX'] > 0) {
		//Add divider before all event groups
		$LOCAL_LANG[$_SESSION['lang']]['kota_ko_event_calendar_-1'] = '--- '.getLL('daten_groups_list_title').' ---';
		$KOTA['ko_event']['calendar_-1'] = array('list' => 'FCN:kota_listview_events_by_eventgroup');
		$KOTA['ko_event']['_listview'][$tc] = array('name' => 'calendar_-1', 'sort' => FALSE, 'multiedit' => FALSE);
		$tc += 10;

		foreach($calendars as $cal) {
			$calID = $cal['id'];
			if($access['daten']['ALL'] < 1 && $access['daten']['cal'.$calID] < 1) continue;

			$KOTA['ko_event']['calendar_'.$calID] = array('list' => 'FCN:kota_listview_events_by_eventgroup');
			$KOTA['ko_event']['_listview'][$tc] = array('name' => 'calendar_'.$calID, 'sort' => FALSE, 'multiedit' => FALSE);
			$LOCAL_LANG[$_SESSION['lang']]['kota_ko_event_calendar_'.$calID] = strtoupper($cal['name']);
			$tc += 10;

			$eventGroups = db_select_data('ko_eventgruppen', "WHERE `calendar_id` = '$calID'", '*', 'ORDER BY `name` ASC');
			foreach($eventGroups as $egID => $eg) {
				if($access['daten']['ALL'] < 1 && $access['daten'][$egID] < 1) continue;

				$KOTA['ko_event']['eventgroup_'.$egID] = array('list' => 'FCN:kota_listview_events_by_eventgroup');
				$KOTA['ko_event']['_listview'][$tc] = array('name' => 'eventgroup_'.$egID, 'sort' => FALSE, 'multiedit' => FALSE);
				$LOCAL_LANG[$_SESSION['lang']]['kota_ko_event_eventgroup_'.$egID] = $eg['name'];
				$tc += 10;
			}
		}
	}

	if(ko_module_installed('reservation')) {
		if (!isset($access['reservation'])) ko_get_access('reservation');
		$resItemns = db_select_data("ko_resitem", "WHERE 1=1");
		$showRes = FALSE;
		foreach($resItemns as $iid => $item) {
			if ($access['reservation'][$iid] >= 1) {
				$showRes = TRUE;
				break;
			}
		}

		if ($showRes) {
			$KOTA['ko_event']['_listview'][70] = array('name' => 'reservationen', 'sort' => "(ROUND(LENGTH(`reservationen`)-LENGTH(REPLACE(`reservationen`,\',\',\'\'))))", 'multiedit' => FALSE);

			$KOTA['ko_event']['reservationen'] = array(
				'xls' => 'FCN:kota_listview_event_reservations_xls',
				'list' => 'FCN:kota_listview_event_reservations',
				'pdf' => 'FCN:kota_pdf_event_reservations',
			);
		}
	}

	if(ko_module_installed('rota')) {
		$KOTA['ko_event']['rota']['form'] = array('type' => 'switch');
		$KOTA['ko_event']['rota']['list'] = 'FCN:kota_listview_boolyesno';
		$KOTA['ko_event']['_listview'][60] = array('name' => 'rota', 'sort' => 'rota', 'filter' => TRUE);

		ko_get_access('rota');
		$order = 'ORDER BY name ASC';
		if(ko_get_setting('rota_manual_ordering')) $order = 'ORDER BY sort ASC';
		$rota_teams = db_select_data('ko_rota_teams', "WHERE 1", '*', $order);
		$tc = 10010;
		//Add divider before all rota teams
		if(sizeof($rota_teams) > 0 && $access['rota']['MAX'] > 0) {
			$LOCAL_LANG[$_SESSION['lang']]['kota_ko_event_rotateam_0'] = '--- '.getLL('rota_teams_list_title').' ---';
			$KOTA['ko_event']['rotateam_0'] = array('list' => 'FCN:kota_listview_rota_schedule');
			$KOTA['ko_event']['_listview'][$tc] = array('name' => 'rotateam_0', 'sort' => FALSE, 'multiedit' => FALSE);
			$tc += 10;

			foreach($rota_teams as $team) {
				if($access['rota']['ALL'] > 0 || $access['rota'][$team['id']] > 0) {
					$LOCAL_LANG[$_SESSION['lang']]['kota_ko_event_rotateam_'.$team['id']] = getLL('rota_kota_prefix_ko_event').' '.$team['name'];
					$LOCAL_LANG[$_SESSION['lang']]['kota_listview_ko_event_rotateam_'.$team['id']] = getLL('rota_kota_prefix_ko_event_short').' '.$team['name'];
					$KOTA['ko_event']['rotateam_'.$team['id']] = array(
						'list' => 'FCN:kota_listview_rota_schedule',
						'filter' => array(
							'type' => 'FCN:kota_filter_form_rota_schedule:selectplus',
							'overwrite' => true,
							'list' => 'FCN:kota_filter_rota_schedule',
						),
					);

					$KOTA['ko_event']['_listview'][$tc] = array('name' => 'rotateam_'.$team['id'], 'filter' => TRUE, 'sort' => FALSE, 'multiedit' => FALSE);

					$tc += 10;
				}
			}
		}
	}

	// allow to display absence columns in list
	if ($access['daten']['ABSENCE'] >= 1) {
		$tc = 30010;
		$LOCAL_LANG[$_SESSION['lang']]["kota_ko_event_absences_all"] = getLL("absence_eventgroup_all");
		$KOTA['ko_event']['_listview'][$tc] = ["name" => "absences_all",];
		$KOTA['ko_event']['absences_all'] = ['list' => 'FCN:kota_listview_ko_event_absences',];

		$absence_filters = array_merge((array)ko_get_userpref('-1', '', 'filterset', 'ORDER BY `key` ASC'), (array)ko_get_userpref($_SESSION['ses_userid'], '', 'filterset', 'ORDER BY `key` ASC'));
		$tc += 10;
		foreach ($absence_filters AS $absence_filter) {
			$label = getLL("absence_eventgroup_filter") . ": " . ($absence_filter['user_id'] == '-1' ? getLL('itemlist_global_short') : "") . $absence_filter["key"];
			$LOCAL_LANG[$_SESSION['lang']]["kota_ko_event_absences_filter_" . $absence_filter['id']] = $label;
			$KOTA['ko_event']['_listview'][$tc] = ["name" => "absences_filter_" . $absence_filter['id'],];
			$KOTA['ko_event']['absences_filter_' . $absence_filter['id']] = ['list' => 'FCN:kota_listview_ko_event_absences',];
			$tc += 10;
		}
	}

	// set event program kota
	if (ko_get_setting('activate_event_program') == 1) {
		$KOTA['ko_event']['program'] = array(
			'form' => array(
				'type' => 'foreign_table', // TODO: access! problem: user may change eventgroup after he entered program
				'foreign_table_preset' => array(
					'table' => 'ko_eventgruppen_program',
					'join_column_local' => 'eventgruppen_id',
					'join_column_foreign' => 'pid',
					'll_no_join_value' => 'daten_alert_program_no_eventgroup_selected',
					//'check_access' => 'FCN:kota_event_program_check_access',
				),
				'table' => 'ko_event_program',
				'sort_button' => '`time` ASC',
				'new_row' => TRUE,
				'ignore_test' => TRUE,
				'columnWidth' => '12',
			),
		);
	}

	// set event program kota
	if (ko_get_setting('activate_event_program') == 1) {
		$KOTA['ko_event_program'] = array(
			'_access' => array(
				'module' => 'daten',
				'key' => 'daten',
				'level' => 3,
			),
			'_multititle' => array(
				'time' => "substr('@VALUE@', 0, -3)",
				'name' => '',
			),
			'_inlineform' => array(),
			'_special_cols' => array(
				'crdate' => 'crdate',
				'cruser' => 'cruser',
			),
			"_listview" => array(
				10 => array('name' => 'time'),
				15 => array('name' => 'name'),
				20 => array('name' => 'title'),
				25 => array('name' => 'infrastructure'),
				30 => array('name' => 'team'),
			),
			'_listview_default' => array(),

			"time" => array(
				"list" => "sql_zeit('@VALUE@')",
				"pre" => "sql_zeit('@VALUE@')",
				"post" => "sql_zeit('@VALUE@')",
				"form" => array(
					"type" => "text",
					"params" => 'size="11" maxlength="11"',
					'columnWidth' => '1',
				),
			),  //time
			"team" => array(
				'list' => 'FCN:kota_listview_rota_teams',
				"form" => array(
					'type' => 'dynamicsearch',
					'show_add' => FALSE,
					'columnWidth' => '2',
				),
			),
			'name' => array(
				'pre' => 'ko_html',
				'form' => array(
					'type' => 'text',
					'columnWidth' => '3',
				),
			),  //name
			'title' => array(
				'pre' => 'ko_html',
				'pdf' => "nl2br('@VALUE@')",
				'form' => array(
					'type' => 'textarea',
					'params' => 'cols="80" rows="2"',
					'columnWidth' => '3',
				),
			),  //title
			'infrastructure' => array(
				'pre' => 'ko_html',
				'pdf' => "nl2br('@VALUE@')",
				'form' => array(
					'type' => 'textarea',
					'params' => 'cols="80" rows="2"',
					'columnWidth' => '3',
				),
			),  //title
		);
	}

	$markFields = ko_array_column($EVENT_PROPAGATE_FIELDS, 'from');
	foreach ($KOTA['ko_event'] as $field => &$def) {
		if (in_array($field, $markFields)) {
			$def['form']['descicon'] = array(
				'icon' => "star",
				'context' => 'highlight',
			);
		}
	}
}



if(in_array('ko_event_rooms', $KOTA_TABLES)) {
	$KOTA['ko_event_rooms'] = array(
		'_access' => array(
			'module' => 'daten',
			'chk_col' => '',
			'level' => 2,
		),
		"_multititle" => array(
			'title' => '',
		),
		"_listview" => array(
			10 => array("name" => "title", "sort" => "title", "multiedit" => "title", 'filter' => TRUE),
			20 => array("name" => "title_short", "sort" => "title_short", "multiedit" => "title_short", 'filter' => TRUE),
			30 => array("name" => "address", "sort" => "address", "multiedit" => "address", 'filter' => TRUE),
			40 => array("name" => "coordinates", "sort" => "coordinates", "multiedit" => "coordinates", 'filter' => TRUE),
			50 => array("name" => "used_in", "sort" => FALSE, "multiedit" => FALSE, 'filter' => FALSE),
			60 => array("name" => "hidden", "sort" => FALSE, "multiedit" => FALSE, 'filter' => TRUE),
		),
		"_listview_default" => array('title', 'title_short', 'address', 'coordinates', 'used_in', 'hidden'),
		'_inlineform' => array(
			'redraw' => array(
				'sort' => "setsortrooms",
				'fcn' => 'ko_list_rooms();'
			),
			'module' => 'daten',
		),
		'_form' => array(
			'redraw' => array(
				'fcn' => "ko_formular_room('@MODE@', '@ID@')",
			),
			'module' => 'daten',
		),
		'_special_cols' => array(
			'lastchange' => 'lastchange',
			'lastchange_user' => 'lastchange_user',
			'crdate' => 'crdate',
			'cruser' => 'cruser',
		),
		"title" => array(
			'list' => 'ko_html',
			"form" => array(
				"type" => "text",
				"params" => 'size="60" maxlength="100"'
			),
		),
		"title_short" => array(
			'list' => 'ko_html',
			"form" => array(
				"type" => "text",
				"params" => 'size="60" maxlength="100"'
			),
		),
		"address" => array(
			'list' => 'ko_html',
			"form" => array(
				"type" => "text",
				"params" => 'size="60" maxlength="100"'
			),
		),
		"coordinates" => array(
			'list' => 'FCN:kota_listview_googlemaps_link',
			"form" => array(
				"type" => "text",
				"params" => 'size="60" maxlength="255"'
			),
		),
		"url" => array(
			'list' => 'ko_html',
			"form" => array(
				"type" => "text",
				"params" => 'size="60" maxlength="200"'
			),
		),
		'used_in' => array(
			'list' => 'FCN:kota_listview_ko_event_rooms_used_in',
		),
		'hidden' => array(
			'list' => 'FCN:kota_listview_boolyesno',
			'form' => array(
				'type' => 'switch',
				'label_0' => getLL('no'),
				'label_1' => getLL('yes'),
				'value' => '0',
			),
		)
	);
}

if(in_array('ko_eventgruppen', $KOTA_TABLES)) {
	$KOTA['ko_eventgruppen'] = array(
		'_access' => array(
			'module' => 'daten',
			'chk_col' => 'id',
			'level' => 3,
		),
		"_multititle" => array(
			"name" => "",
		),
		'_inlineform' => array(
			'redraw' => array(
				'sort' => 'sort_tg',
				'fcn' => 'ko_list_groups();'
			),
			'module' => 'daten',
		),
		'_special_cols' => array(
			'crdate' => 'crdate',
			'cruser' => 'cruser',
			'lastchange' => 'lastchange',
			'lastchange_user' => 'lastchange_user',
		),
		"_listview" => array(
			10 => array("name" => "name", "sort" => "name", "multiedit" => "name", 'filter' => TRUE),
			15 => array("name" => "calendar_id", "sort" => "calendar_id", 'filter' => TRUE),
			16 => array("name" => "farbe", "sort" => "farbe", 'multiedit' => 'farbe', 'filter' => FALSE),
			//20 => array("name" => "room", "sort" => "room"),
			//30 => array("name" => "startzeit", "sort" => "startzeit", "multiedit" => "startzeit,endzeit"),
			35 => array('name' => 'title', 'sort' => 'title', 'filter' => TRUE),
			40 => array("name" => "kommentar", "sort" => "kommentar", 'filter' => TRUE),
			//50, 70 are reserved for rota, res_combined (see below)
			80 => array("name" => "moderation", "sort" => "moderation", 'filter' => TRUE),
		),
		'_types' => array(
			'field' => 'type',
			'default' => 0,
			'types' => array(
				2 => array(  //Rota week team
					'use_fields' => array('name', 'shortname', 'farbe'),
				),
				3 => array(  //iCal import
					'use_fields' => array('calendar_id', 'name', 'shortname', 'farbe', 'ical_url'),
					'add_fields' => array(
						'ical_url' => array(
							'form' => array(
								'type' => 'text',
							),
						),
						'ical_title' => array(
							'form' => array(
								'type' => 'text',
							),
						),
						'update' => array(
							'form' => array(
								'type' => 'select',
								'params' => 'size="0"',
								'values' => array(5, 10, 15, 30, 45, 60, 120, 180, 240, 300),
								'descs'  => array('5 '.getLL('time_minutes'), '10 '.getLL('time_minutes'), '15 '.getLL('time_minutes'), '30 '.getLL('time_minutes'), '45 '.getLL('time_minutes'), '1 '.getLL('time_hour'), '2 '.getLL('time_hours'), '3 '.getLL('time_hours'), '4 '.getLL('time_hours'), '5 '.getLL('time_hours')),
							)
						),
						'last_update' => array(
							'pre' => "sql2datetime('@VALUE@')",
							'list' => "sql2datetime('@VALUE@')",
							'form' => array(
								'type' => 'html',
								'dontsave' => TRUE,
								'ignore_test' => TRUE,
							),
						),
					),
				),
			),
		),

		"name" => array(
			'list' => 'FCN:kota_listview_ko_eventgruppen_name',
			"form" => array(
				"type" => "text",
				"params" => 'size="60" maxlength="100"'
			),
		),  //name
		"calendar_id" => array(
			"post" => 'uint',
			"list" => 'db_get_column("ko_event_calendar", "@VALUE@", "name")',
			"form" => array_merge(array(
				"type" => "textplus",
				"params" => 'maxlength="200" placeholder="'.getLL('kota_ko_eventgruppen_calendar_id_placeholder').'"',
				"id_link" => "ko_event_calendar",
			), kota_get_form("ko_eventgruppen", "calendar_id")),
		),  //gruppen_id
		"shortname" => array(
			"list" => "ko_html",
			"form" => array(
				"type" => "text",
				"params" => 'size="10" maxlength="5"'
			),
		),  //shortname
		"url" => array(
			"form" => array(
				"type" => "text",
				"params" => 'size="60" maxlength="255"'
			),
		),  //url
		"room" => array(
			'xls' => 'none',
			"list" => "FCN:kota_listview_ko_event_rooms",
			"form" => array_merge(array(
				"type" => "selectplus",
				"id_link" => "ko_event_rooms",
				"async_form" => [
					"tag" => "add_room",
					"table" => "ko_event_rooms",
				],
				"params" => 'size="0"',
			), kota_get_form("ko_event_rooms", "title")),
		),
		'title' => array(
			'list' => 'ko_html',
			'pre' => 'ko_html',
			'form' => array(
				'type' => 'text',
				'params' => 'size="60" maxlength="255"',
			),
		),  //title
		"startzeit" => array(
			"list" => 'FCN:kota_listview_time',
			"pre" => "sql_zeit('@VALUE@')",
			"post" => "sql_zeit('@VALUE@')",
			'filter' => array(
				'type' => 'time',
			),
			"form" => array(
				"type" => "text",
				"params" => 'size="11" maxlength="11"',
				"descicon" => array(
					'icon' => "star",
					'context' => 'highlight',
				),
			),
		),  //startzeit
		"endzeit" => array(
			"pre" => "sql_zeit('@VALUE@')",
			"post" => "sql_zeit('@VALUE@')",
			'filter' => array(
				'type' => 'time',
			),
			"form" => array(
				"type" => "text",
				"params" => 'size="11" maxlength="11"',
				"descicon" => array(
					'icon' => "star",
					'context' => 'highlight',
				),
			),
		),  //endzeit
		"kommentar" => array(
			"list" => "ko_html",
			"form" => array(
				"type" => "textarea",
				"params" => 'cols="50" rows="4"',
				'new_row' => TRUE,
			),
		),  //kommentar
		"farbe" => array(
			'list' => 'FCN:kota_listview_color',
			"post" => 'str_replace("#", "", format_userinput("@VALUE@", "alphanum"))',
			"form" => array(
				"type" => "color",
				"params" => 'size="10" maxlength="7"',
			),
		),
		"moderation" => array(
			"pre" => "ko_html",
			"post" => 'uint',
//			"filter" => [
//				"list" => "FCN:kota_listview_",
//			],
			"list" => "FCN:kota_listview_ll",
			"form" => array_merge(array(
				"type" => "select",
				"params" => 'size="0"',
			), kota_get_form("ko_eventgruppen", "moderation")),
		),  //moderation

		'_form_layout' => [
			'general' => [
				'group' => FALSE,
				'sorting' => 10,
				'groups' => [
					'general' => [
						'sorting' => 10,
						'group' => TRUE,
						'rows' => [
							10 => ['name' => 6, 'calendar_id' => 6],
							20 => ['shortname' => 6, 'url' => 6],
							30 => ['farbe' => 6, 'moderation' => 6],
							40 => ['notify' => 6],
						],
					],
					'event' => [
						'sorting' => 20,
						'group' => TRUE,
						'rows' => [
							10 => ['title' => 6, 'room' => 6],
							20 => ['startzeit' => 6, 'endzeit' => 6],
							30 => ['kommentar' => 6, 'terms' => 6],
						],
					],
					'rota' => [
						'sorting' => 30,
						'group' => TRUE,
						'rows' => [
							10 => ['rota' => 6, 'rota_teams' => 6],
						],
					],
					'reservation' => [
						'sorting' => 40,
						'group' => TRUE,
						'rows' => [
							10 => ['resitems' => 6, 'responsible_for_res' => 6],
							20 => ['res_startzeit' => 6, 'res_endzeit' => 6],
							30 => ['res_combined' => 6],
						],
					],
					'program' => [
						'sorting' => 50,
						'group' => TRUE,
						'rows' => [
							10 => ['program' => 12],
						],
					],
				],
			],
			'_default_cols' => 6,
			'_default_width' => 6,
			'_ignore_fields' => [],
		],
	);


	if (ko_module_installed("taxonomy") && $access['taxonomy']['ALL'] >= 1) {
		$KOTA['ko_eventgruppen']['terms'] = [
			'list' => 'FCN:kota_listview_ko_event_terms',
			'form' => array(
				"desc" => getLL("form_taxonomy_title"),
				'type' => "dynamicsearch",
				"name" => "terms",
				"descicon" => array(
					'icon' => "star",
					'context' => 'highlight',
				),
				"module" => "taxonomy",
				'ajaxHandler' => [
					'url' => "../taxonomy/inc/ajax.php",
					'actions' => ['search' => "termsearch"]
				]
			),
		];

		$KOTA['ko_eventgruppen']['_listview'][] = [
			"name" => "terms",
			"sort" => FALSE,
			"multiedit" => ($access['daten']['MAX'] >= 2 ? "terms" : FALSE),
			"filter" => FALSE,
		];

		$KOTA['ko_eventgruppen']['_types']['types'][3]['use_fields'][] = 'terms'; // in iCal import
	}

	if(ko_module_installed('rota')) {
		$KOTA['ko_eventgruppen']['rota']['form'] = array(
			'type' => 'switch',
		);
		$KOTA['ko_eventgruppen']['_listview'][50] = array('name' => 'rota', 'sort' => 'rota', 'filter' => TRUE);
		$KOTA['ko_eventgruppen']['rota']['list'] = 'FCN:kota_listview_boolyesno';

		$KOTA['ko_eventgruppen']['rota_teams'] = array(
			'post' => 'FCN:kota_eventgruppen_post_rota_teams',
			'fill' => 'FCN:kota_eventgruppen_fill_rota_teams',
			'form' => array_merge(array(
				'type' => 'doubleselect',
				'dontsave' => TRUE,
				'params' => 'size="7"',
			), kota_get_form('ko_eventgruppen', 'rota_teams')),
		);

	}//if(ko_module_installed(rota))

	if(ko_module_installed('reservation') && in_array($ko_menu_akt, array('daten', 'home'))) {
		kota_ko_reservation_item_id_dynselect($res_values, $res_output, 2);
		$KOTA['ko_eventgruppen']['resitems'] = array(
			'post' => 'intlist',
			'form' => array( 'type' => 'dyndoubleselect',
											'js_func_add' => 'resgroup_doubleselect_add',
											'values' => $res_values,
											'descs' => $res_output,
											'params' => 'size="7"',
											"descicon" => array(
												'icon' => "star",
												'context' => 'highlight',
											),
			)
		);
		$KOTA['ko_eventgruppen']['res_combined'] = array(
			'post' => 'uint',
			'form' => array('type' => 'switch')
		);
		$KOTA['ko_eventgruppen']['res_startzeit'] = array(
			'pre' => "sql_zeit('@VALUE@')",
			'post' => "sql_zeit('@VALUE@')",
			'form' => array('type' => 'text',
											'params' => 'size="11" maxlength="11"',
											),
		);
		$KOTA['ko_eventgruppen']['res_endzeit'] = array(
			'pre' => "sql_zeit('@VALUE@')",
			'post' => "sql_zeit('@VALUE@')",
			'form' => array('type' => 'text',
											'params' => 'size="11" maxlength="11"',
											),
		);
		$KOTA['ko_eventgruppen']['_listview'][70] = array('name' => 'res_combined', 'sort' => 'res_combined', 'filter' => TRUE);
		$KOTA['ko_eventgruppen']['res_combined']['list'] = 'FCN:kota_listview_boolyesno';
		$KOTA['ko_eventgruppen']['responsible_for_res'] = array(
			'form' => array_merge(array(
				'type' => 'select',
			), kota_get_form('ko_eventgruppen', 'responsible_for_res'))
		);
	}//if(ko_module_installed(reservation))

	if(ko_module_installed('groups')) {
		//Add group select to event group (notify)
		$KOTA['ko_eventgruppen']['notify'] = array('post' => 'format_userinput("@VALUE@", "alphanumlist")',
			'form' => array(
				'type' => 'groupsearch',
				'show_add' => TRUE,
			),
		);
	}//if(ko_module_installed(groups))


	// set event program kota
	if (ko_get_setting('activate_event_program') == 1) {
		$KOTA['ko_eventgruppen']['program'] = array(
			'form' => array(
				'type' => 'foreign_table',
				'table' => 'ko_eventgruppen_program',
				'sort_button' => '`time` ASC',
				'new_row' => TRUE,
				'ignore_test' => TRUE,
				'columnWidth' => '12',
			),
		);
	}

	// set event program kota
	if (ko_get_setting('activate_event_program') == 1) {
		$KOTA['ko_eventgruppen_program'] = array(
			'_access' => array(
				'module' => 'daten',
				'key' => 'daten',
				'level' => 3,
			),
			'_multititle' => array(),
			'_inlineform' => array(),
			'_special_cols' => array(
				'crdate' => 'crdate',
				'cruser' => 'cruser',
			),
			"_listview" => array(),
			'_listview_default' => array(),

			"time" => array(
				"list" => "sql_zeit('@VALUE@')",
				"pre" => "sql_zeit('@VALUE@')",
				"post" => "sql_zeit('@VALUE@')",
				"form" => array(
					"type" => "text",
					"params" => 'size="11" maxlength="11"',
					'columnWidth' => '1',
				),
			),
			"team" => array(
				'list' => 'FCN:kota_listview_rota_teams',
				"form" => array(
					'type' => 'dynamicsearch',
					'show_add' => FALSE,
					'columnWidth' => '2',
				),
			),
			'name' => array(
				'pre' => 'ko_html',
				'form' => array(
					'type' => 'text',
					'columnWidth' => '3',
				),
			),
			'title' => array(
				'pre' => 'ko_html',
				'form' => array(
					'type' => 'textarea',
					'params' => 'cols="80" rows="2"',
					'columnWidth' => '3',
				),
			),
			'infrastructure' => array(
				'pre' => 'ko_html',
				'form' => array(
					'type' => 'textarea',
					'params' => 'cols="80" rows="2"',
					'columnWidth' => '3',
				),
			),
		);
	}

	$markFields = ko_array_column($EVENT_PROPAGATE_FIELDS, 'from');
	foreach ($KOTA['ko_eventgruppen'] as $field => &$def) {
		if (in_array($field, $markFields)) {
			$def['form']['descicon'] = array(
				'icon' => "star",
				'context' => 'highlight',
			);
		}
	}
}


if(in_array('ko_rota_teams', $KOTA_TABLES)) {
	$data_eg_id = kota_get_form('ko_rota_teams', 'eventgruppen_id');

	//Prepare array for filter select containing eventgroups
	$filter_eg_id = array();
	foreach($data_eg_id['values'] as $k => $v) {
		if(is_array($v)) {
			foreach($v as $kk => $vv) {
				$filter_eg_id[$vv] = $data_eg_id['descs'][$k].': '.$data_eg_id['descs'][$vv];
			}
		} else {
			$filter_eg_id[$v] = $data_eg_id['descs'][$v];
		}
	}

	$KOTA['ko_rota_teams'] = array(
		'_access' => array(
			'module' => 'rota',
			'chk_col' => 'id',
			'level' => 5,
		),
		'_multititle' => array(
			'name' => '',
		),
		'_listview' => array(
			10 => array('name' => 'name', 'sort' => 'name', 'multiedit' => 'name', 'filter' => TRUE),
			20 => array('name' => 'eg_id', 'multiedit' => FALSE, 'filter' => TRUE),
			30 => array('name' => 'rotatype', 'sort' => 'rotatype', 'multiedit' => FALSE, 'filter' => TRUE),
			40 => array('name' => 'allow_consensus', 'sort' => 'allow_consensus', 'multiedit' => 'allow_consensus', 'filter' => TRUE),
			56 => array("name" => "farbe", "sort" => "farbe", 'multiedit' => 'farbe', 'filter' => FALSE),
		),
		'_inlineform' => array(
			'redraw' => array(
				'sort' => 'sort_rota_teams',
				'fcn' => 'ko_rota_list_teams();'
			),
			'module' => 'rota',
		),
		'_special_cols' => array(
			'crdate' => 'crdate',
			'cruser' => 'cruser',
			'lastchange' => 'lastchange',
			'lastchange_user' => 'lastchange_user',
		),
		'name' => array(
			'list' => 'ko_html;FCN:kota_listview_rootid',
			'form' => array(
				'type' => 'text',
				'params' => 'size="60" maxlength="100"',
			),
		),
		'rotatype' => array(
			'list' => 'getLL("kota_ko_rota_teams_rotatype_@VALUE@")',
			'post' => 'alpha',
			'form' => array(
				'type' => 'select',
				'params' => 'size="0"',
				'values' => array('event', 'day'),
				'descs' => array(getLL('kota_ko_rota_teams_rotatype_event'), getLL('kota_ko_rota_teams_rotatype_day')),
				'noinline' => TRUE,
			),
		),
		'days_range' => array(
			'form' => array(
				'type' => 'days_range',
				'avalues' => [1,2,3,4,5,6,7],
				'values' => [1,2,3,4,5,6,7],
				'descs' => getLL('kota_ko_rota_teams_days_range_values'),
				'activated_days' => [1,2,3,4,5,6,7],
			),
		),
		'eg_id' => array(
			'list' => 'FCN:kota_listview_eventgroups',
			'post' => 'intlist',
			'filter' => array(
				'type' => 'select',
				'params' => 'size="1"',
				'data' => $filter_eg_id,
			),
			'form' => array_merge(array(
				'type' => 'dyndoubleselect',
				'js_func_add' => 'eg_doubleselect_add',
				'params' => 'size="7"',
				'noinline' => TRUE,
			), $data_eg_id),
		),
		'group_id' => array(
			'post' => 'format_userinput("@VALUE@", "group_role")',
			'form' => array(
				'type' => 'groupsearch',
				'show_add' => TRUE,
				'include_roles' => ko_get_setting('rota_showroles') == 1 ? TRUE : FALSE,
			),
		),
		'schedule_subgroup_members' => array(
			'form' => array(
				'type' => 'switch',
			),
		),
		"farbe" => array(
			'list' => 'FCN:kota_listview_color',
			"post" => 'str_replace("#", "", format_userinput("@VALUE@", "alphanum"))',
			"form" => array(
				"type" => "color",
				"params" => 'size="10" maxlength="7"',
			),
		),
		// Consensus
		'allow_consensus' => array(
			'list' => 'FCN:kota_listview_boolyesno',
			'form' => array(
				'type' => 'switch',
				'label_0' => getLL('no'),
				'label_1' => getLL('yes'),
				'value' => '0',
			),
		),
		'consensus_disable_maybe_option' => array(
			'list' => 'FCN:kota_listview_boolyesno',
			'post' => "format_userinput('@VALUE@', 'uint')",
			'form' => array(
				'type' => 'switch',
			),
		),
		'consensus_description' => array(
			'post' => "format_userinput('@VALUE@', 'text')",
			'form' => array(
				'type' => 'textarea',
				'params' => 'rows="5" cols="50"',
			),
		),
		'consensus_max_promises' => array(
			'post' => "format_userinput('@VALUE@', 'uint')",
			'form' => array(
				'type' => 'text',
			),
		),
		'_form_layout' => array(
			'general' => array(
				'group' => FALSE,
				'sorting' => 10,
				'groups' => array(
					'general' => array(
						'sorting' => 10,
						'group' => false,
						'rows' => array(
							10 => array('name' => 6,'rotatype' => 6),
							20 => array('eg_id' => 6,'group_id' => 6),
							30 => array('schedule_subgroup_members' => 6, 'days_range' => 6),
						),
					),
					'consensus' => array(
						'sorting' => 20,
						'group' => true,
						'rows' => array(
							10 => array('allow_consensus' => 6,'consensus_disable_maybe_option' => 6),
							20 => array('consensus_description' => 6,'consensus_max_promises' => 6),
						),
					),
				),
			),
			'_default_cols' => 6,
			'_default_width' => 6,
			'_ignore_fields' => array(),
		),
	);

	//Manual ordering
	if(ko_get_setting('rota_manual_ordering')) {
		ko_get_access('rota');
		if($access['rota']['ALL'] > 4) {
			$KOTA['ko_rota_teams']['_sortable'] = TRUE;
		}
	}

	//Unset group_id if groups module is not installed
	if(!ko_module_installed('groups')) {
		unset($KOTA['ko_rota_teams']['group_id']);
	}
}



if(in_array('ko_donations', $KOTA_TABLES)) {
	$KOTA['ko_donations'] = array(
		'_access' => array(
			'module' => 'donations',
			'chk_col' => 'account',
			'level' => 3,
			'condition' => 'return end(db_select_data("ko_donations_accounts a", "WHERE a.`id` = (SELECT d.`account` FROM `ko_donations` d WHERE d.`id` = @id@)", "archived", "", "", TRUE, TRUE)) ? FALSE : TRUE;',
		),
		"_multititle" => array(
			"date" => "strftime('".$GLOBALS["DATETIME"]["dmY"]."', sql2timestamp('@VALUE@'))",
			"person" => "ko_get_person_name(@VALUE@)",
		),
		'_special_cols' => array(
			'crdate' => 'crdate',
			'cruser' => 'cruser',
			'lastchange' => 'lastchange',
			'lastchange_user' => 'lastchange_user',
		),
		"_listview" => array(
			10 => array("name" => "date", "sort" => "date", 'filter' => TRUE),
			20 => array("name" => "valutadate", "sort" => "valutadate", 'filter' => TRUE),
			30 => array("name" => "account", "sort" => "account", 'filter' => TRUE),
			35 => array("name" => "account_group", "sort" => FALSE, 'filter' => FALSE, 'multiedit' => FALSE),
			40 => array("name" => "amount", "sort" => "amount", 'filter' => TRUE),
			50 => array("name" => "person_firm"),
			60 => array("name" => "person_vorname"),
			70 => array("name" => "person_nachname"),
			80 => array("name" => "person_adresse"),
			90 => array("name" => "person_plz"),
			100 => array("name" => "person_ort"),
			110 => array("name" => "person", "sort" => "person", 'filter' => TRUE),
			120 => array("name" => "source", "sort" => "source", 'filter' => TRUE),
			130 => array("name" => "comment", "sort" => "comment", 'filter' => TRUE),
			140 => array("name" => "thanked", "sort" => "thanked", 'filter' => TRUE),
			150 => array("name" => "crdate", "sort" => "crdate", 'filter' => TRUE, 'multiedit' => FALSE),
		),
		"_listview_default" => array('date', 'valutadate', 'account', 'amount', 'person', 'source', 'comment', 'thanked'),
		"_listview_xls" => array('date', 'valutadate', 'account', 'amount', 'person_firm', 'person_vorname', 'person_nachname', 'person_adresse', 'person_plz', 'person_ort', 'source', 'comment', 'thanked'),

		'_inlineform' => array(
			'redraw' => array(
				'sort' => 'sort_donations',
				'fcn' => 'ko_list_donations();'
			),
			'module' => 'donations',
		),
		"date" => array(
			"list" => "strftime('".$GLOBALS["DATETIME"]["dmY"]."', sql2timestamp('@VALUE@'));FCN:kota_listview_rootid",
			"pre" => "sql2datum('@VALUE@')",
			"post" => "sql_datum('@VALUE@')",
			"form" => array(
				"type" => "jsdate",
				'prefill_new' => TRUE,
			),
		),
		'valutadate' => array(
			'list' => "strftime('".$GLOBALS['DATETIME']['dmY']."', sql2timestamp('@VALUE@'))",
			'pre' => "sql2datum('@VALUE@')",
			'post' => "sql_datum('@VALUE@')",
			'form' => array(
				'type' => 'jsdate',
			),
		),
		"source" => array(
			"form" => array(
				"type" => "textplus",
				"params" => 'maxlength="100"',
				'prefill_new' => TRUE,
			),
			'filter' => array(
				'type' => 'textplus',
				'params' => 'size="0"',
			),
		),  //source
		"account" => array(
			"list" => 'db_get_column("ko_donations_accounts", "@VALUE@", "number,name", " ")',
			"post" => 'uint',
			"form" => array_merge(array(
				"type" => "select",
				"params" => 'size="0"',
				'prefill_new' => TRUE,
			), kota_get_form("ko_donations", "account")),
		),  //account
		"account_group" => array(
			"list" => 'FCN:kota_listview_ko_donations_account_group',
		),
		"person" => array(
			"list" => 'FCN:kota_listview_ko_donations_person',
			"post" => 'uint',
			"form" => array(
				"type" => "peoplesearch",
				'single' => TRUE,
				'noinline' => TRUE,
				'show_add' => TRUE,
			),
		),  //person
		"person_firm" => array(
			"list" => 'FCN:kota_listview_people',
			'list_options' => 'firm',
			'list_col' => 'person',
		),
		"person_vorname" => array(
			"list" => 'FCN:kota_listview_people',
			'list_options' => 'vorname',
			'list_col' => 'person',
		),
		"person_nachname" => array(
			"list" => 'FCN:kota_listview_people',
			'list_options' => 'nachname',
			'list_col' => 'person',
		),
		"person_adresse" => array(
			"list" => 'FCN:kota_listview_people',
			'list_options' => 'adresse',
			'list_col' => 'person',
		),
		"person_plz" => array(
			"list" => 'FCN:kota_listview_people',
			'list_options' => 'plz',
			'list_col' => 'person',
		),
		"person_ort" => array(
			"list" => 'FCN:kota_listview_people',
			'list_options' => 'ort',
			'list_col' => 'person',
		),
		"amount" => array(
			"post" => 'float',
			"form" => array(
				"type" => "text",
				"params" => 'size="10" maxlength="40"'
			),
		),  //amount
		"comment" => array(
			"pre" => "ko_html",
			"form" => array(
				"type" => "textarea",
				"params" => 'cols="40" rows="5"',
			),
		),  //comment
		"reoccuring" => array(
			"pre" => "ko_html",
			"post" => 'alphanum',
			'list' => 'FCN:kota_listview_ll',
			"form" => array_merge(array(
				"type" => "select",
				"params" => 'size="0"',
			), kota_get_form("ko_donations", "reoccuring")),
		),  //reoccuring
		"thanked" => array(
			"list" => 'FCN:kota_listview_boolyesno',
			"form" => array(
				"type" => "switch",
			),
		),
		'crdate' => array(
			'list_options' => 'dmY',
			'list' => 'FCN:kota_listview_datetimecol',
			'filter' => array(
				'type' => 'jsdate',
			)
		),

		/*	'_form_layout' => array(
		10 => array(
			'rows' => array(
				10 => array('title' => 2, 'group_id' => 2),
				20 => array('status' => 2, 'account_id' => 2),
				30 => array('layout' => 2, 'description' => 2),
			),
		),
		20 => array(
			'title' => 'email', // fetches title from LL: kota_layout_group_ko_billing_dossiers_email
			'appearance' => 'default', // choose between 'primary', 'default', 'success', 'warning', 'danger', 'info'
			'rows' => array(
				10 => array('email_subject' => 2, 'email_text' => 2), // 2 means 2 cols per row
				20 => array('email_admonition_subject' => 2, 'email_admonition_text' => 2),
				30 => array('send_email' => 2),
			),
		),
		30 => array(
			'rows' => array(
				10 => array('due_days' => 2, 'due_date' => 2),
				20 => array('admonition_due_days' => 2, 'account_id' => 2),
				30 => array('article_ids' => 2, 'target' => 2),
				40 => array('group_ids' => 2, 'multiple_billings_per_person' => 2),
			),
		),
		'_default_cols' => 6,
		'_default_width' => 6,
	),*/
	);


	if(ko_module_installed("crm")) {
		$KOTA['ko_donations']["crm_project_id"] = [
			"list" => "db_get_column('ko_crm_projects', '@VALUE@', 'number,title', ' ')",
			"form" => array_merge(array(
				"type" => "select",
				"params" => 'size="0"',
			), kota_get_form("ko_crm_projects", "title")),
		];

		$KOTA['ko_donations']['_listview'][] = [
			"name" => "crm_project_id",
			"sort" => "crm_project_id",
			'filter' => TRUE
		];
		$KOTA['ko_donations']['_listview_default'][] = "crm_project_id";
	}

	if(!ko_get_setting('donations_use_repetition')) {
		unset($KOTA['ko_donations']["reoccuring"]);
	}
}


if(in_array('ko_donations_accounts', $KOTA_TABLES)) {
	$KOTA['ko_donations_accounts'] = array(
		'_access' => array(
			'module' => 'donations',
			'chk_col' => 'id',
			'level' => 4,
			'condition' => array('delete' => "return db_get_count('ko_donations', '', 'AND `account` = \'@id@\'') == 0;"),
		),
		"_multititle" => array(
			"number" => "",
			"name" => "",
		),
		'_special_cols' => array(
			'crdate' => 'crdate',
			'cruser' => 'cruser',
			'lastchange' => 'lastchange',
			'lastchange_user' => 'lastchange_user',
		),
		"_listview" => array(
			5 => array("name" => "id"),
			10 => array("name" => "number"),
			20 => array("name" => "name"),
			30 => array("name" => "comment"),
			40 => array("name" => "archived"),
			50 => array("name" => "accountgroup_id"),
		),
		'_inlineform' => array(
			'redraw' => array(
				'cols' => 'number',
				'fcn' => 'ko_list_accounts();'
			),
			'module' => 'donations',
		),
		"name" => array(
			"form" => array(
				"type" => "text",
				"params" => 'size="60"',
			),
		),
		"number" => array(
			'list' => 'ko_html;FCN:kota_listview_rootid',
			"form" => array(
				"type" => "text",
				"params" => 'size="60"',
			),
		),
		"accountgroup_id" => array(
			"list" => "FCN:kota_listview_ko_donations_accountgroups",
			"form" => array_merge(array(
				"type" => "selectplus",
				"id_link" => "ko_donations_accountgroups",
				"async_form" => [
					"tag" => "add_accountgroup",
					"table" => "ko_donations_accountgroups",
				],
				"params" => 'size="0"',
			), kota_get_form("ko_donations_accountgroups", "title")),
		),
		"comment" => array(
			"form" => array(
				"type" => "textarea",
				"params" => 'cols="40" rows="3"',
			),
		),
		"archived" => array(
			'list' => 'FCN:kota_listview_boolyesno',
			'form' => array(
				'type' => 'switch',
			),
		),
	);

	if(ko_module_installed('groups')) {
		$KOTA['ko_donations_accounts']['group_id'] = array('post' => 'format_userinput("@VALUE@", "group_role")',
			'form' => array(
				'type' => 'groupsearch',
				'include_roles' => TRUE,
				'show_add' => TRUE,
			),
		);
	}
	if($access['donations']['ALL'] < 4) {
		$KOTA['ko_donations_accounts']['accountgroup_id']['form']['type'] = 'select';
	}
}


if(in_array('ko_donations_accountgroups', $KOTA_TABLES)) {
	$KOTA['ko_donations_accountgroups'] = array(
		'_access' => array(
			'module' => 'donations',
			'chk_col' => 'id',
			'level' => 4,
		),
		"_multititle" => array(
			"title" => "",
		),
		"_listview" => array(
			10 => array("name" => "title"),
			20 => array("name" => "archived"),
		),
		'_form' => array(
			'redraw' => array(
				'fcn' => "ko_formular_accountgroup('@MODE@', '@ID@')",
			),
			'module' => 'donations',
		),
		'_inlineform' => array(
			'redraw' => array(
				'cols' => 'title',
				'fcn' => 'ko_list_accountgroups();'
			),
			'module' => 'donations',
		),
		'_special_cols' => array(
			'crdate' => 'crdate',
			'cruser' => 'cruser',
			'lastchange' => 'lastchange',
			'lastchange_user' => 'lastchange_user',
		),

		"title" => array(
			"form" => array(
				"type" => "text",
			),
		),
		"archived" => array(
			'list' => 'FCN:kota_listview_boolyesno',
			'form' => array(
				'type' => 'switch',
			),
		),
	);
}


if(in_array('ko_leute', $KOTA_TABLES)) {
	$KOTA['ko_leute'] = array(
		'_access' => array(
			'module' => 'leute',
			'chk_col' => 'ALL&id',
			'level' => 2,
		),
		"_multititle" => array(
			"vorname" => "",
			"nachname" => "",
		),
		'_inlineform' => array(
			'redraw' => array(
				'sort' => 'sort_leute',
				'cols' => array_merge($GLOBALS['COLS_LEUTE_UND_FAMILIE'], array('hidden')),
				'fcn' => $_SESSION['show'] == 'show_my_list' ? 'ko_list_personen(\'my_list\');' : 'ko_list_personen(\'liste\');',
			),
			'module' => 'leute',
		),
		'_form' => array(
			'redraw' => array(
				'fcn' => "ko_formular_leute('@MODE@', '@ID@')",
				'mode_map' => array(
					'new' => 'neu',
				),
			),
			'module' => 'leute',
		),
		'_special_cols' => array(
			'lastchange' => 'lastchange',
			'crdate' => 'crdate',
			'cruser' => 'cruserid',
		),
		'family_data' => array(
			'pre' => 'FCN:kota_pre_leute_family_data',
			'form' => array(
				'type' => 'html',
			),
		),
		"anrede" => array(
			"form" =>
				array_merge(
					array(
						"type" => "textplus",
						"params" => 'size="0"',
					), kota_get_form('ko_leute', 'anrede')
				)
		),
		'hidden' => array(
			'list' => 'FCN:kota_listview_boolx',
			'form' => array(
				'type' => 'switch',
			),
		),
		"firm" => array(
			"pre" => "ko_html",
			"form" => array(
				"type" => "text",
				"params" => 'size="50" maxlength="200"',
			),
		),  //firm
		"department" => array(
			"pre" => "ko_html",
			"form" => array(
				"type" => "text",
				"params" => 'size="50" maxlength="200"',
			),
		),  //department
		"vorname" => array(
			"pre" => "ko_html",
			"form" => array(
				"type" => "text",
				"params" => 'size="50" maxlength="50"',
			),
		),  //vorname
		"nachname" => array(
			"pre" => "ko_html",
			"post" => (($famField = (!$LEUTE_NO_FAMILY && in_array("nachname", $GLOBALS['COLS_LEUTE_UND_FAMILIE']))) ? "FCN:ko_multiedit_familie" : ""),
			"form" => array(
				"title_pre_html" => ($famField ? '<i class="fa fa-group" title="'.getLL("form_leute_family").'"></i>' : ''),
				"headerclass" => ($famField ? 'family-field-header' : ''),
				"contentclass" => ($famField ? 'family_field_with_warning' : ''),
				"type" => "text",
				"params" => 'size="50" maxlength="50"',
			),
		),  //nachname
		"adresse" => array(
			"pre" => "ko_html",
			"post" => (($famField = (!$LEUTE_NO_FAMILY && in_array("adresse", $GLOBALS['COLS_LEUTE_UND_FAMILIE']))) ? "FCN:ko_multiedit_familie" : ""),
			"form" => array(
				"title_pre_html" => ($famField ? '<i class="fa fa-group" title="'.getLL("form_leute_family").'"></i>' : ''),
				"headerclass" => ($famField ? 'family-field-header' : ''),
				"contentclass" => ($famField ? 'family_field_with_warning' : ''),
				"type" => "text",
				"params" => 'size="60" maxlength="100"',
			),
		),  //adresse
		"adresse_zusatz" => array(
			"pre" => "ko_html",
			"post" => (($famField = (!$LEUTE_NO_FAMILY && in_array("adresse_zusatz", $GLOBALS['COLS_LEUTE_UND_FAMILIE']))) ? "FCN:ko_multiedit_familie" : ""),
			"form" => array(
				"title_pre_html" => ($famField ? '<i class="fa fa-group" title="'.getLL("form_leute_family").'"></i>' : ''),
				"headerclass" => ($famField ? 'family-field-header' : ''),
				"contentclass" => ($famField ? 'family_field_with_warning' : ''),
				"type" => "text",
				"params" => 'size="60" maxlength="100"',
			),
		),  //adresse_zusatz
		"plz" => array(
			"pre" => "ko_html",
			"post" => (($famField = (!$LEUTE_NO_FAMILY && in_array("plz", $GLOBALS['COLS_LEUTE_UND_FAMILIE']))) ? "FCN:ko_multiedit_familie" : ""),
			"form" => array(
				"title_pre_html" => ($famField ? '<i class="fa fa-group" title="'.getLL("form_leute_family").'"></i>' : ''),
				"headerclass" => ($famField ? 'family-field-header' : ''),
				"contentclass" => ($famField ? 'family_field_with_warning' : ''),
				"type" => "text",
				"params" => 'size="11" maxlength="11"',
			),
		),  //plz
		"ort" => array(
			"pre" => "ko_html",
			"post" => (($famField = (!$LEUTE_NO_FAMILY && in_array("ort", $GLOBALS['COLS_LEUTE_UND_FAMILIE']))) ? "FCN:ko_multiedit_familie" : ""),
			"form" => array(
				"title_pre_html" => ($famField ? '<i class="fa fa-group" title="'.getLL("form_leute_family").'"></i>' : ''),
				"headerclass" => ($famField ? 'family-field-header' : ''),
				"contentclass" => ($famField ? 'family_field_with_warning' : ''),
				"type" => "text",
				"params" => 'size="50" maxlength="50"',
			),
		),  //ort
		"land" => array(
			"pre" => "ko_html",
			"post" => (($famField = (!$LEUTE_NO_FAMILY && in_array("land", $GLOBALS['COLS_LEUTE_UND_FAMILIE']))) ? "FCN:ko_multiedit_familie" : ""),
			"form" => array_merge(array(
				"title_pre_html" => ($famField ? '<i class="fa fa-group" title="'.getLL("form_leute_family").'"></i>' : ''),
				"headerclass" => ($famField ? 'family-field-header' : ''),
				"contentclass" => ($famField ? 'family_field_with_warning' : ''),
				"type" => "textplus",
				"params" => 'size="0"',
			), kota_get_form("ko_leute", "land")),
		),  //land
		"telp" => array(
			"pre" => "ko_html",
			"post" => (($famField = (!$LEUTE_NO_FAMILY && in_array("telp", $GLOBALS['COLS_LEUTE_UND_FAMILIE']))) ? "FCN:ko_multiedit_familie" : ""),
			"list" => 'FCN:kota_listview_tel',
			'allow_html' => true,
			"form" => array(
				"title_pre_html" => ($famField ? '<i class="fa fa-group" title="'.getLL("form_leute_family").'"></i>' : ''),
				"headerclass" => ($famField ? 'family-field-header' : ''),
				"contentclass" => ($famField ? 'family_field_with_warning' : ''),
				"type" => "text",
				"html_type" => "tel",
				"params" => 'maxlength="30"',
			),
		),  //telp
		"telg" => array(
			"pre" => "ko_html",
			"list" => 'FCN:kota_listview_tel',
			'allow_html' => true,
			"form" => array(
				"type" => "text",
				"html_type" => "tel",
				"params" => 'maxlength="30"',
			),
		),  //telg
		"natel" => array(
			"pre" => "ko_html",
			"list" => 'FCN:kota_listview_tel',
			'allow_html' => true,
			"form" => array(
				"type" => "text",
				"html_type" => "tel",
				"params" => 'maxlength="30"',
			),
		),  //natel
		"fax" => array(
			"pre" => "ko_html",
			"form" => array(
				"type" => "text",
				"html_type" => "tel",
				"params" => 'maxlength="30"',
			),
		),  //fax
		"email" => array(
			"pre" => "ko_html",
			"form" => array(
				"type" => "text",
				"html_type" => "email",
				"params" => 'size="100" maxlength="100"',
			),
		),  //email
		"web" => array(
			"pre" => "ko_html",
			"form" => array(
				"type" => "text",
				"html_type" => "text",
				"params" => '',
			),
		),  //web
		"geburtsdatum" => array(
			'list' => "sql2datum('@VALUE@')",
			"pre" => "sql2datum('@VALUE@')",
			"post" => "sql_datum('@VALUE@')",
			"form" => array(
				"type" => "jsdate",
				"params" => 'size="11" maxlength="11"',
			),
		),  //geburtsdatum
		"zivilstand" => array(
			'list' => "FCN:kota_listview_ll",
			"pre" => "ko_html",
			"form" => array(
				"type" => "select",
				"params" => 'size="0"',
				"values" => ($vs = array('','single','married','separated','divorced','widowed', 'partner')),
				"descs" => kota_array_ll($vs, 'ko_leute', 'zivilstand'),
			),
		),  //zivilstand
		"geschlecht" => array(
			'list' => "FCN:kota_listview_ll",
			"pre" => "ko_html",
			"form" => array(
				"type" => "select",
				"params" => 'size="0"',
				"values" => ($vs = array('','m', 'w')),
				"descs" => kota_array_ll($vs, 'ko_leute', 'geschlecht'),
			),
		),  //geschlecht
		"memo1" => array(
			"pre" => "ko_html",
			"form" => array(
				"type" => "textarea",
				"params" => 'cols="50" rows="4"',
			),
		),  //memo1
		"memo2" => array(
			"pre" => "ko_html",
			"form" => array(
				"type" => "textarea",
				"params" => 'cols="50" rows="4"',
			),
		),  //memo2
		"famfunction" => array(
			'list' => "FCN:kota_listview_ll",
			"pre" => "ko_html",
			"form" => array(
				"type" => "select",
				"params" => 'size="0"',
				"values" => ($vs = array('','husband', 'wife', 'child')),
				"descs" => kota_array_ll($vs, 'ko_leute', 'famfunction'),
			),
		),  //famfunction
		"father" => array(
			'allow_html' => TRUE,
			'processIfEmpty' => TRUE,
			'list' => "FCN:kota_listview_father_link",
			'pdf' => "FCN:kota_listview_father",
			'filter' => array(
				'type' => 'peoplesearch',
			),
			'form' => array(
				'type' => 'peoplesearch',
				'noinline' => TRUE,
				'single' => TRUE,
			),
		), //father
		"mother" => array(
			'allow_html' => TRUE,
			'processIfEmpty' => TRUE,
			'list' => "FCN:kota_listview_mother_link",
			'pdf' => "FCN:kota_listview_mother",
			'filter' => array(
				'type' => 'peoplesearch',
			),
			'form' => array(
				'type' => 'peoplesearch',
				'noinline' => TRUE,
				'single' => TRUE,
			),
		), //mother
		"spouse" => array(
			'allow_html' => TRUE,
			'list' => "FCN:kota_listview_people_link",
			'pdf' => "FCN:kota_listview_people",
			'filter' => array(
				'type' => 'peoplesearch',
			)
		), //spouse
		'rectype' => array(
			'list' => 'FCN:kota_listview_ll',
			'pre' => 'ko_html',
			'form' => array_merge(array(
				'type' => 'select',
				'params' => 'size="0"',
			), kota_get_form('ko_leute', 'rectype')),
		),
		'crdate' => array(
			'list_options' => 'dmY',
			'list' => 'FCN:kota_listview_datecol',
			'filter' => array(
				'type' => 'jsdate',
			)
		),
		'lastchange' => array(
			'list_options' => 'dmY',
			'list' => 'FCN:kota_listview_datecol',
		),
		'MODULEsalutation_formal' => array(
			'list' => 'FCN:kota_listview_salutation_formal',
		),
		'MODULEsalutation_informal' => array(
			'list' => 'FCN:kota_listview_salutation_informal',
		),
		'MODULEfamid_title' => array(
			'list' => 'FCN:kota_listview_title_household',
			'xls' => 'FCN:kota_xls_title_household',
			'pdf' => 'FCN:kota_xls_title_household',
		),
		'_form_layout' => array(
			'general' => array(
				'group' => FALSE,
				'sorting' => 10,
				'groups' => array(
					'family' => array(
						'sorting' => 10,
						'group' => TRUE,
						'rows' => array(
							10 => array('family_data' => 12),
						),
					),
					'general' => array(
						'sorting' => 20,
						'group' => FALSE,
						'rows' => array(
							10 => array("anrede" => 6, "hidden" => 6),
							20 => array("firm" => 6, "department" => 6),
							30 => array("vorname" => 6, "nachname" => 6),
							40 => array("adresse" => 6, "adresse_zusatz" => 6),
							50 => array("plz" => 6, "ort" => 6),
							60 => array("land" => 6),
						),
					),
					'contact' => array(
						'sorting' => 30,
						'group' => FALSE,
						'show_save' => TRUE,
						'rows' => array(
							10 => array("telp" => 6, "telg" => 6),
							20 => array("natel" => 6, "fax" => 6),
							30 => array("email" => 6, "web" => 6),
							40 => array("zivilstand" => 6, "geschlecht" => 6),
							50 => array("geburtsdatum" => 6),
						),
					),
					'membership' => array(
						'sorting' => 40,
						'show_save' => TRUE,
						'group' => TRUE,
						'rows' => array(
							//10 => array("groups" => 12),
							//20 => array("groups_datafields" => 12),
							//30 => array("groups_history" => 12),
							//40 => array("assignment_history" => 12),
							//50 => array("smallgroups" => 12),
						),
					),
					'memo' => array(
						'sorting' => 50,
						'group' => FALSE,
						'rows' => array(
							10 => array("memo1" => 6, "memo2" => 6),
						),
					),
				),
			),
			'data' => array(
				'group' => TRUE,
				'sorting' => 2000,
				'groups' => array(),
			),
			'_default_cols' => 6,
			'_default_width' => 6,
			'_ignore_fields' => array(),
		),
	);


	if(ko_get_setting('leute_information_lock')) {
		$KOTA['ko_leute']['information_lock'] = array(
			'list' => 'FCN:kota_listview_boolyesno',
			'form' => array(
				'type' => 'switch',
			),
		);
	}

	if(ko_module_installed("telegram")) {
		$KOTA['ko_leute']['MODULEtelegram_token'] = array(
			'list' => 'FCN:kota_listview_telegram_token',
			'allow_html' => TRUE,
		);

		$KOTA['ko_leute']['telegram_id'] = array(
			'list' => 'FCN:kota_listview_telegram_registration',
			'allow_html' => TRUE,
		);
	}


	//info_billing: sorting 11

	//Info about person's donations
	if(ko_module_installed('donations')) {
		$KOTA['ko_leute']['_form_layout']['data']['groups']['donations'] = array(
			'sorting' => 20,
			'group' => TRUE,
			'rows' => array(10 => array('info_donations' => 12)),
		);

		$KOTA['ko_leute']['info_donations'] = array(
			'pre' => 'FCN:kota_pre_ko_leute_info_donations',
			'form' => array(
				'type' => 'html',
				'ignore_test' => TRUE,
				'dontsave' => TRUE,
			),
		);
	}
	//Info about person's taxonomy keywords
	if(ko_module_installed('taxonomy')) {
		$KOTA['ko_leute']['_form_layout']['data']['groups']['taxonomy'] = array(
			'sorting' => 30,
			'group' => TRUE,
			'rows' => array(10 => array('info_taxonomy' => 12)),
		);

		$KOTA['ko_leute']['info_taxonomy'] = array(
			'pre' => 'FCN:kota_pre_ko_leute_info_taxonomy',
			'form' => array(
				'type' => 'html',
				'ignore_test' => TRUE,
				'dontsave' => TRUE,
			),
		);
	}
	//Info about person's group subscriptions from events
	if(ko_module_installed('daten') && ko_module_installed('groups') && ko_get_setting('daten_gs_pid')) {
		$KOTA['ko_leute']['_form_layout']['data']['groups']['gs'] = array(
			'sorting' => 40,
			'group' => TRUE,
			'rows' => array(10 => array('info_gs' => 12)),
		);

		$KOTA['ko_leute']['info_gs'] = array(
			'pre' => 'FCN:kota_pre_ko_leute_info_gs',
			'form' => array(
				'type' => 'html',
				'ignore_test' => TRUE,
				'dontsave' => TRUE,
			),
		);
	}

	//Info about person's rota activity
	if(ko_module_installed('rota')) {
		$KOTA['ko_leute']['_form_layout']['data']['groups']['rota'] = array(
			'sorting' => 40,
			'group' => TRUE,
			'rows' => array(10 => array('info_rota_1' => 6, 'info_rota_2' => 6)),
		);
		$KOTA['ko_leute']['info_rota_1'] = array(
			'pre' => 'FCN:kota_pre_ko_leute_info_rota_1',
			'form' => array(
				'type' => 'html',
				'ignore_test' => TRUE,
				'dontsave' => TRUE,
			),
		);
		$KOTA['ko_leute']['info_rota_2'] = array(
			'pre' => 'FCN:kota_pre_ko_leute_info_rota_2',
			'form' => array(
				'type' => 'html',
				'ignore_test' => TRUE,
				'dontsave' => TRUE,
			),
		);
	}

	//Info about person's references in other records. E.g. father/mother, spouse, contact_id etc
	$KOTA['ko_leute']['info_ref'] = array(
		'pre' => 'FCN:kota_pre_ko_leute_info_ref',
		'form' => array(
			'type' => 'html',
			'ignore_test' => TRUE,
			'dontsave' => TRUE,
		),
	);
	$KOTA['ko_leute']['_form_layout']['data']['groups']['ref'] = array(
		'sorting' => 50,
		'group' => TRUE,
		'rows' => array(10 => array('info_ref' => 6)),
	);
	if(ko_module_installed('admin')) {
		ko_get_access('admin');
		if($access['admin']['ALL'] > 3) {
			$KOTA['ko_leute']['_form_layout']['data']['groups']['ref']['rows'] = array(10 => array('info_ref' => 4, 'info_login' => 8));
			$KOTA['ko_leute']['info_login'] = array(
				'pre' => 'FCN:kota_pre_ko_leute_info_login',
				'form' => array(
					'type' => 'html',
					'ignore_test' => TRUE,
					'dontsave' => TRUE,
				),
			);
		}
	}


	if(ko_module_installed('kg')) {
		$KOTA['ko_leute']['smallgroups'] = array(
			'list' => 'FCN:kota_listview_smallgroups',
			'form' => array_merge(
				array(
					'type' => 'doubleselect',
					"size" => array(
						"for_filter" => 6,
						"normal" => 8,
					),
					'show_filter' => TRUE,
				), kota_get_form('ko_leute', 'smallgroups')
			),
		);
		/*
		$KOTA['ko_leute']['MODULE::kg_seit'] = array(
			'pre' => 'FCN:kota_pre_leute_MODULEkg_seit',
			'form' => array(
				'type' => 'html',
			),
		);
		*/

		$KOTA['ko_leute']['_form_layout']['general']['groups']['membership']['rows'][50] = array("smallgroups" => 12);
		//$KOTA['ko_leute']['_form_layout']['membership']['rows'][60] = array("MODULE::kg_seit" => 12);
	}

	if(ko_module_installed('groups')) {
		ko_get_access('groups');

		$KOTA['ko_leute']['groups'] = array(
			'list' => 'FCN:kota_listview_ko_leute_groups',
			'xls' => 'FCN:kota_xls_ko_leute_groups',
			'form' => array(
				'type' => 'groupselect',
			),
		);
		$KOTA['ko_leute']['groups_datafields'] = array(
			'list' => 'FCN:kota_pre_leute_groups_datafields',
			'pre' => 'FCN:kota_pre_leute_groups_datafields',
			'form' => array(
				'type' => 'html',
			),
		);
		$KOTA['ko_leute']['groups_history'] = array(
			'list' => 'FCN:kota_pre_leute_groups_history',
			'pre' => 'FCN:kota_pre_leute_groups_history',
			'form' => array(
				'type' => 'html',
			),
		);
		$KOTA['ko_leute']['assignment_history'] = array(
			'pre' => 'FCN:kota_pre_ko_leute_assignment_history',
			'form' => array(
				'type' => 'html',
				'ignore_test' => TRUE,
				'dontsave' => TRUE,
			),
		);

		$KOTA['ko_leute']['_form_layout']['general']['groups']['membership']['rows'][10] = array("groups" => 12);
		$KOTA['ko_leute']['_form_layout']['general']['groups']['membership']['rows'][20] = array("groups_datafields" => 12);
		$KOTA['ko_leute']['_form_layout']['general']['groups']['membership']['rows'][30] = array("groups_history" => 12);
		$KOTA['ko_leute']['_form_layout']['general']['groups']['membership']['rows'][40] = array("assignment_history" => 12);


		$z_where = "AND (`start` = '0000-00-00' OR `start` < NOW()) AND (`stop` = '0000-00-00' OR `stop` > NOW())";
		ko_get_groups($groups, $z_where);
		ko_get_grouproles($roles);
		foreach($groups as $group) {

			if($access['groups']['ALL'] > 1 || $access['groups'][$group['id']] > 1) {
				if($group['type'] != 1) {
					list($values, $descs, $all_descs) = ko_groups_get_group_id_names($group['id'], $groups, $roles);
					$KOTA['ko_leute']['MODULEgrp'.$group['id']] = array(
						'list' => 'FCN:kota_map_leute_daten',
						'form' => array(
							'desc' => $group['name'],
							'type' => 'doubleselect',
							'params' => 'size="4"',
							'values' => $values,
							'descs' => $descs,
							'all_descs' => $all_descs,
						),
					);
					$KOTA['ko_leute']['_form_layout']['_ignore_fields'][] = 'MODULEgrp'.$group['id'];
				}

				//Datafields for multiedit
				foreach(explode(',', $group['datafields']) as $df) {
					if(!$df) continue;
					//Only dummy definition, will be definied properly as html element in ko_multiedit_formular()
					$KOTA['ko_leute']['MODULEgrp'.$group['id'].':'.$df] = array(
						'form' => array(
							'type' => 'html',
						)
					);
					$KOTA['ko_leute']['_form_layout']['_ignore_fields'][] = 'MODULEgrp'.$group['id'].':'.$df;
				}
			}
		}
	}

	if (ko_module_installed("taxonomy") && $access['taxonomy']['ALL'] >= 1) {
		$KOTA['ko_leute']['terms'] = [
			'list' => 'FCN:kota_listview_ko_leute_terms',
		];
	}

	if (ko_module_installed("daten") && $access['daten']['ABSENCE'] >= 1) {
		$KOTA['ko_leute']['absence'] = [
			'list' => 'FCN:kota_listview_ko_leute_absence',
		];
	}
}



if(in_array('ko_event_absence', $KOTA_TABLES)) {
	$KOTA['ko_event_absence'] = array(
		'_access' => array(
			'module' => 'daten',
			'chk_col' => '',
			'level' => 1,
		),
		'_special_cols' => array(
			'crdate' => 'crdate',
			'cruser' => 'cruser',
		),
		'_listview' => array(
			10 => array('name' => 'leute_id', 'sort' => 'leute_id', 'multiedit' => FALSE, 'filter' => TRUE),
			20 => array('name' => 'from_date', 'sort' => 'from_date', 'multiedit' => FALSE, 'filter' => TRUE),
			30 => array('name' => 'to_date', 'sort' => 'to_date', 'multiedit' => FALSE, 'filter' => TRUE),
			40 => array('name' => 'type', 'sort' => 'type', 'multiedit' => FALSE, 'filter' => TRUE),
			50 => array('name' => 'description', 'sort' => 'description', 'multiedit' => FALSE, 'filter' => TRUE),
		),
		'_inlineform' => array(
			'redraw' => array(
				'fcn' => 'ko_daten_list_absence();'
			),
			'module' => "daten",
		),
		"leute_id" => array(
			'list' => 'FCN:kota_listview_ko_event_absence_person',
			'post' => 'uint',
			'form' => array(
				'type' => 'peoplesearch',
				'single' => TRUE,
				'mandatory' => TRUE,
				'prefill_new' => TRUE,
				'noinline' => TRUE,
			),
		),
		"type" => array(
			"list" => "FCN:kota_listview_ll",
			"form" => array(
				"type" => "select",
				"params" => 'size="0"',
				"values" => ($vs = array('','free','vacation','training','school','compensation','army','civilservice','sick','other')),
				"descs" => kota_array_ll($vs, 'ko_event_absence', 'type'),
				'mandatory' => TRUE,
				'prefill_new' => TRUE
			),
		),
		"from_date" => array(
			"list" => 'FCN:kota_listview_datecol',
			"pre" => "sql2datum('@VALUE@')",
			"post" => "sql_datum('@VALUE@')",
			"form" => array(
				"type" => "jsdate",
				"mandatory" => TRUE,
				'prefill_new' => TRUE,
				"sibling" => "to_date",
			),
		),
		"to_date" => array(
			"list" => 'FCN:kota_listview_datecol',
			"pre" => "sql2datum('@VALUE@')",
			"post" => "sql_datum('@VALUE@')",
			"form" => array(
				"type" => "jsdate",
				"mandatory" => TRUE,
				'prefill_new' => TRUE,
			),
		),
		'description' => array(
			'list' => 'FCN:kota_listview_longtext25',
			'form' => array(
				'type' => 'textarea',
				'params' => 'cols="50" rows="4"',
				'prefill_new' => TRUE

			),
		),


	);
}


if(in_array('ko_kleingruppen', $KOTA_TABLES)) {
	$KOTA['ko_kleingruppen'] = array(
		'_access' => array(
			'module' => 'kg',
			'chk_col' => '',
			'level' => 3,
		),
		"_multititle" => array(
			"name" => "",
		),
		'_special_cols' => array(
			'lastchange' => 'lastchange',
			'lastchange_user' => 'lastchange_user',
			'crdate' => 'crdate',
			'cruser' => 'cruser',
		),
		'_inlineform' => array(
			'redraw' => array(
				'sort' => 'sort_kg',
				'fcn' => 'ko_list_kg(FALSE);'
			),
			'module' => 'leute|kg',
		),
		'_supermodule' => 'leute',
		"_listview" => array(
			10 => array('name' => 'name', 'sort' => 'name', 'multiedit' => 'name', 'filter' => TRUE),
			20 => array('name' => 'alter', 'sort' => 'alter', 'multiedit' => 'alter', 'filter' => TRUE),
			30 => array('name' => 'geschlecht', 'sort' => 'geschlecht', 'multiedit' => 'geschlecht', 'filter' => TRUE),
			40 => array('name' => 'wochentag', 'sort' => 'wochentag', 'multiedit' => 'wochentag', 'filter' => TRUE),
			50 => array('name' => 'ort', 'sort' => 'ort', 'multiedit' => 'ort', 'filter' => TRUE),
			60 => array('name' => 'zeit', 'sort' => 'zeit', 'multiedit' => 'zeit', 'filter' => TRUE),
			70 => array('name' => 'treffen', 'sort' => 'treffen', 'multiedit' => 'treffen', 'filter' => TRUE),
			80 => array('name' => 'anz_frei', 'sort' => 'anz_frei', 'multiedit' => 'anz_frei', 'filter' => TRUE),
			85 => array('name' => 'anz_leute', 'sort' => 'anz_leute', 'multiedit' => FALSE),
			90 => array('name' => 'kg-gen', 'sort' => 'kg-gen', 'multiedit' => 'kg-gen', 'filter' => TRUE),
			100 => array('name' => 'type', 'sort' => 'type', 'multiedit' => 'type', 'filter' => TRUE),
			110 => array('name' => 'region', 'sort' => 'region', 'multiedit' => 'region', 'filter' => TRUE),
			120 => array('name' => 'comments', 'sort' => 'comments', 'multiedit' => 'comments', 'filter' => TRUE),
			130 => array('name' => 'picture', 'sort' => 'picture', 'multiedit' => 'picture'),
			140 => array('name' => 'url', 'sort' => 'url', 'multiedit' => 'url', 'filter' => TRUE),
			150 => array('name' => 'eventGroupID', 'sort' => 'eventGroupID', 'multiedit' => 'eventGroupID'),
			//160 for mailing_alias
			170 => array('name' => 'crdate', 'sort' => 'crdate', 'multiedit' => FALSE, 'filter' => TRUE),
			180 => array('name' => 'lastchange', 'sort' => 'lastchange', 'multiedit' => FALSE, 'filter' => TRUE),
			//500-530: used for roles
		),
		'_listview_default' => array('name'),

		"name" => array(
			'list' => 'FCN:kota_listview_ko_kleingruppen_name',
			"pre" => "ko_html",
			"form" => array(
				"type" => "text",
			),
		),  //name
		"type" => array(
			"form" => array(
				"type" => "textplus",
				"select_case_sensitive" => TRUE,
			),
		),  //type
		"alter" => array(
			"pre" => "ko_html",
			"post" => 'int',
			"form" => array(
				"type" => "text",
			),
		),  //alter
		"geschlecht" => array(
			"list" => "FCN:kota_listview_ll",
			"form" => array(
				"type" => "select",
				"params" => 'size="0"',
				"values" => ($vs = array('','m','w','mixed')),
				"descs" => kota_array_ll($vs, 'ko_kleingruppen', 'geschlecht'),
			),
		),  //geschlecht
		"wochentag" => array(
			"list" => "FCN:kota_listview_ll",
			"form" => array(
				"type" => "select",
				"params" => 'size="0"',
				"values" => ($vs = array('','monday','tuesday','wednesday','thursday','friday','saturday','sunday')),
				"descs" => kota_array_ll($vs, 'ko_kleingruppen', 'wochentag'),
			),
		),  //wochentag
		"ort" => array(
			"pre" => "ko_html",
			"form" => array(
				"type" => "text",
			),
		),  //ort
		"zeit" => array(
			"form" => array(
				"type" => "text",
			),
		),  //zeit
		"treffen" => array(
			"list" => "FCN:kota_listview_ll",
			"form" => array(
				"type" => "select",
				"params" => 'size="0"',
				"values" => ($vs = array('','weekly','biweekly','once a month','twice a month','threetimes a month')),
				"descs" => kota_array_ll($vs, 'ko_kleingruppen', 'treffen'),
			),
		),  //treffen
		"anz_frei" => array(
			"pre" => "ko_html",
			"post" => 'int',
			"form" => array(
				"type" => "text",
				"params" => 'size="4"',
			),
		),  //anz_frei
		"kg-gen" => array(
			"pre" => "ko_html",
			"post" => 'int',
			"form" => array(
				"type" => "text",
				"params" => 'size="9"',
			),
		),  //kg-gen
		"region" => array(
			"form" => array(
				"type" => "textplus",
			),
		),  //region
		"comments" => array(
			"pre" => "ko_html",
			"form" => array(
				"type" => "textarea",
				"params" => 'cols="30" rows="4"',
			),
		),  //comments
		"picture" => array(
			'list' => 'FCN:kota_pic_tooltip',
			"form" => array(
				'noinline' => TRUE,
				"type" => "file",
				"params" => '',
			),
		),  //picture
		"url" => array(
			"pre" => "ko_html",
			"form" => array(
				"type" => "text",
			),
		),  //url
		"eventGroupID" => array(
			'list' => 'FCN:kota_listview_eventgroup_name',
			"pre" => "ko_html",
			"post" => 'uint',
			"form" => array_merge(array(
				"type" => "select",
				"params" => 'size="0"',
			), kota_get_form("ko_kleingruppen", "eventGroupID")),
		),  //eventGroupID
		'crdate' => array(
			'list' => 'FCN:kota_listview_datetimecol',
			'filter' => array(
				'type' => 'jsdate',
			),
		),
		'lastchange' => array(
			'list' => 'FCN:kota_listview_datetimecol',
			'filter' => array(
				'type' => 'jsdate',
			),
		),
	);

	if(ko_module_installed('mailing')) {
		$KOTA['ko_kleingruppen']['mailing_alias'] = array(
			'post' => 'FCN:kota_mailing_check_unique_alias',
			'list' => 'FCN:kota_mailing_link_alias',
			'form' => array(
				'type' => 'text',
				'params' => 'size="40"',
			),
		);

		$KOTA['ko_kleingruppen']['_listview'][160] = array('name' => 'mailing_alias', 'sort' => 'mailing_alias', 'multiedit' => 'mailing_alias', 'filter' => TRUE);
	}

	$role_listview_counter = 500;
	foreach($SMALLGROUPS_ROLES as $role) {
		$KOTA['ko_kleingruppen']['members_'.$role] = array(
			'fill' => 'FCN:kota_smallgroup_members_fill',
			'post' => 'FCN:kota_smallgroup_members_post',
			'form' => array(
				'dontsave' => TRUE,
				'type' => 'peoplesearch',
				'params' => 'size="7" style="width:150px;"',
			),
		);
		//Add roles as new columns
		$KOTA['ko_kleingruppen']['role_'.$role] = array('list' => 'FCN:kota_listview_people_link');
		$KOTA['ko_kleingruppen']['_listview'][$role_listview_counter++] = array('name' => 'role_'.$role);
	}

}



if(in_array('ko_reservation', $KOTA_TABLES) || in_array('ko_reservation_mod', $KOTA_TABLES)) {
	$KOTA['ko_reservation'] = array(
		'_access' => array(
			'module' => 'reservation',
			'chk_col' => 'item_id',
			'level' => 4,
		),
		"_multititle" => array(
			"item_id" => "ko_get_resitem_name('@VALUE@')",
			"startdatum" => "sql2datum('@VALUE@')",
			"zweck" => "",
		),
		'_special_cols' => array(
			'crdate' => 'cdate',
			'cruser' => 'user_id',
			'lastchange' => 'last_change',
			'lastchange_user' => 'lastchange_user',
		),
		'_listview' => array(
			10 => array('name' => 'item_id', 'sort' => 'item_id'),
			20 => array('name' => 'startdatum', 'sort' => 'startdatum', 'multiedit' => 'startdatum,enddatum', 'filter' => 'startdatum'),
			30 => array('name' => 'startzeit', 'sort' => 'startzeit', 'multiedit' => 'startzeit,endzeit', 'filter' => TRUE),
			40 => array('name' => 'zweck', 'sort' => 'zweck', 'multiedit' => 'zweck', 'filter' => TRUE),
			50 => array('name' => 'name', 'sort' => 'name', 'multiedit' => 'name', 'filter' => TRUE),
			51 => array('name' => 'email', 'sort' => 'email', 'multiedit' => 'email', 'filter' => TRUE),
			52 => array('name' => 'telefon', 'sort' => 'telefon', 'multiedit' => 'telefon', 'filter' => TRUE),
			60 => array('name' => 'comments', 'sort' => 'comments', 'multiedit' => 'comments', 'filter' => TRUE),
			//40 is reserved for purpose (see below)
			//50 is reserved for name with email and telephone (see below)
			//60 is reserved for comments (see below)
			//70 is reserved for event_id (see below)
			80 => array("name" => "cdate", "sort" => 'cdate', "filter" => TRUE, 'multiedit' => FALSE),
			90 => array("name" => "user_id", "sort" => 'user_id', "filter" => TRUE, 'multiedit' => FALSE),
		),
		'_listview_default' => array('item_id', 'startdatum', 'startzeit', 'zweck', 'name'),
		'_inlineform' => array(
			'redraw' => array(
				'sort' => 'sort_item',
				'fcn' => 'ko_list_reservations();'
			),
			'module' => 'reservation',
		),
		'_form' => array(
			'redraw' => array(
				'fcn' => "ko_formular_reservation('@MODE@', '@ID@')",
				'mode_map' => array(
					'new' => 'neu',
				),
			),
			'module' => 'reservation',
		),
		"item_id" => array(
			"post" => 'intlist',
			"list" => "ko_get_resitem_name('@VALUE@');FCN:kota_listview_rootid",
			"form" => array_merge(array(
				"type" => "dynselect",
				"js_func_add" => "resgroup_select_add",
				"params" => 'size="5"',
				"new_row" => TRUE,
				"colspan" => 'colspan="2"',
				"add_class" => 'res-conflict-field',
				"mandatory" => TRUE,
			), kota_get_form("ko_reservation", "item_id")),
		),  //item_id
		"startdatum" => array(
			"list" => 'FCN:kota_listview_date',
			"xls" => "sql2datum('@VALUE@')",
			"pre" => "sql2datum('@VALUE@')",
			"post" => "sql_datum('@VALUE@')",
			"form" => array(
				"type" => "jsdate",
				"add_class" => 'res-conflict-field',
				"mandatory" => TRUE,
				'sibling' => 'enddatum',
			),
			'filter' => array(
				'type' => 'select',
				'data' => array(
					2 => strftime('%A', mktime(1,1,1, 1, 1, 2018)),  //Monday
					3 => strftime('%A', mktime(1,1,1, 1, 2, 2018)),
					4 => strftime('%A', mktime(1,1,1, 1, 3, 2018)),
					5 => strftime('%A', mktime(1,1,1, 1, 4, 2018)),
					6 => strftime('%A', mktime(1,1,1, 1, 5, 2018)),
					7 => strftime('%A', mktime(1,1,1, 1, 6, 2018)),
					1 => strftime('%A', mktime(1,1,1, 1, 7, 2018)),  //Sunday
				),
				'sql' => "DAYOFWEEK([TABLE].[FIELD]) [NOTEQUAL]= '[VALUE]'",
				'list' => 'FCN:kota_listview_dayofweek',
			),
		),  //startdatum
		"enddatum" => array(
			'pre' => 'FCN:kota_pre_enddate',
			"xls" => "FCN:kota_xls_enddate",
			'post' => 'FCN:kota_post_enddate',
			"form" => array(
				"type" => "jsdate",
				"add_class" => 'res-conflict-field',
			),
		),  //enddatum
		"startzeit" => array(
			"list" => 'FCN:kota_listview_time',
			"xls" => "sql_zeit('@VALUE@')",
			"pre" => "sql_zeit('@VALUE@')",
			"post" => "sql_zeit('@VALUE@')",
			'filter' => array(
				'type' => 'time',
			),
			"form" => array(
				"type" => "text",
				"params" => 'size="11" maxlength="11"',
				"add_class" => 'res-conflict-field',
			),
		),  //startzeit
		"endzeit" => array(
			"pre" => "sql_zeit('@VALUE@')",
			"post" => "sql_zeit('@VALUE@')",
			"list" => "",
			"xls" => "sql_zeit('@VALUE@')",
			'filter' => array(
				'type' => 'time',
			),
			"form" => array(
				"type" => "text",
				"params" => 'size="11" maxlength="11"',
				"add_class" => 'res-conflict-field',
			),
		),  //endzeit
		"zweck" => array(
			'list' => 'FCN:kota_listview_ko_reservation_zweck',
			"pre" => 'ko_html',
			"form" => array(
				"type" => "text",
				"params" => 'size="60" maxlength="255"',
			),
		),  //zweck
		"name" => array(
			"pre" => 'ko_html',
			"form" => array(
				"type" => "text",
				"params" => 'size="60" maxlength="255"',
			),
		),  //name
		"email" => array(
			"pre" => 'ko_html',
			"post" => 'email',
			"form" => array(
				"type" => "text",
				"html_type" => "email",
				"params" => 'size="60" maxlength="255"',
			),
		),  //email
		"telefon" => array(
			"pre" => 'ko_html',
			"post" => 'alphanum++',
			"list" => 'FCN:kota_listview_tel',
			'allow_html' => true,
			"form" => array(
				"type" => "text",
				"html_type" => "tel",
				"params" => 'size="60" maxlength="255"',
			),
		),  //telefon
		"comments" => array(
			"pre" => 'ko_html',
			"form" => array(
				"type" => "textarea",
				"params" => 'rows="3" cols="50"',
			),
		),  //comments
		"cdate" => array(
			'list' => 'FCN:kota_listview_datetimecol',
			'filter' => array(
				'type' => 'jsdate',
			),
		), //cdate
		"user_id" => array(
			'list' => 'FCN:kota_listview_login',
		),//user_id
	);

	if (ko_module_installed('daten')) {
		$KOTA['ko_reservation']['_listview'][70] = array('name' => 'event_id', 'sort' => FALSE, 'multiedit' => FALSE);
		$KOTA['ko_reservation']['event_id'] = array(
			'list' => 'FCN:kota_listview_reservation_event_id',
		);
	}

	//Remove columns for guest according to setting
	if ($_SESSION['ses_userid'] == ko_get_guest_id()) {
		$guest_fields = array_merge(explode(',', ko_get_setting('res_show_fields_to_guest')), $RES_GUEST_FIELDS_FORCE);
		foreach ($KOTA['ko_reservation']['_listview'] as $k => $v) {
			if (!in_array($v['name'], $guest_fields)) unset($KOTA['ko_reservation']['_listview'][$k]);
		}
	}

}


if(in_array('ko_reservation', $KOTA_TABLES) || in_array('ko_reservation_mod', $KOTA_TABLES)) {
	$KOTA['ko_reservation_mod'] = array(
		'_access' => array(
			'module' => 'reservation',
			'chk_col' => 'item_id',
			'level' => 4,
		),
		'_multititle' => array(
			'item_id' => "ko_get_resitem_name('@VALUE@')",
			'startdatum' => "sql2datum('@VALUE@')",
			'zweck' => '',
		),
		'_special_cols' => array(
			'crdate' => 'cdate',
			'cruser' => 'user_id',
			'lastchange' => 'last_change',
			'lastchange_user' => 'lastchange_user',
		),
		'_listview' => array(
			10 => array('name' => 'item_id', 'sort' => FALSE, 'multiedit' => 'item_id', 'filter' => TRUE),
			20 => array('name' => 'startdatum', 'sort' => 'startdatum', 'multiedit' => 'startdatum,enddatum', 'filter' => FALSE),
			30 => array('name' => 'startzeit', 'sort' => 'startzeit', 'multiedit' => 'startzeit,endzeit', 'filter' => FALSE),
			40 => array('name' => 'zweck', 'sort' => 'zweck', 'multiedit' => 'zweck,comments', 'filter' => TRUE),
			50 => array('name' => 'name', 'sort' => 'name', 'multiedit' => 'name,email,telefon', 'filter' => TRUE),
			60 => array('name' => 'comments', 'sort' => 'comments', 'multiedit' => 'comments', 'filter' => TRUE),
			// 70 => array('name' => 'event_id', 'sort' => FALSE, 'multiedit' => FALSE),
		),
		'_inlineform' => array(
      'redraw' => array(
        'sort' => 'sort_item',
        'fcn' => 'ko_show_res_liste("mod");'
      ),
      'module' => 'reservation',
    ),

		'item_id' => array(
			'list' => "FCN:kota_listview_ko_reservation_mod_item_id",
		),
		'startdatum' => array(
			'list' => 'FCN:kota_listview_date',
			'list_options' => 'ddmy',
		),
		'startzeit' => array(
			'list' => 'FCN:kota_listview_time',
		),
		'name' => array(
			'list' => 'FCN:kota_listview_ko_reservation_mod_name',
		),
	);

	if (ko_module_installed('daten')) {
		$KOTA['ko_reservation_mod']['_listview'][70] = array('name' => 'event_id', 'sort' => FALSE, 'multiedit' => FALSE);
		$KOTA['ko_reservation_mod']['event_id'] = array(
			'fill' => 'FCN:kota_listview_reservation_event_id',
		);
	}
}



if(in_array('ko_resitem', $KOTA_TABLES)) {
	$KOTA['ko_resitem'] = array(
		'_access' => array(
			'module' => 'reservation',
			'chk_col' => 'id',
			'level' => 4,
		),
		"_multititle" => array(
			"name" => "",
		),
		'_special_cols' => array(
			'crdate' => 'crdate',
			'cruser' => 'cruser',
			'lastchange' => 'lastchange',
			'lastchange_user' => 'lastchange_user',
		),
		'_listview' => array(
			10 => array('name' => 'name', 'sort' => 'name', 'multiedit' => 'name', 'filter' => TRUE),
			20 => array('name' => 'gruppen_id', 'sort' => 'gruppen_id', 'multiedit' => 'gruppen_id', 'filter' => TRUE),
			30 => array('name' => 'farbe', 'sort' => 'farbe', 'multiedit' => 'farbe', 'filter' => FALSE),
			40 => array('name' => 'beschreibung', 'sort' => 'beschreibung', 'multiedit' => 'beschreibung', 'filter' => TRUE),
			50 => array('name' => 'moderation', 'sort' => 'moderation', 'multiedit' => 'moderation', 'filter' => TRUE),
			60 => array('name' => 'email_recipient', 'sort' => 'email_recipient', 'multiedit' => 'email_recipient', 'filter' => TRUE),
		),
		'_inlineform' => array(
			'redraw' => array(
				'sort' => 'sort_group',
				'fcn' => 'ko_show_items_liste();'
			),
			'module' => 'reservation',
		),
		"name" => array(
			'list' => 'FCN:kota_listview_ko_resitem_name;FCN:kota_listview_rootid',
			"pre" => "ko_html",
			"post" => 'js',
			"form" => array(
				"type" => "text",
				"params" => 'size="60" maxlength="100"',
			),
		),  //name
		"gruppen_id" => array(
			"post" => 'FCN:ko_multiedit_resitem_group',
			'list' => 'db_get_column("ko_resgruppen", "@VALUE@", "name")',
			"form" => array_merge(array(
				"type" => "textplus",
				"params" => 'maxlength="100" placeholder="'.getLL('kota_ko_resitem_gruppen_id_placeholder').'"',
			), kota_get_form("ko_resitem", "gruppen_id")),
		),  //gruppen_id
		"beschreibung" => array(
			"pre" => 'FCN:kota_sanitize_html',
			"list" => 'FCN:kota_listview_html',
			"xls" => 'FCN:kota_html_to_text',
			"form" => array(
				"type" => "richtexteditor",
			),
		),  //beschreibung
		"bild" => array(
			"form" => array(
				"type" => "file",
				"params" => 'size="60"',
			),
		),  //name
		"farbe" => array(
			'list' => 'FCN:kota_listview_color',
			"post" => 'str_replace("#", "", format_userinput("@VALUE@", "alphanum"))',
			"form" => array(
				"type" => "color",
				"params" => 'size="10" maxlength="7"',
			),
		),  //farbe
		"moderation" => array(
			'list' => 'FCN:kota_listview_ko_resitem_moderation',
			"pre" => "ko_html",
			"post" => 'uint',
			"form" => array_merge(array(
				"type" => "select",
				"params" => 'size="0"',
			), kota_get_form("ko_resitem", "moderation")),
		),  //moderation
		"linked_items" => array(
			"post" => 'intlist',
			"form" => array(
				"type" => "dyndoubleselect",
				"js_func_add" => "resgroup_doubleselect_add_no_linked",
				"params" => 'size="7"',
				'new_row' => TRUE,
			),
		),  //linked_items
		'email_recipient' => array(
			'list' => 'str_replace(",", ", ", '."'@VALUE@'".')',
			'pre' => 'ko_html',
			'form' => array(
				'type' => 'text',
				'params' => 'size="60" maxlength="250"',
			),
		),
		'email_text' => array(
			'list' => 'nl2br('."'@VALUE@'".')',
			'post' => 'htmlentities(format_userinput("@VALUE@", "text"), ENT_COMPAT | ENT_HTML401, "ISO-8859-1")',
			'form' => array(
				'type' => 'textarea',
				'params' => 'cols="60" rows="5"',
			),
		),

		'_form_layout' => [
			'general' => [
				'group' => FALSE,
				'sorting' => 10,
				'groups' => [
					'general' => [
						'sorting' => 10,
						'group' => TRUE,
						'rows' => [
							10 => ['name' => 6, 'gruppen_id' => 6],
							20 => ['beschreibung' => 6, 'bild' => 6],
							30 => ['farbe' => 6, 'moderation' => 6],
							40 => ['linked_items' => 6],
						],
					],
					'email' => [
						'sorting' => 20,
						'group' => TRUE,
						'rows' => [
							10 => ['email_recipient' => 6, 'email_text' => 6],
						],
					],
				],
			],
			'_default_cols' => 6,
			'_default_width' => 6,
			'_ignore_fields' => [],
		],
	);
}



if(in_array('ko_groups', $KOTA_TABLES)) {
	$KOTA['ko_groups'] = array(
		'_access' => array(
			'module' => 'groups',
			'chk_col' => 'ALL&id',
			'level' => 3,
		),
		'_multititle' => array(
			'name' => '',
		),
		'_listview' => array(
			10 => array('name' => 'name', 'sort' => 'name', 'multiedit' => 'name', 'filter' => 'name'),
			20 => array('name' => 'nump', 'sort' => FALSE, 'multiedit' => FALSE),
			30 => array('name' => 'numug', 'sort' => FALSE, 'multiedit' => FALSE),
			40 => array('name' => 'description', 'sort' => 'description', 'multiedit' => 'description', 'filter' => 'description'),
			50 => array('name' => 'roles', 'sort' => FALSE, 'multiedit' => FALSE, 'filter' => 'roles'),
			60 => array('name' => 'stop', 'sort' => 'stop', 'multiedit' => 'stop', 'filter' => 'stop'),
			65 => array('name' => 'crdate', 'sort' => 'crdate', 'multiedit' => FALSE, 'filter' => 'crdate'),
			70 => array('name' => 'type', 'sort' => 'type', 'multiedit' => 'type', 'filter' => 'type'),
			80 => array('name' => 'maxcount', 'sort' => "maxcount", 'multiedit' => "maxcount", 'filter' => "maxcount"),
			90 => array('name' => 'mailing_alias', 'sort' => "mailing_alias", 'multiedit' => FALSE, 'filter' => "mailing_alias"),
			91 => array('name' => 'mailing_mod_role', 'sort' => "mailing_mod_role", 'multiedit' => "mailing_mod_role", 'filter' => "mailing_mod_role"),
			92 => array('name' => 'mailing_mod_logins', 'sort' => "mailing_mod_logins", 'multiedit' => "mailing_mod_logins", 'filter' => "mailing_mod_logins"),
			93 => array('name' => 'mailing_mod_members', 'sort' => "mailing_mod_members", 'multiedit' => "mailing_mod_members", 'filter' => "mailing_mod_members"),
			94 => array('name' => 'mailing_mod_others', 'sort' => "mailing_mod_others", 'multiedit' => "mailing_mod_others", 'filter' => "mailing_mod_others"),
			95 => array('name' => 'mailing_reply_to', 'sort' => "mailing_reply_to", 'multiedit' => "mailing_reply_to", 'filter' => "mailing_reply_to"),
			96 => array('name' => 'mailing_modify_rcpts', 'sort' => "mailing_modify_rcpts", 'multiedit' => TRUE, 'filter' => "mailing_modify_rcpts"),
			97 => array('name' => 'mailing_prefix', 'sort' => "mailing_prefix", 'multiedit' => "mailing_prefix", 'filter' => "mailing_prefix"),
			98 => array('name' => 'mailing_rectype', 'sort' => "mailing_rectype", 'multiedit' => "mailing_rectype", 'filter' => "mailing_rectype"),
			99 => array('name' => 'mailing_crm_project_id', 'sort' => "mailing_crm_project_id", 'multiedit' => "mailing_crm_project_id", 'filter' => "mailing_crm_project_id"),
			100 => array('name' => 'linked_group', 'sort' => "linked_group", 'multiedit' => FALSE, 'filter' => "linked_group"),
			110 => array('name' => 'deadline', 'sort' => "deadline", 'multiedit' => "deadline", 'filter' => "deadline"),
			120 => array('name' => 'datafields', 'multiedit' => FALSE, 'sort' => FALSE, 'filter' => FALSE),
		),
		'_listview_default' => ["name", "nump", "numug", "description", "roles", "stop", "type",],
		'_inlineform' => array(
			'redraw' => array(
				'sort' => 'sort_groups',
				'fcn' => 'ko_groups_list();'
			),
			'module' => 'groups',
		),
		'_special_cols' => array(
			'crdate' => 'crdate',
			'cruser' => 'cruser',
			'lastchange' => 'lastchange',
			'lastchange_user' => 'lastchange_user',
		),
		'_form' => array(
			'redraw' => array(
				'fcn' => "ko_groups_formular_group('@MODE@', '@ID@')",
				'mode_map' => array(
				),
			),
			'module' => 'groups',
		),
		'name' => array(
			'list' => 'FCN:kota_listview_ko_groups_name',
			'form' => array(
				'type' => 'text',
				'params' => 'size="60" maxlength="200"'
			),
		),
		'nump' => array(
			'list' => 'FCN:kota_listview_ko_groups_nump',
		),
		'numug' => array(
			'list' => 'FCN:kota_listview_ko_groups_numug',
		),
		'description' => array(
			'list' => 'FCN:kota_listview_longtext25',
			'form' => array(
				'type' => 'textarea',
				'params' => 'cols="50" rows="4"',
			),
		),
		'datafields' => array(
			'list' => 'FCN:kota_listview_groups_datafields',
		),
		'crdate' => array(
			'list' => "sql2datetime('@VALUE@')",
		),
		'deadline' => array(
			'list' => "sql2datum('@VALUE@')",
			'form' => array(
				'type' => "jsdate",
			)
		),
		'stop' => array(
			'list' => 'FCN:kota_listview_datespan',
			'pre' => "sql2datum('@VALUE@')",
			'post' => "sql_datum('@VALUE@')",
			'form' => array(
				'type' => 'jsdate',
			),
		),
		'roles' => array(
			'list' => 'FCN:kota_listview_ko_groups_roles',
			'form' => array_merge(array(
				'type' => 'doubleselect',
				'params' => 'size="4"',
			), kota_get_form('ko_groups', 'roles')),
		),
		'type' => array(
			'list' => 'FCN:kota_listview_ko_groups_placeholder',
			'form' => array(
				'type' => 'switch',
			),
		),
		'count_role' => array(
			'form' => array_merge(array(
				'type' => 'select',
				'params' => 'size="0"',
			), kota_get_form('ko_groups', 'roles')),
		),
		'maxcount' => array(
			'post' => 'uint',
			'form' => array(
				'type' => 'text',
				'html_type' => 'number',
				'params' => 'size="5" maxlength="200"'
			),
		),
		'linked_group' => array(
			'list' => "FCN:kota_listview_ko_groups_linked_group",
			'form' => array(
				'additional_where' => " AND (stop >= '" . date('Y-m-d',time()) ."' OR stop = '0000-00-00') ",
			),
		),
	);

	if (ko_module_installed('mailing')) {
		$KOTA['ko_groups']['mailing_alias'] = array(
			'form' => array(
				'type' => "text",
			),
		);

		$KOTA['ko_groups']['mailing_mod_role'] = array(
			'list' => "FCN:kota_listview_ko_groups_mailing_mod_role",
			'form' => array_merge(array(
				'type' => 'select',
			), kota_get_form('ko_groups', 'roles')),
		);

		$KOTA['ko_groups']['mailing_mod_logins'] = array(
			'list' => 'FCN:kota_listview_ll',
			'form' => array(
				'type' => "select",
				'values' => $vs = [0,1],
				'descs' => kota_array_ll($vs, 'ko_groups', 'mailing_mod_logins'),
			),
		);

		$KOTA['ko_groups']['mailing_mod_members'] = array(
			'list' => 'FCN:kota_listview_ll',
			'form' => array(
				'type' => "select",
				'values' => $vs = array(0, 1, 2),
				'descs' => kota_array_ll($vs, 'ko_groups', 'mailing_mod_members'),
			),
		);

		$KOTA['ko_groups']['mailing_mod_others'] = array(
			'list' => 'FCN:kota_listview_ll',
			'form' => array(
				'type' => "select",
				'values' => $vs = array(0, 1, 2),
				'descs' => kota_array_ll($vs, 'ko_groups', 'mailing_mod_others'),
			),
		);

		$KOTA['ko_groups']['mailing_reply_to'] = array(
			'list' => 'FCN:kota_listview_ll',
			'form' => array(
				'type' => "select",
				'values' => $vs = array('', 'sender', 'list'),
				'descs' => kota_array_ll($vs, 'ko_groups', 'mailing_reply_to'),
			),
		);

		$KOTA['ko_groups']['mailing_modify_rcpts'] = array(
			'list' => 'FCN:kota_listview_ll',
			'form' => array(
				'type' => "select",
				'values' => $vs = array(1, 0),
				'descs' => kota_array_ll($vs, 'ko_groups', 'mailing_modify_rcpts'),
				),
		);

		$KOTA['ko_groups']['mailing_prefix'] = array(
			'list' => 'ko_html',
			'form' => array(
				'type' => "text",
			),
		);

		$values[] = '';
		$descs[] = '';
		global $RECTYPES;
		foreach ($RECTYPES as $recType => $x) {
			if (!is_array($x)) continue;
			if($recType == '_default') continue;
			$values[] = $recType;
			$descs[] = getLL('kota_ko_leute_rectype_' . $recType);
		}

		$KOTA['ko_groups']['mailing_rectype'] = array(
			'list' => "FCN:kota_listview_ko_groups_mailing_rectype",
			'form' => array(
				'type' => "select",
				"values" => $values,
				"descs" => $descs,
			),
		);

		if(ko_module_installed("crm")) {
			ko_get_access('crm');
			$x = kota_get_form('ko_crm_contacts', 'project_id');
			$values = $descs = array();
			foreach($x['values'] as $i => $project_id) {
				if(max($access['crm'][$project_id],$access['crm']['ALL']) >= 2) {
					$values[] = $project_id;
					$descs[] = $x['descs'][$i];
				}
			}

			$KOTA['ko_groups']['mailing_crm_project_id'] = [
				'list' => "FCN:kota_listview_ko_groups_crm_project_id",
				'form' => [
					'type' => "select",
					"values" => $values,
					"descs" => $descs,
				],
			];

		}
	}


	if (ko_module_installed("taxonomy") && $access['taxonomy']['ALL'] >= 1) {
		$KOTA['ko_groups']['terms'] = [
			'list' => 'FCN:kota_listview_ko_taxonomy_terms',
			'filter' => [
				'list' => "FCN:kota_listview_ko_taxonomy_terms_filter",
			],
			'form' => array(
				"desc" => getLL("form_taxonomy_title"),
				'type' => "dynamicsearch",
				"name" => "terms",
				"module" => "taxonomy",
				'ajaxHandler' => [
					'url' => "../taxonomy/inc/ajax.php",
					'actions' => ['search' => "termsearch"]
				]
			),
		];

		$KOTA['ko_groups']['_listview'][] = [
			"name" => "terms",
			"sort" => FALSE,
			"multiedit" => ($access['groups']['MAX'] >= 2 ? "terms" : FALSE),
			'filter' => 'terms',
		];

		$KOTA['ko_groups']['_listview_default'][] = "terms";
	}
}

if(in_array('ko_grouproles', $KOTA_TABLES)) {
	$KOTA['ko_grouproles'] = array(
		'_access' => array(
			'module' => 'groups',
			'chk_col' => '',
			'level' => 3,
		),
		'_multititle' => array(
			'name' => '',
		),
		'_special_cols' => array(
			'crdate' => 'crdate',
			'cruser' => 'cruser',
			'lastchange' => 'lastchange',
			'lastchange_user' => 'lastchange_user',
		),
		'_listview' => array(
			10 => array('name' => 'name', 'multiedit' => 'name'),
			20 => array('name' => 'used_in', 'multiedit' => FALSE),
		),
		'_inlineform' => array(
			'redraw' => array(
				'cols' => 'name',
				'fcn' => 'ko_groups_list_roles();'
			),
			'module' => 'groups',
		),
		'name' => array(
			'list' => 'ko_html;FCN:kota_listview_rootid',
			'form' => array(
				'type' => 'text',
				'params' => 'size="60"',
			),
		),
		'used_id' => array(
			'list' => '',
		),
	);
}



if(in_array('ko_groups_datafields', $KOTA_TABLES)) {
	$KOTA['ko_groups_datafields'] = array(
		'_access' => array(
			'module' => 'groups',
			'chk_col' => '',
			'level' => 3,
		),
		'_multititle' => array(
			'description' => '',
		),
		'_special_cols' => array(
			'crdate' => 'crdate',
			'cruser' => 'cruser',
			'lastchange' => 'lastchange',
			'lastchange_user' => 'lastchange_user',
		),
		'_listview' => array(
			10 => array('name' => 'description', 'multiedit' => 'description', 'filter' => TRUE),
			20 => array('name' => 'type', 'multiedit' => 'type', 'filter' => TRUE),
			30 => array('name' => 'preset', 'multiedit' => 'preset', 'filter' => TRUE),
			40 => array('name' => 'reusable', 'multiedit' => 'reusable', 'filter' => TRUE),
			50 => array('name' => 'used_in', 'multiedit' => FALSE),
			60 => array('name' => 'options', 'multiedit' => 'options', 'filter' => TRUE),
			70 => array('name' => 'private', 'multiedit' => 'private', 'filter' => TRUE),
		),
		'_inlineform' => array(
			'redraw' => array(
				'cols' => 'name,type',
				'fcn' => 'ko_groups_list_datafields();'
			),
			'module' => 'groups',
		),
		'help' => array(
			'form' => array(
				'type' => 'html',
				'value' => getLL('form_groups_datafield_help'),
				'colspan' => 'colspan="2"',
				'new_row' => TRUE,
				'dontsave' => TRUE,
				'ignore_test' => TRUE,
			),
		),
		'description' => array(
			'list' => 'ko_html;FCN:kota_listview_rootid',
			'post' => 'js',
			'pre' => 'ko_html',
			'form' => array(
				'type' => 'text',
				'params' => 'size="40"',
				'new_row' => TRUE,
			),
		),
		'preset' => array(
			'post' => 'uint',
			'list' => 'FCN:kota_listview_boolyesno',
			'filter' => array(
				'type' => 'switch',
			),
		),
		'private' => array(
			'post' => 'uint',
			'list' => 'FCN:kota_listview_boolyesno',
			'form' => array(
				'type' => 'switch',
			),
		),
		'reusable' => array(
			'post' => 'uint',
			'list' => 'FCN:kota_listview_boolyesno',
			'form' => array(
				'type' => 'switch',
			),
		),
		'type' => array(
			'post' => 'FCN:kota_post_groups_datafields_type',
			'list' => 'FCN:kota_listview_ll',
			'form' => array(
				'type' => 'select',
				'values' => array('text', 'textarea', 'checkbox', 'select', 'multiselect'),
				'descs' => array(getLL('groups_datafields_text'), getLL('groups_datafields_textarea'), getLL('groups_datafields_checkbox'), getLL('groups_datafields_select'), getLL('groups_datafields_multiselect')),
			),
		),
		'options' => array(
			'pre' => 'implode("\n", unserialize(stripslashes(\'@VALUE@\')))',
			'post' => 'FCN:kota_post_groups_datafields_options',
			'list' => 'implode(", ", unserialize(stripslashes(\'@VALUE@\')))',
			'form' => array(
				'type' => 'textarea',
				'params' => 'cols="30" rows="6"',
			),
		),
		'used_in' => array(
			'list' => '',
		),
	);
}



if(in_array('ko_groups_assignment_history', $KOTA_TABLES)) {
	$KOTA['ko_groups_assignment_history'] = array(
		'_form' => array(
			'redraw' => array(
				'fcn' => "ko_groups_formular_assignment_history_entry('@MODE@', '@ID@')",
			),
			'module' => 'groups',
		),
		'group_id' => array(
			'pre' => 'FCN:kota_pre_groups_assignment_history_group_id',
			'form' => array(
				'type' => 'html',
				'dontsave' => TRUE,
				'ignore_test' => TRUE,
			)
		),
		'person_id' => array(
			'pre' => 'FCN:kota_pre_groups_assignment_history_person_id',
			'form' => array(
				'type' => 'html',
				'new_row' => TRUE,
				'dontsave' => TRUE,
				'ignore_test' => TRUE,
			)
		),
		'start' => array(
			'pre' => "sql2datetime('@VALUE@')",
			'post' => "sql_datetime('@VALUE@')",
			'form' => array(
				'type' => 'text',
				/*'type' => 'jsdate',
				'picker_mode' => 'datetime',*/
			)
		),
		'stop' => array(
			'pre' => "sql2datetime('@VALUE@')",
			'post' => "sql_datetime('@VALUE@')",
			'form' => array(
				'type' => 'text',
				/*'type' => 'jsdate',
				'picker_mode' => 'datetime',*/
			)
		),
	);
}

if(in_array('ko_taxonomy_terms', $KOTA_TABLES)) {
	$KOTA['ko_taxonomy_terms'] = array(
		'_listview' => array(
			10 => array('name' => 'name', 'sort' => 'name', 'multiedit' => FALSE, 'filter' => TRUE),
			20 => array('name' => 'parent', 'sort' => FALSE, 'multiedit' => FALSE),
			30 => array('name' => 'used_in', 'sort' => FALSE, 'multiedit' => FALSE),
		),

		// Example for configuration
		/*
		'_listview_config' => array(
			'disableManualSortingColumns' => TRUE,
		),
		*/

		'_special_cols' => array(
			'used_in' => 'used_in',
		),
		'_inlineform' => array(
			'redraw' => array(
				'fcn' => 'ko_taxonomy_list();'
			),
			'module' => 'taxonomy',
		),
		'name' => array(
			'list' => 'FCN:kota_listview_ko_taxonomy_terms_name',
			'form' => array(
				'type' => 'text',
				'params' => 'size="60" maxlength="100"'
			),
		),
		'parent' => array(
			'list' => 'FCN:kota_listview_ko_taxonomy_terms_parents',
			'form' => array_merge(array(
				'type' => 'select',
			), kota_get_form('ko_taxonomy_terms', 'parent')),
		),
		'used_in' => array(
			'list' => 'FCN:kota_listview_ko_taxonomy_terms_used_in',
		),
	);
}


if(in_array('ko_pdf_layout', $KOTA_TABLES)) {
	$KOTA['ko_pdf_layout'] = array(
		'_special_cols' => array(
			'crdate' => 'crdate',
			'cruser' => 'cruser',
			'lastchange' => 'lastchange',
			'lastchange_user' => 'lastchange_user',
		),
		'_listview' => array(
			10 => array('name' => 'name'),
			20 => array('name' => 'layout'),
			30 => array('name' => 'start'),
			40 => array('name' => 'length'),
		),
		'_listview_default' => array('name', 'layout', 'start', 'length'),
		'name' => array(
			'post' => 'js',
			'form' => array(
				'type' => 'text',
				'params' => 'size="60" maxlength="100"'
			),
		),
		'layout' => array(
			'list' => 'FCN:kota_listview_pdf_layout',
		),
		'start' => array(
			'list' => 'FCN:kota_listview_pdf_layout',
		),
		'length' => array(
			'list' => 'FCN:kota_listview_pdf_layout',
		),
	);
}



if(in_array('ko_vesr', $KOTA_TABLES)) {
	$KOTA['ko_vesr'] = array(
		'_inlineform' => array(
			'module' => 'admin',
		),
		'_listview' => array(
			10 => array('name' => 'type', 'sort' => FALSE, 'filter' => FALSE),
			20 => array('name' => 'reason', 'sort' => FALSE, 'filter' => FALSE),
			30 => array('name' => 'transaction', 'sort' => FALSE, 'filter' => FALSE),
			40 => array('name' => 'account', 'sort' => FALSE, 'filter' => FALSE),
			50 => array('name' => 'refnumber', 'sort' => FALSE, 'filter' => FALSE),
			60 => array('name' => 'amount', 'sort' => FALSE, 'filter' => FALSE),
			70 => array('name' => 'paydate', 'sort' => FALSE, 'filter' => FALSE),
			80 => array('name' => 'valutadate', 'sort' => FALSE, 'filter' => FALSE),
			90 => array('name' => 'misc', 'sort' => FALSE, 'filter' => FALSE),
		),
		'_listview_default' => array('type', 'reason', 'transaction', 'account', 'refnumber', 'amount', 'paydate', 'valutadate', 'misc'),
		'type' => array(
			'list' => 'FCN:kota_listview_ll',
		),
		'reason' => array(
			'list' => 'FCN:kota_listview_vesr_reason',
		),
		'transaction' => array(
			'list' => 'FCN:kota_listview_ll',
		),
		'account' => array(),
		'refnumber' => array(
			'list' => 'FCN:kota_listview_refnr',
		),
		'amount' => array(
			'list' => 'FCN:kota_listview_money',
		),
		'paydate' => array(
			'list' => 'FCN:kota_listview_datecol_right',
		),
		'valutadate' => array(
			'list' => 'FCN:kota_listview_datecol_right',
		),
	);
}



if(in_array('ko_vesr_camt', $KOTA_TABLES)) {
	$KOTA['ko_vesr_camt'] = array(
		'_inlineform' => array(
			'module' => 'admin',
		),
		'_listview' => array(
			10 => array('name' => 'type', 'sort' => FALSE, 'filter' => FALSE),
			20 => array('name' => 'reason', 'sort' => FALSE, 'filter' => FALSE),
			30 => array('name' => 'refnumber', 'sort' => FALSE, 'filter' => FALSE),
			40 => array('name' => 'amount', 'sort' => FALSE, 'filter' => FALSE),
			50 => array('name' => 'booking_date', 'sort' => FALSE, 'filter' => FALSE),
			60 => array('name' => 'valuta_date', 'sort' => FALSE, 'filter' => FALSE),
			70 => array('name' => 'misc', 'sort' => FALSE, 'filter' => FALSE),
			80 => array('name' => 'source', 'sort' => FALSE, 'filter' => FALSE),
			90 => array('name' => 'note', 'sort' => FALSE, 'filter' => FALSE),
			100 => array('name' => 'additional_information', 'sort' => FALSE, 'filter' => FALSE),
		),
		'_listview_default' => array('type', 'reason', 'refnumber', 'amount', 'booking_date', 'valuta_date', 'misc', 'source', 'note', 'additional_information'),
		'type' => array(
			'list' => 'FCN:kota_listview_ll',
		),
		'reason' => array(
			'list' => 'FCN:kota_listview_vesr_reason',
		),
		'refnumber' => array(
			'list' => 'FCN:kota_listview_refnr',
		),
		'amount' => array(
			'list' => 'FCN:kota_listview_money',
		),
		'booking_date' => array(
			'list' => 'FCN:kota_listview_datecol',
			'list_options' => 'dmY',
		),
		'valuta_date' => array(
			'list' => 'FCN:kota_listview_datecol',
			'list_options' => 'dmY',
		),
	);
}



if(in_array('ko_detailed_person_exports', $KOTA_TABLES)) {
	$KOTA['ko_detailed_person_exports'] = array(
		'_special_cols' => array(
			'crdate' => 'crdate',
			'cruser' => 'cruser',
			'lastchange' => 'lastchange',
			'lastchange_user' => 'lastchange_user',
		),
		'_listview' => array(
			10 => array('name' => 'name'),
			20 => array('name' => 'template'),
		),
		'name' => array(
			'form' => array(
				'type' => 'text',
			),
		),
		'template' => array(
			'list' => 'FCN:kota_listview_file',
			'form' => array(
				'type' => 'file',
			),
		),
		'instructions' => array(
			'pre' => 'FCN:kota_pre_detailed_person_exports_instructions',
			'form' => array(
				'type' => 'html',
				'dontsave' => TRUE,
				'ignore_test' => TRUE,
			)
		)
	);
}



if(in_array('ko_labels', $KOTA_TABLES)) {
	$KOTA['ko_labels'] = array(
		'_special_cols' => array(
			'crdate' => 'crdate',
			'cruser' => 'cruser',
			'lastchange' => 'lastchange',
			'lastchange_user' => 'lastchange_user',
		),
		'_listview' => array(
			10 => array('name' => 'name'),
			20 => array('name' => 'page_format'),
			30 => array('name' => 'per_col'),
		),
		'_listview_default' => array('name', 'page_format', 'per_col'),
		'name' => array(
			'form' => array(
				'type' => 'text',
				'new_row' => true,
			),
		),
		'page_format' => array(
			'list' => 'ko_html',
			'form' => array_merge(array(
				'type' => 'select',
			), kota_get_form('ko_labels', 'page_format')),
		),
		'page_orientation' => array(
			"list" => "FCN:kota_listview_ll",
			'form' => array_merge(array(
				'type' => 'select',
			), kota_get_form('ko_labels', 'page_orientation')),
		),
		'per_row' => array(
			'form' => array(
				'type' => 'text',
				"html_type" => "number",
			),
		),
		'per_col' => array(
			'form' => array(
				'type' => 'text',
				"html_type" => "number",
			),
		),
		'border_top' => array(
			'form' => array(
				'type' => 'text',
				"html_type" => "number",
			),
		),
		'border_right' => array(
			'form' => array(
				'type' => 'text',
				"html_type" => "number",
			),
		),
		'border_bottom' => array(
			'form' => array(
				'type' => 'text',
				"html_type" => "number",
			),
		),
		'border_left' => array(
			'form' => array(
				'type' => 'text',
				"html_type" => "number",
			),
		),
		'spacing_horiz' => array(
			'form' => array(
				'type' => 'text',
				"html_type" => "number",
			),
		),
		'spacing_vert' => array(
			'form' => array(
				'type' => 'text',
				"html_type" => "number",
			),
		),
		'align_horiz' => array(
			"list" => "FCN:kota_listview_ll",
			'form' => array_merge(array(
				'type' => 'select',
			), kota_get_form('ko_labels', 'align_horiz')),
		),
		'align_vert' => array(
			"list" => "FCN:kota_listview_ll",
			'form' => array_merge(array(
				'type' => 'select',
			), kota_get_form('ko_labels', 'align_vert')),
		),
		'font' => array(
			"list" => "FCN:kota_font_name",
			'form' => array_merge(array(
				'type' => 'select',
			), kota_get_form('ko_labels', 'font')),
		),
		'textsize' => array(
			'form' => array_merge(array(
				'type' => 'select',
			), kota_get_form('ko_labels', 'textsize')),
		),
		/*'ra_margin_top' => array(
			'form' => array(
				'type' => 'text',
				"html_type" => "number",
			),
		),
		'ra_margin_left' => array(
			'form' => array(
				'type' => 'text',
				"html_type" => "number",
			),
		),*/
		'ra_font' => array(
			"list" => "FCN:kota_font_name",
			'form' => array_merge(array(
				'type' => 'select',
			), kota_get_form('ko_labels', 'ra_font')),
		),
		'ra_textsize' => array(
			'form' => array_merge(array(
				'type' => 'select',
			), kota_get_form('ko_labels', 'ra_textsize')),
		),
		'pp_position' => array(
			'form' => array_merge(array(
				'type' => 'select',
				'new_row' => TRUE,
			), kota_get_form('ko_labels', 'pp_position')),
		),
		'pic_file' => array(
			'form' => array(
				'type' => 'file',
			),
		),
		'pic_w' => array(
			'form' => array(
				'type' => 'text',
				"html_type" => "number",
			),
		),
		'pic_x' => array(
			'form' => array(
				'type' => 'text',
				"html_type" => "number",
			),
		),
		'pic_y' => array(
			'form' => array(
				'type' => 'text',
				"html_type" => "number",
			),
		),
	);
}



if(in_array('ko_tracking', $KOTA_TABLES)) {
	$KOTA['ko_tracking'] = array(
		'_access' => array(
			'module' => 'tracking',
			'chk_col' => 'id',
			'level' => 3,
		),
		'_multititle' => array(
			'name' => '',
		),
		'_special_cols' => array(
			'crdate' => 'crdate',
			'cruser' => 'cruser',
			'lastchange' => 'lastchange',
			'lastchange_user' => 'lastchange_user',
		),
		'_listview' => array(
			10 => array('name' => 'name', 'sort' => 'name', 'multiedit' => 'name', 'filter' => TRUE),
			20 => array('name' => 'group_id', 'sort' => 'group_id', 'multiedit' => 'group_id', 'filter' => TRUE),
			30 => array('name' => 'mode', 'sort' => 'mode', 'multiedit' => 'mode', 'filter' => TRUE),
			40 => array('name' => 'filter', 'sort' => FALSE, 'multiedit' => 'filter'),
			50 => array('name' => 'hidden', 'sort' => 'hidden', 'multiedit' => 'hidden', 'filter' => TRUE),
			//60 => array('name' => 'enable_checkin', 'sort' => 'enable_checkin', 'multiedit' => 'enable_checkin', 'filter' => TRUE),
			//70 => array('name' => 'checkin_links', 'sort' => FALSE, 'multiedit' => FALSE, 'filter' => FALSE),
		),
		'_listview_default' => array('name', 'group_id', 'mode', 'filter', 'hidden'),
		'_inlineform' => array(
			'redraw' => array(
				'sort' => 'sort_trackings',
				'fcn' => 'ko_list_trackings();',
				'cols' => array('hidden'),
			),
			'module' => 'tracking',
		),
		'name' => array(
			'pre' => 'ko_html',
			'list' => 'ko_html;FCN:kota_listview_rootid',
			'form' => array(
				'type' => 'text',
				'params' => 'size="60" maxlength="200"',
			),
		),
		'group_id' => array(
			"post" => "FCN:ko_multiedit_tracking_group",
			'list' => 'db_get_column("ko_tracking_groups", "@VALUE@", "name")',
			'form' => array_merge(array(
				'type' => 'textplus',
				'params_PLUS' => 'size="60" maxlength="200"',
			), kota_get_form('ko_tracking', 'group_id')),
		),  //group_id
		'mode' => array(
			"list" => "FCN:kota_listview_ll",
			'form' => array_merge(array(
				'type' => 'select',
			), kota_get_form('ko_tracking', 'mode')),
		),
		'hidden' => array(
			'list' => 'FCN:kota_listview_boolyesno',
			'form' => array(
				'type' => 'switch',
			),
		),
		'types' => array(
			'form' => array(
				'type' => 'textarea',
				'params' => 'cols="50" rows="4"',
			),
		),
		'label_value' => array(
			'pre' => 'ko_html',
			'form' => array(
				'type' => 'text',
				'params' => 'size="60" maxlength="200"',
			),
		),
		'filter' => array(
			'list' => 'FCN:kota_listview_ko_tracking_filter',
			'form' => array_merge(array(
				'type' => 'doubleselect',
				"size" => array(
					"for_filter" => 6,
					"normal" => 8,
				),
				'show_filter' => TRUE,
				"js_func_add" => "tracking_ds_filter",
			), kota_get_form('ko_tracking', 'filter')),
		),
		'date_eventgroup' => array(
			'form' => array_merge(array(
				'type' => 'doubleselect',
				"size" => array(
					"for_filter" => 6,
					"normal" => 8,
				),
				'show_filter' => TRUE,
			), kota_get_form('ko_tracking', 'date_eventgroup')),
		),
		'dates' => array(
			'post' => 'FCN:kota_sort_comma_list',
			'form' => array(
				'type' => 'multidateselect',
				'params' => 'size="8"',
			),
		),
		'date_weekdays' => array(
			'form' => array_merge(array(
				'type' => 'checkboxes',
				'size' => '7',
			), kota_get_form('ko_tracking', 'date_weekdays')),
		),
		'description' => array(
			'form' => array(
				'type' => 'textarea',
				'params' => 'cols="50" rows="4"',
			),
		),
		'type_multiple' => array(
			'list' => 'FCN:kota_listview_boolyesno',
			'post' => 'uint',
			'form' => array(
				'type' => 'switch',
			),
		),
	);

	// add fields for checkin
	if (ko_get_setting('tracking_enable_checkin')) {
		$KOTA['ko_tracking']['_listview'][60] = array('name' => 'enable_checkin', 'sort' => 'enable_checkin', 'multiedit' => 'enable_checkin', 'filter' => TRUE);
		$KOTA['ko_tracking']['_listview'][70] = array('name' => 'checkin_links', 'sort' => FALSE, 'multiedit' => FALSE, 'filter' => FALSE);
		$KOTA['ko_tracking']['_default_listview'][] = 'enable_checkin';
		$KOTA['ko_tracking']['_default_listview'][] = 'checkin_links';

		$KOTA['ko_tracking']['enable_checkin'] = array(
			'list' => 'FCN:kota_listview_boolyesno',
			'post' => 'uint',
			'form' => array(
				'type' => 'switch',
			),
		);
		$KOTA['ko_tracking']['checkin_links'] = array(
			'list' => 'FCN:kota_listview_checkin_links',
			'pre' => 'FCN:kota_listview_checkin_links',
			'form' => array(
				'type' => 'html',
				'dontsave' => TRUE,
				'ignore_test' => TRUE,
			),
		);
		$KOTA['ko_tracking']['checkin_guest_pass'] = array(
			'form' => array(
				'type' => 'text',
			),
		);
		$KOTA['ko_tracking']['checkin_admin_pass'] = array(
			'form' => array(
				'type' => 'text',
			),
		);
	}
}



if(in_array('ko_tracking_entries', $KOTA_TABLES)) {
	//Get types from all temporary entries (for filter form)
	$access_where = '';
	if($access['tracking']['ALL'] < 1) {
		$trackings = db_select_data('ko_tracking', 'WHERE 1');
		if(sizeof($trackings) > 0) {
			foreach($trackings as $k => $t) {
				if($access['tracking'][$t['id']] < 1) unset($trackings[$k]);
			}
			if(sizeof($trackings) > 0) {
				$access_where = " AND `tid` IN (".implode(',', array_keys($trackings)).") ";
			} else {
				$access_where = ' AND 1=2 ';
			}
		}
	}
	$type_values = db_select_distinct('ko_tracking_entries', 'type', 'ORDER BY `type` ASC', "WHERE `status` = '1' ".$access_where, TRUE);


	$KOTA['ko_tracking_entries'] = array(
		'_access' => array(
			'module' => 'tracking',
			'chk_col' => 'tid',
			'level' => 2,
		),
		'_multititle' => array(
			'lid' => 'ko_get_person_name(@VALUE@)',
			'date' => "sql2datum('@VALUE@')",
		),
		'_inlineform' => array(
			'redraw' => array(
				'cols' => 'status',
				'fcn' => 'ko_tracking_list_wrapper();'
			),
			'module' => 'tracking',
		),
		'_special_cols' => array(
			'crdate' => 'crdate',
			'cruser' => 'cruser',
		),
		'_listview' => array(
			10 => array('name' => 'tid', 'sort' => 'tid', 'filter' => TRUE, 'multiedit' => FALSE),
			20 => array('name' => 'date', 'sort' => 'date', 'filter' => TRUE, 'multiedit' => FALSE),
			30 => array('name' => 'lid', 'sort' => 'lid', 'filter' => TRUE, 'multiedit' => FALSE),
			40 => array('name' => 'type', 'sort' => 'type', 'filter' => TRUE, 'multiedit' => TRUE),
			50 => array('name' => 'value', 'sort' => 'value', 'filter' => TRUE, 'multiedit' => TRUE),
			60 => array('name' => 'comment', 'sort' => 'comment', 'filter' => TRUE, 'multiedit' => TRUE),
			70 => array('name' => 'status', 'sort' => 'status', 'filter' => TRUE),
			80 => array('name' => 'crdate', 'sort' => 'crdate', 'multiedit' => FALSE),
		),
		'_listview_default' => array('tid', 'lid', 'date', 'type', 'value', 'comment', 'status', 'crdate'),

		'tid' => array(
			'list' => 'db_get_column("ko_tracking", "@VALUE@", "name")',
			'post' => 'uint',
			'form' => array_merge(array(
				'type' => 'select',
				'noinline' => TRUE,
			), kota_get_form('ko_tracking_entries', 'tid')),
		),
		'date' => array(
			'list' => "sql2datum('@VALUE@')",
			'pre' => "sql2datum('@VALUE@')",
			'post' => "sql_datum('@VALUE@')",
			'form' => array(
				'type' => 'jsdate',
				'noinline' => TRUE,
			),
		),
		'lid' => array(
			'list' => 'FCN:kota_listview_people',
			'post' => 'uint',
			'form' => array(
				'type' => 'peoplesearch',
				'single' => TRUE,
				'noinline' => TRUE,
				'show_add' => TRUE,
			),
		),
		'type' => array(
			'pre' => 'ko_html',
			'form' => array(
				'type' => 'select',
				'params' => 'size="0"',
				'values' => $type_values,
				'descs' => $type_values,
				'noinline' => TRUE,
			),
		),
		'value' => array(
			'pre' => 'ko_html',
			'form' => array(
				'type' => 'text',
				'params' => 'size="10" maxlength="200"',
			),
		),
		'comment' => array(
			'list' => 'ko_html',
			'pre' => 'ko_html',
			'form' => array(
				'type' => 'textarea',
				'params' => 'cols="50" rows="4"',
			),
		),
		'status' => array(
			'list' => 'FCN:kota_listview_boolyesno',
			'form' => array(
				'type' => 'switch',
				'label_0' => getLL('no'),
				'label_1' => getLL('yes'),
				'value' => '0',
			),
		),
	);
}



if(in_array('ko_google_cloud_printers', $KOTA_TABLES)) {
	$KOTA['ko_google_cloud_printers'] = array(
		'_listview' => array(
			10 => array('name' => 'name', 'sort' => 'name', 'multiedit' => FALSE),
			20 => array('name' => 'owner_name', 'sort' => 'owner_name', 'multiedit' => FALSE),
			30 => array('name' => 'google_id', 'sort' => 'google_id', 'multiedit' => FALSE),
		),
		'_listview_default' => array('name', 'owner_name', 'google_id'),
	);
}



if(in_array('ko_news', $KOTA_TABLES)) {
	$KOTA['ko_news'] = array(
		'_multititle' => array(
			'title' => '',
		),
		'_special_cols' => array(
			'crdate' => 'cdate',
			'cruser' => 'cruser',
			'lastchange' => 'lastchange',
			'lastchange_user' => 'lastchange_user',
		),
		'_listview' => array(
			10 => array('name' => 'title', 'sort' => 'title', 'multiedit' => 'title', 'filter' => TRUE),
			20 => array('name' => 'type', 'sort' => 'type', 'multiedit' => 'type', 'filter' => TRUE),
			30 => array('name' => 'category', 'sort' => 'category', 'multiedit' => 'category', 'filter' => TRUE),
			40 => array('name' => 'cdate', 'sort' => 'cdate', 'multiedit' => 'cdate'),
		),
		'type' => array(
			'list' => 'FCN:kota_listview_ll',
			'form' => array(
				'type' => 'select',
				'values' => array('1', '2'),
				'descs' => array(getLL('kota_ko_news_type_1'), getLL('kota_ko_news_type_2')),
			),
		),
		'category' => array(
			'pre' => 'ko_html',
			'form' => array(
				'type' => 'textplus',
			),
		),
		'title' => array(
			'pre' => 'ko_html',
			'form' => array(
				'type' => 'text',
			),
		),
		'subtitle' => array(
			'pre' => 'ko_html',
			'form' => array(
				'type' => 'text',
			),
		),
		'cdate' => array(
			'list' => "sql2datum('@VALUE@')",
			'pre' => "sql2datum('@VALUE@')",
			'post' => "sql_datum('@VALUE@')",
			'form' => array(
				'type' => 'jsdate',
			),
		),
		'author' => array(
			'pre' => 'ko_html',
			'form' => array(
				'type' => 'text',
			),
		),
		'text' => array(
			'form' => array(
				'type' => 'richtexteditor',
			),
		),
		'link' => array(
			'pre' => 'ko_html',
			'form' => array(
				'type' => 'text',
				"html_type" => "url",
			),
		),
	);
}



if(in_array('_ko_sms_log', $KOTA_TABLES)) {
	$KOTA['_ko_sms_log'] = array(
		"_listview" => array(
			10 => array('name' => 'date', 'filter' => TRUE,),
			20 => array('name' => 'user_id', 'filter' => FALSE,),
			30 => array('name' => 'credits', 'filter' => FALSE,),
			40 => array('name' => 'ratio', 'filter' => FALSE,),
			50 => array('name' => 'numbers', 'filter' => FALSE,),
			60 => array('name' => 'text', 'filter' => TRUE,),
		),
		'_inlineform' => array(
			'redraw' => array(
				'fcn' => 'ko_show_sms_log();'
			),
			'module' => 'admin',
		),
		'date' => array(
			'filter' => array(
				'type' => 'jsdate',
			),
		),
		'user_id' => array(),
		'credits' => array(),
		'ratio' => array(),
		'numbers' => array(),
		'text' => array(
			'filter' => array(
				'type' => 'text',
			),
		),
	);
}



if(in_array('_ko_telegram_log', $KOTA_TABLES)) {
	$KOTA['_ko_telegram_log'] = array(
		"_listview" => array(
			5 => array('name' => 'id', 'filter' => FALSE,),
			10 => array('name' => 'date', 'filter' => TRUE,),
			20 => array('name' => 'user_id', 'filter' => FALSE,),
			30 => array('name' => 'recipients', 'filter' => TRUE,),
			40 => array('name' => 'text', 'filter' => TRUE,),
		),
		'_inlineform' => array(
			'redraw' => array(
				'fcn' => 'ko_show_telegram_log();'
			),
			'module' => 'admin',
		),
		'id' => array(),
		'date' => array(
			'filter' => array(
				'type' => 'jsdate',
			),
		),
		'user_id' => array(),
		'recipients' => array(
			'filter' => array(
				'type' => 'text',
			),
		),
		'text' => array(
			'filter' => array(
				'type' => 'text',
			),
		),
	);
}


if(in_array('ko_scheduler_tasks', $KOTA_TABLES)) {
	$KOTA['ko_scheduler_tasks'] = array(
		'_access' => array(
			'module' => 'tools',
			'chk_col' => '',
			'level' => 4,
		),
		'_multititle' => array(
			'name' => '',
		),
		'_listview' => array(
			10 => array('name' => 'name', 'sort' => 'name', 'multiedit' => 'name'),
			20 => array('name' => 'crontime', 'multiedit' => 'crontime'),
			30 => array('name' => 'status', 'sort' => 'status', 'multiedit' => 'status'),
			40 => array('name' => 'call', 'sort' => 'call', 'multiedit' => 'call'),
			50 => array('name' => 'last_call', 'sort' => 'last_call'),
			60 => array('name' => 'next_call', 'sort' => 'next_call'),
		),
		'_inlineform' => array(
			'redraw' => array(
				'cols' => 'status,crontime',
				'fcn' => 'ko_list_tasks();'
			),
			'module' => 'tools',
		),
		'name' => array(
			'form' => array(
				'type' => 'text',
				'params' => 'size="60"',
			),
		),
		'status' => array(
			'list' => 'FCN:kota_listview_boolyesno',
			'form' => array(
				'type' => 'switch',
			),
		),
		'crontime' => array(
			'form' => array(
				'type' => 'text',
				'params' => 'size="60"',
			),
		),
		'call' => array(
			'form' => array(
				'type' => 'text',
				'params' => 'size="60"',
			),
		),
		'last_call' => array(
			'list' => "sql2datetime('@VALUE@')",
		),
		'next_call' => array(
			'list' => 'FCN:kota_listview_scheduler_task_next_call',
		),

	);
}




if(in_array('ko_updates', $KOTA_TABLES)) {
	$KOTA['ko_updates'] = array(
		'_access' => array(
			'module' => 'tools',
			'chk_col' => '',
			'level' => 1,
		),
		'_multititle' => array(
			'name' => '',
		),
		'_inlineform' => array(
			'redraw' => array(
				'cols' => array('status'),
				'fcn' => 'ko_list_updates();',
			),
			'module' => 'tools',
		),
		'_listview' => array(
			10 => array('name' => 'name', 'sort' => 'name', 'multiedit' => FALSE),
			20 => array('name' => 'module', 'sort' => 'module'),
			30 => array('name' => 'plugin', 'sort' => 'plugin'),
			40 => array('name' => 'crdate', 'sort' => 'crdate'),
			50 => array('name' => 'status', 'sort' => 'status', 'multiedit' => 'status'),
			60 => array('name' => 'done_date', 'sort' => 'done_date'),
		),
		'name' => array(
			'form' => array(
				'type' => 'text',
			),
		),
		'status' => array(
			'list' => 'FCN:kota_listview_ll',
			'form' => array(
				'type' => 'select',
				'values' => $vs = array(0, 1, 2),
				'descs' => kota_array_ll($vs, 'ko_updates', 'status'),
			),
		),
		'crdate' => array(
			'list' => "sql2datetime('@VALUE@')",
		),
		'done_date' => array(
			'list' => "sql2datetime('@VALUE@')",
		),
		'module' => array(
			'form' => array(
				'type' => 'text',
			),
		),
		'plugin' => array(
			'form' => array(
				'type' => 'text',
			),
		),

	);
}



if(in_array('ko_mailing_mails', $KOTA_TABLES)) {
  $KOTA['ko_mailing_mails'] = array(
    '_access' => array(
      'module' => 'tools',
      'chk_col' => '',
      'level' => 0,
    ),
    '_multititle' => array(
      'subject' => '',
    ),
    '_listview' => array(
      10 => array('name' => 'crdate', 'sort' => FALSE, 'multiedit' => FALSE),
      20 => array('name' => 'recipient', 'sort' => FALSE, 'multiedit' => FALSE),
      30 => array('name' => 'from', 'sort' => FALSE, 'multiedit' => FALSE),
      40 => array('name' => 'subject', 'sort' => FALSE, 'multiedit' => FALSE),
      50 => array('name' => 'status', 'sort' => FALSE, 'multiedit' => FALSE),
      60 => array('name' => 'user_id', 'sort' => FALSE, 'multiedit' => FALSE),
			70 => array('name' => 'size', 'sort' => FALSE, 'multiedit' => FALSE),
    ),
    'status' => array(
      'list' => 'FCN:kota_listview_ll',
    ),
    'user_id' => array(
      'list' => 'FCN:kota_listview_login',
    ),
    'crdate' => array(
      'list' => 'FCN:kota_listview_datetimecol',
    ),
    'recipient' => array(
    ),
    'from' => array(
    ),
    'subject' => array(
      'list' => 'str_replace("_"," ", mb_decode_mimeheader('."'@VALUE@'".'))',
    ),
    'size' => array(
      'list' => 'FCN:kota_listview_ko_mailing_mails_size',
    ),

  );
}





if(in_array('ko_log', $KOTA_TABLES)) {
	$user_id_data_ = kota_get_form('ko_log', 'user_id');
	$user_id_data = array_combine($user_id_data_['values'], $user_id_data_['descs']);

	$KOTA['ko_log'] = array(
		'_access' => array(
			'module' => 'admin',
			'chk_col' => '',
			'level' => 4,
		),
		'_multititle' => array(
			'type' => '',
		),
		'_inlineform' => array(
			'redraw' => array(
				'sort' => 'sort_logs',
				'fcn' => 'ko_show_logs();'
			),
			'module' => 'admin',
		),
		'_listview' => array(
			10 => array('name' => 'date', 'sort' => 'date', 'multiedit' => FALSE, 'filter' => TRUE),
			20 => array('name' => 'type', 'sort' => 'type', 'multiedit' => FALSE, 'filter' => TRUE),
			30 => array('name' => 'user_id', 'sort' => 'user_id', 'multiedit' => FALSE, 'filter' => TRUE),
			40 => array('name' => 'comment', 'sort' => 'comment', 'multiedit' => FALSE, 'filter' => TRUE),
			50 => array('name' => 'request_data', 'sort' => FALSE, 'multiedit' => FALSE, 'filter' => TRUE),
		),
		'date' => array(
			'list' => 'FCN:kota_listview_datetimecol',
			'filter' => array(
				'type' => 'jsdate',
			),
		),
		'type' => array(
			'filter' => array(
				'type' => 'text',
				'params' => 'size="20"',
			),
		),
		'user_id' => array(
			'list' => 'FCN:kota_listview_login',
			'filter' => array(
				'type' => 'select',
				'data' => $user_id_data,
			),
		),
		'comment' => array(
			'list' => 'ko_html',
			'filter' => array(
				'type' => 'textarea',
			),
		),
		'request_data' => array(
			'list' => 'FCN:kota_listview_ko_log_request_data',
			'filter' => array(
				'type' => 'textarea',
			),
		),
	);
}



if(in_array('ko_admin', $KOTA_TABLES)) {
	$ag_values = $ag_descs = array();
	$admingroups = db_select_data('ko_admingroups', 'WHERE 1', '*', 'ORDER BY `name` ASC');
	foreach($admingroups as $ag) {
		$ag_values[] = $ag['id'];
		$ag_descs[] = $ag['name'];
	}

	$mod_values = $mod_descs = array();
	foreach($MODULES as $mod) {
		$mod_values[] = $mod;
		$mod_descs[] = getLL('module_'.$mod);
	}

	$KOTA['ko_admin'] = array(
		'_access' => array(
			'module' => 'admin',
			'chk_col' => '',
			'level' => 5,
		),
		'_multititle' => array(
			'name' => '',
		),
		'_inlineform' => array(
			'redraw' => array(
				'sort' => 'sort_logins',
				'fcn' => 'ko_set_logins_list();'
			),
			'module' => 'admin',
		),
		'_listview' => array(
			10 => array('name' => 'login', 'sort' => 'login', 'multiedit' => FALSE, 'filter' => TRUE),
			20 => array('name' => 'disabled', 'sort' => 'disabled', 'multiedit' => FALSE, 'filter' => FALSE),
			30 => array('name' => 'modules', 'sort' => FALSE, 'multiedit' => FALSE, 'filter' => TRUE),
			40 => array('name' => 'admingroups', 'sort' => 'admingroups', 'multiedit' => FALSE, 'filter' => TRUE),
			50 => array('name' => 'leute_id', 'sort' => 'leute_id', 'multiedit' => FALSE, 'filter' => FALSE),
		),
		'login' => array(
			'form' => array(
				'type' => 'text',
			),
		),
		'disabled' => array(
			'list' => 'FCN:kota_listview_login_status',
		),
		'modules' => array(
			'list' => 'FCN:kota_listview_modules',
			'form' => array(
				'type' => 'select',
				'params' => 'size="0"',
				'values' => $mod_values,
				'descs' => $mod_descs,
			),
		),
		'admingroups' => array(
			'list' => 'FCN:kota_listview_admingroups4login',
			'form' => array(
				'type' => 'select',
				'params' => 'size="0"',
				'values' => $ag_values,
				'descs' => $ag_descs,
			),
		),
		'leute_id' => array(
			'list' => 'FCN:kota_listview_person4login',
		),
		'ical_hash' => array(
		)
	);
}




if(in_array('ko_admingroups', $KOTA_TABLES)) {
	$modules_values = array_merge(array(''), $GLOBALS['MODULES']);
	$modules_descs = array('');
	foreach($GLOBALS['MODULES'] as $m) {
		$modules_descs[$m] = getLL('module_'.$m) ? getLL('module_'.$m) : $m;
	}

	$KOTA['ko_admingroups'] = array(
		'_access' => array(
			'module' => 'admin',
			'chk_col' => '',
			'level' => 5,
		),
		'_multititle' => array(
			'name' => '',
		),
		'_inlineform' => array(
			'redraw' => array(
				'fcn' => 'ko_list_admingroups();'
			),
			'module' => 'admin',
		),
		'_listview' => array(
			10 => array('name' => 'name', 'sort' => 'name', 'multiedit' => FALSE, 'filter' => TRUE),
			20 => array('name' => 'modules', 'sort' => 'modules', 'multiedit' => FALSE, 'filter' => TRUE),
			30 => array('name' => 'logins', 'sort' => FALSE, 'multiedit' => FALSE, 'filter' => FALSE),
		),
		'name' => array(
			'filter' => array(
				'type' => 'text',
				'params' => 'size="20"',
			),
		),
		'modules' => array(
			'list' => 'FCN:kota_listview_modules',
			'form' => array(
				'type' => 'select',
				'params' => 'size="0"',
				'values' => $modules_values,
				'descs' => $modules_descs,
			),
		),
		'logins' => array(
			'list' => 'FCN:kota_listview_logins4admingroup',
		),
	);
}


if(in_array('ko_reminder', $KOTA_TABLES)) {
	$KOTA['ko_reminder'] = array(
		'_access' => array(
			'module' => 'daten',
			'chk_col' => '', // TODO : Access
			'level' => 1,
		),
		"_multititle" => array(
			"title" => "",
		),
		'_inlineform' => array(
			'redraw' => array(
				'fcn' => 'ko_list_reminders();'
			),
			'module' => 'daten',
		),
		'_special_cols' => array(
			'crdate' => 'crdate',
			'cruser' => 'cruser',
			'lastchange' => 'lastchange',
			'lastchange_user' => 'lastchange_user',
		),
		'_listview' => array(
			10 => array('name' => 'title', 'sort' => 'title', 'multiedit' => 'title', 'filter' => FALSE),
			20 => array('name' => 'action', 'sort' => 'action', 'multiedit' => 'action', 'filter' => FALSE),
			30 => array('name' => 'deadline', 'sort' => 'deadline', 'multiedit' => 'deadline', 'filter' => FALSE),
			40 => array('name' => 'recipients_mails', 'sort' => 'recipients_mails', 'multiedit' => 'recipients_mails', 'filter' => FALSE),
			50 => array('name' => 'status', 'sort' => 'status', 'multiedit' => 'status', 'filter' => FALSE),
		),
		'_types' => array(
			'field' => 'type',
			'default' => 0,
			'types' => array(
				1 => array(
					'use_fields' => array('title', 'action', 'deadline', 'subject', 'replyto_email', 'text', 'status', 'recipients_groups', 'recipients_leute', 'recipients_mails'),
					'add_fields' => array(
						'filter' => array(
							'form' => array_merge(array(
								'type' => 'select',
								"params" => 'size="0"',
							), kota_get_form('ko_reminder', 'filter_event')),
						),
					),
				),
			),
		),

		"title" => array(
			'list' => 'ko_html',
			"pre" => "ko_html",
			"post" => 'js',
			"form" => array(
				"type" => "text",
				"params" => 'size="60" maxlength="100"',
			),
		),
		'status' => array(
			'list' => 'FCN:kota_listview_boolyesno',
			'form' => array(
				'type' => 'switch',
				'label_0' => getLL('no'),
				'label_1' => getLL('yes'),
				'value' => '0',
			),
		),
		"action" => array(
			"list" => 'ko_html',
			"form" => array_merge(array(
				"type" => "select",
				"params" => 'size="0"',
			), kota_get_form('ko_reminder', 'action')),
		),
		"deadline" => array(
			'list' => 'kota_reminder_get_deadlines("@VALUE@")',
			"form" => array_merge(array(
				"type" => "select",
				"params" => 'size="0"',
			), kota_get_form("ko_reminder", "deadline")),
		),
		'recipients_leute' => array(
			'form' => array(
				'type' => 'peoplesearch',
				'noinline' => TRUE,
				'show_add' => TRUE,
			),
		),
		'recipients_groups' => array(
			'form' => array(
				'type' => 'groupsearch',
				'include_roles' => TRUE,
				'show_add' => TRUE,
			),
		),
		'recipients_mails' => array(
			'list' => 'FCN:kota_reminder_get_recipients',
			'post' => 'FCN:kota_explode_trim_implode',
			'form' => array(
				'type' => 'textarea',
				"params" => 'cols="50" rows="4"',
				'new_row' => true,
			),
		),
		"subject" => array(
			'list' => 'ko_html',
			"form" => array(
				"type" => "text",
			),
		),
		'replyto_email' => array(
			'list' => 'ko_html',
			'form' => array(
				'type' => 'text',
				'new_row' => true,
			),
		),

		"text" => array(
			"form" => array(
				"type" => "richtexteditor",
			),
		),


		'_form_layout' => [
			'general' => [
				'group' => FALSE,
				'sorting' => 10,
				'groups' => [
					'general' => [
						'sorting' => 10,
						'group' => TRUE,
						'rows' => [
							10 => ['title' => 6, 'status' => 6],
							20 => ['action' => 6, 'deadline' => 6],
							30 => ['filter' => 6],
						],
					],
					'recipient' => [
						'sorting' => 20,
						'group' => TRUE,
						'rows' => [
							10 => ['recipients_leute' => 6, 'recipients_groups' => 6],
							20 => ['recipients_mails' => 6],
						],
					],
					'content' => [
						'sorting' => 30,
						'group' => TRUE,
						'rows' => [
							10 => ['subject' => 6, 'replyto_email' => 6],
							20 => ['text' => 6],
						],
					],
				],
			],
			'_default_cols' => 6,
			'_default_width' => 6,
			'_ignore_fields' => [],
		],


	);
}


if(in_array('ko_crm_projects', $KOTA_TABLES)) {
	$KOTA['ko_crm_projects'] = array(
		'_access' => array(
			'module' => 'crm',
			'chk_col' => 'id',
			'level' => 5,
		),
		"_multititle" => array(
			"title" => "",
		),
		'_inlineform' => array(
			'redraw' => array(
				'fcn' => 'ko_list_crm_projects();'
			),
			'module' => 'crm',
		),
		'_special_cols' => array(
			'crdate' => 'crdate',
			'cruser' => 'cruser',
			'lastchange' => 'lastchange',
			'lastchange_user' => 'lastchange_user',
		),
		'_listview' => array(
			10 => array('name' => 'number', 'sort' => 'number', 'multiedit' => 'number', 'filter' => TRUE),
			20 => array('name' => 'title', 'sort' => 'title', 'multiedit' => 'title', 'filter' => TRUE),
			30 => array('name' => 'stopdate', 'sort' => 'stopdate', 'multiedit' => 'stopdate', 'filter' => FALSE),
			40 => array('name' => 'project_status', 'sort' => 'project_status', 'multiedit' => 'project_status', 'filter' => 'project_status'),
			50 => array('name' => 'status_ids', 'sort' => FALSE, 'multiedit' => FALSE, 'filter' => FALSE),
		),
		"number" => array(
			"pre" => "ko_html",
			"form" => array(
				"type" => "text",
			),
		),
		"title" => array(
			'list' => 'FCN:kota_listview_crm_project_title',
			"pre" => "ko_html",
			"form" => array(
				"type" => "text",
			),
		),
		"project_status" => array(
			"form" => array(
				"type" => "textplus",
			),
		),
		"stopdate" => array(
			'list' => "sql2datum('@VALUE@')",
			"pre" => "sql2datum('@VALUE@')",
			"post" => "sql_datum('@VALUE@')",
			"form" => array(
				"type" => "jsdate",
			),
		),
		"status_ids" => array(
			'list' => 'FCN:kota_listview_crm_project_status_ids',
			'form' => array_merge(array(
				'type' => 'doubleselect',
			), kota_get_form('ko_crm_projects', 'status_ids')),
		),
	);

	if(ko_module_installed("donations")) {
		$KOTA['ko_crm_projects']["total_amount"] = [
			"list" => "FCN:kota_listview_crm_project_total_amount",
		];

		$KOTA['ko_crm_projects']['_listview'][] = [
			"name" => "total_amount",
			"sort" => FALSE,
			"multiedit" => FALSE,
			'filter' => FALSE
		];

		$KOTA['ko_crm_projects']['_listview_default'] = ['number', 'title', 'stopdate', 'project_status', 'status_ids', 'total_amount'];
	}
}


if(in_array('ko_crm_status', $KOTA_TABLES)) {
	$deadlines = ko_get_crm_deadlines(FALSE);

	$KOTA['ko_crm_status'] = array(
		'_access' => array(
			'module' => 'crm',
			'chk_col' => '',
			'level' => 5,
		),
		"_multititle" => array(
			"title" => "",
		),
		'_inlineform' => array(
			'redraw' => array(
				'fcn' => 'ko_list_crm_status();'
			),
			'module' => 'crm',
		),
		'_special_cols' => array(
			'crdate' => 'crdate',
			'cruser' => 'cruser',
			'lastchange' => 'lastchange',
			'lastchange_user' => 'lastchange_user',
		),
		'_listview' => array(
			10 => array('name' => 'title', 'sort' => 'title', 'multiedit' => 'title', 'filter' => FALSE),
			20 => array('name' => 'default_deadline', 'sort' => 'default_deadline', 'multiedit' => 'default_deadline', 'filter' => FALSE),
		),
		"title" => array(
			'list' => 'ko_html',
			"pre" => "ko_html",
			"form" => array(
				"type" => "text",
			),
		),
		"default_deadline" => array(
			'list' => 'ko_get_crm_deadlines(FALSE, "@VALUE@")',
			'post' => 'uint',
			'form' => array(
				'type' => "select",
				'values' => array_merge(array(''), array_keys($deadlines)),
				'descs' => array_merge(array(''), array_values($deadlines)),
			),
		),
	);
}


if(in_array('ko_crm_contacts', $KOTA_TABLES)) {
	$KOTA['ko_crm_contacts'] = array(
		'_access' => array(
			'module' => 'crm',
			'chk_col' => 'ALL&project_id',
			'level' => 5,
		),
		"_multititle" => array(
			"title" => "",
		),
		'_inlineform' => array(
			'redraw' => array(
				'fcn' => 'ko_list_crm_contacts();'
			),
			'module' => 'crm',
		),
		'_special_cols' => array(
			'crdate' => 'crdate',
			'cruser' => 'cruser',
			'lastchange' => 'lastchange',
			'lastchange_user' => 'lastchange_user',
		),
		'_listview' => array(
			10 => array('name' => 'date', 'sort' => 'date', 'multiedit' => 'date', 'filter' => TRUE),
			20 => array('name' => 'title', 'sort' => 'title', 'multiedit' => 'title', 'filter' => TRUE),
			30 => array('name' => 'description', 'sort' => 'description', 'multiedit' => 'description', 'filter' => TRUE),
			40 => array('name' => 'type', 'sort' => 'type', 'multiedit' => 'type', 'filter' => TRUE),
			50 => array('name' => 'project_id', 'sort' => 'project_id', 'multiedit' => 'project_id', 'filter' => TRUE),
			60 => array('name' => 'file', 'sort' => FALSE, 'multiedit' => FALSE, 'filter' => FALSE),
			70 => array('name' => 'reference', 'sort' => FALSE, 'multiedit' => FALSE, 'filter' => FALSE),
			80 => array('name' => 'status_id', 'sort' => 'status_id', 'multiedit' => 'status_id', 'filter' => TRUE),
			90 => array('name' => 'cruser', 'sort' => 'cruser', 'multiedit' => FALSE, 'filter' => TRUE),
			100 => array('name' => 'deadline', 'sort' => 'deadline', 'multiedit' => 'deadline', 'filter' => TRUE),
		),
		'_listview_default' => array('date', 'title', 'description', 'type', 'project_id', 'file', 'reference', 'status_id', 'deadline'),
		"type" => array(
			"list" => 'FCN:kota_listview_ll',
			"form" => array(
				"type" => "select",
				'values' => array('', 'email', 'mailing', 'sms', 'tel', 'letter', 'meeting', 'training', 'donation', 'other'),
				'descs' => array('', getLL('kota_ko_crm_contacts_type_email'), getLL('kota_ko_crm_contacts_type_mailing'), getLL('kota_ko_crm_contacts_type_sms'), getLL('kota_ko_crm_contacts_type_tel'), getLL('kota_ko_crm_contacts_type_letter'), getLL('kota_ko_crm_contacts_type_meeting'), getLL('kota_ko_crm_contacts_type_training'), getLL('kota_ko_crm_contacts_type_donation'), getLL('kota_ko_crm_contacts_type_other')),
			),
		),
		"date" => array(
			'list' => "sql2datetime('@VALUE@')",
			"pre" => "sql2datetime('@VALUE@')",
			"post" => "sql_datetime('@VALUE@', 'now')",
			"form" => array(
				"type" => "jsdate",
				"picker_mode" => "datetime",
			),
		),
		"title" => array(
			'list' => 'ko_html',
			"pre" => "ko_html",
			"form" => array(
				"type" => "text",
			),
		),
		"description" => array(
			"list" => "kota_no_fcn_listview_description('@VALUE@', 200)",
			"pre" => "ko_html",
			"form" => array(
				"type" => "richtexteditor",
			),
		),
		"project_id" => array(
			'list' => "db_get_column('ko_crm_projects', '@VALUE@', 'number,title', ' ')",
			'form' => array_merge(array(
				'type' => 'select',
				'params' => 'onchange="javascript:selProject(this.value);"',
			), kota_get_form('ko_crm_contacts', 'project_id')),
		),
		"status_id" => array(
			'list' => "db_get_column('ko_crm_status', '@VALUE@', 'title')",
			'form' => array(
				'type' => 'select',
				'params' => 'onchange="javascript:selStatus(this.value);"',
				'data_func' => 'kota_form_data_crm_contacts_status_id',
			),
			'filter' => array(
				'type' => 'select',
			),
		),
		"leute_ids" => array(
			'pre' => 'FCN:kota_pre_crm_contacts_leute_ids',
			'post' => 'FCN:kota_post_crm_contacts_leute_ids',
			'form' => array(
				'type' => 'peoplesearch',
				'dontsave' => TRUE,
				'ignore_test' => TRUE,
				'show_add' => TRUE,
			)
		),
		"deadline" => array(
			"list" => 'FCN:kota_listview_crm_deadline',
			'xls' => "sql2datum('@VALUE@')",
			'pre' => "sql2datum('@VALUE@')",
			'post' => "sql_datum('@VALUE@')",
			'form' => array(
				'type' => 'jsdate',
			),
		),
		"file" => array(
			'list' => 'FCN:kota_listview_file',
			'form' => array(
				'type' => 'file',
			)
		),
		"reference" => array(
			'list' => 'FCN:kota_listview_crm_contacts_leute_values',
		),
		"cruser" => array(
			'pre' => "FCN:kota_listview_login",
			'list' => "FCN:kota_listview_login",
			'form' => array(
				'type' => 'html',
				'ignore_test' => TRUE,
			),
		),
	);
	$x = kota_get_form('ko_crm_projects', 'status_ids');
	$statusData = array();
	if(!empty($x['values'])) {
		foreach ($x['values'] as $k => $v) {
			$statusData[$x['values'][$k]] = $x['descs'][$k];
		}
	}
	$KOTA['ko_crm_contacts']['status_id']['filter']['data'] = $statusData;
}



if(in_array('ko_subscription_forms', $KOTA_TABLES)) {
	$KOTA['ko_subscription_forms'] = array(
		'_access' => array(
			'module' => 'subscription',
			'level' => 1,
			'chk_col' => 'ALL&form_group',
		),
		"_multititle" => array(
			"title" => "",
		),
		'_inlineform' => array(
			'redraw' => array(
				'sort' => 'sort_forms',
				'fcn' => 'ko_subscription_form_list();'
			),
			'module' => 'subscription',
		),
		'_special_cols' => array(
			'crdate' => 'crdate',
			'cruser' => 'cruser',
			'lastchange' => 'lastchange',
			'lastchange_user' => 'lastchange_user',
		),
		'_listview' => array(
			10 => array('name' => 'title', 'sort' => 'title', 'multiedit' => 'title', 'filter' => TRUE),
			20 => array('name' => 'form_group', 'sort' => 'form_group', 'multiedit' => 'form_group', 'filter' => TRUE),
			30 => array('name' => 'groups', 'sort' => FALSE, 'multiedit' => 'groups', 'filter' => FALSE),
			40 => array('name' => 'url_segment', 'sort' => false, 'multiedit' => false, 'filter' => false),
			//50 => array('name' => 'notification_to', 'sort' => 'notification_to', 'multiedit' => 'notification_to', 'filter' => TRUE),
			//60 => array('name' => 'response','sort' => 'response','multiedit' => 'response', 'filter' => TRUE),
			//70 => array('name' => 'response_replyto', 'sort' => 'response_replyto', 'multiedit' => 'response_replyto', 'filter' => TRUE),
			80 => array('name' => 'moderated', 'sort' => 'moderated', 'multiedit' => 'moderated', 'filter' => TRUE),
			90 => array('name' => 'layout', 'sort' => 'layout', 'multiedit' => 'layout', 'filter' => TRUE),
			//100 => array('name' => 'protected', 'sort' => 'protected', 'multiedit' => 'protected', 'fitler' => TRUE),
			//110 => array('name' => 'cruser', 'sort' => 'cruser', 'multiedit' => FALSE, 'filter' => TRUE),
		),
		'url_segment' => array(
			'list' => 'FCN:kota_subscription_form_link',
			'pre' => 'FCN:kota_subscription_form_link',
			'form' => array(
				'type' => 'html',
				'dontsave' => TRUE,
				'ignore_test' => TRUE,
			),
		),
		'iframe_code' => array(
			'pre' => 'FCN:kota_subscription_form_get_iframe_code',
			'form' => array(
				'type' => 'html',
				'ignore_test' => true,
			),
		),
		'form_group' => array(
			'list' => 'FCN:kota_listview_subscription_form_groups',
			'form' => array(
				'type' => 'select',
				'data_func' => 'kota_subscription_get_form_group_select_options',
			),
		),
		'layout' => array(
			'list' => 'FCN:kota_listview_subscription_form_layout',
			'form' => array(
				'type' => 'select',
				'data_func' => 'kota_subscription_get_form_layout_select_options',
			),
		),
		'title' => array(
			'list' => 'ko_html',
			'pre' => 'ko_html',
			'post' => 'FCN:kota_subscription_form_save_url_segment',
			'form' => array(
				'type' => 'text',
				'mandatory' => true,
			),
		),
		'groups' => array(
			'post' => 'format_userinput("@VALUE@", "group_role")',
			'list' => 'FCN:kota_listview_group_names',
			'form' => array(
				'type' => 'groupsearch',
				'include_roles' => true,
				'additional_where' => " AND (`stop` = '0000-00-00' OR `stop` > NOW()) ",
			),
		),
		'fields' => array(
			'post' => 'FCN:kota_subscription_form_fields_store',
			'pre' => 'FCN:kota_subscription_form_fields_render',
			'form' => array(
				'type' => 'html',
			),
		),
		'notification_to' => array(
			'list' => 'ko_html',
			'pre' => 'ko_html',
			'form' => array(
				'type' => 'text',
			),
		),
		'notification_subject' => array(
			'list' => 'ko_html',
			'pre' => 'ko_html',
			'form' => array(
				'type' => 'text',
			),
		),
		'notification_body' => array(
			"list" => "kota_no_fcn_listview_description('@VALUE@', 200)",
			"pre" => "ko_html",
			"form" => array(
				"type" => "richtexteditor",
			),
		),
		'response' => array(
			'list' => 'FCN:kota_listview_boolyesno',
			'form' => array(
				'type' => 'switch',
				'default' => 1,
			),
		),
		'response_replyto' => array(
			'list' => 'ko_html',
			'pre' => 'ko_html',
			'form' => array(
				'type' => 'text',
				"html_type" => "email",
			),
		),
		'response_subject_subscription' => array(
			'list' => 'ko_html',
			'pre' => 'ko_html',
			'form' => array(
				'type' => 'text',
			),
		),
		'response_body_subscription' => array(
			"list" => "kota_no_fcn_listview_description('@VALUE@', 200)",
			"pre" => "ko_html",
			"form" => array(
				"type" => "richtexteditor",
			),
		),
		'response_subject_edit' => array(
			'list' => 'ko_html',
			'pre' => 'ko_html',
			'form' => array(
				'type' => 'text',
			),
		),
		'response_body_edit' => array(
			"list" => "kota_no_fcn_listview_description('@VALUE@', 200)",
			"pre" => "ko_html",
			"form" => array(
				"type" => "richtexteditor",
			),
		),
		"moderated" => array(
			'list' => 'FCN:kota_listview_boolyesno',
			'form' => array(
				'type' => 'switch',
				'default' => 0,
			),
		),
		"overflow" => array(
			'list' => 'FCN:kota_listview_boolyesno',
			'form' => array(
				'type' => 'switch',
				'default' => 0,
			),
		),
		'protected' => array(
			'list' => 'FCN:kota_listview_boolyesno',
			'form' => array(
				'type' => 'switch',
				'default' => 0,
			),
		),
		'edit_link' => array(
			'list' => 'FCN:kota_listview_boolyesno',
			'form' => array(
				'type' => 'switch',
				'default' => 0,
			),
		),
		'double_opt_in' => array(
			'list' => 'FCN:kota_listview_boolyesno',
			'form' => array(
				'type' => 'switch',
				'default' => 0,
			),
		),
		'double_opt_in_title' => array(
			'list' => 'ko_html',
			'pre' => 'ko_html',
			'form' => array(
				'type' => 'text',
			),
		),
		'double_opt_in_text' => array(
			"list" => "kota_no_fcn_listview_description('@VALUE@', 200)",
			"pre" => "ko_html",
			'form' => array(
				'type' => 'richtexteditor',
			),
		),
		'confirmation_title' => array(
			'list' => 'ko_html',
			'pre' => 'ko_html',
			'form' => array(
				'type' => 'text',
			),
		),
		'confirmation_text' => array(
			"list" => "kota_no_fcn_listview_description('@VALUE@', 200)",
			"pre" => "ko_html",
			'form' => array(
				'type' => 'richtexteditor',
			),
		),
		'cruser' => array(
			'pre' => "FCN:kota_listview_login",
			'list' => "FCN:kota_listview_login",
			'form' => array(
				'type' => 'html',
				'ignore_test' => TRUE,
			),
		),
		'crdate' => array(
			'list' => 'FCN:kota_listview_datecol',
		),
		'_form_layout' => array(
			'general' => array(
				'group' => FALSE,
				'sorting' => 10,
				'groups' => array(
					'general' => array(
						'sorting' => 10,
						'group' => true,
						'show_save' => TRUE,
						'rows' => array(
							10 => array('title' => 6, 'form_group' => 6),
							20 => array('groups' => 6, 'layout' => 6),
							30 => array('moderated' => 6, 'overflow' => 6),
							40 => array('protected' => 6, 'edit_link' => 6),
// 							50 => array('double_opt_in' => 6),
							60 => array('url_segment' => 6,'iframe_code' => 6),
						),
					),
					'content' => array(
						'sorting' => 20,
						'group' => true,
						'show_save' => TRUE,
						'rows' => array(
							10 => array('fields' => 12),
						),
					),
					'response' => array(
						'sorting' => 30,
						'group' => true,
						'rows' => array(
							10 => array('response' => 6,'response_replyto' => 6),
							20 => array('response_subject_subscription' => 6,'response_subject_edit' => 6),
							30 => array('response_body_subscription' => 6,'response_body_edit' => 6),
						),
					),
					'notification' => array(
						'sorting' => 40,
						'group' => true,
						'rows' => array(
							20 => array('notification_to' => 6,'notification_subject' => 6),
							30 => array('notification_body' => 6),
						),
					),
					'confirmation' => array(
						'sorting' => 40,
						'group' => true,
						'rows' => array(
							10 => array('confirmation_title' => 6,'double_opt_in_title' => 6),
							20 => array('confirmation_text' => 6,'double_opt_in_text' => 6),
						),
					),
				),
			),
			'_default_cols' => 6,
			'_default_width' => 6,
		),
	);
}

if(in_array('ko_subscription_form_groups', $KOTA_TABLES)) {
	$KOTA['ko_subscription_form_groups'] = array(
		'_access' => array(
			'module' => 'subscription',
			'level' => 1,
		),
		"_multititle" => array(
			"name" => "",
		),
		'_special_cols' => array(
			'crdate' => 'crdate',
			'cruser' => 'cruser',
		),
		'_listview' => array(
			10 => array('name' => 'name', 'sort' => 'name', 'multiedit' => 'name', 'filter' => TRUE),
			20 => array('name' => 'cruser', 'sort' => 'cruser', 'multiedit' => FALSE, 'filter' => TRUE),
		),
		'_inlineform' => array(
			'redraw' => array(
				'sort' => '',
				'fcn' => 'ko_subscription_form_group_list();'
			),
			'module' => 'subscription',
		),
		'name' => array(
			'list' => 'ko_html',
			'pre' => 'ko_html',
			'form' => array(
				'type' => 'text',
			),
		),
		'cruser' => array(
			'pre' => "FCN:kota_listview_login",
			'list' => "FCN:kota_listview_login",
		),
		'crdate' => array(
			'list' => 'FCN:kota_listview_datecol',
		),
	);
}



if(in_array('ko_plugins', $KOTA_TABLES)) {
	$KOTA['ko_plugins'] = array(
		'_listview' => array(
			0 => array('name' => 'sql_diffs'),
			5 => array('name' => 'updates'),
			10 => array('name' => 'key'),
			20 => array('name' => 'title'),
			30 => array('name' => 'description'),
			40 => array('name' => 'dependencies'),
			50 => array('name' => 'version'),
			60 => array('name' => 'status'),
		),
	);
}





//Add definition for ko_reservation_mod only used for multiedit of open reservations
if(in_array('ko_reservation', $KOTA_TABLES) || in_array('ko_reservation_mod', $KOTA_TABLES)) {
	$new = array();
	foreach ($KOTA["ko_reservation_mod"] as $k => $v) {
		if (substr($k, 0, 1) == '_') $new[$k] = $v;
	}
	foreach ($KOTA["ko_reservation"] as $k => $v) {
		if (substr($k, 0, 1) != '_') {
			if (!is_array($KOTA["ko_reservation_mod"][$k])) {
				$new[$k] = $v;
			} else {
				$new[$k] = $KOTA["ko_reservation_mod"][$k];
				foreach ($v as $kk => $vv) {
					if (!isset($new[$k][$kk])) {
						$new[$k][$kk] = $vv;
					}
				}
			}
		}
	}
	$KOTA["ko_reservation_mod"] = $new;
}



//Only show kommentar2 for events to logged in users
if(in_array('ko_event', $KOTA_TABLES) && $_SESSION['ses_userid'] != ko_get_guest_id()) {
	$KOTA['ko_event']['_listview']['35'] = array('name' => 'kommentar2', 'sort' => 'kommentar2', 'filter' => TRUE);
}

//KOTA for ko_event_mod as a copy from ko_event
if(in_array('ko_event', $KOTA_TABLES)) {
	$KOTA["ko_event_mod"] = $KOTA["ko_event"];

	$KOTA['ko_event_mod']['_listview'][756] = array('name' => '_user_id', 'sort' => '_user_id');
	$KOTA['ko_event_mod']['_user_id'] = array('list' => 'FCN:kota_listview_login');
	$KOTA['ko_event_mod']['_listview_default'][] = '_user_id';

	$KOTA['ko_event_mod']['reservationen']['list'] = 'FCN:kota_listview_event_mod_res';

	unset($KOTA['ko_event_mod']['_access']['condition']);
}



$origLeuteData = $KOTA['ko_leute']['_form_layout']['data'];

//Include kota.inc from web directory (specific to each installation)
if(file_exists($BASE_PATH."config/kota.inc")) {
	include($BASE_PATH."config/kota.inc");
}

//Allow plugins to change KOTA
foreach($GLOBALS['PLUGINS'] as $plugin) {
	$file = $BASE_PATH."plugins/".$plugin["name"]."/kota.inc";
	if(file_exists($file)) include($file);
}

//Merge data tab entries.
// Main plugin usually overwrites _form_layout
// but other plugins (e.g. billing or rodel) add new fields, so they have to be merged
foreach($origLeuteData as $k => $v) {
	if($k == 'groups') continue;
	$KOTA['ko_leute']['_form_layout']['data'][$k] = $v;
}
$KOTA['ko_leute']['_form_layout']['data']['groups'] = array_merge((array)$KOTA['ko_leute']['_form_layout']['data']['groups'], $origLeuteData['groups']);;



if ($access['crm']['MAX'] > 0) {
	$KOTA['ko_leute']['_form_layout']['crm'] = [
		'sorting' => 9000,
		'group' => TRUE,
		'title' => "CRM",
		'groups' => [
			'crm' => [
				'group' => TRUE,
				'sorting' => 10,
				'rows' => [
					10 => ["crm" => 12],
				]
			]
		],
	];

	$KOTA['ko_leute']['crm'] = [
		'pre' => 'FCN:my_kota_pre_ko_leute_crm_entries_html',
		'form' => [
			'type' => 'html',
			'ignore_test' => TRUE,
			'dontsave' => TRUE,
		],
	];
}

//Order listview arrays by index, so loops over them will be in the right order
foreach($KOTA as $table => $table_data) {
	if(!isset($table_data["_listview"])) continue;
	ksort($KOTA[$table]['_listview']);
}

?>
