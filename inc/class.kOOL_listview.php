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


/**
 * Class to render list views
 * This is the preferred way to create lists in kOOL. It is based on
 * the smarty template ko_list2.tpl.
 * It takes the definitions from $KOTA to render the list.
 */
class kOOL_listview {
	var $_editColumns = array('chk', 'chk2', 'edit', 'delete', 'check', 'forward', 'undelete', 'tracking_show', 'tracking_add', 'mailing', 'add', 'remove', 'send');
	var $tmpl = array();  //Holds the values to be submitted to smarty on rendering
	var $doFooter = FALSE;
	var $disableMultiedit = FALSE;
	var $disableAccessRights = FALSE;
	var $rowData = array();
	var $manualAccess = array();
	var $sortable = FALSE;


	/**
	 * Initialise the list with some base informations
	 * @param module: The kOOL module in which this list is used
	 * @param table: Database table as defined in $KOTA
	 * @param editColumns: Which action columns (edit, delete, etc) should be shown
	 * @param start: Set the first element to be shown in a paged view
	 * @param limit: Set the limit of items to be shown on one page
	 */
	function init($module, $table, $editColumns="", $start="", $limit="") {
		$this->module = $module;
		$this->table = $table;
		if($editColumns != "") $this->setEditColumns($editColumns);
		if($start != "") $this->start = $start;
		if($limit != "") $this->limit = $limit;
		$this->session_id = session_id();
	}//init()


	function disableMultiedit() {
		$this->disableMultiedit = TRUE;
	}//disableMultiedit()

	function disableListCheckAll() {
		$this->disableListCheckAll = TRUE;
	}//disableListCheckAll()

	function disableKotaProcess() {
		$this->disableKotaProcess = TRUE;
	}//disableKotaProcess()


	function setTitle($title) {
		$this->tmpl["list_title"] = $title;
	}//setTitle

	function setSubTitle($title) {
		$this->tmpl["list_subtitle"] = $title;
	}//setSubTitle


	function setWarning($text) {
		if($text != '') $this->tmpl['list_warning'] = $text;
	}//setWarning()


	/**
	 * Set what columns should be displayed to the left (chk, edit, delete etc.)
	 */
	function setEditColumns($cols) {
		if(!is_array($cols)) {
			$cols = explode(",", $cols);
		}
		foreach($cols as $col) {
			if(!in_array($col, $this->_editColumns)) continue;
			$this->editColumns[$col] = $col;
		}
	}//setEditColumns()


	/**
	 * Set Statistic information for paging
	 */
	function setStats($total, $start="", $limit="", $limitAction='', $hideLimitIcons=FALSE) {
		if($start != "") $this->start = $start;
		if($limit != "") $this->limit = $limit;

		//Paging stats
		$this->tmpl["stats"] = array("start" => $this->start, "end" => min( ($this->start+$this->limit-1), $total), "total" => $total, "oftotal" => getLL("list_oftotal"));
		//Prepare icons to increase/decrease the list limit
		if($hideLimitIcons) {
			$this->tmpl['stats']['hide_listlimiticons'] = TRUE;
		} else {
			$this->tmpl['stats']['limitM'] = $this->limit >= 100 ? $this->limit-50 : max(10, $this->limit-10);
			$this->tmpl['stats']['limitP'] = $this->limit >= 50 ? $this->limit+50 : $this->limit+10;
			if($limitAction != '') $this->tmpl['stats']['limitAction'] = $limitAction;
		}

		//Links for prev and next page
		$setStartAction = $limitAction ? $limitAction : 'setstart';
		if($this->start > 1) {
			$this->tmpl["paging"]["prev"] = "javascript:sendReq('../".$this->module."/inc/ajax.php', 'action,set_start,sesid', '".$setStartAction.",".max($this->start-$this->limit, 1).",".session_id()."', do_element);";
		} else {
			$this->tmpl["paging"]["prev"] = "";
		}
		if(($this->start+$this->limit-1) < $total) {
			$this->tmpl["paging"]["next"] = "javascript:sendReq('../".$this->module."/inc/ajax.php', 'action,set_start,sesid', '".$setStartAction.",".($this->limit+$this->start).",".session_id()."', do_element);";
		} else {
			$this->tmpl["paging"]["next"] = "";
		}
	}//setStats()


