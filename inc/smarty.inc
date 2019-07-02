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

// Smarty Library Dateien laden
if(FALSE === include((empty($BASE_PATH) ? $ko_path : $BASE_PATH)."vendor/smarty/smarty/libs/Smarty.class.php")) {
	ko_die("Could not find Smarty. Please install libraries via Composer and ensure that $BASE_PATH is set correctly.");
}

//Load smarty template engine
if(substr($smarty_dir, -1) != "/") $smarty_dir .= "/";
$smarty = new Smarty();
$smarty->template_dir = $smarty_dir.'templates/';
$smarty->compile_dir = $smarty_dir.'templates_c/';
$smarty->cache_dir = $smarty_dir.'cache/';
$smarty->config_dir = $smarty_dir.'configs/';


$smarty->assign("ko_path", $ko_path);
if($ko_menu_akt != 'install') $smarty->assign('ko_guest', ($_SESSION['ses_userid'] == ko_get_guest_id()));


function smarty_modifier_contrast($hexcolor, $dark = '#000000', $light = '#FFFFFF') {
	return ko_get_contrast_color($hexcolor, $dark, $light);
}
if(method_exists($smarty, 'registerPlugin')) {  //Smarty v3
	$smarty->registerPlugin('modifier', 'contrast', 'smarty_modifier_contrast');
} else {  //Smarty v2
	$smarty->register_modifier("contrast", "smarty_modifier_contrast");
}


function smarty_function_ll($params, &$smarty) {
	return getLL($params['key']);
}
$smarty->register_function('ll', 'smarty_function_ll');


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
