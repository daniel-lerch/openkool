<?php
/**
TODO: make it work even without ';' delimiters or at least warn about that
TODO: better parse error reporting
TODO: accept empty datetime value and 0000-00-00 00:00:00 are equal, similar with date and time, also enum('0','1') [default 0], what's with floats?(float(10,2) NOT NULL default '0.00'); text,mediumtext,etc;
TODO: option to add database name with dot before the table names
TODO: add option "order does matter"
DONE: breaks table definition on commas and brackets, not newlines
DONE: handles `database`.`table` in CREATE TABLE string (but does not add database to result sql for a while - and if it
should? as same tables struct in 2 DBs compared is also a case)
DONE: handles double (and more) spaces in CREATE TABLE string
DONE: add filter option (fields: MODIFY, ADD, DROP, tables: CREATE, DROP)
DONE: make it work also with comments
DONE: move all options to $this->config
 */
/**
 * The class provides ability to compare 2 database structure dumps and compile a set of sql statements to update
 * one database to make it structure identical to another.
 *
 * @author Kirill Gerasimenko <ger.kirill@gmail.com>
 *
 * The input for the script could be taken from the phpMyAdmin structure dump, or provided by some custom code
 * that uses 'SHOW CREATE TABLE' query to get database structure table by table.
 * The output is either array of sql statements suitable for executions right from php or a string where the
 * statements are placed each at new line and delimited with ';' - suitable for execution from phpMyAdmin SQL
 * page.
 * The resulting sql may contain queries that aim to:
 * Create missing table (CREATE TABLE query)
 * Delete table which should not longer exist (DROP TABLE query)
 * Update, drop or add table field or index definition (ALTER TABLE query)
 *
 * Some features:
 * - AUTO_INCREMENT value is ommited during the comparison and in resulting CREATE TABLE sql
 * - fields with definitions like "(var)char (255) NOT NULL default ''" and "(var)char (255) NOT NULL" are treated
 *   as equal, the same for (big|tiny)int NOT NULL default 0;
 * - IF NOT EXISTS is automatically added to the resulting sql CREATE TABLE statement
 * - fields updating queries always come before key modification ones for each table
 * Not implemented:
 * - The class even does not try to insert or re-order fields in the same order as in the original table.
 *   Does order matter?
 * IMPORTANT!!! Class will not handle a case when the field was renamed. It will generate 2 queries - one to drop
 * the column with the old name and one to create column with the new name, so if there is a data in the dropped
 * column, it will be lost.
 * Usage example:
$updater = new dbStructUpdater();
$res = $updater->getUpdates($struct1, $struct2);
-----
$res == array (
[0]=>"ALTER TABLE `b` MODIFY `name` varchar(255) NOT NULL",
...
)
 */
class dbStructUpdater
{
	var $sourceStruct = '';//structure dump of the reference database
	var $destStruct = '';//structure dump of database to update
	var $config = array();//updater configuration
	var $updateActions = array();//Allowed actions to be performed (set in config)
	var $typesUpper = array();
	var $typesLower = array();

	/**
	 * Constructor
	 * @access public
	 */
	function __construct($_config) {
		$this->init($_config);
	}

	function init($_config) {
		global $EXCLUDE_FROM_MOD, $TABLE_KEYS;

		//table operations: create, dropTable; field operations: add, drop, modify
		$this->config['updateTypes'] = array('create', 'dropTable', 'add', 'drop', 'modify');
		//ignores default part in cases like (var)char NOT NULL default '' upon the	comparison
		$this->config['varcharDefaultIgnore'] = true;
		//the same for int NOT NULL default 0
		$this->config['intDefaultIgnore'] = true;
		//ignores table autoincrement field value, also remove AUTO_INCREMENT value from the create query if exists
		$this->config['ignoreIncrement'] = true;
		//add 'IF NOT EXIST' to each CREATE TABLE query
		$this->config['forceIfNotExists'] = true;
		//remove 'IF NOT EXIST' if already exists CREATE TABLE dump
		$this->config['ingoreIfNotExists'] = false;
		//Exclude fields: comma separated table.field
		$this->config['excludeFields'] = array();
		//Set default lengths of different field types
		$this->config['defaultNumberLengths'] = array(
			'bigint' => '20',
			'int' => '11',
			'mediumint' => '8',
			'smallint' => '6',
			'tinyint' => '3',
		);
		//Define tables that should always contain certain columns of other tables
		$this->config['modTables'] = array(
			'ko_leute_mod' => array(
				'baseTable' => 'ko_leute',
				'exclude' => array_merge($EXCLUDE_FROM_MOD['ko_leute_mod'], array()),
			),
			'ko_reservation_mod' => array(
				'baseTable' => 'ko_reservation',
				'exclude' => array_merge($EXCLUDE_FROM_MOD['ko_reservation_mod'], array()),
			),
			'ko_event_mod' => array(
				'baseTable' => 'ko_event',
				'exclude' => array_merge($EXCLUDE_FROM_MOD['ko_event_mod'], array()),
			),
		);
		//Define keys of insert tables
		$this->insertTables = $TABLE_KEYS;

		$this->typesUpper = array(' VARCHAR', ' TINYINT', ' SMALLINT', ' MEDIUMINT', ' BIGINT', ' INT', ' ENUM', ' DATE', ' DATETIME', ' DECIMAL');
		$upperTypes = $this->typesUpper;
		$this->typesLower = array_map(function($e)use($upperTypes){return strtolower($e);}, $upperTypes);


		$this->setConfig($_config);

		$this->excludeFields = $this->config['excludeFields'];

		$allowedActions = array('create', 'dropTable', 'add', 'drop', 'modify');
		if(is_array($this->config['updateTypes'])) {
			$updateActions = $this->config['updateTypes'];
		} else {
			$updateActions = array_map('trim', explode(',', $this->config['updateTypes']));
		}
		$this->updateActions = array_intersect($updateActions, $allowedActions);
	}

