<?php

namespace Sabre\DAVACL\PrincipalBackend;

use Sabre\DAV;
use Sabre\DAVACL;

/**
 * kOOL principal backend
 *
 *
 * This backend assumes all principals are in a single collection. The default collection
 * is 'principals/', but this can be overriden.
 *
 * @copyright Copyright (C) 2013 Volksmission Freudenstadt.
 * @author Christoph Fischer (chris@toph.de)
 * @license http://code.google.com/p/sabredav/wiki/License Modified BSD License
 */
class kOOL extends AbstractBackend {

	/**
	 * pdo
	 *
	 * @var PDO
	 */
	protected $pdo;

	/**
	 * PDO table name for 'principals'
	 *
	 * @var string
	 */
	protected $tableName;

	/**
	 * PDO table name for 'group members'
	 *
	 * @var string
	 */
	protected $groupMembersTableName;

	/**
	 * A list of additional fields to support
	 *
	 * @var array
	 */
	protected $fieldMap = array(
		/**
		 * This property can be used to display the users' real name.
		 */
		'{DAV:}displayname' => array(
				'dbField' => 'login',
		),

		/**
		 * This property is actually used by the CardDAV plugin, where it gets
		 * mapped to {http://calendarserver.orgi/ns/}me-card.
		 *
		 * The reason we don't straight-up use that property, is because
		 * me-card is defined as a property on the users' addressbook
		 * collection.
		 */
		'{http://sabredav.org/ns}vcard-url' => array(
				'dbField' => 'login',
		),
		/**
		 * This is the users' primary email-address.
		 */
		'{http://sabredav.org/ns}email-address' => array(
				'dbField' => 'email',
		),
	);

