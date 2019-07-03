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

require_once($BASE_PATH.'inc/class.kOOL_listview.php');




/**
 * List currently available trackings
 */
function ko_list_trackings($output=TRUE) {
	global $smarty;
	global $access;

	if($access['tracking']['MAX'] < 1) return;
	apply_tracking_filter($z_where, $z_limit);

	$order = 'ORDER BY '.$_SESSION['sort_trackings'].' '.$_SESSION['sort_trackings_order'];

	$rows = db_get_count('ko_tracking', 'id', $z_where);
	if($_SESSION['show_start'] > $rows) {
		$_SESSION['show_start'] = 1;
		$z_limit = 'LIMIT '.($_SESSION['show_start']-1).', '.$_SESSION['show_limit'];
	}
	$es = db_select_data('ko_tracking', 'WHERE 1=1 '.$z_where, '*', $order, $z_limit);
	//Check for view rights, otherwise don't show tracking
	foreach($es as $tid => $t) {
		if($access['tracking']['ALL'] < 1 && $access['tracking'][$tid] < 1) {
			unset($es[$tid]);
			$rows--;
		}
	}

	$list = new kOOL_listview();

	$list->init('tracking', 'ko_tracking', array('chk', 'edit', 'delete'), $_SESSION['show_start'], $_SESSION['show_limit']);
	$list->setTitle(getLL('tracking_list_title'));
	$list->setAccessRights(array('edit' => 3, 'delete' => 4), $access['tracking']);
	$list->setActions(array('edit' => array('action' => 'edit_tracking'),
													'delete' => array('action' => 'delete_tracking', 'confirm' => TRUE))
										);
	$list->setSort(TRUE, 'setsort', $_SESSION['sort_trackings'], $_SESSION['sort_trackings_order']);
	$list->setStats($rows);
	$list->setColumnLink('name', 'index.php?action=enter_tracking&amp;id=ID');
	$list->setRowClass("ko_list_hidden", 'return HIDDEN == 1;');


	//Output the list
	if($output) {
		$list->render($es);
	} else {
		print $list->render($es);
	}
}//ko_list_trackings()




function ko_list_tracking_mod_entries($output=TRUE) {
	global $smarty;
	global $access;

	if($access['tracking']['MAX'] < 2) return;
	apply_tracking_entries_filter($z_where);

	$order = 'ORDER BY '.$_SESSION['sort_modtrackings'].' '.$_SESSION['sort_modtrackings_order'];

	$rows = db_get_count('ko_tracking_entries', 'id', $z_where." AND `status` = '1'");
	if($_SESSION['show_start'] > $rows) {
		$_SESSION['show_start'] = 1;
		$z_limit = 'LIMIT '.($_SESSION['show_start']-1).', '.$_SESSION['show_limit'];
	}
	$es = db_select_data('ko_tracking_entries', "WHERE `status` = '1' $z_where", '*', $order, $z_limit);

	$list = new kOOL_listview();

	$list->init('tracking', 'ko_tracking_entries', array('chk', 'check', 'delete'), $_SESSION['show_start'], $_SESSION['show_limit']);
	$list->setTitle(getLL('tracking_entries_list_title'));
	$list->setAccessRights(array('check' => 2, 'delete' => 2), $access['tracking']);
	$list->setActions(array('check' => array('action' => 'confirm_tracking_entry'),
													'delete' => array('action' => 'delete_tracking_entry', 'confirm' => TRUE))
										);
	$list->setSort(TRUE, 'setsortmod', $_SESSION['sort_modtrackings'], $_SESSION['sort_modtrackings_order']);
	$list->setStats($rows);

	$list->setWarning(kota_filter_get_warntext('ko_tracking_entries'));


	//Output the list
	if($output) {
		$list->render($es);
	} else {
		print $list->render($es);
	}
}//ko_list_tracking_mod_entries()





/**
 * Show form to enter and edit trackings. Uses fields as defined in KOTA
 */
function ko_formular_tracking($mode, $id='') {
	global $KOTA, $access;

	if($mode == 'new') {
		$id = 0;
	} else if($mode == 'edit') {
		if(!$id) return FALSE;
	} else {
		return FALSE;
	}

	$form_data['title'] =  $mode == 'new' ? getLL('tracking_form_title_new') : getLL('tracking_form_title_edit');
	$form_data['submit_value'] = getLL('save');
	$form_data['action'] = $mode == 'new' ? 'submit_new_tracking' : 'submit_edit_tracking';
	if($mode == 'edit' && ($access['tracking']['ALL'] > 3 || $access['tracking'][$id] > 3)) {
		$form_data['action_as_new'] = 'submit_as_new_tracking';
		$form_data['label_as_new'] = getLL('tracking_form_submit_as_new');
	}
	$form_data['cancel'] = 'list_trackings';

	ko_multiedit_formular('ko_tracking', '', $id, '', $form_data);
}//ko_formular_tracking()






/**
 * Show form to enter tracking
 */
