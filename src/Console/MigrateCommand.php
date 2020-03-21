<?php
/*******************************************************************************
*
*    OpenKool - Online church organization tool
*
*    Copyright © 2003-2015 Renzo Lauper (renzo@churchtool.org)
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

namespace kOOL\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateCommand extends Command {

    protected static $defaultName = 'migrate';

    protected function configure() {
        $this->setDescription('Runs all pending database migrations');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        global $db_connection;

        $result = $db_connection->query('SELECT `value` FROM `ko_settings` WHERE `key` = \'db_migration\'');
        if ($result->num_rows > 0)
            $db_migration = (int)(($result->fetch_assoc())['value']);
        else
            $db_migration = 0;
        $result->free();

        for ($i = $db_migration + 1; class_exists('kOOL\\Migrations\\M' . $i); $i++) {
            $class = new \ReflectionClass('kOOL\\Migrations\\M' . $i);
            $instance = $class->newInstance($db_connection);
            $instance->apply();
        }
    }
}