	/**
	 * merges current updater config with the given one
	 * @param array $config new configuration values
	 */
	function setConfig($config=array())
	{
		if (is_array($config))
		{
			$this->config = array_merge($this->config, $config);
		}
	}

	function getAllSQL($dest) {
		$this->destStruct = $dest;
		$destTabNames = $this->getTableList($this->destStruct);

		$src = '';
		foreach ($destTabNames as $t) $src .= $this->getTableStruct($t) . ";\n";

		return (array('inserts' => $this->getInserts($dest), 'updates' => $this->getUpdates($src, $dest), 'alters' => $this->getAlters($dest)));
	}

	const UPDATES = 1;
	const UPDATES_AS_STRING = 2;
	const TABLE_DEFINITION = 4;
	function getModTabUpdates($mode=NULL, $useDB=TRUE, $struct=NULL) {
		if ($mode==NULL) $mode = $this::UPDATES;

		$rv = array();
		foreach ($this->config['modTables'] as $modTable => $settings) {
			$t = $useDB ? $this->getTableStruct($settings['baseTable']) : $this->getTabSql($struct, $settings['baseTable']);
			$tm = $useDB ? $this->getTableStruct($modTable) : $this->getTabSql($struct, $modTable);

			//print($t."\n");
			//print("-------------->>>>>>>>>>>>>>>>\n");
			//print($tm."\n");
			//print("\n\n----------------------------\n\n");

			assert($t != '' && $tm != '');
			// go through table cols and extract SQL of those that should be present in table_mod
			$t_cols = array_map(function($el) {$el = trim($el); if (substr($el, -1) == ',') return substr($el, 0, -1); else return $el;}, explode("\n", $t));
			$new_t_cols = array();
			foreach ($t_cols as $k => $col) {
				if ($k == 0 || $k == sizeof($t_cols) - 1) continue;
				if (substr($col, 0, 3) == "KEY") continue;
				if (substr($col, 0, 11) == "PRIMARY KEY") continue;
				if (substr($col, 0, 12) == "FULLTEXT KEY") continue;
				if (sizeof($settings['exclude']) > 0 && preg_match('/^`('.implode('|', $settings['exclude']).')`/', $col)) continue;

				$match = preg_match('/`([^`]*)`/', $col, $matches);
				assert($match);

				$new_t_cols[$matches[1]] = $col;
			}
			// go through mod cols and seperate normal cols from special cols
			$tm_cols = array_map(function($el) {$el = trim($el); if (substr($el, -1) == ',') return substr($el, 0, -1); else return $el;}, explode("\n", $tm));
			$new_tm_cols = array();
			$new_tm_cols_append = array();
			foreach ($tm_cols as $k => $col) {
				if ($k == 0 || $k == sizeof($tm_cols) - 1) continue;
				if (substr($col, 0, 3) == "KEY") $new_tm_cols_append[] = $col;
				else if (substr($col, 0, 11) == "PRIMARY KEY") $new_tm_cols_append[] = $col;
				else if (substr($col, 0, 12) == "FULLTEXT KEY") $new_tm_cols_append[] = $col;
				//else if (strpos($col, "`_") !== FALSE) $new_tm_cols_append[] = $col;
				else {
					$match = preg_match('/`([^`]*)`/', $col, $matches);
					assert($match);
					$new_tm_cols[$matches[1]] = $col;
				}
			}
			// merge the cols from table into those of table_mod
			foreach ($new_t_cols as $k => $col) {
				$new_tm_cols[$k] = $col;
			}
			$new_tm_cols = $new_tm_cols + $new_tm_cols_append;
			// rebuild create table statement of table_mod and add it to the source code
			$dest = "\n" . $tm_cols[0] . ",\n  " . implode(",\n  ", $new_tm_cols) . "\n" . $tm_cols[sizeof($tm_cols) - 1] . ";\n";
			$src = $tm;

			switch ($mode) {
				case $this::UPDATES:
					$rv = array_merge($rv, $this->getUpdates($src, $dest, FALSE));
					break;
				case $this::UPDATES_AS_STRING:
					$rv = array_merge($rv, $this->getUpdates($src, $dest, TRUE));
					break;
				case $this::TABLE_DEFINITION:
					$rv[] = $dest;
			}
		}
		return $mode > $this::UPDATES ? implode("\n", $rv) : $rv;
	}

