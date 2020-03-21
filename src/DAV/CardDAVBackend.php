<?php
/*******************************************************************************
*
*    OpenKool - Online church organization tool
*
*    Copyright © 2013      Christoph Fischer (chris@toph.de)
*                          Volksmission Freudenstadt
*    Copyright © 2019-2020 Daniel Lerch
*
*    This program is free software; you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation; either version 2 of the License, or
*    (at your option) any later version.
*
*    This program is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*******************************************************************************/

namespace kOOL\DAV;

use Sabre\CardDAV\Plugin;
use Sabre\CardDAV\Backend\AbstractBackend;
use Sabre\CardDAV\Property\SupportedAddressData;

class CardDAVBackend extends AbstractBackend {

    /**
     * @var \mysqli
     */
    private $db_connection;

    public function __construct(\mysqli $db_connection) {
        $this->db_connection = $db_connection;
    }

    /**
     * Returns the list of addressbooks for a specific user.
     *
     * Every addressbook should have the following properties:
     *   id - an arbitrary unique id
     *   uri - the 'basename' part of the url
     *   principaluri - Same as the passed parameter
     *
     * Any additional clark-notation property may be passed besides this. Some
     * common ones are :
     *   {DAV:}displayname
     *   {urn:ietf:params:xml:ns:carddav}addressbook-description
     *   {http://calendarserver.org/ns/}getctag
     *
     * @param string $principalUri
     * @return array
     */
    public function getAddressBooksForUser($principalUri) {
		global $BASE_URL;

		// get the user login from $principalUri
		$tmp = explode('/', $principalUri);
		$user = $tmp[count($tmp)-1];   			

		//Get timestamp of last changed address in addressbook: for getctag
		$tstamp = $this->getAddressBookCTag($user);
		
		$addressBooks = array();
		$addressBooks[] = array(
			'id' => $user,
			'uri' => 'contacts',
			'principaluri' => 'principals/'.$user,
			'{'.Plugin::NS_CARDDAV.'}addressbook-description' => 'kOOL addressbook for '.$user,
			'{http://calendarserver.org/ns/}getctag' => md5($BASE_URL.'-'.$user.'-'.$tstamp),
			'{'.Plugin::NS_CARDDAV.'}supported-address-data' => new SupportedAddressData(),        	
		);
    }

    /**
     * Updates an addressbook's properties
     *
     * See Sabre\DAV\IProperties for a description of the mutations array, as
     * well as the return value.
     *
     * @param mixed $addressBookId
     * @param array $mutations
     * @see Sabre\DAV\IProperties::updateProperties
     * @return bool|array
     */
    public function updateAddressBook($addressBookId, array $mutations) {
        return false;
    }

    /**
     * Creates a new address book
     *
     * @param string $principalUri
     * @param string $url Just the 'basename' of the url.
     * @param array $properties
     * @return void
     */
    public function createAddressBook($principalUri, $url, array $properties) {
    }

    /**
     * Deletes an entire addressbook and all its contents
     *
     * @param mixed $addressBookId
     * @return void
     */
    public function deleteAddressBook($addressBookId) {
    }

    /**
     * Returns all cards for a specific addressbook id.
     *
     * This method should return the following properties for each card:
     *   * carddata - raw vcard data
     *   * uri - Some unique url
     *   * lastmodified - A unix timestamp
     *
     * It's recommended to also return the following properties:
     *   * etag - A unique etag. This must change every time the card changes.
     *   * size - The size of the card in bytes.
     *
     * If these last two properties are provided, less time will be spent
     * calculating them. If they are specified, you can also ommit carddata.
     * This may speed up certain requests, especially with large cards.
     *
     * @param mixed $addressbookId
     * @return array
     */
    public function getCards($addressbookId) {
        // we completely ignore $addressbookId here, since there is always only ONE
		// addressbook available (the one from kOOL).
		$p = $this->retrieveAddresses($addressbookId);
		
		$o = array();
		
		$loginID = $this->getKoolUserId($addressbookId);   	

		foreach($p as $person) {
			$mod = $this->getLastModified($person);
			
			$card = new vCard();
			$card->addPerson($person, $loginID);
											
			$o[] = array(
				'uri' => $person['id'],
				'lastmodified' => $mod,
				'etag' => md5(serialize($person)).'_'.$mod,
				'size' => strlen($card->output),
			);
		}
		return $o;
    }

    /**
     * Returns a specfic card.
     *
     * The same set of properties must be returned as with getCards. The only
     * exception is that 'carddata' is absolutely required.
     *
     * @param mixed $addressBookId
     * @param string $cardUri
     * @return array
     */
    public function getCard($addressBookId, $cardUri) {

        $person = $this->retrieveAddresses($addressBookId, $cardUri);
		$mod = $this->getLastModified($person);
		
		$loginID = $this->getUserId($addressBookId);  	
		
		$card = new vCard();
		$card->addPerson($person, $loginID);
		$carddata = $card->output;

		$o = array(
			'carddata' => $carddata,
			'uri' => $person['id'],
			'lastmodified' => $mod,
			'etag' => md5(serialize($person)).'_'.$mod,
			'size' => strlen($carddata),
		);
		
		return $o; 	    			
    }

