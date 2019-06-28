<?php
$FAMILIE_EXCLUDE  = array("famid", "nachname");

$LEUTE_ENUMPLUS = array();
$LEUTE_TEXTSELECT = array("land");
$LEUTE_LAYOUT   = array(
	array("anrede", "hidden"),
	array("firm", "department"),
	array("vorname", "nachname"),
	array("adresse", "adresse_zusatz"),
	array("plz", "ort"),
	array("land"),
	array("---"),
	array("telp", "telg"),
	array("natel", "fax"),
	array("email", "web"),
	array("zivilstand", "geschlecht"),
	array("geburtsdatum"),
	array("---"),
	array("MODULE::save"),
	array("---"),
	array("MODULE::groups"),
	array("---"),
	array("MODULE::save"),
	array("---"),
	array("smallgroups"),
	array("---"),
	array("memo1", "memo2"),
);
?>