function ko_tracking_enter_form($output=TRUE) {
	global $smarty, $access, $DATETIME;

	$tracking = db_select_data('ko_tracking', 'WHERE `id` = \''.$_SESSION['tracking_id'].'\'', '*', '', '', TRUE);

	if(!$_SESSION['date_start']) {
		//No date set yet, so calculate date_prev starting from today
		$dates = ko_tracking_get_dates($tracking, date('Y-m-d'), '', $date_prev, $date_next, $date_prev1);
		//And use date_prev as start, so most recent past dates show
		$_SESSION['date_start'] = $date_prev;
		$dates = ko_tracking_get_dates($tracking, $_SESSION['date_start'], '', $date_prev, $date_next, $date_prev1);
	} else {
		$dates = ko_tracking_get_dates($tracking, '', '', $date_prev, $date_next, $date_prev1);
	}
	$raw_dates = array();
	foreach($dates as $date) {
		$raw_dates[] = $date['date'];
	}

	$people = ko_tracking_get_people($tracking['filter'], $raw_dates, $tracking['id'], TRUE);

	//Additional columns to be shown when entering
	$show_cols = $show_cols_title = array();
	$preset = ko_get_userpref($_SESSION['ses_userid'], 'tracking_show_cols');
	if($preset) {
		$colnames = ko_get_leute_col_name(FALSE, TRUE);
		$userid = substr($preset, 0, 3) == '@G@' ? '-1' : $_SESSION['ses_userid'];
		$row = ko_get_userpref($userid, str_replace('@G@', '', $preset), 'leute_itemset');
		$columns = explode(',', $row[0]['value']);
		if(sizeof($columns) > 0) {
			list($testp) = $people;
			$_people = $people;
			$testp = array_shift($_people);
			unset($_people);
			foreach($columns as $col) {
				if(in_array($col, array('vorname', 'nachname'))) continue;
				if(!isset($testp[$col])) {
					foreach($people as $pid => $p) {
						$people[$pid][$col] = strip_tags(ko_unhtml(map_leute_daten($p[$col], $col, $p)));
					}
				}
				$show_cols[] = $col;
				$show_cols_title[$col] = $colnames[$col];
			}
		}
	}

	//Add type select
	if($tracking['mode'] == 'type' || $tracking['mode'] == 'typecheck') {
		$types = array();
		foreach(explode("\n", $tracking['types']) as $t) {
			if(!$t) continue;
			$t = trim($t);
			$types[] = array('value' => $t, 'desc' => $t);
		}
		$smarty->assign('types', $types);
	}
	//Add types for bitmask
	if(substr($tracking['mode'], 0, 8) == 'bitmask_') {
		$types = array();
		for($i = 6; $i < 32; $i++) {
			$desc = getLL('tracking_'.$tracking['mode'].'_'.pow(2, $i));
			if(!$desc) continue;
			$types[] = array('value' => pow(2, $i), 'desc' => $desc);
			$types_short[pow(2, $i)] = getLL('tracking_'.$tracking['mode'].'_short_'.pow(2, $i));
		}
		$smarty->assign('types', $types);
		$smarty->assign('types_short', $types_short);
		$smarty->assign('types_short', $types_short);
	}

	//Get all currently stored entries (only for people currently active for this tracking)
	$entries = array();
	$where = "WHERE `tid` = '".$tracking['id']."' AND `date` IN ('".implode("','", $raw_dates)."') AND `lid` IN ('".implode("','", array_keys($people))."')";
	if(!in_array($tracking['mode'], array('type', 'typecheck'))) $where .= " AND `type` = '' ";
	$rows = db_select_data('ko_tracking_entries', $where, '*', 'ORDER BY lid,date ASC');
	foreach($rows as $row) {
		$nv = 0;

		//Prepare comment for JS tooltip
		if($row['comment']) $row['comment'] = ko_js_save($row['comment']);

		if($tracking['mode'] == 'type' || $tracking['mode'] == 'typecheck') {
			$entries[$row['lid']][$row['date']][] = $row;
		} else if(substr($tracking['mode'], 0, 8) == 'bitmask_') {
			$vs = array();
			for($i=0; $i<32; $i++) {
				//Numeric value
				if($i < 6 && $row['value'] & pow(2, $i)) {
					$nv += pow(2, $i);
				} else {
					if($row['value'] & pow(2, $i)) $vs[pow(2, $i)] = getLL('tracking_'.$tracking['mode'].'_'.pow(2, $i));
				}
			}
			$entries[$row['lid']][$row['date']] = $vs;

			//Num value
			if($nv > 0) {
				$num_entries[$row['lid']][$row['date']] = $nv;
				$entries[$row['lid']][$row['date']][$nv] = $nv.' '.getLL('tracking_'.$tracking['mode'].'_1');
			}

			if($row['comment']) $comments[$row['lid']][$row['date']] = ko_js_save($row['comment']);
		} else {
			$entries[$row['lid']][$row['date']] = $row['value'];
		}

		if($tracking['mode'] == 'typecheck') {
			if($row['value'] == 1) $sums[$row['date']][$row['type']] += 1;
		} else {
			$sums[$row['date']][] = $row['value'];
		}
	}

	//Get default values
	$preset_values = array();
	$where = "WHERE `tid` = '".$tracking['id']."' AND `date` IN ('".implode("','", $raw_dates)."') AND `lid` = '-1'";
	if(!in_array($tracking['mode'], array('type', 'typecheck'))) $where .= " AND `type` = '' ";
	$rows = db_select_data('ko_tracking_entries', $where, '*', 'ORDER BY date ASC');
	foreach($rows as $row) {
		if($tracking['mode'] == 'type') {
			$preset_values[$row['date']][] = $row;
		} else if($tracking['mode'] == 'typecheck') {
			$preset_values[$row['date']][$row['type']] = $row['value'];
		} else if(substr($tracking['mode'], 0, 8) == 'bitmask_') {

		} else {
			$preset_values[$row['date']] = $row['value'];
		}
	}

	//Build sums for each person over the whole active month
	$sum_where = "WHERE `tid` = '".$tracking['id']."' AND YEAR(`date`) = '".substr($_SESSION['date_start'], 0, 4)."' AND MONTH(`date`) = '".substr($_SESSION['date_start'], 5, 2)."' AND `lid` IN ('".implode("','", array_keys($people))."')";
	if(!in_array($tracking['mode'], array('type', 'typecheck'))) $sum_where .= " AND `type` = '' ";
	$sum_rows = db_select_data('ko_tracking_entries', $sum_where);
	$psums = array();
	foreach($sum_rows as $row) {
		if($row['value'] == '') continue;
		switch($tracking['mode']) {
			case 'simple':
				if($row['value'] == 1) $psums[$row['lid']] += 1;
			break;
			case 'value':
				if(is_numeric($row['value'])) {
					$psums[$row['lid']]['numeric'] += (float)$row['value'];
				} else {
					$psums[$row['lid']][$row['value']] += 1;
				}
			break;
			case 'valueNonNum':  //Force non numeric treatment
				if($row['value'] != '') $psums[$row['lid']][$row['value']] += 1;
			break;
			case 'type':
				$psums[$row['lid']] += (float)$row['value'];
			break;
			case 'typecheck':
				$psums[$row['lid']][$row['type']] += $row['value'];
			break;
			default:
				if(substr($tracking['mode'], 0, 8) == 'bitmask_') {
					if(function_exists('my_'.$tracking['mode'].'_psum')) {
						$psums[$row['lid']] += call_user_func('my_'.$tracking['mode'].'_psum', $row);
					}
				}
		}
	}
	foreach($people as $id => $p) {
		if($tracking['mode'] == 'value' || $tracking['mode'] == 'valueNonNum') {
			$psum = '';
			if($psums[$id]['numeric'] != 0) $psum = $psums[$id]['numeric'];
			if(sizeof($psums[$id]) > 0) {
				foreach($psums[$id] as $k => $v) {
					if($k == 'numeric') continue;
					$psum .= ', '.$v.'x'.$k;
				}
			}
			if(substr($psum, 0, 2) == ', ') $psum = substr($psum, 2);
			$people[$id]['_sum'] = $psum;
		} else if($tracking['mode'] == 'typecheck') {
			$_sum = array();
			foreach($types as $t) {
				if($psums[$id][$t['value']]) $_sum[] = '<b>'.$psums[$id][$t['value']].'</b>x'.$t['value'];
			}
			if(sizeof($_sum) > 0) $people[$id]['_sum'] = implode('<br />', $_sum);
		} else {
			if($psums[$id] != 0) $people[$id]['_sum'] = $psums[$id];
		}
	}

	//Build sums for each date
	if($tracking['mode'] == 'simple' || $tracking['mode'] == 'valueNonNum') {
		foreach($sums as $date => $values) {
			$s = 0;
			foreach($values as $value) {
				if($value) $s++;
			}
			$sum[$date] = $s;
		}
	} elseif($tracking['mode'] == 'value' || $tracking['mode'] == 'type') {
		foreach($sums as $date => $values) {
			$s1 = $s2 = $s3 = 0;
			foreach($values as $value) {
				if(!$value) continue;
				if(is_numeric($value)) $s1 += $value;
				else if(!is_numeric($value)) $s2++;
				$s3++;
			}
			$sum[$date] = $s2 > 0 ? $s3 : $s1;
		}
	}
	else if(substr($tracking['mode'], 0, 8) == 'bitmask_') {
		//Call summation function for bitmasks
		if(function_exists('my_'.$tracking['mode'].'_sum')) {
			$sum = call_user_func('my_'.$tracking['mode'].'_sum', $sums);
		}
	}
	else if($tracking['mode'] == 'typecheck') {
		foreach($sums as $date => $values) {
			$_sum = array();
			foreach($types as $t) {
				if($values[$t['value']]) $_sum[] = $values[$t['value']].'x'.$t['value'];
			}
			if(sizeof($_sum) > 0) $sum[$date] = implode('<br />', $_sum);
		}
	}

	//Store person id of next person (used for tabindex calculation in smarty template)
	$count = 0;
	foreach($people as $id => $p) {
		$count++;
		if($count <= 1) {
			$prev = $id;
		} else {
			$people[$prev]['_next_id'] = $id;
			$prev = $id;
		}
	}

	//Create month/year select for navigation
	$today = date('Y-m-d');
	$dateselect['months'] = array();
	for($i=-24; $i<=12; $i++) {
		$date = add2date($today, 'month', $i, TRUE);
		$dateselect['months'][] = array('value' => substr($date, 0, 7).'-01', 'desc' => strftime($DATETIME['nY'], strtotime($date)));
	}
	$dateselect['selected'] = substr($_SESSION['date_start'], 0, 7).'-01';
	$dateselect['today'] = substr($today, 0, 7).'-01';

	$smarty->assign('sums', $sum);
	$smarty->assign('label_total', getLL('tracking_list_total'));
	$smarty->assign('total', sizeof($people));

	$smarty->assign('label_name', getLL('tracking_list_name'));
	$smarty->assign('people', $people);
	$smarty->assign('show_cols', $show_cols);
	$smarty->assign('show_cols_title', $show_cols_title);
	$smarty->assign('tracking', $tracking);
	$smarty->assign('dates', $dates);
	$smarty->assign('entries', $entries);
	$smarty->assign('preset_values', $preset_values);
	$smarty->assign('num_entries', $num_entries);
	$smarty->assign('comments', $comments);
	//Set links for date navigation
	$smarty->assign('next_date', $date_next);
	$smarty->assign('prev_date', $date_prev);
	$smarty->assign('next1_date', $raw_dates[1]);
	$smarty->assign('prev1_date', $date_prev1);
	$smarty->assign('today_date', date('Y-m-d'));
	$smarty->assign('dateselect', $dateselect);

	//Set form to readonly if appropriate rights are not given
	$smarty->assign('readonly', ($access['tracking']['ALL'] < 2 && $access['tracking'][$_SESSION['tracking_id']] < 2) || $tracking['hidden'] == 1);

	//Comments used for this tracking?  //TODO
	//$smarty->assign('show_comments', $tracking['show_comments'] == 1);
	$smarty->assign('show_comments', TRUE);

	//Help link
	$smarty->assign('help', ko_get_help('tracking', 'enter_tracking'));

	//Date limit
	$limit = ko_get_userpref($_SESSION['ses_userid'], 'tracking_date_limit');
	$smarty->assign('limitM', max(1, $limit-1));
	$smarty->assign('limitP', $limit+1);

	$smarty->assign('module', 'tracking');
	$smarty->assign('sesid', session_id());
	$smarty->assign('label_del_confirm', getLL('list_label_confirm_delete'));
	$smarty->assign('label_sum', strftime($DATETIME['nY'], strtotime($_SESSION['date_start'])));

	$smarty->assign('label_add_comment', getLL('tracking_label_add_comment'));
	$smarty->assign('label_del_comment', getLL('tracking_label_del_comment'));

	$smarty->assign('label_confirm_entry', getLL('tracking_label_confirm_entry'));

	$smarty->assign('label_for_all', getLL('tracking_enter_for_all'));
	$smarty->assign('label_for_all_del', getLL('tracking_enter_for_all_del'));

	$smarty->assign('label_preset', getLL('tracking_enter_preset'));
	$smarty->assign('label_set_default', getLL('tracking_set_default'));

	$smarty->assign('label_next', getLL('tracking_paging_next_page'));
	$smarty->assign('label_next1', getLL('tracking_paging_next_day'));
	$smarty->assign('label_prev', getLL('tracking_paging_prev_page'));
	$smarty->assign('label_prev1', getLL('tracking_paging_prev_day'));
	$smarty->assign('label_today', getLL('tracking_paging_today'));

	//Output the list
	if($output) {
		$smarty->display('ko_formular_tracking.tpl');
	} else {
		print $smarty->fetch('ko_formular_tracking.tpl');
	}
}//ko_tracking_enter_form()





