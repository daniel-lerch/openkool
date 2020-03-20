<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2003-2018 Renzo Lauper (renzo@churchtool.org)
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




/**
 * Hook for ko_tools_misc()
 * Correct all reservations' linked_items: Overwrite with linked_items field from ko_resitem
 *
 * @return string HTML
 */
function ko_tools_cleanup_resitem_linked_items() {
	$items = db_select_data('ko_resitem', "WHERE 1", 'id,linked_items');
	if(sizeof($items) == 0) return 'no resitems';

	foreach($items as $item) {
		db_update_data('ko_reservation', "WHERE `item_id` = '".$item['id']."'", array('linked_items' => $item['linked_items']));
		db_update_data('ko_reservation_mod', "WHERE `item_id` = '".$item['id']."'", array('linked_items' => $item['linked_items']));
	}
}//resitem_linked_items()



/**
 * Hook for ko_tools_misc()
 * Find duplicate entries in ko_groups_datafields_data. Deletes all entries except for the newest one (with the highest ID)
 *
 * @return string HTML
 */
function ko_tools_cleanup_gdf_duplicates() {
	$duplicates = db_select_data('ko_groups_datafields_data', "WHERE 1", 'id,group_id,datafield_id,person_id,value, count(*) as numDup', 'GROUP BY group_id,datafield_id,person_id having numDup > 1');

	if(sizeof($duplicates) == 0) {
		return 'No duplicates found.';
	}

	foreach($duplicates as $dup) {
		$lastEntry = db_select_data('ko_groups_datafields_data', "WHERE `group_id` = '".$dup['group_id']."' AND `datafield_id` = '".$dup['datafield_id']."' AND `person_id` = '".$dup['person_id']."'", '*', "ORDER BY `id` DESC", "LIMIT 0,1", TRUE);

		db_delete_data('ko_groups_datafields_data', "WHERE `group_id` = '".$dup['group_id']."' AND `datafield_id` = '".$dup['datafield_id']."' AND `person_id` = '".$dup['person_id']."' AND `id` != '".$lastEntry['id']."'");
	}
}//gdf_duplicates()




/**
 * Hook for ko_tools_misc()
 * Update group_assignment_history startdate according to specified datafield for selected group
 *
 * @todo check if roles should also be validated
 * @return string HTML
 */
function ko_tools_cleanup_group_entry() {
    global $ko_path, $notifier, $smarty;

    if ($_POST['sel_groups']) {
        $submit_datafield_start = format_userinput($_POST['sel_datafield_start'], 'text');
        $submit_datafield_stop = format_userinput($_POST['sel_datafield_stop'], 'text');

        $update = function($value, $mode) {
            if (empty($value)) return;

            $submit_groups = format_userinput($_POST['sel_groups'], 'text');

            $groups = explode(",", $submit_groups);
            $where_group = "";
            foreach($groups AS $group) {
                list($g, $r) = explode(":",$group);
                $g = substr($g,1);
                $where_group.= " ( group_id = $g) OR";
            }

            $where_group = substr($where_group,0,-3);

            $where = "WHERE datafield_id = '" . $value . "' AND ($where_group) AND value != '' AND deleted = 0";
            $result = db_select_data('ko_groups_datafields_data', $where);

            foreach ($result as $entry) {
                $datum = sql_datetime($entry['value']);
                $data = [$mode => $datum];
                $where = "WHERE person_id = " . $entry['person_id'] . " AND group_id = " . $entry['group_id'];
                db_update_data("ko_groups_assignment_history", $where, $data);
            }
        };

        $update($submit_datafield_start, 'start');
        $update($submit_datafield_stop, 'stop');

        $notifier->addTextInfo('Updated start/stop in assignment history');
    }

    $localSmarty = clone($smarty);
    $sel_groups = array(
        'type' => 'groupsearch',
//        'include_roles' => true,
        'name' => 'sel_groups'
    );
    $localSmarty->assign('input', $sel_groups);
    $sel_code_groups = $localSmarty->fetch('ko_formular_elements.tmpl');


    $sel_code_datafields_start = '<select name="sel_datafield_start" class="input-sm form-control" size="0"><option value=""></option>';
    $sel_code_datafields_stop = '<select name="sel_datafield_stop" class="input-sm form-control" size="0"><option value=""></option>';

    $datafields = db_select_data('ko_groups_datafields','');
    $sel_code_datafields = '';
    foreach($datafields as $key => $value) {
        $sel_code_datafields .= '<option value="'.$key.'">'.$value['description'].'</option>';
    }

    $sel_code_datafields_start .= $sel_code_datafields . '</select>';
    $sel_code_datafields_stop .= $sel_code_datafields . '</select>';

    $c = '<form action="'.$ko_path.'tools/index.php" method="POST">
        <p>Zum Übertragen von Werten aus Gruppen-Datenfeldern bestimmter Gruppen in die beiden Felder "start" und "stop" der Tabelle ko_groups_assignment_history</p>
        <input type="hidden" name="call" value="ko_tools_cleanup_group_entry">
        <div class="row">
            <div class="col-md-4">
                Groups: ' . $sel_code_groups . '
            </div>
            <div class="col-md-3">
                Start: ' . $sel_code_datafields_start . '
            </div>
            <div class="col-md-3">
                Stop: ' . $sel_code_datafields_stop . '
            </div>
            <div class="col-md-2">
                <input type="submit" value="update" onclick="set_action(\'misc\', this)" />
            </div>
        </div>
        </form>';

    return $c;
}


