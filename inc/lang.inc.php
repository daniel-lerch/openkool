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

//Available languages in this installation
$LANGS  = array();
$LANGS2 = array();
foreach($WEB_LANGS as $ll) {
	list($l, $l2) = explode('_', $ll);
	if(in_array($l, $LIB_LANGS)) $LANGS[] = mb_strtolower($l);
	if(in_array($l2, $LIB_LANGS2[$l])) $LANGS2[$l][] = mb_strtoupper($l2);
}
if(sizeof($LANGS) == 0) $LANGS = $LIB_LANGS;
if(sizeof($LANGS2) == 0) $LANGS2 = $LIB_LANGS2;

//Set a new language
if(!empty($_GET["set_lang"])) {
	$new_lang = mb_strtolower(format_userinput($_GET["set_lang"], "alpha", FALSE, 5));
	list($new_lang, $new_lang2) = explode("_", $new_lang);
	if(in_array($new_lang, $LANGS)) {
		$_SESSION["lang"] = mb_strtolower($new_lang);
		//Unset lang2 so it is newly set for the new language
		unset($_SESSION["lang2"]);
		//Save selection as userpref
		if($ko_menu_akt != "install" && $_SESSION["ses_userid"] != ko_get_guest_id()) {
			ko_save_userpref($_SESSION["ses_userid"], "lang", $new_lang);
		}
	}
}

//Use default language if not set yet or get from browser
if(!$_SESSION["lang"] && $GET_LANG_FROM_BROWSER) {
	$browser_langs = getBrowserLanguages();
	foreach($browser_langs as $bl) {
		list($new_lang, $new_lang2) = explode("_", $bl);
		if($new_lang != "" && in_array($new_lang, $LANGS)) {
			$_SESSION["lang"] = mb_strtolower($new_lang);
			break;
		}
	}
}

//Use default language if none was found above
if(!$_SESSION['lang']) $_SESSION['lang'] = mb_strtolower($LANGS[0]);

//Use default regional settings for the selected language if none was found above
if(sizeof($LANGS2[$_SESSION['lang']]) == 0) $LANGS2[$_SESSION['lang']] = $LIB_LANGS2[$_SESSION['lang']];

//Set lang2, the region part as US in en_US
if(!$_SESSION['lang2']) {
	if($new_lang2 != '' && in_array(mb_strtoupper($new_lang2), $LANGS2[$_SESSION['lang']])) {
		//Set region as given by Browser
		$_SESSION['lang2'] = mb_strtoupper($new_lang2);
	} else {
		//Otherwise use first entry in LANGS2 as default
		$_SESSION['lang2'] = mb_strtoupper($LANGS2[$_SESSION['lang']][0]);
	}
}

setlocale(LC_ALL, ($_SESSION["lang"]."_".$_SESSION["lang2"].'.UTF-8'));


//Include locallang-files to the current language
$LOCAL_LANG = NULL;
include($ko_path."locallang/locallang.".$_SESSION["lang"].".php");
//Include locallang file for regional changes according to lang2 if region is not default as set in LIB_LANGS2
if(mb_strtoupper($_SESSION['lang2']) != mb_strtoupper($LIB_LANGS2[$_SESSION['lang']][0])) {
	if(file_exists($ko_path.'locallang/locallang.'.$_SESSION['lang'].'_'.$_SESSION['lang2'].'.php')) {
		include($ko_path.'locallang/locallang.'.$_SESSION['lang'].'_'.$_SESSION['lang2'].'.php');
	}
}


if($_SESSION["lang"] != $LIB_LANGS[0]) {
	//Include default-language if this is not the used language
	include($ko_path."locallang/locallang.".$LIB_LANGS[0].".php");

	//HOOK: Include locallang files from the plugins
	$hooks = hook_include_ll();
	if(sizeof($hooks) > 0) foreach($hooks as $hook) include($hook);

	$LOCAL_LANG[$_SESSION["lang"]] = array_merge($LL[$LIB_LANGS[0]], $LL[$_SESSION["lang"]]);

} else {

	//HOOK: Include locallang files from the plugins
	$hooks = hook_include_ll();
	if(sizeof($hooks) > 0) foreach($hooks as $hook) include($hook);

	$LOCAL_LANG[$_SESSION["lang"]] = $LL[$_SESSION["lang"]];
}
unset($LL);
?>