	function getTableStruct($tableName) {
		$res = mysqli_query(db_get_link(), 'SHOW CREATE TABLE `'.$tableName.'`');
		$res = mysqli_fetch_assoc($res);
		//Remove collation
		if(FALSE !== strpos($res['Create Table'], 'COLLATE')) {
			$res['Create Table'] = preg_replace('/ COLLATE\s+\w+/', '', $res['Create Table']);
		}
		return $res['Create Table'];
	}

	/**
	 * Returns array of update SQL with default options, $source, $dest - database structures
	 * @access public
	 * @param string $source structure dump of database to update
	 * @param string $dest structure dump of the reference database
	 * @param bool $asString if true - result will be a string, otherwise - array
	 * @return array|string update sql statements - in array or string (separated with ';')
	 */
	function getUpdates($source, $dest, $asString=false, $debug=FALSE)
	{
		$result = $asString?'':array();
		$compRes = $this->compare($source, $dest);
		if (empty($compRes))
		{
			return $result;
		}
		$compRes = $this->filterDiffs($compRes);
		if (empty($compRes))
		{
			return $result;
		}
		$result = $this->getDiffSql($compRes, $debug);
		if ($asString)
		{
			$result = implode(";\r\n", $result).';';
		}
		return $result;
	}



	function getInserts($dest) {
		global $UPDATER_CONF;
		$inserts = array();
		foreach(preg_split("/(?<=;)\s*\n/", $dest) as $line) {
			if(preg_match('/^(INSERT INTO `?(\w+)`?(?:\s*\([^\)]+\))?\s*VALUES)\s*?\((.*)\);$/s', $line, $matches)) {
				$table = $matches[2];

				if(in_array($table, array_keys($this->insertTables))) {
					$columnNames = array();
					$tableLines = $this->splitTabSql($this->getTabSql($this->destStruct, $table, true));
					foreach ($tableLines as $tableLine) {
						$lineInfo = $this->processLine($tableLine);
						if (substr($lineInfo['key'], 0, 2) == '!`') $columnNames[] = substr($lineInfo['key'], 2, -1);
					}

					$insertValues = [];
					foreach(preg_split('/\)\s*,\s*\(/s',$matches[3]) as $values) {

						$result = mysqli_query(db_get_link(),"SELECT ".$values);
						$vals = $result->fetch_row();

						// create a map from column names to values (for identity check below)
						if (preg_match('/^INSERT INTO `?(\w+)`?\s*\((.+)\)\s*VALUES\s*\((.*)\);$/s', $line, $m)) {
							$keys = array_map(function($e){return substr(trim($e), 1, -1);}, explode(',', $m[2]));
						} else {
							$tableExists = db_query("SHOW TABLES LIKE '{$table}';");
							if (sizeof($tableExists) > 0) {
								$keys = array();
								foreach (db_get_columns($table) as $field) {
									$keys[] = $field['Field'];
								}
								$keys = array_unique(array_merge($keys, $columnNames));
							} else {
								$keys = $columnNames;
							}
						}
						$keys = array_slice($keys,0,count($vals));
						$map = array_combine($keys,$vals);

						$q = implode(' AND ',array_map(function($key) use($map) {
							return "`".$key."` = '".mysqli_real_escape_string(db_get_link(),$map[$key])."'";
						},$this->insertTables[$table]['keys']));
						$checkQuery = "SELECT * FROM `$table` WHERE ".$q;
						$resultCheck = mysqli_query(db_get_link(), $checkQuery);

						if(mysqli_num_rows($resultCheck) == 0) {
							$insertValues[] = $values;
						} else if(!empty($UPDATER_CONF['updateFields'][$table])) {
							$updateFields = $UPDATER_CONF['updateFields'][$table];
							if($updateFields === '*') {
								$updateFields = $keys;
							}

							$updateFields = array_filter($keys,function($field) use($UPDATER_CONF,$table) {
								return ($UPDATER_CONF['updateFields'][$table] === '*' || in_array($field,$UPDATER_CONF['updateFields'][$table])) && !$this->isAutoIncrement($table,$field);
							});

							//Check for excludeFields
							$doUpdate = TRUE;
							foreach($updateFields as $field) {
								if(in_array($table.'.'.$field.'.'.$map[$field], $this->excludeFields)) $doUpdate = FALSE;
							}

							if($updateFields && $doUpdate) {
								$secondCheckWhere = implode(' AND ',array_map(function($field) use($map) {
									return '`'.$field."`='".mysqli_real_escape_string(db_get_link(),$map[$field])."'";
								},$updateFields));
								$result = mysqli_query(db_get_link(),'SELECT * FROM `'.$table.'` WHERE '.$secondCheckWhere);
								if(mysqli_num_rows($result) == 0) {
									$currentRow = mysqli_fetch_assoc($resultCheck);
									$updateFields = array_filter($updateFields,function($field) use($currentRow,$map) {
										return !isset($currentRow[$field]) || $currentRow[$field] != $map[$field];
									});
									if($updateFields) {
										$inserts[] = "UPDATE `".$table."` SET ".implode(', ',array_map(function($field) use($map) {
											return "`".$field."` = '".mysqli_real_escape_string(db_get_link(),$map[$field])."'";
										},$updateFields))." WHERE ".$q;
									}
								}
							}
						}
					}
					if($insertValues) {
						$inserts[] = $matches[1].' ('.implode('), (',$insertValues).');';
					}
				}
			}
		}
		return $inserts;
	}//getInserts()

