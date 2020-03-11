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

// Smarty Library Dateien laden
if(FALSE === include_once("Smarty.class.php")) ko_die("Could not find Smarty. Please install. For more information see http://www.smarty.net");

//Load smarty template engine
if(!$smarty_dir) $smarty_dir = $BASE_PATH;
if(substr($smarty_dir, -1) != "/") $smarty_dir .= "/";
$smarty = new Smarty();
$smarty->template_dir = $smarty_dir.'templates/';
$smarty->compile_dir = $smarty_dir.'templates_c/';
$smarty->cache_dir = $smarty_dir.'cache/';
$smarty->config_dir = $smarty_dir.'configs/';


$smarty->assign("ko_path", $ko_path);
if($ko_menu_akt != 'install') $smarty->assign('ko_guest', ($_SESSION['ses_userid'] == ko_get_guest_id()));


if(!function_exists('smarty_modifier_contrast')) {
	function smarty_modifier_contrast($hexcolor, $dark = '#000000', $light = '#FFFFFF') {
		return ko_get_contrast_color($hexcolor, $dark, $light);
	}
}
if(method_exists($smarty, 'registerPlugin')) {  //Smarty v3
	$smarty->registerPlugin('modifier', 'contrast', 'smarty_modifier_contrast');
} else {  //Smarty v2
	$smarty->register_modifier("contrast", "smarty_modifier_contrast");
}


if(!function_exists('smarty_function_ll')) {
	function smarty_function_ll($params, &$smarty) {
		$array_position = strpos($params['key'],"[");
		if($array_position !== FALSE) {
			$array = getLL(substr($params['key'],0, $array_position));
			return $array[substr($params['key'],$array_position+1, -1)];
		} else {
			return getLL($params['key']);
		}
	}
}
$smarty->register_function('ll', 'smarty_function_ll');

$uid = 0;
if(!function_exists('smarty_function_uid')) {
	function smarty_function_uid($params, &$smarty) {
		global $uid;

		$u = round(microtime(true)*1000) . '-' . $uid;
		$uid++;
		$smarty->assign($params['loc'], 'agi-' . $u);
	}
}
$smarty->register_function('uid', 'smarty_function_uid');

if(!function_exists('smarty_function_strtoupper')) {
	function smarty_function_strtoupper($params, &$smarty) {
		return strtoupper($params['str']);
	}
}
$smarty->register_function('strtoupper', 'smarty_function_strtoupper');

if(!function_exists('smarty_function_utf8_encode_array')) {
	function smarty_function_utf8_encode_array($params, &$smarty) {
		$a = $params['arr'];
		array_walk_recursive($a, 'utf8_encode_array');
		if ($params['var']) {
			$smarty->assign($params['var'], $a);
		} else {
			return $a;
		}
	}
}
$smarty->register_function('utf8_encode_array', 'smarty_function_utf8_encode_array');

if (!function_exists('smarty_function_preprocess_form')) {
	function smarty_function_preprocess_form($params, &$smarty) {
		global $BOOTSTRAP_COLS_PER_ROW;

		$form = &$params['form'];
		$activeTabFound = FALSE;
		foreach ($form as $tk => &$tab) {
			$tab['name'] = $tab['name'] ? $tab['name'] : $tk;
			$isEmpty = TRUE;
			foreach ($tab['groups'] as $gk => &$group) {
				$group['name'] = $group['name'] ? $group['name'] : $gk;
				$group['appearance'] = $group['appearance'] ? $group['appearance'] : 'primary';

				foreach ($group['rows'] as $rk => &$row) {
					if (sizeof($row['inputs']) == 0) unset($group['rows'][$rk]);
					else {
						$colspansSet = TRUE;
						foreach ($row['inputs'] as $ik => &$input) {
							if (!$input['columnWidth']) $colspansSet = FALSE;
						}

						if (!$colspansSet) {
							foreach ($row['inputs'] as $ik => &$input) {
								$input['columnWidth'] = min($BOOTSTRAP_COLS_PER_ROW / 2, round($BOOTSTRAP_COLS_PER_ROW / sizeof($row['inputs'])));
							}
						}
					}
				}

				if (sizeof($group['rows']) > 0) $isEmpty = FALSE;
				else {
					unset($tab['groups'][$gk]);
				}
			}
			if ($isEmpty) unset($form[$tk]);
			else if ($tab['active']) {
				if (!$activeTabFound) $activeTabFound = TRUE;
				else $tab['active'] = FALSE;
			}
		}

		if (sizeof($form) > 1) {
			$ti = 0;
			foreach ($form as $tk => &$tab) {
				if (!$activeTabFound && $ti == 0) $tab['active'] = TRUE;
				$tab['group'] = TRUE;
				if (!$tab['titel']) $tab['titel'] = getLL('kota_group_title__default');
				$ti++;
			}
		}

		$smarty->assign('tpl_groups', $form);
	}
}
$smarty->register_function('preprocessForm', 'smarty_function_preprocess_form');