	/**
	 * Define a list footer
	 */
	function setFooter($footer) {
		if($footer) {
			$this->doFooter = TRUE;
			$this->tmpl["list"]["footer"] = array("show" => TRUE, "data" => $footer);
		}
	}//setFooter




	function getColItemlist($possible_cols) {
		global $smarty, $KOTA;

		$itemlist = array();
		$counter = 0;
		foreach($possible_cols as $d) {
			$active = 0;
			if(in_array($d, $_SESSION['kota_show_cols_'.$this->table])) $active = 1;
			$itemlist[$counter]['name'] = getLL('kota_'.$this->table.'_'.$d) ? getLL('kota_'.$this->table.'_'.$d) : $d;
			$itemlist[$counter]['aktiv'] = $active;
			$itemlist[$counter]['value'] = $d;
			$counter++;
		}//foreach(teams)
		$smarty->assign('tpl_itemlist_select', $itemlist);

		//Get all presets
		$akt_value = implode(',', $_SESSION['kota_show_cols_'.$this->table]);
		$itemset = array_merge((array)ko_get_userpref('-1', '', $this->table.'_colitemset', 'ORDER by `key` ASC'), (array)ko_get_userpref($_SESSION['ses_userid'], '', $this->table.'_colitemset', 'ORDER by `key` ASC'));
		$itemselect_values = $itemselect_output = array();
		foreach($itemset as $i) {
			$value = $i['user_id'] == '-1' ? '@G@'.$i['key'] : $i['key'];
			$itemselect_values[] = $value;
			$itemselect_output[] = $i['user_id'] == '-1' ? getLL('itemlist_global_short').' '.$i['key'] : $i['key'];
			if($i['value'] == $akt_value) $itemselect_selected = $value;
		}
		$smarty->assign('tpl_itemlist_values', $itemselect_values);
		$smarty->assign('tpl_itemlist_output', $itemselect_output);
		$smarty->assign('tpl_itemlist_selected', $itemselect_selected);
		//TODO?: Allow global: if($max_rights > 3) $smarty->assign('allow_global', TRUE);
		$smarty->assign('table', $this->table);
		$smarty->assign('hide_table_html', TRUE);
		$smarty->assign('show_flyout_header', TRUE);
		$smarty->assign('label_flyout_header', getLL('kota_listview_flyout_header'));
		$smarty->assign('sm', array('mod' => $KOTA[$this->table]['_access']['module'], 'sesid' => session_id()));

		$r = $smarty->fetch('ko_itemlist_kota.tpl');

		//Clear smarty assigns
		$clear_vars = array('tpl_itemlist_select', 'tpl_itemlist_values', 'tpl_itemlist_output', 'tpl_itemlist_selected', 'action_suffix', 'hide_table_html', 'sm', 'show_flyout_header');
		if(method_exists($smarty, 'clearAssign')) {  //Smarty v3
			$smarty->clearAssign($clear_vars);
		} else {  //Smarty v2
			$smarty->clear_assign($clear_vars);
		}

		return $r;
	}//getColItemlist()



	/**
	 * Activate flyout for ColItemlist
	 */
	function showColItemlist() {
		$this->showColItemlist = TRUE;
	}//showColItemlist()



