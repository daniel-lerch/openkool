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

function hook_include_main($type) {
	global $BASE_PATH;

	$r = NULL;
	$hooks = hook_get_by_type($type);

	if(sizeof($hooks) > 0) {
		foreach($hooks as $hook) {
			$file = $BASE_PATH."plugins/$hook/$hook.php";
			if(file_exists($file)) $r[] = $file;
		}
	}

	return $r;
}//hook_include_main()


function hook_include_sm() {
	global $BASE_PATH, $PLUGINS;

	$r = NULL;
	
	foreach($PLUGINS as $plugin) {
		$file = $BASE_PATH."plugins/".$plugin["name"]."/submenu.php";
		if(file_exists($file)) $r[] = $file;
	}
	return $r;
}//hook_include_sm()


function hook_include_ll() {
	global $BASE_PATH, $PLUGINS;

	$r = NULL;
	
	foreach($PLUGINS as $plugin) {
		$file = $BASE_PATH."plugins/".$plugin["name"]."/locallang.php";
		if(file_exists($file)) $r[] = $file;
	}
	return $r;
}//hook_include_ll()


function hook_include_scheduler_task() {
	global $BASE_PATH, $PLUGINS;

	$r = NULL;
	
	foreach($PLUGINS as $plugin) {
		$file = $BASE_PATH.'plugins/'.$plugin['name'].'/scheduler_task.php';
		if(file_exists($file)) $r[] = $file;
	}
	return $r;
}//hook_include_sm()



function hook_include_js($type='') {
	global $ko_path, $PLUGINS;

	$r = NULL;

	if($type) {
		$use_plugins = hook_get_by_type($type);
	} else {
		$use_plugins = array();
		foreach($PLUGINS as $p) {
			$use_plugins[] = $p['name'];
		}
	}

	if(sizeof($use_plugins) > 0) {
		foreach($use_plugins as $plugin) {
			$file = $ko_path.'plugins/'.$plugin.'/'.$plugin.'.js';
			if(file_exists($file)) $r[] = $file;
		}
	}
	return $r;
}//hook_include_js()



//Erwartet Komma-getrennte Liste der Plugin-Typen und gibt die entsprechenden Include-Datein inkl. Pfad zurück
function hook_get_by_type($type) {
	global $PLUGINS;
	global $BASE_PATH;

	if(!$BASE_PATH) return;

	$types = explode(",", $type);
	//Globale Plugins immer mit einlesen
	$types[] = "global";

	$r = array();
	foreach($PLUGINS as $m) {
		//Check for valid name and main PHP file
  	if(!$m['name'] || !file_exists($BASE_PATH.'plugins/'.$m['name'])) continue;

		//Check for type
		$ok = FALSE;
		foreach($types as $type) {
	  	if($type == '_all' || in_array($type, explode(',', $m['type']))) $ok = TRUE;
		}
		if($ok) {
	    $temp = $BASE_PATH."plugins/".$m["name"];

			//Dateiname nur gemäss BASE_PATH erlauben
			$full_path = realpath($temp);
	    if(substr($full_path, 0, strlen($BASE_PATH."plugins")) != ($BASE_PATH."plugins")) {
				trigger_error("Not allowed Hook-File: ".$temp, E_USER_ERROR);
	      exit;
	    }
			$r[] = $m["name"];
	  }
	}//foreach(PLUGINS)

	return $r;
}//hook_get_by_type()




function hook_action_handler($action) {
	global $PLUGINS;

	$found = FALSE;
	foreach($PLUGINS as $plugin) {
		if($plugin["name"] == substr($action, 0, strlen($plugin["name"]))) $found = TRUE;
	}

	if($found && function_exists("my_action_handler_".$action)) {
    call_user_func("my_action_handler_".$action);
		return TRUE;
  } else { 
	  return FALSE;
	}
}//hook_action_handler()




function hook_action_handler_add($action) {
	global $PLUGINS;

	foreach($PLUGINS as $plugin) {
		$action_ = $plugin["name"]."_".$action;
		if(function_exists("my_action_handler_add_".$action_)) {
			call_user_func("my_action_handler_add_".$action_);
		}
	}
}//hook_action_handler_add()



function hook_action_handler_inline($action) {
	global $PLUGINS;

	foreach($PLUGINS as $plugin) {
		$action_ = $plugin["name"]."_".$action;
		if(function_exists("my_action_handler_inline_".$action_)) {
			call_user_func("my_action_handler_inline_".$action_);
		}
	}
}//hook_action_handler_inline()



/**
 * Allow hooks to add a new show case
 */
function hook_show_case($show) {
	global $PLUGINS;

	$found = FALSE;
	foreach($PLUGINS as $plugin) {
		if($plugin["name"] == substr($show, 0, strlen($plugin["name"]))) $found = TRUE;
	}

	if($found && function_exists("my_show_case_".$show)) {
    call_user_func("my_show_case_".$show);
  }
}//hoock_show_case()



/**
 * Allow hooks to act after a defined show case
 */
function hook_show_case_add($show) {
	global $PLUGINS;

	foreach($PLUGINS as $plugin) {
		$show_ = $plugin["name"]."_".$show;
		if(function_exists("my_show_case_add_".$show_)) {
			call_user_func("my_show_case_add_".$show_);
		}
	}
}//hook_show_case_add()



/**
 * Allow hooks to act before a defined show case
 */
function hook_show_case_pre($show) {
	global $PLUGINS;

	foreach($PLUGINS as $plugin) {
		$show_ = $plugin["name"]."_".$show;
		if(function_exists("my_show_case_pre_".$show_)) {
			call_user_func("my_show_case_pre_".$show_);
		}
	}
}//hook_show_case_pre()



