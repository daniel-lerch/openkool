<?php
/*******************************************************************************
*
*    OpenKool - Online church organization tool
*
*    Copyright © 2003-2015 Renzo Lauper (renzo@churchtool.org)
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
require __DIR__ . '/ko.inc.php';

$string = base64_decode($_GET['s']);
if(!$string) exit;
$hash = $_GET['h'];
if(mb_strlen($hash) != 32) exit;

$check = md5(KOOL_ENCRYPTION_KEY.$string);
if($check != $hash) exit;

$size = $_GET['size'];
if(!$size || $size > 1000 || $size < 100) $size = 250;

if(mb_substr($string, 0, 4) == 'pid:') {
	$pid = intval(mb_substr($string, 4));
	if(!$pid) exit;
	ko_get_access('leute');
	if($access['leute']['ALL'] > 0 || $access['leute'][$pid]) {
		$vcard = new OpenKool\DAV\vCard(TRUE);
		$vcard->addPerson($pid);
		$string = $vcard->getVCard($pid);
		if(!$string) exit;
	} else exit;
}

$qr = new Endroid\QrCode\QrCode($string);
$qr->setSize((int)$size);
$qr->setMargin(4);
header('Content-Type: ' . $qr->getContentType());
echo $qr->writeString();


/*
$vcard = 'BEGIN:VCARD
VERSION:2.1
N;ENCODING=QUOTED-PRINTABLE:Lauper;Renzo;;Herr;
FN;ENCODING=QUOTED-PRINTABLE:Herr Renzo  Lauper
ADR;HOME;POSTAL;ENCODING=QUOTED-PRINTABLE:;;R=F6ssligutstrasse 6;Aarau;;5000;
EMAIL;INTERNET:renzo@lauper.cc
REV:20111102T192231Z
END:VCARD';

$link = 'http://192.168.7.8/web_test/ical/?user=3cb9523f1f16b5578b4670cbf945066f&egs=4';

//QRcode::png($link);
QRcode::png($vcard);

//QRtools::buildCache();
*/
?>
