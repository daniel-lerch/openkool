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

if(in_array('ko_event', $KOTA_TABLES)) {
	$KOTA['ko_event'] = array(
		'_access' => array(
			'module' => 'daten',
			'chk_col' => 'eventgruppen_id',
			'level' => 3,
			'condition' => "return 'import_id' == '';",  //Imported events may not be edited
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
				'fcn' => 'ko_list_events(\'all\', FALSE);'
			),
			'module' => 'daten',
		),
		"_listview" => array(
			10 => array("name" => "startdatum", "sort" => "startdatum", "multiedit" => "startdatum,enddatum"),
			20 => array("name" => "eventgruppen_id", "sort" => "eventgruppen_id"),
			25 => array('name' => 'title', 'sort' => 'title', 'filter' => TRUE),
			30 => array("name" => "kommentar", "sort" => "kommentar", 'filter' => TRUE),
			//35 for kommentar2 if not ko_guest
			40 => array("name" => "startzeit", "sort" => "startzeit", "multiedit" => "startzeit,endzeit", 'filter' => TRUE),
			50 => array("name" => "room", "sort" => "room", 'filter' => TRUE),
			//60 is reserved for rota (set further down) only if rota module is installed
			//70 is reserved for reservations (set further down) only if res module is installed
			80 => array("name" => "registrations", "sort" => "registrations", "filter" => TRUE),
		),
		'_listview_default' => array('startdatum', 'eventgruppen_id', 'title', 'startzeit', 'room', 'rota', 'reservationen'),

		"eventgruppen_id" => array(
			"list" => 'db_get_column("ko_eventgruppen", @VALUE@, "name")',
			"post" => 'uint',
			"form" => array_merge(array(
				"type" => "dynselect",
				"js_func_add" => "event_cal_select_add",
				"params" => 'size="5"',
				'new_row' => true,
			), kota_get_form("ko_event", "eventgruppen_id")),
		),  //eventgruppen_id
		'title' => array(
			'list' => 'ko_html',
			'pre' => 'ko_html',
			'form' => array(
				'type' => 'text',
				'params' => 'size="60" maxlength="255"',
			),
		),  //title
		"url" => array(
			"pre" => "",
			"form" => array(
				"type" => "text",
				"params" => 'size="60"',
			),
		),  //url
		"startdatum" => array(
			"list" => 'FCN:kota_listview_date',
			"pre" => "sql2datum('@VALUE@')",
			"post" => "sql_datum('@VALUE@')",
			"form" => array(
				"type" => "jsdate",
				'noinline' => TRUE,
			),
		),  //startdatum
		"enddatum" => array(
			"pre" => 'FCN:kota_pre_enddate',
			'post' => 'FCN:kota_post_enddate',
			"form" => array(
				"type" => "jsdate",
				'noinline' => TRUE,
			),
		),  //enddatum
		"startzeit" => array(
			"list" => 'FCN:kota_listview_time',
			"pre" => "sql_zeit('@VALUE@')",
			"post" => "sql_zeit('@VALUE@')",
			"form" => array(
				"type" => "text",
				"params" => 'size="11" maxlength="11"',
			),
		),  //startzeit
		"endzeit" => array(
			"pre" => "sql_zeit('@VALUE@')",
			"post" => "sql_zeit('@VALUE@')",
			"form" => array(
				"type" => "text",
				"params" => 'size="11" maxlength="11"',
			),
		),  //endzeit
		"room" => array(
			"list" => "ko_html",
			"form" => array(
				"type" => "textplus",
				"params" => 'size="0"',
				"params_PLUS" => 'size="50" maxlength="50"',
				'where' => "WHERE `import_id` = ''",
			),
		),  //room
		"kommentar" => array(
			'list' => 'ko_html;FCN:kota_listview_rootid',
			"pre" => "ko_html",
			"form" => array(
				"type" => "textarea",
				"params" => 'cols="50" rows="4"',
			),
		),  //kommentar
		"kommentar2" => array(
			"list" => "ko_html",
			"pre" => "ko_html",
			"form" => array(
				"type" => "textarea",
				"params" => 'cols="50" rows="4"',
			),
		),  //kommentar2
		"registrations" => array(
			"list" => "FCN:kota_listview_ko_event_registrations",
		),  //registrations
	);

	if(ko_get_userpref($_SESSION['ses_userid'], 'daten_rooms_only_future') == 1) {
		$KOTA['ko_event']['room']['form']['where'] .= " AND `startdatum` >= '".date('Y-m-d')."'";
	}

	if(ko_module_installed('rota')) {
		$KOTA['ko_event']['rota']['form'] = array('type' => 'checkbox');
		$KOTA['ko_event']['rota']['list'] = 'FCN:kota_listview_boolyesno';
		$KOTA['ko_event']['_listview'][60] = array('name' => 'rota', 'sort' => 'rota', 'filter' => TRUE);

		ko_get_access('rota');
		$rota_teams = db_select_data('ko_rota_teams', "WHERE 1", '*', 'ORDER BY name ASC');
		$tc = 10010;
		//Add divider before all rota teams
		if(sizeof($rota_teams) > 0 && $access['rota']['MAX'] > 0) {
			$LOCAL_LANG['de']['kota_ko_event_rotateam_0'] = '--- '.getLL('rota_teams_list_title').' ---';
			$KOTA['ko_event']['rotateam_0'] = array('list' => 'FCN:kota_listview_rota_schedule');
			$KOTA['ko_event']['_listview'][$tc] = array('name' => 'rotateam_0', 'sort' => FALSE, 'multiedit' => FALSE);
			$tc += 10;

			foreach($rota_teams as $team) {
				if($access['rota']['ALL'] > 0 || $access['rota'][$team['id']] > 0) {
					$LOCAL_LANG['de']['kota_ko_event_rotateam_'.$team['id']] = getLL('rota_kota_prefix_ko_event').' '.$team['name'];
					$LOCAL_LANG['de']['kota_listview_ko_event_rotateam_'.$team['id']] = getLL('rota_kota_prefix_ko_event_short').' '.$team['name'];

					$KOTA['ko_event']['rotateam_'.$team['id']] = array('list' => 'FCN:kota_listview_rota_schedule');
					$KOTA['ko_event']['_listview'][$tc] = array('name' => 'rotateam_'.$team['id'], 'sort' => FALSE, 'multiedit' => FALSE);

					$tc += 10;
				}
			}
		}
	}

	if(ko_module_installed('reservation')) {
		$KOTA['ko_event']['reservationen']['list'] = 'FCN:kota_listview_event_reservations';
		$KOTA['ko_event']['_listview'][70] = array('name' => 'reservationen', 'sort' => FALSE, 'multiedit' => FALSE);
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
				'new_row' => TRUE,
				'ignore_test' => TRUE,
				'colspan' => 'colspan="2"',
			),
		);
	}

	// set event program kota
	if (ko_get_setting('activate_event_program') == 1) {
		$KOTA['ko_event_program'] = array(
			'_access' => array(
				'module' => 'daten',
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
				"pre" => "sql_zeit('@VALUE@')",
				"post" => "sql_zeit('@VALUE@')",
				"form" => array(
					"type" => "text",
					"params" => 'size="11" maxlength="11"',
				),
			),  //time
			"team" => array(
				"form" => array_merge(array(
					'type' => 'select',
					'params' => 'size="0"'
				), kota_get_form('ko_event_program', 'teams')),
			),
			'title' => array(
				'pre' => 'ko_html',
				'form' => array(
					'type' => 'textarea',
					'params' => 'cols="80" rows="5"',
				),
			),  //title
		);
	}
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
				'fcn' => 'ko_list_groups(\'all\', FALSE);'
			),
			'module' => 'daten',
		),
		"_listview" => array(
			10 => array("name" => "name", "sort" => "name", "multiedit" => "name,shortname", 'filter' => TRUE),
			15 => array("name" => "calendar_id", "sort" => "calendar_id", 'filter' => TRUE),
			16 => array("name" => "farbe", "sort" => "farbe", 'multiedit' => 'farbe', 'filter' => FALSE),
			//20 => array("name" => "room", "sort" => "room"),
			//30 => array("name" => "startzeit", "sort" => "startzeit", "multiedit" => "startzeit,endzeit"),
			35 => array('name' => 'title', 'sort' => 'title', 'filter' => TRUE),
			40 => array("name" => "kommentar", "sort" => "kommentar", 'filter' => TRUE),
			//50, 60, 70 are reserved for rota, tapes, res_combined (see below)
			80 => array("name" => "moderation", "sort" => "moderation", 'filter' => TRUE),
		),
		'_types' => array(
			'field' => 'type',
			'default' => 0,
			'types' => array(
				1 => array(  //Google calendar
					'use_fields' => array('calendar_id', 'name', 'shortname', 'farbe', 'gcal_url'),
					'add_fields' => array(
						'gcal_url' => array(
							'post' => 'strtr("@VALUE@", array("/ical/" => "/feeds/", ".ics" => ""));',
							'form' => array(
								'type' => 'text',
								'params' => 'size="60" maxlength="255"',
							),
						),
					),
				),
				2 => array(  //Rota week team
					'use_fields' => array('name', 'shortname', 'farbe'),
				),
				3 => array(  //iCal import
					'use_fields' => array('calendar_id', 'name', 'shortname', 'farbe', 'ical_url'),
					'add_fields' => array(
						'ical_url' => array(
							'form' => array(
								'type' => 'text',
								'params' => 'size="60" maxlength="255"',
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

		"calendar_id" => array(
			"post" => 'uint',
			"list" => 'db_get_column("ko_event_calendar", @VALUE@, "name")',
			"form" => array_merge(array(
				"type" => "textplus",
				"params_PLUS" => 'size="60" maxlength="200"',
			), kota_get_form("ko_eventgruppen", "calendar_id")),
		),  //gruppen_id
		"name" => array(
			'list' => 'FCN:kota_listview_ko_eventgruppen_name',
			"form" => array(
				"type" => "text",
				"params" => 'size="60" maxlength="100"'
			),
		),  //name
		"url" => array(
			"form" => array(
				"type" => "text",
				"params" => 'size="60" maxlength="255"'
			),
		),  //url
		"shortname" => array(
			"list" => "ko_html",
			"form" => array(
				"type" => "text",
				"params" => 'size="10" maxlength="5"'
			),
		),  //shortname
		"moderation" => array(
			"pre" => "ko_html",
			"post" => 'uint',
			"list" => "FCN:kota_listview_ll",
			"form" => array_merge(array(
				"type" => "select",
				"params" => 'size="0"',
			), kota_get_form("ko_eventgruppen", "moderation")),
		),  //moderation
		"room" => array(
			"list" => "ko_html",
			"form" => array(
				"type" => "textplus",
				"params" => 'size="0"',
				"params_PLUS" => 'size="50" maxlength="50"',
				"descimg" => "bullet_star.png",
			),
		),  //room
		"startzeit" => array(
			"list" => 'FCN:kota_listview_time',
			"pre" => "sql_zeit('@VALUE@')",
			"post" => "sql_zeit('@VALUE@')",
			"form" => array(
				"type" => "text",
				"params" => 'size="11" maxlength="11"',
				"descimg" => "bullet_star.png",
			),
		),  //startzeit
		"endzeit" => array(
			"pre" => "sql_zeit('@VALUE@')",
			"post" => "sql_zeit('@VALUE@')",
			"form" => array(
				"type" => "text",
				"params" => 'size="11" maxlength="11"',
				"descimg" => "bullet_star.png",
			),
		),  //endzeit
		'title' => array(
			'list' => 'ko_html',
			'pre' => 'ko_html',
			'form' => array(
				'type' => 'text',
				'params' => 'size="60" maxlength="255"',
				'descimg' => 'bullet_star.png',
			),
		),  //title
		"farbe" => array(
			'list' => 'FCN:kota_listview_color',
			"post" => 'str_replace("#", "", format_userinput("@VALUE@", "alphanum"))',
			"form" => array(
				"type" => "color",
				"params" => 'size="10" maxlength="7"',
			),
		),
		"kommentar" => array(
			"list" => "ko_html",
			"form" => array(
				"type" => "textarea",
				"params" => 'cols="50" rows="4"',
				"descimg" => "bullet_star.png",
				'new_row' => TRUE,
			),
		),  //kommentar
	);

	if(ko_module_installed('rota')) {
		$KOTA['ko_eventgruppen']['rota']['form'] = array('type' => 'checkbox', 'descimg' => 'bullet_star.png');
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

	if(ko_module_installed('tapes')) {
		$KOTA['ko_eventgruppen']['tapes']['form'] = array('type' => 'checkbox', 'new_row' => TRUE);
		$KOTA['ko_eventgruppen']['_listview'][60] = array('name' => 'tapes', 'sort' => 'tapes', 'filter' => TRUE);
		$KOTA['ko_eventgruppen']['tapes']['list'] = 'FCN:kota_listview_boolyesno';
	}//if(ko_module_installed(tapes))

	if(ko_module_installed('reservation') && in_array($ko_menu_akt, array('daten', 'home'))) {
		kota_ko_reservation_item_id_dynselect($res_values, $res_output, 2);
		$KOTA['ko_eventgruppen']['resitems'] = array(
			'post' => 'intlist',
			'form' => array( 'type' => 'dyndoubleselect',
											 'js_func_add' => 'resgroup_doubleselect_add',
											 'values' => $res_values,
											 'descs' => $res_output,
											 'params' => 'size="7"',
											 'descimg' => 'bullet_star.png',)
		);
		$KOTA['ko_eventgruppen']['res_combined'] = array(
			'post' => 'uint',
			'form' => array('type' => 'checkbox')
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
	}//if(ko_module_installed(reservation))

	if(ko_module_installed('groups')) {
		//Add group select to event group (notify)
		$KOTA['ko_eventgruppen']['notify'] = array('post' => 'format_userinput("@VALUE@", "alphanumlist")',
																							 'form' => array_merge(array(
																								'type' => 'doubleselect',
																								'params' => 'size="7"',
																							), kota_get_form('ko_eventgruppen', 'notify')),
																							);
	}//if(ko_module_installed(groups))


	// set event program kota
	if (ko_get_setting('activate_event_program') == 1) {
		$KOTA['ko_eventgruppen']['program'] = array(
			'form' => array(
				'type' => 'foreign_table',
				'table' => 'ko_eventgruppen_program',
				'new_row' => TRUE,
				'ignore_test' => TRUE,
				'colspan' => 'colspan="2"',
			),
		);
	}

	// set event program kota
	if (ko_get_setting('activate_event_program') == 1) {
		$KOTA['ko_eventgruppen_program'] = array(
			'_access' => array(
				'module' => 'daten',
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

			'title' => array(
				'pre' => 'ko_html',
				'form' => array(
					'type' => 'textarea',
					'params' => 'cols="80" rows="5"',
				),
			),  //title
			"time" => array(
				"pre" => "sql_zeit('@VALUE@')",
				"post" => "sql_zeit('@VALUE@')",
				"form" => array(
					"type" => "text",
					"params" => 'size="11" maxlength="11"',
				),
			),  //time
			"team" => array(
				"form" => array_merge(array(
					'type' => 'select',
					'params' => 'size="0"'
				), kota_get_form('ko_eventgruppen_program', 'teams')),
			),
		);
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
		),
		'_inlineform' => array(
			'redraw' => array(
				'sort' => 'sort_rota_teams',
				'fcn' => 'ko_rota_list_teams(FALSE);'
			),
			'module' => 'rota',
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
				'values' => array('event', 'week'),
				'descs' => array(getLL('kota_ko_rota_teams_rotatype_event'), getLL('kota_ko_rota_teams_rotatype_week')),
				'noinline' => TRUE,
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

	);

	//Manual ordering
	if(ko_get_setting('rota_manual_ordering')) {
		ko_get_access('rota');
		if($access['rota']['ALL'] > 4) {
			$KOTA['ko_rota_teams']['_sortable'] = TRUE;
		}
	}

	if(ko_module_installed('groups')) {
		//Add group select to rota team form
		$KOTA['ko_rota_teams']['group_id'] = array('post' => 'format_userinput("@VALUE@", "group_role")',
																							 'form' => array_merge(array(
																								 'type' => 'doubleselect',
																								 'params' => 'size="7"',
																							 ), kota_get_form('ko_rota_teams', 'groupid')),
																							);
	}

	$KOTA['ko_rota_teams']['allow_consensus'] = array(
		'list' => 'FCN:kota_listview_boolyesno',
		'form' => array(
			'type' => 'switch',
			'label_0' => getLL('no'),
			'label_1' => getLL('yes'),
			'value' => '0',
		),
	);
	$KOTA['ko_rota_teams']['consensus_description'] = array(
		'post' => "format_userinput('@VALUE@', 'text')",
		'form' => array(
			'type' => 'textarea',
			'params' => 'rows="5" cols="50"',
		),
	);
}



if(in_array('ko_donations', $KOTA_TABLES)) {
	$KOTA['ko_donations'] = array(
		'_access' => array(
			'module' => 'donations',
			'chk_col' => 'account',
			'level' => 3,
		),
		"_multititle" => array(
			"date" => "strftime('".$GLOBALS["DATETIME"]["dmY"]."', sql2timestamp('@VALUE@'))",
			"person" => "ko_get_person_name(@VALUE@)",
		),
		"_listview" => array(
			10 => array("name" => "date", "sort" => "date", 'filter' => TRUE),
			20 => array("name" => "valutadate", "sort" => "valutadate", 'filter' => TRUE),
			30 => array("name" => "account", "sort" => "account", 'filter' => TRUE),
			40 => array("name" => "amount", "sort" => "amount", 'filter' => TRUE),
			50 => array("name" => "person", "sort" => "person", 'filter' => TRUE),
			60 => array("name" => "source", "sort" => "source", 'filter' => TRUE),
			70 => array("name" => "comment", "sort" => "comment", 'filter' => TRUE),
		),
		'_inlineform' => array(
			'redraw' => array(
				'sort' => 'sort_donations',
				'fcn' => 'ko_list_donations(FALSE);'
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
				"params" => 'size="40" maxlength="100"',
				'prefill_new' => TRUE,
			),
			'filter' => array(
				'type' => 'textplus',
				'params' => 'size="0"',
			),
		),  //source
		"account" => array(
			"list" => 'db_get_column("ko_donations_accounts", @VALUE@, "number,name", " ")',
			"post" => 'uint',
			"form" => array_merge(array(
				"type" => "select",
				"params" => 'size="0"',
				'prefill_new' => TRUE,
			), kota_get_form("ko_donations", "account")),
		),  //account
		"amount" => array(
			"post" => 'float',
			"form" => array(
				"type" => "text",
				"params" => 'size="10" maxlength="40"'
			),
		),  //amount
		"person" => array(
			"list" => 'FCN:kota_listview_ko_donations_person',
			"post" => 'uint',
			"form" => array(
				"type" => "peoplesearch",
				'noinline' => TRUE,
			),
		),  //person
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
	);
}


if(in_array('ko_donations_accounts', $KOTA_TABLES)) {
	$KOTA['ko_donations_accounts'] = array(
		'_access' => array(
			'module' => 'donations',
			'chk_col' => 'id',
			'level' => 4,
			'condition' => array('delete' => "return db_get_count('ko_donations', '', 'AND `account` = \'id\'') == 0;"),
		),
		"_multititle" => array(
			"number" => "",
			"name" => "",
		),
		"_listview" => array(
			10 => array("name" => "number"),
			20 => array("name" => "name"),
			30 => array("name" => "comment"),
		),
		'_inlineform' => array(
			'redraw' => array(
				'cols' => 'number',
				'fcn' => 'ko_list_accounts(FALSE);'
			),
			'module' => 'donations',
		),
		"number" => array(
			'list' => 'ko_html;FCN:kota_listview_rootid',
			"form" => array(
				"type" => "text",
				"params" => 'size="60"',
			),
		),  //number
		"name" => array(
			"form" => array(
				"type" => "text",
				"params" => 'size="60"',
			),
		),  //name
		"comment" => array(
			"form" => array(
				"type" => "textarea",
				"params" => 'cols="40" rows="3"',
			),
		),  //comment
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
				'fcn' => $_SESSION['show'] == 'show_my_list' ? 'ko_list_personen(\'my_list\', FALSE);' : 'ko_list_personen(\'liste\', FALSE);',
			),
			'module' => 'leute',
		),
		"anrede" => array(
			"form" => array(
				"type" => "select",
				"params" => 'size="0"',
				"values" => array_merge(array(""), db_get_enums("ko_leute", "anrede")),
				"descs" => array_merge(array(""), db_get_enums("ko_leute", "anrede")),
			),
		),  //anrede
		'hidden' => array(
			'list' => 'FCN:kota_listview_boolx',
			'form' => array(
				'type' => 'checkbox',
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
			"post" => (in_array("nachname", $GLOBALS['COLS_LEUTE_UND_FAMILIE']) ? "FCN:ko_multiedit_familie" : ""),
			"form" => array(
				"descimg" => (in_array("nachname", $GLOBALS['COLS_LEUTE_UND_FAMILIE']) ? "icon_familie.png" : ""),
				"type" => "text",
				"params" => 'size="50" maxlength="50"',
			),
		),  //nachname
		"adresse" => array(
			"pre" => "ko_html",
			"post" => (in_array("adresse", $GLOBALS['COLS_LEUTE_UND_FAMILIE']) ? "FCN:ko_multiedit_familie" : ""),
			"form" => array(
				"descimg" => (in_array("adresse", $GLOBALS['COLS_LEUTE_UND_FAMILIE']) ? "icon_familie.png" : ""),
				"type" => "text",
				"params" => 'size="60" maxlength="100"',
			),
		),  //adresse
		"adresse_zusatz" => array(
			"pre" => "ko_html",
			"post" => (in_array("adresse_zusatz", $GLOBALS['COLS_LEUTE_UND_FAMILIE']) ? "FCN:ko_multiedit_familie" : ""),
			"form" => array(
				"descimg" => (in_array("adresse_zusatz", $GLOBALS['COLS_LEUTE_UND_FAMILIE']) ? "icon_familie.png" : ""),
				"type" => "text",
				"params" => 'size="60" maxlength="100"',
			),
		),  //adresse_zusatz
		"plz" => array(
			"pre" => "ko_html",
			"post" => (in_array("plz", $GLOBALS['COLS_LEUTE_UND_FAMILIE']) ? "FCN:ko_multiedit_familie" : ""),
			"form" => array(
				"descimg" => (in_array("plz", $GLOBALS['COLS_LEUTE_UND_FAMILIE']) ? "icon_familie.png" : ""),
				"type" => "text",
				"params" => 'size="11" maxlength="11"',
			),
		),  //plz
		"ort" => array(
			"pre" => "ko_html",
			"post" => (in_array("ort", $GLOBALS['COLS_LEUTE_UND_FAMILIE']) ? "FCN:ko_multiedit_familie" : ""),
			"form" => array(
				"descimg" => (in_array("ort", $GLOBALS['COLS_LEUTE_UND_FAMILIE']) ? "icon_familie.png" : ""),
				"type" => "text",
				"params" => 'size="50" maxlength="50"',
			),
		),  //ort
		"land" => array(
			"pre" => "ko_html",
			"post" => (in_array("land", $GLOBALS['COLS_LEUTE_UND_FAMILIE']) ? "FCN:ko_multiedit_familie" : ""),
			"form" => array_merge(array(
				"descimg" => (in_array("land", $GLOBALS['COLS_LEUTE_UND_FAMILIE']) ? "icon_familie.png" : ""),
				"type" => "textplus",
				"params" => 'size="0"',
				"params_PLUS" => 'size="50" maxlength="50"',
			), kota_get_form("ko_leute", "land")),
		),  //land
		"telp" => array(
			"pre" => "ko_html",
			"post" => (in_array("telp", $GLOBALS['COLS_LEUTE_UND_FAMILIE']) ? "FCN:ko_multiedit_familie" : ""),
			"form" => array(
				"descimg" => (in_array("telp", $GLOBALS['COLS_LEUTE_UND_FAMILIE']) ? "icon_familie.png" : ""),
				"type" => "text",
				"params" => 'size="30" maxlength="30"',
			),
		),  //telp
		"telg" => array(
			"pre" => "ko_html",
			"form" => array(
				"type" => "text",
				"params" => 'size="30" maxlength="30"',
			),
		),  //telg
		"natel" => array(
			"pre" => "ko_html",
			"form" => array(
				"type" => "text",
				"params" => 'size="30" maxlength="30"',
			),
		),  //natel
		"fax" => array(
			"pre" => "ko_html",
			"form" => array(
				"type" => "text",
				"params" => 'size="30" maxlength="30"',
			),
		),  //fax
		"email" => array(
			"pre" => "ko_html",
			"form" => array(
				"type" => "text",
				"params" => 'size="100" maxlength="100"',
			),
		),  //email
		"web" => array(
			"pre" => "ko_html",
			"form" => array(
				"type" => "text",
				"params" => 'size="50" maxlength="50"',
			),
		),  //web
		"geburtsdatum" => array(
			'list' => "sql2datum('@VALUE@')",
			"pre" => "sql2datum('@VALUE@')",
			"post" => "sql_datum('@VALUE@')",
			"form" => array(
				"type" => "text",
				"params" => 'size="11" maxlength="11"',
			),
		),  //geburtsdatum
		"zivilstand" => array(
			'list' => "FCN:kota_listview_ll",
			"pre" => "ko_html",
			"form" => array(
				"type" => "select",
				"params" => 'size="0"',
				"values" => db_get_enums("ko_leute", "zivilstand"),
				"descs" => db_get_enums_ll("ko_leute", "zivilstand"),
			),
		),  //zivilstand
		"geschlecht" => array(
			'list' => "FCN:kota_listview_ll",
			"pre" => "ko_html",
			"form" => array(
				"type" => "select",
				"params" => 'size="0"',
				"values" => db_get_enums("ko_leute", "geschlecht"),
				"descs" => db_get_enums_ll("ko_leute", "geschlecht"),
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
				"values" => db_get_enums("ko_leute", "famfunction"),
				"descs" => db_get_enums_ll("ko_leute", "famfunction"),
			),
		),  //famfunction
		"picture" => array(
			"form" => array(
				"type" => "file",
				"params" => '',
				'noinline' => TRUE,
			),
		),  //picture
		'rectype' => array(
			'list' => 'FCN:kota_listview_ll',
			'pre' => 'ko_html',
			'form' => array_merge(array(
				'type' => 'select',
				'params' => 'size="0"',
			), kota_get_form('ko_leute', 'rectype')),
		),
	);

	if(ko_module_installed('kg')) {
		$KOTA['ko_leute']['smallgroups'] = array(
			'list' => 'FCN:kota_listview_smallgroups',
			'form' => array_merge(array('type' => 'doubleselect',
																	'params' => 'size="7"',),
														kota_get_form('ko_leute', 'smallgroups')
														),
			);
	}

	if(ko_module_installed('groups')) {
		ko_get_access('groups');
		$z_where = "AND (`start` = '0000-00-00' OR `start` < NOW()) AND (`stop` = '0000-00-00' OR `stop` > NOW())";
		ko_get_groups($groups, $z_where);
		ko_get_grouproles($roles);
		foreach($groups as $group) {
			if(($access['groups']['ALL'] > 1 || $access['groups'][$group['id']] > 1) && $group['type'] != 1) {
				list($values, $descs, $all_descs) = ko_groups_get_group_id_names($group['id'], $groups, $roles);
				$KOTA['ko_leute']['MODULEgrp'.$group['id']] = array('form' => array('desc' => $group['name'],
																																						'type' => 'doubleselect',
																																						'params' => 'size="4"',
																																						'values' => $values,
																																						'descs' => $descs,
																																						'all_descs' => $all_descs),
																														'list' => 'FCN:kota_map_leute_daten',
				);
				//Datafields for multiedit
				foreach(explode(',', $group['datafields']) as $df) {
					if(!$df) continue;
					//Only dummy definition, will be definied properly as html element in ko_multiedit_formular()
					$KOTA['ko_leute']['MODULEgrp'.$group['id'].':'.$df] = array('form' => array('type' => 'html'));
				}
			}
		}
	}

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
		'_inlineform' => array(
			'redraw' => array(
				'sort' => 'sort_kg',
				'fcn' => 'ko_list_kg(FALSE);'
			),
			'module' => 'leute|kg',
		),
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
			//500-530: used for roles
		),
		'_listview_default' => array('name'),

		"type" => array(
			"form" => array(
				"type" => "textplus",
				"params" => 'size="30"',
				"select_case_sensitive" => TRUE,
			),
		),  //type
		"name" => array(
			'list' => 'FCN:kota_listview_ko_kleingruppen_name',
			"pre" => "ko_html",
			"form" => array(
				"type" => "text",
				"params" => 'size="30"',
			),
		),  //name
		"alter" => array(
			"pre" => "ko_html",
			"post" => 'int',
			"form" => array(
				"type" => "text",
				"params" => 'size="30"',
			),
		),  //alter
		"geschlecht" => array(
			"list" => "FCN:kota_listview_ll",
			"form" => array(
				"type" => "select",
				"params" => 'size="0"',
				"values" => db_get_enums("ko_kleingruppen", "geschlecht"),
				"descs" => db_get_enums_ll("ko_kleingruppen", "geschlecht"),
			),
		),  //geschlecht
		"wochentag" => array(
			"list" => "FCN:kota_listview_ll",
			"form" => array(
				"type" => "select",
				"params" => 'size="0"',
				"values" => db_get_enums("ko_kleingruppen", "wochentag"),
				"descs" => db_get_enums_ll("ko_kleingruppen", "wochentag"),
			),
		),  //wochentag
		"ort" => array(
			"pre" => "ko_html",
			"form" => array(
				"type" => "text",
				"params" => 'size="30"',
			),
		),  //ort
		"zeit" => array(
			"form" => array(
				"type" => "text",
				"params" => 'size="30"',
			),
		),  //zeit
		"treffen" => array(
			"list" => "FCN:kota_listview_ll",
			"form" => array(
				"type" => "select",
				"params" => 'size="0"',
				"values" => db_get_enums("ko_kleingruppen", "treffen"),
				"descs" => db_get_enums_ll("ko_kleingruppen", "treffen"),
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
				"params" => 'size="30"',
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
				"params" => 'size="30"',
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



if(in_array('ko_reservation', $KOTA_TABLES)) {
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
		'_listview' => array(
			10 => array('name' => 'item_id', 'sort' => 'item_id'),
			20 => array('name' => 'startdatum', 'sort' => 'startdatum', 'multiedit' => 'startdatum,enddatum'),
			30 => array('name' => 'startzeit', 'sort' => 'startzeit', 'multiedit' => 'startzeit,endzeit', 'filter' => TRUE),
			//40 is reserved for purpose (see below)
			//50 is reserved for name with email and telephone (see below)
			//60 is reserved for comments (see below)
		),
		'_listview_default' => array('item_id', 'startdatum', 'startzeit', 'zweck', 'name'),
		'_inlineform' => array(
			'redraw' => array(
				'sort' => 'sort_item',
				'fcn' => 'ko_list_reservations(FALSE);'
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
			), kota_get_form("ko_reservation", "item_id")),
		),  //item_id
		"startdatum" => array(
			"list" => 'FCN:kota_listview_date',
			"pre" => "sql2datum('@VALUE@')",
			"post" => "sql_datum('@VALUE@')",
			"form" => array(
				"type" => "jsdate",
			),
		),  //startdatum
		"enddatum" => array(
			'pre' => 'FCN:kota_pre_enddate',
			'post' => 'FCN:kota_post_enddate',
			"form" => array(
				"type" => "jsdate",
			),
		),  //enddatum
		"startzeit" => array(
			"list" => 'FCN:kota_listview_time',
			"pre" => "sql_zeit('@VALUE@')",
			"post" => "sql_zeit('@VALUE@')",
			"form" => array(
				"type" => "text",
				"params" => 'size="11" maxlength="11"',
			),
		),  //startzeit
		"endzeit" => array(
			"pre" => "sql_zeit('@VALUE@')",
			"post" => "sql_zeit('@VALUE@')",
			"list" => "",
			"form" => array(
				"type" => "text",
				"params" => 'size="11" maxlength="11"',
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
				"params" => 'size="60" maxlength="255"',
			),
		),  //email
		"telefon" => array(
			"pre" => 'ko_html',
			"post" => 'alphanum++',
			"form" => array(
				"type" => "text",
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
	);

	//Only add column with purpose if settings allow it
	$show_purpose = ($_SESSION['ses_userid'] != ko_get_guest_id() || ko_get_setting('res_show_purpose'));
	if($show_purpose) {
		$KOTA['ko_reservation']['_listview'][40] = array('name' => 'zweck', 'sort' => 'zweck', 'multiedit' => 'zweck,comments', 'filter' => TRUE);
	}
	//Only add column with info about owner of reservation if settings allow it
	$show_persondata = ($_SESSION['ses_userid'] != ko_get_guest_id() || ko_get_setting('res_show_persondata'));
	if($show_persondata) {
		$KOTA['ko_reservation']['_listview'][50] = array('name' => 'name', 'sort' => 'name', 'multiedit' => 'name', 'filter' => TRUE);
		$KOTA['ko_reservation']['_listview'][51] = array('name' => 'email', 'sort' => 'email', 'multiedit' => 'email', 'filter' => TRUE);
		$KOTA['ko_reservation']['_listview'][52] = array('name' => 'telefon', 'sort' => 'telefon', 'multiedit' => 'telefon', 'filter' => TRUE);
	}
	//Only add column comments if not guest
	$show_comments = $_SESSION['ses_userid'] != ko_get_guest_id();
	if($show_comments) {
		$KOTA['ko_reservation']['_listview'][60] = array('name' => 'comments', 'sort' => 'comments', 'multiedit' => 'comments', 'filter' => TRUE);
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
				'fcn' => 'ko_show_items_liste(FALSE);'
			),
			'module' => 'reservation',
		),
		"gruppen_id" => array(
			"post" => 'uint',
			'list' => 'db_get_column("ko_resgruppen", @VALUE@, "name")',
			"form" => array_merge(array(
				"type" => "textplus",
				"params_PLUS" => 'size="60" maxlength="100"',
			), kota_get_form("ko_resitem", "gruppen_id")),
		),  //gruppen_id
		"name" => array(
			'list' => 'FCN:kota_listview_ko_resitem_name;FCN:kota_listview_rootid',
			"pre" => "ko_html",
			"post" => 'js',
			"form" => array(
				"type" => "text",
				"params" => 'size="60" maxlength="100"',
			),
		),  //name
		"bild" => array(
			"form" => array(
				"type" => "file",
				"params" => 'size="60"',
			),
		),  //name
		"beschreibung" => array(
			'list' => 'str_replace("<br />", "\n", '."'@VALUE@'".')',
			"pre" => 'str_replace("<br />", "\n", '."'@VALUE@'".')',
			"post" => 'htmlentities(format_userinput("@VALUE@", "text"))',
			"form" => array(
				"type" => "textarea",
				"params" => 'cols="60" rows="5"',
			),
		),  //beschreibung
		"linked_items" => array(
			"post" => 'intlist',
			"form" => array(
				"type" => "dyndoubleselect",
				"js_func_add" => "resgroup_doubleselect_add_no_linked",
				"params" => 'size="7"',
			),
		),  //linked_items
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
			'post' => 'htmlentities(format_userinput("@VALUE@", "text"))',
			'form' => array(
				'type' => 'textarea',
				'params' => 'cols="60" rows="5"',
			),
		),
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
			10 => array('name' => 'name', 'sort' => 'name', 'multiedit' => 'name'),
			20 => array('name' => 'nump', 'sort' => FALSE, 'multiedit' => FALSE),
			30 => array('name' => 'numug', 'sort' => FALSE, 'multiedit' => FALSE),
			40 => array('name' => 'description', 'sort' => 'description', 'multiedit' => 'description'),
			50 => array('name' => 'roles', 'sort' => FALSE, 'multiedit' => 'roles'),
			60 => array('name' => 'start', 'sort' => 'stop', 'multiedit' => 'start,stop'),
			70 => array('name' => 'type', 'sort' => 'type', 'multiedit' => 'type'),
		),
		'_inlineform' => array(
			'redraw' => array(
				'sort' => 'sort_groups',
				'fcn' => 'ko_groups_list(FALSE);'
			),
			'module' => 'groups',
		),
		'name' => array(
			'list' => 'FCN:kota_listview_ko_groups_name',
			'form' => array(
				'type' => 'text',
				'params' => 'size="60" maxlength="200"'
			),
		),  //name
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
		),  //description
		'start' => array(
			'list' => 'FCN:kota_listview_datespan',
			'pre' => "sql2datum('@VALUE@')",
			'post' => "sql_datum('@VALUE@')",
			'form' => array(
				'type' => 'jsdate',
			),
		),  //start
		'stop' => array(
			'pre' => "sql2datum('@VALUE@')",
			'post' => "sql_datum('@VALUE@')",
			'form' => array(
				'type' => 'jsdate',
			),
		),  //stop
		'roles' => array(
			'list' => 'FCN:kota_listview_ko_groups_roles',
			'form' => array_merge(array(
				'type' => 'doubleselect',
				'params' => 'size="4"',
			), kota_get_form('ko_groups', 'roles')),
		),  //roles
		'type' => array(
			'list' => 'FCN:kota_listview_boolx',
			'form' => array(
				'type' => 'checkbox',
			),
		),  //type
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
				'params' => 'size="5" maxlength="200"'
			),
		),  //name
	);
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
		'_listview' => array(
			10 => array('name' => 'name', 'multiedit' => 'name'),
			20 => array('name' => 'used_in', 'multiedit' => FALSE),
		),
		'_inlineform' => array(
			'redraw' => array(
				'cols' => 'name',
				'fcn' => 'ko_groups_list_roles(FALSE);'
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
				'fcn' => 'ko_groups_list_datafields(FALSE);'
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
				'type' => 'checkbox',
			),
		),
		'private' => array(
			'post' => 'uint',
			'list' => 'FCN:kota_listview_boolyesno',
			'form' => array(
				'type' => 'checkbox',
			),
		),
		'reusable' => array(
			'post' => 'uint',
			'list' => 'FCN:kota_listview_boolyesno',
			'form' => array(
				'type' => 'checkbox',
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




if(in_array('ko_tapes', $KOTA_TABLES)) {
	$KOTA['ko_tapes'] = array(
		'_access' => array(
			'module' => 'tapes',
			'chk_col' => 'group_id',
		),
		"_multititle" => array(
			"date" => "sql2datum('@VALUE@')",
			"title" => "",
		),
		"group_id" => array(
			"post" => "uint",
			"form" => array_merge(array(
				"type" => "select",
				"params" => 'size="0"'
			), kota_get_form("ko_tapes", "group_id")),
		),  //group_id
		"date" => array(
			"pre" => "sql2datum('@VALUE@')",
			"post" => "sql_datum('@VALUE@')",
			"form" => array(
				"type" => "jsdate",
			),
		),  //date
		"serie_id" => array(
			"post" => "FCN:ko_multiedit_tapeserie",
			"form" => array_merge(array(
				"type" => "textplus",
				"params" => 'size="0"',
				"params_PLUS" => 'size="60" maxlength="255"',
			), kota_get_form("ko_tapes", "serie_id")),
		),  //serie_id
		"title" => array(
			"pre" => "ko_html",
			"form" => array(
				"type" => "text",
				"params" => 'size="60" maxlength="255"',
			),
		),  //title
		"subtitle" => array(
			"pre" => "ko_html",
			"form" => array(
				"type" => "text",
				"params" => 'size="60" maxlength="255"',
			),
		),  //subtitle
		"preacher" => array(
			"pre" => "ko_html",
			"form" => array_merge(array(
				"type" => "textplus",
				"params" => 'size="0"',
				"params_PLUS" => 'size="60" maxlength="255"',
			), db_select_distinct("ko_tapes", "preacher", "", "", TRUE)),
		),  //preacher
		"item_number" => array(
			"pre" => "ko_html",
			"form" => array(
				"type" => "text",
				"params" => 'size="60" maxlength="200"',
			),
		),  //item_number
		"price" => array(
			"form" => array(
				"type" => "text",
				"params" => 'size="10" maxlength="20"',
			),
		),  //item_number
	);
}



	/* Tape groups */

if(in_array('ko_tapes_groups', $KOTA_TABLES)) {
	$KOTA['ko_tapes_groups'] = array(
		'_access' => array(
			'module' => 'tapes',
			'chk_col' => 'id',
		),
		"_multititle" => array(
			"name" => "",
		),
		"name" => array(
			"pre" => "ko_html",
			"form" => array(
				"type" => "text",
				"params" => 'size="60" maxlength="255"',
			),
		),  //name
		"printname" => array(
			"pre" => "ko_html",
			"form" => array(
				"type" => "text",
				"params" => 'size="60" maxlength="255"',
			),
		),  //printname
	);
}



if(in_array('ko_tapes_series', $KOTA_TABLES)) {
	$KOTA['ko_tapes_series'] = array(
		"_multititle" => array(
			"name" => "",
		),
		"name" => array(
			"pre" => "ko_html",
			"form" => array(
				"type" => "text",
				"params" => 'size="60" maxlength="255"',
			),
		),  //name
		"printname" => array(
			"pre" => "ko_html",
			"form" => array(
				"type" => "text",
				"params" => 'size="60" maxlength="255"',
			),
		),  //printname
	);
}



if(in_array('ko_pdf_layout', $KOTA_TABLES)) {
	$KOTA['ko_pdf_layout'] = array(
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
		'_listview' => array(
			10 => array('name' => 'name', 'sort' => 'name', 'multiedit' => 'name', 'filter' => TRUE),
			20 => array('name' => 'group_id', 'sort' => 'group_id', 'multiedit' => 'group_id', 'filter' => TRUE),
			30 => array('name' => 'mode', 'sort' => 'mode', 'multiedit' => 'mode', 'filter' => TRUE),
			40 => array('name' => 'filter', 'sort' => FALSE, 'multiedit' => 'filter'),
			50 => array('name' => 'hidden', 'sort' => 'hidden', 'multiedit' => 'hidden', 'filter' => TRUE),
		),
		'_inlineform' => array(
			'redraw' => array(
				'sort' => 'sort_trackings',
				'fcn' => 'ko_list_trackings(FALSE);',
				'cols' => array('hidden'),
			),
			'module' => 'tracking',
		),
		'group_id' => array(
			"post" => "FCN:ko_multiedit_tracking_group",
			'list' => 'db_get_column("ko_tracking_groups", @VALUE@, "name")',
			'form' => array_merge(array(
				'type' => 'textplus',
				'params_PLUS' => 'size="60" maxlength="200"',
			), kota_get_form('ko_tracking', 'group_id')),
		),  //group_id
		'name' => array(
			'pre' => 'ko_html',
			'list' => 'ko_html;FCN:kota_listview_rootid',
			'form' => array(
				'type' => 'text',
				'params' => 'size="60" maxlength="200"',
			),
		),
		'hidden' => array(
			'list' => 'FCN:kota_listview_boolx',
			'form' => array(
				'type' => 'checkbox',
			),
		),
		'mode' => array(
			"list" => "FCN:kota_listview_ll",
			'form' => array_merge(array(
				'type' => 'select',
			), kota_get_form('ko_tracking', 'mode')),
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
				'params' => 'size="7"',
				"js_func_add" => "tracking_ds_filter",
			), kota_get_form('ko_tracking', 'filter')),
		),
		'types' => array(
			'form' => array(
				'type' => 'textarea',
				'params' => 'cols="50" rows="4"',
			),
		),
		'date_eventgroup' => array(
			'form' => array_merge(array(
				'type' => 'select',
			), kota_get_form('ko_tracking', 'date_eventgroup')),
		),
		'dates' => array(
			'post' => 'FCN:kota_sort_comma_list',
			'form' => array(
				'type' => 'multidateselect',
				'params' => 'size="7"',
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
				'type' => 'checkbox',
			),
		),
	);
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
				'fcn' => 'ko_list_tracking_mod_entries(FALSE);'
			),
			'module' => 'tracking',
		),
		'_listview' => array(
			10 => array('name' => 'crdate', 'sort' => 'crdate', 'multiedit' => FALSE),
			20 => array('name' => 'tid', 'sort' => 'tid', 'filter' => TRUE, 'multiedit' => FALSE),
			30 => array('name' => 'date', 'sort' => 'date', 'multiedit' => FALSE),
			40 => array('name' => 'lid', 'sort' => 'lid', 'multiedit' => FALSE),
			50 => array('name' => 'type', 'sort' => 'type', 'filter' => TRUE, 'multiedit' => FALSE),
			60 => array('name' => 'value', 'sort' => 'value', 'filter' => TRUE, 'multiedit' => FALSE),
			70 => array('name' => 'comment', 'sort' => 'comment', 'filter' => TRUE, 'multiedit' => FALSE),
			80 => array('name' => 'status', 'sort' => 'status', 'filter' => TRUE),
		),
		'_listview_default' => array('crdate', 'tid', 'lid', 'date', 'type', 'value', 'comment', 'status'),

		'tid' => array(
			'list' => 'db_get_column("ko_tracking", @VALUE@, "name")',
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
				'params' => 'size="7" style="width:150px;"',
				'noinline' => TRUE,
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



if(in_array('ko_news', $KOTA_TABLES)) {
	$KOTA['ko_news'] = array(
		'_multititle' => array(
			'title' => '',
		),
		'_listview' => array(
			10 => array('name' => 'title', 'sort' => 'title', 'multiedit' => 'title'),
			20 => array('name' => 'type', 'sort' => 'type', 'multiedit' => 'type'),
			30 => array('name' => 'cdate', 'sort' => 'cdate', 'multiedit' => 'cdate'),
		),
		'type' => array(
			'list' => 'FCN:kota_listview_ll',
			'form' => array(
				'type' => 'select',
				'values' => array('1', '2'),
				'descs' => array(getLL('kota_ko_news_type_1'), getLL('kota_ko_news_type_2')),
			),
		),
		'title' => array(
			'pre' => 'ko_html',
			'form' => array(
				'type' => 'text',
				'params' => 'size="60" maxlength="255"',
			),
		),
		'subtitle' => array(
			'pre' => 'ko_html',
			'form' => array(
				'type' => 'text',
				'params' => 'size="60" maxlength="255"',
			),
		),
		'text' => array(
			'pre' => 'ko_html',
			'form' => array(
				'type' => 'textarea',
				'params' => 'cols="50" rows="8"',
			),
		),
		'link' => array(
			'pre' => 'ko_html',
			'form' => array(
				'type' => 'text',
				'params' => 'size="60" maxlength="255"',
			),
		),
		'author' => array(
			'pre' => 'ko_html',
			'form' => array(
				'type' => 'text',
				'params' => 'size="60" maxlength="255"',
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
	);
}



if(in_array('_ko_sms_log', $KOTA_TABLES)) {
	$KOTA['_ko_sms_log'] = array(
		"_listview" => array(
			10 => array('name' => 'date'),
			20 => array('name' => 'user_id'),
			30 => array('name' => 'credits'),
			40 => array('name' => 'ratio'),
			50 => array('name' => 'numbers'),
			60 => array('name' => 'text'),
		),
		'date' => array(),
		'user_id' => array(),
		'credits' => array(),
		'ratio' => array(),
		'numbers' => array(),
		'text' => array(),
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
				'fcn' => 'ko_list_tasks(FALSE);'
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
				'type' => 'checkbox',
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




if(in_array('ko_log', $KOTA_TABLES)) {
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
				'fcn' => 'ko_show_logs(FALSE);'
			),
			'module' => 'admin',
		),
		'_listview' => array(
			10 => array('name' => 'date', 'sort' => 'date', 'multiedit' => FALSE, 'filter' => TRUE),
			20 => array('name' => 'type', 'sort' => 'type', 'multiedit' => FALSE, 'filter' => TRUE),
			30 => array('name' => 'user_id', 'sort' => 'user_id', 'multiedit' => FALSE, 'filter' => TRUE),
			40 => array('name' => 'comment', 'sort' => 'comment', 'multiedit' => FALSE, 'filter' => TRUE),
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
				'type' => 'text',
				'params' => 'size="20"',
			),
		),
		'comment' => array(
			'filter' => array(
				'type' => 'textarea',
			),
		),
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
				'fcn' => 'ko_list_admingroups(FALSE);'
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
					'use_fields' => array('title', 'action', 'deadline', 'subject', 'text', 'status', 'recipients_groups', 'recipients_leute', 'recipients_mails'),
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
			),
		),
		'recipients_groups' => array(
			'form' => array_merge(array(
				'type' => 'doubleselect',
				'params' => 'size="7"',
			), kota_get_form('ko_reminder', 'recipients_groups')),
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
				"params" => 'size="100"',
				'new_row' => true,
			),
		),

		"text" => array(
			"form" => array(
				"type" => "richtexteditor",
			),
		),


	);
}