/**
 * Does the date calculations for the different date methods
 *
 * @param array tracking: Tracking to get dates for
 * @param date start: If set this date will be used as first date, otherwise _SESSION[date_start] will be used
 * @param int limit: Set a maximum number of dates to return. If not set, _SESSION[date_limit] will be used
 * @param date &prev: Value for backwards date navigation
 * @param date &next: Value for forward date navigation
 * @param date &prev1: Value to navigation back one day
 * @param boolean apply_filter: Defines if filters from session should be applied or not, default to TRUE
 * @return array dates: Returns an array of dates fulfilling the given parameters
 */
function ko_tracking_get_dates($tracking, $_start='', $_limit='', &$prev, &$next, &$prev1, $apply_filter=TRUE) {
	global $DATETIME;

	$start = $_start ? $_start : $_SESSION['date_start'];
	if(!$start) $_SESSION['date_start'] = $start = date('Y-m-d');
	$limit = $_limit ? $_limit : $_SESSION['date_limit'];

	$dates = array();
	$_dates = $neg_dates = array();

	if($tracking['date_eventgroup']) {
		//TODO: More than one event on a date?
		apply_tracking_dates_filter($z_where, $z_limit, 'ko_event');
		foreach(explode(',', $tracking['date_eventgroup']) as $egid) {
			if($egid < 0) {
				$neg = TRUE;
				$egid = -1 * $egid;
			} else {
				$neg = FALSE;
			}
			$where = "WHERE `eventgruppen_id` = '$egid' ";
			$where .= $apply_filter ? $z_where : '';
			$eg_dates = db_select_data('ko_event', $where, '*', 'ORDER BY `startdatum` ASC');
			foreach($eg_dates as $d) {
				if($neg) {
					//Remove dates (for every day if multiday events)
					if($d['startdatum'] != $d['enddatum']) {
						$cd = $d['startdatum'];
						while($cd != $d['enddatum']) {
							$neg_dates[] = $cd;
							$cd = add2date($cd, 'day', 1, TRUE);
						}
						$neg_dates[] = $d['enddatum'];
					} else {
						$neg_dates[] = $d['startdatum'];
					}
				}
				else {
					if($d['startdatum'] != $d['enddatum']) {
						$cd = $d['startdatum'];
						while($cd != $d['enddatum']) {
							$_dates[] = $cd;
							$cd = add2date($cd, 'day', 1, TRUE);
						}
						$_dates[] = $d['enddatum'];
					} else {
						$_dates[] = $d['startdatum'];
					}
				}
			}
		}
	}

	if($tracking['date_weekdays'] != '') {
		//Get limit dates forward from start
		$date = $start != -1 ? $start : $_SESSION['date_start'];
		$num_dates = 0;
		//set max number of dates beginning with start
		if($apply_filter && $_SESSION['tracking_filter']['date1'] && $_SESSION['tracking_filter']['date2']) {
			//Get all dates in between date1 and date2 of filter  
			$days = (strtotime($_SESSION['tracking_filter']['date2']) - strtotime($_SESSION['tracking_filter']['date1']))/60/60/24;
			$max = ceil($days/7*sizeof(explode(',', $tracking['date_weekdays'])))+1;
		} else {
			//2*limit to show the current and calculate the link to the next page
			$max = 2*$limit;
		}
		while($num_dates < $max) {
			if(in_array(strftime('%w', strtotime($date)), explode(',', $tracking['date_weekdays']))) {
				$_dates[] = $date;
				$num_dates++;
			}
			$date = add2date($date, 'tag', 1, TRUE);
		}

		//Get limit dates backwards from start
		$date = $start != -1 ? $start : $_SESSION['date_start'];
		$num_dates = 0;
		while($num_dates < ceil($max/2)) {
			$date = add2date($date, 'tag', -1, TRUE);
			if(in_array(strftime('%w', strtotime($date)), explode(',', $tracking['date_weekdays']))) {
				$_dates[] = $date;
				$num_dates++;
			}
		}
	}

	if($tracking['dates'] != '') {
		$_dates = array_merge($_dates, explode(',', $tracking['dates']));
	}


	//Remove negative dates
	foreach($_dates as $k => $v) {
		if(in_array($v, $neg_dates)) unset($_dates[$k]);
	}


	//Sort dates
	sort($_dates);


	//Apply filter
	if($apply_filter && ($_SESSION['tracking_filter']['date1'] || $_SESSION['tracking_filter']['date2'])) {
		$fd1 = $_SESSION['tracking_filter']['date1'] ? strftime('%Y-%m-%d', strtotime($_SESSION['tracking_filter']['date1'])) : '1900-01-01';
		$fd2 = $_SESSION['tracking_filter']['date2'] ? strftime('%Y-%m-%d', strtotime($_SESSION['tracking_filter']['date2'])) : '2100-01-01';
		$__dates = array();
		foreach($_dates as $date) {
			if((int)str_replace('-', '', $date) >= (int)str_replace('-', '', $fd1)
			  && (int)str_replace('-', '', $date) <= (int)str_replace('-', '', $fd2)) {
				$__dates[] = $date;
			}
		}
		$_dates = $__dates;
	}


	if(sizeof($_dates) == 0) return array();

	$dateformat = ko_get_userpref($_SESSION['ses_userid'], 'tracking_dateformat');
	if(!$dateformat) $dateformat = 'dmy';

	$dates = array();
	$next = FALSE;
	asort($_dates);
	//Reset start to first date if filter is not be applied (e.g. for export mode all)
	if(!$apply_filter || $start == -1) $start = $_dates[0];

	$num_dates = 0;
	foreach($_dates as $date) {
		if(!$date) continue;
		if((int)str_replace('-', '', $date) >= (int)str_replace('-', '', $start)) {
			if($num_dates < $limit) {
				$dates[] = array('date' => $date, 'title' => strftime($DATETIME[$dateformat], strtotime($date)), 'timestamp' => strtotime($date));
				$num_dates++;
			} else if(!$next) {
				$next = $date;
			}
		}
	}
	//If no events found after start
	if(sizeof($dates) == 0) {
		arsort($_dates);
		$num_dates = 0;
		foreach($_dates as $date) {
			if(!$date) continue;
			if($num_dates < $limit && (int)str_replace('-', '', $date) < (int)str_replace('-', '', $start)) {
				array_unshift($dates, array('date' => $date, 'title' => strftime($DATETIME[$dateformat], strtotime($date))));
				$num_dates++;
			}
		}
		//Reset start for the following search for the link to the previous page
		$start = $dates[0]['date'];
	}

	//Find link to previous page
	arsort($_dates);
	$prev = $start;
	$num_dates = 0;
	$prev1 = '';
	foreach($_dates as $date) {
		if(!$date) continue;
		if($num_dates < $limit && (int)str_replace('-', '', $date) < (int)str_replace('-', '', $prev)) {
			if(!$prev1) $prev1 = $date;
			$prev = $date;
			$num_dates++;
		}
	}
	if($prev == $start) $prev = FALSE;

	return $dates;
}//ko_tracking_get_dates()