	protected $autoIncrementColumns = [];

	function isAutoIncrement($table,$column) {
		if(!isset($this->autoIncrementColumns[$table])) {
			$res = mysqli_query(db_get_link(),"SHOW COLUMNS FROM `".$table."` WHERE FIND_IN_SET('auto_increment',extra)");
			$cols = array();
			while($row = $res->fetch_assoc()) {
				$cols[] = $row['Field'];
			}
			$this->autoIncrementColumns[$table] = $cols;
		}
		return in_array($column,$this->autoIncrementColumns[$table]);
	}


	function getAlters($dest) {
		$alters = array();
		foreach(explode("\n", $dest) as $line) {//
			if(substr($line, 0, 12) != 'ALTER TABLE ') continue;


			if(preg_match('/ALTER TABLE `?(\w+)`? (ADD|ADD COLUMN) `?(\w+)`? (.*);$/', $line, $m)) {
				$line = substr($line, 12);

				list($all, $table, $mode, $col, $dummy) = $m;
				switch ($mode) {
					case 'ADD':
					case 'ADD COLUMN':
						if ($col == 'KEY') $type = 'KEY';
						else if ($col == 'PRIMARY KEY') $type = 'PRIMARY KEY';
						else if ($col == 'FULLTEXT KEY') $type = 'FULLTEXT KEY';
						else $type = 'FIELD';

						$orig = $this->getTableStruct($table);
						$orig_ = explode("\n", "{$orig};\n");
						$orig = array();
						$lastCol = '';
						$origAfter = '';
						foreach ($orig_ as $l) {
							$l = trim($l);
							if (substr($l, 0, 1) != "`") {
								$orig[] = $l;
							} else {
								$orig[] = (substr($l,-1)==','?substr($l,0,-1):$l) . ($lastCol?" AFTER `{$lastCol}`":'') . (substr($l,-1)==','?',':'');
								$currentCol = preg_replace('/^`(\w+)` .*$/', '$1', $l);
								if ($currentCol == $col) $origAfter = $lastCol;
								$lastCol = $currentCol;
							}
						}
						$orig = implode("\n", $orig);

						if ($type == 'FIELD') {
							$line = "`{$col}` $dummy";
							if (strpos($line, 'AFTER') === FALSE && $origAfter) {
								$line .= " AFTER `{$origAfter}`";
							}
						} else {
							$line = "{$col} $dummy";
						}
						$line = $this->processLine($line);

						$new_ = explode("\n", $orig);
						$new = array();
						$found = FALSE;
						foreach ($new_ as $k => $l) {
							if (substr($l, 0, 1) == ')' && !$found) {
								$new[sizeof($new)-1] .= ',';
								$new[] = $line['line'];
							}
							if (substr($l, 0, 3) == "KEY" || substr($l, 0, 11) == "PRIMARY KEY" || substr($l, 0, 12) == "FULLTEXT KEY") {
								if (!$found && $type == 'FIELD') {
									$new[] = $line['line'].',';
									$found = TRUE;
									$new[] = $l;
								} else if (!$found && $type != 'FIELD' && strtolower(substr($l, 0, strlen($line['key']))) == $line['key']) {
									$new[] = $line['line'] . (substr($l, -1)==','?',':'');
									$found = TRUE;
								} else {
									$new[] = $l;
								}
							} else if ($type == 'FIELD' && substr($l, 0, strlen("`{$col}`")) == "`{$col}`") {
								$new[] = $line['line'] . (substr($l, -1)==','?',':'');
								$found = TRUE;
							} else {
								$new[] = $l;
							}
						}
						$new = implode("\n", $new);

						$alters = array_merge($alters, $this->getUpdates($orig, $new));

						break;
				}
			}
		}
		return $alters;
	}


	/**
	 * Filters comparison result and lefts only sync actions allowed by 'updateTypes' option
	 */
	function filterDiffs($compRes)
	{
		$result = array();
		foreach($compRes as $table=>$info)
		{
			if ($info['sourceOrphan'])
			{
				if (in_array('dropTable', $this->updateActions))
				{
					$result[$table] = $info;
				}
			}
			elseif ($info['destOrphan'])
			{
				if (in_array('create', $this->updateActions))
				{
					$result[$table] = $info;
				}
			}
			elseif($info['differs'])
			{
				//Check for excludeFields
				foreach($info['differs'] as $key => $_info) {
					$field = '';
					if(isset($_info['source'])) {
						$line = str_replace('`', '', $_info['source']);
						$field = substr($line, 0, strpos($line, ' '));
					}
					if(!$field && isset($_info['dest'])) {
						$line = str_replace('`', '', $_info['dest']);
						$field = substr($line, 0, strpos($line, ' '));
					}
					if($field != '' && in_array($table.'.'.$field, $this->excludeFields)) {
						unset($info['differs'][$key]);
					}
				}

				$resultInfo = $info;
				unset($resultInfo['differs']);
				foreach ($info['differs'] as $diff)
				{
					if (empty($diff['dest']) && in_array('add', $this->updateActions))
					{
						$resultInfo['differs'][] = $diff;
					}
					elseif (empty($diff['source']) && in_array('drop', $this->updateActions))
					{
						$resultInfo['differs'][] = $diff;
					}
					elseif(in_array('modify', $this->updateActions))
					{
						$resultInfo['differs'][] = $diff;
					}
				}
				if (!empty($resultInfo['differs']))
				{
					$result[$table] = $resultInfo;
				}
			}
		}
		return $result;
	}

