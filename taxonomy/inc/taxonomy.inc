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

include_once($BASE_PATH."inc/class.kOOL_listview.php");
ko_get_access('taxonomy');

function ko_taxonomy_list($output=TRUE, $highlight=NULL) {
	global $access;

	if($access['taxonomy']['ALL'] < 1) return;

	$kota_where = kota_apply_filter('ko_taxonomy_terms');
	$z_where = "";
	if($kota_where) $z_where .= " AND ($kota_where) ";

	$order = 'ORDER BY name ASC';
	$rows = db_get_count('ko_taxonomy_terms', 'name', $z_where);
	if(!isset($_SESSION['show_start']) || $_SESSION['show_start'] > $rows) $_SESSION['show_start'] = 1;
	if($_SESSION['show_start'] && $_SESSION['show_limit']) $z_limit  = 'LIMIT '.($_SESSION['show_start']-1).', '.$_SESSION['show_limit'];
	$terms = db_select_data('ko_taxonomy_terms', 'WHERE 1=1' . $z_where, '*', $order, $z_limit);

	foreach($terms AS $key => $term) {
		$terms[$key]['used_in'] = $key;
	}

	$list = new kOOL_listview();

	$list->init('taxonomy', 'ko_taxonomy_terms', array('chk', 'edit', 'delete'), $_SESSION['show_start'], $_SESSION['show_limit']);
	$list->disableMultiedit();
	$list->setTitle(getLL('submenu_taxonomy_title_taxonomy'));
	$list->setAccessRights(array('edit' => 2, 'delete' => 2), $access['taxonomy']);
	$list->setActions(array('edit' => array('action' => 'edit_term'),
			'delete' => array('action' => 'delete_term', 'confirm' => TRUE))
	);

	if($access['taxonomy']['ALL'] > 1) {
		$list->setActionNew('new_term');
	}

	$list->setStats($rows, '', '', '', TRUE);

	//Output the list
	if($output) {
		$list->render($terms);
	} else {
		print $list->render($terms);
	}
}

/**
 * Create input dynamicsearch for taxonomy terms
 *
 * @param int $node_id
 * @param string $table
 * @param array $additionalTerms IDs to be set into $form['data']
 * @return array|bool
 */
function ko_taxonomy_form_field($node_id, $table, $additionalTerms = []) {
	global $access, $KOTA;

	$prefilledTerms = [];

	if(is_numeric($node_id) && !empty($table)) {
		$usedTaxonomyTerms = ko_taxonomy_get_terms_by_node($node_id, $table);
		foreach($usedTaxonomyTerms AS $term) {
			$prefilledTerms[] = [
				'id' => $term['id'],
				'name' => $term['name'],
				'parent' => $term['parent'],
			];
		}
	}

	if(!empty($additionalTerms[0])) {
		foreach($additionalTerms AS $additionalTerm) {
			$term = ko_taxonomy_get_term_by_id($additionalTerm);
			$prefilledTerms[] = [
				'id' => $term['id'],
				'name' => $term['name'],
				'parent' => $term['parent'],
			];
		}
	}

	$taxonomy_actions = [
		'search' => "termsearch",
	];

	if ($access['taxonomy']['MAX'] >= 2) {
		$taxonomy_actions['insert'] = "terminsert";
	}

	$form_field = $KOTA[$table]['terms']["form"];
	$form_field["data"] = $prefilledTerms;
	$form_field["avalue"] = implode(",", array_keys($prefilledTerms));
	$form_field['ajaxHandler']['actions'] = $taxonomy_actions;
	return $form_field;
}

/**
 * Show form to enter and edit news. Uses fields as defined in KOTA
 */