/**
 * Get members of a tracking
 *
 * @param mixed filter: A trackings filter (from DB). Can be group id, small group id or serialized array of filter preset
 * @return array people: Returns an array of addresses
 */
function ko_tracking_get_people($filters, &$dates, $tid, $apply_filters=FALSE) {
	$where = " AND `deleted` = '0'";
	$mode = '';

	$filter_where = '';
	foreach(explode(',', $filters) as $filter) {
		if(!$filter) continue;
		if($apply_filters && (isset($_SESSION['tracking_filter']['filter']) && $_SESSION['tracking_filter']['filter'] != 'all' && $_SESSION['tracking_filter']['filter'] != $filter)) continue;

		//Group ID
		if(strlen($filter) >= 7 && substr($filter, 0, 1) == 'g' && ereg('[g0-9:r,]*', $filter)) {
			$mode = 'group';
			list($gid, $rid) = explode(':', $filter);
			if(ko_get_setting('tracking_add_roles') == 1 && strlen($filter) > 7) {  //Role
				$filter_where .= " `groups` REGEXP '".$gid."[g:0-9]*:".$rid."' OR ";
			} else {  //No role, just group
				$filter_where .= " `groups` REGEXP '$gid' OR ";
			}
		}
		//Small group
		else if(strlen($filter) == 4) {
			$mode = 'kg';
			$filter_where .= " `smallgroups` REGEXP '$filter' OR ";
		}
		//base64 serialized filter preset array
		else if(substr($filter, 0, 1) == 'F') {
			$mode = 'filter';
			$fa = unserialize(base64_decode(substr($filter, 1)));
			if(is_array($fa)) {
				apply_leute_filter($fa, $temp_where, FALSE);

				//Remove leading AND
				$temp_where = trim($temp_where);
				$temp_where = preg_replace('/^AND/', '', $temp_where);

				$filter_where .= " ($temp_where) OR ";
			} else {
				$where = ' AND 1=2';
			}
		}
	}//foreach(filters as filter)

	if($filter_where != '') $where = $where.' AND ('.substr($filter_where, 0, -3).')';
	else return array();

	//Find addresses with assigned values for the given dates which are not currently assigned to the given filter and still show them
	if(ko_get_userpref($_SESSION['ses_userid'], 'tracking_show_inactive') == 1 && sizeof($dates) > 0 && !isset($_SESSION['tracking_filter']['filter'])) {
		$active = array_keys(db_select_data('ko_leute', 'WHERE 1 '.$where, 'id'));
		$ids = db_select_distinct('ko_tracking_entries', 'lid', '', "WHERE `tid` = '$tid' AND `date` IN ('".implode("','", $dates)."')");
		if(sizeof($ids) > 0) $where .= ' OR `id` IN ('.implode(',', $ids).') ';
	} else {
		$active = array();
	}

	switch(ko_get_userpref($_SESSION['ses_userid'], 'tracking_order_people')) {
		case 'role':
		case 'nachname':
			$orderby = '`nachname` ASC, `vorname` ASC';
		break;
		case 'vorname':
			$orderby = '`vorname` ASC, `nachname` ASC';
		break;
		default:
			$orderby = '`nachname` ASC, `vorname` ASC';
	}
	ko_get_leute($people, $where, '', '', 'ORDER BY '.$orderby);

	//Add flag to addresses not currently assigned to the given group/small group (will be marked in the template)
	if(sizeof($active) > 0) $inactive = array_diff(array_keys($people), $active);
	foreach($inactive as $id) {
		$people[$id]['_inactive'] = TRUE;
	}

	//Order group members by their role as set for the group
	if($mode == 'group' && ko_get_userpref($_SESSION['ses_userid'], 'tracking_order_people') == 'role') {
		$new = array();
		$group = db_select_data('ko_groups', 'WHERE `id` = \''.substr($gid, 1).'\'', '*', '', '', TRUE);
		//Use the order of the roles as they're set for the group
		foreach(explode(',', $group['roles']) as $rid) {
			foreach($people as $pid => $person) {
				$role = substr($person['groups'], strpos($person['groups'], $gid)+9, 6);
				if($rid == $role) {
					$new[$pid] = $person;
					unset($people[$pid]);
				}
			}
		}
		//Add all with no role
		foreach($people as $pid => $person) {
			$new[$pid] = $person;
		}
		$people = $new;
	}

	return $people;
}//ko_tracking_get_people()





function ko_tracking_settings() {
	global $smarty, $access, $DATETIME;

	if($access['tracking']['MAX'] < 1) return FALSE;

	//Build settings form
	$gc = 0;
	$rowcounter = 0;
	$frmgroup[$gc]['titel'] = getLL('settings_title_user');

	//Limits
	$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('admin_settings_limits_numberof_trackings'),
			'type' => 'text',
			'params' => 'size="10"',
			'name' => 'txt_limit_trackings',
			'value' => ko_html(ko_get_userpref($_SESSION['ses_userid'], 'show_limit_trackings'))
			);
	$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('admin_settings_limits_numberof_tracking_dates'),
			'type' => 'text',
			'params' => 'size="10"',
			'name' => 'txt_limit_tracking_dates',
			'value' => ko_html(ko_get_userpref($_SESSION['ses_userid'], 'tracking_date_limit'))
			);

	$value = ko_get_userpref($_SESSION['ses_userid'], 'tracking_order_people');
	$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('tracking_settings_order_people'),
			'type' => 'select',
			'name' => 'sel_order_people',
			'values' => array('role', 'vorname', 'nachname'),
			'descs' => array(getLL('tracking_settings_order_people_role'), getLL('tracking_settings_order_people_vorname'), getLL('tracking_settings_order_people_nachname')),
			'value' => $value,
	);
	$value = ko_get_userpref($_SESSION['ses_userid'], 'tracking_show_inactive');
	$frmgroup[$gc]['row'][$rowcounter++]['inputs'][1] = array('desc' => getLL('tracking_settings_show_inactive'),
			'type' => 'switch',
			'name' => 'sel_show_inactive',
			'label_0' => getLL('no'),
			'label_1' => getLL('yes'),
			'value' => $value == '' ? 0 : $value,
	);

	$value = ko_get_userpref($_SESSION['ses_userid'], 'tracking_dateformat');
	$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('tracking_settings_dateformat'),
			'type' => 'select',
			'name' => 'sel_dateformat',
			'values' => array('dmy', 'ddmy', 'dm', 'dM', 'DdmY'),
			'descs' => array(strftime($DATETIME['dmy'], time()), strftime($DATETIME['ddmy'], time()), strftime($DATETIME['dm'], time()), strftime($DATETIME['dM'], time()), strftime($DATETIME['DdmY'], time())),
			'value' => $value,
	);
	if(ko_module_installed('leute')) {
		$value = ko_get_userpref($_SESSION['ses_userid'], 'tracking_show_cols');
		$itemset = array_merge((array)ko_get_userpref('-1', '', 'leute_itemset', 'ORDER BY `key` ASC'), (array)ko_get_userpref($_SESSION['ses_userid'], '', 'leute_itemset', 'ORDER BY `key` ASC'));
		$values = $descs = array();
		$values[] = '';
		$descs[] = getLL('tracking_export_columns_name');
		foreach($itemset as $i) {
			$values[] = $i['user_id'] == '-1' ? '@G@'.$i['key'] : $i['key'];
			$descs[] = '&quot;'.($i['user_id'] == '-1' ? getLL('itemlist_global_short').' '.$i['key'] : $i['key']).'&quot;';
		}
		$frmgroup[$gc]['row'][$rowcounter]['inputs'][1] = array('desc' => getLL('tracking_settings_show_cols'),
				'type' => 'select',
				'name' => 'sel_show_cols',
				'values' => $values,
				'descs' => $descs,
				'value' => $value,
		);
	}
	$rowcounter++;



	//Global settings
	if($access['tracking']['MAX'] > 3) {
		$gc++;
		$rowcounter = 0;
		$frmgroup[$gc]['titel'] = getLL('settings_title_global');

		$value = ko_get_setting('tracking_add_roles');
		$frmgroup[$gc]['row'][$rowcounter]['inputs'][1] = array('desc' => getLL('tracking_settings_add_roles'),
				'type' => 'switch',
				'name' => 'sel_add_roles',
				'label_0' => getLL('no'),
				'label_1' => getLL('yes'),
				'value' => $value == '' ? 0 : $value,
		);
		$rowcounter++;
	}


	//display the form
	$smarty->assign('tpl_titel', getLL('tracking_settings_form_title'));
	$smarty->assign('tpl_submit_value', getLL('save'));
	$smarty->assign('tpl_action', 'submit_tracking_settings');
	$cancel = ko_get_userpref($_SESSION['ses_userid'], 'default_view_tracking');
	if(!$cancel) $cancel = 'list_trackings';
  $smarty->assign('tpl_cancel', $cancel);
	$smarty->assign('tpl_groups', $frmgroup);

	$smarty->assign('help', ko_get_help('tracking', 'tracking_settings'));

	$smarty->display('ko_formular.tpl');
}//ko_tracking_settings()






