<?php
/**
 * Date: 07.07.14
 * Time: 17:14
 */
class koFormLayoutEditor {

	const INSERT_FIRST = 0;
	const INSERT_LAST = 1;
	const INSERT_BEFORE = 2;
	const INSERT_AFTER = 3;


	public static function unsetField(&$KOTA, $table, $field, $propagate=FALSE, $addToIgnoreFields=TRUE) {
		if (!isset($KOTA[$table]['_form_layout'])) return;

		$formLayout = &$KOTA[$table]['_form_layout'];

		self::getFieldIds($KOTA, $table, $field, $tabId, $groupId, $rowId);

		$originalRowSize = sizeof($formLayout[$tabId]['groups'][$groupId]['rows'][$rowId]);
		$colIndex = array_search($field, array_keys($formLayout[$tabId]['groups'][$groupId]['rows'][$rowId]));

		unset($formLayout[$tabId]['groups'][$groupId]['rows'][$rowId][$field]);
		if (sizeof($formLayout[$tabId]['groups'][$groupId]['rows'][$rowId]) == 0) {
			unset($formLayout[$tabId]['groups'][$groupId]['rows'][$rowId]);
		} else if ($propagate) {
			self::sortRows($KOTA, $table, $tabId, $groupId);
			if ($colIndex == $originalRowSize - 1) {
				self::propagate($KOTA, $table, key(array_slice($formLayout[$tabId]['groups'][$groupId]['rows'][$rowId], $colIndex-1, 1, TRUE)));
			} else {
				self::propagate($KOTA, $table, key(array_slice($formLayout[$tabId]['groups'][$groupId]['rows'][$rowId], $colIndex, 1, TRUE)));
			}

		}
		if($addToIgnoreFields) $formLayout['_ignore_fields'][] = $field;
	}


	/**
	 * @param            $KOTA
	 * @param            $table
	 * @param            $field
	 * @param string     $tab
	 * @param string     $group
	 * @param int        $insertMode for now, only INSERT_AFTER works
	 * @param string     $ref
	 * @param bool|FALSE $propagate
	 * @param null       $layout
	 */
	public static function addField(&$KOTA, $table, $field, $insertMode=self::INSERT_AFTER, $ref="", $tab="", $group="", $propagate=FALSE, $layout=NULL) {
		if (!isset($KOTA[$table]['_form_layout'])) return;

		$formLayout = &$KOTA[$table]['_form_layout'];

		if ($layout === NULL) {
			$layout = array(
				'colspan' => NULL,
				'onNewRow' => FALSE,
				'onOwnRow' => FALSE
			);
		}

		$colspan = $layout['colspan'] === NULL ? $formLayout['_default_cols'] : $layout['colspan'];

		// Fallback insert mode
		if (!$field && $insertMode != self::INSERT_FIRST) $insertMode = self::INSERT_AFTER;
		if (in_array($insertMode, array(self::INSERT_BEFORE, self::INSERT_AFTER))) {
			self::getFieldIds($KOTA, $table, $ref, $tab, $group, $dummy);
		};
		if (!$tab || !$group) {
			$tab = 'general';
			$group = 'general';
			$insertMode = self::INSERT_LAST;
		}

		switch ($insertMode) {
			case self::INSERT_AFTER:

			break;
			case self::INSERT_LAST:
				self::getLastFieldIds($KOTA, $table, $tab, $group, $lastRowId, $lastFieldId);
				$ref = $lastFieldId;
			break;
		}

		self::sortRows($KOTA, $table, $tab, $group);

		$newEntry = array('colspan' => $colspan, 'on_new_row' => $layout['onNewRow'], 'on_own_row' => $layout['onOwnRow']);

		self::getFieldIds($KOTA, $table, $ref, $tab, $group, $rowId);
		$row = $formLayout[$tab]['groups'][$group]['rows'][$rowId];

		$newRow = array();
		foreach ($row as $f => $e) {
			$newRow[$f] = $e;
			if ($f == $ref) {
				$newRow[$field] = $newEntry;
			}
		}

		$formLayout[$tab]['groups'][$group]['rows'][$rowId] = $newRow;

		self::propagate($KOTA, $table, $field, !$propagate);
	}


