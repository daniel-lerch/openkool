<?php
/*******************************************************************************
*
*    OpenKool - Online church organization tool
*
*    Copyright © 2019      Daniel Lerch
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

namespace OpenKool\Migrations;

use Exception;
use mysqli;

abstract class Migration {

    /**
     * @var mysqli Database connection
     */
    protected $db_connection;

    /**
     * @var int Migration number
     */
    protected $number;

    public function __construct(mysqli $db_connection, int $number) {
        $this->db_connection = $db_connection;
        $this->number = $number;
    }

    public function apply() {
        try {
            $this->apply_internal();
            $this->query("UPDATE `ko_settings` SET `value` = \'$this->number\' WHERE `key` = \'db_migration\'");
            return true;
        } catch (Exception $e) {
            echo "An error occured while performing migration $this->number: ({$e->getCode()}) {$e->getMessage()}\n{$e->getTraceAsString()}";
            return false;
        }
    }

    protected abstract function apply_internal();

    protected function query(string $query) {
        echo "Executing query $query...";
        $result = $this->db_connection->query($query);
        if (!$result) {
            throw new Exception($this->db_connection->error, $this->db_connection->errno);
        } else {
            echo 'OK';
        }
    }
}