/**
 * Creates a tracking export as XLS or PDF
 *
 * @param string mode: can be xls or pdf
 * @param string filename: Filename to be used for the output file
 * @param int id: ID of tracking to be used for export
 * @param string address_columns: Default to 'name' which just exports first and last name. May also be a column preset
 * @param string dates: May be current, filter or all and defines which dates should be used for the export
 * @param boolean combine_families: If set to TRUE family members' tracking data will be combined into one entry
 * @return int error: If there is an error during the LaTeX conversion to PDF an error code will be returned
 */
function ko_tracking_export($mode, $filename, $id, $address_columns='name', $dates='current', $layout='L', $addrows=0, $combine_families=FALSE, $addsums=FALSE) {
	global $ko_path, $BASE_PATH, $BASE_URL, $DATETIME;

	//Get tracking, dates and people
	$tracking = db_select_data('ko_tracking', 'WHERE `id` = \''.$id.'\'', '*', '', '', TRUE);
	switch($dates) {
		case 'current':
			$dates = ko_tracking_get_dates($tracking, '', '', $prev, $next);
		break;
		case 'filter':
			if($_SESSION['tracking_filter']['date1'] && $_SESSION['tracking_filter']['date2']) {
				$dates = ko_tracking_get_dates($tracking, $_SESSION['tracking_filter']['date1'], 10000, $prev, $next);
			} else if($_SESSION['tracking_filter']['date1']) {
				$dates = ko_tracking_get_dates($tracking, $_SESSION['tracking_filter']['date1'], 250, $prev, $next);
			} else if($_SESSION['tracking_filter']['date2']) {
				$dates = ko_tracking_get_dates($tracking, -1, 250, $prev, $next);
			} else {
				$dates = ko_tracking_get_dates($tracking, '', '', $prev, $next);
			}
		break;
		case 'all':
			$dates = ko_tracking_get_dates($tracking, '', 250, $prev, $next, $prev1, FALSE);
		break;
		//Get current dates by default (continuos without a filter doesn't give any options to choose from)
		default:
			$dates = ko_tracking_get_dates($tracking, '', '', $prev, $next);
	}

	$raw_dates = array();
	foreach($dates as $date) {
		$raw_dates[] = $date['date'];
	}
	$people = ko_tracking_get_people($tracking['filter'], $raw_dates, $id, TRUE);

	//Get address columns from selected preset
	if($address_columns == 'name') {
		$columns = array('vorname', 'nachname');
	} else if(substr($address_columns, 0, 4) == 'set_') {
		$preset = substr($address_columns, 4);
		$userid = substr($preset, 0, 3) == '@G@' ? '-1' : $_SESSION['ses_userid'];
		$row = ko_get_userpref($userid, str_replace('@G@', '', $preset), 'leute_itemset');
		$columns = explode(',', $row[0]['value']);
		if(sizeof($columns) == 0) $columns = array('vorname', 'nachname');
	} else return FALSE;

	//Get tracking entries
	$entries = array();
	$where = "WHERE `tid` = '".$tracking['id']."' AND `lid` IN ('".implode("','", array_keys($people))."')";
	if($tracking['mode'] != 'type') $where .= " AND `type` = '' ";
	$rows = db_select_data('ko_tracking_entries', $where, '*', 'ORDER BY lid,date ASC');
	foreach($rows as $row) {
		if($tracking['mode'] == 'type') {
			$entries[$row['lid']][$row['date']][$row['type']] += $row['value'];
		} else {
			$entries[$row['lid']][$row['date']] = $row['value'];
		}
	}

	//Header
	$colnames = ko_get_leute_col_name(FALSE, TRUE);
	$famSumCols = explode("\n", $tracking['types']);
	foreach($famSumCols as $k => $v) {
		$famSumCols[$k] = trim($v);
	}

	//Combine output by family
	if($combine_families) {
		$done_famids = array();
		$sort = array();
		$data = array();
		$row = 0;

		//Group all entries by family
		$famdata = array();
		foreach($people as $pid => $p) {
			if(!$p['famid']) continue;
			foreach($dates as $date) {
				if($entries[$p['id']][$date['date']]) $famdata[$p['famid']][$date['date']][] = array('role' => $p['famfunction'], 'value' => $entries[$p['id']][$date['date']]);
			}
		}

		foreach($people as $pid => $p) {

			if(!$p['famid']) {
				foreach($columns as $col) {
					$data[$row][] = strip_tags(ko_unhtml(map_leute_daten($p[$col], $col, $p)));
				}
				$sums = array();
				$famSums = array();
				foreach($dates as $date) {
					if($tracking['mode'] == 'simple') {
						$value = $entries[$p['id']][$date['date']] > 0 ? 'X' : '';
						$sums['v'] += $entries[$p['id']][$date['date']];
					} else if($tracking['mode'] == 'value') {
						$value = $entries[$p['id']][$date['date']];
						if($value) {
							if(is_numeric($value)) $sums['v'] += $value;
							else $sums['string'][] = $value;
						}
					} else if($tracking['mode'] == 'valueNonNum') {
						$value = $entries[$p['id']][$date['date']];
						if($value) $sums['string'][] = $value;
					} else if($tracking['mode'] == 'type') {
						$value = '';
						foreach($entries[$p['id']][$date['date']] as $t => $v) {
							if(!$v || !$t) continue;
							$value .= $v.' '.$t."\n";
							(float)$famSums[$t] += (float)$v;
							(float)$sums['v'] += (float)$v;
						}
					}
					$data[$row][] = trim($value);
				}
				if($mode == 'xls') {
					if($tracking['mode'] == 'type') {
						foreach ($famSumCols as $fsc) {
							$data[$row][] = ($famSums[$fsc] == '' || !isset($famSums[$fsc])) ? 0 : $famSums[$fsc];
						}
					}
				}
				//Add sum column
				$value = array();
				if($sums['v']) $value[] = $sums['v'];
				if(sizeof($sums['string']) > 0) $value[] = implode(', ', $sums['string']);
				if ($addsums) {
					$data[$row][] = implode(', ', $value);
				}

				$row++;
				continue;
			}

			if(in_array($p['famid'], $done_famids)) continue;

			$fam = ko_get_familie($p['famid']);
			foreach($columns as $col) {
				switch($col) {
					case 'anrede': $p[$col] = $fam['famanrede'] ? $fam['famanrede'] : getLL('ko_leute_anrede_family'); break;
					case 'vorname':
						if(!in_array('anrede', $columns)) {
							$p[$col] = $fam['famanrede'] ? $fam['famanrede'] : getLL('ko_leute_anrede_family');
						} else {
							if($fam['famfirstname']) {
								$p[$col] = $fam['famfirstname'];
							} else {
								//use first names of parents for firstname-col
								$parents = db_select_data('ko_leute', "WHERE `famid` = '".$p['famid']."' AND `famfunction` IN ('husband', 'wife')", 'famfunction,vorname', 'ORDER BY famfunction ASC');
								$parent_values = array();
								foreach($parents as $parent) $parent_values[] = $parent['vorname'];
								$p[$col] = implode((' '.getLL('family_link').' '), $parent_values);
							}
						}
					break;
					case 'nachname': $p[$col] = $fam['famlastname'] ? $fam['famlastname'] : $p['nachname']; break;
				}//switch(col)
			}


			foreach($columns as $col) {
				$data[$row][] = strip_tags(ko_unhtml(map_leute_daten($p[$col], $col, $p)));
			}
			$sums = array();
			$famSums = array();
			foreach($dates as $date) {
				$values = array();

				//Set values and sums for each date for the given family
				$value = array();
				switch($tracking['mode']) {
					case 'simple':
						foreach($famdata[$p['famid']][$date['date']] as $d) {
							$key = in_array($d['role'], array('husband', 'wife')) ? 'p' : 'c';
							$values[$key] += $d['value'];
						}
						if($values['p'] > 0) $value[] = $values['p'].getLL('tracking_export_family_parents_short');
						if($values['c'] > 0) $value[] = $values['c'].getLL('tracking_export_family_children_short');
						$sums['p'] += $values['p'];
						$sums['c'] += $values['c'];
					break;
					case 'value':
						foreach($famdata[$p['famid']][$date['date']] as $d) {
							$key = in_array($d['role'], array('husband', 'wife')) ? 'p' : 'c';
							if(is_numeric($d['value'])) $values[$key]['numeric'] += $d['value'];
							else $values[$key][$d['value']] += 1;
						}
						//Handle numeric values first
						if($values['p']['numeric'] > 0) $value[] = $values['p']['numeric'].getLL('tracking_export_family_parents_short');
						if($values['c']['numeric'] > 0) $value[] = $values['c']['numeric'].getLL('tracking_export_family_children_short');
						$sums['numeric']['p'] += $values['p']['numeric'];
						$sums['numeric']['c'] += $values['c']['numeric'];
						//Handle non-numeric values
						if(sizeof($values['p']) > 0) {
							foreach($values['p'] as $k => $v) {
								if($k == 'numeric') continue;
								$value[] = $v.'x'.$k.getLL('tracking_export_family_parents_short');
								$sums[$k]['p'] += $v;
							}
						}
						if(sizeof($values['c']) > 0) {
							foreach($values['c'] as $k => $v) {
								if($k == 'numeric') continue;
								$value[] = $v.'x'.$k.getLL('tracking_export_family_children_short');
								$sums[$k]['c'] += $v;
							}
						}
					break;
					case 'valueNonNum':
						foreach($famdata[$p['famid']][$date['date']] as $d) {
							$key = in_array($d['role'], array('husband', 'wife')) ? 'p' : 'c';
							$values[$key][$d['value']] += 1;
						}
						//Handle non-numeric values
						if(sizeof($values['p']) > 0) {
							foreach($values['p'] as $k => $v) {
								if($k == 'numeric') continue;
								$value[] = $v.'x'.$k.getLL('tracking_export_family_parents_short');
								$sums[$k]['p'] += $v;
							}
						}
						if(sizeof($values['c']) > 0) {
							foreach($values['c'] as $k => $v) {
								if($k == 'numeric') continue;
								$value[] = $v.'x'.$k.getLL('tracking_export_family_children_short');
								$sums[$k]['c'] += $v;
							}
						}
					break;
					case 'type':
						foreach($famdata[$p['famid']][$date['date']] as $dd) {
							foreach($dd['value'] as $t => $d) {
								$values[$t] += $d;
							}
						}
						foreach($values as $t => $v) {
							$value[] = $v.' '.$t;
							(float)$famSums[$t] += (float)$v;
							$sums['p'] += $v;
						}
					break;

					//TODO: case bitmask
				}
				$data[$row][] = implode(', ', $value);
			}

			//Add personal sums by 'type'
			if($mode == 'xls') {
				if($tracking['mode'] == 'type') {
					foreach ($famSumCols as $fsc) {
						$data[$row][] = ($famSums[$fsc] == '' || !isset($famSums[$fsc])) ? 0 : $famSums[$fsc];
					}
				}
			}

			//Add sum column
			$sum = array();
			if($tracking['mode'] == 'value') {
				if($sums['numeric']['p']) $sum[] = $sums['numeric']['p'].getLL('tracking_export_family_parents_short');
				if($sums['numeric']['c']) $sum[] = $sums['numeric']['c'].getLL('tracking_export_family_children_short');
				if(sizeof($sums) > 0) {
					foreach($sums as $k => $d) {
						if($k == 'numeric') continue;
						foreach($d as $pc => $v) {
							$llpc = $pc == 'p' ? getLL('tracking_export_family_parents_short') : getLL('tracking_export_family_children_short');
							$sum[] = $v.'x'.$k.$llpc;
						}
					}
				}
			} else if($tracking['mode'] == 'type') {  //Don't include p/c for type sums
				if($sums['p']) $sum[] = $sums['p'];
			} else {
				if($sums['p']) $sum[] = $sums['p'].getLL('tracking_export_family_parents_short');
				if($sums['c']) $sum[] = $sums['c'].getLL('tracking_export_family_children_short');
			}
			if ($addsums) {
				$data[$row][] = implode(', ', $sum);
			}

			$done_famids[] = $p['famid'];
			$row++;
		}//foreach(people as p)
	}//if(combine_families)
	else {
		//Fill tracking data
		$data = array();
		$row = 0;
		foreach($people as $p) {
			foreach($columns as $col) {
				$v = strip_tags(ko_unhtml(map_leute_daten($p[$col], $col, $p)));
				//Mark inactive people
				if($p['_inactive'] && in_array($col, array('vorname', 'nachname'))) $v = '('.$v.')';
				$data[$row][] = $v;
			}
			$sums = array();
			$famSums = array();
			foreach($dates as $date) {
				switch($tracking['mode']) {
					case 'simple':
						$value = $entries[$p['id']][$date['date']] > 0 ? 'X' : '';
						if($value) $sums['numeric'] += 1;
					break;
					case 'value':
						$value = $entries[$p['id']][$date['date']];
						if($value) {
							if(is_numeric($value)) $sums['numeric'] += $value;
							else $sums[$value] += 1;
						}
					break;
					case 'valueNonNum':
						$value = $entries[$p['id']][$date['date']];
						if($value) $sums[$value] += 1;
					break;
					case 'type':
						$value = '';
						foreach($entries[$p['id']][$date['date']] as $t => $v) {
							if(!$v || !$t) continue;
							$value .= $v.' '.$t."\n";
							(float)$sums['numeric'] += (float)$v;
							(float)$famSums[$t] += (float)$v;
						}
					break;

					default:
						if(substr($tracking['mode'], 0, 8) == 'bitmask_') {
							if(function_exists('ko_tracking_'.$tracking['mode'].'_export')) {
								$value = call_user_func_array('ko_tracking_'.$tracking['mode'].'_export', array(&$sums, $p, $date, $tracking, $entries));
							}
						}
				}
				$data[$row][] = trim($value);
			}
			//Add personal sums by 'type'
			if($mode == 'xls') {
				if($tracking['mode'] == 'type') {
					foreach ($famSumCols as $fsc) {
						$data[$row][] = ($famSums[$fsc] == '' || !isset($famSums[$fsc])) ? 0 : $famSums[$fsc];
					}
				}
			}
			//Add sum column
			if($addsums) {
				if($tracking['mode'] == 'value' || $tracking['mode'] == 'valueNonNum') {
					$sum = '';
					if($sums['numeric'] > 0) $sum = $sums['numeric'];
					if(sizeof($sums) > 0) {
						foreach($sums as $k => $v) {
							if($k == 'numeric') continue;
							$sum .= ', '.$v.'x'.$k;
						}
					}
					if(substr($sum, 0, 2) == ', ') $sum = substr($sum, 2);
					$data[$row][] = $sum;
				} else {
					if($sums['numeric'] != 0) $data[$row][] = $sums['numeric'];
				}
			}

			$row++;
		}
	}

	//Create XLS file
	if($mode == 'xls') {
		//XLS header
		$col_headers = array();
		foreach($columns as $col) {
			$col_headers[] = $colnames[$col] ? $colnames[$col] : $col;
		}
		foreach($dates as $date) {
			$col_headers[] = $date['title'];
		}
		if ($mode == 'xls' && $tracking['mode'] == 'type') {
			foreach($famSumCols as $fsc) {
				$col_headers[] = $fsc;
			}
		}
		if($addsums) $col_headers[] = getLL('tracking_list_total');

		//Create subtitles (only use first entry for title)
		$subtitles = array();
		list($first_filter) = explode(',', $tracking['filter']);
		if(substr($first_filter, 0, 1) == 'g' && strlen($first_filter) >= 7) {
			$group = db_select_data('ko_groups', "WHERE `id` = '".substr($first_filter, 1)."'", '*', '', '', TRUE);
			$subtitles[getLL('groups_listheader_name')] = $group['name'];
		} else if(strlen($first_filter) == 4) {
			ko_get_kleingruppen($_kg, '', $first_filter);
			$kg = $_kg[$first_filter];
			if($kg['role_L']) {
				$leader = array();
				foreach(explode(',', $kg['role_L']) as $pid) {
					ko_get_person_by_id($pid, $p);
					$leader[] = $p['vorname'].' '.$p['nachname'];
				}
				$subtitles[getLL('tracking_export_subtitle_smallgroup_leader')] = implode(', ', $leader);
			}
			if($kg['wochentag']) $subtitles[getLL('kota_listview_ko_kleingruppen_wochentag')] = getLL('kota_ko_kleingruppen_wochentag_'.$kg['wochentag']);
			if($kg['ort']) $subtitles[getLL('kota_listview_ko_kleingruppen_ort')] = $kg['ort'];
			if($kg['zeit']) $subtitles[getLL('kota_listview_ko_kleingruppen_zeit')] = getLL('time_at').' '.$kg['zeit'];
		} else {
		}

		if($tracking['description'] != '') {
			$subtitles[getLL('tracking_export_label_description')] = $tracking['description'];
		}

		$header = array('header' => $col_headers, 'title' => $tracking['name'], 'subtitle' => $subtitles);
		ko_export_to_xlsx($header, $data, $filename, $tracking['name'], ($layout == 'P' ? 'portrait' : 'landscape'));
	}

	//Create PDF file
	else if($mode == 'pdf') {

		define('FPDF_FONTPATH', $ko_path.'fpdf/schriften/');
		require_once($ko_path.'fpdf/pdf_tracking.php');

		//Create new PDF-creator object
		$pdf = new pdf_tracking($layout, 'mm', 'a4');

		//Set the layout information of the PDF
		$pdf->layout['fontsize'] = 10;
		$pdf->layout['lineheight_xl'] = 7;
		$pdf->layout['lineheight_l'] = 5;
		$pdf->layout['lineheight_m'] = 4;
		$pdf->layout['lineheight_s'] = 3;
		$pdf->layout['margin_left'] = 15;
		$pdf->layout['margin_top'] = 10;
		$pdf->layout['margin_right'] = 15;
		$pdf->layout['margin_bottom'] = 20;
		$pdf->layout['orientation'] = $layout;
		$pdf->layout['x'] = 15;
		$pdf->layout['y'] = 10;
		$pdf->layout['footer_y'] = -14;
		$pdf->layout['header_linewidth'] = 0.2;
		$pdf->layout['page_width'] = $layout == 'P' ? 210 : 297;
		$pdf->layout['page_height'] = $layout == 'P' ? 297 : 210;
		$pdf->layout['page_width_usable'] = $pdf->layout['page_width'] - $pdf->layout['margin_left'] - $pdf->layout['margin_right'];

		//Add the needed data to create the footer and header
		$pdf->data['label_page'] = getLL('tracking_export_label_page');
		$pdf->data['label_name'] = getLL('tracking_export_label_name');
		$pdf->data['label_timespan'] = getLL('tracking_export_label_timespan');
		$pdf->data['label_description'] = getLL('tracking_export_label_description');
		$pdf->data['label_sum'] = getLL('tracking_export_label_sum');
		$pdf->data['description'] = $tracking['description'];
		$pdf->data['tracking_name'] = $tracking['name'];
		$pdf->data['timespan'] = strftime($DATETIME['dmY'], strtotime($dates[0]['date'])).' - '.strftime($DATETIME['dmY'], strtotime($dates[sizeof($dates)-1]['date']));
		$pdf->data['base_url'] = $BASE_URL;
		$person = ko_get_logged_in_person();
		$creator = $person['vorname'] ? $person['vorname'].' '.$person['nachname'] : $_SESSION['ses_username'];
		$pdf->data['created'] = sprintf(getLL('tracking_export_label_created'), strftime($DATETIME['dmY'].' %H:%M', time()), $creator);
		$pdf->data['ko_path'] = $ko_path;
		
		$pdf->SetZeilenhoehe($pdf->layout['lineheight_xl']);

		//Scale down the supplied logo in '../../my_images/pdf_logo.[jpg|png]'
		$logo_width_max = 50;
		$logo_height_max = 14;
		if (ko_get_pdf_logo() == '') {
			$pdf->data['logo_path'] = '';
		}
		else {
			$pdf->data['logo_path'] = $ko_path . 'my_images/' . ko_get_pdf_logo();
			$logo_desc = getimagesize($pdf->data['logo_path']);
			$logo_width = $logo_desc[0];
			$logo_height = $logo_desc[1];
			if ($logo_width > $logo_width_max) {
				$scale_factor = (float)$logo_width/(float)$logo_width_max;
				$logo_width = $logo_width / $scale_factor;
				$logo_height = $logo_height / $scale_factor;
			}
			if ($logo_height > $logo_height_max) {
				$scale_factor = (float)$logo_height/(float)$logo_height_max;
				$logo_width = $logo_width / $scale_factor;
				$logo_height = $logo_height / $scale_factor;
			}
			$pdf->layout['logo_width'] = $logo_width;
			$pdf->layout['logo_height'] = $logo_height;
			$pdf->layout['logo_left'] = $pdf->layout['page_width'] - $pdf->layout['margin_right'] - ceil($logo_width);
			$pdf->layout['logo_top'] = $pdf->layout['margin_top']-5;
		}

		$pdf->Open();

		//Add fonts, here Arial Black and Arial
		$pdf->AddFont('font', '', 'arial.php');
		$pdf->AddFont('fontb', '', 'arialb.php');

		//Set the margins supplied in the layout field and set autoPageBreak to true.
		$pdf->SetMargins($pdf->layout['margin_left'], $pdf->layout['margin_top'], $pdf->layout['margin_right']);
		$pdf->SetAutoPageBreak(true, $pdf->layout['margin_bottom']);

		$pdf->AddPage();

		//Set a minimum and maximum width of the data fields, depending on the type of field
		if(substr($tracking['mode'], 0, 7) == 'bitmask' || $tracking['mode'] == 'type') {
			$data_width_min = 35;
			$data_width_max = 50;
		}
		else {
			$data_width_min = 14;
			$data_width_max = 50;
		}

		$num_cols = sizeof($columns);
		$num_dates = sizeof($dates);

		//Define the width of column-headers and of the sum-header
		$col_width = 30;
		$sum_width = 25;

		//Determine how many data-columns will fit on a page
		$cols_fitting = floor((float)($pdf->layout['page_width_usable'] - $num_cols * $col_width) / (float)$data_width_min);
		$cols_fitting_with_sum = floor((float)($pdf->layout['page_width_usable'] - $num_cols * $col_width - $sum_width) / (float)$data_width_min);

		//Determine how many tables will be needed to show all columns
		if ($addsums) {
			if ($num_dates >= $cols_fitting) {
				$tables = ceil((float)$num_dates/(float)$cols_fitting);
				$data_width = ((float)($pdf->layout['page_width_usable'] - $num_cols * $col_width) / (float)$cols_fitting);
				$last_page_dates = ($num_dates % $cols_fitting == 0) ? (($num_dates == 0) ? 0 : $cols_fitting) : $num_dates % $cols_fitting;
				if ($last_page_dates > $cols_fitting_with_sum) {
					$tables ++;
				}
			}
			else if ($num_dates < $cols_fitting) {
				$tables = 1;
				if ($cols_fitting_with_sum < $num_dates) {
					$tables ++;
					$data_width = ((float)($pdf->layout['page_width_usable'] - $num_cols * $col_width) / (float)$num_dates);
					if ($data_width > $data_width_max) {
						$data_width = $data_width_max;
					}
				}
				else {
					$data_width = ((float)($pdf->layout['page_width_usable'] - $num_cols * $col_width - $sum_width) / (float)$num_dates);
					if ($data_width > $data_width_max) {
						$data_width = $data_width_max;
					}
				}
			}
		}
		else {
			if ($num_dates >= $cols_fitting) {
				$tables = ceil((float)$num_dates/(float)$cols_fitting);
				$data_width = (float)($pdf->layout['page_width_usable'] - $num_cols * $col_width) / (float)$cols_fitting;
			}
			else if ($num_dates < $cols_fitting) {
				$tables = 1;
				$data_width = (float)($pdf->layout['page_width_usable'] - $num_cols * $col_width) / (float)$num_dates;
				if ($data_width > $data_width_max) {
					$data_width = $data_width_max;
				}
			}
		}

		$dateformat = ko_get_userpref($_SESSION['ses_userid'], 'tracking_dateformat');
		if(!$dateformat) $dateformat = 'dm';

		$max = $cols_fitting;
		$table = '';
		$counter = 0;
		for($t=1; $t<=$tables; $t++) {


			$ldates = array();
			$ldata = array();
			$start = $counter;
			$stop = min($counter+$max, $num_dates);
			for($i=$start; $i<$stop; $i++) {
				$ldates[] = strftime($DATETIME[$dateformat], strtotime($dates[$i]['date']));
			}
			for($a=0; $a<$addrows; $a++) {
				$data['add'.$a] = array();
			}
			foreach($data as $key => $row) {
				for($j=0; $j<$num_cols; $j++) {
					$ldata[$key][] = $row[$j];
				}
				for($i=($num_cols+$start); $i<($num_cols+$stop); $i++) {
					$ldata[$key][] = $row[$i];
				}
				//Add sum for last table
				if($t == $tables && $addsums) {
					$ldata[$key][] = $row[$num_cols+$stop];
				}
			}
			//Create table header
			$table_cols = array();
			$table_header = array();
			foreach($columns as $col) {
				$table_cols[] = $col_width;
				$table_header[] = $colnames[$col] ? $colnames[$col] : $col;
			}
			foreach($ldates as $date) {
				$table_cols[] = $data_width;
				$table_header[] = $date;
			}
			//Add sum column header for last table
			if($t == $tables && $addsums) {
				$table_cols[] = $sum_width;
				$table_header[] = $pdf->data['label_sum'];
			}

			$pdf->SetWidths($table_cols);

			//Write table header
			$pdf->SetFont('fontb', '', $pdf->layout['fontsize']);
			$pdf->Row($table_header);

			//Write columns
			$pdf->SetFont('font', '', $pdf->layout['fontsize']);
			foreach($ldata as $row) {
				if (($pdf->GetY() + $pdf->CalculateRowHeight($row)) > ($pdf->layout['page_height'] - $pdf->layout['margin_bottom'])) {
					$pdf->AddPage();
					$pdf->SetFont('fontb', '', $pdf->layout['fontsize']);
					$pdf->Row($table_header);
					$pdf->SetFont('font', '', $pdf->layout['fontsize']);
				}
				$pdf->Row($row);
			}

			// autofill with empty rows if option is set
			if ($addrows == -1) {
				$dummyRow = array();
				for ($k = 0; $k < sizeof($ldata[0]); $k++) $dummyRow[] = '';
				$Y = 0;
				while ($pdf->GetY() + $pdf->layout['lineheight_xl'] < $pdf->PageBreakTrigger && $pdf->GetY() != $Y) {
					if (($pdf->GetY() + $pdf->CalculateRowHeight($row)) > ($pdf->layout['page_height'] - $pdf->layout['margin_bottom'])) {
						$pdf->AddPage();
						$pdf->SetFont('fontb', '', $pdf->layout['fontsize']);
						$pdf->Row($table_header);
						$pdf->SetFont('font', '', $pdf->layout['fontsize']);
					}
					$Y = $pdf->GetY();
					$pdf->Row($dummyRow);
				}
			}

			//Add new page after each table (except for the last one)
			if($t < $tables) $pdf->AddPage();;

			$counter += $max;
		}//for(t=1..tables)


		$pdf->Output($filename, false);

	}
}//ko_tracking_export_excel()