	public static function unsetTab(&$KOTA, $table, $tab) {
		if (!isset($KOTA[$table]['_form_layout'])) return;

		foreach ($KOTA[$tab]['groups'] as $gk => &$groupLayout) {
			self::unsetGroup($KOTA, $table, $tab, $gk);
		}
		unset($KOTA[$table]['_form_layout'][$tab]);
	}
	public static function unsetGroup(&$KOTA, $table, $tab, $group) {
		if (!isset($KOTA[$table]['_form_layout'])) return;

		foreach ($KOTA[$tab]['groups'][$group]['rows'] as $rk => $rowLayout) {
			foreach ($rowLayout as $fieldName => $fieldLayout) {
				$KOTA[$table]['_form_layout']['_ignore_fields'][] = $fieldName;
			}
		}
		unset($KOTA[$table]['_form_layout'][$tab]['groups'][$group]);
	}



	public static function getFieldIds(&$KOTA, $table, $field, &$tabId, &$groupId, &$rowId) {
		if (!isset($KOTA[$table]['_form_layout'])) return;

		if (isset($KOTA[$table]['_form_layout'])) {
			if ($tabId) {
				if ($groupId) {
					foreach ($KOTA[$table]['_form_layout'][$tabId]['groups'][$groupId]['rows'] as $rowKey => $row) {
						if (array_key_exists($field, $row)) {
							$rowId = $rowKey;
							return TRUE;
						}
					}
				} else {
					foreach ($KOTA[$table]['_form_layout'][$tabId]['groups'] as $gk => &$groupLayout) {
						if (self::getFieldIds($KOTA, $table, $field, $tabId, $gk, $rowId)) {
							$groupId = $gk;
							return TRUE;
						}
					}
				}
			} else {
				foreach ($KOTA[$table]['_form_layout'] as $tk => &$tabLayout) {
					if (substr($tk, 0, 1) == '_') continue;
					if (self::getFieldIds($KOTA, $table, $field, $tk, $groupId, $rowId)) {
						$tabId = $tk;
						return TRUE;
					}
				}
			}
		}
		return FALSE;
	}



	public static function getLastFieldIds(&$KOTA, $table, $tab, $group, &$rowId, &$fieldId) {
		if (!isset($KOTA[$table]['_form_layout'])) return;

		$formLayout = &$KOTA[$table]['_form_layout'];

		self::sortRows($KOTA, $table, $tab, $group);
		end($formLayout[$tab]['groups'][$group]['rows']);
		$rowId = key($formLayout[$tab]['groups'][$group]['rows']);
		end($formLayout[$tab]['groups'][$group]['rows'][$rowId]);
		$fieldId = key($formLayout[$tab]['groups'][$group]['rows'][$rowId]);
	}



	public static function getNextRowId(&$KOTA, $table, $group, $rowId) {
		if (!isset($KOTA[$table]['_form_layout'])) return;

		$formLayout = &$KOTA[$table]['_form_layout'];

		self::sortRows($KOTA, $table, $group);
		$keys = array_keys($formLayout[$group]['rows']);
		$rowIndex = array_search($rowId, $keys);
		if ($rowIndex < sizeof($keys) - 1) {
			return $keys[$rowIndex++];
		} else {
			return FALSE;
		}
	}


	public static function getColspan($fieldEntry) {
		if (is_array($fieldEntry)) {
			return ($fieldEntry['colspan']);
		} else {
			return $fieldEntry;
		}
	}



	public static function propagate(&$KOTA, $table, $field, $onlyOneRow=FALSE) {
		if (!isset($KOTA[$table]['_form_layout'])) return;

		$formLayout = &$KOTA[$table]['_form_layout'];

		self::getFieldIds($KOTA, $table, $field, $tab, $group, $rowId);
		self::sortRows($KOTA, $table, $tab, $group);
		$rowIndex = array_search($rowId, array_keys($formLayout[$tab]['groups'][$group]['rows']));

		$prependRows = array_slice($formLayout[$tab]['groups'][$group]['rows'], 0, $rowIndex, TRUE);
		$appendRows = array();
		if ($onlyOneRow) {
			$workingRows = array($formLayout[$tab]['groups'][$group]['rows'][$rowId]);
			$appendRows = array_slice($formLayout[$tab]['groups'][$group]['rows'], $rowIndex + 1, null, TRUE);
		} else {
			$workingRows = array_slice($formLayout[$tab]['groups'][$group]['rows'], $rowIndex, null, TRUE);
		}

		$allFields = array();
		foreach ($workingRows as $workingRow) {
			foreach ($workingRow as $f => $e) {
				$allFields[$f] = $e;
			}
		}

		$newRows = array();
		$row = array();
		$rowWidth = 0;
		foreach ($allFields as $f => $e) {
			$colspan = self::getColspan($e);
			$colspan = $colspan ? $colspan : $formLayout['_default_cols'];
			$onOwnRow = $e['on_own_row'];
			$onNewRow = $e['on_new_row'];

			$fits = ($rowWidth == 0) || (!$onOwnRow && !$onNewRow && $rowWidth + $colspan <= 12);
			if (!$fits) {
				$newRows[] = $row;
				$row = array();
				$rowWidth = 0;
			}
			$row[$f] = $e;
			$rowWidth += $colspan;

			if ($onOwnRow) {
				$newRows[] = $row;
				$row = array();
				$rowWidth = 0;
			}
		}
		if (sizeof($row) > 0) $newRows[] = $row;

		$allRows = array();
		$currRowIndex = 0;
		foreach ($prependRows as $r) $allRows[++$currRowIndex] = $r;
		foreach ($newRows as $r) $allRows[++$currRowIndex] = $r;
		foreach ($appendRows as $r) $allRows[++$currRowIndex] = $r;

		$formLayout[$tab]['groups'][$group]['rows'] = $allRows;
	}

