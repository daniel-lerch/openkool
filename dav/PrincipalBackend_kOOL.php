<?php

namespace Sabre\DAVACL\PrincipalBackend;

use Sabre\DAV;
use Sabre\DAV\MkCol;
use Sabre\HTTP\URLUtil;

/**
 * PDO principal backend
 *
 *
 * This backend assumes all principals are in a single collection. The default collection
 * is 'principals/', but this can be overriden.
 *
 * @copyright Copyright (C) fruux GmbH (https://fruux.com/)
 * @author Evert Pot (http://evertpot.com/)
 * @license http://sabre.io/license/ Modified BSD License
 */
class kOOL extends AbstractBackend implements CreatePrincipalSupport {

	/**
	 * prefix
	 *
	 * @var string
	 */
	public $prefix = 'principals';

	/**
	 * pdo
	 *
	 * @var PDO
	 */
	protected $pdo;

	/**
	 * Sets up the backend.
	 *
	 * @param PDO $pdo
	 */
	function __construct(\PDO $pdo) {

		$this->pdo = $pdo;

	}

	/**
	 * converts a ko_admin database row to a principal array
	 *
	 * @param array $data
	 * @return array
	 */
	private function getPrincipalFromData($data)
	{

		if(isset($data['id']) && !isset($data['login'])) {
			if($data['id'] > 0xffff) {
				$stmt = $this->pdo->prepare("SELECT id,name FROM ko_admingroups WHERE id=?");
				$stmt->execute(array($data['id']>>16));
				$row = $stmt->fetch(\PDO::FETCH_ASSOC);
				if(!$row) return;
				$data['login'] = 'admingroup-'.$row['id'];
				$data['displayname'] = $row['name'];
			} else {
				$stmt = $this->pdo->prepare("SELECT id,login,email,leute_id FROM ko_admin WHERE id=? AND disabled=''");
				$stmt->execute(array($data['id']));
				$data = $stmt->fetch(\PDO::FETCH_ASSOC);
				if(!$data) return;
			}
		}
		if(!isset($data['displayname'])) {
			$data['displayname'] = $data['login'];
		}

		if($data['leute_id']) {
			$stmt = $this->pdo->prepare("SELECT email,vorname,nachname FROM ko_leute WHERE id=?");
			$stmt->execute(array($data['leute_id']));
			if($koLeute = $stmt->fetch(\PDO::FETCH_ASSOC)) {
				$data['displayname'] = $data['vorname'].' '.$data['nachname'];
				if(empty($data['email'])) {
					$data['email'] = $koLeute['email'];
				}
			}
		}

		return [
			'id' => $data['id'],
			'uri' => $this->prefix.'/'.$data['login'],
			'{DAV:}displayname' => $data['displayname'],
			'{http://sabredav.org/ns}email-address' => $data['email'],
		];
	}

	/**
	 * Returns a list of principals based on a prefix.
	 *
	 * This prefix will often contain something like 'principals'. You are only
	 * expected to return principals that are in this base path.
	 *
	 * You are expected to return at least a 'uri' for every user, you can
	 * return any additional properties if you wish so. Common properties are:
	 *   {DAV:}displayname
	 *   {http://sabredav.org/ns}email-address - This is a custom SabreDAV
	 *	 field that's actualy injected in a number of other properties. If
	 *	 you have an email address, use this property.
	 *
	 * @param string $prefixPath
	 * @return array
	 */
	function getPrincipalsByPrefix($prefixPath) {

		$principals = array();

		if($prefixPath == $this->prefix) {
			$result = $this->pdo->query("SELECT id,login,email,leute_id FROM ko_admin WHERE disabled=''");
			while($row = $result->fetch(\PDO::FETCH_ASSOC)) {
				$principals[] = $this->getPrincipalFromData($row);
			}
			$result = $this->pdo->query("SELECT id,name FROM ko_admingroups");
			while($row = $result->fetch(\PDO::FETCH_ASSOC)) {
				$principals[] = $this->getPrincipalFromData([
					'id' => $row['id']<<16,
					'login' => 'admingroup-'.$row['id'],
					'displayname' => $row['name']
				]);
			}
		}

		return $principals;
	}

