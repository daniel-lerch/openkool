<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2003-2015 Renzo Lauper (renzo@churchtool.org)
*  All rights reserved
*
*  This script is part of the kOOL project. The kOOL project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*  kOOL is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

$ko_path = '../';
include($ko_path.'inc/ko.inc');
include($ko_path.'inc/qrcode/qrlib.php');

$string = base64_decode($_GET['s']);
if(!$string) exit;
$hash = $_GET['h'];
if(strlen($hash) != 32) exit;

$check = md5(KOOL_ENCRYPTION_KEY.$string);
if($check != $hash) exit;

$size = $_GET['size'];
if(!$size || $size > 10 || $size < 1) $size = 6;


$cache_path = $ko_path.'my_images/cache/';
$filename = 'qr_'.$check.'_'.$size.'.png';

//Check for cached image
$dontcache = FALSE;
if(file_exists($cache_path.$filename)) {
	//Do nothing
} else {
	if(substr($string, 0, 4) == 'pid:') {
		$pid = intval(substr($string, 4));
		if(!$pid) exit;
		ko_get_access('leute');
		if($access['leute']['ALL'] > 0 || $access['leute'][$pid]) {
			require_once($ko_path.'leute/inc/vcard.php');
			$vcard = new vCard(TRUE);
			$vcard->addPerson($pid);
			$string = $vcard->getVCard();
			if(!$string) exit;
			$dontcache = TRUE;
		} else exit;
	}

	//Create new image
	QRcode::png($string, $cache_path.$filename, 'L', $size);
}

header('Content-type: image/png');
readfile($cache_path.$filename);


if($dontcache) {
	unlink($cache_path.$filename);
}


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
