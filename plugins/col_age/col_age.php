<?php
function my_leute_add_column_col_age(&$r) {
	$las = ko_get_leute_admin_spalten($_SESSION['ses_userid'], 'all');
	if($las === FALSE || (is_array($las['view']) && in_array('geburtsdatum', $las['view']))) {
		$r['MODULEplugincol_age_age'] = getLL('my_col_age_age');
	}
}//my_leute_add_column()




function my_leute_column_map_col_age_age($data, $col, &$p) {
	$age = '';

	if($p['geburtsdatum'] == '0000-00-00') return $age;

	//Check for death field
	$df = ko_get_setting('my_col_age_deathfield');
	if($df && isset($p[$df]) && $p[$df] != '0000-00-00') {
		$todayY = substr($p[$df], 0, 4);
		$todayMD = str_replace('-', '', substr($p[$df], 5));
		$suffix = '&nbsp;&dagger;';
	} else {
		$todayY = date('Y');
		$todayMD = date('md');
		$suffix = '';
	}

	$age = (int)$todayY - (int)substr($p['geburtsdatum'], 0, 4);
	if((int)(substr($p['geburtsdatum'], 5, 2).substr($p['geburtsdatum'], 8, 2)) > (int)($todayMD)) $age--;

	return $age.$suffix;
}//my_leute_column_map()





function my_form_col_age_leute_settings(&$data, $mode, $id) {
	global $access;

	//Check access
	if(!isset($access['leute'])) ko_get_access('leute');
	if($access['leute']['MAX'] < 3) return;

	if(!isset($data[1])) {
		$data[1]['titel'] = getLL('settings_title_global');
	}

	$maxrow = sizeof($data[1]['row']);


	$leute_cols = db_get_columns('ko_leute');
	$col_names = ko_get_leute_col_name();
	$values = array('');
	$descs = array('');
	foreach($leute_cols as $col) {
		if(!isset($col_names[$col['Field']])) continue;
		$values[] = $col['Field'];
		$descs[] = $col_names[$col['Field']] ? $col_names[$col['Field']] : $col['Field'];
	}
	$data[1]['row'][$maxrow]['inputs'][0] = array('desc' => getLL('my_col_age_setting_deathfield'),
																								'type' => 'select',
																								'name' => 'col_age_deathfield',
																								'values' => $values,
																								'descs' => $descs,
																								'value' => ko_get_setting('my_col_age_deathfield'),
																								);
}




function my_action_handler_add_col_age_submit_leute_settings() {
	//Check access
	$all = ko_get_access_all('leute', $_SESSION['ses_userid'], $max);
	if($max < 3) return;

	ko_set_setting('my_col_age_deathfield', format_userinput($_POST['col_age_deathfield'], 'alphanum+'));
}
