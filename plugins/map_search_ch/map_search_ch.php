<?php
function my_map_map_search_ch($data) {
	global $ko_path;

	$link = "";

	$zip = $data["plz"];
	$city = $data["ort"];
	if($zip && $city) $link .= "/".$zip."-".$city;
	else $link .= "/".$zip.$city;

	$replace = array("ö" => "oe", "ä" => "ae", "ü" => "ue", "é" => "e", "è" => "e", "à" => "a", "ç" => "c");
	$address = strtr($data["adresse"], $replace);
	if($address) $link .= "/".$address;

	if($link != "" && $link != "/") {
		$link = "http://map.search.ch".$link;
	} else {
		return "";
	}

	$code  = '<a href="'.$link.'" target="_blank">';
	$code .= '<img src="'.$ko_path.'plugins/map_search_ch/icon_map_search_ch.gif" border="0" alt="map.search.ch" title="'.getLL("my_map_search_ch_show").'" />';
	$code .= '</a>&nbsp;';
	return $code;
}//my_map_map_search_ch()
?>