	/**
	 * Render the list
	 * Second argument may be xls to get an Excel file
	 * Third parameter may be the file prefix for XLS export
	 */
	function render($data, $mode="html", $file_prefix="") {
		global $KOTA, $smarty, $ko_path;

		//Sort listview for the key
		ksort($KOTA[$this->table]["_listview"], SORT_NUMERIC);


		//Get a list of possible cols to be used for list display
		$possible_cols = array();
		foreach($KOTA[$this->table]["_listview"] as $c) {
			if($c['name']) $possible_cols[] = $c['name'];
		}

		//Get default list columns from userpref if non stored in session
		if(!is_array($_SESSION['kota_show_cols_'.$this->table]) || sizeof($_SESSION['kota_show_cols_'.$this->table]) == 0) {
			//Get from userpref
			$userpref = ko_get_userpref($_SESSION['ses_userid'], 'kota_show_cols_'.$this->table);
			if($userpref != '') $_SESSION['kota_show_cols_'.$this->table] = explode(',', $userpref);
		}
		//Or get from KOTA
		if(!is_array($_SESSION['kota_show_cols_'.$this->table]) || sizeof($_SESSION['kota_show_cols_'.$this->table]) == 0) {
			$_SESSION['kota_show_cols_'.$this->table] = $KOTA[$this->table]['_listview_default'];
		}
		$show_cols = $_SESSION['kota_show_cols_'.$this->table];
		if($mode == 'xls' && is_array($KOTA[$this->table]['_listview_xls'])) {
			$show_cols = $KOTA[$this->table]['_listview_xls'];
		}
		if(is_array($show_cols)) {
			foreach($show_cols as $k => $v) {
				if(!in_array($v, $possible_cols)) {  //Remove not allowed columns
					unset($show_cols[$k]);
				} else {  //If a column is given for list view which is not set in the data array, then add it. Allows adding fake columns
					foreach($data as $id => $dataarray) {
						if(!isset($dataarray[$v])) {
							$data[$id][$v] = '';
						}
					}
				}
			}
		} else {  //Backwards compatibility
			$show_cols = array();
			foreach($KOTA[$this->table]['_listview'] as $c) {
				if($c['name']) $show_cols[] = $c['name'];
			}
		}

		if($this->showColItemlist) {
			$this->tmpl['show_colitemlist'] = TRUE;
			$this->tmpl['colitemlist'] = $this->getColItemlist($possible_cols);
		}

		//Multiedit
		$multiedit = array();
		if(!$this->disableMultiedit && ($this->disableAccessRights || $this->access['MAX'] >= $this->accessLevels['edit'])) {
			foreach($KOTA[$this->table]["_listview"] as $col) {
				if(!in_array($col['name'], $show_cols)) continue;
				if(isset($col["multiedit"]) && $col["multiedit"] == FALSE) {
					$multiedit[] = "";
				} else {
					$multiedit[] = $col["multiedit"] != "" ? $col["multiedit"] : $col["name"];
				}
			}
			$this->tmpl["show_multiedit"] = TRUE;
			$this->tmpl["multiedit_cols"] = $multiedit;
		} else {
			$this->tmpl["show_multiedit"] = FALSE;
		}

		//Prepare header
		foreach($KOTA[$this->table]["_listview"] as $col) {
			if(!in_array($col['name'], $show_cols)) continue;

			//Filter
			if($col['filter'] === TRUE) {
				$col['filter'] = $this->table.':'.($col['multiedit'] ? $col['multiedit'] : $col['name']);
			} else if($col['filter'] != '') {
				$col['filter'] = $this->table.':'.$col['filter'];
			}

			//Mark activly filtered column headers
			if($col['filter']) {
				$filterfields = $col['multiedit'] ? $col['multiedit'] : $col['name'];
				foreach(explode(',', $filterfields) as $f) {
					if(!$f) continue;
					if($_SESSION['kota_filter'][$this->table][$f] != '') $col['filter_state'] = 'active';
				}
			}

			$col["name"] = getLL("kota_listview_".$this->table."_".$col["name"]);
			$header[] = $col;
		}
		$this->tmpl["list"]["header"] = $header;

		//Sorting
		$this->tmpl["list"]["sort"] = $this->sort;
		$this->tmpl['list']['sortable'] = $KOTA[$this->table]['_sortable'];

		$this->tmpl['list']['table'] = $this->table;


		$render_data = array(); $row_counter = 0;
		//Go through all rows to be displayed
		foreach($data as $id => $value) {
			//Process data
			if(!$this->disableKotaProcess) kota_process_data($this->table, $value, ($mode == 'xls' ? 'xls,list' : 'list'), $log, $id);
			//Assign values to list view array
			$coli = 0;
			foreach($KOTA[$this->table]['_listview'] as $col) {
				if(!in_array($col['name'], $show_cols)) continue;
				$col = $col['name'];
				if($this->columnLink[$col] && $mode == "html") {  //Add link for this column
					$render_data["data"][$row_counter][$col] = $this->getColumnLink($col, $data, $id, $value[$col]);
				} else {
					$render_data["data"][$row_counter][$col] = $value[$col];
				}

				//Find multiedit columns for this column (e.g. startdatum also edits enddatum)
				$cols = '';
				foreach($KOTA[$this->table]['_listview'] as $c) {
					if(in_array($col, explode(',', $c['multiedit']))) $cols = implode(';', explode(',', $c['multiedit']));
				}
				if(!$cols) $cols = $col;
				$render_data['meta']['id'][$row_counter][$coli] = $this->table.'|'.$id.'|'.$cols;

				$coli++;
			}

			//Set access rights
			$render_data['meta'][$row_counter] = $this->getActions($data[$id]);  //Don't pass $value, as this has already been processed
			//Set special class for this row
			$render_data["meta"][$row_counter]["rowclass"] = $this->getRowClass($data, $id);
			//Set id
			$render_data["meta"][$row_counter]["id"] = $id;
			//Set other data
			if(is_array($this->rowData[$id])) {
				foreach($this->rowData[$id] as $k => $v) {
					if(!$k) continue;
					$render_data['meta'][$row_counter][$k] = $v;
				}
			}

			foreach($this->editColumns as $type) {
				if(isset($this->listActions[$type]['additional_row_js'])) {
					$render_data['meta'][$row_counter]['additional_row_js_'.$type] = $this->map($this->listActions[$type]['additional_row_js'], $value, '###');
				}
			}

			//Add foreign_table columns here as they are not set in _listview or show_cols
			if($mode == 'xls') {
				foreach($KOTA[$this->table] as $kota_col) {
					$addRows = array();
					if(substr($kota_col, 0, 1) == '_') continue;
					if($kota_col['form']['type'] != 'foreign_table') continue;
					$ft_table = $kota_col['form']['table'];
					if(!$ft_table) continue;
					$ft_rows = db_select_data($ft_table, "WHERE `pid` = '$id'", '*', 'ORDER BY `sorting` ASC');
					$addRows = array();
					foreach($ft_rows as $ft_row) {
						if(!$this->disableKotaProcess) kota_process_data($ft_table, $ft_row, ($mode == 'xls' ? 'xls,list' : 'list'), $ft_log, $ft_row['id']);
						$addRow = array('');
						foreach($KOTA[$ft_table]['_listview'] as $ft_col) {
							$addRow[] = $ft_row[$ft_col['name']];
						}
						$addRows[] = $addRow;
					}
					if(is_array($addRows) && sizeof($addRows) > 0) {
						foreach($addRows as $addRow) {
							$row_counter++;
							$render_data['data'][$row_counter] = $addRow;
						}
					}
				}
			}


			$row_counter++;
		}

		if(is_array($this->colParams)) $render_data["meta"]["colparams"] = $this->colParams;

		//Set values for template
		$this->tmpl["list"]["data"] = $render_data["data"];
		$this->tmpl["list"]["meta"] = $render_data["meta"];
		$this->tmpl["list"]["actions"] = $this->listActions;

		//Render as HTML (default)
		if($mode == "html") {
			$this->smarty_assign();
			$smarty->display("ko_list2.tpl");
		}
		//Create XLS file and store filename in this->xls_file
		else if($mode == "xls") {
			$xls_data = $this->tmpl["list"]["data"];
			foreach($this->tmpl["list"]["header"] as $row) {
				$xls_header[] = $row["name"];
			}
			$this->xls_file = $ko_path."download/excel/".$file_prefix.strftime("%d%m%Y_%H%M%S", time()).".xlsx";
			$this->xls_file = ko_export_to_xlsx($xls_header, $xls_data, $this->xls_file, "");
		}
	}//render()




