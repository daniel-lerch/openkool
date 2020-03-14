<?php

namespace Sabre\DAV\Auth\Backend;

/**
 * This is an authentication backend that uses kOOL manage passwords.
 *
 * @copyright Copyright (C) 2013 Volksmission Freudenstadt
 * @author Christoph Fischer (chris@toph.de)
 * @license http://code.google.com/p/sabredav/wiki/License Modified BSD License
 */
class kOOL extends AbstractBasic {

	/**
	 * Reference to PDO connection
	 *
	 * @var PDO
	 */
	protected $pdo;

	/**
	 * PDO table name we'll be using
	 *
	 * @var string
	 */
	protected $tableName;


	/**
	 * Creates the backend object.
	 *
	 * If the filename argument is passed in, it will parse out the specified file fist.
	 *
	 * @param PDO $pdo
	 * @param string $tableName The PDO table name to use
	 */
	public function __construct(\PDO $pdo, $tableName = 'ko_admin') {
		$this->pdo = $pdo;
		$this->tableName = $tableName;
	}

	/**
	 * Returns the digest hash for a user.
	 *
	 * @param string $suppliedUser
	 * @param string $suppliedPass
	 * @return boolean|null
	 */
	public function validateUserPass($suppliedUser, $suppliedPassword) {
		if($suppliedUser == 'ko_guest') return FALSE;

		$suppliedPassword = md5($suppliedPassword);

		$stmt = $this->pdo->prepare("SELECT login, password, id FROM ".$this->tableName." WHERE `login` = ? AND `disabled` = ''");
		$stmt->execute(array($suppliedUser));
		$result = $stmt->fetchAll();

		if(count($result) != 1) return FALSE;
		$ok = ($suppliedPassword==$result[0]['password']);
		if($ok) {
			$_SESSION['ses_userid'] = $result[0]['id'];
		}
		return $ok;
	}

}//class