//Add definition for ko_reservation_mod only used for multiedit of open reservations
if(in_array('ko_reservation', $KOTA_TABLES)) $KOTA["ko_reservation_mod"] = $KOTA["ko_reservation"];


//Only show kommentar2 for events to logged in users
if(in_array('ko_event', $KOTA_TABLES) && $_SESSION['ses_userid'] != ko_get_guest_id()) {
	$KOTA['ko_event']['_listview']['35'] = array('name' => 'kommentar2', 'sort' => 'kommentar2', 'filter' => TRUE);
}

//KOTA for ko_event_mod as a copy from ko_event
if(in_array('ko_event', $KOTA_TABLES)) {
	$KOTA["ko_event_mod"] = $KOTA["ko_event"];
	$KOTA["ko_event_mod"]["_listview"][70] = array("name" => "_user_id", "sort" => "_user_id");
	$KOTA["ko_event_mod"]["_user_id"] = array("list" => "FCN:kota_listview_login");
	unset($KOTA['ko_event_mod']['_access']['condition']);
}



//Include kota.inc from web directory (specific to each installation)
if(file_exists($BASE_PATH."config/kota.inc")) {
	include($BASE_PATH."config/kota.inc");
}

//Allow plugins to change KOTA
foreach($GLOBALS['PLUGINS'] as $plugin) {
	$file = $BASE_PATH."plugins/".$plugin["name"]."/kota.inc";
	if(file_exists($file)) include($file);
}




//Order listview arrays by index, so loops over them will be in the right order
foreach($KOTA as $table => $table_data) {
	if(!isset($table_data["_listview"])) continue;
	ksort($KOTA[$table]['_listview'], SORT_LOCALE_STRING);
}

?>