	function setRowData($data, $id='') {
		if($id) {
			$this->rowData[$id] = $data;
		} else {
			$this->rowData = $data;
		}
	}//setRowData()




	function map($string, $data, $prefix='') {
		if(!is_array($data)) return $string;

		//Prepare map array from raw data
		$map = array();
		foreach($data as $k => $v) {
			$map[$prefix.strtoupper($k).$prefix] = $v;
		}
		return str_replace(array_keys($map), array_values($map), $string);
	}//map()



	/**
	 * Get additional classes for single rows based on set conditions (see setRowClass())
	 */
	function getRowClass($data, $id) {
		$r = "";

		//Prepare map array from raw data
		foreach($data[$id] as $k => $v) {
			$map[strtoupper($k)] = $v;
		}

		foreach($this->rowClasses as $class => $cond) {
			$condition = strtr($cond, $map);
			if($condition != "" && eval($condition)) {
				$r .= " ".$class;
			}
		}
		return $r;
	}//getRowClass()



	/**
	 * Set additional classes for rows based on conditions
	 */
	function setRowClass($class, $cond) {
		$this->rowClasses[$class] = $cond;
	}//setRowClass()



	function setColParams($params) {
		$this->colParams = $params;
	}




	/**
	 * Sets a link for a certain column
	 */
	function setColumnLink($col, $link, $cond="") {
		$this->columnLink[$col] = $link;
		if($cond != "") {
			$this->columnLinkCondition[$col] = $cond;
		}
	}