	/**
	 * Gets structured general info about the databases diff :
	 * array(sourceOrphans=>array(...), destOrphans=>array(...), different=>array(...))
	 */
	function getDiffInfo($compRes)
	{
		if (!is_array($compRes))
		{
			return false;
		}
		$result = array('sourceOrphans'=>array(), 'destOrphans'=>array(), 'different'=>array());
		foreach($compRes as $table=>$info)
		{
			if ($info['sourceOrphan'])
			{
				$result['sourceOrphans'][] = $table;
			}
			elseif ($info['destOrphan'])
			{
				$result['destOrphans'][] = $table;
			}
			else
			{
				$result['different'][] = $table;
			}
		}
		return $result;
	}

	/**
	 * Makes comparison of the given database structures, support some options
	 * @access private
	 * @param string $source and $dest are strings - database tables structures
	 * @return array
	 * - table (array)
	 *		- destOrphan (boolean)
	 *		- sourceOrphan (boolean)
	 *		- differs (array) OR (boolean) false if no diffs
	 *			- [0](array)
	 *				- source (string) structure definition line in the out-of-date table
	 *				- dest (string) structure definition line in the reference table
	 *			- [1](array) ...
	 */
	function compare($source, $dest)
	{
		$this->sourceStruct = $source;
		$this->destStruct = $dest;

		$result = array();
		$destTabNames = $this->getTableList($this->destStruct);
		$sourceTabNames = $this->getTableList($this->sourceStruct);

		$common = array_intersect($destTabNames, $sourceTabNames);
		$destOrphans = array_diff($destTabNames, $common);
		$sourceOrphans = array_diff($sourceTabNames, $common);
		$all = array_unique(array_merge($destTabNames, $sourceTabNames));
		sort($all);
		foreach ($all as $tab)
		{
			$info = array('destOrphan'=>false, 'sourceOrphan'=>false, 'differs'=>false);
			if(in_array($tab, $destOrphans))
			{
				$info['destOrphan'] = true;
			}
			elseif (in_array($tab, $sourceOrphans))
			{
				$info['sourceOrphan'] = true;
			}
			else
			{
				$destSql = $this->getTabSql($this->destStruct, $tab, true);
				$sourceSql = $this->getTabSql($this->sourceStruct, $tab, true);
				$diffs = $this->compareSql($sourceSql, $destSql);
				if ($diffs===false)
				{
					trigger_error('[WARNING] error parsing definition of table "'.$tab.'" - skipped');
					continue;
				}
				elseif (!empty($diffs))//not empty array
				{
					$info['differs'] = $diffs;
				}
				else continue;//empty array
			}
			$result[$tab] = $info;
		}
		return $result;
	}

	/**
	 * Retrieves list of table names from the database structure dump
	 * @access private
	 * @param string $struct database structure listing
	 */
	function getTableList($struct)
	{
		$result = array();
		if (preg_match_all('/CREATE(?:\s*TEMPORARY)?\s*TABLE\s*(?:IF NOT EXISTS\s*)?(?:`?(\w+)`?\.)?`?(\w+)`?/i', $struct, $m))
		{
			foreach($m[2] as $match)//m[1] is a database name if any
			{
				$result[] = $match;
			}
		}
		return $result;
	}

	/**
	 * Retrieves table structure definition from the database structure dump
	 * @access private
	 * @param string $struct database structure listing
	 * @param string $tab table name
	 * @param bool $removeDatabase - either to remove database name in "CREATE TABLE database.tab"-like declarations
	 * @return string table structure definition
	 */
	function getTabSql($struct, $tab, $removeDatabase=true)
	{
		$result = '';
		/* create table should be single line in this case*/
		//1 - part before database, 2-database name, 3 - part after database
		if (preg_match('/(CREATE(?:\s*TEMPORARY)?\s*TABLE\s*(?:IF NOT EXISTS\s*)?)(?:`?(\w+)`?\.)?(`?('.$tab.')`?(\W|$))/i', $struct, $m, PREG_OFFSET_CAPTURE))
		{
			$tableDef = $m[0][0];
			$start = $m[0][1];
			$database = $m[2][0];
			$offset = $start+strlen($m[0][0]);
			$end = $this->getDelimPos($struct, $offset);
			if ($end === false)
			{
				$result = substr($struct, $start);
			}
			else
			{
				$result = substr($struct, $start, $end-$start);//already without ';'
			}
		}
		$result = trim($result);
		if ($database && $removeDatabase)
		{
			$result = str_replace($tableDef, $m[1][0].$m[3][0], $result);
		}
		return $result;
	}

