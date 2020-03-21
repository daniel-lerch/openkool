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

$ko_path = '../';
require __DIR__ . '/../inc/ko.inc.php';

use kOOL\Console\MigrateCommand;
use Symfony\Component\Console\Application;

$app = new Application("OpenKool Console", VERSION);
$app->add(new MigrateCommand());
$app->run();