	/**
	 * Generates a link for a value in a specified column
	 * col: Column to be linked
	 * data: Raw data
	 * id: ID of the current line within the data array
	 * value: the formated value to be linked (already preprocessed with kota_process_data(, , list, , )
	 */
	function getColumnLink($col, $data, $id, $value) {
		//Prepare map array from raw data
		foreach($data[$id] as $k => $v) {
			$map[strtoupper($k)] = $v;
		}

		//Generate link with formated value as text
		if(!isset($this->columnLinkCondition[$col]) ||
			($this->columnLinkCondition[$col] && eval(str_replace("@VALUE@", $value, $this->columnLinkCondition[$col])))
		) {
			$link = str_replace(array_keys($map), $map, $this->columnLink[$col]);
			return '<a href="'.$link.'">'.$value.'</a>';
		} else {
			return $value;
		}
	}//getColumnLink()



	/**
	 * Set edit, delete etc. buttons for a single row
	 */
	function getActions(&$data) {
		global $KOTA;

		$r = array();
		foreach($this->editColumns as $type) {
			if(is_array($this->manualAccess[$type])) {
				$r[$type] = $this->manualAccess[$type][$data['id']];
			} else {
				if($this->disableAccessRights) {
					$r[$type] = TRUE;
				} else {
					if(substr($this->chk_col, 0, 4) == 'ALL&') {
						$r[$type] = ($this->access['ALL'] >= $this->accessLevels[$type] || $this->access[$data[substr($this->chk_col, 4)]] >= $this->accessLevels[$type]);
					} else if($this->chk_col != '' && substr($this->accessLevels[$type], 0, 3) != 'ALL') {
						$r[$type] = ($this->access[$data[$this->chk_col]] >= $this->accessLevels[$type]);
					} else {
						if(substr($this->accessLevels[$type], 0, 3) == 'ALL') {
							$al = substr($this->accessLevels[$type], 3);
						} else {
							$al = $this->accessLevels[$type];
						}
						$r[$type] = ($this->access['ALL'] >= $al);
					}
				}

				//Check for access condition
				if(is_array($KOTA[$this->table]['_access']['condition'])) {
					if(isset($KOTA[$this->table]['_access']['condition'][$type])) {
						if(FALSE === eval(strtr($KOTA[$this->table]['_access']['condition'][$type], $data))) $r[$type] = FALSE;
					}
				} else if(isset($KOTA[$this->table]['_access']['condition'])) {
					if(FALSE === eval(strtr($KOTA[$this->table]['_access']['condition'], $data))) $r[$type] = FALSE;
				}
			}
		}
		return $r;
	}//getActions()