/**
 * Hook for ko_tools_misc()
 * Set new value in selected userpref for all logins.
 * @example Update language code to 'de', so all user get german language
 *
 * @return string HTML
 */
function ko_tools_cleanup_update_userprefs() {
    global $ko_path, $notifier;

		if ($_POST['sel_userpref_key']) {
			$key = format_userinput($_POST['sel_userpref_key'], 'text');
			$value = format_userinput($_POST['txt_userpref_value'], 'text');
			ko_get_logins($logins);
			foreach($logins as $lid => $login) {
				ko_save_userpref($lid, $key, $value);
			}

			$notifier->addTextInfo('Userpref for all logins updated');
		}

		$c = '<form action="'.$ko_path.'tools/index.php" method="POST">	<div class="row"><div class="col-md-5">
			<input type="hidden" name="call" value="ko_tools_cleanup_update_userprefs">
			Key: <select name="sel_userpref_key" class="input-sm form-control" size="0"><option value=""></option>';

		$keys = db_select_distinct('ko_userprefs', '`key`', 'ORDER BY `key` ASC', 'WHERE `type` = \'\'');
		foreach($keys as $key) {
			$c .= '<option value="'.$key.'">'.$key.'</option>';
		}

    $c .= '</select></div><div class="col-md-5">
        Value: <input type="text" class="input-sm form-control col-md-6" name="txt_userpref_value" /></div><div class="col-md-2">
        <input type="submit" class="btn btn-default" value="Save" onclick="set_action(\'misc\', this)" /></div>
        </form>';

    return $c;
}




/**
 * Hook for ko_tools_misc()
 * Convert KOTA field from textarea to richtexteditor calling nl2br() on content.
 *
 * @return string HTML
 */
