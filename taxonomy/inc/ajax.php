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

//Send headers to ensure latin1 charset
header('Content-Type: text/html; charset=ISO-8859-1');

error_reporting(0);
$ko_menu_akt = 'taxonomy';
$ko_path = "../../";
require($ko_path . "inc/ko.inc");
$ko_path = "../";

array_walk_recursive($_GET, 'utf8_decode_array');

//Get access rights
ko_get_access('taxonomy');

ko_include_kota(array('ko_taxonomy_terms', 'ko_taxonomy_index'));

// Plugins einlesen:
$hooks = hook_include_main("taxonomy");
if (sizeof($hooks) > 0) foreach ($hooks as $hook) include_once($hook);

//HOOK: Submenus einlesen
$hooks = hook_include_sm();
if (sizeof($hooks) > 0) foreach ($hooks as $hook) include($hook);

hook_show_case_pre($_SESSION['show']);


if (isset($_GET) && isset($_GET["action"])) {
	$action = format_userinput($_GET["action"], "alphanum");

	hook_ajax_pre($ko_menu_akt, $action);

	switch ($action) {
		case 'termsearch':
			if ($access['taxonomy']['MAX'] < 1) break;

			$string = format_userinput($_GET['query'], 'alphanum++');
			if (!$string) {
				$string = '';
			}

			$terms = ko_taxonomy_get_terms();
			$structuredTerms = ko_taxonomy_terms_sort_hierarchically($terms, $string);
			$result = array();

			foreach ($structuredTerms AS $structuredTerm) {
				if(!empty($structuredTerm['children'])) {
					$result[] = [
						'id' => $structuredTerm['data']['id'],
						'name' => $structuredTerm['data']['name'],
						'title' => ($_GET['allowParentselect'] == "true" ? "" : "[parent] ") . $structuredTerm['data']['name'],
						'placeholder' => ($_GET['allowParentselect'] == "true" ? FALSE : TRUE)
					];

					foreach($structuredTerm['children'] AS $childTerm) {
						$result[] = [
							'id' => $childTerm['id'],
							'title' => "[children] " . $childTerm['name'],
							'name' => $childTerm['name'],
							'placeholder' => FALSE
						];
					}
				} else {
					$result[] = [
						'id' => $structuredTerm['data']['id'],
						'name' => $structuredTerm['data']['name'],
						'title' => $structuredTerm['data']['name'],
						'placeholder' => FALSE
					];
				}
			}

			if(empty($result) && $access['taxonomy']['MAX'] >= 2 && $_GET['allowInsert'] == "true") {
				$result[0] = [
					'id' => -1,
					'name' => sprintf(getLL("form_taxonomy_suggestbox_text"), $string),
					'title' => sprintf(getLL("form_taxonomy_suggestbox_text"), $string),
					'placeholder' => FALSE
				];
			}

			array_walk_recursive($result,'utf8_encode_array');
			print json_encode($result);
			break;
		case 'terminsert':
			if ($access['taxonomy']['MAX'] < 2) break;

			$string = format_userinput($_GET['query'], 'alphanum++');
			ko_taxonomy_add_term($string);
			$terms = ko_taxonomy_get_terms($string);
			$result = array();

			foreach($terms AS $term) {
				$result[] = [
					'id' => $term['id'],
					'name' => $term['name'],
					'title' => $term['name'],
					'placeholder' => FALSE
				];
			}

			array_walk_recursive($result,'utf8_encode_array');
			print json_encode($result);
			break;
		case "setstart":
			if($access['taxonomy']['MAX'] < 1) break;

			//Set list start
			if(isset($_GET['set_start'])) {
				$_SESSION['show_start'] = max(1, format_userinput($_GET['set_start'], 'uint'));
			}
			//Set list limit
			if(isset($_GET['set_limit'])) {
				$_SESSION['show_limit'] = max(1, format_userinput($_GET['set_limit'], 'uint'));
				ko_save_userpref($_SESSION['ses_userid'], 'show_limit_taxonomy', $_SESSION['show_limit']);
			}

			print "main_content@@@";
			ko_taxonomy_list(FALSE);
			break;
	}

	hook_ajax_post($ko_menu_akt, $action);
}
