<?php
function my_map_map24($data) {
	global $ko_path;

	$link = "";

	$replace = array("ö" => "oe", "ä" => "ae", "ü" => "ue", "é" => "e", "è" => "e", "à" => "a", "ç" => "c");
	$address = strtr($data["adresse"], $replace);
	if($address) $link .= "+".$address;

	if($data["plz"]) $link .= "+".$data["plz"];
	if($data["ort"]) $link .= "+".$data["ort"];
	if($data["land"]) $link .= "+".$data["land"];

	if($link != "") {
		$link = "http://www.map24.com/search?q=".substr($link, 1);
	} else {
		return "";
	}

	$code  = '<a href="'.$link.'" target="_blank">';
	$code .= '<img src="'.$ko_path.'plugins/map24/icon_map24.gif" border="0" alt="map24" title="'.getLL("my_map24_show").'" />';
	$code .= '</a>&nbsp;';
	return $code;
}//my_map_map24()
?>