function ko_tools_cleanup_rte() {
	global $ko_path, $notifier;

	$sel_table = format_userinput($_POST['sel_table'], 'text');
	$sel_column = format_userinput($_POST['sel_column'], 'text');

	if ($sel_table != '' && $sel_column != '') {
		$entries = db_select_data($sel_table, "WHERE `$sel_column` != ''", 'id,'.$sel_column);
		foreach($entries as $entry) {
			$new = nl2br($entry[$sel_column]);
			db_update_data($sel_table, "WHERE `id` = '".$entry['id']."'", array($sel_column => $new));
		}
		$notifier->addTextInfo('Data in '.$sel_table.':'.$sel_column.' is now RTE.');
	}

	$c = '<form action="'.$ko_path.'tools/index.php" name="cleanup_action" method="POST">	<div class="row"><div class="col-md-5">
        <input type="hidden" name="call" value="ko_tools_cleanup_rte">
        Table: <select name="sel_table" class="input-sm form-control" size="0"><option value=""></option>';

	$tables = db_query("SELECT table_name FROM information_schema.tables where table_schema='".$GLOBALS['mysql_db']."';");

	foreach($tables as $table) {
		$c .= '<option value="'.$table['table_name'].'" '.($sel_table==$table['table_name']?"selected":"").'>' . $table['table_name'] . '</option>';
	}

	$c .= '</select></div>';

	if ($sel_table) {
		$c .= '<div class="col-md-5">
        	Column: <select name="sel_column" class="input-sm form-control" size="0"><option value=""></option>';

		$columns = db_query("SHOW COLUMNS FROM " . $sel_table);

		foreach($columns as $column) {
			$c .= '<option value="'.$column['Field'].'">' . $column['Field'] . '</option>';
		}

		$c .= '</select></div>';
	}

	$c .= '<div class="col-md-2">
		<input type="submit" class="btn btn-default" value="'.($sel_table?"Update":"select table").'" 
		onclick="set_action(\'misc\', this)" /></div></form>

		<script>
			$(document).ready(function() {
				$(\'select[name="sel_table"]\').on("change", function() {
					$(\'select[name="sel_column"]\').val(\'\');
					$(\'form[name="cleanup_action"] input[type="submit"]\').click();
				});
			});
		</script>';

	return $c;
}


/**
 * Hook for ko_tools_misc()
 * Perform trim() for specified column in database
 *
 * @return string HTML
 */
function ko_tools_cleanup_trim_dbfield() {
	global $ko_path, $notifier;

	$sel_table = format_userinput($_POST['sel_table'], 'text');
	$sel_column = format_userinput($_POST['sel_column'], 'text');

	if ($sel_table != '' && $sel_column != '') {
		db_query("update ".$sel_table." set ".$sel_column." = trim(".$sel_column.");");
		$notifier->addTextInfo('Data in '.$sel_table.':'.$sel_column.' is now trimmed.');
	}

	$c = '<form action="'.$ko_path.'tools/index.php" name="cleanup_action" method="POST">	<div class="row"><div class="col-md-5">
        <input type="hidden" name="call" value="ko_tools_cleanup_trim_dbfield">
        Table: <select name="sel_table" class="input-sm form-control" size="0"><option value=""></option>';

	$tables = db_query("SELECT table_name FROM information_schema.tables where table_schema='".$GLOBALS['mysql_db']."';");

	foreach($tables as $table) {
		$c .= '<option value="'.$table['table_name'].'" '.($sel_table==$table['table_name']?"selected":"").'>' . $table['table_name'] . '</option>';
	}

	$c .= '</select></div>';

	if ($sel_table) {
		$c .= '<div class="col-md-5">
        	Column: <select name="sel_column" class="input-sm form-control" size="0"><option value=""></option>';

		$columns = db_query("SHOW COLUMNS FROM " . $sel_table);

		foreach($columns as $column) {
			$c .= '<option value="'.$column['Field'].'">' . $column['Field'] . '</option>';
		}

		$c .= '</select></div>';
	}

	$c .= '<div class="col-md-2">
		<input type="submit" class="btn btn-default" value="'.($sel_table?"Update":"select table").'" 
		onclick="set_action(\'misc\', this)" /></div></form>

		<script>
			$(document).ready(function() {
				$(\'select[name="sel_table"]\').on("change", function() {
					$(\'select[name="sel_column"]\').val(\'\');
					$(\'form[name="cleanup_action"] input[type="submit"]\').click();
				});
			});
		</script>';

	return $c;
}


/**
 * Hook for ko_tools_misc()
 * Rename field from ko_leute
 * @example Instead of using ledig_name field is now called ledigname
 *
 * @return string HTML
 */
function ko_tools_cleanup_update_ko_leute_field() {
	global $ko_path, $notifier;

	$excluded_ko_leute_fields = ["id", "famid", "lastchange", "deleted", "hidden", "crdate", "cruserid"];

	$oldField = strtolower(format_userinput($_POST['sel_ko_leute_field'],'alphanum+'));
	$newField = strtolower(format_userinput($_POST['txt_newfield'],'alphanum+'));

	if (!empty($oldField) && !empty($newField) && !in_array($oldField, $excluded_ko_leute_fields)) {
		update_ko_leute_field_in_ko_userprefs("leute_children_columns", NULL, $oldField, $newField);
		update_ko_leute_field_in_ko_userprefs(NULL, "leute_itemset", $oldField, $newField);
		update_ko_leute_field_in_ko_userprefs("show_leute_cols", NULL, $oldField, $newField);

		update_ko_leute_field_in_ko_setting("leute_no_delete_columns", $oldField, $newField);
		update_ko_leute_field_in_ko_setting("kota_ko_leute_mandatory_fields", $oldField, $newField);
		update_ko_leute_field_in_ko_setting("my_col_age_deathfield", $oldField, $newField);

		update_ko_leute_field_in_serialized_column("ko_pdf_layout", "data", $oldField, $newField);
		update_ko_leute_field_in_serialized_column("ko_admin", "leute_admin_spalten", $oldField, $newField);
		update_ko_leute_field_in_serialized_column("ko_admingroups", "leute_admin_spalten", $oldField, $newField);
		update_ko_leute_field_in_serialized_column("ko_leute_changes", "changes", $oldField, $newField);

		update_ko_leute_field_filter($oldField, $newField);

		update_ko_leute_field_search_in_var("RECTYPES", $oldField);
		update_ko_leute_field_search_in_var("LEUTE_EMAIL_FIELDS", $oldField);
		update_ko_leute_field_search_in_var("LEUTE_MOBILE_FIELDS", $oldField);
		update_ko_leute_field_search_in_var("COLS_LEUTE_UND_FAMILIE", $oldField);
		update_ko_leute_field_search_in_var("FAMILIE_EXCLUDE", $oldField);
		update_ko_leute_field_search_in_var("EXCLUDE_FROM_MOD", $oldField);
		update_ko_leute_field_search_in_var("VCARD_PROPERTIES", $oldField);

		$notifier->addTextInfo('Old field ' . $oldField . ' updated to ' . $newField);
	}

	$c = '<form action="'.$ko_path.'tools/index.php" method="POST">	<div class="row"><div class="col-md-5">
        <input type="hidden" name="call" value="ko_tools_cleanup_update_ko_leute_field">
        Key: <select name="sel_ko_leute_field" class="input-sm form-control" size="0"><option value=""></option>';

	$keys = db_select_data("ko_leute", "WHERE 1=1", "*", "", "LIMIT 1", TRUE);
	foreach($keys as $key => $value) {
		if (in_array($key, $excluded_ko_leute_fields)) continue;
		$c .= '<option value="'.$key.'">'.$key.'</option>';
	}

	$c .= '</select></div><div class="col-md-5">
        Value: <input type="text" class="input-sm form-control col-md-6" name="txt_newfield" /></div><div class="col-md-2">
        <input type="submit" class="btn btn-default" value="Save" onclick="set_action(\'misc\', this)" /></div>
        </form>';

	return $c;
}

/**
 * Walk recursive through serialized data in $column and replace old with new field
 *
 * @param $table
 * @param $column
 * @param $oldField
 * @param $newField
 */
function update_ko_leute_field_in_serialized_column($table, $column, $oldField, $newField) {
	$rows = db_select_data($table, "WHERE `$column` like '%$oldField%'");
	foreach ($rows as $row) {
		$array = unserialize($row[$column]);
		$new_array = update_ko_leute_field_recursive($array, $oldField, $newField);
		$value = serialize($new_array);
		db_update_data($table, "WHERE `id` = '{$row['id']}'", array($column => $value));
		$log = $table . ":" . $column . " => Old: " . $row[$column] . "\n\nNew: " . $value;
		ko_log('tools_field_update', $log);
	}
}

/**
 * Update fields dbcol, sql1, sql2, sql3 in ko_filter
 *
 * @param $oldField
 * @param $newField
 */
function update_ko_leute_field_filter($oldField, $newField) {
	$where = "WHERE typ = 'leute'";
	$filters = db_select_data("ko_filter", $where);
	foreach ($filters AS $filter) {
		$where = "WHERE id = " . $filter['id'];
		$data = [
			"dbcol" => str_replace($oldField, $newField, $filter['dbcol']),
			"sql1" => str_replace($oldField, $newField, $filter['sql1']),
			"sql2" => str_replace($oldField, $newField, $filter['sql2']),
			"sql3" => str_replace($oldField, $newField, $filter['sql3']),
		];

		db_update_data("ko_filter", $where, $data);
		$log = "ko_filter:" . $filter['id'] . " => Old: " . serialize($filter) . "\n\nNew: " . serialize($data);
		ko_log('tools_field_update', $log);
	}
}

/**
 * Helper function for update_ko_leute_field_in_serialized_column
 * @param $array
 * @param $oldField
 * @param $newField
 * @return array|bool
 */
function update_ko_leute_field_recursive($array, $oldField, $newField) {
	if (!is_array($array)) return FALSE;
	$helper = array();
	foreach ($array as $key => $value) {
		if($key === $oldField) {
			$key = $newField;
		}

		if (is_array($value)) {
			$helper[$key] = update_ko_leute_field_recursive($value, $oldField, $newField);
		} else {
			if($value == $oldField) {
				$value = $newField;
			}
			$helper[$key] = $value;
		}
	}
	return $helper;
}

/**
 * replace a field in a comma seperated field list setting
 *
 * @param $setting
 * @param $oldField
 * @param $newField
 */
function update_ko_leute_field_in_ko_setting($setting, $oldField, $newField) {
	$columns = explode(",", ko_get_setting($setting));
	$old_columns = $columns;
	for ($i = 0; $i < count($columns); $i++) {
		if ($columns[$i] == $oldField) {
			$columns[$i] = $newField;
		}
	}
	ko_set_setting($setting, implode(",", $columns));
	$log = "ko_setting:" . $setting . " => Old: " . implode(",", $old_columns) . "\n\nNew: " . implode(",", $columns);
	ko_log('tools_field_update', $log);
}

/**
 * replace a field in a comma seperated field list of ko_userprefs
 *
 * @param $key
 * @param $type
 * @param $oldField
 * @param $newField
 */
function update_ko_leute_field_in_ko_userprefs($key, $type, $oldField, $newField) {
	$where_key = ($key != NULL ? " AND `key` = '$key'" : "");
	$where_type = ($type != NULL ? " AND `type` = '$type'" : "");

	$userprefs = db_select_data('ko_userprefs', "WHERE `value` like '%$oldField%' $where_key $where_type");
	foreach ($userprefs as $userpref) {
		$values = explode(",", $userpref['value']);
		for($i=0; $i<count($values);$i++) {
			if($key == "leute_children_columns") {
				$values[$i] = ($values[$i] == "_".$oldField ? "_".$newField : $values[$i]);
			} else {
				$values[$i] = ($values[$i] == $oldField ? $newField : $values[$i]);
			}
		}

		$where = "WHERE id = " . $userpref['id'];
		$data = ["value" => implode(",", $values)];
		db_update_data('ko_userprefs',  $where, $data);

		$log = "ko_userprefs:" . $userpref['id'] . " => Old: " . $userpref['value'] . "\n\nNew: " . implode(",", $values);
		ko_log('tools_field_update', $log);
	}
}

/**
 * Search for $oldField in a global variable $varname
 *
 * @param $varname string just the name of our variable in $GLOBALS
 * @param $oldField string
 */
function update_ko_leute_field_search_in_var($varname, $oldField) {
	array_walk_recursive($GLOBALS[$varname], function($item, $key) use ($oldField, $varname) {
		global $notifier;
		if ($key === $oldField || $item === $oldField) {
			$notifier->addTextWarning("Found \"".$oldField."\" in global variable \"" . $varname ."\"");
		}
	});
}
