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


include_once($BASE_PATH."inc/class.kOOL_listview.php");



//Define basic chart types for smallgroup module (may be extended by plugins)
$KG_CHART_TYPES = array("members", "frequency", "weekday", "ages", "types", "regions", "gender");

function ko_list_kg($output=TRUE, $mode='html') {
	global $smarty, $ko_path;
	global $access, $MAILING_PARAMETER;


	//Check for tracking rights
	$show_tracking = FALSE;
	if(ko_module_installed('tracking', $_SESSION['ses_userid'])) {
		ko_get_access('tracking');
		$add_tracking = $access['tracking']['ALL'] > 3;
		$show_tracking = $access['tracking']['MAX'] > 0;
	}

	//Check for mailing rights
	$show_mailing = (ko_module_installed('mailing', $_SESSION['ses_userid']) && $MAILING_PARAMETER['domain'] != '');


	$z_where = $z_limit = "";
	apply_kg_filter($z_where, $z_limit);
	$rows = db_get_count('ko_kleingruppen', 'id', $z_where);
	ko_get_kleingruppen($es, $z_limit, '', 'WHERE 1=1'.$z_where);

	$list = new kOOL_listview();
	$list->init('leute', 'ko_kleingruppen', array('chk', 'edit', 'delete', 'tracking_show', 'tracking_add', 'mailing'), $_SESSION['show_kg_start'], $_SESSION['show_kg_limit']);
	$list->setTitle(getLL('kg_list_title'));
	$list->setAccessRights(array('edit' => 3, 'delete' => 4), $access['kg']);
	$list->setActions(array('edit' => array('action' => 'edit_kg'),
													'delete' => array('action' => 'delete_kg', 'confirm' => TRUE))
										);
	$list->setSort(TRUE, 'setsortkg', $_SESSION['sort_kg'], $_SESSION['sort_kg_order']);
	$list->setStats($rows, '', '', 'setstartkg');


	$manual_access = array();
	foreach($es as $k => $v) {
		//Manual access for tracking
		if($show_tracking) {
			//Find a tracking for this smallgroup
			$tracking = db_select_data('ko_tracking', 'WHERE `filter` = \''.$k.'\'', '*', '', 'LIMIT 0,1', TRUE);
			if(isset($tracking['id']) && ($access['tracking']['ALL'] > 0 || $access['tracking'][$tracking['id']] > 0)) {  //Found a tracking with access to it
				$manual_access['tracking_show'][$k] = TRUE;
				$manual_access['tracking_add'][$k] = FALSE;
				$list->setRowData(array('tracking_id' => $tracking['id']), $k);
			} else if($add_tracking) {  //No tracking so show add link if access rights are 4@ALL
				$manual_access['tracking_show'][$k] = FALSE;
				$manual_access['tracking_add'][$k] = TRUE;
			} else {  //else don't show anything
				$manual_access['tracking_show'][$k] = FALSE;
				$manual_access['tracking_add'][$k] = FALSE;
			}
		} else {
			$manual_access['tracking_show'][$k] = FALSE;
			$manual_access['tracking_add'][$k] = FALSE;
		}

		//Mailing: Show email links
		if($show_mailing) {
			if($v['mailing_alias'] != '') {
				$link = $v['mailing_alias'].'@'.$MAILING_PARAMETER['domain'];
				$manual_access['mailing'][$k] = TRUE;
			} else if(!ko_get_setting('mailing_only_alias')) {
				$link = 'sg'.$v['id'].'@'.$MAILING_PARAMETER['domain'];
				$manual_access['mailing'][$k] = TRUE;
			} else {
				$link = '';
				$manual_access['mailing'][$k] = FALSE;
			}
			$list->setRowData(array('mailing_link' => $link), $k);
		}
	}

	$list->setManualAccess('tracking_show', $manual_access['tracking_show']);
	$list->setManualAccess('tracking_add', $manual_access['tracking_add']);
	$list->setManualAccess('mailing', $manual_access['mailing']);


	//Footer
	$list_footer = $smarty->get_template_vars('list_footer');
	$list->setFooter($list_footer);


	$list->setWarning(kota_filter_get_warntext('ko_kleingruppen'));

	if($output) {
		$list->render($es, $mode, getLL('kg_xls_export_filename'));
		if($mode == 'xls') return $list->xls_file;
	} else {
		print $list->render($es);
	}
}//ko_list_kg()






function ko_kg_formular($mode, $id=0) {
	global $ko_path, $smarty;
	global $KOTA;
	global $access;

	if($mode == "edit" && $id) {
		if($access['kg']['MAX'] < 3) return;
	} else if($mode == "neu") {
		if($access['kg']['MAX'] < 4) return;
		$id = 0;
		//$kg = array();
	} else return;

	$form_data["title"] = $mode == "neu" ? getLL("kota_ko_kleingruppen_new") : getLL("kota_ko_kleingruppen_change");
  $form_data["submit_value"] = getLL("save");
  $form_data["action"] = $mode == "neu" ? "submit_neue_kg" : "submit_edit_kg";
	$form_data["cancel"] = "list_kg";

	ko_multiedit_formular("ko_kleingruppen", "", $id, "", $form_data);
}//ko_kg_formular()