/**
 * Hook called after building the form array which will be passed to smarty.
 * Only used for old or complex form which are not done with KOTA (yet)
 * Used e.g. in groups module for group form and group settings
 * @param string $table DB table for which the form should be changed
 * @param array &$data Array holding the form definition. Passed by reference so plugin can change this
 * @param string $mode Should be new or edit, depending on the way the form is used (to create a new record or to edit one)
 * @param int $id Id of the currently edited record (if mode is edit)
 */
function hook_form($table, &$data, $mode, $id, $additional_data='') {
	global $PLUGINS;

	foreach($PLUGINS as $plugin) {
		$action_ = $plugin['name'].'_'.$table;
		if(function_exists('my_form_'.$action_)) {
			call_user_func_array('my_form_'.$action_, array(&$data, $mode, $id, $additional_data));
		}
	}
}//hook_form()




/**
 * Hook called after the given AJAX call to the ajax.php of the given module
 */
function hook_ajax_post($module, $action) {
	global $PLUGINS;

	foreach($PLUGINS as $plugin) {
		$action_ = $plugin['name'].'_'.$action;
		if(function_exists('my_ajax_post_'.$action_)) {
			call_user_func('my_ajax_post_'.$action_);
		}
	}
}//hook_ajax_post()



/**
 * Hook called before the given AJAX call to the ajax.php of the given module
 */
function hook_ajax_pre($module, $action) {
	global $PLUGINS;

	foreach($PLUGINS as $plugin) {
		$action_ = $plugin['name'].'_'.$action;
		if(function_exists('my_ajax_pre_'.$action_)) {
			call_user_func('my_ajax_pre_'.$action_);
		}
	}
}//hook_ajax_pre()



/**
 * Hook called inside the given AJAX call to the ajax.php of the given module
 */
function hook_ajax_inline($module, $action, &$data) {
	global $PLUGINS;

	foreach($PLUGINS as $plugin) {
		$action_ = $plugin['name'].'_'.$action;
		if(function_exists('my_ajax_inline_'.$action_)) {
			call_user_func_array('my_ajax_inline_'.$action_, array(&$data));
		}
	}
}//hook_ajax_inline()




/**
 * Hook called inside a function call
 */
function hook_function_inline($function, &$data) {
	global $PLUGINS;

	foreach($PLUGINS as $plugin) {
		$action = $plugin['name'].'_'.$function;
		if(function_exists('my_function_inline_'.$action)) {
			call_user_func_array('my_function_inline_'.$action, array(&$data));
		}
	}
}//hook_function_inline()



function hook_submenu($module, $menu, &$submenu, &$menucounter, &$itemcounter) {
	global $my_submenu, $access;

	if(is_array($my_submenu[$module][$menu])) {
		foreach($my_submenu[$module][$menu] as $s) {
			$pre  = is_array($s['show']) && in_array($_SESSION["show"], $s["show"]) ? "<b>" : "";
			$post = is_array($s['show']) && in_array($_SESSION["show"], $s["show"]) ? "</b>" : "";
			$submenu[$menucounter]["output"][$itemcounter] = $pre.$s["output"].$post;
			$submenu[$menucounter]["link"][$itemcounter] = $s["link"];
			$submenu[$menucounter]["html"][$itemcounter++] = $s["html"];
		}
	}
}//hook_submenu()




/**
 * Allow plugins to add new columns for address list
 */
function hook_leute_add_column(&$r) {
	global $PLUGINS;

	foreach($PLUGINS as $plugin) {
		if(function_exists('my_leute_add_column_'.$plugin['name'])) {
			call_user_func_array('my_leute_add_column_'.$plugin['name'], array(&$r));
		}
	}
}//hook_leute_add_column()





/**
 * Allow plugins to add logic after two addresses have been merged
 */
function hook_leute_merge($id, $merged_id) {
	global $PLUGINS;

	foreach($PLUGINS as $plugin) {
		$fcn = 'my_leute_merge_'.$plugin['name'];
		if(function_exists($fcn)) {
			call_user_func_array($fcn, array($id, $merged_id));
		}
	}
}//hook_leute_merge()




/**
 * Allow plugins to handle kota post events (multiedit, inline edit, etc)
 */
function hook_kota_post($table, $data) {
	global $PLUGINS;

	foreach($PLUGINS as $plugin) {
		$action_ = $plugin['name'].'_'.$table;
		if(function_exists('my_kota_post_'.$action_)) {
			call_user_func('my_kota_post_'.$action_, $data);
		}
	}
}//hook_kota_post()





/**
 * Hook for access system: Get group for a given module for which access levels may be set
 * module must be set in global array $MODULES_GROUP_ACCESS
 *
 * @param string $module Name of module
 */
function hook_access_get_groups($module) {
	$groups = array();

	$fcn = 'my_'.$module.'_access_get_groups';
	if(function_exists($fcn)) {
		eval('$groups = '.$fcn.'();');
	}
	return $groups;
}//hook_access_get_groups()




/**
 * Hook for access system: Get available levels for given module
 *
 * @param string $module Name of module
 * @param array &$values Will contain the possible values (usually 0-4)
 * @param array &$descs Will contain the possible descriptions (usually 0-4)
 * @return boolean TRUE if a hook function has been found and so the values/descs should be used
 *								 FALSE to fall back to default levels
 */
function hook_access_get_levels($module, &$values, &$descs) {
	$values = $descs = array();

	$fcn = 'my_'.$module.'_access_get_levels';
	if(function_exists($fcn)) {
		call_user_func_array($fcn, array(&$values, &$descs));
		return TRUE;
	}
	return FALSE;
}//hook_access_get_levels()
?>