	/**
	 * Splits table sql into indexed array
	 *
	 */
	function splitTabSql($sql)
	{
		$result = array();
		//find opening bracket, get the prefix along with it
		$openBracketPos = $this->getDelimPos($sql, 0, '(');
		if ($openBracketPos===false)
		{
			trigger_error('[WARNING] can not find opening bracket in table definition');
			return false;
		}
		$prefix = substr($sql, 0, $openBracketPos+1);//prefix can not be empty, so do not check it, just trim
		$result[] = trim($prefix);
		$body = substr($sql, strlen($prefix));//fields, indexes and part after closing bracket
		//split by commas, get part by part
		while(($commaPos = $this->getDelimPos($body, 0, ',', true))!==false)
		{
			$part = trim(substr($body, 0, $commaPos+1));//read another part and shorten $body
			if ($part)
			{
				$result[] = $part;
			}
			$body = substr($body, $commaPos+1);
		}
		//here we have last field (or index) definition + part after closing bracket (ENGINE, ect)
		$closeBracketPos = $this->getDelimRpos($body, 0, ')');
		if ($closeBracketPos===false)
		{
			trigger_error('[WARNING] can not find closing bracket in table definition');
			return false;
		}
		//get last field / index definition before closing bracket
		$part = substr($body, 0, $closeBracketPos);
		$result[] = trim($part);
		//get the suffix part along with the closing bracket
		$suffix = substr($body, $closeBracketPos);
		$suffix = trim($suffix);
		if ($suffix)
		{
			$result[] = $suffix;
		}
		return $result;
	}

	/**
	 * returns array of fields or keys definitions that differs in the given tables structure
	 * @access private
	 * @param string $sourceSql table structure
	 * @param string $destSql right table structure
	 * supports some $options
	 * @return array
	 * 	- [0]
	 * 		- source (string) out-of-date table field definition
	 * 		- dest (string) reference table field definition
	 * 	- [1]...
	 */
	function compareSql($sourceSql, $destSql)//$sourceSql, $destSql
	{
		$result = array();
		//split with comma delimiter, not line breaks
		$sourceParts =  $this->splitTabSql($sourceSql);
		if ($sourceParts===false)//error parsing sql
		{
			trigger_error('[WARNING] error parsing source sql');
			return false;
		}
		$destParts = $this->splitTabSql($destSql);
		if ($destParts===false)
		{
			trigger_error('[WARNING] error parsing destination sql');
			return false;
		}
		$sourcePartsIndexed = array();
		$destPartsIndexed = array();
		foreach($sourceParts as $line)
		{
			$lineInfo = $this->processLine($line);
			if (!$lineInfo) continue;
			$sourcePartsIndexed[$lineInfo['key']] = $lineInfo['line'];
		}
		foreach($destParts as $line)
		{
			$lineInfo = $this->processLine($line);
			if (!$lineInfo) continue;
			$destPartsIndexed[$lineInfo['key']] = $lineInfo['line'];
		}
		$sourceKeys = array_keys($sourcePartsIndexed);
		$destKeys = array_keys($destPartsIndexed);
		$all = array_unique(array_merge($sourceKeys, $destKeys));
		sort($all);//fields first, then indexes - because fields are prefixed with '!'
		foreach ($all as $key)
		{
			$info = array('source'=>'', 'dest'=>'');
			$inSource= in_array($key, $sourceKeys);
			$inDest= in_array($key, $destKeys);
			$sourceOrphan = $inSource && !$inDest;
			$destOrphan = $inDest && !$inSource;
			$different =  $inSource && $inDest &&
				strcasecmp($this->normalizeString($destPartsIndexed[$key]), $this->normalizeString($sourcePartsIndexed[$key]));
			if ($sourceOrphan)
			{
				$info['source'] = $sourcePartsIndexed[$key];
			}
			elseif ($destOrphan)
			{
				$info['dest'] = $destPartsIndexed[$key];
			}
			elseif ($different)
			{
				$info['source'] = $sourcePartsIndexed[$key];
				$info['dest'] = $destPartsIndexed[$key];
			}
			else continue;
			$result[] = $info;
		}
		return $result;
	}