function apply_tracking_filter (&$z_where, &$z_limit) {

	//Apply selection in itemlist
	if(sizeof($_SESSION['show_tracking_groups']) > 0) {
		$z_where = " AND `group_id` IN ('".implode("','", $_SESSION['show_tracking_groups'])."')";
	} else {
		$z_where = ' AND 1=2';
	}

	// Apply checkbox for hidden trackings
	if ($_SESSION['tracking_filter']['show_hidden'] != 1) {
		$z_where .= " AND `hidden` = 0 ";
	}

	if(function_exists('kota_apply_filter')) {
		$kota_where = kota_apply_filter('ko_tracking');
		if($kota_where) $z_where .= " AND ($kota_where) ";
	}

	$z_limit = 'LIMIT '.($_SESSION['show_start']-1).', '.$_SESSION['show_limit'];
}



/**
	* Apply dates filter and limit for tracking dates
	*/
function apply_tracking_dates_filter(&$z_where, &$z_limit, $table) {
	$field = $table == 'ko_event' ? 'startdatum' : 'date';

	//Apply filters
	foreach($_SESSION['tracking_filter'] as $key => $value) {
		if(!$value) continue;
		switch($key) {
			case 'date1':
				ko_guess_date($_SESSION['tracking_filter'][$key], 'first');
				$z_where .= " AND `$field` >= '".$_SESSION['tracking_filter'][$key]."' ";
			break;

			case 'date2':
				ko_guess_date($_SESSION['tracking_filter'][$key], 'last');
				$z_where .= " AND `$field` <= '".$_SESSION['tracking_filter'][$key]."' ";
			break;
		}//switch(key)
	}//foreach(SESSION[filter])

	//Limit bestimmen
  $z_limit = 'LIMIT '.($_SESSION['show_start']-1).', '.$_SESSION['show_limit'];
}//apply_tracking_filter()