	function setManualAccess($type, $access) {
		$this->manualAccess[$type] = $access;
	}//setManualAccess()



	/**
	 * Supply arrays with access rights for the entries to be displayed
	 * Or set FALSE to disable check for access rights
	 */
	function setAccessRights($level, &$access, $chk_col='') {
		global $KOTA;

		if($level == FALSE) {
			$this->disableAccessRights = TRUE;
		} else {
			//Check for all rights to be higher than all access levels
			$all = TRUE;
			foreach($level as $k => $v) {
				if($access['ALL'] < $v) $all = FALSE;
			}
			if($all) {  //Disable access rights if all rights are higher than all levels
				$this->disableAccessRights = TRUE;
			} else {
				$this->accessLevels = $level;
				$this->access = $access;
			}
		}

		//Set column to be checked for
		$this->chk_col = $chk_col != '' ? $chk_col : $KOTA[$this->table]['_access']['chk_col'];
	}//setAccessRights()



	/**
	 * Supply actions to be performed for each editColumn
	 */
	function setActions($data) {
		foreach($this->editColumns as $type) {
			$this->listActions[$type] = $data[$type];
		}
	}//setActions()



	/**
	 * Define sorting
	 */
	function setSort($enable, $action="", $current="", $current_order="") {
		if($enable) {
			$this->sort = array("show" => $enable, "action" => $action, "akt" => $current, "akt_order" => $current_order);
		} else {
			$this->sort = array("show" => FALSE);
		}
	}//setSort()



	function setSortable() {
		$this->sortable = TRUE;
	}



	/**
	 * Assign all necessary values to smarty
	 */
	function smarty_assign() {
		global $smarty;

		foreach($this->tmpl as $key => $value) {
			$smarty->assign($key, $value);
		}
		$smarty->assign("edit_cols", $this->editColumns);

		//LL values
		$label = array(
			"alt_edit" => getLL("list_label_edit_entry"),
			"alt_check" => getLL("list_label_check_entry"),
			"alt_send" => getLL("list_label_send_entry"),
			"alt_undelete" => getLL("list_label_undelete_entry"),
			"alt_delete" => getLL("list_label_delete_entry"),
			"confirm_delete" => getLL("list_label_confirm_delete"),
			'kota_filter' => getLL('list_label_kota_filter'),
			'alt_tracking_add' => getLL('tracking_group_add'),
			'alt_tracking_show' => getLL('tracking_group_show'),
			'alt_mailing' => getLL('mailing_send_email'),
		);
		$smarty->assign("label", $label);

		if($this->disableListCheckAll) $smarty->assign("list_check_disabled", TRUE);

		//session id and module (for ajax calls)
		$smarty->assign("sesid", session_id());
		$smarty->assign("module", $this->module);

		//Help link
		$smarty->assign("help", ko_get_help($this->module, $_SESSION["show"]));
	}//smarty_assign()

}//kOOL_listview
?>