	public static function collapse(&$KOTA, $table, $modes="save,sep") {
		if (!isset($KOTA[$table]['_form_layout'])) return;

		$removeFields = array();

		$modes = explode(',', $modes);
		$skip = array();
		foreach ($modes as $mode) {
			$skip[$mode] = FALSE;
		}

		$layout = &$KOTA[$table]['_form_layout'];
		foreach ($layout as $tabK => &$tab) {
			foreach ($tab['groups'] as $groupK => &$group) {
				foreach ($group['rows'] as $rowK => &$row) {
					foreach ($row as $col => $size) {
						$found = FALSE;
						foreach ($modes as $mode) {
							if (substr($col, 0, strlen($mode)+1) == "_{$mode}") {
								if ($skip[$mode]) $removeFields[] = $col;
								else $skip[$mode] = TRUE;
								$found = TRUE;
								break;
							}
						}
						if (!$found) {
							foreach ($modes as $mode) {
								$skip[$mode] = FALSE;
							}
						}
					}
				}
			}
		}

		foreach ($removeFields as $f) {
			self::unsetField($KOTA, $table, $f);
		}
	}





	// SORTING
	public static function sortAll(&$KOTA, $table) {
		if (!isset($KOTA[$table]['_form_layout'])) return;

		self::sortTabs($KOTA, $table);
		self::sortGroups($KOTA, $table);
		self::sortRows($KOTA, $table);
	}

	public static function sortTabs(&$KOTA, $table) {
		if (!isset($KOTA[$table]['_form_layout'])) return;

		$cmpFcn = function ($a, $b) {return $b["sorting"] < $a["sorting"];};
		uasort($KOTA[$table]['_form_layout'], $cmpFcn);
	}

	public static function sortGroups(&$KOTA, $table, $tab=NULL) {
		if (!isset($KOTA[$table]['_form_layout'])) return;

		$cmpFcn = function ($a, $b) {return $b["sorting"] < $a["sorting"];};
		if ($tab === NULL) {
			foreach ($KOTA[$table]['_form_layout'] as $k => $t) {
				if (substr($k, 0, 1) == '_') continue;

				uasort($KOTA[$table]['_form_layout'][$k]['groups'], $cmpFcn);
			}
		} else {
			uasort($KOTA[$table]['_form_layout'][$tab], $cmpFcn);
		}
	}

	public static function sortRows(&$KOTA, $table, $tab=NULL, $group=NULL) {
		if (!isset($KOTA[$table]['_form_layout'])) return FALSE;

		if ($tab === NULL) {
			foreach ($KOTA[$table]['_form_layout'] as $tk => $t) {
				if (substr($tk, 0, 1) == '_') continue;
				foreach ($t['groups'] as $gk => $g) {
					ksort($KOTA[$table]['_form_layout'][$tk]['groups'][$gk]['rows']);
				}
			}
		} else if ($tab !== NULL && $group === NULL) {
			foreach ($KOTA[$table]['_form_layout'][$tab]['groups'] as $gk => $g) {
				ksort($KOTA[$table]['_form_layout'][$tab]['groups'][$gk]['rows']);
			}
		} else if ($tab !== NULL && $group !== NULL) {
			ksort($KOTA[$table]['_form_layout'][$tab]['groups'][$group]['rows']);
		} else {
			return FALSE;
		}
		return TRUE;
	}
}