	/**
	 * Returns a specific principal, specified by it's path.
	 * The returned structure should be the exact same as from
	 * getPrincipalsByPrefix.
	 *
	 * @param string $path
	 * @return array
	 */
	function getPrincipalByPath($path) {

		list($prefix,$login) = URLUtil::splitPath($path);
		if($prefix != $this->prefix) return array();

		if(substr($login,0,11) == 'admingroup-') {
			$stmt = $this->pdo->prepare("SELECT id,name FROM ko_admingroups WHERE id=?");
			$stmt->execute(array(substr($login,11)));
			if($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
				$row = array(
					'id' => $row['id']<<16,
					'login' => 'admingroup-'.$row['id'],
					'displayname' => $row['name']
				);
			}
		} else {
			$stmt = $this->pdo->prepare("SELECT id,login,email,leute_id FROM ko_admin WHERE disabled='' AND login=?");
			$stmt->execute(array($login));
			$row = $stmt->fetch(\PDO::FETCH_ASSOC);
		}

		if(!$row) {
			return array();
		}

		return $this->getPrincipalFromData($row);
	}

	/**
	 * Updates one ore more webdav properties on a principal.
	 *
	 * The list of mutations is stored in a Sabre\DAV\PropPatch object.
	 * To do the actual updates, you must tell this object which properties
	 * you're going to process with the handle() method.
	 *
	 * Calling the handle method is like telling the PropPatch object "I
	 * promise I can handle updating this property".
	 *
	 * Read the PropPatch documenation for more info and examples.
	 *
	 * @param string $path
	 * @param DAV\PropPatch $propPatch
	 */
	function updatePrincipal($path, DAV\PropPatch $propPatch) {
		return false;
	}

	/**
	 * This method is used to search for principals matching a set of
	 * properties.
	 *
	 * This search is specifically used by RFC3744's principal-property-search
	 * REPORT.
	 *
	 * The actual search should be a unicode-non-case-sensitive search. The
	 * keys in searchProperties are the WebDAV property names, while the values
	 * are the property values to search on.
	 *
	 * By default, if multiple properties are submitted to this method, the
	 * various properties should be combined with 'AND'. If $test is set to
	 * 'anyof', it should be combined using 'OR'.
	 *
	 * This method should simply return an array with full principal uri's.
	 *
	 * If somebody attempted to search on a property the backend does not
	 * support, you should simply return 0 results.
	 *
	 * You can also just return 0 results if you choose to not support
	 * searching at all, but keep in mind that this may stop certain features
	 * from working.
	 *
	 * @param string $prefixPath
	 * @param array $searchProperties
	 * @param string $test
	 * @return array
	 */
	function searchPrincipals($prefixPath, array $searchProperties, $test = 'allof') {
		if(count($searchProperties) == 0) return array();	//No criteria

		if($prefixPath != $this->prefix) return array();

// 		return array();

		$aquery = 'SELECT a.login';
		$afrom = array('ko_admin' => 'FROM ko_admin a');
		$awhere = array();
		$gquery = 'SELECT g.id';
		$gfrom = array('ko_admingroups' => 'FROM ko_admingroups g');
		$gwhere = array();

		$avalues = array();
		$gvalues = array();
		foreach ($searchProperties as $property => $value) {
			switch ($property) {
				case '{DAV:}displayname' :
					$afrom['ko_leute'] = ' JOIN ko_leute l ON(a.leute_id=l.id)';
					$awhere[] = "CONCAT(l.vorname,' ',l.nachname)";
					$avalues[] = $value;
					$awhere[] = "a.login";
					$avalues[] = $value;
					$gwhere[] = "g.name";
					$gvalues[] = $value;
					break;
				case '{http://sabredav.org/ns}email-address' :
					$afrom['ko_leute'] = ' JOIN ko_leute l ON(a.leute_id=l.id)';
					$awhere[] = "a.email";
					$avalues[] = $value;
					$awhere[] = "l.email";
					$avalues[] = $value;
					break;
				default :
					// Unsupported property
					return array();
			}
			if (count($values) > 0) $query .= (strcmp($test, "anyof") == 0 ? " OR " : " AND ");
			$query .= 'lower(' . $column . ') LIKE lower(?)';
			$values[] = '%' . $value . '%';

		}

		$principals = array();

		$aquery .= ' '.implode(' ',$afrom).' '.implode($test == 'allof' ? ' AND ' : ' OR ',array_map(function($a){return 'LOWER('.$a.') LIKE LOWER(?)';},$awhere));
		$astmt = $this->pdo->prepare($aquery);
		$astmt->execute(array_map(function($a){return '%'.$a.'%';},$avalues));

		while($row = $astmt->fetch(\PDO::FETCH_ASSOC)) {
			$principals[] = $this->prefix.'/'.$row['login'];
		}

		$gquery .= ' '.implode(' ',$gfrom).' '.implode($test == 'allof' ? ' AND ' : ' OR ',array_map(function($a){return 'LOWER('.$a.') LIKE LOWER(?)';},$gwhere));
		$gstmt = $this->pdo->prepare($gquery);
		$gstmt->execute(array_map(function($a){return '%'.$a.'%';},$gvalues));

		while ($row = $gstmt->fetch(\PDO::FETCH_ASSOC)) {
			$principals[] = $this->prefix.'/admingroup-'.$row['id'];
		}

		return $principals;

	}