	/**
	 * Sets up the backend.
	 *
	 * @param PDO $pdo
	 * @param string $tableName
	 * @param string $groupMembersTableName
	 */
	public function __construct(\PDO $pdo, $tableName = 'ko_admin', $groupMembersTableName = 'groupmembers') {
		$this->pdo = $pdo;
		$this->tableName = $tableName;
		$this->groupMembersTableName = $groupMembersTableName;
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
	 *     field that's actualy injected in a number of other properties. If
	 *     you have an email address, use this property.
	 *
	 * @param string $prefixPath
	 * @return array
	 */
	public function getPrincipalsByPrefix($prefixPath) {
		$fields = array('id', 'login', 'email', 'leute_id');

		$result = $this->pdo->query('SELECT '.implode(',', $fields).'  FROM '. $this->tableName." WHERE `disabled` = ''");

		$principals = array();
		while($row = $result->fetch(\PDO::FETCH_ASSOC)) {
			// Checking if the principal is in the prefix
			list($rowPrefix) = DAV\URLUtil::splitPath('principals/'.$row['login']);
			if($rowPrefix !== $prefixPath) continue;

			//Get email
			if($row['email']) {
				//Do nothing, use the admin email address
			}
			else if(intval($row['leute_id']) > 0) {
				//TODO: Check for all email fields and use preferred
				$result2 = $this->pdo->query("SELECT `email` FROM `ko_leute` WHERE `id` = '".intval($row['leute_id'])."' AND `deleted` = '0' AND `hidden` = '0'");
				$person = $result2->fetch(\PDO::FETCH_ASSOC);
				if($person['email']) $row['email'] = $person['email'];
			}
			else {
				$row['email'] = '';
			}

			$principal = array(
				'id' => $row['id'],
				'uri' => 'principals/'.$row['login'],
				'email' => $row['email']
			);
			foreach($this->fieldMap as $key=>$value) {
				if($row[$value['dbField']]) {
					$principal[$key] = $row[$value['dbField']];
				}
			}
			$principals[] = $principal;
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
	public function getPrincipalByPath($path) {
		$fields = array('id', 'login', 'email', 'leute_id');

		//trick this into an uri in the form principals/<login> ...
		$uri = substr($path, 11); 
		
		$stmt = $this->pdo->prepare('SELECT '.implode(',', $fields).'  FROM '. $this->tableName . " WHERE `login` = ? AND `disabled` = ''");
		$stmt->execute(array($uri));

		$row = $stmt->fetch(\PDO::FETCH_ASSOC);
		if (!$row) return;

		//Get email
		if($row['email']) {
			//Do nothing, use the admin email address
		}
		else if(intval($row['leute_id']) > 0) {
			//TODO: Check for all email fields and use preferred
			$result2 = $this->pdo->query("SELECT `email` FROM `ko_leute` WHERE `id` = '".intval($row['leute_id'])."' AND `deleted` = '0' AND `hidden` = '0'");
			$person = $result2->fetch(\PDO::FETCH_ASSOC);
			if($person['email']) $row['email'] = $person['email'];
		}
		else {
			$row['email'] = '';
		}

		$principal = array(
			'id'  => $row['id'],
			'uri' => 'principals/'.$row['login'],
			'email' => $row['email']
		);
		foreach($this->fieldMap as $key=>$value) {
			if($row[$value['dbField']]) {
				$principal[$key] = $row[$value['dbField']];
			}
		}
		return $principal;
	}




	/**
	 * Updates one ore more webdav properties on a principal.
	 *
	 * The list of mutations is supplied as an array. Each key in the array is
	 * a propertyname, such as {DAV:}displayname.
	 *
	 * Each value is the actual value to be updated. If a value is null, it
	 * must be deleted.
	 *
	 * This method should be atomic. It must either completely succeed, or
	 * completely fail. Success and failure can simply be returned as 'true' or
	 * 'false'.
	 *
	 * It is also possible to return detailed failure information. In that case
	 * an array such as this should be returned:
	 *
	 * array(
	 *   200 => array(
	 *      '{DAV:}prop1' => null,
	 *   ),
	 *   201 => array(
	 *      '{DAV:}prop2' => null,
	 *   ),
	 *   403 => array(
	 *      '{DAV:}prop3' => null,
	 *   ),
	 *   424 => array(
	 *      '{DAV:}prop4' => null,
	 *   ),
	 * );
	 *
	 * In this previous example prop1 was successfully updated or deleted, and
	 * prop2 was succesfully created.
	 *
	 * prop3 failed to update due to '403 Forbidden' and because of this prop4
	 * also could not be updated with '424 Failed dependency'.
	 *
	 * This last example was actually incorrect. While 200 and 201 could appear
	 * in 1 response, if there's any error (403) the other properties should
	 * always fail with 423 (failed dependency).
	 *
	 * But anyway, if you don't want to scratch your head over this, just
	 * return true or false.
	 *
	 * @param string $path
	 * @param array $mutations
	 * @return array|bool
	 */
	public function updatePrincipal($path, $mutations) {

		return false;

		/*
		$updateAble = array();
		foreach($mutations as $key=>$value) {

			// We are not aware of this field, we must fail.
			if (!isset($this->fieldMap[$key])) {
				$response = array(
					403 => array(
						$key => null,
					),
					424 => array(),
				);

				// Adding the rest to the response as a 424
				foreach($mutations as $subKey=>$subValue) {
					if ($subKey !== $key) {
						$response[424][$subKey] = null;
					}
				}
				return $response;
			}

			$updateAble[$this->fieldMap[$key]['dbField']] = $value;

		}

		// No fields to update
		$query = "UPDATE " . $this->tableName . " SET ";

		$first = true;
		foreach($updateAble as $key => $value) {
			if(!$first) {
				$query.= ', ';
			}
			$first = false;
			$query.= "$key = :$key ";
		}
		$query.='WHERE uri = :uri';
		$stmt = $this->pdo->prepare($query);
		$updateAble['uri'] =  $path;
		$stmt->execute($updateAble);

		return true;
		*/
	}




	/**
	 * This method is used to search for principals matching a set of
	 * properties.
	 *
	 * This search is specifically used by RFC3744's principal-property-search
	 * REPORT. You should at least allow searching on
	 * http://sabredav.org/ns}email-address.
	 *
	 * The actual search should be a unicode-non-case-sensitive search. The
	 * keys in searchProperties are the WebDAV property names, while the values
	 * are the property values to search on.
	 *
	 * If multiple properties are being searched on, the search should be
	 * AND'ed.
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
	 * @return array
	 */
	public function searchPrincipals($prefixPath, array $searchProperties) {

		$query = 'SELECT login FROM ' . $this->tableName . " WHERE `disabled` = '' ";
		$values = array();
		foreach($searchProperties as $property => $value) {
			switch($property) {
				case '{DAV:}displayname' :
					$query.=' AND LOWER(login) = ?';
					$values[] = strtolower($value);
				break;
				case '{http://sabredav.org/ns}email-address' :
					$query.=' AND LOWER(email) LIKE = ?';
					$values[] = strtolower($value);
				break;
				default :
					// Unsupported property
					return array();
			}
		}
		$stmt = $this->pdo->prepare($query);
		$stmt->execute($values);

		$principals = array();
		while($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			// Checking if the principal is in the prefix
			list($rowPrefix) = DAV\URLUtil::splitPath('principals/'.$row['login']);
			if ($rowPrefix !== $prefixPath) continue;

			$principals[] = 'principals/'.$row['login'];
		}

		return $principals;
	}

	/**
	 * Returns the list of members for a group-principal
	 *
	 * @param string $principal
	 * @return array
	 */
	public function getGroupMemberSet($principal) {
		return array();
	}

	/**
	 * Returns the list of groups a principal is a member of
	 *
	 * @param string $principal
	 * @return array
	 */
	public function getGroupMembership($principal) {
		return array();
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
	public function setGroupMemberSet($principal, array $members) {
	}

}
