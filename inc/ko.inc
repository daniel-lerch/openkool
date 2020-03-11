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

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

use Mika56\SPFCheck\SPFCheck;
use Mika56\SPFCheck\DNSRecordGetter;


define('VERSION', 'R48');

//Reservation: Objekt-Bild
$RESITEM_IMAGE_WIDTH = 60;
//Itemlist-Stringlängen-Maximum
define("ITEMLIST_LENGTH_MAX", 35);
//Default-Werte für Logins
$DEFAULT_USERPREFS = array(
		array('key' => 'show_limit_daten',            'value' => '20', 'type' => ''),
		array('key' => 'show_limit_leute',            'value' => '20', 'type' => ''),
		array('key' => 'show_limit_kg',               'value' => '20', 'type' => ''),
		array('key' => 'show_limit_reservation',      'value' => '20', 'type' => ''),
		array('key' => 'show_limit_logins',           'value' => '20', 'type' => ''),
		array('key' => 'show_limit_groups',           'value' => '20', 'type' => ''),
		array('key' => 'show_limit_taxonomy',         'value' => '20', 'type' => ''),
		array('key' => 'show_limit_donations',        'value' => '20', 'type' => ''),
		array('key' => 'show_limit_trackings',        'value' => '20', 'type' => ''),
		array('key' => 'tracking_date_limit',         'value' => '7',  'type' => ''),
		array('key' => 'default_view_daten',          'value' => 'show_cal_monat', 'type' => ''),
		array('key' => 'default_view_reservation',    'value' => 'show_cal_monat', 'type' => ''),
		array('key' => 'front_modules',               'value' => 'daten_cal,fastfilter,news,today,geburtstage,mod,adressaenderung', 'type' => ''),
		array('key' => 'do_mod_email_for_edit_res',   'value' => '0', 'type' => ''),
		array('key' => 'do_mod_email_for_edit_daten', 'value' => '0', 'type' => ''),
		array('key' => 'do_res_email',                'value' => '0', 'type' => ''),
		array('key' => 'daten_monthly_title',         'value' => 'title', 'type' => ''),
		array('key' => 'show_birthdays',              'value' => '1', 'type' => ''),
		array('key' => 'daten_show_res_in_tooltip',   'value' => '1', 'type' => ''),
		array('key' => 'daten_pdf_show_time',         'value' => '2', 'type' => ''),
		array('key' => 'daten_pdf_week_start',        'value' => '1', 'type' => ''),
		array('key' => 'daten_pdf_week_length',       'value' => '7', 'type' => ''),
		array('key' => 'daten_mark_sunday',           'value' => '0', 'type' => ''),
		array('key' => 'daten_ical_deadline',         'value' => '0', 'type' => ''),
		array('key' => 'show_dateres_combined',       'value' => '0', 'type' => ''),
		array('key' => 'res_pdf_show_time',           'value' => '2', 'type' => ''),
		array('key' => 'res_pdf_show_comment',        'value' => '1', 'type' => ''),
		array('key' => 'res_pdf_week_start',          'value' => '1', 'type' => ''),
		array('key' => 'res_pdf_week_length',         'value' => '7', 'type' => ''),
		array('key' => 'res_mark_sunday',             'value' => '0', 'type' => ''),
		array('key' => 'res_monthly_title',           'value' => 'zweck', 'type' => ''),
		array('key' => 'res_ical_deadline',           'value' => '0', 'type' => ''),
		array('key' => 'cal_woche_start',             'value' => '6', 'type' => ''),
		array('key' => 'cal_woche_end',               'value' => '22', 'type' => ''),
		array('key' => 'geburtstagsliste_deadline_plus', 'value' => '21', 'type' => ''),
		array('key' => 'geburtstagsliste_deadline_minus', 'value' => '5', 'type' => ''),
		array('key' => 'leute_force_family_firstname','value' => '1', 'type' => ''),
		array('key' => 'leute_children_columns',      'value' => '_father,_mother,_father_natel,_mother_natel', 'type' => ''),
		array('key' => 'show_passed_groups',          'value' => '1', 'type' => ''),
		array('key' => 'groups_filterlink_add_column','value' => '1', 'type' => ''),
		array('key' => 'rota_delimiter',              'value' => ', ', 'type' => ''),
		array('key' => 'rota_pdf_fontsize',           'value' => '11', 'type' => ''),
		array('key' => 'rota_eventfields',            'value' => 'kommentar,kommentar2', 'type' => ''),
		array('key' => 'rota_orderby',                'value' => 'vorname', 'type' => ''),
		array('key' => 'leute_apply_informationlock', 'value' => '1', 'type' => ''),
		array('key' => 'rota_export_all_days',        'value' => '1', 'type' => ''),
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

//Domain that must no be used in From addresses for sent emails
$BLACKLISTED_FROM_DOMAINS = array();

//Zugehörigkeiten der Familien-Daten zu den Personendaten
//Diese Spalten kommen sowohl in ko_leute wie auch in ko_familie vor. ko_leute.* wird jeweils von ko_familie.* überschrieben.
//Ausser beim Neu-Anlegen einer Person, dann ist es umgekehrt.
//Nachname ist standardmässig kein Fam-Feld, da es oft verschiedene Namen in Familien geben kann.
$COLS_LEUTE_UND_FAMILIE = array("adresse", "adresse_zusatz", "plz", "ort", "land", "telp");

$FAMILIE_EXCLUDE = array('famid', 'nachname');

//default columns from ko_leute to be hidden in the form
//can be overridden or extended in config/ko-config.php
$LEUTE_EXCLUDE  = array("id", "famid", "lastchange", "deleted", "kinder", "crdate", "cruserid", "spouse", "father", "mother");

// default columns from ko_leute that should not appear in the table ko_leute_mod
$EXCLUDE_FROM_MOD  = array(
	'ko_leute_mod' => array("id", "famid", "lastchange", "deleted", "hidden", "kinder", "crdate", "cruserid", "import_id", "smallgroups", "groups", "family_import_id", "anrede", "checkin_number"),
	'ko_reservation_mod' => array('id', 'cdate', 'last_change', 'lastchange_user'),
	'ko_event_mod' => array('id', 'import_id', 'last_change', 'lastchange_user', 'cdate', 'reservationen'),
); // TODO: how to handle import_id?

//Default fields containing an email address. Add additionals in config/ko-config.php
$LEUTE_EMAIL_FIELDS = array("email");

//Default famfunction household emails should be sent to (if not defined on household). May be 'husband' or 'wife'.
$LEUTE_DEFAULT_HOUSEHOLD_EMAIL = '';

//Default fields containing a mobile number. Add additionals in config/ko-config.php.
$LEUTE_MOBILE_FIELDS = array('natel');

$LEUTE_PARENT_COLUMNS = ["vorname", "nachname", "natel", "email",];

//Smallgroup roles
//L: Leader, M: Member. Add more in your ko-config.php and set names in LL (see kg_roles_*)
$SMALLGROUPS_ROLES = array('L', 'M');
$SMALLGROUPS_ROLES_LEADER = array('L');
$SMALLGROUPS_ROLES_FOR_NUM = array('M');

//Tracking modes
$TRACKING_MODES = array('simple', 'value', 'valueNonNum', 'type', 'typecheck');

// Default checkin value by tracking mode
$CHECKIN_DEFAULT_VALUES = array(
	'simple' => array(
		'type' => '',
		'value' => '1',
		'comment' => '',
		'status' => '',
	),
	'value' => array(
		'type' => '',
		'value' => '1',
		'comment' => '',
		'status' => '',
	),
	'valueNonNum' => array(
		'type' => '',
		'value' => '1',
		'comment' => '',
		'status' => '',
	),
	'type' => array(
		'type' => '',
		'value' => '1',
		'comment' => '',
		'status' => '',
	),
	'typecheck' => array(
		'type' => '',
		'value' => '1',
		'comment' => '',
		'status' => '',
	),
);

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


//Date format for group export with people (entry date)
$GROUPS_PEOPLE_EXPORT_ENTRY_DATE_FORMAT = '%d.%m.%Y';

//If TRUE all access priviliges will be set to maximum (use with caution!)
//define("ALL_ACCESS", TRUE);

//If set to TRUE the PHP Quick Profiler (PQP) will be displayed for each page (not included in standard kOOL package)
//define('DEBUG', TRUE);

//Logo files
$FILE_LOGO_SMALL = 'images/kool.svg';
$FILE_LOGO_BIG = 'images/kool.svg';

//Individually set colors for events by event field (overwrite in config/ko-config.php)
// $EVENT_COLOR['field']: DB field from ko_event
// $EVENT_COLOR['map']:   Array to map above field values to hex colors (e.g. 'foo' => '00ff00', 'bar' => '0000ff')
$EVENT_COLOR = array();


//Individually set colors for reservations by res field (overwrite in config/ko-config.php)
// $RES_COLOR['field']: DB field from ko_reservation
// $RES_COLOR['map']:   Array to map above field values to hex colors (e.g. 'foo' => '00ff00', 'bar' => '0000ff')
$RES_COLOR = array();

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
		'REV' => array('lastchange'),
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


// Columns that define key of table. Has to be extended by plugins if they add new tables
$TABLE_KEYS = array(
	'ko_admin' => array(
		'keys' => array(
			'login'
		)
	),
	'ko_help' => array(
		'keys' => array(
			'module',
			'type',
			'language'
		)
	),
	'ko_scheduler_tasks' => array(
		'keys' => array(
			'call'
		)
	),
	'ko_settings' => array(
		'keys' => array(
			'key'
		)
	),
	'ko_filter' => array(
		'keys' => array(
			'name'
		)
	),
	'ko_userprefs' => array(
		'keys' => array(
			'user_id',
			'type',
			'key'
		)
	),
	'ko_pdf_layout' => array(
		'keys' => array(
			'type',
			'name',
		)
	),
	'ko_detailed_person_exports' => array(
		'keys' => array(
			'name',
		),
	),
);


//Configuration for install/update.phpsh
$UPDATER_CONF = array(
	'updateTypes' => array('create', 'add', 'modify'),
	'excludeFields' => array('ko_filter.name.family', 'ko_filter.name.smallgroup', 'ko_filter.name.role'),
	'updateFields' => array(
		'ko_help' => '*',
		'ko_filter' => '*',
		'ko_scheduler_tasks' => array('name'),
	),
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



/**
 * For PHP < 7.3
 * copied from php.net
 */
if(!function_exists('array_key_first')) {
	function array_key_first(array $arr) {
		foreach($arr as $key => $unused) {
			return $key;
		}
		return NULL;
	}
}

if(! function_exists("array_key_last")) {
	function array_key_last($array) {
		if(!is_array($array) || empty($array)) {
			return NULL;
		}

		return array_keys($array)[count($array)-1];
	}
}




if($ko_menu_akt != 'ldap') {
	require_once($ko_path.'inc/swiftmailer/swift_required.php');

	//Set default notification levels
	require($ko_path . "inc/class.koNotifier.php");
	$NOTIFIER_LEVEL_DISPLAY = koNotifier::ERRS | koNotifier::INFO | koNotifier::WARNING;
	$NOTIFIER_LEVEL_LOG_TO_DB = koNotifier::ALL ^ koNotifier::DEBUG ^ koNotifier::INFO;
	$NOTIFIER_LEVEL_LOG_TO_FILE = koNotifier::DEBUG;
	$NOTIFIER_LOG_FILE_NAME = 'log.txt';
}

if($ko_menu_akt != 'ldap') {
	require($ko_path.'vendor/autoload.php');
}


//Set log types, that cause server to send an email to the warranty email
$EMAIL_LOG_TYPES = array('db_error_insert', 'db_error_update', 'mailing_smtp_error', 'leute_bw_comp_filter_error', 'column_not_enum', 'camt_parse_error', 'familie_filter', 'error_no_cca_apikey', 'deprecation');

//Set default value for option LEUTE_NO_FAMILY, which disables FAMILY-related options
$LEUTE_NO_FAMILY = false;

// Bootstrap columns per row (grip system)
$BOOTSTRAP_COLS_PER_ROW = 12;

// Set array which contains the links shown in the user menu. It may contain elements from ko_get_menuitem_link and ko_get_menuitem_seperator
$USER_MENU = array();

// Set array which contains the action names for the settings of each module
$MODULE_SETTINGS_ACTION = array(
	'admin' => 'admin_settings',
	'groups' => 'groups_settings',
	'leute' => 'settings',
	'donations' => 'donation_settings',
	'daten' => 'daten_settings',
	'reservation' => 'res_settings',
	'tracking' => 'tracking_settings',
	'rota' => 'settings',
	'crm' => 'crm_settings',
	'taxonomy' => 'taxonomy_settings',
	'subscription' => 'subscription_settings',
);

// Container for PDF Layouts for Leute Mailmerge
$MAILMERGE_LAYOUTS = array('default');

// Daten fields that can not be assigned directly by a user
$DATEN_EXCLUDE_DBFIELDS = array('id', 'gs_gid', 'cdate', 'last_change', 'lastchange_user', 'import_id', 'user_id');

// Res fields that are always displayed to guest user, no matter what setting in res module says
$RES_GUEST_FIELDS_FORCE = array('startzeit', 'endzeit', 'startdatum', 'enddatum', 'item_id');

// Red fields that are excluded from displaying in infotext. Can be extended.
$RES_EXCLUDED_FIELDS_IN_INFOTEXT = array('cdate', 'last_change', 'lastchange_user', 'user_id', 'code');

// Define formulas for holidays (used for res and event repetitions)
$HOLIDAYS = array(
	'neujahrstag' => array('type' => 'absolute', 'mm-dd' => '01-01'),
	'berchtoldstag' => array('type' => 'absolute', 'mm-dd' => '01-02'),
	'first_sunday' => array('type' => 'relative_weekday', 'which' => '7', 'ord' => 'after', 'nth' => '1', 'mm-dd' => '01-01'),
	'heilige_drei_koenige' => array('type' => 'absolute', 'mm-dd' => '01-06'),
	'valentinstag' => array('type' => 'absolute', 'mm-dd' => '02-14'),
	'josefstag' => array('type' => 'absolute', 'mm-dd' => '03-19'),
	'tag_der_arbeit' => array('type' => 'absolute', 'mm-dd' => '05-01'),
	'ash_wednesday' => array('type' => 'relative', 'to' => 'ostersonntag', 'delta' => '-46 days'),
	'palm_sunday' => array('type' => 'relative', 'to' => 'ostersonntag', 'delta' => '-7 days'),
	'holy_thursday' => array('type' => 'relative', 'to' => 'ostersonntag', 'delta' => '-3 days'),
	'karfreitag' => array('type' => 'relative', 'to' => 'ostersonntag', 'delta' => '-2 days'),
	'ostersonntag' => array('type' => 'FCN', 'FCN' => 'date_get_easter'),
	'ostermontag' => array('type' => 'relative', 'to' => 'ostersonntag', 'delta' => '+1 day'),
	'low_sunday' => array('type' => 'relative', 'to' => 'ostersonntag', 'delta' => '+7 day'),
	'auffahrt' => array('type' => 'relative', 'to' => 'ostersonntag', 'delta' => '+39 days'),
	'pfingsten' => array('type' => 'relative', 'to' => 'ostersonntag', 'delta' => '+49 days'),
	'pfingstmontag' => array('type' => 'relative', 'to' => 'ostersonntag', 'delta' => '+50 days'),
	'fronleichnam' => array('type' => 'relative', 'to' => 'ostersonntag', 'delta' => '+60 days'),
	'nationalfeiertag' => array('type' => 'absolute', 'mm-dd' => '08-01'),
	'mariae_himmelfahrt' => array('type' => 'absolute', 'mm-dd' => '08-15'),
	'bettag' => array('type' => 'relative_weekday', 'which' => '7', 'ord' => 'after', 'nth' => '3', 'mm-dd' => '08-31'),
	'beginn_der_sommerzeit' => array('type' => 'relative_weekday', 'which' => '7', 'ord' => 'before', 'nth' => '1', 'mm-dd' => '04-01'),
	'ende_der_sommerzeit' => array('type' => 'relative_weekday', 'which' => '7', 'ord' => 'before', 'nth' => '1', 'mm-dd' => '11-01'),
	'allerheiligen' => array('type' => 'absolute', 'mm-dd' => '11-01'),
	'immaculate_conception' => array('type' => 'absolute', 'mm-dd' => '12-08'),
	'heiliger_abend' => array('type' => 'absolute', 'mm-dd' => '12-24'),
	'weihnachtstag' => array('type' => 'absolute', 'mm-dd' => '12-25'),
	'stephanstag' => array('type' => 'absolute', 'mm-dd' => '12-26'),
	'silvester' => array('type' => 'absolute', 'mm-dd' => '12-31'),
);

// Define timespans for schedule view in rota module (plugins can modify this array in order to add new timespans)
$ROTA_TIMESPANS = array('1d', '1w', '2w', '1m', '2m', '3m', '4m', '6m', '12m', '18m', '24m');


// Define container for ids of people for whom a snapshot was created during the current request
$LEUTE_SNAPSHOTS_SAVED = array();

//Fields to be shown for new addresses in list of revisions
$LEUTE_REVISIONS_FIELDS = array('anrede', 'vorname', 'nachname', 'firm', 'adresse', 'plz', 'ort', 'email', 'telp', 'geburtsdatum');


// Define array of fields that should be propagated from eventgroups to events
$EVENT_PROPAGATE_FIELDS = array(
	array('from' => 'rota', 'to' => 'rota', 'type' => 'bool', 'module' => 'rota', 'default' => false),
	array('from' => 'room', 'to' => 'room', 'type' => 'string'),
	array('from' => 'title', 'to' => 'title', 'type' => 'string'),
	array('from' => 'kommentar', 'to' => 'kommentar', 'type' => 'string'),
);



// Include static class to manipulate kota form layout
if($ko_menu_akt != 'ldap') {
	require_once($ko_path."inc/class.koFormLayoutEditor.php");
}


// These fields are shown for each group subscription
$LEUTE_GROUPSUBSCRIPTION_FIELDS = array('firm', 'vorname', 'nachname', 'adresse', 'plz', 'ort', 'telp', 'natel', 'email', 'geburtsdatum');


// Map titles (anrede) to sex (geschlecht)
$LEUTE_TITLE_TO_SEX = array(
	'de' => array(
		'Herr' => 'm',
		'Frau' => 'w',
		'Fräulein' => 'w',
	),
	'en' => array(
		'Mr' => 'm',
		'Mrs' => 'w',
		'Miss' => 'w',
		'Ms' => 'w',
	),
	'nl' => array(
		'Mijnheer' => 'm',
		'Mevrouw' => 'w',
	)
);


// Access data for google cloud print
$GOOGLE_CLOUD_PRINT_CONFIG = array(
	'client_id' => '',
	'client_secret' => '',
);

$PAYMENT_PROVIDER_CONFIG = [
	'PostFinanceCheckout' => [
// 		'userId' => 20455,
// 		'authKey' => 'vABFjw3DHZn47x/7k2aBU0kH9PRl2m3YOTe+ZmhMCoA=',
// 		'space' => 5571
	],
];





if($ko_menu_akt != 'ldap') {
	//Kunden-spezifische Konfiguration einlesen (kann oben stehende Werte überschreiben)
	require($ko_path."config/ko-config.php");
}






if($ko_menu_akt != 'ldap') {
	//set notification levels
	koNotifier::Instance()->setDisplayLevel($NOTIFIER_LEVEL_DISPLAY);
	koNotifier::Instance()->setLogToDBLevel($NOTIFIER_LEVEL_LOG_TO_DB);
	koNotifier::Instance()->setLogToFileLevel($NOTIFIER_LEVEL_LOG_TO_FILE);
	koNotifier::Instance()->setLogFileName($NOTIFIER_LOG_FILE_NAME);

	// include google cloud print api definition
	require_once("{$ko_path}inc/googleCloudPrint/koGoogleCloudPrint.php");
}


//Set default ldap_login_dn if empty
if($ldap_enabled && (!isset($ldap_login_dn) || $ldap_login_dn == '')) {
	$ldap_login_dn = 'ou=login,'.$ldap_dn;
}


//all available modules
$LIB_MODULES = array('daten', 'reservation', 'leute', 'kg', 'groups', 'tracking', 'rota', 'donations', 'sms', 'telegram', 'admin', 'tools', 'mailing', 'crm', 'vesr', 'subscription', 'taxonomy');

//Allow plugins to add modules
//and autoload plugin classes
$loader = new \Composer\Autoload\ClassLoader();
foreach($PLUGINS as $p) {
	$loader->addPsr4('kOOL\\'.preg_replace_callback('/_[a-z]/',function($m) {return strtoupper($m[0][1]);},ucfirst($p['name'])).'\\',$ko_path.'plugins/'.$p['name'].'/inc');
	include_once($ko_path.'plugins/'.$p['name'].'/config.php');
	if(isset($PLUGIN_CONF[$p['name']]['module']) && $PLUGIN_CONF[$p['name']]['module'] != '') {
		$LIB_MODULES[] = $PLUGIN_CONF[$p['name']]['module'];
		$MODULES[] = $PLUGIN_CONF[$p['name']]['module'];
	}
}
$loader->register();
foreach($MODULES as $k => $v) {
	if(!in_array($v, $LIB_MODULES)) unset($MODULES[$k]);
}

//Modules with groups for access levels
$MODULES_GROUP_ACCESS = array('daten', 'reservation', 'donations', 'tracking', 'rota', 'crm', 'subscription');

// Define the placeholders that should be allowed in emails
$MAILING_PLACEHOLDERS_PERSON = array('');
$MAILING_PLACEHOLDERS_FAMILY = array('');


//Session
include_once($ko_path."inc/session.inc");

//Error reporting
include($ko_path."inc/error_handling.inc");


if(defined('DEBUG') && DEBUG) {
	//start output with: if(defined('DEBUG') && DEBUG) $profiler->display($DEBUG_db);

	require($ko_path.'pqp/classes/PhpQuickProfiler.php');
	$debugMode = TRUE;
	$profiler = new PhpQuickProfiler(PhpQuickProfiler::getMicroTime(), 'web_test/pqp/');

	class pqp_db {
		var $queryCount = 0;
		var $queries = array();
		function query($sql) {
			return mysqli_query(db_get_link(), $sql);
		}
	};
	$DEBUG_db = new pqp_db;

	define('DEBUG_SELECT', TRUE);
	define('DEBUG_UPDATE', TRUE);
	define('DEBUG_INSERT', TRUE);
	define('DEBUG_DELETE', TRUE);
}

if(!defined('DEBUG_SELECT')) define('DEBUG_SELECT',FALSE);
if(!defined('DEBUG_UPDATE')) define('DEBUG_UPDATE',FALSE);
if(!defined('DEBUG_INSERT')) define('DEBUG_INSERT',FALSE);
if(!defined('DEBUG_DELETE')) define('DEBUG_DELETE',FALSE);


//Get base_path from _SERVER if not set during first installation
if($ko_menu_akt == "install" && !$BASE_PATH) {
	$bdir = str_replace("install", "", dirname($_SERVER['SCRIPT_NAME']));
	$droot = $_SERVER["DOCUMENT_ROOT"];
	if(substr($droot, -1) == "/") $droot = substr($droot, 0, -1);
	$BASE_PATH = $droot.$bdir;
}
if($BASE_PATH != "" && substr($BASE_PATH, -1) != "/") $BASE_PATH .= "/";

//Hooks (Plugins)
include($BASE_PATH."inc/hooks.inc");

//Set include path for smarty
if($INCLUDE_PATH_SMARTY) set_include_path($INCLUDE_PATH_SMARTY.PATH_SEPARATOR.get_include_path());


//Connect to the database
$db_conn = mysqli_connect($mysql_server, $mysql_user, $mysql_pass);
//Test the connection
$db_connection = $db_conn !== FALSE;
//unset STRICT_TRANS_TABLES mode
mysqli_query($db_conn,"SET SESSION sql_mode=(SELECT REPLACE(@@sql_mode,'STRICT_TRANS_TABLES',''))");
mysqli_select_db(db_get_link(), $mysql_db);
//Set client-server connection to latin1
if($ko_menu_akt != 'install') mysqli_query(db_get_link(), 'SET NAMES LATIN1');

//Set user to ko_guest if none logged in yet
if($db_connection && !in_array($ko_menu_akt, array('scheduler', 'install', 'post.php')) && !$_SESSION['ses_userid']) {
  $_SESSION['ses_username'] = 'ko_guest';
  $_SESSION['ses_userid'] = ko_get_guest_id();

	//Log guest with IP address (but not form mailing cron job or cli)
  if(!in_array($ko_menu_akt, array('mailing', 'scheduler', 'get.php', 'post.php', 'ical', 'oc', 'telegram_bot')) && !ko_called_from_console()) {
		ko_log('guest', 'ko_guest from '.ko_get_user_ip());
	}

	//Redirect guest user upon it's first visit (unless script is called from cli)
	if(!in_array($ko_menu_akt, array('mailing', 'scheduler', 'checkin', 'install', 'get.php', 'post.php', 'ical', 'carddav', 'oc', 'telegram_bot')) && !ko_called_from_console()) {
		ko_redirect_after_login();
	}
}



//Available languages (overwrite only with $WEB_LANGS in config/ko-config.php, or through the installation)
$LIB_LANGS = array('en', 'de', 'nl', 'fr');
//Regions available for each language (first one being the default)
$LIB_LANGS2 = array('en' => array('UK', 'US'),
										'de' => array('CH', 'DE'),
										'nl' => array('NL'),
										'fr' => array('CH'),
										);
include($BASE_PATH.'inc/lang.inc');
if(isset($_DATETIME[$_SESSION['lang'].'_'.$_SESSION['lang2']])) {
	$DATETIME = $_DATETIME[$_SESSION['lang'].'_'.$_SESSION['lang2']];
} else {
	$DATETIME = $_DATETIME[$_SESSION['lang']];
}


//No DB-connection and not the install-tool is running
if(!$db_connection && $ko_menu_akt != "install" && $ko_menu_akt != 'ldap') {
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

include_once($ko_path.'inc/smarty.inc');


//Submenus (für alle Module)
include($BASE_PATH."inc/submenu.inc");


//Namen für die Frontmodule
$FRONTMODULES = array("daten_cal"       => array("modul" => "daten", "name" => getLL("fm_name_daten_cal")),
											"geburtstage"     => array("modul" => "leute", "name" => getLL("fm_name_geburtstage")),
											"mod"             => array("modul" => "leute,reservation,daten", "name" => getLL("fm_name_mod")),
											'fastfilter'      => array('modul' => 'leute', 'name' => getLL('fm_name_fastfilter')),
											"news"            => array("modul" => "", "name" => getLL("fm_name_news")),
											"adressaenderung" => array("modul" => "", "name" => getLL("fm_name_adressaenderung")),
											"today"           => array("modul" => "leute,daten,rota,reservation", "name" => getLL("fm_name_today")),
);
$FRONTMODULES_LAYOUT = array(
	array('fastfilter', 'adressaenderung', 'daten_cal', 'mod'),
	array('news', 'absence'),
	array('rota', 'today'),
	array('geburtstage'),
);


require_once($ko_path."taxonomy/inc/taxonomy.inc");
include($BASE_PATH."inc/front_modules.inc");

//Read in settings etc
if($ko_menu_akt != 'scheduler') {
	ko_init();
}



// Add link to change user's password to usermenu (in case the setting allows this)
if(ko_get_setting("change_password") == 1) {
	$USER_MENU[] = ko_get_menuitem("admin", "change_password");
}


function ko_called_from_console() {
	if (php_sapi_name() == 'cli') {
		return TRUE;
	} else if (isset($_SERVER['argc']) && $_SERVER['argc'] > 0) {
		return TRUE;
	} else {
		return FALSE;
	}
}


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
			header('Location: '.$BASE_URL.'index.php'); exit;
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

		$kota_filter = unserialize(ko_get_userpref($_SESSION['ses_userid'], 'kota_filter'));
		foreach($kota_filter AS $table) {
			foreach($table AS $col_name => $col) {
				if(isset($col['from']) && isset($col['to'])) {
					$kota_filter[$table][$col_name][0] = [
						"from" => $col['from'],
						'to' => $col['to'],
						'neg' => $col['neg'],
					];
				} elseif(!is_array($col)) {
					$kota_filter[$table][$col_name][0] = $col;
				}
			}
		}
		$_SESSION['kota_filter'] = $kota_filter;
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
function ko_module_installed($m, $uid="", $includeAdmingroups=TRUE) {
	if(defined("ALL_ACCESS")) return TRUE;

	if(!$uid) $uid = $_SESSION['ses_userid'];
	ko_get_user_modules($uid, $modules, $includeAdmingroups);

	if(in_array($m, $modules)) return TRUE;
	else return FALSE;
}//ko_module_installed()



/**
	* Get all modules a user is allowed to see
	*
	* @param int userid
	* @param array Contains the modules
	*/
function ko_get_user_modules($uid, &$m, $includeAdmingroups=TRUE) {
	global $MODULES;

	//Get from cache
	if(isset($GLOBALS["kOOL"]["user_modules"][$uid]) && $includeAdmingroups) {
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

	if($includeAdmingroups) {
		$groups = ko_get_admingroups($uid);
		foreach($groups as $group) {
			$row = db_select_data("ko_admingroups", "WHERE `id` = '".$group["id"]."'", "modules", "", "", TRUE);
			$m = array_merge($m, explode(",", $row["modules"]));
		}
		$m = array_unique($m);

		//Store in cache and return
		$GLOBALS["kOOL"]["user_modules"][$uid] = $m;
	}
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
		case 'kg':
		case 'groups':
		case 'donations':
		case 'tracking':
		case 'projects':
		case 'vesr':
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
			$_access[$module]['ABSENCE'] = $row['event_absence_rights'];

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


		case 'crm':
			$projects = db_select_data('ko_crm_projects', 'WHERE 1=1');
			foreach($projects as $project) {
				$_access[$module][$project['id']] = max($_access[$module][$project['id']], $_access[$module]['ALL']);
			}
		break;


		case 'donations':
			$accounts = db_select_data('ko_donations_accounts', 'WHERE 1=1');
			foreach($accounts as $account) {
				if($account['accountgroup_id'] > 0) {
					if(isset($_access[$module]['ag'.$account['accountgroup_id']])) {
						$_access[$module][$account['id']] = max($_access[$module]['ag'.$account['accountgroup_id']], $_access[$module]['ALL']);
					} else {
						$_access[$module][$account['id']] = $_access[$module]['ALL'];
						$_access[$module]['ag'.$account['accountgroup_id']] = $_access[$module]['ALL'];
					}
				} else {
					$_access[$module][$account['id']] = max($_access[$module][$account['id']], $_access[$module]['ALL']);
				}
			}
		break;


		case 'reservation':
			$resgroups = db_select_data('ko_resgruppen', 'WHERE 1=1', '*', 'ORDER BY name ASC');
			foreach($resgroups as $rg) {
				$_access[$module]['grp'.$rg['id']] = max($_access[$module]['ALL'], $_access[$module]['grp'.$rg['id']]);
			}
			unset($resgroups);

			$items = db_select_data('ko_resitem', 'WHERE 1=1');
			$addAccess = array();
			foreach($items as $item) {
				if(isset($_access[$module][$item['id']])) {
					$_access[$module][$item['id']] = max($_access[$module]['ALL'], $_access[$module][$item['id']]);
					//Don't overwrite grp access levels here, as these (wrong) access rights might be propagated
					//  unto newly created items not stored in ko_admin yet. Then they might get a too high access level
					//  if access is granted on items level instead of itemgroups.
					$addAccess['grp'.$item['gruppen_id']] = max($_access[$module]['grp'.$item['gruppen_id']], $_access[$module][$item['id']], $addAccess['grp'.$item['gruppen_id']]);
				}
				else if(isset($_access[$module]['grp'.$item['gruppen_id']])) {
					$_access[$module][$item['id']] = max($_access[$module]['ALL'], $_access[$module]['grp'.$item['gruppen_id']]);
				} else {
					$_access[$module][$item['id']] = $_access[$module]['ALL'];
					$_access[$module]['grp'.$item['gruppen_id']] = $_access[$module]['ALL'];
				}
			}
			unset($items);

			foreach($addAccess as $k => $v) {
				if(!$k || !$v) continue;
				$_access[$module][$k] = $v;
			}
		break;


		case 'leute':
			$rights = array();
			//Always include hidden addresses
			$orig_value = ko_get_userpref($_SESSION['ses_userid'], "leute_show_hidden");
			ko_save_userpref($_SESSION['ses_userid'], 'leute_show_hidden', 1);
			try {
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
			} finally {
				ko_save_userpref($_SESSION['ses_userid'], 'leute_show_hidden', $orig_value);
			}

			$_access[$module]['ALLOW_BYPASS_INFORMATION_LOCK'] = $row['allow_bypass_information_lock'];
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

			if($mode == "login") $_access = ko_get_groups_access_for_terms($_access, $row);

			if($_access['groups']['ALL'] < 1) {
				//Add view rights for all parent groups, so tree to the groups gets visible
				$top_groups = array_unique(array_merge(array_keys((array)$grps1), array_keys((array)$grps2), array_keys((array)$grps3), array_keys((array)$grps4)));
				unset($grps1); unset($grps2); unset($grps3); unset($grps4);

				$top_groups = array_merge((array)$top_groups, array_keys($_access['groups']));
				if (sizeof($top_groups) > 0) {
					ko_get_groups($all_groups);
					foreach ($top_groups as $gid) {
						if ($gid == "ALL") continue;
						$motherline = ko_groups_get_motherline($gid, $all_groups);
						foreach ($motherline as $id) {
							$_access[$module][$id] = max($_access[$module][$id], 1);
						}
					}
				}
			}

		break;

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

	//hook_access_add($_access, $module, $uid, $force, $apply_admingroups, $mode, $store_globally);


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
				$_access[$module]['ALLOW_BYPASS_INFORMATION_LOCK'] = max($_access[$module]['ALLOW_BYPASS_INFORMATION_LOCK'], $group['allow_bypass_information_lock']);
			}
		}
	}

	if($module == 'daten' && $row['admingroups'] != '' && $apply_admingroups) {
		$groups = db_select_data('ko_admingroups', "WHERE `id` IN ('".implode("','", explode(',', $row['admingroups']))."')");
		foreach($groups as $group) {
			$_access[$module]['REMINDER'] = max($_access[$module]['REMINDER'], $group['event_reminder_rights']);
			$_access[$module]['ABSENCE'] = max($_access[$module]['ABSENCE'], $group['event_absence_rights']);
		}
	}

	//Usually the access rights will be stored in the global array $access
	if($store_globally) {
		//Reset access rights before (re)building them
		unset($access[$module]);
		$access[$module] = $_access[$module];
		return;
	}

	//But when reading the access rights for another user it shouldn't overwrite one's own access rights
	return $_access;
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
	$tables = array('ko_kleingruppen', 'ko_event');
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
 * Get access rights for groups based on taxonomy terms (recursive)
 *
 * @param array $_access
 * @param array $rights db entry from ko_admin
 * @return array $_access
 */
function ko_get_groups_access_for_terms($_access, $rights) {
	$groups_terms_rights = json_decode($rights['groups_terms_rights']);
	$modes = [1 => 'view', 2 => 'new', 3 => 'edit', 4 => 'del'];

	if(!empty($rights['admingroups'])) {
		$admingroups = db_select_data('ko_admingroups', "WHERE `id` IN ('" . implode("','", explode(',', $rights['admingroups'])) . "')");
		foreach ($admingroups as $admingroup) {
			$adminrights = json_decode($admingroup['groups_terms_rights']);

			foreach ($adminrights AS $mode => $adminright) {
				$groups_terms_rights->$mode = implode(",", array_merge(explode(",", $adminright), explode(",", $groups_terms_rights->$mode)));
				if (substr($groups_terms_rights->$mode, 0, 1) == ",") {
					$groups_terms_rights->$mode = substr($groups_terms_rights->$mode, 1);
				}

				if (substr($groups_terms_rights->$mode, -1) == ",") {
					$groups_terms_rights->$mode = substr($groups_terms_rights->$mode, 0, -1);
				}
			}
		}
	}

	$groups_access_by_term = [];
	foreach($modes AS $level => $mode) {
		if ($_access['groups']['ALL'] >= $level) continue;
		$groups_access_by_term[$level] = [];
		if (empty($groups_terms_rights->$mode)) continue;
		$term_ids = explode(",", $groups_terms_rights->$mode);
		foreach ($term_ids AS $parent_term_id) {
			$childTerms = ko_taxonomy_get_terms_by_parent($parent_term_id);
			$term = ko_taxonomy_get_term_by_id($parent_term_id);
			$childTerms[$term['id']] = $term;

			foreach($childTerms AS $childTerm) {
				$groups_access_by_term[$level] = array_merge(ko_taxonomy_get_nodes_by_termid($childTerm['id'], "ko_groups"), $groups_access_by_term[$level]);
			}
		}
	}

	foreach($groups_access_by_term AS $level => $groups) {
		foreach($groups AS $group) {
			$group_id = zerofill($group['id'],6);
			$_access['groups'][$group_id] = max($_access['groups'][$group_id], $level);

			$subgroups = ko_groups_get_recursive("",FALSE,$group['id']);
			foreach($subgroups AS $subgroup) {
				$_access['groups'][$subgroup['id']] = max($_access['groups'][$subgroup['id']], $level);
			}
		}
	}

	return $_access;
}

/**
	* Saves admin data in ko_admin
	*
	* Stores access rights for modules, password, available modules etc. in ko_admin
	*
	* @param string module id or other column to store data for
	* @param int user id or admingroup id to store the data for
	* @param string Value to be stored
	* @param string Stores the data for a login if set to "login", for an admingroup otherwise
	* @return array|bool with log message
	*/
function ko_save_admin($module, $uid, $string, $type="login") {
	global $MODULES;

	$format_userinput = "text";

	switch($module) {
		case "daten": $col = "event_admin"; break;
		case "leute": $col = "leute_admin"; break;
		case "leute_id": $col = "leute_id"; break;
		case "leute_filter": $col = "leute_admin_filter"; break;
		case "leute_spalten": $col = "leute_admin_spalten"; break;
		case "leute_groups": $col = "leute_admin_groups"; break;
		case "leute_gs": $col = "leute_admin_gs"; break;
		case "leute_assign": $col = "leute_admin_assign"; break;
		case "email": $col = "email"; $format = "email"; break;
		case "mobile": $col = "mobile"; $format = "alphanum++"; break;
		case "reservation": $col = "res_admin"; break;
		case "admin": $col = "admin"; break;
		case 'rota': $col = 'rota_admin'; break;
		case "kg": $col = "kg_admin"; break;
		case "groups": $col = "groups_admin"; break;
		case "donations": $col = "donations_admin"; break;
		case "tracking": $col = "tracking_admin"; break;
		case 'sms': $col = ''; break;
		case 'telegram': $col = ''; break;
		case 'mailing': $col = ''; break;
		case 'tools': $col = ''; break;
		case "projects": $col = "projects_admin"; break;
		case "modules": $col = "modules"; break;
		case "admingroups": $col = "admingroups"; break;
		case "login": $col = "login"; break;
		case "name": $col = ($type == "login" ? "login" : "name"); break;
		case "password": $col = "password"; break;
		case "kota_columns_ko_kleingruppen": $col = "kota_columns_ko_kleingruppen"; break;
		case "kota_columns_ko_event": $col = "kota_columns_ko_event"; break;
		case "daten_force_global": $col = "event_force_global"; break;
		case "daten_reminder_rights": $col = "event_reminder_rights"; break;
		case "daten_absence_rights": $col = "event_absence_rights"; break;
		case "reservation_force_global": $col = "res_force_global"; break;
		case "disable_password_change": $col = "disable_password_change"; break;
		case "allow_bypass_information_lock": $col = "allow_bypass_information_lock"; break;
		case "groups_terms_rights": $col = "groups_terms_rights"; break;
		default:
			if(in_array($module, $MODULES)) $col = $module.'_admin';
			else $col = "";
	}

	if(!isset($uid)) return FALSE;
	if($col == "") return FALSE;

	$save_value = [$col => format_userinput($string, $format_userinput)];
	if($type == "login") {
		db_update_data('ko_admin', "WHERE `id` = '$uid'", $save_value);
	} else {
		db_update_data('ko_admingroups', "WHERE `id` = '$uid'", $save_value);
	}

	//Unset cached value in GLOBALS[kOOL]
	if(isset($GLOBALS["kOOL"][$col][$uid][$type])) unset($GLOBALS["kOOL"][$col][$uid][$type]);

	return $save_value;
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

	$r = max(0, intval($admin[$module.'_force_global']));
	foreach ($adminGroups as $ag) {
		$r = max($r, intval($ag[$module.'_force_global']));
	}
	if($r > 2 || $r < 0) $r = 0;

	return $r;
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
	* @param int address id to check access for
	* @return array Array with view and edit rights or FALSE if no limitations exist
	*/
function ko_get_leute_admin_spalten($userid, $mode="login", $pid=0) {
	global $FORCE_KO_ADMIN, $LEUTE_ADMIN_SPALTEN_CONDITION;

	//Get from cache
	if(isset($GLOBALS["kOOL"]["leute_admin_spalten"][$userid][$mode][$pid])) return $GLOBALS["kOOL"]["leute_admin_spalten"][$userid][$mode][$pid];

	//>0 means editing. -1 means new address (from ko_leute_mod and add_person)
  if(($pid > 0 || $pid == -1) && is_array($LEUTE_ADMIN_SPALTEN_CONDITION)) {
    $lasc = $LEUTE_ADMIN_SPALTEN_CONDITION[$userid];
    if(!is_array($lasc)) $lasc = $LEUTE_ADMIN_SPALTEN_CONDITION['ALL'];
    if(is_array($lasc)) {
      $p = db_select_data('ko_leute', "WHERE `id` = '$pid'", '*', '', '', TRUE);
      $dontApply = array();
			//Check multiple conditions separated by comma
      foreach(explode(',', $lasc['dontapply']) as $_col) {
        if(substr($_col, 0, 1) == '!') {
          $_col = substr($_col, 1);
          if(!$p[$_col]) $dontApply[] = TRUE;
          else $dontApply[] = FALSE;
        } else {
          if($p[$_col]) $dontApply[] = TRUE;
          else $dontApply[] = FALSE;
        }
      }
			//Check for all conditions to be met (linked by AND)
      $return = TRUE;
      foreach($dontApply as $da) {
        if(!$da) $return = FALSE;
      }
      if($return) return FALSE;
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
		foreach($return as $k => $v) {
			if(!$v) unset($return[$k]);
		}

		//Return FALSE if no cols were found, so all will get displayed
		if(sizeof($return) == 0) $return = FALSE;

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
	*
	* @param $id int ID of login. Currently logged in user is used if left empty
	* @param $preferAdminEmail Boolean Set to true (default) to let admin_email be returned as email
	* @return array|bool person array on success
	*/
function ko_get_logged_in_person($id='', $preferAdminEmail=TRUE) {
	global $LEUTE_EMAIL_FIELDS, $LEUTE_MOBILE_FIELDS;

	$lid = $id ? $id : $_SESSION['ses_userid'];
	if(!$lid) return FALSE;

	$person = db_select_data("ko_admin AS a LEFT JOIN ko_leute as l ON a.leute_id = l.id",
												 "WHERE a.id = '$lid' AND (a.disabled = '0' OR a.disabled = '') AND l.deleted = '0' AND l.hidden = '0'",
												 "l.*, a.email AS admin_email",
												 '', '', TRUE);

	//If no address is set for this login still set email address from login (if given)
  if(sizeof($person) == 0) {
    ko_get_login($lid, $login);
    if($login['email']) $person = array('email' => $login['email'], 'vorname' => $login['login']);
  }
	else {
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
		if($preferAdminEmail && $person['admin_email']) $person['email'] = $person['admin_email'];
	}

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
	if(isset($GLOBALS['kOOL']['guest_id'])) return $GLOBALS['kOOL']['guest_id'];

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
	if(isset($GLOBALS['kOOL']['root_id'])) return $GLOBALS['kOOL']['root_id'];

	$row = db_select_data('ko_admin', "WHERE `login` = 'root'", 'id', '', '', TRUE);
	if(is_array($row)) {
		$GLOBALS["kOOL"]["root_id"] = $row["id"];
		return $row["id"];
	} else {
		return FALSE;
	}
}



/**
	* Get the id of the special login _checkin_user
	*
	* @return int user id of checkin user
	*/
function ko_get_checkin_user_id() {
	//if($GLOBALS['kOOL']['checkin_user_id']) return $GLOBALS['kOOL']['checkin_user_id'];

	$checkinUser = db_select_data('ko_admin', "WHERE `login` = '_checkin_user'", 'id,login', '', '', TRUE);
	if($checkinUser && $checkinUser['login'] == '_checkin_user') {
		$GLOBALS['kOOL']['checkin_user_id'] = $checkinUser['id'];
		return $checkinUser['id'];
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
	global $LEUTE_NO_FAMILY;
	//Get from cache

	if(!$force && isset($GLOBALS['kOOL']['ko_settings'][$key])) {
		$result = $GLOBALS['kOOL']['ko_settings'][$key];
	}
	else {
		$query = "SELECT `value` from `ko_settings` WHERE `key` = '$key' LIMIT 1";
		$result = mysqli_query(db_get_link(), $query);
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


/**
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
 * @return mixed|null Value of user preference
 */
function ko_get_userpref($user_id, $key="", $type="", $order="", $force=FALSE) {
	global $DEFAULT_USERPREFS;

	if($type != "") {
		if($key != "") {
			//Look up userpref in GLOBALS
			if(!$force && $user_id == $_SESSION["ses_userid"] && isset($GLOBALS["kOOL"]["ko_userprefs"]["TYPE@".$type][$key]))
				return array($GLOBALS["kOOL"]["ko_userprefs"]["TYPE@".$type][$key]);

			$where = "WHERE `user_id` = '$user_id' AND `key` = '$key' AND `type` = '$type'";
		} else {
			//Look up userpref in GLOBALS
			if(!$force && $user_id == $_SESSION["ses_userid"] && is_array($GLOBALS["kOOL"]["ko_userprefs"]["TYPE@".$type]))
				return $GLOBALS["kOOL"]["ko_userprefs"]["TYPE@".$type];

			$where = "WHERE `user_id` = '$user_id' AND `type` = '$type' ";
		}

		$result = db_select_data("ko_userprefs", $where, "*", $order, "", FALSE, TRUE);
		if(!empty($result)) {
			return $result;
		}
	} else {
		//Look up userpref in GLOBALS
		if(!$force && $user_id == $_SESSION["ses_userid"] && isset($GLOBALS["kOOL"]["ko_userprefs"][$key]))
			return $GLOBALS["kOOL"]["ko_userprefs"][$key];

		$where = "WHERE `user_id` = '$user_id' AND `key` = '$key'";
		$result = db_select_data("ko_userprefs", $where, "*", $order, "LIMIT 1", TRUE, TRUE);
		if(!empty($result)) {
			return $result['value'];
		}
	}

	foreach($DEFAULT_USERPREFS AS $DEFAULT_USERPREF) {
		if($DEFAULT_USERPREF['key'] == $key && $DEFAULT_USERPREF['type'] == $type) {
			return $DEFAULT_USERPREF['value'];
		}
	}

	return NULL;
}


/**
	* Store a user preference in ko_userprefs
	*
	* @param int user userid
	* @param string Key of user preference
	* @param mixed Value to be stored
	* @param string Type of user preference to store
	*/
function ko_save_userpref($userid, $key, $value, $type="") {
	$userid = format_userinput($userid, "int");
	$key = format_userinput($key, "text");
	$type = format_userinput($type, "alphanum+");

	//Store in db
	if(db_get_count('ko_userprefs', 'key', "AND `user_id`= '$userid' AND `key` = '$key' AND `type` = '$type'") >= 1) {
		db_update_data('ko_userprefs', "WHERE `user_id` = '$userid' AND `key` = '$key' AND `type` = '$type'", array('value' => $value));
  } else {  //...sonst neues einfügen
		db_insert_data('ko_userprefs', array('user_id' => $userid, 'type' => $type, 'key' => $key, 'value' => $value));
  }

	//Save in GLOBALS as well (but only for logged in user)
	if($userid == $_SESSION["ses_userid"]) {
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
 	* @return bool true if successful
	*/
function ko_check_userpref($id, $key, $type="") {
	$id = format_userinput($id, "int");
	$key = format_userinput($key, "text");
	$type = format_userinput($type, "alphanum+");

	if($type != "") {
		$query = "SELECT `key`, `value` FROM `ko_userprefs` WHERE `user_id` = '$id' AND `key` = '$key' AND `type` = '$type'";
		$result = mysqli_query(db_get_link(), $query);
		$row = mysqli_fetch_assoc($result);
	} else {
		$query = "SELECT `value` FROM `ko_userprefs` WHERE `user_id` = '$id' AND `key` = '$key'";
		$result = mysqli_query(db_get_link(), $query);
		$row = mysqli_fetch_assoc($result);
	}
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
	$dbColumns = db_get_columns('ko_leute');
	$allColumns = array();
	foreach($dbColumns as $dbCol) {
		$allColumns[] = $dbCol['Field'];
	}
	foreach($rows as $row) {
		$origFilterRow = $row;
		if(!$get_all) {
			if($row['dbcol'] != '' && FALSE === strpos($row['dbcol'], '.') && substr($row['dbcol'], 0, 3) != 'my_' && !in_array($row['dbcol'], $allColumns)) continue;
			if($row['name'] == 'donation' && !ko_module_installed('donations', $_SESSION['ses_userid'])) continue;
			if(substr($row['name'], 0, 3) == 'crm' && !ko_module_installed('crm', $_SESSION['ses_userid'])) continue;
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
				if($row['dbcol'] != '' && FALSE === strpos($row['dbcol'], '.') && in_array($row['dbcol'], $allColumns)) {
					$dbcol = $row['dbcol'];
					//Check for allowed column
					if(in_array($dbcol, array_merge($allowed_cols['view'], array('id', 'crdate', 'lastchange', 'hidden', 'import_id')))) $ok = TRUE;
				} else {
					$ok = TRUE;
				}

				if(!$ok) continue;
			}
		}//if(!get_all)

		//special filters for other tables
		for($i=1; $i<5; $i++) {
			if(substr($row["code$i"], 0, 4) == "FCN:") {
				$code = "";
				$fcn = substr($row["code$i"], 4);
				$params = array();
				if(strpos($fcn, ":")) {  //Find parameters given along with the function name (e.g. used for enum_ll)
					$params = explode(":", $fcn);
					$fcn = $params[0];
				}
				if(function_exists($fcn)) call_user_func_array($fcn, array(&$code, $params, $origFilterRow));
				$row["code$i"] = $code;
			}
		}

		//Locallang-values if set
		$ll_name = getLL("filter_".$row["name"]);
		foreach(array("var1", "var2", "var3", "var4") as $var) {
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
function ko_get_filter_column($sql, $dbCol=NULL) {
	if ($sql == 'kota_filter') return $dbCol;

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



function ko_specialfilter_kota(&$code, $params, $filter) {
	$table = $params[1];
	$col = $params[2];
	if ($filter['allow_neg']) $showNegative = TRUE;
	else $showNegative = FALSE;

	$kotaFilterData = NULL;

	// TODO: fill in value?
	/*foreach ($_SESSION['filter'] as $f) {
		if ($f[0] == $filter['id']) {
			$kotaFilterData = $f[1][1]['kota_filter_data']['ko_leute'];
		}
	}*/

	$code = kota_get_filter_form($table, $col, $showNegative, FALSE, $kotaFilterData);
}





function kota_get_filter_form($table, $cols, $showNegative=TRUE, $showButtons=TRUE, $kotaFilterData=NULL, $showLabel=TRUE) {
	global $KOTA, $smarty, $access;

	if (!is_array($cols)) $cols = explode(',', $cols);
	$r = '';
	foreach($cols as $col) {
		if(!isset($KOTA[$table][$col])) continue;
		$type = $KOTA[$table][$col]['filter']['type'];
		if(!$type) $type = $KOTA[$table][$col]['form']['type'];
		if(!$type) continue;

		if($kotaFilterData === NULL) $kotaFilterData = $_SESSION['kota_filter'][$table];

		$val = $kotaFilterData[$col];
		if (isset($_SESSION['kota_filter'][$table][$col])) {
			$show_clear = TRUE;
		}

		if ($type == 'jsdate') {
			$val_from = $val['from'];
			$val_to = $val['to'];
			if($val['neg']) {
				$val = substr($val, 1);
				$negChk = 'checked="checked"';
			} else {
				$negChk = '';
			}
		}
		else {
			if(substr($val, 0, 1) == '!') {
				$val = substr($val, 1);
				$negChk = 'checked="checked"';
			} else {
				$negChk = '';
			}
		}

		if(substr($type, 0, 4) == 'FCN:') {
			list($temp, $fcn, $type) = explode(':', $type);
			if($fcn && function_exists($fcn)) {
				eval("$fcn(\$table, \$col);");
			} else {
				$type = '';
			}
		}

		switch($type) {
			case 'text':
			case 'textarea':
			case 'richtexteditor':
			case 'time':
				if ($showLabel) $r .= '<label for="kota_filter['.$table.':'.$col.']">'.getLL('kota_'.$table.'_'.$col).'</label>';
				$r .= '<input type="text" class="kota_filter_inputs form-control input-sm" id="kota_filter['.$table.':'.$col.']" name="kota_filter['.$table.':'.$col.']" value="'.$val.'" />';
				break;

			case 'select':
			case 'doubleselect':
			case 'checkboxes':
				if ($showLabel) $r .= '<label for="kota_filter['.$table.':'.$col.']">'.getLL('kota_'.$table.'_'.$col).'</label>';
				$params = $KOTA[$table][$col]['filter']['params'];
				if(!$params) $params = $KOTA[$table][$col]['form']['params'];
				$r .= '<select class="kota_filter_inputs input-sm form-control" id="kota_filter['.$table.':'.$col.']" name="kota_filter['.$table.':'.$col.']" '.$params.' >';

				//Use data array if set
				if(is_array($KOTA[$table][$col]['filter']['data'])) {
					$values = array_keys($KOTA[$table][$col]['filter']['data']);
					$descs = array_values(array_values($KOTA[$table][$col]['filter']['data']));
				} elseif (isset($KOTA[$table][$col]['form']['data_func'])) {
					$fcn = $KOTA[$table][$col]['form']['data_func'];
					if (function_exists($fcn)) {
						$options = $fcn([]);
						$values = $options['values'];
						$descs = $options['descs'];
					}
				} else if($type == 'select') {
					if($KOTA[$table][$col]['form']['data_func'] && function_exists($KOTA[$table][$col]['form']['data_func'])) {
						$KOTA[$table][$col]['form'] = array_merge($KOTA[$table][$col]['form'], call_user_func_array($KOTA[$table][$col]['form']['data_func'], array()));
					}
					$values = $KOTA[$table][$col]['form']['values'];
					$descs = array_values($KOTA[$table][$col]['form']['descs']);
				} else {
					$values = $KOTA[$table][$col]['form']['values'];
					$descs = array_values($KOTA[$table][$col]['form']['descs']);
				}
				foreach($values as $k => $v) {
					if(in_array($v, $_SESSION['kota_filter'][$table][$col])) continue;

					$sel = $val == $v ? 'selected="selected"' : '';
					$r .= '<option value="'.$v.'" '.$sel.'>'.$descs[$k].'</option>';
				}
				$r .= '</select>';
				break;

			case 'selectplus':
				if ($showLabel) $r .= '<label for="kota_filter['.$table.':'.$col.']">'.getLL('kota_'.$table.'_'.$col).'</label>';
				$params = $KOTA[$table][$col]['filter']['params'];
				if(!$params) $params = $KOTA[$table][$col]['form']['params'];
				if(isset($KOTA[$table][$col]['form']['async_form'])) {
					$r .= '<select class="kota_filter_inputs input-sm form-control" id="kota_filter['.$table.':'.$col.']" name="kota_filter['.$table.':'.$col.']" '.$params.' >';
				} else {
					$r .= '<select class="input-sm form-control" id="kota_filter_' . $table . '_' . $col . '_select" name="kota_filter_' . $table . '_' . $col . '_select" ' . $params . ' >';
				}
				$r .= '<option value=""></option>';

				if(is_array($KOTA[$table][$col]['filter']['data'])) {
					$values = array_keys($KOTA[$table][$col]['filter']['data']);
					$descs = array_values(array_values($KOTA[$table][$col]['filter']['data']));
				} else {
					$values = $KOTA[$table][$col]['form']['values'];
					$descs = array_values($KOTA[$table][$col]['form']['descs']);
				}
				foreach($values as $k => $v) {
					if(in_array($v, $_SESSION['kota_filter'][$table][$col])) continue;

					$sel = ($val == $v ? 'selected="selected"' : '');
					$r .= '<option value="'.$v.'" '.$sel.'>'.$descs[$k].'</option>';
				}
				$r .= '</select>';

				// clear $val if we had an id for select
				if (preg_match('/^(g[0-9]{6}|[0-9]{1,6})$/', $val, $matches)) {
					$val = '';
				}

				if(empty($KOTA[$table][$col]['form']['async_form'])) {
					$r .= '<input type="text" class="form-control input-sm" id="kota_filter_' . $table . '_' . $col . '_plus"
					name="kota_filter_' . $table . '_' . $col . '_plus" value="' . $val . '" />
					<input type="hidden" class="kota_filter_inputs form-control input-sm" name="kota_filter[' . $table . ':' . $col . ']" id="kota_filter_' . $table . '_' . $col . '" />
					<script>
						$(\'button#kota_filterbox_submit\').on(\'click\', function(e){
							e.preventDefault();
							var selectbox = $(\'#kota_filter_' . $table . '_' . $col . '_select\').val();
							var plusbox = $(\'#kota_filter_' . $table . '_' . $col . '_plus\').val();
							if (selectbox != -1) {
								$(\'#kota_filter_' . $table . '_' . $col . '\').val(selectbox);
							} else {
								$(\'#kota_filter_' . $table . '_' . $col . '\').val(plusbox);
							}
						});
					</script>
					';
				}
			break;
			case 'textplus':
			case 'textmultiplus':
				if ($showLabel) $r .= '<label for="kota_filter['.$table.':'.$col.']">'.getLL('kota_'.$table.'_'.$col).'</label>';
				$params = $KOTA[$table][$col]['filter']['params'];
				if(!$params) $params = $KOTA[$table][$col]['form']['params'];
				$r .= '<select class="kota_filter_inputs input-sm form-control" size="0" id="kota_filter['.$table.':'.$col.']" name="kota_filter['.$table.':'.$col.']" '.$params.' >';
				if($type == 'textmultiplus') {
					$values = kota_get_textmultiplus_values($table, $col);
				} else {
					$values = db_select_distinct($table, $col, '', $KOTA[$table][$col]['form']['where'], $KOTA[$table][$col]['form']['select_case_sensitive'] ? TRUE : FALSE);
				}

				//Find FCN for list to apply
				$applyMe = FALSE;
				if(FALSE !== strpos($KOTA[$table][$col]['list'], '(')) {
					$fcn = substr($KOTA[$table][$col]['list'], 0, strpos($KOTA[$table][$col]['list'], '('));
					if(function_exists($fcn)) {
						$applyMe = $KOTA[$table][$col]['list'];
					}
				}

				foreach($values as $v) {
					if(in_array($v, $_SESSION['kota_filter'][$table][$col])) continue;

					$sel = $val == $v ? 'selected="selected"' : '';
					if($applyMe) {
						eval("\$l=".str_replace('@VALUE@', addslashes($v), $applyMe).';');
						if(!$l) $l = $v;
					} else $l = $v;
					if($l == '0') $l = '';
					$r .= '<option value="'.$v.'" '.$sel.'>'.$l.'</option>';
				}
				$r .= '</select>';
				break;

			case 'checkbox':
			case 'switch':
				if ($showLabel) $r .= '<label for="kota_filter['.$table.':'.$col.']">'.getLL('kota_'.$table.'_'.$col).'</label><br>';
				$r .= '<input type="checkbox" class="kota_filter_inputs" id="kota_filter['.$table.':'.$col.']" name="kota_filter['.$table.':'.$col.']" '.$KOTA[$table][$col]['form']['params'].' data-on-text="' . getLL('yes') . '" data-off-text="' . getLL('no') . '" value="1" ' . ($val ? 'checked' : '') . '>';
				$r .= "<script>$('input[name=\"kota_filter[".$table.':'.$col."]\"]').bootstrapSwitch();</script>";
				break;

			case 'jsdate':
				if ($showLabel) $r .= '<label>'.getLL('kota_'.$table.'_'.$col).'</label><br>';

				$random_html_id = mt_rand(0,100000);
				$r .= getLL('date_from').'<br>';
				$input = ['type' => 'datepicker','name' => 'kota_filter['.$table.':'.$col.'][from]','value' => $val_from, 'add_class' => 'kota_filter_inputs', 'html_id' => "agi-" . $random_html_id . "-0" ];
				$input['sibling'] = "agi-" . $random_html_id . "-1";

				$smarty->assign('input', $input);
				$r .= $smarty->fetch('ko_formular_elements.tmpl');

				$r .= getLL('date_to').'<br>';
				$input = ['type' => 'datepicker','name' => 'kota_filter['.$table.':'.$col.'][to]','value' => $val_to, 'add_class' => 'kota_filter_inputs', 'html_id' => "agi-" . $random_html_id . "-1"];

				$smarty->assign('input', $input);
				$r .= $smarty->fetch('ko_formular_elements.tmpl');
				break;

			case 'peoplesearch':
				if(!$access['leute']) ko_get_access('leute');
				$values = db_select_distinct($table, $col, '', $KOTA[$table][$col]['form']['where'], FALSE);
				$ids = array();
				foreach($values as $value) {
					if(FALSE !== strpos($value, ',')) {
						foreach(explode(',', $value) as $v) {
							if(!$v || !intval($v)) continue;
							//Access check
							if($access['leute']['ALL'] < 1 && $access['leute'][intval($v)] < 1) continue;
							$ids[] = intval($v);
						}
					} else {
						//Access check
						if($access['leute']['ALL'] < 1 && $access['leute'][intval($value)] < 1) continue;
						if(intval($value)) $ids[] = intval($value);
					}
				}
				if(sizeof($ids) > 0) {
					$people = db_select_data('ko_leute', "WHERE `id` IN (".implode(',', $ids).")", '*', 'ORDER BY `firm` ASC, `nachname` ASC, `vorname` ASC');

					if ($showLabel) $r .= '<label for="kota_filter['.$table.':'.$col.']">'.getLL('kota_'.$table.'_'.$col).'</label>';
					$r .= '<select class="kota_filter_inputs input-sm form-control" id="kota_filter['.$table.':'.$col.']" name="kota_filter['.$table.':'.$col.']" size="0">';
					$r .= '<option value=""></option>';
					foreach($people as $p) {
						if($p['firm']) {
							$p_name = trim($p['firm'].($p['department'] ? ' ('.$p['department'].')' : ''));
							if($p['nachname'] || $p['vorname']) $p_name .= ' ('.trim($p['vorname'].' '.$p['nachname']).')';
						} else {
							$p_name = trim($p['nachname'].' '.$p['vorname']);
						}
						$p_address = trim($p['adresse'].' '.$p['plz'].' '.$p['ort']).' (ID '.$p['id'].')';

						$sel = $p['id'] == $val ? 'selected="selected"' : '';
						$r .= '<option value="'.$p['id'].'" '.$sel.' title="'.$p_address.'">'.$p_name.'</option>';
					}
					$r .= '</select>';
				}
				break;
			case 'dynamicsearch':
				if($col == "terms") {
					if (!$access['taxonomy']) ko_get_access('taxonomy');

					if ($showLabel) $r .= '<label for="kota_filter[' . $table . ':' . $col . ']">' . getLL('kota_' . $table . '_' . $col) . '</label>';
					$r .= '<select class="kota_filter_inputs input-sm form-control" id="kota_filter[' . $table . ':' . $col . ']" name="kota_filter[' . $table . ':' . $col . ']" size="0">
				<option value=""></option>';

					$terms = ko_taxonomy_get_terms();
					$structuredTerms = ko_taxonomy_terms_sort_hierarchically($terms);

					foreach ($structuredTerms AS $structuredTerm) {
						if (!empty($structuredTerm['children'])) {
							$r .= "<option value='" . $structuredTerm['data']['id'] . "'>" . $structuredTerm['data']['name'] . "</option>";
							foreach ($structuredTerm['children'] AS $childTerm) {
								$r .= "<option value='" . $childTerm['id'] . "'>&nbsp; &nbsp;" . $childTerm['name'] . "</option>";
							}
						} else {
							$r .= "<option value='" . $structuredTerm['data']['id'] . "'>" . $structuredTerm['data']['name'] . "</option>";
						}
					}

					$r .= "</select>";
				}
				break;
			//TODO: other types
		}
	}
	if($r != '' && $showNegative) {
		//Add negative checkbox
		$r .= '<div class="checkbox">';
		$r .= '<label for="kota_filterbox_neg" class="kota_filterbox_neg">';
		$r .= '<input type="checkbox" id="kota_filterbox_neg" name="kota_filterbox_neg" value="1" '.$negChk.' >';
		$r .= getLL('filter_negativ') . '</label></div>';

		if ($showButtons) {
			$r .= '<div style="margin-top: 8px;">';
			if($show_clear) {
				$r .= '<button type="submit" class="btn btn-sm btn-danger" id="kota_filterbox_clear" title="' . getLL('kota_filter_clear') . '" value="'.getLL('kota_filter_clear').'" rel="'.$table.':'.implode(',', $cols).'"><span class="glyphicon glyphicon-remove"></span></button>';
			}
			$r .= '<button type="submit" class="btn btn-sm btn-primary pull-right" id="kota_filterbox_submit" title="' . getLL('kota_filter_submit') . '" value="'.getLL('kota_filter_submit').'"><span class="glyphicon glyphicon-ok"></span></button>';
			$r .= '<i class="clearfix"></i>';
			$r .= '</div>';
		}
	}

	return $r;
}

function kota_get_applied_filter($table, $cols) {
	global $KOTA;
	$applied_filters = [];

	if (!is_array($cols)) $cols = explode(',', $cols);
	foreach($cols AS $col) {
		if (!empty($_SESSION['kota_filter'][$table][$col])) {
			$filters = $_SESSION['kota_filter'][$table][$col];
			foreach($filters AS $key => $value) {
				$negative = FALSE;
				if(substr($value, 0,1) == "!") {
					$negative = TRUE;
					$value = substr($value, 1);
				}
				$data[$col] = $value;
				$type = $KOTA[$table][$col]['filter']['type'];
				if (!$type) $type = $KOTA[$table][$col]['form']['type'];

				if (substr($KOTA[$table][$col]['filter']['list'], 0, 4) == "FCN:" && !is_array($value)) {
					$fcn = substr($KOTA[$table][$col]['filter']['list'], 4);
					if (function_exists($fcn)) {
						$fcn($data[$col], [], [], []);
					}
				} else if (substr($KOTA[$table][$col]['list'], 0, 4) == "FCN:" && !is_array($value)) {
					$fcn = substr($KOTA[$table][$col]['list'], 4);
					if (function_exists($fcn)) {
						if($fcn == "kota_listview_people" || $fcn == "kota_listview_people_link") {
							kota_process_data($table, $data, 'list');
						} elseif($fcn == "kota_listview_ll") {
							$full_data = [
								"table" => $table,
								"col" => $col,
							];
							$fcn($data[$col], $full_data, [], []);
						} else {
							$fcn($data[$col], [], [], []);
						}
					}
				} else if ($type == 'jsdate') {
					$data[$col] = ($value['neg'] ? '! (' : '') . ($value['from'] ? $value['from'] : getLL('filter_always')) . ' - ' . ($value['to'] ? $value['to'] : getLL('filter_always')) . ($value['neg'] ? ')' : '');
				} else {
					kota_process_data($table, $data, 'list');
				}


				$applied_filters[] = "<button type=\"submit\" class=\"btn btn-sm btn-danger kota_filterbox_clear_element\" title=\"" . getLL('kota_filter_clear') . "\" value=\"".getLL('kota_filter_clear')."\" rel=\"".$table.":". $col .":".$key."\"><span class=\"glyphicon glyphicon-remove\"></span></button><span class='title'>" . getLL("kota_" . $table . "_" . $col) . ":</span> " .
					($negative ? "<span class='negative'>!</span>":"") . $data[$col];
			}
		}
	}

	return $applied_filters;
}


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


	$code = '<select class="input-sm form-control" name="var1" onchange="sendReq('."'../groups/inc/ajax.php', 'action,dfid,sesid', 'groupdatafieldsfilter,'+this.options[this.selectedIndex].value+',".session_id()."', do_element);".'">';
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



function ko_specialfilter_groupshistory(&$code) {
	global $BASE_PATH, $smarty;

	if($smarty === NULL) require_once($BASE_PATH.'/inc/smarty.inc');

	$groupId = '';
	$fromDate = '';
	$toDate = '';
	$invert = '';

	$filterId = db_select_data('ko_filter', "WHERE `name` = 'groupshistory'", 'id', '', '', TRUE);
	$filterId = $filterId['id'];
	foreach ($_SESSION['filter'] as $k => $ff) {
		if (!is_numeric($k)) continue;
		if ($ff[0] == $filterId) {
			$groupId = $ff[1][1];
			$fromDate = $ff[1][2];
			$toDate = $ff[1][3];
			$status = $ff[1][4];
		}
	}

	$groupData = kota_groupselect($groupId);
	array_walk_recursive($groupData, 'utf8_encode_array');
	$input = array(
		'type' => 'groupsearch',
		'name' => 'var1',
		'single' => TRUE,
		'include_roles' => TRUE,
		'data' => json_encode($groupData),
	);
	$smarty->assign('input', $input);
	$html = $smarty->fetch('ko_formular_elements.tmpl');

	$html .= getLL('filter_var2_groupshistory').'<br>';
	$input = array(
		'type' => 'datepicker',
		'name' => 'var2',
		'value' => $fromDate,
	);
	$smarty->assign('input', $input);
	$html .= $smarty->fetch('ko_formular_elements.tmpl');

	$html .= getLL('filter_var3_groupshistory').'<br>';
	$input = array(
		'type' => 'datepicker',
		'name' => 'var3',
		'value' => $toDate,
	);
	$smarty->assign('input', $input);
	$html .= $smarty->fetch('ko_formular_elements.tmpl');

	$values = array('member', 'entered', 'left');
	$descs = array_map(function($el) {return getLL('filter_var4_groupshistory_'.$el);}, $values);
	$html .= getLL('filter_var4_groupshistory').'<br>';
	$input = array(
		'type' => 'select',
		'name' => 'var4',
		'value' => $status,
		'values' => $values,
		'descs' => $descs,
	);
	$smarty->assign('input', $input);
	$html .= $smarty->fetch('ko_formular_elements.tmpl');

	$html .= '<br>';

	$code = $html;
}//ko_specialfilter_groupdatafields()



function ko_specialfilter_groupsanniversary(&$code) {
	global $BASE_PATH, $smarty;

	if ($smarty === NULL) require_once($BASE_PATH . '/inc/smarty.inc');

	$input = [
		'type' => 'groupsearch',
		'name' => 'var1',
		'single' => TRUE,
		'include_roles' => FALSE,
	];
	$smarty->assign('input', $input);
	$html = $smarty->fetch('ko_formular_elements.tmpl');

	$html .= getLL('filter_var2_groupsanniversary') . '<br>';
	$input = [
		'type' => 'text',
		'name' => 'var2',
		'value' => '',
	];
	$smarty->assign('input', $input);
	$html .= $smarty->fetch('ko_formular_elements.tmpl');

	$html .= getLL('filter_var3_groupsanniversary') . '<br>';
	$html .= '<select class="input-sm form-control" name="var3">';
	$html .= '<option value="1">1 ' . getLL('year') . '</option>';
	$html .= '<option value="5">5 ' . getLL('years') . '</option>';
	$html .= '<option value="10">10 ' . getLL('years') . '</option>';
	$html .= '<option value="15">15 ' . getLL('years') . '</option>';
	$html .= '<option value="20">20 ' . getLL('years') . '</option>';
	$html .= '</select>';

	$html .= getLL('filter_var4_groupsanniversary') . '<br>';
	$html .= '<select class="input-sm form-control" name="var4">';
	$html .= '<option value="0">'.getLL('filter_var3_jubilee_0').'</option>';
	$html .= '<option value="1">'.getLL('filter_var3_jubilee_1').'</option>';
	$html .= '</select>';

	$code = $html;
}


function ko_specialfilter_lastchange(&$code, $params) {
	global $smarty;

	foreach($_SESSION["filter"] as $i => $f) {
		if(!is_numeric($i)) continue;
		$filter = db_select_data("ko_filter", "WHERE `id` = '".$f[0]."'", "*", "", "", TRUE);
		if($filter["name"] == "lastchange") {
			$value1 = $f[1][1];
			$value2 = $f[1][2];
		}
	}

	if($params[1] == 1) {
		$value1 = $value1 ? $value1 : "00.00.0000";
		$input = ['type' => 'datepicker','name' => 'var1','value' => $value1,];
		$smarty->assign('input', $input);
		$code = $smarty->fetch('ko_formular_elements.tmpl');
	} else if($params[1] == 2) {
		$value2 = $value2 ? $value2 : strftime("%d.%m.%Y", time());
		$input = ['type' => 'datepicker','name' => 'var2','value' => $value2,];
		$smarty->assign('input', $input);
		$code = $smarty->fetch('ko_formular_elements.tmpl');
	}

}//ko_specialfilter_lastchange()



/**
  * Generate a special filter for smallgroup regions
	*
	* This function is called by the filter for smallgroup regions by a FCN: definition in the SQL column
	* It generates the necessary code for the filter dynamically
	*
	* @param string HTML code for the filter
	*/
function ko_specialfilter_kleingruppen_region(&$code) {
	$code = '<select class="input-sm form-control" name="var1"><option value=""></option>';
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
	$code = '<select class="input-sm form-control" name="var1"><option value=""></option>';
	$rows = db_select_distinct("ko_kleingruppen", "type", "", "", TRUE);
	foreach($rows as $row) {
		if(!$row) continue;
		$code .= '<option value="'.$row.'" title="'.$row.'">'.ko_html($row).'</option>';
	}
	$code .= '</select>';
}//ko_spcialfilter_kleingruppen_type()


/**
 * Generate a special filter fields that are represented by select-input-elements
 *
 * @param string HTML code for the filter
 */
function ko_specialfilter_select_ll(&$code, $params) {
	//Parse parameters (0 is function name)
	$table = $params[1];
	$col = $params[2];

	$code = '<select class="input-sm form-control" name="var1"><option value=""></option>';
	$rows = kota_get_select_descs_assoc($table, $col);
	foreach($rows as $key => $value) {
		if(!$key) continue;
		$code .= '<option value="'.$key.'" title="'.$value.'">'.$value.'</option>';
	}
	$code .= '</select>';
}//ko_specialfilter_select_ll()


/**
  * Generate a special filter for enum fields
	*
	* @param string HTML code for the filter
	*/
function ko_specialfilter_enum_ll(&$code, $params) {
	//Parse parameters (0 is function name)
	$table = $params[1];
	$col = $params[2];

	$code = '<select class="input-sm form-control" name="var1"><option value=""></option>';
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

	$code = '<select class="input-sm form-control" name="var1">';
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
	$code = '<select class="input-sm form-control" name="var2"><option value="">'.getLL('all').'</option>';

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
	if(sizeof($itemset) > 0 && sizeof($teams) > 0) $code .= '<option value="" disabled="disabled">-- '.strtoupper(getLL('rota_teams_list_title')).' --</option>';
	if(sizeof($teams) > 0) {
		foreach($teams as $team) {
			$code .= '<option value="'.$team['id'].'" title="'.$team['name'].'">'.$team['name'].'</option>';
		}
	}

	$code .= '</select>';
}//ko_specialfilter_rota_teams()



function ko_specialfilter_filterpreset(&$code) {
	$filterset = array_merge((array)ko_get_userpref('-1', '', 'filterset', 'ORDER BY `key` ASC'), (array)ko_get_userpref($_SESSION['ses_userid'], '', 'filterset', 'ORDER BY `key` ASC'));

	$code = '<select class="input-sm form-control" name="var1">';
	foreach($filterset as $f) {
		$value = $f['id'];
		$desc = $f['user_id'] == '-1' ? getLL('itemlist_global_short').' '.$f['key'] : $f['key'];
	  $code .= '<option value="'.$value.'" title="'.$desc.'">'.$desc.'</option>';
	}
	$code .= '</select>';
}//ko_specialfilter_filterpreset()



function ko_specialfilter_crdate(&$code, $params) {
	global $smarty;

	foreach($_SESSION["filter"] as $i => $f) {
		if(!is_numeric($i)) continue;
		$filter = db_select_data("ko_filter", "WHERE `id` = '".$f[0]."'", "*", "", "", TRUE);
		if($filter["name"] == "crdate") {
			$value1 = $f[1][1];
			$value2 = $f[1][2];
		}
	}

	if($params[1] == 1) {
		$value1 = $value1 ? $value1 : "00.00.0000";
		$input = ['type' => 'datepicker','name' => 'var1','value' => $value1,];
		$smarty->assign('input', $input);
		$code = $smarty->fetch('ko_formular_elements.tmpl');
	} else if($params[1] == 2) {
		$value2 = $value2 ? $value2 : strftime("%d.%m.%Y", time());
		$input = ['type' => 'datepicker','name' => 'var2','value' => $value2,];
		$smarty->assign('input', $input);
		$code = $smarty->fetch('ko_formular_elements.tmpl');
	}

}//ko_specialfilter_crdate()

function ko_specialfilter_dobrange(&$code, $params) {
	global $smarty;

	foreach($_SESSION["filter"] as $i => $f) {
		if(!is_numeric($i)) continue;
		$filter = db_select_data("ko_filter", "WHERE `id` = '".$f[0]."'", "*", "", "", TRUE);
		if($filter["name"] == "dobrange") {
			$value1 = $f[1][1];
			$value2 = $f[1][2];
		}
	}

	if($params[1] == 1) {
		$value1 = $value1 ? $value1 : "00.00.0000";
		$input = ['type' => 'datepicker','name' => 'var1','value' => $value1,];
		$smarty->assign('input', $input);
		$code = $smarty->fetch('ko_formular_elements.tmpl');
	} else if($params[1] == 2) {
		$value2 = $value2 ? $value2 : strftime("%d.%m.%Y", time());
		$input = ['type' => 'datepicker','name' => 'var2','value' => $value2,];
		$smarty->assign('input', $input);
		$code = $smarty->fetch('ko_formular_elements.tmpl');
	}

}



function ko_specialfilter_information_lock(&$code) {
	$code = '<select class="input-sm form-control" name="var1"><option value="_all" title="'.getLL('all').'">'.getLL('all').'</option>';
	$rows = db_select_distinct('ko_donations', 'YEAR(date)', '', "WHERE `promise` = '0'");
	foreach($rows as $row) {
		if(!$row) continue;
		$code .= '<option value="'.$row.'" title="'.$row.'">'.ko_html($row).'</option>';
	}
	$code .= '</select>';
}


function ko_specialfilter_donation(&$code) {
	$code = '<select class="input-sm form-control" name="var1"><option value="_all" title="'.getLL('all').'">'.getLL('all').'</option>';
	$rows = db_select_distinct('ko_donations', 'YEAR(date)', '', "WHERE `promise` = '0'");
	foreach($rows as $row) {
		if(!$row) continue;
		$code .= '<option value="'.$row.'" title="'.$row.'">'.ko_html($row).'</option>';
	}
	$code .= '</select>';
}//ko_spcialfilter_donation()





function ko_specialfilter_donation_account(&$code) {
	$code = '<select class="input-sm form-control" name="var2"><option value=""></option>';
	$rows = db_select_data('ko_donations_accounts', 'WHERE 1=1', '*', 'ORDER BY number ASC, name ASC');
	foreach($rows as $row) {
		$code .= '<option value="'.$row['id'].'" title="'.ko_html($row['number'].' '.$row['name']).'">'.ko_html($row['number'].' '.$row['name']).'</option>';
	}
	$code .= '</select>';
}//ko_spcialfilter_donation_account()




function ko_specialfilter_smallgrouproles(&$code) {
	global $SMALLGROUPS_ROLES;

	$code = '<select class="input-sm form-control" name="var1">';
	foreach($SMALLGROUPS_ROLES as $role) {
		if(!$role) continue;
		$code .= '<option value="'.$role.'" title="'.getLL('kg_roles_'.$role).'">'.getLL('kg_roles_'.$role).'</option>';
	}
	$code .= '</select>';
}//ko_spcialfilter_smallgrouproles()




function ko_specialfilter_duplicates(&$code) {
	$code = '<select name="var1" class="input-sm form-control">';
	$useImportId = FALSE;
	$allFields = db_get_columns('ko_leute');
	foreach ($allFields as $field) {
		if ($field['Field'] == 'import_id') $useImportId = TRUE;
	}
	$fields = array('vorname-nachname', 'vorname-email', 'vorname-adresse', 'nachname-adresse', 'vorname-geburtsdatum', 'natel-geburtsdatum', 'firm-plz', 'firm-ort', 'firm', 'email');
	if ($useImportId) {
		$fields[] = 'import_id';
	}
	foreach($fields as $field) {
		$code .= '<option value="'.$field.'" title="'.getLL('leute_duplicates_'.$field).'">'.getLL('leute_duplicates_'.$field).'</option>';
	}
	$code .= '</select>';
}//ko_spcialfilter_duplicates()


function ko_specialfilter_families(&$code) {
	ko_get_familien($fams);
	$code  = '<select name="var1" class="input-sm form-control">';
	$code .= '<option value="0"></option>';
	foreach($fams as $fam) {
		$code .= '<option value="'.$fam["famid"].'" title="'.$fam['id'].'">'.$fam["id"].'</option>';
	}
	$code .= '</select>';
}


function ko_specialfilter_logins(&$code) {
	$code = '<select class="input-sm form-control" name="var1">';
	$code .= '<option value="_all">'.getLL('all').'</option>';
	$groups = db_select_data('ko_admingroups', "WHERE 1=1", '*');
	foreach($groups as $group) {
		if(!$group['id']) continue;
		$code .= '<option value="'.$group['id'].'" title="'.$group['name'].'">'.ko_html($group['name']).'</option>';
	}
	$code .= '</select>';
}//ko_spcialfilter_logins()




function ko_specialfilter_crm_project(&$code) {
	$code = '<select class="input-sm form-control" name="var1">';
	$code .= '<option value="_all">'.getLL('all').'</option>';
	ko_get_crm_projects($projects);
	foreach($projects as $project) {
		if(!$project['id']) continue;
		$code .= '<option value="'.$project['id'].'" title="'.$project['title'].'">'.ko_html($project['title']).'</option>';
	}
	$code .= '</select>';
}




function ko_specialfilter_crm_status(&$code) {
	$code = '<select class="input-sm form-control" name="var2">';
	$code .= '<option value="_all">'.getLL('all').'</option>';
	ko_get_crm_status($status);
	foreach($status as $status_) {
		if(!$status_['id']) continue;
		$code .= '<option value="'.$status_['id'].'" title="'.$status_['title'].'">'.ko_html($status_['title']).'</option>';
	}
	$code .= '</select>';
}




function ko_specialfilter_crm_contact(&$code) {
	$code = '<select class="input-sm form-control" name="var1">';
	$code .= '<option value="_all">'.getLL('all').'</option>';
	ko_get_crm_contacts($contacts, '', '', "ORDER BY `date` DESC");
	foreach($contacts as $contact) {
		if(!$contact['id']) continue;
		if (!ko_get_crm_contacts_access($contact)) continue;
		$code .= '<option value="'.$contact['id'].'" title="'.$contact['title'].'">'.ko_html($contact['title']).' ('.getLL('kota_ko_crm_contacts_type_'.$contact['type']).' '.getLL('time_on').' '.sql2datetime($contact['date']).')</option>';
	}
	$code .= '</select>';
}





function ko_specialfilter_random_ids(&$code) {
	$code = '<input type="number" class="input-sm form-control" name="var1" value="10">';
}




function ko_specialfilter_childrencount(&$code) {
	$code = '<input type="number" class="input-sm form-control" name="var1" value="2">';
}




function ko_specialfilter_dummy(&$code) {
	$code = '';
}



function ko_specialfilter_candidateadults(&$code) {
	$age = ko_get_setting('candidate_adults_min_age');
	$code = sprintf(getLL('filter_candidateadults_desc'), $age?$age:18);
}



function ko_specialfilter_text(&$code, $params) {
	array_shift($params);
	$varName = $params[0] ? $params[0] : 'var1';
	$defaultValue = $params[1] ? $params[1] : '';
	$triggerKey = $params[2] ? $params[2] : '13';
	$maxLength = $params[3] ? $params[3] : '';
	$htmlType = $params[4] ? $params[4] : 'text';

	if ($triggerKey === '') $on = '';
	else $on = ' onkeydown="if ((event.which == '.$triggerKey.') || (event.keyCode == '.$triggerKey.')) { this.form.submit_filter.click(); return false;} else return true;"';
	if ($maxLength === '') $ml = '';
	else $ml = ' maxlength="50"';
	$code = '<input type="'.$htmlType.'" class="input-sm form-control" name="'.$varName.'" value="'.$defaultValue.'"'.$on.$ml.'>';
}



function ko_specialfilter_mixedhousehold(&$code) {
	$code = '<select class="input-sm form-control" name="var1">';
	$code .= '<option value="mixed:ref_kath">'.getLL('filter_mixedhousehold_mixed_ref_kath').'</option>';
	$code .= '<option value="homogeneous:ref">'.getLL('filter_mixedhousehold_homogeneous_ref').'</option>';
	$code .= '<option value="homogeneous:kath">'.getLL('filter_mixedhousehold_homogeneous_kath').'</option>';
	$code .= '</select>';
}



function ko_specialfilter_addparents(&$code) {
	$code = '<select class="input-sm form-control" name="var1">';
	$code .= '<option value="0">'.getLL('filter_addparents_0').'</option>';
	$code .= '<option value="1">'.getLL('filter_addparents_1').'</option>';
	$code .= '<option value="2">'.getLL('filter_addparents_2').'</option>';
	$code .= '<option value="3">'.getLL('filter_addparents_3').'</option>';
	$code .= '<option value="4">'.getLL('filter_addparents_4').'</option>';
	$code .= '<option value="5">'.getLL('filter_addparents_5').'</option>';
	$code .= '</select>';
}



function ko_specialfilter_jubilee_step(&$code) {
	$code = '<select class="input-sm form-control" name="var2">';
	$code .= '<option value="1">1 '.getLL('year').'</option>';
	$code .= '<option value="5">5 '.getLL('years').'</option>';
	$code .= '<option value="10">10 '.getLL('years').'</option>';
	$code .= '</select>';
}



function ko_specialfilter_jubilee_yearoffset(&$code) {
	$code = '<select class="input-sm form-control" name="var3">';
	$code .= '<option value="0">'.getLL('filter_var3_jubilee_0').'</option>';
	$code .= '<option value="1">'.getLL('filter_var3_jubilee_1').'</option>';
	$code .= '</select>';
}



function ko_specialfilter_trackingentries(&$code) {
	global $access, $smarty;
	if (!is_array($access['tracking'])) ko_get_access('tracking');
	$allTrackings = db_select_data('ko_tracking', "WHERE 1", '*', "ORDER BY `name` ASC");
	$trackingGroups = db_select_data('ko_tracking_groups', "WHERE 1", '*', 'ORDER BY name ASC');
	$code = '<select class="input-sm form-control" name="var1" onchange="ko_update_filter_trackingentries_value()">';
	foreach ($allTrackings as $tracking) {
		if ($access['tracking']['ALL'] > 0 || $access['tracking'][$tracking['id']] > 0) {
			$groupName = $tracking['group_id'] ? $trackingGroups[$tracking['group_id']]['name'] : '';
			$code .= '<option value="'.$tracking['id'].'">'.$tracking['name'].($groupName ? ' ('.$groupName.')' : '').'</option>';
		}
	}
	$code .= '</select>';

	$html = getLL('filter_var2_trackingentries').'<br>';
	$input = array(
		'type' => 'datepicker',
		'name' => 'var2',
		'value' => '',
	);
	$smarty->assign('input', $input);
	$html .= $smarty->fetch('ko_formular_elements.tmpl');

	$html .= getLL('filter_var3_trackingentries').'<br>';
	$input = array(
		'type' => 'datepicker',
		'name' => 'var3',
		'value' => '',
	);
	$smarty->assign('input', $input);
	$html .= $smarty->fetch('ko_formular_elements.tmpl');
	$code .= $html;
	$code .= '<script>ko_update_filter_trackingentries_value();</script>';

	$code .= getLL('filter_var4_trackingentries').'<br>';
	$code .= '<div id="leute_filter_trackingentries_value" name="leute_filter_trackingentries_value"></div>';

	$code.= getLL('filter_trackingentries_withoutentry').'<br>';
	$checkbox = array(
		'type' => 'checkbox',
		'name' => 'var5',
		'value' => '',
		'desc2' => getLL('filter_trackingentries_withoutentry_desc')
	);
	$smarty->assign('input', $checkbox);
	$code.= $smarty->fetch('ko_formular_elements.tmpl');

}


function ko_specialfilter_taxonomy_term(&$code) {
	global $access;

	if(!ko_module_installed('taxonomy')) return FALSE;

	if (!is_array($access['taxonomy'])) ko_get_access('taxonomy');
	if($access['taxonomy']['MAX'] < 1) return FALSE;

	$terms = ko_taxonomy_get_terms();
	$structuredTerms = ko_taxonomy_terms_sort_hierarchically($terms);
	$code = "<select name=\"var1\" class=\"input-sm form-control\">
			    <option value=\"\"></option>";
	foreach($structuredTerms AS $structuredTerm) {
		if(!empty($structuredTerm['children'])) {
			$code .= "<option value='" . $structuredTerm['data']['id'] . "'>" . $structuredTerm['data']['name'] . "</option>";
			foreach($structuredTerm['children'] AS $childTerm) {
				$code .= "<option value='" . $childTerm['id'] . "'>&nbsp; &nbsp;" . $childTerm['name'] . "</option>";
			}
		} else {
			$code .= "<option value='" . $structuredTerm['data']['id'] . "'>" . $structuredTerm['data']['name'] . "</option>";
		}
	}

	$code.= "</select>";

	$roles = db_select_data("ko_grouproles", "WHERE 1=1");
	$code.= "<br />".getLL("kota_listview_ko_groups_roles")."<br /><select name=\"var2\" class=\"input-sm form-control\">
			    <option value=\"\"></option>";
	foreach($roles AS $role) {
		$code.= "<option value='".$role['id']."'>" . $role['name'] . "</option>";
	}

	$code.= "</select>";
}



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

	if($z_sort) {
		$sort = $z_sort;
	}
	else if($_SESSION["sort_events"] && $_SESSION["sort_events_order"]) {
		$add = '';
		$col = $_SESSION['sort_events'];
		if($col == 'eventgruppen_id') $col = 'eventgruppen_name';

		if($col == 'startdatum') $add = ',startzeit '.$_SESSION['sort_events_order'];
		else if($col == 'startzeit') $add = ',startdatum '.$_SESSION['sort_events_order'];
		else $add = ',startdatum '.$_SESSION['sort_events_order'].', startzeit '.$_SESSION['sort_events_order'];
		$sort = "ORDER BY ".$col." ".$_SESSION["sort_events_order"].$add;
	}
	else $sort = 'ORDER BY startdatum,startzeit,eventgruppen_name ASC';

	$e = db_select_data($table.'
	LEFT JOIN ko_event_rooms ON '.$table.'.room = ko_event_rooms.id 
	LEFT JOIN ko_eventgruppen ON '.$table.'.eventgruppen_id = ko_eventgruppen.id', 'WHERE 1=1 '.$z_where,
		$table.'.id AS id, '.$table.'.*, 
		ko_eventgruppen.name AS eventgruppen_name, ko_eventgruppen.farbe AS eventgruppen_farbe, 
		ko_eventgruppen.res_combined AS res_combined, ko_eventgruppen.type AS eg_type,
		ko_event_rooms.title AS room_title',
		$sort, $z_limit);

	//Add color dynamically
	ko_set_event_color($e);
}//ko_get_events()



function ko_get_amtstageevents_for_calendar($startstamp, $endstamp, $export = FALSE) {
	$amtstageEvents = $egs = [];
	if (empty($_SESSION['show_amtstage'])) {
		return $amtstageEvents;
	}

	$all_teams = ko_rota_get_all_teams();
	$selected_teams = db_select_data("ko_rota_teams", "WHERE id IN (" . implode(",", $_SESSION['show_amtstage']) . ")");
	$days = ko_rota_get_days($selected_teams, date('Y-m-d', $startstamp), date('Y-m-d', $endstamp));

	$scheduled_persons = [];
	foreach($days AS $date => $day) {
		foreach($day['schedule'] AS $team_id => $schedule) {
			$persons = explode(",", $schedule);
			foreach($persons AS $person) {
				$scheduled_persons[$person]['teams'][$team_id]['dates'][] = $date;
			}
		}
	}

	foreach($scheduled_persons AS $person_id => $scheduled_person) {
		foreach($scheduled_person['teams'] AS $team_id => $team) {
			if (!in_array($team_id, $_SESSION['show_amtstage'])) continue;

			$start_day = $team['dates'][0];
			foreach($team['dates'] AS $key => $date) {
				$next_entry = $team["dates"][$key+1];
				$next_day = date("Y-m-d", strtotime($date)+86400);
				if(!isset($scheduled_persons[$person_id]['teams'][$team_id]['grouped_days'])) {
					$scheduled_persons[$person_id]['teams'][$team_id]['grouped_days'][$date] = $date;
				}

				if(empty($next_entry)) {
					if(!isset($scheduled_persons[$person_id]['teams'][$team_id]['grouped_days'][$start_day])) {
						$start_day = $date;
					}
					$scheduled_persons[$person_id]['teams'][$team_id]['grouped_days'][$start_day] = $date;
				} else {
					if ($next_day === $next_entry) {
						$scheduled_persons[$person_id]['teams'][$team_id]['grouped_days'][$start_day] = $next_day;
					} else {
						$start_day = $next_entry;
						$scheduled_persons[$person_id]['teams'][$team_id]['grouped_days'][$start_day] = $next_entry;
					}
				}
			}
		}
	}

	foreach($scheduled_persons AS $person_id => $scheduled_person) {
		foreach($scheduled_person['teams'] AS $team_id => $team) {
			if (!in_array($team_id, $_SESSION['show_amtstage'])) continue;

			foreach ($team['grouped_days'] AS $date_start => $date_stop) {
				$amtstag_titles = [];
				$tooltip = "<h3>" . ko_get_person_name($person_id) . "</h3>";

				if(ko_get_userpref($_SESSION['ses_userid'],"daten_monthly_title") == "eventgruppen_id") {
					$amtstag_titles[] = $all_teams[$team_id]['name'];
				} else {
					if(is_numeric($person_id)) {
					$amtstag_titles[] = ko_get_person_name($person_id);
					} else {
						$tooltip = "<h3>\"" . $person_id . "\"</h3>";
						$amtstag_titles[] = "\"" . $person_id . "\"";
					}
					$tooltip.= getLL("rota_kota_prefix_ko_event") . ": " . $all_teams[$team_id]['name'] . "<br>";
				}

				$tooltip.= getLL("daten_date") . ": " . sql2datum($date_start) . " - " . sql2datum($date_stop);

				foreach($amtstag_titles AS $title) {
					$id = 'd' . $date_start."-".$date_stop. "-" . $person_id;
					if($export == TRUE) {
						$duration = ((strtotime($date_stop) - strtotime($date_start))/86400);
						if($duration === 0) $duration = 1;
						$amtstageEvents[$id] = [
							'id' => $id,
							'eventgruppen_id' => $id,
							'startdatum' => $date_start,
							'enddatum' => $date_stop,
							'startzeit' => '00:00:00',
							'endzeit' => '00:00:00',
							'duration' => $duration,
							'title' => $title,
							'eventgruppen_farbe' => $all_teams[$team_id]['farbe'],
						];

						$egs[$id] = [
							'id' => $id,
							'farbe' => $all_teams[$team_id]['farbe'],
							'name' => $title,
							'shortname' => $title
						];
					} else {
						if($date_start != $date_stop) {
							$date = new DateTime();
							$date->setTimestamp(strtotime($date_stop));
							$date->modify('+1 day');
							$date_stop = $date->format("Y-m-d");
						}
						$amtstageEvents[] = [
							'id' => $id,
							'start' => $date_start."T00:00:00",
							'end' => $date_stop."T23:59:59",
							'title' => utf8_encode($title),
							'allDay' => TRUE,
							'editable' => FALSE,
							'className' => 'fc-amtstag',
							'isMod' => '',
							'color' => '#' . $all_teams[$team_id]['farbe'],
							'textColor' => ko_get_contrast_color($all_teams[$team_id]['farbe']),
							'kOOL_tooltip' => utf8_encode($tooltip),
							'kOOL_editIcons' => '',
						];
					}
				}
			}
		}
	}

	return [$amtstageEvents, $egs];
}


/**
 * Liefert alle Absenzen
 */
function ko_get_absence(&$e, $where = '', $z_limit = '', $z_sort='') {
	$e = array();

	if (!empty($where) AND strtolower(substr($where, 0, 5)) != "where") {
		$where = "WHERE 1=1 " . $where;
	}

	$sort = "ORDER BY from_date ASC";
	$e = db_select_data("ko_event_absence", $where, "*", $sort, $z_limit);
}



/**
 * gets all reminders off type $mode and according to where clause
 *
 * @param array  $result contains the result i.e. the reminders
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



function ko_get_crm_projects(&$result, $z_where = '', $z_limit = '', $z_sort = '', $single = FALSE, $noIndex = FALSE) {
	$where = 'where 1 = 1 ';
	$result = db_select_data('ko_crm_projects', $where . $z_where, "*", $z_sort, $z_limit, $single, $noIndex);
} // ko_get_crm_projects


function ko_get_crm_status(&$result, $z_where = '', $z_limit = '', $z_sort = '', $single = FALSE, $noIndex = FALSE) {
	$where = 'where 1 = 1 ';
	$result = db_select_data('ko_crm_status', $where . $z_where, "*", $z_sort, $z_limit, $single, $noIndex);
} // ko_get_crm_projects


function ko_get_crm_contacts(&$result, $z_where = '', $z_limit = '', $z_sort = '', $single = FALSE, $noIndex = FALSE) {
	$where = 'where 1 = 1 ';
	if ($z_sort == '') $z_sort = ' ORDER BY `date` DESC ';
	$result = db_select_data('ko_crm_contacts', $where . $z_where, "*", $z_sort, $z_limit, $single, $noIndex);
} // ko_get_crm_contacts



function ko_get_crm_deadlines($showDates=FALSE, $key=null) {
	global $DATETIME;

	$r = array();
	for($i=1; $i<185; $i++) {
		$date = strftime($DATETIME['ddmy'], strtotime("+$i days"));
		if($i == 1) {
			$r[$i] = ($showDates ? $date.' (' : '') . $i.' '.getLL('day') . ($showDates ? ')' : '');
		}
		else if($i < 15) {
			$r[$i] = ($showDates ? $date.' (' : '') . $i.' '.getLL('days') . ($showDates ? ')' : '');
		}
		else {
			if(!$showDates && $i % 7 != 0) continue;

			$w = floor($i/7);
			$r[$i] = ($showDates ? $date.' (' : '') . $w.' '.getLL('weeks') . ($showDates ? ')' : '');
		}
	}

	if($key == null) {
		return $r;
	} else {
		return $r[$key];
	}
}//ko_get_crm_deadlines()


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
 * Returns eventgroups together with the calendar they are assigned to to be used in selects
 *
 */
function ko_get_eventgruppen_for_select() {

	$grpsNoCal = db_select_data('ko_eventgruppen', "WHERE `calendar_id` = 0", '*, "" as calendar_name', 'ORDER BY name asc, id asc', '', FALSE, TRUE);
	$grps = db_select_data('ko_eventgruppen g, ko_event_calendar c', "WHERE g.`calendar_id` = c.`id`", 'g.*, c.name as calendar_name', 'ORDER BY c.name asc, g.name asc, g.id asc', '', FALSE, TRUE);

	$data = array();
	$lastCal = NULL;
	foreach ($grps as $grp) {
		if ($lastCal === NULL || $grp['calendar_id'] != $lastCal) {
			$data[] = array('value' => '', 'desc' => "--- {$grp['calendar_name']} ---", 'disabled' => TRUE);
			$lastCal = $grp['calendar_id'];
		}
		$data[] =  array('value' => $grp['id'], 'desc' => "&nbsp;&nbsp;&nbsp;{$grp['name']}");
	}
	if (sizeof($grpsNoCal) > 0) {
		$data[] = array('value' => '', 'desc' => "--- ".getLL('daten_no_calendar')." ---", 'disabled' => TRUE);
	}
	foreach ($grpsNoCal as $grp) {
		$data[] =  array('value' => $grp['id'], 'desc' => "&nbsp;&nbsp;&nbsp;{$grp['name']}");
	}

	return $data;
}



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
			$descs[$gid] = $cal["name"].':'.$group["name"];
		}
	}//foreach(cals)
	//Add all event groups without calendars
	$groups = db_select_data("ko_eventgruppen", "WHERE `calendar_id` = '0' AND `type` = '0'", "*", "ORDER BY `name` ASC");
	foreach($groups as $gid => $group) {
		if($access['daten'][$gid] < $rights) continue;
		$values[$gid] = $gid;
		$descs[$gid] = ':'.$group["name"];
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
												 "WHERE ($where_email a.email != '') AND (a.disabled = '0' OR a.disabled = '') AND l.deleted = '0' AND l.hidden = '0'",
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




function ko_daten_set_event_slug($id) {
	if(!$id) return;

	$event = db_select_data('ko_event', "WHERE `id` = '$id'", 'id,title,startdatum', '', '', TRUE);
	if(!$event['title']) return;

	//Convert title
	//setlocale(LC_CTYPE,'de_CH');
	$value = iconv('latin1','ASCII//TRANSLIT',$event['title']);
	$value = strtolower($value);
	$value = preg_replace('#[^a-z0-9_]+#','-',$value);
	$value = trim($value, '-');

	//Prepend with year and month: YYYY/MM/Title
	$value = date('Y/m', strtotime($event['startdatum'])).'/'.$value;

	//Add id if slug is not unique
	$others = db_get_count('ko_event', 'id', "AND `slug` = '$value' AND `id` != '$id'");
	if($others > 0) {
		$value .= '-'.$id;
	}

	db_update_data('ko_event', "WHERE `id` = '$id'", array('slug' => $value));

	return $value;
}//ko_daten_set_event_slug()




function ko_get_event_rooms($id = '', $z_where = '', $z_limit = '') {
	if($id) {
		$where = "WHERE `id` = '$id'";
		$single = TRUE;
	} else {
		$where = $z_where;
		$single = FALSE;
	}

	if($_SESSION["sort_rooms"] && $_SESSION["sort_rooms_order"]) {
		$order = "ORDER BY " . $_SESSION["sort_rooms"] . " " . $_SESSION["sort_rooms_order"];
	} else {
		$order = "ORDER BY title ASC";
	}


	return db_select_data('ko_event_rooms', $where, '*', $order, $z_limit, $single);
}









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
 * Liefert eine Liste alle vorkommenden Anreden aus ko_leute
 */
function ko_get_all_anreden(&$c) {
	$c = db_select_distinct('ko_leute', 'anrede', '', "WHERE `deleted` = '0'");
}//ko_get_all_countries()



/**
	* Liefert Personen-Daten
	*/
function ko_get_leute(&$p, $z_where = "", $z_limit = "", $z_cols = "", $z_sort = "", $apply_version=TRUE) {
	global $ko_menu_akt;

	//only apply sorting if not a MODULE-column is to be sorted
	$z_sort = trim($z_sort);
	if(is_string($z_sort) && !empty($z_sort)) {
		$sort = $z_sort;
	} else if(is_array($_SESSION["sort_leute"]) && is_array($_SESSION["sort_leute_order"])) {
		$sort_add = array();
		foreach($_SESSION["sort_leute"] as $i => $col) {
			if(!trim($col)) continue;
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
			if($old === FALSE) continue;

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
	$famcity = "";
	if($fam["ort"]) {
		$famcity = strlen($fam["ort"]) > $max_len ? substr($fam["ort"], 0, $max_len).".." : $fam["ort"];
	}
	// address
	$famaddress = "";
	if ($fam['adresse']) {
		$famaddress = $fam['adresse'];
	}
	//single members of the family
	$fammembers = "";
	$fullfammembers = "";
	if($num_members > 0) {
		foreach($members as $p) {
			$fammembers .= strtoupper(substr($p["vorname"], 0, 1)).",";
			$fullfammembers .= $p["vorname"].",";
		}
		$fammembers = "(".substr($fammembers, 0, -1).")";
		$fullfammembers = substr($fullfammembers, 0, -1);
	}//if(num_members>0)

	//put it all together into the new fam-id
	$fam["id"] = $famlastname.($famcity?" ".getLL("from")." ".$famcity:"").($fammembers?" ".$fammembers:"");
	$fam["detailed_id"] = $famlastname.(($famcity||$famaddress)?" ".getLL("from")." ":"").($famaddress?$famaddress.", ":"").($famcity?$famcity:"").($fullfammembers?" (".$fullfammembers.")":"");

	//save lastname in family, even if lastname is not a family field. This makes the export work with lastnames
	if(!in_array("nachname", $COLS_LEUTE_UND_FAMILIE)) $fam["nachname"] = $famlastname;
}//ko_add_fam_id()



/**
  * Liefert alle Familien
	* inkl. ID
	*/
function ko_get_familien(&$fam) {
	global $ko_menu_akt;

	$fam = array();

	//Get all families
	$query = "SELECT * FROM ko_familie ORDER BY nachname ASC";
	$result = mysqli_query(db_get_link(), $query);
	while($row = mysqli_fetch_assoc($result)) {
		$fam[$row["famid"]] = $row;
	}

	//Get all family members once, so they don't have to be retrieved inside the loop
	$members = array();
	$deleted = ($ko_menu_akt == "leute" && ko_get_userpref($_SESSION["ses_userid"], "leute_show_deleted") == 1) ? " AND `deleted` = '1' " : " AND `deleted` = '0' ";
	$deleted .= ko_get_leute_hidden_sql();
	$result = mysqli_query(db_get_link(), "SELECT * FROM ko_leute WHERE `famid` != '0' $deleted ORDER BY `famid` ASC");
	while($row = mysqli_fetch_assoc($result)) {
		$members[$row["famid"]][] = $row;
	}

	//Add family ID
	foreach($fam as $i => $f) {
		//Ignore families with no members (maybe from hidden persons, where families are still present)
		// Speedup if many hidden addresses are present, because ko_add_fam_id() takes some time
		if(sizeof($members[$f['famid']]) < 1) {
			unset($fam[$i]);
			continue;
		}

		ko_add_fam_id($fam[$i], $members[$f["famid"]]);
		$sort[$fam[$i]["id"].$fam[$i]['famid']] = $fam[$i]["famid"];
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
		$data['lastchange'] = date('Y-m-d H:i:s');

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





function ko_get_person_by_email($email) {
	global $LEUTE_EMAIL_FIELDS;

	$email = trim($email);

	if (!$email) return NULL;

	$wheres = array();
	foreach (array_merge(array('email'), $LEUTE_EMAIL_FIELDS) as $field) {
		$wheres[] = "`{$field}` = '{$email}'";
	}
	if (sizeof($wheres) > 0) {
		return db_select_data('ko_leute', "WHERE (".implode(' OR ', $wheres).") AND `hidden` = '0' AND `deleted` = '0'", '*', '', 'LIMIT 1', TRUE);
	} else {
		return NULL;
	}
}









/**
 * Changes the address fields of the given address record according to the given rectype
 *
 * @param array $p Address record from ko_leute
 * @param string $force_rectype Specify the rectype that should be applied. If none is given, this persons default rectype ($p[rectype]) will be used
 * @param array $addp Will hold additional addresses if rectype uses reference to other addresses, which might be more than one
 * @returns array $p Returns the address with the applied changes to the fields defined for this rectype
 */
function ko_apply_rectype($p, $force_rectype='', &$addp = null) {
	global $RECTYPES;

	if(!is_array($p)) return $p;

	$target_rectype = $force_rectype != '' ? $force_rectype : $p['rectype'];
	if($target_rectype == '') $target_rectype = '_default';
	$addp = array();

	if($target_rectype && isset($RECTYPES[$target_rectype]) && is_array($RECTYPES[$target_rectype])) {
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

					default: break;
				}
			}
		}
	}

	return $p;
}//ko_apply_rectype()




/**
  * Get the display name for a person
	* @param mixed $id Integer with person's ID or array containing person's db entry
	*/
function ko_get_person_name($id, $format='') {
	if(is_array($id)) {
		$p = $id;
	} else {
		ko_get_person_by_id($id, $p);
	}

	if($format != '') {
		return strtr($format, $p);
	} else {
		if(trim($p['vorname']) == '' && trim($p['nachname']) == '') {
			$val = $p['firm'].($p['department'] ? ' ('.$p['department'].')' : '');
		} else {
			$val = trim($p['vorname'].' '.$p['nachname']);
		}
		return $val;
	}
}//ko_get_person_name()



/**
  * Liefert alle Personen einer Familie
	*/
function ko_get_personen_by_familie($famid, &$p, $function="") {
	global $KOTA;

	if(!is_numeric($famid) || $famid <= 0) return FALSE;
	$p = array();
	$z_where = '';

	if((!is_array($function) && $function != '') || (is_array($function) && sizeof($function) > 0)) {
		if(!is_array($function)) $function = array($function);
		if (!is_array($KOTA['ko_leute'])) ko_include_kota(array('ko_leute'));
		$fam_functions = $KOTA['ko_leute']['famfunction']['form']['values'];
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
	$r = array();
	$z_where  = "WHERE `_leute_id` <> '0' AND `_group_id` = ''";  //don't show web-group-subscriptions
	$z_where .= ($id != "") ? " AND `_id`='$id'" : "";
	$query = "SELECT * FROM `ko_leute_mod` $z_where ORDER BY _crdate DESC";
	$result = mysqli_query(db_get_link(), $query);
	while($row = mysqli_fetch_assoc($result)) {
		$r[$row["_id"]] = $row;
	}
}//ko_get_mod_leute()



/**
	* Liefert eine Liste aller (oder wenn id definiert ist nur diesen Eintrag) zu moderierenden Gruppen-Anmeldungen (aus Tabelle ko_leute_mod)
	*/
function ko_get_groupsubscriptions(&$r, $gsid='', $uid='', $gid='') {
	global $access;

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
	$result = mysqli_query(db_get_link(), $query);
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
 * Returns a list of all entries in ko_leute_revisions (where access is granted)
 *
 * @param $r: will contain the revisions
 * @param bool|FALSE $joinPeople: define if the revisions should be joined with the addresses
 * @param int $id: the id of the requested entry. If not set, all entries are returned
 */
function ko_get_leute_revisions(&$r, $joinPeople=FALSE, $id=NULL) {
	global $access, $LEUTE_REVISIONS_FIELDS;

	if (!isset($access['leute'])) ko_get_access('leute');

	// Get revisions
	$where = '';
	if ($id !== NULL) $where = " WHERE r.`id` = '{$id}'";

	$select = "r.*";
	if ($joinPeople) {
		$leuteColsSelects = array();
		foreach ($LEUTE_REVISIONS_FIELDS as $leuteCol) {
			$leuteColsSelects[] = "l.`{$leuteCol}` as `{$leuteCol}`";
		}
		$select .= ", " . implode(', ', $leuteColsSelects);
		$select .= ", l.id AS orig_leute_id";
		$r = db_select_data("ko_leute_revisions r LEFT JOIN ko_leute l ON r.`leute_id` = l.`id`", $where, $select, "ORDER BY r.crdate DESC");
	} else {
		$r = db_select_data("ko_leute_revisions r JOIN ko_leute l ON r.`leute_id` = l.`id`", $where, $select, "ORDER BY r.crdate DESC");
	}

	if ($access['leute']['ALL'] <= 3) {
		foreach ($r as $k => $v) {
			$leuteId = $v['leute_id'];
			if (!$leuteId || $access['leute'][$leuteId] <= 3) unset($r[$k]);
		}
	}

	if ($id !== NULL) {
		if (sizeof($r) > 0) {
			$r = array_pop($r);
		} else {
			$r = NULL;
		}
	}

}//ko_leute_get_revisions()



function ko_leute_merge_ids($ids, &$addressChanged, $all_datafields=NULL, $forceKeepID=0) {
	global $LEUTE_EXCLUDE, $KOTA;

	if (!$all_datafields) {
		$all_datafields = db_select_data("ko_groups_datafields", "WHERE 1=1", "*");
	}

	$cols = db_get_columns('ko_leute_mod');
	$mod_cols = array();
	foreach($cols as $col) {
		$mod_cols[] = $col['Field'];
	}

	if(sizeof($ids) < 2) return;
	$groups = $smallgroups = $num_fields = array();
	foreach($ids as $id) {
		ko_get_person_by_id($id, $person);
		foreach($person as $k => $v) {
			if(in_array($k, $LEUTE_EXCLUDE)) continue;
			if($k == 'groups') {
				$groups = array_merge($groups, explode(',', $v));
				$num_fields[$id] += sizeof(explode(',', $v));
			} else if($k == 'smallgroups') {
				$smallgroups = array_merge($smallgroups, explode(',', $v));
				$num_fields[$id] += sizeof(explode(',', $v));
			} else {
				if((string)$v != '' && (string)$v != '0' && (string)$v != '0000-00-00') $num_fields[$id]++;
			}
		}
	}

	//Keep specified ID if given as param (used e.g. for leute_revisions)
	// create fake num_fields array with ID to be kept with highest value
	$forceKeepID = intval($forceKeepID);
	if($forceKeepID > 0 && in_array($forceKeepID, array_keys($num_fields))) {
		$new_num_fields = array();
		foreach($num_fields as $k => $v) {
			if($k == $forceKeepID) $new_num_fields[$k] = max($num_fields) + 1;
			else $new_num_fields[$k] = $v;
		}
		$num_fields = $new_num_fields;
	}
	//Else just keep the one with the most data (highest num_fields value)
	arsort($num_fields);

	$first = TRUE;
	foreach($num_fields as $id => $num) {
		if($first) {
			$first = FALSE;

			//Get address record to be kept
			$merged_id = $id;
			ko_get_person_by_id($merged_id, $merged_person);
			$merged_df = ko_get_datafields($merged_id);

			//Store current data for person's history
			ko_save_leute_changes($merged_id, $merged_person, $merged_df);

			//Clean array containing merged groups, smallgroups etc.
			$groups = array_unique($groups);
			foreach($groups as $k => $v) {
				if(!$v) unset($groups[$k]);
			}
			$smallgroups = array_unique($smallgroups);
			foreach($smallgroups as $k => $v) {
				if(!$v) unset($smallgroups[$k]);
			}

			//Update record with merged group, smallgroup etc. data
			db_update_data('ko_leute', "WHERE `id` = '$id'", array('groups' => implode(',', $groups),
				'smallgroups' => implode(',', $smallgroups),
				'lastchange' => date('Y-m-d H:i:s')));
		}
		else {
			ko_get_person_by_id($id, $person);
			$new = $test = array();
			foreach($person as $k => $v) {
				if(!in_array($k, $mod_cols)) continue;
				$test[$k] = $merged_person[$k];
				$new[$k] = $v;
			}
			//Check for differences in address record. Only create moderation entry if differences are present
			$doublediff = array_merge(array_diff($test, $new), array_diff($new, $test));
			if(sizeof($new) > 0 && sizeof($doublediff) > 0) {
				$new['_leute_id'] = $merged_id;
				$new['_crdate'] = date('Y-m-d H:i:s');
				$new['_bemerkung'] = getLL('leute_merged_comment');
				db_insert_data('ko_leute_mod', $new);
				$addressChanged = TRUE;
			}

			//Group datafield data
			$all_dfs = db_select_data('ko_groups_datafields', 'WHERE 1');
			$df = ko_get_datafields($id);
			foreach($df as $dfid => $data) {
				$found = FALSE;
				foreach($merged_df as $mid => $mdata) {
					if($mdata['group_id'] == $data['group_id'] && $mdata['datafield_id'] == $data['datafield_id']) {
						$found = TRUE;
						if($data['value'] != $mdata['value']) {
							if($mdata['value'] == '') {  //Entry of kept person is empty so use the value from the double entry
								db_update_data('ko_groups_datafields_data', "WHERE `group_id` = '".$mdata['group_id']."' AND `datafield_id` = '".$mdata['datafield_id']."' AND `person_id` = '".$mdata['person_id']."'", array('value' => $data['value']));
							} else if($data['value'] == '') {  //Entry of the double entry is empty so keep the other
								//Do nothing
							} else {  //Both value contain something, so store both
								//Merge values for multiselect fields
								if($all_datafields[$data['datafield_id']]['type'] == 'multiselect') {
									db_update_data('ko_groups_datafields_data', "WHERE `group_id` = '".$mdata['group_id']."' AND `datafield_id` = '".$mdata['datafield_id']."' AND `person_id` = '".$mdata['person_id']."'", array('value' => array_unique(array_merge(explode(',', $mdata['value']), explode(',', $data['value'])))));
								} else if($all_datafields[$data['datafield_id']]['type'] == 'select') {
									//Do nothing with different select values
								} else {
									//Concatenate values with ,
									db_update_data('ko_groups_datafields_data', "WHERE `group_id` = '".$mdata['group_id']."' AND `datafield_id` = '".$mdata['datafield_id']."' AND `person_id` = '".$mdata['person_id']."'", array('value' => $mdata['value'].', '.$data['value']));
								}
							}
						}
					}
				}
				if(!$found) {  //If kept person has no such record, then copy it
					unset($data['id']);
					$data['person_id'] = $merged_id;
					db_insert_data('ko_groups_datafields_data', $data);
				}
			}//foreach(df)

			//Rota entries
			$entries = db_select_data('ko_rota_schedulling', "WHERE `schedule` REGEXP '(^|,)$id(,|$)'", '*', '', '', FALSE, TRUE);
			foreach($entries as $entry) {
				$new = array();
				foreach(explode(',', $entry['schedule']) as $e) {
					if($e == $id) $e = $merged_id;
					$new[] = $e;
				}
				db_update_data('ko_rota_schedulling', "WHERE `team_id` = '".$entry['team_id']."' AND `event_id` = '".$entry['event_id']."'", array('schedule' => implode(',', $new)));
			}

			//Reassign crm entries
			db_update_data('ko_crm_mapping', "WHERE `leute_id` = '$id'", array('leute_id' => $merged_id));

			//Check if this person had been assigned to a login. If so, assign the new person to the same login
			db_update_data('ko_admin', "WHERE `leute_id` = '$id'", array('leute_id' => $merged_id));

			//Reassign all donations of this person to the new one
			db_update_data('ko_donations', "WHERE `person` = '$id'", array('person' => $merged_id));

			//Reassign all tracking entries of this person to the new one
			db_update_data('ko_tracking_entries', "WHERE `lid` = '$id'", array('lid' => $merged_id));

			//Merge absences
			db_update_data('ko_event_absence', "WHERE `leute_id` = '$id'", array('leute_id' => $merged_id));

			// merge ko_groups_assignment_history entries of both persons
			ko_groups_assignment_history_merge_entries($id, $merged_id);

			//Preferred fields
			$entries = db_select_data('ko_leute_preferred_fields', "WHERE `lid` = '$id'");
			foreach($entries as $entry) {
				if(db_get_count('ko_leute_preferred_fields', 'id', "AND `type` = '".$entry['type']."' AND `lid` = '$merged_id'") == 0) {
					db_insert_data('ko_leute_preferred_fields', array('type' => $entry['type'], 'lid' => $merged_id, 'field' => $entry['field']));
				}
			}

			//Update address relations on other addresses
			ko_include_kota('_all');
			$relationFields = array();
			foreach ($KOTA as $tableName => &$kotaDefinitions) {
				foreach($kotaDefinitions as $colID => &$col) {
					if(isset($col['form']) && $col['form']['type'] == 'peoplesearch' && !$col['form']['dontsave']) {
						$relationFields[] = "{$tableName}:{$colID}";
					}
				}
			}
			// manually add father, mother spouse to relation fields
			$relationFields = array_unique(array_merge($relationFields, array('ko_leute:spouse', 'ko_leute:mother', 'ko_leute:father')));
			if(sizeof($relationFields) > 0) {
				foreach($relationFields as $tableField) {
					list($table, $field) = explode(':', $tableField);
					$toBeMerged = db_select_data($table, "WHERE `$field` REGEXP '(^|,)$id(,|$)'");
					if(sizeof($toBeMerged) == 0) continue;
					foreach($toBeMerged as $merge) {
						if (!isset($merge['id'])) {
							ko_log('leute_merge_error', 'encountered peoplesearch field on '.$tableField.' without id: '.print_r($merge, TRUE));
							continue;
						}
						$cIDs = explode(',', $merge[$field]);

						foreach($cIDs as $k => $v) {
							if($v == $id) $cIDs[$k] = $merged_id;
						}

						db_update_data($table, "WHERE `id` = '".$merge['id']."'", array($field => implode(',', $cIDs)));
					}
				}
			}
			// Create log entry
			ko_log('addresses_merged', "Merged the following addresses: {$id},{$merged_id} -> {$merged_id}");

			//Hook: Allow plugins to add merging logic
			hook_leute_merge($id, $merged_id, $merged_person);

			//Delete person
			ko_leute_delete_person($id);

			//insert trace record
			db_insert_data('ko_leute_revision_trace',array('lapsed_id' => $id,'current_id' => $merged_id,'user_id' => $_SESSION['ses_userid'], 'date' => date('Y-m-d H:i:s')));
		}//if..else(first)
	}//foreach(num_fields)

	return $merged_id;
}






function ko_leute_delete_person($del_id) {
	ko_get_person_by_id($del_id, $del_person);

	//Check for column which prevent address from being deleted
	$ok = TRUE;
	$del_cols = explode(',', ko_get_setting('leute_no_delete_columns'));
	if(sizeof($del_cols) > 0) {
		foreach($del_cols as $c) {
			if(!$c) continue;
			if($del_person[$c] != '' && $del_person[$c] != '0000-00-00') $ok = FALSE;
		}
	}
	if ($ok) $ok = $ok && hook_leute_allow_delete($del_id, $del_person);

	if(!$ok) return FALSE;

	if($del_person['deleted'] == 1) {  //really delete already deleted entry
		//Check for setting if this is allowed
		if(ko_get_setting('leute_real_delete') != 1) return false;

		db_delete_data('ko_leute', "WHERE `id` = '$del_id'");

		//delete group datafields
		db_delete_data('ko_groups_datafields_data', "WHERE `person_id` = '$del_id'");
	}
	else {
		//add version entry
		ko_save_leute_changes($del_id, $del_person);

		//mark as deleted
		db_update_data('ko_leute', "WHERE `id` = '$del_id'", array('deleted' => '1', 'famid' => '0', 'lastchange' => date('Y-m-d')));

		//unset assigned login
		$login = db_select_data('ko_admin', "WHERE `leute_id` = '$del_id'", 'id,leute_id', '', '', true);
		if(is_array($login) && $login['leute_id'] == $del_id) {
			db_update_data('ko_admin', "WHERE `id` = '".$login['id']."'", array('leute_id' => '0'));
		}

		//unsubscribe from ezmlm
		if(defined('EXPORT2EZMLM') && EXPORT2EZMLM) {
			foreach(explode(',', $del_person['groups']) as $grp) {
				$gid = ko_groups_decode($grp, 'group_id');
				if($all_groups[$gid]['ezmlm_list']) ko_ezmlm_unsubscribe($all_groups[$gid]['ezmlm_list'], $all_groups[$gid]['ezmlm_moderator'], $del_person['email']);
			}
		}

		//set group datafields to deleted
		db_update_data('ko_groups_datafields_data', "WHERE `person_id` = '$del_id'", array('deleted' => '1'));

		//Update group count
		foreach(explode(',', $del_person['groups']) as $fullgid) {
			$group = ko_groups_decode($fullgid, 'group');
			if(!$group['maxcount']) continue;
			ko_update_group_count($group['id'], $group['count_role']);
		}
	}//if(deleted == 0)


	//LDAP
	if(ko_do_ldap()) {
		$ldap = ko_ldap_connect();
		ko_ldap_del_person($ldap, $del_id);
		ko_ldap_close($ldap);
	}

	//Create log entry
	ko_log_diff('delete_person', $del_person);

	//Delete family if the deleted person was the last member
	if($del_person['famid'] > 0) {
		$num = ko_get_personen_by_familie($del_person['famid'], $asdf);
		if($num <= 0) {
			db_update_data('ko_leute', "WHERE `famid` = '".$del_person['famid']."'", array('famid' => '0'));
			db_delete_data('ko_familie', "WHERE `famid` = '".$del_person['famid']."'");
		}
	}

	return TRUE;
}//ko_leute_delete_person()







/**
 * Merge assignment history entris of two persons. checks that no data overlap
 *
 * @param $p1 integer person 1
 * @param $p2 integer person 2
 *
 * @return bool returns false if error occurs
 */
function ko_groups_assignment_history_merge_entries($p1, $p2) {
	if (!is_numeric($p1) || !is_numeric($p2)) return FALSE;

	$history_person = db_select_data('ko_groups_assignment_history', "WHERE `person_id` = '$p1' OR `person_id` = '$p2'");
	$history = $history_sorted = $new_history = [];

	foreach ($history_person AS $key => $group) {
		$group_role = $group['group_id']."|".$group['role_id'];
		$start_person = $group['start'] . "|" . $group['person_id'];
		$history[$group_role][$start_person]['group_id'] = $group['group_id'];
		$history[$group_role][$start_person]['stop'] = $group['stop'];
		$history[$group_role][$start_person]['role'] = $group['role_id'];
	}
	foreach ($history AS $id => $group) {
		ksort($group); // sort chronologically
		$history_sorted[$id] = $group;
	}

	foreach ($history_sorted AS $id => $group) {
		$last_start = $last_stop = '';
		$still_in_group = FALSE;
		foreach ($group AS $start => $entry) {
			$start = substr($start, 0, 19); // get date without id
			if ($still_in_group == TRUE) continue;
			if ($entry['stop'] == '0000-00-00 00:00:00') {
				$new_history[$id][$start] = $entry;
				$still_in_group = TRUE;
			} else {
				if (($start > $last_stop) || (empty($last_start) && empty($last_stop))) {
					$new_history[$id][$start] = $entry;
				} elseif ($start < $last_stop) {
					// extend last entry
					if ($entry['stop'] > $last_stop) {
						$new_history[$id][$last_start]['stop'] = $entry['stop'];
						if ($entry['role'] != 0) {
							$new_history[$id][$last_start]['role'] = $entry['role'];
						}
					}
				}
			}
			$last_start = $start;
			$last_stop = $entry['stop'];
		}
	}

	// delete entries and set new
	db_delete_data("ko_groups_assignment_history", "WHERE `person_id` = '$p1' OR `person_id` = '$p2'");

	foreach($new_history AS $id => $history) {
		foreach($history AS $start => $entry) {
			$store = array(
				"group_id" => $entry['group_id'],
				"person_id" => $p2,
				"role_id" => $entry['role'],
				"start" => $start,
				"start_is_exact" => (stristr($start, "00:00:00") ? 0 : 1),
				"stop" => $entry['stop'],
				"stop_is_exact" =>  (stristr($entry['stop'], "00:00:00") ? 0 : 1),
			);
			db_insert_data("ko_groups_assignment_history", $store);
		}
	}

	return TRUE;
}


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
function ko_get_leute_col_name($groups_hierarchie=FALSE, $add_group_datafields=FALSE, $mode="view", $force=FALSE, &$rawgdata='', $colOrder='form', $includeGroupTitles=FALSE) {
	global $access, $KOTA;
	global $LEUTE_NO_FAMILY, $LEUTE_PARENT_COLUMNS;

	if(!isset($access['kg'])) ko_get_access('kg');
	if(!isset($access['groups'])) ko_get_access('groups');

	// if KOTA['ko_leute'] not yet loaded, then fetch it
	if (!isset($KOTA['ko_leute']['vorname']) && !isset($KOTA['ko_leute']['nachname'])) {
		ko_include_kota(array('ko_leute'));
	}

	//$r_all = unserialize(ko_get_setting("leute_col_name"));
	$dbCols = db_get_columns('ko_leute');
	$r = array();

	$ignoreCols = $LEUTE_NO_FAMILY ? array('famid', 'kinder', 'famfunction', 'father', 'mother', 'spouse', 'MODULEfamid_title') : array();
	$ignoreCols = array_merge($ignoreCols, $KOTA['ko_leute']['_form_layout']['_ignore_fields']);


	if ($colOrder == 'form') {
		$lastTitle = NULL;
		$lastTitleKey = NULL;
		if (!$LEUTE_NO_FAMILY) {
			if ($includeGroupTitles) {
				$ll = getLL('kota_group_title_ko_leute_general_family');
				$lastTitleKey = '___title_general_family___';
				$lastTitle = $ll ? $ll : getLL('kota_group_title__dummy');
			}
			foreach (array('famid', 'famfunction', 'father', 'mother', 'spouse', 'MODULEfamily_children', 'MODULEfamily_childrencount', 'MODULEfamid_title', 'MODULEfamid_famlastname') as $ff) {
				if ($lastTitleKey) {
					$r[$lastTitleKey] = $lastTitle;
					$lastTitleKey = $lastTitle = NULL;
				}
				$r[$ff] = getLL("kota_ko_leute_{$ff}");
			}
		}
		$dbColNames = array();
		foreach ($dbCols as $c) {
			$dbColNames[] = $c['Field'];
		}
		foreach ($KOTA['ko_leute']['_form_layout'] as $lTabKey => $lTab) {
			if(!isset($lTab['groups'])) continue;
			foreach ($lTab['groups'] as $lGroupKey => $lGroup) {
				if ($includeGroupTitles) {
					$ll = getLL("kota_group_title_ko_leute_{$lTabKey}_{$lGroupKey}");
					$lastTitleKey = "___title_{$lTabKey}_{$lGroupKey}___";
					$lastTitle = $ll ? $ll : getLL('kota_group_title__dummy');
				}
				foreach ($lGroup['rows'] as $lRow) {
					foreach ($lRow as $fName => $fWidth) {
						if (getLL("kota_ko_leute_{$fName}") && !in_array($fName, $ignoreCols) && in_array($fName, $dbColNames)) {
							if ($lastTitleKey) {
								$r[$lastTitleKey] = $lastTitle;
								$lastTitleKey = $lastTitle = NULL;
							}
							$r[$fName] = getLL("kota_ko_leute_{$fName}");
							if($fName == 'telegram_id') $r['MODULEtelegram_token'] = getLL("kota_ko_leute_telegram_token");
						}
					}
				}
			}
		}
		$first = TRUE;
		foreach ($dbColNames as $c) {
			if (getLL("kota_ko_leute_{$c}") && !in_array($c, $ignoreCols) && !in_array($c, $r)) {
				if ($first && $includeGroupTitles) {
					$lastTitleKey = "___title_others___";
					$lastTitle = getLL('kota_group_title__others');
				}
				if ($lastTitleKey) {
					$r[$lastTitleKey] = $lastTitle;
					$lastTitleKey = $lastTitle = NULL;
				}
				$r[$c] = getLL("kota_ko_leute_{$c}");
				if($c == 'telegram_id') $r['MODULEtelegram_token'] = getLL("kota_ko_leute_telegram_token");
				$first = FALSE;
			}
		}
		$r['MODULEage_ymd'] = getLL("kota_ko_leute_MODULEage_ymd");
	} else {
		foreach ($dbCols as $c) {
			if (getLL("kota_ko_leute_{$c['Field']}") && !in_array($c['Field'], $ignoreCols)) {
				$r[$c['Field']] = getLL("kota_ko_leute_{$c['Field']}");
				if($c['Field'] == 'telegram_id') $r['MODULEtelegram_token'] = getLL("kota_ko_leute_telegram_token");
			}
		}
		$r['MODULEage_ymd'] = getLL("kota_ko_leute_MODULEage_ymd");
	}

	//$r = $r_all[$_SESSION["lang"]];

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
				if(substr($i, 0, 9) == '___title_') continue;
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
	if(!$famok || $LEUTE_NO_FAMILY) {
		unset($r['mother']);
		unset($r['father']);
	} else {
		$r['MODULEfamid_famlastname'] = getLL('kota_ko_leute__famid_famlastname');
		$r['MODULEfamily_childrencount'] = getLL('kota_ko_leute__family_childrencount');
		$r['MODULEfamily_children'] = getLL('kota_ko_leute__family_children');

		// family salutation
		$r['MODULEfamid_title'] = getLL('kota_ko_leute_MODULEfamid_title');
	}

	// Salutation
	$r['MODULEsalutation_formal'] = getLL('kota_ko_leute_MODULEsalutation_formal');
	$r['MODULEsalutation_informal'] = getLL('kota_ko_leute_MODULEsalutation_informal');

	//Remove empty entries
	foreach($r as $k => $v) if(!$v) unset($r[$k]);

	if(ko_module_installed("taxonomy") && $access['taxonomy']['ALL'] >= 1) {
		$r['terms'] = getLL("kota_ko_leute_terms");
	}

	if(ko_module_installed("daten") && $access['daten']['ABSENCE'] >= 1) {
		$r['absence'] = getLL("kota_ko_leute_absence");
	}

	if(!$LEUTE_NO_FAMILY) {
		if ($includeGroupTitles) {
			$r["___title_parents___"] = getLL('kota_group_title__parents');
		}
		foreach ($LEUTE_PARENT_COLUMNS AS $parent_column) {
			$r['MODULEparent_' . $parent_column] = getLL('kota_ko_leute_' . $parent_column) . " " . getLL("kota_ko_leute_parents");
		}
	}

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
		if(!is_array($groups)) $groups = array();
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
	if (ko_module_installed('donations') || $force) {
		if (!is_array($access['donations'])) ko_get_access('donations');
		if ($access['donations']['MAX'] > 0) {

			$years = db_select_distinct('ko_donations', 'YEAR(`date`)', 'ORDER BY `date` DESC');
			$accounts = db_select_data('ko_donations_accounts', "WHERE 1=1", '*', 'ORDER BY number ASC, name ASC');

			foreach ($accounts as $account) {
				if ($access['donations'][$account['id']] < 1 && $access['donations']['ALL'] < 1) continue;
				$r['MODULEdonations' . 'a' . $account['id']] = getLL('donations_listtitle_short') . ' ' . $account['name'];
			}
			foreach ($years as $year) {
				$r['MODULEdonations' . $year] = getLL('donations_listtitle_short') . ' ' . $year;
				foreach ($accounts as $account) {
					if ($access['donations'][$account['id']] < 1 && $access['donations']['ALL'] < 1) continue;
					$r['MODULEdonations' . $year . $account['id']] = getLL('donations_listtitle_short') . ' ' . $year . ' ' . $account['name'];
				}
			}

			if (ko_module_installed('crm') || $force) {
				$crmProjects = db_select_data('ko_crm_projects', 'WHERE 1', '*', 'ORDER BY `number` ASC, `title` ASC');
			}

			// Reference number for accounts
			foreach ($accounts as $account) {
				if ($access['donations'][$account['id']] < 1 && $access['donations']['ALL'] < 1) continue;
				$r['MODULErefno_donations' . $account['id']] = getLL('donations_refno_listtitle_short') . ' ' . $account['name'];

				if (ko_module_installed('crm') || $force) {
					if (!is_array($access['crm'])) ko_get_access('crm');
					foreach ($crmProjects as $project) {
						if ($access['crm']['ALL'] > 0 || $access['crm'][$project['id']] || $force) {
							$r['MODULErefno_donations' . $account['id'] . ':' . $project['id']] = getLL('donations_refno_listtitle_short') . ' ' . $account['name'] . ': ' . $project['title'];
						}
					}
				}
			}//foreach(accounts as acount)

		}
	}

	//CRM
	if(ko_module_installed('crm') || $force) {
		if(!is_array($access['crm'])) ko_get_access('crm');
		if($access['crm']['MAX'] > 0) {
			$r['MODULEcrm'] = getLL('module_crm');
		}
	}

	if(!ko_module_installed('telegram')) {
		unset($r['telegram_id']);
		unset($r['MODULEtelegram_token']);
	}

	//subscription
	if(ko_module_installed('subscription') || $force) {
		ko_get_access('subscription');
		if($access['subscription']['MAX'] > 0) {
			$forms = db_select_data('ko_subscription_forms','','id,title,form_group,cruser');
			foreach($forms as $form) {
				$formAccess = max($access['subscription']['ALL'],$access['subscription'][$form['form_group']]);
				if($formAccess > 1 || ($formAccess == 1 && $form['cruser'] == $_SESSION['ses_userid'])) {
					$r['MODULEsubscription_'.$form['id']] = getLL('module_subscription').': '.$form['title'];
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
 * Find person in household who is first contactperson.
 *
 * @param int|array $data famid or list of person in family
 * @return Array best matching person is house
 */
function ko_get_family_housekeeper($data) {
	if(is_numeric($data)) {
		ko_get_personen_by_familie($data, $family);
	} elseif(is_array($data)) {
		$family = $data;
	} else {
		return FALSE;
	}

	$priority = ['husband', 'wife'];
	foreach ($priority AS $searchfor) {
		foreach ($family AS $id => $person) {
			if (array_search($searchfor, $person)) return $family[$id];
		}
	}

	return FALSE;
}

/**
 * returns family members according to the fields 'spouse', 'father', 'mother' on the person
 *
 * @param $personId: the id of the person from which to retrieve the family members
 * @return array: the family members
 */
function ko_get_family2($personId) {
	ko_get_person_by_id($personId, $person);
	$result = array();
	$relatives = array('spouse', 'father', 'mother');
	foreach ($relatives as $r) {
		if ($person[$r]) {
			ko_get_person_by_id($person[$r], $p, TRUE);
			$result[$r] = $p;
		}
	}
	return $result;
} // ko_get_family2()



/**
 * updates the spouse of a person. keeps the database consistent (no incompatible spouse definitions afterwards)
 *
 * @param        $personId: person of which to update spouse
 * @param string $spouseId: the id of the new spouse
 */
function ko_leute_set_spouse($personId, $spouseId=0, &$savedSnapshots = null, &$changes = null) {
	$changes = array();

	ko_get_person_by_id($personId, $person);
	$spouseId = (int)$spouseId;
	$person['spouse'] = (int)$person['spouse'];
	if ($spouseId == $person['spouse']) return;
	else {
		$updateLog = array();
		if ($person['spouse']) { // unset old spouses spouse
			ko_get_person_by_id($person['spouse'], $oldSpouse);
			db_update_data('ko_leute', "WHERE `id` = '{$person['spouse']}'", array('spouse' => ''));

			if (!in_array($oldSpouse['id'], $savedSnapshots)) {
				ko_save_leute_changes($oldSpouse['id'], $oldSpouse);
				$savedSnapshots[] = $oldSpouse['id'];
			}

			$updateLog[] = "{$person['spouse']}: {$oldSpouse['spouse']} -> 0";
			$changes[$person['spouse']] = array('old' => $oldSpouse['spouse'], 'new' => 0);
		}
		if ($spouseId) {
			ko_get_person_by_id($spouseId, $spouse);
			if ($spouse['spouse'] && $spouse['spouse'] != $personId) { // unset old spouse of spouse
				ko_get_person_by_id($spouse['spouse'], $oldSpousesSpouse);
				db_update_data('ko_leute', "WHERE `id` = '{$oldSpousesSpouse['id']}'", array('spouse' => ''));

				if (!in_array($oldSpousesSpouse['id'], $savedSnapshots)) {
					ko_save_leute_changes($oldSpousesSpouse['id'], $oldSpousesSpouse);
					$savedSnapshots[] = $oldSpousesSpouse['id'];
				}

				$updateLog[] = "{$spouse['spouse']}: {$oldSpousesSpouse['spouse']} -> 0";
				$changes[$spouse['spouse']] = array('old' => $oldSpousesSpouse['spouse'], 'new' => 0);
			}
			db_update_data('ko_leute', "WHERE `id` = '{$spouseId}'", array('spouse' => "{$personId}"));

			if (!in_array($spouse['id'], $savedSnapshots)) {
				ko_save_leute_changes($spouse['id'], $spouse);
				$savedSnapshots[] = $spouse['id'];
			}

			$updateLog[] = "{$spouseId}: {$spouse['spouse']} -> {$personId}";
			$changes[$spouseId] = array('old' => $spouse['spouse'], 'new' => $personId);
		}
		db_update_data('ko_leute', "WHERE `id` = '{$personId}'", array('spouse' => "{$spouseId}"));

		$updateLog[] = "{$personId}: {$person['spouse']} -> {$spouseId}";
		$changes[$personId] = array('old' => $person['spouse'], 'new' => $spouseId);

		ko_log('update_spouse', implode('; ', $updateLog));
	}
} // ko_leute_set_spouse()


function ko_leute_filter_make_bw_compatible(&$dbDef, &$filter) {
	if ($dbDef['sql1'] == 'kota_filter') {
		if (!is_array($filter[1][1]) || !$filter[1][1]['is_kota']) {
			if (sizeof($filter[1]) > 1) {
				ko_log('leute_bw_comp_filter_error', "the filter ".print_r($filter, TRUE)." could not be automatically translated to a kota_filter. filter definition in database: ".print_r($dbDef, TRUE));
			}
			$field = $dbDef['dbcol'];
			$value = $filter[1][1];
			$filter[1][1] = array(
				'is_kota' => 1,
				'kota_filter_data' => array(
					'ko_leute' => array(
						$field => $value,
					),
				),
			);
		}
	}
}



/**
 * Wendet die Leute-Filter an und gibt SQL-WHERE-Clause zurück
 * Ebenfalls verwendet, um Admin-Filter für Berechtigungen anzuwenden
 * Muss in ko.inc stehen (und nicht in leute/inc/leute.inc), damit ko_get_admin() immer Zugriff darauf hat --> z.B. für Dropdown-Menüs
 */
function apply_leute_filter($filter, &$where_code, $add_admin_filter=TRUE, $admin_filter_level='', $_login_id='', $includeAll=FALSE, $includeHidden=FALSE) {
	global $ko_menu_akt, $BASE_PATH;

	//Set login_id if given as parameter (needed from mailing.php because ses_userid is not set there)
	if($_login_id != '') $login_id = $_login_id;
	else $login_id = $_SESSION['ses_userid'];

	//Innerhalb einer Filtergruppe werden die Filter mit OR verknüpft
	$where_code = "";
	$q = array();
	if(is_array($filter)) {

		//Move addchildren, addparents, random_ids filter to the end, so it will be applied as the last filter
		$new = array(); $last = FALSE;
		foreach($filter as $f_i => $f) {
			ko_get_filter_by_id($f[0], $f_);
			if(in_array($f_['_name'], array('addchildren', 'addparents', 'random_ids'))) $last = $f;
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

					//In jeder Zeile alle Werte VAR[1-5] ersetzen
					$trans = array(
						"[VAR1]" => format_userinput($f[1][1], "sql"),
						"[VAR2]" => format_userinput($f[1][2], "sql"),
						"[VAR3]" => format_userinput($f[1][3], "sql"),
						"[VAR4]" => format_userinput($f[1][4], "sql"),
						"[VAR5]" => format_userinput($f[1][5], "sql"),
					);
					//Add regex escaping
					if(FALSE !== strpos($f_["sql$i"], 'REGEXP')) {
						foreach($trans as $k => $v) {
							$trans[$k] = str_replace(array('(', ')', '?'), array('\\\\(', '\\\\)', '\\\\?'), $v);
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

			//Handle kota_filter
			if($f_['sql1'] == 'kota_filter') {
				ko_leute_filter_make_bw_compatible($f_, $f);
				$kotaFilterData = $f[1][1]['kota_filter_data']['ko_leute'];
				if(!function_exists('kota_apply_filter')) ko_include_kota('ko_leute');
				$kota_where = kota_apply_filter('ko_leute', $kotaFilterData);
				$f_sql = $kota_where;
			}
			//Handle group filter if old groups should not be displayed
			else if($f_["_name"] == "group" && !ko_get_userpref($_SESSION['ses_userid'], 'show_passed_groups')) {
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
			else if($f_['_name'] == 'addchildren') {
				//Clear all other filters if checkbox "only children" has been ticked
				if($f[1][3] == 'true') $q = array();
				//Apply all filters except for this one and get all famids for the filtered people
				$cf = $filter;
				unset($cf[$f_i]);
				apply_leute_filter($cf, $cwhere, $add_admin_filter, $admin_filter_level, $login_id, $includeAll, $includeHidden);
				$parents = db_select_data('ko_leute', "WHERE 1=1 ".$cwhere, 'id');
				$parentIds = array();
				if ($parents) {
					foreach ($parents as $parent) {
						$parentIds[] = $parent['id'];
					}
				}
				if (sizeof($parentIds) > 0) {
					$f_sql = '( ( `mother` in ('.implode(',', $parentIds).') OR `father` in ('.implode(',', $parentIds).')) '.($f_sql ? 'AND '.$f_sql : '').' )';
				} else {
					$f_sql = ' 1=2';
				}
			}
			//Add parents filter
			else if($f_['_name'] == 'addparents') {
				//Clear all other filters if checkbox "only parents" has been ticked
				if($f[1][1] == '1') $q = array();
				//Apply all filters except for this one and get all famids for the filtered people
				$cf = $filter;
				unset($cf[$f_i]);
				apply_leute_filter($cf, $cwhere, $add_admin_filter, $admin_filter_level, $login_id, $includeAll, $includeHidden);
				$children = db_select_data('ko_leute', "WHERE 1=1 ".$cwhere);
				$parentIds = array();
				foreach ($children as $child) {
					if($f[1][1] == 2) {  //Parents for children without email
						if(!ko_get_leute_email($child, $childEmails)) {
							if($child['mother'] && ko_get_leute_email($child['mother'], $motherEmails)) $parentIds[] = $child['mother'];
							if($child['father'] && ko_get_leute_email($child['father'], $fatherEmails)) $parentIds[] = $child['father'];
						}
					}
					else if($f[1][1] == 3) {  //Parents for children without mobile
						if(!ko_get_leute_mobile($child, $childMobiles)) {
							if($child['mother'] && ko_get_leute_mobile($child['mother'], $motherMobiles)) $parentIds[] = $child['mother'];
							if($child['father'] && ko_get_leute_mobile($child['father'], $fatherMobiles)) $parentIds[] = $child['father'];
						}
					}
					else if($f[1][1] == 4) {  //Household leaders for children without email
						if(!ko_get_leute_email($child, $childEmails) && $child['famid']) {
							$parents = db_select_data('ko_leute', "WHERE `famid` = '".$child['famid']."' AND `famfunction` IN ('husband', 'wife') AND `deleted` = '0' AND `hidden` = '0'");
							if(sizeof($parents) > 0) {
								foreach($parents as $parent) {
									if(ko_get_leute_email($parent, $parentEmails)) $parentIds[] = $parent['id'];
								}
							}
						}
					}
					else if($f[1][1] == 4) {  //Household leaders for children without mobile
						if(!ko_get_leute_mobile($child, $childMobiles) && $child['famid']) {
							$parents = db_select_data('ko_leute', "WHERE `famid` = '".$child['famid']."' AND `famfunction` IN ('husband', 'wife') AND `deleted` = '0' AND `hidden` = '0'");
							if(sizeof($parents) > 0) {
								foreach($parents as $parent) {
									if(ko_get_leute_mobile($parent, $parentMobiles)) $parentIds[] = $parent['id'];
								}
							}
						}
					} else {
						if ($child['father']) $parentIds[] = $child['father'];
						if ($child['mother']) $parentIds[] = $child['mother'];
					}
				}
				if (sizeof($parentIds) > 0) {
					$f_sql = ' `id` in ('.implode(',', $parentIds).') ';
				} else {
					$f_sql = ' 1=2';
				}
			}
			// Add childrencount filter
			else if ($f_['_name'] == 'childrencount') {
				$count = format_userinput($f[1][1], 'uint');
				$f_sql = " (select count(`i_id`) from (select p2.`id` as i_id, p2.`mother` as i_m, p2.`father` as i_f from ko_leute p2) l2 where i_m = `id` or i_f = `id`) = '{$count}'";
			}
			//Find possible duplicates
			else if($f_['_name'] == 'duplicates') {
				$where = 'WHERE 1 ';
				//Get leute_admin_filter
				apply_leute_filter(array(), $dwhere, $add_admin_filter, $admin_filter_level, $login_id, $includeAll, $includeHidden);

				if($f[1][1] == "email") {
					// extend to search in all email fields
					$fields = $GLOBALS['LEUTE_EMAIL_FIELDS'];
					$where_email = '';
					foreach($fields as $field) {
						$where_email .= ' `'.$field.'` != \'\' OR ';
					}

					$where = $where . " AND (" . substr($where_email,0,-3) . ") " . $dwhere;
					$all = db_select_data('ko_leute', $where, '*');

					//Build test string for all persons
					$test = array();
					foreach($all as $person) {
						$value = array();
						foreach($fields as $field) $value[] = strtolower($person[$field]);
						$test[$person['id']] = implode('#', $value);
					}

					//Run again through all persons and fields to find dups
					foreach($all as $person) {
						foreach($test as $key => $entries) {
							if($key == $person['id']) continue;
							foreach($GLOBALS['LEUTE_EMAIL_FIELDS'] as $email_field) {
								if(stristr($entries, $person[$email_field]) !== FALSE) {
									$ids[] = $key;
								}
							}
						}
					}

					$ids = array_unique($ids);
					if(sizeof($ids) > 0) {
						$f_sql = " `id` IN ('".implode("','", $ids)."') ";
					}
				} else {
					$fields = explode('-', $f[1][1]);
					foreach($fields as $field) {
						$where .= ' AND (`'.$field.'` != \'\' AND `'.$field.'` != \'0000-00-00\') '.$dwhere;
					}

					$all = db_select_data('ko_leute', $where, '*');

					//Build test string for all persons
					$test = array();
					foreach($all as $person) {
						$value = array();
						foreach($fields as $field) $value[] = strtolower($person[$field]);
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
					}
				}

				if(!$f_sql) {
					$f_sql = ' 1=2 ';
				}
			}
			//Find candidate adults
			else if($f_['_name'] == 'candidateadults') {
				$minAge = ko_get_setting('candidate_adults_min_age');
				$minAge = $minAge ? $minAge : 18;
				$f_sql = " (`famfunction` = 'child' AND `famid` > 0 AND TIMESTAMPDIFF(hour,`geburtsdatum`,CURDATE())/8766 >= '{$minAge}') ";
			}
			//Get random ids
			else if($f_['_name'] == 'random_ids') {
				$number = format_userinput($f[1][1], 'uint');
				if (!$number) $number = 10;
				$cf = $filter;
				unset($cf[$f_i]);
				apply_leute_filter($cf, $cwhere, $add_admin_filter, $admin_filter_level, $login_id, $includeAll, $includeHidden);
				$randomIds = array();
				if ($cwhere) {
					$randomIds_ = db_select_data('ko_leute', "WHERE 1=1 " . $cwhere, 'id', 'ORDER BY RAND()', 'LIMIT ' . $number);
					foreach ($randomIds_ as $randomId_) {
						$randomIds[] = $randomId_['id'];
					}
				}

				if(sizeof($randomIds) == 0) {
					$f_sql = '';
				} else {
					$f_sql = " `id` IN ('".implode("','", array_unique($randomIds))."')";
				}
			}
			//Entries matched by fastfilter
			else if($f_['_name'] == 'fastfilter') {
				$search_string = format_userinput($f[1][1], 'sql');
				$search_words = explode(' ', $search_string);
				foreach ($search_words as $k => $search_word) {
					$search_words[$k] = trim($search_word);
				}
				$konj = array();
				$fast_filter = ko_get_fast_filter();
				if (sizeof($fast_filter) > 0 && sizeof($search_words) > 0) {
					$ff_cols = array();
					foreach($fast_filter as $id) {
						ko_get_filter_by_id($id, $ff);
						$filter_list[$id] = $ff;
						$col = ko_fastfilter_guess_dbcol($ff);
						if ($col) $ff_cols[$id] = $col;
					}
					if(!function_exists('kota_apply_filter')) ko_include_kota('ko_leute');
					foreach ($search_words as $search_word) {
						if (trim($search_word)) {
							$disj = array();
							foreach ($ff_cols as $id => $col) {
								if($filter_list[$id]['sql1'] == "kota_filter") {
									$kotaFilterData = [$filter_list[$id]['dbcol'] => $search_word];
									$kota_where = kota_apply_filter('ko_leute', $kotaFilterData);
									$disj[] = $kota_where;
								} else if(!empty($filter_list[$id]['sql1'])) {
									if(stristr($filter_list[$id]['sql1'],"regexp")) {
										$disj[] = str_replace("[VAR1]", str_replace("+","\\\+", $search_word), $filter_list[$id]['sql1']);
									} else {
										$disj[] = str_replace("[VAR1]", $search_word, $filter_list[$id]['sql1']);
									}
								} else {
									$disj[] = "`{$col}` REGEXP '".str_replace("+","\\\+", $search_word)."'";
								}
							}
							$konj[] = '('.implode(' OR ', $disj).')';
						}
					}
				}
				if(sizeof($konj) == 0) {
					$f_sql = '';
				} else {
					$f_sql = implode(' AND ', $konj);
				}
			}
			//Jubilee
			else if($f_['_name'] == 'jubilee') {
				$minAge = $f[1][1];
				if (!$minAge) $minAge = '0';
				$step = $f[1][2];
				if (!$step) $step = '5';
				$yearOffset = $f[1][3];
				$year = $yearOffset == '0' ? date('Y') : zerofill(intval(date('Y')) + 1, 4);
				$f_sql = "`geburtsdatum` <> '0000-00-00' AND {$year} - YEAR(`geburtsdatum`) >= {$minAge} AND MOD({$year} - YEAR(`geburtsdatum`), {$step}) = 0";
			}
			//Mixedhousehold
			else if($f_['_name'] == 'mixedhousehold') {
				list($mode, $targetConf) = explode(':', $f[1][1]);

				//Get data for analysis
				$fams = array();
				$entries = db_select_data('ko_leute', "WHERE `famid` > '0' && `famfunction` IN ('husband', 'wife') AND `hidden` = '0' AND `deleted` = '0' GROUP BY `famid` ASC, `confession` ASC", '`confession`, `famid`, count(`famid`) as num_members', '', '', FALSE, TRUE);
				foreach($entries as $entry) {
					$fams[$entry['famid']][$entry['confession']] += $entry['num_members'];
				}


				$useFamids = array();
				switch($mode) {
					case 'mixed':
						$targetConfs = explode('_', $targetConf);
						sort($targetConfs);
						foreach($fams as $famid => $fam) {
							if(sizeof($fam) != sizeof($targetConfs)) continue;
							$famConfs = array_keys($fam);
							sort($famConfs);

							if($famConfs == $targetConfs) $useFamids[] = $famid;
						}
						$f_sql = "(`famid` IN (".implode(',', $useFamids)."))";
					break;

					case 'homogeneous':
						foreach($fams as $famid => $fam) {
							if($fam[$targetConf] > 0 && sizeof($fam) == 1) $useFamids[] = $famid;
						}
						$f_sql = "((`famid` = '0' AND `confession` = '$targetConf') OR (`famid` IN (".implode(',', $useFamids).")))";
					break;
				}
			}
			// Age at the end of the year
			else if ($f_['_name'] == 'yearage') {
				$age_start = ($f[1][1] ? $f[1][1] : 0);
				$age_end = ($f[1][2] ? $f[1][2] : 1000);
				$year = date('Y');
				$f_sql = "`geburtsdatum` <> '0000-00-00' AND (
					({$year} - YEAR(`geburtsdatum`)) >= {$age_start} AND
					({$year} - YEAR(`geburtsdatum`)) <= {$age_end})";
			}
			// tracking entries
			else if ($f_['_name'] == 'trackingentries') {
				$trackingId = format_userinput($f[1][1], 'uint');
				$fromDate = sql_datum(format_userinput($f[1][2], 'date'));
				$toDate = sql_datum(format_userinput($f[1][3], 'date'));
				$value = format_userinput($f[1][4], 'sql');
				$withoutEntry = ($f[1][5] == 'true' ? TRUE : FALSE);

				if (!$trackingId) continue;

				$tracking = db_select_data('ko_tracking', "WHERE `id` = {$trackingId}", '*', '', '', TRUE);
				if (!$tracking) continue;

				if ($withoutEntry === TRUE) {
					// ignore $value from filter to find all persons without entry
					$where = "WHERE `deleted` = '0'";
					$filter_where = '';
					foreach(explode(',', $tracking['filter']) as $filter) {
						if(!$filter) continue;

						//Group ID
						if(strlen($filter) >= 7 && substr($filter, 0, 1) == 'g' && preg_match('/[g0-9:r,]*/', $filter)) {
							list($gid, $rid) = explode(':', $filter);
							if(ko_get_setting('tracking_add_roles') == 1 && strlen($filter) > 7) {  //Role
								$filter_where .= " `groups` REGEXP '".$gid."[g:0-9]*:".$rid."' OR ";
							} else {  //No role, just group
								$filter_where .= " `groups` REGEXP '$gid' OR ";
							}
						}
						//Small group
						else if(strlen($filter) == 4) {
							$filter_where .= " `smallgroups` REGEXP '$filter' OR ";
						}
						//base64 serialized filter preset array
						else if(substr($filter, 0, 1) == 'F') {
							$fa = unserialize(base64_decode(substr($filter, 1)));
							if(is_array($fa)) {
								apply_leute_filter($fa, $temp_where, FALSE);

								//Remove leading AND
								$temp_where = trim($temp_where);
								$temp_where = preg_replace('/^AND/', '', $temp_where);
								$filter_where .= " ($temp_where) OR ";
							} else {
								$where = ' AND 1=2';
							}
						}
					}
					if($filter_where != '') $where = $where.' AND ('.substr($filter_where, 0, -3).')';
					$persons_in_tracking = db_select_data("ko_leute", $where, "id");

					// remove all persons already in ko_tracking_entries
					$where = "WHERE tid = '" . $tracking['id'] ."'";
					if ($fromDate != '') {
						$where.= " AND `date` >= '{$fromDate}'";
					}
					if ($toDate != '') {
						$where.= " AND `date` <= '{$toDate}'";
					}
					$persons_submitted = db_select_data("ko_tracking_entries", $where, "lid");
					foreach($persons_submitted AS $person_submitted) {
						unset($persons_in_tracking[$person_submitted['lid']]);
					}

					if (sizeof($persons_in_tracking) > 0) {
						$f_sql = "`id` IN (" . implode(',', ko_array_column($persons_in_tracking, 'id')) . ")";
					} else {
						$f_sql = "1=2";
					}
				} else {
					if (!$value) continue;

					$whereParts = array("`tid` = {$trackingId}");
					if ($fromDate) $whereParts[] = "`date` >= '{$fromDate}'";
					if ($toDate) $whereParts[] = "`date` <= '{$toDate}'";

					$mustPersonMatches = 0;
					$mode = $tracking['mode'];
					switch ($mode) {
						case 'simple':
							$whereParts[] = "`value` = '1'";
							$mustPersonMatches = 1;
							break;
						case 'value':
						case 'valueNonNum':
							$whereParts[] = "`value` = '{$value}'";
							$mustPersonMatches = 1;
							break;
						case 'type':
							$types = ko_array_filter_empty(explode("\n", $tracking['types']));
							$valueArray = json_decode($value, TRUE);
							array_walk_recursive($valueArray, 'utf8_decode_array');
							$orParts = array();
							foreach ($types as $type) {
								if (array_key_exists($type, $valueArray) && $valueArray[$type]) {
									$orParts[] = "`type` = '{$type}' AND `value` = '{$valueArray[$type]}'";
									$mustPersonMatches++;
								}
							}
							$whereParts[] = '(' . implode(' OR ', $orParts) . ')';
							break;
						case 'typecheck':
							$types = ko_array_filter_empty(explode("\n", $tracking['types']));
							$atypes = ko_array_filter_empty(explode(',', $value));
							$orParts = array();
							foreach ($types as $type) {
								if (in_array($type, $atypes)) {
									$orParts[] = "`type` = '{$type}' AND `value` = '1'";
									$mustPersonMatches++;
								}
							}
							$whereParts[] = '(' . implode(' OR ', $orParts) . ')';
							break;
					}

					$ids = array();
					$nPersonMatches = db_select_data('ko_tracking_entries', "WHERE " . implode(' AND ', $whereParts), 'COUNT(`lid`) AS `cnt`, `lid`', '', "GROUP BY `lid`, `date` HAVING `cnt` >= {$mustPersonMatches}", FALSE, TRUE);
					if (sizeof($nPersonMatches) > 0) {
						$f_sql = "`id` IN (" . implode(',', array_unique(ko_array_column($nPersonMatches, 'lid'))) . ")";
					} else {
						$f_sql = "1=2";
					}
				}
			}
			// Past group assignments
			else if($f_['_name'] == 'groupshistory') {
				$groupId = format_userinput($f[1][1], 'group_role');
				$fromDate = sql_datum(format_userinput($f[1][2], 'date'));
				$toDate = sql_datum(format_userinput($f[1][3], 'date'));
				$status = format_userinput($f[1][4], 'alpha');

				if ($groupId) {
					$roleId = '';
					if (strpos($groupId, ':') !== FALSE) {
						list($groupId, $roleId) = explode(':', $groupId);
					}

					//Base group (selected by user)
					$groupId = intval(substr($groupId, 1));
					$groupIds = array($groupId);

					//Get all subgroups of the given group
					$childrenGroupIds = ko_groups_get_recursive('', FALSE, $groupId);
					foreach($childrenGroupIds as $cg) {
						if($cg['id'] && is_numeric($cg['id'])) $groupIds[] = $cg['id'];
					}
					$groupWhere = "AND `group_id` IN (".implode(',', $groupIds).")";


					$roleId = $roleId ? intval(substr($roleId, 1)) : '';
					$roleWhere = $roleId ? "AND `role_id` = {$roleId}" : '';

					$timeWhere = '';

					$now = date('Y-m-d');
					$toDateIsInPast = $now >= $toDate;
					$fromDateIsInPast = $now >= $fromDate;

					switch ($status) {
						case 'entered':
							$timeWhere .= $fromDate ? " AND `start` >= '{$fromDate} 00:00:00'" : '';
							$timeWhere .= $toDate ? " AND `start` <= '{$toDate} 23:59:59'" : '';
							break;
						case 'left':
							if ($fromDate) {
								$timeWhere .= " AND `stop` >= '{$fromDate}'";
							}
							if ($toDate) {
								if (!$toDateIsInPast) $timeWhere .= " AND (`stop` <= '{$toDate} 23:59:59' OR `stop` = '0000-00-00 00:00:00')";
								else $timeWhere .= " AND `stop` <= '{$toDate} 23:59:59'";
							}
							break;
						case 'member':
							if ($fromDate) {
								if ($fromDateIsInPast) $timeWhere .= " AND (`stop` >= '{$fromDate} 00:00:00' OR `stop` = '0000-00-00 00:00:00')";
								else $timeWhere .= " AND `stop` >= '{$fromDate} 00:00:00'";
							}
							$timeWhere .= $toDate ? " AND `start` <= '{$toDate} 23:59:59'" : '';
							break;
					}

					$where = "WHERE 1=1 {$groupWhere} {$roleWhere} {$timeWhere}";
				} else {
					$where = "WHERE 1=1";
				}

				$ids = db_select_distinct('ko_groups_assignment_history', 'person_id', '', $where);

				if(sizeof($ids) == 0) {
					$f_sql = '0=1';
				} else {
					$f_sql = '`id` IN ('.implode(',', $ids).')';
				}
			}
			else if($f_['_name'] == 'groupsanniversary') {
				$groupId = format_userinput($f[1][1], 'group_role');
				$minYear = format_userinput($f[1][2], 'uint');
				$step = format_userinput($f[1][3], 'uint');
				$yearInc = format_userinput($f[1][4], 'uint');
				$keyYear = date('Y') + $yearInc;

				if ($groupId) {
					//Base group (selected by user)
					$groupId = intval(substr($groupId, 1));
					$groupIds = array($groupId);

					//Get all subgroups of the given group
					$childrenGroupIds = ko_groups_get_recursive('', FALSE, $groupId);
					foreach($childrenGroupIds as $cg) {
						if($cg['id'] && is_numeric($cg['id'])) $groupIds[] = $cg['id'];
					}
					$groupWhere = "AND `group_id` IN (".implode(',', $groupIds).") ";

					$where = "WHERE `stop` = '0000-00-00 00:00:00' {$groupWhere}";
				} else {
					$where = "WHERE `stop` = '0000-00-00 00:00:00' ";
				}

				if ($minYear > 0) {
					$where.= " AND $keyYear - YEAR(`start`) >= '$minYear' ";
				}

				if ($step > 0) {
					$where.= " AND MOD($keyYear - YEAR(`start`), {$step}) = 0";
				}

				$ids = db_select_distinct('ko_groups_assignment_history', 'person_id', '', $where);

				if(sizeof($ids) == 0) {
					$f_sql = '0=1';
				} else {
					$f_sql = '`id` IN ('.implode(',', $ids).')';
				}
			}
			else if ($f_['_name'] == 'dobrange' || $f_['_name'] == 'crdate' || $f_['_name'] == 'last change') {
				$fromDate = (empty($f[1][1]) ? "0000-00-00" : sql_datum(format_userinput($f[1][1], 'date')));
				$toDate = (empty($f[1][2]) ? "9900-00-00" : sql_datum(format_userinput($f[1][2], 'date')));

				$f_sql = str_replace("[VAR1]",$fromDate, $f_['sql1']) . " AND ";
				$f_sql.= str_replace("[VAR2]",$toDate, $f_['sql2']);
			}
			else if ($f_['_name'] == "taxonomy") {
				require_once($BASE_PATH.'groups/inc/groups.inc');
				require_once($BASE_PATH.'taxonomy/inc/taxonomy.inc');
				$term_id = format_userinput($f[1][1], 'int');
				$search_role = format_userinput($f[1][2], 'int');

				$childTerms = ko_taxonomy_get_terms_by_parent($term_id);
				$term = ko_taxonomy_get_term_by_id($term_id);
				$childTerms[$term['id']] = $term;
				$groupids = [];

				foreach ($childTerms AS $childTerm) {
					$groupids = array_merge(ko_taxonomy_get_nodes_by_termid($childTerm['id'], "ko_groups"), $groupids);
				}

				$filter_where = "";

				foreach($groupids AS $group) {
					$groupmembers = ko_groups_get_recursive('', FALSE, zerofill($group['id'],6));
					$groupmembers[]['id'] = zerofill($group['id'], 6);
					foreach($groupmembers AS $member) {
						if(!empty($search_role)) {
							$filter_where .= " `groups` REGEXP 'g".$member['id']."[g:0-9]*:r".$search_role."' OR ";
						} else {
							$filter_where .= " `groups` REGEXP 'g".$member['id']."' OR ";
						}
					}
				}

				if (!empty($filter_where)) {
					$where = "WHERE " . substr($filter_where,0,-4);
					$persons = db_select_data("ko_leute", $where);
					if(count($persons) > 0) {
						$f_sql = '`id` IN (' . implode(',', array_keys($persons)) . ')';
					} else {
						$f_sql = ' 1=2';
					}
				} else {
					$f_sql = ' 1=2 ';
				}
			}

			// hook for plugins
			hook_apply_leute_filter($f_sql, $f_, $f);

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
						$f_sql = "( $f_sql OR `event_id` = '". $event['startdatum'] ."') ";
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
					//Get fp by id
					$presetID = format_userinput($f_sql, 'uint');
					if($presetID > 0) {
						$preset = db_select_data('ko_userprefs', "WHERE `id` = '$presetID'", '*', '', '', TRUE);

						//Get filter and convert it into a WHERE clause
						apply_leute_filter(unserialize($preset['value']), $filter_where, $add_admin_filter, $admin_filter_level, $login_id, $includeAll, $includeHidden);
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
					$year = $f[1][1];
					$accountId = $f[1][2];
					$where = "WHERE `person` != '' AND `promise` = '0'";
					if ($accountId) {
						$where .= " AND `account` = '{$accountId}'";
					}
					if ($year != '_all') {
						$where .= " AND YEAR(`date`) = '{$year}'";
					}
					$rows = db_select_distinct('ko_donations', 'person', "", $where);
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
				//Apply filter for a given year of donations made
				else if($db_table == 'ko_crm_contacts') {
					if ($db_col == 'project_id') {
						$projectId = $f[1][1];
						$statusId = $f[1][2];
						$projectId = (!$projectId || $projectId == '_all' ? null : $projectId);
						$statusId = (!$statusId || $statusId == '_all' ? null: $statusId);
						$ids = ko_get_leute_from_crm_project($projectId, $statusId);
						foreach($ids as $key => $value) if(!$value) unset($ids[$key]);
					}
					else if ($db_col == 'id') {
						$contactId = $f[1][1];
						$contactId = (!$contactId || $contactId == '_all' ? null : $contactId);
						$ids = array();
						if ($contactId) {
							$ids = db_select_distinct('ko_crm_mapping', 'leute_id', '', "WHERE `contact_id` = '" . $contactId . "'");
						}
					}
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
		$link_adv = str_replace(array(getLL('filter_OR'), getLL('filter_AND')), array('OR', 'AND'), strtoupper($link_adv));

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
		$result = mysqli_query(db_get_link(), 'SELECT `id` FROM `ko_leute` WHERE '.$where_code);
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
						apply_leute_filter($laf[$admin_filter_level]["filter"], $where, FALSE, '', $login_id, $includeAll, $includeHidden);
						if(trim(substr($where, 4)) != "") $admin_code .= "( ".substr($where, 4)." ) OR ";
					}
				} else {
					//apply all levels for read access
					for($i=1; $i<4; $i++) {
						if(!isset($laf[$i]["filter"])) continue;
						apply_leute_filter($laf[$i]["filter"], $where, FALSE, '', $login_id, $includeAll, $includeHidden);
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
	if(!$hidden_is_set && !$includeHidden) $deleted .= ko_get_leute_hidden_sql();

	if($where_code) {
		$where_code = " AND ( ".substr($where_code, 5)." ) ".$deleted;
		return TRUE;
	} else {
		$where_code = $deleted;
		return FALSE;
	}
}//apply_leute_filter()


/**
 * Check if user is allowed to export leute including information lock entries
 *
 * @return array list of leute_ids to filter out of collection
 */
function ko_apply_leute_information_lock() {
	global $access;

	if(!ko_get_setting("leute_information_lock")) return [];

	if(!isset($access['leute'])) ko_get_access("leute");

	if (!empty($access['leute']['ALLOW_BYPASS_INFORMATION_LOCK'])) {
		if (!ko_get_userpref($_SESSION['ses_userid'], "leute_apply_informationlock")) {
			return [];
		}
	}

	$where = "WHERE information_lock = 1";
	$restricted_leute_ids = array_column(db_select_data("ko_leute", $where, "id", "", "", FALSE, TRUE),"id");
	return $restricted_leute_ids;
}



function ko_get_leute_hidden_sql($_userid=0) {
	$sql = "";

	if(intval($_userid) > 0) $userid = intval($_userid);
	else $userid = $_SESSION['ses_userid'];

	if(ko_get_userpref($userid, "leute_show_hidden") == 0) {
		$sql = " AND hidden = '0' ";
	}

	return $sql;
}//get_leute_hidden_sql()



function ko_fastfilter_guess_dbcol($ff) {
	if ($ff['dbcol']) return $ff['dbcol'];

	$checkVals = array($ff['name']);
	if ($ff['var1']) $checkVals[] = $ff['var1'];
	if ($ff['sql1']) $checkVals[] = $ff['sql1'];

	$dbCols_ = db_get_columns('ko_leute', '');
	$dbCols = array();
	foreach ($dbCols_ as $c) {
		$dbCols[] = $c['Field'];
	}
	foreach ($checkVals as $checkVal) {
		$v = strtolower(str_replace('`', ' ', $checkVal));
		$vs = explode(' ', $v);
		foreach ($vs as $v) {
			if (in_array($v, $dbCols)) return $v;
		}
	}

	return FALSE;
}



/**
  * Liefert Formular zu einzelnem Filter
	* Für Ajax und Submenu.inc
	*/
function ko_get_leute_filter_form($fid, $showButtons=TRUE) {
	$code = "";

	ko_get_filter_by_id($fid, $f);

	$code_filter = array();
	for($i = 1; $i <= $f["numvars"]; $i++) {
		if (!$f["code$i"]) continue;
		if ($f['sql1'] != 'kota_filter') $code_filter[]  = getLL("filter_".$f["var$i"]) ? getLL("filter_".$f["var$i"]) : $f["var$i"];
		$code_filter[] .= $f["code$i"];
	}

	foreach($code_filter as $c) {
		$code .= $c."<br />";
	}


	$code_filter_zusatz = "";
	if ($f['sql1'] != 'kota_filter') {
		if($f["allow_neg"]) {
			$code_filter_zusatz .= '<div class="checkbox"><label><input type="checkbox" name="filter_negativ" id="filter_neg">'.getLL('filter_negativ').'</label></div>';
		} else {
			$code_filter_zusatz .= '<input type="hidden" name="filter_negativ" id="filter_neg" value="0" />';
		}
	}

	if($showButtons) {
		$code_filter_zusatz .= '<p align="center">';
		$code_filter_zusatz .= '<button type="button" class="btn btn-sm btn-primary" value="'.getLL("filter_add").'" name="submit_filter" onclick="javascript:do_submit_filter(\'leutefilter\', \''.session_id().'\');">' . getLL("filter_add") . '</button>';
		$code_filter_zusatz .= '&nbsp;&nbsp;';
		$code_filter_zusatz .= '<button type="button" class="btn btn-sm btn-success" value="'.getLL("filter_replace").'" name="submit_filter_new" onclick="javascript:do_submit_filter(\'leutefilternew\', \''.session_id().'\');">' . getLL("filter_replace") . '</button>';
		$code_filter_zusatz .= '</p>';
	}


	$code .= $code_filter_zusatz;
	return ('<div name="filter_form" class="filter-form">'.$code.'</div>');
}//ko_get_leute_filter_form()





/**
	* Gibt formatierte Personendaten zurück
	* data enthält den Wert aus der DB
	* col ist die DB-Spalte
	* p ist eine Referenz auf den ganzen Personendatensatz
	* all_datafields ist ein Array aller Datenfelder
	* forceDatafields: Damit werden die Datenfelder miteinbezogen, auch wenn userpref nicht gesetzt ist. (Z.B. von TYPO3-Ext kool_leute)
	* options: array with options
	*/
function map_leute_daten($data, $col, &$p = null, &$all_datafields = null, $forceDatafields=FALSE, $_options = array()) {
	global $DATETIME, $KOTA;
	global $all_groups;
	global $access, $ko_path, $BASE_PATH;
	global $LEUTE_EMAIL_FIELDS, $LEUTE_MOBILE_FIELDS;
	global $PLUGINS;

	if(!is_array($all_groups)) ko_get_groups($all_groups);

	//Datenbank-Spalten-Info holen, falls es keine Modul-Spalte ist (die nicht direkt als Leute-Spalte gespeichert ist)
	if(substr($col, 0, 6) != "MODULE") {
		if(!$data && substr($col, 0, 1) != '_' && !$KOTA['ko_leute'][$col]['processIfEmpty']) return '';
		$db_col = db_get_columns("ko_leute", $col);
	}

	//Call KOTA list function if set
	if (isset($_options['kota_process_modes'])) $kotaProcessModes = $_options['kota_process_modes'];
	else $kotaProcessModes = 'list';
	foreach (explode(',', $kotaProcessModes) as $kotaProcessMode) {
		if (!$kotaProcessMode) continue;
		if(substr($KOTA['ko_leute'][$col][$kotaProcessMode], 0, 4) == 'FCN:') {
			$fcn = substr($KOTA['ko_leute'][$col][$kotaProcessMode], 4);
			if(function_exists($fcn) && $fcn != 'kota_map_leute_daten') {
				$kota_data = array('table' => 'ko_leute', 'col' => $col, 'id' => $p['id'], 'dataset' => $p);
				$fcn($data,$kota_data,$log,$orig_data,null,null);
				return $data;
			}
		}
	}


	if($col == "groups") {  //Used for group filters and the groups-column in the excel export (the HTML view is created in leute.inc)
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
		//if(!$p["famid"] || $p["famfunction"] != "child") return "";
		switch($col) {
			case "_father":
			case "_mother":
				if ($p[substr($col, 1)]) {
					$d = db_select_data("ko_leute", "WHERE `id` = '".$p[substr($col, 1)]."' AND `deleted` = '0'", "*", "", "", TRUE);
					return $d["vorname"]." ".$d["nachname"];
				}
				else return "";
			break;  //father
			default:
				$parentIds = array();
				if(in_array(substr($col, 0, 8), array("_father_", "_mother_"))) {
					$p_col = substr($col, 8);
					if ($p[substr($col, 1, 6)]) $parentIds[] = $p[substr($col, 1, 6)];
				} else {
					$p_col = substr($col, 1);
					if ($p['father']) $parentIds[] = $p['father'];
					if ($p['mother']) $parentIds[] = $p['mother'];
				}
				if (sizeof($parentIds) > 0) {
					$d = db_select_data("ko_leute", "WHERE `id` IN ('".implode("','", $parentIds)."') AND `$p_col` != '' AND `deleted` = '0'", "*", "ORDER BY `anrede` ASC");
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

					//Check access
          			if($access['groups']['ALL'] < 1 && $access['groups'][$_gid] < 1) continue;

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
						$value[] = $v ? $v : 'x';
					} else if(in_array($gid, ko_groups_decode($group, "mother_line"))) {  //Check for assignement to a subgroup
						$tooltip = ko_groups_decode($group, 'group_desc_full');
						$value[] = '<a href="#" onclick="'."sendReq('../leute/inc/ajax.php', 'action,id,state,sesid', 'itemlist,MODULEgrp".$_gid.",switch,".session_id()."', do_element);return false;".'" title="'.ko_html($tooltip).'">&rsaquo;&thinsp;'.ko_html(ko_groups_decode($group, "group_desc"))."</a>";
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
			$url = $ko_path.'tracking/index.php?action=select_tracking&id='.$tracking['id'].'#tp'.$p['id'];
			$value = '<a href="'.$url.'" title="'.getLL('leute_show_tracking_for_person').'">'.$value.'</a>';
			return $value;
		}
		else if(substr($col, 0, 12) == 'MODULEparent') {
			$field = substr($col, 13);
			if(!empty($p['father'])) {
				ko_get_person_by_id($p['father'], $father);
			}

			if(!empty($p['mother'])) {
				ko_get_person_by_id($p['mother'], $mother);
			}

			if($father[$field] == $mother[$field]) return $father[$field];

			$values = [];
			if(!empty($father[$field]) && $father[$field] != "0000-00-00") {
				kota_process_data("ko_leute", $father, "list");
				$values[] = $father[$field] . " (V)";
			}
			if(!empty($mother[$field]) && $mother[$field] != "0000-00-00") {
				kota_process_data("ko_leute", $mother, "list");
				$values[] = $mother[$field] . " (M)";
			}
			if(in_array("xls", explode(",",$_options['kota_process_modes']))) return implode(", ", $values);
			return implode("<br /> ", $values);
		}
		else if(substr($col, 6, 6) == 'plugin') {  //Column added by a plugin
			$fcn = 'my_leute_column_map_'.substr($col, 12);
			if(function_exists($fcn)) {
				eval("\$value = $fcn(\$data, \$col, \$p);");
				return $value;
			}
		}
		// Household
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
						kota_listview_people($value, $kotadata, $log, $orig_data, TRUE);
						return $value;
					} else return '';
				} else return '';
			} else return '';
		}
		// Family
		else if(substr($col, 6, 6) == 'family') {
			switch (substr($col, 13)) {
				case 'childrencount':
					$pid = $p['id'];
					$children1 = db_select_data('ko_leute', "WHERE (`father` = '{$pid}' OR `mother` = '{$pid}') AND `deleted` = '0'", "*");
					$children2 = array();
					if ($p['famid'] && in_array($p['famfunction'], array('husband', 'wife'))) {
						$children2 = db_select_data('ko_leute', "WHERE `famid` = '{$p['famid']}' AND `famfunction` = 'child' AND `deleted` = '0'");
					}

					// get surname of children
					$father = db_select_data('ko_leute', "WHERE `famid` = '{$p['famid']}' AND `famfunction` = 'father' AND `deleted` = '0'", "nachname", '', '', TRUE, TRUE);
					$mother = db_select_data('ko_leute', "WHERE `famid` = '{$p['famid']}' AND `famfunction` = 'mother' AND `deleted` = '0'", "nachname", '', '', TRUE, TRUE);
					if ($father['nachname'] && $father['nachname'] == $mother['nachname']) {
						$surname = $father['nachname'];
					} else if (!$father || !$mother) {
						if (!$father && $mother['nachname']) $surname = $mother['nachname'];
						else if (!$mother && $father['nachname']) $surname = $father['nachname'];
						else $surname = NULL;
					} else {
						$surname = NULL;
					}

					$list = array();
					$counter1 = 0;
					$counter2 = 0;
					$counter3 = 0;
					foreach ($children1 as $child) {
						if (!array_key_exists($child['id'], $children2)) $counter2++;
						$counter1++;
					}
					// Add children that are not related by blood
					foreach ($children2 as $child) {
						if (array_key_exists($child['id'], $children1)) continue;
						$counter3++;
					}

					$val = '';
					if ($counter1 > 0) $val = '<span>'.$counter1.'</span>';
					if ($counter2 > 0) $val .= ($val ? '&nbsp;' : '').'<span style="cursor:pointer;" title="'.getLL('leute_col_children_by_blood_not_in_household').'">('.$counter2.')</span>';
					if ($counter3 > 0) $val .= ($val ? '&nbsp;' : '').'<span style="cursor:pointer;" title="'.getLL('leute_col_children_not_by_blood_in_household').'"><i>'.$counter3.'</i></span>';

					return $val;
				case 'children':
					$pid = $p['id'];
					$children1 = db_select_data('ko_leute', "WHERE `father` = '{$pid}' OR `mother` = '{$pid}' AND `deleted` = '0'", "*");
					$children2 = array();
					if ($p['famid'] && in_array($p['famfunction'], array('husband', 'wife'))) {
						$children2 = db_select_data('ko_leute', "WHERE `famid` = '{$p['famid']}' AND `famfunction` = 'child' AND `deleted` = '0'");
					}

					// get surname of children
					$father = db_select_data('ko_leute', "WHERE `famid` = '{$p['famid']}' AND `famfunction` = 'father' AND `deleted` = '0'", "nachname", '', '', TRUE, TRUE);
					$mother = db_select_data('ko_leute', "WHERE `famid` = '{$p['famid']}' AND `famfunction` = 'mother' AND `deleted` = '0'", "nachname", '', '', TRUE, TRUE);
					if ($father['nachname'] && $father['nachname'] == $mother['nachname']) {
						$surname = $father['nachname'];
					} else if (!$father || !$mother) {
						if (!$father && $mother['nachname']) $surname = $mother['nachname'];
						else if (!$mother && $father['nachname']) $surname = $father['nachname'];
						else $surname = NULL;
					} else {
						$surname = NULL;
					}

					$list = array();
					foreach ($children1 as $child) {
						//Mark deleted/hidden persons but still show them
						$pre = $post = '';
						if ($child['deleted'] == 1) {
							$pre = '<span class="text-deleted" title="'.getLL('leute_labels_deleted').'">';
							$post = '</span>';
						} else if ($child['hidden'] == 1) {
							$pre = '<span class="text-hidden" title="'.getLL('leute_labels_hidden').'">';
							$post = '</span>';
						}

						if(trim($child['vorname']) == '' && trim($child['nachname']) == '') {
							$val = $child["firm"].($child['department'] ? ' ('.$child['department'].')' : '');
						} else {
							$val = $child["vorname"]." ".(!$surname || $child["nachname"] != $surname ? $child["nachname"] : '');
						}
						if (!array_key_exists($child['id'], $children2)) {
							$pre = $pre . '<a href="'.$ko_path.'leute/index.php?action=set_idfilter&id='.intval($child['id']).'" title="'.getLL('leute_col_child_by_blood_not_in_household').'">(';
							$post = ')</a>' . $post;
						}
						else {
							$pre = $pre . '<a href="'.$ko_path.'leute/index.php?action=set_idfilter&id='.intval($child['id']).'" title="'.getLL('leute_col_child_by_blood_in_household').'">';
							$post = '</a>' . $post;
						}
						$val = $pre.$val.$post;

						$key = $child['geburtsdatum'].$child['vorname'];
						$list[$key] = $val;
					}
					// Add children that are not related by blood
					foreach ($children2 as $child) {
						if (array_key_exists($child['id'], $children1)) continue;

						//Mark deleted/hidden persons but still show them
						$pre = $post = '';
						if ($child['deleted'] == 1) {
							$pre = '<span class="text-deleted" title="'.getLL('leute_labels_deleted').'">';
							$post = '</span>';
						} else if ($child['hidden'] == 1) {
							$pre = '<span class="text-hidden" title="'.getLL('leute_labels_hidden').'">';
							$post = '</span>';
						}
						if(trim($child['vorname']) == '' && trim($child['nachname']) == '') {
							$val = $child["firm"].($child['department'] ? ' ('.$child['department'].')' : '');
						} else {
							$val = $child["vorname"]." ".(!$surname || $child["nachname"] != $surname ? $child["nachname"] : '');
						}
						$pre = $pre . '<a href="'.$ko_path.'leute/index.php?action=set_idfilter&id='.intval($child['id']).'" title="'.getLL('leute_col_child_not_by_blood_in_household').'"><i>';
						$post = '</i></a>' . $post;
						$val = $pre.$val.$post;

						$key = $child['geburtsdatum'].$child['vorname'];
						$list[$key] = $val;
					}
					ksort($list);
					return implode("<br>", $list);
				break;
			}
		}
		//Refnumber for donations
		else if(substr($col, 6, 15) == 'refno_donations') {
			list($accountId, $crmProjectId) = explode(':', substr($col, 21));
			$account = db_select_data('ko_donations_accounts', "WHERE `id` = {$accountId}", '*', '', '', TRUE);
			if (!$account || $account['id'] != $accountId) return '';

			if (!isset($access['donations'])) ko_get_access('donations');
			if ($access['donations']['ALL'] < 1 && $access['donations'][$accountId] < 1) return '';


			if(ko_module_installed('crm') && $crmProjectId > 0) {
				if (!isset($access['crm'])) ko_get_access('crm');
				if ($access['donations']['ALL'] < 1 && $access['donations'][$accountId] < 1) return '';
				$doCrm = TRUE;
			} else {
				$doCrm = FALSE;
			}

			return ko_donations_get_refnumber($account['id'], $p['id'], $doCrm, $_options['crmContactId']);
		}
		//Donations
		else if (substr($col, 6, 9) == 'donations') {
			// check if only account is set
			if (substr($col, 15, 1) == 'a') {
				$account_id = format_userinput(substr($col, 16), 'uint');
				$year = '';
			} else {
				$year = format_userinput(substr($col, 15, 4), 'uint');
				$account_id = format_userinput(substr($col, 19), 'uint');
				if (!$year || $year < 1900 || $year > 3000) return '';
			}

			$datefield = ko_get_userpref($_SESSION['ses_userid'], 'donations_date_field');
			if (!$datefield) $datefield = 'date';
			$where = " WHERE `person` = '" . $p['id'] . "' AND `promise` = '0' ";
			if ($year) $where .= " AND YEAR(`$datefield`) = '$year'";
			if ($account_id > 0) $where .= " AND `account` = '$account_id'";

			$amount = db_select_data('ko_donations', $where, 'SUM(`amount`) AS total_amount', '', '', TRUE);
			if ($amount['total_amount'] > 0) {
				return number_format($amount['total_amount'], 2, '.', "'");
			} else {
				return '';
			}
		}
		//CRM
		else if(substr($col, 6, 3) == 'crm') {
			$pid = $p['id'];

			$q = "SELECT c1.`project_id`, GROUP_CONCAT(c1.`status_id` SEPARATOR ',') AS `status_ids` FROM ko_crm_mapping m1 JOIN ko_crm_contacts c1 ON m1.`contact_id` = c1.`id` WHERE m1.`leute_id` = '".$pid."' AND c1.`project_id` <> '' AND NOT EXISTS (SELECT * FROM ko_crm_mapping m2 JOIN ko_crm_contacts c2 ON m2.`contact_id` = c2.`id` WHERE m2.`leute_id` = '".$pid."' AND c2.`project_id` = c1.`project_id` AND c2.`date` > c1.`date`) GROUP BY c1.`project_id`;";
			$result = db_query($q);
			$output = '';
			ko_get_crm_projects($projects);
			ko_get_crm_status($status);
			foreach ($result as $row) {
				$statusOutput = '';
				foreach (explode(',', $row['status_ids']) as $statusId) {
					if (!$statusId) continue;
					$statusTitle = $status[$statusId]['title'];
					if (!$statusTitle) $statusTitle = '&lt;project_'.$statusId.'&gt;';
					$statusOutput .=  $statusTitle.", ";
				}
				if ($statusOutput) $statusOutput = substr($statusOutput, 0, -2);
				$projectId = $projects[$row['project_id']]['id'];
				$projectTitle = trim($projects[$row['project_id']]['number'].' '.$projects[$row['project_id']]['title']);
				if (!$projectTitle) $projectTitle = '&lt;project_'.$projectId.'&gt;';
				$output .= '<a href="javascript:change_crm_div(\''.$pid.'\', \'show\', function() {filter_crm_project(\'#leute-crm-entries-'.$pid.'\', \''.$projectId.'\')});">'.$projectTitle.':&nbsp;'.$statusOutput.'</a><br>';
			}
			if ($output) $output = substr($output, 0, -4);
			return $output;
		}
		// age
		else if (substr($col, 6, 4) == 'age_') {
			$mode = substr($col, 10);
			switch ($mode) {
				case 'ymd':
					//Check for death field
					$df = ko_get_setting('my_col_age_deathfield');
				  if($df && isset($p[$df]) && $p[$df] != '0000-00-00') {
						$age = ko_get_age($p['geburtsdatum'], $p[$df], 'ymd');
					} else {
						$age = ko_get_age($p['geburtsdatum'], date('Y-m-d'), 'ymd');
					}
					if (!$age) return '';
					else {
						list ($ageYears, $ageMonths, $ageDays) = $age;
						return "{$ageYears} / {$ageMonths} / {$ageDays}";
					}
				break;
			}
		}
		//subscription
		else if(substr($col,6,13) == 'subscription_') {
			require_once($BASE_PATH.'subscription/inc/subscription.inc');
			static $subscriptionForms = [];
			$formId = substr($col,19);
			if(!isset($subscriptionForms[$formId])) {
				$subscriptionForms[$formId] = db_select_data('ko_subscription_forms','WHERE id='.$formId,'*','','',true);
			}
			$url = ko_subscription_create_edit_link($p['id'],null,$subscriptionForms[$formId],false);
			return '<a href="'.$url.'" target="_blank">'.$subscriptionForms[$formId]['title'].'</a>';
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
	} else if($col == '[VAR1]' && (int)$data > 0) {  //ID of filterpreset
		$presetID = format_userinput($data, 'uint');
		if($presetID > 0) {
			$preset = db_select_data('ko_userprefs', "WHERE `id` = '$presetID'", '*', '', '', TRUE);
			return $preset['key'];
		} else {
			return $data;
		}
	}
	else {  //Den Rest wie gehabt ausgeben
		$ll_value = getLL('kota_ko_leute_'.$col.'_'.$data);
		return ($ll_value ? $ll_value : $data);
	}

}//map_leute_daten()




function ko_leute_get_salutation_for_fam(&$data) {
	$p = $data['p'];
	$orig_es = $data['_orig_es'];
	$xls_cols = $data['_xls_cols'];

	if(in_array('MODULEsalutation_informal', $xls_cols)) {
		$r = array();
		//Use first names of all members found in the current list
		if(ko_get_userpref($_SESSION['ses_userid'], 'leute_force_family_firstname') == 2) {
			$familyMembers = (array)db_select_data('ko_leute', "WHERE `famid` = '".$p['famid']."' AND `famfunction` IN ('husband', 'wife') AND `deleted` = '0'".ko_get_leute_hidden_sql(), 'id,famfunction,vorname,anrede,geschlecht', 'ORDER BY famfunction ASC');
			$familyMembers = array_merge($familyMembers, (array)db_select_data('ko_leute', "WHERE `famid` = '".$p['famid']."' AND `famfunction` IN ('child', '') AND `deleted` = '0'".ko_get_leute_hidden_sql(), 'id,famfunction,vorname,anrede,geschlecht', 'ORDER BY famfunction DESC, geburtsdatum DESC'));
			$first = TRUE;
			foreach($familyMembers as $oneMember) {
				if(in_array($oneMember['id'], array_keys($orig_es))) {
					kota_listview_salutation_informal($r[], array('dataset' => $oneMember), $first);
					$first = FALSE;
				}
			}
		} else {
			//use first names of parents for firstname-col
			$parents = db_select_data("ko_leute", "WHERE `famid` = '".$p["famid"]."' AND `famfunction` IN ('husband', 'wife') AND `deleted` = '0'".ko_get_leute_hidden_sql(), "famfunction,vorname,anrede,geschlecht", "ORDER BY famfunction ASC");
			$first = TRUE;
			foreach($parents as $parent) {
				kota_listview_salutation_informal($r[], array('dataset' => $parent), $first);
				$first = FALSE;
			}
		}
		$buckets = array();
		foreach ($r as $k => $rr) {
			if ($buckets[strtolower($rr)]) unset($r[$k]);
			else $buckets[strtolower($rr)] = TRUE;
		}
		$r = implode(', ', $r);

		$p['MODULEsalutation_informal'] = $r;
		$data['cols_no_map'][] = 'MODULEsalutation_informal';
	}


	if(in_array('MODULEsalutation_formal', $xls_cols)) {
		$r = array();
		//Only use parents in this case (lastnames)
		$parents = db_select_data("ko_leute", "WHERE `famid` = '".$p["famid"]."' AND `famfunction` IN ('husband', 'wife') AND `deleted` = '0'".ko_get_leute_hidden_sql(), "famfunction,nachname,anrede,geschlecht", "ORDER BY famfunction ASC");
		$parent_values = array();
		$first = TRUE;
		foreach($parents as $parent) {
			kota_listview_salutation_formal($r[], array('dataset' => $parent), $first);
			$first = FALSE;
		}
		$buckets = array();
		foreach ($r as $k => $rr) {
			if ($buckets[strtolower($rr)]) unset($r[$k]);
			else $buckets[strtolower($rr)] = TRUE;
		}
		$r = implode(', ', $r);

		$p['MODULEsalutation_formal'] = $r;
		$data['cols_no_map'][] = 'MODULEsalutation_formal';
	}

	$data['p'] = $p;
}


/**
 * Create a greeting based on existing personal data
 *
 * @param $person
 * @return string
 */
function ko_get_salutation($person) {
	$gender = $person['geschlecht'];
	if(!$gender && $person['anrede'] == 'Herr') $gender = 'm';
	if(!$gender && $person['anrede'] == 'Frau') $gender = 'w';

	if(!$gender) {
		return getLL('leute_salutation_formal_');
	} else {
		$selector = $gender;

		if (in_array($person['zivilstand'], array('married', 'widowed'))) $selector .= '_married';
		else $selector .= '_unmarried';

		if($person['geburtsdatum'] != '0000-00-00' && ((int)substr($person['geburtsdatum'], 0, 4) + 18) > (int)date('Y')) {
			return  getLL('leute_salutation_informal_'.$selector).' '.$person['vorname'];
		} else {
			return getLL('leute_salutation_formal_'.$selector).' '.$person['nachname'];
		}
	}
}



/**
 * Get email address for a given person
 * Uses preferred email address as defined in ko_leute_preferred_fields.
 * If no preferred use the first one according to $LEUTE_EMAIL_FIELDS
 */
function ko_get_leute_email($p, &$email, $forceType='') {
	global $LEUTE_EMAIL_FIELDS;

	$email = array();

	if(!is_array($p)) {
		ko_get_person_by_id($p, $person);
		$p = $person;
	}

	if($forceType && in_array($forceType, $LEUTE_EMAIL_FIELDS) && check_email($p[$forceType])) {
		$email[] = $p[$forceType];
		return TRUE;
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
 * Get all email addresses for a given person
 * From address record (ko_leute) and login (ko_admin) if any login has this address assigned to it
 */
function ko_get_leute_emails($p) {
	global $LEUTE_EMAIL_FIELDS;

	$emails = array();

	if(!is_array($p)) {
		ko_get_person_by_id($p, $person);
		$p = $person;
	}

	//Get preferred email field from ko_leute_preferred_fields
	$email_fields = db_select_data('ko_leute_preferred_fields', "WHERE `type` = 'email' AND `lid` = '".$p['id']."'", '*');
	//Use preferred emails first
	foreach($email_fields as $row) {
		if(check_email($p[$row['field']])) $emails[] = $p[$row['field']];
	}

	//Add other email addresses (array_unique() later will deleted duplicates
	foreach($LEUTE_EMAIL_FIELDS as $field) {
		if(check_email($p[$field])) {
			$emails[] = trim($p[$field]);
		}
	}

	//Get admin email from login
	$login = db_select_data('ko_admin', "WHERE `leute_id` = '".$p['id']."' AND `email` != ''", 'id, email', '', '', TRUE);
	if(is_array($login) && check_email($login['email'])) {
		$emails[] = trim($login['email']);
	}

	$emails = array_unique($emails);
	return $emails;
}//ko_get_leute_emails()




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


// TODO: Not used?
function ko_create_leute_snapshot($ids, $forceNew=FALSE, $uid=NULL) {
	global $LEUTE_SNAPSHOTS_SAVED;

	if ($uid === NULL) $uid = $_SESSION['ses_userid'];

	if (!is_array($ids)) {
		$ids = array($ids);
	} else {
		$ids = array_unique($ids);
	}
	foreach ($ids as $id) {
		if (!$forceNew && in_array($id, $LEUTE_SNAPSHOTS_SAVED)) continue;

		ko_save_leute_changes($id, '', '', $uid);
		$LEUTE_SNAPSHOTS_SAVED[] = $id;
	}
}




/**
 * Store an old state of an edited person record in database for versioning
 * @params $id ID of person's dataset
 * @params $data array holding the current data for this record, which will be stored serialized
 " @params $df array holding all datafield data for this user. Will be fetched from db if empty
 * @params $uid int ID of kOOL login to assign this change to, usually ses_userid
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
	if(!$id || !$version) return FALSE;

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
function ko_get_datafield_data($gid, $fid, $pid, $version="", &$all_datafields=null, &$all_groups=null) {
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
	$fast_filter = explode(',', ko_get_userpref($_SESSION['ses_userid'], 'leute_fast_filter'));
	foreach($fast_filter as $k => $v) {
		if(!$v) unset($fast_filter[$k]);
	}

	if(sizeof($fast_filter) == 0) {
		$fast_filter = array();
		$lastNameFilter = db_select_data('ko_filter', "WHERE `name` = 'last name'", '*', '', '', TRUE, TRUE);
		if ($lastNameFilter) $fast_filter[] = $lastNameFilter['id'];
		$firstNameFilter = db_select_data('ko_filter', "WHERE `name` = 'first name'", '*', '', '', TRUE, TRUE);
		if ($firstNameFilter) $fast_filter[] = $firstNameFilter['id'];
		$firmFilter = db_select_data('ko_filter', "WHERE `name` = 'firm'", '*', '', '', TRUE, TRUE);
		if ($firmFilter) $fast_filter[] = $firmFilter['id'];
	}

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
	$kg_members = db_select_data('ko_leute', "WHERE `smallgroups` != '' AND `deleted` = '0'".ko_get_leute_hidden_sql(), 'id,smallgroups');
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
function ko_get_res_by_id($id, &$r, $table='ko_reservation') {
	$r = db_select_data($table, "WHERE `id` = '$id'");
}//ko_get_res_by_id()


/**
	* Liefert die Farbe eines Reservations-Items
	*/
function ko_get_resitem_farbe($id) {
	$row = db_select_data('ko_resitem', "WHERE `id` = '$id'", 'farbe', '', '', TRUE);
	return $row['farbe'];
}

/**
 * Apply res color for each reservation individually if $RES_COLOR is set.
 *
 * @param array &$_res Array with one reservation or more passed by reference.
 */
function ko_set_res_color(&$_ress) {
	global $RES_COLOR;

	if(!is_array($RES_COLOR) || sizeof($RES_COLOR) <= 0) return;

	if(isset($_ress['id'])) {
		$ress = array($_ress['id'] => $_ress);
		$single = TRUE;
	} else {
		$ress = $_ress;
		$single = FALSE;
	}
	foreach($ress as $k => $res) {
		$color = $RES_COLOR['map'][$res[$RES_COLOR['field']]];
		if($color) $ress[$k]['item_farbe'] = $color;
	}

	if($single) {
		$_ress = array_shift($ress);
	} else {
		$_ress = $ress;
	}
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
	$r = array();

	//Sortierung
	if($z_sort) {
    $sort = $z_sort;
  }
  else if($_SESSION["sort_item"] && $_SESSION["sort_item_order"]) {
    $add = '';
    $col = $_SESSION['sort_item'];
    if($col == 'item_id') $col = 'item_name';

    if($col == 'startdatum') $add = ',startzeit '.$_SESSION['sort_item_order'];
    else if($col == 'startzeit') $add = ',startdatum '.$_SESSION['sort_item_order'];
    else $add = ',startdatum '.$_SESSION['sort_item_order'].', startzeit '.$_SESSION['sort_item_order'];
    $sort = "ORDER BY ".$col." ".$_SESSION["sort_item_order"].$add;
  }
  else $sort = 'ORDER BY startdatum,startzeit,item_name ASC';


	//Reservationen oder Mod-Res
	if($type=="res") $db_table = "ko_reservation";
	else if($type=="mod") $db_table = "ko_reservation_mod";
	else return;

	//WHERE anwenden, oder falls leer, eine FALSE-Bedingung einfügen
	if($z_where != "") $z_where = "WHERE 1=1 " . $z_where;
	else $z_where = "WHERE 1=2";  //Nichts anzeigen

	$columns = $db_table . ".*,ko_resitem.name AS item_name,ko_resitem.farbe AS item_farbe,ko_resitem.gruppen_id AS gruppen_id";
	$table = $db_table ." LEFT JOIN ko_resitem ON " . $db_table .".item_id = ko_resitem.id";

	$resitems = db_select_data($table, $z_where, $columns, $sort, $z_limit);
	foreach($resitems AS $resitem) {
		$r[$resitem["id"]] = $resitem;
	}

	//Add color dynamically
	ko_set_res_color($r);

}//ko_get_reservationen()


/**
 * Get an Array of prov. reservations from ko_event_mod table
 *
 * @param string $start
 * @param string $stop
 * @return array
 */
function ko_get_reservations_from_events_mod($start, $stop) {
	$events = [];
	$where = "AND ko_event.startdatum >= '" . $start . "' AND ko_event.enddatum <= '".$stop."' AND ko_event.resitems != ''";
	ko_get_events_mod($events, $where);

	ko_get_resitems($all_resitems);

	$reservations = [];
	foreach($events AS $event) {

		$reservation = [
			'id' => 'prov' . $event['id'],
			'startdatum' => $event['res_startdatum'],
			'enddatum' => $event['res_enddatum'],
			'startzeit' => $event['res_startzeit'],
			'endzeit' => $event['res_endzeit'],
			'zweck' => $event['title'],
			'cdate' => $event['_crdate'],
			'last_change' => $event['last_change'],
			'lastchange_user' => $event['lastchange_user'],
			'user_id' => $event['responsible_for_res'],
			'serie_id' => '0',
			'linked_items' => '',
		];

		$resitems = explode(",", $event['resitems']);

		foreach($resitems AS $resitem_id) {
			$reservation['item_id'] = $resitem_id;
			$reservation['item_name'] = $all_resitems[$resitem_id]['name'];
			$reservation['item_farbe'] = $all_resitems[$resitem_id]['farbe'];
			$reservation['gruppen_id'] = $all_resitems[$resitem_id]['gruppen_id'];
			$reservation['prov_event'] = TRUE;

			$reservations["prov".$event['id']."_".$resitem_id] = $reservation;
		}

	}
	return $reservations;
}

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
	$order = ($_SESSION["sort_group"]) ? (" ORDER BY ".($_SESSION["sort_group"] == "gruppen_id" ? "gruppen_name" : $_SESSION["sort_group"])." ".$_SESSION["sort_group_order"]) : " ORDER BY name ASC ";

	$table = "ko_resitem LEFT JOIN ko_resgruppen ON ko_resitem.gruppen_id = ko_resgruppen.id ";
	$columns = "ko_resitem.*,ko_resgruppen.name AS gruppen_name";
	$resitems = db_select_data($table, $z_where, $columns, $order, $z_limit);

	foreach($resitems AS $resitem) {
		$r[$resitem['id']] = $resitem;
	}
}//ko_get_resitems()

/** Return all items linked with items passed by argument
 *
 * @param array $use_items
 * @return array itemlist
 */
function ko_get_resitems_with_linked_items($use_items) {
	global $access;

	$show_items = array();

	ko_get_resitems($resitems);
	foreach($resitems AS $resitem) {
		if (empty($resitem['linked_items'])) {
			continue;
		}

		$linked_items = explode(",", $resitem['linked_items']);
		foreach($linked_items AS $linked_item) {
			$linked_item_list[$linked_item][] = $resitem['id'];
		}
	}

	foreach($use_items as $item) {
		if($item && $access['reservation'][$item] > 0) {
			$show_items[] = $item;
			foreach($linked_item_list[$item] AS $linked_item) {
				if($access['reservation'][$linked_item] > 0) {
					$show_items[] = $linked_item;
				}
			}
		}
	}

	return array_unique($show_items);
}


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
 * Überprüft auf Doppelbelegungen
 *
 * @param int $item res id
 * @param string $datum1 sql_datum
 * @param string $datum2 sql_datum
 * @param string|null $zeit1 if time empty: all day long
 * @param string|null $zeit2 if time empty: all day long
 * @param null $error_txt will be voided before filled...
 * @param int   $id reservation id to be excluded from search
 * @param array $conflictingRes Array of Reservations from SQL
 * @return bool True if conflict exists
 */
function ko_res_check_double($item, $datum1, $datum2, $zeit1, $zeit2, &$error_txt, $id=0, &$conflictingRes=array()) {
	$datum1 = sql_datum($datum1);
	$datum2 = ($datum2) ? sql_datum($datum2) : $datum1;
	$zeit1  = sql_zeit($zeit1);
	$zeit2  = sql_zeit($zeit2);
	//Reservations without time last all day long, so set endtime to midnight
	if($zeit1 == "" && $zeit2 == "") $zeit2 = "23:59";

	//Mit id Selbsttest ausschliessen
	$where  = "WHERE `id`!='$id' ";

	//check for the right item and possibly linked items
	ko_get_resitem_by_id($item, $resitem_);
	$resitem = $resitem_[$item];
	if($resitem["linked_items"] != "") {  //item with linked items
		//check for all linked items in all linked items of the made reservations
		$or = array();
		foreach(array_merge(array($item), explode(",", $resitem["linked_items"])) as $linked_item) {
			$or[] = "`linked_items` REGEXP '(^|,)".$linked_item."($|,)'";
		}
		//and check for item and the linked items
		$check_itemids = $item.",".$resitem["linked_items"];
		$where .= " AND (`item_id` IN ($check_itemids) OR (".implode(" OR ", $or).")) ";
	}
	else {  //no linked items, so only check for item and linked items of made reservations
		$where .= "AND (`item_id`='$item' OR `linked_items` REGEXP '(^|,)$item($|,)') ";
	}

	//check for overlapping date and time
	$where .= " AND (
		( DATE_ADD(`startdatum`, INTERVAL `startzeit` HOUR_SECOND) >= DATE_ADD('$datum1', INTERVAL '$zeit1' HOUR_MINUTE)
			AND DATE_ADD(`startdatum`, INTERVAL `startzeit` HOUR_SECOND) < DATE_ADD('$datum2', INTERVAL '$zeit2' HOUR_MINUTE) )
		OR ( DATE_ADD(`enddatum`, INTERVAL IF(`endzeit`='00:00:00','23:59:59',`endzeit`) HOUR_SECOND) > DATE_ADD('$datum1', INTERVAL '$zeit1' HOUR_MINUTE)
			AND DATE_ADD(`enddatum`, INTERVAL IF(`endzeit`='00:00:00','23:59:59',`endzeit`) HOUR_SECOND) <= DATE_ADD('$datum2', INTERVAL '$zeit2' HOUR_MINUTE) )
		OR ( DATE_ADD(`startdatum`, INTERVAL `startzeit` HOUR_SECOND) < DATE_ADD('$datum1', INTERVAL '$zeit1' HOUR_MINUTE)
				AND DATE_ADD(`enddatum`, INTERVAL IF(`endzeit`='00:00:00','23:59:59',`endzeit`) HOUR_SECOND) > DATE_ADD('$datum2', INTERVAL '$zeit2' HOUR_MINUTE) )
		)";

	$rows = db_select_data("ko_reservation", $where);
	$conflictingRes = $rows;

	$error_txt = '';
	foreach($rows as $row) {
		$error_txt .= $resitem['name'].' - ';
		$error_txt .= sql2datum($row['startdatum']) . ( ($row['startdatum']==$row['enddatum']) ? '' : ('-' . sql2datum($row['enddatum'])) );
		if($row['startzeit'] == '00:00:00' && $row['endzeit'] == '00:00:00') {
			$error_txt .= ' '.getLL('time_all_day');
		} else {
			$error_txt .= ' '.substr($row['startzeit'],0,-3).'-'.substr($row['endzeit'],0,-3);
		}


		$show_fields_to_guest = explode(",", ko_get_setting("res_show_fields_to_guest"));
		//Only show purpose (zweck) if setting allows it for guest user
		if(($_SESSION['ses_userid'] != ko_get_guest_id() || in_array("zweck", $show_fields_to_guest)) && trim($row['zweck']) != '') {
			$error_txt .= ' "'.(strlen($row['zweck']) > 30 ? substr($row['zweck'], 0, 30).'..' : $row['zweck']).'"';
		}
		//Only show details about person if setting allows it for guest-user
		if(($_SESSION['ses_userid'] != ko_get_guest_id() || in_array("name", $show_fields_to_guest)) && trim($row['name']) != '') {
			$error_txt .= ' '.getLL('by').' '.$row['name'];
		}
		if(($_SESSION['ses_userid'] != ko_get_guest_id() || in_array("email", $show_fields_to_guest)) && trim($row['email']) != '') {
			$error_txt .= ', '.$row['email'];
		}
		if(($_SESSION['ses_userid'] != ko_get_guest_id() || in_array("telefon", $show_fields_to_guest)) && trim($row['telefon']) != '') {
			$error_txt .= ', '.$row['telefon'];
		}

	}
	return ($error_txt) ? FALSE : TRUE;
}//ko_res_check_double()

/**
 * Create a list of emails from $LEUTE_EMAIL_FIELDS
 * @return string SQL email columns
 */
function ko_get_sql_email_fields() {
	global $LEUTE_EMAIL_FIELDS;
	$email_fields = $where_email = '';
	foreach($LEUTE_EMAIL_FIELDS as $field) {
		$email_fields .= 'l.'.$field.' AS '.$field.', ';
		$where_email .= " l.$field != '' OR ";
	}
	return substr($email_fields, 0, -2);
}

/**
 * Find moderators depending on res_access_mode for item or group
 * @param Integer $item_id
 * @return Array of Moderators with contact email
 */
function ko_get_moderators_by_resitem($item_id) {
	// depending access_mode: get mods by item or group
	if (ko_get_setting("res_access_mode") == 1) {
		$email_fields = ko_get_sql_email_fields();
		$logins = db_select_data("ko_admin AS a LEFT JOIN ko_leute as l ON a.leute_id = l.id",
			"WHERE (a.disabled = '0' OR a.disabled = '')",
			"a.id AS id, $email_fields, a.email AS admin_email, l.id AS leute_id, l.vorname AS vorname, l.nachname AS nachname, a.login as login");

		foreach($logins as $login) {
			$all = ko_get_access_all('res_admin', $login['id'], $max);
			if($max < 4) continue;
			$user_access = ko_get_access('reservation', $login['id'], TRUE, TRUE, 'login', FALSE);
			if($user_access['reservation']['ALL'] < 5 && $user_access['reservation'][$item_id] < 5) continue;
			$mods[$login['id']] = $login;
		}

		ko_get_moderators_email($mods);
		return $mods;
	} else {
		//Find resgroup id
		$item = db_select_data("ko_resitem", "WHERE `id` = '$item_id'", "gruppen_id", "", "", TRUE);
		$gid = $item["gruppen_id"];
		return ko_get_moderators_by_resgroup($gid);
	}
}


/**
 * Find moderators for a given res group
 * @param Integer $gid
 * @return Array of Moderators with contact email
 */
function ko_get_moderators_by_resgroup($gid) {
	$email_fields = ko_get_sql_email_fields();

	//Get moderators for this resgroup
	$logins = db_select_data("ko_admin AS a LEFT JOIN ko_leute as l ON a.leute_id = l.id",
		"WHERE (a.disabled = '0' OR a.disabled = '')",
		"a.id AS id, $email_fields, a.email AS admin_email, l.id AS leute_id, l.vorname AS vorname, l.nachname AS nachname, a.login as login");
	foreach($logins as $login) {
		$all = ko_get_access_all('res_admin', $login['id'], $max);
		if($max < 4) continue;
		$user_access = ko_get_access('reservation', $login['id'], TRUE, TRUE, 'login', FALSE);
		if($user_access['reservation']['grp'.$gid] < 5) continue;
		$mods[$login['id']] = $login;
	}

	ko_get_moderators_email($mods);
	return $mods;
}

/**
 * Get contact email in a list of leute
 * @param Array $mods List of moderators
 * @return void
*/
function ko_get_moderators_email(&$mods) {
	$add_mods = array();

	foreach($mods as $i => $mod) {
		$resModEmail = ko_get_userpref($i, 'res_mod_email');

		//Use admin_email as set for the login in first priority
		if((!$resModEmail || $resModEmail == 'admin') && check_email($mod['admin_email'])) {
			$mods[$i]['email'] = $mod['admin_email'];
		} else if($resModEmail == 'none') {
			$mod['email'] = '';
			$mod['admin_email'] = '';
		} else {
			//Get all email addresses for this person
			ko_get_leute_email($mod['leute_id'], $email, $resModEmail);
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
}


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
	$v = db_select_data('ko_labels', 'WHERE 1=1', '*', 'ORDER BY `name` asc');
}//ko_get_etiketten_vorlagen()


/**
  * Liefert einzelne Etiketten-Vorlagen-Werte
	*/
function ko_get_etiketten_vorlage($id, &$v) {
	$v = db_select_data('ko_labels', "where `id` = " . $id, '*', '', '', TRUE, TRUE);
}//ko_get_etiketten_vorlagen()












/************************************************************************************************************************
 *                                                                                                                      *
 * R O T A                                                                                                              *
 *                                                                                                                      *
 ************************************************************************************************************************/

/**
 * @param _teams An array of team IDs that should be returned. If empty the teams currently set in the SESSION will be used
 * @param event_id An ID of a single event to be returned (may also be an array of event ids
 */
function ko_rota_get_events($_teams='', $event_id='', $include_weekteams=FALSE, $includeDisabled=FALSE) {
	global $access, $DATETIME, $ko_menu_akt, $ko_path, $BASE_PATH;

	$e = [];

	//Get all rota teams
	if(is_array($_teams)) {
		$teams = $_teams;
	} else {
		if($ko_menu_akt == 'rota') {
			if(!empty($_SESSION['rota_teams']) && !empty($_SESSION['rota_teams_readonly'])) {
				$teams = array_merge($_SESSION['rota_teams'], $_SESSION['rota_teams_readonly']);
			} else {
				$teams = $_SESSION['rota_teams'];
			}
		}
		else {
			$teams = array_keys(db_select_data('ko_rota_teams'));
		}
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

		$taxonomy_filter = $_SESSION['daten_taxonomy_filter'];
		$events_with_term = [];
		if(!empty($taxonomy_filter)) {
			foreach(explode(",",$taxonomy_filter) AS $term_id) {
				$events_with_term = array_merge(ko_taxonomy_get_nodes_by_termid($term_id, "ko_event"), $events_with_term);
			}

			$where .= "AND e.id IN (" . implode(",", array_column($events_with_term, 'id')) . ") ";
		}

		$room_filter = $_SESSION['daten_room_filter'];
		if(!empty($room_filter)) {
			$where .= "AND e.room IN (" . $room_filter . ") ";
		}

		// check, if the login has the 'force_global_filter' flag set to 1
		$forceGlobalTimeFilter = ko_get_force_global_time_filter('daten', $_SESSION['ses_userid']);

		//Apply global event filters if needed
		if(!is_array($access['daten'])) ko_get_access('daten');
		if($forceGlobalTimeFilter != 2 && ($forceGlobalTimeFilter == 1 || $access['daten']['MAX'] < 2)) {
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

	$query = "SELECT e.*,tg.name AS eventgruppen_name, tg.farbe AS eventgruppen_farbe, tg.shortname AS eventgruppen_shortname FROM `ko_event` AS e LEFT JOIN ko_eventgruppen AS tg ON e.eventgruppen_id = tg.id ".$where." ORDER BY startdatum ASC, startzeit ASC";
	$result = mysqli_query(db_get_link(), $query);
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
		$disabledTeamsWhere = '';
		if (!$includeDisabled) {
			$disabledTeams = array_unique(array_column(db_select_data('ko_rota_disable_scheduling', "WHERE `event_id` = '{$row['id']}'"), 'team_id'));
			if (sizeof($disabledTeams) > 0) $disabledTeamsWhere = " AND `team_id` NOT IN (" . implode(',', $disabledTeams) . ")";
		}

		$schedulling = db_select_data('ko_rota_schedulling', "WHERE `event_id` = '".$row['id']."'{$disabledTeamsWhere}", '*', '', '', FALSE, TRUE);
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
 * Merge both arrays of events and days. anyone scheduled on a day with a normal event,
 * the scheduled teams from the day will get merged into the $event['schedule']
 *
 * @param array $events
 * @param array $days
 * @return array
 */
function ko_rota_combine_events_with_days($events, $days) {
	$day_teams = db_select_data('ko_rota_teams', 'WHERE rotatype="day"');
	foreach($day_teams AS $key => $day_team) {
		$day_teams[$key]['days_range'] = explode(",", $day_team['days_range']);
	}

	$day_events = $return_events = [];
	list($start, $stop) = rota_timespan_startstop($_SESSION['rota_timestart'], $_SESSION['rota_timespan']);
	$current_date = DateTime::createFromFormat('Y-m-d', $start);
	$stop_date = DateTime::createFromFormat('Y-m-d', $stop);
	while($current_date < $stop_date) {
		$current_date_short = $current_date->format("Y-m-d");
		$day = $days[$current_date_short];

		if ($day['schedule']) {
			$day_events[$current_date_short] = [
				"startdatum" => $day['id'],
				"enddatum" => $day['id'],
				"eventgruppen_name" => '',
				"_time" => getLL('time_all_day'),
				"teams" => $day['teams'],
				"id" => $day['id'],
				"schedule" => $day['schedule'],
			];
		} else if(ko_get_userpref($_SESSION['ses_userid'],"rota_export_all_days")) {
			$daynumber = date("N", strtotime($current_date_short));
			$skip = TRUE;
			foreach($day['teams'] AS $day_team) {
				if(in_array($daynumber,$day_teams[$day_team]["days_range"])) {
					$skip = FALSE;
				}
			}

			if($skip == TRUE) {
				$current_date->modify('+1 day');
				continue;
			}

			$day_events[$current_date_short] = [
				"startdatum" => $current_date_short,
				"enddatum" => $current_date_short,
				"eventgruppen_name" => '',
				"_time" => getLL('time_all_day'),
				"teams" => $day['teams'],
				"id" => $current_date_short,
				"schedule" => [],
			];
		}
		$current_date->modify('+1 day');
	}

	foreach($events AS $e_id => $event) {
		if($day_events[$event['startdatum']]) {
			$event['schedule']+= $day_events[$event['startdatum']]['schedule'];
			$event['teams']+=$day_events[$event['startdatum']]['teams'];
			unset($day_events[$event['startdatum']]);
		}

		foreach($day_events AS $day_event) {
			if($day_event['startdatum'] <= $event['startdatum']) {
				$return_events[] = $day_event;
				unset($day_events[$day_event['startdatum']]);
			}
		}
		$return_events[] = $event;
	}

	$return_events = array_merge($return_events, $day_events);

	return $return_events;
}




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
	$teams = db_select_data('ko_rota_teams', "WHERE FIND_IN_SET('".$eg."',`eg_id`) AND `rotatype` = 'event'", '*', $order);

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
 * Get all rota teams that are schedulled on daily basis (Amtstage)
 * @return array list of teams
 */
function ko_rota_get_teams_day() {
	global $access;

	if(isset($GLOBALS['kOOL']['rota_teams_days'])) return $GLOBALS['kOOL']['rota_teams_days'];

	if($_SESSION['sort_rota_teams']) {
		$order = 'ORDER BY ' . $_SESSION['sort_rota_teams'].' '.$_SESSION['sort_rota_teams_order'];
	} else {
		$order = 'ORDER BY ' . (ko_get_setting('rota_manual_ordering') ? 'sort' : 'name').' ASC';
	}

	$teams = db_select_data('ko_rota_teams', "WHERE `rotatype` = 'day'", '*', $order);

	if($access['rota']['ALL'] < 1) {
		foreach($teams as $ti => $t) {
			if($access['rota'][$ti] < 1) unset($teams[$ti]);
		}
	}

	$GLOBALS['kOOL']['rota_teams_days'] = $teams;

	return $teams;
}



/**
 * retrieves a row from ko_rota_schedulling and respects whether the supplied team's type is 'week' or 'event'
 *
 * @param $event int|string the id or the array of the event
 * @param $team int|string the id or the array of the team
 * @return array|null the retrieved schedule entry form the database
 */
function ko_rota_get_schedule_by_event_team($event, $team) {
	if (!is_array($event)) ko_get_event_by_id($event, $event);
	if (!is_array($team)) $team = db_select_data('ko_rota_teams', "WHERE `id` = '{$team}'", '*', '', '', TRUE);
	if (!$team || !$event) return array();

	if ($team['rotatype'] == 'day') {
		$eventId = $event['startdatum'];
	} else {
		$eventId = $event['id'];
	}

	if (ko_rota_is_scheduling_disabled($eventId, $team['id'])) return array();
	else return db_select_data('ko_rota_schedulling', "WHERE `event_id` IN ('{$eventId}') AND `team_id` = '{$team['id']}'", '*', '', '', TRUE, TRUE);
}


/**
 * Get a array of days with schedulled information of teams
 *
 * @param array  $rota_teams list with full team data
 * @param string $start date (for event_id)
 * @param string $stop date (for event_id)
 * @param bool   $includeDisabled
 *
 * @return array
 * @throws Exception
 */
function ko_rota_get_days($rota_teams=null, $start = '', $stop = '', $includeDisabled=FALSE) {
	global $access, $BASE_PATH, $DATETIME;

	if($rota_teams == NULL && sizeof($_SESSION['rota_teams']) == 0) {
		$user_teams = ko_get_userpref($_SESSION['ses_userid'], 'rota_teams');
		if($user_teams) {
			$_SESSION['rota_teams'] = explode(',', $user_teams);
		} else {
			return [];
		}
	}

	if($_SESSION['sort_rota_teams']) {
		$order = 'ORDER BY '.$_SESSION['sort_rota_teams'].' '.$_SESSION['sort_rota_teams_order'];
	} else {
		$order = 'ORDER BY '.(ko_get_setting('rota_manual_ordering') ? 'sort' : 'name').' ASC';
	}

	if(!is_array($rota_teams)) {
		if(!empty($_SESSION['rota_teams']) && !empty($_SESSION['rota_teams_readonly'])) {
			$session_teams = array_merge($_SESSION['rota_teams'], $_SESSION['rota_teams_readonly']);
		} else {
			$session_teams = $_SESSION['rota_teams'];
		}

		$rota_teams = db_select_data('ko_rota_teams', "WHERE `id` IN (" . implode(',', $session_teams) . ")", '*', $order);
	}

	if(stristr($start, "-")) $start = strtotime($start);
	if(stristr($stop, "-")) $stop = strtotime($stop);

	if(empty($start)) {
		list($start, $stop) = rota_timespan_startstop($_SESSION['rota_timestart'], $_SESSION['rota_timespan']);

		if(ko_get_userpref($_SESSION['ses_userid'], 'rota_date_future') == 1) {
			$start = date("Y-m-d", time());
		}

		$start = strtotime(date_find_last_monday($start));
		$stop = date("Y-m-d", strtotime($stop) - 86400);
		$stop = strtotime(date_find_next_sunday($stop));
	}

	if(empty($stop)) {
		$stop = $start;
	}

	$allTeams = [];
	foreach($rota_teams as $team) {
		if($team['rotatype'] == 'day') {
			if($_SERVER['PHP_SELF'] == "/consensus/index.php" || $_SERVER['PHP_SELF'] == "/consensus/ajax.php" || $access['rota']['ALL'] > 0 || $access['rota'][$team['id']] > 0) {
				$allTeams[] = $team['id'];
			}
		}
	}

	require_once($BASE_PATH . "rota/inc/rota.inc");
	$days = [];

	$timezone = new DateTimeZone("Europe/Zurich");
	$start_date = new DateTime();
	$start_date->setTimezone($timezone);
	$start_date->setTimestamp($start);
	$stop_date = new DateTime();
	$stop_date->setTimezone($timezone);
	$stop_date->setTimestamp($stop);
	$current_date = $start_date;

	while($current_date <= $stop_date) {
		$eventId = $current_date->format("Y-m-d");

		$teams = [];
		foreach ($allTeams as $team) {
			if ($includeDisabled || !ko_rota_is_scheduling_disabled($eventId, $team)) {
				$teams[] = $team;
			}
		}

		if(empty($teams)) {
			$current_date->modify("+1 day");
			continue;
		}

		$days[$eventId] = [
			'id' => $eventId,
			'num' => $current_date->format("W"),
			'day' => $current_date->format("d"),
			'month' => $current_date->format("m"),
			'year' => $current_date->format("Y"),
			'_date' => strftime($DATETIME['dMY'], strtotime($current_date->format("Y-m-d"))),
			'teams' => $teams
		];

		$disableTeamsWhere = '';
		if (!$includeDisabled) {
			$disableTeams = array_unique(array_column(db_select_data('ko_rota_disable_scheduling', "WHERE `event_id` = '{$eventId}'"), 'team_id'));
			if (sizeof($disableTeams) > 0) $disableTeamsWhere = " AND `team_id` NOT IN (" . implode(',', $disableTeams) . ")";
		}

		//Get all schedulling information
		$schedulling = db_select_data('ko_rota_schedulling', "WHERE `event_id` = '".$eventId."'{$disableTeamsWhere}", '*', '', '', FALSE, TRUE);
		$schedule = array();
		foreach($schedulling as $s) {
			$schedule[$s['team_id']] = $s['schedule'];
		}
		$days[$eventId]['schedule'] = $schedule;
		$days[$eventId]['rotastatus'] = $schedulling[0]['status'] ? $schedulling[0]['status'] : 1;
		$current_date->modify("+1 day");
	}

	return $days;
}





/**
 * Get start and stop date for a given start date and timespan
 */
function rota_timespan_startstop($timestart, $timespan) {
	//Add time frame from setting / param
	switch(substr($timespan, -1)) {
		case 'd':
			$inc = substr($timespan, 0, -1);
			$start = $timestart;
			$stop  = add2date($timestart, 'day', $inc, TRUE);
		break;
		case 'w':
			$inc = substr($timespan, 0, -1);
			$start = date_find_last_monday($timestart);
			$stop  = add2date($start, 'week', $inc, TRUE);
		break;
		case 'm':
			$inc = substr($timespan, 0, -1);
			$start = substr($timestart, 0, -2).'01';
			$stop  = add2date($start, 'month', $inc, TRUE);
		break;
	}

	return array($start, $stop);
}//rota_timespan_startstop()




function ko_rota_get_schedulling_code_days($week) {
	global $BASE_PATH, $access, $smarty;

	if($_SESSION['sort_rota_teams']) {
		$order = 'ORDER BY '.$_SESSION['sort_rota_teams'].' '.$_SESSION['sort_rota_teams_order'];
	} else {
		$order = 'ORDER BY '.(ko_get_setting('rota_manual_ordering') ? 'sort' : 'name').' ASC';
	}

	if(!$GLOBALS['kOOL']['rota_teams_table']) {
		$GLOBALS['kOOL']['rota_teams_table'] = db_select_data('ko_rota_teams', 'WHERE 1=1', '*', $order);
	}

	$schedulled_codes = [];
	$all_teams = $GLOBALS['kOOL']['rota_teams_table'];

	$schedulling_teams = [];
	require_once($BASE_PATH . 'daten/inc/daten.inc');
	foreach($week['days'] AS $key => $day) {
		foreach($day['teams'] AS $team_id) {
			$members = ko_rota_get_team_members($team_id);
			$schedulling_teams[$team_id]['members'] = $members;
		}
	}

	foreach ($week['days'] AS $key => $day) {
		foreach ($schedulling_teams AS $team_id => $team) {
			foreach ($team['members']['people'] AS $member_id => $member) {

				$schedulling_teams[$team_id]['members']['people'][$member_id]['tooltips'][$key] = "<strong>" . $day['day'].".".$day['month'].".".$day['year']  . "</strong><br>";

				$absences = ko_daten_get_absence_by_leute_id($member_id, $day['id']);
				if(!empty($absences)) {
					$absence_text = [];
					foreach($absences AS $absence) {
						$absence_text[].= prettyDateRange($absence['from_date'], $absence['to_date']) . ": " .
							getLL("kota_ko_event_absence_type_" . $absence['type']) .
							(!empty($absence['description']) ? " (" . $absence['description'] . ")" : "");
					}

					if(!empty($absence_text)) {
						$schedulling_teams[$team_id]['members']['people'][$member_id]['tooltips'][$key] .= "<h4>".getLL("kota_ko_leute_absence")."</h4><p>" . implode("<br />", $absence_text) . "</p>";
						$schedulling_teams[$team_id]['members']['people'][$member_id]['absences'][$key] = TRUE;
					}
				}

				if($day['schedule'][$team_id] === null) continue;
				$schedulled_ids = explode(",", $day['schedule'][$team_id]);
				if(in_array($member_id, $schedulled_ids)) {
					$schedulling_teams[$team_id]['members']['people'][$member_id]['scheduled_days'][$key] = TRUE;
				}

				foreach($schedulled_ids AS $schedulled_id) {
					if(is_numeric($schedulled_id)) continue;

					$schedulling_teams[$team_id]['members']['freetext'][$schedulled_id]['tooltips'][$key] = "<strong>" . $day['day'].".".$day['month'].".".$day['year']  . "</strong><br>";					$schedulling_teams[$team_id]['members']['freetext'][$schedulled_id]['scheduled_days'][$key] = TRUE;
				}
			}
		}
	}

	foreach($schedulling_teams AS $team_id => $team) {
		$activated_days  = explode(",", $all_teams[$team_id]['days_range']);
		$schedulled_codes[$team_id] = "";

		foreach(["people", "freetext"] AS  $type) {
			foreach($team['members'][$type] AS $type_id => $member) {
				$scheduled_days = implode(",", array_keys($member['scheduled_days']));
				if ($scheduled_days === NULL) $scheduled_days = "";

				if(is_numeric($type_id)) {
					$input_id = "rota_entry_" . $week['id'] . "_" . $team_id . "_" . $type_id;
				} else {
					$input_id = "rota_entry_" . $week['id'] . "_" . $team_id . "_" . encodeFreeTextName($type_id);
				}

				if ($week['rotastatus'] == 2) {
					$status = "disabled";
				} else {
					if ($access['rota']['ALL'] < 3 && $access['rota'][$team_id] < 3) {
						$status = "disabled";
					} else {
						$status = "active";
					}
				}

				$input = [
					'type' => 'days_range',
					'name' => "rota_entry_daysrange",
					'html_id' => $input_id,
					'avalues' => $scheduled_days,
					'values' => [1, 2, 3, 4, 5, 6, 7],
					'css_highlight' => [],
					'descs' => getLL('kota_ko_rota_teams_days_range_values'),
					'activated_days' => $activated_days,
					'tooltips' => str_replace('"', '&quot;', $member['tooltips']),
					'absences' => $member['absences'],
					'status' => $status,
				];

				if(is_numeric($type_id)) {
					$where = "WHERE team_id = '" . $team_id . "' AND event_id LIKE '%-%' AND person_id = '" . $type_id . "'";
					$consensus = db_select_data("ko_rota_consensus", $where, "*, event_id AS id");
					if (!empty($consensus)) {
						foreach ($week['days'] AS $key => $day) {
							switch ($consensus[$day['id']]['answer']) {
								case 1:
									$input['css_highlight'][$key] = "consensus_no";
									break;
								case 2:
									$input['css_highlight'][$key] = "consensus_maybe";
									break;
								case 3:
									$input['css_highlight'][$key] = "consensus_yes";
									break;
								default:
									$input['css_highlight'][$key] = "consensus_no_answer";
									break;
							}
						}
					}

					$smarty->assign('input', $input);

					$getParticipationMode = ko_get_userpref($_SESSION['ses_userid'], 'rota_show_participation');
					$toolTipCode = "";
					if ($getParticipationMode != 'no') {
						$toolTipCode = 'data-tooltip-url="/rota/inc/ajax.php?action=minigraph&sesid=' . session_id() . '&person=' . $type_id . '&team=' . $team_id . '" data-tooltip-width="' . ($getParticipationMode == 'all' ? 438 : 219) . '" data-tooltip-height="180"';
					}

					$absences = ko_daten_get_absence_by_date($week['days'][1]['id'], $week['days'][7]['id'], $type_id);
					$absent_tooltip = "";
					if (!empty($absences)) {
						$absence_text = [];
						foreach ($absences AS $absence) {
							$absence_text[] .= prettyDateRange($absence['from_date'], $absence['to_date']) . ": " .
								getLL("kota_ko_event_absence_type_" . $absence['type']) .
								(!empty($absence['description']) ? " (" . $absence['description'] . ")" : "");
						}

						if (!empty($absence_text)) {
							$absent_tooltip = "<h4>" . getLL("kota_ko_leute_absence") . "</h4><p>" . implode("<br />", $absence_text) . "</p>";
						}
					}

					if ($getParticipationMode != 'no') {
						$toolTipCode .= ' data-tooltip-combine-text="true" data-tooltip-show-minigraph="true"';
					}
					$toolTipCode .= " data-tooltip-code=\"" . str_replace('"', '&quot;', $absent_tooltip) . "\"";

					$schedulled_codes[$team_id] .= "<tr>
					<td style='width: 100%;'>
					<span class=\"rota-tooltip\" " . $toolTipCode . " data-member=\"" . $member['id'] . "\">" . $member['vorname'] . " " . $member['nachname'] . "</span></td>
					<td>" . $smarty->fetch('ko_formular_elements.tmpl') . "</td>
					</tr>";
				} else {
					// freetext
					$smarty->assign('input', $input);

					$schedulled_codes[$team_id] .= "<tr>
					<td style='width: 100%;'>
					<span class=\"rota-tooltip\" data-member=\"" . $type_id . "\">\"" . $type_id . "\"</span></td>
					<td>" . $smarty->fetch('ko_formular_elements.tmpl') . "</td>
					</tr>";
				}
			}
		}

		$input_id_freetext = "rota_entry_" . $week['id'] . "_" . $team_id . "_freetexttemplate";

		$input = [
			'type' => 'days_range',
			'name' => "rota_entry_daysrange",
			'html_id' => $input_id_freetext,
			'avalues' => [],
			'values' => [1, 2, 3, 4, 5, 6, 7],
			'css_highlight' => [],
			'descs' => getLL('kota_ko_rota_teams_days_range_values'),
			'activated_days' => $activated_days,
			'tooltips' => "",
			'absences' => $member['absences'],
			'status' => $status,
		];
		$smarty->assign('input', $input);

		$schedulled_codes[$team_id] .= "<tr>
			<td style='width: 100%;'>
			<span class=\"rota-tooltip\" data-member=\"new_entry_todo\">
				<i class=\"fa fa-user-plus\" data-context='schedule' title=\"".getLL('rota_add_freetext_person')."\"></i>
			</span></td>
			<td>" . $smarty->fetch('ko_formular_elements.tmpl') . "</td>
			</tr>";
	}

	$code = '<table class="rota-schedule">';
	foreach ($week['days'][1]['teams'] as $tid) {
		if($access['rota']['ALL'] < 1 && $access['rota'][$tid] < 1) continue;

		$code .= '<tr>';
		$cls = 'bg-info';

		$code .= '<th style="width:15%;" class="'.$cls.'">'.$all_teams[$tid]['name']."</th>";
		$code .= '<td style="width:40%;"><table>'.$schedulled_codes[$tid] .'</table>';
		$code .= '<td style="width:40%;"></td></tr>';
	}
	$code .= "</td></tr></table>";

	return $code;
}

/**
 * @param array|int $event If it's an array, then it must be one event retrieved by ko_rota_get_events(). It may also be an ID of an event
 * @param string $mode May be event or day
 * @param array $_teams An array of teams used for ko_rota_get_events(). These teams can be schedulled.
 * @return string
 */
function ko_rota_get_schedulling_code($event, $mode = 'event', $_teams = []) {
	global $access, $BASE_PATH;

	if(!is_array($event)) {
		$event = ko_rota_get_events($_teams, $event, FALSE, TRUE);
	}

	if($_SESSION['sort_rota_teams']) {
		$order = 'ORDER BY '.$_SESSION['sort_rota_teams'].' '.$_SESSION['sort_rota_teams_order'];
	} else {
		$order = 'ORDER BY '.(ko_get_setting('rota_manual_ordering') ? 'sort' : 'name').' ASC';
	}

	if(!$GLOBALS['kOOL']['rota_teams_table']) {
		$GLOBALS['kOOL']['rota_teams_table'] = db_select_data('ko_rota_teams', 'WHERE 1=1', '*', $order);
	}

	$all_teams = $GLOBALS['kOOL']['rota_teams_table'];

	require_once($BASE_PATH.'daten/inc/daten.inc');
	$absences = ko_daten_get_absence_by_date($event['startdatum'], $event['stopdatum']);

	//Get all people scheduled in this event for double checks
	$currently_scheduled = [];
	$temp = ko_rota_get_recipients_by_event_by_teams($event['id']);
	foreach($temp as $tid => $t) {
		$t_keys = array_keys($t);
		$currently_scheduled[$tid] = array_combine($t_keys,$t_keys);
	}

	$leuteHiddenSQL = ko_get_leute_hidden_sql();
	$showConsensusCol = FALSE;
	foreach($event['teams'] as $team_id) {
		if ($access['rota']['ALL'] < 1 && $access['rota'][$team_id] < 1) continue;
		if ($event['rotastatus'] == 1 && ($access['rota']['ALL'] > 2 || $access['rota'][$team_id] > 2) && !ko_rota_is_scheduling_disabled($event['id'], $team_id)) {  //open and enough access
			$showConsensusCol = $showConsensusCol || ($all_teams[$team_id]['allow_consensus'] == 1);
		}
	}

	$consensus_comments = ko_consensus_get_comments_grouped_by_person();

	$c = '<table class="rota-schedule">';

	foreach($all_teams AS $team_id => $team) {
		if (
			in_array($team_id, $_SESSION['rota_teams_readonly']) &&
			$team['rotatype'] == "day" &&
			in_array($event['eventgruppen_id'], explode(",", $team['eg_id']))
		) {
			$cls = 'bg-info';
			$c .= '<tr><th style="width:15%;" class="' . $cls . '">' . $team['name'] . '</th>';
			$c .= '<td style="width:85%;" colspan="2">';

			$schedulled_persons = ko_rota_get_helpers_by_event_team($event['startdatum'], $team_id);
			$print_schedulled_persons = [];
			foreach ($schedulled_persons AS $schedulled_person) {
				if ($schedulled_person['is_free_text']) {
					$print_schedulled_persons[] = "\"" . trim($schedulled_person['name']) . "\"";
				} else {
					$print_schedulled_persons[] = trim($schedulled_person['vorname'] . " " . $schedulled_person['nachname']);
				}
			}

			$c .= implode(", ", $print_schedulled_persons);
			$c .= '</td></tr>';
		} else {

			if (!in_array($team_id, $event['teams'])) continue;

			if ($access['rota']['ALL'] < 1 && $access['rota'][$team_id] < 1) continue;

			$c .= '<tr>';
			$cls = 'bg-info';
			$disabledHtml = "";
			$isSchedulingDisabled = ko_rota_is_scheduling_disabled($event['id'], $team_id);
			if (!$isSchedulingDisabled) {
				if (empty($event['schedule'][$team_id])) {
					$disabledHtml = '<a class="text-hidden text-hover-danger" href="javascript:sendReq(\'../rota/inc/ajax.php\', \'action,eventid,teamid,status,sesid\', \'seteventteamstatus,' . $event['id'] . ',' . $team_id . ',1,\'+kOOL.sid, do_element);" title="' . getLL('rota_status_e_t_close') . '"><i class="fa fa-ban"></i></a>';
				}
			} else {
				$cls = 'bg-danger';
				$disabledHtml = '<a class="text-danger" href="javascript:sendReq(\'../rota/inc/ajax.php\', \'action,eventid,teamid,status,sesid\', \'seteventteamstatus,' . $event['id'] . ',' . $team_id . ',0,\'+kOOL.sid, do_element);" title="' . getLL('rota_status_e_t_open') . '"><i class="fa fa-ban"></i></a>';
			}

			if ($access['rota'][$team_id] <= 1) $disabledHtml = '';
			$c .= '<th style="width:15%;" class="' . $cls . '">' . $all_teams[$team_id]['name'] . "&nbsp;{$disabledHtml}</th>";

			if ($event['rotastatus'] == 1 && ($access['rota']['ALL'] > 2 || $access['rota'][$team_id] > 2) && !$isSchedulingDisabled) {  //open and enough access
				$consensusAllowed = $all_teams[$team_id]['allow_consensus'] == 1;

				//Prepare select with groups and people to choose from
				$members = ko_rota_get_team_members($all_teams[$team_id]);
				$o = '<option value=""></option>';
				$groupsFromConsensus = [];
				if (sizeof($members['groups']) > 0) {

					if (!isset($GLOBALS['KOOL']['persons_in_groups'])) {
						$persons_in_groups = db_select_data('ko_leute', "WHERE `groups` != '' AND `deleted` = '0'" . $leuteHiddenSQL);
						foreach ($persons_in_groups AS $person) {
							preg_match_all('/(g[0-9]{6})/', $person['groups'], $matches, PREG_SET_ORDER, 0);
							foreach ($matches AS $group) {
								$GLOBALS['KOOL']['persons_in_groups'][$group[0]][] = $person;
							}
						}
					}

					foreach ($members['groups'] as $group) {
						//Check for double
						$double = $title = $warntext = '';
						$group_members = $GLOBALS['KOOL']['persons_in_groups']['g' . $group['id']];

						foreach ($group_members as $person) {
							foreach ($all_teams as $_tid => $_team) {
								if ($_tid == $team_id) continue;
								if (isset($currently_scheduled[$_tid][$person['id']])) {
									$double = ' (!)';
									$title = 'title="' . sprintf(getLL('rota_schedule_warning_double_group'), ($person['vorname'] . ' ' . $person['nachname']), $_team['name']) . '"';
									$warntext = trim(sprintf(getLL('rota_schedule_warning_double_group'), ($person['vorname'] . ' ' . $person['nachname']), $_team['name']));
								}
							}
						}
						$bg = '';
						if ($consensusAllowed) {
							$groupAnswers = ko_consensus_get_answers('group', $event['id'], $team_id, $group['id']);
							$groupsFromConsensus[$group['id']] = ['id' => $group['id'], 'name' => '[' . $group['name'] . ']', 'double' => $double, 'warntext' => $warntext, 'answer' => $groupAnswers];
							$bg = 'background-size:100% 100%; background-repeat:none; background-position: top left; background-image:url(\'/rota/inc/consensus_chart.php?x=' . implode('x', $groupAnswers) . '\');';
						}
						$o .= '<option class="rota-consensus-group-bg hoverable" style="' . $bg . '" value="g' . $group['id'] . '" ' . $title . '>[' . $group['name'] . ']' . $double . '</option>';
					}
				}
				$personsFromConsensus = [];
				if (sizeof($members['people']) > 0) {
					foreach ($members['people'] as $person) {
						$double = $title = $warntext = '';
						$title_parts = [];
						foreach ($all_teams as $_tid => $_team) {
							if ($_tid == $team_id) continue;
							if (isset($currently_scheduled[$_tid][$person['id']])) {
								$double = ' (!)';
								$title_parts[] = sprintf(getLL('rota_schedule_warning_double'), $_team['name']);
								$warntext = trim(sprintf(getLL('rota_schedule_warning_double'), $_team['name']));
							}
						}
						$name = $person['vorname'] . ' ' . $person['nachname'];
						$bg = '';
						if ($consensusAllowed) {
							$bgColor = [0 => 'no-answer', 1 => 'no', 2 => 'maybe', 3 => 'yes'];
							$answer = ko_consensus_get_answers('person', $event['id'], $team_id, $person['id']);
							$personsFromConsensus[$person['id']] = [
								'id' => $person['id'],
								'name' => $name,
								'double' => $double,
								'warntext' => $warntext,
								'answer' => $answer
							];
							$bg = $bgColor[$answer];
						}

						$absence_id = array_search($person['id'], array_column($absences, "leute_id"));
						$icons = "  data-icon=\"glyphicon-empty\"";
						if ($absence_id !== FALSE) {
							$absent = getLL("kota_ko_event_absence_type_" . $absences[$absence_id]['type']) . ": " .
							prettyDateRange($absences[$absence_id]['from_date'], $absences[$absence_id]['to_date']) .
								(!empty($absences[$absence_id]['description']) ? " (" . $absences[$absence_id]['description'] . ")" : "");

							$bg .= " rota-consensus-bg__absent";
							$title_parts[] = $absent;
							$icons = "  data-icon=\"glyphicon-alert	\" ";
						}

						$title = 'title="' . implode("&#013;", $title_parts) . '"';
							$o .= '<option class="rota-consensus-bg hoverable ' . $bg . '" ' . $icons . ' value="' . $person['id'] . '" ' . $title . '>' . $name . $double . '</option>';
						unset($title);
						unset($title_parts);
					}
				}

				//Schedulled values
				$sel_o = [];
				$schedulled = ko_rota_schedulled_text($event['schedule'][$team_id], 'full', TRUE);
				$size = 0;
				foreach ($schedulled as $k => $v) {
					if (!$k) continue;

					$info_tooltip = "";
					$absence_id = array_search($k, array_column($absences, 'leute_id'));
					if ($absence_id !== FALSE) {
						$info_tooltip .= prettyDateRange($absences[$absence_id]['from_date'], $absences[$absence_id]['to_date']) . ": " .
							getLL("kota_ko_event_absence_type_" . $absences[$absence_id]['type']) .
							(!empty($absences[$absence_id]['description']) ? " (" . $absences[$absence_id]['description'] . ")" : "");
					}

					$sel_o[] = '<a class="btn btn-sm btn-primary rota-entry' .
						($absence_id ? ' consensus_absent"' : '"') . ($info_tooltip ? ' data-toggle="tooltip" data-html="true" title="' . $info_tooltip . '"' : '') . '
				id="rota_entry_' . $event['id'] . '_' . $team_id . '_' . $k . '">' . $v . '&nbsp;<i class="fa fa-remove"></i></a>';
					$size++;
				}

				if ($consensusAllowed) {
					// Color table for consensus
					$bgColor = [0 => 'no_answer', 1 => 'no', 2 => 'maybe', 3 => 'yes'];
					//Consensus Values of groups
					$consensus_o_g = [];
					$groupToolTipHtml = '<table><tr><td>' . getLL('yes') . '</td><td>%s</td></tr><tr><td>(' . getLL('yes') . ')</td><td>%s</td></tr><tr><td>' . getLL('no') . '</td><td>%s</td></tr></table><p>%s</p>';
					foreach ($groupsFromConsensus as $k => $v) {
						if ($k === '') continue;
						$toolTipCode = 'data-tooltip-code="' . sprintf($groupToolTipHtml, $v['answer'][3], $v['answer'][2], $v['answer'][1], $v['warntext']) . '"';
						$consensus_o_g[] = '<a class="btn btn-sm btn-default rota-consensus-entry rota-consensus-entry-group rota-tooltip" id="rota_consensus_entry_' . $event['id'] . '_' . $team_id . '_g' . $v['id'] . '" style="background-image:url(\'/rota/inc/consensus_chart.php?x=' . implode('x', $v['answer']) . '\');" ' . $toolTipCode . '>' . $v['name'] . $v['double'] . '</a>';
					}
					//Consensus Values of persons
					$consensus_o_p = [];
					$getParticipationMode = ko_get_userpref($_SESSION['ses_userid'], 'rota_show_participation');

					foreach ($personsFromConsensus as $k => $v) {
						if ($k === '') continue;
						$toolTipText = $toolTipCode = "";
						if ($getParticipationMode != 'no') {
							$toolTipCode = 'data-tooltip-url="/rota/inc/ajax.php?action=minigraph&sesid=' . session_id() . '&person=' . $v['id'] . '&team=' . $team_id . '" data-tooltip-width="' . ($getParticipationMode == 'all' ? 438 : 219) . '" data-tooltip-height="180"';
						}

						if(!empty($v['warntext'])) {
							$toolTipText.= "<p class='alert alert-danger'>" . $v['warntext'] ."</p>";
						}

						$absence_id = array_search($k, array_column($absences, 'leute_id'));
						if ($absence_id !== FALSE) {
							$toolTipText .= '<h4>' . getLL('kota_ko_leute_absence') . '</h4><p>' .
								prettyDateRange($absences[$absence_id]['from_date'], $absences[$absence_id]['to_date']) . ": " .
								getLL("kota_ko_event_absence_type_" . $absences[$absence_id]['type']) .
								(!empty($absences[$absence_id]['description']) ? " (" . htmlspecialchars($absences[$absence_id]['description'], ENT_QUOTES, 'iso-8859-1') . ")" : "") .
								'</p>';
						}

						if (!empty($consensus_comments[$v['id']][$team_id])) {
							$toolTipText .= "<h4>" . getLL("ko_consensus_comment") . "</h4><p>" . nl2br(htmlspecialchars($consensus_comments[$v['id']][$team_id]), ENT_QUOTES, 'iso-8859-1') . "</p>";
							$show_comment_icon = TRUE;
						} else {
							$show_comment_icon = FALSE;
						}

						if (!empty($toolTipText)) {
							$toolTipCode .= ' data-tooltip-code="' . $toolTipText . '"';
						}

						if ($getParticipationMode != 'no') {
							$toolTipCode .= ' data-tooltip-combine-text="true" data-tooltip-show-minigraph="true"';
						}

						$consensus_o_p[] = '<a class="btn btn-sm btn-default rota-consensus-entry rota-tooltip ' . $bgColor[$v['answer']] . ($absence_id !== FALSE ? " consensus_absent\"" : "\"") . ' id="rota_consensus_entry_' . $event['id'] . '_' . $team_id . '_' . $v['id'] . '" ' . $toolTipCode . '>' . $v['name'] . $v['double'] . ($show_comment_icon ? " <i class=\"fa fa-comment\"></i>" : "") . ($absence_id !== FALSE ? " <i class=\"fa fa-exclamation-triangle\"></i>" : "") . '</a>';
					}
				}

				$c .= '<td style="width:40%;"><div class="rota-entries rota-inner-table"><div class="table-row">';
				$c .= '<div class="table-cell first-cell"><select data-container="body" data-showTick="true" class="selectpicker rota-select" id="' . $event['id'] . '_' . $team_id . '">' . $o . '</select>';
				$c .= '<input class="input-sm form-control rota-text" type="text" style="min-width:180px;" id="rota_text_' . $event['id'] . '_' . $team_id . '" /></div>';

				// determine the number of elements which should be stacked in each cell (height in [elements])
				$elemPerCell = max(2, ceil(sizeof($sel_o) / 2));
				if ($consensusAllowed) {
					$elemPerCell2 = max(2, ceil((sizeof($consensus_o_g) + sizeof($consensus_o_p)) / 3));
					$elemPerCell = max($elemPerCell, $elemPerCell2);
				}


				$counter = 0;
				foreach ($sel_o as $entry) {
					if ($counter == 0) $c .= '<div class="table-cell" style="vertical-align:top;">';
					$c .= $entry;
					$counter++;
					if ($counter == $elemPerCell) {
						$counter = 0;
						$c .= '</div>';
					}
				}
				if ($counter > 0 && $counter < $elemPerCell) $c .= '</div>';
				$c .= '</div></div>';

				$c .= '</td>';

				if ($consensusAllowed) {

					$c .= '<td style="width:45%;" class="hidden-xs hidden-sm"><table class="rota-consensus-enries"><tr>';
					// Entries from Consensus groups
					$counter = 0;
					foreach ($consensus_o_g as $entry) {
						if ($counter == 0) $c .= '<td valign="top">';
						$c .= $entry;
						$counter++;
						if ($counter == $elemPerCell) {
							$counter = 0;
							$c .= '</td>';
						}
					}
					/*if($counter > 0 && $counter < $elemPerCell) $c .= '</td>';
					// Entries from Consensus persons
					$counter = 0;*/
					foreach ($consensus_o_p as $entry) {
						if ($counter == 0) $c .= '<td valign="top">';
						$c .= $entry;
						$counter++;
						if ($counter == $elemPerCell) {
							$counter = 0;
							$c .= '</td>';
						}
					}
					if ($counter > 0 && $counter < $elemPerCell) $c .= '</td>';
					$c .= '</tr></table>';

					$c .= '</td>';
				} else if ($showConsensusCol) {
					$c .= '<td style="width:45%;" class="hidden-xs hidden-sm"></td>';
				}
			} else {  // 2 = closed
				$c .= '<td style="width:80%;">';
				if (!$isSchedulingDisabled) {
					$c .= implode(ko_get_userpref($_SESSION['ses_userid'], 'rota_delimiter'), ko_rota_schedulled_text($event['schedule'][$team_id], 'full'));
				}
				$c .= '</td>';

				if ($showConsensusCol) {
					$c .= '<td style="width:45%;" class="hidden-xs hidden-sm"></td>';
				}
			}

			$c .= '</tr>';
		}
	}
	$c .= '</table>';

	return $c;
}//ko_rota_get_schedulling_code()



/**
 * Get the text to be displayed for a certain scheduling: Name of persons, Name of groups or free text
 *
 * @param string $schedule Comma separated list as found in db table ko_rota_schedulling
 * @param string $forceFormat overwrite setting rota_pdf_names
 * @param bool $allowHtml when displayed on website, use html formatting and set freetext in quotes
 * @return array Array of all entires which can be imploded for text rendering
 */
function ko_rota_schedulled_text($schedule, $forceFormat='', $allowHtml=FALSE) {
	$r = array();

	foreach(explode(',', $schedule) as $s) {
		if(!$s) continue;

		if(is_numeric($s)) {  //Person id
			$format = ko_get_userpref($_SESSION['ses_userid'], 'rota_pdf_names');
			if($forceFormat) $format = $forceFormat;

			if(!$GLOBALS['kOOL']['ko_leute_table']) {
				$GLOBALS['kOOL']['ko_leute_table'] = db_select_data('ko_leute', "WHERE 1=1", 'id,vorname,nachname,groups,deleted,hidden', '', '');
			}

			$p = $GLOBALS['kOOL']['ko_leute_table'][$s];
			$pre = $post = '';
			if ($allowHtml) {
				if ($p['deleted'] == 1) {
					$pre = '<span class="text-deleted" title="'.getLL('leute_labels_deleted').'">';
					$post = '</span>';
				} else if ($p['hidden'] == 1) {
					$pre = '<span class="text-hidden" title="'.getLL('leute_labels_hidden').'">';
					$post = '</span>';
				}
			} else {
				if ($p['deleted'] == 1) {
					$pre = '';
					$post = '('.getLL('rota_label_short_deleted_person').')';
				} else if ($p['hidden'] == 1) {
					$pre = '';
					$post = '('.getLL('rota_label_short_hidden_person').')';
				}
			}
			switch($format) {
				case 1:
					$r[$s] = $pre . $p['vorname'].' '.substr($p['nachname'],0,1).'.' . $post;
				break;
				case 2:
					$r[$s] = $pre . $p['vorname'].' '.substr($p['nachname'],0,2).'.' . $post;
				break;
				case 3:
					$r[$s] = $pre . substr($p['vorname'],0,1).'. '.$p['nachname'] . $post;
				break;
				case 4:
					$r[$s] = $pre . $p['vorname'].' '.$p['nachname'] . $post;
				break;
				case 5:
					$r[$s] = $pre . $p['vorname'] . $post;
				break;
				case 6:
					$r[$s] = $pre . substr($p['vorname'], 0, 2).substr($p['nachname'], 0, 2) . $post;
				break;
				case 7:
					$r[$s] = $pre . substr($p['vorname'], 0, 3).substr($p['nachname'], 0, 3) . $post;
				break;
				case 8:
					$r[$s] = $pre . substr($p['vorname'], 0, 2).'.'.substr($p['nachname'], 0, 3).'.' . $post;
				break;
				case 9:
					$r[$s] = $pre . substr($p['vorname'],0,2).'. '.$p['nachname'] . $post;
				break;
				default:
					$r[$s] = $pre . $p['vorname'].' '.$p['nachname'] . $post;
			}
		} else if(preg_match('/^g[0-9]{6}$/', $s)) {  //Group id
			$id = str_replace('g', '', $s);
			$group = db_select_data('ko_groups', "WHERE `id` = '$id'", '*', '', '', TRUE);
			$r[$s] = getLL('rota_prefix_group').$group['name'];
		} else {  //Text
			if($allowHtml) {
				$r[$s] = '"' . $s . '"';
			} else {
				$r[$s] = $s;
			}
		}
	}

	return $r;
}//ko_rota_schedulled_text()



/**
 * gets all helpers of a certain team at a certain event. Helpers are persons or whole group (see third parameter)
 *
 * @param $eventId
 * @param $teamId
 * @param $keepGroup: Set to true to have the group's name returned instead of the people
 * @return array
 */
function ko_rota_get_helpers_by_event_team($eventId, $teamId, $keepGroup=FALSE) {
	if (ko_rota_is_scheduling_disabled($eventId, $teamId)) return array();

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
		else if (preg_match('/g[0-9]{6}/', $helper)) {  //group id
			if($keepGroup) {
				$group = db_select_data('ko_groups', "WHERE `id` = '".substr($helper, 1)."'", '*', '', '', TRUE);
				$helpers[] = $group;
			} else {
				$pattern = $helper . '(:g[0-9]{6})*' . $roleString;
				$res = db_select_data('ko_leute', "where `groups` regexp '" . $pattern . "' AND `deleted` = '0'".ko_get_leute_hidden_sql());
				foreach ($res as $helper) {
					$helpers[] = $helper;
				}
			}
		} else {  //free text
			$helpers[] = array('name' => $helper, 'is_free_text' => TRUE);
		}
	}
	return $helpers;
} // ko_rota_get_helpers_by_event_team()


/**
 * Get all people scheduled in a given event. Also find group's members if a whole group is scheduled
 *
 * @param $event_ids array/int An array of event ids of a single event ID
 * @param $team_ids array An array of teams to include. Empty to include all teams
 * @param $access_level int Access level necessary to include this team
 *
 * @return array|null
 */
function ko_rota_get_recipients_by_event($event_ids, $team_ids='', $access_level=2) {
	global $access;

	if(!is_array($event_ids)) $event_ids = array($event_ids);
	if(sizeof($event_ids) == 0) return array();

	$z_where = '';
	if(is_array($team_ids) || $team_ids != '') {
		if(!is_array($team_ids)) $team_ids = array($team_ids);
		foreach($team_ids as $k => $v) {
			if(!$v) unset($team_ids[$k]);
		}
		if(sizeof($team_ids) > 0) {
			$z_where .= ' AND `team_id` IN ('.implode(',', $team_ids).') ';
		}
	}

	//Add restriction according to access level
	if($access['rota']['ALL'] < $access_level) {
		$a_teams = array();
		static $all_teams = null;
		if($all_teams === null) {
			$all_teams = db_select_data('ko_rota_teams');
		}
		foreach($all_teams as $tid => $team) {
			if($access['rota'][$tid] >= $access_level) $a_teams[] = $tid;
		}
		if(sizeof($a_teams) > 0) {
			$z_where .= ' AND `team_id` IN ('.implode(',', $a_teams).') ';
		} else {
			return [];
		}
	}




	// TODO: refactor to directly get daily-schedules

	//Add weeks
	$events = db_select_data('ko_event', "WHERE `id` IN (".implode(',', $event_ids).')','startdatum');
	foreach($events as $event) {
		$event_ids[] = $event['startdatum'];
	}

	$schedulling = db_select_data('ko_rota_schedulling', "WHERE `event_id` IN ('".implode("','", $event_ids)."')".$z_where, '*', '', '', FALSE, TRUE);
	$constraints = [];
	foreach($schedulling as $schedule) {
		if (ko_rota_is_scheduling_disabled($schedule['event_id'], $schedule['team_id'])) continue;

		foreach(explode(',', $schedule['schedule']) as $s) {
			$s = trim($s);
			if(is_numeric($s)) {  //Person id
				$constraints['ids'][] = $s;
			} else if(preg_match('/^g[0-9]{6}$/', $s)) {  //Group id
				$constraints['groups'][] = "MATCH(`groups`) AGAINST('".$s."' IN BOOLEAN MODE)";
			}
		}
	}

	if(empty($constraints)) {
		return [];
	}

	if($constraints['ids']) {
		$constraints['ids'] = "`id` IN(".implode(',',$constraints['ids']).")";
	}
	if($constraints['groups']) {
		$constraints['groups'] = implode(' OR ',$constraints['groups']);
	}
	$where = "WHERE (".implode(' OR ',$constraints).") AND `deleted` = 0 AND `hidden` = 0";

	return db_select_data('ko_leute',$where);
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
		static $all_teams = null;
		if($all_teams === null) {
			$all_teams = db_select_data('ko_rota_teams');
		}
		foreach($all_teams as $tid => $team) {
			if($access['rota'][$tid] >= $access_level) $a_teams[] = $tid;
		}
		if(sizeof($a_teams) > 0) {
			$z_where .= ' AND `team_id` IN ('.implode(',', $a_teams).') ';
		} else {
			return [];
		}
	}




	// TODO: refactor to get daily-schedules directly

	//Add weeks
	$events = db_select_data('ko_event', "WHERE `id` IN (".implode(',', $event_ids).')','startdatum');
	foreach($events as $event) {
		$event_ids[] = $event['startdatum'];
	}

	$schedulling = db_select_data('ko_rota_schedulling', "WHERE `event_id` IN ('".implode("','", $event_ids)."')".$z_where, 'event_id,team_id,schedule', '', '', FALSE, TRUE);

	$constraints = [];
	foreach($schedulling as $schedule) {
		if (ko_rota_is_scheduling_disabled($schedule['event_id'], $schedule['team_id'])) continue;

		foreach(explode(',', $schedule['schedule']) as $s) {
			$s = trim($s);
			if(is_numeric($s)) {  //Person id
				$constraints[$schedule['team_id']]['ids'][] = $s;
			} else if(preg_match('/^g[0-9]{6}$/', $s)) {  //Group id
				$constraints[$schedule['team_id']]['groups'][] = "MATCH(`groups`) AGAINST('".$s."' IN BOOLEAN MODE)";
			} else {  //Text
				//Don't include in recipients list
			}
		}
	}

	$people = [];

	foreach($constraints as $teamId => $teamConstraints) {
		if($teamConstraints['ids']) {
			$teamConstraints['ids'] = "`id` IN(".implode(',',$teamConstraints['ids']).")";
		}
		if($teamConstraints['groups']) {
			$teamConstraints['groups'] = implode(' OR ',$teamConstraints['groups']);
		}
		$where = "WHERE (".implode(' OR ',$teamConstraints).") AND `deleted` = 0 AND `hidden` = 0";

		$people[$teamId] = db_select_data('ko_leute',$where);
	}

	return $people;
}//ko_rota_get_recipients_by_event_by_teams()





/**
 * Returns team members/leaders for a rota team
 *
 * @param int/array $team teamID or team Array to get members for
 * @param boolean $resolve_groups Set to true to get group members as single people, otherwise just get whole groups, if null the setting on the ko_rota_teams row is respected
 * @param int $roleid Give a role ID to only get members/leaders according to this roleID (e.g. 0000XY)
 * @return Array with two keys: groups and people
 */
function ko_rota_get_team_members($team, $resolve_groups=null, $roleid='', $includeDeleted=FALSE) {
	//Return from cache
	$tid = is_array($team) ? $team['id'] : $team;
	if(is_array($team) && $resolve_groups === null) {
		$resolve_groups = $team['schedule_subgroup_members'];
	}

	if(($resolve_groups !== null) && !$roleid && isset($GLOBALS['kOOL']['rota_team_members'][$tid.'.'.$roleid.'.'.$resolve_groups])) {
		return $GLOBALS['kOOL']['rota_team_members'][$tid.'.'.$roleid.'.'.$resolve_groups];
	}

	if(!is_array($team)) $team = db_select_data('ko_rota_teams', "WHERE `id` = '$team'", '*', '', '', TRUE);
	if(!$team['group_id']) return array('people' => array(), 'groups' => array());

	if($resolve_groups === null) {
		$resolve_groups = $team['schedule_subgroup_members'];
		if(isset($GLOBALS['kOOL']['rota_team_members'][$tid.'.'.$roleid.'.'.$resolve_groups])) {
			return $GLOBALS['kOOL']['rota_team_members'][$tid.'.'.$roleid.'.'.$resolve_groups];
		}
	}

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

	$searchGids = [];
	$where = [];

	//Add sql for each given group/role
	foreach(explode(',', $team['group_id']) as $gid) {
		$rolepos = strpos($gid,':r');
		if($role || $rolepos) {
			if($role) {
				if($rolepos) {
					$gid = substr($gid,0,$rolepos);
				}
				$gid .= $role;
			}
			$where[] = "INSTR(`groups`,'".$gid."')";
		} else {
			$searchGids[] = $gid;
		}
	}

	if($searchGids) {
		$where[] = "MATCH(`groups`) AGAINST('".implode(' ',$searchGids)."' IN BOOLEAN MODE)";
	}

	$searchGids = [];

	//Add members from groups above
	if($resolve_groups) {
		foreach($r['groups'] as $group) {
			$searchGids[] = 'g'.$group['id'];
		}
		if($searchGids) {
			$w = implode(' ',$searchGids);
			if($role) {
				$w = '+('.$w.') +'.substr($role,1);
			}
		} else if($role) {
			$w = substr($role,1);
		} else {
			$w = '';
		}
		if($w) {
			$where[] = "MATCH(`groups`) AGAINST('".$w."' IN BOOLEAN MODE)";
		}
	}

	//Sorting
	$orderby = ko_get_userpref($_SESSION['ses_userid'], 'rota_orderby');
	if(!$orderby) $orderby = 'vorname';
	if($orderby == 'nachname') $orderby = 'nachname,vorname';
	else if($orderby == 'vorname') $orderby = 'vorname,nachname';

	$rows = db_select_data('ko_leute', "WHERE (".implode(' OR ',$where).")" . ($includeDeleted ? "" : " AND `deleted` = 0"), '*', 'ORDER BY '.$orderby.' ASC');

	$p = array();
	foreach($rows as $row) {
		$ok = false;
		foreach(explode(',', $team['group_id']) as $gid) {
			if($role && $rolepos = strpos($gid, ':r')) {
				$gid = substr($gid, 0, $rolepos);
			}
			$gidpos = 0;
			while(($gidpos = strpos($row['groups'],$gid,$gidpos)) !== false) {
				$gidpos += 7;
				if(substr($row['groups'],$gidpos,2) != ':g') {
					$ok = true;
					break 2;
				}
			}
		}
		if(!$ok && $resolve_groups) {
			foreach($r['groups'] as $group) {
				$gidpos = 0;
				while(($gidpos = strpos($row['groups'],'g'.$group['id'],$gidpos)) !== false) {
					$gidpos += 7;
					if($role) {
						if($rolepos = strpos($row['groups'],$role,$gidpos)) {
							$delimpos = strpos($row['groups'],',',$gidpos);
							if($delimpos === false || $delimpos > $rolepos) {
								$ok = true;
								break 2;
							}
						}
					} else {
						$ok = true;
						break 2;
					}
				}
			}
		}
		if($ok) {
			$p[$row['id']] = $row;
		}
	}

	$r['people'] = $p;

	//Store in cache
	$GLOBALS['kOOL']['rota_team_members'][$team['id'].'.'.$roleid.'.'.$resolve_groups] = $r;

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
function ko_rota_get_status($eventId) {
	$event = db_select_data("ko_rota_schedulling", "where `event_id` = " . $eventId, 'status', '', 'LIMIT 0, 1', TRUE, TRUE);
	$eventStatus = $event['status'];
	$eventStatus = $eventStatus == null ? 1 : $eventStatus;
	return $eventStatus;
} // ko_rota_get_status()




/**
 * @param $sorting
 * @param $zWhere
 */
function ko_rota_get_all_teams($zWhere = '') {
	$zWhere = 'where 1=1 ' . $zWhere;

	if(ko_get_setting('rota_manual_ordering')) {
		$orderBy = 'ORDER BY `sort` ASC';
	} else {
		$orderBy = 'ORDER BY `name` ASC';
	}
	$teams = db_select_data('ko_rota_teams', $zWhere, '*', $orderBy);
	return $teams;
}


/**
 * returns all events where $id was scheduled for during the supplied time frame
 *
 * @param Integer  $id the id of the person or group
 * @param DateTime $start the minimal ending time of the event, Y-m-d H:i:s
 * @param DateTime $stop the maximal starting time of the event
 * @param string   $mode either 'person' or later 'group'
 *
 * @return array|mixed|void
 */
function ko_rota_get_scheduled_events($id, $start = NULL, $stop = NULL, $mode = 'person') {
	if ($start === NULL) $start = date('Y-m-d H:i:s');
	if ($stop === NULL) $stop = '2500-12-31 23:59:59';
	if (array_key_exists('ko_scheduled_events', $GLOBALS['kOOL'])) {
		if (array_key_exists($id . $start . $stop . $mode, $GLOBALS['kOOL']['ko_scheduled_events'])) {
			return $GLOBALS['kOOL']['ko_scheduled_events'][$id . $start . $stop . $mode];
		}
	} else {
		$GLOBALS['kOOL']['ko_scheduled_events'] = [];
	}

	$role = ko_get_setting('rota_teamrole');
	$roleString = (trim($role) == '' ? '' : ':r' . $role);

	// get all non-leaf groups associated with a team
	if (array_key_exists('ko_non_leaf_team_groups', $GLOBALS['kOOL'])) {
		$nonLeafTeamGroups = $GLOBALS['kOOL']['ko_non_leaf_team_groups'];
	} else {
		$teams = db_select_data('ko_rota_teams', 'where 1=1', 'group_id');
		$nonLeafTeamGroups = [];
		$teamGroups = [];
		foreach ($teams as $team) {
			foreach (explode(',', $team['group_id']) as $teamGroup) {
				$teamGroup = trim($teamGroup);
				if ($teamGroup == '') continue;
				if (preg_match('/^g[0-9]{6}$/', $teamGroup)) {
					$teamGroups[] = substr($teamGroup, 1);
				} else if (preg_match('/^g[0-9]{6}:r[0-9]{6}$/', $teamGroup)) {
					$teamGroups[] = substr($teamGroup, 1, 6);
				}
			}
		}

		if (sizeof($teamGroups) != 0) {
			$res = db_query("select distinct `id` from `ko_groups` g1 where `id` in ('" . implode("','", $teamGroups) . "') and exists (select `id` from `ko_groups` g2 where g2.`pid` = g1.`id`) order by g1.`id` asc;");
			foreach ($res as $nonLeafTeamGroup) {
				$nonLeafTeamGroups[] = (int)$nonLeafTeamGroup["id"];
			}
		}
		$GLOBALS['kOOL']['ko_non_leaf_team_groups'] = $nonLeafTeamGroups;
	}

	if (substr($id, 0, 1) != 'g') {
		if (!$GLOBALS['kOOL']['ko_leute_table']) {
			$GLOBALS['kOOL']['ko_leute_table'] = db_select_data('ko_leute', "WHERE 1=1", 'id,vorname,nachname,groups,deleted,hidden', '', '');
		}

		$person = $GLOBALS['kOOL']['ko_leute_table'][$id];
		if (!$person) return;
		$groupsString = $person['groups'];
		if (trim($groupsString) == '') return;

		$unprocGroups = [];
		foreach (explode(',', $groupsString) as $group) {
			if (trim($group) == '') continue;
			if ($roleString != '') { // only consider group memberships with 'helper' role
				if (substr($group, -8, 8) != $roleString) continue;
				$group = substr($group, 0, strlen($group) - 8);
			} else {
				if (substr($group, -7, 1) == 'r') { // remove role so we won't search for it in the `schedule` column of ko_rota_schedulling
					$group = substr($group, 0, strlen($group) - 8);
				}
			}
			$explodedGroups = explode(':', $group);
			foreach ($explodedGroups as $singleGroup) {
				if (trim($singleGroup) == '') continue;
				$unprocGroups[] = (int)substr($singleGroup, 1);
			}
		}
		sort($unprocGroups);

		$nonLeafTeamDescendantGroups = [];
		if (sizeof($nonLeafTeamGroups) > 0) {
			$currentDescendants = $nonLeafTeamGroups;
			$iterCnt = 0;
			while (sizeof($currentDescendants) > 0 && $iterCnt < 100) {
				$currentDescendants = db_select_data('ko_groups', "WHERE `pid` IN (" . implode(',', $currentDescendants) . ")", 'id,pid');
				if ($currentDescendants) {
					$currentDescendants = array_keys($currentDescendants);
				} else {
					$currentDescendants = [];
				}
				$nonLeafTeamDescendantGroups = array_merge($nonLeafTeamDescendantGroups, $currentDescendants);
				$iterCnt += 1;
			}
		}

		sort($nonLeafTeamDescendantGroups);

		// get the intersection of all groups of the person and all non-leaf groups associated with a team
		$helperGroups = ko_fast_array_unique(ko_fast_array_intersect($nonLeafTeamDescendantGroups, $unprocGroups));

		//Make proper group IDs including the leading "g"
		$newHelperGroups = [];
		foreach ($helperGroups as $hg) {
			if (!$hg) continue;
			$newHelperGroups[] = 'g' . zerofill($hg, 6);
		}
		$helperGroups = $newHelperGroups;

		$regexp = '(((,|^)' . $id . '(,|$))' . (sizeof($helperGroups) == 0 ? ')' : '|' . implode('|', $helperGroups) . ')');
		$zWhere = " and `ko_rota_schedulling`.`schedule` regexp '" . $regexp . "'";

		$timeFilterEvents1 = " AND TIMESTAMPDIFF(SECOND,CONCAT(CONCAT(`ko_event`.`startdatum`, ' '), `ko_event`.`startzeit`),'" . $stop . "') >= 0";
		$timeFilterEvents2 = " AND TIMESTAMPDIFF(SECOND,CONCAT(CONCAT(`ko_event`.`enddatum`, ' '), `ko_event`.`endzeit`),'" . $start . "') <= 0";
		$timeFilterEvents = $timeFilterEvents1 . $timeFilterEvents2;

		$zWhere .= $timeFilterEvents;

		$events = [];
		$columns = "ko_event.id,ko_event.startdatum,ko_event.enddatum,ko_event.startzeit,ko_event.endzeit,ko_rota_schedulling.team_id";
		$where = "where ko_rota_schedulling.event_id LIKE ko_event.id " . $zWhere;
		$order = "ORDER BY ko_event.startdatum ASC";
		$res = db_select_data("ko_rota_schedulling, ko_event", $where, $columns, $order);
		foreach ($res as $event) {
			if (ko_rota_is_scheduling_disabled($event['id'], $event['team_id'])) continue;

			if (array_key_exists($event['id'], $events)) {
				$events[$event['id']]['in_teams'][] = $event['team_id'];
			} else {
				$events[$event['id']] = $event;
				$events[$event['id']]['in_teams'] = [$event['team_id']];
			}
		}

		// add daily events
		$start_day = date("Y-m-d", strtotime($start));
		$stop_day = date("Y-m-d", strtotime($stop));

		$where = "WHERE `schedule` regexp '" . $regexp . "'";
		$where .= " and `event_id` regexp '[0-9]{4}-[0-9]{2}-[0-9]{2}' and `event_id` >= '" . $start_day . "' and `event_id` <= '" . $stop_day . "'";
		$order = "ORDER BY event_id ASC";

		$dailyEvents = [];
		$res = db_select_data("ko_rota_schedulling", $where, "*", $order);
		foreach ($res as $k => $dailyEvent) {
			if (ko_rota_is_scheduling_disabled($dailyEvent['event_id'], $dailyEvent['team_id'])) continue;

			if (array_key_exists($dailyEvents, $dailyEvent['event_id'])) {
				$dailyEvents[$dailyEvent['event_id']]['in_teams'][] = $dailyEvent['team_id'];
			} else {
				$dailyEvents[$dailyEvent['event_id']] = $dailyEvent;
				$dailyEvents[$dailyEvent['event_id']]['in_teams'] = [$dailyEvent['team_id']];
			}
			$dailyEvents[$dailyEvent['event_id']]['team_id'] = $dailyEvent['team_id'];
		}

		foreach ($dailyEvents as $k => $dailyEvent) {
			$events[$k] = [
				'id' => $k,
				'startdatum' => date('Y-m-d', strtotime($k)),
				'startzeit' => date('H:i:s', strtotime($k)),
				'enddatum' => date('Y-m-d', strtotime($k)),
				'endzeit' => date('H:i:s', strtotime($k)),
				'in_teams' => $dailyEvent['in_teams'],
				'team_id' => $dailyEvent['team_id'],
			];
		}
	} else {
		// TODO : group functionality
		$events = [];
	}

	// cache result
	$GLOBALS['kOOL']['ko_scheduled_events'][$id . $start . $stop . $mode] = $events;

	return $events;
} // ko_rota_get_scheduled_events()


function ko_rota_get_participation($id, $teamId, $mode='past') {

	$result = array();
	$result['past'] = array();
	$result['future'] = array();
	$result['past'][$teamId] = array();
	$result['past']['all'] = array();
	$result['past']['all']['month'] = 0;
	$result['past']['all']['quarter'] = 0;
	$result['past']['all']['year'] = 0;
	$result['past'][$teamId]['month'] = 0;
	$result['past'][$teamId]['quarter'] = 0;
	$result['past'][$teamId]['year'] = 0;
	if ($mode == 'all') {
		$result['future'][$teamId] = array();
		$result['future']['all'] = array();
		$result['future']['all']['month'] = 0;
		$result['future']['all']['quarter'] = 0;
		$result['future']['all']['year'] = 0;
		$result['future'][$teamId]['month'] = 0;
		$result['future'][$teamId]['quarter'] = 0;
		$result['future'][$teamId]['year'] = 0;
		$end = date('Y-m-d H:i:s', strtotime('+1 year'));
	} else {
		$end = date('Y-m-d H:i:s');
	}

	$future = ($mode == 'all');
	$events = ko_rota_get_scheduled_events($id, date('Y-m-d H:i:s', strtotime('-1 year')), $end);

	foreach ($events as $event) {
		$endTime = strtotime($event['enddatum'] . ' ' . $event['endzeit']);
		$startTime = strtotime($event['startdatum'] . ' ' . $event['startzeit']);
		$now = time();
		$inArray = in_array($teamId, $event['in_teams']);
		$diffPast = $now - $endTime;
		$diffFuture = $startTime - $now;

		$x = '';
		if ($diffPast > 0) {
			$x = 'past';
		}
		else if ($future && $diffFuture > 0) {
			$x = 'future';
		}
		if ($x) {
			$result[$x]['all']['year'] += 1;
			if ($inArray) {
				$result[$x][$teamId]['year'] += 1;
			}
		}

		$x = '';
		if ($diffPast > 0 && $diffPast <= 3600 * 24 * 90) {
			$x = 'past';
		}
		else if ($future && $diffFuture > 0 && $diffFuture <= 3600 * 24 * 90) {
			$x = 'future';
		}
		if ($x) {
			$result[$x]['all']['quarter'] += 1;
			if ($inArray) {
				$result[$x][$teamId]['quarter'] += 1;
			}
		}

		$x = '';
		if ($diffPast > 0 && $diffPast <= 3600 * 24 * 30) {
			$x = 'past';
		}
		else if ($future && $diffFuture > 0 && $diffFuture <= 3600 * 24 * 30) {
			$x = 'future';
		}
		if ($x) {
			$result[$x]['all']['month'] += 1;
			if ($inArray) {
				$result[$x][$teamId]['month'] += 1;
			}
		}

	}
	return $result;
} // ko_rota_get_participation()


/**
 * return whether a person is scheduled for the supplied event and team
 *
 * @param $teamId
 * @param $eventId
 * @param $personId
 * @return bool
 */
function ko_rota_person_is_scheduled($teamId, $eventId, $personId) {
	if (ko_rota_is_scheduling_disabled($eventId, $teamId)) return FALSE;

	$es = db_select_data('ko_rota_schedulling', "WHERE `team_id` = '{$teamId}' AND `event_id` = '{$eventId}'", 'schedule', '', '', TRUE, TRUE);
	$schedule = $es ? $es['schedule'] : '';
	if (preg_match('/(^|,)'.$personId.'(,|$)/', $schedule)) {
		return TRUE;
	} else if (preg_match('/g[0-9]{6}/', $schedule)) {
		$scheduledGroups = explode(',', $schedule);
		ko_get_person_by_id($personId, $person);
		foreach ($scheduledGroups as $scheduledGroup) {
			if (substr($scheduledGroup, 0, 1) == 'g') {
				if (strpos($person['groups'], $scheduledGroup) !== FALSE) {
					return TRUE;
				}
			}
		}
	}
	return FALSE;
}

/**
 * return whether a group is scheduled for the supplied event and team
 *
 * @param $teamId
 * @param $eventId
 * @param $groupId
 * @return bool
 */
function ko_rota_group_is_scheduled($teamId, $eventId, $groupId) {
	if (ko_rota_is_scheduling_disabled($eventId, $teamId)) return FALSE;

	$es = db_select_data('ko_rota_schedulling', "WHERE `team_id` = '{$teamId}' AND `event_id` = '{$eventId}'", 'schedule', '', '', TRUE, TRUE);
	$schedule = $es ? $es['schedule'] : '';
	if (preg_match('/(^|,)'.$groupId.'(,|$)/', $schedule)) {
		return TRUE;
	}

	return FALSE;
}


/**
 * Creates a global Array of disabled schedules to check if the scheduling of an event is disabled for specific team
 *
 * @param $eventId Integer Id of Event
 * @param $teamId Integer Id of Team
 * @return bool True, if scheduling is disabled
 */
function ko_rota_is_scheduling_disabled($eventId, $teamId) {
	if(!isset($GLOBALS['kOOL']['rota_disabled_schedules'])) {
		$entries = db_select_data('ko_rota_disable_scheduling', "WHERE 1=1", '*', '', '');
		foreach($entries AS $entry) {
			$GLOBALS['kOOL']['rota_disabled_schedules'][$entry['event_id'].":".$entry['team_id']] = TRUE;
		}
	}

	if(isset($GLOBALS['kOOL']['rota_disabled_schedules'][$eventId.":".$teamId])) {
		return TRUE;
	}  else {
		return FALSE;
	}
}


function ko_rota_disable_scheduling($eventId, $teamId) {
	if (!$eventId || !$teamId) return FALSE;
	if (db_get_count('ko_rota_disable_scheduling', 'id', "AND `event_id` = '{$eventId}' AND `team_id` = {$teamId}") == 0) {
		db_insert_data('ko_rota_disable_scheduling', array('event_id' => $eventId, 'team_id' => $teamId));
	}
	return TRUE;
}


function ko_rota_enable_scheduling($eventId, $teamId) {
	if (!$eventId || !$teamId) return FALSE;
	db_delete_data('ko_rota_disable_scheduling', "WHERE `event_id` = '{$eventId}' AND `team_id` = {$teamId}");
	return TRUE;
}



function ko_rota_team_is_in_event($teamID, $eventID) {
	$event = db_select_data('ko_event', "WHERE `id` = '".$eventID."'", '*', '', '', TRUE);
	$teams = db_select_data('ko_rota_teams', "WHERE `eg_id` REGEXP '(^|,)".$event['eventgruppen_id']."(,|$)'");
	return in_array($teamID, array_keys($teams));
}//ko_rota_team_is_in_event()








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
 * Calculates the years, months, weeks and days in a duration and returns a well readable string.
 * Example: 3 years, 7 months, 1 day.
 * @param String $start Date
 * @param String $end Date
 * @return String well readable duration
 */
function ko_nice_timeperiod($start, $end) {
	$datetime1 = new DateTime($start);
	$datetime2 = new DateTime($end);
	$difference = $datetime1->diff($datetime2);
	$years = $difference->y;
	$months = $difference->m;
	$days = $difference->d;
	$weeks = floor($days / 7);

	if ($years >= 1) {
		$string_array[] = ngettext("$years ". getLL("year"), "$years ". getLL("years"), $years);
	}
	if ($months >= 1) {
		$string_array[] = ngettext("$months ". getLL("month"), "$months ". getLL("months"), $months);
	}
	if ($weeks >= 1) {
		$string_array[] = ngettext("$weeks ". getLL("week"), "$weeks ". getLL("weeks"), $weeks);
		$days = $days - ($weeks*7);
	}
	if ($days >= 1) {
		$string_array[] = ngettext("$days ". getLL("day"), "$days ". getLL("days"), $days);
	}

	if (empty($string_array)) {
		$string_array[] = "0 ". getLL("days");
	}

	return implode(", ", $string_array);
}



function ko_nice_money_amount($a) {
	if((float)$a === (float)floor($a)) {
		return number_format($a, 0, '.', "'").'.-';
	} else {
		return number_format($a, 2, '.', "'");
	}
}



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
function ko_groups_get_group_id_names($gid, &$groups, &$roles, $do_roles=TRUE, $includeMaxedGroups=TRUE) {
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
	if($includeMaxedGroups || (!$group['maxcount'] || $group['count'] < $group['maxcount'] || $group['count_role'])) {
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

	if(!$groupId) return $result;

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


	$groupsToBeSaved = explode(',', $data['groups']);
	//Add motherline groups as well
	foreach($groupsToBeSaved as $gid) {
		$motherline = ko_groups_decode(ko_groups_decode($gid, 'full_gid'), "mother_line");
		foreach($motherline as $mgid) {
			$groupsToBeSaved[] = $mgid;
		}
	}
	$groupsToBeSaved = array_unique($groupsToBeSaved);


	//save datafields of assigned groups
	foreach($groupsToBeSaved as $group_id) {
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
function ko_groups_render_group_datafields($groups, $id, $values=FALSE, $_options=array(), $do_dfs=array(), $printWrapper=TRUE) {
	global $all_groups, $ko_path, $smarty;

	if(!$all_groups) ko_get_groups($all_groups);

	if(!is_array($groups)) {
		$full_groups = explode(",", $groups);
		$groups = NULL;
		foreach($full_groups as $g) {
			$gid = ko_groups_decode($g, "group_id");
			$groups[] = array_merge($all_groups[$gid], array("desc_full" => ko_groups_decode($g, "group_desc_full")));
		}
	}

	//Add motherline groups as well
	foreach($groups as $group) {
		$motherline = ko_groups_decode(ko_groups_decode($group['id'], 'full_gid'), "mother_line");
		foreach($motherline as $mgid) {
			$groups[] = array_merge($all_groups[$mgid], array("desc_full" => ko_groups_decode($mgid, "group_desc_full")));
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

		if(!$_options["hide_title"]) {
			$html[$df]["title"] = $group["desc_full"];
			$html[$df]["title_short"] = $group["name"];
		}
		$html[$df]["content"] = '<div class="row">';
		foreach(explode(",", $group["datafields"]) as $fid) {
			//Only render given datafields if set
			if(sizeof($do_dfs) > 0 && !$do_dfs[$fid]) continue;

			$value = $values[$group['id']][$fid];

			//get datafield
			$field = db_select_data("ko_groups_datafields", "WHERE `id` = '$fid'", "*", "", "", TRUE);
			if(!$field["id"]) continue;

			$html[$df]["content"] .= '<div class="col-md-6"><label>'.$field["description"].': </label><br>';

			if($_options["add_leute_id"]) {
				$input_name = "group_datafields[$id][".$group["id"]."][$fid]";
			} else if($_options["koi"]) {
				$input_name = $_options["koi"];
			} else {
				$input_name = "group_datafields[".$group["id"]."][$fid]";
			}
			if ($group['stop'] != '0000-00-00' && $group['stop'] < date('Y-m-d')) {
				$disabled = TRUE;
			} else {
				$disabled = FALSE;
			}

			switch($field["type"]) {
				case "text":
					$html[$df]["content"] .= '<input class="input-sm form-control" type="text" size="40" name="'.$input_name.'" value="'.$value.'"'.($disabled?' disabled="disabled"':'').'>';
				break;

				case "textarea":
					$html[$df]["content"] .= '<textarea class="input-sm form-control" cols="40" rows="5" name="'.$input_name.'"'.($disabled?' disabled="disabled"':'').'>'.$value.'</textarea>';
				break;

				case "checkbox":
					$checked = $value ? 'checked="checked"' : "";
					$html[$df]["content"] .= '<input type="checkbox" name="'.$input_name.'" value="1" '.$checked.($disabled?' disabled="disabled"':'').'>';
				break;

				case "select":
					$options = unserialize($field["options"]);
					if(sizeof($options) == 0) break;

					$html[$df]["content"] .= '<select class="input-sm form-control" name="'.$input_name.'" size="0"'.($disabled?' disabled="disabled"':'').'>';
					$html[$df]["content"] .= '<option value=""></option>';
					foreach($options as $o) $html[$df]["content"] .= '<option value="'.$o.'" '.($o == $value ? 'selected="selected"' : '').'>'.$o.'</option>';
					$html[$df]["content"] .= '</select>';
				break;

				case "multiselect":
					$options = unserialize($field["options"]);
					$options = array_filter($options, function($el) {return trim($el) != '';});
					if(sizeof($options) == 0) break;

					$avalues = $adescs = array_filter(explode(',', $value), function($el) {return trim($el) != '';});
					$value = implode(',', $avalues);

					$input = array(
						'type' => 'doubleselect',
						'js_func_add' => 'double_select_add',
						'name' => $input_name,
						'values' => $options,
						'descs' => $options,
						'avalue' => $value,
						'avalues' => $avalues,
						'adescs' => $adescs,
						'disabled' => $disabled,
					);

					$smarty->assign('input', $input);
					$html[$df]["content"] .= $smarty->fetch('ko_formular_elements.tmpl');
				break;
			}
			$html[$df]["content"] .= "</div>";
		}
		$html[$df]["content"] .= "</div>";
		$df++;
	}//foreach(datafields as group)

	if ($printWrapper) $df_html = '<div id="datafields_form" name="datafields_form" class="df_content">';

	$df_html  .= '<ul class="nav nav-tabs" style="font-size:1.1em" role="tablist">';
	$first = TRUE;
	$counter = 0;
	foreach($html as $content) {
		$tabName = preg_replace('/[^A-Za-z0-9]+/', '_', $content["title"] . '_' . $counter);
		$df_html .= '<li role="presentation" class="'.($first?'active':'').'"><a href="#tab_leute_gdf_'.$tabName.'" data-toggle="tab">'.$content["title_short"].'</a></li>';
		$first = FALSE;
		$counter++;
	}
	$df_html .= '</ul>';
	$df_html .= '<div style="margin-bottom:0px;margin-top:0px;" class="tab-content">';
	$first = TRUE;
	$counter = 0;
	foreach($html as $content) {
		$tabName = preg_replace('/[^A-Za-z0-9]+/', '_', $content["title"] . '_' . $counter);
		$df_html .= '<div role="tabpanel" class="tab-pane panel panel-success '.($first?' active':'').'" id="tab_leute_gdf_'.$tabName.'">';
		$df_html .= '<div style="padding:12px 8px 8px 8px;" class="bg-success">';
		if($content["title"]) {
			$df_html .= '<span class="label label-default" style="font-size: 12px;">'.$content["title"].'</span><br><br>';
		}
		$df_html .= $content["content"];
		$df_html .= "</div>";
		$df_html .= "</div>";
		$first = FALSE;
		$counter++;
	}
	$df_html .= '</div>';
	if ($printWrapper) $df_html .= '</div>';

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
 * copies the group with the given id recursively
 * linked group pointers: are not touched unless the pointer points to one of the groups to be copied. In that case, the
 * pointer is redirected to the copied version of that group
 *
 * @param integer $id the id of the group to be copied
 * @param bool $assign_people directly assign people to new group
 * @return array
 */
function ko_copy_group_recursively($id, $assign_people = FALSE) {
	$start = db_select_data('ko_groups', "WHERE `id` = '{$id}'", '*', '', '', TRUE, TRUE);
	$queue = array(array('group' => $start, 'pid' => $start['pid']));
	$linkedTo = array();
	$oldToNew = array();
	$hierarchy = array();
	while (sizeof($queue) > 0) {
		$current = array_pop($queue);
		$new = $current['group'];

		$linkedTo[intval($current['group']['id'])] = intval($current['group']['linked_group']);

		if (intval($new['id']) == intval($id)) $new['name'] = $current['group']['name'] . getLL('groups_copy_suffix_new');

		unset($new['id']);
		unset($new['count']);
		unset($new['pid']);
		unset($new['mailing_alias']);  //Reset to not create double entries
		$new['crdate'] = date('Y-m-d H:i:s');
		if ($current['pid']) $new['pid'] = $current['pid'];

		$oldDfIds = array_map(function($e){return intval($e);}, explode(',', $current['group']['datafields']));
		$newDfIds = array();
		foreach ($oldDfIds as $dfId) {
			if (!$dfId) continue;
			$df = db_select_data('ko_groups_datafields', "WHERE `id` = {$dfId}", '*', '', '', TRUE, TRUE);
			if (!$df) continue;
			unset($df['id']);
			$newId = db_insert_data('ko_groups_datafields', $df);
			while (strlen($newId) < 6) $newId = '0'.$newId;
			$newDfIds[] = $newId;
			$oldToNewDfIds[(int)$dfId] = (int)$newId;
		}
		$new['datafields'] = implode(',', $newDfIds);

		$newId = db_insert_data('ko_groups', $new);
		$current['group']['new_id'] = $newId;
		$oldToNew[intval($current['group']['id'])] = $newId;

		$used_taxonomy_terms = ko_taxonomy_get_terms_by_node($current['group']['id'], "ko_groups");
		$new_taxonomy_terms = [];
		foreach($used_taxonomy_terms as $used_taxonomy_term) {
			$new_taxonomy_terms[] = $used_taxonomy_term['id'];
		}
		ko_taxonomy_attach_terms_to_node($new_taxonomy_terms, "ko_groups", $current['group']['new_id']);

		$children = db_select_data('ko_groups', "WHERE `pid` = '{$current['group']['id']}'");
		$childrenIds = array();
		foreach ($children as $child) {
			$childrenIds[] = intval($child['id']);
			array_push($queue, array('group' => $child, 'pid' => $newId));
		}

		$hierarchy[intval($current['group']['id'])] = array('group' => $current['group'], 'children' => $childrenIds);
	}


	if ($assign_people == TRUE) {
		$where = "WHERE groups LIKE '%g" . $id ."%'";
		$persons_in_old_group = db_select_data("ko_leute", $where);
		foreach($persons_in_old_group AS $person_in_old_group) {
			$person_groups = explode(",",$person_in_old_group['groups']);

			foreach($person_groups AS $person_group) {
				if(stristr($person_group, $id)) {
					$new_group = $person_group;
					foreach($oldToNew AS $old_id => $new_id) {
						$new_group = str_replace("g".zerofill($old_id,6), "g".zerofill($new_id,6), $new_group);
					}

					if($new_group != $person_group) {
						$person_groups[] = $new_group;
					}
				}
			}

			$where = "WHERE id = " . $person_in_old_group['id'];
			$data = ["groups" => implode(",", $person_groups)];
			db_update_data("ko_leute", $where, $data);

			foreach($oldToNew AS $old_id => $new_id) {
				$where = "WHERE person_id = '" . $person_in_old_group['id'] . "' AND 
						group_id = '" . $old_id ."' AND stop ='0000-00-00 00:00:00'";
						$last_role = db_select_data("ko_groups_assignment_history", $where, "role_id", "ORDER BY id DESC", "LIMIT 1", TRUE, TRUE);
				if(!empty($last_role)) {
					$data = [
						"group_id" => $new_id,
						"person_id" => $person_in_old_group['id'],
						"role_id" => $last_role['role_id'],
						"start" => date("Y-m-d H:i:s"),
						"start_is_exact" => 1,
					];
					db_insert_data("ko_groups_assignment_history", $data);
				}

				foreach($oldToNewDfIds AS $old_df_id => $new_df_id) {
					$where = "WHERE person_id = '" . $person_in_old_group['id'] . "' AND
						group_id = '" . $old_id ."' AND datafield_id = '" . $old_df_id . "' AND deleted = 0";
					$datafield_datas = db_select_data("ko_groups_datafields_data", $where, "value");

					foreach($datafield_datas AS $datafield_data) {
						$data = [
							"group_id" => $new_id,
							"person_id" => $person_in_old_group['id'],
							"datafield_id" => $new_df_id,
							"value" => $datafield_data['value'],
						];
						db_insert_data("ko_groups_datafields_data", $data);
					}
				}
			}
		}
	}

	// adjust linked groups
	foreach ($linkedTo as $from => $to) {
		if (array_key_exists($to, $oldToNew)) {
			$update = array('linked_group' => $oldToNew[$to]);
			db_update_data('ko_groups', "WHERE `id` = {$oldToNew[$from]}", $update);
		}
	}

	ko_groups_get_hierarchy_lines($hierarchy, $lines, '-', array('id', 'new_id', 'name'));
	ko_log('copy_group_hierarchy', 'copied the following groups: ' . print_r($oldToNew, TRUE) . '<br>' . implode('<br>', $lines));

	return array($oldToNew, $hierarchy);
}


function ko_groups_get_hierarchy_lines($hierarchy, &$lines, $separator='&nbsp;&nbsp;-&nbsp;', $fields=array('name'), $startId=NULL, $depth=0) {
	if ($startId == NULL) $startId = key($hierarchy);

	$pre = '';
	for ($i = 0; $i < $depth; $i++) {
		$pre .= $separator;
	}

	$f = array();
	foreach ($fields as $field) {
		$f[] = $hierarchy[$startId]['group'][$field];
	}
	$lines[] = $pre . implode(', ', $f);
	foreach ($hierarchy[$startId]['children'] as $childId) {
		ko_groups_get_hierarchy_lines($hierarchy, $lines, $separator, $fields, $childId, $depth+1);
	}
}



/**
  * Erstellt die JS-Einträge für ein Selmenu für das Gruppen-Modul
	*/
function ko_selmenu_generate_children_entries($top_id, $list_id, &$all_groups, &$all_roles, $show_all_types=FALSE, $includeMaxedGroups=TRUE) {
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
			list($values, $descs) = ko_groups_get_group_id_names($group["id"], $all_groups, $all_roles, $do_roles=FALSE, $includeMaxedGroups);
			foreach($values as $i => $value) {
				if($show_all_types || $group["type"] != 1) {  //Platzhalter-Gruppen nicht ausgeben
					print 'addItem('.$list_id.', '.$counter.', "'.$value.'", "'.str_replace(array("'", '"'), array("\\'", '\"'), $descs[$i]).'");'."\n";
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
		print 'addSubList('.$list_id.', '.$list_counter[$level].', 0, "'.str_replace(array("'", '"'), array("\\'", '\"'), $all_groups[$top_id]["name"]).' -->", "'.implode(", ", $group_list).'");'."\n";
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
	$count = db_get_count('ko_leute', 'id', "AND `groups` REGEXP '$rex' AND deleted = '0' AND `hidden` = '0'");
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
/**
 * Includes KOTA definitions for given tables
 *
 * @param array|string $tables the array containing the table names for which to include KOTA definitions. If '_all',
 *                             then all KOTA definitions will be included
 */
function ko_include_kota($tables=array()) {
	global $BASE_PATH, $KOTA, $RES_GUEST_FIELDS_FORCE, $ko_menu_akt, $access, $SMALLGROUPS_ROLES, $LOCAL_LANG, $MODULES;
	global $EVENT_PROPAGATE_FIELDS, $LEUTE_NO_FAMILY;

	if ($tables === '_all') {
		$tables = array_map(function($e) {return end($e);}, db_query("SHOW TABLES"));
	}

	//Include KOTA function (once)
	if(!function_exists('kota_get_form')) {
		include($BASE_PATH.'inc/kotafcn.php');
	}

	//Include KOTA table definitions for given tables
	$KOTA_TABLES = $tables;
	include($BASE_PATH.'inc/kota.inc');

	//Apply access rights --> unset KOTA columns the current user has no access to
	foreach($tables as $table) {
		//Store all columns for reference
		$allFormCols = array();
		if(isset($KOTA[$table])) {
			foreach($KOTA[$table] as $k => $v) {
				if(substr($k, 0, 1) == '_') continue;
				if(!isset($v['form']) || $v['form']['dontsave'] === TRUE) continue;
				$allFormCols[] = $k;
			}
		}
		$KOTA[$table]['_allformcolumns'] = $allFormCols;

		$delcols = array();
		$cols = ko_access_get_kota_columns($_SESSION['ses_userid'], $table);
		if(sizeof($cols) > 0) {
			foreach($KOTA[$table] as $k => $v) {
				if(substr($k, 0, 1) == '_') continue;
				if($v['exclude_from_access'] || !isset($v['form'])) continue;
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
	* @param string $columns Comma separated list of columns to be edited (empty for all)
	* @param string $ids Comma separated list of ids to be edited. 0 for a new entry
	* @param string $order ORDER BY statement to be used if editing multiple entries
	* @param array $form_data Data for the rendering of the form (like title etc.)
	* @param boolean $return_only_group Renders form if set to false, only return group array otherwise which can be used to feed ko_formular.tmpl through smarty
	* @param string $_kota_type Specify kota type for a new entry
	* @return string|void the formgroup if $return_only_group==TRUE
	*/
function ko_multiedit_formular($table, $columns="", $ids=0, $order="", $form_data="", $return_only_group=FALSE, $_kota_type='', $showLegend=TRUE, $formMode="formular", $formVersion="1", $accessModifiers=FALSE, $ignoreMandatory=FALSE) {
	global $smarty, $mysql_pass, $access, $BASE_PATH;
	global $KOTA, $BASE_URL;
	global $BOOTSTRAP_COLS_PER_ROW;

	if (!$accessModifiers) $accessModifiers = array();
	else if (!is_array($accessModifiers)) $accessModifiers = explode(',', $accessModifiers);

	//Columns used in SQL
	if($columns == "") {  //not multiedit, so take all KOTA-columns
		$columns = array();
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

		//Add help for the given table
		$smarty->assign("help", ko_get_help("kota", $table));
	} else {  //multiedit, only use given column(s)
		$mode = "multi";
		$showForAll = TRUE;

		//Add help for multiediting
		$smarty->assign("help", ko_get_help("kota", "multiedit"));
	}//if..else(!columns)

	//IDs of the records to be edited
	if($ids == 0) {  //no multiedit, might be a form for a new entry, so don't ask for ids
		$ids = array(0);
		$new_entry = TRUE;
		$sel_ids = 0;
		$row = array("id" => 0);
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
	$result = mysqli_query(db_get_link(), "SELECT * FROM `$table` WHERE `id` IN ($sel_ids) $order");

	//Start building the form
	$rowcounter = 0;
	$gc = 0;
	$tc = 0;
	//Loop through all entries
	while($new_entry || $showForAll || $row = mysqli_fetch_assoc($result)) {
		// Save original KOTA array for table so it can be restored
		$kotaBackup = $KOTA[$table][$col];
		if ($new_entry) {  //Single edit form
			$group[$gc] = array();
		} else if ($showForAll) {  //Add edit fields to be applied to all edited rows
			$group[$tc]['groups'][$gc] = array("group" => TRUE, "forAll" => TRUE, "titel" => getLL("multiedit_title_forAll"), "state" => "closed", "table" => $table);
			$row = array();

			// If not old form, add key to group
			if ($formVersion != '1') $group[$tc]['groups'][$gc]['name'] = 'multiedit_for_all';
		} else {  //normal multiedit rows
			//Check for access condition
			$accessOkay = TRUE;
			$replacementRow = array();
			foreach ($row as $k => $v) {
				$replacementRow["@{$k}@"] = $v;
			}
			if (is_array($KOTA[$table]['_access']['condition'])) {
				if (isset($KOTA[$table]['_access']['condition']['edit'])) {
					if (FALSE === eval(strtr($KOTA[$table]['_access']['condition']['edit'], $replacementRow))) $accessOkay = FALSE;
				}
			} else if (isset($KOTA[$table]['_access']['condition'])) {
				if (FALSE === eval(strtr($KOTA[$table]['_access']['condition'], $replacementRow))) $accessOkay = FALSE;
			}
			if (!$accessOkay) {
				unset($ids[array_search($row['id'], $ids)]);
				if (sizeof($ids) == 0) return FALSE;
				continue;
			}
			$titel = kota_get_multititle($table, $row);
			$group[$tc]['groups'][$gc] = array("group" => TRUE, "titel" => $titel, "state" => "open");
			$group[$tc]['groups'][$gc]['options'] = $form_data['options'];

			// If not old form, add key to group
			if ($formVersion != '1') $group[$tc]['groups'][$gc]['name'] = "multiedit_item_{$row['id']}";
		}

		//Add columns if a certain kota type is given
		if ($mode == 'single' && isset($KOTA[$table]['_types']['field'])) {
			$kota_type = $_kota_type ? $_kota_type : $row[$KOTA[$table]['_types']['field']];
			if ($kota_type != $KOTA[$table]['_types']['default'] && sizeof($KOTA[$table]['_types']['types'][$kota_type]['add_fields']) > 0) {
				foreach ($KOTA[$table]['_types']['types'][$kota_type]['add_fields'] as $add_colid => $add_col) {
					$KOTA[$table][$add_colid] = $add_col;
					$columns[] = $add_colid;
				}
			}
		}

		$colInfo = array();
		//Alle zu bearbeitenden Spalten für diesen Datensatz
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

			$keep_name = "";
			if($showForAll) {
				$keep_name = "koi[$table][$col][forAll]";
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

			// Only show html in case of read-only field
			$readOnly = in_array('readonly', $accessModifiers) || $KOTA[$table][$col]['form']["readonly"] || $KOTA[$table]['_access']["readonly"];

			if(!$readOnly) {
				$accessKey = $KOTA[$table]['_access']['key'];
				if(!$accessKey) $accessKey = $table;
				if($KOTA[$table]['_access']['fillEmptyOnly'] > 0
					&& ($KOTA[$table]['_access']['fillEmptyOnly']  == $access[$accessKey][$row[$KOTA[$table]['_access']['chk_col']]]
						|| $KOTA[$table]['_access']['fillEmptyOnly']  == $access[$accessKey]['ALL'])
					&& !in_array($row[$col], array('', '0', '0000-00-00', '0000-00-00 00:00:00'))
					) {
					$readOnly = TRUE;
				}
			}


			if ($readOnly) $type = 'html';

			//Add description from LL
			if(!$KOTA[$table][$col]["form"]["desc"]) {
				$ll_value = getLL("kota_".$table."_".$col);
				$KOTA[$table][$col]["form"]["desc"] = $ll_value ? $ll_value : $col;
			}
			$help = ko_get_help($module, 'kota.'.$table.'.'.$col);
			if($help['show']) {
				$KOTA[$table][$col]['form']['help'] = $help['link'];
			}

			$setPostValue = is_array($_POST["koi"][$table]);
			if ($setPostValue) $postValue = $_POST['koi'][$table][$col][$row['id']];
			//Vorbehandlung für versch. Typen
			if($type == "date") {  //no JS-DateSelect (used for multiedit)
				$type = "text";
			}

			else if ($type == "select") {
				if ($KOTA[$table][$col]["form"]["data_func"] && function_exists($KOTA[$table][$col]["form"]["data_func"])) {
					$KOTA[$table][$col]["form"] = array_merge($KOTA[$table][$col]["form"], call_user_func_array($KOTA[$table][$col]["form"]["data_func"], array($row)));
				}
				if($new_entry && $KOTA[$table][$col]["form"]["default"] !== null) $row[$col] = $KOTA[$table][$col]["form"]["default"];
			}

			else if($type == "days_range") {
				$KOTA[$table][$col]["form"]['avalues'] = $row[$col];
			}
			else if($type == "jsdate") {
				$type = "datepicker";
				//Prefill form for new entry with POST data
				$post_date = $_POST['koi'][$table][$col][$row['id']];
				if($new_entry && $KOTA[$table][$col]['form']['prefill_new'] && $post_date != '' && $post_date == format_userinput($post_date, 'date')) {
					$date = $_POST['koi'][$table][$col][$row['id']];
				} else {
					$date = $KOTA[$table][$col]["form"]["value"];
					if(strlen($date) > 19) $date = "";  //if several jsdate inputs are used on one page, value still contains HTML from the last one
				}
				if(!$date && $row[$col] != "0000-00-00") $date = sql2datum($row[$col]);

				if($KOTA[$table][$col]['pre'] != '' && isset($row[$col])) {
					$data = $row;
					kota_process_data($table, $data, 'pre', $_log, $row['id'], $new_entry);
					$date = $data[$col];
				}

				$name = $keep_name ? $keep_name : "koi[$table][$col][".$row["id"]."]";
				$KOTA[$table][$col]["form"]["value"] = $date;
				$KOTA[$table][$col]["form"]["name"] = $name;

			}

			else if($type == 'multidateselect') {  //use js-calendar
				$name = $keep_name ? $keep_name : "koi[$table][$col][".$row['id'].']';
				$onchange = "double_select_add(this.value, this.value, 'sel_ds2_$name', '$name');";

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
				if ($setPostValue) {
					$preValue = $postValue;
				}
				else {
					$preValue = $row[$col];
				}
				$KOTA[$table][$col]["form"]["params"] = $preValue ? 'checked="checked"' : '';
				$row[$col] = 1;  //Für Checkboxen Value immer auf 1 setzen
			}

			else if($type == 'switch') {
				if ($setPostValue) {
					$preValue = $postValue;
				}
				else if (!$new_entry) {
					$preValue = $row[$col];
				}
				else {
					if ($KOTA[$table][$col]["form"]["default"] !== null) $preValue = $KOTA[$table][$col]["form"]["default"];
					else $preValue = 0;
				}
				$row[$col] = $preValue;
			}

			else if($type == "textplus") {
				// only show select
				if (!$KOTA[$table][$col]["form"]["_done"]) {
					if ($showForAll) {
						$type = 'select';
						$KOTA[$table][$col]["form"]["type"] = 'select';

						if (!$KOTA[$table][$col]["form"]["id_link"]) {
							foreach ($KOTA[$table][$col]['form']['descs'] as $k => $desc) {
								$KOTA[$table][$col]['form']['values'][$k] = $desc;
							}
						}
					}
					if(!$KOTA[$table][$col]["form"]["values"]) {
						$values = db_select_distinct($table, $col, "", $KOTA[$table][$col]['form']['where'], $KOTA[$table][$col]["form"]["select_case_sensitive"] ? TRUE : FALSE);

						foreach ($values as $k => $v) {
							if (trim($v) == '') unset($values[$k]);
						}

						// Load default value in case of new entry
						if ($row['id'] == 0 && $KOTA[$table][$col]["form"]["default"]) {
							$row[$col] = $KOTA[$table][$col]["form"]["default"];
							if (!in_array($row[$col], $values)) $values[] = $row[$col];
						}
						$KOTA[$table][$col]["form"]["values"] = $values;
						$KOTA[$table][$col]["form"]["descs"] = $values;

						//Add empty value at top
						$KOTA[$table][$col]['form']['descs'][-1] = '';
						$KOTA[$table][$col]['form']['values'][-1] = '';
						ksort($KOTA[$table][$col]['form']['descs']);
						ksort($KOTA[$table][$col]['form']['values']);

					}
					if ($showForAll) {
						$KOTA[$table][$col]["form"]["_done"] = TRUE;
					}
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
				$separator = $KOTA[$table][$col]['form']['separator'];
				if(!$separator) $separator = ',';

				if ($setPostValue) {
					$KOTA[$table][$col]['form']['avalues'] = $KOTA[$table][$col]['form']['adescs'] = array();
					$postValues = explode($separator, $postValue);
					foreach($postValues as $v) {
						$KOTA[$table][$col]['form']['avalues'][] = $v;
					}
				}
				else if (!$new_entry) {  //add entries from db if edit. If new, kota_assign_values() assigns avalue/adescs/avalues - if needed
					$KOTA[$table][$col]['form']['avalues'] = $KOTA[$table][$col]['form']['adescs'] = array();
					$valuesi = array_flip($KOTA[$table][$col]['form']['values']);
					if(isset($row[$col])) {
						foreach(explode($separator, $row[$col]) as $v) {
							$KOTA[$table][$col]['form']['avalues'][] = $v;
						}
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
						[$values, $descs] = kota_convert_dynselect_select($KOTA[$table][$col]["form"]["values"], $KOTA[$table][$col]["form"]["descs"]);
						$KOTA[$table][$col]["form"]["params"] = ' size="0"';  //Set size to 0 for multiedit
						$KOTA[$table][$col]["form"]["values"] = $values;
						$KOTA[$table][$col]["form"]["descs"] = $descs;
						$KOTA[$table][$col]["form"]["_done"] = TRUE;  //So this conversion is only done once for the forAll entry and then used in all
					} else {  //Normal form, no multiediting
						$KOTA[$table][$col]["form"]["multiple"] = true;
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

			else if ($type == 'groupselect') {
				$KOTA[$table][$col]["form"]["onclick_2_add"] = $KOTA[$table][$col]["form"]["onclick_2_add"] ? $KOTA[$table][$col]["form"]["onclick_2_add"] : 'do_update_df_form(\''.$row['id'].'\',\''.session_id().'\');';
				$KOTA[$table][$col]["form"]["onclick_del_add"] = $KOTA[$table][$col]["form"]["onclick_del_add"] ? $KOTA[$table][$col]["form"]["onclick_del_add"] : 'do_update_df_form(\''.$row['id'].'\',\''.session_id().'\');';

				if (!isset($access['groups'])) ko_get_access('groups');
				//Nur gültige IDs auslesen, Rest wird durch js-groupmenu.inc erledigt

				$assigned_groups = array();
				//Hier die alten Gruppen immer ausblenden
				$orig_value = ko_get_userpref($_SESSION['ses_userid'], 'show_passed_groups');
				ko_save_userpref($_SESSION['ses_userid'], 'show_passed_groups', 0);
				ko_get_groups($grps, ko_get_groups_zwhere());
				ko_save_userpref($_SESSION['ses_userid'], 'show_passed_groups', $orig_value);
				$valid_ids = array();
				$allow_assign = false;
				foreach($grps as $grp) {
					if($access['groups']['ALL'] > 0 || $access['groups'][$grp['id']] > 0) $valid_ids[] = $grp['id'];
					if(!$allow_assign && ($access['groups']['ALL'] > 1 || $access['groups'][$grp['id']] > 1)) $allow_assign = true;
				}
				$KOTA[$table][$col]["form"]["allow_assign"] = $allow_assign;
				//Bestehende Werte einfüllen
				$do_datafields = null;
				foreach(explode(",", $row[$col]) as $grp) {
					if($grp) {
						$grp_id = ko_groups_decode($grp, "group_id");
						$grp_desc = ko_groups_decode($grp, "group_desc_full");
						if(in_array($grp_id, $valid_ids)) {
							//Prepare for sorting
							$assigned_groups[$grp] = $grp_desc;
							$sort_assigned_groups[$grp_desc] = $grp;
						}
					}//if(group)
				}//foreach(person[groups])

				$KOTA[$table][$col]['form']['avalue'] = $row[$col];
				$KOTA[$table][$col]["form"]['avalues'] = $KOTA[$table][$col]["form"]['adescs'] = array();
				//Sort assigned groups alphabetically
				ksort($sort_assigned_groups);
				foreach($sort_assigned_groups as $grp) {
					$KOTA[$table][$col]["form"]['avalues'][] = $grp;
					$KOTA[$table][$col]["form"]['adescs'][] = $assigned_groups[$grp];
				}

				$KOTA[$table][$col]['form']['avalue'] = implode(",", $KOTA[$table][$col]["form"]['avalues']);
				if(sizeof($valid_ids) == 0) {
					$KOTA[$table][$col]["form"]["params"] .= 'disabled="disabled"';
				}
			}

			else if($type == 'peoplesearch') {
				if($row[$col] != '' || !empty($postValue)) {
					if(empty($row[$col]) && !empty($postValue)) {
						$row[$col] = $postValue;
					}
					$lids = explode(",", $row[$col]);
					[$avalues, $adescs, $astatus] =  kota_peopleselect($lids, $KOTA[$table][$col]['form']['sort']);
					$KOTA[$table][$col]["form"]["avalues"] = $avalues;
					$KOTA[$table][$col]["form"]["adescs"] = $adescs;
					$KOTA[$table][$col]["form"]["astatus"] = $astatus;

					$KOTA[$table][$col]["form"]["avalue"] = $row[$col];
				}
			}

			else if($type == 'groupsearch') {
				if($row[$col] != '') {
					$groupSData = kota_groupselect($row[$col]);
					array_walk_recursive($groupSData, 'utf8_encode_array');
					$KOTA[$table][$col]["form"]["data"] = json_encode($groupSData);

					$KOTA[$table][$col]["form"]["avalue"] = $row[$col];
				}
			}

			else if($type == 'dynamicsearch') {
				switch($col) {
					case 'team':

						$KOTA[$table]['team']["form"]["data"] = '';
						if($row[$col] != '') {
							$teamsInProgram = explode(",", $row[$col]);
							for ($i = 0; $i < count($teamsInProgram); $i++) {
								if (is_numeric($teamsInProgram[$i])) {
									$team = db_select_data('ko_rota_teams', 'WHERE id =' . $teamsInProgram[$i], 'id, name', '', 'LIMIT 1', TRUE, TRUE);
									$team['title'] = $team['name'];
								} else {
									$team['id'] = $teamsInProgram[$i];
									$team['name'] = $team['title'] = "\"" . $teamsInProgram[$i] . "\"";
								}
								$dynamicData[$i] = $team;
							}
							$KOTA[$table]['team']["form"]["data"] = $dynamicData;
						}

						if (ko_get_setting('rota_manual_ordering')) {
							$teams_orderby = "ORDER BY sort ASC";
						} else {
							$teams_orderby = "ORDER BY name ASC";
						}
						$teams = db_select_data('ko_rota_teams', '', 'id, name, name AS title', $teams_orderby, '' , FALSE, TRUE);

						$KOTA[$table]['team']["form"]["avalues"] = $teams;
						$KOTA[$table]['team']["form"]["adescs"] = $teams;
						$KOTA[$table]['team']["form"]["atitles"] = $teams;
						break;
					case 'terms':
						$KOTA[$table]['terms']["form"] = ko_taxonomy_form_field($row['id'], $table);
						break;
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
					$text = strip_tags(substr($text, 0, -1));
					$adescs[] = $text;
				}
				$KOTA[$table][$col]['form']['avalue'] = $row[$col];
				$KOTA[$table][$col]['form']['avalues'] = $avalues;
				$KOTA[$table][$col]['form']['adescs'] = $adescs;
			}

			else if($type == 'foreign_table') {
				$own_row = TRUE;

				$childTable = $KOTA[$table][$col]['form']['table'];
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
				if (isset($KOTA[$table][$col]['form']['sort_button'])) {
					$sortButtonCols = $KOTA[$table][$col]['form']['sort_button'];
					$sortButtonColsArray = explode(',', $sortButtonCols);
					$sbcNames = array();
					foreach ($sortButtonColsArray as $sbck => $sbc) {
						if (preg_match('/^`?([^` ]*)`? .*$/', $sbc, $sbcMatches)) {
							$sbcNames[] = $sbcMatches[1];
						}
					}
					$smarty->assign('ft_show_sort_btn', TRUE);
					$smarty->assign('ft_sort_btn_title', sprintf(getLL('form_ft_button_sort_title'), implode(', ', ko_array_ll($sbcNames, "kota_{$childTable}_"))));
				} else {
					$smarty->assign('ft_show_sort_btn', FALSE);
				}

				//Check access to foreign_table if new button should be shown
				$accessKey = $KOTA[$childTable]['_access']['key'];
				if(!$accessKey) $accessKey = $childTable;
				$smarty->assign('ft_show_new_btn', $access[$accessKey]['MAX'] >= $KOTA[$childTable]['_access']['level']);

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
					$KOTA[$table][$col]['form']['mtime'] = filemtime($BASE_PATH.$file);
				} else {
					//Reset KOTA entry otherwise previous entries fill later entries
					$KOTA[$table][$col]['form']['value'] = '';
					$KOTA[$table][$col]['form']['special_value'] = '';
				}
				//add delete checkbox for files
				$KOTA[$table][$col]["form"]["value2"] = getLL("delete");
				$KOTA[$table][$col]["form"]["name2"] = "koi[$table][$col"."_DELETE][".$row["id"]."]";
			}//if..else(type==...)

			else if ($type == '_save') {

			}

			else if ($type == 'textarea') {
				if($new_entry && $KOTA[$table][$col]['form']['prefill_new'] && $_POST['koi'][$table][$col][$row['id']] != '') {
					$KOTA[$table][$col]['form']['value'] = $_POST['koi'][$table][$col][$row['id']];
				}
			}

			else if (in_array($type, ['richtexteditor', 'text'])) {
				if ($new_entry && $KOTA[$table][$col]['form']['default']) {
					$row[$col] = $KOTA[$table][$col]['form']['default'];
				}
			}

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
			} else if ($type == 'textplus') {
				if($new_entry && $KOTA[$table][$col]['form']['prefill_new'] && $_POST['koi'][$table][$col][$row['id']] != '') {
					$KOTA[$table][$col]['form']['value'] = $_POST['koi'][$table][$col][$row['id']];
					if (!in_array($KOTA[$table][$col]["form"]["values"], $KOTA[$table][$col]["form"]["descs"])) {
						$KOTA[$table][$col]["form"]["descs"][] = $_POST['koi'][$table][$col][$row['id']];
						$KOTA[$table][$col]["form"]["values"][] = $_POST['koi'][$table][$col][$row['id']];
					}
				}
			}


			$colInfo[$col] = $KOTA[$table][$col]["form"];
			$colInfo[$col]["type"] = $type;
			if(!$KOTA[$table][$col]["form"]["value"]) {
				$val = $row[$col];
				if((!$readOnly && $KOTA[$table][$col]["pre"] != "") || ($readOnly && ($KOTA[$table][$col]["list"] != "" || $KOTA[$table][$col]["xls"] != "" || $KOTA[$table][$col]["pdf"] != ""))) {
					$data = array($col => $val);

					if ($table == "ko_groups_assignment_history" && $col == "stop" ) {
						// check if user is currently in the group, otherwise: hide input
						if ($data[$col] == "0000-00-00 00:00:00") {
							unset($columns[array_search($col,$columns)]);
							unset($colInfo[$col]);
							continue;
						}
					}

					$colInfo[$col]['ovalue'] = $val;

					if ($readOnly) $processMode = 'list,xls,pdf';
					else $processMode = 'pre';
					kota_process_data($table, $data, $processMode, $_log, $row["id"], $new_entry, $row);
					$val = $data[$col];
				}
				$colInfo[$col]["value"] = $val;
				$colInfo[$col]["avalue"] = $KOTA[$table][$col]["form"]["avalue"];
				$colInfo[$col]["avalues"] = $KOTA[$table][$col]["form"]["avalues"];
				$colInfo[$col]["adescs"] = $KOTA[$table][$col]["form"]["adescs"];
				if(is_array($KOTA[$table][$col]["form"]["astatus"])) $colInfo[$col]["astatus"] = $KOTA[$table][$col]["form"]["astatus"];
			}

			if($keep_name) {
				$colInfo[$col]["name"] = $keep_name;
			} else {
				$colInfo[$col]["name"] = "koi[$table][$col][" . $row["id"] . "]";
			}
			// set an escaped id for the html element
			$colInfo[$col]['html_id'] = preg_replace('/\[|\]|\./', '_', $colInfo[$col]['name']);
			$colInfo[$col]['html_id'] = preg_replace('/[_]+/', '_', $colInfo[$col]['html_id']);
			$colInfo[$col]['html_id'] = preg_replace('/_$/', '', $colInfo[$col]['html_id']);
			if (isset($colInfo[$col]["name2"])) {
				$colInfo[$col]["html_id2"] = preg_replace('/\[|\]|\./', '_', $colInfo[$col]['name2']);
				$colInfo[$col]['html_id2'] = preg_replace('/[_]+/', '_', $colInfo[$col]['html_id2']);
				$colInfo[$col]['html_id2'] = preg_replace('/_$/', '', $colInfo[$col]['html_id2']);
			}

			$colInfo[$col]['do_1'] = $do_1; // is not considered below as it is no longer needed
			$colInfo[$col]['do_1_array'] = $do_1_array; // is not considered below as it is no longer needed
			$colInfo[$col]['own_row'] = $own_row;
			$colInfo[$col]['new_row'] = $KOTA[$table][$col]["form"]["new_row"];

			if (isset($colInfo[$col]['overwrite'])) {
				$colInfo[$col]['overwrite_name'] = "koi[$table][" . $col . "_overwrite][" . $row["id"] . "]";
				$colInfo[$col]['overwrite_value'] = (!is_numeric($colInfo[$col]["value"]) ? $colInfo[$col]["value"] : '');
			}

			if(!empty($KOTA[$table][$col]["form"]['sibling'])) {
				$colInfo[$col]['sibling'] = str_replace("_".$col."_", "_".$KOTA[$table][$col]["form"]['sibling']."_", $colInfo[$col]['html_id'] );
			}

			if($col_pos == 1 || $do_1 || $KOTA[$table][$col]["form"]["new_row"]) {
				$rowcounter++;
				$col_pos = 0;
			} else {
				$col_pos = 1;
			}

			$colInfo[$col]['colname'] = $col;

			// set mandatory
			// (not for multiedit as this does not work with forAll field)
			if ($mode != 'multi' && !$ignoreMandatory && kota_field_is_mandatory($table, $col, 'form')) {
				$colInfo[$col]['add_class'] .= ' mandatory';
				$colInfo[$col]['is_mandatory'] = TRUE;
			}

		}//foreach(columns as col) $group[$gc]["row"][$rowcounter]["inputs"][$col_pos];

		// Arrange Columns according to '_form_layout' entry in KOTA
		koFormLayoutEditor::sortAll($KOTA, $table);
		$formLayout = $KOTA[$table]['_form_layout'];
		// set internal default formLayout in case none is set in the KOTA definition
		if (!is_array($formLayout)) {
			$formLayout = array('_default_cols' => 6, '_default_width' => 6);
		}
		//ksort($formLayout);
		$defaultCols = $formLayout['_default_cols'];
		// internal default for cols per row is 2
		if (!$defaultCols) $defaultCols = 6;
		$defaultWidth = $formLayout['_default_width'];
		// internal default for column width is 0.5
		if (!$defaultWidth) $defaultWidth = 6;

		$rc = 0;
		if ($formMode == 'formular' && $mode == 'single') {
			$generalRc = 0;
			$generalGc = 0;
			$generalTc = 0;

			$tc = $gc = 0;
			if ($return_only_group && $formVersion == "1") {
				$tk = 'general';
				$tabLayout = &$formLayout[$tk];

				foreach ($tabLayout as $gk => &$groupLayout) {
					if ($tk == 'general' && $gk == 'general') {
						$generalTc = $tc;
						$generalGc = $gc;
					}

					$group[$tc]['groups'][$gc]['titel'] = getLL("kota_layout_group_{$table}_" . $groupLayout['title']);
					$group[$tc]['groups'][$gc]['show_save'] = $groupLayout['show_save'] ? TRUE : FALSE;
					$group[$tc]['groups'][$gc]['appearance'] = $groupLayout['appearance'];
					$rc = 0;

					foreach ($groupLayout['rows'] as $rk => &$rowLayout) {

						$rowWidth = 0;
						$continue = FALSE;
						foreach ($rowLayout as $columnName => $columnType) {
							$definedColumnWidth = koFormLayoutEditor::getColspan($columnType);
							if (in_array($columnName, $columns) || substr($columnName, 0, 1) == '_') {
								$rowWidth += $definedColumnWidth;
							} else {
								unset($formLayout[$gk]['rows'][$rk][$columnName]);
								if (sizeof($formLayout[$gk]['rows'][$rk]) == 0) {
									unset($formLayout[$gk]['rows'][$rk]);
									$continue = TRUE;
								}
							}
						}
						if (!$continue) {
							$rowSize = sizeof($formLayout[$gk]['rows'][$rk]);
							$cc = 0;
							$differenceToFull = $BOOTSTRAP_COLS_PER_ROW;
							foreach ($formLayout[$gk]['rows'][$rk] as $columnName => $columnType) {
								$definedColumnWidth = koFormLayoutEditor::getColspan($columnType);
								if ($rowWidth < $BOOTSTRAP_COLS_PER_ROW || !$columnType) {
									if ($cc < $rowSize - 1) {
										$columnWidth = round($BOOTSTRAP_COLS_PER_ROW / $rowSize);
										$differenceToFull -= $columnWidth;
									} else {
										if ($definedColumnWidth) $columnWidth = min($differenceToFull, $definedColumnWidth);
										else $columnWidth = $differenceToFull;
									}
								} else {
									$columnWidth = $definedColumnWidth;
								}
								$colInfo[$columnName]['columnWidth'] = $columnWidth;
								$group[$tc]['groups'][$gc]["row"][$rc]["inputs"][$cc] = $colInfo[$columnName];
								unset($colInfo[$columnName]);
								$cc++;
							}
							$rc++;
						}
					}

					if ($tk == 'general' && $gk == 'general') {
						$generalRc = $rc;
					}

					$gc++;
					$rc = 0;
				}
			} else {
				foreach ($formLayout as $tk => &$tabLayout) {
					if (substr($tk, 0, 1) == '_') continue;

					$group[$tc]['titel'] = getLL("kota_group_title_{$table}_{$tk}");
					if ($tabLayout['title']) {
						if (substr($tabLayout['title'], 0, 4) == 'FCN:') {
							call_user_func_array(substr($tabLayout['title'], 4), array(&$group[$tc]['titel'], $ids[0]));
						} else if (strpos($tabLayout['title'], '@VALUE@') !== FALSE) {
							eval('$tabLayout[\'title\'] = ' . str_replace(array('@VALUE@', '@ID@'), array($group[$tc]['titel'], $ids[0]), array($tabLayout['title'])));
						} else {
							$group[$tc]['titel'] = $tabLayout['title'];
						}
					}
					$group[$tc]['name'] = $tk;

					foreach ($tabLayout['groups'] as $gk => &$groupLayout) {
						if ($tk == 'general' && $gk == 'general') {
							$generalTc = $tc;
							$generalGc = $gc;
						}

						$group[$tc]['groups'][$gc]['group'] = $groupLayout['group'];
						$group[$tc]['groups'][$gc]['appearance'] = $groupLayout['appearance'];
						$group[$tc]['groups'][$gc]['show_save'] = $groupLayout['show_save'] ? TRUE : FALSE;
						$group[$tc]['groups'][$gc]['titel'] = getLL("kota_group_title_{$table}_{$tk}_{$gk}");
						$group[$tc]['groups'][$gc]['name'] = $gk;

						foreach ($groupLayout['rows'] as $rk => &$rowLayout) {
							$rowWidth = 0;
							$continue = FALSE;
							foreach ($rowLayout as $columnName => $columnType) {
								$definedColumnWidth = koFormLayoutEditor::getColspan($columnType);
								if (in_array($columnName, $columns) || substr($columnName, 0, 1) == '_') {
									$rowWidth += $definedColumnWidth;
								}
								else {
									unset($rowLayout[$columnName]);
									if (sizeof($rowLayout) == 0) {
										unset($rowLayout);
										$continue = TRUE;
									}
								}
							}
							if (!$continue) {
								$rowSize = sizeof($rowLayout);
								$cc = 0;
								$differenceToFull = $BOOTSTRAP_COLS_PER_ROW;
								foreach ($rowLayout as $columnName => $columnType) {
									$definedColumnWidth = koFormLayoutEditor::getColspan($columnType);
									if ($rowWidth < $BOOTSTRAP_COLS_PER_ROW || !$columnType) {
										if ($cc < $rowSize - 1) {
											$columnWidth = round($BOOTSTRAP_COLS_PER_ROW / $rowSize);
											$differenceToFull -= $columnWidth;
										} else {
											if ($definedColumnWidth) $columnWidth = min($differenceToFull, $definedColumnWidth);
											else $columnWidth = $differenceToFull;
										}
									} else {
										$columnWidth = $definedColumnWidth;
									}

									if (substr($columnName, 0, 5) == '_save') {
										$v = array(
											'desc' => '',
											'type' => '_save',
										);
									} else if (substr($columnName, 0, 4) == '_sep') {
										$v = array(
											'desc' => '',
											'type' => '_sep',
										);
									} else {
										$v = $colInfo[$columnName];
									}
									$v['columnWidth'] = $columnWidth;

									$group[$tc]['groups'][$gc]["rows"][$rc]["inputs"][$cc] = $v;
									unset($colInfo[$columnName]);
									$cc++;
								}
								$rc++;
							}
						}

						if ($tk == 'general' && $gk == 'general') {
							$generalRc = $rc;
						}

						$gc++;
						$rc = 0;
					}

					$tc++;
					$gc = 0;
				}
			}

			// Make sure additional fields are added to general group in general tab (if we are in single formular mode)
			$tc = $generalTc;
			$gc = $generalGc;
			$rc = $generalRc;


			//Show special_columns like crdate, cruser etc
			if($_SESSION['ses_userid'] != ko_get_guest_id()
				&& $new_entry === FALSE
				&& is_array($KOTA[$table]['_special_cols']) && sizeof($KOTA[$table]['_special_cols']) > 0
			) {
				$crInfo = [];

				$crdate = $row[$KOTA[$table]['_special_cols']['crdate']];
				$cruser = $row[$KOTA[$table]['_special_cols']['cruser']];
				if($cruser > 0) ko_get_login($cruser, $cruserLogin);
				if($crdate && $cruser) {
					$crInfo[] = sprintf(getLL('kota_crinfo_cr'), sqldatetime2datum($crdate), $cruserLogin['login'], $cruser);
				}

				$lastchange = $row[$KOTA[$table]['_special_cols']['lastchange']];
				$lastchange_user = $row[$KOTA[$table]['_special_cols']['lastchange_user']];
				ko_get_login($lastchange_user, $lastchangeLogin);
				if($lastchange && $lastchange_user) {
					$crInfo[] = sprintf(getLL('kota_crinfo_lc'), sqldatetime2datum($lastchange), $lastchangeLogin['login'], $lastchange_user);
				}

				$smarty->assign("tpl_crinfo", implode('<br />', $crInfo));
			}

		}

		// Arrange columns which are not specified in the formLayout
		$cc = 0;
		foreach ($colInfo as $columnName => $columnData) {
			if ($formMode == "formular" && $mode == 'single' && in_array($columnName, $formLayout['_ignore_fields'])) continue;

			if ($columnData['own_row'] && $cc > 0) {
				$cc = 0;
				$rc++;
			}
			if (!$columnData['columnWidth']) {
				if ($columnData['own_row']) {
					$columnData['columnWidth'] = $defaultWidth;
				} else {
					$columnData['columnWidth'] = $defaultCols;
				}
			}
			if ($columnData['columnWidth'] > $BOOTSTRAP_COLS_PER_ROW) {
				$columnData['columnWidth'] = $BOOTSTRAP_COLS_PER_ROW;
			}
			if ($cc + $columnData['columnWidth'] > $BOOTSTRAP_COLS_PER_ROW) {
				$cc = 0;
				$rc++;
			}

			// Check which formular version is set
			if ($return_only_group && $formVersion == "1") {
				$group[$tc]['groups'][$gc]["row"][$rc]["inputs"][$cc] = $columnData;
			} else {
				$group[$tc]['groups'][$gc]["rows"][$rc]["inputs"][$cc] = $columnData;
			}

			$cc += $columnData['columnWidth'];
			if ($cc == $BOOTSTRAP_COLS_PER_ROW || $columnData['new_row'] || $columnData['own_row']) {
				$cc = 0;
				$rc++;
			}
		}

		$new_entry = FALSE;
		$showForAll = FALSE;
		$gc++;

		// Restore KOTA array for this table
		//$KOTA[$table][$col] = $kotaBackup;
	}//while(row)

	if (isset($generalTc)) $tc = $generalTc;
	if($return_only_group) {
		if ($formVersion == "1") {
			return $group[$tc]['groups'];
		} else {
			return $group;
		}
	}

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
	if($legend && $showLegend) {
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
	if ($form_data['special_submit']) {
		$smarty->assign('tpl_special_submit', $form_data['special_submit']);
	}
	$smarty->assign("tpl_cancel", $form_data["cancel"]);
	$smarty->assign("tpl_groups", $group);
	$smarty->assign("tpl_options", $form_data["options"]);
	$smarty->display("ko_formular2.tpl");
}//ko_multiedit_formular()



function kota_get_mandatory_fields_choices_for_sel($table, $ko_guest = FALSE) {
	global $KOTA;

	$mandatoryFields = kota_get_mandatory_fields($table, FALSE, $ko_guest);
	$defaultMandatoryFields = kota_get_default_mandatory_fields($table);

	$values = $descs = $avalues = $adescs = array();
	foreach ($KOTA[$table] as $field => $def) {
		$form = $def['form'];
		$ll = trim(getLL("kota_{$table}_{$field}"));
		if (substr($field, 0, 1) == '_') continue;
		if (!$ll) continue;
		if (!is_array($form)) continue;
		if ($form['dontsave'] || $form['ignore_test']) continue;
		if (in_array($form['type'], array('foreign_table', 'html', 'checkbox', 'switch'))) continue;
		if (in_array($field, $defaultMandatoryFields)) continue;

		$values[] = $field;
		$descs[] = $ll;

		if (in_array($field, $mandatoryFields)) {
			$avalues[] = $field;
			$adescs[] = $ll;
		}
	}

	$kota_field = [
		'desc' => getLL("kota_mandatory_fields_{$table}"),
		'values' => $values,
		'descs' => $descs,
		'avalues' => $avalues,
		'adescs' => $adescs,
		'avalue' => implode(',', $avalues),
		'type' => 'checkboxes',
		'size' => 6,
		'name' => "sel_{$table}_mandatory_fields",
	];

	if($ko_guest === TRUE) {
		$kota_field['desc'].= " (" . getLL("kota_mandatory_fields_guest") . ")";
		$kota_field['name'] = "sel_{$table}_guest_mandatory_fields";
	}

	return $kota_field;
}


function kota_save_mandatory_fields($table, $post, $ko_guest = FALSE) {
	if($ko_guest === FALSE) {
		$post_field = "sel_{$table}_mandatory_fields";
		$setting_field = "kota_{$table}_mandatory_fields";
	} else {
		$post_field = "sel_{$table}_guest_mandatory_fields";
		$setting_field = "kota_{$table}_guest_mandatory_fields";
	}

	$mandatoryFields = format_userinput($post[$post_field], 'text');
	ko_set_setting($setting_field, $mandatoryFields);
}


function kota_get_default_mandatory_fields($table) {
	global $KOTA;

	$result = array();
	foreach ($KOTA[$table] as $field => $def) {
		if (substr($field, 0, 1) != '_' && is_array($def) && $def['form']['mandatory']) {
			$result[] = $field;
		}
	}

	return $result;
}


function kota_get_mandatory_fields($table, $includeDefault=TRUE, $ko_guest = FALSE) {
	if($ko_guest === FALSE) {
		$setting_field = "kota_{$table}_mandatory_fields";
	} else {
		$setting_field = "kota_{$table}_guest_mandatory_fields";
	}

	$mandatoryFields = explode(',', ko_get_setting($setting_field));

	foreach ($mandatoryFields as $k => $v) {
		if (!$v) {
			unset($mandatoryFields[$k]);
		}
	}
	if ($includeDefault) {
		$mandatoryFields = array_merge($mandatoryFields,  kota_get_default_mandatory_fields($table));
	}
	return $mandatoryFields;
}


function kota_field_is_mandatory($table, $col, $mode='form') {
	if (!$col) return FALSE;

	if ($_SESSION['ses_userid'] == ko_get_guest_id() &&
		!empty(ko_get_setting("kota_{$table}_guest_mandatory_fields"))
	) {
		$ko_guest_mandatory = TRUE;
	} else {
		$ko_guest_mandatory = FALSE;
	}

	$mandatoryFields = kota_get_mandatory_fields($table, TRUE, $ko_guest_mandatory);
	return in_array($col, $mandatoryFields);
}


function kota_check_all_mandatory_fields($post) {
	$data = array();
	list($table, $cols, $id, $hash) = explode('@', $post['id']);
	foreach ($post['koi'][$table] as $col => $entries) {
		foreach ($entries as $id => $value) {
			$data[$id][$col] = $value;
		}
	}

	$ok = TRUE;
	foreach ($data as $id => $entries) {
		$ok = $ok && kota_check_mandatory_fields($table, $id, $entries);
	}

	return $ok;
}


function kota_check_mandatory_fields($table, $id, $data) {
	if ($_SESSION['ses_userid'] == ko_get_guest_id() &&
		!empty(ko_get_setting("kota_{$table}_guest_mandatory_fields"))
	) {
		$ko_guest_mandatory = TRUE;
	} else {
		$ko_guest_mandatory = FALSE;
	}

	$mandatoryFields = kota_get_mandatory_fields($table, TRUE, $ko_guest_mandatory);

	$ok = TRUE;
	foreach ($mandatoryFields as $mandatoryField) {
		if (array_key_exists($mandatoryField, $data)) {
			$value = $data[$mandatoryField];
			if ($value == '0000-00-00' or $value == '0000-00-00 00:00:00') $value = '';
			if (!$value) {
				$ok = FALSE;
				break;
			}
		}
	}
	return $ok;
}


function kota_check_mandatory_field($table, $id, $col, $value, $mode='form') {
	if (!kota_field_is_mandatory($table, $col, $mode)) return TRUE;
	if ($value == '0000-00-00' or $value == '0000-00-00 00:00:00') $value = '';
	if (!$value) {
		return FALSE;
	} else {
		return TRUE;
	}
}





/**
 * Get addresses from ko_leute for a KOTA field of type peoplesearch
 * @param array Array of currently selected IDs
 * @param boolean Set to true to return addresses ordered by name (default).
                  Set to false to return the addresses in the order of the given IDs
 * @return array Three arrays are returned holding the IDs and labels to be used as options for a select
 */
function kota_peopleselect($ids, $sort=TRUE) {
	$avalues = $adescs = $astatus = [];

	//get people from db
	$order = $sort ? 'ORDER BY nachname,vorname ASC' : '';
	$_leute_rows = db_select_data("ko_leute", "WHERE `id` IN ('" . implode("','", $ids) . "')", "id,vorname,nachname,firm,department,hidden,deleted", $order);

	if (!$sort) {
		//Keep order of ids as given in array
		foreach ($ids as $id) $leute_rows[$id] = $_leute_rows[$id];
	} else {
		$leute_rows = $_leute_rows;
	}

	foreach ($leute_rows as $leute_row) {
		if ($leute_row["nachname"]) {
			$value = $leute_row["vorname"] . " " . $leute_row["nachname"];
		} elseif ($leute_row["firm"]) {
			$value = $leute_row["firm"] . " (" . $leute_row["department"] . ")";
		} else {
			$value = "";
		}

		$avalues[] = $leute_row["id"];
		$adescs[] = $value;
		$astatus[] = ($leute_row['deleted'] == 1 ? "deleted" : ($leute_row['hidden'] == 1 ? "hidden" : "active"));
	}

	return array($avalues, $adescs, $astatus);
}//kota_peopleselect()


/**
 * Get addresses from ko_leute for a KOTA field of type peoplesearch
 * @param array Array of currently selected IDs
 * @param boolean Set to true to return addresses ordered by name (default).
Set to false to return the addresses in the order of the given IDs
 * @return array Two arrays are returned holding the IDs and labels to be used as options for a select
 */
function kota_groupselect($ids) {
	if (!is_array($ids)) {
		$ids = explode(',', $ids);
	}

	$result = array();
	foreach ($ids as $id) {
		$id = trim($id);
		if (!$id) continue;

		if (strlen($id) < 6) $id = zerofill($id, 6);
		if (strpos(':', $id) !== FALSE) {
			$roleId = '';
			$parts = explode(':', $id);
			if (strpos($id, ':r') !== FALSE) {
				$roleId = array_pop($parts);
			}
			$groupId = array_pop($parts);
			if (strpos($groupId, 'g') === FALSE) $groupId = 'g'.zerofill($groupId, 6);
			$id = $groupId;
			if ($roleId) {
				if (strpos($roleId, 'r') === FALSE) $roleId = 'g'.zerofill($roleId, 6);
				$id .= ':' . $roleId;
			}
		}

		$fullGid = ko_groups_decode($id, 'full_gid');
		$fullGroupDesc = ko_groups_decode($fullGid, 'group_desc_full');
		$groupName = ko_groups_decode($fullGid, 'group_desc');
		if (strpos(':', $id) !== FALSE) {
			list($groupName, $roleName) = explode(':', $groupName);
			$groupName = "{$groupName} ({$roleName})";
		}

		$result[] = array('id' => $id, 'title' => $fullGroupDesc, 'name' => $groupName);
	}

	return $result;
}//kota_peopleselect()





function kota_get_select_descs_assoc($table, $field) {
	global $KOTA;

	if (!is_array($KOTA[$table])) ko_include_kota(array($table));
	$f = $KOTA[$table][$field]['form'];
	$r = array();
	foreach ($f['values'] as $i => $v) {
		$r[$v] = $f['descs'][$i];
	}
	return $r;
}







/**
 * Liefert eine Liste aller (oder wenn id definiert ist nur diesen Eintrag) zu moderierenden Mutationen (aus Tabelle ko_leute_mod)
 */
function ko_get_donations_mod(&$r, $id="") {
	$r = array();
	$z_where  = "WHERE 1=1";
	if ($id) $z_where .= " AND `id` = " . $id;
	$query = "SELECT * FROM `ko_donations_mod` $z_where ORDER BY _crdate DESC, `date` ASC";
	$result = mysqli_query(db_get_link(), $query);
	while($row = mysqli_fetch_assoc($result)) {
		$r[$row["id"]] = $row;
	}
	if ($id) $r = $r[$id];
}//ko_get_mod_leute()



function ko_donations_get_refnumber($accountId, $personId, $doCrm=FALSE, $crmContactId=NULL) {
	global $PLUGINS;

	ko_get_person_by_id($personId, $p, TRUE);
	$account = db_select_data('ko_donations_accounts', "WHERE `id` = {$accountId}", '*', '', '', TRUE);

	foreach ($PLUGINS as $plugin) {
		if (function_exists("my_donations_refno_{$plugin['name']}")) {
			return call_user_func("my_donations_refno_{$plugin['name']}", array($account, $p));
		}
	}

	if($doCrm) {
		if($crmContactId) {
			$crm_id = intval($crmContactId);
		} else {
			$crm_id = 'X';
		}
	} else {
		$crm_id = '0';
	}

	$refnumber = '999000' . zerofill($crm_id, 6) . zerofill($account['id'], 6) . zerofill($p['id'], 8);
	$refnumber = $refnumber . ko_vesr_modulo10($refnumber);

	return ko_nice_refnr($refnumber);
}




/************************************************************************************************************************
 *                                                                                                                      *
 * D B - F U N K T I O N E N                                                                                            *
 *                                                                                                                      *
 ************************************************************************************************************************/


/**
 * returns the current link to the database, needed for mysqli actions
 * @return mysqli
 */
function db_get_link() {
	global $db_conn;
	return $db_conn;
}


/**
	* Get the enum values of a db column
	*
	* @param string Table where the enum column is defined
	* @param string Column to get the enum values from
	* @return array All the enum values as array
	*/
function db_get_enums($table, $col) {
	global $DEBUG_db, $KOTA;

	if(isset($GLOBALS["kOOL"]["db_enum"][$table][$col])) {
		return $GLOBALS["kOOL"]["db_enum"][$table][$col];
	}

	$query = "SHOW COLUMNS FROM $table LIKE '$col'";
	if(DEBUG_SELECT) $time_start = microtime(TRUE);
	$result = mysqli_query(db_get_link(), $query);
	if($result === FALSE) trigger_error('DB ERROR (db_get_enums): '.mysqli_errno(db_get_link()).': '.mysqli_error(db_get_link()).', QUERY: '.$query, E_USER_ERROR);
	if(DEBUG_SELECT) {
		$DEBUG_db->queryCount++;
		$DEBUG_db->queries[] = array('time' => (microtime(TRUE)-$time_start)*1000, 'sql' => $query);
	}
	if(mysqli_num_rows($result)>0){
		$row=mysqli_fetch_row($result);
		if (!preg_match('/(enum|set) *\(/', $row[1])) {
			if (!is_array($KOTA[$table])) ko_include_kota(array($table));
			$options = $KOTA[$table][$col]['form']['values'];
			ko_log('column_not_enum', "db column is not enum anymore: {$table} -> {$col} - used definitions in KOTA instead");
		} else {
			$options = explode("','", preg_replace("/(enum|set)\('(.+?)'\)/","\\2",$row[1]));
		}
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
	global $DEBUG_db;

	$r = array();

	//Get value from global cache array if already set
	if($field != "" && isset($GLOBALS["kOOL"]["db_columns"][$table][$field])) {
		return $GLOBALS["kOOL"]["db_columns"][$table][$field];
	}

	if($field != "") $like = "LIKE '$field'";
	else $like = "";

	$query = "SHOW COLUMNS FROM $table $like";
	if(DEBUG_SELECT) $time_start = microtime(TRUE);
	$result = mysqli_query(db_get_link(), $query);
	if($result === FALSE) trigger_error('DB ERROR (db_get_columns): '.mysqli_errno(db_get_link()).': '.mysqli_error(db_get_link()).', QUERY: '.$query, E_USER_ERROR);
	if(DEBUG_SELECT) {
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
	global $DEBUG_db;

	if($field == '') $field = 'id';
	$query = "SELECT COUNT(`$field`) as count FROM `$table` ".(($z_where)?" WHERE 1=1 $z_where":"");
	if(DEBUG_SELECT) $time_start = microtime(TRUE);
	$result = mysqli_query(db_get_link(), $query);
	if($result === FALSE) trigger_error('DB ERROR (db_get_count): '.mysqli_errno(db_get_link()).': '.mysqli_error(db_get_link()).', QUERY: '.$query, E_USER_ERROR);
	if(DEBUG_SELECT) {
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
	$query = "SHOW TABLE STATUS LIKE '$table'";
	$result = mysqli_query(db_get_link(), $query);
	if($result === FALSE) trigger_error('DB ERROR (db_get_next_id): '.mysqli_errno(db_get_link()).': '.mysqli_error(db_get_link()).', QUERY: '.$query, E_USER_ERROR);
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
	global $DEBUG_db;

	$columnsTemp = db_get_columns($table);
	$columns = array();
	$unset = array();

	foreach ($columnsTemp as $column) {
		$columns[] = $column['Field'];
	}

	foreach ($data as $field => $value) {
		if (!in_array($field, $columns) || is_object($value)) {
			$unset[$field] = $data[$field];
			unset($data[$field]);
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
	$query1 = $query2 = '';
	//Alle Daten setzen
	foreach($data as $key => $value) {
		$query1 .= "`$key`, ";
		if((string)$value == "NULL") {
			$query2 .= "NULL, ";
		} else {
			$query2 .= "'" . mysqli_real_escape_string(db_get_link(), $value) . "', ";
		}
	}
	$query .= "(" . substr($query1, 0, -2) . ") VALUES (" . substr($query2, 0, -2) . ")";

	if(DEBUG_INSERT) $time_start = microtime(TRUE);
	$result = mysqli_query(db_get_link(), $query);
	if($result === FALSE) trigger_error('DB ERROR (db_insert_data): '.mysqli_errno(db_get_link()).': '.mysqli_error(db_get_link()).', QUERY: '.$query, E_USER_ERROR);
	if(DEBUG_INSERT) {
		$DEBUG_db->queryCount++;
		$DEBUG_db->queries[] = array('time' => (microtime(TRUE)-$time_start)*1000, 'sql' => $query);
	}
	return mysqli_insert_id(db_get_link());
}//db_insert_data()


/**
 * Wrapper to insert more than 1 row into databse
 *
 * @param string Table where the data should be inserted
 * @param array Data Indexed array with the keys beeing the name of the db columns
 * @return array ids of the newly inserted rows
 */
function db_insert_data_multiple($table, $data_array) {
	$ids = [];

	if(!is_array($data_array[0])) {
		$data_array[0] = $data_array;
	}

	foreach($data_array AS $data) {
		$ids[] = db_insert_data($table, $data);
	}

	return $ids;
}


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
	global $DEBUG_db;

	$columnsTemp = db_get_columns($table);
	$columns = array();
	$unset = array();

	foreach ($columnsTemp as $column) {
		$columns[] = $column['Field'];
	}

	foreach (array_keys($data) as $field) {
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
	foreach($data as $key => &$value) {
		if(!$key) continue;
		$found = TRUE;
		if((string)$value == "NULL") {
			$query .= "`$key` = NULL, ";
		} else {
			$query .= "`$key` = '".mysqli_real_escape_string(db_get_link(), $value)."', ";
		}
	}
	if(!$found) return FALSE;
	$query = substr($query, 0, -2);

	//WHERE-Bedingung
	$query .= " $where ";

	if(DEBUG_UPDATE) $time_start = microtime(TRUE);
	$result = mysqli_query(db_get_link(), $query);
	if($result === FALSE) trigger_error('DB ERROR (db_update_data): '.mysqli_errno(db_get_link()).': '.mysqli_error(db_get_link()).", QUERY: $query", E_USER_ERROR);
	if(DEBUG_UPDATE) {
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
	global $DEBUG_db;

	$query = "DELETE FROM $table $where";
	if(DEBUG_DELETE) $time_start = microtime(TRUE);
	$result = mysqli_query(db_get_link(), $query);
	if($result === FALSE) trigger_error('DB ERROR (db_delete_data): '.mysqli_errno(db_get_link()).': '.mysqli_error(db_get_link()).", QUERY: $query", E_USER_ERROR);
	if(DEBUG_DELETE) {
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
	global $DEBUG_db;

	if(ko_test(__FUNCTION__, func_get_args(), $testreturn) === TRUE) return $testreturn;

	//Spalten
	if(is_array($columns)) {
		foreach($columns as $col_i => $col) $columns[$col_i] = "`".$col."`";
		$columns = implode(",", $columns);
	}

	$query = "SELECT $columns FROM $table $where $order $limit";
	if(DEBUG_SELECT) $time_start = microtime(TRUE);
	$result = mysqli_query(db_get_link(), $query);
	if($result === FALSE) trigger_error('DB ERROR (db_select_data): '.mysqli_errno(db_get_link()).': '.mysqli_error(db_get_link()). ' QUERY: '.$query, E_USER_ERROR);
	if(DEBUG_SELECT) {
		$DEBUG_db->queryCount++;
		$DEBUG_db->queries[] = array('time' => (microtime(TRUE)-$time_start)*1000, 'sql' => $query);
	}
	if(mysqli_num_rows($result) == 0) {
		return $single ? null : [];
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
			if($index && isset($row[$index])) {
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
	// TODO: support testing
	$result = mysqli_query(db_get_link(), $query);
	if($result === FALSE) trigger_error('DB ERROR (db_select_data): '.mysqli_errno(db_get_link()).': '.mysqli_error(db_get_link()). ' QUERY: '.$query, E_USER_ERROR);
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
	* @param string Split character for implode function on return
	* @return mixed Value from database
	*/
function db_get_column($table, $where, $column, $split=" ") {
	if(is_numeric($where)) {
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
	global $DEBUG_db;

	$r = array();

	$col = str_replace('`', '', $col);
	if(FALSE === strpos($col, '(')) $col = '`'.$col.'`';

	$order = $order_ ? $order_ : "ORDER BY $col ASC";

	if($case_sensitive) $query = "SELECT DISTINCT BINARY $col AS '".str_replace('`', '', $col)."' FROM $table $where $order";
	else $query = "SELECT DISTINCT $col FROM $table $where $order";
	if(DEBUG_SELECT) $time_start = microtime(TRUE);
	$result = mysqli_query(db_get_link(), $query);
	if($result === FALSE) trigger_error('DB ERROR (db_select_distinct): '.mysqli_errno(db_get_link()).': '.mysqli_error(db_get_link()).', QUERY: '.$query, E_USER_ERROR);
	if(DEBUG_SELECT) {
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
	$query = "ALTER TABLE `$table` $change";
	$result = mysqli_query(db_get_link(), $query);
	if($result === FALSE) trigger_error('DB ERROR (db_alter_table): '.mysqli_errno(db_get_link()).': '.mysqli_error(db_get_link()).', QUERY: '.$query, E_USER_ERROR);
}//db_alter_table()


/**
  * Parses an SQL string and updates the kOOL-database accordingly
	*
	* Used in /install/index.php and the plugins
	*
	* @param string SQL statement of the entry to be
	*/
function db_import_sql($tobe) {
	$create_code = 'CREATE TABLE `%s` (%s) %s';
	$alter_code  = 'ALTER TABLE `%s` CHANGE `%s` %s';
	$add_code    = 'ALTER TABLE `%s` ADD %s';

	//find tables in actual db
	$is_tables = NULL;
	$result = mysqli_query(db_get_link(), "SHOW TABLES");
	while($row = mysqli_fetch_row($result)) {
		$is_tables[] = $row[0];
	}

	$table = "";
	foreach(explode("\n", $tobe) as $line) {
		$line = trim($line);

		//don't allow any destructive commands
		if(strstr(strtoupper($line), "DROP ") || strstr(strtoupper($line), "TRUNCATE ") || strstr(strtoupper($line), "DELETE ")) {
			continue;
		}

		//INSERT Statement
		if(strtoupper(substr($line, 0, 11)) == "INSERT INTO") {
			$do_sql[] = $line;
			continue;
		}

		//UPDATE Statement
		if(strtoupper(substr($line, 0, 7)) == "UPDATE ") {
			$do_sql[] = $line;
			continue;
		}

		//ALTER Statement
		if(strtoupper(substr($line, 0, 6)) == "ALTER ") {
		  $do_sql[] = $line;
		  continue;
		}

		//start of a create table statement
		if(strtoupper(substr($line, 0, 12)) == "CREATE TABLE") {
			//find table-name to be edited
			$temp = explode(" ", $line);
			$table = str_replace("`", "", trim($temp[2]));

			//find table in current db
			if(in_array($table, $is_tables)) {
				//table already exists - get table create definition
				$result = mysqli_query(db_get_link(), "SHOW CREATE TABLE $table");
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
				$table_options = rtrim(trim(substr($line,1)),';');
				$do_sql[] = sprintf($create_code, $table, $new_table_sql, $table_options);
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
			$result = mysqli_query(db_get_link(), $query);
			if($result === FALSE) {
				trigger_error('DB ERROR (db_import_sql) for query "'.$query.'": '.mysqli_errno(db_get_link()).': '.mysqli_error(db_get_link()), E_USER_ERROR);
			}
		}
	}
}//db_import_sql()




/**
 * Performs a fuzzy search in a db table
 * The search is performed by concatenating the db field values to a single string
 * and calculating the Levenshtein difference.
 * The best match is returned if it is in the given limit.
 *
 * @param array $data array with column names as indizes
 * @param string $table DB table
 * @param int $error Maximum allowed errors per db column
 * @param boolean $case Set to false to ignore the case
 * @param int $lev_limit Levenshtein limit which must be reached to treat the best find as a valuable find
 *
 * @return array IDs of db entries with the best levenshtein difference
 */
function ko_fuzzy_search($data, $table, $error=1, $case=FALSE, $lev_limit=0 ) {
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
	if($lev_limit == 0) {
		$lev_limit = $num_cols*$error-1;
	}

	//Only get db entries matching the total string length (+/- limit) of the original data
	$query  = "SELECT id, CONCAT(`".implode("`, `", $cols)."`) as teststring FROM `$table` ";
	$query .= "WHERE (CHAR_LENGTH(`".implode("`)+CHAR_LENGTH(`", $cols)."`)) > ".($orig_length-$limit)." ";
	$query .= "AND (CHAR_LENGTH(`".implode("`)+CHAR_LENGTH(`", $cols)."`)) < ".($orig_length+$limit)." ";
	$query .= "AND deleted = '0'";

	//Find the best matching db entry
	$result = mysqli_query(db_get_link(), $query);
	$best = 100;
	while($row = mysqli_fetch_assoc($result)) {
		if($case) {
			$lev = levenshtein($orig, $row["teststring"]);
		} else {
			$lev = levenshtein(strtolower($orig), strtolower($row["teststring"]));
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



function ko_fuzzy_search_2($data, $table, $minScore=1, $nResults=3) {
	$cols = array_keys($data);
	$search = implode(' ', $data);

	if (sizeof($data) == 0) return FALSE;

	$wheres = $selects = $having = array();
	foreach ($data as $col => $value) {
		$value = mysqli_real_escape_string(db_get_link(), $value);
		$indexExists = db_query("SELECT COUNT(1) IndexIsThere FROM INFORMATION_SCHEMA.STATISTICS WHERE table_schema=DATABASE() AND table_name='{$table}' AND index_name='fulltext_{$col}'");
		if (!$indexExists[0]['IndexIsThere']) db_query("ALTER TABLE `{$table}` ADD FULLTEXT fulltext_{$col}(`{$col}`)");
		$valueExpl = explode(' ', $value);
		$selects[] = "MATCH(`{$col}`) AGAINST ('{$value}') as `_fuzzy_relev_{$col}_`";
		$wheres[] = "MATCH(`{$col}`) AGAINST('".implode(' ', array_map(function($el){return ''.$el;},$valueExpl))."' IN NATURAL LANGUAGE MODE)";
		$having[] = "`_fuzzy_relev_{$col}_`";
	}
	$orderBy = "(".implode(" + ", $having).")";
	$having = "((" . implode(" + ", $having) . ")/".sizeof($having).")";

	if (sizeof($wheres) == 0) {
		return FALSE;
	}

	$query = "SELECT *, " . implode(", ", $selects) . " FROM `{$table}` WHERE `deleted` = '0' AND (" . implode(" + ", $wheres) . ") > ".number_format(sizeof($wheres) * 0.55, 2, '.', '')." HAVING {$having} > {$minScore} ORDER BY {$orderBy} DESC LIMIT {$nResults}";
	$el = db_query($query);

	if (!$el || sizeof($el) == 0) {
		return FALSE;
	}
	else {
		return $el;
	}
}



/**
 * Search for multiple keywords provided as Array[id,name] in a String and return best result,
 * if it got more points then limit requires.
 *
 * @param String $string to search for keywords
 * @param Array[id,name] $keyword_groups list of keywords to search in $string
 * @param Int $limit which must be reached to return best result
 *
 * @return Int|bool On success return Id of record, otherwise FALSE
 */
function ko_fuzzy_search_in_string($string, $keyword_groups, $limit = 20) {
	if (!is_array($keyword_groups)) {
		return FALSE;
	}

	$keyword_groups = array_values($keyword_groups);
	$best_result['points'] = 0;

	foreach($keyword_groups AS $id => $group) {
		$points = 0;
		$keywords = explode(' ', $group['name']);
		foreach($keywords AS $keyword) {
			similar_text(strtolower($string), strtolower($keyword), $similarity);
			$points+= $similarity;
			// when the keyword is exactly in comment, we give bonus
			if(stristr($string, $keyword)) {
				$points+= 30;
			}
		}

		$group['points'] = ($points / count($keywords));

		if ($best_result['points'] < $group['points']) {
			$best_result = $group;
		}
	}

	if($best_result['points'] > $limit) {
		return $best_result['id'];
	} else {
		return FALSE;
	}
}


/**
 *
 *
 * @param array $person Main Person
 * @param array $allPersons
 * @param array $doneFamilies List with famids, already handled. will be filled in this function
 * @param array $pidsByFamid Person-Ids grouped by Famid
 * @param array $families Result from ko_get_familien()
 * @param array $exportCols ko_leute db fields to export
 * @param String $exportMode keys according to sel_auswahl in submenu "Aktionen" -> "Zeilen"
 * @param Null $forceFamilyFirstname
 * @return array
 */
function ko_leute_process_person_for_export(&$person, &$allPersons, &$doneFamilies, &$pidsByFamid, &$families, $exportCols, $exportMode, $forceFamilyFirstname=NULL) {
	global $LEUTE_DEFAULT_HOUSEHOLD_EMAIL;

	$isFam = FALSE;

	if ($forceFamilyFirstname !== NULL) {
		$lfff = $forceFamilyFirstname;
	} else {
		$lfff = ko_get_userpref($_SESSION['ses_userid'], 'leute_force_family_firstname');
	}

	if( $person["famid"] && (
			($exportMode == "f")
				||
			($exportMode == "Def" && ($families[$person["famid"]]["famgembrief"] == "ja" || !isset($families[$person["famid"]]["famgembrief"])))
				||
			($exportMode == "Fam2")
		)
	) {
		$isFam = TRUE;

		if($doneFamilies[$person["famid"]]) {
			return array(FALSE, $isFam);
		}

		$famFunctions = array();
		$lastNames = array();
		foreach ($pidsByFamid[$person['famid']] as $famMember) {
			$famFunctions[$allPersons[$famMember]['famfunction']] = $famMember;
			$lastNames[$allPersons[$famMember]['famfunction']] = $allPersons[$famMember]['nachname'];
		}
		if ( // ehepaar export
			$famFunctions['husband'] &&
			$famFunctions['wife'] &&
			(sizeof($pidsByFamid[$person['famid']]) == 2 || (sizeof($pidsByFamid[$person['famid']]) > 2 && $lfff == 1)) &&
			in_array("vorname", $exportCols) &&
			in_array("nachname", $exportCols) &&
			$lastNames['husband'] != $lastNames['wife'] &&
			$families[$person['famid']]['famfirstname'] == '' &&
			$families[$person['famid']]['famlastname'] == ''
		) {
			//find which field comes first: vorname or nachname
			$field1 = $field2 = '';
			foreach($exportCols as $xc) {
				if(in_array($xc, array('vorname', 'nachname'))) {
					if($field1) $field2 = $xc;
					else $field1 = $xc;
				}
			}
			// set anrede to ''
			if (in_array('anrede', $exportCols)) {
				if ($families[$person['famid']]['famanrede'] == '') {
					if (sizeof($pidsByFamid[$person['famid']]) == 2) {
						$person['anrede'] = '';
						$person[$field1] = getLL('leute_salutation_m') . ' '; // set prefix of husband's name to Mr.
						$person[$field2] = getLL('leute_salutation_w') . ' '; // set prefix of wifes name to Mrs.
					} else {
						$person['anrede'] = getLL('ko_leute_anrede_family');
						$person[$field1] = '';
						$person[$field2] = '';
					}
				}
				else {
					$person['anrede'] = $families[$person['famid']]['famanrede'];
					$person[$field1] = '';
					$person[$field2] = '';
				}
			} else {
				$person[$field1] = '';
				$person[$field2] = '';
			}
			//Add firstnames if setting is given. Add them in front if field1 is firstname (so firstname comes before lastname in list of columns)
			if($lfff > 0 && $field1 == 'vorname') {
				$person[$field1] .= $allPersons[$famFunctions['husband']]['vorname'] . ' ';
				$person[$field2] .= $allPersons[$famFunctions['wife']]['vorname'] . ' ';
			}
			$person[$field1] .= $allPersons[$famFunctions['husband']]['nachname'];
			$person[$field2] .= $allPersons[$famFunctions['wife']]['nachname'];
			//Add firstnames if setting is given. Add them last if field2 is firstname (so firstname comes after lastname in list of columns)
			if($lfff > 0 && $field2 == 'vorname') {
				$person[$field1] .= ' '.$allPersons[$famFunctions['husband']]['vorname'] . ' ';
				$person[$field2] .= ' '.$allPersons[$famFunctions['wife']]['vorname'] . ' ';
			}
			$person[$field1] = trim($person[$field1]).' '.getLL('and');
			$person[$field2] = trim($person[$field2]);
		}
		else { // not ehepaar export
			if(in_array('anrede', $exportCols)) {
				//Get family salutation from family data (if set)
				if($families[$person['famid']]['famanrede']) {
					$person['anrede'] = $families[$person['famid']]['famanrede'];
				} else {
					//Use generic salutation (depending on members in list)
					$child = FALSE;
					foreach($pidsByFamid[$person['famid']] as $member_id) {
						if(!in_array($allPersons[$member_id]['famfunction'], array('husband', 'wife'))) $child = TRUE;
					}
					if(sizeof($pidsByFamid[$person['famid']]) > 1) {
						if($child) $person['anrede'] = getLL('ko_leute_anrede_family');
						else $person['anrede'] = getLL('ko_leute_anrede_family_no_children');
					}
				}
				//$person["anrede"] = $families[$person["famid"]]["famanrede"] ? $families[$person["famid"]]["famanrede"] : getLL("ko_leute_anrede_family");
			}//anrede
			if(in_array("vorname", $exportCols)) {
				//If no special family values are given, set first name to empty ("Fam", "", "Lastname")
				if(!$families[$person["famid"]]["famanrede"] && !$families[$person["famid"]]["famfirstname"] && $lfff == 0) { // TODO: removed $families[$person["famid"]]["famlastname"] --- correct??
					$person["vorname"] = "";
				} else {
					if($lfff == 2) {
						//Use first names of all members found in the current list
						$familyMembers = (array)db_select_data('ko_leute', "WHERE `famid` = '".$person['famid']."' AND `famfunction` IN ('husband', 'wife') AND `deleted` = '0'".ko_get_leute_hidden_sql(), 'id,famfunction,vorname', 'ORDER BY famfunction ASC');
						$familyMembers = array_merge($familyMembers, (array)db_select_data('ko_leute', "WHERE `famid` = '".$person['famid']."' AND `famfunction` IN ('child', '') AND `deleted` = '0'".ko_get_leute_hidden_sql(), 'id,famfunction,vorname', 'ORDER BY famfunction DESC, geburtsdatum DESC'));
						$foundMembers = array();
						foreach($familyMembers as $oneMember) {
							if(in_array($oneMember['id'], array_keys($allPersons))) $foundMembers[] = $oneMember['vorname'];
						}
						$person['vorname'] = implode(', ', array_slice($foundMembers, 0, -1)) . (sizeof($foundMembers) > 1 ? ' '.getLL('family_link').' ' : '' ) . end($foundMembers);
					} else {
						if($families[$person["famid"]]["famfirstname"]) {
							$person["vorname"] = $families[$person["famid"]]["famfirstname"];
						} else {
							//use first names of parents for firstname-col
							$parents = db_select_data("ko_leute", "WHERE `famid` = '".$person["famid"]."' AND `famfunction` IN ('husband', 'wife') AND `deleted` = '0'".ko_get_leute_hidden_sql(), "id,famfunction,vorname", "ORDER BY famfunction ASC");
							$parent_values = array();
							foreach($parents as $parent) {
								//Use parents firstnames if parents show up in exported list of addresses or
								//  export mode allef had been selected (forced family export)
								if( (in_array($parent['id'], array_keys($allPersons)) && $parent['vorname']) || ($exportMode == 'f' && $lfff == 1)) {
									$parent_values[] = $parent["vorname"];
								}
							}
							$person['vorname'] = implode(', ', array_slice($parent_values, 0, -1)) . (sizeof($parent_values) > 1 ? ' '.getLL('family_link').' ' : '' ) . end($parent_values);
						}
					}
				}
			}//vorname
			if(in_array("nachname", $exportCols)) { // TODO: case distinction correct?
				if ($families[$person["famid"]]["famlastname"]) {
					$person["nachname"] = $families[$person["famid"]]["famlastname"];
				} else {
					if($lfff == 2) {
						$familyMembers = (array)db_select_data('ko_leute', "WHERE `famid` = '".$person['famid']."' AND `famfunction` IN ('husband', 'wife') AND `deleted` = '0'".ko_get_leute_hidden_sql(), 'id,famfunction,nachname', 'ORDER BY famfunction ASC');
						$familyMembers = array_merge($familyMembers, (array)db_select_data('ko_leute', "WHERE `famid` = '".$person['famid']."' AND `famfunction` IN ('child', '') AND `deleted` = '0'".ko_get_leute_hidden_sql(), 'id,famfunction,nachname', 'ORDER BY famfunction DESC, geburtsdatum DESC'));
						$foundMembers = array();
						$fatherMotherLastNames = array();
						foreach($familyMembers as $oneMember) {
							if(in_array($oneMember['id'], array_keys($allPersons)) && !in_array($oneMember['nachname'], $foundMembers)) {
								if (in_array($oneMember['famfunction'], array('husband', 'wife'))) {
									$fatherMotherLastNames[] = $oneMember['nachname'];
									$foundMembers[] = $oneMember['nachname'];
								} else if (sizeof($fatherMotherLastNames) == 0) {
									$foundMembers[] = $oneMember['nachname'];
								}
							}
						}
						$person["nachname"] = implode((' '), $foundMembers);
					} else {
						$familyMembers = (array)db_select_data('ko_leute', "WHERE `famid` = '".$person['famid']."' AND `famfunction` IN ('husband', 'wife') AND `deleted` = '0'".ko_get_leute_hidden_sql(), 'id,famfunction,nachname', 'ORDER BY famfunction ASC');
						$familyMembers = array_merge($familyMembers, (array)db_select_data('ko_leute', "WHERE `famid` = '".$person['famid']."' AND `famfunction` IN ('child', '') AND `deleted` = '0'".ko_get_leute_hidden_sql(), 'id,famfunction,nachname', 'ORDER BY famfunction DESC, geburtsdatum DESC'));
						$foundMembers = array();
						$fatherMotherLastNames = array();
						foreach($familyMembers as $oneMember) {
							if(in_array($oneMember['id'], array_keys($allPersons)) && !in_array($oneMember['nachname'], $foundMembers)) {
								if (in_array($oneMember['famfunction'], array('husband', 'wife'))) {
									$fatherMotherLastNames[] = $oneMember['nachname'];
									$foundMembers[] = $oneMember['nachname'];
								} else if (sizeof($fatherMotherLastNames) == 0) {
									$foundMembers[] = $oneMember['nachname'];
								}
							}
						}
						$person["nachname"] = implode((' '), $foundMembers);
						//$person["nachname"] = $families[$person["famid"]]["nachname"];
					}
				}
			}//nachname
		}

		if(in_array('email', $exportCols) || in_array($_POST['id'], array('email'))) {
			if($LEUTE_DEFAULT_HOUSEHOLD_EMAIL || $families[$person['famid']]['famemail']) {  //Get family email address if set
				$emailFamfunction = $families[$person['famid']]['famemail'];
				if(!$emailFamfunction) $emailFamfunction = $LEUTE_DEFAULT_HOUSEHOLD_EMAIL;

				$parent = db_select_data('ko_leute', ("WHERE `famid` = '".$person['famid']."' AND `famfunction` = '".$emailFamfunction."' AND `deleted` = '0'".ko_get_leute_hidden_sql()), '*', '', 'LIMIT 0,1', TRUE);
				ko_get_leute_email($parent, $email);
				if($email[0]) $person['email'] = $email[0];
			} else if($person['famfunction'] == 'child') {  //if no family email is set but the person is a child, use the email address of one of the parents
				$parents = db_select_data('ko_leute', ("WHERE `famid` = '".$person['famid']."' AND `famfunction` IN ('husband', 'wife') AND `deleted` = '0'".ko_get_leute_hidden_sql()), '*', 'ORDER BY famfunction ASC');
				$done_parent = FALSE;
				foreach($parents as $parent) {
					ko_get_leute_email($parent, $email);
					if($email[0] && !$done_parent) {
						$person['email'] = $email[0];
						$done_parent = TRUE;
					}
				}
			}
		}//email
		$hookData = array('_es' => $allPersons, '_xls_cols' => $exportCols, 'p' => $person, '_orig_es' => $allPersons, 'cols_no_map' => array());
		ko_leute_get_salutation_for_fam($hookData);
		hook_function_inline('leute_export_fam', $hookData);
		$person = $hookData['p'];
		$cols_no_map = $hookData['cols_no_map'];
		unset($hookData);

		$doneFamilies[$person["famid"]] = TRUE;

		// add salutation
		$hookData = array(
			'p' => &$person,
			'_orig_es' => &$allPersons,
			'_xls_cols' => array('MODULEsalutation_informal', 'MODULEsalutation_formal')
		);
		ko_leute_get_salutation_for_fam($hookData);
		unset($hookData);
	}//if(fam)
	else {
		unset($cols_no_map);

		// add salutation
		kota_listview_salutation_informal($person['MODULEsalutation_informal'], array('dataset' => $person));
		kota_listview_salutation_formal($person['MODULEsalutation_formal'], array('dataset' => $person));
	}

	return array(TRUE, $isFam);
}


/************************************************************************************************************************
 *                                                                                                                      *
 * Export-FUNKTIONEN                                                                                                    *
 *                                                                                                                      *
 ************************************************************************************************************************/

/**
 * Creates an XLSX file
 * Based upon PHPSpreadsheet (https://github.com/PHPOffice/PhpSpreadsheet/)
 *
 * @param array header: Array holding the header row's cells
 * @param array data: Two dimensional array holding the cell's values
 * @param string filename: Filename to use for the xls file
 * @param string title: Title for the worksheet
 * @param string format: landscape or portrait
 * @param array wrap: Array with column number as key if this column's values should be wrapped
 * @param array formatting: Array containing formatting information for Column, Row, Cell, Page
 * @param array linebreak_columns
 * @param array cellTypes
 * @param bool $fitToPage if set, all data will be printed to 1 page
 * @return string the modified filename
 * @throws Exception
 */
function ko_export_to_xlsx($header, $data, $filename, $title = '', $format="landscape", $wrap=array(), $formatting=array(), $linebreak_columns=array(), $cellTypes=array(), $fitToPage = FALSE) {
	global $DATETIME;

	if($title == '') {
		$title = 'kOOL';
	} else {
		$title = format_userinput($title, 'alphanum');
	}

	$person = ko_get_logged_in_person();
	$xls_default_font = ko_get_setting('xls_default_font');
	$name = $person['vorname'] . ' ' . $person['nachname'];

	$spreadsheet = new Spreadsheet();
	$spreadsheet->getProperties()->setCreator(utf8_encode($name));
	$spreadsheet->getProperties()->setLastModifiedBy(utf8_encode($name));
	$spreadsheet->getProperties()->setTitle(utf8_encode($title));
	$spreadsheet->getProperties()->setSubject('kOOL-Export');
	$spreadsheet->getProperties()->setDescription('');

	try {
		$sheet = $spreadsheet->getActiveSheet();
		$sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);

		if ($format == 'landscape') {
			$sheet->getPageSetup()->setOrientation(
				\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE
			);
		} else {
			$sheet->getPageSetup()->setOrientation(
				\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT
			);
		}

		if ($xls_default_font) {
			$sheet->getParent()->getDefaultStyle()->getfont()->setName($xls_default_font);
		} else {
			$sheet->getParent()->getDefaultStyle()->getfont()->setName('Arial');
		}

		switch (ko_get_setting('xls_title_color')) {
			case 'blue':
				$colorName = Color::COLOR_BLUE;
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
				$colorName = Color::COLOR_GREEN;
				break;
			case 'orange':
				$colorName = 'FFFFA500';
				break;
			case 'purple':
				$colorName = 'FF800080';
				break;
			case 'red':
				$colorName = Color::COLOR_RED;
				break;
			case 'yellow':
				$colorName = Color::COLOR_YELLOW;
				break;
			case 'black':
			default:
				$colorName = Color::COLOR_BLACK;
		}

		$xlsHeaderFormat = array(
			'font' => array(
				'bold' => ko_get_setting('xls_title_bold') ? true : false,
				'color' => array('argb' => $colorName),
				'name' => utf8_encode(ko_get_setting('xls_title_font'))
			),
		);

		$xlsTitleFormat = array(
			'font' => array(
				'bold' => ko_get_setting('xls_title_bold') ? true : false,
				'size' => 12,
				'name' => utf8_encode(ko_get_setting('xls_title_font'))
			)
		);

		$xlsSubtitleFormat = array(
			'font' => array(
				'bold' => ko_get_setting('xls_title_bold') ? true : false,
				'name' => utf8_encode(ko_get_setting('xls_title_font'))
			)
		);

		$row = 1;
		$col = 1;
		$manual_linebreaks = false;
		//Add header
		if(is_array($header)) {
			if(isset($header['header'])) {
				//Add title
				if($header['title']) {
					$sheet->getStyleByColumnAndRow(1, $row)->applyFromArray($xlsTitleFormat);
					$sheet->setCellValueByColumnAndRow(1, $row++, utf8_encode($header['title']));
				}
				//Add subtitle
				if(is_array($header['subtitle']) && sizeof($header['subtitle']) > 0) {
					foreach($header['subtitle'] as $k => $v) {
						if(substr($k, -1) != ':') {
							$k .= ':';
						}
						$sheet->getStyleByColumnAndRow(1, $row)->applyFromArray($xlsSubtitleFormat);
						$sheet->setCellValueByColumnAndRow(1, $row, utf8_encode($k));
						$sheet->setCellValueByColumnAndRow(2, $row++, utf8_encode($v));
					}
				} else if($header['subtitle']) {
					$sheet->getStyleByColumnAndRow(1, $row)->applyFromArray($xlsHeaderFormat);
					$sheet->setCellValueByColumnAndRow(1, $row++, utf8_encode((string)$header['subtitle']));
				}
				$row++;
				//Add column headers
				$col = 1;
				foreach($header['header'] as $h) {
					$sheet->getStyleByColumnAndRow($col, $row)->applyFromArray($xlsHeaderFormat);
					$sheet->setCellValueByColumnAndRow($col++, $row, utf8_encode(ko_unhtml($h)));
				}
				$row++;
			} else {
				if(is_array($header[0])) {
					foreach($header as $r) {
						$col = 1;
						foreach($r as $h) {
							$sheet->getStyleByColumnAndRow($col, $row)->applyFromArray($xlsHeaderFormat);
							$sheet->setCellValueByColumnAndRow($col++, $row, utf8_encode(ko_unhtml($h)));
						}
						$row++;
					}
				} else {
					$manual_linebreaks = true;
					foreach($header as $h) {
						$sheet->getStyleByColumnAndRow($col, $row)->applyFromArray($xlsHeaderFormat);
						$sheet->setCellValueByColumnAndRow($col++, $row, utf8_encode(ko_unhtml($h)));
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

		$numberFormatMapper = array(
			'text' => NumberFormat::FORMAT_TEXT,
			'date' => 'dd.mm.yyyy',
			'time' => 'HH:MM',
			'datetime' => 'mm.dd.yyyy HH:MM',
		);

		//Daten
		$first = TRUE;
		foreach($data as $dd) {
			$col=1;
			foreach($dd as $k => $d) {
				if($first) {
					if(is_numeric($formatting['column'][$col]['width'])) {
						$sheet->getColumnDimension(Coordinate::stringFromColumnIndex($col))->setWidth($formatting['column'][$col]['width']);
					} else {
						//Set column width to auto
						$sheet->getColumnDimension(Coordinate::stringFromColumnIndex($col))->setAutoSize(true);
					}
				}

				if($wrap[$col] == TRUE || $formatting['column'][$col]['wrap'] == TRUE) {
					$sheet->getStyleByColumnAndRow($col, $row)->getAlignment()->setWrapText(true);
					$sheet->setCellValueByColumnAndRow($col++, $row, utf8_encode(strip_tags(ko_unhtml($d))));
					$sheet->getRowDimension($row)->setRowHeight(-1); // autoheight on wrap
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
						$sheet->getStyleByColumnAndRow($col, $row)->getFont()->setItalic(false)->setBold(false);
					}
					// set format of cell according to values in $cellTypes
					$addString = '';
					if (isset($cellTypes[$col]) && isset($numberFormatMapper[$cellTypes[$col]])){
						$sheet->getStyleByColumnAndRow($col, $row)->getNumberFormat()->setFormatCode($numberFormatMapper[$cellTypes[$col]]);
						if ($cellTypes[$col] == 'text') $addString = ""; // TODO: add apostrophe here
						else if($cellTypes[$col] == 'date') {
							if($d) {
								try {
                  $cellDate = new DateTime($d);
                  $d = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($cellDate);
                } catch(Exception $e) {
                  $d = $d;
                }
							}
						}
						else if($cellTypes[$col] == 'datetime') {
							if($d) {
								$ptime = strptime($d,$DATETIME['ddmy'].' %H:%M:%S');
								$time = new DateTime;
								$time->setDate($ptime['tm_year']+1900,$ptime['tm_mon']+1,$ptime['tm_mday']);
								$time->setTime($ptime['tm_hour'],$ptime['tm_min'],$ptime['tm_sec']);
								$d = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($time);
							}
						}
						else if($cellTypes[$col] == 'time') {
							if($d) {
								try {
                  $time = new DateTime($d);
                  $d = $time->format('H')/24+$time->format('i')/1440+$time->format('s')/86400;
                } catch(Exception $e) {
                  $d = $d;
                }
							}
						}
					}

					$sheet->setCellValueByColumnAndRow($col++, $row, utf8_encode(strip_tags($addString . ko_unhtml($d))));
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
			$first = FALSE;
		}

		$sheet->getPageSetup()->setFitToPage($fitToPage);

		foreach($formatting["merge"] AS $merge) {
			$sheet->mergeCells($merge);
		}

		foreach($formatting['autoheight'] AS $autoheight) {
			$sheet->getRowDimension($autoheight)->setRowHeight(-1);
		}

		foreach($formatting["custom"] AS $custom_format) {
			$sheet->getStyle($custom_format['range'])->applyFromArray($custom_format['style']);
		}

		if(isset($formatting["page"]["margin"]['top'])) {
			$sheet->getPageMargins()->setTop((float) $formatting["page"]["margin"]['top']);
		}
		if(isset($formatting["page"]["margin"]['right'])) {
			$sheet->getPageMargins()->setRight((float) $formatting["page"]["margin"]['right']);
		}
		if(isset($formatting["page"]["margin"]['bottom'])) {
			$sheet->getPageMargins()->setBottom((float) $formatting["page"]["margin"]['bottom']);
		}
		if(isset($formatting["page"]["margin"]['left'])) {
			$sheet->getPageMargins()->setLeft((float) $formatting["page"]["margin"]['left']);
		}

		$sheet->setTitle(utf8_encode($title));

		if (isset($_SESSION['ses_userid']) && ko_get_userpref($_SESSION['ses_userid'], 'export_table_format') == 'xls') {
			$writer = new Xls($spreadsheet);
			if (substr($filename, -1) == 'x') {
				$filename = substr($filename, 0, -1);
			}
		} else {
			$writer = new Xlsx($spreadsheet);
		}

		$writer->save($filename);
		return $filename;

	} catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
		koNotifier::Instance()->addTextError('Excel error ' . $e->getCode() . ': ' . $e->getMessage());
	}

	return FALSE;
}





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
	global $ko_path, $BASE_PATH;

	require_once($BASE_PATH.'inc/class.excelwriter.php');

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
		$pdf->SetCellBorders(strtoupper($layout['cellBorders']));
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
				$averages[$col] = $num ? $sum/$num : 0;
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
 *
 * @param string $module
 * @param Integer $_size days to be displayed. when empty: value from daten_pdf_week_length
 * @param string $_start date to begin displaying $_size days
 * @param string $pages maximum pages to create in pdf
 * @return string filename of pdf
 */
function ko_export_cal_weekly_view($module, $_size = 0, $_start='', $pages='') {
	global $ko_path, $BASE_PATH, $BASE_URL, $DATETIME;

	$_start = $_start != '' ? $_start : date('Y-m-d', mktime(1,1,1, $_SESSION['cal_monat'], $_SESSION['cal_tag'], $_SESSION['cal_jahr']));
	$absence_color = substr(ko_get_setting('absence_color'), 1);

	if($module == 'daten') {
		$moduleShort = 'daten';
		ko_get_eventgruppen($items);
		$planSize = $_size > 0 ? $_size : ko_get_userpref($_SESSION['ses_userid'], 'daten_pdf_week_length');
		if($planSize == 1) {
			$weekday = 1;
			$filename = getLL('daten_filename_pdf').strftime('%d%m%Y', mktime(1,1,1, $_SESSION['cal_monat'], $_SESSION['cal_tag'], $_SESSION['cal_jahr'])).'_'.strftime('%H%M%S', time()).'.pdf';
		} else {
			$weekday = ko_get_userpref($_SESSION['ses_userid'], 'daten_pdf_week_start');
			$filename = getLL('daten_filename_pdf').strftime('%d%m%Y_%H%M%S', time()).'.pdf';
		}
		$show_legend = ko_get_userpref($_SESSION['ses_userid'], 'daten_export_show_legend') == 1;
		$show_time = ko_get_userpref($_SESSION['ses_userid'], 'daten_pdf_show_time');
	} else {
		$moduleShort = 'res';
		ko_get_resitems($items);
		$planSize = $_size > 0 ? $_size : ko_get_userpref($_SESSION['ses_userid'], 'res_pdf_week_length');
		if($planSize == 1) {
			$weekday = 1;
			$filename = getLL('res_filename_pdf').strftime('%d%m%Y', mktime(1,1,1, $_SESSION['cal_monat'], $_SESSION['cal_tag'], $_SESSION['cal_jahr'])).'_'.strftime('%H%M%S', time()).'.pdf';
		} else {
			$weekday = ko_get_userpref($_SESSION['ses_userid'], 'res_pdf_week_start');
			$filename = getLL('res_filename_pdf').strftime('%d%m%Y_%H%M%S', time()).'.pdf';
		}

		$show_fields_to_guest = explode(",", ko_get_setting("res_show_fields_to_guest"));
		$show_persondata = $_SESSION['ses_userid'] != ko_get_guest_id() || in_array("name", $show_fields_to_guest);
		$show_purpose = $_SESSION['ses_userid'] != ko_get_guest_id() || in_array("zweck", $show_fields_to_guest);
		$show_legend = ko_get_userpref($_SESSION['ses_userid'], 'res_export_show_legend') == 1;
		$show_time = ko_get_userpref($_SESSION['ses_userid'], 'res_pdf_show_time');
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
			list($imageWidth, $imageHeight) = ko_imagesize_fit_in_box($BASE_PATH.'my_images/'.$logo, 90, 9);
			$pdf->Image($BASE_PATH.'my_images'.'/'.$logo , 290-$imageWidth, $top-13, $imageWidth);
		}

		//footer right
		$pdf->SetFont('fontn', '', 8);

		if (ko_get_userpref($_SESSION['ses_userid'], $moduleShort."_name_in_pdffooter") !== '0') {
			$person = ko_get_logged_in_person();
			$creator = $person['vorname'] ? $person['vorname'].' '.$person['nachname'] : $_SESSION['ses_username'];
			$footerRight = sprintf(getLL('tracking_export_label_created'), strftime($DATETIME['dmY'].' %H:%M', time()), $creator);
		} else {
			$footerRight = getLL('kota_ko_reservation_cdate') . ' ' . strftime($DATETIME['dmY'].' %H:%M', time());
		}

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


			//Absence
			if($_SESSION['show_absence' . ($table=="ko_reservation" ? "_res" : "")]) {
				require_once($BASE_PATH.'/daten/inc/daten.inc');
				$absenceEvents = ko_get_absences_for_calendar($date, $date, $table);
				if(sizeof($absenceEvents) > 0) {
					$items['absence'] = [
						'id' => 'absence',
						'farbe' => $absence_color,
						'name' => getLL('absence_eventgroup'),
						'shortname' => getLL('absence_eventgroup_short')
					];
					$eventArr = $eventArr + $absenceEvents;
				}
			}

			list($amtstageEvents, $egs) = ko_get_amtstageevents_for_calendar(strtotime($date), strtotime($date), TRUE);
			if(!empty($amtstageEvents)) {
				$eventArr = $amtstageEvents + $eventArr;
				$items = $items + $egs;
			}

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

					if($show_time > 0) {
						if($currEvent['startzeit'] == '00:00:00') $startTime = '';
						if(substr($currEvent['startzeit'], 3, 2) == '00') $startTime = intval(substr($currEvent['startzeit'], 0, 2));
						else $startTime = intval(substr($currEvent['startzeit'], 0, 2)).':'.substr($currEvent['startzeit'], 3, 2);

						if($currEvent['endzeit'] == '00:00:00') $endTime = '';
						if(substr($currEvent['endzeit'], 3, 2) == '00') $endTime = intval(substr($currEvent['endzeit'], 0, 2));
						else $endTime = intval(substr($currEvent['endzeit'], 0, 2)).':'.substr($currEvent['endzeit'], 3, 2);

						if($show_time == 1 && $startTime != '') {
							$eventText[0] = $startTime.' '.$eventText[0];
						} else if($show_time == 2 && ($startTime != '' || $endTime != '')) {
							$eventText[0] = $startTime.'-'.$endTime.' '.$eventText[0];
						}
					}
					if(trim($currEvent['title']) != '') $eventText[1] .= $currEvent['title']."\n";
					if(trim($currEvent['kommentar']) != '') $eventText[1] .= $currEvent['kommentar'];
					$eventShortText[0] = $item['shortname'];
					if(trim($currEvent['title']) != '') $eventShortText[1] .= $currEvent['title']."\n";
					if(trim($currEvent['kommentar']) != '') $eventShortText[1] .= $currEvent['kommentar'];

					if(ko_get_userpref($_SESSION['ses_userid'], 'daten_show_res_in_tooltip') == 2 && !empty($currEvent['reservationen'])) {
						$where = " AND ko_reservation.id IN (".$currEvent['reservationen'].")";
						ko_get_reservationen($reservations, $where);
						ko_get_resitems($resitems);
						$resDesc = [];
						foreach($reservations as $reservation) {
							//Format time
							if($reservation['startzeit'] == '00:00:00' && $reservation['endzeit'] == '00:00:00') {
								$time = getLL('time_all_day');
							} else {
								$time = substr($reservation['startzeit'], 0, -3);
								if($reservation['endzeit'] != '00:00:00') $time .= ' - '.substr($reservation['endzeit'], 0, -3);
							}

							$resDesc[] = "-" . $resitems[$reservation['item_id']]['name'].' ('.$time.')';
						}

						if(!empty($resDesc)) {
							$eventText[1].= "\n" . getLL('res_reservations') . ":\n" . implode("\n", $resDesc);
						}
					}
				}
				//Use item, purpose and name for reservations
				else {
					$eventText[0] = $item['name'];

					if($show_time > 0) {
						if($currEvent['startzeit'] == '00:00:00') $startTime = '';
						if(substr($currEvent['startzeit'], 3, 2) == '00') $startTime = intval(substr($currEvent['startzeit'], 0, 2));
						else $startTime = intval(substr($currEvent['startzeit'], 0, 2)).':'.substr($currEvent['startzeit'], 3, 2);

						if($currEvent['endzeit'] == '00:00:00') $endTime = '';
						if(substr($currEvent['endzeit'], 3, 2) == '00') $endTime = intval(substr($currEvent['endzeit'], 0, 2));
						else $endTime = intval(substr($currEvent['endzeit'], 0, 2)).':'.substr($currEvent['endzeit'], 3, 2);

						if($show_time == 1 && $startTime != '') {
							$eventText[0] = $startTime.' '.$eventText[0];
						} else if($show_time == 2 && ($startTime != '' || $endTime != '')) {
							$eventText[0] = $startTime.'-'.$endTime.' '.$eventText[0];
						}
					}

					if($show_purpose && $currEvent['zweck'] != '') $eventText[1] = $currEvent['zweck'];
					if($show_persondata && ko_get_userpref($_SESSION['ses_userid'], 'res_contact_in_export')) {
						if(trim($currEvent['name']) != '') $eventText[1] .= ($eventText[1] != '' ? ' - ' : '').getLL('by').' '.$currEvent['name'];
						if(trim($currEvent['telefon']) != '') $eventText[1] .= ($eventText[1] != '' ? ' - ' : '').$currEvent['telefon'];
					}
					$eventShortText = $eventText;
				}

				// Strip HTML Tags
				foreach ($eventText as $k => $v) {
					$eventText[$k] = strip_tags($v);
				}
				foreach ($eventShortText as $k => $v) {
					$eventShortText[$k] = strip_tags($v);
				}

				//check if title is still empty (e.g. kommentar is empty)
				if(trim($eventText[0]) == '') {
					$eventText[] = $item['name'];
					$eventShortText[] = $item['shortname'];
				}
				if(trim($eventShortText[0]) == '') {
					$eventShortText = $eventText;
				}
				$replace = array("\t" => ' ', "\v" => ' ');
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


/**
 * Create a PDF with table of objects and when they are used for reservations. Shows: timelineDay
 *
 * @param Integer $_size days to be display
 * @param string $_start date to start displaying
 * @return string Filename of PDF
 */
function ko_export_cal_weekly_view_resource($_size='', $_start='') {
	global $ko_path, $BASE_PATH, $DATETIME, $do_action;

	// Starting parameters
	$startDate = $_start != '' ? $_start : date('Y-m-d', mktime(1,1,1, $_SESSION['cal_monat'], $_SESSION['cal_tag'], $_SESSION['cal_jahr']));

	// get resitems, applies filter from $_SESSION['show_item']
	ko_get_resitems($items, '', sizeof($_SESSION['show_items']) > 0 ? 'where ko_resitem.id in (' . implode(',', $_SESSION['show_items']) . ')' : 'where 1=2');

	if(!empty($_SESSION['show_absences_res'])) {
		$items[] = [
			"name" => getLL('daten_absence_list_title'),
			"id" => "absences",
		];
	}


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

	foreach ($items AS $item) {
		$startDate = date('Y-m-d', $startStamp);
		$endDate = date('Y-m-d', $endStamp);

		$id = $item['id'];
		$where = "WHERE ((`startdatum` <= '$startDate' AND `enddatum` >= '$startDate') OR
			(`startdatum` <= '$endDate' AND `enddatum` >= '$endDate') OR
			(`startdatum` >= '$startDate' AND `enddatum` <= '$endDate')) AND
			(`item_id` = '$id' OR FIND_IN_SET('{$id}', linked_items) > 0) ";
		$res_tmp = db_select_data(
			'ko_reservation',
			$where,
			'*',
			'order by `startdatum` asc',
			'',
			FALSE,
			TRUE);

		foreach($res_tmp AS $res) {
			$reservations[$item['id']][] = $res;
			if(!empty($res['linked_items'])) {
				foreach(explode(",", $res['linked_items']) AS $linked_item) {
					$reservations[$linked_item][] = $res;
				}
			}
		}
	}


	if ($_SESSION['show_absences_res'] && ko_module_installed('leute')) {
		require_once('../../daten/inc/daten.inc');
		if(!isset($endDate)) $endDate = $startDate;
		$absences = ko_get_absences_for_calendar($startDate, $endDate, "resource");
		foreach($absences AS $absence) {
			$reservations["absences"][] = [
				"id" => $absence['id'],
				"startdatum" => substr($absence['start'],0,10),
				"startzeit" => substr($absence['start'],11),
				"enddatum" => substr($absence['end'],0,10),
				"endzeit" => substr($absence['end'],11),
				"zweck" => $absence['title'],
			];

		}
	}

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
				list($imageWidth, $imageHeight) = ko_imagesize_fit_in_box($BASE_PATH.'my_images/'.$logo, 90, 9);
				$pdf->Image($BASE_PATH.'my_images'.'/'.$logo , 290-$imageWidth, $top-13, $imageWidth);
			}

			//footer right
			$pdf->SetFont('fontn', '', 8);

			if (ko_get_userpref($_SESSION['ses_userid'], "res_name_in_pdffooter") !== '0') {
				$person = ko_get_logged_in_person();
				$creator = $person['vorname'] ? $person['vorname'].' '.$person['nachname'] : $_SESSION['ses_username'];
				$footerRight = sprintf(getLL('tracking_export_label_created'), strftime($DATETIME['dmY'].' %H:%M', time()), $creator);
			} else {
				$footerRight = getLL('kota_ko_reservation_cdate') . ' ' . strftime($DATETIME['dmY'].' %H:%M', time());
			}

			$pdf->Text(291 - $pdf->GetStringWidth($footerRight), 202, $footerRight );

			$pdf->AliasNbPages();
			$footerLeft =  getLL('page') . ' ' . $pdf->PageNo() ."/{nb}";
			$pdf->Text(5, 202, $footerLeft );

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
				$weekday = substr($weekday, 0, 2);

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
		$unixTimesT = array();
		$hour = 0;
		$minute = 0;
		$startUnix = $startStamp;
		$endUnix = $endStamp + 3600*24 - 1;

		$unixTimes = array();
		foreach ($reservations[$item['id']] as $res) {
			if ($res['startdatum'] == $res['enddatum'] && $res['startzeit'] == '00:00:00' && $res['startzeit'] == $res['endzeit']) {
				$res['endzeit'] = '23:59:59';
			}
			$unixTimesT[] = array('corners' => '1234', 'start' => strtotime($res['startdatum'] . ' ' . $res['startzeit']), 'end' => strtotime($res['enddatum'] . ' ' . $res['endzeit']), 'zweck' => $res['zweck'], 'name' => $res['name'], 'telefon' => $res['telefon'], 'startzeit' => $res['startzeit']);
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
						$entry['name'] = $ut['name'];
						$entry['telefon'] = $ut['telefon'];
						$entry['email'] = $ut['email'];
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
						$entry['name'] = $ut['name'];
						$entry['telefon'] = $ut['telefon'];
						$entry['email'] = $ut['email'];
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
					if(ko_get_userpref($_SESSION['ses_userid'], 'res_contact_in_export') && ($ut['name'] || $ut['telefon'])) {
						$text .= ' ('.getLL('by');
						if($ut['name']) $text .= ' '.$ut['name'];
						if($ut['telefon']) $text .= ' '.$ut['telefon'];
						$text = trim($text).')';
					}
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
 * Create a PDF with table of objects and when they are used for reservations. Shows: timelineWeek
 *
 * @param string $_size days to be display
 * @param string $_start date to start displaying
 * @return string Filename of PDF
 * @throws Exception
 */
function ko_export_cal_weekly_view_resource_2($_size='', $_start='') {
	global $ko_path, $BASE_PATH;

	// Starting parameters
	$startDate = $_start != '' ? $_start : date('Y-m-d', mktime(1,1,1, $_SESSION['cal_monat'], $_SESSION['cal_tag'], $_SESSION['cal_jahr']));

	// get resitems, applies filter from $_SESSION['show_item']
	ko_get_resitems($items, '', sizeof($_SESSION['show_items']) > 0 ? 'where ko_resitem.id in (' . implode(',', $_SESSION['show_items']) . ')' : 'where 1=2');

	if(!empty($_SESSION['show_absences_res'])) {
		$items[] = [
			"name" => getLL('daten_absence_list_title'),
			"id" => "absences",
		];
	}

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

	$totalWidth = 297;
	$margins = array(15, 8, 8, 8);

	//Prepare PDF file
	define('FPDF_FONTPATH', $BASE_PATH.'fpdf/schriften/');
	require_once($BASE_PATH.'fpdf/PDF_HTML.php');

	$pdf = new PDF_HTML('L', 'mm', 'A4');
	$dummy = new PDF_HTML('L', 'mm', 'A4');
	$pdf->Open();
	$dummy->Open();
	$pdf->SetAutoPageBreak(true, 15);
	$pdf->SetMargins($margins[3], $margins[0], $margins[1]);  //left, top, right
	if(file_exists($ko_path.'fpdf/schriften/DejaVuSansCondensed.php')) {
		$pdf->AddFont('font', '', 'DejaVuSansCondensed.php');
		$dummy->AddFont('font', '', 'DejaVuSansCondensed.php');
	} else {
		$pdf->AddFont('font', '', 'arial.php');
		$dummy->AddFont('font', '', 'arial.php');
	}
	if(file_exists($ko_path.'fpdf/schriften/DejaVuSansCondensed-Bold.php')) {
		$pdf->AddFont('font', 'b', 'DejaVuSansCondensed-Bold.php');
		$dummy->AddFont('font', 'b', 'DejaVuSansCondensed-Bold.php');
	} else {
		$pdf->AddFont('font', 'b', 'arialb.php');
		$dummy->AddFont('font', 'b', 'arialb.php');
	}

	$dummy->AddPage();
	$dummy->SetFont('font', '', 8);

	//Create Resource-View
	$objectLabel = getLL('res_cal_object');
	$fontSizeHeader = 10;
	$fontSizeCells = 8;
	$headerCellMargin = 1;
	$cellMargin = 1;

	$pdf->SetFont('font', '', $fontSizeHeader);

	$availableWidth = $totalWidth - $margins[3] - $margins[1];
	$widths = array(25);
	$dayWidth = ($availableWidth - $widths[0]) / $planSize;
	$headerHAligns = array('C');
	$hAligns = array('L');
	$headerFills = array(TRUE);
	$fills = array(TRUE);
	$headerFillColors = array('336699');
	$headerTextColors = array('ffffff');
	$textColors = array('ffffff');
	for ($i = 0; $i < $planSize; $i++) {
		$widths[] = $dayWidth;
		$headerHAligns[] = 'C';
		$hAligns[] = 'L';
		$headerFills[] = TRUE;
		$fills[] = FALSE;
		$headerFillColors[] = '336699';
		$headerTextColors[] = 'ffffff';
		$textColors[] = '000000';
	}

	$pdf->SetWidths($widths);

	$firstOnPage = TRUE;

	$settings = array(
		'endStamp' => $endStamp,
		'startStamp' => $startStamp,
		'margins' => $margins,
		'planSize' => $planSize,
		'totalWidth' => $totalWidth,
	);

	$pageCount = 0;
	$pdf->headerFcn = function(PDF_HTML $p)use(&$pageCount, $settings, $dummy, $objectLabel, $planSize, $startStamp, $headerCellMargin, $margins, $headerTextColors, $headerFills, $headerFillColors, $fontSizeHeader, $cellMargin, $textColors, $fills, $fontSizeCells){
		global $BASE_PATH;

		$top = 18;
		$left = $settings['margins'][3];

		//Title
		$p->SetFont('font', 'b', 11);
		$m = strftime('%B', $settings['startStamp']) == strftime('%B', $settings['endStamp']) ? '' : strftime(' %B ', $settings['startStamp']);
		$y = strftime('%Y', $settings['startStamp']) == strftime('%Y', $settings['endStamp']) ? '' : strftime('%Y', $settings['startStamp']);

		if($settings['planSize'] == 1) {
			$p->Text($left, $top-6, getLL('reservation_export_pdf_title').strftime(' - %d. %B %Y', $settings['endStamp']));
		} else {
			$p->Text($left, $top-6, getLL('reservation_export_pdf_title').strftime(' %d.', $settings['startStamp']).$m.$y.strftime(' - %d. %B %Y', $settings['endStamp']));
		}

		//Add logo in header (only if legend is not to be shown)
		$logo = ko_get_pdf_logo();
		if($logo != '') {
			list($imageWidth, $imageHeight) = ko_imagesize_fit_in_box($BASE_PATH.'my_images/'.$logo, 90, 9);
			$p->Image($BASE_PATH.'my_images'.'/'.$logo , $settings['totalWidth']-$settings['margins'][1]-$imageWidth, $top-13, $imageWidth);
		}

		$p->SetLineWidth(0.1);

		//Draw day labels and boxes
		$data = array($objectLabel);
		$index = 0;
		while ($index < $planSize) {
			$index++;

			//Get current date information
			$currentStamp = strtotime('+'.($index-1).' day', $startStamp);
			$date = strftime('%d.%m.%y', $currentStamp);

			$weekday = strftime('%a', $currentStamp);
			$weekday = substr($weekday, 0, 2);

			$data[] = $weekday . ', ' . $date;
		}
		$p->cMargin = $headerCellMargin;
		$p->SetXY($margins[3], $margins[0]);

		$p->SetTextColors($headerTextColors);
		$p->SetFills($headerFills);
		$defaultFillColors = $p->fillColors;
		$p->SetFillColors($headerFillColors);
		$p->SetFont('font', '', $fontSizeHeader);
		$dummy->SetFont('font', '', $fontSizeHeader);
		$p->WriteHtmlRow($data, $dummy);

		$p->cMargin = $cellMargin;
		$p->SetFills($fills);
		if($p->page == 1) {
			$p->SetFillColors($headerFillColors);
		} else {
			$fillColorCol1 = dechex($defaultFillColors[0][0]) . dechex($defaultFillColors[0][1]) . dechex($defaultFillColors[0][2]);
			$contrastColorCol1_hex = ko_get_contrast_color($fillColorCol1);
			$textColors[0] = substr($contrastColorCol1_hex,1);
			// fillColors is used when creating htmlcell
			$p->fillColors = $defaultFillColors;
		}
		$p->SetTextColors($textColors);
		$p->SetFont('font', '', $fontSizeCells);
		$dummy->SetFont('font', '', $fontSizeCells);
	};
	$pdf->footerFcn = function(PDF_HTML $p)use($settings){
		global $DATETIME;

		$p->SetFont('font', '', 8);
		if (ko_get_userpref($_SESSION['ses_userid'], "res_name_in_pdffooter") !== '0') {
			$person = ko_get_logged_in_person();
			$creator = $person['vorname'] ? $person['vorname'].' '.$person['nachname'] : $_SESSION['ses_username'];
			$footerRight = sprintf(getLL('tracking_export_label_created'), strftime($DATETIME['dmY'].' %H:%M', time()), $creator);
		} else {
			$footerRight = getLL('kota_ko_reservation_cdate') . ' ' . strftime($DATETIME['dmY'].' %H:%M', time());
		}

		$p->Text(291 - $p->GetStringWidth($footerRight), 202, $footerRight);

		$p->AliasNbPages();
		$footerLeft =  getLL('page') . ' ' . $p->PageNo() ."/{nb}";
		$p->Text(8, 202, $footerLeft );
	};

	$holidays_resitem = ko_get_setting('holidays_resitem');
	$where = "AND item_id = '" . $holidays_resitem ."'";
	ko_get_reservationen($holidays, $where);

	for($i = 1; $i<=$planSize;$i++) {
		$currDateStamp = $startStamp + (86400*($i-1));
		$currDate = date("Y-m-d", $currDateStamp );
		if(in_array($currDate, array_column($holidays, "startdatum"))) {
			if(!empty($items[$holidays_resitem]['farbe'])) {
				$holidayFillColors[$i] = $items[$holidays_resitem]['farbe'];
			} else {
				$holidayFillColors[$i] = "ffff00";
			}
		} else {
			$holidayFillColors[$i] = "ffffff";
		}
	}

	//Go through resources and draw the corresponding lines
	foreach ($items as $item) {
		if ($firstOnPage) {
			$pdf->AddPage();
			$firstOnPage = FALSE;
		}

		$itemColor[$item['id']] = $item['farbe'];

		$data[$item['id']][0] = $item['name'];
		for ($i = 0; $i < $planSize; $i++) {
			$currDate = strftime('%Y-%m-%d', strtotime("+{$i} days", $startStamp));

			if($item['id'] == "absences") {
				require_once('../../daten/inc/daten.inc');
				if(!isset($endDate)) $endDate = $startDate;
				$absences = ko_get_absences_for_calendar($currDate, $currDate, "resource");
				$reservations = [];
				foreach($absences AS $absence) {
					$reservations[] = [
						"id" => $absence['id'],
						"startdatum" => substr($absence['start'],0,10),
						"startzeit" => substr($absence['start'],11),
						"enddatum" => substr($absence['end'],0,10),
						"endzeit" => substr($absence['end'],11),
						"zweck" => $absence['title'],
					];
				}
			} else {
				$where = "WHERE 
				(`item_id` = '{$item['id']}' OR FIND_IN_SET('{$item['id']}', linked_items) <> 0)
				AND `startdatum` <= '{$currDate}' 
				AND `enddatum` >= '{$currDate}'";
				$reservations = db_select_data('ko_reservation', $where, '*', 'ORDER BY `startzeit` ASC, `endzeit` ASC');
			}

			$html = [];
			foreach ($reservations as $res) {
				// reservation for multiple days
				if ($currDate < $res['enddatum']) {
					$res['endzeit'] = '24:00';
				}
				if ($currDate > $res['startdatum']) {
					$res['startzeit'] = '00:00';
				}

				$rTime = substr($res['startzeit'], 0, 5) . " - " . substr($res['endzeit'], 0, 5);
				if ($rTime == '00:00 - 00:00' || $rTime == '00:00 - 24:00') {
					$rTime = getLL('time_all_day');
				}

				$text = "<b>{$rTime}</b><br>{$res['zweck']}";
				if (ko_get_userpref($_SESSION['ses_userid'], 'res_contact_in_export') && ($res['name'] || $res['telefon'])) {
					$text .= '<br />' . getLL('by');
					if ($res['name']) $text .= ' ' . $res['name'];
					if ($res['telefon']) $text .= ' ' . $res['telefon'];
				}
				$html[] = $text;
			}
			$data[$item['id']][$i+1].= implode("<br>", $html);
		}

	}

	foreach($data AS $key => $item) {
		ksort($item);
		$pdf->cMargin = $cellMargin;

		$fillColors[0] = $itemColor[$key] ? $itemColor[$key] : '336699';

		foreach($holidayFillColors AS $day => $holidayFillColor) {
			if(!empty($item[$day]) && $key == $holidays_resitem) {
				$fillColors[$day] = $holidayFillColor;
				$fills[$day] = TRUE;
			} else {
				$fillColors[$day] = "ffffff";
				$fills[$day] = FALSE;
			}
		}

		$pdf->SetFillColors($fillColors);

		$textColors[0] = ko_get_contrast_color($fillColors[0], '000000', 'ffffff');
		$pdf->SetTextColors($textColors);

		$pdf->SetFills($fills);
		$pdf->SetFont('font', '', $fontSizeCells);
		$dummy->SetFont('font', '', $fontSizeCells);
		$pdf->WriteHtmlRow($item, $dummy);
	}

	$file = $BASE_PATH.'download/pdf/'.$filename;

	$pdf->Output($file);
	return $filename;
}//	ko_export_cal_weekly_view_resource_2()





/**
  * Exportiert einen Monat als PDF
	*/
function ko_export_cal_one_month(&$pdf, $monat, $jahr, $kw, $day, $titel, $show_comment=FALSE, $show_legend=FALSE, $legend=array()) {
	global $BASE_URL, $BASE_PATH, $DATETIME, $ko_menu_akt;

	if($ko_menu_akt == 'daten') {
		$moduleShort = 'daten';
	} else {
		$moduleShort = 'res';
	}

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
	$pdf->SetTextColor(0);
	$pdf->Text($left, $top-3, "$titel $month_name $year_name");

	//Add logo in header
	$logo = ko_get_pdf_logo();
	if($logo != '' && !$show_legend) {
		list($imageWidth, $imageHeight) = ko_imagesize_fit_in_box($BASE_PATH.'my_images/'.$logo, 90, 9);
		$pdf->Image($BASE_PATH.'my_images'.'/'.$logo , 290-$imageWidth, $top-10, $imageWidth);
	}

	//Footer right
	$pdf->SetFont('fontn', '', 8);

	if (ko_get_userpref($_SESSION['ses_userid'], $moduleShort."_name_in_pdffooter") !== '0') {
		$person = ko_get_logged_in_person();
		$creator = $person['vorname'] ? $person['vorname'].' '.$person['nachname'] : $_SESSION['ses_username'];
		$footerRight = sprintf(getLL('tracking_export_label_created'), strftime($DATETIME['dmY'].' %H:%M', time()), $creator);
	} else {
		$footerRight = getLL('kota_ko_reservation_cdate') . ' ' . strftime($DATETIME['dmY'].' %H:%M', time());
	}

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





function ko_get_time_as_string($event, $show_time, $mode='default', $shorten=TRUE, $useTextForAllDay=TRUE) {
	$time = '';

	if($show_time) {
		if($event['startzeit'] == '00:00:00' && $event['endzeit'] == '00:00:00') {
			if($useTextForAllDay) $time = getLL('time_all_day');
		} else {
			if($mode == 'default') {
				if($show_time == 1) {  //Only show start time
					$time = (substr($event['startzeit'], 3, 2) == '00' && $shorten) ? substr($event['startzeit'], 0, 2) : substr($event['startzeit'], 0, -3);
				} else if($show_time == 2) {  //Show start and end time
					$time  = (substr($event['startzeit'], 3, 2) == '00' && $shorten) ? substr($event['startzeit'], 0, 2) : substr($event['startzeit'], 0, -3);
					$time .= '-';
					$time .= (substr($event['endzeit'], 3, 2) == '00' && $shorten) ? substr($event['endzeit'], 0, 2) : substr($event['endzeit'], 0, -3);
				}
			}
			else if($mode == 'first') {
				$time = getLL('time_from').' ';
				$time .= (substr($event['startzeit'], 3, 2) == '00' && $shorten) ? substr($event['startzeit'], 0, 2) : substr($event['startzeit'], 0, -3);
			}
			else if($mode == 'middle') {
				if($useTextForAllDay) $time = getLL('time_all_day');
			}
			else if($mode == 'last') {
				$time = getLL('time_to').' ';
				$time .= (substr($event['endzeit'], 3, 2) == '00' && $shorten) ? substr($event['endzeit'], 0, 2) : substr($event['endzeit'], 0, -3);
			}
		}
	} else {
		$time = '';
	}

	return $time;
}//ko_get_time_as_string()






function ko_export_cal_pdf_year($module, $_month, $_year, $_months=0) {
	global $BASE_PATH, $BASE_URL, $DATETIME;
	$absence_color = substr(ko_get_setting('absence_color'), 1);

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
		$moduleShort = 'daten';
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
		$moduleShort = 'res';
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
	require_once($BASE_PATH.'fpdf/fpdf.php');

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
		list($imageWidth, $imageHeight) = ko_imagesize_fit_in_box($BASE_PATH.'my_images/'.$logo, 90, 9);
		$pdf->Image($BASE_PATH.'my_images'.'/'.$logo , 290-$imageWidth, $top-13, $imageWidth);
	}


	//footer right
	$pdf->SetFont('fontn', '', 8);

	if (ko_get_userpref($_SESSION['ses_userid'], $moduleShort."_name_in_pdffooter") !== '0') {
		$person = ko_get_logged_in_person();
		$creator = $person['vorname'] ? $person['vorname'].' '.$person['nachname'] : $_SESSION['ses_username'];
		$footerRight = sprintf(getLL('tracking_export_label_created'), strftime($DATETIME['dmY'].' %H:%M', time()), $creator);
	} else {
		$footerRight = getLL('kota_ko_reservation_cdate') . ' ' . strftime($DATETIME['dmY'].' %H:%M', time());
	}

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

		// when we are in december, next month is january next year
		if ($month == 12) {
			$endmonth = 1;
			$endyear = $year + 1;
		} else {
			$endmonth = $month + 1;
			$endyear = $year;
		}

		// get the events which are at least three days long for vertical lines
		$where = "WHERE (MONTH(startdatum) = ".$month." AND YEAR(startdatum) = ".$year." AND (TO_DAYS(enddatum) - TO_DAYS(startdatum)) > 1 OR MONTH(enddatum) = ".$month." AND YEAR(enddatum) = ".$year." AND (TO_DAYS(enddatum) - TO_DAYS(startdatum)) > 2 OR startdatum < '".$year."-".$month."-01' AND enddatum > '".$endyear."-".($endmonth)."-01' AND (TO_DAYS(enddatum) - TO_DAYS(startdatum)) > 2)";

		if(sizeof($useEventGroups) > 0) {
			if ($db_group_field == "item_id") {
				$useEventGroups = ko_get_resitems_with_linked_items($useEventGroups);
			}

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

		//Absence
		if($_SESSION['show_absence' . ($module=="reservation" ? "_res" : "")]) {
			require_once($BASE_PATH.'daten/inc/daten.inc');
			$absenceEvents = ko_get_absences_for_calendar($year.'-'.$month.'-01', $endyear.'-'.$endmonth.'-01', $db_table);
			foreach($absenceEvents as $aeK => $ae) {
				if($ae['duration'] < 3) unset($absenceEvents[$aeK]);
			}
			if(sizeof($absenceEvents) > 0) {
				$egs['absence'] = array('id' => 'absence', 'farbe' => $absence_color, 'name' => getLL('absence_eventgroup'), 'shortname' => getLL('absence_eventgroup_short'));
				$eventArr = array_merge($eventArr, $absenceEvents);
			}
		}


		list($amtstageEvents, $amtstageEgs) = ko_get_amtstageevents_for_calendar(strtotime($year.'-'.$month.'-01'), strtotime($endyear.'-'.$endmonth.'-01'), TRUE);
		if(!empty($amtstageEvents)) {
			$eventArr = array_merge($amtstageEvents, $eventArr);
			$egs = array_merge($amtstageEgs, $egs);
		}

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
			}else{
				$startDay = (int)substr($currEvent['startdatum'],8,2);
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
			if ($db_group_field == "item_id") {
				$useEventGroups = ko_get_resitems_with_linked_items($useEventGroups);
			}

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


		//Absence
		if($_SESSION['show_absence' . ($module=="reservation" ? "_res" : "")]) {
			require_once($BASE_PATH.'daten/inc/daten.inc');
			$absenceEvents = ko_get_absences_for_calendar($year.'-'.$month.'-01', $endyear.'-'.$endmonth.'-01', $db_table);
			foreach($absenceEvents as $aeK => $ae) {
				if($ae['duration'] > 2) unset($absenceEvents[$aeK]);
			}
			if(sizeof($absenceEvents) > 0) {
				$singleEventArr = array_merge($singleEventArr, $absenceEvents);
				$egs['absence'] = array('id' => 'absence', 'farbe' => $absence_color, 'name' => getLL('absence_eventgroup'), 'shortname' => getLL('absence_eventgroup_short'));
			}
		}


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




function ko_export_etiketten($_vorlage, $_start, $_rahmen, $data, $fill_page=0, $multiply=1, $return_address=FALSE, $return_address_mode='', $return_address_text='', $pp=FALSE, $pp_mode='', $pp_text='', $priority=FALSE) {
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
	$fonts = array('arial', 'arialb', 'arialm');
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
										'C6' => array(114, 162),
										'B6' => array(125, 176),
										'C65' => array(114, 229),
										'C5' => array(162, 229),
										'B5' => array(176, 250),
										'C4' => array(229, 324),
										'B4' => array(250, 353),
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
	require_once($BASE_PATH.'fpdf/mc_table.php');
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
		if(($return_address || ($pp && $vorlage['pp_position'] == 'address')) && $valigns[$i] == 'C') $valigns[$i] = 'T';
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

		$ra_aligns = $ra_valigns = array();
		for($c = 1; $c <= $vorlage['per_row']; $c++) {
			$ra_aligns[] = 'L';
			$ra_valigns[] = 'T';
		}
	}

	//Prepare pp
	if($pp) {
		if (strstr($pp_mode, 'manual_address') != false) {
			$ppParts = explode(' ', $pp_text);
			$ppZip = array_shift($ppParts);
			$ppCity = implode(' ', $ppParts);
		}
		else {
			$ppParts = explode(' ', $pp_mode);
			$ppZip = array_shift($ppParts);
			$ppCity = implode(' ', $ppParts);
		}

		$ppPosition = $vorlage['pp_position'];
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
				$save['x'] = $pdf->GetX();
				$save['y'] = $pdf->GetY();
				$save['zeilenhoehe'] = $pdf->zeilenhoehe;

				$spacing_vert = $vorlage['spacing_vert'];
				$spacing_horiz = $vorlage['spacing_horiz'];

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

					if (!$last && $pp && $do_row) {
						$ppBox = ko_write_pp($pdf, $ppZip, $ppCity, array($col_width, $row_height), array($vorlage['border_left'] + ($c-1)*$col_width, $vorlage['border_top'] + ($r-1)*$row_height),  array($spacing_horiz, $spacing_vert), $ppPosition, $priority);
						$spacing_vert_new = $ppBox[3] + 3.0 - ($vorlage['border_top'] + ($r - 1) * $row_height);
					}
				}//for(c=1..vorlage[per_row])

				if ($pp && $vorlage['pp_position'] == 'address' && $valigns[0] == 'T') {
					$spacing_vert = max($spacing_vert_new, $spacing_vert);
				}

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

					$ra_margin_top += $spacing_vert;

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

				if($return_address && $valigns[0] == 'T') {
					$spacing_vert = max($vorlage['spacing_vert'], $line_top - $save['y'] + 2);
				}

				//Set aligns, font and border for actual address content
				$pdf->SetAligns($aligns);
				$pdf->SetvAligns($valigns);
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



function ko_write_pp(FPDF &$pdf, $plz, $city, $letterSize, $letterPosition, $addressPosition, $ppLocation, $priority=FALSE) {
	if (strlen($city) > 18) {
		$city = substr($city, 0, 17) . '.';
	}
	if (strpos($plz, '-') === FALSE) {
		$plz = "CH-{$plz}";
	}

	$fontSizePP = 18;
	$fontSizeAddress = 8;
	$fontSizePost = 6;
	$fontSizePriority = 22;
	$fontSizePrioritySmall = 6;
	$ppFont = 'arial';
	$ppBoldFont = 'arialm';

	$logo = 'Post CH AG';

	if ($ppLocation == 'address' || !$ppLocation) {
		list($letterX, $letterY) = $letterPosition;
		list($x, $y) = $addressPosition;

		//1mm padding from above inside label
		$y += 3;

		$x += $letterX;
		$y += $letterY;

		$width = 61.0;
		$height = 7.0;

		$col2 = 15.0;
		$col3 = 44.0;

		$pdf->Rect($x, $y, $col3 - 1, $height);
		$pdf->SetFont($ppBoldFont, '', $fontSizePP);
		$pdf->Text($x+0.5, $y+$height-1, 'P.P.');

		$pdf->SetFont($ppFont, '', $fontSizeAddress);
		$pdf->Text($x+$col2, $y+$height/2-0.4, $plz);
		$pdf->Text($x+$col2, $y+$height-1, $city);

		if ($priority) {
			$pdf->SetFont($ppBoldFont, '', $fontSizePriority);
			$pdf->Text($x+$col3-0.2, $y+$height-0.5, 'A');

			$pdf->SetFont($ppBoldFont, '', $fontSizePrioritySmall);
			$pdf->Text($x+$col3+5.8, $y+$height-0.5, '-PRIORITY');
		}

		$pdf->SetFont($ppFont, '', $fontSizePost);
		$pdf->Text($x+$width-$pdf->GetStringWidth($logo), $y + 1.8 + 0.5, $logo);

		$pdf->Line($x, $y+$height+1, $x+$width, $y+$height+1);

		return array($x, $y, $x+$width, $y+$height+1);
	} else {  //stamp
		list($w, $h) = $letterSize;
		list($letterX, $letterY) = $letterPosition;

		$width = 50.0;
		$height = 11.4;

		$col2 = 19.5;
		$row2 = 8.0;

		$x = $letterX + $w - $width - 4.0;
		$y = $height + 4.0;

		$fontSizePP = 14;

		if ($priority) {
			$pdf->SetFont($ppBoldFont, '', $fontSizePriority);
			$pdf->Text($x, $y+$height-1.0, 'A');

			$pdf->SetFont($ppBoldFont, '', $fontSizePrioritySmall);
			$pdf->Text($x+6.0, $y+$height-1.0, '-PRIORITY');
		}

		$pdf->Rect($x+$col2, $y, $width-$col2, $height);
		$pdf->SetFont($ppBoldFont, '', $fontSizePP);
		$pdf->Text($x+$col2+1, $y+4.2, 'P.P.');

		$pdf->SetFont($ppFont, '', $fontSizeAddress);
		$pdf->Text($x+$col2+1, $y+7.2, $plz);
		$pdf->Text($x+$col2+1, $y+$height-1, $city);

		$pdf->SetFont($ppFont, '', $fontSizePost);
		$pdf->Text($x+$col2+1, $y+$height+2.5, $logo);

		return array($x, $y, $x+$width, $y+$height+2.5);
	}
}





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
 * Calculates the image's width and height so it fits inside the given box. Used e.g. for logo on PDF exports
 * @param $image String Filename of image file
 * @param $myWidth Integer Desired maximum width of containing box
 * @param $myHeight Integer Desired maximum height of containing box
 * @return Array holding width and height to be used for image output
 */
function ko_imagesize_fit_in_box($image, $myWidth, $myHeight) {
	list($originalWidth, $originalHeight) = getimagesize($image);
	$ratio = $originalWidth / $originalHeight;

	$ratioW = $originalWidth / $myWidth;
	$ratioH = $originalHeight / $myHeight;
	$ratio = max($ratioW, $ratioH);

	$width = $originalWidth / $ratio;
	$height = $originalHeight / $ratio;

	return array($width, $height);
}//ko_imagesize_fit_in_box()




/**
 * gathers all available mailmerge pdf layouts
 *
 * @return array All available mailmerge layouts
 */
function ko_leute_get_mailmerge_layouts() {
	global $MAILMERGE_LAYOUTS;

	$layouts = $MAILMERGE_LAYOUTS;
	sort($layouts, SORT_LOCALE_STRING);
	return $layouts;
}





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
	$data = array();

	foreach($content as $line) {
		//Check for encodings
		$quoted = strstr($line, ";ENCODING=QUOTED-PRINTABLE");
		$utf8 = strstr($line, ";CHARSET=UTF-8");

		$line = preg_replace("/;ENCODING=QUOTED-PRINTABLE/", "", $line);
		$line = preg_replace("/;CHARSET=ISO-\d{4}-\d{1,2}/", "", $line);
		$line = preg_replace("/;CHARSET=UTF-8/", "", $line);

		//Find prop and value
		$temp = explode(":", $line);
		$prop = strtoupper($temp[0]);
		unset($temp[0]);
		$value = trim(implode(":", $temp));
		if($quoted) $value = quoted_printable_decode($value);
		if($utf8) $value = utf8_decode($value);

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
			$return[$key][$k] = mysqli_real_escape_string(db_get_link(), $v);
		}
	}
	return $return;
}//ko_parse_vcf()



/**
  * Runs some checks, before a csv import can be performed
	*/
function ko_parse_csv($file, $options, $test=FALSE) {
	global $KOTA;

	$separator = $options["separator"];
	$content_separator = $options["content_separator"];
	$first_line = $options["first_line"];
	$dbcols = $options["dbcols"];
	$num_cols = sizeof($dbcols);

	//find date-cols
	$date_cols = $enum_cols = array();
	$table_cols = db_get_columns("ko_leute");
	foreach($table_cols as $col) {
		if ($col["Type"] == "date") $date_cols[] = $col["Field"];
		if (in_array($KOTA['ko_leute'][$col["Field"]]['type'], array('textplus', 'select'))) $enum_cols[] = $col["Field"];
	}


	$error = 0;
	$data = array();
	$first = TRUE;
	$first = TRUE;
	$fp = fopen($file, 'r');
	while($parts = fgetcsv($fp, 0, $separator, $content_separator)) {

		//Encoding
		if($options['file_encoding'] == 'macintosh') {
			foreach($parts as $k => $v) {
				$parts[$k] = iconv('macintosh', 'ISO-8859-1', $v);
			}
		}
		else if($options['file_encoding'] == 'utf-8') {
			foreach($parts as $k => $v) {
				$parts[$k] = utf8_decode($v);
			}
		}

		//ignore first line if set
		if($first && $first_line) {
			$first = FALSE;
		} else {
			$first = FALSE;

			if($test) {
				if(sizeof($parts) < $num_cols) $error = 1;
				if(sizeof($parts) > $num_cols) $error = 2;
			} else {
				$new_data = array();
				foreach($dbcols as $col) {
					$new_data[$col] = mysqli_real_escape_string(db_get_link(), array_shift($parts));
					//create sql-date
					if(in_array($col, $date_cols)) {
						$new_data[$col] = sql_datum($new_data[$col]);
					}
					//Check for LL values in enum fields
					if(in_array($col, $enum_cols)) {
						$enums_ll = kota_get_select_descs_assoc("ko_leute", $col);
						$enums = array_keys($enums_ll);
						//If not in English then try to find it in the ll version
						if(!in_array($new_data[$col], $enums)) {
							foreach($enums_ll as $key => $value) {
								if(strtolower($value) == strtolower($new_data[$col])) {
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
 * Parses a general CSV File, not module specific
 *
 * @param            $csv string The filename of the csv string
 * @param            $options array
 * @param bool|FALSE $fromString Set to true if the $data var contains the content of a csv file instead of the path
 * @return array|int
 */
function ko_parse_general_csv($csv, $options, $fromString=FALSE) {
	global $ko_path;

	$separator = $options["separator"];
	$content_separator = $options["content_separator"];
	$encoding = $options['file_encoding'];

	$error = 0;
	$data = array();
	$first = TRUE;
	$size = 0;

	if ($fromString) {
		$filename = $ko_path . 'my_images/temp_csv_dump_' . date('Y-m-d H:i:s') . '.csv';
		file_put_contents($filename, $csv);
	} else {
		$filename = $csv;
	}

	$fp = fopen($filename, 'r');
	while($parts = fgetcsv($fp, 0, $separator, ((!$content_separator) ? chr(8) : $content_separator))) {
		// Ignore empty lines
		if (sizeof($parts) == 1 && trim($parts[0]) == '') continue;

		//Encoding
		if($encoding == 'macintosh') {
			foreach($parts as $k => $v) {
				$parts[$k] = iconv('macintosh', 'ISO-8859-1', $v);
			}
		}
		else if($encoding == 'utf-8') {
			foreach($parts as $k => $v) {
				$parts[$k] = utf8_decode($v);
			}
		}

		if ($first) $size = sizeof($parts);
		$first = FALSE;

		if (sizeof($parts) != $size) $error = 1;

		if ($error) {
			return $error;
		}

		$data[] = $parts;
	}//foreach(content as line)

	if ($fromString) {
		unlink ($filename);
	}

	return $data;
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




function ko_detect_csv_parameters($data, &$parameters, &$parsedData) {
	$encoding = ko_detect_encoding($data);

	/*
	$lines = explode("\n", $data);
	//Remove empty lines
	foreach($lines as $k => $v) {
		if(trim($v) == '') unset($lines[$k]);
	}
	$data = implode("\n", $lines);
	*/

	if (!trim($data)) return FALSE;

	$candStringDels = array('"', "'", "");
	$candEntryDels = array(";", ",", "\t", "");

	$stronglyPossiblePairs = array();
	$possiblePairs = array();
	foreach ($candStringDels as $csd) {
		foreach ($candEntryDels as $ced) {
			$procLines = ko_parse_general_csv($data, array('separator' => $ced, 'content_separator' => $csd, 'encoding' => $encoding), TRUE);
			//$procLines = array_map(function($el)use($csd,$ced){return str_getcsv($el,$ced,(!$csd?chr(8):$csd));},$lines);

			$ok = TRUE;
			$lastLength = NULL;
			$startEnd = NULL;
			$encapsulated = TRUE;
			foreach ($procLines as $k => $procLine) {
				if ($k == 0) $lastLength = sizeof($procLine);
				else {
					if (sizeof($procLine) != $lastLength) {
						$ok = FALSE;
						break;
					}
				}
				if ($k == 0) $startEnd = substr($procLine[0], 0, 1);

				foreach ($procLine as $field) {
					if (substr($field, 0, 1) != $startEnd || substr($field, -1) != $startEnd) {
						$encapsulated = FALSE;
					}
				}
			}

			if ($ok) {
				if ($lastLength > 1 && !$encapsulated) {
					$stronglyPossiblePairs[] = array($csd, $ced);
				} else {
					$possiblePairs[] = array($csd, $ced);
				}
			}
		}
	}

	if (sizeof($stronglyPossiblePairs) == 1) {
		$csd = $stronglyPossiblePairs[0][0];
		$ced = $stronglyPossiblePairs[0][1];
	} else if (sizeof($stronglyPossiblePairs) > 1 && sizeof($filtered = array_filter($stronglyPossiblePairs, function($el){return strlen($el[0]) > 0;})) == 1) {
		$csd = $filtered[0][0];
		$ced = $filtered[0][1];
	} else if (sizeof($stronglyPossiblePairs) > 1) {
		$csd = $filtered[0][0];
		$ced = $filtered[0][1];
	} else if (sizeof($possiblePairs) == 1) {
		$csd = $possiblePairs[0][0];
		$ced = $possiblePairs[0][1];
	} else {
		return FALSE;
	}

	$parameters = array('content_separator' => $csd, 'separator' => $ced, 'file_encoding' => 'iso-8859-1');
	$parsedData = ko_parse_general_csv($data, $parameters, TRUE);

	return TRUE;
}





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
	$ext = strtolower(substr($img, strrpos($img, '.')));
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
 * @param $file string Absolute path to image file to be scaled
 * @param $max_dim number Size in pixels for the scaled down image
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
			if(!in_array(strtolower(substr($file, -4)), array('.gif', '.jpg', 'jpeg', '.png'))) continue;
			$hashes[] = md5($file.filemtime($BASE_PATH.'my_images/'.$file));
		}
	}
	@closedir($dh);

	//Check all cache files for corresponding hash from above
	if($dh = opendir($BASE_PATH.'my_images/cache/')) {
		while(($file = readdir($dh)) !== false) {
			if(!in_array(strtolower(substr($file, -4)), array('.gif', '.jpg', 'jpeg', '.png'))) continue;
			$hash = substr($file, 0, strpos($file, '_'));
			if(!in_array($hash, $hashes)) unlink($BASE_PATH.'my_images/cache/'.$file);
		}
	}
	@closedir($dh);
}//ko_pic_cleanup_cache()





/**
 * Plugin function to connect to a TYPO3 database
 * Connetion details for TYPO3 db are taken from settings which can be changed in the tools module
 */
function plugin_connect_TYPO3() {
  global $mysql_server, $BASE_PATH, $db_conn;

	if(!ko_get_setting('typo3_db')) return FALSE;

	//Get password and decrypt
	$pwd_enc = ko_get_setting('typo3_pwd');
	require_once($BASE_PATH.'inc/class.openssl.php');
	$crypt = new openssl('AES-256-CBC');
	$crypt->setKey(KOOL_ENCRYPTION_KEY);
	$pwd = trim($crypt->decrypt($pwd_enc));

  if($mysql_server != ko_get_setting('typo3_host')) {
    $db_conn = mysqli_connect(ko_get_setting('typo3_host'), ko_get_setting('typo3_user'), $pwd);
  }

  if(!mysqli_select_db($db_conn, ko_get_setting('typo3_db'))) {
    ko_die('Could not establish connection to the TYPO3 database: '.mysqli_error($db_conn));
  }
}//plugin_connect_TYPO3()



/**
 * Plugin function to connect to the current kOOL database again (called after plugin_connect_TYPO3())
 */
function plugin_connect_kOOL() {
  global $mysql_db, $mysql_server, $mysql_user, $mysql_pass, $db_conn;

  if($mysql_server != ko_get_setting('typo3_host') || $mysql_user != ko_get_setting('typo3_user')) {
    $db_conn = mysqli_connect($mysql_server, $mysql_user, $mysql_pass);
  }

  mysqli_select_db($db_conn, $mysql_db);
}//plugin_connect_kOOL()




/**
 *
 * Return an non-clickable html-link with qr-code
 *
 * @param $url
 * @param $text
 * @param string $title
 * @return string
 */
function ko_get_ical_link($url, $text, $title = '') {
	global $ko_path;

	$r = '<span><a href="javascript:ko_image_popup(\'' . $ko_path . 'inc/qrcode.php?s=' . base64_encode($url).'&h='.md5(KOOL_ENCRYPTION_KEY.$url).'&size=5\');"><i class="fa fa-qrcode" title="'.getLL('ical_qrcode').'"></i></a>';
	$r .= '&nbsp;&nbsp;';
	$r .= '<a href="'.$url.'"'.($title?' title="'.$title.'"':'').' onclick="return false;">'.$text.'</a></span>';

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
	global $BASE_URL, $BASE_PATH;

	$mapping = array(';' => '\;', ',' => '\,', "\r" => '', "\n" => '\n ');
	define('CRLF', chr(13).chr(10));

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
		if($_GET['title_no_item'] == 1) {
			if($r['zweck']) {
				$ical .= 'SUMMARY:'.strtr(trim($r['zweck']), $mapping).CRLF;
			} else {
				$ical .= 'SUMMARY:'.strtr(trim($r['item_name']), $mapping).CRLF;
			}
		} else {
			$ical .= 'SUMMARY:'.strtr(trim($r['item_name']), $mapping).($r['zweck'] ? (': '.strtr(trim($r['zweck']), $mapping)) : '').CRLF;
		}
		$desc = '';
		if($_SESSION["ses_username"] != "ko_guest" || $forceDetails === TRUE) {
			$desc .= $r["name"].($r["email"]?", ".$r["email"]:"").($r["telefon"]?", ".$r["telefon"]:"").CRLF;
			$desc .= $r['comments'].CRLF;
		}

		if($desc) {
			$allowed_html_tags_in_ical = "<b><strong><i><u><ul><li><a><mark>";
			$desc = ko_unhtml(strip_tags($desc, $allowed_html_tags_in_ical));
			require_once($BASE_PATH . 'inc/class.html2text.php');
			$html2text = new html2text($desc);
			$description_plain = $html2text->getText();

			$ical .= 'DESCRIPTION:'.strtr(trim($description_plain), $mapping).CRLF;
			$ical .= 'X-ALT-DESC;FMTTYPE=text/html:<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2//E"><HTML><BODY>'.strtr(trim($desc), $mapping).'</BODY></HTML>' . CRLF;
		}

		$ical .= "END:VEVENT".CRLF;
	}
	$ical .= "END:VCALENDAR".CRLF;

	return $ical;
}//ko_get_ics_for_res()



/**
 * Creates ICS string for rota and returns the string
 *
 * @param array $schedule schedule
 * @return string ICS feed as string
 */
function ko_get_ics_for_rota($schedule) {
	global $BASE_URL;

	$mapping = array(';' => '\;', ',' => '\,', "\r" => '', "\n" => '\n ');
	define('CRLF', chr(13).chr(10));

	//build ical file in a string
	$ical  = "BEGIN:VCALENDAR".CRLF;
	$ical .= "VERSION:2.0".CRLF;
	$ical .= "CALSCALE:GREGORIAN".CRLF;
	$ical .= "METHOD:PUBLISH".CRLF;
	$ical .= "PRODID:-//".str_replace("/", "", $HTML_TITLE)."//www.churchtool.org//DE".CRLF;

	foreach($schedule as $s) {
		$team = db_select_data('ko_rota_teams', "WHERE `id` = '{$s['team_id']}'", '*', '', '', TRUE, TRUE);
		//build ics string
		$ical .= "BEGIN:VEVENT".CRLF;
		//if($r['cdate'] != '0000-00-00 00:00:00') $ical .= "CREATED:".strftime("%Y%m%dT%H%M%S", strtotime($r["cdate"])).CRLF;
		//if($r['last_change'] != '0000-00-00 00:00:00') $ical .= "LAST-MODIFIED:".strftime("%Y%m%dT%H%M%S", strtotime($r["last_change"])).CRLF;
		$ical .= "DTSTAMP:".strftime("%Y%m%dT%H%M%S", time()).CRLF;
		$base_url = $_SERVER['SERVER_NAME'] ? $_SERVER['SERVER_NAME'] : $BASE_URL;
		$ical .= 'UID:rs'.$s['id'].'x'.$s['team_id'].'@'.$base_url.CRLF;
		if(intval(str_replace(':', '', $s['startzeit'])) >= 240000) $s['startzeit'] = '23:59:00';
		if(intval(str_replace(':', '', $s['endzeit'])) >= 240000) $s['endzeit'] = '23:59:00';
		if($s["startzeit"] == "00:00:00" && $s["endzeit"] == "00:00:00") {  //daily event
			$ical .= "DTSTART;VALUE=DATE:".strftime("%Y%m%d", strtotime($s["startdatum"])).CRLF;
			$ical .= "DTEND;VALUE=DATE:".strftime("%Y%m%d", strtotime(add2date($s["enddatum"], "tag", 1, TRUE))).CRLF;
		} else if($s['startzeit'] != '00:00:00' && $s['endzeit'] == '00:00:00') {  //No end time given so set it to midnight
			$ical .= 'DTSTART:'.date_convert_timezone(($s['startdatum'].' '.$s['startzeit']), 'UTC').CRLF;
			$ical .= 'DTEND:'.date_convert_timezone(($s['enddatum'].' 23:59:00'), 'UTC').CRLF;
		} else {
			$ical .= 'DTSTART:'.date_convert_timezone(($s['startdatum'].' '.$s['startzeit']), 'UTC').CRLF;
			$ical .= 'DTEND:'.date_convert_timezone(($s['enddatum'].' '.$s['endzeit']), 'UTC').CRLF;
		}
		if (strpos($s['event_id'], '-') !== FALSE) {
			$ical .= 'SUMMARY:'.strtr(trim($team['name']), $mapping).CRLF;

			$hh = array();
			foreach ($s['_helpers'] as $h) {
				if($h['is_free_text'] && empty($h['vorname'])) {
					$hh[] = "\"" . $h['name'] . "\"";
				} else {
					$hh[] = $h['vorname'] . " " . $h['nachname'];
				}
			}
			$desc = implode(', ', $hh);
			if($desc) $ical .= "DESCRIPTION:".strtr(trim($desc), $mapping).CRLF;
		} else {
			$event = db_select_data('ko_event', "WHERE `id` = '{$s['event_id']}'", '*', '', '', TRUE, TRUE);
			$eg = db_select_data('ko_eventgruppen', "WHERE `id` = '{$event['eventgruppen_id']}'", '*', '', '', TRUE, TRUE);
			$eventTitle = "{$event['title']} ({$eg['name']})";

			$ical .= 'SUMMARY:'.strtr(trim($team['name']), $mapping)." @ ".(strtr(trim($eventTitle), $mapping)).CRLF;
			if ($event['url']) $ical .= 'URL:'.strtr(trim($event['url']), $mapping).CRLF;

			$room_mapping = ['room' => $event["room"]];
			ko_include_kota(['ko_event']);
			kota_process_data("ko_event", $room_mapping, "list");
			$ical .= 'LOCATION:'.strtr(trim($room_mapping['room']), $mapping).CRLF;

			$desc = getLL('rota_ical_label_helpers').": ";
			$hh = array();
			foreach ($s['_helpers'] as $h) {
				if($h['is_free_text'] && empty($h['vorname'])) {
					$hh[] = "\"" . $h['name'] . "\"";
				} else {
					$hh[] = $h['vorname'] . " " . $h['nachname'];
				}			}
			$desc .= implode(', ', $hh);
			if($desc) $ical .= "DESCRIPTION:".strtr(trim($desc), $mapping).CRLF;
		}

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
 * @param string $text 			the html code to appear in the tooltip
 * @param string $position 		either top, right, bottom, left or auto
 * @param string $container 	set the container to which the tooltip will be attached
 * @return string 				returns the html attributes which invoke a bootstrap3 tooltip
 */
function ko_get_tooltip_code($text, $position='auto', $container='body') {
	return ' data-toggle="tooltip" title="' . str_replace('"', '&quot;', $text) . '" data-html="true" data-container="' . $container . '" data-placement="' . $position . '" ';
}


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
		$link = '<a href="#" ' . ko_get_tooltip_code($text) . '><span class="glyphicon glyphicon-info-sign"></span></a>';
	}
	//Create link to online documentation
	else {
		if($help['url']) {
			if($ttparams['linkClass']) $class = $ttparams['linkClass'] ? $ttparams['linkClass'] : 'pull-right';
			$link = '<a href="'.$help['url'].'" target="_blank" class="'.$linkClass.'"><span alt="help" title="'.getLL("help_link_title").'" class="glyphicon glyphicon-info-sign"></span></a>';
		}
		else if($help['t3_page']) {
			$href  = 'http://www.churchtool.org/?id='.$help["t3_page"];
			$href .= '&L='.$map_lang[$help["language"]];
			if($help["t3_content"]) $href .= "#c".$help["t3_content"];

			$link = '<a href="'.$href.'" target="_blank" class="pull-right"><span alt="help" title="'.getLL("help_link_title").'" class="glyphicon glyphicon-info-sign"></span></a>';
		} else {
			return FALSE;
		}
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
			${"sort_".str_replace(':', '_', $col)}[$i] = strtolower($col_value);
		}
	}
	foreach($sort_col as $i => $col) {
		$sort[] = '$sort_'.str_replace(':', '_', $col).', SORT_'.strtoupper($sort_order[$i]);
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
      foreach($d as $dk => $dv) $ldap_entry[$i][$dk] = utf8_encode($dv);
    }
    else if(!$edit && trim($d) == '') unset($ldap_entry[$i]);  //Unset empty values if a new LDAP entry is to be made
    else if($edit && trim($d) == '') $ldap_entry[$i] = array();  //Set empty values to array(), so they get deleted in LDAP (when editing)
    else $ldap_entry[$i] = utf8_encode($d);  //Encode normal values with UTF-8
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
			else $data[$i] = utf8_encode($d);
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
	$query = "SELECT `value` FROM `ko_settings` WHERE `key` = 'cache_sms_balance'";
	$result = mysqli_query(db_get_link(), $query);
	$value = mysqli_fetch_assoc($result);
	return $value["value"];
}




/**
 * Send SMS message using aspsms.net
 */
function send_aspsms($recipients, $text, $from, &$num, &$credits, &$log_id) {
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

	if(!is_array($recipients)) $recipients = explode(',', $recipients);
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
	ko_log('sms_sent', $log_message, $log_id);

	set_cache_sms_balance($sms->showCredits());

	return TRUE;
}//send_aspsms()




/**
 * Sendet SMS-Mitteilung
 */
function send_sms($recipients, $text, $from, $climsgid, $msg_type, &$success, &$done, &$problems, &$charges, &$error_message, &$log_id) {
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
		ko_log("sms_sent", $log_message, $log_id);

		return TRUE;
	}//if(sms->auth())
	else {
		return FALSE;
	}
}//send_sms()


/**
 * Send message through Telegram using API
 *
 * @param array $recipients persons with telegram_id
 * @param String $text Message to send to the telegram user
 *
 * @return Boolean If Call result ok
 */
function send_telegram_message($recipients, $text) {
	global $BASE_PATH;

	$notifier = koNotifier::Instance();
	require_once($BASE_PATH.'inc/TelegramBot/bot.php');

	try {
		$bot = Bot::getInstance(
			ko_get_setting('telegram_token'),
			ko_get_setting('telegram_botid'),
			ko_get_setting('telegram_botname'),
			0);

		$logRecipients = [];
		foreach($recipients AS $recipient) {
			try {
				$logRecipient = [
					"id" => $recipient['id'],
					"name" => $recipient['vorname'] . " " . $recipient['nachname'],
					"telegram_id" => $recipient['telegram_id'],
				];

				$bot->sendNotification($recipient['telegram_id'], $text);
				$notifier->addTextInfo(getLL("leute_telegram_message_sent"));
				$logRecipient["status"] = "ok";
			}
			catch (Exception $e) {
				$notifier->addTextError(getLL('leute_telegram_message_error'));
				$logRecipient["status"] = $e->getMessage();
			}

			$logRecipients[] = $logRecipient;
		}

		$logMessage = json_encode_latin1($logRecipients) . " ### " . $text;
		$bot->addLog("sent_message", $logMessage);
	} catch (Exception $e) {
		$notifier->addTextError($e);
		return FALSE;
	}
}


/**
 * Used instead of telegram webhook (eg. for debugging)
 *
 * @return bool
 */
function ko_task_get_new_telegram_registrations() {
	global $BASE_PATH;

	require_once($BASE_PATH.'inc/TelegramBot/bot.php');

	try {
		$bot = Bot::getInstance(
			ko_get_setting('telegram_token'),
			ko_get_setting('telegram_botid'),
			ko_get_setting('telegram_botname'),
			ko_get_setting('telegram_updateid'),
			"scheduler"
		);
		$bot->processMessages();
		ko_set_setting('telegram_updateid', $bot->getLastUpdateId());
	} catch (Exception $e) {
		koNotifier::Instance()->addTextError($e);
		return FALSE;
	}
}

/**
 * Create a Deep Linking URL with usertoken to start communication with telegram bot.
 *
 * @param $userid
 * @param bool|FALSE $html
 * @return string
 */
function ko_create_telegram_link($userid, $html = FALSE) {
	$usertoken = substr(md5($userid . KOOL_ENCRYPTION_KEY),10,6);
	$url = "https://telegram.me/" . ko_get_setting('telegram_botid') . "?start=" . $usertoken;

	if($html) {
		return "<a href=\"".$url."\" target=\"_new\">" . $usertoken . "</a>";
	}

	return $url;
}


/**
 * ezmlm mailinglist management
 */
function ko_ezmlm_subscribe($list, $moderator, $email) {
	if($list == "" || $moderator == "" || !check_email($email)) return FALSE;
	ko_send_mail($moderator, str_replace("@", "-subscribe-".str_replace("@", "=", $email)."@", $list), ' ', ' ');
}//ko_ezmlm_subscribe()

function ko_ezmlm_unsubscribe($list, $moderator, $email) {
	if($list == "" || $moderator == "" || !check_email($email)) return FALSE;
	ko_send_mail($moderator, str_replace("@", "-unsubscribe-".str_replace("@", "=", $email)."@", $list), ' ', ' ');
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


	// cleanup old ko_log entries
	db_query("UPDATE ko_log set request_data='' WHERE date < DATE_SUB(NOW(),INTERVAL 1 YEAR)");
	db_query("OPTIMIZE TABLE ko_log");

	// cleanup old ko_mailing_mails
	db_query("UPDATE ko_mailing_mails set body='' WHERE crdate < DATE_SUB(NOW(),INTERVAL 1 YEAR)");
	db_query("OPTIMIZE TABLE ko_mailing_mails");



	//Delete unused group datafields, only non-global and non-presets
	$datafields = db_select_data('ko_groups_datafields', "WHERE `reusable` = '0' AND `preset` = '0'");
	$delIDs = array();
	foreach($datafields as $df) {
		$numGroups = db_get_count('ko_groups', 'id', "AND `datafields` LIKE '%".$df['id']."%'");
		if($numGroups == 0) {
			$delIDs[] = $df['id'];
			ko_log_diff('del_datafield', $df);
		}
	}
	if(sizeof($delIDs) > 0) {
		db_delete_data('ko_groups_datafields', "WHERE `id` IN (".implode(',', $delIDs).")");
		db_delete_data('ko_groups_datafields_data', "WHERE `datafield_id` IN (".implode(',', $delIDs).")");
	}


	//Delete rota scheduling entries for deleted teams
	// (should not happen, as these entries get deleted when team is deleted)
	$teams = db_select_data('ko_rota_teams', "WHERE 1");
	$teamIDs = array_keys($teams);
	if(sizeof($teamIDs) > 0) {
		$entries = db_select_data('ko_rota_schedulling', "WHERE `team_id` NOT IN (".implode(',', $teamIDs).")");
		if(sizeof($entries) > 0) {
			foreach($entries as $entry) {
				db_delete_data('ko_rota_schedulling', "WHERE `team_id` = '".$entry['team_id']."' AND `event_id` = '".$entry['event_id']."'");
				ko_log_diff('rota_del_schedule', $entry);
			}
		}
	}
}//ko_task_delete_old_downloads()





/**
 * Scheduler task: Import/update events for event groups with iCal import URL
 */
function ko_task_import_events_ical() {
	global $ko_path, $BASE_PATH;

	require_once($BASE_PATH.'daten/inc/daten.inc');

	ko_daten_import_absences();

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
	global $MAIL_TRANSPORT;

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
					$termIdsString = ko_get_userpref('-1', substr($value, 4), 'daten_taxonomy_filter');
				}
				else {
					$egIdsString = ko_get_userpref($_SESSION['ses_userid'], $value, 'daten_itemset');
					$termIdsString = ko_get_userpref($_SESSION['ses_userid'], $value, 'daten_taxonomy_filter');
				}

				if ($egIdsString === null) {
					$events = null; // TODO: maybe add warning that preset was not found
				}
				else {
					$egIdsString = $egIdsString[0]['value'];
					$zWhere = (trim($egIdsString) == '') ? '' : ' AND `ko_event`.`eventgruppen_id` in (' . $egIdsString . ')';

					if(!empty($termIdsString[0]['value'])) {
						$terms = explode(",", $termIdsString[0]['value']);
						$eventIds_filter_by_term = [];

						foreach($terms AS $term) {
							$child_terms = ko_taxonomy_get_terms_by_parent($term);
							$child_terms[$term]['id'] = $term;
							foreach($child_terms AS $child_term) {
								$eventIds_filter_by_term = array_merge(ko_taxonomy_get_nodes_by_termid($child_term['id'], "ko_event"), $eventIds_filter_by_term);
							}
						}
						$zWhere .= " AND ko_event.id IN (". implode(",", array_column($eventIds_filter_by_term,"id")) . ")";
					}
				}

				break;
			case 'TERM': // taxonomy term
				$child_terms = ko_taxonomy_get_terms_by_parent($value);
				$child_terms[$value]['id'] = $value;
				$eventIds_filter_by_term = [];
				foreach($child_terms AS $child_term) {
					$eventIds_filter_by_term = array_merge(ko_taxonomy_get_nodes_by_termid($child_term['id'], "ko_event"), $eventIds_filter_by_term);
				}

				$zWhere = " AND ko_event.id IN (". implode(",", array_column($eventIds_filter_by_term,"id")) . ")";

				break;
			default:
				break;
		}


		if ($reminder['type'] == 1) {
			// Set db query filter that selects those events which correspond to the deadline (and all 'overdue' events by 23 hours)
			$deadline = $reminder['deadline'];
			if ($reminderId !== null) {
				$limit = ' limit 1';
				if ($deadline >=0) {
					$order = ' order by enddatum asc, endzeit asc';
					$timeFilterEvents = " AND TIMESTAMPDIFF(HOUR,CONCAT(CONCAT(`ko_event`.`enddatum`, ' '), `ko_event`.`endzeit`),NOW()) >= " . $deadline;
				}
				else {
					$order = ' order by startdatum asc, startzeit asc';
					$timeFilterEvents = " AND TIMESTAMPDIFF(HOUR,NOW(),CONCAT(CONCAT(`ko_event`.`startdatum`, ' '), `ko_event`.`startzeit`)) <= " . abs($deadline);
				}
			}
			else {
				$limit = '';
				$order = '';
				if ($deadline >=0) {
					$timeFilterEvents = " AND TIMESTAMPDIFF(HOUR,CONCAT(CONCAT(`ko_event`.`enddatum`, ' '), `ko_event`.`endzeit`),NOW()) >= " . $deadline . " AND TIMESTAMPDIFF(HOUR,CONCAT(CONCAT(`ko_event`.`enddatum`, ' '), `ko_event`.`endzeit`),NOW()) <= " . ($deadline + 23);
				}
				else {
					$timeFilterEvents = " AND TIMESTAMPDIFF(HOUR,NOW(),CONCAT(CONCAT(`ko_event`.`startdatum`, ' '), `ko_event`.`startzeit`)) <= " . abs($deadline) . " AND TIMESTAMPDIFF(HOUR,NOW(),CONCAT(CONCAT(`ko_event`.`startdatum`, ' '), `ko_event`.`startzeit`)) >= " . (abs($deadline) - 23);
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

					$res = db_select_data('ko_leute', "where `groups` like '%" . $recipientFromDBGroup . "%' AND `deleted` = '0'".ko_get_leute_hidden_sql());
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


			//Set replyTo email from reminder or info_email
			if(check_email($reminder['replyto_email'])) {
				$replyTo = $reminder['replyto_email'];
			} else {
				$replyTo = [];
			}

			//Replace person placeholders if recipients are only email addresses and not kOOL addresses
			$textWithoutPersonPlaceholders = preg_replace('/###r_.*###/', '', $text);
			$textWithoutPersonPlaceholders = preg_replace('/###s_.*###/', '', $textWithoutPersonPlaceholders);
			$subjectWithoutPersonPlaceholders = preg_replace('/###r_.*###/', '', $subject);
			$subjectWithoutPersonPlaceholders = preg_replace('/###r_.*###/', '', $subjectWithoutPersonPlaceholders);

			// Send mails
			$done = array();
			$failed = array();
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

							$result = ko_send_html_mail(ko_get_setting('info_email'), $mailAddress, $replacedSubject, $replacedText, [], [], [], $replyTo);
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
					$replacedSubject = preg_replace($eventPatterns[$event['id']], $eventReplacements[$event['id']], $subjectWithoutPersonPlaceholders);
					$replacedText = preg_replace($eventPatterns[$event['id']], $eventReplacements[$event['id']], $textWithoutPersonPlaceholders);

					if ($reminder['action'] == 'email') {
						if (array_key_exists($recipientByAddress, $done)) continue;

						$result = ko_send_html_mail(ko_get_setting('info_email'), $recipientByAddress, $replacedSubject, $replacedText, [], [], [], $replyTo);
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
			if (sizeof($events) > 0) {
				ko_log('send_reminders', 'sent the following reminders' . ($reminderId === null ? '' : ' (testmail)') . ' :: reminder: ' . $reminder['id'] . '; events: ' . implode(',', array_keys($events)) . '; people success: ' . implode(',', $done) . '; people failed: ' . implode(',', $failed) . '; subject: ' . $subject . '; text: ' . $text);
			}
		}
		else if ($reminder['type'] == 2) {
			// TODO: implement leute functionality
		}
	}

	return $done;

}//ko_task_reminder()


function ko_task_update_google_cloud_printers() {
	ko_update_google_cloud_printers();
}


function ko_task_vesr_import() {
	$report = array('general' => '', 'mails' => array());
	$log = '';
	$vesrFiles = array();

	ko_vesr_process_emails($vesrFiles, $report, $log);

	$log .= ';' . $report['general'] . ';';
	if (sizeof($report['mails']) == 0) {
		$log .= 'no mails on server';
	} else {
		ko_vesr_send_emailreport($report, 'v11', $vesrFiles, TRUE);
	}

	// log
	ko_log('vesr_mail_import', $log);
}

function ko_task_vesr_camt_import() {
	global $BASE_PATH;

	$processor = ko_vesr_process_camt_payments();

	$log = '';
	if (sizeof($processor->getProcessedData()) == 0) {
		$log .= 'no files on server';
	} else {
		$vesrFiles = array();
		foreach ($processor->getProcessedData() as $entry) {
			$vesrFiles[$BASE_PATH.'my_images/camt/done/'.$entry['message']->getCamtFile()] = basename($entry['message']->getCamtFile());
		}

		$reportAttachment = ko_vesr_camt_overview($processor->getDoneTotal(), $processor->getDoneRows(), FALSE, TRUE);
		$report['mails'][0]['attachments'][] = getLL('filename') . ":<br>" . $reportAttachment;
		$reportfile = ko_vesr_create_reportattachment($vesrFiles, $processor->getDoneTotal(), $processor->getDoneRows(), "camt");
		$vesrFiles[] = Swift_Attachment::newInstance($reportfile->Output('','S'),'report_esr_import.pdf','application/pdf');
		ko_vesr_send_emailreport($report, 'camt', $vesrFiles, TRUE);
	}

	// log
	ko_log('vesr_camt_import', $log);
}


/**
 * Send E-Mail with attachments to recipients set in vesr_import_email_report_address
 *
 * @param array $report general information for the report
 * @param string $type camt or v11
 * @param array $vesrFiles list of files to attach
 * @param bool $runByTask switch if import was manuelly run or via task
 */
function ko_vesr_send_emailreport($report, $type, $vesrFiles, $runByTask = FALSE) {
	global $BASE_URL;

	$mailContent = $report['general'] . "\n";

	foreach ($report['mails'] as $mail) {
		if(!empty($mail['title'])) {
			$mailContent .= '<div><h3>' . getLL('mailing_header_subject') . ': ' . $mail['title'] . '</h3><br>';
		}
		foreach ($mail['attachments'] as $attachment) {
			$mailContent .= '<div style="margin-left:20px;">' . str_replace('h2', 'h4', $attachment) . '</div>'; // replace h2 titles by h4 titles
		}
		$mailContent .= '</div>';
	}

	if ($type == 'camt') {
		if ($runByTask) {
			$mailText = sprintf(getLL('vesr_camt_import_email_report_content_automatic'), $BASE_URL, $mailContent);
		} else {
			$mailText = sprintf(getLL('vesr_camt_import_email_report_content_manuell'), $BASE_URL, $mailContent);
		}
	} else {
		if ($runByTask) {
			$mailText = sprintf(getLL('vesr_import_email_report_content_automatic'), $BASE_URL, $mailContent);
		} else {
			$mailText = sprintf(getLL('vesr_import_email_report_content_manuell'), $BASE_URL, $mailContent);
		}
	}

	$koVesrReportAddresses = ko_get_setting('vesr_import_email_report_address');
	foreach(explode(',', $koVesrReportAddresses) as $adr) {
		$adr = trim($adr);
		if(!$adr) continue;
		if(!check_email($adr)) continue;

		// send report
		ko_send_html_mail(
			ko_get_setting('info_email'),
			$adr,
			getLL('vesr_import_email_report_subject_' .  (($runByTask == TRUE) ? "automatic" : "manuell")) . ' ' . date('Y-m-d H:i:s'),
			$mailText,
			$vesrFiles
		);
	}
}


//TODO This is here for compatibility. Remove if it's nowhere used anymore
function ko_vesr_get_camt_uid(\LPC\LpcEsr\CashManagement\Message $message) {
	return $message->getUniqueId();
}


function ko_vesr_process_camt_payments() {
	global $BASE_PATH;

	$host = ko_get_setting('camt_import_host');
	$port = ko_get_setting('camt_import_port');
	$username = ko_get_setting('camt_import_user');
	$privateKey = ko_get_setting('camt_import_private_key');
	$processingFolder = $BASE_PATH.'my_images/camt/';

	$reader = new \LPC\LpcEsr\CashManagement\Reader($host, $port);
	$reader->setPrivateKey($privateKey);
	$reader->setMessageFolder($processingFolder);
	$reader->setUsername($username);

	$processor = new \LPC\LpcEsr\CashManagement\koProcessor;
	$reader->registerProcessor($processor);

	try {
		$reader->readAll();
	} catch (Exception $e) {
		ko_log('camt_parse_error', 'Error while reading camt payments'. $e->getMessage());
	}

	return $processor;
}

/**
 * Fetch all mails from mailbox (configured in /admin/index.php?action=vesr_settings),
 * check for v11-Attachments and process new payments.
 *
 * @param array &$vesrFiles list of attachments for email report
 * @param array &$report information about processed data and attachments
 * @param string &$log for debugging, saved in ko_log
 */
function ko_vesr_process_emails(&$vesrFiles, &$report, &$log) {
	global $BASE_PATH, $ko_path;

	$koVesrDataPath = $BASE_PATH.'my_images/v11/';
	$koVesrImapHost = ko_get_setting('vesr_import_email_host');//'{dedi1471.your-server.de:993/imap/ssl}INBOX';
	$koVesrImapUser = ko_get_setting('vesr_import_email_user');
	$koVesrImapPassEnc = ko_get_setting('vesr_import_email_pass');
	// decrypt password
	require_once($BASE_PATH.'inc/class.openssl.php');
	$crypt = new openssl('AES-256-CBC');
	$crypt->setKey(KOOL_ENCRYPTION_KEY);
	$koVesrImapPass = trim($crypt->decrypt($koVesrImapPassEnc));
	$koVesrImapSSL = ko_get_setting('vesr_import_email_ssl');
	$koVesrImapPort = ko_get_setting('vesr_import_email_port');

	//Get from email
	$ssl = ($koVesrImapSSL==1) ? '/ssl' : '';
	$cert = '/novalidate-cert';
	$folder = $folder = 'INBOX';
	$imap = imap_open('{'."$koVesrImapHost:$koVesrImapPort/pop3$ssl$cert"."}$folder",$koVesrImapUser,$koVesrImapPass);
	if($imap === FALSE) {
		print_r(imap_errors());
		$report['general'][] = sprintf(getLL('vesr_import_email_no_connection'), $koVesrImapHost);
		$log .= "can't connect to host" . $koVesrImapHost;
		return;
	}

	$imap_status = imap_check($imap);
	$num_mails = $imap_status->Nmsgs;

	if($num_mails > 0) {
		$response = imap_fetch_overview($imap, '1:'.$num_mails);
		foreach($response as $msg) {
			$reportMail = array('title' => $msg->subject, 'attachments' => array());
			$log .= $msg->subject . ':{';
			$htmlmsg = $plainmsg = $charset = '';
			$attachments = array();
			ko_vesr_getmsg($imap,$msg->msgno,$charset,$htmlmsg,$plainmsg,$attachments);

			// check if mail has attachments
			if (sizeof($attachments) != 0) {
				foreach ($attachments as $name => $attachment) {
					// create a unique filename
					$saveName = 'auto_' . date('Ymd_His') . '_' . sizeof($vesrFiles) . '_' . $name;
					// process attachment
					if (ko_vesr_check_attachment($name, $attachment)) {
						file_put_contents($koVesrDataPath.$saveName, $attachment);

						// check if there is already a file with the exact same content
						if (ko_vesr_is_duplicate_file($saveName)) {
							$newSaveName = 'duplicate_' . $saveName;
							rename($koVesrDataPath.$saveName, $koVesrDataPath.$newSaveName);
							$saveName = $newSaveName;
							$reportMail['attachments'][] = getLL('filename') . ': ' . $name . ' -- ' . getLL('error_admin_21');
							$log .= $saveName . ': ' . getLL('error_admin_21') . ', ';
						} else {
							$error = ko_vesr_import ($koVesrDataPath.$saveName, $vesr_data, $vesr_done);
							if ($error > 0) {
								$reportMail['attachments'][] = getLL('filename') . ': ' . $name . ' -- ' . getLL('error_admin_' . $error);
								$log .= $saveName . ': ' . getLL('error_admin_' . $error) . ', ';
							}
							else {
								$reportAttachment = ko_vesr_v11_overview($vesr_data, $vesr_done, false, true);
								$reportMail['attachments'][] = getLL('filename') . ': ' . $name . "<br>" . $reportAttachment;
								// delete local copy of attachment
								$log .= $saveName . ': success,see import entry, ';
							}
						}
					}
					else {
						$reportMail['attachments'][] = $name . ': invalid format, ';
					}
					$vesrFiles[$koVesrDataPath.$saveName] = $name;
				}
			}
			else {
				$reportMail['attachments'][] = getLL('vesr_import_email_no_attachments');
				$log .= 'no attachments';
			}
			$report['mails'][] = $reportMail;
			$log .= '}, ';
			imap_delete($imap, $msg->msgno);
		}

		$total = $data['totals'];
		$total['total'] = $data['total'];
		$reportfile = ko_vesr_create_reportattachment($vesrFiles, $total, $vesr_done, "v11");
		$vesrFiles[] = Swift_Attachment::newInstance($reportfile->Output('','S'),'report_esr_import.pdf','application/pdf');
// 		$vesrFiles = array_merge($vesrFiles, $reportfile);
	}
	imap_close($imap, CL_EXPUNGE);
}

/**
 * @param array $vesrfiles
 * @param array $done
 * @param string $importtype camt || v11
 * @param array|null $issues if set, these issues are appended, or else all pending issues (from ko_vesr/ko_vesr_camt) are appended
 *
 * @return TCPDF
 */
function ko_vesr_create_reportattachment($vesrfiles, $total, $done, $importtype, $issues = null) {
	global $BASE_PATH, $ko_path, $DATETIME;

	require_once($BASE_PATH . 'inc/tcpdf/tcpdf.php');
	$pdf = new TCPDF('L', 'mm', 'A4', false, 'ISO-8859-1', false);
	$pdf->SetAutoPageBreak(TRUE, 10);
	$pdf->SetMargins(10, 10, 10);
	$pdf->AddPage();
	$pdf->SetFont('Arial', '', 10);
	$pdf->SetLineWidth(0.3);
	$pdf->setPageMark();

	$pdf->writeHTML("<h1>Zahlungsimport " . ko_get_setting('info_name') ."</h1>", true);
	$pdf->writeHTML('<b>Erstellt:</b> ' . date('d.m.Y H:i', time()), true);
	$pdf->Ln();

	$pdf->writeHTML('<b>Verarbeitete Datei:</b>', true);
	foreach($vesrfiles AS $file) {
		$pdf->writeHTML($file, true);
	}

	$pdf->Ln();
	$c = '';
	$c .= '<h2>'.getLL('vesr_title_total').'</h2>';
	$c .= '<table cellpadding="1" border="1"><thead><tr nobr="true">';
	$c .= '<td width="300"></td>';
	foreach(array_keys($total) as $type) {
		$c .= '<th width="100">'.getLL('vesr_overview_title_'.$type).'</th>';
	}
	$c .= '</tr></thead>';
	foreach(['num','amount','fees','rejects'] as $v) {
		$c .= '<tr nobr="true"><th width="300">'.getLL('vesr_total_'.$v).'</th>';
		foreach($total as $type => $sums) {
			$c .= '<td width="100">'.($v == 'amount' || ($v == 'fees' && $importtype != 'v11') ? number_format($sums[$v],2,'.',"'") : $sums[$v]).'</td>';
		}
		$c .= '</tr>';
	}
	$c .= '</table>';
	$pdf->writeHTML($c);

	foreach ($done as $type => $d) {
		if (is_array($d['ok']) && sizeof($d['ok']) > 0) {
			if (function_exists("my_vesr_camt_import_summary_{$type}")) {
				$fcn = "my_vesr_camt_import_summary_{$type}";
			} else if (function_exists("ko_vesr_camt_import_summary_{$type}")) {
				$fcn = "ko_vesr_camt_import_summary_{$type}";
			} else {
				$fcn = NULL;
			}

			if ($fcn != NULL) {
				list($header, $rows) = call_user_func_array($fcn, array($d['ok']));
				$table = '<h2>'.getLL("vesr_{$type}") . ': ' . getLL("vesr_title_ok").'</h2>';
				$table.= "<table cellpadding=\"1\" border=\"1\" style=\"width:100%\"><thead><tr nobr=\"true\">";

				$skipCols = ['valutadate', 'source'];
				$colWidths = [
					'amount' => 15,
					'comment' => 'x3',
				];
				$fixsum = $dynsum = 0;
				$tableWidth = 277;
				foreach($header as $key => $headline) {
					if(in_array($key, $skipCols)) continue;
					if(isset($colWidths[$key])) {
						if(is_string($colWidths[$key])) {
							$dynsum += substr($colWidths[$key],1);
						} else {
							$fixsum += $colWidths[$key];
						}
					} else if(substr($key,-4) == 'date') {
						$colWidths[$key] = 20;
						$fixsum += 20;
					} else {
						$dynsum++;
					}
				}
				$factor = ($tableWidth-$fixsum)/$dynsum;

				foreach ($header AS $key => $headline) {
					if(in_array($key, $skipCols)) continue;
					$colWidths[$key] = isset($colWidths[$key]) ? (is_string($colWidths[$key]) ? $factor*substr($colWidths[$key],1) : $colWidths[$key]) : $factor;
					$table.= '<th width="'.$colWidths[$key].'mm"><b>'. $headline ."</b></th>";
				}
				$table.= "</tr></thead>";

				foreach ($rows AS $row) {
					$table.= "<tr nobr=\"true\">";
					foreach ($row as $key => $cell) {
						if(in_array($key, $skipCols)) continue;

						if ($key == 'person') {
							$cell = strip_tags($cell);
						}
						else if($key == 'account') {
							$accountID = intval($cell);
							if($accountID) {
								$account = db_select_data('ko_donations_accounts', "WHERE `id` = '$accountID'", '*', '', '', TRUE);
								if($account['number'] || $account['name']) {
									$cell = trim($account['number'].' '.$account['name']);
								}
							}
						}
						else if($key == 'date' && preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}\z/', $cell)) {
							$cell = strftime($DATETIME['dmY'], strtotime($cell));
						}

						$table .= '<td width="'.$colWidths[$key].'mm">'.nl2br($cell).'</td>';
					}
					$table.= "</tr>";
				}
				$table.= "</table>";

				$pdf->writeHTML($table, true);
			}
		}
	}

	if ($importtype == "camt") {
		$koVesrDataPath = $BASE_PATH.'my_images/camt/';

		ko_include_kota(array('ko_vesr_camt'));

		if($issues === null) {
			$ids = array();
			foreach ($done as $type => $d) {
				foreach ($d as $reason => $ps) {
					if ($reason != 'ok') {
						foreach ($ps as $p) {
							if ($p['id']) $ids[] = $p['id'];
						}
					}
				}
			}
			if (sizeof($ids) > 0) {
				$z_where = " AND `id` IN (" . implode(',', $ids) . ")";
			} else {
				$z_where = " AND 1=0";
			}
			$rows = db_get_count('ko_vesr_camt', 'id');
			$issues = db_select_data('ko_vesr_camt', "WHERE 1=1{$z_where}", '*', 'ORDER BY `crdate` DESC');
		} else {
			$rows = count($issues);
		}
		if($issues) {
			$table_problems = '<h2>' . getLL('vesr_camt_list_title') . '</h2>';


			$list = new kOOL_listview();
			$list->init('admin', 'ko_vesr_camt', array(), 1, 1000);
			$list->setTitle('');
			$list->setSort(FALSE);
			$list->setStats($rows, '', '', '', TRUE);
			$list->disableMultiedit();
			$list->disableHeader();
			$table_problems .= $list->render($issues, 'html_fetch');
		}
	} else if ($importtype == "v11") {
		$koVesrDataPath = $BASE_PATH.'my_images/v11/';

		ko_include_kota(array('ko_vesr'));
		if($issues === null) {
			$ids = array();
			foreach ($done as $type => $d) {
				foreach ($d as $reason => $ps) {
					if ($reason != 'ok') {
						foreach ($ps as $p) {
							if ($p['id']) $ids[] = $p['id'];
						}
					}
				}
			}
			if (sizeof($ids) > 0) {
				$z_where = " AND `id` IN (" . implode(',', $ids) . ")";
			} else {
				$z_where = " AND 1=0";
			}
			$rows = db_get_count('ko_vesr', 'id');
			$issues = db_select_data('ko_vesr', "WHERE 1=1{$z_where}", '*', 'ORDER BY `crdate` DESC');
		} else {
			$rows = count($issues);
		}

		if($issues) {
			$table_problems = '<h2>'.getLL('vesr_v11_list_title').'</h2>';

			$list = new kOOL_listview();
			$list->init('admin', 'ko_vesr', array(), 1, 1000);
			$list->setTitle('');
			$list->setSort(FALSE);
			$list->setStats($rows, '', '', '', TRUE);
			$list->disableMultiedit();
			$list->disableHeader();
			$table_problems .= $list->render($issues, 'html_fetch');
		}
	}

	$table_problems = preg_replace("/\n\t{0,}/mi", "", $table_problems); // get rid of unnecessary line breaks for mails
	$table_problems = str_replace('<div id="ko_listh_filterbox"></div>','',$table_problems);
	$table_problems = str_replace("<table ", "<table border=\"1\" cellpadding=\"2\"", $table_problems);
	$table_problems = str_replace("<tr ", "<tr nobr=\"true\"", $table_problems);
	$table_problems = str_replace("<td ", "<td nobr=\"true\"", $table_problems);
	$table_problems = str_replace("<th class=\"ko_list nowrap  \"", "<th nobr=\"true\" border=\"1\"", $table_problems);
	$pdf->writeHTML($table_problems);

	return $pdf;
}

function ko_vesr_check_attachment($name, $attachment) {
	if (strtolower(substr($name, -4)) != '.v11') {
		return false;
	}
	return TRUE;
}//ko_vesr_check_attachment()

function ko_vesr_is_duplicate_file($name) {
	global $BASE_PATH;

	$duplicate = FALSE;

	$path = $BASE_PATH . 'my_images/v11/';

	$h1 = hash_file("sha256", $path . $name);

	if ($handle = opendir($path)) {
		while (false !== ($file = readdir($handle))) {
			if ('.' === $file) continue;
			if ('..' === $file) continue;
			if ($file === $name) continue;
			if (substr($file, -3) != 'v11') continue;

			$h2 = hash_file("sha256", $path . $file);

			if ($h1 == $h2) {
				$duplicate = TRUE;
				break;
			}
		}
		closedir($handle);
	}

	return $duplicate;
}//ko_vesr_is_duplicate_file()

function ko_vesr_getmsg($mbox,$mid,&$charset,&$htmlmsg,&$plainmsg,&$attachments) {
	// input $mbox = IMAP stream, $mid = message id
	// output all the following:
	$htmlmsg = $plainmsg = $charset = '';
	$attachments = array();

	// HEADER
	$h = imap_header($mbox,$mid);
	// add code here to get date, from, to, cc, subject...

	// BODY
	$s = imap_fetchstructure($mbox,$mid);
	if (!$s->parts)  // simple
		ko_vesr_getpart($mbox,$mid,$s,0,$charset,$htmlmsg,$plainmsg,$attachments);  // pass 0 as part-number
	else {  // multipart: cycle through each part
		foreach ($s->parts as $partno0=>$p)
			ko_vesr_getpart($mbox,$mid,$p,$partno0+1,$charset,$htmlmsg,$plainmsg,$attachments);
	}
}

function ko_vesr_getpart($mbox,$mid,$p,$partno,&$charset,&$htmlmsg,&$plainmsg,&$attachments) {
	// $partno = '1', '2', '2.1', '2.1.3', etc for multipart, 0 if simple

	// DECODE DATA
	$data = ($partno)?
		imap_fetchbody($mbox,$mid,$partno):  // multipart
		imap_body($mbox,$mid);  // simple
	// Any part may be encoded, even plain text messages, so check everything.
	if ($p->encoding==4)
		$data = quoted_printable_decode($data);
	elseif ($p->encoding==3)
		$data = base64_decode($data);

	// PARAMETERS
	// get all parameters, like charset, filenames of attachments, etc.
	$params = array();
	if ($p->parameters)
		foreach ($p->parameters as $x)
			$params[strtolower($x->attribute)] = $x->value;
	if ($p->dparameters)
		foreach ($p->dparameters as $x)
			$params[strtolower($x->attribute)] = $x->value;

	// ATTACHMENT
	// Any part with a filename is an attachment,
	// so an attached text file (type 0) is not mistaken as the message.
	if ($params['filename'] || $params['name']) {
		// filename may be given as 'Filename' or 'Name' or both
		$filename = ($params['filename'])? $params['filename'] : $params['name'];
		// filename may be encoded, so see imap_mime_header_decode()
		$attachments[$filename] = $data;  // this is a problem if two files have same name
	}

	// TEXT
	if ($p->type==0 && $data) {
		// Messages may be split in different parts because of inline attachments,
		// so append parts together with blank row.
		if (strtolower($p->subtype)=='plain')
			$plainmsg .= trim($data) ."\n\n";
		else
			$htmlmsg .= $data ."<br><br>";
		$charset = $params['charset'];  // assume all parts are same charset
	}

	// EMBEDDED MESSAGE
	// Many bounce notifications embed the original message as type 2,
	// but AOL uses type 1 (multipart), which is not handled here.
	// There are no PHP functions to parse embedded messages,
	// so this just appends the raw source to the main message.
	elseif ($p->type==2 && $data) {
		$plainmsg .= $data."\n\n";
	}

	// SUBPART RECURSION
	if ($p->parts) {
		foreach ($p->parts as $partno0=>$p2)
			ko_vesr_getpart($mbox,$mid,$p2,$partno.'.'.($partno0+1),$charset,$htmlmsg,$plainmsg,$attachments);  // 1.2, 1.2.1, etc.
	}
}

function ko_vesr_camt_preview($camtImport) {
	global $KOTA, $ko_path, $BASE_PATH;

	include_once($BASE_PATH."inc/kotafcn.php");

	if (!is_array($KOTA['ko_donations'])) ko_include_kota(array('ko_donations'));
	$KOTA['ko_donations']['person']['list'] = 'FCN:kota_listview_people_link';

	$c = '';

	$num = 0;
	$showCols = array('kota_ko_vesr_camt_creation_date', 'kota_ko_vesr_camt_booking_date', 'kota_ko_vesr_camt_valuta_date', 'kota_ko_vesr_camt_amount', 'kota_ko_vesr_camt_charges', 'kota_ko_vesr_camt_currency', 'kota_ko_vesr_camt_p_name', 'kota_ko_vesr_camt_account_number', 'kota_ko_vesr_camt_participant', 'kota_ko_vesr_camt_refnumber', 'kota_ko_vesr_camt_reason', 'kota_ko_vesr_camt_note', 'kota_ko_vesr_camt_additional_information');

	$header = array();
	foreach ($showCols as $sc) {
		$header[] = getLL("$sc");
	}

	$c .= '<h2>' . getLL("vesr_preview_message") . '</h2>';
	$c .= '<table class="ko_list table table-bordered table-condensed"><tr class="row-info">';
	foreach ($header as $h) {
		$c .= '<th class="ko_list">'.$h.'</th>';
	}
	$c .= '</tr>';

	/* @var $message LPC\LpcEsr\CashManagement\Message */
	foreach ($camtImport as $message) {
		$rowclass = $num % 2 ? 'ko_list_odd' : 'ko_list_even';
		$c .= '<tr class="'.$rowclass.'">';
		$c .= '<td>'.$message->getCrdate()->format('d.m.Y').'</td>';
		$c .= '<td>'.$message->getBookingDate()->format('d.m.Y').'</td>';
		$c .= '<td>'.$message->getValutaDate()->format('d.m.Y').'</td>';
		$c .= '<td>'.$message->getAmount().'</td>';
		$c .= '<td>'.$message->getCharges().'</td>';
		$c .= '<td>'.$message->getCurrency().'</td>';
		$c .= '<td>'.utf8_decode($message->getDebtorName()).'</td>';
		$c .= '<td>'.$message->getAccountNumber().'</td>';
		$c .= '<td>'.$message->getParticipantNumber().'</td>';
		$c .= '<td>'.$message->getReferenceNumber().'</td>';
		$c .= '<td>'.utf8_decode($message->getPurpose()).'</td>';
		$c .= '<td>'.utf8_decode($message->getNote()).'</td>';
		$c .= '<td>'.utf8_decode($message->getAdditionalInformation()).'</td>';
		$c .= '</tr>';
		$num++;
	}
	$c .= '</table>';

	print $c;
}

/**
 * Return an tabulated overview of camt payments information about processing
 *
 * @param array $total summarized information
 * @param array $done list of camt payments grouped by module and status
 * @param bool $output print or return to caller
 * @param bool $reportIssues if TRUE, print table with unprocessed payments from $done
 * @return string HTML
 */
function ko_vesr_camt_overview($total, $done, $output=TRUE, $reportIssues=FALSE) {
	global $DATETIME, $ko_path, $BASE_PATH;

	include_once($BASE_PATH."inc/kotafcn.php");

	$c = '';

	$c .= '<h2>'.getLL('vesr_title_total').'</h2>';
	$c .= '<div class="row"><div class="col-md-6">';
	$c .= '<table class="table table-bordered table-condensed">';
	$c .= '<tr><td></td>';
	foreach(array_keys($total) as $type) {
		$c .= '<th class="bg-info">'.getLL('vesr_overview_title_'.$type).'</th>';
	}
	$c .= '</tr>';
	foreach(['num','amount','fees','rejects'] as $v) {
		$c .= '<tr><td class="bg-info"><b>'.getLL('vesr_total_'.$v).'</b></td>';
		foreach($total as $type => $sums) {
			$c .= '<td>'.($v == 'amount' || $v == 'fees' ? number_format($sums[$v],2,'.',"'") : $sums[$v]).'</td>';
		}
		$c .= '</tr>';
	}
	$c .= '</table></div></div>';

	foreach ($done as $type => $d) {
		$num = 0;

		if (is_array($d['ok']) && sizeof($d['ok']) > 0) {
			if (function_exists("my_vesr_camt_import_summary_{$type}")) {
				$fcn = "my_vesr_camt_import_summary_{$type}";
			} else if (function_exists("ko_vesr_camt_import_summary_{$type}")) {
				$fcn = "ko_vesr_camt_import_summary_{$type}";
			} else {
				$fcn = NULL;
			}

			if ($fcn != NULL) {

				list($header, $rows) = call_user_func_array($fcn, array($d['ok']));

				$c .= '<h2>'.getLL("vesr_{$type}") . ': ' . getLL("vesr_title_ok").'</h2>';
				$c .= '<table class="ko_list table table-bordered table-condensed"><tr class="row-info">';
				foreach ($header as $h) {
					$c .= '<th class="ko_list">'.$h.'</th>';
				}
				$c .= '</tr>';
				foreach ($rows as $row) {
					$rowclass = $num % 2 ? 'ko_list_odd' : 'ko_list_even';
					$c .= '<tr class="'.$rowclass.'">';
					foreach ($row as $key => $cell) {
						if($key == 'account') {
							$accountID = intval($cell);
							if($accountID) {
								$account = db_select_data('ko_donations_accounts', "WHERE `id` = '$accountID'", '*', '', '', TRUE);
								if($account['number'] || $account['name']) {
									$cell = trim($account['number'].' '.$account['name']);
								}
							}
						}
						else if(in_array($key, array('date', 'valutadate')) && preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}\z/', $cell)) {
							$cell = strftime($DATETIME['dmY'], strtotime($cell));
						}
						$c .= '<td>'.$cell.'</td>';
					}
					$c .= '</tr>';
					$num++;
				}
				$c .= '</table>';
			}
		}
	}

	if ($reportIssues) {
		ko_include_kota(array('ko_vesr_camt'));
		$ids = array();
		foreach ($done as $type => $d) {
			foreach ($d as $reason => $ps) {
				if ($reason != 'ok') {
					foreach ($ps as $p) {
						if ($p['id']) $ids[] = $p['id'];
					}
				}
			}
		}
		if (sizeof($ids) > 0) {
			$z_where = " AND `id` IN (" . implode(',', $ids) . ")";
		} else {
			$z_where = " AND 1=0";
		}
		if(db_get_count('ko_vesr_camt', 'id', $z_where) > 0) {
			$c .= '<h2>'.getLL('vesr_camt_list_title').'</h2>';
			$order = 'ORDER BY `crdate` DESC';
			$rows = db_get_count('ko_vesr_camt', 'id');
			$es = db_select_data('ko_vesr_camt', "WHERE 1=1{$z_where}", '*', $order);

			$list = new kOOL_listview();
			$list->init('admin', 'ko_vesr_camt', array(), 1, 1000);
			$list->setTitle('');
			$list->setSort(FALSE);
			$list->setStats($rows, '', '', '', TRUE);
			$list->disableMultiedit();
			$list->disableHeader();

			//TODO: Add payed-icons to billed_amount and amount columns if billing_id is set on vesr entry

			$c .= $list->render($es, 'html_fetch');
		}
	}

	if ($output) {
		print $c;
	}

	return $c;
}


/**
 * Return an tabulated overview of v11 payments information about processing
 *
 * @param array $data summarized information
 * @param array $done list of v11 payments grouped by module and status
 * @param bool $output print or return to caller
 * @param bool $reportIssues if TRUE, print table with unprocessed payments from $done
 * @return string HTML
 */
function ko_vesr_v11_overview($data, $done, $output=true, $reportIssues=false) {
	global $ko_path, $BASE_PATH;

	include_once($BASE_PATH."inc/kotafcn.php");

	$c .= '<h2>'.getLL('vesr_title_total').'</h2>';
	$c .= '<div class="row"><div class="col-md-6">';
	$c .= '<table class="table table-bordered table-condensed">';
	$c .= '<tr><td></td>';
	$totals = $data['totals'];
	$totals['total'] = $data['total'];
	foreach(array_keys($totals) as $type) {
		$c .= '<th class="bg-info">'.getLL('vesr_overview_title_'.$type).'</th>';
	}
	$c .= '</tr>';
	foreach(['num','amount','fees','rejects'] as $v) {
		$c .= '<tr><td class="bg-info"><b>'.getLL('vesr_total_'.$v).'</b></td>';
		foreach($totals as $type => $sums) {
			if(isset($sums[$v])) {
				$c .= '<td>'.($v == 'amount' || $v == 'fees' ? number_format($sums[$v],2,'.',"'") : $sums[$v]).'</td>';
			} else {
				$c .= '<td style="background:#eee;"></td>';
			}
		}
		$c .= '</tr>';
	}
	$c .= '</table></div></div>';

	foreach ($done as $type => $d) {
		$num = 0;

		if (is_array($d['ok']) && sizeof($d['ok']) > 0) {
			if (function_exists("my_vesr_v11_import_summary_{$type}")) {
				$fcn = "my_vesr_v11_import_summary_{$type}";
			} else if (function_exists("ko_vesr_v11_import_summary_{$type}")) {
				$fcn = "ko_vesr_v11_import_summary_{$type}";
			} else {
				$fcn = NULL;
			}

			if ($fcn != NULL) {

				list($header, $rows) = call_user_func_array($fcn, array($d['ok']));

				$c .= '<h2>'.getLL("vesr_{$type}") . ': ' . getLL("vesr_title_ok").'</h2>';
				$c .= '<table class="ko_list table table-bordered table-condensed"><thead><tr class="row-info">';
				foreach ($header as $h) {
					$c .= '<th class="ko_list">'.$h.'</th>';
				}
				$c .= '</tr></thead>';
				foreach ($rows as $row) {
					$rowclass = $num % 2 ? 'ko_list_odd' : 'ko_list_even';
					$c .= '<tr class="'.$rowclass.'">';
					foreach ($row as $cell) {
						$c .= '<td>'.$cell.'</td>';
					}
					$c .= '</tr>';
					$num++;
				}
				$c .= '</table>';
			}
		}
	}

	if ($reportIssues) {
		ko_include_kota(array('ko_vesr'));
		$ids = array();
		foreach ($done as $type => $d) {
			foreach ($d as $reason => $ps) {
				if ($reason != 'ok') {
					foreach ($ps as $p) {
						if ($p['id']) $ids[] = $p['id'];
					}
				}
			}
		}
		if (sizeof($ids) > 0) {
			$z_where = " AND `id` IN (" . implode(',', $ids) . ")";
		} else {
			$z_where = " AND 1=0";
		}
		if(db_get_count('ko_vesr', 'id', $z_where) > 0) {
			$c .= '<h2>'.getLL('vesr_v11_list_title').'</h2>';
			$order = 'ORDER BY `crdate` DESC';
			$rows = db_get_count('ko_vesr', 'id');
			$es = db_select_data('ko_vesr', "WHERE 1=1{$z_where}", '*', $order);

			$list = new kOOL_listview();
			$list->init('admin', 'ko_vesr', array(), 1, 1000);
			$list->setTitle('');
			$list->setSort(FALSE);
			$list->setStats($rows, '', '', '', TRUE);
			$list->disableMultiedit();
			$list->disableHeader();

			//TODO: Add payed-icons to billed_amount and amount columns if billing_id is set on vesr entry

			$c .= $list->render($es, 'html_fetch');
		}
	}

	if ($output) {
		print $c;
	}

	return $c;
}

function ko_vesr_import ($file, &$data, &$done, $reportOnly = false){
	global $PLUGINS;

	if (TRUE === ko_vesr_parse_v11(file_get_contents($file), $data)) {
		$done = array();
		foreach ($data['payment'] as $payment) {
			//Allow plugins to overwrite these calculations
			$handled = FALSE;
			$summary = $type = $status = NULL;
			$fun = $reportOnly ? 'my_vesr_v11_report_' : 'my_vesr_v11_payment_';
			foreach ($PLUGINS as $plugin) {
				if (function_exists($fun . $plugin['name'])) {
					list($handled, $type, $status, $summary) = call_user_func_array($fun . $plugin['name'], array($payment));
					if ($handled) break;
				}
			}
			if (!$handled) {
				$fun = $reportOnly ? 'ko_vesr_v11_report_' : 'ko_vesr_v11_payment_';
				foreach (array('donations') as $m) {
					if (function_exists($fun . $m)) {
						list($handled, $type, $status, $summary) = call_user_func_array($fun . $m, array($payment));
						if ($handled) break;
					}
				}
			}
			if (!$handled) {
				$type = 'none';
				$status = 'notFound';
			}
			$row = $payment;
			$row['type'] = $type;
			$row['reason'] = $status;
			$row['crdate'] = date('Y-m-d H:i:s');
			$row['cruser'] = $_SESSION['ses_userid'];
			foreach ($summary as $k => $v) {
				$row[$k] = $v;
			}
			if ($status != 'ok' & !$reportOnly) {
				$rowId = db_insert_data('ko_vesr', $row);
				$row['id'] = $rowId;
				ko_log_diff('new_vesr_row', $row);
			}

			$data['totals'][$type]['rejects'] += (boolean)$payment['reject'];
			$data['totals'][$type]['amount'] += $payment['amount'];
			$data['totals'][$type]['num']++;

			$done[$type][$status][] = $row;
		}

		ksort($data['totals']);
		if(isset($data['totals']['none'])) {
			$none = $data['totals']['none'];
			unset($data['totals']['none']);
			$data['totals']['none'] = $none;
		}
	} else {
		koNotifier::Instance()->addError(18);
		return 20;
	}
}

function ko_vesr_parse_v11($data, &$r) {
	$num = 0;
	$r = array();

	$lines = explode("\n", $data);
	if(sizeof($lines) < 2) {
		koNotifier::Instance()->addError(11);
		return FALSE;
	}


	foreach($lines as $line) {
		if(!$line) continue;

		if(strlen($line) < 127) {
			$r['error'][] = array('error' => 'noCode', 'code' => $line);
		}
		$row = ko_vesr_parse_v11_line($line);

		//Payment
		if(in_array($row['transaction'], array('002', '012', '102', '112'))) {
			$r['payment'][] = $row;
			$num++;
		}
		//Valid code but not handled
		else if(in_array($row['transaction'], array('005', '015', '105', '115', '008', '018', '108', '118', '202', '205'))) {
			$r['ignored'][] = $row;
			$num++;
		}
		//Summary entry
		else if(in_array($row['transaction'], array('999', '995'))) {
			$check = array();
			$check['amount'] = ((float)substr($line, 39, 12))/100;
			if($row['transaction'] == '995') $check['amount'] *= -1;
			$check['num'] = intval(substr($line, 51, 12));
			$check['fees'] = ((float)substr($line, 69, 9))/100;
		}
	}
	$r['total'] = $check;

	//Find rejects
	$rejects = 0;
	foreach($r as $k => $v) {
		foreach($v as $d) {
			if($d['reject']) $rejects++;
		}
	}
	$r['total']['rejects'] = $rejects;

	//Check
	if($check['num'] != $num) {
		koNotifier::Instance()->addError(20);
		return FALSE;
	}

	return TRUE;
}//ko_vesr_parse_v11()


function ko_vesr_parse_v11_line($line) {
	$row = array();
	$row['code'] = trim($line);
	$row['transaction'] = substr($line, 0, 3);
	$row['account'] = substr($line, 3, 2).'-'.substr($line, 5, 6).'-'.substr($line, 11, 1);
	$row['refnumber'] = substr($line, 12, 27);
	$row['amount'] = ((float)substr($line, 39, 10))/100;
	$row['bankreference'] = substr($line, 49, 10);
	$row['paydate'] = '20'.substr($line, 59, 2).'-'.substr($line, 61, 2).'-'.substr($line, 63, 2);
	$row['bankdate1'] = '20'.substr($line, 65, 2).'-'.substr($line, 67, 2).'-'.substr($line, 69, 2);
	$row['bankdate2'] = '20'.substr($line, 71, 2).'-'.substr($line, 73, 2).'-'.substr($line, 75, 2);
	$row['microfilm'] = substr($line, 77, 9);
	$row['reject'] = substr($line, 86, 1);
	$row['valutadate'] = '20'.substr($line, 87, 2).'-'.substr($line, 89, 2).'-'.substr($line, 91, 2);
	if($row['valutadate'] == '2000-00-00') $row['valutadate'] = $row['bankdate2'];
	if($row['valutadate'] == '2000-00-00') $row['valutadate'] = $row['bankdate1'];
	$row['tax'] = substr($line, 96, 4);

	return $row;
}//ko_vesr_parse_v11_line()

/**
 * Calculates recursive modulo10 as check for ref numbers
 *
 * @param $ref_nr string
 * @return int Control number for given RefNr
 */
function ko_vesr_modulo10($ref_nr) {
	$table = array(0,9,4,6,8,2,7,1,3,5);

	$next = 0;
	for($i=0; $i<strlen($ref_nr); $i++) {
		$next = $table[($next + substr($ref_nr, $i, 1)) % 10];
	}
	return (10 - $next) % 10;
}//ko_vesr_modulo10()

/**
 * Returns nicely formatted RefNr
 *
 * @param $refnr string refnr from DB
 * @return string Nicely formatted RefNr
 */
function ko_nice_refnr($refnr) {
	$parts = array();
	$rest = $refnr;
	while(strlen($rest) > 0) {
		$parts[] = substr($rest, -5);
		$rest = substr($rest, 0, -5);
	}
	return implode(' ', array_reverse($parts));
}//ko_nice_refnr()



function ko_vesr_v11_payment_donations($payment) {
	$type = $status = $summary = NULL;
	$handled = FALSE;

	$ref = $payment['refnumber'];
	$preamble = substr($ref, 0, 6);
	$comment = '';
	if ($preamble == '999000') {
		$crmContactId = intval(substr($ref, 6, 6));
		$accountId = intval(substr($ref, 12, 6));
		$personId = intval(substr($ref, 18, 8));


		//CRM
		if(ko_module_installed('crm') && $crmContactId > 0) {

			$crmContact = db_select_data('ko_crm_contacts', "WHERE `id` = '$crmContactId'", '*', '', '', TRUE);
			$crmProject = db_select_data('ko_crm_projects', "WHERE `id` = '".$crmContact['project_id']."'", '*', '', '', TRUE);
			$comment .= $crmContact['title'].' ('.trim($crmProject['number'].' '.$crmProject['title']).")\n";

			//New crm contact entry with status from setting
			$crmEntry = array(
				'type' => 'donation',
				'date' => $payment['paydate'],
				'title' => $crmContact['title'],
				'description' => $payment['amount'],
				'status_id' => ko_get_setting('crm_status_donation'),
				'project_id' => $crmContact['project_id'],
				'crdate' => date('Y-m-d H:i:s'),
				'cruser' => $_SESSION['ses_userid']
			);
			$contact_id = db_insert_data('ko_crm_contacts', $crmEntry);
			db_insert_data('ko_crm_mapping', array('contact_id' => $contact_id, 'leute_id' => $personId));
		}

		$comment .= getLL('kota_ko_vesr_account').": {$payment['account']}";

		$donation = array(
			'date' => $payment['paydate'],
			'valutadate' => $payment['valutadate'],
			'person' => $personId,
			'account' => $accountId,
			'amount' => $payment['amount'],
			'comment' => trim($comment),
			'source' => getLL("kota_ko_vesr_transaction_{$payment['transaction']}"),
		);

		$dId = db_insert_data('ko_donations', $donation);
		$donation['id'] = $dId;
		ko_log_diff('vesr_import_donation', $donation);

		$handled = TRUE;
		$type = 'donations';
		$status = 'ok';
		$summary = array('misc_id' => $dId);
	}

	return array($handled, $type, $status, $summary);
}//ko_vesr_v11_payment_donations()

function ko_vesr_v11_report_donations($payment) {
	$type = $status = $summary = NULL;
	$handled = FALSE;

	$ref = $payment['refnumber'];
	$preamble = substr($ref, 0, 6);
	$comment = '';
	if ($preamble == '999000') {
		$crmContactId = intval(substr($ref, 6, 6));
		$accountId = intval(substr($ref, 12, 6));
		$personId = intval(substr($ref, 18, 8));

		$donations = db_select_data('ko_donations',
			"WHERE
				`date`='".$payment['paydate']."' AND
				person='".$personId."' AND
				account='".$accountId."' AND
				amount='".$payment['amount']."'"
		);

		if(count($donations) == 1) {
			$donation = reset($donations);
			$status = 'ok';
			$summary = array('misc_id' => $donation['id']);
		} else {
			$status = 'notFound';
		}

		$handled = TRUE;
		$type = 'donations';
	}

	return array($handled, $type, $status, $summary);
}

function ko_vesr_v11_import_summary_donations($ok) {
	global $KOTA;

	if (!is_array($KOTA['ko_donations'])) ko_include_kota(array('ko_donations'));
	$KOTA['ko_donations']['person']['list'] = 'FCN:kota_listview_people_link';

	$showCols = array('date', 'valutadate', 'person', 'account', 'amount', 'comment', 'source');

	$header = array();
	foreach ($showCols as $sc) {
		$header[] = getLL("kota_listview_ko_donations_{$sc}");
	}

	$rows = array();
	foreach ($ok as $p) {
		$donation = db_select_data('ko_donations', "WHERE `id` = {$p['misc_id']}", '*', '', '', TRUE);
		kota_process_data('ko_donations', $donation, 'list');
		$row = array();
		foreach ($showCols as $sc) {
			$row[] = $donation[$sc];
		}
		$rows[] = $row;
	}
	return array($header, $rows);
} //ko_vesr_v11_import_summary_donations()






function ko_vesr_camt_payment_donations(\LPC\LpcEsr\CashManagement\Message $message) {
	global $WEB_LANGS;

	$lang = substr(reset($WEB_LANGS),0,2);
	$type = $status = $summary = NULL;
	$handled = FALSE;

	$ref = $message->getReferenceNumber();
	$preamble = substr($ref, 0, 6);
	$comment = '';
	if ($preamble == '999000') {
		$crmContactId = intval(substr($ref, 6, 6));
		$accountId = intval(substr($ref, 12, 6));
		$personId = intval(substr($ref, 18, 8));

		//CRM
		if(ko_module_installed('crm') && $crmContactId > 0) {

			$crmContact = db_select_data('ko_crm_contacts', "WHERE `id` = '$crmContactId'", '*', '', '', TRUE);
			$crmProject = db_select_data('ko_crm_projects', "WHERE `id` = '".$crmContact['project_id']."'", '*', '', '', TRUE);
			$comment .= $crmContact['title'].' ('.trim($crmProject['number'].' '.$crmProject['title']).")\n";

			//New crm contact entry with status from setting
			$crmEntry = array(
				'type' => 'donation',
				'date' => $message->getBookingDate()->format('Y-m-d'),
				'title' => $crmContact['title'],
				'description' => $message->getAmount(),
				'status_id' => ko_get_setting('crm_status_donation'),
				'project_id' => $crmContact['project_id'],
				'crdate' => date('Y-m-d H:i:s'),
				'cruser' => $_SESSION['ses_userid']
			);
			$contact_id = db_insert_data('ko_crm_contacts', $crmEntry);
			db_insert_data('ko_crm_mapping', array('contact_id' => $contact_id, 'leute_id' => $personId));
		}


		$currencyComment = '';
		$amount = $message->getAmount();

		//Currency
		if($message->getCurrency()) {
			$defaultCurrency = 'CHF';
			if($message->getCurrency() != $defaultCurrency) {
				$amount2 = ko_currency_converter($message->getCurrency(), $defaultCurrency, $message->getAmount());
				if($amount2) {
					$amount = $amount2;
					$currencyComment = "\n".getLL('vesr_currency_comment').': '.$message->getAmount().' '.$message->getCurrency();
				}
			}
		}


		$donation = array(
			'date' => $message->getBookingDate()->format('Y-m-d'),
			'valutadate' => $message->getValutaDate()->format('Y-m-d'),
			'person' => $personId,
			'account' => $accountId,
			'amount' => $amount,
			'comment' => utf8_decode(trim($message->getNote()."\n".$message->getAdditionalInformation())).$currencyComment,
			'source' => utf8_decode($message->getTranslatedBankTransactionCode($lang)),
			'camt_uid' => $message->getUniqueId(),
			'crm_project_id' => (isset($crmContact['project_id']) ? $crmContact['project_id'] : ''),
		);

		$dId = db_insert_data('ko_donations', $donation);
		$donation['id'] = $dId;
		ko_log_diff('vesr_import_donation', $donation);

		$handled = TRUE;
		$type = 'donations';
		$status = 'ok';
		$summary = array('misc_id' => $dId);
	} else if($ref == '') {
		$currencyComment = '';
		$amount = $message->getAmount();

		//Currency
		if($message->getCurrency()) {
			$defaultCurrency = 'CHF';
			if($message->getCurrency() != $defaultCurrency) {
				$amount2 = ko_currency_converter($message->getCurrency(), $defaultCurrency, $message->getAmount());
				if($amount2) {
					$amount = $amount2;
					$currencyComment = "\n".getLL('vesr_currency_comment').': '.$message->getAmount().' '.$message->getCurrency();
				}
			}
		}
		$donationMod = array(
			'date' => $message->getBookingDate()->format('Y-m-d'),
			'amount' => $amount,
			'valutadate' => $message->getValutaDate()->format('Y-m-d'),
			'comment' => utf8_decode(trim($message->getNote()."\n".$message->getAdditionalInformation())).$currencyComment,
			'source' => utf8_decode($message->getTranslatedBankTransactionCode($lang)),
			'camt_uid' => $message->getUniqueId(),
			'_crdate' => date('Y-m-d H:i:s'),
			'_cruser' => $_SESSION['ses_userid'],
			'_p_vorname' => utf8_decode(substr($message->getDebtorName(), 0, strpos($message->getDebtorName(),' '))),
			'_p_nachname' => utf8_decode(substr($message->getDebtorName(), strpos($message->getDebtorName(),' ')+1)),
			'_p_adresse' => utf8_decode($message->getDebtorStreet()),
			'_p_adresse_zusatz' => utf8_decode($message->getDebtorExtraAddressLines()),
			'_p_plz' => utf8_decode($message->getDebtorZip()),
			'_p_ort' => utf8_decode($message->getDebtorCity()),
			'_p_land' => utf8_decode($message->getDebtorCountry()),
			'_p_email' => utf8_decode($message->getDebtorEmail()),
			'_account_number' => $message->getAccountNumber(),
			'_account_name' => utf8_decode($message->getAccountName()),
		);
		$dId = db_insert_data('ko_donations_mod', $donationMod);
		$handled = TRUE;
		$type = 'donations_mod';
		$status = 'ok';
		$summary = array('misc_id' => $dId);
	}

	return array($handled, $type, $status, $summary);
}//ko_vesr_camt_payment_donations()





function ko_currency_converter($ccyFrom, $ccyTo, $amount) {

	//Get apiKey from settings or abort if not set
	$key = trim(ko_get_setting('currencyconverterapi_key'));
	if(!$key) {
		ko_log('error_no_cca_apikey', 'No apiKey set for currencyconverterapi.com');
		return $amount;
	}

	//Query: From currency _ To currency: Will return exchange rate as factor
	$query =  "{$ccyFrom}_{$ccyTo}";

	$json = file_get_contents("https://free.currencyconverterapi.com/api/v6/convert?apiKey={$key}&q={$query}&compact=ultra");
	$obj = json_decode($json, true);
	if(!$obj) return $amount;

	$val = floatval($obj["$query"]);
	if(!$val) return $amount;

	$total = $val * $amount;
	return number_format($total, 2, '.', '');
}




function ko_vesr_camt_report_donations(\LPC\LpcEsr\CashManagement\Message $message) {
	$type = $status = $summary = NULL;
	$handled = FALSE;

	$ref = $message->getReferenceNumber();
	$preamble = substr($ref, 0, 6);
	$comment = '';
	if ($preamble == '999000') {
		$crmContactId = intval(substr($ref, 6, 6));
		$accountId = intval(substr($ref, 12, 6));
		$personId = intval(substr($ref, 18, 8));

		$donations = db_select_data('ko_donations',"WHERE camt_uid='".$message->getUniqueId()."'");

		// legacy: try do find donations, for whom no camt_uid was stored yet
		if(empty($donations)) {
			$donations = db_select_data('ko_donations',
				"WHERE
					camt_uid='' AND
					`date`='".$message->getBookingDate()->format('Y-m-d')."' AND
					valutadate='".$message->getValutaDate()->format('Y-m-d')."' AND
					person='".$personId."' AND
					account='".$accountId."' AND
					amount='".$message->getAmount()."'"
			);
		}

		if(count($donations) == 1) {
			$donation = reset($donations);
			$status = 'ok';
			$summary = array('misc_id' => $donation['id']);
		} else {
			$status = 'notFound';
		}

		$handled = TRUE;
		$type = 'donations';
	} else if($ref == '') {
		$donations = db_select_data('ko_donations',"WHERE camt_uid='".$message->getUniqueId()."'");
		if(count($donations) == 1) {
			$donation = reset($donations);
			$status = 'ok';
			$summary = array('misc_id' => $donation['id']);
		} else {
			$status = 'notFound';
		}
		$handled = TRUE;
		$type = 'donations';
	}

	if($handled && $status != 'ok') {
		// query ko_donations_mod table
		$donations = db_select_data('ko_donations_mod',"WHERE camt_uid='".$message->getUniqueId()."'");
		if(count($donations) == 1) {
			$status = 'ok';
			$type = 'donations_mod';
			$donation = reset($donations);
			$summary = array('misc_id' => $donation['id']);
		}
	}

	return array($handled, $type, $status, $summary);
}

function ko_vesr_camt_import_summary_donations($ok) {
	global $KOTA;

	if (!is_array($KOTA['ko_donations'])) ko_include_kota(array('ko_donations'));
	$KOTA['ko_donations']['person']['list'] = 'FCN:kota_listview_people_link';

	$showCols = array('date', 'valutadate', 'person', 'account', 'amount', 'comment', 'source');

	$header = array();
	foreach ($showCols as $sc) {
		$header[$sc] = getLL("kota_listview_ko_donations_{$sc}");
	}

	$rows = array();
	foreach ($ok as $p) {
		$donation = db_select_data('ko_donations', "WHERE `id` = {$p['misc_id']}", '*', '', '', TRUE);
		kota_process_data('ko_donations', $donation, 'list');
		$row = array();
		foreach ($showCols as $sc) {
			$row[$sc] = $donation[$sc];
		}
		$rows[] = $row;
	}
	return array($header, $rows);
} //ko_vesr_camt_import_summary_donations()


function ko_vesr_camt_import_summary_donations_mod($ok) {
	$showCols = array('date', 'valutadate', 'person', 'amount', 'comment', 'source');

	$header = array();
	foreach ($showCols as $sc) {
		$header[$sc] = getLL("kota_listview_ko_donations_{$sc}");
	}

	$rows = array();
	foreach ($ok as $p) {
		$donation = db_select_data('ko_donations_mod', "WHERE `id` = {$p['misc_id']}", '*', '', '', TRUE);
		$person = [];
		foreach ($donation AS $key => $field) {
			if (substr($key,0,3) == '_p_') {
				if (!empty($field)) {
					$person[] = $field;
				}
			}
		}
		$row = [
			'date' => sql2datum($donation['date']),
			'valutadate' => sql2datum($donation['valutadate']),
			'person' => implode(" ", $person),
			'amount' => $donation['amount'],
			'comment' => $donation['comment'],
			'source' => $donation['source'],
		];

		$rows[] = $row;
	}
	return array($header, $rows);
}


function ko_task_save_group_assignments() {
	$allPeople = db_select_data('ko_leute', "WHERE 1=1");
	$time = date('Y-m-d H:i:s');

	foreach ($allPeople as $person) {
		ko_create_groups_snapshot($person, $time, NULL, FALSE);
	}
}//ko_task_save_group_assignments()




function ko_create_groups_snapshot($person, $time=NULL, $currentGroups=NULL, $timeIsExact=NULL) {
	if (!is_array($person)) {
		ko_get_person_by_id($person, $person);
	}
	if (!$person['id']) return FALSE;

	if ($time === NULL) {
		$time = date('Y-m-d H:i:s');
		if ($timeIsExact === NULL) $timeIsExact = 1;
	} else if ($timeIsExact === NULL) $timeIsExact = 0;
	$date = substr($time, 0, 10);

	if ($timeIsExact === TRUE) $timeIsExact = 1;
	else if ($timeIsExact === FALSE) $timeIsExact = 0;

	$openEntries = array();
	$currentGroups = $currentGroups !== NULL ? $currentGroups : explode(',', $person['groups']);

	foreach ($currentGroups as $group) {
		if (!preg_match('/(g\d{6}(:?:r\d{6})?)$/', $group, $matches)) continue;
		$groupId = $matches[1];
		if (strpos($groupId, ':') !== FALSE) {
			$roleId = intval(substr($groupId, -6));
			$groupId = intval(substr($groupId, 1, 6));
		} else {
			$roleId = 0;
			$groupId = intval(substr($groupId, -6));
		}

		$e = db_select_data('ko_groups_assignment_history', "WHERE `person_id` = {$person['id']} AND `group_id` = {$groupId} AND `role_id` = {$roleId} AND `start` <= '{$time}' AND (`stop` = '0000-00-00 00:00:00' OR (`stop` >= '{$date} 00:00:00'))", '*', 'ORDER BY `start` ASC');
		if (sizeof($e) > 1) {
			$openFound = FALSE;
			$loopC = 0;
			foreach ($e as $entry) {
				if ($entry['stop'] == '0000-00-00 00:00:00') {
					$openEntries[] = $entry['id'];
					if ($loopC == 0 || $openFound) {
						//print_d('There is something weird with this entry');
						//print_d($entry);
					}
					$openFound = TRUE;
				}
				$loopC++;
			}

			$entry = end($e);
			if ($entry['stop'] != '0000-00-00 00:00:00') {
				db_update_data('ko_groups_assignment_history', "WHERE `id` = {$entry['id']}", array('stop' => '0000-00-00 00:00:00'));
			}
			$openEntries[] = $entry['id'];
		} else if (sizeof($e) == 1) {
			$entry = end($e);
			if ($entry['stop'] != '0000-00-00 00:00:00') {
				db_update_data('ko_groups_assignment_history', "WHERE `id` = {$entry['id']}", array('stop' => '0000-00-00 00:00:00'));
			}
			$openEntries[] = $entry['id'];
		} else {
			$newEntry = array(
				'group_id' => $groupId,
				'person_id' => $person['id'],
				'role_id' => $roleId,
				'start' => "{$time}",
				'start_is_exact' => $timeIsExact,
			);
			$openEntries[] = db_insert_data('ko_groups_assignment_history', $newEntry);
		}
	}

	if (sizeof($openEntries) > 0) {
		db_update_data('ko_groups_assignment_history', "WHERE `person_id` = {$person['id']} AND `stop` = '0000-00-00 00:00:00' AND `start` <= '{$time}' AND `id` NOT IN (".implode(',', $openEntries).")", array('stop' => "{$time}", 'stop_is_exact' => $timeIsExact));
	} else {
		db_update_data('ko_groups_assignment_history', "WHERE `person_id` = {$person['id']} AND `stop` = '0000-00-00 00:00:00' AND `start` <= '{$time}'", array('stop' => "{$time}", 'stop_is_exact' => $timeIsExact));
	}

	$where = "WHERE start = '' OR start = '0000-00-00 00:00:00'";
	$history_with_no_start = db_select_data("ko_groups_assignment_history", $where);
	if(!empty($history_with_no_start)) {
		db_delete_data("ko_groups_assignment_history", $where);
		ko_log("del_assignment_history", serialize($history_with_no_start));
	}

	// handle assigment entries for roles that were deleted in the meantime
	ko_groups_assignment_history_handle_removed_roles();

	return TRUE;
}



function ko_groups_assignment_history_handle_removed_roles() {
	$assignmentRoleIds = db_select_distinct('ko_groups_assignment_history', 'role_id', '', "WHERE `role_id` > 0");
	$roleIds = array_keys(db_select_data('ko_grouproles', "WHERE 1=1", 'id'));

	$removeRoleIds = array();
	foreach ($assignmentRoleIds as $ari) {
		if (!in_array($ari, $roleIds)) {
			$removeRoleIds[] = $ari;
		}
	}

	$datetime = new DateTime('tomorrow');
	$tomorrow = $datetime->format('Y-m-d H:i:s');

	foreach ($removeRoleIds as $roleId) {
		$pgs = array();
		$pidsGids = db_select_data('ko_groups_assignment_history', "WHERE `role_id` = {$roleId}", 'person_id, group_id', '', '', FALSE, TRUE);
		foreach ($pidsGids as $pidGid) {
			$pg = "{$pidGid['person_id']}:{$pidGid['group_id']}";
			if (!in_array($pg, $pgs)) {
				$pgs[] = $pg;
			}
		}

		foreach ($pgs as $pg) {
			list($personId, $groupId) = explode(':', $pg);
			$entries = db_select_data('ko_groups_assignment_history', "WHERE (`role_id` = {$roleId} OR `role_id` = 0) AND `person_id` = {$personId} AND `group_id` = {$groupId}");
			$startStops = array();
			$sortKeyUniquifier = 1;
			foreach ($entries as $entry) {
				$stop = $entry['stop'] == '0000-00-00 00:00:00' ? $stop = $tomorrow : $stop = $entry['stop'];
				$orderKey = "{$entry['start']}:{$sortKeyUniquifier}";
				$startStops[$orderKey] = array('orderKey' => $orderKey, 'type' => 'start', 'entry' => $entry);
				$orderKey = "{$stop}:{$sortKeyUniquifier}";
				$startStops[$orderKey] = array('orderKey' => $orderKey, 'type' => 'stop', 'entry' => $entry);
				$sortKeyUniquifier += 1;
			}
			$deleteIds = ko_array_column($entries, 'id');

			ksort($startStops);
			$newEntries = array();
			$currentlyRunning = 0;
			$firstStart = NULL;
			foreach ($startStops as $startStop) {
				$type = $startStop['type'];
				$entry = $startStop['entry'];
				$time = $entry[$type];

				if ($type == 'start') {
					if ($firstStart === NULL) {
						$firstStart = $entry;
					}
					$currentlyRunning += 1;
				} else {
					if ($firstStart === NULL) {
						throw new Exception('firstStart must not be NULL here');
					}
					$currentlyRunning -= 1;
					if ($currentlyRunning == 0) {
						$newEntries[] = array(
							'start' => $firstStart['start'],
							'start_is_exact' => $firstStart['start_is_exact'],
							'stop' => $entry['stop'],
							'stop_is_exact' => $entry['stop_is_exact'],
							'group_id' => $groupId,
							'role_id' => 0,
							'person_id' => $personId
						);
						$firstStart = NULL;
					}
				}
			}

			if (sizeof($deleteIds) > 0) {
				db_delete_data('ko_groups_assignment_history', "WHERE `id` IN (".implode(',', $deleteIds).")");
			}
			foreach ($newEntries as $newEntry) {
				db_insert_data('ko_groups_assignment_history', $newEntry);
			}
		}

	}
}



function ko_create_groups_history_from_changes($person) {
	if (!is_array($person)) {
		ko_get_person_by_id($person, $person);
	}

	$changes = db_select_data('ko_leute_changes', "WHERE `leute_id` = {$person['id']}", '*', "ORDER BY `date` ASC", '', FALSE, TRUE);

	$lastGroups = '';
	$crdate = $person['crdate'];
	if ($crdate && $crdate != '0000-00-00 00:00:00') {
		$time = $crdate;
		$chUns = unserialize($changes[0]['changes']);
		$groups = explode(',', $chUns['groups']);
		$lastGroups = implode(',', $groups);

		ko_create_groups_snapshot($person, $time, $groups, TRUE);
	}

	foreach ($changes as $k => $change) {
		$time = $change['date'];
		if ($k < sizeof($changes) - 1) {
			$chUns = unserialize($changes[$k+1]['changes']);
			$groups = explode(',', $chUns['groups']);
		} else {
			$groups = explode(',', $person['groups']);
		}

		if (implode(',', $groups) == $lastGroups) continue;
		else $lastGroups = implode(',', $groups);

		ko_create_groups_snapshot($person, $time, $groups, TRUE);
	}
}



/**
 * @param $person
 * @return array
 */
function ko_placeholders_leute_array($person, $prefixPerson = 'r_', $prefixUser = 's_', $tag = '###') {
	global $DATETIME;

	$map = array();

	//Address fields of a person
	foreach($person as $k => $v) {
		$map[$tag . $prefixPerson . strtolower($k).$tag] = $v;
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
		$map[$tag . 'contact_'.strtolower($field).$tag] = ko_get_setting('info_'.$field);
	}

	//Add sender fields of current user
	$sender = ko_get_logged_in_person();
	foreach($sender as $k => $v) {
		$map[$tag . $prefixUser .strtolower($k).$tag] = $v;
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
		$map[$tag . $prefix .strtolower($k).$tag] = $v;
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
 * @return boolean (true if a pattern matched)
 */
function ko_mailing_check_disallowed_alias_patterns($value) {
	return (
		1 == preg_match('/^sg([0-9]{4})([a-zA-Z.]*)$/', $value, $m)     //small group
		|| 1 == preg_match('/^gr([0-9.]*$)/', $value, $m)               //group
		|| 1 == preg_match('/^fp([0-9]*$)/', $value, $m)                //filter preset from address module
		|| $value == 'crm'                                              //crm
		|| 1 == preg_match('/^crm(.*$)/', $value, $m)                  //crm with project id
		|| $value == 'ml'                                               //my list
		|| substr($value, 0, strlen('confirm-')) == 'confirm-'          //confirm emails start with confirm-
		|| substr($value, 0, strlen('sms.')) == 'sms.'                  //Send sms instead of email
		|| substr($value, 0, strlen('filter.')) == 'filter.'            //Filter preset from people module
		|| FALSE !== strpos($value, '+')                                //No plus sign allowed, used for automatically authorized emails
	);
}

/**
 * Create statistics from filter presets and general informations like count(ko_leute), count(ko_familie) ...
 *
 * @testdata If called with $_GET['createdTestData'] = TRUE: more stats will be created
 */
function ko_task_create_new_statistics() {
	global $KOTA, $MODULES;

	if(!in_array('leute',$MODULES)) return;

	$where = "WHERE type = 'filterset'";
	$filtersets = db_select_data("ko_userprefs", $where);
	$date = date('Y-m-d H:i:s');
	$stats = [];
	foreach ($filtersets AS $filterset) {
		$filter = unserialize($filterset["value"]);
		apply_leute_filter($filter, $leute_where);
		$result_count = db_get_count("ko_leute", "id", $leute_where);

		$stats[] = [
			'date' => $date,
			'user_id' => $filterset['user_id'],
			'filter_id' => $filterset['id'],
			'title' => $filterset['key'],
			'filter_hash' => current(unpack('Q',md5($filterset['value'],true))),
			'result' => $result_count,
		];
	}

	// save # addresses
	$where = "AND `deleted` = '0' AND `hidden` = '0'";
	$result_count = db_get_count("ko_leute", "id", $where);
	$stats[] = ['title' => 'addresses','result' => $result_count, 'date' => $date];

	// save # families
	$result_count = db_get_count("ko_familie", "famid");
	$stats[] = ['title' => 'families','result' => $result_count, 'date' => $date];

	// save confessions
	if($KOTA['ko_leute']['confession']) {
		$where = "WHERE `deleted` = '0' AND `hidden` = '0'  GROUP BY `confession` ASC";
		$confessions = db_select_data("ko_leute", $where, "confession AS title, count(confession) AS total");
		if(!empty($confessions)) {
			$stats[] = ['title' => 'confessions','result' => json_encode($confessions), 'date' => $date];
		}
	}

	// save terms
	ko_include_kota(["ko_taxonomy_terms"]);
	if($KOTA['ko_taxonomy_terms']) {
		$where = "WHERE ko_taxonomy_index.table = 'ko_groups'";
		$leftjoin = "LEFT JOIN ko_taxonomy_terms ON ko_taxonomy_terms.id = ko_taxonomy_index.id";
		$db_cols = "ko_taxonomy_index.*, ko_taxonomy_terms.name";
		$terms = db_select_data("ko_taxonomy_index " . $leftjoin, $where, $db_cols);

		$stats_terms = [];

		foreach($terms AS $term) {
			$where = "AND groups LIKE '%g" . zerofill($term['node_id'],6) ."%' AND deleted = 0 AND hidden = 0";
			$persons = db_get_count("ko_leute", "id", $where);
			$stats_terms[$term['id']]+= $persons;
		}

		if(!empty($stats_terms)) {
			$stats[] = ['title' => 'terms','result' => json_encode($stats_terms), 'date' => $date];
		}
	}

	if ($_GET['createTestData']) $stats = ko_task_create_new_statistics_from_testdata($stats);
	if (!empty($stats)) {
		db_insert_data_multiple("ko_statistics", $stats);
	}
}//ko_task_create_new_statistics()


/**
 * Function used for behat testing.
 *
 * @param array $stats used to create similar data as stats from last 10 days
 * @return array
 */
function ko_task_create_new_statistics_from_testdata($stats) {
	$date = time();
	$end_date = time() - 864000;
	$new_stats = $stats;
	while ($date >= $end_date) {
		$date-= 86400;
		foreach($stats AS $key => $stat) {
			if ($stats[$key]['title'] == "confessions" && !isset($stats[$key]['filter_id'])) continue;

			$stats[$key]['result'] = round(($stats[$key]['result'] * .75), 0);
			if ($stats[$key]['result'] < 0) $stats[$key]['result'] = 0;
			$stats[$key]['date'] = strftime('%Y-%m-%d %H:%M:%S', $date);
			$new_stats[] = $stats[$key];
		}
	}

	return $new_stats;
}

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
	return preg_match('#^[A-Za-z0-9\._-]+[@][A-Za-z0-9\._-]+[\.].[A-Za-z0-9]+$#', $email) ? TRUE : FALSE;
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


function str_fill($s, $l, $f, $m='prepend') {
	if ($m == 'prepend')
		while(strlen($s) < $l) {
			$s = $f.$s;
		}
	else
		while(strlen($s) < $l) {
			$s = $s.$f;
		}
	return $s;
}


/**
	* Wandelt die angegebene Zeit in eine SQL-Zeit um
	*/
function sql_zeit($z) {
	if ($z != '') {
		if(ctype_digit($z) && strlen($z) == 4) {
			$z/=100;
		}

		$z = str_replace(array(';', '.', ',', 'h', 'H', ' '), array(':', ':', ':', ':', ':', ''), $z);
		$z_1 = explode(':', $z);
		switch (sizeof($z_1)) {
			case 1:
				$r = $z . ':00';
				break;
			case 2:
				$r = $z;
				break;
			case 3:
				$r = substr($z, 0, -3);
				break;
		}
	} else {
		$r = '';
	}
	if ($r == '00:00') $r = '';
	return format_userinput($r, 'date');
}//sql_zeit()



function sql_datetime($d, $fallback=null) {

	if (!$d || $d == '00.00.0000 00:00') {
		if ($fallback == 'now') {
			return strftime('%Y-%m-%d %H:%M:%S');
		} else {
			return '0000-00-00 00:00:00';
		}
	};
	list($date, $time) = explode(' ', $d);
	if (!$date) {
		$dateString = strftime('%Y-%m-%d');
	} else {
		list($day, $month, $year) = explode('.', $date);
		$dateString = $year . '-' . $month . '-' . $day;
	}
	if (!$time) {
		$timeString = strftime('%H:%M:%S');
	} else {
		$timeString = $time . ':00';
	}
	return $dateString . ' ' . $timeString;
}




/**
	* Wandelt das angegebene Datum in ein SQL-Datum um
	*/
function sql_datum($d) {
	//Testen, ob Datum schon im SQL-Format ist:
	$temp = explode("-", $d);
	if(sizeof($temp) == 3) {
		$d = $temp[0].'-'.zerofill($temp[1], 2).'-'.zerofill($temp[2], 2);
		return format_userinput($d, "date");
	}

  if($d != "") {
    $date = explode(".", $d);
		$r = $date[2].'-'.zerofill($date[1], 2).'-'.zerofill($date[0], 2);
  } else {
    $r = "";
  }
  return format_userinput($r, "date");
}//sql_datum()


/**
	* Wandelt ein SQL-Datum ins Format TG.MT.JAHR um
	*/
function sql2datum($s) {
	if($s=="" || $s=="0000-00-00") return "";
	$s_ = explode("-", $s);
	if(sizeof($s_) == 3) {
		$r = $s_[2].".".$s_[1].".".$s_[0];
  	return $r;
	} else {
		return $s;
	}
}//sql2datum()



function sql2datetime($s) {
	global $DATETIME;

	if($s == '' || $s == '0000-00-00 00:00:00') return '';
	$ts = strtotime($s);
	if($ts > 0) {
		return strftime($DATETIME['dmY'].' %H:%M', $ts);
	} else {
		return $s;
	}
}//sql2datum()



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
 * Returns a shortend date range without redundant data
 *
 * @param String $date1 date in sql format
 * @param string $date2 date in sql format
 *
 * @return string shortend daterange if it makes sense
 */

function prettyDateRange($date1, $date2) {
	list($year1, $month1, $day1) = explode("-", substr($date1,0,10));
	list($year2, $month2, $day2) = explode("-", substr($date2,0,10));

	if ($date1 == $date2) {
		return $day1 . "." . $month1 . "." . $year1;
	} else if ($year1 . $month1 == $year2 . $month2) {
		return $day1 . ". - " . $day2 . "." . $month1 . "." . $year1;
	} else if ($year1 == $year2) {
		return $day1 . "." . $month1 . ". - " . $day2 . "." . $month2 . "." . $year1;
	} else {
		return $day1 . "." . $month1 . "." . $year1 . " - " . $day2 . "." . $month2 . "." . $year2;
	}
}



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
 * computes the age of a person based on the birthdate and today's date
 *
 * @param string   $birthdate    date as SQL date-string
 * @param null     $today        today's date as SQL date-string (if null, then today's date is picked)
 * @param string   $mode         how to return the age (either 'decimal' for a decimal number or 'ymd' for an
 *                               array(years, months, days))
 * @return int                   age in years with one decimal
 */
function ko_get_age($birthdate, $today=NULL, $mode='decimal') {
	if ($today === NULL) $today = date('Y-m-d');

	if ($birthdate == '0000-00-00') $birthdate = '';
	if (!$birthdate) return FALSE;

	if ($today == '0000-00-00') $today = '';
	if (!$today) return FALSE;

	if ($mode == 'decimal'){
		$date = new DateTime($birthdate);
		$now = new DateTime($today);
		$interval = $now->diff($date);
		$fraction = floor(10.0 * ($interval->m * 30.4375 + $interval->d) / 365.25);

		return floatval("{$interval->y}.{$fraction}");
	} else if ($mode == 'ymd') {
		list($bYear, $bMonth, $bDay) = explode('-', $birthdate);
		list($tYear, $tMonth, $tDay) = explode('-', $today);

		$years = intval($tYear) - intval($bYear);
		$months = intval($tMonth) - intval($bMonth);
		$days = intval($tDay) - intval($bDay);

		if ($days < 0) {
			$tMonth2 = $tMonth - 1;
			$tYear2 = $tYear;
			if ($tMonth2 < 1) {
				$tMonth2 += 12;
				$tYear2 -= 1;
			}
			$calDays = cal_days_in_month(CAL_GREGORIAN, $tMonth2, $tYear2);

			$months -= 1;
			$days = $calDays + $days;
		}
		if ($months < 0) {
			$months = 12 + $months;
			$years -= 1;
		}

		return array($years, $months, $days);
	}
}


/**
	* Formatiert eine Emailadresse für die anzeige im Web
	*/
function format_email($m) {
	return strtr($m, array('@' => ' (at) ', '.' => ' (dot) '));
}//format_email()


/**
	* Entfernt alle möglichen "gefährlichen" Zeichen aus User-Strings (z.B. bei der Speicherung von Filter-Vorlagen)
	*/
function format_userinput($s, $type, $enforce=FALSE, $length=0, $replace=array(), $add_own="") {
	foreach($replace as $r) {
		if($r == 'umlaute') $s = strtr($s, array('ä'=>'a','ö'=>'o','ü'=>'u','é'=>'e','è'=>'e','à'=>'a','Ä'=>'A','Ö'=>'O','Ü'=>'U'));

		if($r == 'singlequote' || $r == "allquotes") $s = strtr($s, array("'" => '', '`' => ''));
		if($r == "doublequote" || $r == "allquotes") $s = str_replace('"', "", $s);
		if($r == "backquote" || $r == "allquotes") $s = str_replace("`", "", $s);
	}

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
		break;

		case "int":
			$allowed = "-1234567890";
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
		break;

		case "alphanum":
			$allowed = "abcdefghijklmnopqrstuvwxyzöäüABCDEFGHIJKLMNOPQRSTUVWXYZÖÄÜßÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûüýþÿ1234567890";
		break;

		case "alphanum+":
			$allowed = "abcdefghijklmnopqrstuvwxyzöäüABCDEFGHIJKLMNOPQRSTUVWXYZÖÄÜßÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûüýþÿ1234567890+-_";
		break;

		case "alphanum++":
			$allowed = "abcdefghijklmnopqrstuvwxyzöäüABCDEFGHIJKLMNOPQRSTUVWXYZÖÄÜßÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûüýþÿ1234567890+-_ ";
		break;

		case "email":
			$allowed = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890.+-_@&";
		break;

		case "dir":
			$allowed = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZöäüÖÄÜß1234567890-_ ";
		break;

		case "js":
			$allowed = "abcdefghijklmnopqrstuvwxyzöäüABCDEFGHIJKLMNOPQRSTUVWXYZÖÄÜßÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûüýþÿ1234567890-_?+&@.,;:/'() ";
		break;

		case "alpha":
			$allowed = "abcdefghijklmnopqrstuvwxyzöäüABCDEFGHIJKLMNOPQRSTUVWXYZÖÄÜßÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûüýþÿ";
		break;

		case "alpha+":
			$allowed = "abcdefghijklmnopqrstuvwxyzöäüABCDEFGHIJKLMNOPQRSTUVWXYZÖÄÜßÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûüýþÿ+-_";
		break;

		case "alpha++":
			$allowed = "abcdefghijklmnopqrstuvwxyzöäüABCDEFGHIJKLMNOPQRSTUVWXYZÖÄÜßÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûüýþÿ+-_ ";
		break;

		case "color":
			if (preg_match("/^#[a-zA-Z0-9]{6}$/m", $s) == 0) {
				return FALSE;
			}
			return $s;
		break;

		case "date":
			$allowed = "1234567890-.: ";
		break;

		case 'group_role':
			$allowed = "gr1234567890:,";
		break;

		case "text":
			return $s;
		break;

		case 'sql':
			return mysqli_real_escape_string(db_get_link(), $s);
		break;

		case "all":
			return TRUE;
		break;
	}//switch(type)

	if($add_own) $allowed .= $add_own;

  $new = "";
  for($i=0; $i<strlen($s); $i++) {
    if(FALSE !== strstr($allowed, substr($s, $i, 1))) {
      $new .= substr($s, $i, 1);
    } else {
			if($enforce) {
				return FALSE;  //Bei ungültigen Zeichen nur abbrechen, wenn enforce true ist.
			}
		}
  }
  return $new;
}



/**
  * Formatiert Sonderzeichen in ihre HTML-Entsprechungen
	* Damit soll XSS in Formularen und sonst verhindert werden
	*/
function ko_html($string) {
	return strtr($string, array('&' => "&amp;",
															"'" => "&lsquo;",
															'"' => "&quot;",
															'>' => "&gt;",
															'<' => "&lt;",
															'ö' => "&ouml;",
															'ä' => "&auml;",
															'ü' => "&uuml;",
															'Ö' => "&Ouml;",
															'Ä' => "&Auml;",
															'Ü' => "&Uuml;",
															)
							);
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
															"&nbsp;" => ' ',
															'&#39;' => "'",
															'&laquo;' => '"',
															'&raquo;' => '"',
															'&szlig;' => 'ß',
															)
	);
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
			$out = $out .'%'.((strlen($hex)==1) ? ('0'.strtoupper($hex)):(strtoupper($hex)));
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
 *
 * @param string|array $datum
 * @param string $mode day,week,month
 * @param int $inc value to increment by $mode
 * @param bool $sqlformat
 * @return string|array [0=>day,1=>month,2=>year]
 */
function add2date($datum, $mode, $inc, $sqlformat=FALSE) {
	$inc = intval($inc);
	$calc = $inc > 0 ? 'add' : 'sub';
	$inc = abs($inc);

	if($sqlformat) {
		$date = new \DateTime($datum);
		if (is_array($datum)) $d = array($datum[2], $datum[1], $datum[0]);
		else {
			$d[0] = substr($datum, 8, 2);
			$d[1] = substr($datum, 5, 2);
			$d[2] = substr($datum, 0, 4);
		}

	} else {
		if(is_array($datum)) {
			$sqlDate = $datum[2].'-'.$datum[1].'-'.$datum[0];
		} else {
			$d = explode('.', $datum);
			$sqlDate = $d[2].'-'.$d[1].'-'.$d[0];
		}
		$date = new \DateTime($sqlDate);
	}

	switch($mode) {
		case 'tag':
		case 'day':
			$interval = new \DateInterval('P'.$inc.'D');
		break;
		case 'woche':
		case 'week':
			$interval = new \DateInterval('P'.$inc.'W');
		break;
		case 'monat':
		case 'month':
			$interval = new \DateInterval('P'.$inc.'M');
		break;
	}//switch(mode)

	if($calc == 'add') {
		$date->add($interval);
	} else {
		$date->sub($interval);
	}

	if($sqlformat) {
		$r = $date->format('Y-m-d');
		if(is_array($datum)) $r = explode('-', $r);
	} else {
		$r_ = $date->format('d.m.Y');
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
 *
 * @param string $date
 * @return string
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
 *
 * @param string $date
 * @return string
 */
function date_find_next_sunday($date) {
	$wd = date("w", strtotime($date));
	if($wd == 0) $wd = 7;
	$r = add2date($date, "tag", (7-$wd), TRUE);
	return $r;
}//date_find_next_sunday()


/**
 * returns the first $n-th weekday after $date
 *
 * @param $date string the date, YYYY-mm-dd
 * @param $n int the weekday, 1=monday, 7=sunday
 */
function date_find_next_weekday($date, $n) {
	$d = strtotime($date);
	$wd = ((strftime("%w", $d) - 1) % 7) + 1;
	$r = add2date($date, "day", ($n-$wd) % 7, TRUE);
	return $r;
}




/**
 * Calculate easter day without timestamp limitations
 * From: Comments on php.net for easter_date()
 */
function date_get_easter($year) {
	/*Warning: easter_date(): This function is only valid for years between 1970 and 2037
	 * The easter_days() function can be used instead of easter_date() to calculate Easter for years which fall outside the range.
	 */
	//The next line would do the work if there were no limitations:
	//return date("Y-m-d",easter_date($year));

	/*Outside range (1970,2037) they advise to use easter_days().
	 * Unfortunately, when you have to create a date object as 21-03-yyyy to which add easter_days(), then obtain Easter,
	 * functions like strtotime(), DateTime::createFromFormat() will fail. (return value is 01-01-1970)
	 */
	$march21 = date("$year-03-21");
	$days = easter_days($year);
	if($year <= 2037)
		//The next line would do the work if strtotime() wasn't affected by same limitations. But, the if..else is required to handle all years.
		$date = date('Y-m-d',strtotime(date('Y-m-d', strtotime($march21)) . " +$days day"));
	else {
		if($days <= 10){
			$day = str_pad(21+$days, 2, '0', STR_PAD_LEFT);
			$date = date("$year-03-$day");
		} else {
			$day = str_pad($days-10, 2, '0', STR_PAD_LEFT);
			$date = date("$year-04-$day");
		}
	}

	return $date;
}//date_get_easter()




function date_convert_timezone($date_str, $tz, $date_format = 'Ymd\THis\Z') {
	$time = strtotime($date_str);
	$strCurrentTZ = date_default_timezone_get();
	date_default_timezone_set($tz);
	$ret = date($date_format, $time);
	date_default_timezone_set($strCurrentTZ);
	return $ret;
}//date_convert_timezone()


/**
 * Render a datepicker and switch calendar onchange
 *
 * @return string html datepicker
 */
function ko_calendar_mwselect() {
	$view_stamp = mktime(1,1,1, $_SESSION['cal_monat'], $_SESSION['cal_tag'], $_SESSION['cal_jahr']);
	$date = date("Y-m-d", $view_stamp);

	if($date == "1970-01-01") {
		$date = date("Y-m-d", time());
	}

	$r = '<div class="btn-group btn-group-sm" style="width:32px;">
				<input type="text" name="dateselect" id="dateselect-input" class="input-sm form-control" value="'.$date.'" style="visibility:hidden;width:1px;padding:0px;margin:0px;" onchange="">
				<button type="button" id="dateselect-button" class="btn btn-default" style="position:absolute;top:0px;left:0px;"><i class="fa fa-calendar"></i></button>
				<script>
				var datepicker = $("#dateselect-input").datetimepicker({
						locale: kOOL.language,
						format: "YYYY-MM-DD",
						showTodayButton: true,
						useCurrent: false
						});
				
					$(datepicker).on("dp.change", function(e){
						var start = $("#dateselect-input").val();
						sendReq("inc/ajax.php", "action,ymd,view", "fcsetdate,"+start+",'.$_SESSION['cal_view'].'", do_element);
						$("#ko_calendar").fullCalendar("gotoDate", moment(start));
						});
						
					$("#dateselect-button").click(function(){
						datepicker.data("DateTimePicker").toggle();
					});
				
				</script>
			</div>';

	return $r;
}//ko_calendar_mwselect()



/**
	* Liefert wiederholte Termine nach verschiedenen Modi. (für Reservationen und Termine verwendet)
	* d1 und d2 sind Start- und Enddatum des einzelnen Anlasses (wiederholte mehrtägige Anlässe sind möglich)
	* repeat_mode enthält den Modus der Wiederholung (keine, taeglich, wochentlich, monatlich1, monatlich2)
	* bis_monat und bis_jahr stellen das Ende der Wiederholung dar (inkl.)
	*/
function ko_get_wiederholung($d1, $d2, $repeat_mode, $inc, $bis_tag, $bis_monat, $bis_jahr, &$r, $max_repeats='', $holiday_eg=0) {
	global $HOLIDAYS;

	//Resultat-Array leeren
	$r = array();

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
		$dateTimeStart = new DateTime(sql_datum($d1));
		$dateTimeStop = new DateTime(sql_datum($d2));
		$dateTimeDiff = $dateTimeStop->diff($dateTimeStart);
		$d_diff = $dateTimeDiff->d;
	} else $d_diff = 0;

	//Enddatum in Code umwandeln
	if($max_repeats == "") {  //Keine Anzahl Wiederholungen angegeben --> Enddatum verwenden
		$max_repeats = 1000;
		$until_string = date2code(array($bis_tag, $bis_monat, $bis_jahr));
	} else {  //Sonst Anzahl Wiederholungen verwenden und Datum in ferne Zukunft legen
		$until_string = date2code(array("31", "12", "3000"));
	}

	$iterations = 0;
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
			while($new_code1 <= $until_string && (sizeof($r) / 2) <= $max_repeats && $iterations < 10000) {
				$code1 = code2date($new_code1);
				$r[] = $code1[0] . "." . $code1[1] . "." . $code1[2];
				$code2 = code2date($new_code2);
				if($code2[0] != "") $r[] = $code2[0] . "." . $code2[1] . "." . $code2[2];
				else $r[] = "";

				$new_code1 = date2code(add2date(code2date($new_code1), $add_mode, $inc));
				$new_code2 = ($d_diff == 0) ? "" : date2code(add2date(code2date($new_code2), $add_mode, $inc));

				ko_remove_repetitions_from_eg($r, $holiday_eg);

				$iterations++;
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

			while($new_code <= $until_string && (sizeof($r) / 2) <= $max_repeats && $iterations < 10000) {
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

				ko_remove_repetitions_in_past($r, $sd_string);
				ko_remove_repetitions_from_eg($r, $holiday_eg);

				$iterations++;
			}//while(new_code < until_string)
		break;

		case "holidays":
			list($inc, $offset) = explode('@', $inc);
			$offset = intval($offset);

			$holiday = $HOLIDAYS[$inc];
			if (!is_array($holiday)) break;

			$startCode = date2code($d1e);
			$endCode = $until_string;

			$startYear = substr($startCode, 0, 4);
			$endYear = substr($endCode, 0, 4);
			$currentYear = $startYear;
			do {
				$t = explode("-", ko_get_holiday_date($holiday, $currentYear));
				$t = add2date($t, "day", intval($offset), TRUE);
				$holidayDate = implode('-', $t);
				//strftime('%Y-%m-%d', strtotime("{$offset} days", strtotime(ko_get_holiday_date($holiday, $currentYear))));
				$currentCode = str_replace('-', '', $holidayDate);
				if ($startCode <= $currentCode && $currentCode <= $endCode) {
					$holidayDate1 = code2date($currentCode);
					$h1s = $holidayDate1[0].'.'.$holidayDate1[1].'.'.$holidayDate1[2];
					$r[] = $h1s;
					$holidayDate2 = add2date($holidayDate1, "day", $d_diff);
					$h2s = $holidayDate2[0].'.'.$holidayDate2[1].'.'.$holidayDate2[2];
					$r[] = ($d_diff > 0) ? $h2s : "";

					ko_remove_repetitions_from_eg($r, $holiday_eg);
				}
				$currentYear++;

				$iterations++;
			} while ($currentYear <= $endYear && (sizeof($r) / 2) <= $max_repeats && $iterations < 10000);
		break;

		case 'dates':
			$r[] = $d1;
			$r[] = $d2;
			$dates = explode(',', $inc);
			foreach ($dates as $rDate) {
				list($y, $m, $d) = explode('-', $rDate);
				$rd1 = "{$d}.{$m}.{$y}";
				if ($d_diff > 0) {
					$rd2 = add2date(array($d, $m, $y), "day", $d_diff);
					$rd2 = implode('.', $rd2);
				} else {
					$rd2 = '';
				}

				$r[] = $rd1;
				$r[] = $rd2;
			}

			ko_remove_repetitions_from_eg($r, $holiday_eg);
			break;

		default:  //case 'keine'
			$r[] = $d1;
			$r[] = $d2;
	}//switch(repeat_mode)

	return TRUE;
}//ko_get_wiederholung()




function ko_remove_repetitions_in_past(&$r, $startCode) {
	$removeIndices = array();

	$indexCounter = 0;
	foreach ($r as $k => $v) {
		if ($indexCounter % 2 == 0) {
			if (date2code(explode('.', $v)) < $startCode) {
				$removeIndices[] = $indexCounter;
				$removeIndices[] = $indexCounter+1;
			}
		}
		$indexCounter++;
	}

	foreach ($removeIndices as $ri) {
		unset($r[$ri]);
	}

	$r = array_values($r);
}



function ko_remove_repetitions_from_eg(&$r, $holiday_eg) {
	//Exclude repetition dates that collide with holiday eventgroup
	if($holiday_eg > 0) {
		$del_keys = array();

		$first = $r[0];
		$min = substr($first, -4) . '-' . substr($first, 3, 2) . '-' . substr($first, 0, 2);
		$last = $r[sizeof($r) - 1] ? $r[sizeof($r) - 1] : $r[sizeof($r) - 2];
		$max = substr($last, -4) . '-' . substr($last, 3, 2) . '-' . substr($last, 0, 2);
		$holidays = db_select_data('ko_event', "WHERE `eventgruppen_id` = '$holiday_eg' AND `enddatum` >= '$min' AND `startdatum` <= '$max'");
		$holiday_days = array();
		foreach ($holidays as $day) {
			$start = $day['startdatum'];
			$stop = $day['enddatum'];
			while (str_replace('-', '', $stop) >= str_replace('-', '', $start)) {
				$holiday_days[] = strftime('%d.%m.%Y', strtotime($start));
				$start = add2date($start, 'day', 1, TRUE);
			}
		}
		for ($i = 0; $i < sizeof($r); $i += 2) {
			$dstart = substr($r[$i], -4) . '-' . substr($r[$i], 3, 2) . '-' . substr($r[$i], 0, 2);
			if ($r[$i + 1] == '') {
				$dstop = $dstart;
			} else {
				$dstop = substr($r[$i + 1], -4) . '-' . substr($r[$i + 1], 3, 2) . '-' . substr($r[$i + 1], 0, 2);
			}
			$del = FALSE;
			while (str_replace('-', '', $dstart) <= str_replace('-', '', $dstop)) {
				if (in_array(strftime('%d.%m.%Y', strtotime($dstart)), $holiday_days)) $del = TRUE;
				$dstart = add2date($dstart, 'day', 1, TRUE);
			}
			if ($del) {
				$del_keys[] = $i;
				$del_keys[] = $i + 1;
			}
		}
		foreach ($del_keys as $k) {
			unset($r[$k]);
		}
		//Reset indizes
		$r = array_values($r);
	}
}


/**
 * returns a date in form 'YYYY-mm-dd' or NULL
 *
 * @param $holiday
 * @param $year
 * @return null|string
 */
function ko_get_holiday_date($holiday, $year) {
	global $HOLIDAYS;

	switch ($holiday['type']) {
		case 'absolute':
			$date = $year.'-'.$holiday['mm-dd'];
			break;
		case 'relative':
			$to = $holiday['to'];
			$toDate = is_array($HOLIDAYS[$to]) ? ko_get_holiday_date($HOLIDAYS[$to], $year) : NULL;
			$date = strftime('%Y-%m-%d', strtotime($holiday['delta'], strtotime($toDate)));
			break;
		case 'FCN':
			$date = call_user_func_array($holiday['FCN'], array($year));
			break;
		case'relative_weekday':
			$baseDate = $year.'-'.$holiday['mm-dd'];
			$baseWD = strftime('%u', strtotime($baseDate));
			if ($baseWD == $holiday['which']) {
				$to = $baseDate;
				$delta = $holiday['ord'] == 'after' ? $holiday['nth']*7 : -$holiday['nth']*7;
			} else {
				$to = date_find_next_weekday($baseDate, $holiday['which']);
				$delta = $holiday['ord'] == 'after' ? ($holiday['nth']-1)*7 : -$holiday['nth']*7;
			}
			$date = $delta ? strftime('%Y-%m-%d', strtotime(sprintf('%+d days', $delta), strtotime($to))) : $to;
			break;
		default:
			$date = NULL;
			break;
	}

	return $date;
}



function ko_get_new_serie_id($table) {
	$max1 = db_select_data("ko_".$table, "", "MAX(`serie_id`) as max", "", "", TRUE);
	$max2 = db_select_data("ko_".$table."_mod", "", "MAX(`serie_id`) as max", "", "", TRUE);
	$max = max($max1["max"], $max2["max"]);
	return ($max+1);
}//ko_get_new_serie_id()


/**
	* Erstellt einen Log-Eintrag zu definierten Typ. Timestamp und UserID werden automatisch eingefügt
	*/
function ko_log($type, $msg, &$id = '') {
	global $EMAIL_LOG_TYPES, $BASE_URL;

	//Create db entry
	$type = format_userinput($type, 'alphanum+', FALSE, 0, array(), '@');
	$id = db_insert_data('ko_log', array('type' => $type,
																 'comment' => mysqli_real_escape_string(db_get_link(), $msg),
																 'user_id' => $_SESSION['ses_userid'],
																 'date' => date('Y-m-d H:i:s'),
																 'session_id' => session_id(),
																 'request_data' => print_r(ko_log_clean_request_data($_REQUEST), TRUE),
																 ));

	//Send email notification if activated for given type
	if(is_array($EMAIL_LOG_TYPES) && in_array($type, $EMAIL_LOG_TYPES) && defined('WARRANTY_EMAIL')) {
		$subject = 'kOOL: '.$type.' (on '.$BASE_URL.')';

		$from = ko_get_setting('info_email');
		if(!$from) $from = WARRANTY_EMAIL;

		$msg .= "\n\n- CALL TRACE:\n".ko_get_call_trace()."\n";
		$msg .= "\n\n- GET:\n".print_r($_GET, TRUE);
		$msg .= "\n\n- POST:\n".print_r($_POST, TRUE);
		$msg .= "\n\n- SESSION:\n".print_r($_SESSION, TRUE);
		$msg .= "\n\n- SERVER:\n".print_r($_SERVER, TRUE);

		ko_send_mail($from, WARRANTY_EMAIL, $subject, $msg);
	}
}//ko_log()



/**
  * Erstellt Log-Meldung anhang zwei übergebener Arrays, und gibt die Differenzen an
	*
	* @param string $type Log type
	* @param array $data New data to be logged. Only pass this for new entries
	* @param array $old Old data. If set used this so only log differences between $old and $data
	* @param boolean $logAll Set to TRUE to log all keys in $data, otherwise only keys with changed data will be logged
	*
	* @return void
	*/
function ko_log_diff($type, $data, $old=array(), $logAll=FALSE) {
	$msg = "";
	foreach($data as $key => $value) {
		$oldValue = isset($old[$key]) ? $old[$key] : null;
		if($oldValue != $value || $logAll == TRUE) {
			$msg .= "$key: ".$oldValue." --> ".$value.", ";
		}
	}
	if(isset($old["id"])) $msg = "id: ".$old["id"].", ".$msg;
	ko_log($type, substr($msg, 0, -2));
}//ko_log_diff()




function ko_log_clean_request_data($req) {
	foreach($req as $k => $v) {
		if(in_array($k, array('txt_pwd_old', 'txt_pwd_new1', 'txt_pwd_new2', 'txt_pwd1', 'txt_pwd2', 'password'))) {
			$req[$k] = str_pad('', strlen($v), '*');
		}
	}
	return $req;
}



/**
  * Erstellt Log-Meldung, falls in einem Modul ein behandelter Error auftritt.
	* Dient der Verfolgbarkeit von User-Meldungen, wenn sie einen Error erhalten.
	*/
function ko_error_log($module, $error, $error_txt, $action) {
	$log_message  = "$module Error $error: '$error_txt' - Action: $action - ";
  $log_message .= "User: ".$_SESSION["ses_username"]." (".$_SESSION["ses_userid"].") - ";
  $log_message .= "POST: (".var_export(ko_log_clean_request_data($_POST), TRUE).")";
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



function ko_get_menuitem_link($module, $action, $badge = null, $linkAddress = '', $linkDisabled = false, $linkTitle = null) {
	return ko_get_menuitem($module, $action, 'link', '', '', $badge, $linkAddress, $linkDisabled, false, $linkTitle);
}
function ko_get_menuitem_html($html, $title = '') {
	return ko_get_menuitem('', '', 'html', $html, $title);
}
function ko_get_menuitem_seperator($noLine = false) {
	return ko_get_menuitem('', '', 'seperator', '', '', '', '', false, $noLine);
}
function ko_get_menuitem_itemlist($itemlist_content = array()) {
	$itemlist_content['type'] = 'itemlist';
	return $itemlist_content;
}
function ko_get_menuitem_notizen() {
	return ko_get_menuitem('', '', 'notizen');
}
function ko_get_menuitem($module, $action, $type = 'link', $htmlContent = '', $htmlTitle = '', $badge = null, $linkAddress = '', $linkDisabled = false, $noLine = false, $linkTitle = null) {
	global $do_action;
	global $ko_menu_akt, $ko_path;

	switch ($type) {
		case 'link':
			if (!$linkAddress) {
				$linkAddress = $ko_path . $module . '/index.php?action=' . $action;
			}
			$title = $linkTitle;
			if ($title === null) {
				$title = getLL('submenu_' . $module . '_' . $action);
			}
			return array(
				'type' => 'link',
				'link' => $linkAddress,
				'title' => $title,
				'module' => $module,
				'action' => $action,
				'badge' => ($badge !== null && !$badge ? 0 : $badge),
				'active' => ((($_SESSION['show'] == $action) && $ko_menu_akt == $module) ? true : false),
				'disabled' => $linkDisabled,
			);
		break;
		case 'itemlist':
			return array(
				'type' => 'itemlist',
			);
		break;
		case 'notizen':
			return array(
				'type' => 'notizen',
			);
			break;
		case 'html':
			return array(
				'type' => 'html',
				'title' => $htmlTitle,
				'html' => $htmlContent,
			);
		break;
		case 'separator':
		case 'seperator':
			return array(
				'type' => 'seperator',
				'noLine' => $noLine,
			);
		break;
	}
}


function ko_array_column (array $a, $column) {
	$result = array();
	foreach ($a as $row) {
		if (array_key_exists($column, $row)) {
			$result[] = $row[$column];
		}
	}
	return $result;
}


function ko_array_filter_empty($input, $trim=TRUE) {
	if ($trim) $input = array_map(function($e){return trim($e);}, $input);
	return array_filter($input, function($e){return $e?TRUE:FALSE;});
}


function ko_array_ll($array, $prefix='') {
	return array_map(function($e)use($prefix){return getLL("{$prefix}{$e}");}, $array);
}



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
function ko_include_css($files='') {
	global $ko_path, $PLUGINS;

	$r = '';
	$defaultCSSFiles = array(
		'kool-base.css',
		'inc/bootstrap/plugins/jquery-minicolors-master/jquery.minicolors.css',
		'inc/bootstrap/ko-bootstrap.css',
		'inc/fine-uploader/fine-uploader-gallery.css',
		'inc/tablesaw/stackonly/tablesaw.stackonly.css',
		'inc/jquery-dragtable/dragtable.css',
		'inc/bootstrap/plugins/bootstrap-slider/dist/css/bootstrap-slider.css',
		'kOOL.css',
		'ie6.css',
		'ie7.css',
	);

	foreach ($defaultCSSFiles as $defaultCSSFile) {
		if ($defaultCSSFile == 'ie6.css') {
			$r .= '<!--[if lte IE 6]>' . "\n";
			$r .= '<link rel="stylesheet" type="text/css" href="' . $ko_path . $defaultCSSFile . '?' . filemtime($ko_path . $defaultCSSFile) . '" />' . "\n";
			$r .= '<![endif]-->' . "\n";
		}
		else if ($defaultCSSFile == 'ie7.css') {
			$r .= '<!--[if lte IE 7]>' . "\n";
			$r .= '<link rel="stylesheet" type="text/css" href="' . $ko_path . $defaultCSSFile . '?' . filemtime($ko_path . $defaultCSSFile) . '" />' . "\n";
			$r .= '<![endif]-->' . "\n";
		}
		else {
			$r .= '<link rel="stylesheet" type="text/css" href="' . $ko_path . $defaultCSSFile . '?' . filemtime($ko_path . $defaultCSSFile) . '" />' . "\n";
		}
	}

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

	if(is_array($files)) {
		foreach($files as $file) {
			if(!$file) continue;
			$r .= '<link rel="stylesheet" type="text/css" href="'.$file.'?'.filemtime($file).'" />'."\n";
		}
	}


	//Include favicon and webclip
	$r .= '
<link rel="apple-touch-icon" sizes="57x57" href="/images/webclip/apple-icon-57x57.png">
<link rel="apple-touch-icon" sizes="60x60" href="/images/webclip/apple-icon-60x60.png">
<link rel="apple-touch-icon" sizes="72x72" href="/images/webclip/apple-icon-72x72.png">
<link rel="apple-touch-icon" sizes="76x76" href="/images/webclip/apple-icon-76x76.png">
<link rel="apple-touch-icon" sizes="114x114" href="/images/webclip/apple-icon-114x114.png">
<link rel="apple-touch-icon" sizes="120x120" href="/images/webclip/apple-icon-120x120.png">
<link rel="apple-touch-icon" sizes="144x144" href="/images/webclip/apple-icon-144x144.png">
<link rel="apple-touch-icon" sizes="152x152" href="/images/webclip/apple-icon-152x152.png">
<link rel="apple-touch-icon" sizes="180x180" href="/images/webclip/apple-icon-180x180.png">
<link rel="icon" type="image/png" sizes="192x192"  href="/images/webclip/android-icon-192x192.png">
<link rel="icon" type="image/png" sizes="32x32" href="/images/webclip/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="96x96" href="/images/webclip/favicon-96x96.png">
<link rel="icon" type="image/png" sizes="16x16" href="/images/webclip/favicon-16x16.png">
<link rel="manifest" href="/images/webclip/manifest.json">
<meta name="msapplication-TileColor" content="#ffffff">
<meta name="msapplication-TileImage" content="/images/webclip/ms-icon-144x144.png">
<meta name="theme-color" content="#ffffff">
<link rel="shortcut icon" href="/images/webclip/favicon.ico" type="image/x-icon">
<link rel="icon" href="/images/webclip/favicon.ico" type="image/x-icon">';

	return $r;
}//ko_include_css()




/**
 * Returns HTML code to include the given files
 * @param array $files Relative paths to the JS files to be included
 */
function ko_include_js($files = array(), $module='') {
	global $ko_path, $ko_menu_akt, $BASE_URL;

	$r = '';
	$defaultJSFiles = array(
		'inc/jquery/jquery.js',
		'inc/jquery/jquery-ui.js',
		'inc/jquery/jquery-mobile.js',
		'inc/jquery/jquery-fixedheadertable.js',
		'inc/moment/moment.js',
		'https://www.gstatic.com/charts/loader.js',
		'inc/bootstrap/core/js/bootstrap.min.js',
		'inc/bootstrap/plugins/bootstrap-datetimepicker-master/js/bootstrap-datetimepicker.js',
		'inc/bootstrap/plugins/bootstrap-switch-master/js/bootstrap-switch.min.js',
		'inc/bootstrap/plugins/bootstrap-typeahead/bootstrap3-typeahead.js',
		'inc/bootstrap/plugins/bootstrap-select-master/js/bootstrap-select.js',
		'inc/fine-uploader/jquery.fine-uploader.js',
		'inc/jquery/jquery-peoplesearch.js',
		'inc/jquery/jquery-groupsearch.js',
		'inc/jquery/jquery-dynamicsearch.js',
		'inc/jquery/jquery-asyncform.js',
		'inc/bootstrap/plugins/jquery-minicolors-master/jquery.minicolors.min.js',
		'inc/ckeditor/ckeditor.js',
		'inc/ckeditor/adapters/jquery.js',
		'inc/bootstrap/ko-bootstrap.js',
		'inc/tablesaw/stackonly/tablesaw.stackonly.jquery.js',
		'inc/jquery-dragtable/jquery.dragtable.js',
		'inc/bootstrap/plugins/bootstrap-slider/dist/bootstrap-slider.js',
		'inc/kOOL.js',
	);
	switch(substr($_SESSION['lang'], 0, 2)) {
		case 'de':
			$defaultJSFiles[] = 'inc/moment/de.js';
			$defaultJSFiles[] = 'inc/bootstrap/plugins/bootstrap-select-master/js/i18n/defaults-de_DE.js';
		break;
		case 'en':
			$defaultJSFiles[] = 'inc/moment/en-ca.js';
			$defaultJSFiles[] = 'inc/bootstrap/plugins/bootstrap-select-master/js/i18n/defaults-en_EN.js';

			break;
		case 'fr':
			$defaultJSFiles[] = 'inc/moment/fr.js';
			$defaultJSFiles[] = 'inc/bootstrap/plugins/bootstrap-select-master/js/i18n/defaults-fr_FR.js';

			break;
		case 'nl':
			$defaultJSFiles[] = 'inc/moment/en-ca.js';
			$defaultJSFiles[] = 'inc/bootstrap/plugins/bootstrap-select-master/js/i18n/defaults-nl_NL.js';

			break;
	}

	if($module !== FALSE) {
		$module = $module ? $module : $ko_menu_akt;
		if($module != '') $r .= '<script type=\'text/javascript\'>var kOOL = {base_url:"'.$BASE_URL.'", module:"'.$module.'", sid:"'.session_id().'", language:"'.$_SESSION['lang'].'"};</script>'."\n";

		if($module != '') {
			$r .= '<script type=\'text/javascript\'>
			var kOOL_ll = {
			label_confirm_delete: "' . getLL('list_label_confirm_delete') . '",
			mandatory_field_missing: "' . getLL('js_error_mandatory_field_missing') . '",
			peoplesearch_placeholder_text: "' . getLL('peoplesearch_placeholder') . '",
			groupsearch_placeholder_text: "' . getLL('groupsearch_placeholder') . '",
			relative_removal_warning: "' . getLL('form_leute_family_warning_remove_connection') . '",
			form_ft_button_load_presets_confirm: "' . getLL('form_ft_button_load_presets_confirm') . '",
			form_error_empty_title: "' . getLL('form_error_empty_title') . '",
			form_error_empty_mail: "' . getLL('form_error_empty_mail') . '",
			form_error_incorrect_mail: "' . getLL('form_error_incorrect_mail') . '",
			};
			</script>' . "\n";
		}
	}

	// Add default js files
	foreach($defaultJSFiles as $defaultJSFile) {
		if(!$defaultJSFile) continue;
		if (substr($defaultJSFile, 0, 4) == 'http') $src = $defaultJSFile;
		else $src = $ko_path . $defaultJSFile.'?'.filemtime($ko_path . $defaultJSFile);
		$r .= '<script type=\'text/javascript\' src=\''.$src.'\'></script>'."\n";
	}

	// read templace for fine-uploader input
	$fineUploaderGalleryTemplate = '';
	require_once($ko_path.'inc/fine-uploader/templates/kOOL-gallery.php');
	$r .= $fineUploaderGalleryTemplate;

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
      foreach($languageCodes AS $languageCode => $quality) {
        $quality = substr ($quality,0,5);
        $languages[$languageCode] = str_replace("-", "_", $quality);
      }
    }
  }
	return $languages;
}//getBrowserLanguages()



/**
 * Returns a localized string for the given key in the current language
 *
 * @param string $string
 * @return string|array
*/
function getLL($string) {
	global $LOCAL_LANG;

	if(!$string) return '';
	return $LOCAL_LANG[$_SESSION['lang']][$string];
}//getLL()





/**
 * DEPRECATED function
 */
function ko_mail_get_from($from_name='', $from_email='') {
	ko_log('deprecation', 'Function ko_mail_get_from() is deprecated. Should not be called anymore, ko_prepare_mail() does all the work.');
	return '';
}





/**
  * Get possible sender addresses. SPF check should be used to determine which one to use
	*/
function ko_mail_get_froms($loginID='') {
	global $MAIL_TRANSPORT, $ko_menu_akt, $BLACKLISTED_FROM_DOMAINS;

	$from = array();

	$loginID = $loginID ? $loginID : $_SESSION['ses_userid'];

	//1. current login
	if($loginID != ko_get_guest_id()) {
		$person = ko_get_logged_in_person($loginID);
		$personName = ko_get_person_name($person);
		if(!$personName) $personName = $_SESSION['ses_username'];
		if($person['email']) $from[] = array('email' => $person['email'], 'name' => $personName);
	}

	//2. info data
	$infoName = ko_get_setting('info_name');
	if(!$infoName) $infoName = getLL('kool');
	if(ko_get_setting('info_email')) {
		$from[] = array('email' => ko_get_setting('info_email'), 'name' => $infoName);
	}

	$from[] = array('email' => $MAIL_TRANSPORT['auth_user'], 'name' => $infoName);

	//Remove addresses with blacklisted domains
	// necessary as some mailservers block all mails from these domains
	// (e.g. kathbielbienne.ch does not allow external servers to send mails with this domain in From: header,
	// and they're not using SPF...)
	if(sizeof($BLACKLISTED_FROM_DOMAINS) > 0) {
		foreach($from as $k => $v) {
			$domain = substr(strrchr($v['email'], "@"), 1);
			if(in_array($domain, $BLACKLISTED_FROM_DOMAINS)) unset($from[$k]);
		}
	}

	return $from;
}//ko_mail_get_froms()




/**
 * Sends an email
 */
function ko_send_mail($from, $to, $subject, $body, $files = array(), $cc = array(), $bcc = array(), $replyTo = array()) {
	try {
		$message = ko_prepare_mail($from, $to, $subject, $body, $files, $cc, $bcc, $replyTo);
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



function ko_send_html_mail($from, $to, $subject, $body, $files = array(), $cc = array(), $bcc = array(), $replyTo = array()) {
	global $BASE_PATH;
	try {
		$message = ko_prepare_mail($from, $to, $subject, $body, $files, $cc, $bcc, $replyTo);
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




function ko_check_spf_sender($email) {
	global $MAIL_TRANSPORT;

	//Settings
	$ttl = 3600*24*1;
	$statusOK = array(
		SPFCheck::RESULT_PASS,    //+
		SPFCheck::RESULT_NEUTRAL  //?
		//SPFCheck::RESULT_NONE   //(no answer might mean no SPF entry)
	);


	if(!check_email($email)) return FALSE;

	$domain = substr(strrchr($email, "@"), 1);
	$mailserver = gethostbyname($MAIL_TRANSPORT['host']);

	//Check against manual blacklist
	$blacklistedDomains = explode(',', ko_get_setting('spf_blacklisted_domains'));
	if(in_array($domain, $blacklistedDomains)) return FALSE;

	//Check cache for current entry
	$cached = json_decode(ko_get_setting('spf_domains'), TRUE);
	if(time() - strtotime($cached[$domain]['lastUpdate']) < $ttl) return $cached[$domain]['status'];

	//Check SPF entry
	$checker = new SPFCheck(new DNSRecordGetter());
	$result = $checker->isIPAllowed($mailserver, $domain);

	//Create cache entry and return status
	$r = (in_array($result, $statusOK));
	$cached[$domain] = array('status' => $r, 'lastUpdate' => date('Y-m-d H:i:s'));
	ko_set_setting('spf_domains', json_encode($cached));
	return $r;
}



function ko_mail_get_spf_from(&$from, &$replyTo, &$sender='', $loginID='') {
	//Get default from entries
	$froms = ko_mail_get_froms($loginID);

	//Store given from as originalFrom
	if(is_string($from) && check_email($from)) {
		$fName = '';
		foreach($froms as $f) {
			if($f['email'] == $from) {
				$fName = $f['name'];
				break;
			}
		}
		$originalFrom = array($from => $fName);
	} else if(is_array($from)) {
		$fromEmail = array_pop(array_keys($from));
		$fromName = array_pop(array_values($from));
		$originalFrom = array($fromEmail => $fromName);
		if(check_email($fromEmail)) array_unshift($froms, array('email' => $fromEmail, 'name' => $fromName));
	} else {
		$firstKey = array_key_first($froms);
		$originalFrom = array($froms[$firstKey]['email'] => $froms[$firstKey]['name']);;
	}

	//Check all froms and use the first one that passes the SPF check
	$from = FALSE;
	foreach($froms as $f) {
		if(!check_email($f['email'])) continue;

		//Set replyTo from first entry if not given already
		if(!$replyTo) {
			$replyTo = array($f['email'] => $f['name']);
		}

		//Set from address if SPF check is OK
		if(!$from) {
			if(ko_check_spf_sender($f['email'])) {
				$from = array($f['email'] => $f['name']);
			}
		}
	}

	//No valid from found so use last one as fall back
	if(!$from) {
		$last = array_pop($froms);
		$from = array($last['email'] => $last['name']);
	}

	//If original (desired) From address does not pass SPF check
	// then set Sender address to SPF save address and use original as From
	// See https://tools.ietf.org/html/rfc5322#section-3.6.2
	// and https://swiftmailer.symfony.com/docs/messages.html#specifying-sender-details
	if(array_key_first($originalFrom) && array_key_first($originalFrom) != array_key_first($from)) {
		//But only if original From is not manually blacklisted in email settings
    $domain = substr(strrchr(array_key_first($originalFrom), "@"), 1);
    $blacklistedDomains = explode(',', ko_get_setting('spf_blacklisted_domains'));
    if(!in_array($domain, $blacklistedDomains)) {
      $sender = $from;
      $from = $originalFrom;
    }
	}
}//ko_mail_get_spf_from()





/**
 * Creates a SwiftMailerMessage object with the given data
 * This can either be used for further settings and sent with ko_process_mail()
 * Or it is called from ko_send_mail before this will call ko_process_mail() itself
 *
 * @param null  $from
 * @param null  $to
 * @param null  $subject
 * @param null  $body
 * @param array $files
 * @param array $cc
 * @param array $bcc
 * @param array $replyTo
 * @return Swift_Message
 */
function ko_prepare_mail($from = null, $to = null, $subject = null, $body = null, $files = array(), $cc = array(), $bcc = array(), $replyTo = array()) {

	ko_mail_get_spf_from($from, $replyTo, $sender);

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
		->setBcc($bcc)
		->setReplyTo($replyTo);
	
	if($sender) $message->setSender($sender);

	$message->getHeaders()->addTextHeader('X-Mailer', 'kOOL');

	foreach($files as $filename => $displayName) {
		if($displayName instanceof Swift_Mime_MimeEntity) {
			$message->attach($displayName);
		} else {
			if(!file_exists($filename)) {
				continue;
			}
			$message->attach(
				Swift_Attachment::fromPath($filename)->setFilename($displayName)
			);
		}
	}

	return $message;
} //ko_prepare_mail()



/**
 * Sets the transport method for SwiftMailer
 * Uses setting $MAIL_TRANSPORT from config/ko-config.php
 */
function ko_mail_transport() {
	global $MAIL_TRANSPORT;

	switch(strtolower($MAIL_TRANSPORT['method'])) {
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
		ko_echo_mail($msg, defined('DEBUG_EMAIL_TARGET')?DEBUG_EMAIL_TARGET:'print', defined('DEBUG_EMAIL_DIRECTORY')?DEBUG_EMAIL_DIRECTORY:NULL);
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
function ko_echo_mail(Swift_Message $message, $target='directory', $directory=NULL) {
	global $BASE_PATH;

	$output = '';

	$output .= '<h2>Email sent</h2>';
	$output .= '<b>'.ko_html(trim($message->getHeaders()->get('From'))).'</b><br />';
	$output .= '<b>'.ko_html(trim($message->getHeaders()->get('Sender'))).'</b><br />';
	$output .= '<b>'.ko_html(trim($message->getHeaders()->get('To'))).'</b><br />';
	$output .= '<b>'.ko_html(trim($message->getHeaders()->get('Cc'))).'</b><br />';
	$output .= '<b>'.ko_html(trim($message->getHeaders()->get('Bcc'))).'</b><br />';
	$output .= '<b>'.ko_html(trim($message->getHeaders()->get('Reply-to'))).'</b><br />';
	$output .= '<b>Subject: '.ko_html($message->getSubject()).'</b><br />';
	$output .= '<b>Attachments: </b><ul>';

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
			$output .= '<li>' . $child->getFilename() . '</li>';
		} else {
			$output .= '<li><a href="' . $link . '">' . $child->getFilename() . '</a></li>';
		}
	}

	$output .= '</ul>';
	$output .= '<hr>'.nl2br($message->getBody()).'<hr>';

	if ($target == 'print') {
		print $output;
	} else if ($target == 'directory' && $directory) {
		list($microSec, $sec) = explode(' ', microtime());
		$filename = 'mail_'.date('Ymd_His').'_'.$microSec.'.html';
		file_put_contents($directory.$filename, $output, FILE_APPEND);
	}
} //ko_echo_mail()

function ko_email_signature($format="html") {
	$nl = $format == 'html' ? '<br />' : "\n";

	$signature = $nl.$nl.getLL('email_signature').$nl;

	$person = ko_get_logged_in_person();

	if (!empty($person['vorname']) && !empty($person['nachname'])) {
		$signature.= $person['vorname'] . " " . $person['nachname'].$nl;
	}

	$signature.= ko_get_setting('info_name').$nl."______".$nl.ko_get_setting('info_address').$nl.ko_get_setting('info_zip').' '.ko_get_setting('info_city').$nl.ko_get_setting('info_phone').$nl.ko_get_setting('info_url').$nl;

	return $signature;
}





/**
 * DEPRECATED FUNCTION
 * Old function for sending email. Use ko_send_mail instead which is based on SwiftMailer.
 */
function ko_send_email($to, $subject, $message, $headers) {
	global $MAIL_TRANSPORT;

	if($headers['From']) {
		$reply_to = $headers['From'];
	} else {
		$reply_to = array();
	}

	ko_send_mail('', $to, $subject, $message, null, null, null, $reply_to);
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
function ko_fetch_url($url, $to=10) {
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
			header('Location: '.$loc); exit;
		}
	}
}//ko_redirect_after_login()






/**
 * Checks for constant FORCE_SSL and redirects to SSL enabled $BASE_URL
 */
function ko_check_ssl() {
	global $BASE_URL;

	if(FORCE_SSL === TRUE && (empty($_SERVER['HTTPS']) || strtolower($_SERVER['HTTPS']) == 'off')) {
		//Only redirect if https URL is set in BASE_URL
		if(strtolower(substr($BASE_URL, 0, 5)) == 'https') {
			header('Location: '.$BASE_URL, TRUE, 301); exit;
		}
	}
}//ko_check_ssl()






function ko_check_login() {
	global $ko_path, $LANGS, $LIB_LANGS, $LOCAL_LANG, $WEB_LANGS, $BASE_URL;

	$do_guest = TRUE;
	$reinit = FALSE;

	//Login through sso (only from TYPO3 so far)
	if(ALLOW_SSO && KOOL_ENCRYPTION_KEY && $_GET["sso"] && $_GET["sig"]) {
		$ssoError = FALSE;
		//Decrypt SSO data
		require_once($BASE_PATH.'inc/class.openssl.php');
		$crypt = new openssl('AES-256-CBC');
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
				include($ko_path."inc/lang.inc");
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

		include("inc/session.inc");
		$_SESSION = array();
		$_SESSION['ses_userid'] = ko_get_guest_id();
		$_SESSION['ses_username'] = 'ko_guest';

		$reinit = TRUE;
	}

	//Login
	if($_POST['Login'] && (!$_SESSION['ses_username'] || $_SESSION['ses_username'] == 'ko_guest')) {
		$login = db_select_data('ko_admin', "WHERE MD5(`login`) = '".md5($_POST['username'])."' AND `password` = '".md5($_POST['password'])."'", '*', '', '', TRUE);
		if($login['id'] > 0 && $login['login'] == $_POST['username']) {  //Valid login
			//Create new session id after login (to prevent session fixation)
			session_regenerate_id(TRUE);
			//Empty session data so settings from ko_guest will not be used for logged in user
			$_SESSION = array();

			$_SESSION['ses_username'] = $login['login'];
			$_SESSION['ses_userid'] = $login['id'];
			$_SESSION['disable_password_change'] = $login['disable_password_change'];

			$u_admingroups = ko_get_admingroups($login['id']);
			foreach($u_admingroups AS $u_admingroup) {
				if ($u_admingroup['disable_password_change'] == 1) {
					$_SESSION["disable_password_change"] = 1;
				}
			}

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

	// update absence person cookie for front_module
	if ($_GET['fm_absence_token']) {
		$token = format_userinput($_GET['fm_absence_token'],'text');
		if (ko_fm_absence_check_hash($token)) {
			$absence_cookie = json_decode($_COOKIE['fm_absence_persons'], TRUE);
			if(empty($absence_cookie)) $absence_cookie = [];
			$absence_cookie[$token] = substr($token,10);
			setcookie("fm_absence_persons", json_encode($absence_cookie), time()+(86400*365), "/", $_SERVER['HTTP_HOST']);
			$_COOKIE['fm_absence_persons'] = json_encode($absence_cookie);
			header('location: ' . $BASE_URL . 'index.php?action=select_absence_person&id=' . substr($token,10)); exit;
		}
	}

	if($reinit) {
		unset($GLOBALS['kOOL']);
		ko_init();

		//Select language from userpref, if set
		$user_lang = ko_get_userpref($_SESSION['ses_userid'], 'lang');
		if(!$user_lang) $user_lang = $LANGS[0];
		if($user_lang != '' && in_array($user_lang, $LANGS)) {
			$_SESSION['lang'] = $user_lang;
			include($ko_path.'inc/lang.inc');
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


function print_c($array, $title='') {
	print "<!-- DEBUG: $title\n";
	print_r($array);
	print "\n-->";
}//print_c()





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

				case "leute_no_family":
					$found = TRUE;
					if(substr(trim($line), 0, strlen('$LEUTE_NO_FAMILY')) == '$LEUTE_NO_FAMILY') $line = $data;
				break;  //leute_no_family

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

				case "force_ssl":
					$found = TRUE;
					if(substr(trim($line), 0, strlen("@define('FORCE_SSL")) == "@define('FORCE_SSL") $line = $data;
				break; //force_ssl
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
	fflush($fp);
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

function utf8_decode_array(&$value, $key) {
	$value = utf8_decode($value);
}

function utf8_encode_array(&$value, $key) {
	$value = utf8_encode($value);
}

function urldecode_array(&$value, $key) {
	$value = urldecode($value);
}

function rawurldecode_array(&$value, $key) {
	$value = rawurldecode($value);
}

function ko_utf8_encode_assoc($array) {
	if (!is_array($array)) {
		return utf8_encode($array);
	}
	else {
		$keys = array_keys($array);
		foreach ($keys as $kk => $key) {
			$keys[$kk] = ko_utf8_encode_assoc($key);
		}
		foreach ($array as $vk => $value) {
			$array[$vk] = ko_utf8_encode_assoc($value);
		}
		return array_combine($keys, $array);
	}
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


function ko_get_crm_contacts_access($contact, $type='view', $userId='ses_userid') {
	global $access;
	if ($userId == 'ses_userid') {
		$userId = $_SESSION['ses_userid'];
		if (!isset($access['crm'])) {
			ko_get_access('crm');
		}
	}
	else {
		ko_get_access('crm', $userId);
	}
	if (!$contact) return FALSE;
	if(!$contact['project_id'] && $contact['project_id'] !== 0 && $contact['project_id'] !== '0') return FALSE;
	$cruser = $contact['cruser'];
	$userAccess = max($access['crm']['ALL'], $access['crm'][$contact['project_id']]);
	$ownEntry = $contact['cruser'] == $userId;
	switch ($type) {
		case 'view':
			if ($userAccess < 1) return FALSE;
			if ($userAccess < 3) return $ownEntry;
			if ($userAccess < 5) {
				ko_get_login($_SESSION['ses_userid'], $login);
				ko_get_login($cruser, $cruserLogin);
				$adminGroups = trim($login['admingroups']);
				$cruserAdminGroups = trim($cruserLogin['admingroups']);
				$adminGroupsA = array();
				if ($adminGroups) $adminGroupsA = explode(',', $adminGroups);
				$cruserAdminGroupsA = array();
				if ($cruserAdminGroups) $cruserAdminGroupsA = explode(',', $cruserAdminGroups);
				$diff = array_diff($cruserAdminGroupsA, $adminGroupsA);
				return (sizeof($diff) != $cruserAdminGroupsA);
			}
			if ($userAccess > 4) return TRUE;
			return FALSE;
			break;
		case 'edit':
		case 'delete':
			if ($userAccess < 2) return FALSE;
			if ($userAccess < 4) return $ownEntry;
			if ($userAccess < 5) {
				ko_get_login($_SESSION['ses_userid'], $login);
				ko_get_login($cruser, $cruserLogin);
				$adminGroups = trim($login['admingroups']);
				$cruserAdminGroups = trim($cruserLogin['admingroups']);
				$adminGroupsA = array();
				if ($adminGroups) $adminGroupsA = explode(',', $adminGroups);
				$cruserAdminGroupsA = array();
				if ($cruserAdminGroups) $cruserAdminGroupsA = explode(',', $cruserAdminGroups);
				$diff = array_diff($cruserAdminGroupsA, $adminGroupsA);
				return (sizeof($diff) != $cruserAdminGroupsA);
			}
			if ($userAccess > 4) return TRUE;
			return FALSE;
			break;
		default:
			return FALSE;
			break;
	}
}//ko_get_crm_contacts_access()


function ko_get_crm_contact_form_group($hide_columns=array(), $default_values=array()) {
	global $ko_path, $access, $KOTA;

	if (!ko_module_installed('crm')) return;
	if (!isset($access['crm'])) ko_get_access('crm');
	if ($access['crm']['MAX'] < 2) return;

	ko_include_kota(array('ko_crm_contacts'));

	if (array_key_exists('leute_ids', $default_values)) {
		kota_assign_values('ko_crm_contacts', array('leute_ids' => $default_values['leute_ids']), FALSE);
		unset($KOTA['ko_crm_contacts']['leute_ids']['pre']);
	}

	if (!in_array('cruser', $hide_columns)) $hide_columns[] = 'cruser';

	$form_data = array();
	$groups = ko_multiedit_formular('ko_crm_contacts', '', 0, '', $form_data, TRUE);
	$group = $groups[0];
	$group['titel'] = getLL('crm_contacts_form_title_new');
	$group['name'] = 'crm_addon';
	$group['state'] = 'closed';
	if(!isset($default_values['date'])) $default_values['date'] = strftime('%d.%m.%Y %H:%M');
	foreach ($group['row'] as $k1 => $row) {
		foreach ($row['inputs'] as $k2 => $input) {
			if (in_array($input['colname'], $hide_columns)) unset ($group['row'][$k1]['inputs'][$k2]);
			if (array_key_exists($input['colname'], $default_values)) $group['row'][$k1]['inputs'][$k2]['value'] = $default_values[$input['colname']];
		}
	}
	$value = '<input type="hidden" id="crm_addon_create_entry" name="crm_addon[create_entry]" value="0">';
	$value .= "
<script>
	$('#group_crm_addon_content').on('hide.bs.collapse', function () {
		$('#crm_addon_create_entry').val('0');
	});
	$('#group_crm_addon_content').on('show.bs.collapse', function () {
		$('#crm_addon_create_entry').val('1');
	});

	disable_onunloadcheck();

</script>";
	$group['row'][sizeof($group['row'])]['inputs'][0] = array('type' => 'html', 'value' => $value);
	return array($group);
}//ko_get_crm_contact_form_group()


/**
 * Creates a crm entry based on the data in the $_POST array (see function ko_get_crm_contact_form_group(...))
 *
 * @param bool|TRUE  $remove_post_entries     indicates whether to remove used data from the post array
 * @param array      $values                  can be used to specify values manually
 * @param bool|FALSE $force                   per default, an entry is only created if the field with name
 *                                            "crm_addon[create_entry]" is true. Use this param to force an entry
 * @return int|void                           the id of the created crm contact entry
 */
function ko_create_crm_contact_from_post($remove_post_entries=TRUE, $values=array(), $force = FALSE) {
	global $access, $KOTA, $mysql_pass;
	if (!ko_module_installed('crm')) return;
	if (!isset($access['crm'])) ko_get_access('crm');
	if ($access['crm']['MAX'] < 2) return;

	if ($_POST['crm_addon']['create_entry'] || $force) {
		ko_include_kota(array('ko_crm_contacts'));

		$temp = array();
		$tempId = $_POST['id'];
		foreach ($_POST['koi'] as $k => $koiPost) {
			if ($k == 'ko_crm_contacts') continue;
			$temp[$k] = $koiPost;
			unset($_POST['koi'][$k]);
		}
		foreach ($values as $k => $v) {
			$_POST['koi']['ko_crm_contacts'][$k] = array(0 => $v);
		}

		$ids = array(0);
		$table = 'ko_crm_contacts';

		if ($_FILES['koi']['name']['ko_crm_contacts']) {
			$columns = array_merge(array_keys($_POST['koi']['ko_crm_contacts']), array_keys($_FILES['koi']['name']['ko_crm_contacts']));
		}
		else {
			$columns = array_keys($_POST['koi']['ko_crm_contacts']);
		}
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
		$hash_code = md5(md5($mysql_pass.$table.implode(":", $columns).implode(":", $ids)));
		$hash = $table."@".implode(",", $columns)."@".implode(",", $ids)."@".$hash_code;

		$_POST['id'] = $hash;

		$newId = kota_submit_multiedit('', 'new_crm_contact');

		foreach ($temp as $k => $v) {
			$_POST['koi'][$k] = $v;
		}
		if ($tempId) $_POST['id'] = $tempId;
		else unset($_POST['id']);
	}

	if ($remove_post_entries) {
		unset($_POST['koi']['ko_crm_contacts']);
		unset($_POST['crm_addon']);
	}

	return $newId;
}


function ko_get_leute_from_crm_project($projectId = null, $statusId = null) {
	if ($statusId === null) {
		if ($projectId === null) {
			$q = "SELECT DISTINCT m1.`leute_id` AS `id` FROM ko_crm_mapping m1 JOIN ko_crm_contacts c1 ON m1.`contact_id` = c1.`id`";
		}
		else {
			$q = "SELECT DISTINCT m1.`leute_id` AS `id` FROM ko_crm_mapping m1 JOIN ko_crm_contacts c1 ON m1.`contact_id` = c1.`id` WHERE c1.`project_id` = '".$projectId."'";
		}
	}
	else {
		if ($projectId === null) {
			$q = "SELECT DISTINCT m1.`leute_id` AS `id` FROM ko_crm_mapping m1 JOIN ko_crm_contacts c1 ON m1.`contact_id` = c1.`id` WHERE c1.`status_id` = '".$statusId."' AND NOT EXISTS (SELECT c2.`id` FROM ko_crm_mapping m2 JOIN ko_crm_contacts c2 ON m2.`contact_id` = c2.`id` WHERE c2.`date` > c1.`date` AND c2.`project_id` = c1.`project_id` AND m2.`leute_id` = m1.`leute_id`);";
		}
		else {
			$q = "SELECT DISTINCT m1.`leute_id` AS `id` FROM ko_crm_mapping m1 JOIN ko_crm_contacts c1 ON m1.`contact_id` = c1.`id` WHERE c1.`project_id` = '".$projectId."' AND c1.`status_id` = '".$statusId."' AND NOT EXISTS (SELECT c2.`id` FROM ko_crm_mapping m2 JOIN ko_crm_contacts c2 ON m2.`contact_id` = c2.`id` WHERE c2.`date` > c1.`date` AND c2.`project_id` = '".$projectId."' AND m2.`leute_id` = m1.`leute_id`);";
		}
	}
	$es = db_query($q);
	$result = array();
	foreach ($es as $row) {
		$result[] = $row['id'];
	}
	return $result;
}



function ko_get_searchbox($module) {
	global $smarty, $ENABLE_VERSIONING_FASTFILTER, $access, $KOTA;

	$inputs = array();
	$general_input = NULL;
	$hide_buttons = FALSE;
	$has_active_filters = FALSE;
	$hide_form = FALSE;
	$general_input = array('kota' => FALSE, 'code' => '<input type="text" class="input-sm form-control" name="general_search" placeholder="'.getLL('general_search_placeholder').'" autocomplete="off">');
	$found = FALSE;
	switch ($module) {
		case 'groups':
			if (in_array($_SESSION['show'], array('list_groups'))) {
				if (!is_array($access['groups'])) ko_get_access('groups');
				if ($access['groups']['MAX'] < 1) break;
				if ($access['groups']['MAX'] < 1) break;

				$found = TRUE;
			}
		break;
		case 'leute':
			if (in_array($_SESSION['show'], array('show_all', 'show_adressliste', 'show_my_list', 'geburtstagsliste'))) {
				if (!is_array($access['leute'])) ko_get_access('leute');
				$max_rights = $access['leute']['MAX'];

				$found = TRUE;

				$fast_filter = ko_get_fast_filter();
				$ajax1 = $ajax2 = "";
				foreach($fast_filter as $id) {
					$field_name = '';
					ko_get_filter_by_id($id, $ff);
					$ff_code = str_replace("var1", ("fastfilter".$id), $ff["code1"]);
					$ff_code = str_replace("submit_filter", ("submit_fast_filter"), $ff_code);
					$ajax1 .= "fastfilter".$id.",";

					if($ff['sql1'] == "kota_filter") {
						$orig_ff = db_select_data("ko_filter", "WHERE id = " . $id, "code1", "", "Limit 1", TRUE, TRUE);
						list($prefix, $fcn, $table, $dbcol) = explode(":", $orig_ff['code1']);
						$field_name = "kota_filter[$table:$dbcol]";
						$ff_code = str_replace("name=\"".$field_name."\"", "name=\"fastfilter". $id."\"", $ff_code);
						$ff_code = preg_replace('/<div class="checkbox">.*?<\/div>/', "", $ff_code, 1);
						$label = '';
					} else {
						$label = $ff["name"];
					}

					$ajax2 .= '\'+escape(this.form.fastfilter'.$id.'.value.trim())+\',';
					$inputs[] = array('kota' => FALSE, 'code' => $ff_code, 'label' => $label);
				}
				$inputs[] = array('html' => '<div class="btn-field"><button type="button" class="btn btn-sm btn-primary" name="submit_fast_filter" value="'.getLL("OK").'" onclick="sendReq(\'../leute/inc/ajax.php\', \'action,'.$ajax1.'sesid\', \'leuteschnellfilter,'.$ajax2.session_id().'\', do_element);">' . getLL("OK") . '</button></div>');

				//Versioning
				if($ENABLE_VERSIONING_FASTFILTER && $max_rights > 1) {
					$version_set = $_SESSION["leute_version"] ? TRUE : FALSE;
					$inputs[] = array('kota' => FALSE, 'active' => $version_set, 'code' => '<input class="input-sm form-control" type="date" value="'.sql2datum($_SESSION["leute_version"]).'" name="date_version">', 'label' => getLL("fastfilter_version"));
					$html = '<div class="btn-field"><button type="submit" class="btn btn-sm btn-primary" name="submit_leute_version" onclick="set_action(\'submit_leute_version\', this);" value="'.getLL("OK").'">' . getLL("OK") . '</button>';
					if($version_set) $html .= '<button type="submit" class="btn btn-sm btn-danger" name="submit_leute_version_clear" value="'.getLL("delete").'" onclick="set_action(\'clear_leute_version\', this);">' . getLL("delete") . '</button>';
					$html .= '</div>';
					$inputs[] = array('html' => $html);
				}

				$active_filters_html = '';
				//Angewandte Filter anzeigen
				if(sizeof($_SESSION["filter"]) > 0) {
					$found_filter = FALSE;
					$general_filter = db_select_data('ko_filter', "WHERE `typ` = 'leute' AND `name` = 'fastfilter'", 'id', '', '', TRUE, TRUE);
					$general_filter_id = $general_filter['id'];
					foreach($_SESSION["filter"] as $f_i => $f) {
						if(!is_numeric($f_i)) continue;

						if(!$found_filter) {
							$found_filter = TRUE;
							$code_akt_filter = '';
							$size = 0;
							foreach($_SESSION['filter'] as $k => $v) {
								if(!is_numeric($k)) continue;
								$size++;
							}
							$active_filters_html .= '<label for="sel_filter">'.getLL('leute_filter_current').':</label>';
							$active_filters_html .= '<div class="btn-group-vertical full-width btn-group-sm">';
						}

						ko_get_filter_by_id($f[0], $f_);

						//Name des Filters
						$f_name = $f_["name"];

						// set value of general filter field
						if ($f[0] == $general_filter_id) {
							$general_search_string = format_userinput($f[1][1], 'text');
							$general_input = array('kota' => FALSE, 'code' => '<input type="text" class="input-sm form-control" name="general_search" placeholder="'.getLL('general_search_placeholder').'" value="'.$general_search_string.'" autocomplete="off">');
						}

						$processedValues = array();
						//Tabellen-Name, auf den dieser Filter am ehesten wirkt, auslesen/erraten:
						$col = array();
						if ($f_['sql1'] == 'kota_filter') {
							ko_leute_filter_make_bw_compatible($f_, $f);
							$kotaFilterData = $f[1][1]['kota_filter_data']['ko_leute'];
							$f[1] = array();
							$c = 1;
							foreach ($kotaFilterData as $column => $value) {
								if ($KOTA['ko_leute'][$column]['form']['type'] == 'jsdate' || $KOTA['ko_leute'][$column]['filter']['type'] == 'jsdate') {
									$valueString = "";
									if ($value['neg']) $valueString .= '!';
									$valueString .= sql2datum($value['from']);
									$valueString .= '-'.sql2datum($value['to']);
									$processedValues[$column] = $valueString;
								}
								$f[1][$c] = $value;
								$col[$c] = $column;
								$c++;
							}
						} else {
							for($c=1; $c<5; $c++) {
								list($col[$c]) = explode(' ', $f_['sql'.$c]);
							}
						}

						//Variablen auslesen
						$vars = "";
						$fulltitle = '';
						$t1 = $t2 = '';
						for($i = 1; $i <= sizeof($f[1]); $i++) {
							if ($processedValues[$col[$i]]) {
								$v = $processedValues[$col[$i]];
							} else {
								$v = map_leute_daten($f[1][$i], ($col[$i] ? $col[$i] : $col[1]), $t1, $t2, FALSE, array('num' => $i));
							}
							if (!$v) $v = $f[1][$i];
							$v = strip_tags($v);
							//Limit length of group name for filter list
							if(!$fulltitle) $fulltitle = $v;
							else $fulltitle .= ', '.$v;
							if($col[$i] == 'groups' && strlen($v) > 25) {
								$v = substr($v, 0, 10).'[..]'.substr($v, -10);
							}
							$vars .= $v.', ';
						}
						$vars = substr($vars, 0, -2);

						//Negative Filter markieren
						if($f[2] == 1 && $f_['sql1'] != 'kota_filter') $neg = "!";
						else $neg = "";

						$label = ko_html($f_i.':'.$f_name.': '.$neg.$vars);
						$fulltitle = ko_html($f_name.': '.$neg.$fulltitle);
						$active_filters_html .= '<button type="button" class="btn btn-sm btn-danger" title="'.$fulltitle.'" data-filter-id="'.$f_i.'" onclick="sendReq('."'../leute/inc/ajax.php', 'action,id,sesid', 'leutefilterdel,'+$(this).attr('data-filter-id')+',".session_id()."', do_element);".'"><span class="pull-left">'.$label.'</span><span class="pull-right"><i class="fa fa-remove icon-line-height"></i></span></button>';
					}//foreach(filter)

					if($found_filter) {
						$active_filters_html = '<hr>' . $active_filters_html;
						$active_filters_html .= '</div>';

						//Buttons to delete applied filters
						$active_filters_html .= '<br><br><button type="button" class="btn btn-sm btn-danger full-width" value="'.getLL('delete_all').'" name="submit_del_all_filter" onclick="sendReq(\'../leute/inc/ajax.php\', \'action,sesid\', \'leutefilterdelall,'.session_id().'\', do_element);">' . getLL('delete_all') . '</button>';
					}
				}//if(sizeof(filter)>0)

				$additional_elements = array();
				// hidden
				if ($max_rights > 0) {
					$additional_elements[] = array(
						'type' => 'button',
						'onclick' => 'sendReq(\'../leute/inc/ajax.php\', [\'action\',\'state\',\'sesid\'], [\'showhidden\','.(ko_get_userpref($_SESSION["ses_userid"], "leute_show_hidden")?'false':'true').',\''.session_id().'\'], do_element);toggle_hidden_deleted_warning(\'sb-show-hidden-li\','.(ko_get_userpref($_SESSION["ses_userid"], "leute_show_hidden") == 1 ? '0' : '1') .')',
						'active' => ko_get_userpref($_SESSION["ses_userid"], "leute_show_hidden") == 1,
						'title' => ko_get_userpref($_SESSION["ses_userid"], "leute_show_hidden") == 1 ? getLL('leute_hide_hidden') : getLL("leute_show_hidden"),
						'icon' => 'eye',
						'id' => 'sb-show-hidden-li',
					);
				}
				// deleted
				if ($max_rights > 2) {
					$additional_elements[] = array(
						'type' => 'button',
						'onclick' => 'sendReq(\'../leute/inc/ajax.php\', [\'action\',\'state\',\'sesid\'], [\'showdeleted\','.(ko_get_userpref($_SESSION["ses_userid"], "leute_show_deleted")?'false':'true').',\''.session_id().'\'], do_element);toggle_hidden_deleted_warning(\'sb-show-deleted-li\','.(ko_get_userpref($_SESSION["ses_userid"], "leute_show_deleted") == 1 ? '0' : '1') .')',
						'active' => ko_get_userpref($_SESSION["ses_userid"], "leute_show_deleted") == 1,
						'title' => ko_get_userpref($_SESSION["ses_userid"], "leute_show_deleted") == 1 ? getLL('leute_hide_deleted') : getLL("leute_show_deleted"),
						'icon' => 'trash',
						'id' => 'sb-show-deleted-li',
					);
				}

				$inputs[] = array('html' => $active_filters_html);
				$has_active_filters = $found_filter || $version_set;
				$hide_buttons = TRUE;
				$hide_form = TRUE;
			}
		break;
	}
	return $found ? array('inputs' => $inputs, 'general_input' => $general_input, 'hide_buttons' => $hide_buttons, 'has_active_filters' => $has_active_filters, 'hide_form' => $hide_form, 'additional_elements' => $additional_elements) : array();
}



function ko_get_searchbox_code($module, $mode="all", $sb=NULL) {
	global $smarty;

	if ($sb === NULL) $sb = ko_get_searchbox($module);
	$smarty->assign('searchbox', $sb);
	$smarty->assign('general_only', FALSE);
	$smarty->assign('searchbox_only', FALSE);
	$smarty->assign('hide_li', FALSE);
	switch ($mode) {
		case 'all':
		break;
		case 'general_only':
			$smarty->assign('hide_li', TRUE);
			$smarty->assign('general_only', TRUE);
		break;
		case 'searchbox_only':
			$smarty->assign('hide_li', TRUE);
			$smarty->assign('searchbox_only', TRUE);
		break;
		default:
			$smarty->assign('hide_li', TRUE);
			$smarty->assign('additional_only', TRUE);
			$smarty->assign('additional_only_id', $mode);
		break;
	}
	return $smarty->fetch('ko_searchbox.tpl');
}



function ko_get_botstrap_cols() {
	return ko_get_bootstrap_cols();
}
function ko_get_bootstrap_cols() {
	global $BOOTSTRAP_COLS_PER_ROW;
	return $BOOTSTRAP_COLS_PER_ROW;
}


/**
 * found on: http://stackoverflow.com/questions/3302857/algorithm-to-get-the-excel-like-column-name-of-a-number
 *
 * @param $number the column index, starts from 0
 */
function ko_get_excel_col($n) {
	for($r = ""; $n >= 0; $n = intval($n / 26) - 1)
		$r = chr($n%26 + 0x41) . $r;
	return $r;
}


/**
 * Decouples given addresses (array of ids) from household.
 *
 * @param array|string|null $peopleIds
 *      If NULL given, selects children with age >= ko_get_setting('candidate_adults_min_age') from households
 *      If 'APPLY_FILTER', applies filter
 */
function ko_decouple_from_household($peopleIds=NULL, $checkAccess=FALSE) {
	GLOBAL $access;

	// select candidates from db if $peopleIds is NULL
	if ($peopleIds === NULL) {
		$minAge = ko_get_setting('candidate_adults_min_age');
		$minAge = $minAge ? $minAge : 18;
		$peopleIds_ = db_select_data('ko_leute', "WHERE (`famfunction` = 'child' AND `famid` > 0 AND TIMESTAMPDIFF(hour,`geburtsdatum`,CURDATE())/8766 >= '{$minAge}') AND `deleted` = '0'".ko_get_leute_hidden_sql(), 'id');
		$peopleIds = array();
		foreach ($peopleIds_ as $peopleId_) {
			$peopleIds[] = $peopleId_['id'];
		}
	} else if ($peopleIds == 'APPLY_FILTER') {
		apply_leute_filter($_SESSION["filter"], $where);
		$minAge = ko_get_setting('candidate_adults_min_age');
		$minAge = $minAge ? $minAge : 18;
		$peopleIds_ = db_select_data('ko_leute', "WHERE (`famfunction` = 'child' AND `famid` > 0 AND TIMESTAMPDIFF(hour,`geburtsdatum`,CURDATE())/8766 >= '{$minAge}') {$where}", 'id');
		$peopleIds = array();
		foreach ($peopleIds_ as $peopleId_) {
			$peopleIds[] = $peopleId_['id'];
		}
	}

	// check access (if $checkAccess is true)
	if ($checkAccess && $access['leute']['ALL'] < 2) {
		foreach ($peopleIds as $k => $peopleId) {
			if ($access['leute'][$peopleId] < 2) unset($peopleIds[$k]);
		}
	}

	// delete famid and famfunction from db entries
	if (is_array($peopleIds) && sizeof($peopleIds > 0)) {
		db_update_data('ko_leute', "WHERE `id` IN (".implode(',', $peopleIds).")", array('famid' => '0', 'famfunction' => ''));
	} else {
		return;
	}

	// create log entry
	ko_log('leute_decouple_from_household', "decoupled the following address ids from their respective households: " . implode(',', $peopleIds));
}




/**
 * Creates an events title, e.g. for calendar view in exports
 *
 * @param array $event Event to be processed
 * @param array $eg Event group of the given event
 * @param string $mode Title mode as set by userpref ('kommentar', 'eventgruppen_id' or 'both')
 * @return array $title An array holding the text and the short text which can be used for the event's title
 */
function ko_daten_get_event_title($event, $eg, $mode) {
	$title = array();

	if(!isset($eg['name'])) $eg = $eg[$event['eventgruppen_id']];

	//User event group name if no short name is given (so titles won't end up empty)
	if($eg['shortname'] == '') $eg['shortname'] = $eg['name'];

	//Set default value if userpref is still empty
	if($mode == '') $mode = 'both';

	if($mode == 'kommentar') {
		$title['text'] = $event['kommentar'] ? $event['kommentar'] : $eg['name'];
		$title['short'] = $event['kommentar'] ? $event['kommentar'] : $eg['shortname'];
	}
	else if($mode == 'title') {
		$title['text'] = $event['title'] ? $event['title'] : $eg['name'];
		$title['short'] = $event['title'] ? $event['title'] : $eg['shortname'];
	}
	else if($mode == 'eventgruppen_id') {
		$title['text'] = $eg['name'];
		$title['short'] = $eg['shortname'];
	}
	else if($mode == 'both') {
		$title['text'] = $eg['name'].($event['title'] ? ': '.$event['title'] : '');
		$title['short'] = $eg['shortname'].($event['title'] ? ': '.$event['title'] : '');
	}
	else if($mode == 'eventgruppen_id_kommentar') {
		$title['text'] = $eg['name'].($event['kommentar'] ? ': '.$event['kommentar'] : '');
		$title['short'] = $eg['shortname'].($event['kommentar'] ? ': '.$event['kommentar'] : '');
	}
	else if($mode == 'eventgruppen_id_kommentar2') {
		$title['text'] = $eg['name'].($event['kommentar2'] ? ': '.$event['kommentar2'] : '');
		$title['short'] = $eg['shortname'].($event['kommentar2'] ? ': '.$event['kommentar2'] : '');
	}
	else if($mode == 'title_kommentar') {
		$title['text'] = implode(': ', array($event['title'], $event['kommentar']));
		$title['short'] = implode(': ', array($event['title'], $event['kommentar']));
	}
	else if($mode == 'title_kommentar2') {
		$title['text'] = implode(': ', array($event['title'], $event['kommentar2']));
		$title['short'] = implode(': ', array($event['title'], $event['kommentar2']));
	}
	else {
		if($event[$mode]) $title['text'] = $title['short'] = $event[$mode];
		else $title['text'] = $title['short'] = '';
	}
	//Prevent empty short text
	if($title['short'] == '') $title['short'] = $title['text'];

	// Strip HTML Tags
	foreach ($title as $k => $v) {
		$title[$k] = strip_tags($v);
	}

	return $title;
}//ko_daten_get_event_title()




/**
 * Returns Zweck for new reservations stored with event
 */
function ko_daten_get_zweck_for_res($event) {
	if(trim($event['title'])) {
		$zweck = trim($event['title']);
	} else {
		ko_get_eventgruppe_by_id($event['eventgruppen_id'], $eg);
		$zweck = $eg['name'];
	}

	$hookData = array('event' => $event, 'zweck' => $zweck);
	hook_function_inline('ko_daten_get_zweck_for_res', $hookData);
	$zweck = $hookData['zweck'];

	return $zweck;
}//ko_daten_get_zweck_for_res()



/**
 * parses the time filter as used in daten and reservation module. Allowed values are:
 *   ''
 *   'today'
 *   'immer'
 *   '/[-\\+]\d+/'
 *   '%Y-%m-%d'
 *
 * @param $v
 * @return string the parsed date in form '%Y-%m-%d' | empty string (means 'immer') | 'today' | NULL (error)
 */
function ko_daten_parse_time_filter($v, $default='today', $parseToday=TRUE) {
	if($v === NULL) {
		if ($default == 'today' && $parseToday) $d = date('Y-m-d');
		else $d = $default;
	} // default
	else if (preg_match('/\d{4}-\d{2}-\d{2}/', $v)) $d = $v; // format YYYY-mm-dd
	else if (preg_match('/\d{2}.\d{2}.\d{4}/', $v)) $d = sql_datum($v); // format dd.mm.YYYY
	else if ($v == 'today' || strtolower($v) == strtolower(getLL('time_today'))) $d = ($parseToday?date('Y-m-d'):'today'); // today
	else if ($v == 'immer' || $v == '') $d = ''; // always
	else if (preg_match('/^[-\\+]\d+$/', $v)) $d = date('Y-m-d', strtotime(sprintf("%+d months", $v), time())); // format +5 or -10
	else $d = NULL; // unknown format

	return $d;
}


/**
 * @param $text    string      The string from which to detect the encoding
 * @return         string      The detected encoding (either utf-8, macintosh or iso-8859-1)
 */
function ko_detect_encoding(&$text) {
	$encoding = 'iso-8859-1';
	switch(mb_detect_encoding($text, '', TRUE)) {
		case 'UTF-8':
			$encoding = 'utf-8';
			$text = utf8_decode($text);
			break;
		case 'ASCII':
			$encoding = 'iso-8859-1';
			break;
		default:
			if(FALSE !== strpos($text, chr(159)) || FALSE !== strpos($text, chr(138)) || FALSE !== strpos($text, chr(154))) {
				$encoding = 'macintosh';
				$text = iconv('macintosh', 'ISO-8859-1//TRANSLIT', $text);
			}
	}

	return $encoding;
}


/**
 * @param string        $date
 * @param string        $targetFormat either 'date' or 'datetime'
 * @param string        $dateTimeDelimiter the delimiter between the date part and the time part
 * @param null|string   $dmyOrder the order of day, month and year. A string consisting of exaclty the three characters d, m and y in any order.
 * @return bool|string
 */
function ko_parse_date($date, $targetFormat="date", $dateTimeDelimiter=' ', $dmyOrder=NULL) {
	$date = trim($date);

	if (!$date) return '';

	$time = NULL;
	if (strpos($date, $dateTimeDelimiter) !== FALSE) {
		list($date, $time) = explode($dateTimeDelimiter, $date);
	}

	if ($dmyOrder !== NULL && strlen($dmyOrder) == 3) {
		$dmyOrder = strtolower($dmyOrder);

		$lengths = array();
		$chars = str_split($dmyOrder);
		foreach ($chars as $k => $c) {
			if ($c == 'y') $lengths[] = '\d{2}|\d{4}';
			else $lengths[] = '\d{1,2}';
		}
		if (preg_match('/^('.$lengths[0].')[\/\.:-]('.$lengths[1].')[\/\.:-]('.$lengths[2].')$/', $date, $matches)) {
			list($dummy, ${$chars[0]}, ${$chars[1]}, ${$chars[2]}) = $matches;

			if (strlen($y) == 2) {
				$Y = $y > date('y') + 5 ? ('19'.$y) : ('20'.$y);
				$unixDate = mktime(0, 0, 0, $m, $d, $Y);
				if (date('d', $unixDate) != $d || date('m', $unixDate) != $m || date('Y', $unixDate) != $Y) {
					return FALSE;
				}
			} else if (strlen($y) == 4) {
				$Y = $y;
				$unixDate = mktime(0, 0, 0, $m, $d, $Y);
				if (date('d', $unixDate) != $d || date('m', $unixDate) != $m || date('Y', $unixDate) != $Y) {
					return FALSE;
				}
			} else {
				return FALSE;
			}
		} else {
			return FALSE;
		}
	} else if ($dmyOrder !== NULL) {
		return FALSE;
	} else {
		if (preg_match('/^(\d{1,2})[\/\.:-](\d{1,2})[\/\.:-](\d{1,2})$/', $date, $matches)) {
			list($dummy, $d, $m, $y) = $matches;
			if (strlen($y) == 2) {
				$Y = $y > date('y') ? ('19'.$y) : ('20'.$y);
				$unixDate = mktime(0, 0, 0, $m, $d, $Y);
			}
			if (strlen($y) < 2 || date('d', $unixDate) != $d || date('m', $unixDate) != $m || date('Y', $unixDate) != $Y) {
				$tmp = $y;
				$y = $d;
				if (strlen($y) < 2) return FALSE;

				$d = $tmp;
				$Y = $y > date('y') + 5 ? ('19'.$y) : ('20'.$y);
				$unixDate = mktime(0, 0, 0, $m, $d, $Y);
				if (date('d', $unixDate) != $d || date('m', $unixDate) != $m || date('Y', $unixDate) != $Y) {
					return FALSE;
				}
			}
		} else if (preg_match('/^(\d{4})[\/\.:-](\d{1,2})[\/\.:-](\d{1,2})$/', $date, $matches)) {
			list($dummy, $Y, $m, $d) = $matches;
			$unixDate = mktime(0, 0, 0, $m, $d, $Y);
			if (date('d', $unixDate) != $d || date('m', $unixDate) != $m || date('Y', $unixDate) != $Y) {
				return FALSE;
			}
		} else if (preg_match('/^(\d{1,2})[\/\.:-](\d{1,2})[\/\.:-](\d{4})$/', $date, $matches)) {
			list($dummy, $d, $m, $Y) = $matches;
			$unixDate = mktime(0, 0, 0, $m, $d, $Y);
			if (date('d', $unixDate) != $d || date('m', $unixDate) != $m || date('Y', $unixDate) != $Y) {
				return FALSE;
			}
		} else {
			return FALSE;
		}
	}

	$date = $Y.'-'.str_fill($m, 2, '0').'-'.str_fill($d, 2, '0');

	if ($targetFormat == 'datetime') {
		if ($time) {
			if (preg_match('/^(\d{1,2})[\/\.:-](\d{1,2})$/', $time, $matches)) {
				list($dummy, $h, $min) = $matches;
				$s = '00';
			} else if (preg_match('/^(\d{1,2})[\/\.:-](\d{1,2})[\/\.:-](\d{1,2})$/', $time, $matches)) {
				list($dummy, $h, $min, $s) = $matches;
			} else {
				return FALSE;
			}
		}
		return $date.' '.str_fill($h, 2, '0').':'.str_fill($min, 2, '0').':'.str_fill($s, 2, '0');
	} else {
		return $date;
	}

}



function ko_groups_assignment_history_check_entry($entry) {
	if (!$entry['person_id'] || !$entry['group_id']) return 1;
	if (!$entry['role_id']) $entry['role_id'] = 0;
	if ($entry['start'] == '0000-00-00 00:00:00') $entry['start'] = '';
	if ($entry['stop'] == '0000-00-00 00:00:00') $entry['stop'] = '';
	if (!$entry['start']) return 2;

	if($entry['start']) $entry['start'] = date('Y-m-d H:i:s', strtotime($entry['start']));
	if($entry['stop']) $entry['stop'] = date('Y-m-d H:i:s', strtotime($entry['stop']));

	if ($entry['start'] >= $entry['stop'] && $entry['stop']) return 3;

	$now = date('Y-m-d H:i:s');
	if ($entry['stop'] > $now || $entry['start'] > $now) return 5;

	$where = " AND `person_id` = {$entry['person_id']} AND `group_id` = {$entry['group_id']} AND `role_id` = {$entry['role_id']}";
	if ($entry['id']) $where .= " AND `id` <> {$entry['id']}";
	if ($entry['start']) $where .= " AND (`stop` > '{$entry['start']}' OR `stop` = '0000-00-00 00:00:00')";
	if ($entry['stop']) $where .= " AND `start` < '{$entry['stop']}'";
	if (db_get_count('ko_groups_assignment_history', 'id', $where) > 0) return 4;

	return 0;
}



function ko_groups_get_assignment_timeline($mode="person", $elementId, $group=NULL, $person=NULL, $drawOnCallback=NULL) {
	global $access;

	$roles = db_select_data('ko_grouproles', "WHERE 1=1");
	$fullGroupNames = array();

	$now = date('Y-m-d H:i:s');
	$rows = "[]";
	$firstColLabel = "Group";

	switch ($mode) {
		case 'person':
			if (!is_array($person)) ko_get_person_by_id($person, $person, TRUE);
			if (!$person['id']) return FALSE;

			if (!is_array($access['groups'])) ko_get_access('groups');

			$allGroups = db_select_data('ko_groups', '', 'id, pid, name, datafields');
			$history = db_select_data('ko_groups_assignment_history', "WHERE `person_id` = {$person['id']}");
			if(!$history) $history = array();
			$rowData = array_reduce(array_keys($history), function($acc, $key) use ($roles, $now, $fullGroupNames, $history, $access, $person, $allGroups) {
				$el = $history[$key];
				if ($el['role_id']) $roleName = $roles[zerofill($el['role_id'], 6)]['name'];
				else $roleName = '';

				if($el['start'] == "0000-00-00 00:00:00") {
					$el['start'] = date("Y-m-d H:i:s", time());
				}

				$group = db_select_data('ko_groups', "WHERE `id` = {$el['group_id']}", '*', '', '', TRUE);
				if (!$group['id']) return $acc;
				if ($group['stop'] && $group['stop'] != '0000-00-00' && $group['stop'] < date('Y-m-d') && $el['stop'] == '0000-00-00 00:00:00') return $acc;
				if ($access['groups']['ALL'] < 1 && $access['groups'][zerofill($group['id'], 6)] < 1) return $acc;

				$start = "new Date(".substr($el['start'], 0, 4).",".(substr($el['start'], 5, 2)-1).",".substr($el['start'],8,2).", 0, 0, 0)";
				$stopDate = $el['stop'] == '0000-00-00 00:00:00' ? $now : $el['stop'];
				$stop = "new Date(".substr($stopDate, 0, 4).",".(substr($stopDate, 5, 2)-1).",".substr($stopDate,8,2).", 23, 59, 59)";

				if (!isset($fullGroupNames[$group['id']])) {
					$fullGroupId = ko_groups_decode($group['id'], 'full_gid');
					$fullGroupNames[$group['id']] = ko_groups_decode($fullGroupId, 'group_desc_full');
				}
				$groupName = addslashes($group['name']);

				$startTime = strtotime($el['start']);
				if ($el['stop'] == '0000-00-00 00:00:00') {
					$stopTime = time();
					$dates = ko_date_format_timespan($el['start'], 'today');
				} else {
					$stopTime = strtotime($el['stop']);
					$dates = ko_date_format_timespan($el['start'], $el['stop']);
				}

				$tooltip = str_replace(':', ' <b style="text-decoration: underline;">:</b> ', $fullGroupNames[$group['id']]).'<hr style="margin: 3px 0px 3px 0px;">';
				$tooltip .= $dates;
				if ($roleName) $tooltip .= '<br><b>'.getLL('groups_assignment_history_label_role') . '</b>: ' . $roleName;
				$tooltip .= '<br><b>'.getLL('groups_assignment_history_label_duration').'</b>: '.ko_nice_timeperiod(date('c', $startTime), date('c', $stopTime)).'<br>';

				//Get whole motherline including group itself
				$curGrp = $group;
				$motherLineGroups = array($group);
				while($curGrp['pid']) {
					$motherLineGroups[] = $allGroups[$curGrp['pid']];
					$curGrp = $allGroups[$curGrp['pid']];
				}
				//Add group datafields from all motherline groups as well
				foreach($motherLineGroups as $mGrp) {
					foreach($datafield_ids = explode(",", $mGrp['datafields']) AS $id) {
						$datafield_data = ko_get_datafield_data($mGrp['id'], $id, $person['id'], '', $a, $b);
						if ($datafield_data['value'] != '') {
							$tooltip .= "<br><b>" . $datafield_data['description'] ."</b>: ". format_userinput($datafield_data['value'], 'js');
						}
					}
				}

				$tooltip = '<div style="padding: 3px; width: 200px;">'.str_replace("'", "\'", $tooltip).'</div>';
				$asyncFormData = kota_get_async_modal_code('ko_groups_assignment_history', 'edit', $el['id'], 'primary', '', 'groups-assignment-history-edit-btn', '200px', 'wrapContent', $el['id']);

				$acc[date('Ymd', $startTime).':'.date('Ymd', $stopTime).":".$group['id'].$el['person_id']] = array('context' => array('groupId' => $group['id'], 'roleId' => $el['role_id'], 'person_id' => $el['person_id'], 'asyncFormBtnId' => $asyncFormData['btnId']), 'chartRow' => "['{$groupName}', '', '{$tooltip}', {$start}, {$stop}]", 'asyncFormData' => $asyncFormData);
				return $acc;
			}, array());
			krsort($rowData);
			$rows = "[".implode(",", ko_array_column($rowData, 'chartRow'))."]";
			$context = ko_array_column($rowData, 'context');
			$asyncFormData = ko_array_column($rowData, 'asyncFormData');
			$firstColLabel = "Group";
			break;
		case 'group':
			if (!is_array($group)) $group = db_select_data('ko_groups', "WHERE `id` = {$group}", '*', '', '', TRUE);
			if (!$group['id']) return FALSE;

			if (!is_array($access['leute'])) ko_get_access('leute');

			$allgroups[] = $group['id'];
			$groups_recursive = ko_groups_get_recursive("", FALSE, $group['id']);
			foreach($groups_recursive AS $group_recursive) {
				$allgroups[] = $group_recursive['id'];
			}

			$unmerged_history = db_select_data('ko_groups_assignment_history', "WHERE `group_id` IN (". implode(",",$allgroups) .")", "*", "ORDER BY person_id ASC, start ASC");
			$fullGroupNames = db_select_data("ko_groups", "", "id, name");
			$history = $last_entry = [];

			// go through complete group history and merge subgroups for person
			foreach($unmerged_history AS $h) {
				if ($h['start'] == '0000-00-00 00:00:00') {
					$startTime = time();
				} else {
					$startTime = strtotime($h['start']);
				}

				if ($h['stop'] == '0000-00-00 00:00:00') {
					$stopTime = time();
					$dates = ko_date_format_timespan($h['start'], 'today');
				} else {
					$stopTime = strtotime($h['stop']);
					$dates = ko_date_format_timespan($h['start'], $h['stop']);
				}

				$group_info = $dates;
				$group_info .= '<br><b>'.getLL('groups_assignment_history_label_group') . '</b>: ' . $fullGroupNames[zerofill($h['group_id'], 6)]['name'];
				if ($h['role_id']) $group_info .= '<br><b>'.getLL('groups_assignment_history_label_role') . '</b>: ' . $roles[zerofill($h['role_id'], 6)]['name'];
				$group_info .= '<br><b>'.getLL('groups_assignment_history_label_duration') . '</b>: ' . ko_nice_timeperiod(date('c', $startTime), date('c', $stopTime));

				if ($h['person_id'] == $last_entry['person_id'] &&
					($last_entry['stop'] == '0000-00-00 00:00:00' ||
					substr($h['start'],0,10) <= substr($last_entry['stop'],0,10) ||
					($h['stop'] != '0000-00-00 00:00:00' && $h['stop'] < $last_entry['start']))
				) {
					// merge into previous entry
					if ( $last_entry['stop'] == '0000-00-00 00:00:00' ) {
						$stop = '0000-00-00 00:00:00';
					} else {
						$stop = $h['stop'];
					}
					$history[$h['person_id']][$last_entry['id']]['stop'] = $stop;
					$history[$h['person_id']][$last_entry['id']]['label'] .= " ; " . $fullGroupNames[zerofill($h['group_id'], 6)]['name'];
					$history[$h['person_id']][$last_entry['id']]['group_info'][] = $group_info;
					if ($stop != '0000-00-00 00:00:00') {
						$last_entry = $h;
					} else {
						$last_entry['stop'] = '0000-00-00 00:00:00';
					}
				} else {
					$history[$h['person_id']][$h['id']] = $h;
					$history[$h['person_id']][$h['id']]['label'] = $fullGroupNames[zerofill($h['group_id'], 6)]['name'];
					$history[$h['person_id']][$h['id']]['group_info'][] = $group_info;
					$last_entry = $h;
				}

			}

			// build rowData for google charts
			foreach($history AS $history_person) {
				foreach($history_person AS $el) {
					$personLines = [];
					ko_get_person_by_id($el['person_id'], $person);
					if (!$person['id']) continue;
					if ($access['leute']['ALL'] < 1 && $access['leute'][$person['id']] < 1) continue;

					$personName = array();
					if ($person['vorname']) $personName[] = $person['vorname'];
					if ($person['nachname']) $personName[] = $person['nachname'];
					if ($person['firm']) $personName[] = (sizeof($personName) > 0 ? '(' : '').$person['firm'].(sizeof($personName) > 0 ? ')' : '');
					$personDesc = implode(" ", $personName);

					$startTime = strtotime($el['start']);
					$stopTime = ($el['stop'] == '0000-00-00 00:00:00' ? time() : $stopTime = strtotime($el['stop']));
					$start = "new Date(" . substr($el['start'], 0, 4) . "," . (substr($el['start'], 5, 2) - 1) . "," . substr($el['start'], 8, 2) . ", 0, 0, 0)";
					$stopDate = $el['stop'] == '0000-00-00 00:00:00' ? $now : $el['stop'];
					$stop = "new Date(" . substr($stopDate, 0, 4) . "," . (substr($stopDate, 5, 2) - 1) . "," . substr($stopDate, 8, 2) . ", 23, 59, 59)";

					$personLines[] = preg_replace('/\s+/', ' ', $personDesc);
					$personLines[] = preg_replace('/\s+/', ' ', $person['plz']) . ' ' . preg_replace('/\s+/', ' ', $person['ort']);
					if ($person['geburtsdatum']) $personLines[] = sql2datum($person['geburtsdatum']);

					$tooltip = implode('<br>', $personLines) . '<hr style="margin: 3px 0px 3px 0px;">';

					foreach($el['group_info'] AS $group_info) {
						$tooltip .= "<div class=\"group_info\">" . $group_info . "</div>";
					}

					$tooltip = '<div class="google_charts__tooltip">'.str_replace("'", "\'", $tooltip).'</div>';

					$asyncFormData = '';
					if (count($el['group_info']) == 1) {
						$asyncFormData = kota_get_async_modal_code('ko_groups_assignment_history', 'edit', $el['id'], 'primary', '', 'groups-assignment-history-edit-btn', '200px', 'wrapContent', $el['id']);
					}

					$rowData[date('Ymd', $stopTime).':'.date('Ymd', $startTime).':'.$person['id']] = array('context' => array('groupId' => $el['group_id'], 'roleId' => $el['role_id'], 'personId' => $person['id'], 'asyncFormBtnId' => $asyncFormData['btnId']), 'chartRow' => "[\"".str_replace('"', '\"', $personDesc)."\", \"" . $el['label'] ."\", '{$tooltip}', {$start}, {$stop}]", 'asyncFormData' => $asyncFormData);
				}
			}

			if(empty($rowData)) $rowData = [];
			krsort($rowData);
			$rows = "[".implode(",", ko_array_column($rowData, 'chartRow'))."]";
			$context = ko_array_column($rowData, 'context');
			$asyncFormData = ko_array_column($rowData, 'asyncFormData');
			$firstColLabel = "Person";
			break;
	}

	if ($drawOnCallback !== NULL) {
		$drawOnCallback = sprintf($drawOnCallback, 'drawChart');
	} else {
		$drawOnCallback = 'google.charts.setOnLoadCallback(drawChart);';
	}
	array_walk_recursive($context, 'utf8_encode_array');
	$contextJson = json_encode($context);

	if ($rows == '[]') {
		return getLL('groups_assignment_history_no_entries');
	} else {
		$asyncFormHtml = implode('', ko_array_column($asyncFormData, 'btnHtml'));
		return "<div class=\"groups-assignment-history-control\"><script type=\"text/javascript\">
  google.charts.load('current', {packages:['timeline'], language: '".substr($_SESSION['lang'], 0, 2)."'});
  {$drawOnCallback}
  function drawChart() {
    var container = document.getElementById('{$elementId}');
    var chart = new google.visualization.Timeline(container);
    var dataTable = new google.visualization.DataTable();
    google.visualization.events.addListener(chart, 'select', function(e) {groupsAssignmentHistoryClick('{$mode}', chart, {$contextJson}, e);});

    dataTable.addColumn({ type: 'string', id: '{$firstColLabel}' });
    dataTable.addColumn({ type: 'string', id: 'Role' });
    dataTable.addColumn({ type: 'string', role: 'tooltip', 'p': {'html': true} });
    dataTable.addColumn({ type: 'date', id: 'Start' });
    dataTable.addColumn({ type: 'date', id: 'End' });

    dataTable.addRows({$rows});

    var options = {
		height: dataTable.getNumberOfRows() * 41 + 50,
		hAxis: {
			format: 'd.M.yy',
			gridlines: {count: 5}
		},
		tooltip: {
			isHtml: true
		},
		colors:['#3281be','#73c5f4']
    };

    chart.draw(dataTable, options);
  }
</script>{$asyncFormHtml}</div>";
	}
}



function ko_date_format_timespan($start, $stop) {
	if ($stop == '' || $stop == 'today') {
		$startDate = strtotime($start);
		$result = strftime('%e. %b. %Y', $startDate) . " - " . getLL('time_today');
	} else if ($start == '' || $start == 'today') {
		$stopDate = strtotime($stop);
		$result = getLL('time_today') . " - " . strftime('%e. %b. %Y', $stopDate);
	} else {
		$startDate = strtotime($start);
		$stopDate = strtotime($stop);

		if (date('Y', $startDate) == date('Y', $stopDate)) {
			if (date('m', $startDate) == date('m', $stopDate)) {
				$result = ($startDate > 0 ? strftime('%e.', $startDate) : '').($stopDate > 0 ? ' - '.strftime('%e.', $stopDate).' '.strftime('%b. %Y', $stopDate) : '');
			} else {
				$result = ($startDate > 0 ? strftime('%e. %b.', $startDate) : '').($stopDate > 0 ? ' - '.strftime('%e %b.', $stopDate).' '.strftime('%Y', $stopDate) : '');
			}
		} else {
			$result = ($startdate > 0 ? strftime('%e. %b. %Y', $startDate) : '').($stopDate > 0 ? ' - '.strftime('%e %b. %Y', $stopDate) : '');
		}
	}

	return $result;
}



function ko_update_famfunctions($famid, $splitFamily=FALSE, $saveLeuteChanges=FALSE, $testData=NULL, $returnChanges=FALSE) {
	$return = array();
	$changes = [];
	if($testData === NULL && $famid <= 0) return $return;

	if ($testData === NULL) {
		$result = db_select_data('ko_leute', "WHERE `famid` = '{$famid}' AND `deleted` = '0'".ko_get_leute_hidden_sql(), 'id,geschlecht,geburtsdatum,vorname,nachname,father,mother,spouse,famfunction,zivilstand, (TIMESTAMPDIFF(YEAR, `geburtsdatum`, CURDATE())) AS `alter`', 'ORDER BY `alter` DESC');
	} else {
		$result = $testData;
	}

	//Only work with families with more than one member
	if(!$result || sizeof($result) < 2) {
		if ($result && sizeof($result) == 1) {
			$p = end($result);
			$newFamfunction = '';
			if ($p['geschlecht'] == 'm') $newFamfunction = 'husband';
			else if ($p['geschlecht'] == 'w') $newFamfunction = 'wife';

			if ($p['famfunction'] != $newFamfunction) {
				if ($saveLeuteChanges) ko_save_leute_changes($p['id']);
				if($returnChanges) {
					$changes[] = ['UPDATE','ko_leute',['famfunction' => $newFamfunction],['id' => $p['id']]];
				} else {
					db_update_data('ko_leute', "WHERE `id` = '{$p['id']}'", array('famfunction' => $newFamfunction));
					ko_log_diff('update_famfunctions', array('id' => $p['id'], 'famfunction' => $newFamfunction), array('id' => $p['id'], 'famfunction' => $p['famfunction']));
				}
			}
		}
		return $returnChanges ? $changes : $return;
	}

	// handle persons without birthdate
	foreach ($result as $pid => $p) {
		if ($p['geburtsdatum'] == '0000-00-00') {
			/*if ($p['famfunction'] != '') {
				if ($saveLeuteChanges) ko_save_leute_changes($pid);
				db_update_data('ko_leute', "WHERE `id` = '{$pid}'", array('famfunction' => ''));
				ko_log_diff('update_famfunctions', array('id' => $pid, 'famfunction' => ''), array('id' => $pid, 'famfunction' => $p['famfunction']));
			}*/
			unset($result[$pid]);
		}
	}

	$resultAsc = array_reverse($result, TRUE);

	$subGraphs = array();
	foreach ($resultAsc as $row) {
		foreach (array('spouse', 'father', 'mother') as $rel) {
			if (!empty($row[$rel]) && isset($result[$row[$rel]])) {
				$delta = $rel == 'spouse' ? 0 : 1;

				$rsgid = getFamilySubGraphIndex($subGraphs, $row[$rel]);
				if($rsgid !== null) $rgen = $subGraphs[$rsgid][$row[$rel]];
				$mysgid = getFamilySubGraphIndex($subGraphs, $row['id']);
				if($mysgid !== null) $mygen = $subGraphs[$mysgid][$row['id']];

				if ($mysgid !== NULL && $rsgid === NULL) {
					$subGraphs[$mysgid][$row[$rel]] = $mygen + $delta;
				} else if ($mysgid === NULL && $rsgid !== NULL) {
					$subGraphs[$rsgid][$row['id']] = $rgen - $delta;
				} else if ($mysgid === NULL && $rsgid === NULL) {
					$subGraphs[] = array(
						$row['id'] => 1,
						$row[$rel] => 1 + $delta,
					);
				} else {
					$d = $rgen - $mygen - $delta;
					mergeFamilySubGraphs($subGraphs, $mysgid, $rsgid, $d);
				}
			}
		}
	}

	$doneIds = array();
	foreach ($subGraphs as $sg) {
		foreach ($sg as $id => $gen) $doneIds[] = $id;
	}
	foreach ($result as $id => $p) {
		if (!in_array($id, $doneIds)) $subGraphs[] = array($id => 1);
	}

	$subGraphs = array_values($subGraphs);
	while (sizeof($subGraphs) > 1) {
		foreach (array(0, 1, 2) as $genDiff) {
			$found = FALSE;
			$delta = $sg1 = $sg2 = 0;
			for ($key1 = 0; $key1 <= sizeof($subGraphs); $key1++) {
				for ($key2 = $key1 + 1; $key2 <= sizeof($subGraphs); $key2++) {
					foreach ($subGraphs[$key1] as $id1 => $gen1) {
						$id1Backup = $id1;
						foreach (isset($subGraphs[$key2]) ? $subGraphs[$key2] : [] as $id2 => $gen2) {
							$id1 = $id1Backup;
							$switchKeys = FALSE;
							if ($result[$id2]['alter'] < $result[$id1]['alter']) {
								$id1t = $id1;
								$id1 = $id2;
								$id2 = $id1t;

								$gen1t = $gen1;
								$gen1 = $gen2;
								$gen2 = $gen1t;

								$switchKeys = TRUE;
							}
							$diff = abs($result[$id2]['alter'] - $result[$id1]['alter']);

							if ($genDiff == 0 && $diff < 17) {
								$found = TRUE;
								$delta = $gen2 - $gen1 - $genDiff;
							} else if ($genDiff == 1 && $diff >= 17 && $diff <= 50) {
								$found = TRUE;
								$delta = $gen2 - $gen1 - $genDiff;
							} else if ($genDiff == 2) {
								$found = TRUE;
								$delta = $gen2 - $gen1 - $genDiff;
							}
							if ($found) break;
						}
						if ($found) break;
					}
					if ($found) break;
				}
				if ($found) break;
			}
			if ($found) {
				if ($switchKeys) {
					$key1t = $key1;
					$key1 = $key2;
					$key2 = $key1t;
				}
				mergeFamilySubGraphs($subGraphs, $key1, $key2, $delta);
				$subGraphs = array_values($subGraphs);

				break;
			}
		}
	}

	$familyGraph = end($subGraphs);

	asort($familyGraph);

	// handle case where an adult person lives with parents
	if(end($familyGraph) == 2) {
		$youngestAge = 10000;
		foreach ($familyGraph as $k => $v) {
			$youngestAge = min($youngestAge, $result[$k]['alter']);
		}
		$adapt = $youngestAge > 32;
		if (!$adapt) {
			foreach ($familyGraph as $k => $v) {
				if ($v == 1 && ($result[$k]['spouse'] || $result[$k]['zivilstand'] == 'married')) {
					$adapt = TRUE;
					break;
				}
			}
		}
		if ($adapt) {
			foreach ($familyGraph as $k => $v) {
				$familyGraph[$k] += 1;
			}
		}
	}

	$fgb = $familyGraph;

	$familyData = db_select_data('ko_familie', "WHERE `famid` = '{$famid}'", '*', '', '', TRUE);
	unset($familyData['famid'], $familyData['import_id']);
	if (end($familyGraph) > 2) {
		if ($splitFamily) {
			for ($i = 3; $i <= end($familyGraph); $i++) {
				if (sizeof(array_filter($familyGraph, function($e) use ($i) {return $e==$i;})) > 0) {
					$newFamId = db_insert_data('ko_familie', $familyData);
					$nh = $nw = 0;
					foreach ($familyGraph as $pid => $gen) {
						if ($gen == $i) {
							$sex = $result[$pid]['geschlecht'];
							$newFamfunction = '';
							if ($sex == 'm') {
								$newFamfunction = 'husband';
								$nh++;
							} else if ($sex == 'w') {
								$newFamfunction = 'wife';
								$nw++;
							}

							if ($saveLeuteChanges) ko_save_leute_changes($pid);
							if($returnChanges) {
								$changes[] = ['UPDATE','ko_leute',['famid' => $newFamId, 'famfunction' => $newFamfunction],['id' => $pid]];
							} else {
								db_update_data('ko_leute', "WHERE `id` = '{$pid}'", array('famid' => $newFamId, 'famfunction' => $newFamfunction));
								ko_log_diff('update_famfunctions', array('famid' => $newFamId, 'id' => $pid, 'famfunction' => $newFamfunction), array('famid' => $famid, 'id' => $pid, 'famfunction' => $result[$pid]['famfunction']));
							}
							unset($familyGraph[$pid]);
						}
					}
					if ($nh > 1 || $nw > 1) ko_log('famfunction_error', "got family with more than 1 husband or wife: {$newFamId}");
				}
			}
		} else {
			for ($i = 3; $i <= end($familyGraph); $i++) {
				foreach ($familyGraph as $pid => $gen) {
					if ($gen == $i) {
						if ($result[$pid]['famfunction'] != '') {
							if ($saveLeuteChanges) ko_save_leute_changes($pid);
							if($returnChanges) {
								$changes[] = ['UPDATE','ko_leute',['famfunction' => ''],['id' => $pid]];
							} else {
								db_update_data('ko_leute', "WHERE `id` = '{$pid}'", array('famfunction' => ''));
								ko_log_diff('update_famfunctions', array('id' => $pid, 'famfunction' => ''), array('id' => $pid, 'famfunction' => $result[$pid]['famfunction']));
							}
						}
						unset($familyGraph[$pid]);
					}
				}
			}
		}
	}

	if (end($familyGraph) == 2) {
		foreach ($familyGraph as $pid => $gen) {
			if ($gen == 1) {
				if ($result[$pid]['famfunction'] != 'child') {
					if ($saveLeuteChanges) ko_save_leute_changes($pid);
					if($returnChanges) {
						$changes[] = ['UPDATE','ko_leute',['famfunction' => 'child'],['id' => $pid]];
					} else {
						db_update_data('ko_leute', "WHERE `id` = '{$pid}'", array('famfunction' => 'child'));
						ko_log_diff('update_famfunctions', array('id' => $pid, 'famfunction' => 'child'), array('id' => $pid, 'famfunction' => $result[$pid]['famfunction']));
					}
				}
				unset($familyGraph[$pid]);
			}
		}
	}

	$nh = $nw = 0;
	foreach ($familyGraph as $pid => $gen) {
		$sex = $result[$pid]['geschlecht'];
		$newFamfunction = '';
		if ($sex == 'm') {
			$newFamfunction = 'husband';
			$nh++;
		} else if ($sex == 'w') {
			$newFamfunction = 'wife';
			$nw++;
		}

		if ($result[$pid]['famfunction'] != $newFamfunction) {
			if ($saveLeuteChanges) ko_save_leute_changes($pid);
			if($returnChanges) {
				$changes[] = ['UPDATE','ko_leute',['famfunction' => $newFamfunction],['id' => $pid]];
			} else {
				db_update_data('ko_leute', "WHERE `id` = '{$pid}'", array('famfunction' => $newFamfunction));
				ko_log_diff('update_famfunctions', array('id' => $pid, 'famfunction' => $newFamfunction), array('id' => $pid, 'famfunction' => $result[$pid]['famfunction']));
			}
		}
	}
	if ($nh > 1 || $nw > 1) ko_log('famfunction_error', "got family with more than 1 husband or wife: {$famid}");

	if($returnChanges) return $changes;
	if ($testData) return $fgb;
}

function getFamilySubGraphIndex(&$subGraphs, $pid) {
	foreach ($subGraphs as $k => &$sg) {
		if (array_key_exists($pid, $sg)) return $k;
	}
	return NULL;
}

function mergeFamilySubGraphs(&$subGraphs, $id1, $id2, $delta=0) {
	if ($id1 == $id2) return;

	$minGen1 = $minGen2 = 300;
	foreach ($subGraphs[$id1] as $id => $gen) {
		$minGen1 = $minGen1 == NULL ? $gen : min($minGen1, $gen);
	}
	foreach ($subGraphs[$id2] as $id => $gen) {
		$minGen2 = $minGen2 == NULL ? $gen : min($minGen2, $gen);
	}

	$minGen2 -= $delta;
	$offset = 1 - min($minGen1, $minGen2);

	foreach ($subGraphs[$id1] as $id => $gen) {
		$subGraphs[$id1][$id] += $offset;
	}
	foreach ($subGraphs[$id2] as $id => $gen) {
		$subGraphs[$id1][$id] = $subGraphs[$id2][$id] + $offset - $delta;
	}
	unset($subGraphs[$id2]);
}



/**
 * generates a readable call trace (from http://php.net/manual/en/function.debug-backtrace.php#112238)
 *
 * @return string
 */
function ko_get_call_trace() {
	$e = new Exception();
	$trace = explode("\n", $e->getTraceAsString());
	// reverse array to make steps line up chronologically
	$trace = array_reverse($trace);
	array_shift($trace); // remove {main}
	array_pop($trace); // remove call to this method
	$length = count($trace);
	$result = array();

	for ($i = 0; $i < $length; $i++)
	{
		$result[] = ($i + 1)  . ')' . substr($trace[$i], strpos($trace[$i], ' ')); // replace '#someNum' with '$i)', set the right ordering
	}

	return "\t" . implode("\n\t", $result);
}



function ko_get_available_google_cloud_printers() {
	if (!is_array($GLOBALS['google_cloud_printers'])) {
		$GLOBALS['google_cloud_printers'] = db_select_data('ko_google_cloud_printers', "WHERE 1=1", '*', 'ORDER BY `name` ASC');
	}
	return $GLOBALS['google_cloud_printers'];
}



function ko_update_google_cloud_printers() {

	$old = array_column(db_select_data('ko_google_cloud_printers'),null,'google_id');

	$gcp = koGoogleCloudPrint::Instance();
	$new = $gcp->getPrinters();

	foreach ($new as $printer) {
		$row = [
			'google_id' => $printer['id'],
			'name' => $printer['displayName'],
			'path' => $printer['name'],
			'owner_name' => $printer['ownerName']
		];
		if(isset($old[$printer['id']])) {
			db_update_data('ko_google_cloud_printers','WHERE id='.$old[$printer['id']]['id'],$row);
			unset($old[$printer['id']]);
		} else {
			db_insert_data('ko_google_cloud_printers',$row);
		}
	}

	if(!empty($old)) {
		db_query('DELETE FROM ko_google_cloud_printers WHERE id IN('.implode(',',array_column($old,'id')).')');
	}
}



function ko_leute_get_detail_exports() {
	global $PLUGINS, $BASE_PATH;
	$exports = array(
		array(
			'name' => 'personal_form',
			'desc' => getLL('leute_detail_export_personal_form'),
			'type' => 'pdf'),
		array(
			'name' => 'personal_form_extended',
			'desc' => getLL('leute_detail_export_personal_form_extended'),
			'type' => 'pdf'),
	);
	foreach($PLUGINS as $plugin) {
		if(function_exists('my_leute_export_details_add_'.$plugin['name'])) {
			call_user_func_array('my_leute_export_details_add_' . $plugin['name'], array(&$exports));
		}
	}
	$userDefined = db_select_data('ko_detailed_person_exports', "WHERE 1=1", '*', "ORDER BY `name` ASC");
	foreach ($userDefined as $ud) {
		$file = "{$BASE_PATH}{$ud['template']}";
		if ($ud['template'] && file_exists($file) && is_readable($file)) {
			$export = array(
				'fcn_suffix' => 'user_template',
				'user_template' => $file,
				'name' => $ud['name'],
				'desc' => $ud['name'],
				'type' => 'word',
			);
			$exports[] = $export;
		}
	}
	return $exports;
}



function ko_word_person_array($person) {
	global $DATETIME, $LEUTE_WORD_ADDRESSBLOCK, $KOTA;

	if (!is_array($KOTA['ko_leute']['vorname'])) ko_include_kota(array('ko_leute'));

	$map = array();

	//Address fields of recipient (${address_...})
	foreach($person as $k => $v) {
		$map['${address_'.strtolower($k).'}'] = strip_tags(map_leute_daten($v, $k, $person, $datafields, FALSE, array('kota_process_modes' => 'pdf,list')));
	}

	$allCols = ko_get_leute_col_name();
	foreach($allCols as $col => $llLabel) {
		if(substr($col, 0, 12) != 'MODULEplugin') continue;

		$map['${address_'.strtolower($col).'}'] = strip_tags(map_leute_daten('', $col, $person, $datafields, FALSE, array('kota_process_modes' => 'pdf,list')));
	}

	// Addressblock. expressed in lines because PHPWord can't insert line breaks into templates,
	// LINES START WITH 0!!
	$maxLines = sizeof($LEUTE_WORD_ADDRESSBLOCK);
	$lineCounter = 0;
	foreach ($LEUTE_WORD_ADDRESSBLOCK as $line) {
		$lineString = '';
		$cellCounter = 0;
		foreach ($line as $infoArray) {
			if (trim($person[$infoArray['field']]) != '') {
				$cellContent = trim($person[$infoArray['field']]);
			}
			else if (isset($infoArray['ifEmpty']) && trim($person[$infoArray['ifEmpty']]) != '') {
				$cellContent = trim($person[$infoArray['ifEmpty']]);
			}
			else {
				continue;
			}

			if ($cellCounter == 0) {
				$lineString .= $cellContent;
			}
			else {
				$lineString .= ' ' . $cellContent;
			}

			$cellCounter ++;
		}
		if (trim($lineString != '')) $map['${line' . $lineCounter++ . '}'] = $lineString;
	}
	for ($i = $lineCounter; $i < $maxLines; $i ++) {
		$map['${line' . $i . '}'] = '';
	}

	// Salutations
	$geschlechtMap = array('Herr' => 'm', 'Frau' => 'w');
	$vorname = trim($person['vorname']);
	$nachname = trim($person['nachname']);
	$geschlecht = $person['geschlecht'] != '' ? $person['geschlecht'] : $geschlechtMap[$person['anrede']];
	$map['${address__salutation_formal_name}'] = getLL('mailing_salutation_formal_' . ($nachname != '' ? $geschlecht : '')) . ($nachname == '' ? '' : ' ' . $nachname);
	$map['${address__salutation_name}'] = getLL('mailing_salutation_' . ($vorname != '' ? $geschlecht : '')) . ($vorname == '' ? '' : ' ' . $vorname);

	//Salutation
	$map['${address__salutation}'] = getLL('mailing_salutation_'.$person['geschlecht']);
	$map['${address__salutation_formal}'] = getLL('mailing_salutation_formal_'.$person['geschlecht']);


	//Add current date
	$map['${date}'] = strftime($DATETIME['dMY'], time());
	$map['${date_dmY}'] = strftime($DATETIME['dmY'], time());

	//Add contact fields (from general settings)
	$contact_fields = array('name', 'address', 'zip', 'city', 'phone', 'url', 'email');
	foreach($contact_fields as $field) {
		$map['${contact_'.strtolower($field).'}'] = ko_get_setting('info_'.$field);
	}

	//Add sender fields of current user
	$sender = ko_get_logged_in_person();
	foreach($sender as $k => $v) {
		$map['${user_'.strtolower($k).'}'] = strip_tags(map_leute_daten($v, $k, $sender, $datafields, FALSE, array('kota_process_modes' => 'pdf,list')));
	}

	return $map;
}//ko_word_person_array()



function ko_prepare_itemlist(&$itemlistContent, $groups, $noGroupElements, $sessionShowKey, $sessionStatesKey, $presetType, $globalMinRights, $maxRights) {

	$itemlistContent = array();
	//read account groups
	$counter = 0;
	foreach($groups as $group) {
		$gid = $group['id'];
		$elements = $group['_elements'];

		//Find selected groups
		$selected = array();
		foreach($elements as $eid => $element) {
			if(in_array($eid, $_SESSION[$sessionShowKey])) $selected[$eid] = TRUE;
		}

		$itemlist[$counter]["type"] = "group";
		$itemlist[$counter]["name"] = $group['_title'].'<sup> (<span name="group_'.$group['id'].'">'.sizeof($selected).'</span>)</sup>';
		$itemlist[$counter]["aktiv"] = (sizeof($elements) == sizeof($selected) ? 1 : 0);
		$itemlist[$counter]["value"] = $gid;
		$itemlist[$counter]["open"] = isset($_SESSION[$sessionStatesKey][$gid]) ? $_SESSION[$sessionStatesKey][$gid] : 0;
		$counter++;

		foreach($elements as $eid => $element) {
			$itemlist[$counter]["name"] = $element['_title'];
			$itemlist[$counter]["prename"] = '<span style="margin-right:2px;background-color:#fff;">&emsp;</span>';
			$itemlist[$counter]["aktiv"] = in_array($eid, $_SESSION[$sessionShowKey]) ? 1 : 0;
			$itemlist[$counter]["parent"] = TRUE; // is subitem to a dossier group
			$itemlist[$counter]['action'] = 'itemlistRedraw';
			$itemlist[$counter++]["value"] = $eid;
		}//foreach($dossiers)
		$itemlist[$counter-1]["last"] = TRUE;
	}//foreach($dossier_groups)


	//Add event elements without group
	foreach($noGroupElements as $eid => $element) {
		$itemlist[$counter]["name"] = $element['_title'];
		$itemlist[$counter]["prename"] = '<span style="margin-right:2px;background-color:#fff;">&emsp;</span>';
		$itemlist[$counter]["aktiv"] = in_array($eid, $_SESSION[$sessionShowKey]) ? 1 : 0;
		$itemlist[$counter++]["value"] = $eid;
	}//foreach(groups)

	$itemlistContent['tpl_itemlist_select'] = $itemlist;

	//Get all presets
	$akt_value = $_SESSION[$sessionShowKey];
	$akt_value = implode(',', $_SESSION[$sessionShowKey]);
	$itemset = array_merge((array)ko_get_userpref('-1', '', $presetType, 'ORDER BY `key` ASC'), (array)ko_get_userpref($_SESSION['ses_userid'], '', $presetType, 'ORDER BY `key` ASC'));
	foreach($itemset as $i) {
		$value = $i['user_id'] == '-1' ? '@G@'.$i['key'] : $i['key'];
		$itemselect_values[] = $value;
		$itemselect_output[] = $i['user_id'] == '-1' ? getLL('itemlist_global_short').' '.$i['key'] : $i['key'];
		if($i['value'] == $akt_value) $itemselect_selected = $value;
	}
	$itemlistContent['tpl_itemlist_values'] = $itemselect_values;
	$itemlistContent['tpl_itemlist_output'] = $itemselect_output;
	$itemlistContent['tpl_itemlist_selected'] = $itemselect_selected;
	if($maxRights >= $globalMinRights) $itemlistContent['allow_global'] = TRUE;

}



function ko_tracking_get_entry_input($trackingId, $name='var4') {
	global $smarty;

	$localSmarty = clone($smarty);

	$html = '';

	if (is_array($trackingId)) $tracking = $trackingId;
	else $tracking = db_select_data('ko_tracking', "WHERE `id` = {$trackingId}", '*', '', '', TRUE);

	switch ($tracking['mode']) {
		case 'simple':
			$html = '<i class="fa fa-check-square-o"></i><input type="hidden" value="1" name="'.$name.'">';
		break;
		case 'value':
		case 'valueNonNum':
			$input = array(
				'type' => 'text',
				'name' => $name,
			);
			$localSmarty->assign('input', $input);
			$html = $localSmarty->fetch('ko_formular_elements.tmpl');
		break;
		case 'type':
			$types = ko_array_filter_empty(explode("\n", $tracking['types']));
			$inputs = array();
			foreach ($types as $type) {
				$inputs[] = '<div class="input-group input-group-sm"><span class="input-group-addon">'.$type.'</span><input type="text" class="input-sm form-control tracking-entry-type-input" name="tracking-entry-type-input-'.$type.'" data-tracking-entry-type="'.$type.'"></div>';
			}
			$html = implode('', $inputs);
			$html .= '<input type="hidden" value="" name="'.$name.'"><script>$(".tracking-entry-type-input").on("input", function() {var values = {};$(".tracking-entry-type-input").each(function(){values[$(this).data("tracking-entry-type")] = $(this).val();});$(\'[name="'.$name.'"]\').val(JSON.stringify(values));});</script>';
		break;
		case 'typecheck':
			$types = ko_array_filter_empty(explode("\n", $tracking['types']));
			$input = array(
				'values' => $types,
				'descs' => $types,
				'size' => min(7, sizeof($types)),
				'type' => 'checkboxes',
				'name' => $name,
			);
			$localSmarty->assign('input', $input);
			$html = $localSmarty->fetch('ko_formular_elements.tmpl');
		break;
	}
	return $html;
}



function ko_get_all_subscription_form_groups() {
	static $all_groups = null;
	if($all_groups === null) {
		$all_groups = db_select_data('ko_subscription_form_groups');
	};
	return $all_groups;
}

function json_encode_latin1($data,$returnLatin1 = true) {
	if(is_array($data)) {
		// don't user utf8_encode_array. it turns scalars into strings
		array_walk_recursive($data,function(&$v) {
			if(is_string($v)) {
				$v = utf8_encode($v);
			}
		});
	} else if(!is_object($data)) {
		$data = utf8_encode($data);
	}
	$json = json_encode($data,JSON_UNESCAPED_UNICODE);
	if($returnLatin1) {
		$json = utf8_decode($json);
	}
	return $json;
}

function json_decode_latin1($json,$fromLatin1 = true) {
	if($fromLatin1) {
		$json = utf8_encode($json);
	}
	$data = json_decode($json,true);
	if(is_array($data)) {
		// don't user utf8_decode_array. it turns scalars into strings
		array_walk_recursive($data,function(&$v) {
			if(is_string($v)) {
				$v = utf8_decode($v);
			}
		});
	} else {
		$data = utf8_decode($data);
	}
	return $data;
}



function kota_get_multititle($table, $row) {
	global $KOTA;

	$title = "";
	if(!isset($KOTA[$table]['_multititle'])) return $title;

	//Set title
	foreach ($KOTA[$table]["_multititle"] as $tttc => $tc_fcn) {
		$val = $row[$tttc];
		if ($tc_fcn != "") {
			if (substr($tc_fcn, 0, 18) == 'kota_process_data:') {
				$modes = substr($tc_fcn, 18);
				$mtd = array($tttc => $val);
				kota_process_data($table, $mtd, $modes, $_log, $row["id"], $new_entry, $row);
				$val = $mtd[$tttc];
			} else {
				eval("\$val=" . str_replace("@VALUE@", $val, $tc_fcn) . ";");
			}
		}
		$title .= "$val ";
	}

	return trim($title);
}//kota_get_multititle()

function ko_get_private_key($format = \phpseclib\Crypt\RSA::PRIVATE_FORMAT_PKCS1) {
	global $BASE_PATH;
	$path = $BASE_PATH.'config/id_rsa';
	$key = file_get_contents($path);
	if($key && $format == \phpseclib\Crypt\RSA::PRIVATE_FORMAT_PKCS1) {
		return $key;
	}
	$rsa = new \phpseclib\Crypt\RSA();
	if($key) {
		$rsa->loadKey($key);
		return $rsa->getPrivateKey($format);
	} else {
		$keys = $rsa->createKey(4096);
		touch($path);
		chmod($path,0600);
		file_put_contents($path,$keys['privatekey']);
		return $keys['privatekey'];
	}
}

function ko_get_public_key($format = \phpseclib\Crypt\RSA::PUBLIC_FORMAT_OPENSSH) {
	global $BASE_PATH, $HTML_TITLE;

	$path = $BASE_PATH.'config/id_rsa.pub';
	$key = file_get_contents($path);
	if($key && $format == \phpseclib\Crypt\RSA::PUBLIC_FORMAT_OPENSSH) {
		return $key;
	}
	$rsa = new \phpseclib\Crypt\RSA();
	if($key) {
		$rsa->loadKey($key);
		$rsa->setPublicKey();
	} else {
		$rsa->loadKey(ko_get_private_key());
		$rsa->setComment($HTML_TITLE);
		file_put_contents($path,$rsa->getPublicKey(\phpseclib\Crypt\RSA::PUBLIC_FORMAT_OPENSSH));
	}
	return $rsa->getPublicKey($format);
}

function ko_get_self_signed_cert() {
	global $HTML_TITLE, $BASE_PATH, $BASE_URL;

	$path = $BASE_PATH.'config/self-signed.crt';
	if(file_exists($path)) {
		$cert = file_get_contents($path);
	} else {
		$pub = new \phpseclib\Crypt\RSA();
		$pub->setPublicKey(ko_get_private_key());

		$priv = new \phpseclib\Crypt\RSA();
		$priv->setPrivateKey(ko_get_public_key());

		$cert = new \phpseclib\File\X509();
		$cert->setPublicKey($pub);
		$cert->setPrivateKey($priv);
		$cert->setDN('CN='.parse_url($BASE_URL, PHP_URL_HOST).',O=kOOL,OU='.$HTML_TITLE);

		$x509 = new \phpseclib\File\X509();
		$x509->setStartDate(date('Y-m-d 01:01:01'));
		$x509->setEndDate('99991231235959Z'); // never expires
		$result = $x509->sign($cert,$cert);
		$cert = $x509->saveX509($result);
		file_put_contents($path,$cert);
	}
	return $cert;
}

/**
 * Try to find address in an Array of strings
 *
 * @param array $commentLines where we assume to have an address
 * @param string accountName Possibly the name of Firm / Person
 * @return array
 */
function ko_fuzzy_address_search($commentLines, $accountName = '') {

	$lineCounter = 1;
	$zip = $city = $address = $firm = $postfach = $department = '';
	$found_street_already_by_regex = FALSE;
	foreach ($commentLines as $line) {
		if (preg_match('/^([A-Z]{2}[- ])?(\d{4}\d*)\s+(.+)$/',$line,$matches)) {
			$zip = $matches[2];
			$city = $matches[3];
		} else if ($zip) {
			// pass
		} else if (substr(strtolower($line), 0, 8) == 'postfach') {
			$postfach = $line;
		} else if (preg_match('/(route|str|rue|weg|gasse|weid)/i', strtolower($line))) {
			$found_street_already_by_regex = TRUE;
			$address = $line;
		} else if (preg_match('/^[im |auf |an ].*[0-9aZ]$/i', strtolower($line))) {
			$address = $line;
		} else if ($lineCounter == 1 && !is_numeric(substr($line,-1,1))) {
			$firm = $line;
		} else {
			if($found_street_already_by_regex && empty($firm)) {
				// it is highly possible that the previous regex was false positive, so we correct it
				$firm = $address;
			}
			$address = $line;
		}
		$lineCounter++;
	}

	// try to get better address
	foreach($commentLines AS $commentLine) {
		if (empty($zip)) {
			preg_match('/(.*) (?:ch[- \/])?(\d{4})(.*)/i', $commentLine, $address_matches);
			if (!empty($address_matches[1])) $address = $address_matches[1];
			if (!empty($address_matches[2])) $zip = $address_matches[2];
			if (!empty($address_matches[3])) $city = $address_matches[3];
		}
	}

	if(!empty($accountName)) {
		$firm = $accountName;
	}

	if (empty($zip) && (strlen($firm) > 20)) {
		// we assume the whole address is in $firm
		unset($address_matches);
		preg_match('/(.*) (?:CH[- \/])?(\d{4})(.*)/i', $firm, $address_matches);
		if (!empty($address_matches[3])) {
			$address_tmp = $address_matches[1];
			$address_parts = explode(" ", $address_tmp);
			$firm = '';
			// if the address is long, there seems to be a name included
			if(count($address_parts) >= 4) {
				for($i=0; $i<count($address_parts);$i++) {
					if ($i < (count($address_parts)-2)) {
						$firm.= " ". $address_parts[$i];
					} else {
						$address.= " ". $address_parts[$i];
					}
				}
			} else {
				$address = $address_tmp;
			}

			$zip = $address_matches[2];
			$city = $address_matches[3];
		}
	}


	if(count($commentLines) == 4 && !empty($zip) && !empty($address) && !empty($city) && !empty($firm) && empty($postfach)) {
		$firm.= " " . $commentLines[1];
	}

	$city = strpos($city, "/Othr/Id") ? substr($city, 0, strpos($city, "/Othr/Id")) : $city; // clean city

	if(strtolower(substr($address,-3, 3)) == " ch") {
		$address = substr($address, 0, -3);
	}

	// sometimes people put their names and street in 1 line and seperate with spaces
	preg_match_all("/(.*)\s{3,6}(.*)/", $address, $matches, PREG_SET_ORDER, 0);
	if(str_word_count($matches[0][1]) >= 2 && count($matches[0]) == 3) {
		$firm.= " " . $matches[0][1];
		$address = $matches[0][2];
	}

	$createdAddress = [
		'firm' => trim($firm),
		'department' => trim($department),
		'address' => trim($address),
		'postfach' => trim($postfach),
		'zip' => trim($zip),
		'city' => trim($city),
	];

	return $createdAddress;
}


//Update scripts

function ko_updates_find_update_files() {
	global $BASE_PATH, $PLUGINS, $UPDATES_CONFIG;

	//Read in current updates stored in DB already
	$currentUpdatesDB = db_select_data('ko_updates', "WHERE 1");
	$currentUpdates = array();
	foreach($currentUpdatesDB as $u) {
		$currentUpdates[$u['name']] = $u;
	}

	//Get core updates
	$files = scandir($BASE_PATH.'install/updates/');
	foreach($files as $file) {
		if(substr($file, 0, 1) == '.') continue;

		include($BASE_PATH.'install/updates/'.$file);
	}

	//Get plugins' updates
	foreach($PLUGINS as $plugin) {
		$files = scandir($BASE_PATH.'plugins/'.$plugin['name'].'/updates/');
		foreach($files as $file) {
			if(substr($file, 0, 1) == '.') continue;

			include($BASE_PATH.'plugins/'.$plugin['name'].'/updates/'.$file);
		}
	}


	foreach($UPDATES_CONFIG as $config) {
		//Entry in db present
		if(isset($currentUpdates[$config['name']])) {
			//Update db entry if not done yet
			if($currentUpdates[$config['name']]['status'] == 0) {
				db_update_data('ko_updates', "WHERE `name` = '".$config['name']."'", $config);
			}
		}
		//Add new entry to db
		else {
			$insertData = $config;
			$insertData['status'] = 0;
			db_insert_data('ko_updates', $insertData);
		}
	}

	//Remove db entries for which no file is present anymore (old or replaced updates)
	foreach($currentUpdates as $update) {
		if(!isset($UPDATES_CONFIG[$update['name']])) {
			db_delete_data('ko_updates', "WHERE `name` = '".$update['name']."'");
		}
	}

}//ko_updates_find_update_files()



function ko_updates_call_update($name) {
	global $BASE_PATH, $UPDATES_CONFIG;

	if(!is_array($UPDATES_CONFIG)) ko_updates_find_update_files();

	if(!isset($UPDATES_CONFIG[$name])) return FALSE;

	$update = $UPDATES_CONFIG[$name];
	if($update['plugin']) {
		$fcn = 'ko_update_'.$update['plugin'].'_'.$name;
	} else {
		$fcn = 'ko_update_'.$name;
	}

	$ret = call_user_func($fcn);

	//Success
	if($ret === 0) {
		//Update DB entry with status and done_date
		db_update_data('ko_updates', "WHERE `name` = '$name'", array('status' => 1, 'done_date' => date('Y-m-d H:i:s')));
	}

	return $ret;
}//ko_updates_call_update()

?>
