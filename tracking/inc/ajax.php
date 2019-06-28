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

//Set session id from GET (session will be started in ko.inc)
if(!isset($_GET['sesid'])) exit;
if(FALSE === session_id($_GET['sesid'])) exit;

//Send headers to ensure latin1 charset
header('Content-Type: text/html; charset=ISO-8859-1');
 
error_reporting(0);
$ko_menu_akt = 'tracking';
$ko_path = '../../';
require($ko_path.'inc/ko.inc');
$ko_path = '../';

//Get access rights
ko_get_access('tracking');
if($access['tracking']['MAX'] < 1) exit;

ko_include_kota(array('ko_tracking', 'ko_tracking_entries'));

// Plugins einlesen:
$hooks = hook_include_main('tracking');
if(sizeof($hooks) > 0) foreach($hooks as $hook) include_once($hook);
 
//Smarty-Templates-Engine laden
require($BASE_PATH.'inc/smarty.inc');
 
require($BASE_PATH.'tracking/inc/tracking.inc');

//HOOK: Submenus einlesen
$hooks = hook_include_sm();
if(sizeof($hooks) > 0) foreach($hooks as $hook) include($hook);

hook_show_case_pre($_SESSION['show']);

 
if(isset($_GET) && isset($_GET['action'])) {
 	$action = format_userinput($_GET['action'], 'alphanum');

	hook_ajax_pre($ko_menu_akt, $action);

 	switch($action) {

		case 'setstart':
			//Set list start
			if(isset($_GET['set_start'])) {
				$_SESSION['show_start'] = max(1, format_userinput($_GET['set_start'], 'uint'));
	    }
			//Set list limit
			if(isset($_GET['set_limit'])) {
				$_SESSION['show_limit'] = max(1, format_userinput($_GET['set_limit'], 'uint'));
				ko_save_userpref($_SESSION['ses_userid'], 'show_limit_trackings', $_SESSION['show_limit']);
	    }

			print 'main_content@@@';
			print ko_list_trackings(FALSE);
		break;


		case 'setdatelimit':
			if(isset($_GET['set_limit'])) {
				$_SESSION['date_limit'] = max(1, format_userinput($_GET['set_limit'], 'uint'));
				ko_save_userpref($_SESSION['ses_userid'], 'tracking_date_limit', $_SESSION['date_limit']);
	    }

			print 'main_content@@@';
			print ko_tracking_enter_form(FALSE);
		break;


		case 'setsort':
			$_SESSION['sort_trackings'] = format_userinput($_GET['sort'], 'alphanum+', TRUE, 30);
			$_SESSION['sort_trackings_order'] = format_userinput($_GET['sort_order'], 'alpha', TRUE, 4);

			print 'main_content@@@';
			print ko_list_trackings(FALSE);
		break;


		case 'setsortmod':
			$_SESSION['sort_modtrackings'] = format_userinput($_GET['sort'], 'alphanum+', TRUE, 30);
			$_SESSION['sort_modtrackings_order'] = format_userinput($_GET['sort_order'], 'alpha', TRUE, 4);

			print 'main_content@@@';
			print ko_list_tracking_mod_entries(FALSE);
		break;


 		case 'settrackingall':
 		case 'deltrackingall':
 			if($access['tracking']['MAX'] < 2) continue;

			$tid = format_userinput($_GET['tid'], 'uint');
			if($access['tracking']['ALL'] < 2 && $access['tracking'][$tid] < 2) continue;
			$tracking = db_select_data('ko_tracking', "WHERE `id` = '$tid'", '*', '', '', TRUE);
			$date = format_userinput($_GET['date'], 'date');
			if($action == 'deltrackingall') {
				$value = '';
			} else {
				if(isset($_GET['value'])) {
					$value = mysql_real_escape_string($_GET['value']);
				} else {
					$value = 1;
				}
			}

			if(isset($_GET['type'])) {
				$type = mysql_real_escape_string($_GET['type']);
			} else {
				$type = '';
			}

			//Get all displayed people
			$people = ko_tracking_get_people($tracking['filter'], $dates, $tid, TRUE);
			
			foreach($people as $p) {
				$entry = db_select_data('ko_tracking_entries', "WHERE `tid` = '$tid' AND `lid` = '".$p['id']."' AND `date` = '$date' AND `type` = '$type'", '*', '', '', TRUE);

				if($action == 'deltrackingall') {
					db_delete_data('ko_tracking_entries', "WHERE `tid` = '$tid' AND `lid` = '".$p['id']."' AND `date` = '$date' AND `type` = '$type'");
					ko_log_diff('del_entered_tracking', $entry);
				} else {
					if(isset($entry['id']) && $entry['id'] > 0) {
						$data = array('last_change' => date('Y-m-d H:i:s'), 'value' => $value);
						db_update_data('ko_tracking_entries', "WHERE `id` = '".$entry['id']."'", $data);
						$data['lid'] = $p['id'];
						$data['tid'] = $tid;
						$data['date'] = $date;
						$data['type'] = $type;
					} else {
						$data = array('tid' => $tid,
													'lid' => $p['id'],
													'date' => $date,
													'value' => $value,
													'type' => $type,
													'crdate' => date('Y-m-d H:i:s'),
													'cruser' => $_SESSION['ses_userid'],
													'last_change' => date('Y-m-d H:i:s'));
						db_insert_data('ko_tracking_entries', $data);
					}
					ko_log_diff('enter_tracking', $data);
				}
			}

			//Update whole table
			print 'main_content@@@';
			print ko_tracking_enter_form(FALSE);
		break;



		case 'setdefault':
 			if($access['tracking']['MAX'] < 2) continue;

			$lid = format_userinput($_GET['lid'], 'uint');
			$tid = format_userinput($_GET['tid'], 'uint');
			if($access['tracking']['ALL'] < 2 && $access['tracking'][$tid] < 2) continue;

			ko_tracking_set_default($tid, $lid);

			//Update whole table and show infobox
			print 'main_content@@@';
			print ko_tracking_enter_form(FALSE);
		break;
 

 		case 'settrackingsimple':
 		case 'settrackingvalue':
 			if($access['tracking']['MAX'] < 2) continue;

			$data = array();
			$data['tid'] = format_userinput($_GET['tid'], 'uint');
			if($access['tracking']['ALL'] < 2 && $access['tracking'][$data['tid']] < 2) continue;
			$data['lid'] = format_userinput($_GET['lid'], 'int');
			$data['date'] = format_userinput($_GET['date'], 'date');
			if($action == 'settrackingsimple') {
				$data['value'] = format_userinput($_GET['value'], 'uint') == 1 ? 0 : 1;
			} else {
				$data['value'] = $_GET['value'];
			}

			$entry = db_select_data('ko_tracking_entries', "WHERE `tid` = '".$data['tid']."' AND `lid` = '".$data['lid']."' AND `date` = '".$data['date']."'", '*', '', '', TRUE);
			if(isset($entry['id']) && $entry['id'] > 0) {
				$data['last_change'] = date('Y-m-d H:i:s');
				db_update_data('ko_tracking_entries', "WHERE `id` = '".$entry['id']."'", $data);
			} else {
				$data['crdate'] = date('Y-m-d H:i:s');
				$data['cruser'] = $_SESSION['ses_userid'];
				$data['last_change'] = date('Y-m-d H:i:s');
				db_insert_data('ko_tracking_entries', $data);
			}
			ko_log_diff('enter_tracking', $data);

			print 'tstate_'.$data['lid'].'_'.$data['date'].'@@@';
			print '<img src="'.$ko_path.'images/button_check.png" width="16" height="16" alt="OK" />';
		break;


 		case 'settrackingtype':
 		case 'settrackingtypecheck':
 			if($access['tracking']['MAX'] < 2) continue;

			$data = array();
			$data['tid'] = format_userinput($_GET['tid'], 'uint');
			if($access['tracking']['ALL'] < 2 && $access['tracking'][$data['tid']] < 2) continue;
			$data['lid'] = format_userinput($_GET['lid'], 'int');
			$data['date'] = format_userinput($_GET['date'], 'date');
			$data['value'] = format_userinput($_GET['value'], 'float');
			if($action == 'settrackingtype') {
				$data['type'] = utf8_decode($_GET['type']);
			} else {
				$data['type'] = $_GET['type'];
			}

			$entry = db_select_data('ko_tracking_entries', "WHERE `tid` = '".$data['tid']."' AND `lid` = '".$data['lid']."' AND `date` = '".$data['date']."' AND `type` = '".$data['type']."'", '*', '', '', TRUE);
			if(isset($entry['id']) && $entry['id'] > 0) {
				$data['last_change'] = date('Y-m-d H:i:s');
				if($action == 'settrackingtype') {
					//Check setting on tracking to add to current or create new entry
					$tracking = db_select_data('ko_tracking', "WHERE `id` = '".$data['tid']."'", '*', '', '', TRUE);
					if($tracking['type_multiple']) {
						$data['crdate'] = date('Y-m-d H:i:s');
						$data['cruser'] = $_SESSION['ses_userid'];
						$data['last_change'] = date('Y-m-d H:i:s');
						db_insert_data('ko_tracking_entries', $data);
					} else {
						(float)$data['value'] += (float)$entry['value'];
						db_update_data('ko_tracking_entries', "WHERE `id` = '".$entry['id']."'", $data);
					}
				} else if($action == 'settrackingtypecheck') {
					//Do nothing: Just keep data[value] from GET
					if($data['value'] == 0) {
						db_delete_data('ko_tracking_entries', "WHERE `id` = '".$entry['id']."'");
					} else {
						db_update_data('ko_tracking_entries', "WHERE `id` = '".$entry['id']."'", $data);
					}
				}
			} else {
				$data['crdate'] = date('Y-m-d H:i:s');
				$data['cruser'] = $_SESSION['ses_userid'];
				$data['last_change'] = date('Y-m-d H:i:s');
				db_insert_data('ko_tracking_entries', $data);
			}
			ko_log_diff('enter_tracking', $data);

			//Redraw content for tracking list
			print 'main_content@@@';
			ko_tracking_enter_form(FALSE);
		break;


 		case 'settrackingbitmask':
 			if($access['tracking']['MAX'] < 2) continue;

			$data = array();
			$data['tid'] = format_userinput($_GET['tid'], 'uint');
			if($access['tracking']['ALL'] < 2 && $access['tracking'][$data['tid']] < 2) continue;
			$data['lid'] = format_userinput($_GET['lid'], 'uint');
			$data['date'] = format_userinput($_GET['date'], 'date');
			$data['value'] = format_userinput($_GET['value'], 'uint');

			$entry = db_select_data('ko_tracking_entries', "WHERE `tid` = '".$data['tid']."' AND `lid` = '".$data['lid']."' AND `date` = '".$data['date']."' AND `type` = '".$data['type']."'", '*', '', '', TRUE);
			if(isset($entry['id']) && $entry['id'] > 0) {
				if(!((int)$entry['value'] & (int)$data['value'])) {
					$data['value'] = (int)$entry['value'] + (int)$data['value'];
					$data['last_change'] = date('Y-m-d H:i:s');
					db_update_data('ko_tracking_entries', "WHERE `id` = '".$entry['id']."'", $data);
				}
			} else {
				$data['crdate'] = date('Y-m-d H:i:s');
				$data['cruser'] = $_SESSION['ses_userid'];
				$data['last_change'] = date('Y-m-d H:i:s');
				db_insert_data('ko_tracking_entries', $data);
			}
			ko_log_diff('enter_tracking', $data);

			//Redraw content for tracking list
			print 'main_content@@@';
			ko_tracking_enter_form(FALSE);
		break;


		case 'deltrackingtype':
 			if($access['tracking']['MAX'] < 2) continue;

			$id = format_userinput($_GET['id'], 'uint');
			if(!$id) continue;

			$data = db_select_data('ko_tracking_entries', "WHERE `id` = '$id'", '*', '', '', TRUE);
			if($data['id'] != $id || ($access['tracking']['ALL'] < 2 && $access['tracking'][$data['tid']] < 2)) continue;

			db_delete_data('ko_tracking_entries', "WHERE `id` = '$id'");
			ko_log_diff('del_entered_tracking', $data);

			//Redraw content for tracking list
			print 'main_content@@@';
			ko_tracking_enter_form(FALSE);
		break;



		case 'confirmtrackingtype':
 			if($access['tracking']['MAX'] < 2) continue;

			$data = array();
			$data['tid'] = format_userinput($_GET['tid'], 'uint');
			if($access['tracking']['ALL'] < 2 && $access['tracking'][$data['tid']] < 2) continue;
			$data['lid'] = format_userinput($_GET['lid'], 'uint');
			$data['date'] = format_userinput($_GET['date'], 'date');
			$data['type'] = utf8_decode($_GET['type']);

			db_update_data('ko_tracking_entries', "WHERE `tid` = '".$data['tid']."' AND `lid` = '".$data['lid']."' AND `date` = '".$data['date']."' AND `type` = '".$data['type']."'", array('status' => '0', 'last_change' => date('Y-m-d H:i:s')));
			ko_log_diff('confirm_entered_tracking', $data);

			//Redraw content for tracking list
			print 'main_content@@@';
			ko_tracking_enter_form(FALSE);
		break;


		case 'deltrackingbitmask':
 			if($access['tracking']['MAX'] < 2) continue;

			$data = array();
			$data['tid'] = format_userinput($_GET['tid'], 'uint');
			if($access['tracking']['ALL'] < 2 && $access['tracking'][$data['tid']] < 2) continue;
			$data['lid'] = format_userinput($_GET['lid'], 'uint');
			$data['date'] = format_userinput($_GET['date'], 'date');
			$data['value'] = format_userinput($_GET['value'], 'uint');

			$current = db_select_data('ko_tracking_entries', "WHERE `tid` = '".$data['tid']."' AND `lid` = '".$data['lid']."' AND `date` = '".$data['date']."'", '*', '', '', TRUE);
			if((int)$data['value'] & (int)$current['value']) {
				$data['value'] = (int)$current['value'] - (int)$data['value'];
				db_update_data('ko_tracking_entries', "WHERE `tid` = '".$data['tid']."' AND `lid` = '".$data['lid']."' AND `date` = '".$data['date']."'", array('value' => $data['value'], 'last_change' => date('Y-m-d H:i:s')));
				ko_log_diff('del_entered_tracking', $data, $current);
			}

			//Redraw content for tracking list
			print 'main_content@@@';
			ko_tracking_enter_form(FALSE);
		break;



		case 'comment':
 			if($access['tracking']['MAX'] < 2) continue;

			$tid = format_userinput($_GET['tid'], 'uint');
			if($access['tracking']['ALL'] < 2 && $access['tracking'][$data['tid']] < 2) continue;

			if(isset($_GET['eid'])) {
				$eid = format_userinput($_GET['eid'], 'uint');
				if(!$eid) continue;
				$entry = db_select_data('ko_tracking_entries', "WHERE `id` = '$eid'", '*', '', '', TRUE);
			} else {
				$lid = format_userinput($_GET['lid'], 'uint');
				$date = format_userinput($_GET['date'], 'date');

				$entry = db_select_data('ko_tracking_entries', "WHERE `tid` = '$tid' AND `lid` = '$lid' AND `date` = '$date'", '*', '', '', TRUE);
				$eid = $entry['id'];
			}
			if(!$entry['id']) continue;

			$content = '<textarea name="comment" id="comment_'.$tid.'_'.$eid.'" cols="40" rows="5">'.$entry['comment'].'</textarea><br />';
			$content .= '<input type="button" name="submit_tracking_comment" value="'.getLL('save').'" onclick="$.get(\'../tracking/inc/ajax.php\', {action: \'savecomment\', eid: \''.$eid.'\', tid: \''.$tid.'\', comment: $(\'#comment_'.$tid.'_'.$eid.'\').val(), sesid: \''.session_id().'\'}, tracking_entered_value_type); TINY.box.hide();" />';

			print $content;
		break;



		case 'delcomment':
 			if($access['tracking']['MAX'] < 2) continue;

			$tid = format_userinput($_GET['tid'], 'uint');
			if($access['tracking']['ALL'] < 2 && $access['tracking'][$data['tid']] < 2) continue;
			$lid = format_userinput($_GET['lid'], 'uint');
			$date = format_userinput($_GET['date'], 'date');

			db_update_data('ko_tracking_entries', "WHERE `tid` = '$tid' AND `lid` = '$lid' AND `date` = '$date'", array('comment' => '', 'last_change' => date('Y-m-d H:i:s')));

			//Redraw content for tracking list
			print 'main_content@@@';
			ko_tracking_enter_form(FALSE);
		break;



		case 'savecomment':
 			if($access['tracking']['MAX'] < 2) continue;

			$tid = format_userinput($_GET['tid'], 'uint');
			if($access['tracking']['ALL'] < 2 && $access['tracking'][$data['tid']] < 2) continue;
			$eid = format_userinput($_GET['eid'], 'uint');
			$comment = format_userinput(utf8_decode($_GET['comment']), 'text');

			db_update_data('ko_tracking_entries', "WHERE `id` = '$eid'", array('comment' => $comment, 'last_change' => date('Y-m-d H:i:s')));

			//Redraw content for tracking list
			print 'main_content@@@';
			ko_tracking_enter_form(FALSE);
		break;



		case 'itemlist':
			//ID and state of the clicked field
			$id = format_userinput($_GET['id'], 'js');
			$state = $_GET['state'] == 'true' ? 'checked' : '';

			//Single event group selected
			if($state == 'checked') {  //Select it
				if(!in_array($id, $_SESSION['show_tracking_groups'])) $_SESSION['show_tracking_groups'][] = $id;
			} else {  //deselect it
				if(in_array($id, $_SESSION['show_tracking_groups'])) $_SESSION['show_tracking_groups'] = array_diff($_SESSION['show_tracking_groups'], array($id));
			}
			array_unique($_SESSION['show_tracking_groups']);
			foreach($_SESSION['show_tracking_groups'] as $k => $v) {
				if($v == '') unset($_SESSION['show_tracking_groups'][$k]);
			}

			//Save userpref
			sort($_SESSION['show_tracking_groups']);
			ko_save_userpref($_SESSION['ses_userid'], 'show_tracking_groups', implode(',', $_SESSION['show_tracking_groups']));

			//Redraw content for tracking list
			print 'main_content@@@';
			if($_SESSION['show'] == 'mod_entries') {
				ko_list_tracking_mod_entries(FALSE);
			} else {
				ko_list_trackings(FALSE);
			}
		break;


		case 'itemlistsave':
			//Find position of submenu for redraw
			if(in_array('itemlist_trackinggroups', explode(',', $_SESSION['submenu_left']))) $pos = 'left';
			else $pos = 'right';

			//save new value
			if($_GET['name'] == '') continue;
			$new_value = implode(',', $_SESSION['show_tracking_groups']);
			$user_id = $access['tracking']['MAX'] > 3 && $_GET['global'] == 'true' ? '-1' : $_SESSION['ses_userid'];
			ko_save_userpref($user_id, format_userinput($_GET['name'], 'js', FALSE, 0, array('allquotes')), $new_value, 'tracking_itemset');

			print submenu_tracking('itemlist_trackinggroups', $pos, 'open', 2);
		break;


		case 'itemlistopen':
			//Find position of submenu for redraw
			if(in_array('itemlist_trackinggroups', explode(',', $_SESSION['submenu_left']))) $pos = 'left';
			else $pos = 'right';

			//save new value
			$name = format_userinput($_GET['name'], 'js', FALSE, 0, array(), '@');
			if($name == '') continue;

			if($name == '_all_') {
				$groups = db_select_data('ko_tracking_groups', '', '*');
				$_SESSION['show_tracking_groups'] = array_merge(array(0), array_keys($groups));
			} else if($name == '_none_') {
				$_SESSION['show_tracking_groups'] = array();
			} else {
				if(substr($name, 0, 3) == '@G@') $value = ko_get_userpref('-1', substr($name, 3), 'tracking_itemset');
				else $value = ko_get_userpref($_SESSION['ses_userid'], $name, 'tracking_itemset');
				$_SESSION['show_tracking_groups'] = explode(',', $value[0]['value']);
			}
			ko_save_userpref($_SESSION['ses_userid'], 'show_tracking_groups', implode(',', $_SESSION['show_tracking_groups']));

			print submenu_tracking('itemlist_trackinggroups', $pos, 'open', 2);
			print '@@@main_content@@@';
			ko_list_trackings(FALSE);
		break;


		case 'itemlistdelete':
			//Find position of submenu for redraw
			if(in_array('itemlist_trackinggroups', explode(',', $_SESSION['submenu_left']))) $pos = 'left';
			else $pos = 'right';

			//Get name
			$name = format_userinput($_GET['name'], 'js', FALSE, 0, array(), '@');
			if($name == '') continue;

			if(substr($name, 0, 3) == '@G@') {
				if($access['tracking']['MAX'] > 3) ko_delete_userpref('-1', substr($name, 3), 'tracking_itemset');
			} else ko_delete_userpref($_SESSION['ses_userid'], $name, 'tracking_itemset');

			print submenu_tracking('itemlist_trackinggroups', $pos, 'open', 2);
		break;




		case 'filterpresetsave':
			//Find position of submenu for redraw
			if(in_array('filter', explode(',', $_SESSION['submenu_left']))) $pos = 'left';
			else $pos = 'right';

			//save new value
			if($_GET['name'] == '') continue;
			if ($_SESSION['tracking_filter']['date1'] == '' OR $_SESSION['tracking_filter']['date2'] == '') continue;
			$new_value = $_SESSION['tracking_filter']['date1'] . ',' . $_SESSION['tracking_filter']['date2'];
			$user_id = $access['tracking']['MAX'] > 3 && $_GET['global'] == 'true' ? '-1' : $_SESSION['ses_userid'];
			ko_save_userpref($user_id, format_userinput($_GET['name'], 'js', FALSE, 0, array('allquotes')), $new_value, 'tracking_filterpreset');

			print submenu_tracking('filter', $pos, 'open', 2);
		break;

		case 'filterpresetopen':
			//Find position of submenu for redraw
			if(in_array('filter', explode(',', $_SESSION['submenu_left']))) $pos = 'left';
			else $pos = 'right';

			//save new value
			$name = format_userinput($_GET['name'], 'js', FALSE, 0, array(), '@');
			if($name == '') continue;

			/*if($name == '_all_') {
				$groups = db_select_data('ko_tracking_groups', '', '*');
				$_SESSION['show_tracking_groups'] = array_merge(array(0), array_keys($groups));
			} else if($name == '_none_') {
				$_SESSION['show_tracking_groups'] = array();
			} else {
				if(substr($name, 0, 3) == '@G@') $value = ko_get_userpref('-1', substr($name, 3), 'tracking_itemset');
				else $value = ko_get_userpref($_SESSION['ses_userid'], $name, 'tracking_itemset');
				$_SESSION['show_tracking_groups'] = explode(',', $value[0]['value']);
			}*/

			if(substr($name, 0, 3) == '@G@') $value = ko_get_userpref('-1', substr($name, 3), 'tracking_filterpreset');
			else $value = ko_get_userpref($_SESSION['ses_userid'], $name, 'tracking_filterpreset');
			list($date1, $date2) = explode(',', $value[0]['value']);
			$_SESSION['tracking_filter']['date1'] = $date1;
			$_SESSION['tracking_filter']['date2'] = $date2;

			if($_SESSION['tracking_filter']['date1']) $_SESSION['date_start'] = $_SESSION['tracking_filter']['date1'];

			//ko_save_userpref($_SESSION['ses_userid'], 'show_tracking_groups', implode(',', $_SESSION['show_tracking_groups']));

			print submenu_tracking('filter', $pos, 'open', 2);
			print '@@@main_content@@@';
			if($_SESSION['show'] == 'enter_tracking') {
				ko_tracking_enter_form(FALSE);
			} else {
				ko_list_trackings(FALSE);
			}
		break;

		case 'filterpresetdelete':
			//Find position of submenu for redraw
			if(in_array('filter', explode(',', $_SESSION['submenu_left']))) $pos = 'left';
			else $pos = 'right';

			//Get name
			$name = format_userinput($_GET['name'], 'js', FALSE, 0, array(), '@');
			if($name == '') continue;

			if(substr($name, 0, 3) == '@G@') {
				if($access['tracking']['MAX'] > 3) ko_delete_userpref('-1', substr($name, 3), 'tracking_filterpreset');
			} else ko_delete_userpref($_SESSION['ses_userid'], $name, 'tracking_filterpreset');

			print submenu_tracking('filter', $pos, 'open', 2);
		break;

		case 'setfilterhidden':
			//Find position of submenu for redraw
			if(in_array('filter', explode(',', $_SESSION['submenu_left']))) $pos = 'left';
			else $pos = 'right';

			$showHidden = $_GET['showhidden'];

			if ($showHidden == 'true') {
				$_SESSION['tracking_filter']['show_hidden'] = 1;
			}
			else {
				$_SESSION['tracking_filter']['show_hidden'] = 0;
			}

			print submenu_tracking('trackings', $pos, 'open', 2);
			print '@@@main_content@@@';
			ko_list_trackings(FALSE);
		break;

	}//switch(action);

	hook_ajax_post($ko_menu_akt, $action);

}//if(GET[action])
?>