function ko_taxonomy_formular_term($mode, $id='') {
	global $access;

	if($access['taxonomy']['MAX'] < 2) return FALSE;

	if($mode == 'new') {
		$id = 0;
	} else if($mode == 'edit') {
		if(!$id) return FALSE;
	} else {
		return FALSE;
	}

	$form_data['title'] =  $mode == 'new' ? getLL('ko_taxonomy_terms_form_title_new') : getLL('ko_taxonomy_terms_form_title_edit');
	$form_data['submit_value'] = getLL('save');
	$form_data['action'] = $mode == 'new' ? 'submit_new_term' : 'submit_edit_term';
	$form_data['cancel'] = 'list_terms';
	ko_multiedit_formular('ko_taxonomy_terms', '', $id, '', $form_data);
	echo "<script>
		$(\"input[name*='koi[ko_taxonomy_terms][name]']\").each(function() {
			if($(this).val() === '') return;
			var regex = /\[(\d*)\]$/;
			if ((m = regex.exec($(this).attr(\"name\"))) !== null) {
				$(\"select[name*='koi[ko_taxonomy_terms][parent][\"+ m[1] + \"'] option[value='\" + m[1] + \"']\").remove();
			}
		});
	</script>";


	return TRUE;
}

function ko_get_searchbox_for_taxonomy_terms() {
	$terms = ko_taxonomy_get_terms();
	$structuredTerms = ko_taxonomy_terms_sort_hierarchically($terms);
	$html = "<select id=\"searchbox_taxonomy\" class=\"input-sm form-control\" required>
			    <option value=\"\" selected disabled hidden>".getLL('groups_list_search_taxonomy_label')."</option>";
	foreach($structuredTerms AS $structuredTerm) {
		if(!empty($structuredTerm['children'])) {
			$html .= "<option value='" . $structuredTerm['data']['id'] . "'>" . $structuredTerm['data']['name'] . "</option>";
			foreach($structuredTerm['children'] AS $childTerm) {
				$html .= "<option value='" . $childTerm['id'] . "'>&nbsp; &nbsp;" . $childTerm['name'] . "</option>";
			}
		} else {
			$html .= "<option value='" . $structuredTerm['data']['id'] . "'>" . $structuredTerm['data']['name'] . "</option>";
		}
	}

	$html.= "</select>";
	return $html;
}


/**
  * Displays settings
	*/
function ko_taxonomy_settings() {
	global $smarty;

	//build form
	$gc = 0;
	$rowcounter = 0;
	$frmgroup[$gc]['titel'] = getLL('settings_title_user');
	$frmgroup[$gc]['tab'] = true;

	//Default view and list limit
	$frmgroup[$gc]['row'][$rowcounter]['inputs'][0] = array('desc' => getLL('taxonomy_settings_default_view'),
			'type' => 'select',
			'name' => 'sel_default_view',
			'values' => array('list_terms', 'new_term'),
			'descs' => array(getLL('submenu_taxonomy_list_terms'), getLL('submenu_taxonomy_new_term')),
			'value' => ko_html(ko_get_userpref($_SESSION['ses_userid'], 'default_view_taxonomy'))
			);
	$frmgroup[$gc]['row'][$rowcounter]['inputs'][1] = array('desc' => getLL('admin_settings_limits_numberof_taxonomy'),
			'type' => 'text',
			'name' => 'txt_limit_taxonomy',
			'value' => ko_html(ko_get_userpref($_SESSION['ses_userid'], 'show_limit_taxonomy'))
			);

	//Allow plugins to add further settings
	hook_form('_settings', $frmgroup, '', '');

	//display the form
	$smarty->assign('tpl_titel', getLL('taxonomy_settings_form_title'));
	$smarty->assign('tpl_submit_value', getLL('save'));
	$smarty->assign('tpl_action', 'submit_taxonomy_settings');
	$smarty->assign('tpl_cancel', 'list_terms');
	$smarty->assign('tpl_groups', $frmgroup);
	$smarty->assign('help', ko_get_help('taxonomy', 'taxonomy_settings'));

	$smarty->display('ko_formular.tpl');
}



/**
 * Return term by id
 *
 * @param int $id
 * @return array|null term
 */
function ko_taxonomy_get_term_by_id($id) {
	$where = "WHERE t.id = " . (int) $id;
	$term = db_select_data('ko_taxonomy_terms t', $where, "*", "", "LIMIT 1", TRUE);
	return $term;
}



/**
 * Return all terms filtered by string and/or where it is used
 *
 * @param string $keyword to search in term names
 * @param array  $used_in_table list of tables to search within
 * @return array|null terms indexed by ID
 */
function ko_taxonomy_get_terms($keyword = '', $used_in_table = []) {
	if(empty($keyword)) {
		$where = "WHERE 1=1";
	} else {
		$where = "WHERE t.name LIKE '%".$keyword."%'";
	}

	$order = "ORDER BY t.name ASC";

	if(empty($used_in_table)) {
		$terms = db_select_data('ko_taxonomy_terms t', $where, "*", $order);
	} else {
		$where.= " AND i.table IN (\"" . implode('","', $used_in_table) . "\")";
		$terms = db_select_data("ko_taxonomy_index AS i LEFT JOIN ko_taxonomy_terms as t ON i.id = t.id",
			$where, "*", $order);
	}

	return $terms;
}

/**
 * Return terms by parent
 *
 * @param int $parentid
 * @return array|null term
 */
function ko_taxonomy_get_terms_by_parent($parentid) {
	$where = "WHERE t.parent = " . (int) $parentid;
	$terms = db_select_data('ko_taxonomy_terms t', $where, "*");
	return $terms;
}

/**
 * Return terms by specific node
 *
 * @param int $id
 * @param string $used_in_table
 * @return array|null
 */
function ko_taxonomy_get_terms_by_node($id, $used_in_table) {
	$order = "ORDER BY t.name ASC";
	$where = "WHERE i.node_id = '" . (int)$id . "' AND i.table = '" . $used_in_table . "'";
	$terms = db_select_data("ko_taxonomy_index AS i LEFT JOIN ko_taxonomy_terms as t ON i.id = t.id",
		$where, "*", $order);

	return $terms;
}


/**
 * Return nodes used by specific term id
 *
 * @param int $id Term Id
 * @param string $table
 * @return array
 */
function ko_taxonomy_get_nodes_by_termid($id, $table = '') {
	$order = "ORDER BY node_id ASC";
	$where = "WHERE id = '" . (int)$id . "'";

	if(!empty($table)) {
		$where.= " AND `table` = '" . $table . "'";
	}

	$node_ids = db_select_data("ko_taxonomy_index", $where, "node_id AS id", $order);

	if(!is_array($node_ids)) return array();
	return $node_ids;
}

/**
 * Delete term only if not used in nodes
 *
 * @param $term_id
 * @return bool
 */
function ko_taxonomy_delete_term($term_id) {
	if(!empty(ko_taxonomy_get_nodes_by_termid($term_id)) OR empty($term_id)) {
		return FALSE;
	}

	$where = "WHERE id = " . $term_id;
	db_delete_data("ko_taxonomy_terms", $where);
	return TRUE;
}

/**
 * Delete all nodes from index table specified by node_id
 *
 * @param int $node_id
 * @param string $table
 * @return bool
 */
function ko_taxonomy_delete_node($node_id, $table) {
	if(!is_numeric($node_id) || empty($table)) {
		return FALSE;
	}

	$where = "WHERE node_id = " . $node_id . " AND `table` = '" . $table . "'";
	$deleted_nodes = db_select_data("ko_taxonomy_index", $where);
	$log_message = "Typ: " . $table . ", Node-ID: ". $node_id . ", Term-IDs: " . implode(",", array_column($deleted_nodes, "id"));
	ko_log("delete_term_node", $log_message);
	db_delete_data("ko_taxonomy_index", $where);
	return TRUE;
}


/** Group a list of terms by their parent and sort by names
 *
 * @param $terms
 * @param $filter_keyword
 * @return array
 */
function ko_taxonomy_terms_sort_hierarchically($terms, $filter_keyword = '') {
	$structuredTerms = [];
	foreach($terms AS $term) {
		if($term['parent'] > 0) {
			$structuredTerms[$term['parent']]['children'][$term['id']] = $term;
		} else {
			$structuredTerms[$term['id']]['data'] = $term;
		}
	}

	usort($structuredTerms, function($a, $b) {
		return strcmp(strtolower($a['data']['name']), strtolower($b['data']['name']));
	});

	foreach($structuredTerms AS $key => $structuredTerm) {
		if(!empty($structuredTerms[$key]['children'])) {
			usort($structuredTerms[$key]['children'], function($a, $b) {
				return strcmp(strtolower($a['name']), strtolower($b['name']));
			});
		}
	}

	if(!empty($filter_keyword)) {
		foreach($structuredTerms AS $key => $structuredTerm) {
			if (stristr($structuredTerm['data']['name'], $filter_keyword) !== FALSE) continue;
			if (!empty($structuredTerms[$key]['children'])) {
				foreach ($structuredTerms[$key]['children'] AS $key2 => $childTerm) {
					if (stristr($childTerm['name'], $filter_keyword) === FALSE) {
						unset($structuredTerms[$key]['children'][$key2]);
					}
				}
			}

			// if we dont have children, remove parent
			if (empty($structuredTerms[$key]['children'])) {
				unset($structuredTerms[$key]);
			}
		}
	}

	return $structuredTerms;
}

/**
 * Save a new term in ko_taxonomy_terms
 *
 * @param string $term name of term
 * @param int $parent id of parent term
 * @return int|null Id of inserted term
 */
function ko_taxonomy_add_term($term, $parent = 0) {
	global $access;

	if ($access['taxonomy']['MAX'] < 2) return NULL;

	$where = "AND name = '" . $term . "'";
	if(db_get_count('ko_taxonomy_terms', "id", $where) > 0) return NULL;

	$data = [
		"name" => $term,
		"parent" => $parent,
		"crdate" => strftime("%Y-%m-%d %T", time()),
		"cruser" => $_SESSION['ses_userid'],
	];

	return db_insert_data("ko_taxonomy_terms", $data);
}


/**
 * Connect a term to specific node (save in ko_taxonomy_index)
 *
 * @param array $terms list of ids
 * @param string $table where node_id is saved
 * @param int $node_id
 * @return null
 */
function ko_taxonomy_attach_terms_to_node($terms, $table, $node_id) {
	global $access;
	if ($access['taxonomy']['MAX'] < 1) return null;
	if (empty($terms[0]) OR empty($table) OR empty($node_id)) return;

	foreach($terms AS $term) {
		$where = "AND id = " . $term . " AND `table` = '" . $table . "' AND node_id = " . (int)$node_id;
		if(db_get_count('ko_taxonomy_index', "id", $where) > 0) continue;

		$data = [
			"id" => $term,
			"table" => $table,
			"node_id" => $node_id,
			"crdate" => strftime("%Y-%m-%d %T", time()),
			"cruser" => $_SESSION['ses_userid'],
		];

		db_insert_data("ko_taxonomy_index", $data);
	}
}

/**
 * To correctly save new terms on node, we need to clear the previous state
 *
 * @param string $table
 * @param int $node_id
 * @return void
 */
function ko_taxonomy_clear_terms_on_node($table, $node_id) {
	global $access;
	if ($access['taxonomy']['MAX'] < 1) return;
	if (empty($table) OR empty($node_id)) return;

	$where = "WHERE `table` = '" . $table . "' AND node_id = " . (int)$node_id;
	db_delete_data("ko_taxonomy_index", $where);
}