    /**
     * Creates a new card.
     *
     * The addressbook id will be passed as the first argument. This is the
     * same id as it is returned from the getAddressbooksForUser method.
     *
     * The cardUri is a base uri, and doesn't include the full path. The
     * cardData argument is the vcard body, and is passed as a string.
     *
     * It is possible to return an ETag from this method. This ETag is for the
     * newly created resource, and must be enclosed with double quotes (that
     * is, the string itself must contain the double quotes).
     *
     * You should only return the ETag if you store the carddata as-is. If a
     * subsequent GET request on the same card does not have the same body,
     * byte-by-byte and you did return an ETag here, clients tend to get
     * confused.
     *
     * If you don't return an ETag, you can just return null.
     *
     * @param mixed $addressBookId
     * @param string $cardUri
     * @param string $cardData
     * @return string|null
     */
    public function createCard($addressBookId, $cardUri, $cardData) {
        return null;
    }

    /**
     * Updates a card.
     *
     * The addressbook id will be passed as the first argument. This is the
     * same id as it is returned from the getAddressbooksForUser method.
     *
     * The cardUri is a base uri, and doesn't include the full path. The
     * cardData argument is the vcard body, and is passed as a string.
     *
     * It is possible to return an ETag from this method. This ETag should
     * match that of the updated resource, and must be enclosed with double
     * quotes (that is: the string itself must contain the actual quotes).
     *
     * You should only return the ETag if you store the carddata as-is. If a
     * subsequent GET request on the same card does not have the same body,
     * byte-by-byte and you did return an ETag here, clients tend to get
     * confused.
     *
     * If you don't return an ETag, you can just return null.
     *
     * @param mixed $addressBookId
     * @param string $cardUri
     * @param string $cardData
     * @return string|null
     */
    public function updateCard($addressBookId, $cardUri, $cardData) {
        return null;
    }

    /**
     * Deletes a card
     *
     * @param mixed $addressBookId
     * @param string $cardUri
     * @return bool
     */
    public function deleteCard($addressBookId, $cardUri) {
        return false;
    }

    /**
     * Gets the matching user ID for a login name.
     * 
     * @return null|int
     */
    private function getUserId($login) {
        $stmt = $this->db_connection->prepare("SELECT id FROM ko_admin WHERE `login` = ? AND `disabled` = ''");
        $stmt->bind_param('s', $login);
        $stmt->execute();
        $stmt->bind_result($id);
        $found = $stmt->fetch();
        $stmt->close();
        if ($found)
            return $id;
        else
            return null;
    }

    private function getLastModified($person) {
        $stmt = $this->db_connection->prepare("SELECT crdate, lastchange FROM ko_leute WHERE `id` = ?");
        $stmt->bind_param('i', $person['id']);
        $stmt->execute();
        $stmt->bind_result($crdate,$lastchange);
        $found = $stmt->fetch();
        $stmt->close();
        if (!$found)
            return null;
        else if ($lastchange == '0000-00-00 00:00:00')
            return $crdate;
        else
            return $lastchange;
    }

    /**
	 * Get last changed address in the given addressbook.
	 * @return int Returns timestamp of last change
	 */
	protected function getAddressBookCTag($addressbookId) {
		$loginID = $this->getUserId($addressbookId);

		//Check for filter to be applied
		$filterID = intval(ko_get_userpref($loginID, 'leute_carddav_filter'));
		if($filterID) {
			$preset = db_select_data('ko_userprefs', "WHERE `id` = '".intval($filterID)."'", '*', '', '', TRUE);
			$filter = unserialize($preset['value']);
		} else {
			$filter = array();
		}

		//Apply filter and admin filter
		apply_leute_filter($filter, $where, true, '', $loginID);

		//Get change date of last changed address
		$row = db_select_data('ko_leute', "WHERE 1 ".$where, 'id,lastchange,crdate', 'ORDER BY lastchange DESC, crdate DESC', 'LIMIT 0,1', TRUE);
		$lc = $row['lastchange'];
		$cr = $row['crdate'];
		if($lc != '0000-00-00 00:00:00') {
			return strtotime($lc);
		} else {
			return strtotime($cr);
		}
	}

    private function retrieveAddresses($addressbookId, $id=null) {
		$loginID = $this->getUserId($addressbookId);

		//Check for filter to be applied
		$filterID = intval(ko_get_userpref($loginID, 'leute_carddav_filter'));
		if($filterID) {
			$preset = db_select_data('ko_userprefs', "WHERE `id` = '".intval($filterID)."'", '*', '', '', TRUE);
			$filter = unserialize($preset['value']);
		} else {
			$filter = array();
		}

		//Apply filter and admin filter
		apply_leute_filter($filter, $where, true, '', $loginID);

		//Apply filter for single address
		if($id) $where = "AND (`id`='".intval($id)."') $where";
		
		$ct = ko_get_leute($p, $where);
		if($id) return $p[$id]; else return $p;
	}
}
