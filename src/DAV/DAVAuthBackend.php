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

use Sabre\DAV\Auth\Backend\AbstractBasic;

class DAVAuthBackend extends AbstractBasic {

    /**
     * @var \mysqli
     */
    private $db_connection;

    public function __construct(\mysqli $db_connection) {
        $this->db_connection = $db_connection;
    }

    /**
     * Validates a username and password
     *
     * @param string $username
     * @param string $password
     * @return bool Returns true or false depending on if login succeeded
     */
    public function validateUserPass($username, $password) {
        if ($username == 'ko_guest') return false;

        $password_hash = md5($password);
        $stmt = $this->db_connection->prepare("SELECT COUNT(*) FROM ko_admin WHERE `login` = ? AND `password` = ? AND `disabled` = ''");
        $stmt->bind_param('ss', $username, $password_hash);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        return $count > 0;
    }
}