	/**
	 * Transforms table structure defnition line into key=>value pair where the key is a string that uniquely
	 * defines field or key desribed
	 * @access private
	 * @param string $line field definition string
	 * @return array array with single key=>value pair as described in the description
	 * implements some options
	 */
	function processLine($line)
	{
		$options = $this->config;
		$result = array('key'=>'', 'line'=>'');
		$line = rtrim(trim($line), ", \t\n\r\0\x0B");
		if (preg_match('/^(CREATE\s+TABLE)|(\) ENGINE=)/i', $line))//first or last table definition line
		{
			return false;
		}
		//if (preg_match('/^(PRIMARY KEY)|(((UNIQUE )|(FULLTEXT ))?KEY `?\w+`?)/i', $line, $m))//key definition
		if (preg_match('/^(PRIMARY\s+KEY)|(((UNIQUE\s+)|(FULLTEXT\s+))?KEY\s+`?\w+`?)/i', $line, $m))//key definition
		{
			$key = $m[0];
		}
		elseif (preg_match('/^`?\w+`?/i', $line, $m))//field definition
		{
			$key = '!'.$m[0];//to make sure fields will be synchronised before the keys
		}
		else
		{
			return false;//line has no valuable info (empty or comment)
		}

		$line = str_replace($this->typesUpper, $this->typesLower, $line);
		foreach ($options['defaultNumberLengths'] as $numberType => $length) {
			if (!preg_match("/ {$numberType} *\(/", $line)) $line = preg_replace("/ {$numberType}(\s|$)/", " {$numberType}({$length})$1", $line);
		}
		$line = preg_replace("/'\s*,\s*'/", "','", $line);
		$line = preg_replace("/([^\s])\s+([,\)])/", '$1$2', $line);
		$line = preg_replace("/([,\(])\s+([^\s])/", '$1$2', $line);
		//$key = str_replace('`', '', $key);
		if (!empty($options['varcharDefaultIgnore']))
		{
			$line = preg_replace("/(var)?char\(([0-9]+)\)\s+NOT\s+NULL\s+default\s+''/i", '$1char($2) NOT NULL', $line);
		}
		if (!empty($options['intDefaultIgnore']))
		{
			$line = preg_replace("/((?:big)|(?:tiny))?int\(([0-9]+)\)\s+NOT\s+NULL\s+default\s+'0'/i", '$1int($2) NOT NULL', $line);
		}
		if (!empty($options['ignoreIncrement']))
		{
			$line = preg_replace("/ AUTO_INCREMENT=[0-9]+/i", '', $line);
		}
		// convert default values for integer columns to integer
		$line = preg_replace("/(int\([0-9]+\)\s+.*\sdefault\s)'(\d+)'(.*)/i",'$1$2$3',$line);

		$result['key'] = $this->normalizeString($key);
		$result['line']= $line;
		return $result;
	}

	/**
	 * Takes an output of compare() method to generate the set of sql needed to update source table to make it
	 * look as a destination one
	 * @access private
	 * @param array $diff compare() method output
	 * @return array list of sql statements
	 * supports query generation options
	 */
	function getDiffSql($diff, $debug=FALSE)//maybe add option to ommit or force 'IF NOT EXISTS', skip autoincrement
	{
		$options = $this->config;
		$sqls = array();
		if (!is_array($diff) || empty($diff))
		{
			return $sqls;
		}
		foreach($diff as $tab=>$info)
		{
			if ($info['sourceOrphan'])//delete it
			{
				if(in_array('dropTable', $this->updateActions)) $sqls[] = 'DROP TABLE `'.$tab.'`';
			}
			elseif ($info['destOrphan'])//create destination table in source
			{
				$database = '';
				$destSql = $this->getTabSql($this->destStruct, $tab, $database);
				if (!empty($options['ignoreIncrement']))
				{
					$destSql = preg_replace("/\s*AUTO_INCREMENT=[0-9]+/i", '', $destSql);
				}
				if (!empty($options['ingoreIfNotExists']))
				{
					$destSql = preg_replace("/IF NOT EXISTS\s*/i", '', $destSql);
				}
				if (!empty($options['forceIfNotExists']))
				{
					$destSql = preg_replace('/(CREATE(?:\s*TEMPORARY)?\s*TABLE\s*)(?:IF\sNOT\sEXISTS\s*)?(`?\w+`?)/i', '$1IF NOT EXISTS $2', $destSql);
				}
				$sqls[] = $destSql;
			}
			else
			{
				foreach($info['differs'] as $finfo)
				{
					$inDest = !empty($finfo['dest']);
					$inSource = !empty($finfo['source']);
					if ($inSource && !$inDest)
					{
						$sql = $finfo['source'];
						if($debug && $finfo['dest']) print "\e[0;32mDEBUG DROP  : $tab ".$finfo['dest']."\e[0m\n";
						$action = 'drop';
					}
					elseif ($inDest && !$inSource)
					{
						$sql = $finfo['dest'];
						if($debug && $finfo['source']) print "\e[0;32mDEBUG ADD   : $tab ".$finfo['source']."\e[0m\n";
						$action = 'add';
					}
					else
					{
						$sql = $finfo['dest'];
						if($debug && $finfo['source']) print "\e[0;32mDEBUG MODIFY: $tab ".$finfo['source']."\e[0m\n";
						$action = 'modify';
					}
					if(in_array($action, $this->updateActions)) {
						$sql = $this->getActionSql($action, $tab, $sql);
						$sqls[] = $sql;
					}
				}
			}
		}
		return $sqls;
	}

