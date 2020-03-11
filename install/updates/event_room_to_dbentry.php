<?php

/*
 * Update script to get all values from ko_event.room and ko_eventgruppen.room to save
 * in separate table ko_event_rooms, then set reference id.
 */


$UPDATES_CONFIG['event_rooms_to_dbentry'] = array(
	'name' => 'event_rooms_to_dbentry',
	'description' => "Events' rooms are own db entries in R48. This scripts converts all present rooms from ko_event and ko_eventgruppen to new entries in ko_event_rooms.",
	'crdate' => '2019-08-01',
	'version' => 'R48',
	'optional' => '0',
	'module' => 'daten',
);




/*
 * Main update function
 *
 * @return mixed: int 0 on success, error message as string otherwise
 */
function ko_update_event_rooms_to_dbentry() {
	$new_rooms = [];
	$tmp_rooms = db_select_data('ko_event_rooms');
	foreach($tmp_rooms AS $key => $tmp_room) {
		$new_rooms[$tmp_room['title']] = $key;
	}

	ko_update_event_rooms_to_db_entry_update_rooms('ko_event', $new_rooms);
	ko_update_event_rooms_to_db_entry_update_rooms('ko_eventgruppen', $new_rooms);

	//All OK
	return 0;
}//ko_update_event_rooms_to_dbentry()


/**
 * @param $table
 * @param $new_rooms
 */
function ko_update_event_rooms_to_db_entry_update_rooms($table, &$new_rooms) {
	$where = "WHERE room != '' AND room NOT REGEXP '^[0-9]+$'";
	$events = db_select_data($table, $where);
	foreach ($events AS $event) {
		$new_room_id = $new_rooms[$event['room']];
		if (!is_numeric($new_room_id)) {
			$data = ['title' => $event['room']];

			//coords from plugin daten_coords:
			if(isset($event['coords']) && $event['coords']) {
				$data['coordinates'] = $event['coords'];
			}

			//address from plugin kathbern:
			if((isset($event['street']) && $event['street']) || (isset($event['zipcity']) && $event['zipcity'])) {
				$data['address'] = trim($event['street'].' '.$event['zipcity']);
			}

			$new_room_id = db_insert_data('ko_event_rooms', $data);
			$new_rooms[$event['room']] = $new_room_id;
		}

		$where = 'WHERE id = ' . $event['id'];
		$data = ['room' => $new_room_id];
		db_update_data($table, $where, $data);
	}
}
