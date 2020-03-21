<?php
/*******************************************************************************
*
*    OpenKool - Online church organization tool
*
*    Copyright © 2003-2020 Renzo Lauper (renzo@churchtool.org)
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



if(isset($_GET['id']) && (int)$_GET['id'] > 0) {
	$ko_path = '../../';
	require __DIR__ . '/../../inc/ko.inc.php';

	$id = format_userinput($_GET['id'], 'uint');
	if(!$id) exit;

	if(!ko_module_installed('leute')) exit;
	ko_get_access('leute');
	if($access['leute']['ALL'] < 1 && $access['leute'][$id] < 1) exit;

	$v = new \kOOL\DAV\vCard();
	$v->addPerson($id);
	$v->outputCard();
} else {
	http_response_code(400);
}
