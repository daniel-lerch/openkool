<?php

$UPDATES_CONFIG['amtswoche_to_amtstage'] = array(
	'name' => 'amtswoche_to_amtstage',
	'description' => "Convert a week in rota schedulling to 7 days",
	'crdate' => '2019-08-26',
	'version' => 'R48',
	'optional' => '0',
	'module' => 'rota',
);


/**
 * Main update function
 *
 * @return mixed: int 0 on success, error message as string otherwise
 */
function ko_update_amtswoche_to_amtstage() {
	// increase length of field because we now save YYYY-MM-DD instead of YYYY-WW
	mysqli_query(db_get_link(), "alter table ko_rota_schedulling modify event_id varchar(11) not null;");
	mysqli_query(db_get_link(), "alter table ko_rota_consensus modify event_id varchar(11) not null;");

	if(php_sapi_name() != "cli") {
		ob_start();
	}
	if(!ko_get_setting("absence_color")) {
		$data = ["key" => "absence_color", "value" => "#af4b33"];
		db_insert_data("ko_settings", $data);
	}

	echo "\nUpdating Weeks to Days\n======================\n";
	$where = "WHERE event_id LIKE '%-%'";
	$old_weeks = db_select_data("ko_rota_schedulling", $where);

	$rota_weekstart = ko_get_setting('rota_weekstart');
	if(empty($rota_weekstart)) $rota_weekstart = 0;

	foreach($old_weeks AS $old_week) {
		echo $old_week['event_id'] . "\n";

		$new_day = $old_week;
		list($year, $week_number, $should_be_empty) = explode("-", $old_week['event_id']);
		if(!empty($should_be_empty)) continue; // skip because this is already a day

		$schedules = explode(",", $new_day['schedule']);
		$new_schedules = [];
		foreach($schedules AS $schedule) {
			if(is_numeric($schedule)) {
				$new_schedules[] = $schedule;
			}
		}
		$new_day['schedule'] = implode(",", $new_schedules);

		for($day=1; $day<=7; $day++) {
			$new_day['event_id'] = date('Y-m-d', strtotime($rota_weekstart. " day", strtotime($year . "W" . $week_number . $day)));
			db_insert_data("ko_rota_schedulling", $new_day);
			echo $new_day['event_id'] . "\n";
		}
		echo "\n";

		$where = "WHERE event_id = '" . $old_week['event_id'] . "'";
		db_delete_data("ko_rota_schedulling", $where);
	}

	echo "\nConverting week teams to day teams and set a color\n";
	$where = "WHERE rotatype = 'week'";
	$old_teams = db_select_data("ko_rota_teams", $where);
	foreach($old_teams AS $old_team) {
		$where = "WHERE id = '" . $old_team['id'] . "'";

		$random_color = substr(md5(rand()), 0, 6);
		$data = [
			"rotatype" => "day",
			"days_range" => "1,2,3,4,5,6,7",
			"farbe" => $random_color,
			];
		db_update_data("ko_rota_teams", $where, $data);
	}
	echo "Rename Amtswoche to Amtstage\n";
	$where = "WHERE name = 'Amtswoche'";
	$data = ["name" => "Amtstage"];
	db_update_data("ko_rota_teams", $where, $data);

	echo "Remove dummy events created from schedulling\n";
	$calid = ko_get_setting('rota_export_calid');
	if(!empty($calid)) {
		$eventgroups = db_select_data('ko_eventgruppen', "WHERE `calendar_id` = '$calid'");
		if (!empty($eventgroups)) {
			db_delete_data('ko_event', "WHERE `eventgruppen_id` IN (" . implode(',', array_keys($eventgroups)) . ")");
			db_delete_data('ko_eventgruppen', "WHERE `id` IN (" . implode(',', array_keys($eventgroups)) . ")");
			db_delete_data('ko_event_calendar', "WHERE `id` = '$calid'");
			db_update_data('ko_rota_teams', 'WHERE 1', ['export_eg' => 0]);
		}

		ko_set_setting('rota_export_calid', '');
	}

	if(php_sapi_name() != "cli") {
		$log = ob_get_clean();
		ko_log("db_update", nl2br($log));
	}

	//All OK
	return 0;
}