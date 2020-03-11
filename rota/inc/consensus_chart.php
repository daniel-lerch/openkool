<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2003-2020 Renzo Lauper (renzo@churchtool.org)
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


// 3 = no_answer, 2 = no, 1 = maybe, 0 = yes
$colors = array(3 => 'fcfcfc', 2 => 'ffcfcd', 1 => 'ffeea6', 0 => 'd1f3d1');
// you may change the sharpness of the gradient by changing this value. higher value means sharper
$approxWidth = 20;

$getAnswers = $_GET['x'];
$answers = explode('x', $getAnswers);
// check if parameters have valid format
if (sizeof($answers) != 4) return;
$answersWidth = 0;
foreach ($answers as $answer) {
	// only allow unsigned integers up to 99999
	if (!preg_match('/[0-9]{0,5}/', $answer)) return;
	$answersWidth += $answer;
}
if ($answersWidth == 0) {
	$answersWidth = 1;
	$answers[0] = 1;
}

$width = 0;
$answersReordered = array();
foreach ($answers as $k => $answer) {
	$answersReordered[3-$k] = floor($answer / $answersWidth * $approxWidth);
	$width += $answersReordered[3-$k];
}
$img = ImageCreateTrueColor($width, 1);

$left = 0;
for ($i = 0; $i < 4; $i++) {
	$answer = $answersReordered[$i];
	imagefilledrectangle($img,$left,0,$left + $answer,1,colorHex($img, $colors[$i]));
	$left += $answer;
}

OutputImage($img);
ImageDestroy($img);



function colorHex($img, $HexColorString) {
	$R = hexdec(substr($HexColorString, 0, 2));
	$G = hexdec(substr($HexColorString, 2, 2));
	$B = hexdec(substr($HexColorString, 4, 2));
	return ImageColorAllocate($img, $R, $G, $B);
}

function OutputImage($img) {
	header('Content-type: image/jpg');
	ImageJPEG($img,NULL,100);
}