if(!function_exists('smarty_function_preset_for_logins')) {
	function smarty_function_preset_for_logins($params, &$smarty) {
		ko_get_logins($logins);
		$values = $descs = $avalues = array();
		$avalue = '';
		foreach ($logins as $l) {
			if ($l['id'] == ko_get_root_id() || $l['id'] == $_SESSION['ses_userid'] || $l['disabled']) continue;
			$values[] = $l['id'];
			$descs[] = $l['login'];
		}
		$i = array(
			'name' => $params['name'],
			'type' => 'checkboxes',
			'size' => 6,
			'values' => $values,
			'descs' => $descs,
			'avalues' => $avalues,
			'avalue' => $avalue,
		);
		if ($params['var']) {
			$smarty->assign($params['var'], $i);
		} else {
			return $i;
		}
	}
}
$smarty->register_function('presetForLogins', 'smarty_function_preset_for_logins');

if(!function_exists('smarty_function_ko_path')) {
	function smarty_function_ko_path($params, &$smarty) {
		global $ko_path;

		$p = $ko_path;
		if ($params['var']) {
			$smarty->assign($params['var'], $p);
		} else {
			return $p;
		}
	}
}
$smarty->register_function('koPath', 'smarty_function_ko_path');


if(!function_exists('smarty_function_tooltip')) {
	function smarty_function_tooltip($params, &$smarty) {
		$text = $params['text'];
		$position = $params['position'] ? $params['position'] : 'auto';
		$container = $params['container'] ? $params['container'] : 'body';
		return ko_get_tooltip_code($text, $position, $container);
	}
}
$smarty->register_function('tooltip', 'smarty_function_tooltip');



// Assign bootstrap variables
$smarty->assign("bootstrap_cols_per_row", $BOOTSTRAP_COLS_PER_ROW);
//Assign general LL-Labels
//Itemlists
$smarty->assign("itemlist_open_preset", getLL("itemlist_open_preset"));
$smarty->assign("itemlist_save_preset", getLL("itemlist_save_preset"));
$smarty->assign('itemlist_preset_all', getLL('itemlist_preset_all'));
$smarty->assign('itemlist_preset_none', getLL('itemlist_preset_none'));
$smarty->assign("itemlist_delete_preset", getLL("itemlist_delete_preset"));
$smarty->assign("itemlist_delete_preset_confirm", getLL("itemlist_delete_preset_confirm"));
$smarty->assign("itemlist_show", getLL("itemlist_show"));
$smarty->assign("itemlist_hide", getLL("itemlist_hide"));
$smarty->assign("itemlist_refresh", getLL("itemlist_refresh"));
$smarty->assign("itemlist_sortcols", getLL("itemlist_sortcols"));
$smarty->assign("itemlist_groupdata", getLL("itemlist_groupdata"));
$smarty->assign("itemlist_global", getLL("itemlist_global"));
//GSM-Notes
$smarty->assign("notizen_open", getLL("notizen_open"));
$smarty->assign("notizen_delete", getLL("notizen_delete"));
$smarty->assign("notizen_delete_confirm", getLL("notizen_delete_confirm"));
$smarty->assign("notizen_save", getLL("notizen_save"));
//Multiedit
$smarty->assign("multiedit_list_title", getLL("multiedit_list_title"));
//Forms
$smarty->assign("label_reset", getLL("reset"));
$smarty->assign("label_cancel", getLL("cancel"));
$smarty->assign("label_save", getLL("save"));
$smarty->assign("label_doubleselect_remove", getLL("form_doubleselect_remove"));
$smarty->assign("label_text_mylist_import", getLL("form_text_mylist_import"));
$smarty->assign("label_color_choose", getLL("form_color_choose"));
//Submenu-Actions
$smarty->assign("label_sm_up", getLL("submenu_up"));
$smarty->assign("label_sm_down", getLL("submenu_down"));
$smarty->assign("label_sm_left", getLL("submenu_left"));
$smarty->assign("label_sm_right", getLL("submenu_right"));
$smarty->assign("label_sm_open", getLL("submenu_open"));
$smarty->assign("label_sm_close", getLL("submenu_close"));
//list-navigation-labels
$smarty->assign("label_list_next", getLL("list_next"));
$smarty->assign("label_list_back", getLL("list_back"));
$smarty->assign("label_list_sort_asc", getLL("list_sort_asc"));
$smarty->assign("label_list_sort_desc", getLL("list_sort_desc"));
$smarty->assign("label_list_col_left", getLL("list_col_left"));
$smarty->assign("label_list_col_right", getLL("list_col_right"));
$smarty->assign("label_list_check", getLL("list_check"));
$smarty->assign("label_list_check_family", getLL("list_check_family"));
//formular double select
$smarty->assign("label_form_ds_top", getLL("form_ds_top"));
$smarty->assign("label_form_ds_up", getLL("form_ds_up"));
$smarty->assign("label_form_ds_down", getLL("form_ds_down"));
$smarty->assign("label_form_ds_bottom", getLL("form_ds_bottom"));
$smarty->assign("label_form_ds_del", getLL("form_ds_del"));
$smarty->assign("label_form_ds_assigned", getLL("form_ds_assigned"));
$smarty->assign("label_form_ds_objects", getLL("form_ds_objects"));
//Form element foreign_table
$smarty->assign('label_form_ft_new', getLL('form_ft_new'));
?>