function apply_tracking_entries_filter(&$z_where) {
	$z_where = '';

	//Apply selection in itemlist
	foreach($_SESSION['show_tracking_groups'] as $k => $v) {
		if(trim($v) == '') unset($_SESSION['show_tracking_groups'][$k]);
	}
	if(sizeof($_SESSION['show_tracking_groups']) > 0) {
		$trackings = db_select_data('ko_tracking', "WHERE `group_id` IN (".implode(',', $_SESSION['show_tracking_groups']).")");
		$z_where = "AND `tid` IN ('".implode("','", array_keys($trackings))."')";
	} else {
		$z_where = 'AND 1=2';
	}

	if(function_exists('kota_apply_filter')) {
		$kota_where = kota_apply_filter('ko_tracking_entries');
		if($kota_where) $z_where .= " AND ($kota_where) ";
	}
}//apply_tracking_entries_filter()




/**
 * Gets the name of a given filter
 * @param filter mixed: groupID, smallgroupID or serialized filter preset
 * @returns string Name of the group, smallgroup of filter preset
 */
function ko_tracking_get_filter_name($filter) {
	//Group id
	if(substr($filter, 0, 1) == 'g' && strlen($filter) >= 7) {
		$group = db_select_data('ko_groups', "WHERE `id` = '".substr($filter, 1)."'", '*', '', '', TRUE);
		return $group['name'];
	}
	//Small group id
	else if(strlen($filter) == 4) {
		ko_get_kleingruppen($_kg, '', $filter);
		$sg = db_select_data('ko_kleingruppen', "WHERE `id` = '".substr($filter, 1)."'", '*', '', '', TRUE);
		return $sg['name'];
	}
	//Filter preset
	else if(substr($filter, 0, 1) == 'F') {
		$filterset = array_merge((array)ko_get_userpref('-1', '', 'filterset', 'ORDER BY `key` ASC'), (array)ko_get_userpref($_SESSION['ses_userid'], '', 'filterset', 'ORDER BY `key` ASC'));

		foreach($filterset as $f) {
			if($f['value'] == base64_decode(substr($filter, 1))) return $f['key'];
		}
	}
}//ko_tracking_get_filter_name()




