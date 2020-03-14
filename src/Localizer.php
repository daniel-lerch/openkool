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

namespace OpenKool;

class Localizer {

    /**
     * @var array $dictionary Contains the localized strings for all loaded languages.
     */
    private static $dictionary = array();
    /**
     * @var array $languages Contains only the language parts of supported cultures.
     */
    private static $languages = array();
    /**
     * @var array $regions An associative array with the language and all supported regions.
     */
    private static $regions = array();

    public static function getLanguages() {
        return Localizer::$languages;
    }
    
    public static function init() {
        global $LIB_LANGS, $LIB_LANGS2, $WEB_LANGS;

        // Available languages in this installation
        $languages = array();
        $regions = array();
        Localizer::$languages = &$languages;
        Localizer::$regions = &$regions;
        foreach($WEB_LANGS as $ll) {
            list($l, $l2) = explode('_', $ll);
            if(in_array($l, $LIB_LANGS)) $languages[] = mb_strtolower($l);
            if(in_array($l2, $LIB_LANGS2[$l])) $regions[$l][] = mb_strtoupper($l2);
        }
        if(sizeof($languages) == 0) $languages = $LIB_LANGS;
        if(sizeof($regions) == 0) $regions = $LIB_LANGS2;

        // Set a new language
        if(!empty($_GET["set_lang"])) {
            $new_lang = mb_strtolower(format_userinput($_GET["set_lang"], "alpha", FALSE, 5));
            list($new_lang, $new_lang2) = explode("_", $new_lang);
            if(in_array($new_lang, $languages)) {
                $_SESSION["lang"] = mb_strtolower($new_lang);
                //Unset lang2 so it is newly set for the new language
                unset($_SESSION["lang2"]);
                //Save selection as userpref
                if($ko_menu_akt != "install" && $_SESSION["ses_userid"] != ko_get_guest_id()) {
                    ko_save_userpref($_SESSION["ses_userid"], "lang", $new_lang);
                }
            }
        }

        // Use language from userprefs
        $user_lang = ko_get_userpref($_SESSION['ses_userid'], 'lang');
        if($user_lang != '' && in_array($user_lang, $languages)) {
            $_SESSION['lang'] = $user_lang;
        }

        // Use default language if not set yet or get from browser
        if(!$_SESSION["lang"] && $GET_LANG_FROM_BROWSER) {
            $browser_langs = getBrowserLanguages();
            foreach($browser_langs as $bl) {
                list($new_lang, $new_lang2) = explode("_", $bl);
                if($new_lang != "" && in_array($new_lang, $languages)) {
                    $_SESSION["lang"] = mb_strtolower($new_lang);
                    break;
                }
            }
        }

        // Use default language if none was found above
        if(!$_SESSION['lang']) $_SESSION['lang'] = mb_strtolower($languages[0]);

        // Use default regional settings for the selected language if none was found above
        if(sizeof($regions[$_SESSION['lang']]) == 0) $regions[$_SESSION['lang']] = $LIB_LANGS2[$_SESSION['lang']];

        // Set lang2, the region part as US in en_US
        if(!$_SESSION['lang2']) {
            if(!empty($new_lang2) && in_array(mb_strtoupper($new_lang2), $regions[$_SESSION['lang']])) {
                //Set region as given by Browser
                $_SESSION['lang2'] = mb_strtoupper($new_lang2);
            } else {
                //Otherwise use first entry in LANGS2 as default
                $_SESSION['lang2'] = mb_strtoupper($regions[$_SESSION['lang']][0]);
            }
        }

        setlocale(LC_ALL, ($_SESSION["lang"]."_".$_SESSION["lang2"].'.UTF-8'));

        // Include locallang-files to the current language
        $LOCAL_LANG = NULL;
        include __DIR__ . "/../locallang/locallang.{$_SESSION['lang']}.php";
        // Include locallang file for regional changes according to lang2 if region is not default as set in LIB_LANGS2
        if(mb_strtoupper($_SESSION['lang2']) != mb_strtoupper($LIB_LANGS2[$_SESSION['lang']][0])) {
            $file = __DIR__ . "/../locallang/locallang.{$_SESSION['lang']}_{$_SESSION['lang2']}.php";
            if(file_exists($file)) {
                include $file;
            }
        }

        //HOOK: Include locallang files from the plugins
        $hooks = hook_include_ll();
        foreach($hooks as $hook) include($hook);

        Localizer::$dictionary[$_SESSION["lang"]] = $LL[$_SESSION["lang"]];

        if ($_SESSION["lang"] != $LIB_LANGS[0]) {
            //Include default-language if this is not the used language
            include __DIR__ . "/../locallang/locallang.{$LIB_LANGS[0]}.php";
            Localizer::$dictionary[$LIB_LANGS[0]] = $LL[$LIB_LANGS[0]];
        }
        
        unset($LL);
    }
    
    public static function get(string $key) {
        global $LIB_LANGS;

        if (empty($key)) 
            return '';
        
        $preferred = Localizer::$dictionary[$_SESSION['lang']];
        $fallback = Localizer::$dictionary[$LIB_LANGS[0]];
        if (isset($preferred[$key]))
            return $preferred[$key];
        elseif (isset($fallback[$key]))
            return $fallback[$key];
        
        return '';
    }

    /**
     * Returns an array of all languages set in the browser sorted by priority.
     */
    private static function getBrowserLanguages() {
        $languages = array();
        $strAcceptedLanguage = explode(',',$_SERVER['HTTP_ACCEPT_LANGUAGE']);
        foreach ($strAcceptedLanguage as $languageLine) {
            list ($languageCode, $quality) = explode (';',$languageLine);
            $arrAcceptedLanguages[$languageCode] = $quality ? substr ($quality,2) : 1;
        }

        // Now sort the accepted languages by their quality and create an array containing only the language codes in the correct order.
        if (is_array ($arrAcceptedLanguages)) {
            arsort ($arrAcceptedLanguages);
            $languageCodes = array_keys ($arrAcceptedLanguages);
            if (is_array($languageCodes)) {
                reset ($languageCodes);
                while (list ($languageCode,$quality) = each ($languageCodes)){
                    $quality = substr ($quality,0,5);
                    $languages[$languageCode] = str_replace("-", "_", $quality);
                }
            }
        }
        return $languages;
    }
}
?>