	/**
	 * Compiles update sql
	 * @access private
	 * @param string $action - 'drop', 'add' or 'modify'
	 * @param string $tab table name
	 * @param string $sql definition of the element to change
	 * @return string update sql
	 */
	function getActionSql($action, $tab, $sql)
	{
		$result = 'ALTER TABLE `'.$tab.'` ';
		$action = strtolower($action);
		$keyField = '`?\w`?(?:\(\d+\))?';//matches `name`(10)
		$keyFieldList = '(?:'.$keyField.'(?:,\s?)?)+';//matches `name`(10),`desc`(255)
		if (preg_match('/((?:PRIMARY )|(?:UNIQUE )|(?:FULLTEXT ))?KEY `?(\w+)?`?\s(\('.$keyFieldList.'\))/i', $sql, $m))
		{   //key and index operations
			$type = strtolower(trim($m[1]));
			$name = trim($m[2]);
			$fields = trim($m[3]);
			switch($action)
			{
				case 'drop':
					if ($type=='primary')
					{
						$result.= 'DROP PRIMARY KEY';
					}
					else
					{
						$result.= 'DROP INDEX `'.$name.'`';
					}
					break;
				case 'add':
					if ($type=='primary')
					{
						$result.= 'ADD PRIMARY KEY '.$fields;
					}
					elseif ($type=='')
					{
						$result.= 'ADD INDEX `'.$name.'` '.$fields;
					}
					else
					{
						$result .='ADD '.strtoupper($type).' `'.$name.'` '.$fields;//fulltext or unique
					}
					break;
				case 'modify':
					if ($type=='primary')
					{
						$result.='DROP PRIMARY KEY, ADD PRIMARY KEY '.$fields;
					}
					elseif ($type=='')
					{
						$result.='DROP INDEX `'.$name.'`, ADD INDEX `'.$name.'` '.$fields;
					}
					else
					{
						$result.='DROP INDEX `'.$name.'`, ADD '.strtoupper($type).' `'.$name.'` '.$fields;//fulltext or unique
					}
					break;

			}
		}
		else //fields operations
		{
			$sql = rtrim(trim($sql), ',');
			$result.= strtoupper($action);
			if ($action=='drop')
			{
				$spacePos = strpos($sql, ' ');
				$result.= ' '.substr($sql, 0, $spacePos);
			}
			else
			{
				$result.= ' '.$sql;
			}
		}
		return $result;
	}

	/**
	 * Searches for the position of the next delimiter which is not inside string literal like 'this ; ' or
	 * like "this ; ".
	 *
	 * Handles escaped \" and \'. Also handles sql comments.
	 * Actualy it is regex-based Finit State Machine (FSN)
	 */
	function getDelimPos($string, $offset=0, $delim=';', $skipInBrackets=false)
	{
		$stack = array();
		$rbs = '\\\\';	//reg - escaped backslash
		$regPrefix = "(?<!$rbs)(?:$rbs{2})*";
		$reg = $regPrefix.'("|\')|(/\\*)|(\\*/)|(-- )|(\r\n|\r|\n)|';
		if ($skipInBrackets)
		{
			$reg.='(\(|\))|';
		}
		else
		{
			$reg.='()';
		}
		$reg .= '('.preg_quote($delim).')';
		while (preg_match('%'.$reg.'%', $string, $m, PREG_OFFSET_CAPTURE, $offset))
		{
			$offset = $m[0][1]+strlen($m[0][0]);
			if (end($stack)=='/*')
			{
				if (!empty($m[3][0]))
				{
					array_pop($stack);
				}
				continue;//here we could also simplify regexp
			}
			if (end($stack)=='-- ')
			{
				if (!empty($m[5][0]))
				{
					array_pop($stack);
				}
				continue;//here we could also simplify regexp
			}

			if (!empty($m[7][0]))// ';' found
			{
				if (empty($stack))
				{
					return $m[7][1];
				}
				else
				{
					//var_dump($stack, substr($string, $offset-strlen($m[0][0])));
				}
			}
			if (!empty($m[6][0]))// '(' or ')' found
			{
				if (empty($stack) && $m[6][0]=='(')
				{
					array_push($stack, $m[6][0]);
				}
				elseif($m[6][0]==')' && end($stack)=='(')
				{
					array_pop($stack);
				}
			}
			elseif (!empty($m[1][0]))// ' or " found
			{
				if (end($stack)==$m[1][0])
				{
					array_pop($stack);
				}
				else
				{
					array_push($stack, $m[1][0]);
				}
			}
			elseif (!empty($m[2][0])) // opening comment / *
			{
				array_push($stack, $m[2][0]);
			}
			elseif (!empty($m[4][0])) // opening comment --
			{
				array_push($stack, $m[4][0]);
			}
		}
		return false;
	}

	/**
	 * works the same as getDelimPos except returns position of the first occurence of the delimiter starting from
	 * the end of the string
	 */
	function getDelimRpos($string, $offset=0, $delim=';', $skipInBrackets=false)
	{
		$pos = $this->getDelimPos($string, $offset, $delim, $skipInBrackets);
		if ($pos===false)
		{
			return false;
		}
		do
		{
			$newPos=$this->getDelimPos($string, $pos+1, $delim, $skipInBrackets);
			if ($newPos !== false)
			{
				$pos = $newPos;
			}
		}
		while($newPos!==false);
		return $pos;
	}

	/**
	 * Converts string to lowercase and replaces repeated spaces with the single one -
	 * to be used for the comparison purposes only
	 * @param string $str string to normaize
	 */
	function normalizeString($str)
	{
		$str = strtolower($str);
		$str = preg_replace('/\s+/', ' ', $str);
		return $str;
	}
}
