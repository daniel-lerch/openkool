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

class M2 extends Migration {

    public function __construct(\mysqli $db_connection) {
        parent::__construct($db_connection, 2);
    }

    protected function apply_internal() {
        $this->query('ALTER TABLE `ko_event` ALTER `import_id` SET DEFAULT \'\'');
        $this->query('ALTER TABLE `ko_eventgruppen` ALTER `calendar_id` SET DEFAULT \'0\'');
        $this->query('ALTER TABLE `ko_eventgruppen` ALTER `name` SET DEFAULT \'\'');
        $this->query('ALTER TABLE `ko_eventgruppen` ALTER `shortname` SET DEFAULT \'\'');
        $this->query('ALTER TABLE `ko_eventgruppen` ALTER `room` SET DEFAULT \'\'');
        $this->query('ALTER TABLE `ko_eventgruppen` ALTER `startzeit` SET DEFAULT \'00:00:00\'');
        $this->query('ALTER TABLE `ko_eventgruppen` ALTER `endzeit` SET DEFAULT \'00:00:00\'');
        $this->query('ALTER TABLE `ko_eventgruppen` ALTER `title` SET DEFAULT \'\'');
        $this->query('ALTER TABLE `ko_eventgruppen` ALTER `kommentar` SET DEFAULT \'\'');
        $this->query('ALTER TABLE `ko_eventgruppen` ALTER `url` SET DEFAULT \'\'');
        $this->query('ALTER TABLE `ko_eventgruppen` ALTER `notify` SET DEFAULT \'\'');
        $this->query('ALTER TABLE `ko_eventgruppen` ALTER `gcal_url` SET DEFAULT \'\'');
        $this->query('ALTER TABLE `ko_eventgruppen` ALTER `ical_url` SET DEFAULT \'\'');
        $this->query('ALTER TABLE `ko_eventgruppen` ALTER `update` SET DEFAULT \'0\'');
        $this->query('ALTER TABLE `ko_eventgruppen` ALTER `last_update` SET DEFAULT \'0000-00-00 00:00:00\'');
        $this->query('ALTER TABLE `ko_reservation` ALTER `comments` SET DEFAULT \'\'');
    }
}