/**
 * Fills in default data for a given trackingID and personID
 */
function ko_tracking_set_default($tid, $lid) {
	$tracking = db_select_data('ko_tracking', "WHERE `id` = '$tid'", '*', '', '', TRUE);

	$dates = ko_tracking_get_dates($tracking, '', '', $prev, $next);
	foreach($dates as $date) {
		$default_entries = db_select_data('ko_tracking_entries', "WHERE `tid` = '$tid' AND `lid` = '-1' AND `date` = '".$date['date']."'");
		foreach($default_entries as $default_entry) {
			$entry = db_select_data('ko_tracking_entries', "WHERE `tid` = '$tid' AND `lid` = '$lid' AND `date` = '".$date['date']."' AND `type` = '".$default_entry['type']."'", '*', '', '', TRUE);
			if(isset($entry['id']) && $entry['id'] > 0) {
				$data = array('value' => $default_entry['value'], 'last_change' => date('Y-m-d H:i:s'));
				db_update_data('ko_tracking_entries', "WHERE `id` = '".$entry['id']."'", $data);
			} else {
				$data = array('lid' => $lid,
						'tid' => $tid,
						'date' => $date['date'],
						'type' => $default_entry['type'],
						'value' => $default_entry['value'],
						'crdate' => date('Y-m-d H:i:s'),
						'cruser' => $_SESSION['ses_userid'],
						'last_change' => date('Y-m-d H:i:s'),
						);
				db_insert_data('ko_tracking_entries', $data);
			}
			ko_log_diff('enter_tracking', $data);
		}
	}
}//ko_tracking_set_default()

?>