function ko_kg_update_people() {
	$all_kgs = db_select_data('ko_kleingruppen', 'WHERE 1=1');
	$all_ids = array_keys($all_kgs);

	$people = db_select_data('ko_leute', "WHERE `smallgroups` != ''", 'id,smallgroups');
	foreach($people as $p) {
		$p_kgs = explode(',', $p['smallgroups']);
		$new_kgs = array();
		foreach($p_kgs as $kg) {
			if(in_array(substr($kg, 0, 4), $all_ids)) {
				$new_kgs[] = $kg;
			}
		}
		$new = implode(',', $new_kgs);
		if($new != $p['smallgroups']) {
			db_update_data('ko_leute', "WHERE `id` = '".$p['id']."'", array('smallgroups' => $new));
		}
	}
}//ko_kg_update_people()




/**
 * Shows smallgroup charts
 */
function ko_kg_chart($_type="") {
	global $KG_CHART_TYPES;
	
	//Call all chart functions
	$html = array();
	foreach($KG_CHART_TYPES as $type) {
		if(!function_exists("ko_kg_chart_".$type)) continue;
		$html[$type] = call_user_func("ko_kg_chart_".$type);
	}

	if($_type) {
		$out = '<label>'.getLL("kg_chart_title_".$type).'</label>'.$html[$type];
	} else {
		//Generate HTML output
		$out = '<div class="list_title">'.getLL("kg_chart_title").'</div><br clear="all" />';
		foreach($html as $type => $code) {
			$out .= '<div class="leute_chart" name="kg_chart_'.$type.'" id="kg_chart_'.$type.'"><label>'.getLL("kg_chart_title_".$type).'</label>'.$code.'</div>';
		}
		$out .= '<br clear="all" />';
	}

	return $out;
}//ko_kg_chart()



function ko_kg_chart_types() {
	global $ko_path;

	$value = $lavel = array();
	$query = "SELECT `type`, COUNT(`id`) AS num FROM `ko_kleingruppen` GROUP BY `type` ORDER BY `num` DESC";
	$result = mysql_query($query);
	while($row = mysql_fetch_assoc($result)) {
		$label[] = $row["type"] != "" ? $row["type"] : getLL("leute_chart_none");
		$value[] = $row["num"];
	}

	//Create img link for preview chart
	$r  = '<img border="0" src="'.$ko_path.'inc/graph_bar.php?data='.implode("*", $value).'&label='.urlencode(implode("*", $label));
	$r .= '&size=400x250&yValueMode=3&textXOrientation=vertical';
	$r .= '" />';

	//Create img link for popup to show bar chart a bit bigger
	$p  = $ko_path.'inc/graph_bar.php?data='.implode("*", $value).'&label='.urlencode(implode("*", $label));
	$p .= '&size=1000x550&yValueMode=3&textXOrientation=vertical';

	return '<a href="'.$p.'" target="_blank">'.$r.'</a>';
}



function ko_kg_chart_regions() {
	global $ko_path;

	$value = $lavel = array();
	$query = "SELECT `region`, COUNT(`id`) AS num FROM `ko_kleingruppen` GROUP BY `region` ORDER BY `num` DESC";
	$result = mysql_query($query);
	while($row = mysql_fetch_assoc($result)) {
		$label[] = $row["region"] != "" ? $row["region"] : getLL("leute_chart_none");
		$value[] = $row["num"];
	}

	//Create img link for preview chart
	$r  = '<img border="0" src="'.$ko_path.'inc/graph_bar.php?data='.implode("*", $value).'&label='.urlencode(implode("*", $label));
	$r .= '&size=400x250&yValueMode=3&textXOrientation=vertical';
	$r .= '" />';

	//Create img link for popup to show bar chart a bit bigger
	$p  = $ko_path.'inc/graph_bar.php?data='.implode("*", $value).'&label='.urlencode(implode("*", $label));
	$p .= '&size=1000x550&yValueMode=3&textXOrientation=vertical';

	return '<a href="'.$p.'" target="_blank">'.$r.'</a>';
}




