<?php
/*
* UPDATE SCRIPT FOR kOOL RELEASE R46
*
* Description:
* Performs necessary update steps from R45 to R46.
* 
* Instructions:
* - Update the source to R45 first
* - Call install/kOOL_setup.sh from your web root
* - Update database by calling php install/update.phpsh first
* - Set the path to your kOOL installation below and run the script on
your kOOL server. *
*/


/* Settings */

// Set path to your kOOL installation (absolute or relative)
$kOOL_path = './';

/* End of settings */



// Include ko.inc (and with it database settings from ko-config.php)
$ko_path = $kOOL_path;
require_once($kOOL_path.'inc/ko.inc');


// define container for people for whom a leute_changes entry was created (to avoid double entries)
$leuteSnapshotSaved = array();
function saveLeuteSnapshot($ids) {
	global $leuteSnapshotSaved;

	if (!is_array($ids)) {
		$ids = array($ids);
	}
	foreach ($ids as $id) {
		if (in_array($id, $leuteSnapshotSaved)) continue;;

		ko_save_leute_changes($id, '', '', 99999);
		$leuteSnapshotSaved[] = $id;
	}
}



// transfer labels ('etiketten') from ko_etiketten to ko_labels
$labelVorlagen = db_select_distinct('ko_etiketten', 'vorlage');

foreach ($labelVorlagen as $labelVorlage) {
	$fields = db_select_data('ko_etiketten', "WHERE `vorlage` = '" . $labelVorlage . "'", '*', '', '', FALSE, TRUE);
	$newEntry = array();
	foreach ($fields as $field) {
		$newEntry[$field['key']] = $field['value'];
	}
	db_insert_data('ko_labels', $newEntry);
}



// transform front_modules userprefs
$users = db_select_distinct('ko_userprefs', 'user_id', '', "WHERE `key` in ('front_modules_left', 'front_modules_center', 'front_modules_right') ");

foreach ($users as $user) {
	$fmLeft = db_select_data('ko_userprefs', "WHERE `user_id` = " . $user . " and `key` = 'front_modules_left'", 'value', '', '', TRUE, TRUE);
	$fmCenter = db_select_data('ko_userprefs', "WHERE `user_id` = " . $user . " and `key` = 'front_modules_center'", 'value', '', '', TRUE, TRUE);
	$fmRight = db_select_data('ko_userprefs', "WHERE `user_id` = " . $user . " and `key` = 'front_modules_right'", 'value', '', '', TRUE, TRUE);

	$fmLeft = trim($fmLeft['value']);
	$fmCenter = trim($fmCenter['value']);
	$fmRight = trim($fmRight['value']);

	if ($fmLeft && $fmCenter) {
		$new = $fmLeft . ',' . $fmCenter;
	}
	else {
		$new = $fmLeft . $fmCenter;
	}
	if ($new && $fmRight) {
		$new .= ',' . $fmRight;
	}
	else {
		$new .= $fmRight;
	}

	ko_save_userpref($user, 'front_modules', $new);

}



// transform admin default view in settings and userprefs
db_update_data(
	'ko_settings',
	"WHERE `key` = 'default_view_admin' AND (`value` = 'set_layout' OR `value` = 'set_allgemein')",
	array('key' => 'default_view_admin', 'value' => 'admin_settings')
);

$users = db_select_distinct('ko_userprefs', 'user_id', '', "WHERE `key` = 'default_view_admin' ");

foreach ($users as $user) {
	$value = ko_get_userpref($user, 'default_view_admin');
	if ($value == 'set_layout' || $value == 'set_allgemein') ko_save_userpref($user, 'default_view_admin', 'admin_settings');
}



// create userprefs for front modules
$logins = db_select_data('ko_admin', "WHERE id <> " . ko_get_guest_id(), 'id');

foreach ($logins as $login) {
	$loginId = $login['id'];
	if (!$loginId) continue;
	$value = ko_get_userpref($loginId, 'front_modules');
	if (!$value) ko_save_userpref($loginId, 'front_modules', 'fastfilter,adressaenderung,daten_cal,mod,news,today,geburtstage');
}
$value = ko_get_userpref(ko_get_guest_id(), 'front_modules');
if (!$value) ko_save_userpref(ko_get_guest_id(), 'front_modules', 'adressaenderung');