	/**
	 * Finds a principal by its URI.
	 *
	 * This method may receive any type of uri, but mailto: addresses will be
	 * the most common.
	 *
	 * Implementation of this API is optional. It is currently used by the
	 * CalDAV system to find principals based on their email addresses. If this
	 * API is not implemented, some features may not work correctly.
	 *
	 * This method must return a relative principal path, or null, if the
	 * principal was not found or you refuse to find it.
	 *
	 * @param string $uri
	 * @param string $principalPrefix
	 * @return string
	 */
	function findByUri($uri, $principalPrefix) {
		return null;
	}

	/**
	 * Returns the list of members for a group-principal
	 *
	 * @param string $principal
	 * @return array
	 */
	function getGroupMemberSet($principal) {

		$principal = $this->getPrincipalByPath($principal);
		if (!$principal) throw new DAV\Exception('Principal not found');

		$stmt = $this->pdo->prepare("SELECT login FROM ko_admin a WHERE FIND_IN_SET(?,admingroups)");
		$stmt->execute(array($principal['id']>>16));
		$result = array();
		while($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			$result[] = $this->prefix.'/'.$row['login'];
		}
		return $result;
	}

	/**
	 * Returns the list of groups a principal is a member of
	 *
	 * @param string $principal
	 * @return array
	 */
	function getGroupMembership($principal) {

		$principal = $this->getPrincipalByPath($principal);
		if (!$principal) throw new DAV\Exception('Principal not found');

		$stmt = $this->pdo->prepare("SELECT g.id FROM ko_admingroups g JOIN ko_admin a ON(FIND_IN_SET(g.id,a.admingroups)) WHERE a.id=?");
		$stmt->execute(array($principal['id']));
		$result = array();
		while($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			$result[] = $this->prefix.'/admingroup-'.$row['id'];
		}
		return $result;
	}

	/**
	 * Updates the list of group members for a group principal.
	 *
	 * The principals should be passed as a list of uri's.
	 *
	 * @param string $principal
	 * @param array $members
	 * @return void
	 */
	function setGroupMemberSet($principal, array $members) {

		return false;

		// Grabbing the list of principal id's.
		$stmt = $this->pdo->prepare('SELECT id, uri FROM ' . $this->tableName . ' WHERE uri IN (? ' . str_repeat(', ? ', count($members)) . ');');
		$stmt->execute(array_merge(array($principal), $members));

		$memberIds = array();
		$principalId = null;

		while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			if ($row['uri'] == $principal) {
				$principalId = $row['id'];
			} else {
				$memberIds[] = $row['id'];
			}
		}
		if (!$principalId) throw new DAV\Exception('Principal not found');

		// Wiping out old members
		$stmt = $this->pdo->prepare('DELETE FROM ' . $this->groupMembersTableName . ' WHERE principal_id = ?;');
		$stmt->execute(array($principalId));

		foreach ($memberIds as $memberId) {

			$stmt = $this->pdo->prepare('INSERT INTO ' . $this->groupMembersTableName . ' (principal_id, member_id) VALUES (?, ?);');
			$stmt->execute(array($principalId, $memberId));

		}

	}

	/**
	 * Creates a new principal.
	 *
	 * This method receives a full path for the new principal. The mkCol object
	 * contains any additional webdav properties specified during the creation
	 * of the principal.
	 *
	 * @param string $path
	 * @param MkCol $mkCol
	 * @return void
	 */
	function createPrincipal($path, MkCol $mkCol) {

	}

}