function ko_kg_chart_members() {
	global $ko_path;

	$_label = $_value = array();
	$kgs = db_select_data('ko_kleingruppen', 'WHERE 1', '*', 'ORDER BY name ASC');
	foreach($kgs as $kgid => $kg) {
		$_value[] = db_get_count('ko_leute', 'id', "AND `smallgroups` REGEXP '$kgid:M' AND `deleted` = '0' ".ko_get_leute_hidden_sql());
		$_label[] = $kg["name"];
	}
	//Sort descending by num
	arsort($_value);
	$value = $label = array();
	foreach($_value as $vi => $v) {
		$value[] = $v;
		$label[] = ko_truncate($_label[$vi], 20);
		$label_short[] = ko_truncate($_label[$vi], 10);
	}

	//Create img link for preview chart
	$r  = '<img border="0" src="'.$ko_path.'inc/graph_bar.php?data='.implode("*", $value).'&label='.urlencode(implode("*", $label_short));
	$r .= '&size=400x250&yValueMode=0&textXOrientation=vertical';
	$r .= '" />';

	//Create img link for popup to show bar chart a bit bigger
	$width = sizeof($value) > 60 ? 1500 : 1000;
	$p  = $ko_path.'inc/graph_bar.php?data='.implode("*", $value).'&label='.urlencode(implode("*", $label));
	$p .= '&size='.$width.'x550&yValueMode=3&textXOrientation=vertical';

	return '<a href="'.$p.'" target="_blank">'.$r.'</a>';
}




function ko_kg_chart_gender() {
	return ko_leute_chart_generic_pie_enum("ko_kleingruppen", "", "geschlecht", "kota_ko_kleingruppen_geschlecht_");
}



function ko_kg_chart_weekday() {
	global $ko_path;

	$data = ko_leute_chart_generic_pie_enum("ko_kleingruppen", "", "wochentag", "kota_ko_kleingruppen_wochentag_", TRUE);
	$value = $data["value"];
	$label = $data["label"];

	//Create img link for preview chart
	$out  = '<img border="0" src="'.$ko_path.'inc/graph_bar.php?data='.implode("*", $value).'&label='.urlencode(implode("*", $label));
	$out .= '&size=400x250&yValueMode=3&textXOrientation=vertical';
	$out .= '" />';

	return $out;
}//ko_kg_chart_kg_weekday()



function ko_kg_chart_frequency() {
	global $ko_path;

	$data = ko_leute_chart_generic_pie_enum("ko_kleingruppen", "", "treffen", "kota_ko_kleingruppen_treffen_", TRUE);
	$value = $data["value"];
	$label = $data["label"];

	//Create img link for preview chart
	$out  = '<img border="0" src="'.$ko_path.'inc/graph_bar.php?data='.implode("*", $value).'&label='.urlencode(implode("*", $label));
	$out .= '&size=400x250&yValueMode=3&textXOrientation=vertical';
	$out .= '" />';

	return $out;
}//ko_kg_chart_kg_frequency()





/**
 * Number of smallgroups by the age span they cover
 */
function ko_kg_chart_ages() {
	global $ko_path;

	$kgs = db_select_data("ko_kleingruppen", "WHERE 1");
	$data = array();
	foreach($kgs as $kg) {
		list($min, $max) = explode("-", $kg["alter"]);
		$min = (int)$min; $max = (int)$max;
		if(!$min || !$max || $min > $max) continue;
		for($i = $min; $i <= $max; $i++) {
			$data[$i] += 1;
		}
	}

	ksort($data);
	$value = $label = array();
	foreach($data as $k => $v) {
		$value[] = $v;
		$label[] = $k;
	}

	//Create img link for preview chart
	$r  = '<img border="0" src="'.$ko_path.'inc/graph_bar.php?data='.implode("*", $value).'&label='.urlencode(implode("*", $label));
	$r .= '&size=400x250&yValueMode=0&xStep=10';
	$r .= '" />';

	//Create img link for popup to show bar chart a bit bigger
	$p  = $ko_path.'inc/graph_bar.php?data='.implode("*", $value).'&label='.urlencode(implode("*", $label));
	$p .= '&size=1000x450&yValueMode=3&xStep=5';

	$out = '<a href="'.$p.'" target="_blank">'.$r.'</a>';

	return $out;
}//ko_kg_chart_kg_ages()






function apply_kg_filter(&$z_where, &$z_limit) {
	global $access;

	//Limit
	$z_limit = 'LIMIT '.($_SESSION['show_kg_start']-1).', '.$_SESSION['show_kg_limit'];

	//Only show own small groups if access level is 1
	if($access['kg']['ALL'] < 2) {
		$kgids = kg_get_users_kgid();
		if(sizeof($kgids) == 0) return;
		$z_where .= ' AND `id` IN ('.implode(',', $kgids).') ';
	}

	//SQL where
	foreach($_SESSION['kg_filter'] as $field => $value) {
		if(!$value) continue;
		switch($field) {
			case 'name':
				$z_where .= ' AND `'.$field.'` REGEXP \'.*'.$value.'.*\' ';
			break;
			case 'geschlecht':
			case 'wochentag':
			case 'treffen':
			case 'type':
			case 'region':
				$z_where .= ' AND `'.$field.'` = \''.$value.'\' ';
			break;
		}
	}

	//Set filters from KOTA
	if(function_exists('kota_apply_filter')) {
		$kota_where = kota_apply_filter('ko_kleingruppen');
		if($kota_where != '') $z_where .= " AND ($kota_where) ";
	}
}//apply_kg_filter()
?>