// change style of leute filters in database
$filters = db_select_data('ko_filter', "WHERE 1=1");
foreach ($filters as $filter) {
	$new = array();
	for ($i = 1; $i <= 3; $i++) {
		$key = 'code' . $i;
		$val = $filter[$key];
		if ($val && !preg_match('/<input[^>]*class="[^"]*input-sm/', $val) && !preg_match('/<select[^>]*class="[^"]*input-sm/', $val) && !preg_match('/FCN/', $val)) {
			if (preg_match('/<input[^>]*class="/', $val) || preg_match('/<select[^>]*class="/', $val)) {
				$val = preg_replace('/<input([^>]*)class="([^"]*)"/', '<input$1class="$2 input-sm form-control"', $val);
				$val = preg_replace('/<select([^>]*)class="([^"]*)"/', '<select$1class="$2 input-sm form-control"', $val);
			} else {
				$val = preg_replace('/<input/', '<input class="input-sm form-control"', $val);
				$val = preg_replace('/<select/', '<select class="input-sm form-control"', $val);
			}
			$new[$key] = $val;
		}
	}
	if (sizeof($new) > 0) {
		db_update_data('ko_filter', "WHERE `id` = '" . $filter['id'] . "'", $new);
	}
}
$filters = db_select_data('ko_filter', "WHERE 1=1");
foreach ($filters as $filter) {
	$new = array();
	for ($i = 1; $i <= 3; $i++) {
		$key = 'code' . $i;
		$val = $filter[$key];
		if ($val && preg_match('/<input[^>]* size="[^"]*"/', $val)) {
			$val = preg_replace('/<input([^>]*) size="[^"]*"/', '<input$1', $val);
			$new[$key] = $val;
		}
	}
	if (sizeof($new) > 0) {
		db_update_data('ko_filter', "WHERE `id` = '" . $filter['id'] . "'", $new);
	}
}
db_update_data('ko_filter', "WHERE `group` = 'groups' AND `name` = 'group'", array('code1' => '<input type="hidden" name="var1"><div class="groupfilter" name="sel1-var1" size="6" data-select="single"></div>'));



// set collation of tables to 'latin1_german1_ci'
$sql = "select concat('alter table ',  table_name, ' convert to character set latin1 collate latin1_german1_ci;') as `sql_alter` from information_schema.tables where table_schema='".$mysql_db."' and table_collation != 'latin1_german1_ci' group by table_name";
$result = db_query($sql);
foreach ($result as $r) {
	db_query($r['sql_alter']);
}



// update families
$families = db_select_data('ko_familie', "WHERE 1=1", '*', '', '', FALSE, TRUE);
foreach ($families as $family) {
	$famId = $family['famid'];
	$members = db_select_data('ko_leute', "WHERE `famid` = '{$famId}'", '*');
	$husband = '';
	$wife = '';
	$children = array();
	foreach ($members as $member) {
		$function = $member['famfunction'];
		if ($function == 'husband') $husband = $member;
		else if ($function == 'wife') $wife = $member;
		else if ($function == 'child') $children[] = $member;
	}
	$updateParents = array();
	if ($wife) $updateParents['mother'] = $wife['id'];
	if ($husband) $updateParents['father'] = $husband['id'];
	$childrenIds = array();
	foreach ($children as $child) {
		$childrenIds[] = $child['id'];
	}
	if (sizeof($childrenIds) > 0 && sizeof($updateParents) > 0) {
		saveLeuteSnapshot($childrenIds);
		db_update_data('ko_leute', "WHERE `id` IN ('".implode("','", $childrenIds)."')", $updateParents);
	}

	if ($wife && $husband && $wife['zivilstand'] == 'married' && $husband['zivilstand'] == 'married') {
		saveLeuteSnapshot(array($wife['id'], $husband['id']));
		db_update_data('ko_leute', "WHERE `id` = '{$husband['id']}'", array('spouse' => $wife['id']));
		db_update_data('ko_leute', "WHERE `id` = '{$wife['id']}'", array('spouse' => $husband['id']));
	}
}


/******************************************** 15.11.2015 *********************************************/

