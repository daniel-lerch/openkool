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

define('VERSION', 'R45');


//Reservation: Objekt-Bild
$RESITEM_IMAGE_WIDTH = 60;
//Itemlist-Stringlängen-Maximum
define("ITEMLIST_LENGTH_MAX", 35);
//Default-Werte für Logins
$DEFAULT_USERPREFS = array(
		array('key' => 'show_limit_daten',            'value' => '20',                          'type' => ''),
		array('key' => 'cal_jahr_num',                'value' => '6',                           'type' => ''),
		array('key' => 'show_limit_leute',            'value' => '20',                          'type' => ''),
		array('key' => 'show_limit_kg',               'value' => '20',                          'type' => ''),
		array('key' => 'show_limit_reservation',      'value' => '20',                          'type' => ''),
		array('key' => 'show_limit_logins',           'value' => '20',                          'type' => ''),
		array('key' => 'show_limit_fileshare',        'value' => '20',                          'type' => ''),
		array('key' => 'show_limit_tapes',            'value' => '20',                          'type' => ''),
		array('key' => 'show_limit_groups',           'value' => '20',                          'type' => ''),
		array('key' => 'show_limit_donations',        'value' => '20',                          'type' => ''),
		array('key' => 'show_limit_trackings',        'value' => '20',                          'type' => ''),
		array('key' => 'tracking_date_limit',         'value' => '7',                           'type' => ''),
		array('key' => 'default_view_daten',          'value' => 'show_cal_monat',              'type' => ''),
		array('key' => 'default_view_reservation',    'value' => 'show_cal_monat',              'type' => ''),
		array('key' => 'front_modules_left',          'value' => 'daten_cal,fastfilter,news',   'type' => ''),
		array('key' => 'front_modules_center',        'value' => 'today',                       'type' => ''),
		array('key' => 'front_modules_right',         'value' => 'geburtstage',                 'type' => ''),
		array('key' => 'do_mod_email_for_edit_res',   'value' => '0',                           'type' => ''),
		array('key' => 'do_mod_email_for_edit_daten', 'value' => '0',                           'type' => ''),
		array('key' => 'do_res_email',                'value' => '0',                           'type' => ''),
		array('key' => 'modules_dropdown',            'value' => 'ja',                          'type' => ''),
		array('key' => 'daten_monthly_title',         'value' => 'eventgruppen_id',             'type' => ''),
		array('key' => 'daten_title_length',          'value' => '30',                          'type' => ''),
		array('key' => 'daten_pdf_show_time',         'value' => '2',                           'type' => ''),
		array('key' => 'daten_pdf_week_start',        'value' => '1',                           'type' => ''),
		array('key' => 'daten_pdf_week_length',       'value' => '7',                           'type' => ''),
		array('key' => 'daten_mark_sunday',           'value' => '0',                           'type' => ''),
		array('key' => 'daten_ical_deadline',         'value' => '0',                           'type' => ''),
		array('key' => 'show_dateres_combined',       'value' => '0',                           'type' => ''),
		array('key' => 'res_pdf_show_time',           'value' => '2',                           'type' => ''),
		array('key' => 'res_pdf_show_comment',        'value' => '0',                           'type' => ''),
		array('key' => 'res_pdf_week_start',          'value' => '1',                           'type' => ''),
		array('key' => 'res_pdf_week_length',         'value' => '7',                           'type' => ''),
		array('key' => 'res_mark_sunday',             'value' => '0',                           'type' => ''),
		array('key' => 'res_monthly_title',           'value' => 'item_id',                     'type' => ''),
		array('key' => 'res_title_length',            'value' => '30',                          'type' => ''),
		array('key' => 'res_ical_deadline',           'value' => '0',                           'type' => ''),
		array('key' => 'cal_woche_start',             'value' => '6',                           'type' => ''),
		array('key' => 'cal_woche_end',               'value' => '22',                          'type' => ''),
		array('key' => 'geburtstagsliste_deadline_plus', 'value' => '21',                       'type' => ''),
		array('key' => 'geburtstagsliste_deadline_minus', 'value' => '5',                       'type' => ''),
		array('key' => 'leute_force_family_firstname','value' => '',                            'type' => ''),
		array('key' => 'leute_children_columns',      'value' => '_father,_mother,_natel',      'type' => ''),
		array('key' => 'show_passed_groups',          'value' => '1',                           'type' => ''),
		array('key' => 'groups_filterlink_add_column','value' => '1',                           'type' => ''),
		array('key' => 'rota_delimiter',              'value' => ', ',                          'type' => ''),
		array('key' => 'rota_pdf_fontsize',           'value' => '11',                          'type' => ''),
		array('key' => 'rota_eventfields',            'value' => 'kommentar,kommentar2',        'type' => ''),
		array('key' => 'rota_orderby',                'value' => 'vorname',                     'type' => ''),
);

$LEUTE_WORD_ADDRESSBLOCK = array(
	array(array('field' => 'firm', 'ifEmpty' => 'anrede')),
	array(array('field' => 'vorname'), array('field' => 'nachname')),
	array(array('field' => 'adresse_zusatz')),
	array(array('field' => 'adresse')),
	array(array('field' => 'plz'), array('field' => 'ort')),
);

$LEUTE_ADRESSLISTE = array("firm", "department", "anrede", "vorname", "nachname", "adresse", "plz", "ort", "land", "telp", "telg", "natel", "fax", "email", "web");
$LEUTE_ADRESSLISTE_LAYOUT = array(
		array("firm", "department"),
		array("anrede"),
		array("vorname", "nachname"),
		array("adresse"),
		array("plz", "ort"),
		array("land"),
		array("@P: ","telp", "@G: ","telg"),
		array("@Mobil: ","natel", "@Fax: ","fax"),
		array("email"),
		array("web")
		);

//mapping table for LDAP-entries
$LDAP_ATTRIB = array(
	'firm' => 'o',
	'department' => 'ou',
	'vorname' => 'givenName',
	'nachname' => 'sn',
	'adresse' => array('street', 'postalAddress', 'mozillaHomeStreet', 'homePostalAddress'),
	'adresse_zusatz' => array('postalAddress', 'mozillaHomeStreet2'),
	'plz' => array('postalCode', 'mozillaHomePostalCode'),
	'ort' => array('l', 'mozillaHomeLocalityName'),
	'telp' => 'homePhone',
	'telg' => 'telephoneNumber',
	'natel' => 'mobile',
	'fax' => 'facsimileTelephoneNumber',
	'email' => 'mail',
	/*'email2' => 'mozillaSecondEmail',*/
	'land' => array('c', 'mozillaHomeCountryName'),
);

// LDAP schema to be used for address records
$LDAP_SCHEMA = array(
	0 => 'top',
	1 => 'person',
	2 => 'organizationalPerson',
	3 => 'inetOrgPerson',
	4 => 'mozillaAddressBookEntry',
);

//Zugehörigkeiten der Familien-Daten zu den Personendaten
//Diese Spalten kommen sowohl in ko_leute wie auch in ko_familie vor. ko_leute.* wird jeweils von ko_familie.* überschrieben.
//Ausser beim Neu-Anlegen einer Person, dann ist es umgekehrt.
//Nachname ist standardmässig kein Fam-Feld, da es oft verschiedene Namen in Familien geben kann.
$COLS_LEUTE_UND_FAMILIE = array("adresse", "adresse_zusatz", "plz", "ort", "land", "telp");

//default columns from ko_leute to be hidden in the form
//can be overridden or extended in config/ko-config.php
$LEUTE_EXCLUDE  = array("id", "famid", "lastchange", "deleted", "kinder", "crdate", "cruserid");

//Default fields containing an email address. Add additionals in config/ko-config.php
$LEUTE_EMAIL_FIELDS = array("email");

//Default fields containing a mobile number. Add additionals in config/ko-config.php.
$LEUTE_MOBILE_FIELDS = array('natel');

//Smallgroup roles
//L: Leader, M: Member. Add more in your ko-config.php and set names in LL (see kg_roles_*)
$SMALLGROUPS_ROLES = array('L', 'M');
$SMALLGROUPS_ROLES_FOR_NUM = array('M');

//Tracking modes
$TRACKING_MODES = array('simple', 'value', 'valueNonNum', 'type', 'typecheck');

//Fields from ko_reservation shown in form for events
//IMPORTANT: Add DB fields res_FIELD to ko_event_mod for new fields, so moderations can be stored
$EVENTS_SHOW_RES_FIELDS = array('startzeit', 'endzeit');

//Set date and time formats (see http://php.net/strftime for help)
//Can be overwritten in config/ko-config.php if needed
$_DATETIME['de'] = array('dm' => '%d.%m', 'dM' => '%e. %B',  'db' => '%e. %b',
												 'mY' => '%m %Y', 'nY' => '%b %Y', 'MY' => '%B %Y',
												 'dmy' => '%d.%m.%y', 'dmY' => '%d.%m.%Y', 'dMY' => '%e. %B %Y', 'dbY' => '%e. %b %Y',
												 'DdM' => '%A, %e. %B',
												 'ddmy' => '%a, %d.%m.%y', 'DdmY' => '%A, %d.%m.%Y', 'DdMY' => '%A, %e. %B %Y');
$_DATETIME['en'] = array('dm' => '%d/%m', 'dM' => '%e %B', 'db' => '%e %b',
												 'mY' => '%m/%Y', 'nY' => '%b %Y', 'MY' => '%B %Y',
												 'dmy' => '%d/%m/%y', 'dmY' => '%d/%m/%Y', 'dMY' => '%e %B %Y', 'dbY' => '%e %b %Y',
												 'DdM' => '%A, %e %B',
												 'ddmy' => '%a, %d/%m/%y', 'DdmY' => '%A, %d/%m/%Y', 'DdMY' => '%A, %e %B %Y');
$_DATETIME['en_US'] = array('dm' => '%m/%d', 'dM' => '%B %e', 'db' => '%b %e',
												'mY' => '%m/%Y', 'nY' => '%b %Y', 'MY' => '%B %Y',
												'dmy' => '%m/%d/%y', 'dmY' => '%m/%d/%Y', 'dMY' => '%B %e %Y', 'dbY' => '%b %e %Y',
												'DdM' => '%A, %e %B',
												'ddmy' => '%a, %m/%d/%y', 'DdmY' => '%A, %m/%d/%Y', 'DdMY' => '%A, %B %e %Y');
$_DATETIME['nl'] = array('dm' => '%e %b', 'dM' => '%e %B', 'mY' => "%b '%y",  'db' => '%e %b',
												 'nY' => '%b %Y', 'MY' => '%B %Y',
												 'dmy' => '%d-%m-%y', 'dmY' => '%d-%m-%Y', 'dMY' => '%e %B %Y', 'dbY' => '%e %b %Y',
												 'DdM' => '%A %e %B',
												 'ddmy' => "%a %e %b '%y", 'DdmY' => '%A %e %b %Y', 'DdMY' => '%A %e %B %Y');
$_DATETIME['fr'] = array('dm' => '%d.%m', 'dM' => '%e. %B', 'db' => '%e. %b',
												 'mY' => '%m %Y', 'nY' => '%b %Y', 'MY' => '%B %Y',
												 'dmy' => '%d.%m.%y', 'dmY' => '%d.%m.%Y', 'dMY' => '%e. %B %Y', 'dbY' => '%e. %b %Y',
												 'DdM' => '%A, %e. %B',
												 'ddmy' => '%a, %d.%m.%y', 'DdmY' => '%A, %d.%m.%Y', 'DdMY' => '%A, %e. %B %Y');


//If TRUE all access priviliges will be set to maximum (use with caution!)
//define("ALL_ACCESS", TRUE);

//If set to TRUE the PHP Quick Profiler (PQP) will be displayed for each page (not included in standard kOOL package)
//define('DEBUG', TRUE);

//Logo files
$FILE_LOGO_SMALL = 'images/kool-text.gif';
$FILE_LOGO_BIG = 'images/kool-text.gif';

//Individually set colors for events by event field (overwrite in config/ko-config.php)
// $EVENT_COLOR['field']: DB field from ko_event
// $EVENT_COLOR['map']:   Array to map above field values to hex colors (e.g. 'foo' => '00ff00', 'bar' => '0000ff')
$EVENT_COLOR = array();

//Set to TRUE (in ko-config.php) if you want to enable the versioning view in the fast filter of the people module
$ENABLE_VERSIONING_FASTFILTER = FALSE;

//Properties for vCard export (used from people module, for QRCode and cardDAV)
$VCARD_PROPERTIES = array(
	'version' => '3.0',
	'phone' =>	array(
		'PREF;HOME;VOICE' => 'telp',
		'PREF;WORK;VOICE' => 'telg',
		'PREF;CELL;VOICE' => 'natel',
		'PREF;FAX' => 'fax',
	),
	'address' => array(
		'HOME;POSTAL' => array('adresse', 'plz', 'ort', 'land'),
	),
	'url' => array(
		'WORK' => 'web',
	),
	'email' => array(
		'INTERNET' => 'email',
	),
	'fields' => array(
		// set an array element named _[sep] to define another separator
		// set an array element named _[noenc] to false to switch off encoding
			
		// organization
		'O' => array('_' => array('sep' => ' '), 0 => 'firm', 1 => 'department'),
		
		// name
		'N' => array('nachname', 'vorname', null, null, null),
		'FN' => array('_' => array('sep' => ' '), 'vorname', 'nachname'),
		
		// birthday
		'BDAY' => array('geburtsdatum'),
		
		// phone:
		'TEL;HOME;VOICE' => array('telp'),
		'TEL;CELL;VOICE' => array('natel'),
		'TEL;WORK;VOICE' => array('telg'),
		'TEL;WORK;FAX' => array('fax'),

		// address:
		'ADR;HOME;POSTAL' => array(null, null, 'adresse', 'ort', null, 'plz', 'land'),
		// url
		'URL;WORK' => array('web'),

		// email
		'EMAIL;INTERNET' => array('email'),

		// modified:
		'REV' => array('lastchange|crdate'),
	),
	'format' => array(
		'telp' => array('phone', null),
		'telg' => array('phone', null),
		'natel' => array('phone', null),
		'fax' => array('phone', null),
		'geburtsdatum' => array('date', null),
		'lastchange' => array('tzdate', 'UTC'),
		'crdate' => array('tzdate', 'UTC'),
	),
	'encoding' => array(),
);


//Configuration for install/update.phpsh
$UPDATER_CONF = array(
	'updateTypes' => array('create', 'add', 'modify'),
	'excludeFields' => array('ko_leute.anrede'),
);


//Set some country codes
$COUNTRY_CODES = array(
	'41' => array('names' => array('ch', 'switzerland', 'schweiz')),
	'49' => array('names' => array('de', 'germany', 'deutschland')),
	'33' => array('names' => array('fr', 'france', 'frankreich')),
	'39' => array('names' => array('it', 'italy', 'italien', 'italia'), 'keep_zero' => true),
	'34' => array('names' => array('es', 'spain', 'spanien', 'españa')),
	'40' => array('names' => array('ro', 'romania', 'roumania', 'rumänien')),
);


//Set default notification levels
require($ko_path . "inc/class.koNotifier.php");
$NOTIFIER_LEVEL_DISPLAY = koNotifier::ERRS | koNotifier::INFO | koNotifier::WARNING;
$NOTIFIER_LEVEL_LOG_TO_DB = koNotifier::ALL ^ koNotifier::DEBUG ^ koNotifier::INFO;
$NOTIFIER_LEVEL_LOG_TO_FILE = koNotifier::DEBUG;
$NOTIFIER_LOG_FILE_NAME = 'log.txt';


//Set log types, that cause server to send an email to the warranty email
$EMAIL_LOG_TYPES = array('db_error_insert', 'db_error_update', 'mailing_smtp_error');

//Set default value for option LEUTE_NO_FAMILY, which disables FAMILY-related options
$LEUTE_NO_FAMILY = false;


//Kunden-spezifische Konfiguration einlesen (kann oben stehende Werte überschreiben)
include($ko_path."config/ko-config.php");

// Configure autoloading
include __DIR__ . '/../vendor/autoload.php';
spl_autoload_register(function($class) {
	$prefix = 'OpenKool\\DAV\\';
	if (substr($class, 0, strlen($prefix)) === $prefix) {
		include __DIR__ . '/dav/' . substr($class, strlen($prefix)) . '.php';
	}
});

//set notification levels
koNotifier::Instance()->setDisplayLevel($NOTIFIER_LEVEL_DISPLAY);
koNotifier::Instance()->setLogToDBLevel($NOTIFIER_LEVEL_LOG_TO_DB);
koNotifier::Instance()->setLogToFileLevel($NOTIFIER_LEVEL_LOG_TO_FILE);
koNotifier::Instance()->setLogFileName($NOTIFIER_LOG_FILE_NAME);


//Set default ldap_login_dn if empty
if($ldap_enabled && (!isset($ldap_login_dn) || $ldap_login_dn == '')) {
	$ldap_login_dn = 'ou=login,'.$ldap_dn;
}

//Check and clean PDFLATEX_PATH
if(isset($PDF_LATEX_PATH)) {
	if($PDFLATEX_PATH != '' && substr($PDFLATEX_PATH, -1) != '/') $PDFLATEX_PATH .= '/';
	if($PDFLATEX_PATH != '' && !is_executable(realpath($PDFLATEX_PATH.'pdflatex'))) $PDFLATEX_PATH = '';
}

//LaTeX export: These fields are set to be used as the sender address
$COLS_LEUTE_LATEX_FROM = array('vorname', 'nachname', 'adresse', 'plz', 'ort', 'telp', 'email', 'web');


//all available modules
$LIB_MODULES = array('daten', 'reservation', 'leute', 'kg', 'groups', 'tracking', 'rota', 'donations', 'tapes', 'fileshare', 'sms', 'admin', 'tools', 'mailing');
//Allow plugins to add modules
foreach($PLUGINS as $p) {
	include_once($ko_path.'plugins/'.$p['name'].'/config.php');
	if(isset($PLUGIN_CONF[$p['name']]['module']) && $PLUGIN_CONF[$p['name']]['module'] != '') {
		$LIB_MODULES[] = $PLUGIN_CONF[$p['name']]['module'];
		$MODULES[] = $PLUGIN_CONF[$p['name']]['module'];
	}
}
foreach($MODULES as $k => $v) {
	if(!in_array($v, $LIB_MODULES)) unset($MODULES[$k]);
}

//Modules with groups for access levels
$MODULES_GROUP_ACCESS = array('daten', 'reservation', 'tapes', 'donations', 'tracking', 'rota');


//Session
include_once($ko_path."inc/session.inc.php");

//Error reporting
function ko_error_handler($errno, $errstr, $errfile, $errline) {
	global $ko_path, $FILE_LOGO_BIG;
	switch ($errno) {
		case E_ERROR:
		case E_USER_ERROR:
			$backtrace = debug_backtrace();
			require($ko_path . 'inc/error_handling.inc.php');
			break;
		case E_WARNING:
		case E_USER_WARNING:
		case E_NOTICE:
		case E_USER_NOTICE:
			// TODO: Fix dozens of these errors and enable error page
			break;
	}
}

set_error_handler('ko_error_handler');


if(defined('DEBUG') && DEBUG) {
	//start output with: if(defined('DEBUG') && DEBUG) $profiler->display($DEBUG_db);

	require($ko_path.'pqp/classes/PhpQuickProfiler.php');
	$debugMode = TRUE;
	$profiler = new PhpQuickProfiler(PhpQuickProfiler::getMicroTime(), 'web_test/pqp/');

	class pqp_db {
		var $queryCount = 0;
		var $queries = array();
	};
	$DEBUG_db = new pqp_db;

	define('DEBUG_SELECT', TRUE);
	define('DEBUG_UPDATE', TRUE);
	define('DEBUG_INSERT', TRUE);
	define('DEBUG_DELETE', TRUE);
}


//Get base_path from _SERVER if not set during first installation
if($ko_menu_akt == "install" && !$BASE_PATH) {
	$bdir = str_replace("install", "", dirname($_SERVER['SCRIPT_NAME']));
	$droot = $_SERVER["DOCUMENT_ROOT"];
	if(substr($droot, -1) == "/") $droot = substr($droot, 0, -1);
	$BASE_PATH = $droot.$bdir;
}
if($BASE_PATH != "" && substr($BASE_PATH, -1) != "/") $BASE_PATH .= "/";

//Hooks (Plugins)
include($BASE_PATH."inc/hooks.inc.php");


//Connect to the database
$db_connection = mysqli_connect($mysql_server, $mysql_user, $mysql_pass, $mysql_db);
//Set client-server connection to UTF-8 with multibyte support
if($db_connection) mysqli_query($db_connection, 'SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci');

//Set user to ko_guest if none logged in yet
if($db_connection && !in_array($ko_menu_akt, array('scheduler', 'install', 'get.php', 'post.php')) && empty($_SESSION['ses_userid'])) {
	$_SESSION['ses_username'] = 'ko_guest';
	$_SESSION['ses_userid'] = ko_get_guest_id();

	//Log guest with IP address (but not form mailing cron job or cli)
	if(!in_array($ko_menu_akt, array('mailing', 'scheduler', 'get.php', 'post.php', 'ical')) && php_sapi_name() != 'cli') {
		ko_log('guest', 'ko_guest from '.ko_get_user_ip());
	}

	//Redirect guest user upon it's first visit (unless script is called from cli)
	if(!in_array($ko_menu_akt, array('mailing', 'scheduler', 'install', 'get.php', 'post.php', 'ical', 'carddav')) && php_sapi_name() != 'cli') {
		ko_redirect_after_login();
	}
}



//Available languages (overwrite only with $WEB_LANGS in config/ko-config.php, or through the installation)
$LIB_LANGS  = array('en', 'de', 'nl', 'fr');
//Regions available for each language (first one being the default)
$LIB_LANGS2 = array('en' => array('UK', 'US'),
										'de' => array('CH', 'DE'),
										'nl' => array('NL'),
										'fr' => array('CH'),
										);
include($BASE_PATH.'inc/lang.inc.php');
if(isset($_DATETIME[$_SESSION['lang'].'_'.$_SESSION['lang2']])) {
	$DATETIME = $_DATETIME[$_SESSION['lang'].'_'.$_SESSION['lang2']];
} else {
	$DATETIME = $_DATETIME[$_SESSION['lang']];
}


//No DB-connection and not the install-tool is running
if(!$db_connection && $ko_menu_akt != "install") {
	print '<div align="center" style="font-weight:900;color:red;">';
	print getLL("error_no_db_1")."<br /><br />";
	print getLL("error_no_db_2");
	print '</div>';
	print '<ul>';
	print '<li>'.getLL("error_no_db_reason_1").'</li>';
	print '<li>'.getLL("error_no_db_reason_2").'</li>';
	print '<li>'.getLL("error_no_db_reason_3").'</li>';
	print '</ul>';
	exit;
}


//Submenus (für alle Module)
include($BASE_PATH."inc/submenu.inc.php");

//Submenu-Behandlung
include($BASE_PATH."inc/submenu_actions.inc.php");


//Namen für die Frontmodule
$FRONTMODULES = array("daten_cal"       => array("modul" => "daten", "name" => getLL("fm_name_daten_cal")),
											"geburtstage"     => array("modul" => "leute", "name" => getLL("fm_name_geburtstage")),
											"mod"             => array("modul" => "leute,reservation,daten", "name" => getLL("fm_name_mod")),
											'fastfilter'      => array('modul' => 'leute', 'name' => getLL('fm_name_fastfilter')),
											"news"            => array("modul" => "", "name" => getLL("fm_name_news")),
											"adressaenderung" => array("modul" => "", "name" => getLL("fm_name_adressaenderung")),
											"today"           => array("modul" => "leute,daten,rota,reservation", "name" => getLL("fm_name_today")),
);
if(ENABLE_FILESHARE) $FRONTMODULES["fileshare"] = array("modul" => "fileshare", "name" => getLL("fm_name_fileshare"));

//Front-Modules
include($BASE_PATH."inc/front_modules.inc.php");

//Read in settings etc
if($ko_menu_akt != 'scheduler') {
	ko_init();
}

//Include swiftmailer
require_once($BASE_PATH.'inc/swiftmailer/swift_required.php');

//Include calendar for jsdate input fields
require($ko_path.'inc/calendar/calendar.php');
$js_calendar = new DHTML_Calendar($ko_path.'inc/calendar/', $ko_path.'images/', $_SESSION['lang'], 'calendar-system', false);



function ko_init() {
	global $db_connection, $ko_menu_akt, $BASE_URL;

	//Return during installation, as no DB connection and/or no DB tables are present yet
	if(!$db_connection || $ko_menu_akt == "install") return FALSE;
	//Allow post.php to be called without session or user
	if($ko_menu_akt == 'post.php') return;

	unset($GLOBALS["kOOL"]);

	//Check for valid user (not disabled since login)
	if($_SESSION['ses_userid'] != ko_get_guest_id()) {
		$ok = TRUE;
		$uid = intval($_SESSION['ses_userid']);
		if(!$uid) $ok = FALSE;

		$user = db_select_data('ko_admin', "WHERE `id` = '$uid'", '*', '', '', TRUE);
		if(!$user['id'] || $user['id'] != $uid || $user['disabled'] != '') $ok = FALSE;

		if(!$ok) {
			session_destroy();
			$_SESSION['ses_userid'] = ko_get_guest_id();
			$_SESSION['ses_username'] = 'ko_guest';
			header('Location: '.$BASE_URL.'index.php');
			return;
		}
	}

	//Read settings
	$settings = db_select_data("ko_settings", "WHERE 1", array("key", "value"));
	foreach($settings as $s) {
		$GLOBALS["kOOL"]["ko_settings"][$s["key"]] = $s["value"];
	}

	//Read userprefs for logged in user
	$userprefs = NULL;
	//db_select_data does not work here, as this table doesn't contain an unique_id column
	$rows = db_select_data('ko_userprefs', "WHERE `user_id` = '".$_SESSION['ses_userid']."'", '*', 'ORDER BY `key` ASC', '', FALSE, TRUE);
	foreach($rows as $row) {
		if($row["type"] != "") {
			$userprefs["TYPE@".$row["type"]][$row["key"]] = $row;
		} else {
			$userprefs[$row["key"]] = $row["value"];
		}
	}
	$GLOBALS["kOOL"]["ko_userprefs"] = $userprefs;

	//Set kota_filter if not in session yet
	if($_SESSION['ses_userid'] != ko_get_guest_id() && ko_get_userpref($_SESSION['ses_userid'], 'save_kota_filter') == 1 && !isset($_SESSION['kota_filter'])) {
		$_SESSION['kota_filter'] = unserialize(ko_get_userpref($_SESSION['ses_userid'], 'kota_filter'));
	}

	//Get all help entries for the current module
	$helps = db_select_data('ko_help', "WHERE `module` IN ('$ko_menu_akt', 'kota')", '*');
	foreach($helps as $help) {
		if($help['type'] == '') {
			$GLOBALS['kOOL']['ko_help'][$help['language']]['_notype'] = $help;
		} else {
			$GLOBALS['kOOL']['ko_help'][$help['language']][$help['type']] = $help;
		}
	}

}//ko_init()




/************************************************************************************************************************
 *                                                                                                                      *
 * MODULE UND BERECHTIGUNGEN                                                                                            *
 *                                                                                                                      *
 ************************************************************************************************************************/

/**
  * Checks whether a module is installed for a user
	*
	* If no userid is given as argument the current user will be checked
	* that is stored in $_SESSION["ses_userid"].
	*
	* @param string id of module to check for
	* @param int userid of user to check. If not set, value in $_SESSION["ses_userid"] will be used
	* @return boolean True if module is available to user, false otherwise
	*/
function ko_module_installed($m, $uid="") {
	if(defined("ALL_ACCESS")) return TRUE;

	if($uid) {
		ko_get_user_modules($uid, $modules);
	} else {
		ko_get_user_modules($_SESSION["ses_userid"], $modules);
	}
	if(in_array($m, $modules)) return TRUE;
	else return FALSE;
}//ko_module_installed()



/**
	* Get all modules a user is allowed to see
	*
	* @param int userid
	* @param array Contains the modules
	*/
function ko_get_user_modules($uid, &$m) {
	global $MODULES;

	//Get from cache
	if(isset($GLOBALS["kOOL"]["user_modules"][$uid])) {
		$m = $GLOBALS["kOOL"]["user_modules"][$uid];
		return;
	}

	if(defined("ALL_ACCESS")) {
		$m = $MODULES;
		return;
	}

	if(!$uid) {
		$uid = ko_get_guest_id();
	}

	$row = db_select_data("ko_admin", "WHERE `id` = '$uid'", "modules", "", "", TRUE);
	$m = explode(",", $row["modules"]);

	$groups = ko_get_admingroups($uid);
	foreach($groups as $group) {
		$row = db_select_data("ko_admingroups", "WHERE `id` = '".$group["id"]."'", "modules", "", "", TRUE);
		$m = array_merge($m, explode(",", $row["modules"]));
	}
	$m = array_unique($m);

	//Store in cache and return
	$GLOBALS["kOOL"]["user_modules"][$uid] = $m;
}//ko_get_user_modules()



/**
  * Returns an array of admingroups.
	*
	* If the first argument is set to a user id then the admingroups are returned
	* this user is being assigned to. Otherwise all admingroups are returned.
	*
	* @param int userid
	* @return array admingroups
	*/
function ko_get_admingroups($uid="") {
	//Get from cache
	if($uid && isset($GLOBALS["kOOL"]["admingroups"][$uid])) return $GLOBALS["kOOL"]["admingroups"][$uid];

	$groups = array();
	//get all groups
	if($uid == "") {
		$groups = db_select_data("ko_admingroups", "", "*", "ORDER BY name ASC");
	}
	//get groups for the specified account
	else {
		$row = db_select_data("ko_admin", "WHERE `id` = '$uid'", "admingroups", "", "", TRUE);
		foreach(explode(",", $row["admingroups"]) as $groupid) {
			if(!$groupid) continue;
			$group = db_select_data("ko_admingroups", "WHERE `id` = '$groupid'", "*", "", "", TRUE);
			$groups[$group["id"]] = $group;
		}
	}

	//Store in cache and return
	if($uid) $GLOBALS["kOOL"]["admingroups"][$uid] = $groups;
	return $groups;
}//ko_get_admingroups()




/**
  * Get ALL-Rights
	*/
function ko_get_access_all($col, $id="", &$max=0) {
	$max = 0;
	if(defined('ALL_ACCESS')) $id = ko_get_root_id();
	if(!$id) $id = $_SESSION['ses_userid'];

	//Fake access rights for tools module for root user
	if($col == 'tools' && $id == ko_get_root_id()) {
		$max = 4;
		return 4;
	}

	//Accept module name instead of col name as well
	if(substr($col, -6) != '_admin') {
		switch($col) {
			case 'reservation': $col = 'res_admin'; break;
			case 'admin': $col = 'admin'; break;
			case 'daten': $col = 'event_admin'; break;
			default: $col = $col.'_admin';
		}
	}

	if(isset($GLOBALS['kOOL']['admin_max'][$id][$col])) $max = $GLOBALS['kOOL']['admin_max'][$id][$col];
	if(isset($GLOBALS['kOOL']['admin_all'][$id][$col])) return $GLOBALS['kOOL']['admin_all'][$id][$col];

	$value = 0;
	//Check for settings for login
	$rights = db_select_data('ko_admin', "WHERE `id` = '$id'", '*', '', '', TRUE);
	foreach(explode(',', $rights[$col]) as $r) {
		if(FALSE === strpos($r, "@")) $value = $r;
		$max = max($max, substr($r, 0, 1));
	}
	//Check for settings for admingroups
	if($rights["admingroups"]) {
		$admingroups = db_select_data("ko_admingroups", "WHERE `id` IN ('".implode("','", explode(",", $rights["admingroups"]))."')");
		foreach($admingroups as $ag) {
			foreach(explode(",", $ag[$col]) as $r) {
				if(FALSE === strpos($r, "@")) $value = max($value, $r);
				$max = max($max, substr($r, 0, 1));
			}
			//Raise max rights for people module if a admin_filter is set for the given access level
			if($col == 'leute_admin') {
				$glaf = unserialize($ag['leute_admin_filter']);
				if($max < 3 && $glaf[3]) $max = 3;
				else if($max < 2 && $glaf[2]) $max = 2;
				else if($max < 1 && $glaf[1]) $max = 1;
			}
		}
	}

	//Raise max rights for people module if a admin_filter is set for the given access level
	if($col == 'leute_admin') {
		$laf = unserialize($rights['leute_admin_filter']);
		if($max < 3 && $laf[3]) $max = 3;
		else if($max < 2 && $laf[2]) $max = 2;
		else if($max < 1 && $laf[1]) $max = 1;
	}

	if($col == 'groups_admin') {
		if($max < 4) {
			if(db_get_count('ko_groups', 'id', "AND `rights_del` REGEXP '(^|,)$id(,|$)'") > 0) $max = 4;
		}
		if($max < 3) {
			if(db_get_count('ko_groups', 'id', "AND `rights_edit` REGEXP '(^|,)$id(,|$)'") > 0) $max = 3;
		}
		if($max < 2) {
			if(db_get_count('ko_groups', 'id', "AND `rights_new` REGEXP '(^|,)$id(,|$)'") > 0) $max = 2;
		}
		if($max < 1) {
			if(db_get_count('ko_groups', 'id', "AND `rights_view` REGEXP '(^|,)$id(,|$)'") > 0) $max = 1;
		}
	}

	$GLOBALS['kOOL']['admin_all'][$id][$col] = $value;
	$GLOBALS['kOOL']['admin_max'][$id][$col] = $max;

	return $value;
}//ko_get_access_all()




function ko_get_access($module, $uid='', $force=FALSE, $apply_admingroups=TRUE, $mode='login', $store_globally=TRUE) {
	global $access, $MODULES, $FORCE_KO_ADMIN;

	//Temporary array to hold the access rights within this function
	$_access = array();

	if(!in_array($module, $MODULES)) return FALSE;
	if($uid == '') $uid = $_SESSION['ses_userid'];
	if(defined('ALL_ACCESS')) $uid = ko_get_root_id();

	//Only reread access rights if force is set
	if(is_array($access[$module]) && $uid == $_SESSION['ses_userid'] && !$force) return TRUE;

	switch($module) {
		case 'rota':
		case 'leute':
		case 'fileshare':
		case 'kg':
		case 'tapes':
		case 'groups':
		case 'donations':
		case 'tracking':
		case 'projects':
			$col = $module.'_admin';
		break;
		case 'reservation':
			$col = 'res_admin';
		break;
		case 'admin':
			$col = 'admin';
		break;
		case 'daten':
			$col = 'event_admin';
		break;
		case 'tools':
			if($uid == ko_get_root_id()) {
				$access['tools'] = array('ALL' => 4, 'MAX' => 4);
			}
			return;
		break;
		default:
			if(in_array($module, $MODULES)) $col = $module.'_admin';
			else return FALSE;
	}

	//get rights for user from db
	if($mode == 'login') {
		$row = db_select_data('ko_admin', "WHERE `id` = '$uid'", '*', '', '', TRUE);
	} else {
		$row = db_select_data('ko_admingroups', "WHERE `id` = '$uid'", '*', '', '', TRUE);
	}
	$rights = explode(',', $row[$col]);
	foreach($rights as $r) {
		if(trim($r) == '') continue;
		if(strpos($r, '@') === FALSE) {  //No @ means ALL rights
			$_access[$module]['ALL'] = $r;
		} else {
			list($level, $id) = explode('@', $r);
			$_access[$module][$id] = max($_access[$module]['ALL'], $level);
		}
	}
	//Add access rights from admin groups
	if($row['admingroups'] != '' && $apply_admingroups) {
		$groups = db_select_data('ko_admingroups', "WHERE `id` IN ('".implode("','", explode(',', $row['admingroups']))."')");
		foreach($groups as $group) {
			$rights_group = explode(',', $group[$col]);
			foreach($rights_group as $r) {
				if(trim($r) == '') continue;
				if(strpos($r, '@') === FALSE) {  //No @ means ALL rights
					$_access[$module]['ALL'] = max($r, $_access[$module]['ALL']);
				} else {
					list($level, $id) = explode('@', $r);
					$_access[$module][$id] = max($_access[$module]['ALL'], $_access[$module][$id], $level);
				}
			}
		}
	}//if(apply_admingroups)

	if(defined('ALL_ACCESS')) {
		$_access[$module]['ALL'] = 4;
		foreach($_access[$module] as $id => $level) {
			$_access[$module][$id] = 4;
		}
	}


	switch($module) {
		case 'daten':
			$_access[$module]['REMINDER'] = $row['event_reminder_rights'];
			$egs = db_select_data('ko_eventgruppen', 'WHERE 1=1');
			foreach($egs as $eg) {
				if(ko_get_setting('daten_access_calendar') == 1 && $eg['calendar_id'] > 0) {
					//Access rights set by calendars or event groups
					if(isset($_access[$module]['cal'.$eg['calendar_id']])) {
						$_access[$module][$eg['id']] = max($_access[$module]['cal'.$eg['calendar_id']], $_access[$module]['ALL']);
					} else {
						$_access[$module][$eg['id']] = $_access[$module]['ALL'];
						$_access[$module]['cal'.$eg['calendar_id']] = $_access[$module]['ALL'];
					}
				} else {
					//Access rights set exclusively by event groups
					$_access[$module][$eg['id']] = max($_access[$module][$eg['id']], $_access[$module]['ALL']);
					//Set cal access rights, as they are needed e.g. to fill the KOTA form to enter a new event
					if($eg['calendar_id'] > 0) $_access[$module]['cal'.$eg['calendar_id']] = max($_access[$module]['cal'.$eg['calendar_id']], $_access[$module][$eg['id']]);
				}
				if ($apply_admingroups) $_access[$module]['REMINDER'] = max($_access[$module]['REMINDER'], $eg['event_reminder_rights']);
			}
		break;


		case 'tapes':
			$tapegroups = db_select_data('ko_tapes_groups', 'WHERE 1=1');
			foreach($tapegroups as $tapegroup) {
				$_access[$module][$tapegroup['id']] = max($_access[$module][$tapegroup['id']], $_access[$module]['ALL']);
			}
		break;


		case 'tracking':
			$trackings = db_select_data('ko_tracking', 'WHERE 1=1');
			foreach($trackings as $tracking) {
				$_access[$module][$tracking['id']] = max($_access[$module][$tracking['id']], $_access[$module]['ALL']);
			}
		break;


		case 'rota':
			$teams = db_select_data('ko_rota_teams', 'WHERE 1=1');
			foreach($teams as $team) {
				$_access[$module][$team['id']] = max($_access[$module][$team['id']], $_access[$module]['ALL']);
			}
		break;


		case 'donations':
			$accounts = db_select_data('ko_donations_accounts', 'WHERE 1=1');
			foreach($accounts as $account) {
				$_access[$module][$account['id']] = max($_access[$module][$account['id']], $_access[$module]['ALL']);
			}
		break;


		case 'reservation':
			$resgroups = db_select_data('ko_resgruppen', 'WHERE 1=1', '*', 'ORDER BY name ASC');
			foreach($resgroups as $rg) {
				$_access[$module]['grp'.$rg['id']] = max($_access[$module]['ALL'], $_access[$module]['grp'.$rg['id']]);
			}
			unset($resgroups);

			$items = db_select_data('ko_resitem', 'WHERE 1=1');
			foreach($items as $item) {
				if(isset($_access[$module][$item['id']])) {
					$_access[$module][$item['id']] = max($_access[$module]['ALL'], $_access[$module][$item['id']]);
					$_access[$module]['grp'.$item['gruppen_id']] = max($_access[$module]['grp'.$item['gruppen_id']], $_access[$module][$item['id']]);
				}
				else if(isset($_access[$module]['grp'.$item['gruppen_id']])) {
					$_access[$module][$item['id']] = max($_access[$module]['ALL'], $_access[$module]['grp'.$item['gruppen_id']]);
				} else {
					$_access[$module][$item['id']] = $_access[$module]['ALL'];
					$_access[$module]['grp'.$item['gruppen_id']] = $_access[$module]['ALL'];
				}
			}
			unset($items);
		break;


		case 'leute':
			$rights = array();
			//Always include hidden addresses
			$orig_value = ko_get_setting('leute_hidden_mode');
			ko_set_setting('leute_hidden_mode', 0);
			for($i=3; $i>$_access[$module]['ALL']; $i--) {
				if(FALSE !== apply_leute_filter('', $z_where, TRUE, $i, $uid, TRUE)) {
					$leute = db_select_data('ko_leute', 'WHERE 1=1 '.$z_where, 'id');
					if(sizeof($leute) > 0) {
						foreach($leute as $id => $p) {
							if(!isset($_access[$module][$id])) $_access[$module][$id] = $i;
						}
					} else {
						//If no address found with this filter but filter is allowed, then set dummy entry so MAX will be set
						$_access[$module][-1] = max($_access[$module][-1], $i);
					}
				}
			}
			ko_set_setting('leute_hidden_mode', $orig_value);
			unset($leute);
			//GS will be added at the end (after MAX has been set)
		break;


		case 'groups':
			if($_access[$module]['ALL'] < 4) $not_leaves = db_select_distinct('ko_groups', 'pid');

			$prefix = $mode == 'login' ? '' : 'g';

			$modes = array('', 'view', 'new', 'edit', 'del');
			for($level=4; $level > 0; $level--) {
				//Get access rights for single groups that are higher than the ALL rights
				if($_access[$module]['ALL'] < $level) {
					$where = "WHERE `rights_".$modes[$level]."` REGEXP '(^|,)".$prefix.$uid."(,|$)' ";
					//Add access rights from admin groups
					if($mode == 'login' && $row['admingroups'] != '' && $apply_admingroups) {
						$groups = db_select_data('ko_admingroups', "WHERE `id` IN ('".implode("','", explode(',', $row['admingroups']))."')");
						foreach($groups as $ag) {
							$where .= " OR `rights_".$modes[$level]."` REGEXP '(^|,)g".$ag['id']."(,|$)' ";
						}
					}

					${'grps'.$level} = db_select_data('ko_groups', $where, 'id');
					if(sizeof(${'grps'.$level}) > 0) {
						foreach(${'grps'.$level} as $grp) {
							$_access[$module][$grp['id']] = max($_access[$module][$grp['id']], $level);
							//Propagate rights to all children
							$children = array(); rec_groups($grp, $children, '', $not_leaves);
							foreach($children as $c) $_access[$module][$c['id']] = max($_access[$module][$c['id']], $level);
						}
					}
				}
			}

			//Add view rights for all parent groups, so tree to the groups gets visible
			$top_groups = array_unique(array_merge(array_keys((array)$grps1), array_keys((array)$grps2), array_keys((array)$grps3), array_keys((array)$grps4)));
			unset($grps1); unset($grps2); unset($grps3); unset($grps4);
			if(sizeof($top_groups) > 0) {
				ko_get_groups($all_groups);
				foreach($top_groups as $gid) {
					$motherline = ko_groups_get_motherline($gid, $all_groups);
					foreach($motherline as $id) {
						$_access[$module][$id] = max($_access[$module][$id], 1);
					}
				}
			}
		break;

		case 'fileshare':
		case 'kg':
		case 'admin':
			//Nothing
		break;

		default:
			$module_groups = hook_access_get_groups($module);
			foreach($module_groups as $module_group) {
				$_access[$module][$module_group['id']] = max($_access[$module][$module_group['id']], $_access[$module]['ALL']);
			}
	}


	//Apply FORCE_KO_ADMIN
	if($uid != ko_get_root_id()) {
		foreach($MODULES as $mod) {
			if(is_array($FORCE_KO_ADMIN[$mod])) {
				foreach($FORCE_KO_ADMIN[$mod] as $k => $v) {
					$_access[$mod][$k] = $v;
				}
			}
		}
	}


	//Add max value
	$_access[$module]['MAX'] = max($_access[$module]);

	//Store max and all values in Cache for ko_get_access_all()
	$GLOBALS['kOOL']['admin_all'][$uid][$col] = $_access[$module]['ALL'];
	$GLOBALS['kOOL']['admin_max'][$uid][$col] = $_access[$module]['MAX'];

	//Only add it here after MAX has been set, so this value won't be considered for the MAX value
	if($module == 'leute') {
		// Add right to moderate group subscriptions
		$gs = db_select_data('ko_admin', "WHERE `id` = '$uid'", 'leute_admin_gs', '', '', TRUE);
		$_access[$module]['GS'] = $gs['leute_admin_gs'] == 1;

		//Check admingroups for GS setting
    if($row['admingroups'] != '' && $apply_admingroups) {
      $groups = db_select_data('ko_admingroups', "WHERE `id` IN ('".implode("','", explode(',', $row['admingroups']))."')");
      foreach($groups as $group) {
        if($group['leute_admin_gs'] == 1) $_access[$module]['GS'] = TRUE;
      }
    }//if(apply_admingroups)
	}

	//Usually the access rights will be stored in the global array $access
	if($store_globally) {
		//Reset access rights before (re)building them
		unset($access[$module]);
		$access[$module] = $_access[$module];
		return;
	}
	//But when reading the access rights for another user it shouldn't overwrite one's own access rights
	else {
		return $_access;
	}
	unset($_access);
}//ko_get_access()





/**
 * Get columns the given login / admingroup has access to for a given KOTA table
 *
 * Reads data from ko_admin and/or ko_admingroups to find KOTA columns for the given table this user
 * has access to. Columns from admingroups and login are summed.
 * Used in ko_include_kota() to unset columns with no access to
 *
 * @param $loginid int ID of login or admingroup to be checked
 * @param $table string Name of DB table (as set in $KOTA)
 * @param $mode string Can be all to get summed access rights or login or admingroup to only get access
 *                     for the login/admingroup itself
 * @return $cols array If array is empty then user has access to all columns, otherwise only to the ones in the array
 */
function ko_access_get_kota_columns($loginid, $table, $mode='all') {
	$cols = array();

	//TODO: Make configurable, but not in KOTA as this is not loaded
	$tables = array('ko_kleingruppen');
	if(!in_array($table, $tables)) return $cols;

	if($mode == 'login' || $mode == 'all') {
		$row = db_select_data('ko_admin', "WHERE `id` = '$loginid'", 'kota_columns_'.$table, '', '', TRUE);
	} else if($mode == 'admingroup') {
		$row = db_select_data('ko_admingroups', "WHERE `id` = '$loginid'", 'kota_columns_'.$table, '', '', TRUE);
	}
	if(trim($row['kota_columns_'.$table]) != '') {
		$cols = explode(',', $row['kota_columns_'.$table]);
	}

	if($mode == 'all') {
		$admingroups = ko_get_admingroups($loginid);
		foreach($admingroups as $group) {
			$row = db_select_data('ko_admingroups', "WHERE `id` = '".$group['id']."'", 'kota_columns_'.$table, '', '', TRUE);
			if(trim($row['kota_columns_'.$table]) != '') {
				$cols2 = explode(',', $row['kota_columns_'.$table]);
				$cols = array_merge($cols, $cols2);
			}
		}
	}
	$cols = array_unique($cols);
	foreach($cols as $k => $v) {
		if(!$v) unset($cols[$k]);
	}

	return $cols;
}//ko_access_get_kota_columns()


/**
 * @param $reminder an array containing at least the 'cruser' of the reminder, or the reminderId
 * @return bool true, if the logged in user may access this reminder
 */
function ko_get_reminder_access ($reminder) {
	global $access;

	if (!isset($access['daten'])) ko_get_access('daten');

	if (!is_array($reminder)) ko_get_reminders($reminder, 1, ' and `id` = ' . $reminder, '', '', TRUE, TRUE);

	if ($access['daten']['REMINDER'] == 0) {
		return false;
	}
	else {
		if ($_SESSION['ses_userid'] == $reminder['cruser']) {
			return true;
		}
		else {
			if ($access['daten']['ALL'] >= 3) {
				return true;
			}
			else {
				return false;
			}
		}
	}
} // ko_get_reminder_access()



/**
	* Saves admin data in ko_admin
	*
	* Stores access rights for modules, password, available modules etc. in ko_admin
	*
	* @param string module id or other column to store data for
	* @param int user id or admingroup id to store the data for
	* @param string Value to be stored
	* @param string Stores the data for a login if set to "login", for an admingroup otherwise
	* @return True on success, false on failure
	*/
function ko_save_admin($module, $uid, $string, $type="login") {
	global $MODULES;

	switch($module) {
		case "daten": $col = "event_admin"; break;
		case "leute": $col = "leute_admin"; break;
		case "leute_filter": $col = "leute_admin_filter"; break;
		case "leute_spalten": $col = "leute_admin_spalten"; break;
		case "leute_groups": $col = "leute_admin_groups"; break;
		case "leute_gs": $col = "leute_admin_gs"; break;
		case "leute_assign": $col = "leute_admin_assign"; break;
		case "reservation": $col = "res_admin"; break;
		case "admin": $col = "admin"; break;
		case 'rota': $col = 'rota_admin'; break;
		case "fileshare": $col = "fileshare_admin"; break;
		case "kg": $col = "kg_admin"; break;
		case "tapes": $col = "tapes_admin"; break;
		case "groups": $col = "groups_admin"; break;
		case "donations": $col = "donations_admin"; break;
		case "tracking": $col = "tracking_admin"; break;
		case 'sms': $col = ''; break;
		case 'mailing': $col = ''; break;
		case 'tools': $col = ''; break;
		case "projects": $col = "projects_admin"; break;
		case "modules": $col = "modules"; break;
		case "admingroups": $col = "admingroups"; break;
		case "login": $col = "login"; break;
		case "name": $col = "name"; break;
		case "password": $col = "password"; break;
		case "kota_columns_ko_kleingruppen": $col = "kota_columns_ko_kleingruppen"; break;
		case "daten_force_global": $col = "event_force_global"; break;
		case "daten_reminder_rights": $col = "event_reminder_rights"; break;
		case "reservation_force_global": $col = "res_force_global"; break;
		default:
			if(in_array($module, $MODULES)) $col = $module.'_admin';
			else $col = "";
	}//switch(module)

	if(!isset($uid)) return FALSE;
	if($col == "") return FALSE;

	if($type == "login") {
		db_update_data('ko_admin', "WHERE `id` = '$uid'", array($col => format_userinput($string, 'text')));
	} else {
		db_update_data('ko_admingroups', "WHERE `id` = '$uid'", array($col => format_userinput($string, 'text')));
	}

	//Unset cached value in GLOBALS[kOOL]
	if(isset($GLOBALS["kOOL"][$col][$uid][$type])) unset($GLOBALS["kOOL"][$col][$uid][$type]);

	return TRUE;
}


/**
 * Checks whether the login should only see reservations/events specified by the global time filter
 *
 * @param string The module, either 'reservation' or 'daten'
 * @param int Login id
 * @return boolean
 */
function ko_get_force_global_time_filter($module, $id) {

	$moduleMapper = array('reservation' => 'res', 'daten' => 'event');
	$module = $moduleMapper[$module];
	if(!$module) return FALSE;

	$adminGroups = ko_get_admingroups($id);
	$admin = db_select_data('ko_admin', "where id = $id", $module . "_force_global", '', '', true, true);

	if ($admin[$module . '_force_global'] == 1) return true;
	foreach ($adminGroups as $ag) {
		if ($ag[$module . '_force_global'] == 1) return true;
	}
	return false;
}



/**
  * Returns the array of admin-filters for the given login.
	*
	* This array defines the global filter that is to be applied to ko_leute always for the given login.
	*
	* @param int user id or admingroup id
	* @param string Get filter for login if set to "login", for admingroup otherwise
	* @return array Filter to be applied
	*/
function ko_get_leute_admin_filter($id, $mode="login") {
	//Get from cache
	if(isset($GLOBALS["kOOL"]["leute_admin_filter"][$id][$mode])) return $GLOBALS["kOOL"]["leute_admin_filter"][$id][$mode];

	if($mode == "login") {
		$row = db_select_data("ko_admin", "WHERE `id` = '$id'", "leute_admin_filter", "", "", TRUE);
	} else if($mode == "admingroup") {
		$row = db_select_data("ko_admingroups", "WHERE `id` = '$id'", "leute_admin_filter", "", "", TRUE);
	} else {
		throw new InvalidArgumentException("\$mode must be either 'login' or 'admingroup' but was '$mode'");
	}

	//Store in cache and return
	$r = unserialize($row["leute_admin_filter"]);

	//For backwards compatibility: If no value was set, then use name as value (which is still the case for filter presets)
	foreach($r as $k => $v) {
		if(isset($r[$k]['name']) && !isset($r[$k]['value'])) $r[$k]['value'] = $r[$k]['name'];
	}

	$GLOBALS["kOOL"]["leute_admin_filter"][$id][$mode] = $r;
	return $r;
}//ko_get_leute_admin_filter()



/**
 * Returns the columns of ko_leute, for which the user has [view] and [edit] rights.
 *
 * Remark: Remember special cols like groups, smallgroups.
 * They are not included here, but retrieved from the corresponding module-rights
 *
 * @param int user id or admingroup id
 * @param string Get filter for login if set to "login", for admingroup otherwise
 * @param int Person id to check access for. Use -1 for non existent entities.
 * @return array Array with view and edit rights or FALSE if no limitations exist
 */
function ko_get_leute_admin_spalten($userid, $mode="login", $pid=0) {
	global $FORCE_KO_ADMIN, $LEUTE_ADMIN_SPALTEN_CONDITION;

	//Get from cache
	if (isset($GLOBALS["kOOL"]["leute_admin_spalten"][$userid][$mode][$pid]))
		return $GLOBALS["kOOL"]["leute_admin_spalten"][$userid][$mode][$pid];

	//>0 means editing. -1 means new address (from ko_leute_mod and add_person)
	if(($pid > 0 || $pid == -1) && is_array($LEUTE_ADMIN_SPALTEN_CONDITION)) {
		$lasc = $LEUTE_ADMIN_SPALTEN_CONDITION[$userid];
		if(!is_array($lasc)) $lasc = $LEUTE_ADMIN_SPALTEN_CONDITION['ALL'];
		if(is_array($lasc)) {
			$p = db_select_data('ko_leute', "WHERE `id` = '$pid'", '*', '', '', TRUE);
			foreach(explode(',', $lasc['dontapply']) as $_col) {
				if(substr($_col, 0, 1) == '!') {
					$_col = substr($_col, 1);
					if(!$p[$_col]) return FALSE;
				} else {
					if($p[$_col]) return FALSE;
				}
			}
		}
	}

	if($mode == "login" || $mode == "all") {
		$cols = db_select_data("ko_admin", "WHERE `id` = '$userid'", "leute_admin_spalten", "", "", TRUE);
		$return = unserialize($cols["leute_admin_spalten"]);
	} else if($mode == "admingroup") {
		$cols = db_select_data("ko_admingroups", "WHERE `id` = '$userid'", "leute_admin_spalten", "", "", TRUE);
		$return = unserialize($cols["leute_admin_spalten"]);
	}

	if($mode == "all") {
		$admingroups = ko_get_admingroups($userid);
		foreach($admingroups as $group) {
			$group_cols = db_select_data("ko_admingroups", "WHERE `id` = '".$group["id"]."'", "leute_admin_spalten", "", "", TRUE);
			$cols = unserialize($group_cols["leute_admin_spalten"]);
			if(sizeof($cols) > 0) $return = array_merge_recursive((array)$return, (array)$cols);
		}
	}

	//Unset empty entries
	if(is_array($return)) {
		
		//Return FALSE if no cols were found, so all will get displayed
		if(sizeof($return) == 0) $return = FALSE;

		foreach($return as $k => $v) {
			if(!$v) unset($return[$k]);
		}
	} else {
		//If not an array, then probably just '0', which means all columns may be displayed
		$return = FALSE;
	}

	//Check for forced access rights
	if(isset($FORCE_KO_ADMIN["leute_admin_spalten"])) {
		$return = unserialize($FORCE_KO_ADMIN["leute_admin_spalten"]);
	}

	//Propagate view rights to edit rights if edit is not set at all
	if(is_array($return['view']) && !$return['edit']) {
		$return['edit'] = $return['view'];
	}

	//Store in cache and return
	$GLOBALS["kOOL"]["leute_admin_spalten"][$userid][$mode][$pid] = $return;
	return $return;
}//ko_get_leute_admin_spalten()




function ko_get_leute_admin_groups($userid, $mode='login') {
	$r = FALSE;

	//Get from cache
	if(isset($GLOBALS['kOOL']['leute_admin_groups'][$userid][$mode])) return $GLOBALS['kOOL']['leute_admin_groups'][$userid][$mode];

	if($mode == 'login' || $mode == 'all') {
		$groups = db_select_data('ko_admin', "WHERE `id` = '$userid'", 'leute_admin_groups', '', '', TRUE);
		$r[] = $groups['leute_admin_groups'];
	} else if($mode == 'admingroup') {
		$groups = db_select_data('ko_admingroups', "WHERE `id` = '$userid'", 'leute_admin_groups', '', '', TRUE);
		$r[] = $groups['leute_admin_groups'];
	}

	if($mode == 'all') {
		$admingroups = ko_get_admingroups($userid);
		foreach($admingroups as $group) {
			$r[] = $group['leute_admin_groups'];
		}
	}

	//Unset empty entries
	if(is_array($r)) {
		foreach($r as $k => $v) if(!$v) unset($r[$k]);
	}

	//Store in cache
	$GLOBALS['kOOL']['leute_admin_groups'][$userid][$mode] = $r;

	if(sizeof($r) > 0) {
		return $r;
	} else {
		return FALSE;
	}
}//ko_get_leute_admin_groups()




function ko_get_leute_admin_assign($userid, $mode='login') {
	$r = FALSE;

	//Get from cache
	if(isset($GLOBALS['kOOL']['leute_admin_assign'][$userid][$mode])) return $GLOBALS['kOOL']['leute_admin_assign'][$userid][$mode];

	if($mode == 'login' || $mode == 'all') {
		$assign = db_select_data('ko_admin', "WHERE `id` = '$userid'", 'leute_admin_assign', '', '', TRUE);
		$r[] = $assign['leute_admin_assign'];
	} else if($mode == 'admingroup') {
		$assign = db_select_data('ko_admingroups', "WHERE `id` = '$userid'", 'leute_admin_assign', '', '', TRUE);
		$r[] = $assign['leute_admin_assign'];
	}

	if($mode == 'all') {
		$admingroups = ko_get_admingroups($userid);
		foreach($admingroups as $group) {
			$r[] = $group['leute_admin_assign'];
		}
	}

	//Unset empty entries
	if(is_array($r)) {
		foreach($r as $k => $v) if(!$v) unset($r[$k]);
	}

	//Only allow if admin_group is set
	if(FALSE === ko_get_leute_admin_groups($userid, $mode)) $r = FALSE;

	//Store in cache
	$GLOBALS['kOOL']['leute_admin_assign'][$userid][$mode] = $r;

	if(sizeof($r) > 0) {
		return $r;
	} else {
		return FALSE;
	}
}//ko_get_leute_admin_assign()







/************************************************************************************************************************
 *                                                                                                                      *
 * USER UND EINSTELLUNGEN                                                                                               *
 *                                                                                                                      *
 ************************************************************************************************************************/

/**
	* Get the people id of the logged in id
	*
	* @return int id in ko_leute of the person assigned to the logged in user
	*/
function ko_get_logged_in_id($id="") {
	$lid = $id ? $id : $_SESSION["ses_userid"];
	if(!$lid) return FALSE;

	$row = db_select_data('ko_admin', "WHERE `id` = '$lid'", 'id,leute_id', '', '', TRUE);
	if(is_array($row)) {
		return $row["leute_id"];
	} else {
		return "";
	}
}



/**
  * Returns the person assigned to the currently logged in user
	* If an admin email is set for this login, this will be returned as the email field for this person (if admin=TRUE)
	*/
function ko_get_logged_in_person($id='') {
	global $LEUTE_EMAIL_FIELDS, $LEUTE_MOBILE_FIELDS;

	$lid = $id ? $id : $_SESSION['ses_userid'];
	if(!$lid) return FALSE;

	$person = db_select_data("ko_admin AS a LEFT JOIN ko_leute as l ON a.leute_id = l.id",
												 "WHERE a.id = '$lid' AND (a.disabled = '0' OR a.disabled = '')",
												 "l.*, a.email AS admin_email",
												 '', '', TRUE);
	//Set email from one of the email fields
	if(sizeof($LEUTE_EMAIL_FIELDS) > 1) {
		ko_get_leute_email($person, $email);
		$person['email'] = array_shift($email);
	}
	//Set mobile from one of the mobile fields
	if(sizeof($LEUTE_MOBILE_FIELDS) > 1) {
		ko_get_leute_mobile($person, $mobile);
		$person['natel'] = array_shift($mobile);
	}
	//Overwrite person's email address with admin email from login
	if($person['admin_email']) $person['email'] = $person['admin_email'];

	return $person;
}



/**
  * Get date and time of the last login for a given login
	*
	* @param int user id. $_SESSION["ses_userid"] is being used not given
	* @return datetime SQL datetime value of last login
	*/
function ko_get_last_login($uid="") {
	$uid = $uid ? $uid : $_SESSION["ses_userid"];
	if(!$uid) return FALSE;

	$row = db_select_data('ko_admin', "WHERE `id` = '".$_SESSION['ses_userid']."'", 'id,last_login', '', '', TRUE);
	if(is_array($row)) {
		return $row["last_login"];
	} else {
		return "";
	}
}//ko_get_last_login()




/**
	* Get the id of the special login ko_guest
	*
	* @return int user id of ko_guest
	*/
function ko_get_guest_id() {
	if(isset($GLOBALS["kOOL"]["guest_id"])) return $GLOBALS["kOOL"]["guest_id"];

	$row = db_select_data('ko_admin', "WHERE `login` = 'ko_guest'", 'id', '', '', TRUE);
	if(is_array($row)) {
		$GLOBALS["kOOL"]["guest_id"] = $row["id"];
		return $row["id"];
	} else {
		return FALSE;
	}
}



/**
	* Get the id of the special login root
	*
	* @return int user id of root
	*/
function ko_get_root_id() {
	if($GLOBALS["kOOL"]["root_id"]) return $GLOBALS["kOOL"]["root_id"];

	$row = db_select_data('ko_admin', "WHERE `login` = 'root'", 'id', '', '', TRUE);
	if(is_array($row)) {
		$GLOBALS["kOOL"]["root_id"] = $row["id"];
		return $row["id"];
	} else {
		return FALSE;
	}
}



/**
 * Get a setting from ko_settings
 *
 * @param string Key to get setting for
 * @param boolean Set to true to force rereading setting from db
 * @return mixed Value for the specified key
 */
function ko_get_setting($key, $force=FALSE) {
	global $db_connection, $LEUTE_NO_FAMILY;
	//Get from cache

	if(!$force && isset($GLOBALS['kOOL']['ko_settings'][$key])) {
		$result = $GLOBALS['kOOL']['ko_settings'][$key];
	}
	else {
		$query = "SELECT `value` from `ko_settings` WHERE `key` = '$key' LIMIT 1";
		$result = mysqli_query($db_connection, $query);
		$row = mysqli_fetch_row($result);
		$result = $row[0];
	}

	if ($key == 'leute_col_name' && $LEUTE_NO_FAMILY) {
		$temp = unserialize($result);
		foreach(array('en', 'de', 'it', 'fr', 'nl') as $lan) {
			unset($temp[$lan]['famid']);
			unset($temp[$lan]['kinder']);
			unset($temp[$lan]['famfunction']);
		}
		$result = serialize($temp);
	}

	$GLOBALS["kOOL"]["ko_settings"][$key] = $result;
	return $result;
}//ko_get_setting()


/*
 * Stores a setting in ko_settings
 *
 * @param string Key of the setting to be stored
 * @param mixed Value to be stored
 * @return boolean True on succes, false on failure
 */
function ko_set_setting($key, $value) {
	if(db_get_count('ko_settings', 'key', "AND `key` = '$key'") == 0) {
		db_insert_data('ko_settings', array('key' => $key, 'value' => format_userinput($value, 'text')));
	} else {
		db_update_data('ko_settings', "WHERE `key` = '$key'", array('value' => format_userinput($value, 'text')));
	}
	$GLOBALS['kOOL']['ko_settings'][$key] = $value;

	return TRUE;
}//ko_set_setting()



/**
 * Get a user preference as stored in ko_userprefs
 *
 * @param int user id
 * @param string Key of user preference
 * @param string Type of user preference to get
 * @param string ORDER BY statement to pass to the db
 * @param boolean Set to true to have the userpref read from DB instead of from cache
 * @return mixed Value of user preference
 */
function ko_get_userpref($id, $key="", $type="", $order="", $force=FALSE) {
	global $db_connection;

	if($type != "") {
		if($key != "") {
			//Look up userpref in GLOBALS
			if(!$force && $id == $_SESSION["ses_userid"] && isset($GLOBALS["kOOL"]["ko_userprefs"]["TYPE@".$type][$key]))
				return array($GLOBALS["kOOL"]["ko_userprefs"]["TYPE@".$type][$key]);
			//Get it from DB if not set
			$query = "SELECT * FROM `ko_userprefs` WHERE `user_id` = '$id' AND `key` = '$key' AND `type` = '$type' $order";
		} else {
			//Look up userpref in GLOBALS
			if(!$force && $id == $_SESSION["ses_userid"] && is_array($GLOBALS["kOOL"]["ko_userprefs"]["TYPE@".$type]))
				return $GLOBALS["kOOL"]["ko_userprefs"]["TYPE@".$type];
			//Get it from DB if not set
			$query = "SELECT * FROM `ko_userprefs` WHERE `user_id` = '$id' AND `type` = '$type' $order";
		}
		$result = mysqli_query($db_connection, $query);
		$r = array();
		while($row = mysqli_fetch_assoc($result)) {
			$r[] = $row;
		}
		return $r;
	} else {
		//Look up userpref in GLOBALS
		if(!$force && $id == $_SESSION["ses_userid"] && isset($GLOBALS["kOOL"]["ko_userprefs"][$key]))
			return $GLOBALS["kOOL"]["ko_userprefs"][$key];
		//Get it from DB if not set
		$query = "SELECT * FROM `ko_userprefs` WHERE `user_id` = '$id' AND `key` = '$key' $order";
		$result = mysqli_query($db_connection, $query);
		$row = mysqli_fetch_assoc($result);
		return $row['value'];
	}
}//ko_get_userpref()


/**
	* Store a user preference in ko_userprefs
	*
	* @param int user id
	* @param string Key of user preference
	* @param mixed Value to be stored
	* @param string Type of user preference to store
	*/
function ko_save_userpref($id, $key, $value, $type="") {
	$id = format_userinput($id, "int");
	$key = format_userinput($key, "text");
	$type = format_userinput($type, "alphanum+");

	//Store in db
	if(db_get_count('ko_userprefs', 'key', "AND `user_id`= '$id' AND `key` = '$key' AND `type` = '$type'") >= 1) {
		db_update_data('ko_userprefs', "WHERE `user_id` = '$id' AND `key` = '$key' AND `type` = '$type'", array('value' => $value));
  } else {  //...sonst neues einfügen
		db_insert_data('ko_userprefs', array('user_id' => $id, 'type' => $type, 'key' => $key, 'value' => $value));
  }

	//Save in GLOBALS as well (but only for logged in user)
	if($id == $_SESSION["ses_userid"]) {
		if($type != "") {
			$GLOBALS["kOOL"]["ko_userprefs"]["TYPE@".$type][$key] = array("type" => $type, "key" => $key, "value" => $value);
		} else {
			$GLOBALS["kOOL"]["ko_userprefs"][$key] = $value;
		}
	}
}//ko_save_userpref()


/**
	* Delete a user preference
	*
	* @param int user id
	* @param string Key to be deleted
	* @param string Type of preference to be deleted
	*/
function ko_delete_userpref($id, $key, $type="") {
	$id = format_userinput($id, "int");
	$key = format_userinput($key, "text");
	$type = format_userinput($type, "alphanum+");

	//Delete from DB
	db_delete_data('ko_userprefs', "WHERE `user_id` = '$id' AND `key` = '$key' AND `type` = '$type'");

	//Delete from cache
	if($type != '') {
		unset($GLOBALS['kOOL']['ko_userprefs']['TYPE@'.$type][$key]);
	} else {
		unset($GLOBALS['kOOL']['ko_userprefs'][$key]);
	}
}//ko_delete_userpref()


/**
 * Checks whether a given user preference is set in ko_userprefs
 *
 * @param int user id
 * @param string Key to be checked for
 * @param string Type of preference to be checked for
 */
function ko_check_userpref($id, $key, $type="") {
	global $db_connection;

	$id = format_userinput($id, "int");
	$key = format_userinput($key, "text");
	$type = format_userinput($type, "alphanum+");

	if($type != "") {
		$query = "SELECT `key`, `value` FROM `ko_userprefs` WHERE `user_id` = '$id' AND `key` = '$key' AND `type` = '$type'";
	} else {
		$query = "SELECT `value` FROM `ko_userprefs` WHERE `user_id` = '$id' AND `key` = '$key'";
	}
	$result = mysqli_query($db_connection, $query);
	$row = mysqli_fetch_assoc($result);

	return (sizeof($row) >= 1);
}//ko_check_userpref()








/************************************************************************************************************************
 *                                                                                                                      *
 * FILTER                                                                                                               *
 *                                                                                                                      *
 ************************************************************************************************************************/

/**
	* Returns a single filter from ko_filter
	*
	* @param int Filter id
	* @param array Filter
	*/
function ko_get_filter_by_id($id, &$f) {
	ko_get_filters($all_filters, "leute", TRUE);
	$f = $all_filters[$id];
}//ko_get_filter_by_id()


/**
	* Get filters by type (e.g. type="leute")
	*
	* @param array Filters
	* @param string Type of filters to get
	* @param boolean Get all filter if true, if false only get the allowed filters for the logged in user
	*/
function ko_get_filters(&$f, $typ, $get_all=FALSE, $order='name') {
	global $LEUTE_NO_FAMILY;

	if($order == 'name' && isset($GLOBALS['kOOL']['ko_filter'][$typ][($get_all?'all':'notall')])) {
		$f = $GLOBALS['kOOL']['ko_filter'][$typ][($get_all?'all':'notall')];
		return;
	}

	$map_sort_groups = array('person' => 1, 'com' => 2, 'status' => 3, 'family' => 4, 'groups' => 5, 'smallgroup' => 6, 'misc' => 7);

	//Prepare the filters, that are not to be display because this user is not allowed to view this column
	$allowed_cols = ko_get_leute_admin_spalten($_SESSION["ses_userid"], "all");
	if(is_array($allowed_cols["view"]) && sizeof($allowed_cols["view"]) > 0 && ko_module_installed("groups", $_SESSION["ses_userid"])) {
		$allowed_cols["view"] = array_merge($allowed_cols["view"], array("groups", "roles"));
	}
	//Add column event, which is used for the rota filter
	if(is_array($allowed_cols['view']) && sizeof($allowed_cols['view']) > 0 && ko_module_installed('rota', $_SESSION['ses_userid'])) {
		$allowed_cols['view'] = array_merge($allowed_cols['view'], array('event'));
	}

	$f = $_f = array();
	$orderby = $order == 'name' ? 'ORDER BY `name` ASC' : 'ORDER BY `group` ASC, `name` ASC';
	$rows = db_select_data('ko_filter', "WHERE `typ` = '$typ'", '*', $orderby);
	foreach($rows as $row) {
		if(!$get_all) {
			if($row['name'] == 'donation' && !ko_module_installed('donations', $_SESSION['ses_userid'])) continue;
			if($row['name'] == 'logins' && ko_get_access_all('admin') < 5) continue;
			if($row['name'] == 'duplicates') {  //Only show duplicates filter if access level allows editing and deleting
				ko_get_access_all('leute', $_SESSION['ses_userid'], $max_leute);
				if($max_leute < 3) continue;
			}
			//Filters for the small group module
			if((in_array($row['name'], array('smallgroup', 'smallgrouproles')) || substr($row['dbcol'], 0, strpos($row['dbcol'], '.')) == 'ko_kleingruppen') && !ko_module_installed('kg')) continue;

			//Filters for the rota module
			if(substr($row['dbcol'], 0, strpos($row['dbcol'], '.')) == 'ko_rota_schedulling' && !ko_module_installed('rota')) continue;

			//Don't return filters for columns, that are not allowed
			if(is_array($allowed_cols["view"]) && sizeof($allowed_cols["view"]) > 0) {
				$ok = FALSE;

				//Get DB column from the column ko_filter.dbcol
				if($row['dbcol'] != '' && FALSE === strpos($row['dbcol'], '.')) {
					$dbcol = $row['dbcol'];
					//Check for allowed column
					if(in_array($dbcol, $allowed_cols['view'])) $ok = TRUE;
				} else {
					$ok = TRUE;
				}

				if(!$ok) continue;
			}
		}//if(!get_all)

		//special filters for other tables
		for($i=1; $i<4; $i++) {
			if(substr($row["code$i"], 0, 4) == "FCN:") {
				$fcn = substr($row["code$i"], 4);
				if(strpos($fcn, ":")) {  //Find parameters given along with the function name (e.g. used for enum_ll)
					$params = explode(":", $fcn);
					$fcn = $params[0];
					if(function_exists($fcn)) eval("$fcn(\$code, \$params);");
				} else {
					if(function_exists($fcn)) eval("$fcn(\$code);");
				}
				$row["code$i"] = $code;
			}
		}

		//Locallang-values if set
		$ll_name = getLL("filter_".$row["name"]);
		foreach(array("var1", "var2", "var3") as $var) {
			$ll_var = getLL("filter_".$var."_".$row["name"]);
			$row[$var] = $ll_var ? $ll_var : ($ll_name ? $ll_name : $row[$var]);
		}
		$row["_name"] = $row["name"];  //Keep name as in db table for comparisons
		$row["name"] = $ll_name ? $ll_name : $row["name"];

		//If no group is defined, set it to misc
		if($row['group'] == '') $row['group'] = 'misc';

		$_f[$row["id"]] = $row;

		//prepare for ll sorting
		$filter_sort[$row['id']] = $order == 'name' ? $row['name'] : $map_sort_groups[$row['group']].$row['name'];
	}

	//Sort filters by the localized name
	asort($filter_sort);
	foreach($filter_sort as $id => $name) {
		$f[$id] = $_f[$id];
	}

	// unset family filter option if $LEUTE_NO_FAMILY is set to true
	if ($LEUTE_NO_FAMILY) {
		foreach($f as $k => $ff) {
			if ($ff['group'] == 'family') {
				unset($f[$k]);
			}
		}
	}

	$GLOBALS['kOOL']['ko_filter'][$typ][($get_all?'all':'notall')] = $f;
}//ko_get_filters()



/**
  * Tries to find the column a filter is applied to
	*
	* Will be obsolete soon, after storing this information in a new db column in ko_filter
	*/
function ko_get_filter_column($sql) {
	$remove = explode(",", "(,),1,2,3,4,5,6,7,8,9,0,A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z,-,>,<,=,+,*,/,[,],',`, ");
	while(strlen($sql) > 0 && in_array(substr($sql, 0, 1), $remove)) $sql = substr($sql, 1);

	$keep = explode(",", "a,b,c,d,e,f,g,h,i,j,k,l,m,n,o,p,q,r,s,t,u,v,w,x,y,z,_,1,2,3,4,5,6,7,8,9,0");
	for($i=0; $i<strlen($sql); $i++) {
		if(!in_array(substr($sql, $i, 1), $keep)) {
			$sql = substr($sql, 0, $i);
		}
	}
	return $sql;
}//ko_get_filter_column()



/**
  * Generate a special filter for group datafields
	*
	* This function is called by the filter for group datafields by a FCN: definition in the SQL column
	* It generates the necessary code for the filter dynamically
	*
	* @param string HTML code for the filter
	*/
function ko_specialfilter_groupdatafields(&$code) {
	//Only get groups with datafields set and exclude expired groups according to userpref
	$where = "WHERE `datafields` != '' ";
	if(ko_get_userpref($_SESSION['ses_userid'], 'show_passed_groups') != 1) {
		$where .= "AND (`start` < CURDATE() AND (`stop` = '0000-00-00' OR `stop` > CURDATE()))";
	}
	$df_groups = db_select_data('ko_groups', $where);


	$code = '<select name="var1" size="0" onchange="sendReq('."'../groups/inc/ajax.php', 'action,dfid,sesid', 'groupdatafieldsfilter,'+this.options[this.selectedIndex].value+',".session_id()."', do_element);".'">';
	$code .= '<option value=""></option>';

	//get reusable first
	$dfs = db_select_data("ko_groups_datafields", "WHERE `reusable` = '1' AND `preset` = '0'", "*", "ORDER BY description ASC");
	foreach($dfs as $df) {
		$code .= '<option value="'.$df['id'].'" title="'.$df['description'].'">'.ko_html($df['description']).'</option>';
	}


	//add group specific afterwards
	$dfs = db_select_data("ko_groups_datafields", "WHERE `reusable` = '0' AND `preset` = '0'", "*", "ORDER BY description ASC");
	foreach($dfs as $df) {
		//find first group, this datafield is used in and use this a description
		$group_name = "";
		foreach($df_groups as $group) {
			if(strstr($group["datafields"], $df["id"])) {
				$group_name = $group["name"];
				break;
			}
		}
		//Don't display unused datafields
		if(!$group_name) continue;

		$code .= '<option value="'.$df['id'].'" title="'.$df['description'].' ('.$group_name.')">'.ko_html($df['description']).' ('.$group_name.')</option>';
	}
	$code .= '</select>';
}//ko_specialfilter_groupdatafields()


/**
  * Generate a special filter for smallgroup regions
	*
	* This function is called by the filter for smallgroup regions by a FCN: definition in the SQL column
	* It generates the necessary code for the filter dynamically
	*
	* @param string HTML code for the filter
	*/
function ko_specialfilter_kleingruppen_region(&$code) {
	$code = '<select name="var1" size="0"><option value=""></option>';
	$rows = db_select_distinct("ko_kleingruppen", "region", "", "", TRUE);
	foreach($rows as $row) {
		if(!$row) continue;
		$code .= '<option value="'.$row.'" title="'.$row.'">'.ko_html($row).'</option>';
	}
	$code .= '</select>';
}//ko_spcialfilter_kleingruppen_region()


/**
  * Generate a special filter for smallgroup types
	*
	* This function is called by the filter for smallgroup types by a FCN: definition in the SQL column
	* It generates the necessary code for the filter dynamically
	*
	* @param string HTML code for the filter
	*/
function ko_specialfilter_kleingruppen_type(&$code) {
	$code = '<select name="var1" size="0"><option value=""></option>';
	$rows = db_select_distinct("ko_kleingruppen", "type", "", "", TRUE);
	foreach($rows as $row) {
		if(!$row) continue;
		$code .= '<option value="'.$row.'" title="'.$row.'">'.ko_html($row).'</option>';
	}
	$code .= '</select>';
}//ko_spcialfilter_kleingruppen_type()


/**
  * Generate a special filter for the family role of ko_leute
	*
	* @param string HTML code for the filter
	*/
function ko_specialfilter_enum_ll(&$code, $params) {
	//Parse parameters (0 is function name)
	$table = $params[1];
	$col = $params[2];

	$code = '<select name="var1" size="0"><option value=""></option>';
	$rows = db_get_enums_ll($table, $col);
	foreach($rows as $key => $value) {
		if(!$key) continue;
		$code .= '<option value="'.$key.'" title="'.$value.'">'.$value.'</option>';
	}
	$code .= '</select>';
}//ko_spcialfilter_enum_ll()



/**
 * Rota filter: Show all events with rota schedulling for the user to select one.
 * The applied filter will then show people scheduled in this event.
 */
function ko_specialfilter_rota(&$code) {
	global $DATETIME;

	ko_get_eventgruppen($grps);

	$code = '<select name="var1" size="0">';
	$events = db_select_data("ko_event", "WHERE `rota` IN (1,2) AND `startdatum` > NOW()", "*", "ORDER BY startdatum ASC, eventgruppen_id ASC", "LIMIT 0,30");
	foreach($events as $event) {
		$value  = strftime($DATETIME["dmy"], strtotime($event["startdatum"]));
		$value .= ": ".$grps[$event["eventgruppen_id"]]["name"];
		$code .= '<option value="'.$event["id"].'" title="'.$value.'">'.$value.'</option>';
	}
	$code .= '</select>';
}//ko_specialfilter_rota()



/**
 * Rota filter: Show a list of alle team presets for the user to select one.
 * The applied filter will only show people scheduled in the given event and in one of these teams.
 */
function ko_specialfilter_rota_teams(&$code) {
	$code = '<select name="var2" size="0"><option value="">'.getLL('all').'</option>';

	//Get all presets
	$itemset = array_merge((array)ko_get_userpref('-1', '', 'rota_itemset', 'ORDER by `key` ASC'), (array)ko_get_userpref($_SESSION['ses_userid'], '', 'rota_itemset', 'ORDER by `key` ASC'));
	foreach($itemset as $i) {
		$value = $i['user_id'] == '-1' ? '@G@'.$i['key'] : $i['key'];
		$desc = $i['user_id'] == '-1' ? getLL('itemlist_global_short').' '.$i['key'] : $i['key'];
		$code .= '<option value="'.$value.'" title="'.$desc.'">"'.$desc.'"</option>';
	}

	//Add all teams
	$orderCol = ko_get_setting('rota_manual_ordering') ? 'sort' : 'name';
	$teams = db_select_data('ko_rota_teams', 'WHERE 1', '*', 'ORDER BY `'.$orderCol.'` ASC');
	if(sizeof($itemset) > 0 && sizeof($teams) > 0) $code .= '<option value="" disabled="disabled">-- '.mb_strtoupper(getLL('rota_teams_list_title')).' --</option>';
	if(sizeof($teams) > 0) {
		foreach($teams as $team) {
			$code .= '<option value="'.$team['id'].'" title="'.$team['name'].'">'.$team['name'].'</option>';
		}
	}

	$code .= '</select>';
}//ko_specialfilter_rota_teams()



function ko_specialfilter_filterpreset(&$code) {
	$filterset = array_merge((array)ko_get_userpref('-1', '', 'filterset', 'ORDER BY `key` ASC'), (array)ko_get_userpref($_SESSION['ses_userid'], '', 'filterset', 'ORDER BY `key` ASC'));

	$code = '<select name="var1" size="0">';
	foreach($filterset as $f) {
		$value = $f['user_id'] == '-1' ? '@G@'.$f['key'] : $f['key'];
		$desc = $f['user_id'] == '-1' ? getLL('itemlist_global_short').' '.$f['key'] : $f['key'];
	  $code .= '<option value="'.$value.'" title="'.$desc.'">'.$desc.'</option>';
	}
	$code .= '</select>';
}//ko_specialfilter_filterpreset()



function ko_specialfilter_crdate(&$code, $params) {
	if (isset($_SESSION['filter'])) {
		foreach($_SESSION['filter'] as $i => $f) {
			if(!is_numeric($i)) continue;
			$filter = db_select_data("ko_filter", "WHERE `id` = '".$f[0]."'", "*", "", "", TRUE);
			if($filter["name"] == "crdate") {
				$value1 = $f[1][1];
				$value2 = $f[1][2];
			}
		}
	}

	if($params[1] == 1) {
		$value1 = $value1 ?? "0000-00-00";
		$code = '<input type="text" name="var1" size="12" maxlength="10" onkeydown="if ((event.which == 13) || (event.keyCode == 13)) { this.form.submit_filter.click(); return false;} else return true;" value="'.$value1.'" />';
	} else if($params[1] == 2) {
		$value2 = $value2 ?? strftime("%Y-%m-%d", time());
		$code = '<input type="text" name="var2" size="12" maxlength="10" onkeydown="if ((event.which == 13) || (event.keyCode == 13)) { this.form.submit_filter.click(); return false;} else return true;" value="'.$value2.'" />';
	}

}//ko_specialfilter_crdate()




function ko_specialfilter_donation(&$code) {
	$code = '<select name="var1" size="0">';
	$rows = db_select_distinct('ko_donations', 'YEAR(date)', '', "WHERE `promise` = '0'");
	foreach($rows as $row) {
		if(!$row) continue;
		$code .= '<option value="'.$row.'" title="'.$row.'">'.ko_html($row).'</option>';
	}
	$code .= '</select>';
}//ko_spcialfilter_donation()





function ko_specialfilter_donation_account(&$code) {
	$code = '<select name="var2" size="0"><option value=""></option>';
	$rows = db_select_data('ko_donations_accounts', 'WHERE 1=1', '*');
	foreach($rows as $row) {
		$code .= '<option value="'.$row['id'].'" title="'.ko_html($row['number'].' '.$row['name']).'">'.ko_html($row['number'].' '.$row['name']).'</option>';
	}
	$code .= '</select>';
}//ko_spcialfilter_donation_account()




function ko_specialfilter_smallgrouproles(&$code) {
	global $SMALLGROUPS_ROLES;

	$code = '<select name="var1" size="0">';
	foreach($SMALLGROUPS_ROLES as $role) {
		if(!$role) continue;
		$code .= '<option value="'.$role.'" title="'.getLL('kg_roles_'.$role).'">'.getLL('kg_roles_'.$role).'</option>';
	}
	$code .= '</select>';
}//ko_spcialfilter_smallgrouproles()




function ko_specialfilter_duplicates(&$code) {
	$code = '<select name="var1" size="0">';
	$fields = array('vorname-nachname', 'vorname-email', 'vorname-adresse', 'vorname-geburtsdatum', 'natel-geburtsdatum', 'firm-plz', 'firm-ort', 'firm', 'email');
	foreach($fields as $field) {
		$code .= '<option value="'.$field.'" title="'.getLL('leute_duplicates_'.$field).'">'.getLL('leute_duplicates_'.$field).'</option>';
	}
	$code .= '</select>';
}//ko_spcialfilter_duplicates()




function ko_specialfilter_logins(&$code) {
	$code = '<select name="var1" size="0">';
	$code .= '<option value="_all">'.getLL('all').'</option>';
	$groups = db_select_data('ko_admingroups', "WHERE 1=1", '*');
	foreach($groups as $group) {
		if(!$group['id']) continue;
		$code .= '<option value="'.$group['id'].'" title="'.$group['name'].'">'.ko_html($group['name']).'</option>';
	}
	$code .= '</select>';
}//ko_spcialfilter_logins()







/************************************************************************************************************************
 *                                                                                                                      *
 * MODUL-FUNKTIONEN   D A T E N                                                                                         *
 *                                                                                                                      *
 ************************************************************************************************************************/

/**
	* Get all calendars
	*/
function ko_get_event_calendar(&$r, $id="", $type="") {
	$z_where = "WHERE 1=1 ";
	if($id) $z_where .= " AND `id` = '$id' ";
	if($type) $z_where .= " AND `type` = '$type' ";

	$r = db_select_data('ko_event_calendar', $z_where, '*', 'ORDER BY name ASC');
}//ko_get_event_calendar()

/**
	* Liefert alle Eventgruppen
	*/
function ko_get_eventgruppen(&$grp, $z_limit = "", $z_where = "") {
	$order = ($_SESSION["sort_tg"]) ? " ORDER BY ".$_SESSION["sort_tg"]." ".$_SESSION["sort_tg_order"] : " ORDER BY name ASC ";
	$grp = db_select_data('ko_eventgruppen', "WHERE 1=1 $z_where", '*', $order, $z_limit);
}//ko_get_eventgruppen()


/**
	* Liefert einzelne Eventgruppe
	*/
function ko_get_eventgruppe_by_id($gid, &$grp) {
	$grp = db_select_data('ko_eventgruppen', "WHERE `id` = '$gid'", '*', '', 'LIMIT 1', TRUE);
}//ko_get_eventgruppe_by_id()


/**
	* Liefert die Farbe, die einer Eventgruppe zugewiesen ist
	*/
function ko_get_eventgruppen_farbe($id) {
	$row = db_select_data('ko_eventgruppen', "WHERE `id` = '$id'", 'farbe', '', '', TRUE);
	return $row['farbe'];
}//ko_get_eventgruppen_farbe()


/**
	* Liefert den Namen, der einer Eventgruppe zugewiesen ist
	*/
function ko_get_eventgruppen_name($id) {
	$row = db_select_data('ko_eventgruppen', "WHERE `id` = '$id'", 'name', '', '', TRUE);
	return $row['name'];
}//ko_get_eventgruppen_name()


/**
	* Liefert die Reservations-Items, die einer Eventgruppe zugeordnet sind
	*/
function ko_get_eventgruppen_resitems($id) {
	$row = db_select_data('ko_eventgruppen', "WHERE `id` = '$id'", 'resitems', '', '', TRUE);
	return $row['resitems'];
}//ko_get_eventgruppen_resitems()


/**
	* Liefert einzelnen Event
	*/
function ko_get_event_by_id($id, &$e) {
	$e = db_select_data('ko_event', "WHERE `id` = '$id'", '*', '', '', TRUE);
}//ko_get_event_by_id()


/**
	* Liefert alle Events
	*/
function ko_get_events(&$e, $z_where = '', $z_limit = '', $table='ko_event', $z_sort='') {
	$e = array();

	//Replace ko_event in filter with table name
	if($table != 'ko_event') $z_where = str_replace('ko_event', $table, $z_where);

	if($z_sort) $order = $z_sort;
	else $order = $_SESSION["sort_events"] ? " ORDER BY ".($_SESSION["sort_events"] == "eventgruppen_id" ? "eventgruppen_name" : $_SESSION["sort_events"])." ".$_SESSION["sort_events_order"].",startzeit ".$_SESSION["sort_events_order"] : " ORDER BY startdatum ASC,startzeit ASC";
	$e = db_select_data($table.' LEFT JOIN ko_eventgruppen ON '.$table.'.eventgruppen_id = ko_eventgruppen.id', 'WHERE 1=1 '.$z_where, $table.'.id AS id, '.$table.'.*, ko_eventgruppen.name AS eventgruppen_name, ko_eventgruppen.farbe AS eventgruppen_farbe, ko_eventgruppen.res_combined AS res_combined, ko_eventgruppen.type AS eg_type', $order, $z_limit);

	//Add color dynamically
	ko_set_event_color($e);
}//ko_get_events()



/**
 * gets all reminders off type $mode and according to where clause
 *
 * @param $result contains the result i.e. the reminders
 * @param string $mode either 1 = event or later 2 = leute
 * @param string $z_where MYSQL where clause, starting with AND or OR
 * @param string $z_limit MYSQL limit clause, starting with LIMIT
 * @param string $z_sort MYSQL order clause, starting with ORDER BY
 */
function ko_get_reminders(&$result, $mode = NULL, $z_where = '', $z_limit = '', $z_sort = '', $single = FALSE, $noIndex = FALSE) {
	$where = 'where 1 = 1 ';
	$where .= ($mode === null) ? '' : "and `type` = " . $mode . " ";
	$result = db_select_data('ko_reminder', $where . $z_where, "*", $z_sort, $z_limit, $single, $noIndex);
} // ko_get_reminders


/**
 * Apply event color for each event individually if $EVENT_COLOR is set.
 *
 * @param array &$_events: Array with one event or several passed by reference. eventgruppen_farbe will be set for each event
 */
function ko_set_event_color(&$_events) {
	global $EVENT_COLOR;

	if(!is_array($EVENT_COLOR) || sizeof($EVENT_COLOR) <= 0) return;

	if(isset($_events['id'])) {
		$events = array($_events['id'] => $_events);
		$single = TRUE;
	} else {
		$events = $_events;
		$single = FALSE;
	}
	foreach($events as $k => $event) {
		$color = $EVENT_COLOR['map'][$event[$EVENT_COLOR['field']]];
		if($color) $events[$k]['eventgruppen_farbe'] = $color;
	}

	if($single) {
		$_events = array_shift($events);
	} else {
		$_events = $events;
	}
}//ko_set_event_color()



/**
	* Liefert alle zu moderierenden Events
	*/
function ko_get_events_mod(&$e, $z_where = "", $z_limit = "") {
	ko_get_events($e, $z_where, $z_limit, 'ko_event_mod');
}//ko_get_events_mod()


/**
	* Liefert alle Events an einem Datum
	*/
function ko_get_events_by_date($t="", $m, $j, &$r, $z_where="", $table="ko_event") {
	$datum = $j."-".str_to_2($m)."-".($t?str_to_2($t):"01");

	//Replace ko_event in filter with table name
	if($table != 'ko_event') $z_where = str_replace('ko_event', $table, $z_where);

	$r = db_select_data($table.' LEFT JOIN ko_eventgruppen ON ko_event.eventgruppen_id = ko_eventgruppen.id', "WHERE (`startdatum` <= '$datum' AND `enddatum` >= '$datum') $z_where", 'ko_event.id AS id, ko_event.*, ko_eventgruppen.name AS eventgruppen_name', 'ORDER BY `startdatum` ASC, `startzeit` ASC');
}//ko_get_events_by_date()






function kota_ko_event_eventgruppen_id_dynselect(&$values, &$descs, $rights=0, $_where="") {
	global $access;

	if(!isset($access['daten'])) ko_get_access('daten');

	$values = $descs = array();
	$cals = db_select_data("ko_event_calendar", "WHERE 1=1", "*", "ORDER BY `name` ASC");
	foreach($cals as $cid => $cal) {
		if($access['daten']['cal'.$cid] < $rights) continue;
		//Add cal name (as optgroup)
		$descs["i".$cid] = $cal["name"];
		//Get groups for this cal (only show groups with type=0 (kOOL) but not imported event groups (type>0))
		$where = "WHERE `calendar_id` = '$cid' AND `type` = '0' ";
		$where .= $_where;
		$groups = db_select_data("ko_eventgruppen", $where, "*", "ORDER BY `name` ASC");
		foreach($groups as $gid => $group) {
			if($access['daten'][$gid] < $rights) continue;
			$values["i".$cid][$gid] = $gid;
			$descs[$gid] = $group["name"];
		}
	}//foreach(cals)
	//Add all event groups without calendars
	$groups = db_select_data("ko_eventgruppen", "WHERE `calendar_id` = '0' AND `type` = '0'", "*", "ORDER BY `name` ASC");
	foreach($groups as $gid => $group) {
		if($access['daten'][$gid] < $rights) continue;
		$values[$gid] = $gid;
		$descs[$gid] = $group["name"];
	}
}//kota_ko_event_eventgruppen_id_dynselect()




/**
	* Find moderators for a given event group
	*/
function ko_get_moderators_by_eventgroup($gid) {
	global $LEUTE_EMAIL_FIELDS;

	//email fields
	$email_fields = $where_email = '';
	foreach($LEUTE_EMAIL_FIELDS as $field) {
		$email_fields .= 'l.'.$field.' AS '.$field.', ';
		$where_email .= " l.$field != '' OR ";
	}
	$email_fields = substr($email_fields, 0, -2);

	//Get moderators for this event group
	$logins = db_select_data("ko_admin AS a LEFT JOIN ko_leute as l ON a.leute_id = l.id",
												 "WHERE ($where_email a.email != '') AND (a.disabled = '0' OR a.disabled = '')",
												 "a.id AS id, $email_fields, a.email AS admin_email, l.id AS leute_id");
	foreach($logins as $login) {
		$all = ko_get_access_all('daten', $login['id'], $max);
		if($max < 4) continue;
		$user_access = ko_get_access('daten', $login['id'], TRUE, TRUE, 'login', FALSE);
		if($user_access['daten'][$gid] < 4) continue;
		$mods[$login['id']] = $login;
	}
	$add_mods = array();
	foreach($mods as $i => $mod) {
		//Use admin_email as set for the login in first priority
		if($mod['admin_email']) {
			$mods[$i]['email'] = $mod['admin_email'];
		} else {
			//Get all email addresses for this person
			ko_get_leute_email($mod['leute_id'], $email);
			$mods[$i]['email'] = $email[0];
			//Create additional moderators for every email address to be used (if several are set in ko_leute_preferred_fields)
			if(sizeof($email) > 1) {
				for($j=1; $j<sizeof($email); $j++) {
					$add_mods[$j] = $mod;
					$add_mods[$j]['email'] = $email[$j];
				}
			}
		}
	}
	if(sizeof($add_mods) > 0) $mods = array_merge($mods, $add_mods);

	return $mods;
}//ko_get_moderators_by_eventgroup()











/************************************************************************************************************************
 *                                                                                                                      *
 * MODUL-FUNKTIONEN   L E U T E                                                                                         *
 *                                                                                                                      *
 ************************************************************************************************************************/

/**
	* Liefert eine Liste alle vorkommenden Länder in der Personen-Daten
	*/
function ko_get_all_countries(&$c) {
	$c = db_select_distinct('ko_leute', 'land', '', "WHERE `deleted` = '0'");
}//ko_get_all_countries()



/**
	* Liefert Personen-Daten
	*/
function ko_get_leute(&$p, $z_where = "", $z_limit = "", $z_cols = "", $z_sort = "", $apply_version=TRUE) {
	global $ko_menu_akt;

	//only apply sorting if not a MODULE-column is to be sorted
	if(is_string($z_sort) && !empty($z_sort)) {
		$sort = $z_sort;
	} else if(is_array($_SESSION["sort_leute"]) && is_array($_SESSION["sort_leute_order"])) {
		$sort_add = array();
		foreach($_SESSION["sort_leute"] as $i => $col) {
			if(substr($col, 0, 6) != "MODULE") {
				$sort_add[] = $col." ".$_SESSION["sort_leute_order"][$i];
			}
		}
		if(!in_array("nachname", $_SESSION["sort_leute"])) $sort_add[] = "nachname ASC";
		if(!in_array("vorname", $_SESSION["sort_leute"])) $sort_add[] = "vorname ASC";
		$sort = "ORDER BY ".implode(", ", $sort_add);
	} else {
		$sort = "";
	}

	//Decide on the columns to get
	if($z_cols != "") {
		$cols = $z_cols;
	} else {
		$cols = "*";
	}

	//Unset z_limit if an old version is to be retrieved
	$limit = FALSE;
	if($_SESSION["leute_version"] && $z_limit && $apply_version) {
		list($limit_start, $limit) = explode(", ", str_replace("LIMIT ", "", $z_limit));
		$z_limit = "";
	}

	//Perform query
	$p = array();
	$count = 0;
	$rows = db_select_data('ko_leute', 'WHERE 1=1 '.$z_where, $cols, $sort, $z_limit);
	$num = sizeof($rows);
	foreach($rows as $row) {
		//Get old version of person if set
		if($_SESSION["leute_version"] && $apply_version) {
			//Apply limit manually if z_limit was set but old version is displayed
			if($limit && ($count < $limit_start || $count >= $limit)) continue;
			//Don't show records with crdate greater than the given date. Display all those with no crdate (backwards compatibilty and safer)
			if(strtotime($row["crdate"]) > strtotime($_SESSION["leute_version"]." 23:59:59")) {
				$num--;
				continue;
			}
			//Get old version
			$old = ko_leute_get_version($_SESSION["leute_version"], $row["id"]);
			$hid = strpos(ko_get_leute_hidden_sql(), "hidden = '0'");
			if($old["deleted"] == 1 || ($hid && $old["hidden"] == 1) ) {
				//Don't display old version that used to be deleted or hidden when hidden entries are to be invisible
				$num--;
				continue;
			} else {
				if(isset($old["id"])) {  //old entry found
					$p[$row["id"]] = $old;
				} else if($row["deleted"] == 0) {  //no old entry so display current if not deleted
					$p[$row["id"]] = $row;
				} else {
					//Don't display currently deleted entries with no old version
					$num--;
					continue;
				}
			}
		}
		//Normal case, so just store current entry as it is in ko_leute
		else {
			$p[$row["id"]] = $row;
		}
	}
	return $num;
}//ko_get_leute()



function ko_manual_sorting($cols) {
	if($_SESSION["leute_version"]) {
		return TRUE;
	} else {
		$manual_columns = array('smallgroups', 'famid', 'famfunction');
		foreach($cols as $col) {
			if(in_array($col, $manual_columns) || substr($col, 0, 6) == "MODULE") {
				return TRUE;
			}
		}
	}
	return FALSE;
}//ko_manual_sorting()



/**
  * Liefert Familien-Daten zu Familien-ID
	*/
function ko_get_familie($id) {
	if(!is_numeric($id)) return FALSE;

	$fam = db_select_data('ko_familie', "WHERE `famid` = '$id'", '*', '', 'LIMIT 1', TRUE);
	ko_add_fam_id($fam);

	return $fam;
}//ko_get_familie()



/**
  * Fügt eine Familien-ID bestehend aus Nachname, Ort und Vornamen zur übergebenen Familie
	*/
function ko_add_fam_id(&$fam, $_members="") {
	global $COLS_LEUTE_UND_FAMILIE, $FAMFUNCTION_SORT_ORDER;

	$max_len = 12;

	if($_members) {
		$members = $_members;
		$num_members = sizeof($members);
	} else {
		$num_members = ko_get_personen_by_familie($fam["famid"], $members);
	}
	//order members by Father, Mother, Kids
	$new_members = array();
	foreach($members as $i => $member) {
		$sortKey = $FAMFUNCTION_SORT_ORDER[$member['famfunction']] ? $FAMFUNCTION_SORT_ORDER[$member['famfunction']] : 10;
		$sort_members[$sortKey][$i] = $member['geburtsdatum'].$member['vorname'];
	}
	if(sizeof($sort_members[1]) > 0) {
		asort($sort_members[1]);   //Man
		foreach($sort_members[1] as $k => $v) $new_members[] = $members[$k];
	}
	if(sizeof($sort_members[2]) > 0) {
		asort($sort_members[2]);   //Woman
		foreach($sort_members[2] as $k => $v) $new_members[] = $members[$k];
	}
	if(sizeof($sort_members[3]) > 0) {
		asort($sort_members[3]);   //Children
		foreach($sort_members[3] as $k => $v) $new_members[] = $members[$k];
	}
	if(sizeof($sort_members[10]) > 0) {
		asort($sort_members[10]);  //No famfunction defined
		foreach($sort_members[10] as $k => $v) $new_members[] = $members[$k];
	}
	$members = $new_members;
	reset($members);

	//lastname
	if(in_array("nachname", $COLS_LEUTE_UND_FAMILIE)) {
		//normal families, with lastname as a family field
		$famlastname = trim($fam['nachname']);
	} else {
		//mixed families with different lastnames
		$famnames = array();
		foreach($members as $member) {
			if(!in_array(trim($member['nachname']), $famnames)) $famnames[] = trim($member['nachname']);
		}
		$famlastname = implode(getLL('family_lastname_link'), $famnames);
	}
	//city
	if($fam["ort"]) {
		$famcity = strlen($fam["ort"]) > $max_len ? substr($fam["ort"], 0, $max_len).".." : $fam["ort"];
		$famcity = getLL("from")." ".$famcity;
	}
	//single members of the family
	$fammembers = "";
	if($num_members > 0) {
		foreach($members as $p) {
			$fammembers .= mb_strtoupper(substr($p["vorname"], 0, 1)).",";
		}
		$fammembers = "(".substr($fammembers, 0, -1).")";
	}//if(num_members>0)

	//put it all together into the new fam-id
	$fam["id"] = $famlastname." ".$famcity." ".$fammembers;
	//save lastname in family, even if lastname is not a family field. This makes the export work with lastnames
	if(!in_array("nachname", $COLS_LEUTE_UND_FAMILIE)) $fam["nachname"] = $famlastname;
}//ko_add_fam_id()



/**
 * Liefert alle Familien
 * inkl. ID
 */
function ko_get_familien(&$fam) {
	global $db_connection, $ko_menu_akt;

	$fam = array();

	//Get all families
	$query = "SELECT * FROM ko_familie ORDER BY nachname ASC";
	$result = mysqli_query($db_connection, $query);
	while($row = mysqli_fetch_assoc($result)) {
		$fam[$row["famid"]] = $row;
	}

	//Get all family members once, so they don't have to be retrieved inside the loop
	$members = array();
	$deleted = ($ko_menu_akt == "leute" && ko_get_userpref($_SESSION["ses_userid"], "leute_show_deleted") == 1) ? " AND `deleted` = '1' " : " AND `deleted` = '0' ";
	$deleted .= ko_get_leute_hidden_sql();
	$result = mysqli_query($db_connection, "SELECT * FROM ko_leute WHERE `famid` != '0' $deleted ORDER BY `famid` ASC");
	while($row = mysqli_fetch_assoc($result)) {
		$members[$row["famid"]][] = $row;
	}

	//Add family ID
	foreach($fam as $i => $f) {
		ko_add_fam_id($fam[$i], $members[$f["famid"]]);
		$sort[$fam[$i]["id"]] = $fam[$i]["famid"];
	}//foreach(fam)

	//sort them by famid which is constructed by all the lastnames
	ksort($sort, SORT_LOCALE_STRING);
	$return = NULL;
	foreach($sort as $famid) {
		$return[$famid] = $fam[$famid];
	}
	$fam = $return;
}//ko_get_familien()



/**
  * Aktualisiert eine Familie mit den übergebenen Fam-Daten
	* und aktualisiert alle Member
	*/
function ko_update_familie($famid, $fam_data, $leute_id="") {
	global $FAMILIE_EXCLUDE;

	$data = array();
	$fam_cols = db_get_columns("ko_familie");
	foreach($fam_cols as $col_) {
		$col = $col_["Field"];
		if(in_array($col, $FAMILIE_EXCLUDE)) continue;
		if(!isset($fam_data[$col])) continue;

		$data[$col] = $fam_data[$col];
	}//foreach(fam_cols as col)
	if(sizeof($data) == 0) return;

	//Familien-Daten aktualisieren
	db_update_data('ko_familie', "WHERE `famid` = '$famid'", $data);

	//Alle Familien-Mitglieder aktualisieren
	ko_update_leute_in_familie($famid, $changes=TRUE, $leute_id);
}//ko_update_familie()



/**
  * Aktualisiert alle Member mit den Angaben aus der Familie
	* Only store changes to ko_leute_changes if second argument is set. This gets set in ko_update_familie()
	* The third argument defines the id of the person being saved, as no entry to ko_leute_changes must be saved (this is done in submit_edit_person in leute/index.php
	*/
function ko_update_leute_in_familie($famid, $do_changes=FALSE, $leute_id="") {
	global $COLS_LEUTE_UND_FAMILIE;

	if(!is_numeric($famid) || $famid <= 0) return FALSE;
	$fam_data = ko_get_familie($famid);

	$data = array();
	foreach($COLS_LEUTE_UND_FAMILIE as $col) {
		$data[$col] = $fam_data[$col];
	}

	//Kinder-Feld
	$num_kids = ko_get_personen_by_familie($famid, $members, "child");

	$do_ldap = ko_do_ldap();
	if($do_ldap) $ldap = ko_ldap_connect();

	//Daten aller Family-Members aktualisieren
	ko_get_personen_by_familie($famid, $members);
	foreach($members as $m) {
		//store version
		if($do_changes && $leute_id != $m["id"]) {
			ko_save_leute_changes($m["id"]);
		}
		//Update according to fam data
		if($m["famfunction"] == "husband" || $m["famfunction"] == "wife") {
			$data['kinder'] = $num_kids;
		} else {
			$data['kinder'] = '0';
		}
		db_update_data('ko_leute', "WHERE `id` = '".$m['id']."'", $data);

		//Update LDAP for each member
		if(ko_do_ldap() && $m['id'] != $leute_id) ko_ldap_add_person($ldap, $m, $m['id'], ko_ldap_check_person($ldap, $m['id']));
	}

	if($do_ldap) ko_ldap_close($ldap);
}//ko_update_leute_in_familie()



/**
	* Liefert einzelne Person
	*/
function ko_get_person_by_id($id, &$p, $show_deleted=FALSE) {
	global $ko_menu_akt;

	$p = array();
	if(!is_numeric($id)) return FALSE;

	if(!$show_deleted) {
		$deleted = ($ko_menu_akt == "leute" && ko_get_userpref($_SESSION["ses_userid"], "leute_show_deleted") == 1) ? " AND `deleted` = '1' " : " AND `deleted` = '0' ";
	}

	$p = db_select_data('ko_leute', "WHERE `id` = '$id' $deleted", '*', '', '', TRUE);
}//ko_get_person_by_id()




/**
 * Changes the address fields of the given address record according to the given rectype
 *
 * @param array $p Address record from ko_leute
 * @param string $force_rectype Specify the rectype that should be applied. If none is given, this persons default rectype ($p[rectype]) will be used
 * @param array $addp Will hold additional addresses if rectype uses reference to other addresses, which might be more than one
 * @returns array $p Returns the address with the applied changes to the fields defined for this rectype
 */
function ko_apply_rectype($p, $force_rectype='', &$addp=array()) {
	global $RECTYPES;

	if(!is_array($p)) return $p;

	$target_rectype = $force_rectype != '' ? $force_rectype : $p['rectype'];

	if($target_rectype && is_array($RECTYPES[$target_rectype])) {
		foreach($RECTYPES[$target_rectype] as $pcol => $newcol) {
			if(!isset($p[$pcol])) continue;
			if(FALSE === strpos($newcol, ':')) {
				$p[$pcol] = $p[$newcol];
			} else {
				list($table, $field) = explode(':', $newcol);
				switch($table) {
					//Use data from smallgroup this person is assigned to
					case 'ko_kleingruppen':
						list($sgs) = explode(',', $p['smallgroups']);
						if(!$sgs) break;
						list($sgid, $sgrole) = explode(':', $sgs);
						$sg = ko_get_smallgroup_by_id($sgid);
						if(isset($sg[$field])) $p[$pcol] = $sg[$field];
					break;

					//Use columns from another address in ko_leute. Other address defined in $field
					case 'ko_leute':
						if(!$p[$field]) break;
						$persons = db_select_data('ko_leute', "WHERE `id` IN (".$p[$field].") AND `deleted` = '0' AND `hidden` = '0'");
						if(sizeof($persons) < 1) break;
						$first = TRUE;
						foreach($persons as $person) {
							//Prevent circular dependency
							if($person['id'] == $p['id']) continue;

							$person = ko_apply_rectype($person, $force_rectype);
							//If multiple addresses are returned apply first changes to original $p...
							if($first) {
								$p[$pcol] = $person[$pcol];
								$first = FALSE;
							}
							//...and store the remaining changes in $addp
							else {
								if(!is_array($addp[$person['id']])) $addp[$person['id']] = $p;
								$addp[$person['id']][$pcol] = $person[$pcol];
							}
						}
					break;
				}
			}
		}
	}

	return $p;
}//ko_apply_rectype()




/**
  * Get the name belonging to a person's id
	*/
function ko_get_person_name($id, $format="vorname nachname") {
	ko_get_person_by_id($id, $p);
	return strtr($format, $p);
}//ko_get_person_name()



/**
  * Liefert alle Personen einer Familie
	*/
function ko_get_personen_by_familie($famid, &$p, $function="") {
	if(!is_numeric($famid) || $famid <= 0) return FALSE;
	$p = array();
	$z_where = '';

	if((!is_array($function) && $function != '') || (is_array($function) && sizeof($function) > 0)) {
		if(!is_array($function)) $function = array($function);
		$fam_functions = db_get_enums('ko_leute', 'famfunction');
		foreach($function as $fi => $f) {
			if($f == '') continue;
			if(!in_array($f, $fam_functions)) unset($function[$fi]);
		}
		if(sizeof($function) > 0) {
			$z_where = " AND `famfunction` IN ('".implode("','", $function)."') ";
		}
	}

	$p = db_select_data('ko_leute', "WHERE `famid` = '$famid' $z_where AND `deleted` = '0' AND `hidden` = '0'", '*', 'ORDER BY famfunction DESC');

	return sizeof($p);
}//ko_get_personen_by_familie()



/**
 * Liefert eine Liste aller (oder wenn id definiert ist nur diesen Eintrag) zu moderierenden Mutationen (aus Tabelle ko_leute_mod)
 */
function ko_get_mod_leute(&$r, $id="") {
	global $db_connection;

	$r = array();
	$z_where  = "WHERE `_leute_id` <> '0' AND `_group_id` = ''";  //don't show web-group-subscriptions
	$z_where .= ($id != "") ? " AND `_id`='$id'" : "";
	$query = "SELECT * FROM `ko_leute_mod` $z_where ORDER BY _crdate DESC";
	$result = mysqli_query($db_connection, $query);
	while($row = mysqli_fetch_assoc($result)) {
		$r[$row["_id"]] = $row;
	}
}//ko_get_mod_leute()



/**
	* Liefert eine Liste aller (oder wenn id definiert ist nur diesen Eintrag) zu moderierenden Gruppen-Anmeldungen (aus Tabelle ko_leute_mod)
	*/
function ko_get_groupsubscriptions(&$r, $gsid='', $uid='', $gid='') {
	global $db_connection, $access;

	// Group rights if uid is given
	if($uid > 0) {
		ko_get_access('groups');
	}

	// Get subscriptions
	$r = array();
	$z_where  = "WHERE `_group_id` != ''";  //don't show address changes
	if($gsid != '') $z_where .= " AND `_id` = '$gsid'";
	if($gid != '') $z_where .= " AND `_group_id` LIKE '%g$gid%'";
	$query = "SELECT * FROM `ko_leute_mod` $z_where ORDER BY _crdate DESC";
	$result = mysqli_query($db_connection, $query);
	while($row = mysqli_fetch_assoc($result)) {
		if($uid) {
			// Only display subscriptions to groups the given user has level 2 access to
			if($access['groups']['ALL'] > 1 || $access['groups'][ko_groups_decode($row['_group_id'], 'group_id')] > 1) {
				$r[$row["_id"]] = $row;
			}
		} else {
			// Return them all if no userid is given
			$r[$row["_id"]] = $row;
		}
	}
}//ko_get_groupsubscriptions()



/**
 * Apply filter given in setting 'birthday_filter' and return SQL
 * To be attached to SQL to get birthday list
 */
function ko_get_birthday_filter() {
	$filter = unserialize(ko_get_userpref($_SESSION['ses_userid'], 'birthday_filter'));
	if(!$filter['value']) {
		return '';
	} else {
		apply_leute_filter(unserialize($filter['value']), $z_where);
		return ' '.$z_where;
	}
}//ko_get_birthday_filter()



/**
  * Liefert die Spaltennamen der ko_leute-DB
	* Zusätzlich werden noch Module-Spaltennamen (wie z.B. Gruppen) hinzugefügt
	* mode kann view oder edit sein, jenachdem für welchen Modus die Spalten gemäss ko_admin.leute_admin_spalten verlangt sind
	*   (bei all wird auf den Vergleich mit allowed_cols verzichtet)
	*/
function ko_get_leute_col_name($groups_hierarchie=FALSE, $add_group_datafields=FALSE, $mode="view", $force=FALSE, &$rawgdata='') {
	global $access;
	global $LEUTE_NO_FAMILY;

	if(!isset($access['kg'])) ko_get_access('kg');
	if(!isset($access['groups'])) ko_get_access('groups');

	$r_all = unserialize(ko_get_setting("leute_col_name"));
	$r = $r_all[$_SESSION["lang"]];

	//exclude not allowed cols, if set
	$allowed_cols = ko_get_leute_admin_spalten($_SESSION["ses_userid"], "all");
	$always_allowed = array();
	$do_groups = ko_module_installed('groups', $_SESSION['ses_userid']) && $access['groups']['MAX'] > 0;
	$do_smallgroups = ko_module_installed('kg', $_SESSION['ses_userid']) && $access['kg']['MAX'] > 0;
	if($do_groups) $always_allowed[] = 'groups';
	if($do_smallgroups) $always_allowed[] = 'smallgroups';

	//Unset not allowed columns
	if($mode != "all") {
		if(is_array($allowed_cols[$mode]) && sizeof($allowed_cols[$mode]) > 0) {
			foreach($r as $i => $v) {
				if(in_array($i, $always_allowed)) continue;
				if(!in_array($i, $allowed_cols[$mode])) unset($r[$i]);
			}
		} else {
			if(!$do_groups && in_array('groups', array_keys($r))) {
				foreach($r as $i => $v) {
					if($i == 'groups') unset($r[$i]);
				}
			}
			if(!$do_smallgroups && in_array('smallgroups', array_keys($r))) {
				foreach($r as $i => $v) {
					if($i == 'smallgroups') unset($r[$i]);
				}
			}
		}
	}

	//Family columns (father, mother)
	$famok = TRUE;
	if($mode != 'all' && is_array($allowed_cols[$mode]) && sizeof($allowed_cols[$mode]) > 0) {
		if(!in_array('famid', $allowed_cols[$mode])) $famok = FALSE;
	}
	if($famok && !$LEUTE_NO_FAMILY) {
		$r['MODULEfamid_husband'] = getLL('kota_ko_leute__famid_father');
		$r['MODULEfamid_wife'] = getLL('kota_ko_leute__famid_mother');
		$r['MODULEfamid_famlastname'] = getLL('kota_ko_leute__famid_famlastname');
	}

	//Remove empty entries
	foreach($r as $k => $v) if(!$v) unset($r[$k]);

	//Allow plugins to add columns
	hook_leute_add_column($r);

	//Add small group columns
	if(ko_module_installed('kg') && ko_get_userpref($_SESSION['ses_userid'], 'leute_kg_as_cols') == 1) {
		$kg_cols = db_get_columns('ko_kleingruppen');
		foreach($kg_cols as $col) {
			if(in_array($col['Field'], array('id'))) continue;
			$ll = getLL('kota_listview_ko_kleingruppen_'.$col['Field']);
			$ll = $ll ? $ll : $col['Field'];
			$r['MODULEkg'.$col['Field']] = getLL('kg_shortname').': '.$ll;
		}
	}


	//Add groups
	if(ko_module_installed('groups') || $force) {
		if($add_group_datafields) {
			$all_datafields = db_select_data('ko_groups_datafields', "WHERE 1");
		}

		$rawgdata = array();
		$groups = ko_groups_get_recursive(ko_get_groups_zwhere());
		ko_get_groups($all_groups);
		foreach($groups as $group) {
			if($access['groups']['ALL'] < 1 && $access['groups'][$group['id']] < 1 && !$force) continue;
			$name = strlen($group['name']) > ITEMLIST_LENGTH_MAX ? substr($group['name'], 0, ITEMLIST_LENGTH_MAX).'..' : $group['name'];
			if($groups_hierarchie) {
				$ml = ko_groups_get_motherline($group['id'], $all_groups);
				$depth = sizeof($ml);
				for($i=0; $i<$depth; $i++) $name = '&nbsp;&nbsp;'.$name;
			}
			$rawgdata[$group['id']] = array('id' => $group['id'], 'name' => $group['name'], 'depth' => $depth, 'pid' => array_pop($ml));
			$r['MODULEgrp'.$group['id']] = $name;
			//add datafields for this group if needed
			if($add_group_datafields && $all_groups[$group['id']]['datafields']) {
				foreach(explode(',', $all_groups[$group['id']]['datafields']) as $fid) {
					$field = $all_datafields[$fid];
					if(!$field['id']) continue;
					$name = $field['description'];
					if($groups_hierarchie) for($i=0; $i<=$depth; $i++) $name = '&nbsp;&nbsp;'.$name;
					$r['MODULEgrp'.$group['id'].':'.$fid] = $name;
					$rawgdata[$group['id']]['df'][] = $field;
				}
			}
		}
	}


	//Tracking
	if(ko_module_installed('tracking') || $force) {
		if(!is_array($access['tracking'])) ko_get_access('tracking');
		$groups = db_select_data('ko_tracking_groups', "WHERE 1", '*', 'ORDER BY name ASC');
		array_unshift($groups, array('id' => '0', 'name' => getLL('tracking_itemlist_no_group')));
		$filters = db_select_data('ko_userprefs', 'WHERE `type` = \'tracking_filterpreset\' and (`user_id` = ' . $_SESSION['ses_userid'] . ' or `user_id` = -1)', '`id`,`key`,`value`');
		foreach($groups as $group) {
			$trackings = db_select_data('ko_tracking', "WHERE `group_id` = '".$group['id']."'", '*', 'ORDER BY name ASC');
			foreach($trackings as $tracking) {
				if($access['tracking'][$tracking['id']] < 1 && $access['tracking']['ALL'] < 1) continue;
				$r['MODULEtracking'.$tracking['id']] = getLL('tracking_listtitle_short').' '.$tracking['name'];
				foreach($filters as $filter) {
					$r['MODULEtracking'.$tracking['id'].'f'.$filter['id']] = getLL('tracking_listtitle_short').' '.$tracking['name'] . ' ' . $filter['key'];
				}
			}
		}
	}

	//Donations
	if(ko_module_installed('donations') || $force) {
		if(!is_array($access['donations'])) ko_get_access('donations');
		if($access['donations']['MAX'] > 0) {
			$years = db_select_distinct('ko_donations', 'YEAR(`date`)', 'ORDER BY `date` DESC');
			foreach($years as $year) {
				$r['MODULEdonations'.$year] = getLL('donations_listtitle_short').' '.$year;
				$accounts = db_select_data('ko_donations_accounts', "WHERE 1=1", '*', 'ORDER BY name ASC');
				foreach($accounts as $account) {
					if($access['donations'][$account['id']] < 1 && $access['donations']['ALL'] < 1) continue;
					$r['MODULEdonations'.$year.$account['id']] = getLL('donations_listtitle_short').' '.$year.' '.$account['name'];
				}
			}
		}
	}

	return $r;
}//ko_get_leute_col_name()




function ko_get_family_col_name() {
	$r_all = unserialize(ko_get_setting("familie_col_name"));
	$r = $r_all[$_SESSION["lang"]];

	return $r;
}//ko_get_family_col_name()



/**
 * Wendet die Leute-Filter an und gibt SQL-WHERE-Clause zurück
 * Ebenfalls verwendet, um Admin-Filter für Berechtigungen anzuwenden
 * Muss in ko.inc.php stehen (und nicht in leute/inc/leute.inc.php), damit ko_get_admin() immer Zugriff darauf hat --> z.B. für Dropdown-Menüs
*/
function apply_leute_filter($filter, &$where_code, $add_admin_filter=TRUE, $admin_filter_level='', $_login_id='', $includeAll=FALSE) {
	global $db_connection, $ko_menu_akt;

	//Set login_id if given as parameter (needed from mailing.php because ses_userid is not set there)
	if($_login_id != '') $login_id = $_login_id;
	else $login_id = $_SESSION['ses_userid'];

	//Innerhalb einer Filtergruppe werden die Filter mit OR verknüpft
	$where_code = "";
	$q = array();
	if(is_array($filter)) {

		//Move addchildren filter to the end, so it will be applied as the last filter
		$new = array(); $last = FALSE;
		foreach($filter as $f_i => $f) {
			ko_get_filter_by_id($f[0], $f_);
			if(in_array($f_['_name'], array('addchildren', 'addparents'))) $last = $f;
			else $new[$f_i] = $f;
		}
		$filter = $new;
		if($last) $filter[] = $last;

		//Loop through all filters and build SQL
		$filter_sql = array();
		foreach($filter as $f_i => $f) {
			if(!is_numeric($f_i)) continue;

			ko_get_filter_by_id($f[0], $f_);
			$f_typ = $f_['_name'];


			//Gruppen-, Rollen- und FilterVorlagen-Filter finden
			if(in_array($f_["_name"], array('group', 'role', 'filterpreset', 'rota', 'donation', 'grp data'))) {
				$link = $filter["link"] == "or" ? " OR " : " AND";
			} else {
				$link = "OR";
			}

			$f_sql = "";
			for($i = 1; $i <= sizeof($f[1]); $i++) {
				$f_sql_part = "";
				//Nur Leeres Argument erlauben, wenn es das einzige in diesem Filter ist
				if(sizeof($f[1]) == 1 || $f[1][$i] != "" || $f_["dbcol"] == "ko_groups_datafields_data.value") {

					//In jeder Zeile alle Werte VAR[1-3] ersetzen
					$trans = array(
							"[VAR1]" => format_userinput($f[1][1], "text"),
							"[VAR2]" => format_userinput($f[1][2], "text"),
							"[VAR3]" => format_userinput($f[1][3], "text")
					);
					//Add regex escaping
					if(FALSE !== strpos($f_["sql$i"], 'REGEXP')) {
						foreach($trans as $k => $v) {
							$trans[$k] = str_replace(array('(', ')'), array('\\\\(', '\\\\)'), $v);
						}
					}

					$f_sql_part .= strtr($f_["sql$i"], $trans)." AND ";

					//Leere Suchstrings gehen nur mit LIKE und nicht mit REGEXP!
					if($f[1][$i] == "") $f_sql_part = str_replace("REGEXP", "LIKE", $f_sql_part);
				}

				if(trim($f_sql_part) != "AND") $f_sql .= $f_sql_part;
			}


			//Alle AND's am Schluss entfernen (mehrere möglich!)
			while(substr(rtrim($f_sql), -4) == " AND") {
				$f_sql = substr(rtrim($f_sql), 0, -4);
			}

			//Handle group filter if old groups should not be displayed
			if($f_["_name"] == "group" && !ko_get_userpref($_SESSION['ses_userid'], 'show_passed_groups')) {
				ko_get_groups($all_groups);
				$not_leaves = db_select_distinct("ko_groups", "pid");
				$_gid = substr($f[1][1], 1);
				ko_get_groups($top, "AND `id` = '$_gid'");
				//Get subgroups of current group (if any) and exclude all with expired start and/or stop date
				$z_where = "AND ((`start` != '0000-00-00' AND `start` > NOW()) OR (`stop` != '0000-00-00' AND `stop` < NOW()))";
				rec_groups($top[$_gid], $children, $z_where, $not_leaves);
				foreach($children as $child) {
					//Get full id for child
					$motherline = ko_groups_get_motherline($child["id"], $all_groups);
					$mids = array();
					foreach($motherline as $mg) {
						$mids[] = "g".$all_groups[$mg]["id"];
					}
					$full_id = (sizeof($mids) > 0 ? implode(":", $mids).":" : "")."g".$child["id"];
					//Exclude children with expired start and/or stop date
					$f_sql .= ' AND `groups` NOT REGEXP '."'$full_id' ";
				}
			}
			//Add children filter
			if($f_['_name'] == 'addchildren') {
				//Clear all other filters if checkbox "only children" has been ticked
				if($f[1][3] == 'true') $q = array();
				//Apply all filters except for this one and get all famids for the filtered people
				$cf = $filter;
				unset($cf[$f_i]);
				apply_leute_filter($cf, $cwhere, $add_admin_filter, $admin_filter_level, $login_id, $includeAll);
				$families = db_select_data('ko_leute', 'WHERE famid > 0 AND famfunction IN (\'husband\', \'wife\') '.$cwhere, 'id,famid');
				if(sizeof($families) == 0) {
					$f_sql = '';
				} else {
					$famids = array();
					foreach($families as $fam) $famids[] = $fam['famid'];
					$f_sql = " `famid` IN ('".implode("','", array_unique($famids))."') AND `famfunction` = 'child' ".($f_sql ? "AND ".$f_sql : '');
				}
			}
			//Add parents filter
			if($f_['_name'] == 'addparents') {
				//Clear all other filters if checkbox "only parents" has been ticked
				if($f[1][1] == 'true') $q = array();
				//Apply all filters except for this one and get all famids for the filtered people
				$cf = $filter;
				unset($cf[$f_i]);
				apply_leute_filter($cf, $cwhere, $add_admin_filter, $admin_filter_level, $login_id, $includeAll);
				$families = db_select_data('ko_leute', 'WHERE famid > 0 AND famfunction = \'child\' '.$cwhere, 'id,famid');
				if(sizeof($families) == 0) {
					$f_sql = '';
				} else {
					$famids = array();
					foreach($families as $fam) $famids[] = $fam['famid'];
					$f_sql = " `famid` IN ('".implode("','", array_unique($famids))."') AND `famfunction` IN ('husband', 'wife') ";
				}
			}
			//Find possible duplicates
			if($f_['_name'] == 'duplicates') {
				$where = 'WHERE 1 ';
				//Get leute_admin_filter
				apply_leute_filter(array(), $dwhere, $add_admin_filter, $admin_filter_level, $login_id, $includeAll);
				//Field to test for
				$fields = explode('-', $f[1][1]);
				foreach($fields as $field) {
					$where .= ' AND (`'.$field.'` != \'\' AND `'.$field.'` != \'0000-00-00\') '.$dwhere;
				}
				$all = db_select_data('ko_leute', $where, '*');

				//Build test string for all persons
				$test = array();
				foreach($all as $person) {
					$value = array();
					foreach($fields as $field) $value[] = mb_strtolower($person[$field]);
					$test[$person['id']] = implode('#', $value);
				}
				unset($all);
				//Find dups (only one is left in $dups)
				$dups = array_unique(array_diff_assoc($test, array_unique($test)));
				//Add the removed entries which are the doubles to the ones in $dups
				if(sizeof($dups) > 0) {
					$ids = array_keys($dups);
					foreach($test as $tid => $t) {
						if(in_array($t, $dups)) $ids[] = $tid;
					}
					$ids = array_unique($ids);
					$f_sql = " `id` IN ('".implode("','", $ids)."') ";
				} else {
					$f_sql = ' 1=2 ';
				}
			}

			//find special filters for other db tables
			list($db_table, $db_col) = explode(".", $f_["dbcol"]);
			if($db_table && $db_col) {
				//special group datafields filter
				if($db_table == "ko_groups_datafields_data") {
					$rows = db_select_data("ko_groups_datafields_data", "WHERE $f_sql", "person_id");
					$ids = NULL;
					foreach($rows as $row) {
						if(!$row["person_id"]) continue;
						$ids[] = "'".$row["person_id"]."'";
					}
					if(is_array($ids)) {
						$f_sql = "`id` IN (".implode(",", $ids).")";
					} else {
						$f_sql = " 1=2 ";
					}
				}
				//special small group filters
				else if($db_table == "ko_kleingruppen") {
					$rows = db_select_data("ko_kleingruppen", "WHERE $f_sql", "id");
					$ids = NULL;
					foreach($rows as $row) {
						if(!$row["id"]) continue;
						$ids[] = $row["id"];
					}
					if(is_array($ids)) {
						$kg_sql = array();
						foreach($ids as $id) {
							$kg_sql[] = "`smallgroups` REGEXP '(^|,)$id($|,|:)'";
						}
						$f_sql = implode(" OR ", $kg_sql);
					} else {
						$f_sql = " 1=2 ";
					}
				}
				//special rota filter
				else if($db_table == 'ko_rota_schedulling') {
					//If no SQL given so far (should contain SQL for selected eventID), then don't display anything. Only happens if no event was selected
					if(!$f_sql) $f_sql = ' 1=2 ';
					else {
						//Add year-week to find scheduling from weekly teams (Dienstwochen)
						$event = db_select_data('ko_event', "WHERE `id` = '".$f[1][1]."'", '*', '', '', TRUE);
						$f_sql = "( $f_sql OR `event_id` = '".date('Y-W', strtotime($event['startdatum']))."') ";
					}

					//Check for selected rota team preset
					if($f[1][2] != '') {
						if(substr($f[1][2], 0, 3) == '@G@') $value = ko_get_userpref('-1', substr($f[1][2], 3), 'rota_itemset');
						else $value = ko_get_userpref($_SESSION['ses_userid'], $f[1][2], 'rota_itemset');
						//Check for team ID of a single team
						if(!$value && intval($f[1][2]) > 0) {
							$team = db_select_data('ko_rota_teams', "WHERE `id` = '".intval($f[1][2])."'", '*', '', '', TRUE);
							if($team['id'] > 0 && $team['id'] == intval($f[1][2])) $rota_teams = array($team['id']);
						} else {
							$rota_teams = explode(',', $value[0]['value']);
							$rota_teams = array_unique($rota_teams);
						}
						foreach($rota_teams as $k => $v) {
							if(!$v || !intval($v)) unset($rota_teams[$k]);
						}
						if(sizeof($rota_teams) > 0) {
							$f_sql .= " AND `team_id` IN (".implode(',', $rota_teams).") ";
						} else {
							//No team selected in this preset so don't show anything
							$f_sql .= " AND 1=2 ";
						}
					}

					$rows = db_select_data('ko_rota_schedulling', 'WHERE '.$f_sql, 'schedule');
					$ids = array();
					$gids = array();
					foreach($rows as $row) {
						if(!$row['schedule']) continue;
						foreach(explode(',', $row['schedule']) as $pid) {
							//Group ID
							if(strlen($pid) == 7 && substr($pid, 0, 1) == 'g') {
								$gids[] = $pid;
							} else {
								//Person ID
								if(!$pid || format_userinput($pid, 'uint') != $pid) continue;
								$ids[] = $pid;
							}
						}
					}
					foreach($ids as $key => $value) if(!$value) unset($ids[$key]);
					$ids = array_unique($ids);
					foreach($gids as $key => $value) if(!$value) unset($gids[$key]);
					$gids = array_unique($gids);
					if(sizeof($ids) > 0 || sizeof($gids) > 0) {
						$f_sql = '';
						if(sizeof($ids) > 0) $f_sql = '`id` IN ('.implode(',', $ids).')';
						if(sizeof($gids) > 0) {
							foreach($gids as $gid) {
								$f_sql .= $f_sql != '' ? " OR `groups` LIKE '%$gid%' " : " `groups` LIKE '%$gid%' ";
							}
						}
					} else {
						$f_sql = ' 1=2 ';
					}
				}
				//Apply another filter preset
				else if($db_table == "ko_filter") {
					if(substr($f_sql, 0, 3) == '@G@') $preset = ko_get_userpref('-1', substr($f_sql, 3), 'filterset');
					else $preset = ko_get_userpref($login_id, $f_sql, 'filterset');
					if($preset[0]["key"]) {  //preset found
						//Get filter and convert it into a WHERE clause
						apply_leute_filter(unserialize($preset[0]['value']), $filter_where, $add_admin_filter, $admin_filter_level, $login_id, $includeAll);
						//Get ids of people fitting this condition
						$rows = db_select_data("ko_leute", "WHERE 1=1 ".$filter_where, "id");
						//Convert it into an id-list for an IN () statement
						$ids = NULL;
						foreach($rows as $row) {
							if(!$row["id"]) continue;
							$ids[] = $row["id"];
						}
						if(sizeof($ids) > 0) {
							$f_sql = "`id` IN (".implode(",", $ids).")";
						} else {  //No ids found, so add a false condition
							$f_sql = " 1=2 ";
						}
					} else {  //no preset found
						$f_sql = "";
					}
				}
				//Apply filter for a given year of donations made
				else if($db_table == 'ko_donations') {
					$rows = db_select_distinct('ko_donations', 'person', "", "WHERE `person` != '' AND `promise` = '0' AND ".$f_sql);
					$ids = array();
					foreach($rows as $id) {
						if(FALSE !== strpos($id, ',')) {  //find entries with multiple persons assigned to one donation
							$ids = array_merge($ids, explode(',', format_userinput($id, 'intlist')));
						} else {
							$ids[] = intval($id);
						}
					}
					$ids = array_unique($ids);
					foreach($ids as $key => $value) if(!$value) unset($ids[$key]);
					if(sizeof($ids) > 0) {
						$f_sql = '`id` IN ('.implode(',', $ids).')';
					} else {
						$f_sql = ' 1=2 ';
					}
				}
				else if($db_table == 'ko_admin') {
					if(FALSE !== strpos($f_sql, '_all')) $f_sql = '1';
					if($_SESSION['ses_userid'] != ko_get_root_id()) $f_sql .= " AND `id` != '".ko_get_root_id()."'";
					$f_sql .= " AND `id` != '".ko_get_guest_id()."' ";
					$rows = db_select_data($db_table, 'WHERE '.$f_sql, 'leute_id');
					$ids = array();
					foreach($rows as $row) {
						if(!$row['leute_id']) continue;
						$ids[] = format_userinput($row['leute_id'], 'uint');
					}
					foreach($ids as $key => $value) if(!$value) unset($ids[$key]);
					$ids = array_unique($ids);
					if(sizeof($ids) > 0) {
						$f_sql = '`id` IN ('.implode(',', $ids).')';
					} else {
						$f_sql = ' 1=2 ';
					}
				}
			}//if(db_table && db_col)


			if(trim($f_sql) != "") {
				if($f[2]) {  //Negativ
					$q[$f_typ] .= " ( !($f_sql) ) $link ";
					$filter_sql[$f_i] = "!($f_sql)";
				} else {
					$q[$f_typ] .= " ( $f_sql ) $link ";
					$filter_sql[$f_i] = $f_sql;
				}
			}
		}//foreach(filter)
	}//if(is_array(filter))

	//Einzelne Filter-Gruppen mit AND verbinden und letztes OR löschen
	$done_adv_link = FALSE;
	if($filter['use_link_adv'] === TRUE && $filter['link_adv'] != '') {
		//Replace all numbers with {{d}}
		$link_adv = preg_replace('/(\d+)/', '{{$1}}', $filter['link_adv']);

		//Prepare mapping array for all applied filters
		$filter_map = array();
		foreach($filter_sql as $k => $v) {
			if(!is_numeric($k)) continue;
			$filter_map['{{'.$k.'}}'] = '('.$v.')';
		}

		//Replace OR/AND from current language
		$link_adv = str_replace(array(getLL('filter_OR'), getLL('filter_AND')), array('OR', 'AND'), mb_strtoupper($link_adv));

		//Remove not allowed characters
		$allowed = array('0','1','2','3','4','5','6','7','8','9', '{', '}', 'O', 'R', 'A', 'N', 'D', '(', ')', ' ', '!');
		$new_link_adv = '';
		for($i=0; $i<strlen($link_adv); $i++) {
			if(in_array(substr($link_adv, $i, 1), $allowed)) {
				$new_link_adv .= substr($link_adv, $i, 1);
			}
		}
		$link_adv = $new_link_adv;

		$where_code = str_replace(array_keys($filter_map), array_values($filter_map), $link_adv);

		//Check for valid SQL
		$result = mysqli_query($db_connection, 'SELECT `id` FROM `ko_leute` WHERE '.$where_code);
		if(FALSE === $result) {
			$where_code = '';
			$_SESSION['filter']['use_link_adv'] = FALSE;
		} else {
			$done_adv_link = TRUE;
		}
	}

	//Apply regular link (OR/AND) if no adv_link is set, or if advanced caused SQL error
	if(!$done_adv_link) {
		$link = $filter["link"] == "or" ? " OR " : " AND ";
		if(sizeof($q) > 0) {
			foreach($q as $type => $q_) {
				if(trim(substr($q_, 0, -4)) != "") {
					$q_ = " ( " . substr($q_, 0, -4) . " ) ";
					//Use the link for all filters except for addchildren, which is always added with OR
					$where_code .= ($type == 'addchildren' || $type == 'addparents' ? ' OR ' : $link).$q_;
				}
			}
		}
		$where_code = substr($where_code, 4);  //Erstes OR löschen
	}

	//Admin-Filter anwenden
	if($add_admin_filter) {
		//add all filters from applied admingroups first
		$add_rights = array();
		$admingroups = ko_get_admingroups($login_id);
		foreach($admingroups as $ag) {
			$add_rights[] = "admingroup:".$ag["id"];
		}
		$add_rights[] = "login";
		foreach($add_rights as $type) {
			if(substr($type, 0, 10) == "admingroup") {
				list($type, $use_id) = explode(":", $type);
			} else {
				$use_id = $login_id;
			}
			$laf = ko_get_leute_admin_filter($use_id, $type);
			if(sizeof($laf) > 0) {
				if($admin_filter_level != "") {
					//apply only given level (if set) for ko_get_admin()
					if(isset($laf[$admin_filter_level]["filter"])) {
						apply_leute_filter($laf[$admin_filter_level]["filter"], $where, FALSE, '', $login_id, $includeAll);
						if(trim(substr($where, 4)) != "") $admin_code .= "( ".substr($where, 4)." ) OR ";
					}
				} else {
					//apply all levels for read access
					for($i=1; $i<4; $i++) {
						if(!isset($laf[$i]["filter"])) continue;
						apply_leute_filter($laf[$i]["filter"], $where, FALSE, '', $login_id, $includeAll);
						if(trim(substr($where, 4)) != "") $admin_code .= "( ".substr($where, 4)." ) OR ";
					}//for(i=1..3)
				}
			}//if(sizeof(laf))
		}
		if($admin_code != "") $admin_code = substr($admin_code, 0, -3);
	}//if(add_admin_filter)

	if(trim($where_code) != "") {
		if(trim($admin_code) != "") {
			$where_code = " AND ($where_code) AND ($admin_code) ";
		} else {
			$where_code = " AND $where_code ";
		}
	} else {
		if($admin_code != "") {
			$where_code = " AND $admin_code ";
		}
	}

	//Check if hidden filter is applied. If yes then don't apply "hidden=0" below
	$hiddenfilter = db_select_data('ko_filter', "WHERE `name` = 'hidden'", '*', '', '', TRUE);
	$hidden_is_set = FALSE;
	foreach($filter as $f) {
		if($f[0] == $hiddenfilter['id']) $hidden_is_set = TRUE;
	}

	//deleted ausblenden
	if($includeAll) {
		$deleted = '';
	} else {
		$deleted = ($ko_menu_akt == "leute" && ko_get_userpref($login_id, "leute_show_deleted") == 1)
							 ? " AND `deleted` = '1' "
							 : " AND `deleted` = '0' ";
		//retrieve deleted if old version is to be displayed.
		//They are eliminated later (in ko_get_leute()), if they have been deleted in the desired version.
		$deleted = $_SESSION["leute_version"] ? "" : $deleted;
	}
	//Add SQL for hidden records
	if(!$hidden_is_set) $deleted .= ko_get_leute_hidden_sql();

	if($where_code) {
		$where_code = " AND ( ".substr($where_code, 5)." ) ".$deleted;
		return TRUE;
	} else {
		$where_code = $deleted;
		return FALSE;
	}
}//apply_leute_filter()




function ko_get_leute_hidden_sql() {
	$sql = "";

	switch(ko_get_setting("leute_hidden_mode")) {
		case 0:  //Show always
		break;
		case 1:  //Hide always
			$sql = " AND hidden = '0' ";
		break;
		case 2:  //Let user decide
			if(ko_get_userpref($_SESSION["ses_userid"], "leute_show_hidden") == 0) {
				$sql = " AND hidden = '0' ";
			}
		break;
	}

	return $sql;
}//get_leute_hidden_sql()



/**
  * Liefert Formular zu einzelnem Filter
	* Für Ajax und submenu.inc.php
	*/
function ko_get_leute_filter_form($fid, $showButtons=TRUE) {
	$code = "";

	ko_get_filter_by_id($fid, $f);

	$code_filter = array();
	for($i = 1; $i <= $f["numvars"]; $i++) {
		$code_filter[]  = getLL("filter_".$f["var$i"]) ? getLL("filter_".$f["var$i"]) : $f["var$i"];
		$code_filter[] .= $f["code$i"];
	}

	foreach($code_filter as $c) {
		$code .= $c."<br />";
	}


	$code_filter_zusatz = "";
	if($f["allow_neg"]) {
		$code_filter_zusatz .= '<input type="checkbox" name="filter_negativ" id="filter_neg" /><label for="filter_neg">'.getLL('filter_negativ').'</label>';
	} else {
		$code_filter_zusatz .= '<input type="hidden" name="filter_negativ" id="filter_neg" value="0" />';
	}

	if($showButtons) {
		$code_filter_zusatz .= '<p align="center">';
		$code_filter_zusatz .= '<input type="button" value="'.getLL("filter_add").'" name="submit_filter" onclick="javascript:do_submit_filter(\'leutefilter\', \''.session_id().'\');" />';
		$code_filter_zusatz .= '&nbsp;&nbsp;';
		$code_filter_zusatz .= '<input type="button" value="'.getLL("filter_replace").'" name="submit_filter_new" onclick="javascript:do_submit_filter(\'leutefilternew\', \''.session_id().'\');" />';
		$code_filter_zusatz .= '</p>';
	}


	$code .= $code_filter_zusatz;
	return ('<div name="filter_form" class="filter-form">'.$code.'</div>');
}//ko_get_leute_filter_form()





/**
 * Gibt formatierte Personendaten zurück
 * @param $data enthält den Wert aus der DB
 * @param $col ist die DB-Spalte
 * @param $p ist eine Referenz auf den ganzen Personendatensatz
 * @param array $all_datafields ist ein Array aller Datenfelder
 * @param bool $forceDatafields: Damit werden die Datenfelder miteinbezogen, auch wenn userpref nicht gesetzt ist. (Z.B. von TYPO3-Ext kool_leute)
 * @param array $options: array with options
 */
function map_leute_daten($data, $col, &$p, &$all_datafields=array(), $forceDatafields=FALSE, $_options=NULL) {
	global $DATETIME, $KOTA;
	global $all_groups;
	global $access, $ko_path;
	global $LEUTE_EMAIL_FIELDS, $LEUTE_MOBILE_FIELDS;

	if(!is_array($all_groups)) ko_get_groups($all_groups);

	//Datenbank-Spalten-Info holen, falls es keine Modul-Spalte ist (die nicht direkt als Leute-Spalte gespeichert ist)
	if(substr($col, 0, 6) != "MODULE") {
		if(!$data && substr($col, 0, 1) != "_") return "";
		$db_col = db_get_columns("ko_leute", $col);
	}

	//Call KOTA list function if set
	if(substr($KOTA['ko_leute'][$col]['list'], 0, 4) == 'FCN:') {
		$fcn = substr($KOTA['ko_leute'][$col]['list'], 4);
		if(function_exists($fcn) && $fcn != 'kota_map_leute_daten') {
			$kota_data = array('table' => 'ko_leute', 'col' => $col, 'id' => $p['id'], 'dataset' => $p);
			eval("$fcn(\$data, \$kota_data);");
			return $data;
		}
	}


	if($col == "groups") {  //Used for group filters and the groups-column in the excel export (the HTML view is created in leute.inc.php)
		$value = NULL;
		if(substr($data, 0, 1) == "r" || substr($data, 0, 2) == ":r") {  //Rolle
			ko_get_grouproles($role, "AND `id` = '".substr($data, (strpos($data, "r")+1))."'");
			return $role[substr($data, (strpos($data, "r")+1))]["name"];
		} else {  //Gruppe(n)
			if(!isset($access['groups'])) ko_get_access('groups');
			foreach(explode(',', $data) as $g) {
				$gid = ko_groups_decode($g, 'group_id');
				if($g
					&& ($access['groups']['ALL'] > 0 || $access['groups'][$gid] > 0)
					&& (ko_get_userpref($_SESSION['ses_userid'], 'show_passed_groups') == 1 || ($all_groups[$gid]['start'] <= date('Y-m-d') && ($all_groups[$gid]['stop'] == '0000-00-00' || $all_groups[$gid]['stop'] > date('Y-m-d'))))
					) {
					$value[] = ko_groups_decode($g, 'group_desc_full');
				}
			}
			sort($value);
			return implode(", \n", $value);
		}
	} else if($col == "datafield_id") {  //Angewandte Gruppen-Datenfelder-Filter schön darstellen
		if(strlen($data) == 6 && format_userinput($data, "uint") == $data) {
			$df = db_select_data("ko_groups_datafields", "WHERE `id` = '$data'", "*", "", "", TRUE);
			return $df["description"];
		} else {
			return $data;
		}
	} else if($db_col[0]["Type"] == "date") {  //Datums-Typen von SQL umformatieren
		if($data == "0000-00-00") return "";
		return strftime($DATETIME["dmY"], strtotime($data));
	} else if(substr($db_col[0]["Type"],0,4) == "enum") {  //Find ll values for enum
		$ll_value = getLL('kota_ko_leute_'.$col.'_'.$data);
		return ($ll_value ? $ll_value : $data);
	} else if ($col == 'smallgroups') {  //Smallgroups
		return ko_kgliste($data);
	} else if ($col == "famid") {
		$fam = ko_get_familie($data);
		return $fam["id"]." ".getLL('kota_ko_leute_famfunction_short_'.$p['famfunction']);
	} else if(substr($db_col[0]["Type"], 0, 7) == "tinyint") {  //Treat tinyint as checkbox
		return ($data ? getLL("yes") : getLL("no"));
	} else if(substr($col, 0, 1) == "_") {  //Children export columns
		if(!$p["famid"] || $p["famfunction"] != "child") return "";
		switch($col) {
			case "_father":
			case "_mother":
				$func = $col == "_father" ? "husband" : "wife";
				$d = db_select_data("ko_leute", "WHERE `famid` = '".$p["famid"]."' AND `famfunction` = '$func' AND `deleted` = '0'", "*", "", "", TRUE);
				return $d["vorname"]." ".$d["nachname"];
			break;  //father
			default:
				if(in_array(substr($col, 0, 8), array("_father_", "_mother_"))) {
					$p_col = substr($col, 8);
					$fam_function = substr($col, 0, 7) == "_father" ? "'husband'" : "'wife'";
				} else {
					$p_col = substr($col, 1);
					$fam_function = "'husband', 'wife'";
				}
				$d = db_select_data("ko_leute", "WHERE `famid` = '".$p["famid"]."' AND `famfunction` IN ($fam_function) AND `$p_col` != '' AND `deleted` = '0'", "*", "ORDER BY famfunction ASC");
				foreach($d as $e) {
					if(sizeof($LEUTE_EMAIL_FIELDS) > 1 && in_array($p_col, $LEUTE_EMAIL_FIELDS)) {
						ko_get_leute_email($e, $email);
						if($email[0]) return $email[0];
					} else if(sizeof($LEUTE_MOBILE_FIELDS) > 1 && in_array($p_col, $LEUTE_MOBILE_FIELDS)) {
						ko_get_leute_mobile($e, $mobile);
						if($mobile[0]) return $mobile[0];
					} else {
						if($e[$p_col]) return $e[$p_col];
					}
				}
				return "";
		}
	} else if(substr($col, 0, 6) == "MODULE") {

		//Gruppen-Modul: Rolle in entsprechender Gruppe anzeigen
		if(substr($col, 6, 3) == 'grp') {
			//Only group given, datafields have :
			if(FALSE === strpos($col, ':')) {
				$gid = substr($col, 9);
				$value = array();
				$data = $p["groups"];
				foreach(explode(",", $data) as $group) {
					//Don't display groups with start or stop date if settings show_passed_groups is not set.
					$_gid = ko_groups_decode($group, "group_id");
					$stop = FALSE;
					if(!ko_get_userpref($_SESSION['ses_userid'], 'show_passed_groups')) {
						$motherline = array_merge(array($_gid), ko_groups_get_motherline($_gid, $all_groups));
						foreach($motherline as $mg) {
							if($all_groups[$mg]["stop"] != "0000-00-00" && time() > strtotime($all_groups[$mg]["stop"])) $stop = TRUE;
							if($all_groups[$mg]["start"] != "0000-00-00" && time() < strtotime($all_groups[$mg]["start"])) $stop = TRUE;
						}
					}
					if($stop) continue;
					if($gid == $_gid) {  //Assigned to this group, and not one of the subgroups
						$v = ko_groups_decode($group, "role_desc");
						$value[] = $v ? ko_html($v) : 'x';
					} else if(in_array($gid, ko_groups_decode($group, "mother_line"))) {  //Check for assignement to a subgroup
						$value[] = '<a href="#" onclick="'."sendReq('../leute/inc/ajax.php', 'action,id,state,sesid', 'itemlist,MODULEgrp".$_gid.",switch,".session_id()."', do_element);return false;".'">&rsaquo;&thinsp;'.ko_html(ko_groups_decode($group, "group_desc"))."</a>";
					}
				}
				return implode(",<br />\n", $value);
			}
			else {
				//output datafields of this group
				list($_col, $dfid) = explode(':', $col);
				$gid = substr($_col, 9);
				$value = array();
				if($all_groups[$gid]['datafields']) {
					$group_dfs = explode(',', $all_groups[$gid]['datafields']);
					//check for valid datafield
					if(!isset($all_datafields[$dfid]) || !in_array($dfid, $group_dfs)) return '';

					//Get datafield value (versioning handled in function)
					$value = ko_get_datafield_data($gid, $dfid, $p['id'], $_SESSION['leute_version'], $all_datafields, $all_groups);
					if($value['typ'] == 'checkbox') {
						return $value['value'] == '1' ? ko_html(getLL('yes')) : ko_html(getLL('no'));
					} else {
						return ko_html($value['value']);
					}
				}
			}
		} else if(substr($col, 6, 2) == 'kg') {
			$sg_col = substr($col, 8);
			$value = array();
			foreach(explode(',', $p['smallgroups']) as $sgid) {
				$id = substr($sgid, 0, 4);
				if(!$id) continue;
				$sg = ko_get_smallgroup_by_id($id);

				if(isset($sg[$sg_col]) && $sg[$sg_col] != '') {
					$data = array($sg_col => $sg[$sg_col]);
					kota_process_data('ko_kleingruppen', $data, 'list', $log);
					$value[] = strip_tags($data[$sg_col]);
				}
				//Store empty value if option firstOnly is set.
				// Otherwise the numbering is not the same for all fields which can lead to a mix of values from different small groups
				else if($_options['MODULEkg_firstOnly']) {
					$value[] = '';
				}

				/*
				if($sg_col == 'picture') {
					$value[] = ko_pic_get_tooltip(str_replace('my_images/', '', $sg[$sg_col]), 25, 200, 'm', 'l');
				} else {
					if(isset($sg[$sg_col]) && $sg[$sg_col] != '') $value[] = $sg[$sg_col];
				}
				*/
			}
			if($_options['MODULEkg_firstOnly']) {
				return $value[0];
			} else {
				$value = array_unique($value);
				return implode(', ', $value);
			}
		}
		else if(substr($col, 6, 8) == 'tracking') {  //Tracking column
			$tfid = substr($col, 14);
			if(!$tfid) return '';

			$delimiterPosition = strpos($tfid, 'f');
			if ($delimiterPosition == false) {
				$tid = (int) $tfid;
			}
			else {
				$tid = substr($tfid, 0, $delimiterPosition);
				$fid = substr($tfid, $delimiterPosition + 1);
			}

			if (!$tid) return '';

			$db_filter = '';
			if ($fid) {
				$filter = db_select_data('ko_userprefs', 'WHERE `id` = ' . format_userinput($fid, 'uint'), '`value`', '', '', TRUE, TRUE);
				list($start,$stop) = explode(',', $filter['value']);
				$db_filter = ' AND `date` >= \'' . $start . '\' AND `date` <= \'' . $stop . '\'';
			}

			$tracking = db_select_data('ko_tracking', "WHERE `id` = '$tid'", '*', '', '', TRUE);
			if(!$tracking['id'] || $tracking['id'] != $tid) return '';
			$value = '';
			switch($tracking['mode']) {
				case 'type':
				case 'typecheck':
					$values = array();
					$entries = db_select_data('ko_tracking_entries', "WHERE `tid` = '$tid' AND `lid` = '".$p['id']."'" . $db_filter);
					foreach($entries as $e) {
						$values[$e['type']] += (float)$e['value'];
					}
					$v = array();
					foreach(explode("\n", $tracking['types']) as $type) {
						$type = trim($type);
						if(!$values[$type]) continue;
						$v[] = $values[$type].'x'.$type;
					}
					$value = implode(', ', $v);
				break;

				case 'simple':
					$value = db_get_count('ko_tracking_entries', 'value', "AND `tid` = '$tid' AND `lid` = '".$p['id']."'" . $db_filter);
				break;

				case 'value':
					$sum = db_select_data('ko_tracking_entries', "WHERE `tid` = '$tid' AND `lid` = '".$p['id']."'" . $db_filter, 'id, SUM(`value`) as sum', '', '', TRUE);
					$value = $sum['sum'];
				break;

				case 'valueNonNum':
					$values = array();
					$entries = db_select_data('ko_tracking_entries', "WHERE `tid` = '$tid' AND `lid` = '".$p['id']."'" . $db_filter);
					foreach($entries as $e) {
						$values[$e['value']] += 1;
					}
					ksort($values);
					$v = array();
					foreach($values as $type => $value) {
						if(!$type || !$value) continue;
						$v[] = $value.'x'.$type;
					}
					$value = implode(', ', $v);
				break;
			}
			//Add link to edit tracking for this person
			$url = $ko_path.'tracking/index.php?action=enter_tracking&id='.$tracking['id'].'#tp'.$p['id'];
			$value = '<a href="'.$url.'" title="'.getLL('leute_show_tracking_for_person').'">'.$value.'</a>';
			return $value;
		}
		else if(substr($col, 6, 6) == 'plugin') {  //Column added by a plugin
			$fcn = 'my_leute_column_map_'.substr($col, 12);
			if(function_exists($fcn)) {
				eval("\$value = $fcn(\$data, \$col, \$p);");
				return $value;
			}
		}
		else if(substr($col, 6, 5) == 'famid') {  //Family columns
			if($p['famid'] > 0) {
				if($col == 'MODULEfamid_famlastname') {
					$family = ko_get_familie(intval($p['famid']));
					$value = '';
					if($family['famanrede'] != '') $value .= $family['famanrede'];
					if($family['famfirstname'] != '') $value .= ($value != '' ? ', ' : '').$family['famfirstname'];
					if($family['famlastname'] != '') $value .= ($value != '' ? ', ' : '').$family['famlastname'];
					return $value;
				} else if($p['famfunction'] == 'child') {
					$famfunction = substr($col, 12);
					if(!in_array($famfunction, array('husband', 'wife'))) return '';

					$rel = db_select_data('ko_leute', "WHERE `famid` = '".intval($p['famid'])."' AND `famfunction` = '$famfunction' AND `deleted` = '0' AND `hidden` = '0'", '*', '', '', TRUE);
					if($rel['id']) {
						$kotadata = array('col' => 'id', 'dataset' => array('id' => $rel['id']));
						kota_listview_people($value, $kotadata, TRUE);
						return $value;
					} else return '';
				} else return '';
			} else return '';
		}
		//Donations
		else if(substr($col, 6, 9) == 'donations') {
			$year = format_userinput(substr($col, 15, 4), 'uint');
			$account_id = format_userinput(substr($col, 19), 'uint');
			if(!$year || $year < 1900 || $year > 3000) return '';

			$datefield = ko_get_userpref($_SESSION['ses_userid'], 'donations_date_field');
			if(!$datefield) $datefield = 'date';
			$where = " WHERE `person` = '".$p['id']."' AND YEAR(`$datefield`) = '$year' AND `promise` = '0' ";
			if($account_id > 0) $where .= " AND `account` = '$account_id'";

			$amount = db_select_data('ko_donations', $where, 'SUM(`amount`) AS total_amount', '', '', TRUE);
			if($amount['total_amount'] > 0) {
				return number_format($amount['total_amount'], 2, '.', "'");
			} else {
				return '';
			}
		}
	} else if($col == "`event_id`") {  //Used for rota special filter
		if($_options['num'] == 1) {  //first variable is eventID
			$event = db_select_data('ko_event', "WHERE `id` = '$data'", '*', '', '', TRUE);
			$group = db_select_data('ko_eventgruppen', "WHERE `id` = '".$event['eventgruppen_id']."'", '*', '', '', TRUE);
			return strftime($DATETIME['dmy'], strtotime($event['startdatum'])).': '.$group['name'];
		} else if($_options['num'] == 2) {  //Second variable is team preset or teamID
			if(intval($data) != 0 && intval($data) == $data) {  //TeamID if Integer
				$team = db_select_data('ko_rota_teams', "WHERE `id` = '".intval($data)."'", '*', '', '', TRUE);
				return $team['name'];
			} else {  //Rota teams preset otherwise
				return $data;
			}
		}
	} else if($col == '`account`') {  //Used for donation special filter
		$account = db_select_data('ko_donations_accounts', 'WHERE `id` = \''.$data.'\'', '*', '', '', TRUE);
		return ($account['number'] ? $account['number'] : $account['name']);
	} else if($col == 'plz') {  //For special filter 'plz IN (...)' with long list of zip codes (pfimi bern)
    return strlen($data) > 20 ? substr($data, 0, 20).'..' : $data;
	} else if($db_col[0]['Type'] == 'tinytext') {  //Picture
		return ko_pic_get_tooltip($data, 25, 200, 'm', 'l', TRUE);
	} else if($col == '`admingroups`') {  //Used for special filter 'logins'
		if($data == '_all') return getLL('all');
		else {
			$admingroup = db_select_data('ko_admingroups', "WHERE `id` = '".(int)$data."'", '*', '', '', TRUE);
			return $admingroup['name'];
		}
	} else if($col == 'wochentag') {  //Find ll values for ko_kleingruppen.wochentag
		$ll_value = getLL('kota_ko_kleingruppen_'.$col.'_'.$data);
		return ($ll_value ? $ll_value : $data);
	}
	else {  //Den Rest wie gehabt ausgeben
		$ll_value = getLL('kota_ko_leute_'.$col.'_'.$data);
		return ($ll_value ? $ll_value : $data);
	}

}//map_leute_daten()




/**
 * Get email address for a given person
 * Uses preferred email address as defined in ko_leute_preferred_fields.
 * If no preferred use the first one according to $LEUTE_EMAIL_FIELDS
 */
function ko_get_leute_email($p, &$email) {
	global $LEUTE_EMAIL_FIELDS;

	$email = array();

	if(!is_array($p)) {
		ko_get_person_by_id($p, $person);
		$p = $person;
	}

	//Get preferred email field from ko_leute_preferred_fields
	$email_fields = db_select_data('ko_leute_preferred_fields', "WHERE `type` = 'email' AND `lid` = '".$p['id']."'", '*');

	//First try to use email fields as defined in ko_leute_preferred_fields
	foreach($email_fields as $row) {
		if(check_email($p[$row['field']])) $email[] = $p[$row['field']];
	}
	//If none have been found use first email field in order given in LEUTE_EMAIL_FIELDS
	if(sizeof($email) == 0) {
		foreach($LEUTE_EMAIL_FIELDS as $field) {
			if(check_email($p[$field])) {
				$email[] = $p[$field];
				break;
			}
		}
	}

	//Return status: TRUE if at least one address has been found
	return sizeof($email) > 0;
}//ko_get_leute_email()




/**
 * Get mobile number for a given person
 * Uses preferred mobile number as defined in ko_leute_preferred_fields.
 * If no preferred use the first one according to $LEUTE_MOBILE_FIELDS
 */
function ko_get_leute_mobile($p, &$mobile) {
	global $LEUTE_MOBILE_FIELDS;

	$mobile = array();

	if(!is_array($p)) {
		ko_get_person_by_id($p, $person);
		$p = $person;
	}

	//Get preferred mobile field from ko_leute_preferred_fields
	$mobile_fields = db_select_data('ko_leute_preferred_fields', "WHERE `type` = 'mobile' AND `lid` = '".$p['id']."'", '*');

	//First try to use mobile fields as defined in ko_leute_preferred_fields
	foreach($mobile_fields as $row) {
		if($p[$row['field']]) $mobile[] = $p[$row['field']];
	}
	//If none have been found use first mobile field in order given in LEUTE_MOBILE_FIELDS
	if(sizeof($mobile) == 0) {
		foreach($LEUTE_MOBILE_FIELDS as $field) {
			if($p[$field]) {
				$mobile[] = $p[$field];
				break;
			}
		}
	}

	//Return status: TRUE if at least one number has been found
	return sizeof($mobile) > 0;
}//ko_get_leute_mobile()




/**
 * Get values from db table ko_leute_preferred_fields as an array
 * First index is the person's id, second index is the type (email, mobile)
 */
function ko_get_preferred_fields($type='') {
	$preferred_fields = array();

	if($type) $where = "WHERE `type` = '$type'";
	else $where = 'WHERE 1';

	$rows = db_select_data('ko_leute_preferred_fields', $where, '*');
	foreach($rows as $row) {
		$preferred_fields[$row['lid']][$row['type']][] = $row['field'];
	}
	return $preferred_fields;
}//ko_get_preferred_fields()




/**
 * Store an old state of an edited person record in database for versioning
 * @param $id ID of person's dataset
 * @param $data array holding the current data for this record, which will be stored serialized
 * @param $df array holding all datafield data for this user. Will be fetched from db if empty
 * @param $uid int ID of kOOL login to assign this change to, usually ses_userid
 */
function ko_save_leute_changes($id, $data='', $df='', $uid='') {
	if(!$id) return FALSE;

	$uid = intval($uid) > 0 ? intval($uid) : $_SESSION['ses_userid'];

	//Don't allow two changes by the same user within one second
	// (might happen when editing a person belonging to a family. Then the family members get updated as well)
	$same = db_get_count("ko_leute_changes", "id", "AND `leute_id` = '$id' AND `user_id` = '$uid' AND (UNIX_TIMESTAMP() - UNIX_TIMESTAMP(`date`) <= 1)");
	if($same > 0) return FALSE;

	//Get person from db if not given
	if(!is_array($data)) {
		ko_get_person_by_id($id, $data);
		if($data["id"] != $id) return FALSE;
	}

	//Get datafields from db if not given
	if(!is_array($df)) {
		$df = ko_get_datafields($id);
		if(!is_array($df)) $df = array();
	}

	$store = array("date" => date("Y-m-d H:i:s"),
								 'user_id' => $uid,
								 "leute_id" => $id,
								 "changes" => serialize($data),
								 "df" => serialize($df),
								 );
	db_insert_data("ko_leute_changes", $store);
}//ko_save_leute_changes()





/**
 * Get an old version for a given person
 * @param date version, Set date, for which to return the address data (YYYY-MM-DD). The first version after this date will be returned
 * @param int id, ID of the person for which to return the version
 * @return array, Array of address data for the given timestamp
 */
function ko_leute_get_version($version, $id) {
	if(!$id || !$version) throw new InvalidArgumentException();

	$old = db_select_data("ko_leute_changes", "WHERE `leute_id` = '$id' AND `date` > '$version 23:59:59'", "*", "ORDER BY `date` ASC", "LIMIT 0, 1", TRUE);

	$row = unserialize($old["changes"]);
	return $row;
}//ko_leute_get_version()



/**
 * Get the value for a given group datafield
 * @param int gid, Group id
 * @param int fid, Datafield id
 * @param int pid, Person id
 * @param date version, Optional date for which to get the group datafield value
 * @param array all_datafields, Array with all group datafields given by reference
 * @param array all_groups, Array with all groups given by reference
 */
function ko_get_datafield_data($gid, $fid, $pid, $version="", &$all_datafields, &$all_groups) {
	//Return old version
	if($version != "") {
		$value = db_select_data("ko_groups_datafields_data AS dfd LEFT JOIN ko_groups_datafields AS df ON dfd.datafield_id = df.id", "WHERE dfd.datafield_id = '$fid' AND dfd.person_id = '$pid' AND dfd.group_id = '$gid'", "dfd.value AS value, df.type as typ, dfd.id as dfd_id", "", "", TRUE);

		//Get old version
		$old = db_select_data("ko_leute_changes", "WHERE `leute_id` = '$pid' AND `date` > '$version 23:59:59'", "*", "ORDER BY `date` ASC", "LIMIT 0, 1", TRUE);
		//If an old version has been found display the old value
		//Otherwise display the current value because there is no older version available
		if(isset($old["df"])) {
			$df_old = unserialize($old["df"]);

			//Set back to old value if set
			if(isset($df_old[$value["dfd_id"]])) {
				$value["value"] = $df_old[$value["dfd_id"]]["value"];
			} else {
				$value["value"] = "";
			}
		}
	}

	//Just get current version
	else {
		$value = db_select_data("ko_groups_datafields_data LEFT JOIN ko_groups_datafields ON ko_groups_datafields_data.datafield_id = ko_groups_datafields.id", "WHERE ko_groups_datafields_data.datafield_id = '$fid' AND ko_groups_datafields_data.person_id = '$pid' AND ko_groups_datafields_data.group_id = '$gid'", "ko_groups_datafields.id AS id, ko_groups_datafields_data.value AS value, ko_groups_datafields.type as typ, ko_groups_datafields.description as description", "", "", TRUE);
	}

	return $value;
}//ko_get_datafield_data()



/**
 * Get all group datafields for the given person
 * @param int pid, Person's id to get all datafields for
 * @return array df, Array with all datafields for this person
 */
function ko_get_datafields($pid) {
	$df = db_select_data("ko_groups_datafields_data", "WHERE `person_id` = '$pid' AND `deleted` = '0'", "*", "ORDER BY `group_id` ASC");
	return (!$df ? array() : $df);
}//ko_get_datafields()



function ko_get_fast_filter() {
	global $FAST_FILTER_IDS;

	$fast_filter = explode(',', ko_get_userpref($_SESSION['ses_userid'], 'leute_fast_filter'));
	foreach($fast_filter as $k => $v) {
		if(!$v) unset($fast_filter[$k]);
	}

	if(sizeof($fast_filter) == 0) $fast_filter = $FAST_FILTER_IDS;

	return $fast_filter;
}//ko_get_leute_fast_filter()










/************************************************************************************************************************
 *                                                                                                                      *
 * MODUL-FUNKTIONEN   K L E I N G R U P P E N                                                                           *
 *                                                                                                                      *
 ************************************************************************************************************************/

function ko_get_kleingruppen(&$kg, $z_limit = '', $id = '', $_z_where='') {
	global $SMALLGROUPS_ROLES, $SMALLGROUPS_ROLES_FOR_NUM;

	$kg = array();

	//Limit anwenden, falls gesetzt
	if($z_limit == '0' || $z_limit == 'LIMIT 0' || trim($z_limit) == '') {
		$z_limit = '';
	}

	//Nur einzelne Kleingruppe holen, falls id gesetzt
	if($id) {
		$z_where = ' WHERE `id` IN (';
		foreach(explode(',', $id) as $i) {
			$z_where .= "'".$i."',";
		}
		$z_where = substr($z_where, 0, -1).') ';
	} else if($_z_where != '') {
		$z_where = $_z_where;
	} else $z_where = '';


	if(is_string($_SESSION['sort_kg']) && !in_array($_SESSION['sort_kg'], array('anz_leute', 'kg_leiter')) && substr($_SESSION['sort_kg'], 0, 6) != 'MODULE') {
		$sort_col = $_SESSION['sort_kg'];
		$order = 'ORDER BY `'.$_SESSION['sort_kg'].'` '.$_SESSION['sort_kg_order'];
	} else {
		$sort_col = substr($_SESSION['sort_kg'], 0, 6) == 'MODULE' ? substr($_SESSION['sort_kg'], 6) : 'name';
		$order = 'ORDER BY name ASC';
	}


	//Prepare kg members and leaders
	$num_people = array();
	$kg_data = array();
	$kg_members = db_select_data('ko_leute', "WHERE `smallgroups` != '' AND `deleted` = '0'", 'id,smallgroups');
	foreach($kg_members as $member) {
		$his_kgs = array();
		foreach(explode(',', $member['smallgroups']) as $kgid) {
			if(!$kgid) continue;
			list($_kg, $role) = explode(':', $kgid);
			$kg_data[$role][$_kg][] = $member['id'];
			if(in_array($role, $SMALLGROUPS_ROLES_FOR_NUM)) $his_kgs[] = $_kg;
		}
		//Build sums for number of members for each small group
		$his_kgs = array_unique($his_kgs);
		foreach($his_kgs as $kgid) {
			$num_people[$kgid] += 1;
		}
	}

	//Get all small groups
	$rows = db_select_data('ko_kleingruppen', $z_where, '*', $order, $z_limit);
	$sort = array();
	foreach($rows as $row) {
		//Add a column for each role
		foreach($SMALLGROUPS_ROLES as $role) {
			$row['role_'.$role] = is_array($kg_data[$role][$row['id']]) ? implode(',', $kg_data[$role][$row['id']]) : '';
		}
		$row['anz_leute'] = $num_people[$row['id']];

		$kg[$row['id']] = $row;
		$sort[$row['id']] = $row[$sort_col];
	}

	//Manually sort for MODULE column
	if(substr($_SESSION['sort_kg'], 0, 6) == 'MODULE') {
		if($_SESSION['sort_kg_order'] == 'ASC') asort($sort);
		if($_SESSION['sort_kg_order'] == 'ASC') arsort($sort);
		$new = array();
		foreach($sort as $k => $v) {
			$new[$k] = $kg[$k];
		}
		$kg = $new;
	}
}//ko_get_kleingruppen()




function ko_get_smallgroup_by_id($id) {
	if(isset($GLOBALS['kOOL']['smallgroups'][$id])) return $GLOBALS['kOOL']['smallgroups'][$id];

	$id = format_userinput($id, 'uint');
	$sg = db_select_data('ko_kleingruppen', "WHERE `id` = '$id'", '*', '', '', TRUE);
	$GLOBALS['kOOL']['smallgroups'][$id] = $sg;;
	return $sg;
}//ko_get_smallgroup_by_id()




function ko_kgliste($data) {
	$r = '';

	//One char is the smallgroup role set in a filter
	if(strlen($data) == 1) {
		return getLL('kg_roles_'.$data);
	}

	if(!isset($GLOBALS["kOOL"]["ko_kleingruppen"])) {
    $GLOBALS["kOOL"]["ko_kleingruppen"] = db_select_data("ko_kleingruppen", "", "*", "ORDER BY name ASC");
  }

  foreach(explode(",", $data) as $id) {
		list($kgid, $role) = explode(':', $id);
    $kgs[] = $GLOBALS["kOOL"]["ko_kleingruppen"][$kgid]["name"].($role != '' ? ': '.getLL('kg_roles_'.$role) : '');
  }
  $r = implode("; ", $kgs);
  return $r;
}//ko_kgliste()




/**
 * Get a list of small groups the given login is assigned to
 *
 * @param $uid Login id (defaults to _SESSION['ses_userid'])
 */
function kg_get_users_kgid($uid='') {
	$r = array();
	$uid = $uid ? $uid : $_SESSION['ses_userid'];

	$p = ko_get_logged_in_person($uid);
	foreach(explode(',', $p['smallgroups']) as $kgid) {
		if(!$kgid) continue;
		$r[] = format_userinput(substr($kgid, 0, 4), 'uint');
	}
	return $r;
}//kg_get_users_kgid()












/************************************************************************************************************************
 *                                                                                                                      *
 * MODUL-FUNKTIONEN   R E S E R V A T I O N                                                                             *
 *                                                                                                                      *
 ************************************************************************************************************************/

/**
	* Liefert einzelne Reservation
	*/
function ko_get_res_by_id($id, &$r) {
	$r = db_select_data('ko_reservation', "WHERE `id` = '$id'");
}//ko_get_res_by_id()


/**
	* Liefert die Farbe eines Reservations-Items
	*/
function ko_get_resitem_farbe($id) {
	$row = db_select_data('ko_resitem', "WHERE `id` = '$id'", 'farbe', '', '', TRUE);
	return $row['farbe'];
}


/**
	* Liefert alle Reservationen (normale oder zu moderierende) zu einem definierten Datum
	*/
function ko_get_res_by_date($t="", $m, $j, &$r, $show_all = TRUE, $mode = "res", $z_where="") {
	global $access, $ko_menu_akt;

	//Reservationen oder Mod-Res
	if($mode=="res") $db_table = "ko_reservation";
	else if($mode=="mod") $db_table = "ko_reservation_mod";
	else return;

	$datum = $j."-".str_to_2($m)."-".($t?str_to_2($t):"01");

	$where = "WHERE (`startdatum`<='$datum' AND `enddatum`>='$datum')";

	if($mode == "res") {
		if($ko_menu_akt == "reservation" && sizeof($_SESSION["show_items"]) == 0) return FALSE;
		if(!$show_all) {
			$where .= " AND (`item_id` IN ('".implode("', '", $_SESSION["show_items"])."'))";
		}//if(!show_all)
	}
	else if($mode == "mod") {
		if($access['reservation']['ALL'] > 4) {
			$where .= '';
		} else if($access['reservation']['MAX'] > 4) {
			$items = array();
			foreach($access['reservation'] as $k => $v) {
				if(!intval($k) || $v < 5) continue;
				$items[] = $k;
			}
			$where .= ' AND `item_id` IN (\''.implode("','", $items)."') ";
		} else {
			$where .= ' AND 1=2 ';
		}
	}//if..else(mode==res)
	$r = db_select_data($db_table, $where.' '.$z_where, '*', 'ORDER BY `startdatum` ASC, `startzeit` ASC');
}//ko_get_res_by_date()


/**
 * Liefert alle normalen oder moderierten Reservationen
 */
function ko_get_reservationen(&$r, $z_where, $z_limit='', $type='res', $z_sort='') {
	global $db_connection;

	$r = array();

	//Sortierung
	if($z_sort) $sort = $z_sort;
	else if($_SESSION["sort_item"] && $_SESSION["sort_item_order"]) $sort = "ORDER BY ".($_SESSION["sort_item"] == "item_id" ? "item_name" : $_SESSION["sort_item"])." ".$_SESSION["sort_item_order"];
	else $sort = 'ORDER BY startdatum,startzeit,item_name ASC';

	//Reservationen oder Mod-Res
	if($type=="res") $db_table = "ko_reservation";
	else if($type=="mod") $db_table = "ko_reservation_mod";
	else return;

	//WHERE anwenden, oder falls leer, eine FALSE-Bedingung einfügen
	if($z_where != "") $z_where = "WHERE 1=1 " . $z_where;
	else $z_where = "WHERE 1=2";  //Nichts anzeigen

	$query = "SELECT $db_table.*,ko_resitem.name AS item_name,ko_resitem.farbe AS item_farbe,ko_resitem.gruppen_id AS gruppen_id FROM $db_table LEFT JOIN ko_resitem ON $db_table.item_id = ko_resitem.id $z_where $sort $z_limit";
	$result = mysqli_query($db_connection, $query);
	while($row = mysqli_fetch_assoc($result)) {
		$r[$row["id"]] = $row;
	}//while(row)
}//ko_get_reservationen()


/**
	* Liefert alle Res-Items einer Res-Gruppe
	*/
function ko_get_resitems_by_group($g, &$r) {
	$r = array();
	ko_get_resitems($r, "", "WHERE ko_resitem.gruppen_id = '$g'");
}//ko_get_resitems_by_group()


/**
	* Liefert ein einzelnes Res-Item
	*/
function ko_get_resitem_by_id($id, &$r) {
	$r = array();
	ko_get_resitems($r, "", "WHERE ko_resitem.id = '$id'");
}//ko_get_resitem_by_id()



/**
	* Liefert den Namen eines Res-Items
	*/
function ko_get_resitem_name($id) {
	$row = db_select_data('ko_resitem', "WHERE `id` = '$id'", 'name', '', '', TRUE);
	return $row['name'];
}//ko_get_resitem_name()


/**
	* Liefert alle Resitems in sortierter Reihenfolge
	*/
function ko_get_resitems(&$r, $z_limit="", $z_where="") {
	global $db_connection;
	$order = ($_SESSION["sort_group"]) ? (" ORDER BY ".($_SESSION["sort_group"] == "gruppen_id" ? "gruppen_name" : $_SESSION["sort_group"])." ".$_SESSION["sort_group_order"]) : " ORDER BY name ASC ";
	$query = "SELECT ko_resitem.*,ko_resgruppen.name AS gruppen_name FROM ko_resitem LEFT JOIN ko_resgruppen ON ko_resitem.gruppen_id = ko_resgruppen.id $z_where $order $z_limit";
	$result = mysqli_query($db_connection, $query);
	while($row = mysqli_fetch_assoc($result)) {
		$r[$row["id"]] = $row;
	}
}//ko_get_resitems()


/**
	* Liefert eine Liste aller oder einer einzelnen Res-Gruppen
	*/
function ko_get_resgroups(&$r, $id="") {
	$where = $id != '' ? "WHERE `id` = '$id'" : 'WHERE 1=1';
	$r = db_select_data('ko_resgruppen', $where, '*', 'ORDER BY name ASC');
}//ko_get_resgroups()


/**
	* Liefert alle zu moderierenden Reservationen der angegebenen Resitems
	*/
function ko_get_res_mod(&$r, $items, $user_id="") {
	$where = "";
	//Apply filter for items
	if(is_array($items)) {
		$where .= " AND (`item_id` IN ('".implode("','", $items)."')) ";
	}
	//Apply filter for user_id if given
	if($user_id > 0 && $user_id != ko_get_guest_id()) {
		$where .= " AND (`user_id` = '$user_id') ";
	}

	$r = db_select_data("ko_reservation_mod", "WHERE 1=1 $where", "*");
}//ko_get_res_mod()


/**
	* Liefert eine einzelne zu moderierende Reservation
	*/
function ko_get_res_mod_by_id(&$r, $id) {
	$r = db_select_data('ko_reservation_mod', "WHERE `id` = '$id'");
}//ko_get_res_mod_by_id()


/**
	* Find moderators for a given res item
	* Uses ko_get_moderators_by_resgroup()
	*/
function ko_get_moderators_by_resitem($item_id) {
	//Find resgroup id
	$item = db_select_data("ko_resitem", "WHERE `id` = '$item_id'", "gruppen_id", "", "", TRUE);
	$gid = $item["gruppen_id"];

	return ko_get_moderators_by_resgroup($gid);
}//ko_get_moderators_by_resitem()



/**
	* Find moderators for a given res group
	*/
function ko_get_moderators_by_resgroup($gid) {
	global $LEUTE_EMAIL_FIELDS;

	//email fields
	$email_fields = $where_email = '';
	foreach($LEUTE_EMAIL_FIELDS as $field) {
		$email_fields .= 'l.'.$field.' AS '.$field.', ';
		$where_email .= " l.$field != '' OR ";
	}
	$email_fields = substr($email_fields, 0, -2);

	//Get moderators for this resgroup
	$logins = db_select_data("ko_admin AS a LEFT JOIN ko_leute as l ON a.leute_id = l.id",
												 "WHERE ($where_email a.email != '') AND (a.disabled = '0' OR a.disabled = '')",
												 "a.id AS id, $email_fields, a.email AS admin_email, l.id AS leute_id, l.vorname AS vorname, l.nachname AS nachname, a.login as login");
	foreach($logins as $login) {
		$all = ko_get_access_all('res_admin', $login['id'], $max);
		if($max < 4) continue;
		$user_access = ko_get_access('reservation', $login['id'], TRUE, TRUE, 'login', FALSE);
		if($user_access['reservation']['grp'.$gid] < 5) continue;
		$mods[$login['id']] = $login;
	}
	$add_mods = array();
	foreach($mods as $i => $mod) {
		//Use admin_email as set for the login in first priority
		if($mod['admin_email']) {
			$mods[$i]['email'] = $mod['admin_email'];
		} else {
			//Get all email addresses for this person
			ko_get_leute_email($mod['leute_id'], $email);
			$mods[$i]['email'] = $email[0];
			//Create additional moderators for every email address to be used (if several are set in ko_leute_preferred_fields)
			if(sizeof($email) > 1) {
				for($j=1; $j<sizeof($email); $j++) {
					$add_mods[$j] = $mod;
					$add_mods[$j]['email'] = $email[$j];
				}
			}
		}
	}
	if(sizeof($add_mods) > 0) $mods = array_merge($mods, $add_mods);

	return $mods;
}//ko_get_moderators_by_resgroup()




/**
  * Get values to be used in smarty html_options to display res items in a select
	* Adds optgroups for res groups
	*/
function kota_ko_reservation_item_id_dynselect(&$values, &$descs, $rights=0, $_where="") {
	global $access;

	ko_get_access('reservation');

	$values = $descs = array();
	$groups = db_select_data("ko_resgruppen", "WHERE 1=1", "*", "ORDER BY `name` ASC");
	foreach($groups as $gid => $group) {
		if($access['reservation']['grp'.$gid] < $rights) continue;
		//Add group name (as optgroup)
		$descs["i".$gid] = $group["name"];
		//Get items for this group
		$where = "WHERE `gruppen_id` = '$gid' ";
		$where .= $_where;
		$items = db_select_data("ko_resitem", $where, "*", "ORDER BY `name` ASC");
		foreach($items as $iid => $item) {
			if($access['reservation'][$iid] < $rights) continue;
			$values["i".$gid][$iid] = $iid;
			$descs[$iid] = $item["name"];
		}
	}//foreach(groups)
}//kota_ko_reservation_item_id_dynselect()















/************************************************************************************************************************
 *                                                                                                                      *
 * MODUL-FUNKTIONEN   A D M I N                                                                                         *
 *                                                                                                                      *
 ************************************************************************************************************************/

/**
	* Liefert alle Logins
	*/
function ko_get_logins(&$l, $z_where = "", $z_limit = "", $sort_ = "") {
	global $ko_menu_akt;

	if($sort_ != "") {
		$sort = $sort_;
	} else if($ko_menu_akt == 'admin' && $_SESSION['sort_logins'] && $_SESSION['sort_logins_order']) {
		$sort = 'ORDER BY '.$_SESSION['sort_logins'].' '.$_SESSION['sort_logins_order'];
		if($_SESSION['sort_logins'] != 'login') $sort .= ', login ASC';
	} else {
		$sort = "ORDER BY login ASC";
	}


	//Treat special order columns
	if($ko_menu_akt == 'admin' && substr($_SESSION['sort_logins'], 0, 6) == 'MODULE') {
		switch(substr($_SESSION['sort_logins'], 6)) {
			//Order by name of assigned person
			case 'leute_id':
				$l = db_select_data('ko_admin AS a LEFT JOIN ko_leute AS l ON a.leute_id = l.id', 'WHERE 1 '.$z_where, "a.id AS id, a.*, CONCAT_WS(' ', l.vorname, l.nachname) AS _name", 'ORDER BY _name '.$_SESSION['sort_logins_order'].', login ASC', $z_limit);
			break;

			//Order by status (enabled/disabled) and by login name
			case 'disabled':
				$l = db_select_data('ko_admin', 'WHERE 1 '.$z_where, '*, LENGTH(disabled) AS _len', 'ORDER BY _len '.$_SESSION['sort_logins_order'].', login ASC', $z_limit);
			break;
		}
	}
	//No special ordering, so just get logins through SQL
	else {
		$l = db_select_data('ko_admin', 'WHERE 1=1 '.$z_where, '*', $sort, $z_limit);
	}
}//ko_get_logins()



/**
	* Liefert ein einzelnes Login
	*/
function ko_get_login($id, &$l) {
	$l = db_select_data('ko_admin', "WHERE `id` = '$id'", '*', '', '', TRUE);
}//ko_get_login()


/**
  * Liefert Etiketten-Einstellungen
	*/
function ko_get_etiketten_vorlagen(&$v) {
	global $db_connection;

	$v = array();
	$query = "SELECT * FROM `ko_etiketten` WHERE `key` = 'name' ORDER BY `value`";
	$result = mysqli_query($db_connection, $query);
	while($row = mysqli_fetch_assoc($result)) {
		$v[] = $row;
	}
}//ko_get_etiketten_vorlagen()


/**
  * Liefert einzelne Etiketten-Vorlagen-Werte
	*/
function ko_get_etiketten_vorlage($id, &$v) {
	global $db_connection;

	$v = array();
	$query = "SELECT * FROM `ko_etiketten` WHERE `vorlage` = '$id'";
	$result = mysqli_query($db_connection, $query);
	while($row = mysqli_fetch_assoc($result)) {
		$v[$row["key"]] = $row["value"];
	}
}//ko_get_etiketten_vorlagen()


/**
  * Speichert eine komplette Etiketten-Vorlage
	*/
function ko_save_etiketten_vorlage($id, $values, $mode="new") {
	foreach($values as $key => $value) {
		if($mode == "new") {
			db_insert_data('ko_etiketten', array('vorlage' => $id, 'key' => $key, 'value' => $value));
		} else if($mode == "edit") {
			//Test if this key is already available and only update then, otherwise insert
			if(db_get_count('ko_etiketten', 'vorlage', "AND `vorlage` = '$id' AND `key` = '$key'") > 0) {
				db_update_data('ko_etiketten', "WHERE `vorlage` = '$id' AND `key` = '$key'", array('value' => $value));
			} else {
				db_insert_data('ko_etiketten', array('vorlage' => $id, 'key' => $key, 'value' => $value));
			}
		}
	}
}//ko_save_etiketten_vorlage()














/************************************************************************************************************************
 *                                                                                                                      *
 * R O T A                                                                                                              *
 *                                                                                                                      *
 ************************************************************************************************************************/

/**
 * @param $_teams An array of team IDs that should be returned. If empty the teams currently set in the SESSION will be used
 * @param $event_id An ID of a single event to be returned (may also be an array of event ids
 */
function ko_rota_get_events($_teams='', $event_id='', $include_weekteams=FALSE) {
	global $db_connection, $access, $DATETIME, $ko_menu_akt;

	$e = array();

	//Get all rota teams
	if(is_array($_teams)) {
		$teams = $_teams;
	} else {
		if($ko_menu_akt == 'rota') $teams = $_SESSION['rota_teams'];
		else $teams = array_keys(db_select_data('ko_rota_teams'));
	}
	foreach($teams as $k => $v) {
		if(!$v) unset($teams[$k]);
	}
	if(sizeof($teams) == '0') return array();
	if($_SESSION['sort_rota_teams']) {
		$order = 'ORDER BY '.$_SESSION['sort_rota_teams'].' '.$_SESSION['sort_rota_teams_order'];
	} else {
		$order = 'ORDER BY '.(ko_get_setting('rota_manual_ordering') ? 'sort' : 'name').' ASC';
	}
	$_rota_teams = db_select_data('ko_rota_teams', "WHERE `id` IN (".implode(',', $teams).")", '*', $order);

	//Only show those of type event
	$rota_teams = array();
	if($include_weekteams) {
		$rota_teams = $_rota_teams;
	} else {
		foreach($_rota_teams as $t) {
			if($t['rotatype'] == 'event') $rota_teams[$t['id']] = $t;
		}
	}

	//Check for access level 1 for all these teams (access check for level 2 must be done in other functions, if need be)
	if(!isset($access['rota'])) ko_get_access('rota');
	if($access['rota']['ALL'] < 1) {
		foreach($rota_teams as $ti => $t) {
			if($access['rota'][$ti] < 1) unset($rota_teams[$ti]);
		}
	}


	//Multiple event ids given as array
	if(is_array($event_id)) {
		$where = " WHERE e.id IN ('".implode("','", $event_id)."') ";
	}
	//Only get one single event (e.g. for AJAX)
	else if($event_id > 0) {
		$where = " WHERE e.id = '$event_id' ";
	}
	//Or get all events from a given set of event groups
	else {
		$egs = $_SESSION['rota_egs'];

		if(sizeof($egs) == 0 || sizeof($rota_teams) == 0) return array();


		//Build SQL to only get events from selected event groups
		$where  = 'WHERE e.rota = 1 ';
		$where .= ' AND e.eventgruppen_id IN ('.implode(',', $egs).') ';

		// check, if the login has the 'force_global_filter' flag set to 1
		$forceGlobalTimeFilter = ko_get_force_global_time_filter('daten', $_SESSION['ses_userid']);

		//Apply global event filters if needed
		if(!is_array($access['daten'])) ko_get_access('daten');
		if($forceGlobalTimeFilter || $access['daten']['MAX'] < 2) {
			$perm_filter_start = ko_get_setting('daten_perm_filter_start');
			$perm_filter_ende  = ko_get_setting('daten_perm_filter_ende');
			if($perm_filter_start || $perm_filter_ende) {
				if($perm_filter_start != '') $where .= " AND enddatum >= '".$perm_filter_start."' ";
				if($perm_filter_ende != '') $where .= " AND startdatum <= '".$perm_filter_ende."' ";
			}
		}

		list($start, $stop) = rota_timespan_startstop($_SESSION['rota_timestart'], $_SESSION['rota_timespan']);
		$where .= " AND ( (e.startdatum >= '$start' AND e.startdatum < '$stop') OR (e.enddatum >= '$start' AND e.enddatum < '$stop') ) ";
	}

	//Add date filter so only events in the future show (according to userpref)
	if(ko_get_userpref($_SESSION['ses_userid'], 'rota_date_future') == 1) {
		$where .= " AND (e.enddatum >= '".date('Y-m-d')."') ";
	}


	$query = "SELECT e.*,tg.name AS eventgruppen_name, tg.farbe AS eventgruppen_farbe FROM `ko_event` AS e LEFT JOIN ko_eventgruppen AS tg ON e.eventgruppen_id = tg.id ".$where." ORDER BY startdatum ASC, startzeit ASC";
	$result = mysqli_query($db_connection, $query);
	while($row = mysqli_fetch_assoc($result)) {
		//Set individual event color
		ko_set_event_color($row);

		$all_teams = ko_rota_get_teams_for_eg($row['eventgruppen_id']);

		//Add IDs of all teams assigned to this event
		$teams = array();
		foreach($rota_teams as $t) {
			if(in_array($row['eventgruppen_id'], explode(',', $t['eg_id']))) $teams[] = $t['id'];
		}
		if(sizeof($teams) == 0) continue;
		$row['teams'] = $teams;

		//Assign all schedulling information for this event
		$schedulling = db_select_data('ko_rota_schedulling', "WHERE `event_id` = '".$row['id']."'", '*', '', '', FALSE, TRUE);
		$schedule = array();
		foreach($schedulling as $s) {
			if(in_array($s['team_id'], array_keys($all_teams))) $schedule[$s['team_id']] = $s['schedule'];
		}
		$row['schedule'] = $schedule;
		$row['rotastatus'] = $schedulling[0]['status'] ? $schedulling[0]['status'] : 1;  //Status of this week (1 for open, 2 for closed)

		//Get status of schedulling for this event (done/total)
		$done = 0;
		foreach($all_teams as $t => $v) {
			if(isset($schedule[$t]) && $schedule[$t] != '') $done++;
		}
		$row['_stats'] = array('total' => sizeof($all_teams), 'done' => $done);

		//Add nicely formated date and time
		$row['_time'] = $row['startzeit'] == '00:00:00' && $row['endzeit'] == '00:00:00' ? getLL('time_all_day') : substr($row['startzeit'], 0, -3);
		$row['_date'] = strftime($DATETIME['DdmY'], strtotime($row['startdatum']));
		if($row['enddatum'] != $row['startdatum'] && $row['enddatum'] != '0000-00-00') {
			$row['_date'] .= ' - '.strftime($DATETIME['DdmY'], strtotime($row['enddatum']));
		}

		$e[] = $row;
	}

	//Only return one if event_id was given
	if(!is_array($event_id) && $event_id > 0) $e = array_shift($e);

	return $e;
}//ko_rota_get_events()





/**
 * Get all rota teams working in the given event group
 *
 * @param eg ID of a single event group
 */
function ko_rota_get_teams_for_eg($eg) {
	global $access;

	if(isset($GLOBALS['kOOL']['rota_teams_for_eg'][$eg])) return $GLOBALS['kOOL']['rota_teams_for_eg'][$eg];

	if($_SESSION['sort_rota_teams']) {
		$order = 'ORDER BY '.$_SESSION['sort_rota_teams'].' '.$_SESSION['sort_rota_teams_order'];
	} else {
		$order = 'ORDER BY '.(ko_get_setting('rota_manual_ordering') ? 'sort' : 'name').' ASC';
	}
	$teams = db_select_data('ko_rota_teams', "WHERE `eg_id` REGEXP '(^|,)$eg(,|$)' AND `rotatype` = 'event'", '*', $order);

	//Check for access
	if($access['rota']['ALL'] < 1) {
		foreach($teams as $ti => $t) {
			if($access['rota'][$ti] < 1) unset($teams[$ti]);
		}
	}

	$GLOBALS['kOOL']['rota_teams_for_eg'][$eg] = $teams;

	return $teams;
}//ko_rota_get_teams_for_eg()




/**
 * Get all rota teams that are schedulled weekly (Dienstwochen)
 */
function ko_rota_get_teams_week() {
	global $access;

	if(isset($GLOBALS['kOOL']['rota_teams_week'])) return $GLOBALS['kOOL']['rota_teams_week'];

	if($_SESSION['sort_rota_teams']) {
		$order = 'ORDER BY '.$_SESSION['sort_rota_teams'].' '.$_SESSION['sort_rota_teams_order'];
	} else {
		$order = 'ORDER BY '.(ko_get_setting('rota_manual_ordering') ? 'sort' : 'name').' ASC';
	}
	$teams = db_select_data('ko_rota_teams', "WHERE `rotatype` = 'week'", '*', $order);
	//Check for access
	if($access['rota']['ALL'] < 1) {
		foreach($teams as $ti => $t) {
			if($access['rota'][$ti] < 1) unset($teams[$ti]);
		}
	}

	$GLOBALS['kOOL']['rota_teams_week'] = $teams;

	return $teams;
}//ko_rota_get_teams_week()





function ko_rota_get_weeks($rota_teams, $week_id='') {
	global $access;

	if(sizeof($_SESSION['rota_teams']) == 0) return array();

	if($_SESSION['sort_rota_teams']) {
		$order = 'ORDER BY '.$_SESSION['sort_rota_teams'].' '.$_SESSION['sort_rota_teams_order'];
	} else {
		$order = 'ORDER BY '.(ko_get_setting('rota_manual_ordering') ? 'sort' : 'name').' ASC';
	}
	if(!is_array($rota_teams)) $rota_teams = db_select_data('ko_rota_teams', "WHERE `id` IN (".implode(',', $_SESSION['rota_teams']).")", '*', $order);

	if($week_id) {
		list($start, $stop) = ko_rota_week_get_startstop($week_id);
	} else {
		list($start, $stop) = rota_timespan_startstop($_SESSION['rota_timestart'], $_SESSION['rota_timespan']);
		$start = strtotime(date_find_last_monday($start));
		$stop = strtotime($stop);
	}


	//Get all weekly teams and check for access
	$teams = array();
	foreach($rota_teams as $t) {
		if($t['rotatype'] == 'week' && ($access['rota']['ALL'] > 0 || $access['rota'][$t['id']] > 0)) $teams[] = $t['id'];
	}

	$weeks = array();
	$ts = $start;
	while($ts < $stop) {
		$weeks[date('Y-W', $ts)] = array('id' => date('Y-W', $ts),
																		 'num' => date('W', $ts),
																		 'year' => date('Y', $ts),
																		 //Correct displayed date by rota_weekstart
																		 '_date' => ko_rota_timespan_title(date('Y-m-d', ($ts+(ko_get_setting('rota_weekstart')*3600*24))), '1w'),
																		 'teams' => $teams);

		//Get all schedulling information
		$schedulling = db_select_data('ko_rota_schedulling', "WHERE `event_id` = '".date('Y-W', $ts)."'", '*', '', '', FALSE, TRUE);
		$schedule = array();
		foreach($schedulling as $s) {
			$schedule[$s['team_id']] = $s['schedule'];
		}
		$weeks[date('Y-W', $ts)]['schedule'] = $schedule;
		$weeks[date('Y-W', $ts)]['rotastatus'] = $schedulling[0]['status'] ? $schedulling[0]['status'] : 1;  //Status of this week (1 for open, 2 for closed)

		//Get status of schedulling for this event (done/total)
		$done = 0;
		$all_teams = ko_rota_get_teams_week();
		foreach($all_teams as $t => $v) {
			if(isset($schedule[$t]) && $schedule[$t] != '') $done++;
		}
		$weeks[date('Y-W', $ts)]['_stats'] = array('total' => sizeof($all_teams), 'done' => $done);


		$ts += 3600*24*7;
	}

	//Only return one if week_id was given
	if($week_id > 0) $weeks = array_shift($weeks);

	return $weeks;
}//ko_rota_get_weeks()





/**
 * Get start and stop date for a given start date and timespan
 */
function rota_timespan_startstop($timestart, $timespan) {
	//Add time frame from setting / param
	switch($timespan) {
		case '1d':
			$start = $timestart;
			$stop  = add2date($timestart, 'day', 1, TRUE);
		break;

		case '1w':
			$start = date_find_last_monday($timestart);
			$stop  = add2date($start, 'week', 1, TRUE);
		break;
		case '2w':
			$start = date_find_last_monday($timestart);
			$stop  = add2date($start, 'week', 2, TRUE);
		break;

		case '1m':
			$start = substr($timestart, 0, -2).'01';
			$stop  = add2date($start, 'month', 1, TRUE);
		break;
		case '2m':
			$start = substr($timestart, 0, -2).'01';
			$stop  = add2date($start, 'month', 2, TRUE);
		break;
		case '3m':
			$start = substr($timestart, 0, -2).'01';
			$stop  = add2date($start, 'month', 3, TRUE);
		break;
		case '6m':
			$start = substr($timestart, 0, -2).'01';
			$stop  = add2date($start, 'month', 6, TRUE);
		break;
		case '12m':
			$start = substr($timestart, 0, -2).'01';
			$stop  = add2date($start, 'month', 12, TRUE);
		break;
	}

	return array($start, $stop);
}//rota_timespan_startstop()






/**
 * @param event If it's an array, then it must be one event retrieved by ko_rota_get_events(). It may also be an ID of an event
 * @param mode May be event or week. If event holds an id, the mode tells whether this id is of an event or of a week (YYYY-MM)
 * @param _teams An array of teams used for ko_rota_get_events(). These teams can be schedulled.
 */
function ko_rota_get_schedulling_code($event, $mode='event', $_teams='') {
	global $access;

	if(!is_array($event)) {
		if($mode == 'event') $event = ko_rota_get_events($_teams, $event);
		else if($mode == 'week') $event = ko_rota_get_weeks('', $event);
	}

	if($_SESSION['sort_rota_teams']) {
		$order = 'ORDER BY '.$_SESSION['sort_rota_teams'].' '.$_SESSION['sort_rota_teams_order'];
	} else {
		$order = 'ORDER BY '.(ko_get_setting('rota_manual_ordering') ? 'sort' : 'name').' ASC';
	}
	$all_teams = db_select_data('ko_rota_teams', 'WHERE 1=1', '*', $order);

	//Get all people scheduled in this event for double checks
	if($mode == 'event') {
		$temp = ko_rota_get_recipients_by_event_by_teams($event['id']);
		foreach($temp as $tid => $t) {
			$currently_scheduled[$tid] = array_keys($t);
		}
	}
	else if($mode == 'week') {
		//Get all events of this week
		list($start, $stop) = ko_rota_week_get_startstop($event['id']);
		$start = date('Y-m-d', $start+(ko_get_setting('rota_weekstart')*3600*24));
		$stop = date('Y-m-d', $stop+(ko_get_setting('rota_weekstart')*3600*24));

		//Only check events where the given week-teams are active $event['teams']
		$egs = array();
		foreach($event['teams'] as $tid) {
			$egs = array_merge($egs, explode(',', $all_teams[$tid]['eg_id']));
		}
		$egs = array_unique($egs);
		foreach($egs as $k => $v) if(!$v) unset($egs[$k]);

		$where = "WHERE `rota` = '1' AND (`startdatum` <= '$stop' AND `enddatum` >= '$start') ";
		if(sizeof($egs) > 0) $where .= " AND `eventgruppen_id` IN (".implode(',', $egs).") ";
		else $where .= " AND 1=2 ";

		$events = db_select_data('ko_event', $where);
		foreach($events as $e) {
			$temp = ko_rota_get_recipients_by_event_by_teams($e['id']);
			foreach($temp as $tid => $t) {
				$currently_scheduled[$tid] = array_merge((array)$currently_scheduled[$tid], array_keys($t));
			}
		}
	}

	// needed to determine whether participation will be displayed
	$role = ko_get_setting('rota_teamrole');
	$helperRoleString = (trim($role) == '' ? '' : ':r' . $role);

	$c = '<table class="rota-schedule">';
	foreach($event['teams'] as $tid) {
		if($access['rota']['ALL'] < 1 && $access['rota'][$tid] < 1) continue;

		$c .= '<tr>';
		$c .= '<th style="width:15%;">'.$all_teams[$tid]['name'].'</th>';

		if($event['rotastatus'] == 1 && ($access['rota']['ALL'] > 2 || $access['rota'][$tid] > 2)) {  //open and enough access

			$consensusAllowed = $all_teams[$tid]['allow_consensus'] == 1;

			//Prepare select with groups and people to choose from
			$members = ko_rota_get_team_members($all_teams[$tid], ko_get_userpref($_SESSION['ses_userid'], 'rota_schedule_subgroup_members'));
			$o = '<option value=""></option>';
			$groupsFromConsensus = array();
			if(sizeof($members['groups']) > 0) {
				foreach($members['groups'] as $group) {
					//Check for double
					$double = $title = $warntext = '';
					$group_members = db_select_data('ko_leute', "WHERE `groups` REGEXP 'g".$group['id']."'");
					foreach($group_members as $person) {
						foreach($all_teams as $_tid => $_team) {
							if($_tid == $tid) continue;
							if(in_array($person['id'], $currently_scheduled[$_tid])) {
								$double = ' (!)';
								$title = 'title="'.sprintf(getLL('rota_schedule_warning_double_group'), ($person['vorname'].' '.$person['nachname']), $_team['name']).'"';
								$warntext = trim(sprintf(getLL('rota_schedule_warning_double_group'), ($person['vorname'].' '.$person['nachname']), $_team['name']));
							}
						}
					}
					$o .= '<option value="g'.$group['id'].'" '.$title.'>['.$group['name'].']'.$double.'</option>';
					if ($consensusAllowed) {
						$groupAnswers = ko_consensus_get_answers('group', $event['id'], $tid, $group['id']);
						$groupsFromConsensus[$group['id']] = array('id' => $group['id'], 'name' => '[' . $group['name'] . ']', 'double' => $double, 'warntext' => $warntext, 'answer' => $groupAnswers);
					}
				}
			}
			$personsFromConsensus = array();
			if(sizeof($members['people']) > 0) {
				foreach($members['people'] as $person) {
					$double = $title = $warntext = '';
					foreach($all_teams as $_tid => $_team) {
						if($_tid == $tid) continue;
						if(in_array($person['id'], $currently_scheduled[$_tid])) {
							$double = ' (!)';
							$title = 'title="'.sprintf(getLL('rota_schedule_warning_double'), $_team['name']).'"';
							$warntext = trim(sprintf(getLL('rota_schedule_warning_double'), $_team['name']));
						}
					}
					$name = $person['vorname'].' '.$person['nachname'];
					$o .= '<option value="'.$person['id'].'" '.$title.'>'.$name.$double.'</option>';
					if ($consensusAllowed) {
						$answer = ko_consensus_get_answers('person', $event['id'], $tid, $person['id']);
						$personsFromConsensus[$person['id']] = array('id' => $person['id'], 'name' => $name, 'double' => $double, 'warntext' => $warntext, 'answer' => $answer);
					}
				}
			}

			//Schedulled values
			$sel_o = array();
			$schedulled = ko_rota_schedulled_text($event['schedule'][$tid], 'full');
			$size = 0;
			foreach($schedulled as $k => $v) {
				if(!$k) continue;
				$sel_o[] = '<div class="rota-entry" id="rota_entry_'.$event['id'].'_'.$tid.'_'.$k.'">'.$v.'</div>';
				//unset($personsFromConsensus[$k]);
				$size++;
			}
			$size = max(2, $size);

			if ($consensusAllowed) {
				// Color table for consensus
				$bgColor = array(0 => 'no_answer', 1 => 'no', 2 => 'maybe', 3 => 'yes');
				//Consensus Values of groups
				$consensus_o_g = array();
				$groupToolTipHtml = '<table><tr><td>' . getLL('yes') . '</td><td>%s</td></tr><tr><td>(' . getLL('yes') . ')</td><td>%s</td></tr><tr><td>' . getLL('no') . '</td><td>%s</td></tr></table><p>%s</p>';
				foreach($groupsFromConsensus as $k => $v) {
					if($k === '') continue;
					$toolTipCode = "onmouseover=\"tooltip.show('" . sprintf($groupToolTipHtml, $v['answer'][3], $v['answer'][2], $v['answer'][1], $v['warntext']) . "');\" onmouseout=\"tooltip.hide();\"";
					$consensus_o_g[] = '<div class="rota-consensus-entry rota-consensus-entry-group" id="rota_consensus_entry_'.$event['id'].'_'.$tid.'_g'.$v['id'].'" style="background-image:url(\'/rota/inc/consensus_chart?x=' . implode('x', $v['answer']) . '\');" ' . $toolTipCode . '>'.$v['name'] . $v['double'].'</div>';
				}
				//Consensus Values of persons
				$consensus_o_p = array();
				$personToolTipHtml = '<p>' . getLL('rota_consensus_tooltip_header_person') . '</p><table><tr><td></td><td>' . getLL('time_month') . '</td><td>' . getLL('time_quarter') . '</td><td>' . getLL('time_year') . '</td></tr><tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr><tr><td>' . getLL('rota_consensus_all_teams') . '</td><td>%s</td><td>%s</td><td>%s</td></tr></table><p>%s</p>';
				foreach($personsFromConsensus as $k => $v) {
					if($k === '') continue;
					$participation = ko_rota_get_participation($v['id'], $tid);
					$toolTipCode = "onmouseover=\"tooltip.show('" . sprintf($personToolTipHtml, $all_teams[$tid]['name'], $participation[$tid]['month'], $participation[$tid]['quarter'], $participation[$tid]['year'], $participation['all']['month'], $participation['all']['quarter'], $participation['all']['year'], $v['warntext']) . "');\" onmouseout=\"tooltip.hide();\"";
					$consensus_o_p[] = '<div class="rota-consensus-entry ' . $bgColor[$v['answer']] . '" id="rota_consensus_entry_'.$event['id'].'_'.$tid.'_'.$v['id'].'" '.$toolTipCode.'>'.$v['name'] . $v['double'].'</div>';
				}
			}

			$c .= '<td style="width:40%;"><table border="0" class="rota-entries"><tr>';
			$c .= '<td><select class="rota-select" id="'.$event['id'].'_'.$tid.'" size="0" style="width: 200px;">'.$o.'</select>';
			$c .= '<br /><input class="rota-text" type="text" style="width: 175px; margin-top: 3px;" id="rota_text_'.$event['id'].'_'.$tid.'" /></td>';

			// determine the number of elements which should be stacked in each cell (height in [elements])
			$elemPerCell = max(2, ceil(sizeof($sel_o) / 3));

			$counter = 0;
			foreach($sel_o as $entry) {
				if($counter == 0) $c .= '<td valign="top">';
				$c .= $entry;
				$counter++;
				if($counter == $elemPerCell) {
					$counter = 0;
					$c .= '</td>';
				}
			}
			if($counter > 0 && $counter < $elemPerCell) $c .= '</td>';
			$c .= '</tr></table>';

			$c .= '</td>';

			if ($consensusAllowed) {
				// determine the number of elements which should be stacked in each cell (height in [elements])
				$elemPerCell = max(2, ceil((sizeof($consensus_o_g) + sizeof($consensus_o_p)) / 4));

				$c .= '<td style="width:45%;"><table class="rota-consensus-enries"><tr>';
				// Entries from Consensus groups
				$counter = 0;
				foreach($consensus_o_g as $entry) {
					if($counter == 0) $c .= '<td valign="top">';
					$c .= $entry;
					$counter++;
					if($counter == $elemPerCell) {
						$counter = 0;
						$c .= '</td>';
					}
				}
				if($counter > 0 && $counter < $elemPerCell) $c .= '</td>';
				// Entries from Consensus persons
				$counter = 0;
				foreach($consensus_o_p as $entry) {
					if($counter == 0) $c .= '<td valign="top">';
					$c .= $entry;
					$counter++;
					if($counter == $elemPerCell) {
						$counter = 0;
						$c .= '</td>';
					}
				}
				if($counter > 0 && $counter < $elemPerCell) $c .= '</td>';
				$c .= '</tr></table>';

				$c .= '</td>';
			}
		}

		else {  // 2 = closed
			$c .= '<td style="width:80%;">'.implode(ko_get_userpref($_SESSION['ses_userid'], 'rota_delimiter'), ko_rota_schedulled_text($event['schedule'][$tid], 'full')).'</td>';
		}

		$c .= '</tr>';
	}
	$c .= '</table>';

	return $c;
}//ko_rota_get_schedulling_code()





/**
 * Get the text to be displayed for a certain scheduling: Name of persons, Name of groups or free text
 * @param schedule string Comma separated list as found in db table ko_rota_schedulling
 * @return array Array of all entires which can be imploded for text rendering
 */
function ko_rota_schedulled_text($schedule, $forceFormat='') {
	$r = array();

	foreach(explode(',', $schedule) as $s) {
		if(!$s) continue;

		if(is_numeric($s)) {  //Person id
			$format = ko_get_userpref($_SESSION['ses_userid'], 'rota_pdf_names');
			if($forceFormat) $format = $forceFormat;

			ko_get_person_by_id($s, $p);
			switch($format) {
				case 1:
					$r[$s] = $p['vorname'].' '.substr($p['nachname'],0,1).'.';
				break;
				case 2:
					$r[$s] = $p['vorname'].' '.substr($p['nachname'],0,2).'.';
				break;
				case 3:
					$r[$s] = substr($p['vorname'],0,1).'. '.$p['nachname'];
				break;
				case 4:
					$r[$s] = $p['vorname'].' '.$p['nachname'];
				break;
				case 5:
					$r[$s] = $p['vorname'];
				break;
				default:
					$r[$s] = $p['vorname'].' '.$p['nachname'];
			}
		} else if(preg_match('/^g[0-9]{6}$/', $s)) {  //Group id
			$id = str_replace('g', '', $s);
			$group = db_select_data('ko_groups', "WHERE `id` = '$id'", '*', '', '', TRUE);
			$r[$s] = getLL('rota_prefix_group').$group['name'];
		} else {  //Text
			$r[$s] = $s;
		}
	}

	return $r;
}//ko_rota_schedulled_text()



/**
 * gets all helpers of a certain team at a certain event. Helpers are persons, no groups are returned
 *
 * @param $eventId
 * @param $teamId
 * @param $keepGroup: Set to true to have the group's name returned instead of the people
 * @return array
 */
function ko_rota_get_helpers_by_event_team($eventId, $teamId, $keepGroup=FALSE) {
	$schedule = db_select_data('ko_rota_schedulling', "where `team_id` = '" . $teamId . "' and `event_id` = '" . $eventId . "'", '*', '', '', TRUE, TRUE);
	if ($schedule == null) return array();
	$role = ko_get_setting('rota_teamrole');
	$roleString = (trim($role) == '' ? '' : ':r' . $role);
	$helpers = array();
	foreach (explode(',', $schedule['schedule']) as $helper) {
		if (trim($helper) == '') continue;
		if (is_numeric($helper)) { // person id
			ko_get_person_by_id($helper, $person);
			if ($person == null) continue;
			$helpers[] = $person;
		}
		else if (preg_match('/g[0-9]{6}/', $helper)) {
			if($keepGroup) {
				$group = db_select_data('ko_groups', "WHERE `id` = '".substr($helper, 1)."'", '*', '', '', TRUE);
				$helpers[] = $group['name'];
			} else {
				$pattern = $helper . '(:g[0-9]{6})*' . $roleString;
				$res = db_select_data('ko_leute', "where `groups` regexp '" . $pattern . "'");
				foreach ($res as $helper) {
					$helpers[] = $helper;
				}
			}
		}
	}
	return $helpers;
} // ko_rota_get_helpers_by_event_team()





/**
 * Get all people scheduled in a given event. Also find group's members if a whole group is scheduled
 * @param event_ids array/int An array of event ids of a single event ID
 * @param team_ids array An array of teams to include. Empty to include all teams
 * @param access_level int Access level necessary to include this team
 */
function ko_rota_get_recipients_by_event($event_ids, $team_ids='', $access_level=2) {
	global $access;

	if(!is_array($event_ids)) $event_ids = array($event_ids);
	if(sizeof($event_ids) == 0) return array();

	$z_where = '';
	if(is_array($team_ids) || $team_ids != '') {
		if(!is_array($team_ids)) $team_ids = array($team_ids);
		$z_where .= ' AND `team_id` IN ('.implode(',', $team_ids).') ';
	}

	//Add restriction according to access level
	if($access['rota']['ALL'] < $access_level) {
		$a_teams = array();
		$all_teams = db_select_data('ko_rota_teams');
		foreach($all_teams as $tid => $team) {
			if($access['rota'][$tid] >= $access_level) $a_teams[] = $tid;
		}
		if(sizeof($a_teams) > 0) {
			$z_where .= ' AND `team_id` IN ('.implode(',', $a_teams).') ';
		} else {
			$z_where .= ' AND 1=2 ';
		}
	}

	//Add weeks
	$events = db_select_data('ko_event', "WHERE `id` IN (".implode(',', $event_ids).')');
	foreach($events as $event) {
		$event_ids[] = date('Y-W', (strtotime($event['startdatum'])-(ko_get_setting('rota_weekstart')*3600*24)));
	}

	$people = array();
	$schedulling = db_select_data('ko_rota_schedulling', "WHERE `event_id` IN ('".implode("','", $event_ids)."')".$z_where, '*', '', '', FALSE, TRUE);
	foreach($schedulling as $schedule) {
		foreach(explode(',', $schedule['schedule']) as $s) {
			$s = trim($s);
			if(is_numeric($s)) {  //Person id
				ko_get_person_by_id($s, $p);
				$people[$p['id']] = $p;
			} else if(preg_match('/^g[0-9]{6}$/', $s)) {  //Group id
				$rows = db_select_data('ko_leute', "WHERE `groups` REGEXP '$s' AND `deleted` = '0' AND `hidden` = '0'");
				foreach($rows as $row) {
					$people[$row['id']] = $row;
				}
			} else {  //Text
				//Don't include in recipients list
			}
		}
	}

	return $people;
}//ko_rota_get_recipients_by_event()




function ko_rota_get_recipients_by_event_by_teams($event_ids, $team_ids='', $access_level=2) {
	global $access;

	if(!is_array($event_ids)) {
		if($event_ids == '') return array();
		$event_ids = array($event_ids);
	}
	if(sizeof($event_ids) == 0) return array();

	$z_where = '';
	if(is_array($team_ids) || $team_ids != '') {
		if(!is_array($team_ids)) $team_ids = array($team_ids);
		$z_where .= ' AND `team_id` IN ('.implode(',', $team_ids).') ';
	}

	//Add restriction according to access level
	if($access['rota']['ALL'] < $access_level) {
		$a_teams = array();
		$all_teams = db_select_data('ko_rota_teams');
		foreach($all_teams as $tid => $team) {
			if($access['rota'][$tid] >= $access_level) $a_teams[] = $tid;
		}
		if(sizeof($a_teams) > 0) {
			$z_where .= ' AND `team_id` IN ('.implode(',', $a_teams).') ';
		} else {
			$z_where .= ' AND 1=2 ';
		}
	}

	//Add weeks
	$events = db_select_data('ko_event', "WHERE `id` IN (".implode(',', $event_ids).')');
	foreach($events as $event) {
		$event_ids[] = date('Y-W', (strtotime($event['startdatum'])-(ko_get_setting('rota_weekstart')*3600*24)));
	}

	$people = array();
	$schedulling = db_select_data('ko_rota_schedulling', "WHERE `event_id` IN ('".implode("','", $event_ids)."')".$z_where, '*', '', '', FALSE, TRUE);
	foreach($schedulling as $schedule) {
		foreach(explode(',', $schedule['schedule']) as $s) {
			$s = trim($s);
			if(is_numeric($s)) {  //Person id
				ko_get_person_by_id($s, $p);
				$people[$schedule['team_id']][$p['id']] = $p;
			} else if(preg_match('/^g[0-9]{6}$/', $s)) {  //Group id
				$rows = db_select_data('ko_leute', "WHERE `groups` REGEXP '$s' AND `deleted` = '0' AND `hidden` = '0'");
				foreach($rows as $row) {
					$people[$schedule['team_id']][$row['id']] = $row;
				}
			} else {  //Text
				//Don't include in recipients list
			}
		}
	}

	return $people;
}//ko_rota_get_recipients_by_event_by_teams()





/**
 * Returns team members/leaders for a rota team
 *
 * @param int/array $team teamID or team Array to get members for
 * @param boolean $resolve_groups Set to true to get group members as single people, otherwise just get whole groups
 * @param int $roleid Give a role ID to only get members/leaders according to this roleID (e.g. 0000XY)
 * @return Array with two keys: groups and people
 */
function ko_rota_get_team_members($team, $resolve_groups=FALSE, $roleid='') {
	//Return from cache
	$tid = is_array($team) ? $team['id'] : $team;
	if(!$resolve_groups && isset($GLOBALS['kOOL']['rota_team_members'][$tid])) return $GLOBALS['kOOL']['rota_team_members'][$tid];


	if(!is_array($team)) $team = db_select_data('ko_rota_teams', "WHERE `id` = '$team'", '*', '', '', TRUE);
	if(!$team['group_id']) return array('people' => array(), 'groups' => array());

	$r = array();

	//First get all subgroups of the given groups
	$not_leaves = db_select_distinct('ko_groups', 'pid');
	$gids = explode(',', $team['group_id']);
	foreach($gids as $k => $v) {
		$gids[$k] = format_userinput($v, 'uint');
	}
	ko_get_groups($top, 'AND `id` IN ('.implode(',', $gids).')', '', 'ORDER BY name ASC');

	$level = 0;
	$g = array();
	foreach($top as $t) {
		rec_groups($t, $g, '', $not_leaves, FALSE);
	}//foreach(top)

	$r['groups'] = $g;


	//Then get all people assigned to the selected groups/roles
	if(ko_get_setting('rota_showroles') == 1) {  //Group select already shows roles so don't add the general role here
		$role = '';
	} else {  //Only groups get selected so add general role if set
		$teamrole = ko_get_setting('rota_teamrole');
		$role = $teamrole ? ':r'.$teamrole : '';
	}
	//'all' makes sure we return all team members (leaders and members)
	if($roleid == 'all') $role = '';
	//roleid given as argument overwrites settings
	else if($roleid != '') $role = ':r'.$roleid;

	//Add sql for each given group/role
	foreach(explode(',', $team['group_id']) as $gid) {
		if($role && $rolepos = strpos($gid, ':r')) {  //Remove role in group_id if set
			$gid = substr($gid, 0, $rolepos);
		}
		$where .= " `groups` REGEXP '(^|,|:)".$gid.$role."($|,|:r)' OR ";
	}


	//Add members from groups above
	if($resolve_groups) {
		foreach($r['groups'] as $group) {
			$where .= " `groups` REGEXP 'g".$group['id'].($role != '' ? '(g0-9:)*'.$role : '')."' OR ";
		}
	}


	$where = substr($where, 0, -3);


	//Sorting
	$orderby = ko_get_userpref($_SESSION['ses_userid'], 'rota_orderby');
	if(!$orderby) $orderby = 'vorname';
	if($orderby == 'nachname') $orderby = 'nachname,vorname';
	else if($orderby == 'vorname') $orderby = 'vorname,nachname';

	$rows = db_select_data('ko_leute', "WHERE ($where) AND `deleted` = '0'", '*', 'ORDER BY '.$orderby.' ASC');
	$p = array();
	foreach($rows as $row) {
		$p[$row['id']] = $row;
	}

	$r['people'] = $p;

	//Store in cache
	if(!$resolve_groups) $GLOBALS['kOOL']['rota_team_members'][$team['id']] = $r;

	return $r;
}//ko_rota_get_team_members()



/**
 * kept for backwards compatibility (needed to display old changes in person's history)
 */
function ko_dienstliste($dienste) {
  if(!$dienste) return FALSE;

  $r = '';
  $dienste_a = explode(',', $dienste);
	$all_teams = db_select_data('ko_rota_teams');
  foreach($dienste_a as $d) {
		$ad = $all_teams[$d];
    if($ad[$d]['name']) {
      $r .= $ad[$d]['name'].', ';
    }
  }
  $r = substr($r, 0, -2);

  return $r;
}//ko_dienstliste()


/**
 * calculates array_intersect(array1, array2) in time O(n+m).
 * ARRAYS MUST BE SORTED! KEYS MUST BE ASCENDING FROM, 0,1,2,3,...,n
 *
 * @param array $sortedArray1
 * @param array $sortedArray2
 * @return array sorted
 */
function ko_fast_array_intersect(array $sortedArray1, array $sortedArray2) {
	$result = array();
	$done = false; $i = 0; $j = 0;
	$si = sizeof($sortedArray1);
	$sj = sizeof($sortedArray2);
	$xi = null;
	$xj = null;
	while (!$done) {
		$xi = $sortedArray1[$i];
		$xj = $sortedArray2[$j];
		if ($xi == $xj) {
			$result[] = $xj;
			$i++;
			$j++;
		}
		else if ($xi > $xj) {
			$j++;
		}
		else {
			$i++;
		}
		if ($i >= $si || $j >= $sj) {
			$done = true;
		}
	}
	return $result;
}

/**
 * calculates array_unique of a sorted array
 *
 * @param array $sortedArray1
 * @return array
 */
function ko_fast_array_unique(array $sortedArray1) {
	$lastElem = null;
	$result = array();
	foreach ($sortedArray1 as $entry) {
		if ($entry == $lastElem) continue;
		$result[] = $entry;
		$lastElem = $entry;
	}
	return $result;
}


/**
 * returns the status of an event
 *
 * @param $teamId
 * @param $eventId
 * @return int 1 = open, 2 = closed
 */
function ko_rota_get_status($teamId, $eventId) {
	$event = db_select_data("ko_rota_schedulling", "where `team_id` = " . $teamId . " and `event_id` = " . $eventId, 'status', '', '', TRUE, TRUE);
	$eventStatus = $event['status'];
	$eventStatus = $eventStatus == null ? 1 : $eventStatus;
	return $eventStatus;
} // ko_rota_get_status()




/**
 * @param $sorting
 * @param $zWhere
 */
function ko_rota_get_all_teams($orderBy = 'userdef', $zWhere = '') {
	$zWhere = 'where 1=1 ' . $zWhere;
	if ($orderBy == 'userdef') {
		$orderBy = 'order by `sort` asc';
	}
	$teams = db_select_data('ko_rota_teams', $zWhere, '*', $orderBy);
	return $teams;
}


/**
 * returns all events where $id was scheduled for during the supplied time frame
 *
 * @param $id the id of the person or group
 * @param $start the minimal ending time of the event, Y-m-d H:i:s
 * @param $stop the maximal starting time of the event
 * @param string $mode either 'person' or later 'group' // TODO : implement group functionality
 */
function ko_rota_get_scheduled_events($id, $start, $stop, $mode = 'person') {
	global $BASE_PATH;

	if (array_key_exists('ko_scheduled_events', $GLOBALS['kOOL'])) {
		if (array_key_exists($id . $start . $stop . $mode, $GLOBALS['kOOL']['ko_scheduled_events'])) {
			return $GLOBALS['kOOL']['ko_scheduled_events'][$id . $start . $stop . $mode];
		}
	}
	else {
		$GLOBALS['kOOL']['ko_scheduled_events'] = array();
	}

	$role = ko_get_setting('rota_teamrole');
	$roleString = (trim($role) == '' ? '' : ':r' . $role);

	// get all non-leaf groups associated with a team
	if (array_key_exists('ko_non_leaf_team_groups', $GLOBALS['kOOL'])) {
		$nonLeafTeamGroups = $GLOBALS['kOOL']['ko_non_leaf_team_groups'];
	}
	else {
		$teams = db_select_data('ko_rota_teams', 'where 1=1', 'group_id');
		$nonLeafTeamGroups = array();
		$teamGroups = array();
		foreach ($teams as $team) {
			foreach (explode(',', $team['group_id']) as $teamGroup) {
				$teamGroup = trim($teamGroup);
				if ($teamGroup == '') continue;
				if (preg_match('/^g[0-9]{6}$/', $teamGroup)) {
					$teamGroups[] = substr($teamGroup, 1);
				}
				else if (preg_match('/^g[0-9]{6}:r[0-9]{6}$/', $teamGroup)) {
					$teamGroups[] = substr($teamGroup, 1, 6);
				}
			}
		}


		if (sizeof($teamGroups) != 0) {
			$res = db_query("SELECT DISTINCT `id` FROM `ko_groups` g1 WHERE `id` IN ('" . implode("','", $teamGroups) . "') AND NOT EXISTS (SELECT `id` FROM `ko_groups` g2 WHERE g2.`pid` = g1.`id`) ORDER BY g1.`id` ASC;");
			foreach ($res as $nonLeafTeamGroup) {
				$nonLeafTeamGroups[] = (int) $nonLeafTeamGroup["id"];
			}
		}
		$GLOBALS['kOOL']['ko_non_leaf_team_groups'] = $nonLeafTeamGroups;
	}

	if (substr($id, 0, 1) != 'g') {
		ko_get_person_by_id($id, $person);
		if (!$person) return;
		$groupsString = $person['groups'];
		if (trim($groupsString) == '') return;

		$unprocGroups = array();
		foreach (explode(',', $groupsString) as $group) {
			if (trim($group) == '') continue;
			if ($roleString != '') { // only consider group memberships with 'helper' role
				if (substr($group, -8, 8) != $roleString) continue;
				$group = substr($group, 0, strlen($group) - 8);
			}
			else {
				if (substr($group, -7, 1) == 'r') { // remove role so we won't search for it in the `schedule` column of ko_rota_schedulling
					$group = substr($group, 0, strlen($group) - 8);
				}
			}
			$explodedGroups = explode(':', $group);
			foreach ($explodedGroups as $singleGroup) {
				if (trim($singleGroup) == '') continue;
				$unprocGroups[] = (int) substr($singleGroup, 1);
			}
		}
		sort($unprocGroups);

		// get the intersection of all groups of the person and all non-leaf groups associated with a team
		$helperGroups = ko_fast_array_unique(ko_fast_array_intersect($nonLeafTeamGroups, $unprocGroups));

		$regexp = '(((,|^)' . $id . '(,|$))' . (sizeof($helperGroups) == 0 ? ')' : '|' . implode('|', $helperGroups) . ')');
		$zWhere = " and `ko_rota_schedulling`.`schedule` regexp '" . $regexp . "'";


		$timeFilterEvents1 = " AND TIMESTAMPDIFF(SECOND,CONCAT(CONCAT(`ko_event`.`startdatum`, ' '), `ko_event`.`startzeit`),'" . $stop . "') >= 0";
		$timeFilterEvents2 = " AND TIMESTAMPDIFF(SECOND,CONCAT(CONCAT(`ko_event`.`enddatum`, ' '), `ko_event`.`endzeit`),'" . $start . "') <= 0";
		$timeFilterEvents = $timeFilterEvents1 . $timeFilterEvents2;

		$zWhere .= $timeFilterEvents;

		$events = array();
		$res = db_query("SELECT `ko_event`.`id`,`ko_event`.`startdatum`,`ko_event`.`enddatum`,`ko_event`.`startzeit`,`ko_event`.`endzeit`,`ko_rota_schedulling`.`team_id` FROM `ko_rota_schedulling`, `ko_event` WHERE `ko_rota_schedulling`.`event_id` = `ko_event`.`id` " . $zWhere);
		foreach ($res as $k => $event) {
			if (array_key_exists($events, $event['id'])) {
				$events[$event['id']]['in_teams'][] = $event['team_id'];
			}
			else {
				$events[$event['id']] = $event;
				$events[$event['id']]['in_teams'] = array($event['team_id']);
			}
		}

		// add weekly events
		$startUnix = strtotime($start);
		$startWeekDay = date('w', $startUnix);
		$stopUnix = strtotime($stop);
		$stopWeekDay = date('w', $stopUnix);
		// correct year depending on in which year the thursday of the current week lies
		$startDBForm = date('Y-W', $startUnix + (4 - $startWeekDay) * 3600 * 24);
		$stopDBForm = date('Y-W', $stopUnix + (4 - $stopWeekDay) * 3600 * 24);

		$zWhere = " and `schedule` regexp '" . $regexp . "'";
		$zWhere .= " and `event_id` regexp '[0-9]{4}-[0-9]{2}' and `event_id` >= '" . $startDBForm . "' and `event_id` <= '" . $stopDBForm . "'";

		$weeklyEvents = array();
		$res = db_query("SELECT * FROM `ko_rota_schedulling` WHERE 1=1 " . $zWhere);
		foreach ($res as $k => $weeklyEvent) {
			if (array_key_exists($weeklyEvents, $weeklyEvent['event_id'])) {
				$weeklyEvents[$weeklyEvent['event_id']]['in_teams'][] = $weeklyEvent['team_id'];
			}
			else {
				$weeklyEvents[$weeklyEvent['event_id']] = $weeklyEvent;
				$weeklyEvents[$weeklyEvent['event_id']]['in_teams'] = array($weeklyEvent['team_id']);
			}
		}

		foreach ($weeklyEvents as $k => $weeklyEvent) {
			list($year,$week) = explode('-', $k);
			$eventStart= strtotime("{$year}-W{$week}-1");
			$eventStop = strtotime("{$year}-W{$week}-7");


			if ($eventStop >= strtotime($start) && $eventStart <= strtotime($stop)) {
				$events[$k] = array('id' => $k, 'startdatum' => date('Y-m-d', $eventStart), 'startzeit' => date('H:i:s', $eventStart), 'enddatum' => date('Y-m-d', $eventStop), 'endzeit' => date('H:i:s', $eventStop), 'in_teams' => $weeklyEvent['in_teams']);
			}
		}

	}
	else {
		// TODO : group functionality
		$events = array();
	}

	// cache result
	$GLOBALS['kOOL']['ko_scheduled_events'][$id . $start . $stop . $mode] = $events;

	return $events;
} // ko_rota_get_scheduled_events()


function ko_rota_get_participation ($id, $teamId) {

	$result = array();
	$result[$teamId] = array();
	$result['all'] = array();
	$result['all']['month'] = 0;
	$result['all']['quarter'] = 0;
	$result['all']['year'] = 0;
	$result[$teamId]['month'] = 0;
	$result[$teamId]['quarter'] = 0;
	$result[$teamId]['year'] = 0;

	$events = ko_rota_get_scheduled_events($id, date('Y-m-d H:i:s', strtotime('-1 year')), date('Y-m-d H:i:s'), strtotime('+1 day'));

	foreach ($events as $event) {
		$endTime = strtotime($event['enddatum'] . ' ' . $event['endzeit']);
		$now = time();
		$inArray = in_array($teamId, $event['in_teams']);

		$result['all']['year'] += 1;
		if ($inArray) {
			$result[$teamId]['year'] += 1;
		}
		if ($now - $endTime <= 3600 * 24 * 90) {
			$result['all']['quarter'] += 1;
			if ($inArray) {
				$result[$teamId]['quarter'] += 1;
			}
		}
		if ($now - $endTime <= 3600 * 24 * 30) {
			$result['all']['month'] += 1;
			if ($inArray) {
				$result[$teamId]['month'] += 1;
			}
		}


	}
	return $result;
} // ko_rota_get_participation()











/************************************************************************************************************************
 *                                                                                                                      *
 * MODUL-FUNKTIONEN   F I L E S H A R E                                                                                 *
 *                                                                                                                      *
 ************************************************************************************************************************/

/**
	* Liefert die vorhandenen Shares
	* Erlaubt die Angaben von zusätzlichen Filtern und Limiten
	*/
function ko_get_shares($filter, $orderBy="", $limit="") {
	if(!$filter) return FALSE;

	$r = db_select_data('ko_fileshare', 'WHERE 1=1 '.$filter, '*', 'ORDER BY '.($orderBy ? $orderBy : 'filename ASC'), $limit);
}//ko_get_shares()


/**
	* Speichert mit den gemachten Angaben ein neues Share
	*/
function ko_fileshare_save_share($id, $user_id, $filename, $type, $parent, $size) {
	if(!$id) return FALSE;

	db_insert_data('ko_fileshare', array('id' => $id, 'user_id' => $user_id, 'filename' => format_userinput($filename, 'text'), 'type' => $type, 'c_date' => date('Y-m-d H:i:s'), 'filesize' => $size, 'parent' => $parent));
}//ko_fileshare_save_share()



/**
  * Macht Sent-Eintrag nach dem Versenden eines Mails
	*/
function ko_fileshare_send_file($fileid, $rec, $recid) {
	if(!$fileid || !$recid) return FALSE;

	db_insert_data('ko_fileshare_sent', array('file_id' => $fileid, 'recipient' => $rec, 'recipient_id' => $recid));
}//ko_fileshare_send_file()



/**
  * Liefert alle Ordner für einen Benutzer
	*/
function ko_fileshare_get_folders($userid, $mode="view") {
	global $access;

	$r = array();
	if(!in_array($mode, array("view", "new", "edit", "mod"))) return FALSE;
	if(!$userid || !ko_module_installed("fileshare",$userid)) return FALSE;
	$levels = array('view' => 1, 'new' => 2, 'edit' => 3, 'mod' => 4);
	$level = $levels[$mode];

	if($access['fileshare']['MAX'] >= $level) {
		//Top-Folders
		$top = db_select_data('ko_fileshare_folders', "WHERE `user` = '$userid' AND `parent` = '0' AND `flag` != 'S'", '*', 'ORDER BY parent ASC, name ASC');

		//Build Array in alphabetic tree-order
		foreach($top as $t) {
			$r[$t["id"]] = $t;
			rec_folders($t, $userid, $r);
		}
	}

	//Add Freigabe-Ordner
	$shareroot = ko_fileshare_get_shareroot($userid);
	$r[$shareroot["id"]] = $shareroot;
	//Add Shared Folders
	$shareroot = ko_fileshare_get_shareroot($_SESSION["ses_userid"]);
	$rows = db_select_data('ko_fileshare_folders', "WHERE `user` != '$userid' AND `share_users` REGEXP '@$userid@' AND `share_rights` >= '$level'", '*', 'ORDER BY name ASC');
	foreach($rows as $row) {
		$row['parent'] = $shareroot['id'];
		$r[$row['id']] = $row;
	}

	return $r;
}//ko_fileshare_get_folders()

function rec_folders(&$t, $userid, &$r) {
	//Children
	$children = db_select_data('ko_fileshare_folders', "WHERE `user` = '$userid' AND `parent` = '".$t['id']."' AND `flag` != 'S'", '*', 'ORDER BY name ASC');

	foreach($children as $c) {
		$r[$c["id"]] = $c;
		rec_folders($c, $userid, $r);
		unset($children[$c["id"]]);
	}
}//rec_folders()



function ko_fileshare_get_folder(&$folder, $id) {
	$folder = db_select_data('ko_fileshare_folders', "WHERE `id` = '$id'", '*', '', '', TRUE);
}//ko_fileshare_get_folder()




/**
  * Stellt eine Grössenangabe schön in B, KB, MB oder GB dar
	*/
function ko_nice_size($size) {
	if($size > (1024*1024*1024)) $size = round($size/(1024*1024*1024), 2)."GB";
  else if($size > (1024*1024)) $size = round($size/(1024*1024), 2)."MB";
  else if($size > 1024) $size = round($size/1024)."KB";
  else if($size > 0) $size = $size."B";
	return $size;
}//ko_nice_size()



/**
  * Liefert Rootline eines Folders
	*/
function ko_fileshare_get_rootline($id, $userid) {
	$rootline = array();

	ko_fileshare_get_folder($af, $id);
	if($af["user"] != $userid) {  //Bei Shared-Folders S-Folder als Parent angeben
		$parent_ = ko_fileshare_get_shareroot($userid);
		$parent = $parent_["id"];
	} else {  //Sonst richtigen Parent wählen
		$parent = $af["parent"];
	}
	$rootline[$id] = $af["name"];
	while($parent != 0) {
		ko_fileshare_get_folder($af, $parent);
		$parent = $af["parent"];
		$rootline[$af["id"]] = $af["name"];
	}//while(parent != 0)

	return $rootline;
}//ko_fileshare_get_rootline()



/**
  * Liefert ID des Eingang-Folders für einen Benutzer
	*/
function ko_fileshare_get_inbox($userid) {
	return db_select_data('ko_fileshare_folders', "WHERE `user` = '$userid' AND `flag` = 'I'", '*', '', '', TRUE);
}//ko_fileshare_get_inbox()



/**
  * Liefert ID des Shared-Rootfolders für einen Benutzer
	*/
function ko_fileshare_get_shareroot($userid) {
	return db_select_data('ko_fileshare_folders', "WHERE `user` = '$userid' AND `flag` = 'S'", '*', '', '', TRUE);
}//ko_fileshare_get_shareroot()



/**
  * Check for Inbox- and Shared-Folders, and create them if not present
	*/
function ko_fileshare_check_inbox_shareroot($id) {
	if(!ko_module_installed('fileshare', $id)) return;

	$inbox = ko_fileshare_get_inbox($id);
	if(!$inbox["id"]) {
		db_insert_data('ko_fileshare_folders', array('user' => $id, 'name' => getLL('fileshare_inbox'), 'comment' => getll('fileshare_inbox_comment'), 'c_date' => date('Y-m-d H:i:s'), 'flag' => 'I'));
	}
	$shares = ko_fileshare_get_shareroot($id);
	if(!$shares["id"]) {
		db_insert_data('ko_fileshare_folders', array('user' => $id, 'name' => getLL('fileshare_share'), 'comment' => getll('fileshare_share_comment'), 'c_date' => date('Y-m-d H:i:s'), 'flag' => 'S'));
	}
}//ko_fileshare_check_inbox_shareroot()




/**
  * Liefert Select-Box für Pfad-Auswahl
	*/
function ko_fileshare_get_folder_select($userid, $mode="view", &$values, &$descs, $shareroot=TRUE) {
	$code = "";
	$values = $descs = array();

	$folders = ko_fileshare_get_folders($userid, $mode);
	foreach($folders as $f) {
		if(!$shareroot && $f["flag"] == "S") continue;
		$sel = ($f["id"] == $_SESSION["folderid"]) ? 'selected="selected"' : '';
		$code .= '<option value="'.$f["id"].'" '.$sel.'>';
		$depth = sizeof(ko_fileshare_get_rootline($f["id"], $userid))-1;
		for($i=0; $i<$depth; $i++) $code .= "&nbsp;&nbsp;";
		$code .= $f["name"];
		$code .= '</option>';

		$values[] = $f["id"];
		$desc = "";
		for($i=0; $i<$depth; $i++) $desc .= "&nbsp;&nbsp;";
		$descs[]  = $desc.$f["name"];
	}
	return $code;
}//ko_fileshare_get_folder_select()



/**
  * Überprüft einen Ordner auf gewisse Rechte für einen Benutzer
	*/
function ko_fileshare_check_permission($userid, $folderid, $action) {
	global $access;

	if($userid <= 0) return FALSE;

	ko_fileshare_get_folder($folder, $folderid);
	if($folderid > 0 && $folder["user"] <= 0) return FALSE;

	if($folder["user"] == $userid || $folderid == 0) {  //Owned Folder
		$own = TRUE;
	} else {  //Shared Folder
		$own = FALSE;
		if($folder["share_rights"] >= 1) $share_view = TRUE; else $share_view = FALSE;
		if($folder["share_rights"] >= 2) $share_new = TRUE; else $share_new = FALSE;
		if($folder["share_rights"] >= 3) $share_del = TRUE; else $share_del = FALSE;
	}

	switch($action) {
		case "view_file":
			if($own && $access['fileshare']['MAX'] > 0) return TRUE;
			else if(!$own && $share_view) return TRUE;
			else return FALSE;
		break;

		case "new_file":
			if($own && $access['fileshare']['MAX'] > 1) return TRUE;
			else if(!$own && $share_new) return TRUE;
			else return FALSE;
		break;

		case "del_file":
			if($own && $access['fileshare']['MAX'] > 2) return TRUE;
			else if(!$own && $share_del) return TRUE;
			else return FALSE;
		break;

		case "new_folder":
		case "del_folder":
		case "edit_folder":
			if($own && $access['fileshare']['MAX'] > 3) return TRUE;
			else return FALSE;
		break;

	}//switch(action)

}//ko_fileshare_check_permission()



/**
  * Speichert eine Datei als Share-Datei für den definierten User in seiner Inbox
	*/
function ko_fileshare_save_file_as_share($uid, $dateiname) {
	global $FILESHARE_FOLDER;

	if(!ENABLE_FILESHARE || !ko_module_installed("fileshare", $uid)) return FALSE;

	$save_filename = basename($dateiname);
	$save_id = md5($save_filename.microtime());
	$save_type = exec("file -ib ".escapeshellcmd($dateiname));
	$inbox = ko_fileshare_get_inbox($uid);
	clearstatcache();
	$file_size = filesize($dateiname);
	copy($dateiname, $FILESHARE_FOLDER.$save_id);
	chmod($FILESHARE_FOLDER.$save_id, 0644);
	ko_fileshare_save_share($save_id, $uid, $save_filename, $save_type, $inbox["id"], $file_size);
}//ko_fileshare_save_file_as_share()







/************************************************************************************************************************
 *                                                                                                                      *
 * MODUL-FUNKTIONEN  T A P E S                                                                                          *
 *                                                                                                                      *
 ************************************************************************************************************************/

function ko_get_tapes(&$tapes, $z_where = "", $z_limit = "") {
	global $db_connection;

	if($_SESSION["sort_tapes"] && $_SESSION["sort_tapes_order"]) $sort = " ORDER BY ".$_SESSION["sort_tapes"]." ".$_SESSION["sort_tapes_order"];
	else $sort = "ORDER BY date DESC";

	if($z_where) {
		$z_where = "WHERE (ko_tapes_groups.id = ko_tapes.group_id) ".$z_where;
	} else {
		$z_where = "WHERE ko_tapes_groups.id = ko_tapes.group_id";
	}

	$query = "SELECT ko_tapes.*, ko_tapes_groups.name as group_name FROM `ko_tapes`, `ko_tapes_groups` $z_where $sort $z_limit";

	$result = mysqli_query($db_connection, $query);
	while($row = mysqli_fetch_assoc($result)) {
		//Serie auslesen, falls eine definiert
		if($row["serie_id"] > 0) {
			ko_get_tapeseries($serie, "AND `id` = '".$row["serie_id"]."'");
			$row["serie_name"] = $serie[$row["serie_id"]]["name"];
		}
		$tapes[$row["id"]] = $row;
	}
	return TRUE;
}//ko_get_tapes()



function ko_get_tapeseries(&$series, $z_where = "", $z_limit = "") {
	$series = db_select_data('ko_tapes_series', 'WHERE 1=1 '.$z_where, '*', 'ORDER BY name ASC', $z_limit);
}//ko_get_tapeseries



function ko_get_tapegroups(&$groups, $z_where = "", $z_limit = "") {
	$groups = db_select_data('ko_tapes_groups', $z_where, '*', 'ORDER BY name ASC', $z_limit);
}//ko_get_tapegroups()



function ko_get_preachers(&$preachers) {
	$preachers = db_select_distinct('ko_tapes', 'preacher', 'ORDER BY preacher ASC');
}//ko_get_preachers()



/**
  * Falls ID gesetzt ist, kommt nur das Daten-Array zurück ansonsten kommen alle Layoute roh zurück
	*/
function ko_get_tape_printlayout($id = "") {
	global $db_connection;

	if($id != "") {
		$where = "WHERE `id` = '$id'";
	} else $where = "";

	$query = "SELECT * FROM `ko_tapes_printlayout` $where ORDER BY name ASC";
	$result = mysqli_query($db_connection, $query);
	while($row = mysqli_fetch_assoc($result)) {
		if($id != "") {
			$r = unserialize($row["data"]);
			$r["id"] = $row["id"];
			$r["name"] = $row["name"];
			$r["default"] = $row["default"];
		} else {
			$r[$row["id"]] = $row;
		}
	}
	return $r;
}//ko_get_tape_printlayout()







/************************************************************************************************************************
 *                                                                                                                      *
 * MODUL-FUNKTIONEN  G R O U P E S                                                                                      *
 *                                                                                                                      *
 ************************************************************************************************************************/

/**
  * Liefert alle Gruppen
	*/
function ko_get_groups(&$groups, $z_where="", $z_limit="", $sort_="") {
	if(!$sort_) {
		$sort = ($_SESSION["sort_groups"]) ? "ORDER BY ".$_SESSION["sort_groups"]." ".$_SESSION["sort_groups_order"] : "ORDER BY name ASC";
	} else {
		$sort = $sort_;
	}
	$groups = db_select_data('ko_groups', 'WHERE 1=1 '.$z_where, '*', $sort, $z_limit);
}//ko_get_groups()


/**
  * Liefert alle Rollen
	*/
function ko_get_grouproles(&$roles, $z_where="", $z_limit="") {
	$sort = ($_SESSION["sort_grouproles"]) ? "ORDER BY ".$_SESSION["sort_grouproles"]." ".$_SESSION["sort_grouproles_order"] : "ORDER BY name ASC";
	$roles = db_select_data('ko_grouproles', 'WHERE 1=1 '.$z_where, '*', $sort, $z_limit);
}//ko_get_grouproles()


/**
  * Liefert alle IDs und Bezeichnungen für alle Rollen in einer Gruppe
	*/
function ko_groups_get_group_id_names($gid, &$groups, &$roles, $do_roles=TRUE) {
	//Nicht aus Cache holen, sondern neu berechnen
	$values = $descs = $all_descs = array();

	//Gruppe
	$group = $groups[$gid];
	//Mutter-Gruppen
	$m = $group;
	$line = array("g".$m["id"]);
	while($m["pid"]) {
		$m = $groups[$m["pid"]];
		$line[] = "g".$m["id"];
	}
	$line = array_reverse($line);

	//Gruppe selber
	if(!$group['maxcount'] || $group['count'] < $group['maxcount'] || $group['count_role']) {
		$values[] = implode(":", $line);
		$descs[]  = $group['name'].($group['maxcount'] > 0 ? ' ('.$group['count'].'/'.$group['maxcount'].')' : '');
	}
	//Alle Rollen
	if($do_roles && $group["roles"] != "") {
		foreach(explode(",", $group["roles"]) as $role) {
			if(!$role) continue;
			//If maxcount is reached don't include this role in values/descs (left select of doubleselect)
			// But store in all_descs so it can be displayed in right select of assigned values
			if($group['maxcount'] > 0 && $group['count'] >= $group['maxcount'] && $group['count_role'] == $role) {
				$v = implode(':', $line).':r'.$role;
				$all_descs[$v] = $group['name'].': '.$roles[$role]['name'];
			} else {
				$values[] = implode(":", $line).":r".$role;
				$descs[] = $group["name"].": ".$roles[$role]["name"];
			}
		}
	}

	return array($values, $descs, $all_descs);
}//ko_groups_get_group_id_names()



/**
  * Liefert einzelne Bestandteile eines Gruppen-Rollen-Strings
	*/
function ko_groups_decode($all, $type, $limit=0) {
	global $all_groups;

	if(strlen($all) == 6) { //Einzelne Gruppen-ID übergeben
		$mother_line = array();
		$base_group = $all;
	} else { //Sonst handelt es sich um ein g:000001:r000002 usw.
		$parts = explode(":", $all);
		$base_found = FALSE;
		$mother_line = array();
		$mother_line_names = array();
		for($i=(sizeof($parts)-1); $i>=0; $i--) {
			if(substr($parts[$i], 0, 1) == "r") {
				$rolle = substr($parts[$i], 1);
			} else if(substr($parts[$i], 0, 1) == "g") {
				if(!$base_found) {
					$base_group = substr($parts[$i], 1);
					$base_found = TRUE;
				} else {
					$mother_line[] = substr($parts[$i], 1);
					//Save the groupnames of the motherline aswell for the full path plus role
					if($type == "group_desc_full") {
						if(isset($all_groups[substr($parts[$i], 1)])) {
							$mother_line_names[] = $all_groups[substr($parts[$i], 1)]["name"];
						} else {
							ko_get_groups($group, "AND `id` = '".substr($parts[$i], 1)."'");
							$mother_line_names[] = $group[substr($parts[$i], 1)]["name"];
						}
					}
				}
			}
		}
	}//if..else(strlen(all) == 6)

	switch($type) {
		case "group_id":
			return $base_group;
		break;

		case "group":
			if(isset($all_groups[$base_group])) {
				return $all_groups[$base_group];
			} else {
				ko_get_groups($group, "AND `id` = '$base_group'");
				return $group[$base_group];
			}
		break;

		case "group_desc":
		case "group_desc_full":
			reset($mother_line_names);
			if(isset($all_groups[$base_group])) {
				$group[$base_group] = $all_groups[$base_group];
			} else {
				ko_get_groups($group, "AND `id` = '$base_group'");
			}
			if($rolle) {
				ko_get_grouproles($role, "AND `id` = '$rolle'");
				if($type == "group_desc_full")
					return implode(":", array_reverse($mother_line_names)).(sizeof($mother_line_names)>0?":":"").$group[$base_group]["name"].":".$role[$rolle]["name"];
				else
					return $group[$base_group]["name"].": ".$role[$rolle]["name"];
			} else {
				if($type == "group_desc_full") {
        			$value = implode(":", array_reverse($mother_line_names)).(sizeof($mother_line_names)>0?":":"").$group[$base_group]["name"];
        			if($limit && strlen($value) > $limit) {
            			$limit = floor($limit/2)-2;
            			return substr($value, 0, $limit)."[..]".substr($value, -1*$limit);
          			} else {
            			return $value;
          			}
        		} else {
        			return $group[$base_group]["name"];
        		}
			}
		break;

		case "group_description":
			if(isset($all_groups[$base_group])) {
				return $all_groups[$base_group]["description"];
			} else {
				ko_get_groups($group, "AND `id` = '$base_group'");
				return $group[$base_group]["description"];
			}
		break;

		case "role_id":
			return $rolle;
		break;

		case "role_desc":
			if(!$rolle) return $group[$base_group]["name"];
			ko_get_grouproles($role, "AND `id` = '$rolle'");
			return $role[$rolle]["name"];
		break;

		case "mother_line":
			return array_reverse($mother_line);
		break;


		case 'full_gid':
			if(!is_array($all_groups)) ko_get_groups($all_groups);
      if(sizeof($mother_line) == 0) $mother_line = ko_groups_get_motherline($base_group, $all_groups);
      else $mother_line = array_reverse($mother_line);
      $mids = array();
      foreach($mother_line as $mg) {
        $mids[] = 'g'.$all_groups[$mg]['id'];
      }
      $full_id = (sizeof($mids) > 0 ? implode(':', $mids).':' : '').'g'.$base_group;
      if($rolle) $full_id .= ':r'.$rolle;
      return $full_id;
		break;
	}
}//ko_groups_decode()



function ko_groups_get_motherline($gid, &$groups) {
	if(!$gid) return;

	if(!is_array($groups)) ko_get_groups($groups);

	$mother_line = array();
	$group = $groups[$gid];
	while($group["pid"]) {
		$pid = $group["pid"];
		$mother_line[] = $pid;
		$group = $groups[$pid];
		$gid = $pid;
	}
	return array_reverse($mother_line);
}//ko_groups_get_motherline()



/**
  * Erstellt Save-String gemäss POST-Werten und Berechtigungen, damit nicht bearbeitbare nicht rausfliegen
	*/
function ko_groups_get_savestring(&$value, $data, &$log, $_bisher=NULL, $apply_start_stop=TRUE, $do_ezmlm=TRUE) {
	global $access;

	if(!ko_module_installed("groups")) return;
	ko_get_access('groups');

	//Behandlung der Gruppen
	//Einzeln hinzufügen oder löschen, damit Rechte eingehalten werden. (Bestehende, nicht anzeigbare würden sonst gelöscht, da nicht in Formular)
	if(isset($_bisher)) {
		$bisher = explode(",", $_bisher);
	} else {
		ko_get_person_by_id($data["id"], $person);
		$bisher = explode(",", $person["groups"]);
	}
	$submited = explode(",", $value);
	$log = " ";
	//Neu eingetragene:
	$linkedGroups = array();
	foreach($submited as $g) {
		if(!$g) continue;
		$group = ko_groups_decode($g, "group");
	  //Don't work on timed groups
		if($apply_start_stop && ($group["stop"] != "0000-00-00" && $group["stop"] <= strftime("%Y-%m-%d", time()))) continue;
		if($apply_start_stop && ($group["start"] != "0000-00-00" && $group["start"] > strftime("%Y-%m-%d", time()))) continue;

		//Check for maxcount
		if($group['maxcount'] > 0 && $group['count'] >= $group['maxcount'] && (!$group['count_role'] || $group['count_role'] == ko_groups_decode($g, 'role_id'))) continue;

		if(!in_array($g, $bisher) && ($access['groups']['ALL'] > 1 || $access['groups'][$group['id']] > 1)) {
			$bisher[] = $g;
			$linkedGroupString = implode(',', ko_groups_get_linked_groups($g));
			if ($linkedGroupString != '' && !in_array($linkedGroupString, $bisher)) $linkedGroups[] = $linkedGroupString;
			$log .= "+".ko_groups_decode($g, "group_desc").", ";
			//Check for new ezmlm subscription
			if($do_ezmlm && defined("EXPORT2EZMLM") && EXPORT2EZMLM && $group["ezmlm_list"] != "") {
				if(!is_array($person)) ko_get_person_by_id($data["id"], $person);
				ko_ezmlm_subscribe($group["ezmlm_list"], $group["ezmlm_moderator"], $person["email"]);
			}
		}
	}
	//Gelöschte:
	foreach($bisher as $b_i => $b) {
		$group = ko_groups_decode($b, "group");
		//Falls col==MODULEgrp ist (also die Funktion aus multiedit heraus aufgerufen wird), nur gewählte Gruppe bearbeiten,
		//sonst fallen alle anderen Gruppen-Einteilungen raus
		if(substr($data["col"], 0, 9) == "MODULEgrp" && $group["id"] != substr($data["col"], 9)) continue;
	  //Don't work on timed groups
		if($apply_start_stop && ($group["stop"] != "0000-00-00" && $group["stop"] <= strftime("%Y-%m-%d", time()))) continue;
		if($apply_start_stop && ($group["start"] != "0000-00-00" && $group["start"] > strftime("%Y-%m-%d", time()))) continue;

		if(($access['groups']['ALL'] > 1 || $access['groups'][$group['id']] > 1) && !in_array($b, $submited)) {
			unset($bisher[$b_i]);
			$log .= "-".ko_groups_decode($b, "group_desc").", ";
			//Check for ezmlm subscription to cancel
			if($do_ezmlm && defined("EXPORT2EZMLM") && EXPORT2EZMLM && $group["ezmlm_list"] != "") {
				if(!is_array($person)) ko_get_person_by_id($data["id"], $person);
				ko_ezmlm_unsubscribe($group["ezmlm_list"], $group["ezmlm_moderator"], $person["email"]);
			}
		}
	}

	$linkedGroups = ko_groups_remove_spare_norole($linkedGroups);

	//get rid of empty entries
	$r = array();
	foreach(array_unique(array_merge($bisher, $linkedGroups)) as $v) {
		if(!$v) continue;
		$r[] = $v;
	}


	$value = implode(",", $r);
}//ko_groups_get_savestring()


function ko_groups_get_linked_groups ($group, $result = array()) {
	$groupId = ko_groups_decode($group, 'group_id');
	$role = ko_groups_decode($group, 'role_id');

	$linkedGroupId = NULL;
	$res = db_select_data('ko_groups', 'where id = ' . $groupId, 'linked_group', '', '', TRUE, TRUE);
	if ($res['linked_group'] != '') {
		$linkedGroupId = $res['linked_group'];
	}
	if ($linkedGroupId === NULL) {
		return $result;
	}

	// prevent loops in recursion
	if (isset($result[$linkedGroupId])) {
		return $result;
	}

	$linkedGroup = db_select_data('ko_groups', 'where id = ' . $linkedGroupId, 'roles', '', '', TRUE, TRUE);
	if ($linkedGroup === NULL) {
		// TODO: maybe print info that linked group wasn't found
		return $result;
	}


	$linkedRolesString = $linkedGroup['roles'];
	$linkedRoles = explode(',', $linkedRolesString);
	$linkedRole = '';
	if (in_array($role, $linkedRoles)) {
		$linkedRole = $role;
	}

	$fullLinkedGroupId = ko_groups_decode($linkedGroupId, 'full_gid');
	$result[$linkedGroupId] = $fullLinkedGroupId . ($linkedRole == '' ? '' : ':r' . $linkedRole);

	$recursiveResult = ko_groups_get_linked_groups ($fullLinkedGroupId . ($role == '' ? '' : ':r' . $role), $result);

	return $recursiveResult;

}//ko_groups_get_linked_groups

/**
 * Removes group assignments without role if the person is also assigned with a role. Applies only to groups with
 * exactly 1 role
 *
 * @param $groups
 * @return array
 */
function ko_groups_remove_spare_norole($groups) {
	if ($groups == '' || sizeof($groups) == 0) return array();

	if (!(is_array($groups))) {
		$groups = explode(',', $groups);
	}

	sort($groups);

	$lastNoroleIndex = null;
	$lastNoroleKey = null;

	foreach ($groups as $k => $group) {
		$groupId = format_userinput(ko_groups_decode($group, 'group_id'), 'uint');
		if ($groupId == '' || $groupId == 0) {
			unset($groups[$k]);
			continue;
		}
		if (strpos($group, 'r') === false) {
			$roles = db_select_data('ko_groups', 'where id = ' . $groupId, 'roles', '', '', TRUE, TRUE);
			if ($roles !== null) {
				$roles = $roles['roles'];
				if (strpos($roles, ',') === false) {
					$lastNoroleIndex = $k;
					$lastNoroleGroup = $groupId;
				}
			}
		}
		else {
			if ($groupId == $lastNoroleGroup) {
				unset ($groups[$lastNoroleIndex]);
			}
		}
	}

	return array_merge($groups);
}//ko_groups_remove_spare_norole




/**
  * saves the datafields for a person
	*/
function ko_groups_save_datafields($value, $data, &$log) {
	global $all_groups, $access;

	if(!$all_groups) ko_get_groups($all_groups);

	$id = $data["id"];
	$current_groups = array();
	//save datafields of assigned groups
	foreach(explode(",", $data["groups"]) as $group_id) {
		if(!$group_id) continue;
		$gid = ko_groups_decode($group_id, "group_id");
		$current_groups[] = $gid;
		if($access['groups']['ALL'] < 2 && $access['groups'][$gid] < 2) continue;
		//Don't touch groups with start or stop date set. Their values would be empty and so set to empty as they where not in the form
		if($all_groups[$gid]["stop"] != "0000-00-00" && $all_groups[$gid]["stop"] < strftime("%Y-%m-%d", time())) continue;
		if($all_groups[$gid]["start"] != "0000-00-00" && $all_groups[$gid]["start"] > strftime("%Y-%m-%d", time())) continue;

		if($all_groups[$gid]["datafields"]) {
			$value_log = "";
			// go through all datafields
			foreach(explode(",", $all_groups[$gid]["datafields"]) as $fid) {
				// get current df value
				$old_df = db_select_data("ko_groups_datafields_data", "WHERE `datafield_id` = '$fid' AND `person_id` = '$id' AND `group_id` = '$gid'");
				$old_df = array_shift($old_df);
				// only update and log changes if value has been changed
				if(isset($old_df["value"])) {
					if($old_df["value"] != $value[$gid][$fid]) {
						db_update_data("ko_groups_datafields_data", "WHERE `datafield_id` = '$fid' AND `person_id` = '$id' AND `group_id` = '$gid'", array("value" => $value[$gid][$fid]));
						$value_log .= $value[$gid][$fid].", ";
					}
				} else {
					db_insert_data("ko_groups_datafields_data", array("group_id" => $gid, "person_id" => $id, "datafield_id" => $fid, "value" => $value[$gid][$fid]));
					$value_log .= $value[$gid][$fid].", ";
				}
			}
			if($value_log) $log .= $all_groups[$gid]["name"].": ".$value_log;
		}//if(group[datafields]
	}//foreach(data[groups] as group_id)

	//delete datafields for groups, not assigned anymore
	foreach(explode(",", $data["old_groups"]) as $old) {
		if(!$old) continue;
		$gid = ko_groups_decode($old, "group_id");
		//Don't touch groups with start or stop date set. These would not be in current_groups and so the datafields would be deleted
		if($all_groups[$gid]["stop"] != "0000-00-00" && $all_groups[$gid]["stop"] < strftime("%Y-%m-%d", time())) continue;
		if($all_groups[$gid]["start"] != "0000-00-00" && $all_groups[$gid]["start"] > strftime("%Y-%m-%d", time())) continue;
		if(!in_array($gid, $current_groups)) {
			db_delete_data("ko_groups_datafields_data", "WHERE `group_id` = '$gid' AND `person_id` = '$id'");
		}
	}
}//ko_groups_save_datafields()




/**
  * creates datafields-form for all groups a person is in
	*/
function ko_groups_render_group_datafields($groups, $id, $values=FALSE, $_options=array(), $do_dfs=array()) {
	global $all_groups, $ko_path;

	if(!$all_groups) ko_get_groups($all_groups);

	if(!is_array($groups)) {
		$full_groups = explode(",", $groups);
		$groups = NULL;
		foreach($full_groups as $g) {
			$gid = ko_groups_decode($g, "group_id");
			$groups[] = array_merge($all_groups[$gid], array("desc_full" => ko_groups_decode($g, "group_desc_full")));
		}
	}

	//array_unique()
	$new_groups = array();
	$groups_id = array();
	foreach($groups as $g) {
		if(!in_array($g["id"], $groups_id)) {
			$new_groups[] = $g;
			$groups_id[] = $g["id"];
		}
	}
	$groups = $new_groups;
	unset($groups_id);
	unset($new_groups);


	//get datafield values for this user for all groups
	if(!$values) {
		$fielddata = db_select_data("ko_groups_datafields_data", "WHERE `person_id` = '$id'", "*", "ORDER BY group_id");
		$values = NULL;
		foreach($fielddata as $data) {
			$values[$data["group_id"]][$data["datafield_id"]] = $data["value"];
		}
	}

	$html = NULL; $df = 0;
	foreach($groups as $group) {
		if(!$group["datafields"]) continue;

		if(!$_options["hide_title"]) $html[$df]["title"] = $group["desc_full"];
		foreach(explode(",", $group["datafields"]) as $fid) {
			//Only render given datafields if set
			if(sizeof($do_dfs) > 0 && !$do_dfs[$fid]) continue;

			$value = htmlspecialchars($values[$group["id"]][$fid], ENT_QUOTES, 'ISO-8859-1');

			//get datafield
			$field = db_select_data("ko_groups_datafields", "WHERE `id` = '$fid'", "*", "", "", TRUE);
			if(!$field["id"]) continue;

			$html[$df]["content"] .= '<div style="font-size:9px; font-weight:700;">'.$field["description"].': </div>';

			if($_options["add_leute_id"]) {
				$input_name = "group_datafields[$id][".$group["id"]."][$fid]";
			} else if($_options["koi"]) {
				$input_name = $_options["koi"];
			} else {
				$input_name = "group_datafields[".$group["id"]."][$fid]";
			}

			switch($field["type"]) {
				case "text":
					$html[$df]["content"] .= '<input type="text" size="40" name="'.$input_name.'" value="'.$value.'" />';
				break;

				case "textarea":
					$html[$df]["content"] .= '<textarea cols="40" rows="5" name="'.$input_name.'">'.$value.'</textarea>';
				break;

				case "checkbox":
					$checked = $value ? 'checked="checked"' : "";
					$html[$df]["content"] .= '<input type="checkbox" name="'.$input_name.'" value="1" '.$checked.' />';
				break;

				case "select":
					$options = unserialize($field["options"]);
					if(sizeof($options) == 0) break;

					$html[$df]["content"] .= '<select name="'.$input_name.'" size="0">';
					$html[$df]["content"] .= '<option value=""></option>';
					foreach($options as $o) $html[$df]["content"] .= '<option value="'.$o.'" '.($o == $value ? 'selected="selected"' : '').'>'.$o.'</option>';
					$html[$df]["content"] .= '</select>';
				break;

				case "multiselect":
					$options = unserialize($field["options"]);
					if(sizeof($options) == 0) break;

					$html[$df]["content"] .= '<table><tr><td>';
					$html[$df]["content"] .= '<input type="hidden" name="'.$input_name.'" value="'.$value.'" />';
					$html[$df]["content"] .= '<input type="hidden" name="old_'.$input_name.'" value="'.$value.'" />';
					$html[$df]["content"] .= '<select name="sel_ds1_'.$input_name.'" size="6" onclick="double_select_add(this.options[parseInt(this.selectedIndex)].text, this.options[parseInt(this.selectedIndex)].value, \'sel_ds2_'.$input_name.'\', \''.$input_name.'\');">';
					foreach($options as $o) $html[$df]["content"] .= '<option value="'.$o.'">'.$o.'</option>';
					$html[$df]["content"] .= '</select>';
					$html[$df]["content"] .= '</td><td valign="top">';
					$html[$df]["content"] .= '<img src="'.$ko_path.'images/ds_top.gif" border="0" alt="up" onclick="double_select_move(\''.$input_name.'\', \'top\');" /><br />';
					$html[$df]["content"] .= '<img src="'.$ko_path.'images/ds_up.gif" border="0" alt="up" onclick="double_select_move(\''.$input_name.'\', \'up\');" /><br />';
					$html[$df]["content"] .= '<img src="'.$ko_path.'images/ds_down.gif" border="0" alt="up" onclick="double_select_move(\''.$input_name.'\', \'down\');" /><br />';
					$html[$df]["content"] .= '<img src="'.$ko_path.'images/ds_bottom.gif" border="0" alt="up" onclick="double_select_move(\''.$input_name.'\', \'bottom\');" /><br />';
					$html[$df]["content"] .= '<img src="'.$ko_path.'images/ds_del.gif" border="0" alt="up" onclick="double_select_move(\''.$input_name.'\', \'del\');" />';
					$html[$df]["content"] .= '</td><td>';
					$html[$df]["content"] .= '<select name="sel_ds2_'.$input_name.'" size="6">';
					foreach(explode(",", $value) as $v) if($v) $html[$df]["content"] .= '<option value="'.$v.'">'.$v.'</option>';
					$html[$df]["content"] .= '</select>';
					$html[$df]["content"] .= '</td></tr></table>';
				break;
			}
			$html[$df]["content"] .= "<br />";
		}
		$df++;
	}//foreach(datafields as group)

	$df_html  = '<div id="datafields_form" name="datafields_form" class="df_content">';
	$df_html .= '<table width="100%" cellpadding="0" cellspacing="0">';
	$df_counter = 0;
	foreach($html as $content) {
		if($df_counter == 0) $df_html .= '<tr>';
		$df_html .= '<td width="50%" valign="top">';
		if($content["title"]) $df_html .= '<div class="df_header">'.$content["title"].'</div>';
		$df_html .= $content["content"].'</td>';
		if($df_counter == 0) {
			$df_counter = 1;
		} else {
			$df_counter = 0;
			$df_html .= '</tr>';
		}
	}
	if($df_counter == 1) $df_html .= '<td width="50%">&nbsp;</td></tr>';
	$df_html .= '</table></div>';

	return $df_html;
}//ko_groups_render_group_datafields()





//Liefert alle Gruppen rekursiv
function ko_groups_get_recursive($z_where, $fullarrays=FALSE, $start='NULL') {
	$r = array();

	//Leaves finden
	$not_leaves = db_select_distinct("ko_groups", "pid");

	//Top-Level
	if($start == 'NULL') {
		ko_get_groups($top, "AND `pid` IS NULL ".$z_where, "", "ORDER BY name ASC");
	} else {
		ko_get_groups($top, "AND `pid` = '$start' ".$z_where, "", "ORDER BY name ASC");
	}

	$level = 0;
	foreach($top as $t) {
		if($fullarrays) $r[] = $t;
		else $r[] = array("id" => $t["id"], "name" => $t["name"]);
		rec_groups($t, $r, $z_where, $not_leaves, $fullarrays);
	}//foreach(top)

	return $r;
}//ko_groups_get_recursive()


function rec_groups(&$t, &$r, $z_where="", &$not_leaves, $fullarrays=FALSE) {
	if(!is_array($not_leaves)) $not_leaves = db_select_distinct("ko_groups", "pid");

	//Bei Blättern sofort zurückgeben
	if(!in_array($t["id"], $not_leaves)) return;

	ko_get_groups($children, "AND `pid` = '".$t["id"]."' ".$z_where, "", "ORDER BY name ASC");

	foreach($children as $c) {
		if($fullarrays) $r[] = $c;
		else $r[] = array("id" => $c["id"], "name" => $c["name"]);
		rec_groups($c, $r, $z_where, $not_leaves, $fullarrays);
		unset($children[$c["id"]]);
	}
}//rec_groups()



/**
  * Liefert die WHERE-Bedingung für Gruppen gemäss Option, ob abgelaufene Gruppen angezeigt werden dürfen oder nicht
	*/
function ko_get_groups_zwhere($forceAll=FALSE) {
	if($forceAll || ko_get_userpref($_SESSION['ses_userid'], 'show_passed_groups') == 1) {
		$z_where = "";
	} else {
		$z_where = "AND ((`start` = '0000-00-00' OR `start` <= NOW()) AND (`stop` = '0000-00-00' OR `stop` > NOW()))";
	}
	return $z_where;
}//ko_get_groups_zwhere()



/**
  * Erstellt die JS-Einträge für ein Selmenu für das Gruppen-Modul
	*/
function ko_selmenu_generate_children_entries($top_id, $list_id, &$all_groups, &$all_roles, $show_all_types=FALSE) {
	global $access;
	global $counter, $list_counter, $level, $children;

	if(!is_array($access['groups'])) ko_get_access('groups');

	$level++;
	if(!$list_counter[$level]) $list_counter[$level] = ($level*1000000);
	$group_list = array();

	if($top_id == "NULL") {
		$groups = db_select_data("ko_groups", "WHERE `pid` IS NULL ".ko_get_groups_zwhere(), "*", "ORDER BY `name` ASC");
	} else {
		$groups = db_select_data("ko_groups", "WHERE `pid` = '$top_id' ".ko_get_groups_zwhere(), "*", "ORDER BY `name` ASC");
	}

	//Get an array of the number of children for all groups that have children
	if(!$children) {
		$children = db_select_data('ko_groups', 'WHERE 1=1 '.ko_get_groups_zwhere(), '`pid`,COUNT(`id`) AS num', 'GROUP BY `pid`');
	}

	foreach($groups as $group) {
		if($access['groups']['ALL'] > 0 || $access['groups'][$group['id']] > 0) {
			//Echter Eintrag
			//$mother_line = array_reverse(ko_groups_get_motherline($group["id"], $all_groups));
			//print 'addItem(1, '.$counter.', "g'.implode(":g", $mother_line).":g".$group["id"].'", "'.$descs[$i].'");'."\n";
			list($values, $descs) = ko_groups_get_group_id_names($group["id"], $all_groups, $all_roles, $do_roles=FALSE);
			foreach($values as $i => $value) {
				if($show_all_types || $group["type"] != 1) {  //Platzhalter-Gruppen nicht ausgeben
					print 'addItem('.$list_id.', '.$counter.', "'.$value.'", "'.$descs[$i].'");'."\n";
					$group_list[] = $counter++;
				}
			}

			//Link auf Subliste mit allen Children dieser Gruppe
			if($children[$group['id']]['num'] > 0) {
				ko_selmenu_generate_children_entries($group["id"], $list_id, $all_groups, $all_roles, $show_all_types);
				$level--;
				$group_list[] = $list_counter[($level+1)]++;
			}

		}//if(g_view)
	}//foreach(groups)

	if($top_id == "NULL") {
		print 'addTopList('.$list_id.', 0, "'.implode(", ", $group_list).'");'."\n";
	} else {
		//Muttergruppe für Sublisten-Namen holen
		if($level == 2) {
			print 'addItem('.$list_id.', '.$counter.', "back", "---'.getLL("groups_list_up").'---");'."\n";
		} else {
			print 'addItem('.$list_id.', '.$counter.', "sub:'.$list_counter[($level-1)].'", "---'.getLL("groups_list_up").'---");'."\n";
		}
		$group_list = array_merge(array($counter++), $group_list);
		print 'addSubList('.$list_id.', '.$list_counter[$level].', 0, "'.$all_groups[$top_id]["name"].' -->", "'.implode(", ", $group_list).'");'."\n";
	}
}//ko_selmenu_generate_children_entries()




function ko_update_grouprole_filter() {
	//Rollen-Filter machen, der nur nach Rolle suchen lässt
	$new_code  = "<select name=\"var1\" size=\"0\">";
	$new_code .= '<option value="0"></option>';

	//Gruppen-Select
	ko_get_grouproles($roles);
	foreach($roles as $r) {
		$new_code .= '<option value="r'.$r["id"].'">'.$r["name"].'</option>';
	}
	$new_code .= '</select>';

	db_update_data("ko_filter", "WHERE `typ`='leute' AND `name`='role'", array("code1" => $new_code));
}//ko_update_grouprole_filter()




function ko_get_group_count($id, $rid='') {
	$id = format_userinput($id, 'uint');
	if(!$id) return 0;

	if($rid) {
		$rex = 'g'.$id.':r'.$rid;
	} else {
		$rex = 'g'.$id.'(:r|,|$)';
	}
	$count = db_get_count('ko_leute', 'id', "AND `groups` REGEXP '$rex' AND deleted = '0' ");
	return $count;
}//ko_get_group_count()


function ko_update_group_count($id, $rid='') {
	$id = format_userinput($id, 'uint');
	if(!$id) return 0;

	db_update_data('ko_groups', "WHERE `id` = '$id'", array('count' => ko_get_group_count($id, $rid)));
}//ko_update_group_count()









/************************************************************************************************************************
 *                                                                                                                      *
 * P R O J E C T S - F U N K T I O N E N                                                                                *
 *                                                                                                                      *
 ************************************************************************************************************************/
function ko_projects_get_name($project) {
	if(!is_array($project)) {
		$project = db_select_data("ko_projects", "WHERE `id` = '$project'", "*", "", "", TRUE);
	}
	$name = $project["number"]." ".$project["title"];

	return $name;
}//ko_projects_get_name()





/************************************************************************************************************************
 *                                                                                                                      *
 * M U L T I E D I T - F U N K T I O N E N                                                                              *
 *                                                                                                                      *
 ************************************************************************************************************************/
function ko_include_kota($tables=array()) {
	global $BASE_PATH, $KOTA, $ko_menu_akt, $access, $SMALLGROUPS_ROLES, $LOCAL_LANG;

	//Include KOTA function (once)
	include_once($BASE_PATH.'inc/kotafcn.php');

	//Include KOTA table definitions for given tables
	$KOTA_TABLES = $tables;
	include($BASE_PATH.'inc/kota.inc.php');

	//Apply access rights --> unset KOTA columns the current user has no access to
	foreach($tables as $table) {
		$delcols = array();
		$cols = ko_access_get_kota_columns($_SESSION['ses_userid'], $table);
		if(sizeof($cols) > 0) {
			foreach($KOTA[$table] as $k => $v) {
				if(substr($k, 0, 1) == '_') continue;
				if(!in_array($k, $cols)) {
					$delcols[] = $k;
					unset($KOTA[$table][$k]);
				}
			}
			foreach($KOTA[$table]['_listview'] as $lk => $lv) {
				if(in_array($lv['name'], $delcols)) unset($KOTA[$table]['_listview'][$lk]);
			}
		}
	}
}//ko_include_kota()




/**
	* Generates a form for multiedit or a single entry
	*
	* Get information from KOTA to render a form for editing one or more entries
	*
	* @param string $table Table to edit
	* @param array $columns List of columns to be edited (empty for all)
	* @param string $ids Comma separated list of ids to be edited. 0 for a new entry
	* @param string $order ORDER BY statement to be used if editing multiple entries
	* @param array $form_data Data for the rendering of the form (like title etc.)
	* @param boolean $return_only_group Renders form if set to false, only return group array otherwise which can be used to feed ko_formular.tmpl through smarty
	* @param string $_kota_type Specify kota type for a new entry
	*/
function ko_multiedit_formular($table, $columns=NULL, $ids=0, $order="", $form_data="", $return_only_group=FALSE, $_kota_type='') {
	global $smarty, $mysql_connection, $mysql_pass;
	global $KOTA, $js_calendar, $BASE_URL;

	//Columns used in SQL
	if(empty($columns)) {  //not multiedit, so take all KOTA-columns
		$mode = "single";
		//Get columns from DB
		$_table_cols = db_get_columns($table);
		$table_cols = array();
		foreach($_table_cols as $col) {
			$table_cols[] = $col['Field'];
		}
		foreach($KOTA[$table] as $kota_col => $array) {
			if(substr($kota_col, 0, 1) == "_") continue;   // _multititle, _listview, _access
			if(!isset($array['form']) || $array['form']['ignore']) continue;         // ignore this column all together
			$columns[] = $kota_col;
		}

		foreach($columns as $column) {
			if($KOTA[$table][$column]["form"]["dontsave"]) continue;
			if(!in_array($column, $table_cols)) continue;
			$sel_columns[] = "`".$column."`";
		}
		//Add columns for other types
		if(isset($KOTA[$table]['_types']['field'])) {
			foreach($KOTA[$table]['_types']['types'] as $type => $typedef) {
				if(!is_array($typedef['add_fields'])) continue;
				foreach($typedef['add_fields'] as $add_colid => $add_col) {
					$sel_columns[] = "`".$add_colid."`";
				}
			}
		}
		$sel_columns = implode(',', array_unique($sel_columns));

		//Add help for the given table
		$smarty->assign("help", ko_get_help("kota", $table));
	} else {  //multiedit, only use given column(s)
		$mode = "multi";
		$showForAll = TRUE;
		foreach($columns as $c_i => $col) {
			if(substr($col, 0, 6) == "MODULE") {
				switch(substr($col, 6, 3)) {
					case "grp":
						$db_cols .= "`groups`,";
					break;
				}
			} else {
				$db_cols .= "`".$col."`,";
			}
		}
		$sel_columns = $db_cols.implode(",", array_keys($KOTA[$table]["_multititle"]));

		//Add help for multiediting
		$smarty->assign("help", ko_get_help("kota", "multiedit"));
	}//if..else(!columns)

	//Add type column. Must be selected, so the record can be checked for it's type, which might change the form
	if(isset($KOTA[$table]['_types']['field'])) {
		$sel_columns .= ','.$KOTA[$table]['_types']['field'];
	}

	//IDs of the records to be edited
	if($ids == 0) {  //no multiedit, might be a form for a new entry, so don't ask for ids
		$ids = array(0);
		$new_entry = TRUE;
		$sel_ids = 0;
		$row = array("id" => 0);
		foreach($kota_cols as $kota_col) $row[$kota_col] = "";
	} else {  //multiedit, so ids to be edited must be given
		$new_entry = FALSE;
		$sel_ids = "";
		if(is_array($ids)) {
			foreach($ids as $id) {
				$sel_ids .= "'$id', ";
			}
			$sel_ids = substr($sel_ids, 0, -2);
		} else {
			$sel_ids = $ids;
			$ids = array($ids);
		}

		if(!$sel_ids) return FALSE;
	}


	//Get data from DB
	$result = mysqli_query($db_connection, "SELECT id,$sel_columns FROM `$table` WHERE `id` IN ($sel_ids) $order");

	//Start building the form
	$rowcounter = 0;
	$gc = 0;
	//Loop through all entries
	while($new_entry || $showForAll || $row = mysqli_fetch_assoc($result)) {
		if($new_entry) {  //Single edit form
			$group[$gc] = array();
		} else if($showForAll) {  //Add edit fields to be applied to all edited rows
			$group[$gc] = array("forAll" => TRUE, "titel" => getLL("multiedit_title_forAll"), "state" => "closed", "table" => $table);
			$row = array();
		} else {  //normal multiedit rows
			$titel = "";
			//Set title for each entry
			foreach($KOTA[$table]["_multititle"] as $tc => $tc_fcn) {
				$val = $row[$tc];
				if($tc_fcn != "") eval("\$val=".str_replace("@VALUE@", $val, $tc_fcn).";");
				$titel .= "$val ";
			}
			$group[$gc] = array("titel" => $titel, "state" => "open", "colspan" => 'colspan="2"');
		}

		//Add columns if a certain kota type is given
		if($mode == 'single' && isset($KOTA[$table]['_types']['field'])) {
			$kota_type = $_kota_type ? $_kota_type : $row[$KOTA[$table]['_types']['field']];
			if($kota_type != $KOTA[$table]['_types']['default'] && sizeof($KOTA[$table]['_types']['types'][$kota_type]['add_fields']) > 0) {
				foreach($KOTA[$table]['_types']['types'][$kota_type]['add_fields'] as $add_colid => $add_col) {
					$KOTA[$table][$add_colid] = $add_col;
					$columns[] = $add_colid;
				}
			}
		}

		//Alle zu bearbeitenden Spalten für diesen Datensatz
		$col_pos = 0;
		foreach($columns as $col) {

			//Reset own_row
			$own_row = FALSE;

			//Check if this column should show for this type (if types are defined for this KOTA table)
			if(isset($KOTA[$table]['_types']['field'])) {
				$kota_type = $_kota_type ? $_kota_type : $row[$KOTA[$table]['_types']['field']];
				if($kota_type != $KOTA[$table]['_types']['default']) {
					if(!in_array($col, $KOTA[$table]['_types']['types'][$kota_type]['use_fields'])
							&& !in_array($col, array_keys($KOTA[$table]['_types']['types'][$kota_type]['add_fields'])) ) {
						//Unset ID for multiediting (this column may not be edited for this row)
						if($mode == 'multi') {
							foreach($ids as $ik => $iv) {
								if($iv == $row['id']) unset($ids[$ik]);
							}
						}
						//Unset column, so column check after submission will work correctly
						foreach($columns as $ck => $cv) {
							if($cv == $col) unset($columns[$ck]);
						}
						//And don't show input
						continue;
					}
				}
			}

			//Call a fill function to prefill this input
			if($KOTA[$table][$col]['fill']) {
				$fcn = substr($KOTA[$table][$col]['fill'], 4);
				if(function_exists($fcn)) {
					eval("$fcn(\$row, \$col);");
				}
			}

			$keep_name = $keep_name_PLUS = "";
			if($showForAll) {
				$keep_name = "koi[$table][$col][forAll]";
				$keep_name_PLUS = "koi[$table][$col"."_PLUS][forAll]";
			}
			$col = str_replace("`", "", $col);
			//Module bearbeiten, damit in row[col] überhaupt etwas steht
			if(substr($col, 0, 6) == "MODULE") {
				switch(substr($col, 6, 3)) {
					case "grp":
						if(FALSE === strpos($col, ':')) {
							$g_value = array();
							$gid = substr($col, 9);
							foreach(explode(",", $row["groups"]) as $g) {
								if(ko_groups_decode($g, "group_id") == $gid) $g_value[] = $g;
							}
							$row[$col] = implode(",", $g_value);
						} else {
							$gid = substr($col, 9, 6);  //group id
							$fid = substr($col, 16, 6); //datafield id
							//only continue if person is assigned to this group
							if(FALSE !== strpos($row["groups"], "g".$gid) || $showForAll) {
								$koi_name = $keep_name ? $keep_name : "koi[ko_leute][$col][".$row["id"]."]";
								$code = ko_groups_render_group_datafields($gid, $row["id"], FALSE, array("koi" => $koi_name), array($fid => TRUE));
								$KOTA[$table][$col]["form"] = array("desc" => getLL("groups_edit_datafield"), "type" => "html", "value" => $code);
							} else {
								$KOTA[$table][$col]["form"] = array("desc" => "-");
							}
						}
					break;
				}
			}//if(MODULE)
			$do_1 = FALSE;
			$type = $KOTA[$table][$col]["form"]["type"];
			//If no type defined then don't output this form field (maybe only used in list view)
			if(!$type) continue;

			//Add description from LL
			if(!$KOTA[$table][$col]["form"]["desc"]) {
				$ll_value = getLL("kota_".$table."_".$col);
				$KOTA[$table][$col]["form"]["desc"] = $ll_value ? $ll_value : $col;
			}
			$help = ko_get_help($module, 'kota.'.$table.'.'.$col);
			if($help['show']) {
				$KOTA[$table][$col]['form']['help'] = $help['link'];
			}

			//Vorbehandlung für versch. Typen
			if($type == "date") {  //no JS-DateSelect (used for multiedit)
				$type = "text";
			}

			else if($type == "jsdate" && is_object($js_calendar)) {  //use js-calendar
				$type = "html";
				//Prefill form for new entry with POST data
				$post_date = $_POST['koi'][$table][$col][$row['id']];
				if($new_entry && $KOTA[$table][$col]['form']['prefill_new'] && $post_date != '' && $post_date == format_userinput($post_date, 'date')) {
					$date = $_POST['koi'][$table][$col][$row['id']];
				} else {
					$date = $KOTA[$table][$col]["form"]["value"];
					if(strlen($date) > 10) $date = "";  //if several jsdate inputs are used on one page, value still contains HTML from the last one
				}
				if(!$date && $row[$col] != "0000-00-00") $date = sql2datum($row[$col]);

				if($KOTA[$table][$col]['pre'] != '' && isset($row[$col])) {
					$data = $row;
					kota_process_data($table, $data, 'pre', $_log);
					$date = $data[$col];
				}

				$name = $keep_name ? $keep_name : "koi[$table][$col][".$row["id"]."]";
				$KOTA[$table][$col]["form"]["value"] = $js_calendar->make_input_field(array(), array("name" => $name, "value" => $date));
			}

			else if($type == 'multidateselect' && is_object($js_calendar)) {  //use js-calendar
				$name = $keep_name ? $keep_name : "koi[$table][$col][".$row['id'].']';
				$onchange = "double_select_add(this.value, this.value, 'sel_ds2_$name', '$name');";
				$KOTA[$table][$col]['form']['dateselect'] = $js_calendar->make_input_field(array('ifFormat' => '%Y-%m-%d', 'closeOnClick' => false, 'align' => 'Bl'), array('name' => 'txt_'.$name, 'onchange' => $onchange, 'size' => '10'));

				if(!$new_entry) {  //add entries from db if edit. If new, kota_assign_values() assigns avalue/adescs/avalues - if needed
					$KOTA[$table][$col]['form']['avalues'] = $KOTA[$table][$col]['form']['adescs'] = array();
					foreach(explode(',', $row[$col]) as $v) {
						$KOTA[$table][$col]['form']['avalues'][] = $v;
						$KOTA[$table][$col]['form']['adescs'][] = $v;
					}
					$KOTA[$table][$col]['form']['avalue'] = $row[$col];
				}
			}

			else if($type == "checkbox") {
				$KOTA[$table][$col]["form"]["params"] = $row[$col] ? 'checked="checked"' : '';
				$row[$col] = 1;  //Für Checkboxen Value immer auf 1 setzen
			}

			else if($type == 'switch') {
				if($row[$col] == '') $row[$col] = 0;
			}

			else if($type == "textplus") {
				//Create a second input field as text input (but only when editing a single entry and not for multiedit)
				if($mode == "single") {
					$do_1 = TRUE;
					$do_1_array = array("desc" => getLL("form_textplus").":",
															"type" => "text",
															"name" => ($keep_name_PLUS ? $keep_name_PLUS : ("koi[".$table."][".$col."_PLUS][".$row["id"]."]")),
															"params" => $KOTA[$table][$col]["form"]["params_PLUS"],
															);
				}
				$type = "select";
				//set size of select to 0
				$KOTA[$table][$col]["form"]["params"] = 'size="0"';
				//get values for the select
				if(!$KOTA[$table][$col]["form"]["values"]) {
					$values = db_select_distinct($table, $col, "", $KOTA[$table][$col]['form']['where'], $KOTA[$table][$col]["form"]["select_case_sensitive"] ? TRUE : FALSE);
					if($KOTA[$table][$col]['form']['PLUS_addempty']) $values[] = '';
					$KOTA[$table][$col]["form"]["values"] = $values;
					$KOTA[$table][$col]["form"]["descs"] = $values;
				}
			}

			else if($type == 'textmultiplus') {
				if(!$KOTA[$table][$col]['form']['js_func_add']) $KOTA[$table][$col]['form']['js_func_add'] = 'double_select_add';
				//get values for the select
				if(!$KOTA[$table][$col]['form']['values']) {
					$values = kota_get_textmultiplus_values($table, $col);
					$KOTA[$table][$col]['form']['values'] = $values;
					$KOTA[$table][$col]['form']['descs'] = $values;
				}
				//Add active entries for edit
				if(!$new_entry) {
					$avalue = $row[$col];
					$KOTA[$table][$col]['form']['avalue'] = $avalue;
					if($avalue != '') {
						$KOTA[$table][$col]['form']['adescs'] = explode(',', $avalue);
						$KOTA[$table][$col]['form']['avalues'] = explode(',', $avalue);
					}
				}
			}

			else if($type == "doubleselect") {
				if(!$KOTA[$table][$col]["form"]["js_func_add"]) $KOTA[$table][$col]["form"]["js_func_add"] = "double_select_add";
				if(!$new_entry) {  //add entries from db if edit. If new, kota_assign_values() assigns avalue/adescs/avalues - if needed
					$KOTA[$table][$col]["form"]["avalues"] = $KOTA[$table][$col]["form"]["adescs"] = array();
					$valuesi = array_flip($KOTA[$table][$col]["form"]["values"]);
					foreach(explode(",", $row[$col]) as $v) {
						$KOTA[$table][$col]["form"]["avalues"][] = $v;
						if($KOTA[$table][$col]['form']['group_desc_full']) {
							$KOTA[$table][$col]['form']['adescs'][] = ko_groups_decode(ko_groups_decode($v, 'full_gid'), 'group_desc_full');
						} else {
							//Use description from descs. If not set then fall back to all_descs.
							// This can be used to include descs for values that can not be assigned anymore (like roles for groups which are full)
							$KOTA[$table][$col]['form']['adescs'][] = $KOTA[$table][$col]['form']['descs'][$valuesi[$v]] ? $KOTA[$table][$col]['form']['descs'][$valuesi[$v]] : $KOTA[$table][$col]['form']['all_descs'][$v];
						}
					}
					$KOTA[$table][$col]["form"]["avalue"] = $row[$col];
				}
			}

			else if($type == 'checkboxes') {
				if(!$new_entry) {  //add entries from db if edit. If new, kota_assign_values() assigns avalue/adescs/avalues - if needed
					$KOTA[$table][$col]['form']['avalues'] = $KOTA[$table][$col]['form']['adescs'] = array();
					$valuesi = array_flip($KOTA[$table][$col]['form']['values']);
					foreach(explode(',', $row[$col]) as $v) {
						$KOTA[$table][$col]['form']['avalues'][] = $v;
					}
					$KOTA[$table][$col]['form']['avalue'] = $row[$col];
				}
			}

			else if($type == "dyndoubleselect") {
				$type = "doubleselect";
				if(!$KOTA[$table][$col]["form"]["js_func_add"]) $KOTA[$table][$col]["form"]["js_func_add"] = "double_select_add";
				$KOTA[$table][$col]["form"]["avalues"] = $KOTA[$table][$col]["form"]["adescs"] = array();

				$values = $KOTA[$table][$col]["form"]["values"];
				$descs = $KOTA[$table][$col]["form"]["descs"];
				unset($KOTA[$table][$col]["form"]["values"]);
				unset($KOTA[$table][$col]["form"]["descs"]);
				if($row[$col] || $KOTA[$table][$col]['form']['value']) { //Current value given
					if(!$row[$col]) $row[$col] = $KOTA[$table][$col]['form']['value'];
					foreach(explode(",", $row[$col]) as $v) {
						$KOTA[$table][$col]["form"]["avalues"][] = $v;
						$KOTA[$table][$col]["form"]["adescs"][] = $descs[$v];
					}
					$KOTA[$table][$col]["form"]["avalue"] = $row[$col];
				}
				//Build top level of select
				foreach($values as $vid => $value) {
					$KOTA[$table][$col]["form"]["values"][] = $vid;
					$suffix = is_array($value) ? "-->" : "";
					$KOTA[$table][$col]["form"]["descs"][] = $descs[$vid].$suffix;
				}
			}

			else if($type == "dynselect") {
				//Only works for single edit (not multiedit) because KOTA[..][values] would be different for each multiedit item, which doesn't work
				$type = "select";
				if(!$KOTA[$table][$col]["form"]["_done"]) {
					if($showForAll) {  //First time when multiediting
						list($values, $descs) = kota_convert_dynselect_select($KOTA[$table][$col]["form"]["values"], $KOTA[$table][$col]["form"]["descs"]);
						$KOTA[$table][$col]["form"]["params"] = ' size="0"';  //Set size to 0 for multiedit
						$KOTA[$table][$col]["form"]["values"] = $values;
						$KOTA[$table][$col]["form"]["descs"] = $descs;
						$KOTA[$table][$col]["form"]["_done"] = TRUE;  //So this conversion is only done once for the forAll entry and then used in all
					} else {  //Normal form, no multiediting
						$values = $KOTA[$table][$col]["form"]["values"];
						$descs = $KOTA[$table][$col]["form"]["descs"];
						unset($KOTA[$table][$col]["form"]["values"]);
						unset($KOTA[$table][$col]["form"]["descs"]);
						if($row[$col]) {  //Current value given
							$KOTA[$table][$col]["form"]["avalues"] = $KOTA[$table][$col]["form"]["adescs"] = array();
							$KOTA[$table][$col]["form"]["avalue"] = $row[$col];
							//If current value is not found on top level then go through all lower levels to find it and display this level
							if(!in_array($row[$col], array_keys($values))) {
								foreach($values as $vid => $value) {
									if(substr($vid, 0, 1) != "i") continue;
									if(in_array($row[$col], $value)) {
										$values = array("i-" => "i-");
										//Add all values from this level
										foreach($value as $v) $values[$v] = $v;
										//Add link to go back up to the index
										$descs["i-"] = getLL("form_peopleselect_up");
										break;
									}
								}
							}//if(!in_array(row[col], values))
						}//if(row[col])
						//Build top level of select
						foreach($values as $vid => $value) {
							$KOTA[$table][$col]["form"]["values"][] = $vid;
							$suffix = is_array($value) ? "-->" : "";
							$KOTA[$table][$col]["form"]["descs"][] = $descs[$vid].$suffix;
						}
					}//if..else(showForAll) (multiedit)
				}//if(!KOTA[form][_done])
			}

			else if($type == 'peoplesearch') {
				if($row[$col] != '') {
					$lids = explode(",", $row[$col]);
					list($av, $ad) = kota_peopleselect($lids, $KOTA[$table][$col]['form']['sort']);
					$KOTA[$table][$col]["form"]["avalues"] = $av;
					$KOTA[$table][$col]["form"]["adescs"] = $ad;

					$KOTA[$table][$col]["form"]["avalue"] = $row[$col];
				}
			}

			else if($type == 'peoplefilter') {
				ko_get_filters($_filters, 'leute', TRUE);
				$filters = array();
				foreach($_filters as $k => $f) {
					$filters[$k] = $f['name'];
				}
				$KOTA[$table][$col]['form']['filters'] = $filters;
				
				$avalues = explode(',', $row[$col]);
				$adescs = array();
				$filterArray = kota_peoplefilter2filterarray($row[$col]);
				foreach($filterArray as $fk => $filter) {
					if(!is_numeric($fk)) continue;
					ko_get_filter_by_id($filter[0], $f);
					$text = $f['name'].': ';

					//Mark negative
					if($filter[2]) $text .= '!';

					//Tabellen-Name, auf den dieser Filter am ehesten wirkt, auslesen/erraten:
					$fcol = array();
					for($c=1; $c<4; $c++) {
						list($fcol[$c]) = explode(' ', $f['sql'.$c]);
					}
					$t1 = $t2 = '';
					for($i=0; $i<=sizeof($filter[1]); $i++) {
						$v = map_leute_daten($filter[1][$i], ($fcol[$i] ? $fcol[$i] : $fcol[1]), $t1, $t2, FALSE, array('num' => $i));
						if($v) $text .= $v.',';
					}
					$text = substr($text, 0, -1);
					$adescs[] = $text;
				}
				$KOTA[$table][$col]['form']['avalue'] = $row[$col];
				$KOTA[$table][$col]['form']['avalues'] = $avalues;
				$KOTA[$table][$col]['form']['adescs'] = $adescs;
			}

			else if($type == 'foreign_table') {
				$own_row = TRUE;

				if($new_entry) $pid = 'new'.md5(uniqid('', TRUE));
				else $pid = $row['id'];
				if (isset($KOTA[$table][$col]['form']['foreign_table_preset'])) {
					$presetSettings = $KOTA[$table][$col]['form']['foreign_table_preset'];
					$smarty->assign('ft_preset_table', $presetSettings['table']);
					$smarty->assign('ft_preset_join_value_local', $row[$presetSettings['join_column_local']]);
					$smarty->assign('ft_preset_join_column_foreign', $presetSettings['join_column_foreign']);
					$alertMessage = getLL($presetSettings['ll_no_join_value']);
					if ($alertMessage == '') $alertMessage = getLL('form_ft_alert_no_join_value');
					$smarty->assign('ft_alert_no_join_value', $alertMessage);
				}
				$smarty->assign('ft_pid', $pid);
				$smarty->assign('ft_field', $table.'.'.$col);
				$smarty->assign('ft_content', kota_ft_get_content($table.'.'.$col, $pid));
			}

			else if($type == "file") {
				//Show thumb if possible
				if($row[$col]) {
					$thumb = ko_pic_get_tooltip($row[$col], 40, 200);
					if($thumb) {
						$KOTA[$table][$col]['form']['special_value'] = $thumb;
						$KOTA[$table][$col]['form']['value'] = ' ';
					} else {
						$KOTA[$table][$col]['form']['value'] = $row[$col];
						$KOTA[$table][$col]['form']['special_value'] = '';
					}
				} else {
					//Reset KOTA entry otherwise previous entries fill later entries
					$KOTA[$table][$col]['form']['value'] = '';
					$KOTA[$table][$col]['form']['special_value'] = '';
				}
				//add delete checkbox for files
				$KOTA[$table][$col]["form"]["value2"] = getLL("delete");
				$KOTA[$table][$col]["form"]["name2"] = "koi[$table][$col"."_DELETE][".$row["id"]."]";
			}//if..else(type==...)

			if(($do_1 || $own_row) && $col_pos == 1) {
				$rowcounter++;
				$col_pos = 0;
			}

			//prefill_new: Prefill value for new
			if(in_array($type, array('select'))) {
				$post_date = $_POST['koi'][$table][$col][$row['id']];
				if($new_entry && $KOTA[$table][$col]['form']['prefill_new'] && $_POST['koi'][$table][$col][$row['id']] != '') {
					$KOTA[$table][$col]['form']['value'] = $_POST['koi'][$table][$col][$row['id']];
				}
			}

			$group[$gc]["row"][$rowcounter]["inputs"][$col_pos] = $KOTA[$table][$col]["form"];
			$group[$gc]["row"][$rowcounter]["inputs"][$col_pos]["type"] = $type;
			if(!$KOTA[$table][$col]["form"]["value"]) {
				$val = $row[$col];
				if($KOTA[$table][$col]["pre"] != "") {
					$data = array($col => $val);
					$group[$gc]['row'][$rowcounter]['inputs'][$col_pos]['ovalue'] = $val;
					kota_process_data($table, $data, "pre", $_log);
					$val = $data[$col];
				}
				$group[$gc]["row"][$rowcounter]["inputs"][$col_pos]["value"] = $val;
			}
			if($keep_name) {
				$group[$gc]["row"][$rowcounter]["inputs"][$col_pos]["name"] = $keep_name;
			} else {
				$group[$gc]["row"][$rowcounter]["inputs"][$col_pos]["name"] = "koi[$table][$col][".$row["id"]."]";
			}

			//Zweite Spalte mit einer Eingabe erstellen
			if($do_1) {
				$group[$gc]["row"][$rowcounter]["inputs"][1] = $do_1_array;
			}

			if($col_pos == 1 || $do_1 || $KOTA[$table][$col]["form"]["new_row"]) {
				$rowcounter++;
				$col_pos = 0;
			} else {
				$col_pos = 1;
			}

		}//foreach(columns as col)
		$new_entry = FALSE;
		$showForAll = FALSE;
		$gc++;
	}//while(row)

	if($return_only_group) return $group;

	//Remove columns marked with ignore_test and columns referencing a foreign table
	$new = array();
	foreach($columns as $ci => $c) {
		if($KOTA[$table][$c]['form']['ignore_test']) continue;
		if($KOTA[$table][$c]['form']['type'] == 'foreign_table') continue;
		$new[] = $c;
	}
	$columns = $new;

	//Controll-Hash
	sort($columns);
	sort($ids);
	//print $mysql_pass.$table.implode(":", $columns).implode(":", $ids);
	$hash_code = md5(md5($mysql_pass.$table.implode(":", $columns).implode(":", $ids)));
	$hash = $table."@".implode(",", $columns)."@".implode(",", $ids)."@".$hash_code;

	//Add legend
	$legend = getLL("kota_formlegend_".$table);
	if($legend) {
		$smarty->assign("tpl_legend", $legend);
		$smarty->assign("tpl_legend_icon", getLL("kota_formlegend_".$table."_icon"));
	}

	if($kota_type) {
		$hidden_inputs[] = array('name' => 'kota_type', 'value' => $kota_type);
	}
	$smarty->assign('tpl_hidden_inputs', $hidden_inputs);

	$smarty->assign("tpl_titel", $form_data["title"] ? $form_data["title"] : getLL("multiedit_title"));
	$smarty->assign("tpl_submit_value", $form_data["submit_value"] ? $form_data["submit_value"] : getLL("save"));
	$smarty->assign("tpl_id", $hash);
	$smarty->assign("tpl_action", $form_data["action"] ? $form_data["action"] : "submit_multiedit");
	if($form_data['action_as_new']) {
		$smarty->assign('tpl_submit_as_new', ($form_data['label_as_new']?$form_data['label_as_new']:getLL('save_as_new')));
		$smarty->assign('tpl_action_as_new', $form_data['action_as_new']);
	}
	$smarty->assign("tpl_cancel", $form_data["cancel"]);
	$smarty->assign("tpl_groups", $group);
	$smarty->display("ko_formular.tpl");
}//ko_multiedit_formular()




/**
 * Get addresses from ko_leute for a KOTA field of type peoplesearch
 * @param array Array of currently selected IDs
 * @param boolean Set to true to return addresses ordered by name (default).
 *                Set to false to return the addresses in the order of the given IDs
 * @return array Two arrays are returned holding the IDs and labels to be used as options for a select
 */
function kota_peopleselect($ids, $sort=TRUE) {
	$avalues = $adescs = array();

	//get people from db
	$order = $sort ? 'ORDER BY nachname,vorname ASC' : '';
	$_leute_rows = db_select_data("ko_leute", "WHERE `id` IN ('".implode("','", $ids)."')", "id,vorname,nachname,firm,department", $order);
  if(!$sort) {
    //Keep order of ids as given in array
    foreach($ids as $id) $leute_rows[$id] = $_leute_rows[$id];
  } else {
    $leute_rows = $_leute_rows;
  }
	foreach($leute_rows as $leute_row) {
		if($leute_row["nachname"]) {
			$value = $leute_row["vorname"]." ".$leute_row["nachname"];
		} else if($leute_row["firm"]) {
			$value = $leute_row["firm"]." (".$leute_row["department"].")";
		}
		$avalues[] = $leute_row["id"];
		$adescs[] = $value;
	}

	return array($avalues, $adescs);
}//kota_peopleselect()










/************************************************************************************************************************
 *                                                                                                                      *
 * D B - F U N K T I O N E N                                                                                            *
 *                                                                                                                      *
 ************************************************************************************************************************/

/**
 * Get the enum values of a db column
 *
 * @param string Table where the enum column is defined
 * @param string Column to get the enum values from
 * @return array All the enum values as array
 */
function db_get_enums($table, $col) {
	global $db_connection, $DEBUG_db;

	if(isset($GLOBALS["kOOL"]["db_enum"][$table][$col])) {
		return $GLOBALS["kOOL"]["db_enum"][$table][$col];
	}

	$query = "SHOW COLUMNS FROM $table LIKE '$col'";
	if(defined('DEBUG_SELECT') && DEBUG_SELECT) $time_start = microtime(TRUE);
	$result = mysqli_query($db_connection, $query);
	if($result === FALSE) trigger_error('DB ERROR (db_get_enums): '.mysqli_errno($db_connection).': '.mysqli_error($db_connection).', QUERY: '.$query, E_USER_ERROR);
	if(defined('DEBUG_SELECT') && DEBUG_SELECT) {
		$DEBUG_db->queryCount++;
		$DEBUG_db->queries[] = array('time' => (microtime(TRUE)-$time_start)*1000, 'sql' => $query);
	}
	if(mysqli_num_rows($result)>0){
		$row=mysqli_fetch_row($result);
		$options=explode("','",preg_replace("/(enum|set)\('(.+?)'\)/","\\2",$row[1]));
	}

	$GLOBALS["kOOL"]["db_enum"][$table][$col] = $options;

	return $options;
}//db_get_enums()




/**
	* Get the corresponding ll values for enum values of a db column
	*
	* @param string Table where the enum column is defined
	* @param string Column to get the enum values from
	* @return array All the localised enum values as array
	*/
function db_get_enums_ll($table, $col) {
	$ll = array();

	$options = db_get_enums($table, $col);
	foreach($options as $o) {
		$ll_value = getLL($table."_".$col."_".$o);
		if(!$ll_value) $ll_value = getLL('kota_'.$table.'_'.$col.'_'.$o);
		$ll[$o] = $ll_value ? $ll_value : $o;
	}
	return $ll;
}//db_get_enums_ll()



/**
 * Get columns of a db table
 *
 * @param string Name of database
 * @param string Name of table
 * @param string A search string to only show columns that match this value
 * @return array Columns
 */
function db_get_columns($table, $field="") {
	global $db_connection, $DEBUG_db;

	$r = array();

	//Get value from global cache array if already set
	if($field != "" && isset($GLOBALS["kOOL"]["db_columns"][$table][$field])) {
		return $GLOBALS["kOOL"]["db_columns"][$table][$field];
	}

	if($field != "") $like = "LIKE '$field'";
	else $like = "";

	$query = "SHOW COLUMNS FROM $table $like";
	if(defined('DEBUG_SELECT') && DEBUG_SELECT) $time_start = microtime(TRUE);
	$result = mysqli_query($db_connection, $query);
	if($result === FALSE) trigger_error('DB ERROR (db_get_columns): '.mysqli_errno($db_connection).': '.mysqli_error($db_connection).', QUERY: '.$query, E_USER_ERROR);
	if(defined('DEBUG_SELECT') && DEBUG_SELECT) {
		$DEBUG_db->queryCount++;
		$DEBUG_db->queries[] = array('time' => (microtime(TRUE)-$time_start)*1000, 'sql' => $query);
	}
	while($row = mysqli_fetch_assoc($result)) {
    	$r[] = $row;
	}

	//Store value in global cache array
	if($field) $GLOBALS["kOOL"]["db_columns"][$table][$field] = $r;

	return $r;
}//db_get_columns()



/**
 * Number of entries in a db table
 *
 * @param string Table
 * @param string Column to count the different values for
 * @param string WHERE statement to add
 * @return int Number of different entries
 */
function db_get_count($table, $field = "id", $z_where = "") {
	global $db_connection, $DEBUG_db;

	if($field == '') $field = 'id';
	$query = "SELECT COUNT(`$field`) AS count FROM `$table` ".(($z_where)?" WHERE 1=1 $z_where":"");
	if(defined('DEBUG_SELECT') && DEBUG_SELECT) $time_start = microtime(TRUE);
	$result = mysqli_query($db_connection, $query);
	if($result === FALSE) trigger_error('DB ERROR (db_get_count): '.mysqli_errno($db_connection).': '.mysqli_error($db_connection).', QUERY: '.$query, E_USER_ERROR);
	if(defined('DEBUG_SELECT') && DEBUG_SELECT) {
		$DEBUG_db->queryCount++;
		$DEBUG_db->queries[] = array('time' => (microtime(TRUE)-$time_start)*1000, 'sql' => $query);
	}
	$row = mysqli_fetch_assoc($result);
	return $row["count"];
}//db_get_count()



/**
 * Get the next auto_increment value for a table
 *
 * @param string Table
 * @return int Next auto_increment value
 */
function db_get_next_id($table) {
	global $db_connection;

	$query = "SHOW TABLE STATUS LIKE '$table'";
	$result = mysqli_query($db_connection, $query);
	if($result === FALSE) trigger_error('DB ERROR (db_get_next_id): '.mysqli_errno($db_connection).': '.mysqli_error($db_connection).', QUERY: '.$query, E_USER_ERROR);
	$row = mysqli_fetch_assoc($result);
	return $row["Auto_increment"];
}



/**
 * Inserts data into a database table
 *
 * This should be used instead of issuing INSERT queries directly
 *
 * @param string Table where the data should be inserted
 * @param array Data array with the keys beeing the name of the db columns
 * @return int id of the newly inserted row
 */
function db_insert_data($table, $data) {
	global $db_connection, $DEBUG_db;

	$columnsTemp = db_get_columns($table);
	$columns = array();
	$unset = array();

	foreach ($columnsTemp as $column) {
		$columns[] = $column['Field'];
	}

	foreach ($data as $field => $value) {
		if (!in_array($field, $columns)) {
			$unset[$field] = $data[$field];
			unset($data[$field]);
			trigger_error($field, E_USER_ERROR);
		}
	}

	if (sizeof($unset) > 0) {
		$logMessage = "unknown column error: \nunknown columns: ";
		foreach ($unset as $key => $value) {
			$logMessage .= $key . ': `' . $value . '`, ';
		}
		$logMessage .= "\ntable:" . $table . "\ndata: " . print_r($data, true);
		ko_log('db_error_update', $logMessage);
	}

	$query = "INSERT INTO `$table` ";
	//Alle Daten setzen
	foreach($data as $key => $value) {
		$query1 .= "`$key`, ";
		if((string)$value == "NULL") {
			$query2 .= "NULL, ";
		} else {
			$query2 .= "'" . mysqli_real_escape_string($db_connection, stripslashes($value)) . "', ";
		}
	}
	$query .= "(" . substr($query1, 0, -2) . ") VALUES (" . substr($query2, 0, -2) . ")";

	if(defined('DEBUG_INSERT') && DEBUG_INSERT) $time_start = microtime(TRUE);
	$result = mysqli_query($db_connection, $query);
	if($result === FALSE) trigger_error('DB ERROR (db_insert_data): '.mysqli_errno($db_connection).': '.mysqli_error($db_connection).', QUERY: '.$query, E_USER_ERROR);
	if(defined('DEBUG_INSERT') && DEBUG_INSERT) {
		$DEBUG_db->queryCount++;
		$DEBUG_db->queries[] = array('time' => (microtime(TRUE)-$time_start)*1000, 'sql' => $query);
	}
	return mysqli_insert_id($db_connection);
}//db_insert_data()


/**
 * Update data in the database
 *
 * This should be used instead of issuing UPDATE queries directly
 *
 * @param string Table where the data should be stored
 * @param string WHERE statement that defines the rows to be updated
 * @param array Data array with the keys beeing the name of the db columns
 */
function db_update_data($table, $where, $data) {
	global $db_connection, $DEBUG_db;

	$columnsTemp = db_get_columns($table);
	$columns = array();
	$unset = array();

	foreach ($columnsTemp as $column) {
		$columns[] = $column['Field'];
	}

	foreach ($data as $field => $value) {
		if (!in_array($field, $columns)) {
			$unset[$field] = $data[$field];
			unset($data[$field]);
		}
	}

	if (sizeof($unset) > 0) {
		$logMessage = "unknown column error: \nunknown columns: ";
		foreach ($unset as $key => $value) {
			$logMessage .= $key . ': `' . $value . '`, ';
		}
		$logMessage .= "\ntable:" . $table . "\nwhere: " . $where . "\ndata: " . print_r($data, true);
		ko_log('db_error_update', $logMessage);
	}

	$found = FALSE;
	$query = "UPDATE $table SET ";
	//Alle Daten setzen
	foreach($data as $key => $value) {
		if(!$key) continue;
		$found = TRUE;
		if((string)$value == "NULL") {
			$query .= "`$key` = NULL, ";
		} else {
			$query .= "`$key` = '".mysqli_real_escape_string($db_connection, stripslashes($value))."', ";
		}
	}
	if(!$found) return FALSE;
	$query = substr($query, 0, -2);

	//WHERE-Bedingung
	$query .= " $where ";

	if(defined('DEBUG_UPDATE') && DEBUG_UPDATE) $time_start = microtime(TRUE);
	$result = mysqli_query($db_connection, $query);
	if($result === FALSE) trigger_error('DB ERROR (db_update_data): '.mysqli_errno($db_connection).': '.mysqli_error($db_connection).", QUERY: $query", E_USER_ERROR);
	if(defined('DEBUG_UPDATE') && DEBUG_UPDATE) {
		$DEBUG_db->queryCount++;
		$DEBUG_db->queries[] = array('time' => (microtime(TRUE)-$time_start)*1000, 'sql' => $query);
	}
}//db_update_data()


/**
  * Delete data from the database
	*
	* This should be used instead of issuing DELETE queries directly
	*
	* @param string Table where the data should be deleted
	* @param string WHERE statement that defines the rows to be deleted
	*/
function db_delete_data($table, $where) {
	global $db_connection, $DEBUG_db;

	$query = "DELETE FROM $table $where";
	if(defined('DEBUG_DELETE') && DEBUG_DELETE) $time_start = microtime(TRUE);
	$result = mysqli_query($db_connection, $query);
	if($result === FALSE) trigger_error('DB ERROR (db_delete_data): '.mysqli_errno($db_connection).': '.mysqli_error($db_connection).", QUERY: $query", E_USER_ERROR);
	if(defined('DEBUG_DELETE') && DEBUG_DELETE) {
		$DEBUG_db->queryCount++;
		$DEBUG_db->queries[] = array('time' => (microtime(TRUE)-$time_start)*1000, 'sql' => $query);
	}
}//db_delete_data()



/**
 * Get data from the database
 *
 * This should be used instead of issuing SELECT queries directly
 *
 * @param string Table where the data should be selected from
 * @param string WHERE statement that defines the rows to be selected
 * @param string Comma seperated value with the columns to be selected. * for all of them
 * @param string ORDER BY statement
 * @param string LIMIT statement
 * @param boolean Returns a single entry if set, otherwise an array of entries is returned with their ids as keys
 */
function db_select_data($table, $where="", $columns="*", $order="", $limit="", $single=FALSE, $no_index=FALSE) {
	global $db_connection, $DEBUG_db;

	if(ko_test(__FUNCTION__, func_get_args(), $testreturn) === TRUE) return $testreturn;

	//Spalten
	if(is_array($columns)) {
		foreach($columns as $col_i => $col) $columns[$col_i] = "`".$col."`";
		$columns = implode(",", $columns);
	}

	$query = "SELECT $columns FROM $table $where $order $limit";
	if(defined('DEBUG_SELECT') && DEBUG_SELECT) $time_start = microtime(TRUE);
	$result = mysqli_query($db_connection, $query);
	if($result === FALSE) trigger_error('DB ERROR (db_select_data): '.mysqli_errno($db_connection).': '.mysqli_error($db_connection). ' QUERY: '.$query, E_USER_ERROR);
	if(defined('DEBUG_SELECT') && DEBUG_SELECT) {
		$DEBUG_db->queryCount++;
		$DEBUG_db->queries[] = array('time' => (microtime(TRUE)-$time_start)*1000, 'sql' => $query);
	}
	if(mysqli_num_rows($result) == 0) {
		return $single ? NULL : array();
	} else if($single && mysqli_num_rows($result) == 1) {
		$return = mysqli_fetch_assoc($result);
		return $return;
	} else {
		if($no_index) {
			$index = '';
		} elseif(substr($columns, 0, 1) == '*' || FALSE !== strpos($columns, 'AS id') || in_array('id', explode(',', $columns)) || in_array('*', explode(',', $columns))) {
			$index = 'id';
		} else {
			$cols = explode(",", $columns);
			$index = trim(str_replace("`", "", $cols[0]));
		}
		$return = array();
		while($row = mysqli_fetch_assoc($result)) {
			if($index) {
				$return[$row[$index]] = $row;
			} else {
				$return[] = $row;
			}
		}
		return $return;
	}
}//db_select_data()


/**
 * @param $query: the whole sql query in one string
 * @param string $index: supply a field name that should be used to index the return array
 * @return array: the query result as an array, NULL if there are no matching entries
 */
function db_query($query, $index = '') {
	global $db_connection;
	// TODO: support testing
	$result = mysqli_query($db_connection, $query);
	if($result === FALSE) trigger_error('DB ERROR (db_select_data): '.mysqli_errno($db_connection).': '.mysqli_error($db_connection). ' QUERY: '.$query, E_USER_ERROR);
	if(mysqli_num_rows($result) == 0) {
		return;
	} else {
		$return = array();
		while($row = mysqli_fetch_assoc($result)) {
			if($index) {
				$return[$row[$index]] = $row;
			} else {
				$return[] = $row;
			}
		}
		return $return;
	}
}//db_query()



/**
	* Get the value of a single column
	*
	* @param string Table to select data from
	* @param string WHERE statement
	* @param string Name of column to get value for
	* @return mixed Value from database
	*/
function db_get_column($table, $where, $column, $split=" ") {
	if(is_int($where)) {
		$where = "WHERE `id` = '$where'";
	} else if(substr($where, 0, 5) == "WHERE") {
		$where = $where;
	} else return FALSE;

	//Allow several columns
	if(strstr($column, ",")) {
		$new = array();
		foreach(explode(",", $column) as $col) {
			$new[] = "`".$col."`";
		}
		$column = implode(",", $new);
	} else {
		$column = "`".$column."`";
	}

	$row = db_select_data($table, $where, $column, '', '', TRUE);
	return implode($split, $row);
}//db_get_column()


/**
  * Execute a distinct select
	*
	* @param string Table to get the data from
	* @param string Column to get the values from
	* @param string ORDER BY statement
	* @return array All the different values
	*/
function db_select_distinct($table, $col, $order_="", $where="", $case_sensitive=FALSE) {
	global $db_connection, $DEBUG_db;

	$r = array();

	$order = $order_ ? $order_ : "ORDER BY $col ASC";

	if($case_sensitive) $query = "SELECT DISTINCT BINARY $col AS $col FROM $table $where $order";
	else $query = "SELECT DISTINCT $col FROM $table $where $order";
	if(defined('DEBUG_SELECT') && DEBUG_SELECT) $time_start = microtime(TRUE);
	$result = mysqli_query($db_connection, $query);
	if($result === FALSE) trigger_error('DB ERROR (db_select_distinct): '.mysqli_errno($db_connection).': '.mysqli_error($db_connection).', QUERY: '.$query, E_USER_ERROR);
	if(defined('DEBUG_SELECT') && DEBUG_SELECT) {
		$DEBUG_db->queryCount++;
		$DEBUG_db->queries[] = array('time' => (microtime(TRUE)-$time_start)*1000, 'sql' => $query);
	}
	while($row = mysqli_fetch_assoc($result)) {
    $r[] = $row[ltrim(rtrim($col, '`'), '`')];
	}
	return $r;
}//db_select_distinct()


/**
  * Execute an alter table statement
	*
	* @param string Table to alter
	* @param string new value
	*/
function db_alter_table($table, $change) {
	global $db_connection;

	$query = "ALTER TABLE `$table` $change";
	$result = mysqli_query($db_connection, $query);
	if($result === FALSE) trigger_error('DB ERROR (db_alter_table): '.mysqli_errno($db_connection).': '.mysqli_error($db_connection).', QUERY: '.$query, E_USER_ERROR);
}//db_alter_table()


/**
  * Parses an SQL string and updates the kOOL-database accordingly
	*
	* Used in /install/index.php and the plugins
	*
	* @param string SQL statement of the entry to be
	*/
function db_import_sql($tobe) {
	global $db_connection;

	$create_code = 'CREATE TABLE `%s` (%s) ENGINE=MyISAM';
	$alter_code  = 'ALTER TABLE `%s` CHANGE `%s` %s';
	$add_code    = 'ALTER TABLE `%s` ADD %s';

	//find tables in actual db
	$is_tables = NULL;
	$result = mysqli_query($db_connection, "SHOW TABLES");
	while($row = mysqli_fetch_row($result)) {
		$is_tables[] = $row[0];
	}

	$table = "";
	foreach(explode("\n", $tobe) as $line) {

		$line = trim($line);


		//don't allow any destructive commands
		if(strstr(mb_strtoupper($line), "DROP ") || strstr(mb_strtoupper($line), "TRUNCATE ") || strstr(mb_strtoupper($line), "DELETE ")) {
			continue;
		}


		//INSERT Statement
		if(mb_strtoupper(substr($line, 0, 11)) == "INSERT INTO") {
			$do_sql[] = $line;
			continue;
		}


		//UPDATE Statement
		if(mb_strtoupper(substr($line, 0, 7)) == "UPDATE ") {
			$do_sql[] = $line;
			continue;
		}


		//ALTER Statement
		if(mb_strtoupper(substr($line, 0, 6)) == "ALTER ") {
    		$do_sql[] = $line;
    		continue;
    	}


		//start of a create table statement
		if(mb_strtoupper(substr($line, 0, 12)) == "CREATE TABLE") {
			//find table-name to be edited
			$temp = explode(" ", $line);
			$table = str_replace("`", "", trim($temp[2]));

			//find table in current db
			if(in_array($table, $is_tables)) {
				//table already exists - get table create definition
				$result = mysqli_query($db_connection, "SHOW CREATE TABLE $table");
				$row = mysqli_fetch_row($result);
				$is = $row[1];
				$new_table = FALSE;
			} else {
				//create table
				$is = array();
				$new_table = TRUE;
				$new_table_sql = "";
			}
			continue;
		}

		//end of create table
		else if(substr($line, 0, 1) == ")") {
			if($new_table_sql != "") {
				$do_sql[] = sprintf($create_code, $table, $new_table_sql);
			}
			$new_table_sql = ""; $new_table = FALSE;
			$table = "";
			continue;
		}

		else if(strstr($line, "KEY")) {
			if($new_table) {
				$new_table_sql .= $line;
			}
			continue;
		}

		//empty or comment line
		else if(substr($line, 0, 1) == "#" || substr($line, 0, 1) == "-" || $line == "") {
			continue;
		}

		//line inside of a create table statement
		else {
			if(!$table) continue;

			//find field name
			$temp = explode(" ", $line);
			$field = $temp[0];

			//check for this field in db
			$found = FALSE;
			foreach(explode("\n", $is) as $is_line) {
				$is_line = trim($is_line);
				$temp = explode(" ", $is_line);
				$is_field = $temp[0];
				if($is_field == $field) {
					//field found
					$found = TRUE;
					//change if not the same
					if($is_line != $line) {
						$do_sql[] = sprintf($alter_code, $table, str_replace("`", "", $field), $line);
					}
				}
			}//foreach(is as is_line)

			//add field if not found in existing table definition
			if(!$found) {
				if($new_table) {
					$new_table_sql .= $line;
				} else {
					$do_sql[] = sprintf($add_code, $table, $line);
				}
			}

		}//if..else(line == CREATE TABLE)
	}//foreach(tobe as line)

	//print_d($do_sql);
	//return;

	foreach($do_sql as $query) {
		if($query) {
			if(substr($query, -1) == ",") $query = substr($query, 0, -1);
			$result = mysqli_query($db_connection, $query);
			if($result === FALSE) trigger_error('DB ERROR (db_import_sql): '.mysqli_errno($db_connection).': '.mysqli_error($db_connection), E_USER_ERROR);
		}
	}
}//db_import_sql()




/**
 * Performs a fuzzy search in a db table
 * The search is performed by concatenating the db field values to a single string
 * and calculating the Levenshtein difference.
 * The best match is returned if it is in the given limit.
 *
 * @param array Data array with column names as indizes
 * @param string DB table
 * @param int Maximum allowed errors per db column
 * @param boolean Set to false to ignore the case
 * @param int Levenshtein limit which must be reached to treat the best find as a valuable find
 *
 * return array IDs of db entries with the best levenshtein difference
 */
function ko_fuzzy_search($data, $table, $error=1, $case=FALSE, $lev_limit="") {
	global $db_connection;
	//Get all DB columns
	foreach($data as $col => $value) {
		$cols[] = $col;
	}
	$num_cols = sizeof($cols);

	//Concatenate data to search for
	$orig = implode("", $data);
	$orig_length = strlen($orig);
	//Calculate limit for string length
	$limit = $num_cols*$error+1;

	//Calculate lev limit
	if(!$lev_limit) {
		$lev_limit = $limit;
		$lev_limit = $num_cols*$error-1;
	}

	//Only get db entries matching the total string length (+/- limit) of the original data
	$query  = "SELECT id, CONCAT(`".implode("`, `", $cols)."`) as teststring FROM `$table` ";
	$query .= "WHERE (CHAR_LENGTH(`".implode("`)+CHAR_LENGTH(`", $cols)."`)) > ".($orig_length-$limit)." ";
	$query .= "AND (CHAR_LENGTH(`".implode("`)+CHAR_LENGTH(`", $cols)."`)) < ".($orig_length+$limit)." ";
	$query .= "AND deleted = '0'";

	//Find the best matching db entry
	$result = mysqli_query($db_connection, $query);
	$best = 100;
	while($row = mysqli_fetch_assoc($result)) {
		if($case) {
			$lev = levenshtein($orig, $row["teststring"]);
		} else {
			$lev = levenshtein(mb_strtolower($orig), mb_strtolower($row["teststring"]));
		}
		if($lev <= $best) {
			$found[$lev][] = $row["id"];
			$best = $lev;
		}
	}

	//Return ID if levenshtein difference is smaller than limit
	if($best <= $lev_limit) {
		return $found[$best];
	} else {
		return FALSE;
	}
}//ko_fuzzy_search()


/************************************************************************************************************************
 *                                                                                                                      *
 * Export-FUNKTIONEN                                                                                                    *
 *                                                                                                                      *
 ************************************************************************************************************************/

/**
 * Creates an XLSX file
 * Based upon PHPExcel (http://phpexcel.codeplex.com/)
 *
 * @param array header: Array holding the header row's cells
 * @param array data: Two dimensional array holding the cell's values
 * @param string filename: Filename two use for the xls file
 * @param string title: Title for the worksheet
 * @param string format: landscape or portrait
 * @param array wrap: Array with column number as key if this column's values should be wrapped
 * @param array formatting: Array containing formatting information
 * @return string the modified filename
 */
function ko_export_to_xlsx($header, $data, $filename, $title = '', $format="landscape", $wrap=array(), $formatting=array(), $linebreak_columns=array()) {
    global $ko_path;
	if($title == '') {
		$title = 'kOOL';
	} else {
		$title = format_userinput($title, 'alphanum');
	}
    $person = ko_get_logged_in_person();
    $xls_default_font = ko_get_setting('xls_default_font');
    $name = $person['vorname'] . ' ' . $person['nachname'];

    $objPHPExcel = new PHPExcel();
    $objPHPExcel->getProperties()->setCreator($name);
    $objPHPExcel->getProperties()->setLastModifiedBy($name);
    $objPHPExcel->getProperties()->setTitle($title);
    $objPHPExcel->getProperties()->setSubject('kOOL-Export');
    $objPHPExcel->getProperties()->setDescription('');


    // Add some data
    $sheet = $objPHPExcel->setActiveSheetIndex(0);
    $sheet->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
    if ($format == 'landscape') {
        $sheet->getPageSetup()->setOrientation(
            PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE
        );
    } else {
        $sheet->getPageSetup()->setOrientation(
            PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT
        );
    }

    if ($xls_default_font) {
        $sheet->getDefaultStyle()->getFont()->setName($xls_default_font);
    } else {
        $sheet->getDefaultStyle()->getFont()->setName('Arial');
    }


    switch (ko_get_setting('xls_title_color')) {
        case 'blue':
            $colorName = PHPExcel_Style_Color::COLOR_BLUE;
            break;
        case 'cyan':
            $colorName = 'FF00FFFF';
            break;
        case 'brown':
            $colorName = 'FFA52A2A';
            break;
        case 'magenta':
            $colorName = 'FFFF00FF';
            break;
        case 'grey':
            $colorName = 'FF808080';
            break;
        case 'green':
            $colorName = PHPExcel_Style_Color::COLOR_GREEN;
            break;
        case 'orange':
            $colorName = 'FFFFA500';
            break;
        case 'purple':
            $colorName = 'FF800080';
            break;
        case 'red':
            $colorName = PHPExcel_Style_Color::COLOR_RED;
            break;
        case 'yellow':
            $colorName = PHPExcel_Style_Color::COLOR_YELLOW;
            break;
        case 'black':
        default:
            $colorName = PHPExcel_Style_Color::COLOR_BLACK;
    }

    $xlsHeaderFormat = array(
        'font' => array(
            'bold' => ko_get_setting('xls_title_bold') ? true : false,
            'color' => array('argb' => $colorName),
            'name' => ko_get_setting('xls_title_font')
        ),
    );

    $xlsTitleFormat = array(
        'font' => array(
            'bold' => ko_get_setting('xls_title_bold') ? true : false,
            'size' => 12,
            'name' => ko_get_setting('xls_title_font')
        )
    );

    $xlsSubtitleFormat = array(
        'font' => array(
            'bold' => ko_get_setting('xls_title_bold') ? true : false,
            'name' => ko_get_setting('xls_title_font')
        )
    );

    $row = 1;
    $col = 0;
	$manual_linebreaks = false;
    //Add header
    if(is_array($header)) {
        if(isset($header['header'])) {
            //Add title
            if($header['title']) {
                $sheet->getStyleByColumnAndRow(0, $row)->applyFromArray($xlsTitleFormat);
                $sheet->setCellValueByColumnAndRow(0, $row++, $header['title']);
            }
            //Add subtitle
            if(is_array($header['subtitle']) && sizeof($header['subtitle']) > 0) {
                foreach($header['subtitle'] as $k => $v) {
                    if(substr($k, -1) != ':') {
                        $k .= ':';
                    }
                    $sheet->getStyleByColumnAndRow(0, $row)->applyFromArray($xlsSubtitleFormat);
                    $sheet->setCellValueByColumnAndRow(0, $row, $k);
                    $sheet->setCellValueByColumnAndRow(1, $row++, $v);
                }
            } else if($header['subtitle']) {
                $sheet->getStyleByColumnAndRow(0, $row)->applyFromArray($xlsHeaderFormat);
                $sheet->setCellValueByColumnAndRow(0, $row++, $header['subtitle']);
            }
            $row++;
            //Add column headers
            $col = 0;
            foreach($header['header'] as $h) {
                $sheet->getStyleByColumnAndRow($col, $row)->applyFromArray($xlsHeaderFormat);
                $sheet->setCellValueByColumnAndRow($col++, $row, ko_unhtml($h));
            }
            $row++;
        } else {
            if(is_array($header[0])) {
                foreach($header as $r) {
                    $col = 0;
                    foreach($r as $h) {
                        $sheet->getStyleByColumnAndRow($col, $row)->applyFromArray($xlsHeaderFormat);
                        $sheet->setCellValueByColumnAndRow($col++, $row, ko_unhtml($h));
                    }
                    $row++;
                }
            } else {
				$manual_linebreaks = true;
                foreach($header as $h) {
                    $sheet->getStyleByColumnAndRow($col, $row)->applyFromArray($xlsHeaderFormat);
                    $sheet->setCellValueByColumnAndRow($col++, $row, ko_unhtml($h));
					// add linebreak if the current column is set as a linebreak-column
					if (in_array($h, $linebreak_columns)) {
						$row++;
						$col = 1;
					}
                }
                $row++;
            }
        }
    }

    //Daten
    foreach($data as $dd) {
        $col=0;
        foreach($dd as $k => $d) {
            if($wrap[$col] == TRUE) {
                $sheet->getStyleByColumnAndRow($col, $row)->getAlignment()->setWrapText(true);
                $sheet->setCellValueByColumnAndRow($col++, $row, strip_tags(ko_unhtml($d)));
            } else {
                //Set format of cell according to formatting definition
                if (isset($formatting['cells'][($row - 1) . ':' . $col])) {
                    switch ($formatting['cells'][($row - 1) . ':' . $col]) {
                        case 'bold':
                            $sheet->getStyleByColumnAndRow($col, $row)->getFont()->setBold(true);
                            break;
                        case 'italic':
                            $sheet->getStyleByColumnAndRow($col, $row)->getFont()->setItalic(true);
                            break;
                    }
                } else if(isset($formatting['rows'][($row - 1)])) {
                    switch ($formatting['rows'][($row - 1)]) {
                        case 'bold':
                            $sheet->getStyleByColumnAndRow($col, $row)->getFont()->setBold(true);
                            break;
                        case 'italic':
                            $sheet->getStyleByColumnAndRow($col, $row)->getFont()->setItalic(true);
                            break;
                    }
                } else {
                    $sheet->getStyleByColumnAndRow($col, $row)->getFont()
                        ->setItalic(false)
                        ->setBold(false);
                }

                $sheet->setCellValueByColumnAndRow($col++, $row, strip_tags(ko_unhtml($d)));
            }
			// set manual linebreak if required
			if ($manual_linebreaks) {
				if (in_array($header[$k], $linebreak_columns)) {
					$row ++;
					$col = 1;
				}
			}
        }
        $row++;
    }
    // Rename sheet
    $objPHPExcel->getActiveSheet()->setTitle($title);


    // Save Excel file
    $format = 'xlsx';
    if (isset($_SESSION['ses_userid'])) {
        $format = ko_get_userpref($_SESSION['ses_userid'], 'export_table_format');
    }

    $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
    if ($format == 'xls') {
        $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
        if (substr($filename, -1) == 'x') {
            $filename = substr($filename, 0, -1);
        }
    }

    $objWriter->save($filename);

    return $filename;
}//ko_export_to_xlsx()





/**
 * Creates an XLS file
 * Based upon php_writeexcel (http://www.bettina-attack.de/jonny/view.php/projects/php_writeexcel)
 *
 * @param array header: Array holding the header row's cells
 * @param array data: Two dimensional array holding the cell's values
 * @param string filename: Filename two use for the xls file
 * @param string title: Title for the worksheet
 * @param string format: landscape or portrait
 * @param array wrap: Array with column number as key if this column's values should be wrapped
 * @param array formatting: Array containing formatting information
 */
function ko_export_to_excel($header, $data, $filename, $title, $format="landscape", $wrap=array(), $formatting=array()) {
	global $ko_path;

	require_once($ko_path.'inc/class.excelwriter.php');

	$workbook = new writeexcel_workbook($filename);
	$worksheet =& $workbook->addworksheet(substr($title, 0, 30));
	if($format == "landscape") $worksheet->set_landscape();
	else $worksheet->set_portrait();

	//set encoding
	//$worksheet->setInputEncoding('ISO-8859-1');

  $col = $row = 0;

	//Define formats
	$xls_default_font = ko_get_setting('xls_default_font');
	$xls_title_font = ko_get_setting('xls_title_font');
	$xls_title_bold = ko_get_setting('xls_title_bold');
	$xls_title_color = ko_get_setting('xls_title_color');

	$format_header =& $workbook->addformat(array('bold' => $xls_title_bold, 'color' => $xls_title_color, 'font' => $xls_title_font));
	$format_title =& $workbook->addformat(array('bold' => $xls_title_bold, 'size' => '12', 'font' => $xls_title_font));
	$format_subtitle =& $workbook->addformat(array('bold' => $xls_title_bold, 'font' => $xls_default_font));
	$format_wrap =& $workbook->addformat(array('text_wrap' => 1, 'font' => $xls_default_font));
	$format_default =& $workbook->addformat(array('font' => $xls_default_font));

	//Create formats given in formatting array
	foreach($formatting['formats'] as $f => $format) {
		${'f_'.$f} =& $workbook->addformat($format);
	}

	//Add header
	if(is_array($header)) {
		if(isset($header['header'])) {
			//Add title
			if($header['title']) {
				$worksheet->write($row++, 0, $header['title'], $format_title);
			}
			//Add subtitle
			if(is_array($header['subtitle']) && sizeof($header['subtitle']) > 0) {
				foreach($header['subtitle'] as $k => $v) {
					if(substr($k, -1) != ':') $k .= ':';
					$worksheet->write($row, 0, $k, $format_subtitle);
					$worksheet->write($row++, 1, $v, $format_default);
				}
			} else if($header['subtitle']) {
				$worksheet->write($row++, 0, $header['subtitle'], $format_subtitle);
			}
			$row++;
			//Add column headers
			$col = 0;
			foreach($header['header'] as $h) {
				$worksheet->write($row, $col++, ko_unhtml($h), $format_header);
			}
			$row++;
		}
		else {
			if(is_array($header[0])) {
				foreach($header as $r) {
					$col = 0;
					foreach($r as $h) {
						$worksheet->write($row, $col++, ko_unhtml($h), $format_header);
					}
					$row++;
				}
			} else {
				foreach($header as $h) {
					$worksheet->write($row, $col++, ko_unhtml($h), $format_header);
				}
				$row++;
			}
		}
	}

	//Daten
	foreach($data as $dd) {
		$col=0;
		foreach($dd as $d) {
			if($wrap[$col] == TRUE) {
				$worksheet->write($row, $col++, strip_tags(ko_unhtml($d)), $format_wrap);
			} else {
				//Set format of cell according to formatting definition
				if(isset($formatting['cells'][$row.':'.$col])) $format =& ${'f_'.$formatting['cells'][$row.':'.$col]};
				else if(isset($formatting['rows'][$row])) $format =& ${'f_'.$formatting['rows'][$row]};
				else $format =& $format_default;

				$worksheet->write($row, $col++, strip_tags(ko_unhtml($d)), $format);
			}
		}
		$row++;
	}
	$workbook->close();
	unset($workbook);

}//ko_export_to_excel()





function ko_export_to_csv($header, $data, $filename) {
  $fp = fopen($filename, 'w');
  fputcsv($fp, $header);
  foreach($data as $row) {
    fputcsv($fp, $row);
  }
  fclose($fp);
}//ko_export_to_csv()





function ko_export_to_pdf($layout, $data, $filename) {
	global $ko_path;

	//PDF starten
	define('FPDF_FONTPATH',$ko_path.'fpdf/schriften/');
	require($ko_path.'fpdf/pdf_leute.php');
	$pdf = new PDF_leute($layout["page"]["orientation"], 'mm', 'A4');
  $pdf->Open();
	$pdf->layout = $layout;
	$pdf->SetAutoPageBreak(true, $layout["page"]["margin_bottom"]);

	//Find fonts actually used in this document
	$used_fonts = array();
	foreach(array("header", "footer") as $i) {
		foreach(array("left", "center", "right") as $j) {
			$used_fonts[] = $layout[$i][$j]["font"];
		}
	}
	$used_fonts[] = $layout["headerrow"]["font"];
	$used_fonts[] = $layout["col_template"]["_default"]["font"];
	$used_fonts = array_unique($used_fonts);
	//Add fonts
	$fonts = ko_get_pdf_fonts();
	foreach($fonts as $font) {
		if(!in_array($font["id"], $used_fonts)) continue;
		$pdf->AddFont($font["id"], '', $font["file"]);
	}

	//Set borders from layout (if defined)
	if(array_key_exists('borders', $layout)) {
		$pdf->border($layout['borders']);
	} else {
		$pdf->border(TRUE);
	}
	if(array_key_exists('cellBorders', $layout)) {
		$pdf->SetCellBorders(mb_strtoupper($layout['cellBorders']));
	}

	$pdf->SetMargins($layout["page"]["margin_left"], $layout["page"]["margin_top"], $layout["page"]["margin_right"]);

	//Prepare replacement-array for header and footer
	$map["[[Day]]"] = strftime("%d", time());
	$map["[[Month]]"] = strftime("%m", time());
	$map["[[MonthName]]"] = strftime("%B", time());
	$map["[[Year]]"] = strftime("%Y", time());
	$map["[[Hour]]"] = strftime("%H", time());
	$map["[[Minute]]"] = strftime("%M", time());
	$map["[[kOOL-URL]]"] = $BASE_URL;
	$pdf->header_map = $map;


	for($i = 0; $i < 2; $i++) {

		//First loop: Gather string widths for whole table
		if($i == 0) {
			$find_widths = true;

			//Add header titles
			$string_widths = array();
			$colcounter = 0;
			$pdf->SetFont($pdf->layout["headerrow"]["font"], "", $pdf->layout["headerrow"]["fontsize"]);
			foreach($pdf->layout["columns"] as $colName) {
				$string_widths[$colcounter][] = $pdf->getStringWidth($colName);
				$headerwidth[$colcounter] = $pdf->getStringWidth($colName);
				$colcounter++;
			}
		}

		//Second loop: Use string widths to calculate columns widths for table
		else {
			//Calculate column widths for all columns
			foreach($string_widths as $col => $values) {
				$num = $sum = $max = 0;
				foreach($values as $value) {
					if($value == 0) continue;
					$sum += $value;
					$num++;
					$max = max($max, $value);
				}
				$averages[$col] = $sum/$num;
				$maxs[$col] = $max;
			}

			//Find total width of full text
			$page_width = $pdf->w-$layout["page"]["margin_left"]-$layout["page"]["margin_right"];
			$maxwidth = $page_width/3;
			//Don't let a single column use more than a third of the page width
			foreach($averages as $col => $width) {
				if($width > $maxwidth) $averages[$col] = $maxwidth;
				$maxs[$col] = min($maxs[$col], $maxwidth);
			}
			//Keep a minimum column width of 10mm
			$minwidth = 10;
			foreach($averages as $col => $width) {
				if($width < $minwidth) $averages[$col] = $minwidth;
			}

			$total_width = 0;
			foreach($averages as $col => $width) $total_width += $width;

			//Use space to enlarge the columns where the header is wider than the column
			if($total_width < $page_width) {
				$total_need = 0; $need = array();
				//Find needs for all columns
				foreach($averages as $col => $width) {
					if($width < $headerwidth[$col]) {
						$need[$col] = $headerwidth[$col]-$width;
						$total_need += $need[$col];
					}
				}
				$need_factor = ($page_width-$total_width) / $total_need;
				foreach($averages as $col => $value) {
					if($need[$col]) {
						//Only grow the row to the width of the headertext
						$new_max = $value + $need_factor*$need[$col];
						$averages[$col] = min($headerwidth[$col], $new_max);
					}
				}
			}

			//Use space to enlarge the columns where the content is wider than the column width
			if($total_width < $page_width) {
				$total_need = 0; $need = array();
				//Find needs for all columns
				foreach($averages as $col => $width) {
					if($width < $maxs[$col]) {
						$need[$col] = $maxs[$col]-$width;
						$total_need += $need[$col];
					}
				}
				$need_factor = ($page_width-$total_width) / $total_need;
				foreach($averages as $col => $value) {
					if($need[$col]) {
						//Only grow the row to the width of the headertext
						$new_max = $value + $need_factor*$need[$col];
						$averages[$col] = min($maxs[$col], $new_max);
					}
				}
			}

			$total_width = 0;
			foreach($averages as $col => $width) $total_width += $width;

			//Get scaling factor
			$factor = $page_width / $total_width;

			//Calculate single widths for all columns
			$widths = array();
			foreach($averages as $value) {
				$widths[] = $factor*$value;
			}
			$pdf->SetWidths($widths);

			$pdf->AddPage();
			$find_widths = false;
		}


		//Loop all addresses
		foreach($data as $row) {
			//Layout for normal content
			$pdf->SetFont($layout["col_template"]["_default"]["font"], "", $layout["col_template"]["_default"]["fontsize"]);


			if($find_widths) {
				//Store width for width calculation
				foreach($row as $key => $value) $string_widths[$key][] = $pdf->getStringWidth($value);
			} else {
				//Save this row in pdf
				$pdf->SetZeilenhoehe($layout["col_template"]["_default"]["fontsize"]/2);
				if(is_array($layout['col_template']['_default']['aligns'])) {
					$pdf->SetAligns($layout['col_template']['_default']['aligns']);
				}
				$pdf->Row($row);
			}
		}//foreach(data)

	}//for(i=0..2)

	$pdf->Output($filename);

}//ko_export_to_pdf()





/**
 * Merges several PDF files into one. Uses shell command gs
 *
 * @param $files array holding the files
 * @param $output string Filename used for output
 */
function ko_merge_pdf_files($files, $output) {
	$cmd = "gs -q -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -sOutputFile=$output ".implode(' ', $files);
	$result = shell_exec($cmd);
}//ko_merge_pdf_files()


/**
 * @param FPDF $pdf      a FPDF object, that can calculate the width of a string
 * @param $width         the maximum width of the resulting string (in size $size)
 * @param $text          the text that should fit in the supplied $width             -> REF
 * @param $time          a time that should fit in the supplied $width               -> REF
 * @param $size          the maximum fontsize (in pt)                                -> REF
 * @param int $minSize   the minimum fontsize (in pt)
 * @param int $mode      mode 0:
 *                            handles time and text, $size is resuced until $time fits, then $text is shortened
 *                            till it fits
 *                       mode 1:
 *                            handles only text, is shortened until it fits $width, $size is not modified
 * @return bool          returns false if the supplied $time can't fit into the supplied $width
 */
function ko_get_fitting_text_width(FPDF $pdf, $width, &$text, &$time, &$size, $minSize= 6, $mode=0) {
	$tempSize = $pdf->FontSize;
	$pdf->SetFontSize($size);
	if ($pdf->GetStringWidth($text) > $width || $pdf->GetStringWidth($time) > $width) {

		if ($mode == 0) {
			// shorten $time if possible
			$newTime = (substr($time, 0, 1) == '0' ? substr($time, 1) : $time);
			$shortTime = ($newTime != $time);
			$looping = true;

			// reduce $size until $time (shortened, if possible) fits into $width
			while ($pdf->GetStringWidth($time) > $width && $looping && $size >= $minSize) {
				if ($shortTime && $pdf->GetStringWidth($newTime) <= $width) {
					$time = $newTime;
					$looping = false;
				}
				else {
					$pdf->SetFontSize(--$size);
				}
			}
		}

		// shorten $text to make it fit into $width
		$repr = $text;
		while ($pdf->GetStringWidth($text) > $width && $text != '..') {
			if (substr($repr, strlen($repr) - 4, strlen($repr)) == '@..@') {
				$rTemp = substr($repr, 0, strlen($repr) - 5);
				$tTemp = substr($text, 0, strlen($text) - 3);

				$text = $tTemp . '..';
				$repr = $rTemp . '@..@';
			}
			else {
				$rTemp = substr($repr, 0, strlen($repr) - 1);
				$tTemp = substr($text, 0, strlen($text) - 1);

				$text = $tTemp . '..';
				$repr = $rTemp . '@..@';
			}
		}
		if ($text == '..') $text = '';
	}

	// restore fontsize
	$pdf->SetFontSize($tempSize);
	if ($size < $minSize) return false;
	else return true;
}

function ko_has_time_format($t) {
	$pattern = '/^([0-1][0-9]|2[0-4]):[0-5][0-9]$/';
	return preg_match($pattern, $t);
}



/**
 * Creates a weekly calendar as PDF export (used for reservations and events)
 */
function ko_export_cal_weekly_view($module, $_size='', $_start='', $pages='') {
	global $ko_path, $BASE_PATH, $BASE_URL, $DATETIME;

	$_start = $_start != '' ? $_start : date('Y-m-d', mktime(1,1,1, $_SESSION['cal_monat'], $_SESSION['cal_tag'], $_SESSION['cal_jahr']));

	if($module == 'daten') {
		ko_get_eventgruppen($items);
		$planSize = $_size != '' ? $_size : ko_get_userpref($_SESSION['ses_userid'], 'daten_pdf_week_length');
		if($planSize == 1) {
			$weekday = 1;
			$filename = getLL('daten_filename_pdf').strftime('%d%m%Y', mktime(1,1,1, $_SESSION['cal_monat'], $_SESSION['cal_tag'], $_SESSION['cal_jahr'])).'_'.strftime('%H%M%S', time()).'.pdf';
		} else {
			$weekday = ko_get_userpref($_SESSION['ses_userid'], 'daten_pdf_week_start');
			$filename = getLL('daten_filename_pdf').strftime('%d%m%Y_%H%M%S', time()).'.pdf';
		}
		$show_legend = ko_get_userpref($_SESSION['ses_userid'], 'daten_export_show_legend') == 1;
	} else {
		ko_get_resitems($items);
		$planSize = $_size != '' ? $_size : ko_get_userpref($_SESSION['ses_userid'], 'res_pdf_week_length');
		if($planSize == 1) {
			$weekday = 1;
			$filename = getLL('res_filename_pdf').strftime('%d%m%Y', mktime(1,1,1, $_SESSION['cal_monat'], $_SESSION['cal_tag'], $_SESSION['cal_jahr'])).'_'.strftime('%H%M%S', time()).'.pdf';
		} else {
			$weekday = ko_get_userpref($_SESSION['ses_userid'], 'res_pdf_week_start');
			$filename = getLL('res_filename_pdf').strftime('%d%m%Y_%H%M%S', time()).'.pdf';
		}
		$show_persondata = $_SESSION['ses_userid'] != ko_get_guest_id() || ko_get_setting('res_show_persondata') == 1;
		$show_purpose = $_SESSION['ses_userid'] != ko_get_guest_id() || ko_get_setting('res_show_purpose') == 1;
		$show_legend = ko_get_userpref($_SESSION['ses_userid'], 'res_export_show_legend') == 1;
	}
	if($weekday == 0) $weekday = 7;
	if(!$planSize) $planSize = 7;


	$startDate = add2date($_start, 'day', $weekday-1, TRUE);
	$startStamp = strtotime($startDate);
	$endStamp  = strtotime('+'.($planSize-1).' day', $startStamp);

	$maxHours = ko_get_userpref($_SESSION['ses_userid'], 'cal_woche_end') - ko_get_userpref($_SESSION['ses_userid'], 'cal_woche_start');
	$startHour = ko_get_userpref($_SESSION['ses_userid'], 'cal_woche_start')-1;

	$HourTitleWidth = 4;

	//Prepare PDF file
	define('FPDF_FONTPATH', $BASE_PATH.'fpdf/schriften/');
	require_once($BASE_PATH.'fpdf/mc_table.php');

	$pdf = new PDF_MC_Table('L', 'mm', 'A4');
	$pdf->Open();
	$pdf->SetAutoPageBreak(true, 1);
	$pdf->SetMargins(5, 25, 5);  //left, top, right
	if(file_exists($ko_path.'fpdf/schriften/DejaVuSansCondensed.php')) {
		$pdf->AddFont('fontn', '', 'DejaVuSansCondensed.php');
	} else {
		$pdf->AddFont('fontn', '', 'arial.php');
	}
	if(file_exists($ko_path.'fpdf/schriften/DejaVuSansCondensed-Bold.php')) {
		$pdf->AddFont('fontb', '', 'DejaVuSansCondensed-Bold.php');
	} else {
		$pdf->AddFont('fontb', '', 'arialb.php');
	}


	$pageCounter = 1;
	if ($pages == '') $pages = 1;
	for ($pageCounter = 1; $pageCounter <= $pages; $pageCounter++) {

		$pdf->SetTextColor(0,0,0);

		$pdf->AddPage();
		$pdf->SetLineWidth(0.1);

		$top = 18;
		$left = 5;

		//Title
		$pdf->SetFont('fontb', '', 11);
		$m = strftime('%B', $startStamp) == strftime('%B', $endStamp) ? '' : strftime(' %B ', $startStamp);
		$y = strftime('%Y', $startStamp) == strftime('%Y', $endStamp) ? '' : strftime('%Y', $startStamp);

		if($planSize == 1) {
			$pdf->Text($left, $top-6, getLL('module_'.$module).strftime(' - %d. %B %Y', $endStamp));
		} else {
			$pdf->Text($left, $top-6, getLL('module_'.$module).strftime(' %d.', $startStamp).$m.$y.strftime(' - %d. %B %Y', $endStamp));
		}

		//Add logo in header (only if legend is not to be shown)
		$logo = ko_get_pdf_logo();
		if($logo != '' && !$show_legend) {
			$pic = getimagesize($BASE_PATH.'my_images'.'/'.$logo);
			$picWidth = 9 / $pic[1] * $pic[0];
			$pdf->Image($BASE_PATH.'my_images'.'/'.$logo , 290-$picWidth, 5, $picWidth);
		}

		//footer right
		$pdf->SetFont('fontn', '', 8);
		$person = ko_get_logged_in_person();
		$creator = $person['vorname'] ? $person['vorname'].' '.$person['nachname'] : $_SESSION['ses_username'];
		$footerRight = sprintf(getLL('tracking_export_label_created'), strftime($DATETIME['dmY'].' %H:%M', time()), $creator);
		$pdf->Text(291 - $pdf->GetStringWidth($footerRight), 202, $footerRight );

		//footer left
		$pdf->Text($left, 202, $BASE_URL);

		//get some measures
		$hourHeight = floor((180/$maxHours)*10)/10;
		$dayWidth = floor((286/$planSize)*10)/10;

		//Go through all days
		$legend = array();
		$index = 0;
		while($index < $planSize) {
			$index++;
			// draw title of the Day
			$pdf->SetFillColor(33, 66, 99);
			$pdf->SetDrawColor(255);
			$pdf->Rect($left, $top-4, $dayWidth, 4, 'FD');

			//Get current date information
			$currentStamp = strtotime('+'.($index-1).' day', $startStamp);
			$day = strftime('%d', $currentStamp);
			$month = strftime('%m', $currentStamp);
			$year = strftime('%Y', $currentStamp);
			$weekday = strftime('%u', $currentStamp);


			if($dayWidth < 17) {
				$title = strftime('%d', $currentStamp).'.';
			} else {
				$title = strftime(($dayWidth>24 ? '%A' : '%a').', %d.%m.', $currentStamp);
			}
			$pdf->SetFont('fontb', '', 7);
			$pdf->SetTextColor(255, 255, 255);
			$pdf->Text($left+$dayWidth/2-$pdf->GetStringWidth($title)/2, $top-1, $title);

			// draw frame of the day
			$pdf->SetDrawColor(180);
			$pdf->Rect($left, $top, $dayWidth, $hourHeight * $maxHours, 'D');

			// draw frame of each day
			$pos = $top;
			//find 12th hour
			$twelve = 12 - $startHour;
			for($i=1; $i<=$maxHours; $i++) {
				// Box for each hour
				if($weekday == 7 && $planSize > 1) {  //sunday
					$fillColor = $i == $twelve ? 180 : 210;
					$fillMode = 'DF';
				} else if ($weekday == 6 && $planSize > 1)  {  //saturday
					$fillColor = $i == $twelve ? 210 : 230;
					$fillMode = 'DF';
				} else {
					$fillColor = 210;
					$fillMode = $i == $twelve ? 'DF' : 'D';
				}
				$pdf->SetFillColor($fillColor);
				$pdf->Rect($left, $pos, $dayWidth, $hourHeight, $fillMode);

				// draw the hours
				$pdf->SetFont('fontn', '', 7);
				$pdf->SetTextColor(80);
				$actTime = strtotime('+'.$startHour.' hours', $startStamp);
				$hourTitle = strftime('%H', strtotime('+'.$i.' hours', $actTime));
				$cPos = ($HourTitleWidth - $pdf->GetStringWidth($hourTitle))/2;
				$pdf->Text($left+$cPos, $pos+3, $hourTitle);

				//Go to next day
				$pos = $pos+$hourHeight;
			}

			// get the events for the current day
			$date = "$year-$month-$day";
			$where = "WHERE (`startdatum` <= '$date' AND `enddatum` >= '$date')";

			if($module == 'daten') {
				$table = 'ko_event';
				$where .= sizeof($_SESSION['show_tg']) > 0 ? " AND `eventgruppen_id` IN ('".implode("','", $_SESSION['show_tg'])."') " : ' AND 1=2 ';
			} else {
				$table = 'ko_reservation';
				$where .= sizeof($_SESSION['show_items']) > 0 ? " AND `item_id` IN ('".implode("','", $_SESSION['show_items'])."') " : ' AND 1=2 ';
			}

			//Add kota filter
			$kota_where = kota_apply_filter($table);
			if($kota_where != '') $where .= " AND ($kota_where) ";

			$eventArr = db_select_data($table, $where, '*, TIMEDIFF( CONCAT(enddatum," ",endzeit), CONCAT(startdatum," ",startzeit)) AS duration ', 'ORDER BY duration DESC');

			//Correct $eventArr in relation to events starting and / or ending outside of the choosen timeframe and add corners
			$sort = array();
			foreach($eventArr as $ev) {
				$id = $ev['id'];

				//Set endtime to midnight for all day events
				if($ev['startzeit'] == '00:00:00' && $ev['endzeit'] == '00:00:00') {
					$ev['endzeit'] = '23:59:59';
				}
				if($ev['endzeit'] == '24:00:00') $ev['endzeit'] = '23:59:59';
				if($ev['startzeit'] == '24:00:00') $ev['startzeit'] = '23:59:59';

				$eventArr[$id]['startMin'] = substr($ev['startzeit'],0,2)*60 + substr($ev['startzeit'], 3, 2);
				$eventArr[$id]['stopMin'] = substr($ev['endzeit'],0,2)*60 + substr($ev['endzeit'], 3, 2);
				$eventStart = strtotime($ev['startdatum'].' '.$ev['startzeit']);
				$eventEnd = strtotime($ev['enddatum'].' '.$ev['endzeit']);

				$calStart = mktime($startHour+1, 0, 0, $month, $day, $year);
				$calEnd = mktime($startHour+1+$maxHours, 0, 0, $month, $day, $year);

				//Set color
				if($module == 'daten') {
					$eventArr[$id]['eventgruppen_farbe'] = $items[$ev['eventgruppen_id']]['farbe'];
					ko_set_event_color($eventArr[$id]);
				} else {
					$eventArr[$id]['eventgruppen_farbe'] = $items[$ev['item_id']]['farbe'];
				}

				//Check start: Inside or outside of displayed time frame
				if($eventStart < $calStart) {
					$eventArr[$id]['startMin'] = 1;
				} else if($eventStart > $calEnd) {
					continue;
				} else {
					$eventArr[$id]['startMin'] = $eventArr[$id]['startMin'] - ($startHour+1) * 60;
					$eventArr[$id]['roundedCorners'] = '12';
				}

				//Check end: Inside or outside of displayed time frame
				if($eventEnd <= $calStart) {
					continue;
				} else if($eventEnd > $calEnd) {
					$eventArr[$id]['stopMin'] = $maxHours * 60;
				} else {
					$eventArr[$id]['stopMin'] = $eventArr[$id]['stopMin']-($startHour+1)*60;
					$eventArr[$id]['roundedCorners'] .= '34';
				}

				$eventArr[$id]['duration'] = $eventArr[$id]['stopMin'] - $eventArr[$id]['startMin'];
				$sort[$id] = $eventArr[$id]['stopMin'] - $eventArr[$id]['startMin'];
			}//foreach(eventArr as ev)

			//Sort for duration
			arsort($sort);
			$new = array();
			foreach($sort as $id => $d) {
				$new[$id] = $eventArr[$id];
			}
			$eventArr = $new;
			unset($sort);
			unset($new);

			//create matrix to diplay used columns.
			$colMatrix = array();
			$eventColPosition = array();
			foreach($eventArr as $ev){
				//check if column free
				$col = 1;
				for($min = $ev['startMin']; $min<$ev['stopMin']; $min++) {
					if($colMatrix[$min][$col]['pos']) $col++;
				}

				//mark full columns
				for($min = $ev['startMin']; $min<$ev['stopMin']; $min++) {
					$colMatrix[$min][$col]['pos']= $ev['id'];
					//array to store columnposition for certain event
					$eventColPosition[$ev['id']] = $col;
				}
			}

			//find stripewidth for the day
			$maxColumnCnt = 1;
			foreach($colMatrix as $min) {
				$maxColumnCnt = max($maxColumnCnt, count($min));
			}
			$stripeWidth = ($dayWidth - $HourTitleWidth ) / $maxColumnCnt;

			//loop through the events of this day to draw them
			foreach($eventArr as $currEvent) {
				$eventStart = intval(str_replace('-', '', $currEvent['startdatum']));
				$eventEnd = intval(str_replace('-', '', $currEvent['enddatum']));
				$durationDays = $eventEnd - $eventStart;

				if(($currEvent['duration'] <= 0) && ($durationDays <= 0)) continue;

				//Event group or res item
				$item = $module == 'daten' ? $items[$currEvent['eventgruppen_id']] : $items[$currEvent['item_id']];

				//Legend
				ko_add_color_legend_entry($legend, $currEvent, $item);

				//find position
				$sPos =  $HourTitleWidth + ($stripeWidth * ($eventColPosition[$currEvent['id']])) - $stripeWidth;


				if($eventColPosition[$currEvent['id']] < $maxColumnCnt) {
					$free = array();
					for($j=$eventColPosition[$currEvent['id']]+1; $j<=$maxColumnCnt; $j++) {
						$free[$j] = TRUE;
						for($i=$currEvent['startMin']; $i<$currEvent['stopMin']; $i++) {
							if(isset($colMatrix[$i][$j])) $free[$j] = FALSE;
						}
					}
				}
				$width = $stripeWidth;
				for($j=$eventColPosition[$currEvent['id']]+1; $j<=$maxColumnCnt; $j++) {
					if(!$free[$j]) break;
					$width += $stripeWidth;
				}

				$y = $top + ($currEvent['startMin']*$hourHeight/60) ;
				$height = ($currEvent['stopMin']-$currEvent['startMin']+1)*$hourHeight/60;

				//Get color from event group
				$hex_color = $currEvent['eventgruppen_farbe'];
				if(!$hex_color) $hex_color = 'aaaaaa';
				$pdf->SetFillColor(hexdec(substr($hex_color, 0, 2)), hexdec(substr($hex_color, 2, 2)), hexdec(substr($hex_color, 4, 2)));

				$pdf->RoundedRect($left+$sPos+0.3, $y, $width-0.3, $height-0.2, 1.2, $currEvent['roundedCorners'], 'F');

				//Prepare text for this event
				$eventText = array();
				$eventShortText = array();
				//Use event group and title for events
				if($module == 'daten') {
					$eventText[0] = $item['name'];
					if(trim($currEvent['title']) != '') $eventText[1] .= $currEvent['title']."\n";
					if(trim($currEvent['kommentar']) != '') $eventText[1] .= $currEvent['kommentar'];
					$eventShortText[0] = $item['shortname'];
					if(trim($currEvent['title']) != '') $eventShortText[1] .= $currEvent['title']."\n";
					if(trim($currEvent['kommentar']) != '') $eventShortText[1] .= $currEvent['kommentar'];
				}
				//Use item, purpose and name for reservations
				else {
					$eventText[0] = $item['name'];
					if($show_purpose && $currEvent['zweck'] != '') $eventText[1] = $currEvent['zweck'];
					if($show_persondata && trim($currEvent['name']) != '') $eventText[1] .= ($eventText[1] != '' ? ' - ' : '').getLL('by').' '.$currEvent['name'];
					$eventShortText = $eventText;
				}

				//check if title is still empty (e.g. kommentar is empty)
				if(trim($eventText[0]) == '') {
					$eventText[] = $item['name'];
					$eventShortText[] = $item['shortname'];
				}
				if(trim($eventShortText[0]) == '') {
					$eventShortText = $eventText;
				}
				$replace = array("\n" => ' ', "\r" => ' ', "\t" => ' ', "\v" => ' ');
				$eventText[0] = strtr(trim($eventText[0]), $replace);
				$eventText[1] = strtr(trim($eventText[1]), $replace);
				$eventShortText[0] = strtr(trim($eventShortText[0]), $replace);
				$eventShortText[1] = strtr(trim($eventShortText[1]), $replace);
				while(stristr($eventText[0], '  ') != false) $eventText[0] = str_replace('  ', ' ', $eventText[0]);
				while(stristr($eventText[1], '  ') != false) $eventText[1] = str_replace('  ', ' ', $eventText[1]);
				while(stristr($eventShortText[0], '  ') != false) $eventShortText[0] = str_replace('  ', ' ', $eventShortText[0]);
				while(stristr($eventShortText[1], '  ') != false) $eventShortText[1] = str_replace('  ', ' ', $eventShortText[1]);

				//prepare text to render
				$hex_color = ko_get_contrast_color($currEvent['eventgruppen_farbe'], '000000', 'ffffff');
				if(!$hex_color) $hex_color = '000000';
				$pdf->SetTextColor(hexdec(substr($hex_color, 0, 2)), hexdec(substr($hex_color, 2, 2)), hexdec(substr($hex_color, 4, 2)));

				//check if text is to be rendered vertically
				if($width < 15) {
					//Use shortText if text is too long
					$pdf->SetFont('fontb', '', 7);
					if($pdf->GetStringWidth($eventText[0]) > $height) $eventText = $eventShortText;
					//Shorten texts so they'll fit
					$textLength0 = $pdf->GetStringWidth($eventText[0]);
					while($textLength0 > $height && strlen($eventText[0]) > 0) {
						$eventText[0] = substr($eventText[0], 0, -1);
						$textLength0 = $pdf->GetStringWidth($eventText[0]);
					}
					$pdf->SetFont('fontn', '', 7);
					$textLength1 = $pdf->GetStringWidth($eventText[1]);
					while($textLength1 > $height && strlen($eventText[1]) > 0) {
						$eventText[1] = substr($eventText[1], 0, -1);
						$textLength1 = $pdf->GetStringWidth($eventText[1]);
					}
					$eventText[2] = ': '.$eventText[1];
					$textLength2 = $pdf->GetStringWidth($eventText[2]);
					while($textLength2 > $height - $textLength0 -3 && strlen($eventText[2]) > 0) {
						$eventText[2] = substr($eventText[2], 0, -1);
						$textLength2 = $pdf->GetStringWidth($eventText[2]);
					}

					if($width > 6.1 ) {
						if($textLength0 < $textLength1 ) $textLength0 = $textLength1 ;
						$pdf->SetFont('fontb', '', 7);
						$pdf->TextWithDirection($left+$sPos+2.6, $y+$height/2+($textLength0/2), $eventText[0], $direction='U');
						$pdf->SetFont('fontn', '', 7);
						$pdf->TextWithDirection($left+$sPos+5.5, $y+$height/2+($textLength0/2), $eventText[1], $direction='U');
					}else{
						$pdf->SetFont('fontb', '', 7);
						$pdf->TextWithDirection($left+$sPos+($width/2)+1, $y+$height/2+(($textLength0+3+$textLength2)/2)-1, $eventText[0], $direction='U');
						$pdf->SetFont('fontn', '', 7);
						$pdf->TextWithDirection($left+$sPos+($width/2)+1, $y+$height/2+(($textLength0+3+$textLength2)/2)-1-$textLength0, $eventText[2], $direction='U');
					}
				}
				//Render text horizontally
				else {
					$textPos = $y+1.8;

					//break Text if its too long
					$pdf->SetXY($left+$sPos,$textPos-0.7);
					$pdf->SetFont('fontb', '', 7);
					$titleHeight = ($pdf->NbLines($width, $eventText[0]));
					$pdf->Multicell($width, 3, $eventText[0], 0, 'L');

					//shorten text if it is too long
					$pdf->SetFont('fontn','',7);
					$textHeight = $pdf->NbLines($width, $eventText[1]) + 1;
					if($titleHeight == 2) $textHeigth = $textHeigth +3;
					while($textHeight*3 > $height && strlen($eventText[1]) > 0) {
						if(FALSE !== strpos($eventText[1], ' ')) {
							//Remove a whole word if possible
							$eventText[1] = substr($eventText[1], 0, strrpos($eventText[1], ' '));
						} else {
							//If no more word just remove last letter
							$eventText[1] = substr($eventText[1], 0, -1);
						}
						$textHeight = $pdf->NbLines($width,$eventText[1]) + 1;
					}
					$pdf->SetX($left+$sPos);

					$pdf->Multicell($width,3,$eventText[1],0,'L');
				}

			}//foreach(eventArr as currEvent)
			$left += $dayWidth;
		}//while(index < planSize)


		//Add legend (only for two or more entries and userpref)
		if($show_legend && sizeof($legend) > 1) {
			$right = $planSize*$dayWidth+5;
			ko_cal_export_legend($pdf, $legend, ($top-13.5), $right);
		}

		$startStamp += $planSize * 24 * 3600;
		$endStamp += $planSize * 24 * 3600;
	}




	$file = $BASE_PATH.'download/pdf/'.$filename;

	$ret = $pdf->Output($file);
	return $filename;
}//	ko_export_cal_weekly_view()



function ko_export_cal_weekly_view_resource($_size='', $_start='') {
	global $ko_path, $BASE_PATH, $BASE_URL, $DATETIME, $do_action;

	// Starting parameters
	$startDate = $_start != '' ? $_start : date('Y-m-d', mktime(1,1,1, $_SESSION['cal_monat'], $_SESSION['cal_tag'], $_SESSION['cal_jahr']));

	// get resitems, applies filter from $_SESSION['show_item']
	ko_get_resitems($items, '', sizeof($_SESSION['show_items']) > 0 ? 'where ko_resitem.id in (' . implode(',', $_SESSION['show_items']) . ')' : 'where 1=2');
	$planSize = $_size != '' ? $_size : ko_get_userpref($_SESSION['ses_userid'], 'res_pdf_week_length');
	if($planSize == 1) {
		$weekday = 1;
		$filename = getLL('res_filename_pdf').strftime('%d%m%Y', mktime(1,1,1, $_SESSION['cal_monat'], $_SESSION['cal_tag'], $_SESSION['cal_jahr'])).'_'.strftime('%H%M%S', time()).'.pdf';
	} else {
		$weekday = ko_get_userpref($_SESSION['ses_userid'], 'res_pdf_week_start');
		$filename = getLL('res_filename_pdf').strftime('%d%m%Y_%H%M%S', time()).'.pdf';
	}

	if($weekday == 0) $weekday = 7;
	if(!$planSize) $planSize = 7;


	$startDate = add2date($startDate, 'day', $weekday-1, TRUE);
	$startStamp = strtotime($startDate);
	$endStamp  = strtotime('+'.($planSize-1).' day', $startStamp);

	$maxHours = ko_get_userpref($_SESSION['ses_userid'], 'cal_woche_end') - ko_get_userpref($_SESSION['ses_userid'], 'cal_woche_start');
	$startHour = ko_get_userpref($_SESSION['ses_userid'], 'cal_woche_start')-1;

	$HourTitleWidth = 4;

	//Prepare PDF file
	define('FPDF_FONTPATH', $BASE_PATH.'fpdf/schriften/');
	require($BASE_PATH.'fpdf/mc_table.php');

	$pdf = new PDF_MC_Table('L', 'mm', 'A4');
	$pdf->Open();
	$pdf->SetAutoPageBreak(true, 1);
	$pdf->SetMargins(5, 25, 5);  //left, top, right
	if(file_exists($ko_path.'fpdf/schriften/DejaVuSansCondensed.php')) {
		$pdf->AddFont('fontn', '', 'DejaVuSansCondensed.php');
	} else {
		$pdf->AddFont('fontn', '', 'arial.php');
	}
	if(file_exists($ko_path.'fpdf/schriften/DejaVuSansCondensed-Bold.php')) {
		$pdf->AddFont('fontb', '', 'DejaVuSansCondensed-Bold.php');
	} else {
		$pdf->AddFont('fontb', '', 'arialb.php');
	}


	//Create Resource-View
	$objectLabel = 'Objekt';
	$timeLabel = "";
	//$granularity = ($planSize == 1) ? 24 : 4;
	$granularity = 1;
	$itemMarginV = 0.4;
	$itemMarginH = 0.4;
	$fontSizeItems = 8;
	$fontSizeTime = 6;
	$timeMarginV = 0.45;
	$labelMarginV = 0.2;
	$labelMarginH = 0.2;
	$maxPossCellHeight = 16;
	$minPossCellHeight = 8;
	$resWidth = 18;
	$marginBetwItemLines = 1;

	$timeDelimiter = array();

	// first & last hour in calendar:
	$hour_start = ko_get_userpref($_SESSION['ses_userid'], 'cal_woche_start');
	if ($hour_start == '') {
		$hour_start = "00:00";
		$timeDelimiter[] = $hour_start;
	}
	else if (strlen($hour_start) == 1) {
		$hour_start = "0" . $hour_start . ":00";
		if (ko_has_time_format($hour_start) != 1) {
			$timeDelimiter[] = "00:00";
			koNotifier::Instance()->addNotice(1, $do_action, array($hour_start, 'first', '00:00'));
		}
		else {
			$timeDelimiter[] = $hour_start;
		}
	}
	else if (strlen($hour_start) == 2) {
		$hour_start = $hour_start . ":00";
		if (ko_has_time_format($hour_start) != 1) {
			$timeDelimiter[] = "00:00";
			koNotifier::Instance()->addNotice(1, $do_action, array($hour_start, 'first', '00:00'));
		}
		else {
			$timeDelimiter[] = $hour_start;
		}
	}
	else {
		$timeDelimiter[] = "00:00";
		koNotifier::Instance()->addNotice(1, $do_action, array($hour_start, 'first', '00:00'));
	}

	// add intermediate times
	$intermediateTimes = ko_get_userpref($_SESSION['ses_userid'], 'cal_woche_intermediate_times');
	$imTimesA = explode(';', $intermediateTimes);

	foreach ($imTimesA as $time) {
		if ($time != '') {
			$timeDelimiter[] = $time;
		}
	}

	// last hour in calendar:
	$hour_end = ko_get_userpref($_SESSION['ses_userid'], 'cal_woche_end');
	if ($hour_end == '') {
		$hour_end = "23:59";
		$timeDelimiter[] = $hour_end;
	}
	else if (strlen($hour_end) == 1) {
		$hour_end = "0" . $hour_end . ":00";
		if (ko_has_time_format($hour_end) != 1) {
			$timeDelimiter[] = "23:59";
			koNotifier::Instance()->addNotice(1, $do_action, array($hour_end, 'last', '23:59'));
		}
		else {
			$timeDelimiter[] = $hour_end;
		}
	}
	else if (strlen($hour_end) == 2) {
		$hour_end = $hour_end . ":00";
		if (ko_has_time_format($hour_end) != 1) {
			$timeDelimiter[] = "23:59";
			koNotifier::Instance()->addNotice(1, $do_action, array($hour_end, 'last', '23:59'));
		}
		else {
			$timeDelimiter[] = $hour_end;
		}
	}
	else {
		$timeDelimiter[] = "23:59";
		koNotifier::Instance()->addNotice(1, $do_action, array($hour_end, 'last', '23:59'));
	}

	$rows = sizeof($timeDelimiter) - 1;

	$pdf->SetFont('fontn', '', $fontSizeTime);
	$timeWidth = $pdf->GetStringWidth('00:00') + 1;

	//Calculate the height of each field and the total number of pages
	$maxPossItemHeight = $maxPossCellHeight * $rows;
	$minPossItemHeight = $minPossCellHeight * $rows;
	$noItems = sizeof($items);
	$pageH = (210 - 20 - 2 * 4 - 3);
	$itemHeight = $pageH / $noItems;
	if ($itemHeight < $minPossItemHeight) {
		$itemsPPage = floor($pageH / $minPossItemHeight);
		$itemHeight = $pageH / $itemsPPage;
	}
	else if ($itemHeight > $maxPossItemHeight) {
		$itemsPPage = ceil($pageH / $maxPossItemHeight);
		$itemHeight = $pageH / $itemsPPage;
		if ($itemHeight > $maxPossItemHeight) $itemHeight = $maxPossItemHeight;
	}

	$cellHeight = $itemHeight / $rows;

	//Calculate the width of a day
	$days = $planSize;
	$pageW = 297 - 10;
	$dayWidth = ($pageW - $resWidth - $timeWidth) / $days;

	// Shorten Item Description
	foreach ($items as $key => $item) {
		$firstIter = true;
		while ($pdf->NBLines($resWidth, $items[$key]['name']) * (ko_fontsize_to_mm($fontSizeItems) + $marginBetwItemLines) > $itemHeight && $items[$key]['name'] != '..') {
			if ($firstIter) {
				$items[$key]['name'] .= '..';
			}
			else {
				$items[$key]['name'] = substr($items[$key]['name'], 0, strlen($items[$key]['name']) - 3) . '..';
			}
			$firstIter = false;
		}
	}

	$firstOnPage = true;

	$itemCounter = 1;

	$top = 18;
	$left = 5;

	//Go through resources and draw the corresponding lines
	foreach ($items as $item) {

		// add new page
		if ($top > 5 + 4 + $pageH || $firstOnPage) {

			$pdf->AddPage();
			$pdf->SetLineWidth(0.1);

			$top = 18;
			$left = 5;

			//Title
			$pdf->SetFont('fontb', '', 11);
			$m = strftime('%B', $startStamp) == strftime('%B', $endStamp) ? '' : strftime(' %B ', $startStamp);
			$y = strftime('%Y', $startStamp) == strftime('%Y', $endStamp) ? '' : strftime('%Y', $startStamp);

			if($planSize == 1) {
				$pdf->Text($left, $top-6, getLL('reservation_export_pdf_title').strftime(' - %d. %B %Y', $endStamp));
			} else {
				$pdf->Text($left, $top-6, getLL('reservation_export_pdf_title').strftime(' %d.', $startStamp).$m.$y.strftime(' - %d. %B %Y', $endStamp));
			}

			//Add logo in header (only if legend is not to be shown)
			$logo = ko_get_pdf_logo();
			if($logo != '') {
				$pic = getimagesize($BASE_PATH.'my_images'.'/'.$logo);
				$picWidth = 9 / $pic[1] * $pic[0];
				$pdf->Image($BASE_PATH.'my_images'.'/'.$logo , 290-$picWidth, 5, $picWidth);
			}

			//footer right
			$pdf->SetFont('fontn', '', 8);
			$person = ko_get_logged_in_person();
			$creator = $person['vorname'] ? $person['vorname'].' '.$person['nachname'] : $_SESSION['ses_username'];
			$footerRight = sprintf(getLL('tracking_export_label_created'), strftime($DATETIME['dmY'].' %H:%M', time()), $creator);
			$pdf->Text(291 - $pdf->GetStringWidth($footerRight), 202, $footerRight );

			//Draw resource label
			$pdf->SetFillColor(33, 66, 99);
			$pdf->SetDrawColor(255);
			$pdf->SetTextColor(255, 255, 255);
			$pdf->SetFontSize($fontSizeItems);
			$pdf->Rect($left, $top-4, $resWidth, 4, 'FD');
			$pdf->Text($left+$resWidth/2-$pdf->GetStringWidth($objectLabel)/2, $top-1, $objectLabel);

			$left += $resWidth;

			$pdf->Rect($left, $top-4, $timeWidth, 4, 'FD');
			$pdf->Text($left+$timeWidth/2-$pdf->GetStringWidth($timeLabel)/2, $top-1, $timeLabel);

			$left += $timeWidth;

			$index = 0;

			//Draw day labels and boxes
			while ($index < $planSize) {
				$index++;

				//Get current date information
				$currentStamp = strtotime('+'.($index-1).' day', $startStamp);
				$date = strftime('%d.%m.%Y', $currentStamp);

				$weekday = strftime('%a', $currentStamp);
				$weekday = substr($weekday, 0, strlen($weekday) - 1);

				$pdf->SetFillColor(33, 66, 99);
				$pdf->SetDrawColor(255);
				$pdf->SetTextColor(255, 255, 255);
				$pdf->Rect($left, $top-4, $dayWidth, 4, 'FD');
				$pdf->Text($left+$dayWidth/2-$pdf->GetStringWidth($date)/2, $top-1, $weekday . ', ' . $date);

				$left += $dayWidth;
			}

			$left = 5;
			$top += 4;

			$firstOnPage = true;
		}

		//Print item name
		$pdf->SetFillColor(33, 66, 99);
		$pdf->SetDrawColor(255);
		$pdf->SetTextColor(255);
		$pdf->SetFontSize($fontSizeItems);
		$pdf->Rect($left, $top-4, $resWidth, $itemHeight, 'FD');
		$pdf->SetXY($left, $top - 4);
		$pdf->MultiCell($resWidth, ko_fontsize_to_mm($fontSizeItems) + $marginBetwItemLines, $item['name'], 0, 'L');


		$left += $resWidth;

		$startDate = date('Y-m-d', $startStamp);
		$endDate = date('Y-m-d', $endStamp);

		// draw time boxes
		$index = 0;
		$pdf->SetFillColor(243, 243, 243);
		$pdf->SetDrawColor(255);
		$pdf->SetTextColor(243, 243, 243);
		for ($j = 0; $j < $rows; $j++) {
			$pdf->Rect($left, $top-4 + $j * $cellHeight, $timeWidth, $cellHeight, 'FD');
		}
		$left += $timeWidth;

		//Draw entry boxes
		while ($index < $planSize) {
			$index++;
			for ($i = 0; $i < $granularity; $i++) {
				for ($j = 0; $j < $rows; $j++) {
					$pdf->Rect($left + $i * $dayWidth / $granularity, $top-4 + $j * $cellHeight, $dayWidth / $granularity, $cellHeight, 'FD');
				}
			}
			$left += $dayWidth;
		}

		// draw lines between days
		$left = 5 + $resWidth + $timeWidth;
		$index = 0;
		$oldLineWidth = $pdf->LineWidth;
		$pdf->SetLineWidth(0.2);
		$pdf->SetDrawColor(150);
		while ($index <= $planSize) {
			$index++;
			if ($index == $planSize + 1 && $firstOnPage) {
				$pdf->Line($left, 15 - 1, $left, $top-4+$itemHeight);
			}
			else {
				$pdf->Line($left, $top-4, $left, $top-4+$itemHeight);
			}
			$left += $dayWidth;
		}
		$pdf->SetLineWidth($oldLineWidth);
		$pdf->SetDrawColor(255);

		$left = 5 + $resWidth;

		// draw line to seperate from earlier object
		if (!$firstOnPage) {
			$oldLineWidth = $pdf->LineWidth;
			$pdf->SetLineWidth(0.2);
			$pdf->SetDrawColor(150);
			$pdf->Line($left, $top-4, $left + $timeWidth + $planSize * $dayWidth , $top-4);
			$pdf->SetLineWidth($oldLineWidth);
			$pdf->SetDrawColor(255);
		}

		$left = 5 + $resWidth;

		// get reservations for current id, may contain reservations that don't overlap with de displayed interval
		$id = $item['id'];
		$where = "WHERE ((`startdatum` <= '$startDate' AND `enddatum` >= '$startDate') OR (`startdatum` <= '$endDate' AND `enddatum` >= '$endDate') OR (`startdatum` >= '$startDate' AND `enddatum` <= '$endDate')) AND (`item_id` = '$id') ";
		$reservations = db_select_data('ko_reservation', $where, '*', 'order by `startdatum` asc', '', FALSE, TRUE);
		$unixTimesT = array();

		$hour = 0;
		$minute = 0;

		$startUnix = $startStamp;
		$endUnix = $endStamp + 3600*24 - 1;

		$unixTimes = array();
		foreach ($reservations as $res) {
			if ($res['startdatum'] == $res['enddatum'] && $res['startzeit'] == '00:00:00' && $res['startzeit'] == $res['endzeit']) {
				$res['endzeit'] = '23:59:59';
			}
			$unixTimesT[] = array('corners' => '1234', 'start' => strtotime($res['startdatum'] . ' ' . $res['startzeit']), 'end' => strtotime($res['enddatum'] . ' ' . $res['endzeit']), 'zweck' => $res['zweck'], 'startzeit' => $res['startzeit']);
		}


		// kick results that don't overlap with the desired timespan
		foreach ($unixTimesT as $ut) {
			if ($ut['end'] >= $startUnix && $ut['start'] <= $endUnix) {
				$unixTimes[] = $ut;
			}
		}

		// convert $timeDelimiter s to unix timeformat -> $unixDelimiter
		$unixDelimiter = array();
		foreach ($timeDelimiter as $td) {
			sscanf($td, "%d:%d", $hour, $minute);
			$unixDelimiter[] = ($hour * 3600 + $minute * 60);
		}

		$duration = array();
		$resultRows = array();

		// calculate difference between $unixDelimiter s
		for ($i = 0; $i < $rows; $i ++) {
			$duration[] = ((int) $unixDelimiter[$i + 1]) - ((int) $unixDelimiter[$i]);
			$resultRows[] = array();
		}

		// seconds of a day
		$dayS = 24 * 3600;

		// calculate the entries in the table, switch on whether there will be 1 or more rows per day
		if ($rows > 1) {

			$dayTime = 0;
			$day = 0;

			$prevDayTime = -1;
			$prevEntry = -1;

			foreach ($unixTimes as $k => $ut) {
				$onSameRes = true;
				$belongsToPrevious = false;
				while  ($onSameRes) {
					if ($ut['start'] < $startUnix + $day * $dayS + $unixDelimiter[$dayTime]) {
						$entry = array('start' => $day * $duration[$dayTime]);
						$entry['belongsToPrevious'] = $belongsToPrevious;
						if ($belongsToPrevious) {
							$entry['prevDayTime'] = $prevDayTime;
							$entry['prevEntry'] = $prevEntry;
						}
						else {
							$entry['drawText'] = true;
						}
						$entry['zweck'] = $ut['zweck'];
						$entry['startzeit'] = $ut['startzeit'];
						if ($ut['end'] <= $startUnix + $day * $dayS + ($unixDelimiter[$dayTime + 1]) && $ut['end'] >= $startUnix + $day * $dayS + $unixDelimiter[$dayTime]) {
							$entry['end'] = $ut['end'] - $startUnix - $day * $dayS - $unixDelimiter[$dayTime] + $day * $duration[$dayTime];
							$entry['corners'] = '23';

							/**
							 * check whether the width of the current portion of a reservation is bigger than the previous one
							 * based on the result, decide where to draw the start_time and the purpose
							 **/
							if ($entry['belongsToPrevious'] && !$resultRows[$entry['prevDayTime']][$entry['prevEntry']]['belongsToPrevious']) {
								$width = $entry['end'] - $entry['start'];
								$prevWidth = $resultRows[$entry['prevDayTime']][$entry['prevEntry']]['end'] - $resultRows[$entry['prevDayTime']][$entry['prevEntry']]['start'];
								if ($width / $duration[$dayTime] > $prevWidth / $duration[$entry['prevDayTime']]) {
									$resultRows[$entry['prevDayTime']][$entry['prevEntry']]['drawText'] = false;
									$entry['drawText'] = true;
								}
								else {
									$entry['drawText'] = false;
									$resultRows[$entry['prevDayTime']][$entry['prevEntry']]['drawText'] = true;
								}
							}
							$resultRows[$dayTime][] = $entry;
							$onSameRes = false;
							$belongsToPrevious = false;
						}
						else if ($ut['end'] > $startUnix + $day * $dayS + ($unixDelimiter[$dayTime + 1])) {
							$entry['end'] = ($day + 1) * $duration[$dayTime];
							$entry['corners'] = '';

							/**
							 * check whether the width of the current portion of a reservation is bigger than the previous one
							 * based on the result, decide where to draw the start_time and the purpose
							 **/
							if ($entry['belongsToPrevious'] && !$resultRows[$entry['prevDayTime']][$entry['prevEntry']]['belongsToPrevious']) {
								$width = $entry['end'] - $entry['start'];
								$prevWidth = $resultRows[$entry['prevDayTime']][$entry['prevEntry']]['end'] - $resultRows[$entry['prevDayTime']][$entry['prevEntry']]['start'];
								if ($width / $duration[$dayTime] > $prevWidth / $duration[$entry['prevDayTime']]) {
									$resultRows[$entry['prevDayTime']][$entry['prevEntry']]['drawText'] = false;
									$entry['drawText'] = true;
								}
								else {
									$entry['drawText'] = false;
									$resultRows[$entry['prevDayTime']][$entry['prevEntry']]['drawText'] = true;
								}
							}
							$resultRows[$dayTime][] = $entry;

							// store current array indexes in order to be able to reference this entry in the next loop iteration
							$prevDayTime = $dayTime;
							$prevEntry = sizeof($resultRows[$dayTime]) - 1;

							$dayTime++;
							$belongsToPrevious = true;
						}
						else {
							$onSameRes = false;
						}
					}
					else if ($ut['start'] >= $startUnix + $day * $dayS + $unixDelimiter[$dayTime + 1]) {
						$dayTime ++;
					}
					else {
						$entry = array('start' => $ut['start'] - $startUnix - $day * $dayS - $unixDelimiter[$dayTime] + $day * $duration[$dayTime]);
						$entry['belongsToPrevious'] = $belongsToPrevious;
						if ($belongsToPrevious) {
							$entry['prevDayTime'] = $prevDayTime;
							$entry['prevEntry'] = $prevEntry;
						}
						else {
							$entry['drawText'] = true;
						}
						$entry['zweck'] = $ut['zweck'];
						$entry['startzeit'] = $ut['startzeit'];
						if ($ut['end'] <= $startUnix + $day * $dayS + ($unixDelimiter[$dayTime + 1]) && $ut['end'] >= $startUnix + $day * $dayS + $unixDelimiter[$dayTime]) {
							$entry['end'] = $ut['end'] - $startUnix - $day * $dayS - $unixDelimiter[$dayTime] + $day * $duration[$dayTime];
							$entry['corners'] = '1234';

							/**
							 * check whether the width of the current portion of a reservation is bigger than the previous one
							 * based on the result, decide where to draw the start_time and the purpose
							 **/
							if ($entry['belongsToPrevious'] && !$resultRows[$entry['prevDayTime']][$entry['prevEntry']]['belongsToPrevious']) {
								$width = $entry['end'] - $entry['start'];
								$prevWidth = $resultRows[$entry['prevDayTime']][$entry['prevEntry']]['end'] - $resultRows[$entry['prevDayTime']][$entry['prevEntry']]['start'];
								if ($width / $duration[$dayTime] > $prevWidth / $duration[$entry['prevDayTime']]) {
									$resultRows[$entry['prevDayTime']][$entry['prevEntry']]['drawText'] = false;
									$entry['drawText'] = true;
								}
								else {
									$entry['drawText'] = false;
									$resultRows[$entry['prevDayTime']][$entry['prevEntry']]['drawText'] = true;
								}
							}
							$resultRows[$dayTime][] = $entry;
							$onSameRes = false;
							$belongsToPrevious = false;
						}
						else if ($ut['end'] > $startUnix + $day * $dayS + ($unixDelimiter[$dayTime + 1])) {
							$entry['end'] = ($day + 1) * $duration[$dayTime];
							$entry['corners'] = '41';

							/**
							 * check whether the width of the current portion of a reservation is bigger than the previous one
							 * based on the result, decide where to draw the start_time and the purpose
							 **/
							if ($entry['belongsToPrevious'] && !$resultRows[$entry['prevDayTime']][$entry['prevEntry']]['belongsToPrevious']) {
								$width = $entry['end'] - $entry['start'];
								$prevWidth = $resultRows[$entry['prevDayTime']][$entry['prevEntry']]['end'] - $resultRows[$entry['prevDayTime']][$entry['prevEntry']]['start'];
								if ($width / $duration[$dayTime] > $prevWidth / $duration[$entry['prevDayTime']]) {
									$resultRows[$entry['prevDayTime']][$entry['prevEntry']]['drawText'] = false;
									$entry['drawText'] = true;
								}
								else {
									$entry['drawText'] = false;
									$resultRows[$entry['prevDayTime']][$entry['prevEntry']]['drawText'] = true;
								}
							}
							$resultRows[$dayTime][] = $entry;

							// store current array indexes in order to be able to reference this entry in the next loop iteration
							$prevDayTime = $dayTime;
							$prevEntry = sizeof($resultRows[$dayTime]) - 1;

							$dayTime++;
							$belongsToPrevious = true;
						}
						else {
							$onSameRes = false;
						}
					}

					// if $dayTime exceeds number of $rows, increment $day and set $dayTime back to 0
					if ($dayTime == $rows) {
						$dayTime = 0;
						$day ++;
						if ($day == $planSize) {
							$onSameRes = false;
						}
					}
				}
			}
		}

		else if ($rows == 1) {
			$hourStartUnix = $unixDelimiter[0];
			$hourEndUnix = $unixDelimiter[1];

			$startUnix += $hourStartUnix;
			if (sizeof($unixTimes) > 0) {
				if ($unixTimes[0]['start'] < $startUnix) {
					$unixTimes[0]['start'] = $startUnix;
					$unixTimes[0]['corners'] == '1234' ? $unixTimes[0]['corners'] = '23' : $unixTimes[0]['corners'] = '';
				}
				if ($unixTimes[sizeof($unixTimes) - 1]['end'] > $endUnix) {
					$unixTimes[sizeof($unixTimes) - 1]['end'] = $endUnix;
					$unixTimes[sizeof($unixTimes) - 1]['corners'] == '1234' ? $unixTimes[sizeof($unixTimes) - 1]['corners'] = '41' : $unixTimes[sizeof($unixTimes) - 1]['corners'] = '';
				}
			}
			$dontShowInterval = $hourStartUnix + $dayS - $hourEndUnix;
			foreach ($unixTimes as $k => $ut) {
				// correct start of reservation according to setting 'first hour in export'
				$relToStart = $ut['start'] - $startUnix;
				$modDays = $relToStart % $dayS;
				$daysTo = floor($relToStart / $dayS);
				if ($modDays > $dayS - $dontShowInterval) {
					$unixTimes[$k]['start'] = ($daysTo + 1) * $dayS;
					$unixTimes[$k]['corners'] == '1234' ? $unixTimes[$k]['corners'] = '23' : $unixTimes[$k]['corners'] = '';
				}
				else {
					$unixTimes[$k]['start'] = $relToStart;
				}
				$daysTo = floor($unixTimes[$k]['start'] / $dayS);
				$unixTimes[$k]['start'] = $unixTimes[$k]['start'] - ($daysTo) * $dontShowInterval;

				// correct start of reservation according to setting 'last hour in export'
				$relToStart = $ut['end'] - $startUnix;
				$modDays = $relToStart % $dayS;
				$daysTo = floor($relToStart / $dayS);
				if ($modDays > $dayS - $dontShowInterval) {
					$unixTimes[$k]['end'] = ($daysTo + 1) * $dayS - $dontShowInterval;
					$unixTimes[$k]['corners'] == '1234' ? $unixTimes[$k]['corners'] = '41' : $unixTimes[$k]['corners'] = '';
				}
				else {
					$unixTimes[$k]['end'] = $relToStart;
				}
				$daysTo = floor($unixTimes[$k]['end'] / $dayS);
				$unixTimes[$k]['end'] = $unixTimes[$k]['end'] - ($daysTo) * $dontShowInterval;
			}

			foreach ($unixTimes as $ut) {
				$ut['drawText'] = true;
				$resultRows[0][] = $ut;
			}
		}

		for ($i = 0; $i < $rows; $i++) {
			$planTime = $duration[$i] * $planSize;

			//Draw time label
			$pdf->SetFontSize($fontSizeTime);
			$pdf->SetTextColor(0,0,0);
			$pdf->SetXY(5 + $resWidth, $top-4 + $timeMarginV);
			$pdf->SetDrawColor(0);
			$timeHeight = ko_fontsize_to_mm($fontSizeTime);
			$pdf->Cell($timeWidth, $timeHeight, $timeDelimiter[$i], 0, 0, "C");
			$pdf->SetXY(5 + $resWidth, $top-4 + $cellHeight - $timeHeight - $timeMarginV);
			$pdf->Cell($timeWidth, $timeHeight, $timeDelimiter[$i + 1], 0, 0, "C");

			$left += $timeWidth;

			// draw reservations
			foreach ($resultRows[$i] as $ut) {
				$pdf->SetFillColor(33, 66, 99);
				$pdf->SetDrawColor(255);
				$pdf->SetTextColor(255, 255, 255);
				$hex_color = $item['farbe'];
				if(!$hex_color) $hex_color = 'aaaaaa';
				$pdf->SetFillColor(hexdec(substr($hex_color, 0, 2)), hexdec(substr($hex_color, 2, 2)), hexdec(substr($hex_color, 4, 2)));
				$leftValue = $left + ($ut['start']/$planTime*($pageW - $resWidth - $timeWidth)) + $itemMarginH;
				$topValue = $top-4 + $itemMarginV;
				$width = (($ut['end']-$ut['start'])/$planTime*($pageW - $resWidth - $timeWidth)) - 2 * $itemMarginH;
				$height = $cellHeight - 2 * $itemMarginV;

				$pdf->RoundedRect($leftValue, $topValue, $width, $height, min($cellHeight, (($ut['end']-$ut['start'])/$planTime*($pageW - $resWidth)))/10, $ut['corners'], 'F');

				// draw start_time and purpose of reservation, if possible
				if ($ut['drawText'] === true) {
					$text = $ut['zweck'];
					$time = substr($ut['startzeit'], 0, sizeof($ut['startzeit']) - 4);

					$size = 9;
					if (2 * ko_fontsize_to_mm($size) > $height) {
						$size = floor(ko_mm_to_fontsize(($height) / 2));
					}

					$textMargin = ($labelMarginH + $width / 30 > 2 ? 2 : $labelMarginH + $width / 30);
					$fits = ko_get_fitting_text_width($pdf, $width - $textMargin, $text, $time, $size);

					if ($fits) {
						$hex_color = ko_get_contrast_color($hex_color, '000000', 'FFFFFF');
						$pdf->SetTextColor(hexdec(substr($hex_color, 0, 2)), hexdec(substr($hex_color, 2, 2)), hexdec(substr($hex_color, 4, 2)));
						$pdf->SetFontSize($size);
						$marginBetwLabels = ($height - 2 * ko_fontsize_to_mm($size)) / 3;
						$pdf->Text($leftValue + $textMargin, $topValue + 0.8 * ko_fontsize_to_mm($size) + $marginBetwLabels, $time);
						$pdf->Text($leftValue + $textMargin, $topValue + 1.8 * ko_fontsize_to_mm($size) + 2 * $marginBetwLabels, $text);
					}
				}
			}

			$left = 5 + $resWidth;
			$top += $cellHeight;
		}

		$left = 5;

		// add line on bottom of table, if a new page will be added next or this was the last item
		if ($itemCounter == sizeof($items) || $top > 5 + 4 + $pageH) {
			$oldLineWidth = $pdf->LineWidth;
			$pdf->SetLineWidth(0.2);
			$pdf->SetDrawColor(150);
			$pdf->Line($left, $top-4, $left + $timeWidth + $resWidth + $planSize * $dayWidth , $top-4);
			$pdf->SetLineWidth($oldLineWidth);
			$pdf->SetDrawColor(255);
		}

		$left = 5;

		$firstOnPage = false;

		$itemCounter ++;

	}



	$file = $BASE_PATH.'download/pdf/'.$filename;

	$ret = $pdf->Output($file);
	return $filename;
}//	ko_export_cal_weekly_view_resource()




/**
  * Exportiert einen Monat als PDF
	*/
function ko_export_cal_one_month(&$pdf, $monat, $jahr, $kw, $day, $titel, $show_comment=FALSE, $show_legend=FALSE, $legend=array()) {
	global $BASE_URL, $BASE_PATH, $DATETIME;

	$monthly_title = ko_get_userpref($_SESSION['ses_userid'], 'daten_monthly_title');

	//Datums-Berechnungen
	//Start des Monats
	$startdate = date($jahr."-".$monat."-01");
	$today = date("Y-m-d");
	$startofmonth = $date = $startdate;
	$month_name = strftime("%B", strtotime($date));
	$year_name = strftime("%Y", strtotime($date));

	//Den letzten Tag dieses Monats finden
	$endofmonth = add2date($date, "monat", 1, TRUE);
	$endofmonth = add2date($endofmonth, "tag", -1, TRUE);
	//Ende der letzten Woche dieses Monats finden
	$enddate = date_find_next_sunday($endofmonth);
	//Start der ersten Woche dieses Monats finden
	$date = date_find_last_monday($date);

	$testdate = $date;
	$dayofweek = $num_weeks = 0;
	while((int)str_replace("-", "", $testdate) <= (int)str_replace("-", "", $endofmonth)) {
		$dayofweek++;
		$testdate = add2date($testdate, "tag", 1, TRUE);
		if($dayofweek == 7) {
			$num_weeks++;
			$dayofweek = 0;
		}
	}
	//Falls Sonntag letzter Tag im Monat, wieder eine Woche abziehen
	if((int)$dayofweek == 0) $num_weeks--;


	$pdf->AddPage();

	//Spaltenbreiten für Tabelle
	$width_kw = 7;
	$width_day = 39.5;
	$height_title = 5;
	//$height_day = 9;
	$height_day = (223*0.8)/($num_weeks+1);
	$height_dayheader = 5;
	$height_event_default = 4;
	$offset_x = 1;
	$offset_y = 4;

	$top = 15;
	$left = 7;



	//Titel
	$pdf->SetFont('fontb', '', 11);
	$pdf->Text($left, $top-3, "$titel $month_name $year_name");

	//Add logo in header
	$logo = ko_get_pdf_logo();
	if($logo != '' && !$show_legend) {
		$pic = getimagesize($BASE_PATH.'my_images'.'/'.$logo);
		$picWidth = 9 / $pic[1] * $pic[0];
		$pdf->Image($BASE_PATH.'my_images'.'/'.$logo , 290-$picWidth, $top-10, $picWidth);
	}

	//Footer right
	$pdf->SetFont('fontn', '', 8);
	$person = ko_get_logged_in_person();
	$creator = $person['vorname'] ? $person['vorname'].' '.$person['nachname'] : $_SESSION['ses_username'];
	$footerRight = sprintf(getLL('tracking_export_label_created'), strftime($DATETIME['dmY'].' %H:%M', time()), $creator);
	$pdf->Text(291 - $pdf->GetStringWidth($footerRight), 202, $footerRight);

	//Footer left
	$pdf->SetFont('fontn', '', 8);
	$pdf->Text($left, 202, $BASE_URL);


	//Tabellen-Header
	$pdf->SetTextColor(255);
	$pdf->SetLineWidth(0.1);
	$pdf->SetDrawColor(160);
	$pdf->SetFillColor(33, 66, 99);

	$x = $left;
	$y = $top;
	//KW
	$pdf->SetFont('fontn', '', 8);
	$pdf->Rect($x, $y, $width_kw, $height_title, "FD");
	$pdf->Text($x+$width_kw/2-$pdf->GetStringWidth("KW")/2, $y+3.5, "KW");
	$x+=$width_kw;
	//Tagesnamen
	$monday = date_find_last_monday(date("Y-m-d"));
	$pdf->SetFont('fontb', '', 8);
	for($i=0; $i<7; $i++) {
		$t = strftime("%A", strtotime(add2date($monday, "tag", $i, TRUE)));
		$pdf->Rect($x, $y, $width_day, $height_title, "FD");
		$pdf->Text($x+($width_day-$pdf->GetStringWidth($t))/2, $y+3.5, $t);
		$x += $width_day;
	}

	$x = $left;
	$y += $height_title;

	//Alle anzuzeigenden Tage durchlaufen
	$dayofweek = $weekcounter = 0;
	while((int)str_replace("-", "", $date) <= (int)str_replace("-", "", $enddate)) {
		$pdf->SetTextColor(0);
		$thisday = $day[(int)substr($date, 8, 2)];
		$thisday['tag'] = (int)substr($date, 8, 2);
		//KW ausgeben
		if($dayofweek == 0) {
			$pdf->SetFillColor(200);
			$pdf->Rect($x, $y, $width_kw, $height_day, "FD");
			$pdf->SetFont('fontn', '', 8);
			$pdf->SetTextColor(80);
			$pdf->Text($x+$width_kw/2-$pdf->GetStringWidth($kw[$weekcounter])/2, $y+5, $kw[$weekcounter]);
			$weekcounter++;
			$x += $width_kw;
		}
		//Tag vor und nach aktuellem Monat
		if(substr($date, 5, 2) != $monat) {
			$pdf->SetFillColor(230);
			$pdf->Rect($x, $y, $width_day, $height_day, "FD");
		}
		//Tage dieses Monates
		else {
			$pdf->Rect($x, $y, $width_day, $height_day, "D");
			//Tages-Nummer
			$pdf->SetFont('fontb', '', 8);
			$pdf->SetTextColor(80);
			$pdf->Text( ($x+$width_day-$pdf->GetStringWidth($thisday["tag"])-$offset_x), $y+$offset_y, $thisday["tag"]);
			$y_day = $y+$height_dayheader;
			//Höhe der Termineinträge berechnen
			$num_events = sizeof($thisday["inhalt"]);
			//Add titles
			if($show_comment) {
				foreach($thisday["inhalt"] as $temp) {
					if($temp["kommentar"] != "") $num_events++;
				}
			}
			if($num_events > 0) {
				$height_event = $height_event_default;
				if( ($num_events*$height_event) > ($height_day-$height_dayheader) ) {
					$height_event = ($height_day-$height_dayheader)/$num_events;
				}
				$offset_y_events = 0.75 * $height_event;
				$height_event_1 = $height_event;
				foreach($thisday["inhalt"] as $c) {
					if($show_comment && $c["kommentar"] != "") $height_event = 2* $height_event_1;
					else $height_event = $height_event_1;

					$color_hex = $c["farbe"] ? $c["farbe"] : "999999";
					$pdf->SetFillColor(hexdec(substr($color_hex, 0, 2)), hexdec(substr($color_hex, 2, 2)), hexdec(substr($color_hex, 4, 2)));
					$pdf->Rect($x+0.1, $y_day, $width_day-0.2, $height_event, "F");
					if($num_events > 11) {
            $pdf->SetFont('fontn', '', 5);
						$font2 = 5;
          } else if($num_events > 8) {
            $pdf->SetFont('fontn', '', 6);
						$font2 = 5;
          } else {
            $pdf->SetFont('fontn', '', 7);
						$font2 = 6;
					}
					$t = ($c['zeit'] != '' ? $c['zeit'].': ' : '').ko_unhtml($c['text']);
					//Use short text if long text is too long
					if($pdf->getStringWidth($t) > ($width_day-2*$offset_x)) $t = ($c['zeit'] != '' ? $c['zeit'].': ' : '').ko_unhtml($c['short']);
					//Truncate text if it is too long
					while($pdf->GetStringWidth($t) > ($width_day-2*$offset_x)) {
						$t = substr($t, 0, -1);
					}
					$textcolor = ko_get_contrast_color($color_hex, '000000', 'ffffff');
					$pdf->SetTextColor(hexdec(substr($textcolor, 0, 2)), hexdec(substr($textcolor, 2, 2)), hexdec(substr($textcolor, 4, 2)));
					$pdf->Text($x+$offset_x, $y_day+$offset_y_events, $t);

					//Add title
					if($show_comment && $c["kommentar"]) {
						$y_day += $height_event/2;
						$t = " ".ko_unhtml($c["kommentar"]);
						$pdf->SetFont('fontn', '', $font2);
						while($pdf->GetStringWidth($t) > ($width_day-2*$offset_x)) {
							$t = substr($t, 0, -1);
						}
						$pdf->Text($x+$offset_x, $y_day+$offset_y_events, $t);
						$y_day += $height_event/2;
					} else {
						$y_day += $height_event;
					}

				}
			}//if(num_events > 0)
		}//if(DAY(date) != monat)
		$x += $width_day;
		$dayofweek++;
		$date = add2date($date, "tag", 1, TRUE);
		if($dayofweek == 7) {
			$dayofweek = 0;
			$y += $height_day;
			$x = $left;
		}
	}//while(date < enddate)


	//Add legend (only for two or more entries and userpref)
	if($show_legend && sizeof($legend) > 1) {
		$right = $width_kw+7*$width_day+7;
		ko_cal_export_legend($pdf, $legend, ($top-9.5), $right);
	}
}//ko_export_cal_one_month()





function ko_get_time_as_string($event, $show_time, $mode='default') {
	if($show_time) {
		if($event['startzeit'] == '00:00:00' && $event['endzeit'] == '00:00:00') {
			$time = getLL('time_all_day');
		} else {
			if($mode == 'default') {
				if($show_time == 1) {  //Only show start time
					$time = substr($event['startzeit'], 3, 2) == '00' ? substr($event['startzeit'], 0, 2) : substr($event['startzeit'], 0, -3);
				} else if($show_time == 2) {  //Show start and end time
					$time  = substr($event['startzeit'], 3, 2) == '00' ? substr($event['startzeit'], 0, 2) : substr($event['startzeit'], 0, -3);
					$time .= '-';
					$time .= substr($event['endzeit'], 3, 2) == '00' ? substr($event['endzeit'], 0, 2) : substr($event['endzeit'], 0, -3);
				}
			}
			else if($mode == 'first') {
				$time = getLL('time_from').' ';
				$time .= substr($event['startzeit'], 3, 2) == '00' ? substr($event['startzeit'], 0, 2) : substr($event['startzeit'], 0, -3);
			}
			else if($mode == 'middle') {
				$time = getLL('time_all_day');
			}
			else if($mode == 'last') {
				$time = getLL('time_to').' ';
				$time .= substr($event['endzeit'], 3, 2) == '00' ? substr($event['endzeit'], 0, 2) : substr($event['endzeit'], 0, -3);
			}
		}
	} else {
		$time = '';
	}

	return $time;
}//ko_get_time_as_string()






function ko_export_cal_pdf_year($module, $_month, $_year, $_months=0) {
	global $BASE_PATH, $BASE_URL, $DATETIME;

	// Starting parameters
	$startMonth = $_month ? $_month : '01';
	$startYear = $_year ? $_year : date('Y');
	$planSize = $_months > 0 ? $_months : 12;
	$stripeWidth = 2.5;
	$maxMultiDayColumns = 10;  //Maximum number of columns to be used for multi-day events
	$showWeekNumbers = TRUE;  //Show week numbers on each monday

	$endYear = $startYear;
	$endMonth = $startMonth + $planSize - 1;
	while($endMonth > 12) {
		$endMonth -= 12;
		$endYear += 1;
	}


	$legend = array();

	//Events
	if($module == 'daten') {
		$title_mode = ko_get_userpref($_SESSION['ses_userid'], 'daten_monthly_title');
		$useEventGroups = $_SESSION['show_tg'];
		ko_get_eventgruppen($egs);

		$page_title = getLL('daten_events');
		$db_table = 'ko_event';
		$db_group_field = 'eventgruppen_id';
		$filename_prefix = getLL('daten_filename_pdf');

		$show_legend = ko_get_userpref($_SESSION['ses_userid'], 'daten_export_show_legend') == 1;
	}

	//Reservations
	else if($module == 'reservation') {
		$title_mode = ko_get_userpref($_SESSION['ses_userid'], 'res_monthly_title');
		$useEventGroups = $_SESSION['show_items'];
		ko_get_resitems($egs);

		$page_title = getLL('res_reservations');
		$db_table = 'ko_reservation';
		$db_group_field = 'item_id';
		$filename_prefix = getLL('res_filename_pdf');

		$show_legend = ko_get_userpref($_SESSION['ses_userid'], 'res_export_show_legend') == 1;
	}
	else return FALSE;


	// create Montharray
	//$MonthArr = array (str_to_2($startMonth));
	$index = 0;
	$monthcnt = $startMonth;
	for($index=0; $index<$planSize; $index++) {
		$monthArr[] = $startMonth+$index > 12 ? '01' : str_to_2($startMonth+$index);
	}

	// find offset of each month
	$offsetDate = $startYear."-".$startMonth."-01";
	$offsetDate = date_find_next_sunday($offsetDate);

	$maxDays = 0;
	$year = $startYear;
	for($i=0; $i<$planSize; $i++) {
		$month = $startMonth + $i;
		if($month > 12) {
			$month = $month-12;
			$year = $startYear + 1;
		}
		$offsetDate = 7 - (int)substr(date_find_next_sunday($year.'-'.$month.'-01'), 8, 2);
		$offsetDayArr[str_to_2($month).$year] =  $offsetDate;

		$maxDays = max($maxDays, $offsetDate+(int)strftime('%d', mktime(1,1,1, ($month==12 ? 1 : $month+1), 0, ($month==12 ? ($year+1) : $year))));
	}



	//Start PDF file
	define('FPDF_FONTPATH',$BASE_PATH.'fpdf/schriften/');
	require($BASE_PATH.'fpdf/fpdf.php');

	$pdf=new FPDF('L', 'mm', 'A4');
	$pdf->Open();
	$pdf->SetAutoPageBreak(true, 1);
	$pdf->SetMargins(5, 25, 5);  //left, top, right
	if(file_exists ($BASE_PATH.'fpdf/schriften/DejaVuSansCondensed.php')) {
		$pdf->AddFont('fontn','','DejaVuSansCondensed.php');
	} else {
		$pdf->AddFont('fontn','','arial.php');
	}
	if(file_exists ($BASE_PATH.'fpdf/schriften/DejaVuSansCondensed-Bold.php')) {
		$pdf->AddFont('fontb','','DejaVuSansCondensed-Bold.php');
	} else {
		$pdf->AddFont('fontb','','arialb.php');
	}

	$pdf->AddPage();
	$pdf->SetLineWidth(0.1);


	$top = 18;
	$left = 5;

	//Title
	$pdf->SetFont('fontb', '', 13);
	$pdf->Text($left, $top-7, $page_title."  ".strftime('%B %Y', mktime(1,1,1, $startMonth, 1, $startYear))." - ".strftime('%B %Y', mktime(1,1,1, $endMonth, 1, $endYear)) );

	//Logo
	$logo = ko_get_pdf_logo();
	if($logo && !$show_legend) {
		$pic = getimagesize($BASE_PATH.'my_images'.'/'.$logo);
		$picWidth = 9 / $pic[1] * $pic[0];
		$pdf->Image($BASE_PATH.'my_images'.'/'.$logo , 290 - $picWidth, 5, $picWidth);
	}


	//footer right
	$pdf->SetFont('fontn', '', 8);
	$person = ko_get_logged_in_person();
	$creator = $person['vorname'] ? $person['vorname'].' '.$person['nachname'] : $_SESSION['ses_username'];
	$footerRight = sprintf(getLL('tracking_export_label_created'), strftime($DATETIME['dmY'].' %H:%M', time()), $creator);
	$footerStart = 291  - $pdf->GetStringWidth($footerRight);
	$pdf->Text($footerStart, 202, $footerRight );

	//footer left
	$pdf->Text($left, 202, $BASE_URL);

	//get some mesures
	$dayHeight = 180 / $maxDays;
	$dayHeight = floor($dayHeight*10)/10;
	$monthWidth = 286 / $planSize;
	$monthWidth = floor($monthWidth*10)/10;


	// draw lines of each month
	foreach($offsetDayArr as $key=>$offsetDays) {
		// draw title of the month
		$pdf->SetFillColor(33, 66, 99);
		$pdf->Rect($left, $top-3, $monthWidth, 3, "FD");
		$pdf->SetFont('fontn','',7);
		$pdf->SetTextColor(255 , 255, 255);
		$month = substr($key,0,2);
		$year = substr($key,2);
		$title = strftime('%B',strtotime('2000-'.$month.'-10'));
		$pdf->Text($left+$monthWidth/2-$pdf->GetStringWidth($title)/2, $top-0.7, $title);

		// get the number of days of the month
		$numDays = (int)strftime('%d', mktime(1,1,1, ($month==12 ? 1 : $month+1), 0, ($month==12 ? ($year+1) : $year)));



		// draw frame of the month
		$pdf->Rect($left, $top, $monthWidth, $dayHeight * $maxDays, 'D');
		//Fill areas above and below month
		$pdf->SetFillColor(150, 150, 150);
		$pdf->Rect($left, $top, $monthWidth, $offsetDays*$dayHeight, 'F');
		$pdf->Rect($left, $top+($offsetDays+$numDays)*$dayHeight, $monthWidth, ($maxDays-$offsetDays-$numDays)*$dayHeight, 'F');
		// draw frame of each day
		$pos = $top + $offsetDays*$dayHeight;
		for($i=1; $i<=$numDays; $i++) {
			// Set color according to day of the week (mark weekends)
			switch(date('w', mktime(1,1,1, $month, $i, $year))) {
				case 0: $pdf->SetFillColor(189); break;
				case 6: $pdf->SetFillColor(226); break;
				default: $pdf->SetFillColor(255);
			}
			// Box for each day
			$pdf->Rect($left, $pos, $monthWidth, $dayHeight, 'DF');

			// draw frame for the dates

			// Set color according to day of the week (mark weekends)
			switch(date('w', mktime(1,1,1, $month, $i, $year))) {
				case 0:
					$pdf->SetFillColor(189);
					$pdf->Rect($left, $pos+0.1, 3, $dayHeight-0.2, 'F');
					break;
				case 6:
					$pdf->SetFillColor(226);
					$pdf->Rect($left, $pos+0.1, 3, $dayHeight-0.2, 'F');
					break;
				default: $pdf->SetFillColor(255);
			}



			// draw the dates
			$pdf->SetFont('fontn','',5);
			$pdf->SetTextColor(0 ,0, 0);
			$weekDay = substr(strftime('%a', mktime(1,1,1, $month, $i, $year)), 0, 2);
			$cPos = (3 - $pdf->GetStringWidth($weekDay))/2;
			$pdf->Text($left+$cPos,$pos+2, $weekDay);
			$cPos = (3 - $pdf->GetStringWidth($i))/2;
			$pdf->Text($left+$cPos,$pos+4, $i);

			//Go to next day
			$pos = $pos+$dayHeight;
		}






		// get the events which are at least three days long for vertical lines
		$where = "WHERE (MONTH(startdatum) = ".$month." AND YEAR(startdatum) = ".$year." AND (TO_DAYS(enddatum) - TO_DAYS(startdatum)) > 1 OR MONTH(enddatum) = ".$month." AND YEAR(enddatum) = ".$year." AND (TO_DAYS(enddatum) - TO_DAYS(startdatum)) > 2 OR startdatum < '".$year."-".$month."-01' AND enddatum > '".$year."-".($month+1)."-01' AND (TO_DAYS(enddatum) - TO_DAYS(startdatum)) > 2)";

		if(sizeof($useEventGroups) > 0) {
			$where .= " AND `$db_group_field` IN ('".implode("','", $useEventGroups)."') ";
		} else {
			$where .= ' AND 1=2 ';
		}

		//Add kota filter
		if($module == 'daten') {
			$kota_where = kota_apply_filter('ko_event');
		} else if($module == 'reservation') {
			$kota_where = kota_apply_filter('ko_reservation');
		}
		if($kota_where != '') $where .= " AND ($kota_where) ";

		$order= "ORDER BY startdatum ASC, $db_group_field ASC";
		$eventArr = db_select_data($db_table, $where, "*,(TO_DAYS(enddatum) - TO_DAYS(startdatum)) AS duration ", $order);
		ko_set_event_color($eventArr);

		$columnFillArr = array();

		//draw the multiple day events
		// find the startday
		foreach($eventArr as $currEvent) {
			if($currEvent['duration'] <= 0) continue;

			ko_add_color_legend_entry($legend, $currEvent, $egs[$currEvent[$db_group_field]]);

			$endDay = (int)substr($currEvent['enddatum'],8,2);
			$duration = $currEvent['duration'];
			$eventStart = intval(str_replace('-','',$currEvent['startdatum']));
			$eventEnd = intval(str_replace('-','',$currEvent['enddatum']));
			if ((int)substr($currEvent['startdatum'],5,2) != $month){
				$startDay = 1;
				$durationActMonth = $endDay;
			}else{
				$startDay = (int)substr($currEvent['startdatum'],8,2);
				$durationActMonth = $duration;
			}
			$durationActMonth = $endDay;
			//Find first free column to fit whole event into
			$useColumn = FALSE;
			for($column = 1; $column <= $maxMultiDayColumns; $column++) {
				$stop = FALSE;

				for ($dayCounter = $startDay; $dayCounter <= $startDay + $durationActMonth; $dayCounter++ ) {
					if (isset( $columnFillArr[$dayCounter][$column])) $stop = TRUE;
				}
				if($useColumn === FALSE && !$stop) $useColumn = $column;
			}
			$sPos = $monthWidth-$useColumn*$stripeWidth;



			//Start and end outside of current month - full month
			if($eventStart < intval($year.$month.'01') && $eventEnd > intval($year.$month.$numDays)) {
				$eventStartDay = 1;
				$eventStopDay = $numDays;
				$roundedCorners = '';
			}
			// event starts a month before, ends in this month
			else if($eventStart < intval($year.$month.'01')) {
				$eventStartDay = 1;
				$eventStopDay = $endDay;
				$roundedCorners = '34';
			}
			// event starts in this month, ends next month
			else if($duration > $numDays-$startDay) {
				$eventStartDay = $startDay;
				$eventStopDay = $numDays;
				$roundedCorners = '12';
			}
			// event starts and ends in this month
			else {
				$eventStartDay = $startDay;
				$eventStopDay = $endDay;
				$roundedCorners = '1234';
			}
			$y = $top + ($offsetDays+$eventStartDay-1)*$dayHeight;
			$height = ($eventStopDay-$eventStartDay+1)*$dayHeight;

			//Get color from event group
			$hex_color = $currEvent['eventgruppen_farbe'];
			if(!$hex_color) $hex_color = $egs[$currEvent[$db_group_field]]['farbe'];
			if(!$hex_color) $hex_color = 'aaaaaa';
			$pdf->SetFillColor(hexdec(substr($hex_color, 0, 2)), hexdec(substr($hex_color, 2, 2)), hexdec(substr($hex_color, 4, 2)));
			//Render event box
			$pdf->RoundedRect($left+$sPos, $y+0.1, $stripeWidth, $height-0.2, 1.2, $roundedCorners, 'F');


			//Prepare text for this event
			if($module == 'daten') {
				$titles = ko_daten_get_event_title($currEvent, $egs[$currEvent[$db_group_field]], $title_mode);
				$text = ko_get_userpref($_SESSION['ses_userid'], 'daten_pdf_use_shortname') ? $titles['short'] : $titles['text'];
				$shortText = $titles['short'];
			} else {
				$titles = ko_reservation_get_title($currEvent, $egs[$currEvent[$db_group_field]], $title_mode);
				$text = $titles['text'];
				$shortText = $titles['short'];
			}

			//Render vertical text
			$pdf->SetFont('fontn','',6);
			$hex_color = ko_get_contrast_color($hex_color, '000000', 'ffffff');
			if(!$hex_color) $hex_color = '000000';
			$pdf->SetTextColor(hexdec(substr($hex_color, 0, 2)), hexdec(substr($hex_color, 2, 2)), hexdec(substr($hex_color, 4, 2)));

			//Use shortText if text is too long
			if($pdf->GetStringWidth($text) > $height && $shortText != '') $text = $shortText;
			//Shorten text so it'll fit
			$textLength = $pdf->GetStringWidth($text);
			while($textLength > $height) {
				$text = substr($text, 0, -1);
				$textLength = $pdf->GetStringWidth($text);
			}
			$pdf->TextWithDirection($left+$sPos+2, $y+$height/2+($textLength/2), $text, $direction='U');

			//mark column as used for the just rendered days
			for ($dayCounter = $eventStartDay; $dayCounter <= $eventStopDay; $dayCounter++ ) {
				$columnFillArr[$dayCounter][$useColumn] = 1;
			}
		}







		//get the events which are shorter than 3 days to draw single day events
		$where = "WHERE (MONTH(startdatum) = ".$month." AND YEAR(startdatum) = ".$year." AND (TO_DAYS(enddatum) - TO_DAYS(startdatum)) < 2 OR MONTH(startdatum) <> MONTH(enddatum) AND MONTH(enddatum) = ".$month." AND YEAR(startdatum) = ".$year." AND (TO_DAYS(enddatum) - TO_DAYS(startdatum)) < 2) " ;
		if(sizeof($useEventGroups) > 0) {
			$where .= " AND `$db_group_field` IN ('".implode("','", $useEventGroups)."') ";
		} else {
			$where .= ' AND 1=2 ';
		}

		//Add kota filter
		if($module == 'daten') {
			$kota_where = kota_apply_filter('ko_event');
		} else if($module == 'reservation') {
			$kota_where = kota_apply_filter('ko_reservation');
		}
		if($kota_where != '') $where .= " AND ($kota_where) ";

		$order = " ORDER BY startdatum ASC, startzeit ASC";
		$singleEventArr = db_select_data($db_table, $where, "*, (TO_DAYS(enddatum) - TO_DAYS(startdatum)) AS duration ", $order);
		ko_set_event_color($singleEventArr);


		//Count number of events for each day
		$eventsByDay = array();
		$events = array();
		foreach($singleEventArr as $event) {
			//Add start date
			$dayNum = (int)substr($event['startdatum'], 8, 2);
			//Add end date if different from start date (2-day event)
			$dayNum2 = (int)substr($event['enddatum'], 8, 2);

			//Two-days event: Make two single entries
			if($dayNum2 != $dayNum) {
				//Copy current event into two events
				$event1 = $event2 = $event;
				$event1['enddatum'] = $event1['startdatum'];
				$event2['startdatum'] = $event2['enddatum'];
				//If start and stop date are in the same month, then draw both this time
				if ((int)substr($event['startdatum'],5,2) == (int)substr($event['enddatum'],5,2)){
					$events[] = $event1;
					$events[] = $event2;
					$eventsByDay[$dayNum] += 1;
					$eventsByDay[$dayNum2] += 1;
				}
				//If start and stop are in different months, only draw the one in the current month
				else {
					if((int)substr($event1['enddatum'], 5, 2) == $month) {
						$events[] = $event1;
						$eventsByDay[$dayNum] += 1;
					}
					if((int)substr($event2['enddatum'], 5, 2) == $month) {
						$events[] = $event2;
						$eventsByDay[$dayNum2] += 1;
					}
				}
			}
			//One-day event
			else {
				$events[] = $event;
				$eventsByDay[$dayNum] += 1;
			}
		}

		$eventCounterByDay = array();
		foreach($events as $event) {
			ko_add_color_legend_entry($legend, $event, $egs[$event[$db_group_field]]);

			$startDay = (int)substr($event['startdatum'], 8, 2);
			$duration = $event['duration'];
			$eventStart = intval(str_replace('-', '', $event['startdatum']));

			//Increment counter for rendered events for this day
			$eventCounterByDay[$startDay] += 1;

			//Get upper half. Amount of events to be drawn in upper half of this day's box
			$half = ceil($eventsByDay[$startDay]/2);

			//Calculate y coordinate for this event
			$y = $top + ($offsetDays+$startDay-1)*$dayHeight;
			$y += $eventCounterByDay[$startDay] > $half ? $dayHeight/2 : 0;

			//Set eventHeight and radius depending on number of events on this day
			$fullHeight = FALSE;
			if($eventsByDay[$startDay] > 1) {  //More than one event for this day
				$eventHeight = $dayHeight/2;
				$radius = 0.6;
			} else {  //Only one event
				if($event['startzeit'] == '00:00:00' && $event['endzeit'] == '00:00:00') {  //All day event fill the whole height
					$eventHeight = $dayHeight;
					$radius = 1;
					$fullHeight = TRUE;
				} else {  //Other events only fill half
					$eventHeight = $dayHeight/2;
					$radius = 0.6;
					if((int)substr($event['startzeit'], 0, 2) > 12) $y += $dayHeight/2;
				}
			}

			//Width available to render all events (depending on number of columns used by multi day events)
			$maxCol = max(array_keys($columnFillArr[$startDay]));
			$availableWidth = $monthWidth - 3 - $maxCol*$stripeWidth;

			//Set margin from the left
			$marginLeft = 3;

			//Calculate eventWidth and x coordinate
			if($eventCounterByDay[$startDay] > $half) {
				$eventWidth = $availableWidth/($eventsByDay[$startDay]-$half);
				$x = $left + $marginLeft + ($eventCounterByDay[$startDay]-$half-1)*$eventWidth;
			} else {
				$eventWidth = $availableWidth/$half;
				$x = $left + $marginLeft + ($eventCounterByDay[$startDay]-1)*$eventWidth;
			}


			//Add a little border around each event's box
			$eventWidth -= 0.2;
			$x += 0.1;
			$y += 0.1;
			$eventHeight -= 0.2;

			//Get color from event group
			$hex_color = $event['eventgruppen_farbe'];
			if(!$hex_color) $hex_color = $egs[$event[$db_group_field]]['farbe'];
			if(!$hex_color) $hex_color = 'aaaaaa';
			$pdf->SetFillColor(hexdec(substr($hex_color, 0, 2)), hexdec(substr($hex_color, 2, 2)), hexdec(substr($hex_color, 4, 2)));
			//Render event box
			$pdf->RoundedRect($x, $y, $eventWidth, $eventHeight, $radius, '234', 'F');

			//Prepare text for this event
			if($module == 'daten') {
				$titles = ko_daten_get_event_title($event, $egs[$event[$db_group_field]], $title_mode);
				$text = ko_get_userpref($_SESSION['ses_userid'], 'daten_pdf_use_shortname') ? $titles['short'] : $titles['text'];
				$shortText = $titles['short'];
			} else {
				$titles = ko_reservation_get_title($event, $egs[$event[$db_group_field]], $title_mode);
				$text = $titles['text'];
				$shortText = $titles['short'];
			}


			//Prepare text
			$pdf->SetFont('fontn','',6);
			$hex_color = ko_get_contrast_color($hex_color, '000000', 'ffffff');
			if(!$hex_color) $hex_color = '000000';
			$pdf->SetTextColor(hexdec(substr($hex_color, 0, 2)), hexdec(substr($hex_color, 2, 2)), hexdec(substr($hex_color, 4, 2)));
			$textPos = $y+1.8;
			$textPos += $fullHeight ? $eventHeight/4 : 0;



			//Use shortText if text is too long
			if($pdf->GetStringWidth($text) > $eventWidth && $shortText != '') $text = $shortText;
			//Shorten text so it'll fit
			$textLength = $pdf->GetStringWidth($text);
			while($textLength > $eventWidth && strlen($text) > 0) {
				$text = substr($text, 0, -1);
				$textLength = $pdf->GetStringWidth($text);
			}
			$pdf->Text($x+0.1, $textPos, $text);

		}//foreach(events as event)




		//Add week numbers
		if($showWeekNumbers) {
			$pos = $top + $offsetDays*$dayHeight;
			$pdf->SetFont('fontn','',5);
			for($i=1; $i<=$numDays; $i++) {
				if(substr(strftime('%u', mktime(1,1,1, $month, $i, $year)), 0, 2) == 1) {
					$pdf->SetTextColor(150);
					$pdf->SetFillColor(255, 255, 255);
					$pdf->Circle($left+3.7, $pos+0.1, 1.15, 'F');
					$kw = (int)date('W', mktime(1,1,1, $month, $i, $year));
					$pdf->Text($left+3.7-($pdf->GetStringWidth($kw)/2), $pos+0.8, $kw);
				}
				$pos = $pos+$dayHeight;
			}
		}//if(showWeekNumbers)

		$left += $monthWidth;
	}


	//Add legend (only for two or more entries and userpref)
	if($show_legend && sizeof($legend) > 1) {
		$right = $planSize*$monthWidth+5;
		ko_cal_export_legend($pdf, $legend, ($top-12.5), $right);
	}


	$filename = $filename_prefix.strftime("%d%m%Y_%H%M%S", time()).".pdf";
	$file = $BASE_PATH."download/pdf/".$filename;
	$ret = $pdf->Output($file);

	return 'download/pdf/'.$filename;
}//ko_export_cal_pdf_year()





function ko_cal_export_legend(&$pdf, $legend, $top, $right) {
	if(!is_array($legend) || sizeof($legend) < 2) return;
	
	//Number of entries per column
	$perCol = 3;

	$fontSize = 6;
	$boxSize = $fontSize/2;
	$y = $top;

	//Sort legends by length of title for maximum space usage
	$sort = array();
	foreach($legend as $title => $color) {
		$sort[$title] = strlen($title);
	}
	asort($sort);
	$new = array();
	foreach($sort as $k => $v) {
		$new[$k] = $legend[$k];
	}
	$legend = $new;

	//Find max width of legend titles
	$widths = array();
	$colCounter = 0;
	$pdf->SetFont('fontn', '', $fontSize);
	$counter = 0;
	foreach($legend as $title => $color) {
		$widths[$colCounter] = max($widths[$colCounter], $pdf->GetStringWidth($title));
		$counter++;
		if(fmod($counter, $perCol) == 0) {
			$colCounter++;
			$widths[$colCounter] = 0;
		}
	}
	foreach($widths as $k => $v) {
		$widths[$k] = $v+2;
	}

	$count = 0;
	$colCounter = 0;
	$x = $right-$widths[0];
	foreach($legend as $title => $color) {
		$hex_color = ko_get_contrast_color($color, '000000', 'ffffff');
		if(!$hex_color) $hex_color = '000000';
		$pdf->SetTextColor(hexdec(substr($hex_color, 0, 2)), hexdec(substr($hex_color, 2, 2)), hexdec(substr($hex_color, 4, 2)));

		$hex_color = $color;
		if(!$hex_color) $hex_color = 'aaaaaa';
		$pdf->SetFillColor(hexdec(substr($hex_color, 0, 2)), hexdec(substr($hex_color, 2, 2)), hexdec(substr($hex_color, 4, 2)));
		$pdf->SetDrawColor(255);

		$pdf->Rect($x, $y, $widths[$colCounter], $boxSize, 'FD');
		$pdf->Text($x+1, $y+0.75*$boxSize, $title);

		$count++;
		if(fmod($count, $perCol) == 0) {
			$colCounter++;
			$x-=$widths[$colCounter];
			$y = $top;
		} else {
			$y += $boxSize;
		}
	}
}//ko_cal_export_legend()




function ko_add_color_legend_entry(&$legend, $event, $item) {
	global $EVENT_COLOR;

	$key = $value = '';
	if(is_array($EVENT_COLOR) && sizeof($EVENT_COLOR) > 0 && $event[$EVENT_COLOR['field']] && $EVENT_COLOR['map'][$event[$EVENT_COLOR['field']]]) {
		$key = $event[$EVENT_COLOR['field']];
		$value = $EVENT_COLOR['map'][$event[$EVENT_COLOR['field']]];
	} else {
		$key = $item['name'];
		$value = $item['farbe'];
	}
	if(!$value) $value = 'aaaaaa';

	if($key) $legend[$key] = $value;
}//ko_add_color_legend_entry()




/**
  * Generiert Personen-Liste gemäss Einstellungen (Familie, Personen oder gemäss "AlsFamilieExportieren")
	*/
function ko_generate_export_list($personen, $familien, $mode) {
	if($mode == "p") {
		return array(implode(",", $personen), "");
	}
	else if($mode == "f" || $mode == "def") {
		if(is_array($personen)) {
			foreach($personen as $pid) {
				if($pid) {
					ko_get_person_by_id(format_userinput($pid, "uint"), $p);
					if($p["famid"] > 0) {
						$f = ko_get_familie($p["famid"]);
						if($mode == "f" || ($f["famgembrief"] == "ja" || !isset($f["famgembrief"]))) {
							$fam[] = $p["famid"];
						} else {
							$person[] = $p["id"];
						}
					} else {
						$person[] = format_userinput($pid, "uint");
					}
				}//if(pid)
			}//foreach(personen as pid)
			$xls_auswahl = implode(",", $person);
		} else {
			$xls_auswahl = "";
		}

		if(is_array($familien)) {
			foreach($familien as $f) {
				if($f) $fam[] = format_userinput($f, "uint");
			}
		}
		$xls_fam_auswahl = is_array($fam) ? implode(",", array_unique($fam)) : "";
	}//if(mode == f)

	return array($xls_auswahl, $xls_fam_auswahl);
}//ko_generate_export_list()




function ko_export_etiketten($_vorlage, $_start, $_rahmen, $data, $fill_page=0, $multiply=1, $return_address=FALSE, $return_address_mode = '', $return_address_text = '') {
	global $BASE_PATH;

	ko_get_etiketten_vorlage(format_userinput($_vorlage, "js"), $vorlage);
	$start = format_userinput($_start, "uint");

	//Fill page if needed
	$fill_page = format_userinput($fill_page, "uint");
	if($fill_page > 0) {
		$total = sizeof($data);
		$available = $fill_page*(int)$vorlage["per_col"]*(int)$vorlage["per_row"]-$start+1;
		$new = $total;
		while($new < $available) {
			$data[$new] = $data[(int)fmod($new, $total)];
			$new++;
		}
	}//if(fill_page)

	//Multiply entries
	$multiplyer = format_userinput($multiply, 'uint');
	if(!$multiplyer) $multiplyer = 1;
	if($multiplyer > 1) {
		$orig = $data;
		unset($data);
		foreach($orig as $address) {
			for($i=0; $i<$multiplyer; $i++) {
				$data[] = $address;
			}
		}
	}

	//Get fonts to be used
	$all_fonts = ko_get_pdf_fonts();
	$fonts = array('arial');
	if($vorlage['font']) {
		$fonts[] = $vorlage['font'];
		$font = $vorlage['font'];
	} else {
		$font = 'arial';
	}
	if($vorlage['ra_font']) {
		$fonts[] = $vorlage['ra_font'];
		$ra_font = $vorlage['ra_font'];
	} else {
		$ra_font = 'arial';
	}
	$fonts = array_unique($fonts);

	//Measures for possible page formats
	$formats = array( 'A4' => array(210, 297),
										'A5' => array(148, 210),
										'A6' => array(105, 148),
										'C5' => array(162, 229),
										);
	if(!$vorlage['page_format'] || !in_array($vorlage['page_format'], array_keys($formats))) $vorlage['page_format'] = 'A4';
	if(!$vorlage['page_orientation'] || !in_array($vorlage['page_orientation'], array('L', 'P'))) $vorlage['page_orientation'] = 'P';

	//Set pageW and pageH according to preset
	list($pageW, $pageH) = $formats[$vorlage['page_format']];
	if($vorlage['page_orientation'] == 'L') {
		$t = $pageW;
		$pageW = $pageH;
		$pageH = $t;
	}

	//PDF starten
  define('FPDF_FONTPATH',$BASE_PATH.'fpdf/schriften/');
  require($BASE_PATH.'fpdf/mc_table.php');
  $pdf = new PDF_MC_Table($vorlage['page_orientation'], 'mm', $formats[$vorlage['page_format']]);
  $pdf->Open();
  $pdf->SetAutoPageBreak(false);
  $pdf->calculateHeight(false);
	$pdf->SetMargins($vorlage["border_left"], $vorlage["border_top"], $vorlage["border_right"]);
	foreach($fonts as $f) {
		$pdf->AddFont($f, '', $all_fonts[$f]['file']);
	}
	$pdf->AddPage();

	//Spaltenbreiten ausrechnen
	$page_width = $pageW - $vorlage["border_left"] - $vorlage["border_right"];
	$col_width = $page_width / $vorlage["per_row"];
	$cols = array();
	for($i = 0; $i < $vorlage["per_row"]; $i++) $cols[] = $col_width;
	$pdf->SetWidths($cols);

	//Zeilenhöhe
	$page_height = $pageH - $vorlage["border_top"] - $vorlage["border_bottom"];
	$row_height = $page_height / $vorlage["per_col"];
	$pdf->SetHeight($row_height);

	//Rahmen
	if($_rahmen == "ja") $pdf->border(TRUE);
	else $pdf->border(FALSE);

	//Text-Ausrichtung
	for($i = 0; $i < $vorlage["per_row"]; $i++) $aligns[$i] = $vorlage["align_horiz"]?$vorlage["align_horiz"]:"L";
	for($i = 0; $i < $vorlage['per_row']; $i++) {
		$valigns[$i] = $vorlage['align_vert']?$vorlage['align_vert']:'T';
		//Don't allow center align with return address, as this may lead to overlapping text
		if($return_address && $valigns[$i] == 'C') $valigns[$i] = 'T';
	}

	//Prepare return address
	if($return_address) {
		if (strstr($return_address_mode, 'manual_address') != false) {
			$ra = $return_address_text;
		}
		else if (strstr($return_address_mode, 'login_address') != false) {
			$person = ko_get_logged_in_person();
			$ra  = $person['vorname'] ? $person['vorname'].($person['nachname'] ? ' ' . $person['nachname'] : '') . ', ' : '';
			$ra .= $person['adresse'] ? $person['adresse'].', ' : '';
			$ra .= $person['plz'] ? $person['plz'].' ' : '';
			$ra .= $person['ort'] ? $person['ort'].', ' : '';
			if(substr($ra, -2) == ', ') $ra = substr($ra, 0, -2);
		}
		else {
			$ra  = ko_get_setting('info_name') ? ko_get_setting('info_name').', ' : '';
			$ra .= ko_get_setting('info_address') ? ko_get_setting('info_address').', ' : '';
			$ra .= ko_get_setting('info_zip') ? ko_get_setting('info_zip').' ' : '';
			$ra .= ko_get_setting('info_city') ? ko_get_setting('info_city').', ' : '';
			if(substr($ra, -2) == ', ') $ra = substr($ra, 0, -2);
		}
		if (strstr($return_address_mode, 'pp') != false) {
			$ra = getLL('leute_return_address_pp') . ' ' . $ra;
		}

		$ra_aligns = $ra_valigns = array();
		for($c = 1; $c <= $vorlage['per_row']; $c++) {
			$ra_aligns[] = 'L';
			$ra_valigns[] = 'T';
		}
	}

	//Calculate image width
	if($vorlage['pic_file'] && file_exists($BASE_PATH.$vorlage['pic_file'])) {
		$pic_w = $vorlage['pic_w'] ? $vorlage['pic_w'] : $col_width/4;
		//Limit width of the picture to the width of one label
		if($pic_w > $col_width) $pic_width = $col_width;
		//Limit x position so the picture doesn't leave the label
		if($vorlage['pic_x']+$pic_w > $col_width) $vorlage['pic_x'] = $col_width-$pic_w;
		//Limit y position so the picture doesn't leave the label
		$imagesize = getimagesize($BASE_PATH.$vorlage['pic_file']);
		$pic_h = $pic_w/$imagesize[0]*$imagesize[1];
		if($vorlage['pic_y']+$pic_h > $row_height) $vorlage['pic_y'] = $row_height-$pic_h;
	}

	//Etiketten schreiben
	$all_cols = sizeof($data);
	$last = FALSE;
	$firstpage = TRUE;
	$do_label = FALSE;
	$done = 0;
	$page_counter = 0;
	while(!$last) {
		for($r = 1; $r <= $vorlage["per_col"]; $r++) {  //über alle Zeilen
			$row = array();
			if($return_address) $ra_row = array();
			$do_row = FALSE;
			if(!$last) {
				for($c = 1; $c <= $vorlage["per_row"]; $c++) {  //Über alle Spalten
					$cell_counter++;
					if($firstpage) {  //Auf erster Seite nach erster zu druckenden Etikette suchen
						if($cell_counter >= $start) $do_label = TRUE;
					}//if(firstpage)

					if($do_label) {
						if($done >= $all_cols) $last = TRUE;
						if(!$last) {
							$row[] = $data[$done];
							if($return_address) $ra_row[] = $ra;
							$do_row = TRUE;
							$done++;

							//Add picture if one is given in the selected label preset
							if($vorlage['pic_file'] && file_exists($BASE_PATH.$vorlage['pic_file'])) {
								$pic_x = $vorlage['border_left'] + ($c-1)*$col_width + $vorlage['pic_x'];
								$pic_y = $vorlage['border_top'] + ($r-1)*$row_height + $vorlage['pic_y'];
								$pdf->Image($BASE_PATH.$vorlage['pic_file'], $pic_x, $pic_y, $pic_w);
							}
						}//if(!last)
					}//if(do_label)
					else {
						$row[] = ' ';
						if($return_address) $ra_row[] = ' ';
					}

				}//for(c=1..vorlage[per_row])

				//Print return address on each label of this row
				if($return_address && $do_row) {
					//Store coordinates and line height
					$save['x'] = $pdf->GetX();
					$save['y'] = $pdf->GetY();
					$save['zeilenhoehe'] = $pdf->zeilenhoehe;

					//Print return address
					$ra_margin_left = $vorlage['ra_margin_left'] != '' ? $vorlage['ra_margin_left'] : 3;
					$ra_margin_top = $vorlage['ra_margin_top'] != '' ? $vorlage['ra_margin_top'] : 5;
					$ra_textsize = $vorlage['ra_textsize'] ? $vorlage['ra_textsize'] : 8;

					$pdf->SetFont($ra_font, '', $ra_textsize);
					$pdf->SetZeilenhoehe(3.5);
					$pdf->SetAligns($ra_aligns);
					$pdf->SetvAligns($ra_valigns);
					$pdf->SetInnerBorders($ra_margin_left, $ra_margin_top);
					$pdf->Row($ra_row);
					//Add a line beneath the return address
					$lines = $pdf->NbLines($col_width-2*$ra_margin_left, $ra);
					$line_top = $save['y']+$ra_margin_top+3.5*$lines;
					$pdf->Line($vorlage['border_left'], $line_top, $pageW-$vorlage['border_right'], $line_top);

					//Restore coordinates and line height
					$pdf->SetXY($save['x'], $save['y']);
					$pdf->SetZeilenhoehe($save['zeilenhoehe']);
				} else {
					$ra_margin_top = 0;
				}

				//Set aligns, font and border for actual address content
				$pdf->SetAligns($aligns);
				$pdf->SetvAligns($valigns);
				if($return_address && $valigns[0] == 'T') {
					$spacing_vert = max($vorlage['spacing_vert'], $line_top-$save['y']+2);
				} else {
					$spacing_vert = $vorlage['spacing_vert'];
				}
				$pdf->SetInnerBorders($vorlage['spacing_horiz'], $spacing_vert);
				$pdf->SetFont($font, '', $vorlage["textsize"]?$vorlage["textsize"]:11 );
				$pdf->SetZeilenhoehe(($vorlage['textsize']?$vorlage['textsize']:11)/2);
				$pdf->Row($row);
			}//if(!last)
		}//for(r=1..vorlage[per_col])
		$page_counter++;
		$firstpage = FALSE;
		if($done < $all_cols) $pdf->AddPage();
		$cell_counter = 0;
	}//while(!$last)

	$filename = $BASE_PATH."download/pdf/".getLL("leute_labels_filename").strftime("%d%m%Y_%H%M%S", time()).".pdf";
	$pdf->Output($filename);

	return "download/pdf/".basename($filename);
}//ko_export_etiketten()





function ko_get_pdf_fonts() {
	global $BASE_PATH;

	$fonts = array();
	$files_php = $files_z = array();

	$font_path = $BASE_PATH."fpdf/schriften";
	if($dh = opendir($font_path)) {
		while(($file = readdir($dh)) !== false) {
			if(substr($file, -2) == ".z") {
				$files_z[] = substr($file, 0, -2);
			} else if(substr($file, -4) == ".php") {
				$files_php[] = substr($file, 0, -4);
			}
		}
		closedir($dh);
	}

	foreach($files_z as $font) {
		if(!in_array($font, $files_php)) continue;
		$ll = getLL('fonts_'.$font);
		$fonts[$font] = array("file" => $font.".php", "name" => ($ll?$ll:$font), "id" => $font);
	}
	ksort($fonts, SORT_LOCALE_STRING);

	return $fonts;
}//ko_get_pdf_fonts()




/**
 * Try to find a pdf_logo file to be used in PDF exports
 */
function ko_get_pdf_logo() {
	global $BASE_PATH;

	$r = '';
	$open = @opendir($BASE_PATH.'my_images/');
	while($file = @readdir($open)) {
		if(preg_match('/^pdf_logo\.(png|jpg|jpeg|gif)$/i', $file)) $r = $file;
	}

	return $r;
}//ko_get_pdf_logo()





/**
 * Checks whether LaTeX is installed and pdflatex may be called
 */
function ko_latex_check() {
	global $PDFLATEX_PATH;

	exec($PDFLATEX_PATH.'pdflatex -version', $ret);
	if(sizeof($ret) == 0) return FALSE;
	if(FALSE !== strpos($ret[0], 'TeX')) return TRUE;
	return FALSE;
}//ko_latex_check()



/**
 * Compiles a LaTeX file
 *
 * @param     $file Input file to be compiled
 * @returns   LaTeX compiler output
 */
function ko_latex_compile($file) {
	global $BASE_PATH, $PDFLATEX_PATH;

	system('cd '.$BASE_PATH.'latex/compile/ && '.$PDFLATEX_PATH.'pdflatex -interaction nonstopmode '.$file.'.tex 2&>/dev/null', $ret);
	return $ret;
}//ko_latex_compile()




/**
 * Get all LaTeX layouts stored in latex/layouts for the given type (*.lco files)
 */
function ko_latex_get_layouts($type) {
	global $ko_path;

	$layouts = array();
	if($handle = opendir($ko_path.'latex/layouts/')) {
		while(false !== ($file = readdir($handle))) {
			if($file == '.' || $file == '..' || substr($file, -4) != '.lco' || substr($file, 0, strlen($type)) != $type) continue;
			$layouts[] = substr($file, strlen($type)+1, -4);
		}
	}
	closedir($handle);

	sort($layouts, SORT_LOCALE_STRING);
	return $layouts;
}//ko_latex_get_layouts()




/**
 * Escape charachters from user input so they will show correctly in LaTeX
 */
function ko_latex_escape_chars($text) {
	$map = array(
		'<' => '\textless{}',
		'>' => '\textgreater{}',
		'~' => '\textasciitilde{}',
		'^' => '\textasciicircum{}',
		'&' => '\&',
		'#' => '\#',
		'_' => '\_',
		'$' => '\$',
		'%' => '\%',
		'|' => '\docbooktolatexpipe{}',
		'{' => '\{',
		'}' => '\}',
		'"' => "''",
	);

	return strtr(stripslashes($text), $map);
}//ko_latex_escape_chars()




/**
 * Checks whether pdftk is installed. Is needed to merge several PDF files
 */
function ko_check_for_pdftk() {
	exec('pdftk --version', $ret);
	if(sizeof($ret) == 0) return FALSE;
	if(FALSE !== strpos($ret[1], 'pdftk')) return TRUE;
	return FALSE;
}//ko_check_for_pdftk()








/************************************************************************************************************************
 *                                                                                                                      *
 * Import-FUNKTIONEN                                                                                                    *
 *                                                                                                                      *
 ************************************************************************************************************************/

/**
  * Parses a vCard file (.vcf) and assigns the values to an array to be imported into ko_leute
	*/
function ko_parse_vcf($content) {
	global $db_connection;

	$data = array();

	foreach($content as $line) {
		//Check for encodings
		$quoted = strstr($line, ";ENCODING=QUOTED-PRINTABLE");
		$latin1 = strstr($line, ";CHARSET=ISO-8859-1");

		$line = preg_replace("/;ENCODING=QUOTED-PRINTABLE/", "", $line);
		$line = preg_replace("/;CHARSET=ISO-\d{4}-\d{1,2}/", "", $line);
		$line = preg_replace("/;CHARSET=UTF-8/", "", $line);

		//Find prop and value
		$temp = explode(":", $line);
		$prop = mb_strtoupper($temp[0]);
		unset($temp[0]);
		$value = trim(implode(":", $temp));
		if($quoted) $value = quoted_printable_decode($value);
		if($latin1) $value = utf8_encode($value);

		//Begin of a vCard
		if($prop == "BEGIN" && $value == "VCARD") {
			$new_data = array();
		}
		//Name
		else if($prop == "N") {
			list($new_data["nachname"], $new_data["vorname"], $temp1, $new_data["anrede"], $temp2) = explode(";", $value);
		}
		//address
		else if(substr($prop, 0, 3) == "ADR") {
			$values = explode(";", $value);
			list($temp1, $new_data["adresse_zusatz"], $new_data["adresse"], $new_data["ort"], $temp2, $new_data["plz"], $new_data["land"]) = $values;
		}
		//Phone
		else if(substr($prop, 0, 3) == "TEL") {
			if(strstr($prop, "HOME")) {
				$new_data["telp"] = $value;
			} else if(strstr($prop, "WORK")) {
				$new_data["telg"] = $value;
			} else if(strstr($prop, "CELL")) {
				$new_data["natel"] = $value;
			} else if(strstr($prop, "FAX")) {
				$new_data["fax"] = $value;
			}
		}
		//email
		else if(substr($prop, 0, 5) == "EMAIL") {
			$new_data["email"] = $value;
		}
		//Birthdate
		else if(substr($prop, 0, 4) == "BDAY") {
			$new_data["geburtsdatum"] = substr($value, 0, 10);
		}
		//note
		else if(substr($prop, 0, 4) == "NOTE") {
			$new_data["memo1"] = $value;
		}
		//url
		else if(substr($prop, 0, 3) == "URL") {
			$new_data["web"] = $value;
		}
		//End of a vCard
		else if($prop == "END" && $value == "VCARD") {
			$data[] = $new_data;
		}
	}

	//prepare for mysql
	foreach($data as $key => $value) {
		foreach($value as $k => $v) {
			$return[$key][$k] = mysqli_real_escape_string($db_connection, $v);
		}
	}
	return $return;
}//ko_parse_vcf()



/**
  * Runs some checks, before a csv import can be performed
	*/
function ko_parse_csv($content, $options, $test=FALSE) {
	$separator = $options["separator"];
	$content_separator = $options["content_separator"];
	$first_line = $options["first_line"];
	$dbcols = $options["dbcols"];
	$num_cols = sizeof($dbcols);

	//find date-cols
	$date_cols = $enum_cols = array();
	$table_cols = db_get_columns("ko_leute");
	foreach($table_cols as $col) {
		if($col["Type"] == "date") $date_cols[] = $col["Field"];
		if(substr($col["Type"],0,4) == "enum") $enum_cols[] = $col["Field"];
	}


	$error = 0;
	$data = array();
	$first = TRUE;
	foreach($content as $line) {
		$line = trim($line);

		//Encoding
		if($options['file_encoding'] == 'macintosh') {
			$line = iconv('macintosh', 'UTF-8', $line);
		}
		else if($options['file_encoding'] == 'iso-8859-1') {
			$line = utf8_encode($line);
		}

		//ignore first line if set
		if($first && $first_line) {
			$first = FALSE;
		} else {
			$first = FALSE;

			//get values from one line
			$parts = ko_get_csv_values($line, $separator, $content_separator);

			if($test) {
				if(sizeof($parts) < $num_cols) $error = 1;
				if(sizeof($parts) > $num_cols) $error = 2;
			} else {
				$new_data = array();
				foreach($dbcols as $col) {
					$new_data[$col] = mysqli_real_escape_string($db_connection, array_shift($parts));
					//create sql-date
					if(in_array($col, $date_cols)) {
						$new_data[$col] = sql_datum($new_data[$col]);
					}
					//Check for LL values in enum fields
					if(in_array($col, $enum_cols)) {
						$enums = db_get_enums("ko_leute", $col);
						//If not in English then try to find it in the ll version
						if(!in_array($new_data[$col], $enums)) {
							$enums_ll = db_get_enums_ll("ko_leute", $col);
							foreach($enums_ll as $key => $value) {
								if(mb_strtolower($value) == mb_strtolower($new_data[$col])) {
									$new_data[$col] = $key;
								}
							}
						}//if(!in_array(enums))
					}//if(enum_cols)
				}
				$data[] = $new_data;
			}//if..else(test)
		}//if..else(first)
	}//foreach(content as line)

	if($test) {
		if($error) return FALSE;
		else return TRUE;
	} else {
		return $data;
	}
}//ko_parse_csv()




/**
  * parses a csv line and returns the values as array
	* recognises values separated by sep and embraced between csep
	* from usercomments on php.net for function split()
	*/
function ko_get_csv_values($string, $sep=",", $csep="") {
	//no content separator, so just explode it
	if(!$csep) {
		$elements = explode($sep, $string);
	}
	else {
		$elements = explode($sep, $string);
		for ($i = 0; $i < count($elements); $i++) {
			$nquotes = substr_count($elements[$i], '"');
			if ($nquotes %2 == 1) {
				for ($j = $i+1; $j < count($elements); $j++) {
					if (substr_count($elements[$j], $csep) > 0) {
						// Put the quoted string's pieces back together again
						array_splice($elements, $i, $j-$i+1, implode($sep, array_slice($elements, $i, $j-$i+1)));
						break;
					}
				}
			}
			if ($nquotes > 0) {
				// Remove first and last quotes, then merge pairs of quotes
				$qstr =& $elements[$i];
				$qstr = substr_replace($qstr, '', strpos($qstr, $csep), 1);
				$qstr = substr_replace($qstr, '', strrpos($qstr, $csep), 1);
				$qstr = str_replace('""', '"', $qstr);
			}
		}
	}
	return $elements;
}//ko_get_csv_values()







/**
 * Return HTML img tag with the thumbnail for the given image
 * @param $img Name of image in folder my_images
 * @param $max_dim Size in pixels of bigger dimension to be used for thumbnail
 */
function ko_pic_get_thumbnail($img, $max_dim, $imgtag=TRUE) {
	global $BASE_PATH, $ko_path;

	//Check for valid image
	$img = basename($img);
	if(trim($img) == '') return '';
	if(!is_file($BASE_PATH.'my_images/'.$img)) return '';

	clearstatcache();

	//Get modification time for the image
	$file = $BASE_PATH.'my_images/'.$img;
	$ext = mb_strtolower(substr($img, strrpos($img, '.')));
	$filemtime = filemtime($file);

	//Create filename for cache image (using filename and file's modification time)
	$cache_filename = md5($img.$filemtime).'_'.$max_dim.'.png';
	$cache_file = $BASE_PATH.'my_images/cache/'.$cache_filename;
	$cachemtime = filemtime($cache_file);

	//Create new thumbnail if none stored yet
	if(!$cachemtime || $filemtime > $cachemtime) {
		//Create new thumbnail
		$scaled = ko_pic_scale_image($file, $max_dim);
		if($scaled === FALSE) return '';
	}

	if($imgtag) {
		$r = '<img src="'.$ko_path.'my_images/cache/'.$cache_filename.'" />';
	} else {
		$r = $ko_path.'my_images/cache/'.$cache_filename;
	}
	return $r;
}//ko_pic_get_preview()





/**
 * Return HTML img tag with tooltip effect showing a thumbnail of the given image
 * @param $thumb Size of thumbnail to be used. Set to 0 (default) to only display icon
 * @param $img Name of image in folder my_images
 * @param $dim Size in pixels of the tooltip (defaults to 200px)
 * @param $pv Vertical position for tooltip (t, m, b)
 * @param $ph Horizontal position for tooltip (l, c, r)
 * @param $link boolean Link to original image
 */
function ko_pic_get_tooltip($img, $thumb=0, $dim=200, $pv='t', $ph='c', $link=FALSE) {
	global $ko_path;

	$ttimg = ko_pic_get_thumbnail($img, $dim);
	if($ttimg == '') return '';

	if($thumb > 0) {
		$thumbimg = ko_pic_get_thumbnail($img, $thumb, FALSE);
	} else {
		$thumbimg = $ko_path.'images/image.png';
	}

	$r = '<img src="'.$thumbimg.'" border="0" onmouseover="tooltip.show(\''.ko_html($ttimg).'\', \'\', \''.$pv.'\', \''.$ph.'\');" onmouseout="tooltip.hide();" />';

	if($link) {
		$r = '<a href="'.$img.'" target="_blank">'.$r.'</a>';
	}

	return $r;
}//ko_pic_get_tooltip()





/**
 * Creates a scaled down image of the given file and stores it in my_images/cache
 * @param $file Absolute path to image file to be scaled
 * @param $max_dim Size in pixels for the scaled down image
 */
function ko_pic_scale_image($file, $max_dim) {
	global $BASE_PATH;

	//detect type and process accordinally
	$size = getimagesize($file);
	switch($size['mime']){
		case 'image/jpeg':
			$image = imagecreatefromjpeg($file);
		break;
		case 'image/gif':
			$image = imagecreatefromgif($file);
		break;
		case 'image/png':
			$image = imagecreatefrompng($file);
		break;
		default:
			$image=false;
	}
	if($image === false) return FALSE;

	//Get name for cached file
	$cache_filename = md5(basename($file).filemtime($file)).'_'.$max_dim.'.png';
	$cache_file = $BASE_PATH.'my_images/cache/'.$cache_filename;

	//Get current image size
	$w = imagesx($image);
	$h = imagesy($image);
	//Get new height
	if($w > $h) {
		$thumb_w = $max_dim;
		$thumb_h = floor($thumb_w*($h/$w));
	} else {
		$thumb_h = $max_dim;
		$thumb_w = floor($thumb_h*($w/$h));
	}
	//Create thumb
	$thumb = ImageCreateTrueColor($thumb_w, $thumb_h);
	imagecopyResampled($thumb, $image, 0, 0, 0, 0, $thumb_w, $thumb_h, $w, $h);
	imagepng($thumb, $cache_file);
	//Clean up
	imagedestroy($image);
	imagedestroy($thumb);

	//Clean up image cache by deleting not used images
	ko_pic_cleanup_cache();

	return TRUE;
}//ko_pic_scale_image()





/**
 * Remove unused images from my_images/cache
 */
function ko_pic_cleanup_cache() {
	global $BASE_PATH;

	clearstatcache();

	//Get all images in my_images and calculate their md5 values for comparison
	$hashes = array();
	if($dh = opendir($BASE_PATH.'my_images/')) {
		while(($file = readdir($dh)) !== false) {
			if(!in_array(mb_strtolower(substr($file, -4)), array('.gif', '.jpg', 'jpeg', '.png'))) continue;
			$hashes[] = md5($file.filemtime($BASE_PATH.'my_images/'.$file));
		}
	}
	@closedir($dh);

	//Check all cache files for corresponding hash from above
	if($dh = opendir($BASE_PATH.'my_images/cache/')) {
		while(($file = readdir($dh)) !== false) {
			if(!in_array(mb_strtolower(substr($file, -4)), array('.gif', '.jpg', 'jpeg', '.png'))) continue;
			$hash = substr($file, 0, strpos($file, '_'));
			if(!in_array($hash, $hashes)) unlink($BASE_PATH.'my_images/cache/'.$file);
		}
	}
	@closedir($dh);
}//ko_pic_cleanup_cache()








/**
 * Plugin function to connect to a TYPO3 database
 * Connetion details for TYPO3 db are taken from settings which can be changed in the tools module
 * @deprecated This function is not maintained anymore
 */
function plugin_connect_TYPO3() {
  global $mysql_server, $BASE_PATH;

	if(!ko_get_setting('typo3_db')) return FALSE;

	//Get password and decrypt
	$pwd_enc = ko_get_setting('typo3_pwd');
	include_once($BASE_PATH.'inc/class.mcrypt.php');
	$crypt = new mcrypt('aes');
	$crypt->setKey(KOOL_ENCRYPTION_KEY);
	$pwd = trim($crypt->decrypt($pwd_enc));

  if($mysql_server != ko_get_setting('typo3_host')) {
    mysql_connect(ko_get_setting('typo3_host'), ko_get_setting('typo3_user'), $pwd);
  }

  if(!mysql_select_db(ko_get_setting('typo3_db'))) {
    ko_die('Could not establish connection to the TYPO3 database: '.mysql_error());
  }
}//plugin_connect_TYPO3()



/**
 * Plugin function to connect to the current kOOL database again (called after plugin_connect_TYPO3())
 * @deprecated This function is not maintained anymore
 */
function plugin_connect_kOOL() {
  global $mysql_db, $mysql_server, $mysql_user, $mysql_pass;

  if($mysql_server != ko_get_setting('typo3_host') || $mysql_user != ko_get_setting('typo3_user')) {
    mysql_connect($mysql_server, $mysql_user, $mysql_pass);
  }

  mysql_select_db($mysql_db);
}//plugin_connect_kOOL()





function ko_get_ical_link($url, $text) {
	global $ko_path;

	$r = '';

	$r .= '<a href="javascript:ko_image_popup(\''.$ko_path.'inc/qrcode.php?s='.base64_encode($url).'&h='.md5(KOOL_ENCRYPTION_KEY.$url).'&size=250\');"><img src="'.$ko_path.'images/icon_qrcode.png" title="'.getLL('ical_qrcode').'" /></a>';
	$r .= '&nbsp;&nbsp;';
	$r .= '<a href="'.$url.'" onclick="return false;">'.$text.'</a>';

	return $r;
}//ko_get_ical_link()





/**
 * Creates ICS string for reservations and returns the string
 *
 * @param $res array DB array from ko_reservation
 * @param $forceDetails boolean Set to true to always have details included normally only visible to logged in users
 * @return string ICS feed as string
 */
function ko_get_ics_for_res($res, $forceDetails=FALSE) {
	global $BASE_URL;

	$mapping = array(';' => '\;', ',' => '\,', "\n" => "\n ", "\r" => '');
	define('CRLF', chr(10));

	//build ical file in a string
	$ical  = "BEGIN:VCALENDAR".CRLF;
	$ical .= "VERSION:2.0".CRLF;
	$ical .= "CALSCALE:GREGORIAN".CRLF;
	$ical .= "METHOD:PUBLISH".CRLF;
	$ical .= "PRODID:-//".str_replace("/", "", $HTML_TITLE)."//www.churchtool.org//DE".CRLF;
	foreach($res as $r) {
		//build ics string
		$ical .= "BEGIN:VEVENT".CRLF;
		if($r['cdate'] != '0000-00-00 00:00:00') $ical .= "CREATED:".strftime("%Y%m%dT%H%M%S", strtotime($r["cdate"])).CRLF;
		if($r['last_change'] != '0000-00-00 00:00:00') $ical .= "LAST-MODIFIED:".strftime("%Y%m%dT%H%M%S", strtotime($r["last_change"])).CRLF;
		$ical .= "DTSTAMP:".strftime("%Y%m%dT%H%M%S", time()).CRLF;
		$base_url = $_SERVER['SERVER_NAME'] ? $_SERVER['SERVER_NAME'] : $BASE_URL;
		$ical .= 'UID:r'.$r['id'].'@'.$base_url.CRLF;
		if(intval(str_replace(':', '', $r['startzeit'])) >= 240000) $r['startzeit'] = '23:59:00';
		if(intval(str_replace(':', '', $r['endzeit'])) >= 240000) $r['endzeit'] = '23:59:00';
		if($r["startzeit"] == "00:00:00" && $r["endzeit"] == "00:00:00") {  //daily event
			$ical .= "DTSTART;VALUE=DATE:".strftime("%Y%m%d", strtotime($r["startdatum"])).CRLF;
			$ical .= "DTEND;VALUE=DATE:".strftime("%Y%m%d", strtotime(add2date($r["enddatum"], "tag", 1, TRUE))).CRLF;
		} else if($r['startzeit'] != '00:00:00' && $r['endzeit'] == '00:00:00') {  //No end time given so set it to midnight
			$ical .= 'DTSTART:'.date_convert_timezone(($r['startdatum'].' '.$r['startzeit']), 'UTC').CRLF;
			$ical .= 'DTEND:'.date_convert_timezone(($r['enddatum'].' 23:59:00'), 'UTC').CRLF;
		} else {
			$ical .= 'DTSTART:'.date_convert_timezone(($r['startdatum'].' '.$r['startzeit']), 'UTC').CRLF;
			$ical .= 'DTEND:'.date_convert_timezone(($r['enddatum'].' '.$r['endzeit']), 'UTC').CRLF;
		}
		$ical .= 'SUMMARY:'.strtr(trim($r['item_name']), $mapping).($r['zweck'] ? (': '.strtr(trim($r['zweck']), $mapping)) : '').CRLF;
		$desc = '';
		if($_SESSION["ses_username"] != "ko_guest" || $forceDetails === TRUE) {
			$desc .= $r["name"].($r["email"]?", ".$r["email"]:"").($r["telefon"]?", ".$r["telefon"]:"").CRLF;
			$desc .= $r['comments'].CRLF;
		}
		if($desc) $ical .= "DESCRIPTION:".strtr(trim($desc), $mapping).CRLF;
		$ical .= "END:VEVENT".CRLF;
	}
	$ical .= "END:VCALENDAR".CRLF;

	return $ical;
}//ko_get_ics_for_res()



/**
 * Writes an ICS file with the given data and returns the filename
 *
 * @param $mode string Can be res or daten to either create ICS for reservations or events
 * @param $data array DB data for ko_reservation or ko_events
 * @param $forceDetails boolean Force the inclusion of details normally not visible to ko_guest
 * @return string Filename of ics file relative to BASE_PATH/download/
 */
function ko_get_ics_file($mode, $data, $forceDetails=FALSE) {
	global $BASE_PATH;

	switch($mode) {
		case 'res':
			$ical = ko_get_ics_for_res($data, $forceDetails);
		break;

		case 'daten':
			//TODO
		break;
	}

	$filename = 'ical_'.date('Ymd_His').'.ics';
	$fp = fopen($BASE_PATH.'download/'.$filename, 'w');
	fputs($fp, $ical);
	fclose($fp);

	return $filename;
}//ko_get_ics_file()








/************************************************************************************************************************
 *                                                                                                                      *
 * Util-FUNKTIONEN                                                                                                      *
 *                                                                                                                      *
 ************************************************************************************************************************/


/**
 * Get help entry from db (ko_help) for the given module and type
 *
 * @param $module string: Module this help is for
 * @param $type string: Type of help to display
 * @param $ttparams array: Parameters for tooltip (if text help): Assoziative array. Possible keys: w for width, pv for vertical position (t, m, b), ph for horizontal position (l, c, r)
 * @return array: show = TRUE, link: HTML code to include which shows the help icon with link or tooltip
 */
function ko_get_help($module, $type, $ttparams=array()) {
	global $ko_path;

	//Map kOOL languages to TYPO3-Language uids
	$map_lang = array("en" => 0, "de" => 1, "nl" => 2);

	if($type == '') $type = '_notype';
	$help = FALSE;

	//Get help entry from cache
	if(isset($GLOBALS['kOOL']['ko_help'][$_SESSION['lang']][$type])) {
		$help = $GLOBALS['kOOL']['ko_help'][$_SESSION['lang']][$type];
	} else {
		$help = $GLOBALS['kOOL']['ko_help']['en'][$type];
	}
	if(!$help["id"]) return FALSE;


	//Help text given in DB - display as tooltip
	if($help["text"]) {
		$text = str_replace("\r", "", str_replace("\n", "", nl2br(ko_html($help["text"]))));
		$link = '<span onmouseover="tooltip.show(\''.$text.'\', \''.$ttparams['w'].'\', \''.$ttparams['pv'].'\', \''.$ttparams['ph'].'\');" onmouseout="tooltip.hide();"><img src="'.$ko_path.'images/icon_help.png" border="0" alt="help" /></span>';
	}
	//Create link to online documentation
	else {
		if(!$help["t3_page"]) return FALSE;
		$href  = 'http://www.churchtool.org/?id='.$help["t3_page"];
		$href .= '&L='.$map_lang[$help["language"]];
		if($help["t3_content"]) $href .= "#c".$help["t3_content"];

		$link = '<a href="'.$href.'" target="_blank"><img src="'.$ko_path.'images/icon_help.png" border="0" alt="help" title="'.getLL("help_link_title").'" /></a>';
	}

	return array("show" => TRUE, "link" => $link);
}//ko_get_help()




function ko_leute_sort(&$data, $sort_col, $sort_order, $dont_apply_limit=FALSE, $forceDatafields=FALSE) {
	global $all_groups, $FAMFUNCTION_SORT_ORDER, $access;

	//Check for columns which don't need second sorting as they can be sorted by MySQL directly (see ko_get_leute)
	if(!is_array($sort_col)) $sort_col = array($sort_col);
	if(!is_array($sort_order)) $sort_order = array($sort_order);
	if(!ko_manual_sorting($sort_col)) return $data;

	//get all datafields (used in map_leute_daten)
	$all_datafields = db_select_data("ko_groups_datafields", "WHERE 1=1", "*");

	//build sort-array
	foreach($data as $i => $v) {
		foreach($sort_col as $col) {
			if(!$col) continue;

			$col_value = NULL;
			$map_col = $col;  //Used for map_leute_daten()

			//Sort by birthday instead of age (only used from tx_koolleute_pi1)
			if($col == "MODULEgeburtsdatum") {
				$map_col = "geburtsdatum";
			}

			if(!$col_value) $col_value = map_leute_daten($v[$map_col], $map_col, $v, $all_datafields, $forceDatafields);

			switch($col) {
				case "MODULEgeburtsdatum":  //Order by month and day
					if($v[$map_col] == '0000-00-00') $col_value = 0;  //Would map to 01011970 which would be wrong
					else $col_value = strftime('%m%d%Y', strtotime($v[$map_col]));
				break;
				case 'geburtsdatum':  //Order by year (age) (Needed, as mapped value $col_value has already been transformed with sql_datum in map_leute_daten()
					$col_value = strftime('%Y%m%d', strtotime($v[$map_col]));
				break;
				case 'famid':
					//Use the full family name without the fam function for sorting, so families with same names in the same city still don't get mixed
					$col_value = substr($col_value, 0, strpos($col_value, ')'));
				break;
				case 'famfunction':
					if(isset($FAMFUNCTION_SORT_ORDER[$v[$col]])) $col_value = $FAMFUNCTION_SORT_ORDER[$v[$col]];
					else $col_value = 9;  //Add entires with no famfunction at the end
				break;
			}

			//Build sort arrays for array_multisort()
			${"sort_".str_replace(':', '_', $col)}[$i] = mb_strtolower($col_value);
		}
	}
	foreach($sort_col as $i => $col) {
		$sort[] = '$sort_'.str_replace(':', '_', $col).', SORT_'.mb_strtoupper($sort_order[$i]);
	}
	eval('array_multisort('.implode(", ", $sort).', $data);');

	if(!$dont_apply_limit) {
		$data = array_slice($data, ($_SESSION["show_start"]-1), $_SESSION["show_limit"]);
	}

	//Correct array index (numeric indizes get rearranged by array_multisort())
	foreach($data as $key => $value) {
		$r[$value["id"]] = $value;
	}
	return $r;
}//ko_leute_sort()




function ko_get_map_links($data) {
	$code = "";
	$hooks = hook_get_by_type("leute");
	if(sizeof($hooks) > 0) {
		foreach($hooks as $hook) {
			if(function_exists("my_map_".$hook)) {
				$code .= call_user_func("my_map_".$hook, $data);
			}
		}
	}

	return $code;
}//ko_get_map_links()




function ko_check_fm_for_user($fm_id, $uid) {
	global $FRONTMODULES;

	$allow = FALSE;
	$fm = $FRONTMODULES[$fm_id];

	ko_get_user_modules($uid, $user_modules);

	if(!$fm["modul"]) {  //no module needed, to display this FM
		$allow = TRUE;
	} else {  //One of the given modules must be installed for this FM
		foreach(explode(",", $fm["modul"]) as $m) {
			if(in_array($m, $user_modules)) $allow = TRUE;
		}
	}
	return $allow;
}//ko_check_fm_for_user()

/**
  * Check, whether LDAP ist active for this kOOL
	*/
function ko_do_ldap() {
	global $ldap_enabled, $ldap_dn;

	$do_ldap = ($ldap_enabled && trim($ldap_dn) != "");
	return $do_ldap;
}//ko_do_ldap()



/**
	* Connect and bind to the LDAP-Server
	*/
function ko_ldap_connect() {
	global $ldap_server, $ldap_admin, $ldap_login_dn, $ldap_admin_pw;

	ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
	$ldap = ldap_connect($ldap_server);
	if($ldap) {
		//Bind (Login)
		$r = ldap_bind($ldap, ('cn='.$ldap_admin.','.$ldap_login_dn), $ldap_admin_pw);

		//Error handling for ldap operations
		if($r === FALSE) {
			ko_log('ldap_error', 'LDAP bind ('.ldap_errno($ldap).'): '.ldap_error($ldap).': cn='.$ldap_admin.','.$ldap_login_dn);
			return FALSE;
		}
		else return $ldap;
	}
	return FALSE;
}//ko_ldap_connect()



/**
  * Disconnect from the LDAP-Server
	*/
function ko_ldap_close(&$ldap) {
	ldap_close($ldap);
}//ko_ldap_close()



/**
  * LDAP: Add Person Entry
	*/
function ko_ldap_add_person(&$ldap, $person, $uid, $edit=FALSE) {
  global $ldap_dn, $LDAP_ATTRIB, $LDAP_SCHEMA;

  if(!$ldap) return FALSE;
  if(!$uid) return FALSE;

  //Add id
  $person['id'] = $uid;

  //Map all person values to LDAP attributes
  $ldap_entry = array();
  foreach($LDAP_ATTRIB as $pkey => $lkey) {
    //Handle array parameters, where one kOOL field is matched to several LDAP fields
    if(!is_array($lkey)) $lkey = array($lkey);
    foreach($lkey as $lkey2) {
      if($ldap_entry[$lkey2] != '' || is_array($ldap_entry[$lkey2])) {
        if($person[$pkey] != '') {  //If several kOOL columns end up in one ldap field, then store them as array
          if(is_array($ldap_entry[$lkey2])) {
            $ldap_entry[$lkey2][] = $person[$pkey];  //Add new entry
          } else {
            $ldap_entry[$lkey2] = array($ldap_entry[$lkey2], $person[$pkey]);  //Convert current value in new array
          }
        }
      } else {
        $ldap_entry[$lkey2] = $person[$pkey];
      }
    }
  }
  //Use preferred email and mobile
  ko_get_leute_email($person, $email);
  $ldap_entry['mail'] = $email[0];
  ko_get_leute_mobile($person, $mobile);
  $ldap_entry['mobile'] = $mobile[0];

  //Add cn and uid
  $ldap_entry['cn'] = $ldap_entry['givenName'].' '.$ldap_entry['sn'];
  $ldap_entry['uid'] = $uid;

  //Data vorbehandeln, damit es mit z.B. Umlauten keine Probleme gibt.
  foreach($ldap_entry as $i => $d) {
    if(!$i) unset($ldap_entry[$i]);  //Unset entries with no key
    else if(is_array($d)) {  //Multiple values for one key are stored as array
      foreach($d as $dk => $dv) $ldap_entry[$i][$dk] = $dv;
    }
    else if(!$edit && trim($d) == '') unset($ldap_entry[$i]);  //Unset empty values if a new LDAP entry is to be made
    else if($edit && trim($d) == '') $ldap_entry[$i] = array();  //Set empty values to array(), so they get deleted in LDAP (when editing)
    else $ldap_entry[$i] = $d;
  }
  //ObjectClass inetOrgPerson requires sn
  if(!isset($ldap_entry['sn'])) $ldap_entry['sn'] = ' ';

  $ldap_entry['objectclass'] = $LDAP_SCHEMA;

  if($edit) {
    $r = ldap_modify($ldap, ('uid='.$uid.','.$ldap_dn), $ldap_entry);

		//Try to add new entry on error
    if($r === FALSE) {
      foreach($ldap_entry as $i => $d) {
        if((!is_array($d) && trim($d) == '') || (is_array($d) && sizeof($d) == 0)) unset($ldap_entry[$i]);
      }
      $r = ldap_add($ldap, ('uid='.$uid.','.$ldap_dn), $ldap_entry);
    }
  } else {
    $r = ldap_add($ldap, ('uid='.$uid.','.$ldap_dn), $ldap_entry);
  }

	//Error handling for ldap operations
	if($r === FALSE) ko_log('ldap_error', 'LDAP add_person ('.ldap_errno($ldap).'): '.ldap_error($ldap).': '.print_r($ldap_entry, TRUE));

  return $r;
}//ko_ldap_add_person()



/**
  * LDAP: Delete Person Entry
	*/
function ko_ldap_del_person(&$ldap, $id) {
	global $ldap_dn;

	if($ldap) {
		$r = ldap_delete($ldap, ("uid=".$id.",".$ldap_dn));

		//Error handling for ldap operations
		if($r === FALSE) ko_log('ldap_error', 'LDAP del_person ('.ldap_errno($ldap).'): '.ldap_error($ldap).': id '.$id);

		return $r;
	}
	return FALSE;
}//ko_ldap_del_person()




/**
  * LDAP: Check for a person
	*/
function ko_ldap_check_person(&$ldap, $id) {
	global $ldap_dn;

	if($ldap) {
		$result = ldap_search($ldap, $ldap_dn, "uid=".$id);
		$num = ldap_count_entries($ldap, $result);

		if($num >= 1) return TRUE;
		else return FALSE;
	}
	return FALSE;
}//ko_ldap_check_person()




/**
  * LDAP: Check for a login
	*/
function ko_ldap_check_login(&$ldap, $cn) {
	global $ldap_login_dn;

	if($ldap) {
		$result = ldap_search($ldap, $ldap_login_dn, 'cn='.$cn);
		$num = ldap_count_entries($ldap, $result);

		if($num >= 1) return TRUE;
		else return FALSE;
	}
	return FALSE;
}//ko_ldap_check_login()




/**
  * LDAP: Add Login Entry
	*/
function ko_ldap_add_login(&$ldap, $data) {
	global $ldap_login_dn, $LDAP_SCHEMA;

	if($ldap) {
		//Data vorbehandeln, damit es mit z.B. Umlauten keine Probleme gibt.
		foreach($data as $i => $d) {
			if($d == "") $data[$i] = array();
			else if($i == "userPassword") $data[$i] = '{md5}'.base64_encode(pack('H*', $d));
			else $data[$i] = $d;
		}
		$data['objectclass'] = $LDAP_SCHEMA;

		$r = ldap_add($ldap, ('cn='.$data["cn"].','.$ldap_login_dn), $data);

		//Error handling for ldap operations
		if($r === FALSE) ko_log('ldap_error', 'LDAP add_login ('.ldap_errno($ldap).'): '.ldap_error($ldap).': '.print_r($data, TRUE));

		return $r;
	}
	return FALSE;
}//ko_ldap_add_login()





/**
  * LDAP: Delete Login Entry
	*/
function ko_ldap_del_login(&$ldap, $cn) {
	global $ldap_login_dn;

	if($ldap) {
		$r = ldap_delete($ldap, ('cn='.$cn.','.$ldap_login_dn));

		//Error handling for ldap operations
		if($r === FALSE) ko_log('ldap_error', 'LDAP del_login ('.ldap_errno($ldap).'): '.ldap_error($ldap).': cn '.$cn);

		return $r;
	}
	return FALSE;
}//ko_ldap_del_login()





/**
  * Speichert SMS-Balance von ClickaTell-Account in DB (Caching)
	*/
function set_cache_sms_balance($balance) {
	db_update_data('ko_settings', "WHERE `key` = 'cache_sms_balance'", array('value' => $balance));;
}


/**
 * Holt den gecachten SMS-Balance-Wert
 */
function get_cache_sms_balance() {
	global $db_connection;

	$query = "SELECT `value` FROM `ko_settings` WHERE `key` = 'cache_sms_balance'";
	$result = mysqli_query($db_connection, $query);
	$value = mysqli_fetch_assoc($result);
	return $value["value"];
}




/**
 * Send SMS message using aspsms.net
 */
function send_aspsms($recipients, $text, $from, &$num, &$credits) {
	global $SMS_PARAMETER, $BASE_PATH;

	require_once($BASE_PATH.'inc/aspsms.php');

	//Sender ID
  $originator = 'kOOL';  //Default value
	$sender_ids = explode(',', ko_get_setting('sms_sender_ids'));
	if(sizeof($sender_ids) > 0) {  //Check for sender_ids
		if(in_array($from, $sender_ids)) $originator = $from;
	}

	$sent = array();

	$sms = new SMS($SMS_PARAMETER['user'], $SMS_PARAMETER['pass']);
	$sms->setOriginator($originator);
	foreach($recipients as $r) {
		if(!check_natel($r)) continue;
		$sms->addRecipient($r);
		$sent[] = $r;
	}
	$sms->setContent($text);
	$error = $sms->sendSMS();

	if($error != 1) {
		$error_message = $sms->getErrorDescription();
		$my_error_txt = $error . ': ' . $error_message;
		koNotifier::Instance()->addTextError($my_error_txt);
		return FALSE;
	}
	$num = sizeof($sent);
	$credits = $sms->getCreditsUsed();
	$log_message = format_userinput(strtr($text, array("\n"=>' ', "\r"=>'')), 'text').' - '.implode(', ', $sent).' - '.$num.'/'.$num.' - 0 - '.$credits;
	ko_log('sms_sent', $log_message);

	set_cache_sms_balance($sms->showCredits());

	return TRUE;
}//send_aspsms()




/**
  * Sendet SMS-Mitteilung
	*/
function send_sms($recipients, $text, $from, $climsgid, $msg_type, &$success, &$done, &$problems, &$charges, &$error_message) {
	global $SMS_PARAMETER, $BASE_PATH;

	require($BASE_PATH."inc/Clickatell.php");
  set_time_limit(0);

	//Text
  $sms_message["text"] = $text;

  //Sender ID
  $sms_message["from"] = "kOOL";  //Default value
	$sender_ids = explode(',', ko_get_setting('sms_sender_ids'));
	if(sizeof($sender_ids) > 0) {  //Check for sender_id
		if(in_array($from, $sender_ids)) $sms_message['from'] = $from;
	}

  //Client-Message-ID
  $sms_message["climsgid"] = $climsgid;

  //Message-Type
  $sms_message["msg_type"] = $msg_type;


	$done = $success = $charges = 0;
	$problems = "";
  $sms = new SMS_Clickatell;
  $sms->init($SMS_PARAMETER);
  $log_message = "";


  if($sms->auth($error_message)) {
    foreach($recipients as $e) {
      if(check_natel($e)) {
        $sms_message["to"] = $e;
        $status = $sms->sendmsg($sms_message);

				//Get Api-Msg-ID and store it
        $temp = explode(" ", $status[1]);
        $apimsgid = $temp[0];

				//Get Status and Charge of sent SMS
        $charge = $sms->getmsgcharge($apimsgid);     //Array ( [0] => 1.5 [1] => 003 )
				//002: Queued, 003: Sent, 004: Received, 008: OK
        if(in_array($charge[1], array("002", "003", "004", "008"))) {
					$success++;
        } else {
          $problems .= $e.", ";
        }
        $charges += $charge[0];
        $done++;
        $log_message .= $e.", ";
      }//if(check_natel(e))
    }//foreach(empfaenger as e)

		//Neue Balance speichern
    set_cache_sms_balance($sms->getbalance());

		$log_message = format_userinput(strtr($sms_message['text'], array("\n" => ' ', "\r" => '')), 'text') . ' - ' . substr($log_message, 0, -2) . " - $success/$done - " . substr($problems, 0, -2) . " - $charges";
    ko_log("sms_sent", $log_message);

		return TRUE;
  }//if(sms->auth())
  else {
    return FALSE;
	}
}//send_sms()





/**
 * ezmlm mailinglist management
 */
function ko_ezmlm_subscribe($list, $moderator, $email) {
	if($list == "" || $moderator == "" || !check_email($email)) return FALSE;
	ko_send_mail($moderator, str_replace("@", "-subscribe-".str_replace("@", "=", $email)."@", $list), ' ', ' ');
	//ko_send_email(str_replace("@", "-subscribe-".str_replace("@", "=", $email)."@", $list), " ", " ", array("From" => $moderator));
}//ko_ezmlm_subscribe()

function ko_ezmlm_unsubscribe($list, $moderator, $email) {
	if($list == "" || $moderator == "" || !check_email($email)) return FALSE;
	ko_send_mail($moderator, str_replace("@", "-unsubscribe-".str_replace("@", "=", $email)."@", $list), ' ', ' ');
	//ko_send_email(str_replace("@", "-unsubscribe-".str_replace("@", "=", $email)."@", $list), " ", " ", array("From" => $moderator));
}//ko_ezmlm_unsubscribe()




/**
 * Find contrast color for given background color.
 * Based on YIQ color space
 * Found on http://24ways.org/2010/calculating-color-contrast/
 */
function ko_get_contrast_color($hexcolor, $dark = '#000000', $light = '#FFFFFF') {
	$r = hexdec(substr($hexcolor,0,2));
	$g = hexdec(substr($hexcolor,2,2));
	$b = hexdec(substr($hexcolor,4,2));
	$yiq = (($r*299)+($g*587)+($b*114))/1000;
	return ($yiq >= 128) ? $dark : $light;

	//$sum3 = hexdec(substr($hexcolor, 0, 2)) + 1.6*hexdec(substr($hexcolor, 2, 2)) + hexdec(substr($hexcolor, 4, 2));
  //return ($sum3 > 3*127 || $hexcolor == '') ? $dark : $light;

	//$sum3 = hexdec(substr($hexcolor, 0, 2)) + hexdec(substr($hexcolor, 2, 2)) + hexdec(substr($hexcolor, 4, 2));
  //return ($sum3 > 3*0x000088 || $hexcolor == "") ? $dark : $light;
}




function ko_scheduler_set_next_call($task) {
	global $ko_path;

	if(!is_array($task)) {
		$task = db_select_data('ko_scheduler_tasks', "WHERE `id` = '".intval($task)."'", '*', '', '', TRUE);
	}
	if(!$task['crontime']) return FALSE;

	if($task['status'] == 0) {
		db_update_data('ko_scheduler_tasks', "WHERE `id` = '".$task['id']."'", array('next_call' => '0000-00-00 00:00:00'));
	} else {
		require_once($ko_path.'inc/cron.php');
		try {
			$cron = Cron\CronExpression::factory($task['crontime']);
			$next_call = $cron->getNextRunDate()->format('Y-m-d H:i:s');
		} catch (Exception $e) {
			//Disable task
			db_update_data('ko_scheduler_tasks', "WHERE `id` = '".$task['id']."'", array('next_call' => '0000-00-00 00:00:00', 'status' => '0'));
			//Return error
			return 8;
		}

		if($next_call && $next_call != '0000-00-00 00:00:00') {
			db_update_data('ko_scheduler_tasks', "WHERE `id` = '".$task['id']."'", array('next_call' => $next_call));
		}
	}

}//ko_scheduler_set_next_call()





/**
 * Scheduler task: Delete old files in download folder
 */
function ko_task_delete_old_downloads() {
	global $ko_path;

	$deadline = 60*60*24;
	clearstatcache();

	$dirs = array($ko_path.'download/pdf/',
								$ko_path.'download/dp/',
								$ko_path.'download/excel/',
								$ko_path.'download/word/',
								$ko_path.'download/');


	//Delete old files
	foreach($dirs as $dir) {
		$dh = opendir($dir);
		while($file = readdir($dh)) {
			if(!is_file($dir.$file)) continue;  //Only check files and ignore dirs and links
			if(substr($file, 0, 1) == '.') continue;  //Ignore hidden files and ./..
			if($file == 'index.php') continue;  //Ignore index.php files

			$stat = stat($dir.$file);
			if((time()-$stat['mtime']) > $deadline) {
				unlink($dir.$file);
			}
		}
		closedir($dh);
	}
}//ko_task_delete_old_downloads()





/**
 * Scheduler task: Import/update events for event groups with iCal import URL
 */
function ko_task_import_events_ical() {
	global $ko_path, $BASE_PATH;

	require_once($ko_path.'daten/inc/daten.inc.php');

	//Get event groups to be imported
	$egs = db_select_data('ko_eventgruppen', "WHERE `type` = '3' AND `ical_url` != ''");
	if(sizeof($egs) == 0) return;

	foreach($egs as $eg) {
		//Apply update interval
		$update = $eg['update'] ? $eg['update'] : 60;
		if((strtotime($eg['last_update']) + 60*$update) > time()) continue;

		db_update_data('ko_eventgruppen', "WHERE `id` = '".$eg['id']."'", array('last_update' => date('Y-m-d H:i:s')));
		ko_daten_import_ical($eg);
	}

	//Find and remove multiple entries
	$doubles = db_select_data('ko_event', "WHERE `import_id` != ''", "*, COUNT(id) AS num", 'GROUP BY import_id ORDER BY num DESC');
	$double_import_ids = array();
	foreach($doubles as $double) {
		if($double['num'] < 2) continue;
		$double_import_ids[] = $double['import_id'];
	}
	unset($doubles);
	foreach($double_import_ids as $ii) {
		if(!$ii) continue;
		$lowest = db_select_data('ko_event', "WHERE `import_id` = '$ii'", 'id', 'ORDER BY `id` ASC', 'LIMIT 0,1', TRUE);
		db_delete_data('ko_event', "WHERE `import_id` = '$ii' AND `id` != '".$lowest['id']."'");
	}

}//ko_task_import_events_ical()


/**
 * Scheduler task: Send reminder emails
 */
function ko_task_reminder($reminderId = null) {

	$eventPatterns = array();
	$eventReplacements = array();

	if ($reminderId === null) {
		$reminders = db_select_data('ko_reminder', 'where `status` = 1');
	}
	else {
		$reminders = db_select_data('ko_reminder', 'where `id` = ' . $reminderId);
	}
	foreach ($reminders as $reminder) {

		$filter = $reminder['filter'];
		$type = substr($filter, 0, 4);
		$value = substr($filter, 4);
		if (trim($value) == '' || trim($type) == '') continue;
		switch ($type) {
			case 'LEPR':
				// TODO: implement leute functionality
				break;
			case 'EVGR': // event group id
				$zWhere = ' AND `ko_event`.`eventgruppen_id` = ' . $value;
				break;
			case 'EVID': // event id
				$zWhere = ' AND `ko_event`.`id` = ' . $value;
				break;
			case 'CALE': // calendar id
				$egs = db_select_data('ko_eventgruppen', 'where `calendar_id` = ' . $value, 'id');
				$egsString = implode(',', array_keys($egs));
				$zWhere = (trim($egsString) == '' ? '' : ' AND `ko_event`.`eventgruppen_id` in (' . $egsString . ')');
				break;
			case 'EGPR': // event group preset
				if (substr($value, 0, 4) == '[G] ') {
					$egIdsString = ko_get_userpref('-1', substr($value, 4), 'daten_itemset');
				}
				else {
					$egIdsString = ko_get_userpref($_SESSION['ses_userid'], $value, 'daten_itemset');
				}

				if ($egIdsString === null) {
					$events = null; // TODO: maybe add warning that preset was not found
				}
				else {
					$egIdsString = $egIdsString[0]['value'];
					$zWhere = (trim($egIdsString) == '') ? '' : ' AND `ko_event`.`eventgruppen_id` in (' . $egIdsString . ')';
				}

				break;
		}


		if ($reminder['type'] == 1) {


			// Set db query filter that selects those events which correspond to the deadline (and all 'overdue' events by 23 hours)
			$deadline = $reminder['deadline'];
			if ($reminderId !== null) {
				$limit = ' limit 1';
				if ($deadline >=0) {
					$order = ' order by enddatum asc, endzeit asc';
					$timeFilterEvents = " AND TIMESTAMPDIFF(HOUR,CONCAT(CONCAT(`ko_event`.`enddatum`, ' '), `ko_event`.`endzeit`), NOW()) <= " . ($deadline + 24);
				}
				else {
					$order = ' order by startdatum asc, startzeit asc';
					$timeFilterEvents = " AND TIMESTAMPDIFF(HOUR,CONCAT(CONCAT(`ko_event`.`startdatum`, ' '), `ko_event`.`startzeit`),NOW()) <= " . ($deadline + 24);
				}
			}
			else {
				$limit = '';
				$order = '';
				if ($deadline >=0) {
					$timeFilterEvents = " AND TIMESTAMPDIFF(HOUR,CONCAT(CONCAT(`ko_event`.`enddatum`, ' '), `ko_event`.`endzeit`), NOW()) >= " . $deadline . " AND TIMESTAMPDIFF(HOUR,CONCAT(CONCAT(`ko_event`.`enddatum`, ' '), `ko_event`.`endzeit`), NOW()) <= " . ($deadline + 23);
				}
				else {
					$timeFilterEvents = " AND TIMESTAMPDIFF(HOUR,CONCAT(CONCAT(`ko_event`.`startdatum`, ' '), `ko_event`.`startzeit`),NOW()) >= " . $deadline . " AND TIMESTAMPDIFF(HOUR,CONCAT(CONCAT(`ko_event`.`startdatum`, ' '), `ko_event`.`startzeit`),NOW()) <= " . ($deadline + 23);
				}
			}

			$events = db_select_data('ko_event', 'where 1=1 ' . $zWhere . $timeFilterEvents, "*", $order, $limit);


			// No reminders to send for this reminder entry
			if ($events === null) {
				if ($reminderId !== null) koNotifier::Instance()->addError(11);
				continue;
			}

			$recipientsByKool = array();
			$recipientsByAddress = array();
			if ($reminderId === null) {
				// Kick events for which the reminder has already been sent
				$eventIds = array_keys($events);
				$zWhere = ' AND `reminder_id` = ' . $reminder['id'];
				$zWhere .= (sizeof($eventIds) == 0 ? '' : ' AND `event_id` in (' . implode(',', $eventIds) . ')');
				$alreadyHandledEvents = db_select_data('ko_reminder_mapping', 'where 1=1 ' . $zWhere, 'event_id');
				foreach ($alreadyHandledEvents as $k => $alreadyHandledEvent) {
					unset($events[$k]);
				}

				// Process recipients
				$recipientsFromDBMails = explode(',', $reminder['recipients_mails']);
				$recipientsFromDBGroups = explode(',', $reminder['recipients_groups']);
				$recipientsFromDBLeute = explode(',', $reminder['recipients_leute']);

				foreach ($recipientsFromDBMails as $recipientFromDBMail) {
					if(!check_email($recipientFromDBMail)) continue;
					$recipientsByAddress[] = $recipientFromDBMail;
				}
				foreach ($recipientsFromDBGroups as $recipientFromDBGroup) {
					if(!$recipientFromDBGroup || strlen($recipientFromDBGroup) != 7) continue;

					$res = db_select_data('ko_leute', "where `groups` like '%" . $recipientFromDBGroup . "%'");
					foreach ($res as $person) {
						$recipientsByKool[$person['id']] = $person;
					}
				}
				foreach ($recipientsFromDBLeute as $recipientsFromDBPerson) {
					if(!intval($recipientsFromDBPerson)) continue;

					$person = null;
					ko_get_person_by_id($recipientsFromDBPerson, $person);
					$recipientsByKool[$person['id']] = $person;
				}
			}
			else {
				$user = ko_get_logged_in_person();
				$user['id'] = ko_get_logged_in_id();
				$recipientsByKool[$user['id']] = $user;
			}


			$text = '<body>' . $reminder['text'] . '</body>';
			$subject = $reminder['subject'];

			// Get placeholders for recipients
			foreach ($recipientsByKool as $recipientByKool) {
				if (!isset($personPatterns[$recipientByKool['id']])) {
					$personPlaceholders = ko_placeholders_leute_array($recipientByKool);
					$personPatterns[$recipientByKool['id']] = array();
					$personReplacements[$recipientByKool['id']] = array();
					foreach ($personPlaceholders as $k => $personPlaceholder) {
						$personPatterns[$recipientByKool['id']][] = '/' . $k . '/';
						$personReplacements[$recipientByKool['id']][] = $personPlaceholder;
					}
				}
			}

			// Get placeholders for events
			foreach ($events as $event) {
				if (!isset($eventPatterns[$event['id']])) {
					$eventPlaceholders = ko_placeholders_event_array($event);
					$eventPatterns[$event['id']] = array();
					$eventReplacements[$event['id']] = array();
					foreach ($eventPlaceholders as $k => $eventPlaceholder) {
						$eventPatterns[$event['id']][] = '/' . $k . '/';
						$eventReplacements[$event['id']][] = $eventPlaceholder;
					}
				}
			}


			// Send mails
			$done = array();
			$failed = array();
			$textWithoutPlaceholders = preg_replace('/###.*###/', '', $text);
			$subjectWithoutPlaceholders = preg_replace('/###.*###/', '', $subject);
			foreach ($events as $event) {
				foreach ($recipientsByKool as $recipientByKool) {
					$replacedSubject = preg_replace($eventPatterns[$event['id']], $eventReplacements[$event['id']], $subject);
					$replacedSubject = preg_replace($personPatterns[$recipientByKool['id']], $personReplacements[$recipientByKool['id']], $replacedSubject);

					$replacedText = preg_replace($eventPatterns[$event['id']], $eventReplacements[$event['id']], $text);
					$replacedText = preg_replace($personPatterns[$recipientByKool['id']], $personReplacements[$recipientByKool['id']], $replacedText);

					if ($reminder['action'] == 'email') {
						$mailAddresses = null;
						ko_get_leute_email($recipientByKool, $mailAddresses);
						foreach ($mailAddresses as $mailAddress) {
							$lip = ko_get_logged_in_person();
							$result = ko_send_html_mail($lip['email'], $mailAddress, $replacedSubject, $replacedText);
							if ($result) {
								$done[$mailAddress] = $recipientByKool['nachname'] . ' ' . $recipientByKool['vorname'] . ' (' . $recipientByKool['id'] . '):' . $mailAddress;
							}
							else {
								$failed[$mailAddress] = $recipientByKool['nachname'] . ' ' . $recipientByKool['vorname'] . ' (' . $recipientByKool['id'] . '):' . $mailAddress;
							}
						}
					}
					else if ($reminder['action'] == 'sms') {
						// TODO: add implementation
					}
				}
				foreach ($recipientsByAddress as $recipientByAddress) {
					if ($reminder['action'] == 'email') {
						if (array_key_exists($recipientByAddress, $done)) continue;
						$lip = ko_get_logged_in_person();
						$result = ko_send_html_mail($lip['email'], $recipientByAddress, $subjectWithoutPlaceholders, $textWithoutPlaceholders);
						if ($result) {
							$done[$recipientByAddress] = $recipientByAddress;
						}
						else {
							$failed[$recipientByAddress] = $recipientByAddress;
						}
					}
					else if ($reminder['action'] == 'sms') {
						// TODO: add implementation
					}
				}

				if ($reminderId === null) {
					// Insert entry into ko_reminder_mapping, so the reminder won't be sent again
					db_insert_data('ko_reminder_mapping', array('reminder_id' => $reminder['id'], 'event_id' => $event['id'], 'crdate' => date('Y-m-d H:i:s')));
				}
			}

			// Log
			ko_log('send_reminders', 'sent the following reminders' . ($reminderId === null ? '' : ' (testmail)') . ' :: reminder: ' . $reminder['id'] . '; events: ' . implode(',', array_keys($events)) . '; people success: ' . implode(',', $done) . '; people failed: ' . implode(',', $failed), '; subject: ' . $subject . '; text: ' . $text);
		}
		else if ($reminder['type'] == 2) {
			// TODO: implement leute functionality
		}
	}

	return $done;

}//ko_task_reminder()


/**
 * @param $person
 * @return array
 */
function ko_placeholders_leute_array($person, $prefixPerson = 'r_', $prefixUser = 's_', $tag = '###') {
	global $DATETIME;

	$map = array();

	//Address fields of a person
	foreach($person as $k => $v) {
		$map[$tag . $prefixPerson . mb_strtolower($k).$tag] = $v;
	}

	// Salutations
	$geschlechtMap = array('Herr' => 'm', 'Frau' => 'w');
	$vorname = trim($person['vorname']);
	$nachname = trim($person['nachname']);
	$geschlecht = $person['geschlecht'] != '' ? $person['geschlecht'] : $geschlechtMap[$person['anrede']];
	$map[$tag . $prefixPerson . '_salutation_formal_name' . $tag] = getLL('mailing_salutation_formal_' . ($nachname != '' ? $geschlecht : '')) . ($nachname == '' ? '' : ' ' . $nachname);
	$map[$tag . $prefixPerson . '_salutation_name' . $tag] = getLL('mailing_salutation_' . ($vorname != '' ? $geschlecht : '')) . ($vorname == '' ? '' : ' ' . $vorname);

	//Salutation
	$map[$tag . $prefixPerson . '_salutation' . $tag] = getLL('mailing_salutation_'.$person['geschlecht']);
	$map[$tag . $prefixPerson . '_salutation_formal' . $tag] = getLL('mailing_salutation_formal_'.$person['geschlecht']);


	//Add current date
	$map[$tag . 'date' . $tag] = strftime($DATETIME['dMY'], time());
	$map[$tag . 'date_dmY' . $tag] = strftime($DATETIME['dmY'], time());

	//Add contact fields (from general settings)
	$contact_fields = array('name', 'address', 'zip', 'city', 'phone', 'url', 'email');
	foreach($contact_fields as $field) {
		$map[$tag . 'contact_'.mb_strtolower($field).$tag] = ko_get_setting('info_'.$field);
	}

	//Add sender fields of current user
	$sender = ko_get_logged_in_person();
	foreach($sender as $k => $v) {
		$map[$tag . $prefixUser .mb_strtolower($k).$tag] = $v;
	}

	return $map;
}//ko_placeholders_leute_array()


/**
 * @param $event
 * @return array
 */
function ko_placeholders_event_array($event, $prefix = 'e_', $tag = '###') {
	global $DATETIME;

	$map = array();

	//Fields of event
	foreach($event as $k => $v) {
		$map[$tag . $prefix .mb_strtolower($k).$tag] = $v;
	}

	$startDatetime = strtotime($map[$tag . $prefix . 'startdatum' . $tag] . ' ' . $map[$tag . $prefix . 'startzeit' . $tag]);
	$endDatetime = strtotime($map[$tag . $prefix . 'enddatum' . $tag] . ' ' . $map[$tag . $prefix . 'endzeit' . $tag]);

	$map[$tag . $prefix . 'startdatum' . $tag] = strftime($DATETIME['dMY'], $startDatetime);
	$map[$tag . $prefix . 'enddatum' . $tag] = strftime($DATETIME['dMY'], $endDatetime);
	$map[$tag . $prefix . 'startzeit' . $tag] = strftime("%H:%M", $startDatetime);
	$map[$tag . $prefix . 'endzeit' . $tag] = strftime("%H:%M", $endDatetime);
	if (trim($event['eventgruppen_id']) != '') {
		$eventGroupName = db_select_data('ko_eventgruppen', 'where id = ' . $event['eventgruppen_id'], 'name', '', '', TRUE, TRUE);
		$map[$tag . $prefix . 'eventgruppe' . $tag] = $eventGroupName['name'];
	}

	return $map;
}//ko_placeholders_event_array()



/*
 * Scheduler task: process mails which were sent to groups ...
 */
function ko_task_mailing() {
	global $ko_path;

	require_once($ko_path.'mailing.php');
	ko_mailing_main();
}//ko_task_mailing()



/**
	* Überprüft und korrigiert ein Datum
	* Basiert auf PEAR-Klasse zur Überprüfung der Richtigkeit des Datums
	*/
function check_datum(&$d) {
	$d = format_userinput($d, "date");

	get_heute($tag, $monat, $jahr);

	//Trennzeichen testen (. oder ,)
  $date_ = explode(".", $d);
  $date__ = explode(",", $d);
  $date___ = explode("-", $d);
	if(sizeof($date_) >= 2) $date = $date_;
	else if(sizeof($date__) >= 2) $date = $date__;
	else if(sizeof($date___) >= 2) {  //SQL-Datum annehmen
		$date = $date___;
		$temp = $date[0]; $date[0] = $date[2]; $date[2] = $temp;
	}

	//Angaben ohne Jahr erlauben, dann einfach aktuelles Jahr einfügen
	if(sizeof($date) == 2) $date[2] = $jahr;
	if($date[2] == "") $date[2] = $jahr;  //Falls noch kein Jahr gefunden, dann einfach auf aktuelles setzen

	//Jahr auf vier Stellen ergänzen, falls nötig (immer 20XX verwenden)
	if(strlen($date[2]) == 2) $date[2] = (int)("20".$date[2]);
	else if(strlen($date[2]) == 1) $date[2] = (int)("200".$date[2]);

	$d = strftime('%d.%m.%Y', mktime(1,1,1, $date[1], $date[0], $date[2]));
	return ($date[0] > 0 && $date[1] > 0 && $date[2] > 0);
}//check_datum()


/**
	* Überprüft eine Zeit auf syntaktische Richtigkeit
	*/
function check_zeit(&$z) {
	$z = format_userinput($z, "date");

  $z_1 = explode(":", $z);
  $z_2 = explode(".", $z);
  if(sizeof($z_1) == 2) $z_ = $z_1;
  else if(sizeof($z_2) == 2) $z_ = $z_2;
  else $z_ = explode(":", ($z . ":00"));

	$z = implode(":", $z_);
  if($z_ != "" && $z_[0] >= 0 && $z_[0] <= 24 && $z_[1] >=0 && $z_[1] <=60) return true;
  else return false;
}//check_zeit()


/**
  * Überprüft auf syntaktisch korrekte Emailadresse
	*/
function check_email($email) {
	$email = trim($email);
	if(strpos($email, ' ') !== FALSE) {
		return FALSE;
	}
	return preg_match('^[A-Za-z0-9\._-]+[@][A-Za-z0-9\._-]+[\.].[A-Za-z0-9]+$', $email) ? TRUE : FALSE;
}//check_email()


/**
  * Formatiert eine Natelnummer ins internationale Format für clickatell
	*/
function check_natel(&$natel) {
	if(trim($natel) == "") return FALSE;

	$natel = format_userinput($natel, "uint");

	//Ignore invalid numbers (e.g. strings)
	if($natel == '') return FALSE;
	//Check for min/max length for a reasonable mobile number
	if(strlen($natel) < 9 OR strlen($natel) > 18) return FALSE;

	if(substr($natel, 0, 2) == '00') {  //Area code given as 00XY
		$natel = substr($natel, 2);
	} else if(substr($natel, 0, 1) == "0") {
		$natel = ko_get_setting("sms_country_code").substr($natel, 1);
	}
	if($natel) return TRUE;
	else return FALSE;
}//check_natel()



/**
	* Fügt einem String eine "0" vorne hinzu, falls der String nur 1 Zeichen enthält
	*/
function str_to_2($s) {
	while(strlen($s) < 2) {
		$s = "0" . $s;
	}
	return $s;
}


function zerofill($s, $l) {
	while(strlen($s) < $l) {
		$s = '0'.$s;
	}
	return $s;
}


/**
	* Wandelt die angegebene Zeit in eine SQL-Zeit um
	*/
function sql_zeit($z) {
  if($z != '') {
		$z = str_replace(array('.', ',', ' '), array(':', ':', ''), $z);
    $z_1 = explode(':', $z);
		switch(sizeof($z_1)) {
			case 1: $r = $z.':00'; break;
			case 2: $r = $z; break;
			case 3: $r = substr($z, 0, -3); break;
		}
  } else {
    $r = '';
	}
	if($r == '00:00') $r = '';
  return format_userinput($r, 'date');
}//sql_zeit()



/**
 * Wandelt das angegebene Datum in ein SQL-Datum um
 */
function sql_datum($d) {
	//Testen, ob Datum schon im SQL-Format ist:
	$temp = explode("-", $d);
	if(sizeof($temp) == 3) return format_userinput($d, "date");

	if(!empty($d)) {
    	$date = explode(".", $d);
		$r = $date[2] . "-" . $date[1] . "-" . $date[0];
	} else {
    	$r = '0000-00-00';
	}
	return format_userinput($r, "date");
}//sql_datum()


/**
 * Converts an SQL DATE to a string with format DD.MM.YYYY.
 */
function sql2datum($s) {
	if (empty($s) || $s == '0000-00-00')
		return ''; // Return empty string for zero dates
	$s_ = explode("-", $s);
	if (sizeof($s_) == 3) {
		$r = $s_[2].".".$s_[1].".".$s_[0];
  		return $r;
	} else {
		return $s;
	}
}


function sql2datetime($s) {
	global $DATETIME;

	if (empty($s) || $s == '0000-00-00 00:00:00')
		return ''; // Return empty string for zero datetimes
	$ts = strtotime($s);
	if ($ts > 0) {
		return strftime($DATETIME['dmY'].' %H:%M', $ts);
	} else {
		return $s;
	}
}



/**
 * Converts an SQL date (YYYY-MM-DD) into a unix timestamp
 */
function sql2timestamp($s) {
	if($s=="" || $s=="0000-00-00") return "";
	else return strtotime($s);
}//sql2timestamp()



/**
	* Wandelt ein SQL-DateTime ins Format TG.MT.JAHR hh:mm:ss um
	*/
function sqldatetime2datum($s) {
	if($s=="" || $s=="0000-00-00 00:00:00") return "";
	$temp = explode(" ", $s);
	$date = $temp[0];
	$time = $temp[1];

	$s_ = explode("-", $date);
	if(sizeof($s_) == 3) {
		$r = $s_[2].".".$s_[1].".".$s_[0];
  	return $r." ".$time;
	} else {
		return $s;
	}
}//sqldatetime2datum()


/**
	* Addiert zu einem angegebenen Datum s Monate (inkl. Überlauf-Check)
	*/
function addmonth(&$m, &$y, $s) {
  $m = (int)$m; $y = (int)$y; $s = (int)$s;

  $m += $s;
  while($m < 1) {
    $m += 12;
    $y--;
	}
	while($m > 12) {
    $m -= 12;
    $y++;
  }
}//addmonth()


/**
	* Liefert Tag, Monat und Jahr des aktuellen Datums
	*/
function get_heute(&$t, &$m, &$j) {
  $heute = getdate(time());
  $t = $heute["mday"];
  $m = $heute["mon"];
  $j = $heute["year"];
}


/**
	* Formaitert eine Emailadresse für die anzeige im Web
	*/
function format_email($m) {
	return strtr($m, array('@' => ' (at) ', '.' => ' (dot) '));
}//format_email()


/**
 * Removes all dangerous chars from user input. Used e.g. to save filters.
 * @param string $s Raw user input. An empty string will be converted to the default value for numeric types.
 * @param string $type Desired type which defines allowed chars.
 * @param bool $enforce Return false on rule violations.
 * @param mixed $length The maximum length. Prepend an '=' sign to specify an exact length.
 * @param string $add_own Additional allowed chars.
 */
function format_userinput($s, $type, $enforce=FALSE, $length=0, $replace=array(), $add_own="") {
	if(!empty($replace['umlaute'])) $s = strtr($s, array('ä'=>'a','ö'=>'o','ü'=>'u','é'=>'e','è'=>'e','à'=>'a','Ä'=>'A','Ö'=>'O','Ü'=>'U'));

	if(!empty($replace['singlequote']) || !empty($replace["allquotes"])) $s = strtr($s, array("'" => '', '`' => ''));
	if(!empty($replace["doublequote"]) || !empty($replace["allquotes"])) $s = str_replace('"', "", $s);
	if(!empty($replace["backquote"]) || !empty($replace["allquotes"])) $s = str_replace("`", "", $s);

	//Bei falscher Länge abbrechen
	if($length != 0) {
		if(substr($length, 0, 1) == "=") {  //Falls exakte Länge verlangt...
			if(strlen($s) != $length) {
				if($enforce) {
					$s = "";
					return FALSE;
				} else {
					$s = substr($s, 0, $length);
				}
			}
		} else {  //...sonst auf maximale Länge prüfen
			if(strlen($s) > $length) {
				if($enforce) {
					$s = "";
					return FALSE;
				} else {
					$s = substr($s, 0, $length);
				}
			}
		}
	}//if(length)

	//Type testen
	switch($type) {
		case "uint":
			$allowed = "1234567890";
			$default = '0';
		break;

		case "int":
			$allowed = "-1234567890";
			$default = '0';
		break;

		case "int@":
			$allowed = "1234567890@";
		break;

		case "intlist":
			$allowed = "1234567890,";
		break;

		case "alphanumlist":
			$allowed = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890_-,:;";
		break;

		case "float":
			$allowed = "-1234567890.";
			$default = '0.0';
		break;

		case "alphanum":
			$allowed = "abcdefghijklmnopqrstuvwxyzöäüABCDEFGHIJKLMNOPQRSTUVWXYZÖÄÜéèëàÉÀÈß1234567890";
		break;

		case "alphanum+":
			$allowed = "abcdefghijklmnopqrstuvwxyzöäüABCDEFGHIJKLMNOPQRSTUVWXYZÖÄÜéèëàÉÀÈß1234567890+-_";
		break;

		case "alphanum++":
			$allowed = "abcdefghijklmnopqrstuvwxyzöäüABCDEFGHIJKLMNOPQRSTUVWXYZÖÄÜéèëàÉÀÈß1234567890+-_ ";
		break;

		case "email":
			$allowed = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890.+-_@&";
		break;

		case "dir":
			$allowed = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZöäüÖÄÜß1234567890-_ ";
		break;

		case "js":
			$allowed = "abcdefghijklmnopqrstuvwxyzöäüABCDEFGHIJKLMNOPQRSTUVWXYZÖÄÜéèëàÉÀÈ1234567890-_?+&ß@.;:/'() ";
		break;

		case "alpha":
			$allowed = "abcdefghijklmnopqrstuvwxyzöäüABCDEFGHIJKLMNOPQRSTUVWXYZÖÄÜéèëàÉÀÈß";
		break;

		case "alpha+":
			$allowed = "abcdefghijklmnopqrstuvwxyzöäüABCDEFGHIJKLMNOPQRSTUVWXYZÖÄÜéàëèÉÀÈß+-_";
		break;

		case "alpha++":
			$allowed = "abcdefghijklmnopqrstuvwxyzöäüABCDEFGHIJKLMNOPQRSTUVWXYZÖÄÜéàëèÉÀÈß+-_ ";
		break;

		case "date":
			$allowed = "1234567890-.: ";
		break;

		case 'group_role':
			$allowed = "gr1234567890:,";
		break;

		case "text":
			return addslashes($s); // TODO: Review if some input is already escaped
		break;

		case "all":
			return TRUE;
		break;
	}//switch(type)

	// Empty strings are not allowed for numeric values in databases.
	if (empty($s) && isset($default)) return $default;
	
	if($add_own) $allowed .= $add_own;

	$new = "";
	for($i=0; $i<strlen($s); $i++) {
	    if(FALSE !== strstr($allowed, substr($s, $i, 1))) {
    		$new .= substr($s, $i, 1);
    	} else if($enforce) {
			return FALSE;  //Bei ungültigen Zeichen nur abbrechen, wenn enforce true ist.
		}
	}
	return $new;
}



/**
 * Formatiert Sonderzeichen in ihre HTML-Entsprechungen
 * Damit soll XSS in Formularen und sonst verhindert werden
 */
function ko_html($string) {
	return strtr($string, array(
		'&' => "&amp;",
		"'" => "&lsquo;",
		'"' => "&quot;",
		'>' => "&gt;",
		'<' => "&lt;",
		'ö' => "&ouml;",
		'ä' => "&auml;",
		'ü' => "&uuml;",
		'Ö' => "&Ouml;",
		'Ä' => "&Auml;",
		'Ü' => "&Uuml;"));
}//ko_html()



/**
  * Wendet ko_html-Funktion zweimal an. Z.B. für Overlib-Code
	*/
function ko_html2($string) {
	$r = ko_html(ko_html($string));
	return $r;
}



function ko_unhtml($string) {
	return strtr($string, array("&amp;" => "&",
		"&lsquo;" => "'",
		"&quot;" => '"',
		"&gt;" => '>',
		"&lt;" => '<',
		"&ouml;" => 'ö',
		"&auml;" => 'ä',
		"&uuml;" => 'ü',
		"&Ouml;" => 'Ö',
		"&Auml;" => 'Ä',
		"&Uuml;" => 'Ü',
		"&rsaquo;" => '',
		"&thinsp;" => '',
		"&nbsp;" => ' '));
}//ko_unhtml()



/**
 * Escapes a string in a way it can be decoded again using JavaScript's unescape()
 * Found on http://php.net/manual/de/function.urlencode.php
 */
function ko_js_escape($in) {
	$out = '';
	for($i=0; $i<strlen($in); $i++) {
		$hex = dechex(ord($in[$i]));
		if($hex=='') {
			$out = $out.urlencode($in[$i]);
		} else {
			$out = $out .'%'.((strlen($hex)==1) ? ('0'.mb_strtoupper($hex)):(mb_strtoupper($hex)));
		}
	}
	$out = str_replace('+','%20',$out);
	$out = str_replace('_','%5F',$out);
	$out = str_replace('.','%2E',$out);
	$out = str_replace('-','%2D',$out);
	return $out;
}//ko_js_escape()


function ko_js_save($s) {
	return str_replace("\r", '', str_replace("\n", '', nl2br(ko_html($s))));
}



/**
 * Bereitet einen Text für ein Email auf, indem jeder Zeile schliessende \n entfernt werden
 */
function ko_emailtext($input) {
	$lines = explode("\n", $input);
  $text = "";
  foreach($lines as $l) {
    $text .= rtrim($l)."\n";
  }
	return $text;
}//ko_emailtext()



/**
 * Trimmt jedes Element eines Arrays
 */
function array_trim($arr){
	unset($result);
	foreach($arr as $key => $value){
    if (is_array($value)) $result[$key] = array_trim($value);
    else $result[$key] = trim($value);
  }
  return $result;
}




/**
 * Erwartet Array eines Datums mit den Einträgen 0=>Tag, 1=>Monat, 2=>Jahr (4-stellig)
 * Gibt einen Code in der Form JJJJMMTT zurück
 * Geeignet für Int-Vergleiche von Daten
 */
function date2code($d) {
	$return = $d[2] . str_to_2($d[1]) . str_to_2($d[0]);
	return $return;
}

function code2date($d) {
	$r[2] = substr($d, 0, 4);
	$r[1] = substr($d, 4, 2);
	$r[0] = substr($d, 6, 2);
	return $r;
}


/**
 * Erwartet Datum als .-getrennter String, einen Modus (tag, woche, monat) und ein Inkrement
 * Gibt Datum als Array zurück (0=>Tag, 1=>Monat, 2=>Jahr)
 */
function add2date($datum, $mode, $inc, $sqlformat=FALSE) {
	if($sqlformat) {
		$d[0] = substr($datum, 8, 2);
		$d[1] = substr($datum, 5, 2);
		$d[2] = substr($datum, 0, 4);
	} else {
		if(is_array($datum)) $d = $datum;
		else $d = explode('.', $datum);
	}

	switch($mode) {
		case 'tag':
		case 'day':
			$d[0] = (int)$d[0] + (int)$inc;
		break;
		case 'woche':
		case 'week':
			$d[0] = (int)$d[0] + ( 7*(int)$inc );
		break;
		case 'monat':
		case 'month':
			$d[1] = (int)$d[1] + (int)$inc;
		break;
	}//switch(mode)

	//Ueberläufe korrigieren
	if($sqlformat) {
		$r = strftime('%Y-%m-%d', mktime(1, 1, 1, $d[1], $d[0], $d[2]));
	} else {
		$r_ = strftime('%d.%m.%Y', mktime(1, 1, 1, $d[1], $d[0], $d[2]));
		$r = explode('.', $r_);
	}
	return $r;
}



function date_get_days_between_dates($d1, $d2) {
	$c1 = str_replace('-', '', $d1);
	$c2 = str_replace('-', '', $d2);

	$diff = 0;
	while($c1 < $c2) {
		$d1 = add2date($d1, 'day', 1, TRUE);
		$c1 = str_replace('-', '', $d1);
		$diff++;
	}
	return $diff;
}



function add2time($time, $inc) {
	list($hour, $minute) = explode(':', $time);
	$new = 60*(int)$hour + (int)$minute + (int)$inc;
	return intval($new/60).':'.($new%60);
}//add2time()


/**
  * Liefert den ersten Montag vor dem übergebenen Datum (YYYY-MM-DD)
	*/
function date_find_last_monday($date) {
	$wd = date("w", strtotime($date));
	if($wd == 0) $wd = 7;
	$r = add2date($date, "tag", (-1*($wd-1)), TRUE);
	return $r;
}//date_find_last_monday()


function date_find_next_monday($date) {
	$wd = date('w', strtotime($date));
	if($wd == 0) $wd = 7;
	$r = add2date($date, 'day', ($wd-1), TRUE);
	return $r;
}//date_find_last_monday()



/**
  * Liefert den nächsten Sonntag nach dem übergebenen Datum (YYYY-MM-DD)
	*/
function date_find_next_sunday($date) {
	$wd = date("w", strtotime($date));
	if($wd == 0) $wd = 7;
	$r = add2date($date, "tag", (7-$wd), TRUE);
	return $r;
}//date_find_next_sunday()




function date_convert_timezone($date_str, $tz, $date_format = 'Ymd\THis\Z') {
	$time = strtotime($date_str);
	$strCurrentTZ = date_default_timezone_get();
	date_default_timezone_set($tz);
	$ret = date($date_format, $time);
	date_default_timezone_set($strCurrentTZ);
	return $ret;
}//date_convert_timezone()




function ko_calendar_mwselect($mode) {
	global $DATETIME;

	$r = '';

	if($mode == 'month' || $mode == 'resourceMonth') {
		$r .= '<select name="sel_mwselect" size="1" onchange="val=this.options[this.selectedIndex].value; sendReq(\'inc/ajax.php\', \'action,ymd\', \'fcsetdate,\'+val); $(\'#ko_calendar\').fullCalendar(\'gotoDate\', val.substring(0,4), (val.substring(5,7)-1), val.substring(8));">';
		$today = date('Y-m-').min(28, date('d'));
		$view_stamp = mktime(1,1,1, $_SESSION['cal_monat'], $_SESSION['cal_tag'], $_SESSION['cal_jahr']);
		$cur_month = date('m-Y');

		//Dynamically adjust select size
		$start = min(-12, floor(($view_stamp-time())/(3600*24*30)-12));
		$stop  = max(25, floor(($view_stamp-time())/(3600*24*30)+12));

		if($_SESSION['ses_userid'] == ko_get_guest_id()) {
			if(defined('GUEST_MWSELECT_MONTH_START')) $start = GUEST_MWSELECT_MONTH_START;
			if(defined('GUEST_MWSELECT_MONTH_STOP')) $stop = GUEST_MWSELECT_MONTH_STOP;
		}

		for($i=$start; $i<$stop; $i++) {
			$temp = strtotime(add2date($today, 'monat', $i, TRUE));
			$label = strftime('%m-%Y', $temp);
			$value = strftime('%Y-%m-%d', $temp);
			$sel = $label == strftime('%m-%Y', $view_stamp) ? 'selected="selected"' : '';
			//Mark today
			$mark = $label == $cur_month ? 'style="background: #c0c0c0; font-weight: bold;"' : '';
			$r .= '<option value="'.$value.'" '.$sel.' '.$mark.'>'.$label.'</option>';
		}
		$r .= '</select>';
	}
	else if($mode == 'agendaWeek' || $mode == 'resourceWeek') {
		$r .= '<select name="sel_mwselect" size="1" onchange="val=this.options[this.selectedIndex].value; sendReq(\'inc/ajax.php\', \'action,ymd\', \'fcsetdate,\'+val); $(\'#ko_calendar\').fullCalendar(\'gotoDate\', val.substring(0,4), (val.substring(5,7)-1), val.substring(8));">';
		$today = date('Y-m-d');
		$view_stamp = mktime(1,1,1, $_SESSION['cal_monat'], $_SESSION['cal_tag'], $_SESSION['cal_jahr']);
		$cur_week = strftime('%V-%G', time());

		//Dynamically adjust select size
		$start = min(-52, floor(($view_stamp-time())/(3600*24*7)-10));
		$stop  = max(105, floor(($view_stamp-time())/(3600*24*7)+10));

		if($_SESSION['ses_userid'] == ko_get_guest_id()) {
			if(defined('GUEST_MWSELECT_WEEK_START')) $start = GUEST_MWSELECT_WEEK_START;
			if(defined('GUEST_MWSELECT_WEEK_STOP')) $stop = GUEST_MWSELECT_WEEK_STOP;
		}

		for($i=$start; $i<$stop; $i++) {
			$temp = strtotime(add2date($today, 'woche', $i, TRUE));
			$label = strftime('%V-%G', $temp);
			$value = strftime('%Y-%m-%d', $temp);
			$sel = $label == strftime('%V-%G', $view_stamp) ? 'selected="selected"' : '';
			//Mark today
			$mark = $label == $cur_week ? 'style="background: #c0c0c0; font-weight: bold;"' : '';
			$r .= '<option value="'.$value.'" '.$sel.' '.$mark.'>'.$label.'</option>';
		}
		$r .= '</select>';
	}
	else if($mode == 'agendaDay' || $mode == 'resourceDay') {
		$r .= '<select name="sel_mwselect" size="1" onchange="val=this.options[this.selectedIndex].value; sendReq(\'inc/ajax.php\', \'action,ymd\', \'fcsetdate,\'+val); $(\'#ko_calendar\').fullCalendar(\'gotoDate\', val.substring(0,4), (val.substring(5,7)-1), val.substring(8));">';
		$today = date('Y-m-d');
		$view_stamp = mktime(1,1,1, $_SESSION['cal_monat'], $_SESSION['cal_tag'], $_SESSION['cal_jahr']);
		$cur_day = strftime($DATETIME['ddmy'], time());

		//Dynamically adjust select size
		$start = min(-60, floor(($view_stamp-time())/(3600*24)-30));
		$stop  = max(365, floor(($view_stamp-time())/(3600*24)+30));

		for($i=$start; $i<$stop; $i++) {
			$temp = strtotime(add2date($today, 'tag', $i, TRUE));
			$label = strftime($DATETIME['ddmy'], $temp);
			$value = strftime('%Y-%m-%d', $temp);
			$sel = $label == strftime($DATETIME['ddmy'], $view_stamp) ? 'selected="selected"' : '';
			//Mark sundays
			if(strftime('%w', $temp) == 0) $mark = 'style="background: #e6e6e6;"';
			//Mark today
			else if($label == $cur_day) $mark = 'style="background: #c0c0c0; font-weight: bold;"';
			else $mark = '';
			$r .= '<option value="'.$value.'" '.$sel.' '.$mark.'>'.$label.'</option>';
		}
		$r .= '</select>';
	}

	return $r;
}//ko_calendar_mwselect()



/**
	* Liefert wiederholte Termine nach verschiedenen Modi. (für Reservationen und Termine verwendet)
	* d1 und d2 sind Start- und Enddatum des einzelnen Anlasses (wiederholte mehrtägige Anlässe sind möglich)
	* repeat_mode enthält den Modus der Wiederholung (keine, taeglich, wochentlich, monatlich1, monatlich2)
	* bis_monat und bis_jahr stellen das Ende der Wiederholung dar (inkl.)
	*/
function ko_get_wiederholung($d1, $d2, $repeat_mode, $inc, $bis_tag, $bis_monat, $bis_jahr, &$r, $max_repeats='', $holiday_eg=0) {
	//Resultat-Array leeren
	$r = array();
	$num_repeats = 1;

	$d1 = format_userinput($d1, "date");
	$d2 = format_userinput($d2, "date");
	$repeat_mode = format_userinput($repeat_mode, "alphanum");
	$bis_tag = format_userinput($bis_tag, "uint");
	$bis_monat = format_userinput($bis_monat, "uint");
	$bis_jahr = format_userinput($bis_jahr, "uint");
	if(!$inc) $inc = 1;

	//Datum vorbereiten und Dauer für ein mehrtägiges Ereignis berechnen
	$d1e = explode(".", $d1);
	$sd_string = date2code($d1e);
	if($d2 != "") {
		$d2e = explode(".", $d2);
		$ed_string = date2code($d2e);
		$d_diff = (int)($sd_string - $ed_string);
	} else $d_diff = 0;

	//Enddatum in Code umwandeln
	if($max_repeats == "") {  //Keine Anzahl Wiederholungen angegeben --> Enddatum verwenden
		$max_repeats = 1000;
		$until_string = date2code(array($bis_tag, $bis_monat, $bis_jahr));
	} else {  //Sonst Anzahl Wiederholungen verwenden und Datum in ferne Zukunft legen
		$until_string = date2code(array("31", "12", "3000"));
	}

	switch($repeat_mode) {
		case "taeglich":
		case "woechentlich":
		case "monatlich2":  //Immer am gleichen Datum

			//Inkrement wird vor dem Aufruf richtig gesetzt, also muss nur noch definiert werden, was inkrementiert werden soll
			if($repeat_mode == "taeglich") $add_mode = "tag";
			else if($repeat_mode == "woechentlich") $add_mode = "woche";
			else if($repeat_mode == "monatlich2") $add_mode = "monat";

			$r[] = $d1;
			$r[] = $d2;
			$new_code1 = date2code(add2date($d1, $add_mode, $inc));
			$new_code2 = ($d_diff == 0) ? "" : date2code(add2date($d2, $add_mode, $inc));
			while($new_code1 <= $until_string && $num_repeats < $max_repeats) {
				$num_repeats++;

				$code1 = code2date($new_code1);
				$r[] = $code1[0] . "." . $code1[1] . "." . $code1[2];
				$code2 = code2date($new_code2);
				if($code2[0] != "") $r[] = $code2[0] . "." . $code2[1] . "." . $code2[2];
				else $r[] = "";

				$new_code1 = date2code(add2date(code2date($new_code1), $add_mode, $inc));
				$new_code2 = ($d_diff == 0) ? "" : date2code(add2date(code2date($new_code2), $add_mode, $inc));

			}//while(new_code1 < until_string)
		break;

		case "monatlich1":  //z.B. "Jeden 3. Montag"
			$nr_ = explode("@", $inc);
			$nr = $nr_[0];
			$tag = $nr_[1];

			// check if nr means every last xyz of month
			$everyLast = false;
			if ($nr == 6) {
				$nr = 5;
				$everyLast = true;
			}

			$erster = $d1e;
			$erster[0] = 1;
			$new_code = date2code($erster);

			while($new_code <= $until_string && $num_repeats <= $max_repeats) {
				$num_repeats++;
				$found = FALSE;
				while(!$found && $erster[0] < 8) {
					$wochentag = strftime("%w", mktime(1, 1, 1, $erster[1], $erster[0], $erster[2]));
					if($wochentag == $tag) $found = TRUE;
					else $erster[0] += 1;
				}
				$neues_datum = add2date($erster, "tag", ($nr-1)*7);

				// in case of 'every 5. xyz', check whether the current month has 5 xyz
				if ($nr < 5 || $neues_datum[0] > 28) {
					$r[] = $neues_datum[0] . "." . $neues_datum[1] . "." . $neues_datum[2];
					$neues_datum2 = add2date($neues_datum, "tag", $d_diff);
					$r[] = ($d_diff > 0) ? ($neues_datum2[0] . "." . $neues_datum2[1] . "." . $neues_datum2[2]) : "";
				}
				// in case of 'every last xyz' and that a month doesn't have 5 xyz, enter event at 4. xyz of month
				else if ($everyLast) {
					$neues_datum = add2date($neues_datum, "tag", -7);
					$r[] = $neues_datum[0] . "." . $neues_datum[1] . "." . $neues_datum[2];
					$neues_datum2 = add2date($neues_datum, "tag", $d_diff);
					$r[] = ($d_diff > 0) ? ($neues_datum2[0] . "." . $neues_datum2[1] . "." . $neues_datum2[2]) : "";
				}

				$erster[0] = 1;
				$erster = add2date($erster, "monat", 1);
				$new_code = date2code($erster);

			}//while(new_code < until_string)

		break;

		default:  //case 'keine'
			$r[] = $d1;
			$r[] = $d2;
	}//switch(repeat_mode)


	//Exclude repetition dates that collide with holiday eventgroup
	if($holiday_eg > 0) {
		$first = $r[0];
		$min = substr($first, -4).'-'.substr($first, 3, 2).'-'.substr($first, 0, 2);
		$last = $r[sizeof($r)-1] ? $r[sizeof($r)-1] : $r[sizeof($r)-2];
		$max = substr($last, -4).'-'.substr($last, 3, 2).'-'.substr($last, 0, 2);
		$holidays = db_select_data('ko_event', "WHERE `eventgruppen_id` = '$holiday_eg' AND `enddatum` >= '$min' AND `startdatum` <= '$max'");
		$holiday_days = array();
		foreach($holidays as $day) {
			$start = $day['startdatum'];
			$stop = $day['enddatum'];
			while(str_replace('-', '', $stop) >= str_replace('-', '', $start)) {
				$holiday_days[] = strftime('%d.%m.%Y', strtotime($start));
				$start = add2date($start, 'day', 1, TRUE);
			}
		}
		for($i=0; $i<sizeof($r); $i+=2) {
			$dstart = substr($r[$i], -4).'-'.substr($r[$i], 3, 2).'-'.substr($r[$i], 0, 2);
			if($r[$i+1] == '') {
				$dstop = $dstart;
			} else {
				$dstop = substr($r[$i+1], -4).'-'.substr($r[$i+1], 3, 2).'-'.substr($r[$i+1], 0, 2);
			}
			$del = FALSE;
			while(str_replace('-', '', $dstart) <= str_replace('-', '', $dstop)) {
				if(in_array(strftime('%d.%m.%Y', strtotime($dstart)), $holiday_days)) $del = TRUE;
				$dstart = add2date($dstart, 'day', 1, TRUE);
			}
			if($del) {
				$del_keys[] = $i;
				$del_keys[] = $i+1;
			}
		}
		foreach($del_keys as $k) {
			unset($r[$k]);
		}
		//Reset indizes
		array_values($r);
	}

	return TRUE;
}//ko_get_wiederholung()



function ko_get_new_serie_id($table) {
	$max1 = db_select_data("ko_".$table, "", "MAX(`serie_id`) as max", "", "", TRUE);
	$max2 = db_select_data("ko_".$table."_mod", "", "MAX(`serie_id`) as max", "", "", TRUE);
	$max = max($max1["max"], $max2["max"]);
	return ($max+1);
}//ko_get_new_serie_id()


/**
 * Erstellt einen Log-Eintrag zu definierten Typ. Timestamp und UserID werden automatisch eingefügt
 */
function ko_log($type, $msg) {
	global $db_connection, $EMAIL_LOG_TYPES, $BASE_URL;

	//Create db entry
	$type = format_userinput($type, 'alphanum+', FALSE, 0, array(), '@');
	db_insert_data('ko_log', array(
		'type' => $type,
		'comment' => mysqli_real_escape_string($db_connection, $msg),
		'user_id' => empty($_SESSION['ses_userid']) ? '0' : $_SESSION['ses_userid'],
		'date' => date('Y-m-d H:i:s'),
		'session_id' => session_id(),
		'request_data' => print_r($_REQUEST, TRUE),
		));

	//Send email notification if activated for given type
	if(is_array($EMAIL_LOG_TYPES) && in_array($type, $EMAIL_LOG_TYPES) && defined('WARRANTY_EMAIL')) {
		$subject = 'kOOL: '.$type.' (on '.$BASE_URL.')';

		$from = ko_get_setting('info_email');
		if(!$from) $from = WARRANTY_EMAIL;

		$msg .= "\n\n- GET:\n".print_r($_GET, TRUE);
		$msg .= "\n\n- POST:\n".print_r($_POST, TRUE);
		$msg .= "\n\n- SESSION:\n".print_r($_SESSION, TRUE);
		$msg .= "\n\n- SERVER:\n".print_r($_SERVER, TRUE);

		ko_send_mail($from, WARRANTY_EMAIL, $subject, $msg);
		//ko_send_email(WARRANTY_EMAIL, $subject, $msg, array('From' => $from));
	}
}//ko_log()



/**
  * Erstellt Log-Meldung anhang zwei übergebener Arrays, und gibt die Differenzen an
	*/
function ko_log_diff($type, $data, $old=array()) {
	$msg = "";
	foreach($data as $key => $value) {
		if($old[$key] != $value) {
			$msg .= "$key: ".$old[$key]." --> ".$value.", ";
		}
	}
	if(isset($old["id"])) $msg = "id: ".$old["id"].", ".$msg;
	ko_log($type, substr($msg, 0, -2));
}//ko_log_diff()



/**
  * Erstellt Log-Meldung, falls in einem Modul ein behandelter Error auftritt.
	* Dient der Verfolgbarkeit von User-Meldungen, wenn sie einen Error erhalten.
	*/
function ko_error_log($module, $error, $error_txt, $action) {
	$log_message  = "$module Error $error: '$error_txt' - Action: $action - ";
  $log_message .= "User: ".$_SESSION["ses_username"]." (".$_SESSION["ses_userid"].") - ";
  $log_message .= "POST: (".var_export($_POST, TRUE).")";
  $log_message .= " - GET: (".var_export($_GET, TRUE).")";

	ko_log("error", $log_message);
}//ko_error_log()





/**
	* Liefert einen einzelnen Logeintrag
	*/
function ko_get_log(&$logs, $z_where="", $z_limit="") {
	if($_SESSION["sort_logs"] && $_SESSION["sort_logs_order"]) {
		$sort = " ORDER BY ".$_SESSION["sort_logs"]." ".$_SESSION["sort_logs_order"].' , id DESC';
	} else {
		$sort = " id DESC ";
	}

	$logs = db_select_data('ko_log', 'WHERE 1=1 '.$z_where, '*', $sort, $z_limit);
}//ko_get_log()


/**
	* Versucht, die IP, des aktuellen Users zu ermitteln
	*/
function ko_get_user_ip() {
	if(isset($HTTP_X_FORWARDED_FOR) && $HTTP_X_FORWARDED_FOR != NULL) {  //Bei Proxy
		$ip = $HTTP_X_FORWARDED_FOR;
	} else {
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	return $ip;
}//ko_get_user_ip()



/**
  *
	*/
function ko_menuitem($module, $show) {
	global $ko_menu_akt;

	$pre = '<b>'; $post = '</b>';

	$ll_item = getLL('submenu_'.$module.'_'.$show);
	//Mark active entry
	if($_SESSION['show'] == $show && $ko_menu_akt == $module) {
		return $pre.$ll_item.$post;
	} else {
		return $ll_item;
	}
}//ko_menuitem()



function ko_get_filename($file_name) {
  $newfile = basename($file_name);
  if (strpos($newfile,'\\') !== false) {
     $tmp = preg_split("[\\\]",$newfile);
     $newfile = $tmp[count($tmp) - 1];
     return($newfile);
   } else {
     return($file_name);
	}
}




function ko_returnfile($file_, $path_="download/pdf/", $filename_="") {
  $file_ = basename(format_userinput($file_, "alphanum+", FALSE, 0, array(), "."));
	$file = $path_.$file_;
  $filename = $filename_ ? $filename_ : $file_;

  $fp = @fopen($file, "r");
  if (!$fp) {
    header("HTTP/1.0 404 Not Found");
    print "Not found!";
    return false;
  }

  if (isset($_SERVER["HTTP_USER_AGENT"]) && strpos($_SERVER["HTTP_USER_AGENT"], "MSIE")) {
	  // IE cannot download from sessions without a cache
   	header("Cache-Control: public");

		 // q316431 - Don't set no-cache when over HTTPS
		 if (  !isset($_SERVER["HTTPS"]) || $_SERVER["HTTPS"] != "on") {
			 header("Pragma: no-cache");
		 }
	}
  else {
    header("Cache-Control: no-cache, must-revalidate");
    header("Pragma: no-cache");
  }

  $mime = exec("/usr/bin/file -bin ".$file." 2>/dev/null");
  if ($mime == "") $mime = "application/octet-stream";
  header("Content-Type: ".$mime);

  // Inline text files, don't separatly save them
  $ext = substr($file, -3);
  if ($ext != "txt") {
    header("Content-Disposition: attachment; filename=\"".$filename."\"");
  }

  header("Content-Length: ".filesize($file));
  header("Content-Description: kOOL");
  fpassthru($fp);
	fclose($fp);
  exit;
}//ko_returnfile()



function dirsize($path) {
	global $ko_path;

  $old_path = getcwd();
  if(!is_dir($ko_path."/".$path)) return -1;
  $size = trim(shell_exec("cd \"".$ko_path."/".$path."\"; du -sb; cd \"".$old_path."\";"), "\x00..\x2F\x3A..\xFF");

  return $size;
}




/**
 * Include all necessary CSS files
 * Called from module/index.php
 * Returns HTML string to be included in <head>
 */
function ko_include_css() {
	global $ko_path, $PLUGINS;

	//Basic CSS files
	$r = '<link rel="stylesheet" type="text/css" href="'.$ko_path.'kOOL.css?'.filemtime($ko_path.'kOOL.css').'" />
<link rel="stylesheet" type="text/css" media="print" href="'.$ko_path.'print.css?'.filemtime($ko_path.'print.css').'" />

<!--[if lte IE 6]>
<link rel="stylesheet" type="text/css" href="'.$ko_path.'ie6.css?'.filemtime($ko_path.'ie6.css').'" />
<![endif]-->
<!--[if IE 7]>
<link rel="stylesheet" type="text/css" href="'.$ko_path.'ie7.css?'.filemtime($ko_path.'ie7.css').'" />
<![endif]-->'."\n";
	if(file_exists($ko_path.'ko.css')) {
		$r .= '<link rel="stylesheet" type="text/css" href="'.$ko_path.'ko.css?'.filemtime($ko_path.'ko.css').'" />'."\n";
	}

	//Include CSS files from plugins
	foreach($PLUGINS as $p) {
		$css_file = $ko_path.'plugins/'.$p['name'].'/'.$p['name'].'.css';
		if(file_exists($css_file)) {
			$r .= '<link rel="stylesheet" type="text/css" href="'.$css_file.'?'.filemtime($css_file).'" />'."\n";
		}
	}

	return $r;
}//ko_include_css()




/**
 * Returns HTML code to include the given files
 * @param array $files Relative paths to the JS files to be included
 */
function ko_include_js($files, $module='') {
	global $ko_menu_akt;

	$r = '';

	if($module !== FALSE) {
		$module = $module ? $module : $ko_menu_akt;
		if($module != '') $r .= '<script type=\'text/javascript\'>var kOOL = {module:"'.$module.'", sid:"'.session_id().'"};</script>'."\n";
	}

	foreach($files as $file) {
		if(!$file) continue;
		$r .= '<script type=\'text/javascript\' src=\''.$file.'?'.filemtime($file).'\'></script>'."\n";
	}

	//Add JS files from plugins
	$plugin_files = hook_include_js($module);
	if(is_array($plugin_files) && sizeof($plugin_files) > 0) {
		foreach($plugin_files as $file) {
			if(!$file) continue;
			$r .= '<script type=\'text/javascript\' src=\''.$file.'?'.filemtime($file).'\'></script>'."\n";
		}
	}

	return $r;
}//ko_include_js()





function ko_list_set_sorting($table, $sortCol) {
	$rows = db_select_data($table, 'WHERE 1', 'id,sort,'.$sortCol, "ORDER BY $sortCol ASC");

	$cY = $cN = $max = 0;
	foreach($rows as $row) {
		if($row['sort'] > 0) $cY++;
		else $cN++;
		$max = max($max, $row['sort']);
	}

	if($cY == 0) {
		$c = 1;
		foreach($rows as $row) {
			db_update_data($table, "WHERE `id` = '".$row['id']."'", array('sort' => $c++));
		}
	} else {
		$c = $max+1;
		foreach($rows as $row) {
			if($row['sort'] > 0) continue;
			db_update_data($table, "WHERE `id` = '".$row['id']."'", array('sort' => $c++));
		}
	}
}//ko_list_set_sorting()




/**
  * Liefert ein Array aller im Browser eingestellten Sprachen in der Reihenfolge der Prioritäten
	*/
function getBrowserLanguages() {
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
}//getBrowserLanguages()



/**
	* Returns a localized string for the given key in the current language
	*/
function getLL($string) {
	global $LOCAL_LANG;

	if(!$string) return '';
	return $LOCAL_LANG[$_SESSION['lang']][$string];
}//getLL()





/**
 * Sends an email
 * @return bool: TRUE when mail was sent successfully, otherwise FALSE
 */
function ko_send_mail($from, $to, $subject, $body, $files = array(), $cc = array(), $bcc = array()) {
	try {
		$message = ko_prepare_mail($from, $to, $subject, $body, $files, $cc, $bcc);
	} catch (Exception $e) {
		if ($e->getCode() != 0) {
			koNotifier::Instance()->addTextError('Swiftmailer error ' . $e->getCode() . ': ' . $e->getMessage());
		}
		else {
			koNotifier::Instance()->addError($e->getCode(), '', array($e->getMessage()), 'swiftmailer');
		}
		return FALSE;
	}
	return ko_process_mail($message);
} //ko_send_mail



function ko_send_html_mail($from, $to, $subject, $body, $files = array(), $cc = array(), $bcc = array()) {
	global $BASE_PATH;
	try {
		$message = ko_prepare_mail($from, $to, $subject, $body, $files, $cc, $bcc);
		$message->setContentType('text/html');
		require_once($BASE_PATH . 'inc/class.html2text.php');
		$html2text = new html2text($body);
		$plainText = $html2text->get_text();
		$message->addPart($plainText, 'text/plain');
	} catch (Exception $e) {
		if ($e->getCode() != 0) {
			koNotifier::Instance()->addTextError('Swiftmailer error ' . $e->getCode() . ': ' . $e->getMessage());
		}
		else {
			koNotifier::Instance()->addError($e->getCode(), '', array($e->getMessage()), 'swiftmailer');
		}
		return FALSE;
	}
	return ko_process_mail($message);
} //ko_send_html_mail()





/**
 * Creates a SwiftMailerMessage object with the given data
 * This can either be used for further settings and sent with ko_process_mail()
 * Or it is called from ko_send_mail before this will call ko_process_mail() itself
 */
function ko_prepare_mail($from = null, $to = null, $subject = null, $body = null, $files = array(), $cc = array(), $bcc = array()) {
	if(is_string($from)) {
		$from = array($from => $from);
	}

	if(is_string($to)) {
		$to = array($to => $to);
	}
	Swift_Preferences::getInstance()->setCharset('iso-8859-1');
	$message = Swift_Message::newInstance();
	$message->setBody($body)
		->setSubject($subject)
		->setFrom($from)
		->setTo($to)
		->setCc($cc)
		->setBcc($bcc);

	//Add Return-Path for error messages
	if(defined('EMAIL_SET_RETURN_PATH') && EMAIL_SET_RETURN_PATH == TRUE) {
		$message->setReturnPath(ko_get_setting('info_email'));
	}
	$message->getHeaders()->addTextHeader('X-Mailer', getLL('kool'));

	foreach($files as $filename => $displayName) {
		if(!file_exists($filename)) {
			continue;
		}
		$message->attach(
			Swift_Attachment::fromPath($filename)->setFilename($displayName)
		);
	}

	return $message;
} //ko_prepare_mail()



/**
 * Sets the transport method for SwiftMailer
 * Uses setting $MAIL_TRANSPORT from config/ko-config.php
 */
function ko_mail_transport() {
	global $MAIL_TRANSPORT;

	switch(mb_strtolower($MAIL_TRANSPORT['method'])) {
		case 'smtp':
			$transport = Swift_SmtpTransport::newInstance(
				$MAIL_TRANSPORT['host'] ? $MAIL_TRANSPORT['host'] : 'localhost',
				$MAIL_TRANSPORT['port'] ? $MAIL_TRANSPORT['port'] : '25',
				$MAIL_TRANSPORT['ssl'] ? 'ssl' : ($MAIL_TRANSPORT['tls'] ? 'tls' : '')
			);
			if($MAIL_TRANSPORT['auth_user'] && $MAIL_TRANSPORT['auth_pass']) {
				$transport->setUsername($MAIL_TRANSPORT['auth_user']);
				$transport->setPassword($MAIL_TRANSPORT['auth_pass']);
			}
		break;

		case 'mail':
			$transport = Swift_MailTransport::newInstance();
		break;

		default:
			$transport = Swift_SendmailTransport::newInstance();
	}

	return $transport;
} //ko_mail_transport()




/**
 * Takes SwiftMessage and sends it using a SwiftTransport from ko_mail_transport()
 * @param Swift_Message $msg Message object created using ko_prepare_mail()
 */
function ko_process_mail(Swift_Message $msg) {
	if(defined('ALLOW_SEND_EMAIL') && ALLOW_SEND_EMAIL === FALSE) return FALSE;

	if(defined('DEBUG_EMAIL') && DEBUG_EMAIL === TRUE) {
		ko_echo_mail($msg);
		return TRUE;
	}

	try {
		$transport = ko_mail_transport();
		// Create the Mailer using your created Transport
		$mailer = Swift_Mailer::newInstance($transport);
		$sent = $mailer->send($msg, $failures);
	} catch (Exception $e) {
		if ($e->getCode() != 0) {
			koNotifier::Instance()->addTextError('Swiftmailer error ' . $e->getCode() . ': ' . $e->getMessage());
		}
		else {
			koNotifier::Instance()->addError($e->getCode(), '', array($e->getMessage()), 'swiftmailer');
		}
		return FALSE;
	}

	if(!$sent) {
		koNotifier::Instance()->addTextError('Swiftmailer error, could not send mail to the following addresses: ' . implode(',', $failures) . ';');
		return FALSE;
	}

	return TRUE;
} //ko_process_mail()





/**
 * Create debug output for email
 */
function ko_echo_mail(Swift_Message $message) {
	global $BASE_PATH;

	print '<h2>Email sent</h2>';
	print '<b>'.ko_html(trim($message->getHeaders()->get('From'))).'</b><br />';
	print '<b>'.ko_html(trim($message->getHeaders()->get('To'))).'</b><br />';
	print '<b>'.ko_html(trim($message->getHeaders()->get('Cc'))).'</b><br />';
	print '<b>'.ko_html(trim($message->getHeaders()->get('Bcc'))).'</b><br />';
	print '<b>Subject: '.ko_html($message->getSubject()).'</b><br />';
	print '<b>Attachments: </b><ul>';

	foreach($message->getChildren() as $child) {
		/** @var Swift_Attachment $child */
		if(!is_a($child, 'Swift_Attachment')) {
			continue;
		}
		$dirs = array(
				'download'.DIRECTORY_SEPARATOR.'word',
				'download'.DIRECTORY_SEPARATOR.'excel',
				'download'.DIRECTORY_SEPARATOR.'pdf',
				);
		$link = null;
		foreach($dirs as $dir) {
			$fullPath = $BASE_PATH . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . $child->getFilename();
			if (file_exists($fullPath)) {
				$link = $dir . DIRECTORY_SEPARATOR . $child->getFilename();
			}
		}
		if($link == null) {
			print '<li>' . $child->getFilename() . '</li>';
		} else {
			print '<li><a href="' . $link . '">' . $child->getFilename() . '</a></li>';
		}
	}

	print '</ul>';
	print '<hr>'.nl2br($message->getBody()).'<hr>';
} //ko_echo_mail()







/**
 * DEPRECATED FUNCTION
 * Old function for sending email. Use ko_send_mail instead which is based on SwiftMailer.
 */
function ko_send_email($to, $subject, $message, $headers) {
	if(defined('ALLOW_SEND_EMAIL') && ALLOW_SEND_EMAIL == FALSE) return FALSE;

	//Check for Reply-To header
	if(isset($headers["From"]) && !isset($headers["Reply-To"])) {
		$headers["Reply-To"] = $headers["From"];
	}

	$mail_headers = NULL;
	foreach($headers as $k => $v) {
		$mail_headers[] = trim($k).": ".trim($v);
	}
	//Add Return-Path for error messages
	if(defined('EMAIL_SET_RETURN_PATH') && EMAIL_SET_RETURN_PATH == TRUE) $mail_headers[] = 'Return-Path: <'.ko_get_setting('info_email').'>';
	$mail_headers[] = 'X-Mailer: '.getLL('kool');

	if(defined('DEBUG_EMAIL') && DEBUG_EMAIL) {
		print '<h2>Email sent</h2>';
		foreach($mail_headers as $h) {
			print ko_html($h).'<br />';
		}
		print '<b>To: '.ko_html($to).'</b><br />';
		print '<b>Subject: '.ko_html($subject).'</b><br />';
		print '<hr>'.nl2br($message).'<hr>';
		return TRUE;
	} else {
		$return_path = defined('EMAIL_SET_RETURN_PATH') && EMAIL_SET_RETURN_PATH == TRUE ? '-f'.ko_get_setting('info_email') : '';
		$sent = mail($to, $subject, $message, implode("\n", $mail_headers), $return_path);
		return $sent;
	}
}//ko_send_email()







function ko_die($msg) {
	print '<div style="border: 2px solid; padding: 10px; background: #3282be; color: white; font-weight: 900;">'.$msg.'</div>';
	exit;
}//ko_die()



function ko_round05($amount) {
	$value = (floor(20*$amount+0.5)/20);
	return $value;
}//ko_round05()



function ko_guess_date(&$v, $mode="first") {
	if(!$v) return $v;
	$r = "";

	$v = str_replace("-", ".", $v);
	$v = str_replace("/", ".", $v);
	$parts = explode(".", $v);
	if(sizeof($parts) == 3) {
		//TODO: first value could also be month (USA)!
		if($parts[0] > 31) {  //assume sql date
			$r = intval($parts[0])."-".intval($parts[1])."-".intval($parts[2]);
		} else {  //assume date dd.mm.yyyy
			$r = intval($parts[2])."-".intval($parts[1])."-".intval($parts[0]);
		}
	} else if(sizeof($parts) == 2) {
		$r = intval($parts[1])."-".intval($parts[0]).($mode=="first"?"-01":"-31");
	} else {  //only one value --> year
		if($v < 1900) {
			if($v < 20) {
				$v += 2000;
			} else {
				$v += 1900;
			}
		}
		$r = intval($v).($mode=="first"?"-01-01":"-12-31");
	}

	$v = strftime('%Y-%m-%d', strtotime($r));
}//ko_guess_date()




function ko_bar_chart($data, $legend, $mode="", $total_width=600) {
	//find max value
	$max = 0;
	foreach($data as $value) {
		$max = max($value, $max);
	}
	//find width of values
	$num_data = sizeof($data);
	$width1 = round($total_width/$num_data, 0);
	$width2 = round(0.75*$total_width/$num_data, 0);
	//build table
	$c = '<table style="border:1px solid #aaa;" cellpadding="0" cellspacing="0"><tr height="100">';
	foreach($data as $value) {
		if($mode == "log") {
			$value = $value == 1 ? 1.5 : $value;
			$height = floor(log($value)/log($max)*100);
			$value = $value == 1.5 ? 1 : $value;
		} else {
			$height = $max == 0 ? 0 : floor($value/$max*100);
		}
		$c .= '<td align="center" valign="bottom" style="height:100px; width:'.$width1.'px;">';
		$c .= '<div style="text-align:center; color: white; width:'.$width2.'px; height:'.$height.'px; background-color:#9abdea;">'.$value.'</div>';
		$c .= '</td>';
	}
	$c .= '</tr><tr>';
	foreach($legend as $value) {
		$c .= '<td align="center">'.$value.'</td>';
	}
	$c .= '</tr></table><br />';

	return $c;
}//ko_bar_chart()



function ko_truncate($s, $l, $l2=0, $add="..") {
	if(strlen($s) <= $l) {
		return $s;
	} else {
		return substr($s, 0, $l-$l2).$add.($l2 > 0 ? substr($s, -$l2) : "");
	}
}//ko_truncate()



/**
 * Read from a URL and return the content as string.
 * It first with file_get_contents if this is allowed, fallback method is with cURL
 *
 * @param $url string The URL to fetch
 * @param $to int Timeout in seconds
 */
function ko_fetch_url($url, $to=3) {
	//Only use file_get_contents on the url if allow_url_fopen is set
	if(ini_get('allow_url_fopen')) {
		//TODO: Timeout does not seem to work...
		//ini_set('default_socket_timeout', $to);
		//$cxt = stream_context_create(array('http' => array('header'=>'Connection: close', 'timeout' => $to)));
		//return file_get_contents($url, FALSE, $ctx);
		return @file_get_contents($url);
	} else {
		//Otherwise use cURL
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_ENCODING , '');  //Don't allow gzip or other compressions
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);  //Follow 301 redirects
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, $to);
		$response = curl_exec($ch);
		curl_close($ch);
		return $response;
	}
}//ko_fetch_url()




function ko_redirect_after_login() {
	global $MODULES, $BASE_URL, $ko_menu_akt;

	if(ko_get_userpref($_SESSION['ses_userid'], 'default_module') && !in_array($ko_menu_akt, array('consensus', 'updater'))) {
		$m = ko_get_userpref($_SESSION['ses_userid'], 'default_module');
		if(in_array($m, $MODULES) && ko_module_installed($m)) {
			$action = ko_get_userpref($_SESSION['ses_userid'], 'default_view_'.$m);
			if(!$action) $action = ko_get_setting('default_view_'.$m);

			$loc = $BASE_URL.$m."/index.php?action=$action";
			header('Location: '.$loc);
			exit;
		}
	}
}//ko_redirect_after_login()






/**
 * Checks for constant FORCE_SSL and redirects to SSL enabled $BASE_URL
 */
function ko_check_ssl() {
	global $BASE_URL;

	if(FORCE_SSL === TRUE && (empty($_SERVER['HTTPS']) || mb_strtolower($_SERVER['HTTPS']) == 'off')) {
		//Only redirect if https URL is set in BASE_URL
		if(mb_strtolower(substr($BASE_URL, 0, 5)) == 'https') {
			header('Location: '.$BASE_URL, TRUE, 301);
			exit;
		}
	}
}//ko_check_ssl()






function ko_check_login() {
	global $db_connection, $ko_path, $LANGS;

	$do_guest = TRUE;
	$reinit = FALSE;

	//Login through sso (only from TYPO3 so far)
	if(ALLOW_SSO && KOOL_ENCRYPTION_KEY && $_GET["sso"] && $_GET["sig"]) {
		$ssoError = FALSE;
		//Decrypt SSO data
		require($ko_path."inc/class.mcrypt.php");
		$crypt = new mcrypt("aes");
		$crypt->setKey(KOOL_ENCRYPTION_KEY);
		list($kool_user, $timestamp, $ssoID, $user) = explode("@@@", $crypt->decrypt(base64_decode($_GET["sig"])));
		$kool_user = trim(format_userinput($kool_user, "js")); $timestamp = trim($timestamp); $ssoID = trim($ssoID); $user = trim($user);
		if(!$kool_user || (int)$timestamp < (int)time() || strlen($ssoID) != 32) $ssoError = TRUE;
		//Check for unique ssoID
		$usedID = db_get_count("ko_log", "id", "AND `type` = 'singlesignon' AND `comment` REGEXP '$ssoID$'");
		if($usedID > 0) $ssoError = TRUE;
		
		//Check for valid user and log in
		$row = db_select_data("ko_admin", "WHERE login = '$kool_user'", "*", "", "", TRUE);
		//Don't allow ko_guest or root
		if(!$ssoError && $row["id"] && !$row["disabled"] && $kool_user != "ko_guest" && $kool_user != "root") {
			$_SESSION["ses_username"] = $kool_user;
			$_SESSION["ses_userid"] = $row["id"];
			ko_log('singlesignon', $user.' from '.format_userinput($_GET['sso'], 'alphanum').': '.$ssoID);
			ko_log("login", $_SESSION["ses_username"]." from ".ko_get_user_ip()." via SSO");

			//Last-Login speichern
			$_SESSION["last_login"] = ko_get_last_login($_SESSION["ses_userid"]);
			db_update_data("ko_admin", "WHERE `id` = '".$_SESSION["ses_userid"]."'", array("last_login" => date("Y-m-d H:i:s")));

			//select language from userpref, if set
			//Use language from userprefs
			$user_lang = ko_get_userpref($_SESSION["ses_userid"], "lang");
			if($user_lang != "" && in_array($user_lang, $LANGS)) {
				$_SESSION["lang"] = $user_lang;
				include($ko_path."inc/lang.inc.php");
			}

			//Reread user settings
			ko_init();
			//Clear all access data read so far. Will be reread next time if not set
			unset($access);

			$do_guest = FALSE;
			$reinit = TRUE;
		}//if(valid_login)
	}//if(sso)


	//Logout
	if($_GET['action'] == "logout" && ($_SESSION["ses_username"] != "" && $_SESSION["ses_username"] != "ko_guest")) {
		ko_log("logout", $_SESSION["ses_userid"].": ".$_SESSION["ses_username"]);

		//Delete old session
		if(ini_get('session.use_cookies')) {
			$params = session_get_cookie_params();
			setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
		}
		session_destroy();

		include("inc/session.inc.php");
		$_SESSION = array();
		$_SESSION['ses_userid'] = ko_get_guest_id();
		$_SESSION['ses_username'] = 'ko_guest';

		$reinit = TRUE;
	}

	//Login
	if($_POST['Login'] && (!$_SESSION['ses_username'] || $_SESSION['ses_username'] == 'ko_guest')) {
		$username = mysqli_real_escape_string($db_connection, $_POST['username']);
		$login = db_select_data('ko_admin', "WHERE `login` = '".$username."' AND `password` = '".md5($_POST['password'])."'", '*', '', '', TRUE);
		if($login['id'] > 0 && $login['login'] == $_POST['username']) {  //Valid login
			//Create new session id after login (to prevent session fixation)
			session_regenerate_id(TRUE);
			//Empty session data so settings from ko_guest will not be used for logged in user
			$_SESSION = array();

			$_SESSION['ses_username'] = $login['login'];
			$_SESSION['ses_userid'] = $login['id'];
			ko_log('login', $_SESSION['ses_username'].' from '.ko_get_user_ip());

			//Read and reset last login
			$_SESSION['last_login'] = ko_get_last_login($_SESSION['ses_userid']);
			db_update_data('ko_admin', "WHERE `id` = '".$_SESSION['ses_userid']."'", array('last_login' => date('Y-m-d H:i:s')));

			$do_guest = FALSE;
			$reinit = TRUE;

			hook_action_handler_inline('login_success');
		}
		else {  //Wrong login
			ko_log('loginfailed', "Username: '".format_userinput($_POST['username'], 'text')."' from ".ko_get_user_ip());
			koNotifier::Instance()->addError(1);
			return FALSE;
		}
	}//if(POST[login])


	if($reinit) {
		unset($GLOBALS['kOOL']);
		ko_init();

		//Select language from userpref, if set
		$user_lang = ko_get_userpref($_SESSION['ses_userid'], 'lang');
		if(!$user_lang) $user_lang = $LANGS[0];
		if($user_lang != '' && in_array($user_lang, $LANGS)) {
			$_SESSION['lang'] = $user_lang;
			include($ko_path.'inc/lang.inc.php');
		}

		//Redirect to default page (if set)
		ko_redirect_after_login();
	}

}//ko_check_login()







/**
  * Stopwatch
	*/
function sw($do="", $tag="") {
	global $sw, $sws;

  list($usec, $sec) = explode(" ", microtime());
  $time = ((float)$usec + (float)$sec);

	switch($do) {
		case "init":
			$sw = $time;
		break;

		case "tag":
			$sws[] = array("tag" => $tag, "value" => ($time - $sw));
		break;

		case "print":
			print "\n<!--\n";
			foreach($sws as $s) {
				print ($s["tag"] ? '"'.$s["tag"].'"' : "").': '.$s["value"]."\n";
			}
			print "\n-->\n";
		break;

		case "printout":
			print '<br /><hr width="100%" /><b>Time:</b><br />';
			foreach($sws as $s) {
				print ($s["tag"] ? '"'.$s["tag"].'"' : "").': '.$s["value"]."<br />";
			}
		break;

		default:
			return $time;
		break;
	}//switch(do)
}


/**
  * Debug-Print
	*/
function print_d($array) {
	print '<pre>';
	print_r($array);
	print '</pre>';
}//print_d()





/**
 * Testing function used for automated testing
 *
 * @param $fcn string Name of the function where this is called from (Usually __FUNCTION__)
 * @param $args array Array of arguments given to original function ($fcn). Usually func_get_args()
 * @param &$return mixed Return value of test function (kotest_TESTCASE_FCN()).
 * @returns boolean TRUE if test function has been found, FALSE otherwise.
 */
function ko_test($fcn, $args, &$return) {
	global $TESTCASE;

	if(defined('KOOLTEST') && KOOLTEST === TRUE && $TESTCASE != '' && function_exists('kotest_'.$TESTCASE.'_'.$fcn)) {
		$return = call_user_func_array('kotest_'.$TESTCASE.'_'.$fcn, $args);
		return TRUE;
	}
	return FALSE;
}//ko_test()






function ko_update_ko_config($mode, $data) {
	global $ko_path;

	$start = $ignore = $found = FALSE;
	//Open config file
	$config_file = $ko_path."config/ko-config.php";
	$fp = @fopen($config_file, "r");
	if($fp) {
		//Go through all the lines
		while (!feof($fp)) {
			$line = fgets($fp);
			switch($mode) {
				case "plugins":
					if(!$start && substr(trim($line), 0, 8) == '$PLUGINS') {
						$found = TRUE;
						$start = TRUE;
						$ignore = TRUE;
					} else if($start == TRUE && trim($line) == ');') {
						$start = FALSE;
						$ignore = FALSE;
						$line = $data;
					}
				break;  //plugins

				case "db":
					if(!$start && substr(trim($line), 0, 11) == '$mysql_user') {
						$found = TRUE;
						$start = TRUE;
						$ignore = TRUE;
					} else if($start == TRUE && substr(trim($line), 0, 9) == '$mysql_db') {
						$start = FALSE;
						$ignore = FALSE;
						$line = $data;
					}
				break;  //db

				case "html_title":
					$found = TRUE;
					if(substr(trim($line), 0, 11) == '$HTML_TITLE') $line = $data;
				break;

				case "base_url":
					$found = TRUE;
					if(substr(trim($line), 0, 9) == '$BASE_URL') $line = $data;
				break;

				case "base_path":
					$found = TRUE;
					if(substr(trim($line), 0, 10) == '$BASE_PATH') $line = $data;
				break;

				case "modules":
					$found = TRUE;
					if(substr(trim($line), 0, 8) == '$MODULES') $line = $data;
				break;

				case "web_langs":
					$found = TRUE;
					if(substr(trim($line), 0, 10) == '$WEB_LANGS') $line = $data;
				break;

				case "get_lang_from_browser":
					$found = TRUE;
					if(substr(trim($line), 0, 22) == '$GET_LANG_FROM_BROWSER') $line = $data;
				break;

				case "sms":
					$found = TRUE;
					if(substr(trim($line), 0, 14) == '$SMS_PARAMETER') $line = $data;
				break;  //sms

				case "mail_transport":
					$found = TRUE;
					if(substr(trim($line), 0, 15) == '$MAIL_TRANSPORT') $line = $data;
					break;  //sms

				case "warranty":
					if(!$start && substr(trim($line), 0, 23) == "@define('WARRANTY_GIVER") {
						$found = TRUE;
						$start = TRUE;
						$ignore = TRUE;
					} else if($start == TRUE && substr(trim($line), 0, 21) == "@define('WARRANTY_URL") {
						$start = FALSE;
						$ignore = FALSE;
						$line = $data;
					}
				break;  //warranty

				case "webfolders":
					$found = TRUE;
					if(substr(trim($line), 0, 19) == '@define("WEBFOLDERS') $line = $data;
				break;
			}//switch(mode)

			//Check whether the data could be updated before the last line
			if(trim($line) == '?>') {
				if(!$found) {  //else insert the data right before the end
					$new_config .= "\n".$data;
				}
			}

			//Build new config-file
			if(!$ignore) {
				$new_config .= $line;
			}

		}//while(!feof(fp))
		fclose($fp);
	} else {
		return FALSE;
	}

	//Write new config
	$fp = @fopen($config_file, "w");
	fputs($fp, $new_config);
	fclose($fp);
	return TRUE;
}//ko_update_ko-config()


function ko_fontsize_to_mm($fontSize) {
	return $fontSize / 2.8457;
}

function ko_mm_to_fontsize($mm) {
	return $mm * 2.8457;
}

function ko_explode_trim_implode($s, $separator = ',') {
	$result = explode($separator, $s);
	foreach ($result as $k => $r) {
		$result[$k] = trim($r);
	}
	return implode($separator, $result);
}

function urldecode_array(&$value, $key) {
	$value = urldecode($value);
}

/**
 * returns $date2 - $date1
 *
 * @param $format 'd' -> days,
 * @param $date1
 * @param $date2
 */
function ko_get_time_diff($format, $date1, $date2) {
	$date1 = date_create($date1);
	$date2 = date_create($date2);
	$diff = date_diff($date1, $date2);
	switch ($format) {
		case 'd':
			$result = $diff->format('%R%a');
			break;
	}
	return $result;
}



?>