// update kg cols_itemlist
db_update_data('ko_userprefs', "WHERE `type` = 'leute_kg_itemset'", array('type' => 'ko_kleingruppen_colitemset'));


/******************************************** 23.12.2015 *********************************************/

// update res fields that are shown to the guest user
$showPersondata = ko_get_setting('res_show_persondata');
$showPurpose = ko_get_setting('res_show_purpose');

$showFields = array();
if ($showPersondata) {
	$showFields[] = 'name';
	$showFields[] = 'email';
	$showFields[] = 'telefon';
}
if ($showPurpose) {
	$showFields[] = 'zweck';
}

ko_set_setting('res_show_fields_to_guest', implode(',', $showFields));


/******************************************** 10.02.2016 *********************************************/

// update MODULEfamid_husband and MODULEfamid_wife in userprefs and pdf layouts
function isSerialized($str) {
    return ($str == serialize(false) || @unserialize($str) !== false);
}

function replace_in_string($str) {
	return str_replace(array('MODULEfamid_husband', 'MODULEfamid_wife'), array('father', 'mother'), $str);
}

function replace_in_array($array) {
	array_walk_recursive($array, function(&$item, &$key) {
		$item = str_replace(array('MODULEfamid_husband', 'MODULEfamid_wife'), array('father', 'mother'), $item);
		$key = str_replace(array('MODULEfamid_husband', 'MODULEfamid_wife'), array('father', 'mother'), $key);
	});
	return $array;
}

//print ("\n\n                      USERPREFS                      \n");
$es = db_select_data('ko_userprefs', "WHERE `value` like '%MODULEfamid_husband%' OR `value` like '%MODULEfamid_wife%'");
foreach ($es as $e) {
	$old_value = $e['value'];
	if (isSerialized($e['value'])) {
		$array = unserialize($e['value']);
		$array = replace_in_array($array);
		$value = serialize($array);
	} else {
		$value = replace_in_string($e['value']);
	}
	//print($e['id'].":\n  {$old_value}\n  {$value}\n");
	db_update_data('ko_userprefs', "WHERE `id` = '{$e['id']}'", array('value' => $value));
}

//print ("\n\n                     PDF LAYOUTS                     \n");
$es = db_select_data('ko_pdf_layout', "WHERE `data` like '%MODULEfamid_husband%' OR `data` like '%MODULEfamid_wife%'");
foreach ($es as $e) {
	$old_value = $e['data'];
	$array = unserialize($e['data']);
	$array = replace_in_array($array);
	$value = serialize($array);

	//print($e['id'].":\n  {$old_value}\n  {$value}\n");
	db_update_data('ko_pdf_layout', "WHERE `id` = '{$e['id']}'", array('data' => $value));
}


/******************************************** 01.06.2016 *********************************************/

// Update filter presets that reference another filter preset
print ("Updating filter presets ...\n");
$filter = db_select_data('ko_filter', "WHERE `name` = 'filterpreset'", '*', '', '', TRUE);
$filterID = $filter['id'];
if(!$filterID) die('FilterID for filterpreset not found!');


$fps = db_select_data('ko_userprefs', "WHERE `type` = 'filterset'");
foreach($fps as $fp) {
	$doUpdate = FALSE;
	$value = unserialize($fp['value']);
	$new_value = $value;
	foreach($value as $k => $v) {
		if(!is_integer($k)) continue;
		if($v[0] == $filterID) {
			$presetString = $v[1][1];
			if(substr($presetString, 0, 3) == '@G@') {
				$userid = -1;
				$presetString = substr($presetString, 3);
			} else {
				$userid = $fp['user_id'];
			}
			$referencedPreset = db_select_data('ko_userprefs', "WHERE `user_id` = '$userid' AND `type` = 'filterset' AND `key` = '$presetString'", '*', '', '', TRUE);

			if($referencedPreset['id'] > 0) {
				$new_value[$k][1][1] = $referencedPreset['id'];
				$doUpdate = TRUE;
			} else {
				print "  fp not found ({$presetString})\n";
				//print_r($fp);
			}
		}
	}
	if($doUpdate) {
		db_update_data('ko_userprefs', "WHERE `id` = '".$fp['id']."'", array('value' => serialize($new_value)));
	}
}
print ("-> done\n");


